<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WMNotification Class.
 */
class WMNotification {

	public static function init() {
		add_action( 'woomelly_notification_orders', array( __CLASS__, 'woomelly_notification_orders' ), 10, 2 );
	}

	/**
	 * get_woomelly_last_resource.
	 *
	 * @return string
	 */
	public static function get_woomelly_last_resource () {
		$woomelly_notification = get_option( '_wm_notification_last_resource' );
		return $woomelly_notification;
	} //End get_woomelly_last_resource()

	/**
	 * get_woomelly_last_notification.
	 *
	 * @return string
	 */
	public static function get_woomelly_last_notification () {
		$woomelly_notification = get_option( '_wm_notification_last_notification' );
		return $woomelly_notification;
	} //End get_woomelly_last_notification()

	/**
	 * set_woomelly_last_resource.
	 *
	 * @return string
	 */
	public static function set_woomelly_last_resource ( $value ) {
		$value = trim($value);
		update_option( '_wm_notification_last_resource', $value );
	} //End set_woomelly_last_resource()

	/**
	 * set_woomelly_last_notification.
	 *
	 * @return string
	 */
	public static function set_woomelly_last_notification ( $value ) {
		$value = trim($value);
		update_option( '_wm_notification_last_notification', $value );
	} //End set_woomelly_last_notification()	

