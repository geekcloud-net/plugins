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
 * @class      YITH_Advanced_Refund_System_Admin
 * @package    Yithemes
 * @since      Version 1.0.0
 * @author     Carlos Mora <carlos.eugenio@yourinspiration.it>
 *
 */

if ( ! class_exists( 'YITH_Advanced_Refund_System_Admin' ) ) {
    /**
     * Class YITH_Advanced_Refund_System_Admin
     *
     * @author Carlos Mora <carlos.eugenio@yourinspiration.it>
     */
    class YITH_Advanced_Refund_System_Admin {

        /**
         * @var bool Show the premium landing page
         */
        public $show_premium_landing = true;

        /**
         * @var string
         */
        protected $_premium_landing_url = 'http://yithemes.com/themes/plugins/yith-advanced-refund-system-for-woocommerce/';


        /**
         * Construct
         *
         * @author Carlos Mora <carlos.eugenio@yourinspiration.it>
         * @since 1.0.0
         */
        public function __construct() {
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
            add_filter( 'woocommerce_screen_ids', array( $this, 'add_wc_screen_id' ) );
            add_action( 'add_meta_boxes', array( $this, 'manage_meta_boxes' ), 10, 2 );
	        add_action( 'woocommerce_process_shop_order_meta', array( $this, 'save_refund_request_actions' ), 50, 2 );
            add_filter( 'manage_yith_refund_request_posts_columns', array( $this, 'add_cpt_columns' ) );
            add_action( 'manage_yith_refund_request_posts_custom_column', array( $this, 'add_cpt_columns_content' ), 10, 2 );
            add_filter( 'manage_edit-shop_order_columns',  array( $this, 'add_refund_request_column_orders_page' ) );
            add_action( 'manage_shop_order_posts_custom_column', array( $this, 'add_refund_request_column_orders_page_content' ), 10, 2 );
            add_filter( 'views_edit-shop_order', array( $this, 'add_refund_requests_view_on_orders' ) );
            add_action( 'pre_get_posts', array( $this, 'refund_requests_view_filters' ) );
	        add_action( 'load-edit.php', array( $this, 'bulk_actions' ) );
            add_action( 'yith_wcars_premium_tab', array( $this, 'show_premium_landing' ) );
        }

        public function enqueue_scripts( $hook_suffix ) {
            global $post;

	        $current_screen = get_current_screen();

            wp_enqueue_style( 'ywcars-admin-style',
                YITH_WCARS_ASSETS_URL . 'css/ywcars-admin.css',
                array(),
                YITH_WCARS_VERSION );

	        wp_enqueue_style( 'ywcars-common',
		        YITH_WCARS_ASSETS_URL . 'css/ywcars-common.css',
		        array(),
		        YITH_WCARS_VERSION
	        );

            $locale  = localeconv();
            $decimal = isset( $locale['decimal_point'] ) ? $locale['decimal_point'] : '.';
	        $post_id  = isset( $post->ID ) ? $post->ID : '';
	        $currency = '';

	        if ( $post_id ) {
		        $request  = new YITH_Refund_Request( $post_id );
		        if ( $request->exists() ) {
			        $order    = wc_get_order( $request->order_id );
			        $currency = version_compare( WC()->version, '3.0.0', '<' ) ? $order->get_order_currency() : $order->get_currency();
		        }
	        }
            $params = array(
                'i18n_do_refund'                    => __( 'Are you sure you want to process this refund? This action cannot be undone.',
                    'yith-advanced-refund-system-for-woocommerce' ),
                'ajax_url'                          => admin_url( 'admin-ajax.php', apply_filters( 'ywcars_ajax_url_scheme_backend', '' ) ),
                'order_item_nonce'                  => wp_create_nonce( 'order-item' ),
                'create_coupon_nonce'               => wp_create_nonce( 'create-coupon' ),
                'change_status_nonce'               => wp_create_nonce( 'change-status' ),
                'ywcars_submit_message'             => wp_create_nonce( 'ywcars-submit-message' ),
                'ywcars_update_messages'            => wp_create_nonce( 'ywcars-update-messages' ),
                'decimal_point'                     => $decimal,
                'i18n_decimal_error'                => sprintf( __( 'Please enter in decimal format (%s) without thousand separators.',
                    'yith-advanced-refund-system-for-woocommerce' ), $decimal ),
                'i18n_mon_decimal_error'            => sprintf( __( 'Please enter in monetary decimal format (%s) without thousand separators and
                currency symbols.', 'yith-advanced-refund-system-for-woocommerce' ), wc_get_price_decimal_separator() ),
                'i18n_country_iso_error'            => __( 'Please enter in country code with two capital letters.', 'yith-advanced-refund-system-for-woocommerce' ),
                'i18_sale_less_than_regular_error'  => __( 'Please enter in a lower value than the regular price.',
                    'yith-advanced-refund-system-for-woocommerce' ),
                'mon_decimal_point'                 => wc_get_price_decimal_separator(),
                'currency_format_num_decimals'      => wc_get_price_decimals(),
                'currency_format_symbol'            => get_woocommerce_currency_symbol( $currency ),
                'currency_format_decimal_sep'       => esc_attr( wc_get_price_decimal_separator() ),
                'currency_format_thousand_sep'      => esc_attr( wc_get_price_thousand_separator() ),
                'currency_format'                   => esc_attr( str_replace( array( '%1$s', '%2$s' ), array( '%s', '%v' ), get_woocommerce_price_format() ) ), // For accounting JS
                'create_coupon'                     => _x( 'Do you want to offer a coupon for:', 'Money amount after semicolon', 'yith-advanced-refund-system-for-woocommerce' ),
                'reject'                            => __( 'Are you sure you want to reject this request? This action cannot be undone.', 'yith-advanced-refund-system-for-woocommerce' ),
                'close_request'                     => __( "Are you sure you want to close this request? This action cannot be undone. Messages system will be closed and you won't be able to change the request status anymore.", 'yith-advanced-refund-system-for-woocommerce' ),
                'fill_fields'                       => __( 'Please enter a message', 'yith-advanced-refund-system-for-woocommerce' ),
                'success_message'                   => __( 'Message submitted successfully', 'yith-advanced-refund-system-for-woocommerce' )
            );

	        if ( 'yith_refund_request' == $current_screen->id || 'product' == $current_screen->id || 'edit-shop_order' == $current_screen->id ) {
		        wp_register_script(
			        'ywcars-admin',
			        YITH_WCARS_ASSETS_JS_URL . yit_load_js_file( 'ywcars-admin.js' ),
			        array( 'jquery', 'jquery-blockui', 'jquery-tiptip', 'accounting' ),
			        YITH_WCARS_VERSION );
		        wp_localize_script( 'ywcars-admin', 'ywcars_params', $params );
		        wp_enqueue_script( 'ywcars-admin' );
	        }

            // BULK ACTIONS
	        if ( YITH_WCARS_CUSTOM_POST_TYPE == $current_screen->post_type ) {
		        wp_register_script( 'ywcars-bulk-actions',
			        YITH_WCARS_ASSETS_JS_URL . yit_load_js_file( 'ywcars-bulk-actions.js' ),
			        array( 'jquery' ),
			        YITH_WCARS_VERSION,
			        true );
		        wp_localize_script( 'ywcars-bulk-actions', 'ywcars_bk_data', array(
			        'set_approved' => '<option value="ywcars-set-approved">' . __( 'Approve', 'yith-advanced-refund-system-for-woocommerce' ) . '</option>',
			        'set_rejected' => '<option value="ywcars-set-rejected">' . __( 'Reject', 'yith-advanced-refund-system-for-woocommerce' ) . '</option>',
		        ) );
		        wp_enqueue_script( 'ywcars-bulk-actions' );
	        }
        }

        public function add_wc_screen_id( $screen_ids ) {
            $screen_ids[] = 'yith_refund_request';
            return $screen_ids;
        }

        public function manage_meta_boxes( $post_type, $post ) {
            if ( $post_type && YITH_WCARS_CUSTOM_POST_TYPE == $post_type ) {
                remove_meta_box( 'submitdiv', YITH_WCARS_CUSTOM_POST_TYPE, 'side' );
                add_meta_box( 'ywcars-info-metabox',
                    __( 'Request info', 'yith-advanced-refund-system-for-woocommerce' ),
                    array( $this, 'ywcars_info_metabox_content' ), YITH_WCARS_CUSTOM_POST_TYPE, 'side', 'core'
                );
                add_meta_box( 'ywcars-items-metabox',
                    __( 'Items', 'yith-advanced-refund-system-for-woocommerce' ),
                    array( $this, 'ywcars_items_metabox_content' ), YITH_WCARS_CUSTOM_POST_TYPE, 'normal', 'core'
                );
                add_meta_box( 'ywcars-messages-metabox',
                    __( 'Request messages', 'yith-advanced-refund-system-for-woocommerce' ),
                    array( $this, 'ywcars_messages_metabox_content' ), YITH_WCARS_CUSTOM_POST_TYPE, 'normal', 'core'
                );

            }
            if ( $post_type && $post && 'shop_order' == $post_type ) {
                $order = wc_get_order( $post );
                $requests = yit_get_prop( $order, '_ywcars_requests', true );
                if ( $requests ) {
                    add_meta_box( 'ywcars-manage-refund-requests',
                        __( 'YITH Advanced Refunds', 'yith-advanced-refund-system-for-woocommerce' ),
                        array( $this, 'ywcars_manage_refund_requests_content' ), 'shop_order', 'side', 'core'
                    );
                }
            }
        }

        public function ywcars_info_metabox_content( $post ) {
            if ( ! $post ) {
                return;
            }
            $request = new YITH_Refund_Request( $post->ID );
            if ( ! ( $request instanceof YITH_Refund_Request && $request->exists() ) ) {
                return;
            }
            ?>
            <div class="submitbox" id="submitpost">
                <div id="minor-publishing">
                    <div id="misc-publishing-actions">
                        <div class="misc-pub-section ywcars-misc-pub-icons ywcars-request-status">
                            <span><?php _e( 'Status: ', 'yith-advanced-refund-system-for-woocommerce' ); ?></span>
                            <span><b><?php echo ywcars_get_request_status_by_key( $post->post_status ); ?></b></span>
                        </div>
                        <div class="misc-pub-section ywcars-misc-pub-icons ywcars-request-customer">
                            <span><?php _e( 'Customer:', 'yith-advanced-refund-system-for-woocommerce' ) ?></span>
                            <span><b><?php echo version_compare( WC()->version, '3.0.0', '<' ) ? $request->get_customer_link_legacy() : $request->get_customer_link(); ?></b></span>
                        </div>
                        <div class="misc-pub-section ywcars-misc-pub-icons ywcars-request-date">
                            <span><?php _e( 'Request date:', 'yith-advanced-refund-system-for-woocommerce' ); ?></span>
                            <span><b><?php echo $request->get_date(); ?></b></span>
                        </div>
                    </div>
                </div>
                <div id="major-publishing-actions">
                    <div id="publishing-action" style="display: none;">
                        <span class="spinner"></span>
                        <?php submit_button( __( 'Save' ), 'primary large', 'submit', false ); ?>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <?php
        }

        public function ywcars_items_metabox_content( $post ) {
	        if ( ! $post ) {
		        return;
	        }
	        wc_get_template( 'ywcars-items-table.php',
		        array( 'post' => $post ),
		        '',
		        YITH_WCARS_WC_TEMPLATE_PATH . 'admin/' );
        }

        public function ywcars_messages_metabox_content( $post ) {
            if ( ! $post ) {
                return;
            }
            $request = new YITH_Refund_Request( $post->ID );
            if ( ! ( $request instanceof YITH_Refund_Request ) ) {
                return;
            }
	        ?>
            <div class="ywcars_messages_history_block ywcars_block">
                <a class="ywcars_update_messages" title="<?php _e( 'Reload', 'yith-advanced-refund-system-for-woocommerce' ); ?>"></a>
                <h4 style="display: inline-block;"><?php _e( 'Message history:', 'yith-advanced-refund-system-for-woocommerce' ); ?></h4>
                <div class="ywcars_messages_history_frame"><?php YITH_Advanced_Refund_System_Request_Manager::load_messages( $request->ID ); ?></div>
            </div>

            <?php if( ! $request->is_closed ) : ?>
            <div class="ywcars_new_message_block ywcars_block">
                <form id="ywcars_form_admin_new_message" method="post" enctype="multipart/form-data">
                    <h4><?php _e( 'Answer the customer: ', 'yith-advanced-refund-system-for-woocommerce' ); ?></h4>
                    <div>
                        <textarea id="ywcars_new_message" name="ywcars_new_message" rows="8"></textarea>
                    </div>
                    <div>
                        <?php do_action( 'ywcars_admin_before_submit' ); ?>
                        <button class="button button-primary" id="ywcars_submit_message" style="float:right;">
                            <span><?php _e( 'Submit', 'yith-advanced-refund-system-for-woocommerce' ) ?></span>
                        </button>
                    </div>
                    <div class="ywcars_block">
                        <div class="ywcars_alert ywcars_success_alert">
                            <span class="ywcars_close_alert">x</span>
                            <span class="ywcars_alert_content"></span>
                        </div>
                        <div class="ywcars_alert ywcars_error_alert">
                            <span class="ywcars_close_alert">x</span>
                            <span class="ywcars_alert_content"></span>
                        </div>
                    </div>
                </form>
            </div>
	        <?php endif;
        }

        public function ywcars_manage_refund_requests_content( $post ) {
            if ( $post ) {
                $order = wc_get_order( $post );
                ?>
                <ul class="order_actions submitbox">
                    <li class="wide">
                        <?php
                        $requests = yit_get_prop( $order, '_ywcars_requests', true );
                        foreach ( $requests as $request_id ) {
                            $request = new YITH_Refund_Request( $request_id );
                            if ( ! ( $request instanceof YITH_Refund_Request && $request->exists() ) ) {
                                continue;
                            }
	                        $open_request = apply_filters( 'ywcars_open_request', ( 'ywcars-approved' != $request->status && 'ywcars-rejected' != $request->status && 'trash' != $request->status ), $request );
	                        $status_title = ywcars_get_request_status_by_key( $request->status );
	                        $src = YITH_WCARS_ASSETS_URL . 'images/' . $request->status . '.png';
	                        $request_link = admin_url( 'post.php?post=' . absint( $request_id ) . '&action=edit' );
	                        $link_content = sprintf( __( 'Request #%d', 'yith-advanced-refund-system-for-woocommerce' ), $request_id );
	                        $title_if_finished_request = 'disabled title="' . __( 'This request can neither be Approved nor Rejected.',
                                    'yith-advanced-refund-system-for-woocommerce' ) . '"';
	                        ?>
                            <div class="ywcars_action_list_element">
                                <input type="checkbox" name="ywcars_selected_requests[]"
                                       value="<?php echo $request_id; ?>" <?php echo $open_request ? '' : $title_if_finished_request; ?>>
		                        <?php if ( 'trash' == $request->status ) : ?>
                                    <span class="ywcars_trash_status_icon"></span>
		                        <?php else : ?>
                                    <img class="ywcars_request_actions_icons" title="<?php echo $status_title; ?>" src="<?php echo $src; ?>">
		                        <?php endif; ?>
                                <table class="ywcars_actions_list_table">
                                    <tbody>
                                    <tr>
                                        <td>
                                            <a href="<?php echo esc_url( $request_link ); ?>"><?php echo $link_content; ?></a>
                                            <span> - <strong><?php echo wc_price( $request->refund_total ); ?></strong></span>
                                        </td>
                                    </tr>
			                        <?php if ( $request->whole_order ) : ?>
                                        <tr>
                                            <td>
                                                <span><?php _e( 'Whole order', 'yith-advanced-refund-system-for-woocommerce' ); ?></span>
                                            </td>
                                        </tr>
			                        <?php elseif ( $request->product_id ) : ?>
				                        <?php
				                        $product = wc_get_product( $request->product_id );
				                        $product_link = admin_url( 'post.php?post=' . absint( $request->product_id ) . '&action=edit' );
				                        ?>
                                        <tr>
                                            <td>
                                                <span><a href="<?php echo esc_url( $product_link ); ?>"><?php echo $product->get_title(); ?></a></span>
                                            </td>
                                        </tr>
			                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
	                        <?php
                        }
                        ?>
                    </li>
                    <li class="wide" id="ywcars-actions">
                        <select name="ywcars_refund_request_actions">
                            <option value=""><?php _e( 'Actions', 'yith-advanced-refund-system-for-woocommerce' ); ?></option>
                            <option value="ywcars-set-approved"><?php _e( 'Approve selected', 'yith-advanced-refund-system-for-woocommerce' ); ?></option>
                            <option value="ywcars-set-rejected"><?php _e( 'Reject selected', 'yith-advanced-refund-system-for-woocommerce' ); ?></option>
                        </select>
                        <button class="button wc-reload" title="<?php esc_attr_e( 'Apply', 'yith-advanced-refund-system-for-woocommerce' ); ?>">
                            <span><?php _e( 'Apply', 'yith-advanced-refund-system-for-woocommerce' ); ?></span>
                        </button>
                    </li>

                </ul>
                <?php
            }
        }

	    public function save_refund_request_actions( $post_id, $post ) {
		    if ( empty( $_POST['ywcars_refund_request_actions'] ) || empty( $_POST['ywcars_selected_requests'] ) ) {
		        return;
		    }
		    $action   = $_POST['ywcars_refund_request_actions'];
		    $requests = $_POST['ywcars_selected_requests'];
		    $this->process_requests_array( $requests, $action );
	    }

        public function add_cpt_columns( $posts_columns ) {
            unset( $posts_columns['title'] );
	        unset( $posts_columns['date'] );
	        $posts_columns['status']   = '';
	        $posts_columns['title']    = __( 'Title', 'yith-advanced-refund-system-for-woocommerce' );
            $posts_columns['date']     = __( 'Date', 'yith-advanced-refund-system-for-woocommerce' );
	        $posts_columns['order']    = __( 'Order', 'yith-advanced-refund-system-for-woocommerce' );
	        $posts_columns['customer'] = __( 'Customer', 'yith-advanced-refund-system-for-woocommerce' );
            $posts_columns['product']  = __( 'Product', 'yith-advanced-refund-system-for-woocommerce' );

            return $posts_columns;
        }

        public function add_cpt_columns_content( $column_name, $post_id ) {
            if ( ! $post_id ) {
                return;
            }
	        $request = new YITH_Refund_Request( $post_id );
	        if ( ! $request->exists() ) {
	            return;
	        }
	        if ( $column_name == 'order') {
		        if ( $request->order_id ) {
			        echo '<a href="'
			             . admin_url( 'post.php?post=' . absint( $request->order_id ) . '&action=edit' ) . '" >'
			             . '#' . $request->order_id . '</a>';
		        }
	        }
	        if ( $column_name == 'product') {
		        if ( $request->whole_order ) {
			        echo '<span style="font-weight: 600;">' . __( 'Whole order', 'yith-advanced-refund-system-for-woocommerce' ) . '</span>';
		        } else if ( $request->product_id ) {
			        $product = wc_get_product( $request->product_id );
			        $id = yit_get_base_product_id( $product );
			        echo '<a href="' . admin_url( 'post.php?post=' . $id . '&action=edit' ) . '" >'
			             . $product->get_formatted_name()
			             . '</a>';
		        }
	        }
	        if ( $column_name == 'qty') {
		        if ( $request->whole_order ) {
			        echo '-';
		        } else if ( $request->qty ) {
			        echo $request->qty;
		        }
	        }
	        if ( $column_name == 'customer') {
		        if ( $request->customer_id ) {
			        echo version_compare( WC()->version, '3.0.0', '<' ) ? $request->get_customer_link_legacy() : $request->get_customer_link();
		        }
	        }
	        if ( $column_name == 'status') {
		        $status_title = ywcars_get_request_status_by_key( $request->status );
		        $src = YITH_WCARS_ASSETS_URL . 'images/' . $request->status . '.png';
		        ?>
		        <?php if ( 'trash' == $request->status ) : ?>
                    <span class="ywcars_trash_status_icon"></span>
		        <?php else : ?>
                    <img title="<?php echo $status_title; ?>" src="<?php echo $src; ?>">
		        <?php endif; ?>
		        <?php
	        }

        }

        public function add_refund_request_column_orders_page( $posts_columns ) {
            $posts_columns['ywcars-requests'] = __( 'Refund status', 'yith-advanced-refund-system-for-woocommerce' );
            return $posts_columns;
        }

        public function add_refund_request_column_orders_page_content( $column_name, $post_id ) {
            if ( 'ywcars-requests' == $column_name && $post_id ) {
                $order = wc_get_order( $post_id );
                if ( $order ) {
                    $requests = yit_get_prop( $order, '_ywcars_requests', true );
                    if ( $requests ) {
                        echo '<div class="ywcars_requests_wrapper">';
                        $first = true;
	                    foreach ( $requests as $request_id ) {
		                    $request = new YITH_Refund_Request( $request_id );
		                    if ( ! $request->exists() ) {
			                    continue;
		                    }
		                    if ( $request ) : ?>
			                    <?php
			                    $status_title = ywcars_get_request_status_by_key( $request->status );
			                    $src          = YITH_WCARS_ASSETS_URL . 'images/' . $request->status . '.png';
			                    $request_link = admin_url( 'post.php?post=' . absint( $request_id ) . '&action=edit' );
			                    $link_title   = $request->whole_order ? __( 'Request for entire order', 'yith-advanced-refund-system-for-woocommerce' ) : esc_html( wc_get_product( $request->product_id )->get_name() );
			                    $link_content = sprintf( __( 'Request #%d', 'yith-advanced-refund-system-for-woocommerce' ), $request->ID );

			                    $class  = $first ? 'first ' : '';
			                    $class .= 'ywcars_single_request';
			                    $style  = $first ? '' : 'style="display: none;"';
			                    ?>
                                <div class="<?php echo $class; ?>" <?php echo $style; ?>>
				                    <?php if ( 'trash' == $request->status ) : ?>
                                        <span class="ywcars_request_actions_icons ywcars_trash_status_icon"></span>
				                    <?php else : ?>
                                        <img class="ywcars_request_actions_icons" title="<?php echo $status_title; ?>" src="<?php echo $src; ?>">
				                    <?php endif; ?>
                                    <a href="<?php echo esc_url( $request_link ) ?>" title="<?php echo $link_title; ?>"><?php echo $link_content; ?></a>
                                    <?php if ( $first && 1 < count( $requests ) ) : ?>
                                    <span> - </span>
                                    <a href="" class="ywcars_requests_toggle_button"><?php _e( 'More', 'yith-advanced-refund-system-for-woocommerce' ); ?></a>
                                    <?php endif; ?>
                                </div>
                                <?php $first = false; ?>
		                    <?php endif;
	                    }
	                    echo '</div>';
                    }
                }
            }
        }

        public function add_refund_requests_view_on_orders( $views ) {
            $order_statuses = wc_get_order_statuses();

            $args = array(
                'post_type'   => wc_get_order_types(),
                'post_status' => array_keys( $order_statuses ),
                'numberposts' => - 1,
                'fields'      => 'ids',
                'meta_query'  => array(
                    array(
                        'key'     => '_ywcars_requests',
                        'compare' => 'EXISTS'
                    )
                ));
            // Get all order ids with Refund requests
            $order_ids = get_posts( $args );
            if ( $order_ids ) {
                $filter_url = esc_url( add_query_arg( array( 'post_type' => 'shop_order', 'ywcars-refund' => true ), admin_url( 'edit.php' ) ) );
                $filter_class = isset( $_GET['ywcars-refund'] ) ? 'current' : '';
                $views[ 'ywcars_refund' ] = sprintf( '<a href="%s" class="%s">%s <span class="count">(%d)</span></a>',
                    $filter_url, $filter_class, __( 'Refund requests' ), count( $order_ids ) );
            }
            return $views;
        }

        public function refund_requests_view_filters() {
            if( isset( $_GET['ywcars-refund'] ) && $_GET['ywcars-refund'] ){
                add_filter( 'posts_join', array( $this, 'filter_order_join_for_view' ) );
                add_filter( 'posts_where', array( $this, 'filter_order_where_for_view' ) );
            }
        }

        public function filter_order_join_for_view( $join ) {
            global $wpdb;

            $join .= " LEFT JOIN {$wpdb->prefix}postmeta as i ON {$wpdb->posts}.ID = i.post_id";

            return $join;
        }

        public function filter_order_where_for_view( $where ) {
            global $wpdb;

            $where .= $wpdb->prepare( " AND i.meta_key = %s", array( '_ywcars_requests' ) );

            return $where;
        }

	    public function bulk_actions() {
		    global $typenow;

		    $post_type     = $typenow;
		    if ( YITH_WCARS_CUSTOM_POST_TYPE != $post_type ) {
			    return;
		    }
		    $page = isset( $_GET['paged'] ) ? '&paged=' . $_GET['paged'] : '';
		    $url           = 'edit.php?post_type=' . $post_type . $page;
		    $sendback      = admin_url( $url );
		    $wp_list_table = _get_list_table( 'WP_Posts_List_Table' );
		    $action        = $wp_list_table->current_action();
		    if ( ( $action == 'ywcars-set-approved' ) || ( $action == 'ywcars-set-rejected' ) ) {
			    $post_ids = $_GET['post'];
			    check_admin_referer( 'bulk-posts' );
			    if ( ! $post_ids ) {
				    return;
			    }
			    $this->process_requests_array( $post_ids, $action );
			    wp_redirect( $sendback );
			    exit();
		    }
	    }

	    // Process a given array of YITH_Refund_Request IDs in order to set them Approved or Rejected
	    public function process_requests_array( $requests, $action ) {
		    foreach ( $requests as $request_id ) {
			    $request = new YITH_Refund_Request( $request_id );
			    if ( ! $request->exists() )
				    continue;
			    if ( $request->status == 'ywcars-approved' || $request->status == 'ywcars-rejected' )
				    continue;
			    if ( $action == 'ywcars-set-approved' )
				    do_action( 'ywcars_process_automatic_request', $request->ID );
			    if ( $action == 'ywcars-set-rejected' )
				    $request->set_rejected();
		    }
        }


        /**
         * Show the premium landing
         *
         * @author Carlos Javier Mora de Eugenio 
         * @since 1.0.1
         * @return void
         */
        public function show_premium_landing(){
            if( file_exists( YITH_WCARS_TEMPLATE_PATH . 'premium/premium.php' ) && $this->show_premium_landing ){
                require_once( YITH_WCARS_TEMPLATE_PATH . 'premium/premium.php' );
            }
        }

        /**
         * Get the premium landing uri
         *
         * @since   1.0.0
         * @author   Carlos Rodríguez <carlos.rodriguez@yourinspiration.it>
         * @return  string The premium landing link
         */
        public function get_premium_landing_uri()
        {
            return defined('YITH_REFER_ID') ? $this->_premium_landing_url . '?refer_id=' . YITH_REFER_ID : $this->_premium_landing_url.'?refer_id=1030585';
        }




    }
}