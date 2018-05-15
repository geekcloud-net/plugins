jQuery(document).ready(function ($) {
    localStorage.setItem('cart_' + pos_register_data.ID, '{}');
    window.POS_CART = {
        /** @var object Contains an object of cart items. */
        cart_contents: {},
        /** @var object Contains an object of coupon codes applied to the cart. */
        applied_coupons: [],
        /** @var object Contains an object of coupon code discounts after they have been applied. */
        coupon_discount_amounts: {},
        /** @var object Contains an object of coupon code discounts after they have been applied (without custom discount). */
        _coupon_discount_amounts: {},
        /** @var object Contains an object of coupon code discount taxes. Used for tax incl pricing. */
        coupon_discount_tax_amounts: {},
        /** @var object Contains an object of coupon code discount taxes. Used for tax incl pricing (without custom discount). */
        _coupon_discount_tax_amounts: {},
        /** @var object Contains an object of coupon usage counts after they have been applied. */
        coupon_applied_count: {},
        /** @var object Object of coupons */
        coupons: {},
        /** @var object Contains an object of shipping methods. */
        chosen_shipping_methods: {title: '', price: ''},
        /** @var float The total cost of the cart items. */
        cart_contents_total: 0,
        /** @var float The total weight of the cart items. */
        cart_contents_weight: 0,
        /** @var float The total count of the cart items. */
        cart_contents_count: 0,
        /** @var float Cart grand total. */
        total: 0,
        /** @var float Cart subtotal. */
        subtotal: 0,
        /** @var float Cart subtotal without tax. */
        subtotal_ex_tax: 0,

        /** @var float Total cart tax. */
        tax_total: 0,

        /** @var object An object of taxes/tax rates for the cart. */
        taxes: {},

        /** @var object An object of taxes/tax rates for the shipping. */
        shipping_taxes: {},

        /** @var float Discount amount before tax */
        discount_cart: 0,

        /** @var float Discounted tax amount. Used predominantly for displaying tax inclusive prices correctly */
        discount_cart_tax: 0,

        /** @var float Total for additional fees. */
        fee_total: 0,

        /** @var float Shipping cost. */
        shipping_total: 0,

        /** @var float Shipping tax. */
        shipping_tax_total: 0,

        /** @var string Customer note. */
        customer_note: '',

        /** @var object cart_session_data. object of data the cart calculates and stores in the session with defaults */
        cart_session_data: {
            'cart_contents_total': 0,
            'cart_contents_weight': 0,
            'cart_contents_count': 0,
            'total': 0,
            'subtotal': 0,
            'subtotal_ex_tax': 0,
            'tax_total': 0,
            'taxes': {},
            'shipping_taxes': {},
            'discount_cart': 0,
            'discount_cart_tax': 0,
            'shipping_total': 0,
            'shipping_tax_total': 0,
            'coupon_discount_amounts': {},
            '_coupon_discount_amounts': {},
            'coupon_discount_tax_amounts': {},
            '_coupon_discount_tax_amounts': {},
            'fee_total': 0,
            'fees': {}
        },
        cart_empty_data: {
            'cart_contents': {},
            'applied_coupons': [],
            'coupon_applied_count': {},
            'coupons': {},
            'chosen_shipping_methods': {title: '', price: ''},
            'customer_note': '',
        },

        /** @var object An object of fees. */
        fees: {},

        /** @var boolean Prices inc tax */
        prices_include_tax: pos_cart.prices_include_tax,

        /** @var boolean */
        round_at_subtotal: pos_cart.round_at_subtotal,

        /** @var string */
        tax_display_cart: pos_cart.tax_display_cart,

        /** @var int Prices inc tax */
        dp: pos_cart.dp,

        /** @var boolean */
        display_totals_ex_tax: pos_cart.display_totals_ex_tax,

        /** @var boolean */
        display_cart_ex_tax: pos_cart.display_cart_ex_tax,

        init: function () {
            wp.hooks.addAction('wc_pos_add_to_cart', CART.calculate_totals, 80, 0);
            wp.hooks.addAction('wc_pos_applied_coupon', CART.calculate_totals, 80, 0);
        },
        /*-----------------------------------------------------------------------------------*/
        /* Add to cart handling */
        /*-----------------------------------------------------------------------------------*/

        get_cart: function () {
            return CART.cart_contents;
        },
        set_quantity: function (cart_item_key, quantity, refresh_totals) {
            if (typeof quantity == 'undefined') {
                quantity = 1;
            }
            if (typeof refresh_totals == 'undefined') {
                refresh_totals = true;
            }
            if (quantity == 0 || quantity < 0) {
                delete cart_contents[cart_item_key];
            } else {
                var old_quantity = CART.cart_contents[cart_item_key]['quantity'];

                CART.cart_contents[cart_item_key]['quantity'] = quantity;
            }

            if (refresh_totals) {
                CART.calculate_totals();
            }

            return true;
        },
        addToCart: function (product_data, product_id, quantity, variation_id, variation, cart_item_data) {
            if (typeof product_data == 'undefined') {
                APP.showNotice(pos_i18n[0], 'error');
                return;
            }
            product_data = wp.hooks.applyFilters('wc_pos_add_to_cart_product_data', product_data, product_id, quantity, variation_id, variation, cart_item_data);
            if (typeof product_id == 'undefined') {
                product_id = product_data.id;
            }
            if (typeof quantity == 'undefined') {
                quantity = wc_pos_params.decimal_quantity_value;
            }
            if (typeof variation_id == 'undefined') {
                variation_id = 0;
            }
            if (typeof variation == 'undefined') {
                variation = {};
            }
            if (typeof cart_item_data == 'undefined') {
                cart_item_data = {};
            }
            try {
                product_id = parseInt(product_id);
                variation_id = parseInt(variation_id);

                var variation_data = false;

                // Get the variation
                if (variation_id > 0 && typeof product_data.variations != 'undefined') {
                    $.each(product_data.variations, function (i, variation) {
                        if (variation.id == variation_id) {
                            variation_data = variation;
                        }
                    });
                }
                if (variation_data) {
                    var _variation = {};
                    $.each(variation, function (slug, opt) {
                        var key = slug;
                        var val = opt;
                        $.each(product_data.attributes, function (index, attr) {
                            if (attr.slug === slug) {
                                key = attr.name;
                                $.each(attr.options, function (i, o) {
                                    if (opt == o.slug) {
                                        val = o.name;
                                        return;
                                    }
                                });
                            }
                        });
                        _variation[key] = val;
                    });
                    variation = _variation;
                }
                variation = wp.hooks.applyFilters('wc_pos_cart_variation', variation, product_data, product_id, quantity, variation_id, cart_item_data);
                // Generate a ID based on product ID, variation ID, variation data, and other cart item data
                var cart_id = CART.generate_cart_id(product_id, variation_id, variation, cart_item_data);

                // Find the cart item key in the existing cart
                var cart_item_key = CART.find_product_in_cart(cart_id);
                // Force quantity to 1 if sold individually and check for exisitng item in cart
                if (product_data.sold_individually === true) {
                    quantity = 1;
                    var in_cart_quantity = cart_item_key ? CART.cart_contents[cart_item_key]['quantity'] : 0;

                    if (in_cart_quantity > 0) {
                        throw new Error(sprintf(pos_i18n[1], product_data.title));
                    }
                }

                // Check product is_purchasable
                if (product_data.purchaseable === false) {
                    throw new Error(pos_i18n[2]);
                }
                // Stock check - only check if we're managing stock and backorders are not allowed
                if (variation_data) {
                    if (variation_data.in_stock === false && variation_data.backorders_allowed === false) {
                        throw new Error(sprintf(pos_i18n[3], product_data.title));
                    }
                    if (CART.has_enough_stock(variation_data, quantity) === false) {
                        if (wc_pos_params.decimal_quantity == 'yes' && !cart_item_key && variation_data.stock_quantity > 0) {
                            quantity = variation_data.stock_quantity;
                        } else {
                            throw new Error(sprintf(pos_i18n[4], product_data.title, variation_data.stock_quantity));
                        }
                    }
                    // Stock check - this time accounting for whats already in-cart
                    if (variation_data.managing_stock === true) {
                        var managing_stock = variation_data.managing_stock;
                        var products_qty_in_cart = CART.get_cart_item_quantities();
                        var check_qty = typeof products_qty_in_cart[variation_id] != 'undefined' ? products_qty_in_cart[variation_id] : 0;

                        /**
                         * Check stock based on all items in the cart
                         */
                        if (CART.has_enough_stock(variation_data, check_qty + quantity) === false) {
                            throw new Error(sprintf(pos_i18n[5], variation_data.stock_quantity, check_qty));
                        }
                    }
                } else if (product_data.id != pos_custom_product.id) {
                    if (product_data.in_stock === false && product_data.backorders_allowed === false) {
                        throw new Error(sprintf(pos_i18n[3], product_data.title));
                    }
                    if (CART.has_enough_stock(product_data, quantity) === false) {
                        if (wc_pos_params.decimal_quantity == 'yes' && !cart_item_key && product_data.stock_quantity > 0) {
                            quantity = product_data.stock_quantity;
                        } else {
                            throw new Error(sprintf(pos_i18n[4], product_data.title, product_data.stock_quantity));
                        }
                    }
                    // Stock check - this time accounting for whats already in-cart
                    if (product_data.managing_stock === true) {
                        var managing_stock = product_data.managing_stock;
                        var products_qty_in_cart = CART.get_cart_item_quantities();
                        var check_qty = typeof products_qty_in_cart[product_id] != 'undefined' ? products_qty_in_cart[product_id] : 0;

                        /**
                         * Check stock based on all items in the cart
                         */
                        if (CART.has_enough_stock(product_data, check_qty + quantity) === false) {
                            throw new Error(sprintf(pos_i18n[5], product_data.stock_quantity, check_qty));
                        }
                    }
                }
                // If cart_item_key is set, the item is already in the cart
                if (cart_item_key) {
                    var new_quantity = parseFloat(quantity) + parseFloat(CART.cart_contents[cart_item_key]['quantity']);
                    CART.set_quantity(cart_item_key, new_quantity, false);
                    $('#' + cart_item_key + ' .quantity').val(new_quantity);
                } else {
                    cart_item_key = cart_id;

                    if (variation_id > 0) {
                        var formatedprice = accountingPOS(variation_data.price, 'formatMoney');
                        var price = variation_data.price;
                    } else {
                        var formatedprice = accountingPOS(product_data.price, 'formatMoney');
                        var price = product_data.price;
                    }

                    // Add item after merging with $cart_item_data - hook to allow plugins to modify cart item
                    var _extend = $.extend(cart_item_data, {
                        product_id: product_id,
                        variation_id: variation_id,
                        variation: variation,
                        quantity: quantity,
                        price: price,
                        formatedprice: formatedprice,
                        item_id: typeof(product_data.item_id) != 'undefined' ? product_data.item_id : 0,
                        data: product_data,
                        v_data: variation_data,
                    });
                    cart_item_data = wp.hooks.applyFilters('tmpl_cart_item_data', cart_item_data);
                    CART.cart_contents[cart_item_key] = cart_item_data;
                    var source = $('#tmpl-cart-product-item').html();
                    var template = Handlebars.compile(source);
                    var editable = cart_item_data.data.type == 'booking' ? false : true;
                    editable = true;
                    var context = {cart_item_data: cart_item_data, cart_item_key: cart_item_key, editable: editable};
                    var html = template(context);
                    $('#order_items_list').append(html);
                    jQuery("div#order_items_list-wrapper").scrollTop(jQuery("div#order_items_list-wrapper")[0].scrollHeight);

                    if ($('#order_items_list .new_row').length > 0) {
                        calculateSelectedQuantity();
                        calculateSelectedPrice();
                        $('#order_items_list tr').removeClass("new_row")
                    }
                    if (typeof product_data.loaded_price != 'undefined' && cart_item_data.price != product_data.loaded_price) {
                        var $qty_inp = $('#order_items_list tr').last().find('.line_cost .product_price');
                        $qty_inp.val(product_data.loaded_price);
                        changeProductPrice($qty_inp);
                    }
                }
                wp.hooks.doAction('wc_pos_add_to_cart', cart_item_key, product_id, quantity, variation_id, variation, cart_item_data);
                return cart_item_key;
            } catch (e) {
                console.log(e);
                APP.showNotice(e.message, 'error');
            }
        },
        /**
         * Remove a cart item
         *
         * @since  2.3.0www.kouvolanautokiilto.fi/wp-admin
         * @param  string $cart_item_key
         * @return bool
         */
        remove_cart_item: function (cart_item_key) {
            if (typeof CART.cart_contents[cart_item_key] != 'undefined') {

                delete CART.cart_contents[cart_item_key];
                CART.calculate_totals();

                return true;
            }

            return false;
        },
        has_enough_stock: function (product_data, quantity) {
            if (typeof quantity == 'undefined') {
                quantity = 0;
            }
            ;
            var has_enough_stock = product_data.managing_stock === false || product_data.backorders_allowed === true || product_data.stock_quantity >= quantity ? true : false;
            return has_enough_stock;
        },
        generate_cart_id: function (product_id, variation_id, variation, cart_item_data) {
            var id_parts = [product_id];
            if (variation_id && 0 != variation_id) {
                id_parts.push(variation_id);
            }

            if (typeof variation == 'object') {
                var variation_key = '';
                $.each(variation, function (key, value) {
                    if (typeof key != 'string') {
                        key = key.toString();
                    }
                    if (typeof value != 'string') {
                        value = value.toString();
                    }
                    variation_key += key.trim() + value.trim();
                });
                id_parts.push(variation_key);
            }

            if (typeof cart_item_data == 'object') {
                var cart_item_data_key = '';
                $.each(cart_item_data, function (key, value) {
                    if (typeof key != 'string') {
                        key = key.toString();
                    }

                    if (typeof value == 'object') {
                        value = http_build_query(value);
                    }
                    else if (typeof value != 'string') {
                        value = value.toString();
                    }
                    cart_item_data_key += key.trim() + value.trim();
                });
                if (cart_item_data_key != '') {
                    id_parts.push(cart_item_data_key);
                }
            }

            if (product_id == pos_custom_product.id) {
                id_parts.push(time());
            }
            id_parts = id_parts.join('_');
            return md5(id_parts);
        },
        find_product_in_cart: function (cart_id) {
            var result = '';
            if (typeof cart_id != 'undefined') {
                if (typeof CART.cart_contents == 'object') {
                    $.each(CART.cart_contents, function (cart_item_key, cart_item) {
                        if (cart_item_key == cart_id) {
                            result = cart_item_key;
                            return result;
                        }
                    });
                }
            }
            return result;
        },
        /*-----------------------------------------------------------------------------------*/
        /* Cart Data Functions */
        /*-----------------------------------------------------------------------------------*/

        /**
         * Coupons enabled function. Filterable.
         *
         * @return bool
         */
        coupons_enabled: function () {
            return pos_cart.enable_coupons;
        },

        /**
         * Get number of items in the cart.
         *
         * @return int
         */
        get_cart_contents_count: function () {
            return CART.cart_contents_count;
        },

        /**
         * Checks if the cart is empty.
         *
         * @return bool
         */
        is_empty: function () {
            var cart = CART.get_cart();
            var size = Object.size(cart);
            return 0 === size;
        },
        get_cart_item_quantities: function () {
            var quantities = [];
            var cart = CART.get_cart();
            $.each(cart, function (cart_item_key, values) {

                if (values.variation_id > 0) {
                    quantities[values.variation_id] = typeof quantities[values.variation_id] != 'undefined' ? quantities[values.variation_id] + values['quantity'] : values['quantity'];
                } else {
                    quantities[values['product_id']] = typeof quantities[values['product_id']] != 'undefined' ? quantities[values['product_id']] + values['quantity'] : values['quantity'];
                }
            });

            return quantities;
        },

        /**
         * Get all tax classes for items in the cart
         * @return array
         */
        get_cart_item_tax_classes: function () {
            var found_tax_classes = [];
            var items = this.get_cart();
            $.each(items, function (index, item) {
                var _product = item['data'];

                if (item.variation_id > 0) {
                    _product = item['v_data'];
                }
                found_tax_classes.push(_product.tax_class);
            });

            return array_unique(found_tax_classes);
        },

        /*-----------------------------------------------------------------------------------*/
        /* Cart Calculation Functions */
        /*-----------------------------------------------------------------------------------*/

        /**
         * Reset cart totals to the defaults. Useful before running calculations.
         *
         * @param  bool    $unset_session If true, the session data will be forced unset.
         * @access private
         */
        reset: function () {
            $.each(CART.cart_session_data, function (_key, _default) {
                if (_key == 'taxes') {
                    _default = {};
                }
                window.POS_CART[_key] = clone(_default);
            });
        },

        /**
         * Empties the cart and optionally the persistent cart too.
         *
         * @param bool $clear_persistent_cart (default: true)
         */
        empty_cart: function (updateCastomer) {
            $.each(CART.cart_empty_data, function (key, _default) {
                window.POS_CART[key] = clone(_default);
            });

            if (updateCastomer !== false) {
                if (pos_default_customer) {
                    APP.setCustomer(pos_default_customer);
                } else {
                    APP.setGuest();
                }
            }
            //CART.calculate_totals();
            $('#order_items_list').html('');
            $('#order_comments').val('');
        },
        /**
         * Calculate totals for the items in the cart.
         */
        calculate_totals: function () {
            CART.reset();
            var points_earned = 0;
            var tax_rates = {};
            var shop_tax_rates = {};
            var cart = CART.get_cart();
            /**
             * Calculate subtotals for items. This is done first so that discount logic can use the values.
             */
            $.each(cart, function (cart_item_key, values) {

                var _product = values['data'];

                if (values.variation_id > 0) {
                    _product = values['v_data'];
                }

                // Count items + weight
                CART.cart_contents_weight += _product.weight * values['quantity'];
                CART.cart_contents_count += values['quantity'];

                // Prices
                var base_price = parseFloat(_product.price);
                var base_price = wp.hooks.applyFilters('calculate_totals_base_price', parseFloat(_product.price), values);
                var line_price = base_price * values['quantity'];

                var line_subtotal = 0;
                var line_subtotal_tax = 0;
                /**
                 * No tax to calculate
                 */
                if (!_product.taxable || wc_pos_params.pos_calc_taxes === false) {

                    // Subtotal is the undiscounted price
                    CART.subtotal += line_price;
                    CART.subtotal_ex_tax += line_price;

                    /**
                     * Prices include tax
                     *
                     * To prevent rounding issues we need to work with the inclusive price where possible
                     * otherwise we'll see errors such as when working with a 9.99 inc price, 20% VAT which would
                     * be 8.325 leading to totals being 1p off
                     *
                     * Pre tax coupons come off the price the customer thinks they are paying - tax is calculated
                     * afterwards.
                     *
                     * e.g. $100 bike with $10 coupon = customer pays $90 and tax worked backwards from that
                     */
                } else if (CART.prices_include_tax) {

                    // Get base tax rates
                    if (typeof shop_tax_rates[_product.tax_class] == 'undefined') {
                        shop_tax_rates[_product.tax_class] = TAX.get_base_tax_rates(_product.tax_class);
                    }

                    // Get item tax rates
                    if (typeof tax_rates[_product.tax_class] == 'undefined') {
                        tax_rates[_product.tax_class] = TAX.get_rates(_product.tax_class);
                    }

                    var base_tax_rates = shop_tax_rates[_product.tax_class];
                    var item_tax_rates = tax_rates[_product.tax_class];

                    /**
                     * ADJUST TAX - Calculations when base tax is not equal to the item tax
                     */
                    if (item_tax_rates !== base_tax_rates) {

                        // Work out a new base price without the shop's base tax
                        var taxes = TAX.calc_tax(line_price, base_tax_rates, true, true);

                        // Now we have a new item price (excluding TAX)
                        line_subtotal = line_price - array_sum(taxes);

                        // Now add modified taxes
                        var tax_result = TAX.calc_tax(line_subtotal, item_tax_rates);
                        line_subtotal_tax = array_sum(tax_result);

                        /**
                         * Regular tax calculation (customer inside base and the tax class is unmodified
                         */
                    } else {

                        // Calc tax normally
                        var taxes = TAX.calc_tax(line_price, item_tax_rates, true);
                        line_subtotal_tax = array_sum(taxes);
                        line_subtotal = line_price - array_sum(taxes);
                    }


                    /**
                     * Prices exclude tax
                     *
                     * This calculation is simpler - work with the base, untaxed price.
                     */
                } else {

                    // Get item tax rates
                    if (typeof tax_rates[_product.tax_class] == 'undefined') {
                        tax_rates[_product.tax_class] = TAX.get_rates(_product.tax_class);
                    }

                    item_tax_rates = tax_rates[_product.tax_class];

                    // Base tax for line before discount - we will store this in the order data
                    var taxes = TAX.calc_tax(line_price, item_tax_rates);

                    line_subtotal_tax = array_sum(taxes);
                    line_subtotal = line_price;
                }
                // Add to main subtotal
                CART.subtotal += line_subtotal + line_subtotal_tax;
                CART.subtotal_ex_tax += line_subtotal;
            });

            // Order cart items by price so coupon logic is 'fair' for customers and not based on order added to cart.
            cart = uasort(cart, CART.sort_by_subtotal);

            /**
             * Calculate totals for items
             */
            $.each(cart, function (cart_item_key, values) {
                var _product = values['data'];

                if (values.variation_id > 0) {
                    _product = values['v_data'];
                }

                // Prices
                var base_price = parseFloat(_product.price);
                base_price = wp.hooks.applyFilters('calculate_totals_base_price', base_price, values);
                var line_price = base_price * values['quantity'];
                var line_o_price = line_price;
                if (typeof values['original_price'] != 'undefined') {
                    line_o_price = parseFloat(values['original_price']) * values['quantity'];
                    line_o_price = wp.hooks.applyFilters('calculate_totals_line_o_price', line_o_price, values);
                }


                // Tax data
                var taxes = [];
                var o_taxes = [];
                var discounted_taxes = [];

                /**
                 * No tax to calculate
                 */
                if (!_product.taxable || wc_pos_params.pos_calc_taxes === false) {

                    // Discounted Price (price with any pre-tax discounts applied)
                    var discounted_price = CART.get_discounted_price(values, base_price, true);
                    var line_subtotal_tax = 0;
                    var line_subtotal = line_price;
                    var line_tax = 0;

                    var line_o_subtotal_tax = 0;
                    var line_o_subtotal = line_o_price;


                    var line_total = TAX.round(discounted_price * values['quantity']);

                    /**
                     * Prices include tax
                     */
                } else if (CART.prices_include_tax) {

                    var base_tax_rates = shop_tax_rates[_product.tax_class];
                    var item_tax_rates = tax_rates[_product.tax_class];


                    /**
                     * ADJUST TAX - Calculations when base tax is not equal to the item tax
                     */

                    if (JSON.stringify(item_tax_rates) !== JSON.stringify(base_tax_rates)) {

                        // Work out a new base price without the shop's base tax
                        taxes = TAX.calc_tax(line_price, base_tax_rates, true, true);
                        o_taxes = TAX.calc_tax(line_o_price, base_tax_rates, true, true);

                        // Now we have a new item price (excluding TAX)
                        var line_subtotal = TAX.round(line_price - array_sum(taxes));
                        taxes = TAX.calc_tax(line_subtotal, item_tax_rates);
                        var line_subtotal_tax = array_sum(taxes);

                        var line_o_subtotal = TAX.round(line_o_price - array_sum(o_taxes));
                        o_taxes = TAX.calc_tax(line_o_subtotal, item_tax_rates);
                        var line_o_subtotal_tax = array_sum(o_taxes);

                        // Adjusted price (this is the price including the new tax rate)
                        var adjusted_price = ( line_subtotal + line_subtotal_tax ) / values['quantity'];

                        // Apply discounts
                        var discounted_price = CART.get_discounted_price(values, adjusted_price, true);
                        discounted_line_price = TAX.round(discounted_price * values['quantity']);
                        discounted_taxes = TAX.calc_tax(discounted_line_price, item_tax_rates, true);
                        var line_tax = array_sum(discounted_taxes);
                        var line_total = discounted_line_price - line_tax;

                        /**
                         * Regular tax calculation (customer inside base and the tax class is unmodified
                         */
                    } else {

                        // Work out a new base price without the item tax
                        taxes = TAX.calc_tax(line_price, item_tax_rates, true);
                        o_taxes = TAX.calc_tax(line_o_price, item_tax_rates, true);

                        // Now we have a new item price (excluding TAX)
                        var line_subtotal = line_price - array_sum(taxes);
                        line_subtotal_tax = array_sum(taxes);

                        var line_o_subtotal = line_o_price - array_sum(o_taxes);
                        line_o_subtotal_tax = array_sum(o_taxes);

                        // Calc prices and tax (discounted)
                        var discounted_price = CART.get_discounted_price(values, base_price, true);
                        discounted_line_price = TAX.round(discounted_price * values['quantity']);
                        discounted_taxes = TAX.calc_tax(discounted_line_price, item_tax_rates, true);
                        var line_tax = array_sum(discounted_taxes);
                        var line_total = discounted_line_price - line_tax;
                    }

                    // Tax rows - merge the totals we just got
                    var new_arr_tax = clone(CART.taxes);
                    $.extend(new_arr_tax, discounted_taxes);
                    $.each(new_arr_tax, function (key, amount) {
                        CART.taxes[key] = ( typeof discounted_taxes[key] != 'undefined' ? discounted_taxes[key] : 0 ) + ( typeof CART.taxes[key] != 'undefined' ? CART.taxes[key] : 0 );
                    });

                    /**
                     * Prices exclude tax
                     */
                } else {

                    var item_tax_rates = tax_rates[_product.tax_class];

                    // Work out a new base price without the shop's base tax
                    taxes = TAX.calc_tax(line_price, item_tax_rates);
                    o_taxes = TAX.calc_tax(line_price, item_tax_rates);

                    // Now we have the item price (excluding TAX)
                    var line_subtotal = line_price;
                    line_subtotal_tax = array_sum(taxes);

                    var line_o_subtotal = line_o_price;
                    line_o_subtotal_tax = array_sum(o_taxes);
                    // Now calc product rates
                    var discounted_price = CART.get_discounted_price(values, base_price, true);
                    discounted_taxes = TAX.calc_tax(discounted_price * values['quantity'], item_tax_rates);
                    var discounted_tax_amount = array_sum(discounted_taxes);
                    var line_tax = discounted_tax_amount;
                    var line_total = discounted_price * values['quantity'];

                    // Tax rows - merge the totals we just got
                    var new_arr_tax = clone(CART.taxes);
                    $.extend(new_arr_tax, discounted_taxes);
                    $.each(new_arr_tax, function (key, amount) {
                        CART.taxes[key] = ( typeof discounted_taxes[key] != 'undefined' ? discounted_taxes[key] : 0 ) + ( typeof CART.taxes[key] != 'undefined' ? CART.taxes[key] : 0 );
                    });
                }

                // Cart contents total is based on discounted prices and is used for the final total calculation
                CART.cart_contents_total += line_total;

                // Store costs + taxes for lines
                CART.cart_contents[cart_item_key]['line_total'] = line_total;
                CART.cart_contents[cart_item_key]['line_tax'] = line_tax;
                CART.cart_contents[cart_item_key]['line_subtotal'] = line_subtotal;
                CART.cart_contents[cart_item_key]['line_subtotal_tax'] = line_subtotal_tax;

                var original_subtotal = CART.prices_include_tax ? line_o_subtotal + line_o_subtotal_tax : line_o_subtotal;
                var grand_subtotal = CART.prices_include_tax ? line_total + line_tax : line_total;

                var line_total_row = '<span class="amount">' + accountingPOS(grand_subtotal, 'formatMoney') + '</span>';

                if (TAX.round(line_subtotal) != TAX.round(line_total) || TAX.round(line_price) != TAX.round(line_o_price)) {
                    line_total_row = line_total_row + '<del>' + accountingPOS(original_subtotal, 'formatMoney') + '</del>';
                }

                var line_total_data = {
                    'line_total': line_total,
                    'line_tax': line_tax,
                    'line_subtotal': line_subtotal,
                    'line_subtotal_tax': line_subtotal_tax,
                    'line_price': line_price,
                    'line_o_price': line_o_price,
                };
                line_total_row = wp.hooks.applyFilters('calculate_totals_line_total_row', line_total_row, line_total_data, values);
                $('tr#' + cart_item_key + ' .line_cost_total .view').html(line_total_row);

                // Store rates ID and costs - Since 2.2
                CART.cart_contents[cart_item_key]['line_tax_data'] = {'total': discounted_taxes, 'subtotal': taxes};
            });

            // Calculate the Shipping
            CART.calculate_shipping();

            // Trigger the fees API where developers can add fees to the cart
            CART.calculate_fees();

            // Total up/round taxes and shipping taxes
            if (CART.round_at_subtotal) {
                CART.tax_total = TAX.get_tax_total(CART.taxes);
                CART.shipping_tax_total = TAX.get_tax_total(CART.shipping_taxes);
                CART.taxes = array_map(TAX.round, CART.taxes);
                CART.shipping_taxes = array_map(TAX.round, CART.shipping_taxes);
            } else {
                CART.tax_total = array_sum(CART.taxes);
                CART.shipping_tax_total = array_sum(CART.shipping_taxes);
            }

            // Grand Total - Discounted product prices, discounted tax, shipping cost + tax
            var r = round(CART.cart_contents_total + CART.tax_total + CART.shipping_total + CART.fee_total, CART.dp);
            CART.total = max(0, r);

            if (wc_pos_params.pos_calc_taxes === true) {
                var subtotal_amount_row = accountingPOS(CART.subtotal_ex_tax, 'formatMoney');
            } else {
                var subtotal_amount_row = accountingPOS(CART.subtotal, 'formatMoney');
            }

            $('#subtotal_amount').html(subtotal_amount_row);
            $('#total_amount').html(accountingPOS(CART.total, 'formatMoney'));
            $('#show_total_amt .amount').html(accountingPOS(CART.total, 'formatMoney'));

            CART.calculateAmountTendered();

            //get_coupon_discount_amount
            $('.woocommerce_order_items .tr_order_coupon').remove();
            if (Object.size(CART.coupons) > 0) {
                var source = '';
                var ordered_coupons = {};
                var points = {};
                $.each(CART.coupons, function (coupon_code, the_coupon) {
                    if (coupon_code == 'WC_POINTS_REDEMPTION') {
                        points[coupon_code] = the_coupon;
                    } else {
                        ordered_coupons[coupon_code] = the_coupon;
                    }
                });
                if (Object.size(points) > 0) {
                    $.each(points, function (coupon_code, the_coupon) {
                        ordered_coupons[coupon_code] = the_coupon;
                    });
                }
                CART.coupons = ordered_coupons;

                $.each(CART.coupons, function (coupon_code, the_coupon) {
                    var coupon_label = coupon_code;
                    if (coupon_code == 'POS Discount') {
                        source = $('#tmpl-cart-pos-discount').html();
                    } else if (typeof pos_cart.coupons_labels[coupon_code] != 'undefined') {
                        source = $('#tmpl-cart-coupon-label').html();
                        coupon_label = pos_cart.coupons_labels[coupon_code];
                    } else {
                        source = $('#tmpl-cart-coupon-code').html();
                    }

                    var value = new array();
                    var amount = CART.get_coupon_discount_amount(coupon_code, CART.display_cart_ex_tax);
                    if (amount) {
                        var discount_html = '- ' + accountingPOS(amount, 'formatMoney');
                        value.push(discount_html);
                    }

                    if (the_coupon.free_shipping === true) {
                        value.push(pos_i18n[23]);
                    }
                    value = value.join('<br>');

                    var template = Handlebars.compile(source);
                    var context = {'coupon_code': coupon_code, 'amount': value, 'coupon_label': coupon_label};
                    var html = template(context);
                    $('.woocommerce_order_items .shipping_methods_register').before(html);
                });
            }

            if (CART.needs_shipping()) {
                var source = $('#tmpl-cart-ship-row').html();
                var template = Handlebars.compile(source);
                var ship = {
                    title: CART.chosen_shipping_methods.title,
                    price: accountingPOS(CART.chosen_shipping_methods.price, 'formatMoney')
                }
                var ship_html = template(ship);
                $('.woocommerce_order_items .shipping_methods_register').show().html(ship_html);
            } else {
                $('.woocommerce_order_items .shipping_methods_register').hide().html('');
            }
            if (wc_pos_params.pos_calc_taxes === true) {
                if (pos_cart.tax_total_display == 'itemized') {
                    var tax_data = {};
                    $.each(CART.taxes, function (index, amount) {
                        //pos_wc.all_rates
                        if (typeof pos_wc.all_rates[index] != 'undefined') {
                            tax_data[index] = {
                                'label': pos_wc.all_rates[index]['label'],
                                'amount': accountingPOS(amount, 'formatMoney')
                            };
                        }
                    });
                } else {
                    var tax_data = {
                        1: {
                            'label': pos_cart.tax_or_vat,
                            'amount': accountingPOS(CART.tax_total, 'formatMoney')
                        }
                    };
                }
                var source = $('#tmpl-cart-tax-row').html();
                var template = Handlebars.compile(source);
                var tax_html = template(tax_data);
                $('.woocommerce_order_items .tax_row table').html(tax_html);
            } else {
                $('.woocommerce_order_items .tax_row table').html('');
            }
            if (CART.fee_total > 0) {
                var tax_rates = TAX.get_rates();
                var data = {};
                $.each(CART.fees, function (index, fee) {
                    var amount = fee.amount;
                    if (fee.taxable) {
                        var taxes = TAX.calc_exclusive_tax(fee.amount, tax_rates);
                        $.each(taxes, function (i, t) {
                            amount = floatval(amount) + floatval(t)
                        });
                    }
                    data[index] = {
                        'name': fee.name,
                        'amount': accountingPOS(amount, 'formatMoney')
                    };
                });
                var source = $('#tmpl-cart-fee-row').html();
                var template = Handlebars.compile(source);
                var fee_html = template(data);
                $('.woocommerce_order_items .fee_row table').html(fee_html);
            }
            $.each(CART.coupons, function (code, coupon) {
                var the_coupon = new WC_Coupon(code, coupon.data);
                CART.coupons[code] = the_coupon;
            });
            localStorage.setItem('cart_' + pos_register_data.ID, JSON.stringify(CART));
            wp.hooks.doAction('wc_pos_end_calculate_totals', cart);
            resizeCart();
        },
        /**
         * Sort by subtotal
         * @param  array $a
         * @param  array $b
         * @return int
         */
        sort_by_subtotal: function (a, b) {
            var first_item_subtotal = typeof a['line_subtotal'] != 'undefined' ? a['line_subtotal'] : 0;
            var second_item_subtotal = typeof b['line_subtotal'] != 'undefined' ? b['line_subtotal'] : 0;
            if (first_item_subtotal === second_item_subtotal) {
                return 0;
            }
            return ( first_item_subtotal < second_item_subtotal ) ? 1 : -1;
        },


        /*-----------------------------------------------------------------------------------*/
        /* Shipping related functions */
        /*-----------------------------------------------------------------------------------*/

        /**
         * Uses the shipping class to calculate shipping then gets the totals when its finished.
         */
        calculate_shipping: function () {
            if (CART.needs_shipping()) {
                // Get totals for the chosen shipping method
                var price = round(CART.chosen_shipping_methods.price, pos_wc.precision);
                var s_taxes = {};
                if (wc_pos_params.pos_calc_taxes === true) {
                    var tax_rate = TAX.get_shipping_tax_rates();
                    s_taxes = TAX.calc_shipping_tax(price, tax_rate);

                    // Tax rows - merge the totals we just got
                    var new_arr_tax = clone(CART.taxes);
                    $.extend(new_arr_tax, s_taxes);
                    $.each(new_arr_tax, function (key, amount) {
                        CART.taxes[key] = ( typeof s_taxes[key] != 'undefined' ? s_taxes[key] : 0 ) + ( typeof CART.taxes[key] != 'undefined' ? CART.taxes[key] : 0 );
                    });
                }

                CART.shipping_total = price;
                CART.shipping_taxes = s_taxes;

            } else {
                CART.shipping_total = 0;
                CART.shipping_taxes = {};
            }
        },
        calculate_fees: function () {
            var total = 0;
            var tax_rates = TAX.get_rates();
            $.each(CART.fees, function (k, fee) {
                var amount = fee.amount;
                if (fee.type === 'percent') {
                    amount = CART.subtotal_ex_tax / 100 * fee.value;
                    CART.cart_session_data.fees[fee.name]['amount'] = amount;
                    CART.fees[fee.name]['amount'] = amount;
                }
                if (fee.taxable) {
                    var taxes = TAX.calc_exclusive_tax(fee.amount, tax_rates);
                    $.each(taxes, function (i, t) {
                        amount = floatval(amount) + floatval(t)
                    });
                }
                total = total + floatval(amount);
            });
            CART.fee_total = total;
        },

        /**
         * Get packages to calculate shipping for.
         *
         * This lets us calculate costs for carts that are shipped to multiple locations.
         *
         * Shipping methods are responsible for looping through these packages.
         *
         * By default we pass the cart itself as a package - plugins can change this
         * through the filter and break it up.
         *
         * @since 1.5.4
         * @return array of cart items
         */
        get_shipping_packages: function () {
            // Packages array for storing 'carts'
            var packages = [{
                contents: CART.get_cart(),  // Items in the package
                contents_cost: 0,                // Cost of items in the package, set below
                applied_coupons: CART.applied_coupons,
                user: {
                    id: CUSTOMER.id,
                },
                destination: {
                    country: CUSTOMER.shipping_address.country,
                    state: CUSTOMER.shipping_address.state,
                    postcode: CUSTOMER.shipping_address.postcode,
                    city: CUSTOMER.shipping_address.city,
                    address: CUSTOMER.shipping_address.address,
                    address_2: CUSTOMER.shipping_address.address_2,
                }
            }];

            $.each(CART.get_cart(), function (index, item) {
                if (item['data'].virtual === false) {
                    if (typeof item['line_total'] != 'undefined') {
                        packages[0]['contents_cost'] += item['line_total'];
                    }
                }
            });

            return packages
        },
        /**
         * Looks through the cart to see if shipping is actually required.
         *
         * @return bool whether or not the cart needs shipping
         */
        needs_shipping: function () {
            if (!pos_cart.calc_shipping) {
                return false;
            }
            ;
            if (this.chosen_shipping_methods.price == '') {
                return false;
            }

            var needs_shipping = false;
            var cart_contents = this.cart_contents;
            $.each(cart_contents, function (index, val) {
                var _product = val['data'];

                if (val.variation_id > 0) {
                    _product = val['v_data'];
                }
                if (_product.virtual === false) {
                    needs_shipping = true;
                }
            });

            return needs_shipping;
        },
        /**
         * Should the shipping address form be shown
         *
         * @return bool
         */
        needs_shipping_address: function () {

            needs_shipping_address = false;

            if (CART.needs_shipping() === true) {
                needs_shipping_address = true;
            }

            return needs_shipping_address;
        },


        /*-----------------------------------------------------------------------------------*/
        /* Coupons/Discount related functions */
        /*-----------------------------------------------------------------------------------*/

        /**
         * Returns whether or not a discount has been applied.
         *
         * @return bool
         */
        has_discount: function (coupon_code) {
            return in_array(coupon_code, CART.applied_coupons);
        },

        /**
         * Applies a custom discount.
         *
         * @param string $coupon_code - The code to apply
         * @return bool    True if the coupon is applied, false if it does not exist or cannot be applied
         */
        add_custom_discount: function (amount, type, coupon_code) {
            if (typeof amount == 'undefined' || amount <= 0) return false;
            if (typeof type == 'undefined') {
                type = 'fixed_cart';
            }

            if (typeof coupon_code == 'undefined') {
                coupon_code = 'POS Discount';
            }
            var message = WC_POS_DISCOUNT_SUCCESS;
            if (typeof CART.coupons[coupon_code] != 'undefined') {
                message = WC_POS_DISCOUNT_UPDATED;
            }

            var the_coupon = new WC_Coupon(coupon_code, {'type': type, 'amount': amount});
            // Check it can be used with cart
            if (!the_coupon.is_valid()) {
                APP.showNotice(the_coupon.get_error_message(), 'error');
                return false;
            }
            if (!in_array(coupon_code, CART.applied_coupons)) {
                CART.applied_coupons.push(coupon_code);
            }
            CART.coupons[coupon_code] = the_coupon;

            the_coupon.add_coupon_message(message);
            CART.calculate_totals();
        },

        add_custom_fee: function (fee) {
            CART.cart_session_data.fees[fee.name] = fee;
            CART.calculate_totals();
        },

        remove_fee: function (fee) {
            delete CART.cart_session_data.fees[fee];
            CART.calculate_totals();
            return true;
        },

        /**
         * Applies a coupon code passed to the method.
         *
         * @param string $coupon_code - The code to apply
         * @return bool    True if the coupon is applied, false if it does not exist or cannot be applied
         */
        add_discount: function (coupon_code) {
            // Coupons are globally disabled
            if (!this.coupons_enabled()) {
                return false;
            }

            APP.db.get('coupons', coupon_code).always(function (record) {
                if (typeof record != 'undefined') {
                    // Get the coupon
                    var the_coupon = new WC_Coupon(coupon_code, record);
                    // Check it can be used with cart
                    if (!the_coupon.is_valid()) {
                        APP.showNotice(the_coupon.get_error_message(), 'error');
                        return false;
                    }

                    // Check if applied
                    if (CART.has_discount(coupon_code)) {
                        the_coupon.add_coupon_message(E_WC_COUPON_ALREADY_APPLIED);
                        return false;
                    }

                    // If its individual use then remove other coupons
                    if (the_coupon.individual_use == true) {
                        CART.applied_coupons = new Array();
                    }

                    var individual_use = false;
                    if (CART.applied_coupons) {
                        $.each(CART.coupons, function (code, coupon) {
                            //var coupon = new WC_Coupon( code );

                            if (coupon.individual_use == true) {
                                // Reject new coupon
                                coupon.add_coupon_message(E_WC_COUPON_ALREADY_APPLIED_INDIV_USE_ONLY);
                                individual_use = true;
                                return false;
                            }
                        });
                    }
                    if (individual_use) {
                        return false;
                    }
                    ;

                    CART.applied_coupons.push(coupon_code);
                    CART.coupons[coupon_code] = the_coupon;

                    // Choose free shipping
                    if (the_coupon.enable_free_shipping) {
                        CART.chosen_shipping_methods['free_shipping'] = true;
                    }

                    if (typeof CART.coupons['POS Discount'] != 'undefined') {
                        var pos_discount = clone(CART.coupons['POS Discount']);
                        delete CART.coupons['POS Discount'];
                        CART.coupons['POS Discount'] = pos_discount;
                    }

                    the_coupon.add_coupon_message(WC_COUPON_SUCCESS);
                    CART.calculate_totals();
                } else {
                    var the_coupon = new WC_Coupon(coupon_code, {});
                    the_coupon.add_coupon_message(E_WC_COUPON_NOT_EXIST);
                }
            });
        },

        /**
         * Get array of applied coupon objects and codes.
         * @return array of applied coupons
         */
        get_coupons: function () {
            return CART.coupons
        },


        /**
         * Function to apply discounts to a product and get the discounted price (before tax is applied).
         *
         * @param mixed $values
         * @param mixed $price
         * @param bool $add_totals (default: false)
         * @return float price
         */
        get_discounted_price: function (values, price, add_totals) {
            if (!price) {
                return price;
            }
            if (typeof add_totals == 'undefined') {
                add_totals = false;
            }

            var undiscounted_price = price;
            if (Object.size(CART.coupons) > 0) {
                var product = values['data'];
                if (values.variation_id > 0) {
                    product = values['v_data'];
                }
                $.each(CART.coupons, function (code, coupon) {
                    if (coupon.is_valid() && (coupon.is_valid_for_product(values['data'], values) || coupon.is_valid_for_cart() )) {
                        if (!coupon.is_valid_for_product(values['data']) && code !== 'POS Discount') {
                            return price;
                        }
                        var discount_amount = coupon.get_discount_amount(( 'yes' === pos_wc.calc_discounts_seq ? price : undiscounted_price ), values, true);
                        discount_amount = Math.min(price, discount_amount);
                        price = Math.max(price - discount_amount, 0);
                        // Store the totals for DISPLAY in the cart
                        if (add_totals) {
                            var total_discount = discount_amount * values['quantity'];
                            var total_discount_tax = 0;

                            if (wc_pos_params.pos_calc_taxes == true) {
                                var tax_rates = TAX.get_rates(product.tax_class);
                                var taxes = TAX.calc_tax(discount_amount, tax_rates, CART.prices_include_tax);
                                var total_discount_tax = TAX.get_tax_total(taxes) * values['quantity'];
                                var total_discount = CART.prices_include_tax ? total_discount - total_discount_tax : total_discount;
                                CART.discount_cart_tax += total_discount_tax;
                            }

                            CART.discount_cart += total_discount;
                            CART.increase_coupon_discount_amount(code, total_discount, total_discount_tax);
                            CART.increase_coupon_applied_count(code, values['quantity']);
                        }
                    }
                });
            }
            return price;
        },

        /**
         * Remove a single coupon by code
         * @param  string $coupon_code Code of the coupon to remove
         * @return bool
         */
        remove_coupon: function (coupon_code, refresh_totals) {
            // Coupons are globally disabled
            if (!CART.coupons_enabled()) {
                return false;
            }

            // Get the coupon
            var position = array_search(coupon_code, CART.applied_coupons);

            if (position !== false && typeof CART.coupons[coupon_code] != 'undefined') {
                delete CART.applied_coupons[position];
                delete CART.coupons[coupon_code];
            }

            if (refresh_totals) {
                CART.calculate_totals();
            }

            return true;
        },

        /**
         * Store how much discount each coupon grants.
         *
         * @access private
         * @param string $code
         * @param double $amount
         * @param double $tax
         */
        increase_coupon_discount_amount: function (code, amount, tax) {
            CART.coupon_discount_amounts[code] = typeof CART.coupon_discount_amounts[code] != 'undefined' ? CART.coupon_discount_amounts[code] + amount : amount;
            CART.coupon_discount_tax_amounts[code] = typeof CART.coupon_discount_tax_amounts[code] != 'undefined' ? CART.coupon_discount_tax_amounts[code] + tax : tax;

            if (code == 'POS Discount') {
                CART._coupon_discount_amounts[code] = typeof CART._coupon_discount_amounts[code] != 'undefined' ? CART._coupon_discount_amounts[code] + amount : amount;
                CART._coupon_discount_tax_amounts[code] = typeof CART._coupon_discount_tax_amounts[code] != 'undefined' ? CART._coupon_discount_tax_amounts[code] + tax : tax;
            }
        },

        /**
         * Store how many times each coupon is applied to cart/items
         *
         * @access private
         * @param string $code
         * @param integer $count
         */
        increase_coupon_applied_count: function (code, count) {
            if (typeof count == 'undefined') {
                count = 1;
            }
            if (typeof CART.coupon_applied_count[code] == 'undefined') {
                CART.coupon_applied_count[code] = 0
            }
            CART.coupon_applied_count[code] += count;
        },

        /**
         * Gets the array of applied coupon codes.
         *
         * @return array of applied coupons
         */
        get_applied_coupons: function () {
            return CART.applied_coupons;
        },

        /**
         * Get the discount amount for a used coupon
         * @param  string $code coupon code
         * @param  bool inc or ex tax
         * @return float discount amount
         */
        get_coupon_discount_amount: function (code, ex_tax) {
            if (typeof ex_tax == 'undefined') {
                ex_tax = true;
            }
            discount_amount = isset(CART.coupon_discount_amounts[code]) ? CART.coupon_discount_amounts[code] : 0;

            if (!ex_tax) {
                discount_amount += CART.get_coupon_discount_tax_amount(code);
            }
            return round(discount_amount, CART.dp);
        },

        /**
         * Get the discount amount for a custom discount
         * @param  string $code coupon code
         * @param  bool inc or ex tax
         * @return float discount amount
         */
        get_discount_amount: function (code, ex_tax) {
            if (typeof ex_tax == 'undefined') {
                ex_tax = true;
            }
            discount_amount = isset(CART._coupon_discount_amounts[code]) ? CART._coupon_discount_amounts[code] : 0;

            if (!ex_tax) {
                discount_amount += CART.get_discount_tax_amount(code);
            }

            return round(discount_amount, CART.dp);
        },

        /**
         * Get the discount tax amount for a used coupon (for tax inclusive prices)
         * @param  string $code coupon code
         * @param  bool inc or ex tax
         * @return float discount amount
         */
        get_coupon_discount_tax_amount: function (code) {
            return round(typeof CART.coupon_discount_tax_amounts[code] != 'undefined' ? CART.coupon_discount_tax_amounts[code] : 0, CART.dp);
        },

        /**
         * Get the discount tax amount for a custom discount (for tax inclusive prices)
         * @param  string $code coupon code
         * @param  bool inc or ex tax
         * @return float discount amount
         */
        get_discount_tax_amount: function (code) {
            return round(typeof CART._coupon_discount_tax_amounts[code] != 'undefined' ? CART._coupon_discount_tax_amounts[code] : 0, CART.dp);
        },

        calculateAmountTendered: function () {
            var amount = clone(CART.total);
            var t1 = round(amount, 2);
            var t2 = '';
            var t3 = '';
            var t4 = '';

            if (amount % 1 === 0) {
                t2 = Math.ceil(amount / 5) * 5;
                t3 = ( t2 + 5 ) + '.00';
                t4 = ( t2 + 10 ) + '.00';
                t1 = t1 + '.00';
                t2 = t2 + '.00';
            } else {
                var v_expl = explode('.', t1);
                var c = parseInt(v_expl[0]);
                var d = parseInt(v_expl[1]);

                if (v_expl[1].length == 1) {
                    t1 = t1 + '0';
                    d = d * 10;
                }

                if (d < 50) {
                    d = Math.ceil(d / 50) * 50;
                    t2 = c + '.' + d;
                }
                else {
                    c = c + 1;
                    t2 = c + '.00';
                }
                var _c = Math.ceil(c / 5) * 5;
                if (_c == c) {
                    c = _c + 5;
                } else {
                    c = _c;
                }
                t3 = c + '.00';
                t4 = ( c + 5 ) + '.00';
            }

            tendered_num = {
                'tendered_1': t1,
                'tendered_2': t2,
                'tendered_3': t3,
                'tendered_4': t4,
            };

            $.each(tendered_num, function (key, val) {
                if (val == '') {
                    jQuery('.amount_pay_keypad .keypad-' + key).text('').hide();
                } else {
                    jQuery('.amount_pay_keypad .keypad-' + key).text(val).show();
                }
            });

        }

    }
});

