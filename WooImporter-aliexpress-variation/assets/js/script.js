/*global wc_add_to_cart_variation_params, wc_cart_fragments_params */
;
(function ($, window, document, undefined) {
	$(function () {
		if (typeof wc_add_to_cart_variation_params !== 'undefined') {

			/**
			 * Matches inline variation objects to chosen attributes
			 * @type {Object}
			 */
			var wpeae_variation_form_matcher = {
				find_matching_variations: function (product_variations, settings) {
					var matching = [];
					for (var i = 0; i < product_variations.length; i++) {
						var variation = product_variations[i];

						if (wpeae_variation_form_matcher.variations_match(variation.attributes, settings)) {
							matching.push(variation);
						}
					}
					return matching;
				},
				variations_match: function (attrs1, attrs2) {
					var match = true;
					for (var attr_name in attrs1) {
						if (attrs1.hasOwnProperty(attr_name)) {
							var val1 = attrs1[ attr_name ];
							var val2 = attrs2[ attr_name ];
							if (val1 !== undefined && val2 !== undefined && val1.length !== 0 && val2.length !== 0 && val1 !== val2) {
								match = false;
							}
						}
					}
					return match;
				}
			};


			$('.variations_form').each(function () {

				var $form = $(this).wc_variation_form();
				var $single_variation = $form.find('.single_variation');
				var $product = $form.closest('.product');
				var $product_id = parseInt($form.data('product_id'), 10);
				var $product_variations = $form.data('product_variations');
				var $use_ajax = $product_variations === false;
				//var $use_ajax = true;
				var $xhr = false;
				var $reset_variations = $form.find('.reset_variations');
				var $single_variation_wrap = $form.find('.single_variation_wrap');

				$form.on('click', '.variations .wpeae_variation_select', function () {
					
					$single_variation.hide();
					
					$form.find('input[name="variation_id"], input.variation_id').val('').change();
					$form.find('.wc-no-matching-variations').remove();

					var is_selected = $(this).hasClass('selected');
					$(this).closest('.wpeae_variation_set').find('.wpeae_variation_select').removeClass('selected');
					if (!is_selected) {
						$(this).addClass('selected');
						$(this).closest('.wpeae_variation_set').find('.wpeae_variation_attribute_val').val($(this).data('attribute_value'));
					} else {
						$(this).closest('.wpeae_variation_set').find('.wpeae_variation_attribute_val').val('');
					}

					if ($use_ajax) {
						if ($xhr) {
							$xhr.abort();
						}

						var total_attr = $form.find('.variations .wpeae_variation_set').length;
						var selected_attr = $form.find('.variations .wpeae_variation_select.selected').length;

						var all_attributes_chosen = total_attr === selected_attr;
						var some_attributes_chosen = selected_attr > 0;
						var data = {};

						$form.find('.variations .wpeae_variation_select.selected').each(function () {
							var attribute_name = $(this).data('attribute_name')/* || $(this).attr('name')*/;
							data[ attribute_name ] = $(this).data('attribute_value');
						});

						if (all_attributes_chosen) {
							// Get a matchihng variation via ajax
							data.product_id = $product_id;
							$xhr = $.ajax({
								url: wc_cart_fragments_params.wc_ajax_url.toString().replace('%%endpoint%%', 'get_variation'),
								type: 'POST',
								data: data,
								success: function (variation) {
									
									if (variation) {
										$form.trigger('found_variation', [variation]);
									} else {
										$form.trigger('reset_data');
										$form.find('.single_variation').after('<p class="wc-no-matching-variations woocommerce-info">' + wc_add_to_cart_variation_params.i18n_no_matching_variations_text + '</p>');
										$form.find('.wc-no-matching-variations').slideDown(200);
									}
									
									$single_variation.show();
								}
							});
						} else {
							$form.trigger('reset_data');
						}
						if (some_attributes_chosen) {
							if ($reset_variations.css('visibility') === 'hidden') {
								$reset_variations.css('visibility', 'visible').hide().fadeIn();
							}
						} else {
							$reset_variations.css('visibility', 'hidden');
						}

					} else {
						$form.trigger('woocommerce_variation_select_change');
						$form.trigger('wpeae_check_variations', ['', false]);
						$(this).blur();
					}

					// added to get around variation image flicker issue
					$('.product.has-default-attributes > .images').fadeTo(200, 1);

					// Custom event for when variation selection has been changed
					$form.trigger('woocommerce_variation_has_changed');

					return false;
				});

				$form.on('wpeae_check_variations', function (event, exclude, focus) {
					if ($use_ajax) {
						return;
					}

					var $form = $(this);
					var $reset_variations = $form.find('.reset_variations');

					var total_attr = $form.find('.variations .wpeae_variation_set').length;
					var selected_attr = $form.find('.variations .wpeae_variation_select.selected').length;

					var all_attributes_chosen = total_attr === selected_attr;
					var some_attributes_chosen = selected_attr > 0;
					var current_settings = {};

					$form.find('.variations .wpeae_variation_select.selected').each(function () {
						var attribute_name = $(this).data('attribute_name');

						if (exclude && attribute_name === exclude) {
							all_attributes_chosen = false;
							current_settings[ attribute_name ] = '';
						} else {
							current_settings[ attribute_name ] = ''+$(this).data('attribute_value');
						}
					});

					var matching_variations = wpeae_variation_form_matcher.find_matching_variations($product_variations, current_settings);

					if (all_attributes_chosen) {

						var variation = matching_variations.shift();

						if (variation) {
							$form.trigger('found_variation', [variation]);
						} else {
							// Nothing found - reset fields
							$form.find('.variations .wpeae_variation_select').removeClass('selected');
							$form.find('.variations .wpeae_variation_attribute_val').val('');

							if (!focus) {
								$form.trigger('reset_data');
							}

							window.alert(wc_add_to_cart_variation_params.i18n_no_matching_variations_text);
						}

					} else {

						$form.trigger('update_variation_values', [matching_variations]);

						if (!focus) {
							$form.trigger('reset_data');
						}

						if (!exclude) {
							$single_variation.slideUp(200).trigger('hide_variation');
						}
					}
					if (some_attributes_chosen) {
						if ($reset_variations.css('visibility') === 'hidden') {
							$reset_variations.css('visibility', 'visible').hide().fadeIn();
						}
					} else {
						$reset_variations.css('visibility', 'hidden');
					}
				});

				$form.on('click', '.reset_variations', function (event) {
					event.preventDefault();
					$form.find('.variations .wpeae_variation_select').removeClass('selected');
					$form.find('.variations .wpeae_variation_attribute_val').val('');
					$reset_variations.css('visibility', 'hidden');
				});

				$form.on('wpeae_init', function (event, variation) {
					$form.trigger('reset_data');
					$reset_variations.css('visibility', 'hidden');

					// added to get around variation image flicker issue
					$('.product.has-default-attributes > .images').fadeTo(200, 1);

					// Custom event for when variation selection has been changed
					$form.trigger('woocommerce_variation_has_changed');
                                        
                                        $form.find('.wpeae_variation_attribute_default_val').each(function () {
                                            if ($(this).val()!='') {
                                                $(this).parents('.wpeae_variation_set').find('.wpeae_variation_select[data-attribute_value="'+$(this).val()+'"]').click();
                                            }
					});

				});

				$form.trigger('wpeae_init');
			});
		}
	});

})(jQuery, window, document);


