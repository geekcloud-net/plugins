<?php
/**
 * Global functions file
 */

/**
 * check if the current TCB version is the one required by Thrive Ultimatum
 */
function tve_ult_check_tcb_version() {
	if ( ! EXTERNAL_TCB ) { // the internal TCB code will always be up to date
		return true;
	}

	if ( ! defined( 'TVE_VERSION' ) || TVE_VERSION != TVE_Ult_Const::REQUIRED_TCB_VERSION ) {
		return false;
	}

	return true;
}

/**
 * make sure the TL_product is displayed in thrive dashboard
 *
 * @param array $items
 *
 * @return array
 */
function tve_ult_add_to_dashboard( $items ) {
	require_once dirname( __FILE__ ) . '/classes/class-tu-product.php';

	$items[] = new TU_Product();

	return $items;
}

/**
 * Load the version file of Thrive Dashboard
 */
function tve_ult_load_dash_version() {
	$tve_dash_path      = dirname( dirname( __FILE__ ) ) . '/thrive-dashboard';
	$tve_dash_file_path = $tve_dash_path . '/version.php';

	if ( is_file( $tve_dash_file_path ) ) {
		$version                                  = require_once( $tve_dash_file_path );
		$GLOBALS['tve_dash_versions'][ $version ] = array(
			'path'   => $tve_dash_path . '/thrive-dashboard.php',
			'folder' => '/thrive-ultimatum',
			'from'   => 'plugins',
		);
	}
}

/**
 * Registers needed post types
 */
function tve_ult_init() {
	register_post_type( TVE_Ult_Const::POST_TYPE_NAME_FOR_CAMPAIGN, array(
		'publicly_queryable' => true,
		'query_var'          => false,
		'description'        => 'Entity for TU Campaign',
		'rewrite'            => false,
		'labels'             => array(
			'name' => 'Thrive Ultimatum - Campaign',
		),
	) );

	register_post_type( TVE_Ult_Const::POST_TYPE_NAME_FOR_SCHEDULE, array(
		'description'         => 'Each campaign can have more schedules',
		'publicly_queryable'  => true,
		'query_var'           => false,
		'exclude_from_search' => true,
		'rewrite'             => false,
		'hierarchical'        => true,
		'labels'              => array(
			'name' => 'Thrive Ultimatum - Schedules',
		),
	) );
}

/**
 * registers the Thrive Ultimatum widget
 */
function tve_ult_register_widget() {
	require_once TVE_Ult_Const::plugin_path( 'inc/classes/class-tu-campaign-widget.php' );

	register_widget( 'TU_Campaign_Widget' );
}

/**
 * Set the path where the translation files are being kept
 */
function tve_ult_load_plugin_textdomain() {
	$domain = TVE_Ult_Const::T;
	$locale = $locale = apply_filters( 'plugin_locale', get_locale(), $domain );
	$path   = 'thrive-ultimatum/languages/';
	load_textdomain( $domain, WP_LANG_DIR . '/thrive/' . $domain . '-' . $locale . '.mo' );
	load_plugin_textdomain( $domain, false, $path );
}

/**
 * Hooks into the leads conversion so it can start a evergreen
 * campaign or trigger a conversion event which also starts a evergreen campaign
 *
 * @param $group
 * @param $form_type
 * @param $variation
 * @param $test_model_id
 * @param $post_data
 * @param $current_screen
 */
