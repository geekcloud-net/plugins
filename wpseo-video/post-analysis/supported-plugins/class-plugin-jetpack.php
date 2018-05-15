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
 * Add support for the Jetpack plugin
 *
 * @see      https://wordpress.org/plugins/jetpack/
 * @see      https://jetpack.com/support/shortcode-embeds/
 *
 * {@internal Last update: July 2014 based upon v 3.0.2.}}
 */
if ( ! class_exists( 'WPSEO_Video_Plugin_Jetpack' ) ) {

	/**
	 * Class WPSEO_Video_Plugin_Jetpack
	 */
	class WPSEO_Video_Plugin_Jetpack extends WPSEO_Video_Supported_Plugin {

		/**
		 * @var array $shortcodes_to_add Shortcodes added by this plugin
		 */
		private $shortcodes_to_add = array(
			'blip.tv',
			'dailymotion',
			'flickr',
			'googlevideo',
			'ted',
			'vimeo',
			'vine',
			'youtube',
		);


		/**
		 * Conditionally add plugin features to analyse for video content
		 */
		public function __construct() {
			if ( defined( 'JETPACK__VERSION' ) ) {
				// Only load if shortcode module is activated.
				if ( function_exists( 'shortcode_new_to_old_params' ) ) {
					foreach ( $this->shortcodes_to_add as $sc ) {
						// Respect JP filter even though they themselves don't half the time.
						if ( apply_filters( 'jetpack_bail_on_shortcode', false, $sc ) !== true ) {
							$this->shortcodes[] = $sc;
						}
					}

					// Handler name => VideoSEO service name.
					$this->video_autoembeds = array(
						'flickr'                        => 'flickr',
						'jetpack_vine'                  => 'vine',
						'wpcom_youtube_embed_crazy_url' => 'youtube',
					);
				}

				// Conditionally add VideoPress shortcodes.
				if ( class_exists( 'Jetpack_VideoPress_Shortcode' ) ) {
					$this->shortcodes[] = 'videopress';
					// Deprecated.
					$this->shortcodes[] = 'wpvideo';
				}

				// Full Oembed url as specified in plugin => VideoSEO service name.
				$this->video_oembeds['https://cloudup.com/oembed'] = 'cloudup';
			}
		}


		/**
		 * Analyse a video shortcode from the plugin for usable video information
		 *
		 * Consistency is overrated... every JetPack shortcode has different parameters... *sigh*
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

			// Let's avoid some code duplication, parameters are the same as for VideoPress plugin (also by Automattic).
			if ( $sc === 'videopress' || $sc === 'wpvideo' ) {
				$sc = 'videopress';

				if ( ! empty( $content )
					&& call_user_func( array( 'Jetpack_VideoPress_Shortcode', 'is_valid_guid' ), $content )
				) {
					$vid['id']   = $content;
					$vid['type'] = 'videopress';
					$vid         = $this->maybe_get_dimensions( $vid, $atts, true );
				}
			}
			else {
				// No dots allowed in method names, so rename.
				if ( $sc === 'blip.tv' ) {
					$sc = 'blip';
				}

				$method = 'get_' . $sc . '_info';
				if ( method_exists( $this, $method ) ) {
					$vid = $this->$method( $full_shortcode, $sc, $atts, $content );
				}
			}

			if ( $vid !== array() ) {
				$vid['type'] = $sc;
			}

			return $vid;
		}


		/**
		 * Interpret the JetPack Blip shortcode
		 * Note: does not support width/height
		 *
		 * @param  string $full_shortcode Full shortcode as found in the post content.
		 * @param  string $sc             Shortcode found.
		 * @param  array  $atts           Shortcode attributes - already decoded if needed.
		 * @param  string $content        The shortcode content, i.e. the bit between [sc]content[/sc].
		 *
		 * @return array
		 */
		protected function get_blip_info( $full_shortcode, $sc, $atts = array(), $content = '' ) {
			return $this->what_the_blip( array(), $content, $full_shortcode );
		}


		/**
		 * Interpret the JetPack dailymotion shortcode
		 * Note: does not support width/height
		 *
		 * @param  string $full_shortcode Full shortcode as found in the post content.
		 * @param  string $sc             Shortcode found.
		 * @param  array  $atts           Shortcode attributes - already decoded if needed.
		 * @param  string $content        The shortcode content, i.e. the bit between [sc]content[/sc].
		 *
		 * @return array
		 */
		protected function get_dailymotion_info( $full_shortcode, $sc, $atts = array(), $content = '' ) {
			$vid = array();

			if ( $content !== '' ) {
				$id = $content;
			}
			elseif ( isset( $atts['id'] ) && ( is_string( $atts['id'] ) && $atts['id'] !== '' ) ) {
				$id = $atts['id'];
			}

			if ( isset( $id ) ) {
				// Deal with attribute collition: [dailymotion id=x8oma9&title=2&user=3&video=4].
				$id        = explode( '&', $id );
				$vid['id'] = $id[0];
				unset( $id );
			}

			return $vid;
		}


		/**
		 * Interpret the JetPack flickr shortcode
		 *
		 * @param  string $full_shortcode Full shortcode as found in the post content.
		 * @param  string $sc             Shortcode found.
		 * @param  array  $atts           Shortcode attributes - already decoded if needed.
		 * @param  string $content        The shortcode content, i.e. the bit between [sc]content[/sc].
		 *
		 * @return array
		 */
		protected function get_flickr_info( $full_shortcode, $sc, $atts = array(), $content = '' ) {
			$vid = array();

			if ( ! empty( $atts['video'] ) ) {
				if ( strpos( $atts['video'], 'http' ) === 0 || strpos( $atts['video'], '//' ) === 0 ) {
					$vid['url'] = $atts['video'];
				}
				elseif ( $this->is_flickr_id( $atts['video'] ) ) {
					$vid['id'] = $atts['video'];
				}
			}

			if ( $vid !== array() ) {
				$vid = $this->maybe_get_dimensions( $vid, $atts, true );

				/*
				 * If no width/height set via shortcode, use the shortcode defaults
				 * as found in jetpack/modules/shortcodes/flickr.php
				 */
				if ( ! isset( $vid['width'] ) ) {
					$vid['width'] = 400;
				}
				if ( ! isset( $vid['height'] ) ) {
					$vid['height'] = 300;
				}
			}

			return $vid;
		}


		/**
		 * Interpret the JetPack googlevideo shortcode
		 * Note: does not support width/height
		 *
		 * @param  string $full_shortcode Full shortcode as found in the post content.
		 * @param  string $sc             Shortcode found.
		 * @param  array  $atts           Shortcode attributes - already decoded if needed.
		 * @param  string $content        The shortcode content, i.e. the bit between [sc]content[/sc].
		 *
		 * @return array
		 */
		protected function get_googlevideo_info( $full_shortcode, $sc, $atts = array(), $content = '' ) {
			$vid = array();

			if ( $content !== '' ) {
				$vid['url'] = $content;
			}

			return $vid;
		}


		/**
		 * Interpret the JetPack ted shortcode
		 *
		 * @param  string $full_shortcode Full shortcode as found in the post content.
		 * @param  string $sc             Shortcode found.
		 * @param  array  $atts           Shortcode attributes - already decoded if needed.
		 * @param  string $content        The shortcode content, i.e. the bit between [sc]content[/sc].
		 *
		 * @return array
		 */
		protected function get_ted_info( $full_shortcode, $sc, $atts = array(), $content = '' ) {
			$vid = array();

			if ( ! empty( $atts['id'] ) ) {
				if ( strpos( $atts['id'], 'http' ) === 0 || strpos( $atts['id'], '//' ) === 0 ) {
					$vid['url'] = $atts['id'];
				}
				elseif ( $this->is_numeric_id( $atts['id'] ) ) {
					$vid['short_id'] = $atts['id'];
				}
				else {
					$vid['id'] = $atts['id'];
				}

				$vid = $this->maybe_get_dimensions( $vid, $atts );
			}

			return $vid;
		}


		/**
		 * Interpret the JetPack vimeo shortcode
		 *
		 * @param  string $full_shortcode Full shortcode as found in the post content.
		 * @param  string $sc             Shortcode found.
		 * @param  array  $atts           Shortcode attributes - already decoded if needed.
		 * @param  string $content        The shortcode content, i.e. the bit between [sc]content[/sc].
		 *
		 * @return array
		 */
		protected function get_vimeo_info( $full_shortcode, $sc, $atts = array(), $content = '' ) {
			$vid = array();

			if ( $content !== '' ) {
				$id_or_url = $content;
			}
			elseif ( isset( $atts['id'] ) && ( is_string( $atts['id'] ) && $atts['id'] !== '' ) ) {
				$id_or_url = $atts['id'];
			}

			if ( isset( $id_or_url ) ) {
				if ( strpos( $id_or_url, 'vimeo.com' ) !== false ) {
					$vid['url'] = $id_or_url;
				}
				else {
					$vid['id'] = $id_or_url;
				}
				unset( $id_or_url );

				/**
				 * Deal with stupid width/height formats
				 * [vimeo 44633289 w=500&h=280]
				 * [vimeo 141358 h=500&w=350]
				 */
				if ( isset( $atts['w'] ) ) {
					$dim       = explode( '&', $atts['w'] );
					$atts['w'] = $dim[0];
					if ( isset( $dim[1] ) && ! empty( $dim[1] ) && empty( $atts['h'] ) ) {
						$atts['h'] = str_replace( 'h=', '', $dim[1] );
					}
				}
				if ( isset( $atts['h'] ) ) {
					$dim       = explode( '&', $atts['h'] );
					$atts['h'] = $dim[0];
					if ( isset( $dim[1] ) && ! empty( $dim[1] ) && empty( $atts['w'] ) ) {
						$atts['w'] = str_replace( 'w=', '', $dim[1] );
					}
				}
				unset( $dim );

				$vid = $this->maybe_get_dimensions( $vid, $atts, true );

				/*
				 * If no width/height set via shortcode, use the shortcode defaults
				 * as found in jetpack/modules/shortcodes/vimeo.php
				 */
				if ( ! isset( $vid['width'] ) ) {
					$vid['width'] = 400;
				}
				if ( ! isset( $vid['height'] ) ) {
					$vid['height'] = 300;
				}
			}

			return $vid;
		}


		/**
		 * Interpret the JetPack vine shortcode
		 *
		 * @param  string $full_shortcode Full shortcode as found in the post content.
		 * @param  string $sc             Shortcode found.
		 * @param  array  $atts           Shortcode attributes - already decoded if needed.
		 * @param  string $content        The shortcode content, i.e. the bit between [sc]content[/sc].
		 *
		 * @return array
		 */
		protected function get_vine_info( $full_shortcode, $sc, $atts = array(), $content = '' ) {
			$vid = array();

			if ( isset( $atts['url'] ) && ( is_string( $atts['url'] ) && $atts['url'] !== '' ) ) {
				$vid['url'] = $atts['url'];
				$vid        = $this->maybe_get_dimensions( $vid, $atts );
			}

			return $vid;
		}


		/**
		 * Interpret the JetPack youtube shortcode
		 *
		 * @param  string $full_shortcode Full shortcode as found in the post content.
		 * @param  string $sc             Shortcode found.
		 * @param  array  $atts           Shortcode attributes - already decoded if needed.
		 * @param  string $content        The shortcode content, i.e. the bit between [sc]content[/sc].
		 *
		 * @return array   An array with the usable information found or else an empty array
		 */
		public function get_youtube_info( $full_shortcode, $sc, $atts = array(), $content = '' ) {
			$vid = array();

			if ( isset( $content ) && ( is_string( $content ) && $content !== '' ) ) {
				$list       = explode( '&', $content );
				$vid['url'] = $list[0];
				unset( $list[0] );

				if ( $list !== array() ) {
					// Retrieve width/height.
					foreach ( $list as $key => $value ) {
						$value = explode( '=', $value );
						if ( in_array( $value[0], array( 'w', 'h' ), true ) ) {
							if ( ! empty( $value[1] ) ) {
								$atts[ $value[0] ] = $value[1];
							}
							unset( $list[ $key ] );
						}
					}

					$vid = $this->maybe_get_dimensions( $vid, $atts, true );

					// Any attributes left over are really url parts.. let's put them back on.
					if ( $list !== array() ) {
						$vid['url'] = $vid['url'] . '&' . implode( '&', $list );
					}
				}
			}

			return $vid;
		}
	} /* End of class */

} /* End of class-exists wrapper */
