<?php

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * @since Quality Control 0.1
 */
function qc_setup() {
	global $qc_options;

	if ( is_array( $qc_options->modules ) ) {
		foreach ( $qc_options->modules as $module ) {
			$name = ( $module == 'changesets' ) ? $module : 'ticket-' . $module;
			add_theme_support( $name );
		}
	}

	add_theme_support( 'ticket-notifications' );

	add_theme_support( 'automatic-feed-links' );

	add_theme_support( 'custom-background' );

	add_editor_style();

	// Sidebars
	register_sidebar( array(
		'name' => __( 'Primary Widget Area', APP_TD ),
		'id' => 'primary-widget-area',
		'description' => __( 'The primary widget area.', APP_TD ),
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	register_sidebar( array(
		'name' => __( 'Blog Sidebar', APP_TD ),
		'id' => 'sidebar_blog',
		'description' => __( 'The widget area displayed on blog pages.', APP_TD ),
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	register_sidebar( array(
		'name' => __( 'Page Sidebar', APP_TD ),
		'id' => 'sidebar_page',
		'description' => __( 'The widget area displayed on pages.', APP_TD ),
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	// Menus
	register_nav_menu( 'header', __( 'Header Menu', APP_TD ) );
	register_nav_menu( 'footer', __( 'Footer Menu', APP_TD ) );

}

function qc_scripts() {
	wp_enqueue_script( 'jquery-timeago', get_template_directory_uri() . '/scripts/jquery.timeago.js', array( 'jquery' ), '1.4.1', true );

	if ( ! is_archive() ) {
		wp_enqueue_script( 'qc', get_template_directory_uri() . '/scripts/qc.js', array( 'suggest', 'validate', 'validate-lang' ), QC_VERSION, true );
		wp_localize_script( 'qc', 'QC_L10N', array(
			'ajaxurl' => admin_url( 'admin-ajax.php', 'relative' )
		) );
	}

	// used to convert header menu into select list on mobile devices
	wp_enqueue_script( 'tinynav', get_template_directory_uri() . '/scripts/jquery.tinynav.min.js', array( 'jquery' ), '1.2' );

	wp_enqueue_script( 'qc-general', get_template_directory_uri() . '/scripts/qc-general.js', array( 'jquery', 'jquery-timeago' ), QC_VERSION, true );

	wp_localize_script( 'qc-general', 'QC_General', array(
		'home_url' => home_url( '/' ),
		'text_mobile_navigation' => __( 'Navigation', APP_TD ),
	) );

	// used in 1140px framework
	wp_enqueue_script( 'css3-mediaqueries', get_template_directory_uri() . '/scripts/css3-mediaqueries.js', array( 'jquery' ), '1.0' );
}

/**
 * Includes files for supported modules. Needs to be loaded after
 * the child theme has had a chance to deactive some stuff.
 *
 * @since Quality Control 0.2
 */
function qc_theme_support_files() {
	require_if_theme_supports( 'ticket-priorities', dirname( __FILE__ ) . '/modules/priorities.php' );
	require_if_theme_supports( 'ticket-milestones', dirname( __FILE__ ) . '/modules/milestones.php' );
	require_if_theme_supports( 'ticket-categories', dirname( __FILE__ ) . '/modules/categories.php' );
	require_if_theme_supports( 'ticket-tags', dirname( __FILE__ ) . '/modules/tags.php' );
	require_if_theme_supports( 'ticket-attachments', dirname( __FILE__ ) . '/modules/attachments.php' );
	require_if_theme_supports( 'ticket-assignment', dirname( __FILE__ ) . '/modules/assignment.php' );
	require_if_theme_supports( 'changesets', dirname( __FILE__ ) . '/modules/changesets.php' );
}

function _tax_has_terms( $taxonomy ) {
	return (int) get_terms( $taxonomy, array( 'hide_empty' => false, 'fields' => 'count' ) );
}

function qc_query_vars( $qvars ) {
	$qvars[] = 'assigned';

	return $qvars;
}

function qc_search_shortcut( $request ) {
	if ( isset( $request['s'] ) && preg_match( '|^#(\d+)$|', trim( $request['s'] ), $matches ) ) {
		$post = get_post( $matches[1] );
		if ( QC_TICKET_PTYPE == $post->post_type && 'publish' == $post->post_status ) {
			wp_redirect( get_permalink( $post ) );
			die;
		}
	}

	return $request;
}

/**
 * Create the CSS to style the .ticket-status links.
 *
 * @since Quality Control 0.1
 * @global array $qc_options User-defined settings.
 */
function qc_status_colors_css() {
	global $qc_options;

	if ( empty( $qc_options->status_colors ) ) {
		return;
	}

	echo "\n<style type='text/css'>\n";

	foreach ( $qc_options->status_colors as $state => $colors ) {
		if ( empty( $colors ) ) {
			continue;
		}

		echo ".ticket-status.$state {";

		if ( ! empty( $colors['background'] ) ) {
			printf( 'background: %s;', $colors['background'] );
		}

		if ( ! empty( $colors['text'] ) ) {
			printf( 'color: %s;', $colors['text'] );
		}

		echo "}\n";
	}

	echo "</style>\n";
}

/**
 * This filter is run on wp_title. It wraps the separator in a
 * <span> tag so it can be styled. Also allows for more precise
 * control over certain page titles.
 *
 * @since Quality Control 0.1
 */
function qc_filter_page_title( $title, $separator ) {
	global $paged, $page, $post, $wp_query;

	$title = str_replace( $separator, '<span>' . $separator . '</span>', $title );

	if ( is_search() ) {
		$new_title = '<span>' . $separator . '</span> ' . sprintf( __( 'Containing "%s"', APP_TD ), get_search_query() );
	} elseif ( is_singular( QC_TICKET_PTYPE ) ) {
		$new_title = '<span>' . $separator . '</span> ' . sprintf( __( 'Ticket #%d', APP_TD ), $post->ID );
	} elseif ( qc_is_home_assigned() ) {
		$new_title = '<span>' . $separator . '</span> ' . sprintf( __( 'Assigned to %s', APP_TD ), get_query_var( 'assigned' ) );
	} elseif ( qc_is_home() ) {
		$new_title = '<span>' . $separator . '</span> ' . __( 'Dashboard', APP_TD );
	} elseif ( is_home() ) {
		$new_title = '<span>' . $separator . '</span> ' . __( 'Blog', APP_TD );
	} elseif ( is_front_page() ) {
		$new_title = '<span>' . $separator . '</span> ' . $post->post_title;
	}

	if ( ! empty( $new_title ) ) {
		return $new_title;
	} elseif ( qc_is_assigned() ) {
		$title = $title . ' <small>' . sprintf( __( '(assigned to %s)', APP_TD ), get_query_var( 'assigned' ) ) . '</small>';
	}

	return $title;
}

/**
 * For people who allow guests to submit tickets,
 * the author is blank. Create a label so something shows.
 *
 * @since Quality Control 0.1.2
 */
function qc_the_author( $display_name ) {
	if ( empty ( $display_name ) ) {
		return apply_filters( 'qc_anon_author', __( 'Anonymous', APP_TD ) );
	} else {
		return $display_name;
	}
}

/**
 * Automatically create links to other tickets when
 * someone submits #xxx where xxx is the ticket ID.
 *
 * TODO: don't replace tickets already inside links
 */
function qc_create_ticket_link( $content ) {
	return preg_replace_callback( '/#([0-9]+)/i', '_qc_create_ticket_link_cb', $content );
}

function _qc_create_ticket_link_cb( $matches ) {
	return html_link( get_permalink( $matches[1] ), $matches[0] );
}

/**
 * Update post_modified whenever a comment is added
 */
function _qc_touch_post( $comment_id, $comment ) {
	global $wpdb;

	$wpdb->update( $wpdb->posts,
		array(
			'post_modified' => current_time( 'mysql' ),
			'post_modified_gmt' => current_time( 'mysql', true ),
		),
		array( 'ID' => $comment->comment_post_ID )
	);

	update_post_meta( $comment->comment_post_ID, '_edit_last', $comment->user_id );
}

function qc_admin_bar() {
	if ( ! is_admin() && ! current_user_can( 'edit_users' ) ) {
		remove_action( 'admin_bar_menu', 'wp_admin_bar_dashboard_view_site_menu', 25 );
	}

	remove_action( 'admin_bar_menu', 'wp_admin_bar_new_content_menu', 40 );
	remove_action( 'admin_bar_menu', 'wp_admin_bar_comments_menu', 50 );
}

function qc_add_login_style() {
	wp_enqueue_style(
		'qc-login',
		get_template_directory_uri() . "/style-login.css",
		array(),
		QC_VERSION
	);
}

/**
 * Redirect to login page if site is locked, and disable menus and widgets
 */
function qc_lock_site() {
	global $qc_options, $pagenow;

	if ( ! $qc_options->lock_site || is_user_logged_in() || defined( 'DOING_AJAX' ) ) {
		return;
	}

	if ( qc_is_login_page_template() || $pagenow == 'wp-login.php' ) {
		add_filter( 'qc_can_view_all_tickets', '__return_false' );
		add_filter( 'wp_nav_menu_items', '__return_false' );
		add_filter( 'sidebars_widgets', '__return_false' );
		return;
	}

	appthemes_auth_redirect_login();
}
add_action( 'template_redirect', 'qc_lock_site' );

/**
 * Conditional tag to check if currently viewed page is one of login page templates
 */
function qc_is_login_page_template() {
	$templates = array(
		'form-login.php',
		'form-registration.php',
		'form-password-reset.php',
		'form-password-recovery.php',
	);

	foreach ( $templates as $template ) {
		if ( is_page_template( $template ) ) {
			return true;
		}
	}

	return false;
}
