jQuery(document).ready(function ($) {
    $('#receipt_print_outlet_contact_details').change(function () {
        if ($(this).is(':checked')) {
            $('.show_receipt_print_outlet_contact_details').show();
        } else {
            $('.show_receipt_print_outlet_contact_details').hide();
        }
    }).trigger('change');

    $('#receipt_print_outlet_address').change(function () {
        if ($(this).is(':checked')) {
            $('.show_receipt_print_outlet_address').show();
        } else {
            $('.show_receipt_print_outlet_address').hide();
        }
    }).trigger('change');

    $('#receipt_print_order_time').change(function () {
        if ($(this).is(':checked')) {
            $('#print_order_time').show();
            $('.show_receipt_print_order_time').show();
        } else {
            $('#print_order_time').hide();
            $('.show_receipt_print_order_time').hide();
        }
    }).trigger('change');

    $('#receipt_show_image_product').change(function () {
        if ($(this).is(':checked')) {
            $('table.receipt_items tr .column-product-image').show();
        } else {
            $('table.receipt_items tr .column-product-image').hide();
        }
    }).trigger('change');

    $('#show_outlet').change(function () {
        if ($(this).is(':checked')) {
            $('.outlet_name').show();
        } else {
            $('.outlet_name').hide();
        }
    }).trigger('change');

    $('#show_register').change(function () {
        if ($(this).is(':checked')) {
            $('.register_name').show();
        } else {
            $('.register_name').hide();
        }
    }).trigger('change');

    $('#show_site_name').change(function () {
        if ($(this).is(':checked')) {
            $('.site_name').show();
        } else {
            $('.site_name').hide();
        }
    }).trigger('change');

    $('#receipt_print_tax_summary').change(function () {
        if ($(this).is(':checked')) {
            $('.pos_receipt_tax_summary').show();
        } else {
            $('.pos_receipt_tax_summary').hide();
        }
    }).trigger('change');

    $('#show_sku').change(function () {
        if ($(this).is(':checked')) {
            $('table.receipt_items .sku').show();
        } else {
            $('table.receipt_items .sku').hide();
        }
    }).trigger('change');

    $('#receipt_print_server').change(function () {
        if ($(this).is(':checked')) {
            $('.show_receipt_print_server').show();
            $('#print_server').show();
            $('.show_receipt_print_register_name_served').show();
        } else {
            $('#print_server').hide();
            $('.show_receipt_print_server').hide();
            $('.show_receipt_print_register_name_served').hide();
        }
    }).trigger('change');

    $('#receipt_print_number_items').change(function () {
        if ($(this).is(':checked')) {
            $('#print_number_items').show();
            $('.show_receipt_print_number_items').show();
        } else {
            $('#print_number_items').hide();
            $('.show_receipt_print_number_items').hide();
        }
    }).trigger('change');

    $('#receipt_print_barcode').change(function () {
        if ($(this).is(':checked')) {
            $('#pos_receipt_barcode').show();
        } else {
            $('#pos_receipt_barcode').hide();
        }
    }).trigger('change');

    $('#receipt_print_tax_number').change(function () {
        if ($(this).is(':checked')) {
            $('#pos_receipt_tax').show();
            $('.show_receipt_print_tax_number').show();
        } else {
            $('#pos_receipt_tax').hide();
            $('.show_receipt_print_tax_number').hide();
        }
    }).trigger('change');

    $('#receipt_print_order_notes').change(function () {
        if ($(this).is(':checked')) {
            $('#print_order_notes').show();
            $('.show_receipt_print_order_notes').show();
        } else {
            $('#print_order_notes').hide();
            $('.show_receipt_print_order_notes').hide();
        }
    }).trigger('change');

    $('#receipt_print_customer_name').change(function () {
        if ($(this).is(':checked')) {
            $('#print_customer_name').show();
            $('.show_receipt_print_customer_name').show();
        } else {
            $('#print_customer_name').hide();
            $('.show_receipt_print_customer_name').hide();
        }
    }).trigger('change');

    $('#receipt_print_customer_email').change(function () {
        if ($(this).is(':checked')) {
            $('#print_customer_email').show();
            $('.show_receipt_print_customer_email').show();
        } else {
            $('#print_customer_email').hide();
            $('.show_receipt_print_customer_email').hide();
        }
    }).trigger('change');

    $('#receipt_print_customer_phone').change(function () {
        if ($(this).is(':checked')) {
            $('#print_customer_phone').show();
            $('.show_receipt_print_customer_phone').show();
        } else {
            $('#print_customer_phone').hide();
            $('.show_receipt_print_customer_phone').hide();
        }
    }).trigger('change');

    $('#receipt_print_customer_ship_address').change(function () {
        if ($(this).is(':checked')) {
            $('#print_customer_ship_address').show();
            $('.show_receipt_print_customer_ship_address').show();
        } else {
            $('#print_customer_ship_address').hide();
            $('.show_receipt_print_customer_ship_address').hide();
        }
    }).trigger('change');


    /********/

    $('#receipt_telephone_label').on('change', function () {
        var val = $(this).val();
        $('#print-telephone_label').html(val);
        if (val == '')
            $('#print-telephone_label').next('.colon').hide();
        else
            $('#print-telephone_label').next('.colon').show();
    }).trigger('change');

    $('#receipt_fax_label').on('change', function () {
        var val = $(this).val();
        $('#print-fax_label').html(val);
        if (val == '')
            $('#print-fax_label').next('.colon').hide();
        else
            $('#print-fax_label').next('.colon').show();
    }).trigger('change');

    $('#receipt_email_label').on('change', function () {
        var val = $(this).val();
        $('#print-email_label').html(val);
        if (val == '')
            $('#print-email_label').next('.colon').hide();
        else
            $('#print-email_label').next('.colon').show();
    }).trigger('change');

    $('#receipt_website_label').on('change', function () {
        var val = $(this).val();
        $('#print-website_label').html(val);
        if (val == '')
            $('#print-website_label').next('.colon').hide();
        else
            $('#print-website_label').next('.colon').show();
    }).trigger('change');

    $('#receipt_receipt_title').on('change', function () {
        var val = $(this).val();
        $('#pos_receipt_title').html(val);
    }).trigger('change');

    $('#receipt_order_number_label').on('change', function () {
        var val = $(this).val();
        $('#print-order_number_label').html(val);
        if (val == '')
            $('#print-order_number_label').next('.colon').hide();
        else
            $('#print-order_number_label').next('.colon').show();
    }).trigger('change');

    $('#receipt_order_date_label').on('change', function () {
        var val = $(this).val();
        $('#print-order_date_label').html(val);
        if (val == '')
            $('#print-order_date_label').next('.colon').hide();
        else
            $('#print-order_date_label').next('.colon').show();
    }).trigger('change');

    $('#receipt_served_by_label').on('change', function () {
        var val = $(this).val();
        $('#print-served_by_label').html(val);
    }).trigger('change');

    $('#receipt_served_by_type').on('change', function () {
        var type = $(this).val();
        var val = typeof $('#print-served_by_type').data(type) != 'undefined' ? $('#print-served_by_type').data(type) : $('#print-served_by_type').text();
        $('#print-served_by_type').html(val);
    }).trigger('change');

    $('#receipt_tax_label').on('change', function () {
        var val = $(this).val();
        if (val == '')
            $('#print-tax_label').html(val);
        else
            $('#print-tax_label').html(val);
    }).trigger('change');

    $('#receipt_tax_label').on('change', function () {
        var val = $(this).val();
        if (val == '')
            $('#print-tax_label_summary').html(val);
        else
            $('#print-tax_label_summary').html(val);
    }).trigger('change');

    $('#receipt_tax_label').on('change', function () {
        var val = $(this).val();
        if (val == '')
            $('#print-tax_label_rate').html(val);
        else
            $('#print-tax_label_rate').html(val);
    }).trigger('change');

    $('#receipt_tax_label').on('change', function () {
        var val = $(this).val();
        if (val == '')
            $('#print-tax_label_perc').html(val);
        else
            $('#print-tax_label_perc').html(val);
    }).trigger('change');

    $('#receipt_tax_label').on('change', function () {
        var val = $(this).val();
        if (val == '')
            $('#print-tax_label_tax').html(val);
        else
            $('#print-tax_label_tax').html(val);
    }).trigger('change');

    $('#receipt_total_label').on('change', function () {
        var val = $(this).val();
        $('#print-total_label').html(val);
    }).trigger('change');

    $('#receipt_payment_label').on('change', function () {
        var val = $(this).val();
        $('#print-payment_label').html(val);
    }).trigger('change');

    $('#receipt_items_label').on('change', function () {
        var val = $(this).val();
        $('#print-items_label').html(val);
    }).trigger('change');

    $('#receipt_tax_number_label').on('change', function () {
        var val = $(this).val();
        $('#print-tax_number_label').html(val);
        if (val == '')
            $('#print-tax_number_label').next('.colon').hide();
        else
            $('#print-tax_number_label').next('.colon').show();
    }).trigger('change');

    $('#receipt_order_notes_label').on('change', function () {
        var val = $(this).val();
        $('#print-order_notes_label').html(val);
    }).trigger('change');

    $('#receipt_customer_name_label').on('change', function () {
        var val = $(this).val();
        $('#print-customer_name_label').html(val);
    }).trigger('change');

    $('#receipt_customer_email_label').on('change', function () {
        var val = $(this).val();
        $('#print-customer_email_label').html(val);
    }).trigger('change');

    $('#receipt_customer_ship_address_label').on('change', function () {
        var val = $(this).val();
        $('#print-customer_ship_address_label').html(val);
    }).trigger('change');
    $('#show_cost').change(function () {
        if ($(this).is(':checked')) {
            $('table.receipt_items .cost').show();
        } else {
            $('table.receipt_items .cost').hide();
        }
    }).trigger('change');

    /********/

    $('#receipt_header_text').on('change', function () {
        $('#pos_receipt_header').html($(this).val());
    }).trigger('change');

    $('#receipt_footer_text').on('change', function () {
        $('#pos_receipt_footer').html($(this).val());
    }).trigger('change');

    setTimeout(function () {
        $("#receipt_header_text_ifr").contents().find('body').keyup(function () {
            $('#pos_receipt_header').html($(this).html());
        });
    }, 2000);

    setTimeout(function () {
        $("#receipt_footer_text_ifr").contents().find('body').keyup(function () {
            $('#pos_receipt_footer').html($(this).html());
        });
    }, 2000);

    // Uploading files
    var file_frame;
    var current_shape_image;
    $('.set_receipt_logo').click(function () {

        // If the media frame already exists, reopen it.
        if (file_frame) {
            file_frame.open();
            return;
        }

        // Create the media frame.
        file_frame = wp.media.frames.file_frame = wp.media({
            title: "Select a Receipt Logo ",
            button: {
                text: "Set Receipt Logo",
            },
            multiple: false,
        });

        // When an image is selected, run a callback.
        file_frame.on('select', function () {
            // We set multiple to false so only get one image from the uploader
            attachment = file_frame.state().get('selection').first().toJSON();

            // Set the image id/display the image thumbnail
            $('#receipt_logo').val(attachment.id);
            $('#print_receipt_logo').attr('src', attachment.url);
            $('#set_receipt_logo_img img').attr('src', attachment.sizes.thumbnail.url);

            $('#set_receipt_logo_img, .remove_receipt_logo, #print_receipt_logo').show();
            $('#set_receipt_logo_text, #receipt_logo_placeholder').hide();

        });

        // Finally, open the modal
        file_frame.open();
        return false;
    });

    $('.remove_receipt_logo').click(function () {

        $('#set_receipt_logo_img, .remove_receipt_logo, #print_receipt_logo').hide();
        $('#set_receipt_logo_text, #receipt_logo_placeholder').show();

        $('#receipt_logo').val('');
        $('#print_receipt_logo').attr('src', '');
        $('#set_receipt_logo_img img').attr('src', '');
        return false;
    });

    $('select.wc_pos_receipt').on('change', function (event) {
        var style = '';
        $('select.wc_pos_receipt').each(function (index, el) {
            var id = $(this).attr('id');
            var val = $(this).val();
            if (typeof wc_pos_receipt.pos_receipt_style[id] != 'undefined' && typeof wc_pos_receipt.pos_receipt_style[id][val] != 'undefined')
                style += wc_pos_receipt.pos_receipt_style[id][val];
        });
        $('#receipt_style_tag').replaceWith('<style id="receipt_style_tag">' + style + '</style>');
    }).first().trigger('change');

    postboxes.add_postbox_toggles();


    $("input[name='receipt_order_date_format']").click(function () {
        if ("date_format_custom_radio" != $(this).attr("id"))
            $("input[name='receipt_order_date_format_custom']").val($(this).val()).siblings('.example').text($(this).parent('label').text());
    });
    $("input[name='receipt_order_date_format_custom']").focus(function () {
        $('#date_format_custom_radio').prop('checked', true);
    });

    $("input[name='receipt_order_date_format_custom']").change(function () {
        var format = $(this);
        format.siblings('.spinner').addClass('is-active');
        $.post(ajaxurl, {
            action: 'receipt_order_date_format_custom' == format.attr('name') ? 'date_format' : 'time_format',
            date: format.val()
        }, function (d) {
            format.siblings('.spinner').removeClass('is-active');
            format.siblings('.example').text(d);
        });
    });

    var editor = CodeMirror.fromTextArea(document.getElementById("receipt_custom_css"), {
        lineNumbers: true,
        mode: "css",
    });

    jQuery('#socials_display_option').on('change', function () {
        switch (jQuery(this).val()) {
            case 'none':
                jQuery('.display-socials').hide();
                jQuery('.social').hide();
                break;
            case 'header':
                jQuery('.social').show();
                jQuery('.display-socials.footer').hide();
                jQuery('.display-socials.header').show();
                break;
            case 'footer':
                jQuery('.display-socials.footer').show();
                jQuery('.display-socials.header').hide();
                jQuery('.social').show();
                break;
        }
    }).trigger('change');

    jQuery('#show_twitter').on('change', function () {
        if ($(this).is(':checked')) {
            $('.display-twitter').show();
        } else {
            $('.display-twitter').hide();
        }
    })
    jQuery('#show_facebook').on('change', function () {
        if ($(this).is(':checked')) {
            $('.display-facebook').show();
        } else {
            $('.display-facebook').hide();
        }
    })
    jQuery('#show_instagram').on('change', function () {
        if ($(this).is(':checked')) {
            $('.display-instagram').show();
        } else {
            $('.display-instagram').hide();
        }
    })
    jQuery('#show_snapchat').on('change', function () {
        if ($(this).is(':checked')) {
            $('.display-snapchat').show();
        } else {
            $('.display-snapchat').hide();
        }
    })
});