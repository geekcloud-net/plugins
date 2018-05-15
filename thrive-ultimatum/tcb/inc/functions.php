<?php
/**
 * general functions used all across TCB
 */

/**
 * @param string $file optional file path
 *
 * @return string the URL to the /editor/css/ dir
 */
function tve_editor_css( $file = null ) {
	return tve_editor_url() . '/editor/css' . ( null !== $file ? '/' . $file : '' );
}

/**
 * @return string the url to the editor/js folder
 */
function tve_editor_js() {
	return tve_editor_url() . '/editor/js/dist';
}

/**
 * return the absolute path to the plugin folder
 *
 * @param string $file
 *
 * @return string
 */
function tve_editor_path( $file = '' ) {
	return plugin_dir_path( dirname( __FILE__ ) ) . ltrim( $file, '/' );
}

/**
 * get all the style families used by TCB
 *
 * @return array
 */
function tve_get_style_families() {
	return apply_filters( 'tcb_style_families', array(
		'Flat'    => tve_editor_css() . '/thrive_flat.css?ver=' . TVE_VERSION,
		'Classy'  => tve_editor_css() . '/thrive_classy.css?ver=' . TVE_VERSION,
		'Minimal' => tve_editor_css() . '/thrive_minimal.css?ver=' . TVE_VERSION,
	) );
}

/**
 *
 * @return string the absolute url to the landing page templates folder
 */
function tve_landing_page_template_url() {
	return tve_editor_url() . '/landing-page/templates';
}

/**
 * notice to be displayed if license not validated - going to load the styles inline because there are so few lines and not worth an extra server hit.
 */
function tve_license_notice() {
	include dirname( dirname( __FILE__ ) ) . '/inc/license_notice.php';
}

/**
 * register Thrive Architect global settings
 */
function tve_global_options_init() {
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

	$plugin_db_version = get_option( 'tve_version' );
	if ( ! $plugin_db_version || $plugin_db_version != TVE_VERSION ) {
		tve_run_plugin_upgrade( $plugin_db_version, TVE_VERSION );
		update_option( 'tve_version', TVE_VERSION );
	}

	/**
	 * check and run any database migrations
	 */
	require_once dirname( dirname( __FILE__ ) ) . '/database/Manager.php';
	Thrive_TCB_Database_Manager::check();

	/**
	 * Cloud Content Templates - custom post type
	 */
	register_post_type( TCB_CT_POST_TYPE, array(
		'public' => false,
	) );
}

/**
 * Returns the url for closing the TCB editing screen.
 *
 * If no post id is set then will use native WP functions to get the editing URL for the piece of content that's currently being edited
 *
 * @param bool $post_id
 *
 * @return string
 */
function tcb_get_editor_close_url( $post_id = false ) {
	/**
	 * we need to make sure that if the admin is https, then the editor link is also https, otherwise any ajax requests through wp ajax api will not work
	 */
	$admin_ssl = strpos( admin_url(), 'https' ) === 0;
	$post_id   = ( $post_id ) ? $post_id : get_the_ID();

	$editor_link = set_url_scheme( get_permalink( $post_id ) );
	$close_url   = apply_filters( 'tcb_close_url', $admin_ssl ? str_replace( 'http://', 'https://', $editor_link ) : $editor_link );

	return $close_url;
}

/**
 * Returns the url for the TCB editing screen.
 *
 * If no post id is set then will use native WP functions to get the editing URL for the piece of content that's currently being edited
 *
 * @param bool $post_id
 * @param bool $main_frame whether or not to get the main frame Editor URL or the child frame one
 *
 * @return string
 */
function tcb_get_editor_url( $post_id = false, $main_frame = true ) {
	/**
	 * we need to make sure that if the admin is https, then the editor link is also https, otherwise any ajax requests through wp ajax api will not work
	 */
	$admin_ssl = strpos( admin_url(), 'https' ) === 0;
	$post_id   = ( $post_id ) ? $post_id : get_the_ID();
	/*
     * We need the post to complete the full arguments for the preview_post_link filter
     */
	$post        = get_post( $post_id );
	$editor_link = set_url_scheme( get_permalink( $post_id ) );
	$params      = array(
		TVE_EDITOR_FLAG => 'true',
	);
	if ( ! $main_frame ) {
		$params[ TVE_FRAME_FLAG ] = wp_create_nonce( TVE_FRAME_FLAG . $post_id );
	}
	$editor_link = apply_filters( 'preview_post_link', add_query_arg( apply_filters( 'tcb_editor_edit_link_query_args', $params, $post_id ), $editor_link ), $post );

	return $admin_ssl ? str_replace( 'http://', 'https://', $editor_link ) : $editor_link;
}

/**
 * Returns the preview URL for any given post/page
 *
 * If no post id is set then will use native WP functions to get the editing URL for the piece of content that's currently being edited
 *
 * @param bool $post_id
 *
 * @return string
 */
function tcb_get_preview_url( $post_id = false ) {
	$post_id = ( $post_id ) ? $post_id : get_the_ID();
	/*
     * We need the post to complete the full arguments for the preview_post_link filter
     */
	$post         = get_post( $post_id );
	$preview_link = set_url_scheme( get_permalink( $post_id ) );
	$preview_link = esc_url( apply_filters( 'preview_post_link', add_query_arg( apply_filters( 'tcb_editor_preview_link_query_args', array( 'preview' => 'true' ), $post_id ), $preview_link ), $post ) );

	return $preview_link;
}

/**
 *
 * checks whether the $post_type is editable using the TCB
 *
 * @param string $post_type
 * @param int    $post_id
 *
 * @return bool true if the post type is editable
 */
function tve_is_post_type_editable( $post_type, $post_id = null ) {
	/* post types that are not editable using the content builder - handled as a blacklist */
	$blacklist_post_types = array(
		'focus_area',
		'thrive_optin',
		'tvo_shortcode',
	);

	$blacklist_post_types = apply_filters( 'tcb_post_types', $blacklist_post_types );

	if ( isset( $blacklist_post_types['force_whitelist'] ) && is_array( $blacklist_post_types['force_whitelist'] ) ) {
		return in_array( $post_type, $blacklist_post_types['force_whitelist'] );
	}

	if ( in_array( $post_type, $blacklist_post_types ) ) {
		return false;
	}

	if ( $post_id === null ) {
		$post_id = get_the_ID();
	}

	return apply_filters( 'tcb_post_editable', true, $post_type, $post_id );
}

/**
 * Sometimes the only way to make the plugin work with other scripts is by deregistering them on the editor page
 */
function tve_remove_conflicting_scripts() {
	if ( is_editor_page() ) {
		/**  Genesis framework - Media Child theme contains a script that prevents users from being able to close the media library */
		wp_dequeue_script( 'yt-embed' );
		wp_deregister_script( 'yt-embed' );

		/** Member player loads jquery tools which conflicts with jQuery UI */
		wp_dequeue_script( 'mpjquerytools' );
		wp_deregister_script( 'mpjquerytools' );

		/** Solved Conflict with WooCommerce Geolocation setting with cache */
		/** When Geolocation with page cache is enabled scripts are duplicated in the iFrame */
		wp_deregister_script( 'wc-geolocation' );
		wp_dequeue_script( 'wc-geolocation' );
	}
}

/**
 * Adds TCB editing URL to underneath the post title in the Wordpress post listings view
 *
 * @param $actions
 * @param $page_object
 *
 * @return mixed
 */
function thrive_page_row_buttons( $actions, $page_object ) {
	// don't add url to blacklisted content types
	if ( ! tve_is_post_type_editable( $page_object->post_type ) || ! current_user_can( 'edit_posts' ) ) {
		return $actions;
	}

	$page_for_posts = get_option( 'page_for_posts' );
	if ( $page_for_posts && $page_object->ID == $page_for_posts ) {
		return $actions;
	}

	?>
	<style type="text/css">
		.thrive-adminbar-icon {
			background: url('<?php echo tve_editor_css(); ?>/images/admin-bar-logo.png') no-repeat 0 0;
			background-size: contain;
			padding-left: 25px;
		}
	</style>
	<?php

	$url            = tcb_get_editor_url( $page_object->ID );
	$actions['tcb'] = '<span class="thrive-adminbar-icon"></span><a target="_blank" href="' . $url . '">' . __( 'Edit with Thrive Architect', 'thrive-cb' ) . '</a>';

	return $actions;
}

/**
 * Load meta tags for social media and others
 *
 * @param int $post_id
 */
function tve_load_meta_tags( $post_id = 0 ) {

	if ( empty( $post_id ) ) {
		$post_id = get_the_ID();
	}
	$globals = tve_get_post_meta( $post_id, 'tve_globals' );
	if ( ! empty( $globals['fb_comment_admins'] ) ) {
		$fb_admins = json_decode( $globals['fb_comment_admins'] );
		if ( ! empty( $fb_admins ) && is_array( $fb_admins ) ) {
			foreach ( $fb_admins as $admin ) {
				echo '<meta property="fb:admins" content="' . $admin . '"/>';
			}
		}
	}
}


/**
 * it's a hook on the wp_head WP action
 *
 * outputs the CSS needed for the custom fonts
 */
function tve_load_font_css() {
	do_action( 'tcb_extra_fonts_css' );

	$all_fonts = tve_get_all_custom_fonts();
	if ( empty( $all_fonts ) ) {
		return;
	}
	echo '<style type="text/css">';

	/** @var array $css prepare and array of css classes what will have as value an array of css rules */
	$css = array();
	foreach ( $all_fonts as $font ) {
		$css[ $font->font_class ] = array(
			'font-family: ' . tve_prepare_font_family( $font->font_name ) . ' !important;',
		);
		$font_weight              = preg_replace( '/[^0-9]/', '', $font->font_style );
		$font_style               = preg_replace( '/[0-9]/', '', $font->font_style );
		if ( ! empty( $font->font_color ) ) {
			$css[ $font->font_class ][] = "color: {$font->font_color};";
		}
		if ( ! empty( $font_weight ) ) {
			$css[ $font->font_class ][] = "font-weight: {$font_weight} !important;";
		}
		if ( ! empty( $font_style ) ) {
			$css[ $font->font_class ][] = "font-style: {$font_style};";
		}
		if ( ! empty( $font->font_bold ) ) {
			$arr_key         = "{$font->font_class}.bold_text,.{$font->font_class} .bold_text,.{$font->font_class} b,.{$font->font_class} strong";
			$css[ $arr_key ] = array(
				"font-weight: {$font->font_bold} !important;"
			);
		}
	}

	/**
	 * Loop through font classes and display their css properties
	 *
	 * @var string $font_class
	 * @var array  $rules
	 */
	foreach ( $css as $font_class => $rules ) {
		/** add font css rules to the page */
		echo "#tve_editor .{$font_class}{" . implode( '', $rules ) . '}';
		/** set the font css rules for inputs also */
		echo ".{$font_class} input, .{$font_class} select, .{$font_class} textarea, .{$font_class} button {" . implode( '', $rules ) . '}';
	}

	echo '</style>';

}

/**
 * output the css for the $fonts array
 *
 * @param array $fonts
 */
function tve_output_custom_font_css( $fonts ) {
	echo '<style type="text/css">';

	/** @var array $css prepare and array of css classes what will have as value an array of css rules */
	$css = array();
	foreach ( $fonts as $font ) {
		$font                     = (object) $font;
		$css[ $font->font_class ] = array(
			'font-family: ' . ( strpos( $font->font_name, ',' ) === false ? "'" . $font->font_name . "'" : $font->font_name ) . ' !important;',
		);

		$font_weight = preg_replace( '/[^0-9]/', '', $font->font_style );
		$font_style  = preg_replace( '/[0-9]/', '', $font->font_style );
		if ( ! empty( $font->font_color ) ) {
			$css[ $font->font_class ][] = "color: {$font->font_color} !important;";
		}
		if ( ! empty( $font_weight ) ) {
			$css[ $font->font_class ][] = "font-weight: {$font_weight} !important;";
		}
		if ( ! empty( $font_style ) ) {
			$css[ $font->font_class ][] = "font-style: {$font_style};";
		}
		if ( ! empty( $font->font_bold ) ) {
			$font_key         = "{$font->font_class}.bold_text,.{$font->font_class} .bold_text,.{$font->font_class} b,.{$font->font_class} strong";
			$css[ $font_key ] = array(
				"font-weight: {$font->font_bold} !important;"
			);
		}
	}

	/**
	 * Loop through font classes and display their css properties
	 *
	 * @var string $font_class
	 * @var array  $rules
	 */
	foreach ( $css as $font_class => $rules ) {
		/** add font css rules to the page */
		echo ".{$font_class}{" . implode( '', $rules ) . '}';
		/** set the font css rules for inputs also */
		echo ".{$font_class} input, .{$font_class} select, .{$font_class} textarea, .{$font_class} button {" . implode( '', $rules ) . '}';
	}

	echo '</style>';
}

/**
 * Prepare font family name to be added to css rule
 *
 * @param $font_family
 */
function tve_prepare_font_family( $font_family ) {
	$chunks = explode( ',', $font_family );
	$length = count( $chunks );
	$font   = '';
	foreach ( $chunks as $key => $value ) {
		$font .= "'" . trim( $value ) . "'";
		$font .= ( $key + 1 ) < $length ? ', ' : '';
	}

	return $font;
}

/**
 * TODO: I think this one can be removed since we do not display admin bar in Thrive Architect Editor Page
 *
 * adds an icon and link to the admin bar for quick access to the editor. Only shows when not already in Thrive Architect
 *
 * @param WP_Admin_Bar $wp_admin_bar
 */
function thrive_editor_admin_bar( $wp_admin_bar ) {
	$theme = wp_get_theme();
	// SUPP-1408 Hive theme leaves the query object in an unknown state
	if ( 'Hive' == $theme->name || 'Hive' == $theme->parent_theme ) {
		wp_reset_query();
	}
	$post_id = get_the_ID();
	if ( is_admin_bar_showing() && ( is_single() || is_page() ) && tve_is_post_type_editable( get_post_type() ) && current_user_can( 'edit_post', $post_id ) ) {

		if ( ! isset( $_GET[ TVE_EDITOR_FLAG ] ) ) {
			$editor_link = tcb_get_editor_url( $post_id );
			$args        = array(
				'id'    => 'tve_button',
				'title' => '<span class="thrive-adminbar-icon"><img src="' . tve_editor_css() . '/images/thrive-architect-logo-white.png" style="width:1em;"/></span>' . __( 'Edit with Thrive Architect', 'thrive-cb' ),
				'href'  => $editor_link,
				'meta'  => array(
					'class' => 'thrive-admin-bar',
					'html'  => '<style>.thrive-adminbar-icon{margin-right:5px !important;}</style>',
				),
			);
		} elseif ( get_post_type() == 'post' || get_post_type() == 'page' ) {
			$close_editor_link = tcb_get_editor_close_url( $post_id );
			$args              = array(
				'id'    => 'tve_button',
				'title' => '<span class="thrive-adminbar-icon"></span>' . __( 'Close Thrive Architect', 'thrive-cb' ),
				'href'  => $close_editor_link,
				'meta'  => array(
					'class' => 'thrive-admin-bar',
				),
			);
		} else {
			return;
		}

		$wp_admin_bar->add_node( $args );
	}
}

