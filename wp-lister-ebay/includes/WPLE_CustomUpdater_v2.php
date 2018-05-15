<?php
/**
 * Updater class for WooCommerce API Manager
 * Version: 1.2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Displays an inactive message if the API License Key has not yet been activated
 */
if ( get_option( 'wple_activated_key' ) != '1' ) {
    add_action( 'admin_notices', 'WPLE_Update_API::license_inactive_notice' );
}

class WPLE_Update_API {

	/**
	 * Self Upgrade Values
	 */
	// Base URL to the remote upgrade API Manager server. If not set then the Author URI is used.
	// public $upgrade_url = 'http://localhost/toddlahman/';
	// public $upgrade_url = 'http://wplabcom.staging.wpengine.com/';
	public $upgrade_url = 'http://update.wplab.de/beta/';

	/**
	 * @var string
	 */
	public $version = WPLE_VERSION;

	/**
	 * @var string
	 * This version is saved after an upgrade to compare this db version to $version
	 */
	public $wple_version_name = 'plugin_wple_version';

	/**
	 * @var string
	 */
	public $plugin_url;

	/**
	 * @var string
	 * used to defined localization for translation, but a string literal is preferred
	 *
	 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/issues/59
	 * http://markjaquith.wordpress.com/2011/10/06/translating-wordpress-plugins-and-themes-dont-get-clever/
	 * http://ottopress.com/2012/internationalization-youre-probably-doing-it-wrong/
	 */
	public $text_domain = 'wplister';

	/**
	 * Data defaults
	 * @var mixed
	 */
	private $ame_software_product_id;

	public $ame_data_key;
	public $ame_api_key;
	public $ame_activation_email;
	public $ame_product_id_key;
	public $ame_instance_key;
	public $ame_activated_key;
	// public $ame_deactivate_checkbox_key;

	public $ame_deactivate_checkbox;
	public $ame_activation_tab_key;
	public $ame_deactivation_tab_key;
	public $ame_settings_menu_title;
	public $ame_settings_title;
	public $ame_menu_tab_activation_title;
	public $ame_menu_tab_deactivation_title;

	public $ame_options;
	public $ame_plugin_name;
	public $ame_product_id;
	public $ame_renew_license_url;
	public $ame_instance_id;
	public $ame_domain;
	public $ame_software_version;
	public $ame_plugin_or_theme;

	public $ame_update_version;

	public $ame_update_check = 'am_example_plugin_update_check';

	/**
	 * Used to send any extra information.
	 * @var mixed array, object, string, etc.
	 */
	public $ame_extra;

    /**
     * @var The single instance of the class
     */
    protected static $_instance = null;

