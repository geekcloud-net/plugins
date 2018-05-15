<?php
/**
 * Class that handles API integration settings in WooCommerce.
 *
 * @package WooCommerce_Instagram
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Instagram Integration
 *
 * Enables Instagram integration.
 *
 * @class   Woocommerce_Instagram_Integration
 * @extends WC_Integration
 * @version 1.6.4
 * @package WooCommerce/Classes/Integrations
 * @author  WooCommerce
 */
class Woocommerce_Instagram_Integration extends WC_Integration {
	/**
	 * Init and hook in the integration.
	 *
	 * @since  1.0.0
	 */
	public function __construct() {
		$this->id                 = 'instagram';
		$this->_token             = 'woocommerce-instagram';
		$this->method_title       = __( 'Instagram', 'woocommerce-instagram' );
		$this->method_description = __( 'Connect to your Instagram account, to display product-related Instagrams on each individual product screen.', 'woocommerce-instagram' );

		// Actions.
		add_action( 'woocommerce_update_options_integration_instagram', array( $this, 'admin_screen_logic' ) );
		add_action( 'admin_init', array( $this, 'process_instagram_oauth_callback' ) );
		add_action( 'init', array( $this, 'maybe_start_output_buffer' ) );
	}

	/**
	 * Override from the WC_Integration abstract.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function admin_options() {
		?>
		<h3><?php echo isset( $this->method_title ) ? $this->method_title : __( 'Settings', 'woocommerce-instagram' ); ?></h3>
		<?php echo isset( $this->method_description ) ? wpautop( $this->method_description ) : ''; ?>
		<?php $this->generate_settings_html(); ?>
		<!-- Section -->
		<div><input type="hidden" name="section" value="<?php echo esc_attr( $this->id ); ?>" /></div>
		<?php
	}

	/**
	 * Settings HTML.
	 *
	 * @param array $form_fields Form fields.
	 * @param bool  $echo Whether to print or not.
	 */
	public function generate_settings_html( $form_fields = array(), $echo = true ) {
		$GLOBALS['hide_save_button'] = true;
		$this->admin_screen();
	}

	/**
	 * Start the output buffer, so we can do a safe redirect.
	 *
	 * @since 1.0.3
	 *
	 * @return void
	 */
	public function maybe_start_output_buffer() {
		$integrations = 1;
		if ( version_compare( WC_VERSION, '2.2.0', '>=' ) ) {
			$integrations = count( WC()->integrations->get_integrations() );
		}
		if ( is_admin() && isset( $_GET['tab'] ) && isset( $_GET['section'] ) && 'integration' == $_GET['tab'] && ( 'instagram' == $_GET['section'] || 1 < intval( $integrations ) ) ) {
			ob_start();
		}
	}

	/**
	 * Should be hooked in the capture the $_GET access token value
	 *
	 * We are expecting connect.woocommerce.com to return:
	 *  - access_token
	 *  - username
	 *
	 * @since 1.0.9 introduced
	 */
	public function process_instagram_oauth_callback() {

		if ( ! isset( $_GET['instagram_access_token'] ) || ! isset( $_GET['instagram_user_name'] ) ) {
			return;
		}

		$access_token   = sanitize_text_field( $_GET['instagram_access_token'] );
		$instagram_user = sanitize_text_field( $_GET['instagram_user_name'] );

		if ( ! $access_token ) {
			return;
		}

		$new_settings = array(
			'access_token' => $access_token,
			'username'     => $instagram_user,
		);

		$this->_save_settings( $new_settings );

		// Redirect to instagram settings.
		$query_parameters = array(
			'page'    => 'wc-settings',
			'tab'     => 'integration',
			'section' => 'instagram',
		);

		$instagram_integrations_settings_url = add_query_arg( $query_parameters, admin_url( 'admin.php' ) );
		wp_safe_redirect( $instagram_integrations_settings_url );
		exit;
	}

