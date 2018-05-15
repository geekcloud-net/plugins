<?php
/* 
Plugin Name: WP-Lister Pro for Amazon
Plugin URI: https://www.wplab.com/plugins/wp-lister-for-amazon/
Description: List your products on Amazon the easy way.
Version: 0.9.7.8
Author: WP Lab
Author URI: https://www.wplab.com/ 
Max WP Version: 4.9.5
WC requires at least: 3.0.0
WC tested up to: 3.3.3
Text Domain: wpla
Domain Path: /languages/
License: GPL2+
*/

if ( class_exists('WPLA_WPLister') ) die(sprintf( 'WP-Lister for Amazon %s is already installed and activated. Please deactivate any other version before you activate this one.', WPLA_VERSION ));

define('WPLA_VERSION', '0.9.7.8' );
define('WPLA_PATH', realpath( dirname(__FILE__) ) );
define('WPLA_URL', plugins_url() . '/' . basename(dirname(__FILE__)) . '/' );

// set up autoloader
require_once( WPLA_PATH . '/classes/core/WPLA_Autoloader.php' );
spl_autoload_register('WPLA_Autoloader::autoload');

require_once( WPLA_PATH . '/classes/core/WPLA_Functions.php' );

// legacy support for PHP 5.2
if ( version_compare(phpversion(), '5.3', '<')) {
	require_once( WPLA_PATH . '/includes/php52_legacy.php' );
}

## BEGIN PRO ##
define('WPLA_LIGHT', false );

// load updater
if ( get_option( 'wpla_updater_mode' ) == 'old' ) {
	require_once( WPLA_PATH . '/classes/helper/WPLA_CustomUpdater.php' );
} else {
	require_once( WPLA_PATH . '/includes/WPLA_CustomUpdater_v2.php' );
}
## END PRO ##
if ( ! defined('WPLA_LIGHT')) define('WPLA_LIGHT', true );


class WPLA_WPLister extends WPLA_BasePlugin {
	
	var $pages         = array();
	var $accounts      = array();
	var $shortcodes    = array();
	var $multi_account = false;
	var $logger;

	protected static $_instance = null;

	// get singleton instance
    public static function get_instance() {

        if ( is_null( self::$_instance ) ) {
        	self::$_instance = new self();
        }

        return self::$_instance;
    }

	public function __construct() {
		parent::__construct();

		$this->initLogger();
		$this->initClasses();
		$this->loadAccounts();
		$this->loadShortcodes();
		
		if ( is_admin() ) {
			// require_once( WPLA_PATH . '/classes/integration/WooBackendIntegration.php' );
			// $oInstall 	= new WPLister_Install( __FILE__ );
			// $oUninstall = new WPLister_Uninstall( __FILE__ );
			$this->loadPages();
			$this->checkPermissions();

			// load MSRP integration
			if ( ! class_exists( 'woocommerce_msrp_admin' ) ) {
				require_once( WPLA_PATH . '/classes/integration/WPLA_MSRP_Addon.php' );
			}

		}
	}
		
	// initialize logger
	public function initLogger() {
		// global $wpla_logger;

		define( 'WPLA_DEBUG', get_option('wpla_log_level') );

		$this->logger = new WPLA_Logger();

        new WPLA_StocksLogger();
	}
		
	// initialize core classes
	public function initClasses() {

		$this->api_hooks        = new WPLA_API_Hooks();	
		$this->ajax_hactions    = new WPLA_AjaxHandler();
		$this->cron_actions     = new WPLA_CronActions();
		$this->toolbar          = new WPLA_Toolbar();
		$this->memcache         = new WPLA_MemCache();
		$this->messages         = new WPLA_AdminMessages();
		
		$this->woo_backend      = new WPLA_WooBackendIntegration();
		$this->woo_mb_product   = new WPLA_Product_MetaBox();
		$this->woo_mb_images    = new WPLA_Product_Images_MetaBox();
		$this->woo_mb_feed      = new WPLA_Product_Feed_MetaBox();

		## BEGIN PRO ##
		$this->woo_mb_order     = new WPLA_Order_MetaBox();
		## END PRO ##
		$this->minmax_wiz       = new WPLA_MinMaxPriceWizard();

        $this->woo_product_attr = new WPLA_Product_Attributes();

        if ( get_option('wpla_fba_wc_shipping_options') == 1 ) {
			$this->woo_shipping = new WPLA_Shipping_Options();
        }
	}

