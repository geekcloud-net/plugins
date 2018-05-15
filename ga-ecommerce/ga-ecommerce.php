<?php
/**
 * Plugin Name: MonsterInsights - eCommerce Addon
 * Plugin URI:  https://www.monsterinsights.com
 * Description: Adds eCommerce tracking options to MonsterInsights
 * Author:      MonsterInsights Team
 * Author URI:  https://www.monsterinsights.com
 * Version:     7.0.7
 * Text Domain: monsterinsights-ecommerce
 * Domain Path: languages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin class.
 *
 * @since 6.0.0
 *
 * @package MonsterInsights_eCommerce
 * @author  Chris Christoff
 */
class MonsterInsights_eCommerce {
	/**
	 * Holds the class object.
	 *
	 * @since 6.0.0
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since 6.0.0
	 *
	 * @var string
	 */
	public $version = '7.0.7';

	/**
	 * The name of the plugin.
	 *
	 * @since 6.0.0
	 *
	 * @var string
	 */
	public $plugin_name = 'MonsterInsights eCommerce';

	/**
	 * Unique plugin slug identifier.
	 *
	 * @since 6.0.0
	 *
	 * @var string
	 */
	public $plugin_slug = 'monsterinsights-ecommerce';

	/**
	 * Plugin file.
	 *
	 * @since 6.0.0
	 *
	 * @var string
	 */
	public $file;

	/**
	 * Primary class constructor.
	 *
	 * @since 6.0.0
	 */
	public function __construct() {	
		$this->file = __FILE__;

		// Load the plugin textdomain.
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );

		// Load the updater
		add_action( 'monsterinsights_updater', array( $this, 'updater' ) );

		// Load the plugin.
		add_action( 'monsterinsights_load_plugins', array( $this, 'init' ), 99 );
	}

	/**
	 * Loads the plugin textdomain for translation.
	 *
	 * @since 6.0.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( $this->plugin_slug, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Loads the plugin into WordPress.
	 *
	 * @since 6.0.0
	 */
	public function init() {

		if ( ! defined( 'MONSTERINSIGHTS_VERSION' ) || ! defined( 'MONSTERINSIGHTS_PRO_VERSION' ) || version_compare( MONSTERINSIGHTS_VERSION, '6.0', '<' ) ) {
			// admin notice, MI not installed
			add_action( 'admin_notices', array( self::$instance, 'requires_monsterinsights' ) );
			return;
		}

		// Load admin only components.
		if ( is_admin() ) {
			$this->require_admin();
		}

		// Load frontend components.
		$this->require_frontend();
	}

	/**
	 * Loads all admin related files into scope.
	 *
	 * @since 6.0.0
	 */
	public function require_admin() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/admin/settings.php';
		new MonsterInsights_GA_eCommerce_Admin();
	}

	/**
	 * Initializes the addon updater.
	 *
	 * @since 6.0.0
	 *
	 * @param string $key The user license key.
	 */
	function updater( $key ) {
		$args = array(
			'plugin_name' => $this->plugin_name,
			'plugin_slug' => $this->plugin_slug,
			'plugin_path' => plugin_basename( __FILE__ ),
			'plugin_url'  => trailingslashit( WP_PLUGIN_URL ) . $this->plugin_slug,
			'remote_url'  => 'https://www.monsterinsights.com/',
			'version'     => $this->version,
			'key'         => $key
		);
		
		$updater = new MonsterInsights_Updater( $args );
	}

	/**
	 * Loads all frontend files into scope.
	 *
	 * @since 6.0.0
	 */
	public function require_frontend() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/frontend/abstract-class-ecommerce-tracking.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/frontend/class-edd-ecommerce-tracking.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/frontend/class-woo-ecommerce-tracking.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/providers/edd.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/providers/woocommerce.php';

		$tracking_code     = monsterinsights_get_ua();
		$universal_enabled = monsterinsights_get_option( 'tracking_mode', false ) === 'ga' ? false : true;
		$enhanced_commerce = (bool) monsterinsights_get_option( 'enhanced_ecommerce', false ) && version_compare( MONSTERINSIGHTS_VERSION, '6.1.7', '>' );

		if ( $tracking_code && $universal_enabled && ! $enhanced_commerce ) {
			if ( class_exists( 'Easy_Digital_Downloads' ) ) {
				new MonsterInsights_GA_EDD_eCommerce_Tracking();
			}

			if ( class_exists( 'WooCommerce' ) ) {
				new MonsterInsights_GA_Woo_eCommerce_Tracking();
			}
		} else if ( $tracking_code && $universal_enabled && $enhanced_commerce ) {
			if ( class_exists( 'Easy_Digital_Downloads' ) ) {
				MonsterInsights_eCommerce_EDD_Integration::get_instance();
			}

			if ( class_exists( 'WooCommerce' ) ) {
				MonsterInsights_eCommerce_WooCommerce_Integration::get_instance();
			}
		}
	}

	/**
	 * Output a nag notice if the user does not have MI installed
	 *
	 * @access public
	 * @since 6.0.0
	 *
	 * @return 	void
	 */
	public function requires_monsterinsights() {
		?>
		<div class="error">
			<p><?php echo sprintf( esc_html__( 'Please install MonsterInsights Pro version 6.0 or newer to use the MonsterInsights eCommerce addon. If you have MonsterInsights Lite installed, please remove it from your site before attempting to install MonsterInsights Pro. You can download a copy of MonsterInsights Pro from the %1$saccount page%2$s on the monsterinsights.com website.', 'monsterinsights-ecommerce' ), '<a href="https://www.monsterinsights.com/my-account/" target="_blank">', '</a>' ); ?></p>
		</div>
		<?php
	}

	 /**
	 * Returns the singleton instance of the class.
	 *
	 * @since 6.0.0
	 *
	 * @return object The MonsterInsights_eCommerce object.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof MonsterInsights_eCommerce ) ) {
			self::$instance = new MonsterInsights_eCommerce();
		}
		return self::$instance;
	}
}
// Load the main plugin class.
$monsterinsights_ecommerce = MonsterInsights_eCommerce::get_instance();