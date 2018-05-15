function accountingPOS(price, format) {
    if (typeof price == 'undefined')
        price = 0;

    switch (format) {
        case 'formatMoney':
            price = accounting.formatMoney(price, {
                symbol: wc_pos_params.currency_format_symbol,
                decimal: wc_pos_params.currency_format_decimal_sep,
                thousand: wc_pos_params.currency_format_thousand_sep,
                precision: wc_pos_params.currency_format_num_decimals,
                format: wc_pos_params.currency_format
            });
            break;
        case 'unformat':
            price = accounting.unformat(price, wc_pos_params.mon_decimal_point);
            break;
        case 'defaultNum':
            price = accounting.formatNumber(price, 2, '', '.');
            break;
        case 'formatNumber':
            price = accounting.formatNumber(price, wc_pos_params.currency_format_num_decimals, wc_pos_params.currency_format_thousand_sep, wc_pos_params.currency_format_decimal_sep);
            break;
    }
    return price;
}
function runTips() {
    jQuery('.tips').not('.tiped').tipTip({
        'attribute': 'data-tip',
        'fadeIn': 50,
        'fadeOut': 50,
        'delay': 200
    }).addClass('tiped');
}

function wc_cart_round_discount(value, precision) {
    return parseFloat(accounting.toFixed(value, precision));
}
function get_transient(name) {
    if (typeof POS_TRANSIENT[name] == 'undefined') {
        return false;
    }
    return POS_TRANSIENT[name];
}
function set_transient(name, val) {
    POS_TRANSIENT[name] == val;
}

/**
 * Returns the price (including tax). Uses customer tax rates. Can work for a specific $qty for more accurate taxes.
 *
 * @param  string $price to calculate, left blank to just use get_price()
 * @return string
 */
function get_price_including_tax(product, qty) {
    if (typeof qty == 'undefined') {
        qty = 1;
    }
    price = clone(product.price);
    if (product.taxable) {

        if (!pos_cart.prices_include_tax) {

            var tax_rates = TAX.get_rates(product.tax_class);
            var taxes = TAX.calc_tax(price * qty, tax_rates, false);
            var tax_amount = TAX.get_tax_total(taxes);
            price = round(price * qty + tax_amount, pos_wc.precision);

        } else {

            var tax_rates = TAX.get_rates(product.tax_class);
            var base_tax_rates = TAX.get_base_tax_rates(product.tax_class);

            if (tax_rates !== base_tax_rates) {

                var base_taxes = TAX.calc_tax(price * qty, base_tax_rates, true);
                var modded_taxes = TAX.calc_tax(( price * qty ) - array_sum(base_taxes), tax_rates, false);
                price = round(( price * qty ) - array_sum(base_taxes) + array_sum(modded_taxes), pos_wc.precision);

            } else {

                price = price * qty;

            }

        }

    } else {
        price = price * qty;
    }
    return price;
}

/**
 * Returns the price (excluding tax) - ignores tax_class filters since the price may *include* tax and thus needs subtracting.
 * Uses store base tax rates. Can work for a specific $qty for more accurate taxes.
 *
 * @param  string $price to calculate, left blank to just use get_price()
 * @return string
 */
function get_price_excluding_tax(product, qty) {

    if (typeof qty == 'undefined') {
        qty = 1;
    }
    price = clone(product.price);

    if (product.taxable && pos_cart.prices_include_tax) {
        var tax_rates = TAX.get_base_tax_rates(product.tax_class);
        var taxes = TAX.calc_tax(price * qty, tax_rates, true);
        price = TAX.round(price * qty - array_sum(taxes));
    } else {
        price = price * qty;
    }

    return price;
}

