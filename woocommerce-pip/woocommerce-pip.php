<?php
/**
 * Plugin Name: WooCommerce Print Invoices/Packing Lists
 * Plugin URI: http://www.woocommerce.com/products/print-invoices-packing-lists/
 * Description: Customize and print invoices and packing lists for WooCommerce orders from the WordPress admin
 * Author: SkyVerge
 * Author URI: http://www.woocommerce.com/
 * Version: 3.5.0
 * Text Domain: woocommerce-pip
 * Domain Path: /i18n/languages/
 *
 * Copyright: (c) 2011-2018, SkyVerge, Inc. (info@skyverge.com)
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   WC-Print-Invoices-Packing-Lists
 * @author    SkyVerge
 * @category  Plugin
 * @copyright Copyright (c) 2011-2018, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 *
 * Woo: 18666:465de1126817cdfb42d97ebca7eea717
 * WC requires at least: 2.6.14
 * WC tested up to: 3.3.4
 */

defined( 'ABSPATH' ) or exit;

// Required functions
if ( ! function_exists( 'woothemes_queue_update' ) ) {
	require_once( plugin_dir_path( __FILE__ ) . 'woo-includes/woo-functions.php' );
}

// Plugin updates
woothemes_queue_update( plugin_basename( __FILE__ ), '465de1126817cdfb42d97ebca7eea717', '18666' );

// WC active check
if ( ! is_woocommerce_active() ) {
	return;
}

// Required library class
if ( ! class_exists( 'SV_WC_Framework_Bootstrap' ) ) {
	require_once( plugin_dir_path( __FILE__ ) . 'lib/skyverge/woocommerce/class-sv-wc-framework-bootstrap.php' );
}

SV_WC_Framework_Bootstrap::instance()->register_plugin( '4.9.0', __( 'WooCommerce Print Invoices/Packing Lists', 'woocommerce-pip' ), __FILE__, 'init_woocommerce_pip', array(
	'minimum_wc_version'   => '2.6.14',
	'minimum_wp_version'   => '4.4',
	'backwards_compatible' => '4.4',
) );

