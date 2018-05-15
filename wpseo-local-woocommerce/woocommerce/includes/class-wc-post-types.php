<?php

class Yoast_WCSEO_Local_Post_Types {
    public function init() {

    	// actions
        add_action( 'init', array( $this, 'register_post_status' ), 9 );

	    // filters
	    add_filter( 'wc_order_statuses', array( $this, 'wc_append_post_statusus' ) );
    }

    public function register_post_status() {
        register_post_status( 'wc-transporting', array(
            'label'                     => _x( 'Transporting', 'Order status', 'yoast-local-seo-woocommerce' ),
            'public'                    => false,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop( 'Transporting <span class="count">(%s)</span>', 'Transporting <span class="count">(%s)</span>', 'yoast-local-seo-woocommerce' )
        ) );

        register_post_status( 'wc-ready-for-pickup', array(
            'label'                     => _x( 'Ready for pickup', 'Order status', 'yoast-local-seo-woocommerce' ),
            'public'                    => false,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop( 'Ready for pickup <span class="count">(%s)</span>', 'Ready for pickup <span class="count">(%s)</span>', 'yoast-local-seo-woocommerce' )
        ) );
    }


	public function wc_append_post_statusus( $order_statuses ) {

		$new_order_statuses = array();

		// add new order status after processing
		foreach ( $order_statuses as $key => $status ) {

			$new_order_statuses[ $key ] = $status;

			if ( 'wc-processing' === $key ) {
				$new_order_statuses['wc-transporting']      = _x( 'Transporting', 'Order status', 'yoast-local-seo-woocommerce' );
				$new_order_statuses['wc-ready-for-pickup']  = _x( 'Ready for pickup', 'Order status', 'yoast-local-seo-woocommerce' );
			}
		}

		return $new_order_statuses;
	}

}