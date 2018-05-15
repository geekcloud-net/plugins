var order_code_scanned = false;
jQuery(document).ready(function ($) {
    /******************************
     ****** Shop Order Page *******
     ******************************/
    if ($('.order_actions a.reprint_receipts').length > 0) {

        $('.order_actions a.reprint_receipts').click(function () {

            var url = $(this).attr("href");
            var start_print = false;
            var print_document = '';
            $.get(url + '#print_receipt', function (data) {
                print_document = data;
                if ($('#printable').length)
                    $('#printable').remove();
                var newHTML = $('<div id="printable">' + print_document + '</div>');

                $('body').addClass('print_receipt').append(newHTML);
                if ($('#print_barcode img').length) {
                    var src = $('#print_barcode img').attr('src');
                    if (src != '') {
                        $("<img>").load(function () {
                            window.print();
                            $('#printing_receipt').hide();
                        }).attr('src', src);
                    } else {
                        window.print();
                        $('#printing_receipt').hide();
                    }
                }
                else if ($('#print_receipt_logo').length) {
                    var src = $('#print_receipt_logo').attr('src');
                    if (src != '') {
                        $("<img>").load(function () {
                            window.print();
                            $('#printing_receipt').hide();
                        }).attr('src', src);
                    } else {
                        window.print();
                        $('#printing_receipt').hide();
                    }
                }
                else {
                    window.print();
                    $('#printing_receipt').hide();
                }
            });
            return false;
        });
    }

    if ($('body').hasClass('post-type-shop_order') && $('#posts-filter').length) {
        $(document).on('keydown', function (event) {
            if (order_code_scanned === true) {
                return false;
            }
        });

        $('div.wrap > a').first().after('<span class="add-new-h2 scan_order">Scan order</span>');
        $('body').on('click', '.add-new-h2.scan_order', function (event) {
            $(this).trigger('blur');
            if (order_code_scanned === false) {

                $('body').block({message: null});

                var code = prompt('Please scan the code');
                if (code != null && code != 'null' && code != '') {
                    order_code_scanned = true;

                    $.ajax({
                        type: 'GET',
                        url: wc_pos_params.ajax_url + '?action=wc_pos_search_order_by_code',
                        data: {
                            code: code
                        },
                        success: function (response) {
                            if (response && response != 'error') {
                                //alert(response);
                                var find = '&amp;';
                                var re = new RegExp(find, 'g');
                                var url = response.replace(re, '&');
                                window.location.href = url;
                            } else {
                                $('body').unblock();
                                order_code_scanned = false;
                            }
                        },
                        error: function (response) {
                            $('body').unblock();
                            order_code_scanned = false;
                        }
                    });
                } else {
                    $('body').unblock();
                    order_code_scanned = false;
                }
            }
            return false;
        });
    }
});