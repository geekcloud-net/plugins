<?php

$settings = array(

    'general' => array(

        'header'    => array(

            array(
                'name' => __( 'General Settings', 'ywdpd' ),
                'type' => 'title'
            ),

            array( 'type' => 'close' )
        ),


        'settings' => array(

            array( 'type' => 'open' ),

            array(
                'id'      => 'enabled',
                'name'    => __( 'Enable Dynamic Pricing and Discounts', 'ywdpd' ),
                'desc'    => '',
                'type'    => 'on-off',
                'std'     => 'yes'
            ),

            array(
                'id'      => 'coupon_label',
                'name'    => __( 'Coupon Label', 'ywdpd' ),
                'desc'    => __( 'Name of the coupon showed in cart if there are discounts in cart (add a single word)', 'ywdpd' ),
                'type'    => 'text',
                'std' => __( 'DISCOUNT', 'ywdpd' )
            ),

            //@since 1.1.0
            array(
                'id'      => 'show_note_on_products',
                'name'    => __( 'Display notes on products', 'ywdpd' ),
                'desc'    => __( 'Display notes on "Apply to" products and on "Apply Adjustment to" products - available for price rules', 'ywdpd' ),
                'type'    => 'on-off',
                'std'     => 'no'
            ),
            //@since 1.1.0
            array(
                'id'      => 'show_note_on_products_place',
                'name'    => __( 'Position of notes in products', 'ywdpd' ),
                'desc'    => __( 'Position of notes on "Apply to" products and on "Apply Adjustment to" products - available for price rules', 'ywdpd' ),
                'type'    => 'select',
                'options' => array(
                    'before_add_to_cart' => __( 'Before "Add to cart" button', 'ywdpd' ),
                    'after_add_to_cart'  => __( 'After "Add to cart" button', 'ywdpd' ),
                    'before_excerpt'     => __( 'Before excerpt', 'ywdpd' ),
                    'after_excerpt'      => __( 'After excerpt', 'ywdpd' ),
                    'after_meta'         => __( 'After product meta', 'ywdpd' ),

                ),
                'std'     => 'before_add_to_cart',
            ),

            array(
                'id'      => 'show_quantity_table',
                'name'    => __( 'Display Quantity Table', 'ywdpd' ),
                'desc'    => '',
                'type'    => 'on-off',
                'std'     => 'no'
            ),

            array(
                'id'      => 'show_quantity_table_schedule',
                'name'    => __( 'Display Expiring Date In Quantity Table', 'ywdpd' ),
                'desc'    => '',
                'type'    => 'on-off',
                'std'     => 'yes'
            ),

            array(
                'id'      => 'show_quantity_table_label',
                'name'    => __( 'Quantity Table Title', 'ywdpd' ),
                'desc'    => __( 'Title of the Quantity Table', 'ywdpd' ),
                'type'    => 'text',
                'std' => __( 'Discount per Quantity', 'ywdpd')
            ),

            array(
                'id'      => 'show_quantity_table_label_quantity',
                'name'    => __( 'Label for Quantity', 'ywdpd' ),
                'desc'    => '',
                'type'    => 'text',
                'std' => __( 'Quantity', 'ywdpd')
            ),

            array(
                'id'      => 'show_quantity_table_label_price',
                'name'    => __( 'Label for Price', 'ywdpd' ),
                'desc'    => '',
                'type'    => 'text',
                'std' => __( 'Price', 'ywdpd')
            ),

            array(
                'id'      => 'show_quantity_table_place',
                'name'    => __( 'Display Quantity Table Position', 'ywdpd' ),
                'desc'    => '',
                'type'    => 'select',
                'options' => array(
                    'before_add_to_cart' => __( 'Before "Add to cart" button', 'ywdpd' ),
                    'after_add_to_cart'  => __( 'After "Add to cart" button', 'ywdpd' ),
                    'before_excerpt'     => __( 'Before excerpt', 'ywdpd' ),
                    'after_excerpt'      => __( 'After excerpt', 'ywdpd' ),
                    'after_meta'         => __( 'After product meta', 'ywdpd' ),

                ),
                'std'     => 'before_add_to_cart',
            ),


	        //@since 1.1.7
	        array(
		        'id'      => 'show_minimum_price',
		        'name'    => __( 'Show minimum price for products with quantity discount enabled', 'ywdpd' ),
		        'desc'    => __( 'The discount is visible only if the discount starts from quantity equal to 1', 'ywdpd' ),
		        'type'    => 'on-off',
		        'std'     => 'no'
	        ),
			//@since 1.1.7
	        array(
		        'id'      => 'price_format',
		        'name'    => __( 'Price format', 'ywdpd' ),
		        'desc'    => __( 'You can use: %original_price%, %discounted_price%, %percentual_discount%. Note: enable the above option to see the minimum discounted amount', 'ywdpd'),
		        'type'    => 'text',
		        'std' => __( '<del>%original_price%</del> %discounted_price%', 'ywdpd')
	        ),

	        //@since 1.1.7
	        array(
		        'id'      => 'calculate_discounts_tax',
		        'name'    => __( 'Calculate cart discount starting from', 'ywdpd' ),
		        'desc'    => '',
		        'type'    => 'select',
		        'options' => array(
			        'tax_excluded' => __( 'Subtotal - tax excluded', 'ywdpd' ),
			        'tax_included' => __( 'Subtotal - tax included', 'ywdpd' ),
		        ),
		        'std'     => 'tax_excluded',
	        ),

            //@since 1.1.0
            array(
                'id'      => 'enable_shop_manager',
                'name'    => __( 'Enable Shop Manager to edit these options', 'ywdpd' ),
                'desc'    => '',
                'type'    => 'on-off',
                'std'     => 'no'
            ),

        )
    )
);

if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
	$settings['general']['settings'][] = array(
		'id'   => 'wpml_extend_to_translated_object',
		'name' => __( 'Extend the rules to translated contents', 'ywdpd' ),
		'desc' => '',
		'type' => 'on-off',
		'std'  => 'no'
	);
}

$settings['general']['settings'][] = array( 'type' => 'close' );

return apply_filters( 'yith_ywdpd_panel_settings_options', $settings );