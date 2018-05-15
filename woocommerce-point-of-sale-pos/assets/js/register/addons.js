jQuery(document).ready(function ($) {
    jQuery(document.body).trigger('updated_checkout');
    window.POS_ADDONS = {
        init: function () {
            if (typeof Stripe == 'function' && typeof wc_stripe_params != 'undefined') {
                Stripe.setPublishableKey(wc_stripe_params.key);
            }
            if (wc_pos_params.cc_scanning == 'yes') {
                if ($('#modal-order_payment').length > 0) {
                    $.cardswipe({
                        parser: ADDONS.cardParser,
                        firstLineOnly: false,
                        success: ADDONS.goodCardScan,
                        error: ADDONS.badCardScan,
                        debug: false,
                        prefixCharacter: ';'
                    });
                }
            }

            $('.wc_points_rewards_apply_discount').click(function () {
                ADDONS.points.apply_discount();
            });

            $('#add-fee').on('click', function () {
                if ($('#fee-name').val()) {
                    ADDONS.points.apply_fee();
                } else {
                    APP.showNotice(pos_i18n[44], 'error');
                }
            });

            if ($('#payment_method_securesubmit').length) {

            }

            wp.hooks.addFilter('wc_pos_process_payment', ADDONS.processPayment, 20, 2);

            wp.hooks.addAction('wc_pos_end_calculate_totals', ADDONS.calculate_points_earned_for_purchase, 999, 0);
        },
        validatePayment: function (payment_method) {
            var valid = true;
            switch (payment_method) {
                case 'securesubmit':
                    if (!$('#securesubmit iframe').length) {

                        var v1 = $('#securesubmit_card_number').val();
                        var v2 = $('#securesubmit_card_expiration').val();
                        var v3 = $('#securesubmit_card_cvv').val();
                        if (v1 == '' || v2 == '' || v3 == '') {
                            valid = false;
                        }

                    }
                    break;
                case 'simplify_commerce':
                    var v1 = $('#simplify_commerce-card-number').val();
                    var v2 = $('#simplify_commerce-card-expiry').val();
                    var v3 = $('#simplify_commerce-card-cvc').val();
                    if (v1 == '' || v2 == '' || v3 == '') {
                        valid = false;
                    }
                    break;
                case 'stripe':
                    var v1 = $('#stripe-card-number').val();
                    var v2 = $('#stripe-card-expiry').val();
                    var v3 = $('#stripe-card-cvc').val();
                    if (v1 == '' || v2 == '' || v3 == '') {
                        valid = false;
                    }
                    break;
                case 'realex':
                    var v1 = $('#realex_accountNumber').val();
                    var v2 = $('#realex_cardType').val();
                    var v3 = $('#realex_expirationMonth').val();
                    var v4 = $('#realex_expirationYear').val();
                    var v5 = $('#realex_cvNumber').val();
                    if (v1 == '' || v2 == '' || v3 == '' || v4 == '' || v5 == '') {
                        valid = false;
                    }
                    break;
                case 'braintree':
                    var v1 = $('#braintree-cc-number').val();
                    var v2 = $('#braintree-cc-exp-month').val();
                    var v3 = $('#braintree-cc-exp-year').val();
                    var v4 = $('#braintree-cc-cvv').val();
                    if (v1 == '' || v2 == '' || v3 == '' || v4 == '') {
                        valid = false;
                    }
                    break;
                case 'authorize_net_cim':
                    var v1 = $('#authorize-net-cim-cc-number').val();
                    var v2 = $('#authorize-net-cim-cc-exp-month').val();
                    var v3 = $('#authorize-net-cim-cc-exp-year').val();
                    if (v1 == '' || v2 == '' || v3 == '') {
                        valid = false;
                    }
                    break;
                case 'authorize_net_cim_credit_card':
                    var v1 = $('#wc-authorize-net-cim-credit-card-account-number').val();
                    var v2 = $('#wc-authorize-net-cim-credit-card-expiry').val();
                    if (v1 == '' || v2 == '') {
                        valid = false;
                    }
                    if ($('#wc-authorize-net-cim-credit-card-csc').length) {
                        var v3 = $('#wc-authorize-net-cim-credit-card-csc').val();
                        if (v3 == '') {
                            valid = false;
                        }
                    }
                    break;
                case 'authorize_net_cim_echeck':
                    var v1 = $('#wc-authorize-net-cim-echeck-routing-number').val();
                    var v2 = $('#wc-authorize-net-cim-echeck-account-number').val();
                    var v3 = $('#wc-authorize-net-cim-echeck-account-type').val();
                    if (v1 == '' || v2 == '' || v3 == '') {
                        valid = false;
                    }
                    break;
                case 'authorize_net_aim':
                    var v1 = $('#wc-authorize-net-aim-account-number').val();
                    var v2 = $('#wc-authorize-net-aim-exp-month').val();
                    var v3 = $('#wc-authorize-net-aim-exp-year').val();
                    var v4 = $('#wc-authorize-net-aim-csc').val();
                    if (v1 == '' || v2 == '' || v3 == '' || v4 == '') {
                        valid = false;
                    }
                    break;
                case 'authorize_net_aim_echeck':
                    var v1 = $('#wc-authorize-net-aim-echeck-routing-number').val();
                    var v2 = $('#wc-authorize-net-aim-echeck-account-number').val();
                    var v3 = $('#wc-authorize-net-aim-echeck-account-type').val();
                    if (v1 == '' || v2 == '' || v3 == '') {
                        valid = false;
                    }
                    break;
                case 'credomatic_aim':
                    var v1 = $('#credomatic_aim-card-number').val();
                    var v2 = $('#credomatic_aim-card-expiry').val();
                    var v3 = $('#credomatic_aim-card-cvc').val();
                    if (v1 == '' || v2 == '' || v3 == '') {
                        valid = false;
                    }
                    break;
                case 'paytrace':
                    var v1 = $('#paytrace-card-number').val();
                    var v2 = $('#paytrace-card-type').val();
                    var v3 = $('#paytrace-card-expiry').val();
                    var v4 = $('#paytrace-card-cvc').val();
                    if (v1 == '' || v2 == '' || v3 == '' || v4 == '') {
                        valid = false;
                    }
                    break;
                case 'paypal_pro':
                    var v1 = $('#paypal_pro-card-number').val();
                    var v2 = $('#paypal_pro-card-expiry').val();
                    var v3 = $('#paypal_pro-card-cvc').val();
                    if (v1 == '' || v2 == '' || v3 == '') {
                        valid = false;
                    }
                    break;
                case 'paypal_pro_payflow':
                    var v1 = $('#paypal_pro_payflow-card-number').val();
                    var v2 = $('#paypal_pro_payflow-card-expiry').val();
                    var v3 = $('#paypal_pro_payflow-card-cvc').val();
                    if (v1 == '' || v2 == '' || v3 == '') {
                        valid = false;
                    }
                    break;
            }
            return valid;
        },
        processPayment: function (cart, payment_method) {
            switch (payment_method) {
                case 'simplify_commerce':
                    var card = $('#simplify_commerce-card-number').val(),
                        cvc = $('#simplify_commerce-card-cvc').val(),
                        expiry = $.payment.cardExpiryVal($('#simplify_commerce-card-expiry').val()),

                        card = card.replace(/\s/g, '');
                    if (CUSTOMER.customer) {
                        address1 = CUSTOMER.billing_address.address_1;
                        address2 = CUSTOMER.billing_address.address_2;
                        addressState = CUSTOMER.billing_address.state;
                        addressCity = CUSTOMER.billing_address.city;
                        addressZip = CUSTOMER.billing_address.postcode;
                        addressCountry = CUSTOMER.billing_address.country;
                    } else {
                        var outlet = pos_wc.outlet_location;
                        address1 = outlet.contact.address_1;
                        address2 = outlet.contact.address_2;
                        addressState = outlet.contact.state;
                        addressCity = outlet.contact.city;
                        addressZip = outlet.contact.postcode;
                        addressCountry = outlet.contact.country;
                    }
                    SimplifyCommerce.generateToken({
                        key: Simplify_commerce_params.key,
                        card: {
                            number: card,
                            cvc: cvc,
                            expMonth: expiry.month,
                            expYear: ( expiry.year - 2000 ),
                            addressLine1: address1,
                            addressLine2: address2,
                            addressCountry: addressCountry,
                            addressState: addressState,
                            addressZip: addressZip,
                            addressCity: addressCity
                        }
                    }, function (data) {
                        if (data.error) {

                            // show the errors
                            $('#modal-order_payment, #post-body').unblock();

                            // Show any validation errors
                            if ('validation' === data.error.code) {
                                var fieldErrors = data.error.fieldErrors,
                                    fieldErrorsLength = fieldErrors.length,
                                    errorList = '';

                                for (var i = 0; i < fieldErrorsLength; i++) {
                                    var message = Simplify_commerce_params[fieldErrors[i].field] + ' ' + Simplify_commerce_params.is_invalid + ' - ' + fieldErrors[i].message;
                                    APP.showNotice(message, 'error');
                                }

                            }

                        } else {

                            // Insert the token into the form so it gets submitted to the server
                            cart.order.simplify_token = data.id;

                            APP.processPayment(cart, true);
                        }
                    });
                    return false;
                    break;
                case 'stripe':
                    if (jQuery('#payment_terms').bootstrapSwitch('state')) {
                        jQuery('#terms').attr('checked', 'checked');
                    }
                    jQuery('form.woocommerce-checkout').trigger('checkout_place_order_stripe');
                    jQuery('form.woocommerce-checkout').one('submit', function () {
                        if (jQuery('.stripe-source').length > 0) {
                            cart.order['stripe_source'] = jQuery('.stripe-source').val();
                            APP.processPayment(cart, true);
                            jQuery('.wc-stripe-error, .stripe-source, .stripe_token, .stripe-checkout-object').remove();
                        }
                    });
                    return false;
                    break;
                case 'realex':
                    var data = {
                        'realex_accountNumber': jQuery('#realex_accountNumber').val(),
                        'realex_cardType': jQuery('#realex_cardType').val(),
                        'realex_expirationMonth': jQuery('#realex_expirationMonth').val(),
                        'realex_expirationYear': jQuery('#realex_expirationYear').val(),
                        'realex_cvNumber': jQuery('#realex_cvNumber').length ? jQuery('#realex_cvNumber').val() : '',
                    };
                    if (typeof cart.order.create_post == 'undefined') {
                        cart.order.create_post = [];
                    }
                    cart.order.create_post.push(data);
                    APP.processPayment(cart, true);
                    return false;
                    break;
                case 'braintree':
                    var data = {
                        'braintree-cc-number': jQuery('#braintree-cc-number').val(),
                        'braintree-cc-exp-month': jQuery('#braintree-cc-exp-month').val(),
                        'braintree-cc-exp-year': jQuery('#braintree-cc-exp-year').val(),
                        'braintree-cc-cvv': jQuery('#braintree-cc-cvv').length ? jQuery('#braintree-cc-cvv').val() : '',
                    };
                    if (typeof cart.order.create_post == 'undefined') {
                        cart.order.create_post = [];
                    }
                    cart.order.create_post.push(data);
                    APP.processPayment(cart, true);
                    return false;
                    break;

                case 'authorize_net_cim':
                    var data = {
                        'wc-authorize-net-cim-credit-card-account-number': jQuery('#wc-authorize-net-cim-credit-card-account-number').val(),
                        'wc-authorize-net-cim-credit-card-expiry': jQuery('#wc-authorize-net-cim-credit-card-expiry').val(),
                        'wc-authorize-net-cim-credit-card-csc': jQuery('#wc-authorize-net-cim-credit-card-csc').length ? jQuery('#wc-authorize-net-cim-credit-card-csc').val() : '',
                    };
                    if (typeof cart.order.create_post == 'undefined') {
                        cart.order.create_post = [];
                    }
                    cart.order.create_post.push(data);
                    APP.processPayment(cart, true);
                    return false;
                case 'authorize_net_cim_credit_card':
                    var data = {
                        'wc-authorize-net-cim-credit-card-account-number': jQuery('#wc-authorize-net-cim-credit-card-account-number').val(),
                        'wc-authorize-net-cim-credit-card-expiry': jQuery('#wc-authorize-net-cim-credit-card-expiry').val(),
                        'wc-authorize-net-cim-credit-card-csc': jQuery('#wc-authorize-net-cim-credit-card-csc').length ? jQuery('#wc-authorize-net-cim-credit-card-csc').val() : '',
                    };
                    if (typeof cart.order.create_post == 'undefined') {
                        cart.order.create_post = [];
                    }
                    cart.order.create_post.push(data);
                    APP.processPayment(cart, true);
                    return false;
                    break;
                case 'authorize_net_cim_echeck':
                    var data = {
                        'wc-authorize-net-cim-echeck-routing-number': jQuery('#wc-authorize-net-cim-echeck-routing-number').val(),
                        'wc-authorize-net-cim-echeck-account-number': jQuery('#wc-authorize-net-cim-echeck-account-number').val(),
                        'wc-authorize-net-cim-echeck-account-type': jQuery('#wc-authorize-net-cim-echeck-account-type').val()
                    };
                    if (typeof cart.order.create_post == 'undefined') {
                        cart.order.create_post = [];
                    }
                    cart.order.create_post.push(data);
                    APP.processPayment(cart, true);
                    return false;
                    break;
                case 'authorize_net_aim':
                    var data = {};
                    if (jQuery('#wc-authorize-net-aim-expiry').length) {
                        data = {
                            'wc-authorize-net-aim-account-number': jQuery('#wc-authorize-net-aim-account-number').val(),
                            'wc-authorize-net-aim-expiry': jQuery('#wc-authorize-net-aim-expiry').val(),
                            'wc-authorize-net-aim-csc': jQuery('#wc-authorize-net-aim-csc').val()
                        };
                    } else {
                        var expires = {
                            month: jQuery('#wc-authorize-net-aim-exp-month').val(),
                            year: jQuery('#wc-authorize-net-aim-exp-year').val(),
                        };
                        data = {
                            'wc-authorize-net-aim-account-number': jQuery('#wc-authorize-net-aim-account-number').val(),
                            'input-text js-wc-payment-gateway-csc': jQuery('#wc-authorize-net-aim-csc').length ? jQuery('#wc-authorize-net-aim-csc').val() : '',
                            'wc-authorize-net-aim-exp-month': parseInt(expires['month']) || 0,
                            'wc-authorize-net-aim-exp-year': parseInt(expires['year']) || 0
                        };
                    }
                    if (typeof cart.order.create_post == 'undefined') {
                        cart.order.create_post = [];
                    }
                    cart.order.create_post.push(data);
                    APP.processPayment(cart, true);
                    return false;
                    break;
                case 'authorize_net_aim_echeck':
                    var data = {
                        'wc-authorize-net-aim-echeck-routing-number': jQuery('#wc-authorize-net-aim-echeck-routing-number').val(),
                        'wc-authorize-net-aim-echeck-account-number': jQuery('#wc-authorize-net-aim-echeck-account-number').val(),
                        'wc-authorize-net-aim-echeck-account-type': jQuery('#wc-authorize-net-aim-echeck-account-type').val()
                    };
                    if (typeof cart.order.create_post == 'undefined') {
                        cart.order.create_post = [];
                    }
                    cart.order.create_post.push(data);
                    APP.processPayment(cart, true);
                    return false;
                    break;
                case 'credomatic_aim':
                    var data = {
                        'credomatic_aim-card-number': jQuery('#credomatic_aim-card-number').val(),
                        'credomatic_aim-card-expiry': jQuery('#credomatic_aim-card-expiry').val(),
                        'credomatic_aim-card-cvc': jQuery('#credomatic_aim-card-cvc').length ? jQuery('#credomatic_aim-card-cvc').val() : '',
                    };
                    if (typeof cart.order.create_post == 'undefined') {
                        cart.order.create_post = [];
                    }
                    cart.order.create_post.push(data);
                    APP.processPayment(cart, true);
                    return false;
                    break;
                case 'paytrace':
                    var data = {
                        'paytrace-card-number': jQuery('#paytrace-card-number').val(),
                        'paytrace-card-type': jQuery('#paytrace-card-type').val(),
                        'paytrace-card-expiry': jQuery('#paytrace-card-expiry').val(),
                        'paytrace-card-cvc': jQuery('#paytrace-card-cvc').length ? jQuery('#paytrace-card-cvc').val() : '',
                    };
                    if (typeof cart.order.create_post == 'undefined') {
                        cart.order.create_post = [];
                    }
                    cart.order.create_post.push(data);
                    APP.processPayment(cart, true);
                    return false;
                    break;
                case 'paypal_pro':
                    var data = {
                        'paypal_pro-card-number': jQuery('#paypal_pro-card-number').val(),
                        'paypal_pro-card-expiry': jQuery('#paypal_pro-card-expiry').val(),
                        'paypal_pro-card-cvc': jQuery('#paypal_pro-card-cvc').length ? jQuery('#paypal_pro-card-cvc').val() : '',
                    };
                    if (typeof cart.order.create_post == 'undefined') {
                        cart.order.create_post = [];
                    }
                    cart.order.create_post.push(data);
                    APP.processPayment(cart, true);
                    return false;
                    break;
                case 'paypal_pro_payflow':
                    var exp_date = jQuery('#paypal_pro_payflow-card-expiry').val();
                    exp_date = exp_date.replace(' / ', '');
                    var data = {
                        'paypal_pro_payflow-card-number': jQuery('#paypal_pro_payflow-card-number').val(),
                        'paypal_pro_payflow-card-expiry': exp_date,
                        'paypal_pro_payflow-card-cvc': jQuery('#paypal_pro_payflow-card-cvc').length ? jQuery('#paypal_pro_payflow-card-cvc').val() : '',
                    };
                    if (typeof cart.order.create_post == 'undefined') {
                        cart.order.create_post = [];
                    }
                    cart.order.create_post.push(data);
                    APP.processPayment(cart, true);
                    return false;
                    break;
                case 'nmi':
                    var data = {
                        'nmi-card-number': jQuery('#nmi-card-number').val(),
                        'nmi-card-expiry': jQuery('#nmi-card-expiry').val(),
                        'nmi-card-cvc': jQuery('#nmi-card-cvc').length ? jQuery('#nmi-card-cvc').val() : '',
                    };
                    if (typeof cart.order.create_post == 'undefined') {
                        cart.order.create_post = [];
                    }
                    cart.order.create_post.push(data);
                    APP.processPayment(cart, true);
                    return false;
                    break;
                case 'securesubmit':
                    ADDONS.cart = cart;
                    var securesubmitMethod = document.getElementById('payment_method_securesubmit');
                    var storedCards = document.querySelectorAll('input[name=secure_submit_card]');
                    var storedCardsChecked = filter(storedCards, function (el) {
                        return el.checked;
                    });
                    var token = document.getElementById('securesubmit_token');

                    if (securesubmitMethod && securesubmitMethod.checked && (storedCardsChecked.length === 0 || storedCardsChecked[0] && storedCardsChecked[0].value === 'new') && token.value === '') {
                        var card = document.getElementById('securesubmit_card_number');
                        var cvv = document.getElementById('securesubmit_card_cvv');
                        var expiration = document.getElementById('securesubmit_card_expiration');

                        if (!expiration && expiration.value) {
                            return false;
                        }

                        var split = expiration.value.split(' / ');
                        var month = split[0].replace(/^\s+|\s+$/g, '');
                        var year = split[1].replace(/^\s+|\s+$/g, '');

                        (new Heartland.HPS({
                            publicKey: wc_securesubmit_params.key,
                            cardNumber: card.value.replace(/\D/g, ''),
                            cardCvv: cvv.value.replace(/\D/g, ''),
                            cardExpMonth: month.replace(/\D/g, ''),
                            cardExpYear: year.replace(/\D/g, ''),
                            success: ADDONS.securesubmit.responseHandler,
                            error: ADDONS.securesubmit.responseHandler
                        })).tokenize();
                    }
                    return false;
                    break;
                case 'yanco_wc_ean_payment_gateway':
                    var data = {
                        'ean_payment': jQuery('#ean_payment').val(),
                    };
                    if (typeof cart.order.create_post == 'undefined') {
                        cart.order.create_post = [];
                    }
                    cart.order.create_post.push(data);
                    APP.processPayment(cart, true);
                    return false;
                    break;
                case 'intuit_qbms_credit_card':
                    break;
                default:
                    var $wrap = $('#modal-order_payment .popup_section').filter('#' + payment_method);
                    if ($('.wc-credit-card-form-card-number', $wrap).length) {
                        var data = {
                            'wc-credit-card-form-card-number': jQuery('.wc-credit-card-form-card-number', $wrap).val(),
                            'wc-credit-card-form-card-expiry': jQuery('.wc-credit-card-form-card-expiry', $wrap).val(),
                            'wc-credit-card-form-card-cvc': jQuery('.wc-credit-card-form-card-cvc', $wrap).length ? jQuery('.wc-credit-card-form-card-cvc', $wrap).val() : '',
                        };
                        if (typeof cart.order.create_post == 'undefined') {
                            cart.order.create_post = [];
                        }
                        cart.order.create_post.push(data);
                        APP.processPayment(cart, true);
                        return false;

                    }
                    break;
            }
            return cart;
        },
        goodCardScan: function (cardData) {
            var payment_method = $('.select_payment_method:checked:not(:disabled)').val();
            switch (payment_method) {
                case 'securesubmit':
                    $('.securesubmit-content .card-number').val(cardData.account);
                    $('.securesubmit-content .expiry-date').val(cardData.exp_month + '/' + cardData.exp_year);
                    $('.securesubmit-content .card-cvc').focus();
                    break;
                case 'stripe':
                    $('#stripe-card-number').val(cardData.account);
                    $('#stripe-card-expiry').val(cardData.exp_month + '/' + cardData.s_exp_year);
                    $('#stripe-card-cvc').focus();
                    break;
                case 'realex':
                    $('#realex_accountNumber').val(cardData.account);
                    $('#realex_cardType').val(cardData.c_type[1]);
                    $('#realex_expirationMonth').val(cardData.exp_month);
                    $('#realex_expirationYear').val(cardData.exp_year);
                    $('#realex_cvNumber').focus();
                    break;
                case 'braintree':
                    $('#braintree-cc-number').val(cardData.account);
                    $('#braintree-cc-exp-month').val(cardData.exp_month);
                    $('#braintree-cc-exp-year').val(cardData.exp_year);
                    $('#braintree-cc-cvv').focus();
                    break;
                case 'authorize_net_cim_credit_card':
                    $('#wc-authorize-net-cim-credit-card-account-number').val(cardData.account);
                    $('#wc-authorize-net-cim-credit-card-expiry').val(cardData.exp_month + '/' + cardData.s_exp_year);
                    $('#wc-authorize-net-cim-credit-card-csc').focus();
                    break;
                case 'authorize_net_cim':
                    $('#authorize-net-cim-cc-number').val(cardData.account);
                    $('#authorize-net-cim-cc-exp-month').val(cardData.exp_month);
                    var year = parseInt(cardData.exp_year);
                    $('#authorize-net-cim-cc-exp-year').val(year);
                    break;
                case 'authorize_net_aim':
                    $('#wc-authorize-net-aim-account-number').val(cardData.account);
                    $('#wc-authorize-net-aim-exp-month').val(cardData.exp_month);
                    var year = parseInt(cardData.exp_year);
                    $('#wc-authorize-net-aim-exp-year').val(year);
                    $('#wc-authorize-net-aim-csc').focus();
                    break;
                case 'credomatic_aim':
                    $('#credomatic_aim-card-number').val(cardData.account);
                    $('#credomatic_aim-card-expiry').val(cardData.exp_month + '/' + cardData.s_exp_year);
                    $('#credomatic_aim-card-cvc').focus();
                    break;
                case 'paytrace':
                    $('#paytrace-card-number').val(cardData.account);
                    $('#paytrace-card-type').val(cardData.c_type[1]);
                    $('#paytrace-card-expiry').val(cardData.exp_month + '/' + cardData.s_exp_year);
                    $('#paytrace-card-cvc').focus();
                    break;
                case 'paypal_pro':
                    $('#paypal_pro-card-number').val(cardData.account);
                    $('#paypal_pro-card-expiry').val(cardData.exp_month + '/' + cardData.s_exp_year);
                    $('#paypal_pro-card-cvc').focus();
                    break;
                case 'paypal_pro_payflow':
                    $('#paypal_pro_payflow-card-number').val(cardData.account);
                    $('#paypal_pro_payflow-card-expiry').val(cardData.exp_month + '/' + cardData.s_exp_year);
                    $('#paypal_pro_payflow-card-cvc').focus();
                    break;
                default:
                    var $wrap = $('#modal-order_payment .popup_section').filter('#' + payment_method);
                    $('.wc-credit-card-form-card-number', $wrap).val(cardData.account);
                    $('.wc-credit-card-form-card-expiry', $wrap).val(cardData.exp_month + '/' + cardData.s_exp_year);
                    $('.wc-credit-card-form-card-cvc', $wrap).focus();
                    break;
            }
        },
        crlearCardfields: function (cardData) {
            $('.securesubmit-content .card-number').val('');
            $('.securesubmit-content .expiry-date').val('');
            $('.securesubmit-content .card-cvc').val('');

            $('.wc-credit-card-form-card-number').val('');
            $('.wc-credit-card-form-card-expiry').val('');
            $('.wc-credit-card-form-card-cvc').val('');

            $('#stripe-card-number').val('');
            $('#stripe-card-expiry').val('');
            $('#stripe-card-cvc').val('');

            $('#realex_accountNumber').val('');
            $('#realex_cardType').val('');
            $('#realex_expirationMonth').val('');
            $('#realex_expirationYear').val('');
            $('#realex_cvNumber').val('');

            $('#braintree-cc-number').val('');
            $('#braintree-cc-exp-month').val('');
            $('#braintree-cc-exp-year').val('');
            $('#braintree-cc-cvv').val('');

            $('#authorize-net-cim-cc-number').val('');
            $('#authorize-net-cim-cc-exp-month').val('');
            $('#authorize-net-cim-cc-exp-year').val('');

            $('#wc-authorize-net-aim-account-number').val('');
            $('#wc-authorize-net-aim-exp-month').val('');
            $('#wc-authorize-net-aim-exp-year').val('');
            $('#wc-authorize-net-aim-csc').val('');

            $('#credomatic_aim-card-number').val('');
            $('#credomatic_aim-card-expiry').val('');
            $('#credomatic_aim-card-cvc').val('');

            $('#paytrace-card-number').val('');
            $('#paytrace-card-type').val('');
            $('#paytrace-card-expiry').val('');
            $('#paytrace-card-cvc').val('');

            $('#paypal_pro-card-number').val('');
            $('#paypal_pro-card-expiry').val('');
            $('#paypal_pro-card-cvc').val('');

            $('#paypal_pro_payflow-card-number').val('');
            $('#paypal_pro_payflow-card-expiry').val('');
            $('#paypal_pro_payflow-card-cvc').val('');
        },
        cardParser: function (rawData) {
            var swipeData = new SwipeParserObj(rawData);
            return swipeData.obj();
        },
        badCardScan: function () {
            APP.showNotice(pos_i18n[33], 'error');
        },
        calculate_points_earned_for_purchase: function () {
            if (wc_points_and_rewards.enabled === true) {
                var points_earned = 0;
                var cart = CART.get_cart();

                // Order cart items by price so coupon logic is 'fair' for customers and not based on order added to cart.
                cart = uasort(cart, CART.sort_by_subtotal);
                $.each(cart, function (cart_item_key, values) {

                    var _product = values['data'];

                    if (values.variation_id > 0) {
                        _product = values['v_data'];
                        _product.categories_ids = values['data']['categories_ids'];
                    }

                    points_earned += ADDONS.points.get_points_earned_for_product_purchase(_product) * values['quantity'];

                });
                var discount_amount = Math.min(ADDONS.points.calculate_points(CART.discount_cart), points_earned);
                points_earned = points_earned - discount_amount;

                if (Object.size(CART.coupons) > 0) {
                    var points_modifier = 0;

                    // get the maximum points modifier if there are multiple coupons applied, each with their own modifier
                    $.each(CART.coupons, function (code, coupon) {
                        if (typeof coupon.coupon_custom_fields != 'undefined' && typeof coupon.coupon_custom_fields['_wc_points_modifier'] != 'undefined' && coupon.coupon_custom_fields['_wc_points_modifier'][0] != '') {
                            points_modifier = coupon.coupon_custom_fields['_wc_points_modifier'][0];
                        }
                    });

                    if (points_modifier > 0)
                        points_earned = Math.round(points_earned * ( points_modifier / 100 ));
                }

                $('.woocommerce_order_items #tr_order_points_earned').remove();
                if (!isNaN(points_earned) && points_earned != '' && points_earned > 0) {

                    var message_txt = wc_points_and_rewards.i18n_earn_points_message_single;
                    if (points_earned > 1) {
                        message_txt = wc_points_and_rewards.i18n_earn_points_message_multy;
                    }
                    CUSTOMER.points_earned = points_earned;
                    var ms_template = Handlebars.compile(message_txt);
                    var message = ms_template({points: points_earned});

                    var source = $('#tmpl-cart-points-earned').html();
                    var template = Handlebars.compile(source);

                    var data = {message: message}
                    var html = template(data);
                    $('.wc_pos_register_subtotals .woocommerce_order_items tbody').append(html);
                }
                ADDONS.points.set_discount_for_redeeming_points();
            }
        },
        points: {
            get_points_earned_for_product_purchase: function (product) {
                if (typeof product != 'object') return 0;
                // check if earned points are set at product-level
                var points = ADDONS.points.get_product_points(product);

                if (!isNaN(points) && points != '') {
                    return points;
                }

                // check if earned points are set at category-level
                points = ADDONS.points.get_category_points(product);

                if (!isNaN(points) && points != '') {
                    return points;
                }

                // otherwise, show the default points set for the price of the product
                return ADDONS.points.calculate_points(product.price);
            },
            get_product_points: function (product) {
                if (typeof product != 'object') return 0;

                var points = '';
                if (typeof product.points_earned != 'undefined') {
                    points = product.points_earned;
                }

                // if a percentage modifier is set, adjust the points for the product by the percentage
                if (false !== strpos(points, '%')) {
                    points = ADDONS.points.calculate_points_multiplier(points, product);
                }

                return points;
            },
            get_category_points: function (product) {
                var category_points = '';
                var category_points_array = wc_points_and_rewards.category_poins;

                $.each(product.categories_ids, function (index, category_id) {
                    var points = '';
                    if (typeof category_points_array[category_id] != 'undefined') {
                        points = category_points_array[category_id];

                        // if a percentage modifier is set, adjust the default points earned for the category by the percentage
                        if (false !== strpos(points, '%')) {
                            points = ADDONS.points.calculate_points_multiplier(points, product);
                        }

                        if (!isNaN(points) && points != '') {

                            // in the case of a product being assigned to multiple categories with differing points earned, we want to return the biggest one
                            if (category_points == '' || points >= parseInt(category_points)) {
                                category_points = points;
                            }
                        }

                    }

                });
                return category_points;
            },
            calculate_points_multiplier: function (points, product) {
                var percentage = parseFloat(points.replace('%', '')) / 100;

                return percentage * ADDONS.points.calculate_points(product.price);
            },
            calculate_points: function (amount) {
                var ratio = wc_points_and_rewards.ratio.split(':');
                var points = parseFloat(ratio[0]);
                var monetary_value = typeof ratio[1] != 'undefined' ? ratio[1] : '';

                if (!points || points == '')
                    return 0;

                switch (wc_points_and_rewards.rounding) {
                    case 'ceil' :
                        return Math.ceil(amount * ( points / monetary_value ));
                        break;
                    case 'floor' :
                        return Math.floor(amount * ( points / monetary_value ));
                        break;
                    default :
                        return Math.round(amount * ( points / monetary_value ));
                        break;
                }
            },
            calculate_discount_modifier: function (percentage, product) {
                percentage = parseFloat(percentage.replace('%', '')) / 100;

                if (typeof product != 'undefined') {
                    return percentage * product.price;
                } else {
                    var discount = CART.subtotal;
                    if (!pos_cart.prices_include_tax) {
                        discount = CART.subtotal_ex_tax;
                    }
                    return percentage * discount;
                }

            },
            get_product_max_discount: function (product) {
                if (typeof product != 'object') return '';

                var max_discount = '';
                if (typeof product.points_max_discount != 'undefined') {
                    max_discount = product.points_max_discount;
                }

                // if a percentage modifier is set, set the maximum discount using the price of the product
                if (false !== strpos(max_discount, '%')) {
                    max_discount = ADDONS.points.calculate_discount_modifier(max_discount, product);
                }

                return max_discount;
            },
            get_category_max_discount: function (product) {
                var category_max_discount = '';
                var category_discount_array = wc_points_and_rewards.category_max_discount;

                $.each(product.categories_ids, function (index, category_id) {
                    var max_discount = '';
                    if (typeof category_discount_array[category_id] != 'undefined') {
                        max_discount = category_discount_array[category_id];

                        // if a percentage modifier is set, set the maximum discount using the price of the product
                        if (false !== strpos(max_discount, '%')) {
                            max_discount = ADDONS.points.calculate_discount_modifier(points, product);
                        }

                        if (!isNaN(max_discount) && max_discount != '') {

                            // get the minimum discount if the product belongs to multiple categories with differing maximum discounts
                            if (category_max_discount == '' || max_discount >= parseInt(category_max_discount)) {
                                category_max_discount = max_discount;
                            }
                        }

                    }

                });
                return category_max_discount;
            },
            get_maximum_points_discount_for_product: function (product) {
                if (typeof product != 'object')
                    return '';

                // check if max discount is set at product-level
                var max_discount = this.get_product_max_discount(product);

                if (!isNaN(max_discount) && max_discount != '') {
                    return max_discount;
                }

                // check if max discount is are set at category-level
                max_discount = this.get_category_max_discount(product);

                if (!isNaN(max_discount) && max_discount != '') {
                    return max_discount;
                }

                // limit the discount available by the global maximum discount if set
                max_discount = wc_points_and_rewards.max_discount;

                // if the global max discount is a percentage, calculate it by multiplying the percentage by the product price
                if (false !== strpos(max_discount, '%'))
                    max_discount = ADDONS.points.calculate_discount_modifier(max_discount, product);

                if (!isNaN(max_discount) && max_discount != '') {
                    return max_discount;
                }

                // otherwise, there is no maximum discount set
                return '';
            },

            get_discount_for_redeeming_points: function (points) {
                var available_user_discount = ADDONS.points.calculate_points_value(points);
                // get the value of the user's point balance
                // no discount
                if (typeof available_user_discount == 'undefined' || available_user_discount <= 0) {
                    return 0;
                }

                /*if ( $applying && 'yes' === get_option( 'wc_points_rewards_partial_redemption_enabled' ) && WC()->session->get( 'wc_points_rewards_discount_amount' ) ) {
                 $requested_user_discount = WC_Points_Rewards_Manager::calculate_points_value( WC()->session->get( 'wc_points_rewards_discount_amount' ) );
                 if ( $requested_user_discount > 0 && $requested_user_discount < $available_user_discount ) {
                 $available_user_discount = $requested_user_discount;
                 }
                 }*/

                var discount_applied = 0;

                // calculate the discount to be applied by iterating through each item in the cart and calculating the individual
                // maximum discount available
                var cart = CART.get_cart();

                // Order cart items by price so coupon logic is 'fair' for customers and not based on order added to cart.
                cart = uasort(cart, CART.sort_by_subtotal);
                $.each(cart, function (item_key, item) {
                    var _product = item['data'];

                    if (item.variation_id > 0) {
                        _product = item['v_data'];
                        _product.categories_ids = item['data']['categories_ids'];
                    }

                    var discount = 0;
                    var max_discount = ADDONS.points.get_maximum_points_discount_for_product(_product);

                    if (!isNaN(max_discount) && max_discount != '') {

                        // adjust the max discount by the quantity being ordered
                        max_discount *= item['quantity'];

                        // if the discount available is greater than the max discount, apply the max discount
                        discount = ( available_user_discount <= max_discount ) ? available_user_discount : max_discount;

                        // Max should be product price. As this will be applied before tax, it will respect other coupons.
                    } else {

                        max_discount = _product.price * item['quantity'];

                        // if the discount available is greater than the max discount, apply the max discount
                        discount = ( available_user_discount <= max_discount ) ? available_user_discount : max_discount;
                    }

                    // add the discount to the amount to be applied
                    discount_applied += discount;

                    // reduce the remaining discount available to be applied
                    available_user_discount -= discount;
                });

                // if the available discount is greater than the order total, make the discount equal to the order total less any other discounts
                if (!pos_cart.prices_include_tax) {
                    discount_applied = Math.max(0, Math.min(discount_applied, CART.subtotal_ex_tax));

                } else {
                    discount_applied = Math.max(0, Math.min(discount_applied, CART.subtotal));
                }

                // limit the discount available by the global maximum discount if set
                max_discount = wc_points_and_rewards.cart_max_discount;

                if (false !== strpos(max_discount, '%'))
                    max_discount = ADDONS.points.calculate_discount_modifier(max_discount);

                if (max_discount && max_discount < discount_applied) {
                    discount_applied = max_discount;
                }

                return discount_applied;
            },
            calculate_points_for_discount: function (discount_amount) {
                var ratio = wc_points_and_rewards.redeem_ratio.split(':');
                var points = parseFloat(ratio[0]);
                var monetary_value = typeof ratio[1] != 'undefined' ? ratio[1] : 1;

                var required_points = discount_amount * ( points / monetary_value );

                // to prevent any rounding errors we need to round off any fractions
                // ex. 408.000000001 should require 408 points but 408.50 should require 409
                required_points = floor(required_points * 100);
                required_points = required_points / 100;

                return Math.ceil(required_points);
            },

            calculate_points_value: function (amount) {

                var ratio = wc_points_and_rewards.redeem_ratio.split(':');
                var points = parseFloat(ratio[0]);
                var monetary_value = typeof ratio[1] != 'undefined' ? ratio[1] : '';

                return number_format(amount * ( monetary_value / points ), 2, '.', '');
            },

            set_discount_for_redeeming_points: function () {
                var points_balance = '';
                if (typeof CUSTOMER.points_balance != 'undefined') {
                    points_balance = CUSTOMER.points_balance;
                }
                if ($('#wc_points_rewards_tab').length && points_balance != '') {
                    var discount_available = ADDONS.points.get_discount_for_redeeming_points(points_balance);
                    formated_discount = accountingPOS(discount_available, 'formatMoney');
                    var points = ADDONS.points.calculate_points_for_discount(discount_available);
                    // points required to redeem for the discount available
                    $('#wc_points_rewards_number_of_points').text(points);
                    $('#wc_points_rewards_points_value').html(formated_discount);

                    if (typeof CART.coupons['WC_POINTS_REDEMPTION'] != 'undefined' && discount_available != CART.coupons['WC_POINTS_REDEMPTION']['amount']) {
                        CUSTOMER.points_redeemed = points;
                        CART.add_custom_discount(discount_available, 'fixed_cart', 'WC_POINTS_REDEMPTION');
                    }
                }
            },
            apply_discount: function () {
                var points_balance = '';
                if (typeof CUSTOMER.points_balance != 'undefined') {
                    points_balance = CUSTOMER.points_balance;
                }
                if ($('#wc_points_rewards_tab').length && points_balance != '') {
                    var discount_available = ADDONS.points.get_discount_for_redeeming_points(points_balance);
                    var points = ADDONS.points.calculate_points_for_discount(discount_available);
                    CART.add_custom_discount(discount_available, 'fixed_cart', 'WC_POINTS_REDEMPTION');
                    CUSTOMER.points_redeemed = points;
                    closeModal('modal-order_discount');
                }
            },
            apply_fee: function () {
                var taxable = (typeof $('#taxable-fee:checked').val() !== 'undefined') ? true : false;
                var fee_type = 'fixed';
                if ($('.keypad-percent_symbol.active').length > 0) {
                    fee_type = 'percent';
                }
                var fee = {
                    name: $('#modal-add_custom_fee #fee-name').val(),
                    amount: floatval($('#modal-add_custom_fee .keypad-keyentry').val()),
                    value: floatval($('#modal-add_custom_fee .keypad-keyentry').val()),
                    taxable: taxable,
                    type: fee_type
                };
                CART.add_custom_fee(fee);
                closeModal('modal-add_custom_fee');
            }
        },
        securesubmit: {
            // Handles tokenization response
            responseHandler: function (response) {

                if (response.error) {
                    APP.showNotice(response.error.message, 'error');
                } else {

                    var data = {
                        'last_four': response.last_four,
                        'card_type': response.card_type,
                        'exp_month': response.exp_month,
                        'exp_year': response.exp_year,
                        'securesubmit_token': response.token_value
                    };
                    var token = document.getElementById('securesubmit_token');
                    token.value = response.token_value;

                    if (typeof ADDONS.cart.order.create_post == 'undefined') {
                        ADDONS.cart.order.create_post = [];
                    }
                    ADDONS.cart.order.create_post.push(data);
                    APP.processPayment(ADDONS.cart, true);
                }
                setTimeout(function () {
                    document.getElementById('securesubmit_token').value = '';
                }, 500);

            },
        },
    }
});

