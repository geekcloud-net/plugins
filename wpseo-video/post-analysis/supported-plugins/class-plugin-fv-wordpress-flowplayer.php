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
 * Add support for the FV WordPress Flowplayer plugin
 *
 * @see https://wordpress.org/plugins/fv-wordpress-flowplayer/
 *
 * {@internal Last update: July 2014 based upon v 2.2.18.}}
 */
if ( ! class_exists( 'WPSEO_Video_Plugin_FV_Wordpress_Flowplayer' ) ) {

	/**
	 * Class WPSEO_Video_Plugin_FV_Wordpress_Flowplayer
	 */
	class WPSEO_Video_Plugin_FV_Wordpress_Flowplayer extends WPSEO_Video_Supported_Plugin {


		/**
		 * Conditionally add plugin features to analyse for video content
		 */
		public function __construct() {
			if ( class_exists( 'FV_Player_Checker' ) ) {
				$this->shortcodes = array(
					'flowplayer',
					'fvplayer',
				);
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
		 * @return array   An array with the usable information found or else an empty array
		 */
		public function get_info_from_shortcode( $full_shortcode, $sc, $atts = array(), $content = '' ) {
			$vid = array();

			if ( isset( $atts['src'] ) && preg_match( '`\.(?:' . WPSEO_Video_Sitemap::$video_ext_pattern . ')$`', $atts['src'] ) ) {
				$vid['type']        = 'jwplayer'; // @todo  is this really the correct type ?
				$vid['url']         = $atts['src'];
				$vid['content_loc'] = $atts['src'];
				$vid['maybe_local'] = true;


				if ( isset( $atts['splash'] ) && preg_match( '`\.(?:' . WPSEO_Video_Sitemap::$image_ext_pattern . ')$`', $atts['splash'] ) ) {
					$vid['thumbnail_loc'] = $atts['splash'];
				}

				$vid = $this->maybe_get_dimensions( $vid, $atts );

				/*
				@todo - maybe implement ? would need post_id within this method

				$meta = get_post_meta( $post->ID, flowplayer::get_video_key( $meta_original ), true );
				if ( $meta !== false && ! empty( $meta['duration'] ) ) {
					$vid['duration'] = $meta['duration'];
				}
				*/
			}

			return $vid;
		}
	} /* End of class */

} /* End of class-exists wrapper */
