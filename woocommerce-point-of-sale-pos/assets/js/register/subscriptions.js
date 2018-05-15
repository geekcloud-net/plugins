jQuery(document).ready(function($) {
	window.POS_SUBSCRIPTION = {

		init : function () {
			wp.hooks.addAction( 'wc_pos_add_to_cart', SUBSCRIPTION.add_to_cart, 20, 6);
			wp.hooks.addAction( 'wc_pos_end_calculate_totals', SUBSCRIPTION.display_recurring_totals, 20, 1);
			wp.hooks.addAction( 'wc_pos_save_product_custom_meta', SUBSCRIPTION.save_product_custom_meta, 20, 1);

			wp.hooks.addFilter( 'wc_pos_add_to_cart_validation', SUBSCRIPTION.cart_validation, 20, 7);
			wp.hooks.addFilter( 'tmpl_cart_item_data', SUBSCRIPTION.tmpl_cart_item_data, 20, 1);
			wp.hooks.addFilter( 'calculate_totals_base_price', SUBSCRIPTION.calculate_totals_base_price, 20, 2);
			wp.hooks.addFilter( 'calculate_totals_line_o_price', SUBSCRIPTION.calculate_totals_line_o_price, 20, 2);
			wp.hooks.addFilter( 'calculate_totals_line_total_row', SUBSCRIPTION.calculate_totals_line_total_row, 20, 3);

			$( document.body ).on( 'change', '#product_type', function() {
                var type = $(this).val();
                if( type == 'subscription'){
                    $('#custom_subscription_fields').show();
                }else{
                	$('#custom_subscription_fields').hide();
                }
            }).trigger('change');

            $( document.body ).on( 'change', '._subscription_period, ._subscription_period_interval', function() {
            	$('._subscription_length').each(function(){
					var $lengthElement    = $(this),
						selectedLength    = $lengthElement.val(),
						hasSelectedLength = false,
						$periodElement    = $(this).closest('.subscription_pricing_table').find('._subscription_period'),
						billingInterval   = parseInt($(this).closest('.subscription_pricing_table').find('._subscription_period_interval').val());

					$lengthElement.empty();

					$.each(WCSubscriptions.subscriptionLengths[ $periodElement.val() ], function(length,description) {
						if(parseInt(length) == 0 || 0 == (parseInt(length) % billingInterval)) {
							$lengthElement.append($('<option></option>').attr('value',length).text(description));
						}
					});

					$lengthElement.children('option').each(function(){
						if (this.value == selectedLength) {
							hasSelectedLength = true;
							return false;
						}
					});

					if(hasSelectedLength){
						$lengthElement.val(selectedLength);
					} else {
						$lengthElement.val(0);
					}

				});

            });

            $('#wc-pos-register-data').on('click', '.add_custom_meta', function() {
            	var item_key      = $(this).closest('tr').attr('id');
				var cart_contents = CART.cart_contents;

				if( typeof cart_contents[item_key] != 'undefined'){
					var product   = cart_contents[item_key];
					if( product.data.type == 'subscription' || product.data.type == 'variable-subscription' ){
						$('#edit_subscription_fields').show();
						$subscription_fields = $('#edit_subscription_fields');
						var _key = 'data';
	                    if( product.variation_id > 0 ){
	                        _key = 'v_data';
	                    }
						var subscription = product[_key].subscription;
						
						$('._subscription_sign_up_fee', $subscription_fields).val(subscription.sign_up_fee);
						$('._subscription_period_interval', $subscription_fields).val(subscription.period_interval).trigger('change');
						$('._subscription_period', $subscription_fields).val(subscription.period).trigger('change');
						$('._subscription_length', $subscription_fields).val(subscription.length).trigger('change');
						
					}else{
						$('#edit_subscription_fields').hide();
					}
				}				
            	return false;
            });

			this.update_fee_column();
		},

		tmpl_cart_item_data : function(cart_item_data){
			if( cart_item_data.data.type == 'subscription' ) {
				cart_item_data.sign_up_fee = cart_item_data.data.subscription.sign_up_fee;
				cart_item_data.formatedprice = cart_item_data.formatedprice + ' / '
			}else if( cart_item_data.data.type  == 'variable-subscription' ){
				cart_item_data.sign_up_fee = cart_item_data.v_data.subscription.sign_up_fee;
			}
			return cart_item_data;
		},

		update_fee_column : function () {
			var contains_fee = false;
			var cart_contents = CART.get_cart();
			$.each(cart_contents, function(cart_item_key, cart_item) {
				if( ( cart_item.data.type == 'subscription' || cart_item.data.type  == 'variable-subscription') && cart_item.sign_up_fee ) {
					contains_fee = true;
				}
			});
			if( contains_fee ){
				$('#order_items_list-wrapper .line_fee').show();
			}else{
				$('#order_items_list-wrapper .line_fee').hide();
			}
		},

		/**
		 * When a subscription is added to the cart, remove other products/subscriptions to
		 * work with PayPal Standard, which only accept one subscription per checkout.
		 *
		 * If multiple purchase flag is set, allow them to be added at the same time.
		 */
		cart_validation : function(valid, adding_to_cart, product_id, quantity, variation_id, variations){
			var is_subscription                 = SUBSCRIPTION.is_subscription(adding_to_cart);
			var cart_contains_subscription      = SUBSCRIPTION.cart_contains_subscription();
			var multiple_subscriptions_possible = wc_pos_subscriptions_options.multiple_subscriptions;
			var manual_renewals_enabled         = wc_pos_subscriptions_options.accept_manual_renewals;
			var multiple_purchase_enabled       = wc_pos_subscriptions_options.multiple_purchase;
			var canonical_product_id            = ( typeof variation_id != 'undefined' ) ? variation_id : product_id;

			if ( is_subscription && !multiple_purchase_enabled ) {

				CART.empty_cart();

			} else if ( is_subscription && SUBSCRIPTION.cart_contains_renewal() && ! multiple_subscriptions_possible && ! manual_renewals_enabled ) {

				SUBSCRIPTION.remove_subscriptions_from_cart();

				APP.showNotice(subscriptions_i18n[0], 'info');

			} else if ( is_subscription && cart_contains_subscription && ! multiple_subscriptions_possible && ! manual_renewals_enabled && ! SUBSCRIPTION.cart_contains_product( canonical_product_id ) ) {

				SUBSCRIPTION.remove_subscriptions_from_cart();

				APP.showNotice(subscriptions_i18n[1], 'info');

			} else if ( cart_contains_subscription && !multiple_purchase_enabled ) {

				SUBSCRIPTION.remove_subscriptions_from_cart();

				APP.showNotice(subscriptions_i18n[2], 'info');

			}

			return valid;
		},

		/**
		 * Checks a given product to determine if it is a subscription.
		 * When the received arg is a product object, make sure it is passed into the filter intact in order to retain any properties added on the fly.
		 */
		is_subscription : function( product_data ){
			return product_data.type == 'variable-subscription' || product_data.type == 'subscription';
		},

		/**
		 * Check if the cart contains a subscription which requires shipping.
		 */
		cart_contains_subscription: function(){
			var contains_subscription = false;
			var cart_contents = CART.get_cart();
			$.each(cart_contents, function(cart_item_key, cart_item) {
				 if( cart_item.data.type == 'variable-subscription' || cart_item.data.type == 'subscription' ){
				 	contains_subscription = true;
				 }
			});
			return contains_subscription;
		},

		/**
		 * Removes all subscription products from the shopping cart.
		 */
		remove_subscriptions_from_cart: function(){
			var cart_contents = CART.get_cart();
			$.each(cart_contents, function(cart_item_key, cart_item) {
				 if( SUBSCRIPTION.is_subscription(cart_item.data) ){
				 	$('#wc-pos-register-data tr#' + cart_item_key).remove();
				 	CART.remove_cart_item(cart_item_key);
				 }
			});
		},

		/**
		 * Checks the cart to see if it contains a subscription product renewal.
		 *
		 * @param  bool | Array The cart item containing the renewal, else false.
		 * @return object
		 */
		cart_contains_renewal: function(){
			var contains_renewal = false;
			var cart_contents    = CART.get_cart();

			$.each(cart_contents, function(cart_item_key, cart_item) {
				 if( typeof cart_item.subscription_renewal != 'undefined' ){
				 	contains_renewal = cart_item;
				 	return false;
				 }
			});

			return contains_renewal;
		},

		/**
		 * Checks the cart to see if it contains a specific product.
		 *
		 * @param int The product ID or variation ID to look for.
		 * @return bool Whether the product is in the cart.
		 */
		cart_contains_product: function( product_id ) {
			var cart_contains_product = false;
			var cart_contents    = CART.get_cart();

			$.each(cart_contents, function(cart_item_key, cart_item) {
				 if( SUBSCRIPTION.get_canonical_product_id( cart_item ) == product_id ){
				 	cart_contains_product = true;
				 	return false;
				 }
			});

			return cart_contains_product;
		},

		/**
		 * Get the variation ID for variation items or the product ID for non-variation items.
		 *
		 * When acting on cart items or order items, Subscriptions often needs to use an item's canonical product ID. For
		 * items representing a variation, that means the 'variation_id' value, if the item is not a variation, that means
		 * the 'product_id value. This function helps save keystrokes on the idiom to check if an item is to a variation or not.
		 *
		 * @param array $item Either a cart item or order/subscription line item
		 */
		get_canonical_product_id : function( cart_item ){
			return ( typeof cart_item.variation_id != 'undefined' && cart_item.variation_id > 0  ) ? cart_item.variation_id : cart_item.product_id ;
		},

		add_to_cart: function (cart_item_key, product_id,  quantity, variation_id, variation, cart_item_data) {
			SUBSCRIPTION.update_fee_column();
			/*var $row = $('#wc-pos-register-data tr#' + cart_item_key);
			
			if( SUBSCRIPTION.is_subscription(cart_item_data.data) ){
				console.log(cart_item_data.data);
			}*/
		},

		calculate_totals_base_price : function(price, cart_item) {
			if( typeof cart_item.sign_up_fee != 'undefined' && cart_item.sign_up_fee && cart_item.sign_up_fee != '' && parseFloat(cart_item.sign_up_fee) > 0 ){
				price += parseFloat(cart_item.sign_up_fee);
			}
			return price;
		},
		calculate_totals_line_o_price : function(price, cart_item) {
			if( typeof cart_item.sign_up_fee != 'undefined' && cart_item.sign_up_fee && cart_item.sign_up_fee != '' && parseFloat(cart_item.sign_up_fee) > 0 ){
				price += parseFloat(cart_item.sign_up_fee) * cart_item['quantity'];
			}
			return price;
		},

		calculate_totals_line_total_row : function(line_total_row, line_total_data, cart_item) {
			
			if( SUBSCRIPTION.is_subscription(cart_item.data) ){
				var product = cart_item.v_data ? cart_item.v_data : cart_item.data;
				var line_subtotal     = line_total_data.line_subtotal,
					line_total        = line_total_data.line_total,
					line_tax          = line_total_data.line_tax,
					line_subtotal_tax = line_total_data.line_subtotal_tax,
					line_price        = line_total_data.line_price,
					line_o_price      = line_total_data.line_o_price;

				var billing_interval    = parseInt(product.subscription.period_interval),
					billing_period      = product.subscription.period,
					subscription_length = parseInt(product.subscription.length),
					trial_length        = parseInt(product.subscription.trial_length),
					trial_period        = product.subscription.trial_period;

				var base_price   = parseFloat(product.price);
				var line_price   = base_price * cart_item['quantity'];	

				line_total_row = '<span class="amount">' + accountingPOS(line_price, 'formatMoney') + '</span>';
				

				var price       = line_total_row + ' <span class="subscription-details">',					
					ranges      = WCSubscriptions.subscriptionLengths[billing_period];
					include_length = subscription_length != 0 ? true : false;
					subscription_string = '';

				if ( include_length && subscription_length == billing_interval ) {
					subscription_string = price; // Only for one billing period so show "$5 for 3 months" instead of "$5 every 3 months for 3 months"
				} else if ( SUBSCRIPTION.is_product_synced( product ) && in_array( billing_period, ['week', 'month', 'year'] ) ) {
					var payment_day = SUBSCRIPTION.get_products_payment_day( product );
					switch ( billing_period ) {
						case 'week':
							var payment_day_of_week = wc_pos_subscriptions_options.weekdays[payment_day];
							if ( 1 == billing_interval ) {
								// translators: 1$: recurring amount string, 2$: day of the week (e.g. "$10 every Wednesday")
								subscription_string = sprintf( subscriptions_i18n[3], price, payment_day_of_week );
							} else {
								// translators: 1$: recurring amount string, 2$: period, 3$: day of the week (e.g. "$10 every 2nd week on Wednesday")
								subscription_string = sprintf( subscriptions_i18n[4], price, SUBSCRIPTION.get_subscription_period_strings( billing_interval, billing_period ), payment_day_of_week );
							}
							break;
						case 'month':
							if ( 1 == billing_interval ) {
								if ( payment_day > 27 ) {
									// translators: placeholder is recurring amount
									subscription_string = sprintf( subscriptions_i18n[5], price );
								} else {
									// translators: 1$: recurring amount, 2$: day of the month (e.g. "23rd") (e.g. "$5 every 23rd of each month")
									subscription_string = sprintf( subscriptions_i18n[6], price, SUBSCRIPTION.append_numeral_suffix( payment_day ) );
								}
							} else {
								if ( payment_day > 27 ) {
									// translators: 1$: recurring amount, 2$: interval (e.g. "3rd") (e.g. "$10 on the last day of every 3rd month")
									subscription_string = sprintf( subscriptions_i18n[7], price, SUBSCRIPTION.append_numeral_suffix( billing_interval ) );
								} else {
									// translators: 1$: <price> on the, 2$: <date> day of every, 3$: <interval> month (e.g. "$10 on the 23rd day of every 2nd month")
									subscription_string = sprintf( subscriptions_i18n[8], price, SUBSCRIPTION.append_numeral_suffix( payment_day ), SUBSCRIPTION.append_numeral_suffix(billing_interval ) );
								}
							}
							break;
						case 'year':
							if ( 1 == billing_interval ) {
								var payment_day_month = wc_pos_subscriptions_options.months[payment_day['month']];
								// translators: 1$: <price> on, 2$: <date>, 3$: <month> each year (e.g. "$15 on March 15th each year")
								subscription_string = sprintf( subscriptions_i18n[9], price, payment_day_month, SUBSCRIPTION.append_numeral_suffix( payment_day['day'] ) );
							} else {
								// translators: 1$: recurring amount, 2$: month (e.g. "March"), 3$: day of the month (e.g. "23rd") (e.g. "$15 on March 15th every 3rd year")
								subscription_string = sprintf( subscriptions_i18n[10], $price, payment_day_month, SUBSCRIPTION.append_numeral_suffix( payment_day['day'] ), SUBSCRIPTION.append_numeral_suffix( billing_interval ) );
							}
							break;
					}
				} else {
					// translators: 1$: recurring amount, 2$: subscription period (e.g. "month" or "3 months") (e.g. "$15 / month" or "$15 every 2nd month")
					subscription_string = sprintf( ( billing_interval == 1 ? subscriptions_i18n[13][0] : subscriptions_i18n[13][1] ), price, SUBSCRIPTION.get_subscription_period_strings( billing_interval, billing_period ) );
				}

				// Add the length to the end
				if ( include_length ) {
					// translators: 1$: subscription string (e.g. "$10 up front then $5 on March 23rd every 3rd year"), 2$: length (e.g. "4 years")
					subscription_string = sprintf( subscriptions_i18n[14], subscription_string, ranges[ subscription_length ] );
				}

				if ( 0 != trial_length ) {
					var trial_string = SUBSCRIPTION.get_trial_period_strings( trial_length, trial_period );
					// translators: 1$: subscription string (e.g. "$15 on March 15th every 3 years for 6 years"), 2$: trial length (e.g.: "with 4 months free trial")
					subscription_string = sprintf( subscriptions_i18n[15], subscription_string, trial_string );
				}
				if ( typeof cart_item.sign_up_fee != 'undefined' && cart_item.sign_up_fee && cart_item.sign_up_fee != '' && parseFloat(cart_item.sign_up_fee) > 0) {
					var sign_up_fee = accountingPOS(cart_item.sign_up_fee * cart_item['quantity'], 'formatMoney');
					// translators: 1$: subscription string (e.g. "$15 on March 15th every 3 years for 6 years with 2 months free trial"), 2$: signup fee price (e.g. "and a $30 sign-up fee")
					subscription_string = sprintf( subscriptions_i18n[17], subscription_string, sign_up_fee );
				}

				subscription_string += '</span>';

				line_total_row = subscription_string;
			}
			return line_total_row;
		},
		display_recurring_totals : function( cart ){
			var html = '';
			var multiple_payments = 0;
			$('.tr_recurring_total_item').remove();
			$.each(cart, function(cart_item_key, cart_item) {

				var _product = cart_item['data'];

				if( SUBSCRIPTION.is_subscription(_product) ){
				 	if( cart_item.variation_id > 0 ){
				 		_product = cart_item['v_data'];
				 	}

				 	var start_date        = gmdate( 'Y-m-d H:i:s' );
				 	var next_payment_date = SUBSCRIPTION.get_first_renewal_payment_date(_product, start_date);

				 	if( next_payment_date != 0){
				 		recurring_total = accountingPOS( _product.price * cart_item.quantity, 'formatMoney' );
				 		var source = $('#tmpl-recurring-total-item').html();
				 		var template = Handlebars.compile(source);
						var context  = {'recurring_total': recurring_total, 'next_payment_date': next_payment_date, 'label' :  !multiple_payments ? true : false };
							html    = html + template(context);					
				 		multiple_payments++;
				 	}				
				}


			});			
			if (multiple_payments > 0 && html != ''){
				$('#tr_order_total_label').after(html);
			}
		},

		save_product_custom_meta : function(item_key){
			if( typeof CART.cart_contents[item_key] != 'undefined'){
				
				var _key = 'data';
                if( CART.cart_contents[item_key].variation_id > 0 ){
                    _key = 'v_data';
                }
                if( typeof CART.cart_contents[item_key][_key].subscription != 'undefined'){
					var $subscription_fields = $('#edit_subscription_fields'),
						$sign_up_fee         = $('._subscription_sign_up_fee', $subscription_fields).val(),
						$period_interval     = $('._subscription_period_interval', $subscription_fields).val(),
						$period              = $('._subscription_period', $subscription_fields).val(),
						$length              = $('._subscription_length', $subscription_fields).val();

					CART.cart_contents[item_key].sign_up_fee                         = $sign_up_fee.length ? $sign_up_fee : 0;
					CART.cart_contents[item_key][_key].subscription.sign_up_fee     = $sign_up_fee.length ? $sign_up_fee : 0;
					CART.cart_contents[item_key][_key].subscription.period_interval = $period_interval.length ? $period_interval : 1;
					CART.cart_contents[item_key][_key].subscription.period          = $period.length ? $period : 'day';
					CART.cart_contents[item_key][_key].subscription.length          = $length.length ? $length : 0;
                }
			}
		},
		/**
		 * Returns the subscription interval for a product, if it's a subscription.
		 */
		get_interval : function ( product ) {

			var subscription_period_interval = 1;

			if ( typeof product != 'undefined' && SUBSCRIPTION.is_subscription( product ) && typeof product.subscription.period_interval != 'undefined') {
				subscription_period_interval = product.subscription.period_interval;
			}

			return parseInt(subscription_period_interval);
		},
		/**
		 * Returns the length of a subscription product, if it is a subscription.
		 */
		get_length : function ( product ) {

			var subscription_length = 0;

			if ( typeof product != 'undefined' && SUBSCRIPTION.is_subscription( product ) && typeof product.subscription.length != 'undefined') {
				subscription_length = product.subscription.length;
			}

			return parseInt(subscription_length);
		},
		/**
		 * Returns the trial length of a subscription product, if it is a subscription.
		 */
		get_trial_length : function ( product ) {

			var subscription_trial_length = 0;

			if ( typeof product != 'undefined' && SUBSCRIPTION.is_subscription( product ) && typeof product.subscription.trial_length != 'undefined') {
				subscription_trial_length = product.subscription.trial_length;
			}

			return parseInt(subscription_trial_length);
		},
		/**
		 * Returns the subscription period for a product, if it's a subscription.
		 */
		get_period : function ( product ) {

			var subscription_period = 0;

			if ( typeof product != 'undefined' && SUBSCRIPTION.is_subscription( product ) && typeof product.subscription.period != 'undefined') {
				subscription_period = product.subscription.period;
			}

			return subscription_period;
		},
		/**
		 * Returns the trial period of a subscription product, if it is a subscription.
		 */
		get_trial_period : function ( product ) {

			var subscription_trial_period = '';
			if ( typeof product != 'undefined' && SUBSCRIPTION.is_subscription( product ) ) {
				if( typeof product.subscription.trial_period == 'undefined' || product.subscription.trial_period == ''){
					subscription_trial_period = SUBSCRIPTION.get_period( product );
				}else{
					subscription_trial_period = product.subscription.trial_period ;
				}
			}

			return subscription_trial_period;
		},
		/**
		 * Takes a subscription product's ID and returns the date on which the subscription trial will expire,
		 * based on the subscription's trial length and calculated from either the from_date if specified,
		 * or the current date/time.
		 */
		get_trial_expiration_date : function ( product, from_date ) {

			var trial_length = SUBSCRIPTION.get_trial_length( product );
			var trial_expiration_date = 0;
			from_date = typeof from_date != 'undefined' ? from_date : '';
			if ( trial_length > 0 ) {

				if ( from_date == '' ) {
					from_date = gmdate( 'Y-m-d H:i:s' );
				}

				trial_expiration_date = gmdate( 'Y-m-d H:i:s', pos_wcs_add_time( trial_length, SUBSCRIPTION.get_trial_period( product ), pos_date_to_time( from_date ) ) );

			}

			return trial_expiration_date;
		},

		/**
		 * Takes a subscription product's ID and returns the date on which the first renewal payment will be processed
		 * based on the subscription's length and calculated from either the from_date if specified, or the current date/time.
		 */
		get_first_renewal_payment_time : function ( product, from_date, timezone ) {
			if ( ! SUBSCRIPTION.is_subscription( product ) ) {
				return 0
			}

			from_date = typeof from_date != 'undefined' ? from_date : '';
			timezone  = typeof timezone != 'undefined' ? timezone : 'gmt';

			var billing_interval = SUBSCRIPTION.get_interval( product ),
				billing_length   = SUBSCRIPTION.get_length( product ),
				trial_length     = SUBSCRIPTION.get_trial_length( product );

			if ( billing_interval !== billing_length || trial_length > 0 ) {

				if ( from_date == '' ) {
					from_date = gmdate( 'Y-m-d H:i:s' );
				}

				// If the subscription has a free trial period, the first renewal is the same as the expiration of the free trial
				if ( trial_length > 0 ) {

					first_renewal_timestamp = pos_date_to_time( SUBSCRIPTION.get_trial_expiration_date( product, from_date ) );

				} else {

					first_renewal_timestamp = pos_wcs_add_time( billing_interval, SUBSCRIPTION.get_period( product ), pos_date_to_time( from_date ) );

					/*if ( 'site' == timezone ) {
						first_renewal_timestamp += ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
					}*/
				}
			} else {
				first_renewal_timestamp = 0;
			}

			return first_renewal_timestamp;
		},
		/**
		 * Takes a subscription product's ID and returns the date on which the first renewal payment will be processed
		 * based on the subscription's length and calculated from either the from_date if specified, or the current date/time.
		 */
		get_first_renewal_payment_date : function( product, from_date){
			var timezone = 'gmt';
			var first_renewal_timestamp = SUBSCRIPTION.get_first_renewal_payment_time( product, from_date, timezone );

			if ( first_renewal_timestamp > 0 ) {
				first_renewal_date = gmdate( wc_pos_params.date_format, first_renewal_timestamp );
			} else {
				first_renewal_date = 0;
			}
			return first_renewal_date;
		},

		is_product_synced : function ( product ) {
			if ( typeof product != 'object' || ! wc_pos_subscriptions_options.syncing_enabled || 'day' == product.subscription.period  ) {
				return false;
			}

			var payment_date = SUBSCRIPTION.get_products_payment_day( product );

			return ( ( typeof payment_date == 'number' && payment_date > 0 ) || (  typeof payment_date == 'object' && typeof payment_date['day'] != 'undefined' && payment_date['day'] > 0 ) ) ? true : false;
		},
		get_products_payment_day : function(product){
			var payment_date = 0;
			if (  ! wc_pos_subscriptions_options.syncing_enabled ) {
				payment_date = 0;
			} else if (  typeof product == 'object'  ) {
				payment_date = product.subscription.payment_sync_date;
			} else {
				payment_date = 0;
			}

			return payment_date;
		},
		get_weekday : function(product){
			var payment_date = 0;
			if (  ! wc_pos_subscriptions_options.syncing_enabled ) {
				payment_date = 0;
			} else if (  typeof product == 'object'  ) {
				payment_date = product.subscription.payment_sync_date;
			} else {
				payment_date = 0;
			}

			return payment_date;
		},
		get_subscription_period_strings : function ( number, period ) {
			if (typeof number == 'undefined') {number = 1}
			if (typeof period == 'undefined') {period = ''}

			var translated_periods = {
					// translators: placeholder is number of days. (e.g. "Bill this every day / 4 days")
					'day'   : sprintf( (number == 1 ? subscriptions_i18n[11][0] : subscriptions_i18n[11][1]),  number),
					// translators: placeholder is number of weeks. (e.g. "Bill this every week / 4 weeks")
					'week'   : sprintf( (number == 1 ? subscriptions_i18n[11][2] : subscriptions_i18n[11][3]),  number),
					// translators: placeholder is number of months. (e.g. "Bill this every month / 4 months")
					'month'   : sprintf( (number == 1 ? subscriptions_i18n[11][4] : subscriptions_i18n[11][5]),  number),
					// translators: placeholder is number of years. (e.g. "Bill this every year / 4 years")
					'year'   : sprintf( (number == 1 ? subscriptions_i18n[11][6] : subscriptions_i18n[11][7]),  number),
				};

			return period != '' && typeof translated_periods[ period ] != 'undefined' ? translated_periods[ period ] : translated_periods;
		},

		/**
		 * Takes a number and returns the number with its relevant suffix appended, eg. for 2, the function returns 2nd
		 *
		 * @since 1.0
		 */
		append_numeral_suffix: function( number ) {

			var number_string = '';
			number = number.toString();
			// Handle teens: if the tens digit of a number is 1, then write "th" after the number. For example: 11th, 13th, 19th, 112th, 9311th. http://en.wikipedia.org/wiki/English_numerals
			if ( number.length > 1 && '1' == number.substr( -2, 1 ) ) {
				// translators: placeholder is a number, this is for the teens
				number_string = sprintf( subscriptions_i18n[12][0], number );
			} else { // Append relevant suffix
				switch ( number.substr( -1 ) ) {
					case '1':
						// translators: placeholder is a number, numbers ending in 1
						number_string = sprintf( subscriptions_i18n[12][1], number );
						break;
					case '2':
						// translators: placeholder is a number, numbers ending in 2
						number_string = sprintf( subscriptions_i18n[12][2], number );
						break;
					case '3':
						// translators: placeholder is a number, numbers ending in 3
						number_string = sprintf( subscriptions_i18n[12][3], number );
						break;
					default:
						// translators: placeholder is a number, numbers ending in 4-9, 0
						number_string = sprintf( subscriptions_i18n[12][0], number );
						break;
				}
			}

			return number_string;
		},


		get_trial_period_strings : function(number, period) {

			if (typeof number == 'undefined') {number = 1}
			if (typeof period == 'undefined') {period = ''}

			var translated_periods = {
					'day'    : sprintf( (number == 1 ? subscriptions_i18n[16][0] : subscriptions_i18n[16][1]),  number),
					'week'   : sprintf( (number == 1 ? subscriptions_i18n[16][2] : subscriptions_i18n[16][3]),  number),
					'month'  : sprintf( (number == 1 ? subscriptions_i18n[16][4] : subscriptions_i18n[16][4]),  number),
					'year'   : sprintf( (number == 1 ? subscriptions_i18n[16][6] : subscriptions_i18n[16][7]),  number)
				};

			return period != '' && typeof translated_periods[ period ] != 'undefined' ? translated_periods[ period ] : translated_periods;
		},

		

	/** END POS_SUBSCRIPTION **/
	};
});

