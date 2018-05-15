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
 * Add support for the Flowplayer HTML5 plugin
 *
 * @see      https://wordpress.org/plugins/flowplayer5/
 *
 * {@internal Last update: July 2014 based upon v 1.8.1.}}
 */
if ( ! class_exists( 'WPSEO_Video_Plugin_Flowplayer5' ) ) {

	/**
	 * Class WPSEO_Video_Plugin_Flowplayer5
	 */
	class WPSEO_Video_Plugin_Flowplayer5 extends WPSEO_Video_Supported_Plugin {


		/**
		 * Conditionally add plugin features to analyse for video content
		 */
		public function __construct() {
			if ( defined( 'FP5_PLUGIN_FILE' ) ) {
				$this->shortcodes[] = 'flowplayer';
				$this->post_types[] = 'flowplayer5';
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

			if ( ! empty( $atts['id'] ) && WPSEO_Video_Wrappers::yoast_wpseo_video_validate_int( $atts['id'] ) !== false ) {
				$vid = $this->get_info_for_post_type( (int) $atts['id'], 'flowplayer5', null, true );
			}
			else {
				// Old flowplayer shortcode format.
				if ( isset( $atts['webm'] ) && ( is_string( $atts['webm'] ) && $atts['webm'] !== '' ) ) {
					$vid['url'] = $atts['webm'];
				}
				elseif ( isset( $atts['mp4'] ) && ( is_string( $atts['mp4'] ) && $atts['mp4'] !== '' ) ) {
					$vid['url'] = $atts['mp4'];
				}
				elseif ( isset( $atts['ogg'] ) && ( is_string( $atts['ogg'] ) && $atts['ogg'] !== '' ) ) {
					$vid['url'] = $atts['ogg'];
				}

				if ( isset( $vid['url'] ) ) {
					$vid['content_loc'] = $vid['url'];
					$vid['maybe_local'] = true;

					if ( isset( $atts['splash'] ) && ( is_string( $atts['splash'] ) && $atts['splash'] !== '' ) ) {
						$vid['thumbnail_loc'] = $atts['splash'];
					}

					$vid['type'] = 'flowplayer';
					$vid         = $this->maybe_get_dimensions( $vid, $atts, true );
				}
			}

			return $vid;
		}


		/**
		 * Set the video details based on the post meta information
		 *
		 * @param int    $post_id   The post id of the video post.
		 * @param string $post_type The post type of the video post.
		 * @param object $post      The post object.
		 * @param bool   $shortcode Whether the info is called up as a post or as a shortcode in another post.
		 *                          Defaults to false (= post, not shortcode).
		 *
		 * @return array $vid
		 */
		public function get_info_for_post_type( $post_id, $post_type, $post, $shortcode = false ) {
			$vid = array();

			$url            = '';
			$fp5_webm_video = get_post_meta( $post_id, 'fp5-webm-video', true );
			if ( $fp5_webm_video !== '' ) {
				$url = $fp5_webm_video;
			}
			else {
				$fp5_mp4_video = get_post_meta( $post_id, 'fp5-mp4-video', true );
				if ( $fp5_mp4_video !== '' ) {
					$url = $fp5_mp4_video;
				}
				else {
					$fp5_ogg_video = get_post_meta( $post_id, 'fp5-ogg-video', true );
					if ( $fp5_ogg_video !== '' && strpos( $fp5_ogg_video, '.ogv' ) === ( strlen( $fp5_ogg_video ) - 4 ) ) {
						$url = $fp5_ogg_video;
					}
				}
			}

			if ( $url !== '' ) {
				$vid['type']        = 'flowplayer';
				$vid['url']         = $url;
				$vid['content_loc'] = $url;
				$vid['maybe_local'] = true;

				if ( $shortcode === false ) {
					$vid['post_id'] = $post_id;
				}

				/*
				 * {@internal [JRF] I can't find the duration meta field being added anywhere in the plugin,
				 * but this came from the code *they* provided...}}
				 */
				$duration = get_post_meta( $post_id, 'fp5-duration', true );
				if ( $duration !== '' && $duration > 0 ) {
					$vid['duration'] = $duration;
				}

				$thumbnail = get_post_meta( $post_id, 'fp5-splash-image', true );
				if ( $thumbnail !== '' ) {
					$vid['thumbnail_loc'] = $thumbnail;
				}

				$width = get_post_meta( $post_id, 'fp5-width', true );
				if ( $width !== '' ) {
					$vid['width'] = $width;
				}

				$height = get_post_meta( $post_id, 'fp5-height', true );
				if ( $height !== '' ) {
					$vid['height'] = $height;
				}
			}

			return $vid;
		}
	} /* End of class */

} /* End of class-exists wrapper */
