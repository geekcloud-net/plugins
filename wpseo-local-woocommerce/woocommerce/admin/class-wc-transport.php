<?php

class Yoast_WCSEO_Local_Transport {

	public function init() {
		add_action( 'admin_menu', array( $this, 'register_submenu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
	}

	public function admin_styles() {

		if ( get_current_screen()->id == 'woocommerce_page_yoast_wcseo_local_transport' ) {
			wp_register_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );
			wp_enqueue_style( 'woocommerce_admin_styles' );
		}

	}

	public function register_submenu() {
		add_submenu_page(
			'woocommerce',
			__('Transport',  'yoast-local-seo-woocommerce' ),
			__('Transport',  'yoast-local-seo-woocommerce' ),
			'manage_options',
			'yoast_wcseo_local_transport',
			array( $this, 'menu_callback' )
		);
	}

	public function menu_callback() {
		echo '<h3>' . __('Transport',  'yoast-local-seo-woocommerce' ) . '</h3>';
		/* translators: transport-page-description-text = a container for placing an explanatory text for the Transport page, it elaborates on what the pag is actually for */
		echo '<p>' . __('transport-page-description-text',  'yoast-local-seo-woocommerce' ) . '</p>';

		$list = new Yoast_WCSEO_Local_Transport_List();
		$list->prepare_items();
		$list->items = $this->get_transport_items();
		usort( $list->items, array( $list, 'usort_reorder' ) );
		$list->display();
	}

	public function get_transport_items() {
		global $wpdb;

		$query = "
			SELECT p.*
			FROM wp_woocommerce_order_itemmeta woim
			LEFT JOIN wp_woocommerce_order_items woi ON woi.order_item_id = woim.order_item_id
			LEFT JOIN wp_posts p ON p.ID = woi.order_id
			WHERE ( p.post_status = 'wc-processing' OR p.post_status = 'wc-transporting' OR p.post_status = 'wc-ready-for-pickup' )
			AND woim.meta_key = 'method_id'
			AND woim.meta_value LIKE 'yoast_wcseo_local_pickup%';
		";

		return $wpdb->get_results( $query );
	}
}