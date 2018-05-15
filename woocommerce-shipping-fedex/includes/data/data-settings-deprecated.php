<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$freight_classes     = include( 'data-freight-classes.php' );
$smartpost_hubs      = include( 'data-smartpost-hubs.php' );
$smartpost_hubs      = array( '' => __( 'N/A', 'woocommerce-shipping-fedex' ) ) + $smartpost_hubs;
$shipping_class_link = version_compare( WC_VERSION, '2.6', '>=' ) ? admin_url( 'admin.php?page=wc-settings&tab=shipping&section=classes' ) : admin_url( 'edit-tags.php?taxonomy=product_shipping_class&post_type=product' );

/**
 * Array of settings
 */
return array(
	'enabled'          => array(
		'title'           => __( 'Enable FedEx', 'woocommerce-shipping-fedex' ),
		'type'            => 'checkbox',
		'label'           => __( 'Enable this shipping method', 'woocommerce-shipping-fedex' ),
		'default'         => 'no'
	),
	'debug'      => array(
		'title'           => __( 'Debug Mode', 'woocommerce-shipping-fedex' ),
		'label'           => __( 'Enable debug mode', 'woocommerce-shipping-fedex' ),
		'type'            => 'checkbox',
		'default'         => 'no',
		'desc_tip'    => true,
		'description'     => __( 'Enable debug mode to show debugging information on the cart/checkout.', 'woocommerce-shipping-fedex' )
	),
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
    'availability'  => array(
		'title'           => __( 'Method Availability', 'woocommerce-shipping-fedex' ),
		'type'            => 'select',
		'default'         => 'all',
		'class'           => 'availability',
		'options'         => array(
			'all'            => __( 'All Countries', 'woocommerce-shipping-fedex' ),
			'specific'       => __( 'Specific Countries', 'woocommerce-shipping-fedex' ),
		),
	),
	'countries'        => array(
		'title'           => __( 'Specific Countries', 'woocommerce-shipping-fedex' ),
		'type'            => 'multiselect',
		'class'           => 'chosen_select',
		'css'             => 'width: 450px;',
		'default'         => '',
		'options'         => WC()->countries->get_allowed_countries(),
	),
    'api'              => array(
		'title'           => __( 'API Settings', 'woocommerce-shipping-fedex' ),
		'type'            => 'title',
		'description'     => __( 'Your API access details are obtained from the FedEx website. After signup, get a <a href="https://www.fedex.com/us/developer/web-services/process.html?tab=tab2">developer key here</a>. After testing you can get a <a href="https://www.fedex.com/us/developer/web-services/process.html?tab=tab4">production key here</a>.', 'woocommerce-shipping-fedex' ),
    ),
    'account_number'           => array(
		'title'           => __( 'FedEx Account Number', 'woocommerce-shipping-fedex' ),
		'type'            => 'text',
		'description'     => '',
		'default'         => ''
    ),
    'meter_number'           => array(
		'title'           => __( 'Fedex Meter Number', 'woocommerce-shipping-fedex' ),
		'type'            => 'text',
		'description'     => '',
		'default'         => ''
    ),
    'api_key'           => array(
		'title'           => __( 'Web Services Key', 'woocommerce-shipping-fedex' ),
		'type'            => 'text',
		'description'     => '',
		'default'         => '',
		'custom_attributes' => array(
			'autocomplete' => 'off'
		)
    ),
    'api_pass'           => array(
		'title'           => __( 'Web Services Password', 'woocommerce-shipping-fedex' ),
		'type'            => 'password',
		'description'     => '',
		'default'         => '',
		'custom_attributes' => array(
			'autocomplete' => 'off'
		)
    ),
    'production'      => array(
		'title'           => __( 'Production Key', 'woocommerce-shipping-fedex' ),
		'label'           => __( 'This is a production key', 'woocommerce-shipping-fedex' ),
		'type'            => 'checkbox',
		'default'         => 'no',
		'desc_tip'    => true,
		'description'     => __( 'If this is a production API key and not a developer key, check this box.', 'woocommerce-shipping-fedex' )
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
	'freight'           => array(
		'title'           => __( 'FedEx LTL Freight', 'woocommerce-shipping-fedex' ),
		'type'            => 'title',
		'description'     => __( 'If your account supports Freight, we need some additional details to get LTL rates. Note: These rates require the customers CITY so won\'t display until checkout.', 'woocommerce-shipping-fedex' ),
    ),
    'freight_enabled'      => array(
		'title'           => __( 'Enable', 'woocommerce-shipping-fedex' ),
		'label'           => __( 'Enable Freight', 'woocommerce-shipping-fedex' ),
		'type'            => 'checkbox',
		'default'         => 'no'
	),
	'freight_number' => array(
		'title'       => __( 'FedEx Freight Account Number', 'woocommerce-shipping-fedex' ),
		'type'        => 'text',
		'description' => '',
		'default'     => '',
		'placeholder' => __( 'Defaults to your main account number', 'woocommerce-shipping-fedex' )
	),
	'freight_billing_street'           => array(
		'title'           => __( 'Billing Street Address', 'woocommerce-shipping-fedex' ),
		'type'            => 'text',
		'default'         => ''
    ),
    'freight_billing_street_2'           => array(
		'title'           => __( 'Billing Street Address', 'woocommerce-shipping-fedex' ),
		'type'            => 'text',
		'default'         => ''
    ),
    'freight_billing_city'           => array(
		'title'           => __( 'Billing City', 'woocommerce-shipping-fedex' ),
		'type'            => 'text',
		'default'         => ''
    ),
    'freight_billing_state'           => array(
		'title'           => __( 'Billing State Code', 'woocommerce-shipping-fedex' ),
		'type'            => 'text',
		'default'         => '',
    ),
    'freight_billing_postcode'           => array(
		'title'           => __( 'Billing ZIP / Postcode', 'woocommerce-shipping-fedex' ),
		'type'            => 'text',
		'default'         => '',
    ),
    'freight_billing_country'           => array(
		'title'           => __( 'Billing Country Code', 'woocommerce-shipping-fedex' ),
		'type'            => 'text',
		'default'         => '',
    ),
    'freight_shipper_street'           => array(
		'title'           => __( 'Shipper Street Address', 'woocommerce-shipping-fedex' ),
		'type'            => 'text',
		'default'         => ''
    ),
    'freight_shipper_street_2'           => array(
		'title'           => __( 'Shipper Street Address 2', 'woocommerce-shipping-fedex' ),
		'type'            => 'text',
		'default'         => ''
    ),
    'freight_shipper_city'           => array(
		'title'           => __( 'Shipper City', 'woocommerce-shipping-fedex' ),
		'type'            => 'text',
		'default'         => ''
    ),
    'freight_shipper_state'           => array(
		'title'           => __( 'Shipper State Code', 'woocommerce-shipping-fedex' ),
		'type'            => 'text',
		'default'         => '',
    ),
    'freight_shipper_postcode'           => array(
		'title'           => __( 'Shipper ZIP / Postcode', 'woocommerce-shipping-fedex' ),
		'type'            => 'text',
		'default'         => '',
    ),
    'freight_shipper_country'           => array(
		'title'           => __( 'Shipper Country Code', 'woocommerce-shipping-fedex' ),
		'type'            => 'text',
		'default'         => '',
    ),
    'freight_shipper_residential'           => array(
    	'title'           => __( 'Residential', 'woocommerce-shipping-fedex' ),
		'label'           => __( 'Shipper Address is Residential?', 'woocommerce-shipping-fedex' ),
		'type'            => 'checkbox',
		'default'         => 'no'
    ),
    'freight_class'           => array(
		'title'           => __( 'Default Freight Class', 'woocommerce-shipping-fedex' ),
		'description'     => sprintf( __( 'This is the default freight class for shipments. This can be overridden using <a href="%s">shipping classes</a>', 'woocommerce-shipping-fedex' ), $shipping_class_link ),
		'type'            => 'select',
		'default'         => '50',
		'options'         => $freight_classes
    ),
);
