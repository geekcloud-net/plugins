<?php
/**
 * @package    Internals
 * @since      1.7.0
 * @version    1.7.0
 */

// Avoid direct calls to this file.
if ( ! class_exists( 'WPSEO_Video_Sitemap' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}


/**
 *****************************************************************
 * Brightcove Video SEO Details
 *
 * JSON response format [2014/7/22]:
 * {
 *    "name":"Space-Galaxy",
 *    "videoStillURL":"http:\/\/brightcove.vo.llnwd.net\/e1\/pd\/57838016001\/57838016001_1520916808001_vs-1520911645001.jpg?pubId=57838016001",
 *    "thumbnailURL":"http:\/\/brightcove.vo.llnwd.net\/e1\/pd\/57838016001\/57838016001_1520916809001_th-1520911645001.jpg?pubId=57838016001",
 *    "length":20000,
 *    "playsTotal":86,
 *    "FLVURL":"http:\/\/brightcove.vo.llnwd.net\/e1\/uds\/pd\/57838016001\/57838016001_1520916807001_Space-Galaxy.mp4",
 *    "videoFullLength":{
 *        "audioOnly":false,
 *        "controllerType":"DEFAULT",
 *        "displayName":"Space-Galaxy.mp4",
 *        "encodingRate":500000,
 *        "frameHeight":268,
 *        "frameWidth":480,
 *        "id":1520916807001,
 *        "referenceId":null,
 *        "remoteStreamName":null,
 *        "remoteUrl":null,
 *        "size":1126642,
 *        "uploadTimestampMillis":1332265138822,
 *        "url":"http:\/\/brightcove.vo.llnwd.net\/e1\/uds\/pd\/57838016001\/57838016001_1520916807001_Space-Galaxy.mp4",
 *        "videoCodec":"H264",
 *        "videoContainer":"MP4",
 *        "videoDuration":20000
 *    }
 * }
 */
if ( ! class_exists( 'WPSEO_Video_Details_Brightcove' ) ) {

	/**
	 * Class WPSEO_Video_Details_Brightcove
	 */
	class WPSEO_Video_Details_Brightcove extends WPSEO_Video_Details {

		/**
		 * @var	array	Information on the remote url to use for retrieving the video details
		 */
		protected $remote_url = array(
			'pattern'       => 'http://api.brightcove.com/services/library?command=find_video_by_id&video_id=%s&video_fields=name,playsTotal,videoStillURL,thumbnailURL,length,FLVURL,videoFullLength&media_delivery=http',
			'replace_key'   => 'id',
			'response_type' => 'json',
		);

		/**
		 * @var string  Brightcove token
		 */
		private $bc_token;


		/**
		 * Instantiate the class
		 *
		 * Retrieve the Brightcove token and only pass of to the parent constructor if we find one
		 *
		 * @param array $vid     The video array with all the data.
		 * @param array $old_vid The video array with all the data of the previous "fetch", if available.
		 *
		 * @return \WPSEO_Video_Details_Brightcove
		 */
		public function __construct( $vid, $old_vid = array() ) {
			// Grab Brightcove api key from wp_options.
			$this->bc_token = get_option( 'bc_api_key' );

			if ( ! empty( $this->bc_token ) ) {
				$this->remote_url['pattern'] .= '&token=' . $this->bc_token;

				// Set the class properties before the parent constructor so they're available to maybe_use_old.
				$vid       = (array) $vid;
				$this->vid = array_merge( $this->vid, array_filter( $vid ) );

				if ( is_array( $old_vid ) && $old_vid !== array() ) {
					$this->old_vid = $old_vid;
				}

				// Bail out as early as possible to avoid extra API call.
				$this->maybe_use_old_video_data();
				parent::__construct( $this->vid, $this->old_vid );
			}
			else {
				// @todo [JRF -> Yoast] Why not use (merge with) oldvid data here if available ? The api key might be removed, but old data might still be better than none.
				$this->vid = $vid;
			}
		}


		/**
		 * Retrieve the video id based on a known video url via an external API call.
		 *
		 * @param  int $match_nr [Not used in this implementation].
		 *
		 * @return void
		 */
		protected function determine_video_id_from_url( $match_nr = 1 ) {
			if ( is_string( $this->vid['url'] ) && $this->vid['url'] !== '' ) {
				$parse      = WPSEO_Video_Analyse_Post::wp_parse_url( $this->vid['url'] );
				$query_vars = array();

				if ( ! empty( $parse['query'] ) ) {

					parse_str( $parse['query'], $query_vars );

					if ( isset( $query_vars['vidID'] ) && ( is_string( $query_vars['vidID'] ) && $query_vars['vidID'] !== '' ) ) {
						$this->vid['id'] = $query_vars['ID'];
					}
					elseif ( isset( $query_vars['playerID'] ) && ( is_string( $query_vars['playerID'] ) && $query_vars['playerID'] !== '' ) ) {
						$this->vid['player_id'] = $query_vars['playerID'];
					}

					// Player id is given which means this is a playlist so grab the first video from the playlist.
					if ( isset( $this->vid['player_id'] ) && $this->vid['player_id'] ) {
						$this->determine_video_id_from_playlist();
					}
				}
			}
		}


		/**
		 * Retrieve the video id of the first video of a playlist via an external API call.
		 *
		 * @return void
		 */
		private function determine_video_id_from_playlist() {
			$url = 'http://api.brightcove.com/services/library?command=find_playlists_for_player_id&player_id=%s&video_fields=id&token=%s';
			$url = sprintf( $url, $this->vid['player_id'], $this->bc_token );
			$url = $this->url_encode( $url );

			$response = $this->remote_get( $url );
			if ( is_string( $response ) && $response !== 'null' ) {
				$decoded_response = json_decode( $response );

				if ( is_object( $decoded_response ) && ! isset( $decoded_response->error ) ) {
					if ( isset( $decoded_response->items[0]->videoIds[0] ) && ( is_string( $decoded_response->items[0]->videoIds[0] ) && $decoded_response->items[0]->videoIds[0] !== '' ) ) {
						$this->vid['id'] = $decoded_response->items[0]->videoIds[0];
					}
				}
			}
		}


		/**
		 * Use the "new" post data with the old video data, to prevent the need for an external video
		 * API call when the video hasn't changed.
		 *
		 * Match whether old data can be used on video id or on video url if id is not available
		 *
		 * @param string $match_on  Array key to use in the $vid array to determine whether or not to use the old data
		 *                          Defaults to 'url' for this implementation.
		 *
		 * @return bool  Whether or not valid old data was found (and used)
		 */
		protected function maybe_use_old_video_data( $match_on = 'url' ) {
			if ( ( isset( $this->old_vid['id'] ) && isset( $this->vid['id'] ) ) && $this->old_vid['id'] === $this->vid['id'] ) {
				$match_on = 'id';
			}
			return parent::maybe_use_old_video_data( $match_on );
		}


		/**
		 * Check if the response is for a video
		 *
		 * @return bool
		 */
		protected function is_video_response() {
			return ( isset( $this->decoded_response ) && ( is_object( $this->decoded_response ) && ! isset( $this->decoded_response->error ) ) );
		}


		/**
		 * Set the content location
		 */
		protected function set_content_loc() {
			if ( ! empty( $this->decoded_response->FLVURL ) ) {
				$this->vid['content_loc'] = $this->decoded_response->FLVURL;
			}
		}


		/**
		 * Set the video duration
		 */
		protected function set_duration() {
			if ( ! empty( $this->decoded_response->length ) && $this->decoded_response->length > 0 ) {
				$this->vid['duration'] = ( $this->decoded_response->length / 1000 );
			}
			elseif ( ! empty( $this->decoded_response->videoFullLength->videoDuration ) && $this->decoded_response->videoFullLength->videoDuration > 0 ) {
				$this->vid['duration'] = ( $this->decoded_response->videoFullLength->videoDuration / 1000 );
			}
		}


		/**
		 * Set the video height
		 */
		protected function set_height() {
			if ( ! empty( $this->decoded_response->videoFullLength->frameHeight ) ) {
				$this->vid['height'] = $this->decoded_response->videoFullLength->frameHeight;
			}
		}


		/**
		 * Set the player location
		 */
		protected function set_player_loc() {
			/*
			 * @todo - find out what the player_loc should be - this method is set by (nearly)
			 * every other video class, so why not in this one ?
			 */
			return;
		}


		/**
		 * Set the thumbnail location
		 */
		protected function set_thumbnail_loc() {
			if ( isset( $this->decoded_response->videoStillURL ) && is_string( $this->decoded_response->videoStillURL ) && $this->decoded_response->videoStillURL !== '' ) {
				$image = $this->make_image_local( $this->decoded_response->videoStillURL );
				if ( is_string( $image ) && $image !== '' ) {
					$this->vid['thumbnail_loc'] = $image;
				}
			}
			elseif ( isset( $this->decoded_response->thumbnailURL ) && is_string( $this->decoded_response->thumbnailURL ) && $this->decoded_response->thumbnailURL !== '' ) {
				$image = $this->make_image_local( $this->decoded_response->thumbnailURL );
				if ( is_string( $image ) && $image !== '' ) {
					$this->vid['thumbnail_loc'] = $image;
				}
			}
		}


		/**
		 * Set the video view count
		 */
		protected function set_view_count() {
			if ( ! empty( $this->decoded_response->playsTotal ) ) {
				$this->vid['view_count'] = $this->decoded_response->playsTotal;
			}
		}


		/**
		 * Set the video width
		 */
		protected function set_width() {
			if ( ! empty( $this->decoded_response->videoFullLength->frameWidth ) ) {
				$this->vid['width'] = $this->decoded_response->videoFullLength->frameWidth;
			}
		}
	} /* End of class */

} /* End of class-exists wrapper */