function tve_ult_check_campaign_trigger( $group, $form_type, $variation, $test_model_id, $post_data, $current_screen ) {
	// get all campaigns
	$campaigns = tve_ult_get_campaigns( array(
		'get_designs'  => false,
		'only_running' => true,
		'get_logs'     => false,
		'lockdown'     => true,
	) );

	foreach ( $campaigns as $campaign ) {
		$settings = $campaign->settings;

		//check if we have only one trigger id and if we do let's make it an array
		if ( ! empty( $settings['trigger'] ) && ! is_array( $settings['trigger']['ids'] ) ) {
			$settings['trigger']['ids'] = array( $settings['trigger']['ids'] );
		}

		if ( ! empty( $settings['trigger'] ) && ! empty( $settings['trigger']['ids'] ) && in_array( $group->ID, $settings['trigger']['ids'] ) ) {
			//only for evergreen campaigns

			// set the start date of the campaign
			$start_date['date'] = date( 'j F Y', tve_ult_current_time( 'timestamp' ) );
			$start_date['time'] = date( 'H:i:s', tve_ult_current_time( 'timestamp' ) );
			// set the end date of the campaign
			$end_date['date'] = date( 'j F Y', strtotime( $start_date['date'] . '  ' . $start_date['time'] . ' + ' . $settings['end'] . ' days' ) );
			$end_date['time'] = $start_date['time'];

			$params = array( 'start_date' => $start_date, 'end_date' => $end_date );

			if ( ! empty( $campaign->lockdown ) ) {
				$params['lockdown'] = array(
					'email' => $post_data['email'],
					'type'  => 'leads',
				);
				$campaign->tu_schedule_instance->set_cookie_and_save_log( $params );

			} else {
				if ( ! isset( $_COOKIE[ TVE_Ult_Const::COOKIE_NAME . $campaign->ID ] ) ) {
					$data = $campaign->tu_schedule_instance->set_cookie_data( $params );
					$campaign->tu_schedule_instance->setCookie( $data['value'], $data['expire'] );
				}
			}
		}

		foreach ( $campaign->conversion_events as $event ) {
			if ( $event['trigger_options']['trigger'] === TVE_Ult_Const::TRIGGER_OPTION_CONVERSION && in_array( $group->ID, $event['trigger_options']['trigger_ids'] ) ) {
				$campaign->tu_schedule_instance->do_conversion( $event['trigger_options'] );
			}
		}
	}
}

/**
 * appends the WordPress tables prefix and the default tve_ult prefix to the table name
 *
 * @param string $table
 *
 * @return string the modified table name
 */
function tve_ult_table_name( $table ) {
	global $wpdb;

	return $wpdb->prefix . TVE_Ult_Const::DB_PREFIX . $table;
}

/**
 * check if there is a valid activated license for the TU plugin
 *
 * @return bool
 */
function tve_ult_license_activated() {
	return true;
}

/**
 * wrapper over the wp_enqueue_script function
 * it will add the plugin version to the script source if no version is specified
 *
 * @param        $handle
 * @param string $src
 * @param array  $deps
 * @param bool   $ver
 * @param bool   $in_footer
 */
function tve_ult_enqueue_script( $handle, $src = false, $deps = array(), $ver = false, $in_footer = false ) {
	if ( $ver === false ) {
		$ver = TVE_Ult_Const::PLUGIN_VERSION;
	}

	if ( defined( 'TVE_DEBUG' ) && TVE_DEBUG ) {
		$src = preg_replace( '#\.min\.js$#', '.js', $src );
	}

	wp_enqueue_script( $handle, $src, $deps, $ver, $in_footer );
}

/**
 * wrapper over the wp_enqueue_style function
 * it will add the plugin version to the style link if no version is specified
 *
 * @param             $handle
 * @param string|bool $src
 * @param array       $deps
 * @param bool|string $ver
 * @param string      $media
 */
function tve_ult_enqueue_style( $handle, $src = false, $deps = array(), $ver = false, $media = 'all' ) {
	if ( $ver === false ) {
		$ver = TVE_Ult_Const::PLUGIN_VERSION;
	}
	wp_enqueue_style( $handle, $src, $deps, $ver, $media );
}

/**
 * Get the TCB editor EDIT URL for a design
 *
 * @param int $post_id campaign
 * @param int $design_id
 *
 * @return string the url to open the editor for this variation
 */