function init_woocommerce_pip() {

/**
 * # WooCommerce Print Invoices/Packing Lists Main Plugin Class
 *
 * ## Plugin Overview
 *
 * This plugin allows the store admin to print ane email invoices, packing lists,
 * and pick lists for WooCommerce orders without the need for thrid-party software.
 * Additionally, customers can view and optionally print invoices from the
 * "My Account" page.
 *
 * Invoices and packing lists are objects referred to as 'documents'. PIP provides
 * an abstract document model (WC_PIP_Document) that can be extended by specific
 * document objects. Many document object properties are filterable, to offer
 * customization options and integration with other WooCommerce extensions
 * or third party plugins and themes.
 *
 * ## Features
 *
 * + Print or email invoices, packing lists, and pick lists from the Orders admin (individually and in bulk)
 * + Allow customers to view and print invoices from the "My Account" page
 *
 * ## Admin Considerations
 *
 * The plugin adds bulk actions to the orders screen as well as the individual
 * view/edit order screen.
 *
 * Actions (bulk and individual) include opening a new browser tab with the corresponding
 * invoice(s) or packing list(s) for the matching orders for printing or review. Or send
 * by email the invoice to the customer or the packing list to shop manager(s) or other
 * addresses as specified in the plugin settings.
 *
 * A special action is added to the list of bulk actions, which can be used only with
 * multiple orders in bulk and consists of a packing list wrapping many orders at once.
 *
 * The documents managed by PIP use a single template, split into multiple partials and
 * managed by standard WooCommerce template functions (e.g. `wc_get_template()`). The
 * template files provide several hooks. There is the added possibility to edit
 * some of the template appearance using the built-in WordPress Customizer and live
 * preview the changes.
 *
 * The plugin adds a settings page as WooCommerce settings tab. The page has tabbed
 * subsections:
 *
 * 1. General - These options tweak the appearance of the document template (using Customizer)
 * 2. Invoices - Options related to invoices, mostly to customize invoice numbers
 * 3. Packing lists - Tweaks to exclude certain product types/items in list
 *
 * The email settings have been placed within the WooCommerce Email settings.
 *
 * ## Frontend Considerations
 *
 * The plugin adds a "View Invoice" button which performs a similar action as in the admin
 * to open a browser tab with an invoice for printing or review. Only the user connected
 * to the matching order is allowed to see the invoice.
 *
 * ## Database
 *
 * This plugin stores its settings using WooCommerce Settings API. Additional options are
 * set during the upgrade and install routines. An additional option `wc_pip_invoice_number_start`
 * is used to set the start number for invoices. This is set during the generation of
 * the first invoice number.
 *
 * ### Global Options
 *
 * The following are options meant to tweak template logic or elaborate dynamic content or data such as invoice numbers.
 *
 * + `wc_pip_invoice_number_start` - Stores the global invoice number counter
 * + `wc_pip_use_order_number` - Option to use order numbers for invoice numbers
 * + `wc_pip_invoice_minimum_digits` - Option to set leading zeros in invoice numbers
 * + `wc_pip_invoice_number_prefix` - Optional invoice number prefix
 * + `wc_pip_invoice_number_suffix` - Optional invoice number suffix
 * + `wc_pip_invoice_show_shipping_method` - When set it will show the shipping method in invoices
 * + `wc_pip_invoice_show_customer_details` - When set it will show customer details in invoices
 * + `wc_pip_packing_list_show_customer_details - When set it will show customer details in packing lists
 * + `wc_pip_invoice_show_customer_note` - When set it will show the customer note in invoices
 * + `wc_pip_packing_list_show_customer_note` - When set it will show the customer note in packing lists
 * + `wc_pip_invoice_show_coupons` - When set it will display customer used coupons in invoices
 * + `wc_pip_packing_list_exclude_virtual_items` - When set it will exclude virtual items from showing in packing lists
 * + `wc_pip_packing_list_show_terms_and_conditions` - When set it will show the terms and conditions also in packing lists
 * + `wc_pip_packing_list_show_footer` - When set it will show the footer content also in packing lists
 *
 * ### Customizer Options
 *
 * The following options contain template information.
 *
 * + `wc_pip_company_logo` - The company logo to be shown in templates
 * + `wc_pip_company_logo_max_width` - Sets the max width of the company logo
 * + `wc_pip_company_name` - The company name to be shown when logo is not used, defaults to site name
 * + `wc_pip_company_url` - The company website, defaults to site url
 * + `wc_pip_company_extra` - Additional information used as company name subheading
 * + `wc_pip_company_title_align` - Company name and extra information text positioning in template
 * + `wc_pip_company_address` - Company address information
 * + `wc_pip_company_address_align` - Company address text positioning in template
 * + `wc_pip_body_font_size` - Template body font size
 * + `wc_pip_heading_font_size` - Template heading font size
 * + `wc_pip_link_color` - Anchor links color in templates
 * + `wc_pip_headings_color` - Color of headings in templates
 * + `wc_pip_table_head_bg_color` - Background color of main table head
 * + `wc_pip_table_head_color` - Foreground text color of main table head
 * + `wc_pip_header` - Content to be printed in template header
 * + `wc_pip_return_policy` - Information containing terms and conditions, returns policy, etc.
 * + `wc_pip_return_policy_fine_print` - Option to display the return policy in smaller font size
 * + `wc_pip_footer` - Additional footer content
 * + `wc_pip_custom_styles` - CSS styles to be added to the templates
 *
 * ### Other Options
 *
 * + `woocommerce_pip_upgraded_to_3_0_0` - Internal flag to mark an installation upgrade from earlier versions of the plugin
 *
 * ### Order Meta
 *
 * + `_pip_invoice_number` - Stores an invoice number in the the WC Order post meta
 *
 *
 * @since 3.0.0
 */
class WC_PIP extends SV_WC_Plugin {


	/** string version number */
	const VERSION = '3.5.0';

	/** @var WC_PIP single instance of this plugin */
	protected static $instance;

	/** string the plugin id */
	const PLUGIN_ID = 'pip';

	/** @var \WC_PIP_Document instance */
	protected $document = null;

	/** @var \WC_PIP_Handler instance */
	protected $handler;

	/** @var \WC_PIP_Print instance */
	protected $print;

	/** @var \WC_PIP_Emails instance */
	protected $emails;

	/** @var \WC_PIP_Ajax instance */
	protected $ajax;

	/** @var \WC_PIP_Admin instance */
	protected $admin;

	/** @var \WC_PIP_Customizer instance */
	protected $customizer;

	/** @var \WC_PIP_Orders_Admin instance */
	protected $orders_admin;

	/** @var \WC_PIP_Settings instance */
	protected $settings;

	/** @var \WC_PIP_Frontend instance */
	protected $frontend;

	/** @var \WC_PIP_Integrations instance */
	protected $integrations;


	/**
	 * Setup main plugin class
	 *
	 * @since 3.0.0
	 * @see \SV_WC_Plugin::__construct()
	 */
	public function __construct() {

		parent::__construct(
			self::PLUGIN_ID,
			self::VERSION,
			array(
				'dependencies'       => array( 'dom' ),
				'text_domain'        => 'woocommerce-pip',
				'display_php_notice' => true,
			)
		);

		// load includes after WC is loaded
		add_action( 'sv_wc_framework_plugins_loaded', array( $this, 'includes' ), 11 );
	}


	/**
	 * Loads any required files
	 *
	 * @since 3.0.0
	 */
	public function includes() {

		// template functions
		require_once( $this->get_plugin_path() . '/includes/wc-pip-template-functions.php' );

		// abstract class
		require_once( $this->get_plugin_path() . '/includes/abstract-wc-pip-document.php' );

		// invoices
		require_once( $this->get_plugin_path() . '/includes/class-wc-pip-document-invoice.php' );

		// packing lists
		require_once( $this->get_plugin_path() . '/includes/class-wc-pip-document-packing-list.php' );
		require_once( $this->get_plugin_path() . '/includes/class-wc-pip-document-pick-list.php' );

		// handler
		$this->handler = $this->load_class( '/includes/class-wc-pip-handler.php', 'WC_PIP_Handler' );

		// print documents
		$this->print = $this->load_class( '/includes/class-wc-pip-print.php', 'WC_PIP_Print' );

		// document emails
		$this->emails = $this->load_class( '/includes/class-wc-pip-emails.php', 'WC_PIP_Emails' );

		if ( is_admin() ) {
			// admin side
			$this->admin_includes();
		} else {
			// frontend side
			$this->frontend = $this->load_class( '/includes/frontend/class-wc-pip-frontend.php', 'WC_PIP_Frontend' );
		}

		// ajax
		if ( is_ajax() ) {
			$this->ajax = $this->load_class( '/includes/class-wc-pip-ajax.php', 'WC_PIP_Ajax' );
		}

		// template customizer
		$this->customizer = $this->load_class( '/includes/admin/class-wc-pip-customizer.php', 'WC_PIP_Customizer' );

		// integrations
		$this->integrations = $this->load_class( '/includes/integrations/class-wc-pip-integrations.php', 'WC_PIP_Integrations' );
	}


	/**
	 * Loads required admin files
	 *
	 * @since 3.0.0
	 */
	private function admin_includes() {

		// load admin classes
		$this->admin        = $this->load_class( '/includes/admin/class-wc-pip-admin.php', 'WC_PIP_Admin' );
		$this->orders_admin = $this->load_class( '/includes/admin/class-wc-pip-orders-admin.php', 'WC_PIP_Orders_Admin' );
	}


	/** Admin methods ******************************************************/


	/**
	 * Render admin notices (such as upgrade notices)
	 *
	 * @since 3.0.0
	 * @see \SV_WC_Plugin::add_admin_notices()
	 */
	public function add_admin_notices() {

		// Show any dependency notices
		parent::add_admin_notices();

		$screen = get_current_screen();

		// only render on plugins or settings screen
		if ( 'plugins' === $screen->id || $this->is_plugin_settings() ) {

			if ( 'yes' === get_option( 'woocommerce_pip_upgraded_to_3_0_0' ) ) {

				// display a notice for installations that are upgrading
				$message_id  = 'wc_pip_upgrade_install';

				/* translators: Placeholders: %1$s - this plugin name, %2$s - opening HTML <a> anchor tag, %3$s - closing HTML </a> tag */
				$message_content = sprintf( __( 'Hi there! It looks like you have upgraded %1$s from an older version. We have added lots of new features, please %2$scheck out the documentation%3$s for an overview and some helpful upgrading tips!', 'woocommerce-pip' ), $this->get_plugin_name(), '<a target="_blank" href="https://docs.woocommerce.com/document/woocommerce-print-invoice-packing-list/#upgrade">', '</a>' );

			} else {

				// Display a notice for fresh installs
				$message_id = 'wc_pip_fresh_install';

				/* translators: Placeholders: %1$s - the plugin name, %2$s - opening HTML <a> anchor tag, %3$s closing HTML </a> tag */
				$message_content = sprintf( __( 'Thanks for installing %1$s! To get started, take a minute to %2$sread the documentation%3$s :)', 'woocommerce-pip' ), $this->get_plugin_name(), '<a href="' . $this->get_documentation_url()  . '" target="_blank">', '</a>' );
			}

			// Add notice
			$this->get_admin_notice_handler()->add_admin_notice( $message_content, $message_id, array(
				'always_show_on_settings' => false,
				'notice_class'            => 'updated',
			) );
		}
	}


	/** Helper methods ******************************************************/


	/**
	 * Main Print Invoices/Packing Lists Instance, ensures only one instance is/can be loaded
	 *
	 * @since 3.0.0
	 * @see wc_pip()
	 * @return \WC_PIP
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/** Getter methods ******************************************************/


	/**
	 * Return admin class instance
	 *
	 * @since 3.0.0
	 * @return \WC_PIP_Admin
	 */
	public function get_admin_instance() {
		return $this->admin;
	}


	/**
	 * Return frontend class instance
	 *
	 * @since 3.0.0
	 * @return \WC_PIP_Frontend
	 */
	public function get_frontend_instance() {
		return $this->frontend;
	}


	/**
	 * Return ajax class instance
	 *
	 * @since 3.0.0
	 * @return \WC_PIP_Ajax
	 */
	public function get_ajax_instance() {
		return $this->ajax;
	}


	/**
	 * Return customizer class instance
	 *
	 * @since 3.0.0
	 * @return \WC_PIP_Customizer
	 */
	public function get_customizer_instance() {
		return $this->customizer;
	}


	/**
	 * Return orders class instance
	 *
	 * @since 3.0.0
	 * @return \WC_PIP_Orders_Admin
	 */
	public function get_orders_instance() {
		return $this->orders_admin;
	}


	/**
	 * Return settings class instance
	 *
	 * @since 3.0.0
	 * @return \WC_PIP_Settings
	 */
	public function get_settings_instance() {
		return $this->settings;
	}


	/**
	 * Return admin class instance
	 *
	 * @since 3.0.0
	 * @return \WC_PIP_Integrations
	 */
	public function get_integrations_instance() {
		return $this->integrations;
	}


	/**
	 * Return email class instance
	 *
	 * @since 3.0.0
	 * @return \WC_PIP_Emails
	 */
	public function get_email_instance() {
		return $this->emails;
	}


	/**
	 * Return print class instance
	 *
	 * @since 3.0.0
	 * @return \WC_PIP_Print
	 */
	public function get_print_instance() {
		return $this->print;
	}


	/**
	 * Return handler class instance
	 *
	 * @since 3.0.0
	 * @return \WC_PIP_Handler
	 */
	public function get_handler_instance() {
		return $this->handler;
	}


	/**
	 * Get document types
	 *
	 * @since 3.0.0
	 * @return array Associative array of PIP document types with their names
	 */
	public function get_document_types() {

		/**
		 * Filters the document types.
		 *
		 * @since 3.0.0
		 * @param array $types The document types
		 */
		return apply_filters( 'wc_pip_document_types', array(
			'invoice'      => __( 'Invoice', 'woocommerce-pip' ),
			'packing-list' => __( 'Packing List', 'woocommerce-pip' ),
			'pick-list'    => __( 'Pick List', 'woocommerce-pip' ),
		) );
	}


	/**
	 * Get a PIP document object
	 *
	 * @see \WC_PIP_Document::__construct()
	 *
	 * @since 3.0.0
	 * @param string $type Document type, such as 'invoice' or 'packing-list'
	 * @param array $args Array of arguments passed to make a WC_PIP_Document object
	 * @return \WC_PIP_Document|\WC_PIP_Document_Invoice|\WC_PIP_Document_Packing_List|\WC_PIP_Document_Pick_List|null
	 */
	public function get_document( $type, array $args = array() ) {

		if ( $this->document instanceof WC_PIP_Document ) {

			// ensure if there's a request for a document
			// which is already instantiated by comparing the order id
			// and the document type
			if ( $type === $this->document->type && $this->get_document_args_order_id( $args ) === $this->document->order_id ) {

				return $this->document;
			}
		}

		$class = 'WC_PIP_Document_' . implode( '_', array_map( 'ucfirst', explode( '-', strtolower( $type ) ) ) );

		return $this->document = class_exists( $class ) ? new $class( $args ) : null;
	}


	/**
	 * Get order id from document args
	 *
	 * @see WC_PIP::get_document()
	 *
	 * @since 3.0.0
	 * @param array $args
	 * @return int
	 */
	private function get_document_args_order_id( $args ) {

		$order_id = 0;

		if ( isset( $args['order'] ) && $args['order'] instanceof WC_Order ) {
			$order_id = SV_WC_Order_Compatibility::get_prop( $args['order'], 'id' );
		} elseif ( isset( $args['order_id'] ) ) {
			$order_id = $args['order_id'];
		} elseif ( isset( $args['order_ids'] ) && is_array( $args['order_ids'] ) ) {
			$order_id = isset( $args['order_ids'][0] ) ? $args['order_ids'][0] : $order_id;
		}

		return (int) $order_id;
	}


	/**
	 * Get the PIP template
	 *
	 * @since 3.0.0
	 * @param string $template
	 * @param array $args
	 */
	public function get_template( $template, array $args = array() ) {

		// Load the template
		wc_get_template( 'pip/' . $template . '.php', $args, '', $this->get_plugin_path() . '/templates/' );
	}


	/**
	 * Returns the plugin name, localized
	 *
	 * @since 3.0.0
	 * @see SV_WC_Plugin::get_plugin_name()
	 * @return string the plugin name
	 */
	public function get_plugin_name() {
		return __( 'WooCommerce Print Invoices/Packing Lists', 'woocommerce-pip' );
	}


	/**
	 * Returns __FILE__
	 *
	 * @since 3.0.0
	 * @see SV_WC_Plugin::get_file()
	 * @return string the full path and filename of the plugin file
	 */
	protected function get_file() {
		return __FILE__;
	}


	/**
	 * Gets the plugin configuration URL
	 *
	 * @since 3.0.0
	 * @see SV_WC_Plugin::get_settings_url()
	 * @param string $_ unused
	 * @return string plugin settings URL
	 */
	public function get_settings_url( $_ = null ) {
		return admin_url( 'admin.php?page=wc-settings&tab=pip' );
	}


	/**
	 * Gets the plugin documentation URL
	 *
	 * @since 3.0.0
	 * @see SV_WC_Plugin::get_documentation_url()
	 * @return string
	 */
	public function get_documentation_url() {
		return 'https://docs.woocommerce.com/document/woocommerce-print-invoice-packing-list/';
	}


	/**
	 * Gets the plugin support URL
	 *
	 * @since 3.0.0
	 * @see SV_WC_Plugin::get_support_url()
	 * @return string
	 */
	public function get_support_url() {
		return 'https://woocommerce.com/my-account/marketplace-ticket-form/';
	}


	/**
	 * Returns true if on the plugin's settings page
	 *
	 * @since 3.0.0
	 * @see \SV_WC_Plugin::is_plugin_settings()
	 * @return boolean true if on the settings page
	 */
	public function is_plugin_settings() {
		return isset( $_GET['page'] )    && 'wc-settings' === $_GET['page']     &&
		       isset( $_GET['tab'] )     && 'pip'         === $_GET['tab'];
	}


	/** Lifecycle methods ******************************************************/


	/**
	 * Run install
	 *
	 * @since 3.0.0
	 * @see SV_WC_Plugin::install()
	 */
	protected function install() {

		// Include settings so we can install defaults
		require_once( WC()->plugin_path() . '/includes/admin/settings/class-wc-settings-page.php' );

		$this->settings = $this->load_class( '/includes/admin/class-wc-pip-settings.php', 'WC_PIP_Settings' );

		foreach ( $this->settings->get_settings() as $setting ) {

			if ( isset( $setting['default'] ) ) {

				update_option( $setting['id'], $setting['default'] );
			}
		}

		// PIP versions prior to 2.7.0 did not set a version option,
		// so the upgrade method needs to be called manually.
		// We do this by checking first if an old option exists,
		// but a new one doesn't.
		if ( get_option( 'woocommerce_pip_invoice_start' ) && ! get_option( 'wc_pip_invoice_number_start' ) ) {
			$this->upgrade( '2.7.1' );
		}
	}


	/**
	 * Perform any version-related changes.
	 *
	 * @since 3.0.0
	 * @see SV_WC_Plugin::upgrade()
	 * @param int $installed_version the currently installed version of the plugin
	 */
	protected function upgrade( $installed_version ) {

		if ( version_compare( $installed_version, '3.0.0', '<' ) ) {

			// old option name => new option name
			$options = array(
				'woocommerce_pip_logo'           => 'wc_pip_company_logo',
				'woocommerce_pip_company_name'   => 'wc_pip_company_name',
				'woocommerce_pip_company_extra'  => 'wc_pip_company_extra',
				'woocommerce_pip_return_policy'  => 'wc_pip_return_policy',
				'woocommerce_pip_header'         => 'wc_pip_header',
				'woocommerce_pip_footer'         => 'wc_pip_footer',
				'woocommerce_pip_invoice_start'  => 'wc_pip_invoice_number_start',
				'woocommerce_pip_invoice_prefix' => 'wc_pip_invoice_number_prefix',
				'woocommerce_pip_invoice_suffix' => 'wc_pip_invoice_number_suffix',
			);

			foreach ( $options as $old_option => $new_option ) {

				if ( $old_setting = get_option( $old_option ) ) {

					update_option( $new_option, $old_setting );
					delete_option( $old_option );
				}
			}

			// emails option needs a different handling
			$emails_enabled  = 'enabled' === get_option( 'woocommerce_pip_send_email' ) ? 'yes' : 'no';
			$default_setting = array( 'enabled' => $emails_enabled );
			$emails_settings = array(
				'woocommerce_pip_email_invoice_settings'      => get_option( 'woocommerce_pip_email_invoice_settings', $default_setting ),
				'woocommerce_pip_email_packing_list_settings' => get_option( 'woocommerce_pip_email_packing_list_settings', $default_setting ),
			);

			// update from a legacy setting to send HTML emails with an array compatible with WC Emails settings
			foreach ( $emails_settings as $emails_setting_key => $emails_setting_options ) {

				if ( $emails_setting_options && is_array( $emails_setting_options ) ) {

					$emails_setting_options['enabled'] = $emails_enabled;

					update_option( $emails_setting_key, $emails_setting_options );
				}
			}

			// delete legacy email option
			delete_option( 'woocommerce_pip_send_email' );

			// prevent changing default behaviour for old installations
			update_option( 'wc_pip_use_order_number', 'no' );

			// print preview option is no longer used
			delete_option( 'woocommerce_pip_preview' );

			// redundant option before WC Settings Page was used
			delete_option( 'pip_fields_submitted' );

			// now update the print status of past orders if PIP was previously installed
			$posts_per_page = 500;
			$offset         = (int) get_option( 'wc_pip_upgrade_install_offset', 0 );
			$documents      = array( 'invoice', 'packing-list' );

			do {

				// grab order ids
				$order_ids = get_posts( array(
					'post_type'      => 'shop_order',
					'fields'         => 'ids',
					'posts_per_page' => $posts_per_page,
					'offset'         => $offset,
					'post_status'    => 'any'
				) );

				// sanity check
				if ( is_wp_error( $order_ids ) ) {
					break;
				}

				if ( ! empty( $order_ids ) && is_array( $order_ids ) ) {

					$is_wc_3_0 = SV_WC_Plugin_Compatibility::is_wc_version_gte_3_0();

					foreach( $order_ids as $order_id ) {

						$invoice_number = null;

						// previously, PIP would generate an invoice number when a print window was open for the first time,
						// therefore we can check this meta to see if a document has been printed before
						if ( $is_wc_3_0 ) {

							$order = wc_get_order( $order_id );

							if ( $order ) {
								$invoice_number = $order->get_meta( '_pip_invoice_number', true, 'view' );
							}

						} else {

							$invoice_number = get_post_meta( $order_id, '_pip_invoice_number', true );
						}

						if ( ! empty( $invoice_number ) ) {

							foreach ( $documents as $document_type ) {

								$document = $this->get_document( $document_type, array( 'order_id' => $order_id ) );
								$document->update_print_count();
							}
						}
					}
				}

				// increment offset
				$offset += $posts_per_page;

				// keep track of how far we made it in case we hit a script timeout
				update_option( 'wc_pip_upgrade_install_offset', $offset );

			} while ( count( $order_ids ) === $posts_per_page );  // while full set of results returned (meaning there may be more results still to retrieve)

			// upgrade flag
			update_option( 'woocommerce_pip_upgraded_to_3_0_0', 'yes' );
		}
	}


} // end \WC_PIP class


/**
 * Returns the One True Instance of Print Invoices/Packing Lists
 *
 * @since 3.0.0
 * @return WC_PIP
 */
function wc_pip() {
	return WC_PIP::instance();
}


// fire it up!
wc_pip();

} // init_woocommerce_pip
