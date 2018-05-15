<div id="primary">
    <div id="wcContent" role="main">
        <?php
        if ( isset($_GET['updated']) ) {
            echo '<div class="woocommerce-message">'. $_GET['updated'] .'</div>';
        }

        $order          = wc_get_order( $order_id );
        $order_status   = WC_Warranty_Compatibility::get_order_prop( $order, 'status' );
        $include        = get_option( 'warranty_request_statuses', array() );

        if ( in_array($order_status, $include) && Warranty_Order::order_has_warranty($order) ) {
            if ( empty( $_GET['idx'] ) ) {
                // show products in an order

                if ( version_compare( WC_VERSION, '3.0', '<' ) ) {
                    $completed = get_post_meta( $order->id, '_completed_date', true);
                } else {
                    $completed = $order->get_date_completed() ? $order->get_date_completed()->date( 'Y-m-d H:i:s' ) : false;
                }
                $items      = $order->get_items();

                if ( empty($completed) ) {
                    $completed = false;
                }

                $args = compact( 'woocommerce', 'completed', 'items', 'order_id', 'product_id', 'order', 'order_status', 'include' );

                wc_get_template( 'shortcode-order-items.php', $args, 'warranty', WooCommerce_Warranty::$base_path .'/templates/' );
            } else {
                // Request warranty on selected product
                $items  = $order->get_items();
                $idxs   = $_GET['idx'];

                $args = compact( 'woocommerce', 'order', 'order_id', 'order_status', 'items', 'completed', 'include', 'idxs' );
                wc_get_template( 'shortcode-request-form.php', $args, 'warranty', WooCommerce_Warranty::$base_path .'/templates/' );
            }
        } else {
            echo '<div class="woocommerce-error">'. __('There are no valid warranties for this order', 'wc_warranty') .'</div>';
            echo '<p><a href="'. get_permalink(wc_get_page_id('myaccount')) .'" class="button">'. __('Back to My Account', 'wc_warranty') .'</a></p>';
        }

        ?>
    </div>
</div>
