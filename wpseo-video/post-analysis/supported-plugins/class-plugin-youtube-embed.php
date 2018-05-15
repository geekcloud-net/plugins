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
 * Add support for the YouTube Embed plugin
 *
 * @see https://wordpress.org/plugins/youtube-embed/
 *
 * {@internal Last update: July 2014 based upon v 3.2.1.}}
 */
if ( ! class_exists( 'WPSEO_Video_Plugin_Youtube_Embed' ) ) {

	/**
	 * Class WPSEO_Video_Plugin_Youtube_Embed
	 */
	class WPSEO_Video_Plugin_Youtube_Embed extends WPSEO_Video_Supported_Plugin {

		/**
		 * Conditionally add plugin features to analyse for video content
		 */
		public function __construct() {
			if ( defined( 'youtube_embed_version' ) ) {
				$this->shortcodes = array(
					'youtube',
					'youtube_playlist',
					'youtube_video',
				);

				$xtra_shortcodes = get_option( 'youtube_embed_shortcode' );
				if ( is_array( $xtra_shortcodes ) && $xtra_shortcodes !== array() ) {
					foreach ( $xtra_shortcodes as $sc ) {
						if ( is_string( $sc ) && $sc !== '' ) {
							$this->shortcodes[] = $sc;
						}
					}
				}
				unset( $xtra_shortcodes, $sc );

				// @todo - check in which version which protocol was added and adjust for that.
				$this->alt_protocols = array(
					'httpv://',
					'httpvh://',
					'httpa://',
					'youtube::',
				);

				/*
				 * @todo figure out what to do (if anything) about the 'bracket' and 'alt' syntax
				 * @see info from the plugin below
				 * [http://www.youtube.com/watch?v=Z_sCoHGIpU0]
				 *
				 * get_option( 'youtube_embed_general' ); //'bracket' == '1', 'alt' == '1'
				 */
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

			if ( isset( $content ) ) {
				/*
				 * ignore audio = yes
				 * ignore list -> value doesn't matter (=playlist)
				 * ignore if has search (=playlist)
				 * ignore if has user (=playlist)
				 */
				if ( ( ! isset( $atts['list'] ) && ( ! isset( $atts['search'] ) && ! isset( $atts['user'] ) ) ) && ( ! isset( $atts['audio'] ) || $atts['audio'] !== 'yes' ) ) {

					// Is it a url or an id.
					if ( strpos( $content, 'http' ) === 0 || strpos( $content, '//' ) === 0 ) {
						$vid['url'] = $content;
					}
					elseif ( $this->is_youtube_id( $content ) ) {
						$vid['id'] = $content;
					}

					if ( $vid !== array() ) {
						$vid['type'] = 'youtube';
						$vid         = $this->maybe_get_dimensions( $vid, $atts, true );
					}
				}
			}

			return $vid;
		}

		/*
		Alternative Shortcodes

		Within your WordPress administration page, select Options from the YouTube menu to see a list of general options:

		One section is named Alternative Shortcodes and allows you to specify two additional shortcodes - these will work exactly the same as the standard shortcode of [youtube].

		There are two reasons why you might want to do this...

		    If migrating from another plug-in, it may use a different shortcode - more details can be found in the section named "Migration".
		    If another plug-in uses the same shortcode (e.g. Jetpack) this will allow you to specify and use an alternative.

		Each of the new shortcodes can also have their own default profile assigned to them (see Profiles for more details).

		Automatically Generated Playlists

		Vixy YouTube Embed includes the ability to automatically generate playlists based upon a user name or a search name. Simply use the user or search parameter to switch the appropriate option on. Instead of a video ID or URL, specify either the user name or search word(s), like this:

		[youtube search=yes]Blake Griffin[/youtube]
		[youtube user=yes]NBA[/youtube]

		Migrating from Other Plug-ins

		Within your WordPress administration page, select Options from the YouTube menu, then scroll to the Migration section. There are two boxes that can be checked to activate two different types of alternative embedding - these have been provided to allow easy migration from other similar plug-ins. You can also assign a specific profile to these migrated options.

		The Bracket Embedding option allows YouTube URLs to be assigned within brackets - similar to shortcodes but without the actual shortcode name. Example:

		[http://www.youtube.com/watch?v=Z_sCoHGIpU0]

		The Alternative Embedding option activates a shortcode of other alternative embedding methods.

		Deploying these will impact performance, so they should only be used if absolutely necessary.
		*/
	}
}
