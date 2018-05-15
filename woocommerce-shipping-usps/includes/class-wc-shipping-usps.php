<?php
/**
 * WC_Shipping_USPS class.
 *
 * @package WC_Shipping_USPS
 */

/**
 * Shipping method main class.
 *
 * @version 4.4.0
 * @extends WC_Shipping_Method
 */
class WC_Shipping_USPS extends WC_Shipping_Method {

	/**
	 * API endpoint.
	 *
	 * @var string
	 */
	private $endpoint = 'http://production.shippingapis.com/shippingapi.dll';

	/**
	 * Default user ID.
	 *
	 * @var string
	 */
	private $default_user_id = '150WOOTH2143';

	/**
	 * Countries considered as domestic.
	 *
	 * @var array
	 */
	private $domestic = array( 'US', 'PR', 'VI', 'MH', 'FM', 'GU', 'MP', 'AS', 'UM' );

	/**
	 * Found rates.
	 *
	 * @var array
	 */
	private $found_rates;

	/**
	 * Flat rate boxes.
	 *
	 * @var array
	 */
	private $flat_rate_boxes;

	/**
	 * Flat rate pricing.
	 *
	 * @var array
	 */
	private $flat_rate_pricing;

	/**
	 * Services.
	 *
	 * @var array
	 */
	private $services;

	/**
	 * Origin postcode.
	 *
	 * @var string
	 */
	private $origin;

	/**
	 * Whether debug is enabled or not.
	 *
	 * @var bool
	 */
	private $debug;

	/**
	 * Whether flat rate boxes is enabled or not.
	 *
	 * Valid values are "yes" and "no".
	 *
	 * @var string
	 */
	private $enable_flat_rate_boxes;

	/**
	 * Shipping classes whose restricted to Media Mail.
	 *
	 * @var array
	 */
	private $mediamail_restriction;

	/**
	 * USPS user ID.
	 *
	 * @var string
	 */
	private $user_id;

	/**
	 * Packing method.
	 *
	 * Possible values are "per_item", "box_packing", and "weight_based".
	 *
	 * @var string
	 */
	private $packing_method;

	/**
	 * User defined boxes.
	 *
	 * @var array
	 */
	private $boxes;

	/**
	 * Defined services from setting.
	 *
	 * @var array
	 */
	private $custom_services;

	/**
	 * Rates to offer.
	 *
	 * Valid values are "all" and "cheapest".
	 *
	 * @var string
	 */
	private $offer_rates;

	/**
	 * Fallback rate amount if no matching rates from API.
	 *
	 * @var string
	 */
	private $fallback;

	/**
	 * Flat rate fee.
	 *
	 * @var string
	 */
	private $flat_rate_fee;

	/**
	 * Method to handle unpacked item.
	 *
	 * Possible values are "", "ignore", "fallback", and "abort".
	 *
	 * @var string
	 */
	private $unpacked_item_handling;

	/**
	 * Whether standard service (rates API) is enabled or not.
	 *
	 * @var bool
	 */
	private $enable_standard_services;

	/**
	 * Total cost of unpacked items.
	 *
	 * @var float
	 */
	private $unpacked_item_costs;

	/**
	 * Transient's name for USPS API request.
	 *
	 * Saved the request params in property because transient need to created
	 * in another method.
	 *
	 * @since 4.4.9
	 * @version 4.4.9
	 *
	 * @see https://github.com/woocommerce/woocommerce-shipping-usps/issues/145
	 *
	 * @var string
	 */
	private $request_transient;

	/**
	 * Constructor.
	 *
	 * @param int $instance_id Instance ID.
	 */
	public function __construct( $instance_id = 0 ) {
		$this->id                 = 'usps';
		$this->instance_id        = absint( $instance_id );
		$this->method_title       = __( 'USPS', 'woocommerce-shipping-usps' );
		$this->method_description = __( 'The USPS extension obtains rates dynamically from the USPS API during cart/checkout.', 'woocommerce-shipping-usps' );
		$this->services           = include( 'data/data-services.php' );
		$this->flat_rate_boxes    = include( 'data/data-flat-rate-boxes.php' );
		$this->flat_rate_pricing  = include( 'data/data-flat-rate-box-pricing.php' );
		$this->supports           = array(
			'shipping-zones',
			'instance-settings',
			'settings',
		);
		$this->init();
	}

	/**
	 * Chceks whether this shipping instance is available or not.
	 *
	 * @param array $package Package to ship.
	 *
	 * @return bool True if available.
	 */
	public function is_available( $package ) {
		if ( empty( $package['destination']['country'] ) ) {
			return false;
		}

		return apply_filters( 'woocommerce_shipping_usps_is_available', true, $package );
	}

	/**
	 * Initialize settings.
	 *
	 * @version 4.4.0
	 * @since 4.4.0
	 * @return bool
	 */
	private function set_settings() {
		// Define user set variables.
		$this->title                    = $this->get_option( 'title', $this->method_title );
		$this->origin                   = $this->get_option( 'origin', '' );
		$this->user_id                  = $this->get_option( 'user_id', $this->default_user_id );
		$this->packing_method           = $this->get_option( 'packing_method', 'per_item' );
		$this->custom_services          = $this->get_option( 'services', array() );
		$this->boxes                    = $this->get_option( 'boxes', array() );
		$this->offer_rates              = $this->get_option( 'offer_rates', 'all' );
		$this->fallback                 = $this->get_option( 'fallback', '' );
		$this->flat_rate_fee            = $this->get_option( 'flat_rate_fee', '' );
		$this->mediamail_restriction    = array_filter( (array) $this->get_option( 'mediamail_restriction', array() ) );
		$this->unpacked_item_handling   = $this->get_option( 'unpacked_item_handling', '' );
		$this->enable_standard_services = 'yes' === $this->get_option( 'enable_standard_services', 'no' );
		$this->enable_flat_rate_boxes   = $this->get_option( 'enable_flat_rate_boxes', 'yes' );
		$this->debug                    = 'yes' === $this->get_option( 'debug_mode' );
		$this->shippingrates            = $this->get_option( 'shippingrates', 'ALL' );
		$this->flat_rate_boxes          = apply_filters( 'usps_flat_rate_boxes', $this->flat_rate_boxes );

		return true;
	}

