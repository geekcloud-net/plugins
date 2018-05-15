<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WC_ShipStation_Integration Class
 */
class WC_ShipStation_Integration extends WC_Integration {

	public static $auth_key        = null;
	public static $export_statuses = array();
	public static $logging_enabled = true;
	public static $shipped_status  = null;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id                 = 'shipstation';
		$this->method_title       = __( 'ShipStation', 'woocommerce-shipstation' );
		$this->method_description = __( 'ShipStation allows you to retrieve &amp; manage orders, then print labels &amp; packing slips with ease.', 'woocommerce-shipstation' );

		if ( ! get_option( 'woocommerce_shipstation_auth_key', false ) ) {
			update_option( 'woocommerce_shipstation_auth_key', $this->generate_key() );
		}

		// Load admin form
		$this->init_form_fields();

		// Load settings
		$this->init_settings();

		self::$auth_key             = get_option( 'woocommerce_shipstation_auth_key', false );
		self::$export_statuses      = $this->get_option( 'export_statuses', array( 'wc-processing', 'wc-on-hold', 'wc-completed', 'wc-cancelled' ) );
		self::$logging_enabled      = 'yes' === $this->get_option( 'logging_enabled', 'yes' );
		self::$shipped_status       = $this->get_option( 'shipped_status', 'wc-completed' );

		// Force saved value
		$this->settings['auth_key'] = self::$auth_key;

		// Hooks
		add_action( 'woocommerce_update_options_integration_shipstation', array( $this, 'process_admin_options') );
		add_filter( 'woocommerce_subscriptions_renewal_order_meta_query', array( $this, 'subscriptions_renewal_order_meta_query' ), 10, 4 );

		if ( empty( self::$auth_key ) || empty( self::$export_statuses ) || empty( self::$shipped_status ) ) {
			add_action( 'admin_notices', array( $this, 'settings_notice' ) );
		}
	}

	/**
	 * Generate a key
	 * @return string
	 */
	public function generate_key() {
		$to_hash = get_current_user_id() . date( 'U' ) . mt_rand();
		return 'WCSS-' . hash_hmac( 'md5', $to_hash, wp_hash( $to_hash ) );
	}

	/**
	 * Init integration form fields
	 */
	public function init_form_fields()	{
		$this->form_fields = include( 'data/data-settings.php' );
	}

	/**
	 * Prevents WooCommerce Subscriptions from copying across certain meta keys to renewal orders.
	 * @param  array $order_meta_query
	 * @param  int $original_order_id
	 * @param  int $renewal_order_id
	 * @param  string $new_order_role
	 * @return array
	 */
	public function subscriptions_renewal_order_meta_query( $order_meta_query, $original_order_id, $renewal_order_id, $new_order_role ) {
		if ( 'parent' == $new_order_role ) {
			$order_meta_query .= " AND `meta_key` NOT IN ("
							  .		"'_tracking_provider', "
							  .		"'_tracking_number', "
							  .		"'_date_shipped', "
							  .		"'_order_custtrackurl', "
							  .		"'_order_custcompname', "
							  .		"'_order_trackno', "
							  .		"'_order_trackurl' )";
		}
		return $order_meta_query;
	}

	/**
	 * Settings prompt
	 */
	public function settings_notice() {
		if ( ! empty( $_GET['tab'] ) && 'integration' === $_GET['tab'] ) {
			return;
		}
		?>
		<div id="message" class="updated woocommerce-message">
			<p><?php _e( '<strong>ShipStation</strong> is almost ready &#8211; Please configure the plugin to begin exporting orders.', 'woocommerce-shipstation' ); ?></p>
			<p class="submit"><a href="<?php echo admin_url( 'admin.php?page=wc-settings&tab=integration' ); ?>" class="button-primary"><?php _e( 'Settings', 'woocommerce-shipstation' ); ?></a></p>
		</div>
		<?php
	}
}