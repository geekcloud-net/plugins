<?php
/**
 * @package   PHP Settings
 * @date      2017-03-04
 * @version   1.0.6
 * @author    Askupa Software <hello@askupasoftware.com>
 * @link      http://products.askupasoftware.com/php-settings
 * @copyright 2017 Askupa Software
 */


class PHPSettings
{
    private $title = 'PHP Settings';
    
    function __construct() 
    {
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'wp_ajax_save_php_settings', array( $this, 'save' ) );
        add_action( 'wp_ajax_delete_ini_files', array( $this, 'delete' ) );
        add_action( 'wp_ajax_refresh_table', array( $this, 'refresh_table' ) );
        add_filter( 'plugin_action_links_' . plugin_basename( dirname( __DIR__ ).'/bootstrap.php' ), array( $this, 'add_action_links' ) );
    }

    function admin_menu() 
    {
        add_submenu_page(
            'tools.php',
            $this->title,
            $this->title,
            'manage_options',
            $this->get_slug(),
            array(
                $this,
                'render'
            )
        );
    }

    function save() 
    {   
        check_ajax_referer( 'ajax_validation', 'nonce' );
        $content = filter_input( INPUT_POST, 'ini_settings' );
        try {
            if( !current_user_can( 'administrator' ) ) throw new Exception('You must have administrative privileges to create/save files');
            INIFile::set_content( $content );
            wp_send_json_success();
        } catch (Exception $ex) {
            wp_send_json_error($ex->getMessage());
        }
        wp_die();
    }
    
    function delete() 
    {
        check_ajax_referer( 'ajax_validation', 'nonce' );
        try {
            if( !current_user_can( 'administrator' ) ) throw new Exception('You must have administrative privileges to delete files');
            INIFile::remove_files();
            wp_send_json_success();
        } catch (Exception $ex) {
            wp_send_json_error($ex->getMessage());
        }
        wp_die();
    }
    
    function refresh_table() 
    {
        check_ajax_referer( 'ajax_validation', 'nonce' );
        wp_send_json_success(PHPInfo::render('Core'));
        wp_die();
    }

    function render() 
    {
        include(dirname( __DIR__ ).'/view/options-page.phtml');
    }
    
    function render_social_buttons() 
    {
        include(dirname( __DIR__ ).'/view/social.phtml');
    }
    
    function enqueue_scripts( $hook ) 
    {
        if( 'tools_page_php_settings' === $hook )
        {
            wp_enqueue_script( 'php-settings-callback', PHP_SETTINGS_JS_URL.'script.js', array('jquery'), PHP_SETTINGS_VERSION );
            wp_enqueue_script( 'process-button', PHP_SETTINGS_JS_URL.'process-button.js', array('jquery'), PHP_SETTINGS_VERSION );
            wp_enqueue_script( 'ace-editor', 'https://cdnjs.cloudflare.com/ajax/libs/ace/1.2.3/ace.js', array('jquery'), '1.2.3' );
            wp_enqueue_script( 'listjs', 'https://cdnjs.cloudflare.com/ajax/libs/list.js/1.2.0/list.min.js', array(), '1.2.0' );
            wp_localize_script( 'php-settings-callback', 'php_settings', array('nonce' => wp_create_nonce( 'ajax_validation' ) ) );
            wp_enqueue_style( 'php-settings', PHP_SETTINGS_CSS_URL.'style.css', array(), PHP_SETTINGS_VERSION );
            add_filter('admin_footer_text', array( $this, 'footer_text' ) );
        }
    }
    
    function footer_text() 
    {
        echo 'Proudly developed by <a class="askupa-logo" href="http://askupasoftware.com/"><img height="30" src="'.PHP_SETTINGS_IMG_URL.'askupa-logo.png"/></a>';
    }
    
    function get_slug()
    {
        return strtolower(str_replace(' ', '_', $this->title));
    }
    
    function add_action_links( $links ) 
    {
        $mylinks = array(
            '<a href="' . admin_url( 'tools.php?page='.$this->get_slug() ) . '">Settings</a>',
        );
        return array_merge( $links, $mylinks );
    }
}
new PHPSettings();