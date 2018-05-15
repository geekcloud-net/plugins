<?php
/**
 * Admin handler.
 *
 * @package WooCommerce_Instagram
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WooSlider Instagram Admin Class
 *
 * @package WordPress
 * @subpackage Woocommerce_Instagram
 * @category Admin
 * @author WooThemes
 * @since 1.0.0
 */
class Woocommerce_Instagram_Admin {
	/**
	 * Token as plugin identification.
	 *
	 * @var string
	 */
	private $_token;

	/**
	 * Plugin's main file.
	 *
	 * @var string
	 */
	private $_file;

	/**
	 * API instance.
	 *
	 * @var Woocommerce_Instagram_API
	 */
	private $_api;

	/**
	 * Constructor function.
	 *
	 * @since 1.0.0
	 *
	 * @param string $file    Plugin's main file.
	 * @param object $api_obj Instance of API class.
	 */
	public function __construct( $file, $api_obj ) {
		$this->_file  = $file;
		$this->_token = 'woocommerce-instagram';
		$this->_api   = $api_obj;

		// Load WooCommerce Integration.
		require_once( 'class-woocommerce-instagram-integration.php' );

		// Register the integration within WooCommerce.
		add_filter( 'woocommerce_integrations', array( $this, 'add_woocommerce_integration' ) );

		// Maybe display admin notices.
		add_action( 'admin_notices', array( $this, 'maybe_display_admin_notices' ) );

		if ( version_compare( WC_VERSION, '3.0', '<' ) ) {
			// Add a new tab to the product data meta box.
			add_filter( 'woocommerce_product_write_panel_tabs', array( $this, 'render_product_data_tab_markup' ) );
			// Add markup for our new product data tab.
			add_action( 'woocommerce_product_write_panels', array( $this, 'product_data_tab_markup' ) );
		} else {
			// Add a new tab to the product data meta box.
			add_filter( 'woocommerce_product_data_tabs', array( $this, 'add_product_data_tab' ) );
			// Add markup for our new product data tab.
			add_action( 'woocommerce_product_data_panels', array( $this, 'product_data_tab_markup' ) );
		}

		// Sae product data tab fields.
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_product_data_tab_fields' ) );

		$print_css_on = apply_filters( 'woocommerce_instagram_screen_ids', array( 'post-new.php', 'post.php' ) );

		foreach ( $print_css_on as $page ) {
			add_action( 'admin_print_styles-' . $page, array( $this, 'enqueue_styles' ) );
		}
	}

	/**
	 * Queue admin CSS.
	 *
	 * @since  1.0.0
	 */
	public function enqueue_styles() {
		global $typenow, $post, $wp_scripts;

		if ( 'post' === $typenow && ! empty( $_GET['post'] ) ) {
			$typenow = $post->post_type;
		} elseif ( empty( $typenow ) && ! empty( $_GET['post'] ) ) {
			$post = get_post( $_GET['post'] );
			$typenow = $post->post_type;
		}

		if ( '' == $typenow || 'product' == $typenow ) {
			wp_enqueue_style( $this->_token . '-admin', plugins_url( '/assets/css/admin.css', $this->_file ) );
		}
	}

	/**
	 * Add a new tab to the product data meta box. Add to an array, for use with WC 2.1.
	 *
	 * @since 1.0.0
	 *
	 * @param array $tabs Array of existing tabs.
	 *
	 * @return array Modified tabs array.
	 */
	public function add_product_data_tab( $tabs ) {
		$tabs['instagram'] = array(
			'label'  => __( 'Instagram', 'woocommerce-instagram' ),
			'target' => 'instagram_data',
			'class'  => array(),
		);
		return $tabs;
	}

	/**
	 * Add a new tab to the product data meta box. Render HTML markup, for use with WC 2.0.x.
	 *
	 * @since 1.0.0
	 */
	public function render_product_data_tab_markup() {
		echo '<li class="instagram_options instagram_data wc-2-0-x"><a href="#instagram_data">' . __( 'Instagram', 'woocommerce-instagram' ) . '</a></li>';
	}

	/**
	 * Render fields for our newly added tab.
	 *
	 * @since 1.0.0
	 */
	public function product_data_tab_markup() {
		echo '<div id="instagram_data" class="panel woocommerce_options_panel">' . "\n";
		// Instagram hashtag.
		woocommerce_wp_text_input(
			array(
				'id'          => '_instagram_hashtag',
				'class'       => 'short',
				'label'       => __( 'Hashtag', 'woocommerce-instagram' ),
				'description' => '<br />' . __( 'Display images for a given hashtag. If no hashtag is entered, no images will display.', 'woocommerce-instagram' ),
				'desc_tip'    => false,
				'type'        => 'text',
			)
		);
		echo '</div>' . "\n";
	}

	/**
	 * Save the simple product points earned / maximum discount fields
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id Product ID.
	 */
	public function save_product_data_tab_fields( $post_id ) {
		if ( '' !== $_POST['_instagram_hashtag'] ) {
			$value = stripslashes( wc_clean( $_POST['_instagram_hashtag'] ) );
			// Strip out spaces.
			$value = str_replace( ' ', '', $value );
			// Strip out the #, if it's at the front.
			$value = str_replace( '#', '', $value );
			update_post_meta( $post_id, '_instagram_hashtag', $value );
		} else {
			delete_post_meta( $post_id, '_instagram_hashtag' );
		}
	}

	/**
	 * Add the integration to WooCommerce.
	 *
	 * @since 1.0.0
	 *
	 * @param array $integrations Array of integration instances.
	 *
	 * @return array
	 */
	public function add_woocommerce_integration( $integrations ) {
		$integrations[] = 'Woocommerce_Instagram_Integration';
		return $integrations;
	}

	/**
	 * Display an admin notice, if not on the integration screen and if the account isn't yet connected.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function maybe_display_admin_notices() {
		// Don't show these notices on our admin screen.
		if ( ( isset( $_GET['page'] ) && ( 'woocommerce_settings' == $_GET['page'] || 'wc-settings' == $_GET['page'] ) ) && ( isset( $_GET['section'] ) && 'instagram' == $_GET['section'] ) ) {
			return;
		}

		$settings = $this->_get_settings();
		if ( ! isset( $settings['access_token'] ) || '' == $settings['access_token'] ) {
			$url = admin_url( 'admin.php' );
			$page = 'wc-settings';
			$url = add_query_arg( 'page', $page, $url );
			$url = add_query_arg( 'tab', 'integration', $url );
			$url = add_query_arg( 'section', 'instagram', $url );

			/* translators: 1) and 2) are anchor tag, 3) and 4) are opening and closing container tags. */
			echo '<div class="updated fade"><p>' . sprintf( __( '%1$sWooCommerce Instagram is almost ready.%2$s To get started, %3$sconnect your Instagram account%4$s.', 'woocommerce-instagram' ), '<strong>', '</strong>', '<a href="' . esc_url( $url ) . '">', '</a>' ) . '</p></div>' . "\n";
		}
	}

	/**
	 * Retrieve stored settings.
	 *
	 * @since 1.0.0
	 *
	 * @return array Stored settings.
	 */
	private function _get_settings() {
		return wp_parse_args( (array) get_option( $this->_token . '-settings', array( 'access_token' => '', 'username' => '' ) ), array( 'access_token' => '', 'username' => '' ) );
	}
}
