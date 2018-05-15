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
 * Add support for the Smart YouTube plugin
 *
 * @see https://wordpress.org/plugins/smart-youtube/
 *
 * {@internal Last update: July 2014 based upon v 4.2.5.}}
 */
if ( ! class_exists( 'WPSEO_Video_Plugin_Smart_Youtube' ) ) {

	/**
	 * Class WPSEO_Video_Plugin_Smart_Youtube
	 */
	class WPSEO_Video_Plugin_Smart_Youtube extends WPSEO_Video_Plugin_Jetpack {


		/**
		 * Conditionally add plugin features to analyse for video content
		 */
		public function __construct() {
			if ( class_exists( 'SmartYouTube_PRO' ) ) {
				$this->shortcodes[] = 'youtube';

				/**
				 * As the plugin doesn't offer a reliable version nr variable/constant, there's no way
				 * to add these based on version. v3.8.1 introduced httpvhp
				 */
				$this->alt_protocols = array(
					'httpv://',
					'httpvh://',
					'httpvhd://',
					// 'httpvp://', = playlist, not (yet) supported
					// 'httpvhp://', = HD playlist, not (yet) supported
				);
			}
		}


		/**
		 * Analyse a video shortcode from the plugin for usable video information
		 *
		 * {@internal This method is 100% the same as the YouTube one in JetPack, so made this an extending class.}}
		 *
		 * @param  string $full_shortcode  Full shortcode as found in the post content.
		 * @param  string $sc              Shortcode found.
		 * @param  array  $atts            Shortcode attributes - already decoded if needed.
		 * @param  string $content         The shortcode content, i.e. the bit between [sc]content[/sc].
		 *
		 * @return array   An array with the usable information found or else an empty array
		 */
		public function get_info_from_shortcode( $full_shortcode, $sc, $atts = array(), $content = '' ) {
			$vid         = parent::get_youtube_info( $full_shortcode, $sc, $atts, $content );
			$vid['type'] = 'youtube';
			return $vid;
		}
	} /* End of class */

} /* End of class-exists wrapper */