function filter(elements, fun) {
    var i = 0;
    var length = elements.length;
    var result = [];
    for (i; i < length; i++) {
        if (fun(elements[i]) === true) {
            result.push(elements[i]);
        }
    }
    return result;
}

function SwipeParserObj(strParse) {
    ///////////////////////////////////////////////////////////////
    ///////////////////// member variables ////////////////////////
    this.input_trackdata_str = strParse;
    this.account_name = null;
    this.surname = null;
    this.firstname = null;
    this.acccount = null;
    this.exp_month = null;
    this.exp_year = null;
    this.track1 = null;
    this.track2 = null;
    this.hasTrack1 = false;
    this.hasTrack2 = false;
    /////////////////////////// end member fields /////////////////


    sTrackData = this.input_trackdata_str;     //--- Get the track data

    //-- Example: Track 1 & 2 Data
    //-- %B1234123412341234^CardUser/John^030510100000019301000000877000000?;1234123412341234=0305101193010877?
    //-- Key off of the presence of "^" and "="

    //-- Example: Track 1 Data Only
    //-- B1234123412341234^CardUser/John^030510100000019301000000877000000?
    //-- Key off of the presence of "^" but not "="

    //-- Example: Track 2 Data Only
    //-- 1234123412341234=0305101193010877?
    //-- Key off of the presence of "=" but not "^"

    if (strParse != '') {
        // alert(strParse);

        //--- Determine the presence of special characters
        nHasTrack1 = strParse.indexOf("^");
        nHasTrack2 = strParse.indexOf("=");

        //--- Set boolean values based off of character presence
        this.hasTrack1 = bHasTrack1 = false;
        this.hasTrack2 = bHasTrack2 = false;
        if (nHasTrack1 > 0) {
            this.hasTrack1 = bHasTrack1 = true;
        }
        if (nHasTrack2 > 0) {
            this.hasTrack2 = bHasTrack2 = true;
        }

        //--- Test messages
        // alert('nHasTrack1: ' + nHasTrack1 + ' nHasTrack2: ' + nHasTrack2);
        // alert('bHasTrack1: ' + bHasTrack1 + ' bHasTrack2: ' + bHasTrack2);

        //--- Initialize
        bTrack1_2 = false;
        bTrack1 = false;
        bTrack2 = false;

        //--- Determine tracks present
        if (( bHasTrack1) && ( bHasTrack2)) {
            bTrack1_2 = true;
        }
        if (( bHasTrack1) && (!bHasTrack2)) {
            bTrack1 = true;
        }
        if ((!bHasTrack1) && ( bHasTrack2)) {
            bTrack2 = true;
        }

        //--- Test messages
        // alert('bTrack1_2: ' + bTrack1_2 + ' bTrack1: ' + bTrack1 + ' bTrack2: ' + bTrack2);

        //--- Initialize alert message on error
        bShowAlert = false;

        //-----------------------------------------------------------------------------
        //--- Track 1 & 2 cards
        //--- Ex: B1234123412341234^CardUser/John^030510100000019301000000877000000?;1234123412341234=0305101193010877?
        //-----------------------------------------------------------------------------
        if (bTrack1_2) {
//      alert('Track 1 & 2 swipe');

            strCutUpSwipe = '' + strParse + ' ';
            arrayStrSwipe = new Array(4);
            arrayStrSwipe = strCutUpSwipe.split("^");

            var sAccountNumber, sName, sShipToName, sMonth, sYear;

            if (arrayStrSwipe.length > 2) {
                this.account = stripAlpha(arrayStrSwipe[0].substring(1, arrayStrSwipe[0].length));
                this.c_type = detectCardType(this.account);
                this.account_name = arrayStrSwipe[1];
                this.exp_month = arrayStrSwipe[2].substring(2, 4);
                this.exp_year = '20' + arrayStrSwipe[2].substring(0, 2);
                this.short_exp_year = arrayStrSwipe[2].substring(0, 2);


                //--- Different card swipe readers include or exclude the % in the front of the track data - when it's there, there are
                //---   problems with parsing on the part of credit cards processor - so strip it off
                if (sTrackData.substring(0, 1) == '%') {
                    sTrackData = sTrackData.substring(1, sTrackData.length);
                }

                var track2sentinel = sTrackData.indexOf(";");
                if (track2sentinel != -1) {
                    this.track1 = sTrackData.substring(0, track2sentinel);
                    this.track2 = sTrackData.substring(track2sentinel);
                }

                //--- parse name field into first/last names
                var nameDelim = this.account_name.indexOf("/");
                if (nameDelim != -1) {
                    this.surname = this.account_name.substring(0, nameDelim);
                    this.firstname = this.account_name.substring(nameDelim + 1);
                }
            }
            else  //--- for "if ( arrayStrSwipe.length > 2 )"
            {
                bShowAlert = true;  //--- Error -- show alert message
            }
        }

        //-----------------------------------------------------------------------------
        //--- Track 1 only cards
        //--- Ex: B1234123412341234^CardUser/John^030510100000019301000000877000000?
        //-----------------------------------------------------------------------------
        if (bTrack1) {
//      alert('Track 1 swipe');

            strCutUpSwipe = '' + strParse + ' ';
            arrayStrSwipe = new Array(4);
            arrayStrSwipe = strCutUpSwipe.split("^");

            var sAccountNumber, sName, sShipToName, sMonth, sYear;

            if (arrayStrSwipe.length > 2) {
                this.account = sAccountNumber = stripAlpha(arrayStrSwipe[0].substring(1, arrayStrSwipe[0].length));
                this.account_name = sName = arrayStrSwipe[1];
                this.exp_month = sMonth = arrayStrSwipe[2].substring(2, 4);
                this.exp_year = sYear = '20' + arrayStrSwipe[2].substring(0, 2);


                //--- Different card swipe readers include or exclude the % in
                //--- the front of the track data - when it's there, there are
                //---   problems with parsing on the part of credit cards processor - so strip it off
                if (sTrackData.substring(0, 1) == '%') {
                    this.track1 = sTrackData = sTrackData.substring(1, sTrackData.length);
                }

                //--- Add track 2 data to the string for processing reasons
//        if (sTrackData.substring(sTrackData.length-1,1) != '?')  //--- Add a ? if not present
//        { sTrackData = sTrackData + '?'; }
                this.track2 = ';' + sAccountNumber + '=' + sYear.substring(2, 4) + sMonth + '111111111111?';
                sTrackData = sTrackData + this.track2;

                //--- parse name field into first/last names
                var nameDelim = this.account_name.indexOf("/");
                if (nameDelim != -1) {
                    this.surname = this.account_name.substring(0, nameDelim);
                    this.firstname = this.account_name.substring(nameDelim + 1);
                }

            }
            else  //--- for "if ( arrayStrSwipe.length > 2 )"
            {
                bShowAlert = true;  //--- Error -- show alert message
            }
        }

        //-----------------------------------------------------------------------------
        //--- Track 2 only cards
        //--- Ex: 1234123412341234=0305101193010877?
        //-----------------------------------------------------------------------------
        if (bTrack2) {
//      alert('Track 2 swipe');

            nSeperator = strParse.indexOf("=");
            sCardNumber = strParse.substring(1, nSeperator);
            sYear = strParse.substr(nSeperator + 1, 2);
            sMonth = strParse.substr(nSeperator + 3, 2);

            // alert(sCardNumber + ' -- ' + sMonth + '/' + sYear);

            this.account = sAccountNumber = stripAlpha(sCardNumber);
            this.exp_month = sMonth = sMonth;
            this.exp_year = sYear = '20' + sYear;

            //--- Different card swipe readers include or exclude the % in the front of the track data - when it's there,
            //---  there are problems with parsing on the part of credit cards processor - so strip it off
            if (sTrackData.substring(0, 1) == '%') {
                sTrackData = sTrackData.substring(1, sTrackData.length);
            }

        }

        //-----------------------------------------------------------------------------
        //--- No Track Match
        //-----------------------------------------------------------------------------
        if (((!bTrack1_2) && (!bTrack1) && (!bTrack2)) || (bShowAlert)) {
            //alert('Difficulty Reading Card Information.\n\nPlease Swipe Card Again.');
        }

//    alert('Track Data: ' + document.formFinal.trackdata.value);

        //document.formFinal.trackdata.value = replaceChars(document.formFinal.trackdata.value,';','');
        //document.formFinal.trackdata.value = replaceChars(document.formFinal.trackdata.value,'?','');

//    alert('Track Data: ' + document.formFinal.trackdata.value);

    } //--- end "if ( strParse != '' )"


    this.dump = function () {
        var s = "";
        var sep = "\r"; // line separator
        s += "Name: " + this.account_name + sep;
        s += "Surname: " + this.surname + sep;
        s += "first name: " + this.firstname + sep;
        s += "account: " + this.account + sep;
        s += "exp_month: " + this.exp_month + sep;
        s += "exp_year: " + this.exp_year + sep;
        s += "has track1: " + this.hasTrack1 + sep;
        s += "has track2: " + this.hasTrack2 + sep;
        s += "TRACK 1: " + this.track1 + sep;
        s += "TRACK 2: " + this.track2 + sep;
        s += "Raw Input Str: " + this.input_trackdata_str + sep;

        return s;
    }
    this.obj = function () {
        var data = {
            name: this.account_name,
            surname: this.surname,
            firstname: this.firstname,
            account: this.account,
            c_type: this.c_type,
            exp_month: this.exp_month,
            exp_year: this.exp_year,
            s_exp_year: this.short_exp_year,
            hasTrack1: this.hasTrack1,
            hasTrack2: this.hasTrack2,
            track1: this.track1,
            track2: this.track2,
            trackdata_str: this.input_trackdata_str,
        };
        return data;
    }

    function stripAlpha(sInput) {
        if (sInput == null)    return '';
        return sInput.replace(/[^0-9]/g, '');
    }

}
function detectCardType(number) {
    var re = {
        electron: /^(4026|417500|4405|4508|4844|4913|4917)\d+$/,
        maestro: /^(5018|5020|5038|5612|5893|6304|6759|6761|6762|6763|0604|6390)\d+$/,
        dankort: /^(5019)\d+$/,
        interpayment: /^(636)\d+$/,
        unionpay: /^(62|88)\d+$/,
        visa: /^4[0-9]{12}(?:[0-9]{3})?$/,
        mastercard: /^5[1-5][0-9]{14}$/,
        amex: /^3[47][0-9]{13}$/,
        diners: /^3(?:0[0-5]|[68][0-9])[0-9]{11}$/,
        discover: /^6(?:011|5[0-9]{2})[0-9]{12}$/,
        jcb: /^(?:2131|1800|35\d{3})\d{11}$/,
        laser: /^(6304|6706|6709|6771)[0-9]{12,15}$/,
        switch_: /^(4903|4905|4911|4936|6333|6759)[0-9]{12}|(4903|4905|4911|4936|6333|6759)[0-9]{14}|(4903|4905|4911|4936|6333|6759)[0-9]{15}|564182[0-9]{10}|564182[0-9]{12}|564182[0-9]{13}|633110[0-9]{10}|633110[0-9]{12}|633110[0-9]{13}$/,
    };
    if (re.electron.test(number)) {
        return ['ELECTRON', 'ELECTRON'];
    } else if (re.maestro.test(number)) {
        return ['MAESTRO', 'MAESTRO'];
    } else if (re.dankort.test(number)) {
        return ['DANKORT', 'DANKORT'];
    } else if (re.interpayment.test(number)) {
        return ['INTERPAYMENT', 'INTERPAYMENT'];
    } else if (re.unionpay.test(number)) {
        return ['UNIONPAY', 'UNIONPAY'];
    } else if (re.visa.test(number)) {
        return ['VISA', 'VISA'];
    } else if (re.mastercard.test(number)) {
        return ['MASTERCARD', 'MC'];
    } else if (re.amex.test(number)) {
        return ['AMEX', 'AMEX'];
    } else if (re.diners.test(number)) {
        return ['DINERS', 'DINERS'];
    } else if (re.discover.test(number)) {
        return ['DISCOVER', 'DISCOVER'];
    } else if (re.jcb.test(number)) {
        return ['JCB', 'JCB'];
    } else if (re.laser.test(number)) {
        return ['LASER', 'LASER'];
    } else if (re.switch_.test(number)) {
        return ['SWITCH', 'SWITCH'];
    } else {
        return undefined;
    }
}