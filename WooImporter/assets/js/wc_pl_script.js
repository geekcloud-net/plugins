var wpeae_reload_page_after_ajax = false;
(function ($, window, document, undefined) {
    $(function () {

        $(document).on("click", ".wpeae-product-info", function () {
            var id = $(this).attr('id').split('-')[1];
            $.wpeae_show(id);
            return false;
        });

        $.wpeae_show = function (id) {
            $('<div id="wpeae-dialog' + id + '"></div>').dialog({
                dialogClass: 'wp-dialog',
                modal: true,
                title: "WooImporter Info (ID: " + id + ")",
                open: function () {
                    $('#wpeae-dialog' + id).html(wpeae_wc_pl_script.lang.please_wait_data_loads);
                    var data = {'action': 'wpeae_product_info', 'id': id};

                    $.post(ajaxurl, data, function (response) {
                        //console.log('response: ', response);
                        var json = jQuery.parseJSON(response);
                        //console.log('result: ', json);

                        if (json.state === 'error') {

                            console.log(json);

                        } else {
                            //console.log(json);
                            $('#wpeae-dialog' + json.data.id).html(json.data.content.join('<br/>'));
                        }

                    });


                },
                close: function (event, ui) {
                    $("#wpeae-dialog" + id).remove();
                },
                buttons: {
                    Ok: function () {
                        $(this).dialog("close");
                    }
                }
            });

            return false;

        };

        jQuery("#doaction, #doaction2").click(function () {
            var check_action = ($(this).attr('id') == 'doaction') ? $('#bulk-action-selector-top').val() : $('#bulk-action-selector-bottom').val();

            if ('wpeae_product_update_manual' === check_action) {
                wpeae_reload_page_after_ajax = true;
                $("#wpeae_update_process_loader").remove();
                var num_to_update = $('input:checkbox[name="post[]"]:checked').length;
                if (num_to_update > 0) {
                    $("#posts-filter .tablenav.top").after('<div id="wpeae_update_process_loader">Process update 0 of ' + num_to_update + '.</div>');

                    var update_cnt = 0;
                    var update_error_cnt = 0;
                    var update_cnt_total = 0;

                    $('input:checkbox[name="post[]"]:checked').each(function () {
                        var data = {'action': 'wpeae_update_goods', 'post_id': $(this).val()};
                        $.post(ajaxurl, data, function (response) {
                            var json = $.parseJSON(response);
                            //console.log('result: ', json);
                            if (json.state === 'error') {
                                console.log(json);
                                update_error_cnt++;
                            } else {
                                if (jQuery.isArray(json.js_hook)) {
                                    jQuery.each(json.js_hook, function (index, value) {
                                        eval(value.name)(value.params);
                                    });
                                }
                                update_cnt++;
                            }
                            update_cnt_total++;


                            jQuery("#wpeae_update_process_loader").html(sprintf(wpeae_wc_pl_script.lang.process_update_d_of_d_erros_d, update_cnt, num_to_update, update_error_cnt));

                            if (update_cnt_total === num_to_update) {
                                jQuery("#wpeae_update_process_loader").html(sprintf(wpeae_wc_pl_script.lang.complete_result_updated_d_erros_d, update_cnt, update_error_cnt));
                            }
                        });
                    });
                }

                return false;
            }
            return true;
        });
    });

})(jQuery, window, document);
