<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 *
 * @package    WP-Google-Translate
 * @subpackage WP-Google-Translate/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WP-Google-Translate
 * @subpackage WP-Google-Translate/admin
 */
class WP_Google_Translate_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $mts_google_translator    The ID of this plugin.
     */
    private $mts_google_translator;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $mts_google_translator       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($mts_google_translator, $version) {

        $this->mts_google_translator = $mts_google_translator;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        $screen = get_current_screen();
        if ($screen->id != 'toplevel_page_wp-google-translate') return;

        global $wp_scripts;
        wp_enqueue_style('wp-color-picker');

        wp_enqueue_style($this->mts_google_translator, plugin_dir_url(__FILE__) . 'css/wp-google-translate-admin.css', array(), $this->version, 'all');
        wp_enqueue_style( 'select2', plugin_dir_url( __FILE__ ) . 'css/select2.min.css', array(), $this->version, 'all' );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        $screen = get_current_screen();
        if ($screen->id != 'toplevel_page_wp-google-translate') return;

        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-tabs');
        wp_enqueue_script('wp-color-picker');
        
        wp_enqueue_script($this->mts_google_translator, plugin_dir_url(__FILE__) . 'js/wp-google-translate-admin.js', array('jquery'), $this->version, false);
        $options = get_option('mts_google_translate_options');
        if (empty($options) || empty($options['last_tab'])) {
            $last_tab = 0;
        } else {
            $last_tab = $options['last_tab'];
        }
        wp_localize_script( $this->mts_google_translator, 'wpgt_opts', array('tabindex' => $last_tab) );
        
        wp_enqueue_script(
                'select2', plugin_dir_url(__FILE__) . 'js/select2.full.min.js', array(
            'jquery',
                ), $this->version, false
        );
    }

}