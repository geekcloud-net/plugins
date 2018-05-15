<?php
/**
 * MonsterInsights Pro Installation and Automatic Upgrades.
 *
 * This file handles special Pro install & upgrade routines.
 *
 * @package MonsterInsights
 * @subpackage Install/Upgrade
 * @since 6.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { 
	exit;
}

// Add defaults for new installs
//do_action( 'monsterinsights_after_new_install_routine', $version );

// do_action( 'monsterinsights_after_existing_upgrade_routine', $version );

// Add default 
//do_action( 'monsterinsights_after_install_routine', $version );

function monsterinsights_auto_install_upgrade_addons( $key, $network ) {
	// Perform a request to verify the key.
	$verify = monsterinsights_yoast_perform_remote_request( $key, 'https://www.monsterinsights.com/', 'verify-key', array( 'tgm-updater-key' => trim( $key ) ) );

	// If it returns false, return.
	if ( ! $verify ) {
		return;
	}

	// If an error is returned, return.
	if ( ! empty( $verify->error ) ) {
		return;
	}

	// Otherwise, our request has been done successfully. Update the option and set the success message.
	if ( $network ) {
		$option                = array();
		$option['key']         = $key;
		$option['type']        = isset( $verify->type ) ? $verify->type : '';
		$option['is_expired']  = false;
		$option['is_disabled'] = false;
		$option['is_invalid']  = false;
		update_site_option( 'monsterinsights_license', $option );
	} else {
		$option                = array();
		$option['key']         = $key;
		$option['type']        = isset( $verify->type ) ? $verify->type : '';
		$option['is_expired']  = false;
		$option['is_disabled'] = false;
		$option['is_invalid']  = false;
		update_option( 'monsterinsights_license', $option );        
	}

	// $addons = monsterinsights_yoast_get_addons( $key, $option['type'] );
	
	// // If custom dimensions in use download, install, activate if not already
	// $options = get_option( 'yst_ga', array() );
	// if ( ! empty( $options['ga_general'] ) ) {
	// 	$options = $options['ga_general'];
	// }
	// if ( ! empty( $options['custom_dimensions' ] ) && ! empty( $addons ) && ! empty( $addons['licensed'] ) && is_array( $addons['licensed'] ) ) {
	// 	foreach( $addons['licensed'] as $addon ) {
	// 		if ( isset( $addon->title ) && $addon->title === 'MonsterInsights Dimensions' ) {
	// 			if ( isset( $addon->url ) && ! empty( $addon->slug ) ) {
	// 				$plugin_basename   = monsterinsights_yoast_get_plugin_basename_from_slug( 'monsterinsights-' . $addon->slug );
	// 				$installed_plugins = get_plugins();
	// 				if ( isset( $installed_plugins[ $plugin_basename ] ) ){
	// 					monsterinsights_yoast_activate_addon( $plugin_basename, $network );
	// 				} else {
	// 					monsterinsights_yoast_install_addon( $addon->url );
	// 					$plugin_basename   = monsterinsights_yoast_get_plugin_basename_from_slug( 'monsterinsights-' . $addon->slug );
	// 					monsterinsights_yoast_activate_addon( $plugin_basename, $network );
	// 				}
	// 			}
	// 		}
	// 	}
	// }

	// // if Adsense in use download, install, activate if not already
	// if ( ! empty( $options['track_adsense' ] ) && ! empty( $addons ) && ! empty( $addons['licensed'] ) && is_array( $addons['licensed'] ) ) {
	// 	foreach( $addons['licensed'] as $addon ) {
	// 		if ( isset( $addon->title ) && $addon->title === 'MonsterInsights Ads' ) {
	// 			if ( isset( $addon->url ) && ! empty( $addon->slug ) ) {
	// 				$plugin_basename   = monsterinsights_yoast_get_plugin_basename_from_slug( 'monsterinsights-' . $addon->slug );
	// 				$installed_plugins = get_plugins();
	// 				if ( isset( $installed_plugins[ $plugin_basename ] ) ){
	// 					monsterinsights_yoast_activate_addon( $plugin_basename, $network );
	// 				} else {
	// 					monsterinsights_yoast_install_addon( $addon->url );
	// 					$plugin_basename   = monsterinsights_yoast_get_plugin_basename_from_slug( 'monsterinsights-' . $addon->slug );
	// 					monsterinsights_yoast_activate_addon( $plugin_basename, $network );
	// 				}
	// 			}
	// 		}
	// 	}
	// }
}
add_action( 'monsterinsights_upgrade_from_yoast', 'monsterinsights_auto_install_upgrade_addons',10, 2 );


/**
 * Installs a MonsterInsights addon.
 *
 * @access public
 * @since 6.0.0
 */
