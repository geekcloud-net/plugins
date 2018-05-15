var ThriveUlt = ThriveUlt || {};
ThriveUlt.util = ThriveUlt.util || {};

/**
 * Settings for the underscore templates
 * Enables <##> tags instead of <%%>
 *
 * @type {{evaluate: RegExp, interpolate: RegExp, escape: RegExp}}
 */
_.templateSettings = {
	evaluate: /<#([\s\S]+?)#>/g,
	interpolate: /<#=([\s\S]+?)#>/g,
	escape: /<#-([\s\S]+?)#>/g
};

/**
 * Override Backbone ajax call and append wp security token
 *
 * @returns {*}
 */
Backbone.ajax = function () {
	if ( arguments[0].url.indexOf( '_nonce' ) === - 1 ) {
		arguments[0]['url'] += "&_nonce=" + ThriveUlt.admin_nonce;
	}

	return Backbone.$.ajax.apply( Backbone.$, arguments );
};

(function ( $ ) {

	/**
	 * Uppercase the 1st letter in string
	 *
	 * @param str
	 * @returns string
	 */
	ThriveUlt.util.upperFirst = function ( str ) {
		if ( ! str ) {
			return '';
		}

		return str.toLowerCase().charAt( 0 ).toUpperCase() + str.slice( 1 );
	};

	/**
	 * Opens AJAX-loaded modal
	 *
	 * @param props
	 */
	ThriveUlt.util.ajaxModal = function ( props ) {
		TVE_Dash.showLoader();

		var _ajax = _.extend(
			{
				type: 'get'
			}, props
			),
			self = this;

		$.ajax( _ajax ).done(
			function ( response ) {
				var _view = new TVE_Dash.views.Modal( props );
				_view.data = props;
				_view.template = _.template( response );
				_view.render().open( props );
				if ( typeof props.afterOpen === 'function' ) {
					props.afterOpen.call( _view );
				}
			}
		).always(
			function () {
				TVE_Dash.hideLoader();
			}
		);

	};

	/**
	 * Sprintf js version
	 * @param string
	 * @param {...*} args
	 * @returns {*}
	 */
	ThriveUlt.util.printf = function ( string, args ) {
		if ( ! args ) {
			return string;
		}

		var is_array = args instanceof Array;

		if ( ! is_array ) {
			args = [args];
		}

		_.each(
			args, function ( replacement ) {
				string = string.replace( "%s", replacement );
			}
		);

		return string;
	};

	/**
	 * Returns the correct form of translated string
	 * based on count number
	 *
	 * @param {String} single
	 * @param {String} plural
	 * @param {Int} count
	 *
	 * @returns {String}
	 */
	ThriveUlt.util.plural = function ( single, plural, count ) {
		return count == 1 ? ThriveUlt.util.printf( single, count ) : ThriveUlt.util.printf( plural, count );
	};

	/**
	 * Some constants defined
	 *
	 * @type {{normal: string, delete: string}}
	 */
	ThriveUlt.util.states = {
		normal: 'normal',
		delete: 'delete',
		checked: 'checked'
	};

	/**
	 * Some days constants
	 * @type {{week: number, month: number}}
	 */
	ThriveUlt.util.days = {
		week: 7,
		month: 31
	};

	ThriveUlt.util.weekdays = {
		weekdays: ['S', 'M', 'T', 'W', 'T', 'F', 'S'],
		weekdays_full: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
	};

	/**
	 * Campaign types constants
	 *
	 * @type {{absolute: string, rolling: string, evergreen: string}}
	 */
	ThriveUlt.util.campaignType = {
		absolute: 'absolute',
		rolling: 'rolling',
		evergreen: 'evergreen'
	};

	/**
	 * Campaign status constants
	 *
	 * @type {{running: string, paused: string}}
	 */
	ThriveUlt.util.status = {
		running: 'running',
		paused: 'paused',
		archived: 'archived'
	};

	/**
	 *  Campaign rolling type constants
	 * @type {{daily: string, weekly: string, monthly: string, yearly: string}}
	 */
	ThriveUlt.util.rollingType = {
		daily: 'daily',
		weekly: 'weekly',
		monthly: 'monthly',
		yearly: 'yearly'
	};

	/**
	 * Trigger types constants
	 * @type {{conversion: string, first: string, url: string}}
	 */
	ThriveUlt.util.triggerType = {
		conversion: 'conversion',
		first: 'first',
		url: 'url',
		promotion: 'promotion'
	};

	/**
	 * Strings for triggers display
	 * @type {{conversion: string, first: string, url: string}}
	 */
	ThriveUlt.util.triggerVisit = {
		conversion: 'Thrive Leads Conversion',
		first: 'first page visit',
		url: 'page visit',
		linked: 'Conversion Event',
		promotion: 'Visit to Promotion Page'
	};

	/**
	 * Constants for conversion events
	 * @type {{end: string, move: string}}
	 */
	ThriveUlt.util.conversion_event = {
		end: 'end',
		move: 'move'
	};

	/**
	 * Constants for conversion trigger types
	 * @type {{conversion: string, specific: string}}
	 */
	ThriveUlt.util.trigger_type = {
		conversion: 'conversion',
		specific: 'specific'
	};

	ThriveUlt.util.leadtype = {
		lead_group: 'tve_lead_group',
		shortcode: 'tve_lead_shortcode'
	};

	/**
	 * Create campaign tile from the campaign type
	 * @param type
	 * @returns {string}
	 */
	ThriveUlt.util.get_type_title = function ( type ) {
		var title;

		switch ( type ) {
			case 'absolute':
				title = "Fixed Date";
				break;
			case 'rolling':
				title = "Recurring";
				break;
			case 'evergreen':
				title = "Evergreen";
				break;
		}

		return title + " Campaign";
	};

	/**
	 * pre-process the ajaxurl admin js variable and append a querystring to it
	 * some plugins are adding an extra parameter to the admin-ajax.php url. Example: admin-ajax.php?lang=en
	 *
	 * @param {string} [query_string] optional, query string to be appended
	 */
	ThriveUlt.ajaxurl = function ( query_string ) {
		var _q = ajaxurl.indexOf( '?' ) !== - 1 ? '&' : '?';
		if ( ! query_string || ! query_string.length ) {
			return ajaxurl + _q + '_nonce=' + ThriveUlt.admin_nonce;
		}
		query_string = query_string.replace( /^(\?|&)/, '' );
		query_string += '&_nonce=' + ThriveUlt.admin_nonce;

		return ajaxurl + _q + query_string;
	};
	/**
	 * Function to select card, and deselect all other cards
	 *
	 * @param target element
	 * @param sibligns
	 * @param selected class
	 */
	ThriveUlt.select_card = function ( targetEl, targetSiblings, selectedClass ) {
		targetSiblings.removeClass( selectedClass );
		targetEl.addClass( selectedClass );
	};

	/**
	 * To be called on ajax.done callback
	 * Overwrite this from anywhere
	 */
	ThriveUlt.util.ajax_done = function () {
	};

	/**
	 * Calculates how many days are in hours given as param
	 *
	 * @param hours
	 * @returns {*[]} with days and hours
	 */
	ThriveUlt.util.get_days_and_hours = function ( hours ) {
		if ( isNaN( hours ) ) {
			return [0, 0];
		}
		if ( hours <= 23 ) {
			return [0, hours];
		}
		var days = parseInt( hours / 24 );

		return [days, hours % 24];
	};

	/**
	 * jQuery plugin that searches for selects and
	 * bind change event on them which sets data-name attribute
	 * with text of selected option
	 *
	 * @see: ThriveUlt.views.ModalEditEvent.displayActionOptions()
	 */
	$.fn.data_name = function () {
		this.find( 'select' ).change(
			function () {
				var $this = $( this ),
					id = $this.val();
				$this.attr( 'data-name', $this.find( 'option[value="' + id + '"]' ).text() );
			}
		);
	};

	/**
	 * handles the keyup event on a text input and if enter is pressed it will:
	 * a) call the target function, if target is a Function
	 * b) trigger click on a DOM element if target is a selector or a jquery wrapper over a button (mimics the "Enter" keypress on forms with submit buttons)
	 *
	 * @param {Function|jQuery|string} target callback function for ENTER, or jquery wrapper over a button that should be "clicked" on enter
	 *
	 * @returns {Function} callback
	 */
	ThriveUlt.util.enter_key_fn = function ( target ) {

		function callback() {
			if ( $.isFunction( target ) ) {
				return target();
			}
			if ( typeof target === 'string' || target.jquery ) {
				return $( target ).trigger( 'click' );
			}
		}

		return function ( event ) {
			if ( event.which === 13 ) {
				callback();
			}
		};
	};

	ThriveUlt.util.isValidHour = function ( hour ) {
		return /^([0-1]?[0-9]|2[0-4]):([0-5][0-9])(:[0-5][0-9])?$/.test( hour );
	};

	/**
	 * binds all form elements on a view
	 * Form elements must have a data-bind attribute which should contain the field name from the model
	 * composite fields are not supported
	 *
	 * this will bind listeners on models and on the form elements
	 *
	 * @param {Backbone.View} view
	 * @param {Backbone.Model} [model] optional, it will default to the view's model
	 */
	ThriveUlt.util.data_binder = function ( view, model ) {

		if ( typeof model === 'undefined' ) {
			model = view.model;
		}

		if ( ! model instanceof Backbone.Model ) {
			return;
		}

		/**
		 * separate value by input type
		 *
		 * @param {object} $input jquery
		 * @returns {*}
		 */
		function value_getter( $input ) {
			if ( $input.is( ':checkbox' ) ) {
				return $input.is( ':checked' ) ? true : false;
			}
			if ( $input.is( ':radio' ) ) {
				return $input.is( ':checked' ) ? $input.val() : '';
			}

			return $input.val();
		}

		/**
		 * separate setter vor values based on input type
		 *
		 * @param {object} $input jquery object
		 * @param {*} value
		 * @returns {*}
		 */
		function value_setter( $input, value ) {
			if ( $input.is( ':radio' ) ) {
				return view.$el.find( 'input[name="' + $input.attr( 'name' ) + '"]:radio' ).filter( '[value="' + value + '"]' ).prop( 'checked', true );
			}
			if ( $input.is( ':checkbox' ) ) {
				return $input.prop( 'checked', value ? true : false );
			}

			return $input.val( value );
		}

		/**
		 * iterate through each of the elements and bind change listeners on DOM and on the model
		 */
		var $elements = view.$el.find( '[data-bind]' ).each(
			function () {

				var $this = $( this ),
					prop = $this.attr( 'data-bind' ),
					_dirty = false;

				$this.on(
					'change', function () {
						var _value = value_getter( $this );
						if ( model.get( prop ) != _value ) {
							_dirty = true;
							model.set( prop, _value )
							_dirty = false;
						}
					}
				);

				view.listenTo(
					model, 'change:' + prop, function () {
						if ( ! _dirty ) {
							value_setter( $this, this.model.get( prop ) );
						}
					}
				);
			}
		);

		/**
		 * if a model defines a validate() function, it should return an array of binds in the form of:
		 *      ['post_title']
		 * this will add error classes to the bound dom elements
		 */
		view.listenTo(
			model, 'invalid', function ( model, error ) {
				if ( _.isArray( error ) ) {
					_.each(
						error, function ( field ) {
							var _field = field;
							if ( field.field ) { // if this is an object, we need to use the field property
								_field = field.field
							}
							var $target = $elements.filter( '[data-bind="' + _field + '"]' ).first().addClass( 'tvd-validate tvd-invalid' ).focus();
							if ( field.message ) {
								$target.siblings( 'label' ).attr( 'data-error', field.message );
							}
							if ( $target.is( ':radio' ) || $target.is( ':checkbox' ) ) {
								TVE_Dash.err( $target.next( 'label' ).attr( 'data-error' ) );
							}
						}
					);
				} else if ( _.isString( error ) ) {
					TVE_Dash.err( error );
				}
			}
		);
	};

	/**
	 * construct a date object and set its time to the server time
	 * example:
	 *  suppose the user local time is 13:30, user timezone: GMT+3 (this means GMT time is 10:30)
	 *  the server's gmt_offset is GMT-1, this function will return a date object having the hour set at 09:30 (according to the server's time)
	 *
	 * this function should only be used on date validations (date x greater than date y)
	 * ! DO NOT RELY ON THIS FUNCTION TO MAKE DATE CALCULATIONS, because it does not represent the correct browser time !! just a representation of the server time
	 *
	 * @param {Date} [browser_date] a javascript Date object (which will have by default the timezone of the browser setup
	 */
	ThriveUlt.util.server_date = function ( browser_date ) {
		browser_date = browser_date || new Date();
		var utc_timestamp = Date.UTC( browser_date.getFullYear(), browser_date.getMonth(), browser_date.getDate(), browser_date.getHours(), browser_date.getMinutes(), browser_date.getSeconds(), browser_date.getMilliseconds() ),
			browser_offset = utc_timestamp - browser_date.getTime(), // difference between browser timezone and UTC, in miliseconds
			server_offset = ThriveUlt.wp_timezone_offset * 3600 * 1000; // difference between server timezone and UTC, in miliseconds

		/* in order to get the server now() date, substract the browser difference and add the server difference to the utc_timezone */
		utc_timestamp = utc_timestamp + server_offset - 2 * browser_offset;
		browser_date.setTime( utc_timestamp );

		return browser_date;
	};

	/**
	 * Re-binds the Wistia video popover listeners
	 */
	ThriveUlt.util.bind_wistia = function () {
		if ( window.rebindWistiaFancyBoxes ) {
			window.rebindWistiaFancyBoxes();
		}
	}

})( jQuery );
;/**
 * Thrive Ultimatum Models and Collections
 */
var ThriveUlt = ThriveUlt || {};
ThriveUlt.models = ThriveUlt.models || {};
ThriveUlt.collections = ThriveUlt.collections || {};

