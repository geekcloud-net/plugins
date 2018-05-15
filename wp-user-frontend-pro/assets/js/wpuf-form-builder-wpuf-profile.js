;(function($) {
    'use strict';

    /**
     * Only proceed if current page is a 'Profile Forms' form builder page
     */
    if (!$('#wpuf-form-builder.wpuf-form-builder-profile').length) {
        return;
    }

    window.wpuf_forms_mixin_root = {
        data: function () {
            return {
                validation_error_msg: wpuf_form_builder.i18n.email_needed,
            };
        },

        methods: {
            // wpuf_profile must have 'user_email'
            // field template
            validate_form_before_submit: function () {
                var is_valid = false;

                _.each(this.form_fields, function (form_field) {
                    if (_.indexOf(['user_email'], form_field.template) >= 0) {
                        is_valid = true;
                        return;
                    }
                });

                return is_valid;
            }
        }
    };

    window.wpuf_forms_mixin_builder_stage = {
        data: function () {
            return {
                label_type: 'left',
                post_form_settings: {
                    submit_text: '',
                    draft_post: false
                }
            };
        },

        mounted: function () {
            var self = this;

            $('[name="wpuf_settings[label_position]"]').on('change', function () {
                self.label_type = $(this).val();
            });

            $('[name="wpuf_settings[label_position]"]').trigger('change');
        }
    };

    var SettingsTab = {
        init: function() {

            $('#wpuf-metabox-settings-registration').on('change', 'select[name="wpuf_settings[reg_redirect_to]"]', this.settingsRedirect);
            $('#wpuf-metabox-settings-update').on('change', 'select[name="wpuf_settings[edit_reg_redirect_to]"]', this.settingsRedirect);
            $('#wpuf-metabox-settings-profile').on('change', 'select[name="wpuf_settings[profile_redirect_to]"]', this.settingsRedirect);
            $('#wpuf-metabox-settings-update').on('change', 'select[name="wpuf_settings[edit_profile_redirect_to]"]', this.settingsRedirect);
            $('select[name="wpuf_settings[reg_redirect_to]"]').change();
            $('select[name="wpuf_settings[edit_reg_redirect_to]"]').change();
            $('select[name="wpuf_settings[profile_redirect_to]"]').change();
            $('select[name="wpuf_settings[edit_profile_redirect_to]"]').change();
        },


        settingsRedirect: function(e) {
            e.preventDefault();

            var $self = $(this),
                $table = $self.closest('table'),
                value = $self.val();

            switch( value ) {
                case 'post':
                    $table.find('tr.wpuf-page-id, tr.wpuf-url, tr.wpuf-same-page').hide();
                    break;

                case 'page':
                    $table.find('tr.wpuf-page-id').show();
                    $table.find('tr.wpuf-same-page').hide();
                    $table.find('tr.wpuf-url').hide();
                    break;

                case 'url':
                    $table.find('tr.wpuf-page-id').hide();
                    $table.find('tr.wpuf-same-page').hide();
                    $table.find('tr.wpuf-url').show();
                    break;

                case 'same':
                    $table.find('tr.wpuf-page-id').hide();
                    $table.find('tr.wpuf-url').hide();
                    $table.find('tr.wpuf-same-page').show();
                    break;
            }
        },

    };

    // on DOM ready
    $(function() {
        SettingsTab.init();
    });

})(jQuery);
