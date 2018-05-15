<?php
if(!defined('ABSPATH')) exit; // Exit if accessed directly

require_once('aelia-wc-requirementscheck.php');

/**
 * Checks that plugin's requirements are met.
 */
class Aelia_WC_CurrencySwitcher_RequirementsChecks extends Aelia_WC_RequirementsChecks {
	// @var string The namespace for the messages displayed by the class.
	protected $text_domain = 'woocommerce-aelia-currencyswitcher';
	// @var string The plugin for which the requirements are being checked. Change it in descendant classes.
	protected $plugin_name = 'Aelia Currency Switcher for WooCommerce';

	// @var array An array of PHP extensions required by the plugin
	protected $required_extensions = array(
		'curl',
	);

	// @var array An array of WordPress plugins (name => version) required by the plugin.
	protected $required_plugins = array(
		'WooCommerce' => '2.4',
		'Aelia Foundation Classes for WooCommerce' => array(
			'version' => '1.8.3.170202',
			'extra_info' => 'You can get the plugin <a href="http://bit.ly/WC_AFC_S3">from our site</a>, free of charge.',
			'autoload' => true,
			'url' => 'http://bit.ly/WC_AFC_S3',
		),
	);

	/**
	 * Factory method. It MUST be copied to every descendant class, as it has to
	 * be compatible with PHP 5.2 and earlier, so that the class can be instantiated
	 * in any case and and gracefully tell the user if PHP version is insufficient.
	 *
	 * @return Aelia_WC_AFC_RequirementsChecks
	 */
	public static function factory() {
		$instance = new self();
		return $instance;
	}

	public function __construct() {
		$this->required_plugins['Aelia Foundation Classes for WooCommerce']['extra_info'] =
			__('The Aelia Foundation classes is a small framework that is required by the ' .
			$this->plugin_name . ' to work correctly. ' .
			'Simply click on the "Install" or "Activate" button, as needed, to install ' .
			'the framework automatically. If you prefer to install the framework ' .
			'manually, you can download it <a href="http://bit.ly/WC_AFC_S3">from our site</a>, free of charge.',
			$this->text_domain);

		// Safeguard. Only call the parent constructor if the parent class actually
		// has it. This will prevent errors arising from plugins loading an old
		// version of the Aelia_WC_RequirementsChecks class, which doesn't contain a
		// constructor.
		// @since 4.4.15.170420
		if(method_exists('\Aelia_WC_RequirementsChecks', __FUNCTION__)) {
			parent::__construct();
		}
	}
}
