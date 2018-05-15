<?php
/**
 * Plugin Name: WooCommerce UPS Shipping
 * Plugin URI: https://woocommerce.com/products/ups-shipping-method/
 * Description: WooCommerce UPS Shipping allows a store to obtain shipping rates for your orders dynamically via the UPS Shipping API.
 * Version: 3.2.9
 * WC requires at least: 2.6
 * WC tested up to: 3.3
 * Author: WooCommerce
 * Author URI: https://woocommerce.com
 *
 * Copyright: 2009-2017 WooCommerce.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * Woo: 18665:8dae58502913bac0fbcdcaba515ea998
 *
 * @package WC_Shipping_UPS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WC_SHIPPING_UPS_VERSION', '3.2.9' );

/**
 * Required functions
 */
if ( ! function_exists( 'woothemes_queue_update' ) ) {
	require_once( 'woo-includes/woo-functions.php' );
}

/**
 * Plugin updates
 */
woothemes_queue_update( plugin_basename( __FILE__ ), '8dae58502913bac0fbcdcaba515ea998', '18665' );

/**
 * Plugin activation check
 */
function wc_ups_activation_check() {
	if ( ! function_exists( 'simplexml_load_string' ) ) {
		deactivate_plugins( basename( __FILE__ ) );
		wp_die( "Sorry, but you can't run this plugin, it requires the SimpleXML library installed on your server/hosting to function." );
	}
}
register_activation_hook( __FILE__, 'wc_ups_activation_check' );

/**
 * WC_Shipping_UPS_Init Class
 */
class WC_Shipping_UPS_Init {

	/**
	 * Instance of this class.
	 *
	 * @var WC_Shipping_UPS_Init
	 */
	private static $instance;

	/**
	 * Get the class instance.
	 */
	public static function get_instance() {
		return null === self::$instance ? ( self::$instance = new self ) : self::$instance;
	}

	/**
	 * Initialize the plugin's public actions.
	 */
	public function __construct() {
		if ( class_exists( 'WC_Shipping_Method' ) ) {
			add_action( 'admin_init', array( $this, 'maybe_install' ), 5 );
			add_action( 'init', array( $this, 'load_textdomain' ) );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_links' ) );
			add_action( 'woocommerce_shipping_init', array( $this, 'includes' ) );
			add_filter( 'woocommerce_shipping_methods', array( $this, 'add_method' ) );
			add_action( 'admin_notices', array( $this, 'upgrade_notice' ) );
			add_action( 'wp_ajax_ups_dismiss_upgrade_notice', array( $this, 'dismiss_upgrade_notice' ) );
		} else {
			add_action( 'admin_notices', array( $this, 'wc_deactivated' ) );
		}
	}

	/**
	 * Include needed files.
	 */
	public function includes() {
		if ( version_compare( WC_VERSION, '2.6.0', '<' ) ) {
			include_once( dirname( __FILE__ ) . '/includes/class-wc-shipping-ups-deprecated.php' );
		} else {
			include_once( dirname( __FILE__ ) . '/includes/class-wc-shipping-ups.php' );
		}
	}

	/**
	 * Add UPS shipping method.
	 *
	 * @since 1.0.0
	 *
	 * @param array $methods Shipping methods.
	 *
	 * @return array Shipping methods.
	 */
	public function add_method( $methods ) {
		if ( version_compare( WC_VERSION, '2.6.0', '<' ) ) {
			$methods[] = 'WC_Shipping_UPS';
		} else {
			$methods['ups'] = 'WC_Shipping_UPS';
		}

		return $methods;
	}

	/**
	 * Localisation.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'woocommerce-shipping-ups', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Plugin page links.
	 *
	 * @since 1.0.0
	 *
	 * @param array $links Plugin links.
	 *
	 * @return array Plugin links.
	 */
	public function plugin_links( $links ) {
		$plugin_links = array(
			'<a href="http://support.woothemes.com/">' . __( 'Support', 'woocommerce-shipping-ups' ) . '</a>',
			'<a href="http://wcdocs.woothemes.com/user-guide/ups/">' . __( 'Docs', 'woocommerce-shipping-ups' ) . '</a>',
		);
		return array_merge( $plugin_links, $links );
	}

