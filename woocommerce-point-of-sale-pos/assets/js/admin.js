jQuery(document).ready(function ($) {
    $('input#wc_pos_autoupdate_stock').change(function () {
        if ($(this).is(':checked')) {
            $('#wc_pos_autoupdate_interval').closest('tr').show();
        } else {
            $('#wc_pos_autoupdate_interval').closest('tr').hide();
        }
    }).change();
    $('input#wc_pos_day_end_report').change(function () {
        if ($(this).is(':checked')) {
            $('#wc_pos_day_end_emails').closest('tr').show();
        } else {
            $('#wc_pos_day_end_emails').closest('tr').hide();
        }
    }).change();
    $('input#wc_pos_rounding').change(function () {
        if ($(this).is(':checked')) {
            $('#wc_pos_rounding_value').closest('tr').show();
        } else {
            $('#wc_pos_rounding_value').closest('tr').hide();
        }
    }).change();
    $('input#woocommerce_pos_register_instant_quantity').change(function () {
        if ($(this).is(':checked')) {
            $('#woocommerce_pos_register_instant_quantity_keypad').closest('tr').show();
        } else {
            $('#woocommerce_pos_register_instant_quantity_keypad').closest('tr').hide();
        }
    }).change();
    $('input#wc_pos_decimal_quantity').change(function () {
        if ($(this).is(':checked')) {
            $('#wc_pos_decimal_quantity_value').closest('tr').show();
        } else {
            $('#wc_pos_decimal_quantity_value').closest('tr').hide();
        }
    }).change();
    $('input#woocommerce_pos_register_ready_to_scan').change(function () {
        if ($(this).is(':checked')) {
            $('#woocommerce_pos_register_scan_field').closest('tr').show();
        } else {
            $('#woocommerce_pos_register_scan_field').closest('tr').hide();
        }
    }).change();
    $('input#wc_pos_lock_screen').change(function () {
        if ($(this).is(':checked')) {
            $('#wc_pos_unlock_pass').closest('tr').show();
        } else {
            $('#wc_pos_unlock_pass').closest('tr').hide();
        }
    }).change();

    $('input:radio[value=company_image_text]').change(function () {
        if ($(this).is(':checked')) {
            $('#woocommerce_pos_company_logo_hidden').closest('tr').show();
        } else {
            $('#woocommerce_pos_company_logo_hidden').closest('tr').hide();
        }
    }).change();


    // Sorting
    if (jQuery('table.wc_gateways tbody').length) {
        jQuery('table.wc_gateways tbody').sortable({
            items: 'tr',
            cursor: 'move',
            axis: 'y',
            handle: 'td.sort',
            scrollSensitivity: 40,
            helper: function (event, ui) {
                ui.children().each(function () {
                    jQuery(this).width(jQuery(this).width());
                });
                ui.css('left', '0');
                return ui;
            },
            start: function (event, ui) {
                ui.item.css('background-color', '#f6f6f6');
            },
            stop: function (event, ui) {
                ui.item.removeAttr('style');
            }
        });

    }
// Tooltips
    var tiptip_args = {
        'attribute': 'data-tip',
        'fadeIn': 50,
        'fadeOut': 50,
        'delay': 200
    };
    $('.tips, .help_tip, .woocommerce-help-tip').tipTip(tiptip_args);
    if ($('input[name="products_or_cat"]').length > 0) {
        $('input[name="products_or_cat"]').change(function () {
            var type = $('input[name="products_or_cat"]:checked').val();
            if (type == 'category') {
                $('#products_opt_wrap').hide();
                $('#category_opt_wrap').show();
            } else {
                $('#products_opt_wrap').show();
                $('#category_opt_wrap').hide();
            }
        }).trigger('change');
        if ($().select2) {
            $('.category_chosen').css('width', '400px').select2();
        } else {
            $('.category_chosen').css('width', '400px').chosen();
        }

    }
    $('.close_popup, .back_to_sale').on('click', function () {
        $('.overlay_order_popup').hide();
    });
    if ($().select2) {
        if (wc_version >= 3) {
            $('#woocommerce_pos_register_discount_presets').select2({
                multiple: true,
                maximumSelectionLength: 4,
                tags: true,
                createTag: function (params) {
                    var term = params.term.replace(',', '.');
                    // Don't offset to create a tag if there is no @ symbol
                    if (isNaN(parseFloat(term))) {
                        // Return null to disable tag creation
                        return null;
                    }

                    return {
                        id: parseFloat(term).toFixed(2),
                        text: parseFloat(term).toFixed(2) + '%'
                    }
                }
            });
        } else {
            $('#woocommerce_pos_register_discount_presets').select2({
                maximumSelectionSize: 4,
                tags: true
            });
        }
        jQuery('select#billing_country').select2();
        jQuery('select#shipping_country').select2();
        if (jQuery('select#billing_state').length > 0) {
            jQuery('select#billing_state').select2();
        }
        if (jQuery('select#shipping_state').length > 0) {
            jQuery('select#shipping_state').select2();
        }

        if ($('#_register_default_customer').length > 0) {
            $.fn.select2.amd.require([
                'select2/data/array',
                'select2/utils'
            ], function (ArrayData, Utils) {
                function CustomData($element, options) {
                    CustomData.__super__.constructor.call(this, $element, options);
                }

                Utils.Extend(CustomData, ArrayData);

                CustomData.prototype.query = function (params, callback) {
                    $.ajax({
                        url: wc_pos_params.ajax_url,
                        dataType: 'json',
                        data: {
                            term: params.term,
                            action: 'wc_pos_json_search_customers',
                            security: wc_pos_params.search_customers
                        },
                        success: function (results) {
                            var data = {
                                results: []
                            };
                            for (var k in results) {
                                var text = jQuery.parseHTML(results[k]);
                                data.results.push({
                                    id: k,
                                    text: text[0].data
                                });
                            }
                            callback(data);
                        }
                    });
                };

                $("#_register_default_customer").select2({
                    dataAdapter: CustomData,
                    minimumInputLength: 3
                });
            })
        }
    } else {
        jQuery('select#billing_country').chosen();
        jQuery('select#shipping_country').chosen();
        if (jQuery('select#billing_state').length > 0) {
            jQuery('select#billing_state').chosen();
        }
        if (jQuery('select#shipping_state').length > 0) {
            jQuery('select#shipping_state').chosen();
        }
        jQuery('select.ajax_chosen_select_customer, #_register_default_customer').ajaxChosen({
            method: 'GET',
            url: wc_pos_params.ajax_url,
            dataType: 'json',
            afterTypeDelay: 100,
            minTermLength: 1,
            data: {
                action: 'wc_pos_json_search_customers',
                security: wc_pos_params.search_customers
            }
        }, function (data) {
            var terms = {};
            $.each(data, function (i, val) {
                terms[i] = val;
            });
            return terms;
        });
    }

    if ($('#woocommerce_pos_tax_calculation').length > 0) {
        $('.disabled_select').attr('disabled', 'disabled');
        $('#woocommerce_pos_tax_calculation').change(function () {
            if ($(this).val() == 'disabled') {
                $('#woocommerce_pos_calculate_tax_based_on').parent().parent().hide();
            } else {
                $('#woocommerce_pos_calculate_tax_based_on').parent().parent().show();
            }
        }).change();
    }

    if ($('#woocommerce_pos_register_layout_text').length > 0) {

        $('.pos_register_layout_opt').change(function () {
            var val = $('.pos_register_layout_opt:checked').val();
            if (val == 'text' || val == 'company_image_text') {
                $('#woocommerce_pos_register_layout_text').parents('tr').show();
            } else {
                $('#woocommerce_pos_register_layout_text').parents('tr').hide();
            }

        }).first().change();
        // Uploading files
        var file_frame;
        var current_shape_image;
        $('#woocommerce_pos_company_logo').click(function () {

            // If the media frame already exists, reopen it.
            if (file_frame) {
                file_frame.open();
                return;
            }

            // Create the media frame.
            file_frame = wp.media.frames.file_frame = wp.media({
                title: "Select a Company Logo", // $(this).data('uploader_title'),
                button: {
                    text: "Set Company Logo", //$(this).data('uploader_button_text'),
                },
                multiple: false,
            });

            // When an image is selected, run a callback.
            file_frame.on('select', function () {
                // We set multiple to false so only get one image from the uploader
                attachment = file_frame.state().get('selection').first().toJSON();

                // Set the image id/display the image thumbnail
                $('#woocommerce_pos_company_logo_hidden').val(attachment.id);
                $('#woocommerce_pos_company_logo').val("Change");

                $('#woocommerce_pos_company_logo_img').attr('src', attachment.sizes.thumbnail.url);  // TODO: will the thumbnail always be available?
                $('#woocommerce_pos_company_logo_img').show();
            });

            // Finally, open the modal
            file_frame.open();
        });
    }
    $('#add_wc_pos_outlets').submit(function () {
        $('.form-invalid').removeClass('form-invalid');
        var err = 0;
        if ($('#_outlet_name').val() == '') {
            $('#_outlet_name').parent().addClass('form-invalid');
            err++;
        }
        if ($('#_outlet_email').val() != '' && !checkEmail($('#_outlet_email').val())) {
            $('#_outlet_email').parent().addClass('form-invalid');
            err++;
        }
        if ($('#_outlet_phone').val() != '' && !checkPhone($('#_outlet_phone').val())) {
            $('#_outlet_phone').parent().addClass('form-invalid');
            err++;
        }
        if (err) {
            window.scrollTo(0, parseInt($('.form-invalid').first().offset().top) - 100);
            return false;
        }
    });
    if ($().select2) {
        $('form#add_wc_pos_outlets select#_outlet_country, form#edit_wc_pos_outlets select#_outlet_country, form#add_wc_pos_outlets select#_outlet_state, form#edit_wc_pos_outlets select#_outlet_state').select2();
    } else {
        $('form#add_wc_pos_outlets select#_outlet_country, form#edit_wc_pos_outlets select#_outlet_country, form#add_wc_pos_outlets select#_outlet_state, form#edit_wc_pos_outlets select#_outlet_state').chosen();
    }
    $('#add_wc_pos_outlets').on('change', '#_outlet_country', function () {
        if ($('form#add_wc_pos_outlets #_outlet_country').val() != '') {
            $('#add_wc_pos_outlets').block({
                message: null,
                overlayCSS: {
                    background: '#fff url(' + wc_pos_params.ajax_loader_url + ') no-repeat center',
                    backgroundSize: '16px 16px',
                    opacity: 0.6
                }
            });
            var data = {
                action: 'wc_pos_new_update_outlets_address',
                security: wc_pos_params.new_update_pos_outlets_address_nonce,
                name: $('form#add_wc_pos_outlets #_outlet_name').val(),
                country: $('form#add_wc_pos_outlets #_outlet_country').val(),
                address_1: $('form#add_wc_pos_outlets #_outlet_address_1').val(),
                address_2: $('form#add_wc_pos_outlets #_outlet_address_2').val(),
                city: $('form#add_wc_pos_outlets #_outlet_city').val(),
                state: $('form#add_wc_pos_outlets #_outlet_state').val(),
                postcode: $('form#add_wc_pos_outlets #_outlet_postcode').val(),
                email: $('form#add_wc_pos_outlets #_outlet_email').val(),
                phone: $('form#add_wc_pos_outlets #_outlet_phone').val(),
                fax: $('form#add_wc_pos_outlets #_outlet_fax').val(),
                website: $('form#add_wc_pos_outlets #_outlet_website').val(),
                twitter: $('form#add_wc_pos_outlets #_outlet_twitter').val(),
                facebook: $('form#add_wc_pos_outlets #_outlet_facebook').val(),
            };
            if ($('#id_outlet').length > 0) {
                data.ID = $('#id_outlet').val();
            }
            xhr = $.ajax({
                type: 'POST',
                url: wc_pos_params.ajax_url,
                data: data,
                success: function (response) {
                    if (response) {
                        $('form#add_wc_pos_outlets select#_outlet_country').select2('destroy');
                        var html = $($.parseHTML($.trim(response)));
                        $('#add_wc_pos_outlets').html(html);
                        $('body').trigger('updated_checkout');
                        if ($().select2) {
                            $('form#add_wc_pos_outlets select#_outlet_country').select2();
                            $('form#add_wc_pos_outlets select#_outlet_state').select2();
                        } else {
                            $('form#add_wc_pos_outlets select#_outlet_country').chosen();
                            $('form#add_wc_pos_outlets select#_outlet_state').chosen();
                        }
                        $('#add_wc_pos_outlets').unblock();
                    }
                }
            });
        }
    });

    $('#edit_wc_pos_outlets').on('change', '#_outlet_country', function () {
        if ($('form#edit_wc_pos_outlets #_outlet_country').val() != '') {
            $('#edit_wc_pos_outlets').block({
                message: null,
                overlayCSS: {
                    background: '#fff url(' + wc_pos_params.ajax_loader_url + ') no-repeat center',
                    backgroundSize: '16px 16px',
                    opacity: 0.6
                }
            });

            var data = {
                action: 'wc_pos_edit_update_outlets_address',
                security: wc_pos_params.edit_update_pos_outlets_address_nonce,
                name: $('form#edit_wc_pos_outlets #_outlet_name').val(),
                country: $('form#edit_wc_pos_outlets #_outlet_country').val(),
                address_1: $('form#edit_wc_pos_outlets #_outlet_address_1').val(),
                address_2: $('form#edit_wc_pos_outlets #_outlet_address_2').val(),
                city: $('form#edit_wc_pos_outlets #_outlet_city').val(),
                state: $('form#edit_wc_pos_outlets #_outlet_state').val(),
                postcode: $('form#edit_wc_pos_outlets #_outlet_postcode').val(),
                email: $('form#edit_wc_pos_outlets #_outlet_email').val(),
                phone: $('form#edit_wc_pos_outlets #_outlet_phone').val(),
                fax: $('form#edit_wc_pos_outlets #_outlet_fax').val(),
                website: $('form#edit_wc_pos_outlets #_outlet_website').val(),
                twitter: $('form#edit_wc_pos_outlets #_outlet_twitter').val(),
                facebook: $('form#edit_wc_pos_outlets #_outlet_facebook').val(),
                ID: $('form#edit_wc_pos_outlets #id_outlet').val(),
            };

            xhr = $.ajax({
                type: 'POST',
                url: wc_pos_params.ajax_url,
                data: data,
                success: function (response) {

                    if (response) {
                        var html = $($.parseHTML($.trim(response)));
                        $('#edit_wc_pos_outlets').html(html);
                        if ($().select2) {
                            $('form#edit_wc_pos_outlets select#_outlet_country').select2();
                            $('form#edit_wc_pos_outlets select#_outlet_state').select2();
                        } else {
                            $('form#edit_wc_pos_outlets select#_outlet_country').chosen();
                            $('form#edit_wc_pos_outlets select#_outlet_state').chosen();
                        }
                        $('#edit_wc_pos_outlets').unblock();
                    }
                }
            });
        }
    });

    if ($('#sale_report_popup').length > 0) {
        $('#sale_report_popup .close_popup').click(function () {
            history.pushState('', '', 'admin.php?page=wc_pos_registers');
        });
    }

    if ($('.previous-next-toggles').length > 0) {
        if ($('#grid_layout_cycle > div').length <= 1) {
            $('.previous-next-toggles').hide();
        }
        $('#grid_layout_cycle').cycle({
            speed: 'fast',
            timeout: 0,
            pager: '.previous-next-toggles #nav_layout_cycle',
            next: '.previous-next-toggles .next-grid-layout',
            prev: '.previous-next-toggles .previous-grid-layout',
            before: function (currSlideElement, nextSlideElement, options, forwardFlag) {
                var table = $(nextSlideElement).find('table');
                if (typeof table.data('title') != undefined) {
                    var title = table.data('title');
                    $('#wc-pos-register-grids-title').html(title);
                }
            }
        });
    }

    var product_data = {};
    if ($('.tile_style').length > 0) {
        $('.tile_style').change(function () {
            var val = $('.tile_style:checked').val();
            if (val == 'colour') {
                $('.tile_style_bg_row').show();
            } else {
                $('.tile_style_bg_row').hide();
            }
            check_preview();
        }).trigger('change');

        $('#dafault_selection').change(function () {
            var val = $(this).val();
            if (val != '') {
                tiles_img = $(this).find('option[value="' + val + '"]').attr('data-img');
                $('#custom-background-image1').data('shop_thumbnail', tiles_img);
            } else {
                var selected_produst = $("#product_id").val();
                if (product_data[selected_produst] && product_data[selected_produst].image) {
                    var tiles_img = product_data[selected_produst].image;
                    $('#custom-background-image1').data('shop_thumbnail', tiles_img);
                } else {
                    $('#custom-background-image1').data('shop_thumbnail', '');
                }
            }
            check_preview();
        }).trigger('change');


        // Ajax product search box
        $('input.ajax_chosen_input_products').filter(':not(.enhanced)').each(function () {
            var select2_args = {
                allowClear: $(this).data('allow_clear') ? true : false,
                placeholder: $(this).data('placeholder'),
                minimumInputLength: $(this).data('minimum_input_length') ? $(this).data('minimum_input_length') : '3',
                escapeMarkup: function (m) {
                    return m;
                },
                //TODO: Don't work with new select2 version
                ajax: {
                    url: wc_enhanced_select_params.ajax_url,
                    dataType: 'json',
                    quietMillis: 250,
                    data: function (term, page) {
                        return {
                            term: term,
                            action: 'wc_pos_json_search_products',
                            security: wc_pos_params.search_products_and_variations
                        };
                    },
                    results: function (data, page) {
                        product_data = {};
                        product_data = data;
                        var terms = [];
                        if (data) {
                            $.each(data, function (id, val) {
                                terms.push({id: id, text: val.formatted_name});
                            });
                        }
                        return {results: terms};
                    },
                    cache: true
                }
            };

            if ($(this).data('multiple') === true) {
                select2_args.multiple = true;
                select2_args.initSelection = function (element, callback) {
                    var data = $.parseJSON(element.attr('data-selected'));
                    var selected = [];

                    $(element.val().split(",")).each(function (i, val) {
                        selected.push({id: val, text: data[val]});
                    });
                    return callback(selected);
                };
                select2_args.formatSelection = function (data) {
                    return '<div class="selected-option" data-id="' + data.id + '">' + data.text + '</div>';
                };
            } else {
                select2_args.multiple = false;
                select2_args.initSelection = function (element, callback) {
                    var data = {id: element.val(), text: element.attr('data-selected')};
                    return callback(data);
                };
            }


            $(this).select2(select2_args).addClass('enhanced');
        });
        check_preview();
    }
    $("#product_id").change(function () {
        var selected_produst = $(this).val();
        product_data = $.ajax({
            type: "GET",
            async: false,
            url: wc_enhanced_select_params.ajax_url,
            data: {
                term: selected_produst,
                action: 'wc_pos_json_search_products',
                security: wc_pos_params.search_products_and_variations
            },
            success: function (data) {
                return data;
            }
        }).responseJSON;

        if (product_data[selected_produst] && product_data[selected_produst].image) {
            var tiles_img = product_data[selected_produst].image;
            $('#custom-background-image1').data('shop_thumbnail', tiles_img);
        } else {
            $('#custom-background-image1').data('shop_thumbnail', '');
        }
        check_preview();

        if (selected_produst != '') {
            $('#serach_tile_product, #wc-pos-outlets-edit').block({
                message: null,
                overlayCSS: {
                    background: '#fff url(' + wc_pos_params.ajax_loader_url + ') no-repeat center',
                    backgroundSize: '16px 16px',
                    opacity: 0.6
                }
            });
            var data = {
                action: 'wc_pos_search_variations_for_product',
                id_product: selected_produst,
                security: wc_pos_params.search_variations_for_product,
            };
            $.post(wc_pos_params.ajax_url, data, function (response) {
                option = '<option value="0" selected>No default selection</option>';//wc_pos_params.no_default_selection
                response = response.trim();
                if (response != '') {
                    var obj = $.parseJSON(response);
                    $.each(obj, function (i, val) {
                        option += '<option value="' + i + '" data-img = "' + val.image + '">' + val.formatted_name + '</option>';
                    });
                    $('.dafault_selection').show();
                } else {
                    $('.dafault_selection').hide();
                }
                $('#dafault_selection').html(option);
                if ($().select2) {
                    $('#dafault_selection').select2();
                }
                $('#serach_tile_product, #wc-pos-outlets-edit').unblock();
            });
        }
    });

    function check_preview() {
        if ($('#tile_style_image').is(':checked')) {
            var image = $('#custom-background-image1').data('shop_thumbnail');
            $('#custom-background-image1').removeAttr('style').css({
                'background': 'url("' + image + '") center no-repeat',
                'background-size': 'contain',
                'background-color': '#ffffff'
            });
            $('#custom-background-tiles-color').hide();
        } else {
            $('#custom-background-tiles-color').show();
            var selected_produst = $("#product_id").val();
            var tiles_text = '';
            var background_color = $('#background_color').val();
            var text_color = $('#text-color').val();

            if (product_data[selected_produst] && product_data[selected_produst].name) {
                tiles_text = product_data[selected_produst].name;
            }
            else {
                tiles_text = $("#product_id_chosen").find('span').text();
                var arr = tiles_text.split(' – ');
                if (arr[1]) tiles_text = arr[1];
            }

            if ($('#product_id').val() != '')
                $("#custom-background-tiles-color").text(tiles_text);

            $("#custom-background-tiles-color").removeAttr('style').css({
                'color': text_color
            });

            $('#custom-background-image1').removeAttr('style').css({
                'background-color': background_color
            });
        }
    }

    if ($('#product_grid-add-toggle').length > 0) {
        $('#product_grid-add-toggle').click(function () {
            $(this).closest('#product_grid-adder').toggleClass('wp-hidden-children');
            return false;
        });
        $('#product_grid-add-submit').click(function () {
            add_product_grid();
            return false;
        });
        $('#newproduct_grid').keydown(function (e) {
            var code = e.keyCode || e.which;
            if (code == 13) { //Enter keycode
                add_product_grid();
                return false;
            }
        });
    }

    function add_product_grid() {
        var val = $('#newproduct_grid').val();
        var term = val.trim();
        if (term == '') return;
        var data = {
            action: 'wc_pos_add_product_grid',
            security: wc_pos_params.add_product_grid,
            term: term,
        };
        $('#product_grid-add-submit').attr('disabled', 'disabled');
        $.ajax({
            type: 'POST',
            async: false,
            url: wc_pos_params.ajax_url,
            data: data,
            success: function (response) {
                var id = parseInt(response);
                if (id > 0) {
                    if ($('div.gridcategorydiv ul').length)
                        $('div.gridcategorydiv ul').prepend('<li id="product_grid-' + id + '"><label class="selectit"><input type="checkbox" checked="checked" id="in-product_grid-' + id + '" name="pos_input[product_grid][]" value="' + id + '"> ' + term + '</label></li>');
                    else
                        $('div.gridcategorydiv').prepend('<div class="tabs-panel"><ul class="categorychecklist form-no-clear"><li id="product_grid-' + id + '"><label class="selectit"><input type="checkbox" checked="checked" id="in-product_grid-' + id + '" name="pos_input[product_grid][]" value="' + id + '"> ' + term + '</label></li></ul></div>');
                }
                $('#product_grid-add-submit').removeAttr('disabled');
                $('#newproduct_grid').val('');
            },
            error: function () {
                $('#product_grid-add-submit').removeAttr('disabled');
            }
        });
    }

    jQuery('.add-nominal').on('click', function (e) {
        e.preventDefault;
        jQuery('.cash-nominal-content').append('<div class="nominal-row"><input type="number" name="wc_pos_cash_nominal[]" step="0.01"><span class="remove"></span></div>');
    });

    jQuery('.cash-nominal-content').on('click', '.remove', function (e) {
        jQuery(this).parents('.nominal-row').remove();
    });

    jQuery('.actual-cash').on('click', function () {
        jQuery('.cash-popup').show();
    });

    jQuery('.cash-popup .button').on('click', function (e) {
        e.preventDefault;
        var sum = 0;
        var currency_symbol = jQuery('.woocommerce-Price-currencySymbol').first().text();
        jQuery('.nominal').each(function () {
            sum = sum + jQuery(this).data('value') * jQuery(this).val();
        });
        var difference = sum - jQuery('#drawer-cash').data('value');
        var difference_html;
        difference_html = currency_symbol + difference.toFixed(2);
        $.ajax({
            type: 'POST',
            url: wc_pos_params.ajax_url,
            data: {
                action: 'wc_pos_set_register_actual_cash',
                register_id: register_id,
                sum: sum.toFixed(2)
            },
            success: function (response) {

            },
            error: function () {

            }
        });
        jQuery('.actual-cash').html(currency_symbol + sum.toFixed(2));
        jQuery('.cash-difference').html(difference_html);
        jQuery('#cash-popup').hide();
    });

    jQuery('#pos-visibility .edit-pos-visibility').on('click', function (e) {
        e.preventDefault();
        jQuery('#pos-visibility #pos-visibility-select').slideDown('fast');
    });

    jQuery('#pos-visibility .save-post-visibility').on('click', function (e) {
        e.preventDefault();
        var option = jQuery('[name="_pos_visibility"]:checked');
        jQuery('#pos-visibility-display').text(option.data('label'));
        jQuery('#pos-visibility #pos-visibility-select').slideUp('fast');
    });

    jQuery('#custom-fee').on('click', '.button.add', function () {
        var id = jQuery('#custom-fee tbody tr').last().data('id');
        var data = {
            id: id + 1
        };
        var source = $('#tmpl-fee-row').html();
        var template = Handlebars.compile(source);
        var html = template(data);
        $('#custom-fee tbody').append(html);
    });

    jQuery('#custom-fee').on('click', '.button.remove', function () {
        var id = jQuery(this).data('id');
        $('#custom-fee tbody tr[data-id="' + id + '"]').remove();
    });

    $('#wc_pos_custom_fee').change(function () {
        if (this.checked)
            $('#custom-fees').show();
        else
            $('#custom-fees').hide();

    });
    jQuery('.wc-enhanced-select-required-fields option[value="billing_first_name"],' +
        '.wc-enhanced-select-required-fields option[value="billing_last_name"],' +
        '.wc-enhanced-select-required-fields option[value="billing_email"]').attr({
        'selected': 'selected',
        'disabled': 'disabled'
    });
    jQuery('.wc-enhanced-select-required-fields').select2().on("select2:unselecting", function (e) {
        if (e.params.args.data.disabled) {
            return false;
        }
    });
    jQuery('.outlet_select').on('change', function (e) {
        var base_url = jQuery(this).next('.button').data('url');
        jQuery(this).next('.button').attr('href', base_url + "&outlet=" + jQuery(this).val());
    })

});

function checkEmail(e) {
    ok = "1234567890qwertyuiop[]asdfghjklzxcvbnm.@-_QWERTYUIOPASDFGHJKLZXCVBNM";

    for (i = 0; i < e.length; i++)
        if (ok.indexOf(e.charAt(i)) < 0)
            return (false);

    if (document.images) {
        re = /(@.*@)|(\.\.)|(^\.)|(^@)|(@$)|(\.$)|(@\.)/;
        re_two = /^.+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;
        if (!e.match(re) && e.match(re_two))
            return true;
        else
            return false;

    }
    return true;

}

function checkPhone(e) {
    var number_count = 0;
    for (i = 0; i < e.length; i++)
        if ((e.charAt(i) >= '0') && (e.charAt(i) <= 9))
            number_count++;

    if (number_count == 11 || number_count <= 12)
        return true;

    return false;
}
