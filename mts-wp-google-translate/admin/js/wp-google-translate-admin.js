(function ($) {
    'use strict';
    $(document).ready(function ($) {
        $('#google_translate_tabs').tabs({active: wpgt_opts.tabindex, activate: function(event,ui) {
            console.log($('#google_translate_tabs').tabs('option','active'));
            $('#last_tab_field').val($('#google_translate_tabs').tabs('option','active'));
        }});
        $('.mtsnb-colors-select').select2({
            placeholder: "Select predefined color set",
            allowClear: true,
            formatResult: format_colors_dropdown,
            escapeMarkup: function (m) {
                return m;
            },
            minimumResultsForSearch: 10
        }).change(function (event) {
            console.log('select changd');
            var $el = $(this).find(':selected');
            if (!$el.val())
                return;

            //var target = $el.data('target');
            $.each($el.data('colors'), function (index, val) {
                $('#' + index).iris('color', val);
            });
        });

        function format_colors_dropdown(state) {
            var palette = '<div class="single-palette"><table class="color-palette"><tbody><tr>';
            $.each($(state.element).data('colors'), function (index, val) {
                palette += '<td style="background-color: ' + val + '">&nbsp;</td>';
            });
            palette += '</tr></tbody></table></div>';
            return state.text + palette;
        }
                
                
                
                
        $('#button_bg_color').wpColorPicker({
            change: function (event, ui) {
                $("#translate_preview_button").css('background-color', ui.color.toString());
            },
        });

        $('#button_font_color').wpColorPicker({
            change: function (event, ui) {
                $("#translate_preview_button").css('color', ui.color.toString());
            },
        });

        $('#button_border_color').wpColorPicker({
            change: function (event, ui) {
                $("#translate_preview_button").css('border-color', ui.color.toString());
            },
        });

        $('#list_bg_color').wpColorPicker({
            change: function (event, ui) {
                $("#translate_preview_list li a").css('background-color', ui.color.toString());
            },
        });

        $('#list_font_color').wpColorPicker({
            change: function (event, ui) {
                $("#translate_preview_list li a").css('color', ui.color.toString());
            },
        });

        $('#list_border_color').wpColorPicker({
            change: function (event, ui) {
                $("#translate_preview_list li a").css('border-color', ui.color.toString());
            },
        });

        //manage the hover
        $('#button_hover_bg_color').wpColorPicker({
            change: function (event, ui) {
                $("#button_bg_hover").html('#translate_preview_button:hover{background-color: ' + ui.color.toString() + ' !important;}');
            },
        });

        $('#button_hover_font_color').wpColorPicker({
            change: function (event, ui) {
                $("#button_font_hover").html('#translate_preview_button:hover{color: ' + ui.color.toString() + ' !important;}');
            },
        });

        $('#list_hover_bg_color').wpColorPicker({
            change: function (event, ui) {
                $("#list_bg_hover").html('#translate_preview_list li a:hover{background-color: ' + ui.color.toString() + ' !important;}');
            },
        });

        $('#list_hover_font_color').wpColorPicker({
            change: function (event, ui) {
                $("#list_font_hover").html('#translate_preview_list li a:hover{color: ' + ui.color.toString() + ' !important;}');
            },
        });
        
        $('#translate_preview_list a').click(function(event) {
            event.preventDefault();
        });
        $('.fieldtoggle').change(function(event) {
            $($(this).data('rel')).toggle($(this).is(':checked'));
        }).change();
    });
})(jQuery);