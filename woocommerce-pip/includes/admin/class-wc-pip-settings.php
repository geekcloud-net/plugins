<?php
/**
 * WooCommerce Print Invoices/Packing Lists
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce Print
 * Invoices/Packing Lists to newer versions in the future. If you wish to
 * customize WooCommerce Print Invoices/Packing Lists for your needs please refer
 * to http://docs.woocommerce.com/document/woocommerce-print-invoice-packing-list/
 *
 * @package   WC-Print-Invoices-Packing-Lists/Admin/Settings
 * @author    SkyVerge
 * @copyright Copyright (c) 2011-2018, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * PIP Settings Admin class
 *
 * Loads / saves the admin settings page
 *
 * @since 3.0.0
 */
class WC_PIP_Settings extends WC_Settings_Page {


	/**
	 * Add various admin hooks/filters
	 *
	 * @since 3.0.0
	 */
	public function __construct() {

		$this->id    = 'pip';
		$this->label = __( 'Invoices/Packing Lists', 'woocommerce-pip' );

		parent::__construct();

		// reset invoice number counter when admin has opted to do so
		add_action( 'woocommerce_update_options_pip_invoice', array( $this, 'maybe_reset_invoice_counter' ) );
	}


	/**
	 * Get sections
	 *
	 * @since 3.0.0
	 * @return array
	 */
	public function get_sections() {

		$sections = array(
			''             => __( 'General', 'woocommerce-pip' ),
			'invoice'      => __( 'Invoice', 'woocommerce-pip' ),
			'packing_list' => __( 'Packing List', 'woocommerce-pip' ),
			'pick_list'    => __( 'Pick List', 'woocommerce-pip' ),
		);

		/**
		 * Filters the plugin's settings sections.
		 *
		 * @since 3.0.0
		 * @param array $sections sections
		 * @param \WC_PIP_Settings $this settings class instance
		 */
		return apply_filters( 'wc_pip_get_settings_sections', $sections, $this );
	}


	/**
	 * Get settings array for the current section
	 *
	 * @since 3.0.0
	 * @return array
	 */
	public function get_settings() {
		global $current_section;

		// default to general
		if ( ! $current_section ) {
			$current_section = 'general';
		}

		$method           = "get_{$current_section}_settings";
		$settings         = is_callable( array( $this, $method ) ) ? $this->$method() : array();
		$hide_save_button = 'general' === $current_section;

		/**
		 * Filters if the plugin's settings save button should be hidden.
		 *
		 * Some default settings page, like 'General' do not need one
		 *
		 * @since 3.0.0
		 * @param bool $hide Whether to hide or not the save button, default false (do not hide), true on general tab
		 * @param string $current_section the current settings section
		 */
		$hide_save_button = apply_filters( 'wc_pip_settings_hide_save_button', $hide_save_button, $current_section );

		if ( true === $hide_save_button ) {
			$GLOBALS['hide_save_button'] = true;
		}

		/**
		 * Filters the plugin's settings.
		 *
		 * @since 3.0.0
		 * @param array $settings Array of the plugin settings
		 * @param string $section Current section
		 * @param \WC_PIP_Settings $wc_pip_settings settings class instance
		 */
		return apply_filters( 'wc_pip_settings', $settings, $current_section, $this );
	}


	/**
	 * Return general settings. Note that the customizer classes hooks into
	 * this method's filter to add the "Customize" button and most integrations
	 * will want to add their custom settings here as well.
	 *
	 * @since 3.0.0
	 * @return array
	 */
	protected function get_general_settings() {

		$settings = array(

			// section start
			array(
				'name' => __( 'Emails', 'woocommerce-pip' ),
				'type' => 'title',
				/* translators: Placeholders: %1$s - opening HTML <a> anchor tag, %2$s closing HTML </a> anchor tag */
				'desc' => sprintf( __( 'You can configure Invoices/Packing Lists %1$semail settings here%2$s.', 'woocommerce-pip' ), '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=email' ) . '">', '</a>' ),
			),

			// section end
			array(
				'type' => 'sectionend'
			),

		);