(function ( $ ) {

	/**
	 * Sets Backbone to emulate HTTP requests for models
	 * HTTP_X_HTTP_METHOD_OVERRIDE set to PUT|POST|PATH|DELETE|GET
	 *
	 * @type {boolean}
	 */
	Backbone.emulateHTTP = true;

	/**
	 * Base Model
	 */
	ThriveUlt.models.Base = Backbone.Model.extend( {
		idAttribute: 'ID',
		/**
		 * deep-json implementation for backbone models - flattens any abject, collection etc from the model
		 *
		 * @returns {Object}
		 */
		toDeepJSON: function () {
			var obj = $.extend( true, {}, this.attributes );
			_.each( _.keys( obj ), function ( key ) {
				if ( ! _.isUndefined( obj[key] ) && ! _.isNull( obj[key] ) && _.isFunction( obj[key].toJSON ) ) {
					obj[key] = obj[key].toJSON();
				}
			} );
			return obj;
		},
		/**
		 * deep clone a backbone model
		 * this will duplicate all included collections, models etc located in the attributes field
		 *
		 * @returns {ThriveUlt.models.Base}
		 */
		deepClone: function () {
			return new this.constructor( this.toDeepJSON() );
		},
		/**
		 * ensures the same instance of a collection is used in a Backbone model
		 *
		 * @param {object} data
		 * @param {object} collection_map map with object keys and collection constructors
		 */
		ensureCollectionData: function ( data, collection_map ) {
			_.each( collection_map, _.bind( function ( constructor, key ) {
				if ( ! data[key] ) {
					return true;
				}
				var instanceOf = this.get( key ) instanceof constructor;
				if ( ! instanceOf ) {
					data[key] = new constructor( data[key] );
					return true;
				}
				this.get( key ).reset( data[key] );
				data[key] = this.get( key );
			}, this ) );
		},
		validation_error: function ( field, message ) {
			return {
				field: field,
				message: message
			};
		}
	} );

	/**
	 * Base Collection
	 */
	ThriveUlt.collections.Base = Backbone.Collection.extend( {
		/**
		 * helper function to get the last item of a collection
		 *
		 * @return Backbone.Model
		 */
		last: function () {
			return this.at( this.size() - 1 );
		}
	} );

	/**
	 * Model for the main settings in the header
	 */
	ThriveUlt.models.Settings = ThriveUlt.models.Base.extend( {
		defaults: {
			ID: '',
		},
		url: function () {
			var url = ThriveUlt.ajaxurl( 'action=' + ThriveUlt.ajax_actions.admin_controller + '&route=dateSettings' );

			return url;
		},
		/**
		 * Overwrite Backbone validation
		 * Return something to invalidate the model
		 *
		 * @param {Object} attrs
		 * @param {Object} options
		 */
		validate: function ( attrs, options ) {
			var errors = [];
			if ( ! attrs.offset ) {
				errors.push( ThriveUlt.t.Timezone_required );
			}

			if ( errors.length ) {
				return errors;
			}
		}
	} );

	/**
	 * Campaign Model
	 */
	ThriveUlt.models.Campaign = ThriveUlt.models.Base.extend( {
		defaults: {
			ID: '',
			rolling_type: '',
			status: ThriveUlt.util.status.paused,
			impressions: 0,
			has_event_logs: false,
			settings: {
				start: {
					date: '',
					time: ''
				},
				end: '',
				duration: 1,
				repeat: 1,
				evergreen_repeat: 0,
				repeatOn: [],
				trigger: [],
				real: 0,
				realtime: '00:00'
			},
			lockdown_settings: {}
		},
		/**
		 *
		 * get dummy data for displaying a "placeholder" chart when there is no impression / conversion registered
		 *
		 * @returns {Object}
		 */
		get_chart_dummy_data: function () {
			var _data = {};
			_data.conversions = [11, 8, 14, 11, 17, 12, 9, 19, 17, 11, 21, 13, 4];
			_data.impressions = [64, 58, 89, 85, 93, 75, 74, 83, 88, 72, 90, 82, 27];
			_data.labels = _.map( function () {
				return ''
			}, _.range( _data.impressions.length ) );

			return _data;
		},
		url: function () {
			var url = ThriveUlt.ajaxurl( 'action=' + ThriveUlt.ajax_actions.admin_controller + '&route=campaigns' );

			if ( this.get( 'ID' ) ) {
				url += '&ID=' + this.get( 'ID' );
			}

			return url;
		},
		initialize: function () {
			this.set( 'state', ThriveUlt.util.states.normal );
			this.set( 'lockdown_state', ThriveUlt.util.states.normal );
		},

		/**
		 * Creates all the collections for the model
		 * (needed so we can check if the campaign is ready when campaign conversion events with move event are added)
		 */
		createModelCollections: function () {
			if ( this.get( 'display_settings' ) && ! ( this.get( 'display_settings' ) instanceof ThriveUlt.collections.Hangers ) ) {
				this.set( 'display_settings', new ThriveUlt.collections.Hangers( this.get( 'display_settings' ) ) );
				this.set( 'display_settings_tpl', new ThriveUlt.models.TemplateList( this.get( 'display_settings_tpl' ) ) );
			}

			if ( this.get( 'designs' ) && ! ( this.get( 'designs' ) instanceof ThriveUlt.collections.Designs ) ) {
				this.set( 'designs', new ThriveUlt.collections.Designs( this.get( 'designs' ) ) );
			}
			if ( this.get( 'timeline' ) && ! ( this.get( 'timeline' ) instanceof ThriveUlt.collections.Events ) ) {
				this.set( 'timeline', new ThriveUlt.collections.Events( this.get( 'timeline' ) ) );
			}
		},
		parse: function ( data ) {

			//sets the state to default normal value
			data.state = ThriveUlt.util.states.normal;

			if ( data.settings ) {
				//do we know current settings are meant to be for rolling collection ??? wtf?!
				data.settings_collection = new ThriveUlt.collections.RollingCollection();
				data.settings_collection.setOptions( data.settings.duration, data.rolling_type, data.settings.repeatOn );
			}
			if ( data.linked_to ) {
				var campaigns = new ThriveUlt.collections.LinkedToCollection();
				_.each( data.linked_to, function ( item ) {
					var campaign = ThriveUlt.globals.campaigns.findWhere( {ID: parseInt( item )} );
					campaigns.add( campaign );
				} );

				data.linked_to = campaigns;
			}

			if ( data.display_settings ) {
				data.display_settings = new ThriveUlt.collections.Hangers( data.display_settings );
				data.display_settings_summary = data.display_settings.get_display_summary();
				data.display_settings_tpl = new ThriveUlt.models.TemplateList( data.display_settings_tpl );
			}

			if ( data.lockdown_settings && data.lockdown_settings.promotion ) {
				data.lockdown_settings.promotion = new ThriveUlt.collections.PromotionURLCollection( data.lockdown_settings.promotion );
			}

			this.ensureCollectionData( data, {
				timeline: ThriveUlt.collections.Events,
				designs: ThriveUlt.collections.Designs,
				conversion_events: ThriveUlt.collections.ConversionEvents
			} );

			return data;
		},
		/**
		 * Overwrite Backbone validation
		 * Return something to invalidate the model
		 *
		 * @param {Object} attrs
		 * @param {Object} options
		 */
		validate: function ( attrs, options ) {

			var errors = [];
			if ( ! attrs.post_title ) {
				errors.push( this.validation_error( 'post_title', ThriveUlt.t.InvalidName ) );
			}
			if ( errors.length ) {
				return errors;
			}
			if ( attrs.skip_settings_validation ) {
				return;
			}
			if ( ! attrs.type ) {
				errors.push( ThriveUlt.t.Choose_campaign_type );
				return errors;
			}

			if ( attrs.type && typeof this['validate_' + attrs.type] === 'function' ) {
				this['validate_' + attrs.type]( attrs, errors );
			}

			if ( attrs.lockdown ) {
				this.validate_lockdown( attrs, errors );
			}

			if ( errors.length ) {
				return errors;
			}
		},
		/**
		 * Validate Lockdown settings
		 * @param attrs
		 * @param errors
		 * @returns {*}
		 */
		validate_lockdown: function ( attrs, errors ) {
			var s = attrs.lockdown_settings;

			if ( attrs.settings_modal ) {
				return;
			}
			if ( ! s.preaccess || (s.preaccess instanceof Array && s.preaccess.length === 0) ) {
				errors.push( this.validation_error( 'preaccess', ThriveUlt.t.Pre_access_required ) );
			}

			if ( ! s.promotion || s.promotion.length == 0 || (s.promotion instanceof Object && ! s.promotion.at( 0 ).get( 'id' )) ) {
				errors.push( this.validation_error( 'promotion', ThriveUlt.t.Promotion_required ) );
			}

			if ( ! s.expired || (s.expired instanceof Array && s.expired.length === 0) ) {
				errors.push( this.validation_error( 'expired', ThriveUlt.t.Expired_required ) );
			}

			if ( errors.length ) {
				return errors;
			}

			var data = [];
			if ( s.preaccess.value === s.promotion.value ) {
				data = [ThriveUlt.t.promotion, ThriveUlt.t.pre_access];
				errors.push( this.validation_error( 'promotion', ThriveUlt.util.printf( ThriveUlt.t.SamePage, data ) ) );
			}

			if ( s.promotion.value === s.expired.value ) {
				data = [ThriveUlt.t.expired, ThriveUlt.t.promotion];
				errors.push( this.validation_error( 'expired', ThriveUlt.util.printf( ThriveUlt.t.SamePage, data ) ) );
			}

			if ( errors.length ) {
				return errors;
			}
		},
		/**
		 * Validate rolling campaign
		 *
		 * @param {Object} attrs
		 * @param {Array} errors
		 */
		validate_rolling: function ( attrs, errors ) {
			var s = attrs.settings,
				today = ThriveUlt.util.server_date(),
				duration = parseInt( s.duration ),
				start_date = new Date( s.start.date + ' ' + s.start.time );

			/** validate start date/time */
			if ( ! $.trim( s.start.date ) ) {
				errors.push( this.validation_error( 'start_date', ThriveUlt.t.Start_date_required ) );
			}
			if ( ! $.trim( s.start.time ) ) {
				errors.push( this.validation_error( 'start_time', ThriveUlt.t.Start_time_required ) );
			}
			if ( $.trim( s.start.time ) && ! ThriveUlt.util.isValidHour( s.start.time ) ) {
				errors.push( this.validation_error( 'start_time', ThriveUlt.t.InvalidHour ) );
			}
			if ( ! $.trim( s.duration ).match( /\d+/ ) || isNaN( duration ) || duration < 1 ) {
				errors.push( this.validation_error( 'duration', ThriveUlt.t.Invalid_duration ) )
			}

			/** return errors for these validations if the exists */
			if ( errors.length ) {
				return errors;
			}

			/** for new campaign the start date has to be in the future */
			if ( attrs.edit_mode === 'new' && today > start_date ) {
				var m = this._same_day( today, start_date ) ? ThriveUlt.t.Start_hour_in_the_past : ThriveUlt.t.InvalidStartDateToday;
				errors.push( this.validation_error( this._same_day( today, start_date ) ? 'start_time' : 'start_date', m ) );
			}

			/** for daily type the duration cannot be greater than 24 */
			if ( attrs.rolling_type === ThriveUlt.util.rollingType.daily && duration > 24 ) {
				errors.push( this.validation_error( 'duration', ThriveUlt.util.printf( ThriveUlt.t.InvalidDurationTime, 24 ) ) );
			}
			/** for weekly type the duration cannot be greater than 7 */
			if ( attrs.rolling_type === ThriveUlt.util.rollingType.weekly && duration > 7 ) {
				errors.push( this.validation_error( 'duration', ThriveUlt.util.printf( ThriveUlt.t.InvalidDurationTime, 7 ) ) );
			}
			/** for monthly type the duration cannot be greater than 31 */
			if ( attrs.rolling_type === ThriveUlt.util.rollingType.monthly && duration > 31 ) {
				errors.push( this.validation_error( 'duration', ThriveUlt.util.printf( ThriveUlt.t.InvalidDurationTime, 31 ) ) );
			}
			/** for yearly type the duration cannot be greater than 365 */
			if ( attrs.rolling_type === ThriveUlt.util.rollingType.yearly && duration > 365 ) {
				errors.push( this.validation_error( 'duration', ThriveUlt.util.printf( ThriveUlt.t.InvalidDurationTime, 365 ) ) );
			}
			/** campaign ends on a specific date */
			if ( s.end !== null && typeof s.end === 'object' ) {
				/** validate end date */
				if ( ! $.trim( s.end.date ) ) {
					errors.push( this.validation_error( 'end_date', ThriveUlt.t.End_date_required ) );
				}
				/** validate end time */
				if ( ! $.trim( s.end.time ) ) {
					errors.push( this.validation_error( 'end_time', ThriveUlt.t.End_time_required ) );
				}
				/** validate end hour */
				if ( $.trim( s.end.time ) && ! ThriveUlt.util.isValidHour( s.end.time ) ) {
					errors.push( this.validation_error( 'end_time', ThriveUlt.t.InvalidEndHour ) );
				}
				var end_date = new Date( s.end.date + ' ' + s.end.time );
				if ( end_date <= start_date ) {
					var message = s.start.date === s.end.date ? ThriveUlt.t.End_after_start_time : ThriveUlt.t.End_after_start_date;
					errors.push( this.validation_error( s.start.date === s.end.date ? 'end_time' : 'end_date', message ) );
				}
			}
			/** campaign ends after specific occurrences */
			if ( typeof s.end === 'string' ) {
				if ( ! $.trim( s.end ) ) {
					errors.push( this.validation_error( 'occurrences_number', ThriveUlt.t.OccurrencesRequired ) );
				}
				if ( $.trim( s.end ) && (isNaN( s.end ) || s.end <= 0 ) ) {
					errors.push( this.validation_error( 'occurrences_number', ThriveUlt.t.InvalidOccurrences ) );
				}
			}
			if ( errors.length ) {
				return errors;
			}
			// if the user selects weekly or monthly rolling options let's do some checks
			if ( attrs.rolling_type === ThriveUlt.util.rollingType.weekly || attrs.rolling_type === ThriveUlt.util.rollingType.monthly ) {
				// make sure that the user has selected atleast one day for the start of the campaign
				if ( attrs.settings.repeatOn.length === 0 ) {
					errors.push( ThriveUlt.t.InvalidRepeatOn );
				}
			}
		},
		/**
		 * Validate evergreen campaign
		 *
		 * @param {object} attrs
		 * @param {Array} errors
		 */
		validate_evergreen: function ( attrs, errors ) {
			var s = attrs.settings,
				duration = parseInt( s.duration ),
				end = parseInt( s.end );

			/**
			 * duration and "END" are required fields and they must be positive integers
			 */
			if ( ! $.trim( s.duration ).match( /^\d+$/ ) || isNaN( duration ) || duration < 1 ) {
				errors.push( this.validation_error( 'duration', ThriveUlt.t.Invalid_duration ) );
			}

			if ( parseInt( s.evergreen_repeat ) == 1 && (! $.trim( s.end ).match( /^\d+$/ ) || isNaN( end ) || end < 1 ) ) {
				errors.push( this.validation_error( 'end', ThriveUlt.t.Invalid_end_evergreen ) );
			}

			if ( errors.length ) {
				return errors;
			}

			/** is trigger a url ? */
			if ( s.trigger.type === ThriveUlt.util.triggerType.url ) {
				// check if the user has selected a post
				if ( s.trigger.ids == '' ) {
					errors.push( this.validation_error( 'post_search', ThriveUlt.t.Choose_trigger_page ) );
				}
			}

			if ( errors.length ) {
				return errors;
			}

			/** check if the campaign is not a linked campaign */
			if ( ! attrs.linked_to && attrs.edit_mode != 'evergreen' ) {

				/** let's make sure the user has selected a trigger */
				if ( s.trigger.type === '' ) {
					errors.push( ThriveUlt.t.InvalidTriggerIds );
				}
				/** is trigger a url ? */
				if ( s.trigger.type === ThriveUlt.util.triggerType.url ) {
					// check if the user has selected a post
					if ( s.trigger.ids == '' ) {
						errors.push( ThriveUlt.t.InvalidPost );
					}
				}
				/** is trigger a conversion ? */
				if ( s.trigger.type === ThriveUlt.util.triggerType.conversion ) {
					/** check if the user has selected atleast one lead group */
					if ( s.trigger.ids.length === 0 ) {
						errors.push( ThriveUlt.t.InvalidLeadGroup );
					}
				}
			}
		},
		/**
		 * Validate absolute campaign
		 *
		 * @param {Object} attrs
		 * @param {Array} errors
		 */
		validate_absolute: function ( attrs, errors ) {
			var today = ThriveUlt.util.server_date(),
				start = attrs.settings.start,
				end = attrs.settings.end,
				start_date = new Date( start.date + ' ' + start.time ),
				end_date = new Date( end.date + ' ' + end.time );

			/** check start date */
			if ( ! $.trim( start.date ) ) {
				errors.push( this.validation_error( 'start_date', ThriveUlt.t.Start_date_required ) );
			}
			if ( ! $.trim( start.time ) ) {
				errors.push( this.validation_error( 'start_time', ThriveUlt.t.Start_time_required ) );
			}
			/** check end date */
			if ( ! $.trim( end.date ) ) {
				errors.push( this.validation_error( 'end_date', ThriveUlt.t.End_date_required ) );
			}
			if ( ! $.trim( end.time ) ) {
				errors.push( this.validation_error( 'end_time', ThriveUlt.t.End_time_required ) );
			}
			/* first round of validations ends here */
			if ( errors.length ) {
				return errors;
			}

			/** for new campaigns */
			if ( attrs.edit_mode === 'new' ) {
				/** we must have a future end date setup */
				if ( end_date < today ) {
					var m = this._same_day( today, start_date ) ? ThriveUlt.t.End_hour_in_the_past : ThriveUlt.t.InvalidEndDateToday;
					errors.push( this.validation_error( this._same_day( today, start_date ) ? 'end_time' : 'end_date', m ) );
				}
			}


			if ( end_date <= start_date ) {
				var message = start.date === end.date ? ThriveUlt.t.End_after_start_time : ThriveUlt.t.End_after_start_date;
				errors.push( this.validation_error( start.date === end.date ? 'end_time' : 'end_date', message ) );
			}
			if ( errors.length ) {
				return errors;
			}
		},
		/**
		 * check if the 2 dates have the same day (same year, month and date)
		 *
		 * @param date1
		 * @param date2
		 * @private
		 */
		_same_day: function ( date1, date2 ) {
			return date1.getFullYear() === date2.getFullYear() && date1.getMonth() === date2.getMonth() && date1.getDate() === date2.getDate();
		},
		getDisplaySettingsUrl: function () {
			return ThriveUlt.ajaxurl( 'action=' + ThriveUlt.ajax_actions.admin_controller + '&route=displaySettingsCampaign&campaign_id=' + this.get( 'ID' ) );
		},
		saveStatus: function ( new_status ) {
			var self = this;
			return $.ajax( {
				url: ThriveUlt.ajaxurl(),
				dataType: 'json',
				method: 'POST',
				data: {
					_nonce: ThriveUlt.admin_nonce,
					action: ThriveUlt.ajax_actions.admin_controller,
					route: 'campaignStatus',
					ID: this.get( 'ID' ),
					status: new_status
				}
			} ).done( function ( response ) {
				self.set( 'status', new_status );
			} );
		},
		cleanModel: function () {
			var type = this.get( 'type' );
			if ( type === ThriveUlt.util.campaignType.absolute ) {
				this.get( 'settings' ).repeat = 1;
				this.get( 'settings' ).duration = 1;
				this.get( 'settings' ).repeatOn = [];
				this.set( 'rolling_type', '' );
				this.get( 'settings' ).trigger = {type: '', ids: ''};
				this.get( 'settings' ).real = 0;
				this.get( 'settings' ).realtime = '00:00';
			} else if ( type === ThriveUlt.util.campaignType.evergreen ) {
				this.get( 'settings' ).repeatOn = [];
				this.set( 'rolling_type', '' );
				this.get( 'settings' ).start = {date: '', time: ''};
				var evergreen_repeat = this.get( 'settings' ).evergreen_repeat;
				if ( ! evergreen_repeat || parseInt( evergreen_repeat ) === 0 ) {
					this.get( 'settings' ).end = '';
				}
			} else {
				this.get( 'settings' ).trigger = {type: '', ids: ''};
				this.get( 'settings' ).real = 0;
				this.get( 'settings' ).realtime = '00:00';
			}
		},
		changeRepeat: function ( e ) {
			var val = '',
				type = this.get( 'rolling_type' );
			if ( e ) {
				val = e.target.value;
				this.get( 'settings' ).repeat = val;
			} else {
				val = this.get( 'settings' ).repeat;
			}
			! type ? type = ThriveUlt.util.rollingType.daily : type;

			if ( type === ThriveUlt.util.rollingType.daily ) {
				val == 1 ? type = type.replace( 'ily', 'y' ) : type = type.replace( 'ily', 'ys' );
			} else {
				val == 1 ? type = type.replace( 'ly', '' ) : type = type.replace( 'ly', 's' );
			}
			val == 1 ? val = '' : val;

			return val + ' ' + type;
		},
		getSummary: function () {

			var type = this.get( 'type' ),
				end = '';
			if ( type === ThriveUlt.util.campaignType.rolling ) {
				var repeat = this.changeRepeat(),
					append = this.get( 'settings_collection' ).prepareAppend(),
					rolling = this.get( 'rolling_type' );

				if ( rolling == ThriveUlt.util.rollingType.daily || rolling == ThriveUlt.util.rollingType.yearly || ! rolling ) {
					this.set( 'summary', ThriveUlt.util.printf( ThriveUlt.t.SummaryRunsDayYear, [repeat] ) );
					return;
				}

				this.set( 'summary', ThriveUlt.util.printf( ThriveUlt.t.SummaryRuns, [
					repeat,
					append
				] ) );
			} else if ( type === ThriveUlt.util.campaignType.absolute ) {

				var start = this.get( 'settings' ).start,
					end = this.get( 'settings' ).end,
					start_date = new Date( start.date + ' ' + start.time ),
					end_date = new Date( end.date + ' ' + end.time ),
					diff = (end_date.getTime() - start_date.getTime()) / 1000;

				var data = [$.datepicker.formatDate( ThriveUlt.date_format, start_date ) + ' ' + $().timepicker.formatTime( ThriveUlt.time_format, start_date )],
					days = Math.floor( diff / 86400 ),
					hours = Math.floor( (diff - days * 86400) / 3600 );

				if ( days ) {
					data.push( ThriveUlt.util.plural( ThriveUlt.t.count_day, ThriveUlt.t.count_days, days ) );
				}

				if ( hours ) {
					data.push( ThriveUlt.util.plural( ThriveUlt.t.count_hour, ThriveUlt.t.count_hours, hours ) );
				}

				var summary = ThriveUlt.util.printf( days && hours && hours > 0 ? ThriveUlt.t.SummaryStartsDaysHours : ThriveUlt.t.SummaryStarts, data );

				return this.set( 'summary', summary );
			} else {
				var trigger = this.get( 'settings' ).trigger.type,
					on = '',
					days = '';
				if ( type === ThriveUlt.util.campaignType.evergreen ) {
					end = this.get( 'settings' ).duration;
				} else {
					end = this.get( 'settings' ).end;
				}
				end == 1 ? days = 'day' : days = 'days';

				if ( trigger ) {
					if ( trigger === ThriveUlt.util.triggerType.url ) {
						on = ThriveUlt.util.triggerVisit.url;
					} else if ( trigger === ThriveUlt.util.triggerType.first ) {
						on = ThriveUlt.util.triggerVisit.first;
					} else if ( trigger === ThriveUlt.util.triggerType.promotion ) {
						on = ThriveUlt.util.triggerVisit.promotion;
					} else {
						on = ThriveUlt.util.triggerVisit.conversion;
					}
				} else {
					on = ThriveUlt.util.triggerVisit.linked;
				}

				this.set( 'summary', ThriveUlt.util.printf( ThriveUlt.t.SummaryStartsEvergreen, [
					on,
					end,
					days
				] ) );
			}
		},
		checkDesignsTemplate: function () {
			var designs = this.get( 'designs' ),
				tpl = '';

			designs.each( function ( item ) {
				var template = item.get( 'tpl' );

				if ( template ) {
					tpl = template;
				}
			}, this );


			return tpl ? true : false;
		},
		/**
		 * Set the time
		 * @param val
		 */
		setTime: function ( val ) {
			this.get( 'settings' ).end = this.get( 'settings' ).end !== null && typeof this.get( 'settings' ).end === 'object'
				? this.get( 'settings' ).end : {
				date: '',
				time: ''
			};

			this.get( 'settings' ).end.time = val;

			return this;
		},
		/**
		 * checks if a design type already exists in this campaign
		 *
		 * @param {string} design_type type key
		 *
		 * @returns {Boolean}
		 */
		has_design_type: function ( design_type ) {

			if ( typeof design_type !== 'string' || ! design_type.length ) {
				return true;
			}

			if ( design_type === 'shortcode' ) {
				return false;
			}

			return this.get( 'designs' ).findWhere( {post_type: design_type} ) !== undefined;
		},
		/**
		 * check if the current campaign meets the requirements for setting up events
		 * currently, ony the type is required to setup the events
		 */
		can_set_events: function () {
			return this.get( 'type' );
		},
		/**
		 * checks the next step that needs to be completed in the setup of the campaign
		 *
		 * These steps can be interchanged easily
		 *
		 * @returns {string}
		 */
		get_settings_step: function () {

			if ( ! this.get( 'display_settings' ) ) {
				return 'display';
			}
			/**
			 * Step 1. if there is no type setup, the user needs to choose a campaign type and its settings
			 */
			if ( ! this.get( 'type' ) ) {
				return 'type';
			}

			/**
			 * If leads is deactivated and the start trigger is set as conversion notify the user that he needs to change his triggers
			 */
			if ( (! ThriveUlt.data.lead_groups || ! ThriveUlt.data.shortcodes) && this.get( 'settings' ).trigger.type == ThriveUlt.util.triggerType.conversion ) {
				return 'leads_missing';
			}

			if ( this.get( 'type' ) == ThriveUlt.util.campaignType.evergreen && ! this.get( 'lockdown' ) && this.get( 'settings' ).trigger.type == ThriveUlt.util.triggerType.promotion ) {
				return 'lockdown_type';
			}

			/**
			 * Step 2. if the display settings have not been chosen yet, the user needs to choose those
			 */
			if ( ! this.get( 'display_settings' ).has_saved_options() ) {
				return 'display';
			}

			/**
			 * Step 3. if the display settings have not been chosen yet, the user needs to choose those
			 */
			if ( this.get( 'lockdown' ) && ( this.get( 'lockdown_settings' ).preaccess.length == 0 || this.get( 'lockdown_settings' ).expired.length == 0 || this.get( 'lockdown_settings' ).promotion.length == 0 ) ) {
				return 'lockdown';
			}

			/**
			 * Step 4. add at least a design to the campaign
			 */
			if ( ! this.get( 'designs' ).size() ) {
				return 'design';
			}

			/**
			 * Step 5. Add event to the timeline
			 *
			 * TODO: conversion events step
			 */
			if ( this.get( 'timeline' ).size() <= 2 && ! this.get( 'timeline' ).at( 0 ).get( 'actions' ).size() ) {

				return 'timeline';
			}
			return '';
		},
		/**
		 * get the data required for the chart (impressions and conversion reported to time intervals)
		 * searches first for a local copy of the chart data
		 *
		 * @param {Function} cb callback to apply on successful data retrieval
		 * @returns mixed
		 */
		load_chart_data: function ( cb ) {
			if ( ! this.get( 'ID' ) ) {
				return null;
			}
			if ( this.has( 'chart_data' ) ) {
				return cb.call( null, this.get( 'chart_data' ) );
			}

			$.ajax( {
				url: this.url(),
				data: {
					custom: 'chart_data',
					_nonce: ThriveUlt.admin_nonce
				},
				type: 'post',
				dataType: 'json'
			} ).done( _.bind( function ( r ) {
				this.set( 'chart_data', r );
				cb.call( null, this.get( 'chart_data' ) );
			}, this ) ).fail( _.bind( function () {
				cb.call( null, null );
			}, this ) );
		},
		/**
		 * Round the time to a fixed hour
		 * @param time
		 * @returns {string}
		 */
		roundTime: function ( time ) {
			if ( time ) {
				var minutes = time.slice( - 2 ),
					hours = time.slice( 0, 2 );

				if ( minutes > 30 ) {
					hours ++;
				}
				minutes = '00';

				return hours + ':' + minutes;
			}

			return '';
		},
		/**
		 * check if this campaign has a specific campaign type
		 *
		 * @param {String} type
		 */
		_is_type: function ( type ) {
			return this.get( 'type' ) && this.get( 'type' ) === type;
		},
		/**
		 * checks if this campaign is an Evergreen campaign
		 *
		 * @returns {Boolean}
		 */
		is_evergreen: function () {
			return this._is_type( ThriveUlt.util.campaignType.evergreen );
		},
		/**
		 * checks if campaign is an Fixed date camapaign
		 * @returns {Boolean}
		 */
		is_absolute: function () {
			return this._is_type( ThriveUlt.util.campaignType.absolute );
		},
		/**
		 * check if campaign is a Rolling dates campaign
		 * @returns {Boolean}
		 */
		is_rolling: function () {
			return this._is_type( ThriveUlt.util.campaignType.rolling );
		},
		/**
		 * Checks if a valid trigger has been set that is compatible with the lockdown feature
		 *
		 * @returns {boolean}
		 */
		has_valid_lockdown_trigger: function () {
			if ( ! this.is_evergreen() ) {
				return true;
			}
			var _settings = this.get( 'settings' );
			if ( ! _settings.trigger || ! $.isPlainObject( _settings.trigger ) || ! _settings.trigger.type ) {
				return false;
			}

			return _settings.trigger.type === ThriveUlt.util.triggerType.conversion || _settings.trigger.type === ThriveUlt.util.triggerType.promotion;
		},
		is_archived: function () {
			return this.get( 'status' ) === ThriveUlt.util.status.archived;
		},
		is_running: function () {
			return this.get( 'status' ) === ThriveUlt.util.status.running;
		},
		get_archive_tooltip: function () {
			return this.get( 'status' ) === ThriveUlt.util.status.archived ? ThriveUlt.t.restore_campaign : ThriveUlt.t.archive_campaign;
		}
	} );

	/**
	 * Rolling Campaign Type Checkboxes
	 * @type {void|*}
	 */
	ThriveUlt.models.CheckboxModel = ThriveUlt.models.Base.extend( {
		defaults: {
			ID: '',
			checked: false,
			label: '',
			disabled: false
		}
	} );

	/**
	 * LeadGroup Model
	 */
	ThriveUlt.models.LeadGroupModel = ThriveUlt.models.Base.extend( {} );

	/**
	 * Lead Groups collection
	 * @type {void|*}
	 */
	ThriveUlt.collections.LeadGroupsCollection = ThriveUlt.collections.Base.extend( {
		model: ThriveUlt.models.LeadGroupModel
	} );

	/**
	 * LeadGroup Model
	 */
	ThriveUlt.models.ShortcodeModel = ThriveUlt.models.Base.extend( {} );

	/**
	 * Lead Groups collection
	 * @type {void|*}
	 */
	ThriveUlt.collections.ShortcodesCollection = ThriveUlt.collections.Base.extend( {
		model: ThriveUlt.models.ShortcodeModel
	} );

	/**
	 * Promotion URL Model
	 */
	ThriveUlt.models.PromotionURLModel = ThriveUlt.models.Base.extend( {
		idAttribute: 'ID',
		defaults: {
			id: '',
			label: '',
			type: '',
			value: '',
			link: ''
		}
	} );

	/**
	 * Promotion Url Collection
	 * @type {void|*}
	 */
	ThriveUlt.collections.PromotionURLCollection = ThriveUlt.collections.Base.extend( {
		model: ThriveUlt.models.PromotionURLModel,
		generateEmailLink: function () {
			var model = this.model,
				parent_model = this.parent_model,
				$select = $( '#tvu-autoresponder-type' ),
				tag = $select.find( 'option:selected' ).data( 'tag' ),
				$input = this.$( '.tvu-email-link' );

			tag = tag || '[email]';
			if ( model instanceof Array && model.length === 0 ) {
				return;
			}

			function prepare_link( link, email_tag ) {
				var glue = link.indexOf( '?' ) > 0 ? '&' : '?';
				link = link + glue + 'tu_id=' + parent_model.get( 'ID' ) + '&tu_em=' + email_tag;

				return link;
			}

			if ( model !== null && ! model.get( 'id' ) && model.get( 'value' ) ) {
				var link = prepare_link( model.get( 'value' ), tag );
				model.set( 'link', link );

				return;
			}

			var url = ajaxurl + '?action=' + ThriveUlt.ajax_actions.admin_controller + '&route=getPostByID',
				data = {
					id: model.get( 'id' ),
					_nonce: ThriveUlt.admin_nonce
				};

			$input.val( ThriveUlt.t.Loading );

			$.ajax( {
				method: 'POST',
				url: url,
				data: data,
				success: function ( result ) {
					var link = prepare_link( result.url, tag );
					model.set( {link: link} )
				}
			} );
		},
		cleanCollection: function () {
			var errors = [];

			if ( ! this.at( 0 ).get( 'id' ) ) {
				errors.push( this.at( 0 ).validation_error( 'promotion', ThriveUlt.t.Promotion_required ) );

				return errors;
			}

			if ( this.length > 1 ) {

				_.each( this.where( {'id': ''} ), function ( item ) {
					this.remove( item, {silent: true} );
				}, this );
			}

			return errors;
		}
	} );

	/**
	 * Rolling Campaign Monthly or Weekly Type Checkboxes collection
	 * @type {void|*}
	 */
	ThriveUlt.collections.RollingCollection = ThriveUlt.collections.Base.extend( {
		model: ThriveUlt.models.CheckboxModel,
		/**
		 * Resets the collection for weekly and monthly rolling types with 7 or 31 models
		 * Sets the default checkboxes
		 * @param duration
		 * @param type
		 * @param data
		 * @returns {ThriveUlt.collections.RollingCollection}
		 */
		setOptions: function ( duration, type, data ) {
			var limit = 0,
				x = duration - 1,
				y = 2 * x + 1,
				collection = this;

			// set our collection length depending what type our  rolling campaign is
			if ( type === ThriveUlt.util.rollingType.weekly ) {
				var days = ThriveUlt.util.weekdays.weekdays;
				limit = ThriveUlt.util.days.week;
			} else if ( type === ThriveUlt.util.rollingType.monthly ) {
				limit = ThriveUlt.util.days.month;
			}

			// let's make sure our collection is empty
			this.reset();
			/**
			 * Set our collection of 31 or 7 models and set the checked propriety for the ones in our array
			 */
			for ( var i = 0; i < limit; i ++ ) {
				this.add( new ThriveUlt.models.CheckboxModel( {

					checked: _.contains( data, i ),
					ID: i,
					label: days ? days[i] : i + 1,
					disabled: false
				} ) );
			}

			/**
			 * Get our checked ID's and disable the checkboxes next to them
			 */
			var c = this.where( {checked: true} );

			if ( c ) {
				_.each( c, function ( item ) {
					var ID = item.get( 'ID' );
					for ( var i = 0; i < limit; i ++ ) {
						var d = i - ID < 0 ? ID - i + x : i - ID + x;
						if ( d % limit < y ) {
							if ( i !== ID ) {
								collection.at( i ).set( 'disabled', true );
							}
						}
					}
				} );
			}

			return this;
		},
		/**
		 * Changes the checkboxes states
		 * @param duration
		 * @param ID
		 */
		changeCheckboxes: function ( duration, ID ) {
			var m = this.length,
				x = duration - 1,
				y = 2 * x + 1,
				value = this.at( ID ).get( 'checked' ),
				collection = this;
			/**
			 * Disable checkboxes next to our element
			 */
			for ( var i = 0; i < m; i ++ ) {
				var d = i - ID < 0 ? ID - i + x : i - ID + x;
				if ( d % m < y ) {
					if ( i !== ID ) {
						this.at( i ).set( 'disabled', value ? false : true );
					}
				}
			}

			/**
			 * disable the checkboxes from the id's which are already set
			 */
			var c = this.where( {checked: true} );

			if ( c && value == true ) {
				_.each( c, function ( item ) {
					if ( item.get( 'ID' ) !== ID ) {
						var newID = item.get( 'ID' );
						for ( var i = 0; i < m; i ++ ) {
							var d = i - newID < 0 ? newID - i + x : i - newID + x;
							if ( d % m < y ) {
								if ( i !== newID ) {
									collection.at( i ).set( 'disabled', true );
								}
							}
						}
					}
				} );
			}

		},
		/**
		 * Gets all the models with the checked state and pushes them into an array
		 * @returns {Array}
		 */
		getIDsArray: function () {
			var models = this.where( {checked: true} ),
				array = [];

			_.each( models, function ( item ) {
				array.push( item.get( 'ID' ) );
			} );

			return array;
		},
		/**
		 * Returns the weekdays, or days on which a rolling campaign will start
		 * @returns {string}
		 */
		prepareAppend: function () {
			var models = this.where( {checked: true} ),
				collection = this,
				$return = '',
				last_element = models[models.length - 1],
				first_element = models[0];

			_.each( models, function ( item ) {
				if ( collection.length === 7 ) {
					var days = ThriveUlt.util.weekdays.weekdays_full;
					if ( models.length > 1 && last_element.get( 'ID' ) === item.get( 'ID' ) ) {
						$return += ' and ' + days[item.get( 'ID' )];
					} else if ( models.length >= 1 && first_element.get( 'ID' ) === item.get( 'ID' ) ) {
						$return += ' ' + days[item.get( 'ID' )];
					} else {
						$return += ', ' + days[item.get( 'ID' )];
					}
				} else {
					var ID = item.get( 'ID' ) + 1,
						j = ID % 10,
						k = ID % 100;

					if ( models.length > 1 && last_element.get( 'ID' ) === item.get( 'ID' ) ) {
						if ( j == 1 && k != 11 ) {
							$return += ' and ' + ID + 'st';
						} else if ( j == 2 && k != 12 ) {
							$return += ' and ' + ID + 'nd';
						} else if ( j == 3 && k != 13 ) {
							$return += ' and ' + ID + 'rd';
						} else {
							$return += ' and ' + ID + 'th';
						}
					} else if ( models.length >= 1 && first_element.get( 'ID' ) === item.get( 'ID' ) ) {
						if ( j == 1 && k != 11 ) {
							$return += ' ' + ID + 'st';
						} else if ( j == 2 && k != 12 ) {
							$return += ' ' + ID + 'nd';
						} else if ( j == 3 && k != 13 ) {
							$return += ' ' + ID + 'rd';
						} else {
							$return += ' ' + ID + 'th';
						}
					} else {
						if ( j == 1 && k != 11 ) {
							$return += ', ' + ID + 'st';
						} else if ( j == 2 && k != 12 ) {
							$return += ', ' + ID + 'nd';
						} else if ( j == 3 && k != 13 ) {
							$return += ', ' + ID + 'rd';
						} else {
							$return += ', ' + ID + 'th';
						}
					}
				}

			} );

			return $return;

		}
	} );

	/**
	 * Campaigns Collection
	 */
	ThriveUlt.collections.Campaigns = ThriveUlt.collections.Base.extend( {
		model: ThriveUlt.models.Campaign,
		/**
		 * Used to sort the collection
		 *
		 * @param model
		 * @returns {*}
		 */
		comparator: function ( model ) {
			return parseInt( model.get( 'order' ) );
		},
		removeSpecificModel: function ( ID ) {
			var _model = this.findWhere( {ID: parseInt( ID )} );
			if ( _model ) {
				this.remove( _model );
			}

			return this;
		},
		filter_archived: function ( archived ) {
			if ( archived === undefined ) {
				archived = true;
			}

			if ( archived === true ) {
				return this.where( {status: ThriveUlt.util.status.archived} );
			}

			return this.filter( function ( model ) {
				return model.get( 'status' ) === ThriveUlt.util.status.paused || model.get( 'status' ) === ThriveUlt.util.status.running;
			} );
		}
	} );

	/**
	 * Design Model
	 */
	ThriveUlt.models.Design = ThriveUlt.models.Base.extend( {
		defaults: {
			ID: '',
			state: ThriveUlt.util.states.normal
		},
		previous_state: null,
		/**
		 * This is not a WP post in DB so it will not have the ID property by default
		 * So we need to be consisted and set the id prop to ID
		 *
		 * @param data
		 */
		initialize: function ( data ) {
			if ( ! this.get( 'ID' ) && data.id ) {
				this.set( 'ID', data.id );
			}
			this.on( 'change:state', _.bind( this.state_changed, this ) );

			if ( data.children && ! ( data.children instanceof ThriveUlt.collections.Designs ) ) {
				this.set( 'children', new ThriveUlt.collections.Designs( data.children ) );
			}
		},
		/**
		 * maintain the previous state so we can restore it on fetch (if the design does not have a template yet)
		 *
		 * @param {object} model
		 * @param {string} state
		 */
		state_changed: function ( model, state ) {
			this.previous_state = state;
		},
		url: function () {
			var url = ThriveUlt.ajaxurl( 'action=' + ThriveUlt.ajax_actions.admin_controller + '&route=designs' );

			if ( this.get( 'ID' ) ) {
				url += '&ID=' + this.get( 'ID' );
			}

			return url;
		},
		parse: function ( data ) {
			if ( ! data.state ) {
				//set the state to default normal value
				data.state = this.previous_state ? this.previous_state : ThriveUlt.util.states.normal;
			}
			this.ensureCollectionData( data, {
				children: ThriveUlt.collections.Designs
			} );

			return data;
		},
		/**
		 * get the full URL to the thumbnail
		 * @return {string}
		 */
		thumb: function () {

			if ( ! this.get( 'thumb_url' ) ) {
				return ThriveUlt.t.Choose_a_template;
			}

			return this.get( 'thumb_url' );
		},
		validate: function ( attrs, options ) {
			if ( ! attrs.post_parent ) {
				return ThriveUlt.t.InvalidCampaign;
			}
			if ( ! attrs.post_type ) {
				return ThriveUlt.t.DesignTypeMissing;
			}
		},
		/**
		 * clears the refetch timer for this model
		 */
		clearRefetchTimer: function () {
			clearTimeout( this.refetch_timeout );
		},
		/**
		 * initialize a timeout for refetching the data from the server - this is done for auto-updating the design thumbnail if a template has been chosen
		 *
		 * @return void
		 */
		initRefetchTimer: function () {
			var self = this;

			function tick() {
				if ( ! self.get( 'thumb_url' ) ) {
					self.fetch();
					self.refetch_timeout = setTimeout( tick, 5000 );
				}
			}

			self.refetch_timeout = setTimeout( tick, 5000 );
		},
		getShortcode: function () {
			return '[tu_countdown id=' + this.get( 'post_parent' ) + ' design=' + this.get( 'ID' ) + '][/tu_countdown]';
		}
	} );

	/**
	 * Designs Collection
	 */
	ThriveUlt.collections.Designs = ThriveUlt.collections.Base.extend( {
		model: ThriveUlt.models.Design,
		campaign_id: 0,
		/**
		 * set the campaign ID so that this collection can be used as a stand-alone collection of designs
		 *
		 * @param {Number} ID
		 * @returns {ThriveUlt.collections.Designs}
		 */
		set_campaign_id: function ( ID ) {
			this.campaign_id = ID;
			return this;
		},
		url: function () {
			return ThriveUlt.ajaxurl( 'action=' + ThriveUlt.ajax_actions.admin_controller + '&route=designList&get_states=1&campaign_id=' + this.campaign_id );
		},
		/**
		 * mark each of the designs from this collection as selected or not, also the corresponding design states
		 *
		 * @param {ThriveUlt.collections.EventActions} event_actions
		 *
		 * @return {ThriveUlt.collections.Designs} allow chained calls
		 */
		mark_selected_event_actions: function ( event_actions ) {
			if ( ! event_actions || ! event_actions.length ) {
				return this;
			}
			event_actions.each( _.bind( function ( action ) {
				var design = this.findWhere( {ID: action.get( 'design' )} );
				if ( design ) {
					design.set( 'event_display', true );
					var _state = this._find_state( action.get( 'design' ), action.get( 'state' ) );
					if ( _state ) {
						design.set( 'event_state', action.get( 'state' ) );
					}
				}
			}, this ) );

			return this;
		},
		/**
		 * find a state from the design collection
		 *
		 * @param design_id
		 * @param state_id
		 * @returns {ThriveUlt.models.Design}
		 * @private
		 */
		_find_state: function ( design_id, state_id ) {
			var _design = this.get( design_id );
			if ( design_id == state_id ) {
				return _design;
			}
			if ( ! _design.get( 'children' ) ) {
				return _design;
			}
			return _design.get( 'children' ).get( state_id );
		}
	} );

	/**
	 * Event Model
	 */
	ThriveUlt.models.Event = ThriveUlt.models.Base.extend( {
		initialize: function ( data ) {
			var isCollection = this.get( 'actions' ) instanceof ThriveUlt.collections.EventActions;

			if ( ! isCollection ) {
				this.set( 'actions', new ThriveUlt.collections.EventActions( data.actions ) );
			}

			this.set( 'ID', data.id ? data.id : '' );
		},
		parse: function ( data ) {
			this.ensureCollectionData( data, {
				actions: ThriveUlt.collections.EventActions
			} );

			return data;
		},
		url: function () {

			var url = ajaxurl + '?action=' + ThriveUlt.ajax_actions.admin_controller + '&route=events';

			if ( this.get( 'ID' ) ) {
				url += '&ID=' + this.get( 'ID' );
			}

			return url;
		},
		validate: function ( attr ) {
			/** if event is not start or end */
			if ( ! attr.type || attr.type !== 'start' ) {
				/** check if hours or days are set */
				if ( ! attr.days && ! attr.hours ) {
					return this.validation_error( 'time', ThriveUlt.t.InvalidTriggerTime );
				}
				if ( attr.days < 0 || attr.hours < 0 ) {
					return this.validation_error( 'time', ThriveUlt.t.InvalidTriggerTime );
				}
			}
		},
		/**
		 * Return something if there is an error
		 *
		 * @param {ThriveUlt.models.Event} start_event
		 */
		validateTrigger: function ( start_event ) {

			if ( this.get( 'type' ) === ThriveUlt.event_type.start ) {
				return null;
			}

			var duration = parseInt( start_event.get( 'days' ) ) * 24 + parseInt( start_event.get( 'hours' ) ),
				trigger = parseInt( this.get( 'days' ) * 24 + parseInt( this.get( 'hours' ) ) );

			/**
			 * Trigger time should be strict smaller than campaign duration
			 * If they are equal then we don't know which event should be activated: start or next event
			 */
			if ( trigger >= duration ) {
				return this.validation_error( 'time', ThriveUlt.t.InvalidEventTriggerTime );
			}
		},
		getDuration: function () {
			var days = parseInt( this.get( 'days' ) ),
				hours = parseInt( this.get( 'hours' ) );

			return days * 24 + hours;
		},
		setTime: function ( value, unit ) {
			if ( unit === 'days' ) {
				this.set( 'days', value );
				this.set( 'hours', 0 );
			}
			if ( unit === 'hours' ) {
				this.set( 'days', 0 );
				this.set( 'hours', value );
			}
		},
		getLabel: function () {
			if ( this.get( 'label' ) && this.get( 'label' ).length ) {
				return this.get( 'label' );
			}

			if ( this.get( 'hours' ) == 0 ) {
				return '-' + ThriveUlt.util.plural( '%s ' + ThriveUlt.t.Day, '%s ' + ThriveUlt.t.Days, this.get( 'days' ) );
			}

			if ( this.get( 'days' ) == 0 ) {
				if ( parseInt( this.get( 'hours' ) ) > 24 ) {
					var int_days = parseInt( this.get( 'hours' ) / 24 );
					var h_days = '-' + ThriveUlt.util.plural( '%s ' + ThriveUlt.t.Day, '%s ' + ThriveUlt.t.Days, int_days );
					var h_hours = ThriveUlt.util.plural( '%s ' + ThriveUlt.t.Hour, '%s ' + ThriveUlt.t.Hours, this.get( 'hours' ) - (int_days * 24) );

					return h_days + ' and ' + h_hours;
				}
				return '-' + ThriveUlt.util.plural( '%s ' + ThriveUlt.t.Hour, '%s ' + ThriveUlt.t.Hours, this.get( 'hours' ) );
			}


			var days = ThriveUlt.util.plural( "%s " + ThriveUlt.t.Day, '%s ' + ThriveUlt.t.Days, this.get( 'days' ) ),
				hours = ThriveUlt.util.plural( "%s " + ThriveUlt.t.Hour, '%s ' + ThriveUlt.t.Hours, this.get( 'hours' ) );

			return '-' + days + '<br/>' + hours;
		},
		/**
		 * builds the actions Collection from a list of designs. Each design has its own property for the selected state
		 *
		 * @param {ThriveUlt.collections.Designs} designs
		 * @return {ThriveUlt.models.Event} allows chained calls
		 */
		buildActionsFromDesigns: function ( designs ) {
			var actions = new ThriveUlt.collections.EventActions();
			designs.each( function ( design ) {
				if ( ! design.get( 'event_display' ) ) {
					return true;
				}
				actions.add( {
					design: design.get( 'ID' ),
					state: design.get( 'event_state' ),
					key: ThriveUlt.data.actions.design_show.key
				} );
			} );

			this.set( 'actions', actions );

			return this;
		}
	} );

	/**
	 * Events collection
	 */
	ThriveUlt.collections.Events = ThriveUlt.collections.Base.extend( {
		model: ThriveUlt.models.Event,
		url: ThriveUlt.ajaxurl( 'action=' + ThriveUlt.ajax_actions.admin_controller + '&route=events' ),
		comparator: function ( item ) {
			return - (
				parseInt( item.get( 'hours' ) ) + item.get( 'days' ) * 24
			);
		},
		/**
		 * Remove actions from events when a design is deleted
		 * @param deleted
		 */
		removeEvents: function ( deleted ) {
			this.each( function ( event ) {
				if ( event.get( 'type' ) === ThriveUlt.event_type.end ) {
					return;
				}
				var actions = event.get( 'actions' ),
					action_to_delete = actions.filter( function ( action ) {
						return action.get( 'design' ) == deleted.get( 'ID' )
					} );
				action_to_delete.forEach( function ( action ) {

					if ( action === undefined ) {
						return;
					}
					actions.remove( action );
				} );
			} );
		}
	} );

	/**
	 * Event Action
	 */
	ThriveUlt.models.EventAction = ThriveUlt.models.Base.extend( {
		validate: function ( attr ) {
			if ( ! attr.key || attr.key === '0' ) {
				return this.validation_error( 'action', ThriveUlt.t.SelectAction );
			}
			if ( ! ThriveUlt.data.actions[attr.key] ) {
				return this.validation_error( 'action', ThriveUlt.t.InvalidAction );
			}
			/**
			 * validation for design and action
			 * covers the following actions:
			 *      - design_show
			 *      - design_switch_state
			 */
			if ( attr.key === ThriveUlt.data.actions.design_show.key || attr.key === ThriveUlt.data.actions.design_switch_state.key ) {
				var err = [];
				if ( ! attr.design || attr.design === '0' ) {
					err.push( this.validation_error( 'design', ThriveUlt.t.SelectDesign ) );
				}
				if ( ! attr.state || attr.state === '0' ) {
					err.push( this.validation_error( 'state', ThriveUlt.t.SelectState ) );
				}
				if ( err.length ) {
					return err;
				}
			}
			/**
			 * validation for campaign move
			 */
			if ( ThriveUlt.data.actions.campaign_move && attr.key === ThriveUlt.data.actions.campaign_move.key ) {
				if ( ! attr.campaign || attr.campaign === '0' ) {
					return ThriveUlt.t.SelectCampaign;
				}
			}
		},
		getFullName: function () {
			if ( this.get( 'key' ) !== 'design_show' ) {
				return this.get( 'name' );
			}
			if ( this.get( 'event_full_name' ) ) {
				return this.get( 'event_full_name' );
			}
			/**
			 * dynamically build the event name from the designs
			 */
			var designs = ThriveUlt.globals.campaign.get( 'designs' );
			var design_id = this.get( 'design' ),
				design = designs.get( design_id ),
				state_id = this.get( 'state' ),
				state = designs._find_state( design_id, state_id ),
				_name = '';
			if ( ! design || ! state ) {
				return '';
			}
			_name = design.get( 'post_title' );
			_name += ' (' + ( design_id == state_id ? ThriveUlt.t.main_state : state.get( 'post_title' )) + ')';
			_name = ThriveUlt.util.printf( ThriveUlt.t.display_countdown_design, '<strong>' + _name + '</strong>' );

			this.set( 'event_full_name', _name );
			return _name;
		}
	} );

	/**
	 * EventActions collection
	 */
	ThriveUlt.collections.EventActions = ThriveUlt.collections.Base.extend( {
		model: ThriveUlt.models.EventAction
	} );

	/**
	 * breadcrumb link model
	 */
	ThriveUlt.models.BreadcrumbLink = ThriveUlt.models.Base.extend( {
		defaults: {
			ID: '',
			hash: '',
			label: '',
			full_link: false
		},
		/**
		 * we pass only hash and label, and build the ID based on the label
		 *
		 * @param {object} att
		 */
		initialize: function ( att ) {
			if ( ! this.get( 'ID' ) ) {
				this.set( 'ID', att.label.split( ' ' ).join( '' ).toLowerCase() );
			}
			this.set( 'full_link', att.hash.match( /^http/ ) );
		},
		/**
		 *
		 * @returns {String}
		 */
		get_url: function () {
			return this.get( 'full_link' ) ? this.get( 'hash' ) : ( '#' + this.get( 'hash' ));
		}
	} );

	/**
	 * Model for conversion event
	 * @type {void|*}
	 */
	ThriveUlt.models.ConversionEvent = ThriveUlt.models.Event.extend( {
		defaults: {
			ID: '',
			actions: [],
			trigger_options: {
				trigger: '',
				trigger_ids: '',
				event: ''
			}
		},
		initialize: function ( data ) {
			var options = this.get( 'trigger_options' );

			if ( ! options ) {
				this.set( 'trigger_options', data.trigger_options );
			}

			this.set( 'ID', data.id ? data.id : '' );
		},
		parse: function ( data ) {

			return data;
		},
		validate: function ( attrs, options ) {
			var t = attrs.trigger_options,
				errors = [];

			if ( ! t.event || parseInt( t.event ) === 0 ) {
				errors.push( this.validation_error( 'action_event', ThriveUlt.t.Event_type_required ) );
			}
			if ( errors.length ) {
				return errors;
			}

			if ( t.event === ThriveUlt.util.conversion_event.move && ! t.end_id ) {
				return ThriveUlt.t.InvalidEndId;
			}
		},
		validateTrigger: function () {
			var t = this.get( 'trigger_options' ),
				errors = [];

			// check if the user selected any trigger type
			if ( ! t.trigger || parseInt( t.trigger ) === 0 ) {
				errors.push( this.validation_error( 'trigger', ThriveUlt.t.Trigger_required ) );
			}

			if ( errors.length ) {
				return errors;
			}

			// check if any trigger has been chosen
			// I don't really agree with these kinds of if{}else{} statements
			if ( ! t.trigger_ids ) {
				if ( t.trigger === ThriveUlt.util.trigger_type.conversion ) {
					return ThriveUlt.t.Select_conversion_target;
				}
				if ( t.trigger === ThriveUlt.util.trigger_type.specific ) {
					errors.push( this.validation_error( 'conversion_page', ThriveUlt.t.Choose_conversion_page ) );
				}
			}
			if ( errors.length ) {
				return errors;
			}
		}
	} );

	/**
	 * Conversion Events Collection
	 * @type {void|*}
	 */
	ThriveUlt.collections.ConversionEvents = ThriveUlt.collections.Base.extend( {
		model: ThriveUlt.models.ConversionEvent
	} );

	/**
	 * Linked campaigns model
	 * @type {void|*}
	 */
	ThriveUlt.models.LinkedTo = ThriveUlt.models.Base.extend( {} );

	/**
	 * Campaigns that are linked to this campaign collection
	 * @type {void|*}
	 */
	ThriveUlt.collections.LinkedToCollection = ThriveUlt.collections.Base.extend( {
		model: ThriveUlt.models.LinkedTo
	} );

	/**
	 * Breadcrumb links collection
	 *
	 * not sure if anything else is needed here
	 */
	ThriveUlt.collections.Breadcrumbs = ThriveUlt.collections.Base.extend( {
		model: ThriveUlt.models.Base.extend( {
			defaults: {
				hash: '',
				label: ''
			}
		} ),
		/**
		 * helper function allows adding items to the collection easier
		 *
		 * @param {string} route
		 * @param {string} label
		 */
		add_page: function ( route, label ) {
			var _model = new ThriveUlt.models.BreadcrumbLink( {
				hash: route,
				label: label
			} );
			return this.add( _model );
		}
	} );
})( jQuery );
;/**
 * Thrive Ultimatum Modal views
 */
