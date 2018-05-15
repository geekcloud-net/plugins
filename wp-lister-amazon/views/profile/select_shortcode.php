<?php

	$buildin_shortcodes = array(
		'[product_title]'          => 'Product title',
		'[product_sku]'            => 'SKU',
		'[product_price]'          => 'Price',
		'[product_sale_price]'     => 'Sale price',
		'[product_sale_start]'     => 'Sale start date',
		'[product_sale_end]'       => 'Sale end date',
		'[product_msrp]'           => 'MSRP price',
		// '[product_quantity]'       => 'Quantity',
		'[product_content]'        => 'Product description',
		'[product_excerpt]'        => 'Product short description',
		'[product_length]'         => 'Product length',
		'[product_width]'          => 'Product width',
		'[product_height]'         => 'Product height',
		'[product_weight]'         => 'Product weight',
		'[amazon_product_id]'      => 'Amazon product ID',
		'[meta_YOUR-CUSTOM-FIELD]' => '-- custom meta field --',
		'[---]'                    => '-- leave empty --',
	);

	// handle custom shortcodes registered by wpla_register_profile_shortcode()
	foreach (WPLA()->getShortcodes() as $key => $custom_shortcode) {
		$buildin_shortcodes[ "[$key]" ] = $custom_shortcode['title'];
	}

	// handle custom variation meta fields
	$variation_meta_fields = get_option('wpla_variation_meta_fields', array() );
	foreach ( $variation_meta_fields as $key => $varmeta ) {
		$key = 'meta_'.$key;
		$buildin_shortcodes[ "[$key]" ] = $varmeta['label'];
	}

	// $product_attributes = WPLA_ProductWrapper::getAttributeTaxonomies();
	// echo "<pre>";print_r($product_attributes);echo"</pre>";#die();

?>

	<div id="wpla_shortcode_selection_container">
		
		<p>
			Click on a shortcode and it will be inserted to the selected field.
		</p>

		<table style="width:100%;">
			<tr>
				<th>Product properties</th>
				<th>Product attributes</th>
			</tr>
			<tr>
				<td style="width:50%; vertical-align:top;">
					<?php foreach ( $buildin_shortcodes as $shortcode => $title ) : ?>
				
						<a href="#" onclick="wpla_insert_shortcode('<?php echo $shortcode ?>');return false;"><?php echo $title ?></a>

					<?php endforeach; ?>
				</td>
				<td style="width:50%; vertical-align:top;">
					<?php foreach ( $wpl_product_attributes as $attribute ) : ?>
				
						<a href="#" onclick="wpla_insert_shortcode('[<?php echo str_replace('pa_','attribute_',$attribute->name) ?>]');return false;"><?php echo $attribute->label ?></a> 

					<?php endforeach; ?>
				</td>
			</tr>
		</table>

	</div>


<style type="text/css">

	#wpla_shortcode_selection_container a,
	#wpla_shortcode_selection_container a:visited,
	#wpla_shortcode_selection_container a:hover {
		display: block;
		width: 90%;
		padding: 0.5em 1em;
		background-color: #ddd;
		color: #000;
		margin-bottom: 0.5em;
		text-decoration: none;
	}

	#wpla_shortcode_selection_container a:hover {
		background-color: #eee;
	}

	#wpla_shortcode_selection_wrapper_INACTIVE {
		/*max-height: 320px;*/
		/*margin-left: 35%;*/
		overflow: auto;
		width: 65%;
		display: none;
	}

</style>
