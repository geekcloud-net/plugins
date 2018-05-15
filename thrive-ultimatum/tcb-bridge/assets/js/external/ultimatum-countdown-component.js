/**
 * Created by Ovidiu on 7/20/2017.
 */
module.exports = TVE.Views.Base.component.extend( {
	controls_init: function () {
	},
	placeholder_action: function () {
		var campaignShortcodes = TVE_Ult_Ext.UltimatumCountdown.get_instance( TVE.modal.get_element( 'campaign-shotcodes' ) );
		campaignShortcodes.open( {
			top: '20%'
		} );
	},
	/**
	 * Callback for change countdown button inside the Ultimatum Countdown Options Menu
	 */
	change_countdown: function () {
		this.placeholder_action();
	}
} );