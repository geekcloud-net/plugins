<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * WC_Shipping_UPS class deprecated.
 *
 * This class serves only WC < 2.6 and will be removed by WC 2.8
 * @extends WC_Shipping_Method
 */
class WC_Shipping_UPS extends WC_Shipping_Method {
	const SIGNATURE_FEE       = 4.25;
	const ADULT_SIGNATURE_FEE = 5.25;

	private $endpoint = 'https://www.ups.com/ups.app/xml/Rate';

	private $pickup_code = array(
		'01' => "Daily Pickup",
		'03' => "Customer Counter",
		'06' => "One Time Pickup",
		'07' => "On Call Air",
		'19' => "Letter Center",
		'20' => "Air Service Center",
	);

	private $services = array(
		// Domestic
		"12" => "3 Day Select",
		"03" => "Ground",
		"02" => "2nd Day Air",
		"59" => "2nd Day Air AM",
		"01" => "Next Day Air",
		"13" => "Next Day Air Saver",
		"14" => "Next Day Air Early AM",

		// International
		"11" => "Standard",
		"07" => "Worldwide Express",
		"54" => "Worldwide Express Plus",
		"08" => "Worldwide Expedited Standard",
		"65" => "Worldwide Saver",

	);

	private $eu_array = array('BE','BG','CZ','DK','DE','EE','IE','GR','ES','FR','HR','IT','CY','LV','LT','LU','HU','MT','NL','AT','PT','RO','SI','SK','FI','GB');

	// Shipments Originating in the European Union
	private $euservices = array(
		"07" => "UPS Express",
		"08" => "UPS ExpeditedSM",
		"11" => "UPS Standard",
		"54" => "UPS Express PlusSM",
		"65" => "UPS Saver",
	);

	private $polandservices = array(
		"07" => "UPS Express",
		"08" => "UPS ExpeditedSM",
		"11" => "UPS Standard",
		"54" => "UPS Express PlusSM",
		"65" => "UPS Saver",
		"82" => "UPS Today Standard",
		"83" => "UPS Today Dedicated Courier",
		"84" => "UPS Today Intercity",
		"85" => "UPS Today Express",
		"86" => "UPS Today Express Saver",
	);

	// Packaging not offered at this time: 00 = UNKNOWN, 30 = Pallet, 04 = Pak
	// Code 21 = Express box is valid code, but doesn't have dimensions
	// References:
	// http://www.ups.com/content/us/en/resources/ship/packaging/supplies/envelopes.html
	// http://www.ups.com/content/us/en/resources/ship/packaging/supplies/paks.html
	// http://www.ups.com/content/us/en/resources/ship/packaging/supplies/boxes.html
	// https://www.ups.com/content/us/en/shipping/create/package_type_help.html
	private $packaging = array(
		"01" => array(
					"name" 	 => "UPS Letter",
					"length" => "12.5",
					"width"  => "9.5",
					"height" => "0.25",
					"weight" => "0.5"
				),
		"03" => array(
					"name" 	 => "Tube",
					"length" => "38",
					"width"  => "6",
					"height" => "6",
					"weight" => "100" // no limit, but use 100
				),
		"24" => array(
					"name" 	 => "25KG Box",
					"length" => "19.375",
					"width"  => "17.375",
					"height" => "14",
					"weight" => "55.1156"
				),
		"25" => array(
					"name" 	 => "10KG Box",
					"length" => "16.5",
					"width"  => "13.25",
					"height" => "10.75",
					"weight" => "22.0462"
				),
		"2a" => array(
					"name" 	 => "Small Express Box",
					"length" => "13",
					"width"  => "11",
					"height" => "2",
					"weight" => "100" // no limit, but use 100
				),
		"2b" => array(
					"name" 	 => "Medium Express Box",
					"length" => "15",
					"width"  => "11",
					"height" => "3",
					"weight" => "100" // no limit, but use 100
				),
		"2c" => array(
					"name" 	 => "Large Express Box",
					"length" => "18",
					"width"  => "13",
					"height" => "3",
					"weight" => "30"
				)
	);

