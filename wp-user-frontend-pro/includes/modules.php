<?php

/**
 * Parses module file and retrieves module metadata
 *
 * @param  string $module_file Path to module file
 *
 * @return array
 */
function wpuf_pro_get_module_data( $module_file ) {
    $default_headers = array(
        'name'        => 'Plugin Name',
        'description' => 'Description',
        'plugin_uri'  => 'Plugin URI',
        'thumbnail'   => 'Thumbnail Name',
        'class'       => 'Integration Class',
        'author'      => 'Author',
        'author_uri'  => 'Author URI',
        'version'     => 'Version',
    );

    $module_data = get_file_data( $module_file, $default_headers, 'wpuf_pro_modules' );

    return $module_data;
}

/**
 * Gets all the available modules
 *
 * @return array
 */
function wpuf_pro_get_modules() {
    $module_root  = WPUF_PRO_MODULES;
    $modules_dir  = @opendir( $module_root);
    $modules      = array();
    $module_files = array();

    if ( $modules_dir ) {

        while ( ( $file = readdir( $modules_dir ) ) !== false ) {

            if ( substr( $file, 0, 1 ) == '.' ) {
                continue;
            }

            if ( is_dir( $module_root . '/' . $file ) ) {
                $plugins_subdir = @opendir( $module_root . '/' . $file );

                if ( $plugins_subdir ) {

                    while ( ( $subfile = readdir( $plugins_subdir ) ) !== false ) {
                        if ( substr( $subfile, 0, 1 ) == '.' ) {
                            continue;
                        }

                        if ( substr($subfile, -4) == '.php' ) {
                            $module_files[] = "$file/$subfile";
                        }
                    }

                    closedir( $plugins_subdir );
                }
            }
        }

        closedir( $modules_dir );
    }

    if ( $module_files ) {

        foreach ( $module_files as $module_file ) {

            if ( ! is_readable( "$module_root/$module_file" ) ) {
                continue;
            }

            $module_data = wpuf_pro_get_module_data( "$module_root/$module_file" );

            if ( empty ( $module_data['name'] ) ) {
                continue;
            }

            $file_base = wp_normalize_path( $module_file );


            $modules[ $file_base ] = $module_data;
        }
    }

    return $modules;
}

/**
 * Get a single module data
 *
 * @param  string $module
 *
 * @return WP_Error|Array
 */
function wpuf_pro_get_module( $module ) {
    $module_root  = WPUF_PRO_MODULES;

    $module_data = wpuf_pro_get_module_data( "$module_root/$module" );

    if ( empty ( $module_data['name'] ) ) {
        return new WP_Error( 'not-valid-plugin', __( 'This is not a valid plugin', 'wpuf-pro' ) );
    }

    return $module_data;
}

/**
 * Get the meta key to store the active module list
 *
 * @return string
 */
function wpuf_pro_active_module_key() {
    return 'wpuf_pro_active_modules';
}

/**
 * Get active modules
 *
 * @return array
 */
function wpuf_pro_get_active_modules() {
    return get_option( wpuf_pro_active_module_key(), array() );
}

/**
 * Check if a module is active
 *
 * @param  string $module basename
 *
 * @return boolean
 */
function wpuf_pro_is_module_active( $module ) {
    return in_array( $module, wpuf_pro_get_active_modules() );
}

/**
 * Check if a module is inactive
 *
 * @param  string $module basename
 *
 * @return boolean
 */
function wpuf_pro_is_module_inactive( $module ) {
    return ! wpuf_pro_is_module_active( $module );
}

/**
 * Activate a module
 *
 * @param  string $module basename of the module file
 *
 * @return WP_Error|null WP_Error on invalid file or null on success.
 */
function wpuf_pro_activate_module( $module ) {
    $current = wpuf_pro_get_active_modules();

    $module_root = WPUF_PRO_MODULES;
    $module_data = wpuf_pro_get_module_data( "$module_root/$module" );

    if ( empty ( $module_data['name'] ) ) {
        return new WP_Error( 'invalid-module', __( 'The module is invalid', 'wpuf-pro' ) );
    }

    // activate if enactive
    if ( wpuf_pro_is_module_inactive( $module ) ) {
        $current[] = $module;
        sort($current);

        // deactivate the addon if exists
        $module_class = wpuf_module_class_map( $module );

        if ( $module_class && class_exists( $module_class ) ) {
            $reflector = new ReflectionClass( $module_class );
            $addon_path = plugin_basename( $reflector->getFileName() );

            deactivate_plugins( $addon_path );

            return new WP_Error( 'plugin-exists', __( 'Deactivated the plugin, please try again.', 'wpuf-pro' ) );
        }

        $file_path = plugin_basename( "$module_root/$module" );

        if ( file_exists( "$module_root/$module" ) ) {
            require_once "$module_root/$module";
            do_action( "wpuf_activate_{$file_path}", $module );
        }

        update_option( wpuf_pro_active_module_key(), $current );
    }

    return null;
}

/**
 * Deactivate a module
 *
 * @param  string $module basename of the module file
 *
 * @return boolean
 */
function wpuf_pro_deactivate_module( $module ) {
    $current = wpuf_pro_get_active_modules();

    if ( wpuf_pro_is_module_active( $module ) ) {

        $key = array_search( $module, $current );

        if ( false !== $key ) {
            unset( $current[ $key ] );
            sort($current);
        }

        $module_root = WPUF_PRO_MODULES;
        $file_path = plugin_basename( "$module_root/$module" );

        if ( file_exists( "$module_root/$module" ) ) {
            require_once "$module_root/$module";
            do_action( "wpuf_deactivate_{$file_path}", $module );
        }

        update_option( wpuf_pro_active_module_key(), $current );

        return true;
    }

    return false;
}

/**
 * wpuf register activation hook description]
 *
 * @param string $file     full file path
 * @param array|string $function callback function
 *
 * @return void
 */
function wpuf_register_activation_hook( $file, $function ) {
    if ( file_exists( $file ) ) {
        require_once $file;
        $base_name = plugin_basename( $file );
        add_action( "wpuf_activate_{$base_name}", $function );
    }
}

/**
 * wpuf register deactivation hook description]
 *
 * @param string $file     full file path
 * @param array|string $function callback function
 *
 * @return void
 */
function wpuf_register_deactivation_hook( $file, $function ) {
    if ( file_exists( $file ) ) {
        require_once $file;
        $base_name = plugin_basename( $file );
        add_action( "wpuf_deactivate_{$base_name}", $function );
    }
}

function wpuf_module_class_map( $module='' ) {
    $modules = apply_filters( 'wpuf_module_list', array(
        'bp-profile/wpuf-bp.php'                    => 'WPUF_BP_Profile',
        'comments/comments.php'                     => 'WPUF_Comments',
        'mailchimp/wpuf-mailchimp.php'              => 'WPUF_Mailchimp',
        'mailpoet/wpuf-mailpoet.php'                => 'WPUF_Mailpoet',
        'pmpro/wpuf-pmpro.php'                      => 'WPUF_Pm_Pro',
        'qr-code-addons/wpuf-qr-code.php'           => 'WPUF_QR_Code',
        'sms-notification/wpuf-sms.php'             => 'WPUF_Admin_sms',
        'stripe/wpuf-stripe.php'                    => 'WPUF_Stripe',
        'user-analytics/wpuf-user-analytics.php'    => 'WPUF_User_Analytics',
        'user-directory/userlisting.php'            => 'WPUF_User_Listing',
    ) );

    if ( array_key_exists( $module, $modules) ) {
        return $modules[ $module ];
     }

    return false;
}
