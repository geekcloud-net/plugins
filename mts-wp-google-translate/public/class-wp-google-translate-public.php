<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @since      1.0.0
 *
 * @package    WP-Google-Translate
 * @subpackage WP-Google-Translate/public
 */
class WP_Google_Translate_Public {

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
     * @param      string    $mts_google_translator       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($mts_google_translator, $version) {
        $this->mts_google_translator = $mts_google_translator;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style($this->mts_google_translator, plugin_dir_url(__FILE__) . 'css/wp-google-translate-public.css', array(), $this->version, 'all');
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script($this->mts_google_translator, plugin_dir_url(__FILE__) . 'js/wp-google-translate-public.js', array('jquery'), $this->version, false);
    }

}