function posPrintReceipt(url, gift_receipt) {
    gift_receipt = gift_receipt || false;
    var find = '&amp;';
    var re = new RegExp(find, 'g');

    url = url.replace(re, '&');
    url = url + '&gift_receipt=' + gift_receipt;
    openModal('modal-printing-receipt');
    if (jQuery('#printable').length)
        jQuery('#printable').html('');

    var newHTML = jQuery('<div></div>');

    //var container = ;
    newHTML.load(url + '#pos_receipt', function () {
        newHTML.find('title, meta').remove();
        jQuery('#printable').append(newHTML.html());
        console.log(wc_pos_params.use_passprint);
        if (jQuery('#print_barcode img').length) {
            var src = jQuery('#print_barcode img').attr('src');
            if (src != '') {
                jQuery("<img>").load(function () {
                    if (wc_pos_params.use_passprint) {
                        var passprnt_url = "starpassprnt://v1/print/nopreview?size=" + wc_pos_params.passprint_size + "&back=http://webprnt.com/demofiles/PassPRNT/PassPRNT.html&html=<!DOCTYPE html>" + +encodeURIComponent(newHTML.html());
                        location.href = passprnt_url;
                    } else {
                        window.print();
                    }
                    closeModal('modal-printing-receipt');
                    wp.heartbeat.connectNow();
                    if (change_user && typeof APP_auth_show != 'undefined') {
                        APP_auth_show();
                    }
                }).attr('src', src);
            } else {
                if (wc_pos_params.use_passprint) {
                    var passprnt_url = "starpassprnt://v1/print/nopreview?size=" + wc_pos_params.passprint_size + "&back=http://webprnt.com/demofiles/PassPRNT/PassPRNT.html&html=" + +encodeURIComponent(newHTML.html());
                    location.href = passprnt_url;
                } else {
                    window.print();
                }
                closeModal('modal-printing-receipt');
                wp.heartbeat.connectNow();
                if (change_user && typeof APP_auth_show != 'undefined') {
                    APP_auth_show();
                }
            }
        }
        else if (jQuery('#print_receipt_logo').length) {
            var src = jQuery('#print_receipt_logo').attr('src');
            if (src != '') {
                jQuery("<img>").load(function () {
                    if (wc_pos_params.use_passprint) {
                        var passprnt_url = "starpassprnt://v1/print/nopreview?size=" + wc_pos_params.passprint_size + "&back=http://webprnt.com/demofiles/PassPRNT/PassPRNT.html&html=" + +encodeURIComponent(newHTML.html());
                        location.href = passprnt_url;
                    } else {
                        window.print();
                    }
                    closeModal('modal-printing-receipt');
                    wp.heartbeat.connectNow();
                    if (change_user && typeof APP_auth_show != 'undefined') {
                        APP_auth_show();
                    }
                }).attr('src', src);
            } else {
                if (wc_pos_params.use_passprint) {
                    var passprnt_url = "starpassprnt://v1/print/nopreview?size=" + wc_pos_params.passprint_size + "&back=http://webprnt.com/demofiles/PassPRNT/PassPRNT.html&html=" + +encodeURIComponent(newHTML.html());
                    location.href = passprnt_url;
                } else {
                    window.print();
                }
                closeModal('modal-printing-receipt');
                wp.heartbeat.connectNow();
                if (change_user && typeof APP_auth_show != 'undefined') {
                    APP_auth_show();
                }
            }
        }
        else {
            if (wc_pos_params.use_passprint) {
                var passprnt_url = "starpassprnt://v1/print/nopreview?size=" + wc_pos_params.passprint_size + "&back=http://webprnt.com/demofiles/PassPRNT/PassPRNT.html&html=" + encodeURIComponent(newHTML.html());
                window.location.href = passprnt_url;
            } else {
                window.print();
            }
            closeModal('modal-printing-receipt');
            wp.heartbeat.connectNow();
            if (change_user && typeof APP_auth_show != 'undefined') {
                APP_auth_show();
            }
        }
    });
}

//initiates print once content has been loaded into iframe
function callPrint(iframeId) {
    var RECEIPT = document.getElementById(iframeId);
    RECEIPT.focus();
    RECEIPT.contentWindow.print();
    closeModal('modal-printing-receipt');
}

function pos_get_price_html(product) {
    if (typeof product == 'undefined') {
        return '';
    }
    var tax_display_mode = pos_wc.tax_display_shop;
    var price = '';
    if (typeof product.variations != 'undefined' && product.variations.length > 0) {
        var min = 0;
        var max = 0;
        jQuery.each(product.variations, function (index, val) {
            var display_price = tax_display_mode == 'incl' ? get_price_including_tax(val) : get_price_excluding_tax(val);
            var p = parseFloat(display_price);
            if (min == 0 || p < min) {
                min = p;
            }
            if (p > max) {
                max = display_price;
            }
        });

        if (min != 0) {
            min = min;
            price += accountingPOS(min, 'formatMoney');
        }
        if (max != 0 && min != max) {
            if (min != 0) {
                price += ' - ';
            }
            price += accountingPOS(max, 'formatMoney');
        }

    } else if (product.price > 0) {
        var display_price = tax_display_mode == 'incl' ? get_price_including_tax(product) : get_price_excluding_tax(product);
        price = accountingPOS(display_price, 'formatMoney');
    }
    if (price == '') {
        price = pos_i18n[29];
    }
    return price;
}

function pos_date_to_time(date_string) {
    if (0 == date_string) {
        return 0;
    }
    var d = new Date(date_string);
    return Date.UTC(d.getUTCFullYear(), d.getUTCMonth(), d.getUTCDate(), d.getUTCHours(), d.getUTCMinutes(), d.getUTCSeconds(), d.getUTCMilliseconds()) / 1000;
}

function pos_wcs_add_time(number_of_periods, period, from_timestamp) {
    if (typeof from_timestamp == 'undefined' || from_timestamp == '') {
        from_timestamp = gmdate('Y-m-d H:i:s');
    }
    var next_timestamp = from_timestamp;
    if (number_of_periods > 0) {

        var msPerMinute = 60;// * 1000;
        var msPerHour = msPerMinute * 60;
        var msPerDay = msPerHour * 24;

        var time = 0;
        switch (period) {
            case 'day':
                time = msPerDay;
                break;
            case 'week':
                time = msPerDay * 7;
                break;
            case 'month':
                time = msPerDay * 30;
                break;
            case 'year':
                time = msPerDay * 365;
                break;
        }
        next_timestamp += time * number_of_periods;
    }

    return next_timestamp;
}

function POSvalidateEmail(email) {
    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}