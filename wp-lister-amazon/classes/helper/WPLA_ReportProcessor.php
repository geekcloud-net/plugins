<?php

class WPLA_ReportProcessor {


    // process  FBA Amazon Fulfilled Shipments Report
    // - not called via ajax right now
    public static function processAmazonShipmentsReportPage( $report, $rows, $job, $task ) {
        $wc_orders_processed = 0;

        // process rows
        foreach ($rows as $row) {

            // check for MCF order ID
            $order_id               = str_replace( '#','', $row['merchant-order-id'] );
            $order_item_id          = $row['merchant-order-item-id'];
            $is_mcf_order           = true;
            // if ( empty( $order_id ) ) continue;
            // if ( empty( $order_item_id ) ) continue;

            // no merchant-order-id means this order was placed on Amazon - find WooCommerce order by reference
            if ( empty( $order_id ) ) {

                $amazon_order_id = $row['amazon-order-id'];
                $is_mcf_order    = false;

                $om    = new WPLA_OrdersModel();
                $order = $om->getOrderByOrderID( $amazon_order_id );
                if ( $order ) $order_id = $order->post_id;
            };
            if ( empty( $order_id ) ) continue;

            // get WooCommerce order
            $_order = wc_get_order( $order_id );

            // try Sequential Order Pro
            if ( ! $_order && function_exists('wc_seq_order_number_pro') ) {
                $order_id = wc_seq_order_number_pro()->find_order_by_order_number( $order_id );
                if ( !empty( $order_id ) ) $_order = wc_get_order( $order_id );
            }

            if ( ! $_order ) continue;

            // echo "<pre>";print_r($_order);echo"</pre>";#die();
            // echo "<pre>";print_r($row);echo"</pre>";die();

            $shipment_date          = $row['shipment-date'];
            $estimated_arrival_date = $row['estimated-arrival-date'];
            $ship_service_level     = $row['ship-service-level'];
            $tracking_number        = $row['tracking-number'];
            $carrier                = $row['carrier'];

            // update order meta fields
            update_post_meta( $order_id, '_wpla_fba_submission_status',      'shipped' );
            update_post_meta( $order_id, '_wpla_fba_shipment_date',          $shipment_date );
            update_post_meta( $order_id, '_wpla_fba_estimated_arrival_date', $estimated_arrival_date );
            update_post_meta( $order_id, '_wpla_fba_ship_service_level',     $ship_service_level );
            update_post_meta( $order_id, '_wpla_fba_tracking_number',        $tracking_number );
            update_post_meta( $order_id, '_wpla_fba_ship_carrier',           $carrier );

            // update meta fields for WooCommerce Shipment Tracking plugin
            update_post_meta( $order_id, '_date_shipped',                    strtotime( $shipment_date ) ); // shipment-date column contains TZ offset
            update_post_meta( $order_id, '_tracking_number',                 $tracking_number );
            update_post_meta( $order_id, '_custom_tracking_provider',        $carrier );
            update_post_meta( $order_id, '_tracking_provider',               '' ); // known providers - would require mapping ('usps' <=> 'USPS')

            $wc_orders_processed++;

            // skip further processing for non-MCF orders - no need to to update orders placed on Amazon
            if ( ! $is_mcf_order ) continue;

            // notify WPLE - mark order as shipped on eBay
            $args = array();
            $args['TrackingNumber']  = $tracking_number;
            $args['TrackingCarrier'] = $carrier;
            $args['ShippedTime']     = $shipment_date;
            // $args['FeedbackText']    = 'Thank You...';

            do_action( 'wple_complete_sale_on_ebay', $order_id, $args );

            // complete order - after WPLE has submitted tracking details to eBay!
            $_order->update_status( 'completed' );

        }

        // build response
        $response = new stdClass();
        $response->job      = $job;
        $response->task     = $task;
        $response->errors   = '';
        $response->success  = true;
        $response->count    = $wc_orders_processed;

        return $response;
    } // processAmazonShipmentsReportPage()


    // convert raw CSV data to PHP array
    public static function csv_to_array( $input, $query = false, $delimiter = "\t" ) {

        $header  = null;
        $data    = array();
        $csvData = str_getcsv( $input, "\n", '' );
        // $line = 0;

        // echo "<pre>";print_r($csvData);echo"</pre>";die();

        foreach( $csvData as $csvLine ) {

            if ( $csvLine == null ) continue; // skip empty lines

            if ( is_null($header) ) {
                $header = explode($delimiter, $csvLine);    
            } else {

                // handle query string
                if ( $query && false === stripos( $csvLine, $query ) ) continue;


                // split row into cells
                $items = explode($delimiter, $csvLine);

                // $line++;
                // echo "line $line <br>";

                for ( $n = 0, $m = count($header); $n < $m; $n++ ){
                    $prepareData[$header[$n]] = isset( $items[$n] ) ? $items[$n] : '';
                }

                $data[] = $prepareData;
            }
        }

        return $data;
    } // csv_to_array()
	

} // class WPLA_ReportProcessor
