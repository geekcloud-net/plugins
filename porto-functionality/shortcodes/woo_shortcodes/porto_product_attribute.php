<?php

// Porto Product Attribute
add_shortcode('porto_product_attribute', 'porto_shortcode_product_attribute');
add_action('vc_after_init', 'porto_load_product_attribute_shortcode');

function porto_shortcode_product_attribute($atts, $content = null) {
    ob_start();
    if ($template = porto_shortcode_woo_template('porto_product_attribute'))
        include $template;
    return ob_get_clean();
}

function porto_load_product_attribute_shortcode() {
    $animation_type = porto_vc_animation_type();
    $animation_duration = porto_vc_animation_duration();
    $animation_delay = porto_vc_animation_delay();
    $custom_class = porto_vc_custom_class();
    $order_by_values = porto_vc_woo_order_by();
    $order_way_values = porto_vc_woo_order_way();

    $attributes_tax = wc_get_attribute_taxonomies();
    $attributes = array();
    foreach ( $attributes_tax as $attribute ) {
        $attributes[ $attribute->attribute_label ] = $attribute->attribute_name;
    }

    // woocommerce product attribute
    vc_map(
        array(
            'name' => "Porto " . __( 'Product Attribute', 'js_composer' ),
            'base' => 'porto_product_attribute',
            'icon' => 'porto_vc_woocommerce',
            'category' => __( 'WooCommerce', 'js_composer' ),
            'description' => __( 'Show products with an attribute shortcode', 'porto-shortcodes' ),
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
                    'value' => porto_sh_commons('products_view_mode'),
                    'admin_label' => true
                ),
                array(
                    'type' => 'textfield',
                    'heading' => __( 'Per page', 'js_composer' ),
                    'value' => 12,
                    'param_name' => 'per_page',
                    'description' => __( 'The "per_page" shortcode determines how many products to show on the page', 'js_composer' ),
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => __( 'Columns', 'porto-shortcodes' ),
                    'param_name' => 'columns',
                    'dependency' => Array('element' => 'view', 'value' => array( 'products-slider', 'grid' )),
                    'std' => '4',
                    'value' => porto_sh_commons('products_columns')
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => __( 'Columns on mobile ( <= 575px )', 'porto-shortcodes' ),
                    'param_name' => 'columns_mobile',
                    'dependency' => Array('element' => 'view', 'value' => array( 'products-slider', 'grid' )),
                    'std' => '',
                    'value' => array(
                        __( 'Default', 'porto-shortcodes' ) => '',
                        '1' => '1',
                        '2' => '2',
                        '3' => '3'
                    )
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => __( 'Column Width', 'porto-shortcodes' ),
                    'param_name' => 'column_width',
                    'dependency' => Array('element' => 'view', 'value' => array( 'products-slider', 'grid' )),
                    'value' => porto_sh_commons('products_column_width')
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => __( 'Order by', 'js_composer' ),
                    'param_name' => 'orderby',
                    'value' => $order_by_values,
                    'description' => sprintf( __( 'Select how to sort retrieved products. More at %s.', 'js_composer' ), '<a href="http://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters" target="_blank">WordPress codex page</a>' )
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => __( 'Order way', 'js_composer' ),
                    'param_name' => 'order',
                    'value' => $order_way_values,
                    'description' => sprintf( __( 'Designates the ascending or descending order. More at %s.', 'js_composer' ), '<a href="http://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters" target="_blank">WordPress codex page</a>' )
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => __( 'Attribute', 'js_composer' ),
                    'param_name' => 'attribute',
                    'value' => $attributes,
                    'description' => __( 'List of product taxonomy attribute', 'js_composer' ),
                ),
                array(
                    'type' => 'checkbox',
                    'heading' => __( 'Filter', 'js_composer' ),
                    'param_name' => 'filter',
                    'value' => array( 'empty' => 'empty' ),
                    'description' => __( 'Taxonomy values', 'js_composer' ),
                    'dependency' => array(
                        'element' => 'attribute',
                        'is_empty' => true,
                        'callback' => 'vcWoocommerceProductAttributeFilterDependencyCallback',
                    ),
                ),
                array(
                    'type' => 'dropdown',
                    'heading' => __( 'Add Links Position', 'porto-shortcodes' ),
                    'description' => __('Select position of add to cart, add to wishlist, quickview.', 'porto-shortcodes'),
                    'param_name' => 'addlinks_pos',
                    'value' => porto_sh_commons('products_addlinks_pos')
                ),
                array(
                    'type' => 'checkbox',
                    'heading' => __( 'Show Slider Navigation', 'porto-shortcodes' ),
                    'param_name' => 'navigation',
                    'std' => 'yes',
                    'dependency' => Array('element' => 'view', 'value' => array( 'products-slider' )),
                    'value' => array( __( 'Yes', 'js_composer' ) => 'yes' )
                ),
                array(
                    'type' => 'checkbox',
                    'heading' => __( 'Show Slider Pagination', 'porto-shortcodes' ),
                    'param_name' => 'pagination',
                    'std' => 'no',
                    'dependency' => Array('element' => 'view', 'value' => array( 'products-slider' )),
                    'value' => array( __( 'Yes', 'js_composer' ) => 'yes' )
                ),
                $custom_class,
                $animation_type,
                $animation_duration,
                $animation_delay
            )
        )
    );

    //For param: "filter" param value
    //vc_form_fields_render_field_{shortcode_name}_{param_name}_param
    add_filter( 'vc_form_fields_render_field_porto_product_attribute_filter_param', 'porto_shortcode_product_attribute_filter_param', 10, 4 ); // Defines default value for param if not provided. Takes from other param value.

    if (!class_exists('WPBakeryShortCode_Porto_Product_Attribute')) {
        class WPBakeryShortCode_Porto_Product_Attribute extends WPBakeryShortCode {
        }
    }
}

function porto_shortcode_product_attribute_filter_param($param_settings, $current_value, $map_settings, $atts) {
    if (class_exists('Vc_Vendor_Woocommerce')) {
        $vc_vendor_wc = new Vc_Vendor_Woocommerce();
        return $vc_vendor_wc->productAttributeFilterParamValue($param_settings, $current_value, $map_settings, $atts);
    }
    return '';
}