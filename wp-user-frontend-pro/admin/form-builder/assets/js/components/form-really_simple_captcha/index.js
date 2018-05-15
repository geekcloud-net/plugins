/**
 * Field template: Really Simple Captcha
 */
Vue.component('form-really_simple_captcha', {
    template: '#tmpl-wpuf-form-really_simple_captcha',

    mixins: [
        wpuf_mixins.form_field_mixin
    ],

    computed: {
        is_rs_captcha_active: function () {
            return wpuf_form_builder.is_rs_captcha_active;
        },

        no_plugin_msg: function () {
            return wpuf_form_builder.field_settings.really_simple_captcha.validator.msg;
        }
    }
});
