;(function($) {
    'use strict';

    /**
     * Only proceed if current page is a 'Post Forms' form builder page
     */
    if (!$('#wpuf-form-builder').length) {
        // return;
    }

    window.mixin_builder_stage_pro = {
        computed: {
            action_hook_fields: function () {
                return this.$store.state.form_fields.filter(function (item) {
                    return 'action_hook' === item.input_type;
                });
            }
        }
    };
})(jQuery);
