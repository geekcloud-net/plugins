(function ( $ ) {
	_.templateSettings = {
		evaluate: /<#([\s\S]+?)#>/g,
		interpolate: /<#=([\s\S]+?)#>/g,
		escape: /<#-([\s\S]+?)#>/g
	};

	var ScriptManager = ScriptManager || {};

	$.extend( ScriptManager, {
		sm_router: Backbone.Router.extend( {

			view: null,
			$el: $( '#tvd-sm-container' ),

			routes: {
				'': 'dashboard'
			},

			dashboard: function () {
				if ( this.view ) {
					this.view.remove();
				}
				this.view = new ScriptManager.views.ScriptDashboard( {
					el: this.$el
				} )
			}
		} ),

		utils: require( '../utils' ),
		models: require( './models' ),
		views: require( './views' )
	} );

	ScriptManager.router = new ScriptManager.sm_router();
	Backbone.history.start( {hashchange: true} );

})( jQuery );