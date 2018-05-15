;(function($){

    var WPUF_Admin = {

        init: function() {
            $('.wpuf-modules').on( 'change', 'input.wpuf-toggle-module', this.toggleModule );
        },

        toggleModule: function(e) {
            var self = $(this);

            if ( self.is(':checked') ) {
                // Enabled
                var mesg = wpuf_module.activating,
                    data = {
                        action: 'wpuf-toggle-module',
                        type: 'activate',
                        module: self.closest( 'li' ).data( 'module' ),
                        nonce: wpuf_module.nonce
                    };
            } else {
                // Disbaled
                var mesg = wpuf_module.deactivating,
                    data = {
                        action: 'wpuf-toggle-module',
                        type: 'deactivate',
                        module: self.closest( 'li' ).data( 'module' ),
                        nonce: wpuf_module.nonce
                    };
            }

            self.closest('.plugin-card').block({
                message: mesg,
                overlayCSS: { background: '#222', opacity: 0.7 },
                css: {
                    fontSize: '19px',
                    color:      '#fff',
                    border:     'none',
                    backgroundColor:'none',
                    cursor:     'wait'
                },
            });

            wp.ajax.send( 'wpuf-toggle-module', {
                data: data,
                success: function(response) {

                },

                error: function(error) {
                    if ( error.error === 'plugin-exists' ) {
                        wp.ajax.send( 'wpuf-toggle-module', {
                            data: data
                        });
                    }
                },

                complete: function(resp) {
                    $('.blockMsg').text(resp.data);
                    setTimeout( function() {
                        self.closest('.plugin-card').unblock();
                    }, 1000)
                }
            });
        }
    };

    $(document).ready(function(){
        WPUF_Admin.init();
    });
})(jQuery);