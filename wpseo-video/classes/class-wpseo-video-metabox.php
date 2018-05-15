<?php
/**
 * @package    Admin
 * @since      1.6.0
 * @version    1.6.0
 */

// Avoid direct calls to this file.
if ( ! class_exists( 'WPSEO_Video_Sitemap' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

if ( ! class_exists( 'WPSEO_Video_Metabox' ) ) {

	/**
	 * This class adds the Video tab to the WP SEO metabox and makes sure the settings are saved.
	 */
	class WPSEO_Video_Metabox extends WPSEO_Metabox {

		/**
		 * Class constructor
		 */
		public function __construct() {

			add_action( 'wpseo_tab_translate', array( $this, 'translate_meta_boxes' ) );
			add_action( 'wpseo_tab_header', array( $this, 'tab_header' ) );
			add_action( 'wpseo_tab_content', array( $this, 'tab_content' ) );
			add_filter( 'wpseo_save_metaboxes', array( $this, 'save_meta_boxes' ), 10, 1 );

			add_filter( 'wpseo_do_meta_box_field_videositemap-duration', array( $this, 'do_number_field' ), 10, 4 );
			add_filter( 'wpseo_do_meta_box_field_videositemap-rating', array( $this, 'do_number_field' ), 10, 4 );

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}


		/**
		 * Translate text strings for use in the meta box
		 *
		 * IMPORTANT: if you want to add a new string (option) somewhere, make sure you add that array key to
		 * the main meta box definition array in the class WPSEO_Meta() as well!!!!
		 */
		public static function translate_meta_boxes() {
			self::$meta_fields['video']['videositemap-disable']['title'] = __( 'Disable video', 'yoast-video-seo' );
			/* translators: %s: post type name. */
			self::$meta_fields['video']['videositemap-disable']['expl'] = __( 'Disable video for this %s', 'yoast-video-seo' );

			self::$meta_fields['video']['videositemap-thumbnail']['title'] = __( 'Video Thumbnail', 'yoast-video-seo' );
			/* translators: 1: link open tag; 2: link closing tag. */
			self::$meta_fields['video']['videositemap-thumbnail']['description'] = __( 'Now set to %1$sthis image%2$s based on the embed code.', 'yoast-video-seo' );
			self::$meta_fields['video']['videositemap-thumbnail']['placeholder'] = __( 'URL to thumbnail image (remember it\'ll be displayed as 16:9)', 'yoast-video-seo' );

			self::$meta_fields['video']['videositemap-duration']['title']       = __( 'Video Duration', 'yoast-video-seo' );
			self::$meta_fields['video']['videositemap-duration']['description'] = __( 'Overwrite the video duration, or enter one if it\'s empty.', 'yoast-video-seo' );

			self::$meta_fields['video']['videositemap-tags']['title']       = __( 'Tags', 'yoast-video-seo' );
			self::$meta_fields['video']['videositemap-tags']['description'] = __( 'Add extra tags for this video', 'yoast-video-seo' );

			self::$meta_fields['video']['videositemap-category']['title']       = __( 'Category', 'yoast-video-seo' );
			self::$meta_fields['video']['videositemap-category']['description'] = __( 'Override video category for this video', 'yoast-video-seo' );

			self::$meta_fields['video']['videositemap-rating']['title']       = __( 'Rating', 'yoast-video-seo' );
			self::$meta_fields['video']['videositemap-rating']['description'] = __( 'Set a rating between 0 and 5.', 'yoast-video-seo' );

			self::$meta_fields['video']['videositemap-not-family-friendly']['title']       = __( 'Not Family-friendly', 'yoast-video-seo' );
			self::$meta_fields['video']['videositemap-not-family-friendly']['expl']        = __( 'Mark this video as not Family-friendly', 'yoast-video-seo' );
			self::$meta_fields['video']['videositemap-not-family-friendly']['description'] = __( 'If this video should not be available for safe search users, check this box.', 'yoast-video-seo' );
		}


		/**
		 * Helper function to check if the metabox functionality should be loaded
		 *
		 * @return bool
		 */
		public function has_video() {
			if ( isset( $GLOBALS['post']->ID ) ) {
				$video = WPSEO_Meta::get_value( 'video_meta', $GLOBALS['post']->ID );
				if ( is_array( $video ) && $video !== array() ) {
					return true;
				}
			}

			return false;
		}


		/**
		 * Output the tab header for the Video tab in the WordPress SEO meta box on edit post pages.
		 *
		 * @since 0.1
		 */
		public function tab_header() {
			if ( ! $this->should_show_metabox() ) {
				return;
			}
			echo '<li class="video"><a class="wpseo_tablink" href="#wpseo_video">' . esc_html__( 'Video', 'yoast-video-seo' ) . '</a></li>';
		}


		/**
		 * Output the tab content for the Video tab in the WordPress SEO meta box on edit post pages.
		 *
		 * @since 0.1
		 */
		public function tab_content() {
			if ( ! $this->should_show_metabox() ) {
				return;
			}

			if ( $this->has_video() ) {
				$content = '';
				foreach ( $this->get_meta_field_defs( 'video' ) as $meta_key => $meta_field ) {
					$content .= $this->do_meta_box( $meta_field, $meta_key );
				}
				$this->do_tab( 'video', __( 'Video', 'yoast-video-seo' ), $content );
			}
			else {
				$content = '<p>' . __( 'It looks like your content does not yet contain a video. Please add a video and save your draft in order for Video SEO to work.', 'yoast-video-seo' ) . '</p>';
				$this->do_tab( 'video', __( 'Video', 'yoast-video-seo' ), $content );
			}
		}


		/**
		 * Filter over the meta boxes to save, this function adds the Video meta box fields.
		 *
		 * @param  array $field_defs Array of metaboxes to save.
		 *
		 * @return array
		 */
		public function save_meta_boxes( $field_defs ) {
			return array_merge( $field_defs, $this->get_meta_field_defs( 'video' ) );
		}


		/**
		 * Form field generator for number fields in WPSEO metabox
		 *
		 * @param  string $content      The current content of the metabox.
		 * @param  mixed  $meta_value   The meta value to use for the form field.
		 * @param  string $esc_form_key The pre-escaped key for the form field.
		 * @param  array  $options      Contains the min and max value of the number field, if relevant.
		 *
		 * @return string
		 */
		public function do_number_field( $content, $meta_value, $esc_form_key, $options = array() ) {
			$options  = $options['options'];
			$minvalue = '';
			$maxvalue = '';
			$step     = '';

			if ( isset( $options['min_value'] ) ) {
				$minvalue = ' min="' . $options['min_value'] . '" ';
			}

			if ( isset( $options['max_value'] ) ) {
				$maxvalue = ' max="' . $options['max_value'] . '" ';
			}

			if ( isset( $options['step'] ) ) {
				$step = ' step="' . $options['step'] . '" ';
			}

			$content .= '<input type="number" id="' . $esc_form_key . '" name="' . $esc_form_key . '" value="' . $meta_value . '"' . $minvalue . $maxvalue . $step . 'class="small-text" /><br />';

			return $content;
		}

		/**
		 * Enqueues the pluginscripts.
		 */
		public function enqueue_scripts() {
			wp_enqueue_script( 'wp-seo-video-seo', plugins_url( 'js/yoast-videoseo-plugin-510' . WPSEO_CSSJS_SUFFIX . '.js', WPSEO_VIDEO_FILE ), array(), WPSEO_VERSION, true );

			wp_localize_script( 'wp-seo-video-seo', 'wpseoVideoL10n', $this->localize_video_script() );
		}

		/**
		 * Check if the post type the user is currently editing is shown in the sitemaps. If so, the video metabox should be shown.
		 *
		 * @return bool
		 */
		private function should_show_metabox() {
			return WPSEO_Video_Sitemap::is_videoseo_active_for_posttype( get_post_type() );
		}

		/**
		 * Localizes scripts for the videoplugin.
		 *
		 * @return array
		 */
		private function localize_video_script() {
			return array(
				'has_video'           => $this->has_video(),
				'video'               => __( 'video', 'yoast-video-seo' ),
				'video_title_ok'      => __( 'You should consider adding the word "video" in your title, to optimize your ability to be found by people searching for video.', 'yoast-video-seo' ),
				'video_title_good'    => __( 'You\'re using the word "video" in your title, this optimizes your ability to be found by people searching for video.', 'yoast-video-seo' ),
				'video_body_short'    => __( 'Your body copy is too short for Search Engines to understand the topic of your video, add some more content describing the contents of the video.', 'yoast-video-seo' ),
				'video_body_good'     => __( 'Your body copy is at optimal length for your video to be recognized by Search Engines.', 'yoast-video-seo' ),
				/* translators: 1: links to https://yoast.com/video-not-showing-search-results, 2: closing link tag */
				'video_body_long'     => __( 'Your body copy is quite long, make sure that the video is the most important asset on the page, read %1$sthis post%2$s for more info.', 'yoast-video-seo' ),
				'video_body_long_url' => '<a target="new" href="https://yoast.com/video-not-showing-search-results/">',
			);
		}


		/********************** DEPRECATED METHODS **********************/


		/**
		 * Replace the default snippet with a video snippet by hooking this function into the wpseo_snippet filter.
		 *
		 * @since      0.1
		 * @deprecated 3.8 Functionality can no longer be reached as the filter calling this was
		 *                 removed from WPSEO. Replaced by JavaScript.
		 *
		 * @param string $content The original snippet content.
		 * @param object $post    The post object of the post for which the snippet was generated.
		 * @param array  $vars    An array of variables for use within the snippet, containing title, description, date and slug.
		 *
		 * @return string $content The new video snippet if video metadata was found for the post.
		 */
		public function snippet_preview( $content, $post, $vars ) {
			_deprecated_function( __FUNCTION__, 'WPSEO 3.8' );
			$options = get_option( 'wpseo_video' );
			if ( ! $this->should_show_metabox() ) {
				return $content;
			}

			$disable = self::get_value( 'videositemap-disable', $post->ID );
			if ( $disable === 'on' || $this->has_video() !== true ) {
				return $content;
			}

			$video = self::get_value( 'video_meta', $post->ID );
			$video = $GLOBALS['wpseo_video_xml']->get_video_image( $post->ID, $video );

			if ( is_ssl() ) {
				$video['thumbnail_loc'] = str_replace( 'http://', 'https://', $video['thumbnail_loc'] );
			}

			$video_duration = self::get_value( 'videositemap-duration', $post->ID );
			if ( $video_duration === 0 && isset( $video['duration'] ) ) {
				$video_duration = $video['duration'];
			}

			$duration      = '';
			$duration_snip = '';
			if ( $video_duration ) {
				$mins = floor( $video_duration / MINUTE_IN_SECONDS );
				$secs = ( $video_duration - ( $mins * MINUTE_IN_SECONDS ) );
				if ( $secs === 0 ) {
					$secs = '00';
				}
				elseif ( $secs < 10 ) {
					$secs = '0' . $secs;
				}
				$duration = $mins . ':' . $secs;

				if ( $video_duration > MINUTE_IN_SECONDS ) {
					$duration_snip = number_format( $video_duration / MINUTE_IN_SECONDS ) . ' min';
				}
				else {
					$duration_snip = $video_duration . ' sec';
				}
			}
			$url = trailingslashit( home_url() ) . $vars['slug'];
			$url = str_replace( 'http://', '', $url );

			$content = '<div id="wpseosnippet">
				<table class="video" cellpadding="0" cellspacing="0">
					<tr>
						<td colspan="2">
							<h4 style="margin: 0; font-weight: normal;"><a class="title" target="_blank" id="wpseosnippet_title" href="' . esc_url( get_permalink( $post->ID ) ) . '">' . $vars['title'] . '</a></h4>
						</td>
					</tr>
					<tr>
						<td style="padding-right: 8px; padding-top: 4px; vertical-align: top;" width="1%">
							<div style="position: relative; width: 120px; height: 65px; overflow: hidden">
								<a href="#" style="text-decoration: none">
									<div style="position: relative; top: -12px">
										<img align="middle" style="display: inline-block; height: 90px; margin: 0; width: 120px" width="120" height="90" src="' . esc_url( $video['thumbnail_loc'] ) . '"/>
									</div>
									<span style="position: absolute; bottom: 0; right: 0; text-align: right; font-size: 11px; color: #000; background-color: #000; padding: 1px 3px; text-decoration: none; font-weight: bold; filter: alpha(opacity=70); -moz-opacity: 0.7; -khtml-opacity: 0.7; opacity: 0.7">&#x25B6;&nbsp;' . esc_html( $duration ) . '</span>
									<span style="position: absolute; bottom: 0; right: 0; text-align: right; font-size: 11px; color: #fff; padding: 1px 3px; text-decoration: none; font-weight: bold">&#x25B6;&nbsp;' . esc_html( $duration ) . '</span>
								</a>
							</div>
						</td>
						<td style="padding-top: 1px; vertical-align: text-top;">
							<div>
								<cite class="url">' . esc_url( $url ) . '</cite>
								<p style="color: #666; font-size: 13px; line-height: 16px;">' . date( 'j M Y', strtotime( $post->post_date ) ) . ' - ' . esc_html( $duration_snip ) . '</p>
								<p style="color: #222; font-size: 13px; line-height: 16px;" class="desc"><span class="content">' . $vars['description'] . '</span></p>
							</div>
						</td>
					</tr>
				</table>
				<div style="margin-top:7px">';

			return $content;
		}


		/**
		 * Restricts the length of the meta description in the snippet preview and throws appropriate warnings.
		 *
		 * @since      0.1
		 * @deprecated 3.8 Functionality can no longer be reached as the filter calling this was
		 *                 removed from WPSEO. Replaced by JavaScript.
		 *
		 * @todo  [JRF -> whomever] should 115 be an override of the WPSEO_Meta property ? or a property of this class ?
		 *
		 * @param int $length The snippet length as defined by default.
		 *
		 * @return int $length The max snippet length for a video snippet.
		 */
		public function meta_length( $length ) {
			_deprecated_function( __FUNCTION__, 'WPSEO 3.8' );
			$disable = self::get_value( 'videositemap-disable', $GLOBALS['post']->ID );
			if ( $disable === 'on' || $this->has_video() !== true ) {
				return $length;
			}

			return 115;
		}


		/**
		 * Explains the length restriction of the meta description
		 *
		 * @since      0.1
		 * @deprecated 3.8 Functionality can no longer be reached as the filter calling this was
		 *                 removed from WPSEO. Replaced by JavaScript.
		 *
		 * @param string $reason Input string.
		 *
		 * @return string $reason  The reason why the meta description is limited.
		 */
		public function meta_length_reason( $reason ) {
			_deprecated_function( __FUNCTION__, 'WPSEO 3.8' );
			if ( $this->has_video() === true ) {
				$reason = __( ' (because it\'s a video snippet)', 'yoast-video-seo' );
			}

			return $reason;
		}


		/**
		 * Filter the Page Analysis results to make sure we're giving the correct hints.
		 *
		 * @since      1.4
		 * @deprecated 3.8 Functionality can no longer be reached as the filter calling this was
		 *                 removed from WPSEO. Replaced by JavaScript.
		 *
		 * @param array  $results The results array to filter and update.
		 * @param array  $job     The current jobs variables.
		 * @param object $post    The post object for the current page.
		 *
		 * @return array $results
		 */
		public function filter_linkdex_results( $results, $job, $post ) {
			_deprecated_function( __FUNCTION__, 'WPSEO 3.8' );
			$disable = self::get_value( 'videositemap-disable', $post->ID );
			if ( $disable === 'on' || $this->has_video() !== true ) {
				return $results;
			}

			if ( stripos( $job['title'], __( 'video', 'yoast-video-seo' ) ) === false ) {
				$results['title_video'] = array(
					'val' => 6,
					'msg' => __( 'You should consider adding the word "video" in your title, to optimize your ability to be found by people searching for video.', 'yoast-video-seo' ),
				);
			}
			else {
				$results['title_video'] = array(
					'val' => 9,
					'msg' => __( 'You\'re using the word "video" in your title, this optimizes your ability to be found by people searching for video.', 'yoast-video-seo' ),
				);
			}

			if ( $results['body_length']['raw'] > 150 && $results['body_length']['raw'] < 400 ) {
				$results['body_length'] = array(
					'val' => 9,
					'msg' => __( 'Your body copy is at optimal length for your video to be recognized by Search Engines.', 'yoast-video-seo' ),
				);
			}
			elseif ( $results['body_length']['raw'] < 150 ) {
				$results['body_length'] = array(
					'val' => 6,
					'msg' => __( 'Your body copy is too short for Search Engines to understand the topic of your video, add some more content describing the contents of the video.', 'yoast-video-seo' ),
				);
			}
			else {
				$results['body_length'] = array(
					'val' => 6,
					/* translators: 1: links to https://yoast.com/video-not-showing-search-results, 2: closing link tag */
					'msg' => sprintf( __( 'Your body copy is quite long, make sure that the video is the most important asset on the page, read %1$sthis post%2$s for more info.', 'yoast-video-seo' ), '<a href="https://yoast.com/video-not-showing-search-results/">', '</a>' ),
				);
			}

			return $results;
		}
	} /* End of class */

} /* End of class-exists wrapper */