		/**
		 * Filters the general settings.
		 *
		 * @since 3.0.0
		 * @param array $settings
		 * @param \WC_PIP_Settings $this settings class instance
		 * @return array
		 */
		return apply_filters( 'wc_pip_general_settings', $settings, $this );
	}


	/**
	 * Return the invoice settings array
	 *
	 * @since 3.0.0
	 * @return array
	 */
	protected function get_invoice_settings() {

		$isset_invoice_count   = is_numeric( get_option( 'wc_pip_invoice_number_start' ) );
		$reset_invoice_counter = true === $isset_invoice_count ? array(
			'id'       => 'wc_pip_invoice_number_reset_counter',
			'name'     => __( 'Reset counter', 'woocommerce-pip' ),
			'desc'     => __( 'Reset the starting invoice number.', 'woocommerce-pip' ),
			'desc_tip' => __( 'Tick this if you want to edit the invoice start number.', 'woocommerce-pip' ),
			'type'     => 'checkbox',
			'class'    => 'hide-if-use-order-number',
		) : array();

		$merge_tags = '
				<ul>' .
					/* translators: Placeholder: %s - merge tag */
					'<li>' . sprintf( __( '%s Day of the month without leading zeros', 'woocommerce-pip' ), '<code>{D}</code>' ) . '</li>' .
					/* translators: Placeholder: %s - merge tag */
					'<li>' . sprintf( __( '%s Day of the month with leading zeros (2 digits)', 'woocommerce-pip' ), '<code>{DD}</code>' ) . '</li>' .
					/* translators: Placeholder: %s - merge tag */
					'<li>' . sprintf( __( '%s Month, without leading zeros', 'woocommerce-pip' ), '<code>{M}</code>' ) . '</li>' .
					/* translators: Placeholder: %s - merge tag */
					'<li>' . sprintf( __( '%s Month, with leading zeros (2 digits)', 'woocommerce-pip' ), '<code>{MM}</code>' ) . '</li>' .
					/* translators: Placeholder: %s - merge tag */
					'<li>' . sprintf( __( '%s Year (2 digits)', 'woocommerce-pip' ), '<code>{YY}</code>' ) . '</li>' .
					/* translators: Placeholder: %s - merge tag */
					'<li>' . sprintf( __( '%s Year (4 digits)', 'woocommerce-pip' ), '<code>{YYYY}</code>' ) . '</li>' .
					/* translators: Placeholder: %s - merge tag */
					'<li>' . sprintf( __( '%s 24-hour format of an hour without leading zeros', 'woocommerce-pip' ), '<code>{H}</code>' ) . '</li>' .
					/* translators: Placeholder: %s - merge tag */
					'<li>' . sprintf( __( '%s 24-hour format of an hour with leading zeros (2 digits)', 'woocommerce-pip' ), '<code>{HH}</code>' ) . '</li>' .
					/* translators: Placeholder: %s - merge tag */
					'<li>' . sprintf( __( '%s Minutes with leading zeros', 'woocommerce-pip' ), '<code>{N}</code>' ) . '</li>' .
					/* translators: Placeholder: %s - merge tag */
					'<li>' . sprintf( __( '%s Seconds with leading zeros', 'woocommerce-pip' ), '<code>{S}</code>' ) . '</li>
				</ul>
			';

		$settings = array(

			// section start
			array(
				'name' => __( 'Invoice Appearance', 'woocommerce-pip' ),
				'type' => 'title',
			),

			// display shipping method
			array(
				'id'      => 'wc_pip_invoice_show_shipping_method',
				'name'    => __( 'Show shipping method', 'woocommerce-pip' ),
				'desc'    => __( 'Enable if you want to display shipping method for the invoice order.', 'woocommerce-pip' ),
				'default' => 'yes',
				'type'    => 'checkbox',
			),

			// display customer details
			array(
				'id'      => 'wc_pip_invoice_show_customer_details',
				'name'    => __( 'Show customer details', 'woocommerce-pip' ),
				'desc'    => __( 'Enable if you want to display customer details.', 'woocommerce-pip' ),
				'default' => 'yes',
				'type'    => 'checkbox',
			),

			// display customer note
			array(
				'id'      => 'wc_pip_invoice_show_customer_note',
				'name'    => __( 'Show customer provided note', 'woocommerce-pip' ),
				'desc'    => __( 'Enable if you want to display the customer provided note.', 'woocommerce-pip' ),
				'default' => 'yes',
				'type'    => 'checkbox',
			),

			// display used coupons
			array(
				'id'      => 'wc_pip_invoice_show_coupons',
				'name'    => __( 'Show coupons used in order', 'woocommerce-pip' ),
				'desc'    => __( 'Enable if you want to display a list of coupons applied to the order.', 'woocommerce-pip' ),
				'default' => 'yes',
				'type'    => 'checkbox',
			),

			// show item prices tax exclusive (EU compliance)
			array(
				'id'       => 'wc_pip_invoice_show_tax_exclusive_item_prices',
				'name'     => __( 'Show item prices excluding taxes', 'woocommerce-pip' ),
				'desc'     => __( 'Enable if you want to display item prices exclusive of tax.', 'woocommerce-pip' ),
				'desc_tip' => __( 'Overrides the store tax display settings when displaying item prices in invoices.', 'woocommerce-pip' ),
				'default'  => 'no',
				'type'     => 'checkbox',
			),

			// section end
			array(
				'type' => 'sectionend'
			),

			// section start
			array(
				'name' => __( 'Invoice Number Generation', 'woocommerce-pip' ),
				'type' => 'title',
			),

			// use order number as invoice number
			array(
				'id'      => 'wc_pip_use_order_number',
				'name'    => __( 'Use Order Number as Invoice Number', 'woocommerce-pip' ),
				'desc'    => __( 'Enable this to use the order number as the invoice number. Disable to auto-generate the invoice number based on a starting number of your choice.', 'woocommerce-pip' ),
				'type'    => 'checkbox',
				'default' => 'yes',
				'class'   => 'hide-options-if-checked',
			),

			// invoice number start
			array(
				'id'                => 'wc_pip_invoice_number_start',
				'name'              => __( 'Invoice Number Start', 'woocommerce-pip' ),
				'desc_tip'          => __( 'Set the starting invoice number.', 'woocommerce-pip' ),
				'type'              => 'number',
				'class'             => 'hide-if-use-order-number',
				'default'           => '1',
				'custom_attributes' => array(
					'min'      => '1',
					'step'     => '1',
					'readonly' => 'readonly',
				),
			),

			// option to reset the number start
			$reset_invoice_counter,

			// invoice number leading zeros
			array(
				'id'                => 'wc_pip_invoice_minimum_digits',
				'name'              => __( 'Invoice Number Minimum Digits', 'woocommerce-pip' ),
				'desc_tip'          => __( 'Adds leading zeros to the invoice number count to keep the number of characters in the invoice number consistent. Leave to 1 to ignore the setting.', 'woocommerce-pip' ),
				'type'              => 'number',
				'class'             => 'small-text hide-if-use-order-number',
				'default'           => '1',
				'custom_attributes' => array(
					'min'  => '1',
					'step' => '1',
				),
			),

			// invoice number prefix
			array(
				'id'       => 'wc_pip_invoice_number_prefix',
				'name'     => __( 'Invoice Number Prefix', 'woocommerce-pip' ),
				'desc_tip' => __( 'Set your custom invoice number prefix.', 'woocommerce-pip' ),
				/* translators: Placeholder: %s - List of merge tags */
				'desc'     => sprintf( __( 'You may also use the following merge tags: %s', 'woocommerce-pip' ), $merge_tags ),
				'type'     => 'text',
			),

			// invoice number suffix
			array(
				'id'       => 'wc_pip_invoice_number_suffix',
				'name'     => __( 'Invoice Number Suffix', 'woocommerce-pip' ),
				'desc_tip' => __( 'Set your custom invoice number suffix.', 'woocommerce-pip' ),
				/* translators: Placeholder: %s - List of merge tags */
				'desc'     => sprintf( __( 'You may also use the following merge tags: %s', 'woocommerce-pip' ), $merge_tags ),
				'type'     => 'text',
			),

			// section end
			array(
				'type' => 'sectionend'
			),
		);

		/**
		 * Filters the invoice settings.
		 *
		 * @since 3.0.0
		 * @param array $settings
		 * @param \WC_PIP_Settings $this settings class instance
		 */
		return apply_filters( 'wc_pip_invoice_settings', $settings, $this );
	}


	/**
	 * Return the packing list settings array
	 *
	 * @since 3.0.0
	 * @return array
	 */
	protected function get_packing_list_settings() {

		$settings = array(

			// section start
			array(
				'name' => __( 'Packing List Appearance', 'woocommerce-pip' ),
				'type' => 'title'
			),

			// display customer details
			array(
				'id'      => 'wc_pip_packing_list_show_customer_details',
				'name'    => __( 'Show customer details', 'woocommerce-pip' ),
				'desc'    => __( 'Enable if you want to display customer details.', 'woocommerce-pip' ),
				'default' => 'no',
				'type'    => 'checkbox',
			),

			// display customer note
			array(
				'id'      => 'wc_pip_packing_list_show_customer_note',
				'name'    => __( 'Show customer note', 'woocommerce-pip' ),
				'desc'    => __( 'Enable if you want to display the customer note.', 'woocommerce-pip' ),
				'default' => 'yes',
				'type'    => 'checkbox',
			),

			// display terms and conditions
			array(
				'id'      => 'wc_pip_packing_list_show_terms_and_conditions',
				'name'    => __( 'Show terms and conditions', 'woocommerce-pip' ),
				'desc'    => __( 'Enable if you want to display terms and conditions or return policy on packing lists.', 'woocommerce-pip' ),
				'default' => 'no',
				'type'    => 'checkbox',
			),

			// display footer information
			array(
				'id'      => 'wc_pip_packing_list_show_footer',
				'name'    => __( 'Show footer', 'woocommerce-pip' ),
				'desc'    => __( 'Enable if you want to display footer information on packing lists.', 'woocommerce-pip' ),
				'default' => 'no',
				'type'    => 'checkbox',
			),

			// exclude virtual items from packing lists
			array(
				'id'      => 'wc_pip_packing_list_exclude_virtual_items',
				'name'    => __( 'Exclude Virtual Items', 'woocommerce-pip' ),
				'desc'    => __( 'Exclude virtual items from showing on packing lists.', 'woocommerce-pip' ),
				'default' => 'no',
				'type'    => 'checkbox',
			),

			// section end
			array(
				'type' => 'sectionend',
			),

		);

		/**
		 * Filters the packing list settings.
		 *
		 * @since 3.0.0
		 * @param array $settings
		 * @param \WC_PIP_Settings $this settings class instance
		 */
		return apply_filters( 'wc_pip_packing_list_settings', $settings, $this );
	}


	/**
	 * Reset the invoice number counter if the admin
	 * has checked the option to do so
	 *
	 * @since 3.0.0
	 */
	public function maybe_reset_invoice_counter() {

		if ( isset( $_POST['wc_pip_invoice_number_reset_counter'] ) && 1 === absint( $_POST['wc_pip_invoice_number_reset_counter'] ) ) {
			update_option( 'wc_pip_invoice_number_start', max( 0, absint( $_POST['wc_pip_invoice_number_start'] ) ) );
		}
	}


	/**
	 * Return the pick list settings array.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	protected function get_pick_list_settings() {

		$settings = array(

			// section start
			array(
				'name' => __( 'Pick List Appearance', 'woocommerce-pip' ),
				'type' => 'title'
			),

			// pick list template selection
			array(
				'title'   => __( 'Pick List Output', 'woocommerce-pip' ),
				'desc'    => __( 'This controls grouped by for selected orders on pick list.', 'woocommerce-pip' ),
				'id'      => 'wc_pip_pick_list_output_type',
				'default' => 'order',
				'type'    => 'radio',
				'options' => array(
					'order'    => __( 'Groups items by order for selected orders', 'woocommerce-pip' ),
					'category' => __( 'Groups items by category for selected orders', 'woocommerce-pip' ),
				),
				'autoload'        => false,
				'desc_tip'        => true,
				'show_if_checked' => 'option',
			),

			// exclude virtual items
			array(
				'id'      => 'wc_pip_pick_list_exclude_virtual_items',
				'name'    => __( 'Exclude Virtual Items', 'woocommerce-pip' ),
				'desc'    => __( 'Exclude virtual items from showing on pick lists.', 'woocommerce-pip' ),
				'default' => 'no',
				'type'    => 'checkbox',
			),

			// section end
			array(
				'type' => 'sectionend',
			),

		);

		/**
		 * Filters the pick list settings.
		 *
		 * @since 3.3.0
		 * @param array $settings
		 * @param \WC_PIP_Settings $this settings class instance
		 */
		return apply_filters( 'wc_pip_pick_list_settings', $settings, $this );
	}


}
