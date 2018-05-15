<?php

defined( 'ABSPATH' ) or exit;

/**
 * @class      YITH_COG_Orders
 * @package    Yithemes
 * @since      Version 1.0.0
 * @author     Francisco Mendoza
 *
 */

if ( ! class_exists( 'YITH_COG_Orders' ) ) {
    /**
     * Class YITH_COG_Orders
     */
    class YITH_COG_Orders {

        protected static $_instance = null;

        /**
         * Construct
         *
         * @since 1.0
         */
        public function __construct(){

            // set the order meta when an order is placed from standard checkout
            add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'set_order_cost_meta' ), 10, 1 );

            // add negative cost of good item meta for refunds
            add_action( 'woocommerce_refund_created', array( $this, 'add_refund_order_costs' ) );

            //Show the CoG in the order
            add_action( 'woocommerce_admin_order_item_headers', array( $this, 'yith_cog_order_item_header') );
            add_action( 'woocommerce_admin_order_item_values', array( $this, 'yith_cog_order_item_value'), 10, 3 );

        }

        /**
         * Main plugin Instance
         * @return stdClass
         * @var YITH_COG_Orders instance
         * @author
         */
        public static function get_instance()
        {
            $self = __CLASS__ . (class_exists(__CLASS__ . '_Premium') ? '_Premium' : '');

            if (is_null($self::$_instance)) {
                $self::$_instance = new $self;
            }
            return $self::$_instance;
        }


        /** Set necesary values when an order has been placed ************************/

        /**
         * Set the cost of goods item meta for a order.
         */
        public function set_order_cost_meta( $order_id ) {

            $order = wc_get_order( $order_id );
            $total_cost = 0;

            foreach ( $order->get_items() as $item_id => $item ) {
                $product_id = ( ! empty( $item['variation_id'] ) ) ? $item['variation_id'] : $item['product_id'];
                $product = wc_get_product($product_id);

                if ( ! is_object( $product ) ){
                    return;
                }

                if ( $product->is_type( 'gift-card' ) ) {
                    return;
                }


                $product_price = $item['subtotal'] / $item['qty'];
                $item_cost  = YITH_COG_Product::get_cost( $product );
                $quantity   = $item['qty'];
                $name = $item['name'];


                $item_cost = apply_filters( 'wc_cost_of_goods_set_order_item_cost_meta_item_cost', $item_cost, $item, $order );

                $this->set_item_cost_meta( $item_id, $item_cost, $quantity );
                $this->set_item_price_meta ( $item_id, $product_price );
                $this->set_item_taxes( $order_id );
                $this->set_item_name_meta( $item_id, $name );

                $total_cost += ( $item_cost * $quantity );
            }
            $total_cost = apply_filters( 'yith_set_order_cost_meta', $total_cost, $order );

            $formatted_total_cost = wc_format_decimal( $total_cost, wc_get_price_decimals() );

            update_post_meta( $order->get_id(), '_yith_cog_order_total_cost', $formatted_total_cost );
        }


        /**
         * Set an item's taxes.
         */
        protected function set_item_taxes( $order_id )
        {
            $order = wc_get_order( $order_id );

            foreach ( $order->get_items() as $item_id => $item ) {

                $item_tax = $order->get_item_tax( $item );

                wc_update_order_item_meta( $item_id, '_yith_cog_item_tax', $item_tax );

            }
        }


        /**
         * Set an item's cost meta.
         */
        protected function set_item_cost_meta( $item_id, $item_cost = '0', $quantity ) {

            $formatted_cost = wc_format_decimal( $item_cost );
            $formatted_total = wc_format_decimal( $item_cost * $quantity );

            wc_update_order_item_meta( $item_id, '_yith_cog_item_cost', $formatted_cost );
            wc_update_order_item_meta( $item_id, '_yith_cog_item_total_cost', $formatted_total );

            wp_cache_delete( $item_id, 'order_item_meta' );
        }


        /**
         * Set an item's price meta.
         */
        protected function set_item_price_meta ( $item_id, $product_price ){

            wc_update_order_item_meta( $item_id, '_yith_cog_item_price', $product_price );
        }


        /**
         * Set an item's name meta.
         */
        protected function set_item_name_meta ( $item_id, $name ){

            wc_update_order_item_meta( $item_id, '_yith_cog_item_name_sortable', $name );
        }


        /**
         * Add order costs and price to a refund
         */
        public function add_refund_order_costs( $refund_id ) {

            $refund = wc_get_order( $refund_id );

            foreach ( $refund->get_items() as $refund_line_item_id => $refund_line_item ) {

                $refunded_item_cost = 0;
                $refunded_item_total_cost = 0;
                $refunded_item_price = 0;
                $refunded_item_tax = 0;
                $refunded_item_shipping = 0;

                // add as meta to the refund line item
                wc_update_order_item_meta( $refund_line_item_id, '_yith_cog_item_cost',       wc_format_decimal( $refunded_item_cost ) );
                wc_update_order_item_meta( $refund_line_item_id, '_yith_cog_item_total_cost', wc_format_decimal( $refunded_item_total_cost ) );
                wc_update_order_item_meta( $refund_line_item_id, '_yith_cog_item_price',       wc_format_decimal( $refunded_item_price ) );
                wc_update_order_item_meta( $refund_line_item_id, '_yith_cog_item_tax', wc_format_decimal($refunded_item_tax ) );
                wc_update_order_item_meta( $refund_line_item_id, '_yith_cog_item_shipping_tax', wc_format_decimal($refunded_item_shipping ) );
            }
        }


        /** Render the COG in the order ************************/

        /**
         * COG Column in the Order.
         */
        public function yith_cog_order_item_header(){
            ?>
            <th class="item_cog sortable" data-sort="float"><?php _e( 'Cost of Goods', 'yith-cost-of-goods-for-woocommerce' ); ?></th>
            <?php
        }


        /**
         * COG Value in the Order.
         */
        public function yith_cog_order_item_value( $product, $item, $item_id ){

            $cost = wc_get_order_item_meta( $item_id, '_yith_cog_item_cost', true );
            if ($cost > 0 ){
                ?>
                <td class="item_cost" data-sort-value="<?php echo $cost ?>" width="1%">
                    <div class="view">
                        <span><?php echo wc_price( $cost ) ?></span>
                    </div>
                </td>
                <?php
            }
            else{
                ?>
                <td class="item_cost" data-sort-value="-" width="1%">
                    <div class="view">
                        <span> - </span>
                    </div>
                </td>
                <?php
            }
        }

    }
}
