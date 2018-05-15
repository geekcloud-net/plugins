var WC_Coupon = null;
// Coupon message codes
var E_WC_COUPON_INVALID_FILTERED = 100;
var E_WC_COUPON_INVALID_REMOVED = 101;
var E_WC_COUPON_NOT_YOURS_REMOVED = 102;
var E_WC_COUPON_ALREADY_APPLIED = 103;
var E_WC_COUPON_ALREADY_APPLIED_INDIV_USE_ONLY = 104;
var E_WC_COUPON_NOT_EXIST = 105;
var E_WC_COUPON_USAGE_LIMIT_REACHED = 106;
var E_WC_COUPON_EXPIRED = 107;
var E_WC_COUPON_MIN_SPEND_LIMIT_NOT_MET = 108;
var E_WC_COUPON_NOT_APPLICABLE = 109;
var E_WC_COUPON_NOT_VALID_SALE_ITEMS = 110;
var E_WC_COUPON_PLEASE_ENTER = 111;
var E_WC_COUPON_MAX_SPEND_LIMIT_MET = 112;
var E_WC_COUPON_EXCLUDED_PRODUCTS = 113;
var E_WC_COUPON_EXCLUDED_CATEGORIES = 114;
var WC_COUPON_SUCCESS = 200;
var WC_COUPON_REMOVED = 201;
var WC_POS_DISCOUNT_SUCCESS = 202;
var WC_POS_DISCOUNT_UPDATED = 203;

