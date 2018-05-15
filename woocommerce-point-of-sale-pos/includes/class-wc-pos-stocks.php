<?php
/**
 * Add extra profile fields for users in admin.
 *
 * @author    Actuality Extensions
 * @package   WoocommercePointOfSale/Classes/profile
 * @category	Class
 * @since     0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WC_Pos_Stocks' ) ) :

/**
 * WC_Pos_Stocks Class
 */
class WC_Pos_Stocks {

	/**
	 * @var WC_Pos_Stocks The single instance of the class
	 * @since 1.9
	 */
	protected static $_instance = null;

	/**
	 * Main WC_Pos_Stocks Instance
	 *
	 * Ensures only one instance of WC_Pos_Stocks is loaded or can be loaded.
	 *
	 * @since 1.9
	 * @static
	 * @return WC_Pos_Stocks Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.9
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'woocommerce' ), '1.9' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.9
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'woocommerce' ), '1.9' );
	}

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		
	}
	public function display_single_stocks_page()
	{
		?>
		<style>
			.filter-items label,
			.filter-items input {
				margin-right: 10px;
			}
			.filter-items{
				padding: 12px 0;
			}
		</style>
		<div class="wrap">
			<h2><?php _e( 'Stock Controller', 'wc_point_of_sale' ); ?></h2>
			<p class="description"><?php _e( 'This page is for users who want to manage their inventory or stock. Simple scan the SKU and the product will load below with the option to enter value and whether this is an increase or decrease in the inventory.', 'wc_point_of_sale' ); ?></p>
			<div id="lost-connection-notice" class="error hidden">

			</div>
			
			<div id="wc_pos_stock_controller">
				<div class="wp-filter">
					<div class="filter-items">
						<form action="" method="post" id="put_wc_pos_barcode">
							<label for="woocommerce_default_country"><?php _e("SKU", 'wc_point_of_sale'); ?></label>
							<input type="text" id="product_barcode" name="product_barcode" value="" minlength="3" >
							<input type="submit" value="<?php _e("Find", 'wc_point_of_sale'); ?>" class="button button-primary button-large" id="find_product_by_barcode">
						</form>
					</div>
				</div>
				<div id="message" style="display: none">
				</div>
				<div id="poststuff_stock" style="display: none">
					<input type="hidden" name="id" id="product_id" value="">
					<table class="wp-list-table widefat striped posts" id="barcode_options_table">
						<thead>
							<tr>
								<th scope="col" id="thumb" class="manage-column column-thumb"><span class="stock_page_image tips" data-tip="<?php _e("Image", 'wc_point_of_sale'); ?>"></span></th>
								<th scope="col" id="name" class="manage-column column-name"><?php _e("Name", 'wc_point_of_sale'); ?></th>
								<th scope="col" id="sku" class="manage-column column-sku"><?php _e("SKU", 'wc_point_of_sale'); ?></th>
								<th scope="col" id="is_in_stock" class="manage-column column-is_in_stock"><?php _e("Stock", 'wc_point_of_sale'); ?></th>
								<th scope="col" id="price" class="manage-column column-price"><?php _e("Price", 'wc_point_of_sale'); ?></th>
								<th scope="col" id="stock_val" class="manage-column column-price"><?php _e("Update Stock", 'wc_point_of_sale'); ?></th>
								<th scope="col" id="increase" class="manage-column column-price"></th>
							</tr>
						</thead>
						<tbody>
							<tr id="" class="iedit author-self level-0 post-99 type-product status-publish has-post-thumbnail hentry product_cat-music product_cat-singles">
								<td class="thumb column-thumb" data-colname="Image">
									<div id="product_image"></div>
								</td>
								<th id="name" class="column-name name" data-colname="Name" scope="col">
									<div id="product_name"></div>
								</th>
								<td class="sku column-sku" data-colname="SKU">
									<div id="product_sku"></div>
								</td>
								<td class="stock column-stock" data-colname="Stock">
									<div id="product_stock"></div>
								</td>
								<td class="price column-price" data-colname="Price">
									<div id="product_price"></div>
								</td>
								<td class="price column-price" data-colname="Stock Value">
									<input type="number" id="stock_value" name="stock_value" min="1">
								</td>
								<td class="price column-price" data-colname="Increase">
									<input type="submit" value="Increase" class="page-title-action" id="increase_stock">
									<input type="submit" value="Decrease" class="page-title-action" id="decrease_stock">
								</td>
							</tr>
						</tbody>
					</table>
				</div>
				<div class="clear"></div>
			</div>
		</div>
		<script>
			jQuery(document).ready(function($){
				$(document).anysearch({
	                searchSlider: false,
	                isBarcode: function(barcode) {
	                    filter_product(barcode);
	                },
	                searchFunc: function(search) {
	                    filter_product(search);
	                },
	            });
				$("#message").fadeOut();
				$("#poststuff_stock").fadeOut();
				$("#put_wc_pos_barcode").on('submit', function(e) {
					if( $('#product_barcode').val() != '' ) {						
						$("#message").fadeOut();
						var barcode = $('#product_barcode').val();
						filter_product(barcode);
					}
					return false;
				});

				$("#increase_stock").on('click', function(e) {
					e.preventDefault();
					var id = $('#product_id').val();
					var operation = 'increase';
					var value = $('#stock_value').val();

					if( value > 0 ) {
						$("#message").fadeOut();
						$('#wc_pos_stock_controller').block({
			                message: null,
			                overlayCSS: {
			                  background: '#fff',
			                  opacity: 0.6
			                }
			              });

						data = {
							action: 'wc_pos_change_stock',
							id: id,
							value: value,
							operation: operation
						};
						$.ajax({
							type: 'post',
							dataType: 'json',
							url: ajaxurl,
							data: data,
							success: function (data, textStatus, XMLHttpRequest) {
								if( data.status == 'success' && data.response ) {
									update_sku_controller_table (data.response);
								}
							},
							error: function (MLHttpRequest, textStatus, errorThrown) {
							},
							complete : function (argument) {
								$('#wc_pos_stock_controller').unblock();
							}
						});
					}
				});
				$("#decrease_stock").on('click', function(e) {
					e.preventDefault();
					var id = $('#product_id').val();
					var operation = 'decrease';
					var value = $('#stock_value').val();

					if( value > 0 ) {
						$("#message").fadeOut();
						$('#wc_pos_stock_controller').block({
			                message: null,
			                overlayCSS: {
			                  background: '#fff',
			                  opacity: 0.6
			                }
			              });

						data = {
							action: 'wc_pos_change_stock',
							id: id,
							value: value,
							operation: operation
						};
						$.ajax({
							type: 'post',
							dataType: 'json',
							url: ajaxurl,
							data: data,
							success: function (data, textStatus, XMLHttpRequest) {
								if( data.status == 'success' && data.response ) {
									update_sku_controller_table (data.response);
								}
							},
							error: function (MLHttpRequest, textStatus, errorThrown) {
							},
							complete : function (argument) {
								$('#wc_pos_stock_controller').unblock();
							}
						});
					}
				});

				function filter_product(barcode)
				{					
					$('#wc_pos_stock_controller').block({
			                message: null,
			                overlayCSS: {
			                  background: '#fff',
			                  opacity: 0.6
			                }
			              });
					data = {
						action: 'wc_pos_filter_product_barcode',
						barcode: barcode
					};
					$.ajax({
						type: 'post',
						dataType: 'json',
						url: ajaxurl,
						data: data,
						success: function (data, textStatus, XMLHttpRequest) {
							if( data.status == 'success' && data.response ) {
								update_sku_controller_table (data.response);							
							} else {
								$("#message").html("Product wasn't found.");
								$("#message").fadeIn();
								$("#poststuff_stock").fadeOut();
							}
						},
						error: function (MLHttpRequest, textStatus, errorThrown) {
						}, 
						complete : function (argument) {
							$('#wc_pos_stock_controller').unblock();
						}
					});
					$('#product_barcode').val('');
				}

				function update_sku_controller_table (product) {
					$("#product_id").val(product.id);
					$("#product_name").html(product.name);
					$("#product_sku").html(product.sku);
					$("#product_image").html(product.image);
					$("#product_price").html(product.price);
					$("#product_stock").html(product.stock_status);
					$('#stock_value').val('');
					$("#poststuff_stock").fadeIn();
				}

			});



		</script>
		<?php
	}

	function display_messages()
	{
		$i = 0;
		if(isset($_GET['message']) && !empty($_GET['message']) ) $i = $_GET['message'];
		$messages = array(
			 0 => '', // Unused. Messages start at index 1.
			 1 => '<div id="message" class="updated"><p>'.  __('Barcode Template created.') . '</p></div>',
			 2 => '<div id="message" class="updated"><p>'. __('Barcode Template updated.') . '</p></div>',
		);
		return $messages[$i];
	}
	public function save_stocks()
	{
	}

}

endif;