<?php
/**
 * WPUF Update class
 *
 * Performas license validation and update checking
 *
 * @package WPUF
 */
class WPUF_Updates {

    /**
     * The product ID
     *
     * @var string
     */
    private $product_id = 'wpuf-pro';

    /**
     * The plugin slug
     *
     * @var string
     */
    private $plugin_slug = 'wp-user-frontend-pro';

    /**
     * The license plan id
     *
     * @var string
     */
    private $plan_id;

    const base_url     = 'https://wedevs.com/wp-user-frontend-pro/';
    const api_endpoint = 'http://api.wedevs.com/';
    const option       = 'wpuf_license';

    function __construct( $plan ) {

        $this->plan_id = $plan;

        // bail out if it's a local server
        if ( $this->is_local_server() ) {
            return;
        }

        add_action( 'wpuf_admin_menu', array($this, 'admin_menu'), 99 );

        if ( is_multisite() ) {

            if ( is_main_site() ) {
                add_action( 'admin_notices', array($this, 'license_enter_notice') );
                add_action( 'admin_notices', array($this, 'license_check_notice') );
            }

        } else {
            add_action( 'admin_notices', array($this, 'license_enter_notice') );
            add_action( 'admin_notices', array($this, 'license_check_notice') );
        }

        add_filter( 'pre_set_site_transient_update_plugins', array($this, 'check_update') );
        add_filter( 'pre_set_transient_update_plugins', array($this, 'check_update') );

        add_action( 'in_plugin_update_message-' . plugin_basename( WPUF_PRO_FILE ), array( $this, 'plugin_update_message' ) );

        add_filter( 'plugins_api', array($this, 'check_info'), 10, 3 );
    }

    /**
     * Check if the current server is localhost
     *
     * @return boolean
     */
    private function is_local_server() {
        return in_array( $_SERVER['REMOTE_ADDR'], array( '127.0.0.1', '::1' ) );
    }

    /**
     * Add admin menu to User Frontend option
     *
     * @return void
     */
    function admin_menu() {
        add_submenu_page( 'wp-user-frontend', __( 'Updates', 'wpuf-pro' ), __( 'Updates', 'wpuf-pro' ), 'activate_plugins', 'wpuf_updates', array($this, 'plugin_update') );
    }

    /**
     * Get license key
     *
     * @return array
     */
    function get_license_key() {
        return get_option( self::option, array() );
    }

    /**
     * Prompts the user to add license key if it's not already filled out
     *
     * @return void
     */
    function license_enter_notice() {
        if ( $key = $this->get_license_key() ) {
            return;
        }
        ?>
        <div class="error">
            <p><?php printf( __( 'Please <a href="%s">enter</a> your <strong>WP User Frontend</strong> plugin license key to get regular update and support.' ), admin_url( 'admin.php?page=wpuf_updates' ) ); ?></p>
        </div>
        <?php
    }

    /**
     * Check activation every 12 hours to the server
     *
     * @return void
     */
    function license_check_notice() {
        if ( !$key = $this->get_license_key() ) {
            return;
        }

        $error = __( 'Please activate your copy', 'wpuf-pro' );

        $license_status = get_option( 'wpuf_license_status' );

        if ( $license_status && $license_status->activated ) {

            $status = get_transient( self::option );

            if ( false === $status ) {
                $status = $this->activation();

                $duration = 60 * 60 * 12; // 12 hour
                set_transient( self::option, $status, $duration );
            }

            if ( $status && $status->success ) {

                // notice if validity expires
                if ( isset( $status->update ) ) {
                    $update = strtotime( $status->update );

                    if ( time() > $update ) {
                        echo '<div class="error">';
                        echo '<p>Your <strong>WP User Frontend Pro</strong> License has been expired. Please <a href="https://wedevs.com/account/" target="_blank">renew your license</a>.</p>';
                        echo '</div>';
                    }
                }

                return;
            }

            // may be the request didn't completed
            if ( !isset( $status->error )) {
                return;
            }

            $error = $status->error;
        }
        ?>
        <div class="error">
            <p><strong><?php _e( 'WP User Frontend Error:', 'wpuf-pro' ); ?></strong> <?php echo $error; ?></p>
        </div>
        <?php
    }

