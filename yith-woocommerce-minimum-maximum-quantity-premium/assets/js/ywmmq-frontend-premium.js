jQuery(function ($) {

    if (ywmmq.variations) {
        
        $(document).init(function ($) {

            var product_id = parseInt($('.single_variation_wrap .product_id, .single_variation_wrap input[name="product_id"]').val()),
                variation_id = parseInt($('.single_variation_wrap .variation_id, .single_variation_wrap input[name="variation_id"]').val());

            if (!isNaN(product_id) && !isNaN(variation_id)) {

                get_variation_rules(product_id, variation_id);

            }

        });


        $(document).on('woocommerce_variation_has_changed', function () {

            var product_id = parseInt($('.single_variation_wrap .product_id, .single_variation_wrap input[name="product_id"]').val()),
                variation_id = parseInt($('.single_variation_wrap .variation_id, .single_variation_wrap input[name="variation_id"]').val());

            if (!isNaN(product_id) && !isNaN(variation_id)) {

                get_variation_rules(product_id, variation_id);

            }

        });

    }

    function get_variation_rules(product_id, variation_id) {

        var container = $('.ywmmq-rules-wrapper'),
            variations_form = $('.variations_form ');

        if (variations_form.is('.processing')) {
            return false;
        }

        variations_form.addClass('processing');

        variations_form.block({
            message   : null,
            overlayCSS: {
                background: '#fff',
                opacity   : 0.6
            }
        });

        $.ajax({
            type    : 'POST',
            url     : ywmmq.ajax_url,
            data    : {
                action      : 'ywmmq_get_rules',
                product_id  : product_id,
                variation_id: variation_id
            },
            success : function (response) {

                if (response.status == 'success') {

                    container.html(response.rules);

                    if (response.limits.max != 0) {

                        $('.single_variation_wrap .quantity input[name="quantity"]').attr('max', response.limits.max);

                    } else {

                        $('.single_variation_wrap .quantity input[name="quantity"]').removeAttr('max');

                    }

                    if (response.limits.min != 0) {

                        $('.single_variation_wrap .quantity input[name="quantity"]').attr('min', response.limits.min).val(response.limits.min);

                    } else {

                        $('.single_variation_wrap .quantity input[name="quantity"]').attr('min', 1).val(1);

                    }

                    if (response.limits.step != 0) {

                        $('.single_variation_wrap .quantity input[name="quantity"]').attr('step', response.limits.step);

                    } else {

                        $('.single_variation_wrap .quantity input[name="quantity"]').attr('step', 1).val(1);

                    }


                    $(document).trigger('ywmmq_additional_operations', [response.limits.min]);


                } else {

                    container.html();

                }

                variations_form.removeClass('processing').unblock();

            },
            dataType: 'json'
        });

        return false;

    }

});