	/**
	 * Init function.
	 *
	 * @access private
	 * @return void
	 */
	private function init() {
		// Load the settings.
		$this->init_form_fields();
		$this->set_settings();

		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'test_user_id' ), -10 );
		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'clear_transients' ) );
	}

	/**
	 * Process settings on save.
	 *
	 * @access public
	 * @return void
	 */
	public function process_admin_options() {
		parent::process_admin_options();

		$this->set_settings();
	}

	/**
	 * HTML for services option.
	 *
	 * @return string HTML for services option.
	 */
	public function generate_services_html() {
		ob_start();
		include( 'views/html-services.php' );
		return ob_get_clean();
	}

	/**
	 * HTML for box packing option.
	 *
	 * @return string HTML for box packing option.
	 */
	public function generate_box_packing_html() {
		ob_start();
		include( 'views/html-box-packing.php' );
		return ob_get_clean();
	}

	/**
	 * Validate flat_rate_fee field.
	 *
	 * @since 4.4.6
	 * @version 4.4.6
	 *
	 * @param string $key   Key.
	 * @param string $value Value.
	 *
	 * @return string
	 */
	public function validate_flat_rate_fee_field( $key, $value ) {
		$value  = is_null( $value ) ? '' : $value;
		$suffix = substr( $value, -1, 1 ) === '%' ? '%' : '';
		return ( '' === $value  ) ? '' : wc_format_decimal( trim( stripslashes( $value ) ) ) . $suffix;
	}

	/**
	 * Validate box packing field.
	 *
	 * @param string $key Field's key.
	 *
	 * @return array Validated value.
	 */
	public function validate_box_packing_field( $key ) {
		$boxes = array();

		if ( isset( $_POST['boxes_outer_length'] ) ) {
			$boxes_name         = isset( $_POST['boxes_name'] ) ? $_POST['boxes_name'] : array();
			$boxes_outer_length = $_POST['boxes_outer_length'];
			$boxes_outer_width  = $_POST['boxes_outer_width'];
			$boxes_outer_height = $_POST['boxes_outer_height'];
			$boxes_inner_length = $_POST['boxes_inner_length'];
			$boxes_inner_width  = $_POST['boxes_inner_width'];
			$boxes_inner_height = $_POST['boxes_inner_height'];
			$boxes_box_weight   = $_POST['boxes_box_weight'];
			$boxes_max_weight   = $_POST['boxes_max_weight'];
			$boxes_is_letter    = isset( $_POST['boxes_is_letter'] ) ? $_POST['boxes_is_letter'] : array();

			for ( $i = 0; $i < sizeof( $boxes_outer_length ); $i ++ ) {

				if ( $boxes_outer_length[ $i ] && $boxes_outer_width[ $i ] && $boxes_outer_height[ $i ] && $boxes_inner_length[ $i ] && $boxes_inner_width[ $i ] && $boxes_inner_height[ $i ] ) {

					$boxes[] = array(
						'name'         => wc_clean( $boxes_name[ $i ] ),
						'outer_length' => floatval( $boxes_outer_length[ $i ] ),
						'outer_width'  => floatval( $boxes_outer_width[ $i ] ),
						'outer_height' => floatval( $boxes_outer_height[ $i ] ),
						'inner_length' => floatval( $boxes_inner_length[ $i ] ),
						'inner_width'  => floatval( $boxes_inner_width[ $i ] ),
						'inner_height' => floatval( $boxes_inner_height[ $i ] ),
						'box_weight'   => floatval( $boxes_box_weight[ $i ] ),
						'max_weight'   => floatval( $boxes_max_weight[ $i ] ),
						'is_letter'    => isset( $boxes_is_letter[ $i ] ) ? true : false,
					);

				}
			}
		}

		return $boxes;
	}

	/**
	 * Validate services field.
	 *
	 * @param string $key Field's key.
	 *
	 * @return array Validated value.
	 */
	public function validate_services_field( $key ) {
		$services         = array();
		$posted_services  = $_POST['usps_service'];

		foreach ( $posted_services as $code => $settings ) {

			$services[ $code ] = array(
				'name'  => wc_clean( $settings['name'] ),
				'order' => wc_clean( $settings['order'] ),
			);

			foreach ( $this->services[ $code ]['services'] as $key => $name ) {
				// Process sub sub services.
				if ( 0 === $key ) {
					foreach ( $name as $subsub_service_key => $subsub_service ) {
						$services[ $code ][ $key ][ $subsub_service_key ]['enabled'] = isset( $settings[ $key ][ $subsub_service_key ]['enabled'] ) ? true : false;
						$services[ $code ][ $key ][ $subsub_service_key ]['adjustment'] = wc_clean( $settings[ $key ][ $subsub_service_key ]['adjustment'] );
						$services[ $code ][ $key ][ $subsub_service_key ]['adjustment_percent'] = wc_clean( $settings[ $key ][ $subsub_service_key ]['adjustment_percent'] );
					}
				} else {
					$services[ $code ][ $key ]['enabled'] = isset( $settings[ $key ]['enabled'] ) ? true : false;
					$services[ $code ][ $key ]['adjustment'] = wc_clean( $settings[ $key ]['adjustment'] );
					$services[ $code ][ $key ]['adjustment_percent'] = wc_clean( $settings[ $key ]['adjustment_percent'] );
				}
			}
		}

		return $services;
	}

	/**
	 * Clear transients used by this shipping method.
	 */
	public function clear_transients() {
		global $wpdb;

		$wpdb->query( "DELETE FROM `$wpdb->options` WHERE `option_name` LIKE ('_transient_usps_quote_%') OR `option_name` LIKE ('_transient_timeout_usps_quote_%')" );
	}

	/**
	 * Initialize form fields.
	 */
	public function init_form_fields() {
		$shipping_classes = array();
		$classes = ( $classes = get_terms( 'product_shipping_class', array( 'hide_empty' => '0' ) ) ) ? $classes : array();

		foreach ( $classes as $class ) {
			$shipping_classes[ $class->term_id ] = $class->name;
		}

		$this->instance_form_fields = array(
			'title' => array(
				'title'       => __( 'Method Title', 'woocommerce-shipping-usps' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce-shipping-usps' ),
				'default'     => __( 'USPS', 'woocommerce-shipping-usps' ),
				'desc_tip'    => true,
			),
			'offer_rates' => array(
				'title'        => __( 'Offer Rates', 'woocommerce-shipping-usps' ),
				'type'         => 'select',
				'description'  => '',
				'default'      => 'all',
				'options'      => array(
					'all'      => __( 'Offer the customer all returned rates', 'woocommerce-shipping-usps' ),
					'cheapest' => __( 'Offer the customer the cheapest rate only', 'woocommerce-shipping-usps' ),
				),
			),
			'fallback' => array(
				'title'       => __( 'Fallback', 'woocommerce-shipping-usps' ),
				'type'        => 'text',
				'desc_tip'    => true,
				'description' => __( 'If USPS returns no matching rates, offer this amount for shipping so that the user can still checkout. Leave blank to disable.', 'woocommerce-shipping-usps' ),
				'default'     => '',
				'placeholder' => __( 'Disabled', 'woocommerce-shipping-usps' ),
			),
			'flat_rates' => array(
				'title'       => __( 'Flat Rates', 'woocommerce-shipping-usps' ),
				'type'        => 'title',
				'description' => __( 'These are USPS flat rate boxes services. They are not calculated using the USPS API.', 'woocommerce-shipping-usps' ),
			),
			'enable_flat_rate_boxes' => array(
				'title'        => __( 'Flat Rate Boxes &amp; envelopes', 'woocommerce-shipping-usps' ),
				'type'         => 'select',
				'default'      => 'yes',
				'options'      => array(
					'yes'      => __( 'Yes - Enable flat rate services', 'woocommerce-shipping-usps' ),
					'no'       => __( 'No - Disable flat rate services', 'woocommerce-shipping-usps' ),
					'priority' => __( 'Enable Priority flat rate services only', 'woocommerce-shipping-usps' ),
					'express'  => __( 'Enable Express flat rate services only', 'woocommerce-shipping-usps' ),
				),
				'description'  => __( 'Enable this option to offer shipping using USPS Flat Rate services. Items will be packed into the boxes/envelopes and the customer will be offered a single rate from these.', 'woocommerce-shipping-usps' ),
				'desc_tip'     => true,
			),
			'flat_rate_express_title' => array(
				'title'       => __( 'Express Flat Rate Title', 'woocommerce-shipping-usps' ),
				'type'        => 'text',
				'description' => '',
				'default'     => '',
				'placeholder' => 'Priority Mail Express Flat Rate&#0174;',
			),
			'flat_rate_priority_title' => array(
				'title'       => __( 'Priority Flat Rate Title', 'woocommerce-shipping-usps' ),
				'type'        => 'text',
				'description' => '',
				'default'     => '',
				'placeholder' => 'Priority Mail Flat Rate&#0174;',
			),
			'flat_rate_fee' => array(
				'title'       => __( 'Additional Fee', 'woocommerce' ),
				'type'        => 'price',
				'description' => __( 'Fee per-box excluding tax. Enter an amount, e.g. 2.50, or a percentage, e.g. 5%. Leave blank to disable.', 'woocommerce' ),
				'default'     => '',
				'desc_tip'    => true,
			),
			'standard_rates' => array(
				'title'       => __( 'API Rates', 'woocommerce-shipping-usps' ),
				'type'        => 'title',
				'description' => __( 'These are standard service rates pulled from the USPS API.', 'woocommerce-shipping-usps' ),
			),
			'enable_standard_services' => array(
				'title'       => __( 'Enable API Rates', 'woocommerce-shipping-usps' ),
				'label'       => __( 'Retrieve Standard Service rates from the USPS API', 'woocommerce-shipping-usps' ),
				'type'        => 'checkbox',
				'default'     => 'no',
				'desc_tip'    => true,
				'description' => __( 'Enable non-flat rate services.', 'woocommerce-shipping-usps' ),
			),
			'origin' => array(
				'title'       => __( 'Origin Postcode (required)', 'woocommerce-shipping-usps' ),
				'type'        => 'text',
				'description' => __( 'Enter the postcode for the <strong>sender</strong>.', 'woocommerce-shipping-usps' ),
				'default'     => '',
				'desc_tip'    => true,
			),
			'shippingrates' => array(
				'title'       => __( 'Shipping Rates', 'woocommerce-shipping-usps' ),
				'type'        => 'select',
				'default'     => 'ALL',
				'options'     => array(
					'ONLINE'  => __( 'Use Commercial Rates', 'woocommerce-shipping-usps' ),
					'ALL'     => __( 'Use Retail Rates', 'woocommerce-shipping-usps' ),
				),
				'desc_tip'    => true,
				'description' => __( 'Choose which rates to show your customers: Standard retail or discounted commercial rates.', 'woocommerce-shipping-usps' ),
			),
			'packing_method' => array(
				'title'            => __( 'Parcel Packing Method', 'woocommerce-shipping-usps' ),
				'type'             => 'select',
				'default'          => '',
				'class'            => 'packing_method',
				'options'          => array(
					'per_item'     => __( 'Default: Pack items individually', 'woocommerce-shipping-usps' ),
					'box_packing'  => __( 'Recommended: Pack into boxes with weights and dimensions', 'woocommerce-shipping-usps' ),
					'weight_based' => __( 'Weight based: Regular sized items (< 12 inches) are grouped and quoted for weights only. Large items are quoted individually.', 'woocommerce-shipping-usps' ),
				),
			),
			'boxes' => array(
				'type' => 'box_packing',
			),
			'unpacked_item_handling' => array(
				'title'        => __( 'Unpacked item handling', 'woocommerce-shipping-usps' ),
				'type'         => 'select',
				'description'  => '',
				'default'      => 'all',
				'options'      => array(
					''         => __( 'Get a quote for the unpacked item by itself', 'woocommerce-shipping-usps' ),
					'ingore'   => __( 'Ignore the item - do not quote', 'woocommerce-shipping-usps' ),
					'fallback' => __( 'Use the fallback price (above)', 'woocommerce-shipping-usps' ),
					'abort'    => __( 'Abort - do not return any quotes for the standard services', 'woocommerce-shipping-usps' ),
				),
			),
			'services' => array(
				'type' => 'services',
			),
			'mediamail_restriction' => array(
				'title'                => __( 'Restrict Media Mail to...', 'woocommerce-shipping-usps' ),
				'type'                 => 'multiselect',
				'class'                => 'chosen_select',
				'css'                  => 'width: 450px;',
				'default'              => '',
				'options'              => $shipping_classes,
				'custom_attributes'    => array(
					'data-placeholder' => __( 'No restrictions', 'woocommerce-shipping-usps' ),
				),
			),
		);

		$this->form_fields = array(
			'user_id' => array(
				'title'       => __( 'USPS User ID', 'woocommerce-shipping-usps' ),
				'type'        => 'text',
				'description' => sprintf( __( 'You can obtain a USPS user ID by %s, or just use ours by leaving the field blank. This is optional.', 'woocommerce-shipping-usps' ), '<a href="https://www.usps.com/business/web-tools-apis/welcome.htm">' . __( 'signing up on the USPS website', 'woocommerce-shipping-usps' ) . '</a>' ),
				'default'     => '',
				'placeholder' => $this->default_user_id,
				'desc_tip'    => true,
			),
			'debug_mode' => array(
				'title'       => __( 'Debug Mode', 'woocommerce-shipping-usps' ),
				'label'       => __( 'Enable debug mode', 'woocommerce-shipping-usps' ),
				'type'        => 'checkbox',
				'default'     => 'no',
				'desc_tip'    => true,
				'description' => __( 'Enable debug mode to show debugging information on your cart/checkout.', 'woocommerce-shipping-usps' ),
			),
		);
	}

	/**
	 * Perform a request to check user ID validness.
	 *
	 * Error notice will displayed if user ID is invalid.
	 */
	public function test_user_id() {
		if ( empty( $_POST['woocommerce_usps_user_id'] ) ) {
			return;
		}

		$example_xml  = '<RateV4Request USERID="' . esc_attr( $_POST['woocommerce_usps_user_id'] ) . '">';
		$example_xml .= '<Revision>2</Revision>';
		$example_xml .= '<Package ID="1">';
		$example_xml .= '<Service>PRIORITY</Service>';
		$example_xml .= '<ZipOrigination>97201</ZipOrigination>';
		$example_xml .= '<ZipDestination>44101</ZipDestination>';
		$example_xml .= '<Pounds>1</Pounds>';
		$example_xml .= '<Ounces>0</Ounces>';
		$example_xml .= '<Container />';
		$example_xml .= '<Size>REGULAR</Size>';
		$example_xml .= '</Package>';
		$example_xml .= '</RateV4Request>';

		$response = wp_remote_post( $this->endpoint, array(
			'body'      => 'API=RateV4&XML=' . $example_xml,
		) );

		if ( is_wp_error( $response ) ) {
			return;
		}
		if ( ! ( $xml = $this->get_parsed_xml( $response['body'] ) ) ) {
			return;
		}
		if ( ! is_object( $xml ) && ! is_a( $xml, 'SimpleXMLElement' ) ) {
			return;
		}

		// 80040B1A is an Authorization failure
		if ( '80040B1A' !== $xml->Number->__toString() ) {
			return;
		}

		echo '<div class="error">
			<p>' . __( 'The USPS User ID you entered is invalid. Please make sure you entered a valid ID (<a href="https://www.usps.com/business/web-tools-apis/welcome.htm">which can be obtained here</a>). Our User ID will be used instead.', 'woocommerce-shipping-usps' ) . '</p>
		</div>';

		$_POST['woocommerce_usps_user_id'] = '';
	}

	/**
	 * Get Parsed XML response.
	 *
	 * @param string $xml XML string.
	 *
	 * @return string|bool
	 */
	private function get_parsed_xml( $xml ) {
		if ( ! class_exists( 'WC_Safe_DOMDocument' ) ) {
			include_once( 'class-wc-safe-domdocument.php' );
		}

		libxml_use_internal_errors( true );

		$dom     = new WC_Safe_DOMDocument;
		$success = $dom->loadXML( $xml );

		if ( ! $success ) {
			if ( $this->debug ) {
				trigger_error( 'wpcom_safe_simplexml_load_string(): Error loading XML string', E_USER_WARNING );
			}
			return false;
		}

		if ( isset( $dom->doctype ) ) {
			if ( $this->debug ) {
				trigger_error( 'wpcom_safe_simplexml_import_dom(): Unsafe DOCTYPE Detected', E_USER_WARNING );
			}
			return false;
		}

		return simplexml_import_dom( $dom, 'SimpleXMLElement' );
	}

	/**
	 * Calculate shipping cost.
	 *
	 * @since 1.0.0
	 * @version 4.4.7
	 *
	 * @param array $package Package to ship.
	 * @return void
	 */
	public function calculate_shipping( $package = array() ) {
		$this->unpacked_item_costs = 0;
		$domestic                  = in_array( $package['destination']['country'], $this->domestic ) ? true : false;

		$this->debug( __( 'USPS debug mode is on - to hide these messages, turn debug mode off in the settings.', 'woocommerce-shipping-usps' ) );

		if ( $this->enable_standard_services ) {
			$this->calculate_standard_service( $package );
		}

		// Flat Rate boxes quote.
		if ( 'yes' === $this->enable_flat_rate_boxes || 'priority' === $this->enable_flat_rate_boxes ) {
			// Priority.
			$flat_rate = $this->calculate_flat_rate_box_rate( $package, 'priority' );

			if ( $flat_rate ) {
				$this->found_rates[ $flat_rate['id'] ] = $flat_rate;
			}
		}
		if ( 'yes' === $this->enable_flat_rate_boxes || 'express' === $this->enable_flat_rate_boxes ) {
			// Express.
			$flat_rate = $this->calculate_flat_rate_box_rate( $package, 'express' );

			if ( $flat_rate ) {
				$this->found_rates[ $flat_rate['id'] ] = $flat_rate;
			}
		}

		// Add rates.
		if ( $this->found_rates ) {
			$this->check_found_rates();
		} elseif ( $this->fallback ) {
			$this->add_rate( array(
				'id'    => $this->id . '_fallback',
				'label' => $this->title,
				'cost'  => $this->fallback,
				'sort'  => 0,
			) );
		}
	}

	/**
	 * Check found rates.
	 *
	 * @version 4.4.7
	 */
	private function check_found_rates() {
		// Only offer one priority rate.
		if ( isset( $this->found_rates['usps:D_PRIORITY_MAIL'] ) && isset( $this->found_rates['usps:flat_rate_box_priority'] ) ) {
			if ( $this->found_rates['usps:flat_rate_box_priority']['cost'] < $this->found_rates['usps:D_PRIORITY_MAIL']['cost']  ) {
				$this->debug( 'Unsetting PRIORITY MAIL api rate - flat rate box is cheaper.' );
				unset( $this->found_rates['usps:D_PRIORITY_MAIL'] );
			} else {
				$this->debug( 'Unsetting PRIORITY MAIL flat rate - api rate is cheaper.' );
				unset( $this->found_rates['usps:flat_rate_box_priority'] );
			}
		}

		if ( isset( $this->found_rates['usps:D_EXPRESS_MAIL'] ) && isset( $this->found_rates['usps:flat_rate_box_express'] ) ) {
			if ( $this->found_rates['usps:flat_rate_box_express']['cost'] < $this->found_rates['usps:D_EXPRESS_MAIL']['cost']  ) {
				$this->debug( 'Unsetting PRIORITY MAIL EXPRESS api rate - flat rate box is cheaper.' );
				unset( $this->found_rates['usps:D_EXPRESS_MAIL'] );
			} else {
				$this->debug( 'Unsetting PRIORITY MAIL EXPRESS flat rate - api rate is cheaper.' );
				unset( $this->found_rates['usps:flat_rate_box_express'] );
			}
		}

		if ( isset( $this->found_rates['usps:I_PRIORITY_MAIL'] ) && isset( $this->found_rates['usps:flat_rate_box_priority'] ) ) {
			if ( $this->found_rates['usps:flat_rate_box_priority']['cost'] < $this->found_rates['usps:I_PRIORITY_MAIL']['cost']  ) {
				$this->debug( 'Unsetting PRIORITY MAIL api rate - flat rate box is cheaper.' );
				unset( $this->found_rates['usps:I_PRIORITY_MAIL'] );
			} else {
				$this->debug( 'Unsetting PRIORITY MAIL flat rate - api rate is cheaper.' );
				unset( $this->found_rates['usps:flat_rate_box_priority'] );
			}
		}

		if ( isset( $this->found_rates['usps:I_EXPRESS_MAIL'] ) && isset( $this->found_rates['usps:flat_rate_box_express'] ) ) {
			if ( $this->found_rates['usps:flat_rate_box_express']['cost'] < $this->found_rates['usps:I_EXPRESS_MAIL']['cost']  ) {
				$this->debug( 'Unsetting PRIORITY MAIL EXPRESS api rate - flat rate box is cheaper.' );
				unset( $this->found_rates['usps:I_EXPRESS_MAIL'] );
			} else {
				$this->debug( 'Unsetting PRIORITY MAIL EXPRESS flat rate - api rate is cheaper.' );
				unset( $this->found_rates['usps:flat_rate_box_express'] );
			}
		}

		if ( 'all' === $this->offer_rates ) {
			uasort( $this->found_rates, array( $this, 'sort_rates' ) );

			foreach ( $this->found_rates as $key => $rate ) {
				$this->add_rate( $rate );
			}
		} else {
			$cheapest_rate = '';

			foreach ( $this->found_rates as $key => $rate ) {
				if ( ! $cheapest_rate || $cheapest_rate['cost'] > $rate['cost'] ) {
					$cheapest_rate = $rate;
				}
			}

			$cheapest_rate['label'] = $this->title;

			$this->add_rate( $cheapest_rate );
		}
	}

	/**
	 * Prepare rate.
	 *
	 * @param mixed  $rate_code Rate code.
	 * @param mixed  $rate_id   Rate ID.
	 * @param mixed  $rate_name Rate name.
	 * @param mixed  $rate_cost Cost.
	 * @param string $meta_data Rate meta data.
	 * @return void
	 */
	private function prepare_rate( $rate_code, $rate_id, $rate_name, $rate_cost, $meta_data = '' ) {
		// Name adjustment.
		if ( ! empty( $this->custom_services[ $rate_code ]['name'] ) ) {
			$rate_name = $this->custom_services[ $rate_code ]['name'];
		}

		// Merging.
		if ( isset( $this->found_rates[ $rate_id ] ) ) {
			$rate_cost = $rate_cost + $this->found_rates[ $rate_id ]['cost'];
			$packages  = 1 + $this->found_rates[ $rate_id ]['packages'];
		} else {
			$packages = 1;
		}

		// Package metadata.
		$meta_data_value = array();
		if ( $meta_data ) {
			if ( isset( $this->found_rates[ $rate_id ] ) && array_key_exists( 'meta_data', $this->found_rates[ $rate_id ] ) ) {
				$meta_data_value = $this->found_rates[ $rate_id ]['meta_data'];
				$meta_data_value[ 'Package ' . $packages ] = $meta_data;
			} else {
				$meta_data_value = array( 'Package ' . $packages => $meta_data );
			}
		}

		// Weight based shipping doesn't have package information.
		if ( 'weight_based' === $this->packing_method ) {
			$meta_data_value = array( 'Packing' => 'Weight Based Shipping' );
		}

		// Sort.
		if ( isset( $this->custom_services[ $rate_code ]['order'] ) ) {
			$sort = $this->custom_services[ $rate_code ]['order'];
		} else {
			$sort = 999;
		}

		$this->found_rates[ $rate_id ] = array(
			'id'        => $rate_id,
			'label'     => $rate_name,
			'cost'      => $rate_cost,
			'sort'      => $sort,
			'packages'  => $packages,
			'meta_data' => $meta_data_value,
		);
	}

	/**
	 * Get meta data string for the shipping rate.
	 *
	 * @since 4.4.7
	 * @version 4.4.7
	 *
	 * @param array $params Meta data info to join.
	 *
	 * @return string Rate meta data.
	 */
	private function get_rate_meta_data( $params ) {
		$meta_data = array();

		if ( ! empty( $params['name'] ) ) {
			$meta_data[] = $params['name'] . ' -';
		}

		if ( $params['length'] && $params['width'] && $params['height'] ) {
			$meta_data[] = sprintf( '%1$s × %2$s × %3$s (in)', $params['length'], $params['width'], $params['height'] );
		}
		if ( $params['weight'] ) {
			$meta_data[] = round( $params['weight'], 2 ) . 'lbs';
		}
		if ( $params['qty'] ) {
			$meta_data[] = '× ' . $params['qty'];
		}

		return implode( ' ', $meta_data );
	}

	/**
	 * Sort rate.
	 *
	 * @access public
	 * @param mixed $a A.
	 * @param mixed $b B.
	 * @return int
	 */
	public function sort_rates( $a, $b ) {
		if ( $a['sort'] == $b['sort'] ) {
			return 0;
		}

		return ( $a['sort'] < $b['sort'] ) ? -1 : 1;
	}

	/**
	 * Get package request.
	 *
	 * @access private
	 * @param array $package Package to ship.
	 * @return array
	 */
	private function get_package_requests( $package ) {
		// Choose selected packing.
		switch ( $this->packing_method ) {
			case 'box_packing' :
				$requests = $this->box_shipping( $package );
			break;
			case 'weight_based' :
				$requests = $this->weight_based_shipping( $package );
			break;
			case 'per_item' :
			default :
				$requests = $this->per_item_shipping( $package );
			break;
		}

		return $requests;
	}

	/**
	 * Per item shipping.
	 *
	 * @access private
	 * @param mixed $package Package to ship.
	 * @return array
	 */
	private function per_item_shipping( $package ) {
		$domestic = in_array( $package['destination']['country'], $this->domestic ) ? true : false;
		$requests = array();

		// Get weight of order.
		foreach ( $package['contents'] as $item_id => $values ) {

			if ( ! $values['data']->needs_shipping() ) {
				$this->debug( sprintf( __( 'Product # is virtual. Skipping.', 'woocommerce-shipping-usps' ), $item_id ) );
				continue;
			}

			if ( ! $values['data']->get_weight() ) {
				$this->debug( sprintf( __( 'Product # is missing weight. Using 1lb.', 'woocommerce-shipping-usps' ), $item_id ) );

				$weight = 1;
			} else {
				$weight = wc_get_weight( $values['data']->get_weight(), 'lbs' );
			}

			$size   = 'REGULAR';

			if ( $values['data']->get_length() && $values['data']->get_height() && $values['data']->get_width() ) {

				$dimensions = array( wc_get_dimension( $values['data']->get_length(), 'in' ), wc_get_dimension( $values['data']->get_height(), 'in' ), wc_get_dimension( $values['data']->get_width(), 'in' ) );

				sort( $dimensions );

				if ( max( $dimensions ) > 12 ) {
					$size   = 'LARGE';
				}

				$girth = $dimensions[0] + $dimensions[0] + $dimensions[1] + $dimensions[1];
			} else {
				$dimensions = array( 0, 0, 0 );
				$girth      = 0;
			}

			if ( $domestic ) {

				$request  = '<Package ID="' . $this->generate_package_id( $item_id, $values['quantity'], $dimensions[2], $dimensions[1], $dimensions[0], $weight ) . '">' . "\n";
				$request .= '	<Service>' . $this->shippingrates . '</Service>' . "\n";
				$request .= '	<ZipOrigination>' . str_replace( ' ', '', strtoupper( $this->origin ) ) . '</ZipOrigination>' . "\n";
				$request .= '	<ZipDestination>' . strtoupper( substr( $package['destination']['postcode'], 0, 5 ) ) . '</ZipDestination>' . "\n";
				$request .= '	<Pounds>' . floor( $weight ) . '</Pounds>' . "\n";
				$request .= '	<Ounces>' . number_format( ( $weight - floor( $weight ) ) * 16, 2 ) . '</Ounces>' . "\n";

				if ( 'LARGE' === $size ) {
					$request .= '	<Container>RECTANGULAR</Container>' . "\n";
				} else {
					$request .= '	<Container />' . "\n";
				}

				$request .= '	<Size>' . $size . '</Size>' . "\n";
				$request .= '	<Width>' . $dimensions[1] . '</Width>' . "\n";
				$request .= '	<Length>' . $dimensions[2] . '</Length>' . "\n";
				$request .= '	<Height>' . $dimensions[0] . '</Height>' . "\n";
				$request .= '	<Girth>' . round( $girth ) . '</Girth>' . "\n";
				$request .= '	<Machinable>true</Machinable> ' . "\n";
				$request .= '	<ShipDate>' . date( 'd-M-Y', ( current_time( 'timestamp' ) + ( 60 * 60 * 24 ) ) ) . '</ShipDate>' . "\n";
				$request .= '</Package>' . "\n";

			} else {

				$request  = '<Package ID="' . $this->generate_package_id( $item_id, $values['quantity'], $dimensions[2], $dimensions[1], $dimensions[0], $weight ) . '">' . "\n";
				$request .= '	<Pounds>' . floor( $weight ) . '</Pounds>' . "\n";
				$request .= '	<Ounces>' . number_format( ( $weight - floor( $weight ) ) * 16, 2 ) . '</Ounces>' . "\n";
				$request .= '	<Machinable>true</Machinable> ' . "\n";
				$request .= '	<MailType>Package</MailType>' . "\n";
				$request .= '	<ValueOfContents>' . $values['data']->get_price() . '</ValueOfContents>' . "\n";
				$request .= '	<Country>' . $this->get_country_name( $package['destination']['country'] ) . '</Country>' . "\n";

				$request .= '	<Container>RECTANGULAR</Container>' . "\n";

				$request .= '	<Size>' . $size . '</Size>' . "\n";
				$request .= '	<Width>' . $dimensions[1] . '</Width>' . "\n";
				$request .= '	<Length>' . $dimensions[2] . '</Length>' . "\n";
				$request .= '	<Height>' . $dimensions[0] . '</Height>' . "\n";
				$request .= '	<Girth>' . round( $girth ) . '</Girth>' . "\n";
				$request .= '	<OriginZip>' . str_replace( ' ', '', strtoupper( $this->origin ) ) . '</OriginZip>' . "\n";
				$request .= '	<CommercialFlag>' . ( 'ONLINE' === $this->shippingrates ? 'Y' : 'N' ) . '</CommercialFlag>' . "\n";
				$request .= '</Package>' . "\n";

			}

			$requests[] = $request;
		}

		return $requests;
	}

	/**
	 * Generate shipping request for weights only.
	 *
	 * @param array $package Package to ship.
	 *
	 * @return array
	 */
	private function weight_based_shipping( $package ) {
		$requests                  = array();
		$domestic                  = in_array( $package['destination']['country'], $this->domestic ) ? true : false;
		$total_regular_item_weight = 0;

		// Add requests for larger items.
		foreach ( $package['contents'] as $item_id => $values ) {

			if ( ! $values['data']->needs_shipping() ) {
				$this->debug( sprintf( __( 'Product #%d is virtual. Skipping.', 'woocommerce-shipping-usps' ), $item_id ) );
				continue;
			}

			if ( ! $values['data']->get_weight() ) {
				$this->debug( sprintf( __( 'Product #%d is missing weight. Using 1lb.', 'woocommerce-shipping-usps' ), $item_id ) );

				$weight = 1;
			} else {
				$weight = wc_get_weight( $values['data']->get_weight(), 'lbs' );
			}

			$dimensions = array( wc_get_dimension( $values['data']->get_length(), 'in' ), wc_get_dimension( $values['data']->get_height(), 'in' ), wc_get_dimension( $values['data']->get_width(), 'in' ) );

			sort( $dimensions );

			if ( max( $dimensions ) <= 12 ) {
				$total_regular_item_weight += ( $weight * $values['quantity'] );
				continue;
			}

			$girth = $dimensions[0] + $dimensions[0] + $dimensions[1] + $dimensions[1];

			if ( $domestic ) {
				$request  = '<Package ID="' . $this->generate_package_id( $item_id, $values['quantity'], $dimensions[2], $dimensions[1], $dimensions[0], $weight ) . '">' . "\n";
				$request .= '	<Service>' . $this->shippingrates . '</Service>' . "\n";
				$request .= '	<ZipOrigination>' . str_replace( ' ', '', strtoupper( $this->origin ) ) . '</ZipOrigination>' . "\n";
				$request .= '	<ZipDestination>' . strtoupper( substr( $package['destination']['postcode'], 0, 5 ) ) . '</ZipDestination>' . "\n";
				$request .= '	<Pounds>' . floor( $weight ) . '</Pounds>' . "\n";
				$request .= '	<Ounces>' . number_format( ( $weight - floor( $weight ) ) * 16, 2 ) . '</Ounces>' . "\n";
				$request .= '	<Container>RECTANGULAR</Container>' . "\n";
				$request .= '	<Size>LARGE</Size>' . "\n";
				$request .= '	<Width>' . $dimensions[1] . '</Width>' . "\n";
				$request .= '	<Length>' . $dimensions[2] . '</Length>' . "\n";
				$request .= '	<Height>' . $dimensions[0] . '</Height>' . "\n";
				$request .= '	<Girth>' . round( $girth ) . '</Girth>' . "\n";
				$request .= '	<Machinable>true</Machinable> ' . "\n";
				$request .= '	<ShipDate>' . date( 'd-M-Y', ( current_time( 'timestamp' ) + ( 60 * 60 * 24 ) ) ) . '</ShipDate>' . "\n";
				$request .= '</Package>' . "\n";
			} else {
				$request  = '<Package ID="' . $this->generate_package_id( $item_id, $values['quantity'], $dimensions[2], $dimensions[1], $dimensions[0], $weight ) . '">' . "\n";
				$request .= '	<Pounds>' . floor( $weight ) . '</Pounds>' . "\n";
				$request .= '	<Ounces>' . number_format( ( $weight - floor( $weight ) ) * 16, 2 ) . '</Ounces>' . "\n";
				$request .= '	<Machinable>true</Machinable> ' . "\n";
				$request .= '	<MailType>Package</MailType>' . "\n";
				$request .= '	<ValueOfContents>' . $values['data']->get_price() . '</ValueOfContents>' . "\n";
				$request .= '	<Country>' . $this->get_country_name( $package['destination']['country'] ) . '</Country>' . "\n";
				$request .= '	<Container>RECTANGULAR</Container>' . "\n";
				$request .= '	<Size>LARGE</Size>' . "\n";
				$request .= '	<Width>' . $dimensions[1] . '</Width>' . "\n";
				$request .= '	<Length>' . $dimensions[2] . '</Length>' . "\n";
				$request .= '	<Height>' . $dimensions[0] . '</Height>' . "\n";
				$request .= '	<Girth>' . round( $girth ) . '</Girth>' . "\n";
				$request .= '	<OriginZip>' . str_replace( ' ', '', strtoupper( $this->origin ) ) . '</OriginZip>' . "\n";
				$request .= '	<CommercialFlag>' . ( 'ONLINE' === $this->shippingrates ? 'Y' : 'N' ) . '</CommercialFlag>' . "\n";
				$request .= '</Package>' . "\n";
			}

			$requests[] = $request;
		}

		// Regular package.
		if ( $total_regular_item_weight > 0 ) {
			$max_package_weight = ( $domestic || 'MX' === $package['destination']['country'] ) ? 70 : 44;
			$package_weights    = array();

			$full_packages      = floor( $total_regular_item_weight / $max_package_weight );

			for ( $i = 0; $i < $full_packages; $i ++ ) {
				$package_weights[] = $max_package_weight;
			}

			if ( $remainder = fmod( $total_regular_item_weight, $max_package_weight ) ) {
				$package_weights[] = $remainder;
			}

			foreach ( $package_weights as $key => $weight ) {
				if ( $domestic ) {
					$request  = '<Package ID="' . $this->generate_package_id( 'regular_' . $key, 1, 0, 0, 0, 0 ) . '">' . "\n";
					$request .= '	<Service>' . $this->shippingrates . '</Service>' . "\n";
					$request .= '	<ZipOrigination>' . str_replace( ' ', '', strtoupper( $this->origin ) ) . '</ZipOrigination>' . "\n";
					$request .= '	<ZipDestination>' . strtoupper( substr( $package['destination']['postcode'], 0, 5 ) ) . '</ZipDestination>' . "\n";
					$request .= '	<Pounds>' . floor( $weight ) . '</Pounds>' . "\n";
					$request .= '	<Ounces>' . number_format( ( $weight - floor( $weight ) ) * 16, 2 ) . '</Ounces>' . "\n";
					$request .= '	<Container />' . "\n";
					$request .= '	<Size>REGULAR</Size>' . "\n";
					$request .= '	<Machinable>true</Machinable> ' . "\n";
					$request .= '	<ShipDate>' . date( 'd-M-Y', ( current_time( 'timestamp' ) + ( 60 * 60 * 24 ) ) ) . '</ShipDate>' . "\n";
					$request .= '</Package>' . "\n";
				} else {
					$request  = '<Package ID="' . $this->generate_package_id( 'regular_' . $key, 1, 0, 0, 0, 0 ) . '">' . "\n";
					$request .= '	<Pounds>' . floor( $weight ) . '</Pounds>' . "\n";
					$request .= '	<Ounces>' . number_format( ( $weight - floor( $weight ) ) * 16, 2 ) . '</Ounces>' . "\n";
					$request .= '	<Machinable>true</Machinable> ' . "\n";
					$request .= '	<MailType>Package</MailType>' . "\n";
					$request .= '	<ValueOfContents>' . $values['data']->get_price() . '</ValueOfContents>' . "\n";
					$request .= '	<Country>' . $this->get_country_name( $package['destination']['country'] ) . '</Country>' . "\n";
					$request .= '	<Container />' . "\n";
					$request .= '	<Size>REGULAR</Size>' . "\n";
					$request .= '	<Width />' . "\n";
					$request .= '	<Length />' . "\n";
					$request .= '	<Height />' . "\n";
					$request .= '	<Girth />' . "\n";
					$request .= '	<OriginZip>' . str_replace( ' ', '', strtoupper( $this->origin ) ) . '</OriginZip>' . "\n";
					$request .= '	<CommercialFlag>' . ( 'ONLINE' === $this->shippingrates ? 'Y' : 'N' ) . '</CommercialFlag>' . "\n";
					$request .= '</Package>' . "\n";
				}

				$requests[] = $request;
			}
		}

		return $requests;
	}

	/**
	 * Generate a package ID for the request.
	 *
	 * Contains qty and dimension info so we can look at it again later when it
	 * comes back from USPS if needed.
	 *
	 * @param string $id     Package ID.
	 * @param int    $qty    Quantity.
	 * @param float  $l      Length.
	 * @param float  $w      Width.
	 * @param float  $h      Height.
	 * @param float  $weight Weight.
	 *
	 * @return string
	 */
	public function generate_package_id( $id, $qty, $l, $w, $h, $weight ) {
		return implode( ':', array( $id, $qty, $l, $w, $h, $weight ) );
	}

	/**
	 * Generate XML requests using box packing method.
	 *
	 * @version 4.4.7
	 *
	 * @param array $package Package to ship.
	 *
	 * @return array Array of XML requests.
	 */
	private function box_shipping( $package ) {
		$requests = array();
		$domestic = in_array( $package['destination']['country'], $this->domestic ) ? true : false;

		if ( ! class_exists( 'WC_Boxpack' ) ) {
			include_once 'box-packer/class-wc-boxpack.php';
		}

		$boxpack = new WC_Boxpack();

		// Define boxes.
		foreach ( $this->boxes as $key => $box ) {

			$newbox = $boxpack->add_box( $box['outer_length'], $box['outer_width'], $box['outer_height'], $box['box_weight'] );

			$newbox->set_id( isset( $box['name'] ) ? $box['name'] : $key );
			$newbox->set_inner_dimensions( $box['inner_length'], $box['inner_width'], $box['inner_height'] );

			if ( $box['max_weight'] ) {
				$newbox->set_max_weight( $box['max_weight'] );
			}

			if ( $box['is_letter'] ) {
				$newbox->set_type( 'envelope' );
			}
		}

		// Define box size A.
		if ( ! empty( $this->custom_services['D_PRIORITY_MAIL']['47']['enabled'] ) ) {
			$newbox = $boxpack->add_box( 10, 7, 4.75 );
			$newbox->set_id( 'Regional Rate Box A1' );
			$newbox->set_max_weight( 15 );
			$newbox = $boxpack->add_box( 12.8125, 10.9375, 2.375 );
			$newbox->set_id( 'Regional Rate Box A2' );
			$newbox->set_max_weight( 15 );
		}

		// Define box size B.
		if ( ! empty( $this->custom_services['D_PRIORITY_MAIL']['49']['enabled'] ) ) {
			$newbox = $boxpack->add_box( 12, 10.25, 5 );
			$newbox->set_id( 'Regional Rate Box B1' );
			$newbox->set_max_weight( 20 );
			$newbox = $boxpack->add_box( 15.875, 14.375, 2.875 );
			$newbox->set_id( 'Regional Rate Box B2' );
			$newbox->set_max_weight( 20 );
		}

		// Add items.
		foreach ( $package['contents'] as $item_id => $values ) {

			if ( ! $values['data']->needs_shipping() ) {
				continue;
			}

			if ( $values['data']->get_length() && $values['data']->get_height() && $values['data']->get_width() ) {
				$dimensions = array( wc_get_dimension( $values['data']->get_length(), 'in' ), wc_get_dimension( $values['data']->get_height(), 'in' ), wc_get_dimension( $values['data']->get_width(), 'in' ) );
			} else {
				$this->debug( sprintf( __( 'Product #%d is missing dimensions! Using 1x1x1.', 'woocommerce-shipping-usps' ), $item_id ) );
				$dimensions = array( 1, 1, 1 );
			}

			if ( $values['data']->get_weight() ) {
				$weight = wc_get_weight( $values['data']->get_weight(), 'lbs' );
			} else {
				$this->debug( sprintf( __( 'Product #%d is missing weight! Using 1lb.', 'woocommerce-shipping-usps' ), $item_id ) );
				$weight = 1;
			}

			for ( $i = 0; $i < $values['quantity']; $i ++ ) {
				$boxpack->add_item(
					$dimensions[2],
					$dimensions[1],
					$dimensions[0],
					$weight,
					$values['data']->get_price()
				);
			}
		}

		// Allow boxpack to be overriden by devs
		// (see https://github.com/woocommerce/woocommerce-shipping-usps/issues/155)
		$boxpack = apply_filters( 'woocommerce_shipping_usps_boxpack_before_pack', $boxpack );

		// Pack it.
		$boxpack->pack();

		// Get packages.
		$box_packages = $boxpack->get_packages();

		foreach ( $box_packages as $key => $box_package ) {

			if ( true === $box_package->unpacked ) {
				$this->debug( 'Unpacked Item' );

				switch ( $this->unpacked_item_handling ) {
					case 'fallback' :
						// No request, just a fallback.
						$this->unpacked_item_costs += $this->fallback;
						continue;
					break;
					case 'ignore' :
						// No request.
						continue;
					break;
					case 'abort' :
						// No requests!
						return false;
					break;
				}
			} else {
				$this->debug( 'Packed ' . $box_package->id );
			}

			$weight     = $box_package->weight;
			$size       = 'REGULAR';
			$dimensions = array( $box_package->length, $box_package->width, $box_package->height );

			sort( $dimensions );

			if ( max( $dimensions ) > 12 ) {
				$size   = 'LARGE';
			}

			$girth = $dimensions[0] + $dimensions[0] + $dimensions[1] + $dimensions[1];

			if ( $domestic ) {

				$request  = '<Package ID="' . $this->generate_package_id( $key, 1, $dimensions[2], $dimensions[1], $dimensions[0], $weight ) . '">' . "\n";
				$request .= '	<Service>' . $this->shippingrates . '</Service>' . "\n";
				$request .= '	<ZipOrigination>' . str_replace( ' ', '', strtoupper( $this->origin ) ) . '</ZipOrigination>' . "\n";
				$request .= '	<ZipDestination>' . strtoupper( substr( $package['destination']['postcode'], 0, 5 ) ) . '</ZipDestination>' . "\n";
				$request .= '	<Pounds>' . floor( $weight ) . '</Pounds>' . "\n";
				$request .= '	<Ounces>' . number_format( ( $weight - floor( $weight ) ) * 16, 2 ) . '</Ounces>' . "\n";

				if ( 'LARGE' === $size ) {
					$request .= '	<Container>RECTANGULAR</Container>' . "\n";
				} else {
					$request .= '	<Container />' . "\n";
				}

				$request .= '	<Size>' . $size . '</Size>' . "\n";
				$request .= '	<Width>' . $dimensions[1] . '</Width>' . "\n";
				$request .= '	<Length>' . $dimensions[2] . '</Length>' . "\n";
				$request .= '	<Height>' . $dimensions[0] . '</Height>' . "\n";
				$request .= '	<Girth>' . round( $girth ) . '</Girth>' . "\n";
				$request .= '	<Machinable>true</Machinable> ' . "\n";
				$request .= '	<ShipDate>' . date( 'd-M-Y', ( current_time( 'timestamp' ) + ( 60 * 60 * 24 ) ) ) . '</ShipDate>' . "\n";
				$request .= '</Package>' . "\n";

			} else {

				$request  = '<Package ID="' . $this->generate_package_id( $key, 1, $dimensions[2], $dimensions[1], $dimensions[0], $weight ) . '">' . "\n";
				$request .= '	<Pounds>' . floor( $weight ) . '</Pounds>' . "\n";
				$request .= '	<Ounces>' . number_format( ( $weight - floor( $weight ) ) * 16, 2 ) . '</Ounces>' . "\n";
				$request .= '	<Machinable>true</Machinable> ' . "\n";
				$request .= '	<MailType>' . ( 'envelope' === $box_package->type ? 'ENVELOPE' : 'PACKAGE' ) . '</MailType>' . "\n";
				$request .= '	<GXG><POBoxFlag>N</POBoxFlag><GiftFlag>N</GiftFlag></GXG>' . "\n";
				$request .= '	<ValueOfContents>' . number_format( $box_package->value, 2, '.', '' ) . '</ValueOfContents>' . "\n";
				$request .= '	<Country>' . $this->get_country_name( $package['destination']['country'] ) . '</Country>' . "\n";

				$request .= '	<Container>RECTANGULAR</Container>' . "\n";

				$request .= '	<Size>' . $size . '</Size>' . "\n";
				$request .= '	<Width>' . $dimensions[1] . '</Width>' . "\n";
				$request .= '	<Length>' . $dimensions[2] . '</Length>' . "\n";
				$request .= '	<Height>' . $dimensions[0] . '</Height>' . "\n";
				$request .= '	<Girth>' . round( $girth ) . '</Girth>' . "\n";
				$request .= '	<OriginZip>' . str_replace( ' ', '', strtoupper( $this->origin ) ) . '</OriginZip>' . "\n";
				$request .= '	<CommercialFlag>' . ( 'ONLINE' === $this->shippingrates ? 'Y' : 'N' ) . '</CommercialFlag>' . "\n";
				$request .= '</Package>' . "\n";

			}

			$requests[] = $request;
		}

		return $requests;
	}

	/**
	 * Get country name from given country code.
	 *
	 * @param string $code Country's code.
	 *
	 * @return bool|string False if country code is not found or string if there's
	 *                     a match from a given country's code.
	 */
	private function get_country_name( $code ) {
		$countries = apply_filters( 'usps_countries', array(
			'AF' => 'Afghanistan',
			'AX' => 'Aland Island (Finland)',
			'AL' => 'Albania',
			'DZ' => 'Algeria',
			'AS' => 'American Samoa',
			'AD' => 'Andorra',
			'AO' => 'Angola',
			'AI' => 'Anguilla',
			'AG' => 'Antigua and Barbuda',
			'AR' => 'Argentina',
			'AM' => 'Armenia',
			'AW' => 'Aruba',
			'AU' => 'Australia',
			'AT' => 'Austria',
			'AZ' => 'Azerbaijan',
			'BS' => 'Bahamas',
			'BH' => 'Bahrain',
			'BD' => 'Bangladesh',
			'BB' => 'Barbados',
			'BY' => 'Belarus',
			'BE' => 'Belgium',
			'BZ' => 'Belize',
			'BJ' => 'Benin',
			'BM' => 'Bermuda',
			'BT' => 'Bhutan',
			'BO' => 'Bolivia',
			'BQ' => 'Bonaire (Curacao)',
			'BA' => 'Bosnia-Herzegovina',
			'BW' => 'Botswana',
			'BV' => 'Norway',
			'BR' => 'Brazil',
			'IO' => 'Great Britain and Northern Ireland',
			'VG' => 'British Virgin Islands',
			'BN' => 'Brunei Darussalam',
			'BG' => 'Bulgaria',
			'BF' => 'Burkina Faso',
			'BI' => 'Burundi',
			'KH' => 'Cambodia',
			'CM' => 'Cameroon',
			'CA' => 'Canada',
			'CV' => 'Cape Verde',
			'KY' => 'Cayman Islands',
			'CF' => 'Central African Republic',
			'TD' => 'Chad',
			'CL' => 'Chile',
			'CN' => 'China',
			'CX' => 'Christmas Island',
			'CC' => 'Cocos Island (Australia)',
			'CO' => 'Colombia',
			'KM' => 'Comoros',
			'CG' => 'Congo, Republic of the',
			'CD' => 'Congo, Democratic Republic of the',
			'CK' => 'Cook Islands',
			'CR' => 'Costa Rica',
			'HR' => 'Croatia',
			'CU' => 'Cuba',
			'CW' => 'Curacao',
			'CY' => 'Cyprus',
			'CZ' => 'Czech Republic',
			'DK' => 'Denmark',
			'DJ' => 'Djibouti',
			'DM' => 'Dominica',
			'DO' => 'Dominican Republic',
			'EC' => 'Ecuador',
			'EG' => 'Egypt',
			'SV' => 'El Salvador',
			'GQ' => 'Equatorial Guinea',
			'ER' => 'Eritrea',
			'EE' => 'Estonia',
			'ET' => 'Ethiopia',
			'FK' => 'Falkland Islands',
			'FO' => 'Faroe Islands',
			'FJ' => 'Fiji',
			'FI' => 'Finland',
			'FR' => 'France',
			'GF' => 'French Guiana',
			'PF' => 'French Polynesia',
			'TF' => 'France',
			'GA' => 'Gabon',
			'GM' => 'Gambia',
			'GE' => 'Georgia',
			'DE' => 'Germany',
			'GH' => 'Ghana',
			'GI' => 'Gibraltar',
			'GR' => 'Greece',
			'GL' => 'Greenland',
			'GD' => 'Grenada',
			'GP' => 'Guadeloupe',
			/*'GU' => 'Guam', // Guam is considered a country but USPS currently sees it as a state */
			'GT' => 'Guatemala',
			'GG' => 'Guernsey',
			'GN' => 'Guinea',
			'GW' => 'Guinea-Bissau',
			'GY' => 'Guyana',
			'HT' => 'Haiti',
			'HM' => 'Australia',
			'HN' => 'Honduras',
			'HK' => 'Hong Kong',
			'HU' => 'Hungary',
			'IS' => 'Iceland',
			'IN' => 'India',
			'ID' => 'Indonesia',
			'IR' => 'Iran',
			'IQ' => 'Iraq',
			'IE' => 'Ireland',
			'IM' => 'Isle of Man',
			'IL' => 'Israel',
			'IT' => 'Italy',
			'CI' => 'Ivory Coast',
			'JM' => 'Jamaica',
			'JP' => 'Japan',
			'JE' => 'Jersey',
			'JO' => 'Jordan',
			'KZ' => 'Kazakhstan',
			'KE' => 'Kenya',
			'KI' => 'Kiribati',
			'KW' => 'Kuwait',
			'KG' => 'Kyrgyzstan',
			'LA' => 'Laos',
			'LV' => 'Latvia',
			'LB' => 'Lebanon',
			'LS' => 'Lesotho',
			'LR' => 'Liberia',
			'LY' => 'Libya',
			'LI' => 'Liechtenstein',
			'LT' => 'Lithuania',
			'LU' => 'Luxembourg',
			'MO' => 'Macao',
			'MK' => 'Macedonia',
			'MG' => 'Madagascar',
			'MW' => 'Malawi',
			'MY' => 'Malaysia',
			'MV' => 'Maldives',
			'ML' => 'Mali',
			'MT' => 'Malta',
			'MQ' => 'Martinique',
			'MR' => 'Mauritania',
			'MU' => 'Mauritius',
			'YT' => 'Mayotte',
			'MX' => 'Mexico',
			'MD' => 'Moldova',
			'MC' => 'Monaco',
			'MN' => 'Mongolia',
			'ME' => 'Montenegro',
			'MS' => 'Montserrat',
			'MA' => 'Morocco',
			'MZ' => 'Mozambique',
			'MM' => 'Myanmar',
			'NA' => 'Namibia',
			'NR' => 'Nauru',
			'NP' => 'Nepal',
			'NL' => 'Netherlands',
			'NC' => 'New Caledonia',
			'NZ' => 'New Zealand',
			'NI' => 'Nicaragua',
			'NE' => 'Niger',
			'NG' => 'Nigeria',
			'NU' => 'Niue',
			'NF' => 'Norfolk Island',
			'MP' => 'Northern Mariana Islands',
			'KP' => 'North Korea',
			'NO' => 'Norway',
			'OM' => 'Oman',
			'PK' => 'Pakistan',
			'PS' => 'Israel', // Palestinian Territory, Occupied.
			'PA' => 'Panama',
			'PG' => 'Papua New Guinea',
			'PY' => 'Paraguay',
			'PE' => 'Peru',
			'PH' => 'Philippines',
			'PN' => 'Pitcairn Island',
			'PL' => 'Poland',
			'PT' => 'Portugal',
			'PR' => 'Puerto Rico',
			'QA' => 'Qatar',
			'RE' => 'Reunion',
			'RO' => 'Romania',
			'RU' => 'Russia',
			'RW' => 'Rwanda',
			'BL' => 'Saint Barthelemy (Guadeloupe)',
			'SH' => 'Saint Helena',
			'KN' => 'Saint Kitts and Nevis',
			'LC' => 'Saint Lucia',
			'MF' => 'Saint Martin (French) (Guadeloupe)',
			'SX' => 'Sint Maarten',
			'PM' => 'Saint Pierre and Miquelon',
			'VC' => 'Saint Vincent and the Grenadines',
			'SM' => 'San Marino',
			'ST' => 'Sao Tome and Principe',
			'SA' => 'Saudi Arabia',
			'SN' => 'Senegal',
			'RS' => 'Serbia',
			'SC' => 'Seychelles',
			'SL' => 'Sierra Leone',
			'SG' => 'Singapore',
			'SK' => 'Slovakia',
			'SI' => 'Slovenia',
			'SB' => 'Solomon Islands',
			'SO' => 'Somalia',
			'ZA' => 'South Africa',
			'GS' => 'Great Britain and Northern Ireland', // South Georgia and the South Sandwich Islands.
			'KR' => 'South Korea',
			'ES' => 'Spain',
			'LK' => 'Sri Lanka',
			'SD' => 'Sudan',
			'SR' => 'Suriname',
			'SJ' => 'Norway', // Svalbard and Jan Mayen.
			'SZ' => 'Swaziland',
			'SE' => 'Sweden',
			'CH' => 'Switzerland',
			'SY' => 'Syria',
			'TW' => 'Taiwan',
			'TJ' => 'Tajikistan',
			'TZ' => 'Tanzania',
			'TH' => 'Thailand',
			'TL' => 'Timor-Leste',
			'TG' => 'Togo',
			'TK' => 'Tokelau',
			'TO' => 'Tonga',
			'TT' => 'Trinidad and Tobago',
			'TN' => 'Tunisia',
			'TR' => 'Turkey',
			'TM' => 'Turkmenistan',
			'TC' => 'Turks and Caicos Islands',
			'TV' => 'Tuvalu',
			'UG' => 'Uganda',
			'UA' => 'Ukraine',
			'AE' => 'United Arab Emirates',
			'GB' => 'United Kingdom',
			'UM' => 'United States (US) Minor Outlying Islands',
			'VI' => 'United States (US) Virgin Islands',
			'UY' => 'Uruguay',
			'UZ' => 'Uzbekistan',
			'VU' => 'Vanuatu',
			'VA' => 'Vatican City',
			'VE' => 'Venezuela',
			'VN' => 'Vietnam',
			'WF' => 'Wallis and Futuna Islands',
			'EH' => 'Morocco', // Western Sahara.
			'WS' => 'Samoa',
			'YE' => 'Yemen',
			'ZM' => 'Zambia',
			'ZW' => 'Zimbabwe',
		) );

		if ( isset( $countries[ $code ] ) ) {
			return strtoupper( $countries[ $code ] );
		} else {
			return false;
		}
	}

	/**
	 * Calculate Flat Rate Box rate.
	 *
	 * @param array  $package  Package to ship.
	 * @param string $box_type Box type.
	 *
	 * @return array
	 */
	private function calculate_flat_rate_box_rate( $package, $box_type = 'priority' ) {
		$cost = 0;

		if ( ! class_exists( 'WC_Boxpack' ) ) {
			include_once 'box-packer/class-wc-boxpack.php';
		}

		$boxpack  = new WC_Boxpack();
		$domestic = in_array( $package['destination']['country'], $this->domestic ) ? true : false;
		$added    = array();

		// Define boxes.
		foreach ( $this->flat_rate_boxes as $service_code => $box ) {

			if ( $box['box_type'] != $box_type ) {
				continue;
			}

			$domestic_service = substr( $service_code, 0, 1 ) == 'd' ? true : false;

			if ( $domestic && $domestic_service || ! $domestic && ! $domestic_service ) {
				$newbox = $boxpack->add_box( $box['length'], $box['width'], $box['height'] );

				$newbox->set_max_weight( $box['weight'] );
				$newbox->set_id( $service_code );

				if ( isset( $box['volume'] ) && method_exists( $newbox, 'set_volume' ) ) {
					$newbox->set_volume( $box['volume'] );
				}

				if ( isset( $box['type'] ) && method_exists( $newbox, 'set_type' ) ) {
					$newbox->set_type( $box['type'] );
				}

				$added[] = $service_code . ' - ' . $box['name'] . ' (' . $box['length'] . 'x' . $box['width'] . 'x' . $box['height'] . ')';
			}
		}

		$this->debug( 'Calculating USPS Flat Rate with boxes: ' . implode( ', ', $added ) );

		// Add items.
		foreach ( $package['contents'] as $item_id => $values ) {

			if ( ! $values['data']->needs_shipping() ) {
				continue;
			}

			if ( $values['data']->get_length() && $values['data']->get_height() && $values['data']->get_width() ) {
				$dimensions = array( wc_get_dimension( $values['data']->get_length(), 'in' ), wc_get_dimension( $values['data']->get_height(), 'in' ), wc_get_dimension( $values['data']->get_width(), 'in' ) );
			} else {
				$this->debug( sprintf( __( 'Product #%d is missing dimensions! Using 1x1x1.', 'woocommerce-shipping-usps' ), $item_id ) );
				$dimensions = array( 1, 1, 1 );
			}

			if ( $values['data']->get_weight() ) {
				$weight = wc_get_weight( $values['data']->get_weight(), 'lbs' );
			} else {
				$this->debug( sprintf( __( 'Product #%d is missing weight! Using 1lb.', 'woocommerce-shipping-usps' ), $item_id ) );
				$weight = 1;
			}

			for ( $i = 0; $i < $values['quantity']; $i ++ ) {
				$boxpack->add_item(
					$dimensions[2],
					$dimensions[1],
					$dimensions[0],
					$weight,
					$values['data']->get_price()
				);
			}
		}

		// Pack it.
		$boxpack->pack();

		// Get packages.
		$flat_packages   = $boxpack->get_packages();
		$meta_data       = array();
		$package_counter = 1;

		if ( $flat_packages ) {

			foreach ( $flat_packages as $flat_package ) {

				if ( isset( $this->flat_rate_boxes[ $flat_package->id ] ) ) {

					$this->debug( 'Packed ' . $flat_package->id . ' - ' . $this->flat_rate_boxes[ $flat_package->id ]['name'] );

					// The meta data contains the total of packed items.
					$meta_data[ 'Package ' . $package_counter++ ] = $this->get_rate_meta_data( array(
						'length' => $flat_package->length,
						'width'  => $flat_package->width,
						'height' => $flat_package->height,
						'weight' => $flat_package->weight,
						'qty'    => 1,
						'name'   => $this->flat_rate_boxes[ $flat_package->id ]['name'],
					) );

					// Get pricing.
					$box_pricing  = 'ONLINE' === $this->shippingrates && isset( $this->flat_rate_pricing[ $flat_package->id ]['online'] ) ? $this->flat_rate_pricing[ $flat_package->id ]['online'] : $this->flat_rate_pricing[ $flat_package->id ]['retail'];

					if ( is_array( $box_pricing ) ) {
						if ( isset( $box_pricing[ $package['destination']['country'] ] ) ) {
							$box_cost = $box_pricing[ $package['destination']['country'] ];
						} else {
							$box_cost = $box_pricing['*'];
						}
					} else {
						$box_cost = $box_pricing;
					}

					// Fees.
					if ( ! empty( $this->flat_rate_fee ) ) {
						$sym = substr( $this->flat_rate_fee, 0, 1 );
						$fee = '-' === $sym ? substr( $this->flat_rate_fee, 1 ) : $this->flat_rate_fee;

						if ( strstr( $fee, '%' ) ) {
							$fee = str_replace( '%', '', $fee );

							if ( '-' === $sym ) {
								$box_cost = $box_cost - ( $box_cost * ( floatval( $fee ) / 100 ) );
							} else {
								$box_cost = $box_cost + ( $box_cost * ( floatval( $fee ) / 100 ) );
							}
						} else {
							if ( '-' === $sym ) {
								$box_cost = $box_cost - $fee;
							} else {
								$box_cost += $fee;
							}
						}

						if ( $box_cost < 0 ) {
							$box_cost = 0;
						}
					}

					$cost += $box_cost;

				} else {
					return; // No match.
				}
			}

			if ( 'express' === $box_type ) {
				$label = $this->get_option( 'flat_rate_express_title', ( $domestic ? '' : 'International ' ) . 'Priority Mail Express Flat Rate&#0174;' );
			} else {
				$label = $this->get_option( 'flat_rate_priority_title', ( $domestic ? '' : 'International ' ) . 'Priority Mail Flat Rate&#0174;' );
			}

			return array(
				'id'        => $this->id . ':flat_rate_box_' . $box_type,
				'label'     => $label,
				'cost'      => $cost,
				'sort'      => ( 'express' === $box_type ? -1 : -2 ),
				'meta_data' => $meta_data,
			);
		}
	}

	/**
	 * Calculate shipping cost using standard services.
	 *
	 * @since 4.4.7
	 *
	 * @param mixed $package Package to ship.
	 */
	private function calculate_standard_service( $package ) {
		$package_requests = $this->get_package_requests( $package );
		$domestic         = in_array( $package['destination']['country'], $this->domestic ) ? true : false;

		$response = null;
		if ( $package_requests ) {
			$response = $this->request_usps_standard_service( $package, $package_requests, $domestic );

			if ( $response ) {
				$this->parse_rates_from_usps_standard_service_response( $response, $domestic, $package );
			}
		}

		// Ensure rates were found for all packages.
		if ( $this->found_rates ) {

			// Cache the response for one week if response contains rates.
			if ( $this->request_transient && $response ) {
				$transient_expiration = apply_filters( 'woocommerce_shipping_usps_transient_expiration', DAY_IN_SECONDS * 7 );
				set_transient( $this->request_transient, $response, $transient_expiration );
			}

			foreach ( $this->found_rates as $key => $value ) {
				if ( $value['packages'] < sizeof( $package_requests ) ) {
					$this->debug( "Unsetting {$key} - too few packages." );
					unset( $this->found_rates[ $key ] );
				}
				if ( $this->unpacked_item_costs && ! empty( $this->found_rates[ $key ] ) ) {
					$this->debug( sprintf( __( 'Adding unpacked item costs to rate %s', 'woocommerce-shipping-usps' ), $key ) );
					$this->found_rates[ $key ]['cost'] += $this->unpacked_item_costs;
				}
			}
		}
	}

	/**
	 * Request standard service through USPS API.
	 *
	 * @since 4.4.7
	 *
	 * @param array $package          Raw package.
	 * @param array $package_requests Package params for the request.
	 * @param bool  $domestic         Whether domestic or not.
	 */
	private function request_usps_standard_service( $package, $package_requests, $domestic ) {
		$api     = $domestic ? 'RateV4' : 'IntlRateV2';
		$request = $this->build_usps_standard_service_request( $package_requests, $domestic );

		// Need to save the transient's name because `set_transient` is called
		// from another method.
		$this->request_transient = 'usps_quote_' . md5( $request );
		$cached_response         = get_transient( $this->request_transient );

		$this->debug( 'USPS REQUEST: <pre>' . print_r( htmlspecialchars( $request ), true ) . '</pre>' );

		// If there's a cached response, return it.
		if ( false !== $cached_response ) {
			$this->debug( sprintf(
				'USPS CACHED RESPONSE: <pre style="height: 200px; overflow:auto;">%s</pre>',
				print_r( htmlspecialchars( $cached_response ), true )
			) );

			return $cached_response;
		}

		// Request to USPS.
		$response = wp_remote_post(
			$this->endpoint,
			array(
				'timeout' => 70,
				'body'    => apply_filters(
					'woocommerce_shipping_usps_request',
					$request,
					$api,
					$package_requests,
					$package
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			$this->debug( 'USPS REQUEST FAILED', 'error' );
			return false;
		}

		$response = $response['body'];

		$this->debug( sprintf(
			'USPS RESPONSE: <pre style="height: 200px; overflow:auto;">%s</pre>',
			print_r( htmlspecialchars( $response ), true )
		) );

		return $response;
	}

	/**
	 * Build XML request for USPS standard service.
	 *
	 * @since 4.4.7
	 *
	 * @param array $package_requests Package params for the request.
	 * @param bool  $domestic         Whether domestic or not.
	 */
	private function build_usps_standard_service_request( $package_requests, $domestic ) {
		$api = $domestic ? 'RateV4' : 'IntlRateV2';

		$request  = '<' . $api . 'Request USERID="' . $this->user_id . '">' . "\n";
		$request .= '<Revision>2</Revision>' . "\n";

		foreach ( $package_requests as $key => $package_request ) {
			$request .= $package_request;
		}

		$request .= '</' . $api . 'Request>' . "\n";
		$request = 'API=' . $api . '&XML=' . str_replace( array( "\n", "\r" ), '', $request );

		return $request;
	}

	/**
	 * Parse response from USPS standard service request.
	 *
	 * @since 4.4.7
	 * @version 4.4.8
	 *
	 * @param mixed $response Body from WP HTTP API.
	 * @param bool  $domestic Whether domestic or not.
	 * @param array $package  Package to ship.
	 */
	private function parse_rates_from_usps_standard_service_response( $response, $domestic, $package ) {
		if ( ! ( $usps_packages = $this->get_parsed_xml( $response ) ) ) {
			$this->debug( 'Failed loading XML', 'error' );
		}

		if ( ! is_object( $usps_packages ) && ! is_a( $usps_packages, 'SimpleXMLElement' ) ) {
			$this->debug( 'Invalid XML response format', 'error' );
		}

		// No rates, return.
		if ( empty( $usps_packages ) ) {
			$this->debug( 'Invalid request; no rates returned', 'error' );
			return;
		}

		foreach ( $usps_packages as $usps_package ) {
			if ( ! $usps_package || ! is_object( $usps_package ) ) {
				continue;
			}
			// Get package data.
			$data_parts = explode( ':', $usps_package->attributes()->ID );
			if ( count( $data_parts ) < 6 ) {
				continue;
			}

			list( $package_item_id, $cart_item_qty, $package_length, $package_width, $package_height, $package_weight ) = $data_parts;
			$quotes = $usps_package->children();

			// Diplay quotes nicely in debug notice.
			$this->debug_usps_standard_service_quotes( $quotes, $domestic );

			// Loop defined services.
			foreach ( $this->services as $service => $values ) {
				if ( $domestic && strpos( $service, 'D_' ) !== 0 || ! $domestic && strpos( $service, 'I_' ) !== 0 ) {
					continue;
				}

				$rate_code      = (string) $service;
				$rate_id        = $this->id . ':' . $rate_code;
				$rate_name      = (string) $values['name'];
				$rate_cost      = null;
				$svc_commitment = null;

				// Loop through rate quotes returned from USPS.
				foreach ( $quotes as $quote ) {
					$quoted_service_name = sanitize_title( strip_tags( htmlspecialchars_decode( (string) $quote->{'MailService'} ) ) );

					$code = strval( $quote->attributes()->CLASSID );
					$cost = null;

					if ( ! $domestic ) {
						$code = strval( $quote->attributes()->ID );
					}

					if ( '' !== $code && in_array( $code, array_keys( $values['services'] ) ) ) {
						$cost = (float) $quote->{'Rate'} * $cart_item_qty;

						if ( ! empty( $quote->{'CommercialRate'} ) ) {
							$cost = (float) $quote->{'CommercialRate'} * $cart_item_qty;
						}

						if ( ! $domestic ) {
							$cost = (float) $quote->{'Postage'} * $cart_item_qty;

							if ( ! empty( $quote->{'CommercialPostage'} ) ) {
								$cost = (float) $quote->{'CommercialPostage'} * $cart_item_qty;
							}
						}

						// Process sub sub services.
						if ( '0' == $code ) {
							if ( array_key_exists( $quoted_service_name, $this->custom_services[ $rate_code ][ $code ] ) ) {
								// Enabled check.
								if ( ! empty( $this->custom_services[ $rate_code ][ $code ][ $quoted_service_name ] ) && ( true != $this->custom_services[ $rate_code ][ $code ][ $quoted_service_name ]['enabled'] || empty( $this->custom_services[ $rate_code ][ $code ][ $quoted_service_name ]['enabled'] ) ) ) {
									continue;
								}

								// Cost adjustment %.
								if ( ! empty( $this->custom_services[ $rate_code ][ $code ][ $quoted_service_name ]['adjustment_percent'] ) ) {
									$cost = round( $cost + ( $cost * ( floatval( $this->custom_services[ $rate_code ][ $code ][ $quoted_service_name ]['adjustment_percent'] ) / 100 ) ), wc_get_price_decimals() );
								}

								// Cost adjustment.
								if ( ! empty( $this->custom_services[ $rate_code ][ $code ][ $quoted_service_name ]['adjustment'] ) ) {
									$cost = round( $cost + floatval( $this->custom_services[ $rate_code ][ $code ][ $quoted_service_name ]['adjustment'] ), wc_get_price_decimals() );
								}
							}
						} else {
							// Enabled check.
							if ( ! empty( $this->custom_services[ $rate_code ][ $code ] ) && ( true != $this->custom_services[ $rate_code ][ $code ]['enabled'] || empty( $this->custom_services[ $rate_code ][ $code ]['enabled'] ) ) ) {

								continue;
							}

							// Cost adjustment %.
							if ( ! empty( $this->custom_services[ $rate_code ][ $code ]['adjustment_percent'] ) ) {
								$cost = round( $cost + ( $cost * ( floatval( $this->custom_services[ $rate_code ][ $code ]['adjustment_percent'] ) / 100 ) ), wc_get_price_decimals() );
							}

							// Cost adjustment.
							if ( ! empty( $this->custom_services[ $rate_code ][ $code ]['adjustment'] ) ) {
								$cost = round( $cost + floatval( $this->custom_services[ $rate_code ][ $code ]['adjustment'] ), wc_get_price_decimals() );
							}
						}

						if ( $domestic ) {
							switch ( $code ) {
								// Handle first class - there are multiple d0 rates and we need to handle size retrictions because the API doesn't do this for us!
								case '0' :
									$service_name = strip_tags( htmlspecialchars_decode( (string) $quote->{'MailService'} ) );

									if ( apply_filters( 'usps_disable_first_class_rate_' . sanitize_title( $service_name ), false ) ) {
										continue 2;
									}
								break;
								// Media mail has restrictions - check here.
								case '6' :
									if ( sizeof( $this->mediamail_restriction ) > 0 ) {
										$invalid = false;

										foreach ( $package['contents'] as $package_item ) {
											if ( ! in_array( $package_item['data']->get_shipping_class_id(), $this->mediamail_restriction ) ) {
												$invalid = true;
											}
										}

										if ( $invalid ) {
											$this->debug( 'Skipping media mail' );
											continue 2;
										}
									}
								break;
							}
						}

						if ( $domestic && $package_length && $package_width && $package_height ) {
							switch ( $code ) {
								// Regional rate boxes need additonal checks to deal with USPS's complex API.
								case '47' :
									if ( ( $package_length > 10 || $package_width > 7 || $package_height > 4.75 ) && ( $package_length > 12.8125 || $package_width > 10.9375 || $package_height > 2.375 ) ) {
										continue 2;
									} else {
										// Valid.
										break;
									}
								break;
								case '49' :
									if ( ( $package_length > 12 || $package_width > 10.25 || $package_height > 5 ) && ( $package_length > 15.875 || $package_width > 14.375 || $package_height > 2.875 ) ) {
										continue 2;
									} else {
										// Valid.
										break;
									}
								break;
								case '58' :
									if ( $package_length > 14.75 || $package_width > 11.75 || $package_height > 11.5 ) {
										continue 2;
									} else {
										// Valid.
										break;
									}
								break;
								// Handle first class - there are multiple d0 rates and we need to handle size retrictions because the API doesn't do this for us!
								//
								// See https://www.usps.com/ship/preparing-domestic-shipments.htm.
								case '0' :
									$service_name = strip_tags( htmlspecialchars_decode( (string) $quote->{'MailService'} ) );

									if ( strstr( $service_name, 'Postcards' ) ) {

										if ( $package_length > 6 || $package_length < 5 ) {
											continue 2;
										}
										if ( $package_width > 4.25 || $package_width < 3.5 ) {
											continue 2;
										}
										if ( $package_height > 0.016 || $package_height < 0.007 ) {
											continue 2;
										}
									} elseif ( strstr( $service_name, 'Large Envelope' ) ) {
										if ( ( $package_length > 11.5 && $package_length < 15 ) || ( $package_width > 6 && $package_width < 12 ) || ( $package_height > 0.25 || $package_width < 0.75 ) ) {
											break;
										}

										if ( $package_length > 15 || $package_length < 11.5 ) {
											continue 2;
										}
										if ( $package_width > 12 || $package_width < 6 ) {
											continue 2;
										}
										if ( $package_height > 0.75 || $package_height < 0.25 ) {
											continue 2;
										}
									} elseif ( strstr( $service_name, 'Letter' ) ) {
										if ( $package_length > 11.5 || $package_length < 5 ) {
											continue 2;
										}
										if ( $package_width > 6.125 || $package_width < 3.5 ) {
											continue 2;
										}
										if ( $package_height > 0.25 || $package_height < 0.007 ) {
											continue 2;
										}
									} elseif ( strstr( $service_name, 'Parcel' ) ) {
										$girth = ( $package_width + $package_height ) * 2;

										if ( $girth + $package_length > 108 ) {
											continue 2;
										}
									} elseif ( strstr( $service_name, 'Package' ) ) {
										$girth = ( $package_width + $package_height ) * 2;

										if ( $girth + $package_length > 108 ) {
											continue 2;
										}
									} else {
										continue 2;
									}
								break;
							}
						}

						if ( is_null( $rate_cost ) ) {
							$rate_cost      = $cost;
							$svc_commitment = $quote->SvcCommitments;
						} elseif ( $cost < $rate_cost ) {
							$rate_cost      = $cost;
							$svc_commitment = $quote->SvcCommitments;
						}
					}
				}

				if ( $rate_cost ) {
					if ( ! empty( $svc_commitment ) && strstr( $svc_commitment, 'days' ) ) {
						$rate_name .= ' (' . current( explode( 'days', $svc_commitment ) ) . ' days)';
					}

					$meta_data = $this->get_rate_meta_data( array(
						'length' => $package_length,
						'width'  => $package_width,
						'height' => $package_height,
						'weight' => $package_weight,
						'qty'    => 'per_item' === $this->packing_method ? $cart_item_qty : 0,
					) );

					$this->prepare_rate( $rate_code, $rate_id, $rate_name, $rate_cost, $meta_data );
				}
			}
		}
	}

	/**
	 * Debug found quotes in USPS standard service.
	 *
	 * @param array $quotes   Found quotes.
	 * @param bool  $domestic Whether domestic or not.
	 */
	private function debug_usps_standard_service_quotes( $quotes, $domestic ) {
		$found_quotes = array();

		foreach ( $quotes as $quote ) {
			if ( $domestic ) {
				$code = strval( $quote->attributes()->CLASSID );
				$name = strip_tags( htmlspecialchars_decode( (string) $quote->{'MailService'} ) );
			} else {
				$code = strval( $quote->attributes()->ID );
				$name = strip_tags( htmlspecialchars_decode( (string) $quote->{'SvcDescription'} ) );
			}

			if ( $name && $code ) {
				$found_quotes[ $code ] = $name;
			} elseif ( $name ) {
				// @todo Remove $code here? Because if reached here it's empty or evaluate to `false`.
				$found_quotes[ $code . '-' . sanitize_title( $name ) ] = $name;
			}
		}

		if ( $found_quotes ) {
			ksort( $found_quotes );
			$found_quotes_html = '';
			foreach ( $found_quotes as $code => $name ) {
				if ( ! strstr( $name, 'Flat Rate' ) ) {
					$found_quotes_html .= '<li>' . $code . ' - ' . $name . '</li>';
				}
			}
			$this->debug( 'The following quotes were returned by USPS: <ul>' . $found_quotes_html . '</ul> If any of these do not display, they may not be enabled in USPS settings.', 'success' );
		}
	}

	/**
	 * Log debug by printing it as notice.
	 *
	 * @param string $message Debug message.
	 * @param string $type    Notice type.
	 */
	public function debug( $message, $type = 'notice' ) {
		if ( $this->debug && ( is_cart() || is_checkout() ) ) {
			wc_add_notice( $message, $type );
		}
	}
}