    /**
     * Activation request to the plugin server
     *
     * @return object
     */
    function activation( $request = 'check' ) {
        global $wp_version;

        if ( !$option = $this->get_license_key() ) {
            return;
        }

        $params = array(
            'timeout'    => ( ( defined( 'DOING_CRON' ) && DOING_CRON ) ? 30 : 3 ),
            'user-agent' => 'WordPress/' . $wp_version . '; ' . home_url( '/' ),
            'body'       => array(
                'request'     => $request,
                'email'       => $option['email'],
                'licence_key' => $option['key'],
                'product_id'  => $this->product_id,
                'instance'    => home_url()
            )
        );

        $response = wp_remote_post( self::api_endpoint . 'activation', $params );
        $update   = wp_remote_retrieve_body( $response );

        if ( is_wp_error( $response ) || $response['response']['code'] != 200 ) {
            if ( is_wp_error( $response ) ) {
                echo '<div class="error"><p><strong>WP User Frontend Pro Activation Error:</strong> ' . $response->get_error_message() . '</p></div>';
                return false;
            }

            if ( $response['response']['code'] != 200 ) {
                echo '<div class="error"><p><strong>WP User Frontend Pro Activation Error:</strong> ' . $response['response']['code'] .' - ' . $response['response']['message'] . '</p></div>';
                return false;
            }

            printf('<pre>%s</pre>', print_r( $response, true ) );
            return false;
        }

        return json_decode( $update );
    }

    /**
     * Integrates into plugin update api check
     *
     * @param object $transient
     * @return object
     */
    function check_update( $transient ) {
        if ( empty( $transient->checked ) ) {
            return $transient;
        }

        $remote_info = $this->get_info();

        if ( ! $remote_info ) {
            return $transient;
        }

        list( $plugin_name, $plugin_version) = $this->get_current_plugin_info();

        if ( version_compare( $plugin_version, $remote_info->latest, '<' ) ) {

            $obj              = new stdClass();
            $obj->slug        = $this->product_id;
            $obj->new_version = $remote_info->latest;
            $obj->url         = self::base_url;

            if ( isset( $remote_info->latest_url ) ) {
                $obj->package = $remote_info->latest_url;
            }

            $basefile = plugin_basename( WPUF_FILE );
            $transient->response[$basefile] = $obj;
        }

        return $transient;
    }

    /**
     * Plugin changelog information popup
     *
     * @param type $false
     * @param type $action
     * @param type $args
     * @return \stdClass|boolean
     */
    function check_info( $false, $action, $args ) {
        if ( isset( $args->slug ) && $this->plugin_slug == $args->slug ) {

            $remote_info = $this->get_info();

            $obj               = new stdClass();
            $obj->slug         = $this->plugin_slug;
            $obj->new_version  = isset( $remote_info->latest ) ? $remote_info->latest : '';
            $obj->name         = 'WP User Frontend Pro';
            $obj->last_updated = false;

            if ( isset( $remote_info->latest_url ) ) {
                $obj->download_link = $remote_info->latest_url;
            }

            $obj->sections = array(
                'description' => isset( $remote_info->msg ) ? $remote_info->msg : ''
            );

            return $obj;
        }

        return $false;
    }

    /**
     * Collects current plugin information
     *
     * @return array
     */
    function get_current_plugin_info() {
        require_once ABSPATH . '/wp-admin/includes/plugin.php';

        $plugin_data    = get_plugin_data( WPUF_FILE );
        $plugin_name    = $plugin_data['Name'];
        $plugin_version = $plugin_data['Version'];

        return array($plugin_name, $plugin_version);
    }

    /**
     * Get plugin update information from server
     *
     * @global string $wp_version
     * @global object $wpdb
     * @return boolean
     */
    function get_info() {
        global $wp_version, $wpdb;

        list( $plugin_name, $plugin_version) = $this->get_current_plugin_info();

        if ( is_multisite() ) {
            $wp_install = network_site_url();
        } else {
            $wp_install = home_url( '/' );
        }

        $license = $this->get_license_key();

        $params = array(
            'timeout'    => ( ( defined( 'DOING_CRON' ) && DOING_CRON ) ? 30 : 3 ),
            'user-agent' => 'WordPress/' . $wp_version . '; ' . home_url( '/' ),
            'body'       => array(
                'name'              => $plugin_name,
                'slug'              => $this->plan_id,
                'type'              => 'plugin',
                'version'           => $plugin_version,
                'wp_version'        => $wp_version,
                'php_version'       => phpversion(),
                'site_url'          => $wp_install,
                'license'           => isset( $license['key'] ) ? $license['key'] : '',
                'license_email'     => isset( $license['email'] ) ? $license['email'] : '',
                'product_id'        => $this->product_id
            )
        );

        $response = wp_remote_post( self::api_endpoint . 'update_check', $params );
        $update   = wp_remote_retrieve_body( $response );

        if ( is_wp_error( $response ) || $response['response']['code'] != 200 ) {
            return false;
        }

        return json_decode( $update );
    }

