<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class MonsterInsights_eCommerce_EDD_Integration {

	// Holds instance of eCommerce object to ensure no double instantiation of hooks
	private static $instance;

	/** @var array Queued events and impression JavaScript **/
	private $queued_js = array();

	/** @var int What number is this to output **/
	private $position = 1;

	/** @var array Funnel steps **/
	private $funnel_steps = array();

	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new MonsterInsights_eCommerce_EDD_Integration();
			self::$instance->hooks();
		}
		return self::$instance;
	}

	private function hooks() {
		// If ec.js isn't already requested, add it now
		add_filter( 'monsterinsights_frontend_tracking_options_analytics_before_scripts', array( $this, 'require_ec' ), 10, 1 );
		
		// Setup Funnel steps for EDD
		$this->funnel_steps = $this->get_funnel_steps();

		// Impression: User sees the product in a list
		add_action( 'edd_purchase_link_end',   array( $this, 'impression' ), 10, 2 );

		// Click: user then clicks on product listing to view more about product
		// Note: EDD has no standard for link clicks on lists, so we can't use this in the funnel yet.

		// View details: user views product details
		add_action( 'template_redirect',   array( $this, 'product_detail' ), 10, 2 );

		// Add to cart
		add_action( 'edd_pre_add_to_cart',    array( $this, 'add_to_cart' ), 10, 2 );

		// Update cart quantity
		add_action( 'wp_ajax_edd_update_quantity', array( $this, 'change_cart_quantity' ), 5 );
		add_action( 'wp_ajax_nopriv_edd_update_quantity', array( $this, 'change_cart_quantity' ), 5 );

		// Remove from Cart
		add_action( 'edd_pre_remove_from_cart',  array( $this, 'remove_from_cart' ), 10, 1 );

		// Checkout Page
		add_action( 'edd_before_checkout_cart',  array( $this, 'checkout_page' ) );

		// Save CID on checkout
		add_action( 'edd_insert_payment',   array( $this, 'save_user_cid' ), 10, 2 );
		
		// Add Order to GA
		add_action( 'edd_update_payment_status',  array( $this, 'add_order' ), 10, 3 );

		// Remove Order from GA
		add_action( 'edd_update_payment_status', array( $this, 'remove_order' ), 10, 3 );

		// PayPal Redirect
		add_filter( 'edd_paypal_redirect_args', array( $this, 'change_paypal_return_url' ) );

		// If we have queued JS to print, print it now
			// Impression JS
			add_filter( 'wp_footer', array( $this, 'print_impressions_js' ), 11, 1 );
			
			// Event JS
			add_filter( 'wp_footer', array( $this, 'print_events_js' ), 11, 1 );
	}

	public function edd_get_price( $download_id = 0, $price_id = 0 ) {
		$prices = edd_get_variable_prices( $download_id );
		$amount = 0.00;
		if ( $prices && is_array( $prices ) ) {
			if ( isset( $prices[ $price_id ] ) ) {
				$amount = $prices[ $price_id ]['amount'];
			} else {
				$amount = edd_get_download_price( $download_id );
			}
		}
		return apply_filters( 'edd_get_price_option_amount', edd_sanitize_amount( $amount ), $download_id, $price_id );
	}


	public function require_ec( $options ) {
		if ( empty( $options['ec'] ) ) {
			$options['ec'] = "'require', 'ec'";
		}
		return $options;
	}

	public function impression( $download_id, $args ) {

		// If this is a single download page, exit if for the same product
		if ( is_singular( 'download' ) && get_the_ID() === (int) $download_id ) {
			return;
		}

		$download       = new EDD_Download( $download_id );
		$categories     = (array) get_the_terms( $download->ID, 'download_category' );
		$category_names = wp_list_pluck( $categories, 'name' );
		$first_category = reset( $category_names );
		$variation      = ! empty( $args['price_id'] ) ? absint( $args['price_id'] ) : '';

		// @todo: Do we want to make impressions unique per product? Current thoughts are no, it should be true to count displayed
		// like ads.
		$data = array(
			'id'       => $download->ID,
			'name'     => $download->post_title,
			'list'     => $this->get_list_type( $download->ID ),
			'brand'    => '', // @todo: use this for FES
			'category' => $first_category, // @todo: Possible  hierarchy the cats in the future
			'variant'  => $variation,
			'position' => $this->position, // @todo: possibly don't send
			'price'    => $this->edd_get_price( $download->ID, $variation ),
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

	public function product_click(){
		// No standard yet in EDD core :(
	}


	public function product_detail() {
		
		//  Return if not a single download page, or if the ID !== the one displayed
		if ( ! is_singular( 'download' ) ) {
			return;
		}

		$product_id = get_the_ID();

		// If page reload, then return
		if ( monsterinsights_is_page_reload() ) {
			return;
		}

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

	public function add_to_cart( $download_id, $options ) {

		$download       = new EDD_Download( $download_id );
		$price_options  = $download->get_prices();
		$price_id       = isset( $options['price_id'] ) ? ( is_array( $options['price_id'] ) ? $options['price_id'][0] : $options['price_id'] ) : false;
		$variation      = isset( $price_id ) && isset( $price_options[ $price_id ] ) ? $price_options[ $price_id ]['name'] : '';
		$price          = isset( $price_id ) && isset( $price_options[ $price_id ] ) ? $price_options[ $price_id ]['amount'] : '';
		$price          = empty( $price ) ? $download->get_price() : $price;
		$quantity       = isset( $options['quantity'] ) ? $options['quantity'] : 1;
		$categories     = (array) get_the_terms( $download->ID, 'download_category' );
		$category_names = wp_list_pluck( $categories, 'name' );
		$first_category = reset( $category_names );

		$atts = array(
			't'     => 'event',													   // Type of hit
			'ec'    => 'Products',												   // Event Category
			'ea'    => 'Add to Cart',											   // Event Action
			'el'    => htmlentities( $download->post_title, ENT_QUOTES, 'UTF-8' ), // Event Label (product title)
			'ev'    => (int) $quantity,											   // Event Value (quantity)
			'cos'   => $this->get_funnel_step( 'added_to_cart' ), 				   // Checkout Step: Add to cart
			'pa'    => 'add',													   // Product Action			   
			'pal'   => '',													       // Product Action
			'pr1id' => $download->ID, 											   // Product ID
			'pr1nm' => $download->post_title, 									   // Product Name
			'pr1ca' => $first_category, 										   // Product Category
			'pr1va' => $variation, 												   // Product Variation Title
			'pr1pr' => $price, 													   // Product Price
			'pr1qt' => $quantity, 												   // Product Quantity
		);

		if ( monsterinsights_get_option( 'userid', false ) && is_user_logged_in() ) {
			$atts['uid'] = get_current_user_id(); // UserID tracking
		}

		monsterinsights_mp_track_event_call( $atts );
	}

	public function change_cart_quantity() {
		// If we don't have the quantity & download id of change, then return
		if ( empty( $_POST['quantity'] ) || empty( $_POST['download_id'] ) ) {
			return;
		}

		// Get download ID
		$download          = new EDD_Download( $_POST['download_id'] );

		// Let's see if this is for a variation
		$options           = isset( $_POST['options'] )    ? maybe_unserialize( stripslashes( $_POST['options'] ) ) : array();
		$price_id          = isset( $options['price_id'] ) ? $options['price_id'] : false;

		$original_quantity = edd_get_cart_item_quantity( $download->ID, $options );
		$new_quantity      = absint( $_POST['quantity'] );

		// If we are not really changing quantity, return
		if ( $original_quantity === $new_quantity ) {
			return;
		}


		$price_options  = $download->get_prices();
		$variation      = isset( $price_id ) && isset( $price_options[ $price_id ] ) ? $price_options[ $price_id ]['name'] : '';
		$price          = isset( $price_id ) && isset( $price_options[ $price_id ] ) ? $price_options[ $price_id ]['amount'] : '';
		$price          = empty( $price ) ? $download->get_price() : $price;
		$quantity       = isset( $options['quantity'] ) ? $options['quantity'] : 1;
		$categories     = (array) get_the_terms( $download->ID, 'download_category' );
		$category_names = wp_list_pluck( $categories, 'name' );
		$first_category = reset( $category_names );

		if ( $original_quantity < $new_quantity ) {
			$atts = array(
				't'     => 'event',													   // Type of hit
				'ec'    => 'Cart',												       // Event Category
				'ea'    => 'Increased Cart Quantity',								   // Event Action
				'el'    => htmlentities( $download->post_title, ENT_QUOTES, 'UTF-8' ), // Event Label (product title)
				'ev'    => absint( $new_quantity - $original_quantity ),			   // Event Value (quantity)
				'pa'    => 'add',													   // Product Action			   
				'pal'   => '',													       // Product Action List
				'pr1id' => $download->ID, 											   // Product ID
				'pr1nm' => $download->post_title, 									   // Product Name
				'pr1ca' => $first_category, 										   // Product Category
				'pr1va' => $variation, 												   // Product Variation Title
				'pr1pr' => $price, 													   // Product Price
				'pr1qt' => absint( $new_quantity - $original_quantity ), 			   // Product Quantity
			);

			if ( monsterinsights_get_option( 'userid', false ) && is_user_logged_in() ) {
				$atts['uid'] = get_current_user_id(); // UserID tracking
			}
			
			monsterinsights_mp_track_event_call( $atts );
		} else {
			$atts = array(
				't'     => 'event',													   // Type of hit
				'ec'    => 'Cart',												       // Event Category
				'ea'    => 'Decreased Cart Quantity',								   // Event Action
				'el'    => htmlentities( $download->post_title, ENT_QUOTES, 'UTF-8' ), // Event Label (product title)
				'ev'    => absint( $new_quantity - $original_quantity ),			   // Event Value (quantity)
				'cos'   => $this->get_funnel_step( 'added_to_cart' ), 				   // Checkout Step: Add to cart
				'pa'    => 'remove',												   // Product Action			   
				'pal'   => '',   													   // Product Action List:
				'pr1id' => $download->ID, 											   // Product ID
				'pr1nm' => $download->post_title, 									   // Product Name
				'pr1ca' => $first_category, 										   // Product Category
				'pr1va' => $variation, 												   // Product Variation Title
				'pr1pr' => $price, 													   // Product Price
				'pr1qt' => absint( $new_quantity - $original_quantity ),			   // Product Quantity
			);

			if ( monsterinsights_get_option( 'userid', false ) && is_user_logged_in() ) {
				$atts['uid'] = get_current_user_id(); // UserID tracking
			}

			monsterinsights_mp_track_event_call( $atts );
		}
	}

	public function remove_from_cart( $cart_key ) {

		$cart_contents = edd_get_cart_contents();

		// If the cart key provided is invalid, not much we can do.
		if ( ! isset( $cart_contents[ $cart_key ] ) ) {
			return;
		}

		$download       = new EDD_Download( $cart_contents[ $cart_key ]['id'] );
		$price_options  = $download->get_prices();
		$price_id       = isset( $cart_contents[ $cart_key ]['options']['price_id'] ) ? $cart_contents[ $cart_key ]['options']['price_id'] : null;
		$variation      = isset( $price_id ) && isset( $price_options[ $price_id ] ) ? $price_options[ $price_id ]['name'] : '';
		$price          = isset( $price_id ) && isset( $price_options[ $price_id ] ) ? $price_options[ $price_id ]['amount'] : '';
		$price          = empty( $price ) ? $download->get_price() : $price;
		$quantity       = isset( $cart_contents[ $cart_key ]['quantity'] ) ? $cart_contents[ $cart_key ]['quantity'] : 1;
		$categories     = (array) get_the_terms( $download->ID, 'download_category' );
		$category_names = wp_list_pluck( $categories, 'name' );
		$first_category = reset( $category_names );

		$atts = array(
			't'     => 'event',													   // Type of hit
			'ec'    => 'Products',												   // Event Category
			'ea'    => 'Remove From Cart',										   // Event Action
			'el'    => htmlentities( $download->post_title, ENT_QUOTES, 'UTF-8' ), // Event Label (product title)
			'ev'    => (int) $quantity,											   // Event Value (quantity)
			'pa'    => 'remove',												   // Product Action			   
			'pal'   => '',   													   // Product Action List
			'pr1id' => $download->ID, 											   // Product ID
			'pr1nm' => $download->post_title, 									   // Product Name
			'pr1ca' => $first_category, 										   // Product Category
			'pr1va' => $variation, 												   // Product Variation Title
			'pr1pr' => $price, 													   // Product Price
			'pr1qt' => $quantity, 												   // Product Quantity
		);

		if ( monsterinsights_get_option( 'userid', false ) && is_user_logged_in() ) {
			$atts['uid'] = get_current_user_id(); // UserID tracking
		}

		monsterinsights_mp_track_event_call( $atts );
	}

	public function checkout_page() {

		// If not EDD checkout page, return
		if ( ! edd_is_checkout() ) {
			return;
		}

		// If page refresh, don't re-track
		if ( monsterinsights_is_page_reload() ) {
			return;
		}

		$cart_contents = edd_get_cart_content_details();

		// If there's no cart contents, then return
		if ( empty( $cart_contents ) ) {
			return;
		}

		$atts = array(
			't'     => 'event',													   // Type of hit
			'ec'    => 'Checkout',												   // Event Category
			'ea'    => 'Started Checkout',										   // Event Action
			'el'    => 'Checkout Page', 										   // Event Label
			'ev'    => '',											   			   // Event Value (unused)
			'cos'   => '1', 			   										   // Checkout Step
			'pa'    => $this->get_funnel_action( 'started_checkout' ),			   // Product Action			   
			'pal'   => '',												 		   // Product Action List
		//	'col'   => edd_get_gateway_admin_label( edd_get_chosen_gateway() ),    // Current Gateway. Will always use default gateway?
			'nonInteraction' => true,											   // Set as non-interaction event
		);

		if ( monsterinsights_get_option( 'userid', false ) && is_user_logged_in() ) {
			$atts['uid'] = get_current_user_id(); // UserID tracking
		}

		// Declare items in cart
		$items        = array();
		$i 			  = 1;
		foreach ( $cart_contents as $key => $item ) {
			$download       = new EDD_Download( $item['id'] );
			$price_options  = $download->get_prices();
			$price_id       = isset( $item['item_number']['options']['price_id'] ) ? $item['item_number']['options']['price_id'] : null;
			$variation      = ! empty( $price_id ) && isset( $price_options[ $price_id ] ) ? $price_options[ $price_id ]['name'] : '';
			$price          = isset( $price_id ) && isset( $price_options[ $price_id ] ) ? $price_options[ $price_id ]['amount'] : '';
			$price          = empty( $price ) ? $download->get_price() : $price;
			$categories     = (array) get_the_terms( $item['id'], 'download_category' );
			$category_names = wp_list_pluck( $categories, 'name' );
			$first_category = reset( $category_names );

			$items["pr{$i}id"]  = $item['id']; 			 // Product ID
			$items["pr{$i}nm"]  = $download->post_title; // Product Name
			$items["pr{$i}ca"]  = $first_category; 		 // Product Category
			$items["pr{$i}va"]  = $variation; 			 // Product Variation Title
			$items["pr{$i}pr"]  = $price; 				 // Product Price
			$items["pr{$i}qt"]  = $item['quantity']; 	 // Product Quantity
			$items["pr{$i}ps"]  = $i; 					 // Product Order
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

	public function add_order( $payment_id, $new_status, $old_status ) {

		// If not a completed or published payment, then return
		if ( 'publish' !== $new_status && 'edd_subscription' !== $new_status && 'complete' !== $new_status ) {
			return;
		}

		$is_in_ga = get_post_meta( $payment_id, '_monsterinsights_is_in_ga', true );
		$skip_ga  = apply_filters( 'monsterinsights_ecommerce_do_transaction_skip_ga', false, $payment_id );

		// If it's already in GA or filtered to skip, then skip adding
		if ( $is_in_ga === 'yes' || $skip_ga ) {
			return;
		}

		$payment_meta  = edd_get_payment_meta( $payment_id );

		// If there's no cart contents, then return
		if ( empty( $payment_meta['cart_details'] ) ) {
			return;
		}

		$cart_contents = $payment_meta['cart_details'];
		$discount      = ! empty( $payment_meta['user_info']['discount'] ) ? $payment_meta['user_info']['discount'] : 'none';
		$discount      = $discount != 'none' ? explode( ',', $discount ) : null;
		$discount      = is_array( $discount ) ? reset( $discount ) : $discount;

		$atts = array(
			't'     => 'event',													   // Type of hit
			'ec'    => 'Checkout',												   // Event Category
			'ea'    => 'Completed Checkout',									   // Event Action
			'el'    => $payment_id, 									           // Event Label
			'ev'    => round( edd_get_payment_amount( $payment_id ) * 100 ),       // Event Value
			'cos'   => '2', 			   										   // Checkout Step
			'pa'    => $this->get_funnel_action( 'completed_purchase' ),		   // Product Action
			'cid'   => monsterinsights_get_client_id( $payment_id ),			   // GA Client ID
			'ti'    => $payment_id, 											   // Transaction ID
			'ta'    => null, 													   // Affiliation
			'tr'    => edd_get_payment_amount( $payment_id ), 					   // Revenue
			'tt'    => edd_use_taxes() ? edd_get_payment_tax( $payment_id ) : null,// Taxes
			'ts'    => null, 													   // Shipping
			'tcc'   => $discount, 												   // Discount code
			'nonInteraction' => true,											   // Set as non-interaction event
		);

		if ( monsterinsights_get_option( 'userid', false ) ) {
			$atts['uid'] = edd_get_payment_user_id( $payment_id ); // UserID tracking
		}

		// Declare items in cart
		$items        = array();
		$i 			  = 1;
		foreach ( $cart_contents as $key => $item ) {
			$download       = new EDD_Download( $item['id'] );
			$price_options  = $download->get_prices();
			$price_id       = isset( $item['item_number']['options']['price_id'] ) ? $item['item_number']['options']['price_id'] : null;
			$variation      = ! empty( $price_id ) && isset( $price_options[ $price_id ] ) ? $price_options[ $price_id ]['name'] : '';
			$price          = isset( $price_id ) && isset( $price_options[ $price_id ] ) ? $price_options[ $price_id ]['amount'] : '';
			$price          = empty( $price ) ? $download->get_price() : $price;
			$categories     = (array) get_the_terms( $item['id'], 'download_category' );
			$category_names = wp_list_pluck( $categories, 'name' );
			$first_category = reset( $category_names );

			$items["pr{$i}id"]  = $item['id']; 			 // Product ID
			$items["pr{$i}nm"]  = $download->post_title; // Product Name
			$items["pr{$i}ca"]  = $first_category; 		 // Product Category
			$items["pr{$i}va"]  = $variation; 			 // Product Variation Title
			$items["pr{$i}pr"]  = $price; 				 // Product Price
			$items["pr{$i}qt"]  = $item['quantity']; 	 // Product Quantity
			$items["pr{$i}ps"]  = $i; 					 // Product Order
			$i++;
		}

		$atts = array_merge( $atts, $items );
		monsterinsights_mp_track_event_call( $atts );
		update_post_meta( $payment_id, '_monsterinsights_is_in_ga', 'yes' );
	}

	public function remove_order( $payment_id, $new_status, $old_status ) {

		// If not a refunded or revoked order skip
		if ( $new_status !== 'refunded' ) {
			return;
		}

		// If not in GA or skip is on, then skip
		$is_in_ga = get_post_meta( $payment_id, '_monsterinsights_is_in_ga', true );
		$skip_ga  = apply_filters( 'monsterinsights_ecommerce_undo_transaction_skip_ga', false, $payment_id );
		if ( $is_in_ga !== 'yes' || $skip_ga ) {
			return;
		}

		$payment_meta  = edd_get_payment_meta( $payment_id );

		// If there's no cart contents, then return
		if ( empty( $payment_meta['cart_details'] ) ) {
			return;
		}

		$cart_contents = $payment_meta['cart_details'];
		$atts = array(
			't'     => 'event',													   // Type of hit
			'ec'    => 'Orders',										   		   // Event Category
			'ea'    => 'Refunded', 												   // Event Action
			'el'    => $payment_id, 									           // Event Label
			'ev'    => -1 * round( edd_get_payment_amount( $payment_id ) * 100 ),  // Event Value
			'pa'    => 'refund',		  										   // Product Action			   
			'cid'   => monsterinsights_get_client_id( $payment_id ),			   // GA Client ID
			'ti'    => $payment_id, 											   // Transaction ID
			'nonInteraction' => true,											   // Set as non-interaction event
		);

		if ( monsterinsights_get_option( 'userid', false ) ) {
			$atts['uid'] = edd_get_payment_user_id( $payment_id ); // UserID tracking
		}

		// Declare items in cart
		$items        = array();
		$i 			  = 1;
		foreach ( $cart_contents as $key => $item ) {
			$items["pr{$i}id"]  = $item['id']; 			 // Product ID
			$items["pr{$i}qt"]  = $item['quantity']; 	 // Product Quantity
			$i++;
		}

		$atts = array_merge( $atts, $items );
		monsterinsights_mp_track_event_call( $atts );
		delete_post_meta( $payment_id, '_monsterinsights_is_in_ga' );
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
	public function change_paypal_return_url( $paypal_args ) {
		$paypal_args['return'] = add_query_arg( array( 'utm_nooverride' => '1' ), $paypal_args['return'] );
		return $paypal_args;
	}

	public function get_list_type( $download_id = 0 ) {
		$list_type = '';
		if ( is_search() ) {
			$list_type = __( 'Search', 'ga-ecommerce' );
		} elseif ( is_tax( 'download_category' ) ) {
			$list_type = __( 'Product category', 'ga-ecommerce' );
		} elseif ( is_tax( 'download_tag' ) ) {
			$list_type = __( 'Product tag', 'ga-ecommerce' );
		} elseif ( is_post_type_archive( 'download' ) ) {
			$list_type = __( 'Archive', 'ga-ecommerce' );
		} elseif ( is_singular( 'download' ) && (int) get_the_ID() !== (int) $download_id ) {
			$list_type = __( 'Recommended Products', 'ga-ecommerce' );
		} elseif ( edd_is_checkout() ) {
			$list_type = __( 'Recommended Products', 'ga-ecommerce' );
		} else {
			// @todo: we could use the current url for the list name
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

		foreach( $args as $key => $value ) {
			if ( empty( $value ) ) {
				unset( $args[ $key ] );
			}
		}

		$args = wp_json_encode( $args );
		$this->enqueue_js( 'event', "__gaTracker( 'send', {$args} );" );
	}

	private function enhanced_ecommerce_add_product( $product_id, $price_id = false, $quantity = 1 ) {
		$download       = new EDD_Download( $product_id );
		$categories     = (array) get_the_terms( $download->ID, 'download_category' );
		$category_names = wp_list_pluck( $categories, 'name' );
		$first_category = reset( $category_names );
		$price_options  = $download->get_prices();
		$price_id       = ( $price_id === false || $price_id === null ) ? $price_id : '';
		$variation      = isset( $price_options[ $price_id ] ) ? $price_options[ $price_id ]['name'] : '';

		$data = array(
			'id'       => $download->ID,
			'name'     => $download->post_title,
			'quantity' => $quantity,
			'brand'    => '', // @todo: use this for FES
			'category' => $first_category, // @todo: Possible  hierarchy the cats in the future
			'variant'  => $variation,
			'position' => '',
			'price'    => $this->edd_get_price( $download->ID, $variation ),
		);

		$js = sprintf( "__gaTracker( 'ec:addProduct', %s );", wp_json_encode( $data ) );
		return $js;
	}

	private function get_funnel_steps() {
		return array(
			// @todo: Unlike WooCommerce, EDD currently has no standard for click on lists
			// so we can't use it in the funnel yet :(
			//'clicked_product' => array(
			//	'action' => 'click',
			//	'step'   => 1,
			//),
			'viewed_product' => array(
				'action' => 'detail',
				'step'   => 1,
			),
			'added_to_cart' => array(
				'action' => 'add',
				'step'   => 2,
			),
			'started_checkout' => array(
				'action' => 'checkout',
				'step'   => 3,
			),
			'completed_purchase' => array(
				'action' => 'purchase',
				'step'   => 4,
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