/**
 * Checks for [embed] shortcodes inside the content and uses the run_shortcode() function from class-wp-embed.php to render them instead of using do_shortcode() .
 *
 * @param $content
 *
 * @return mixed
 */
function tve_handle_embed_shortcode( $content ) {
	/* if we find an [embed] tag, give the content to the run_shortcode() function from class-wp-embed */
	if ( strpos( $content, '[embed' ) !== false ) {
		global $wp_embed;
		$content = $wp_embed->run_shortcode( $content );
	}

	return $content;
}

/**
 * add the editor content to $content, but at priority 101 so not affected by custom theme shortcode functions that are common with some theme developers
 *
 * @param string      $content  the post content
 * @param null|string $use_case used to control the output, e.g. it can be used to return just TCB content, not full content
 *
 * @return string
 */
function tve_editor_content( $content, $use_case = null ) {
	global $post;

	$tcb_post = tcb_post( $post );

	$post_id = get_the_ID();

	if ( isset( $GLOBALS['TVE_CONTENT_SKIP_ONCE'] ) ) {
		unset( $GLOBALS['TVE_CONTENT_SKIP_ONCE'] );

		return $content;
	}

	/**
	 * check if current post is protected by a membership plugin
	 */
	if ( ! tve_membership_plugin_can_display_content() ) {
		return $content;
	}

	if ( ! tve_is_post_type_editable( get_post_type( $post_id ) ) ) {
		return $content;
	}

	$is_landing_page   = tve_post_is_landing_page( $post_id );
	$tcb_force_excerpt = false;

	if ( $use_case !== 'tcb_content' && post_password_required( $post ) ) {
		return $is_landing_page ? '<div class="tve-lp-pw-form">' . get_the_password_form( $post ) . '</div>' : $content;
	}

	if ( is_editor_page() ) {

		// this is an editor page
		$tve_saved_content = tve_get_post_meta( $post_id, 'tve_updated_post', true );

		/**
		 * SUPP-4806 Conflict (max call stack exceeded most likely) with Yoast SEO Address / Map Widgets
		 */
		if ( doing_filter( 'get_the_excerpt' ) || doing_filter( 'the_excerpt' ) ) {
			return $tve_saved_content . $content;
		}

		/**
		 * If there is no TCB-saved content, but the post / page contains WP content, create a WP-Content element in TCB containing everything from WP
		 */
		if ( empty( $tve_saved_content ) ) {
			$tve_saved_content = $tcb_post->get_wp_element();
			$tcb_post->meta( 'tcb2_ready', 1 );
		}
	} else {
		/* SUPP-2680 - removed the custom css display from here - it's loaded from the wp_enqueue_scripts hook */

		if ( $use_case !== 'tcb_content' ) { // do not trucate the contents if we require it all
			/* if the editor was specifically disabled for this post, just return the content */
			if ( $tcb_post->editor_disabled() ) {
				return $content;
			}
			/**
			 * do not truncate the post content if the current page is a feed and the option for the feed display is "Full text"
			 */
			$rss_use_excerpt = false;
			if ( is_feed() ) {
				$rss_use_excerpt = (bool) get_option( 'rss_use_excerpt' );
			}
			$tcb_force_excerpt = apply_filters( 'tcb_force_excerpt', false );
			if ( $rss_use_excerpt || ! is_singular() || $tcb_force_excerpt ) {
				$more_found          = tve_get_post_meta( get_the_ID(), 'tve_content_more_found', true );
				$content_before_more = tve_get_post_meta( get_the_ID(), 'tve_content_before_more', true );
				if ( ! empty( $content_before_more ) && $more_found ) {
					if ( is_feed() ) {
						$more_link = ' [&#8230;]';
					} else {
						$more_link = apply_filters( 'the_content_more_link', ' <a href="' . get_permalink() . '#more-' . $post->ID . '" class="more-link">' . __( 'Continue Reading', 'thrive-cb' ) . '</a>', __( 'Continue Reading', 'thrive-cb' ) );
					}

					$tve_saved_content = $content_before_more . $more_link;
					$tve_saved_content = force_balance_tags( $tve_saved_content );
					$content           = ''; /* clear out anything else after this point */
					$content_trimmed   = true;
				} elseif ( is_feed() && $rss_use_excerpt ) {
					$rss_content = tve_get_post_meta( $post_id, 'tve_updated_post', true ) . $content;
					if ( $rss_content ) {
						$tve_saved_content = wp_trim_excerpt( $rss_content );
					}
					$content_trimmed = true;
				}
			}
		}

		if ( ! isset( $tve_saved_content ) ) {
			$tve_saved_content = tve_get_post_meta( $post_id, 'tve_updated_post', true );
			$tve_saved_content = tve_restore_script_tags( $tve_saved_content );
		}
		if ( empty( $tve_saved_content ) ) {
			// return empty content if nothing is inserted in the editor - this is to make sure that first page section on the page will actually be displayed ok
			return $use_case === 'tcb_content' ? '' : $content;
		}

		$tve_saved_content = tve_compat_content_filters_before_shortcode( $tve_saved_content );

		/* prepare Events configuration */
		if ( ! is_feed() && ( in_the_loop() || $is_landing_page ) ) {
			// append lightbox HTML to the end of the body
			tve_parse_events( $tve_saved_content );
		}

		/* make images responsive */
		if ( function_exists( 'wp_make_content_images_responsive' ) ) {
			$tve_saved_content = wp_make_content_images_responsive( $tve_saved_content );
		}
	}

	$tve_saved_content = tve_thrive_shortcodes( $tve_saved_content, is_editor_page() );

	/* render the content added through WP Editor (element: "WordPress Content") */
	$tve_saved_content = tve_do_wp_shortcodes( $tve_saved_content, is_editor_page() );

	if ( ! is_editor_page() ) {
		//for the case when user put a shortcode inside a "p" element
		$tve_saved_content = shortcode_unautop( $tve_saved_content );

		/* search for WP's <!--more--> tag and split the content based on that */
		if ( $use_case !== 'tcb_content' && ( ! is_singular() || $tcb_force_excerpt ) && ! isset( $content_trimmed ) ) {
			if ( preg_match( '#<!--more(.*?)?-->#', $tve_saved_content, $m ) ) {
				list( $tve_saved_content ) = explode( $m[0], $tve_saved_content, 2 );
				$tve_saved_content = preg_replace( '#<p>$#s', '', $tve_saved_content );
				$more_link         = apply_filters( 'the_content_more_link', ' <a href="' . get_permalink() . '#more-' . $post->ID . '" class="more-link">' . __( 'Continue Reading', 'thrive-cb' ) . '</a>', __( 'Continue Reading', 'thrive-cb' ) );
				$tve_saved_content = force_balance_tags( $tve_saved_content . $more_link );
			}
		}

		/* fix for SUPP-5168, treat [embed] shortcodes separately by delegating the shortcode function to class-wp-embed.php */
		$tve_saved_content = tve_handle_embed_shortcode( $tve_saved_content );

		if ( $is_landing_page ) {
			$tve_saved_content = do_shortcode( $tve_saved_content );
			$tve_saved_content = tve_compat_content_filters_after_shortcode( $tve_saved_content );
		} else {
			$theme = wp_get_theme();
			/**
			 * Stendhal theme removes the default WP do_shortcode on the_content filter and adds their own. not sure why
			 */
			if ( $theme->name === 'Stendhal' || $theme->parent_theme === 'Stendhal' ) {
				$tve_saved_content = do_shortcode( $tve_saved_content );
			}
		}
	}

	$style_family_class = tve_get_style_family_class( $post_id );

	$style_family_id = is_singular() ? ' id="' . $style_family_class . '" ' : ' ';

	$wrap = array(
		'start' => '<div' . $style_family_id . 'class="' . $style_family_class . '"><div id="tve_editor" class="tve_shortcode_editor">',
		'end'   => '</div></div>',
	);

	if ( is_feed() ) {
		$wrap['start'] = $wrap['end'] = '';
	} elseif ( is_editor_page() && get_post_type( $post_id ) == 'tcb_lightbox' ) {
		$wrap['start'] .= '<div class="tve_p_lb_control tve_editor_main_content tve_content_save tve_empty_dropzone">';
		$wrap['end']   .= '</div>';
	}

	if ( tve_get_post_meta( $post_id, 'thrive_icon_pack' ) ) {
		TCB_Icon_Manager::enqueue_icon_pack();
	}

	tve_enqueue_extra_resources( $post_id );

	/**
	 * fix for LG errors being included in the page
	 */
	$tve_saved_content = preg_replace_callback( '/__CONFIG_lead_generation__(.+?)__CONFIG_lead_generation__/s', 'tcb_lg_err_inputs', $tve_saved_content );

	if ( ! is_editor_page() ) {
		$tve_saved_content = apply_filters( 'tcb_clean_frontend_content', $tve_saved_content );
	}

	$tve_saved_content = tcb_remove_deprecated_strings( $tve_saved_content );

	if ( $use_case === 'tcb_content' ) {
		return $tve_saved_content;
	}

	if ( doing_filter( 'get_the_excerpt' ) ) {
		/* add some space for when the content is stripped for the excerpt */
		$tve_saved_content = str_replace( '</p><p>', '</p>&nbsp;<p>', $tve_saved_content );
	}

	return $wrap['start'] . $tve_saved_content . $wrap['end'] . $content;
}

/**
 * Pre-process of content before serving it - remove some of the problem strings reported by customers
 * Ensure backward-compatibility with fixed issues - e.g. remove "noopener" and "noreferrer" attributes
 *
 * @param string $content
 *
 * @return string
 */
function tcb_remove_deprecated_strings( $content ) {
	$content = str_replace( array( ' data-default="Your Heading Here"', ' data-default="Enter your text here..."' ), array( '', '' ), $content );
	$content = str_replace( array( ' rel="noopener noreferrer"', ' rel="noreferrer noopener"' ), '', $content );
	$content = str_replace( array( ' rel="nofollow noopener noreferrer"', ' rel="noreferrer noopener nofollow"' ), ' rel="nofollow"', $content );
	$content = str_replace( array( ' rel="noopener nofollow noreferrer"', ' rel="noreferrer nofollow noopener"' ), ' rel="nofollow"', $content );

	/**
	 * Action filter - remove deprecated texts
	 */
	return apply_filters( 'tcb_remove_deprecated_strings', $content );
}

/**
 * Filter the wp content out of the post for posts that only use TCB content
 *
 * @param string $content
 *
 * @return string
 */
function tve_clean_wp_editor_content( $content ) {
	if ( post_password_required() || ! tve_is_post_type_editable( get_post_type() ) ) {
		return $content;
	}

	if ( ! tve_membership_plugin_can_display_content() ) {
		return $content;
	}

	$tcb_post = tcb_post();

	/**
	 * Optimize Press Conflict With TAR
	 * Is the page is an optimize press page, we return the page
	 */
	$is_optimize_press_page = get_post_meta( $tcb_post->ID, '_optimizepress_pagebuilder', true );
	if ( ! empty( $is_optimize_press_page ) ) {
		return $content;
	}

	if ( $tcb_post->meta( 'tcb_editor_enabled' ) ) {
		$content = '<div class="tcb_flag" style="display: none"></div>';
	} elseif ( $tcb_post->meta( 'tcb2_ready' ) && is_editor_page() ) {
		$content = '<div class="tcb_flag" style="display: none"></div>';
	}

	return $content;
}

/**
 * check if there are any extra icon packs needed on the current page / post
 *
 * @param $post_id
 */
function tve_enqueue_extra_resources( $post_id ) {
	$globals = tve_get_post_meta( $post_id, 'tve_globals' );

	if ( ! empty( $globals['used_icon_packs'] ) && ! empty( $globals['extra_icons'] ) ) {
		$used_icons_font_family = $globals['used_icon_packs'];

		foreach ( $globals['extra_icons'] as $icon_pack ) {
			if ( ! in_array( $icon_pack['font-family'], $used_icons_font_family ) ) {
				continue;
			}
			wp_enqueue_style( md5( $icon_pack['css'] ), tve_url_no_protocol( $icon_pack['css'] ) );
		}
	}

	/* any of the extra imported fonts - only in case of imported landing pages */
	if ( ! empty( $globals['extra_fonts'] ) ) {
		foreach ( $globals['extra_fonts'] as $font ) {
			if ( empty( $font['ignore'] ) ) {
				wp_enqueue_style( md5( $font['font_url'] ), tve_url_no_protocol( $font['font_url'] ) );
			}
		}
	}
}

/**
 * Fix added by Paul McCarthy - 25th September 2014.
 * This is a fix for the theme called "Pitch" that applies a filter to wordpress media gallery that runs a backend only native Wordpress function get_current_screen()
 * As we're loading the media library in the front end, the function that's called doesn't exist and causes a fatal error
 * This function removes the filter so that it isn't processed while in Thrive Editor mode.
 */
function tve_turn_off_get_current_screen() {
	if ( is_editor_page() ) {
		remove_filter( 'media_view_strings', 'siteorigin_settings_media_view_strings', 10 );
	}
}

/**
 * wrapper over the wp enqueue_style function
 * it will append the TVE_VERSION as a query string parameter to the $src if $ver is left empty
 *
 * @param       $handle
 * @param       $src
 * @param array $deps
 * @param bool  $ver
 * @param       $media
 */
function tve_enqueue_style( $handle, $src, $deps = array(), $ver = false, $media = 'all' ) {
	if ( $ver === false ) {
		$ver = TVE_VERSION;
	}
	wp_enqueue_style( $handle, $src, $deps, $ver, $media );
}

/**
 * wrapper over the wp_enqueue_script functions
 * it will add the plugin version to the script source if no version is specified
 *
 * @param        $handle
 * @param string $src
 * @param array  $deps
 * @param bool   $ver
 * @param bool   $in_footer
 */
function tve_enqueue_script( $handle, $src = '', $deps = array(), $ver = false, $in_footer = false ) {
	if ( $ver === false ) {
		$ver = TVE_VERSION;
	}
	wp_enqueue_script( $handle, $src, $deps, $ver, $in_footer );
}

/**
 * some features in the editor can only be displayed if we have knowledge about the theme and thus should only display on a thrive theme (borderless content for instance)
 * this function checks the global variable that's set in all thrive themes to check if the user is using a thrive theme or not
 **/
