<?php
/**
 * Utility functions to be used in all Thrive Products
 */

function tve_dash_get_thrivethemes_shares( $network = 'facebook' ) {
	$cache_for = 300; // 5 minutes
	$url       = 'https://thrivethemes.com/';
	$tt_shares = get_option( 'thrive_tt_shares', array() );
	$fn        = 'tve_dash_fetch_share_count_' . $network;
	if ( ! function_exists( $fn ) ) {
		return 0;
	}
	if ( empty( $tt_shares ) || ! isset( $tt_shares[ $network ] ) || time() - $tt_shares[ $network ]['last_fetch'] > $cache_for ) {
		$tt_shares[ $network ] = array(
			'count'      => $fn( $url ),
			'last_fetch' => time(),
		);
		update_option( 'thrive_tt_shares', $tt_shares );
	}

	return $tt_shares[ $network ]['count'];
}

/**
 * fetch the FB total number of shares for an url
 *
 * @param string $url
 *
 * @return int
 */
function tve_dash_fetch_share_count_facebook( $url ) {
	$data = _tve_dash_util_helper_get_json( 'http://graph.facebook.com/?id=' . rawurlencode( $url ) );

	return empty( $data['share']['share_count'] ) ? 0 : (int) $data['share']['share_count'];
}

/**
 * fetch the total number of shares for an url from twitter
 *
 * Update Nov. 2015 - twitter removed their share count API
 *
 * @param string $url
 *
 * @return int
 */
function tve_dash_fetch_share_count_twitter( $url ) {
	return 0;
}

/**
 * fetch the total number of shares for an url from Pinterest
 *
 * @param string $url
 *
 * @return int
 */
function tve_dash_fetch_share_count_pinterest( $url ) {
	$response = wp_remote_get( 'http://api.pinterest.com/v1/urls/count.json?callback=_&url=' . rawurlencode( $url ), array(
		'sslverify' => false,
	) );

	$body = wp_remote_retrieve_body( $response );
	if ( empty( $body ) ) {
		return 0;
	}
	$body = preg_replace( '#_\((.+?)\)$#', '$1', $body );
	$data = json_decode( $body, true );

	return empty( $data['count'] ) ? 0 : (int) $data['count'];
}

/**
 * fetch the total number of shares for an url from LinkedIn
 *
 * @param string $url
 *
 * @return int
 */
function tve_dash_fetch_share_count_linkedin( $url ) {
	$data = _tve_dash_util_helper_get_json( 'http://www.linkedin.com/countserv/count/share?format=json&url=' . rawurlencode( $url ) );

	return empty( $data['count'] ) ? 0 : (int) $data['count'];
}

/**
 * fetch the total number of shares for an url from Google
 *
 * @param string $url
 *
 * @return int
 */
function tve_dash_fetch_share_count_google( $url ) {
	$response = wp_remote_post( 'https://clients6.google.com/rpc', array(
		'sslverify' => false,
		'headers'   => array(
			'Content-type' => 'application/json'
		),
		'body'      => json_encode( array(
			array(
				'method'     => 'pos.plusones.get',
				'id'         => 'p',
				'params'     => array(
					'nolog'   => true,
					'id'      => $url,
					'source'  => 'widget',
					'userId'  => '@viewer',
					'groupId' => '@self',
				),
				'jsonrpc'    => '2.0',
				'key'        => 'p',
				'apiVersion' => 'v1'
			)
		) )
	) );

	if ( $response instanceof WP_Error ) {
		return 0;
	}

	$data = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( empty( $data ) || ! isset( $data[0]['result']['metadata']['globalCounts'] ) ) {
		return 0;
	}

	return (int) $data[0]['result']['metadata']['globalCounts']['count'];
}


/**
 * fetch the total number of shares for an url from Xing
 *
 * @param string $url
 *
 * @return int
 */
function tve_dash_fetch_share_count_xing( $url ) {
	$response = wp_remote_get( 'https://www.xing-share.com/app/share?op=get_share_button;counter=top;url=' . rawurlencode( $url ), array(
		'sslverify' => false
	) );

	if ( $response instanceof WP_Error ) {
		return 0;
	}

	$html = wp_remote_retrieve_body( $response );

	if ( ! preg_match_all( '#xing-count(.+?)(\d+)(.*?)</span>#', $html, $matches, PREG_SET_ORDER ) ) {
		return 0;
	}

	return (int) $matches[0][2];
}

/**
 * fetch and decode a JSON response from a URL
 *
 * @param string $url
 * @param string $fn
 *
 * @return array
 */
function _tve_dash_util_helper_get_json( $url, $fn = 'wp_remote_get' ) {
	$response = $fn( $url, array( 'sslverify' => false ) );
	if ( $response instanceof WP_Error ) {
		return array();
	}

	$body = wp_remote_retrieve_body( $response );
	if ( empty( $body ) ) {
		return array();
	}

	$data = json_decode( $body, true );

	return empty( $data ) ? array() : $data;
}

/**
 * Checks if the current request is performed by a crawler. It identifies crawlers by inspecting the user agent string
 *
 * @param bool $apply_filter Whether or not to apply the crawler detection filter ( tve_dash_is_crawler )
 * @param bool $check_cache_plugins
 *
 * @return int|false False form empty UAS. int 1|0 if a crawler has|not been detected
 */