	private $packaging_select = array(
		"01" => "UPS Letter",
		"03" => "Tube",
		"24" => "25KG Box",
		"25" => "10KG Box",
		"2a" => "Small Express Box",
		"2b" => "Medium Express Box",
		"2c" => "Large Express Box",
	);

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		$this->id                 = 'ups';
		$this->method_title       = __( 'UPS', 'woocommerce-shipping-ups' );
		$this->method_description = __( 'The <strong>UPS</strong> extension obtains rates dynamically from the UPS API during cart/checkout.', 'woocommerce-shipping-ups' );
		$this->init();
	}

	/**
	 * Output a message or error
	 * @param  string $message
	 * @param  string $type
	 */
	public function debug( $message, $type = 'notice' ) {

		if ( $this->debug || ( current_user_can( 'manage_options' ) && 'error' == $type ) ) {
			wc_add_notice( $message, $type );
		}

	}

	/**
     * init function.
     *
     * @access public
     * @return void
     */
	private function init() {

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Enqueue UPS Scripts
		wp_enqueue_script( 'ups-admin-js' );

		// Define user set variables
		$this->enabled         = $this->get_option( 'enabled', $this->enabled );
		$this->title           = $this->get_option( 'title', $this->method_title );
		$this->availability    = $this->get_option( 'availability', 'all' );
		$this->countries       = $this->get_option( 'countries', array() );
		$this->simple_advanced = $this->get_option( 'simple_advanced', 'simple' );

		// API Settings
		$this->user_id              = $this->get_option( 'user_id' );
		$this->password             = $this->get_option( 'password' );
		$this->access_key           = $this->get_option( 'access_key' );
		$this->shipper_number       = $this->get_option( 'shipper_number' );
		$this->negotiated           = ( $bool = $this->get_option( 'negotiated' ) ) && $bool == 'yes' ? true : false;
		$this->origin_addressline   = ( $bool = $this->get_option( 'origin_addressline' ) ) && $bool == 'yes' ? true : false;
		$this->origin_city          = $this->get_option( 'origin_city' );
		$this->origin_postcode      = $this->get_option( 'origin_postcode' );
		$this->origin_country_state = $this->get_option( 'origin_country_state' );
		$this->debug                = ( $bool = $this->get_option( 'debug' ) ) && $bool == 'yes' ? true : false;

		// Pickup and Destination
		$this->pickup      = $this->get_option( 'pickup', '01' );
		$this->residential = ( $bool = $this->get_option( 'residential' ) ) && $bool == 'yes' ? true : false;

		// Services and Packaging
		$this->offer_rates     = $this->get_option( 'offer_rates', 'all' );
		$this->fallback        = $this->get_option( 'fallback' );
		$this->packing_method  = $this->get_option( 'packing_method', 'per_item' );
		$this->ups_packaging   = $this->get_option( 'ups_packaging', array() );
		$this->custom_services = $this->get_option( 'services', array() );
		$this->boxes           = $this->get_option( 'boxes', array() );
		$this->insuredvalue    = ( $bool = $this->get_option( 'insuredvalue' ) ) && $bool == 'yes' ? true : false;
		$this->signature       = $this->get_option( 'signature', 'none' );

		// Units
		$this->units = $this->get_option( 'units', 'imperial' );

		if ( $this->units == 'metric' ) {
			$this->weight_unit = 'KGS';
			$this->dim_unit    = 'CM';
		} else {
			$this->weight_unit = 'LBS';
			$this->dim_unit    = 'IN';
		}

		/**
		 * If no origin country / state saved / exists, set it to store base country:
		 * Includes compat for < 2.3.
		 */
		if ( ! $this->origin_country_state ) {
			if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.3', '>=' ) ) {
				$origin = wc_get_base_location();
				$this->origin_country = $origin['country'];
				$this->origin_state   = $origin['state'];
			} else {
				$base_country = get_option( 'woocommerce_default_country' );
				$this->split_country_state( $base_country );
			}
		} else {
			$this->split_country_state( $this->origin_country_state );
		}

		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'clear_transients' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
	}

	/**
	 * Helper method to split the country/state and set them.
	 */
	public function split_country_state( $country_state ) {
		if ( strstr( $country_state, ':') ) {
			$origin_country_state = explode( ':', $country_state );
			$this->origin_country = current( $origin_country_state );
			$this->origin_state   = end( $origin_country_state );
		} else {
			$this->origin_country = $country_state;
			$this->origin_state   = '';
		}
	}

	/**
	 * Assets to enqueue in admin
	 */

	public function assets() {
		wp_register_style( 'ups-admin-css', plugin_dir_url( __FILE__ ) . '../assets/css/ups-admin.css', '', WC_SHIPPING_UPS_VERSION );
		wp_register_script( 'ups-admin-js', plugin_dir_url( __FILE__ ) . '../assets/js/ups-admin.js', array( 'jquery' ), WC_SHIPPING_UPS_VERSION, true );

		wp_enqueue_style( 'ups-admin-css' );
		wp_enqueue_script( 'jquery-ui-sortable' );

		$vars = array(
			'dim_unit' 		=> $this->dim_unit,
			'weight_unit'	=> $this->weight_unit,
		);

		wp_localize_script( 'ups-admin-js', 'wcups', $vars );

	}

	/**
	 * environment_check function.
	 *
	 * @access public
	 * @return void
	 */
	private function environment_check() {

		$error_message = '';

		// Check for UPS User ID
		if ( ( ! $this->user_id || ! $this->password || ! $this->access_key || ! $this->shipper_number ) && $this->enabled == 'yes' ) {
			$error_message .= '<p>' . __( 'UPS is enabled, but you have not entered all of your UPS details!', 'woocommerce-shipping-ups' ) . '</p>';
		}

		// If user has selected to pack into boxes,
		// Check if at least one UPS packaging is chosen, or a custom box is defined
		if ( ( $this->packing_method == 'box_packing' ) && ( $this->enabled == 'yes' ) ) {
			if ( empty( $this->ups_packaging )  && empty( $this->boxes ) ){
				$error_message .= '<p>' . __( 'UPS is enabled, and Parcel Packing Method is set to \'Pack into boxes\', but no UPS Packaging is selected and there are no custom boxes defined. Items will be packed individually.', 'woocommerce-shipping-ups' ) . '</p>';
			}
		}

		// Check for at least one service enabled
		$ctr=0;
		if ( isset($this->custom_services ) && is_array( $this->custom_services ) ){
			foreach ( $this->custom_services as $key => $values ){
				if ( $values['enabled'] == 1) {
					$ctr++;
				}
			}
		}
		if ( ( $ctr == 0 ) && $this->enabled == 'yes' ) {
			$error_message .= '<p>' . __( 'UPS is enabled, but there are no services enabled.', 'woocommerce-shipping-ups' ) . '</p>';
		}

		if ( ! $error_message == '' ) {
			echo '<div class="error">';
			echo $error_message;
			echo '</div>';
		}
	}

	/**
	 * admin_options function.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_options() {
		// Check users environment supports this method
		$this->environment_check();

		// Show settings
		parent::admin_options();
	}

	/**
	 *
	 * generate_single_select_country_html function
	 *
	 * @access public
	 * @return void
	 */
	public function generate_single_select_country_html() {

		ob_start();
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="origin_country"><?php _e( 'Origin Country', 'woocommerce-shipping-ups' ); ?></label>
			</th>
			<td class="forminp"><select name="woocommerce_ups_origin_country_state" id="woocommerce_ups_origin_country_state" style="width: 250px;" data-placeholder="<?php _e('Choose a country&hellip;', 'woocommerce'); ?>" title="Country" class="chosen_select">
				<?php echo WC()->countries->country_dropdown_options( $this->origin_country, $this->origin_state ? $this->origin_state : '*' ); ?>
			</select>
			</td>
		</tr>
		<?php
		return ob_get_clean();
	}

	/**
	 * generate_services_html function.
	 *
	 * @access public
	 * @return void
	 */
	public function generate_services_html() {
		ob_start();
		?>
		<tr valign="top" id="service_options">
			<th scope="row" class="titledesc"><?php _e( 'Services', 'woocommerce-shipping-ups' ); ?></th>
			<td class="forminp">
				<table class="ups_services widefat">
					<thead>
						<th class="sort">&nbsp;</th>
						<th><?php _e( 'Service Code', 'woocommerce-shipping-ups' ); ?></th>
						<th><?php _e( 'Name', 'woocommerce-shipping-ups' ); ?></th>
						<th><?php _e( 'Enabled', 'woocommerce-shipping-ups' ); ?></th>
						<th><?php echo sprintf( __( 'Price Adjustment (%s)', 'woocommerce-shipping-ups' ), get_woocommerce_currency_symbol() ); ?></th>
						<th><?php _e( 'Price Adjustment (%)', 'woocommerce-shipping-ups' ); ?></th>
					</thead>
					<tfoot>
					<?php if( !$this->origin_country == 'PL' && !in_array( $this->origin_country, $this->eu_array ) ) : ?>
						<tr>
							<th colspan="6">
								<small class="description"><?php _e( '<strong>Domestic Rates</strong>: Next Day Air, 2nd Day Air, Ground, 3 Day Select, Next Day Air Saver, Next Day Air Early AM, 2nd Day Air AM', 'woocommerce-shipping-ups' ); ?></small><br/>
								<small class="description"><?php _e( '<strong>International Rates</strong>: Worldwide Express, Worldwide Expedited, Standard, Worldwide Express Plus, UPS Saver', 'woocommerce-shipping-ups' ); ?></small>
							</th>
						</tr>
					<?php endif ?>
					</tfoot>
					<tbody>
						<?php
							$sort = 0;
							$this->ordered_services = array();

							if ( $this->origin_country == 'PL' ) {
								$use_services = $this->polandservices;
							} elseif ( in_array( $this->origin_country, $this->eu_array ) ) {
								$use_services = $this->euservices;
							} else {
								$use_services = $this->services;
							}

							foreach ( $use_services as $code => $name ) {

								if ( isset( $this->custom_services[ $code ]['order'] ) ) {
									$sort = $this->custom_services[ $code ]['order'];
								}

								while ( isset( $this->ordered_services[ $sort ] ) ) {
									$sort++;
								}

								$this->ordered_services[ $sort ] = array( $code, $name );

								$sort++;
							}

							ksort( $this->ordered_services );

							foreach ( $this->ordered_services as $value ) {
								$code = $value[0];
								$name = $value[1];
								?>
								<tr>
									<td class="sort"><input type="hidden" class="order" name="ups_service[<?php echo $code; ?>][order]" value="<?php echo isset( $this->custom_services[ $code ]['order'] ) ? $this->custom_services[ $code ]['order'] : ''; ?>" /></td>
									<td><strong><?php echo $code; ?></strong></td>
									<td><input type="text" name="ups_service[<?php echo $code; ?>][name]" placeholder="<?php echo $name; ?> (<?php echo $this->title; ?>)" value="<?php echo isset( $this->custom_services[ $code ]['name'] ) ? $this->custom_services[ $code ]['name'] : ''; ?>" size="50" /></td>
									<td><input type="checkbox" name="ups_service[<?php echo $code; ?>][enabled]" <?php checked( ( ! isset( $this->custom_services[ $code ]['enabled'] ) || ! empty( $this->custom_services[ $code ]['enabled'] ) ), true ); ?> /></td>
									<td><input type="text" name="ups_service[<?php echo $code; ?>][adjustment]" placeholder="N/A" value="<?php echo isset( $this->custom_services[ $code ]['adjustment'] ) ? $this->custom_services[ $code ]['adjustment'] : ''; ?>" size="4" /></td>
									<td><input type="text" name="ups_service[<?php echo $code; ?>][adjustment_percent]" placeholder="N/A" value="<?php echo isset( $this->custom_services[ $code ]['adjustment_percent'] ) ? $this->custom_services[ $code ]['adjustment_percent'] : ''; ?>" size="4" /></td>
								</tr>
								<?php
							}
						?>
					</tbody>
				</table>
			</td>
		</tr>
		<?php
		return ob_get_clean();
	}


	/**
	 * generate_box_packing_html function.
	 *
	 * @access public
	 * @return void
	 */
	public function generate_box_packing_html() {
		ob_start();
		?>
		<tr valign="top" id="packing_options">
			<th scope="row" class="titledesc"><?php _e( 'Custom Boxes', 'woocommerce-shipping-ups' ); ?></th>
			<td class="forminp">
				<table class="ups_boxes widefat">
					<thead>
						<tr>
							<th class="check-column"><input type="checkbox" /></th>
							<th><?php _e( 'Outer Length', 'woocommerce-shipping-ups' ); ?></th>
							<th><?php _e( 'Outer Width', 'woocommerce-shipping-ups' ); ?></th>
							<th><?php _e( 'Outer Height', 'woocommerce-shipping-ups' ); ?></th>
							<th><?php _e( 'Inner Length', 'woocommerce-shipping-ups' ); ?></th>
							<th><?php _e( 'Inner Width', 'woocommerce-shipping-ups' ); ?></th>
							<th><?php _e( 'Inner Height', 'woocommerce-shipping-ups' ); ?></th>
							<th><?php _e( 'Weight of Box', 'woocommerce-shipping-ups' ); ?></th>
							<th><?php _e( 'Max Weight', 'woocommerce-shipping-ups' ); ?></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th colspan="3">
								<a href="#" class="button plus insert"><?php _e( 'Add Box', 'woocommerce-shipping-ups' ); ?></a>
								<a href="#" class="button minus remove"><?php _e( 'Remove selected box(es)', 'woocommerce-shipping-ups' ); ?></a>
							</th>
							<th colspan="6">
								<small class="description"><?php _e( 'Items will be packed into these boxes depending based on item dimensions and volume. Outer dimensions will be passed to UPS, whereas inner dimensions will be used for packing. Items not fitting into boxes will be packed individually.', 'woocommerce-shipping-ups' ); ?></small>
							</th>
						</tr>
					</tfoot>
					<tbody id="rates">
						<?php
							if ( $this->boxes && ! empty( $this->boxes ) ) {
								foreach ( $this->boxes as $key => $box ) {
									?>
									<tr>
										<td class="check-column"><input type="checkbox" /></td>
										<td><input type="text" size="5" name="boxes_outer_length[<?php echo $key; ?>]" value="<?php echo esc_attr( $box['outer_length'] ); ?>" /><?php echo $this->dim_unit; ?></td>
										<td><input type="text" size="5" name="boxes_outer_width[<?php echo $key; ?>]" value="<?php echo esc_attr( $box['outer_width'] ); ?>" /><?php echo $this->dim_unit; ?></td>
										<td><input type="text" size="5" name="boxes_outer_height[<?php echo $key; ?>]" value="<?php echo esc_attr( $box['outer_height'] ); ?>" /><?php echo $this->dim_unit; ?></td>
										<td><input type="text" size="5" name="boxes_inner_length[<?php echo $key; ?>]" value="<?php echo esc_attr( $box['inner_length'] ); ?>" /><?php echo $this->dim_unit; ?></td>
										<td><input type="text" size="5" name="boxes_inner_width[<?php echo $key; ?>]" value="<?php echo esc_attr( $box['inner_width'] ); ?>" /><?php echo $this->dim_unit; ?></td>
										<td><input type="text" size="5" name="boxes_inner_height[<?php echo $key; ?>]" value="<?php echo esc_attr( $box['inner_height'] ); ?>" /><?php echo $this->dim_unit; ?></td>
										<td><input type="text" size="5" name="boxes_box_weight[<?php echo $key; ?>]" value="<?php echo esc_attr( $box['box_weight'] ); ?>" /><?php echo $this->weight_unit; ?></td>
										<td><input type="text" size="5" name="boxes_max_weight[<?php echo $key; ?>]" value="<?php echo esc_attr( $box['max_weight'] ); ?>" /><?php echo $this->weight_unit; ?></td>
									</tr>
									<?php
								}
							}
						?>
					</tbody>
				</table>
			</td>
		</tr>
		<?php
		return ob_get_clean();
	}

	/**
	 * validate_single_select_country_field function.
	 *
	 * @access public
	 * @param mixed $key
	 * @return void
	 */
	public function validate_single_select_country_field( $key ) {

		if ( isset( $_POST['woocommerce_ups_origin_country_state'] ) ) {
			return $_POST['woocommerce_ups_origin_country_state'];
		} else {
			return '';
		}
	}

	/**
	 * validate_box_packing_field function.
	 *
	 * @access public
	 * @param mixed $key
	 * @return void
	 */
	public function validate_box_packing_field( $key ) {

		$boxes = array();

		if ( isset( $_POST['boxes_outer_length'] ) ) {
			$boxes_outer_length = $_POST['boxes_outer_length'];
			$boxes_outer_width  = $_POST['boxes_outer_width'];
			$boxes_outer_height = $_POST['boxes_outer_height'];
			$boxes_inner_length = $_POST['boxes_inner_length'];
			$boxes_inner_width  = $_POST['boxes_inner_width'];
			$boxes_inner_height = $_POST['boxes_inner_height'];
			$boxes_box_weight   = $_POST['boxes_box_weight'];
			$boxes_max_weight   = $_POST['boxes_max_weight'];


			for ( $i = 0; $i < sizeof( $boxes_outer_length ); $i ++ ) {

				if ( $boxes_outer_length[ $i ] && $boxes_outer_width[ $i ] && $boxes_outer_height[ $i ] && $boxes_inner_length[ $i ] && $boxes_inner_width[ $i ] && $boxes_inner_height[ $i ] ) {

					$boxes[] = array(
						'outer_length' => floatval( $boxes_outer_length[ $i ] ),
						'outer_width'  => floatval( $boxes_outer_width[ $i ] ),
						'outer_height' => floatval( $boxes_outer_height[ $i ] ),
						'inner_length' => floatval( $boxes_inner_length[ $i ] ),
						'inner_width'  => floatval( $boxes_inner_width[ $i ] ),
						'inner_height' => floatval( $boxes_inner_height[ $i ] ),
						'box_weight'   => floatval( $boxes_box_weight[ $i ] ),
						'max_weight'   => floatval( $boxes_max_weight[ $i ] ),
					);

				}

			}

		}

		return $boxes;
	}

	/**
	 * validate_services_field function.
	 *
	 * @access public
	 * @param mixed $key
	 * @return void
	 */
	public function validate_services_field( $key ) {
		$services         = array();
		$posted_services  = $_POST['ups_service'];

		foreach ( $posted_services as $code => $settings ) {

			$services[ $code ] = array(
				'name'               => wc_clean( $settings['name'] ),
				'order'              => wc_clean( $settings['order'] ),
				'enabled'            => isset( $settings['enabled'] ) ? true : false,
				'adjustment'         => wc_clean( $settings['adjustment'] ),
				'adjustment_percent' => str_replace( '%', '', wc_clean( $settings['adjustment_percent'] ) )
			);

		}

		return $services;
	}

	/**
	 * clear_transients function.
	 *
	 * @access public
	 * @return void
	 */
	public function clear_transients() {
		global $wpdb;

		$wpdb->query( "DELETE FROM `$wpdb->options` WHERE `option_name` LIKE ('_transient_ups_quote_%') OR `option_name` LIKE ('_transient_timeout_ups_quote_%')" );
	}

	/**
	 * init_form_fields function.
	 *
	 * @access public
	 * @return void
	 */
	public function init_form_fields() {

		$this->form_fields  = array(
			'enabled'          => array(
				'title'           => __( 'Enable/Disable', 'woocommerce-shipping-ups' ),
				'type'            => 'checkbox',
				'label'           => __( 'Enable this shipping method', 'woocommerce-shipping-ups' ),
				'default'         => 'no'
			),
			'api'           => array(
				'title'           => __( 'API Settings', 'woocommerce-shipping-ups' ),
				'type'            => 'title',
				'description'     => sprintf( __( 'You need to obtain UPS account credentials by registering on %svia their website%s.', 'woocommerce-shipping-ups' ), '<a href="https://www.ups.com/upsdeveloperkit">', '</a>' ),
				'class'			  => 'ups-section-title ups-api-title',
			),
			'user_id'           => array(
				'title'           => __( 'UPS User ID', 'woocommerce-shipping-ups' ),
				'type'            => 'text',
				'description'     => __( 'Obtained from UPS after getting an account.', 'woocommerce-shipping-ups' ),
				'default'         => '',
				'class'			  => 'ups-api-setting',
				'desc_tip' => true
			),
			'password'            => array(
				'title'           => __( 'UPS Password', 'woocommerce-shipping-ups' ),
				'type'            => 'password',
				'description'     => __( 'Obtained from UPS after getting an account.', 'woocommerce-shipping-ups' ),
				'default'         => '',
				'class'			  => 'ups-api-setting',
				'desc_tip' => true
			),
			'access_key'          => array(
				'title'           => __( 'UPS Access Key', 'woocommerce-shipping-ups' ),
				'type'            => 'text',
				'description'     => __( 'Obtained from UPS after getting an account.', 'woocommerce-shipping-ups' ),
				'default'         => '',
				'class'			  => 'ups-api-setting',
				'desc_tip' => true
			),
			'shipper_number'      => array(
				'title'           => __( 'UPS Account Number', 'woocommerce-shipping-ups' ),
				'type'            => 'text',
				'description'     => __( 'Obtained from UPS after getting an account.', 'woocommerce-shipping-ups' ),
				'default'         => '',
				'class'			  => 'ups-api-setting',
				'desc_tip' => true
			),
			'core'           => array(
				'title'           => __( 'Method & Origin Settings', 'woocommerce-shipping-ups' ),
				'type'            => 'title',
				'description'     => '',
				'class'			  => 'ups-section-title',
			),
			'title'            => array(
				'title'           => __( 'Method Title', 'woocommerce-shipping-ups' ),
				'type'            => 'text',
				'description'     => __( 'This controls the title which the user sees during checkout.', 'woocommerce-shipping-ups' ),
				'default'         => __( 'UPS', 'woocommerce-shipping-ups' ),
				'desc_tip' => true
			),
			'availability'  => array(
				'title'           => __( 'Method Availability', 'woocommerce-shipping-ups' ),
				'type'            => 'select',
				'default'         => 'all',
				'class'           => 'availability',
				'options'         => array(
					'all'            => __( 'All Countries', 'woocommerce-shipping-ups' ),
					'specific'       => __( 'Specific Countries', 'woocommerce-shipping-ups' ),
				),
			),
			'countries'        => array(
				'title'           => __( 'Specific Countries', 'woocommerce-shipping-ups' ),
				'type'            => 'multiselect',
				'class'           => 'chosen_select',
				'css'             => 'width: 450px;',
				'default'         => '',
				'options'         => WC()->countries->get_allowed_countries(),
			),
			'origin_city'      	  => array(
				'title'           => __( 'Origin City', 'woocommerce-shipping-ups' ),
				'type'            => 'text',
				'description'     => __( 'Enter the city for the <strong>sender</strong>.', 'woocommerce-shipping-ups' ),
				'default'         => '',
				'desc_tip' => true
			),
			'origin_postcode'     => array(
				'title'           => __( 'Origin Postcode', 'woocommerce-shipping-ups' ),
				'type'            => 'text',
				'description'     => __( 'Enter the zip/postcode for the <strong>sender</strong>.', 'woocommerce-shipping-ups' ),
				'default'         => '',
				'desc_tip' => true
			),
			'origin_country_state'      => array(
				'type'            => 'single_select_country',
			),
			'services_packaging'  => array(
				'title'           => __( 'Services and Packaging', 'woocommerce-shipping-ups' ),
				'type'            => 'title',
				'description'     => __( 'Please enable all of the different services you\'d like to offer customers.', 'woocommerce-shipping-ups' ) . ' <em>' . __( 'By enabling a service, it doesn\'t gaurantee that it will be offered, as the plugin will only offer the available rates based on the package, the origin and the destination.', 'woocommerce-shipping-ups' ) . '</em>',
				'class'			  => 'ups-section-title',
			),
			'services'  => array(
				'type'            => 'services'
			),
			'offer_rates'   => array(
				'title'           => __( 'Offer Rates', 'woocommerce-shipping-ups' ),
				'type'            => 'select',
				'description'     => '',
				'default'         => 'all',
				'options'         => array(
				    'all'         => __( 'Offer the customer all returned rates', 'woocommerce-shipping-ups' ),
				    'cheapest'    => __( 'Offer the customer the cheapest rate only', 'woocommerce-shipping-ups' ),
				),
			),
			'negotiated'  => array(
				'title'           => __( 'Negotiated Rates', 'woocommerce-shipping-ups' ),
				'label'           => __( 'Enable negotiated rates', 'woocommerce-shipping-ups' ),
				'type'            => 'checkbox',
				'default'         => 'no',
				'description'     => sprintf( __( 'Enable this %sonly%s if this shipping account has %snegotiated rates%s available.', 'woocommerce-shipping-ups' ), '<strong>', '</strong>', '<a href="http://www.ups.com/content/au/en/resources/techsupport/worldship/negotiated_rates.html">', '</a>' ),
			),
			'signature'  => array(
				'title'           => __( 'Delivery Confirmation', 'woocommerce-shipping-ups' ),
				'label'           => __( 'Enable debug mode', 'woocommerce-shipping-ups' ),
				'type'            => 'select',
				'options'		  => array(
					'none'		=> __( 'No Signature Required', 'woocommerce-shipping-ups' ),
					'regular'	=> sprintf( __( 'Signature Required: $%s per package', 'woocommerce-shipping-ups' ), self::SIGNATURE_FEE ),
					'adult'		=> sprintf( __( 'Adult Signature Required: $%s per package', 'woocommerce-shipping-ups' ), self::ADULT_SIGNATURE_FEE ),
				),
				'default'         => 'none',
				'description'     => __( 'Optionally you may charge customers for signature on delivery. This will just add the specified amount above to the returned rates.', 'woocommerce-shipping-ups' ),
				'desc_tip' => true
			),
			'packing_method'  => array(
				'title'           => __( 'Parcel Packing Method', 'woocommerce-shipping-ups' ),
				'type'            => 'select',
				'default'         => '',
				'class'           => 'packing_method',
				'options'         => array(
					'per_item'       => __( 'Default: Pack items individually', 'woocommerce-shipping-ups' ),
					'box_packing'    => __( 'Recommended: Pack into boxes with weights and dimensions', 'woocommerce-shipping-ups' ),
				),
			),
			'ups_packaging'  => array(
				'title'           => __( 'UPS Packaging', 'woocommerce-shipping-ups' ),
				'type'            => 'multiselect',
				'description'	  => __( 'Select UPS standard packaging options to enable', 'woocommerce-shipping-ups' ),
				'default'         => array(),
				'css'			  => 'width: 450px;',
				'class'           => 'ups_packaging chosen_select',
				'options'         => $this->packaging_select
			),
			'boxes'  => array(
				'type'            => 'box_packing'
			),
			'advanced_title'  => array(
				'title'           => __( 'Advanced Options', 'woocommerce-shipping-ups' ),
				'type'            => 'title',
				'description'     => __( 'Only modify the following options if needed. They will most likely alter the regularly offered rate(s).', 'woocommerce-shipping-ups' ),
				'class'			  => 'ups-section-title',
			),
			'debug'  => array(
				'title'           => __( 'Debug Mode', 'woocommerce-shipping-ups' ),
				'label'           => __( 'Enable debug mode', 'woocommerce-shipping-ups' ),
				'type'            => 'checkbox',
				'default'         => 'no',
				'description'     => __( 'Enable debug mode to show debugging information on your cart/checkout.', 'woocommerce-shipping-ups' ),
				'desc_tip' => true
			),
			'origin_addressline'  => array(
				'title'           => __( 'Origin Address', 'woocommerce-shipping-ups' ),
				'type'            => 'text',
				'description'     => __( 'Sometimes you may need to enter the address for the <strong>sender / origin</strong>.', 'woocommerce-shipping-ups' ),
				'default'         => '',
				'desc_tip' => true
			),
			'residential'  => array(
				'title'           => __( 'Residential', 'woocommerce-shipping-ups' ),
				'label'           => __( 'Enable residential address flag', 'woocommerce-shipping-ups' ),
				'type'            => 'checkbox',
				'default'         => 'yes',
				'description'     => __( 'Enable this to indicate to UPS that the receiver / customer is a residential address.', 'woocommerce-shipping-ups' ),
				'desc_tip' => true
			),
			'insuredvalue'  => array(
				'title'           => __( 'Insured Value', 'woocommerce-shipping-ups' ),
				'label'           => __( 'Request Insurance to be included in UPS rates', 'woocommerce-shipping-ups' ),
				'type'            => 'checkbox',
				'default'         => 'no',
				'description'     => __( 'Enable insured value option to include insurance in UPS rates', 'woocommerce-shipping-ups' ),
				'desc_tip' => true
			),
			'pickup'  => array(
				'title'           => __( 'Pickup Type', 'woocommerce-shipping-ups' ),
				'type'            => 'select',
				'css'             => 'width: 250px;',
				'description'	  => __( 'This will adjust the rate you get, so only change it if needed.', 'woocommerce-shipping-ups' ),
				'class'           => 'chosen_select',
				'default'         => '03',
				'options'         => $this->pickup_code,
				'desc_tip' => true
			),
			'fallback' => array(
				'title'       => __( 'Fallback', 'woocommerce-shipping-ups' ),
				'type'        => 'price',
				'description' => __( 'If UPS returns no matching rates, offer this amount for shipping so that the user can still checkout. Leave blank to disable. Enter a numeric value with no currency symbols.', 'woocommerce-shipping-ups' ),
				'default'     => '',
				'desc_tip' => true
			),
			'units'      => array(
				'title'           => __( 'Weight/Dimension Units', 'woocommerce-shipping-ups' ),
				'type'            => 'select',
				'description'     => __( 'If you see "This measurement system is not valid for the selected country" errors, switch this to metric units.', 'woocommerce-shipping-ups' ),
				'default'         => 'imperial',
				'options'         => array(
					'imperial'        => __( 'LB / IN', 'woocommerce-shipping-ups' ),
					'metric'          => __( 'KG / CM', 'woocommerce-shipping-ups' ),
				),
				'desc_tip' => true
			),

		);
	}

	/**
	 * Get Parsed XML response
	 * @param  string $xml
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
	 * calculate_shipping function.
	 *
	 * @access public
	 * @param mixed $package
	 * @return void
     */
	public function calculate_shipping( $package = array() ) {
		$rates        = array();
		$ups_responses = array();

		// Only return rates if the package has a destination including country
		if ( '' === $package['destination']['country'] ) {
			$this->debug( __( 'UPS: Country not supplied. Rates not requested.', 'woocommerce-shipping-ups' ) );
			return;
		}

		// If no origin postcode set, throw an error and stop the calculation
		if ( ! $this->origin_postcode ) {
			$this->debug( sprintf( __( 'UPS: No Origin Postcode has been set. Please %sadd one%s so rates can be calculated!', 'woocommerce-shipping-ups' ), '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=shipping&section=wc_shipping_ups' ) . '">', '</a>' ), 'error' );
			return;
		}

		$package_requests = $this->get_package_requests( $package );

		if ( $package_requests ) {

			$rate_requests = $this->get_rate_requests( $package_requests, $package );

			if ( ! $rate_requests ) {
				$this->debug( __( 'UPS: No Services are enabled in admin panel.', 'woocommerce-shipping-ups' ) );
			}

			// get live or cached result for each rate
			foreach ( $rate_requests as $code => $request ) {
				$send_request           = str_replace( array( "\n", "\r" ), '', $request );
				$transient              = 'ups_quote_' . md5( $request );
				$cached_response        = get_transient( $transient );
				$ups_responses[ $code ] = false;

				if ( $cached_response === false ) {
					$response = wp_remote_post( $this->endpoint,
						array(
							'timeout'   => 70,
							'sslverify' => 0,
							'body'      => $send_request
						)
					);

					if ( ! empty( $response['body'] ) ) {
						$ups_responses[ $code ] = $response['body'];
						set_transient( $transient, $response['body'], YEAR_IN_SECONDS );
					}

				} else {
					$ups_responses[ $code ] = $cached_response;
					$this->debug( __( 'UPS: Using cached response.', 'woocommerce-shipping-ups' ) );
				}

				$this->debug( 'UPS REQUEST: <pre>' . print_r( htmlspecialchars( $request ), true ) . '</pre>' );
				$this->debug( 'UPS RESPONSE: <pre>' . print_r( htmlspecialchars( $ups_responses[ $code ] ), true ) . '</pre>' );

			}

			// parse the results
			foreach ( $ups_responses as $code => $response ) {
				$xml = $this->get_parsed_xml( $response );

				if ( $this->debug && ! $xml ) {
					$this->debug( __( 'Failed loading XML', 'woocommerce-shipping-ups' ), 'error' );
				}

				if ( $xml->Response->ResponseStatusCode == 1 ) {

					$service_name = $this->services[ $code ];

					if ( $this->negotiated && isset( $xml->RatedShipment->NegotiatedRates->NetSummaryCharges->GrandTotal->MonetaryValue ) ) {
						$rate_cost = (float) $xml->RatedShipment->NegotiatedRates->NetSummaryCharges->GrandTotal->MonetaryValue;
					} else {
						$rate_cost = (float) $xml->RatedShipment->TotalCharges->MonetaryValue;
					}

					$rate_id     = $this->id . ':' . $code;
					$rate_name   = $service_name . ' (' . $this->title . ')';

					// Name adjustment
					if ( ! empty( $this->custom_services[ $code ]['name'] ) ) {
						$rate_name = $this->custom_services[ $code ]['name'];
					}

					// Signature Adjustment
					switch ( $this->signature ) {
						case 'regular':
							$rate_cost = $rate_cost + ( count( $package_requests ) * self::SIGNATURE_FEE );
							break;
						case 'adult':
							$rate_cost = $rate_cost + ( count( $package_requests ) * self::ADULT_SIGNATURE_FEE );
							break;
						default:
							$rate_cost = $rate_cost;
							break;
					}

					// Cost adjustment %
					if ( ! empty( $this->custom_services[ $code ]['adjustment_percent'] ) ) {
						$rate_cost = $rate_cost + ( $rate_cost * ( floatval( $this->custom_services[ $code ]['adjustment_percent'] ) / 100 ) );
					}
					// Cost adjustment
					if ( ! empty( $this->custom_services[ $code ]['adjustment'] ) ) {
						$rate_cost = $rate_cost + floatval( $this->custom_services[ $code ]['adjustment'] );
					}

					// Sort
					if ( isset( $this->custom_services[ $code ]['order'] ) ) {
						$sort = $this->custom_services[ $code ]['order'];
					} else {
						$sort = 999;
					}

					$rates[ $rate_id ] = array(
						'id'    => $rate_id,
						'label' => $rate_name,
						'cost' 	=> $rate_cost,
						'sort'  => $sort,
					);

				} else {
					// Either there was an error on this rate, or the rate is not valid (i.e. it is a domestic rate, but shipping international)
					$this->debug( sprintf( __( '[UPS] No rate returned for service code %1$s, %2$s (UPS code: %3$s)', 'woocommerce-shipping-ups' ),
							$code,
							$xml->Response->Error->ErrorDescription,
							$xml->Response->Error->ErrorCode 
						)
					);
				}

			} // foreach ( $ups_responses )

		} // foreach ( $package_requests )

		// Add rates
		if ( $rates ) {

			if ( $this->offer_rates == 'all' ) {

				uasort( $rates, array( $this, 'sort_rates' ) );
				foreach ( $rates as $key => $rate ) {
					$this->add_rate( $rate );
				}

			} else {

				$cheapest_rate = '';

				foreach ( $rates as $key => $rate ) {
					if ( ! $cheapest_rate || $cheapest_rate['cost'] > $rate['cost'] ) {
						$cheapest_rate = $rate;
					}
				}

				$cheapest_rate['label'] = $this->title;

				$this->add_rate( $cheapest_rate );

			}
		// Fallback
		} elseif ( $this->fallback ) {
			$this->add_rate( array(
				'id' 	=> $this->id . '_fallback',
				'label' => $this->title,
				'cost' 	=> $this->fallback,
				'sort'  => 0
			) );
			$this->debug( __( 'UPS: Using Fallback setting.', 'woocommerce-shipping-ups' ) );
		}
	}

	/**
	 * sort_rates function.
	 *
	 * @access public
	 * @param mixed $a
	 * @param mixed $b
	 * @return void
	 */
	public function sort_rates( $a, $b ) {
		if ( $a['sort'] == $b['sort'] ) return 0;
		return ( $a['sort'] < $b['sort'] ) ? -1 : 1;
	}

	/**
	 * get_package_requests
	 *
	 *
	 *
	 * @access private
	 * @return void
	 */
	private function get_package_requests( $package ) {

		// Choose selected packing
		switch ( $this->packing_method ) {
			case 'box_packing' :
				$requests = $this->box_shipping( $package );
				break;
			case 'per_item' :
			default :
				$requests = $this->per_item_shipping( $package );
				break;
		}

		return $requests;
	}

	/**
	 * get_rate_requests
	 *
	 * Get rate requests for all
	 * @access private
	 * @return array of strings - XML
	 *
	 */
	private function get_rate_requests( $package_requests, $package ) {

		$rate_requests = array();

		foreach ( $this->custom_services as $code => $params ) {
			if ( 1 == $params['enabled'] ) {

			// Security Header
			$request  = "<?xml version=\"1.0\" ?>" . "\n";
			$request .= "<AccessRequest xml:lang='en-US'>" . "\n";
			$request .= "	<AccessLicenseNumber>" . $this->access_key . "</AccessLicenseNumber>" . "\n";
			$request .= "	<UserId>" . $this->user_id . "</UserId>" . "\n";
			// Ampersand will break XML doc, so replace with encoded version.
			$valid_pass = str_replace( '&', '&amp;', $this->password );
			$request .= "	<Password>" . $valid_pass . "</Password>" . "\n";
			$request .= "</AccessRequest>" . "\n";
				$request .= "<?xml version=\"1.0\" ?>" . "\n";
				$request .= "<RatingServiceSelectionRequest>" . "\n";
				$request .= "	<Request>" . "\n";
				$request .= "	<TransactionReference>" . "\n";
				$request .= "		<CustomerContext>Rating and Service</CustomerContext>" . "\n";
				$request .= "		<XpciVersion>1.0</XpciVersion>" . "\n";
				$request .= "	</TransactionReference>" . "\n";
				$request .= "	<RequestAction>Rate</RequestAction>" . "\n";
				$request .= "	<RequestOption>Rate</RequestOption>" . "\n";
				$request .= "	</Request>" . "\n";
				$request .= "	<PickupType>" . "\n";
				$request .= "		<Code>" . $this->pickup . "</Code>" . "\n";
				$request .= "		<Description>" . $this->pickup_code[$this->pickup] . "</Description>" . "\n";
				$request .= "	</PickupType>" . "\n";
				// Shipment information
				$request .= "	<Shipment>" . "\n";
				$request .= "		<Description>WooCommerce Rate Request</Description>" . "\n";
				$request .= "		<Shipper>" . "\n";
				$request .= "			<ShipperNumber>" . $this->shipper_number . "</ShipperNumber>" . "\n";
				$request .= "			<Address>" . "\n";
				if ( $this->origin_addressline ) {
					$request .= "			<AddressLine>" . $this->origin_addressline . "</AddressLine>" . "\n";
				}
				$request .= "				<City>" . $this->origin_city . "</City>" . "\n";
				$request .= "				<PostalCode>" . $this->origin_postcode . "</PostalCode>" . "\n";
				$request .= "				<CountryCode>" . $this->origin_country . "</CountryCode>" . "\n";
				$request .= "			</Address>" . "\n";
				$request .= "		</Shipper>" . "\n";
				$request .= "		<ShipTo>" . "\n";
				$request .= "			<Address>" . "\n";
				$request .= "				<StateProvinceCode>" . $package['destination']['state'] . "</StateProvinceCode>" . "\n";
				$request .= "				<PostalCode>" . $package['destination']['postcode'] . "</PostalCode>" . "\n";
				// if Country / State is 'Puerto Rico', set it to be the country, else use set country
				if ( ( "PR" == $package['destination']['state'] ) && ( "US" == $package['destination']['country'] ) ) {
					$request .= "			<CountryCode>PR</CountryCode>" . "\n";
				} else {
					$request .= "			<CountryCode>" . $package['destination']['country'] . "</CountryCode>" . "\n";
				}
				if ( $this->residential ) {
					$request .= "			<ResidentialAddressIndicator></ResidentialAddressIndicator>" . "\n";
				}
				$request .= "			</Address>" . "\n";
				$request .= "		</ShipTo>" . "\n";
				$request .= "		<ShipFrom>" . "\n";
				$request .= "			<Address>" . "\n";
				if ( $this->origin_addressline ) {
					$request .= "			<AddressLine>" . $this->origin_addressline . "</AddressLine>" . "\n";
				}
				$request .= "				<City>" . $this->origin_city . "</City>" . "\n";
				$request .= "				<PostalCode>" . $this->origin_postcode . "</PostalCode>" . "\n";
				$request .= "				<CountryCode>" . $this->origin_country . "</CountryCode>" . "\n";
				if ( $this->negotiated && $this->origin_state ) {
					$request .= "			<StateProvinceCode>" . $this->origin_state . "</StateProvinceCode>" . "\n";
				}
				$request .= "			</Address>" . "\n";
				$request .= "		</ShipFrom>" . "\n";
				$request .= "		<Service>" . "\n";
				$request .= "			<Code>" . $code . "</Code>" . "\n";
				$request .= "		</Service>" . "\n";
				// packages
				foreach ( $package_requests as $key => $package_request ) {
					$request .= $package_request;
				}
				// negotiated rates flag
				if ( $this->negotiated ) {
					$request .= "		<RateInformation>" . "\n";
					$request .= "			<NegotiatedRatesIndicator />" . "\n";
					$request .= "		</RateInformation>" . "\n";
				}
				$request .= "	</Shipment>" . "\n";
				$request .= "</RatingServiceSelectionRequest>" . "\n";

				$rate_requests[$code] = $request;

			} // if (enabled)
		} // foreach()

		return $rate_requests;

	}

	/**
	 * per_item_shipping function.
	 *
	 * @access private
	 * @param mixed $package
	 * @return mixed $requests - an array of XML strings
	 */
	private function per_item_shipping( $package ) {

		$requests = array();

		$ctr=0;
		foreach ( $package['contents'] as $item_id => $values ) {
			$ctr++;

			if ( ! $values['data']->needs_shipping() ) {
				$this->debug( sprintf( __( 'Product #%d is virtual. Skipping.', 'woocommerce-shipping-ups' ), $ctr ) );
				continue;
			}

			if ( ! $values['data']->get_weight() ) {
				$this->debug( sprintf( __( 'Product #%d is missing weight. Aborting.', 'woocommerce-shipping-ups' ), $ctr ), 'error' );
				return;
			}

			// get package weight
			$weight = wc_get_weight( $values['data']->get_weight(), $this->weight_unit );

			// get package dimensions
			if ( $values['data']->length && $values['data']->height && $values['data']->width ) {

				$dimensions = array( number_format( wc_get_dimension( $values['data']->length, $this->dim_unit ), 2, '.', '' ),
									 number_format( wc_get_dimension( $values['data']->height, $this->dim_unit ), 2, '.', '' ),
									 number_format( wc_get_dimension( $values['data']->width, $this->dim_unit ), 2, '.', '' ) );

				sort( $dimensions );

			}

			// get quantity in cart
			$cart_item_qty = $values['quantity'];
			// get weight, or 1 if less than 1 lbs.
			// $_weight = ( floor( $weight ) < 1 ) ? 1 : $weight;

			$request  = '<Package>' . "\n";
			$request .= '	<PackagingType>' . "\n";
			$request .= '		<Code>02</Code>' . "\n";
			$request .= '		<Description>Package/customer supplied</Description>' . "\n";
			$request .= '	</PackagingType>' . "\n";
			$request .= '	<Description>Rate</Description>' . "\n";

			if ( $values['data']->length && $values['data']->height && $values['data']->width ) {
				$request .= '	<Dimensions>' . "\n";
				$request .= '		<UnitOfMeasurement>' . "\n";
				$request .= '	 		<Code>' . $this->dim_unit . '</Code>' . "\n";
				$request .= '		</UnitOfMeasurement>' . "\n";
				$request .= '		<Length>' . round( $dimensions[2] ). '</Length>' . "\n";
				$request .= '		<Width>' . round( $dimensions[1] ) . '</Width>' . "\n";
				$request .= '		<Height>' . round( $dimensions[0] ). '</Height>' . "\n";
				$request .= '	</Dimensions>' . "\n";
			}

			$request .= '	<PackageWeight>' . "\n";
			$request .= '		<UnitOfMeasurement>' . "\n";
			$request .= '			<Code>' . $this->weight_unit . '</Code>' . "\n";
			$request .= '		</UnitOfMeasurement>' . "\n";
			$request .= '		<Weight>' . $weight . '</Weight>' . "\n";
			$request .= '	</PackageWeight>' . "\n";
			if( $this->insuredvalue ) {
				$request .= '	<PackageServiceOptions>' . "\n";
				// InsuredValue
				if( $this->insuredvalue ) {

					$request .= '		<InsuredValue>' . "\n";
					$request .= '			<CurrencyCode>' . get_woocommerce_currency() . '</CurrencyCode>' . "\n";
					$request .= '			<MonetaryValue>' . $values['data']->price . '</MonetaryValue>' . "\n";
					$request .= '		</InsuredValue>' . "\n";
				}

				$request .= '	</PackageServiceOptions>' . "\n";
			}
			$request .= '</Package>' . "\n";

			for ( $i=0; $i < $cart_item_qty ; $i++) {
				$requests[] = $request;
			}
		}

		return $requests;
	}

    /**
     * Convert dimentions
     */
    public function get_packaging_dimension( $dim, $from_unit = 'in', $to_unit = null ) {
		if ( empty( $to_unit ) ) {
			$to_unit = strtolower( $this->dim_unit );
		}

		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.5', '>=' ) ) {
			return wc_get_weight( $dim, $to_unit, $from_unit );
		}

		// back compat below

		// Unify all units to cm first
		if ( $from_unit !== $to_unit ) {

			switch ( $from_unit ) {
				case 'in':
					$dim *= 2.54;
				break;
				case 'm':
					$dim *= 100;
				break;
				case 'mm':
					$dim *= 0.1;
				break;
				case 'yd':
					$dim *= 91.44;
				break;
			}

			// Output desired unit
			switch ( $to_unit ) {
				case 'in':
					$dim *= 0.3937;
				break;
				case 'm':
					$dim *= 0.01;
				break;
				case 'mm':
					$dim *= 10;
				break;
				case 'yd':
					$dim *= 0.010936133;
				break;
			}
		}
		return ( $dim < 0 ) ? 0 : $dim;
	}

    /**
     * Convert weights
     */
    public function get_packaging_weight( $weight, $from_unit = 'lbs', $to_unit = null ) {
		if ( empty( $to_unit ) ) {
			$to_unit = strtolower( $this->weight_unit );
		}

		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.5', '>=' ) ) {
			return wc_get_weight( $weight, $to_unit, $from_unit );
		}

		// back compat below

		//Unify all units to kg first
		if ( $from_unit !== $to_unit ) {

			switch ( $from_unit ) {
				case 'g':
					$weight *= 0.001;
				break;
				case 'lbs':
					$weight *= 0.453592;
				break;
				case 'oz':
					$weight *= 0.0283495;
				break;
			}

			// Output desired unit
			switch ( $to_unit ) {
				case 'g':
					$weight *= 1000;
				break;
				case 'lbs':
					$weight *= 2.20462;
				break;
				case 'oz':
					$weight *= 35.274;
				break;
			}
		}
		return ( $weight < 0 ) ? 0 : $weight;
	}

	/**
	 * box_shipping function.
	 *
	 * @access private
	 * @param mixed $package
	 * @return void
	 */
	private function box_shipping( $package ) {

		$requests = array();

		if ( ! class_exists( 'WC_Boxpack' ) ) {
			include_once 'box-packer/class-wc-boxpack.php';
		}

		$boxpack = new WC_Boxpack();

		// Add Standard UPS boxes
		if ( ! empty( $this->ups_packaging )  ) {
			foreach ( $this->ups_packaging as $key => $box_code ) {
				$box    = $this->packaging[ $box_code ];
				$newbox = $boxpack->add_box( $this->get_packaging_dimension( $box['length'] ), $this->get_packaging_dimension( $box['width'] ), $this->get_packaging_dimension( $box['height'] ) );
				$newbox->set_inner_dimensions( $this->get_packaging_dimension( $box['length'] ), $this->get_packaging_dimension( $box['width'] ), $this->get_packaging_dimension( $box['height'] ) );
                $newbox->set_id( $box['name'] );

				if ( $box['weight'] ) {
					$newbox->set_max_weight( $this->get_packaging_weight( $box['weight'] ) );
				}
			}
		}

		// Define boxes
		if ( ! empty( $this->boxes ) ) {
			foreach ( $this->boxes as $box ) {
				$newbox = $boxpack->add_box( $box['outer_length'], $box['outer_width'], $box['outer_height'], $box['box_weight'] );
				$newbox->set_inner_dimensions( $box['inner_length'], $box['inner_width'], $box['inner_height'] );

				if ( $box['max_weight'] ) {
					$newbox->set_max_weight( $box['max_weight'] );
				}
			}
		}

		// Add items
		$ctr = 0;
		foreach ( $package['contents'] as $item_id => $values ) {
			$ctr++;

			if ( ! $values['data']->needs_shipping() ) {
				$this->debug( sprintf( __( 'Product #%d is virtual. Skipping.', 'woocommerce-shipping-ups' ), $ctr ) );
				continue;
			}

			if ( $values['data']->length && $values['data']->height && $values['data']->width && $values['data']->weight ) {

				$dimensions = array( $values['data']->length, $values['data']->height, $values['data']->width );

				for ( $i = 0; $i < $values['quantity']; $i ++ ) {
					$boxpack->add_item(
						number_format( wc_get_dimension( $dimensions[2], strtolower( $this->dim_unit ) ), 2, '.', ''),
						number_format( wc_get_dimension( $dimensions[1], strtolower( $this->dim_unit ) ), 2, '.', ''),
						number_format( wc_get_dimension( $dimensions[0], strtolower( $this->dim_unit ) ), 2, '.', ''),
						number_format( wc_get_weight( $values['data']->get_weight(), strtolower( $this->weight_unit ) ), 2, '.', ''),
						$values['data']->get_price()
					);
				}

			} else {
				$this->debug( sprintf( __( 'UPS Parcel Packing Method is set to Pack into Boxes. Product #%d is missing dimensions. Aborting.', 'woocommerce-shipping-ups' ), $ctr ), 'error' );
				return;
			}
		}

		// Pack it
		$boxpack->pack();

		// Get packages
		$box_packages = $boxpack->get_packages();

		$ctr=0;
		foreach ( $box_packages as $key => $box_package ) {
			$ctr++;

			$this->debug( "PACKAGE " . $ctr . " (" . $key . ")\n<pre>" . print_r( $box_package,true ) . "</pre>" );

			$request  = '<Package>' . "\n";
			$request .= '	<PackagingType>' . "\n";
			$request .= '		<Code>02</Code>' . "\n";
			$request .= '		<Description>Package/customer supplied</Description>' . "\n";
			$request .= '	</PackagingType>' . "\n";
			$request .= '	<Description>Rate</Description>' . "\n";

			$request .= '	<Dimensions>' . "\n";
			$request .= '		<UnitOfMeasurement>' . "\n";
			$request .= '	 		<Code>IN</Code>' . "\n";
			$request .= '		</UnitOfMeasurement>' . "\n";
			$request .= '		<Length>' . round( $this->get_packaging_dimension( $box_package->length, strtolower( $this->dim_unit ), 'in' ) ) . '</Length>' . "\n";
			$request .= '		<Width>' . round( $this->get_packaging_dimension( $box_package->width, strtolower( $this->dim_unit ), 'in' ) ) . '</Width>' . "\n";
			$request .= '		<Height>' . round( $this->get_packaging_dimension( $box_package->height, strtolower( $this->dim_unit ), 'in' ) ) . '</Height>' . "\n";
			$request .= '	</Dimensions>' . "\n";

			$request .= '	<PackageWeight>' . "\n";
			$request .= '		<UnitOfMeasurement>' . "\n";
			$request .= '			<Code>LBS</Code>' . "\n";
			$request .= '		</UnitOfMeasurement>' . "\n";
			$request .= '		<Weight>' . $this->get_packaging_weight( $box_package->weight, strtolower( $this->weight_unit ), 'lbs' ) . '</Weight>' . "\n";
			$request .= '	</PackageWeight>' . "\n";
			if( $this->insuredvalue ) {
				$request .= '	<PackageServiceOptions>' . "\n";
				// InsuredValue
				if( $this->insuredvalue ) {

					$request .= '		<InsuredValue>' . "\n";
					$request .= '			<CurrencyCode>' . get_woocommerce_currency() . '</CurrencyCode>' . "\n";
					$request .= '			<MonetaryValue>' . $box_package->value . '</MonetaryValue>' . "\n";
					$request .= '		</InsuredValue>' . "\n";
				}

				$request .= '	</PackageServiceOptions>' . "\n";
			}
			$request .= '</Package>' . "\n";

			$requests[] = $request;

		}

		return $requests;
	}

}
