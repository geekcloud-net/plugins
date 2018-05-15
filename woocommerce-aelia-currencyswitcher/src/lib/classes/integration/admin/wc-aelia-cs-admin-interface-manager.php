<?php
namespace Aelia\WC\CurrencySwitcher;
if(!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Handles the rendering of the elements of the Admin interface.
 */
class WC_Aelia_CS_Admin_Interface_Manager {
	protected $admin_views_path;

	// @var Aelia_Order The order being displayed. Used to determine the currency to be used
	protected $current_order;

	/**
	 * Returns the currency of the order currently being displayed.
	 *
	 * @return string
	 */
	protected function displayed_order_currency() {
		global $post;
		return WC_Aelia_CurrencySwitcher::instance()->get_order_currency($post->ID);
	}

	/**
	 * Adds meta boxes to the admin interface.
	 *
	 * @see add_meta_boxes().
	 */
	public function add_meta_boxes() {
		add_meta_box('aelia_cs_order_currency_box',
								 __('Order currency', Definitions::TEXT_DOMAIN),
								 array($this, 'render_currency_selector_widget'),
								 'shop_order',
								 'side',
								 'default');
	}

	/**
	 * Renders the currency selector widget in "new order" page.
	 */
	public function render_currency_selector_widget() {
		$order_currency = $this->displayed_order_currency();

		if(empty($order_currency)) {
			echo '<p>';
			echo __('Set currency for this new order. It is recommended to choose ' .
							'the order currency <b>before</b> adding the products, as changing ' .
							'it later will not update the product prices.',
							Definitions::TEXT_DOMAIN);
			echo '</p>';
			echo '<p>';
			echo __('<b>NOTE</b>: you can only select the currency <b>once</b>. If ' .
							'you choose the wrong currency, please discard the order and ' .
							'create a new one.',
							Definitions::TEXT_DOMAIN);
			echo '</p>';
			$currency_selector_options = array(
				'title' => '',
				'widget_type' => 'dropdown',
			);

			echo WC_Aelia_CurrencySwitcher_Widget::render_currency_selector($currency_selector_options);
		}
		else {
			// Prepare the text to use to display the order currency
			$order_currency_text = $order_currency;

			$currency_name = WC_Aelia_Currencies_Manager::get_currency_name($order_currency);
			// If a currency name is returned, append it to the code for displau.
			// If a currency name cannot be found, the method will return the currency
			// code itself. In such case, there would be no point in displaying the
			// code twice.
			if($currency_name != $order_currency) {
				$order_currency_text .= ' - ' . $currency_name;
			}

			echo '<h4 class="order-currency">';
			echo $order_currency_text;
			echo '</h4>';
		}
	}

	/**
	 * Displays additional data in the "orders list" page.
	 *
	 * @param string column The column being displayed.
	 */
	public function manage_shop_order_posts_custom_column($column) {
		global $post, $woocommerce;

		$currency_switcher = WC_Aelia_CurrencySwitcher::instance();
		// Use the Aelia_Order class, which provides additional methods
		$order = $currency_switcher->get_order($post->ID);

		// Keep track of the order being displayed. This information will be used to
		// use the correct formatting for the currency
		$this->current_order = $order;

		switch($column) {
			case 'order_total':
			case 'total_cost':
				$base_currency = WC_Aelia_CurrencySwitcher::settings()->base_currency();

				/* If order is not in base currency, display order total in base currency
				 * before the one in order currency. It's not possible to display it after,
				 * because WooCommerce core simply outputs the information and it's not
				 * possible to modify it.
				 */
				if($order->get_currency() != $base_currency) {
					$order_total_base_currency = $currency_switcher->format_price(
						$order->get_total_in_base_currency(),
						$base_currency
					);
					echo '<div class="order_total_base_currency" title="' .
							 __('Order total in base currency (estimated)', Definitions::TEXT_DOMAIN) .
							 '">';
					echo '(' . esc_html(strip_tags($order_total_base_currency)) . ')';
					echo '</div>';
				}

			break;
		}
	}

	/**
	 * Resets the current order after the "order total" column is displayed,
	 * to prevent it from changing the active currency.
	 *
	 * @param string column The column being displayed.
	 */
	public function after_manage_shop_order_posts_custom_column($column) {
		$this->current_order = null;
	}

	/**
	 * Indicates if we are on the Edit Order page.
	 *
	 * @param string action The action to check for ("edit" to check if we are
	 * modifying an existing order, or "add" to check if we are creating a new order).
	 * @return bool
	 * @since 4.4.0.161221
	 * @since WC 2.7
	 */
	protected function is_edit_order_page($action = 'edit') {
		if(!function_exists('get_current_screen')) {
			return false;
		}

		$screen = get_current_screen();

		return is_object($screen) && ($screen->post_type === 'shop_order') && ($screen->action === $action);
	}

	/**
	 * Overrides the active currency, depending on the Admin page being rendered.
	 *
	 * @param string currency The currency passed to the filter.
	 * @return string
	 */
	public function woocommerce_currency($currency) {
		// If we know which order we are handling, we can take its currency immediately
		if(is_object($this->current_order)) {
			$order_currency = $this->current_order->get_currency();

			if(!empty($order_currency)) {
				$currency = $order_currency;
			}
		}
		else {
			if(is_admin() && !defined('DOING_AJAX') && function_exists('get_current_screen')) {
				if($this->is_edit_order_page('add')) {
					$currency = null;
				}
				elseif($this->is_edit_order_page('edit')) {
					global $post;

					if($post->post_type == 'shop_order') {
						// Disable this filter temporarily, to prevent infinite recursion. This
						// is required due to changes in the admin pages in WooCommerce 2.7
						// @since @since 4.4.0.161221
						// @since WC 2.7
						remove_filter('woocommerce_currency', array($this, 'woocommerce_currency'), 20, 1);
						$order_currency = WC_Aelia_CurrencySwitcher::instance()->get_order_currency($post->ID);

						if(!empty($order_currency)) {
							$currency = $order_currency;
						}
						// Restore the filter
						add_filter('woocommerce_currency', array($this, 'woocommerce_currency'), 20, 1);
					}
				}
			}
		}

		return $currency;
	}

	/**
	 * Sets the hooks required by the class.
	 */
	protected function set_hooks() {
		global $post;

		add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
		add_action('manage_shop_order_posts_custom_column', array($this, 'manage_shop_order_posts_custom_column'), 1);
		add_action('manage_shop_order_posts_custom_column', array($this, 'after_manage_shop_order_posts_custom_column'), 50);
		add_filter('woocommerce_currency', array($this, 'woocommerce_currency'), 20, 1);

		// Coupons UI
		add_action('woocommerce_coupon_data_tabs', array($this, 'woocommerce_coupon_data_tabs'), 10);
		add_action('woocommerce_coupon_data_panels', array($this, 'woocommerce_coupon_data_panels'), 10);
	}

	/**
	 * Loads (includes) a View file.
	 *
	 * @param string view_file_name The name of the view file to include.
	 */
	protected function load_view($view_file_name) {
		$file_to_load = $this->get_view($view_file_name);
		include($file_to_load);
	}

	/**
	 * Retrieves an admin view.
	 *
	 * @param string The view file name (without path).
	 * @return string
	 */
	protected function get_view($view_file_name) {
		return $this->admin_views_path . '/' . $view_file_name;
	}

	/**
	 * Class constructor.
	 */
	public function __construct() {
		global $wpdb;
		//$wpdb->show_errors();

		// TODO Determine Views Path dynamically, depending on WooCommerce version
		$this->admin_views_path = WC_Aelia_CurrencySwitcher::instance()->path('views') . '/admin';

		$this->set_hooks();
	}

	/**
	 * Adds a new tab to the Edit Coupon page. The tab will allow to set
	 * currency-specific parameters for a coupon.
	 *
	 * @param array tabs The tabs passed by WooCommerce.
	 * @return array The array of tabs, with the additional one.
	 * @since 3.8.0.150813
	 */
	public function woocommerce_coupon_data_tabs($tabs) {
		$tabs['multi_currency'] = array(
			'label' => __('Multi-currency', Definitions::TEXT_DOMAIN),
			'target' => 'multi_currency_coupon_data',
			'class' => 'multi_currency_coupon_data',
		);

		return $tabs;
	}

	/**
	 * Renders the new tab in Edit Coupon page. The tab will allow to set
	 * currency-specific parameters for a coupon.
	 *
	 * @since 3.8.0.150813
	 */
	public function woocommerce_coupon_data_panels() {
		include($this->get_view('coupons_currencydata_view.php'));
	}
}
