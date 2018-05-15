<?php
if ( ! defined ( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists ( 'YITH_Invoice_Details' ) ) {
	
	/**
	 * Calculate all the details of an invoice
	 *
	 * @class   YITH_Invoice_Details
	 * @package Yithemes
	 * @since   1.0.0
	 * @author  Your Inspiration Themes
	 */
	class YITH_Invoice_Details {
		
		/**
		 * @var YITH_Document the document
		 */
		public $document = null;
		
		/** @var WC_Order */
		private $order = null;
		
		/**
		 * Initialize plugin and registers actions and filters to be used
		 *
		 * @param YITH_Document $document the order to be invoiced
		 *
		 * @since  1.0
		 * @author Lorenzo giuffrida
		 * @access public
		 */
		public function __construct( $document ) {
			$this->document = $document;
			
			if ( $document instanceof YITH_Credit_Note ) {
				$current_order_id = yit_get_prop ( $document->order, 'id' );
				$parent_order_id  = get_post_field ( 'post_parent', $current_order_id );
				
				$this->order = wc_get_order ( $parent_order_id );
			} else {
				$this->order = $document->order;
			}
		}
		
		public function get_item_product( $item ) {
			$_product = apply_filters ( 'woocommerce_order_item_product',
				is_object ( $item ) ? $item->get_product () : $this->order->get_product_from_item ( $item ),
				$item );
			
			return $_product;
		}
		
		/**
		 * Retrieve the path of the product image
		 *
		 * @param mixed $item
		 *
		 * @return mixed
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function get_product_image( $item ) {
			
			$_product = $this->get_item_product ( $item );
			if ( $_product ) {
				
				
				$upload_dir = wp_upload_dir ();
				
				$product_image = $_product->get_image_id () ? current ( wp_get_attachment_image_src ( $_product->get_image_id (),
					'thumbnail' ) ) : wc_placeholder_img_src ();
				$product_image = str_replace ( $upload_dir["baseurl"], $upload_dir["basedir"], $product_image );
			}
			
			return $product_image;
		}
		
		/**
		 * Retrieve the text to be shown when asked for the product SKU
		 *
		 * @param array $item
		 *
		 * @return string
		 *
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function get_sku_text( $item ) {
			$_product = is_object ( $item ) ? $item->get_product () : $this->order->get_product_from_item ( $item );
			
			$result = '';
			if ( is_object ( $_product ) && $_product->get_sku () ) {
				$result = "SKU: " . esc_html ( $_product->get_sku () );
			}
			
			return $result;
		}
		
		function get_meta_field( $meta ) {
			
			if ( version_compare ( WC ()->version, '3.0', '>=' ) && is_object ( $meta ) ) {
				$meta = array(
					'meta_id'    => $meta->id,
					'meta_key'   => $meta->key,
					'meta_value' => $meta->value
				);
			}
			
			return $meta;
		}
		
		/**
		 * Retrieve the text to be shown when asked for the product variation text
		 *
		 * @param int $item_id
		 *
		 * @return string
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function get_variation_text( $item_id, $_product ) {
			$variation_text = '';
			
			if ( ywpi_is_enabled_column_variation ( $this->document ) ) {
				$metadata = version_compare ( WC ()->version, '3.0', '<' ) ? $this->order->has_meta ( $item_id ) : $this->order->get_item ( $item_id )->get_meta_data ();
				
				if ( $metadata ) {
					
					foreach ( $metadata as $meta ) {
						$meta = $this->get_meta_field ( $meta );
						
						$pos = strpos ( $meta['meta_key'], '_' );
						if ( ( $pos !== false ) && ( $pos == 0 ) ) {
							continue;
						}
						
						// Skip serialised meta
						if ( is_serialized ( $meta['meta_value'] ) ) {
							continue;
						}
						
						// Get attribute data
						if ( taxonomy_exists ( wc_sanitize_taxonomy_name ( $meta['meta_key'] ) ) ) {
							$term             = get_term_by ( 'slug', $meta['meta_value'], wc_sanitize_taxonomy_name ( $meta['meta_key'] ) );
							$meta['meta_key'] = wc_attribute_label ( wc_sanitize_taxonomy_name ( $meta['meta_key'] ) );
							
							$meta['meta_value'] = isset( $term->name ) ? $term->name : $meta['meta_value'];
						}
						$variation_text .= apply_filters ( 'yith_ywpi_template_product_variation_string', sprintf ( '%s: %s %s ', $meta['meta_key'], $meta['meta_value'], '<br>' ), $meta, $_product );
					}
				}
			}
			
			return $variation_text;
		}
		
		public function get_order_shipping() {
			$order_shipping = apply_filters ( 'yith_ywpi_get_order_shipping_for_invoice',
				$this->order->get_items ( 'shipping' ),
				$this->order );
			
			return $order_shipping;
		}
		
		public function get_order_fees() {
			$order_fee = apply_filters ( 'yith_ywpi_get_order_fee_for_invoice',
				$this->order->get_items ( 'fee' ),
				$this->order );
			
			return $order_fee;
		}
		
		public function get_order_items() {
			$order_items = apply_filters ( 'yith_ywpi_get_order_items_for_invoice',
				$this->order->get_items (),
				$this->order );
			
			return $order_items;
		}
		
		public function get_products_total_discount() {
			$product_discount = 0.00;
			foreach ( $this->get_order_items () as $item_id => $item ) {
				$diff = apply_filters ( 'yith_ywpi_line_discount', $this->get_item_product_regular_price ( $item ) - $this->get_item_price_per_unit ( $item ), $item );
				if ( $diff > 0.01 ) {
					$product_discount += $item['qty'] * $diff;
				}
			}
			
			return $product_discount;
		}
		
		public function get_item_price_per_unit( $item ) {
			$price = 0.00;
			if ( isset( $item['qty'] ) ) {
				$price = $item["line_subtotal"] / $item['qty'];
			}
			
			return $price;
		}
		
		public function get_item_price_per_unit_sale( $item ) {
			$price = 0.00;
			if ( isset( $item['qty'] ) ) {
				$price = $item["line_total"] / $item['qty'];
			}
			
			return $price;
		}
		
		public function get_item_percentage_discount( $item ) {
			$sale_price    = $this->get_item_price_per_unit_sale ( $item );
			$product_price = $this->get_item_product_regular_price ( $item );
			
			$discount = 0;
			if ( ( $sale_price > 0 ) && ( $product_price > 0 ) ) {
				$discount = 100 - floatval ( $sale_price / $product_price * 100 );
			}
			
			return number_format ( $discount, 2 ) . '%';
		}
		
		public function get_item_product_regular_price( $item ) {
			$product_regular_price = 0.00;
			$_product              = $this->get_item_product ( $item );
			
			/*  Fix for gift cards products that hasn't a regular price */
			if ( $_product instanceof WC_Product_Gift_Card ) {
				$product_regular_price = $this->get_item_price_per_unit ( $item );
			} else if ( yit_get_prop ( $this->order, 'prices_include_tax' ) ) {
				/** @var WC_Product $_product */
				$product_regular_price = yit_get_price_excluding_tax ( $_product );
			} else {
				/** @var WC_Product $_product */
				$product_regular_price = yit_get_prop ( $_product, 'regular_price' );
			}
			
			return $product_regular_price;
		}
		
		public function get_order_subtotal( $incl_order_discount = true ) {
			$order_fee_amount       = 0.00;
			$order_fee_taxes_amount = 0.00;
			
			foreach ( $this->get_order_fees () as $item_id => $item ) {
				$order_fee_amount += $item['line_total'];
				$order_fee_taxes_amount += $item['line_tax'];
			}
			
			$_order_subtotal = 	apply_filters ( 'yith_wcpdi_order_subtotal',
								$this->order->get_subtotal () + $this->order->get_total_shipping (),
								$this->order,
								$this->order->get_total_shipping ()
								);
								

			if ( $incl_order_discount ) {

				$_order_subtotal -= $this->get_order_discount ();

			}


			$_order_subtotal = apply_filters ( 'yith_ywpi_invoice_subtotal',
				$_order_subtotal,
				$this->order,
				$this->get_products_total_discount (),
				$order_fee_amount );

			return $_order_subtotal;
		}
		
		public function get_order_taxes() {
			$_order_taxes = 'yes' == get_option ( 'woocommerce_calc_taxes' ) ? apply_filters ( 'yith_ywpi_invoice_tax_totals', $this->order->get_tax_totals (), $this->order ) : array();
			
			return $_order_taxes;
		}
		
		public function get_order_total() {
			
			$_order_taxes       = $this->get_order_taxes ();
			$_order_taxes_total = 0.00;
			foreach ( $_order_taxes as $code => $tax ) {
				$_order_taxes_total += $tax->amount;
			}
			
			$_order_total = apply_filters ( 'yith_ywpi_invoice_total',
				$this->order->get_total (), yit_get_prop ( $this->order, 'id' ) );
			
			return $_order_total;
		}
		
		public function get_order_discount() {
			$_order_discount = apply_filters ( 'yith_ywpi_invoice_total_discount',
				$this->order->get_total_discount () == 0 ? $this->get_products_total_discount () : $this->order->get_total_discount (),
				$this->order,
				$this->get_products_total_discount () );
			
			return $_order_discount;
		}
		
		public function get_item_shipping_taxes( $item ) {
			$taxes = 0.00;
			
			if ( isset( $item['taxes'] ) ) {
				$taxes_list = maybe_unserialize ( $item['taxes'] );
				
				$taxes_list = isset( $taxes_list['total'] ) ? $taxes_list['total'] : $taxes_list;
				
				if ( $taxes_list ) {
					foreach ( $taxes_list as $tax_id => $amount ) {
						if ( 'total' != $tax_id ) {
							$taxes += $amount;
						}
					}
				}
			}
			
			return $taxes;
		}

        public function get_order_currency( $order, $amount ) {

            $order_currency = $order->get_currency();
            $currency = array('currency' => $order_currency);

            $currency = apply_filters( 'yith_wc_ywpi_order_currency', $currency, $order_currency, $order );
            $amount = apply_filters( 'yith_wc_ywpi_order_amount', $amount, $order );

            return wc_price( $amount, $currency );
        }




	}
}