	/**
	 * woomelly_notification_orders.
	 *
	 * @return
	 */
	public static function woomelly_notification_orders ( $resource, $received ) {		
		$data_resource = array();
		$woocommerce_status = '';
		$all_products_is_ok = false;
		$list_woo_products = array();

		$data_resource = WMeli::get_resource( $resource );
		if ( is_object($data_resource) ) {
			$wm_settings_page = new WMSettings();
			$date_created = strtotime( $data_resource->date_created );
			if ( $date_created >= $wm_settings_page->get_settings_datetime_order() ) {
				$code = $data_resource->id;
				switch ( $data_resource->status ) {
					case 'confirmed':
						$woocommerce_status = 'on-hold';
						break;
					case 'payment_required':
						$woocommerce_status = 'pending';
						break;
					case 'payment_in_process':
						$woocommerce_status = 'on-hold';
						break;
					case 'partially_paid':
						$woocommerce_status = 'on-hold';
						break;
					case 'paid':
						$woocommerce_status = 'processing';
						break;
					case 'cancelled':
						$woocommerce_status = 'failed';
						break;
					case 'invalid':
						$woocommerce_status = 'failed';
						break;
				}
				if ( !empty($data_resource->order_items) ) {
					foreach ( $data_resource->order_items as $value ) {
						$wm_product = wm_get_product_by_code( $value->item->id );
						if ( $wm_product ) {
							$woo_product = wc_get_product( $wm_product->get_id() );
							if ( is_object($woo_product) && $woo_product->get_status() != "trash" ) {
								$list_woo_products[] = $woo_product;
								$all_products_is_ok = true;
							} else {
								$all_products_is_ok = false;
								break;								
							}
						} else {
							$all_products_is_ok = false;
							break;
						}
						unset( $wm_product );
					}
				}
				if ( $all_products_is_ok && !empty($list_woo_products) ) {
					//$continue_sync_order = false;










					//if ( $continue_sync_order ) {						
						$wm_order = wm_get_order_by_code( $code );
						if ( is_object($wm_order) ) {
							$order = wc_get_order( $wm_order->get_id() );
							if ( is_object($order) && $order->get_status() != "trash" ) {
								$notes = '';
								if ( is_object($data_resource->status_detail) && isset($data_resource->status_detail->description) ) {
									$notes = $data_resource->status_detail->description;
								}
								$status_now = $order->get_status();
								if ( $status_now != "failed" && $status_now != "cancelled" ) {
									if ( is_object($data_resource->feedback) && isset($data_resource->feedback->purchase) && $data_resource->feedback->purchase!="" && $woocommerce_status != 'failed' ) {
										if ( $data_resource->feedback->purchase->fulfilled ) {
											if ( $status_now != 'completed' ) {
												$order->update_status( 'completed' );
											}
										} else if ( $data_resource->status == 'confirmed' ) {
											$order->update_status( 'failed' );
										}
									}
									if ( $status_now != 'completed' || ($status_now=='completed' && $woocommerce_status=='failed') ) {
										if ( $status_now != $woocommerce_status ) {
											$order->update_status( $woocommerce_status, $notes, true );
										}
										
									}
								} else {
									if ( is_object($data_resource->feedback) && isset($data_resource->feedback->purchase) && $data_resource->feedback->purchase!="" && $woocommerce_status != 'failed' ) {
										if ( $data_resource->feedback->purchase->fulfilled ) {
											if ( $status_now != 'completed' ) {
												$order->update_status( 'completed' );
											}
										}
									}
								}
							}
						} else {
							unset( $wm_order );
							$order = new WC_Order( 0 );
							$order->set_status( $woocommerce_status );
							$order->set_customer_note( __('Order from Mercadolibre', 'woomelly') );
							$order->set_currency( $data_resource->currency_id );
							$meli_buyer = array(
								'first_name' => $data_resource->buyer->first_name,
								'last_name'  => $data_resource->buyer->last_name,
								'company'    => '',
								'email'      => 'mercadolibre@noreply.com',
								'phone'      => $data_resource->buyer->phone->area_code.' '.$data_resource->buyer->phone->number,
								'address_1'  => __("Address by default", "woomelly"),
								'address_2'  => '', 
								'city'       => '',
								'state'      => '',
								'postcode'   => '',
								'country'    => ''
							);
							$order->set_address( $meli_buyer, 'billing' );
							$total_amount = floatval($data_resource->total_amount);
							foreach ( $data_resource->order_items as $value ) {
								$wm_product = wm_get_product_by_code( $value->item->id );
								$line_item = new WC_Order_Item_Product();
								$line_item->set_product( wc_get_product( $wm_product->get_id() ) );
								$line_item->set_quantity( intval($value->quantity) );
								$line_item->set_total( floatval($value->unit_price) );
								$line_item->set_subtotal( floatval($value->unit_price) );
								$order->add_item( $line_item );
								unset( $line_item );
								unset( $wm_product );
							}
							if ( is_object($data_resource->shipping) && isset($data_resource->shipping->id) ) {
								$data_shipping = WMeli::get_shipping( $data_resource->shipping->id );
								if ( is_object($data_shipping) ) {
									$shipping_option_name = '';
									$shipping_mode = '';
									$address_1 = '';
									$postcode = '';
									$city = '';
									$state = '';
									$country = '';
									if ( isset($data_shipping->shipping_mode) && $data_shipping->shipping_mode!="" ) {
										$shipping_mode = $data_shipping->shipping_mode;
									} else if ( isset($data_shipping->mode) && $data_shipping->mode!="" ) {
										$shipping_mode = $data_shipping->mode;
									}
									if ( isset($data_shipping->shipping_option->name) && $data_shipping->shipping_option->name!="" && $data_shipping->shipping_option->name!="add_shipping_cost" ) {
										$shipping_option_name = ' | ' . $data_shipping->shipping_option->name;
									}
									if ( isset($data_shipping->receiver_address->address_line) ) {
										$address_1 .= $data_shipping->receiver_address->address_line;    
									}
									if ( isset($data_shipping->receiver_address->street_name) ) {
										if ( $address_1 == '' ) {
											$address_1 .= $data_shipping->receiver_address->street_name;
										} else {
											$address_1 .= ', ' . $data_shipping->receiver_address->street_name;
										}
									}
									if ( isset($data_shipping->receiver_address->street_number) ) {
										if ( $address_1 == '' ) {
											$address_1 .= $data_shipping->receiver_address->street_number;
										} else {
											$address_1 .= ', ' . $data_shipping->receiver_address->street_number;
										}
									}
									if ( isset($data_shipping->receiver_address->comment) ) {
										if ( $address_1 == '' ) {
											$address_1 .= $data_shipping->receiver_address->comment;
										} else {
											$address_1 .= ', '.$data_shipping->receiver_address->comment;
										}
									}
									if ( isset($data_shipping->receiver_address->zip_code) ) {
										$postcode = $data_shipping->receiver_address->zip_code;
									}
									if ( isset($data_shipping->receiver_address->city->name) ) {
										$city = $data_shipping->receiver_address->city->name;
									}		
									if ( isset($data_shipping->receiver_address->state->name) ) {
										$state = $data_shipping->receiver_address->state->name;
									}
									if ( isset($data_shipping->receiver_address->country->id) && isset($data_shipping->receiver_address->country->name) ) {
										if ( isset( WC()->countries->countries[ $data_shipping->receiver_address->country->id ] ) ) {
											$country = WC()->countries->countries[ $data_shipping->receiver_address->country->id ];
										} else {
											$country = $data_shipping->receiver_address->country->id;
										}
									}
									$meli_shipping = array(
										'first_name' => $data_resource->buyer->first_name,
										'last_name'  => $data_resource->buyer->last_name,
										'company'    => '',
										'phone'      => $data_resource->buyer->phone->area_code.' '.$data_resource->buyer->phone->number,
										'address_1'  => $address_1,
										'address_2'  => '',
										'city'       => $city,
										'state'      => $state,
										'postcode'   => $postcode,
										'country'    => $country
									);
									$order->set_address( $meli_shipping, 'shipping' );
									if ( $shipping_mode == 'not_specified' ) {
										$shipping_option_name = __('Not Specified', 'woomelly');
									} else if ( $shipping_mode == 'custom' ) {
										$shipping_option_name = sprintf( __('Custom %s', 'woomelly'), $shipping_option_name );
									} else if ( $shipping_mode == 'me1' ) {
										$shipping_option_name = sprintf( __('ME type 1 %s', 'woomelly'), $shipping_option_name );
									} else if ( $shipping_mode == 'me2' ) {
										$shipping_option_name = sprintf( __('ME type 2 %s', 'woomelly'), $shipping_option_name );
									}
									$item_shipping = new WC_Order_Item_Shipping();
									$item_shipping->set_props( array(
										'method_title' => $shipping_option_name,
										'method_id'    => 0,
										'total'        => floatval( $data_shipping->shipping_option->cost ),
										'taxes'        => 0,
									) );
									$item_shipping->save();
									$order->add_item( $item_shipping );
								}
							}
							$order->calculate_totals( false );
							$order->save();
							$order->set_billing_email( $data_resource->buyer->email );
							$order->save();
							$wm_order = new WMOrder( $order->get_id() );
							$wm_order->set_woomelly_code_meli_field( $code );
							$wm_order->set_woomelly_buyer( $data_resource->buyer->id );
							$wm_order->set_woomelly_billing_info( $data_resource->buyer->billing_info );
							if ( is_object($data_resource->status_detail) && isset($data_resource->status_detail->description) ) {
								$order->add_order_note( $data_resource->status_detail->description );
							}
							if ( is_object($data_resource->shipping) && isset($data_resource->shipping->id) ) {
								if ( is_object($data_shipping) ) {
									if ( isset($data_shipping->id) && $data_shipping->id != "" ) {
										$wm_order->set_woomelly_shipping_id( $data_shipping->id );
									}									
									if ( isset($data_shipping->tracking_method) && $data_shipping->tracking_method != "" ) {
										$wm_order->set_woomelly_shipping_tracking_method( $data_shipping->tracking_method );
									}
									if ( isset($data_shipping->tracking_number) && $data_shipping->tracking_number != "" ) {
										$wm_order->set_woomelly_shipping_tracking_number( $data_shipping->tracking_number );
									}
									if ( isset($data_shipping->comments) && $data_shipping->comments != "" ) {
										$wm_order->set_woomelly_shipping_comments( $data_shipping->comments );
									}
									if ( isset($data_shipping->shipping_mode) && $data_shipping->shipping_mode!="" ) {
										$wm_order->set_woomelly_shipping_mode( $data_shipping->shipping_mode );
									} else if ( isset($data_shipping->mode) && $data_shipping->mode!="" ) {
										$wm_order->set_woomelly_shipping_mode( $data_shipping->mode );
									}
									if ( is_object($data_shipping->shipping_option) && isset($data_shipping->shipping_option->id) ) {
										$wm_order->set_woomelly_shipping_option_id( $data_shipping->shipping_option->id );
									}
								}
							}
							wc_reduce_stock_levels( $order->get_id() );
						}
						
						$settings_extensions = $wm_settings_page->get_settings_extensions();
						if ( $settings_extensions['feedback'] ) {
							if ( is_object($data_resource->feedback) && isset($data_resource->feedback->purchase) && $data_resource->feedback->purchase!="" ) {
								global $wpdb;
								$review_text = '';
								switch ( $data_resource->feedback->purchase->rating ) {
									case 'negative':
										$review_text = __("Bad Review | Review from Mercadolibre", "woomelly");
										break;
									case 'neutral':
										$review_text = __("Neutral Review | Review from Mercadolibre", "woomelly");
										break;
									default:
										$review_text = __("Success Review | Review from Mercadolibre", "woomelly");
										break;
								}
								$review = $wpdb->get_row( "SELECT comment_id FROM ". $wpdb->prefix ."commentmeta WHERE meta_key = 'purchase_id' AND meta_value = '".$data_resource->feedback->purchase->id."'" );
								if ( $review ) {
									if ( $data_resource->feedback->purchase->fulfilled ) {
										update_comment_meta( $review->comment_id, 'fulfilled', true );
									} else {
										update_comment_meta( $review->comment_id, 'fulfilled', false );
									}
									switch ( $data_resource->feedback->purchase->rating ) {
										case 'negative':
											update_comment_meta( $review->comment_id, 'rating', '1' );
											break;
										case 'neutral':
											update_comment_meta( $review->comment_id, 'rating', '3' );
											break;
										default:
											update_comment_meta( $review->comment_id, 'rating', '5' );
											break;
									}
									wp_update_comment( array( 'comment_ID' => $review->comment_id, 'comment_content' => $review_text ) );
								} else {
									if ( !empty($list_woo_products) ) {
										unset( $value );
										foreach ( $list_woo_products as $value ) {										
											$data_review = array(
												'comment_post_ID' => $value->get_id(),
												'comment_author' => $data_resource->buyer->first_name.' '.$data_resource->buyer->last_name,
												'comment_author_email' => $data_resource->buyer->email,
												'comment_author_url' => '',
												'comment_content' => $review_text,
												'comment_type' => 'review',
												'comment_parent' => 0,
												'user_id' => 0,
												'comment_author_IP' => '',
												'comment_agent' => 'Mercadolibre',
												'comment_date' => current_time('mysql'),
												'comment_approved' => 1,
											);
											$product_review_id = wp_insert_comment( $data_review );
											if ( $product_review_id > 0 ) {
												if ( $data_resource->feedback->purchase->fulfilled ) {
													update_comment_meta( $product_review_id, 'fulfilled', true );
												} else {
													update_comment_meta( $product_review_id, 'fulfilled', false );
												}
												switch ( $data_resource->feedback->purchase->rating ) {
													case 'negative':
														update_comment_meta( $product_review_id, 'rating', '1' );
														break;
													case 'neutral':
														update_comment_meta( $product_review_id, 'rating', '3' );
														break;
													default:
														update_comment_meta( $product_review_id, 'rating', '5' );
														break;
												}
												update_comment_meta( $product_review_id, 'purchase_id', $data_resource->feedback->purchase->id );
												foreach ( $list_woo_products as $value ) {
													$average = WC_Comments::get_average_rating_for_product( $value );
													update_post_meta( $value->get_id(), '_wc_average_rating', $average );
													$counts = WC_Comments::get_rating_counts_for_product( $value );
													update_post_meta( $value->get_id(), '_wc_rating_count', $counts );										
												}
											}
										}
									}

								}
							}			
						}

					//}
				}
			}
		}
	} //End woomelly_notification_orders()
}

WMNotification::init();