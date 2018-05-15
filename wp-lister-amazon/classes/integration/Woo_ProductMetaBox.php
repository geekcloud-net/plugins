<?php
/**
 * add amazon options metaboxes to product edit page
 */

class WPLA_Product_MetaBox {

	function __construct() {

		add_action( 'add_meta_boxes', array( &$this, 'add_meta_box' ) );
		add_action( 'woocommerce_process_product_meta', array( &$this, 'save_meta_box' ), 0, 2 );

        // add options to variable products
        add_action('woocommerce_product_after_variable_attributes', array(&$this, 'woocommerce_variation_options'), 1, 3);
        add_action('woocommerce_product_after_variable_attributes', array(&$this, 'woocommerce_custom_variation_meta_fields'), 2, 3);
        add_action('woocommerce_process_product_meta_variable', array(&$this, 'process_product_meta_variable'), 10, 1);
        add_action('woocommerce_process_product_meta_variable', array(&$this, 'process_custom_variation_meta_fields'), 10, 1);
		add_action('woocommerce_ajax_save_product_variations',  array( $this, 'process_product_meta_variable') ); // WC2.4
		add_action('woocommerce_ajax_save_product_variations',  array( $this, 'process_custom_variation_meta_fields') ); // WC2.4

		// remove amazon specific meta data from duplicated products
		add_action( 'woocommerce_duplicate_product', array( &$this, 'woocommerce_duplicate_product' ), 0, 2 );
	}

	function add_meta_box() {

		$title = __('Amazon Options', 'wpla');
		add_meta_box( 'wpla-amazon-options', $title, array( &$this, 'meta_box_basic' ), 'product', 'normal', 'default');

		$title = __('Advanced Amazon Options', 'wpla');
		add_meta_box( 'wpla-amazon-advanced', $title, array( &$this, 'meta_box_advanced' ), 'product', 'normal', 'default');

		$this->enqueueFileTree();

	}

