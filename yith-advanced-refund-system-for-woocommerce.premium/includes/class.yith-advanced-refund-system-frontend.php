<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */
if ( ! defined( 'YITH_WCARS_VERSION' ) ) {
    exit( 'Direct access forbidden.' );
}

/**
 *
 *
 * @class      YITH_Advanced_Refund_System_Frontend
 * @package    Yithemes
 * @since      Version 1.0.0
 * @author     Carlos Mora <carlos.eugenio@yourinspiration.it>
 *
 */

if ( ! class_exists( 'YITH_Advanced_Refund_System_Frontend' ) ) {
    /**
     * Class YITH_Advanced_Refund_System_Frontend
     *
     * @author Carlos Mora <carlos.eugenio@yourinspiration.it>
     */
    class YITH_Advanced_Refund_System_Frontend {
        /**
         * Construct
         *
         * @author Carlos Mora <carlos.eugenio@yourinspiration.it>
         * @since 1.0.0
         */
        public function __construct() {
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
            add_action( 'woocommerce_order_details_after_order_table', array( $this, 'refund_whole_order_button' ), 9 );
        }

        public function enqueue_scripts() {

            if ( ! is_account_page() ) {
                return;
            }

            wp_enqueue_style( 'ywcars-frontend',
                YITH_WCARS_ASSETS_URL . 'css/ywcars-frontend.css',
                array(),
                YITH_WCARS_VERSION
            );
	        wp_enqueue_style( 'ywcars-common',
		        YITH_WCARS_ASSETS_URL . 'css/ywcars-common.css',
		        array(),
		        YITH_WCARS_VERSION
	        );
            wp_enqueue_script( 'ywcars-frontend',
                YITH_WCARS_ASSETS_JS_URL . yit_load_js_file( 'ywcars-frontend.js' ),
                array( 'jquery' ),
                YITH_WCARS_VERSION,
                'true'
            );
            wp_localize_script( 'ywcars-frontend', 'localize_js_ywcars_frontend',
                array(
                    'ajax_url'               => admin_url( 'admin-ajax.php', apply_filters( 'ywcars_ajax_url_scheme_frontend', '' ) ),
                    'ywcars_submit_request'  => wp_create_nonce( 'ywcars-submit-request' ),
                    'ywcars_submit_message'  => wp_create_nonce( 'ywcars-submit-message' ),
                    'ywcars_update_messages' => wp_create_nonce( 'ywcars-update-messages' ),
                    'reloading'              => __( 'Reloading...', 'yith-advanced-refund-system-for-woocommerce' ),
                    'success_message'        => __( 'Message submitted successfully', 'yith-advanced-refund-system-for-woocommerce' ),
                    'fill_fields'            => __( 'Please fill in with all required information',
                        'yith-advanced-refund-system-for-woocommerce' )
                )
            );

            $suffix      = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

	        if ( version_compare( WC()->version, '2.7.0', '>=' ) ) {
		        wp_enqueue_style( 'ywcars_prettyPhoto_css', YITH_WCARS_ASSETS_URL . '/css/prettyPhoto.css' );
		        wp_enqueue_script( 'ywcars-prettyPhoto', YITH_WCARS_ASSETS_JS_URL . '/jquery.prettyPhoto' . $suffix . '.js', array( 'jquery' ), false, true );
	        }else{
		        $assets_path = str_replace( array( 'http:', 'https:' ), '', WC()->plugin_url() ) . '/assets/';
		        wp_enqueue_style( 'woocommerce_prettyPhoto_css', $assets_path . 'css/prettyPhoto.css' );
		        wp_enqueue_script( 'prettyPhoto', $assets_path . 'js/prettyPhoto/jquery.prettyPhoto' . $suffix . '.js', array( 'jquery' ), '3.1.6', true );
	        }


        }

	    public function refund_whole_order_button( $order ) {
		    if ( ! is_account_page() ) {
		        return;
		    }
		    if ( ! $order ) {
			    return;
		    }
		    if ( ! $this->order_has_enough_ndays( $order ) ) {
			    ?>
                <div style="margin-bottom: 10px;">
                    <button disabled title="<?php _e( 'The deadline for refund requests is over', 'yith-advanced-refund-system-for-woocommerce' ); ?>"
                            class="button ywcars_button_refund"><?php _e( 'Refund my entire order', 'yith-advanced-refund-system-for-woocommerce' ); ?></button>
                </div>
			    <?php
                return;
		    }

		    $refundable = get_option( 'yith_wcars_allow_refunds' );
		    if ( 'yes' != $refundable ) {
			    ?>
                <div style="margin-bottom: 10px;">
                    <button disabled title="<?php _e( 'Refunds are not available', 'yith-advanced-refund-system-for-woocommerce' ); ?>"
                            class="button ywcars_button_refund"><?php _e( 'Refund my entire order', 'yith-advanced-refund-system-for-woocommerce' ); ?></button>
                </div>
			    <?php
                return;
		    }
		    $requests = yit_get_prop( $order, '_ywcars_requests', true );
		    if ( ! $requests ) {
			    $params = array(
				    'ajax'       => 'true',
				    'action'     => 'ywcars_open_request_window',
				    'order_id'   => yit_get_order_id( $order ),
				    'target'     => 'whole_order',
				    'line_total' => $order->get_total()
			    );
			    $link = add_query_arg( $params, admin_url( 'admin-ajax.php', apply_filters( 'ywcars_ajax_url_scheme_frontend', '' ) ) );
			    ?>
                <div style="margin-bottom: 10px;">
                    <a class="button ywcars_button_refund" data-rel="prettyPhoto"
                       href="<?php echo $link; ?>"><?php _e( 'Refund my entire order', 'yith-advanced-refund-system-for-woocommerce' ); ?></a>
                </div>
			    <?php
		    } else {
			    ?>
                <div style="margin-bottom: 10px;">
                    <button disabled title="<?php _e( 'Refund on the entire order is not allowed', 'yith-advanced-refund-system-for-woocommerce' ); ?>"
                            class="button ywcars_button_refund"><?php _e( 'Refund my entire order', 'yith-advanced-refund-system-for-woocommerce' ); ?></button>
                </div>
			    <?php
		    }
	    }

	    public function order_has_enough_ndays( $order ) {
	        $order_date = yit_get_prop( $order, '_paid_date', true ) ? yit_get_prop( $order, '_paid_date', true ) : yit_get_prop( $order, '_completed_date', true );

		    $ndays = get_option( 'yith_wcars_ndays_refund' );
		    if ( ! $ndays && $order_date ) {
		        return true;
            }
		    if ( $order_date && ( ( $ndays * DAY_IN_SECONDS ) + strtotime( $order_date ) ) > time() ) {
			    return true;
		    }
		    return false;
	    }


    }
}