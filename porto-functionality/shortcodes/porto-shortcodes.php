<?php
// don't load directly
if (!defined('ABSPATH'))
    die('-1');

class PortoShortcodesClass {

    private $shortcodes = array("porto_toggles", "porto_block", "porto_container", "porto_animation", "porto_carousel", "porto_carousel_item", "porto_testimonial", "porto_content_box", "porto_image_frame", "porto_preview_image", "porto_feature_box", "porto_lightbox_container", "porto_lightbox", "porto_blockquote", "porto_tooltip", "porto_popover", "porto_grid_container", "porto_grid_item", "porto_links_block", "porto_links_item", "porto_recent_posts", "porto_blog", "porto_recent_portfolios", "porto_portfolios", "porto_portfolios_category", "porto_recent_members", "porto_members", "porto_faqs", "porto_concept", "porto_map_section", "porto_history", "porto_diamonds", "porto_section", "porto_price_boxes", "porto_price_box","porto_sort_filters","porto_sort_filter", "porto_sort_container", "porto_sort_item", "porto_sticky", "porto_sticky_nav", "porto_sticky_nav_link", "porto_schedule_timeline_container", "porto_schedule_timeline_item", "porto_experience_timeline_container", "porto_experience_timeline_item", "porto_floating_menu_container", "porto_floating_menu_item", "porto_events",
    /* 4.0 shortcodes */
    "porto_icon", "porto_ultimate_heading", "porto_info_box", "porto_stat_counter", "porto_buttons", "porto_ultimate_content_box", "porto_google_map", "porto_icons", "porto_single_icon", "porto_countdown", "porto_ultimate_carousel", "porto_fancytext", "porto_modal", "porto_carousel_logo", "porto_info_list", "porto_info_list_item", "porto_interactive_banner" );

    private $woo_shortcodes = array("porto_recent_products", "porto_featured_products", "porto_sale_products", "porto_best_selling_products", "porto_top_rated_products", "porto_products", "porto_product_category", "porto_product_attribute", "porto_product", "porto_product_categories", "porto_widget_woo_products", "porto_widget_woo_top_rated_products", "porto_widget_woo_recently_viewed", "porto_widget_woo_recent_reviews", "porto_widget_woo_product_tags");

    function __construct() {

        add_action( 'init', array( $this, 'addTinyMCEButtons' ) );

        $this->addShortcodes();

        add_action( 'admin_enqueue_scripts', array( $this, 'loadAdminCssAndJs' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'loadFrontendCssAndJs' ) );
        add_filter( 'the_content', array( $this, 'formatShortcodes' ) );
        add_filter( 'widget_text', array( $this, 'formatShortcodes' ) );
    }

    // load frontend css and js
    function loadFrontendCssAndJs() {
        if ( !is_404() && !is_search() ) {
            global $post;
            if ( $post ) {
                $use_google_map = get_post_meta( $post->ID, 'porto_page_use_google_map_api', true );
                if ( '1' === $use_google_map || stripos( $post->post_content, '[porto_google_map') ) {
                    wp_enqueue_script('googleapis');
                }
            }
        }
    }

    // load css and js
    function loadAdminCssAndJs() {
        wp_register_style( 'porto_shortcodes_admin', PORTO_SHORTCODES_URL . 'assets/css/admin.css' );
        wp_enqueue_style( 'porto_shortcodes_admin' );
        wp_register_style( 'porto_shortcodes_simpleline', PORTO_SHORTCODES_URL . 'assets/css/Simple-Line-Icons/Simple-Line-Icons.css' );
        wp_enqueue_style( 'porto_shortcodes_simpleline' );

        global $pagenow;
        if ( in_array( $pagenow, array('post.php', 'post-new.php') ) ) {

            wp_register_style( 'porto_shortcodes_bootstrap_datetimepicker', PORTO_SHORTCODES_URL . 'assets/css/bootstrap-datetimepicker-admin'. (WP_DEBUG?'':'.min') .'.css' );
            wp_enqueue_style( 'porto_shortcodes_bootstrap_datetimepicker' );
        }
    }

    // Add buttons to tinyMCE
    function addTinyMCEButtons() {
        if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
            return;

        if ( get_user_option('rich_editing') == 'true' ) {
            add_filter( 'mce_external_plugins', array(&$this, 'addTinyMCEJS') );
            add_filter( 'mce_buttons', array(&$this, 'registerTinyMCEButtons') );
        }
    }

    function addTinyMCEJS($plugin_array) {
        if (get_bloginfo('version') >= 3.9)
            $plugin_array['shortcodes'] = PORTO_SHORTCODES_URL . 'assets/tinymce/shortcodes_4.js';
        else
            $plugin_array['shortcodes'] = PORTO_SHORTCODES_URL . 'assets/tinymce/shortcodes.js';

        $plugin_array['porto_shortcodes'] = PORTO_SHORTCODES_URL . 'assets/tinymce/porto_shortcodes' . (WP_DEBUG?'':'.min') . '.js';
        return $plugin_array;
    }

    function registerTinyMCEButtons($buttons) {
        array_push($buttons, "porto_shortcodes_button");
        return $buttons;
    }

    // Add shortcodes
    function addShortcodes() {

        if (function_exists('get_plugin_data')) {
            $plugin = get_plugin_data(dirname(dirname(__FILE__)) . '/porto-functionality.php');
            define('PORTO_SHORTCODES_VERSION', $plugin['Version']);
        } else {
            define('PORTO_SHORTCODES_VERSION', '');
        }

        require_once(PORTO_SHORTCODES_LIB . 'functions.php');
        foreach ($this->shortcodes as $shortcode) {
            require_once(PORTO_SHORTCODES_PATH . $shortcode . '.php');
        }
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
            foreach ($this->woo_shortcodes as $woo_shortcode) {
                require_once(PORTO_SHORTCODES_WOO_PATH . $woo_shortcode . '.php');
            }
        }
    }

    // Format shortcodes content
    function formatShortcodes($content) {
        $block = join("|", $this->shortcodes);
        // opening tag
        $content = preg_replace("/(<p>)?\[($block)(\s[^\]]+)?\](<\/p>|<br \/>)?/","[$2$3]", $content);
        // closing tag
        $content = preg_replace("/(<p>)?\[\/($block)](<\/p>|<br \/>)/","[/$2]", $content);

        $woo_block = join("|", $this->woo_shortcodes);
        // opening tag
        $content = preg_replace("/(<p>)?\[($woo_block)(\s[^\]]+)?\](<\/p>|<br \/>)?/","[$2$3]", $content);
        // closing tag
        $content = preg_replace("/(<p>)?\[\/($woo_block)](<\/p>|<br \/>)/","[/$2]", $content);

        return $content;
    }

}

// Finally initialize code
new PortoShortcodesClass();