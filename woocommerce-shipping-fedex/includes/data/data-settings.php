<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$smartpost_hubs      = include( 'data-smartpost-hubs.php' );
$smartpost_hubs      = array( '' => __( 'N/A', 'woocommerce-shipping-fedex' ) ) + $smartpost_hubs;
$shipping_class_link = version_compare( WC_VERSION, '2.6', '>=' ) ? admin_url( 'admin.php?page=wc-settings&tab=shipping&section=classes' ) : admin_url( 'edit-tags.php?taxonomy=product_shipping_class&post_type=product' );

/**
 * Array of settings
 */
return array(
	'title'            => array(
		'title'           => __( 'Method Title', 'woocommerce-shipping-fedex' ),
		'type'            => 'text',
		'description'     => __( 'This controls the title which the user sees during checkout.', 'woocommerce-shipping-fedex' ),
		'default'         => __( 'FedEx', 'woocommerce-shipping-fedex' ),
		'desc_tip'        => true
	),
	'origin'           => array(
		'title'           => __( 'Origin Postcode', 'woocommerce-shipping-fedex' ),
		'type'            => 'text',
		'description'     => __( 'Enter the postcode for the <strong>sender</strong>.', 'woocommerce-shipping-fedex' ),
		'default'         => '',
		'desc_tip'        => true
    ),
    'packing'           => array(
		'title'           => __( 'Packages', 'woocommerce-shipping-fedex' ),
		'type'            => 'title',
		'description'     => __( 'The following settings determine how items are packed before being sent to FedEx.', 'woocommerce-shipping-fedex' ),
    ),
	'packing_method'   => array(
		'title'           => __( 'Parcel Packing Method', 'woocommerce-shipping-fedex' ),
		'type'            => 'select',
		'default'         => '',
		'class'           => 'packing_method',
		'options'         => array(
			'per_item'       => __( 'Default: Pack items individually', 'woocommerce-shipping-fedex' ),
			'box_packing'    => __( 'Recommended: Pack into boxes with weights and dimensions', 'woocommerce-shipping-fedex' ),
		),
	),
	'boxes'  => array(
		'type'            => 'box_packing'
	),
    'rates'           => array(
		'title'           => __( 'Rates and Services', 'woocommerce-shipping-fedex' ),
		'type'            => 'title',
		'description'     => __( 'The following settings determine the rates you offer your customers.', 'woocommerce-shipping-fedex' ),
    ),
    'residential'      => array(
		'title'           => __( 'Residential', 'woocommerce-shipping-fedex' ),
		'label'           => __( 'Default to residential delivery', 'woocommerce-shipping-fedex' ),
		'type'            => 'checkbox',
		'default'         => 'no',
		'description'     => __( 'Enables residential flag. If you account has Address Validation enabled, this will be turned off/on automatically.', 'woocommerce-shipping-fedex' ),
		'desc_tip'    => true,
	),
    'insure_contents'      => array(
		'title'       => __( 'Insurance', 'woocommerce-shipping-fedex' ),
		'label'       => __( 'Enable Insurance', 'woocommerce-shipping-fedex' ),
		'type'        => 'checkbox',
		'default'     => 'yes',
		'desc_tip'    => true,
		'description' => __( 'Sends the package value to FedEx for insurance.', 'woocommerce-shipping-fedex' ),
	),
	'fedex_one_rate'      => array(
		'title'       => __( 'Fedex One', 'woocommerce-shipping-fedex' ),
		'label'       => sprintf( __( 'Enable %sFedex One Rates%s', 'woocommerce-shipping-fedex' ), '<a href="https://www.fedex.com/us/onerate/" target="_blank">', '</a>' ),
		'type'        => 'checkbox',
		'default'     => 'no',
		'desc_tip'    => true,
		'description' => __( 'Fedex One Rates will be offered if the items are packed into a valid Fedex One box, and the origin and destination is the US.', 'woocommerce-shipping-fedex' ),
	),
	'direct_distribution' => array(
		'title'       => __( 'International Ground Direct Distribution', 'woocommerce-shipping-fedex' ),
		'label'       => __( 'Enable direct distribution Rates.', 'woocommerce-shipping-fedex' ),
		'type'        => 'checkbox',
		'default'     => 'no',
		'desc_tip'    => true,
		'description' => __( 'Enable to get direct distribution rates if your account has this enabled.  For US to Canada or Canada to US shipments.', 'woocommerce-shipping-fedex' ),
	),
	'request_type'     => array(
		'title'           => __( 'Request Type', 'woocommerce-shipping-fedex' ),
		'type'            => 'select',
		'default'         => 'LIST',
		'class'           => '',
		'desc_tip'        => true,
		'options'         => array(
			'LIST'        => __( 'List rates', 'woocommerce-shipping-fedex' ),
			'ACCOUNT'     => __( 'Account rates', 'woocommerce-shipping-fedex' ),
		),
		'description'     => __( 'Choose whether to return List or Account (discounted) rates from the API.', 'woocommerce-shipping-fedex' )
	),
	'smartpost_hub'           => array(
		'title'           => __( 'Fedex SmartPost Hub', 'woocommerce-shipping-fedex' ),
		'type'            => 'select',
		'description'     => __( 'Only required if using SmartPost.', 'woocommerce-shipping-fedex' ),
		'desc_tip'        => true,
		'default'         => '',
		'options'         => $smartpost_hubs
    ),
	'offer_rates'   => array(
		'title'           => __( 'Offer Rates', 'woocommerce-shipping-fedex' ),
		'type'            => 'select',
		'description'     => '',
		'default'         => 'all',
		'options'         => array(
		    'all'         => __( 'Offer the customer all returned rates', 'woocommerce-shipping-fedex' ),
		    'cheapest'    => __( 'Offer the customer the cheapest rate only, anonymously', 'woocommerce-shipping-fedex' ),
		),
    ),
	'services'  => array(
		'type'            => 'services'
	),
);
