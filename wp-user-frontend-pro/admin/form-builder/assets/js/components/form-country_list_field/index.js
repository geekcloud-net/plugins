/**
 * Field template: Country list
 */
Vue.component('form-country_list_field', {
    template: '#tmpl-wpuf-form-country_list_field',

    mixins: [
        wpuf_mixins.form_field_mixin
    ],

    computed: {
        countries: function () {
            var countries   = wpuf_form_builder.countries,
                visibility  = this.field.country_list.country_list_visibility_opt_name,
                hide_list   = this.field.country_list.country_select_hide_list,
                show_list   = this.field.country_list.country_select_show_list;

            if ('hide' === visibility && hide_list && hide_list.length) {
                countries = countries.filter(function (country) {
                    return (_.indexOf(hide_list, country.code) < 0);
                });

            } else if ('show' === visibility && show_list && show_list.length) {
                countries = countries.filter(function (country) {
                    return (_.indexOf(show_list, country.code) >= 0);
                });
            }

            return countries;
        },

        default_country: function () {
            return this.field.country_list.name;
        }
    }
});
