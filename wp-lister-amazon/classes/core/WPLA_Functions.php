<?php
/**
 * globally available functions
 */


// get instance of WP-Lister object (singleton)
function WPLA() {
    return WPLA_WPLister::get_instance();
}

// custom tooltips
function wpla_tooltip( $desc ) {
	if ( defined('WPLISTER_RESELLER_VERSION') ) $desc = apply_filters( 'wpla_tooltip_text', $desc );
	if ( defined('WPLISTER_RESELLER_VERSION') && apply_filters( 'wplister_reseller_disable_tooltips', false ) ) return;
    echo '<img class="help_tip" data-tip="' . esc_attr( $desc ) . '" src="' . WPLA_URL . '/img/help.png" height="16" width="16" />';
}

// un-CamelCase string
function wpla_spacify( $str ) {
	return preg_replace('/([a-z])([A-Z])/', '$1 $2', $str);
}

// make logger available in static methods (obsolete since WPLA())
function wpla_logger_start_timer($key) {
	WPLA()->logger->startTimer($key);
}
function wpla_logger_end_timer($key) {
	WPLA()->logger->endTimer($key);
}

// show admin message (since 0.9.4.2)
function wpla_show_message( $message, $type = 'info', $params = null ) {
	WPLA()->messages->add_message( $message, $type, $params );
}

// register custom shortcode to be used in listing profiles
function wpla_register_profile_shortcode( $shortcode, $title, $callback ) {

	WPLA()->shortcodes[ $shortcode ] = array(
		'slug'       => $shortcode,
		'title'      => $title,
		'callback'   => $callback,
		'content'    => false,
	);

}

// Shorthand way to access a product's property
function wpla_get_product_meta( $product_id, $key ) {
    $product = $product_id;
    if ( !is_object( $product ) ) {
        $product = WPLA_ProductWrapper::getProduct( $product_id );
    }

    // Check for a valid product object
    if ( ! $product || ! $product->exists() ) {
        return false;
    }

    if ( $key == 'product_type' && is_callable( array( $product, 'get_type' ) ) ) {
        return $product->get_type();
    } elseif ( $key == 'stock' && is_callable( array( $product, 'get_stock_quantity')) ) {
        return $product->get_stock_quantity();
    }

    // custom WPLA postmeta
    if ( substr( $key, 0, 7 ) == 'amazon_' ) {
        return get_post_meta( $product_id, '_'. $key, true );
    }

    if ( is_callable( array( $product, 'get_'. $key ) ) ) {
        return call_user_func( array( $product, 'get_'. $key ) );
    } else {
        return $product->$key;
    }
}


function wpla_get_order_meta( $order_id, $key ) {
    $order = $order_id;
    if ( ! is_object( $order ) ) {
        $order = wc_get_order( $order_id );
    }

    if ( ! $order ) {
        return false;
    }

    if ( $key == 'order_date' && is_callable( array( $order, 'date_created' ) ) ) {
        return $order->get_date_created();
    }

    if ( is_callable( array( $order, 'get_'. $key ) ) ) {
        return call_user_func( array( $order, 'get_'. $key ) );
    } else {
        return $order->$key;
    }
}

