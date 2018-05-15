<?php

// Porto Widget Woo Recent Reviews
add_shortcode('porto_widget_woo_recent_reviews', 'porto_shortcode_widget_woo_recent_reviews');
add_action('vc_after_init', 'porto_load_widget_woo_recent_reviews_shortcode');

function porto_shortcode_widget_woo_recent_reviews($atts, $content = null) {
    ob_start();
    if ($template = porto_shortcode_woo_template('porto_widget_woo_recent_reviews'))
        include $template;
    return ob_get_clean();
}

function porto_load_widget_woo_recent_reviews_shortcode() {
    $animation_type = porto_vc_animation_type();
    $animation_duration = porto_vc_animation_duration();
    $animation_delay = porto_vc_animation_delay();
    $custom_class = porto_vc_custom_class();

    // woocommerce recent reviews
    vc_map(
        array(
            'name' => "Porto " . __( 'Recent Reviews', 'porto-shortcodes' ) . " " . __("Widget", 'porto-shortcodes' ),
            'base' => 'porto_widget_woo_recent_reviews',
            'icon' => 'porto_vc_woocommerce',
            'category' => __( 'WooCommerce Widgets', 'porto-shortcodes' ),
            'class' => 'wpb_vc_wp_widget',
            'description' => __( 'Display a list of your most recent reviews on your site.', 'woocommerce' ),
            'params' => array(
                array(
                    'type' => 'textfield',
                    'heading' => __( 'Title', 'woocommerce' ),
                    'param_name' => 'title',
                    'admin_label' => true
                ),
                array(
                    'type' => 'textfield',
                    'heading' => __( 'Number of products to show', 'woocommerce' ),
                    'param_name' => 'number',
                    'value' => 6
                ),
                $custom_class,
                $animation_type,
                $animation_duration,
                $animation_delay
            )
        )
    );

    if (!class_exists('WPBakeryShortCode_Porto_Widget_Woo_Recent_Reviews')) {
        class WPBakeryShortCode_Porto_Widget_Woo_Recent_Reviews extends WPBakeryShortCode {
        }
    }
}