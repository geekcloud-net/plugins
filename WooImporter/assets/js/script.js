String.prototype.replaceAllTags = function (tag) {
	var div = document.createElement('div');
	div.innerHTML = this;
	var scripts = div.getElementsByTagName(tag);
	var i = scripts.length;
	while (i--) {
		scripts[i].parentNode.removeChild(scripts[i]);
	}
	return div.innerHTML;
}

var SYNCHRONIZE_IMPORT = false;

function buildGoodsId(goods, dlv) {
	return goods.type + dlv + goods.external_id + ((goods.variation_id !== "" && goods.variation_id !== "-") ? dlv + goods.variation_id : "");
}

jQuery(function () {
	window.wpeae_tb_remove = window.tb_remove;

	jQuery(".wpeae-settings-content .account-content a.use_custom_account_param").click(function () {
		jQuery(this).closest('form').find('input[name="account_type"]').remove();
		jQuery(this).closest('form').append('<input type="hidden" name="account_type" value="custom"/>');
		jQuery(this).closest('form').submit();
		return false;
	});

	jQuery(".wpeae-settings-content .account-content a.use_default_account_param").click(function () {
		jQuery(this).closest('form').find('input[name="account_type"]').remove();
		jQuery(this).closest('form').append('<input type="hidden" name="account_type" value="default"/>');
		jQuery(this).closest('form').submit();
		return false;
	});

	jQuery("#wpeae-do-filter").click(function () {
		jQuery("#wpeae-search-form").find("#reset").val("1");
		jQuery("#wpeae-search-form").submit();
		return true;
	});


	jQuery("#wpeae-search-form").submit(function () {
		jQuery("input[name='_wp_http_referer']").attr("disabled", "disabled");
		jQuery("input[name='_wpnonce']").attr("disabled", "disabled");

		jQuery(this).find(":input").filter(function () {
			return !this.value;
		}).attr("disabled", "disabled");
		return true;
	});

	jQuery("#wpeae-search-form #current-page-selector").keypress(function (e) {
		if (e.which == 13) {
			jQuery(this).closest('form').submit();
		}
	});

	jQuery("#wpeae-goods-table").on("click", ".select_image", function () {
		return true;
	});

	jQuery("body").on("click", ".wpeae_select_image img", function () {
		var id = jQuery(this).parent().parent().find('.item_id').val();
		var new_image = jQuery(this).attr('src');

		jQuery(this).parent().parent().find("img.sel").removeClass("sel");
		jQuery(this).addClass("sel");

		jQuery("#wpeae-goods-table").find('tr').each(function () {
			var row_id = jQuery(this).attr('id');
			if (row_id === id) {
				jQuery(this).find('.column-image img').attr('src', new_image);
			}
		});


		var data = {'action': 'wpeae_select_image', 'id': id, 'image': new_image};

		jQuery.post(ajaxurl, data, function (response) {
		});
		return false;
	});
	jQuery("body").on("click", ".show_preview", function () {
		jQuery(this).parents(".edit_description_dlg").find('.description_preview').show();
		jQuery(this).parents(".edit_description_dlg").find('.edit_description').show();
		jQuery(this).parents(".edit_description_dlg").find('.description').hide();
		jQuery(this).parents(".edit_description_dlg").find('.show_preview').hide();
		jQuery(this).parents(".edit_description_dlg").find('.save_description').hide();

		var text = jQuery(this).parents(".edit_description_dlg").find('.description').val();
		jQuery(this).parents(".edit_description_dlg").find('.description_preview').html(text.replaceAllTags('script'));
	});

	jQuery("body").on("click", ".edit_description", function () {
		jQuery(this).parents(".edit_description_dlg").find('.description_preview').hide();
		jQuery(this).parents(".edit_description_dlg").find('.edit_description').hide();
		jQuery(this).parents(".edit_description_dlg").find('.description').show();
		jQuery(this).parents(".edit_description_dlg").find('.show_preview').show();
		jQuery(this).parents(".edit_description_dlg").find('.save_description').show();
	});

	jQuery("#wpeae-goods-table").on("click", ".edit_btn", function () {
		var block = jQuery(this).parents(".block_field");
		var text = jQuery(block).find(".field_text").html();

		jQuery(block).find(".field_edit").val(text);

		jQuery(block).find(".field_text").hide();
		jQuery(block).find(".edit_btn").hide();
		jQuery(block).find(".field_edit").show();
		jQuery(block).find(".save_btn").show();
		jQuery(block).find(".cancel_btn").show();

		return false;
	});
	jQuery("#wpeae-goods-table").on("click", ".save_btn", function () {
		var id = jQuery(this).parents('tr').attr('id');
		var block = jQuery(this).parents(".block_field");

		var field_code = jQuery(block).find(".field_code").val();
		var text = jQuery(block).find(".field_edit").val();

		jQuery(block).find(".field_text").show();
		jQuery(block).find(".edit_btn").show();
		jQuery(block).find(".field_edit").hide();
		jQuery(block).find(".save_btn").hide();
		jQuery(block).find(".cancel_btn").hide();

		jQuery(block).find(".field_text").html(text);

		var data = {'action': 'wpeae_edit_goods', 'id': id,
			'field': (field_code.lastIndexOf('user_', 0) === 0) ? field_code : ('user_' + field_code),
			'value': text};

		jQuery.post(ajaxurl, data, function (response) {
		});

		return false;
	});

	jQuery("#wpeae-goods-table").on("click", ".cancel_btn", function () {
		var block = jQuery(this).parents(".block_field");

		jQuery(block).find(".field_text").show();
		jQuery(block).find(".edit_btn").show();
		jQuery(block).find(".field_edit").hide();
		jQuery(block).find(".save_btn").hide();
		jQuery(block).find(".cancel_btn").hide();
		return false;
	});

	jQuery("#wpeae-goods-table").on("click", ".moredetails", function () {
		var block = jQuery(this).parent();
		var curr_row = jQuery(this).parents("tr");
		var id = jQuery(this).parents("tr").attr('id');

		jQuery(block).html("<i>loading...</i> | ");

		var edit_fields = '';
		jQuery(curr_row).find(".block_field").each(function () {
			var field_code = jQuery(this).find(".field_code").val();
			if (jQuery(this).hasClass('edit')) {
				edit_fields += (edit_fields.length > 0 ? ',' : '') + field_code;
			}
		});

		var data = {'action': 'wpeae_load_details', 'id': id, 'edit_fields': edit_fields};

		jQuery.post(ajaxurl, data, function (response) {
			jQuery(block).html('<i>Details loaded</i> | ');
			//console.log('json: ', response);
			var json = jQuery.parseJSON(response);
			//console.log('json: ', json);

			if (json.state == 'ok') {
				jQuery(curr_row).find("#select-image-dlg-" + buildGoodsId(json.goods, '-')).html(json.images_content);

				if (jQuery(curr_row).find(".seller_url_block").is(':hidden')) {
					jQuery(curr_row).find(".seller_url_block").find('a').attr('href', json.goods.seller_url);
					jQuery(curr_row).find(".seller_url_block").show();
				}

				jQuery(curr_row).find(".block_field").each(function () {
					
					var field_code = '';
					if (jQuery(this).find(".field_code").length > 0){
						field_code = jQuery(this).find(".field_code").val();
						jQuery(this).find('.field_text').html(json.goods[field_code]);
					}
					
					if (jQuery(this).find(".meta_field_code").length > 0){
						field_code = jQuery(this).find(".meta_field_code").val();
						jQuery(this).find('.field_text').html(json.goods.additional_meta[field_code]);
					}
					
					jQuery(this).find('.field_text').show();
					jQuery(this).find('.edit_btn').show();
				});
				//console.log('[' + json.state + ']message: ', json.message);
			} else {
				console.log('[' + json.state + ']message: ', json.message);
			}
		});

		return false;
	});

	jQuery("#wpeae-goods-table").on("click", ".post_import", function () {
		var id = jQuery(this).parents("tr").attr('id');
		var curr_row = jQuery(this).parents("tr");
		var block = jQuery(this).parent();
		jQuery(block).html('<i>Posting...</i> | ');

		var edit_fields = '';
		jQuery(curr_row).find(".block_field").each(function () {
			var field_code = jQuery(this).find(".field_code").val();
			if (jQuery(this).hasClass('edit')) {
				edit_fields += (edit_fields.length > 0 ? ',' : '') + field_code;
			}
		});

		var data = {'action': 'wpeae_import_goods', 'id': id, 'edit_fields': edit_fields};

		jQuery.post(ajaxurl, data, function (response) {
			//console.log('response: ', response);
			var json = jQuery.parseJSON(response);
			//console.log('json: ', json);

			if (json.state === 'error') {
				jQuery(block).html('<i>Posting error</i> | ');
				console.log(json);
			} else {
								if(jQuery.isArray(json.js_hook)) {
									jQuery.each(json.js_hook, function( index, value ) {
										eval(value.name)(value.params);
									});
								}
							
				//jQuery(this).parents("tr").find('input[type=checkbox]').attr('disabled', 'disabled');
				jQuery(block).html('<i>Posted</i>');
				jQuery(block).parents('.row-actions').find('.schedule_import').remove();
				jQuery(block).parents("tr").find('input[type=checkbox]').attr('disabled', 'disabled');

				// update row content
				jQuery(curr_row).find('.load_more_detail').html('<i>Details loaded</i> | ');
				jQuery(curr_row).find("#select-image-dlg-" + buildGoodsId(json.goods, '-')).html(json.images_content);

				if (jQuery(curr_row).find(".seller_url_block").is(':hidden')) {
					jQuery(curr_row).find(".seller_url_block").find('a').attr('href', json.goods.seller_url);
					jQuery(curr_row).find(".seller_url_block").show();
				}

				jQuery(curr_row).find(".block_field").each(function () {
					var field_code = jQuery(this).find(".field_code").val();
					jQuery(this).find('.field_text').html(json.goods[field_code]);
					jQuery(this).find('.field_text').show();
					jQuery(this).find('.edit_btn').show();
				});

			}
		});

		return false;
	});

        window.wpeae_synchronize_import = true;
	jQuery("#wpeae-goods-table").on("click", "#doaction,#doaction2", function () {
		var check_action = (jQuery(this).attr('id') == 'doaction') ? jQuery('#bulk-action-selector-top').val() : jQuery('#bulk-action-selector-bottom').val();
		jQuery("#wpeae-goods-table .import_process_loader").html("");
		if (check_action == 'import') {
			var num_to_import = jQuery("#wpeae-goods-table input.gi_ckb:checked").length;

			if (num_to_import > 0) {
				jQuery("#wpeae-goods-table .import_process_loader").html("Process import 0 of " + num_to_import + ".");
				var import_cnt = 0;
				var import_error_cnt = 0;
				var import_cnt_total = 0;

				var products_to_import = [];
				jQuery("#wpeae-goods-table input.gi_ckb:checked").each(function () {
					var id = jQuery(this).parents("tr").attr('id');
					var curr_row = jQuery(this).parents("tr");
					var block = jQuery(this).parents("tr").find('.row-actions .import');

					var edit_fields = '';
					jQuery(curr_row).find(".block_field").each(function () {
						var field_code = jQuery(this).find(".field_code").val();
						if (jQuery(this).hasClass('edit')) {
							edit_fields += (edit_fields.length > 0 ? ',' : '') + field_code;
						}
					});

					var data = {'action': 'wpeae_import_goods', 'id': id, 'edit_fields': edit_fields};
                                        
                                        /*
					if(jQuery('#wpeae-search-form input[name="type"]').length>0 && jQuery('#wpeae-search-form input[name="type"]').val()==='aliexpress'){
						SYNCHRONIZE_IMPORT = true;
					}
                                        */
                                       // always synchronize import...
                                       SYNCHRONIZE_IMPORT = true;

					if (SYNCHRONIZE_IMPORT) {
						products_to_import.push(data);
					} else {
						//console.log('process: '+id);
						jQuery.post(ajaxurl, data, function (response) {
							//console.log('response: ', response);
							var json = jQuery.parseJSON(response);
							//console.log('result: ', json);

							if (json.state === 'error') {
								jQuery(block).html('<i>Posting error</i> | ');
								console.log(json);
								import_error_cnt++;
							} else {
																if(jQuery.isArray(json.js_hook)) {
																	jQuery.each(json.js_hook, function( index, value ) {
																		eval(value.name)(value.params);
																	});
																}
								jQuery(block).html('<i>Posted</i>');
								jQuery(block).parents('.row-actions').find('.schedule_import').remove();
								jQuery(block).parents("tr").find('input[type=checkbox]').attr('disabled', 'disabled');
								jQuery(block).parents("tr").find('input[type=checkbox]').removeAttr('checked');

								// update row content
								jQuery(curr_row).find('.load_more_detail').html('<i>Details loaded</i> | ');
								jQuery(curr_row).find("#select-image-dlg-" + buildGoodsId(json.goods, '-')).html(json.images_content);

								if (jQuery(curr_row).find(".seller_url_block").is(':hidden')) {
									jQuery(curr_row).find(".seller_url_block").find('a').attr('href', json.goods.seller_url);
									jQuery(curr_row).find(".seller_url_block").show();
								}

								jQuery(curr_row).find(".block_field").each(function () {
									var field_code = jQuery(this).find(".field_code").val();
									jQuery(this).find('.field_text').html(json.goods[field_code]);
									jQuery(this).find('.field_text').show();
									jQuery(this).find('.edit_btn').show();
								});

								import_cnt++;
							}
							import_cnt_total++;
							jQuery("#wpeae-goods-table .import_process_loader").html("Process import " + import_cnt + " of " + num_to_import + ". Errors: " + import_error_cnt + ".");

							if (import_cnt_total == num_to_import) {
								jQuery("#wpeae-goods-table .import_process_loader").html("Complete! Result imported: " + import_cnt + "; errors: " + import_error_cnt + ".");
							}
						});
					}
				});
				
				if (SYNCHRONIZE_IMPORT) {
					var cur_index = 0;
					wpeae_js_post_to_woocomerce(products_to_import, cur_index, import_cnt, import_error_cnt, import_cnt_total);
				}
			}
		}

		return false;
	});

	function wpeae_js_post_to_woocomerce(products_to_import, cur_index, import_cnt, import_error_cnt, import_cnt_total) {
		if (products_to_import.length > 0 && products_to_import.length > cur_index) {
			//console.log('data(' + products_to_import[cur_index]['id'] + '): ', products_to_import[cur_index]);
                        
                    var curr_row = jQuery('input[value="' + products_to_import[cur_index]['id'] + '"]').parents("tr");
                    var block = jQuery(curr_row).find('.row-actions .import');
                    var num_to_import = products_to_import.length;
                        
                    jQuery.post(ajaxurl, products_to_import[cur_index])
                        .done(function (response) {
                            //console.log('response: ', response);
                            var json = jQuery.parseJSON(response);
                            //console.log('result: ', json);

                            if (json.state === 'error') {
				jQuery(block).html('<i>Posting error</i> | ');
				console.log(json);
				import_error_cnt++;
                            } else {
                                if(jQuery.isArray(json.js_hook)) {
                                    jQuery.each(json.js_hook, function( index, value ) {
                                        eval(value.name)(value.params);
                                    });
                                }
                                jQuery(block).html('<i>Posted</i>');
                                jQuery(block).parents('.row-actions').find('.schedule_import').remove();
                                jQuery(block).parents("tr").find('input[type=checkbox]').attr('disabled', 'disabled');
                                jQuery(block).parents("tr").find('input[type=checkbox]').removeAttr('checked');

                                // update row content
                                jQuery(curr_row).find('.load_more_detail').html('<i>Details loaded</i> | ');
                                jQuery(curr_row).find("#select-image-dlg-" + buildGoodsId(json.goods, '-')).html(json.images_content);

                                if (jQuery(curr_row).find(".seller_url_block").is(':hidden')) {
                                    jQuery(curr_row).find(".seller_url_block").find('a').attr('href', json.goods.seller_url);
                                    jQuery(curr_row).find(".seller_url_block").show();
                                }

                                jQuery(curr_row).find(".block_field").each(function () {
                                    var field_code = jQuery(this).find(".field_code").val();
                                    jQuery(this).find('.field_text').html(json.goods[field_code]);
                                    jQuery(this).find('.field_text').show();
                                    jQuery(this).find('.edit_btn').show();
                                });

                                import_cnt++;
                            }
                            import_cnt_total++;
                            jQuery("#wpeae-goods-table .import_process_loader").html("Process import " + import_cnt + " of " + num_to_import + ". Errors: " + import_error_cnt + ".");

                            if (import_cnt_total == num_to_import) {
                                    jQuery("#wpeae-goods-table .import_process_loader").html("Complete! Result imported: " + import_cnt + "; errors: " + import_error_cnt + ".");
                            }

                            wpeae_js_post_to_woocomerce(products_to_import, cur_index + 1, import_cnt, import_error_cnt, import_cnt_total);
                        })
                        .fail(function (xhr, status, error) {
                            jQuery(block).html('<i>Posting error</i> | ');
                            import_error_cnt++;
                            import_cnt_total++;
                            
                            if (import_cnt_total == num_to_import) {
                                jQuery("#wpeae-goods-table .import_process_loader").html("Complete! Result imported: " + import_cnt + "; errors: " + import_error_cnt + ".");
                            }
                            
                            wpeae_js_post_to_woocomerce(products_to_import, cur_index + 1, import_cnt, import_error_cnt, import_cnt_total);
                        });
		}
	}

	jQuery(".schedule_post_date").xdsoft_datetimepicker({
		format: 'm/d/Y H:i',
		step: 10,
		onSelectTime: function (dateText, input) {
			var id = jQuery(input).parents("tr").attr('id');
			var block = jQuery(input).parent();

			jQuery(block).html("<i>Process...</i>");

			var data = {'action': 'wpeae_schedule_import_goods', 'id': id, 'time': jQuery(input).val()};

			jQuery.post(ajaxurl, data, function (response) {
				var json = jQuery.parseJSON(response);
				if (json.state == 'error') {
					jQuery(block).html("<i>Schedule post error</i>");
				} else {
					jQuery(block).html("<i>Will be post on " + json.time + "</i>");
				}
			});


		}
	});

	jQuery("#wpeae-goods-table").on("click", ".schedule_post_import", function () {
		jQuery(this).prev().xdsoft_datetimepicker('show');
		return false;
	});

	jQuery(".upload_image").click(function () {
		jQuery("#upload_product_id").val(jQuery(this).parents('tr').attr('id'));
		return true;
	});



	jQuery(".edit_desc_action").click(function () {
		var id = jQuery(this).parents("tr").attr('id');

		jQuery('#edit_desc_dlg').empty();
		jQuery('#edit_desc_dlg').append('<div><h2>Edit description</h2><div id="edit_desc_content">Loading...</div></div>');

		var data = {'action': 'wpeae_description_editor', 'id': id};
		jQuery.post(ajaxurl, data, function (response) {
			//console.log('response: ', response);
			jQuery('body').find('#edit_desc_content').html(response);
		});

		return true;
	});

	function get_tinymce_content(id) {
		var content;
		var inputid = id;
		var editor = tinyMCE.get(inputid);
		var textArea = jQuery('textarea#' + inputid);
		if (textArea.length > 0 && textArea.is(':visible')) {
			content = textArea.val();
		} else {
			content = editor.getContent();
		}
		return content;
	}

	jQuery("body").on("click", ".save_description", function () {
		var save_btn = this;
		jQuery(save_btn).val('Saving...');
		jQuery(save_btn).prop('disabled', true);

		var id = jQuery(this).parent().find('.item_id').val();
		var editor_id = jQuery(this).parent().find('.editor_id').val();
		var data = {'action': 'wpeae_edit_goods', 'id': id, 'field': 'user_description', 'value': get_tinymce_content(editor_id)/*jQuery(this).parent().find('textarea').val()*/};
		jQuery.post(ajaxurl, data, function (response) {
			jQuery(save_btn).val('Save description');
			jQuery(save_btn).prop('disabled', false);
			wpeae_tb_remove();
		});
	});

	if (!!jQuery.prototype.ajaxForm) {
		var options = {target: '', beforeSubmit: showRequest, success: showResponse, url: ajaxurl};
		jQuery('#image_upload_form').ajaxForm(options);
	} else {
		console.log('Warnign! ajaxForm is not suported by your theme');
	}

	jQuery("#image_upload_form").on("change", "#upload_image", function () {
		jQuery("#image_upload_form").find('#upload_progress').html('');
	});

	jQuery("#wpeae_add_formula").click(function () {
		var this_row = jQuery(this).parents('tr');
		
		var data = {'action': 'wpeae_price_formula_add',
			'type': jQuery('#wpeae_price_formula_add_form [name="type"]').val(),
			'type_name': jQuery('#wpeae_price_formula_add_form [name="type"] option:selected').text(),
			'category': jQuery('#wpeae_price_formula_add_form [name="category"]').val(),
			'category_name': jQuery('#wpeae_price_formula_add_form [name="category"] option:selected').text(),
			'min_price': jQuery('#wpeae_price_formula_add_form [name="min_price"]').val(),
			'max_price': jQuery('#wpeae_price_formula_add_form [name="max_price"]').val(),
			'sign': jQuery('#wpeae_price_formula_add_form [name="sign"]').val(),
			'value': jQuery('#wpeae_price_formula_add_form [name="value"]').val(),
			'discount1': jQuery('#wpeae_price_formula_add_form [name="discount1"]').val(),
			'discount2': jQuery('#wpeae_price_formula_add_form [name="discount2"]').val()};
		//console.log('data: ', data);
		
		if(isNaN(parseFloat(data.value))){
			alert(WPURLS.lang.value_is_required);
			return false;
		}
		
		if(isNaN(parseFloat(data.min_price))){
			data.min_price = 0;
		}
		
		if(isNaN(parseFloat(data.max_price))){
			data.max_price = 0;
		}
		
		if(data.min_price<0.001 && data.max_price<0.001){
			alert(WPURLS.lang.min_price_or_max_price_is_required);
			return false;
		}

		jQuery('#wpeae_price_formula_add_form input').val('');
		jQuery("#wpeae_price_formula_add_form select").prop("selectedIndex", 0);

		jQuery(this_row).find('.button-primary').hide();
		var loaderContainer = jQuery( '<span/>', {'class': 'loader-image-container'}).insertAfter( this );
		jQuery( '<img/>', {src: WPURLS.siteurl + '/wp-admin/images/loading.gif','class': 'loader-image'}).appendTo( loaderContainer );
		

		jQuery.post(ajaxurl, data, function (response) {
			//console.log('response: ', response);
			var json = jQuery.parseJSON(response);
			var discount_html = "source %";
			if(json.formula.discount1 && json.formula.discount2){
				if(parseInt(json.formula.discount1)>parseInt(json.formula.discount2)){
					discount_html = json.formula.discount2+"% &mdash; "+json.formula.discount1+"%";
				} else{
					discount_html = json.formula.discount1+"% &mdash; "+json.formula.discount2+"%";
				}
			}else if(json.formula.discount1 || json.formula.discount2){
				discount_html = (json.formula.discount1?json.formula.discount1:json.formula.discount2)+"%";
			}
			jQuery('#wpeae_price_formula').append('<tr formula-id="' + json.formula.id + '"><td>' + json.formula.pos + '</td><td>' + json.formula.type_name + '</td><td>' + json.formula.category_name + '</td><td>' + json.formula.min_price + ' < PRICE < ' + json.formula.max_price + '</td><td>' + ((json.formula.sign == "=") ? (json.formula.value) : ("PRICE " + json.formula.sign + " " + json.formula.value)) + '</td><td>'+discount_html+'</td><td><button class="button-primary wpeae_edit_formula">Edit</button> <button class="button-primary wpeae_del_formula">Delete</button></td></tr>');

			jQuery('#wpeae_price_formula_add_form button').removeAttr('disabled');
			
			jQuery(this_row).find('.button-primary').show();
			loaderContainer.remove();
		});

		return false;
	});

	jQuery("#wpeae_price_formula").on("click", ".wpeae_edit_formula", function () {
		var this_row = jQuery(this).parents('tr');
		var data = {'action': 'wpeae_price_formula_get','id': jQuery(this_row).attr('formula-id')};
		
		jQuery(this_row).find('.button-primary').hide();
		var loaderContainer = jQuery( '<span/>', {'class': 'loader-image-container'}).insertAfter( this );
		jQuery( '<img/>', {src: WPURLS.siteurl + '/wp-admin/images/loading.gif','class': 'loader-image'}).appendTo( loaderContainer );
		
		jQuery.post(ajaxurl, data, function (response) {
			//console.log('response: ', response);
			var json = jQuery.parseJSON(response);
			console.log('response: ', json);
			
			var html = '<tr>';
			
			html += '<td><input type="text" name="pos" value="'+json.formula.pos+'"/></td>';
			
			html += '<td><select name="type">';
			html += '<option value="">Any module</option>';
			jQuery.each( json.api_list, function( key, value ) {
				if(json.formula.type == value.id){
					html += '<option value="'+value.id+'" selected>'+value.name+'</option>';
				}else{
					html += '<option value="'+value.id+'">'+value.name+'</option>';
				}
				
			});
			html += '</select></td>';
			
			html += '<td><select name="category">';
			html += '<option value="">Any category</option>';
			jQuery.each( json.categories_tree, function( key, value ) {
				if(json.formula.category == value.id){
					html += '<option value="'+value.id+'" selected>'+value.name+'</option>';
				}else{
					html += '<option value="'+value.id+'">'+value.name+'</option>';
				}
			});
			html += '</select></td>';
			
			html += '<td class="price"><table><tr><td style="padding:0;vertical-align:middle;width:33%;"><input type="text" name="min_price" value="'+json.formula.min_price+'"/></td><td style="padding:0;vertical-align:middle;text-align: center;">< PRICE <</td><td style="padding:0;vertical-align:middle;width: 33%;"><input type="text" name="max_price" value="'+json.formula.max_price+'"/></td></tr></table></td>';
			
			html += '<td><table><tr>';
			html += '<td style="padding:0;width:45%;"><select name="sign">';
			jQuery.each( json.sign_list, function( key, value ) {
				if(json.formula.sign == value.id){
					html += '<option value="'+value.id+'" selected>'+value.name+'</option>';
				}else{
					html += '<option value="'+value.id+'">'+value.name+'</option>';
				}
			});
			html += '</select></td>';
			html += '<td style="padding:0;width:55%;"><input type="text" name="value" value="'+json.formula.value+'"/></td>';
			html += '</tr></table></td>';
			
			html += '<td><table><tr>';
			html += '<td style="padding:0;width:45%;"><select name="discount1">';
			jQuery.each( json.discount_list, function( key, value ) {
				if(json.formula.discount1 == value.id){
					html += '<option value="'+value.id+'" selected>'+value.name+'</option>';
				}else{
					html += '<option value="'+value.id+'">'+value.name+'</option>';
				}
			});
			html += '</select></td>';
			html += '<td style="padding:0;width:45%;"><select name="discount2">';
			jQuery.each( json.discount_list, function( key, value ) {
				if(json.formula.discount2 == value.id){
					html += '<option value="'+value.id+'" selected>'+value.name+'</option>';
				}else{
					html += '<option value="'+value.id+'">'+value.name+'</option>';
				}
			});
			html += '</select></td>';
			html += '</tr></table></td>';
			
			html += '<td class="action"><a class="button-primary wpeae_save_formula">Save</a> <a class="button-primary wpeae_cancel_edit_formula">Cancel</a></td>';
			
			html += '</tr>';
			
			jQuery(this_row).after(html);
			jQuery(this_row).hide();
			
			jQuery(this_row).find('.button-primary').show();
			loaderContainer.remove();
		});
		
		return false;
	});
	
	jQuery("#wpeae_price_formula").on("click", ".wpeae_save_formula", function () {
		var this_row = jQuery(this).parents('tr');
		var data = {'action': 'wpeae_price_formula_edit',
			'id': jQuery(this_row).prev().attr('formula-id'),
			'pos': jQuery(this_row).find('[name="pos"]').val(),
			'type': jQuery(this_row).find('[name="type"]').val(),
			'type_name': jQuery(this_row).find('[name="type"] option:selected').text(),
			'category': jQuery(this_row).find('[name="category"]').val(),
			'category_name': jQuery(this_row).find('[name="category"] option:selected').text(),
			'min_price': jQuery(this_row).find('[name="min_price"]').val(),
			'max_price': jQuery(this_row).find('[name="max_price"]').val(),
			'sign': jQuery(this_row).find('[name="sign"]').val(),
			'value': jQuery(this_row).find('[name="value"]').val(),
			'discount1': jQuery(this_row).find('[name="discount1"]').val(),
			'discount2': jQuery(this_row).find('[name="discount2"]').val()};
		
		jQuery(this_row).find('.button-primary').hide();
		var loaderContainer = jQuery( '<span/>', {'class': 'loader-image-container'}).insertAfter( this );
		jQuery( '<img/>', {src: WPURLS.siteurl + '/wp-admin/images/loading.gif','class': 'loader-image'}).appendTo( loaderContainer );
		
		jQuery.post(ajaxurl, data, function (response) {
			//console.log('response: ', response);
			var json = jQuery.parseJSON(response);
			console.log('json: ', json);
			
			var discount_html = "source %";
			if(json.formula.discount1 && json.formula.discount2){
				if(parseInt(json.formula.discount1)>parseInt(json.formula.discount2)){
					discount_html = json.formula.discount2+"% &mdash; "+json.formula.discount1+"%";
				} else{
					discount_html = json.formula.discount1+"% &mdash; "+json.formula.discount2+"%";
				}
			}else if(json.formula.discount1 || json.formula.discount2){
				discount_html = (json.formula.discount1?json.formula.discount1:json.formula.discount2)+"%";
			}
			jQuery(this_row).prev().html('<td>' + json.formula.pos + '</td><td>' + json.formula.type_name + '</td><td>' + json.formula.category_name + '</td><td>' + json.formula.min_price + ' < PRICE < ' + json.formula.max_price + '</td><td>' + ((json.formula.sign == "=") ? (json.formula.value) : ("PRICE " + json.formula.sign + " " + json.formula.value)) + '</td><td>'+discount_html+'</td><td><button class="button-primary wpeae_edit_formula">Edit</button> <button class="button-primary wpeae_del_formula">Delete</button></td>');
			
			jQuery(this_row).prev().show();
			jQuery(this_row).remove();
			
			jQuery(this_row).find('.button-primary').show();
			loaderContainer.remove();
		});
	});
	
	jQuery("#wpeae_price_formula").on("click", ".wpeae_cancel_edit_formula", function () {
		var this_row = jQuery(this).parents('tr');
		jQuery(this_row).prev().show();
		jQuery(this_row).remove();
	});

	jQuery("#wpeae_price_formula").on("click", ".wpeae_del_formula", function () {
		var this_row = jQuery(this).closest('tr');
		var data = {'action': 'wpeae_price_formula_del', 'id': jQuery(this_row).attr('formula-id')};
		
		jQuery(this_row).find('.button-primary').hide();
		var loaderContainer = jQuery( '<span/>', {'class': 'loader-image-container'}).insertAfter( this );
		jQuery( '<img/>', {src: WPURLS.siteurl + '/wp-admin/images/loading.gif','class': 'loader-image'}).appendTo( loaderContainer );

		jQuery.post(ajaxurl, data, function (response) {
			//console.log('response: ', response);

			var find = false;
			jQuery('#wpeae_price_formula tr').each(function (index, el) {
				if (find) {
					jQuery(el).find('td').first().html(index - 1);
				}
				if (jQuery(el).attr('formula-id') == jQuery(this_row).attr('formula-id')) {
					find = true;
				}
			});

			loaderContainer.remove();
			jQuery(this_row).remove();
		});

		return false;
	});

	jQuery(".wpeae_save_formula").click(function () {
		//console.log('save ' + jQuery(this).closest('tr').attr('formula-id'));
		return false;
	});
	jQuery(".wpeae_cancel_formula").click(function () {
		//console.log('cancel ' + jQuery(this).closest('tr').attr('formula-id'));
		return false;
	});
});