function tve_ult_get_editor_url( $post_id, $design_id ) {
	$cache = isset( $GLOBALS['TVE_ULT_CACHE_PERMALINKS'] ) ? $GLOBALS['TVE_ULT_CACHE_PERMALINKS'] : array();
	if ( ! isset( $cache[ $post_id ] ) ) {
		$cache[ $post_id ]                   = set_url_scheme( get_permalink( $post_id ) );
		$GLOBALS['TVE_ULT_CACHE_PERMALINKS'] = $cache;
	}

	//We need the post to complete the full arguments
	$post        = get_post( $post_id );
	$editor_link = $cache[ $post_id ];
	$editor_link = esc_url( apply_filters( 'preview_post_link', add_query_arg( array(
		'tve'                                => 'true',
		TVE_Ult_Const::DESIGN_QUERY_KEY_NAME => $design_id,
		'r'                                  => uniqid(),
	), $editor_link ), $post ) );

	/**
	 * we need to make sure that if the admin is https, then the editor link is also https, otherwise any ajax requests through wp ajax api will not work
	 */
	$admin_ssl = strpos( admin_url(), 'https' ) === 0;

	return $admin_ssl ? str_replace( 'http://', 'https://', $editor_link ) : $editor_link;
}

/**
 * Builds TCB editor PREVIEW URL for a design
 *
 * @param int $post_id of a campaign
 * @param int $design_id
 *
 * @return string url to open the editor for this design
 */
function tve_ult_get_preview_url( $post_id, $design_id ) {
	$cache = isset( $GLOBALS['TVE_ULT_CACHE_PERMALINKS'] ) ? $GLOBALS['TVE_ULT_CACHE_PERMALINKS'] : array();
	if ( ! isset( $cache[ $post_id ] ) ) {
		$cache[ $post_id ]                   = set_url_scheme( get_permalink( $post_id ) );
		$GLOBALS['TVE_ULT_CACHE_PERMALINKS'] = $cache;
	}
	/*
	 * We need the post to complete the full arguments
	 */
	$post        = get_post( $post_id );
	$editor_link = $cache[ $post_id ];
	$editor_link = esc_url( apply_filters( 'preview_post_link', add_query_arg( array(
		TVE_Ult_Const::DESIGN_QUERY_KEY_NAME => $design_id,
		'r'                                  => uniqid(),
	), $editor_link ), $post ) );

	return $editor_link;
}

/**
 * Enqueues scripts and styles for a specific design
 *
 * @param array $for_design
 *
 * @return array
 */
