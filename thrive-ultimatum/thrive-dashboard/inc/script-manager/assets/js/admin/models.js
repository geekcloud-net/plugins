(function ( $ ) {

	var utils = require( '../utils.js' ),
		models = {};

	models.ScriptItem = Backbone.Model.extend( {
		idAttribute: 'id',
		defaults: {
			label: '',
			status: 0,
			placement: '',
			code: '',
			order: 0,
			icon: 'nonstandard'
		},

		url: function () {
			return TVD_SM_CONST.routes.scripts + (this.get( 'id' ) ? '/' + this.get( 'id' ) : '');
		},

		/**
		 * Set nonce header before every Backbone sync.
		 *
		 * @param {string} method.
		 * @param {Backbone.Model} model.
		 * @param {{beforeSend}, *} options.
		 * @returns {*}.
		 */
		sync: function ( method, model, options ) {
			var beforeSend;

			options = options || {};

			options.cache = false;
			options.url = this.url();

			if ( ! _.isUndefined( TVD_SM_CONST.nonce ) && ! _.isNull( TVD_SM_CONST.nonce ) ) {
				beforeSend = options.beforeSend;

				options.beforeSend = function ( xhr ) {
					xhr.setRequestHeader( 'X-WP-Nonce', TVD_SM_CONST.nonce );

					if ( beforeSend ) {
						return beforeSend.apply( this, arguments );
					}
				};
			}
			return Backbone.sync( method, model, options );
		}
	} );

	models.collections = {
		ScriptCollection: Backbone.Collection.extend( {

			model: models.ScriptItem,
			comparator: 'order',
			url: function () {
				return TVD_SM_CONST.routes.scripts;
			},
			/**
			 * Set nonce header before every Backbone sync.
			 *
			 * @param {string} method.
			 * @param {Backbone.Model} model.
			 * @param {{beforeSend}, *} options.
			 * @returns {*}.
			 */
			sync: function ( method, model, options ) {
				var beforeSend;

				options = options || {};

				options.cache = false;

				if ( ! _.isUndefined( TVD_SM_CONST.nonce ) && ! _.isNull( TVD_SM_CONST.nonce ) ) {
					beforeSend = options.beforeSend;

					options.beforeSend = function ( xhr ) {
						xhr.setRequestHeader( 'X-WP-Nonce', TVD_SM_CONST.nonce );

						if ( beforeSend ) {
							return beforeSend.apply( this, arguments );
						}
					};
				}
				return Backbone.sync( method, model, options );
			}
		} )
	};

	module.exports = models;
})
( jQuery );