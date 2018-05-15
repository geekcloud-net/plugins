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
 * Wistia Video SEO Details
 *
 * @see http://wistia.com/doc/data-api for the API (but needs a API key, while oembed actually already gives us what we need)
 *
 * Example JSON response format with iframe response [2014/7/22]:
 * {
 * 	  "version":"1.0",
 * 	  "type":"video",
 * 	  "html":"<iframe src=\"//fast.wistia.net/embed/iframe/ms9j4z4sdg\" allowtransparency=\"true\" frameborder=\"0\" scrolling=\"no\" class=\"wistia_embed\" name=\"wistia_embed\" allowfullscreen mozallowfullscreen webkitallowfullscreen oallowfullscreen msallowfullscreen width=\"960\" height=\"540\"></iframe>",
 * 	  "width":960,
 * 	  "height":540,
 * 	  "provider_name":"Wistia, Inc.",
 * 	  "provider_url":"http://wistia.com",
 * 	  "title":"Puppies",
 * 	  "thumbnail_url":"https://embed-ssl.wistia.com/deliveries/d5f1c25578e8748cc5b6b946e02e654d9f9db47a.jpg?image_crop_resized=960x540",
 * 	  "thumbnail_width":960,
 * 	  "thumbnail_height":540,
 * 	  "duration":23.268
 * }
 */