function showRequest(formData, jqForm, options) {
	if (jQuery(jqForm).find("#upload_image").val() !== '') {
		jQuery(jqForm).find('#upload_progress').html('Sending...');
		jQuery(jqForm).find('input[name="submit-ajax"]').attr("disabled", "disabled");
		return true;
	} else {
		jQuery(jqForm).find('#upload_progress').html('<font color="red">Please select a file first</font>');
		jQuery(jqForm).find('input[name="submit-ajax"]').removeAttr("disabled");
		return false;
	}

}
function showResponse(responseText, statusText, xhr, $form) {
	var json = jQuery.parseJSON(responseText);
	if (json.state == 'ok') {
		jQuery("#wpeae-goods-table").find('tr').each(function () {
			var row_id = jQuery(this).attr('id');
			if (row_id === buildGoodsId(json.goods, '#')) {
				jQuery(this).find('.column-image img').attr('src', json.cur_image);
			}
		});

		jQuery("#select-image-dlg-" + buildGoodsId(json.goods, '-')).html(json.images_content);
	} else {
		console.log(json.state + "; " + json.message);
	}


	jQuery($form).find('input[name="submit-ajax"]').removeAttr("disabled");
	jQuery($form).find('#upload_image').val('');
	jQuery($form).find('#upload_product_id').val('');
	jQuery($form).find('#upload_progress').html('');

	wpeae_tb_remove();
}