<?php
/**
 * WooCommerce POS General Settings
 *
 * @author    Actuality Extensions
 * @package   WoocommercePointOfSale/Classes/settings
 * @category	Class
 * @since     0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_POS_Admin_Settings_Tiles' ) ) :

/**
 * WC_POS_Admin_Settings_Layout
 */
class WC_POS_Admin_Settings_Tiles extends WC_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'tiles_pos';
		$this->label = __( 'Tiles', 'woocommerce' );

		add_filter( 'wc_pos_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'wc_pos_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'wc_pos_settings_save_' . $this->id, array( $this, 'save' ) );

	}

	/**
	 * Get settings array
	 *
	 * @return array
	 */
	public function get_settings() {
		global $woocommerce;

		return apply_filters( 'woocommerce_point_of_sale_general_settings_fields', array(
			
			array( 'title' => __( 'Tile Options', 'woocommerce' ), 'desc' => __( 'The following options affect how the tiles appear on the product grid.', 'woocommerce' ), 'type' => 'title', 'id' => 'tile_options' ),
			
			array(
					'name' => __( 'Quantity Increment', 'wc_point_of_sale' ),
					'id'   => 'woocommerce_pos_register_instant_quantity',
					'std'  => '',
					'type' => 'checkbox',
					'desc' => __( 'Enable quantity increment', 'wc_point_of_sale' ),
					'desc_tip' => __( 'Shows a quantity increment button when adding products to the basket.', 'wc_point_of_sale' ),
					'default'	=> 'no',
					'autoload'  => false					
				),
			array(
					'name' => __( 'Quantity Keypad', 'wc_point_of_sale' ),
					'id'   => 'woocommerce_pos_register_instant_quantity_keypad',
					'std'  => '',
					'type' => 'checkbox',
					'desc' => __( 'Enable quantity keypad', 'wc_point_of_sale' ),
					'desc_tip' => __( 'Shows a quantity increment button and a keypad when adding products to the basket.', 'wc_point_of_sale' ),
					'default'	=> 'no',
					'autoload'  => false					
				),

			array(
				'name' => __( 'Decimal Quantity', 'wc_point_of_sale' ),
				'id'   => 'wc_pos_decimal_quantity',
				'type' => 'checkbox',
				'desc' => __( 'Enable decimal quantity', 'wc_point_of_sale' ),
				'desc_tip' => __( 'Allow to enter decimal quantity from the register and from backend.', 'wc_point_of_sale' ),
				'default'	=> 'no',
				'autoload'  => true					
			),

			array(
				'title'    => __( 'Tile Quantity Value', 'wc_point_of_sale' ),
				'desc_tip' => __( 'This sets the quantity at which tiles are reduced at when Decimal Quantity is set.', 'wc_point_of_sale' ),
				'id'       => 'wc_pos_decimal_quantity_value',
				'class'    => 'wc-enhanced-select',
				'default'  => '0.5',
				'type'     => 'select',
				'options'  => array(
					'0.1'   => '0.1',
					'0.25'  => '0.25',
					'0.5'   => '0.5',
					'1'     => '1',
				)
			),
			
			array(
				'title'    => __( 'Tile Layout ', 'wc_point_of_sale' ),
				'desc_tip' => __( 'This controls the layout of the tile on the product grid.', 'wc_point_of_sale' ),
				'id'       => 'wc_pos_tile_layout',
				'class'    => 'wc-enhanced-select',
				'css'      => 'min-width:300px;',
				'default'  => 'image_title_price',
				'type'     => 'select',
				'options'  => array(
					'image'       => __( 'Product image', 'wc_point_of_sale' ),
					'image_title' => __( 'Product image and title', 'wc_point_of_sale' ),
					'image_title_price' => __( 'Product image, title and price', 'wc_point_of_sale' )
				)
			),



			array(
					'name'  => __( 'Product Image Size', 'wc_point_of_sale' ),
					'id'    => 'wc_pos_display_image_size',
					'css'   => '',
					'desc_tip'  => __( 'These settings affect the display and dimensions of product images in register.', 'wc_point_of_sale' ), 
					'std'   => '',
					'type'  => 'select',
					'class'	=> 'wc-enhanced-select',
					'default' => 'thumbnail',
					'options' => array(
							'thumbnail' => __( 'Thumbnail', 'wc_point_of_sale' ),
							'full_size' => __( 'Full size', 'wc_point_of_sale' ),
						)
				),

			array(
				'title'    => __( 'Variables ', 'wc_point_of_sale' ),
				'desc_tip'     => __( 'Settings to choose how variables can be shown.', 'wc_point_of_sale' ),
				'id'       => 'wc_pos_tile_variables',
				'class'    => 'wc-enhanced-select',
				'css'      => 'min-width:300px;',
				'default'  => 'overlay',
				'type'     => 'select',
				'options'  => array(
					'overlay' => __( 'Overlay', 'wc_point_of_sale' ),
					'tiles' => __( 'Tiles', 'wc_point_of_sale' )
				)
			),
		
			array(
					'title'    => __( 'Default Tile Sorting', 'wc_point_of_sale' ),
					'desc_tip'     => __( 'This controls the default sort order of the tile.', 'wc_point_of_sale' ),
					'id'       => 'wc_pos_default_tile_orderby',
					'class'    => 'wc-enhanced-select',
					'css'      => 'min-width:300px;',
					'default'  => 'menu_order',
					'type'     => 'select',
					'options'  => apply_filters( 'woocommerce_default_catalog_orderby_options', array(
						'menu_order' => __( 'Default sorting (custom ordering + name)', 'woocommerce' ),
						'popularity' => __( 'Popularity (sales)', 'woocommerce' ),
						'rating'     => __( 'Average Rating', 'woocommerce' ),
						'date'       => __( 'Sort by most recent', 'woocommerce' ),
						'price'      => __( 'Sort by price (asc)', 'woocommerce' ),
						'price-desc' => __( 'Sort by price (desc)', 'woocommerce' ),
						'title-asc' => __( 'Name (asc)', 'woocommerce' ),
					) ),
				),

			array( 'type' => 'sectionend', 'id' => 'tile_options'),			
		)); // End general settings

	}

	/**
	 * Save settings
	 */
	public function save() {
		$settings = $this->get_settings();
		WC_POS_Admin_Settings::save_fields( $settings );
	}

}

endif;

return new WC_POS_Admin_Settings_Tiles();