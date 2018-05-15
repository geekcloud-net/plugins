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
 * Add support for the WP YouTube Lyte plugin
 *
 * @see https://wordpress.org/plugins/wp-youtube-lyte/
 *
 * {@internal Last update: July 2014 based upon v 1.4.2.}}
 */
if ( ! class_exists( 'WPSEO_Video_Plugin_WP_Youtube_Lyte' ) ) {

	/**
	 * Class WPSEO_Video_Plugin_WP_Youtube_Lyte
	 */
	class WPSEO_Video_Plugin_WP_Youtube_Lyte extends WPSEO_Video_Supported_Plugin {


		/**
		 * Conditionally add plugin features to analyse for video content
		 */
		public function __construct() {
			if ( function_exists( 'shortcode_lyte' ) ) {
				$this->shortcodes[] = 'lyte';

				$this->alt_protocols = array(
					'httpv://',
					'httpa://',
				);
			}
		}


		/**
		 * Analyse a video shortcode from the plugin for usable video information
		 *
		 * Note: currently disregards playlist and audio shortcodes
		 *
		 * @param  string $full_shortcode Full shortcode as found in the post content.
		 * @param  string $sc             Shortcode found.
		 * @param  array  $atts           Shortcode attributes - already decoded if needed.
		 * @param  string $content        The shortcode content, i.e. the bit between [sc]content[/sc].
		 *
		 * @return array   An array with the usable information found or else an empty array.
		 */
		public function get_info_from_shortcode( $full_shortcode, $sc, $atts = array(), $content = '' ) {
			$vid = array();

			if ( isset( $atts['id'] ) && $this->is_youtube_id( $atts['id'] ) ) {
				if ( ( ! isset( $atts['playlist'] ) || $atts['playlist'] != 'true' ) && ( ! isset( $atts['audio'] ) || $atts['audio'] != 'true' ) ) {
					$vid['type'] = 'youtube';
					$vid['id']   = $atts['id'];
				}
			}

			return $vid;
		}
	} /* End of class */

} /* End of class-exists wrapper */
