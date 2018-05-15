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
 * Add support for the TubePress plugin
 *
 * @see      https://wordpress.org/plugins/tubepress/
 *
 * {@internal Last update: July 2014 based upon v 3.1.6.}}
 */
if ( ! class_exists( 'WPSEO_Video_Plugin_Tubepress' ) ) {

	/**
	 * Class WPSEO_Video_Plugin_Tubepress
	 */
	class WPSEO_Video_Plugin_Tubepress extends WPSEO_Video_Supported_Plugin {

		/**
		 * Conditionally add plugin features to analyse for video content
		 */
		public function __construct() {
			if ( function_exists( 'bootTubePress' ) ) {
				$this->shortcodes[] = 'tubepress';
			}
		}


		/**
		 * Analyse a video shortcode from the plugin for usable video information
		 *
		 * @todo Figure out how to deal with the plain [tubepress] shortcode which apparently works as well
		 * @see  https://github.com/Yoast/wpseo-video/issues/75
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

			if ( ! empty( $atts['video'] ) ) {
				// Vimeo needs to be tested first as Vimeo id also matches on YouTube id regex.
				if ( $this->is_vimeo_id( $atts['video'] ) ) {
					$vid['type'] = 'vimeo';
					$vid['id']   = $atts['video'];
				}
				elseif ( $this->is_youtube_id( $atts['video'] ) ) {
					$vid['type'] = 'youtube';
					$vid['id']   = $atts['video'];

				}

				if ( $vid !== array() ) {
					if ( isset( $atts['embeddedwidth'] ) && ! empty( $atts['embeddedwidth'] ) ) {
						$vid['width'] = (int) $atts['embeddedwidth'];
					}
					if ( isset( $atts['embeddedheight'] ) && ! empty( $atts['embeddedheight'] ) ) {
						$vid['height'] = (int) $atts['embeddedheight'];
					}
				}
			}

			return $vid;
		}
	} /* End of class */

} /* End of class-exists wrapper */
