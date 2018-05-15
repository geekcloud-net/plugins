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
 * Add support for the JW Player plugin
 *
 * @see https://wordpress.org/plugins/jw-player-plugin-for-wordpress/
 *
 * {@internal Last update: July 2014 based upon v 2.1.5.}}
 */
if ( ! class_exists( 'WPSEO_Video_Plugin_JW_Player' ) ) {

	/**
	 * Class WPSEO_Video_Plugin_JW_Player
	 */
	class WPSEO_Video_Plugin_JW_Player extends WPSEO_Video_Supported_Plugin {


		/**
		 * Conditionally add plugin features to analyse for video content
		 */
		public function __construct() {
			if ( defined( 'JWP6' ) ) {
				$this->shortcodes[] = 'jwplayer';
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

			if ( isset( $atts['mediaid'] ) ) {
				$mediaid = WPSEO_Video_Wrappers::yoast_wpseo_video_validate_int( $atts['mediaid'] );

				if ( $mediaid !== false && $mediaid > 0 ) {
					$content_loc = wp_get_attachment_url( $mediaid ); // @todo should we maybe use JWP6_Plugin::url_from_post( $mediaid ) to stay in line ?
					if ( $content_loc !== false ) {
						$vid['content_loc'] = $content_loc;
					}

					$duration = get_post_meta( $mediaid, 'jwplayermodule_duration', true );
					if ( $duration !== '' ) {
						$vid['duration'] = $duration;
					}

					$thumbnail_loc = get_post_meta( $mediaid, 'jwplayermodule_thumbnail_url', true );
					if ( $thumbnail_loc !== '' ) {
						$vid['thumbnail_loc'] = $thumbnail_loc;
					}
				}
				unset( $mediaid, $content_loc, $duration, $thumbnail_loc );
			}

			// @todo [JRF] Does this really belong with this plugin ? can't find documentation for this plugin on the html5_file or file attributes
			if ( ! isset( $vid['content_loc'] ) ) {
				if ( isset( $atts['html5_file'] ) && ( is_string( $atts['html5_file'] ) && $atts['html5_file'] !== '' ) ) {
					$vid['content_loc'] = $atts['html5_file'];
				}
				elseif ( isset( $atts['file'] ) && ( is_string( $atts['file'] ) && $atts['file'] !== '' ) ) {
					$vid['content_loc'] = $atts['file'];
				}

				if ( isset( $vid['content_loc'], $atts['image'] ) && ( is_string( $atts['image'] ) && $atts['image'] !== '' ) ) {
					$vid['thumbnail_loc'] = $atts['image'];
				}
			}

			if ( $vid !== array() ) {
				$vid['type'] = 'jwplayer';
				// @todo - should this be added ? or should we believe the jwplayer plugin info to be good enough (though incomplete) ?
				// $vid['url']         = $vid['content_loc'];
				// $vid['maybe_local'] = true;
				$vid = $this->maybe_get_dimensions( $vid, $atts );
			}
			return $vid;
		}
	} /* End of class */

} /* End of class-exists wrapper */
