<?php
/**
 * Display notices in admin.
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_POS_Admin_Notices Class
 */
class WC_POS_Admin_Notices extends WC_Admin_Notices {

	/**
	 * Array of notices - name => callback
	 * @var array
	 */
	private $core_notices = array(
		'pos_install'  => 'pos_install_notice',
		'pos_update'   => 'pos_update_notice'
	);

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wp_loaded', array( $this, 'hide_notices' ) );
		

		if ( current_user_can( 'manage_woocommerce' ) ) {
			add_action( 'admin_print_styles', array( $this, 'add_notices' ) );
		}
	}

	/**
	 * Remove all notices
	 */
	public static function remove_all_notices() {
		delete_option( 'wc_pos_admin_notices' );
	}

	/**
	 * Show a notice
	 * @param  string $name
	 */
	public static function add_notice( $name ) {
		$notices = array_unique( array_merge( get_option( 'wc_pos_admin_notices', array() ), array( $name ) ) );
		update_option( 'wc_pos_admin_notices', $notices );
	}

	/**
	 * Remove a notice from being displayed
	 * @param  string $name
	 */
	public static function remove_notice( $name ) {
		$notices = array_diff( get_option( 'wc_pos_admin_notices', array() ), array( $name ) );
		update_option( 'wc_pos_admin_notices', $notices );
	}

	/**
	 * See if a notice is being shown
	 * @param  string  $name
	 * @return boolean
	 */
	public static function has_notice( $name ) {
		return in_array( $name, get_option( 'wc_pos_admin_notices', array() ) );
	}


	/**
	 * Hide a notice if the GET variable is set.
	 */
	public function hide_notices() {
		if ( isset( $_GET['wc-hide-notice'] ) && isset( $_GET['_wc_notice_nonce'] ) ) {
			if ( ! wp_verify_nonce( $_GET['_wc_notice_nonce'], 'woocommerce_hide_notices_nonce' ) ) {
				wp_die( __( 'Action failed. Please refresh the page and retry.', 'woocommerce' ) );
			}

			if ( ! current_user_can( 'manage_woocommerce' ) ) {
				wp_die( __( 'Cheatin&#8217; huh?', 'woocommerce' ) );
			}

			$hide_notice = sanitize_text_field( $_GET['wc-hide-notice'] );
			self::remove_notice( $hide_notice );
			do_action( 'woocommerce_hide_' . $hide_notice . '_notice' );
		}
	}

	/**
	 * Add notices + styles if needed.
	 */
	public function add_notices() {
		$notices = get_option( 'wc_pos_admin_notices', array() );
		if ( $notices ) {
			wp_enqueue_style( 'woocommerce-activation', plugins_url(  '/assets/css/activation.css', WC_PLUGIN_FILE ) );
			foreach ( $notices as $notice ) {
				if ( ! empty( $this->core_notices[ $notice ] ) && apply_filters( 'woocommerce_show_admin_notice', true, $notice ) ) {
					add_action( 'admin_notices', array( $this, $this->core_notices[ $notice ] ) );
				}
			}
		}
	}

	/**
	 * If we need to update, include a message with the update button
	 */
	public function pos_update_notice() {
		include( 'views/html-notice-update.php' );
	}

	/**
	 * If we have just installed, show a message with the install pages button
	 */
	public function pos_install_notice() {
		include( 'views/html-notice-install.php' );
	}
}

new WC_POS_Admin_Notices();