	function meta_box_basic() {
		global $woocommerce, $post;

	    // if ( get_option( 'wpla_enable_missing_details_warning' ) == '1' ) {
		//	$this->add_validation_js();
	    // }

        ?>
        <style type="text/css">
        	/* standard input fields */
            #wpla-amazon-options label, 
            #wpla-amazon-advanced label { 
            	float: left;
            	width: 25%;
            	line-height: 2em;
            }
            #wpla-amazon-options input,
            #wpla-amazon-advanced .wpl_amazon_asin_field input {
            	width: 70%; 
            }

            /* radio buttons */
            #wpla-amazon-options label ul.wc-radios label, 
            #wpla-amazon-advanced label ul.wc-radios label { 
            	float: right;
            	width: auto;
            }
            #wpla-amazon-options input.select, 
            #wpla-amazon-advanced input.select { 
            	width: auto; 
            }

            #wpla-amazon-options .description, 
            #wpla-amazon-advanced .description { 
            	clear: both;
            	display: block;
            	margin-left: 25%;
            }
            #wpl_amazon_product_description { 
            	height: 10em;
            }

            #wpla-amazon-options .woocommerce-help-tip,
            #wpla-amazon-advanced .woocommerce-help-tip {
            	float: right;
            	margin-top: 5px;
            	margin-right: 10px;
            	font-size: 1.4em;
            }
        </style>
        <?php

		wp_nonce_field( 'wpla_save_product', 'wpla_save_product_nonce' );

		woocommerce_wp_text_input( array(
			'id' 			=> 'wpl_amazon_product_id',
			'label' 		=> __('Product ID', 'wpla'),
			'placeholder' 	=> 'UPC or EAN',
			'description' 	=> 'A standard, alphanumeric string that uniquely identifies the product. This could be a GCID (16 alphanumeric characters), UPC or EAN. This is a required field to list new products on Amazon.',
			'desc_tip'		=>  true,
			'value'			=> get_post_meta( $post->ID, '_amazon_product_id', true )
		) );

		woocommerce_wp_select( array(
		// woocommerce_wp_radio( array(
			'id' 			=> 'wpl_amazon_id_type',
			'label' 		=> __('Product ID Type', 'wpla'),
			'options' 		=> array( 
					''          => __('-- use profile setting --', 'wpla'),
					'UPC'   	=> __('UPC', 'wpla'),
					'EAN'   	=> __('EAN', 'wpla')
				),
			'description' 	=> 'The type of standard, unique identifier entered in the Product ID field. This is a required field to list new products on Amazon.',
			'desc_tip'		=>  true,
			'value'			=> get_post_meta( $post->ID, '_amazon_id_type', true )
		) );

		woocommerce_wp_text_input( array(
			'id' 			=> 'wpl_amazon_title',
			'label' 		=> __('Listing title', 'wpla'),
			'placeholder' 	=> 'Custom listing title',
			'description' 	=> __('Leave empty to generate title from product name.<br>Use the placeholder "%%%" to insert variation attribute values at a custom position.<br><br>Maximum length: 500 characters','wpla'),
			'desc_tip'		=>  true,
			'custom_attributes' => array( 'maxlength' => 500 ),
			'value'			=> get_post_meta( $post->ID, '_amazon_title', true )
		) );

		if ( get_option( 'wpla_enable_custom_product_prices', 1 ) != 0 ) {

			woocommerce_wp_text_input( array(
				'id' 			=> 'wpl_amazon_price',
				'label' 		=> __('Amazon Price', 'wpla'),
				'description' 	=> __('A custom price to be used when listing this product on Amazon.<br>Leave empty to use the Woocommerce product price.<br><br>Note: If a custom Amazon price is set, the product\'s price will not be updated with the current Amazon price when processing an inventory report with the "Update product prices" import option enabled.','wpla'),
				'desc_tip'		=>  true,
				'placeholder' 	=> 'Custom Price',
				'class' 		=> 'wc_input_price',
				'value'			=> get_post_meta( $post->ID, '_amazon_price', true )
			) );

		}

		if ( get_option( 'wpla_enable_minmax_product_prices', 0 ) != 0 ) {

			woocommerce_wp_text_input( array(
				'id' 			=> 'wpl_amazon_minimum_price',
				'label' 		=> __('Minimum Price', 'wpla'),
				'description' 	=> __('This is used to automatically set the price to the lowest price on Amazon - if it is between minimum and maxmimum price.','wpla'),
				'desc_tip'		=>  true,
				'placeholder' 	=> 'Minimum Price',
				'class' 		=> 'wc_input_price',
				'value'			=> get_post_meta( $post->ID, '_amazon_minimum_price', true )
			) );

			woocommerce_wp_text_input( array(
				'id' 			=> 'wpl_amazon_maximum_price',
				'label' 		=> __('Maximum Price', 'wpla'),
				'description' 	=> __('This is used to automatically set the price to the lowest price on Amazon - if it is between minimum and maxmimum price.','wpla'),
				'desc_tip'		=>  true,
				'placeholder' 	=> 'Maximum Price',
				'class' 		=> 'wc_input_price',
				'value'			=> get_post_meta( $post->ID, '_amazon_maximum_price', true )
			) );

		}

		if ( get_option( 'wpla_enable_item_condition_fields', 2 ) != 0 ) {

			woocommerce_wp_select( array(
				'id' 			=> 'wpl_amazon_condition_type',
				'label' 		=> __('Item Condition', 'wpla'),
				'options' 		=> array( 
						''                      => __('-- use profile setting --', 'wpla'),
						'New'                   => __('New', 'wpla'),
						'UsedLikeNew'           => __('Used - Like New', 'wpla'),
						'UsedVeryGood'          => __('Used - Very Good', 'wpla'),
						'UsedGood'              => __('Used - Good', 'wpla'),
						'UsedAcceptable'        => __('Used - Acceptable', 'wpla'),
						'Refurbished'           => __('Refurbished', 'wpla'),
						'CollectibleLikeNew'    => __('Collectible - Like New', 'wpla'),
						'CollectibleVeryGood'   => __('Collectible - Very Good', 'wpla'),
						'CollectibleGood'       => __('Collectible - Good', 'wpla'),
						'CollectibleAcceptable' => __('Collectible - Acceptable', 'wpla'),
					),
				'description' 	=> 'Indicates the condition of the item. Review the condition guidelines definitions.',
				'desc_tip'		=>  true,
				'value'			=> get_post_meta( $post->ID, '_amazon_condition_type', true )
			) );

			woocommerce_wp_text_input( array(
				'id' 			=> 'wpl_amazon_condition_note',
				'label' 		=> __('Condition Note', 'wpla'),
				'description' 	=> 'Descriptive text explaining the actual condition of the item. Required if item condition is not "New". <br>Example: "Small dent in left side panel."',
				'desc_tip'		=>  true,
				'custom_attributes' => array( 'maxlength' => 1000 ),
				'value'			=> get_post_meta( $post->ID, '_amazon_condition_note', true )
			) );

		}

		woocommerce_wp_text_input( array(
			'id' 			=> 'wpl_amazon_bullet_point1',
			'label' 		=> __('Bullet Point 1', 'wpla'),
			'custom_attributes' => array( 'maxlength' => 2000 ),
			'value'			=> get_post_meta( $post->ID, '_amazon_bullet_point1', true )
		) );
		woocommerce_wp_text_input( array(
			'id' 			=> 'wpl_amazon_bullet_point2',
			'label' 		=> __('Bullet Point 2', 'wpla'),
			'custom_attributes' => array( 'maxlength' => 2000 ),
			'value'			=> get_post_meta( $post->ID, '_amazon_bullet_point2', true )
		) );
		woocommerce_wp_text_input( array(
			'id' 			=> 'wpl_amazon_bullet_point3',
			'label' 		=> __('Bullet Point 3', 'wpla'),
			'custom_attributes' => array( 'maxlength' => 2000 ),
			'value'			=> get_post_meta( $post->ID, '_amazon_bullet_point3', true )
		) );
		woocommerce_wp_text_input( array(
			'id' 			=> 'wpl_amazon_bullet_point4',
			'label' 		=> __('Bullet Point 4', 'wpla'),
			'custom_attributes' => array( 'maxlength' => 2000 ),
			'value'			=> get_post_meta( $post->ID, '_amazon_bullet_point4', true )
		) );
		woocommerce_wp_text_input( array(
			'id' 			=> 'wpl_amazon_bullet_point5',
			'label' 		=> __('Bullet Point 5', 'wpla'),
			'custom_attributes' => array( 'maxlength' => 2000 ),
			'value'			=> get_post_meta( $post->ID, '_amazon_bullet_point5', true )
		) );

		woocommerce_wp_text_input( array(
			'id' 			=> 'wpl_amazon_generic_keywords1',
			'label' 		=> __('Keywords 1', 'wpla'),
			'custom_attributes' => array( 'maxlength' => 500 ),
			'value'			=> get_post_meta( $post->ID, '_amazon_generic_keywords1', true )
		) );
		woocommerce_wp_text_input( array(
			'id' 			=> 'wpl_amazon_generic_keywords2',
			'label' 		=> __('Keywords 2', 'wpla'),
			'custom_attributes' => array( 'maxlength' => 500 ),
			'value'			=> get_post_meta( $post->ID, '_amazon_generic_keywords2', true )
		) );
		woocommerce_wp_text_input( array(
			'id' 			=> 'wpl_amazon_generic_keywords3',
			'label' 		=> __('Keywords 3', 'wpla'),
			'custom_attributes' => array( 'maxlength' => 500 ),
			'value'			=> get_post_meta( $post->ID, '_amazon_generic_keywords3', true )
		) );
		woocommerce_wp_text_input( array(
			'id' 			=> 'wpl_amazon_generic_keywords4',
			'label' 		=> __('Keywords 4', 'wpla'),
			'custom_attributes' => array( 'maxlength' => 500 ),
			'value'			=> get_post_meta( $post->ID, '_amazon_generic_keywords4', true )
		) );
		woocommerce_wp_text_input( array(
			'id' 			=> 'wpl_amazon_generic_keywords5',
			'label' 		=> __('Keywords 5', 'wpla'),
			'custom_attributes' => array( 'maxlength' => 500 ),
			'value'			=> get_post_meta( $post->ID, '_amazon_generic_keywords5', true )
		) );

	} // meta_box_basic()

	function meta_box_advanced() {
		global $woocommerce, $post;

		/*woocommerce_wp_textarea_input( array(
			'id' 			=> 'wpl_amazon_product_description',
			'label' 		=> __('Custom Product Description', 'wpla'),
			'placeholder' 	=> 'Leave this empty to use the default description.',
			// 'description' 	=> 'Leave this empty to use the default description.',
			// 'desc_tip'		=>  true,
			'value'			=> get_post_meta( $post->ID, '_amazon_product_description', true )
		) );*/
		$description = get_post_meta( $post->ID, '_amazon_product_description', true );
        $settings = apply_filters( 'wpla_product_description_editor_settings', array(
            'wpautop' => false,
            'media_buttons' => false,
            'tinymce' => false
        ) );

		echo '<label for="wpl_amazon_product_description">'. __( 'Custom Product Description', 'wpla' ) .'</label>';
		wp_editor( $description, 'wpl_amazon_product_description', $settings );

		woocommerce_wp_text_input( array(
			'id' 			=> 'wpl_amazon_asin',
			'label' 		=> __('ASIN', 'wpla'),
			'placeholder' 	=> 'ASIN',
			'description' 	=> 'Do not change this unless you know what you are doing.',
			// 'desc_tip'		=>  true,
			'value'			=> get_post_meta( $post->ID, '_wpla_asin', true )
		) );

		// $tb_url = 'admin-ajax.php?action=wpla_show_product_matches&id='.$post->ID.'&width=640&height=420';
		// echo '<a href="'.$tb_url.'" class="thickbox" title="Match product on Amazon"><img src="'.WPLA_URL.'/img/search3.png" alt="match" /></a>';

	} // meta_box_advanced()


	function add_validation_js() {
		global $woocommerce;
        wc_enqueue_js("
			jQuery( document ).ready( function () {

			    // 
			    // Validation
			    // 

				// check required values on submit
				jQuery('form#post').on('submit', function() {

					var missing_fields = new Array();

					// SKU
					if ( jQuery('#_sku')[0].value == '' ) {
						// alert('Please enter a SKU to be able to list this product on Amazon.'); //return false;
						missing_fields.push('SKU');
					}

					// handle variable products
					if ( jQuery('#product-type')[0].value == 'variable' ) {

					} else { // non-variable product

						// Price
						if ( jQuery('#_regular_price')[0].value == '' ) {
							// alert('Please enter a price to be able to list this product on Amazon.'); //return false;
							missing_fields.push('".__('Price','wpla')."');
						}

						// Sale Price Dates
						// if ( jQuery('#_sale_price')[0].value != '' ) {
						// 	if ( jQuery('#_sale_price_dates_from')[0].value == '' ) {
						// 		missing_fields.push('".__('Sale start date','wpla')."');
						// 	}
						// 	if ( jQuery('#_sale_price_dates_to')[0].value == '' ) {
						// 		missing_fields.push('".__('Sale end date','wpla')."');
						// 	}
						// }

						// Quantity
						if ( jQuery('#_stock')[0].value == '' ) {
							// alert('Please enter a stock quantity to be able to list this product on Amazon.'); //return false;
							missing_fields.push('".__('Quantity','wpla')."');
						}

					}

					if ( missing_fields.length > 0 ) {
						var CRLF = \"\\n\";
						var msg  = '".__('This product is missing the following fields required to be listed on Amazon:','wpla')."' + CRLF + CRLF + '- ' + missing_fields.join(CRLF+'- ');
						alert(msg); //return false;
					}

					return true;
				})

			});
	    ");
	} // add_validation_js()


	function save_meta_box( $post_id, $post ) {
		$lm = new WPLA_ListingsModel();

		// check nonce
		if ( ! isset( $_POST['wpla_save_product_nonce'] ) || ! wp_verify_nonce( $_POST['wpla_save_product_nonce'], 'wpla_save_product' ) ) return;

		// convert decimal comma for all price fields
		$_amazon_price         = str_replace( ',', '.', @$_POST['wpl_amazon_price'] );
		$_amazon_minimum_price = str_replace( ',', '.', @$_POST['wpl_amazon_minimum_price'] );
		$_amazon_maximum_price = str_replace( ',', '.', @$_POST['wpl_amazon_maximum_price'] );

		// Update post meta
		update_post_meta( $post_id, '_amazon_title', 				esc_attr( trim( @$_POST['wpl_amazon_title'] ) ) );
		update_post_meta( $post_id, '_amazon_price', 				esc_attr( trim( $_amazon_price ) ) );
		update_post_meta( $post_id, '_amazon_minimum_price', 		esc_attr( trim( $_amazon_minimum_price ) ) );
		update_post_meta( $post_id, '_amazon_maximum_price', 		esc_attr( trim( $_amazon_maximum_price ) ) );
		update_post_meta( $post_id, '_amazon_product_id', 			esc_attr( trim( @$_POST['wpl_amazon_product_id'] ) ) );
		update_post_meta( $post_id, '_amazon_id_type', 				esc_attr( @$_POST['wpl_amazon_id_type'] ) );
		update_post_meta( $post_id, '_amazon_condition_type', 		esc_attr( @$_POST['wpl_amazon_condition_type'] ) );
		update_post_meta( $post_id, '_amazon_condition_note', 		esc_attr( @$_POST['wpl_amazon_condition_note'] ) );
		update_post_meta( $post_id, '_amazon_bullet_point1',		esc_attr( @$_POST['wpl_amazon_bullet_point1'] ) );
		update_post_meta( $post_id, '_amazon_bullet_point2',		esc_attr( @$_POST['wpl_amazon_bullet_point2'] ) );
		update_post_meta( $post_id, '_amazon_bullet_point3',		esc_attr( @$_POST['wpl_amazon_bullet_point3'] ) );
		update_post_meta( $post_id, '_amazon_bullet_point4',		esc_attr( @$_POST['wpl_amazon_bullet_point4'] ) );
		update_post_meta( $post_id, '_amazon_bullet_point5',		esc_attr( @$_POST['wpl_amazon_bullet_point5'] ) );
		update_post_meta( $post_id, '_amazon_generic_keywords1',	esc_attr( @$_POST['wpl_amazon_generic_keywords1'] ) );
		update_post_meta( $post_id, '_amazon_generic_keywords2',	esc_attr( @$_POST['wpl_amazon_generic_keywords2'] ) );
		update_post_meta( $post_id, '_amazon_generic_keywords3',	esc_attr( @$_POST['wpl_amazon_generic_keywords3'] ) );
		update_post_meta( $post_id, '_amazon_generic_keywords4',	esc_attr( @$_POST['wpl_amazon_generic_keywords4'] ) );
		update_post_meta( $post_id, '_amazon_generic_keywords5',	esc_attr( @$_POST['wpl_amazon_generic_keywords5'] ) );
		update_post_meta( $post_id, '_amazon_product_description',	esc_attr( @$_POST['wpl_amazon_product_description'] ) );

		update_post_meta( $post_id, '_wpla_asin',					esc_attr( trim( @$_POST['wpl_amazon_asin'] ) ) );


        // create matched listing when ASIN is entered manually
        $this->auto_match_ASIN( $post_id );

		// update min/max prices in listings table
		if ( isset( $_POST['wpl_amazon_minimum_price'] ) ) {

			$min_price = $_amazon_minimum_price;
			$max_price = $_amazon_maximum_price;
			$data      = array();

			if ( $listing = $lm->getItemByPostID( $post_id ) ) {

				if ( $min_price != $listing->min_price ) {
					$data['min_price']  = $min_price;
					$data['pnq_status'] = 1; // mark as changed
				}

				if ( $max_price != $listing->max_price ) {
					$data['max_price']  = $max_price;
					$data['pnq_status'] = 1; // mark as changed
				}

				// update listing
				if ( ! empty($data) ) {
					$lm->updateWhere( array( 'id' => $listing->id ), $data );
				}

			}

		}

		// update custom listings title
		$lm->updateCustomListingTitle( $post_id );

	} // save_meta_box()



    // create matched listing when ASIN is entered manually
    function auto_match_ASIN( $post_id ) {

        // check if we have an ASIN
        $asin = trim( get_post_meta( $post_id, '_wpla_asin', true ) );
        if ( ! $asin ) return;

		// check if this ASIN / ID already exist - skip if it does
		$lm = new WPLA_ListingsModel();
		if ( $lm->getItemByASIN( $asin, false ) ) return;
		if ( $lm->getItemByPostID( $post_id ) ) return;

        // get default account
		$default_account_id = get_option( 'wpla_default_account_id', 1 );
		if ( ! $default_account_id ) return;

		// insert matched listing
		$success = $lm->insertMatchedProduct( $post_id, $asin, $default_account_id );

		if ( $success ) {
			$msg = isset($lm->lastError) ? $lm->lastError : '';
			WPLA()->logger->info( "auto-matched product #$post_id - $msg" );
		} else {
			// TODO: implement persistent admin messages
			$msg = isset($lm->lastError) ? $lm->lastError : '';
			wpla_show_message( "Failed to match product #$post_id: $msg", 'error' ); // won't show because page is reloaded after saving
			WPLA()->logger->warn( "Failed to match product #$post_id - please report this to support." );
			// echo "Failed to match product #$post_id - please report this to support.";
		}

	} // auto_match_ASIN()



	/* show custom meta fields for variations */
    function woocommerce_custom_variation_meta_fields( $loop, $variation_data, $variation ) {

		// get variation post_id - WC2.3
		$variation_post_id = $variation ? $variation->ID : false;

		// handle custom variation meta fields
		$variation_meta_fields = get_option('wpla_variation_meta_fields', array() );
		foreach ( $variation_meta_fields as $key => $varmeta ) :

			// $meta_key    = 'meta_'.$key;
			$field_label = $varmeta['label'];

			// get current value
			$current_value = get_post_meta( $variation_post_id, $key, true );
			?>

            <div>
                <p class="form-row form-row-full">
                    <label>
                        <?php echo $field_label ?>
                    </label> 
                    <input type="text" name="variable_wpla_<?php echo $key; ?>[<?php echo $loop; ?>]" class="" value="<?php echo $current_value ?>" placeholder="" />
                </p>
            </div>

			<?php
		endforeach;

	} // woocommerce_custom_variation_meta_fields()

    public function process_custom_variation_meta_fields( $post_id ) {

		// get custom variation meta fields
		$variation_meta_fields = get_option('wpla_variation_meta_fields', array() );
		if ( ! is_array($variation_meta_fields) ) return;
		
		foreach ( $variation_meta_fields as $key => $varmeta ) {
			$this->process_single_custom_variation_meta_field( $post_id, $key );
		}

	} // process_custom_variation_meta_fields()

    public function process_single_custom_variation_meta_field( $post_id, $key ) {
        if ( ! isset($_POST['variable_wpla_'.$key]) ) return;

		$variable_post_id       = $_POST['variable_post_id'];
		$variable_VALUES        = $_POST['variable_wpla_'.$key];

        $max_loop = max( array_keys( $_POST['variable_post_id'] ) );
        for ( $i=0; $i <= $max_loop; $i++ ) {

            if ( ! isset( $variable_post_id[$i] ) ) continue;
            $variation_id = (int) $variable_post_id[$i];

            // Update post meta
            update_post_meta( $variation_id, $key, $variable_VALUES[$i] );

        } // each variation

    } // process_single_custom_variation_meta_field()



	/* show additional fields for variations */
    function woocommerce_variation_options( $loop, $variation_data, $variation ) {
        // echo "<pre>";print_r($loop);echo"</pre>";#die();
        // echo "<pre>";print_r($variation_data);echo"</pre>";#die();
        // echo "<pre>";print_r($variation);echo"</pre>";#die();


        // available ID types
		$available_id_types = array( 
			''          => __('-- use profile setting --', 'wpla'),
			'UPC'   	=> __('UPC', 'wpla'),
			'EAN'   	=> __('EAN', 'wpla')
		);

        // available item conditions
		$available_item_conditions = array( 
			''                      => __('-- use profile setting --', 'wpla'),
			'New'                   => __('New', 'wpla'),
			'UsedLikeNew'           => __('Used - Like New', 'wpla'),
			'UsedVeryGood'          => __('Used - Very Good', 'wpla'),
			'UsedGood'              => __('Used - Good', 'wpla'),
			'UsedAcceptable'        => __('Used - Acceptable', 'wpla'),
			'Refurbished'           => __('Refurbished', 'wpla'),
			'CollectibleLikeNew'    => __('Collectible - Like New', 'wpla'),
			'CollectibleVeryGood'   => __('Collectible - Very Good', 'wpla'),
			'CollectibleGood'       => __('Collectible - Good', 'wpla'),
			'CollectibleAcceptable' => __('Collectible - Acceptable', 'wpla'),
		);

		// // current values
		// $_amazon_id_type       = isset( $variation_data['_amazon_id_type'][0] ) 		? $variation_data['_amazon_id_type'][0] 		: '';
		// $_amazon_product_id    = isset( $variation_data['_amazon_product_id'][0] ) 		? $variation_data['_amazon_product_id'][0] 		: '';
		// $_amazon_price         = isset( $variation_data['_amazon_price'][0] )      		? $variation_data['_amazon_price'][0] 			: '';
		// $_amazon_minimum_price = isset( $variation_data['_amazon_minimum_price'][0] )   ? $variation_data['_amazon_minimum_price'][0] 	: '';
		// $_amazon_maximum_price = isset( $variation_data['_amazon_maximum_price'][0] )   ? $variation_data['_amazon_maximum_price'][0] 	: '';
		// $_amazon_asin          = isset( $variation_data['_wpla_asin'][0] ) 				? $variation_data['_wpla_asin'][0] 				: '';

		// get variation post_id - WC2.3
		$variation_post_id = $variation ? $variation->ID : $variation_data['variation_post_id']; // $variation exists since WC2.2 (at least)

		// get current values - WC2.3
		$_amazon_id_type        = get_post_meta( $variation_post_id, '_amazon_id_type'  		, true );
		$_amazon_product_id     = get_post_meta( $variation_post_id, '_amazon_product_id'  		, true );
		$_amazon_price          = get_post_meta( $variation_post_id, '_amazon_price'       		, true );
		$_amazon_minimum_price  = get_post_meta( $variation_post_id, '_amazon_minimum_price' 	, true );
		$_amazon_maximum_price  = get_post_meta( $variation_post_id, '_amazon_maximum_price' 	, true );
		$_amazon_condition_type = get_post_meta( $variation_post_id, '_amazon_condition_type' 	, true );
		$_amazon_condition_note = get_post_meta( $variation_post_id, '_amazon_condition_note' 	, true );
		$_amazon_is_disabled    = get_post_meta( $variation_post_id, '_amazon_is_disabled'   	, true );
		$_amazon_asin           = get_post_meta( $variation_post_id, '_wpla_asin'  				, true );

        ?>

            <div>
	        	<h4 style="border-bottom: 1px solid #ddd; margin:0; padding-top:1em; clear:both;"><?php _e('Amazon Options', 'wpla'); ?></h4>
                <p class="form-row form-row-first">
                    <label>
                        <?php _e('Product ID', 'wpla'); ?>
                        <a class="tips" data-tip="To list <b>new products</b> on Amazon, you need to enter a UPC or EAN for each single variation.<br>If your products already exist on Amazon leave this empty and enter or select an ASIN below." href="#">[?]</a>
                    </label> 
                    <input type="text" name="variable_amazon_product_id[<?php echo $loop; ?>]" class="" value="<?php echo $_amazon_product_id ?>" placeholder="UPC / EAN" />
                </p>
                <p class="form-row form-row-last">
                    <label>
                        <?php _e('Product ID Type', 'wpla'); ?>
                        <a class="tips" data-tip="The type of standard, unique identifier entered in the Product ID field. You can leave this unset if you specify the Product ID Type in your listing profile." href="#">[?]</a>
                    </label> 
                    <select name="variable_amazon_id_type[<?php echo $loop; ?>]" class="wpla_var_selector">
                        <?php
                        foreach ( $available_id_types as $key => $option_name ) {
                            echo '<option value="' . $key . '" ';
                            selected($key, $_amazon_id_type);
                            echo '>' . $option_name . '</option>';
                        }
                        ?>
                    </select>
                </p>
            </div>
            <div>
                <p class="form-row form-row-first">
                    <label>
                        <?php _e('ASIN', 'wpla'); ?>
                        <a class="tips" data-tip="To list <b>existing products</b> on Amazon, you need to enter an ASIN for each variation." href="#">[?]</a>
                    </label> 
                    <input type="text" id="variable_amazon_asin_<?php echo $loop; ?>" name="variable_amazon_asin[<?php echo $loop; ?>]" class="" value="<?php echo $_amazon_asin ?>" />
                </p>
                <p class="form-row form-row-last">
                    <label style="display: block; margin-top: 2px;">&nbsp;</label>
                	<?php 
                		$tb_url = "admin-ajax.php?action=wpla_show_product_matches&id=" . $variation_post_id; // . "&height=420&width=640"; 
                		$onclick  = 'window.wpla_matching_asin_field_id = "variable_amazon_asin_'.$loop.'";';
                		$onclick .= 'tb_show("Match variation #'.$variation_post_id.' on Amazon", "'.$tb_url.'");';
                		$onclick .= 'return false;';
                	?>
                    <a href="#" onclick='<?php echo $onclick ?>' class="button">
                    	<?php echo 'Select from Amazon' ?>
                    </a>
                </p>
            </div>

            <?php if ( get_option( 'wpla_enable_custom_product_prices', 1 ) == 1 ) : ?>
            <div>
                <p class="form-row form-row-first">
                    <label>
                        <?php _e('Amazon Price', 'wpla'); ?>
                        <a class="tips" data-tip="Custom price to be used when listing this product on Amazon. This will override price modifier settings in your listing profile." href="#">[?]</a>
                    </label> 
                    <input type="text" name="variable_amazon_price[<?php echo $loop; ?>]" class="wc_input_price" value="<?php echo $_amazon_price ?>" />
                </p>
                <p class="form-row form-row-last">
                    <label style="display: block;">
                        <?php _e('Amazon Visibility', 'wpla'); ?>
                        <a class="tips" data-tip="Tick the checkbox below to omit this particular variation when this product is listed on Amazon.<br><br>Note: Ticking the box will not remove an existing listing for this variation!" href="#">[?]</a>
                    </label> 
                	<label style="line-height: 2.6em;">
                		<input type="checkbox" class="checkbox" name="variable_amazon_is_disabled[<?php echo $loop; ?>]" style="margin-top: 9px !important; margin-right: 9px !important;"
                			<?php if ( $_amazon_is_disabled ) echo 'checked="checked"' ?> >
                		<?php _e('Hide on Amazon', 'wpla'); ?>
                	</label>
                </p>
            </div>
            <?php endif; ?>

            <?php if ( get_option( 'wpla_enable_minmax_product_prices', 0 ) == 1 ) : ?>
            <div>
                <p class="form-row form-row-first">
                    <label>
                        <?php _e('Minimum Price', 'wpla'); ?>
                        <a class="tips" data-tip="This is used to automatically set the price to the lowest price on Amazon - if it is between minimum and maxmimum price." href="#">[?]</a>
                    </label> 
                    <input type="text" name="variable_amazon_minimum_price[<?php echo $loop; ?>]" class="wc_input_price" value="<?php echo $_amazon_minimum_price ?>" />
                </p>
                <p class="form-row form-row-last">
                    <label>
                        <?php _e('Maximum Price', 'wpla'); ?>
                        <a class="tips" data-tip="This is used to automatically set the price to the lowest price on Amazon - if it is between minimum and maxmimum price." href="#">[?]</a>
                    </label> 
                    <input type="text" name="variable_amazon_maximum_price[<?php echo $loop; ?>]" class="wc_input_price" value="<?php echo $_amazon_maximum_price ?>" />
                </p>
            </div>
            <?php endif; ?>

            <?php if ( get_option( 'wpla_enable_item_condition_fields', 0 ) == 1 ) : ?>
            <div>
                <p class="form-row form-row-first">
                    <label>
                        <?php _e('Item Condition', 'wpla'); ?>
                        <a class="tips" data-tip="Indicates the condition of the item." href="#">[?]</a>
                    </label> 
					<select name="variable_amazon_condition_type[<?php echo $loop; ?>]" class="select" style="">
                    <?php
                        foreach ( $available_item_conditions as $key => $option_name ) {
                            echo '<option value="' . $key . '" ';
                            selected($key, $_amazon_condition_type);
                            echo '>' . $option_name . '</option>';
                        }
                    ?>
					</select>


                </p>
                <p class="form-row form-row-last">
                    <label>
                        <?php _e('Condition Note', 'wpla'); ?>
                        <a class="tips" data-tip="Descriptive text explaining the actual condition of the item. Required if item condition is not New." href="#">[?]</a>
                    </label> 
                    <input type="text" name="variable_amazon_condition_note[<?php echo $loop; ?>]" class="" value="<?php echo $_amazon_condition_note ?>" maxlength="1000" />
                </p>
            </div>
            <?php endif; ?>

        <?php

    } // woocommerce_variation_options()


    public function process_product_meta_variable( $post_id ) {
    	WPLA()->logger->info('process_product_meta_variable() - '.$post_id);
        if ( ! isset($_POST['variable_sku']) ) return;

		$variable_post_id               = $_POST['variable_post_id'];
		$variable_amazon_product_id     = $_POST['variable_amazon_product_id'];
		$variable_amazon_id_type        = $_POST['variable_amazon_id_type'];
		$variable_amazon_asin           = $_POST['variable_amazon_asin'];
		$variable_sku                   = $_POST['variable_sku'];
		$variable_amazon_price          = isset( $_POST['variable_amazon_price']          ) ? $_POST['variable_amazon_price']          : '';
		$variable_amazon_minimum_price  = isset( $_POST['variable_amazon_minimum_price']  ) ? $_POST['variable_amazon_minimum_price']  : '';
		$variable_amazon_maximum_price  = isset( $_POST['variable_amazon_maximum_price']  ) ? $_POST['variable_amazon_maximum_price']  : '';
		$variable_amazon_condition_type = isset( $_POST['variable_amazon_condition_type'] ) ? $_POST['variable_amazon_condition_type'] : '';
		$variable_amazon_condition_note = isset( $_POST['variable_amazon_condition_note'] ) ? $_POST['variable_amazon_condition_note'] : '';
		$variable_amazon_is_disabled    = isset( $_POST['variable_amazon_is_disabled']    ) ? $_POST['variable_amazon_is_disabled']    : '';

		// convert decimal comma for all price fields
		$variable_amazon_price         = str_replace( ',', '.', $variable_amazon_price         );
		$variable_amazon_minimum_price = str_replace( ',', '.', $variable_amazon_minimum_price );
		$variable_amazon_maximum_price = str_replace( ',', '.', $variable_amazon_maximum_price );

        $lm = new WPLA_ListingsModel();
        $all_variations_with_SKU  = array();
        $all_variations_with_ASIN = array();

        $max_loop = max( array_keys( $_POST['variable_post_id'] ) );
        for ( $i=0; $i <= $max_loop; $i++ ) {

            if ( ! isset( $variable_post_id[$i] ) ) continue;
            $variation_id = (int) $variable_post_id[$i];

            // Update post meta
            update_post_meta( $variation_id, '_amazon_product_id', 		trim( $variable_amazon_product_id[$i] ) );
            update_post_meta( $variation_id, '_amazon_id_type', 		      $variable_amazon_id_type[$i]      );
            update_post_meta( $variation_id, '_wpla_asin', 				trim( $variable_amazon_asin[$i]       ) );
            update_post_meta( $variation_id, '_amazon_price', 			isset( $variable_amazon_price[$i]          ) ? trim( $variable_amazon_price[$i]         ) : '' );
            update_post_meta( $variation_id, '_amazon_minimum_price', 	isset( $variable_amazon_minimum_price[$i]  ) ? trim( $variable_amazon_minimum_price[$i] ) : '' );
            update_post_meta( $variation_id, '_amazon_maximum_price', 	isset( $variable_amazon_maximum_price[$i]  ) ? trim( $variable_amazon_maximum_price[$i] ) : '' );
            update_post_meta( $variation_id, '_amazon_condition_type', 	isset( $variable_amazon_condition_type[$i] ) ? trim( $variable_amazon_condition_type[$i] ) : '' );
            update_post_meta( $variation_id, '_amazon_condition_note', 	isset( $variable_amazon_condition_note[$i] ) ? trim( $variable_amazon_condition_note[$i] ) : '' );
            update_post_meta( $variation_id, '_amazon_is_disabled', 	isset( $variable_amazon_is_disabled[$i]    ) ? $variable_amazon_is_disabled[$i]           : '' );

            // if ( $variable_amazon_product_id[$i] !== 'parent' )
            //     update_post_meta( $variation_id, '_amazon_product_id', $variable_amazon_product_id[$i] );
            // else
            //     delete_post_meta( $variation_id, '_amazon_product_id' );

			// update min/max prices in listings table
			if ( isset( $_POST['variable_amazon_minimum_price'] ) ) {

				$min_price = isset( $variable_amazon_minimum_price[$i] ) ? $variable_amazon_minimum_price[$i] : '';
				$max_price = isset( $variable_amazon_maximum_price[$i] ) ? $variable_amazon_maximum_price[$i] : '';
				$data      = array();

				if ( $min_price || $max_price ) {

					if ( $listing = $lm->getItemByPostID( $variation_id ) ) {

						if ( $min_price != $listing->min_price ) {
							$data['min_price']  = $min_price;
							$data['pnq_status'] = 1; // mark as changed
						}

						if ( $max_price != $listing->max_price ) {
							$data['max_price']  = $max_price;
							$data['pnq_status'] = 1; // mark as changed
						}

						// update listing
						if ( ! empty($data) ) {
							$lm->updateWhere( array( 'id' => $listing->id ), $data );
						}

					}

				}

			}

            // collect (matched) variations with ASIN
            if ( $variable_amazon_asin[$i] ) {
            	$all_variations_with_ASIN[ $variation_id ] = trim( $variable_amazon_asin[$i] );
            }
            // collect all variations with SKU
            if ( $variable_sku[$i] ) {
            	$all_variations_with_SKU[ $variation_id ] = $variable_sku[$i];
            }

        } // each variation

    	WPLA()->logger->info('Variations with ASIN: '.print_r($all_variations_with_ASIN,1));
    	WPLA()->logger->info('Variations with SKU : '.print_r($all_variations_with_SKU,1));

        // process matched variations
        // check all variations with ASIN and add missing ones to listings table
        if ( ! empty( $all_variations_with_ASIN ) ) {

			$lm = new WPLA_ListingsModel();
			$default_account_id = get_option( 'wpla_default_account_id', 1 );
			if ( ! $default_account_id ) return; // ***

        	foreach ( $all_variations_with_ASIN as $variation_id => $asin ) {

        		// check if this ASIN / ID already exist - skip if it does
		    	WPLA()->logger->info("searching for existing listing for #$variation_id / $asin");
				if ( $lm->getItemByASIN( $asin, false ) ) continue;
				if ( $lm->getItemByPostID( $variation_id ) ) continue;
		    	WPLA()->logger->info("no listing found for variation #$variation_id / $asin");

				// skip hidden variations
				if ( get_post_meta( $variation_id, '_amazon_is_disabled', true ) == 'on' ) continue;

        		// insert matched listing
				$success = $lm->insertMatchedProduct( $variation_id, $asin, $default_account_id );
				$error_msg = isset($lm->lastError) ? $lm->lastError : '';

				if ( $success ) {
					// TODO: use persistent admin message
			    	WPLA()->logger->info("Matched variation #$variation_id / $asin - $error_msg");
				} else {
					echo "Failed to match variation #$variation_id - please report this to support: $error_msg";
			    	WPLA()->logger->error("Failed to match variation #$variation_id / $asin - $error_msg");
				}

        	} // each matched variation
        } // if $all_variations_with_ASIN


        // add missing variations
        // if the parent product has one or more listing items, then check for and add missing variation listings
		$lm = new WPLA_ListingsModel();
		$parent_listings = $lm->getAllItemsByPostID( $post_id );

		if ( ! empty( $parent_listings ) ) {
			foreach ( $parent_listings as $parent_listing ) {

				// get account from parent listing
				$account = WPLA_AmazonAccount::getAccount( $parent_listing->account_id );
				if ( ! $account ) continue;

	        	foreach ( $all_variations_with_SKU as $variation_id => $sku ) {

	        		// check if this SKU / ID already exist - skip if it does
					if ( $lm->getItemBySkuAndAccount( $sku, $parent_listing->account_id, false ) ) continue;
					// if ( $lm->getItemByPostID( $variation_id ) ) continue; // should be obsolete, right?
			    	WPLA()->logger->info("no listing found for missing variation #$variation_id / $sku");

					// check if this variation has a UPC/EAN set - skip if empty (unless brand registry is enabled)
					$_amazon_product_id = get_post_meta( $variation_id, '_amazon_product_id', true );
					if ( ! $_amazon_product_id && ! $account->is_reg_brand ) continue;

					// skip hidden variations
					if ( get_post_meta( $variation_id, '_amazon_is_disabled', true ) == 'on' ) continue;

	        		// insert variation listing
					$success = $lm->insertMissingVariation( $variation_id, $sku, $parent_listing );
					// $error_msg = isset($lm->lastError) ? $lm->lastError : '';

					if ( $success ) {
						// TODO: use persistent admin message
				    	WPLA()->logger->info("Added missing variation #$variation_id / $sku");
					} else {
						echo "Failed to add missing variation #$variation_id - please report this to support!";
				    	WPLA()->logger->error("Failed to add missing variation #$variation_id / $sku");
					}

	        	} // each variation
			} // each parent listing
        } // if parent listing(s) exists


    } // process_product_meta_variable()








	function woocommerce_duplicate_product( $new_id, $post ) {

		// remove amazon specific meta data from duplicated products
		delete_post_meta( $new_id, '_amazon_title' 			);
		delete_post_meta( $new_id, '_amazon_price' 			);
		delete_post_meta( $new_id, '_amazon_minimum_price' 	);
		delete_post_meta( $new_id, '_amazon_maximum_price' 	);
		delete_post_meta( $new_id, '_amazon_is_disabled' 	);
		delete_post_meta( $new_id, '_amazon_product_id' 	);
		delete_post_meta( $new_id, '_amazon_id_type' 		);
		delete_post_meta( $new_id, '_wpla_asin'				);

	} // woocommerce_duplicate_product()


	function enqueueFileTree() {

		// // jqueryFileTree
		wp_register_style('jqueryFileTree_style', WPLA_URL.'/js/jqueryFileTree/jqueryFileTree.css' );
		wp_enqueue_style('jqueryFileTree_style');

		// // jqueryFileTree
		wp_register_script( 'jqueryFileTree', WPLA_URL.'/js/jqueryFileTree/jqueryFileTree.js', array( 'jquery' ) );
		wp_enqueue_script( 'jqueryFileTree' );

		// // mustache template engine
		// wp_register_script( 'mustache', WPLA_URL.'/js/template/mustache.js', array( 'jquery' ) );
		// wp_enqueue_script( 'mustache' );

		// // jQuery UI Autocomplete
		// wp_enqueue_script( 'jquery-ui-button' );
		// wp_enqueue_script( 'jquery-ui-autocomplete' );

	}

} // class WPLA_Product_MetaBox
// $WPLA_Product_MetaBox = new WPLA_Product_MetaBox();
