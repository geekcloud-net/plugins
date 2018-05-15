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
if ( ! class_exists( 'WPSEO_Video_Supported_Plugin' ) ) {

	/**
	 * @package    WordPress\Plugins\Video-seo
	 * @subpackage Internals
	 * @since      1.8.0
	 * @version    1.8.0
	 *
	 * This abstract class and it's concrete classes implement the post analysis using the best
	 * available PHP extension.
	 */
	abstract class WPSEO_Video_Supported_Plugin {

		/**
		 * @var array  Array of supported shortcodes
		 *
		 * {@internal   Should be set from the class constructor!}}
		 */
		protected $shortcodes = array();

		/**
		 * @var array  Array of (additional) video post types
		 *
		 * {@internal   Should be set from the class constructor!}}
		 */
		protected $post_types = array();

		/**
		 * @var array  Array of meta_keys which contain video information
		 *
		 * {@internal   Should be set from the class constructor!}}
		 */
		protected $meta_keys = array();

		/**
		 * @var array  Array of supported alternative url protocol schemes
		 *
		 * {@internal   Should be set from the class constructor!}}
		 */
		protected $alt_protocols = array();

		/**
		 * @var array  Array of registered embed handlers which should be treated as video embed handlers
		 *             Array key   = name of the embed handler
		 *             Array value = video service type or empty if the verify_type_from_url method should determine it
		 *
		 * {@internal   Should be set from the class constructor!}}
		 */
		protected $video_autoembeds = array();

		/**
		 * @var array  Array of registered oembed handlers which should be treated as video oembed handlers
		 *             Array key   = first part of the oembed url used
		 *             Array value = video service type or empty if the verify_type_from_url method should determine it
		 *
		 * {@internal   Should be set from the class constructor!}}
		 */
		protected $video_oembeds = array();


		/**
		 * The child constructor should double-check that we really have the required plugin available
		 * and add any and all plugin specific properties so everything can be bypassed based on properties
		 * being not set / empty
		 */
		abstract public function __construct();


		/**
		 * Retrieve a property
		 *
		 * @param array $property The property.
		 *
		 * @return array|bool  The property array or false
		 */
		public function maybe_get_property( $property ) {
			if ( is_array( $property ) && $property !== array() ) {
				return $property;
			}
			else {
				return false;
			}
		}


		/**
		 * Retrieve the shortcodes added by a plugin
		 *
		 * @return array|bool  Array with shortcodes or false if the plugin does not add shortcodes
		 */
		public function get_shortcodes() {
			return $this->maybe_get_property( $this->shortcodes );
		}


		/**
		 * Retrieve the video post types added by a plugin
		 *
		 * @return array|bool  Array with post types or false if the plugin does not add video post types
		 */
		public function get_post_types() {
			return $this->maybe_get_property( $this->post_types );
		}


		/**
		 * Retrieve the meta keys which may contain video information as added by a plugin
		 *
		 * @return array|bool  Array with meta keys or false if the plugin does not add relevant meta keys
		 */
		public function get_meta_keys() {
			return $this->maybe_get_property( $this->meta_keys );
		}


		/**
		 * Retrieve the alternative url protocols added by a plugin
		 *
		 * @return array|bool  Array with alternative url protocols or false if the plugin does not add protocols
		 */
		public function get_alt_protocols() {
			return $this->maybe_get_property( $this->alt_protocols );
		}


		/**
		 * Retrieve the names for the video embeds added by a plugin
		 *
		 * @return array|bool  Array with video embeds or false if the plugin does not add embeds
		 */
		public function get_video_autoembeds() {
			return $this->maybe_get_property( $this->video_autoembeds );
		}


		/**
		 * Retrieve the info for the video oembeds added by a plugin
		 *
		 * @return array|bool  Array with video oembeds or false if the plugin does not add oembeds
		 */
		public function get_video_oembeds() {
			return $this->maybe_get_property( $this->video_oembeds );
		}


		/**
		 * Deal with non-named width/height attributes
		 *
		 * Examples:
		 * [collegehumor 1727961 200 100]
		 * [tube]http://www.youtube.com/watch?v=AFVlJAi3Cso, 500, 290[/tube]
		 *
		 * @param array $list  A pre-explode array of the attribute/content value.
		 * @param array $atts  The current attributes.
		 *
		 * @return array
		 */
		protected function normalize_dimension_attributes( $list, $atts ) {
			if ( ! isset( $atts['width'] ) ) {
				if ( ! empty( $list[1] ) ) {
					$atts['width'] = $list[1];
				}
				elseif ( ! empty( $atts[1] ) ) {
					$atts['width'] = $atts[1];
					unset( $atts[1] );
				}
			}
			if ( ! isset( $atts['height'] ) ) {
				if ( ! empty( $list[2] ) ) {
					$atts['height'] = $list[2];
				}
				elseif ( ! empty( $atts[2] ) ) {
					$atts['height'] = $atts[2];
					unset( $atts[2] );
				}
			}
			return $atts;
		}


		/**
		 * Distill video dimensions from shortcodes attributes
		 *
		 * @param  array $vid             Current video info array.
		 * @param  array $atts            The shortcode attributes.
		 * @param  bool  $try_alternative Whether to try and find only the 'normal' "width" and "height" attributes
		 *                                or also to try and find the alternative "w" and "h" attributes.
		 *
		 * @return array  Potentially adjusted video info array
		 */
		protected function maybe_get_dimensions( $vid, $atts, $try_alternative = false ) {
			if ( isset( $atts['width'] ) && ! empty( $atts['width'] ) && $atts['width'] > 0 ) {
				$vid['width'] = (int) $atts['width'];
			}
			if ( isset( $atts['height'] ) && ! empty( $atts['height'] ) && $atts['height'] > 0 ) {
				$vid['height'] = (int) $atts['height'];
			}

			if ( $try_alternative === true ) {
				if ( ! isset( $vid['width'] ) && ! empty( $atts['w'] ) && $atts['w'] > 0 ) {
					$vid['width'] = (int) $atts['w'];
				}
				if ( ! isset( $vid['height'] ) && ! empty( $atts['h'] ) && $atts['h'] > 0 ) {
					$vid['height'] = (int) $atts['h'];
				}
			}

			return $vid;
		}


		/**
		 * Determine if a given id could be a youtube video id
		 *
		 * @param  string $id ID string to evaluate.
		 *
		 * @return bool
		 */
		public function is_youtube_id( $id ) {
			return ( ! empty( $id ) && preg_match( '`^(' . WPSEO_Video_Sitemap::$youtube_id_pattern . ')$`', $id ) );
		}


		/**
		 * Determine if a given id could be a google video id
		 *
		 * @param  string $id ID string to evaluate.
		 *
		 * @return bool
		 */
		public function is_googlevideo_id( $id ) {
			return ( ! empty( $id ) && preg_match( '`^[-]?[0-9]+$`', $id ) );
		}


		/**
		 * Determine if a given id could be a vimeo video id
		 *
		 * @param  string $id ID string to evaluate.
		 *
		 * @return bool
		 */
		public function is_vimeo_id( $id ) {
			return $this->is_numeric_id( $id );
		}


		/**
		 * Determine if a given id could be a flickr video id
		 *
		 * Allow both real (numeric) ids as well as short ids
		 * Keep this regex in line with the one in the flickr service class
		 *
		 * @param  string $id ID string to evaluate.
		 *
		 * @return bool
		 */
		public function is_flickr_id( $id ) {
			return ( ! empty( $id ) && preg_match( '`^[a-z0-9_-]+$`i', $id ) );
		}


		/**
		 * Determine if a given id is numeric
		 *
		 * @param  string $id ID string to evaluate.
		 *
		 * @return bool
		 */
		public function is_numeric_id( $id ) {
			return ( ! empty( $id ) && preg_match( '`^[0-9]+$`', $id ) );
		}


		/**
		 * Figure out whether the received input is a blip url, id or embedlookup or combination of those
		 * (url which contains the id or url which contains the embedlookup).
		 *
		 * Example data:
		 * [bliptv id="hdljgdbVBwI"]
		 * http://blip.tv/rss/view/3516963
		 * http://blip.tv/day9tv/day-9-daily-101-kawaii-rice-tvp-style-3516963
		 * http://blip.tv/play/hdljgdbVBwI
		 *
		 * @param  array  $vid            The current video info array.
		 * @param  string $check_this     Primary value to check.
		 * @param  string $full_shortcode The full Shortcode found.
		 *
		 * @return array  Potentially adjusted video info array
		 */
		protected function what_the_blip( $vid, $check_this, $full_shortcode ) {
			// Is it a url, an id or embedlookup ?
			if ( $check_this !== '' && ( ( strpos( $check_this, 'http' ) === 0 || strpos( $check_this, '//' ) === 0 ) && strpos( $check_this, 'blip.tv' ) !== false ) ) {
				$vid['url'] = $check_this;
			}

			if ( preg_match( '`posts_id=["\']?([0-9]+)`i', $full_shortcode, $match ) ) {
				$vid['id'] = $match[1];
			}
			elseif ( isset( $vid['url'] ) && preg_match( '`(?:[/-])([0-9]+)$`i', $vid['url'], $match ) ) {
				$vid['id'] = $match[1];
			}
			elseif ( $check_this !== '' && preg_match( '`(?:^|[\?/=])([A-Za-z0-9-]{5,})(?:$|[&%\./])`', $check_this, $match ) ) {
				$vid['embedlookup'] = $match[1];
			}
			return $vid;
		}
	} /* End of class */

} /* End of class-exists wrapper */
