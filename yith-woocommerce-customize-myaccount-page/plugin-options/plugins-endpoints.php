<?php
/**
 * GENERAL PLUGINS ENDPOINTS ARRAY
 */
if ( ! defined( 'YITH_WCMAP' ) ) {
	exit;
} // Exit if accessed directly

$plugins = [];

if( defined( 'YITH_WCWL' ) && YITH_WCWL ) {
	$plugins['my-wishlist'] = array(
		'slug'		=> 'my-wishlist',
		'active' 	=> true,
		'label'  	=> __( 'My Wishlist', 'yith-woocommerce-customize-myaccount-page' ),
		'icon'	 	=> 'heart',
		'content' 	=> '[yith_wcwl_wishlist]'
	);
}
if( defined( 'YITH_WOCC_PREMIUM' ) && YITH_WOCC_PREMIUM ) {
	$plugins['one-click'] = array(
		'slug'		=> 'one-click',
		'active' 	=> true,
		'label'  	=> __( 'One click checkout', 'yith-woocommerce-customize-myaccount-page' ),
		'icon'	 	=> 'hand-o-up',
		'content' 	=> '[yith_wocc_myaccount]'
	);
}
if( defined( 'YITH_WCSTRIPE_PREMIUM' ) && YITH_WCSTRIPE_PREMIUM ) {
	$plugins['stripe'] = array(
		'slug'		=> 'saved-cards',
		'active' 	=> true,
		'label'  	=> __( 'Saved Cards', 'yith-woocommerce-customize-myaccount-page' ),
		'icon'	 	=> 'cc-stripe',
		'content' 	=> ''
	);
}
if( defined( 'YITH_YWRAQ_PREMIUM' ) && YITH_YWRAQ_PREMIUM ) {
	$plugins['view-quote'] = array(
		'slug'		=> 'view-quote',
		'active' 	=> true,
		'label'  	=> __( 'My Quotes', 'yith-woocommerce-customize-myaccount-page' ),
		'icon'	 	=> 'pencil',
		'content' 	=> '[yith_ywraq_myaccount_quote]'
	);
}
if( defined( 'YITH_WCWTL_PREMIUM' ) && YITH_WCWTL_PREMIUM ) {
	$plugins['waiting-list'] = array(
		'slug'		=> 'my-waiting-list',
		'active' 	=> true,
		'label'  	=> __( 'My Waiting List', 'yith-woocommerce-customize-myaccount-page' ),
		'icon'	 	=> 'clock-o',
		'content' 	=> '[ywcwtl_waitlist_table]'
	);
}
if( defined( 'YITH_WCMBS_PREMIUM' ) && YITH_WCMBS_PREMIUM ) {

	$title     = __( 'Membership Plans:', 'yith-woocommerce-membership' );
	$shortcode = '[membership_history title="' . $title . '"]';
	$shortcode = apply_filters( 'yith_wcmbs_membership_history_shortcode_in_my_account', $shortcode, $title );

	$plugins['yith-membership'] = array(
		'slug'		=> 'membership-plans',
		'active' 	=> true,
		'label'  	=> __( 'Membership Plans', 'yith-woocommerce-customize-myaccount-page' ),
		'icon'	 	=> 'list',
		'content' 	=> $shortcode
	);
}
if( defined( 'YITH_YWSBS_PREMIUM' ) && YITH_YWSBS_PREMIUM ) {
	$plugins['yith-subscription'] = array(
		'slug'		=> 'my-subscription',
		'active' 	=> true,
		'label'  	=> __( 'My Subscriptions', 'yith-woocommerce-customize-myaccount-page' ),
		'icon'	 	=> 'pencil',
		'content' 	=> '[ywsbs_my_account_subscriptions]'
	);
}
if( class_exists( 'WC_Memberships' ) ) {
	$plugins['members-area'] = array(
		'slug'		=> 'members-area',
		'active' 	=> true,
		'label'  	=> __( 'My Membership', 'yith-woocommerce-customize-myaccount-page' ),
		'icon'	 	=> 'list',
		'content' 	=> ''
	);
}
if( class_exists( 'WC_Subscriptions' ) ) {
	$plugins['view-subscription'] = array(
		'slug'		=> 'view-subscription',
		'active' 	=> true,
		'label'  	=> __( 'My Subscription', 'yith-woocommerce-customize-myaccount-page' ),
		'icon'	 	=> 'pencil',
		'content' 	=> '[ywcmap_woocommerce_subscription]'
	);
}

if( defined('YITH_FUNDS_PREMIUM') && YITH_FUNDS_PREMIUM ){

	$plugins['make-a-deposit'] = array(
		'slug' => 'make-a-deposit',
		'active' => true,
		'label' => __('Make a Deposit', 'yith-woocommerce-customize-myaccount-page'),
		'icon' => 'money',
		'content' => '[yith_ywf_make_a_deposit_endpoint]'
	);

	$plugins['income-expenditure-history'] = array(
		'slug'=> 'income-expenditure-history',
		'active' => true,
		'label' => __('Income/Expenditure History', 'yith-woocommerce-customize-myaccount-page' ),
		'icon' => 'list-ol',
		'content' => '[yith_ywf_show_history pagination="yes"]'
	);
}

if( defined('YITH_YWGC_PREMIUM') && YITH_YWGC_PREMIUM ){

	$plugins['gift-cards'] = array(
		'slug' => 'gift-cards',
		'active' => true,
		'label' => __('Gift Cards', 'yith-woocommerce-customize-myaccount-page'),
		'icon' => 'gift',
		'content' => ''
	);
}

return apply_filters( 'yith_wcmap_get_plugins_endpoints_array', $plugins );