	public function loadAccounts() {
		$accounts = get_option('wpla_db_version') > 3 ? WPLA_AmazonAccount::getAll( true ) : array();
		foreach ($accounts as $account) {
			$this->accounts[ $account->id ] = $account;
		}
		$this->multi_account = count( $this->accounts ) > 1 ? true : false;
	}

    /**
     * Initialize the WPLA_WPLister::shortcodes array with custom shortcodes from the DB
     */
	public function loadShortcodes() {

		$shortcodes = get_option('wpla_custom_shortcodes');
		if ( ! is_array($shortcodes) ) return;

		$this->shortcodes = $shortcodes;
	}

    /**
     * Get the available shortcodes
     * @return array
     */
	public function getShortcodes() {
        return apply_filters( 'wpla_custom_values', $this->shortcodes );
    }

	public function loadPages() {

		if ( is_network_admin() ) {
	
			// $this->pages['sites']    	= new WPLA_NetworkAdminPage();
			// $this->pages['settings']     = new WPLA_SettingsPage();
	
		} else {

			if ( ( is_multisite() ) && ( self::getOption('is_enabled') == 'N' ) ) return;

			$this->pages['listings']     = new WPLA_ListingsPage();
			$this->pages['orders']       = new WPLA_OrdersPage();
			$this->pages['reports']      = new WPLA_ReportsPage();
			$this->pages['feeds']        = new WPLA_FeedsPage();
			$this->pages['profiles']     = new WPLA_ProfilesPage();
			$this->pages['import']     	 = new WPLA_ImportPage();
			$this->pages['tools']     	 = new WPLA_ToolsPage();
			$this->pages['repricing']  	 = new WPLA_RepricingPage();
			$this->pages['stocklog']  	 = new WPLA_StockLogPage();
			$this->pages['skugen']  	 = new WPLA_SkuGenPage();
			$this->pages['settings']     = new WPLA_SettingsPage();
			$this->pages['accounts']     = new WPLA_AccountsPage();
			$this->pages['tutorial']  	 = new WPLA_HelpPage();
			$this->pages['log']       	 = new WPLA_LogPage();

		}

	}
		
	public function onWpInit() {

		// load language
		load_plugin_textdomain( 'wpla', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );	

	}

	public function onWpAdminInit() {

		add_action( 'admin_print_styles', array( &$this, 'onWpPrintStyles' ) );

    	// add / fix enqueued scripts - only on wpla pages
    	if  ( ( isset( $_GET['page'] ) ) && ( substr( $_GET['page'], 0, 4 ) == 'wpla') ) {
		    add_action( 'wp_print_scripts', array( &$this, 'onWpPrintScripts' ), 99 );
    	}

    	// modify bulk actions menu - only on products list page
		if ( $this->isProductsPage() ) {
			add_action( 'admin_footer', array( &$this, 'modifyProductsBulkActionMenu' ) );
			add_action( 'admin_footer', array( &$this, 'modifyProductsBulkActionMenu2' ), 100 );
			add_action( 'admin_print_styles', array( &$this, 'printProductsPageStyles' ) );
		}
        add_action( 'wp_print_scripts', array( &$this, 'printProductsPageScripts' ) );
		add_action( 'admin_print_styles', array( &$this, 'printOrdersPageStyles' ) );

	}
	
	public function onWpPrintStyles() {
		if  ( ( isset( $_GET['page'] ) ) && ( substr( $_GET['page'], 0, 4 ) == 'wpla') ) {
			wp_register_style( 'wpla_style', self::$PLUGIN_URL.'css/style.css' );
			wp_enqueue_style( 'wpla_style' );
		}
	}

	// add custom bulk action 'list_on_amazon' for cpt products
	// should be called by 'admin_footer' action
	public function modifyProductsBulkActionMenu() {	
		if ( ! current_user_can( 'manage_amazon_listings' ) ) return;
		?>
	    <script type="text/javascript">
    	    jQuery(document).ready(function() {
        	    jQuery('<option>').val('list_on_amazon').text('<?php echo __('List on Amazon','wpla') ?>').appendTo("select[name='action']");
            	jQuery('<option>').val('list_on_amazon').text('<?php echo __('List on Amazon','wpla') ?>').appendTo("select[name='action2']");
	        });
    	</script>
    	<?php
	}

