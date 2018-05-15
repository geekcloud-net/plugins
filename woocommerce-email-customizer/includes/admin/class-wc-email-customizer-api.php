<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
* Class for all WordPress customizer settings
*/
class WC_Email_Customizer_API {
	private static $_this;

	private $email_trigger;

	/**
	 * Constructor
	 */
	public function __construct() {
		global $wp_version;

		self::$_this = $this;

		$this->email_trigger = 'wc-email-customizer';

		// add customizer settings
		add_filter( 'customize_register', array( $this, 'customizer_settings' ) );

		// only load controls for this plugin
		if ( isset( $_GET[ $this->email_trigger ] ) ) {
			add_filter( 'customize_register', array( $this, 'remove_sections' ), 60 );

			if ( version_compare( $wp_version, '4.4', '>=' ) ) {
				add_filter( 'customize_loaded_components', array( $this, 'remove_widget_panels' ), 60 );
				add_filter( 'customize_loaded_components', array( $this, 'remove_nav_menus_panels' ), 60 );
			} else {
				add_filter( 'customize_register', array( $this, 'remove_panels' ), 60 );
			}

			add_filter( 'customize_register', array( $this, 'customizer_sections' ), 40 );
			add_filter( 'customize_register', array( $this, 'customizer_controls' ), 50 );
			add_filter( 'customize_control_active', array( $this, 'control_filter' ), 10, 2 );
			add_action( 'customize_preview_init', array( $this, 'customizer_styles' ) );

			// enqueue customizer js
			add_action( 'customize_preview_init', array( $this, 'enqueue_customizer_script' ) );

			add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue_customizer_control_script' ) );
		}

		// make sure our changes show up in the email
		add_filter( 'woocommerce_email_styles', array( $this, 'add_styles' ) );
		add_filter( 'woocommerce_email_footer_text', array( $this, 'email_footer_text' ) );

		// add our custom query vars to the whitelist
		add_filter( 'query_vars', array( $this, 'add_query_vars' ) );

		// listen for the query var and load template
		add_action( 'template_redirect', array( $this, 'load_email_template' ) );

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
	 * Adds style to the head of preview page
	 *
	 * @since 1.0.0
	 */
	public function customizer_styles() {
		echo '<style>#template_container { margin:0 auto; border:0;} #template_header_image { text-align:center;padding-bottom:20px; } table.addresses td, table.address th { border:none !important; } #wrapper { position:relative; z-index:9999; } td, th { padding:0 !important; } #body_content td:first-child { padding:48px !important; } #body_content #body_content_inner th, #body_content #body_content_inner td { padding:12px !important; }</style>';

		return true;
	}

	/**
	 * Show only our email settings in the preview
	 *
	 * @since 1.0.0
	 */
	public function control_filter( $active, $control ) {
		if ( in_array( $control->section, array( 'wc_email_header', 'wc_email_body', 'wc_email_footer', 'wc_email_send' ) ) ) {

			return true;
		}

		return false;
	}

	/**
	 * Add our settings to the WordPress Customizer.
	 *
	 * @param object $wp_customize
	 * @since 1.0.0
	 */
	public function customizer_settings( $wp_customize ) {
		global $wp_customize;

		include( 'class-wc-email-customizer-settings.php' );

		WC_Email_Customizer_Settings::add_settings( $wp_customize );

		return true;
	}

	/**
	 * Add our sections to the WordPress Customizer.
	 *
	 * @param object $wp_customize
	 * @since 1.0.0
	 */
	public function customizer_sections( $wp_customize ) {
		global $wp_customize;

		include( 'class-wc-email-customizer-sections.php' );

		WC_Email_Customizer_Sections::add_sections();

		return true;
	}

	/**
	 * Remove any unwanted default conrols.
	 *
	 * @param object $wp_customize
	 * @since 1.0.0
	 */
	public function remove_sections( $wp_customize ) {
		global $wp_customize;

		$wp_customize->remove_section( 'themes' );

		return true;
	}

	/**
	 * Removes the core 'Widgets' panel from the Customizer.
	 *
	 * @param array $components Core Customizer components list.
	 * @return array (Maybe) modified components list.
	 */
	public function remove_widget_panels( $components ) {
		$i = array_search( 'widgets', $components );
		if ( false !== $i ) {
			unset( $components[ $i ] );
		}
		return $components;
	}

	/**
	 * Removes the core 'Menus' panel from the Customizer.
	 *
	 * @param array $components Core Customizer components list.
	 * @return array (Maybe) modified components list.
	 */
	public function remove_nav_menus_panels( $components ) {
		$i = array_search( 'nav_menus', $components );
		if ( false !== $i ) {
			unset( $components[ $i ] );
		}
		return $components;
	}

	/**
	 * Remove any unwanted default panels.
	 *
	 * @param object $wp_customize
	 * @since 1.1.2
	 */
	public function remove_panels( $wp_customize ) {
		global $wp_customize;

		// note this causes a undefined object notice
		// but I believe this is a WP core issue
		//$wp_customize->remove_panel( 'nav_menus' );

		// because above causes issues, for now use below work around
		$wp_customize->get_panel( 'nav_menus' )->active_callback = '__return_false';
		$wp_customize->remove_panel( 'widgets' );

		return true;
	}

	/**
	 * Add our controls to the WordPress Customizer.
	 *
	 * @param object $wp_customize
	 * @since 1.0.0
	 */
	public function customizer_controls( $wp_customize ) {
		global $wp_customize;

		include( 'class-wc-email-customizer-controls.php' );

		WC_Email_Customizer_Controls::add_controls();

		return true;
	}

	/**
	 * If the right query var is present load the email template
	 *
	 * @since 1.0.0
	 */
	public function load_email_template( $wp_query ) {

		// load this conditionally based on the query var
		if ( get_query_var( $this->email_trigger ) ) {

			// load the mailer class
			$mailer = WC()->mailer();

			wp_head();

			ob_start();

			include( WC_EMAIL_CUSTOMIZER_PATH . '/includes/admin/views/html-email-template-preview.php' );

			$message = ob_get_clean();

			$email_heading = __( 'HTML Email Template!', 'woocommerce-email-customizer' );

			$email = new WC_Email();

			// wrap the content with the email template and then add styles
			$message = $email->style_inline( $mailer->wrap_message( $email_heading, $message ) );

			wp_footer();

			echo $message;
			exit;
		}

		return $wp_query;
	}

	/**
	 * Add custom variables to the available query vars
	 *
	 * @param  mixed $vars
	 * @return mixed
	 * @since 1.0.0
	 */
	public function add_query_vars( $vars ) {
		$vars[] = $this->email_trigger;

		return $vars;
	}

	/**
	 * Enqueues the customizer JS script
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function enqueue_customizer_script() {
		wp_enqueue_script( 'woocommerce-email-customizer-live-preview', WC_EMAIL_CUSTOMIZER_PLUGIN_URL . '/assets/js/customizer.js', array( 'jquery', 'customize-preview' ), WC_EMAIL_CUSTOMIZER_VERSION, true );

		return true;
	}

	/**
	 * Modify the styles in the WooCommerce emails with the styles in the customizer
	 *
	 * @param $styles CSS a blob of CSS
	 * @todo need to refactor this in future versions
	 * @since 1.0.0
	 * @return CSS $styles
	 */
	public function add_styles( $styles ) {

		$bg_color = 'body, body > div, body > #wrapper > table > tbody > tr > td { background-color:' . get_option( 'woocommerce_email_background_color', '#f5f5f5' ) . '; }' . PHP_EOL;

		$body_bg_color = '#template_body, #template_body td, #template_footer { background-color:' . get_option( 'woocommerce_email_body_background_color', '#fdfdfd' ) . '; }' . PHP_EOL;

		$header_bg_color = '#template_header, #header_wrapper { background-color:' . get_option( 'woocommerce_email_header_background_color', '#557da1' ) . '; }' . PHP_EOL;

		$header_text_color = '#template_header h1 { color:' . get_option( 'woocommerce_email_header_text_color', '#ffffff' ) . '; text-shadow:0 1px 0 ' . get_option( 'woocommerce_email_header_text_color', '#ffffff' ) . '; }' . PHP_EOL;

		$header_font_size = '#template_header h1 { font-size:' . get_option( 'woocommerce_email_header_font_size', '30' ) . 'px' . '; }' . PHP_EOL;

		$header_cell_padding = '#header_wrapper { padding:36px 48px !important; }' . PHP_EOL;

		$template_container_border = 'table, table th, table td { border:none; border-style:none; border-width:0; }' . PHP_EOL;

		$template_rounded_corners = '#template_container { border-radius:' . get_option( 'woocommerce_email_rounded_corners', '6' ) . 'px !important; } #template_header { border-radius:' . get_option( 'woocommerce_email_rounded_corners', '6' ) . 'px ' . get_option( 'woocommerce_email_rounded_corners', '6' ) . 'px 0 0 !important; } #template_footer { border-radius:0 0 ' . get_option( 'woocommerce_email_rounded_corners', '6' ) . 'px ' . get_option( 'woocommerce_email_rounded_corners', '6' ) . 'px !important; }' . PHP_EOL;

		$template_shadow = '#template_container { box-shadow:0 0 6px ' . get_option( 'woocommerce_email_box_shadow_spread', '1' ) . 'px rgba(0,0,0,0.6) !important; }' . PHP_EOL;

		$body_items_table = '#body_content_inner table { border-collapse: collapse; width:100%; }' . PHP_EOL;

		$body_text_color = '#template_body div, #template_body div p, #template_body h2, #template_body h3, #template_body table td, #template_body table th, #template_body table tr, #template_body table, #template_body table h3 { color:' . get_option( 'woocommerce_email_body_text_color', '#505050' ) . '; }' . PHP_EOL;

		$body_border_color = '#body_content_inner table td, #body_content_inner table th { border-color:' . get_option( 'woocommerce_email_body_text_color', '#505050' ) . '; border-width:1px; border-style:solid; text-align:left; }' . PHP_EOL;

		$addresses = '#addresses td, #addresses th { border:none !important; }' . PHP_EOL;

		$body_font_size = '#template_body div, #template_body div p, #template_body h2, #template_body h3, #template_body table td, #template_body table th, #template_body table h3 { font-size:' . get_option( 'woocommerce_email_body_font_size', '12' ) . 'px' . '; }' . PHP_EOL;

		$body_link_color = '#template_body div a, #template_body table td a { color:' . get_option( 'woocommerce_email_link_color', '#214cce' ) . '; }' . PHP_EOL;

		$width = '#template_container, #template_header, #template_body, #template_footer { width:' . get_option( 'woocommerce_email_width', '600' ) . 'px' . '; }' . PHP_EOL;

		$footer_font_size = '#template_footer p { font-size:' . get_option( 'woocommerce_email_footer_font_size', '12' ) . 'px' . '; }' . PHP_EOL;

		$footer_text_color = '#template_footer p { color:' . get_option( 'woocommerce_email_footer_text_color', '#202020' ) . '; line-height:1.5; }' . PHP_EOL;

		$font_family = get_option( 'woocommerce_email_font_family', 'sans-serif' );

		if ( 'sans-serif' === $font_family ) {
			$font_family = 'Helvetica, Arial, sans-serif';

		} else {
			$font_family = 'Georgia, serif';

		}

		$font_family = '#template_container, #template_header h1, #template_body table div, #template_footer p, #template_footer th, #template_footer td, #template_body table table, #body_content_inner table, #template_footer table { font-family:' . $font_family . '; }' . PHP_EOL;

		$styles .= PHP_EOL;
		$styles .= $template_container_border;
		$styles .= $bg_color;
		$styles .= $body_bg_color;
		$styles .= $header_bg_color;
		$styles .= $header_font_size;
		$styles .= $header_cell_padding;
		$styles .= $header_text_color;
		$styles .= $body_items_table;
		$styles .= $body_text_color;
		$styles .= $body_border_color;
		$styles .= $body_font_size;
		$styles .= $body_link_color;
		$styles .= $width;
		$styles .= $footer_text_color;
		$styles .= $footer_font_size;
		$styles .= $font_family;
		$styles .= $template_rounded_corners;
		$styles .= $template_shadow;
		$styles .= $addresses;

		return $styles;
	}

	/**
	 * Replace the footer text with the footer text from the customizer
	 *
	 * @since 1.0.0
	 */
	public function email_footer_text( $text ) {
		return get_option( 'woocommerce_email_footer_text', __( 'WooCommece Email Customizer - Powered by WooCommerce and WordPress', 'woocommerce-email-customizer' ) );
	}

	/**
	 * Enqueues scripts on the control panel side
	 *
	 * @since 1.0.0
	 */
	public function enqueue_customizer_control_script() {

		wp_enqueue_script( 'woocommerce-email-customizer-controls', WC_EMAIL_CUSTOMIZER_PLUGIN_URL . '/assets/js/customizer-controls.js', array( 'jquery' ), WC_EMAIL_CUSTOMIZER_VERSION, true );

		// localize the script
		$localized_vars = array(
			'ajaxurl'            => admin_url( 'admin-ajax.php' ),
			'ajaxSendEmailNonce' => wp_create_nonce( '_wc_email_customizer_send_email_nonce' ),
			'error'              => __( 'Error, Try again!', 'woocommerce-email-customizer' ),
			'success'            => __( 'Email Sent!', 'woocommerce-email-customizer' ),
			'saveFirst'          => __( 'Please click on save/publish before sending the test email', 'woocommerce-email-customizer' ),
		);

		wp_localize_script( 'woocommerce-email-customizer-controls', 'woocommerce_email_customizer_controls_local', $localized_vars );

		return true;
	}
}

new WC_Email_Customizer_API();
