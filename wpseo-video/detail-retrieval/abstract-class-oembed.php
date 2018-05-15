<?php
/**
 * @package    Internals
 * @since      1.8.0
 * @version    1.8.0
 */

// Avoid direct calls to this file.
if ( ! class_exists( 'WPSEO_Video_Sitemap' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}


/**
 *****************************************************************
 * OEmbed Video SEO Details
 *
 * @see http://oembed.com/
 */
if ( ! class_exists( 'WPSEO_Video_Details_Oembed' ) ) {

	/**
	 * Class WPSEO_Video_Details_Oembed
	 *
	 * Base class for all services where detail retrieval is done via the oembed
	 */
	abstract class WPSEO_Video_Details_Oembed extends WPSEO_Video_Details {

		/**
		 * @var	array	Information on the remote url to use for retrieving the video details
		 */
		protected $remote_url = array(
			'pattern'       => '',
			'replace_key'   => 'url',
			'response_type' => 'json',
		);


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
		protected function maybe_use_old_video_data( $match_on = 'id' ) {
			if ( $this->id_regex !== '' ) {
				return parent::maybe_use_old_video_data( $match_on );
			}
			else {
				return parent::maybe_use_old_video_data( 'url' );
			}
		}


		/**
		 * Check to see if this is really a video.
		 *
		 * @return bool
		 */
		protected function is_video_response() {
			return ( ! empty( $this->decoded_response ) && isset( $this->decoded_response->type ) && $this->decoded_response->type === 'video' );
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
