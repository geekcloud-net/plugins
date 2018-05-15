<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class WPLA_Shipping_Method extends WC_Shipping_Method {

    /**
     * Constructor
     */
    public function __construct() {
        $this->id                 = 'wpla_shipping';
        $this->title              = __( 'FBA Shipping Options', 'wpla' );   // Displays on the main shipping settings page
        $this->method_title       = __( 'FBA Shipping', 'wpla' );           // Displays on the shipping settings tab
        $this->method_description = __( 'Customize how the FBA shipping options are displayed to a customer when the cart contains only items which are fulfilled by Amazon.', 'wpla' );

        $this->init();
    }

    /**
     * Initialize free shipping.
     */
    public function init() {

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        // Define user set variables
        // $this->enabled               = $this->get_option( 'enabled' );
        $this->discount_mode            = $this->get_option( 'discount_mode' );
        $this->show_estimated_arrival   = $this->get_option( 'show_estimated_arrival' );
	    $this->standard_fixed_fee       = $this->get_option( 'standard_fixed_fee' );
	    $this->standard_percent_fee     = $this->get_option( 'standard_percent_fee' );
	    $this->expedited_fixed_fee      = $this->get_option( 'expedited_fixed_fee' );
	    $this->expedited_percent_fee    = $this->get_option( 'expedited_percent_fee' );
	    $this->priority_fixed_fee       = $this->get_option( 'priority_fixed_fee' );
	    $this->priority_percent_fee     = $this->get_option( 'priority_percent_fee' );
        $this->enabled                  = 'yes';

        // Actions
        add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
    }

    /**
     * Initialize Gateway Settings Form Fields.
     */
    public function init_form_fields() {
        $this->form_fields = array(
            // 'enabled' => array(
            //     'title'         => __( 'Enable/Disable', 'wpla' ),
            //     'type'          => 'checkbox',
            //     'label'         => __( 'Enable Free Shipping', 'wpla' ),
            //     'default'       => 'no'
            // ),
            'discount_mode' => array(
                'title'         => __( 'Discount mode', 'wpla' ),
                'type'          => 'select',
                'default'       => 'none',
                'class'         => 'wc-enhanced-select',
                'options'       => array(
                    'none'          => __( 'No discount', 'wpla' ),
                    'free_standard' => __( 'Free Standard shipping', 'wpla' )
                )
            ),
            'show_estimated_arrival' => array(
                'title'         => __( 'Show estimated arrival time', 'wpla' ),
                'type'          => 'select',
                'default'       => 'no',
                'class'         => 'wc-enhanced-select',
                'options'       => array(
                    'no'        => __( 'No', 'wpla' ),
                    'yes'       => __( 'Yes', 'wpla' ),
                )
            ),
            'shipping_fees_table'     => array(
	            'type'          => 'shipping_fees',
	            'title'         => __( 'Add-on Shipping Fees', 'wpla' ),
            ),
        );
    }

	/**
	 * Generate Shipping Fees HTML.
	 *
	 * @param  mixed $key
	 * @param  mixed $data
	 * @return string
	 */
	public function generate_shipping_fees_html( $key, $data ) {
		$field_key              = $this->get_field_key( $key );
		$standard_fixed_key     = $this->get_field_key( 'standard_fixed' );
		$standard_variable_key  = $this->get_field_key( 'standard_variable' );
		$expedited_fixed_key    = $this->get_field_key( 'expedited_fixed' );
		$expedited_variable_key = $this->get_field_key( 'expedited_variable' );
		$priority_fixed_key     = $this->get_field_key( 'priority_fixed' );
		$priority_variable_key  = $this->get_field_key( 'priority_variable' );
		$defaults  = array(
			'title'             => '',
			'disabled'          => false,
			'class'             => '',
			'css'               => '',
			'placeholder'       => '',
			'type'              => 'text',
			'desc_tip'          => false,
			'description'       => '',
			'custom_attributes' => array(),
		);

		$data = wp_parse_args( $data, $defaults );

		ob_start();
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></label>
				<?php echo $this->get_tooltip_html( $data ); ?>
			</th>
			<td class="forminp">
				<table class="widefat" style="width:400px;">
					<thead>
					<tr>
						<th>&nbsp;</th>
						<th><?php _e( 'Fixed Fee', 'wpla' ); ?></th>
						<th><?php _e( 'Variable Fee (%)', 'wpla' ); ?></th>
					</tr>
					</thead>
					<tbody>
					<tr>
						<td><?php _e( 'Standard', 'wpla' ); ?></td>
						<td><input type="text" size="5" name="<?php echo $standard_fixed_key; ?>" placeholder="0" value="<?php echo esc_attr( $this->get_option( 'standard_fixed_fee' ) ); ?>" /></td>
						<td><input type="text" size="5" name="<?php echo $standard_variable_key; ?>" placeholder="0" value="<?php echo esc_attr( $this->get_option( 'standard_variable_fee' ) ); ?>" /></td>
					</tr>
					<tr>
						<td><?php _e( 'Expedited', 'wpla' ); ?></td>
						<td><input type="text" size="5" name="<?php echo $expedited_fixed_key; ?>" placeholder="0" value="<?php echo esc_attr( $this->get_option( 'expedited_fixed_fee' ) ); ?>" /></td>
						<td><input type="text" size="5" name="<?php echo $expedited_variable_key; ?>" placeholder="0" value="<?php echo esc_attr( $this->get_option( 'expedited_variable_fee' ) ); ?>" /></td>
					</tr>
					<tr>
						<td><?php _e( 'Priority', 'wpla' ); ?></td>
						<td><input type="text" size="5" name="<?php echo $priority_fixed_key; ?>" placeholder="0" value="<?php echo esc_attr( $this->get_option( 'priority_fixed_fee' ) ); ?>" /></td>
						<td><input type="text" size="5" name="<?php echo $priority_variable_key; ?>" placeholder="0" value="<?php echo esc_attr( $this->get_option( 'priority_variable_fee' ) ); ?>" /></td>
					</tr>
					</tbody>
				</table>
			</td>
		</tr>
		<?php

		return ob_get_clean();
	}

	public function process_admin_options() {
		parent::process_admin_options();

		$standard_fixed_key     = $this->get_field_key( 'standard_fixed' );
		$standard_variable_key  = $this->get_field_key( 'standard_variable' );
		$expedited_fixed_key    = $this->get_field_key( 'expedited_fixed' );
		$expedited_variable_key = $this->get_field_key( 'expedited_variable' );
		$priority_fixed_key     = $this->get_field_key( 'priority_fixed' );
		$priority_variable_key  = $this->get_field_key( 'priority_variable' );

		$this->settings['standard_fixed_fee']       = @$_POST[ $standard_fixed_key ];
		$this->settings['standard_variable_fee']    = @$_POST[ $standard_variable_key ];
		$this->settings['expedited_fixed_fee']      = @$_POST[ $expedited_fixed_key ];
		$this->settings['expedited_variable_fee']   = @$_POST[ $expedited_variable_key ];
		$this->settings['priority_fixed_fee']       = @$_POST[ $priority_fixed_key ];
		$this->settings['priority_variable_fee']    = @$_POST[ $priority_variable_key ];

		return update_option( $this->get_option_key(), $this->settings );
	}

    /**
     * calculate_shipping function.
     *
     * @param mixed $package
     */
    public function calculate_shipping( $package = array() ) {
	    // Set up a dummy address and city when calculating shipping from the cart page.
	    // Fixes issue with guests and customers with no stored shipping address.
	    // https://secure.helpscout.net/conversation/210253820/8651/?folderId=81714
	    //
	    // 2) Also fixes an issue when using the Amazon Payments gateway wherein
	    // the address key in the returned address by the API is empty
	    // https://secure.helpscout.net/conversation/214905309/8814/?folderId=856813
	    if ( !empty( $_REQUEST['calc_shipping'] ) || is_cart() || 'true' === @$_REQUEST['amazon_payments_advanced'] ) {
		    if ( empty( $package['destination']['address'] ) ) {
			    $package['destination']['address'] = '1 SW Dr.';
		    }

		    if ( empty( $package['destination']['city'] ) ) {
			    $package['destination']['city'] = 'SomeCity';
		    }
	    }

        if ( !$this->is_address_valid( $package['destination'] ) ) {
            WPLA()->logger->info("WPLASO: calculate_shipping() - destination address seems to be invalid...");
            return;
        }

        $products = array();

        foreach ( $package['contents'] as $product ) {
            $products[] = array(
                'sku'   => $product['data']->get_sku(),
                'qty'   => $product['quantity']
            );
        }
        WPLA()->logger->info("WPLASO: calculate_shipping() - products: ".print_r($products,1));

        $address    = array(
            'name'      => 'Customer',
            'street'    => $package['destination']['address'],
            'city'      => $package['destination']['city'],
            'state'     => $package['destination']['state'],
            'postcode'  => $package['destination']['postcode'],
            'country'   => $package['destination']['country']
        );

        $default_account_id = get_option( 'wpla_default_account_id', 1 );
        $api = new WPLA_AmazonAPI( $default_account_id );
        $result = $api->getFulfillmentPreview( $products, $address );

        $shipping_discount = 0;
        if ( isset( $result->success ) && $result->success ) {
            foreach ( $result->previews as $preview ) {
                WPLA()->logger->info("WPLASO: calculate_shipping() - preview object: ".print_r($preview,1));
                if ( ! $preview->IsFulfillable ) continue;      // not working?
                if ( ! $preview->TotalShippingFee ) continue;   // this works!

                // build label (added filter #14710)
                $method_label = apply_filters( 'wpla_shipping_method_label', $preview->ShippingSpeedCategory, $preview, $this, $package );

                if ( $this->show_estimated_arrival == 'yes' ) {
                    $estimated_arrival  = self::time_diff_in_days( strtotime( $preview->EarliestArrivalDate ) ) . ' - ';
                    $estimated_arrival .= self::time_diff_in_days( strtotime( $preview->LatestArrivalDate   ) ) . ' days';
                    $method_label .= ' (' . $estimated_arrival . ')';
                }

                if ( $this->discount_mode == 'free_standard' ) {
                    if ( 'Standard' == $preview->ShippingSpeedCategory ) {
                        $shipping_discount = $preview->TotalShippingFee;
                    }
                }

	            $shipping_fee = $this->apply_addon_fee( $preview->TotalShippingFee, $preview->ShippingSpeedCategory );

                $this->add_rate( array(
                    'id'    => $preview->ShippingSpeedCategory,
                    'label' => $method_label,
                    'cost'  => $shipping_fee - $shipping_discount
                ) );
            }
        } else {
            WPLA()->logger->error("WPLASO: calculate_shipping() - getFulfillmentPreview FAILED! result: ".print_r($result,1));
            $api->dblogger->updateLog( array(
                'result'    => $result->ErrorMessage,
                'success'   => 'Error'
            ));
        }
    }

	public function apply_addon_fee( $shipping_fee, $service_category ) {
		switch( strtolower( $service_category ) ) {
			case 'standard':
				$fixed      = $this->get_option( 'standard_fixed_fee', 0 );
				$percent    = $this->get_option( 'standard_variable_fee', 0 );
				break;

			case 'expedited':
				$fixed      = $this->get_option( 'expedited_fixed_fee', 0 );
				$percent    = $this->get_option( 'expedited_variable_fee', 0 );
				break;

			case 'priority':
				$fixed      = $this->get_option( 'priority_fixed_fee', 0 );
				$percent    = $this->get_option( 'priority_variable_fee', 0 );
				break;

			default:
				$fixed      = 0;
				$percent    = 0;
		}

		$addon_fee = 0;

		if ( ! empty( $fixed ) ) {
			$addon_fee += $fixed;
		}

		if ( ! empty( $percent ) ) {
			$addon = $shipping_fee * ( floatval( $percent ) / 100 );
			$addon_fee += $addon;
		}

		return $shipping_fee + $addon_fee;
	}

    // calculate number of days to given timestamp $ts
    static function time_diff_in_days( $ts ) {

        $diff = (int) abs( $ts - time() );

        $days = round( $diff / DAY_IN_SECONDS );
        if ( $days <= 1 ) $days = 1;

        return $days;
    }

    /**
     * Make sure the address, city, state, postcode and country are set
     * @param array $address
     * @return bool
     */
    public function is_address_valid( $address ) {
        $is_valid   = true;
        $keys       = array( 'address', 'city', 'state', 'postcode', 'country' );

        foreach ( $keys as $key ) {
            if ( empty( $address[ $key ] ) ) {
                $is_valid = false;
                WPLA()->logger->info("WPLASO: is_address_valid() - address is missing '$key' !");
                break;
            }
        }

        return $is_valid;
    }
}
