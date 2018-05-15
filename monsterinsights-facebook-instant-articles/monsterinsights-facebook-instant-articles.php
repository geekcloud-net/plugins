<?php
/**
 * Plugin Name: MonsterInsights - Facebook Instant Articles Addon
 * Plugin URI:  https://www.monsterinsights.com
 * Description: Adds Facebook Instant Articles Tracking to MonsterInsights.
 * Author:      MonsterInsights Team
 * Author URI:  https://www.monsterinsights.com
 * Version:     1.0.5
 * Text Domain: monsterinsights-facebook-instant-articles
 * Domain Path: languages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin class.
 *
 * @since 1.0.0
 *
 * @package MonsterInsights_FB_Instant_Articles
 * @author  Chris Christoff
 */
class MonsterInsights_FB_Instant_Articles {
	/**
	 * Holds the class object.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $version = '1.0.5';

	/**
	 * The name of the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $plugin_name = 'MonsterInsights Facebook Instant Articles';

	/**
	 * Unique plugin slug identifier.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $plugin_slug = 'monsterinsights-facebook-instant-articles';

	/**
	 * Plugin file.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $file;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
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
	 * @since 1.0.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( $this->plugin_slug, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Loads the plugin into WordPress.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		
		if ( ! defined( 'MONSTERINSIGHTS_PRO_VERSION' ) ) {
			// admin notice, MI not installed
			add_action( 'admin_notices', array( self::$instance, 'requires_monsterinsights' ) );
			return;
		}

		if ( ! defined( 'IA_PLUGIN_VERSION'  ) || version_compare( IA_PLUGIN_VERSION, '3.3.5', '<' ) ) {
			// AMP Plugin required
			add_action( 'admin_notices', array( self::$instance, 'requires_ia_plugin' ) );
			return;		
		}

		if ( version_compare( MONSTERINSIGHTS_VERSION, '6.2', '<' ) ) {
			// MonsterInsights version not supported
			add_action( 'admin_notices', array( self::$instance, 'requires_monsterinsights_version' ) );
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
	 * Initializes the addon updater.
	 *
	 * @since 1.0.0
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
	 * Loads all admin related files into scope.
	 *
	 * @since 1.1.0
	 */
	public function require_admin() {
		require plugin_dir_path( __FILE__ ) . 'includes/admin/admin.php';
	}

	/**
	 * Loads all frontend files into scope.
	 *
	 * @since 1.0.0
	 */
	public function require_frontend() {
		require plugin_dir_path( __FILE__ ) . 'includes/frontend/tracking.php';
	}

	/**
	 * Output a nag notice if the user does not have MI installed
	 *
	 * @access public
	 * @since 1.0.0
	 *
	 * @return 	void
	 */
	public function requires_monsterinsights() {
		?>
		<div class="error">
			<p><?php esc_html_e( 'Please install MonsterInsights Pro to use the MonsterInsights Facebook Instant Articles addon', 'monsterinsights-facebook-instant-articles' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Output a nag notice if the user does not have IA plugin installed
	 *
	 * @access public
	 * @since 1.0.0
	 *
	 * @return 	void
	 */
	public function requires_ia_plugin() {
		?>
		<div class="error">
			<p><?php esc_html_e( 'Please install the Instant Articles for WP plugin by Automattic version 3.3.5 or newer.', 'monsterinsights-facebook-instant-articles' ); ?></p>
		</div>
		<?php
	}
	
	/**
	 * Output a nag notice if the user does not have MI version installed
	 *
	 * @access public
	 * @since 1.0.0
	 *
	 * @return 	void
	 */
	public function requires_monsterinsights_version() {
		?>
		<div class="error">
			<p><?php esc_html_e( 'Please install or update MonsterInsights Pro with version 6.2.0 or newer to use the MonsterInsights Facebook Instant Articles addon', 'monsterinsights-facebook-instant-articles' ); ?></p>
		</div>
		<?php
	}

	 /**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return object The MonsterInsights_FB_Instant_Articles object.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof MonsterInsights_FB_Instant_Articles ) ) {
			self::$instance = new MonsterInsights_FB_Instant_Articles();
		}
		return self::$instance;
	}
}
// Load the main plugin class.
$MonsterInsights_FB_Instant_Articles = MonsterInsights_FB_Instant_Articles::get_instance();