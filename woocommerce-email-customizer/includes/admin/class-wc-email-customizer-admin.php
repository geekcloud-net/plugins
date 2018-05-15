<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
* Class for modifying the admin pages
*/
class WC_Email_Customizer_Admin {
	private static $_this;

	private $customizer_url;
	private $unused_settings;

	/**
	 * Constructor
	 */
	public function __construct() {
		self::$_this = $this;

		$this->_set_customizer_url();
		$this->_set_unused_settings();

		// modify the existing email settings
		add_action( 'woocommerce_email_settings', array( $this, 'email_settings' ) );

		// add a custom action for the button setting type
		add_action( 'woocommerce_admin_field_wc_email_customize_button', array( $this, 'customize_button' ) );

		add_action( 'wp_ajax_woocommerce_email_customizer_send_email', array( $this, 'send_email' ) );

		return true;
	}

	/**
	 * public function to get instance
	 *
	 * @since 1.0.0
	 * @return instance object
	 */
	public function get_instance() {
		return self::$_this;
	}

	/**
	 * Set unused email settings
	 *
	 * @since 1.0.0
	 */
	private function _set_unused_settings() {
		$this->unused_settings = array(
			'email_template_options',
			'woocommerce_email_header_image',
			'woocommerce_email_footer_text',
			'woocommerce_email_base_color',
			'woocommerce_email_background_color',
			'woocommerce_email_body_background_color',
			'woocommerce_email_text_color',
		);

		return true;
	}

	/**
	 * Set the customizer url
	 *
	 * @since 1.0.0
	 */
	private function _set_customizer_url() {

		$url = admin_url( 'customize.php' );

		$url = add_query_arg( 'wc-email-customizer', 'true', $url );

		$url = add_query_arg( 'url', wp_nonce_url( site_url() . '/?wc-email-customizer=true', 'preview-mail' ), $url );

		$url = add_query_arg( 'return', urlencode( add_query_arg( array( 'page' => 'wc-settings', 'tab' => 'email' ), admin_url( 'admin.php' ) ) ), $url );

		$this->customizer_url = esc_url_raw( $url );

		return true;
	}

	/**
	 * Add a link to the customizer from the WooCommerce emails settings page
	 *
	 * @param mixed $settings
	 * @return mixed
	 * @since 1.0.0
	 */
	public function email_settings( $settings ) {

		// remove unnecessary email settings
		foreach( $settings as $key => $value ) {
			if ( isset( $value['id'] ) && in_array( $value['id'], $this->unused_settings ) ) {
				unset( $settings[$key] );
			}
		}

		// configure our new settings
		$customizer_settings[] = array(
			'title' => __( 'Email Customizer', 'woocommerce-email-customizer' ),
			'type'  => 'title',
			'id'    => 'email_customizer',
		);

		$customizer_settings[] = array(
			'title' => __( 'Customize!', 'woocommerce-email-customizer' ),
			'desc'  => __( 'Customize Emails', 'woocommerce-email-customizer' ),
			'type'  => 'wc_email_customize_button',
			'id'    => 'email_customizer_button',
			'link'  => $this->customizer_url,
		);

		$customizer_settings[] = array(
			'type'  => 'sectionend',
			'id'    => 'email_customizer_sectionend',
		);

		// add the new settings to the existing settings
		$settings = array_merge( $settings, $customizer_settings );

		return $settings;
	}

	/**
	 * Add a custom setting type
	 *
	 * @param mixed $settings
	 * @since 1.0.0
	 */
	public function customize_button( $settings ) {
		?>
		<tr valign="top">
			<th scope="row" class="titledesc"><?php echo $settings['desc'];?></th>
			<td class="forminp forminp-<?php echo sanitize_title( $settings['type'] ) ?>">
			   <a href="<?php echo $settings['link']; ?>">
				   <button
						name="<?php echo esc_attr( $settings['id'] ); ?>"
						id="<?php echo esc_attr( $settings['id'] ); ?>"
						style="<?php echo esc_attr( $settings['css'] ); ?>"
						class="button-secondary <?php echo esc_attr( $settings['class'] ); ?>"
						type="button">
						<?php echo $settings['title']; ?>
					</button>
				</a>
			</td>
		</tr>
		<?php

		return true;
	}

	/**
	 * Sends a test email
	 *
	 * @since 1.0.0
	 */
	public function send_email() {
		$nonce = $_POST['ajaxSendEmailNonce'];

		// bail if nonce don't check out
		if ( ! wp_verify_nonce( $nonce, '_wc_email_customizer_send_email_nonce' ) ) {
			die ( 'error' );
		}

		$current_user = wp_get_current_user();

		$default_user_email = $current_user->user_email;

		// sends to the email defined by user otherwise fallback to current logged in user's email
		$send_to_email = isset( $_POST['email_to'] ) ? sanitize_text_field( $_POST['email_to'] ) : $default_user_email;

		// load the mailer class
		$mailer = WC()->mailer();

		ob_start();

		include( WC_EMAIL_CUSTOMIZER_PATH . '/includes/admin/views/html-email-template-preview.php' );

		$message = ob_get_clean();

		$email_heading = __( 'HTML Email Template!', 'woocommerce-email-customizer' );

		$wc_email = new WC_Email();

		// wrap the content with the email template and then add styles
		$message = $wc_email->style_inline( $mailer->wrap_message( $email_heading, $message ) );

		$subject = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) . ' ' . __( 'Test Email Customizer Preview', 'woocommerce-email-customizer' );

		$headers = array();

		$headers[] = sprintf( __( '%s', 'woocommerce-email-customizer' ) . ' ' . wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) . ' <' . $send_to_email . '>', 'From:' ) . PHP_EOL;

		$headers[] = 'Content-Type: text/html' . PHP_EOL;

		// if email is sent successfully
		if ( wp_mail( $send_to_email, $subject, $message, $headers ) ) {
			echo 'success';
		} else {
			echo 'error';
		}

		exit;
	}
}

new WC_Email_Customizer_Admin();