    public static function instance() {

        if ( is_null( self::$_instance ) ) {
        	self::$_instance = new self();
        }

        return self::$_instance;
    }

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.2
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wplister' ), '1.2' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.2
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wplister' ), '1.2' );
	}

	public function __construct() {

		// Run the activation function
		register_activation_hook( __FILE__, array( $this, 'on_plugin_activation' ) );

		// Ready for translation
		// load_plugin_textdomain( $this->text_domain, false, dirname( untrailingslashit( plugin_basename( __FILE__ ) ) ) . '/languages' );

		if ( is_admin() ) {

			// Check for external connection blocking
			add_action( 'admin_notices', array( $this, 'check_external_blocking' ) );

			/**
			 * Software Product ID is the product title string
			 * This value must be unique, and it must match the API tab for the product in WooCommerce
			 */
			$this->ame_software_product_id = 'WP-Lister Pro for eBay';

			/**
			 * Set all data defaults here
			 */
			$this->ame_data_key 				= 'wple_options';
			$this->ame_api_key 					= 'wple_api_key';
			$this->ame_activation_email 		= 'wple_activation_email';
			$this->ame_product_id_key 			= 'wple_product_id';
			$this->ame_instance_key 			= 'wple_instance';
			$this->ame_activated_key 			= 'wple_activated_key';
			// $this->ame_deactivate_checkbox_key 	= 'wple_deactivate_checkbox';

			/**
			 * Set all admin menu data
			 */
			$this->ame_deactivate_checkbox 			= 'am_deactivate_example_checkbox';
			$this->ame_activation_tab_key 			= 'wple_dashboard';
			$this->ame_deactivation_tab_key 		= 'wple_deactivation';
			$this->ame_settings_menu_title 			= 'WP-Lister for eBay';
			$this->ame_settings_title 				= 'WP-Lister for eBay';
			$this->ame_menu_tab_activation_title 	= __( 'License Activation', 'wplister' );
			$this->ame_menu_tab_deactivation_title 	= __( 'License Deactivation', 'wplister' );

			/**
			 * Set all software update data here
			 */
			$this->ame_options 				= get_option( $this->ame_data_key );
			$this->ame_plugin_name 			= 'wp-lister-ebay/wp-lister-ebay.php'; // same as plugin slug. if a theme use a theme name like 'twentyeleven'
			// $this->ame_plugin_name 			= untrailingslashit( plugin_basename( __FILE__ ) ); // same as plugin slug. if a theme use a theme name like 'twentyeleven'
			$this->ame_product_id 			= get_option( $this->ame_product_id_key ); // Software Title
			$this->ame_renew_license_url 	= 'https://www.wplab.com/my-account/'; // URL to renew a license. Trailing slash in the upgrade_url is required.
			$this->ame_instance_id 			= get_option( $this->ame_instance_key ); // Instance ID (unique to each blog activation)
			/**
			 * Some web hosts have security policies that block the : (colon) and // (slashes) in http://,
			 * so only the host portion of the URL can be sent. For example the host portion might be
			 * www.example.com or example.com. http://www.example.com includes the scheme http,
			 * and the host www.example.com.
			 * Sending only the host also eliminates issues when a client site changes from http to https,
			 * but their activation still uses the original scheme.
			 * To send only the host, use a line like the one below:
			 *
			 * $this->ame_domain = str_ireplace( array( 'http://', 'https://' ), '', home_url() ); // blog domain name
			 */
			$this->ame_domain 				= str_ireplace( array( 'http://', 'https://' ), '', home_url() ); // blog domain name
			$this->ame_software_version 	= $this->version; // The software version
			$this->ame_plugin_or_theme 		= 'plugin'; // 'theme' or 'plugin'

			// load license details
			$this->license_key 				= get_option( $this->ame_api_key ); 		 // API key
			$this->license_email 			= get_option( $this->ame_activation_email ); // API email
			$this->update_channel 			= get_option( 'wple_update_channel' ); 

			// Performs activations and deactivations of API License Keys
			require_once( plugin_dir_path( __FILE__ ) . 'am/classes/class-wc-key-api.php' );

			// Checks for software updatess
			require_once( plugin_dir_path( __FILE__ ) . 'am/classes/class-wc-plugin-update.php' );

			// Admin menu with the license key and license email form
			// require_once( plugin_dir_path( __FILE__ ) . 'am/admin/class-wc-api-manager-menu.php' );
			// $this->menu_page = new API_Manager_Example_MENU();

			// $options = get_option( $this->ame_data_key );

			/**
			 * Check for software updates
			 */
			if ( ! empty( $this->ame_instance_id ) ) {

				$this->update_check(
					$this->upgrade_url,
					$this->ame_plugin_name,
					$this->ame_product_id,
					$this->license_key,
					$this->license_email,
					$this->ame_renew_license_url,
					$this->ame_instance_id,
					$this->ame_domain,
					$this->ame_software_version,
					$this->ame_plugin_or_theme,
					$this->text_domain,
					$this->update_channel // extra
					);

			}

			// make sure to initialize instance
			if ( ! get_option( $this->ame_instance_key ) ) {
				$this->on_plugin_activation();
			}

		}

		/**
		 * Deletes all data if plugin deactivated
		 */
		register_deactivation_hook( __FILE__, array( $this, 'uninstall' ) );

	}

	/** Load Shared Classes as on-demand Instances **********************************************/

	/**
	 * API Key Class.
	 *
	 * @return WPLE_Api_Manager_Example_Key
	 */
	public function key() {
		return WPLE_Api_Manager_Example_Key::instance();
	}

	/**
	 * Update Check Class.
	 *
	 * @return WPLE_Updater_Update_API_Check
	 */
	public function update_check( $upgrade_url, $plugin_name, $product_id, $api_key, $activation_email, $renew_license_url, $instance, $domain, $software_version, $plugin_or_theme, $text_domain, $extra = '' ) {

		return WPLE_Updater_Update_API_Check::instance( $upgrade_url, $plugin_name, $product_id, $api_key, $activation_email, $renew_license_url, $instance, $domain, $software_version, $plugin_or_theme, $text_domain, $extra );
	}

	public function check_for_new_version( $quiet = false ) {

		$this->license_key   = get_option( 'wple_api_key' );
		$this->license_email = get_option( 'wple_activation_email' );

		$response = $this->update_check(
			$this->upgrade_url,
			$this->ame_plugin_name,
			$this->ame_product_id,
			$this->license_key,
			$this->license_email,
			$this->ame_renew_license_url,
			$this->ame_instance_id,
			$this->ame_domain,
			$this->ame_software_version,
			$this->ame_plugin_or_theme,
			$this->text_domain,
			$this->update_channel // extra
		)->check_for_new_version( $quiet );

		if ( is_object( $response ) && !empty( $response ) && $response->new_version ) {

			// store update data for later use
			$response->timestamp    = time();
			$response->title        = 'WP-Lister for eBay';
			// $response->upgrade_html = '';
			update_option( 'wple_update_details', $response );

			return $response;
		} else {

			// empty result means no new version
			$update = get_option( 'wple_update_details', new stdClass() );
			$update->timestamp    = time();
			$update->new_version  = WPLE_VERSION;
			$update->title        = 'WP-Lister for eBay';
			$update->upgrade_html = '';
			update_option( 'wple_update_details', $update );

		}

		return $response;
	} // check_for_new_version()

	public function plugin_url() {
		if ( isset( $this->plugin_url ) ) {
			return $this->plugin_url;
		}

		return $this->plugin_url = plugins_url( '/', __FILE__ );
	}

	/**
	 * Generate the default data arrays
	 */
	public function on_plugin_activation() {

		$global_options = array(
			$this->ame_api_key 				=> '',
			$this->ame_activation_email 	=> '',
		);

		update_option( $this->ame_data_key, $global_options );

		require_once( plugin_dir_path( __FILE__ ) . 'am/classes/class-wc-api-manager-passwords.php' );

		// $wple_password_management = new WPLE_Updater_Password_Management();

		// // Generate a unique installation $instance id
		// $instance = $wple_password_management->generate_password( 12, false );

		$single_options = array(
			$this->ame_product_id_key 			=> $this->ame_software_product_id,
			// $this->ame_instance_key 			=> $instance,
			// $this->ame_deactivate_checkbox_key 	=> 'on',
			$this->ame_activated_key 			=> '0',
		);

		foreach ( $single_options as $key => $value ) {
			update_option( $key, $value );
		}

		// reset instance ID
		$instance_key = str_replace( array('http://','https://','www.'), '', get_site_url() ); // example.com
		update_option( $this->ame_instance_key, $instance_key );						

		// $curr_ver = get_option( $this->wple_version_name );

		// // checks if the current plugin version is lower than the version being installed
		// if ( version_compare( $this->version, $curr_ver, '>' ) ) {
		// 	// update the version
		// 	update_option( $this->wple_version_name, $this->version );
		// }

	}

	/**
	 * Deletes all data if plugin deactivated
	 * @return void
	 */
	public function uninstall() {
		global $blog_id;

		$this->license_key_deactivation();

		// Remove options
		if ( is_multisite() ) {

			switch_to_blog( $blog_id );

			foreach ( array(
					$this->ame_data_key,
					$this->ame_product_id_key,
					$this->ame_instance_key,
					$this->ame_activated_key,
					) as $option) {

					delete_option( $option );

					}

			restore_current_blog();

		} else {

			foreach ( array(
					$this->ame_data_key,
					$this->ame_product_id_key,
					$this->ame_instance_key,
					$this->ame_activated_key
					) as $option) {

					delete_option( $option );

					}

		}

	}

	/**
	 * Deactivates the license on the API server
	 * @return void
	 */
	public function license_key_deactivation() {

		$activation_status = get_option( $this->ame_activated_key );

		$api_email = $this->ame_options[$this->ame_activation_email];
		$api_key = $this->ame_options[$this->ame_api_key];

		$args = array(
			'email' => $api_email,
			'licence_key' => $api_key,
			);

		if ( $activation_status == '1' && $api_key != '' && $api_email != '' ) {
			$this->key()->deactivate( $args ); // reset license key activation
		}
	}

    /**
     * Displays an inactive notice when the software is inactive.
     */
	public static function license_inactive_notice() { 
		if ( ! current_user_can( 'manage_options' ) ) return;
		if ( isset( $_GET['page'] ) && 'wplister' != substr($_GET['page'],0,8) ) return;
		if ( isset( $_GET['tab'] )  && 'license' == $_GET['tab'] ) return;
		?>
		<div id="message" class="error">
			<p><?php printf( __( 'The plugin license key has not been activated, so you won\'t be able to get updates and support! %sClick here%s to activate the license key and the plugin.', 'wplister' ), '<a href="' . esc_url( admin_url( 'admin.php?page=wplister-settings&tab=license' ) ) . '">', '</a>' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Check for external blocking contstant
	 * @return string
	 */
	public function check_external_blocking() {
		// show notice if external requests are blocked through the WP_HTTP_BLOCK_EXTERNAL constant
		if( defined( 'WP_HTTP_BLOCK_EXTERNAL' ) && WP_HTTP_BLOCK_EXTERNAL === true ) {

			// check if our API endpoint is in the allowed hosts
			$host = parse_url( $this->upgrade_url, PHP_URL_HOST );

			if( ! defined( 'WP_ACCESSIBLE_HOSTS' ) || stristr( WP_ACCESSIBLE_HOSTS, $host ) === false ) {
				?>
				<div class="error">
					<p><?php printf( __( '<b>Warning!</b> You\'re blocking external requests which means you won\'t be able to get %s updates. Please add %s to %s.', 'wplister' ), $this->ame_software_product_id, '<strong>' . $host . '</strong>', '<code>WP_ACCESSIBLE_HOSTS</code>'); ?></p>
				</div>
				<?php
			}

		}
	}

} // End of class

function WPLEUP() {
    return WPLE_Update_API::instance();
}

// Initialize the class instance only once
WPLEUP();