function tve_ult_enqueue_design_scripts( $for_design = null ) {

	if ( empty( $for_design ) ) {
		global $design;
		$for_design = $design;
	}

	foreach ( array( 'fonts', 'css', 'js' ) as $f ) {
		$GLOBALS['tve_ult_res'][ $f ] = isset( $GLOBALS['tve_ult_res'][ $f ] ) ? $GLOBALS['tve_ult_res'][ $f ] : array();
	}

	if ( empty( $for_design ) || empty( $for_design[ TVE_Ult_Const::FIELD_TEMPLATE ] ) ) {
		return array(
			'fonts' => array(),
			'css'   => array(),
			'js'    => array(),
		);
	}

	/** enqueue Custom Fonts, if any */
	$fonts = tve_ult_editor_enqueue_custom_fonts( $for_design );

	$config = tve_ult_editor_get_template_config( $for_design[ TVE_Ult_Const::FIELD_TEMPLATE ] );

	/** custom fonts for the form */
	if ( ! empty( $config['fonts'] ) ) {
		foreach ( $config['fonts'] as $font ) {
			$fonts[ 'tve-ult-font-' . md5( $font ) ] = $font;
			wp_enqueue_style( 'tve-ult-font-' . md5( $font ), $font );
		}
	}

	/** include also the CSS for each type design */
	$css_key = 'tve-ult-' . TU_Template_Manager::type( $for_design[ TVE_Ult_Const::FIELD_TEMPLATE ] ) . '-' . str_replace( '.css', '', $config['css'] );
	if ( ! empty( $config['css'] ) ) {
		tve_ult_enqueue_style( $css_key, TVE_Ult_Const::plugin_url( 'tcb-bridge/editor-templates/css/' . TU_Template_Manager::type( $for_design['post_type'] ) . '/' . $config['css'] ) );
	}

	/** if any sdk is needed for the social sharing networks, enqueue that also */
	$globals = $for_design[ TVE_Ult_Const::FIELD_GLOBALS ];
	$js      = array();
	if ( ! empty( $globals['js_sdk'] ) ) {
		foreach ( $globals['js_sdk'] as $handle ) {
			$link                          = tve_social_get_sdk_link( $handle );
			$js[ 'tve_js_sdk_' . $handle ] = $link;
			wp_script_is( 'tve_js_sdk_' . $handle ) || wp_enqueue_script( 'tve_js_sdk_' . $handle, $link, array(), false );
		}
	}

	$css = array(
		$css_key => TVE_Ult_Const::plugin_url( 'tcb-bridge/editor-templates/css/' . TU_Template_Manager::type( $for_design['post_type'] ) . '/' . $config['css'] . '?ver=' . TVE_Ult_Const::PLUGIN_VERSION ),
	);

	if ( ! empty( $for_design[ TVE_Ult_Const::FIELD_ICON_PACK ] ) ) {
		tve_enqueue_icon_pack();
	}

	if ( ! empty( $for_design[ TVE_Ult_Const::FIELD_MASONRY ] ) ) {
		wp_enqueue_script( 'jquery-masonry' );
		$js['jquery-masonry'] = includes_url( 'js/jquery/jquery.masonry.min.js' );
	}
	if ( ! empty( $for_design[ TVE_Ult_Const::FIELD_TYPEFOCUS ] ) ) {
		tve_enqueue_script( 'tve_typed', tve_editor_js() . '/typed.min.js', array(), false, true );
		$js['tve_typed'] = tve_editor_js() . '/typed.min.js';
	}

	$GLOBALS['tve_ult_res']['fonts'] = array_merge( $GLOBALS['tve_ult_res']['fonts'], $fonts );
	$GLOBALS['tve_ult_res']['js']    = array_merge( $GLOBALS['tve_ult_res']['js'], $js );
	$GLOBALS['tve_ult_res']['css']   = array_merge( $GLOBALS['tve_ult_res']['css'], $css );

	return array(
		'fonts' => $fonts,
		'js'    => $js,
		'css'   => $css,
	);

}

/**
 * Enqueue the default styles when they are needed
 *
 * @return array the enqueued styles
 */
function tve_ult_enqueue_default_scripts() {

	$js_suffix = defined( 'TVE_DEBUG' ) && TVE_DEBUG ? '.js' : '.min.js';

	/* flat is the default style */
	global $tve_style_family_classes;
	$tve_style_families = tve_get_style_families();
	$style_family       = 'Flat';
	$style_key          = 'tve_style_family_' . strtolower( $tve_style_family_classes[ $style_family ] );

	/** Style family */
	wp_style_is( $style_key ) || tve_enqueue_style( $style_key, $tve_style_families[ $style_family ] );
	$GLOBALS['tve_ult_res']['css']      = isset( $GLOBALS['tve_ult_res']['css'] ) ? $GLOBALS['tve_ult_res']['css'] : array();
	$GLOBALS['tve_ult_res']['js']       = isset( $GLOBALS['tve_ult_res']['js'] ) ? $GLOBALS['tve_ult_res']['js'] : array();
	$GLOBALS['tve_ult_res']['localize'] = isset( $GLOBALS['tve_ult_res']['localize'] ) ? $GLOBALS['tve_ult_res']['localize'] : array();

	$GLOBALS['tve_ult_res']['css'][ $style_key ] = $tve_style_families[ $style_family ];

	$frontend_options = array(
		'is_editor_page'   => is_editor_page(),
		'page_events'      => array(),
		'is_single'        => 1,
		'ajaxurl'          => admin_url( 'admin-ajax.php' ),
		'social_fb_app_id' => function_exists( 'tve_get_social_fb_app_id' ) ? tve_get_social_fb_app_id() : '',
	);

	if ( ! wp_script_is( 'tve_frontend' ) ) {

		tve_enqueue_script( 'tve_frontend', tve_editor_js() . '/frontend' . $js_suffix, array( 'jquery' ), false, true );

		wp_localize_script( 'tve_frontend', 'tve_frontend_options', $frontend_options );
	}

	$GLOBALS['tve_ult_res']['localize']['tve_frontend_options'] = $frontend_options;
	$GLOBALS['tve_ult_res']['js']['tve_frontend']               = tve_editor_js() . '/frontend' . $js_suffix;

}

