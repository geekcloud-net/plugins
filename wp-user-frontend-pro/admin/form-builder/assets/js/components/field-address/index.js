Vue.component('field-address', {
    template: '#tmpl-wpuf-field-address',

    mixins: [
        wpuf_mixins.option_field_mixin
    ],

    data: function () {
        return {
            default_country: this.editing_form_field.address.country_select.value,
            show_details: {
                street_address:  false,
                street_address2: false,
                city_name:       false,
                state:           false,
                zip:             false,
                country_select:  false,
            },
        };
    },

    computed: {
        countries: function () {
            return wpuf_form_builder.countries;
        },

        visibility_buttons: function () {
            return [
                { name: 'all', title: this.i18n.show_all },
                { name: 'hide', title: this.i18n.hide_these },
                { name: 'show', title: this.i18n.only_show_these }
            ];
        },

        active_visibility: function () {
            return this.editing_form_field.address.country_select.country_list_visibility_opt_name;
        },

        country_in_hide_list: function () {
            return this.editing_form_field.address.country_select.country_select_hide_list;
        },

        country_in_show_list: function () {
            return this.editing_form_field.address.country_select.country_select_show_list;
        }
    },

    mounted: function () {
        this.bind_selectize();
    },

    methods: {
        toggle_address_checked: function(field) {
            this.editing_form_field.address[field].checked = this.editing_form_field.address[field].checked ? '' : 'checked';
        },

        toggle_address_required: function(field) {
            this.editing_form_field.address[field].required = this.editing_form_field.address[field].required ? '' : 'checked';
        },

        toggle_show_details: function (field) {
            this.show_details[field] = !this.show_details[field];
        },

        bind_selectize: function () {
            var self = this;

            $(this.$el).find('.default-country').selectize({
                plugins: ['remove_button'],

            }).on('change', function () {
                var value = $(this).val();

                self.default_country = value;
                self.update_country_select('value', value);
            });

            $(this.$el).find('select.country-list-selector').selectize({
                plugins: ['remove_button'],
                placeholder: this.i18n.select_countries

            }).on('change', function (e) {
                var select      = $(this),
                    visibility  = e.target.dataset.visibility,
                    value       = select.val(),
                    list        = '';

                switch(visibility) {
                    case 'hide':
                        list = 'country_select_hide_list';
                        break;

                    case 'show':
                        list = 'country_select_show_list';
                        break;
                }

                if (!value) {
                    value = [];
                }

                self.update_country_select(list, value);

            });
        },

        update_country_select: function (prop, value) {
            var address = $.extend(true, {}, this.editing_form_field.address);

            address.country_select[prop] = value;

            this.update_value('address', address);
        },

        set_visibility: function(visibility) {
            this.update_country_select('country_list_visibility_opt_name', visibility);
        },
    }
});