var ThriveUlt = ThriveUlt || {};
ThriveUlt.views = ThriveUlt.views || {};

(function ( $ ) {

	$( function () {

		/**
		 * Modal Steps View
		 * If a wizard is needed implement or extend this view
		 */
		ThriveUlt.views.ModalSteps = TVE_Dash.views.Modal.extend( {
			stepClass: '.tvu-modal-step',
			currentStep: 0,
			$step: null,
			events: {
				'click .tvu-modal-next-step': "next",
				'click .tvu-modal-prev-step': "prev"
			},
			afterRender: function () {
				this.steps = this.$el.find( this.stepClass ).hide();
				this.gotoStep( 0 );
				return this;
			},
			gotoStep: function ( index ) {
				var step = this.steps.hide().eq( index ).show(),
					self = this;
				this.$step = step;
				setTimeout( function () {
					self.input_focus( step );
				}, 50 );

				this.currentStep = index;

				return this;
			},
			next: function () {
				this.beforeNext();
				this.gotoStep( this.currentStep + 1 );
				this.afterNext();
			},
			prev: function () {
				this.beforePrev();
				this.gotoStep( this.currentStep - 1 );
				this.afterPrev();
			},
			beforeNext: function () {
				return this;
			},
			afterNext: function () {
				return this;
			},
			beforePrev: function () {
				return this;
			},
			afterPrev: function () {
				return this;
			}
		} );

		/**
		 * Add Design Modal View
		 */
		ThriveUlt.views.ModalAddDesign = TVE_Dash.views.Modal.extend( {
			template: TVE_Dash.tpl( 'modals/new-design' ),
			type: '',
			events: {
				'click .tvu-save-new-design': 'save',
				'click div[data-type]': 'select_type'
			},
			afterRender: function () {
				this.$el.addClass( 'tvu-modal-design-type' );

				this.design_model = new ThriveUlt.models.Design( {
					post_parent: this.model.get( 'ID' ),
					post_type: this.type
				} );
				ThriveUlt.util.data_binder( this, this.design_model );

				return this;
			},
			select_type: function ( event ) {
				var $target = $( event.currentTarget ),
					type = $target.data( 'type' ),
					target_card = $target.children( '.tvd-card' ),
					siblingCards = this.$el.find( '.tvu-campaign-selector' ).children( '.tvd-card' );

				ThriveUlt.select_card( target_card, siblingCards, 'tvu-selected-design' );

				if ( type === 'shortcode' ) {
					this.$( '#tu-design-name-wrapper' ).show();
					this.$( '#tu-design-name' ).focus().select();
				} else {
					this.$( '#tu-design-name-wrapper' ).hide();
				}

				this.design_model.set( 'post_type', type );
			},
			save: function () {
				if ( ! this.design_model.isValid() ) {
					return;
				}
				var self = this,
					model = this.design_model;

				TVE_Dash.showLoader();

				var xhr = model.save( {wait: true} );
				if ( xhr ) {
					xhr.done( function ( response, status, options ) {
						model.set( response );
						model.set( 'ID', response.id );
						self.model.get( 'designs' ).add( model );
						self.model.trigger( 'tve_ult_campaign_saved' );
						model.initRefetchTimer();
						TVE_Dash.hideLoader();
					} );
					xhr.error( function ( errorObj ) {
						TVE_Dash.hideLoader();
					} );
					xhr.always( function () {
						self.close();
					} );
				}
			}
		} );

		ThriveUlt.views.ModalEditCampaignType = TVE_Dash.views.Modal.extend( {
			className: 'tvd-modal-fixed-footer tvd-modal',
			template: TVE_Dash.tpl( 'modals/edit-campaign' ),
			events: {
				'click .tvu-campaign-selector': 'showCampaignOptions',
				'click .tvu-save-campaign-type': 'save'
			},
			afterInitialize: function () {
				this.listenTo( this.model, 'change:type', this.renderSettings );
			},
			afterRender: function () {
				this.$el.addClass( 'tvu-campaign-modal' );
				this.renderSettings();
				return this;
			},
			renderSettings: function () {
				var type = this.model.get( 'type' ),
					collection = this.collection,
					shortcodes = null;

				if ( ! type ) {
					this.model.set( 'edit_mode', 'new' );
				}

				this.model.set( 'settings_modal', 'modal' );

				if ( ! ThriveUlt.views['CampaignType' + ThriveUlt.util.upperFirst( type ) + 'State'] ) {
					return;
				}

				if ( type == ThriveUlt.util.campaignType.evergreen ) {
					if ( ThriveUlt.data.lead_groups === false ) {
						collection = null;
					} else {
						collection = new ThriveUlt.collections.LeadGroupsCollection( ThriveUlt.data.lead_groups );
						shortcodes = new ThriveUlt.collections.ShortcodesCollection( ThriveUlt.data.shortcodes );
					}
				}

				if ( ! this[type] ) {
					this[type] = new ThriveUlt.views['CampaignType' + ThriveUlt.util.upperFirst( type ) + 'State']( {
						el: this.$el.find( '#tvu-campaign-type-options' )[0],
						model: this.model,
						collection: collection,
						shortcodes: shortcodes
					} );
				} else {
					this[type].setElement( this.$( '#tvu-campaign-type-options' )[0] );
				}

				this[type].render();
				this[type].$el.hide().fadeIn().slideDown();

				return this;
			},
			save: function () {
				var repeatOn = this.collection.getIDsArray(),
					rolling = this.model.get( 'rolling_type' );

				if ( ! rolling ) {
					this.model.set( 'rolling_type', 'daily' );
				}

				this.model.get( 'settings' ).repeatOn = repeatOn;

				this.tvd_clear_errors();

				if ( ! this.model.isValid() ) {
					return this.tvd_show_errors( this.model );
				}

				var self = this;
				TVE_Dash.showLoader();

				this.model.cleanModel();
				this.model.save().done( function () {
					TVE_Dash.hideLoader();
					self.model.getSummary();
					self.model.unset( 'edit_mode' );
					self.model.unset( 'settings_modal' );
					try {
						var campaign = ThriveUlt.globals.campaigns.findWhere( {ID: self.model.get( 'ID' )} );
						if ( campaign instanceof ThriveUlt.models.Campaign ) {
							campaign.set( self.model.toJSON() );
						}
						//update current global
						ThriveUlt.globals.campaign.set( self.model.toJSON() );
					} catch ( error ) {
						console.log( 'Error: ' + error );
					}
					//throw this trigger here so we know when to fetch timeline events
					self.original_model.trigger( 'tve_ult_campaign_saved' );
				} ).error( function () {
					//todo: error handling
					TVE_Dash.hideLoader();
				} ).always( function () {
					//todo: do we really want to close the modal each time?
					self.close();
				} );
			},
			/**
			 * Changes states of the campaign types
			 * @param e
			 */
			showCampaignOptions: function ( e ) {
				var $this = e.currentTarget,
					type = $( $this ).attr( 'data-type' ),
					targetCard = $( $this ).children( '.tvd-card' ),
					siblingCards = this.$el.find( '.tvd-card' );

				ThriveUlt.select_card( targetCard, siblingCards, 'tvu-selected-design' );

				this.model.set( 'type', type );
			},
			onClose: function () {
				$( '.tvd-material-tooltip' ).hide();
			}
		} );

		ThriveUlt.views.ModalEditDateSettings = TVE_Dash.views.Modal.extend( {
			className: 'tvd-modal-fixed-footer tvd-modal tvu-edit-date-settings-modal',
			template: TVE_Dash.tpl( 'modals/edit-date-settings' ),
			events: {
				'change #tvu-date-format-setting': 'changeDateFormat',
				'change #tvu-time-format-setting': 'changeTimeFormat',
				'change #tvu-timezone-setting': 'changeTimeZone',
				'click .tvu-save-date-settings': 'save'
			},
			afterOpen: function () {
				var offset = (this.model.get( 'offset' ));

				if ( offset ) {
					this.$el.find( '#tvu-timezone-setting option[value="' + offset + '"]' ).attr( 'selected', 'selected' );
				}
			},
			changeDateFormat: function ( e ) {
				this.model.set( 'date_format', e.target.value );
			},
			changeTimeFormat: function ( e ) {
				this.model.set( 'time_format', e.target.value );

			},
			changeTimeZone: function ( e ) {
				this.model.set( 'offset', e.target.value );
			},
			save: function () {
				this.tvd_clear_errors();

				if ( ! this.model.isValid() ) {
					return this.tvd_show_errors( this.model );
				}

				var self = this;

				this.model.save().done( function () {
					TVE_Dash.hideLoader();
				} ).error( function () {
					//todo: error handling
					TVE_Dash.hideLoader();
				} ).always( function () {
					//todo: do we really want to close the modal each time?
					self.close();
				} );
			}
		} );

		/**
		 * Edit Lockdown settings modal
		 */
		ThriveUlt.views.ModalEditLockDownSettings = TVE_Dash.views.Modal.extend( {
			className: 'tvd-modal-fixed-footer tvd-modal',
			template: TVE_Dash.tpl( 'modals/edit-lockdown' ),
			events: {
				'click .tvu-save-lockdown-settings': 'save',
				'click .tve_ult_add_promotion_field': function () {
					this.addPromotionField();
				},
				'click .tve-ult-remove-button': 'removePromotionField',
				'change #tvu-autoresponder-type': 'generateAllEmailLinks'
			},
			afterInitialize: function () {
				this.listenTo( this.collection, 'add', function ( model ) {
					this.renderOnePromotion( model );
					this.renderOneLink( model );
				} );
				this.listenTo( this.collection, 'remove', this.renderPromotionUrls );
			},
			afterOpen: function () {
				TVE_Dash.bindZClip( this.$el.find( 'a.tve-copy-to-clipboard' ) );
			},
			afterRender: function () {
				this.renderPromotionUrls();

				var view = this,
					model = this.model,
					$pre_access = this.$( '#tvu-lockdown-pre-access-url' ),
					$expired = this.$( '#tvu-lockdown-expired-url' ),
					$service = this.$( '#tvu-autoresponder-type' );

				this.$el.addClass( 'tvu-lockdown-modal' );

				model.set( 'lockdown', true );

				function no_value_callback() {
					var $this = $( this );
					model.get( 'lockdown_settings' )[$this.data( 'field' )] = null;
				}

				function change() {
					var $this = $( this );
					model.get( 'lockdown_settings' )[$this.data( 'field' )] = {value: $this.val()};
					if ( $this.data( 'field' ) === 'promotion' ) {
						view.$( '#tvu-autoresponder-type' ).change();
					}
				}

				function select( event, ui ) {
					var $input = $( this );
					model.get( 'lockdown_settings' )[$input.data( 'field' )] = ui.item;
					if ( $input.data( 'field' ) === 'promotion' ) {
						view.$( '#tvu-autoresponder-type' ).change();
					}
				}

				var defaults = {
					url: ThriveUlt.ajaxurl( 'action=' + ThriveUlt.ajax_actions.admin_controller + '&route=progressiveGetPosts' ),
					no_value_callback: no_value_callback,
					change_callback: change,
					select: select
				};

				new ThriveUlt.PostSearch( $pre_access, defaults );
				new ThriveUlt.PostSearch( $expired, defaults );


				if ( this.collection.length > 0 ) {
					$service.find( 'option[value="' + model.get( 'lockdown_settings' ).service + '"]' ).attr( 'selected', 'seletced' ).select2();
				}

				return this;
			},
			generateAllEmailLinks: function ( e ) {
				var collection = this.collection,
					tag = $( "option:selected", e.target ).attr( "data-tag" ),
					$element = this.$el;

				collection.each( function ( link ) {
					if ( link.get( 'link' ) ) {
						var url = link.get( 'link' ).split( 'tu_em=' )[0],
							key = collection.indexOf( link ),
							new_url = url + 'tu_em=' + tag;

						$element.find( '#tvu-email-link' + key ).val( new_url );
						link.set( 'link', new_url, {silent: true} );
					}
				}, this );

				this.model.get( 'lockdown_settings' ).service = e.target.value;

				return this;
			},
			save: function () {
				this.tvd_clear_errors();

				var self = this;

				var errors = self.collection.cleanCollection();
				this.model.get( 'lockdown_settings' ).promotion = self.collection;

				if ( ! this.model.isValid() ) {
					return this.tvd_show_errors( this.model );
				}

				if ( ! Object.keys( errors ).length === 0 ) {
					return this.tvd_show_errors( this.model );
				}

				TVE_Dash.showLoader();

				this.model.save().done( function () {
					TVE_Dash.hideLoader();
					try {
						var campaign = ThriveUlt.globals.campaigns.findWhere( {ID: self.model.get( 'ID' )} );

						if ( campaign instanceof ThriveUlt.models.Campaign ) {
							campaign.set( self.model.toJSON() );
						}

						//update current global
						ThriveUlt.globals.campaign.set( self.model.toJSON() );
					} catch ( error ) {
						console.log( 'Error: ' + error );
					}
					//throw this trigger here so we know when to fetch timeline events
					self.original_model.trigger( 'tve_ult_campaign_saved' );
				} ).error( function () {
					//todo: error handling
					TVE_Dash.hideLoader();
				} ).always( function () {
					//todo: do we really want to close the modal each time?
					self.close();
				} );
			},
			addPromotionField: function () {
				var new_model = new ThriveUlt.models.PromotionURLModel;
				this.collection.add( new_model );

				ZeroClipboard.Client.prototype.destroy();
				this.$el.find( '.zclip' ).remove();
				TVE_Dash.bindZClip( this.$el.find( 'a.tve-copy-to-clipboard' ) );
			},
			removePromotionField: function ( e ) {
				var container = $( e.target ).closest( '.tvd-input-field' ),
					$input = container.find( '.tvu-promotion-url' ),
					num = parseInt( $input.prop( 'id' ).match( /\d+/g ), 10 );

				this.collection.remove( this.collection.at( num ) );
			},
			renderPromotionUrls: function () {
				var urls = this.collection;
				this.$el.find( '.tve-ult-promotion-wrapper' ).empty();
				this.$el.find( '.tvu-url-to-copy-wrapper' ).empty();

				urls.each( this.renderOnePromotion, this );
				urls.each( this.renderOneLink, this );
			},
			renderOnePromotion: function ( model ) {
				var view = new ThriveUlt.views.LockdownPromotionURL( {
					model: model,
					collection: this.collection,
					parent_model: this.model
				} );

				this.$el.find( '.tve-ult-promotion-wrapper' ).append( view.render().$el );
			},
			renderOneLink: function ( model ) {
				var view = new ThriveUlt.views.LockdownServiceURL( {
					model: model,
					collection: this.collection
				} );

				this.$el.find( '.tvu-url-to-copy-wrapper' ).append( view.render().$el );
			}
		} );

		/**
		 *  Modal Add/Edit Event
		 *
		 *  @field {ThriveUlt.collections.Designs} designs the list of all designs, fetched from the server
		 */
		ThriveUlt.views.ModalEditEvent = TVE_Dash.views.Modal.extend( {
			template: TVE_Dash.tpl( 'modals/edit-event' ),
			className: 'tvd-modal tvd-modal-fixed-footer',
			events: function () {
				return _.extend( {}, ThriveUlt.views.ModalSteps.prototype.events, {
					'change #tvu-event-time': 'setTime',
					'change #tvu-event-unit': 'setTime',
					'click .tvu-event-save': 'saveEvent'
				} );
			},
			/**
			 * Render a row for each of the designs and append it to the table
			 */
			renderDesignItems: function () {
				var $table = this.$( '#tvu-event-designs' );
				this.designs.each( function ( item ) {
					$table.append( (new ThriveUlt.views.EventModalDesignItem( {
						model: item
					} ) ).render().$el );
				} );
			},
			afterRender: function () {
				this.renderDesignItems();
				this.$el.addClass( 'tvu-add-timeline-event-modal' );

				this.steps = this.$el.find( this.stepClass ).hide();
				this.$actionsList = this.$el.find( '#tvu-event-actions-list' );
				this.$triggerOptions = this.$el.find( '#tvu-trigger-options' );

				if ( this.model.get( 'type' ) !== ThriveUlt.event_type.start ) {
					this.$triggerOptions.show();
				}

				/**
				 * if there are hours that means the user set the event in hours
				 * an we should pre-fill the form accordingly
				 */
				var time = parseInt( this.model.get( 'days' ) );
				if ( this.model.get( 'hours' ) !== undefined && this.model.get( 'hours' ) != 0 ) {
					this.$el.find( '#tvu-event-unit' ).val( 'hours' );
					TVE_Dash.materialize( this.$el );
					time = parseInt( this.model.get( 'days' ) ) * 24 + parseInt( this.model.get( 'hours' ) );
				}
				if ( ! isNaN( time ) ) {
					this.$el.find( '#tvu-event-time' ).val( time );
				}

				return this;
			},
			/**
			 * Puts back the copy action models into this.model and
			 * Does an ajax request to save this.model
			 */
			saveEvent: function () {
				this.tvd_clear_errors();

				this.model.buildActionsFromDesigns( this.designs );

				if ( ! this.model.isValid() ) {
					return this.tvd_show_errors();
				}

				var invalid = this.model.validateTrigger( this.collection.findWhere( {type: 'start'} ) );
				if ( invalid ) {
					return this.tvd_show_errors( invalid );
				}

				TVE_Dash.showLoader();

				var self = this;

				this.model.save( {}, {
					success: function ( model, response ) {
						TVE_Dash.hideLoader();
						self.close();
						if ( ! model.get( 'ID' ) ) {
							model.set( 'ID', response.id );
							model.set( 'status', 'new' );
							self.collection.add( model );
						} else {
							var status = self.model.getDuration() === self.original_model.getDuration() ? 'edited' : 'duration_changed',
								original_index = self.collection.indexOf( self.original_model );

							self.model.set( 'label', null );
							self.original_model.set( model.attributes );
							self.original_model.set( 'status', status );

							self.collection.sort();

							if ( original_index === self.collection.indexOf( self.original_model ) ) {
								self.original_model.unset( 'status' );
							}

							self.collection.trigger( 'update' );
						}
					},
					error: function ( model, response ) {
						TVE_Dash.err( response.responseText );
						TVE_Dash.hideLoader();
					}
				} );
			},
			setTime: function ( event ) {
				var $target = $( event.currentTarget );
				if ( $target.is( 'input' ) ) {
					this.model.setTime( parseInt( $target.val() ), this.$el.find( '#tvu-event-unit' ).val() );
				} else {
					this.model.setTime( parseInt( this.$el.find( '#tvu-event-time' ).val() ), $target.val() );
				}
			}
		} );

		/**
		 * Copy Campaign Modal View
		 */
		ThriveUlt.views.ModalCopyCampaign = ThriveUlt.views.ModalSteps.extend( {
			template: TVE_Dash.tpl( 'modals/copy-campaign' ),
			events: {
				'click .tvd-modal-submit': 'save'
			},
			afterRender: function () {
				this.$el.addClass( 'tvu-campaign-modal' );
				this.renderSettings();
				return this;
			},
			renderSettings: function () {
				var type = this.model.get( 'type' );
				this.settings_collection = new ThriveUlt.collections.RollingCollection();

				if ( ! type ) {
					this.model.set( 'edit_mode', 'new' );
				}

				this.model.set( 'settings_modal', 'modal' );

				if ( ! ThriveUlt.views['CampaignType' + ThriveUlt.util.upperFirst( type ) + 'State'] ) {
					return;
				}

				if ( type == ThriveUlt.util.campaignType.evergreen ) {
					if ( ThriveUlt.data.lead_groups === false ) {
						this.settings_collection = null;
					} else {
						this.settings_collection = new ThriveUlt.collections.LeadGroupsCollection( ThriveUlt.data.lead_groups );
						this.shortcodes = new ThriveUlt.collections.ShortcodesCollection( ThriveUlt.data.shortcodes );
					}
				}
				var view = new ThriveUlt.views['CampaignType' + ThriveUlt.util.upperFirst( type ) + 'State']( {
					el: this.$el.find( '#tvu-campaign-type-options' ),
					model: this.model,
					collection: this.settings_collection,
					shortcodes: this.shortcodes
				} );

				view.render();
			},
			save: function () {
				var type = this.model.get( 'type' );
				if ( type == ThriveUlt.util.campaignType.rolling ) {
					var repeatOn = this.settings_collection.getIDsArray(),
						rolling = this.model.get( 'rolling_type' );
					if ( ! rolling ) {
						this.model.set( 'rolling_type', 'daily' );
					}
					this.model.get( 'settings' ).repeatOn = repeatOn;
				}

				this.tvd_clear_errors();

				if ( ! this.model.isValid() ) {
					return this.tvd_show_errors( this.model );
				}

				var self = this;
				TVE_Dash.showLoader();
				this.model.cleanModel();
				this.model.save().done( function ( response ) {
					self.model.set( 'ID', response );
					self.model.set( 'post_title', ThriveUlt.t.Copy_of + self.model.get( 'post_title' ) );
					self.model.set( 'status', ThriveUlt.util.status.paused );
					self.model.set( 'chart_data', '' );
					self.collection.add( self.model, {at: self.collection.length} );
					TVE_Dash.hideLoader();
				} ).error( function () {
					//todo: error handling
					TVE_Dash.hideLoader();
				} ).always( function () {
					//todo: do we really want to close the modal each time?
					self.close();
				} );
			}
		} );

		/**
		 * New Campaign Modal View
		 */
		ThriveUlt.views.ModalNewCampaign = ThriveUlt.views.ModalSteps.extend( {
			template: TVE_Dash.tpl( 'modals/new-campaign' ),
			events: function () {
				return _.extend( {}, ThriveUlt.views.ModalSteps.prototype.events, {
					'click .tvu-new-campaign-tpl': function ( event ) {
						var targetCard = $( event.target ).parents(),
							siblingCards = this.$el.find( '.tvu-new-campaign-tpl' ).parents();
						ThriveUlt.select_card( targetCard, siblingCards, 'tvu-selected-design' );

						this.model.set( 'tpl', event.target.dataset.id );
						this.renderDescription();
					},
					'click .tvd-modal-submit': 'save'
				} );
			},
			afterInitialize: function () {
				this.listenTo( this.model, 'change:tpl', _.bind( function () {
					var _tpl = this.data.templates.findWhere( {id: this.model.get( 'tpl' )} );
					this.model.set( 'post_title', _tpl.get( 'is_empty' ) ? '' : _tpl.get( 'name' ) );
				}, this ) );
			},
			afterRender: function () {
				this.steps = this.$el.find( this.stepClass ).hide();
				this.gotoStep( 0 );
				this.renderTemplates();
				this.renderDescription();

				ThriveUlt.util.data_binder( this );

				return this;
			},
			renderTemplates: function () {
				var $wrapper = this.$el.find( '.tvu-new-campaign-templates' );
				$wrapper.empty();

				this.data.templates.each( function ( template, _index ) {
					$wrapper.append( TVE_Dash.tpl( 'modals/new-campaign-tpl', {
						item: template,
						index: _index + 1
					} ) );
				}, this );

				return this;
			},
			renderDescription: function () {
				var $wrapper = this.$el.find( '.tvu-new-campaign-description' ),
					tpl = this.model.get( 'tpl' );

				if ( tpl ) {
					var description = this.data.templates.at( tpl );
					$wrapper.empty().append( '<span>' + description.get( 'description' ) + '</span>' );
				}
			},
			next: function () {
				if ( ! this.model.get( 'tpl' ) ) {
					return TVE_Dash.err( ThriveUlt.t.CampaignMissingTemplate );
				}

				this.gotoStep( this.currentStep + 1 );
			},
			save: function () {
				this.model.set( 'skip_settings_validation', true );

				if ( ! this.model.isValid() ) {
					/* error message is shown automatically from the "invalid" listener setup in the data_binder function */
					return;
				}

				var self = this;
				TVE_Dash.showLoader();
				this.model.set( 'template_values', true );
				this.model.save().done( function ( response ) {
					self.model.set( 'ID', response );
					self.collection.add( self.model, {at: self.collection.length} );
					ThriveUlt.globals.campaigns.add( self.model, {at: self.collection.length} );
					self.close();
					ThriveUlt.router.navigate( '#dashboard/campaign/' + response, {trigger: true} );
				} ).error( function () {
					//todo: error handling
					TVE_Dash.hideLoader();
				} ).always( function () {
					TVE_Dash.showLoader();
				} );
			}
		} );

		/**
		 * Add Conversion Event Modal
		 * @type {void|*}
		 */
		ThriveUlt.views.ModalConversionEvent = ThriveUlt.views.ModalSteps.extend( {
			template: TVE_Dash.tpl( 'modals/new-conversion-event' ),
			events: function () {
				return _.extend( {}, ThriveUlt.views.ModalSteps.prototype.events, {
					'change #tvu-select-campaign-trigger': 'changeTriggers',
					'change #tvu-select-campaign-event': 'changeEvents',
					'click #tvu-add-evergreen-campaign': 'addEvergreenCampaign',
					'click .tvu-continue-events': 'showEvents',
					'click .tvu-save-new-evergreen-campaign': 'createEvergreenCampaign',
					'click #tvu-new-campaign-repeat-campaign-switch': 'toggleEndCampaign',
					'click .tvu-save-new-conversion-event': 'save'
				} );
			},
			afterInitialize: function () {
				this.listenTo( ThriveUlt.globals.campaigns, 'add', this.renderEvents );
			},
			afterRender: function () {
				this.$el.addClass( 'tvu-conversion-event-modal' );
				this.renderTriggers();
				this.renderEvents();
				this.renderSummary();

				this.steps = this.$el.find( this.stepClass ).hide();
				this.gotoStep( 0 );

				TVE_Dash.materialize( this.$el );

				var $select2 = this.$el.find( 'select' ).select2();
				$select2.each( function () {
					var $this = $( this );
					$this.on( 'select2:open', function () {
						$this.data( 'select2' ).$dropdown.addClass( 'tvu-conversion-event-select' );
					} );
				} );

				return this;
			},
			renderSummary: function () {
				var summary = new ThriveUlt.views.ConversionSummary( {
					el: this.$el.find( '#tvu-trigger-description' ),
					model: this.model
				} );

				summary.render();
			},
			renderTriggers: function () {
				var trigger = this.model.get( 'trigger_options' ).trigger,
					collection = new ThriveUlt.collections.LeadGroupsCollection(),
					shortcodes = new ThriveUlt.collections.ShortcodesCollection();

				if ( ThriveUlt.data.lead_groups ) {
					collection.reset( ThriveUlt.data.lead_groups );
				}

				if ( ThriveUlt.data.shortcodes ) {
					shortcodes.reset( ThriveUlt.data.shortcodes );
				}

				if ( ! trigger || parseInt( trigger ) === 0 ) {
					return;
				}

				var view = new ThriveUlt.views['TriggerType' + ThriveUlt.util.upperFirst( trigger )]( {
					el: this.$el.find( '#tvu-conversion-triggers' ),
					model: this.model,
					collection: collection,
					shortcodes: shortcodes
				} );

				view.render();
				view.$el.hide().fadeIn().slideDown();
			},
			renderEvents: function () {
				var event = this.model.get( 'trigger_options' ).event,
					collection = new ThriveUlt.collections.Campaigns( ThriveUlt.globals.campaigns.where( {type: ThriveUlt.util.campaignType.evergreen} ) );

				// remove our model from the collection if it's there
				if ( collection.length > 0 ) {
					var _model = collection.findWhere( {ID: parseInt( this.model.get( 'campaign_id' ) )} );
					collection.remove( _model );
				}

				if ( ! event || parseInt( event ) === 0 ) {
					return;
				}

				var event = new ThriveUlt.views['EventType' + ThriveUlt.util.upperFirst( event )]( {
					el: this.$el.find( '#tvu-conversion-events' ),
					model: this.model,
					collection: collection
				} );

				event.render();
				event.$el.hide().fadeIn().slideDown();
			},
			changeTriggers: function ( e ) {
				this.model.get( 'trigger_options' ).trigger = e.target.value;
				this.renderTriggers();
				this.renderEvents();
				this.renderSummary();
			},
			changeEvents: function ( e ) {
				this.model.get( 'trigger_options' ).event = e.target.value;
				this.renderTriggers();
				this.renderEvents();
				this.renderSummary();
				this.gotoStep( 1 );
			},
			save: function () {
				var self = this;

				// check if the event is set to end the campaign and if so, set the end id to empty
				if ( this.model.get( 'trigger_options' ).event === ThriveUlt.util.conversion_event.end ) {
					this.model.get( 'trigger_options' ).end_id = '';
				}

				if ( ! this.model.isValid() ) {
					return this.tvd_show_errors();
				}
				TVE_Dash.showLoader();

				this.model.save( {}, {
					success: function ( model, response ) {
						if ( ! self.model.get( 'ID' ) ) {
							self.model.set( 'ID', response.id );
							self.collection.add( self.model );
						} else {
							self.original_model.set( model.attributes );
						}

						self.close();
						TVE_Dash.hideLoader();
					},
					error: function ( model, response ) {
						TVE_Dash.err( response.responseText );
						TVE_Dash.hideLoader();
					}
				} );

			},
			showEvents: function () {
				var validate = this.model.validateTrigger();
				if ( validate ) {
					return this.tvd_show_errors( validate );
				}

				this.next();
			},
			addEvergreenCampaign: function () {
				this.next();
			},
			toggleEndCampaign: function () {
				this.$el.find( '.tvu-repeat-wrapper' ).toggle();
			},
			createEvergreenCampaign: function () {
				var campaign = new ThriveUlt.models.Campaign( {order: ThriveUlt.globals.campaigns.length} ),
					self = this,
					post_title = this.$el.find( '#tvu-new-campaign-name' ).val(),
					duration = this.$el.find( '#tvu-new-campaign-days' ).val(),
					end = this.$el.find( '#tvu-new-campaign-expire' ).val(),
					repeat = this.$el.find( '.tvu-new-campaign-repeat-switch' ),
					repeat_val = '';

				if ( ! repeat.is( ':checked' ) ) {
					repeat_val = 1;
					end = '';
				} else {
					repeat_val = 0;

				}
				var settings = {
					duration: duration,
					end: end,
					repeat: repeat_val,
					real: 1,
					realtime: '00:00',
					trigger: {
						type: '',
						ids: ''
					}
				};

				campaign.set( {
					post_title: post_title,
					type: ThriveUlt.util.campaignType.evergreen,
					tpl: 1,
					edit_mode: 'evergreen',
					settings: settings
				} );

				campaign.cleanModel();

				this.tvd_clear_errors();

				if ( ! campaign.isValid() ) {
					return this.tvd_show_errors( campaign );
				}

				TVE_Dash.showLoader();

				campaign.save( {}, {
					success: function ( model, response ) {
						var end_id = self.model.get( 'trigger_options' ).end_id;

						campaign.set( 'ID', response );
						if ( self.model.get( 'ID' ) ) {
							self.model.get( 'trigger_options' ).end_id_old = end_id;
						}
						self.model.get( 'trigger_options' ).end_id = response;

						ThriveUlt.globals.campaigns.add( campaign );

						TVE_Dash.hideLoader();
						self.gotoStep( 1 );
					},
					error: function ( model, response ) {
						TVE_Dash.err( response.responseText );
						TVE_Dash.hideLoader();
					}
				} );
			}
		} );

		ThriveUlt.views.ModalShortcodeCode = TVE_Dash.views.Modal.extend( {
			template: TVE_Dash.tpl( 'modals/get-shortcode' ),
			afterOpen: function () {
				TVE_Dash.bindZClip( this.$el.find( 'a.tve-copy-to-clipboard' ) );
			}
		} );

		//end document ready
	} );
})( jQuery );
;/**
 * Thrive Ultimatum Views
 */
