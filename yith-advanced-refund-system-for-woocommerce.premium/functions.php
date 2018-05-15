<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

if ( ! function_exists( 'ywcars_get_request_statuses' ) ) {
    function ywcars_get_request_statuses() {
        $request_statuses = array(
            'ywcars-new'        => _x( 'New refund request', 'Request status', 'yith-advanced-refund-system-for-woocommerce' ),
            'ywcars-processing' => _x( 'Processing', 'Request status', 'yith-advanced-refund-system-for-woocommerce' ),
            'ywcars-on-hold'    => _x( 'On hold', 'Request status', 'yith-advanced-refund-system-for-woocommerce' ),
            'ywcars-approved'   => _x( 'Approved', 'Request status', 'yith-advanced-refund-system-for-woocommerce' ),
            'ywcars-rejected'   => _x( 'Rejected', 'Request status', 'yith-advanced-refund-system-for-woocommerce' ),
            'trash'             => _x( 'Refund request in Trash', 'Request status', 'yith-advanced-refund-system-for-woocommerce' ),
        );
        return apply_filters( 'ywcars_request_statuses', $request_statuses );
    }
}

if ( ! function_exists( 'ywcars_get_request_status_by_key' ) ) {
    function ywcars_get_request_status_by_key( $status_key ) {
        $request_statuses = ywcars_get_request_statuses();
        return ! empty( $request_statuses[$status_key] ) ? $request_statuses[$status_key] : __( 'No status', 'yith-advanced-refund-system-for-woocommerce' );
    }
}

if ( ! function_exists( 'ywcars_get_requests_by_customer_id' ) ) {
    function ywcars_get_requests_by_customer_id( $customer_id ) {
        if ( empty( $customer_id ) ) {
            return false;
        }
        $request_statuses = ywcars_get_request_statuses();
        $args = array(
            'post_type'   => YITH_WCARS_CUSTOM_POST_TYPE,
            'post_status' => array_keys( $request_statuses ),
            'numberposts' => - 1,
            'fields'      => 'ids',
            'meta_query'  => array(
                array(
                    'key' => '_ywcars_customer_id',
                    'value' => $customer_id,
                    'compare' => '='
                )
            )
        );

        $request_ids = get_posts( $args );
        if ( empty( $request_ids ) ) {
            return false;
        } else {
            return $request_ids;
        }
    }
}

if ( ! function_exists( 'is_plugin_active' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

if ( ! function_exists( 'yith_initialize_plugin_fw' ) ) {
    /**
     * Initialize plugin-fw
     */
    function yith_initialize_plugin_fw( $plugin_dir ) {
        if ( ! function_exists( 'yit_deactive_free_version' ) ) {
            require_once $plugin_dir . 'plugin-fw/yit-deactive-plugin.php';
        }

        if ( ! function_exists( 'yith_plugin_registration_hook' ) ) {
            require_once $plugin_dir . 'plugin-fw/yit-plugin-registration-hook.php';
        }

        /* Plugin Framework Version Check */
        if ( ! function_exists( 'yit_maybe_plugin_fw_loader' ) && file_exists( $plugin_dir . 'plugin-fw/init.php' ) ) {
            require_once( $plugin_dir . 'plugin-fw/init.php' );
        }
    }
}

if ( ! function_exists( 'yith_ywars_install_woocommerce_admin_notice' ) ) {

    function yith_ywars_install_woocommerce_admin_notice() {
        ?>
        <div class="error">
            <p><?php _e( 'YITH Advanced Refund System for WooCommerce is enabled but not effective. It requires WooCommerce in order to work.', 'yith-advanced-refund-system-for-woocommerce' ); ?></p>
        </div>
        <?php
    }
}

if ( ! function_exists( 'yith_ywars_install' ) ) {
    /**
     * Install the plugin
     */
    function yith_ywars_install() {

        if ( ! function_exists( 'WC' ) ) {
            add_action( 'admin_notices', 'yith_ywars_install_woocommerce_admin_notice' );
        } else {
            do_action( 'yith_ywars_init' );
            YITH_ARS_DB::install();
        }
    }
}

if ( ! function_exists( 'yith_ywars_init' ) ) {
    /**
     * Start the plugin
     */
    function yith_ywars_init() {
        /**
         * Load text domain
         */
        load_plugin_textdomain( 'yith-advanced-refund-system-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

        

        /** include plugin's files */
        require_once( YITH_WCARS_PATH . 'includes/class.yith-advanced-refund-system.php' );
        require_once( YITH_WCARS_PATH . 'includes/class.yith-advanced-refund-system-premium.php' );
        require_once( YITH_WCARS_PATH . 'includes/class.yith-advanced-refund-system-db.php' );
        require_once( YITH_WCARS_PATH . 'includes/class.yith-ywars-plugin-fw-loader.php' );

        YITH_YWARS_Plugin_FW_Loader::get_instance();
        YITH_Advanced_Refund_System();
    }
}

if ( ! function_exists( 'YITH_Advanced_Refund_System' ) ) {
    /**
     * Unique access to instance of YITH_Advanced_Refund_System class
     *
     * @return YITH_Advanced_Refund_System
     * @since 1.0.0
     */
    function YITH_Advanced_Refund_System() {
        return YITH_Advanced_Refund_System_Premium::instance();
    }
}
add_action( 'yith_ywars_init', 'yith_ywars_init' );