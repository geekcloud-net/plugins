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
 * Add support for WP Core video functionality
 *
 * @see      https://codex.wordpress.org/Video_Shortcode
 * @see      https://codex.wordpress.org/Embed_Shortcode
 *
 * {@internal Last update: August 2014 based upon v 3.9.2.}}
 */
if ( ! class_exists( 'WPSEO_Video_Support_Core' ) ) {

	/**
	 * Class WPSEO_Video_Support_Core
	 */
	class WPSEO_Video_Support_Core extends WPSEO_Video_Supported_Plugin {

		/**
		 * @var string $att_regex Regular expression to use to find the video file
		 *                         Set here as other classes extend on this one using a slightly different regex.
		 */
		protected $att_regex = '`(?:src|mp4|m4v|webm|ogv|wmv|flv)=([\'"])?([^\'"\s]+)[\1\s]?`';

		/**
		 * Conditionally add features to analyse for video content
		 */
		public function __construct() {
			$this->shortcodes = array(
				'embed',
			);

			$this->shortcodes[] = 'video'; // WP 3.6+.

			// Handler name => VideoSEO service name.
			$this->video_autoembeds = array(
				'googlevideo' => 'googlevideo',
				'video'       => '',
			);

			// OEmbed url (well, without the protocol or {format} tags) as specified in plugin => VideoSEO service name.
			$this->video_oembeds = array(
				'//blip.tv/oembed/'                     => 'blip',
				'//www.dailymotion.com/services/oembed' => 'dailymotion',
				'//www.flickr.com/services/oembed/'     => 'flickr',
				'//www.funnyordie.com/oembed'           => 'funnyordie',
				'//www.hulu.com/api/oembed'             => 'hulu',
				'//revision3.com/api/oembed/'           => 'revision3',
				'//vimeo.com/api/oembed'                => 'vimeo',
				'//wordpress.tv/oembed/'                => 'wordpresstv',
				'//www.youtube.com/oembed'              => 'youtube',
				'//animoto.com/oembeds/create'          => 'animoto',
				'//www.collegehumor.com/oembed'         => 'collegehumor',
				'//www.ted.com/talks/oembed'            => 'ted',
			);
		}

		/**
		 * Analyse a video shortcode as used in WP core for usable video information
		 *
		 * @param  string $full_shortcode Full shortcode as found in the post content.
		 * @param  string $sc             Shortcode found.
		 * @param  array  $atts           Shortcode attributes - already decoded if needed.
		 * @param  string $content        The shortcode content, i.e. the bit between [sc]content[/sc].
		 *
		 * @return array   An array with the usable information found or else an empty array
		 */
		public function get_info_from_shortcode( $full_shortcode, $sc, $atts = array(), $content = '' ) {
			$method = 'get_info_from_shortcode_' . $sc;

			return $this->$method( $full_shortcode, $sc, $atts, $content );
		}

		/**
		 * Analyse the video shortcode as used in WP core for usable video information
		 *
		 * @param  string $full_shortcode Full shortcode as found in the post content.
		 * @param  string $sc             Shortcode found.
		 * @param  array  $atts           Shortcode attributes - already decoded if needed.
		 * @param  string $content        The shortcode content, i.e. the bit between [sc]content[/sc].
		 *
		 * @return array   An array with the usable information found or else an empty array
		 */
		public function get_info_from_shortcode_video( $full_shortcode, $sc, $atts = array(), $content = '' ) {
			$vid = array();

			if ( preg_match( $this->att_regex, $full_shortcode, $match ) ) {
				$vid['type']        = 'mediaelement-js';
				$vid['url']         = $match[2];
				$vid['maybe_local'] = true;

				// If a poster image was specified, use that, otherwise, try and find a suitable .jpg.
				if ( isset( $atts['poster'] ) && is_string( $atts['poster'] ) && $atts['poster'] !== '' ) {
					if ( WPSEO_Video_Wrappers::yoast_wpseo_video_is_url_relative( $atts['poster'] ) === true ) {
						$info = WPSEO_Video_Analyse_Post::wp_parse_url( get_site_url() );
						// @todo should we surround this with a file_exists check?
						$vid['thumbnail_loc'] = $info['scheme'] . '://' . $info['host'] . $atts['poster'];
					}
					else {
						$vid['thumbnail_loc'] = $atts['poster'];
					}
				}

				$vid = $this->maybe_get_dimensions( $vid, $atts );
			}

			return $vid;
		}


		/**
		 * Analyse the embed shortcode as used in WP core for usable video information
		 *
		 * @param  string $full_shortcode Full shortcode as found in the post content.
		 * @param  string $sc             Shortcode found.
		 * @param  array  $atts           Shortcode attributes - already decoded if needed.
		 * @param  string $content        The shortcode content, i.e. the bit between [sc]content[/sc].
		 *
		 * @return array   An array with the usable information found or else an empty array
		 */
		public function get_info_from_shortcode_embed( $full_shortcode, $sc, $atts = array(), $content = '' ) {
			$vid = array();

			if ( ! empty( $content ) && ( strpos( $content, 'http' ) === 0 || strpos( $content, '//' ) === 0 ) ) {
				$vid['url'] = $content;
			}

			if ( $vid !== array() ) {
				$vid = $this->maybe_get_dimensions( $vid, $atts );
			}

			return $vid;
		}
	} /* End of class */

} /* End of class-exists wrapper */
