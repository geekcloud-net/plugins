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
 * Blip.tv Video SEO Details
 *
 * @todo: consider changing retrieval to json ?
 *
 * JSON response format [2014/7/22] based on retrieval via http://blip.tv/oembed/?url=%s :
 * {
 *    "width":480,
 *    "author_name":"nostalgiacritic",
 *    "author_url":"http://blip.tv/nostalgiacritic",
 *    "provider_url":"http://blip.tv",
 *    "version":"1.0",
 *    "thumbnail_width":480,
 *    "provider_name":"Blip",
 *    "thumbnail_url":"http://a.images.blip.tv/NostalgiaCritic-NostalgiaCriticSailorMoon314.jpg",
 *    "height":392,
 *    "thumbnail_height":392,
 *    "html":"<iframe src=\"http://blip.tv/play/gbk7g5S1HwI.x?p=1\" width=\"480\" height=\"392\" frameborder=\"0\" allowfullscreen></iframe><embed type=\"application/x-shockwave-flash\" src=\"http://a.blip.tv/api.swf#gbk7g5S1HwI\" style=\"display:none\"></embed>",
 *    "type":"video",
 *    "title":"Nostalgia Critic: Sailor Moon"
 * }
 */
if ( ! class_exists( 'WPSEO_Video_Details_Blip' ) ) {

	/**
	 * Class WPSEO_Video_Details_Blip
	 *
	 * {@internal
	 * Blip videos have an embedLookup in the format 'gbk7g5S1HwI' and a numeric id.
	 * These two have no discernable relation to each other. Detail lookup can be done via two distinct
	 * methods, for one the embedLookup is usable, for the other the id... }}
	 */
	class WPSEO_Video_Details_Blip extends WPSEO_Video_Details_Oembed {

		/**
		 * @var array   Different remote information retrieval sets to be used depending on the information available
		 */
		private $remotes = array(
			'rss' => array(
				'pattern'       => 'http://blip.tv/rss/view/%s',
				'replace_key'   => 'id',
				'response_type' => '',
			),
			'oembed' => array(
				'pattern'       => 'http://blip.tv/oembed/?url=%s',
				'replace_key'   => 'url',
				'response_type' => 'json',
			),
		);

		/**
		 * @var string oembed|rss
		 */
		private $retrieve_method = '';


		/**
		 * Instantiate the class and determine which remote retrieval method we can use before
		 * passing of to the parent constructor.
		 *
		 * @param array $vid     The video array with all the data.
		 * @param array $old_vid The video array with all the data of the previous "fetch", if available.
		 *
		 * @return \WPSEO_Video_Details_Blip
		 */
		public function __construct( $vid, $old_vid = array() ) {
			if ( isset( $vid['url'] ) ) {
				$this->retrieve_method = 'oembed';
			}
			elseif ( isset( $vid['embedlookup'] ) ) {
				$vid['url']            = 'http://blip.tv/play/' . $vid['embedlookup'];
				$this->retrieve_method = 'oembed';
			}
			elseif ( isset( $vid['id'] ) ) {
				$this->retrieve_method = 'rss';
			}

			if ( isset( $this->remotes[ $this->retrieve_method ] ) ) {
				$this->remote_url = $this->remotes[ $this->retrieve_method ];
			}

			parent::__construct( $vid, $old_vid );
		}


		/**
		 * Check if the response is for a video
		 *
		 * @return bool
		 */
		protected function is_video_response() {
			$valid = false;
			switch ( $this->retrieve_method ) {
				case 'oembed':
					$valid = parent::is_video_response();
					break;

				case 'rss':
					// No way from the base rss info to determine whether it is video, but most info should not match anyway if it isn't.
					if ( ! empty( $this->decoded_response ) ) {
						$valid = true;
					}
					break;
			}
			return $valid;
		}


		/**
		 * Set video details to their new values
		 */
		protected function put_video_details() {
			$this->set_embedlookup();
			parent::put_video_details();
		}


		/**
		 * Set the content location
		 */
		protected function set_content_loc() {
			if ( $this->retrieve_method === 'rss' && preg_match( '`<enclosure length="[\d]+" type="[^"]+" url="([^"]+)"/>`', $this->decoded_response, $match ) ) {
				$this->vid['content_loc'] = $match[1];
			}
		}


		/**
		 * Set the video duration
		 */
		protected function set_duration() {
			switch ( $this->retrieve_method ) {
				case 'oembed':
					parent::set_duration();
					break;

				case 'rss':
					if ( preg_match( '`<blip:runtime>(\d+)</blip:runtime>`', $this->decoded_response, $match ) ) {
						$this->vid['duration'] = $match[1];
					}
					break;
			}
		}


		/**
		 * Grab the embedlookup so we can use the faster oembed method next time.
		 */
		protected function set_embedlookup() {
			switch ( $this->retrieve_method ) {
				case 'oembed':
					// Do this for oembed too as the embedlookup is used for the player loc.
					if ( empty( $this->vid['embedlookup'] ) && ! empty( $this->decoded_response->html ) ) {
						$this->decoded_response->html = stripslashes( $this->decoded_response->html );
						if ( preg_match( '`src="http://blip\.tv/play/([A-Za-z0-9-]{5,})(?:"|[&%\./])`i', $this->decoded_response->html, $match ) ) {
							$this->vid['embedlookup'] = $match[1];
						}
					}
					break;

				case 'rss':
					if ( preg_match( '`<blip:embedLookup>([A-Za-z0-9-]{5,})</blip:embedLookup>`', $this->decoded_response, $match ) ) {
						$this->vid['embedlookup'] = $match[1];
					}
					break;
			}
		}


		/**
		 * Set the video height
		 */
		protected function set_height() {
			switch ( $this->retrieve_method ) {
				case 'oembed':
					parent::set_height();
					break;

				case 'rss':
					if ( preg_match( '`<media:player url=.+?<iframe.+?" height="([0-9]+)"`', $this->decoded_response, $match ) ) {
						$this->vid['height'] = $match[1];
					}
					break;
			}
		}


		/**
		 * Set the player location
		 */
		protected function set_player_loc() {
			switch ( $this->retrieve_method ) {
				case 'oembed':
					if ( ! empty( $this->vid['embedlookup'] ) ) {
						// @todo [JRF => Yoast] Review if this could be the correct player loc
						$this->vid['player_loc'] = 'http://a.blip.tv/api.swf#' . urlencode( $this->vid['embedlookup'] );
					}
					break;

				case 'rss':
					if ( preg_match( '`<media:player url="([^"]+)">`', $this->decoded_response, $match ) ) {
						$this->vid['player_loc'] = $match[1];
					}
					break;
			}
		}


		/**
		 * Set the thumbnail location
		 */
		protected function set_thumbnail_loc() {
			switch ( $this->retrieve_method ) {
				case 'oembed':
					parent::set_thumbnail_loc();
					break;

				case 'rss':
					if ( preg_match( '`<media:thumbnail url="([^"]+)"/>`', $this->decoded_response, $match ) ) {
						$image = $this->make_image_local( $match[1] );
						if ( is_string( $image ) && $image !== '' ) {
							$this->vid['thumbnail_loc'] = $image;
						}
					}
					break;
			}
		}


		/**
		 * Set the video type
		 *
		 * @todo - chould this be changed to blip ? or does that impact something else ?
		 */
		protected function set_type() {
			$this->vid['type'] = 'blip.tv';
		}


		/**
		 * Set the video width
		 */
		protected function set_width() {
			switch ( $this->retrieve_method ) {
				case 'oembed':
					parent::set_width();
					break;

				case 'rss':
					if ( preg_match( '`<media:player url=.+?<iframe.+?" width="([0-9]+)"`', $this->decoded_response, $match ) ) {
						$this->vid['width'] = $match[1];
					}
					break;
			}
		}
	} /* End of class */

} /* End of class-exists wrapper */
