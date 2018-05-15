/*
 * @package YITH WooCommerce Dynamic Pricing and Discounts Premium
 * @since   1.1.7
 * @author  YITHEMES
 */

jQuery(document).ready( function($) {
	"use strict";

	var $product_id = $('[name|="product_id"]'),
		product_id = $product_id.val(),
		$variation_id = $('[name|="variation_id"]'),
		form = $product_id.closest('form'),
		$table = $('.ywdpd-table-discounts-wrapper');


	$(document).on('found_variation', form, function(event, variation){
		$('.ywdpd-table-discounts-wrapper').replaceWith(variation.table_price);
	});

	if( ! $variation_id.length ){
		return false;
	}

	$variation_id.on('change', function () {
		if( $(this).val() == ''){
			$('.ywdpd-table-discounts-wrapper').replaceWith($table);
		}
	});

});