var ThriveUlt = ThriveUlt || {};
ThriveUlt.views = ThriveUlt.views || {};

(function ( $ ) {

	$( function () {

		/**
		 * remove tvd-invalid class for all inputs in the view's root element
		 *
		 * @returns {Backbone.View}
		 */
		Backbone.View.prototype.tvd_clear_errors = function () {
			this.$( '.tvd-invalid' ).removeClass( 'tvd-invalid' );
			this.$( 'select' ).trigger( 'tvdclear' );
			return this;
		};

		/**
		 *
		 * @param {Backbone.Model|object} [model] backbone model or error object with 'field' and 'message' properties
		 *
		 * @returns {Backbone.View|undefined}
		 */
		Backbone.View.prototype.tvd_show_errors = function ( model ) {
			model = model || this.model;

			if ( ! model ) {
				return;
			}

			var err = model instanceof Backbone.Model ? model.validationError : model,
				self = this,
				$all = $();

			function show_error( error_item ) {
				if ( typeof error_item === 'string' ) {
					return TVE_Dash.err( error_item );
				}
				$all = $all.add( self.$( '[data-field=' + error_item.field + ']' ).addClass( 'tvd-invalid' ).each( function () {
					var $this = $( this );
					if ( $this.is( 'select' ) ) {
						$this.trigger( 'tvderror', error_item.message );
					} else {
						$this.next( 'label' ).attr( 'data-error', error_item.message )
					}
				} ) );
			}

			if ( $.isArray( err ) ) {
				_.each( err, function ( item ) {
					show_error( item );
				} );
			} else {
				show_error( err );
			}
			$all.not( '.tvd-no-focus' ).first().focus();
			/* if the first error message is not visible, scroll the contents to include it in the viewport. At the moment, this is only implemented for modals */
			this.scroll_first_error( $all.first() );

			return this;
		};

		/**
		 * scroll the contents so that the first errored input is visible
		 * currently this is only implemented for modals
		 *
		 * @param {Object} $input first input element that has the error
		 *
		 * @returns {Backbone.View}
		 */
		Backbone.View.prototype.scroll_first_error = function ( $input ) {
			if ( ! ( this instanceof TVE_Dash.views.Modal ) || ! $input.length ) {
				return this;
			}
			var input_top = $input.offset().top,
				content_top = this.$_content.offset().top,
				scroll_top = this.$_content.scrollTop(),
				content_height = this.$_content.outerHeight();
			if ( input_top >= content_top && input_top < content_height + content_top - 50 ) {
				return this;
			}

			this.$_content.animate( {
				'scrollTop': scroll_top + input_top - content_top - 40 // 40px difference
			}, 200, 'swing' );
		};

		/**
		 * Base View
		 */
		ThriveUlt.views.Base = Backbone.View.extend( {
			/**
			 * Always try to return this !!!
			 *
			 * @returns {ThriveUlt.views.Base}
			 */
			render: function () {
				return this;
			},
			/**
			 *
			 * Instantiate and open a new modal which has the view constructor assigned and send params further along
			 *
			 * @param ViewConstructor View constructor
			 * @param params
			 */
			modal: function ( ViewConstructor, params ) {
				return TVE_Dash.modal( ViewConstructor, params );
			}
		} );

		ThriveUlt.views.Header = ThriveUlt.views.Base.extend( {
			template: TVE_Dash.tpl( 'header' ),
			events: {
				'click #date_time_settings': 'openSettingsModal'
			},
			render: function () {
				this.$el.html( this.template( {} ) );
				TVE_Dash.materialize( this.$el );
				return this;
			},
			openSettingsModal: function () {
				this.modal( ThriveUlt.views.ModalEditDateSettings, {
					model: this.model,
					width: '1200px',
					'max-width': '80%'
				} );
			}
		} );
		/**
		 * Dashboard View
		 */
		ThriveUlt.views.Dashboard = ThriveUlt.views.Base.extend( {
			className: 'tvd-container',
			template: TVE_Dash.tpl( 'dashboard' ),
			initialize: function () {
				this.listenTo( this.collection, 'add', this.toggleNoCampaignText );
				this.listenTo( this.collection, 'remove', this.toggleNoCampaignText );
			},
			render: function () {
				this.$el.html( this.template( {} ) );

				var campaignsListView = new ThriveUlt.views.CampaignsList( {
					el: this.$el.find( '#tvu-campaigns-list' ),
					collection: this.collection
				} );
				campaignsListView.render();

				this.toggleNoCampaignText();
				TVE_Dash.hideLoader();
				return this;
			},
			toggleNoCampaignText: function () {
				if ( this.collection.length ) {
					this.$el.find( '#tvu-no-campaign-text' ).hide();
				} else {
					this.$el.find( '#tvu-no-campaign-text' ).show();
				}
			}
		} );

		ThriveUlt.views.ArchivedCampaigns = ThriveUlt.views.Dashboard.extend( {
			template: TVE_Dash.tpl( 'campaign/archived' )
		} );

		ThriveUlt.views.CampaignsList = ThriveUlt.views.Base.extend( {
			events: {
				'click .tvu-add-campaign': 'addNew'
			},
			initialize: function () {
				this.listenTo( this.collection, 'add', this.renderOne );
			},
			/**
			 * @param {Array} used for sortable
			 */
			itemViews: [],
			render: function () {
				this.collection.each( this.renderOne, this );

				var self = this;

				function show_position( event, ui ) {
					var $placeholder = $( ui.placeholder ),
						position = $placeholder.prevAll().not( ui.item ).length + 1;
					$placeholder.html( "<div class='tvu-inside-placeholder'><span>" + position + (
							ThriveUlt.t.n_suffix[position] ? ThriveUlt.t.n_suffix[position] : ThriveUlt.t.n_th
						) + ' ' + ThriveUlt.t.priority + "</span></div>" );
				}

				this.$el.sortable( {
					placeholder: 'ui-sortable-placeholder',
					items: '.tvu-campaign-item',
					forcePlaceholderSize: true,
					handle: '.tvu-drag-card',
					update: _.bind( self.updateOrder, this ),
					tolerance: 'pointer',
					change: show_position,
					start: function ( event, ui ) {
						show_position( event, ui );
						$( 'body' ).addClass( 'tvu-sorting' );
					},
					stop: function () {
						setTimeout( function () {
							$( 'body' ).removeClass( 'tvu-sorting' );
						}, 200 );
					}
				} );

				return this;
			},
			renderOne: function ( item ) {
				var $lastItem = this.$el.find( '.tvu-campaign-item' ).last(),
					view = new ThriveUlt.views.Campaign( {
						model: item,
						collection: this.collection
					} );

				if ( $lastItem.length ) {
					$lastItem.after( view.render().$el );
				} else {
					this.$el.prepend( view.render().$el );
				}

				this.itemViews.push( view );

				return this;
			},
			updateOrder: function () {
				var to_update = {};

				_.each( this.itemViews, function ( item ) {
					if ( item.model.get( 'order' ) != item.$el.index() ) {
						item.model.set( 'order', item.$el.index() );
						to_update[item.model.get( 'ID' )] = item.$el.index();
					}
				} );

				this.collection.sort();
				$.ajax( {
					type: 'post',
					url: ajaxurl,
					data: {
						action: ThriveUlt.ajax_actions.admin_controller,
						route: 'campaigns',
						custom: 'update_order',
						new_order: to_update,
						_nonce: ThriveUlt.admin_nonce
					}
				} );
			},
			addNew: function () {
				this.modal( ThriveUlt.views.ModalNewCampaign, {
					model: new ThriveUlt.models.Campaign( {order: this.collection.length} ),
					collection: this.collection,
					'max-width': '60%',
					width: '750px',
					in_duration: 200,
					out_duration: 0,
					templates: new Backbone.Collection( ThriveUlt.campaign_templates )
				} );
				return this;
			}
		} );

		/**
		 * Campaign View
		 */
		ThriveUlt.views.Campaign = ThriveUlt.views.Base.extend( {
			events: {
				'click .tvu-campaign-status': 'changeStatus',
				'click .tvu-archive-campaign': 'toggle_archive'
			},
			initialize: function () {
				this.listenTo( this.model, 'change:state', this.renderState );
				this.listenTo( this.model, 'change:status', this.renderStatus );
			},
			render: function () {
				this.renderState();
				return this;
			},
			renderState: function () {
				var state = this.model.get( 'state' );

				if ( this.model.get( 'status' ) === ThriveUlt.util.status.archived ) {
					state = ThriveUlt.util.status.archived;
				}

				if ( ! ThriveUlt.views['Campaign' + ThriveUlt.util.upperFirst( state ) + 'State'] ) {
					return;
				}

				var view = new ThriveUlt.views['Campaign' + ThriveUlt.util.upperFirst( state ) + 'State']( {
					model: this.model,
					collection: this.collection
				} );

				view.render();
				this.$el.replaceWith( view.$el );
				this.setElement( view.$el );

				return this;
			},
			changeStatus: function () {
				var status = this.model.get( 'status' );
				var new_status,
					message;
				switch ( status ) {
					case ThriveUlt.util.status.paused:
						new_status = ThriveUlt.util.status.running;
						message = ThriveUlt.t.Campaign_started;
						break;
					case ThriveUlt.util.status.running:
						new_status = ThriveUlt.util.status.paused;
						message = ThriveUlt.t.Campaign_paused;
						break;
					default:
						return;
				}
				TVE_Dash.showLoader();
				this.model.saveStatus( new_status ).done( function ( response ) {
					new_status === ThriveUlt.util.status.paused ? TVE_Dash['err']( message ) : TVE_Dash['success']( message );
					TVE_Dash.hideLoader();
				} ).fail( function ( response ) {
					TVE_Dash.err( response.responseText );
					TVE_Dash.hideLoader();
				} );

			},
			renderStatus: function () {
				var status = this.model.get( 'status' );
				if ( status != '' ) {
					this.$( '.tvu-campaign-status' ).addClass( 'tvd-hide' );
					this.$( '.tvu-campaign-status-' + this.model.get( 'status' ) ).removeClass( 'tvd-hide' );
				}
			},
			toggle_archive: function ( e ) {
				var self = this,
					$element = $( e.currentTarget ),
					data = $element.data(),
					new_status = data.archived === false ? ThriveUlt.util.status.archived : ThriveUlt.util.status.paused,
					title = this.model.get( 'post_title' ),
					message = data.archived === false ? ThriveUlt.util.printf( ThriveUlt.t.campaign_archived, [title] ) : ThriveUlt.util.printf( ThriveUlt.t.campaign_restored, [title] );

				TVE_Dash.showLoader();
				this.model.saveStatus( new_status ).done( function () {

					self.collection.remove( self.model );

					/**
					 * remove the item's view from the DOM
					 */
					self.remove();
					TVE_Dash.success( message );
					TVE_Dash.hideLoader();

				} ).fail( function ( response ) {
					TVE_Dash.err( response.responseText );
					TVE_Dash.hideLoader();
				} );
			}
		} );

		/**
		 * Campaign Normal State View
		 */
		ThriveUlt.views.CampaignNormalState = ThriveUlt.views.Base.extend( {
			className: 'tvd-col tvd-s6 tvd-ms6 tvd-m4 tvd-l3 tvu-campaign-item',
			template: TVE_Dash.tpl( 'campaign/item' ),
			events: {
				'click .tvu-delete-campaign': function () {
					this.$el.live_tooltip( 'destroy' );
					var delete_state = this.collection.findWhere( {state: 'delete'} );
					if ( delete_state ) {
						delete_state.set( 'state', ThriveUlt.util.states.normal );
					}
					this.model.set( 'state', ThriveUlt.util.states.delete );
				},
				'click .tvu-copy-campaign': 'copy',
				'click .tvu-campaign-display': 'openDisplaySettings',
				'click .tvu-edit-campaign-title': 'editTitle'
			},
			render: function () {
				this.$el.html( this.template( {item: this.model} ) );
				this.$campaignTitle = this.$el.find( '.tvd-card-title' );

				this.model.load_chart_data( _.bind( this.chart, this ) );

				return this;
			},
			copy: function () {
				var self = this,
					model = this.model.deepClone();
				model.set( 'copy', model.get( 'ID' ) );
				model.set( 'ID', '', {silent: true} );
				model.set( 'order', self.collection.length, {silent: true} );
				//clear conversion info
				model.set( 'impressions', 0 );
				model.set( 'conversion_rate', 0 );
				model.set( 'conversion_events', '' );
				model.set( 'has_event_logs', false );

				this.modal( ThriveUlt.views.ModalCopyCampaign, {
					model: model,
					collection: this.collection,
					'max-width': '60%',
					width: '800px'
				} );
			},
			/**
			 * Hides Title and shows Edit Title input
			 */
			editTitle: function () {
				var self = this,
					edit_btn = this.$el.find( '.tvu-edit-campaign-title' ),
					edit_model = new Backbone.Model( {
						value: this.model.get( 'post_title' ),
						label: ThriveUlt.t.Campaign_name,
						required: true
					} );
				edit_btn.hide();
				edit_model.on( 'change:value', function () {
					self.saveTitle.apply( self, arguments );
					self.$campaignTitle.show();
					textEdit.remove();
					edit_btn.show();
				} );
				edit_model.on( 'tvu_no_change', function () {
					self.$campaignTitle.html( self.model.get( 'post_title' ) ).show();
					textEdit.remove();
					edit_btn.show();
				} );

				var textEdit = new ThriveUlt.views.TextEdit( {
					model: edit_model,
					tagName: 'div'
				} );

				this.$campaignTitle.hide().after( textEdit.render().$el );
				textEdit.focus();
			},
			/**
			 * Saves the new title and hides the input value
			 */
			saveTitle: function ( edit_model, new_value ) {
				var self = this;

				this.model.set( {
					post_title: new_value,
					skip_settings_validation: true
				} );
				self.$campaignTitle.html( new_value );
				var xhr = this.model.save();
				if ( xhr ) {
					xhr.always( function () {
						self.$campaignTitle.html( new_value );
						self.model.set( 'skip_settings_validation', false );
					} );
				}
			},
			/**
			 * Open Display Settings modal
			 */
			openDisplaySettings: function () {
				open_display_settings( this );
			},
			/**
			 * render the chart displaying impressions and conversion rates
			 *
			 * @param {object} _data chart data (impressions, conversion_rates and labels)
			 */
			chart: function ( _data ) {
				var has_chart_data = true,
					$no_data_clone = this.$( '.tvu-chart-no-data' ).clone();
				if ( ! _data || _data.impressions.length < 2 ) {
					has_chart_data = false;
					_data = this.model.get_chart_dummy_data();
				}
				var y_axis = [
						{
							title: {
								text: ThriveUlt.t.No_of_impressions
							}
						}
					],
					series = [
						{
							name: ThriveUlt.t.Impressions,
							type: 'line',
							data: _data.impressions
						}
					];
				if ( _data.conversions ) {
					y_axis.push( {
						labels: {
							format: '{value}'
						},
						title: {
							text: ThriveUlt.t.No_of_conversions
						},
						opposite: true
					} );
					series.push( {
						name: ThriveUlt.t.Conversions,
						type: 'line',
						yAxis: 1,
						data: _data.conversions,
						tooltip: {
							valueSuffix: ''
						}
					} );
				}
				setTimeout( _.bind( function () {
					this.$( '.tvu-campaign-chart' ).highcharts( {
						colors: has_chart_data ? ['#3498db', '#47bb28'] : ['#fff', '#fff'],
						credits: {
							enabled: false
						},
						plotOptions: {
							line: {
								marker: {
									enabled: false
								}
							}
						},
						title: {
							text: ' '
						},
						xAxis: {
							categories: _data.labels,
							labels: {
								enabled: false
							}
						},
						yAxis: y_axis,
						tooltip: {
							shared: true
						},
						legend: {
							enabled: false
						},
						series: series
					} );
					if ( ! has_chart_data ) {
						this.$( '.tvu-campaign-chart' ).addClass( 'tvd-relative tvu-blurred' ).append( $no_data_clone.removeClass( 'tvd-hide' ) );
					}
				}, this ) );
			}
		} );

		ThriveUlt.views.CampaignArchivedState = ThriveUlt.views.CampaignNormalState.extend( {
			template: TVE_Dash.tpl( 'campaign/archived-item' )
		} );

		/**
		 * Campaign Delete State View
		 */
		ThriveUlt.views.CampaignDeleteState = ThriveUlt.views.Base.extend( {
			className: 'tvd-col tvd-s6 tvd-m4 tvd-ms6 tvd-l3 tvu-campaign-item',
			template: TVE_Dash.tpl( 'campaign/delete-state' ),
			events: {
				'click .tvu-delete-no': function () {
					this.model.set( 'state', ThriveUlt.util.states.normal );
				},
				'click .tvu-delete-yes': 'yes',
				'keydown': 'keyAction'
			},
			initialize: function () {
				this.listenTo( this.collection, 'remove', this.remove );
			},
			render: function () {
				this.$el.html( this.template( {item: this.model} ) );
				var _this = this;
				_.defer( function () {
					_this.$( '.tve-delete-campaign-card' ).focus();
				} );
				return this;
			},
			keyAction: function ( e ) {
				var code = e.which;
				if ( code == 13 ) {
					this.yes();
				} else if ( code == 27 ) {
					this.model.set( 'state', ThriveUlt.util.states.normal );
				}
			},
			yes: function () {

				TVE_Dash.cardLoader( this.$el );
				this.model.destroy( {
					wait: true,
					success: function () {
						TVE_Dash.hideLoader();
					},
					error: function () {
						//todo: error handling
						TVE_Dash.hideLoader();
					}
				} );
			}
		} );

		ThriveUlt.views.EditCampaign = ThriveUlt.views.Base.extend( {
			className: 'tvd-container',
			template: TVE_Dash.tpl( 'campaign/edit' ),
			timeline_view: null,
			conversion_view: null,
			events: {
				'click .tvu-add-design': 'addDesign',
				'click .tvu-edit-campaign-title': 'editTitle',
				'click .tvu-campaign-status': 'changeStatus',
				'click .tvu-edit-campaign': 'editCampaignType',
				'click .tvu-display-settings': 'openDisplaySettings',
				'click .tvu-lockdown': 'openLockdownSettings',
				'click .tvu-disable-lockdown': 'disableLockdown'
			},
			initialize: function () {
				this.listenTo( this.model.get( 'designs' ), 'add', this.render );
				this.listenTo( this.model.get( 'designs' ), 'remove', this.render );
				this.listenTo( this.model.get( 'designs' ), 'remove', this.removeEventActions );
				this.listenTo( this.model, 'change:summary', this.changeSettings );
				this.listenTo( this.model, 'change:type', this.render );
				this.listenTo( this.model, 'change:lockdown_state', this.renderLockdown );
				this.listenTo( this.model, 'change:lockdown', this.render );
				this.listenTo( this.model, 'change:status', this.renderStatus );
				this.listenTo( this.model, 'change:display_settings_summary', this.render );
				this.listenTo( this.model, 'tve_ult_campaign_saved', this.onCampaignSaved );
				this.listenTo( this.model.get( 'timeline' ), 'reset', this.renderTimeline );
			},
			onCampaignSaved: function () {
				this.fetchEvents();
				this.render();
			},
			render: function () {
				this.$el.html( this.template( {item: this.model} ) );
				this.$designList = this.$el.find( '#tvu-designs-list' );
				this.$campaignTitle = this.$el.find( '.tvu-campaign-title' );
				this.$campaignStatus = this.$el.find( '.tvu-campaign-status' );

				var status = this.model.get( 'status' ),
					self = this.model;

				if ( ! ThriveUlt.data.shortcodes || ! ThriveUlt.data.lead_groups ) {
					if ( this.model.get( 'settings' ).trigger.type === ThriveUlt.util.triggerType.conversion && status === ThriveUlt.util.status.running ) {
						status = ThriveUlt.util.status.paused;
						this.model.saveStatus( status ).done( function ( response ) {
							self.set( 'status', ThriveUlt.util.status.paused );
							TVE_Dash.err( ThriveUlt.t.leads_missing_paused, 6000 );
						} ).fail( function ( response ) {
							TVE_Dash.err( response.responseText, 6000 );
						} );
					}
				}

				if ( ! status ) {
					status = ThriveUlt.util.status.paused;
				}
				this.$campaignStatus.html( status );

				this.changeSettings();

				//render designs items
				this.model.get( 'designs' ).each( _.bind( this.renderDesign, this ) );

				this.renderTimeline();
				this.renderConversionEvents();
				this.renderLockdown();

				if ( this.model.get( 'status' ) == '' ) {
					this.model.set( 'status', ThriveUlt.util.status.paused );
				}

				TVE_Dash.materialize( this.$el );

				return this;
			},
			renderDesign: function ( item ) {

				if ( ! this.$designList.length ) {
					return;
				}
				var $lastItem = this.$designList.find( '.tvu-design-item' ).last(),
					view = new ThriveUlt.views.Design( {
						model: item,
						collection: this.model.get( 'designs' )
					} );

				view.render();

				if ( $lastItem.length ) {
					$lastItem.after( view.$el );
				} else {
					this.$designList.prepend( view.$el );
				}

				return this;
			},
			addDesign: function () {
				this.modal( ThriveUlt.views.ModalAddDesign, {
					model: this.model,
					'max-width': '50%'
				} );
			},
			/**
			 * Opens Display Settings modal
			 */
			openDisplaySettings: function () {
				open_display_settings( this );
			},
			/**
			 * Initiates the lockdown display
			 */
			renderLockdown: function () {
				var state = this.model.get( 'lockdown_state' );

				if ( ! state ) {
					state = this.model.set( 'lockdown_state', ThriveUlt.util.states.normal );
				}

				var view = new ThriveUlt.views['Lockdown' + ThriveUlt.util.upperFirst( state ) + 'State']( {
					model: this.model,
					el: this.$el.find( '#tvd-lockdown-wrapper' )[0]
				} );

				view.render();

			},
			/**
			 * Opens Lockdown options modal
			 */
			openLockdownSettings: function () {
				if ( ! this.model.has_valid_lockdown_trigger() ) {
					return false;
				}
				var model = new ThriveUlt.models.Campaign( $.extend( true, {}, this.model.toJSON() ) );
				var collection = new ThriveUlt.collections.PromotionURLCollection();

				this.model.get( 'lockdown_settings' ).promotion.each( function ( model ) {
					collection.add( new ThriveUlt.models.PromotionURLModel( model.toJSON() ) );
				} );

				this.modal( ThriveUlt.views.ModalEditLockDownSettings, {
					model: model,
					collection: collection,
					original_model: this.model,
					original_collection: this.model.get( 'lockdown_settings' ).promotion,
					width: '800px',
					'max-width': '60%'
				} );
			},
			disableLockdown: function () {
				this.model.set( 'lockdown_state', ThriveUlt.util.states.delete );
			},
			/**
			 * Open Modal to edit the campaign type
			 */
			editCampaignType: function () {
				var model = new ThriveUlt.models.Campaign( $.extend( true, {}, this.model.toJSON() ) );

				this.modal( ThriveUlt.views.ModalEditCampaignType, {
					model: model,
					original_model: this.model,
					collection: this.model.get( 'settings_collection' ),
					width: '800px',
					'max-width': '60%'
				} );
			},
			/**
			 * Toggles Campaign status
			 */
			changeStatus: function () {
				var status = this.model.get( 'status' ),
					new_status,
					message;
				switch ( status ) {
					case ThriveUlt.util.status.paused:
						new_status = ThriveUlt.util.status.running;
						message = ThriveUlt.t.Campaign_started;
						break;
					case ThriveUlt.util.status.running:
						new_status = ThriveUlt.util.status.paused;
						message = ThriveUlt.t.Campaign_paused;
						break;
					default:
						return;
				}
				TVE_Dash.showLoader();
				this.model.saveStatus( new_status ).done( function ( response ) {
					new_status === ThriveUlt.util.status.paused ? TVE_Dash['err']( message ) : TVE_Dash['success']( message );
					TVE_Dash.hideLoader();
				} ).fail( function ( response ) {
					TVE_Dash.err( response.responseText );
					TVE_Dash.hideLoader();
				} );

			},
			renderStatus: function () {
				try {
					var campaign = ThriveUlt.globals.campaigns.findWhere( {ID: this.model.get( 'ID' )} );
					if ( campaign instanceof ThriveUlt.models.Campaign ) {
						campaign.set( 'status', this.model.get( 'status' ) );
					}
				} catch ( error ) {
					console.log( 'Error: ' + error );
				}
				this.render();
			},
			/**
			 * Hides Title and shows Edit Title input
			 */
			editTitle: function () {
				var self = this,
					edit_btn = this.$el.find( '.tvu-edit-campaign-title' ),
					edit_model = new Backbone.Model( {
						value: this.model.get( 'post_title' ),
						label: ThriveUlt.t.Campaign_name,
						required: true
					} );
				edit_btn.hide();
				edit_model.on( 'change:value', function () {
					self.saveTitle.apply( self, arguments );
					self.$campaignTitle.show();
					textEdit.remove();
					edit_btn.show();
				} );
				edit_model.on( 'tvu_no_change', function () {
					self.$campaignTitle.html( self.model.get( 'post_title' ) ).show();
					textEdit.remove();
					edit_btn.show();
				} );

				var textEdit = new ThriveUlt.views.TextEdit( {
					model: edit_model,
					tagName: 'div'
				} );

				this.$campaignTitle.hide().after( textEdit.render().$el );
				textEdit.focus();
			},
			/**
			 * Saves the new title and hides the input value
			 */
			saveTitle: function ( edit_model, new_value ) {
				var self = this;

				try {
					var campaign = ThriveUlt.globals.campaigns.findWhere( {ID: this.model.get( 'ID' )} );
					if ( campaign instanceof ThriveUlt.models.Campaign ) {
						campaign.set( 'post_title', new_value );
					}
				} catch ( error ) {
					console.log( 'Error: ' + error );
				}

				this.model.set( {
					post_title: new_value,
					skip_settings_validation: true
				} );

				self.$campaignTitle.html( new_value );

				var xhr = this.model.save();
				if ( xhr ) {
					xhr.always( function () {
						self.$campaignTitle.html( new_value );
						self.model.set( 'skip_settings_validation', false );
					} );
				}
			},
			changeSettings: function () {
				var summary = this.model.get( 'summary' ),
					type = this.model.get( 'type' );
				if ( type ) {
					this.updateDashboardCampaignType();
					this.$el.find( '.tvu-campaign-type-title' ).text( ThriveUlt.util.get_type_title( type ) );
					this.$el.find( '.tvu-campaign-icon' ).html( '<img width="55" src="' + ThriveUlt.plugin_url + 'admin/img/tvd-' + type + '-campaign.png"/>' );
					if ( summary ) {
						this.$el.find( '.tvu-campaign-type' ).text( summary );
					} else {
						this.model.getSummary();
					}
				}
			},
			/**
			 * update the view to show the updated "Showing on" and "Hidden on" summary
			 */
			displaySettingsChanged: function () {
				this.$el.find( '#tvu-display-summary' ).html( this.model.get( 'display_settings_summary' ) );
			},
			removeEventActions: function ( deleted ) {
				this.model.get( 'timeline' ).removeEvents( deleted );
				this.renderTimeline();
			},
			renderTimeline: function () {
				if ( ! this.timeline_view ) {
					this.timeline_view = new ThriveUlt.views.Timeline( {
						model: this.model,
						el: this.$( '#tvu-timeline-wrapper' )[0],
						collection: this.model.get( 'timeline' )
					} );
				} else {
					this.timeline_view.setElement( this.$( '#tvu-timeline-wrapper' )[0] );
				}
				this.timeline_view.render();
			},
			renderConversionEvents: function () {
				if ( ! this.conversion_view ) {
					this.conversion_view = new ThriveUlt.views.ConversionEvents( {
						model: this.model,
						el: this.$( '#tvu-conversion-wrapper' )[0],
						collection: this.model.get( 'conversion_events' )
					} );
				} else {
					this.conversion_view.setElement( this.$( '#tvu-conversion-wrapper' )[0] );
				}

				this.conversion_view.render();
			},
			updateDashboardCampaignType: function () {
				try {
					var campaign = ThriveUlt.globals.campaigns.findWhere( {ID: this.model.get( 'ID' )} );
					if ( campaign instanceof ThriveUlt.models.Campaign ) {
						campaign.set( 'type', this.model.get( 'type' ) );
					}
				} catch ( error ) {
					console.log( 'Error: ' + error );
				}
			},
			fetchEvents: function () {
				this.model.get( 'timeline' ).fetch( {
					reset: true,
					data: {
						campaign_id: this.model.get( 'ID' )
					}
				} );
			}
		} );

		ThriveUlt.views.LockdownNormalState = ThriveUlt.views.Base.extend( {
			template: TVE_Dash.tpl( 'campaign/lockdown/normal-state' ),
			render: function () {
				this.$el.html( this.template( {item: this.model} ) );
				return this;
			}
		} );

		/**
		 * Promotion inputs generation
		 */
		ThriveUlt.views.LockdownPromotionURL = ThriveUlt.views.Base.extend( {
			template: TVE_Dash.tpl( 'campaign/lockdown/promotion-url' ),
			className: 'tvd-input-field',
			initialize: function ( options ) {
				this.parent_model = options.parent_model;
				this.listenTo( this.model, 'change', this.collection.generateEmailLink );
			},
			render: function () {
				var view = this,
					model = this.model,
					collection = this.collection,
					key = this.collection.indexOf( this.model );

				this.$el.empty().append( this.template( {model: model, key: key} ) );

				function no_value_callback() {
					model.set( model.defaults );
				}

				function change() {
					var $this = $( this );
					model.set( {value: $this.val()} );
				}

				function select( event, ui ) {
					model.set( ui.item );
				}

				var defaults = {
					url: ThriveUlt.ajaxurl( 'action=' + ThriveUlt.ajax_actions.admin_controller + '&route=progressiveGetPosts' ),
					no_value_callback: no_value_callback,
					change_callback: change,
					select: select,
					collection: collection
				};

				new ThriveUlt.PostSearch( this.$el.find( '#tvu-promotion-url' + key ), defaults );

				return this;
			}
		} );

		ThriveUlt.views.LockdownServiceURL = ThriveUlt.views.Base.extend( {
			template: TVE_Dash.tpl( 'campaign/lockdown/link-generation' ),
			className: 'tvd-copy-row tvd-row tvd-collapse tvd-no-mb tvu-url-container',
			initialize: function () {
				this.listenTo( this.model, 'change', this.render );
				this.listenTo( this.model, 'change', this.bindZclip );
			},
			render: function () {
				var key = this.collection.indexOf( this.model );
				this.$el.empty().append( this.template( {model: this.model, key: key} ) );

				return this;
			},
			bindZclip: function () {
				ZeroClipboard.Client.prototype.destroy();
				this.$el.find( '.zclip' ).remove();
				TVE_Dash.bindZClip( this.$el.find( 'a.tve-copy-to-clipboard' ) );
			}
		} );

		ThriveUlt.views.LockdownDeleteState = ThriveUlt.views.Base.extend( {
			template: TVE_Dash.tpl( 'campaign/lockdown/delete-state' ),
			events: {
				'click .tvu-delete-no': 'switchNormal',
				'click .tvu-delete-yes': 'save'
			},
			render: function () {
				this.$el.html( this.template( {item: this.model} ) );
				return this;
			},
			switchNormal: function () {
				this.model.set( 'lockdown_state', ThriveUlt.util.states.normal );
			},
			save: function () {
				this.model.set( 'lockdown', '' );
				this.model.get( 'settings' ).trigger.type = ThriveUlt.util.triggerType.first;

				var self = this;
				TVE_Dash.showLoader();

				this.model.save().done( function () {
					TVE_Dash.hideLoader();
					try {
						ThriveUlt.globals.campaign.set( self.model.toJSON() );
					} catch ( error ) {
						console.log( 'Error: ' + error );
					}
				} ).error( function () {
					//todo: error handling
					TVE_Dash.hideLoader();
				} );
				this.switchNormal();
			}
		} );

		/**
		 * Absolute campaign view
		 * @type {void|*}
		 */
		ThriveUlt.views.CampaignTypeAbsoluteState = ThriveUlt.views.Base.extend( {
			template: TVE_Dash.tpl( 'campaign/type/absolute-state' ),
			events: {
				'change #tvu-end-date': 'setEndDate',
				'change #tvu-start-date': 'setStartDate'
			},
			default_settings: {
				end: {
					time: '00:00'
				},
				start: {
					time: '00:00'
				}
			},
			initialize: function () {
				var settings = $.extend( true, this.default_settings, this.model.get( 'settings' ) );
				this.model.set( 'settings', settings );
			},
			render: function () {
				var self = this.model;
				this.$el.html( this.template( {item: this.model.get( 'settings' )} ) );
				TVE_Dash.materialize( this.$el );

				this.$el.find( '#tvu-start-date, #tvu-end-date' ).pickadate( {
					format: 'd mmmm yyyy',
					selectYears: true,
					selectMonths: true
				} );
				this.$el.find( '#tvu-start-hour' ).timepicker( {
					timeFormat: 'HH:mm',
					dynamic: false,
					interval: 60, // 60 minutes
					change: function () {
						var text = $( this ).removeClass( 'tvd-invalid' ).val(),
							element = $( this ),
							time = self.roundTime( text );

						element.val( time );
						self.get( 'settings' ).start.time = time;
					}
				} );

				this.$el.find( '#tvu-end-hour' ).timepicker( {
					timeFormat: 'HH:mm',
					dynamic: false,
					interval: 60, // 60 minutes
					change: function () {
						var text = $( this ).removeClass( 'tvd-invalid' ).val(),
							element = $( this ),
							time = self.roundTime( text );

						element.val( time );
						self.setTime( time );
					}
				} );

				return this;
			},
			setStartDate: function ( e ) {
				$( e.target ).removeClass( 'tvd-invalid' );
				this.model.get( 'settings' ).start.date = e.target.value;
			},
			setEndDate: function ( e ) {
				$( e.target ).removeClass( 'tvd-invalid' );
				if ( typeof this.model.get( 'settings' ).end === 'object' ) {
					this.model.get( 'settings' ).end.date = e.target.value;
				} else {
					var end = {
						date: e.target.value,
						time: ''
					};
					this.model.get( 'settings' ).end = end;
				}
			}
		} );

		/**
		 * Rolling campaign view
		 * @type {void|*}
		 */
		ThriveUlt.views.CampaignTypeRollingState = ThriveUlt.views.Base.extend( {
			className: 'tvu-campaign-option tvu-campaign-rolling',
			template: TVE_Dash.tpl( 'campaign/type/rolling-state' ),
			default_settings: {
				start: {
					time: '00:00'
				},
				end: {
					time: '00:00'
				}
			},
			events: {
				'change select.tvu-rolling-repeat': 'changeDescription',
				'change #tvu-select-recurrence-change': 'changeRollingState',
				'click #tve-rolling-end-never': function () {
					this.$( '#tvu-end-date' ).removeClass( 'tvd-invalid' ).val( '' );
					this.$( 'label[for="tvu-end-date"]' ).removeClass();

					this.$( '#tvu-end-hour' ).removeClass( 'tvd-invalid' ).val( '' );
					this.$( 'label[for="tvu-end-hour"]' ).removeClass();

					this.$( '#tvu-rolling-end-after-in' ).removeClass( 'tvd-invalid' ).val( '' );
					this.$( 'label[for="tvu-rolling-end-after-in"]' ).removeClass( 'tvd-active' );

					this.model.get( 'settings' ).end = null;
				},
				'click #tve-rolling-end-after': function () {
					this.$( '#tvu-end-date' ).removeClass( 'tvd-invalid' ).val( '' );
					this.$( 'label[for="tvu-end-date"]' ).removeClass();

					this.$( '#tvu-end-hour' ).removeClass( 'tvd-invalid' ).val( '' );
					this.$( 'label[for="tvu-end-hour"]' ).removeClass();

					var $input = this.$( '#tvu-rolling-end-after-in' );
					$input.focus();

					if ( typeof this.model.get( 'settings' ).end !== 'string' ) {
						this.model.get( 'settings' ).end = '1';
						$input.val( '1' );
					}
				},
				'change #tvu-rolling-end-after-in': function ( e ) {
					this.model.get( 'settings' ).end = e.target.value;
				},
				'click #tvu-rolling-end-after-in': function () {
					this.$( '#tvu-end-date' ).removeClass( 'tvd-invalid' ).val( '' );
					this.$( 'label[for="tvu-end-date"]' ).removeClass();

					this.$( '#tvu-end-hour' ).removeClass( 'tvd-invalid' ).val( '' );
					this.$( 'label[for="tvu-end-hour"]' ).removeClass();

					this.$( '#tve-rolling-end-after' ).attr( 'checked', 'checked' );

					if ( typeof this.model.get( 'settings' ).end !== 'string' ) {
						this.model.get( 'settings' ).end = '1';
						this.$( '#tvu-rolling-end-after-in' ).val( '1' );
					}
				},
				'click #tve-rolling-end-on': function () {
					this.$el.find( '#tvu-end-date' ).trigger( 'click' ).focus();
				},
				'click #tvu-end-date': function () {
					this.$( '#tvu-rolling-end-after-in' ).removeClass( 'tvd-invalid' ).val( '' );
					this.$( 'label[for="tvu-rolling-end-after-in"]' ).removeClass( 'tvd-active' );

					this.$( '#tve-rolling-end-on' ).attr( 'checked', 'checked' );

					if ( this.model.get( 'settings' ).end === null || typeof this.model.get( 'settings' ).end !== 'object' ) {
						this.model.get( 'settings' ).end = {
							date: '',
							time: ''
						};
					}
				},
				'change #tvu-end-date': function ( e ) {
					$( e.target ).removeClass( 'tvd-invalid' );

					this.model.get( 'settings' ).end.date = e.target.value;
				},
				'click #tvu-end-hour': function ( e ) {
					this.$( '#tvu-rolling-end-after-in' ).removeClass( 'tvd-invalid' ).val( '' );
					this.$( 'label[for="tvu-rolling-end-after-in"]' ).removeClass( 'tvd-active' );

					this.$( '#tve-rolling-end-on' ).attr( 'checked', 'checked' );

					$( e.target ).removeClass( 'tvd-invalid' );
				},
				'change #tvu-end-hour': function ( e ) {
					$( e.target ).removeClass( 'tvd-invalid' );
				},
				'change #tvu-start-date': function ( e ) {
					$( e.target ).removeClass( 'tvd-invalid' );
					this.model.get( 'settings' ).start.date = e.target.value;
				}
			},
			initialize: function ( args ) {
				this.data = args;
				this.listenTo( this.model, 'change:rolling_type', this.renderSettings );

				var settings = $.extend( true, this.default_settings, this.model.get( 'settings' ) );
				this.model.set( 'settings', settings );
			},
			render: function () {
				var self = this;
				this.$el.html( this.template( {
					item: this.model,
					settings: this.model.get( 'settings' )
				} ) );

				TVE_Dash.materialize( this.$el );//do we really need this?

				this.renderSettings();

				this.$el.find( '#tvu-start-date, #tvu-end-date' ).pickadate( {
					format: 'd mmmm yyyy',
					selectYears: true,
					selectMonths: true
				} );

				this.$el.find( '#tvu-start-hour' ).timepicker( {
					timeFormat: 'HH:mm',
					dynamic: false,
					interval: 60, // 60 minutes
					change: function () {
						var text = $( this ).removeClass( 'tvd-invalid' ).val(),
							element = $( this ),
							time = self.model.roundTime( text );

						element.val( time );
						self.model.get( 'settings' ).start.time = $( this ).removeClass( 'tvd-invalid' ).val();
					}
				} );

				this.$el.find( '#tvu-end-hour' ).timepicker( {
					timeFormat: 'HH:mm',
					dynamic: false,
					interval: 60, // 60 minutes
					change: function () {
						var text = $( this ).removeClass( 'tvd-invalid' ).val(),
							element = $( this ),
							time = self.model.roundTime( text );

						element.val( time );
						self.model.setTime( $( this ).removeClass( 'tvd-invalid' ).val() );
					}
				} );

				return this;
			},
			renderSettings: function () {
				var state = this.model.get( 'rolling_type' );
				if ( ! state ) {
					state = ThriveUlt.util.rollingType.daily;
				}

				this.collection.setOptions( this.model.get( 'settings' ).duration, state, this.model.get( 'settings' ).repeatOn );

				if ( ! this[state] ) {
					this[state] = new ThriveUlt.views['Rolling' + ThriveUlt.util.upperFirst( state ) + 'Settings']( {
						el: this.$el.find( '#tvu-rolling-options' )[0],
						model: this.model,
						collection: this.collection
					} );
				} else {
					this[state].setElement( this.$( '#tvu-rolling-options' )[0] );
				}

				this.changeDescription();

				var repeatOn = this.collection.prepareAppend();
				if ( repeatOn ) {
					this.$el.find( '.tvu-campaign-rolling-day' ).show().text( 'on ' + repeatOn );
				}

				this[state].render();
				this[state].$el.hide().fadeIn().slideDown();

				return this;
			},
			changeRollingState: function ( e ) {
				var type = e.target.value;
				this.$el.find( '.tvu-campaign-rolling-repeat, .tvu-campaign-rolling-day' ).empty();
				this.model.get( 'settings' ).repeatOn = [];
				this.model.get( 'settings' ).repeat = 1;
				this.model.get( 'settings' ).duration = 1;
				this.model.set( 'rolling_type', type );
			},
			changeDescription: function ( e ) {
				var repeat = this.model.changeRepeat( e );
				this.$el.find( '.tvu-campaign-rolling-repeat' ).text( repeat );
			}
		} );

		/**
		 * Evergreen campaign view
		 * @type {void|*}
		 */
		ThriveUlt.views.CampaignTypeEvergreenState = ThriveUlt.views.Base.extend( {
			className: 'tvu-campaign-option tvu-campaign-evergreen',
			template: TVE_Dash.tpl( 'campaign/type/evergeen-state' ),
			events: {
				'click .tvu-evergreen-settings': 'changeEvergreenState',
				'change #tvu-evergreen-days': function ( e ) {
					this.model.get( 'settings' ).duration = e.target.value;
				},
				'change #tvu-evergreen-expire': function ( e ) {
					this.model.get( 'settings' ).end = e.target.value;
				},
				'click .tvu-lead-conversion': 'saveCheckboxes',
				'change .tvu-repeat-switch': 'changeRepeat',
				'change .tvu-real-time': 'changeRealEnding',
				'change .tvu-lockdown-switch': 'changeLockdown'
			},
			initialize: function ( options ) {
				this.shortcodes = options.shortcodes;
			},
			render: function () {

				var linked_to = this.model.get( 'linked_to' ),
					self = this.model;

				this.$el.html( this.template( {
					item: this.model.get( 'settings' ),
					lockdown: this.model.get( 'lockdown' ),
					collection: this.collection
				} ) );
				this.renderSettings();

				if ( linked_to && linked_to.length > 0 ) {
					linked_to.each( this.renderLinkedCampaign, this );
				}

				TVE_Dash.materialize( this.$el );

				this.$el.find( '#tvu-real-time' ).timepicker( {
					timeFormat: 'HH:mm',
					dynamic: false,
					interval: 60, // 60 minutes
					change: function () {
						var text = $( this ).removeClass( 'tvd-invalid' ).val(),
							element = $( this ),
							time = self.roundTime( text );

						element.val( time );
						self.get( 'settings' ).realtime = time;
					}
				} );

				return this;
			},
			renderSettings: function () {
				var state = this.model.get( 'settings' ).trigger.type;

				if ( state == ThriveUlt.util.trigger_type.conversion && ! this.shortcodes && ! this.collection ) {
					this.model.get( 'settings' ).trigger.type = '';
				}

				if ( ! ThriveUlt.views['Evergreen' + ThriveUlt.util.upperFirst( state ) + 'Settings'] ) {
					return;
				}

				var view = new ThriveUlt.views['Evergreen' + ThriveUlt.util.upperFirst( state ) + 'Settings']( {
					el: this.$el.find( '#tvu-evergreen-settings' ),
					model: this.model,
					collection: this.collection,
					shortcodes: this.shortcodes
				} );

				view.render();
				this.$( '#tvu-trigger-description' ).html( view.get_trigger_description() );
				view.$el.hide().fadeIn().slideDown();
			},
			changeEvergreenState: function ( e ) {
				var type = e.target.value;
				this.model.get( 'settings' ).trigger.type = type;
				this.model.get( 'settings' ).trigger.ids = '';
				this.renderSettings();
			},
			renderLinkedCampaign: function ( item ) {
				var v = new ThriveUlt.views.LinkedCampaign( {
					model: item
				} );
				this.$el.find( '.tvu-evergreen-linked' ).show();
				this.$el.find( '.tvu-evergreen-linked-to' ).append( v.render().$el );

				return this;
			},
			saveCheckboxes: function () {
				var checked = [];

				this.$el.find( 'input[type=checkbox].tvu-lead-conversion:checked' ).each( function () {
					var val = parseInt( $( this ).val() );
					checked.push( val );
				} );

				this.model.get( 'settings' ).trigger.ids = checked;
			},
			changeRepeat: function ( e ) {
				var repeat = $( e.currentTarget );
				if ( repeat.is( ':checked' ) ) {
					this.model.get( 'settings' ).evergreen_repeat = 1;
					this.$el.find( '.tvu-repeat-wrapper' ).slideDown( 200 );
					return;
				}

				this.model.get( 'settings' ).evergreen_repeat = 0;
				this.model.get( 'settings' ).end = '';
				this.$el.find( '#tvu-evergreen-expire' ).val( '' );
				this.$el.find( '.tvu-repeat-wrapper' ).slideUp( 200 );
			},
			changeRealEnding: function ( e ) {
				var repeat = $( e.currentTarget );
				if ( repeat.is( ':checked' ) ) {
					this.model.get( 'settings' ).real = 1;
					this.$el.find( '.tvu-real-time-wrapper' ).slideDown( 200 );
					return;
				}

				this.model.get( 'settings' ).real = 0;
				this.model.get( 'settings' ).realtime = '00:00';
				this.$el.find( '#tvu-real-time' ).val( '00:00' );
				this.$el.find( '.tvu-real-time-wrapper' ).slideUp( 200 );
			},
			changeLockdown: function ( e ) {
				var lockdown = $( e.currentTarget );
				this.model.set( 'lockdown', lockdown.is( ':checked' ) );
				this.model.get( 'settings' ).trigger.type = '';

				if ( this.model.get( 'lockdown' ) && this.model.get( 'status' ) == ThriveUlt.util.status.running && (! this.model.get( 'lockdown_settings' ).expired || ! this.model.get( 'lockdown_settings' ).preaccess || ! this.model.get( 'lockdown_settings' ).promotion) ) {
					this.model.set( 'status', ThriveUlt.util.status.paused );
					this.model.saveStatus();
					TVE_Dash.err( ThriveUlt.t.Campaign_paused );
				}
				this.render();
			}
		} );

		ThriveUlt.views.LinkedCampaign = ThriveUlt.views.Base.extend( {
			template: TVE_Dash.tpl( 'campaign/type/evergreen-linked' ),
			render: function () {
				this.$el.empty().append( this.template( {item: this.model} ) );

				return this;
			}
		} );

		/**
		 * Evergreen Conversion Options
		 * @type {void|*}
		 */
		ThriveUlt.views.EvergreenConversionSettings = ThriveUlt.views.Base.extend( {
			className: 'tvu-trigger-option tvu-trigger-option-conversion',
			template: TVE_Dash.tpl( 'campaign/type/evergreen-conversion' ),
			initialize: function ( options ) {
				this.shortcodes = options.shortcodes;
			},
			render: function () {
				this.$el.html( this.template( {
					item: this.model.get( 'settings' ).trigger,
					collection: this.collection,
					shortcodes: this.shortcodes
				} ) );
				if ( this.collection && this.shortcodes ) {
					this.collection.each( this.renderLeadGroups, this );
					this.shortcodes.each( this.renderShortcodes, this );
				}
				TVE_Dash.materialize( this.$el );

				return this;
			},
			renderLeadGroups: function ( item ) {
				var v = new ThriveUlt.views.EvergreenCampaignCheckbox( {
					model: item,
					triggers: this.model.get( 'settings' ).trigger

				} );

				this.$el.find( '.tvu-evergreen-leads-checkboxes' ).append( v.render().$el );

				return this;
			},
			renderShortcodes: function ( item ) {
				var v = new ThriveUlt.views.EvergreenCampaignCheckbox( {
					model: item,
					triggers: this.model.get( 'settings' ).trigger

				} );

				this.$el.find( '.tvu-evergreen-shortcodes-checkboxes' ).append( v.render().$el );

				return this;
			},
			get_trigger_description: function () {
				return '<em>' + ThriveUlt.t.trigger_conversion + '</em>';
			}
		} );

		/**
		 * Evergreen campaign checkboxes view
		 * @type {*|void}
		 */
		ThriveUlt.views.EvergreenCampaignCheckbox = ThriveUlt.views.Base.extend( {
			template: TVE_Dash.tpl( 'campaign/type/evergreen-checkbox' ),
			tagName: 'div',
			className: 'tvu-posts-checkbox tvd-col tvd-s4',
			initialize: function ( options ) {
				this.triggers = options.triggers;
			},
			render: function () {
				this.$el.empty().append( this.template( {
					item: this.model,
					ids: this.triggers.ids.constructor === Array ? this.triggers.ids : ''
				} ) );

				return this;
			}
		} );

		/**
		 * Evergreen First Visit Options
		 * @type {void|*}
		 */
		ThriveUlt.views.EvergreenFirstSettings = ThriveUlt.views.Base.extend( {
			render: function () {
				this.$el.empty();

				return this;
			},
			get_trigger_description: function () {
				return '<em>' + ThriveUlt.t.trigger_first_visit + '</em>'
			}
		} );

		/**
		 * Evergreen Promotion page Options
		 * @type {void|*}
		 */
		ThriveUlt.views.EvergreenPromotionSettings = ThriveUlt.views.Base.extend( {
			render: function () {
				this.$el.empty();

				return this;
			},
			get_trigger_description: function () {
				return '<em>' + ThriveUlt.t.trigger_promotion_page + '</em>';
			}
		} );

		/**
		 * Evergreen Visit to a specific page Options
		 * @type {void|*}
		 */
		ThriveUlt.views.EvergreenUrlSettings = ThriveUlt.views.Base.extend( {
			template: TVE_Dash.tpl( 'campaign/type/evergreen-specific' ),
			render: function () {
				var model = this.model;
				this.$el.html( this.template( {item: this.model.get( 'settings' ).trigger} ) );
				var $post_search = this.$( '#tvu-specific-url' );

				new ThriveUlt.PostSearch( $post_search, {
					url: ThriveUlt.ajaxurl( 'action=' + ThriveUlt.ajax_actions.admin_controller + '&route=progressiveGetPosts' ),
					select: function ( event, ui ) {
						model.get( 'settings' ).trigger.ids = ui.item.id;
					},
					search: function () {
						model.get( 'settings' ).trigger.ids = '';
					},
					open: function () {
						model.get( 'settings' ).trigger.ids = '';
					},
					fetch_single: model.get( 'settings' ).trigger.ids
				} );

				return this;
			},
			get_trigger_description: function () {
				return '<em>' + ThriveUlt.t.trigger_url + '</em>';
			}
		} );

		/**
		 * Rolling Daily option change state
		 * @type {void|*}
		 */
		ThriveUlt.views.RollingDailySettings = ThriveUlt.views.Base.extend( {
			template: TVE_Dash.tpl( 'campaign/type/daily-campaign' ),
			events: {
				'change #tvu-rolling-day-duration': function ( e ) {
					this.model.get( 'settings' ).duration = e.target.value;
				}
			},
			render: function () {
				this.$el.html( this.template( {item: this.model.get( 'settings' )} ) );
				TVE_Dash.materialize( this.$el );

				return this;
			}
		} );

		/**
		 * Rolling Weekly option change state
		 * @type {void|*}
		 */
		ThriveUlt.views.RollingWeeklySettings = ThriveUlt.views.Base.extend( {
			template: TVE_Dash.tpl( 'campaign/type/weekly-campaign' ),
			events: {
				'change #tvu-rolling-duration': function ( e ) { // TODO: this is copy-paste in 2 instances, I think this can be refactored
					this.model.get( 'settings' ).duration = e.target.value;
					this.collection.each( function ( item ) {
						item.set( {
							'disabled': false,
							'checked': false
						} );
					}, this );

					$( '.tvu-campaign-rolling-day' ).empty();
					this.render();
				}
			},
			render: function () {
				this.$el.empty();
				this.$el.html( this.template( {item: this.model.get( 'settings' )} ) );
				this.$c_wrapper = this.$( '.tvu-weekly-repeats-wrapper' ); // cache this here so that it doesn't perform a DOM query for each checkbox
				this.collection.each( this.renderOne, this );
				TVE_Dash.materialize( this.$el );
				return this;
			},
			renderOne: function ( item ) {
				var v = new ThriveUlt.views.RollingCampaignCheckbox( {
					model: item,
					collection: this.collection
				} );
				this.$c_wrapper.append( v.render().$el );

				return this;
			}
		} );

		/**
		 * Rolling Monthly option change state
		 * @type {void|*}
		 */
		ThriveUlt.views.RollingMonthlySettings = ThriveUlt.views.Base.extend( {
			template: TVE_Dash.tpl( 'campaign/type/monthly-campaign' ),
			events: {
				'change #tvu-rolling-duration': function ( e ) {
					this.model.get( 'settings' ).duration = e.target.value;
					this.collection.each( function ( item ) {
						item.set( {
							'disabled': false,
							'checked': false
						} );
					}, this );

					this.render();
				}
			},
			render: function () {
				this.$el.html( this.template( {item: this.model.get( 'settings' )} ) );
				this.$c_wrapper = this.$( '.tvu-monthly-repeats-wrapper' ); // cache this here so that it doesn't perform a DOM query for each checkbox
				this.collection.each( this.renderOne, this );
				TVE_Dash.materialize( this.$el );
				return this;
			},
			renderOne: function ( item ) {
				var v = new ThriveUlt.views.RollingCampaignCheckbox( {
					model: item,
					collection: this.collection
				} );
				this.$c_wrapper.append( v.render().$el );

				return this;
			}
		} );

		/**
		 * Rolling Yearly option change state
		 * @type {void|*}
		 */
		ThriveUlt.views.RollingYearlySettings = ThriveUlt.views.Base.extend( {
			template: TVE_Dash.tpl( 'campaign/type/yearly-campaign' ),
			events: {
				'change #tvu-rolling-year-duration': function () {
					this.model.get( 'settings' ).duration = this.$el.find( '#tvu-rolling-year-duration' ).val();
				}
			},
			render: function () {
				this.$el.html( this.template( {item: this.model.get( 'settings' )} ) );
				TVE_Dash.materialize( this.$el );

				return this;
			}
		} );
		/**
		 * Rolling Campaign Checkboxes view
		 * @type {void|*}
		 */
		ThriveUlt.views.RollingCampaignCheckbox = ThriveUlt.views.Base.extend( {
			template: TVE_Dash.tpl( 'campaign/type/checkbox' ),
			tagName: 'span',
			className: 'tvu-repeat-checkbox',
			events: {
				'click .tvu-rolling-repeat-check': 'prepareCheckboxes'
			},
			initialize: function () {
				this.listenTo( this.model, 'change', this.render );
			},
			render: function () {
				this.$el.empty().append( this.template( {item: this.model} ) );
				return this;
			},
			prepareCheckboxes: function ( e ) {
				var duration = $( '#tvu-rolling-options' ).find( '#tvu-rolling-duration' ).val(),
					ID = this.model.get( 'ID' ),
					value = this.model.get( 'checked' );

				this.collection.changeCheckboxes( duration, ID );

				this.model.set( 'checked', value ? false : true );

				var repeatOn = this.collection.prepareAppend();
				$( '.tvu-campaign-rolling-day' ).show().text( 'on ' + repeatOn );//something is wrong with that select; is this the only way?
			}
		} );

		/**
		 * Design View
		 * Base view of design
		 * Based on model's state property renders the corresponding view
		 */
		ThriveUlt.views.Design = ThriveUlt.views.Base.extend( {
			events: {
				'click .tvu-delete-design': function () {
					this.model.set( 'state', 'delete' );
				},
				'click .tvu-edit-design': function () {
					/* when clicking on edit design start checking for thumbnail url */
					this.model.initRefetchTimer();
				},
				'click .tvu-get-shortcode': 'openShortcodeModal'
			},
			initialize: function () {
				this.listenTo( this.model, 'change:state', this.renderState );
				this.listenTo( this.model, 'change:thumb_url', this.renderState );
			},
			render: function () {
				this.renderState();
				return this;
			},
			renderState: function () {
				var state = this.model.get( 'state' );

				if ( ! ThriveUlt.views['Design' + ThriveUlt.util.upperFirst( state ) + 'State'] ) {
					return;
				}

				var view = new ThriveUlt.views['Design' + ThriveUlt.util.upperFirst( state ) + 'State']( {
					model: this.model,
					collection: this.collection
				} );

				view.render();
				this.$el.replaceWith( view.$el );
				this.setElement( view.$el );

				return this;
			},
			openShortcodeModal: function () {
				this.modal( ThriveUlt.views.ModalShortcodeCode, {
					model: this.model
				} );
			}
		} );

		/**
		 * Design Normal State View
		 */
		ThriveUlt.views.DesignNormalState = ThriveUlt.views.Base.extend( {
			template: TVE_Dash.tpl( 'design-item' ),
			className: 'tvd-col tvd-s12 tvd-m4 tvd-ms6 tvd-l3 tvu-design-item',
			events: {},
			render: function () {
				this.$el.html( this.template( {item: this.model} ) );
				return this;
			}
		} );

		/**
		 * Design Delete State View
		 */
		ThriveUlt.views.DesignDeleteState = ThriveUlt.views.Base.extend( {
			className: 'tvd-col tvd-s12 tvd-m4 tvd-ms6 tvd-l3',
			template: TVE_Dash.tpl( 'design-delete-state' ),
			events: {
				'click .tvu-delete-yes': 'yes',
				'keydown': 'keyAction',
				'click. .tvu-delete-no': function () {
					this.model.set( 'state', ThriveUlt.util.states.normal );
				}
			},
			initialize: function () {
				this.listenTo( this.collection, 'remove', this.remove );
			},
			render: function () {
				this.$el.html( this.template( {item: this.model} ) );
				var _this = this;
				_.defer( function () {
					_this.$( '.tve-delete-design-card' ).focus();
				} );
				return this;
			},
			keyAction: function ( e ) {
				var code = e.which;
				if ( code == 13 ) {
					this.yes();
				} else if ( code == 27 ) {
					this.model.set( 'state', ThriveUlt.util.states.normal );
				}
			},
			yes: function () {
				var self = this;
				TVE_Dash.cardLoader( this.$el );
				this.model.clearRefetchTimer();
				this.model.destroy( {
					wait: true,
					success: function ( model, response ) {
						self.collection.remove( {ID: self.model.get( 'ID' )} );
						if ( response.campaign_paused ) {
							ThriveUlt.globals.campaign.set( 'status', response.campaign_status );
							TVE_Dash.err( response.campaign_paused, 6000 );
						}
					},
					error: function () {
						//todo: error handling
						TVE_Dash.hideLoader();
					}
				} );
			}
		} );

		ThriveUlt.views.TextEdit = ThriveUlt.views.Base.extend( {
			className: 'tvd-input-field tvu-inline-edit',
			template: TVE_Dash.tpl( 'textedit' ),
			events: {
				'keyup input': 'keyup',
				'change input': function ( e ) {
					if ( ! $.trim( this.input.val() ) ) {
						this.input.addClass( 'tvd-invalid' );
						return false;
					}
					this.model.set( 'value', this.input.val() );
					return false;
				},
				'blur input': function () {
					this.model.trigger( 'tvu_no_change' );
				}
			},
			keyup: function ( event ) {
				if ( event.which === 27 ) {
					this.model.trigger( 'tvu_no_change' );
				}
			},
			render: function () {
				this.$el.html( this.template( {item: this.model} ) );
				this.input = this.$el.find( 'input' );

				return this;
			},
			focus: function () {
				this.input.focus().select();
			}
		} );

		ThriveUlt.views.Timeline = ThriveUlt.views.Base.extend( {
			template: TVE_Dash.tpl( 'campaign/timeline' ),
			events: {
				'click #tvu-add-timeline-event': 'addEvent'
			},
			initialize: function () {
				this.listenTo( this.collection, 'add', this.render );
				this.listenTo( this.collection, 'update', this.render );
			},
			render: function () {
				this.$el.html( this.template( {item: this.model} ) );
				this.collection.each( _.bind( this.renderEvent, this ) );

				return this;
			},
			renderEvent: function ( item ) {
				if ( item.get( 'type' ) === ThriveUlt.event_type.end ) {
					return this.renderEndEvent( item );
				}

				var view = new ThriveUlt.views.Event( {
					model: item,
					collection: this.collection
				} );
				this.$( '#tvu-timeline' ).append( view.render().$el );

				return this;
			},
			renderEndEvent: function ( item ) {
				var view = new ThriveUlt.views.EndEvent( {
					model: item,
					collection: this.collection
				} );
				this.$( '#tvu-timeline' ).append( view.render().$el );

				return this;
			},
			addEvent: function () {
				if ( ! this.model.get( 'designs' ) || ! this.model.get( 'designs' ).size() ) {
					TVE_Dash.err( ThriveUlt.t.NoDesigns );
					return;
				}
				var item = new ThriveUlt.models.Event( {campaign_id: this.model.get( 'ID' )} ),
					event = this.collection.at( this.collection.size() - 2 );
				var actions = new ThriveUlt.collections.EventActions( event.get( 'actions' ).toJSON() );
				item.set( 'actions', actions );
				ThriveUlt.views.Timeline.openEventModal( item, null, this.collection );
			}
		}, { // static functions
			openEventModal: function ( event_model, original_event_model, event_collection ) {
				TVE_Dash.showLoader();
				var designs = new ThriveUlt.collections.Designs();
				designs.set_campaign_id( event_model.get( 'campaign_id' ) ).fetch().done( _.bind( function () {
					designs.mark_selected_event_actions( event_model.get( 'actions' ) );
					ThriveUlt.globals.campaign.set( 'designs', designs );// override the main design collection
					this.prototype.modal( ThriveUlt.views.ModalEditEvent, {
						model: event_model,
						original_model: original_event_model,
						designs: designs,
						collection: event_collection,
						'max-width': '45%'
					} );
				}, this ) ).fail( function ( response ) {
					TVE_Dash.err( response.responseText );
				} ).always( function () {
					TVE_Dash.hideLoader();
				} );
			}
		} );

		ThriveUlt.views.Event = ThriveUlt.views.Base.extend( {
			className: 'tvu-timeline-row',
			newClassName: 'tvu-animation-edit',
			durationClassName: 'tvu-animation-duration',
			deleteClassName: 'tvu-deleting',
			template: TVE_Dash.tpl( 'event/item' ),
			events: {
				'click .tvu-event-edit': 'edit',
				'click .tvu-event-delete': 'delete'
			},
			initialize: function () {
				this.listenTo( this.model, 'destroy', this.remove );
			},
			render: function () {
				this.$el.html( this.template( {item: this.model} ) );
				this.$actionsList = this.$el.find( '.tvu-event-actions' );

				this.renderActions();

				if ( this.model.get( 'status' ) === 'edited' ) {
					this.$el.addClass( this.newClassName );
					this.model.unset( 'status' );
				}

				if ( this.model.get( 'status' ) === 'duration_changed' || this.model.get( 'status' ) === 'new' ) {
					this.$el.addClass( this.durationClassName );
					setTimeout( _.bind( function () {
						this.model.unset( 'status' );
					}, this ), 2000 );
				}

				setTimeout( _.bind( function () {
					this.$el.removeClass( this.newClassName + '  ' + this.durationClassName )
				}, this ), 2000 );

				return this;
			},
			renderActions: function () {
				this.$actionsList.empty();
				var models = this.model.get( 'actions' ).models;

				if ( models.length > 0 ) {
					_.each( models, function ( item ) {
						this.$actionsList.append( TVE_Dash.tpl( 'event/action', {item: item} ) );
					}, this );
				} else {
					this.$actionsList.append( 'There are no Designs attached to this event. To add a new Design edit the event.' );
				}
			},
			edit: function () {
				ThriveUlt.views.Timeline.openEventModal( this.model.deepClone(), this.model, this.collection );
			},
			delete: function () {
				this.$el.addClass( this.deleteClassName );
				setTimeout( _.bind( function () {
					this.model.destroy( {
						success: function ( model, response, options ) {
							TVE_Dash.hideLoader();
						},
						error: function ( model, response, options ) {
							TVE_Dash.hideLoader();
							TVE_Dash.err( response.responseText );
						}
					} );
				}, this ), 500 );
			}
		} );

		ThriveUlt.views.EndEvent = ThriveUlt.views.Base.extend( {
			template: TVE_Dash.tpl( 'event/end' ),
			className: 'tvu-timeline-row tvu-timeline-row-last',
			render: function () {
				this.$el.html( this.template( {item: this.model} ) );

				return this;
			}
		} );

		/**
		 * Represents a row for a design in the EditEvent modal
		 *
		 * element: <tr>
		 * @field {ThriveUlt.models.Design} model
		 */
		ThriveUlt.views.EventModalDesignItem = ThriveUlt.views.Base.extend( {
			tagName: 'li',
			template: TVE_Dash.tpl( 'event/design-row' ),
			className: 'tvu-event-design-item tvd-collection-item',
			events: {
				'change .tvu-event-design-display': 'eventDisplayChange',
				'change .tvu-event-design-state': 'eventStateChange'
			},
			eventDisplayChange: function ( e ) {
				var $checkbox = $( e.target );
				this.model.set( 'event_display', $checkbox.is( ':checked' ) );
				this.$( '.tvu-event-design-state-container' )[$checkbox.is( ':checked' ) ? 'fadeIn' : 'fadeOut']( 150 );
			},
			eventStateChange: function ( e ) {
				var state_id = $( e.target ).val();
				this.model.set( 'event_state', state_id );
			},
			render: function () {
				this.$el.html( this.template( {design: this.model} ) );
				this.$( '.tvu-event-design-state' ).trigger( 'change' );
				return this;
			}
		} );

		/**
		 * This view should render the conversion events and listen to changes to them
		 * @type {void|*}
		 */
		ThriveUlt.views.ConversionEvents = ThriveUlt.views.Base.extend( {
			template: TVE_Dash.tpl( 'campaign/conversion_events' ),
			events: {
				'click #tvu-add-conversion-event': 'addEvent'
			},
			initialize: function () {
				this.listenTo( this.collection, 'add', this.renderOne );
			},
			render: function () {
				this.$el.html( this.template( {item: this.model} ) );
				this.collection.each( this.renderOne, this );

				return this;
			},
			/**
			 * Opens Modal for conversion event
			 */
			addEvent: function () {
				var item = new ThriveUlt.models.ConversionEvent( {
					campaign_id: this.model.get( 'ID' )
				} );

				item.set( 'trigger_options', {
					end_id: '',
					event: '',
					trigger: '',
					trigger_ids: ''
				} );

				this.modal( ThriveUlt.views.ModalConversionEvent, {
					model: item,
					collection: this.collection,
					'max-width': '40%'
				} );
			},
			renderOne: function ( item ) {
				var collection = new ThriveUlt.collections.Campaigns( ThriveUlt.globals.campaigns.where( {type: ThriveUlt.util.campaignType.evergreen} ) );

				var v = new ThriveUlt.views.ConversionEvent( {
					model: item,
					collection: collection
				} );
				this.$el.find( '#tvu-conversion-events' ).append( v.render().$el );

				return this;
			}
		} );

		ThriveUlt.views.ConversionEvent = ThriveUlt.views.Base.extend( {
			template: TVE_Dash.tpl( 'conversion/event' ),
			tagName: 'div',
			className: 'tvu-conversion-event',
			events: {
				'click .tvu-campaign-delete': 'delete',
				'click .tvu-campaign-edit': 'editEvent'

			},
			initialize: function () {
				this.listenTo( this.model, 'destroy', this.remove );
				this.listenTo( this.model, 'change', this.render );
			},
			render: function () {
				var campaign = ThriveUlt.globals.campaigns.findWhere( {ID: parseInt( this.model.get( 'trigger_options' ).end_id )} ),
					self = this;

				if ( campaign ) {
					campaign.createModelCollections();
				}

				self.$el.empty().append( self.template( {item: self.model, campaign: campaign} ) );

				return this;
			},
			delete: function () {
				TVE_Dash.showLoader();
				this.model.destroy( {
					success: function ( model, response, options ) {
						TVE_Dash.hideLoader();
					},
					error: function ( model, response, options ) {
						TVE_Dash.hideLoader();
						TVE_Dash.err( response.responseText );
					}
				} );
			},
			editEvent: function () {
				this.modal( ThriveUlt.views.ModalConversionEvent, {
					model: this.model.deepClone(),
					original_model: this.model,
					collection: this.collection,
					'max-width': '40%'
				} );
			}
		} );


		ThriveUlt.views.ConversionSummary = ThriveUlt.views.Base.extend( {
			template: TVE_Dash.tpl( 'conversion/conversion-summary' ),
			render: function () {
				this.$el.html( this.template( {item: this.model} ) );

				return this;
			}
		} );

		/**
		 * View for visit to conversion page trigger
		 * @type {void|*}
		 */
		ThriveUlt.views.TriggerTypeSpecific = ThriveUlt.views.Base.extend( {
			template: TVE_Dash.tpl( 'conversion/visit-page' ),
			events: {},
			render: function () {
				var model = this.model;
				this.$el.html( this.template( {item: this.model} ) );

				new ThriveUlt.PostSearch( this.$( '#tvu-specific-url' ), {
					url: ThriveUlt.ajaxurl( 'action=' + ThriveUlt.ajax_actions.admin_controller + '&route=progressiveGetPosts' ),
					select: function ( event, ui ) {
						model.get( 'trigger_options' ).trigger_ids = ui.item.id;
					},
					no_value_callback: function () {
						model.get( 'trigger_options' ).trigger_ids = '';
					},
					fetch_single: model.get( 'trigger_options' ).trigger_ids
				} );
			}
		} );

		/**
		 * View for user subscription page trigger
		 * @type {void|*}
		 */
		ThriveUlt.views.TriggerTypeConversion = ThriveUlt.views.Base.extend( {
			template: TVE_Dash.tpl( 'conversion/subscription' ),
			events: {
				'click .tvu-lead-conversion': 'saveCheckboxes'
			},
			initialize: function ( options ) {
				this.shortcodes = options.shortcodes;
			},
			render: function () {
				this.$el.html( this.template( {
					collection: this.collection,
					shortcodes: this.shortcodes,
					item: this.model
				} ) );
				this.collection.each( this.renderLeads, this );
				this.shortcodes.each( this.renderLeads, this );
			},
			renderLeads: function ( item ) {
				var v = new ThriveUlt.views.ConversionCheckbox( {
					model: item,
					triggers: this.model.get( 'trigger_options' ).trigger_ids
				} );
				if ( item.get( 'post_type' ) === ThriveUlt.util.leadtype.lead_group ) {
					this.$el.find( '.tvu-conversion-leads-checkboxes' ).append( v.render().$el );
				} else {
					this.$el.find( '.tvu-conversion-shortcodes-checkboxes' ).append( v.render().$el );
				}

				return this;
			},
			saveCheckboxes: function () {
				var checked = [];

				this.$el.find( 'input[type=checkbox]:checked' ).each( function () {
					var val = parseInt( $( this ).val() );
					checked.push( val );
				} );

				this.model.get( 'trigger_options' ).trigger_ids = checked;
			}
		} );

		/**
		 * View for lead groups checkboxes
		 * @type {void|*}
		 */
		ThriveUlt.views.ConversionCheckbox = ThriveUlt.views.Base.extend( {
			template: TVE_Dash.tpl( 'conversion/subscription-checkbox' ),
			className: 'tvu-posts-checkbox tvd-col tvd-s4',
			initialize: function ( options ) {
				this.triggers = options.triggers;
			},
			render: function () {
				this.$el.empty().append( this.template( {
					item: this.model,
					ids: this.triggers ? this.triggers : ''
				} ) );

				return this;
			}
		} );

		/**
		 * View for end campaign event
		 * @type {void|*}
		 */
		ThriveUlt.views.EventTypeEnd = ThriveUlt.views.Base.extend( {
			template: TVE_Dash.tpl( 'conversion/move-to-campaign' ),
			render: function () {
				this.$el.empty();

				return this;
			}
		} );

		/**
		 * View for move to another campaign event
		 * @type {void|*}
		 */
		ThriveUlt.views.EventTypeMove = ThriveUlt.views.Base.extend( {
			template: TVE_Dash.tpl( 'conversion/move-to-campaign' ),
			events: {
				'click .tvu-campaign-conversion': 'setMove'
			},
			render: function () {
				this.$el.html( this.template( {item: this.model, collection: this.collection} ) );
				this.collection.each( this.renderOne, this );

				return this;
			},
			renderOne: function ( item ) {
				var end_id = this.model.get( 'trigger_options' ).end_id,
					v = new ThriveUlt.views.ConversionRadio( {
						model: item,
						end_id: end_id ? end_id : ''
					} );
				this.$el.find( '#tvu-campaign-move-wrapper' ).append( v.render().$el );

				return this;
			},
			setMove: function ( e ) {
				var id = e.target.value;
				if ( this.model.get( 'ID' ) ) {

					var old_id = this.model.get( 'trigger_options' ).end_id;
					this.model.get( 'trigger_options' ).end_id_old = old_id;
				}
				this.model.get( 'trigger_options' ).end_id = id;

			}
		} );

		ThriveUlt.views.ConversionRadio = ThriveUlt.views.Base.extend( {
			template: TVE_Dash.tpl( 'conversion/move-to-campaign-radio' ),
			className: 'tvu-caimpaigns-radio tvd-col tvd-s4',
			initialize: function ( options ) {
				this.end_id = options.end_id;
			},
			render: function () {
				this.$el.empty().append( this.template( {
					item: this.model,
					end_id: this.end_id ? this.end_id : ''
				} ) );

				return this;
			}
		} );

		/**
		 * breadcrumbs view - renders breadcrumb links
		 */
		ThriveUlt.views.Breadcrumbs = ThriveUlt.views.Base.extend( {
			el: $( '#tvu-breadcrumbs-wrapper' )[0],
			template: TVE_Dash.tpl( 'breadcrumbs' ),
			/**
			 * setup collection listeners
			 */
			initialize: function () {
				this.$title = $( 'head > title' );
				this.original_title = this.$title.html();
				this.listenTo( this.collection, 'change', this.render );
				this.listenTo( this.collection, 'add', this.render );
			},
			/**
			 * render the html
			 */
			render: function () {
				this.$el.empty().html( this.template( {links: this.collection} ) );
			}
		} );
	} );

	/**
	 *
	 * @param {ThriveUlt.views.Base} view
	 */
	function open_display_settings( view ) {
		// this means we are on the "edit" page
		if ( view.model.has( 'display_settings' ) ) {
			ThriveUlt.objects.hangers = view.model.get( 'display_settings' );
			ThriveUlt.objects.savedTemplates = view.model.get( 'display_settings_tpl' );
			TVE_Dash.modal( ThriveUlt.views.CampaignSettings, {
				model: view.model,
				'max-width': '90%',
				width: '80%',
				collection: view.model.get( 'display_settings' )
			} );
			return;
		}
		TVE_Dash.showLoader();
		$.ajax( {
			url: view.model.getDisplaySettingsUrl(),
			data: {
				_nonce: ThriveUlt.admin_nonce
			},
			dataType: 'json'
		} ).done( function ( r ) {
			ThriveUlt.objects.savedTemplates = new ThriveUlt.models.TemplateList( r.savedTemplates );
			ThriveUlt.objects.hangers = new ThriveUlt.collections.Hangers( r.hangers );
			TVE_Dash.modal( ThriveUlt.views.CampaignSettings, {
				model: view.model,
				'max-width': '90%',
				width: '80%',
				collection: ThriveUlt.objects.hangers
			} );

		} ).fail( function ( r ) {
			console.error && console.error( arguments );
		} ).always( function () {
			setTimeout( function () {
				TVE_Dash.hideLoader();
			}, 300 );
		} );
	}

	/**
	 * ------------------------------------------ Utility function - views -----------------------------------------
	 */

	/**
	 * ajax-suggest post search for an input
	 * if fetch_single is passed and it's not empty, it will also fetch the selected post from the server and populate the input with it
	 *
	 * @param {object} $input jquery wrapper over the autocomplete input
	 * @param {object} options map of autocomplete options to control jquery ui autocomplete
	 * @constructor
	 */
	ThriveUlt.PostSearch = function ( $input, options ) {
		options = options || {};
		options.no_value_callback = options.no_value_callback || $.noop;
		options.change_callback = options.change_callback || $.noop;

		function matches() {
			var regex;

			if ( ! (regex = $input.data( 'allow-regex' )) ) {
				return false;
			}

			return $input.val().match( new RegExp( regex ) );
		}

		var defaults = {
			appendTo: $input.parent(),
			minLength: 2,
			delay: 200,
			change: function ( event, ui ) {
				if ( ! ui.item && ! $input.data( 'value-filled' ) && ! matches() ) {
					$input.val( '' );
					options.no_value_callback.apply( $input, arguments );
				}
				$input.data( 'value-filled', null );

				if ( matches() ) {
					options.change_callback.apply( $input, arguments );
				}
			}
		};

		options = $.extend( true, defaults, options );

		if ( ! options.source ) {
			options.source = options.url;
		}

		$input.autocomplete( options ).data( "ui-autocomplete" )._renderItem = function ( ul, item ) {
			var _class = '';
			if ( options.collection && options.collection.length ) {
				var model = options.collection.findWhere( {id: item.id} );
				if ( model ) {
					_class = 'tvu-selected-post';
				}
			}

			return $( "<li class='" + _class + "'>" ).append( "<span class='tvu-ps-result-title'>" + item.label + "</span><span class='tvu-ps-result-type'>" + item.type + "</span>" ).appendTo( ul );
		};

		$input.on( 'blur', function () {
			if ( ! $.trim( this.value ).length ) {
				options.no_value_callback.apply( $input, arguments );
			}
		} );

		if ( options.fetch_single && typeof options.fetch_single === 'number' ) {
			$input.addClass( 'ui-autocomplete-loading' );
			$.ajax( {
				url: ThriveUlt.ajaxurl( 'action=' + ThriveUlt.ajax_actions.admin_controller + '&route=getPostByID' ),
				data: {
					id: options.fetch_single
				},
				success: function ( result ) {
					$input.data( 'value-filled', 1 ).val( result.title ).removeClass( 'ui-autocomplete-loading' ).next( 'label' ).addClass( 'tvd-active' );
				}
			} );
		}

		if ( options.fetch_single && typeof options.fetch_single === 'string' ) {
			$input.data( 'value-filled', 1 ).val( options.fetch_single ).next( 'label' ).addClass( 'tvd-active' );
		}
	};
})
( jQuery );
;/**
 * Thrive Ultimatum Routers
 */