	/**
	 * Logic for the admin screen.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_screen_logic() {
		if ( ! empty( $_POST ) && check_admin_referer( 'woocommerce-instagram', 'woocommerce-instagram-nonce' ) ) {
			global $woocommerce_instagram; // Load in the global so we can use the API instance.
			$disconnected = '';
			if ( isset( $_POST['disconnect-instagram'] ) && 'true' == $_POST['disconnect-instagram'] ) {
				// Disconnect the account, if need be.
				$updated = 'true';
				$saved = (bool) $this->_save_settings( array( 'access_token' => '', 'username' => '' ) );
				$disconnected = '&disconnected=true';
				// Look into clearing transients, here.
			}

			$page = 'wc-settings';
			$url = add_query_arg( 'page', $page, admin_url( 'admin.php' ) );
			$url = add_query_arg( 'tab', 'integration', $url );
			$url = add_query_arg( 'section', 'instagram', $url );

			$url = add_query_arg( 'updated', urlencode( $updated ), $url );
			$url .= $disconnected;

			wp_safe_redirect( $url );
			exit;
		}
	}

	/**
	 * The contents of the WordPress admin screen.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_screen() {
		$html = '';
		$html .= $this->_maybe_display_error();
		$html .= wp_nonce_field( 'woocommerce-instagram', 'woocommerce-instagram-nonce', true, false );
		$html .= '<table class="form-table">' . "\n";
		$html .= '<tr>' . "\n";
		$html .= $this->_get_instagram_connect_html() . "\n";
		$html .= '</tr>' . "\n";
		$html .= '</table>' . "\n";
		echo $html;
	}

	/**
	 * Return HTML for connecting or disconnecting the access token.
	 *
	 * @since  1.0.0
	 * @return string HTML.
	 */
	private function _get_instagram_connect_html() {
		$settings = $this->_get_settings();
		if ( '' == $settings['access_token'] ) {
			$oauth_connect_url    = 'http://connect.woocommerce.com/login/instagram/?scopes=public_content&redirect=' . admin_url( 'admin.php' );
			$oauth_connect_button = '<a class="button button-primary" href="' . esc_url( $oauth_connect_url ) . '">' . __( 'Connect Instagram Account', 'woocommerce-instagram' ) . '</a>';
			$html = '';
			$html .= '<td style="padding-left: 0;">' . $oauth_connect_button . '</td>' . "\n";
		} else {
			// Otherwise, provide a form to disconnect.
			$html = '';
			$html .= '<th scope="row">' . sprintf( __( 'Currently connected as %s.', 'woocommerce-instagram' ), '<strong>' . $settings['username'] . '</strong>' ) . '</th><td valign="top">' . get_submit_button( __( 'Disconnect Instagram Account', 'woocommerce-instagram' ), 'primary', 'submit', false ) . '<input type="hidden" name="disconnect-instagram" value="true" />' . '</td>' . "\n";
		}

		return $html;
	}

	/**
	 * Display the error, if one is logged.
	 *
	 * @since   1.0.0
	 * @return  string Formatted HTML markup.
	 */
	private function _maybe_display_error() {
		$transient_key = $this->_token . '-request-error';
		if ( false !== ( $data = get_transient( $transient_key ) ) ) {
			$html = '<div class="error fade"><p><strong>' . sprintf( __( 'Error code %s', 'woocommerce-instagram' ), $data->code ) . '</strong> - ' . esc_html( $data->error_message ) . '</p></div>' . "\n";
			delete_transient( $transient_key );
			return $html;
		}
	} // End _log_error()

	/**
	 * Log an error, for display on the settings screen.
	 *
	 * @since   1.0.0
	 * @param   object $error_obj An error object containing code, error_type and error_message.
	 * @return  void
	 */
	private function _log_error( $error_obj ) {
		set_transient( $this->_token . '-request-error', (object) $error_obj, 10 );
	}

	/**
	 * Save settings.
	 *
	 * @since 1.0.0
	 *
	 * @param array $settings Settings.
	 *
	 * @return  boolean
	 */
	private function _save_settings( $settings = array() ) {
		$current_settings = $this->_get_settings();
		$settings = wp_parse_args( $settings, $current_settings );
		return (bool) update_option( $this->_token . '-settings', (array) $settings );
	}

	/**
	 * Retrieve stored settings.
	 *
	 * @access  private
	 * @since   1.0.0
	 * @return  array Stored settings.
	 */
	private function _get_settings() {
		return wp_parse_args( (array) get_option( $this->_token . '-settings', array( 'access_token' => '', 'username' => '' ) ), array( 'access_token' => '', 'username' => '' ) );
	}
}
