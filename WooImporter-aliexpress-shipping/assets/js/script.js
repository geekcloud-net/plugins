(function ($) {
	$(function () {
		
		jQuery("#wpeae-import-free").click(function () {
			$('.import_process_loader').html(wpeae_ali_ship_script.lang.search_for_free_shipping_items);
			wpeae_aliship_import_ids(1, 'free');
		});

		jQuery("#wpeae-import-epacket").click(function () {
			$('.import_process_loader').html(wpeae_ali_ship_script.lang.search_for_epacket_items);
			wpeae_aliship_import_ids(1, 'ePacket');
		});

	

		function wpeae_aliship_import_ids(page, type, count) {
            
            count = typeof count == 'undefined' ? 0 : count;
            limit = 5;
            if(count === limit) return;
    
			var data = {'action': 'wpeae_aliship_get_products_by_filter', 'filter': $("#wpeae-search-form").serialize(), 'page': page};
			jQuery.post(ajaxurl, data, function (response) {
				//console.log('response: ', response);
				var json = jQuery.parseJSON(response);
				//console.log('json: ', json);

				if ((json.pages - json.pages_loaded) > 0) {
					$('.import_process_loader').html(wpeae_ali_ship_script.lang.build_product_list_to_import + ' ' + Math.round(json.pages_loaded * 100 / json.pages) + '%');
					wpeae_aliship_import_ids(json.pages_loaded + 1, type);
				} else {
					$('.import_process_loader').html(wpeae_ali_ship_script.lang.build_product_list_to_import_100);
					var data = {'action': 'wpeae_aliship_get_ids'};
					$.post(ajaxurl, data, function (ids_response) {
						//console.log('ids_response: ', ids_response);
						var ids_json = jQuery.parseJSON(ids_response);
						//console.log('json: ', ids_json);

						// run import load...
						var ids = ids_json.ids;
						var num_to_import = ids.length;
						var import_cnt = 0;
						var import_error_cnt = 0;
						var import_skip_cnt = 0;
						var import_cnt_total = 0;
						jQuery(".import_process_loader").html("Process import " + import_cnt + " of " + num_to_import + ". Errors: " + import_error_cnt + ". Skiped: " + import_skip_cnt + ".");


						var products_to_import = [];
						jQuery.each(ids, function (index, value) {
							var id_part = value.split('#');
							var id = jQuery('#wpeae-search-form input[name="type"]').val() + '#' + id_part[0];
							//console.log('id: ', id);
							var data = {'action': 'wpeae_aliship_load_and_import_goods', 'id': id, 'link_category_id': id_part[1], 'shipping_filter':type};
							products_to_import.push(data);
						});

						var cur_index = 0;
						wpeae_csv_js_post_to_woocomerce(products_to_import, cur_index, import_cnt, import_error_cnt, import_skip_cnt, import_cnt_total);

					});
				}
			}).fail(function(jqXHR, textStatus, errorThrown) {
                    count += 1;
                    console.log("Trying to repeat last request");
                    setTimeout(function() {
                        wpeae_aliship_import_ids(page, type, count);
                    }, 500*count);
                });
		}

		function wpeae_csv_js_post_to_woocomerce(products_to_import, cur_index, import_cnt, import_error_cnt, import_skip_cnt, import_cnt_total) {
			if (products_to_import.length > 0 && products_to_import.length > cur_index) {
				//console.log('data(' + products_to_import[cur_index]['id'] + '): ', products_to_import[cur_index]);
				jQuery.post(ajaxurl, products_to_import[cur_index], function (response) {
					var num_to_import = products_to_import.length;
					//console.log('response: ', response);
					var json = jQuery.parseJSON(response);
					//console.log('result: ', json);

					import_cnt_total++;

					if (json.state === 'error') {
						import_error_cnt++;
						console.log('result: ', json);
					}
					else if (json.state === 'skip') {
						import_skip_cnt++;
						console.log('result: ', json);
					} 
					else {
                        if(jQuery.isArray(json.js_hook)) {
                            jQuery.each(json.js_hook, function( index, value ) {
                                eval(value.name)(value.params);
                            });
                        }
						import_cnt++;
					}

					jQuery(".import_process_loader").html("Process import " + import_cnt + " of " + num_to_import + ". Errors: " + import_error_cnt + ". Skiped: " + import_skip_cnt +".");

					if (import_cnt_total === num_to_import) {
						jQuery(".import_process_loader").html("Complete! Result imported: " + import_cnt + "; errors: " + import_error_cnt + "; skiped: " + import_skip_cnt + ".");
					}
					wpeae_csv_js_post_to_woocomerce(products_to_import, cur_index + 1, import_cnt, import_error_cnt, import_skip_cnt, import_cnt_total);
				});
			}
		}


	});
})(jQuery);