<?php
/**
 * WooCommerce POS General Settings
 *
 * @author    Actuality Extensions
 * @package   WoocommercePointOfSale/Classes/settings
 * @category	Class
 * @since     0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_POS_Admin_Settings_Tax' ) ) :

/**
 * WC_POS_Admin_Settings_Tax
 */
class WC_POS_Admin_Settings_Tax extends WC_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'tax_pos';
		$this->label = __( 'Tax', 'woocommerce' );

		add_filter( 'wc_pos_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'wc_pos_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'wc_pos_settings_save_' . $this->id, array( $this, 'save' ) );

	}

	/**
	 * Get settings array
	 *
	 * @return array
	 */
	public function get_settings() {
		global $woocommerce;
		$enable_taxes = get_option('woocommerce_calc_taxes', 'no');
		$class = 'wc-enhanced-select';
		if($enable_taxes != 'yes'){
			update_option('woocommerce_pos_tax_calculation', 'disabled');
			$class =  'disabled_select';
		}
			$tax_calculation = array(
					'name' => __( 'Tax Calculation', 'wc_point_of_sale' ),
					'id'   => 'woocommerce_pos_tax_calculation',
					'css'  => '',
					'desc_tip' => __( 'Enables the calculation of tax using the WooCommerce configurations.', 'wc_point_of_sale' ), 
					'std'  => '',
					'type' => 'select',
					'class'		=> $class,
					'options' => array(
							'enabled'  => __( 'Enabled (using WooCommerce configurations)', 'wc_point_of_sale' ),
							'disabled' => __( 'Disabled', 'wc_point_of_sale' ),
						)
				);
			$tax_based_on = array(
					'name' => __( 'Calculate Tax Based On', 'wc_point_of_sale' ),
					'id'   => 'woocommerce_pos_calculate_tax_based_on',
					'css'  => '',
					'std'  => '',
					'class'   => 'wc-enhanced-select',
					'desc_tip' => __( 'This option determines which address used to calculate tax.', 'wc_point_of_sale' ),
					'type' => 'select',
					'default' => 'outlet',
					'options' => array(
							'default'  => __( 'Default WooCommerce', 'wc_point_of_sale' ),
							'shipping' => __( 'Customer shipping address', 'wc_point_of_sale' ),
							'billing'  => __( 'Customer billing address', 'wc_point_of_sale' ),
							'base'     => __( 'Shop base address', 'wc_point_of_sale' ),
							'outlet'   => __( 'Outlet address', 'wc_point_of_sale' ),
						)
				);

			$default_customer_address = array(
					'name' => __( 'Default Customer Address', 'wc_point_of_sale' ),
					'id'   => 'woocommerce_pos_tax_default_customer_address',
					'type' => 'select',
					'class'   => 'wc-enhanced-select',
					'desc_tip' => __( 'This option determines which address used to calculate tax for the default customer such as Guest.', 'wc_point_of_sale' ),
					'default' => 'outlet',
					'options' => array(
							'no_address' => __( 'No address', 'wc_point_of_sale' ),
							'base'       => __( 'Shop base address', 'wc_point_of_sale' ),
							'outlet'     => __( 'Outlet address', 'wc_point_of_sale' ),
						)
				);

		return apply_filters( 'woocommerce_point_of_sale_tax_settings_fields', array(

			array( 'title' => __( 'Tax Options', 'wc_point_of_sale' ), 'type' => 'title', 'desc' => '', 'id' => 'tax_pos_options' ),
			$tax_calculation,
			$tax_based_on,
			$default_customer_address,
			array( 'type' => 'sectionend', 'id' => 'tax_pos_options'),

		) ); // End general settings

	}

	/**
	 * Save settings
	 */
	public function save() {
		$settings = $this->get_settings();

		WC_POS_Admin_Settings::save_fields( $settings );
	}

}

endif;

return new WC_POS_Admin_Settings_Tax();
