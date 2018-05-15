<?php
/**
 * @package    Internals
 * @since      x.x.x
 * @version    x.x.x
 */

// Avoid direct calls to this file
if ( ! class_exists( 'WPSEO_Video_Sitemap' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}


/*******************************************************************
 * [SERVICENAME] Video SEO Details
 *******************************************************************/
if ( ! class_exists( 'WPSEO_Video_Details_[SERVICENAME]' ) ) {

	/**
	 * Class WPSEO_Video_Details_[SERVICENAME]
	 */
	class WPSEO_Video_Details_[SERVICENAME] extends WPSEO_Video_Details {
	//class WPSEO_Video_Details_[SERVICENAME] extends WPSEO_Video_Details_Oembed {

		/**
		 * @var	string	Regular expression to retrieve a video id from a known video url
		 */
		//protected $id_regex = '``i';

		/**
		 * @var	string	Sprintf template to create a url from an id
		 */
		//protected $url_template = '.../%s/';

		/**
		 * @var	array	Information on the remote url to use for retrieving the video details
		 */
		protected $remote_url = array(
			'pattern'       => '.../%s',
			'replace_key'   => 'url|id',
			'response_type' => 'json|serial|simplexml',
		);



		/**
		 * Set the player location
		 */
		protected function set_player_loc() {
		}


		/**
		 * Set the thumbnail location
		 */
		protected function set_thumbnail_loc() {
		}


		/*
		protected function set_content_loc() {}
		protected function set_duration() {}
		protected function set_height() {}
		protected function set_id() {}
		protected function set_view_count() {}
		protected function set_width() {}

		protected function set_type() {} -> normally not needed
		*/

	} /* End of class */

} /* End of class-exists wrapper */