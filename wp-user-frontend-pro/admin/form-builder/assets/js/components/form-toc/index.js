/**
 * Field template: Terms & Conditions
 */
Vue.component('form-toc', {
    template: '#tmpl-wpuf-form-toc',

    mixins: [
        wpuf_mixins.form_field_mixin
    ],

    computed: {
        content: function () {
            return this.field.description.replace(/\n/g, '<br>');
        }
    }
});
