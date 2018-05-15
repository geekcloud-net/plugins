var runQuantityKeypad;
var calculateDiscountKeyPad,
    calculateQuantity,
    calculateSelectedQuantity,
    calculateSelectedPrice,
    calculateShippingPrice;
var product_price;

var amount_tendered = {
    'tendered_1': '0.00',
    'tendered_2': '',
    'tendered_3': '',
    'tendered_4': '',
};
var tendered_num = {
    'tendered_1': '0.00',
    'tendered_2': '',
    'tendered_3': '',
    'tendered_4': '',
};
var currency_symbol = jQuery('.tendered_change_cod .amount_change .currency_symbol').text();

jQuery(document).ready(function ($) {
    runQuantityKeypad = function (el, mode, _return) {
        var args = {
            keypadOnly: false,
            keypadClass: 'quantity_keypad doublez',
            separator: '|',
            layout: [$.keypad.MINUS + '|' + $.keypad.INPVAL + '|' + $.keypad.PLUS, '1|2|3|' + $.keypad.CLEAR, '4|5|6|' + $.keypad.BACK, '7|8|9|0'],
            closeText: '',
            plusText: '+',
            minusText: '-',
            inpvalText: '1',
            minusStatus: 'Minus',
            plusStatus: 'Plus',
            onKeypress: function (key, value, inst) {
                if (value == '') {
                    value = 1;
                }
                var q = parseInt(value);
                if (wc_pos_params.decimal_quantity == 'yes') {
                    q = parseFloat(value);
                }
                if (q <= 0) {
                    q = 1;
                }
                $(inst._mainDiv).find('.keypad-inpval').text(q);
            },
            beforeShow: function (div, inst) {
                $(inst._mainDiv).find('.keypad-inpval').text(1);
            },
        };
        switch (mode) {
            case 'min':
                args.layout = [$.keypad.MINUS + '|' + $.keypad.INPVAL + '|' + $.keypad.PLUS];
                args.keypadClass = 'quantity_keypad';
                break;
            case 'full':
                args.layout = [$.keypad.MINUS + '|' + $.keypad.INPVAL + '|' + $.keypad.PLUS, '1|2|3|' + $.keypad.CLEAR, '4|5|6|' + $.keypad.BACK, '7|8|9|' + $.keypad.CLOSE, '0|00'];
                if (_return == false) {
                    args.layout = [$.keypad.MINUS + '|' + $.keypad.INPVAL + '|' + $.keypad.PLUS, '1|2|3|' + $.keypad.CLEAR, '4|5|6|' + $.keypad.BACK, '7|8|9|0'];
                    if (wc_pos_params.decimal_quantity == 'yes') {
                        args.layout = [$.keypad.MINUS + '|' + $.keypad.INPVAL + '|' + $.keypad.PLUS, '1|2|3|' + $.keypad.CLEAR, '4|5|6|' + $.keypad.BACK, '7|8|9', '0|00|.'];
                    }
                    args.keypadClass = 'quantity_keypad';
                }
                break;
            default:
                args.layout = ['1|2|3|' + $.keypad.CLEAR, '4|5|6|' + $.keypad.BACK, '7|8|9|' + $.keypad.CLOSE, '0|00'];
                if (_return == false) {
                    args.layout = ['1|2|3|' + $.keypad.CLEAR, '4|5|6|' + $.keypad.BACK, '7|8|9|0'];
                    args.keypadClass = 'quantity_keypad';
                }
                delete args.prompt;
                break;
        }
        el.keypad(args);
    }


    calculateQuantity = function (input, action) {
        var val = $(input).val();
        if (val == '') val = 1;
        val = parseInt(val);
        if (action == 'plus') {
            val = val + 1;
        }
        if (action == 'minus') {
            val = val - 1;
            if (val < 1) val = 1;
        }

        $('.quantity_keypad .keypad-inpval').text(val);
        if ($('#var_tip input.quantity').length)
            $('#var_tip input.quantity').val(val);

        $(input).val(val);
    }

    calculateSelectedQuantity = function () {
        var layout = [$.keypad.MINUS + '|' + $.keypad.INPVAL + '|' + $.keypad.PLUS, '1|2|3|' + $.keypad.CLEAR, '4|5|6|' + $.keypad.BACK, '7|8|9|' + $.keypad.CLOSE, '0|00|000'];
        if (wc_pos_params.decimal_quantity == 'yes') {
            layout = [$.keypad.MINUS + '|' + $.keypad.INPVAL + '|' + $.keypad.PLUS, '1|2|3|' + $.keypad.CLEAR, '4|5|6|' + $.keypad.BACK, '7|8|9|' + $.keypad.CLOSE, '0|00|.'];
        }
        $('#order_items_list .new_row .quantity .quantity').keypad({
            keypadOnly: false,
            keypadClass: 'quantity_keypad',
            separator: '|',
            layout: layout,
            closeText: '',
            plusText: '+',
            minusText: '-',
            inpvalText: '0',
            minusStatus: 'Minus',
            plusStatus: 'Plus',
            onKeypress: function (key, value, inst) {
                if (value == '') {
                    value = 1;
                }
                var q = parseInt(value);
                if (wc_pos_params.decimal_quantity == 'yes') {
                    q = parseFloat(value);
                }
                if (q <= 0) {
                    q = 1;
                }
                $(inst._mainDiv).find('.keypad-inpval').text(q);
                changeProductQuantity($(inst.elem));
            },
            beforeShow: function (div, inst) {
                $(inst._mainDiv).find('.keypad-inpval').text(this.value);
            },
            onClose: function (value, inst) {
                if (value == '') {
                    value = 1;
                }
                var q = parseInt(value);
                if (wc_pos_params.decimal_quantity == 'yes') {
                    q = parseFloat(value);
                }
                this.value = q;
                changeProductQuantity($(inst.elem));
            }
        }).click(function () {
            this.select();
        }).keypress(function (e) {

            if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57 ) && e.which != 46) {
                return false;
            }
            if (e.which == 46 && wc_pos_params.decimal_quantity == 'no') {
                return false;
            }
        }).keyup(function (ev) {
            changeProductQuantity($(this));
        }).keydown(function (ev) {
            $('.keypad-popup').hide();
            if (ev.which == 13) {
                ev.preventDefault();
            }
        });

    };

    calculateSelectedPrice = function () {
        var product_line_id;
        $('#order_items_list .new_row .product_price').keypad({
            keypadOnly: false,
            separator: '|',
            layout: [
                '1|2|3|' + $.keypad.CLEAR + '|' + $.keypad.FIVEPERCENT,
                '4|5|6|' + $.keypad.BACK + '|' + $.keypad.TENPERCENT,
                '7|8|9|' + $.keypad.CLOSE + '|' + $.keypad.FIFTEENPERCENT,
                '0|.|00|' + $.keypad.TWENTYPERCENT
            ],
            closeText: '',
            fivepercentText: wc_pos_params.discount_presets[0] + '%',
            tenpercentText: wc_pos_params.discount_presets[1] + '%',
            fifteenpercentText: wc_pos_params.discount_presets[2] + '%',
            twentypercentText: wc_pos_params.discount_presets[3] + '%',
            onKeypress: function (key, value, inst) {
                var arr = value.split('.');
                if (arr.length > 2) {
                    var l = arr[0].length;
                    var new_v = value.substr(l);
                    new_v = new_v.replace(/\./g, "");
                    value = arr[0] + '.' + new_v;
                    $(inst.elem).val(value);
                }
                $(inst._mainDiv).find('#discount-value').val(this.value);
                //changeProductPrice($(inst.elem));
            },
            beforeShow: function (div, inst, value) {
                var input_val = jQuery(this).val();
                product_price = input_val;
                $('<div class="keypad-row"><div class="disc-type pound active button keypad-key" data-value="0">' + wc_pos_params.currency_format_symbol + '</div><input type="text" id="discount-value" data-row="' + jQuery(this).data('row') + '" value="' + input_val + '"><div class="disc-type percents button keypad-key" data-value="0">%</div><div class="button restore-price"></div></div>').prependTo(div);
                jQuery('#discount-value').select();
                hideEmptyDiscounts();
            },
            onClose: function (value, inst) {
                var row_id = jQuery('#discount-value').data('row');
                var price_input = jQuery('#item_note-' + row_id).find('.product_price');
                var cur_value = CART.cart_contents[row_id].price;
                if (jQuery('.disc-type.active').hasClass('pound')) {
                    jQuery('.disc-type.pound').data('value', value);
                } else {
                    percents_val = parseFloat(value);
                    value = cur_value - cur_value * (value / 100);
                    jQuery('#discount-value').val(value);
                    jQuery('.disc-type.pound').data('value', value);
                }
                price_input.val(parseFloat(value).toFixed(2));
                changeProductPrice($(inst.elem));
            }
        }).click(function () {
            product_line_id = jQuery(this).parents('tr.item').attr('id');
            jQuery('#discount-value').data('row', product_line_id);
            this.select();
        }).keypress(function (e) {
            if (e.which != 8 && e.which != 0 && e.which != 46 && (e.which < 48 || e.which > 57)) {
                return false;
            }
            if (e.which === 46 && $(this).val().split('.').length === 2) {
                return false;
            }
        }).keydown(function (ev) {
            $('.keypad-popup').hide();
            if (ev.which == 13) {
                ev.preventDefault();
            }
        });
        jQuery('body').on('click', '.disc-type:not(".active")', function () {
            var value = jQuery('#discount-value').val();
            jQuery('.disc-type.active').removeClass('active');
            jQuery(this).addClass('active');
            jQuery('#discount-value').val(0);
            jQuery('#discount-value').select();
        });
    };

    calculateShippingPrice = function () {
        $('#custom_shipping_table tbody #custom_shipping_price').keypad({
            keypadOnly: false,
            separator: '|',
            layout: ['1|2|3|' + $.keypad.CLEAR, '4|5|6|' + $.keypad.BACK, '7|8|9|' + $.keypad.CLOSE, '0|.|00'],
            closeText: '',
            onKeypress: function (key, value, inst) {
                var arr = value.split('.');
                if (arr.length > 2) {
                    var l = arr[0].length;
                    var new_v = value.substr(l);
                    new_v = new_v.replace(/\./g, "");
                    value = arr[0] + '.' + new_v;
                    $(inst.elem).val(value);
                }
            }
        }).click(function () {
            this.select();
        }).keypress(function (e) {
            if (e.which != 8 && e.which != 0 && e.which != 46 && (e.which < 48 || e.which > 57)) {
                return false;
            }
            if (e.which === 46 && $(this).val().split('.').length === 2) {
                return false;
            }
        }).keydown(function (ev) {
            $('.keypad-popup').hide();
            if (ev.which == 13) {
                ev.preventDefault();
            }
        });
    }

    calculateDiscountKeyPad = function () {
        var $keyentry = $('#inline_order_discount .keypad-keyentry');
        var discount_prev = $keyentry.val();

        var discount_val = 0;
        var percent = 0;

        var total = 0;
        if (typeof CART != 'undefined') {
            var discount_cart = CART.discount_cart;
            if (typeof CART._coupon_discount_amounts['POS Discount'] != 'undefined') {
                var coupon_discount_amounts = CART._coupon_discount_amounts['POS Discount'];
            } else {
                var coupon_discount_amounts = 0;
            }

            if (pos_wc.calc_discounts_seq === 'yes') {
                var total = CART.subtotal_ex_tax - round(discount_cart, pos_wc.precision);
            } else {
                var total = CART.subtotal_ex_tax - round(discount_cart - coupon_discount_amounts, pos_wc.precision);
            }
        } else {
            var total = 0;
        }

        //if(total == '') total = 0.00;
        //else total = parseFloat(total).toFixed(2);
        var symbol = $('#order_discount_symbol').val();

        /*if ( typeof CART != 'undefined') {
         var amount = CART.get_discount_amount( 'POS Discount', CART.display_cart_ex_tax );
         } else {
         var amount = 0;
         }*/

        $('#order_discount_prev').val(discount_prev);

        if (discount_prev && total) {
            discount_prev = parseFloat(discount_prev.replace("/\%/g", "")).toFixed(2);
            if (symbol == 'percent_symbol') {
                var price = $('#order_discount_prev').val();
                percent = parseFloat(discount_prev);
                discount_val = (total * percent / 100);
                discount_val = accountingPOS(discount_val, 'formatNumber');
                if (price.indexOf(".") >= 1 && String(percent).indexOf('.') === -1) {
                    percent = String(percent + '.');
                }
                $('#order_discount_prev').val(percent);
                $keyentry.val(percent);
                $('.discount_keypad .keypad-discount_val1').text(discount_val);
                $('.discount_keypad .keypad-discount_val2').text(percent);
            } else {
                var price = $('#order_discount_prev').val();
                if (price != '' && price != parseInt(price)) {
                    var num_after_dot = price.length - price.indexOf(".") - 1;
                    if (num_after_dot > wc_pos_params.currency_format_num_decimals) {
                        discount_val = accountingPOS(price, 'formatNumber');
                        discount_prev = price;
                        $('#order_discount_prev').val(discount_prev);
                        $keyentry.val(discount_prev);
                    }
                }
                percent = (discount_prev * 100 / total).toFixed(2);
                discount_val = discount_prev;
                $('.discount_keypad .keypad-discount_val1').text(percent + '% off ');
                $('.discount_keypad .keypad-discount_val2').text(discount_val);
            }
        } else {
            if (symbol == 'percent_symbol') {
                discount_prev = parseInt(discount_prev);
                if (isNaN(discount_prev))
                    discount_prev = 0;

                $('#order_discount_prev').val(discount_prev);
                $keyentry.val(discount_prev);

                $('.discount_keypad .keypad-discount_val1').text('0.00');
                $('.discount_keypad .keypad-discount_val2').text(discount_prev);
            } else {
                var price = $('#order_discount_prev').val();
                if (price != '' && price != parseInt(price)) {
                    var num_after_dot = price.length - price.indexOf(".") - 1;
                    if (num_after_dot > 2) {
                        price = parseFloat(price);
                        price = Math.floor(100 * price) / 100;
                        discount_prev = price;
                        $('#order_discount_prev').val(discount_prev);
                        $keyentry.val(discount_prev);
                    }
                }
                $('.discount_keypad .keypad-discount_val1').text('0% off ');
                $('.discount_keypad .keypad-discount_val2').text(discount_prev);
            }
        }
    };

    function calculateFeeKeypad() {
        var val = $('#custom_fee_value .keypad-keyentry').val();
        $('.custom_fee .keypad-fee_val').text(val);
    }

    $.keypad.addKeyDef('CLEAR', 'clear', function () {
        jQuery('.disc-type.active').removeClass('active');
        jQuery('.disc-type.pound').addClass('active');
        jQuery('#discount-value').val(0.00);
        jQuery('#discount-value').select();
    });

    $.keypad.setDefaults({
        showAnim: '',
        duration: 'fast'
    });

    $.keypad.addKeyDef('PLUS', 'plus', function (inst) {
        var value = this.val();
        if (value == '') {
            value = 1;
        }
        var q = parseInt(value) + 1;
        $(inst._mainDiv).find('.keypad-inpval').text(q);
        this.val(q);
        this.focus();
    });
    $.keypad.addKeyDef('MINUS', 'minus', function (inst) {
        var value = this.val();
        if (value == '') {
            value = 1;
        }
        var q = parseInt(value) - 1;
        if (q <= 0) {
            q = 1;
        }
        $(inst._mainDiv).find('.keypad-inpval').text(q);
        this.val(q);
        this.focus();
    });

    $.keypad.addKeyDef('INPVAL', 'inpval', function (inst) {
        this.focus();
    });

    $.keypad.addKeyDef('FIVEPERCENT', 'fivepercent', function (inst) {
        $(this).val(wc_pos_params.discount_presets[0]);
        $('#order_discount_symbol').val('percent_symbol');

        $('.keypad-currency_symbol, .pound').removeClass('active');
        $('.keypad-percent_symbol, .percents').addClass('active');
        calculateDiscountKeyPad();
        this.focus();
    });

    $.keypad.addKeyDef('TENPERCENT', 'tenpercent', function (inst) {
        $(this).val(wc_pos_params.discount_presets[1]);
        $('#order_discount_symbol').val('percent_symbol');

        $('.keypad-currency_symbol, .pound').removeClass('active');
        $('.keypad-percent_symbol, .percents').addClass('active');
        calculateDiscountKeyPad();
        this.focus();
    });

    $.keypad.addKeyDef('FIFTEENPERCENT', 'fifteenpercent', function (inst) {
        $(this).val(wc_pos_params.discount_presets[2]);
        $('#order_discount_symbol').val('percent_symbol');

        $('.keypad-currency_symbol, .pound').removeClass('active');
        $('.keypad-percent_symbol, .percents').addClass('active');
        calculateDiscountKeyPad();
        this.focus();
    });

    $.keypad.addKeyDef('TWENTYPERCENT', 'twentypercent', function (inst) {
        $(this).val(wc_pos_params.discount_presets[3]);
        $('#order_discount_symbol').val('percent_symbol');

        $('.keypad-currency_symbol, .pound').removeClass('active');
        $('.keypad-percent_symbol, .percents').addClass('active');
        calculateDiscountKeyPad();
        this.focus();
    });

    $.keypad.addKeyDef('CURRENCY_SYMBOL', 'currency_symbol', function (inst) {
        $('#order_discount_symbol').val('currency_symbol');

        $('.keypad-percent_symbol').removeClass('active');
        $('.keypad-currency_symbol').addClass('active');
        calculateDiscountKeyPad();
        this.focus();
    });

    $.keypad.addKeyDef('PERCENT_SYMBOL', 'percent_symbol', function (inst) {
        $('#order_discount_symbol').val('percent_symbol');

        $('.keypad-currency_symbol').removeClass('active');
        $('.keypad-percent_symbol').addClass('active');
        calculateDiscountKeyPad();
        this.focus();
    });

    $.keypad.addKeyDef('PRICE_CURRENCY_SYMBOL', 'price_currency_symbol', function (inst) {
        this.select();
        if ($('.keypad-price_currency_symbol').hasClass('active')) return;

        var percent = $(inst.elem).data('percent');
        var modprice = $(inst.elem).data('modprice');

        $(inst.elem).val(modprice);

        $('.price_keypad .keypad-price_val1').text((percent) + '% off ');
        $('.price_keypad .keypad-price_val2').text(modprice);

        $(inst.elem).data('discountsymbol', 'currency_symbol');
        $('.keypad-price_percent_symbol').removeClass('active');
        $('.keypad-price_currency_symbol').addClass('active');

        $(inst.elem).focus();
    });

    $.keypad.addKeyDef('PRICE_PERCENT_SYMBOL', 'price_percent_symbol', function (inst) {
        this.select();
        if ($('.keypad-price_percent_symbol').hasClass('active')) return;

        var percent = $(inst.elem).data('percent');
        var modprice = $(inst.elem).data('modprice');

        $(inst.elem).val(percent);
        modprice = accountingPOS(modprice, 'formatMoney');

        $('.price_keypad .keypad-price_val1').text(modprice);
        $('.price_keypad .keypad-price_val2').text(percent);
        $(inst.elem).data('discountsymbol', 'percent_symbol');
        $('.keypad-price_currency_symbol').removeClass('active');
        $('.keypad-price_percent_symbol').addClass('active');

        $(inst.elem).focus();
    });

    $.keypad.addKeyDef('DISCOUNT_VAL1', 'discount_val1', function (inst) {
        this.focus();
    });
    $.keypad.addKeyDef('FEE_VAL', 'fee_val', function (inst) {
        this.focus();
    });

    $.keypad.addKeyDef('DISCOUNT_VAL2', 'discount_val2', function (inst) {
        this.focus();
    });

    $.keypad.addKeyDef('PRICE_VAL1', 'price_val1', function (inst) {
        this.focus();
    });

    $.keypad.addKeyDef('PRICE_VAL2', 'price_val2', function (inst) {
        this.focus();
    });

    $.keypad.addKeyDef('TENDERED_1', 'tendered_1', function (inst) {
        $(this).val(tendered_num.tendered_1);
        $("#amount_pay_cod").val(tendered_num.tendered_1).change();
        $("#amount_pay_cod").focus();
    });
    $.keypad.addKeyDef('TENDERED_2', 'tendered_2', function (inst) {
        $(this).val(tendered_num.tendered_2).change();
        $("#amount_pay_cod").val(tendered_num.tendered_2).change();
        $("#amount_pay_cod").focus();
    });
    $.keypad.addKeyDef('TENDERED_3', 'tendered_3', function (inst) {
        $(this).val(tendered_num.tendered_3);
        $("#amount_pay_cod").val(tendered_num.tendered_3).change();
        $("#amount_pay_cod").focus();
    });
    $.keypad.addKeyDef('TENDERED_4', 'tendered_4', function (inst) {
        $(this).val(tendered_num.tendered_4);
        $("#amount_pay_cod").val(tendered_num.tendered_4).change();
        $("#amount_pay_cod").focus();
    });

    if ($('#custom_product_price').length > 0) {
        custom_product();
    }
    if ($('#inline_amount_tendered').length > 0) {
        amount_tendered();
    }
    if ($('#inline_order_discount').length > 0) {
        order_discount();
    }
    if ($('#custom_fee_value').length > 0) {
        custom_fee_keypad();
    }
    $('#amount_pay_cod').on('change', function () {
        updateChangeCOD();
    });

    /*if(isTouchDevice()){
     $('#custom_product_price, #custom_product_quantity, #amount_pay_cod, #custom_shipping_price').attr('readonly', 'readonly');
     }*/

    function order_discount() {
        $('#inline_order_discount').keypad({
            keypadOnly: false,
            beforeShow: function (div, inst) {
                var order_discount_symbol = $('#order_discount_symbol').val();
                $('.discount_keypad .keypad-currency_symbol, .discount_keypad .keypad-percent_symbol').removeClass('active');
                $('.discount_keypad .keypad-' + order_discount_symbol).addClass('active');
                calculateDiscountKeyPad();
            },
            onKeypress: calculateDiscountKeyPad,

            keypadClass: 'discount_keypad',
            separator: '|',
            layout: [
                $.keypad.CURRENCY_SYMBOL + '|' + $.keypad.DISCOUNT_VAL2 + '|' + $.keypad.PERCENT_SYMBOL + '|' + $.keypad.DISCOUNT_VAL1,
                '1|2|3|' + $.keypad.CLEAR + '|' + $.keypad.FIVEPERCENT,
                '4|5|6|' + $.keypad.BACK + '|' + $.keypad.TENPERCENT,
                '7|8|9|' + $.keypad.CLOSE + '|' + $.keypad.FIFTEENPERCENT,
                '0|.|00|' + $.keypad.TWENTYPERCENT
            ],
            closeText: '',

            discount_val1Text: '0% off',
            currency_symbolText: wc_pos_params.currency_format_symbol,
            discount_val2Text: '0',
            percent_symbolText: '%',

            discount_val1Status: '',
            currency_symbolStatus: '',
            discount_val2Status: '',
            percent_symbolStatus: '',

            fivepercentText: wc_pos_params.discount_presets[0] + '%',
            tenpercentText: wc_pos_params.discount_presets[1] + '%',
            fifteenpercentText: wc_pos_params.discount_presets[2] + '%',
            twentypercentText: wc_pos_params.discount_presets[3] + '%',

            fivepercentStatus: '',
            tenpercentStatus: '',
            fifteenpercentStatus: '',
            twentypercentStatus: '',
        });
    }

    function custom_fee_keypad() {
        $('#custom_fee_value').keypad({
            keypadOnly: false,
            onKeypress: calculateFeeKeypad,
            keypadClass: 'custom_fee',
            separator: '|',
            layout: [
                $.keypad.CURRENCY_SYMBOL + '|' + $.keypad.FEE_VAL + '|' + $.keypad.PERCENT_SYMBOL,
                '1|2|3|' + $.keypad.CLEAR,
                '4|5|6|' + $.keypad.BACK,
                '7|8|9',
                '0|.|00'
            ],
            closeText: '',

            discount_val1Text: '0% off',
            currency_symbolText: wc_pos_params.currency_format_symbol,
            discount_val2Text: '0',
            percent_symbolText: '%',

            discount_val1Status: '',
            currency_symbolStatus: '',
            discount_val2Status: '',
            percent_symbolStatus: '',

            fivepercentText: wc_pos_params.discount_presets[0] + '%',
            tenpercentText: wc_pos_params.discount_presets[1] + '%',
            fifteenpercentText: wc_pos_params.discount_presets[2] + '%',
            twentypercentText: wc_pos_params.discount_presets[3] + '%',

            fivepercentStatus: '',
            tenpercentStatus: '',
            fifteenpercentStatus: '',
            twentypercentStatus: '',
        });
    }

    function custom_product() {
        var layout = [$.keypad.MINUS + '|' + $.keypad.INPVAL + '|' + $.keypad.PLUS, '1|2|3|' + $.keypad.CLEAR, '4|5|6|' + $.keypad.BACK, '7|8|9|' + $.keypad.CLOSE, '0|00|000'];
        if (wc_pos_params.decimal_quantity == 'yes') {
            layout = [$.keypad.MINUS + '|' + $.keypad.INPVAL + '|' + $.keypad.PLUS, '1|2|3|' + $.keypad.CLEAR, '4|5|6|' + $.keypad.BACK, '7|8|9|' + $.keypad.CLOSE, '0|00|.'];
        }
        $('#custom_product_quantity').keypad({
            keypadOnly: false,
            keypadClass: 'quantity_keypad',
            separator: '|',
            layout: layout,
            closeText: '',
            plusText: '+',
            minusText: '-',
            inpvalText: '0',
            minusStatus: 'Minus',
            plusStatus: 'Plus',
            onKeypress: function (key, value, inst) {
                if (value == '') {
                    value = 1;
                }
                var q = parseInt(value);
                if (wc_pos_params.decimal_quantity == 'yes') {
                    q = parseFloat(value);
                }
                if (q <= 0) {
                    q = 1;
                }
                $(inst._mainDiv).find('.keypad-inpval').text(q);
            },
            beforeShow: function (div, inst) {
                if (this.value == '') {
                    $(inst._mainDiv).find('.keypad-inpval').text(1);
                } else {
                    $(inst._mainDiv).find('.keypad-inpval').text(this.value);
                }
            },
            onClose: function (value, inst) {
                if (value == '') {
                    this.value = 1;
                } else {
                    var q = parseInt(value);
                    if (wc_pos_params.decimal_quantity == 'yes') {
                        q = parseFloat(value);
                    }
                    if (q <= 0) {
                        this.value = 1;
                    }
                }
            }
        }).click(function () {
            this.select();
        }).keypress(function (e) {
            if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57 ) && e.which != 46) {
                return false;
            }
            if (e.which == 46 && wc_pos_params.decimal_quantity == 'no') {
                return false;
            }
        }).keydown(function (ev) {
            $('.keypad-popup').hide();
            if (ev.which == 13) {
                ev.preventDefault();
            }
        });

        $('#custom_product_price').keypad({
            keypadOnly: false,
            separator: '|',
            layout: ['1|2|3|' + $.keypad.CLEAR, '4|5|6|' + $.keypad.BACK, '7|8|9|' + $.keypad.CLOSE, '0|.|00'],
            closeText: '',
            onKeypress: function (key, value, inst) {
                var arr = value.split('.');
                if (arr.length > 2) {
                    var l = arr[0].length;
                    var new_v = value.substr(l);
                    new_v = new_v.replace(/\./g, "");
                    value = arr[0] + '.' + new_v;
                    $(inst.elem).val(value);
                }
            }
        }).click(function () {
            this.select();
        }).keypress(function (e) {
            if (e.which != 8 && e.which != 0 && e.which != 46 && (e.which < 48 || e.which > 57)) {
                return false;
            }
            if (e.which === 46 && $(this).val().split('.').length === 2) {
                return false;
            }
        }).keydown(function (ev) {
            $('.keypad-popup').hide();
            if (ev.which == 13) {
                ev.preventDefault();
            }
        });

    }

    function amount_tendered() {
        $('#amount_pay_cod').click(function () {
            this.select();
        }).keypress(function (e) {
            if (e.which != 8 && e.which != 0 && e.which != 46 && (e.which < 48 || e.which > 57)) {
                return false;
            }
            if (e.which === 46 && $(this).val().split('.').length === 2) {
                return false;
            }
        }).keyup(function (ev) {
            updateChangeCOD();
        });
        $('#inline_amount_tendered').keypad({
            onKeypress: function (key, value, inst) {
                var arr = value.split('.');
                if (arr.length > 2) {
                    var l = arr[0].length;
                    var new_v = value.substr(l);
                    new_v = new_v.replace(/\./g, "");
                    value = arr[0] + '.' + new_v;
                    $(inst.elem).val(value);
                }
                $('#amount_pay_cod').val(value);
                updateChangeCOD();
            },
            keypadOnly: false,
            keypadClass: 'amount_pay_keypad',
            separator: '|',
            layout: [
                '1|2|3|' + $.keypad.CLEAR + '|' + $.keypad.TENDERED_1,
                '4|5|6|' + $.keypad.BACK + '|' + $.keypad.TENDERED_2,
                '7|8|9|' + $.keypad.CLOSE + '|' + $.keypad.TENDERED_3,
                '0|.|00|' + $.keypad.TENDERED_4
            ],
            closeText: '',
            tendered_1Text: '',
            tendered_2Text: '',
            tendered_3Text: '',
            tendered_4Text: '',

            tendered_1Status: '',
            tendered_2Status: '',
            tendered_3Status: '',
            tendered_4Status: '',
        });
    }

    function updateChangeCOD() {
        var total_amount = CART.total;
        var amount_pay = $('#amount_pay_cod').val();
        var change = parseFloat(amount_pay) - total_amount;
        change = change.toFixed(wc_pos_params.currency_format_num_decimals);
        if (amount_pay == '') {
            $('#amount_change_cod').val(0);
        } else if (change > 0) {
            $('#amount_change_cod').val(change);
            jQuery('#amount_change_cod').removeClass('error');
            $('#less-amount-notice').fadeOut();
        } else {
            $('#amount_change_cod').val(0);
        }
    }

    jQuery('body').on('click', '.restore-price', function () {
        var row_id = jQuery('#discount-value').data('row');
        jQuery('#discount-value,#' + row_id + ' .product_price').val(CART.cart_contents[row_id].original_price)
    });
});

function hideEmptyDiscounts() {
    //Hide % discounts if it miss in options
    if (wc_pos_params.discount_presets[0] === undefined) {
        jQuery('.keypad-fivepercent').hide();
    }
    if (wc_pos_params.discount_presets[1] === undefined) {
        jQuery('.keypad-tenpercent').hide();
    }
    if (wc_pos_params.discount_presets[2] === undefined) {
        jQuery('.keypad-fifteenpercent').hide();
    }
    if (wc_pos_params.discount_presets[3] === undefined) {
        jQuery('.keypad-twentypercent').hide();
    }
}