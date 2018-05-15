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
 * Add support for the WP Video Lightbox plugin
 *
 * @see https://wordpress.org/plugins/wp-video-lightbox/
 *
 * {@internal Last update: July 2014 based upon v 1.6.8.}}
 */
if ( ! class_exists( 'WPSEO_Video_Plugin_WP_Video_Lightbox' ) ) {

	/**
	 * Class WPSEO_Video_Plugin_WP_Video_Lightbox
	 */
	class WPSEO_Video_Plugin_WP_Video_Lightbox extends WPSEO_Video_Supported_Plugin {


		/**
		 * Conditionally add plugin features to analyse for video content
		 */
		public function __construct() {
			if ( class_exists( 'WP_Video_Lightbox' ) ) {
				$this->shortcodes[] = 'video_lightbox_vimeo5';
				$this->shortcodes[] = 'video_lightbox_youtube';
			}
		}


		/**
		 * Analyse a video shortcode from the plugin for usable video information
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

			if ( isset( $atts['video_id'] ) ) {

				$thumb = '';
				if ( ( isset( $atts['anchor'] ) && ( is_string( $atts['anchor'] ) && $atts['anchor'] !== '' ) ) && preg_match( '`\.(?:' . WPSEO_Video_Sitemap::$image_ext_pattern . ')$`', $atts['anchor'] ) ) {
					$thumb = $atts['anchor'];
				}

				switch ( $sc ) {
					case 'video_lightbox_vimeo5':
						if ( $this->is_vimeo_id( $atts['video_id'] ) ) {
							$vid['type'] = 'vimeo';
							$vid['id']   = $atts['video_id'];
						}
						break;

					case 'video_lightbox_youtube':
						if ( $this->is_youtube_id( $atts['video_id'] ) ) {
							$vid['type'] = 'youtube';
							$vid['id']   = $atts['video_id'];
						}
						break;
				}

				if ( $vid !== array() ) {
					if ( $thumb !== '' ) {
						$vid['thumbnail_loc'] = $thumb;
					}
					$vid = $this->maybe_get_dimensions( $vid, $atts );
				}
			}

			return $vid;
		}
	} /* End of class */

} /* End of class-exists wrapper */
