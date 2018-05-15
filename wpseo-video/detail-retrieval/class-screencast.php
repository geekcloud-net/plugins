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
 * Screencast.com Video SEO Details
 *
 * @todo: no api or connection from getting video details from the url so we extract the
 * details from the embed code itself
 * Embedly actually provides usable info, so we may want to consider implementing this using
 * Embedly as Screencast does not seem to support oembed nor have their own API.
 */
if ( ! class_exists( 'WPSEO_Video_Details_Screencast' ) ) {

	/**
	 * Class WPSEO_Video_Details_Screencast
	 */
	class WPSEO_Video_Details_Screencast extends WPSEO_Video_Details {

		/**
		 * @var	string	Regular expression to retrieve a video id from a known video url
		 */
		protected $id_regex = '`[/\.]screencast\.com/(.*)$`i';

		/**
		 * @var	array	Information on the remote url to use for retrieving the video details
		 */
		protected $remote_url = array();

		/**
		 * Retrieve the video id from a known video url based on a regex match.
		 *
		 * @param int $match_nr The captured parenthesized sub-pattern to use from matches. Defaults to 1.
		 *
		 * @return void
		 */
		protected function determine_video_id_from_url( $match_nr = 1 ) {
			if ( ( isset( $this->vid['url']['url'] ) && is_string( $this->vid['url']['url'] ) && $this->vid['url']['url'] !== '' ) && $this->id_regex !== '' ) {
				if ( preg_match( $this->id_regex, $this->vid['url']['url'], $match ) ) {
					$this->vid['id'] = $match[ $match_nr ];
				}
			}
		}

		/**
		 * Retrieve information on a video via a remote API call.
		 *
		 * Currently implemented to use already existing information.
		 *
		 * @return void
		 */
		protected function get_remote_video_info() {
			if ( is_array( $this->vid['url'] ) && isset( $this->vid['url']['embed'] ) ) {
				$this->remote_response = $this->vid['url']['embed'];
			}
		}


		/**
		 * Decode a remote response as DOMXPath object
		 *
		 * @uses WPSEO_Video_Details::$remote_url
		 *
		 * @return void
		 */
		protected function decode_remote_video_info() {
			if ( ! empty( $this->remote_response ) && ( extension_loaded( 'dom' ) && class_exists( 'DOMXPath' ) ) ) {
				$dom = new DOMDocument();
				@$dom->loadHTML( $this->remote_response );
				$xpath = new DOMXPath( $dom );

				$item = $xpath->query( '//object/param[@name="flashVars"]' );
				if ( $item instanceof DOMNodeList && $item->length > 0 ) {
					$item = $item->item( 0 )->getAttribute( 'value' );
					parse_str( $item, $response );
					if ( is_array( $response ) && $response !== array() ) {
						$this->decoded_response = $response;
					}
				}
			}

			if ( ! isset( $this->decoded_response ) ) {
				$this->decoded_response = $this->remote_response;
			}
		}


		/**
		 * Check if the response is for a valid video
		 *
		 * @return bool
		 */
		protected function is_video_response() {
			return ( ! empty( $this->decoded_response ) );
		}


		/**
		 * Set the video height
		 */
		protected function set_height() {
			if ( ! empty( $this->decoded_response['containerheight'] ) ) {
				$this->vid['height'] = $this->decoded_response['containerheight'];
			}
		}


		/**
		 * Set the player location
		 */
		protected function set_player_loc() {
			if ( ! empty( $this->decoded_response['content'] ) ) {
				$this->vid['player_loc'] = $this->decoded_response['content'];
			}
		}


		/**
		 * Set the thumbnail location
		 */
		protected function set_thumbnail_loc() {
			if ( isset( $this->decoded_response['thumb'] ) && is_string( $this->decoded_response['thumb'] ) && $this->decoded_response['thumb'] !== '' ) {
				$image = $this->make_image_local( $this->decoded_response['thumb'] );
				if ( is_string( $image ) && $image !== '' ) {
					$this->vid['thumbnail_loc'] = $image;
				}
			}
		}


		/**
		 * Set the video width
		 */
		protected function set_width() {
			if ( ! empty( $this->decoded_response['containerwidth'] ) ) {
				$this->vid['width'] = $this->decoded_response['containerwidth'];
			}
		}
	} /* End of class */

} /* End of class-exists wrapper */
