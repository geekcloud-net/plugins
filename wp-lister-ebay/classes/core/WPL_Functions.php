<?php
/**
 * globally available functions
 */


/**
 * get instance of WP-Lister object
 * @return WPL_WPLister
 */
function WPLE() {
    return WPL_WPLister::get_instance();
}


// custom tooltips
function wplister_tooltip( $desc ) {
	if ( defined('WPLISTER_RESELLER_VERSION') ) $desc = apply_filters( 'wplister_tooltip_text', $desc );
	if ( defined('WPLISTER_RESELLER_VERSION') && apply_filters( 'wplister_reseller_disable_tooltips', false ) ) return;
    echo '<img class="help_tip" data-tip="' . esc_attr( $desc ) . '" src="' . WPLISTER_URL . '/img/help.png" height="16" width="16" />';
}

// fetch eBay ItemID for a specific product_id / variation_id
// Note: this function does not return archived listings
function wplister_get_ebay_id_from_post_id( $post_id ) {
	$ebay_id = WPLE_ListingQueryHelper::getEbayIDFromPostID( $post_id );
	return $ebay_id;
}

// fetch fetch eBay items by column
// example: wple_get_listings_where( 'status', 'changed' );
function wple_get_listings_where( $column, $value ) {
	return WPLE_ListingQueryHelper::getWhere( $column, $value );
}


// show admin message (since 2.0.2)
function wple_show_message( $message, $type = 'info', $params = null ) {
	WPLE()->messages->add_message( $message, $type, $params );
}

// Return TRUE if the current request is done via AJAX
function wple_request_is_ajax() {
    return ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || ( defined( 'WOOCOMMERCE_CHECKOUT' ) && WOOCOMMERCE_CHECKOUT ) || ( isset($_POST['action']) && ( $_POST['action'] == 'editpost' ) ) ;
}

// Return TRUE if the current request is done via the REST API
function wple_request_is_rest() {
    return ( (defined( 'WC_API_REQUEST' ) && WC_API_REQUEST) || (defined( 'REST_REQUEST' ) && REST_REQUEST) );
}

// Shorthand way to access a product's property
function wple_get_product_meta( $product_id, $key ) {
    //return WPL_WooProductDataStore::getProperty( $product_id, $key );
    if ( is_object( $product_id ) ) {
        $product_id = is_callable( array( $product_id, 'get_id' ) ) ? $product_id->get_id() : $product_id->id;
    }

    $product = ProductWrapper::getProduct( $product_id );

    // Check for a valid product object
    if ( ! $product || ! $product->exists() ) {
        return false;
    }

    if ( $key == 'product_type' && is_callable( array( $product, 'get_type' ) ) ) {
        return call_user_func( array( $product, 'get_type' ) );
    }

    // custom WPLE postmeta
    if ( substr( $key, 0, 5 ) == 'ebay_' ) {
        return get_post_meta( $product_id, '_'. $key, true );
    }

    if ( is_callable( array( $product, 'get_'. $key ) ) ) {
        return call_user_func( array( $product, 'get_'. $key ) );
    } else {
        return $product->$key;
    }
}


function wple_get_order_meta( $order_id, $key ) {
    $order = $order_id;
    if ( ! is_object( $order ) ) {
        $order = wc_get_order( $order_id );
    }

    if ( is_callable( array( $order, 'get_'. $key ) ) ) {
        return call_user_func( array( $order, 'get_'. $key ) );
    } else {
        return $order->$key;
    }
}


//
// Template API functions
// 

function wplister_register_custom_fields( $type, $id, $default, $label, $config = array() ) {
    global $wpl_tpl_fields;
    if ( ! $wpl_tpl_fields ) $wpl_tpl_fields = array();

    if ( ! $type || ! $id ) return;

    // create field
    $field = new stdClass();
    $field->id      = $id;
    $field->type    = $type;
    $field->label   = $label;
    $field->default = $default;
    $field->value   = $default;
    $field->slug    = isset($config['slug']) ? $config['slug'] : $id;
    $field->options = isset($config['options']) ? $config['options'] : array();

    // add to template fields
    $wpl_tpl_fields[$id] = $field;

}