function monsterinsights_yoast_install_addon( $download_url ) {
	// Install the addon.
	global $hook_suffix;
	require_once ABSPATH . 'wp-admin/includes/class-wp-screen.php';
	require_once ABSPATH . 'wp-admin/includes/screen.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';

	// Set the current screen to avoid undefined notices.
	set_current_screen();

	// Prepare variables.
	$method = '';
	$url    = add_query_arg(
		array(
			'page' => 'monsterinsights_settings'
		),
		admin_url( 'admin.php' )
	);
	$url = esc_url( $url );

	if ( false === ( $creds = request_filesystem_credentials( $url, $method, false, false, null ) ) ) {
		return;
	}

	// If we are not authenticated return.
	if ( ! WP_Filesystem( $creds ) ) {
		return;
	}

	// We do not need any extra credentials if we have gotten this far, so let's install the plugin.
	require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
	require_once MONSTERINSIGHTS_PLUGIN_DIR . 'includes/admin/licensing/skin.php';
	
	// Create the plugin upgrader with our custom skin.
	$installer = new Plugin_Upgrader( $skin = new MonsterInsights_Skin() );
	$installer->install( $download_url );

	// Flush the cache and return the newly installed plugin basename.
	wp_cache_flush();
}

/**
 * Activates a MonsterInsights addon.
 *
 * @access public
 * @since 6.0.0
 */
function monsterinsights_yoast_activate_addon( $plugin, $network = false ) {
	// Activate the addon.
	if ( $network ) {
		$activate = activate_plugin( $plugin, NULL, true );
	} else {
		$activate = activate_plugin( $plugin );
	}
}

/**
 * Retrieves addons from the stored transient or remote server.
 *
 * @since 6.0.0
 *
 * @return bool | array    false | Array of licensed and unlicensed Addons.
 */
function monsterinsights_yoast_get_addons( $key, $type ) {
	
	// Get addons data from transient or perform API query if no transient.
	if ( false === ( $addons = get_transient( '_monsterinsights_addons' ) ) ) {
		$addons = monsterinsights_get_addons_data( $key );
	}

	// If no Addons exist, return false
	if ( ! $addons ) {
		return false;
	}

	// Iterate through Addons, to build two arrays: 
	// - Addons the user is licensed to use,
	// - Addons the user isn't licensed to use.
	$results = array(
		'licensed'  => array(),
		'unlicensed'=> array(),
	);
	foreach ( (array) $addons as $i => $addon ) {

		// Determine whether the user is licensed to use this Addon or not.
		if ( 
			empty( $type ) ||
			( in_array( 'advanced', $addon->categories ) && $type != 'pro' ) ||
			( in_array( 'intermediate', $addon->categories ) && $type != 'plus' && $type != 'pro' ) ||
			( in_array( 'basic', $addon->categories ) && ( $type != 'basic' && $type != 'plus' && $type != 'pro' ) )
		) {
			// Unlicensed
			$results['unlicensed'][] = $addon;
			continue;
		}

		// Licensed
		$results['licensed'][] = $addon;

	}

	// Return Addons, split by licensed and unlicensed.
	return $results;

}

/**
 * Queries the remote URL via wp_remote_post and returns a json decoded response.
 *
 * @since 6.0.0
 *
 * @param string $action        The name of the $_POST action var.
 * @param array $body           The content to retrieve from the remote URL.
 * @param array $headers        The headers to send to the remote URL.
 * @param string $return_format The format for returning content from the remote URL.
 * @return string|bool          Json decoded response on success, false on failure.
 */
function monsterinsights_yoast_perform_remote_request( $key, $remote_url, $action, $body = array(), $headers = array(), $return_format = 'json' ) {

	// Build the body of the request.
	$body = wp_parse_args(
		$body,
		array(
			'tgm-updater-action'     => $action,
			'tgm-updater-key'        => $key,
			'tgm-updater-wp-version' => get_bloginfo( 'version' ),
			'tgm-updater-referer'    => site_url(),
			'tgm-updater-mi-version' => MONSTERINSIGHTS_VERSION,
			'tgm-updater-is-pro'     => monsterinsights_is_pro_version(),
		)
	);
	$body = http_build_query( $body, '', '&' );

	// Build the headers of the request.
	$headers = wp_parse_args(
		$headers,
		array(
			'Content-Type'   => 'application/x-www-form-urlencoded',
			'Content-Length' => strlen( $body )
		)
	);

	// Setup variable for wp_remote_post.
	$post = array(
		'headers' => $headers,
		'body'    => $body
	);

	// Perform the query and retrieve the response.
	$response      = wp_remote_post( esc_url_raw( $remote_url ), $post );
	$response_code = wp_remote_retrieve_response_code( $response );
	$response_body = wp_remote_retrieve_body( $response );

	// Bail out early if there are any errors.
	if ( 200 != $response_code || is_wp_error( $response_body ) ) {
		return false;
	}

	// Return the json decoded content.
	return json_decode( $response_body );

}


function monsterinsights_yoast_get_plugin_basename_from_slug( $slug ) {
	$keys = array_keys( get_plugins() );

	foreach ( $keys as $key ) {
		if ( preg_match( '|^' . $slug . '|', $key ) ) {
			return $key;
		}
	}

	return $slug;

}