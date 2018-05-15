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
 * Add support for the Advanced Responsive Video Embedder plugin
 *
 * @see      https://wordpress.org/plugins/advanced-responsive-video-embedder/
 *
 * {@internal Last update: October 2016 based upon v 7.5.1.}}
 *
 * Shortcode list from plugin:
 *   'shortcodes'            => array(
 *       'allmyvideos'            => 'allmyvideos',
 *       'alugha'                 => 'alugha',
 *       'archiveorg'             => 'archiveorg',
 *       'break'                  => 'break',
 *       'collegehumor'           => 'collegehumor',
 *       'comedycentral'          => 'comedycentral',
 *       'dailymotion'            => 'dailymotion',
 *       'dailymotionlist'        => 'dailymotionlist', // should not be recognized
 *       'facebook'               => 'facebook',
 *       'funnyordie'             => 'funnyordie',
 *       'iframe'                 => 'iframe',
 *       'ign'                    => 'ign',
 *       'kickstarter'            => 'kickstarter',
 *       'liveleak'               => 'liveleak',
 *       'livestream'             => 'livestream',
 *       'klatv'                  => 'klatv',
 *       'metacafe'               => 'metacafe',
 *       'movieweb'               => 'movieweb',
 *       'mpora'                  => 'mpora',
 *       'myspace'                => 'myspace',
 *       'snotr'                  => 'snotr',
 *       'spike'                  => 'spike',
 *       'ted'                    => 'ted',
 *       'twitch'                 => 'twitch',
 *       'ustream'                => 'ustream',
 *       'veoh'                   => 'veoh',
 *       'vevo'                   => 'vevo',
 *       'viddler'                => 'viddler',
 *       'vidspot'                => 'vidspot',
 *       'vine'                   => 'vine',
 *       'vimeo'                  => 'vimeo',
 *       'xtube'                  => 'xtube',
 *       'yahoo'                  => 'yahoo',
 *       'youku'                  => 'youku',
 *       'youtube'                => 'youtube',
 *       'youtubelist'            => 'youtubelist', //* Deprecated and should not be recognized
 *   ),
 */