var ThriveUlt = ThriveUlt || {};
ThriveUlt.globals = ThriveUlt.globals || {};

(function ( $ ) {

	var Router = Backbone.Router.extend( {
		view: null,
		editCampaignView: null,
		$el: $( '#tvu-admin-wrapper' ),
		routes: {
			'dashboard': 'dashboard',
			'dashboard/campaign/:id': 'campaignEdit',
			'purge-cache': 'purgeCache',
			'dashboard/archived-campaigns': 'archived'
		},
		params: {},
		/**
		 * @var {object}
		 */
		breadcrumbs: {
			col: null,
			view: null
		},
		/**
		 * init the breadcrumbs collection and view
		 */
		init_breadcrumbs: function () {
			this.breadcrumbs.col = new ThriveUlt.collections.Breadcrumbs();
			this.breadcrumbs.view = new ThriveUlt.views.Breadcrumbs( {
				collection: this.breadcrumbs.col
			} )
		},
		/**
		 * set the current page - adds the structure to breadcrumbs and sets the new document title
		 *
		 * @param {string} section page hierarchy
		 * @param {string} label current page label
		 *
		 * @param {Array} [structure] optional the structure of the links that lead to the current page
		 */
		set_page: function ( section, label, structure ) {
			this.breadcrumbs.col.reset();
			structure = structure || {};
			/* Thrive Dashboard is always the first element */
			this.breadcrumbs.col.add_page( ThriveUlt.dash_url, ThriveUlt.t.Thrive_Dashboard, true );
			_.each( structure, _.bind( function ( item ) {
				this.breadcrumbs.col.add_page( item.route, item.label );
			}, this ) );
			/**
			 * last link - no need for route
			 */
			this.breadcrumbs.col.add_page( '', label );
			/* update the page title */
			var $title = $( 'head > title' );
			if ( ! this.original_title ) {
				this.original_title = $title.html();
			}
			$title.html( label + ' &lsaquo; ' + this.original_title )
		},
		/**
		 * dashboard route callback
		 */
		dashboard: function () {
			this.set_page( 'dashboard', ThriveUlt.t.Dashboard );
			$( '.tvd-material-tooltip' ).hide();

			if ( TVE_Dash.opened_modal_view ) {
				TVE_Dash.opened_modal_view.close();
			}

			TVE_Dash.showLoader();
			if ( this.view ) {
				this.view.remove();
			}

			this.renderHeader();

			this.view = new ThriveUlt.views.Dashboard( {
				collection: new ThriveUlt.collections.Campaigns( ThriveUlt.globals.campaigns.filter_archived( false ) )
			} );

			this.$el.html( this.view.render().$el );

			ThriveUlt.util.bind_wistia();
		},
		archived: function () {
			this.set_page( 'archived-campaigns', ThriveUlt.t.archived_campaigns, [
				{
					route: 'purge-cache',
					label: ThriveUlt.t.Dashboard
				}
			] );

			if ( TVE_Dash.opened_modal_view ) {
				TVE_Dash.opened_modal_view.close();
			}

			TVE_Dash.showLoader();

			if ( this.view ) {
				this.view.remove();
			}

			this.view = new ThriveUlt.views.ArchivedCampaigns( {
				collection: new ThriveUlt.collections.Campaigns( ThriveUlt.globals.campaigns.filter_archived() )
			} );

			this.$el.html( this.view.render().$el );

			this.renderHeader();

			TVE_Dash.hideLoader();
		},
		/**
		 * Edit campaign route callback
		 * @param id int
		 */
		campaignEdit: function ( id ) {
			if ( this.view ) {
				this.view.remove();
			}

			if ( TVE_Dash.opened_modal_view ) {
				TVE_Dash.opened_modal_view.close();
			}

			$( '.tvd-material-tooltip' ).hide();

			if ( ! id ) {
				ThriveUlt.router.navigate( '#dashboard', {trigger: true} );
				return;
			}

			var self = this,
				model = new ThriveUlt.models.Campaign( {ID: id} );

			TVE_Dash.showLoader();

			function set_page_breadcrumbs( section, title ) {
				self.set_page( section, title, [
					{
						route: 'purge-cache',
						label: ThriveUlt.t.Dashboard
					}
				] );
			}

			this.renderHeader();

			//fetch the model from the server and after that renders the EditCampaign view
			model.fetch().success( function () {

				ThriveUlt.globals.campaign = model;

				self.view = new ThriveUlt.views.EditCampaign( {
					model: model
				} );


				self.$el.html( self.view.render().$el );

				set_page_breadcrumbs( 'dashboard/campaign', model.get( 'post_title' ) );
				// when editing the title, also update the breadcrumbs / page title for the campaign dashboard
				model.on( 'change:post_title', function () {
					set_page_breadcrumbs( 'dashboard/campaign', model.get( 'post_title' ) );
				} );

				TVE_Dash.hideLoader();
				ThriveUlt.util.bind_wistia();
			} ).error( function ( response ) {
				TVE_Dash.err( response.responseText );
				TVE_Dash.hideLoader();
			} );

			this.params.campaign = model;
		},
		purgeCache: function () {
			TVE_Dash.showLoader();
			$.ajax( {
				type: 'post',
				url: ajaxurl,
				dataType: 'json',
				data: {
					action: ThriveUlt.ajax_actions.admin_controller,
					route: 'settings',
					custom: 'purge_cache',
					_nonce: ThriveUlt.admin_nonce
				}
			} ).done( _.bind( function ( response ) {
				ThriveUlt.globals.campaigns.reset( response );
				this.navigate( "#dashboard", {trigger: true} );
			}, this ) ).always( function () {
				TVE_Dash.hideLoader();
			} );
		},

		/**
		 * Creates a view for the header
		 */
		renderHeader: function () {
			if ( ! this.header ) {
				this.header = new ThriveUlt.views.Header( {
					el: '.tvu-header',
					model: ThriveUlt.globals.settings
				} );
			} else {
				this.header.setElement( $( '.tvu-header' ) );
			}

			this.header.render();
		}
	} );

	$( function () {
		//save campaign to globals because the user can access the edit campaign link directly
		//and if he edits the campaign name the name has to be updated on dashboard view too
		ThriveUlt.globals.campaigns = new ThriveUlt.collections.Campaigns( ThriveUlt.data.campaigns );
		ThriveUlt.globals.actions = new ThriveUlt.collections.EventActions( ThriveUlt.data.actions );
		ThriveUlt.globals.settings = new ThriveUlt.models.Settings( ThriveUlt.data.settings );
		ThriveUlt.globals.date_formats = new ThriveUlt.models.Settings( ThriveUlt.date_formats );

		ThriveUlt.router = new Router;
		ThriveUlt.router.init_breadcrumbs();
		Backbone.history.start( {hashchange: true} );

		if ( ! Backbone.history.fragment ) {
			ThriveUlt.router.navigate( '#dashboard', {trigger: true} );
		}
	} );

})( jQuery );
;/**
 * contains all javascript required for the Display Settings Manager
 */
