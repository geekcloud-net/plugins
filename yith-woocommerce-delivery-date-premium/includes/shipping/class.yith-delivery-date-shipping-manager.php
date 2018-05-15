<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( !class_exists( 'YITH_Delivery_Date_Shipping_Manager' ) ) {

    class YITH_Delivery_Date_Shipping_Manager {

        protected static $_instance;
        protected        $shipping_method;

        public function __construct() {
            add_action( 'admin_init', array( $this, 'set_shipping_method' ), 99 );


            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );
            add_action( 'wp_ajax_update_datepicker', array( $this, 'update_datepicker' ) );
            add_action( 'wp_ajax_nopriv_update_datepicker', array( $this, 'update_datepicker' ) );
            add_action( 'wp_ajax_update_timeslot', array( $this, 'update_timeslot_ajax' ) );
            add_action( 'wp_ajax_nopriv_update_timeslot', array( $this, 'update_timeslot_ajax' ) );
            add_action( 'wp_ajax_update_carrier_list', array( $this, 'update_carrier_list_by_shipping_method' ) );
            add_action( 'wp_ajax_nopriv_update_carrier_list', array( $this, 'update_carrier_list_by_shipping_method' ) );


            add_action( 'woocommerce_after_checkout_validation', array( $this, 'validate_checkout_width_delivery_date' ), 10 );

            if ( version_compare( WC()->version, '2.7.0', '>=' ) ) {

                add_action( 'woocommerce_checkout_create_order', array( $this, 'add_delivery_date_info_order_meta' ) );
                add_action( 'woocommerce_checkout_shipping', array( $this, 'print_delivery_from' ), 20 );
            } else {
                add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'add_delivery_date_info_order_meta' ), 10, 1 );
                add_action( 'woocommerce_after_order_notes', array( $this, 'print_delivery_from' ), 20 );
            }
            add_action( 'woocommerce_checkout_update_order_review', array( $this, 'set_timeslot_session' ) );
            add_action( 'woocommerce_cart_calculate_fees', array( $this, 'add_timeslot_fee' ), 10 );

            add_action( 'woocommerce_order_details_after_order_table', array( $this, 'show_delivery_order_details_after_order_table' ) );

            add_action( 'woocommerce_order_status_changed', array( $this, 'manage_order_event' ), 20, 3 );

            //add delivery date information into woocommerce email
            add_action( 'woocommerce_email_order_meta', array( $this, 'print_delivery_date_into_email' ), 10, 4 );

        }

        /**
         * @author YITHEMES
         * @since 1.0.0
         * @return YITH_Delivery_Date_Shipping_Manager
         */
        public static function get_instance() {

            if ( is_null( self::$_instance ) ) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }

        /**
         * get the shipping method and add custom form fields filter
         *
         * @author YITHEMES
         * @since 1.0.0
         */
        public function set_shipping_method() {

            WC()->shipping->load_shipping_methods();
            $this->shipping_method = wp_list_pluck( WC()->shipping()->get_shipping_methods(), 'id' );


            if ( !empty( $this->shipping_method ) ) {

                foreach ( $this->shipping_method as $key => $shipping_id ) {

                    if ( apply_filters( 'ywcdd_disable_delivery_date_for_shipping_method', false, $key, $shipping_id ) ) {
                        continue;
                    }
                    add_filter( 'woocommerce_settings_api_form_fields_' . $shipping_id, array( $this, 'add_custom_fields' ), 99 );
                    /**
                     * added compatibility with wc 2.6
                     */
                    add_filter( 'woocommerce_shipping_instance_form_fields_' . $shipping_id, array( $this, 'add_custom_fields' ), 99 );

                }
            }
        }

        /**
         * @author YITHEMES
         * @since 1.0.0
         */
        public function enqueue_frontend_scripts() {

            $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

            if ( is_checkout() ) {
                wp_enqueue_script( 'ywcdd_frontend', YITH_DELIVERY_DATE_ASSETS_URL . 'js/yith_deliverydate_frontend' . $suffix . '.js', array( 'jquery', 'jquery-ui-datepicker' ), YITH_DELIVERY_DATE_VERSION, true );

                $params = array(
                    'ajax_url'        => admin_url( 'admin-ajax.php', is_ssl() ? 'https' : 'http' ),
                    'actions'         => array(
                        'update_datepicker'   => 'update_datepicker',
                        'update_timeslot'     => 'update_timeslot',
                        'update_carrier_list' => 'update_carrier_list'
                    ),
                    'timeformat'      => 'H:i',
                    'dateformat'      => 'yy-mm-dd',
                    'numberOfMonths'  => wp_is_mobile() ? 1 : 2,
                    'open_datepicker' => ywcdd_get_delivery_mode()

                );

                wp_localize_script( 'ywcdd_frontend', 'ywcdd_params', $params );
                //   wp_register_style( 'jquery-ui-style-css', '//ajax.googleapis.com/ajax/libs/jqueryui/1.11.3/themes/smoothness/jquery-ui.css', array(), '1.11.3' );
                wp_enqueue_style( 'ywcdd_style', YITH_DELIVERY_DATE_ASSETS_URL . 'css/yith_delivery_date_frontend.css', array(), YITH_DELIVERY_DATE_VERSION );
            }
        }

        /**
         * add custom form fields in shipping method
         *
         * @author YITHEMES
         * @since 1.0.0
         *
         * @param array $defaults
         * @param array $form_fields
         *
         * @return array
         */
        public function add_custom_fields( $form_fields ) {
            $all_processing_method = get_posts( array( 'post_type' => 'yith_proc_method', 'post_status' => 'publish', 'numberposts' => -1 ) );

            $options = array();

            $options[ '' ] = __( 'Select an order processing method', 'yith-woocommerce-delivery-date' );

            foreach ( $all_processing_method as $key => $method ) {
                $options[ $method->ID ] = get_the_title( $method->ID );
            }

            $form_fields[ 'select_process_method' ] = array(
                'title'   => __( 'Order Processing Method', 'yith-woocommerce-delviery-date' ),
                'type'    => 'select',
                'default' => '',
                'class'   => 'ywcdd_processing_method wc-enhanced-select',
                'options' => $options,
            );

            $form_fields[ 'set_method_as_mandatory' ] = array(
                'title'       => __( 'Set as mandatory', 'yith-woocommerce-delivery-date' ),
                'type'        => 'checkbox',
                'default'     => 'no',
                'class'       => 'ywcdd_set_mandatory',
                'description' => __( 'If enabled, the customers must select a date for the delivery', 'yith-woocommerce-delivery-date' )
            );

            return $form_fields;
        }

        /**
         * @param WC_Checkout $checkout
         */
        public function print_delivery_from() {

            $chosen_methods  = WC()->session->get( 'chosen_shipping_methods' );
            $chosen_shipping = $chosen_methods[ 0 ];

            $this->load_delivery_template( $chosen_shipping );

        }

        public function load_delivery_template( $shipping_method, $html = false ) {

            $shipping_settings = $this->get_woocommerce_shipping_option( $shipping_method );

            $processing_method = isset( $shipping_settings[ 'select_process_method' ] ) ? $shipping_settings[ 'select_process_method' ] : '';
            $is_mandatory      = isset( $shipping_settings[ 'set_method_as_mandatory' ] ) ? $shipping_settings[ 'set_method_as_mandatory' ] : 'no';

            $available_days = array();
            $carrier_system = get_option( 'yith_delivery_date_enable_carrier_system', 'no' ) === 'yes';
            if ( !$carrier_system ) {

                $timezone_format = 'Y-m-d H:i:s';
                $now             = strtotime( date_i18n( $timezone_format ) );


                $shipping_day = $this->get_first_available_day_for_shipping( $now, $processing_method );

                $delivery_time = $this->get_first_carrier_delivery_day( $shipping_day );

                $available_days = $this->get_available_date_range( $delivery_time );

            }
            if ( $html ) {
                return wc_get_template_html( 'woocommerce/checkout/delivery-date-content.php', array( 'shipping_id' => $shipping_method, 'is_mandatory' => $is_mandatory, 'available_days' => $available_days, 'processing_method' => $processing_method ), YITH_DELIVERY_DATE_TEMPLATE_PATH, YITH_DELIVERY_DATE_TEMPLATE_PATH );
            } else {
                wc_get_template( 'woocommerce/checkout/delivery-date-content.php', array( 'shipping_id' => $shipping_method, 'is_mandatory' => $is_mandatory, 'available_days' => $available_days, 'processing_method' => $processing_method ), YITH_DELIVERY_DATE_TEMPLATE_PATH, YITH_DELIVERY_DATE_TEMPLATE_PATH );
            }
        }

        /**
         * @author Salvatore Strano
         * get the woocommerce shipping options by shipping name
         *
         * @param string $shipping_option
         *
         * @return array
         */
        public function get_woocommerce_shipping_option( $shipping_option ) {


            if ( version_compare( WC()->version, '2.6.0', '>=' ) ) {
                $shipping_option = str_replace( ':', '_', $shipping_option );
            }


            $shipping_settings = get_option( 'woocommerce_' . $shipping_option . '_settings' );

            return apply_filters( 'ywcdd_get_shipping_method_option', $shipping_settings, $shipping_option );
        }

        public function update_carrier_list_by_shipping_method() {

            $shipping_id = isset( $_POST[ 'ywcdd_shipping_id' ] ) ? $_POST[ 'ywcdd_shipping_id' ] : '';

            $shipping_settings = $this->get_woocommerce_shipping_option( $shipping_id );

            $processing_method = !empty( $shipping_settings[ 'select_process_method' ] ) ? $shipping_settings[ 'select_process_method' ] : '';
            $template          = '';
            $change_template   = true;

            $current_proc_method = isset( $_POST[ 'ywcdd_process_method' ] ) ? $_POST[ 'ywcdd_process_method' ] : '';

            if ( $current_proc_method == $processing_method ) {
                $change_template = false;
            }

            if ( $processing_method != '' && $change_template ) {

                $shipping_id = isset( $_POST[ 'ywcdd_shipping_id' ] ) ? $_POST[ 'ywcdd_shipping_id' ] : '';
                $template    = $this->load_delivery_template( $shipping_id, true );
            }


            wp_send_json( array( 'template' => $template, 'update_delivery_form' => $change_template ) );

        }

        /**
         * compute shipping date from delivery date
         *
         * @author YITHEMES
         * @since 1.0.0;
         *
         * @param $delivery_date
         * @param $day_for_ship
         *
         * @return bool|string
         */
        public function compute_shipping_date( $delivery_date, $day_for_ship ) {

            $delivery_time = strtotime( $delivery_date );

            $shipping_date = $delivery_time - ( absint( $day_for_ship ) * DAY_IN_SECONDS );

            $format = get_option( 'date_format' );

            return date( $format, $shipping_date );

        }

        /**
         * find the right min days for process an order
         *
         * @author YITHEMES
         * @since 1.0.0
         *
         * @param $process_shipping_method_id
         *
         * @return mixed|string
         */
        public function find_day_for_shipping( $process_shipping_method_id ) {

            $base_day     = apply_filters( 'yith_delivery_date_base_shipping_day', get_post_meta( $process_shipping_method_id, '_ywcdd_minworkday', true ) );
            $new_base_day = '';

            $category_rules = get_option( 'yith_new_shipping_day_cat' );

            $product_rules = get_option( 'yith_new_shipping_day_prod' );

            $new_base_days = array();

            /**
             * @var WC_Cart
             */
            $cart = WC()->cart;


            if ( isset( $cart ) && !$cart->is_empty() ) {

                foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
                    /**
                     * @var WC_Product $_product
                     */
                    $_product = $values[ 'data' ];

                    $new_base_days[] = $this->get_custom_base_day_for_product( $_product, $product_rules, $category_rules );

                }

            }

            $max_day = count( $new_base_days ) > 0 ? max( $new_base_days ) : 0;

            return $max_day == 0 ? $base_day : $max_day;
        }

        /**
         *
         * @param WC_Product $product
         * @param array      $product_rule
         * @param array      $category_rule
         *
         * @return int;
         */
        public function get_custom_base_day_for_product( $product, $product_rule, $category_rule ) {

            $base_day        = 0;
            $product_id      = yit_get_product_id( $product );
            $product_base_id = false;


            if ( $product->is_type( 'variation' ) ) {
                $product_id      = yit_get_prop( $product, version_compare( WC()->version, '3.0.0', '>=' ) ? 'id' : 'variation_id' );
                $product_base_id = yit_get_base_product_id( $product );
            }


            if ( isset( $product_rule[ $product_id ] ) ) {
                $base_day = $product_rule[ $product_id ][ 'need_process_day' ];
            } elseif ( ( $product_base_id && isset( $product_rule[ $product_base_id ] ) ) ) {

                $base_day = $product_rule[ $product_base_id ][ 'need_process_day' ];
            } else {
                if ( $product_base_id ) {
                    $terms = wp_get_post_terms( $product_base_id, 'product_cat' );
                } else {
                    $terms = wp_get_post_terms( $product_id, 'product_cat' );
                }
                $term_ids = wp_list_pluck( $terms, 'term_id' );


                foreach ( $terms as $term ) {


                    if ( $term->parent == 0 && in_array( $term->parent, $term_ids ) ) {

                        continue;
                    }

                    if ( isset( $category_rule[ $term->term_id ] ) ) {

                        $base_day = max( $base_day, $category_rule[ $term->term_id ][ 'need_process_day' ] );
                    }
                }
            }

            return $base_day;

        }

        /**
         *
         * @param $current_time
         * @param $process_shipping_id
         *
         * @return mixed
         */
        public function get_first_available_day_for_shipping( $current_time, $process_shipping_id ) {

            //get all works day
            $w_day = $this->get_process_shipping_worksday( $process_shipping_id );

            $min_workdays = $this->find_day_for_shipping( $process_shipping_id );//get_post_meta( $process_shipping_id, '_ywcdd_minworkday', true );

            //$now = strtotime( 'now midnight' )+absint( $min_workdays ) * DAY_IN_SECONDS;

            $timezone_format = 'Y-m-d H:i:s';
            $now             = strtotime( date_i18n( $timezone_format ) );


            $now = strtotime( 'midnight', $now ) + absint( $min_workdays ) * DAY_IN_SECONDS;

            $first_shipping_date = $current_time + absint( $min_workdays ) * DAY_IN_SECONDS;

            $shipping_dates = array();
            $current_day    = strtolower( date( "D", $first_shipping_date ) );
            $date_from      = date( 'Y/m/d', $first_shipping_date );

            $holiday_from = YITH_Delivery_Date_Calendar()->get_calendar_holiday_from( $process_shipping_id, $date_from );
            $holiday_to   = YITH_Delivery_Date_Calendar()->get_calendar_holiday_to( $process_shipping_id, $date_from );


            $all_holidays = $this->get_all_holidays( $holiday_from, $holiday_to );


            foreach ( $w_day as $key => $day ) {

                $day_name = strtolower( $day[ 'day' ] );

                $day_name_full = strtr( $day_name, yith_get_worksday( false ) );

                $new_date = strtotime( 'next ' . $day_name_full, $now - 1 );


                while ( in_array( $new_date, $all_holidays ) ) {

                    $new_date += 7 * DAY_IN_SECONDS;
                }

                if ( $min_workdays == 0 && strcmp( $current_day, $day_name ) == 0 && $day[ 'timelimit' ] != '' ) {

                    $this_time = strtotime( $day[ 'timelimit' ], $now );

                    if ( $this_time < $first_shipping_date ) {
                        $new_date += 7 * DAY_IN_SECONDS;
                    }
                }

                $shipping_dates[] = $new_date;
            }

            return empty( $shipping_dates ) ? false : min( $shipping_dates );
        }

        /**
         * @param $shipping_date
         * @param $carrier_id
         *
         * @return bool|mixed
         */
        public function get_first_carrier_delivery_day( $shipping_date, $carrier_id = -1 ) {

            if ( $carrier_id == -1 ) {
                $min_dd            = apply_filters( 'yith_delivery_date_base_carrier_day', get_option( 'yith_delivery_date_range_day' ), $carrier_id );
                $delivery_time     = $shipping_date + absint( $min_dd ) * DAY_IN_SECONDS;
                $delivery_worksday = get_option( 'yith_delivery_date_workday' );
            } else {
                $min_dd            = apply_filters( 'yith_delivery_date_base_carrier_day', get_post_meta( $carrier_id, '_ywcdd_dayrange', true ), $carrier_id );
                $delivery_time     = $shipping_date + absint( $min_dd ) * DAY_IN_SECONDS;
                $delivery_worksday = get_post_meta( $carrier_id, '_ywcdd_workday', true );

            }

            $delivery_worksday = empty( $delivery_worksday ) ? array() : $delivery_worksday;
            $delivery_av_date  = array();


            if ( count( $delivery_worksday ) > 0 ) {
                foreach ( $delivery_worksday as $key => $dd ) {

                    $select_day = strtolower( $dd );
                    $day_name   = strtr( $select_day, yith_get_worksday( false ) );

                    $new_date = strtotime( 'next ' . $day_name, $delivery_time - 1 );

                    $delivery_av_date[] = $new_date;
                }
            }


            return empty( $delivery_av_date ) ? false : min( $delivery_av_date );
        }

        /**
         * @author YITHEMES
         * @since 1.0.0
         * get only worksday for shipping_method
         *
         * @param $process_shipping_id
         */
        public function get_process_shipping_worksday( $process_shipping_id ) {

            $works_day = get_post_meta( $process_shipping_id, '_ywcdd_list_day', true );

            if ( empty( $works_day ) ) {
                $works_day = array();
            }

            foreach ( $works_day as $key => $wday ) {

                if ( $wday[ 'enabled' ] == 'no' ) {
                    unset( $works_day[ $key ] );
                }
            }

            return $works_day;
        }

        /**
         * get all available dates for delivery
         *
         * @author YITHEMES
         * @since 1.0.0
         *
         * @param      $from
         * @param bool $carrier_id
         *
         * @return array
         */
        public function get_available_date_range( $from, $carrier_id = -1 ) {

            $all_select_days = array();
            if ( $carrier_id != -1 ) {

                $max_selected_range = get_post_meta( $carrier_id, '_ywcdd_max_selec_orders', true );
                $delivery_worksday  = get_post_meta( $carrier_id, '_ywcdd_workday', true );
            } else {
                $max_selected_range = get_option( 'yith_delivery_date_max_range', 30 );
                $delivery_worksday  = get_option( 'yith_delivery_date_workday' );
            }
            $delivery_worksday = empty( $delivery_worksday ) ? array() : $delivery_worksday;
            $max_dd_av_date    = $from + ( absint( $max_selected_range ) * DAY_IN_SECONDS );

            $current_day = $from;

            $all_select_days = array();
            $date_from       = date( 'Y/m/d', $from );

            $holiday_from = YITH_Delivery_Date_Calendar()->get_calendar_holiday_from( $carrier_id, $date_from );
            $holiday_to   = YITH_Delivery_Date_Calendar()->get_calendar_holiday_to( $carrier_id, $date_from );

            $all_holidays = $this->get_all_holidays( $holiday_from, $holiday_to );

            $has_timeslot = $this->is_timeslot_exist( $carrier_id );
            $count_days   = 0;

            while ( $count_days < $max_selected_range ) {

                $date                = date( 'Y-m-d', $current_day );
                $day1                = strtolower( date( 'D', $current_day ) );
                $time_slot_available = $this->get_available_timeslot( $carrier_id, $date );

                if ( in_array( $day1, $delivery_worksday ) && !in_array( $current_day, $all_holidays ) && ( !$has_timeslot || count( $time_slot_available ) > 0 ) ) {

                    $all_select_days[] = $current_day;
                    $count_days++;
                }
                $current_day += DAY_IN_SECONDS;


            }
            $all_select_days = apply_filters( 'ywcdd_add_custom_delivery_dates', array_unique( $all_select_days ) );
            asort( $all_select_days );

            $all_select_days = $this->get_format_available_date_range( $all_select_days );

            return $all_select_days;
        }

        public function get_format_available_date_range( $date_range ) {

            $available_days = array();

            foreach ( $date_range as $date ) {
                $available_days[] = date( 'Y-m-d', $date );
            }

            return $available_days;
        }

        /**
         * @param array $from_rows
         * @param array $to_row
         *
         * @return array
         */
        public function get_all_holidays( $from_rows, $to_row ) {

            $all_holidays = array();

            $all_holidays = $this->parse_holidays( $from_rows, $all_holidays );
            $all_holidays = $this->parse_holidays( $to_row, $all_holidays );

            return array_unique( $all_holidays );
        }

        public function parse_holidays( $holidays, $all_holidays ) {

            foreach ( $holidays as $row ) {

                $start        = strtotime( $row[ 'start' ] );
                $end          = strtotime( $row[ 'end' ] );
                $new_holidays = $this->get_date_range( $start, $end );
                $all_holidays = array_merge( $all_holidays, $new_holidays );
            }

            return $all_holidays;
        }

        /**
         * @param int $from
         * @param int $to
         *
         * @return array
         */
        public function get_date_range( $from, $to ) {

            $range = array();
            while ( $from <= $to ) {
                $range[] = $from;
                $from    += DAY_IN_SECONDS;
            }

            return $range;
        }

        /**
         * @param $carrier_id
         * @param $shipping_id
         * @param $date_delivery
         *
         * @return bool|int
         */
        public function get_shipping_day( $carrier_id = -1, $shipping_id, $date_delivery ) {
            if ( $carrier_id == -1 ) {

                $min_carrier_days = apply_filters( 'yith_delivery_date_base_carrier_day', get_option( 'yith_delivery_date_range_day' ), $carrier_id );
                $workday_carrier  = get_option( 'yith_delivery_date_workday' );
            } else {

                $min_carrier_days = apply_filters( 'yith_delivery_date_base_carrier_day', get_post_meta( $carrier_id, '_ywcdd_dayrange', true ), $carrier_id );
                $workday_carrier  = get_post_meta( $carrier_id, '_ywcdd_workday', true );
            }

            $workday_admin = wp_list_pluck( $this->get_process_shipping_worksday( $shipping_id ), 'day' );


            $delivery_time = strtotime( $date_delivery ) - $min_carrier_days * DAY_IN_SECONDS;

            $timezone_format = 'Y-m-d H:i:s';
            $now             = strtotime( date_i18n( $timezone_format ) );
            $now             = strtotime( 'midnight', $now );

            $worksday = array_intersect( $workday_carrier, $workday_admin );

            $date_from    = date( 'Y-m-d', $now );
            $holiday_from = YITH_Delivery_Date_Calendar()->get_calendar_holiday_from( $shipping_id, $date_from );
            $holiday_to   = YITH_Delivery_Date_Calendar()->get_calendar_holiday_to( $shipping_id, $date_from );

            $all_holidays = $this->get_all_holidays( $holiday_from, $holiday_to );


            while ( $delivery_time > $now ) {

                $current_day = date( 'D', $delivery_time );

                if ( in_array( strtolower( $current_day ), $worksday ) && !in_array( $delivery_time, $all_holidays ) ) {

                    return $delivery_time;
                }
                $delivery_time -= DAY_IN_SECONDS;

            }

            return $now;

        }

        /**
         * set all available delivery date
         *
         * @author YITHEMES
         * @since 1.0.0
         */
        public function update_datepicker() {

            if ( isset( $_POST[ 'ywcdd_carrier_id' ] ) ) {

                $carrier_id      = $_POST[ 'ywcdd_carrier_id' ];
                $process_id      = $_POST[ 'ywcdd_process_id' ];
                $timezone_format = 'Y-m-d H:i:s';
                $now             = strtotime( date_i18n( $timezone_format ) );


                $shipping_day = $this->get_first_available_day_for_shipping( $now, $process_id );

                $delivery_time = $this->get_first_carrier_delivery_day( $shipping_day, $carrier_id );

                $all_select_days = $this->get_available_date_range( $delivery_time, $carrier_id );

                $timeslot_result = array();
                $text            = '';

                if ( count( $all_select_days ) > 0 ) {
                    $format_min_date = ywcdd_get_date_by_format( min( $all_select_days ) );

                    $text            = sprintf( '%s <strong>%s</strong> <a href="" class="ywcdd_edit_date">%s</a>', __( 'Your order will be delivered on', 'yith-woocommerce-delivery-date' ), $format_min_date, __( 'Edit date', 'yith-woocommerce-delivery-date' ) );
                    $timeslot_result = $this->update_timeslot( $carrier_id, $all_select_days[ 0 ], $process_id );


                }

                wp_send_json( array_merge( array( 'available_days' => $all_select_days, 'message' => $text ), $timeslot_result ) );

            }
        }

        /**
         * @author YITHEMES
         * get all available timeslot for specific date and carrier
         */
        public function update_timeslot( $carrier_id, $date_selected, $proc_method ) {
            $available_timeslot = $this->get_available_timeslot( $carrier_id, $date_selected );
            $json_slot          = $this->format_timeslot( $available_timeslot );
            $sh_time            = $this->get_shipping_day( $carrier_id, $proc_method, $date_selected );

            $shipping_day = date( 'Y-m-d', $sh_time );


            return array( 'available_timeslot' => $json_slot, 'shipping_date' => $shipping_day );
        }


        public function update_timeslot_ajax() {

            if ( isset( $_POST[ 'ywcdd_carrier_id' ] ) ) {

                $carrier_id    = $_POST[ 'ywcdd_carrier_id' ];
                $date_selected = $_POST[ 'ywcdd_date_selected' ];
                $proc_method   = $_POST[ 'ywcdd_process_method' ];

                $results = $this->update_timeslot( $carrier_id, $date_selected, $proc_method );

                wp_send_json( $results );
            }
        }

        /**
         * @param int $carrier_id
         *
         * @return array
         */
        public function get_all_timeslots( $carrier_id = -1 ) {
            if ( -1 == $carrier_id ) {
                $all_slots = get_option( 'yith_delivery_date_time_slot' );
            } else {
                $all_slots = YITH_Delivery_Date_Carrier()->get_timeslot( $carrier_id );
            }

            return apply_filters( 'ywcdd_get_all_timeslots', $all_slots, $carrier_id );
        }


        /**
         * @param int $carrier_id
         *
         * @return bool
         */
        public function is_timeslot_exist( $carrier_id = -1 ) {
            $all_slots = $this->get_all_timeslots( $carrier_id );

            return ( !empty( $all_slots ) && count( $all_slots ) ) > 0;
        }

        /**
         * get all available time slot
         *
         * @author YITHEMES
         * @since 1.0.0
         *
         * @param int $carrier_id
         * @param     $date_selected
         *
         * @return bool|mixed|void
         */
        public function get_available_timeslot( $carrier_id = -1, $date_selected ) {

            $all_slots = $this->get_all_timeslots( $carrier_id );
            if ( $carrier_id == -1 ) {
                $delivery_worksday = get_option( 'yith_delivery_date_workday' );
                $carrier_name      = 'no_car';
            } else {
                $delivery_worksday = get_post_meta( $carrier_id, '_ywcdd_workday', true );
                $carrier_name      = strtolower( get_the_title( $carrier_id ) );
            }

            $available_timeslot = $all_slots;

            if ( $all_slots ) {

                $date_time    = strtotime( $date_selected );
                $day_selected = strtolower( date( "D", $date_time ) );


                $res             = $this->count_timeslot_order_used( ywcdd_get_date_mysql( $date_selected ), $carrier_name );
                $timezone_format = 'h:i:s a';
                $time_now        = date_i18n( $timezone_format );

                $cut_off_time = apply_filters( 'ywcdd_cut_off_time', 0 );
                $time_now     = strtotime( $time_now ) + $cut_off_time;
                foreach ( $all_slots as $slot_id => $slot ) {

                    if ( 'yes' === $slot[ 'override_days' ] ) {
                        if ( !in_array( $day_selected, $slot[ 'day_selected' ] ) ) {
                            unset( $available_timeslot[ $slot_id ] );
                        }
                    } else if ( !in_array( $day_selected, $delivery_worksday ) ) {

                        unset( $available_timeslot[ $slot_id ] );
                    }

                    $time_from = strtotime( $slot[ 'timefrom' ], $date_time );
                    $time_to   = strtotime( $slot[ 'timeto' ], $date_time );


                    if ( $time_to < $time_now ) {

                        unset( $available_timeslot[ $slot_id ] );
                    }

                    if ( ( $slot[ 'max_order' ] != '' && $slot[ 'max_order' ] > 0 ) ) {


                        $key = $slot[ 'timefrom' ] . '-' . $slot[ 'timeto' ];

                        $key     = strtolower( $key );
                        $max_ord = $slot[ 'max_order' ];

                        if ( isset( $res[ $carrier_name ][ $key ] ) && ( $max_ord - $res[ $carrier_name ][ $key ] <= 0 ) ) {

                            unset( $available_timeslot[ $slot_id ] );
                        }


                    }
                }
            }

            @uasort( $available_timeslot, array( $this, 'sort_time_slots' ) );

            return $available_timeslot;
        }


        /**
         * @param $a
         * @param $b
         *
         * @return int
         */
        public function sort_time_slots( $a, $b ) {

            $time_from_a = strtotime( $a[ 'timefrom' ] );
            $time_from_b = strtotime( $b[ 'timefrom' ] );

            if ( $time_from_a < $time_from_b ) {
                return -1;
            } else if ( $time_from_a > $time_from_b ) {
                return 1;
            } else {
                return 0;
            }
        }

        /**
         * count time slot used by carrier,delivery date
         *
         * @author YITHEMES
         * @since 1.0.0
         *
         * @param        $date_selected
         * @param string $carrier_name
         *
         * @return array
         */
        public function count_timeslot_order_used( $date_selected, $carrier_name = 'no_car' ) {

            global $wpdb;

            $order_status  = apply_filters( 'ywcdd_order_status', array( 'wc-pending', 'wc-processing', 'wc-on-hold', 'wc-completed' ) );
            $date_selected = date( 'Y-m-d', strtotime( $date_selected ) );
            $query         = $wpdb->prepare( "SELECT ord.ID FROM {$wpdb->posts} ord INNER JOIN {$wpdb->postmeta}  pm ON ord.ID = pm.post_id 
                                      WHERE ord.post_type='%s' AND ord.post_status IN ('" . implode( "','", $order_status ) . "') AND pm.meta_key='%s' AND pm.meta_value='%s'", 'shop_order', 'ywcdd_order_delivery_date', $date_selected );

            $order_ids = $wpdb->get_col( $query );
            $results   = array();


            foreach ( $order_ids as $order_id ) {

                $timefrom = strtolower( get_post_meta( $order_id, 'ywcdd_order_slot_from', true ) );
                $timeto   = strtolower( get_post_meta( $order_id, 'ywcdd_order_slot_to', true ) );
                $carrier  = get_post_meta( $order_id, 'ywcdd_order_carrier', true );
                $carrier  = empty( $carrier ) ? 'no_car' : strtolower( $carrier );
                $skip     = ( $carrier_name != $carrier ) || $carrier_name == $carrier && ( $timefrom === '' || $timeto === '' );


                if ( !$skip ) {

                    if ( !is_numeric( $timefrom ) && !is_numeric( $timeto ) ) {
                        $key = $timefrom . '-' . $timeto;
                    } else {
                        $key = date_i18n( 'H:i', $timefrom ) . '-' . date_i18n( 'H:i', $timeto );
                    }
                    if ( !isset( $results[ $carrier_name ][ $key ] ) ) {
                        $results[ $carrier_name ][ $key ] = 1;
                    } else {
                        $results[ $carrier_name ][ $key ] = $results[ $carrier_name ][ $key ] + 1;
                    }
                }
            }


            return $results;
        }

        /**
         * @param array $available_timeslot
         */
        public function format_timeslot( $available_timeslot ) {

            $format_slot = array();

            if ( !empty( $available_timeslot ) ) {
                foreach ( $available_timeslot as $slot_id => $slot ) {

                    $timefrom       = ywcdd_display_timeslot( strtotime( $slot[ 'timefrom' ] ) );
                    $timeto         = ywcdd_display_timeslot( strtotime( $slot[ 'timeto' ] ) );
                    $fee            = $slot[ 'fee' ];
                    $timeslotformat = sprintf( '%s: %s - %s: %s', _x( 'From', 'form time', 'yith-woocommerce-delivery-date' ), $timefrom, _x( 'To', 'to time', 'yith-woocommerce-delivery-date' ), $timeto );

                    if ( $fee != '' ) {

                        $timeslotformat .= sprintf( ' ( %s %s )', __( 'Fee', 'yith-woocommerce-delivery-date' ), wc_price( $fee ) );
                    }

                    $format_slot[ $slot_id ] = $timeslotformat;
                }
            }

            return $format_slot;
        }

        /**
         * validate checkout with delivery information
         *
         * @author YITHEMES
         * @since 1.0.0
         */
        public function validate_checkout_width_delivery_date() {

            if ( isset( $_POST[ 'ywcdd_carrier' ] ) && isset( $_POST[ 'ywcdd_datepicker' ] ) ) {

                $carrier_id         = $_POST[ 'ywcdd_carrier' ];
                $date_selected      = $_POST[ 'ywcdd_datepicker' ];
                $is_mandatory       = $_POST[ 'ywcdd_is_mandatory' ];
                $time_slot_av       = $_POST[ 'ywcdd_timeslot_av' ];
                $time_slot_selected = $_POST[ 'ywcdd_timeslot' ];

                if ( 'yes' == $is_mandatory ) {

                    if ( -1 != $carrier_id && '' === $carrier_id ) {

                        $error = sprintf( '<strong>%s</strong> %s', __( 'Carrier', 'yith-woocommerce-delivery-date' ), __( 'is a required field.', 'yith-woocommerce-delivery-date' ) );
                        wc_add_notice( $error, 'error' );
                    }
                    if ( is_numeric( $carrier_id ) && '' === $date_selected ) {
                        $error = sprintf( '<strong>%s</strong> %s', __( 'Delivery Date', 'yith-woocommerce-delivery-date' ), __( 'is a required field.', 'yith-woocommerce-delivery-date' ) );
                        wc_add_notice( $error, 'error' );

                    }

                    if ( 'yes' == $time_slot_av && $time_slot_selected == '' ) {
                        $error = sprintf( '<strong>%s</strong> %s', __( 'Time Slot', 'yith-woocommerce-delivery-date' ), __( 'is a required field.', 'yith-woocommerce-delivery-date' ) );
                        wc_add_notice( $error, 'error' );
                    }
                }
            }
        }

        /**
         * @param $order_id
         */
        public function add_delivery_date_info_order_meta( $order ) {


            if ( isset( $_POST[ 'ywcdd_datepicker' ] ) ) {

                if ( !$order instanceof WC_Order ) {

                    $order = wc_get_order( $order );
                }

                $carrier_id         = isset( $_POST[ 'ywcdd_carrier' ] ) ? $_POST[ 'ywcdd_carrier' ] : -1;
                $delivery_date      = isset( $_POST[ 'ywcdd_datepicker' ] ) ? $_POST[ 'ywcdd_datepicker' ] : '';
                $slot_id            = isset( $_POST[ 'ywcdd_timeslot' ] ) ? $_POST[ 'ywcdd_timeslot' ] : '';
                $last_shipping_date = isset( $_POST[ 'ywcdd_shipping_date' ] ) ? $_POST[ 'ywcdd_shipping_date' ] : '';
                $proc_method        = isset( $_POST[ 'ywcdd_process_method' ] ) ? $_POST[ 'ywcdd_process_method' ] : -1;
                $carrier_name       = '';
                $time_from          = '';
                $time_to            = '';

                $all_slots         = $this->get_all_timeslots($carrier_id);
                $timeslot  = isset( $all_slots[ $slot_id ] ) ? $all_slots[ $slot_id ] : false;

                if ( $carrier_id != -1 ) {
                    $carrier_name = get_the_title( $carrier_id );
                }

                if ( $timeslot ) {

                    $time_from = strtotime( $timeslot[ 'timefrom' ] );
                    $time_to   = strtotime( $timeslot[ 'timeto' ] );

                }


                if ( 'woocommerce_checkout_update_order_meta' == current_filter() ) {

                    yit_save_prop( $order, 'ywcdd_order_delivery_date', $delivery_date );
                    yit_save_prop( $order, 'ywcdd_order_shipping_date', $last_shipping_date );
                    yit_save_prop( $order, 'ywcdd_order_slot_from', $time_from );
                    yit_save_prop( $order, 'ywcdd_order_slot_to', $time_to );
                    yit_save_prop( $order, 'ywcdd_order_carrier', $carrier_name );
                    yit_save_prop( $order, 'ywcdd_order_carrier_id', $carrier_id );
                    yit_save_prop( $order, 'ywcdd_order_processing_method', $proc_method );
                } else {

                    yit_set_prop( $order, 'ywcdd_order_delivery_date', $delivery_date );
                    yit_set_prop( $order, 'ywcdd_order_shipping_date', $last_shipping_date );
                    yit_set_prop( $order, 'ywcdd_order_slot_from', $time_from );
                    yit_set_prop( $order, 'ywcdd_order_slot_to', $time_to );
                    yit_set_prop( $order, 'ywcdd_order_carrier', $carrier_name );
                    yit_set_prop( $order, 'ywcdd_order_carrier_id', $carrier_id );
                    yit_set_prop( $order, 'ywcdd_order_processing_method', $proc_method );

                }
            }
        }

        /**
         * @param WC_Order $order
         */
        public function show_delivery_order_details( $order, $show_shipping = false ) {


            $carrier_label = yit_get_prop( $order, 'ywcdd_order_carrier' );
            $shipping_date = yit_get_prop( $order, 'ywcdd_order_shipping_date' );
            $delivery_date = yit_get_prop( $order, 'ywcdd_order_delivery_date' );
            $time_from     = yit_get_prop( $order, 'ywcdd_order_slot_from' );
            $time_to       = yit_get_prop( $order, 'ywcdd_order_slot_to' );
            $date_format   = get_option( 'date_format' );

            if ( !empty( $delivery_date ) ) {

                echo sprintf( '<h2>%s</h2>', __( 'Delivery Details', 'yith-woocommerce-delivery-date' ) );

                $fields = array(
                    'carrier'       => array(
                        'label' => apply_filters( 'ywcdd_change_carrier_label', __( 'Carrier', 'yith-woocommerce-delivery-date' ) ),
                        'value' => $carrier_label
                    ),
                    'shipping_date' => array(
                        'label' => __( 'Shipping Date', 'yith-woocommerce-delivery-date' ),
                        'value' => sprintf( '%s %s', __( 'within', 'yith-woocommerce-delivery-date' ), ywcdd_get_date_by_format( $shipping_date, $date_format ) ),
                    ),
                    'delivery_date' => array(
                        'label' => __( 'Delivery Date', 'yith-woocommerce-delivery-date' ),
                        'value' => ywcdd_get_date_by_format( $delivery_date, $date_format ),
                    ),
                    'timeslot'      => array(
                        'label' => __( 'Time Slot', 'yith-woocommerce-delivery-date' ),
                        'value' => ( empty( $time_from ) || empty( $time_to ) ) ? '' : sprintf( '%s - %s', ywcdd_display_timeslot( $time_from ), ywcdd_display_timeslot( $time_to ) )
                    )
                );

                echo '<ul class="order_details bacs_details">' . PHP_EOL;
                foreach ( $fields as $field_key => $field ) {
                    if ( !empty( $field[ 'value' ] ) ) {

                        if ( $field_key != 'shipping_date' ) {
                            echo '<li class="' . esc_attr( $field_key ) . '">' . esc_attr( $field[ 'label' ] ) . ': <strong>' . wptexturize( $field[ 'value' ] ) . '</strong></li>' . PHP_EOL;
                        } elseif ( $field_key == 'shipping_date' && $show_shipping ) {
                            echo '<li class="' . esc_attr( $field_key ) . '">' . esc_attr( $field[ 'label' ] ) . ': <strong>' . wptexturize( $field[ 'value' ] ) . '</strong></li>' . PHP_EOL;
                        }
                    }
                }
                echo '</ul>' . PHP_EOL;
            }

        }

        /**
         * add delivery details in review order
         *
         * @author YITHEMES
         * @since 1.0.0
         *
         * @param WC_Order $order
         */
        public function show_delivery_order_details_after_order_table( $order ) {


            $this->show_delivery_order_details( $order );
        }

        /**
         * add delivery details in woocommerce email
         *
         * @author YITHEMES
         * @since 1.0.0
         *
         * @param $order
         * @param $sent_to_admin
         * @param $plain_text
         * @param $email
         */
        public function print_delivery_date_into_email( $order, $sent_to_admin, $plain_text, $email ) {


            $this->show_delivery_order_details( $order, $sent_to_admin );
        }

        /**
         * @param $post_data
         */
        public function set_timeslot_session( $post_data ) {

            $args = wp_parse_args( $post_data );

            WC()->session->__unset( 'ywcdd_fee' );
            if ( isset( $args[ 'ywcdd_timeslot_av' ] ) && 'yes' === $args[ 'ywcdd_timeslot_av' ] ) {

                $timeslot_id = isset( $args[ 'ywcdd_timeslot' ] ) ? $args[ 'ywcdd_timeslot' ] : '';
                $carrier_id  = isset( $args[ 'ywcdd_carrier' ] ) ? $args[ 'ywcdd_carrier' ] : -1;

                if ( !empty( $timeslot_id ) ) {
                    $all_slots = $this->get_all_timeslots($carrier_id);
                    $selected_slot = ( isset( $all_slots[ $timeslot_id ] ) ) ? $all_slots[ $timeslot_id ] : false;

                    if ( $selected_slot ) {

                        $fee = wc_format_decimal( $selected_slot[ 'fee' ] );
                    } else {
                        $fee = 0;
                    }

                    if ( $fee > 0 ) {
                        WC()->session->set( 'ywcdd_fee', $fee );
                    }
                }
            }
        }

        /**
         * add timeslot fee
         *
         * @author YITHEMES
         * @since 1.0.0
         */
        public function add_timeslot_fee() {

            if ( WC()->session->get( 'ywcdd_fee' ) ) {

                WC()->cart->add_fee( __( 'Time Slot Fee', 'yith-woocommerce-delivery-date' ), WC()->session->get( 'ywcdd_fee' ) );
            }
        }

        /**
         * add/remove even to calendar
         *
         * @author YITHEMES
         * @since 1.0.0
         *
         * @param $order_id
         * @param $old_status
         * @param $new_status
         */
        public function manage_order_event( $order_id, $old_status, $new_status ) {

            $order         = wc_get_order( $order_id );
            $delivery_date = yit_get_prop( $order, 'ywcdd_order_delivery_date' );
            $shipping_date = yit_get_prop( $order, 'ywcdd_order_shipping_date' );
            $carrier_id    = yit_get_prop( $order, 'ywcdd_order_carrier_id' );
            $proc_id       = yit_get_prop( $order, 'ywcdd_order_processing_method' );
            $has_child     = apply_filters( 'yith_delivery_date_order_has_child', false, $order_id );

            if ( !empty( $delivery_date ) && !$has_child ) {

                $add_event_order_status = get_option( 'ywcdd_add_event_into_calendar' );

                if ( in_array( $new_status, $add_event_order_status ) ) {
                    /**add new shipping event and add new delivery event into calendar*/
                    YITH_Delivery_Date_Calendar()->add_calendar_event( $proc_id, '', 'shipping_to_carrier', $shipping_date, '', $order_id );

                    YITH_Delivery_Date_Calendar()->add_calendar_event( $carrier_id, '', 'delivery_day', $delivery_date, $delivery_date, $order_id );
                } else {

                    YITH_Delivery_Date_Calendar()->delete_event_by_order_id( $order_id );

                }
            }
        }


    }
}
if ( !function_exists( 'YITH_Delivery_Date_Shipping_Manager' ) ) {

    function YITH_Delivery_Date_Shipping_Manager() {
        return YITH_Delivery_Date_Shipping_Manager::get_instance();
    }
}

YITH_Delivery_Date_Shipping_Manager();