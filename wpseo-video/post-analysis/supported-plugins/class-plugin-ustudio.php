<?php
/**
 * @package    Internals
 * @since      3.7.0
 * @version    3.7.0
 */

// Avoid direct calls to this file.
if ( ! class_exists( 'WPSEO_Video_Sitemap' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

/**
 *****************************************************************
 * Add support for the uStudio plugin
 */
if ( ! class_exists( 'WPSEO_Video_Plugin_Ustudio' ) ) {

	/**
	 * Class WPSEO_Video_Plugin_Ustudio
	 */
	class WPSEO_Video_Plugin_Ustudio extends WPSEO_Video_Supported_Plugin {


		/**
		 * Conditionally add plugin features to analyse for video content
		 */
		public function __construct() {
			if ( function_exists( 'ustudio_video' ) ) {
				$this->shortcodes = array( 'ustudio' );
				// This adds support for pulling video metadata from a featured video.
				$this->meta_keys[] = '_ustudio_featured_video_shortcode';
			}
		}


		/**
		 * Analyse a video shortcode from the plugin for usable video information.
		 *
		 * @param  string $full_shortcode Full shortcode as found in the post content.
		 * @param  string $sc             Shortcode found.
		 * @param  array  $atts           Shortcode attributes - already decoded if needed.
		 * @param  string $content        The shortcode content, i.e. the bit between [sc]content[/sc].
		 *
		 * @return array   An array with the usable information found or else an empty array
		 */
		public function get_info_from_shortcode( $full_shortcode, $sc, $atts = array(), $content = '' ) {
			return $this->get_vid_from_atts( $atts );
		}

		/**
		 * Analyse a specific post meta field for usable video information.
		 *
		 * @param  string $meta_value  The value to analyse.
		 * @param  string $meta_key    The associated meta key.
		 * @param  int    $post_id     The id of the post this meta value applies to.
		 *
		 * @return array   An array with the usable information found or else an empty array
		 */
		public function get_info_from_post_meta( $meta_value, $meta_key, $post_id ) {
			if ( $meta_key === '_ustudio_featured_video_shortcode' ) {
				$atts = shortcode_parse_atts( $meta_value );
				return $this->get_vid_from_atts( $atts );
			}
		}

		/**
		 * Centralized code for generating video info from shortcode attributes.
		 *
		 * @param  array $atts           Shortcode attributes.
		 */
		public function get_vid_from_atts( $atts ) {
			$vid = array();

			if ( isset( $atts['destination'] ) && isset( $atts['video'] ) ) {
				$vid['id']   = $atts['destination'] . '/' . $atts['video'];
				$vid['type'] = 'ustudio';
			}

			return $vid;
		}
	} /* End of class */


} /* End of class-exists wrapper */
