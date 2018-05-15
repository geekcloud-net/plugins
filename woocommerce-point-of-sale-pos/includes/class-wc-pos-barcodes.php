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

if ( ! class_exists( 'WC_Pos_Barcodes' ) ) :

/**
 * WC_Pos_Barcodes Class
 */
class WC_Pos_Barcodes {

	/**
	 * @var WC_Pos_Barcodes The single instance of the class
	 * @since 1.9
	 */
	protected static $_instance = null;

	/**
	 * Main WC_Pos_Barcodes Instance
	 *
	 * Ensures only one instance of WC_Pos_Barcodes is loaded or can be loaded.
	 *
	 * @since 1.9
	 * @static
	 * @return WC_Pos_Barcodes Main instance
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
	public function display_single_barcode_page()
	{
		?>
		<div class="wrap">
			<h2><?php _e( 'Barcode', 'wc_point_of_sale' ); ?></h2>
			<?php echo $this->display_messages();?>
			<div id="lost-connection-notice" class="error hidden">
				<p><span class="spinner"></span> <?php _e( '<strong>Connection lost.</strong> Saving has been disabled until you&#8217;re reconnected.' ); ?>
				<span class="hide-if-no-sessionstorage"><?php _e( 'We&#8217;re backing up this post in your browser, just in case.' ); ?></span>
				</p>
			</div>
			<form action="" method="post" id="edit_wc_pos_barcode" onsubmit="return false;">
				<?php wp_nonce_field('wc_point_of_sale_edit_barcode'); ?>
				<div id="poststuff">
					<div id="post-body" class="metabox-holder columns-2">
						<div id="postbox-container-2" class="postbox-container">
							<div class="postbox products_list">
								<div class="inside">
									<?php include_once 'views/html-admin-barcode-options.php';?>									
								</div>
							</div>
						</div>
						<div id="postbox-container-1" class="postbox-container">
							<div class="postbox ">
								<h3 class="hndle">
									<label ><?php _e( 'Print Settings', 'wc_point_of_sale' ); ?></label>
								</h3>
								<div class="inside">
									<div>
										<label for="number_of_labels"><?php _e( 'Number of Labels', 'wc_point_of_sale' ); ?></label>
										<input type="number" step="1" name="number_of_labels" id="number_of_labels">
									</div>
									<div>
										<label for="label_type"><?php _e( 'Label Type', 'wc_point_of_sale' ); ?></label>
										<select id="label_type" name="label_type" class="wc-enhanced-select">
										 <option value="continuous_feed"><?php _e( 'Continuous Feed', 'wc_point_of_sale' ); ?></option>
										 <option value="a4"><?php _e( 'A4 (2 x 7)', 'wc_point_of_sale' ); ?></option>
										 <option value="a4_30"><?php _e( 'A4 (3 x 7)', 'wc_point_of_sale' ); ?></option>
										 <option value="letter"><?php _e( 'Letter (4 x 5)', 'wc_point_of_sale' ); ?></option>
										 <option value="per_sheet_30"><?php _e( 'Letter (3 x 10)', 'wc_point_of_sale' ); ?></option>
										 <option value="per_sheet_80"><?php _e( 'Letter (4 x 20)', 'wc_point_of_sale' ); ?></option>
										 <option value="con_4_3"><?php _e( 'Continuous Feed (4cm x 3cm)', 'wc_point_of_sale' ); ?></option>
										 <option value="con_4_2"><?php _e( 'Continuous Feed (40mm x 20mm)', 'wc_point_of_sale' ); ?></option>
										 <option value="jew_50_10"><?php _e( 'Jewellery Tag (50mm x 10mm)', 'wc_point_of_sale' ); ?></option>

										</select>
									</div>
									<div>
										<label for="label_fields"><?php _e( 'Choose which fields to print', 'wc_point_of_sale' ); ?></label>
										<div><label><input type="checkbox" name="fields_print" class="fields_to_print"  id="field_barcode"  checked="checked"><?php _e( 'Barcode', 'wc_point_of_sale' ); ?></label></div>
										<div><label><input type="checkbox" name="fields_print" class="fields_to_print"  id="field_sku" checked="checked"><?php _e( 'SKU', 'wc_point_of_sale' ); ?></label></div>
										<div><label><input type="checkbox" name="fields_print" class="fields_to_print" id="field_price"><?php _e( 'Price', 'wc_point_of_sale' ); ?></label></div>
										<div><label><input type="checkbox" name="fields_print" class="fields_to_print"  id="field_name"><?php _e( 'Product Name', 'wc_point_of_sale' ); ?></label></div>
                                        <div><label><input type="checkbox" name="fields_print" class="fields_to_print"  id="field_meta_value"><?php _e( 'Variable', 'wc_point_of_sale' ); ?></label></div>
                                        <div><label><input type="checkbox" name="fields_print" class="fields_to_print"  id="field_meta_title"><?php _e( 'Variable label', 'wc_point_of_sale' ); ?></label></div>
									</div>
									<div>
										<p class="description" style="margin-top: 1em;"><?php _e( 'Note: be sure to set your paper size to the corresponding paper size and the margins to none to ensure printing is accurate.', 'wc_point_of_sale' ); ?></p>
									</div>
									
								</div>
								<div id="major-publishing-actions">
									<div id="publishing-action">
										<span class="spinner"></span>
										<input type="button" value="Print" class="button button-primary button-large" id="print_barcode">
									</div>
									<div class="clear"></div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="clear"></div>
			</form>
		</div>
		
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
	public function save_barcode()
	{
	}

}

endif;