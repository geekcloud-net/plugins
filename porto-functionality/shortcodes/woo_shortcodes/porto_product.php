<?php

// Porto Product
add_shortcode('porto_product', 'porto_shortcode_product');
add_action('vc_after_init', 'porto_load_product_shortcode');

function porto_shortcode_product($atts, $content = null) {
    ob_start();
    if ($template = porto_shortcode_woo_template('porto_product'))
        include $template;
    return ob_get_clean();
}

function porto_load_product_shortcode() {
    $animation_type = porto_vc_animation_type();
    $animation_duration = porto_vc_animation_duration();
    $animation_delay = porto_vc_animation_delay();
    $custom_class = porto_vc_custom_class();

    vc_map( array(
        'name' => "Porto " . __( 'Product', 'js_composer' ),
        'base' => 'porto_product',
        'icon' => 'porto_vc_woocommerce',
        'category' => __( 'WooCommerce', 'js_composer' ),
        'description' => __( 'Show a single product by ID or SKU', 'porto-shortcodes' ),
        'params' => array(
            array(
                'type' => 'textfield',
                'heading' => __( 'Title', 'woocommerce' ),
                'param_name' => 'title',
                'admin_label' => true
            ),
            array(
                'type' => 'dropdown',
                'heading' => __( 'View mode', 'porto-shortcodes' ),
                'param_name' => 'view',
                'value' => porto_sh_commons('product_view_mode'),
                'admin_label' => true
            ),
            array(
                'type' => 'dropdown',
                'heading' => __( 'Width', 'porto-shortcodes' ),
                'param_name' => 'column_width',
                'dependency' => Array('element' => 'view', 'value' => array( 'grid' )),
                'value' => porto_sh_commons('products_column_width')
            ),
            array(
                'type' => 'autocomplete',
                'heading' => __( 'Select identificator', 'js_composer' ),
                'param_name' => 'id',
                'description' => __( 'Input product ID or product SKU or product title to see suggestions', 'js_composer' ),
                'admin_label' => true
            ),
            array(
                'type' => 'hidden',
                // This will not show on render, but will be used when defining value for autocomplete
                'param_name' => 'sku',
            ),
            array(
                'type' => 'dropdown',
                'heading' => __( 'Add Links Position', 'porto-shortcodes' ),
                'description' => __('Select position of add to cart, add to wishlist, quickview.', 'porto-shortcodes'),
                'param_name' => 'addlinks_pos',
                'value' => porto_sh_commons('products_addlinks_pos')
            ),
            $custom_class,
            $animation_type,
            $animation_duration,
            $animation_delay
        )
    ) );

    //Filters For autocomplete param:
    //For suggestion: vc_autocomplete_[shortcode_name]_[param_name]_callback
    add_filter( 'vc_autocomplete_porto_product_id_callback', 'porto_shortcode_product_id_callback', 10, 1 ); // Get suggestion(find). Must return an array
    add_filter( 'vc_autocomplete_porto_product_id_render', 'porto_shortcode_product_id_render', 10, 1 ); // Render exact product. Must return an array (label,value)
    //For param: ID default value filter
    add_filter( 'vc_form_fields_render_field_porto_product_id_param_value', 'porto_shortcode_product_id_param_value', 10, 4 ); // Defines default value for param if not provided. Takes from other param value.

    if (!class_exists('WPBakeryShortCode_Porto_Product')) {
        class WPBakeryShortCode_Porto_Product extends WPBakeryShortCode {
        }
    }
}

function porto_shortcode_product_id_callback($query) {
    if (class_exists('Vc_Vendor_Woocommerce')) {
        $vc_vendor_wc = new Vc_Vendor_Woocommerce();
        return $vc_vendor_wc->productIdAutocompleteSuggester($query);
    }
    return '';
}

function porto_shortcode_product_id_render($query) {
    if (class_exists('Vc_Vendor_Woocommerce')) {
        $vc_vendor_wc = new Vc_Vendor_Woocommerce();
        return $vc_vendor_wc->productIdAutocompleteRender($query);
    }
    return '';
}

function porto_shortcode_product_id_param_value($current_value, $param_settings, $map_settings, $atts) {
    if (class_exists('Vc_Vendor_Woocommerce')) {
        $vc_vendor_wc = new Vc_Vendor_Woocommerce();
        return $vc_vendor_wc->productIdDefaultValue($current_value, $param_settings, $map_settings, $atts);
    }
    return '';
}