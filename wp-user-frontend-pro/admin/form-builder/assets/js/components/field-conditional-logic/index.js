Vue.component('field-conditional-logic', {
    template: '#tmpl-wpuf-field-conditional-logic',

    mixins: [
        wpuf_mixins.option_field_mixin,
    ],

    data: function () {
        return {
            conditions: []
        };
    },

    computed: {
        wpuf_cond: function () {
            return this.editing_form_field.wpuf_cond;
        },

        hierarchical_taxonomies: function () {
            var hierarchical_taxonomies = [];

            _.each(wpuf_form_builder.wp_post_types, function (taxonomies) {
                _.each(taxonomies, function (tax_props, taxonomy) {
                    if (tax_props.hierarchical) {
                        hierarchical_taxonomies.push(taxonomy);
                    }
                });
            });

            return hierarchical_taxonomies;
        },

        wpuf_cond_supported_fields: function () {
            return wpuf_form_builder.wpuf_cond_supported_fields.concat(this.hierarchical_taxonomies);
        },

        dependencies: function () {
            var self = this;

            return this.$store.state.form_fields.filter(function (form_field) {
                if ('taxonomy' !== form_field.template) {
                    return (_.indexOf(self.wpuf_cond_supported_fields, form_field.template) >= 0) &&
                            form_field.name &&
                            form_field.label &&
                            (self.editing_form_field.name !== form_field.name);
                } else {
                    return (_.indexOf(self.wpuf_cond_supported_fields, form_field.name) >= 0) &&
                            form_field.label &&
                            (self.editing_form_field.name !== form_field.name);
                }
            });
        }
    },

    created: function () {
        var wpuf_cond = $.extend(true, {}, this.editing_form_field.wpuf_cond),
            self = this;

        _.each(wpuf_cond.cond_field, function (name, i) {

            if (name && wpuf_cond.cond_field[i] && wpuf_cond.cond_operator[i]) {

                self.conditions.push({
                    name: name,
                    operator: wpuf_cond.cond_operator[i],
                    option: wpuf_cond.cond_option[i]
                });
            }

        });

        if (!self.conditions.length) {
            self.conditions = [{
                name: '',
                operator: '=',
                option: ''
            }];
        }
    },

    methods: {
        get_cond_options: function (field_name) {
            var options = [];

            if (_.indexOf(this.hierarchical_taxonomies, field_name) < 0) {
                var dep = this.dependencies.filter(function (field) {
                    return field.name === field_name;
                });

                if (dep.length && dep[0].options) {
                    _.each(dep[0].options, function (option_title, option_name) {
                        options.push({opt_name: option_name, opt_title: option_title});
                    });
                }

            } else {
                // NOTE: Two post types cannot have same taxonomy
                // ie: post_type_one and post_type_two cannot have same taxonomy my_taxonomy
                var i;

                for (i in wpuf_form_builder.wp_post_types) {
                    var taxonomies = wpuf_form_builder.wp_post_types[i];

                    if (taxonomies.hasOwnProperty(field_name)) {
                        var tax_field = taxonomies[field_name];

                        if (tax_field.terms && tax_field.terms.length) {
                            var j = 0;

                            for (j = 0; j < tax_field.terms.length; j++) {
                                options.push({opt_name: tax_field.terms[j].term_id, opt_title: tax_field.terms[j].name});
                            }
                        }

                        break;
                    }
                }
            }

            return options;
        },

        on_change_cond_field: function (index) {
            this.conditions[index].option = '';
        },

        add_condition: function () {
            this.conditions.push({
                name: '',
                operator: '=',
                option: ''
            });
        },

        delete_condition: function (index) {
            if (this.conditions.length === 1) {
                this.warn({
                    text: this.i18n.last_choice_warn_msg,
                    showCancelButton: false,
                    confirmButtonColor: "#46b450",
                });

                return;
            }

            this.conditions.splice(index, 1);
        }
    },

    watch: {
        conditions: {
            deep: true,
            handler: function (new_conditions) {
                var new_wpuf_cond = $.extend(true, {}, this.editing_form_field.wpuf_cond);

                if (!this.editing_form_field.wpuf_cond) {
                    new_wpuf_cond.condition_status = 'no';
                    new_wpuf_cond.cond_logic = 'all';
                }

                new_wpuf_cond.cond_field       = [];
                new_wpuf_cond.cond_operator    = [];
                new_wpuf_cond.cond_option      = [];

                _.each(new_conditions, function (cond) {
                    new_wpuf_cond.cond_field.push(cond.name);
                    new_wpuf_cond.cond_operator.push(cond.operator);
                    new_wpuf_cond.cond_option.push(cond.option);
                });

                this.update_value('wpuf_cond', new_wpuf_cond);
            }
        }
    }
});
