<?php

// Porto Product Categories
add_shortcode('porto_product_categories', 'porto_shortcode_product_categories');
add_action('vc_after_init', 'porto_load_product_categories_shortcode');

function porto_shortcode_product_categories($atts, $content = null) {
    ob_start();
    if ($template = porto_shortcode_woo_template('porto_product_categories'))
        include $template;
    return ob_get_clean();
}

function porto_load_product_categories_shortcode() {
    $animation_type = porto_vc_animation_type();
    $animation_duration = porto_vc_animation_duration();
    $animation_delay = porto_vc_animation_delay();
    $custom_class = porto_vc_custom_class();
    $order_by_values = porto_vc_woo_order_by();
    $order_way_values = porto_vc_woo_order_way();

    // woocommerce product categories
    vc_map(
        array(
            'name' => "Porto " . __( 'Product Categories', 'js_composer' ),
            'base' => 'porto_product_categories',
            'icon' => 'porto_vc_woocommerce',
            'category' => __( 'WooCommerce', 'js_composer' ),
            'description' => __( 'Display product categories loop', 'porto-shortcodes' ),
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
                    'value' => array(
                        __( 'Grid', 'porto-shortcodes' )=> 'grid',
                        __( 'Slider', 'porto-shortcodes' )  => 'products-slider',
                    ),
                    'admin_label' => true
                ),
                array(
                    'type' => 'textfield',
                    'heading' => __( 'Number', 'js_composer' ),
                    'param_name' => 'number',
                    'description' => __( 'The `number` field is used to display the number of products.', 'js_composer' ),
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
                    'type' => 'textfield',
                    'heading' => __( 'Number', 'js_composer' ),
                    'param_name' => 'hide_empty',
                    'description' => __( 'Hide empty', 'js_composer' ),
                ),
                array(
                    'type' => 'textfield',
                    'heading' => __( 'Parent Category ID', 'porto-shortcodes' ),
                    'param_name' => 'parent'
                ),
                array(
                    'type' => 'autocomplete',
                    'heading' => __( 'Categories', 'js_composer' ),
                    'param_name' => 'ids',
                    'settings' => array(
                        'multiple' => true,
                        'sortable' => true,
                    ),
                    'description' => __( 'List of product categories', 'js_composer' ),
                ),
                array(
                    'type' => 'checkbox',
                    'heading' => __( 'Hide products count', 'porto-shortcodes' ),
                    'param_name' => 'hide_count',
                    'value' => array( __( 'Yes', 'js_composer' ) => 'yes' )
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
    add_filter( 'vc_autocomplete_porto_product_categories_ids_callback', 'porto_shortcode_product_categories_ids_callback', 10, 1 ); // Get suggestion(find). Must return an array
    add_filter( 'vc_autocomplete_porto_product_categories_ids_render', 'porto_shortcode_product_categories_ids_render', 10, 1 ); // Render exact category by id. Must return an array (label,value)

    if (!class_exists('WPBakeryShortCode_Porto_Product_Categories')) {
        class WPBakeryShortCode_Porto_Product_Categories extends WPBakeryShortCode {
        }
    }
}

function porto_shortcode_product_categories_ids_callback($query) {
    if (class_exists('Vc_Vendor_Woocommerce')) {
        $vc_vendor_wc = new Vc_Vendor_Woocommerce();
        return $vc_vendor_wc->productCategoryCategoryAutocompleteSuggester($query);
    }
    return '';
}

function porto_shortcode_product_categories_ids_render($query) {
    if (class_exists('Vc_Vendor_Woocommerce')) {
        $vc_vendor_wc = new Vc_Vendor_Woocommerce();
        return $vc_vendor_wc->productCategoryCategoryRenderByIdExact($query);
    }
    return '';
}