	/**
	 * WooCommerce not installed notice.
	 */
	public function wc_deactivated() {
		echo '<div class="error"><p>' . sprintf( __( 'WooCommerce UPS Shipping requires %s to be installed and active.', 'woocommerce-shipping-ups' ), '<a href="http://www.woothemes.com/woocommerce/" target="_blank">WooCommerce</a>' ) . '</p></div>';
	}

	/**
	 * Checks the plugin version.
	 *
	 * @since 3.2.0
	 * @version 3.2.0
	 * @return bool
	 */
	public function maybe_install() {
		// Only need to do this for versions less than 3.2.0 to migrate settings
		// to shipping zone instance.
		$doing_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;
		if ( ! $doing_ajax
		     && ! defined( 'IFRAME_REQUEST' )
		     && version_compare( WC_VERSION, '2.6.0', '>=' )
		     && version_compare( get_option( 'wc_ups_version' ), '3.2.0', '<' ) ) {

			$this->install();

		}

		return true;
	}

	/**
	 * Update/migration script.
	 *
	 * @since 3.2.0
	 * @version 3.2.0
	 */
	public function install() {
		// Get all saved settings and cache it.
		$ups_settings = get_option( 'woocommerce_ups_settings', false );

		// Settings exists.
		if ( $ups_settings ) {
			global $wpdb;

			// Unset un-needed settings.
			unset( $ups_settings['enabled'] );
			unset( $ups_settings['availability'] );
			unset( $ups_settings['countries'] );

			// First add it to the "rest of the world" zone when no ups
			// instance.
			if ( ! $this->is_zone_has_ups( 0 ) ) {
				$wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}woocommerce_shipping_zone_methods ( zone_id, method_id, method_order, is_enabled ) VALUES ( %d, %s, %d, %d )", 0, 'ups', 1, 1 ) );
				// Add settings to the newly created instance to options table.
				$instance = $wpdb->insert_id;
				add_option( 'woocommerce_ups_' . $instance . '_settings', $ups_settings );
			}

			update_option( 'woocommerce_ups_show_upgrade_notice', 'yes' );
		}

		update_option( 'wc_ups_version', WC_SHIPPING_UPS_VERSION );
	}

	/**
	 * Show the user a notice for plugin updates.
	 *
	 * @since 3.2.0
	 */
	public function upgrade_notice() {
		$show_notice = get_option( 'woocommerce_ups_show_upgrade_notice' );

		if ( 'yes' !== $show_notice ) {
			return;
		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
			
		$query_args = array( 'page' => 'wc-settings', 'tab' => 'shipping' );
		$zones_admin_url = add_query_arg( $query_args, get_admin_url() . 'admin.php' );
		?>
		<div class="notice notice-success is-dismissible wc-ups-notice">
			<p><?php echo sprintf( __( 'UPS now supports shipping zones. The zone settings were added to a new UPS method on the "Rest of the World" Zone. See the zones %1$shere%2$s ', 'woocommerce-shipping-ups' ), '<a href="' . $zones_admin_url . '">', '</a>' ); ?></p>
		</div>

		<script type="application/javascript">
			jQuery( '.notice.wc-ups-notice' ).on( 'click', '.notice-dismiss', function () {
				wp.ajax.post('ups_dismiss_upgrade_notice');
			});
		</script>
		<?php
	}

	/**
	 * Turn of the dismisable upgrade notice.
	 *
	 * @since 3.2.0
	 */
	public function dismiss_upgrade_notice() {
		update_option( 'woocommerce_ups_show_upgrade_notice', 'no' );
	}

	/**
	 * Helper method to check whether given zone_id has ups method instance.
	 *
	 * @since 3.2.0
	 *
	 * @param int $zone_id Zone ID.
	 *
	 * @return bool True if given zone_id has ups method instance.
	 */
	public function is_zone_has_ups( $zone_id ) {
		global $wpdb;

		return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(instance_id) FROM {$wpdb->prefix}woocommerce_shipping_zone_methods WHERE method_id = 'ups' AND zone_id = %d", $zone_id ) ) > 0;
	}
}

add_action( 'plugins_loaded' , array( 'WC_Shipping_UPS_Init', 'get_instance' ), 0 );
