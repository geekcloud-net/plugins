<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class MonsterInsights_eCommerce_WooCommerce_Integration {

	// Holds instance of eCommerce object to ensure no double instantiation of hooks
	private static $instance;

	/** @var array Queued events and impression JavaScript **/
	private $queued_js = array();

	/** @var int What number is this to output **/
	private $position = 1;

	/** @var bool Has tracked on the page for the detail **/
	private $has_tracked_detail = false;

	/** @var array Funnel steps **/
	private $funnel_steps   = array();

	/** @var @var int Will populate with order ID if a order is fully refunded */
	private $remove_from_ga = 0;

	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new MonsterInsights_eCommerce_WooCommerce_Integration();
			self::$instance->hooks();
		}
		return self::$instance;
	}

	private function hooks() {
		// If ec.js isn't already requested, add it now
		add_filter( 'monsterinsights_frontend_tracking_options_analytics_before_scripts', array( $this, 'require_ec' ), 10, 1 );

		// Setup Funnel steps for WooCommerce
		$this->funnel_steps = $this->get_funnel_steps();

		// Impression: User sees the product in a list
		add_action( 'woocommerce_before_shop_loop_item', array( $this, 'impression' ) );

		// Click: user then clicks on product listing to view more about product
		add_action( 'woocommerce_before_shop_loop_item', array( $this, 'product_click' ) );

		// View details: user views product details
		add_action( 'woocommerce_after_single_product_summary', array( $this, 'product_detail' ), 1 );

		// Add to cart
		add_action( 'woocommerce_add_to_cart', array( $this, 'add_to_cart' ), 10, 4 );

		// Update cart quantity
		add_action( 'woocommerce_after_cart_item_quantity_update', array( $this, 'change_cart_quantity' ), 10, 3 );

		// Remove from Cart
		add_action( 'woocommerce_before_cart_item_quantity_zero', array( $this, 'remove_from_cart' ) );
		add_action( 'woocommerce_remove_cart_item',               array( $this, 'remove_from_cart' ) );

		// Checkout Page
		add_action( 'woocommerce_after_checkout_form',  array( $this, 'checkout_page' ) );

		// Save CID on checkout
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'save_user_cid' ), 10, 2 );

		// Add Order to GA
		add_action( 'woocommerce_order_status_processing', array( $this, 'add_order' ), 10 );
		add_action( 'woocommerce_order_status_completed', array( $this, 'add_order' ), 10 );

		// Remove Order from GA
		add_action( 'woocommerce_order_partially_refunded', array( $this, 'remove_order' ), 10, 2 );
		add_action( 'woocommerce_order_fully_refunded', array( $this, 'refund_full_order' ), 10, 2 );

		// PayPal Redirect
		add_filter( 'woocommerce_get_return_url', array( $this, 'change_paypal_return_url' ) );

		// If we have queued JS to print, print it now
			// Impression JS
			add_filter( 'wp_footer', array( $this, 'print_impressions_js' ), 11, 1 );

			// Event JS
			add_filter( 'wp_footer', array( $this, 'print_events_js' ), 11, 1 );
	}

	public function require_ec( $options ) {
		if ( empty( $options['ec'] ) ) {
			$options['ec'] = "'require', 'ec'";
		}
		return $options;
	}

	public function impression() {
		global $product, $woocommerce_loop;

		if ( ! $product instanceof WC_Product ) {
			return;
		}

		$variation_id = version_compare( WC_VERSION, '3.0', '<' ) ? $product->id : $product->get_id();
		$product_id   = wp_get_post_parent_id( $variation_id );
		$product      = null;
		$variation    = '';

		// We need to see if the product_id in question is the post ID of a
		// variation, or of the parent post. We need the parent's ID for a variable product
		if ( $product_id === false ) {
			// If getting the parent post ID failed, this is the post id of a non-variable
			// product, or the parent post ID for a variable product.
			$product_id   = $variation_id; // Set the product ID back to the variation ID
			$product      = wc_get_product( $product_id );
		} else {
			// That product ID was the post ID for a variation.
			$product    = wc_get_product( $variation_id );
			if ( method_exists( $product, 'get_name' ) ) {
				$variation  = $product->get_name();
			} else {
				$variation  = $product->post->post_title;
			}
		}

		$categories     = (array) get_the_terms( $product_id, 'product_cat' );
		$category_names = wp_list_pluck( $categories, 'name' );
		$first_category = reset( $category_names );

		$data = array(
			'id'       => $product_id,
			'name'     => get_the_title( $product_id ),
			'list'     => $this->get_list_type( $product_id ),
			'brand'    => '', // @todo: use this for WC Product Vendors
			'category' => $first_category, // @todo: Possible  hierarchy the cats in the future
			'variant'  => $variation,
			'position' => isset( $woocommerce_loop['loop'] ) ? $woocommerce_loop['loop'] : 1,
			'price'    => $product->get_price(),
		);

		// @todo: Author + other custom dimensions scoped to products
		$this->position = $this->position + 1;

		// Unset empty values to reduce request size
		foreach ( $data as $key => $value ) {
			if ( empty( $value ) ) {
				unset( $data[ $key ] );
			}
		}
		$this->enqueue_js( 'impression', sprintf( "__gaTracker( 'ec:addImpression', %s );", wp_json_encode( $data ) ) );
	}

	public function product_click() {
		global $product;
		
		if ( ! $product instanceof WC_Product ) {
			return;
		}

		$list       = $this->get_list_type();
		$properties = array(
			'eventCategory' => 'Products',
			'eventLabel'    => htmlentities( $product->get_title(), ENT_QUOTES, 'UTF-8' ),
		);
		$id = version_compare( WC_VERSION, '3.0', '<' ) ? $product->id : $product->get_id();
		$js =
			"jQuery( '.products .post-" . esc_js( $id ) . " a' ).click( function() {
				if ( true === jQuery(this).hasClass( 'add_to_cart_button' ) ) {
					return;
				}
				" . $this->enhanced_ecommerce_add_product( $id ) . "
				" . $this->get_funnel_js( 'clicked_product', array( 'list' => $list ) ) . "
				__gaTracker( 'send', {
					hitType       : 'event',
					eventCategory : 'Products',
					eventAction   : 'Click',
					eventLabel    : '". htmlentities( $product->get_title(), ENT_QUOTES, 'UTF-8' ) . "',
				});
			});";

		$this->enqueue_js( 'event', $js );
	}

	public function product_detail() {

		// Return if this product detail is already tracked. Prevents
		// double tracking as there could be multiple buy buttons on the page.
		if ( $this->has_tracked_detail ) {
			return;
		}

		$this->has_tracked_detail = true;

		// If page reload, then return
		if ( monsterinsights_is_page_reload() ) {
			return;
		}

		$product_id = get_the_ID();

		// Output view product details EE
		$js = $this->enhanced_ecommerce_add_product( $product_id );

		// Output setAction for EC funnel
		$js .= $this->get_funnel_js( 'viewed_product' );

		// Add JS to output queue
		$this->enqueue_js( 'event', $js );

		// Send view product event
		$properties = array(
			'eventCategory'  => 'Products',
			'eventLabel'     => esc_js( get_the_title() ),
			'nonInteraction' => true,
		);

		$this->js_record_event( 'Viewed Product', $properties );
	}

	public function add_to_cart( $cart_item_key, $product_id, $quantity, $variation_id = false ) {

		$variation      = '';
		$product        = null;
		if ( ! empty( $variation_id ) ) {
			$product    = wc_get_product( $variation_id );
			if ( method_exists( $product, 'get_name' ) ) {
				$variation  = $product->get_name();
			} else {
				$variation  = $product->post->post_title;
			}
		} else {
			$product    = wc_get_product( $product_id );
		}

		$categories     = (array) get_the_terms( $product_id, 'product_cat' );
		$category_names = wp_list_pluck( $categories, 'name' );
		$first_category = reset( $category_names );

		$atts = array(
			't'     => 'event',                            							// Type of hit
			'ec'    => 'Products',                           						// Event Category
			'ea'    => 'Add to Cart',                        						// Event Action
			'el'    => htmlentities( get_the_title( $product_id ), ENT_QUOTES, 'UTF-8' ),  // Event Label (product title)
			'ev'    => (int) $quantity,                        						// Event Value (quantity)
			'cos'   => $this->get_funnel_step( 'added_to_cart' ),            		// Checkout Step: Add to cart
			'pa'    => 'add',                            							// Product Action
			'pal'   => '',                                 							// Product Action
			'pr1id' => $product_id,                          						// Product ID
			'pr1nm' => get_the_title( $product_id ),                     			// Product Name
			'pr1ca' => $first_category,                      						// Product Category
			'pr1va' => $variation,                         							// Product Variation Title
			'pr1pr' => $product->get_price(),                             			// Product Price
			'pr1qt' => $quantity,                           						// Product Quantity
		);

		if ( monsterinsights_get_option( 'userid', false ) && is_user_logged_in() ) {
			$atts['uid'] = get_current_user_id(); // UserID tracking
		}

		monsterinsights_mp_track_event_call( $atts );
	}

	public function change_cart_quantity( $cart_key, $quantity, $old_quantity ) {
		// If the cart key provided is invalid, not much we can do.
		if ( ! isset( WC()->cart->cart_contents[ $cart_key ] ) ) {
			return;
		}

		$item    = WC()->cart->cart_contents[ $cart_key ];

		$original_quantity = $old_quantity;
		$new_quantity      = $quantity;

		// If we are not really changing quantity, return
		if ( $original_quantity === $new_quantity ) {
			return;
		}

		$product_id     = $item['product_id'];
		$variation      = '';
		$product        = null;

		if ( ! empty( $item['variation_id'] ) ) {
			$product    = wc_get_product( $item['variation_id'] );
			if ( method_exists( $product, 'get_name' ) ) {
				$variation  = $product->get_name();
			} else {
				$variation  = $product->post->post_title;
			}
		} else {
			$product    = wc_get_product( $product_id );
		}

		$categories     = (array) get_the_terms( $product_id, 'product_cat' );
		$category_names = wp_list_pluck( $categories, 'name' );
		$first_category = reset( $category_names );

		if ( $original_quantity < $new_quantity ) {
			$atts = array(
				't'     => 'event',                            							// Type of hit
				'ec'    => 'Cart',                              						// Event Category
				'ea'    => 'Increased Cart Quantity',                  					// Event Action
				'el'    => htmlentities( get_the_title( $product_id ), ENT_QUOTES, 'UTF-8' ),  // Event Label (product title)
				'ev'    => absint( $new_quantity - $original_quantity ),         		// Event Value (quantity)
				'pa'    => 'add',                            							// Product Action
				'pal'   => '',                                						    // Product Action List
				'pr1id' => $product_id,                          						// Product ID
				'pr1nm' => get_the_title( $product_id ),                     			// Product Name
				'pr1ca' => $first_category,                      						// Product Category
				'pr1va' => $variation,                         							// Product Variation Title
				'pr1pr' => $product->get_price(),                             			// Product Price
				'pr1qt' => absint( $new_quantity - $original_quantity ),                // Product Quantity	
			);

			if ( monsterinsights_get_option( 'userid', false ) && is_user_logged_in() ) {
				$atts['uid'] = get_current_user_id(); // UserID tracking
			}

			monsterinsights_mp_track_event_call( $atts );
		} else {
			$atts = array(
				't'     => 'event',                            							// Type of hit
				'ec'    => 'Cart',                               						// Event Category
				'ea'    => 'Decreased Cart Quantity',                  					// Event Action
				'el'    => htmlentities( get_the_title( $product_id ), ENT_QUOTES, 'UTF-8' ),  // Event Label (product title)
				'ev'    =>  absint( $new_quantity - $original_quantity ),        		// Event Value (quantity)
				'cos'   => $this->get_funnel_step( 'added_to_cart' ),            		// Checkout Step: Add to cart
				'pa'    => 'remove',                           							// Product Action
				'pal'   => '',                               							// Product Action List:
				'pr1id' => $product_id,                          						// Product ID
				'pr1nm' => get_the_title( $product_id ),                     			// Product Name
				'pr1ca' => $first_category,                      						// Product Category
				'pr1va' => $variation,                         							// Product Variation Title
				'pr1pr' => $product->get_price(),                             			// Product Price
				'pr1qt' => absint( $new_quantity - $original_quantity ),                // Product Quantity		
			);

			if ( monsterinsights_get_option( 'userid', false ) && is_user_logged_in() ) {
				$atts['uid'] = get_current_user_id(); // UserID tracking
			}

			monsterinsights_mp_track_event_call( $atts );
		}
	}

	public function remove_from_cart( $cart_key ) {

		// If the cart key provided is invalid, not much we can do.
		if ( ! isset( WC()->cart->cart_contents[ $cart_key ] ) ) {
			return;
		}

		$item    = WC()->cart->cart_contents[ $cart_key ];

		$product_id     = $item['product_id'];
		$variation      = '';
		$product        = null;

		if ( ! empty( $item['variation_id'] ) ) {
			$product    = wc_get_product( $item['variation_id'] );
			if ( method_exists( $product, 'get_name' ) ) {
				$variation  = $product->get_name();
			} else {
				$variation  = $product->post->post_title;
			}
		} else {
			$product    = wc_get_product( $product_id );
		}

		$categories     = (array) get_the_terms( $product_id, 'product_cat' );
		$category_names = wp_list_pluck( $categories, 'name' );
		$first_category = reset( $category_names );

		$atts = array(
			't'     => 'event',                              						// Type of hit
			'ec'    => 'Products',                           						// Event Category
			'ea'    => 'Remove From Cart',                      				    // Event Action
			'el'    => htmlentities( get_the_title( $product_id ), ENT_QUOTES, 'UTF-8' ),  // Event Label (product title)
			'ev'    => (int) $item['quantity'],                        				// Event Value (quantity)
			'pa'    => 'remove',                           							// Product Action
			'pal'   => '',                              							// Product Action List
			'pr1id' => $product_id,                          						// Product ID
			'pr1nm' => get_the_title( $product_id ),                     			// Product Name
			'pr1ca' => $first_category,                      						// Product Category
			'pr1va' => $variation,                         							// Product Variation Title
			'pr1pr' => $product->get_price(),                             			// Product Price
			'pr1qt' => $item['quantity'],                           				// Product Quantity
		);

		if ( monsterinsights_get_option( 'userid', false ) && is_user_logged_in() ) {
			$atts['uid'] = get_current_user_id(); // UserID tracking
		}

		monsterinsights_mp_track_event_call( $atts );
	}

	public function checkout_page() {

		// If page refresh, don't re-track
		if ( monsterinsights_is_page_reload() ) {
			return;
		}

		$cart_contents = WC()->cart->get_cart();

		// If there's no cart contents, then return
		if ( empty( $cart_contents ) ) {
			return;
		}

		$atts = array(
			't'     => 'event',                           					  // Type of hit
			'ec'    => 'Checkout',                           				  // Event Category
			'ea'    => 'Started Checkout',                       			  // Event Action
			'el'    => 'Checkout Page',                        				  // Event Label
			'ev'    => '',                                 					  // Event Value (unused)
			'cos'   => 1,                                					  // Checkout Step
			'pa'    => $this->get_funnel_action( 'started_checkout' ),        // Product Action
			'pal'   => '',                               					  // Product Action List
			'nonInteraction' => true,                        				  // Set as non-interaction event
		);

		if ( monsterinsights_get_option( 'userid', false ) && is_user_logged_in() ) {
			$atts['uid'] = get_current_user_id(); // UserID tracking
		}

		// Declare items in cart
		$items = array();
		$i     = 1;
		foreach ( $cart_contents as $item ) {
			$product_id     = $item['product_id'];
			$variation      = '';
			$product        = null;

			if ( ! empty( $item['variation_id'] ) ) {
				$product    = wc_get_product( $item['variation_id'] );
				if ( method_exists( $product, 'get_name' ) ) {
					$variation  = $product->get_name();
				} else {
					$variation  = $product->post->post_title;
				}
			} else {
				$product    = wc_get_product( $product_id );
			}

			$categories     = (array) get_the_terms( $product_id, 'product_cat' );
			$category_names = wp_list_pluck( $categories, 'name' );
			$first_category = reset( $category_names );

			$items["pr{$i}id"]  = $product_id;      			 // Product ID
			$items["pr{$i}nm"]  = get_the_title( $product_id );  // Product Name
			$items["pr{$i}ca"]  = $first_category;    			 // Product Category
			$items["pr{$i}va"]  = $variation;        			 // Product Variation Title
			$items["pr{$i}pr"]  = $product->get_price();         // Product Price
			$items["pr{$i}qt"]  = $item['quantity'];   			 // Product Quantity
			$items["pr{$i}ps"]  = $i;            				 // Product Order
			$i++;
		}

		$atts = array_merge( $atts, $items );
		monsterinsights_mp_track_event_call( $atts );
	}

	public function save_user_cid( $payment_id ) {
		$tracked_already = get_post_meta( $payment_id, '_yoast_gau_uuid', true );

		// Don't track checkout complete if already sent
		if ( ! empty( $tracked_already ) ) {
			return;
		}

		$ga_uuid = monsterinsights_get_client_id();
		if ( $ga_uuid ) {
			$cookie = monsterinsights_get_cookie();
			update_post_meta( $payment_id, '_yoast_gau_uuid',   $ga_uuid );
			update_post_meta( $payment_id, '_monsterinsights_cookie', $cookie );
		}
	}

	public function add_order( $payment_id ) {

		$is_in_ga = get_post_meta( $payment_id, '_monsterinsights_is_in_ga', true );
		$skip_ga  = apply_filters( 'monsterinsights_ecommerce_do_transaction_skip_ga', false, $payment_id );

		// If it's already in GA or filtered to skip, then skip adding
		if ( $is_in_ga === 'yes' || $skip_ga ) {
			return;
		}

		$order = wc_get_order( $payment_id );

		$discount = '';
		if ( sizeof( $order->get_used_coupons() ) > 0 ) {
			foreach ( $order->get_used_coupons() as $code ) {
				if ( ! $code ) {
					continue;
				} else {
					$discount = $code;
					break;
				}
			}
		}

		$atts = array(
			't'     => 'event',                            					// Type of hit
			'ec'    => 'Checkout',                           				// Event Category
			'ea'    => 'Completed Checkout',                     			// Event Action
			'el'    => $payment_id,                              			// Event Label
			'ev'    => round( $order->get_total() * 100 ),              	// Event Value
			'cos'   => 2,                                					// Checkout Step
			'pa'    => $this->get_funnel_action( 'completed_purchase' ),    // Product Action
			'cid'   => monsterinsights_get_client_id( $payment_id ),        // GA Client ID
			'ti'    => $payment_id,                          				// Transaction ID
			'ta'    => null,                             					// Affiliation
			'tr'    => $order->get_total(),              					// Revenue
			'tt'    => $order->get_total_tax(), 							// Taxes
			'ts'    => method_exists( $order, 'get_shipping_total' ) ? $order->get_shipping_total() : $order->get_total_shipping(), // Shipping
			'tcc'   => $discount,                            				// Discount code
		);

		if ( monsterinsights_get_option( 'userid', false ) ) {
			$atts['uid'] = $order->user_id; // UserID tracking
		}

		// Declare items in cart
		$cart_contents  = $order->get_items();
		$items    		= array();
		$i        		= 1;
		foreach ( $cart_contents as $key => $item ) {
			$variation_id = $item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id();
			$product_id   = $variation_id > 0 ? wp_get_post_parent_id( $variation_id ) : 0;
			$product      = null;
			$variation    = '';

			// We need to see if the product_id in question is the post ID of a
			// variation, or of the parent post. We need the parent's ID for a variable product
			if ( $product_id == false ) {
				// If getting the parent post ID failed, this is the post id of a non-variable
				// product, or the parent post ID for a variable product.
				$product_id   = $variation_id; // Set the product ID back to the variation ID
				$product      = wc_get_product( $product_id );
			} else {
				// That product ID was the post ID for a variation.
				$product    = wc_get_product( $variation_id );
				if ( method_exists( $product, 'get_name' ) ) {
					$variation  = $product->get_name();
				} else {
					$variation  = $product->post->post_title;
				}
			}

			$categories     = (array) get_the_terms( $product_id, 'product_cat' );
			$category_names = wp_list_pluck( $categories, 'name' );
			$first_category = reset( $category_names );

			$items["pr{$i}id"]  = $product_id;      			    // Product ID
			$items["pr{$i}nm"]  = get_the_title( $product_id );     // Product Name
			$items["pr{$i}ca"]  = $first_category;    			    // Product Category
			$items["pr{$i}va"]  = $variation;        			    // Product Variation Title
			$items["pr{$i}pr"]  = $order->get_item_total( $item );  // Product Price
			$items["pr{$i}qt"]  = $item->get_quantity();   	        // Product Quantity
			$items["pr{$i}ps"]  = $i;            				    // Product Order
			$i++;
		}

		$atts = array_merge( $atts, $items );
		monsterinsights_mp_track_event_call( $atts );
		update_post_meta( $payment_id, '_monsterinsights_is_in_ga', 'yes' );
	}

	public function refund_full_order( $order_id, $refund_id ) {
		$this->remove_order( $order_id, $refund_id, true );
	}

	public function remove_order( $order_id, $refund_id, $is_total_refund = false ) {

		// If not in GA or skip is on, then skip
		$is_in_ga = get_post_meta( $refund_id, '_monsterinsights_refund_is_in_ga', true );
		$skip_ga  = apply_filters( 'monsterinsights_ecommerce_undo_transaction_skip_ga', false, $order_id );
		if ( $is_in_ga === 'yes' || $skip_ga ) {
			return;
		}

		$order          = wc_get_order( $order_id );
		$refund         = ! empty( $refund_id ) ? wc_get_order( $refund_id ) : $order;
		$cart_contents  = $refund->get_items();

		$atts = array(
			't'     => 'event',                                      		// Type of hit
			'ec'    => 'Orders',                                     		// Event Category
			'ea'    => 'Refunded',                                   		// Event Action
			'el'    => $order_id,                                    		// Event Label
			'ev'    => -1 * round( $refund->get_amount() * 100 ),    	    // Event Value
			'cid'   => monsterinsights_get_client_id( $order_id ),   		// GA Client ID
		);

		$ee_atts = array(
			'pa'    => 'refund',                             	     		// Product Action
			'ti'    => $order_id,                          		     		// Transaction ID
		);

		// Declare items in cart
		if ( ! $is_total_refund ) {
			$items = array();
			$i     = 1;
			foreach ( $cart_contents as $key => $item ) {
				// Refund lines with a negative total and a quantity of at least 1
				if ( $item['qty'] >= 1 && $refund->get_item_total( $item ) <= 0 ) {
					$items["pr{$i}id"]  = $key;          	       // Product ID
					$items["pr{$i}qt"]  = $item->get_quantity();   // Product Quantity
					$i++;
				}
			}
			// If we have line items, then we have an EE event
			if ( ! empty( $items ) ) {
				$atts = array_merge( $atts, $items );
				$atts = array_merge( $atts, $ee_atts );
			}
		} else {
			// If it's a full refund, then it's an EE event
			$atts = array_merge( $atts, $ee_atts );
		}

		if ( monsterinsights_get_option( 'userid', false ) ) {
			$atts['uid'] = $order->user_id; // UserID tracking
		}

		monsterinsights_mp_track_event_call( $atts );
		update_post_meta( $refund_id, '_monsterinsights_refund_is_in_ga', 'yes' );
	}

	/**
	 * Add utm_nooverride to the PayPal return URL so the original source of the transaction won't be overridden.
	 *
	 * @since 6.0.0
	 *
	 * @param array $paypal_args
	 *
	 * @link  https://support.bigcommerce.com/questions/1693/How+to+properly+track+orders+in+Google+Analytics+when+you+accept+PayPal+as+a+method+of+payment.
	 *
	 * @return array
	 */
	public function change_paypal_return_url( $paypal_url ) {
		// If already added, remove
		$paypal_url = remove_query_arg( 'utm_nooverride', $paypal_url );

		// Add UTM no override
		$paypal_url = add_query_arg( 'utm_nooverride', '1', $paypal_url );
		return $paypal_url;
	}

	public function get_list_type( $product_id = 0 ) {
		$list_type = '';
		if ( is_search() ) {
			$list_type = __( 'Search', 'ga-ecommerce' );
		} elseif ( is_product_category() ) {
			$list_type = __( 'Product category', 'ga-ecommerce' );
		} elseif ( is_product_tag() ) {
			$list_type = __( 'Product tag', 'ga-ecommerce' );
		} elseif ( is_post_type_archive( 'product' ) ) {
			$list_type = __( 'Archive', 'ga-ecommerce' );
		} elseif ( is_singular( 'product' ) && (int) get_the_ID() !== (int) $product_id ) {
			$list_type = __( 'Product Related/Upsells', 'ga-ecommerce' );
		} elseif ( is_cart() ) {
			$list_type = __( 'Cart Cross Sell', 'ga-ecommerce' );
		}

		return $list_type; // @todo: allow filtering?
	}

	public function enqueue_js( $type, $javascript ) {

		if ( ! isset( $this->queued_js[ $type ] ) ) {
			$this->queued_js[ $type ] = array();
		}

		$this->queued_js[ $type ][] = $javascript;
	}

	public function print_impressions_js( $options ) {
		// If tracking for user is disabled, so will the JS. So don't output.
		if ( ! monsterinsights_track_user() ) {
			return $options;
		}

		if ( empty( $this->queued_js[ 'impression' ] ) ) {
			return;
		}

		ob_start(); ?>
<!-- MonsterInsights Enhanced eCommerce Impression JS -->
<script type="text/javascript">
<?php 
foreach ( $this->queued_js[ 'impression' ] as $code ) {
	echo $code . "\n";
}
?>
__gaTracker('send', {
	'hitType' : 'event',
	'eventCategory' : 'Products',
	'eventAction' : 'Impression',
	'eventLabel': 'Impression',
	'nonInteraction' : true,
} );
</script>
<!-- / MonsterInsights Enhanced eCommerce Impression JS -->
<?php
		echo ob_get_clean();
	}

	public function print_events_js( $options ) {
		// If tracking for user is disabled, so will the JS. So don't output.
		if ( ! monsterinsights_track_user() ) {
			return $options;
		}

		if ( empty( $this->queued_js[ 'event' ] ) ) {
			return;
		}

		ob_start(); ?>
<!-- MonsterInsights Enhanced eCommerce Event JS -->
<script type="text/javascript">
<?php 
foreach ( $this->queued_js[ 'event' ] as $code ) {
	echo $code . "\n";
}
?>
</script>
<!-- / MonsterInsights Enhanced eCommerce Event JS -->
<?php
		echo ob_get_clean();
	}

	private function js_record_event( $event_name, $args = array() ) {
		if ( ! is_array( $args ) ) {
			return;
		}

		$args = array(
			'hitType'        => isset( $args['hitType'] )        ? $args['hitType']        : 'event',     // Required
			'eventCategory'  => isset( $args['eventCategory'] )  ? $args['eventCategory']  : 'page',      // Required
			'eventAction'    => isset( $args['eventAction'] )    ? $args['eventAction']    : $event_name, // Required
			'eventLabel'     => isset( $args['eventLabel'] )     ? $args['eventLabel']     : null,
			'eventValue'     => isset( $args['eventValue'] )     ? $args['eventValue']     : null,
			'nonInteraction' => isset( $args['nonInteraction'] ) ? $args['nonInteraction'] : false,
		);

		// Remove blank args
		unset( $args[''] );

		foreach ( $args as $key => $value ) {
			if ( empty( $value ) ) {
				unset( $args[ $key ] );
			}
		}

		$args = wp_json_encode( $args );
		$this->enqueue_js( 'event', "__gaTracker( 'send', {$args} );" );
	}

	private function enhanced_ecommerce_add_product( $product_id, $quantity = 1 ) {
		$variation_id = $product_id;
		$product_id   = wp_get_post_parent_id( $variation_id );
		$product      = null;
		$variation    = '';

		// We need to see if the product_id in question is the post ID of a
		// variation, or of the parent post. We need the parent's ID for a variable product
		if ( $product_id === false ) {
			// If getting the parent post ID failed, this is the post id of a non-variable
			// product, or the parent post ID for a variable product.
			$product_id   = $variation_id; // Set the product ID back to the variation ID
			$product      = wc_get_product( $product_id );
		} else {
			// That product ID was the post ID for a variation.
			$product    = wc_get_product( $variation_id );
			if ( method_exists( $product, 'get_name' ) ) {
				$variation  = $product->get_name();
			} else {
				$variation  = $product->post->post_title;
			}
		}

		$categories     = (array) get_the_terms( $product_id, 'product_cat' );
		$category_names = wp_list_pluck( $categories, 'name' );
		$first_category = reset( $category_names );

		$data = array(
			'id'       => $product_id,
			'name'     => get_the_title( $product_id ),
			'brand'    => '', // @todo: use this for WC Product Vendors
			'category' => $first_category, // @todo: Possible  hierarchy the cats in the future
			'variant'  => $variation,
			'quantity' => $quantity,
			'position' => isset( $woocommerce_loop['loop'] ) ? $woocommerce_loop['loop'] : '',
			'price'    => $product->get_price(),
		);

		$js = sprintf( "__gaTracker( 'ec:addProduct', %s );", wp_json_encode( $data ) );
		return $js;
	}

	private function get_funnel_steps() {
		return array(
			'clicked_product' => array(
				'action' => 'click',
				'step'   => 1,
			),
			'viewed_product' => array(
				'action' => 'detail',
				'step'   => 2,
			),
			'added_to_cart' => array(
				'action' => 'add',
				'step'   => 3,
			),
			'started_checkout' => array(
				'action' => 'checkout',
				'step'   => 4,
			),
			'completed_purchase' => array(
				'action' => 'purchase',
				'step'   => 5,
			),
		);
	}

	private function get_funnel_js( $event_key, $args = array() ) {

		if ( ! isset( $this->funnel_steps[ $event_key ] ) ) {
			return '';
		}

		$funnel_js = '';
		$funnel_action = $this->get_funnel_action( $event_key );
		$funnel_step   = $this->get_funnel_step( $event_key );

		if ( ! empty( $funnel_action ) ) {
			if ( ! empty( $funnel_step ) ) {
				$args['step'] = $funnel_step;
			}
			$action_obj = wp_json_encode( $args );
			$funnel_js = "__gaTracker( 'ec:setAction', '{$funnel_action}', {$action_obj} );";
		}
		return $funnel_js;
	}

	private function get_funnel_step( $event_key ) {
		$step = '';
		if ( isset( $this->funnel_steps[ $event_key ] ) && isset( $this->funnel_steps[ $event_key ]['step'] ) ) {
			$step = $this->funnel_steps[ $event_key ]['step'];
		}
		return $step;
	}

	private function get_funnel_action( $event_key ) {
		$action = '';
		if ( isset( $this->funnel_steps[ $event_key ] ) && isset( $this->funnel_steps[ $event_key ]['action'] ) ) {
			$action = $this->funnel_steps[ $event_key ]['action'];
		}
		return $action;
	}
}
