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
 * Add support for the YouTube Embed Plus plugin
 *
 * @todo We may want to remove their og tags from the main class:
 * remove_action( 'wp_head', array( 'YouTubePrefs', 'do_ogvideo' ) );
 *
 * @see https://wordpress.org/plugins/youtube-embed-plus/
 *
 * {@internal Last update: August 2014 based upon v 8.7.}}
 */
if ( ! class_exists( 'WPSEO_Video_Plugin_Youtube_Embed_Plus' ) ) {

	/**
	 * Class WPSEO_Video_Plugin_Youtube_Embed_Plus
	 */
	class WPSEO_Video_Plugin_Youtube_Embed_Plus extends WPSEO_Video_Supported_Plugin {


		/**
		 * Conditionally add plugin features to analyse for video content
		 */
		public function __construct() {
			if ( class_exists( 'YouTubePrefs' ) ) {
				$this->shortcodes[] = 'embedyt';
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

			if ( isset( $content ) && ( is_string( $content ) && $content !== '' ) ) {
				$vid['type'] = 'youtube';
				$list        = explode( '&', $content );
				$vid['url']  = $list[0];
				unset( $list[0] );

				if ( $list !== array() ) {
					// Retrieve width/height.
					foreach ( $list as $key => $value ) {
						$value = explode( '=', $value );
						if ( in_array( $value[0], array( 'width', 'height', 'w', 'h' ), true ) ) {
							if ( ! empty( $value[1] ) ) {
								$atts[ $value[0] ] = $value[1];
							}
							unset( $list[ $key ] );
						}
					}

					$vid = $this->maybe_get_dimensions( $vid, $atts, true );

					/*
					 * Any attributes left over are partly real url parts, partly plugin settings, let's
					 * just put them back on...
					 */
					if ( $list !== array() ) {
						$vid['url'] = $vid['url'] . '&' . implode( '&', $list );
					}
				}
			}

			return $vid;
		}
	} /* End of class */
} /* End of class-exists wrapper */