/**
 * Checks if we are previewing a design
 *
 * @return bool
 */
function tve_ult_is_preview_page() {
	global $design;

	return tve_ult_is_editable( get_the_ID() ) && ! empty( $design );
}

/**
 * Checks if we are editing a design
 */
function tve_ult_is_editor_page() {
	global $design;

	return isset( $_GET[ TVE_EDITOR_FLAG ] ) && ! empty( $design ) && tve_ult_is_editable( get_the_ID() );
}

/**
 * wrapper over the wp get_option function - it appends the tve_ult_ prefix to the option name
 *
 * @param      $name
 * @param bool $default
 *
 * @return mixed|void
 */
function tve_ult_get_option( $name, $default = false ) {
	$name  = 'tve_ult_' . preg_replace( '/^tve_ult_/', '', $name );
	$value = get_option( $name, $default );
	if ( $name == 'tve_ult_ajax_load' ) {
		return (int) $value;
	}

	return $value;
}

/**
 * Adds close button to the admin bar when editing a design
 *
 * @param $wp_admin_bar
 */
function tve_ult_admin_bar( $wp_admin_bar ) {

	if ( get_post_type() == 'tve_ult_campaign' ) {
		$args = array(
			'id'    => 'tve_button',
			'title' => '<span class="thrive-adminbar-icon"></span>' . __( 'Close Design Editor', TVE_Ult_Const::T ),
			'href'  => 'javascrip:void(0)',
			'meta'  => array(
				'class'   => 'thrive-admin-bar',
				'onclick' => 'window.close();',
			),
		);

		$wp_admin_bar->add_node( $args );
	}

	if ( ! is_admin() && current_user_can( 'edit_posts' ) && $campaigns = tve_ult_get_campaigns_for_promotion_page( get_the_ID() ) ) {
		$args = array(
			'id'    => 'tve_ult_button',
			'title' => '<span class="tvd-tooltipped" data-tooltip="' . __( 'You are currently viewing a page restricted by Thrive Ultimatum. You can only view this page because you are logged in as an admin.', TVE_Ult_Const::T ) . '"><span class="thrive-adminbar-icon-ultimatum"></span>' . __( 'Admin Mode', TVE_Ult_Const::T ) . '</span>',
			'meta'  => array(
				'class' => 'tvu-admin-bar-button',
			),
		);

		$wp_admin_bar->add_node( $args );
	}
}

/**
 * Hook to dashboard to add the settings when  ultimatum is activated
 *
 * @param $features
 *
 * @return mixed
 */
function tvu_dash_add_features( $features ) {
	$features['font_manager']     = true;
	$features['icon_manager']     = true;
	$features['api_connections']  = true;
	$features['general_settings'] = true;

	return $features;
}

/**
 * Initialize the Update Checker
 */
function tve_ult_update_checker() {
	new TVE_PluginUpdateChecker(
		'http://service-api.thrivethemes.com/plugin/update',
		TVE_Ult_Const::plugin_path( 'thrive-ultimatum.php' ),
		'thrive-ultimatum',
		12,
		'',
		'thrive_ultimatum'
	);
}

