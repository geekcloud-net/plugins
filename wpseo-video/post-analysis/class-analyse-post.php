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
 * Analyse post content for videos
 */
if ( ! class_exists( 'WPSEO_Video_Analyse_Post' ) ) {

	/**
	 * @package    WordPress\Plugins\Video-seo
	 * @subpackage Internals
	 * @since      1.8.0
	 * @version    1.8.0
	 *
	 * Find video content in posts/pages/cpts.
	 *
	 * @todo       [JRF -> Yoast] This currently stops at the first video (=old behaviour). Is this correct
	 * and as intended ? What about adding the potential secondary videos to the sitemap as well ?
	 * and what about wrapping them in schema.org info in the content ?
	 * This would impact the saving of metadata, how the metadata is presented to the user in the metabox
	 * as the user should be able to edit info on all videos (should they get a choice which is the main
	 * video to use for header info ? ), how the schema.org data is added and how the sitemap is generated.
	 * Adding the 'matched' string to the saved metadata would help with the schema.org data
	 * (str_replace on the correct video)
	 */
	class WPSEO_Video_Analyse_Post {

		/**
		 * @var bool Whether or not the DOM extension is enabled
		 */
		public static $dom_enabled = false;

		/**
		 * @var  array   Array of supported plugins to take into account when analysing a post
		 *               Format: key = class name suffix, value = plugin basename
		 *
		 * {@internal Changing the order of this array will change the priority with which the plugin is treated
		 *            The current order is based on the number of plugin downloads as stated in the
		 *            WP repository per 2014-07-25.}}
		 *
		 * {@internal To add (or remove) support for a plugin:
		 *            - Create a class file in the supported-plugins folder (see other files and template for examples).
		 *            - Add the plugin to the below list.
		 *            - Add the class file to the autoload list in video-seo.php.
		 *            - Add one of more unit test file(s) for the features supported by the plugin.
		 *            - Add the plugin to travis.yml for download via git/svn.}}
		 */
		public static $supported_plugins = array(
			'Jetpack'                            => 'jetpack/jetpack.php',
			'Smart_Youtube'                      => 'smart-youtube/smartyoutube.php',
			'Cincopa_Media'                      => 'video-playlist-and-gallery-plugin/wp-media-cincopa.php',
			'JW_Player'                          => 'jw-player-plugin-for-wordpress/jwplayermodule.php',
			'Youtube_Embed_Plus'                 => 'youtube-embed-plus/youtube.php',
			'Tubepress'                          => 'tubepress/tubepress.php',
			'Media_Element_Player'               => 'media-element-html5-video-and-audio-player/mediaelement-js-wp.php',
			'Youtube_Embed'                      => 'youtube-embed/youtube-embed.php',
			'Featured_Video_Plus'                => 'featured-video-plus/featured-video-plus.php',
			'FV_Wordpress_Flowplayer'            => 'fv-wordpress-flowplayer/flowplayer.php',
			'WP_Youtube_Lyte'                    => 'wp-youtube-lyte/wp-youtube-lyte.php',
			'WP_Video_Lightbox'                  => 'wp-video-lightbox/wp-video-lightbox.php',
			'Advanced_Responsive_Video_Embedder' => 'advanced-responsive-video-embedder/advanced-responsive-video-embedder.php',
			'Flowplayer5'                        => 'flowplayer5/flowplayer.php',
			'Ustudio'                            => 'ustudio/plugin.php',
		);

		/**
		 * @var  array  Array of active plugins - subset of the supported plugins
		 *              Format: key = class name suffix, value = object instance.
		 */
		protected static $active_plugins = array();

		/**
		 * @var  array  Array of supported shortcodes with their handler methods
		 *              Format: key = shortcode, value = array of handler methods, i.e.
		 *              if several plugins use the same shortcode, each handler method will be used in
		 *              turn.
		 */
		protected static $shortcodes = array();

		/**
		 * @var  array  Array of additional supported post_types with their handler methods
		 *              Format: key = post_type, value = array of handler methods, i.e.
		 *              if several plugins use the same post_type, each handler method will be used in
		 *              turn.
		 */
		protected static $post_types = array();

		/**
		 * @var  array  Array of additional supported custom post meta fields with their handler methods
		 *              Format: key = meta_key, value = array of handler methods, i.e.
		 *              if several plugins use the same meta_key, each handler method will be used in
		 *              turn.
		 */
		protected static $meta_keys = array();

		/**
		 * @var    array    Array of alternative protocol schemes to take into account.
		 */
		protected static $alt_protocols = array();

		/**
		 * @var    array    Array of video embeds to take into account.
		 */
		protected static $video_autoembeds = array();

		/**
		 * @var    array    Array of video Oembeds to take into account.
		 */
		protected static $video_oembeds = array();

		/**
		 * @var array   The options set for this plugin.
		 */
		protected $options = array();

		/**
		 * @var array   The video info array.
		 */
		protected $vid = array(
			'id'   => null,
			'type' => null,
			'url'  => null,
		);

		/**
		 * @var array  The video array with all the data of the previous "fetch", if available.
		 */
		protected $old_vid = array();

		/**
		 * @var string  The content of the post to analyse.
		 */
		protected $content = '';

		/**
		 * @var object  The post object for the post to analyse.
		 */
		protected $post;

		/**
		 * Use embedly as a fall back method for video detail retrieval?
		 *
		 * @var bool
		 */
		protected static $use_embedly;


		/**
		 * Initialize the class
		 *
		 * @param string $content The content to parse for videos.
		 * @param array  $vid     The video array to update.
		 * @param array  $old_vid The former video array.
		 * @param mixed  $post    The post object or the post id of the post to analyse.
		 *
		 * @return \WPSEO_Video_Analyse_Post
		 */
		public function __construct( $content, $vid, $old_vid = array(), $post = null ) {
			// Set the base properties and deal with alternative protocols.
			$this->options = get_option( 'wpseo_video' );
			$this->vid     = array_merge( $this->vid, $vid );
			$this->old_vid = $old_vid;
			$content       = apply_filters( 'wpseo_video_index_content', $content, $this->vid );

			// Deal with alternative protocols.
			if ( in_array( 'youtube::', self::$alt_protocols, true ) ) {
				// Very specific to the YouTube Embed plugin - maybe should be moved to plugin methods.
				$content = str_replace( 'youtube::', 'http://www.youtube.com/watch?v=', $content );
			}
			$this->content = str_replace( self::$alt_protocols, 'http://', $content );

			// Set up post object if needed.
			if ( ! empty( $post ) && ! is_object( $post ) ) {
				$post = get_post( $post );
			}

			if ( is_object( $post ) ) {
				$this->post = $post;
			}
			elseif ( ! empty( $GLOBALS['post'] ) ) {
				// Default to the current post - @todo probably wrong as it might be a term which is being analysed.
				$this->post = $GLOBALS['post'];
			}

			// Can we use Embedly?
			if ( ! isset( self::$use_embedly ) ) {
				// Check if we have an Embedly api key.
				if ( isset( $this->options['embedly_api_key'] ) && $this->options['embedly_api_key'] !== '' ) {
					self::$use_embedly = true;
				}
				else {
					self::$use_embedly = false;
				}
			}

			$this->analyse();
		}


		/**
		 * Reset statics
		 */
		public static function reset_statics() {
			self::$dom_enabled      = false;
			self::$active_plugins   = array();
			self::$shortcodes       = array();
			self::$post_types       = array();
			self::$meta_keys        = array();
			self::$alt_protocols    = array();
			self::$video_autoembeds = array();
			self::$video_oembeds    = array();
		}

		/**
		 * Set statics
		 */
		public static function set_statics() {
			// Reset just in case this method is called more than once (like when we're testing).
			self::reset_statics();

			if ( extension_loaded( 'dom' ) && class_exists( 'domxpath' ) ) {
				self::$dom_enabled = true;
			}

			/**
			 * Add WP core to 'active plugins' as the first (highest prio) item
			 * Add this plugin as the second
			 */
			self::$active_plugins['wp_core']  = new WPSEO_Video_Support_Core();
			self::$active_plugins['videoseo'] = new WPSEO_Video_Plugin_Yoast_Videoseo();

			/*
			 * @todo It might be better if we can figure out if the plugin is installed rather than active
			 * Also - this will give issues with plugins in the mu-plugins directory as is_plugin_active()
			 * incorrectly returns false (should be fixed in WP4 ?)
			 */
			if ( ! function_exists( 'is_plugin_active' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}
			foreach ( self::$supported_plugins as $name => $plugin_basenames ) {
				$plugin_basenames = (array) $plugin_basenames;
				foreach ( $plugin_basenames as $plugin_basename ) {
					if ( is_plugin_active( $plugin_basename ) ) {
						$classname                     = 'WPSEO_Video_Plugin_' . $name;
						self::$active_plugins[ $name ] = new $classname();
						break;
					}
				}
			}
			unset( $name, $plugin_basenames, $plugin_basename, $classname );

			// Add the plugin features for all active plugins.
			if ( is_array( self::$active_plugins ) && self::$active_plugins !== array() ) {
				foreach ( self::$active_plugins as $name => $instance ) {

					// Add known shortcodes.
					$shortcodes = $instance->get_shortcodes();
					if ( is_array( $shortcodes ) && $shortcodes !== array() ) {
						foreach ( $shortcodes as $sc ) {
							self::$shortcodes[ $sc ][] = array( $instance, 'get_info_from_shortcode' );
						}
					}
					unset( $shortcodes, $sc );

					// Add known additional post_types.
					$post_types = $instance->get_post_types();
					if ( is_array( $post_types ) && $post_types !== array() ) {
						foreach ( $post_types as $pt ) {
							self::$post_types[ $pt ][] = array( $instance, 'get_info_for_post_type' );
						}
					}
					unset( $post_types, $pt );

					// Add known additional meta_keys.
					$meta_keys = $instance->get_meta_keys();
					if ( is_array( $meta_keys ) && $meta_keys !== array() ) {
						foreach ( $meta_keys as $key ) {
							self::$meta_keys[ $key ][] = array( $instance, 'get_info_from_post_meta' );
						}
					}
					unset( $meta_keys, $key );


					// Add alternative protocols.
					$alt_protocols = $instance->get_alt_protocols();
					if ( is_array( $alt_protocols ) && $alt_protocols !== array() ) {
						self::$alt_protocols = array_unique( array_merge( self::$alt_protocols, $alt_protocols ) );
					}
					unset( $alt_protocols );


					// Add autoembed information.
					$video_autoembeds = $instance->get_video_autoembeds();
					if ( is_array( $video_autoembeds ) && $video_autoembeds !== array() ) {
						/*
						 * {@internal Merge order reversed, if there is a handler name conflict between plugins,
						 * defer to the more popular plugin which will have been added first.}}
						 */
						self::$video_autoembeds = array_unique( array_merge( $video_autoembeds, self::$video_autoembeds ) );
					}
					unset( $video_autoembeds );


					// Add oembed information.
					$video_oembeds = $instance->get_video_oembeds();
					if ( is_array( $video_oembeds ) && $video_oembeds !== array() ) {
						/*
						 * {@internal Merge order reversed, if there is a handler name conflict between plugins,
						 * defer to the more popular plugin which will have been added first.}}
						 */
						self::$video_oembeds = array_unique( array_merge( $video_oembeds, self::$video_oembeds ) );
					}
					unset( $video_oembeds );

				}
				unset( $name, $instance );
			}
		}


		/**
		 * Get the video info
		 *
		 * @return array|string        Return video array or 'none'
		 */
		public function get_vid_info() {
			if ( isset( $this->vid['content_loc'] ) || isset( $this->vid['player_loc'] ) ) {
				$this->normalize_values();
				$vid = apply_filters( 'wpseo_video_' . $this->vid['type'] . '_details', $this->vid );

				return array_filter( $vid );
			}
			else {
				return 'none';
			}
		}


		/**
		 * Make sure all duration, view_count, height and width values are integers
		 */
		protected function normalize_values() {
			$keys = array(
				'duration',
				'height',
				'view_count',
				'width',
			);
			foreach ( $keys as $key ) {
				if ( isset( $this->vid[ $key ] ) ) {
					$this->vid[ $key ] = absint( round( $this->vid[ $key ] ) );
				}
			}
		}


		/**
		 * Analyse the post for video content
		 */
		protected function analyse() {

			$methods = array(
				'get_video_from_post_type',
				'get_video_from_post_meta',
				'get_video_from_attachment',
				'get_video_from_shortcode',
				'get_video_from_auto_embeds',

				'get_video_through_old_methods',
			);

			foreach ( $methods as $method ) {
				if ( is_callable( array( $this, $method ) ) ) {
					$vid = call_user_func( array( $this, $method ) );

					// Check for video.
					if ( $vid !== array() && ( isset( $vid['content_loc'] ) || isset( $vid['player_loc'] ) ) ) {
						$this->vid = array_merge( $this->vid, $vid );
						break;
					}
				}
			}
		}


		/**
		 * Check if the current post type is a video post type and if we can find usable info through it
		 */
		protected function get_video_from_post_type() {
			$vid = array();

			if ( ( is_array( self::$post_types ) && self::$post_types !== array() ) && ( is_object( $this->post ) && isset( self::$post_types[ $this->post->post_type ] ) ) ) {
				foreach ( self::$post_types[ $this->post->post_type ] as $function ) {
					if ( is_callable( $function ) ) {
						$vid = call_user_func( $function, $this->post->ID, $this->post->post_type, $this->post );
						if ( is_array( $vid ) && $vid !== array() ) {
							$vid = $this->get_video_details( $vid );
							if ( ! empty( $vid['player_loc'] ) || ! empty( $vid['content_loc'] ) ) {
								// Stop on the first function which delivers results.
								break;
							}
							else {
								// Reset $vid if no usable info was found.
								$vid = array();
							}
						}
					}
				}
			}

			return $vid;
		}


		/**
		 * Check if any custom fields are video fields and if they contain usable info
		 */
		protected function get_video_from_post_meta() {
			$vid = array();

			if ( is_array( self::$meta_keys ) && self::$meta_keys !== array() && ! empty( $this->post->ID ) ) {

				foreach ( self::$meta_keys as $key => $callables ) {
					$meta_values = $this->get_normalized_meta_values( $key, $this->post->ID );

					if ( is_array( $meta_values ) && $meta_values !== array() ) {
						foreach ( $meta_values as $single_meta_value ) {

							foreach ( $callables as $function ) {
								if ( is_callable( $function ) ) {

									$vid = call_user_func( $function, $single_meta_value, $key, $this->post->ID );

									if ( is_array( $vid ) && $vid !== array() ) {
										$vid = $this->get_video_details( $vid );
										if ( ! empty( $vid['player_loc'] ) || ! empty( $vid['content_loc'] ) ) {
											// Stop on the first function which deliveres results.
											unset( $vid['__add_to_content'] );
											break 3;
										}
										elseif ( ! empty( $vid['__add_to_content'] ) ) {
											$this->content = $vid['__add_to_content'] . "\n" . $this->content;
											$vid           = array();
										}
										else {
											// Reset $vid if no usable info was found.
											$vid = array();
										}
									}
								}
							}
						}
					}
				}
			}

			return $vid;
		}


		/**
		 * Get post meta values to analyse for video content
		 *
		 * @param  string $key     Meta key to get the values for.
		 * @param  int    $post_id Post to get the values for.
		 *
		 * @return array  Single dimensional array with already entity normalized potentially usable meta values.
		 */
		protected function get_normalized_meta_values( $key, $post_id ) {

			$real_values = array();
			$meta_values = get_post_custom_values( $key, $post_id );

			if ( is_array( $meta_values ) && $meta_values !== array() ) {
				foreach ( $meta_values as $meta_value ) {
					$meta_value = maybe_unserialize( $meta_value );

					if ( is_scalar( $meta_value ) && ! empty( $meta_value ) ) {
						$real_values[] = $meta_value;
					}
					elseif ( is_array( $meta_value ) && $meta_value !== array() ) {
						foreach ( $meta_value as $value ) {
							if ( is_scalar( $value ) && ! empty( $value ) ) {
								$real_values[] = $value;
							}
							elseif ( is_array( $value ) && ! empty( $value[0] ) && is_scalar( $value[0] ) ) {
								/*
								 * Ignore deeper meta values which are multi-dim arrays as we really
								 * don't know what we need from it them
								 */
								$real_values[] = $value[0];
							}
						}
					}
				}
			}
			unset( $meta_values, $meta_value, $value );

			/*
			 * Silly, silly themes _encode_ the value of the post meta field. Yeah it's ridiculous.
			 * But this fixes it.
			 *
			 * ^ Helpful comment, thanks!
			 */
			$real_values = array_map( array( $this, 'normalize_entities' ), $real_values );
			$real_values = array_map( 'trim', $real_values );

			// Remove empties.
			$real_values = array_filter( $real_values );

			return $real_values;
		}


		/**
		 * Get all video attachments and see if we can find one we can use
		 */
		protected function get_video_from_attachment() {
			$vid = array();

			if ( ! empty( $this->post->ID ) ) {
				$media = get_attached_media( 'video', $this->post->ID );
				if ( is_array( $media ) && $media !== array() ) {
					foreach ( $media as $video ) {
						$vid['type']          = 'localfile';
						$vid['maybe_local']   = true;
						$vid['attachment_id'] = $video->ID;
						$vid['url']           = $video->guid;

						$vid = $this->get_video_details( $vid );
						if ( ! empty( $vid['player_loc'] ) || ! empty( $vid['content_loc'] ) ) {
							// Stop on the first video which delivers results (i.e. has a usable extension).
							break;
						}
						else {
							// Reset $vid if no usable info was found.
							$vid = array();
						}
					}
				}
			}

			return $vid;
		}


		/**
		 * Get all the shortcodes, check for any video shortcodes and see if we can parse them to useable info
		 */
		protected function get_video_from_shortcode() {
			$vid = array();

			if ( false !== strpos( $this->content, '[' ) && is_array( self::$shortcodes ) && self::$shortcodes !== array() ) {

				$old_shortcode_tags        = $GLOBALS['shortcode_tags'];
				$GLOBALS['shortcode_tags'] = self::$shortcodes; // WPCS: override ok.

				/**
				 * 1 - An extra [ to allow for escaping shortcodes with double [[]]
				 * 2 - The shortcode name
				 * 3 - The shortcode argument list
				 * 4 - The self closing /
				 * 5 - The content of a shortcode when it wraps some content.
				 * 6 - An extra ] to allow for escaping shortcodes with double [[]]
				 */
				if ( preg_match_all( '/' . get_shortcode_regex() . '/s', $this->content, $matches, PREG_SET_ORDER ) ) {

					foreach ( $matches as $match ) {
						// No need to do anything is it's an escaped shortcode.
						if ( $match[1] !== '[' && $match[6] !== ']' ) {
							$full       = $match[0];
							$tag        = trim( $match[2] );
							$sc_content = $match[5];
							$atts       = shortcode_parse_atts( $match[3] );

							// Handle WordPress.com shortcode format.
							if ( isset( $atts[0] ) && $sc_content === '' ) {
								$atts       = $this->fix_sc_attributes( $atts );
								$sc_content = trim( $atts[0] );
								unset( $atts[0] );
							}

							$sc_content = $this->normalize_entities( $sc_content );
							if ( is_array( $atts ) && $atts !== array() ) {
								$atts = array_map( array( $this, 'normalize_entities' ), $atts );
							}

							$thumb = '';
							if ( isset( $atts['image'] ) && ( is_string( $atts['image'] ) && $atts['images'] !== '' ) ) {
								$thumb = $atts['image'];
							}

							foreach ( self::$shortcodes[ $tag ] as $function ) {
								if ( is_callable( $function ) ) {
									$vid = call_user_func( $function, $full, $tag, $atts, $sc_content );
									if ( is_array( $vid ) && $vid !== array() ) {
										if ( ! isset( $vid['thumbnail_loc'] ) && $thumb !== '' ) {
											$vid['thumbnail_loc'] = $thumb;
										}

										$vid = $this->get_video_details( $vid );
										if ( ! empty( $vid['player_loc'] ) || ! empty( $vid['content_loc'] ) ) {
											// Stop on the first function which delivers results.
											break 2;
										}
										elseif ( ! empty( $vid['__add_to_content'] ) ) {
											$this->content = $vid['__add_to_content'] . "\n" . $this->content;
											$vid           = array();
										}
										else {
											// Reset $vid if no usable info was found.
											$vid = array();
										}
									}
								}
							}
						}
					}
				}

				$GLOBALS['shortcode_tags'] = $old_shortcode_tags; // WPCS: override ok.
			}

			return $vid;
		}


		/**
		 * Grab all urls which are on their own line and check if any are registered video urls
		 * and if so, grab usable info
		 */
		protected function get_video_from_auto_embeds() {
			$vid = array();

			/*
			 * Get all the embeddable urls
			 * Use the same regex as in WP_Embed::autoembed()
			 */
			if ( preg_match_all( '`^(?:\s*)(https?://[^\s<>"]+)(?:\s*)$`im', $this->content, $matches, PREG_PATTERN_ORDER ) ) {
				// Only interested in the real url matches.
				$urls = $matches[1];

				foreach ( $urls as $url ) {
					// Follow WP.
					$url = str_replace( '&amp;', '&', $url );

					if ( ! empty( $GLOBALS['wp_embed']->handlers ) && is_array( $GLOBALS['wp_embed']->handlers ) ) {
						// Go through the embed handlers.
						foreach ( $GLOBALS['wp_embed']->handlers as $handler_array ) {
							foreach ( $handler_array as $name => $details ) {
								if ( isset( self::$video_autoembeds[ $name ] ) && preg_match( $details['regex'], $url ) ) {
									$vid['url'] = $url;
									if ( self::$video_autoembeds[ $name ] !== '' ) {
										$vid['type'] = self::$video_autoembeds[ $name ];
									}

									$vid = $this->get_video_details( $vid );
									if ( ! empty( $vid['player_loc'] ) || ! empty( $vid['content_loc'] ) ) {
										// Stop on the first function which delivers results.
										break 3;
									}
									else {
										// Reset $vid if no usable info was found.
										$vid = array();
									}
								}
							}
							unset( $name, $details );
						}
						unset( $handler_array );
					}

					/*
					 * Go through the Oembed handlers
					 */
					$oembed      = _wp_oembed_get_object();
					$providerurl = $oembed->get_provider( $url, array( 'discover' => false ) );
					if ( ( is_string( $providerurl ) && $providerurl !== '' ) && ! empty( self::$video_oembeds ) ) {
						foreach ( self::$video_oembeds as $partial_url => $service ) {
							if ( stripos( $providerurl, $partial_url ) !== false ) {
								$vid['url'] = $url;
								if ( $service !== '' ) {
									$vid['type'] = $service;
								}

								$vid = $this->get_video_details( $vid );
								if ( ! empty( $vid['player_loc'] ) || ! empty( $vid['content_loc'] ) ) {
									// Stop on the first function which delivers results.
									break 2;
								}
								else {
									// Reset $vid if no usable info was found.
									$vid = array();
								}
							}
						}
					}
				}
			}

			return $vid;
		}


		/**
		 * Parse the content of a post or term description.
		 *
		 * {@internal Stripped version of the old function.}}
		 *
		 * @since    1.3
		 *
		 * @return array $vid
		 */
		protected function get_video_through_old_methods() {
			$content = $this->content;
			$vid     = array();

			if ( preg_match( '`(<video.*</video>)`s', $content, $html5vid ) ) {

				if ( preg_match( '`src=([\'"])(.*?)\.(' . WPSEO_Video_Sitemap::$video_ext_pattern . ')\1`', $html5vid[1], $content_loc ) ) {
					$vid['content_loc'] = $content_loc[2] . '.' . $content_loc[3];
					$vid['maybe_local'] = true;

					if ( preg_match( '`poster=([\'"])([^\'"\s]+)\1`', $html5vid[1], $thumbnail_loc ) ) {
						$vid['thumbnail_loc'] = $thumbnail_loc[2];
					}

					$vid['type'] = 'html5vid';

					return $this->get_video_details( $vid );
				}
			}

			$vid = $this->get_wistia_video_through_old_methods( $content );

			if ( isset( $vid['content_loc'] ) || isset( $vid['player_loc'] ) ) {
				return $vid;
			}
			else {
				// Reset vid.
				$vid = array();
			}


			$oembed = $this->grab_embeddable_urls_xpath( $content );
			if ( is_array( $oembed ) && $oembed !== array() ) {

				foreach ( $oembed as $url ) {
					$vid['url'] = $url;
					$vid        = $this->get_video_details( $vid );

					if ( isset( $vid['content_loc'] ) || isset( $vid['player_loc'] ) ) {
						return $vid;
					}
					else {
						// Reset vid.
						$vid = array();
					}
				}
			}
			unset( $oembed );


			$oembed = $this->grab_embeddable_urls( $content );
			if ( is_array( $oembed ) && $oembed !== array() ) {

				foreach ( $oembed as $url ) {
					$vid['url'] = $url;
					$vid        = $this->get_video_details( $vid );

					if ( isset( $vid['content_loc'] ) || isset( $vid['player_loc'] ) ) {
						return $vid;
					}
					else {
						// Reset vid.
						$vid = array();
					}
				}
			}
			unset( $oembed );


			return $vid;
		}


		/**
		 * Analyse post content for typical Wistia embed codes.
		 *
		 * @see https://wistia.com/doc/embedding
		 *
		 * @since 3.9
		 *
		 * @param string $content Post content.
		 *
		 * @return array Video info array or empty array if no wistia video was matched.
		 */
		protected function get_wistia_video_through_old_methods( $content ) {
			$vid = array();

			if ( preg_match( '`<(?:div|span)(?: [a-z]+=\S+)* class=(?:[\'"])wistia_embed wistia_async_([^\'"\s]+)`', $content, $matches ) ) {
				$vid['id']   = $matches[1];
				$vid['type'] = 'wistia';
				$vid         = $this->get_video_details( $vid );
			}
			elseif ( preg_match( '`<div id=([\'"])wistia_([^\'"\s]+)\1 class=([\'"])wistia_embed[^\'"]*\3`', $content, $matches ) ) {
				$vid['id']   = $matches[2];
				$vid['type'] = 'wistia';
				$vid         = $this->get_video_details( $vid );
			}
			elseif ( preg_match( '`<a(?:.*?)href="(?:http[s]?:)?//fast\.wistia\.(?:com|net)/embed/iframe/([^\?]+)\?`', $content, $matches ) ) {
				$vid['id']   = $matches[1];
				$vid['type'] = 'wistia';
				$vid         = $this->get_video_details( $vid );
			}

			return $vid;
		}


		/**
		 * Checks whether there are oembed URLs in the post that should be included in the video sitemap.
		 *
		 * {@internal Look at WP native function `get_media_embedded_in_content( $content, $types = null )`.}}
		 *
		 * @since    0.1
		 *
		 * @param string $content the content of the post.
		 *
		 * @return array|boolean returns array $urls with type of video as array key and video URL as content, or false on negative
		 */
		protected function grab_embeddable_urls( $content ) {
			$options      = $this->options;
			$evs_location = get_option( 'evs_location' );

			// Catch both the single line embeds as well as the embeds using the [embed] shortcode.
			preg_match_all( '`\[embed(?:[^\]]+)?\](http[s]?://[^\s"]+)\s*\[/embed\]`im', $content, $matches );
			preg_match_all( '`^\s*(?:<p>)?(http[s]?://[^\s"]+)\s*$`im', $content, $matches2 );

			$matched_urls = array();
			if ( isset( $matches[1] ) && is_array( $matches[1] ) ) {
				$matched_urls = array_merge( $matched_urls, $matches[1] );
			}
			if ( isset( $matches2[1] ) && is_array( $matches2[1] ) ) {
				$matched_urls = array_merge( $matched_urls, $matches2[1] );
			}

			if ( preg_match_all( '`(<iframe.*</iframe>)`s', $content, $iframes, PREG_SET_ORDER ) ) {
				foreach ( $iframes as $iframe ) {
					if ( preg_match( '`src=([\'"])([^\s\'"]+)\1`', $iframe[1], $iframesrc ) ) {
						$matched_urls[] = $iframesrc[2];
					}
				}
			}

			if ( preg_match_all( '`(<object.*</object>)`s', $content, $objects, PREG_SET_ORDER ) ) {
				foreach ( $objects as $object ) {
					if ( preg_match( '`<param name=([\'"])src\1 value=([\'"])([^\s\'"]+)\2`', $object[1], $srcmatch ) ) {
						$matched_urls[] = $srcmatch[3];
					}
					elseif ( preg_match( '`<param name=([\'"])movie\1 value=([\'"])([^\s\'"]+)\2`', $object[1], $moviematch ) ) {
						$matched_urls[] = $moviematch[3];
					}
				}
			}

			if ( preg_match( '`<a href=([\'"])(http[s]?://(?:www\.)?(?:youtube|vimeo)\.com/[^\s\'"]*)\1 rel=([\'"])wp-video-lightbox\3`', $content, $matches ) ) {
				$matched_urls[] = $matches[2];
			}

			if ( preg_match( '`<a class=([\'"])youtubepop\1 href=([\'"])(http[s]?://(?:www\.)?(?:youtube|vimeo)\.com/[^\s\'"]*)\2>`', $content, $matches ) ) {
				$matched_urls[] = $matches[3];
			}

			$wistia_info = array( 'domain' => 'wistia.com' );
			if ( $options['wistia_domain'] !== '' ) {
				$wistia_info = $this->parse_url( $options['wistia_domain'] );
			}

			$evs_info = $this->parse_url( $evs_location );
			if ( ! isset( $evs_info ) || ! isset( $evs_info['domain'] ) ) {
				$evs_info = array( 'domain' => 'easyvideosuite.com' );
			}


			if ( count( $matched_urls ) > 0 ) {
				$urls = array();

				foreach ( $matched_urls as $match ) {
					$url_info = $this->parse_url( $match );
					if ( ! isset( $url_info['domain'] ) ) {
						continue;
					}

					switch ( $url_info['domain'] ) {
						case 'brightcove.com':
							if ( preg_match( '`<param name="flashVars" value="playerID=(\d+)`', $content, $bcmatch ) ) {
								$urls['brightcove'] = $bcmatch[1];
							}

							if ( preg_match( '`<param name="flashVars" value="videoId=(\d+)`', $content, $bcmatch ) ) {
								$urls[] = $bcmatch[1];
							}
							break;

						case '23video.com':
						case 'animoto.com':
						case 'video214.com':
						case 'archive.org':
						case 'blip.tv':
						case 'blip.com':
						case 'cincopa.com':
						case 'collegehumor.com':
						case 'dailymotion.com':
						case 'dai.ly':
						case 'easyvideosuite.com':
						case 'embed.ly':
						case 'embedly.com':
						case 'flickr.com':
						case 'flic.kr':
						case 'ifilm.com':
						case 'funnyordie.com':
						case 'hulu.com':
						case 'metacafe.com':
						case 'muzu.tv':
						case 'revision3.com':
						case 'screencast.com':
						case 'screenr.com':
						case 'snotr.com':
						case 'spike.com':
						case 'ted.com':
						case 'ted.org':
						case 'ustudio.com':
						case 'veoh.com':
						case 'viddler.com':
						case 'videojug.com':
						case 'vidyard.com':
						case 'vimeo.com':
						case 'vine.co':
						case $wistia_info['domain']:
						case 'wistia.com':
						case 'wistia.net':
						case 'wi.st':
						case 'wordpress.tv':
						case 'youtu.be':
						case 'youtube.com':
						case 'youtube-nocookie.com':
						case $evs_info['domain']:
							$urls[] = $match;
							break;
					}
				}

				if ( count( $urls ) > 0 ) {
					return $urls;
				}
				else {
					return false;
				}
			}
			else {
				return false;
			}
		}


		/**
		 * Checks whether there are oembed URLs in the post that should be included in the video sitemap.
		 * Uses DOMDocument and XPath to parse the content for urls instead of preg matches.
		 *
		 * @since 1.5.4.4
		 *
		 * @param string $content the content of the post.
		 *
		 * @return array|boolean returns array $urls with type of video as array key and video URL as content, or false on negative
		 */
		protected function grab_embeddable_urls_xpath( $content ) {
			if ( ( ! is_string( $content ) || trim( $content ) === '' ) || self::$dom_enabled === false ) {
				return false;
			}

			$dom = new DOMDocument();
			@$dom->loadHTML( $content );
			$xpath = new DOMXPath( $dom );

			$matched_urls = array();

			// For object embeds (i.e screencast.com).
			$objects = $xpath->query( '//object/param[@name="movie"] | //object/param[@name="src"]' );
			if ( is_object( $objects ) && $objects->length > 0 ) {
				foreach ( $objects as $object ) {
					$value          = $object->getAttribute( 'value' );
					$matched_urls[] = $value;
				}
			}
			unset( $objects, $object, $value );

			// For iframe embeds (i.e. vidyard.com).
			$iframes = $xpath->query( '//iframe' );
			if ( is_object( $iframes ) && $iframes->length > 0 ) {
				foreach ( $iframes as $iframe ) {
					$src            = $iframe->getAttribute( 'src' );
					$matched_urls[] = $src;
				}
			}
			unset( $iframes, $iframe, $src );

			// Specific check for vidyard embed with javascript and lightbox.
			$script = $xpath->query( '//script[contains(@src,"play.vidyard.com")]' );
			if ( is_object( $script ) && $script->length > 0 ) {
				foreach ( $script as $element ) {
					$src            = $element->getAttribute( 'src' );
					$matched_urls[] = $src;
				}
			}
			unset( $script, $element, $src );

			// Specific check for cincopa embed via javascript.
			$script = $xpath->query( '//script/text()[contains(.,"cp_load_widget")]' );
			if ( is_object( $script ) && $script->length > 0 ) {
				foreach ( $script as $element ) {
					// Remove CDATA.
					$src            = preg_replace( '`//\s*?<!\[CDATA\[\s*|\s*//\s*\]\]>`', '', $element->wholeText );
					$src            = 'http://cincopa.com?' . $src;
					$matched_urls[] = $src;
				}
			}
			unset( $script, $element, $src );

			// Specific check for brightcove.
			$script = $xpath->query( '//object/param[contains(@value,"brightcove.com")]/following-sibling::param[@name="flashVars"]' );
			if ( is_object( $script ) && $script->length > 0 ) {
				foreach ( $script as $element ) {
					$src            = $element->getAttribute( 'value' );
					$src            = 'http://brightcove.com?' . $src;
					$matched_urls[] = $src;
				}
			}
			unset( $script, $element, $src );

			// Specific check for screenr.
			$script = $xpath->query( '//object/param[contains(@value,"screenr.com")]/following-sibling::param[@name="flashvars"]' );
			if ( is_object( $script ) && $script->length > 0 ) {
				foreach ( $script as $element ) {
					$flashvars = $element->getAttribute( 'value' );
					if ( preg_match( '`<iframe src=(&quot;|["\'])(.+?)\1`', $flashvars, $match ) ) {
						$matched_urls[] = $match[2];
					}
				}
			}
			unset( $script, $element, $flashvars );

			// Specific check for veoh.
			$script = $xpath->query( '//object[@name="veohFlashPlayer"]/param[@name="movie"]' );
			if ( is_object( $script ) && $script->length > 0 ) {
				foreach ( $script as $element ) {
					$value = $element->getAttribute( 'value' );
					$value = self::wp_parse_url( $value );
					parse_str( $value['query'], $query );
					if ( ! empty( $query['permalinkId'] ) ) {
						$matched_urls[] = 'http://www.veoh.com/watch/' . $query['permalinkId'];
					}
				}
			}
			unset( $script, $element, $value, $query );

			// Specific check for 23video.
			$link = $xpath->query( '//a[contains(@href,"23video.com")]/img[contains(@src,"23video.com")]/following-sibling::div/..' );
			if ( is_object( $link ) && $link->length > 0 ) {
				foreach ( $link as $element ) {
					$matched_urls[] = $element->getAttribute( 'href' );
				}
			}
			unset( $link, $element );

			if ( count( $matched_urls ) > 0 ) {
				$urls = array();

				foreach ( $matched_urls as $match ) {
					$url_info = $this->parse_url( $match );
					if ( ! isset( $url_info['domain'] ) ) {
						continue;
					}

					switch ( $url_info['domain'] ) {
						/*
						 * work around for screencast.com b/c there's no connection between url and the embed code
						 * @todo JRF -> this ought to be changed so that the $vid['url'] value is a normal value and we add another key for passing the content
						 */
						case 'screencast.com':
							$urls['screencast']['url']   = $match;
							$urls['screencast']['embed'] = $content;
							break;

						case '23video.com':
						case 'animoto.com':
						case 'video214.com':
						case 'brightcove.com':
						case 'cincopa.com':
						case 'screenr.com':
						case 'veoh.com':
						case 'vidyard.com':
							$urls[] = $match;
							break;
					}
				}

				if ( count( $urls ) > 0 ) {
					return $urls;
				}
				else {
					return false;
				}
			}
			else {
				return false;
			}
		}


		/**
		 * Retrieve video details
		 *
		 * @since      1.7.0
		 * @see        WPSEO_Video_Details
		 *
		 * @param  array $vid A potential video array with all the data.
		 *
		 * @todo       - Should be changed to visibility protected, but that would cause fatal
		 * errors with the fall-back for deprecated methods. Let's wait a few versions.
		 *
		 * @return array $vid
		 */
		public function get_video_details( $vid ) {
			$vid = $this->verify_service_type( $vid );

			// Make sure we don't lose an updated title, description or publication date.
			$vid = array_merge( $this->vid, $vid );

			$class = 'unknown';
			if ( isset( $vid['type'] ) ) {
				$class = 'WPSEO_Video_Details_' . ucfirst( $vid['type'] );
			}

			if ( class_exists( $class ) ) {
				$video = new $class( $vid, $this->old_vid );
				$vid   = $video->get_details();
			}
			elseif ( isset( $vid['maybe_local'] ) && $vid['maybe_local'] === true ) {
				$video = new WPSEO_Video_Details_Localfile( $vid, $this->old_vid );
				$vid   = $video->get_details();
			}

			// Alternatively try to get details via embedly.
			if ( ( empty( $vid['content_loc'] ) && empty( $vid['player_loc'] ) ) && ( self::$use_embedly === true && WPSEO_Video_Details_Embedly::$functional !== false ) && ( ! empty( $vid['url'] ) ) ) {
				$video = new WPSEO_Video_Details_Embedly( $vid, $this->old_vid );
				$vid   = $video->get_details();

			}

			return $vid;
		}


		/**
		 * Verify the service type based on the url - work around user error and shortcodes without type indication.
		 * Also: check if the url might be local to use the local detail retrieval as fall back.
		 *
		 * @todo Add Videopress domains to the switches
		 *
		 * @param  array $vid A potential video array with all the data.
		 *
		 * @return array $vid
		 */
		protected function verify_service_type( $vid ) {
			static $site_host;
			static $site_domain;
			static $network_host;
			static $network_domain;
			static $wistia_host;
			static $evs_domain;

			$type = '';

			// Get the url we're going to use.
			$url = '';
			if ( isset( $vid['url'] ) && ( is_string( $vid['url'] ) && $vid['url'] !== '' ) ) {
				$url = $vid['url'];
			}
			// (Temporary) Work around for screencast - @todo - get rid of the array in url.
			elseif ( isset( $vid['url'] ) && ( is_array( $vid['url'] ) && isset( $vid['url']['url'] ) && $vid['url']['url'] !== '' ) ) {
				$url = $vid['url']['url'];
			}


			/* Test the url against the known domains */
			if ( $url !== '' ) {

				// Set the local domain statics if not done before.
				if ( ! isset( $site_host ) && ! isset( $site_domain ) ) {
					$site        = $this->parse_url( site_url() );
					$site_host   = $site['host'];
					$site_domain = $site['domain'];
					unset( $site );
				}

				if ( ! isset( $network_host ) && ! isset( $network_domain ) ) {
					$network_host   = '';
					$network_domain = '';
					if ( is_multisite() ) {
						$network        = $this->parse_url( network_site_url() );
						$network_host   = $network['host'];
						$network_domain = $network['domain'];
						unset( $network );
					}
				}


				// Ok, we have a url, let's see if it's typed properly.
				$parsed_url = $this->parse_url( $url );

				// Only go through all the switches if we need to - prevents overhead in 80% of the cases.
				if ( ! isset( $vid['type'] ) || $parsed_url['domainname'] !== $vid['type'] ) {

					// Set the rest of the statics if not done before.
					if ( ! isset( $wistia_host ) ) {
						$wistia_host = '';
						if ( $this->options['wistia_domain'] !== '' ) {
							$wistia_host = 'http://' . $this->options['wistia_domain'];
							$wistia_host = $this->parse_url( $wistia_host, 'host' );
						}
					}

					// @todo Verify what the value of evs_location could be to make sure this will work.
					if ( ! isset( $evs_domain ) ) {
						$evs_domain   = 'easyvideosuite.com';
						$evs_location = get_option( 'evs_location' );
						if ( is_string( $evs_location ) && $evs_location !== '' ) {
							$evs_domain = $this->parse_url( $evs_location, 'domain' );
						}
						unset( $evs_location );
					}


					// Full hostname: www.test.com.
					switch ( $parsed_url['host'] ) {
						case $wistia_host:
							$type = 'wistia';
							break;

						case 'v.wordpress.com':
							$type = 'wordpresstv';
							break;
					}

					/*
					 * Only domain: 'test' in www.test.com
					 * Used for domains which have a wide range of tld registrations, think youtube.nl, youtube.de etc
					 */
					if ( $type === '' ) {
						switch ( $parsed_url['domainname'] ) {
							case 'youtube':
								$type = $parsed_url['domainname'];
								break;
						}
					}

					/* Domain: 'test.com' in www.test.com */
					if ( $type === '' ) {
						switch ( $parsed_url['domain'] ) {
							case 'dai.ly':
								$type = 'dailymotion';
								break;

							case 'flic.kr':
								$type = 'flickr';
								break;

							case 'wi.st':
								$type = 'wistia';
								break;

							case 'youtu.be':
							case 'youtube-nocookie.com':
								$type = 'youtube';
								break;

							case $evs_domain:
							case 'easyvideosuite.com':
								$type = 'evs';
								break;

							case 'video214.com':
								$type = 'animoto';
								break;

							case 'ifilm.com':
								$type = 'spike';
								break;

							case 'archive.org':
							case 'embed.ly':
							case 'muzu.tv':
							case 'wordpress.tv':
								$type = str_replace( '.', '', $parsed_url['domain'] );
								break;

							case '23video.com':
							case 'animoto.com':
							case 'blip.tv':
							case 'blip.com':
							case 'brightcove.com':
							case 'cincopa.com':
							case 'collegehumor.com':
							case 'dailymotion.com':
							case 'embedly.com':
							case 'flickr.com':
							case 'funnyordie.com':
							case 'hulu.com':
							case 'metacafe.com':
							case 'revision3.com':
							case 'screencast.com':
							case 'screenr.com':
							case 'snotr.com':
							case 'spike.com':
							case 'ted.com':
							case 'ted.org':
							case 'ustudio.com':
							case 'veoh.com':
							case 'viddler.com':
							case 'videojug.com':
							case 'vidyard.com':
							case 'vimeo.com':
							case 'vine.co':
							case 'wistia.com':
							case 'wistia.net':
								$type = $parsed_url['domainname'];
								break;
						}
					}
				}

				// Add the 'maybe_local' key if potentially needed.
				if ( ( $parsed_url['host'] === $network_host || $parsed_url['host'] === $site_host ) || ( $parsed_url['domain'] === $site_domain || $parsed_url['domain'] === $network_domain ) ) {
					$vid['maybe_local'] = true;
				}
			}

			if ( $type !== '' ) {
				$vid['type'] = $type;
			}
			/* If we're using the old type, make sure it's not in a non-usable format */
			elseif ( isset( $vid['type'] ) ) {
				$vid['type'] = $this->correct_faulty_service_types( $vid['type'] );
			}

			return $vid;
		}


		/**
		 *    If we have a service type make sure it's not in a non-usable format
		 *
		 * @param  string $type Service type.
		 *
		 * @return string
		 */
		private function correct_faulty_service_types( $type ) {
			if ( is_string( $type ) && $type !== '' ) {
				switch ( $type ) {
					case 'archive.org':
						$type = 'archiveorg';
						break;

					case 'blip.tv':
					case 'bliptv':
						$type = 'blip';
						break;

					case 'muzu.tv':
					case 'muzu':
						$type = 'muzutv';
						break;

					case 'wordpress.tv':
						$type = 'wordpresstv';
						break;
				}
			}

			return $type;
		}


		/**
		 * Fix no-name attributes, i.e. [video=http://.....] syntax
		 * Inspired by Viper video Quicktags
		 *
		 * @param  array $atts Shortcode attributes.
		 *
		 * @return array
		 */
		protected function fix_sc_attributes( $atts = array() ) {
			if ( isset( $atts[0] ) && ! empty( $atts[0] ) ) {
				$atts[0] = ltrim( $atts[0], '="\'' );
				$atts[0] = rtrim( $atts[0], '"\'' );
			}

			return $atts;
		}


		/**
		 * Html entity decoding for shortcode attributes and post meta values
		 * - Will first change invalid entities to valid ones - &#000058 -> &#58;
		 * - Then change named ones to numeric ones
		 * - Then decode them all to their normal characters
		 * - And remove any surrounding whitespace
		 *
		 * @param string $string Arbitrary string.
		 *
		 * @return string
		 */
		protected function normalize_entities( $string ) {
			return trim( wp_kses_decode_entities( ent2ncr( wp_kses_normalize_entities( $string ) ) ) );
		}


		/**
		 * Parse a url to its components in a largely cross-PHP consistent manner.
		 *
		 * {@internal The WP version was introduced in WP 4.4.0 and will started
		 * supporting the second argument "component" in WP 4.7.0.}}
		 *
		 * @link https://developer.wordpress.org/reference/functions/wp_parse_url/
		 * @link http://php.net/manual/en/function.parse-url.php
		 * @link https://core.trac.wordpress.org/ticket/36356
		 *
		 * @since 3.8.0
		 * @since 6.3.0 Now always falls through to the WP function as the minimum
		 *              required WP version is now > 4.6.
		 *
		 * @param string $url       The url to parse.
		 * @param int    $component One of the predefined PHP url component constants.
		 *
		 * @return array|string|int|null|false False for seriously malformed urls.
		 *                                     Array of components if no specific
		 *                                     component was requested.
		 *                                     String if a component was requested and
		 *                                     available in the parsed url.
		 *                                     Int if the requested component was 'port'.
		 *                                     Null if the requested component was not
		 *                                     in the url.
		 */
		public static function wp_parse_url( $url, $component = -1 ) {
			return wp_parse_url( $url, $component );
		}


		/**
		 * Parse a URL and find the host name and more.
		 *
		 * {@internal Used to be regex based, but the regex was buggy in a few places, most notably it
		 * failed on:
		 * - protocol independent urls
		 * - arguments in array syntax
		 * - query starting with & not ?
		 * This version deals with all those cases too and is compatible with the expected array return elements
		 * from both the old function as well as the php native parse_url function.}}
		 *
		 * @since    1.1
		 *
		 * @param string     $url       The URL to parse.
		 * @param string|int $component (optional) The specific component to return, either the component
		 *                              key or one of the PHP Native PHP_URL_* constants.
		 *
		 * @return array|string  An array of url parts or the string value of one specific part which could
		 *                       be an empty string
		 */
		public static function parse_url( $url, $component = -1 ) {
			$defaults = array(
				'scheme'     => '',
				'user'       => '',
				'login'      => '',
				'pass'       => '',
				'host'       => '',
				'subdomain'  => '',
				'domain'     => '',
				'domainname' => '',
				'extension'  => '',
				'port'       => '',
				'path'       => '',
				'file'       => '',
				'query'      => '',
				'arg'        => '',
				'fragment'   => '',
				'anchor'     => '',
			);

			if ( strpos( $url, '//' ) === 0 ) {
				// Work around php pre5.4.17 bug for protocol independent urls.
				$url = 'http:' . $url;
			}

			$parsed_url = $defaults;
			if ( strpos( $url, '/' ) !== 0 ) {
				// This function is not meant for relative urls.
				$parsed_url = self::wp_parse_url( $url );
				if ( is_array( $parsed_url ) && $parsed_url !== array() ) {
					$parsed_url = array_merge( $defaults, $parsed_url );

					if ( $parsed_url['host'] !== '' ) {
						$host                     = explode( '.', $parsed_url['host'] );
						$parsed_url['extension']  = array_pop( $host );
						$parsed_url['domainname'] = array_pop( $host );
						$parsed_url['domain']     = implode( '.', array(
							$parsed_url['domainname'],
							$parsed_url['extension'],
						) );
						$parsed_url['subdomain']  = implode( '.', $host );
					}
					if ( $parsed_url['path'] !== '' && strrpos( $parsed_url['path'], '/' ) !== ( strlen( $parsed_url['path'] ) - 1 ) ) {
						$file               = explode( '/', $parsed_url['path'] );
						$parsed_url['file'] = array_pop( $file );

						if ( strpos( $parsed_url['file'], '&' ) !== false && $parsed_url['query'] === '' ) {
							$parsed_url['query'] = substr( self::stristr( $parsed_url['file'], '&' ), 1 );
							$parsed_url['file']  = self::stristr( $parsed_url['file'], '&', true );
							$parsed_url['path']  = str_replace( '&' . $parsed_url['query'], '', $parsed_url['path'] );
						}
					}

					// Compatibility with the array keys of the previously used regex based function.
					$parsed_url['login']  = $parsed_url['user'];
					$parsed_url['arg']    = $parsed_url['query'];
					$parsed_url['anchor'] = $parsed_url['fragment'];
				}
				else {
					$parsed_url = $defaults;
				}
			}

			// Maybe translate component constract to key name.
			if ( $component !== -1 && ! is_string( $component ) ) {
				$component = self::translate_php_url_constant_to_key( $component );
			}

			if ( is_string( $component ) ) {
				if ( isset( $parsed_url[ $component ] ) ) {
					return $parsed_url[ $component ];
				}
				else {
					return '';
				}
			}
			else {
				return $parsed_url;
			}
		}


		/**
		 * Translate a PHP_URL_* constant to the named array keys we use
		 *
		 * @param  int $constant PHP_URL_* constant.
		 *
		 * @return string|bool   The named key or false
		 */
		private static function translate_php_url_constant_to_key( $constant ) {
			$translation = array(
				0 => 'scheme',
				1 => 'host',
				2 => 'port',
				3 => 'user',
				4 => 'pass',
				5 => 'path',
				6 => 'query',
				7 => 'fragment',
			);

			if ( isset( $translation[ $constant ] ) ) {
				return $translation[ $constant ];
			}
			else {
				return false;
			}
		}


		/**
		 * Strstr PHP 5.2 compatibility
		 *
		 * @see http://php.net/strstr
		 *
		 * @param string $haystack      Text to search.
		 * @param mixed  $needle        Needle to find.
		 * @param bool   $before_needle Before needle.
		 *
		 * @return string|bool Returns the matched substring or false if needle is not found
		 */
		public static function strstr( $haystack, $needle, $before_needle = false ) {
			if ( version_compare( PHP_VERSION, '5.3.0', '>=' ) ) {
				return strstr( $haystack, $needle, $before_needle );
			}
			else {
				if ( $before_needle === false ) {
					return strstr( $haystack, $needle );
				}
				else {
					$pos = strpos( $haystack, $needle );
					if ( $pos !== false ) {
						return substr( $haystack, 0, $pos );
					}
					else {
						return false;
					}
				}
			}
		}


		/**
		 * Stristr PHP 5.2 compatibility
		 *
		 * @see http://php.net/stristr
		 *
		 * @param string $haystack      Text to search.
		 * @param mixed  $needle        Needle to find.
		 * @param bool   $before_needle Before needle.
		 *
		 * @return string|bool Returns the matched substring or false if needle is not found
		 */
		public static function stristr( $haystack, $needle, $before_needle = false ) {
			if ( version_compare( PHP_VERSION, '5.3.0', '>=' ) ) {
				return stristr( $haystack, $needle, $before_needle );
			}
			else {
				if ( $before_needle === false ) {
					return stristr( $haystack, $needle );
				}
				else {
					$pos = stripos( $haystack, $needle );
					if ( $pos !== false ) {
						return substr( $haystack, 0, $pos );
					}
					else {
						return false;
					}
				}
			}
		}
	} /* End of class */


	/**
	 * Setup the class statics on file load
	 */
	WPSEO_Video_Analyse_Post::set_statics();

} /* End of class-exists wrapper */