if ( ! class_exists( 'WPSEO_Video_Details_Wistia' ) ) {

	/**
	 * Class WPSEO_Video_Details_Wistia
	 */
	class WPSEO_Video_Details_Wistia extends WPSEO_Video_Details {

		/**
		 * @var	string	Sprintf template to create a url from an id
		 */
		protected $url_template = 'http://fast.wistia.net/medias/%s?embedType=iframe';

		/**
		 * @var	array	Information on the remote url to use for retrieving the video details
		 */
		protected $remote_url = array(
			'pattern'       => 'http://fast.wistia.com/oembed?url=%s',
			'replace_key'   => 'url',
			'response_type' => 'json',
		);

		/**
		 * @var bool   Whether or not to use the fallback method to retrieve certain information on the video
		 */
		protected $use_fallback = false;

		/**
		 * @var string Frame source - used by fallback method
		 */
		protected $frame_source;


		/**
		 * Instantiate the class
		 *
		 * Adjust the video url before passing off to the parent constructor
		 *
		 * @param array $vid     The video array with all the data.
		 * @param array $old_vid The video array with all the data of the previous "fetch", if available.
		 *
		 * @return \WPSEO_Video_Details_Wistia
		 */
		public function __construct( $vid, $old_vid = array() ) {
			if ( isset( $vid['id'] ) ) {
				$vid['url'] = sprintf( $this->url_template, rawurlencode( $vid['id'] ) );
			}
			parent::__construct( $vid, $old_vid );
		}


		/**
		 * Use the "new" post data with the old video data, to prevent the need for an external video
		 * API call when the video hasn't changed.
		 *
		 * Match whether old data can be used on url rather than video id
		 *
		 * @param string $match_on  Array key to use in the $vid array to determine whether or not to use the old data
		 *                          Defaults to 'url' for this implementation.
		 *
		 * @return bool  Whether or not valid old data was found (and used)
		 */
		protected function maybe_use_old_video_data( $match_on = 'url' ) {
			return parent::maybe_use_old_video_data( $match_on );
		}


		/**
		 * Retrieve information on a video via a remote API call
		 *
		 * @return void
		 */
		protected function get_remote_video_info() {
			if ( is_string( $this->vid['url'] ) && $this->vid['url'] !== '' ) {
				// Temporarily change the url.
				$real_url = $this->vid['url'];
				if ( strpos( $this->vid['url'], 'embedType=' ) !== false ) {
					// Avoid adding embedType twice.
					$this->vid['url'] = str_replace( array( 'embedType=api', 'embedType=iframe', 'embedType=popover', 'embedType=seo', 'embedType=async' ), 'embedType=seo', $this->vid['url'] );
				}
				else {
					$this->vid['url'] = add_query_arg( 'embedType', 'seo', $this->vid['url'] );
				}

				parent::get_remote_video_info();

				// Reset the url.
				$this->vid['url'] = $real_url;
			}
		}


		/**
		 * Check if the response is for a video
		 *
		 * @return bool
		 */
		protected function is_video_response() {
			return ( ! empty( $this->decoded_response ) && isset( $this->decoded_response->type ) && $this->decoded_response->type === 'video' );
		}


		/**
		 * Determine whether or not the use of the fallback method is needed and set video details
		 * to their new values by passing off to the parent method
		 */
		protected function put_video_details() {
			if ( ! empty( $this->decoded_response->html ) ) {
				$this->decoded_response->html = stripslashes( $this->decoded_response->html );

				if ( strpos( $this->decoded_response->html, '<div itemprop="video"' ) === false && preg_match( '`<iframe src=([\'"])([^\'"\s]+)\1`', $this->decoded_response->html, $match ) ) {

					$this->use_fallback = true;

					$response = $this->remote_get( $match[2] );
					if ( is_string( $response ) && $response !== '' && $response !== 'null' ) {
						$this->frame_source = $response;
					}
				}
			}

			parent::put_video_details();
		}


		/**
		 * Set the content location
		 */
		protected function set_content_loc() {
			if ( $this->use_fallback === false ) {
				if ( ! empty( $this->decoded_response->html ) && preg_match( '`<meta itemprop="contentURL" content="([^"]+)" />`', $this->decoded_response->html, $match ) ) {
					$this->vid['content_loc'] = $match[1];
				}
			}
			elseif ( ! empty( $this->frame_source ) && preg_match( '`<a href=([\'"])([^\'"\s]+)\1 id=([\'"])wistia_fallback\3`', $this->frame_source, $match ) ) {
				$this->vid['content_loc'] = $match[2];
			}
		}


		/**
		 * Set the video duration
		 */
		protected function set_duration() {
			$this->set_duration_from_json_object();
		}


		/**
		 * Set the video height
		 */
		protected function set_height() {
			$this->set_height_from_json_object();
		}


		/**
		 * Set the player location
		 */
		protected function set_player_loc() {
			if ( $this->use_fallback === false ) {
				if ( ! empty( $this->decoded_response->html ) && preg_match( '`<meta itemprop="embedURL" content="([^"]+)" />`', $this->decoded_response->html, $match ) ) {
					$this->vid['player_loc'] = $match[1];
				}
			}
			elseif ( ! empty( $this->frame_source ) ) {
				if ( preg_match( '`"type":"flv","url":"([^"]*)"`', $this->frame_source, $match ) ) {
					$flv = $match[1];
				}
				elseif ( preg_match( '`"type":"hdflv","url":"([^"]*)"`', $this->frame_source, $match ) ) {
					$flv = $match[1];
				}

				if ( preg_match( '`"type":"still","url":"([^"]*)"`', $this->frame_source, $match ) ) {
					$still = $match[1];
				}

				if ( preg_match( '`"accountKey":"([^"]*)"`', $this->frame_source, $match ) ) {
					$account_key = $match[1];
				}

				if ( preg_match( '`"mediaKey":"([^"]*)"`', $this->frame_source, $match ) ) {
					$media_key = $match[1];
				}

				if ( empty( $this->vid['duration'] ) ) {
					$this->set_duration();
				}

				if ( isset( $flv, $still, $account_key, $media_key, $this->vid['duration'] ) ) {
					$url = sprintf( 'https://wistia.sslcs.cdngc.net/flash/embed_player_v2.0.swf?videoUrl=%1$s&stillUrl=%2$s&controlsVisibleOnLoad=false&unbufferedSeek=true&autoLoad=false&autoPlay=true&endVideoBehavior=default&embedServiceURL=http://distillery.wistia.com/x&accountKey=%3$s&mediaID=%4$s&mediaDuration=%5$s&fullscreenDisabled=false',
						$flv, // #1.
						$still, // #2.
						$account_key, // #3.
						$media_key, // #4.
						$this->vid['duration'] // #5.
					);

					$this->vid['player_loc'] = $this->url_encode( $url );
				}
			}
		}



		/**
		 * Set the thumbnail location
		 */
		protected function set_thumbnail_loc() {
			$this->set_thumbnail_loc_from_json_object();
		}


		/**
		 * Set the video width
		 */
		protected function set_width() {
			$this->set_width_from_json_object();
		}
	} /* End of class */

} /* End of class-exists wrapper */