/**
 * Filter before and after params for Thrive Ultimatum Widgets
 * This only applies if the user has a Thrive theme installed -> remove the white space around the widget.
 *
 * @param array $params
 *
 * @return mixed
 */
function tve_ult_dynamic_sidebar_params( $params ) {
	if ( ! tve_check_if_thrive_theme() ) {
		return $params;
	}
	/**
	 * on our themes, we need to remove any other inside div in order for the widget to have the correct padding
	 */
	if ( $params[0]['widget_name'] === __( 'Thrive Ultimatum', TVE_Ult_Const::T ) ) {
		$params[0]['before_widget'] = '<section id="' . $params[0]['widget_id'] . '">';
		$params[0]['after_widget']  = '</section>';
	}

	return $params;
}

/**
 * Push the campaigns with their shortcode designs into array
 * Used int TCB tve_path_params so we can know what campaign has what shortcode designs
 *
 * @param $data
 *
 * @return mixed
 */
function tve_ult_append_shortcode_campaigns( $data ) {

	$campaigns = tve_ult_get_campaign_with_shortcodes();

	$data['tu_shortcode_campaigns'] = array();
	foreach ( $campaigns as $campaign ) {
		$data['tu_shortcode_campaigns'][ $campaign->ID ] = array(
			'post_title' => $campaign->post_title,
		);
		foreach ( $campaign->designs as $design ) {
			$data['tu_shortcode_campaigns'][ $campaign->ID ]['designs'][ $design['id'] ] = $design['post_title'];
		}
	}

	return $data;
}

/**
 * Callback for TCB TU Shortcode Element
 * for rendering a shortcode design
 *
 * @param      $arguments
 * @param bool $is_editor
 *
 * @return string
 */
function tve_ult_render_shortcode( $arguments, $is_editor = true ) {

	$design    = tve_ult_get_design( $arguments['tve_ult_shortcode'] );
	$resources = tve_ult_enqueue_design_scripts( $design );

	if ( empty( $design['tpl'] ) && $is_editor ) {
		return '<div class="thrive-shortcode-html">' . __( 'Shortcode Design not found', TVE_Ult_Const::T ) . '</div>';
	}

	$html = tve_ult_editor_custom_content( $design, $is_editor );
	list( $type, $key ) = TU_Template_Manager::tpl_type_key( $design[ TVE_Ult_Const::FIELD_TEMPLATE ] );
	$html = sprintf(
		'<div class="tve-ult-shortcode tvu-triggered">
			<div class="tl-style" id="tvu_%s" data-state="%s">%s</div>
		</div>',
		$key,
		$design['id'],
		$html
	);

	if ( ! empty( $design[ TVE_Ult_Const::FIELD_INLINE_CSS ] ) ) {
		$html .= sprintf( '<style type="text/css" class="tve_custom_style">%s</style>', stripslashes( $design[ TVE_Ult_Const::FIELD_INLINE_CSS ] ) );
	}

	ob_start();
	echo $html;
	foreach ( $resources['fonts'] as $font ) {
		echo '<link href="' . $font . '"/>';
	}
	foreach ( $resources['css'] as $css ) {
		echo '<link href="' . $css . '" type="text/css" rel="stylesheet"/>';
	}
	foreach ( $resources['js'] as $js ) {
		echo '<script type="text/javascript" src="' . $js . '"></script>';
	}
	$output = ob_get_clean();

	$is_preview = ! empty( $_GET['preview'] );

	if ( ! $is_editor && ! $is_preview ) {
		return class_exists( 'TU_Shortcode_Countdown' ) ? TU_Shortcode_Countdown::instance()->code( $arguments['tve_ult_campaign'], $arguments['tve_ult_shortcode'] ) : 'Shortcode could not be rendered';
	}

	return '<div class="thrive-shortcode-html">' . str_replace( 'id="tve_editor"', '', $output ) . '</div>';
}
