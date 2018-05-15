Vue.component('field-repeater-columns', {
    template: '#tmpl-wpuf-field-repeater-columns',

    mixins: [
        wpuf_mixins.option_field_mixin
    ],

    mounted: function () {
        var self = this;

        $(this.$el).find('.repeater-columns').sortable({
            items: '.repeater-single-column',
            handle: '.sort-handler',
            update: function (e, ui) {
                var item        = ui.item[0],
                    data        = item.dataset,
                    toIndex     = parseInt($(ui.item).index()),
                    fromIndex   = parseInt(data.index);

                var columns = $.extend(true, [], self.editing_form_field.columns);

                columns.swap(fromIndex, toIndex);

                self.update_value('columns', columns);
            }
        }).disableSelection();
    },

    methods: {
        add_column: function () {
            var count       = this.editing_form_field.columns.length,
                new_column  = this.i18n.column + ' ' + (count + 1);

            this.editing_form_field.columns.push(new_column);
        },

        delete_column: function (index) {
            if (this.editing_form_field.columns.length === 1) {
                this.warn({
                    text: this.i18n.last_column_warn_msg,
                    showCancelButton: false,
                    confirmButtonColor: "#46b450",
                });

                return;
            }

            this.editing_form_field.columns.splice(index, 1);
        }
    },

    watch: {

    }
});
