<?php
/**
 * provide the admin user interface to edit the _msrp_price post meta field
 */

if ( ! class_exists( 'woocommerce_msrp_admin' ) && ! class_exists( 'WPL_MSRP_Addon' ) && ! class_exists( 'WPLE_MSRP_Addon' ) ) {
	class WPLA_MSRP_Addon {

		/**
		 * Add required hooks
		 */
		function __construct() {

			// Add meta box to the product page
			add_action( 'woocommerce_product_options_pricing', array( $this, 'product_meta_field') );
			add_action( 'woocommerce_product_after_variable_attributes', array( $this, 'variation_show_fields'), 10, 3 );
			add_action( 'woocommerce_process_product_meta_variable', array( $this, 'variation_save_fields') );
			add_action( 'woocommerce_ajax_save_product_variations',  array( $this, 'variation_save_fields') ); // WC2.4
			add_action( 'save_post', array( $this, 'save_product' ) );

		}

		/**
		 * Display the meta field for MSRP prices on the product page
		 */
		function product_meta_field() {

			woocommerce_wp_text_input( array( 
				'id'          => '_msrp_price', 
				'class'       => 'wc_input_price short', 
				'label'       => __( 'MSRP Price', 'wpla' ) . ' (' . get_woocommerce_currency_symbol() . ')', 
				'description' => '' 
			) );

		}

		/**
		 * Show the fields for editing the MSRP on the variations panel on the post edit screen
		 * @param  array $variation_data The variation data for this variation
		 * @param  [type] $loop          Unused
		 */
		function variation_show_fields( $loop, $variation_data, $variation ) {

			// get variation post_id - WC2.3
			$variation_post_id = $variation ? $variation->ID : $variation_data['variation_post_id']; // $variation exists since WC2.2 (at least)

			// get current values - WC2.3
			$_msrp       = get_post_meta( $variation_post_id, '_msrp'  		, true );

			?>
			<div>
                <p class="form-row form-row-full">
					<label>
						<?php // echo __( 'MSRP Price', 'wpla' ) . ' (' . get_woocommerce_currency_symbol() . ')'; ?>
						<?php echo __( 'MSRP Price', 'wpla' ) ?>
                        <a class="tips" data-tip="The Maxmimum Suggested Retail Price you can use in your listing profile." href="#">[?]</a>
					</label>
					<input type="text" size="5" name="variable_msrp[<?php echo $loop; ?>]" value="<?php echo $_msrp ?>" />
				</p>
			</div>
			<?php

		}

		/**
		 * Save MSRP values for variable products
		 * @param  int $product_id The parent product ID (Unused)
		 */
		function variation_save_fields( $product_id ) {

			if ( ! isset ( $_POST['variable_post_id'] ) )
				return;

			$max_loop = max( array_keys( $_POST['variable_post_id'] ) );

			for ( $idx = 0; $idx <= $max_loop; $idx++ ) {
				if ( empty ( $_POST['variable_post_id'][$idx] ) )
					continue;
				$variation_id = (int) $_POST['variable_post_id'][$idx];
				update_post_meta( $variation_id, '_msrp', $_POST['variable_msrp'][$idx] );
			}

		}

		/**
		 * Save the product meta information
		 * @param int $product_id The product ID
		 */
		function save_product( $product_id ) {
			// Verify if this is an auto save routine.
			// If it is our form has not been submitted, so we dont want to do anything
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
				return;

			if ( ! isset ( $_POST['_msrp_price'] ) )
				return;

			update_post_meta( $product_id, '_msrp_price', $_POST['_msrp_price'] );
		}

	} // class WPLA_MSRP_Addon()

	$WPLA_MSRP_Addon = new WPLA_MSRP_Addon();

} // if class not already exists
