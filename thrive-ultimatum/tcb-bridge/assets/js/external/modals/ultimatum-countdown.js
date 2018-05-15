/**
 * Created by Ovidiu on 7/20/2017.
 */
var _instance = null,
	shortcode_base = TVE.shortcode_manager;
module.exports = TVE.modal.base.extend( {
	after_initialize: function () {
		this.$el.addClass( 'medium' );
		this.$campaign = this.$el.find( '#tve_ult_campaign' );
		this.$shortcode = this.$el.find( '#tve_ult_shortcode' );
	},
	before_open: function () {
		if ( ! TVE.ActiveElement.hasClass( 'tcb-elem-placeholder' ) ) {
			var _config = this.get_config();
			if ( typeof _config['tve_ult_campaign'] !== 'undefined' && typeof _config['tve_ult_shortcode'] !== 'undefined' ) {
				this.$campaign.val( _config['tve_ult_campaign'] ).trigger( 'change' );
				this.$shortcode.val( _config['tve_ult_shortcode'] );
			}
		}
	},
	/**
	 * Returns Ultimatum Shortcode Settings
	 *
	 * @returns {*}
	 */
	get_config: function () {
		var shortcode_config = shortcode_base( TVE.ActiveElement.find( '.thrive-shortcode-config' ), 'ultimatum_shortcode' );

		return shortcode_config.get();
	},
	campaign_changed: function ( event, dom ) {
		var _$shortcode = this.$shortcode;

		_$shortcode.html( '' );
		if ( dom.value ) {
			jQuery.each( tve_ult_page_data.tu_shortcode_campaigns[dom.value].designs, function ( id, name ) {
				var $option = jQuery( '<option/>' ).text( name ).val( id );
				_$shortcode.append( $option );
			}, this );
		}
	},
	generate_countdown_html: function () {
		var self = this,
			$target = TVE.ActiveElement;

		this.countdown_ajax( {
			tve_ult_campaign: this.$campaign.val(),
			tve_ult_shortcode: this.$shortcode.val()
		} ).done( function ( response ) {
			if ( response ) {
				$target.html( response ).removeClass( 'tcb-elem-placeholder' );
			}
		} ).error( function ( error ) {
			TVE.page_message( error.responseText, 2, 5000 );
		} ).complete( function () {
			TVE.main.overlay( 'close' );
			self.close();
		} );

	},
	countdown_ajax: function ( data, ajax_param ) {
		var params = {
			type: 'post',
			dataType: 'json',
			url: tve_ult_page_data.ajaxurl
		};
		TVE.main.overlay();
		data.action = 'tve_ult_fetch_countdown_for_editor';
		data._nonce = tve_ult_page_data.security;
		params.data = data;

		if ( ajax_param ) {
			for ( var k in ajax_param ) {
				params[k] = ajax_param[k];
			}
		}

		return jQuery.ajax( params, data );
	}
}, {
	/**
	 * "Singleton" implementation for modal instance
	 *
	 * @param el
	 */
	get_instance: function ( el ) {
		if ( ! _instance ) {
			_instance = new TVE_Ult_Ext.UltimatumCountdown( {
				el: el
			} );
		}

		return _instance;
	}
} );