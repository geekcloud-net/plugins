<?php
/*
Plugin Name: Porto Theme - Functionality
Plugin URI:  http://themeforest.net/user/SW-THEMES
Description: Adds functionality such as Shortcodes, Post Types ans Widgets to Porto Theme
Version:     1.0.5
Author:      SW-THEMES
Author URI:  http://themeforest.net/user/SW-THEMES
License:     GPL2
*/

// don't load directly
if (!defined('ABSPATH'))
    die('-1');

class Porto_Functionality {

    private $widgets = array("block", "recent_posts", "recent_portfolios", "twitter_tweets", "contact_info", "follow_us");

    /**
     * Constructor
     *
     * @since 1.0
     *
    */
    public function __construct() {

        // Load text domain
        add_action( 'plugins_loaded', array( $this, 'loadTextDomain' ) );

        $active_plugins = get_option( 'active_plugins', array() );
        if ( is_multisite() ) {
            $active_plugins = array_merge( $active_plugins, array_flip( get_site_option( 'active_sitewide_plugins', array() ) ) );
        }

        $porto_old_plugins = ( in_array( 'porto-content-types/porto-content-types.php', $active_plugins ) || 
                    in_array( 'porto-shortcodes/porto-shortcodes.php', $active_plugins ) ||
                    in_array( 'porto-widgets/porto-widgets.php', $active_plugins ) );
        if ( $porto_old_plugins ) {
            add_action( 'admin_notices', array( $this, 'removeOldPluginsNotice' ) );
            add_action( 'network_admin_notices', array( $this, 'removeOldPluginsNotice' ) );
        }

        // define contants
        $this->defineConstants( $active_plugins );

        // add shortcodes
        if ( !in_array( 'porto-shortcodes/porto-shortcodes.php', $active_plugins ) ) {
            $this->loadShortcodes();
        }

        // add porto content types
        if ( !in_array( 'porto-content-types/porto-content-types.php', $active_plugins ) ) {
            $this->loadContentTypes();
        }

        // load porto widgets
        if ( !in_array( 'porto-widgets/porto-widgets.php', $active_plugins ) ) {
            $this->loadWidgets();
        }
    }

	// load plugin text domain
    function loadTextDomain() {
        load_plugin_textdomain( 'porto-functionality', false, dirname( plugin_basename(__FILE__) ) . '/languages' );
    }
    
    function removeOldPluginsNotice() {
        if (!current_user_can( 'manage_options')) return;
        echo '<div class="error"><p>'. __( '<b>Important:</b> Please deactivate Porto Shortcodes, Porto Content Types and Porto Widgets plugins from old Porto 3.x version.', 'porto' ) .'</p></div>';
    }

    protected function defineConstants( $active_plugins ) {

        if ( !in_array( 'porto-shortcodes/porto-shortcodes.php', $active_plugins ) ) {
            define('PORTO_SHORTCODES_URL', plugin_dir_url(__FILE__) . 'shortcodes/');
            define('PORTO_SHORTCODES_PATH', dirname(__FILE__) . '/shortcodes/shortcodes/');
            define('PORTO_SHORTCODES_WOO_PATH', dirname(__FILE__) . '/shortcodes/woo_shortcodes/');
            define('PORTO_SHORTCODES_LIB', dirname(__FILE__) . '/shortcodes/lib/');
            define('PORTO_SHORTCODES_TEMPLATES', dirname(__FILE__) . '/shortcodes/templates/');
            define('PORTO_SHORTCODES_WOO_TEMPLATES', dirname(__FILE__) . '/shortcodes/woo_templates/');
        }
        if ( !in_array( 'porto-content-types/porto-content-types.php', $active_plugins ) ) {
            define('PORTO_CONTENT_TYPES_PATH', dirname(__FILE__) . '/content-types/');
            define('PORTO_CONTENT_TYPES_LIB', dirname(__FILE__) . '/content-types/lib/');
        }
        if ( !in_array( 'porto-widgets/porto-widgets.php', $active_plugins ) ) {
            define('PORTO_WIDGETS_PATH', dirname(__FILE__) . '/widgets/');
        }
    }

    // Load Shortcodes
    function loadShortcodes() {
        require_once( PORTO_SHORTCODES_PATH . '../porto-shortcodes.php' );
    }

    // Load Content Types
    function loadContentTypes() {
        require_once( PORTO_CONTENT_TYPES_PATH . 'porto-content-types.php' );
    }

    // Load widgets
    function loadWidgets() {
        foreach ( $this->widgets as $widget ) {
            require_once( PORTO_WIDGETS_PATH . $widget . '.php' );
        }
    }
}

/**
 * Instantiate the Class
 *
 * @since     1.0
 * @global    object
 */
$porto_functionality = new Porto_Functionality();