function tve_dash_is_crawler( $apply_filter = false ) {

	if ( isset( $GLOBALS['thrive_dashboard_bot_detection'] ) ) {
		return $GLOBALS['thrive_dashboard_bot_detection'];
	}
	if ( empty( $_SERVER['HTTP_USER_AGENT'] ) ) {
		return $GLOBALS['thrive_dashboard_bot_detection'] = false;
	}

	$user_agent = trim( $_SERVER['HTTP_USER_AGENT'] );

	$uas_list = require plugin_dir_path( __FILE__ ) . '_crawlers.php';
	$regexp   = '#(' . implode( '|', $uas_list ) . ')#i';

	if ( ! $apply_filter ) {
		return $GLOBALS['thrive_dashboard_bot_detection'] = preg_match( $regexp, $user_agent );
	}

	/**
	 * Filter tve_dash_is_crawler
	 *
	 * @param int $detected 1|0 whether or not the crawler is detected
	 *
	 * @since 1.0.20
	 */
	return apply_filters( 'tve_dash_is_crawler', $GLOBALS['thrive_dashboard_bot_detection'] = preg_match( $regexp, $user_agent ) );
}

/**
 * Defines the products order in the Thrive Dashboard Wordpress Menu
 *
 * @return array
 */
function tve_dash_get_menu_products_order() {
	return array(
		'tva',
		'tcm',
		'tho',
		'tvo',
		'tab',
		'tl',
		'tqb',
		'tu',
		'license_manager',
		'general_settings',
		'ui_toolkit',
		'font_manager',
		'font_import_manager',
		'icon_manager',
		'tcb',
		'tcm_sub_menu',
		/*For Thrive Themes*/
		'thrive_theme_admin_page_templates',
		'thrive_theme_license_validation',
		'thrive_theme_admin_options',
	);
}

/**
 * Enqueue a script during an ajax call - this will make sure the script will be loaded in the page when the ajax call returns content
 *
 * @param string|array $handle
 * @param string|null $url if empty, it will try to get it from the WP_Scripts object
 * @param string $extra_js extra javascript to be outputted before the script
 *
 * @return bool
 */
function tve_dash_ajax_enqueue_script( $handle, $url = null, $extra_js = null ) {
	if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
		return false;
	}

	if ( empty( $url ) ) {
		$scripts = wp_scripts();
		$data    = $scripts->query( $handle );
		if ( empty( $data ) || ! is_object( $data ) || ! $data->src ) {
			return false;
		}
		$url      = $data->ver ? add_query_arg( 'ver', $data->ver, $data->src ) : $data->src;
		$extra_js = $scripts->get_data( $handle, 'data' );
		if ( ! preg_match( '|^(https?:)?//|', $url ) ) {
			$url = $scripts->base_url . $url;
		}

	}

	_tve_dash_ajax_enqueue( $handle, $url, 'js', $extra_js );

	return true;
}

/**
 * Enqueue a CSS external stylesheet during an ajax call
 *
 * @param string|array $handle
 * @param string|null $url if empty, it will try to get it from the WP_Scripts object
 * @param string $extra_js extra javascript to be outputted before the script
 *
 * @return bool
 */
function tve_dash_ajax_enqueue_style( $handle, $url = null ) {
	if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
		return false;
	}

	if ( empty( $url ) ) {
		$styles = wp_styles();
		$data   = $styles->query( $handle );
		if ( empty( $data ) || ! is_object( $data ) || ! $data->src ) {
			return false;
		}
		$url = $data->ver ? add_query_arg( 'ver', $data->ver, $data->src ) : $data->src;
		if ( ! preg_match( '|^(https?:)?//|', $url ) ) {
			$url = $styles->base_url . $url;
		}
	}

	_tve_dash_ajax_enqueue( $handle, $url, 'css' );

	return true;
}

/**
 * Enqueue a resource (css or js) based on $type parameter
 *
 * @param string $handle
 * @param string $url
 * @param string $type
 * @param string $extra used for javascript resources, will prepend a script node with these contents before loading the script
 */
function _tve_dash_ajax_enqueue( $handle, $url, $type = 'js', $extra = '' ) {
	if ( ! isset( $GLOBALS['tve_dash_resources'][ $type ] ) ) {
		$GLOBALS['tve_dash_resources'][ $type ] = array();
	}
	$GLOBALS['tve_dash_resources'][ $type ][ $handle ] = $url;

	if ( 'js' === $type && ! empty( $extra ) ) {
		$GLOBALS['tve_dash_resources'][ $type ][ $handle . '_before' ] = $extra;
	}
}

/**
 * Get server information
 * @return array
 */
function tve_get_debug_data() {

	$info = array();

	global $wpdb;

	$info[] = array(
		'name'  => 'PHP Version',
		'value' => PHP_VERSION,
	);

	$info[] = array(
		'name'  => 'WP Memory Limit',
		'value' => WP_MEMORY_LIMIT,
	);

	$info[] = array(
		'name'  => 'Memory Limit',
		'value' => ini_get( 'memory_limit' ),
	);

	$info[] = array(
		'name'  => 'Max upload size',
		'value' => size_format( wp_max_upload_size() ),
	);

	$info[] = array(
		'name'  => 'Max execution time',
		'value' => ini_get( 'max_execution_time' ),
	);

	$info[] = array(
		'name'  => 'Max Post Size',
		'value' => ini_get( 'post_max_size' ),
	);

	$info[] = array(
		'name'  => 'Max Input Vars',
		'value' => ini_get( 'max_input_vars' ),
	);

	$info[] = array(
		'name'  => 'MySQL Version',
		'value' => $wpdb->db_version(),
	);

	return $info;
}
