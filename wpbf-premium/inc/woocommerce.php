<?php
/**
 * WooCommerce Integration
 *
 * @package Page Builder Framework
 */
 
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


// Styles & Scripts
add_action( 'wp_enqueue_scripts', 'wpbf_premium_woocommerce_scripts', 11 );

function wpbf_premium_woocommerce_scripts() {

	// wpbf premium style
	wp_enqueue_style( 'wpbf-premium-woocommerce', WPBF_PREMIUM_URI . 'css/wpbf-premium-woocommerce.css', '', WPBF_PREMIUM_VERSION );

}

// WooCommerce Cart Icon
add_filter( 'wp_nav_menu_items', 'wpbf_menu_cart', 10, 2 );
function wpbf_menu_cart( $items, $args, $ajax = false ) {

	// stop right here if menu item is hidden
	if( get_theme_mod( 'woocommerce_menu_item' ) == 'hide' ) return $items;

	if ( ( isset( $ajax ) && $ajax ) || $args->theme_location === 'main_menu' ) {

		// vars
		$icon = get_theme_mod( 'woocommerce_menu_item_icon' ) ? get_theme_mod( 'woocommerce_menu_item_icon' ) : 'cart';
		$css_classes = 'menu-item wpbf-menu-item-cart';
		$cart_items = WC()->cart->get_cart();
		$dropdown = get_theme_mod( 'woocommerce_menu_item_dropdown' );

		if ( is_cart() ) $css_classes .= ' current-menu-item';
		if ( $cart_items && !$dropdown ) $css_classes .= ' menu-item-has-children';

		$items .= '<li class="' . esc_attr( $css_classes ) . '">';
		$items .= '<a class="cart-contents" href="' . esc_url( wc_get_cart_url() ) . '">';
		if( get_theme_mod( 'woocommerce_menu_item_text' ) ) $items .= __( 'Cart ', 'wpbfpremium' );
		if( get_theme_mod( 'woocommerce_menu_item_amount' ) ) $items .= '<span class="total">' . wp_kses_data( WC()->cart->get_cart_total() ) . '</span> - ';
		$items .= '<span class="count"><i class="wpbff wpbff-'. esc_attr( $icon ) .'"></i>';
		if( !get_theme_mod( 'woocommerce_menu_item_count' ) ) $items .= '<span class="number">' . wp_kses_data( WC()->cart->get_cart_contents_count() ) . '</span>';
		$items .= '</span>';
		$items .= '</a>';

		if(  $cart_items && !$dropdown ) {

			$items .= '<div class="woo-sub-menu">';
			$items .= '<table class="wpbf-table">';

			$items .= '<thead>';
				$items .= '<tr>';

				$items .= '<th>'. __( 'Product/s', 'wpbfpremium' ) .'</th>';
				$items .= '<th>'. __( 'Quantity', 'wpbfpremium' ) .'</th>';

				$items .= '</tr>';
			$items .= '</thead>';

			foreach( $cart_items as $cart_item => $values ) { 

				// vars
				$_product = wc_get_product( $values['data']->get_id()); 
				$item_name = $_product->get_title();
				$quantity = $values['quantity'];
				$price = $_product->get_price();
				$image = $_product->get_image();
				$link = $_product->get_permalink();

				$items .= '<tr>';

					$items .= '<td>';
					$items .= '<a href="'. esc_url( $link ) .'">';
					$items .= $image;
					$items .= $item_name;
					$items .= '</a>';
					$items .= '</td>';

					$items .= '<td>';
					$items .= $quantity;
					$items .= '</td>';

				$items .= '</tr>';

			}

			$items .= '<tr>';
			$items .= '<th>'. __( 'Subtotal', 'wpbfpremium' ) .'</th>';
			$items .= '<td>'. WC()->cart->get_cart_subtotal() .'</td>';
			$items .= '</tr>';

			$items .= '</table>';

			$items .= '<a href="'. esc_url( wc_get_cart_url() ) .'" class="wpbf-button">'. __( 'Cart', 'wpbfpremium' ) .'</a>';
			$items .= '<a href="'. esc_url( wc_get_checkout_url() ) .'" class="wpbf-button wpbf-button-primary">'. __( 'Checkout', 'wpbfpremium' ) .'</a>';

			$items .= '</div>';

		}

		$items .= '</li>';

	}

	return $items;

}

/**
 * This function updates the Top Navigation WooCommerce cart link contents when an item is added via AJAX.
 */
add_filter( 'woocommerce_add_to_cart_fragments', 'wpbf_woocommerce_add_to_cart_fragments' );

function wpbf_woocommerce_add_to_cart_fragments( $fragments ) {
	// Add our fragment
	$fragments['li.wpbf-menu-item-cart'] = wpbf_menu_cart( '', new stdClass(), true );
	return $fragments;
}

/* Theme Mods */

// Hide Star Rating from Catalog
add_action( 'wp', 'wpbf_woocommerce_loop_remove_star_rating' );
function wpbf_woocommerce_loop_remove_star_rating() {
	if ( get_theme_mod( 'woocommerce_loop_remove_star_rating' ) ) {
		remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
	}
}

// remove sales badge from loop
add_action( 'wp', 'wpbf_woocommerce_loop_remove_sale_badge' );
function wpbf_woocommerce_loop_remove_sale_badge() {
	if ( get_theme_mod( 'woocommerce_loop_sale_position' ) && get_theme_mod( 'woocommerce_loop_sale_position' ) == 'none' ) {
		remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
	}
}


// hide woocommerce page title for archives
add_filter( 'woocommerce_show_page_title' , 'wpbf_woocommerce_loop_show_page_title' );
function wpbf_woocommerce_loop_show_page_title() {
	if ( !get_theme_mod( 'woocommerce_loop_show_page_title' ) ) {
		return false;
	} else {
		return true;
	}
}

// remove woocommerce breadcrumbs from shop pages
add_action( 'wp', 'wpbf_woocommerce_loop_show_breadcrumbs' );
function wpbf_woocommerce_loop_show_breadcrumbs() {
	if( !get_theme_mod( 'woocommerce_loop_show_breadcrumbs' ) ) {
    	remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0 );
	}
}

// Remove the result count from WooCommerce
add_action( 'wp', 'wpbf_woocommerce_loop_remove_result_count' );
function wpbf_woocommerce_loop_remove_result_count() {
	if( get_theme_mod( 'woocommerce_loop_remove_result_count' ) ) {
		remove_action( 'woocommerce_before_shop_loop' , 'woocommerce_result_count', 20 );
	}
}

// Remove the sorting dropdown from Woocommerce
add_action( 'wp', 'wpbf_woocommerce_loop_remove_ordering' );
function wpbf_woocommerce_loop_remove_ordering() {
	if( get_theme_mod( 'woocommerce_loop_remove_ordering' ) ) {
		remove_action( 'woocommerce_before_shop_loop' , 'woocommerce_catalog_ordering', 30 );
	}
}



// remove sales badge from product pages
// remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