if ( ! class_exists( 'WPSEO_Video_Plugin_Advanced_Responsive_Video_Embedder' ) ) {

	/**
	 * Class WPSEO_Video_Plugin_Advanced_Responsive_Video_Embedder
	 */
	class WPSEO_Video_Plugin_Advanced_Responsive_Video_Embedder extends WPSEO_Video_Supported_Plugin {

		/**
		 * ARVE native options, containing relevant information on the supported shortcodes.
		 *
		 * @since 3.8.0
		 *
		 * @var array
		 */
		protected $arve_options;

		/**
		 * ARVE native properties, containing relevant information on the supported embeds.
		 *
		 * @since 3.8.0
		 *
		 * @var array
		 */
		protected $arve_properties;


		/**
		 * Conditionally add plugin features to analyse for video content.
		 */
		public function __construct() {
			// ARVE is getting into a habit of continuously renaming their core classes and methods....
			// so checking for several for compatibility.
			if ( class_exists( 'Advanced_Responsive_Video_Embedder_Shared' ) || function_exists( 'arve_get_options' ) ) {

				// Register the main ARVE 5.3+ shortcode.
				$this->shortcodes[] = 'arve';

				// ARVE still supports legacy format, so we should too.
				$arve_options = array();
				if ( method_exists( 'Advanced_Responsive_Video_Embedder_Shared', 'get_options' ) ) {
					$arve_options = Advanced_Responsive_Video_Embedder_Shared::get_options();
				}
				elseif ( function_exists( 'arve_get_options' ) ) {
					$arve_options = arve_get_options();
				}

				if ( ! empty( $arve_options ) && is_array( $arve_options ) ) {
					// We don't support playlists.
					unset( $arve_options['shortcodes']['dailymotionlist'], $arve_options['shortcodes']['youtubelist'] );

					$this->arve_options = $arve_options;

					foreach ( $this->arve_options['shortcodes'] as $provider => $shortcode ) {
						$this->shortcodes[] = $shortcode;
					}
				}
			}


			if ( class_exists( 'Advanced_Responsive_Video_Embedder_Shared' ) || function_exists( 'arve_get_host_properties' ) ) {
				$arve_properties = array();
				if ( method_exists( 'Advanced_Responsive_Video_Embedder_Shared', 'get_properties' ) ) {
					$arve_properties = Advanced_Responsive_Video_Embedder_Shared::get_properties();
				}
				elseif ( function_exists( 'arve_get_host_properties' ) ) {
					$arve_properties = arve_get_host_properties();
				}

				if ( ! empty( $arve_properties ) && is_array( $arve_properties ) ) {
					// We don't support playlists.
					unset( $arve_properties['dailymotionlist'], $arve_properties['youtubelist'] );

					/*
					 * Add the embed keys.
					 * Handler name => VideoSEO service name.
					 */
					foreach ( $arve_properties as $provider => $values ) {
						if ( ! empty( $values['regex'] ) ) {
							$this->video_autoembeds[ 'arve_' . $provider ] = $provider;
						}
					}
				}

				$this->arve_properties = $arve_properties;
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

			if ( $sc === 'arve' ) {
				if ( ! isset( $atts['url'] ) ) {
					// Not usable, no url.
					return $vid;
				}

				$maybe_type = $this->get_service_type_from_shortcode( $atts['url'] );
				if ( $maybe_type !== false ) {
					$vid['type'] = $maybe_type;
					$vid['url']  = $atts['url'];
				}

				if ( $vid !== array() && ! empty( $atts['thumbnail'] ) ) {
					$maybe_thumb = $this->get_thumbnail_loc_from_shortcode( $atts['thumbnail'] );

					if ( is_string( $maybe_thumb ) && $maybe_thumb !== '' ) {
						$vid['thumbnail_loc'] = $maybe_thumb;
					}
				}
			}

			/* Legacy shortcodes. */
			else {
				// Deal with blip weirdness.
				if ( ( $sc === 'blip' || $sc === 'bliptv' ) && ! empty( $atts['id'] ) ) {
					$vid = $this->what_the_blip( $vid, $atts['id'], $full_shortcode );
				}
				elseif ( $sc !== 'iframe' && ! empty( $atts['id'] ) ) {
					$vid['id'] = $atts['id'];
				}
				elseif ( $sc === 'iframe' && ( isset( $atts['id'] ) && is_string( $atts['id'] ) && $atts['id'] !== '' ) ) {
					$vid['url'] = $atts['id'];
				}

				if ( $vid !== array() ) {
					// Only add type if we succesfully found an id/url.
					switch ( $sc ) {
						case 'bliptv':
							$vid['type'] = 'blip';
							break;

						case 'iframe':
							// @todo what should this be? - url iframe embed?
							$vid['type']        = 'iframe';
							$vid['maybe_local'] = true;
							break;

						default:
							$type = false;
							if ( isset( $this->arve_options['shortcodes'] ) ) {
								$type = array_search( $sc, $this->arve_options['shortcodes'], true );
							}

							if ( $type !== false ) {
								$vid['type'] = $type;
							}
							break;
					}
				}
			}

			return $vid;
		}


		/**
		 * Get a thumbnail url from an ARVE thumbnail shortcode attribute.
		 *
		 * This code is a simplified version of code found in the ARVE plugin.
		 *
		 * @see Advanced_Responsive_Video_Embedder_Public::build_embed()
		 *
		 * @since 3.8.0
		 *
		 * @param string $thumbnail The value of the thumbnail shortcode attribute.
		 *
		 * @return string|false URL if a valid thumbnail was found, false otherwise.
		 */
		private function get_thumbnail_loc_from_shortcode( $thumbnail ) {
			$maybe_url = filter_var( $thumbnail, FILTER_SANITIZE_STRING );

			if ( substr( $maybe_url, 0, 4 ) === 'http' && filter_var( $maybe_url, FILTER_VALIDATE_URL ) ) {
				return $thumbnail;
			}
			elseif ( is_numeric( $thumbnail ) ) {
				$img_src = wp_get_attachment_image_url( $thumbnail, 'medium' );

				if ( $img_src !== false ) {
					return $img_src;
				}
			}

			return false;
		}


		/**
		 * Try and determine the video service based on the url using input from ARVE.
		 *
		 * This code is a simplified version of code found in the ARVE plugin.
		 *
		 * @see Advanced_Responsive_Video_Embedder_Public::build_embed()
		 *
		 * @since 3.8.0
		 *
		 * @param string $url The url provided in the shortcode.
		 *
		 * @return string|false The provider name or false if provider could not be determined.
		 */
		private function get_service_type_from_shortcode( $url ) {

			foreach ( $this->arve_properties as $provider => $values ) {

				if ( in_array( $provider, array( 'dailymotionlist', 'youtubelist' ), true ) || empty( $values['regex'] ) ) {
					continue;
				}

				if ( preg_match( '#' . $values['regex'] . '#i', $url, $matches ) === 1 ) {
					return $provider;
				}
			}

			return false;
		}
	} /* End of class */

} /* End of class-exists wrapper */