(function ($) {

    WC_Coupon = function (code, data) {


        /** @public string Coupon code. */


        /** @public int Coupon ID. */


        /** @public bool Coupon exists */


        /**
         * Coupon constructor. Loads coupon data.
         *
         * @access public
         * @param mixed $code code of the coupon to load
         */


        /**
         * Checks the coupon type.
         *
         * @param string $type Array or string of types
         * @return bool
         */
        this.is_type = function (type) {
            if (typeof type === "undefined") {
                type = '';
            }

            return ( instance.type == type || ( is_array(type) && in_array(instance.type, type) ) ) ? true : false;
        };


        /**
         * Gets an coupon from the database.
         *
         * @param string $code
         * @return bool
         */
        this.get_coupon = function (code, data) {
            if (typeof code === "undefined") {
                code = '';
            }
            this.code = code;

            // Coupon data lets developers cre coupons through code
            var coupon = wp.hooks.applyFilters('wc_pos_get_shop_coupon_data', false, this.code);
            if (coupon) {
                populate(coupon);
                return true;
            }
            if (data && sizeof(data) > 0) {
                this.id = data.id;
                populate(data);
                return true;
            }
            return false;
        }

        /**
         * Populates an order from the loaded post data.
         */
        populate = function (data) {

            if (typeof data === "undefined") {
                data = {};
            }

            var defaults = {
                'type': 'fixed_cart',
                'amount': 0,
                'individual_use': 'no',
                'product_ids': [],
                'exclude_product_ids': [],
                'usage_limit': '',
                'usage_limit_per_user': '',
                'limit_usage_to_x_items': 0,
                'usage_count': '',
                'expiry_date': '',
                'free_shipping': false,
                'product_category_ids': [],
                'exclude_product_category_ids': [],
                'exclude_sale_items': false,
                'minimum_amount': '',
                'maximum_amount': '',
                'customer_emails': []
            };
            $.extend(defaults, data);
            $.each(defaults, function (key, value) {
                instance[key] = value;
            });
        }


        /**
         * Check if coupon needs applying before tax.
         *
         * @return bool
         */
        this.apply_before_tax = function () {
            return true;
        };


        /**
         * Check if a coupon excludes sale items.
         *
         * @return bool
         */
        this.exclude_sale_items = function () {
            return true === this.exclude_sale_items;
        };


        /**
         * Returns the error_message string
         *
         * @access public
         * @return string
         */
        this.get_error_message = function () {
            return this.error_message;
        };


        /**
         * Ensure coupon exists or throw exception
         */
        function validate_exists() {
            if (!instance.exists) {
                throw new Error(E_WC_COUPON_NOT_EXIST);
            }
        }

        /**
         * Ensure coupon usage limit is valid or throw exception
         */
        function validate_usage_limit() {
            if (instance.usage_limit > 0 && instance.usage_count >= instance.usage_limit) {
                throw new Error(E_WC_COUPON_USAGE_LIMIT_REACHED);
            }
        }

        /**
         * Ensure coupon user usage limit is valid or throw exception
         *
         * Per user usage limit - check here if user is logged in (against user IDs)
         * Checked again for emails later on in WC_Cart::check_customer_coupons()
         */
        function validate_user_usage_limit() {
            if (instance.usage_limit_per_user > 0 && CUSTOMER.id > 0 && instance.id > 0) {
                var used_by = instance.used_by;
                if (used_by != null) {
                    var usage_count = sizeof(array_keys(used_by, CUSTOMER.id));

                    if (usage_count >= instance.usage_limit_per_user) {
                        throw new Error(E_WC_COUPON_USAGE_LIMIT_REACHED);
                    }
                }
            }
        }

        /**
         * Ensure coupon date is valid or throw exception
         */
        function validate_expiry_date() {
            if (instance.expiry_date) {
                var x = new Date().setHours(0, 0, 0, 0);
                var timestamp = Math.floor(x / 1000);

                var s = new Date(instance.expiry_date);
                var expiry = Math.floor(s / 1000);

                if (timestamp > expiry) {
                    throw new Error(E_WC_COUPON_EXPIRED);
                }
            }
        }

        /**
         * Ensure coupon amount is valid or throw exception
         */
        function validate_minimum_amount() {
            if (instance.minimum_amount > 0 && instance.minimum_amount > CART.subtotal) {
                throw new Error(E_WC_COUPON_MIN_SPEND_LIMIT_NOT_MET);
            }
        }

        /**
         * Ensure coupon amount is valid or throw exception
         */
        function validate_maximum_amount() {
            if (instance.maximum_amount > 0 && instance.maximum_amount < CART.subtotal) {
                throw new Error(E_WC_COUPON_MAX_SPEND_LIMIT_MET);
            }
        }

        /**
         * Ensure coupon is valid for products in the cart is valid or throw exception
         */
        function validate_product_ids() {
            if (sizeof(instance.product_ids) > 0) {
                var valid_for_cart = false;
                if (!CART.is_empty()) {
                    var cart = CART.get_cart();
                    $.each(cart, function (cart_item_key, cart_item) {
                        if (in_array(cart_item['product_id'], instance.product_ids) || in_array(cart_item['variation_id'], instance.product_ids)) {
                            valid_for_cart = true;
                        }
                    });
                }
                if (!valid_for_cart) {
                    throw new Error(E_WC_COUPON_NOT_APPLICABLE);
                }
            }
        }

        /**
         * Ensure coupon is valid for product categories in the cart is valid or throw exception
         */
        function validate_product_categories() {
            if (sizeof(instance.product_category_ids) > 0) {
                var valid_for_cart = false;
                if (!CART.is_empty()) {
                    var cart = CART.get_cart();
                    $.each(cart, function (cart_item_key, cart_item) {
                        var product_cats = cart_item.data.categories_ids;
                        if (product_cats && sizeof(array_intersect(product_cats, instance.product_category_ids)) > 0) {
                            valid_for_cart = true;
                        }
                    });

                }
                if (!valid_for_cart) {
                    throw new Error(E_WC_COUPON_NOT_APPLICABLE);
                }
            }
        }

        /**
         * Ensure coupon is valid for sale items in the cart is valid or throw exception
         */
        function validate_sale_items() {
            if (true === instance.exclude_sale_items && instance.is_type(['fixed_product', 'percent_product'])) {
                var valid_for_cart = false;

                if (!CART.is_empty()) {
                    var cart = CART.get_cart();
                    $.each(cart, function (cart_item_key, cart_item) {
                        var product = cart_item['data'];
                        if (( cart_item.variation_id )) {
                            product = cart_item['v_data'];
                        }
                        if (product.on_sale === true) {
                            valid_for_cart = true;
                        }

                    });
                }
                if (!valid_for_cart) {
                    throw new Error(E_WC_COUPON_NOT_VALID_SALE_ITEMS);
                }
            }
        }

        /**
         * Cart discounts cannot be added if non-eligble product is found in cart
         */
        function validate_cart_excluded_items() {
            if (!instance.is_type(['fixed_product', 'percent_product'])) {
                validate_cart_excluded_product_ids();
                validate_cart_excluded_product_categories();
                validate_cart_excluded_sale_items();
            }
        }

        /**
         * Exclude products from cart
         */
        function validate_cart_excluded_product_ids() {
            // Exclude Products
            if (sizeof(instance.exclude_product_ids) > 0) {
                var valid_for_cart = true;
                if (!CART.is_empty()) {
                    var cart = CART.get_cart();
                    $.each(cart, function (cart_item_key, cart_item) {
                        if (in_array(cart_item['product_id'], instance.exclude_product_ids) || in_array(cart_item['variation_id'], instance.exclude_product_ids)) {
                            //valid_for_cart = false;
                            valid_for_cart = true;
                        }
                    });
                }
                if (!valid_for_cart) {
                    throw new Error(E_WC_COUPON_EXCLUDED_PRODUCTS);
                }
            }
        }

        /**
         * Exclude categories from cart
         */
        function validate_cart_excluded_product_categories() {
            if (sizeof(instance.exclude_product_category_ids) > 0) {
                var valid_for_cart = false;
                if (!CART.is_empty()) {
                    var cart = CART.get_cart();
                    $.each(cart, function (cart_item_key, cart_item) {

                        var product_cats = cart_item.data.categories_ids;

                        if (product_cats && sizeof(array_intersect(product_cats, instance.exclude_product_category_ids)) === 0) {
                            valid_for_cart = true;
                        }
                    });
                }
                if (!valid_for_cart) {
                    throw new Error(E_WC_COUPON_EXCLUDED_CATEGORIES);
                }
            }
        }

        /**
         * Exclude sale items from cart
         */
        function validate_cart_excluded_sale_items() {
            if (instance.exclude_sale_items == true) {
                var valid_for_cart = true;
                if (!CART.is_empty()) {
                    var cart = CART.get_cart();
                    $.each(cart, function (cart_item_key, cart_item) {
                        var product = cart_item['data'];
                        if (( cart_item.variation_id )) {
                            product = cart_item['v_data'];
                        }
                        if (product.on_sale === true) {
                            valid_for_cart = false;
                        }
                    });
                }
                if (!valid_for_cart) {
                    throw new Error(E_WC_COUPON_NOT_VALID_SALE_ITEMS);
                }
            }
        }

        /**
         * Check if a coupon is valid.
         *
         * @return boolean validity
         */
        this.is_valid = function () {
            try {
                validate_exists();
                validate_usage_limit();
                validate_user_usage_limit();
                validate_expiry_date();
                validate_minimum_amount();
                validate_maximum_amount();
                validate_product_ids();
                validate_product_categories();
                validate_sale_items();
                validate_cart_excluded_items();

            } catch (e) {
                console.log(e);
                instance.error_message = instance.get_coupon_error(e.message);
                return false;
            }

            return true;
        };


        /**
         * Check if a coupon is valid
         *
         * @return bool
         */
        this.is_valid_for_cart = function () {
            return instance.is_type(['fixed_cart', 'percent','smart_coupon']);
        };


        /**
         * Check if a coupon is valid for a product
         *
         * @param  WC_Product  $product
         * @return boolean
         */
        this.is_valid_for_product = function (product, values) {
            if (typeof values === "undefined") {
                values = new Array();
            }

            if (!instance.is_type(['fixed_product', 'percent', 'fixed_cart','smart_coupon'])) {
                return false;
            }

            var valid = false;
            var product_cats = product.categories_ids;


            // Specific products get the discount
            if (sizeof(instance.product_ids) > 0) {
                if (in_array(product.id, instance.product_ids) || ( ( product.variation_id ) && in_array(product.variation_id, instance.product_ids) )) {
                    valid = true;
                }
            }

            // Category discounts
            if (sizeof(instance.product_category_ids) > 0) {

                if (product_cats && sizeof(array_intersect(product_cats, instance.product_category_ids)) > 0) {
                    valid = true;
                }
            }

            if (!sizeof(instance.product_ids) && !sizeof(instance.product_category_ids)) {
                // No product ids - all items discounted
                valid = true;
            }

            // Specific product ID's excluded from the discount
            if (sizeof(instance.exclude_product_ids) > 0) {
                if (in_array(product.id, instance.exclude_product_ids) || ( ( product.variation_id ) && in_array(product.variation_id, instance.exclude_product_ids) )) {
                    valid = false;
                }
            }

            // Specific categories excluded from the discount
            if (sizeof(instance.exclude_product_category_ids) > 0) {
                if (product_cats && sizeof(array_intersect(product_cats, instance.exclude_product_category_ids)) > 0) {
                    valid = false;
                }
            }

            // Sale Items excluded from discount
            if (instance.exclude_sale_items === true) {
                if (product.on_sale === true) {
                    valid = false;
                }
            }
            return valid;
        };


        /**
         * Get discount amount for a cart item
         *
         * @param  float $discounting_amount Amount the coupon is being applied to
         * @param  array|null $cart_item Cart item being discounted if applicable
         * @param  boolean $single True if discounting a single qty item, false if its the line
         * @return float Amount this coupon has discounted
         */
        this.get_discount_amount = function (discounting_amount, cart_item, single) {
            if (typeof cart_item === "undefined") {
                cart_item = null;
            }

            if (typeof single === "undefined") {
                single = false;
            }
            var discount = 0;
            var cart_item_qty = cart_item == null ? 1 : cart_item['quantity'];

            if (this.is_type(['percent_product', 'percent'])) {

                discount = parseFloat(instance.amount) * ( discounting_amount / 100 );

            } else if (instance.is_type(['fixed_cart','smart_coupon']) && cart_item != null && CART.subtotal_ex_tax) {
                var product = cart_item['data'];
                if (cart_item.variation_id > 0) {
                    product = cart_item['v_data'];
                }
                /**
                 * This is the most complex discount - we need to divide the discount between rows based on their price in
                 * proportion to the subtotal. This is so rows with different tax rates get a fair discount, and so rows
                 * with no price (free) don't get discounted.
                 *
                 * Get item discount by dividing item cost by subtotal to get a %
                 */
                if (pos_cart.prices_include_tax) {
                    var discount_percent = ( get_price_including_tax(product) * cart_item_qty ) / CART.subtotal;
                } else {
                    discount_percent = ( get_price_excluding_tax(product) * cart_item_qty ) / CART.subtotal_ex_tax;
                }
                discount = ( instance.amount * discount_percent ) / cart_item_qty;

            } else if (instance.is_type('fixed_product')) {
                discount = Math.min(instance.amount, discounting_amount);
                discount = single ? discount : discount * cart_item_qty;
            }
            discount = Math.min(discount, discounting_amount);
            // Handle the limit_usage_to_x_items option

            if (instance.is_type(['percent_product', 'fixed_product'])) {
                if (discounting_amount) {
                    if ('' === instance.limit_usage_to_x_items || null === instance.limit_usage_to_x_items) {//|| 0 === instance.limit_usage_to_x_items
                        var limit_usage_qty = cart_item_qty;
                    } else {
                        var limit_usage_qty = Math.min(instance.limit_usage_to_x_items, cart_item_qty);
                        instance.limit_usage_to_x_items = Math.max(0, instance.limit_usage_to_x_items - limit_usage_qty);
                    }
                    if (single) {
                        discount = ( discount * limit_usage_qty ) / cart_item_qty;
                    } else {
                        discount = ( discount / cart_item_qty ) * limit_usage_qty;
                    }
                }
            }
            discount = round(discount, pos_wc.precision);
            return discount;
        };


        /**
         * Converts one of the WC_Coupon message/error codes to a message string and
         * displays the message/error.
         *
         * @param int $msg_code Message/error code.
         */
        this.add_coupon_message = function (msg_code) {
            if (typeof msg_code === "undefined") {
                msg_code = 0;
            }


            var msg = msg_code < 200 ? this.get_coupon_error(msg_code) : this.get_coupon_message(msg_code);

            if (!msg) {
                return;
            }

            if (msg_code < 200) {
                APP.showNotice(msg, 'error');
            } else {
                APP.showNotice(msg);
            }
        };


        /**
         * Map one of the WC_Coupon message codes to a message string
         *
         * @param integer $msg_code
         * @return string| Message/error string
         */
        this.get_coupon_message = function (msg_code) {
            if (typeof msg_code === "undefined") {
                msg_code = '';
            }
            var msg = '';

            switch (msg_code) {
                case WC_COUPON_SUCCESS :
                case WC_COUPON_REMOVED :
                case WC_POS_DISCOUNT_SUCCESS :
                case WC_POS_DISCOUNT_UPDATED :
                    msg = coupon_i18n[msg_code];
                    break;
                default:
                    msg = '';
                    break;
            }
            return msg;
        };


        /**
         * Map one of the WC_Coupon error codes to a message string
         *
         * @param int $err_code Message/error code.
         * @return string| Message/error string
         */
        this.get_coupon_error = function (err_code) {
            if (typeof err_code === "undefined") {
                err_code = '';
            }
            err_code = parseInt(err_code);

            switch (err_code) {
                case E_WC_COUPON_INVALID_FILTERED:
                    var err = coupon_i18n[E_WC_COUPON_INVALID_FILTERED];
                    break;
                case E_WC_COUPON_NOT_EXIST:
                    err = sprintf(coupon_i18n[E_WC_COUPON_NOT_EXIST], this.code);
                    break;
                case E_WC_COUPON_INVALID_REMOVED:
                    err = sprintf(coupon_i18n[E_WC_COUPON_INVALID_REMOVED], this.code);
                    break;
                case E_WC_COUPON_NOT_YOURS_REMOVED:
                    err = sprintf(coupon_i18n[E_WC_COUPON_NOT_YOURS_REMOVED], this.code);
                    break;
                case E_WC_COUPON_ALREADY_APPLIED:
                    err = coupon_i18n[E_WC_COUPON_ALREADY_APPLIED];
                    break;
                case E_WC_COUPON_ALREADY_APPLIED_INDIV_USE_ONLY:
                    err = sprintf(coupon_i18n[E_WC_COUPON_ALREADY_APPLIED_INDIV_USE_ONLY], this.code);
                    break;
                case E_WC_COUPON_USAGE_LIMIT_REACHED:
                    err = coupon_i18n[E_WC_COUPON_USAGE_LIMIT_REACHED];
                    break;
                case E_WC_COUPON_EXPIRED:
                    err = coupon_i18n[E_WC_COUPON_EXPIRED];
                    break;
                case E_WC_COUPON_MIN_SPEND_LIMIT_NOT_MET:
                    err = sprintf(coupon_i18n[E_WC_COUPON_MIN_SPEND_LIMIT_NOT_MET], accountingPOS(this.minimum_amount, 'formatMoney'));
                    break;
                case E_WC_COUPON_MAX_SPEND_LIMIT_MET:
                    err = sprintf(coupon_i18n[E_WC_COUPON_MAX_SPEND_LIMIT_MET], accountingPOS(this.maximum_amount, 'formatMoney'));
                    break;
                case E_WC_COUPON_NOT_APPLICABLE:
                    err = coupon_i18n[E_WC_COUPON_NOT_APPLICABLE];
                    break;
                case E_WC_COUPON_EXCLUDED_PRODUCTS:
                    // Store excluded products that are in cart in $products
                    var products = [];
                    if (!CART.is_empty()) {
                        var cart = CART.get_cart();
                        $.each(cart, function (cart_item_key, cart_item) {
                            if (in_array(cart_item['product_id'], instance.exclude_product_ids) || in_array(cart_item['variation_id'], instance.exclude_product_ids)) {
                                products.push(cart_item['data'].title);
                            }
                        });
                    }

                    err = sprintf(coupon_i18n[E_WC_COUPON_EXCLUDED_PRODUCTS], implode(', ', products));
                    break;
                case E_WC_COUPON_EXCLUDED_CATEGORIES:
                    // Store excluded categories that are in cart in $categories
                    var _obj_cats = {};
                    var categories = [];
                    if (!CART.is_empty()) {
                        var cart = CART.get_cart();
                        $.each(cart, function (cart_item_key, cart_item) {

                            var product_cats = cart_item.data.categories_ids;
                            if (product_cats) {
                                var intersect = array_intersect(product_cats, instance.exclude_product_category_ids)

                                if (sizeof(intersect) > 0) {
                                    $.each(intersect, function (index, cat_id) {
                                        var cats = cart_item.data.categories;
                                        var cat_idKey = array_search(cat_id, product_cats);
                                        if (typeof cats[cat_idKey] != 'undefined' && typeof _obj_cats[cat_id] == 'undefined') {
                                            _obj_cats[cat_id] = cats[cat_idKey];
                                            categories.push(cats[cat_idKey]);
                                        }
                                    });
                                }

                            }
                        });
                    }

                    err = sprintf(coupon_i18n[E_WC_COUPON_EXCLUDED_CATEGORIES], implode(', ', categories));
                    break;
                case E_WC_COUPON_NOT_VALID_SALE_ITEMS:
                    err = coupon_i18n[E_WC_COUPON_NOT_VALID_SALE_ITEMS];
                    break;
                default:
                    err = '';
                    break;
            }
            return err;
        };


        /**
         * Map one of the WC_Coupon error codes to an error string
         * No coupon instance will be available where a coupon does not exist,
         * so this static method exists.
         *
         * @param int $err_code Error code
         * @return string| Error string
         */
        this.get_generic_coupon_error = function (err_code) {
            if (typeof err_code === "undefined") {
                err_code = '';
            }

            switch (err_code) {
                case E_WC_COUPON_NOT_EXIST:
                    var err = coupon_i18n[E_WC_COUPON_NOT_EXIST];
                    break;
                case E_WC_COUPON_PLEASE_ENTER:
                    err = coupon_i18n[E_WC_COUPON_PLEASE_ENTER];
                    break;
                default:
                    err = '';
                    break;
            }
            // When using this static method, there is no $this to pass to filter
            return err;
        };

        var instance = this;
        this.code = '';
        this.id = 0;
        this.exists = false;

        if (typeof code === "undefined") {
            code = '';
        }
        if (typeof data === "undefined") {
            data = false;
        }
        this.data = data;
        code = code.toLowerCase();
        this.exists = this.get_coupon(code, data);
    }
})(jQuery);