function tve_check_if_thrive_theme() {
	global $is_thrive_theme;
	if ( isset( $is_thrive_theme ) && $is_thrive_theme == true ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Hides thrive editor custom fields from being modified in the standard WP post / page edit screen
 *
 * @param $protected
 * @param $meta_key
 *
 * @return bool
 */
function tve_hide_custom_fields( $protected, $meta_key ) {
	if ( strpos( $meta_key, 'tve_revision_' ) === 0 ) {
		return true;
	}

	$keys                   = array(
		'tve_save_post',
		'tve_updated_post',
		'tve_content_before_more_shortcoded',
		'tve_content_before_more',
		'tve_style_family',
		'tve_updated_post_shortcoded',
		'tve_user_custom_css',
		'tve_custom_css',
		'tve_content_more_found',
		'tve_disable_theme_dependency',
		'tve_landing_page',
		'thrive_post_fonts',
		'thrive_tcb_post_fonts',
		'tve_globals',
		'tve_special_lightbox',
		'thrive_icon_pack',
		'tve_global_scripts',
		'tve_has_masonry',
		'tve_page_events',
		'tve_typefocus',
		'tve_has_wistia_popover',
		'tcb2_ready',
		'tcb_editor_enabled',
		'tcb_editor_disabled',
	);
	$landing_page_templates = array_keys( include dirname( dirname( __FILE__ ) ) . '/landing-page/templates/_config.php' );

	foreach ( $keys as $key ) {
		if ( $key == $meta_key || strpos( $meta_key, $key ) === 0 ) {
			return true;
		}
		foreach ( $landing_page_templates as $suffix ) {
			if ( $key . '_' . $suffix == $meta_key ) {
				return true;
			}
		}
	}

	return $protected;
}

/**
 * This is a replica of the WP function get_extended
 * The returned array has 'main', 'extended', and 'more_text' keys. Main has the text before
 * the <code><!--tvemore--></code>. The 'extended' key has the content after the
 * <code><!--tvemore--></code> comment. The 'more_text' key has the custom "Read More" text.
 *
 * @param string $post Post content.
 *
 * @return array Post before ('main'), after ('extended'), and custom readmore ('more_text').
 */
function tve_get_extended( $post ) {

	//Match the "More..." nodes
	$more_tag = '#<!--tvemorestart-->(.+?)<!--tvemoreend-->#s';

	if ( preg_match( $more_tag, $post, $matches ) ) {
		list( $main, $extended ) = explode( $matches[0], $post, 2 );
		$more_text  = $matches[1];
		$more_found = true;
	} else {
		$main       = $post;
		$extended   = '';
		$more_text  = '';
		$more_found = false;
	}

	// ` leading and trailing whitespace
	$main      = preg_replace( '/^[\s]*(.*)[\s]*$/', '\\1', $main );
	$extended  = preg_replace( '/^[\s]*(.*)[\s]*$/', '\\1', $extended );
	$more_text = preg_replace( '/^[\s]*(.*)[\s]*$/', '\\1', $more_text );

	return array(
		'main'       => $main,
		'extended'   => $extended,
		'more_text'  => $more_text,
		'more_found' => $more_found,
	);
}

/**
 * Adds inline script to hide more tag from the front end display
 */
function tve_hide_more_tag() {
	echo '<style type="text/css">.tve_more_tag {visibility: hidden; height: 1px!important;}</style>';
}

/**
 * if the current post is a landing page created with TCB, forward the control over to the landing page layout.php file
 *
 * if the current post is a Thrive CB Lightbox, display it on a page that will mimic it's behaviour (semi-transparent background, close button etc)
 *
 * if there is a hook registered for displaying content, call that hook
 *
 * @return bool
 */
function tcb_custom_editable_content() {
	// don't apply template redirects unless single post / page is being displayed.
	if ( ! apply_filters( 'tcb_is_editor_page', is_singular() ) || is_feed() || is_comment_feed() ) {
		return false;
	}

	$tcb_inactive = defined( 'EXTERNAL_TCB' ) && EXTERNAL_TCB === 0;

	$post_id   = get_the_ID();
	$post_type = get_post_type( $post_id );

	/**
	 * the filter should append its own custom templates based on the post ID / type
	 * if this array is not empty, it will use the first found file from this array as the post content template
	 */
	$custom_post_layouts = apply_filters( 'tcb_custom_post_layouts', array(), $post_id, $post_type );

	/* For TCB, we only have tcb_lightbox and landing pages editable with a separate layout */
	if ( $post_type != 'tcb_lightbox' && ! ( $lp_template = tve_post_is_landing_page( $post_id ) ) && empty( $custom_post_layouts ) ) {
		return false;
	}

	$landing_page_dir = plugin_dir_path( dirname( __FILE__ ) ) . 'landing-page';

	if ( ! $tcb_inactive && $post_type == 'tcb_lightbox' ) {
		tcb_lightbox( $post_id )->output_layout();
		exit();
	}

	if ( ! $tcb_inactive && ! empty( $lp_template ) ) {
		/**
		 * first, check if a membership plugin is protecting this page and, if the user does not have access, just proceed with the regular page content
		 */
		if ( ! tve_membership_plugin_can_display_content() ) {
			return false;
		}

		/* instantiate the $tcb_landing_page object - this is used throughout the layout.php for the landing page */
		$tcb_landing_page = tcb_landing_page( $post_id, $lp_template );

		$GLOBALS['tcb_lp_template']  = $lp_template;
		$GLOBALS['tcb_landing_page'] = $tcb_landing_page;

		/* base CSS file for all Page Templates */
		if ( ! tve_check_if_thrive_theme() ) {
			tve_enqueue_style( 'tve_landing_page_base_css', TVE_LANDING_PAGE_TEMPLATE . '/css/base.css', 99 );
		}

		$tcb_landing_page->enqueue_css();
		$tcb_landing_page->ensure_external_assets();

		include_once ABSPATH . '/wp-admin/includes/plugin.php';
		if ( is_editor_page() || ! tve_hooked_in_template_redirect() ) {

			/**
			 * added this here, because setting up a Landing Page as the homepage of your site would cause WP to not redirect properly non-www homepage to www homepage
			 */
			redirect_canonical();

			/* give the control over to the landing page template */
			include $landing_page_dir . '/layout.php';
			exit();
		}
		/**
		 * temporarily remove the_content filter for landing pages (just to not output anything in the head) - it caused issues on some shortcodes.
		 * this is re-added from the landing page layout.php file
		 */
		remove_filter( 'the_content', 'tve_editor_content' );
		/**
		 * remove thrive_template_redirect filter from the themes
		 */
		remove_filter( 'template_redirect', 'thrive_template_redirect' );

		/**
		 * this is a fix for conflicts appearing with various membership / coming soon plugins that use the template_redirect hook
		 */
		remove_all_filters( 'template_include' );
		add_filter( 'template_include', 'tcb_get_landing_page_template_layout' );
		/**
		 * make sure we'll have at least one of these fired
		 */
		add_filter( 'page_template', 'tcb_get_landing_page_template_layout' );

	} elseif ( $post_type != 'post' && $post_type != 'page' && ! empty( $custom_post_layouts ) && is_array( $custom_post_layouts ) ) {

		/**
		 * loop through each of the post_custom_layouts files array to find the first valid one
		 *
		 * TODO: we need to enforce the checks we perform here
		 */
		foreach ( $custom_post_layouts as $file ) {
			$file = @realpath( $file );
			if ( ! is_file( $file ) ) {
				continue;
			}
			include $file;
			exit();
		}
	}
}

/**
 * @param string $template
 *
 * @return string the full path to the landing page layout template
 */
function tcb_get_landing_page_template_layout( $template ) {
	return plugin_dir_path( dirname( __FILE__ ) ) . 'landing-page/layout.php';
}

/**
 * parse and prepare all the required configuration for the different events
 *
 * @param string $content TCB - meta post content
 */
function tve_parse_events( & $content ) {
	list( $start, $end ) = array(
		'__TCB_EVENT_',
		'_TNEVE_BCT__',
	);
	if ( strpos( $content, $start ) === false ) {
		return;
	}
	$triggers = tve_get_event_triggers();
	$actions  = tve_get_event_actions();

	$event_pattern = "#data-tcb-events=('|\"){$start}(.+?){$end}('|\")#";

	/* hold all the javascript callbacks required for the identified actions */
	$javascript_callbacks = isset( $GLOBALS['tve_event_manager_callbacks'] ) ? $GLOBALS['tve_event_manager_callbacks'] : array();
	/* holds all the Global JS required by different actions and event triggers on page load */
	$registered_javascript_globals = isset( $GLOBALS['tve_event_manager_global_js'] ) ? $GLOBALS['tve_event_manager_global_js'] : array();

	/* hold all instances of the Action classes in order to output stuff in the footer, we need to get out of the_content filter */
	$registered_actions = isset( $GLOBALS['tve_event_manager_actions'] ) ? $GLOBALS['tve_event_manager_actions'] : array();

	/*
     * match all instances for Event Configurations
     */
	if ( preg_match_all( $event_pattern, $content, $matches, PREG_OFFSET_CAPTURE ) !== false ) {

		foreach ( $matches[2] as $i => $data ) {
			$m = htmlspecialchars_decode( $data[0] ); // the actual matched regexp group
			if ( ! ( $_params = json_decode( $m, true ) ) ) {
				$_params = array();
			}
			if ( empty( $_params ) ) {
				continue;
			}

			foreach ( $_params as $index => $event_config ) {
				if ( empty( $event_config['t'] ) || empty( $event_config['a'] ) || ! isset( $triggers[ $event_config['t'] ] ) || ! isset( $actions[ $event_config['a'] ] ) ) {
					continue;
				}
				/** @var TCB_Event_Action_Abstract $action */
				$action                = clone $actions[ $event_config['a'] ];
				$registered_actions [] = array(
					'class'        => $action,
					'event_config' => $event_config,
				);

				if ( ! isset( $javascript_callbacks[ $event_config['a'] ] ) ) {
					$javascript_callbacks[ $event_config['a'] ] = $action->getJsActionCallback();
				}
				if ( ! isset( $registered_javascript_globals[ 'action_' . $event_config['a'] ] ) ) {
					$registered_javascript_globals[ 'action_' . $event_config['a'] ] = $action;
				}
				if ( ! isset( $registered_javascript_globals[ 'trigger_' . $event_config['t'] ] ) ) {
					$registered_javascript_globals[ 'trigger_' . $event_config['t'] ] = $triggers[ $event_config['t'] ];
				}
			}
		}
	}

	if ( empty( $javascript_callbacks ) ) {
		return;
	}

	/* we need to add all the javascript callbacks into the page */
	/* this cannot be done using wp_localize_script WP function, as each if the callback will actually be JS code */
	///euuuughhh

	//TODO: how could we handle this in a more elegant fashion ?
	$GLOBALS['tve_event_manager_callbacks'] = $javascript_callbacks;
	$GLOBALS['tve_event_manager_global_js'] = $registered_javascript_globals;
	$GLOBALS['tve_event_manager_actions']   = $registered_actions;

	/* execute the mainPostCallback on all of the related actions, some of them might need to register stuff (e.g. lightboxes) */
	foreach ( $GLOBALS['tve_event_manager_actions'] as $key => $item ) {
		if ( empty( $item['main_post_callback_'] ) ) {
			$GLOBALS['tve_event_manager_actions'][ $key ]['main_post_callback_'] = true;
			$result                                                              = $item['class']->mainPostCallback( $item['event_config'] );
			if ( is_string( $result ) ) {
				$content .= $result;
			}
		}
	}

	/* remove previously assigned callback, if any - in case of list pages */
	remove_action( 'wp_print_footer_scripts', 'tve_print_footer_events', - 50 );
	add_action( 'wp_print_footer_scripts', 'tve_print_footer_events', - 50 );

}

/**
 * load up all event manager callbacks into the page
 */
function tve_print_footer_events() {
	if ( ! empty( $GLOBALS['tve_event_manager_callbacks'] ) ) {
		echo '<script type="text/javascript">var TVE_Event_Manager_Registered_Callbacks = TVE_Event_Manager_Registered_Callbacks || {};';
		foreach ( $GLOBALS['tve_event_manager_callbacks'] as $key => $js_function ) {
			echo 'TVE_Event_Manager_Registered_Callbacks.' . $key . ' = ' . $js_function . ';';
		}
		echo '</script>';
	}

	if ( ! empty( $GLOBALS['tve_event_manager_triggers'] ) ) {
		echo '<script type="text/javascript">';
		foreach ( $GLOBALS['tve_event_manager_triggers'] as $data ) {
			if ( ! empty( $data['class'] ) && $data['class'] instanceof TCB_Event_Trigger_Abstract ) {
				$js_code = $data['class']->getInstanceJavascript( $data['event_config'] );
				if ( ! $js_code ) {
					continue;
				}
				echo '(function(){' . $js_code . '})();';
			}
		}
		echo '</script>';
	}

	if ( ! empty( $GLOBALS['tve_event_manager_global_js'] ) ) {
		foreach ( $GLOBALS['tve_event_manager_global_js'] as $object ) {
			$object->outputGlobalJavascript();
		}
	}

	if ( ! empty( $GLOBALS['tve_event_manager_actions'] ) ) {
		foreach ( $GLOBALS['tve_event_manager_actions'] as $data ) {
			if ( ! empty( $data['class'] ) && $data['class'] instanceof TCB_Event_Action_Abstract ) {
				echo $data['class']->applyContentFilter( $data['event_config'] );
			}
		}
	}
}

/**
 * fills in some default font data and adds the custom font to the custom fonts list
 *
 * @return array the full array for the added font
 */
function tve_add_custom_font( $font_data ) {
	$custom_fonts = tve_get_all_custom_fonts();

	if ( ! isset( $font_data['font_id'] ) ) {
		$font_data['font_id'] = count( $custom_fonts ) + 1;
	}

	if ( ! isset( $font_data['font_class'] ) ) {
		$font_data['font_class'] = 'ttfm' . $font_data['font_id'];
	}
	if ( ! isset( $font_data['custom_css'] ) ) {
		$font_data['custom_css'] = '';
	}
	if ( ! isset( $font_data['font_color'] ) ) {
		$font_data['font_color'] = '';
	}
	if ( ! isset( $font_data['font_height'] ) ) {
		$font_data['font_height'] = '1.6em';
	}
	if ( ! isset( $font_data['font_size'] ) ) {
		$font_data['font_size'] = '1.6em';
	}
	if ( ! isset( $font_data['font_character_set'] ) ) {
		$font_data['font_character_set'] = 'latin';
	}

	$custom_fonts [] = $font_data;

	update_option( 'thrive_font_manager_options', json_encode( $custom_fonts ) );

	return $font_data;
}

/**
 * run any necessary code that would be required during an upgrade
 *
 * @param $old_version
 * @param $new_version
 */
function tve_run_plugin_upgrade( $old_version, $new_version ) {
	if ( version_compare( $old_version, '1.74', '<' ) ) {
		/**
		 * refactoring of user templates
		 */
		$user_templates = get_option( 'tve_user_templates', array() );
		$css            = get_option( 'tve_user_templates_styles' );
		$new_templates  = array();
		if ( ! empty( $user_templates ) ) {
			foreach ( $user_templates as $name => $content ) {
				if ( is_array( $content ) ) {
					continue;
				}
				$found            = true;
				$new_templates [] = array(
					'name'    => urldecode( stripslashes( $name ) ),
					'content' => stripslashes( $content ),
					'css'     => isset( $css[ $name ] ) ? trim( stripslashes( $css[ $name ] ) ) : '',
				);
			}
		}

		if ( isset( $found ) ) {
			usort( $new_templates, 'tve_tpl_sort' );
			update_option( 'tve_user_templates', $new_templates );
			delete_option( 'tve_user_templates_styles' );
		}
	}
}

/**
 * determine whether the user is on the editor page or not (also takes into account edit capabilities)
 *
 * @return bool
 */
function is_editor_page() {
	/**
	 * during AJAX calls, we need to apply a filter to get this value, we cannot rely on the traditional detection
	 */
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		$is_editor_page = apply_filters( 'tcb_is_editor_page_ajax', false );
		if ( $is_editor_page ) {
			return true;
		}
	}

	if ( apply_filters( 'tcb_is_inner_frame_override', false ) ) {
		return true;
	}

	if ( ! apply_filters( 'tcb_is_editor_page', is_singular() ) ) {
		return false;
	}

	if ( isset( $_GET[ TVE_EDITOR_FLAG ] ) && ( current_user_can( 'edit_post', get_the_ID() ) ) && tve_membership_plugin_can_display_content() ) {
		return true;
	} else {
		return false;
	}
}

/**
 * check if there is a valid activated license for the TCB plugin
 *
 * @return bool
 */
function tve_tcb__license_activated() {
	return true;
}

/**
 * determine whether the user is on the editor page or not based just on a $_GET parameter
 * modification: WP 4 removed the "preview" parameter
 *
 * @return bool
 */
function is_editor_page_raw() {
	/**
	 * during AJAX calls, we need to apply a filter to get this value, we cannot rely on the traditional detection
	 */
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		$is_editor_page = apply_filters( 'tcb_is_editor_page_raw_ajax', false );
		if ( $is_editor_page ) {
			return true;
		}
	}
	if ( isset( $_GET[ TVE_EDITOR_FLAG ] ) ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Removes the theme CSS from the architect page
 */
function tve_remove_theme_css() {
	global $wp_styles;

	$theme          = get_template();
	$stylesheet_dir = basename( get_stylesheet_directory() );

	foreach ( $wp_styles->queue as $handle ) {
		$src = $wp_styles->registered[ $handle ]->src;
		if ( strpos( $src, $theme ) !== false || strpos( $src, $stylesheet_dir ) !== false ) {
			wp_deregister_style( $handle );
		}
	}
}

/**
 * only enqueue scripts on our own editor pages
 */
function tve_enqueue_editor_scripts() {
	if ( is_editor_page() && tve_is_post_type_editable( get_post_type( get_the_ID() ) ) ) {

		/**
		 * the constant should be defined somewhere in wp-config.php file
		 */
		$js_suffix = defined( 'TVE_DEBUG' ) && TVE_DEBUG ? '.js' : '.min.js';

		/**
		 * this is to handle the following case: an user who has the TL plugin (or others) installed, TCB installed and enabled, but TCB license is expired
		 * in this case, users should still be able to edit stuff from outside the TCB plugin, such as forms
		 */
		if ( tve_tcb__license_activated() || apply_filters( 'tcb_skip_license_check', false ) ) {
			$post_id = get_the_ID();

			/**
			 * apply extra filters that should check if the user can actually use the editor to edit this particular piece of content
			 */
			if ( apply_filters( 'tcb_user_can_edit', true, $post_id ) ) {

				global $tve_style_family_classes;

				// Thrive Architect javascript file (loaded both frontend and backend).
				tve_enqueue_script( 'tve_frontend', tve_editor_js() . '/frontend' . $js_suffix, array(
					'jquery',
				), false, true );

				/**
				 * enqueue resizable for older WP versions
				 */
				wp_enqueue_script( 'jquery-ui-resizable' );

				wp_enqueue_script( 'tcb-froala', tve_editor_url( 'editor/js/dist/froala' . $js_suffix ), array( 'jquery' ) );

				wp_enqueue_style( 'fa', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css' );
				wp_enqueue_style( 'tcb-froala-style', 'https://cdnjs.cloudflare.com/ajax/libs/froala-editor/2.5.1/css/froala_editor.pkgd.min.css' );

				/** control panel scripts and dependencies */
				tve_enqueue_script( 'tve_editor', tve_editor_js() . '/editor' . $js_suffix, array(
					'jquery',
					'jquery-ui-autocomplete',
					'jquery-ui-slider',
					'jquery-ui-resizable',
					'tcb-froala',
				), false, true );

				// Enqueue dom-to-image script. Used for generation of the images
				wp_enqueue_script( 'tcb-dom-to-image', tve_editor_url() . '/editor/js/libs/dom-to-image.min.js', array( 'tve_editor' ) );

				// jQuery UI stuff
				// no need to append TVE_VERSION for these scripts
				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( 'jquery-serialize-object' );
				wp_enqueue_script( 'jquery-ui-core', array( 'jquery' ) );
				wp_enqueue_script( 'jquery-ui-autocomplete' );
				wp_enqueue_script( 'jquery-ui-sortable' );
				wp_enqueue_script( 'jquery-ui-slider', array( 'jquery', 'jquery-ui-core' ) );

				wp_enqueue_script( 'jquery-masonry', array( 'jquery' ) );

				// now enqueue the styles
				tve_enqueue_style( 'tve_editor_style', tve_editor_css() . '/editor.css' );
				tve_enqueue_style( 'tve_inner_style', tve_editor_css() . '/editor/style.css' );

				if ( is_rtl() ) {
					tve_enqueue_style( 'tve_rtl', tve_editor_css() . '/editor_rtl.css' );
				}

				// load style family
				$loaded_style_family = tve_get_style_family( $post_id );

				$timezone_offset = get_option( 'gmt_offset' );
				$sign            = ( $timezone_offset < 0 ? '-' : '+' );
				$min             = abs( $timezone_offset ) * 60;
				$hour            = floor( $min / 60 );
				$tzd             = $sign . str_pad( $hour, 2, '0', STR_PAD_LEFT ) . ':' . str_pad( $min % 60, 2, '0', STR_PAD_LEFT );

				// if the post is a TCB landing page, get the landing page configuration and send it to javascript
				$landing_page_config = array();
				if ( ( $template = tve_post_is_landing_page( get_the_ID() ) ) !== false ) {
				}

				// custom fonts from Font Manager
				$all_fonts         = tve_get_all_custom_fonts();
				$all_fonts_enqueue = apply_filters( 'tve_filter_custom_fonts_for_enqueue_in_editor', $all_fonts );
				tve_enqueue_fonts( $all_fonts_enqueue );

				$post_type = get_post_type( get_the_ID() );

				/**
				 * we need to enforce this check here, so that we don't make http requests from https pages
				 */
				$admin_base_url = admin_url( '/', is_ssl() ? 'https' : 'admin' );
				// for some reason, the above line does not work in some instances
				if ( is_ssl() ) {
					$admin_base_url = str_replace( 'http://', 'https://', $admin_base_url );
				}

				// pass variables needed to client side
				$tve_path_params = array(
					'admin_url'                     => $admin_base_url,
					'cpanel_dir'                    => tve_editor_url() . '/editor',
					'shortcodes_dir'                => tve_editor_url() . '/shortcodes/templates/',
					'editor_dir'                    => tve_editor_css(),
					'style_families'                => tve_get_style_families(),
					'style_classes'                 => $tve_style_family_classes,
					'loaded_style'                  => $loaded_style_family,
					'post_id'                       => get_the_ID(),
					'post_url'                      => get_permalink( get_the_ID() ),
					'tve_version'                   => TVE_VERSION,
					'tve_loaded_stylesheet'         => $loaded_style_family,
					'ajax_url'                      => $admin_base_url . 'admin-ajax.php',
					'is_rtl'                        => (int) is_rtl(),
					'custom_fonts'                  => $all_fonts,
					'post_type'                     => $post_type,
					// this is to allow overriding the default save_post action ajax callback,
					'tve_display_save_notification' => (int) get_option( 'tve_display_save_notification', 1 ),
				);

				$tve_path_params = apply_filters( 'tcb_editor_javascript_params', $tve_path_params, $post_id, $post_type );

				wp_localize_script( 'tve_editor', 'tve_path_params', $tve_path_params );

				wp_localize_script( 'tcb-froala', 'tve_froala_const', array(
					'inline_shortcodes' => apply_filters( 'tcb_inline_shortcodes', array() ),
				) );

				/* some params will be needed also for the frontend script */
				$frontend_options = array(
					'is_editor_page'   => true,
					'ajaxurl'          => admin_url( 'admin-ajax.php' ),
					'social_fb_app_id' => tve_get_social_fb_app_id(),
				);
				wp_localize_script( 'tve_frontend', 'tve_frontend_options', $frontend_options );

				do_action( 'tcb_editor_enqueue_scripts' );
			}
		} else {
			add_action( 'wp_print_footer_scripts', 'tve_license_notice' );
		}
	}
}

/**
 * enqueue the associated style family for a post / page
 *
 * this also gets called in archive (list) pages, there we need to load style families for each post from the list
 *
 * @param null $post_id optional this will only come filled in when calling it from a lightbox
 */
function tve_enqueue_style_family( $post_id = null ) {
	global $tve_style_family_classes, $wp_query;
	$tve_style_families = tve_get_style_families();

	if ( null === $post_id ) {
		$posts_to_load = $wp_query->posts;
		if ( empty( $posts_to_load ) || ! is_array( $posts_to_load ) ) {
			return;
		}
		$post_id = array();
		foreach ( $posts_to_load as $post ) {
			$post_id [] = $post->ID;
		}
	} else {
		$post_id = array( $post_id );
	}

	foreach ( $post_id as $p_id ) {
		$current_post_style = tve_get_style_family( $p_id );

		$style_key = 'tve_style_family_' . strtolower( $tve_style_family_classes[ $current_post_style ] );
		if ( ! wp_style_is( $style_key ) ) {
			tve_enqueue_style( $style_key, $tve_style_families[ $current_post_style ] );
		}
	}
}

/**
 * retrieve the style family used for a specific post / page
 *
 * @param        $post_id
 * @param string $default
 */
function tve_get_style_family( $post_id, $default = 'Flat' ) {
	$tve_style_families = tve_get_style_families();
	$current_post_style = get_post_meta( $post_id, 'tve_style_family', true );

	// Flat is default style family if nothing set
	$current_post_style = empty( $current_post_style ) || ! isset( $tve_style_families[ $current_post_style ] ) ? $default : $current_post_style;

	return $current_post_style;
}

/**
 * get the css class for a style family
 *
 * @param int $post_id
 *
 * @return string
 */
function tve_get_style_family_class( $post_id ) {
	global $tve_style_family_classes;
	$style_family = get_post_meta( $post_id, 'tve_style_family', true );

	return ! empty( $style_family ) && isset( $tve_style_family_classes[ $style_family ] ) ? $tve_style_family_classes[ $style_family ] : $tve_style_family_classes['Flat'];
}

/**
 * ajax function for updating post meta with the current style family
 */
function tve_change_style_family() {
	check_ajax_referer( 'tve-le-verify-sender-track129', 'security' );
	if ( current_user_can( 'manage_options' ) || current_user_can( 'edit_posts' ) ) {
		if ( ob_get_contents() ) {
			ob_clean();
		}
		// style family should remain the same when switching over to landing page and back
		update_post_meta( $_POST['post_id'], 'tve_style_family', $_POST['style_family'] );
		die;
	}
}

/**
 * Loads user defined custom css in the header to override style family css
 * If called with $post_id != null, it will load the custom css and user custom css from inside the loop (in case of homepage consisting of other pages, for example)
 */
function tve_load_custom_css( $post_id = null ) {
	if ( is_feed() ) {
		return;
	}
	if ( ! is_null( $post_id ) ) {
		$custom_css = trim( tve_get_post_meta( $post_id, 'tve_custom_css', true ) . tve_get_post_meta( $post_id, 'tve_user_custom_css', true ) );
		if ( $custom_css ) {
			echo sprintf(
				'<style type="text/css" class="tve_custom_style">%s</style>',
				$custom_css
			);
		}

		return;
	}
	global $wp_query;
	$posts_to_load = $wp_query->posts;

	global $css_loaded_post_id;
	$css_loaded_post_id = array();

	/* user-defined css from the Custom CSS content element */
	$user_custom_css = '';
	if ( $posts_to_load ) {

		$inline_styles = '';
		foreach ( $posts_to_load as $post ) {
			$inline_styles   .= tve_get_post_meta( $post->ID, 'tve_custom_css', true );
			$user_custom_css .= tve_get_post_meta( $post->ID, 'tve_user_custom_css', true );
			array_push( $css_loaded_post_id, $post->ID );
		}

		if ( ! empty( $inline_styles ) ) {
			?>
			<style type="text/css"
				   class="tve_custom_style"><?php echo $inline_styles ?></style><?php
		}
		/* also check for user-defined custom CSS inserted via the "Custom CSS" content editor element */
		echo $user_custom_css ? sprintf( '<style type="text/css" id="tve_head_custom_css" class="tve_user_custom_style">%s</style>', $user_custom_css ) : '';
	}
}

/**
 * checks to see if content being loaded is actually being loaded from within the loop (correctly) or being pulled
 * incorrectly to make up another page (for instance, a homepage that pulls different sections from pieces of content)
 */
function tve_check_in_loop( $post_id ) {
	global $css_loaded_post_id;
	if ( ! empty( $css_loaded_post_id ) && in_array( $post_id, $css_loaded_post_id ) ) {
		return true;
	}

	return false;
}

/**
 * replace [tcb-script] with script tags
 *
 * @param array $matches
 *
 * @return string
 */
function tve_restore_script_tags_replace( $matches ) {
	$matches[2] = str_replace( '<\\/script', '<\\\\/script', $matches[2] );

	return '<script' . $matches[1] . '>' . html_entity_decode( $matches[2] ) . '</script>';
}

/**
 * replace [tcb-noscript] with <noscript> tags
 *
 * @param array $matches
 *
 * @return string
 */
function tve_restore_script_tags_noscript_replace( $matches ) {
	return '<noscript' . $matches[1] . '>' . html_entity_decode( $matches[2] ) . '</noscript>';
}

/**
 * restore all script tags from custom html controls. script tags are replaced with <code class="tve_js_placeholder">
 *
 * @param string $content
 *
 * @return string having all <code class="tve_js_placeholder">..</code> replaced with their script tag equivalent
 */
function tve_restore_script_tags( $content ) {
	$shortcode_js_pattern = '/\[tcb-script(.*?)\](.*?)\[\/tcb-script\]/s';
	$content              = preg_replace_callback( $shortcode_js_pattern, 'tve_restore_script_tags_replace', $content );

	$shortcode_nojs_pattern = '/\[tcb-noscript(.*?)\](.*?)\[\/tcb-noscript\]/s';
	$content                = preg_replace_callback( $shortcode_nojs_pattern, 'tve_restore_script_tags_noscript_replace', $content );

	return $content;
}

/**
 * get a list of all published Thrive Opt-Ins post types
 *
 * @return array pairs id => title
 */
function tve_get_thrive_optins() {
	$optins = array();

	$args = array(
		'posts_per_page' => null,
		'numberposts'    => null,
		'post_type'      => 'thrive_optin',
	);

	foreach ( get_posts( $args ) as $post ) {
		$optins[ $post->ID ] = $post->post_title;
	}

	return $optins;
}

/**
 * Thrive Shortcode callback that will call apply_filters on "tve_additional_fields" tag
 *
 * @see tve_thrive_shortcodes
 *
 * @param array $data with [group_id, form_type_id, variation_id]
 *
 * @return mixed
 */
function tve_leads_additional_fields_filters( $data ) {
	$group     = $data['group_id'];
	$form_type = $data['form_type_id'];
	$variation = $data['variation_id'];

	if ( ! empty( $form_type ) && function_exists( 'tve_leads_get_form_type' ) ) {
		$form_type = tve_leads_get_form_type( $form_type, array( 'get_variations' => false ) );
		if ( $form_type && $form_type->post_parent ) {
			$group = get_post( $form_type->post_parent );
		}
	}

	if ( ! empty( $variation ) && function_exists( 'tve_leads_get_form_variation' ) ) {
		$variation = tve_leads_get_form_variation( null, $variation );
		if ( ! empty( $variation['parent_id'] ) ) {
			$variation = tve_leads_get_form_variation( null, $variation['parent_id'] );
		}
	}

	return apply_filters( 'tve_additional_fields', '', $group, $form_type, $variation );
}

/**
 * parse content for configuration that belongs to theme-equivalent shortcodes, e.g. Opt-in shortcode
 *
 * for each key from $tve_thrive_shortcodes, it will search the content string for __CONFIG_{$key}__(.+)__CONFIG_{$key}__
 * if elements are found, the related callback will be called with the contents from between the two flags (this is a json_encoded string)
 *
 * shortcode configuration is held in JSON-encoded format inside a hidden div
 * these contents will get deleted if we're currently NOT in editor mode
 *
 * @param string $content
 * @param bool   $keep_config
 */
function tve_thrive_shortcodes( $content, $keep_config = false ) {
	global $tve_thrive_shortcodes;

	$shortcode_pattern = '#>__CONFIG_%s__(.+?)__CONFIG_%s__</div>#';

	foreach ( $tve_thrive_shortcodes as $shortcode => $callback ) {
		if ( ! tve_check_if_thrive_theme() && $shortcode !== 'widget' && $shortcode !== 'post_grid' && $shortcode !== 'widget_menu' && $shortcode !== 'leads_shortcode' && $shortcode !== 'tve_leads_additional_fields_filters' && $shortcode !== 'social_default' && $shortcode !== 'tvo_shortcode' && $shortcode != 'ultimatum_shortcode' && $shortcode != 'quiz_shortcode' ) {
			continue;
		}

		if ( ! function_exists( $callback ) ) {
			continue;
		}

		/**
		 * we dont want to apply this shortcode if $keep_config is true => is_editor
		 */
		if ( $shortcode === 'tve_leads_additional_fields_filters' && $keep_config === true ) {
			continue;
		}

		/*
         * match all instances of the current shortcode
         */
		if ( preg_match_all( sprintf( $shortcode_pattern, $shortcode, $shortcode ), $content, $matches, PREG_OFFSET_CAPTURE ) !== false ) {
			/* as we go over the $content and replace each shortcode, we must take into account the differences of replacement length and the length of the part getting replaced */
			$position_delta = 0;
			foreach ( $matches[1] as $i => $data ) {
				$m           = $data[0]; // the actual matched regexp group
				$position    = $matches[0][ $i ][1] + $position_delta; //the index at which the whole group starts in the string, at the current match
				$whole_group = $matches[0][ $i ][0];
				$json_safe   = tve_json_utf8_slashit( $m );
				if ( ! ( $_params = @json_decode( $json_safe, true ) ) ) {
					$_params = array();
				}
				$replacement = call_user_func( $callback, $_params, $keep_config );

				$replacement = ( $keep_config ? ">__CONFIG_{$shortcode}__{$m}__CONFIG_{$shortcode}__</div>" : '></div>' ) . $replacement;

				$content = substr_replace( $content, $replacement, $position, strlen( $whole_group ) );
				/* increment the positioning offsets for the string with the difference between replacement and original string length */
				$position_delta += strlen( $replacement ) - strlen( $whole_group );

			}
		}
	}

	// we include the wistia js only if wistia popover responsive video is added to the content (div with class tve_wistia_popover)
	if ( ! $keep_config && strpos( $content, 'tve_wistia_popover' ) !== false ) {
		wp_script_is( 'tl-wistia-popover' ) || wp_enqueue_script( 'tl-wistia-popover', '//fast.wistia.com/assets/external/E-v1.js', array(), '', true );
	}

	return $content;
}

/**
 * Render post grid shortcode
 * Called from shortcode parser and when user drags element into page
 */
function tve_do_post_grid_shortcode( $config ) {

	require_once dirname( dirname( __FILE__ ) ) . '/inc/classes/class-tcb-post-grid.php';
	$post_grid = new TCB_Post_Grid( $config );

	$post_grid->output_shortcode_config = false;
	$html                               = $post_grid->render();

	return $html;
}

/**
 * handle the Opt-In shortcode from the themes
 *
 * at this point this just forwards the call to the theme's Opt-In shortcode
 *
 * TODO: perhaps we can use the call to thrive_shortcode_optin (and other shortcodes) directly (?)
 *
 * @param array $attrs
 *
 * @return string
 */
function tve_do_optin_shortcode( $attrs ) {
	return '<div class="thrive-shortcode-html">' . thrive_shortcode_optin( $attrs, '' ) . '</div>';
}

/**
 * handle the posts lists shortcode from the themes.  Full docs in function tve_do_optin_shortcode comments
 *
 * @param $attrs
 *
 * @return string
 */
function tve_do_posts_list_shortcode( $attrs ) {
	return '<div class="thrive-shortcode-html">' . thrive_shortcode_posts_list( $attrs, '' ) . '</div>';
}

/**
 * handle the leads shortcode
 *
 * @param $attr
 *
 * @return string
 */
function tve_do_leads_shortcode( $attrs ) {
	$error_content = '<div class="thrive-shortcode-html"><p>' . __( 'Thrive Leads Shortcode could not be rendered, please check it in Thrive Leads Section!', 'thrive-cb' ) . '</p></div>';
	if ( ! function_exists( 'tve_leads_shortcode_render' ) ) {
		return $error_content;
	}

	if ( is_editor_page() ) {
		$attrs['for_editor'] = true;
		$content             = tve_leads_shortcode_render( $attrs );
		$content             = ! empty( $content['html'] ) ? $content['html'] : '';
	} else {
		$content = tve_leads_shortcode_render( $attrs );
	}

	if ( empty( $content ) ) {
		return $error_content;
	}

	return '<div class="thrive-shortcode-html">' . str_replace( 'tve_editor_main_content', '', $content ) . '</div>';
}

/**
 * handle the custom menu shortcode
 *
 * @param $atts
 *
 * @return string
 */
function tve_do_custom_menu_shortcode( $atts ) {
	return '<div class="thrive-shortcode-html">' . thrive_shortcode_custom_menu( $atts, '' ) . '</div>';
}

/**
 * handle the custom phone shortcode
 *
 * @param $atts
 *
 * @return string
 */
function tve_do_custom_phone_shortcode( $atts ) {
	return '<div class="thrive-shortcode-html">' . thrive_shortcode_custom_phone( $atts, '' ) . '</div>';
}

/**
 * mimics all wordpress called functions when rendering a shortcode
 *
 * @param $content
 */
function tcb_render_wp_shortcode( $content ) {

	$do_shortcode = is_editor_page() || ( defined( 'DOING_AJAX' ) && DOING_AJAX );

	/* fix for SUPP-5168, treat [embed] shortcodes separately by delegating the shortcode function to class-wp-embed.php */
	if ( $do_shortcode ) {
		$content = tve_handle_embed_shortcode( $content );
	}

	$content = wptexturize( ( $content ) );
	$content = convert_smilies( $content );
	$content = convert_chars( $content );
	$content = wpautop( $content );
	$content = shortcode_unautop( $content );
	$content = shortcode_unautop( wptexturize( $content ) );

	if ( $do_shortcode ) {
		$content = preg_replace( '#<!--more(.*?)-->#', '<span class="tcb-wp-more-tag"></span>', $content );
	}

	return $do_shortcode ? do_shortcode( $content ) : $content;
}

/**
 * render any shortcodes that might be included in the post meta-data using the Insert Shortcode element
 * raw shortcode texts are saved between 2 flags: ___TVE_SHORTCODE_RAW__ AND __TVE_SHORTCODE_RAW___
 *
 * @param string $content
 * @param bool   $is_editor_page
 */
function tve_do_wp_shortcodes( $content, $is_editor_page = false ) {
	/**
	 * replace all the {tcb_post_} shortcodes with actual values
	 */
	if ( ! $is_editor_page ) {
		/**
		 * if we are currently redering a TCB lightbox, we still need to have the main post title, url etc
		 */
		if ( ! empty( $GLOBALS['tcb_main_post_lightbox'] ) ) {
			$post_id = $GLOBALS['tcb_main_post_lightbox']->ID;
		} else {
			$post_id = get_the_ID();
		}
		$featured_image = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'full' );
		$permalink      = get_permalink( $post_id ); // TODO: I think get_the_permalink is slow, we need to cache this somehow
		$search         = array(
			'{tcb_post_url}',
			'{tcb_encoded_post_url}',
			'{tcb_post_title}',
			'{tcb_post_image}',
			'{tcb_current_year}',
		);
		$replace        = array(
			$permalink,
			urlencode( $permalink ),
			get_the_title( $post_id ),
			! empty( $featured_image ) && ! empty( $featured_image[0] ) ? $featured_image[0] : '',
			date( 'Y' ),
		);
		$content        = str_replace( $search, $replace, $content );
	}

	list( $start, $end ) = array(
		'___TVE_SHORTCODE_RAW__',
		'__TVE_SHORTCODE_RAW___',
	);

	if ( strpos( $content, $start ) === false ) {
		return $content;
	}
	if ( ! preg_match_all( "/{$start}((<p>)?(.+?)(<\/p>)?){$end}/s", $content, $matches, PREG_OFFSET_CAPTURE ) ) {
		return $content;
	}

	$position_delta = 0;
	foreach ( $matches[1] as $i => $data ) {
		$raw_shortcode = $data[0]; // the actual matched regexp group
		$position      = $matches[0][ $i ][1] + $position_delta; //the index at which the whole group starts in the string, at the current match
		$whole_group   = $matches[0][ $i ][0];

		$raw_shortcode = html_entity_decode( $raw_shortcode );//we keep the code encoded and now we need to decode

		$replacement = tcb_render_wp_shortcode( $raw_shortcode );
		$replacement = ( $is_editor_page ? $whole_group : '' ) . ( '</div><div class="tve_shortcode_rendered">' . $replacement );

		$content = substr_replace( $content, $replacement, $position, strlen( $whole_group ) );
		/* increment the positioning offsets for the string with the difference between replacement and original string length */
		$position_delta += strlen( $replacement ) - strlen( $whole_group );
	}

	return $content;
}

/**
 * check if post having id $id is a landing page created with TCB
 *
 * @param $id
 */
function tve_post_is_landing_page( $id ) {
	$is_landing_page = get_post_meta( $id, 'tve_landing_page', true );

	if ( ! $is_landing_page ) {
		return false;
	}

	return $is_landing_page; // this is the actual landing page template
}

/**
 * get post meta key. Also takes into account whether or not this post is a landing page
 * each regular meta key from the editor has the associated meta key for the landing page constructed by appending a "_{template_name}" after the key
 *
 * @param int    $post_id
 * @param string $meta_key
 *
 * @return string
 */
function tve_get_post_meta( $post_id, $meta_key, $single = true ) {
	if ( ( $template = tve_post_is_landing_page( $post_id ) ) !== false ) {
		$meta_key = $meta_key . '_' . $template;
	}

	$value = get_post_meta( $post_id, $meta_key, $single );

	/**
	 * I'm not sure why this is happening, but we had some instances where these meta values were being serialized twice
	 */
	if ( $single ) {
		$value = maybe_unserialize( $value );
	}

	return $value;
}

/**
 * update a post meta key. Also takes into account whether or not this post is a landing page
 * each regular meta key from the editor has the associated meta key for the landing page constructed by appending a "_{template_name}" after the key
 *
 * @param $post_id
 * @param $meta_key
 * @param $value
 */
function tve_update_post_meta( $post_id, $meta_key, $meta_value ) {
	if ( ( $template = tve_post_is_landing_page( $post_id ) ) !== false ) {
		$meta_key = $meta_key . '_' . $template;
	}

	return update_post_meta( $post_id, $meta_key, $meta_value );
}

/**
 * loads the landing pages configuration file and returns the item in that array corresponding to the template passed in as parameter
 *
 * @param $template_name
 */
function tve_get_landing_page_config( $template_name ) {
	if ( ! $template_name ) {
		return array();
	}

	if ( tve_is_cloud_template( $template_name ) ) {
		$config = tve_get_cloud_template_config( $template_name, false );

		return $config === false ? array() : $config;
	}

	$config = include plugin_dir_path( dirname( __FILE__ ) ) . 'landing-page/templates/_config.php';

	return isset( $config[ $template_name ] ) ? $config[ $template_name ] : array();
}

/**
 * return a list with the current saved Landing Page templates
 */
function tve_landing_pages_load() {
	$templates = get_option( 'tve_saved_landing_pages_meta', array() );
	$templates = empty( $templates ) ? array() : array_reverse( $templates, true ); // order by date DESC

	return $templates;
}

/**
 * get the link to the google font based on $font
 *
 * @param array|object $font
 */
function tve_custom_font_get_link( $font ) {
	if ( is_array( $font ) ) {
		$font = (object) $font;
	}

	if ( Tve_Dash_Font_Import_Manager::isImportedFont( $font ) ) {
		return Tve_Dash_Font_Import_Manager::getCssFile();
	}

	return '//fonts.googleapis.com/css?family=' . str_replace( ' ', '+', $font->font_name ) . ( $font->font_style ? ':' . $font->font_style : '' ) . ( $font->font_bold ? ',' . $font->font_bold : '' ) . ( $font->font_italic ? $font->font_italic : '' ) . ( $font->font_character_set ? '&subset=' . $font->font_character_set : '' );
}

/**
 * get all fonts created with the font manager
 *
 * @param bool $assoc whether to decode as array or object
 *
 * @return array
 */
function tve_get_all_custom_fonts( $assoc = false ) {
	$all_fonts = get_option( 'thrive_font_manager_options' );
	if ( empty( $all_fonts ) ) {
		$all_fonts = array();
	} else {
		$all_fonts = json_decode( $all_fonts, $assoc );
	}

	return (array) $all_fonts;
}

/**
 *
 * @param $post_id
 * @param $custom_font_classes array containing all the custom font css classes
 */
function tve_update_post_custom_fonts( $post_id, $custom_font_classes ) {
	$all_fonts = tve_get_all_custom_fonts();

	$post_fonts = array();
	foreach ( array_unique( $custom_font_classes ) as $cls ) {
		foreach ( $all_fonts as $font ) {
			if ( Tve_Dash_Font_Import_Manager::isImportedFont( $font->font_name ) ) {
				$post_fonts[] = Tve_Dash_Font_Import_Manager::getCssFile();
			} else if ( $font->font_class == $cls && ! tve_is_safe_font( $font ) ) {
				$post_fonts[] = tve_custom_font_get_link( $font );
				break;
			}
		}
	}

	$post_fonts = array_unique( $post_fonts );

	tve_update_post_meta( $post_id, 'thrive_tcb_post_fonts', $post_fonts );
}

/**
 * get all custom fonts used for a post
 *
 * @param      $post_id
 * @param bool $include_thrive_fonts - whether or not to include Thrive Themes fonts for this post in the list.
 *                                   By default it will return all the fonts that are used in TCB but are not already used from the Theme (admin WP editor)
 *
 * @return array with index => href link
 */
function tve_get_post_custom_fonts( $post_id, $include_thrive_fonts = false ) {
	$post_fonts = tve_get_post_meta( $post_id, 'thrive_tcb_post_fonts' );
	$post_fonts = empty( $post_fonts ) ? array() : $post_fonts;

	if ( empty( $post_fonts ) && ! $include_thrive_fonts ) {
		return array();
	}

	$all_fonts       = tve_get_all_custom_fonts();
	$all_fonts_links = array();
	foreach ( $all_fonts as $f ) {
		if ( Tve_Dash_Font_Import_Manager::isImportedFont( $f->font_name ) ) {
			$all_fonts_links[] = Tve_Dash_Font_Import_Manager::getCssFile();
		} else if ( ! tve_is_safe_font( $f ) ) {
			$all_fonts_links [] = tve_custom_font_get_link( $f );
		}
	}

	if ( empty( $all_fonts ) ) {
		// all fonts have been deleted - delete the saved fonts too for this post
		tve_update_post_meta( $post_id, 'thrive_tcb_post_fonts', array() );
	} else {
		$fixed = array_intersect( $post_fonts, $all_fonts_links );
		if ( count( $fixed ) != count( $post_fonts ) ) {
			$post_fonts = $fixed;
			tve_update_post_meta( $post_id, 'thrive_tcb_post_fonts', $post_fonts );
		}
	}

	$theme_post_fonts = get_post_meta( $post_id, 'thrive_post_fonts', true );
	$theme_post_fonts = empty( $theme_post_fonts ) ? array() : json_decode( $theme_post_fonts, true );

	$post_fonts = empty( $post_fonts ) || ! is_array( $post_fonts ) ? array() : $post_fonts;

	/* return just fonts that will not be loaded from any possible theme shortcodes */

	return $include_thrive_fonts ? array_values( array_unique( array_merge( $post_fonts, $theme_post_fonts ) ) ) : array_diff( $post_fonts, $theme_post_fonts );
}

/**
 * enqueue all the custom fonts used on a post (used only on frontend, not on editor page)
 *
 * @param mixed $post_id              if null -> use the global wp query; if not, load the fonts for that specific post
 * @param bool  $include_thrive_fonts by default thrive themes fonts are included by the theme. for lightboxes for example, we need to include those also
 */
function tve_enqueue_custom_fonts( $post_id = null, $include_thrive_fonts = false ) {
	if ( $post_id === null ) {
		global $wp_query;
		$posts_to_load = $wp_query->posts;
		if ( empty( $posts_to_load ) || ! is_array( $posts_to_load ) ) {
			return;
		}
		$post_id = array();
		foreach ( $posts_to_load as $p ) {
			$post_id [] = $p->ID;
		}
	} else {
		$post_id = array( $post_id );
	}

	foreach ( $post_id as $_id ) {
		tve_enqueue_fonts( tve_get_post_custom_fonts( $_id, $include_thrive_fonts ) );
	}
}

/**
 * Enqueue custom scripts thant need to be loaded on FRONTEND
 */
function tve_enqueue_custom_scripts() {
	global $wp_query;

	$posts_to_load = $wp_query->posts;

	if ( ! is_array( $posts_to_load ) ) {
		return;
	}

	$js_suffix = defined( 'TVE_DEBUG' ) && TVE_DEBUG ? '.js' : '.min.js';

	foreach ( $posts_to_load as $post ) {
		if ( tve_get_post_meta( $post->ID, 'tve_has_masonry' ) ) {
			wp_script_is( 'jquery-masonry' ) || wp_enqueue_script( 'jquery-masonry', array( 'jquery' ) );
		}
		/* include wistia script for popover videos */
		if ( tve_get_post_meta( $post->ID, 'tve_has_wistia_popover' ) && ! wp_script_is( 'tl-wistia-popover' ) ) {
			wp_enqueue_script( 'tl-wistia-popover', '//fast.wistia.com/assets/external/E-v1.js', array(), '', true );
		}
		$globals = tve_get_post_meta( $post->ID, 'tve_globals' );
		if ( ! empty( $globals['js_sdk'] ) ) {
			foreach ( $globals['js_sdk'] as $handle ) {
				wp_script_is( 'tve_js_sdk_' . $handle ) || wp_enqueue_script( 'tve_js_sdk_' . $handle, tve_social_get_sdk_link( $handle ), array(), false );
			}
		}
	}
}

/**
 * Enqueue the javascript for the social sharing elements, if any is required
 * Will throw an event called "tve_socials_init_[network_name]"
 * It will throw an event for Pinterest by default
 * If the event is thrown the enqueue will be skipped
 *
 * @param $do_action_for array of networks.
 */
function tve_enqueue_social_scripts( $do_action_for = array() ) {
	global $wp_query;

	$posts_to_load = $wp_query->posts;

	if ( ! is_array( $posts_to_load ) ) {
		return;
	}

	foreach ( $posts_to_load as $post ) {
		$globals = tve_get_post_meta( $post->ID, 'tve_globals' );
		if ( ! empty( $globals['js_sdk'] ) ) {
			foreach ( $globals['js_sdk'] as $handle ) {
				$link = tve_social_get_sdk_link( $handle );
				if ( ! $link ) {
					continue;
				}
				wp_script_is( 'tve_js_sdk_' . $handle ) || wp_enqueue_script( 'tve_js_sdk_' . $handle, $link, array(), false );
			}
		}
	}
}

/**
 * enqueue all fonts passed in as an array with font links
 *
 * @param array $font_array can either be a list of links to google fonts css or a list with font objects returned from the font manager options
 *
 * @return array
 */
function tve_enqueue_fonts( $font_array ) {
	if ( ! is_array( $font_array ) ) {
		return array();
	}
	$return = array();
	/** @var $font object|array|string */
	foreach ( $font_array as $font ) {
		if ( is_string( $font ) ) {
			$href = $font;
		} else if ( is_array( $font ) || is_object( $font ) ) {
			$font_name = is_array( $font ) ? $font['font_name'] : $font->font_name;
			if ( Tve_Dash_Font_Import_Manager::isImportedFont( $font_name ) ) {
				$href = Tve_Dash_Font_Import_Manager::getCssFile();
			} else {
				$href = tve_custom_font_get_link( $font );
			}
		}
		$font_key            = 'tcf_' . md5( $href );
		$return[ $font_key ] = $href;
		wp_enqueue_style( $font_key, $href );
	}

	return $return;
}

/**
 * remove tinymce conflicts
 * 1. if 3rd party products include custom versions of jquery UI, those will completely break the 'wplink' plugin
 * 2. MemberMouse adds some media buttons and does not correctly setup links to images
 */
function tcb_remove_tinymce_conflicts() {
	/* Membermouse adds some extra media buttons */
	remove_all_actions( 'media_buttons_context' );
}

/**
 * render the html for the "Custom Menu" widget element
 *
 * called either from the editor section or from frontend, when rendering everything
 *
 * @param $attributes
 */
function tve_render_widget_menu( $attributes ) {
	$menu_id = ! empty( $attributes['menu_id'] ) ? $attributes['menu_id'] : null;

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX && function_exists( 'Nav_Menu_Roles' ) ) {
		/**
		 * If loading the menu via ajax ( in the TCB editor page ) and the Nav Menu Roles plugin is active, we need to add its filtering function here
		 * in order to show the same menu items in the editor page and in Preview
		 */
		$nav_menu_roles = Nav_Menu_Roles();
		if ( ! empty( $nav_menu_roles ) && $nav_menu_roles instanceof Nav_Menu_Roles ) {
			add_filter( 'wp_get_nav_menu_items', array( $nav_menu_roles, 'exclude_menu_items' ) );
		}
	}

	$items = wp_get_nav_menu_items( $menu_id );
	if ( empty( $items ) ) {
		return '';
	}
	$head_css_attr         = ! empty( $attributes['head_css'] ) ? sprintf( " data-css='%s'", $attributes['head_css'] ) : '';
	$ul_custom_color       = ! empty( $attributes['ul_attr'] ) ? sprintf( " data-tve-custom-colour='%s'", $attributes['ul_attr'] ) : '';
	$trigger_color         = ! empty( $attributes['trigger_attr'] ) ? sprintf( " data-tve-custom-colour='%s'", $attributes['trigger_attr'] ) : '';
	$link_custom_color     = ! empty( $attributes['link_attr'] ) ? $attributes['link_attr'] : '';
	$top_link_custom_color = ! empty( $attributes['top_link_attr'] ) ? $attributes['top_link_attr'] : '';
	$font_family           = ! empty( $attributes['font_family'] ) ? $attributes['font_family'] : '';
	// Member mouse login / logout links not being shown in the menu
	$is_primary = ! empty( $attributes['primary'] );

	if ( ! empty( $link_custom_color ) || ! empty( $top_link_custom_color ) ) {
		/* ugly ugly solution */
		$GLOBALS['tve_menu_link_custom_color']     = $link_custom_color;
		$GLOBALS['tve_menu_top_link_custom_color'] = $top_link_custom_color;
		add_filter( 'nav_menu_link_attributes', 'tve_menu_custom_color', 10, 3 );
	}

	if ( ! empty( $font_family ) ) {
		$GLOBALS['tve_menu_top_link_custom_font_family'] = $font_family;
		add_filter( 'nav_menu_link_attributes', 'tve_menu_custom_font_family', 10, 3 );
	}

	if ( ! empty( $attributes['font_class'] ) ) {
		$GLOBALS['tve_menu_font_class'] = $attributes['font_class'];
		add_filter( 'nav_menu_css_class', 'tve_widget_menu_li_classes' );
	}

	$menu_html = '<div class="thrive-shortcode-html tve_clearfix"  ' . $head_css_attr . '><a' . $trigger_color . ' class="tve-m-trigger t_' . $attributes['dir'] . ' ' .
	             $attributes['color'] . '" href="javascript:void(0)"><span class="thrv-icon thrv-icon-align-justify"></span></a>' .
	             wp_nav_menu( array(
		             'echo'           => false,
		             'menu'           => $menu_id,
		             'container'      => false,
		             'theme_location' => ! empty( $is_primary ) ? 'primary' : '',
		             'items_wrap'     => '<ul' . $ul_custom_color . ' id="%1$s" class="%2$s"' . ( ! empty( $attributes['font_size'] ) ? ' style="font-size:' . $attributes['font_size'] . '"' : '' ) . '>%3$s</ul>',
		             'menu_class'     => 'tve_w_menu ' . $attributes['dir'] . ' ' . ( ! empty( $attributes['font_class'] ) ? $attributes['font_class'] . ' ' : '' ) . $attributes['color'],
	             ) ) . '</div>';

	/* clear out the global variable */
	unset( $GLOBALS['tve_menu_link_custom_color'], $GLOBALS['tve_menu_top_link_custom_color'], $GLOBALS['tve_menu_font_class'], $GLOBALS['tve_menu_top_link_custom_font_family'] );
	remove_filter( 'nav_menu_link_attributes', 'tve_menu_custom_color' );
	remove_filter( 'nav_menu_link_attributes', 'tve_menu_custom_font_family' );
	remove_filter( 'nav_menu_css_class', 'tve_widget_menu_li_classes' );

	return $menu_html;
}

/**
 * append font classes also to the <li> menu elements
 *
 * @param array $classes
 *
 * @return array
 */
function tve_widget_menu_li_classes( $classes ) {
	if ( empty( $GLOBALS['tve_menu_font_class'] ) ) {
		return $classes;
	}

	$classes [] = $GLOBALS['tve_menu_font_class'];

	return $classes;
}

/**
 * append custom color attributes to the link items from the menu
 *
 * @param $attrs
 *
 * @return mixed
 */
function tve_menu_custom_color( $attrs, $menu_item ) {
	$custom_color = $menu_item->menu_item_parent ? 'tve_menu_link_custom_color' : 'tve_menu_top_link_custom_color';
	$value        = isset( $GLOBALS[ $custom_color ] ) ? $GLOBALS[ $custom_color ] : '';

	if ( ! $value ) {
		return $attrs;
	}
	$attrs['data-tve-custom-colour'] = $value;

	return $attrs;
}

function tve_menu_custom_font_family( $attrs, $menu_item ) {
	$font_family = $GLOBALS['tve_menu_top_link_custom_font_family'];
	$style       = 'font-family: ' . $font_family . ';';

	if ( isset( $attrs['style'] ) && ! empty( $attrs['style'] ) ) {
		$style = trim( ';', $attrs['style'] ) . ';' . $style;
	}

	$attrs['style'] = $style;

	return $attrs;
}

/**
 * custom call of an action hook - this will forward the call to the WP do_action function
 * it will inject parameters read from $_GET based on the filter that others might use
 *
 * @param string $hook  required. The action hook to be called
 * @param mixed  $_args arguments that will be passed on to the do_action call
 */
function tve_do_action() {
	/**
	 * filter to allow passing variables from $_GET into the various actions
	 * this is used only on editor page
	 */
	$_get_fields = apply_filters( 'tcb_required_get_fields', array() );
	$args        = func_get_args();

	if ( ! is_array( $_get_fields ) ) {
		$_get_fields = array();
	}

	foreach ( $_get_fields as $field ) {
		$args [] = isset( $_GET[ $field ] ) ? $_GET[ $field ] : null;
	}

	return call_user_func_array( 'do_action', $args );
}

/**
 * sort the user-defined templates alphabetically by name
 *
 * @param $a
 * @param $b
 *
 * @return int
 */
function tve_tpl_sort( $a, $b ) {
	return strcasecmp( $a['name'], $b['name'] );
}

/**
 *
 * transform any url into a protocol-independent url
 *
 * @param string $raw_url
 *
 * @return string
 */
function tve_url_no_protocol( $raw_url ) {
	return preg_replace( '#http(s)?://#', '//', $raw_url );
}

/**
 * called via AJAX, it will load a file from a list of allowed files from the editor
 * designed to work
 */
function tve_ajax_load() {

	if ( ob_get_contents() ) {
		ob_clean();
	}
	if ( empty( $_POST['ajax_load'] ) ) {
		return;
	}
	$file = $_POST['ajax_load'];

	switch ( $file ) {
		case 'control_panel':
		case 'lb_icon':
		case 'lb_lead_generation_code':
		case 'lb_post_grid':
		case 'lb_revision_manager':
		case 'lb_social':
		case 'lb_custom_css':
		case 'lb_custom_html':
		case 'lb_full_html':
		case 'lb_global_scripts':
		case 'lb_google_map':
		case 'lb_image_link':
		case 'lb_landing_pages':
		case 'lb_table':
		case 'lb_text_link':
		case 'lb_text_link_settings':
		case 'lb_ultimatum_shortcode':
			include plugin_dir_path( dirname( __FILE__ ) ) . 'editor/' . $file . '.php';
			break;
		case 'sc_thrive_custom_menu':
		case 'sc_thrive_custom_phone':
		case 'sc_thrive_leads_shortcode':
		case 'sc_thrive_ultimatum_shortcode':
		case 'sc_thrive_optin':
		case 'sc_thrive_posts_list':
		case 'sc_widget_menu':
		case 'sc_icon':
			include plugin_dir_path( dirname( __FILE__ ) ) . 'shortcodes/templates/' . $file . '.php';
			break;
		default:
			do_action( 'tcb_ajax_load', $file );
			break;
	}

	exit();
}

/**
 * Fields that will be displayed with differences in revisions page(admin section)
 *
 * @param $fields
 *
 * @return mixed
 */
function tve_post_revision_fields( $fields ) {
	$fields['tve_revision_tve_updated_post']    = __( 'Thrive Architect Content', 'thrive-cb' );
	$fields['tve_revision_tve_user_custom_css'] = __( 'Thrive Architect Custom CSS', 'thrive-cb' );
	$fields['tve_revision_tve_landing_page']    = __( 'Landing Page', 'thrive-cb' );

	return $fields;
}

/**
 * At this moment post is reverted to required revision.
 * This means the post is saved and a new revision is already created.
 * When a revision is created all metas are assigned to revision;
 *
 * @see tve_save_post_callback
 *
 * Get all the metas of the revision received as parameter and set it for the newly revision created.
 * Set all revision metas to post received as parameter
 *
 * @param $post_id
 * @param $revision_id
 *
 * @return bool
 */
function tve_restore_post_to_revision( $post_id, $revision_id ) {
	$revisions     = wp_get_post_revisions( $post_id );
	$last_revision = array_shift( $revisions );

	if ( ! $last_revision ) {
		return false;
	}

	$meta_keys = tve_get_used_meta_keys();
	foreach ( $meta_keys as $meta_key ) {
		$revision_content = get_metadata( 'post', $revision_id, 'tve_revision_' . $meta_key, true );
		update_metadata( 'post', $last_revision->ID, 'tve_revision_' . $meta_key, $revision_content );

		if ( $meta_key === 'tve_landing_page' ) {
			update_post_meta( $post_id, $meta_key, $revision_content );
		} else {
			tve_update_post_meta( $post_id, $meta_key, $revision_content );
		}
	}
}

/**
 * Filter called from wp_save_post_revision. If this logic returns true a post revision will be added by WP
 * If there are any changes in meta then we need a revision to be made
 *
 * @see wp_save_post_revision
 *
 * @param $post_has_changed
 * @param $last_revision
 * @param $post
 *
 * @return bool
 */
function tve_post_has_changed( $post_has_changed, $last_revision, $post ) {
	$meta_keys = tve_get_used_meta_keys();

	/**
	 * check the meta
	 * if there is any meta differences a revision should be made
	 */
	foreach ( $meta_keys as $meta_key ) {
		if ( $meta_key === 'tve_landing_page' ) {
			$post_content = get_post_meta( $post->ID, $meta_key, true );
		} else {
			$post_content = tve_get_post_meta( $post->ID, $meta_key );
		}
		$revision_content = get_post_meta( $last_revision->ID, 'tve_revision_' . $meta_key, true );
		$post_has_changed = $revision_content !== $post_content;
		if ( $post_has_changed ) {
			return true;
		}
	}

	/** @var $total_fields array fields that are tracked for versioning */
	$total_fields = array_keys( _wp_post_revision_fields() );

	/** @var $tve_custom_fields array fields that are pushed to be tracked by this plugin */
	$tve_custom_fields = array_keys( tve_post_revision_fields( array() ) );

	/** @var $to_be_checked array remove additional plugin tracking fields */
	$to_be_checked = array();
	foreach ( $total_fields as $total ) {
		if ( in_array( $total, $tve_custom_fields ) ) {
			continue;
		}
		$to_be_checked[] = $total;
	}

	foreach ( $to_be_checked as $field ) {
		if ( normalize_whitespace( $post->$field ) != normalize_whitespace( $last_revision->$field ) ) {
			$post_has_changed = true;
			break;
		}
	}

	return $post_has_changed;
}

/**
 * Return an array with meta keys that are used for custom content on posts
 *
 * @see tve_save_post_callback, tve_post_has_changed, tve_restore_post_to_revision
 *
 * @return array
 */
function tve_get_used_meta_keys() {
	$meta_keys = array(
		'tve_landing_page',
		'tve_disable_theme_dependency',
		'tve_content_before_more',
		'tve_content_more_found',
		'tve_save_post',
		'tve_custom_css',
		'tve_user_custom_css',
		'tve_page_events',
		'tve_globals',
		'tve_global_scripts',
		'thrive_icon_pack',
		'thrive_tcb_post_fonts',
		'tve_has_masonry',
		'tve_has_typefocus',
		'tve_updated_post',
		'tve_has_wistia_popover',
	);

	return $meta_keys;
}

/**
 * Called when post is loaded and tve_revert_theme exists in get request
 * Redirects the user to post edit form
 */
function tve_revert_page_to_theme() {
	if ( ! isset( $_GET['tve_revert_theme'] ) ) {
		return;
	}
	if ( ! isset( $_GET['post'] ) || ! isset( $_GET['action'] ) ) {
		return;
	}
	$post_id = $_GET['post'];

	if ( tve_post_is_landing_page( $_GET['post'] ) ) {
		delete_post_meta( $post_id, 'tve_landing_page' );
		//Delete Also The Setting To Disable Theme CSS
		delete_post_meta( $post_id, 'tve_disable_theme_dependency' );
		//force save, a revision needs to be created
		wp_update_post( array(
			'ID'                => $post_id,
			'post_modified'     => current_time( 'mysql' ),
			'post_modified_gmt' => current_time( 'mysql' ),
			'post_title'        => get_the_title( $post_id ),
		) );
		wp_redirect( get_edit_post_link( $post_id, 'url' ) );
		exit();
	}
}

/**
 * strip out any un-necessary stuff from the content before displaying it on frontend
 *
 * @param string $tve_saved_content
 *
 * @return string the clean content
 */
function tcb_clean_frontend_content( $tve_saved_content ) {
	/**
	 * strip out the lead generation code
	 *
	 * TODO: we also need to move the error messages inside a data-errors attribute of the LG element
	 */
	if ( strpos( $tve_saved_content, '__CONFIG_lead_generation' ) !== false ) {
		$tve_saved_content = preg_replace( '/__CONFIG_lead_generation_code__(.+?)__CONFIG_lead_generation_code__/s', '', $tve_saved_content );
	}

	return $tve_saved_content;
}

/**
 * create a hidden input containing the error messages instead of holding them in the html content
 *
 * @param array $match
 *
 * @return string
 */
function tcb_lg_err_inputs( $match ) {
	return '<input type="hidden" class="tve-lg-err-msg" value="' . htmlspecialchars( $match[1] ) . '">';
}

/**
 * One place to rule them all
 * Please use this function to read the FB AppID used in Social Sharing Element
 *
 * @return string
 */
function tve_get_social_fb_app_id() {
	return get_option( 'tve_social_fb_app_id', '' );
}

/**
 * Please use this function to read the Disqus Short Name used in Disqus Comments Element
 *
 * @return string
 */
function tve_get_comments_disqus_shortname() {
	return get_option( 'tve_comments_disqus_shortname', '' );
}

/**
 * Please use this function to read the Facebook Admins used in Facebook Comments Element
 *
 * @return array
 */
function tve_get_comments_facebook_admins() {
	return get_option( 'tve_comments_facebook_admins', '' );
}

/**
 * Set the path where the translation files are being kept
 */
function tve_load_plugin_textdomain() {
	$domain = 'thrive-cb';
	$locale = $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	$path = 'thrive-visual-editor/languages/';
	$path = apply_filters( 'tve_filter_plugin_languages_path', $path );

	load_textdomain( $domain, WP_LANG_DIR . '/thrive/' . $domain . '-' . $locale . '.mo' );
	load_plugin_textdomain( $domain, false, $path );
}

/**
 * Check the Object font sent as param if it's web sef font
 *
 * @param $font array|StdClass
 *
 * @return bool
 */
function tve_is_safe_font( $font ) {
	foreach ( tve_dash_font_manager_get_safe_fonts() as $safe_font ) {
		if ( ( is_object( $font ) && $safe_font['family'] === $font->font_name )
		     || ( is_array( $font ) && $safe_font['family'] === $font['font_name'] )
		) {
			return true;
		}
	}

	return false;
}

/**
 * Remove the web safe fonts from the list cos we don't want them to import them from google
 * They already exists loaded in browser from user's computer
 *
 * @param $fonts_saved
 *
 * @return mixed
 */
function tve_filter_custom_fonts_for_enqueue_in_editor( $fonts_saved ) {
	$safe_fonts = tve_dash_font_manager_get_safe_fonts();
	foreach ( $safe_fonts as $safe ) {
		foreach ( $fonts_saved as $key => $font ) {
			if ( is_object( $font ) && $safe['family'] === $font->font_name ) {
				unset( $fonts_saved[ $key ] );
			} elseif ( is_array( $font ) && $safe['family'] === $font['font_name'] ) {
				unset( $fonts_saved[ $key ] );
			}
		}
	}

	return $fonts_saved;
}

/**
 * includes a message in the media uploader window about the allowed file types
 */
function tve_media_restrict_filetypes() {
	$file_types = array(
		'zip',
		'jpg',
		'gif',
		'png',
		'pdf',
	);
	foreach ( $file_types as $file_type ) {
		echo '<p class="tve-media-message tve-media-allowed-' . $file_type . '" style="display: none"><strong>' . sprintf( __( 'Only %s files are accepted' ), '.' . $file_type ) . '</strong></p>';
	}
}

function tve_json_utf8_slashit( $value ) {
	return str_replace( array( '_tveutf8_', '_tve_quote_' ), array( '\u', '\"' ), $value );
}

function tve_json_utf8_unslashit( $value ) {
	return str_replace( array( '\u', '\"' ), array( '_tveutf8_', '_tve_quote_' ), $value );
}

/**
 * Loads dashboard's version file
 */
function tve_load_dash_version() {
	$tve_dash_path      = dirname( dirname( __FILE__ ) ) . '/thrive-dashboard';
	$tve_dash_file_path = $tve_dash_path . '/version.php';

	if ( is_file( $tve_dash_file_path ) ) {
		$version                                  = require_once( $tve_dash_file_path );
		$GLOBALS['tve_dash_versions'][ $version ] = array(
			'path'   => $tve_dash_path . '/thrive-dashboard.php',
			'folder' => '/thrive-visual-editor',
			'from'   => 'plugins',
		);
	}
}

/**
 * handles all api-related AJAX calls made when editing a Lead Generation element
 */
function tve_api_editor_actions() {
	$controller = new Thrive_Dash_List_Editor_Controller();
	$controller->run();
}

function tve_custom_form_submit() {

	$post = $_POST;
	/**
	 * action filter -  allows hooking into the form submission event
	 *
	 * @param array $post the full _POST data
	 *
	 */
	do_action( 'tcb_api_form_submit', $post );
}

/**
 * AJAX call on a Lead Generation form that's connected to an api
 */
function tve_api_form_submit() {
	$data = $_POST;

	if ( isset( $data['_use_captcha'] ) && $data['_use_captcha'] == '1' ) {
		$captcha_url = 'https://www.google.com/recaptcha/api/siteverify';
		$captcha_api = Thrive_Dash_List_Manager::credentials( 'recaptcha' );

		$_capthca_params = array(
			'response' => $data['g-recaptcha-response'],
			'secret'   => empty( $captcha_api['secret_key'] ) ? '' : $captcha_api['secret_key'],
			'remoteip' => $_SERVER['REMOTE_ADDR'],
		);

		$request  = tve_dash_api_remote_post( $captcha_url, array( 'body' => $_capthca_params ) );
		$response = json_decode( wp_remote_retrieve_body( $request ) );
		if ( empty( $response ) || $response->success === false ) {
			exit( json_encode( array(
				'error' => __( 'Please prove us that you are not a robot!!!', 'thrive-cb' ),
			) ) );
		}
	}

	if ( empty( $data['email'] ) ) {
		exit( json_encode( array(
			'error' => __( 'The email address is required', 'thrive-cb' ),
		) ) );
	}

	if ( ! is_email( $data['email'] ) ) {
		exit( json_encode( array(
			'error' => __( 'The email address is invalid', 'thrive-cb' ),
		) ) );
	}

	$post = $data;
	unset( $post['action'], $post['__tcb_lg_fc'], $post['_back_url'] );

	/**
	 * action filter -  allows hooking into the form submission event
	 *
	 * @param array $post the full _POST data
	 *
	 */
	do_action( 'tcb_api_form_submit', $post );

	if ( empty( $data['__tcb_lg_fc'] ) || ! ( $connections = Thrive_Dash_List_Manager::decodeConnectionString( $data['__tcb_lg_fc'] ) ) ) {
		exit( json_encode( array(
			'error' => __( 'No connection for this form', 'thrive-cb' ),
		) ) );
	}

	//these are not needed anymore
	unset( $data['__tcb_lg_fc'], $data['_back_url'], $data['action'] );

	$result        = array();
	$data['name']  = ! empty( $data['name'] ) ? $data['name'] : '';
	$data['phone'] = ! empty( $data['phone'] ) ? $data['phone'] : '';

	/**
	 * filter - allows modifying the data before submitting it to the API
	 *
	 * @param array $data
	 */
	$data = apply_filters( 'tcb_api_subscribe_data', $data );

	if ( isset( $data['__tcb_lg_msg'] ) ) {
		$result['form_messages'] = Thrive_Dash_List_Manager::decodeConnectionString( $data['__tcb_lg_msg'] );
	}

	$available = Thrive_Dash_List_Manager::getAvailableAPIs( true );
	foreach ( $available as $key => $connection ) {
		if ( ! isset( $connections[ $key ] ) ) {
			continue;
		}
		if ( $key == 'klicktipp' && $data['_submit_option'] == 'klicktipp-redirect' ) {
			$result['redirect'] = tve_api_add_subscriber( $connection, $connections[ $key ], $data );
			if ( filter_var( $result['redirect'], FILTER_VALIDATE_URL ) !== false ) {
				$result[ $key ] = true;
			}
		} else {
			// Not sure how we can perform validations / mark errors here
			$result[ $key ] = tve_api_add_subscriber( $connection, $connections[ $key ], $data );
		}
	}

	/**
	 * $result will contain boolean 'true' or string error messages for each connected api
	 * these error messages will literally have no meaning for the user - we'll just store them in a db table and show them in admin somewhere
	 */
	echo json_encode( $result );
	die;
}


/**
 * make an api call to a subscribe a user
 *
 * @param string|Thrive_Dash_List_Connection_Abstract $connection
 * @param mixed                                       $list_identifier the list identifier
 * @param array                                       $data            submitted data
 * @param bool                                        $log_error       whether or not to log errors in a DB table
 *
 * @return result mixed
 */
function tve_api_add_subscriber( $connection, $list_identifier, $data, $log_error = true ) {

	if ( is_string( $connection ) ) {
		$connection = Thrive_Dash_List_Manager::connectionInstance( $connection );
	}

	$key = $connection->getKey();

	/**
	 * filter - allows modifying the sent data to each individual API instance
	 *
	 * @param array                           $data            data to be sent to the API instance
	 * @param Thrive_List_Connection_Abstract $connection      the connection instance
	 * @param mixed                           $list_identifier identifier for the list which will receive the new email
	 */
	$data = apply_filters( 'tcb_api_subscribe_data_instance', $data, $connection, $list_identifier );

	/** @var Thrive_Dash_List_Connection_Abstract $connection */
	$result = $connection->addSubscriber( $list_identifier, $data );

	if ( ! $log_error || true === $result || ( $key == 'klicktipp' && filter_var( $result, FILTER_VALIDATE_URL ) !== false ) ) {
		return $result;
	}

	global $wpdb;

	/**
	 * at this point, we need to log the error in a DB table, so that the user can see all these error later on and (maybe) re-subscribe the user
	 */
	$log_data = array(
		'date'          => date( 'Y-m-d H:i:s' ),
		'error_message' => $result,
		'api_data'      => serialize( $data ),
		'connection'    => $connection->getKey(),
		'list_id'       => maybe_serialize( $list_identifier ),
	);

	$wpdb->insert( $wpdb->prefix . 'tcb_api_error_log', $log_data );

	return $result;
}

/**
 * called on the 'init' hook
 *
 * load all classes and files needed for TCB
 */
function tve_load_tcb_classes() {
	require_once plugin_dir_path( dirname( __FILE__ ) ) . 'landing-page/inc/TCB_Landing_Page_Transfer.php';
}

/**
 * @return TCB_Editor
 */
function tcb_editor() {
	return TCB_Editor::instance();
}

/**
 * Get the global cpanel configuration attributes (position, side, minimized etc)
 *
 * @return array
 */
function tve_cpanel_attributes() {
	$defaults = array(
		'position' => 'left',
	);

	$user_option = get_user_option( 'tve_cpanel_config' );
	if ( ! is_array( $user_option ) ) {
		$user_option = array();
	}

	$user_option = array_merge( $defaults, $user_option );

	return $user_option;
}

/**
 * Get the post categories
 *
 * @return array
 */
function tve_get_post_categories() {
	$categories = array( 0 => __( 'All categories', 'thrive-cb' ) );
	foreach ( get_categories() as $cat ) {
		$categories[ $cat->cat_ID ] = $cat->cat_name;
	}

	return $categories;
}

/**
 * Get all defined menus
 *
 * @return array
 */
function tve_get_custom_menus() {
	$menu_items = get_terms( 'nav_menu', array( 'hide_empty' => false ) );
	$all_menus  = array();
	foreach ( $menu_items as $menu ) {
		$all_menus[] = array(
			'id'   => $menu->term_id,
			'name' => $menu->name,
		);
	}

	return $all_menus;
}

/**
 * include a template file from inc/views folder
 *
 * @param string $file
 * @param mixed  $data
 * @param bool   $return whether or not to return the content instead of outputting it
 *
 * @return string|null $content string when $return is non-false and void otherwise
 */
function tcb_template( $file, $data = null, $return = false ) {
	if ( strpos( $file, '.php' ) === false ) {
		$file .= '.php';
	}

	$file      = ltrim( $file, '\\/' );
	$file_path = TVE_TCB_ROOT_PATH . 'inc/views/' . $file;
	$content   = null;

	if ( ! is_file( $file_path ) ) {
		return false;
	}

	if ( false !== $return ) {
		ob_start();
		include $file_path;
		$content = ob_get_contents();
		ob_end_clean();
	} else {
		include $file_path;
	}

	return $content;
}

/**
 * Displays an icon using svg format
 *
 * @param string $icon
 * @param bool   $return      whether to return the icon as a string or to output it directly
 * @param string $namespace   (where this icon is used - for 'editor' it will add another prefix to it)
 * @param string $extra_class classes to be added to the svg
 * @param array  $svg_attr    array with extra attributes to add to the <svg> tag
 *
 * @return mixed
 */
function tcb_icon( $icon, $return = false, $namespace = 'sidebar', $extra_class = '', $svg_attr = array() ) {
	$use = $namespace !== 'sidebar' ? 'tcb-icon-' : 'icon-';

	$extra_attr = '';
	if ( ! empty( $svg_attr ) ) {
		foreach ( $svg_attr as $attr_name => $attr_value ) {
			$extra_attr .= ( $extra_attr ? ' ' : '' ) . $attr_name . '="' . esc_attr( $attr_value ) . '"';
		}
	}

	$html = '<svg class="tcb-icon tcb-icon-' . $icon . ( empty( $extra_class ) ? '' : ' ' . $extra_class ) . '"' . $extra_attr . '><use xlink:href="#' . $use . $icon . '"></use></svg>';

	if ( false !== $return ) {
		return $html;
	}

	echo $html;
}

/**
 * Gets the post revisions as an array of objects
 *
 * @param null $post
 *
 * @return array
 */
function tve_get_post_revisions( $post = null ) {

	$post_id = ( $post instanceof WP_Post ) ? $post->ID : intval( $post );

	$revisions = wp_get_post_revisions( $post_id );
	$return    = array();

	foreach ( $revisions as $revision ) {
		$modified                          = strtotime( $revision->post_modified );
		$modified_gmt                      = strtotime( $revision->post_modified_gmt );
		$now_gmt                           = time();
		$restore_link                      = str_replace( '&amp;', '&', wp_nonce_url(
			add_query_arg(
				array(
					'revision' => $revision->ID,
					'action'   => 'restore',
				),
				admin_url( 'revision.php' )
			),
			"restore-post_{$revision->ID}"
		) );
		$show_avatars                      = get_option( 'show_avatars' );
		$authors[ $revision->post_author ] = array(
			'id'     => (int) $revision->post_author,
			'avatar' => $show_avatars ? get_avatar( $revision->post_author, 64 ) : '',
			'name'   => get_the_author_meta( 'display_name', $revision->post_author ),
		);
		$autosave                          = (bool) wp_is_post_autosave( $revision );
		$return[]                          = array(
			'id'         => $revision->ID,
			'title'      => get_the_title( $post_id ),
			'author'     => $authors[ $revision->post_author ],
			'date'       => date_i18n( __( 'M j, Y @ G:i' ), $modified ),
			'dateShort'  => date_i18n( _x( 'j M Y,G:i', 'revision date short format' ), $modified ),
			'timeAgo'    => sprintf( __( '%s ago', 'thrive-cb' ), human_time_diff( $modified_gmt, $now_gmt ) ),
			'autosave'   => $autosave,
			'restoreUrl' => $restore_link,
		);
	}

	return $return;

}

/**
 * Computes the time settings necessary for Countdown Element and Countdown Evergreen Element
 */
function tve_get_time_settings() {

	$timezone_offset = get_option( 'gmt_offset' );
	$sign            = ( $timezone_offset < 0 ? '-' : '+' );
	$min             = abs( $timezone_offset ) * 60;
	$hour            = floor( $min / 60 );
	$tzd             = $sign . str_pad( $hour, 2, '0', STR_PAD_LEFT ) . ':' . str_pad( $min % 60, 2, '0', STR_PAD_LEFT );

	return array(
		'timezone_offset' => $timezone_offset,
		'sign'            => $sign,
		'min'             => $min,
		'hour'            => $hour,
		'tzd'             => $tzd,
	);
}

/**
 * get the current editor key for the user
 *
 * @param mixed $user_id
 *
 * @return bool|mixed
 */
function tcb_user_get_editor_key( $user_id = null ) {
	if ( ! $user_id && ! ( $user_id = get_current_user_id() ) ) {
		return null;
	}

	return get_user_meta( $user_id, 'tcb_edit_key', true );
}

/**
 * Generate a new editor key for the user and save it
 *
 * @return bool|string
 */
function tcb_user_generate_editor_key() {
	if ( ! $id = get_current_user_id() ) {
		return false;
	}

	$key = wp_create_nonce( 'tcb_editor_key' );
	update_user_meta( $id, 'tcb_edit_key', $key );

	return $key;
}

/**
 * Handles the interim login logic for TCB pages - shown only on successful interim login
 */
function tcb_interim_login_footer() {
	global $interim_login;
	if ( empty( $interim_login ) || $interim_login !== 'success' || empty( $_POST ) || empty( $_POST['tve_interim_editor_key'] ) || empty( $GLOBALS['tcb_interim_user_id'] ) ) {
		return;
	}

	/**
	 * Problem: during the login POST, after login, the user does not seem to be actually available
	 */
	$user_id = $GLOBALS['tcb_interim_user_id'];
	/**
	 * This is used to correctly re-generate the nonce
	 */
	$_COOKIE[ LOGGED_IN_COOKIE ] = $GLOBALS['tcb_interim_login_cookie'];
	$user_key                    = tcb_user_get_editor_key( $user_id );

	wp_set_current_user( $user_id );

	if ( $user_key == $_POST['tve_interim_editor_key'] ) {
		tcb_template( 'handle-login', array(
			'nonce'      => wp_create_nonce( TCB_Editor_Ajax::NONCE_KEY ),
			'rest_nonce' => wp_create_nonce( 'wp_rest' ),
			'userkey'    => $user_key,
		) );
	}
}

/**
 * Helper function to store the actual value of the loggedin cookie during the login process stared from TCB editor
 *
 * @param string $logged_in_cookie The logged-in cookie.
 * @param int    $expire           The time the login grace period expires as a UNIX timestamp.
 *                                 Default is 12 hours past the cookie's expiration time.
 * @param int    $expiration       The time when the logged-in authentication cookie expires as a UNIX timestamp.
 *                                 Default is 14 days from now.
 * @param int    $user_id          User ID.
 */
function tcb_store_interim_login_id( $logged_in_cookie, $expire, $expiration, $user_id ) {
	global $interim_login;
	if ( ! empty( $interim_login ) && ! empty( $_POST ) && ! empty( $_POST['tve_interim_editor_key'] ) ) {
		$GLOBALS['tcb_interim_user_id']      = $user_id;
		$GLOBALS['tcb_interim_login_cookie'] = $logged_in_cookie;
	}
}

/**
 * Filters the upload user template location.
 * Callback used in action_save_user_template function
 *
 * @param $upload
 *
 * @return mixed
 */
function tve_filter_upload_user_template_location( $upload ) {
	$sub_dir = '/thrive-visual-editor/user_templates';

	$upload['path']   = $upload['basedir'] . $sub_dir;
	$upload['url']    = $upload['baseurl'] . $sub_dir;
	$upload['subdir'] = $sub_dir;

	return $upload;
}

if ( ! function_exists( 'tve_is_numeric_array' ) ) {
	/**
	 * Determines if the variable is a numeric-indexed array.
	 *
	 * @since 4.4.0
	 *
	 * @param mixed $data Variable to check.
	 *
	 * @return bool Whether the variable is a list.
	 */
	function tve_is_numeric_array( $data ) {
		if ( ! is_array( $data ) ) {
			return false;
		}

		$keys        = array_keys( $data );
		$string_keys = array_filter( $keys, 'is_string' );

		return count( $string_keys ) === 0;
	}
}

/**
 * Own implementation for array_replace_recursive so we can overwrite numeric arrays
 *
 * @return mixed
 */
function tve_array_replace_recursive() {

	if ( ! function_exists( 'tve_array_recurse' ) ) {
		/**
		 * Merge two arrays recursively
		 *
		 * @param $array
		 * @param $array1
		 *
		 * @return mixed
		 */
		function tve_array_recurse( $array, $array1 ) {

			if ( tve_is_numeric_array( $array ) && tve_is_numeric_array( $array1 ) ) {
				/* if both arrays are numeric, we don't concatenate them, we just return the second one */
				return $array1;
			}

			foreach ( $array1 as $key => $value ) {
				/* create new key in $array, if it is empty or not an array */
				if ( ! isset( $array[ $key ] ) || ( isset( $array[ $key ] ) && ! is_array( $array[ $key ] ) ) ) {
					$array[ $key ] = array();
				}

				/* overwrite the value in the base array */
				if ( is_array( $value ) ) {
					$value = tve_array_recurse( $array[ $key ], $value );
				}
				$array[ $key ] = $value;
			}

			return $array;
		}
	}

	/* handle the arguments, merge one by one */
	$args  = func_get_args();
	$array = $args[0];

	if ( ! is_array( $array ) ) {
		return $array;
	}

	for ( $i = 1, $length = count( $args ); $i < $length; $i ++ ) {
		if ( is_array( $args[ $i ] ) ) {
			$array = tve_array_recurse( $array, $args[ $i ] );
		}
	}

	return $array;
}

if ( ! function_exists( 'tve_frontend_enqueue_scripts' ) ) {

	/**
	 * enqueue scripts for the frontend - also editor and preview
	 */
	function tve_frontend_enqueue_scripts() {
		$js_suffix = defined( 'TVE_DEBUG' ) && TVE_DEBUG ? '.js' : '.min.js';

		if ( ! apply_filters( 'tcb_overwrite_scripts_enqueue', false ) && ! is_editor_page_raw() ) {
			/**
			 * enqueue scripts and styles only for posts / pages that actually have tcb content
			 */
			global $wp_query;
			if ( empty( $wp_query->posts ) ) {
				return;
			}
			$enqueue_tcb_resources = false;
			foreach ( $wp_query->posts as $_post ) {
				if ( tve_get_post_meta( $_post->ID, 'tve_updated_post' ) ) {
					$enqueue_tcb_resources = true;
					break;
				}
			}
			$enqueue_tcb_resources = apply_filters( 'tcb_enqueue_resources', $enqueue_tcb_resources );
			if ( ! $enqueue_tcb_resources ) {
				if ( ! is_singular() ) {
					return;
				}
				/* check also if we have page events, e.g. open lightbox on exit intent */
				$events = tve_get_post_meta( get_the_ID(), 'tve_page_events' );
				if ( empty( $events ) ) {
					/* no events defined -> safe to return here */
					return;
				}
			}
		}

		/**
		 * Enqueue some dash scripts in the editor page
		 */
		if ( is_editor_page() ) {
			tve_enqueue_script( 'jquery-zclip', TVE_DASH_URL . '/js/util/jquery.zclip.1.1.1/jquery.zclip.js', array( 'jquery' ) );
		}

		tve_enqueue_style_family();

		tve_enqueue_script( 'tve_frontend', tve_editor_js() . '/frontend' . $js_suffix, array( 'jquery' ), false, true );

		if ( apply_filters( 'tcb_overwrite_scripts_enqueue', false ) || ( ! is_editor_page() && is_singular() ) ) {
			$events = tve_get_post_meta( get_the_ID(), 'tve_page_events' );
			if ( ! empty( $events ) ) {
				tve_page_events( $events );
			}

			// custom fonts from Font Manager
			$all_fonts         = tve_get_all_custom_fonts();
			$all_fonts_enqueue = apply_filters( 'tve_filter_custom_fonts_for_enqueue_in_editor', $all_fonts );
			tve_enqueue_fonts( $all_fonts_enqueue );
		}

		/* params for the frontend script */
		$frontend_options = array(
			'ajaxurl'          => admin_url( 'admin-ajax.php' ),
			'is_editor_page'   => true,
			'page_events'      => isset( $events ) ? $events : array(),
			'is_single'        => (string) ( (int) is_singular() ),
			'social_fb_app_id' => tve_get_social_fb_app_id(),
			'dash_url'         => TVE_DASH_URL,
			'translations'     => array(
				'Copy' => __( 'Copy', 'thrive-cb' ),
			),
		);
		if ( $frontend_options['is_single'] ) {
			global $post;
			$frontend_options['post_id'] = $post instanceof WP_Post ? $post->ID : null;
		}
		tve_enqueue_social_scripts();
		// hide tve more tag from front end display
		if ( ! is_editor_page() ) {
			tve_hide_more_tag();
			tve_enqueue_custom_fonts();
			tve_enqueue_custom_scripts();
			$frontend_options['is_editor_page'] = false;
		}
		wp_localize_script( 'tve_frontend', 'tve_frontend_options', $frontend_options );

		do_action( 'tve_frontend_extra_scripts' );

		$theme_dependency = get_post_meta( get_the_ID(), 'tve_disable_theme_dependency', true );
		if ( $theme_dependency === '1' ) {
			add_action( 'wp_print_styles', 'tve_remove_theme_css', PHP_INT_MAX );
			tve_enqueue_style( 'the_editor_no_theme', tve_editor_css() . '/no-theme.css' );
		}
	}
}

function tve_enqueue_icon_pack() {
	TCB_Icon_Manager::enqueue_icon_pack();
}
