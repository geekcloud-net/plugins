<?php

// Porto Products
add_shortcode('porto_products', 'porto_shortcode_products');
add_action('vc_after_init', 'porto_load_products_shortcode');

function porto_shortcode_products($atts, $content = null) {
    ob_start();
    if ($template = porto_shortcode_woo_template('porto_products'))
        include $template;
    return ob_get_clean();
}

function porto_load_products_shortcode() {
    $animation_type = porto_vc_animation_type();
    $animation_duration = porto_vc_animation_duration();
    $animation_delay = porto_vc_animation_delay();
    $custom_class = porto_vc_custom_class();
    $order_by_values = porto_vc_woo_order_by();
    $order_way_values = porto_vc_woo_order_way();

    // woocommerce products
    vc_map(
        array(
            'name' => "Porto " . __( 'Products', 'js_composer' ),
            'base' => 'porto_products',
            'icon' => 'porto_vc_woocommerce',
            'category' => __( 'WooCommerce', 'js_composer' ),
            'description' => __( 'Show multiple products by ID or SKU.', 'js_composer' ),
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
                    'type' => 'autocomplete',
                    'heading' => __( 'Products', 'js_composer' ),
                    'param_name' => 'ids',
                    'settings' => array(
                        'multiple' => true,
                        'sortable' => true,
                        'unique_values' => true,
                        // In UI show results except selected. NB! You should manually check values in backend
                    ),
                    'description' => __( 'Enter List of Products', 'js_composer' ),
                    'admin_label' => true
                ),
                array(
                    'type' => 'hidden',
                    'param_name' => 'skus',
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

    //Filters For autocomplete param:
    //For suggestion: vc_autocomplete_[shortcode_name]_[param_name]_callback
    add_filter( 'vc_autocomplete_porto_products_ids_callback', 'porto_shortcode_products_ids_callback', 10, 1 ); // Get suggestion(find). Must return an array
    add_filter( 'vc_autocomplete_porto_products_ids_render', 'porto_shortcode_products_ids_render', 10, 1 ); // Render exact product. Must return an array (label,value)
    //For param: ID default value filter
    add_filter( 'vc_form_fields_render_field_porto_products_ids_param_value', 'porto_shortcode_products_ids_param_value', 10, 4 ); // Defines default value for param if not provided. Takes from other param value.

    if (!class_exists('WPBakeryShortCode_Porto_Products')) {
        class WPBakeryShortCode_Porto_Products extends WPBakeryShortCode {
        }
    }
}

function porto_shortcode_products_ids_callback($query) {
    if (class_exists('Vc_Vendor_Woocommerce')) {
        $vc_vendor_wc = new Vc_Vendor_Woocommerce();
        return $vc_vendor_wc->productIdAutocompleteSuggester($query);
    }
    return '';
}

function porto_shortcode_products_ids_render($query) {
    if (class_exists('Vc_Vendor_Woocommerce')) {
        $vc_vendor_wc = new Vc_Vendor_Woocommerce();
        return $vc_vendor_wc->productIdAutocompleteRender($query);
    }
    return '';
}

function porto_shortcode_products_ids_param_value($current_value, $param_settings, $map_settings, $atts) {
    if (class_exists('Vc_Vendor_Woocommerce')) {
        $vc_vendor_wc = new Vc_Vendor_Woocommerce();
        return $vc_vendor_wc->productsIdsDefaultValue($current_value, $param_settings, $map_settings, $atts);
    }
    return '';
}