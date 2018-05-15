jQuery(document).ready(function ($) {
    window.POS_TAX = {

        init: function () {

        },
        get_base_tax_rates: function (tax_class) {
            if (typeof tax_class == 'undefined') {
                tax_class = '';
            }
            if ('outlet' === pos_wc.pos_tax_based_on) {
                var args = {
                    country: pos_wc.outlet_location.contact.country,
                    state: pos_wc.outlet_location.contact.state,
                    city: pos_wc.outlet_location.contact.city,
                    postcode: pos_wc.outlet_location.contact.postcode
                };
            } else {
                var args = clone(pos_wc.shop_location);
            }
            args['tax_class'] = tax_class;
            var rates = this.find_rates(args);
            return rates;
        },
        find_rates: function (args) {
            if (typeof args == 'undefined') {
                args = {};
            }
            var args = $.extend({
                'country': '',
                'state': '',
                'postcode': '',
                'city': '',
                'tax_class': ''
            }, args);

            if (args.country == '') {
                return {};
            }

            var valid_postcodes = this._get_wildcard_postcodes(args.postcode);
            var rates_transient_key = 'wc_tax_rates_' + hex_md5(sprintf('%s+%s+%s+%s+%s', args.country, args.state, args.city, valid_postcodes.join(','), args.tax_class));

            var matched_tax_rates = null;
            if (typeof POS_TRANSIENT[rates_transient_key] != 'undefined') {
                matched_tax_rates = POS_TRANSIENT[rates_transient_key];
            } else {
                matched_tax_rates = this.get_matched_tax_rates(args.country, args.state, args.postcode, args.city, args.tax_class, valid_postcodes);
                POS_TRANSIENT[rates_transient_key] = matched_tax_rates;
            }

            return matched_tax_rates;
        },
        /**
         * Get's an array of matching rates for a tax class.
         * @param string $tax_class
         * @return  array
         */
        get_rates: function (tax_class) {
            if (typeof tax_class == 'undefined') {
                tax_class = '';
            }

            var location = this.get_tax_location();
            var matched_tax_rates = {};
            if (Object.size(location) === 4) {

                matched_tax_rates = this.find_rates({
                    'country': location.country,
                    'state': location.state,
                    'postcode': location.postcode,
                    'city': location.city,
                    'tax_class': tax_class
                });
            }

            return matched_tax_rates;
        },
        /**
         * Searches for all matching country/state/postcode tax rates.
         *
         * @param array $args
         * @return array
         */
        find_shipping_rates: function (args) {
            if (typeof args == 'undefined') {
                args = {};
            }
            var rates = this.find_rates(args);
            var shipping_rates = {};

            if (typeof rates == 'object') {
                $.each(rates, function (key, rate) {
                    if ('yes' === rate['shipping']) {
                        shipping_rates[key] = rate;
                    }
                });
            }

            return shipping_rates;
        },
        /**
         * Gets an array of matching shipping tax rates for a given class.
         *
         * @param   string    Tax Class
         * @return  mixed
         */
        get_shipping_tax_rates: function (tax_class) {
            // See if we have an explicitly set shipping tax class
            if (pos_cart.shipping_tax_class && pos_cart.shipping_tax_class != '') {
                tax_class = 'standard' === pos_cart.shipping_tax_class ? '' : pos_cart.shipping_tax_class;
            }

            var location = this.get_tax_location();
            var matched_tax_rates = {};

            if (Object.size(location) === 4) {

                if (typeof tax_class != 'undefined') {
                    // This will be per item shipping
                    matched_tax_rates = this.find_shipping_rates({
                        'country': location.country,
                        'state': location.state,
                        'postcode': location.postcode,
                        'city': location.city,
                        'tax_class': tax_class
                    });

                } else {

                    // This will be per order shipping - loop through the order and find the highest tax class rate
                    var cart_tax_classes = CART.get_cart_item_tax_classes();

                    // If multiple classes are found, use highest. Don't bother with standard rate, we can get that later.
                    if (sizeof(cart_tax_classes) > 1 && !in_array('', cart_tax_classes)) {
                        var tax_classes = clone(pos_cart.tax_classes);

                        $.each(tax_classes, function (index, tax_class) {
                            if (in_array(tax_class, cart_tax_classes)) {
                                matched_tax_rates = this.find_shipping_rates({
                                    'country': location.country,
                                    'state': location.state,
                                    'postcode': location.postcode,
                                    'city': location.city,
                                    'tax_class': tax_class
                                });
                                return false;
                            }
                        });

                        // If a single tax class is found, use it
                    } else if (sizeof(cart_tax_classes) == 1) {
                        matched_tax_rates = this.find_shipping_rates({
                            'country': location.country,
                            'state': location.state,
                            'postcode': location.postcode,
                            'city': location.city,
                            'tax_class': cart_tax_classes[0]
                        });
                    }
                }

                // Get standard rate if no taxes were found
                if (!sizeof(matched_tax_rates)) {
                    matched_tax_rates = this.find_shipping_rates({
                        'country': location.country,
                        'state': location.state,
                        'postcode': location.postcode,
                        'city': location.city,
                    });
                }
            }

            return matched_tax_rates;
        },

        /**
         * Loop through a set of tax rates and get the matching rates (1 per priority)
         *
         * @param  string $country
         * @param  string $state
         * @param  string $postcode
         * @param  string $city
         * @param  string $tax_class
         * @param  string[] $valid_postcodes
         * @return array
         */
        get_matched_tax_rates: function (country, state, postcode, city, tax_class, valid_postcodes) {
            var match_country = country.toUpperCase();
            var match_state = state.toUpperCase();
            var match_tax_class = tax_class;
            var match_city = city.toUpperCase();
            var match_postcode = postcode.toUpperCase();
            var found_rates = pos_wc.all_rates;

            var matched_tax_rates = {};
            var found_priority = {};
            $.each(found_rates, function (index, rate) {
                if (rate.taxclass == match_tax_class) {

                    var cities = rate.city.split(";");
                    var postcodes = rate.postcode.split(";");

                    if ((rate.country == match_country || rate.country == '')
                        && (rate.state == match_state || rate.state == '')
                        && (rate.city == '' || in_array(match_city, cities) )
                        && (rate.postcode == '' || in_array(match_postcode, postcodes) )) {

                        if (typeof found_priority[rate.priority] == 'undefined') {
                            matched_tax_rates[index] = rate;
                            found_priority[rate.priority] = '1';
                        }
                    }

                }
            });
            return matched_tax_rates;
        },
        /**
         * Round tax lines and return the sum.
         *
         * @param   array
         * @return  float
         */
        get_tax_total: function (taxes) {
            return array_sum(array_map(TAX.round, taxes));
        },

        /**
         * Get the customer tax location based on their status and the current page
         *
         * Used by get_rates(), get_shipping_rates()
         *
         * @param  $tax_class string Optional, passed to the filter for advanced tax setups.
         * @return array
         */
        get_tax_location: function () {
            var location = {};
            if (pos_wc.pos_tax_based_on == 'shipping' || pos_wc.pos_tax_based_on == 'billing') {
                location = CUSTOMER.get_taxable_address();
                return location;
            } else if ('base' === pos_wc.pos_tax_based_on) {
                location = pos_wc.shop_location;
            } else if ('outlet' === pos_wc.pos_tax_based_on) {
                location = {
                    country: pos_wc.outlet_location.contact.country,
                    state: pos_wc.outlet_location.contact.state,
                    city: pos_wc.outlet_location.contact.city,
                    postcode: pos_wc.outlet_location.contact.postcode
                };
            } else if (pos_cart.prices_include_tax) {
                location = pos_wc.shop_location;
            }
            return location;
        },

        /**
         * Calculate tax for a line
         * @param  float  $price              Price to calc tax on
         * @param  array  $rates              Rates to apply
         * @param  boolean $price_includes_tax Whether the passed price has taxes included
         * @param  boolean $suppress_rounding  Whether to suppress any rounding from taking place
         * @return array                      Array of rates + prices after tax
         */
        calc_tax: function (price, rates, price_includes_tax, suppress_rounding) {
            if (typeof price_includes_tax == 'undefined') {
                price_includes_tax = false;
            }
            if (typeof suppress_rounding == 'undefined') {
                suppress_rounding = false;
            }
            // Work in pence to X precision
            price = this.precision(price);
            var taxes;

            if (price_includes_tax) {
                taxes = this.calc_inclusive_tax(price, rates);
            } else {
                taxes = this.calc_exclusive_tax(price, rates);
            }

            // Round to precision
            if (pos_wc.tax_round_at_subtotal === false && suppress_rounding === false) {
                taxes = array_map('round', taxes); // Round to precision
            }

            // Remove precision
            price = this.remove_precision(price);
            taxes = array_map(TAX.remove_precision, taxes);

            return taxes;
        },
        calc_inclusive_tax: function (price, rates) {
            var taxes = {};

            var regular_tax_rates = 0;
            var compound_tax_rates = 0;
            $.each(rates, function (key, rate) {
                if (rate.compound == 'yes') {
                    compound_tax_rates = compound_tax_rates + parseFloat(rate.rate);
                }
                else {
                    regular_tax_rates = regular_tax_rates + parseFloat(rate.rate);
                }
            });

            var regular_tax_rate = 1 + ( regular_tax_rates / 100 );
            var compound_tax_rate = 1 + ( compound_tax_rates / 100 );
            var non_compound_price = price / compound_tax_rate;

            $.each(rates, function (key, rate) {
                if (typeof taxes[key] == 'undefined') {
                    taxes[key] = 0;
                }

                var the_rate = parseFloat(rate.rate) / 100;

                if (rate['compound'] == 'yes') {
                    var the_price = price;
                    the_rate = the_rate / compound_tax_rate;
                } else {
                    var the_price = non_compound_price;
                    the_rate = the_rate / regular_tax_rate;
                }

                var net_price = price - ( the_rate * the_price );
                var tax_amount = price - net_price;
                taxes[key] += tax_amount;
            });

            return taxes;
        },
        calc_exclusive_tax: function (price, rates) {
            var taxes = {};

            if (typeof rates == 'object') {
                // Multiple taxes
                $.each(rates, function (key, rate) {

                    if (rate.compound != 'yes') {
                        var tax_amount = price * ( parseFloat(rate.rate) / 100 );

                        // Add rate
                        if (typeof taxes[key] == 'undefined')
                            taxes[key] = tax_amount;
                        else
                            taxes[key] += tax_amount;
                    }

                });

                var pre_compound_total = array_sum(taxes);

                // Compound taxes
                $.each(rates, function (key, rate) {

                    if (rate['compound'] != 'no') {

                        var the_price_inc_tax = price + ( pre_compound_total );

                        var tax_amount = the_price_inc_tax * ( parseFloat(rate.rate) / 100 );

                        // Add rate
                        if (typeof taxes[key] == 'undefined')
                            taxes[key] = tax_amount;
                        else
                            taxes[key] += tax_amount;
                    }
                });
            }

            return taxes;
        },

        /**
         * Calculate the shipping tax using a passed array of rates.
         *
         * @param   float        Price
         * @param    array        Taxation Rate
         * @return  array
         */
        calc_shipping_tax: function (price, rates) {
            return this.calc_exclusive_tax(price, rates);
        },
        /**
         * Multiply cost by pow precision
         * @param  float $price
         * @return float
         */
        precision: function (price) {
            return price * ( Math.pow(10, pos_wc.precision) );
        },
        /**
         * Divide cost by pow precision
         * @param  float $price
         * @return float
         */
        remove_precision: function (price) {
            return price / ( Math.pow(10, pos_wc.precision) );
        },
        /**
         * Round to precision.
         * @return float
         */
        round: function (_in) {
            return round(_in, pos_wc.precision);
        },

        /**
         * Get postcode wildcards in array format
         *
         * Internal use only.
         *
         * @since 2.3.0
         * @access private
         *
         * @param  string  $postcode array of values
         * @return string[] Array of postcodes with wildcards
         */
        _get_wildcard_postcodes: function (postcode) {
            postcodes = ['*', postcode.toUpperCase(), postcode.toUpperCase() + '*'];
            var postcode_length = postcode.length;
            var wildcard_postcode = postcode.toUpperCase();
            for (var i = 0; i < postcode_length; i++) {
                wildcard_postcode = wildcard_postcode.substr(0, wildcard_postcode.length - 1);
                postcodes.push(wildcard_postcode + '*');
            }
            ;
            return postcodes;
        }


    }
});

