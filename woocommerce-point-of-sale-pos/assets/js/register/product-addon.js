jQuery(document).ready(function ($) {
    window.POS_PR_ADDONS = {
        init: function () {
            wp.hooks.addFilter('wc_pos_before_add_to_cart_validation', PR_ADDONS.addons_attributes_validation, 20, 6);
            wp.hooks.addFilter('wc_pos_add_to_cart_validation', PR_ADDONS.addons_attributes_validation, 20, 6);
            wp.hooks.addFilter('wc_pos_validate_missing_attributes', PR_ADDONS.validate_missing_attributes, 20, 7);
            wp.hooks.addFilter('wc_pos_cart_variation', PR_ADDONS.cart_variation, 20, 6);
            wp.hooks.addFilter('wc_pos_add_to_cart_product_data', PR_ADDONS.cart_product_data, 20, 6);

            $('#product-addons-attributes').on('keyup change', 'input, textarea', function () {

                if ($(this).attr('maxlength') > 0) {

                    var value = $(this).val();
                    var remaining = $(this).attr('maxlength') - value.length;

                    $(this).next('.chars_remaining').find('span').text(remaining);
                }

            });

            var $cart = $('#modal-missing-attributes');

            // Clear all values on variable product when clear selection is clicked
            $cart.on('click', '#reset_selected_variation', function () {

                $.each($cart.find('.product-addon'), function () {
                    var element = $(this).find('.addon');

                    if (element.is(':checkbox') || element.is(':radio')) {
                        element.prop('checked', false);
                    }

                    if (element.is('select')) {
                        element.prop('selectedIndex', 0);
                    }

                    if (element.is(':text') || element.is('textarea') || element.is('input[type="number"]') || element.is('input[type="file"]')) {
                        element.val('');
                    }
                });

                $cart.trigger('wc-pos-product-addons-update');
            });

            $cart.on('change', '#product-addons-attributes input, #product-addons-attributes textarea, #product-addons-attributes select', function () {
                $cart.trigger('wc-pos-product-addons-update');
            });
            $cart.on('wc_pos_updated_addons', function () {
                var addons_price = $('#product-addons-total').data('addons-price');
                APP.tmp.product_item.cart_item_data['addons_price'] = addons_price;
            });


            $('#missing-attributes-select').on('found_variation', this.update_price);
            $cart.on('wc-pos-product-addons-update', this.update_price);
        },
        cart_product_data: function (product_data, product_id, quantity, variation_id, variation, cart_item_data) {
            if (typeof cart_item_data != 'undefined' && typeof cart_item_data.addons_price != 'undefined') {
                var addons_price = cart_item_data.addons_price;
                var product_type = product_data.type;
                if (product_type == 'variable' && variation_id > 0) {
                    $.each(product_data.variations, function (index, variation) {
                        if (variation_id == variation.id) {
                            var v_price = parseFloat(product_data.variations[index]['price']);
                            product_data.variations[index]['price'] = v_price + parseFloat(addons_price);
                            return;
                        }
                    });
                } else {
                    product_data.price = parseFloat(product_data.price) + parseFloat(addons_price);
                    product_data.regular_price = parseFloat(product_data.regular_price) + parseFloat(addons_price);
                }
            }
            return product_data;
        },
        update_price: function () {
            if (!$('#product-addons-attributes table').length) {
                $('#product-addons-total').remove();
                return;
            }
            if (!$('#product-addons-total').length) {
                var source = $('#tmpl-product-addons-total').html();
                var template = Handlebars.compile(source);
                var html = template({});
                $('#selected-variation-data').append(html);
            }
            var $cart = $('#modal-missing-attributes');
            var total = 0;
            var product = APP.tmp.product_item.adding_to_cart;
            var $totals = $('#product-addons-total');
            var product_type = product.type;
            var product_id = APP.tmp.product_item.product_id;
            var variation_id = APP.tmp.product_item.variation_id;
            var product_price = parseFloat(product.price);
            if (product_type == 'variable' && variation_id > 0) {
                $.each(product.variations, function (index, variation) {
                    if (variation_id == variation.id) {
                        product_price = parseFloat(variation.price);
                        return;
                    }
                });
            }

            // We will need some data about tax modes (both store and display)
            // and 'raw prices' (prices that don't take into account taxes) so we can use them in some
            // instances without making an ajax call to calculate taxes
            var product_raw = product.price;
            var tax_mode = $totals.data('tax-mode');
            var tax_display_mode = $totals.data('tax-display-mode');
            var total_raw = 0;


            if ($('#product-addons-attributes table').length) {

                $('#product-addons-attributes table').find('.addon').each(function () {
                    var addon_cost = 0;
                    var addon_cost_raw = 0;

                    if ($(this).is('.addon-custom-price')) {
                        addon_cost = $(this).val();
                    } else if ($(this).is('.addon-input_multiplier')) {
                        if (isNaN($(this).val()) || $(this).val() == "") { // Number inputs return blank when invalid
                            $(this).val('');
                            $(this).closest('td').find('.addon-alert').show();
                        } else {
                            if ($(this).val() != "") {
                                $(this).val(Math.ceil($(this).val()));
                            }
                            $(this).closest('td').find('.addon-alert').hide();
                        }
                        addon_cost = $(this).data('price') * $(this).val();
                        addon_cost_raw = $(this).data('raw-price') * $(this).val();
                    } else if ($(this).is('.addon-checkbox, .addon-radio')) {
                        if ($(this).is(':checked')) {
                            addon_cost = $(this).data('price');
                            addon_cost_raw = $(this).data('raw-price');
                        }
                    } else if ($(this).is('.addon-select')) {
                        if ($(this).val()) {
                            addon_cost = $(this).find('option:selected').data('price');
                            addon_cost_raw = $(this).find('option:selected').data('raw-price');
                        }
                    } else {
                        if ($(this).val()) {
                            addon_cost = $(this).data('price');
                            addon_cost_raw = $(this).data('raw-price');
                        }
                    }

                    if (!addon_cost) {
                        addon_cost = 0;
                    }
                    if (!addon_cost_raw) {
                        addon_cost_raw = 0;
                    }

                    total = parseFloat(total) + parseFloat(addon_cost);
                    total_raw = parseFloat(total_raw) + parseFloat(addon_cost_raw);
                });

                $totals.data('addons-price', total);
                $totals.data('addons-raw-price', total_raw);

                if (total > 0) {
                    var product_total_price, product_total_raw_price;

                    var formatted_addon_total = accountingPOS(total, 'formatMoney');
                    var formatted_raw_total = accountingPOS(product_total_raw_price + total_raw, 'formatMoney');

                    if ('undefined' !== typeof product_price) {
                        var formatted_grand_total = accountingPOS(product_price + total, 'formatMoney');
                    }

                    /*var subscription_details = false;

                     if ( $( '.entry-summary .subscription-details' ).length ) {
                     subscription_details = $( '.entry-summary .subscription-details' ).clone().wrap( '<p>' ).parent().html();
                     }

                     if ( subscription_details ) {
                     formatted_addon_total += subscription_details;
                     if ( formatted_grand_total ) {
                     formatted_grand_total += subscription_details;
                     }
                     }*/

                    var html = '<tr class="product-addon-totals"><th>' + product_addons_i18n[0] + '</th><td><span class="amount">' + formatted_addon_total + '</span></td></tr>';

                    if (formatted_grand_total && '1' == $totals.data('show-grand-total')) {

                        // To show our "price display suffix" we have to do some magic since the string can contain variables (excl/incl tax values)
                        // so we have to take our grand total and find out what the tax value is, which we can do via an ajax call
                        // if its a simple string, or no string at all, we can output the string without an extra call
                        var price_display_suffix = '';

                        // no sufix is present, so we can just output the total
                        if (!wc_pos_product_addons_params.price_display_suffix) {
                            html = html + '<tr class="product-addon-totals"><th>' + product_addons_i18n[1] + '</th><td><span class="amount">' + formatted_grand_total + '</span></td></tr>';
                            $totals.html(html);
                            $cart.trigger('wc_pos_updated_addons');
                            return;
                        }

                        // a suffix is present, but no special labels are used - meaning we don't need to figure out any other special values - just display the playintext value
                        if (false === ( wc_pos_product_addons_params.price_display_suffix.indexOf('{price_including_tax}') > -1 ) && false === ( wc_pos_product_addons_params.price_display_suffix.indexOf('{price_excluding_tax}') > -1 )) {
                            html = html + '<dt>' + product_addons_i18n[1] + '</dt><dd><span class="amount">' + formatted_grand_total + '</span> ' + wc_pos_product_addons_params.price_display_suffix + '</dd></dl>';
                            $totals.html(html);
                            $cart.trigger('wc_pos_updated_addons');
                            return;
                        }

                        // If prices are entered exclusive of tax but display inclusive, we have enough data from our totals above
                        // to do a simple replacement and output the totals string
                        if ('excl' === tax_mode && 'incl' === tax_display_mode) {
                            price_display_suffix = '<small class="woocommerce-price-suffix">' + wc_pos_product_addons_params.price_display_suffix + '</small>';
                            price_display_suffix = price_display_suffix.replace('{price_including_tax}', formatted_grand_total);
                            price_display_suffix = price_display_suffix.replace('{price_excluding_tax}', formatted_raw_total);
                            html = html + '<dt>' + product_addons_i18n[1] + '</dt><dd><span class="amount">' + formatted_grand_total + '</span> ' + price_display_suffix + ' </dd></dl>';
                            $totals.html(html);
                            $cart.trigger('wc_pos_updated_addons');
                            return;
                        }

                        // Prices are entered inclusive of tax mode but displayed exclusive, we have enough data from our totals above
                        // to do a simple replacement and output the totals string.
                        if ('incl' === tax_mode && 'excl' === tax_display_mode) {
                            price_display_suffix = '<small class="woocommerce-price-suffix">' + wc_pos_product_addons_params.price_display_suffix + '</small>';
                            price_display_suffix = price_display_suffix.replace('{price_including_tax}', formatted_raw_total);
                            price_display_suffix = price_display_suffix.replace('{price_excluding_tax}', formatted_grand_total);
                            html = html + '<dt>' + product_addons_i18n[1] + '</dt><dd><span class="amount">' + formatted_grand_total + '</span> ' + price_display_suffix + ' </dd></dl>';
                            $totals.html(html);
                            $cart.trigger('wc_pos_updated_addons');
                            return;
                        }

                        // Based on the totals/info and settings we have, we need to use the get_price_*_tax functions
                        // to get accurate totals. We can get these values with a special Ajax function
                        $.ajax({
                            type: 'POST',
                            url: wc_pos_product_addons_params.ajax_url,
                            data: {
                                action: 'wc_product_addons_calculate_tax',
                                total: product_total_price + total,
                                product_id: product_id
                            },
                            success: function (code) {
                                result = $.parseJSON(code);
                                if (result.result == 'SUCCESS') {
                                    price_display_suffix = '<small class="woocommerce-price-suffix">' + wc_pos_product_addons_params.price_display_suffix + '</small>';
                                    var formatted_price_including_tax = accounting.formatMoney(result.price_including_tax, {
                                        symbol: wc_pos_product_addons_params.currency_format_symbol,
                                        decimal: wc_pos_product_addons_params.currency_format_decimal_sep,
                                        thousand: wc_pos_product_addons_params.currency_format_thousand_sep,
                                        precision: wc_pos_product_addons_params.currency_format_num_decimals,
                                        format: wc_pos_product_addons_params.currency_format
                                    });
                                    var formatted_price_excluding_tax = accounting.formatMoney(result.price_excluding_tax, {
                                        symbol: wc_pos_product_addons_params.currency_format_symbol,
                                        decimal: wc_pos_product_addons_params.currency_format_decimal_sep,
                                        thousand: wc_pos_product_addons_params.currency_format_thousand_sep,
                                        precision: wc_pos_product_addons_params.currency_format_num_decimals,
                                        format: wc_pos_product_addons_params.currency_format
                                    });
                                    price_display_suffix = price_display_suffix.replace('{price_including_tax}', formatted_price_including_tax);
                                    price_display_suffix = price_display_suffix.replace('{price_excluding_tax}', formatted_price_excluding_tax);
                                    html = html + '<dt>' + product_addons_i18n[1] + '</dt><dd><span class="amount">' + formatted_grand_total + '</span> ' + price_display_suffix + ' </dd></dl>';
                                    $totals.html(html);
                                    $cart.trigger('wc_pos_updated_addons');
                                } else {
                                    html = html + '<dt>' + product_addons_i18n[1] + '</dt><dd><span class="amount">' + formatted_grand_total + '</span></dd></dl>';
                                    $totals.html(html);
                                    $cart.trigger('wc_pos_updated_addons');
                                }
                            },
                            error: function () {
                                html = html + '<dt>' + product_addons_i18n[1] + '</dt><dd><span class="amount">' + formatted_grand_total + '</span></dd></dl>';
                                $totals.html(html);
                                $cart.trigger('wc_pos_updated_addons');
                            }
                        });
                    } else {
                        $totals.empty();
                        $cart.trigger('wc_pos_updated_addons');
                    }

                } else {
                    $totals.empty();
                    $cart.trigger('wc_pos_updated_addons');
                }

            }


        },
        addons_attributes_validation: function (valid, adding_to_cart, product_id, quantity, variation_id, variations) {
            if ($('#product-addons-attributes table').length) {
                var product_addons = PR_ADDONS.get_product_addons(adding_to_cart);
                if (product_addons.length > 0) {
                    var addons = [];
                    $.each(product_addons, function (index, addon) {
                        var fields = {};
                        switch (addon.type) {
                            case 'checkbox':
                            case 'radiobutton':
                                $.each(addon.options, function (i, option) {
                                    var $input = $('.' + addon.type + '_' + index + '_' + i);
                                    if ($input.length && $input.is(':checked')) {
                                        fields[i] = option.label;
                                    }
                                });
                                break;
                            case 'custom_textarea':
                            case 'custom_price':
                            case 'input_multiplier':
                            case 'custom':
                            case 'custom_letters_only':
                            case 'custom_digits_only':
                            case 'custom_letters_or_digits':
                            case 'custom_email':
                                $.each(addon.options, function (i, option) {
                                    var $input = $('.' + addon.type + '_' + index + '_' + i);
                                    if ($input.length && $input.val() != '') {
                                        fields[i] = $input.val();
                                    }
                                });
                                break;
                            case 'select':
                                var $input = $('.' + addon.type + '_' + index);
                                if ($input.length && $input.val() != '') {
                                    fields[0] = $('.' + addon.type + '_' + index).val();
                                }
                                break;
                        }
                        if (!sizeof(fields) && addon.required) {
                            valid = false;
                        }
                        addons.push(fields);

                        if (valid) {
                            $.each(addon.options, function (i, option) {
                                var $input = $('.' + addon.type + '_' + index + '_' + i);
                                var val = $input.val();
                                if ($input.length) {
                                    switch (addon.type) {
                                        case "custom" :
                                        case "custom_textarea" :
                                        case "custom_letters_only" :
                                        case "custom_digits_only" :
                                        case "custom_letters_or_digits" :
                                            var val = $input.val();
                                            if (option['min'] != '' && val != '' && val.length < option['min']) {
                                                var msg = sprintf(product_addons_i18n[3], addon['name'], option['label'], option['min']);
                                                APP.showNotice(msg, 'error');
                                                valid = false;
                                            }
                                            if (option['max'] != '' && val != '' && val.length > option['max']) {
                                                var msg = sprintf(product_addons_i18n[4], addon['name'], option['label'], option['max']);
                                                APP.showNotice(msg, 'error');
                                                valid = false;
                                            }
                                            break;
                                        case "custom_price" :
                                        case "input_multiplier" :
                                            if (option['min'] != '' && val != '' && val.length > option['min']) {
                                                var msg = sprintf(product_addons_i18n[5], addon['name'], option['label'], option['min']);
                                                APP.showNotice(msg, 'error');
                                                valid = false;
                                            }
                                            if (option['max'] != '' && val != '' && val.length > option['max']) {
                                                var msg = sprintf(product_addons_i18n[6], addon['name'], option['label'], option['max']);
                                                APP.showNotice(msg, 'error');
                                                valid = false;
                                            }
                                            break;
                                    }


                                }
                            });
                            $.each(addon.options, function (i, option) {
                                var $input = $('.' + addon.type + '_' + index + '_' + i);
                                var val = $input.val();
                                // Other option specific checks
                                switch (addon['type']) {
                                    case "input_multiplier" :
                                        val = parseInt(val);
                                        if (val < 0) {
                                            var msg = sprintf(product_addons_i18n[7], addon['name'], option['label']);
                                            APP.showNotice(msg, 'error');
                                            valid = false;
                                        }
                                        break;
                                    case "custom_letters_only" :
                                        if (null === val.match(/^[A-Z]*$/i)) {
                                            var msg = sprintf(product_addons_i18n[8], addon['name'], option['label']);
                                            APP.showNotice(msg, 'error');
                                            valid = false;
                                        }
                                        break;
                                    case "custom_digits_only" :
                                        if (null === val.match(/^[0-9]*$/)) {
                                            var msg = sprintf(product_addons_i18n[9], addon['name'], option['label']);
                                            APP.showNotice(msg, 'error');
                                            valid = false;
                                        }
                                        break;
                                    case "custom_letters_or_digits" :
                                        if (null === val.match(/^[A-Z0-9]*$/i)) {
                                            var msg = sprintf(product_addons_i18n[10], addon['name'], option['label']);
                                            APP.showNotice(msg, 'error');
                                            valid = false;
                                        }
                                        break;
                                    case "custom_email" :
                                        if (val != '' && !POSvalidateEmail(val)) {
                                            var msg = sprintf(product_addons_i18n[11], addon['name'], option['label']);
                                            APP.showNotice(msg, 'error');
                                            valid = false;
                                        }
                                        break;
                                }
                            });
                        }


                    });

                    APP.tmp.product_item.cart_item_data['addons'] = addons;
                }

            }
            return valid;
        },
        get_product_addons: function (adding_to_cart) {
            var product_addons = [];
            if (typeof adding_to_cart.post_meta != 'undefined' && typeof adding_to_cart.post_meta.product_addons != 'undefined' && adding_to_cart.post_meta.product_addons.length > 0) {
                product_addons = adding_to_cart.post_meta.product_addons.slice(0);
            }


            if (typeof wc_pos_product_global_add_on != 'undefined' && wc_pos_product_global_add_on.length > 0) {
                $.each(wc_pos_product_global_add_on, function (index, global_add_on) {
                    if (global_add_on.addons.length > 0) {

                        if (global_add_on.all_products) {
                            $.each(global_add_on.addons, function (i, add_on) {
                                product_addons.push(add_on);
                            });
                        } else if (typeof global_add_on.categories != 'undefined' && global_add_on.categories.length > 0 && typeof adding_to_cart.categories_ids != 'undefined' && adding_to_cart.categories_ids.length > 0) {
                            var ids = array_intersect(global_add_on.categories, adding_to_cart.categories_ids);
                            if (sizeof(ids) > 0) {

                                $.each(global_add_on.addons, function (i, add_on) {
                                    product_addons.push(add_on);
                                });
                            }
                        }

                    }

                });
            }

            return product_addons;
        },
        validate_missing_attributes: function (missing, adding_to_cart, product_id, quantity, variation_id, selected_attr, cart_item_data) {
            var product_addons = PR_ADDONS.get_product_addons(adding_to_cart);
            if (product_addons.length > 0) {

                if (typeof cart_item_data.addons == 'undefined') {
                    missing = true;
                } else {
                    $.each(product_addons, function (index, addon) {

                        if (addon.required && ( typeof cart_item_data.addons[index] == 'undefined' || !sizeof(cart_item_data.addons[index]) )) {
                            missing = true;
                        }

                    });
                }
                if (missing) {
                    var source = $('#tmpl-product-addons').html();
                    var template = Handlebars.compile(source);
                    var html = template({product_addons: product_addons});
                    $html = $(html);

                    $('#modal-missing-attributes').addClass('missing-attributes md-close-by-overlay');
                    $('#product-addons-attributes').html($html);

                    $('#product-addons-attributes').find('.addon-custom, .addon-custom-textarea').each(function () {
                        if ($(this).attr('maxlength') > 0) {
                            $(this).after('<small class="chars_remaining"><span>' + $(this).attr('maxlength') + '</span> ' + product_addons_i18n[2] + '</small>');
                        }
                    });

                }

            }
            return missing;
        },
        cart_variation: function (variation, product_data, product_id, quantity, variation_id, cart_item_data) {

            var product_addons = PR_ADDONS.get_product_addons(product_data);

            if (typeof cart_item_data.addons !== 'undefined' && product_addons.length > 0) {
                $.each(cart_item_data.addons, function (index, addon) {
                    var key_count = 2;
                    var name = product_addons[index]['name'];
                    var type = product_addons[index]['type'];
                    $.each(addon, function (i, value) {
                        var option = product_addons[index]['options'][i];
                        switch (type) {
                            case 'custom_textarea':
                            case 'custom_price':
                            case 'input_multiplier':
                            case 'custom':
                            case 'custom_letters_only':
                            case 'custom_digits_only':
                            case 'custom_letters_or_digits':
                            case 'custom_email':
                                name += option.label != '' ? ' - ' + option.label : '';
                                break;
                        }
                        var key = name + (option.price != '' ? ' (' + accountingPOS(option.price, 'formatMoney') + ')' : '');
                        if (variation[key] != undefined) {
                            variation['#' + key_count + ' ' + key] = value;
                            key_count++;
                        } else {
                            variation[key] = value;
                        }
                    });
                });
            }
            return variation;
        }
    }
});
