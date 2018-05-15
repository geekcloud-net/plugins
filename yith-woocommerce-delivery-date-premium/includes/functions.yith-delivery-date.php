<?php
if(!defined('ABSPATH')){
    exit;
}

if( !function_exists('yith_get_worksday')){
    /**
     * return workday
     * @author YITHEMES
     * @since 1.0.0
     * @return array
     */
    function yith_get_worksday( $localized = true ){
        if($localized) {
            $days = array(
                'sun' => __( 'Sunday', 'yith-woocommerce-delivery-date' ),
                'mon' => __( 'Monday', 'yith-woocommerce-delivery-date' ),
                'tue' => __( 'Tuesday', 'yith-woocommerce-delivery-date' ),
                'wed' => __( 'Wednesday', 'yith-woocommerce-delivery-date' ),
                'thu' => __( 'Thursday', 'yith-woocommerce-delivery-date' ),
                'fri' => __( 'Friday', 'yith-woocommerce-delivery-date' ),
                'sat' => __( 'Saturday', 'yith-woocommerce-delivery-date' )
            );
        }else{
            $days = array(
                'sun'   =>  'Sunday',
                'mon'   =>  'Monday',
                'tue'   =>  'Tuesday',
                'wed'   =>  'Wednesday',
                'thu'   =>  'Thursday',
                'fri'   =>  'Friday',
                'sat'   =>  'Saturday',
            );
        }
        
        return $days;
    }
}

if( !function_exists('yith_get_month')){
	/**
	 * 
	 * @param string $abbr
	 * @return string|bool
	 */
	function yith_get_month( $abbr ){
		
		$abbr = strtolower( $abbr );
		$months = array(
				'jan' => _x('January','month','yith-woocommerce-delivery-date'),
				'feb' => _x('February','month', 'yith-woocommerce-delivery-date'),
				'mar' => _x('March','month','yith-woocommerce-delivery-date'),
				'apr' => _x('April','month','yith-woocommerce-delivery-date'),
				'may' => _x('May', 'month','yith-woocommerce-delivery-date'),
				'jun'	=> _x('June', 'month','yith-woocommerce-delivery-date'),
				'jul' =>	_x('July', 'month','yith-woocommerce-delivery-date'),
				'aug' => _x('August ', 'month','yith-woocommerce-delivery-date'),
				'sep' => _x('September ', 'month','yith-woocommerce-delivery-date'),
				'sept' => _x('September ', 'month','yith-woocommerce-delivery-date'),
				'oct' => _x('October ', 'month','yith-woocommerce-delivery-date'),
				'nov' => _x('November ', 'month','yith-woocommerce-delivery-date'),
				'dec' => _x('December ', 'month','yith-woocommerce-delivery-date'),
		);
		
		return isset( $months[$abbr] ) ? $months[$abbr] : false;
	}
}
if( !function_exists('ywcdd_search_product_category' ) ) {
    
    function ywcdd_search_product_category(){
        global $wpdb;
        check_ajax_referer( 'search-products', 'security' );

        if ( ! current_user_can( 'edit_shop_orders' ) ) {
            die(-1);
        }

        $term = wc_clean( stripslashes( $_GET['term'] ) );

        $term = "%" . $term . "%";

        $query_cat = $wpdb->prepare( "SELECT {$wpdb->terms}.term_id,{$wpdb->terms}.name, {$wpdb->terms}.slug
                                   FROM {$wpdb->terms} INNER JOIN {$wpdb->term_taxonomy} ON {$wpdb->terms}.term_id = {$wpdb->term_taxonomy}.term_id
                                   WHERE {$wpdb->term_taxonomy}.taxonomy IN (%s) AND {$wpdb->terms}.name LIKE %s", 'product_cat', $term );

        $product_categories = $wpdb->get_results( $query_cat );

        $to_json = array();

        foreach ( $product_categories as $product_category ) {

            $to_json[ $product_category->term_id ] = "#" . $product_category->term_id . "-" . $product_category->name;
        }

        wp_send_json( $to_json );
    }
}

if( !function_exists('ywcdd_get_date_by_format')){

    function ywcdd_get_date_by_format( $date, $format='' ){

        $format = empty( $format ) ? get_option( 'date_format' ) : $format;

            if( is_string( $date ) ) {
                $time = strtotime( $date );
            }
            else{
                $time = $date;
            }
            $new_date = date_i18n( $format, $time );
            return $new_date;
       
        return $date;
    }
}

if( !function_exists('ywcdd_get_date_mysql')){

    function ywcdd_get_date_mysql( $date ){
        
        return mysql2date( __( 'Y/m/d' ), $date, false );
    }
}

add_action('wp_ajax_ywcdd_search_product_category', 'ywcdd_search_product_category' );

if( !function_exists( 'ywcdd_get_delivery_mode' ) ){
	/**
	 * get delivery mode
	 * @author YITHEMES
	 * @since 1.0.5
	 * @return string
	 */
	function ywcdd_get_delivery_mode(){
		$option = get_option( 'ywcdd_delivery_mode', 'no' );
		return $option ;
	}
}

if( !function_exists( 'ywcdd_display_timeslot' ) ){
    
    function ywcdd_display_timeslot( $timeslot ){

      
        if( is_numeric( $timeslot ) ){
            $time_format = apply_filters( 'ywcdd_timeslot_format', get_option( 'time_format' ) );
            $timeslot = date_i18n( $time_format,  $timeslot );
         }
        return $timeslot;
    }
}

if( !function_exists( 'ywcdd_get_delivery_info' ) ){

    /**
     * @param WC_Order $order
     */
    function ywcdd_get_delivery_info( $order ){

        ob_start();
        wc_get_template('woocommerce/pdf/delivery-date-info.php', array(  'order' => $order ), YITH_DELIVERY_DATE_TEMPLATE_PATH,YITH_DELIVERY_DATE_TEMPLATE_PATH );
        $delivery_info = ob_get_contents();
        ob_end_clean();
        
        return $delivery_info;
    }
}



add_action( 'yith_ywpi_after_document_notes', 'ywpi_print_delivery_notes', 10 );

function ywpi_print_delivery_notes( $document ){

    $print_notes = get_option( 'ywpi_show_delivery_info' );
    
    if( 'yes' == $print_notes ) {
        $notes = ywcdd_get_delivery_info( $document->order );

        echo $notes;
    }
}