    /**
     * Plugin license enter admin UI
     *
     * @return void
     */
    function plugin_update() {
        $errors = array();
        if ( isset( $_POST['submit'] ) ) {
            if ( empty( $_POST['email'] ) ) {
                $errors[] = __( 'Empty email address', 'wpuf-pro' );
            }

            if ( empty( $_POST['license_key'] ) ) {
                $errors[] = __( 'Empty license key', 'wpuf-pro' );
            }

            if ( !$errors ) {
                update_option( self::option, array('email' => $_POST['email'], 'key' => $_POST['license_key']) );
                delete_transient( self::option );

                $license_status = get_option( 'wpuf_license_status' );

                if ( !isset( $license_status->activated ) || $license_status->activated != true ) {
                    $response = $this->activation( 'activation' );

                    if ( $response && isset( $response->activated ) && $response->activated ) {
                        update_option( 'wpuf_license_status', $response );
                    }
                }


                echo '<div class="updated"><p>' . __( 'Settings Saved', 'wpuf-pro' ) . '</p></div>';
            }
        }

        if ( isset( $_POST['delete_license'] ) ) {
            delete_option( self::option );
            delete_transient( self::option );
            delete_option( 'wpuf_license_status' );
        }

        $license = $this->get_license_key();
        $email = $license ? $license['email'] : '';
        $key = $license ? $license['key'] : '';
        ?>
        <div class="wrap">
            <h2><?php _e( 'Plugin Activation', 'wpuf-pro' ); ?></h2>

            <p class="description">
                Enter the E-mail address that was used for purchasing the plugin and the license key.
                We recommend you to enter those details to get regular <strong>plugin update and support</strong>.
            </p>

            <?php
            if ( $errors ) {
                foreach ($errors as $error) {
                    ?>
                    <div class="error"><p><?php echo $error; ?></p></div>
                    <?php
                }
            }

            $license_status = get_option( 'wpuf_license_status' );
            if ( !isset( $license_status->activated ) || $license_status->activated != true ) {
                ?>

                <form method="post" action="">
                    <table class="form-table">
                        <tr>
                            <th><?php _e( 'E-mail Address', 'wpuf-pro' ); ?></th>
                            <td>
                                <input type="email" name="email" class="regular-text" value="<?php echo esc_attr( $email ); ?>" required>
                                <span class="description"><?php _e( 'Enter your purchase Email address', 'wpuf-pro' ); ?></span>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e( 'License Key', 'wpuf-pro' ); ?></th>
                            <td>
                                <input type="text" name="license_key" class="regular-text" value="<?php echo esc_attr( $key ); ?>">
                                <span class="description"><?php _e( 'Enter your license key', 'wpuf-pro' ); ?></span>
                            </td>
                        </tr>
                    </table>

                    <?php submit_button( 'Save & Activate' ); ?>
                </form>
            <?php } else {

                if ( isset( $license_status->update ) ) {
                    $update = strtotime( $license_status->update );
                    $expired = false;

                    if ( time() > $update ) {
                        $string = __( 'has been expired %s ago', 'wpuf-pro' );
                        $expired = true;
                    } else {
                        $string = __( 'will expire in %s', 'wpuf-pro' );
                    }
                    // $expired = true;
                    ?>
                    <div class="updated <?php echo $expired ? 'error' : ''; ?>">
                        <p>
                            <strong><?php _e( 'Validity:', 'wpuf-pro' ); ?></strong>
                            <?php printf( 'Your license %s.', sprintf( $string, human_time_diff( $update, time() ) ) ); ?>
                        </p>

                        <?php if ( $expired ) { ?>
                            <p><a href="https://wedevs.com/account/" target="_blank" class="button-primary"><?php _e( 'Renew License', 'wpuf-pro' ); ?></a></p>
                        <?php } ?>
                    </div>
                    <?php
                }
                ?>

                <div class="updated">
                    <p><?php _e( 'Plugin is activated', 'wpuf-pro' ); ?></p>
                </div>

                <form method="post" action="">
                    <?php submit_button( __( 'Delete License', 'wpuf-pro' ), 'delete', 'delete_license' ); ?>
                </form>

            <?php } ?>
        </div>
        <?php
    }

    /**
     * Show plugin udpate message
     *
     * @since  1.0.0
     *
     * @param  array $args
     *
     * @return void
     */
    public function plugin_update_message( $args ) {
        $cache_key    = md5( $this->plugin_slug . '_plugin_' . sanitize_key( $this->product_id ) . '_version_info' );
        $version_info = get_transient( $cache_key );

        if ( false === $version_info ) {
            $version_info = $this->get_info();
            set_transient( $cache_key, $version_info, 3600 );
        }

        if ( version_compare( $args['Version'], $args['new_version'], '<' ) && empty( $version_info->latest_url ) ) {
            $upgrade_notice = sprintf( '</p><p class="%s-plugin-upgrade-notice" style="background: #dc4b02;color: #fff;padding: 10px;">Please <a href="%s" target="_blank">activate</a> your license key for getting regular updates and support',
                                $this->plugin_slug,
                                admin_url( 'admin.php?page=wpuf_updates' )
                            );
            echo apply_filters( $this->product_id . '_in_plugin_update_message', wp_kses_post( $upgrade_notice ) );
        }
    }

}
