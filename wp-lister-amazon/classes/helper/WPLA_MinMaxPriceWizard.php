<?php

class WPLA_MinMaxPriceWizard {


	public function __construct() {
		// parent::__construct();
		
		// price wizard
		// add_action('wp_ajax_wpla_select_profile', array( &$this, 'ajax_wpla_select_profile' ) );
		add_action('wp_ajax_wpla_show_minmax_price_wizard', array( &$this, 'ajax_wpla_show_minmax_price_wizard' ) );

	}

	// show price wizard
	public function ajax_wpla_show_minmax_price_wizard() {

		// load template
		$tpldata = array(
			'plugin_url'				=> WPLA_URL,
			'selected_items'			=> $_REQUEST['item_ids'] ? explode( ',', $_REQUEST['item_ids'] ) : array(),
			'form_action'				=> 'admin.php?page=wpla-tools&tab=repricing'
		);

		@WPLA_Page::display( 'tools/price_wizard', $tpldata );
		exit();
	
	} // ajax_wpla_show_minmax_price_wizard()

	static public function updateMinMaxPrices( $item_ids ) {
		// echo "<pre>";print_r($item_ids);echo"</pre>";

		// TODO: sanitize values
		$min_base_price       = trim( $_REQUEST['min_base_price'] );
		$min_price_percentage = trim( $_REQUEST['min_price_percentage'] );
		$min_price_amount     = trim( $_REQUEST['min_price_amount'] );
		$max_base_price       = trim( $_REQUEST['max_base_price'] );
		$max_price_percentage = trim( $_REQUEST['max_price_percentage'] );
		$max_price_amount     = trim( $_REQUEST['max_price_amount'] );

		$min_price_amount     = str_replace( ',', '.', $min_price_amount ); // convert decimal comma
		$max_price_amount     = str_replace( ',', '.', $max_price_amount );

		// remember last used options
	    $options = array(
	        'min_base_price'       => $min_base_price,
	        'max_base_price'       => $max_base_price,
	        'min_price_amount'     => $min_price_amount,
	        'max_price_amount'     => $max_price_amount,
	        'min_price_percentage' => $min_price_percentage,
	        'max_price_percentage' => $max_price_percentage,
	    );
	    update_option('wpla_price_wizard_options', $options );

		$lm = new WPLA_ListingsModel();

		foreach ( $item_ids as $listing_id ) {
		
			// load listing item
			$item       = $lm->getItem( $listing_id, OBJECT );
			if ( ! $item ) continue;
			if ( $item->product_type == 'variable') continue;

			$post_id 	= $item->post_id;

			// get base price (min)
			$base_price = 0;
			if ( $min_base_price == 'price' ) 		$base_price = WPLA_ProductWrapper::getOriginalPrice( $post_id );
			if ( $min_base_price == 'sale_price' ) 	$base_price = WPLA_ProductWrapper::getPrice( $post_id );
			if ( $min_base_price == 'msrp' ) 	    $base_price = get_post_meta( $post_id, '_msrp', true ) ? get_post_meta( $post_id, '_msrp', true ) : get_post_meta( $post_id, '_msrp_price', true );

			// calculate new min price
			if ( $min_price_percentage )			$base_price = $base_price + ( $base_price * floatval($min_price_percentage) / 100 );
			if ( $min_price_amount )				$base_price = $base_price + floatval($min_price_amount);
			if ( $min_base_price == 'no_change' ) 	$base_price = $item->min_price;
			$new_min_price = round( $base_price, 2 );
			if ( $min_base_price == 'remove' ) 		$new_min_price = NULL;


			// get base price (max)
			$base_price = 0;
			if ( $max_base_price == 'price' ) 		$base_price = WPLA_ProductWrapper::getOriginalPrice( $post_id );
			if ( $max_base_price == 'sale_price' ) 	$base_price = WPLA_ProductWrapper::getPrice( $post_id );
			if ( $max_base_price == 'msrp' ) 	    $base_price = get_post_meta( $post_id, '_msrp', true ) ? get_post_meta( $post_id, '_msrp', true ) : get_post_meta( $post_id, '_msrp_price', true );

			// calculate new max price
			if ( $max_price_percentage )			$base_price = $base_price + ( $base_price * floatval($max_price_percentage) / 100 );
			if ( $max_price_amount )				$base_price = $base_price + floatval($max_price_amount);
			if ( $max_base_price == 'no_change' ) 	$base_price = $item->max_price;
			$new_max_price = round( $base_price, 2 );
			if ( $max_base_price == 'remove' ) 		$new_max_price = NULL;


			// update listing table
			$data = array(
				'min_price' => $new_min_price,
				'max_price' => $new_max_price,
				'pnq_status' => 1, // mark as changed
			);
			$lm->updateWhere( array( 'id' => $listing_id ), $data );

			// update product
        	update_post_meta( $item->post_id, '_amazon_minimum_price', $new_min_price );
        	update_post_meta( $item->post_id, '_amazon_maximum_price', $new_max_price );

		} // foreach item

	} // updateMinMaxPrices()
		

} // class WPLA_MinMaxPriceWizard
