var APP = null;
var TAX = null;
var CUSTOMER = null;
var SHIPPING = null;
var ADDONS = null;
var BOOKING = null;
var SUBSCRIPTION = null;
var PR_ADDONS = null;
var POS_TRANSIENT = {};
var resizeCart = null;
var change_price_timer, changeProductPrice = null;
var changeProductQuantity = null;
var subtotals_height = 0; //TODO: need find better solution
var total_height = 0;
if (jQuery('#offline_indication').length) {
    var $indication = jQuery('#offline_indication');
}
jQuery(document).ready(function ($) {
    localStorage.setItem('register_status_' + pos_register_data.ID, 'open');
    jQuery('#close_register').on('click', function (e) {
        var result = confirm('You are about to close this register. All sales will be logged for this session.');
        if (result) {
            localStorage.setItem('register_status_' + pos_register_data.ID, 'close');
        } else {
            e.preventDefault();
        }
    });
// ajaxSetup is global, but we use it to ensure JSON is valid once returned.temp@_@admintemp
    $.ajaxSetup({
        dataFilter: function (raw_response, dataType) {
            // We only want to work with JSON
            if ('json' !== dataType) {
                return raw_response;
            }
            try {
                // Check for valid JSON
                var data = $.parseJSON(raw_response);
                if (data && 'object' === typeof data) {

                    // Valid - return it so it can be parsed by Ajax handler
                    return raw_response;
                }

            } catch (e) {
                // Attempt to fix the malformed JSON
                var matches = new Array();
                matches.push(raw_response.match(/{"count.*}/));
                matches.push(raw_response.match(/{"order.*}/));
                matches.push(raw_response.match(/{"customer.*}/));
                matches.push(raw_response.match(/{"product.*}/));
                matches.push(raw_response.match(/{"coupon.*}/));
                matches.push(raw_response.match(/{"posts_ids.*}/));


                var valid_json = null;
                for (var i = 0; i < matches.length; i++) {
                    var m = matches[i];
                    if (m !== null) {
                        valid_json = m;
                    }
                }

                if (null === valid_json) {
                    console.log('Unable to fix malformed JSON');
                } else {
                    console.log('Fixed malformed JSON. Original:');
                    //console.log(valid_json[0]);
                    raw_response = valid_json[0];
                }
            }
            return raw_response;
        }
    });

    toastr.options = {
        "closeButton": false,
        "debug": false,
        "newestOnTop": true,
        "progressBar": false,
        "positionClass": "toast-bottom-left",
        "preventDuplicates": true,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "1500",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    }
    window.openwin = false;
    CART = window.POS_CART;
    TAX = window.POS_TAX;
    CUSTOMER = window.POS_CUSTOMER;
    SHIPPING = window.POS_SHIPPING;
    ADDONS = window.POS_ADDONS;
    BOOKING = window.POS_BOOKING;
    SUBSCRIPTION = window.POS_SUBSCRIPTION;
    PR_ADDONS = window.POS_PR_ADDONS;
    APP = window.POS_APP = {
        //version          : Math.random(),
        version: '32',
        customerVersion: '2',
        db: null,
        initialized: false,
        lastUpdate: 'null',
        lastOffset: 0,
        lastUpdateCoupon: 'null',
        lastOffsetCoupon: 0,
        lastUpdateCustomer: 'null',
        lastOffsetCustomer: 0,
        tmp: {
            product_item: {},
            products: {},
            productscans: {},
        },
        coupons: {},
        schema: {
            //version: 1,
            //autoSchema: false, // must be false when version is defined
            stores: [{
                name: 'products',
                keyPath: 'id',
                indexes: [
                    {
                        name: 'title',
                        keyPath: 'title',
                        multiEntry: true,
                    },
                    {
                        name: 'sku',
                        keyPath: 'sku'
                    },
                    {
                        name: 'skutitle',
                        generator: function (obj) {
                            var sku = '';
                            if (obj.sku != '') {
                                sku = obj.sku + ' ';
                            }
                            var search_str = sku + obj.title;
                            search_str = search_str.toLowerCase();
                            return search_str;
                        }
                    },
                ]
            },
                {
                    name: 'variations',
                    keyPath: 'id',
                    indexes: [
                        {
                            name: 'prod_id',
                            keyPath: 'prod_id',
                        },
                        {
                            name: 'sku',
                            keyPath: 'sku',
                        },
                        {
                            name: 'title',
                            keyPath: 'title',
                        },
                    ]
                },
                {
                    name: 'orders',
                    keyPath: 'id'
                },
                {
                    name: 'coupons',
                    keyPath: 'code',
                    /* indexes: [
                     {
                     name: 'code',
                     keyPath: 'code',
                     },
                     ]*/
                },
                {
                    name: 'customers',
                    keyPath: 'id',
                    indexes: [
                        {
                            name: 'email',
                            keyPath: 'email',
                            multiEntry: true,
                        },
                        {
                            name: 'username',
                            keyPath: 'username',
                        },
                        {
                            name: 'phone',
                            generator: function (obj) {
                                var phonenumber = obj.billing_address['phone'];
                                var search_str = phonenumber;
                                if (search_str == '') {
                                    search_str = obj.username;
                                }
                                if (typeof search_str != 'string') {
                                    search_str = '';
                                }
                                search_str = search_str.toLowerCase();
                                return search_str;
                            }
                        },
                        {
                            name: 'fullname',
                            generator: function (obj) {
                                var fullname = [obj.first_name, obj.last_name]
                                var search_str = fullname.join(' ').trim();
                                if (search_str == '') {
                                    search_str = obj.username;
                                }
                                if (typeof search_str != 'string') {
                                    search_str = '';
                                }
                                search_str = search_str.toLowerCase();
                                return search_str;
                            }
                        },
                        {
                            name: 'lastfirst',
                            generator: function (obj) {
                                var lastfirst = [obj.last_name, obj.first_name]
                                var search_str = lastfirst.join(' ').trim();
                                if (search_str == '') {
                                    search_str = obj.username;
                                }
                                if (typeof search_str != 'string') {
                                    search_str = '';
                                }
                                search_str = search_str.toLowerCase();
                                return search_str;
                            }
                        },
                    ]
                },
                {
                    name: 'offline_orders',
                    keyPath: 'id'
                }
            ]
        },
        processing_payment: false,
        interval: 800 * 1000,
        sync_status: {
            'product': false,
            'coupon': false,
            'customer': false
        },


        init: function () {
            if (wc_pos_params.default_country) {
                CUSTOMER.default_country = wc_pos_params.default_country;
                CUSTOMER.default_state = '';
                if (CUSTOMER.default_country.indexOf(':') !== false) {
                    var location = CUSTOMER.default_country.split(':');
                    CUSTOMER.default_country = location[0];
                    CUSTOMER.default_state = location[1];
                }
                CUSTOMER.billing_address.country = CUSTOMER.default_country;
                CUSTOMER.billing_address.state = CUSTOMER.default_state;
                CUSTOMER.shipping_address.country = CUSTOMER.default_country;
                CUSTOMER.shipping_address.state = CUSTOMER.default_state;
            }

            var mechanisms = ['indexeddb', 'websql'];
            var ua = navigator.userAgent.toLowerCase();
            if (ua.indexOf('safari') != -1) {
                if (ua.indexOf('chrome') > -1) {
                    // Chrome
                } else {
                    mechanisms = ['websql', 'indexeddb']; // Safari
                }
            }

            APP.initialized = true;
            APP.checkCookieVersion();

            APP.debug("Open Database. Version " + APP.version);
            APP.db = new ydn.db.Storage('WC-POS', APP.schema, {mechanisms: mechanisms});

            var v = APP.getCookie("pos_Version");
            if (parseInt(v) <= 22) {
                APP.db.clear();
            }

            APP.db.count('products').done(function (x) {
                if (x == 0) {
                    APP.resetCookieVersion();
                }
                APP.lastUpdate = APP.checkCookie();
                APP.lastOffset = APP.checkCookieOffset();

                APP.lastUpdateCoupon = APP.checkCookie('pos_lastUpdateCoupon');
                APP.lastOffsetCoupon = APP.checkCookieOffset('pos_LastOffsetCoupon');

                APP.lastUpdateCustomer = APP.checkCookie('pos_lastUpdateCustomer');
                APP.lastOffsetCustomer = APP.checkCookieOffset('pos_LastOffsetCustomer');

                if (typeof BOOKING != 'undefined') {
                    BOOKING.init();
                }
                if (typeof SUBSCRIPTION != 'undefined') {
                    SUBSCRIPTION.init();
                }
                if (typeof CART != 'undefined') {
                    CART.init();
                }
                if (typeof PR_ADDONS != 'undefined') {
                    PR_ADDONS.init();
                }
                APP.updateSearchList(x);
                APP.sync_data(true);
                APP.ready();
                APP.setCookieVersion();

                wp.heartbeat.interval(15);

                if (wc_pos_params.autoupdate_interval != '') {
                    APP.interval = wc_pos_params.autoupdate_interval * 1000;
                }
                if (wc_pos_params.autoupdate_stock == 'yes') {
                    setInterval(APP.sync_data, APP.interval);
                }
                if (need_register_sync) {
                    APP.db.clear();
                    APP.sync_data(true);
                    window.location.reload();
                }
            });

            if (sizeof(custom_fees) > 0) {
                $.each(custom_fees, function (k, fee) {
                    //TODO: change to bollean by default
                    if (fee.taxable === 'yes') {
                        fee.taxable = true;
                    } else {
                        fee.taxable = false;
                    }
                    fee.amount = floatval(fee.value);
                    CART.add_custom_fee(fee);
                });
            }
        },
        sync_data: function (block) {
            $('#last_sync_time').attr('title', APP.lastUpdate).timeago();
            APP.sync_ProductData(block);
            APP.sync_CouponData(block);
            APP.sync_CustomerData(block);
            APP.check_RemovedItems();
        },
        sync_ProductData: function (block) {
            if (APP.processing_payment === true || APP.sync_status.product === true) return;
            if ($('#grid_layout_cycle').length && block === true && pos_grid.second_column_layout == 'product_grids') {
                var h = parseFloat($('#wc-pos-register-grids').height()) - 80;
                $('#grid_layout_cycle').height(h);
                $('#grid_layout_cycle').block({
                    message: null,
                    overlayCSS: {
                        background: '#fff',
                        opacity: 0.6
                    }
                });
            }
            APP.debug('Checking new products');
            APP.sync_status.product = true;
            $.when(APP.getServerProductsCount()).then(function (ProductsCount) {
                if (parseInt(ProductsCount.count) > 0) {
                    APP.debug(ProductsCount.count + ' new products', false);
                    APP.insertUpdate(100, APP.lastOffset, ProductsCount.count);
                } else {
                    APP.debug('No new products', false);
                    APP.lastUpdate = APP._formatLastUpdateFilter();
                    APP.lastOffset = 0;
                    APP.setCookie('pos_LastOffset', APP.lastOffset, 5);
                    APP.sync_status.product = false;
                    if (block === true) {
                        APP.addGrid();
                    }
                }
            });
        },
        sync_CouponData: function (block) {
            if (APP.processing_payment === true || APP.sync_status.coupon === true) return;
            APP.sync_status.coupon = true;
            $.when(APP.getServerCouponsCount()).then(function (CouponsCount) {
                if (parseInt(CouponsCount.count) > 0) {
                    APP.couponsUpdate(100, APP.lastOffsetCoupon, CouponsCount.count);
                } else {
                    APP.lastUpdateCoupon = APP._formatLastUpdateFilter('pos_lastUpdateCoupon');
                    APP.lastOffsetCoupon = 0;
                    APP.setCookie('pos_LastOffsetCoupon', APP.lastOffsetCoupon, 5);
                    APP.sync_status.coupon = false;
                }
            });
        },
        sync_CustomerData: function (block) {
            if (APP.processing_payment === true || APP.sync_status.customer === true) return;
            if (block === true) {
                $('#wc-pos-customer-data').block({
                    message: null,
                    overlayCSS: {
                        background: '#fff',
                        opacity: 0.6
                    }
                });
            }
            APP.sync_status.customer = true;
            $.when(APP.getServerCustomersCount()).then(function (CustomersCount) {
                if (parseInt(CustomersCount.count) > 0) {
                    APP.customersUpdate(100, APP.lastOffsetCustomer, CustomersCount.count);
                } else {
                    APP.lastOffsetCustomer = 0;
                    APP.setCookie('pos_LastOffsetCustomer', APP.lastOffsetCustomer, 5);
                    APP.updateCustomersCompleted();
                }
            });
        },
        check_RemovedItems: function () {
            $.when(APP.getServerRemovedItems()).then(function (items) {
                if (parseInt(items.post_ids.length) > 0) {
                    $.each(items.post_ids, function (index, post_id) {
                        APP.db.remove('products', post_id);
                        APP.db.remove('variations', post_id);
                        APP.db.remove('orders', post_id);
                        APP.db.remove('coupons', post_id);
                    });
                }
                if (parseInt(items.user_ids.length) > 0) {
                    $.each(items.user_ids, function (index, user_id) {
                        APP.db.remove('customers', user_id);
                    });
                }

            });
        },
        updateCustomersCompleted: function () {
            APP.sync_status.customer = false;
            APP.lastUpdateCustomer = APP._formatLastUpdateFilter('pos_lastUpdateCustomer');
            //Commented 15.09 - circular customer update bug with invalid taxes
            /*if (pos_default_customer) {
             APP.setCustomer(pos_default_customer);
             }*/
            $('#wc-pos-customer-data').unblock();
        },
        insertUpdate: function (limit, offset, ProductsCount) {
            var d = limit + offset;
            if (ProductsCount < d)
                d = ProductsCount;

            $.when(APP.getServerProducts(limit, offset)).then(function (productsData) {
                if (productsData != null && productsData.products.length > 0) {
                    $.each(productsData.products, function (k, product) {
                        if (product.type == 'variable') {
                            $.getJSON(wc_pos_params.ajax_url, {
                                action: "wc_pos_get_default_variations",
                                product_id: product.id
                            }).then(function (result) {
                                product.default_variations = result;
                            });
                        }
                        if (disable_sale_prices) {
                            product.price = product.regular_price;
                            product.on_sale = false;
                            if (product.variations.length > 0) {
                                $.each(product.variations, function (i, variation) {
                                    variation.price = variation.regular_price;
                                    variation.on_sale = false;
                                });
                            }
                        }
                    });
                    APP.db.putAll('products', productsData.products).fail(function (e) {
                        throw e;
                    });
                    $.each(productsData.products, function (index, product) {
                        APP.insertSearchListItem(product);
                        if (product.variations.length > 0) {
                            $.each(product.variations, function (i, variation) {
                                APP.db.put('variations', {
                                    id: variation.id,
                                    prod_id: product.id,
                                    title: product.title,
                                    sku: variation.sku
                                });
                            });
                        }
                    });
                }
                APP.lastOffset = offset;
                APP.setCookie('pos_LastOffset', APP.lastOffset, 5);

                APP.debug('Loaded ' + d + ' of ' + ProductsCount + ' products');

                /*APP.sync_status.product = false;
                 APP.lastUpdate = APP._formatLastUpdateFilter();
                 $.when(APP.getServerGridOptions()).then(function (opt) {
                 APP.addGrid();
                 });*/
                if (ProductsCount >= limit + offset) {
                    APP.insertUpdate(limit, limit + offset, ProductsCount);
                    APP.canHide();
                }
                else {
                    APP.sync_status.product = false;
                    APP.lastUpdate = APP._formatLastUpdateFilter();
                    $.when(APP.getServerGridOptions()).then(function (opt) {
                        APP.addGrid();
                    });
                }
            });
        },
        getServerProductsCount: function () {
            var v = APP.makeid();
            var filter = "?filter[updated_at_min]=" + APP.lastUpdate + "&v=" + v;
            var e = $.getJSON(wc_pos_params.wc_api_url + 'products/count/' + filter);
            return e;
        },
        getServerProducts: function (limit, offset) {
            var v = APP.makeid();
            var filter = "?filter[limit]=" + limit + "&filter[offset]=" + offset + "&filter[updated_at_min]=" + APP.lastUpdate + "&v=" + v;
            var e = $.getJSON(wc_pos_params.wc_api_url + 'products/' + filter, {
                action: "wc_pos_json_api",
            });
            return e;
        },
        getServerOrdersCount: function (reg_id, search) {
            var filter = {};
            if (reg_id == 'all') {
                if (!wc_pos_params.load_web_order) {
                    filter['meta_key'] = 'wc_pos_id_register';
                    filter['meta_value'] = '';
                    filter['meta_compare'] = '!=';
                }
            } else {
                filter['meta_key'] = 'wc_pos_id_register';
                filter['meta_value'] = reg_id;
            }

            if (typeof search != 'undefined') {
                filter['q'] = search;
            }
            var v = APP.makeid();
            var e = $.getJSON(wc_pos_params.wc_api_url + 'orders/count/?v=' + v, {
                action: "wc_pos_json_api",
                reg_id: reg_id,
                filter: filter,
                status: wc_pos_params.load_order_status
            });
            return e;
        },
        getServerOrders: function (opt) {
            var filter = {limit: 20};
            if (typeof opt.currentpage != 'undefined') {
                filter.offset = parseInt(( opt.currentpage - 1 ) * 20);
            }
            if (opt.reg_id == 'all') {
                if (!wc_pos_params.load_web_order) {
                    filter['meta_key'] = 'wc_pos_id_register';
                    filter['meta_value'] = '';
                    filter['meta_compare'] = '!=';
                }
            } else {
                filter['meta_key'] = 'wc_pos_id_register';
                filter['meta_value'] = opt.reg_id;
            }
            if (typeof opt.search != 'undefined') {
                filter['q'] = opt.search;
            }

            var v = APP.makeid();
            var e = $.getJSON(wc_pos_params.wc_api_url + 'orders/?v=' + v, {
                action: "wc_pos_json_api",
                reg_id: opt.reg_id,
                status: wc_pos_params.load_order_status,
                filter: filter
            });
            return e;
        },
        couponsUpdate: function (limit, offset, CouponsCount) {
            var d = limit + offset;
            if (CouponsCount < d)
                d = CouponsCount;

            $.when(APP.getServerCoupons(limit, offset)).then(function (couponsData) {
                if (couponsData.coupons.length > 0) {
                    APP.db.putAll('coupons', couponsData.coupons).fail(function (e) {
                        throw e;
                    });
                }
                APP.lastOffsetCoupon = offset;
                APP.setCookie('pos_LastOffsetCoupon', APP.lastOffsetCoupon, 5);

                if (CouponsCount >= limit + offset) {
                    APP.couponsUpdate(limit, limit + offset, CouponsCount);
                }
                else {
                    APP.lastUpdateCoupon = APP._formatLastUpdateFilter('pos_lastUpdateCoupon');
                    APP.sync_status.coupon = false;
                }
            });
        },

        getServerCouponsCount: function () {
            var v = APP.makeid();
            var filter = "?filter[updated_at_min]=" + APP.lastUpdateCoupon + "&v=" + v;
            var e = $.getJSON(wc_pos_params.wc_api_url + 'coupons/count/' + filter);
            return e;
        },
        getServerCoupons: function (limit, offset) {
            var v = APP.makeid();
            var filter = "?filter[limit]=" + limit + "&filter[offset]=" + offset + "&filter[updated_at_min]=" + APP.lastUpdateCoupon + "&v=" + v;
            var e = $.getJSON(wc_pos_params.wc_api_url + 'coupons/' + filter);
            return e;
        },
        customersUpdate: function (limit, offset, CustomersCount) {
            var d = limit + offset;
            if (CustomersCount < d)
                d = CustomersCount;

            $.when(APP.getServerCustomers(limit, offset)).then(function (customersData) {
                if (customersData.customers.length > 0) {
                    APP.db.putAll('customers', customersData.customers).fail(function (e) {
                        throw e;
                    });
                }
                APP.lastOffsetCustomer = offset;
                APP.setCookie('pos_LastOffsetCustomer', APP.lastOffsetCustomer, 5);

                if (CustomersCount >= limit + offset) {
                    APP.customersUpdate(limit, limit + offset, CustomersCount);
                }
                else {
                    APP.updateCustomersCompleted();
                }
                $('#wc-pos-customer-data').unblock();
            });
        },
        getServerCustomersCount: function () {
            var v = APP.makeid();
            var filter = "?filter[updated_at_min]=" + APP.lastUpdateCustomer + "&v=" + v;
            var e = $.getJSON(wc_pos_params.wc_api_url + 'customers/count/' + filter, {
                action: "wc_pos_json_api",
                role: "all",
            });
            return e;
        },
        getServerCustomers: function (limit, offset) {
            var v = APP.makeid();
            var filter = "?filter[limit]=" + limit + "&filter[offset]=" + offset + "&filter[updated_at_min]=" + APP.lastUpdateCustomer + "&v=" + v;
            var e = $.getJSON(wc_pos_params.wc_api_url + 'customers/' + filter, {
                action: "wc_pos_json_api",
                role: "all",
            });
            return e;
        },
        getServerGridOptions: function () {
            var e = $.getJSON(wc_pos_params.ajax_url, {
                action: "wc_pos_get_grid_options",
                reg: wc_pos_register_id
            });
            return e;
        },
        getServerRemovedItems: function () {
            var v = APP.makeid();
            return jQuery.getJSON(wc_pos_params.wc_api_url + 'pos_removed/?v=' + v);
        },
        canHide: function () {
            APP.addGrid();
            $('#modal-1 .md-close').show();
        },
        loadOrder: function (order_id) {
            APP.db.get('orders', order_id).always(function (order) {

                if (typeof order != 'undefined') {
                    CART.empty_cart(false);

                    POS_TRANSIENT.order_id = order.id;
                    $.each(order.line_items, function (index, item) {

                        item.stock_reduced = order.stock_reduced;

                        var quantity = parseInt(item.quantity);
                        if (wc_pos_params.decimal_quantity == 'yes') {
                            quantity = parseFloat(item.quantity);
                        }
                        var variation_id = 0;
                        var variation = {};

                        if (typeof item.variation_id != 'undefined') {
                            variation_id = item.variation_id;
                        }
                        if (sizeof(item.meta) > 0) {
                            $.each(item.meta, function (index, val) {
                                variation[val.key] = val.value;
                            });
                        }
                        if (pos_custom_product.id == item.product_id || item.product_id == null) {
                            var adding_to_cart = clone(pos_custom_product);
                            adding_to_cart.title = item.name;
                            adding_to_cart.price = item.price;

                            adding_to_cart.regular_price = adding_to_cart.price;

                            var subtotal_tax = parseFloat(item.subtotal_tax);
                            if (item.tax_class != null || subtotal_tax > 0) {
                                adding_to_cart.tax_class = item.tax_class != null ? item.tax_class : '';
                                adding_to_cart.taxable = true;
                                adding_to_cart.tax_status = 'taxable';
                            } else {
                                adding_to_cart.tax_status = 'none';
                                adding_to_cart.taxable = false;
                            }
                            adding_to_cart.item_id = item.id;

                            CART.addToCart(adding_to_cart, adding_to_cart.id, quantity, 0, variation, item);

                        } else {
                            APP.addToCart(item.product_id, quantity, variation_id, variation, 0, item.id, item, true);
                        }
                    });

                    $.each(order.shipping_lines, function (index, method) {
                        var price = parseFloat(method.total);
                        CART.chosen_shipping_methods = {
                            title: method.method_title,
                            price: max(0, price),
                        };
                    });
                    $.each(order.coupon_lines, function (index, coupon) {
                        var amount = parseFloat(coupon.amount);
                        if (coupon.code == 'POS Discount') {
                            if (typeof coupon.percent != 'undefined') {
                                var amount = parseFloat(coupon.percent);
                                CART.add_custom_discount(amount, 'percent');
                            } else {
                                CART.add_custom_discount(amount);
                            }
                        } else {
                            CART.add_discount(coupon.code)
                        }
                    });


                    CART.customer_note = order.note;
                    $('#order_comments').val(order.note);
                    if (order.note != '') {
                        openModal('modal-order_comments');
                    }

                    if (order.customer_id > 0) {
                        APP.setCustomer(order.customer_id);
                    } else {
                        APP.setGuest();
                        CUSTOMER.id = 0;
                    }
                    var arr = ['country', 'first_name', 'last_name', 'company', 'address_1', 'address_2', 'city', 'state', 'postcode', 'email', 'phone'];
                    $.each(arr, function (index, key) {
                        if (typeof order.billing_address[key] != 'undefined') {
                            CUSTOMER.billing_address[key] = order.billing_address[key];
                        }
                        if (typeof order.shipping_address[key] != 'undefined') {
                            CUSTOMER.shipping_address[key] = order.shipping_address[key];
                        }
                    });
                    if (CUSTOMER.first_name == '' && CUSTOMER.billing_address['first_name'] != '') {
                        CUSTOMER.first_name = CUSTOMER.billing_address['first_name'];
                    }
                    if (CUSTOMER.last_name == '' && CUSTOMER.billing_address['last_name'] != '') {
                        CUSTOMER.last_name = CUSTOMER.billing_address['last_name'];
                    }
                    if (CUSTOMER.email == '' && CUSTOMER.billing_address['email'] != '') {
                        CUSTOMER.email = CUSTOMER.billing_address['email'];
                    }

                    var fullname = [CUSTOMER.first_name, CUSTOMER.last_name];
                    fullname = fullname.join(' ').trim();

                    if (fullname == '') {
                        fullname = clone(CUSTOMER.username);
                    }
                    if (fullname != '') {
                        CUSTOMER.fullname = fullname;
                    }
                    CUSTOMER.points_balance = 0;

                    $('#createaccount').prop('checked', false);
                    CUSTOMER.create_account = false;

                    if (fullname != '' || CUSTOMER.email != '') {
                        var source = $('#tmpl-cart-customer-item').html();
                        var template = Handlebars.compile(source);
                        var html = template(CUSTOMER);
                        $('tbody#customer_items_list').html(html);
                    }
                    CART.calculate_totals();
                    $('#pos_register_buttons').append('<a class="order-remove page-title-action load-order">Order #' + order_id + '</a>');
                    $('.order-remove').on('click', function (e) {
                        e.preventDefault();
                        $('.load-order').remove();
                        CART.empty_cart();
                        delete POS_TRANSIENT.order_id;
                    })
                } else {
                    APP.showNotice(pos_i18n[4], 'error');
                }
            });
        },
        addToCart: function (product_id, quantity, variation_id, variation, cart_item_data, item_id, item, loaded) {
            if (typeof loaded === 'undefined') {
                loaded = false;
            }
            product_id = parseInt(product_id);
            product_id = wp.hooks.applyFilters('wc_pos_add_to_cart_product_id', product_id);
            var was_added_to_cart = false;

            if (typeof quantity == 'undefined') {
                quantity = wc_pos_params.decimal_quantity_value;
            }
            if (typeof variation_id == 'undefined') {
                variation_id = 0;
            }
            if (typeof variation == 'undefined') {
                variation = {};
            }
            if (typeof cart_item_data != 'object') {
                cart_item_data = {};
            }

            APP.db.get('products', product_id).always(function (record) {
                var adding_to_cart = record;
                if (!adding_to_cart) {
                    return;
                }
                if (typeof item_id != 'undefined') {
                    adding_to_cart.item_id = item_id;
                }
                if (typeof item != 'undefined') {
                    adding_to_cart.loaded_price = item.price;
                    if (record.managing_stock === true && item.stock_reduced) {
                        adding_to_cart.in_stock = true;
                        adding_to_cart.stock_quantity += quantity;
                    }
                    if (typeof item.hidden_fields != 'undefined') {
                        adding_to_cart.hidden_fields = item.hidden_fields;
                    }
                }

                var add_to_cart_handler = wp.hooks.applyFilters('wc_pos_add_to_cart_handler', adding_to_cart.type, adding_to_cart);

                APP.tmp.product_item = {
                    product_id: product_id,
                    adding_to_cart: adding_to_cart,
                    quantity: quantity,
                    variation_id: variation_id,
                    variation: variation,
                    cart_item_data: cart_item_data
                }
                var handler_action_name = 'wc_pos_add_to_cart_handler_' + add_to_cart_handler;

                // Variable product handling
                if ('variable' === add_to_cart_handler || 'variable-subscription' === add_to_cart_handler) {
                    was_added_to_cart = APP.add_to_cart_handler_variable(product_id, adding_to_cart, quantity, variation_id, variation, cart_item_data);
                    // Grouped Products
                    /*} else if ( 'grouped' === add_to_cart_handler ) {
                     was_added_to_cart = APP.add_to_cart_handler_grouped( product_id, adding_to_cart, quantity, variation_id, variation, cart_item_data );
                     */
                    // Custom Handler
                } else if (wp.hooks.hasFilter(handler_action_name)) {
                    was_added_to_cart = wp.hooks.applyFilters(handler_action_name, false, product_id, adding_to_cart, quantity, variation_id, variation, cart_item_data);

                    // Simple Products
                } else {
                    was_added_to_cart = APP.add_to_cart_handler_simple(product_id, adding_to_cart, quantity, variation_id, variation, cart_item_data);
                }
                // If we added the product to the cart we can now optionally do a reset.
                if (was_added_to_cart) {
                    APP.tmp.product_item = {};
                    runTips();
                    //var cart_item_key = CART.addToCart(adding_to_cart, product_id, quantity, variation_id, variation, cart_item_data);
                    var msg = wp.hooks.applyFilters('wc_pos_added_to_cart_message', '', product_id, adding_to_cart, quantity, variation_id, variation, cart_item_data);
                    APP.showNotice(msg, 'basket_addition');
                } else if (loaded) {
                    CART.addToCart(adding_to_cart, product_id, quantity, variation_id, variation, cart_item_data)
                }
            });
        },
        add_to_cart_handler_simple: function (product_id, adding_to_cart, quantity, variation_id, variation, cart_item_data) {
            var missing = wp.hooks.applyFilters('wc_pos_validate_missing_attributes', false, adding_to_cart, product_id, quantity, variation_id, variation, cart_item_data);
            if (missing === true) {
                if (window.openwin === false && typeof adding_to_cart.item_id == 'undefined') {
                    openModal('modal-missing-attributes', true);

                    if (adding_to_cart.type !== 'variable') {
                        $('#missing-attributes-select').trigger('found_variation', [{data: adding_to_cart}]);
                    } else {
                        $('#selected-variation-data, #reset_selected_variation').hide();
                    }
                    return false;
                }
            } else if (wc_pos_params.instant_quantity == 'yes' && $('#modal-qt-product').length && window.openwin === false && typeof adding_to_cart.item_id == 'undefined') {
                jQuery('#modal-qt-product .keypad-clear').click();
                openModal('modal-qt-product', true);
                return false;
            }
            window.openwin = false;
            //TODO: Commented by load order variations bug 11.05.17 - filter don't work
            //var passed_validation = wp.hooks.applyFilters('wc_pos_add_to_cart_validation', true, adding_to_cart, product_id, quantity);
            var passed_validation = true;
            var cart_item_key = '';
            if (passed_validation && (cart_item_key = CART.addToCart(adding_to_cart, product_id, quantity, variation_id, variation, cart_item_data) )) {
                return true;
            }
            return false;
        },
        add_to_cart_handler_variable: function (product_id, adding_to_cart, quantity, variation_id, selected_attr, cart_item_data) {
            var missing = false;
            var missing_attributes = {};
            var variations = {};
            var attributes = adding_to_cart.attributes;
            var variation = null;
            APP.tmp.product_item.product_variations = [];
            if (typeof selected_attr != 'undefined' && sizeof(selected_attr) > 0) {
                variations = selected_attr;
            } else {
                selected_attr = {};
            }
            $.each(adding_to_cart.variations, function (index, val) {
                var attributes = {};
                $.each(val.attributes, function (i, attr) {
                    var slug = attr.slug;
                    attributes[slug] = attr.option;
                });
                APP.tmp.product_item.product_variations[index] = {attributes: attributes};
                APP.tmp.product_item.product_variations[index]['variation_is_active'] = true;
                APP.tmp.product_item.product_variations[index]['variation_id'] = val.id;
                APP.tmp.product_item.product_variations[index]['data'] = val;

                if (val.id == variation_id) {
                    if (val.attributes) {
                        $.each(val.attributes, function (i, attr) {
                            if (attr.option != '') {
                                selected_attr[attr.slug] = attr.option;
                            }
                        });
                    }
                    variation = val;
                    return;
                }
            });
            if (typeof adding_to_cart.item_id == 'undefined') {
                $.each(attributes, function (index, attribute) {
                    var taxonomy = attribute['slug'];
                    if (attribute.variation == true) {

                        if (typeof selected_attr[taxonomy] != 'undefined' && variation != null) {
                            // Get value
                            variations[taxonomy] = selected_attr[taxonomy];
                        } else {
                            missing = true;
                        }
                        missing_attributes[taxonomy] = attribute;
                    }
                });
            }

            missing = wp.hooks.applyFilters('wc_pos_validate_missing_attributes', missing, adding_to_cart, product_id, quantity, variation_id, selected_attr, cart_item_data);
            if (missing === true) {
                var source = $('#tmpl-missing-attributes').html();
                var template = Handlebars.compile(source);
                var html = template({attr: missing_attributes});
                $html = $(html);
                $.each(variations, function (i, opt) {
                    i = i.replace(/[\s!@#$%^&*();:]/g, '');
                    $html.find("select.attribute_" + i).val(opt);
                });

                $('#modal-missing-attributes').addClass('missing-attributes md-close-by-overlay');
                $('#missing-attributes-select').html($html);

                if (window.openwin === false && typeof adding_to_cart.item_id == 'undefined') {
                    $('#selected-variation-data, #reset_selected_variation').hide();
                    openModal('modal-missing-attributes', true);
                    $.each(default_variations[adding_to_cart.id], function (k, v) {
                        var taxonomy = k.replace('pa_', '');
                        $("[data-taxonomy=" + taxonomy + "] [value='" + v + "']").attr('selected', true);
                        $("[data-taxonomy=" + taxonomy + "]").change();
                    });
                }
            } else if (typeof variation_id == 'undefined') {
                APP.showNotice(pos_i18n[2], 'error');
            }
            else {
                /*if (wc_pos_params.instant_quantity == 'yes' && $('#modal-qt-product').length && window.openwin === false && typeof adding_to_cart.item_id == 'undefined') {
                 openModal('modal-qt-product', true);
                 return false;
                 }
                 window.openwin = false;
                 var passed_validation = wp.hooks.applyFilters('wc_pos_add_to_cart_validation', true, adding_to_cart, product_id, quantity, variation_id, variations);
                 var cart_item_key = '';

                 if (passed_validation && (cart_item_key = CART.addToCart(adding_to_cart, product_id, quantity, variation_id, variations, cart_item_data) )) {
                 return true;
                 }*/
                return APP.add_to_cart_handler_simple(product_id, adding_to_cart, quantity, variation_id, variations, cart_item_data);
            }
            return false;
        },
        voidRegister: function (notice) {
            if (typeof POS_TRANSIENT.order_id != 'undefined' && POS_TRANSIENT.order_id > 0) {
                $('#post-body').block({
                    message: null,
                    overlayCSS: {
                        background: '#fff',
                        opacity: 0.6
                    }
                });
                var order_id = POS_TRANSIENT.order_id;
                var register_id = wc_pos_register_id;
                $.ajax({
                    type: 'POST',
                    url: wc_pos_params.ajax_url,
                    data: {
                        action: 'wc_pos_void_register',
                        security: wc_pos_params.void_register_nonce,
                        order_id: order_id,
                        register_id: register_id,
                    },
                    success: function (response) {
                        if (notice !== false) {
                            APP.showNotice(pos_i18n[13]);
                        }

                        CART.empty_cart();
                        delete POS_TRANSIENT.order_id;
                    },

                })
                    .always(function (response) {
                        $('#post-body').unblock();
                    });
            } else {
                CART.empty_cart();
                if (notice !== false) {
                    APP.showNotice(pos_i18n[13]);
                }
                delete POS_TRANSIENT.order_id;
            }
        },
        setCustomer: function (customer_id, open) {
            CUSTOMER.reset();
            if (customer_id != '' && parseInt(customer_id) > 0) {
                customer_id = parseInt(customer_id);
                APP.db.get('customers', customer_id).done(function (record) {
                    if (typeof record.user_meta.wp_user_avatar != 'undefined' && record.user_meta.wp_user_avatar[0]) {
                        $.ajax({
                            type: 'POST',
                            url: wc_pos_params.ajax_url,
                            data: {
                                action: 'wc_pos_get_user_avatars',
                                userdata: record
                            },
                            success: function (response) {
                                CUSTOMER.set_default_data(JSON.parse(response))
                            }
                        })
                    } else {
                        CUSTOMER.set_default_data(record);
                    }
                    CART.calculate_totals();

                    if (open == true) {
                        $('a.show_customer_popup').trigger('click');
                    }

                });
            }
        },
        setGuest: function () {
            CUSTOMER.reset();
            CUSTOMER.set_default_data();
            CART.calculate_totals();
            runTips();
            return false;
        },
        searchByTerm: function (term) {
            var _term = term.toLowerCase();
            //var q = APP.db.from( 'products' ).where('sku', '^', term);//.where('title', '^', term, '^', _term)
            /*var q = APP.db.from( 'products' ).where('title', '^', term);
             var limit = 10000;
             var result = [];
             q.list( limit ).done( function( objs ) {
             result = objs;
             });
             var result = [];
             APP.db.count('products').done(function(x) {
             console.log('Number of authors: ' + x);
             APP.db.from('products').order('sku').list(x).done(function(records) {

             result = $.grep(records, function(e){
             var sku  = e.sku;
             var title = e.title;
             title = title.toLowerCase();
             return title.indexOf(_term) >= 0 || sku.indexOf(_term) >= 0;
             });

             console.log(result);

             });
             });

             return result;*/
        },
        debug: function (msg, type) {
            if ($('#process_loding').length) {
                if (typeof msg == 'string' && msg != '') {
                    if (type == false)
                        $('#process_loding').append('<p>' + msg + '</p>');
                    else
                        $('#process_loding').append('<p>' + msg + '<span class="dot one">.</span><span class="dot two">.</span><span class="dot three">.</span>​</p>');
                }
                $('#process_loding').scrollTop($('#process_loding')[0].scrollHeight);
            }
        },
        setCookie: function (cname, cvalue, exdays) {
            var d = new Date();
            d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
            var expires = "expires=" + d.toGMTString();
            document.cookie = cname + "=" + cvalue + "; " + expires;
        },
        getCookie: function (cname) {
            var name = cname + "=";
            var ca = document.cookie.split(';');
            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) == ' ') c = c.substring(1);
                if (c.indexOf(name) != -1) {
                    return c.substring(name.length, c.length);
                }
            }
            return "";
        },
        checkCookie: function (name) {
            if (typeof name == 'undefined') {
                name = 'pos_lastUpdate';
            }
            var LU = APP.getCookie(name);
            if (LU != "") {
                return LU;
            }
            return 'null';
        },
        checkCookieOffset: function (name) {
            if (typeof name == 'undefined') {
                name = 'pos_LastOffset';
            }
            var LO = APP.getCookie(name);
            if (LO != "") {
                return parseInt(LO);
            }
            return 0;
        },
        checkCookieVersion: function () {
            var v = APP.getCookie("pos_Version");
            var cv = APP.getCookie("pos_ustomerVersion");
            if (v != APP.version) {
                APP.resetCookieVersion();
            } else if (cv != APP.customerVersion) {
                APP.setCookie('pos_LastOffsetCustomer', '', 5);
                APP.setCookie('pos_lastUpdateCustomer', '', 5);
            }
        },
        resetCookieVersion: function () {
            APP.setCookie('pos_LastOffset', '', 5);
            APP.setCookie('pos_lastUpdate', '', 5);
            APP.setCookie('pos_LastOffsetCoupon', '', 5);
            APP.setCookie('pos_lastUpdateCoupon', '', 5);
            APP.setCookie('pos_LastOffsetCustomer', '', 5);
            APP.setCookie('pos_lastUpdateCustomer', '', 5);
        },
        setCookieVersion: function () {
            APP.setCookie("pos_Version", APP.version, 365);
            APP.setCookie("pos_ustomerVersion", APP.customerVersion, 365);
        },
        updateSearchList: function (x) {
            if (x > 0) {
                APP.db.from('products').list(x).done(function (records) {

                    for (i = 0; i < x; i++) {
                        var obj = records[i];
                        APP.insertSearchListItem(obj);
                    }
                });
            }
        },
        insertSearchListItem: function (obj) {
            var val = JSON.stringify({id: obj.id});
            var t = obj.title;
            if (obj.sku != '') {
                t = obj.sku + ' - ' + obj.title;
            }
            APP.tmp.products[obj.id] = {id: val, text: t, post_meta: obj.post_meta};

            if (wc_pos_params.scan_field && wc_pos_params.scan_field != '' && typeof obj.post_meta != 'undefined' && typeof obj.post_meta[wc_pos_params.scan_field] != 'undefined') {
                var s = obj.post_meta[wc_pos_params.scan_field][0];
                if (s != '') {
                    APP.tmp.productscans[s] = {id: obj.id};
                }
            }

            if (obj.variations.length > 0) {
                for (j = 0; j < obj.variations.length; j++) {
                    var name = t;
                    var v = obj.variations[j];

                    if (v.sku != '') {
                        name = v.sku + ' - ' + obj.title;
                    }
                    var selected_attr = {};

                    $.each(v.attributes, function (k, a) {
                        if (a.option != '') {
                            selected_attr[a.slug] = a.option;
                            name += ' - ' + a.option;
                        }
                    });
                    var val = JSON.stringify({id: obj.id, vid: v.id, variation: selected_attr});
                    APP.tmp.products[v.id] = {id: val, text: name, post_meta: obj.post_meta};

                    if (wc_pos_params.scan_field && wc_pos_params.scan_field != '' && typeof v.post_meta != 'undefined' && typeof v.post_meta[wc_pos_params.scan_field] != 'undefined') {
                        var s = v.post_meta[wc_pos_params.scan_field][0];
                        if (s != '' && typeof APP.tmp.productscans[s] == 'undefined') {
                            APP.tmp.productscans[s] = {id: obj.id, vid: v.id};
                        }
                    }

                }
            }
        },
        _formatLastUpdateFilter: function (name) {
            if (typeof name == 'undefined') {
                name = 'pos_lastUpdate';
            }
            var t = new Date();
            if (t.getTime() > 0) {
                var r = t.getUTCFullYear(),
                    i = t.getUTCMonth() + 1,
                    s = t.getUTCDate(),
                    o = t.getUTCHours(),
                    u = t.getUTCMinutes(),
                    a = t.getUTCSeconds();
                var dd = r + "-" + i + "-" + s + "T" + o + ":" + u + ":" + a + "Z";
                APP.setCookie(name, dd, 5);
                return dd;
            }
            $('#last_sync_time').html('');
            return null
        },
        showNotice: function (msg, type) {
            if (typeof type == 'undefined') {
                type = 'success';
            }
            if (typeof msg != 'undefined') {
                switch (type) {
                    case 'error':
                        toastr.error(msg);
                        if (!wc_pos_params.disable_sound_notifications) {
                            ion.sound.play("error");
                        }
                        break;
                    case 'success':
                        toastr.success(msg);
                        if (!wc_pos_params.disable_sound_notifications) {
                            ion.sound.play("succesful_order");
                        }
                        break;
                    case 'info':
                        toastr.info(msg);
                        if (!wc_pos_params.disable_sound_notifications) {
                            ion.sound.play("succesful_order");
                        }
                        break;
                    case 'basket_addition':
                        if (msg == '') {
                            msg = pos_i18n[6];
                        }
                        toastr.info(msg);
                        if (!wc_pos_params.disable_sound_notifications) {
                            ion.sound.play("basket_addition");
                        }
                        break;
                    case 'succesful_order':
                        toastr.success(msg);
                        if (!wc_pos_params.disable_sound_notifications) {
                            ion.sound.play("succesful_order");
                        }
                        break;
                }
            }
        },
        addGrid: function () {
            if (pos_grid.second_column_layout == 'product_grids') {
                var ul = $('<ul></ul>');
                if (pos_grid.grid_id == 'categories') {
                    $.each(pos_grid.categories, function (i, cat) {
                        /*if (cat.parent != 0) {
                         return true;
                         }*/
                        var $li = $('<li id="category_' + cat.term_id + '" class="title_category open_category category_cycle" data-catid="' + cat.term_id + '" data-parent="' + cat.parent + '" data-title="' + cat.name + '"><span></span></li>');
                        $li.find('span').html(cat.name);
                        $li.data('title', cat.name).css({
                            'background-image': 'url(' + cat.image + ')'
                        });
                        ul.append($li);
                    });
                }
                $('#grid_layout_cycle').html(ul);
                $('#grid_layout_cycle').unblock();
                resizeGrid();
            } else {
                $('#grid_layout_cycle').unblock();
            }
        },
        find_matching_variations: function (product_variations, settings) {
            var matching = [];
            for (var i = 0; i < product_variations.length; i++) {
                var variation = product_variations[i];

                if (APP.variations_match(variation.attributes, settings)) {
                    matching.push(variation);
                }
            }
            return matching;
        },
        variations_match: function (attrs1, attrs2) {
            var match = true;
            for (var attr_name in attrs1) {
                if (attrs1.hasOwnProperty(attr_name)) {
                    var val1 = attrs1[attr_name];
                    var val2 = attrs2[attr_name];

                    if (val1 !== undefined && val2 !== undefined && val1.length !== 0 && val2.length !== 0 && val1 != val2) {
                        match = false;
                    }
                }
            }

            return match;
        },
        createOrder: function (paid) {
            if (typeof paid === 'undefined') {
                paid = false;
            }

            var cart_contents = CART.cart_contents;

            var line_items = [];
            $.each(cart_contents, function (index, item) {
                var _item = {};
                _item.id = typeof(item.item_id) != 'undefined' ? item.item_id : 0;
                _item.product_id = item.product_id;
                _item.price = item.price;
                _item.variation_id = item.variation_id;
                _item.quantity = item.quantity;
                _item.variations = item.variation;
                _item.total = item.line_total;
                _item.subtotal = item.line_subtotal;
                _item.title = item.data.title;
                _item.tax_class = item.data.tax_class;
                _item.tax_status = item.data.tax_status;

                if (typeof item.data.hidden_fields != 'undefined') {
                    _item.hidden_fields = item.data.hidden_fields;
                }

                if (item.variation_id > 0) {
                    _item.tax_class = item.v_data.tax_class;
                    _item.tax_status = item.v_data.tax_status;
                }
                if (_item.tax_status == 'none') {
                    _item.tax_class = '0';
                } else {
                    _item.subtotal_tax = item.line_subtotal_tax;
                    _item.tax_data = item.line_tax_data;
                    _item.total_tax = item.line_tax;
                }
                line_items.push(_item);
            });

            var cart = {
                order: {
                    "action": 'create',
                    "status": paid ? wc_pos_params.complete_order_status : wc_pos_params.save_order_status,
                    "line_items": line_items,
                    "note": CART.customer_note,
                    "billing_address": CUSTOMER.billing_address.first_name != '' ? CUSTOMER.billing_address : {},
                    "shipping_address": CUSTOMER.shipping_address.first_name != '' ? CUSTOMER.shipping_address : {},
                    "additional_fields": CUSTOMER.additional_fields,
                    "customer_id": CUSTOMER.id,
                    "create_account": CUSTOMER.create_account,
                    "user_meta": CUSTOMER.acf_fields,
                    "custom_order_meta": CUSTOMER.custom_order_fields,
                    "fees": CART.fees,
                    "order_meta": {
                        "wc_pos_order_saved": false,
                        "wc_pos_amount_change": "",
                        "wc_pos_amount_pay": "",
                        "wc_pos_id_register": wc_pos_register_id,
                        "wc_pos_order_tax_number": "",
                        "wc_pos_order_type": "POS",
                        "wc_pos_prefix_suffix_order_number": pos_register_data.prefix + String(pos_register_data.order_id) + pos_register_data.suffix
                    },
                }
            };
            $.each(CUSTOMER.additional_fields, function (index, val) {
                cart.order.order_meta[index] = val;
            });
            var acf_order_fields = wc_pos_params.acf_order_fields;
            $.each(wc_pos_params.acf_order_fields, function (index, val) {
                if (typeof CUSTOMER.acf_fields[val] != 'undefined') {
                    cart.order.custom_order_meta[val] = CUSTOMER.acf_fields[val];
                    delete cart['order']['user_meta'][val];
                }
            });

            if (typeof POS_TRANSIENT.order_id != 'undefined' && POS_TRANSIENT.order_id > 0) {
                cart.order.action = 'update';
                cart.order.order_meta.wc_pos_prefix_suffix_order_number = pos_register_data.prefix + String(POS_TRANSIENT.order_id) + pos_register_data.suffix;
            }
            if (paid) {
                var selected_pm = $('.select_payment_method:checked:not(:disabled)').val();
                var selected_pm_t = $('a.payment_method_' + selected_pm).text();
                cart.order.payment_details = {
                    "method_id": selected_pm,
                    "method_title": selected_pm_t.trim(),
                    "paid": paid
                };
                if (selected_pm == 'cod') {
                    if (wc_pos_params.wc_pos_rounding) {
                        cart.order.order_meta.wc_pos_order_rounding = 'yes';
                        cart.order.order_meta.wc_pos_rounding_total = CART.total;
                    }
                    cart.order.order_meta.wc_pos_amount_change = $('#amount_change_cod').val();
                    cart.order.order_meta.wc_pos_amount_pay = $('#amount_pay_cod').val();
                }
            } else {
                cart.order.order_meta.wc_pos_order_saved = true;
            }
            ;
            //shipping_lines
            if (CART.needs_shipping()) {
                cart.order.shipping_lines = [{
                    method_id: '',
                    method_title: CART.chosen_shipping_methods.title,
                    total: CART.shipping_total,
                    taxes: CART.shipping_taxes,
                }];
            }
            if (Object.size(CART.applied_coupons) > 0) {
                cart.order.coupon_lines = [];
                //cart.order.coupon_lines.push({'amount' : 10, 'code' : 'POS Discount'});
                $.each(CART.applied_coupons, function (index, coupon_code) {
                    var amount = CART.get_coupon_discount_amount(coupon_code, true);
                    if (amount) {
                        var c_data = {'amount': amount, 'code': coupon_code};
                        if (coupon_code == 'POS Discount') {
                            var type = CART.coupons[coupon_code]['data']['type'];
                            if (type == 'percent') {
                                c_data.type = type;
                                c_data.pamount = CART.coupons[coupon_code]['data']['amount'];
                            }
                        }
                        cart.order.coupon_lines.push(c_data);
                    }
                });
                if (sizeof(cart.order.coupon_lines) == 0) {
                    delete cart.order.coupon_lines;
                }
                ;
            }

            $('#modal-order_payment, #post-body').block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });

            var selected_pm = $('.select_payment_method:checked:not(:disabled)').val();
            if (paid) {
                $.when(wp.hooks.applyFilters('wc_pos_process_payment', cart, selected_pm)).then(function (cart) {
                    if (cart) {
                        if (!$indication || $indication.hasClass('offline-ui-up')) {
                            APP.processPayment(cart, true);
                        } else {
                            cart.order.id = Date.now();
                            var order_number = Math.floor(Math.random() * 900000) + 100000;
                            cart.order.order_meta.wc_pos_prefix_suffix_order_number = pos_register_data.prefix + order_number + pos_register_data.suffix + '-OFFLINE';
                            APP.db.put('offline_orders', cart.order).fail(function (e) {
                                throw e;
                            });
                            closeModal('modal-order_payment');
                            APP.showNotice(pos_i18n[43], 'succesful_order');
                            CART.empty_cart();
                            jQuery('.blockOverlay').css('display', 'none');
                        }
                    }
                });
            } else {
                APP.processPayment(cart, false, pos_register_data.settings.note_request, pos_register_data.settings.print_receipt);
            }
        },
        processPayment: function (cart, paid, show_message, print_receipt) {
            show_message = (typeof show_message !== 'undefined' && show_message !== 0 ) ? show_message : true;
            print_receipt = (typeof print_receipt !== 'undefined' && print_receipt !== 0) ? print_receipt : true;
            var v = APP.makeid();
            var wc_api_url = wc_pos_params.wc_api_url + 'pos_orders/';
            if (typeof POS_TRANSIENT.order_id != 'undefined' && POS_TRANSIENT.order_id > 0) {
                wc_api_url += POS_TRANSIENT.order_id;
            } else {
                wc_api_url += pos_register_data.order_id;
            }
            wc_api_url += '/?v=' + v;
            APP.processing_payment = true;
            $.ajax({
                url: wc_api_url + '/',
                /*beforeSend: function(xhr) {
                 xhr.setRequestHeader("Authorization", "Basic " + btoa("username:password"));
                 },*/
                type: 'POST',
                dataType: 'json',
                contentType: 'application/json',
                processData: false,
                data: JSON.stringify(cart),
                success: function (data) {
                    var success = true;
                    if (paid) {
                        if (typeof data != 'undefined' && typeof data['payment_result'] != 'undefined' && typeof data['payment_result']['result'] != 'undefined') {
                            if (data['payment_result']['result'] == 'error') {
                                APP.showNotice(data['payment_result']['messages'], 'error');
                                success = false;
                            } else {
                                if (typeof data['payment_result']['redirect'] != 'undefined' && data['payment_result']['redirect'] != '') {
                                    $('#modal-redirect-payment #payment_result_message').html(data['payment_result']['messages']);
                                    openModal('modal-redirect-payment');
                                    setTimeout(function () {
                                        window.location.href = data['payment_result']['redirect'];
                                    }, 1000);
                                } else {
                                    //APP.showNotice( data['payment_result']['messages'], 'success');
                                    if ($('#payment_switch').bootstrapSwitch('state')) {
                                        if (print_receipt != 0) {
                                            posPrintReceipt(data.order.print_url, $('#payment_print_gift_receipt').bootstrapSwitch('state'));
                                        }
                                    } else {
                                        if (change_user) {
                                            APP_auth_show();
                                        }
                                        wp.heartbeat.connectNow();
                                    }
                                }
                            }
                        } else if ($('#payment_switch').bootstrapSwitch('state')) {
                            if (print_receipt != 0) {
                                posPrintReceipt(data.order.print_url);
                            }
                        } else {
                            if (change_user) {
                                APP_auth_show();
                            }
                            wp.heartbeat.connectNow();
                        }

                        //$('#payment_switch').bootstrapSwitch('state', print_receipt);
                        //$('#payment_email_receipt').bootstrapSwitch('state', email_receipt);
                    } else {
                        if (print_receipt != 0) {
                            posPrintReceipt(data.order.print_url);
                        }
                    }
                    if (success) {
                        if (show_message) {
                            if (paid) {
                                APP.showNotice(pos_i18n[12], 'succesful_order');
                            } else {
                                APP.showNotice(pos_i18n[14], 'succesful_order');
                            }
                        }
                        CART.empty_cart();
                        closeModal('modal-order_payment');
                        $('.load-order').remove();

                        if (typeof data.new_order != 'undefined') {
                            pos_register_data.order_id = data.new_order;
                        }
                        delete POS_TRANSIENT.order_id;
                        ADDONS.crlearCardfields();
                        APP.processing_payment = false;
                        APP.sync_data(true);

                        if (pos_default_customer > 0) {
                            APP.setCustomer(pos_default_customer);
                        } else {
                            APP.setGuest();
                        }
                    }
                },
                error: function (data) {
                    console.log(data);
                    var data = $.parseJSON(data.responseText);
                    if (data.errors && typeof data.errors != 'undefined') {
                        $.each(data.errors, function (index, val) {
                            APP.showNotice(val.message, 'error');
                        });
                    }
                }
            }).always(function (response) {
                APP.processing_payment = false;
                $('#modal-order_payment, #post-body, form.woocommerce-checkout').unblock();
                jQuery(document.body).trigger('updated_checkout');
            });
            POS_TRANSIENT.save_order = false;
        },
        getOrdersListContent: function (opt) {
            $('#retrieve-sales-wrapper .box_content').hide();
            $('#retrieve_sales_popup_inner').html('');
            $('#modal-retrieve_sales .wrap-button').html('');
            $('#retrieve-sales-wrapper').block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });
            $.when(APP.getServerOrders(opt)).then(function (ordersData) {

                APP.db.putAll('orders', ordersData.orders).fail(function (e) {
                    throw e;
                });
                var pager = getOrdersListPager(opt);
                $('#modal-retrieve_sales .wrap-button').html(pager);

                var source = $('#tmpl-retrieve-sales-orders-list').html();
                var template = Handlebars.compile(source);
                var html = template(ordersData.orders);

                $('#retrieve-sales-wrapper .box_content').css('visibility', 'hidden').show();
                $('#retrieve_sales_popup_inner').html(html);

                var table_h = $('#retrieve_sales_popup_inner table').height();
                var wrapper_h = $('#retrieve-sales-wrapper .box_content').height();
                var nav_h = $('#retrieve-sales-wrapper .tablenav_wrap_top').height();

                if (table_h > ( wrapper_h - nav_h )) {
                    $('#retrieve-sales-wrapper').addClass('big-size');
                } else {
                    $('#retrieve-sales-wrapper').removeClass('big-size');
                }
                $('#retrieve-sales-wrapper .box_content').removeAttr('style');
                runTips();
                $('#retrieve-sales-wrapper').unblock();
            });
            return false;
        },
        checkStock: function (product_data, quantity, cart_item_key) {
            try {
                var product_id = typeof product_data.variation_id != 'undefined' ? parseInt(product_data.variation_id) : parseInt(product_data.product_id);

                if (product_data.in_stock === false && product_data.backorders_allowed === false) {
                    throw new Error(sprintf(pos_i18n[3], product_data.title));
                }
                if (CART.has_enough_stock(product_data, quantity) === false) {
                    throw new Error(sprintf(pos_i18n[4], product_data.title, product_data.stock_quantity));
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

                    if (product_data.stock_quantity < check_qty + quantity && product_data.backorders_allowed) {
                        if (cart_item_key != 'undefined') {
                            var view = $('tr#' + cart_item_key + ' td.name .view');
                            if (!view.find('.backorders_allowed').length) {
                                view.append('<span class="register_stock_indicator backorders_allowed">' + pos_i18n[40] + ' </span>');
                            }
                        }
                    } else if (cart_item_key != 'undefined') {
                        $('tr#' + cart_item_key + ' td.name .view .backorders_allowed').remove();
                    }
                }
                return true;
            } catch (e) {
                console.log(e);
                APP.showNotice(e.message, 'error');
                return false;
            }
        },
        makeid: function () {
            var text = "";
            var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

            for (var i = 0; i < 5; i++)
                text += possible.charAt(Math.floor(Math.random() * possible.length));

            return text;
        },
        add_customer_item_to_result: function (customer) {
            var source = $('#tmpl-search-customer-result').html();
            var template = Handlebars.compile(source);
            var html = template(customer);
            $('#customer_search_result').append(html);
        },
        search_customer: function (query) {
            $('#customer_search_result').html('');
            var term = query.term;
            var _term = POS_TRANSIENT.searching_term = term.toLowerCase();

            var data = {results: []};
            var q = APP.db.from('customers').where('fullname', '^', _term);
            var limit = 10000;
            var result = [];
            var chk = {};
            q.list(limit).done(function (objs) {
                $.each(objs, function (index, val) {
                    if (POS_TRANSIENT.searching_term !== _term) return false;
                    var fullname = [val.first_name, val.last_name];
                    fullname = fullname.join(' ').trim();
                    if (fullname == '') {
                        fullname = val.username;
                    }
                    fullname += ' (' + val.email + ' / ' + ' ' + val.phone + ')';
                    var data_pr = {id: val.id, text: fullname};
                    chk[val.id] = fullname;
                    result.push(data_pr);
                    if (typeof query.callback == 'undefined') {
                        fullname = fullname.replace(/(cows)/g, '<span class="smallcaps">$1</span>')
                        APP.add_customer_item_to_result({id: val.id, avatar_url: val.avatar_url, fullname: fullname});
                    }
                });
                var q_lastfirst = APP.db.from('customers').where('lastfirst', '^', _term);
                q_lastfirst.list(limit).done(function (objs) {
                    $.each(objs, function (index, val) {
                        if (POS_TRANSIENT.searching_term !== _term) return false;
                        if (typeof chk[val.id] == 'undefined') {
                            var fullname = [val.first_name, val.last_name];
                            fullname = fullname.join(' ').trim();
                            if (fullname == '') {
                                fullname = val.username;
                            }
                            fullname += ' (' + val.email + ' / ' + ' ' + val.phone + ')';
                            var data_pr = {id: val.id, text: fullname};
                            chk[val.id] = fullname;
                            result.push(data_pr);
                            if (typeof query.callback == 'undefined') {
                                APP.add_customer_item_to_result({
                                    id: val.id,
                                    avatar_url: val.avatar_url,
                                    fullname: fullname
                                });
                            }
                        }
                    });
                    var qq = APP.db.from('customers').where('email', '^', _term);
                    qq.list(limit).done(function (objs) {
                        var i = 0;
                        $.each(objs, function (index, val) {
                            if (POS_TRANSIENT.searching_term !== _term) return false;
                            if (typeof chk[val.id] == 'undefined') {
                                var fullname = [val.first_name, val.last_name]
                                var fullname = fullname.join(' ').trim();
                                if (fullname == '') {
                                    fullname = val.username;
                                }
                                fullname += ' (' + val.email + ' / ' + ' ' + val.phone + ')';
                                var data_pr = {id: val.id, text: fullname};
                                chk[val.id] = fullname;
                                result.push(data_pr);
                                if (typeof query.callback == 'undefined') {
                                    APP.add_customer_item_to_result({
                                        id: val.id,
                                        avatar_url: val.avatar_url,
                                        fullname: fullname
                                    });
                                }
                            }
                        });

                        var qqq = APP.db.from('customers').where('phone', '^', _term);
                        qqq.list(limit).done(function (objs) {
                            var i = 0;
                            $.each(objs, function (index, val) {
                                if (POS_TRANSIENT.searching_term !== _term) return false;
                                if (typeof chk[val.id] == 'undefined') {
                                    var fullname = [val.first_name, val.last_name]
                                    var fullname = fullname.join(' ').trim();
                                    if (fullname == '') {
                                        fullname = val.username;
                                    }
                                    fullname += ' (' + val.email + ' / ' + ' ' + val.phone + ')';
                                    var data_pr = {id: val.id, text: fullname};
                                    result.push(data_pr);
                                    if (typeof query.callback == 'undefined') {
                                        APP.add_customer_item_to_result({
                                            id: val.id,
                                            avatar_url: val.avatar_url,
                                            fullname: fullname
                                        });
                                    }
                                }
                            });
                            data.results = result;
                            if (typeof query.callback != 'undefined') {
                                query.callback(data);
                            }

                        });

                    });
                });
            });
        },
        //WC 3.0 search customer function
        search_customer_wc_3: function (query, callback) {
            $('#customer_search_result').html('');
            var term = query.term;
            var _term = '';
            if (term) {
                _term = POS_TRANSIENT.searching_term = term.toLowerCase();
            }
            var data = {results: []};
            var q = APP.db.from('customers').where('fullname', '^', _term);
            var limit = 10000;
            var result = [];
            var chk = {};
            q.list(limit).done(function (objs) {
                $.each(objs, function (index, val) {
                    if (POS_TRANSIENT.searching_term !== _term) return false;
                    var fullname = [val.first_name, val.last_name];
                    fullname = fullname.join(' ').trim();
                    if (fullname == '') {
                        fullname = val.username;
                    }
                    fullname += ' (' + val.email + ' / ' + ' ' + val.phone + ')';
                    var data_pr = {id: val.id, text: fullname};
                    chk[val.id] = fullname;
                    result.push(data_pr);
                    if (typeof callback == 'undefined') {
                        fullname = fullname.replace(/(cows)/g, '<span class="smallcaps">$1</span>')
                        APP.add_customer_item_to_result({id: val.id, avatar_url: val.avatar_url, fullname: fullname});
                    }
                });
                var q_lastfirst = APP.db.from('customers').where('lastfirst', '^', _term);
                q_lastfirst.list(limit).done(function (objs) {
                    $.each(objs, function (index, val) {
                        if (POS_TRANSIENT.searching_term !== _term) return false;
                        if (typeof chk[val.id] == 'undefined') {
                            var fullname = [val.first_name, val.last_name];
                            fullname = fullname.join(' ').trim();
                            if (fullname == '') {
                                fullname = val.username;
                            }
                            fullname += ' (' + val.email + ' / ' + ' ' + val.phone + ')';
                            var data_pr = {id: val.id, text: fullname};
                            chk[val.id] = fullname;
                            result.push(data_pr);
                            if (typeof callback == 'undefined') {
                                APP.add_customer_item_to_result({
                                    id: val.id,
                                    avatar_url: val.avatar_url,
                                    fullname: fullname
                                });
                            }
                        }
                    });
                    var qq = APP.db.from('customers').where('email', '^', _term);
                    qq.list(limit).done(function (objs) {
                        var i = 0;
                        $.each(objs, function (index, val) {
                            if (POS_TRANSIENT.searching_term !== _term) return false;
                            if (typeof chk[val.id] == 'undefined') {
                                var fullname = [val.first_name, val.last_name]
                                var fullname = fullname.join(' ').trim();
                                if (fullname == '') {
                                    fullname = val.username;
                                }
                                fullname += ' (' + val.email + ' / ' + ' ' + val.phone + ')';
                                var data_pr = {id: val.id, text: fullname};
                                chk[val.id] = fullname;
                                result.push(data_pr);
                                if (typeof callback == 'undefined') {
                                    APP.add_customer_item_to_result({
                                        id: val.id,
                                        avatar_url: val.avatar_url,
                                        fullname: fullname
                                    });
                                }
                            }
                        });

                        var qqq = APP.db.from('customers').where('phone', '^', _term);
                        qqq.list(limit).done(function (objs) {
                            var i = 0;
                            $.each(objs, function (index, val) {
                                if (POS_TRANSIENT.searching_term !== _term) return false;
                                if (typeof chk[val.id] == 'undefined') {
                                    var fullname = [val.first_name, val.last_name]
                                    var fullname = fullname.join(' ').trim();
                                    if (fullname == '') {
                                        fullname = val.username;
                                    }
                                    fullname += ' (' + val.email + ' / ' + ' ' + val.phone + ')';
                                    var data_pr = {id: val.id, text: fullname};
                                    result.push(data_pr);
                                    if (typeof callback == 'undefined') {
                                        APP.add_customer_item_to_result({
                                            id: val.id,
                                            avatar_url: val.avatar_url,
                                            fullname: fullname
                                        });
                                    }
                                }
                            });
                            data.results = result;
                            if (typeof callback != 'undefined') {
                                callback(data);
                            }
                        });
                    });
                });
            });
        },
        display_variation_price_sku: function (event, variation) {

            var price_html = pos_get_price_html(variation.data);
            var stock_quantity = parseInt(variation.data.stock_quantity);
            price = '<span class="price">' + price_html + '</span>';

            $('#selected-variation-data .selected-variation-price').html(price);
            $('#selected-variation-data .selected-variation-sku').html(variation.data.sku);

            if (!isNaN(stock_quantity)) {
                if (!variation.data.in_stock) {
                    $('#selected-variation-data .selected-variation-stock').html(pos_i18n[39]).closest('li').show();
                } else {
                    $('#selected-variation-data .selected-variation-stock').html(variation.data.stock_quantity + ' ' + pos_i18n[38]).closest('li').show();
                }
            } else {
                $('#selected-variation-data .selected-variation-stock').html('').closest('li').hide();
            }

            if (!$('#selected-variation-data').is(':visible')) {
                $('#selected-variation-data').slideToggle(0);
            }
        },
        ready: function () {

            Ladda.bind('#sync_data', {
                callback: function (instance) {
                    var progress = 0;
                    var interval = setInterval(function () {
                        progress = Math.min(progress + Math.random() * 0.1, 1);
                        instance.setProgress(progress);
                        if (progress === 1) {
                            instance.stop();
                            clearInterval(interval);
                        }
                    }, 200);
                }
            });
            setInterval(function () {
                if (APP.lastUpdate != 'null') {
                    jQuery('#last_sync_time').attr('title', APP.lastUpdate).timeago('updateFromDOM');
                }
            }, 1000);
            $('#sync_data').click(function () {
                var attr = $(this).attr('disabled');
                if (typeof attr === typeof undefined) {
                    APP.db.clear();
                    APP.sync_data(true);
                    location.reload();
                }
                return false;
            });
            if (wc_version >= 3) {
                $.fn.select2.amd.require([
                    'select2/data/array',
                    'select2/utils'
                ], function (ArrayData, Utils) {
                    function ProductData($element, options) {
                        ProductData.__super__.constructor.call(this, $element, options);
                    }

                    Utils.Extend(ProductData, ArrayData);

                    ProductData.prototype.query = function (params, callback) {
                        var term = params.term;
                        if (term) {
                            var _term = term.toLowerCase();
                        }
                        var result = [];
                        var title;
                        $.each(APP.tmp.products, function (index, o) {
                            if (typeof o.post_meta[wc_pos_params.scan_field] != 'undefined' && wc_pos_params.ready_to_scan !== 'no') {
                                if (typeof o.post_meta[wc_pos_params.scan_field] == 'object') {
                                    title = o.post_meta[wc_pos_params.scan_field][0].toLowerCase();
                                }
                            } else {
                                title = o.text.toLowerCase();
                            }
                            if (title.indexOf(_term) >= 0) {
                                result.push(o);
                            } else if (title !== o.text.toLowerCase()) { //If don't find in scan field check title
                                title = o.text.toLowerCase();
                                if (title.indexOf(_term) >= 0) {
                                    result.push(o);
                                }
                            }
                        });
                        callback({results: result});
                    };

                    $("#add_product_id").select2({
                        multiple: true,
                        dataAdapter: ProductData
                    }).change(function () {
                        var val = $('#add_product_id').val();
                        $("#add_product_id").html('');
                        if (val != '') {
                            val = JSON.parse(val);
                            var product_id = val.id;
                            var variation_id = ( typeof val.vid != 'undefined' ? val.vid : 0 );
                            var variation = ( typeof val.variation != 'undefined' ? val.variation : {} );

                            var quantity = wc_pos_params.decimal_quantity_value;
                            APP.addToCart(product_id, quantity, variation_id, variation);
                        }
                    });
                });
            } else {
                $('#add_product_id').select2({
                    minimumInputLength: 3,
                    multiple: true,

                    query: function (query) {
                        var term = query.term;
                        var _term = term.toLowerCase();
                        var result = [];
                        $.each(APP.tmp.products, function (index, o) {
                            var title = o.text.toLowerCase();
                            if (title.indexOf(_term) >= 0) {
                                result.push(o);
                            }
                        });
                        query.callback({results: result});
                    },

                }).change(function () {
                    var val = $('#add_product_id').val();
                    $('#add_product_id').select2('val', '', false);
                    if (val != '') {
                        val = JSON.parse(val);
                        var product_id = val.id;
                        var variation_id = ( typeof val.vid != 'undefined' ? val.vid : 0 );
                        var variation = ( typeof val.variation != 'undefined' ? val.variation : {} );

                        var quantity = wc_pos_params.decimal_quantity_value;
                        APP.addToCart(product_id, quantity, variation_id, variation);
                    }
                });
            }
            if ($('#customer_user').length) {
                if (wc_version >= 3) {
                    $.fn.select2.amd.require([
                        'select2/data/array',
                        'select2/utils'
                    ], function (ArrayData, Utils) {
                        function CustomerData($element, options) {
                            CustomerData.__super__.constructor.call(this, $element, options);
                        }

                        Utils.Extend(CustomerData, ArrayData);

                        CustomerData.prototype.query = function (params, callback) {
                            APP.search_customer_wc_3(params, callback);
                        };

                        $("#customer_user").select2({
                            minimumInputLength: 3,
                            multiple: true,
                            dataAdapter: CustomerData
                        }).change(function () {
                            var customer_id = $(this).val();
                            $("#customer_user").html('');
                            APP.setCustomer(customer_id, wc_pos_params.load_customer);
                        });
                    });
                } else {
                    $('#customer_user').select2({
                        minimumInputLength: 3,
                        multiple: false,
                        query: function (query) {
                            APP.search_customer(query);
                        }
                    }).change(function () {
                        var customer_id = $(this).val();
                        APP.setCustomer(customer_id, wc_pos_params.load_customer);
                    });
                }
            }

            if ($('#search_customer_to_register').length) {
                $('#search_customer_to_register').click(function (event) {
                    openModal('modal-search-customer');
                    $('#search-customer-input').focus();
                });
                $('#search-customer-input').on('keyup', function (event) {
                    if ($(this).val().length >= 3) {
                        APP.search_customer({term: $(this).val()});
                    } else {
                        $('#customer_search_result').html('');
                    }
                });

                $('#customer_search_result').on('click', '.user-item', function (event) {
                    var customer_id = $(this).data('id');
                    APP.setCustomer(customer_id, wc_pos_params.load_customer);
                    closeModal('modal-search-customer');
                    $('#customer_search_result').html('');
                    $('#search-customer-input').val('');
                });
            }

            if ($('.payment_method_cod').length) {
                $('.payment_method_cod').click(function (event) {
                    $('#amount_pay_cod').focus();
                });
            }


            if (pos_default_customer) {
                APP.setCustomer(pos_default_customer);
            }
            $('#wc-pos-customer-data').on('click', '.remove_customer_row', function () {
                APP.setGuest();
                return false;
            });
            $('body').on('click', '#clear_shipping', function (event) {
                CART.chosen_shipping_methods = {title: '', price: ''};
                CART.calculate_totals();
            });
            $('body').on('click', 'a.show_customer_popup', function (event) {
                var source = $('#tmpl-form-add-customer').html();
                var template = Handlebars.compile(source);
                var html = template(CUSTOMER);
                html = $(html);

                if (CUSTOMER.id > 0) {
                    html.find('#create_new_account').remove();
                } else if (CUSTOMER.create_account === true) {
                    html.find('#createaccount').prop('checked', 'checked');
                }

                $('#customer_details').html(html);
                $('.shipping_address #shipping_country').select2({width: '100%'});
                $('#modal-order_customer .nav-tab-wrapper a').first().trigger('click');
                wc_country_select_select2();
                openModal('modal-order_customer');
                jQuery(document).trigger('acf/setup_fields', [jQuery('#pos_custom_fields')]);
                $(document.body).trigger('wc-enhanced-select-init');
                if (sizeof(wc_country_select_params.allowed_countries) > 1) {
                    $('#shipping_country').val(CUSTOMER.shipping_address.country).trigger('change');
                    $('#billing_country').val(CUSTOMER.billing_address.country).trigger('change');
                }

                $('select#billing_state').val(CUSTOMER.billing_address.state).trigger('change');
                $('select#shipping_state').val(CUSTOMER.shipping_address.state).trigger('change');

                $.each(CUSTOMER.acf_fields, function (key, val) {
                    var a_el = $('#customer_details #pos_custom_fields #acf-field-' + key);
                    if (a_el.length) {
                        if (a_el.first().is(':radio') || a_el.first().is(':checkbox')) {
                            a_el.each(function (index, el) {
                                if ($(el).val() == val) {
                                    $(el).attr('checked', 'checked').trigger('change');
                                }
                            });
                        } else {
                            a_el.val(val).trigger('change');
                        }
                    }
                });

                $.each(CUSTOMER.additional_fields, function (key, val) {
                    var a_el = $('#customer_details #pos_additional_fields #' + key);
                    if (a_el.length) {
                        if (a_el.first().is(':radio') || a_el.first().is(':checkbox')) {
                            a_el.each(function (index, el) {
                                if ($(el).val() == val) {
                                    $(el).attr('checked', 'checked').trigger('change');
                                }
                            });
                        } else {
                            a_el.val(val).trigger('change');
                        }
                    }
                });
                $.each(CUSTOMER.custom_order_fields, function (key, val) {
                    var a_el = $('#customer_details #pos_order_fields #' + key);
                    if (a_el.length) {
                        if (a_el.first().is(':radio') || a_el.first().is(':checkbox')) {
                            a_el.each(function (index, el) {
                                if ($(el).val() == val) {
                                    $(el).attr('checked', 'checked').trigger('change');
                                }
                            });
                        } else {
                            a_el.val(val).trigger('change');
                        }
                    }
                });
                $.each(CUSTOMER.billing_address, function (key, val) {
                    var a_el = $('#customer_details #pos_billing_details #' + key);
                    if (a_el.length) {
                        if (a_el.first().is(':radio') || a_el.first().is(':checkbox')) {

                            a_el.each(function (index, el) {
                                if ($(el).val() == val) {
                                    $(el).attr('checked', 'checked').trigger('change');
                                }
                            });
                        } else {
                            a_el.val(val).trigger('change');
                        }
                    }
                });
                $.each(CUSTOMER.shipping_address, function (key, val) {
                    var a_el = $('#customer_details #pos_shipping_details #' + key);

                    if (a_el.length) {
                        if (a_el.first().is(':radio') || a_el.first().is(':checkbox')) {
                            a_el.each(function (index, el) {
                                if ($(el).val() == val) {
                                    $(el).attr('checked', 'checked').trigger('change');
                                }
                            });
                        } else {
                            a_el.val(val).trigger('change');
                        }
                    }
                });


                runTips();
                return false;
            });
            $('#add_customer_to_register').click(function (event) {
                var source = $('#tmpl-form-add-customer').html();
                var template = Handlebars.compile(source);
                var html = template({});
                $('#customer_details').html(html);
                $('#modal-order_customer .nav-tab-wrapper a').first().trigger('click');
                // $('#pos_billing_details #billing_country, .shipping_address #shipping_country').select2();
                openModal('modal-order_customer');
                wc_country_select_select2();
                jQuery(document).trigger('acf/setup_fields', [jQuery('#pos_custom_fields')]);
                if (sizeof(wc_country_select_params.allowed_countries) > 1) {
                    $('#shipping_country').val(CUSTOMER.default_country).trigger('change');
                    $('#billing_country').val(CUSTOMER.default_country).trigger('change');
                }

                $('select#billing_state').val(CUSTOMER.default_state).trigger('change');
                $('select#shipping_state').val(CUSTOMER.default_state).trigger('change');
                runTips();
            });
            $('body').on('click', '#billing-same-as-shipping', function (event) {
                if ($('.wc-address-validation-address-type[value="shipping"]').length >= 1) {
                    var postcode = $('.wc-address-validation-billing-field [name="wc_address_validation_postcode_lookup_postcode"]').val();
                    $('.wc-address-validation-shipping-field [name="wc_address_validation_postcode_lookup_postcode"]').val(postcode);
                    $('.shipping_address .wc-address-validation-shipping-field a').click();
                } else {
                    var ar = ['first_name', 'last_name', 'company', 'address_1', 'address_2', 'city', 'state', 'postcode'];
                    $('#shipping_country').val($('#billing_country').val());
                    $('#shipping_country').trigger('change');
                    $.each(ar, function (index, val) {
                        if ($('#billing_' + val).length && $('#shipping_' + val)) {
                            var v = $('#billing_' + val).val();
                            $('#shipping_' + val).val(v);
                        }
                    });
                }
                $('#shipping_state').trigger('change');
            });
            $('#edit_wc_pos_registers').on('click', '.add_grid_tile', function (event) {
                var product_id = $(this).data('id');
                var variation_id = $(this).data('varid');
                if (variation_id == 'undefined') {
                    variation_id = 0;
                }
                var quantity = wc_pos_params.decimal_quantity_value;
                if (product_id && product_id != '') {
                    APP.addToCart(product_id, quantity, variation_id);
                }
            });
            $('#save_customer').on('click', function () {
                var err = 0;
                if (jQuery('#billing_account_password').val() != jQuery('#billing_password_confirm').val()) {
                    APP.showNotice(pos_i18n[41], 'error');
                    err++;
                }
                $('#customer_details .woocommerce-billing-fields .validate-required input, #customer_details .woocommerce-billing-fields .validate-required select').each(function (index, el) {
                    if (err == 0) {
                        if ($(this).hasClass('select2-offscreen')) {
                            return;
                        }
                        ;
                        if ($(this).closest('.form-row').css('display') != 'none' && !$(this).closest('.select2-container').length) {

                            var val = $(this).val();
                            if (val == '') {
                                APP.showNotice(pos_i18n[15], 'error');
                                err++;
                            }
                        }
                    }
                });
                if (err > 0) {
                    return;
                }
                ;
                $('#customer_details .woocommerce-shipping-fields .validate-required input, #customer_details .woocommerce-shipping-fields .validate-required select').each(function (index, el) {
                    if (err == 0) {
                        if ($(this).hasClass('select2-offscreen')) {
                            return;
                        }
                        ;
                        if ($(this).closest('.form-row').css('display') != 'none' && !$(this).closest('.select2-container').length) {
                            var val = $(this).val();
                            if (val == '') {
                                APP.showNotice(pos_i18n[16], 'error');
                                err++;
                            }
                        }
                    }
                });
                if (err > 0) {
                    return;
                }
                ;
                $('#customer_details .woocommerce-additional-fields .validate-required input, #customer_details .woocommerce-additional-fields .validate-required select').each(function (index, el) {
                    if (err == 0) {
                        if ($(this).hasClass('select2-offscreen')) {
                            return;
                        }
                        ;
                        var val = $(this).val();
                        if (val == '' && !$(this).closest('.select2-container').length) {
                            APP.showNotice(pos_i18n[17], 'error');
                            err++;
                        }
                    }
                });
                if (err > 0) {
                    return;
                }
                ;
                $('#customer_details .woocommerce-custom-fields .validate-required input, #customer_details .woocommerce-custom-fields .validate-required select').each(function (index, el) {
                    if (err == 0) {
                        if ($(this).hasClass('select2-offscreen')) {
                            return;
                        }
                        ;
                        var val = $(this).val();
                        if (val == '' && !$(this).closest('.select2-container').length) {
                            APP.showNotice(pos_i18n[35], 'error');
                            err++;
                        }
                    }
                });
                if (err > 0) {
                    return;
                }
                ;
                if (err == 0) {
                    var new_customer = $('#customer_details_id').val() == '' ? true : false;
                    if (new_customer) {
                        CUSTOMER.reset();
                    }
                    var arr = ['account_username', 'account_password', 'country', 'first_name', 'last_name', 'company', 'address_1', 'address_2', 'city', 'state', 'postcode', 'email', 'phone'];
                    $.each(arr, function (index, key) {
                        var b_key = 'billing_' + key;
                        var s_key = 'shipping_' + key;
                        var b_el = $('#customer_details #' + b_key);
                        var s_el = $('#customer_details #' + s_key);
                        if (b_el.length) {
                            CUSTOMER.billing_address[key] = b_el.val();
                        }
                        if (s_el.length) {
                            CUSTOMER.shipping_address[key] = s_el.val();
                        }
                    });

                    var acf_fields = wc_pos_params.acf_fields;
                    $.each(acf_fields, function (index, key) {
                        if (key == '') return true;

                        var a_custom_f = $('#acf-' + key + '[data-field_name="' + key + '"]');
                        if (a_custom_f.length && a_custom_f.hasClass('field_type-relationship')) {
                            var _val = [];
                            var a_el = a_custom_f.find('.relationship_right li input');
                            a_el.each(function (index, el) {
                                _val.push($(el).val());
                            });
                            CUSTOMER.acf_fields[key] = _val;
                        } else if (a_custom_f.length && a_custom_f.hasClass('field_type-google_map')) {
                            var _val = {};
                            _val['address'] = a_custom_f.find('.input-address').val();
                            _val['lat'] = a_custom_f.find('.input-lat').val();
                            _val['lng'] = a_custom_f.find('.input-lng').val();
                            CUSTOMER.acf_fields[key] = _val;
                        } else if (a_custom_f.length && a_custom_f.hasClass('field_type-date_picker')) {
                            CUSTOMER.acf_fields[key] = a_custom_f.find('.input-alt').val();
                        } else {
                            var a_el_id = $('#customer_details #pos_custom_fields :input[id^="acf-field-' + key + '"]');
                            var a_el = $('#customer_details #pos_custom_fields :input#acf-field-' + key);
                            var a_el = a_el.length ? a_el : a_el_id;


                            if (a_el.length) {
                                if (a_el.first().is(':checkbox')) {
                                    var _val = [];
                                    a_el.filter(':checked').each(function (index, el) {
                                        _val.push($(el).val());
                                    });
                                    CUSTOMER.acf_fields[key] = _val;
                                } else if (a_el.first().is(':radio')) {
                                    CUSTOMER.acf_fields[key] = a_el.filter(':checked').val();
                                } else {
                                    CUSTOMER.acf_fields[key] = a_el.val();
                                }
                            }
                        }

                    });

                    var additional_fields = wc_pos_params.additional_fields;
                    $.each(additional_fields, function (index, key) {
                        var a_el_id = $('#customer_details #pos_additional_fields :input[id^="' + key + '"]');
                        var a_el = $('#customer_details #pos_additional_fields :input#' + key);
                        var a_el = a_el.length ? a_el : a_el_id;

                        if (a_el.length) {
                            if (a_el.first().is(':checkbox')) {
                                var _val = [];
                                a_el.filter(':checked').each(function (index, el) {
                                    _val.push($(el).val());
                                });
                                CUSTOMER.additional_fields[key] = _val;
                            } else if (a_el.first().is(':radio')) {
                                CUSTOMER.additional_fields[key] = a_el.filter(':checked').val();
                            } else {
                                CUSTOMER.additional_fields[key] = a_el.val();
                            }
                        }

                    });

                    var custom_order_fields = wc_pos_params.custom_order_fields;
                    $.each(custom_order_fields, function (index, key) {

                        var a_el_id = $('#customer_details #pos_order_fields :input[id^="' + key + '"]');
                        var a_el = $('#customer_details #pos_order_fields :input#' + key);
                        var a_el = a_el.length ? a_el : a_el_id;

                        if (a_el.length) {
                            if (a_el.first().is(':checkbox')) {
                                var _val = [];
                                a_el.filter(':checked').each(function (index, el) {
                                    _val.push($(el).val());
                                });
                                CUSTOMER.custom_order_fields[key] = _val;
                            } else if (a_el.first().is(':radio')) {
                                CUSTOMER.custom_order_fields[key] = a_el.filter(':checked').val();
                            } else {
                                CUSTOMER.custom_order_fields[key] = a_el.val();
                            }
                        }
                    });

                    $.each(wc_pos_params.a_billing_fields, function (index, key) {
                        var a_el_id = $('#customer_details #pos_billing_details :input[id^="' + key + '"]');
                        var a_el = $('#customer_details #pos_billing_details :input#' + key);
                        var a_el = a_el.length ? a_el : a_el_id;

                        if (a_el.length) {
                            if (a_el.first().is(':checkbox')) {
                                var _val = [];
                                a_el.filter(':checked').each(function (index, el) {
                                    _val.push($(el).val());
                                });
                                CUSTOMER.additional_fields[key] = _val;
                                CUSTOMER.billing_address[key] = _val;
                            } else if (a_el.first().is(':radio')) {
                                CUSTOMER.additional_fields[key] = a_el.filter(':checked').val();
                                CUSTOMER.billing_address[key] = a_el.filter(':checked').val();
                            } else {
                                CUSTOMER.additional_fields[key] = a_el.val();
                                CUSTOMER.billing_address[key] = a_el.val();
                            }
                        }

                    });
                    $.each(wc_pos_params.a_shipping_fields, function (index, key) {

                        var a_el_id = $('#customer_details #pos_shipping_details :input[id^="' + key + '"]');
                        var a_el = $('#customer_details #pos_shipping_details :input#' + key);
                        var a_el = a_el.length ? a_el : a_el_id;

                        if (a_el.length) {
                            if (a_el.first().is(':checkbox')) {
                                var _val = [];
                                a_el.filter(':checked').each(function (index, el) {
                                    _val.push($(el).val());
                                });
                                CUSTOMER.additional_fields[key] = _val;
                                CUSTOMER.billing_address[key] = _val;
                            } else if (a_el.first().is(':radio')) {
                                CUSTOMER.additional_fields[key] = a_el.filter(':checked').val();
                                CUSTOMER.billing_address[key] = a_el.filter(':checked').val();
                            } else {
                                CUSTOMER.additional_fields[key] = a_el.val();
                                CUSTOMER.billing_address[key] = a_el.val();
                            }
                        }
                    });

                    if (CUSTOMER.first_name == '') {
                        CUSTOMER.first_name = CUSTOMER.billing_address['first_name'];
                    }
                    if (CUSTOMER.last_name == '') {
                        CUSTOMER.last_name = CUSTOMER.billing_address['last_name'];
                    }
                    if (CUSTOMER.email == '') {
                        CUSTOMER.email = CUSTOMER.billing_address['email'];
                    }

                    var fullname = [CUSTOMER.first_name, CUSTOMER.last_name];
                    fullname = fullname.join(' ');

                    if (fullname == '') {
                        fullname = clone(CUSTOMER.username);
                    }
                    CUSTOMER.fullname = fullname;

                    if ($('#createaccount').is(':checked')) {
                        CUSTOMER.create_account = true;
                    }

                    CUSTOMER.customer = true;
                    CUSTOMER.points_n_rewards = 0;

                    var source = $('#tmpl-cart-customer-item').html();
                    var template = Handlebars.compile(source);
                    var html = template(CUSTOMER);
                    $('tbody#customer_items_list').html(html);
                    CART.calculate_totals();
                    closeModal('modal-order_customer');
                }
            });

            $('#wc-pos-register-data').on('click', '.remove_order_item', function () {
                var $el = $(this).closest('tr');
                var id = $el.attr('id');
                $el.remove();
                $('#item_note-' + id).remove();
                $('#tiptip_holder').hide().css({margin: '-100px 0 0 -100px'});
                CART.remove_cart_item(id);
                return false;
            });
            $('#modal-order_customer').on('click', '.nav-tab-wrapper a', function (event) {
                $('#modal-order_customer .nav-tab-wrapper a').removeClass('nav-tab-active');
                $(this).addClass('nav-tab-active');
                var id = $(this).attr('href');
                $('#customer_details .pos-customer-details-tab').hide();
                $('#customer_details ' + id).show();
                return false;
            });
            $('#custom-add-shipping-details').change(function (event) {
                if ($(this).is(':checked')) {
                    $('#custom-shipping-details-wrap').show();
                    wc_country_select_select2();
                    if (sizeof(wc_country_select_params.allowed_countries) > 1) {
                        $('#custom_shipping_country').val(CUSTOMER.shipping_address.country).trigger('change');
                    }
                    $('select#custom_shipping_state').val(CUSTOMER.shipping_address.state).trigger('change');

                } else {
                    $('#custom-shipping-details-wrap').hide();
                }
            });

            $('#add_shipping_to_register').click(function (event) {
                var modal = $(this).attr('data-modal');
                var source = $('#tmpl-custom-shipping-method-title-price').html();
                var template = Handlebars.compile(source);
                var html = template(CART.chosen_shipping_methods);
                $('#custom_shipping_table tbody').html(html);
                if ($('#custom_shipping_table tbody #custom_shipping_price').length > 0) {
                    $('#custom_shipping_table tbody #custom_shipping_price').keypad('destroy');
                    calculateShippingPrice();
                }
                if (CUSTOMER.customer) {
                    $('#custom-add-shipping-details').prop('checked', true).trigger('change');
                } else {
                    $('#custom-add-shipping-details').prop('checked', false).trigger('change');
                }

                var source = $('#tmpl-custom-shipping-shippingaddress').html();
                var template = Handlebars.compile(source);
                var html = template(CUSTOMER);
                $('#custom-shipping-shippingaddress').html(html);
                $('#custom-shipping-shippingaddress #custom_shipping_country').select2({width: '100%'});
                openModal(modal);
                wc_country_select_select2();
                if (sizeof(wc_country_select_params.allowed_countries) > 1) {
                    $('#custom_shipping_country').val(CUSTOMER.shipping_address.country).trigger('change');
                    $('select#custom_shipping_state').val(CUSTOMER.shipping_address.state).trigger('change');
                }

                runTips();
            });
            $('#add_custom_shipping').click(function (event) {
                var err = 0;
                if ($('#custom-add-shipping-details').is(':checked')) {
                    $('#custom-shipping-shippingaddress .validate-required input, #custom-shipping-shippingaddress .validate-required select').each(function (index, el) {
                        if ($(this).hasClass('select2-offscreen')) {
                            return;
                        }
                        ;
                        var val = $(this).val();
                        if (val == '' && !$(this).closest('.select2-container').length) {
                            APP.showNotice(pos_i18n[16], 'error');
                            err++;
                            return false;
                        }
                    });
                    if (err > 0) return;
                }

                $('#custom_shipping_title, #custom_shipping_price').each(function (index, el) {
                    var val = $(this).val();
                    if (val == '' && err == 0) {
                        APP.showNotice(pos_i18n[18 + index], 'error');
                        err++;
                        return false;
                    }
                });
                if (err == 0) {
                    var arr = ['country', 'first_name', 'last_name', 'company', 'address_1', 'address_2', 'city', 'state', 'postcode'];
                    $.each(arr, function (index, key) {
                        var s_key = 'custom_shipping_' + key;
                        var s_el = $('#custom-shipping-shippingaddress #' + s_key);
                        if (s_el.length) {
                            CUSTOMER.shipping_address[key] = s_el.val();
                        }
                    });
                    var price = $('#custom_shipping_price').val();
                    CART.chosen_shipping_methods = {
                        title: $('#custom_shipping_title').val(),
                        price: max(0, price),
                    };
                    CART.calculate_totals();
                    closeModal('modal-add_custom_shipping');
                }
            });

            $('#add_custom_product_meta').click(function () {
                var meta = [{meta_key: '', meta_v: ''}];
                var source = $('#tmpl-add-custom-item-meta').html();
                var template = Handlebars.compile(source);
                var html = template(meta);
                $('#product_custom_meta_table tbody').html(html);
                $('#custom_product_meta_table tbody').append(html);
                $('#custom_product_meta_table, #custom_product_meta_label').show();
                runTips();
            });


            if (wc_pos_params.instant_quantity_keypad == 'yes') {
                runQuantityKeypad($('.inline_quantity'), 'full', false);
            } else if (wc_pos_params.instant_quantity == 'yes') {
                runQuantityKeypad($('.inline_quantity'), 'min', false);
            }

            $('body').on('update_variation_values', '#missing-attributes-select', function (event, variations) {
                $variation_form = $(this);

                // Loop through selects and disable/enable options based on selections
                $variation_form.find('select').each(function (index, el) {
                    var current_attr_name, current_attr_select = $(el);

                    // Reset options
                    if (!current_attr_select.data('attribute_options')) {
                        current_attr_select.data('attribute_options', current_attr_select.find('option:gt(0)').get());
                    }

                    current_attr_select.find('option:gt(0)').remove();
                    current_attr_select.append(current_attr_select.data('attribute_options'));
                    current_attr_select.find('option:gt(0)').removeClass('attached');
                    current_attr_select.find('option:gt(0)').removeClass('enabled');
                    current_attr_select.find('option:gt(0)').removeAttr('disabled');

                    // Get name from data-attribute_name, or from input name if it doesn't exist
                    current_attr_name = current_attr_select.data('taxonomy');

                    // Loop through variations
                    for (var num in variations) {

                        if (typeof( variations[num] ) !== 'undefined') {

                            var attributes = variations[num].attributes;

                            for (var attr_name in attributes) {
                                if (attributes.hasOwnProperty(attr_name)) {
                                    var attr_val = attributes[attr_name];

                                    if (attr_name === current_attr_name) {

                                        var variation_active = '';

                                        if (variations[num].variation_is_active) {
                                            variation_active = 'enabled';
                                        }

                                        if (attr_val) {

                                            // Decode entities
                                            attr_val = $('<div/>').html(attr_val).text();

                                            // Add slashes
                                            attr_val = attr_val.replace(/'/g, '\\\'');
                                            attr_val = attr_val.replace(/"/g, '\\\"');

                                            // Compare the meerkat
                                            current_attr_select.find('option[value="' + attr_val + '"]').addClass('attached ' + variation_active);

                                        } else {

                                            current_attr_select.find('option:gt(0)').addClass('attached ' + variation_active);

                                        }
                                    }
                                }
                            }
                        }
                    }

                    // Detach unattached
                    current_attr_select.find('option:gt(0):not(.attached)').remove();

                    // Grey out disabled
                    current_attr_select.find('option:gt(0):not(.enabled)').attr('disabled', 'disabled');
                });

            });


            $('body').on('found_variation', '#missing-attributes-select', this.display_variation_price_sku);
            $('body').on('check_variations', '#missing-attributes-select', function (event, exclude, focus) {

                var all_attributes_chosen = true,
                    some_attributes_chosen = false,
                    current_settings = {},
                    $form = $(this),
                    product_id = parseInt(APP.tmp.product_item.product_id),
                    $product_variations = APP.tmp.product_item.product_variations;


                $form.find('select').each(function () {
                    var attribute_name = $(this).data('taxonomy');

                    if ($(this).val().length === 0) {
                        all_attributes_chosen = false;
                    } else {
                        some_attributes_chosen = true;
                    }

                    if (exclude && attribute_name === exclude) {
                        all_attributes_chosen = false;
                        current_settings[attribute_name] = '';
                    } else {
                        // Add to settings array
                        current_settings[attribute_name] = $(this).val();
                    }
                });


                var matching_variations = APP.find_matching_variations($product_variations, current_settings);

                if (all_attributes_chosen) {

                    var variation = matching_variations.shift();

                    if (variation) {
                        APP.tmp.product_item.variation_id = variation.variation_id;
                        $form.trigger('found_variation', [variation]);
                    } else {
                        if ($('#selected-variation-data').is(':visible')) {
                            $('#selected-variation-data').slideToggle(0);
                        }
                        // Nothing found - reset fields
                        $form.find('select').val('');

                        if (!focus) {
                            $form.trigger('reset_data');
                        }
                    }

                } else {

                    $form.trigger('update_variation_values', [matching_variations]);
                }

            });
            $('body').on('click', '#reset_selected_variation', function () {
                $('#missing-attributes-select select').val('').first().trigger('change');
                return false;
            });
            $('body').on('change', '#missing-attributes-select select', function () {

                var $form = $(this).closest('#missing-attributes-select');

                var all_attributes_chosen = true,
                    some_attributes_chosen = false;

                $form.find('select').each(function () {

                    if ($(this).val().length === 0) {
                        all_attributes_chosen = false;
                    } else {
                        some_attributes_chosen = true;
                    }

                });
                if (!all_attributes_chosen && $('#selected-variation-data').is(':visible')) {
                    $('#selected-variation-data').slideToggle(0);
                }

                if (some_attributes_chosen) {
                    if (!$('#reset_selected_variation').is(':visible')) {
                        $('#reset_selected_variation').slideToggle(0);
                    }
                } else {
                    if ($('#reset_selected_variation').is(':visible')) {
                        $('#reset_selected_variation').slideToggle(0);
                    }
                }

                $form.trigger('check_variations', ['', false]);
                $(this).blur();

            });
            $('body').on('focusin touchstart', '#missing-attributes-select select', function () {

                $form = $(this).closest('#missing-attributes-select');
                $form.trigger('check_variations', [$(this).data('taxonomy'), true]);

            });
            $('.product-add-btn').click(function (event) {

                var $parent = $(this).closest('.md-modal');
                var product_id = APP.tmp.product_item.product_id;
                var quantity = APP.tmp.product_item.quantity;

                if ($parent.find('.inline_quantity').length) {
                    quantity = $parent.find('.keypad-keyentry').val();
                    $parent.find('.keypad-keyentry').val(1);
                    $parent.find('.keypad-inpval').text(1);
                }
                if (quantity == '') {
                    quantity = wc_pos_params.decimal_quantity_value;
                }

                if (wc_pos_params.decimal_quantity == 'yes') {
                    //quantity = parseFloat(quantity).toFixed(3);
                    quantity = parseFloat(quantity);
                } else {
                    quantity = parseInt(quantity);
                }
                if (quantity <= 0) {
                    quantity = wc_pos_params.decimal_quantity_value;
                }

                var valid = wp.hooks.applyFilters('wc_pos_before_add_to_cart_validation', true, APP.tmp.product_item.adding_to_cart, product_id, quantity, APP.tmp.product_item.variation_id, APP.tmp.product_item.variation);

                if ($parent.find('#missing-attributes-select table').length) {
                    $parent.find('#missing-attributes-select table select').each(function (index, el) {
                        var taxonomy = $(this).data('taxonomy');
                        var value = $(this).val();
                        if (value == '') {
                            valid = false;
                            return;
                        } else {
                            APP.tmp.product_item.variation[taxonomy] = value;
                        }
                    });
                }

                if (valid === true) {
                    APP.addToCart(product_id, quantity, APP.tmp.product_item.variation_id, APP.tmp.product_item.variation, APP.tmp.product_item.cart_item_data);
                    var modalid = $parent.attr('id');
                    closeModal(modalid);
                } else {
                    APP.showNotice(pos_i18n[7], 'error');
                }

            });

            $('.wc_pos_show_tiles').on('click', function () {
                $('#wc-pos-register-grids').css('visibility', 'visible');
            });
            $('.close_product_grids').on('click', function () {
                $('#wc-pos-register-grids').css('visibility', 'hidden');
            });
            $('.wc_pos_register_void').on('click', function () {
                var args = {
                    content: $('#tmpl-confirm-void-register').html(),
                    confirm: APP.voidRegister
                };
                openConfirm(args);

            });
            $('#wc-pos-register-data').on('click', '.add_custom_meta', function () {
                var item_key = $(this).closest('tr').attr('id');
                var cart_contents = CART.cart_contents;

                if (typeof cart_contents[item_key] != 'undefined') {
                    var product = cart_contents[item_key];
                    var variation = product.variation;
                    var meta = [];
                    if (Object.size(variation) === 0) {
                        meta.push({meta_key: '', meta_v: ''});
                    } else {
                        $.each(variation, function (meta_key, meta_v) {
                            var _meta = {'meta_key': meta_key, 'meta_v': meta_v};
                            meta.push(_meta);
                        });
                    }

                    var source = $('#tmpl-add-custom-item-meta').html();
                    var template = Handlebars.compile(source);
                    var html = template(meta);
                    $('#product_custom_meta_table tbody').html(html);
                    $('#add_custom_meta_product_id').val(item_key);

                    var item_title = product.data['title'];
                    var original_title = product.data['title'];
                    if (typeof product.data['original_title'] == 'undefined') {
                        CART.cart_contents[item_key]['data']['original_title'] = clone(product.data['title']);
                    } else {
                        original_title = product.data['original_title'];
                    }
                    $('#original_product_title').html(original_title);
                    $('#product_new_custom_title').val(item_title);

                    var is_taxable = product.data['taxable'];
                    var tax_class = product.data['tax_class'];

                    if (product.variation_id > 0) {
                        is_taxable = product.v_data['taxable'];
                        tax_class = product.v_data['tax_class'];
                    }

                    $('#product_new_is_taxable').prop('checked', is_taxable).trigger('change');
                    $('#product_new_tax_class').val(tax_class);

                    openModal('modal-add_product_custom_meta');
                    runTips();
                }
                return false;
            });
            $('#save_product_custom_meta').click(function () {
                var modalid = $(this).closest('div.md-modal').attr('id');
                var item_key = $('#add_custom_meta_product_id').val();
                var cart = CART.get_cart();
                if (typeof cart[item_key] != 'undefined') {
                    var title = $('#product_new_custom_title').val();
                    var variation = {};
                    var meta = '';
                    $('tr#' + item_key + ' td.name span > .product_title').html(title);
                    cart[item_key]['data']['title'] = title;

                    var is_taxable = $('#product_new_is_taxable').is(':checked');
                    var tax_class = $('#product_new_tax_class').val();
                    var _key = 'data';
                    if (cart[item_key].variation_id > 0) {
                        _key = 'v_data';
                    }
                    cart[item_key][_key]['taxable'] = is_taxable;
                    if (is_taxable) {
                        cart[item_key][_key]['tax_status'] = 'taxable';
                    } else {
                        cart[item_key][_key]['tax_status'] = 'none';
                    }
                    cart[item_key][_key]['tax_class'] = tax_class;
                    $('#product_custom_meta_table tbody tr').each(function (index, el) {
                        var meta_label = $(this).find('.meta_label_value').val();
                        var meta_attribute = $(this).find('.meta_attribute_value').val();
                        if (meta_label != '' && meta_attribute != '') {
                            variation[meta_label] = meta_attribute;
                            meta += '<li><span class="meta_label">' + meta_label + '</span><span class="meta_value">' + meta_attribute + '</span></li>';
                        }
                    });
                    cart[item_key]['variation'] = variation;
                    if (meta != '') {
                        meta = '<ul class="display_meta">' + meta + '</ul>';
                    }
                    var display_meta = $('tr#' + item_key + ' td.name .display_meta');
                    if (display_meta.length) {
                        display_meta.replaceWith(meta);
                    } else {
                        $('tr#' + item_key + ' td.name .view').append(meta);
                    }

                    wp.hooks.doAction('wc_pos_save_product_custom_meta', item_key);
                    CART.calculate_totals();
                }
                $('#add_custom_meta_product_id').val('');
                closeModal(modalid);
            });

            $('#product_new_is_taxable').change(function (e) {
                if (!$(this).is(':checked')) {
                    $('#product_new_tax_class').attr('disabled', 'disabled');
                } else {
                    $('#product_new_tax_class').removeAttr('disabled');
                }
            });
            $('#add_product_custom_meta').click(function () {
                var meta = [{meta_key: '', meta_v: ''}];
                var source = $('#tmpl-add-custom-item-meta').html();
                var template = Handlebars.compile(source);
                var html = template(meta);
                $('#product_custom_meta_table tbody').append(html);
                runTips();
            });
            $('body').on('click', '.remove_custom_product_meta', function () {
                var $tbody = $(this).closest('tbody');
                var id = $(this).closest('table').attr('id');
                $(this).closest('tr').remove();
                var count = $tbody.find('tr').length;

                if (!count) {
                    if (id == 'custom_product_meta_table') {
                        $('#custom_product_meta_label, #custom_product_meta_table').hide();
                    } else {
                        var meta = [{meta_key: '', meta_v: ''}];
                        var source = $('#tmpl-add-custom-item-meta').html();
                        var template = Handlebars.compile(source);
                        var html = template(meta);
                        $tbody.append(html);
                        runTips();
                    }
                }
                return false;
            });
            $('#add_custom_product').click(function () {
                var err = 0;
                $('#custom_product_title, #custom_product_price, #custom_product_quantity').each(function (index, el) {
                    if ($(this).val() == '') {
                        APP.showNotice(pos_i18n[20 + index], 'error');
                        err++;
                        return false;
                    }
                });
                if (err > 0)
                    return false;

                var adding_to_cart = clone(pos_custom_product);
                adding_to_cart.title = $('#custom_product_table input#custom_product_title').val();
                adding_to_cart.price = $('#custom_product_table input#custom_product_price').val();
                adding_to_cart.regular_price = adding_to_cart.price;

                var quantity = parseInt($('#custom_product_table input#custom_product_quantity').val());
                if (wc_pos_params.decimal_quantity == 'yes') {
                    quantity = parseFloat($('#custom_product_table input#custom_product_quantity').val());
                }
                var variation = {};

                $('#custom_product_meta_table tbody tr').each(function (index, el) {
                    var meta_label = $(el).find('.meta_label_value').val();
                    var meta_attribute = $(el).find('.meta_attribute_value').val();
                    variation[meta_label] = meta_attribute;
                });

                CART.addToCart(adding_to_cart, adding_to_cart.id, quantity, 0, variation);
                closeModal('modal-add_custom_product');

            });
            $('#add_product_to_register').click(function (event) {
                $('#custom_product_meta_label, #custom_product_meta_table').hide();
                $('#custom_product_meta_table tbody').html('');
                $('#custom_product_title, #custom_product_price, #custom_product_quantity').val('');
                $('#custom_product_quantity').val(1);
                openModal('modal-add_custom_product');
                $('#custom_product_title').focus();
            });
            $('.wc_pos_register_notes').on('click', function () {
                var customer_note = CART.customer_note;
                $('#order_comments').val(customer_note);
                openModal('modal-order_comments');
                $('#order_comments').focus();
            });
            $('#save_order_comments').on('click', function () {
                var note = $('#order_comments').val();
                if (CART.customer_note == '' && note != '') {
                    APP.showNotice(pos_i18n[30], 'success');
                } else if (CART.customer_note != '' && note == '') {
                    APP.showNotice(pos_i18n[32], 'success');
                } else if (CART.customer_note != '' && note != '') {
                    APP.showNotice(pos_i18n[31], 'success');
                }
                CART.customer_note = note;
                if (typeof POS_TRANSIENT.save_order != 'undefined' && POS_TRANSIENT.save_order === true) {
                    APP.createOrder(false);
                }
                closeModal('modal-order_comments');
                $('.wc_pos_register_pay ').click();
            });
            $('.close-order-comments').on('click', function () {
                POS_TRANSIENT.save_order = false;
            });
            $('#save_order_discount, #inline_order_discount .keypad-close').click(function (e) {
                var $keyentry = $('#inline_order_discount .keypad-keyentry');
                var discount_prev = $keyentry.val();
                if (!discount_prev) {
                    discount_prev = $('.keypad-discount_val2').text();
                }
                var amount = parseFloat(discount_prev);
                var symbol = $('#order_discount_symbol').val();
                if (symbol == 'percent_symbol') {
                    var type = 'percent';
                } else {
                    var type = 'fixed_cart';
                }
                CART.add_custom_discount(amount, type);
                closeModal('modal-order_discount');
                return false;
            });
            $('#apply_coupon_btn').click(function (e) {
                var coupon_code = $('#coupon_code').val();
                CART.add_discount(coupon_code.trim());
                $('#coupon_code').val('');
                return false;
            });
            $('#coupon_code').keypress(function (event) {
                if (event.which == 13) {
                    var coupon_code = $('#coupon_code').val();
                    CART.add_discount(coupon_code.trim());
                    $('#coupon_code').val('');
                    return false;
                }
            });
            $('#retrieve_sales').click(function () {
                retrieve_sales();
                return false;
            });
            $('#btn_retrieve_from').click(function () {
                var register_id = $('#bulk-action-retrieve_sales').val();
                var register_name = $('#bulk-action-retrieve_sales option:selected').data('name');
                retrieve_sales(register_id, register_name);
                return false;
            });
            $('#orders-search-submit').click(function () {
                var register_id = $('#bulk-action-retrieve_sales').val();
                var register_name = $('#bulk-action-retrieve_sales option:selected').data('name');
                var search = $('#orders-search-input').val();
                retrieve_sales(register_id, register_name, search);
                return false;
            });
            $('#modal-retrieve_sales').on('keypress', '#orders-search-input', function (e) {
                if (e.which == 13) {
                    var register_id = $('#bulk-action-retrieve_sales').val();
                    var register_name = $('#bulk-action-retrieve_sales option:selected').data('name');
                    var search = $('#orders-search-input').val();
                    retrieve_sales(register_id, register_name, search);
                    return false;
                }
            });
            $('#modal-retrieve_sales').on('keypress', '#current-page-selector', function (e) {
                if (e.which == 13) {
                    var count = parseInt($(this).data('count'));
                    var reg_id = $(this).data('reg_id');
                    var page = parseInt($(this).val());
                    var max_c = Math.ceil(count / 20);
                    if (page > max_c) {
                        page = max_c;
                    } else if (page <= 0) {
                        page = 1;
                    }
                    APP.getOrdersListContent({count: count, currentpage: page, reg_id: reg_id})
                    return false;
                }
            });
            $(document.body).on('click', '.show_order_items', function () {
                $(this).closest('td').find('table').toggle();
                return false;
            });
            $(document.body).on('click', '.load_order_data', function () {
                var order_id = parseInt($(this).attr('href'));
                APP.loadOrder(order_id);
                closeModal('modal-retrieve_sales');
                return false;
            });

            $(document.body).on('click', 'a.reprint_receipts', function () {
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

            $('.wc_pos_register_save').on('click', function () {
                if (CART.is_empty()) {
                    APP.showNotice(pos_i18n[9], 'error');
                    return false;
                } else if (CART.customer_note == '' && typeof note_request !== 'undefined' && note_request == 1) {
                    openModal('modal-order_comments');
                    $('#order_comments').focus();
                    POS_TRANSIENT.save_order = true;
                } else {
                    APP.createOrder(false);
                }
            });
            $('#wc-pos-actions').on('click', '.wc_pos_register_discount', function () {
                openModal('modal-order_discount');
            });
            $('#wc-pos-actions').on('click', '.wc_pos_register_coupon', function () {
                openModal('modal-order_coupon');
            });
            $('#wc-pos-actions').on('click', '.wc_pos_register_custom_fee', function () {
                openModal('modal-add_custom_fee');
                $('#fee-name').focus();
            });
            $('.wc_pos_register_pay').on('click', function () {
                $('#less-amount-notice').hide();
                var cart_total = CART.total;
                if (CART.is_empty()) {
                    APP.showNotice(pos_i18n[9], 'error');
                    return false;
                } else if (!wc_pos_params.guest_checkout && !$('#customer_items_list tr').data('customer_id')) {
                    APP.showNotice(pos_i18n[42], 'error');
                    return false;
                } else {
                    $('#modal-order_payment input.select_payment_method').removeAttr('disabled');
                    $('#modal-order_payment .media-menu a').first().click();

                    if (!$('#payment_switch_wrap .bootstrap-switch-container').length)
                        $('.payment_switch').bootstrapSwitch();

                    if ($('#pos_chip_pin_order_id').length) {
                        var chip_pin_order_id = pos_register_data.prefix + String(pos_register_data.order_id) + pos_register_data.suffix;
                        if (typeof POS_TRANSIENT.order_id != 'undefined' && POS_TRANSIENT.order_id > 0) {
                            chip_pin_order_id = pos_register_data.prefix + String(POS_TRANSIENT.order_id) + pos_register_data.suffix;
                        }
                        $('#pos_chip_pin_order_id').text(chip_pin_order_id);
                    }

                    $('#amount_pay_cod, #amount_change_cod').val('');

                    var round_total = cart_total;
                    var h = cart_total % wc_pos_params.wc_pos_rounding_value;
                    if (wc_pos_params.wc_pos_rounding) {
                        if (h >= wc_pos_params.wc_pos_rounding_value / 2) {
                            round_total = cart_total - h + parseFloat(wc_pos_params.wc_pos_rounding_value);
                        } else {
                            round_total = cart_total - h;
                        }
                    }
                    if (wc_pos_params.wc_pos_rounding) {
                        CART.total = round_total;
                        $('#show_total_amt_inp').val(round_total);
                        var total_text = $('#show_total_amt .amount').text();
                        var old_value = total_text.replace(/[0-9.,]+/, cart_total.toFixed(2));
                        var new_value = total_text.replace(/[0-9.,]+/, round_total.toFixed(2));
                        $('#show_total_amt .amount').text(new_value);
                        $('.payment_methods').on('click', function () {
                            var bind = $(this).data('bind');
                            if (bind != 'cod') {
                                $('#show_total_amt_inp').val(cart_total);
                                $('#show_total_amt .amount').text(old_value);
                                CART.total = cart_total;
                            } else {
                                $('#show_total_amt_inp').val(round_total);
                                $('#show_total_amt .amount').text(new_value);
                                CART.total = round_total;
                            }
                        });
                    } else {
                        $('#show_total_amt_inp').val(CART.total);
                    }
                    if (CART.customer_note == '' && typeof note_request !== 'undefined' && note_request == 2) {
                        openModal('modal-order_comments');
                        $('#order_comments').focus();
                        return;
                    }
                    $('#payment_switch').bootstrapSwitch('state', print_receipt);
                    var _email_receipt = false;
                    switch (email_receipt) {
                        case 1:
                            _email_receipt = true;
                            break;
                        case 2:
                            _email_receipt = !CUSTOMER.customer ? false : true;
                            break;
                    }
                    $('#payment_email_receipt').bootstrapSwitch('state', _email_receipt);
                    jQuery('#modal-order_payment .payment_method_cod .keypad-clear').click();
                    openModal('modal-order_payment');
                }
            });
            $('#modal-order_payment').on('click', 'input.go_payment', function () {
                var selected_pm = $('.select_payment_method:checked').val();

                if (selected_pm == '' || selected_pm == undefined) {
                    APP.showNotice(pos_i18n[10], 'error');
                    return false;
                }
                var total_amount = parseFloat($("#show_total_amt_inp").val());
                var amount_pay = parseFloat($('#amount_pay_cod').val());

                if (( $('#amount_pay_cod').val() == '' || parseFloat(amount_pay.toFixed(2)) < parseFloat(total_amount.toFixed(2)) ) && selected_pm == 'cod' && !$('#less-amount-notice').data('approve')) {
                    var difference = total_amount.toFixed(2) - amount_pay.toFixed(2);
                    jQuery('#amount_change_cod').val(-difference.toFixed(2)).addClass('error');
                    $('#less-amount-notice').fadeIn();
                    //APP.showNotice(pos_i18n[11], 'error');
                    return false;
                } else {
                    jQuery('#amount_change_cod').removeClass('error');
                    var res = ADDONS.validatePayment(selected_pm);
                    if (!res) {
                        APP.showNotice(pos_i18n[34], 'error');
                        return false;
                    }
                }
                if ($('#payment_email_receipt').bootstrapSwitch('state') && CUSTOMER.billing_address.email == '') {
                    args = {
                        content: $('#tmpl-prompt-email-receipt').html(),
                        cancel: function (answer) {
                            APP.createOrder(true);
                        },
                        confirm: function (answer) {
                            if (answer != '') {
                                CUSTOMER.additional_fields['pos_payment_email_receipt'] = answer;
                            }
                            APP.createOrder(true);
                        }
                    };
                    openPromt(args);
                } else {
                    if ($('#payment_email_receipt').bootstrapSwitch('state') && CUSTOMER.billing_address.email != '') {
                        CUSTOMER.additional_fields['pos_payment_email_receipt'] = CUSTOMER.billing_address.email;
                    }
                    APP.createOrder(true);
                }
            });
            $('#lock_register').click(function (event) {
                $('#unlock_password').val('');
                openModal('modal-lock-screen');
                APP.setCookie('pos_lockScreen', 'yes', 30);
                return false;
            });
            $('#unlock_button').click(function (event) {
                unlockScreen();
                return false;
            });
            $('#unlock_password').keypress(function (e) {
                if (e.which == 13) {
                    unlockScreen();
                }
            });
            $('#edit_wc_pos_registers').on('click', '.span_clear_order_coupon', function (e) {
                var $row = $(this).closest('tr');
                var coupon_code = $row.data('coupon');
                if (CART.remove_coupon(coupon_code, true)) {
                    $row.remove();
                }

            });

            $('#edit_wc_pos_registers').on('click', '.remove-fee', function (e) {
                var $row = $(this).closest('tr');
                var fee = $row.data('fee');
                if (CART.remove_fee(fee)) {
                    $row.remove();
                }
            });

            $(document.body).on('change', '#product_type', function () {
                var type = $(this).val();
            });

            if (wc_pos_params.ready_to_scan == 'yes') {
                $(document).anysearch({
                    searchSlider: false,
                    //05.02.2018 - twice scanning
                    /*isBarcode: function (barcode) {
                     if (!$('.md-modal.md-show').length) {
                     searchProduct(barcode);
                     }
                     },*/
                    searchFunc: function (search) {
                        if (!$('.md-modal.md-show').length) {
                            searchProduct(search);
                        }
                    },
                });
            }

            $('#order_items_list').on('change', '.product_price', function (e) {
                changeProductPrice($(this));
            });
            $('#order_items_list').on('keyup', '.product_price', function (e) {
                changeProductPrice($(this));
            });

            $('#edit_wc_pos_registers').css('visibility', 'visible');
            runTips();
            ADDONS.init();
            $('#modal-1, .md-overlay-logo').remove();
            lockScreen();
        }
    };

    function lockScreen() {
        var lock_screen = APP.getCookie('pos_lockScreen');
        if (wc_pos_params.lock_screen && lock_screen == 'yes') {
            openModal('modal-lock-screen');
            APP.setCookie('pos_lockScreen', 'yes', 30);
        }
    }

    function unlockScreen() {
        var pwd = $('#unlock_password').val();
        if (pwd == '') {
            toastr.error(pos_i18n[27]);
        } else {

            if (md5(pwd) != wc_pos_params.unlock_pass) {
                toastr.error(pos_i18n[28]);
            } else {
                closeModal('modal-lock-screen');
                APP.setCookie('pos_lockScreen', '', 30);
            }
        }
        $('#unlock_password').val('');
    }

    resizeCart = function () {  //TODO: need reworking this hardcode solution
        var h = $('#wc-pos-register-data').height();
        var sh = $('.wc_pos_register_subtotals').height();
        var lh = $('.woocommerce_order_items.labels').height();
        var h_cor = 0;
        if (sh !== subtotals_height) {
            subtotals_height = sh;
            h_cor = 8.5;
        }
        if (total_height === 0) {
            total_height = h;
            h_cor = 8.5;
        }
        if (total_height !== h) {
            if (total_height > h) {
                if (h_cor === 8.5) {
                    h_cor = 17;
                } else {
                    h_cor = 8.5;
                }
            }
            h = total_height;
        }
        $('div#order_items_list-wrapper').height(h - sh - lh - h_cor);
    };
    function resizeGrid() {
        var h = $('#wc-pos-register-data').height();
        var sub_h = $('.wc_pos_register_subtotals').height();
        var th = $('#order_items_th').height();
        if (pos_grid.second_column_layout == 'product_grids') {
            $("#grid_layout_cycle").hide();
            var h = parseFloat($('#wc-pos-register-grids').height()) - 39;
            var hh = 100;
            if (pos_grid.tile_layout == 'image_title_price') {
                hh = 123;
            }
            var _int = parseInt(h / hh);
            var _round = Math.round(h / hh);
            var count = _int * 5;

            if (h / hh >= (_int + 0.7)) {
                var count = _round * 5;
            }

            if ($('#grid_layout_cycle').length) {
                $('#grid_layout_cycle').height(h);
                $('#grid_layout_cycle').category_cycle('destroy');
                $('#grid_layout_cycle').category_cycle({
                    count: count,
                    hierarchy: pos_grid.term_relationships.hierarchy,
                    relationships: pos_grid.term_relationships.relationships,
                    parents: pos_grid.term_relationships.parents,
                    archive_display: pos_grid.category_archive_display,
                    breadcrumbs: $('#wc-pos-register-grids .hndle'),
                    breadcrumbs_h: $('#wc-pos-register-grids-title'),
                });
            }
            $("#grid_layout_cycle").show();

        }
    }

    function retrieve_sales(reg_id, reg_name, search) {
        if (typeof reg_id == 'undefined') {
            reg_id = 'all';
            reg_name = pos_i18n[26];
        }
        if (typeof search == 'undefined') {
            search = '';
        }
        $('#modal-retrieve_sales h3 i').text(reg_name);
        $('#bulk-action-retrieve_sales').val(reg_id);
        $('#orders-search-input').val(search);

        $('#retrieve_sales_popup_inner').html('');
        $('#modal-retrieve_sales .wrap-button').html('');

        $('#retrieve-sales-wrapper .box_content').hide();
        $('#retrieve-sales-wrapper').block({
            message: null,
            overlayCSS: {
                background: '#fff',
                opacity: 0.6
            }
        });
        openModal('modal-retrieve_sales');
        $.when(APP.getServerOrdersCount(reg_id, search)).then(function (result) {
            if (parseInt(result.count) > 0) {
                var opt = {count: result.count, currentpage: 1, reg_id: reg_id, search: search};

                APP.getOrdersListContent(opt);

            } else {
                $('#modal-retrieve_sales .wrap-button').html('');

                var source = $('#tmpl-retrieve-sales-orders-not-found').html();
                $('#retrieve_sales_popup_inner').html(source);
                $('#retrieve-sales-wrapper').removeClass('big-size');
                $('#retrieve-sales-wrapper .box_content').removeAttr('style').show();
                runTips();
                $('#retrieve-sales-wrapper').unblock();
            }
        });
    }

    function getOrdersListPager(opt) {

        opt.urls = {
            a: false,
            b: false,
            c: false,
            d: false
        };
        if (opt.count == 1) {
            opt.items = opt.count + ' ' + pos_i18n[25][0];
        } else {
            opt.items = opt.count + ' ' + pos_i18n[25][1];
        }
        opt.countpages = Math.ceil(opt.count / 20);
        if (opt.countpages > 1) {
            if (opt.currentpage > 1) {

                opt.urls.b = 'APP.getOrdersListContent({count: ' + opt.count + ', currentpage: ' + (opt.currentpage - 1) + ', reg_id: \'' + opt.reg_id + '\', search: \'' + opt.search + '\' })';
                if (opt.currentpage - 1 > 1) {
                    opt.urls.a = 'APP.getOrdersListContent({count: ' + opt.count + ', currentpage: 1, reg_id: \'' + opt.reg_id + '\', search: \'' + opt.search + '\' })';
                }
            }
            if (opt.currentpage != opt.countpages) {
                opt.urls.c = 'APP.getOrdersListContent({count: ' + opt.count + ', currentpage: ' + (opt.currentpage + 1) + ', reg_id: \'' + opt.reg_id + '\', search: \'' + opt.search + '\' })';
                if (opt.currentpage + 1 != opt.countpages) {
                    opt.urls.d = 'APP.getOrdersListContent({count: ' + opt.count + ', currentpage: ' + opt.countpages + ', reg_id: \'' + opt.reg_id + '\', search: \'' + opt.search + '\' })';
                }
            }
        } else {
            opt.count = false;
        }

        var source = $('#tmpl-retrieve-sales-orders-pager').html();
        var template = Handlebars.compile(source);
        var html = template(opt);
        return html;
    }

    changeProductPrice = function (el) {
        if (typeof change_price_timer != 'undefined') {
            clearTimeout(change_price_timer);
        }
        var price = el.val();
        jQuery('#discount-value').val(price);
        var cart_item_key = el.data('row');
        if (typeof CART.cart_contents[cart_item_key] != 'undefined') {

            if (typeof CART.cart_contents[cart_item_key].original_price == 'undefined') {
                CART.cart_contents[cart_item_key].original_price = parseFloat(CART.cart_contents[cart_item_key].price);
            }

            CART.cart_contents[cart_item_key].price = price;
            if (CART.cart_contents[cart_item_key].variation_id > 0) {
                CART.cart_contents[cart_item_key].v_data.price = price;
            } else {
                CART.cart_contents[cart_item_key].data.price = price;
            }
            CART.calculate_totals();
        }
    };

    changeProductQuantity = function (el) {
        var qty = el.val();
        if (qty == '') {
            qty = 0;
        }
        var quantity = parseInt(qty);
        if (wc_pos_params.decimal_quantity == 'yes') {
            quantity = parseFloat(qty);
        }

        var cart_item_key = el.closest('tr').attr('id');
        if (typeof CART.cart_contents[cart_item_key] != 'undefined' && quantity > 0) {
            var old_quantity = CART.cart_contents[cart_item_key]['quantity'];
            var product_data = CART.cart_contents[cart_item_key]['v_data'] != 'undefined' ? CART.cart_contents[cart_item_key]['v_data'] : CART.cart_contents[cart_item_key]['data'];
            if (product_data === false)
                product_data = CART.cart_contents[cart_item_key]['data'];

            var checkStock = APP.checkStock(product_data, quantity, cart_item_key);


            if (checkStock === true) {
                CART.set_quantity(cart_item_key, quantity, true);
            } else {
                $(el).val(old_quantity);
            }
        }
    };

    $(window).resize(function () {
        resizeCart();
        //resizeGrid();
    });
    resizeCart();

    if (pos_ready_to_start == true) {
        WindowStateManager = new WindowStateManager(false, windowUpdated);
    }

    jQuery('#createaccount').on('change', function () {
        if (jQuery(this).attr('checked')) {
            jQuery('#billing_account_username_field, #billing_account_password_field, #billing_password_confirm_field').show();
        } else {
            jQuery('#billing_account_username_field, #billing_account_password_field, #billing_password_confirm_field').hide();
        }
    });
    //Todo: pos_register_data.detail.opening_cash_amount === undefined
    if (pos_register_data.detail.float_cash_management == 1 &&
        pos_register_data.detail.opening_cash_amount && !pos_register_data.detail.opening_cash_amount.status) {
        openModal('modal-opening_cash_amount');
    }

    jQuery('#set_opening_cash_amount').on('click', function () {
        var amount = $('#opening_amount').val();
        var note = $('#opening_amount_note').val();
        $.ajax({
            type: 'POST',
            url: wc_pos_params.ajax_url,
            data: {
                action: 'wc_pos_set_register_opening_cash',
                amount: amount,
                note: note,
                register_id: pos_register_data.ID
            },
            success: function (responce) {
                closeModal('modal-opening_cash_amount');
            }
        });
    });

    jQuery('#full_screen').on('click', function (e) {
        e.preventDefault();
        var elem = document.getElementsByTagName("html")[0];
        element_fullscreen(elem);
    })

    $('#less-amount-notice .approve-less-amount').on('click', function () {
        $('#less-amount-notice').data('approve', '1').fadeOut();
    });
    jQuery('#order_items_list').on('click', '.item', function () {
        var id = jQuery(this).attr('id');
        jQuery('tr.item_note.open').hide().removeClass('open');
        jQuery('#item_note-' + id).show().addClass('open');
    });
    jQuery('#order_items_list').on('change', 'input.item_note', function () {
        var cart = CART.get_cart();
        var item_key = jQuery(this).data('item');
        var value = jQuery(this).val();
        var meta = '';
        var variation = cart[item_key]['variation'];
        if (value != '' && item_key != '') {
            variation[pos_i18n[45]] = value;
            meta += '<li class="item_note_meta"><span class="meta_label">' + pos_i18n[45] + '</span><span class="meta_value">' + value + '</span></li>';
        }
        var display_meta = $('tr#' + item_key + ' td.name .display_meta');
        if (display_meta.length) {
            if (display_meta.find('.item_note_meta').length >= 1) {
                display_meta.find('.item_note_meta').html(meta)
            } else {
                display_meta.append(meta);
            }
        } else {
            meta = '<ul class="display_meta">' + meta + '</ul>';
            $('tr#' + item_key + ' td.name .view').append(meta);
        }
        cart[item_key]['variation'] = variation;
        CART.calculate_totals();
        jQuery('#item_note-' + item_key).hide();
    });

});

function windowUpdated() {
    //"this" is a reference to the WindowStateManager
    if (this.isMainWindow()) {
        closeModal('modal-clone-window');
        if (APP.initialized === false) {
            APP.init();
            process_offline_orders();
        }
    } else {
        openModal('modal-clone-window');
    }
}
function searchProduct(barcode) {
    console.log(barcode);
    var barcode = barcode.trim();
    var quantity = wc_pos_params.decimal_quantity_value;
    if (barcode != '') {
        //wc_pos_params.scan_field
        if (wc_pos_params.scan_field && wc_pos_params.scan_field != '') {

            if (typeof APP.tmp.productscans[barcode] != 'undefined') {
                var s = APP.tmp.productscans[barcode];
                if (typeof s['vid'] != 'undefined') {
                    APP.addToCart(s['id'], quantity, s['vid']);
                } else {
                    APP.addToCart(s['id'], quantity);
                }
            } else {
                APP.showNotice(pos_i18n[36], 'error');
            }
        } else {
            var q = APP.db.from('products').where('sku', '=', barcode);
            q.list(1).done(function (objs) {
                if (objs.length > 0) {
                    APP.addToCart(objs[0].id, quantity);
                } else {
                    var q = APP.db.from('variations').where('sku', '=', barcode);
                    q.list(1).done(function (objs) {
                        if (objs.length > 0) {
                            var variation = objs[0];
                            APP.addToCart(variation.prod_id, quantity, variation.id);
                        } else {
                            APP.showNotice(pos_i18n[36], 'error');
                        }
                    });
                }
            });
        }

    } else {
        APP.showNotice(pos_i18n[36], 'error');
        return;
    }
}

function element_fullscreen(elem) {
    if (!document.webkitFullscreenElement && !document.fullscreenElement && !document.mozFullScreenElement && !document.msFullscreenElement) {
        if (elem.requestFullscreen) {
            elem.requestFullscreen();
        } else if (elem.mozRequestFullScreen) {
            elem.mozRequestFullScreen();
        } else if (elem.webkitRequestFullscreen) {
            elem.webkitRequestFullscreen();
        }
    } else {
        if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        } else if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
        } else if (document.msExitFullscreen) {
            document.msExitFullscreen();
        }
    }
}

function process_offline_orders() {
    APP.db.keys('offline_orders').then(function (keys) {
        if (keys.length) {
            openModal('modal-offline-orders');
            var progressbar = jQuery('.progressbar');
            progressbar.progressbar({
                max: keys.length
            });
            var sec = 1;
            jQuery.each(keys, function (index) {
                APP.db.get("offline_orders", keys[index]).done(function (record) {
                    setTimeout(function () {
                        var cart = {
                            order: record
                        };
                        APP.processPayment(cart, true, false, false);
                        APP.db.remove('offline_orders', keys[index]);
                        var progress_val = progressbar.progressbar("value");
                        progressbar.progressbar("value", progress_val + 1);
                    }, sec * 3000);//3 seconds delay to each order process
                    sec = sec + 1;
                });
            });
            setTimeout(function () {
                closeModal('modal-offline-orders');
            }, (keys.length + 1) * 3000);
        }
    });
}