var ThriveUlt = ThriveUlt || {};
ThriveUlt.views = ThriveUlt.views || {};

ThriveUlt.models = ThriveUlt.models || {};
ThriveUlt.collections = ThriveUlt.collections || {};
ThriveUlt.objects = ThriveUlt.objects || {};

(function ( $ ) {
	$( function () {

		/**
		 * Template Model
		 */
		ThriveUlt.models.Template = Backbone.Model.extend( {
			defaults: {
				name: '', description: '', hangers: ''
			},
			initialize: function ( model, options ) {
				this.set( 'hangers', new Backbone.Collection( [
					model.show_group_options, model.hide_group_options
				] ) );
			}
		} );

		/**
		 * Templates Collection
		 */
		ThriveUlt.collections.Templates = Backbone.Collection.extend( {
			model: ThriveUlt.models.Template
		} );

		ThriveUlt.models.TemplateList = Backbone.Model.extend( {
			defaults: {
				template_groups: []
			},
			initialize: function ( data ) {
				var _groups = [];
				_.each( data, function ( template_list, template_group_name ) {
					var _c = new ThriveUlt.collections.Templates( template_list );
					_c.group_name = template_group_name;
					if ( _c.size() ) {
						_c.group_tag = _c.at( 0 ).get( 'tag' );
					}
					_groups.push( _c );
				} );
				this.set( 'template_groups', _groups );
			},
			get_template: function ( where ) {
				var found = null;
				_.every( this.get( 'template_groups' ), function ( collection ) {
					found = collection.findWhere( where );

					return found ? false : true;
				} );

				return found;
			},
			name_exists: function ( name, tag ) {
				var found = false;
				_.each( this.get( 'template_groups' ), function ( collection ) {
					if ( collection.group_tag != tag ) {
						return true;
					}
					if ( collection.findWhere( {name: name} ) ) {
						found = true;
						return false; // stop iteration
					}
				} );
				return found;
			},
			size: function () {
				return this.get( 'template_groups' ).length;
			}
		} );

		/**
		 * Filter Model
		 */
		ThriveUlt.models.Filter = Backbone.Model.extend( {
			defaults: {
				cssClass: '', identifier: '', label: ''
			}
		} );

		/**
		 * Filters Collection
		 */
		ThriveUlt.collections.Filters = Backbone.Collection.extend( {
			model: ThriveUlt.models.Filter
		} );

		/**
		 * Option Model
		 */
		ThriveUlt.models.Option = Backbone.Model.extend( {
			defaults: {
				label: '', isChecked: false, id: '', type: null
			},
			validate: function ( optionModel ) {
				if ( ! optionModel.label.length ) {
					alert( 'Empty links are not accepted !' );
					return 'just return something';
				}
			},
			toggle: function () {
				this.set( 'isChecked', ! this.get( 'isChecked' ) );
			},
			check: function () {
				this.set( 'isChecked', true );
			},
			uncheck: function () {
				this.set( 'isChecked', false );
			}
		} );

		/**
		 * Options Collection
		 */
		ThriveUlt.collections.Options = Backbone.Collection.extend( {
			model: ThriveUlt.models.Option, countCheckedOptions: function () {
				var checked = 0;
				this.each( function ( model ) {
					checked += model.get( 'isChecked' ) ? 1 : 0
				} );
				return checked;
			}
		} );

		/**
		 * Tab Model
		 */
		ThriveUlt.models.Tab = Backbone.Model.extend( {
			defaults: function () {
				return {
					identifier: '', label: '', isActive: false, actions: [], filters: []
				}
			},
			initialize: function ( jsonModel ) {
				this.set( 'options', new ThriveUlt.collections.Options( jsonModel.options ) );
				this.set( 'filters', new ThriveUlt.collections.Filters( jsonModel.filters ) );
			},
			getTabIdentifierFromTabId: function ( tabId ) {
				return tabId.replace( "tvu_leads_tab_", "" );
			},
			getTabIdFromIdentifier: function () {
				return "tvu_leads_" + (
						this.get( 'exclusion' ) ? "hide" : "show"
					) + "_tab_" + this.get( 'identifier' );
			},
			getTabContentIdentifier: function () {
				return "tvu_leads_tab_content_" + this.get( 'identifier' );
			},
			countCheckedOptions: function () {
				if ( this.get( 'identifier' ) === 'others' ) {
					var count = 0;
					this.get( 'options' ).each( function ( option ) {
						if ( option.get( 'type' ) === 'direct_url' || option.get( 'isChecked' ) ) {
							count ++;
						}
					} );
					return count;
				}
				return this.get( 'options' ).countCheckedOptions();
			},
			uncheckAll: function () {
				var to_remove = [], opts = this.get( 'options' );

				opts.each( function ( option ) {
					option.set( 'isChecked', false );
					if ( option.get( 'type' ) === 'direct_url' ) {
						to_remove.push( option );
					}
				} );
				_.forEach( to_remove, function ( opt ) {
					opts.remove( opt );
				} );
			}
		} );

		/**
		 * Tabs Collection
		 */
		ThriveUlt.collections.Tabs = Backbone.Collection.extend( {
			model: ThriveUlt.models.Tab
		} );

		/**
		 * Hanger Model
		 */
		ThriveUlt.models.Hanger = Backbone.Model.extend( {
			defaults: function () {
				return {
					identifier: '', tabs: ''
				}
			},
			initialize: function ( model, options ) {
				this.set( 'tabs', new ThriveUlt.collections.Tabs( model.tabs ) );
			},
			countCheckedOptions: function () {
				var checked = 0;
				this.get( 'tabs' ).each( function ( tab ) {
					checked += tab.countCheckedOptions();
				} );
				return checked;
			},
			uncheckAll: function () {
				this.get( 'tabs' ).each( function ( tab ) {
					tab.uncheckAll();
				} );
			},
			getDataForSave: function () {
				var data = {
					tabs: []
				};
				this.get( 'tabs' ).each( function ( tab, tabIndex ) {
					data.tabs[tabIndex] = [];
					tab.get( 'options' ).each( function ( option ) {
						if ( option.get( 'isChecked' ) || option.get( 'type' ) == 'direct_url' ) {
							data.tabs[tabIndex].push( option.get( 'id' ) );
						}
					} );
				} );
				return data;
			}, /**
			 * get an array of labels for the selected options
			 *
			 * @param {Array} [saved_data] optional expects an array of saved options (just the ids)
			 *
			 * @returns {Array}
			 */
			get_selected_labels: function ( saved_data ) {
				var _tabs = this.get( 'tabs' ), labels = [];
				if ( typeof saved_data === 'undefined' ) {
					saved_data = this.getDataForSave().tabs;
				}
				_.each( saved_data, function ( tab, tabIndex ) {
					_.each( tab, function ( _id, optionIndex ) {
						labels.push( _tabs.at( tabIndex ).get( 'options' ).findWhere( {id: _id} ).get( 'label' ) );
					} );
				} );
				return labels;
			}
		} );

		/**
		 * Hangers Collection
		 */
		ThriveUlt.collections.Hangers = Backbone.Collection.extend( {
			model: ThriveUlt.models.Hanger, uncheckAll: function () {
				this.each( function ( hanger ) {
					hanger.uncheckAll();
				} );
			}, /**
			 * builds the summary text for the display settings chosen for this campaign
			 * the summary is built in the following format:
			 * "Shown on Pages, Posts & 2 more. Hidden on Archives, Front Page"
			 *
			 * @param {Number} [limit] optional, limit the number of options shown
			 *
			 * @return {string}
			 */
			get_display_summary: function ( limit ) {
				limit = 'undefined' === typeof limit ? 2 : parseInt( limit );
				limit = isNaN( limit ) ? 2 : limit;

				var display_labels = this.at( 0 ).get_selected_labels(), hide_labels = this.at( 1 ).get_selected_labels();

				/**
				 * builds the string from the arr and appends & more if necessary
				 *
				 * @param {Array} arr
				 * @param {string} prefix text to be shown before the list
				 *
				 * @return {String}
				 */
				function build_string_from_options( arr, prefix ) {
					var len;
					if ( ! arr || ! (
							len = arr.length
						) ) {
						return '';
					}
					var _str = arr.slice( 0, limit ).join( ', ' );
					if ( len > limit ) {
						_str += ' &amp; ' + (
								len - limit
							) + ' ' + ThriveUlt.t.more;
					}
					return (
						_str ? prefix + ' ' + _str : ''
					);
				}

				var results = _.filter( [
					build_string_from_options( display_labels, ThriveUlt.t.Showing_on ),
					build_string_from_options( hide_labels, ThriveUlt.t.Hidden_on )
				], function ( item ) {
					return item.length > 0;
				} );

				return results.length ? results.join( '. ' ) : ThriveUlt.t.Not_set;
			}, /**
			 * checks if any display options have been setup for this campaign
			 *
			 * @returns {boolean}
			 */
			has_saved_options: function () {
				return this.at( 0 ).get_selected_labels().concat( this.at( 1 ).get_selected_labels() ).length > 0;
			}
		} );

		ThriveUlt.views.FiltersView = ThriveUlt.views.Base.extend( {
			className: 'tvu_leads_filtersContainer',
			events: {
				'click .tvu_leads_tabFilter': function ( e ) {
					this.filterClicked( jQuery( e.target ) );
				}
			},
			initialize: function () {
			},
			render: function () {
				var thisView = this;
				_.each( this.collection.models, function ( filterModel ) {
					thisView.renderFilter( filterModel );
				} );
			},
			renderFilter: function ( filterModel ) {
				var template = TVE_Dash.tpl( 'campaign/settings/filter', filterModel.toJSON() );
				this.$el.append( template );
			},
			filterClicked: function ( $filter ) {
				this.$el.find( '.tvu_leads_tabFilter' ).removeClass( 'selected' );
				$filter.addClass( 'selected' );
				_.each( this.$el.parent().find( '.tvu_leads_optionContainer,.tvu_leads_tab_content_toggle' ), function ( optionDom ) {
					var $option = jQuery( optionDom );
					//if the option type match the filter's ID
					if ( $option.children( 'label' ).data( 'type' ) === $filter.attr( 'id' ) ) {
						$option.show();
					} else {
						$option.hide();
					}
				} );
				this.renderSelectedFilter( $filter.text() );
			},
			/**
			 * Display the selected filter and remove the old one(if it exists)
			 * @param filterLabel
			 */
			renderSelectedFilter: function ( filterLabel ) {
				var $selectedFilter = this.$el.next( '.tvu_leads_selectedFilter' );
				if ( $selectedFilter.length ) {
					$selectedFilter.remove();
				}
				var template = TVE_Dash.tpl( 'campaign/settings/selected_filter', {filter: filterLabel} );
				this.$el.after( template );
			}
		} );

		ThriveUlt.views.OptionView = ThriveUlt.views.Base.extend( {
			className: 'tvu_leads_optionContainer tvd-col tvd-s6 tvd-m6 tvd-l2',
			events: {
				'click .tvu_leads_toggle_option': 'toggle', 'click .tvu_leads_removeDirectLink': 'removeLink'
			},
			initialize: function () {
				this.listenTo( this.model, 'change:isChecked', this.isCheckedChanged );
			},
			render: function () {
				if ( this.model.get( 'type' ) === 'direct_url' ) {
					this.$el.removeClass( 'tvd-l2' );
					//render a specific tempalte based on option type
					var optionTemplate = TVE_Dash.tpl( 'campaign/settings/direct_url', this.model.toJSON() );
				} else {
					var optionTemplate = TVE_Dash.tpl( 'campaign/settings/option', this.model.toJSON() );
				}
				if ( this.model.get( 'type' ) === 'item_page' ) {
					this.$el.removeClass( 'tvd-l2' ).addClass( 'tvd-l3' );
				}
				this.$el.append( optionTemplate );
			},
			toggle: function () {
				this.model.toggle();
			},
			isCheckedChanged: function () {
				this.$el.find( 'input[type="checkbox"]' ).prop( "checked", this.model.get( 'isChecked' ) );
			},
			removeLink: function () {
				this.model.collection.remove( this.model );
			}
		} );

		ThriveUlt.views.tab_custom_views = {
			_autocomplete: function ( $input, route, onSelect ) {
				$input.autocomplete( {
					appendTo: $input.parent(), minLength: 2, delay: 200, source: function ( request, responseFn ) {
						jQuery.ajax( {
							url: ajaxurl, dataType: 'json', data: {
								action: ThriveUlt.ajax_actions.admin_controller,
								route: route,
								q: request.term,
								tax: 'post_tag',
								_nonce: ThriveUlt.admin_nonce
							}
						} ).done( function ( entries ) {
							responseFn( entries );
						} );
					}, select: onSelect
				} );
			},
			taxonomy_terms: Backbone.View.extend( {
				template: TVE_Dash.tpl( 'campaign/settings/tags_filter' ),

				render: function ( $parentElem ) {
					var self = this;

					this.$el.html( this.template( this.model.attributes ) );
					this.$el.addClass( 'tvu_leads_tab_content_toggle' ).hide();
					ThriveUlt.views.tab_custom_views._autocomplete( this.$el.find( '.tvu-leads-autocomplete' ), 'tagSearch', function ( event, ui ) {
						self.model.get( 'options' ).add( {
							isChecked: true, type: 'post_tag', id: ui.item.id, label: ui.item.label
						} );
						this.value = '';
						return false;
					} );

					$parentElem.find( '.tvu_leads_selectedFilter' ).after( this.$el );

					return this;
				}
			} ),
			posts: Backbone.View.extend( {
				template: TVE_Dash.tpl( 'campaign/settings/posts_filter' ),

				render: function ( $parentElem ) {
					var option_collection = this.model.get( 'options' );
					this.$el.html( this.template( this.model.attributes ) );

					ThriveUlt.views.tab_custom_views._autocomplete( this.$el.find( '.tvu-leads-autocomplete' ), 'postSearch', function ( event, ui ) {
						option_collection.add( {
							isChecked: true,
							type: '',
							id: ui.item.id,
							label: ui.item.label
						} );
						this.value = '';
						return false;
					} );
					$parentElem.find( '.tab-content-title' ).after( this.$el );
				}
			} )
		};

		ThriveUlt.views.TabContentView = ThriveUlt.views.Base.extend( {

			actionContainerClass: 'tvu_leads_actionContainer',
			actionDefaultClass: 'tvd-btn-flat tvd-btn-flat-dark tvd-waves-effect',

			className: 'tvd-row',

			events: {
				"click .selectAll": 'checkAll',
				"click .selectNone": 'checkNone',
				'click .tvu_leads_addDirectLink': 'addDirectLinkClicked',
				'keypress .tvu_leads_directUrl': 'addDirectLinkClicked'
			},
			initialize: function () {
				/**
				 * When a new model is added to the collection just render the options once again
				 */
				this.listenTo( this.model.get( 'options' ), 'add', function ( model ) {
					this.renderOptions( model.get( 'type' ) );
					this.model.trigger( 'change', this.model );
				} );
				this.listenTo( this.model.get( 'options' ), 'remove', function () {
					this.renderOptions();
					this.model.trigger( 'change', this.model );
				} );
			},
			renderOthersTab: function () {
				this.$el.attr( 'id', this.model.getTabIdFromIdentifier() ).append( '<h5 class="tab-content-title">Visitor Status</h5>' ).append( '<div class="tl-visitor-status clearfix"></div>' ).append( '<div class="clear"></div><h5 class="tab-second-title">Direct URLs</h5>' ).append( '<div class="tl-direct-urls tvd-row tvu_leads_tab_content_direct_urls clearfix"></div>' );

				this.renderOptionsOthers();
				this.renderAddDirectUrlForm();
			},
			renderOptionsOthers: function () {
				this.$el.find( '.tl-visitor-status' ).empty();
				this.$el.find( '.tl-direct-urls' ).empty();

				var thisView = this;
				_.each( this.model.get( 'options' ).models, function ( optionModel ) {
					var $targetDiv = (
						optionModel.get( 'type' ) == 'direct_url'
					) ? thisView.$el.find( '.tl-direct-urls' ) : thisView.$el.find( '.tl-visitor-status' );
					optionModel.set( 'base_id', thisView.model.getTabIdFromIdentifier() );
					var optionView = new ThriveUlt.views.OptionView( {
						model: optionModel
					} );
					$targetDiv.append( optionView.el );
					optionView.render();
					optionModel.on( 'change:isChecked', function () {
						thisView.model.trigger( 'change' );
					} );
				} );
			},

			render: function () {
				if ( this.model.get( 'identifier' ) === 'others' ) {
					return this.renderOthersTab();
				}
				this.$el.append( '<span class="tab-content-title"></span>' ).attr( 'id', this.model.getTabIdFromIdentifier() );

				this.renderActions();
				this.renderOptions();
				this.renderFilters();
				this.renderCustomHtml();
			},
			renderCustomHtml: function () {
				if ( ! ThriveUlt.views.tab_custom_views[this.model.get( 'identifier' )] ) {
					return;
				}
				var v = new ThriveUlt.views.tab_custom_views[this.model.get( 'identifier' )]( {
					model: this.model
				} );
				v.render( this.$el );
			},
			renderOptions: function ( type ) {
				if ( this.model.get( 'identifier' ) === 'others' ) {
					return this.renderOptionsOthers();
				}
				this.$el.find( '.tvu_leads_optionContainer' ).remove();
				var thisView = this;
				_.each( this.model.get( 'options' ).models, function ( optionModel ) {
					thisView.renderOption( optionModel, type );
					optionModel.on( 'change:isChecked', function () {
						thisView.model.trigger( 'change', thisView.model );
					} );
				} );
			},
			renderOption: function ( optionModel, type ) {
				optionModel.set( 'base_id', this.model.getTabIdFromIdentifier() );
				var optionView = new ThriveUlt.views.OptionView( {
					model: optionModel
				} );
				this.$el.append( optionView.el );
				optionView.render();
				if ( type && optionModel.get( 'type' ) != type ) {
					optionView.$el.hide();
				}
			},
			renderActions: function () {
				if ( ! this.model.get( 'actions' ).length ) {
					return;
				}
				var $actionsContainer = jQuery( '<div class="' + this.actionContainerClass + ' tvu_leads_clearfix" />' ), thisView = this;
				this.$el.append( $actionsContainer );
				if ( ! this.model.get( 'actions' ).length ) {
					return;
				}
				_.each( this.model.get( 'actions' ), function ( actionObject ) {
					thisView.$el.find( "." + thisView.actionContainerClass ).append( '<a class="' + thisView.actionDefaultClass + ' ' + actionObject.cssClass + '" href="javascript:void(0)">' + actionObject.label + '</a>' );
				} );
			},
			renderFilters: function () {
				if ( ! this.model.get( 'filters' ).length ) {
					return;
				}
				var filtersView = new ThriveUlt.views.FiltersView( {
					model: this.model, collection: this.model.get( 'filters' )
				} );
				filtersView.render();
				this.$el.find( '.tab-content-title' ).after( filtersView.el );

				/**
				 * Call the filterClicked to simulate the click on a first filter
				 * @see filtersView.filterClicked()
				 */
				filtersView.filterClicked( filtersView.$el.children().first() );
			},
			/**
			 * Append form of adding a new link to the element $el
			 */
			renderAddDirectUrlForm: function () {
				var template = TVE_Dash.tpl( 'campaign/settings/add_direct_url_form', {} );
				this.$el.append( template );
			},
			checkAll: function () {
				_.each( this.model.get( 'options' ).models, function ( optionModel ) {
					optionModel.check();
				} )
			},
			checkNone: function () {
				_.each( this.model.get( 'options' ).models, function ( optionModel ) {
					optionModel.uncheck();
				} )
			},
			addDirectLinkClicked: function ( e ) {
				/**
				 * If button is clicked or enter key is pressed
				 * Otherwise do nothing
				 */
				if ( e.type === 'keypress' && e.which !== 13 ) {
					return;
				}

				var $button = jQuery( e.target ), $container = $button.parents( '.tvd-add-link-row' ), $input = $container.find( 'input' );

				/**
				 * Create and add new option model to the collection
				 * Event "add" is triggered on collection
				 * @see this.initialize()
				 */
				this.model.get( 'options' ).add( {
					id: $input.val().trim(), label: $input.val().trim(), type: 'direct_url'
				}, {validate: true} );
				$input.val( '' );
			}
		} );

		ThriveUlt.views.TabLabelView = ThriveUlt.views.Base.extend( {

			template: TVE_Dash.tpl( 'campaign/settings/tab_label' ),

			activeClass: 'tvu_leads_active_tab',

			className: "tvd-tab",

			tagName: 'li',

			events: {
				//'click': 'activate'
			},
			initialize: function () {
				this.listenTo( this.model, 'change', this.render );
			},
			render: function () {
				this.$el.html( this.template( {tab: this.model} ) );
			}
		} );

		ThriveUlt.views.HangerView = ThriveUlt.views.Base.extend( {

			template: TVE_Dash.tpl( 'campaign/settings/hanger' ),

			initialize: function () {
			},
			render: function () {
				var template = this.template( this.model.toJSON() ), thisView = this;
				this.$el.html( template );

				this.$tabLabelsContainer = this.$el.find( '.tvu_leads_tabs' );
				this.$tabContentsContainer = this.$el.find( '.tvu_leads_tabs_wrapper' );

				_.each( this.model.get( 'tabs' ).models, function ( tabModel ) {
					tabModel.set( "exclusion", thisView.model.get( 'identifier' ).indexOf( "hide" ) >= 0 );
					var tabLabelView = thisView.renderTabLabels( tabModel );
					var tabContentView = thisView.renderTabContent( tabModel );
					tabModel.on( 'change:isActive', _.bind( thisView.activateTab, thisView, tabLabelView, tabContentView ) );
					tabModel.on( 'change', _.bind( function () {
						if ( this.model.get( 'identifier' ) === 'hide_options' ) {
							jQuery( ".exclusions-count" ).html( '(' + this.model.countCheckedOptions() + ')' );
						} else {
							jQuery( ".inclusions-count" ).html( '(' + this.model.countCheckedOptions() + ')' );
						}
					}, thisView, tabModel ) );
				} );

				this.model.get( 'tabs' ).at( 0 ).set( 'isActive', true );
			},
			renderTabLabels: function ( tabModel ) {
				var tabLabelView = new ThriveUlt.views.TabLabelView( {
					model: tabModel
				} );
				this.$tabLabelsContainer.append( tabLabelView.el );
				tabLabelView.render();
				return tabLabelView;
			},
			renderTabContent: function ( tabModel ) {
				var tabContentView = new ThriveUlt.views.TabContentView( {
					model: tabModel
				} );
				this.$tabContentsContainer.append( tabContentView.el );
				tabContentView.render();
				return tabContentView;
			},
			activateTab: function ( tabLabelView, tabContenView, tabModel, isActive ) {

				this.$tabLabelsContainer.find( 'li' ).removeClass( tabLabelView.activeClass ).find( 'a' ).removeClass( 'tvd-active' );
				this.$tabContentsContainer.find( '.tvu_leads_tabs_content' ).hide();

				tabLabelView.$el.addClass( tabLabelView.activeClass ).find( '> a' ).addClass( 'tvd-active' );
				tabContenView.$el.show();

				tabModel.attributes.isActive = false;
			}
		} );

		ThriveUlt.views.CampaignSettings = TVE_Dash.views.Modal.extend( {

			template: TVE_Dash.tpl( 'campaign/settings/main' ),

			events: {
				'click .tvu_leads_save_widget_options': 'saveOptions',
				'click .tvu_leads_add_new_template': 'saveTemplate',
				'click .tvu_leads_load_saved_options': 'loadTemplate',
				'click .tvd-modal-close': 'close',
				'click .tl-toggle-tab-display': 'toggleTabDisplay',
				'blur input#tvu_leads_new_template_name': function ( e ) {
					$( e.target ).removeClass( 'tvd-invalid' );
				},
				'keyup input#tvu_leads_new_template_name': function ( e ) {
					if ( e.which === 13 ) {
						this.saveTemplate();
					}
				}
			},
			toggleTabDisplay: function ( e ) {
				var $elem = jQuery( e.currentTarget ), collapsed = $elem.hasClass( 'collapsed' ), $target = jQuery( $elem.data( 'target' ) );

				if ( collapsed ) {
					$target.hide( 0 ).removeClass( 'tvd-not-visible' ).slideDown( 200 );
				} else {
					$target.slideUp( 200, function () {
						$target.addClass( 'tvd-not-visible' );
					} );
				}

				$elem.toggleClass( 'collapsed' );
				$elem.toggleClass( 'hover' );
			},
			afterRender: function () {

				this.$templatesList = this.$el.find( ".tvu_leads_saved_options" );

				var thisView = this;
				_.each( this.collection.models, function ( hanger ) {
					thisView.renderHangerView( hanger );
				} );
				this.$el.addClass( 'tvd-modal-fixed-footer tvd-modal-display-settings' );
				this.renderTemplatesList();

			},
			_render: function () {
				this.$el.html( this.template( {model: this.model} ) );
				this.afterRender();

				TVE_Dash.materialize( this.$el );
			},
			renderHangerView: function ( hanger ) {
				var hangerView = new ThriveUlt.views.HangerView( {
					model: hanger, el: jQuery( "#" + hanger.get( 'identifier' ) )
				} );
				hangerView.render();
			},
			renderTemplatesList: function () {
				var thisView = this,
					has_optgroup = ThriveUlt.objects.savedTemplates.size() > 1;

				if ( this.$templatesList.data( 'select2' ) ) {
					this.$templatesList.data( 'select2' ).destroy();
				}

				this.$templatesList.find( 'optgroup, option' ).each( function ( index ) {
					if ( index > 0 ) {
						//keep the first option
						$( this ).remove();
					}
				} );
				_.every( ThriveUlt.objects.savedTemplates.get( 'template_groups' ), function ( template_group ) {
					if ( template_group.size() === 0 ) {
						return true;
					}
					var $optgroup = $( '<optgroup label="' + template_group.group_name + '"></optgroup>' );
					template_group.each( function ( template ) {
						( has_optgroup ? $optgroup : thisView.$templatesList ).append( '<option value="' + template.get( 'id' ) + '">' + template.get( 'name' ) + '</option>' );
					} );
					if ( has_optgroup ) {
						thisView.$templatesList.append( $optgroup );
					}
					return true;
				} );

				this.$templatesList.select2();
			},
			loadTemplate: function () {
				if ( this.$templatesList.val() === '' ) {
					return this.tvd_show_errors( {
						field: 'load_template', message: ThriveUlt.t.Select_template
					} );
				}
				var templateModel = ThriveUlt.objects.savedTemplates.get_template( {id: this.$templatesList.val()} );
				var self = this;
				TVE_Dash.showLoader();
				jQuery.ajax( {
					url: ajaxurl, data: {
						action: ThriveUlt.ajax_actions.admin_controller,
						route: 'displaySettingsLoadTemplate',
						campaign_id: this.model.get( 'ID' ),
						template_id: this.$templatesList.val(),
						_nonce: ThriveUlt.admin_nonce
					}
				} ).done( function ( response ) {
					self.collection.reset( response );
					self._render();
					TVE_Dash.hideLoader();
					TVE_Dash.success( ThriveUlt.util.printf( ThriveUlt.t.template_loaded, templateModel.get( 'name' ) ) );
				} );
			},
			saveOptions: function ( e ) {
				var data = {
					options: [
						JSON.stringify( this.collection.at( 0 ).getDataForSave() ),
						JSON.stringify( this.collection.at( 1 ).getDataForSave() )
					], campaign_id: this.model.get( 'ID' ), _nonce: ThriveUlt.admin_nonce
				}, $button = $( e.target ), label = $button.html(), self = this;

				TVE_Dash.showLoader();
				this.btnLoading( $button );
				$.ajax( {
					url: ajaxurl + '?action=' + ThriveUlt.ajax_actions.admin_controller + '&route=displaySettingsSave',
					type: 'post',
					dataType: 'json',
					data: data,
					success: function ( response ) {
						if ( ! response.success ) {
							return;
						}
						TVE_Dash.success( ThriveUlt.t.Display_settings_saved );
						self.close();
					},
					error: function ( jqXHR, testStatus, errorThrown ) {
						TVE_Dash.err( ThriveUlt.t.GeneralError + ' ( Error info: <strong>' + errorThrown + '</strong> )' );
					},
					complete: function () {
						$button.html( label ).attr( 'disabled', false );
						TVE_Dash.hideLoader();
					}
				} );
			},
			saveTemplate: function ( e ) {
				var $name = $( "input[name='tvu_leads_new_template_name']" ), self = this;

				if ( ! $name.val().trim().length ) {
					$name.addClass( 'tvd-invalid' ).focus();
					return;
				}
				var data = {
					name: $name.val().trim(), options: [
						JSON.stringify( this.collection.at( 0 ).getDataForSave() ),
						JSON.stringify( this.collection.at( 1 ).getDataForSave() )
					], _nonce: ThriveUlt.admin_nonce
				};

				var templateExists = false;

				//check if for already existing template with the same name
				if ( ThriveUlt.objects.savedTemplates.name_exists( data.name, 'TU' ) ) {
					templateExists = ! confirm( '"' + data.name + '" template already exists. Do you want to overwrite it ?' );
				}

				if ( templateExists ) {
					return;
				}
				TVE_Dash.showLoader();
				$.ajax( {
					type: 'post',
					dataType: 'json',
					data: data,
					url: ajaxurl + '?action=' + ThriveUlt.ajax_actions.admin_controller + '&route=displaySettingsSaveTemplate',
					success: function ( response ) {
						if ( ! response.success ) {
							TVE_Dash.err( response.message ? response.message : ThriveUlt.t.GeneralError );
							return;
						}
						$name.val( '' );

						//reinitialize saved templates collection with the response which contains the new template just added
						ThriveUlt.objects.savedTemplates = new ThriveUlt.models.TemplateList( response.templates );

						self.renderTemplatesList();

						//set as selected the new saved template
						self.$templatesList.find( 'option' ).each( function () {
							if ( $( this ).text() === data.name ) {
								$( this ).attr( 'selected', 'selected' );
							}
						} );
						TVE_Dash.success( ThriveUlt.t.DisplaySettingsTemplateSaved );
					},
					error: function ( jqXHR, testStatus, errorThrown ) {
						TVE_Dash.err( ThriveUlt.t.GeneralError + ' ( Error info: <strong>' + errorThrown + '</strong> )' );
					},
					complete: function () {
						TVE_Dash.hideLoader();
					}
				} );
			}, /**
			 * on close, we should update the parent view with a summary of the selected options
			 */
			onClose: function () {
				var summary = this.collection.get_display_summary();
				this.model.set( 'display_settings_summary', summary );

				ThriveUlt.globals.campaigns.findWhere( {ID: this.model.get( 'ID' )} ).set( 'has_display_settings', summary != "Not set" );
			}
		} );
	} );
})( jQuery );