	// add custom bulk action 'remove_from_amazon' for cpt products
	public function modifyProductsBulkActionMenu2() {	
		if ( ! current_user_can( 'manage_amazon_listings' ) ) return;
		?>
	    <script type="text/javascript">
    	    jQuery(document).ready(function() {
        	    jQuery('<option>').val('remove_from_amazon').text('<?php echo __('Remove from Amazon','wpla') ?>').appendTo("select[name='action']");
            	jQuery('<option>').val('remove_from_amazon').text('<?php echo __('Remove from Amazon','wpla') ?>').appendTo("select[name='action2']");
	        });

		    jQuery(".tablenav .actions input[type='submit'].action").on('click', function() {
		        
		        if ( 'doaction'  == this.id ) var selected_action = jQuery("select[name='action']").first().val();
		        if ( 'doaction2' == this.id ) var selected_action = jQuery("select[name='action2']").first().val();

				if ( selected_action == 'remove_from_amazon' ) {
					var confirmed = confirm("<?php echo __('Are you sure you want to do this?','wpla') .' '.  __('Removing the listing also removes the sales history for the item. If you were to relist these listings later you would then start out with a lower sales rank.','wpla') ?>");
					if ( ! confirmed ) return false;
				}

		    });
    	</script>
    	<?php
	}

	public function printProductsPageStyles() {	
		?>
    	<style type="text/css">
			table.wp-list-table .column-listed_on_amazon { width: 25px; }    	
    	</style>
    	<?php
	}
	public function printOrdersPageStyles() {	
		?>
    	<style type="text/css">
			.post-type-shop_order table.wp-list-table .column-wpl_order_src { width: 56px; text-align: center; padding-left: 1px; padding-right: 1px; }

			@media screen and (max-width: 782px) {
				.post-type-shop_order table.wp-list-table .column-wpl_order_src { display: none !important; }
			}
    	</style>
    	<?php
	}

	public function onWpPrintScripts() {
		global $wp_scripts;

    	// fix thickbox display problems caused by other plugins 
        wp_dequeue_script( 'media-upload' );
        
        // if any registered script depends on media-upload, dequeue that too
        foreach ( $wp_scripts->registered as $script ) {
            if ( in_array( 'media-upload', $script->deps ) ) {
                wp_dequeue_script( $script->handle );
            }
        }

        // enqueue tipTip.js 
        wp_register_script( 'jquery-tiptip', WPLA_URL . '/js/jquery-tiptip/jquery.tipTip.min.js', array( 'jquery' ), WPLA_VERSION, true );
        wp_enqueue_script( 'jquery-tiptip' );


		// PriceMatcher
		wp_register_script( 'wpla_price_matcher', self::$PLUGIN_URL.'js/classes/PriceMatcher.js?ver='.time(), array( 'jquery' ) );
		wp_enqueue_script( 'wpla_price_matcher' );

		wp_localize_script('wpla_price_matcher', 'wpla_PriceMatcher_i18n', array(
				'WPLA_URL' 	=> WPLA_URL
			)
		);

	}

	public function printProductsPageScripts() {
		global $wp_scripts;

		// ProfileSelector
		wp_register_script( 'wpla_profile_selector', self::$PLUGIN_URL.'js/classes/ProfileSelector.js?ver='.time(), array( 'jquery' ) );
		wp_enqueue_script( 'wpla_profile_selector' );

		wp_localize_script('wpla_profile_selector', 'wpla_ProfileSelector_i18n', array(
				'WPLA_URL' 	=> WPLA_URL
			)
		);

		// ProductMatcher
		wp_register_script( 'wpla_product_matcher', self::$PLUGIN_URL.'js/classes/ProductMatcher.js?ver='.time(), array( 'jquery' ) );
		wp_enqueue_script( 'wpla_product_matcher' );

		wp_localize_script('wpla_product_matcher', 'wpla_ProductMatcher_i18n', array(
				'WPLA_URL' 	=> WPLA_URL
			)
		);

	}
	
	// check if current page is products list page
	public function isProductsPage() {
		global $pagenow;

		if ( ( isset( $_GET['post_type'] ) ) &&
		     ( $_GET['post_type'] == 'product' ) &&
			 ( $pagenow == 'edit.php' ) ) {
			return true;
		}
		return false;
	}	


} // class WPLA_WPLister

// instantiate plugin
// global $wplister_amazon;
$wplister_amazon = WPLA_WPLister::get_instance();

