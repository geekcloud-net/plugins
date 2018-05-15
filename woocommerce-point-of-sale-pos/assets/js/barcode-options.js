jQuery(document).ready(function ($) {
    var wc_pos_barcode_options = {
        init: function () {
            $('#edit_wc_pos_barcode')
                .on('click', '.edit-order-item', this.edit_item)
                .on('click', '.delete-order-item', this.delete_item)
                .on('click', '.cancel-action', this.cancel_action)
                .on('click', '.save-action', this.save_action)
                .on('click', 'tr.item', this.select_row)
                .on('click', 'tr.item :input, tr.item a', this.select_row_child)
                .on('click', 'button.add-line-item', this.add_item)
                .on('click', 'button.add-line-item-category', this.add_category_items)
                .on('click', 'button.bulk-add-variations', this.add_item_variations)
                .on('click', 'button.bulk-delete-items', this.bulk_delete_items);

            $(document.body)
                .on('change', '.fields_to_print', this.fields_to_print)
                .on('click', '#add_products', this.add_products)
                .on('click', '.modal-close', this.modal_close)
                .on('click', '#print_barcode', this.print_barcodes)
                .append('<div id="printable_barcode"></div>');

        },
        print_barcodes: function () {
            if (!$('#edit_wc_pos_barcode #order_line_items tr.item').length) {
                return false;
            }
            var type = $('#label_type').val();
            var number = parseInt($('#number_of_labels').val());
            if (isNaN(number) || number < 1) {
                number = 1;
            }
            $('#printable_barcode').html('');

            $('#edit_wc_pos_barcode #order_line_items tr').each(function (index, tr) {
                var qty = parseInt($(tr).find('td.quantity .quantity').val());
                var n = number;
                if (!isNaN(qty) && qty > 1) {
                    n = qty;
                }
                for (var i = 0; i < n; i++) {
                    $('#printable_barcode').append($(tr).find('.barcode_border').clone());
                }
            });


            switch (type) {
                case 'continuous_feed':
                    $('#printable_barcode').addClass('continuous_feed');
                    $('#printable_barcode').removeClass('jew_50_10').removeClass('con_4_2').removeClass('con_4_3').removeClass('a4').removeClass('letter').removeClass('per_sheet_80').removeClass('per_sheet_30').removeClass('a4_30');
                    break;
                case 'a4':
                    $('#printable_barcode').addClass('a4');
                    $('#printable_barcode').removeClass('jew_50_10').removeClass('con_4_2').removeClass('con_4_3').removeClass('continuous_feed').removeClass('letter').removeClass('per_sheet_80').removeClass('per_sheet_30').removeClass('a4_30');
                    break;
                case 'letter':
                    $('#printable_barcode').addClass('letter');
                    $('#printable_barcode').removeClass('jew_50_10').removeClass('con_4_2').removeClass('con_4_3').removeClass('continuous_feed').removeClass('a4').removeClass('per_sheet_80').removeClass('per_sheet_30').removeClass('a4_30');
                    break;
                case 'per_sheet_30':
                    $('#printable_barcode').addClass('per_sheet_30');
                    $('#printable_barcode').removeClass('jew_50_10').removeClass('con_4_2').removeClass('con_4_3').removeClass('continuous_feed').removeClass('a4').removeClass('letter').removeClass('per_sheet_80').removeClass('a4_30');
                    break;
                case 'per_sheet_80':
                    $('#printable_barcode').addClass('per_sheet_80');
                    $('#printable_barcode').removeClass('jew_50_10').removeClass('con_4_2').removeClass('con_4_3').removeClass('continuous_feed').removeClass('a4').removeClass('letter').removeClass('per_sheet_30').removeClass('a4_30');
                    break;
                case 'a4_30':
                    $('#printable_barcode').addClass('a4_30');
                    $('#printable_barcode').removeClass('jew_50_10').removeClass('con_4_2').removeClass('con_4_3').removeClass('continuous_feed').removeClass('a4').removeClass('letter').removeClass('per_sheet_30').removeClass('per_sheet_80');
                    break;
                case 'con_4_3':
                    $('#printable_barcode').addClass('con_4_3');
                    $('#printable_barcode').removeClass('jew_50_10').removeClass('con_4_2').removeClass('per_sheet_80').removeClass('continuous_feed').removeClass('a4').removeClass('letter').removeClass('per_sheet_30').removeClass('a4_30');
                    break;
                case 'con_4_2':
                    $('#printable_barcode').addClass('con_4_2');
                    $('#printable_barcode').removeClass('jew_50_10').removeClass('con_4_3').removeClass('per_sheet_80').removeClass('continuous_feed').removeClass('a4').removeClass('letter').removeClass('per_sheet_30').removeClass('a4_30');
                    break;
                case 'jew_50_10':
                    $('#printable_barcode').addClass('jew_50_10');
                    $('#printable_barcode').removeClass('con_4_2').removeClass('con_4_3').removeClass('per_sheet_80').removeClass('continuous_feed').removeClass('a4').removeClass('letter').removeClass('per_sheet_30').removeClass('a4_30');
                    break;
            }
            window.print();
        },

        fields_to_print: function () {
            var field_barcode = $('#field_barcode').is(':checked');
            var field_sku = $('#field_sku').is(':checked');
            var field_name = $('#field_name').is(':checked');
            var field_price = $('#field_price').is(':checked');
            var field_meta_value = $('#field_meta_value').is(':checked');
            var field_meta_title = $('#field_meta_title').is(':checked');
            $('#order_line_items .item').each(function (index, el) {
                var $barcode_text = $(el).find('.barcode_border .barcode_text');
                var barcode_url = $(el).find('.barcode_border img').data('barcode_url');
                $barcode_text.html('');
                if (!field_barcode) {
                    $(el).find('.barcode_border img').hide();
                } else {
                    $(el).find('.barcode_border img').show().attr('src', barcode_url + '&font_size=0');
                }
                if (field_sku) {
                    var sku = $(el).find('.sku_text').text();
                    $barcode_text.append(sku).append('<br />');
                }
                if (field_name) {
                    var name = $(el).find('.wc-order-item-name').text();
                    $barcode_text.append(name).append('<br />');
                }
                if (field_price) {
                    var amount = $(el).find('.product_price').text();
                    $barcode_text.append(amount).append('<br />');
                }
                if (field_meta_value) {
                    var meta = $(el).find('.display_meta tr');
                    $.each(meta, function (k, v) {
                        var text = '';
                        if (field_meta_title) {
                            text = $(v).find('th').text() + '&nbsp;';
                        }
                        text = text + $(v).find('td').text();
                        $barcode_text.append(text).append('<br />');
                    })
                }
            });
        },

        modal_close: function () {
            $("#add_item_id").select2('destroy');
            $('#wc-pos-barcode-modal-dialog').remove();
            return false;
        },


        init_tiptip: function () {
            $('#tiptip_holder').removeAttr('style');
            $('#tiptip_arrow').removeAttr('style');
            $('.tips').tipTip({
                'attribute': 'data-tip',
                'fadeIn': 50,
                'fadeOut': 50,
                'delay': 200
            });
        },

        block: function () {
            $('#edit_wc_pos_barcode').block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });
        },

        unblock: function () {
            $('#edit_wc_pos_barcode').unblock();
        },
        edit_item: function () {
            var $tr = $(this).closest('tr');
            $tr.addClass('editing');
            $(this).hide();
            $tr.find('td.quantity .view').hide();
            $tr.find('td.quantity .edit').show();

            var qty = $tr.find('td.quantity .quantity').val();
            $tr.find('td.quantity .quantity').data('qty', qty);

            $('.wc-order-bulk-actions .barcode-edit-item').show();
            return false;
        },
        delete_item: function () {

            var parentid = parseInt($(this).closest('tr').data('parentid'));
            $(this).closest('tr').remove();
            $('#tiptip_holder').hide();
            if (!$('#edit_wc_pos_barcode #order_line_items tr').length) {
                var tpl = $('#wc_pos_barcode_no_products').html();
                $('#edit_wc_pos_barcode #order_line_items').html(tpl);
            } else {
                var $parent = $('table.woocommerce_order_items tbody#order_line_items tr[data-parentid="' + parentid + '"]');
                if (!$('table.woocommerce_order_items tbody#order_line_items tr[data-prid="' + parentid + '"]').length) {
                    $parent.removeClass('variation_row');
                }
            }
            return false;
        },
        cancel_action: function () {
            var $tr = $('#edit_wc_pos_barcode tr.editing');
            $tr.removeClass('editing');
            $tr.find('.edit-order-item').removeAttr('style');
            $tr.find('td.quantity .view').show();
            $tr.find('td.quantity .edit').hide();
            $('.wc-order-bulk-actions .barcode-edit-item').hide();

            $tr.each(function (index, el) {
                var qty = $(el).find('td.quantity .quantity').data('qty');
                $(el).find('td.quantity .quantity').val(qty);
                $(el).find('td.quantity .view span').text(qty);

            });

            return false;
        },
        save_action: function () {
            var $tr = $('#edit_wc_pos_barcode tr.editing');
            $tr.each(function (index, el) {
                var qty = $(el).find('td.quantity .quantity').val();
                $(el).find('td.quantity .quantity').data('qty', qty);
            });

            wc_pos_barcode_options.cancel_action();
            return false;
        },
        select_row: function () {
            var $row = false;
            if ($(this).is('tr')) {
                $row = $(this);
            } else {
                $row = $(this).closest('tr');
            }
            var $table = $(this).closest('table');

            if ($row.is('.selected')) {
                $row.removeClass('selected');
            } else {
                $row.addClass('selected');
            }

            var $rows = $table.find('tr.selected');

            if ($rows.length) {
                $('.bulk-delete-items').show();

                var selected_variations = false;

                $rows.each(function () {
                    if ($(this).is('tr.variable')) {
                        selected_variations = true;
                    }
                });

                if (selected_variations) {
                    $('.bulk-add-variations').show();
                } else {
                    $('.bulk-add-variations').hide();
                }
            } else {
                $('.bulk-delete-items, .bulk-add-variations').hide();
            }
        },

        select_row_child: function (e) {
            e.stopPropagation();
        },
        add_item: function () {
            var template = $('#wc_pos_modal_barcode_add_products').html();
            $('body').append(template);
            if ($('#order_line_items tr.item').length) {

                var exclude = [];
                $('#order_line_items tr.item').each(function (index, el) {
                    var id = parseInt($(el).data('prid'));
                    exclude.push(id);
                });
                $('#add_item_id').data('exclude', exclude.join(','));

            }
            $(document.body).trigger('wc-enhanced-select-init');
            return false;
        },
        add_category_items: function () {
            var template = $('#wc_pos_modal_barcode_add_products').html();
            $('body').append(template);
            $('#add_item_id').data('action', 'wc_pos_json_search_categories').attr('data-placeholder', wc_pos_barcode.select_placeholder_category).addClass('search_categories');

            $(document.body).trigger('wc-enhanced-select-init');
            return false;
        },
        bulk_delete_items: function () {
            var $table = $('table.woocommerce_order_items');
            var $rows = $table.find('tr.selected');
            if ($rows.length && window.confirm(wc_pos_barcode.remove_item_notice)) {

                $rows.find('a.delete-order-item').click();
                $('.bulk-delete-items, .bulk-add-variations').hide();

            }
            return false;
        },
        add_products: function () {

            if (wc_version >= 3) {
                var add_item_ids = $("#add_item_id").val();
            } else {
                var add_item_ids = $("#add_item_id").val().split(',');
            }

            if ($("#add_item_id").hasClass('search_categories')) {
                wc_pos_barcode_options.block();
                wc_pos_barcode_options.modal_close();

                var data = {
                    action: 'wc_pos_get_products_by_categories',
                    categories: add_item_ids,
                    security: wc_pos_barcode.product_for_barcode_nonce
                };

                $.post(wc_pos_barcode.ajax_url, data, function (response) {

                    var add_item_ids = typeof response != 'object' ? jQuery.parseJSON(response) : response;
                    wc_pos_barcode_options.add_items(add_item_ids);
                });
            } else {
                wc_pos_barcode_options.add_items(add_item_ids);
            }
        },
        add_items: function (add_item_ids) {
            if (typeof add_item_ids != 'undefined' && add_item_ids) {

                var count = add_item_ids.length;

                wc_pos_barcode_options.block();
                wc_pos_barcode_options.modal_close();

                $.each(add_item_ids, function (index, value) {
                    if ($('table.woocommerce_order_items tbody#order_line_items .item_' + value).length) {
                        if (!--count) {
                            wc_pos_barcode_options.fields_to_print();
                            wc_pos_barcode_options.init_tiptip();
                            wc_pos_barcode_options.unblock();
                        }
                        return;
                    }
                    var data = {
                        action: 'wc_pos_add_product_for_barcode',
                        item_to_add: value,
                        security: wc_pos_barcode.product_for_barcode_nonce
                    };

                    $.post(wc_pos_barcode.ajax_url, data, function (response) {
                        $('table.woocommerce_order_items tbody#order_line_items .no_products').remove();

                        var $line = $(response);
                        var parentid = parseInt($line.data('parentid'));
                        var $parent = $('table.woocommerce_order_items tbody#order_line_items tr[data-parentid="' + parentid + '"]');
                        if (parentid != NaN && parentid != null && $parent.length) {

                            if (value == parentid) {
                                $parent.first().before(response);
                            } else {
                                $parent.last().after(response);
                            }

                            var $parent = $('table.woocommerce_order_items tbody#order_line_items tr[data-parentid="' + parentid + '"]');

                            $parent.removeClass('variation_row');

                            if ($('table.woocommerce_order_items tbody#order_line_items tr[data-prid="' + parentid + '"]').length) {
                                $parent.filter('.variation').addClass('variation_row');
                            }

                        } else {
                            $('table.woocommerce_order_items tbody#order_line_items').append(response);
                        }


                        if (!--count) {
                            wc_pos_barcode_options.fields_to_print();
                            wc_pos_barcode_options.init_tiptip();
                            wc_pos_barcode_options.unblock();
                        }
                    });

                });

            }
        },
        add_item_variations: function () {
            var $table = $('table.woocommerce_order_items');
            var $rows = $table.find('tr.variable.selected');
            var prid = [];
            if ($rows.length) {
                wc_pos_barcode_options.block();
                $rows.each(function (index, el) {
                    var id = parseInt($(el).data('prid'));
                    prid.push(id);
                });

                var data = {
                    action: 'wc_pos_get_product_variations_for_barcode',
                    prid: prid,
                    security: wc_pos_barcode.product_for_barcode_nonce
                };

                $.post(wc_pos_barcode.ajax_url, data, function (response) {

                    var add_item_ids = typeof response != 'object' ? jQuery.parseJSON(response) : response;
                    wc_pos_barcode_options.add_items(add_item_ids);
                });

            }
        }
    }
    wc_pos_barcode_options.init();
});