/**
 * Field template: shortcode
 */
Vue.component('form-shortcode', {
    template: '#tmpl-wpuf-form-shortcode',

    mixins: [
        wpuf_mixins.form_field_mixin
    ],

    data: function () {
        return {
            raw_html: 'from data'
        };
    }
});
