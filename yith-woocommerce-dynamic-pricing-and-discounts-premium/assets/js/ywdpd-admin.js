/*
 * @package YITH WooCommerce Dynamic Pricing and Discounts Premium
 * @since   1.0.0
 * @author  YITHEMES
 */

jQuery(document).ready(function ($) {
    "use strict";
    var wrapper = $(document).find('.ywdpd-sections-group'),
        container = wrapper.find('.ywdpd-section'),
        eventType = container.find('.yith-ywdpd-eventype-select'),
        del_msg = ( typeof yith_ywdpd_admin !== 'undefined' ) ? yith_ywdpd_admin.del_msg : false,
        ajax_url = yith_ywdpd_admin.ajaxurl + '?action=ywdpd_admin_action',

        /****
         * Deps function option
         */
        deps_func = function (eventType) {
            eventType.each(function () {
                var t = $(this),
                    field = t.data('field'),
                    selected = t.find('option:selected');

                hide_show_func(t, selected.val(), field);

                t.on('change', function () {
                    var field = t.data('field'),
                        selected = t.find('option:selected');
                    hide_show_func(t, selected.val(), field);
                })
            });
        },

        hide_show_func = function (t, val, field) {
            var opt = t.closest('.ywdpd-select-wrapper').find('tr.deps-' + field);

            opt.each(function () {
                var types = $(this).data('type').split(';');
                if ($.inArray(val, types) !== -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                    if (typeof $(this).data('rel') !== 'undefined') {
                        var item_class = 'deps-' + $(this).data('rel');
                        $(this).parents('.ywdpd-section').find('.' + item_class).hide();
                    }


                }
            });
        };


    /****
     * Add a row pricing rules
     ****/
    $(document).on('click', '.add-row', function () {
        var $t = $(this),
            table = $t.closest('table'),
            current_row = $t.closest('tr'),
            current_index = parseInt(current_row.data('index')),
            clone = current_row.clone(),
            rows = table.find('tr'),
            max_index = 1;

        clone.find('select').removeClass( 'enhanced' );
        clone.find( 'span.select2' ).remove();

        rows.each(function () {
            var index = $(this).data('index');
            if (index > max_index) {
                max_index = index;
            }
        });

        var new_index = max_index + 1;
        clone.attr('data-index', new_index);
        var fields = clone.find("[name*='rules']");

        fields.each(function () {
            var $t = $(this),
                name = $t.attr('name'),
                id = $t.attr('id'),

                new_name = name.replace('[rules][' + current_index + ']', '[rules][' + new_index + ']'),
                new_id = id.replace('[rules][' + current_index + ']', '[rules][' + new_index + ']');

            $t.attr('name', new_name);
            $t.attr('id', new_id);
            $t.val('');

        });

        clone.find('.remove-row').removeClass('hide-remove');
        table.append(clone);
        $(document.body).trigger('wc-enhanced-select-init');
        var eventType = clone.find('.yith-ywdpd-eventype-select');
        deps_func(eventType);
    });

    /****
     * remove a row pricing rules
     ****/
    $(document).on('click', '.remove-row', function () {
        var $t = $(this),
            current_row = $t.closest('tr');
        current_row.remove();
    });

    $( 'table.widefat.wp-list-table.discounts tr' ).append(
        '<td class="column-handle"></td>'
    );

    jQuery('.ywdpd-sections-group').sortable({
            items: '.ywdpd-section-handle',
            cursor: 'move',
            axis: 'y',
            handle: 'form',
            scrollSensitivity: 40,
            forcePlaceholderSize: true,
            helper: 'clone',
            start: function (event, ui) {
                ui.item.css('background-color', '#f6f6f6');
            },
            stop: function (event, ui) {
                ui.item.removeAttr('style');
                var keys = $('.ywdpd-section-handle'), i = 0, array_keys = new Array();
                for (i = 0; i < keys.length; i++) {
                    array_keys[i] = $(keys[i]).data('key');
                }

                if (array_keys.length > 0) {
                    $.post(ajax_url, {
                        ywdpd_action: 'order_section',
						tab: $('.form-table').closest('.yit-admin-panel-content-wrap-full').data('type'),
						order_keys: array_keys
                    }, function (resp) {
                    });
                }

            }
        });

    // init

    deps_func(eventType);

    container.find('.datepicker').each(function () {
        $(this).prop('placeholder', 'YYYY-MM-DD HH:mm')
    }).datetimepicker({
        timeFormat: 'HH:mm',
        defaultDate    : '',
        dateFormat     : 'yy-mm-dd',
        numberOfMonths : 1,
    });

   /* $.fn.chosenDestroy = function () {
        $(this).show().removeClass('chzn-done');
        $(this).next().remove();

        return $(this);
    };*/


    if ($('#_discount_type').length) {
        var std = $('#_discount_type').data('std'),
            href = $('.page-title-action').attr('href');
        $('#_discount_type').attr('value', std);
        $('.page-title-action').attr('href', href + '&ywdpd_discount_type=' + std);
    }

    $('#_schedule_from, #_schedule_to').each(function () {
        $(this).prop('placeholder', 'YYYY-MM-DD HH:mm')
    }).datetimepicker({
        timeFormat: 'HH:mm',
        defaultDate    : '',
        dateFormat     : 'yy-mm-dd',
        numberOfMonths : 1,
    });

    var neweventType = $('#ywdpd_cart_discount').find('.yith-ywdpd-eventype-select');
    deps_func(neweventType);
    // re-init
   /* $('#ywdpd_cart_discount').find('.chosen-select').chosen({
        width: '100%',
        disable_search: true
    });
*/
    $('.wp-list-table.discounts').sortable({
        items: 'tbody tr:not(.inline-edit-row)',
        cursor: 'move',
        handle: '.column-handle',
        axis: 'y',
        forcePlaceholderSize: true,
        helper: 'clone',
        opacity: 0.65,
        start: function (event, ui) {
            ui.item.css('background-color', '#f6f6f6');
        },
        stop: function (event, ui) {
            ui.item.removeAttr('style');
            var roleid     = ui.item.find( '.check-column input' ).val(); // this post id
            var previd = ui.item.prev().find( '.check-column input' ).val();
            var nextid = ui.item.next().find( '.check-column input' ).val();

            $.post(ajax_url, {
                ywdpd_action: 'table_order_section',
                type: $('#ywdpd-discount-list-table').data('type'),
                roleid: roleid,
                previd: previd,
                nextid: nextid
            }, function (resp) {
            });
        }
    });

    $('#ywdpd-discount-list-table').on( 'submit', function(){
       var $t = $( this ),

       bulk = $t.find('#bulk-action-selector-top').val();

       if( bulk == 'delete'){
           var confirm = window.confirm(del_msg);
           if (confirm == true) {
               return true;
           }else{
               return false;
           }
       }

    });

    $('.cart_discount,.cart_discount_type').show();
});
