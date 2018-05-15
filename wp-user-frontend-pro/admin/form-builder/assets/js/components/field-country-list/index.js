Vue.component('field-country-list', {
    template: '#tmpl-wpuf-field-country-list',

    mixins: [
        wpuf_mixins.option_field_mixin,
    ],

    data: function () {
        return {
            default_country: this.editing_form_field.country_list.name
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
            return this.editing_form_field.country_list.country_list_visibility_opt_name;
        },

        country_in_hide_list: function () {
            return this.editing_form_field.country_list.country_select_hide_list;
        },

        country_in_show_list: function () {
            return this.editing_form_field.country_list.country_select_show_list;
        },

    },

    mounted: function () {
        this.bind_selectize();
    },

    methods: {
        bind_selectize: function () {
            var self = this;

            $(this.$el).find('.default-country').selectize({
                plugins: ['remove_button'],

            }).on('change', function () {
                var value = $(this).val();

                self.default_country = value;
                self.update_country_list('name', value);
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

                self.update_country_list(list, value);

            });
        },

        update_country_list: function (prop, value) {
            var country_list = $.extend(true, {}, this.editing_form_field.country_list);

            country_list[prop] = value;

            this.update_value('country_list', country_list);
        },

        set_visibility: function(visibility) {
            this.update_country_list('country_list_visibility_opt_name', visibility);
        },
    }
});
