;(function($) {
    'use strict';

    /**
     * Only proceed if current page is a 'Post Forms' form builder page
     */
    if (!$('#wpuf-form-builder').length) {
        // return;
    }

    window.mixin_form_field_pro = {
        methods: {
            has_gmap_api_key: function () {
                return wpuf_form_builder.gmap_api_key ? true : false;
            },

            is_rs_captcha_active: function () {
                return wpuf_form_builder.is_rs_captcha_active ? true : false;
            }
        }
    };
})(jQuery);
