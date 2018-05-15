(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
'use strict';

jQuery(document).ready(function ($) {
    $('#use_multiple_locations').click(function () {
        if ($(this).is(':checked')) {
            $('#use_multiple_locations').attr('disabled', true);
            $('#single-location-settings').slideUp(function () {
                $('#multiple-locations-settings').slideDown();
                $('#sl-settings').slideDown();
                $('#opening-hours-hours').slideUp(function () {
                    $('#use_multiple_locations').removeAttr('disabled');
                });
            });
        } else {
            $('#use_multiple_locations').attr('disabled', true);
            $('#multiple-locations-settings').slideUp(function () {
                $('#single-location-settings').slideDown();
                if (!$('#hide_opening_hours').is(':checked')) {
                    $('#opening-hours-hours').slideDown();
                }
                $('#sl-settings').slideUp();
                $('#use_multiple_locations').removeAttr('disabled');
            });
        }
    });

    $('#hide_opening_hours').click(function () {
        if ($(this).is(':checked')) {
            $('#opening-hours-hours, #opening-hours-settings').slideUp();
        } else {
            $('#opening-hours-settings').slideDown();
            if (!$('#use_multiple_locations').is(':checked')) {
                $('#opening-hours-hours').slideDown();
            }
        }
    });
    $('#multiple_opening_hours, #wpseo_multiple_opening_hours').click(function () {
        if ($(this).is(':checked')) {
            $('.opening-hours .opening-hours-second').slideDown();
        } else {
            $('.opening-hours .opening-hours-second').slideUp();
        }
    });
    $('#opening_hours_24h').click(function () {
        $('#opening-hours-container select').each(function () {
            $(this).find('option').each(function () {
                if ($('#opening_hours_24h').is(':checked')) {
                    // Use 24 hour
                    if ($(this).val() != 'closed') {
                        $(this).text($(this).val());
                    }
                } else {
                    // Use 12 hour
                    if ($(this).val() != 'closed') {
                        // Split the string between hours and minutes
                        var time = $(this).val().split(':');

                        // use parseInt to remove leading zeroes.
                        var hour = parseInt(time[0]);
                        var minutes = time[1];
                        var suffix = 'AM';

                        // if the hours number is greater than 12, subtract 12.
                        if (hour >= 12) {
                            if (hour > 12) {
                                hour = hour - 12;
                            }
                            suffix = 'PM';
                        }
                        if (hour == 0) {
                            hour = 12;
                        }

                        $(this).text(hour + ':' + minutes + ' ' + suffix);
                    }
                }
            });
        });
    });
    $('.widget-content').on('click', '#wpseo-checkbox-multiple-locations-wrapper input[type=checkbox]', function () {
        wpseo_show_all_locations_selectbox($(this));
    });

    // Show locations metabox before WP SEO metabox
    if ($('#wpseo_locations').length > 0 && $('#wpseo_meta').length > 0) {
        $('#wpseo_locations').insertBefore($('#wpseo_meta'));
    }

    $('.openinghours_from').change(function () {
        var to_id = $(this).attr('id').replace('_from', '_to_wrapper');
        var second_id = $(this).attr('id').replace('_from', '_second');

        if ($(this).val() == 'closed') {
            $('#' + to_id).css('display', 'none');
            $('#' + second_id).css('display', 'none');
        } else {
            $('#' + to_id).css('display', 'inline');
            $('#' + second_id).css('display', 'block');
        }
    }).change();
    $('.openinghours_from_second').change(function () {
        var to_id = $(this).attr('id').replace('_from', '_to_wrapper');

        if ($(this).val() == 'closed') {
            $('#' + to_id).css('display', 'none');
        } else {
            $('#' + to_id).css('display', 'inline');
        }
    }).change();
    $('.openinghours_to').change(function () {
        var from_id = $(this).attr('id').replace('_to', '_from');
        var to_id = $(this).attr('id').replace('_to', '_to_wrapper');
        if ($(this).val() == 'closed') {
            $('#' + to_id).css('display', 'none');
            $('#' + from_id).val('closed');
        }
    });
    $('.openinghours_to_second').change(function () {
        var from_id = $(this).attr('id').replace('_to', '_from');
        var to_id = $(this).attr('id').replace('_to', '_to_wrapper');
        if ($(this).val() == 'closed') {
            $('#' + to_id).css('display', 'none');
            $('#' + from_id).val('closed');
        }
    });

    if ($('.set_custom_images').length > 0) {
        if (typeof wp !== 'undefined' && wp.media && wp.media.editor) {
            $('.wrap').on('click', '.set_custom_images', function (e) {
                e.preventDefault();
                var button = $(this);
                var id = button.attr('data-id');
                wp.media.editor.send.attachment = function (props, attachment) {
                    if (attachment.hasOwnProperty('sizes')) {
                        var url = attachment.sizes[props.size].url;
                    } else {
                        var url = attachment.url;
                    }

                    $('#' + id + '_image_container').attr('src', url);
                    $('.wpseo-local-' + id + '-wrapper .wpseo-local-hide-button').show();
                    $('#hidden_' + id).attr('value', attachment.id);
                };
                wp.media.editor.open(button);
                return false;
            });
        }
    }

    $('.remove_custom_image').on('click', function (e) {
        e.preventDefault();

        var id = $(this).attr('data-id');
        $('#' + id).attr('src', '').hide();
        $('#hidden_' + id).attr('value', '');
        $('.wpseo-local-' + id + '-wrapper .wpseo-local-hide-button').hide();
    });

    // Copy location data
    $('#wpseo_copy_from_location').change(function () {
        var location_id = $(this).val();

        if (location_id == '') return;

        $.post(wpseo_local_data.ajaxurl, {
            location_id: location_id,
            security: wpseo_local_data.sec_nonce,
            action: 'wpseo_copy_location'
        }, function (result) {
            if (result.charAt(result.length - 1) == 0) {
                result = result.slice(0, -1);
            } else if (result.substring(result.length - 2) == "-1") {
                result = result.slice(0, -2);
            }

            var data = $.parseJSON(result);
            if (data.success == 'true' || data.success == true) {

                for (var i in data.location) {
                    var value = data.location[i];

                    if (value != null && value != '' && typeof value != 'undefined') {
                        if (i == 'is_postal_address' || i == 'multiple_opening_hours') {
                            if (value == '1') {
                                $('#wpseo_' + i).attr('checked', 'checked');
                                $('.opening-hours .opening-hour-second').slideDown();
                            }
                        } else if (i.indexOf('opening_hours') > -1) {
                            $('#' + i).val(value);
                        } else {
                            $('#wpseo_' + i).val(value);
                        }
                    }
                }
            }
        });
    });
});

window.wpseo_show_all_locations_selectbox = function (obj) {
    $ = jQuery;

    $obj = $(obj);
    var parent = $obj.parents('.widget-inside');
    var $locationsWrapper = $('#wpseo-locations-wrapper', parent);

    if ($obj.is(':checked')) {
        $locationsWrapper.slideUp();
    } else {
        $locationsWrapper.slideDown();
    }
};

},{}]},{},[1])
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJqcy9zcmMvd3Atc2VvLWxvY2FsLWdsb2JhbC5qcyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7O0FDQUEsT0FBTyxRQUFQLEVBQWlCLEtBQWpCLENBQXVCLFVBQVUsQ0FBVixFQUFhO0FBQ2hDLE1BQUUseUJBQUYsRUFBNkIsS0FBN0IsQ0FBbUMsWUFBWTtBQUMzQyxZQUFJLEVBQUUsSUFBRixFQUFRLEVBQVIsQ0FBVyxVQUFYLENBQUosRUFBNEI7QUFDeEIsY0FBRSx5QkFBRixFQUE2QixJQUE3QixDQUFrQyxVQUFsQyxFQUE4QyxJQUE5QztBQUNBLGNBQUUsMkJBQUYsRUFBK0IsT0FBL0IsQ0FBdUMsWUFBWTtBQUMvQyxrQkFBRSw4QkFBRixFQUFrQyxTQUFsQztBQUNBLGtCQUFFLGNBQUYsRUFBa0IsU0FBbEI7QUFDQSxrQkFBRSxzQkFBRixFQUEwQixPQUExQixDQUFrQyxZQUFZO0FBQzFDLHNCQUFFLHlCQUFGLEVBQTZCLFVBQTdCLENBQXdDLFVBQXhDO0FBQ0gsaUJBRkQ7QUFHSCxhQU5EO0FBT0gsU0FURCxNQVVLO0FBQ0QsY0FBRSx5QkFBRixFQUE2QixJQUE3QixDQUFrQyxVQUFsQyxFQUE4QyxJQUE5QztBQUNBLGNBQUUsOEJBQUYsRUFBa0MsT0FBbEMsQ0FBMEMsWUFBWTtBQUNsRCxrQkFBRSwyQkFBRixFQUErQixTQUEvQjtBQUNBLG9CQUFJLENBQUUsRUFBRSxxQkFBRixFQUF5QixFQUF6QixDQUE0QixVQUE1QixDQUFOLEVBQWdEO0FBQzVDLHNCQUFFLHNCQUFGLEVBQTBCLFNBQTFCO0FBQ0g7QUFDRCxrQkFBRSxjQUFGLEVBQWtCLE9BQWxCO0FBQ0Esa0JBQUUseUJBQUYsRUFBNkIsVUFBN0IsQ0FBd0MsVUFBeEM7QUFDSCxhQVBEO0FBU0g7QUFDSixLQXZCRDs7QUF5QkEsTUFBRSxxQkFBRixFQUF5QixLQUF6QixDQUErQixZQUFZO0FBQ3ZDLFlBQUks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QUo7O0FBRUEsV0FBTyxFQUFFLEdBQUYsQ0FBUDtBQUNBLFFBQUksU0FBUyxLQUFLLE9BQUwsQ0FBYSxnQkFBYixDQUFiO0FBQ0EsUUFBSSxvQkFBb0IsRUFBRSwwQkFBRixFQUE4QixNQUE5QixDQUF4Qjs7QUFFQSxRQUFJLEtBQUssRUFBTCxDQUFRLFVBQVIsQ0FBSixFQUF5QjtBQUNyQiwwQkFBa0IsT0FBbEI7QUFDSCxLQUZELE1BR0s7QUFDRCwwQkFBa0IsU0FBbEI7QUFDSDtBQUNKLENBYkQiLCJmaWxlIjoiZ2VuZXJhdGVkLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXNDb250ZW50IjpbIihmdW5jdGlvbiBlKHQsbixyKXtmdW5jdGlvbiBzKG8sdSl7aWYoIW5bb10pe2lmKCF0W29dKXt2YXIgYT10eXBlb2YgcmVxdWlyZT09XCJmdW5jdGlvblwiJiZyZXF1aXJlO2lmKCF1JiZhKXJldHVybiBhKG8sITApO2lmKGkpcmV0dXJuIGkobywhMCk7dmFyIGY9bmV3IEVycm9yKFwiQ2Fubm90IGZpbmQgbW9kdWxlICdcIitvK1wiJ1wiKTt0aHJvdyBmLmNvZGU9XCJNT0RVTEVfTk9UX0ZPVU5EXCIsZn12YXIgbD1uW29dPXtleHBvcnRzOnt9fTt0W29dWzBdLmNhbGwobC5leHBvcnRzLGZ1bmN0aW9uKGUpe3ZhciBuPXRbb11bMV1bZV07cmV0dXJuIHMobj9uOmUpfSxsLGwuZXhwb3J0cyxlLHQsbixyKX1yZXR1cm4gbltvXS5leHBvcnRzfXZhciBpPXR5cGVvZiByZXF1aXJlPT1cImZ1bmN0aW9uXCImJnJlcXVpcmU7Zm9yKHZhciBvPTA7bzxyLmxlbmd0aDtvKyspcyhyW29dKTtyZXR1cm4gc30pIiwialF1ZXJ5KGRvY3VtZW50KS5yZWFkeShmdW5jdGlvbiAoJCkge1xuICAgICQoJyN1c2VfbXVsdGlwbGVfbG9jYXRpb25zJykuY2xpY2soZnVuY3Rpb24gKCkge1xuICAgICAgICBpZiAoJCh0aGlzKS5pcygnOmNoZWNrZWQnKSkge1xuICAgICAgICAgICAgJCgnI3VzZV9tdWx0aXBsZV9sb2NhdGlvbnMnKS5hdHRyKCdkaXNhYmxlZCcsIHRydWUpO1xuICAgICAgICAgICAgJCgnI3NpbmdsZS1sb2NhdGlvbi1zZXR0aW5ncycpLnNsaWRlVXAoZnVuY3Rpb24gKCkge1xuICAgICAgICAgICAgICAgICQoJyNtdWx0aXBsZS1sb2NhdGlvbnMtc2V0dGluZ3MnKS5zbGlkZURvd24oKTtcbiAgICAgICAgICAgICAgICAkKCcjc2wtc2V0dGluZ3MnKS5zbGlkZURvd24oKTtcbiAgICAgICAgICAgICAgICAkKCcjb3BlbmluZy1ob3Vycy1ob3VycycpLnNsaWRlVXAoZnVuY3Rpb24gKCkge1xuICAgICAgICAgICAgICAgICAgICAkKCcjdXNlX211bHRpcGxlX2xvY2F0aW9ucycpLnJlbW92ZUF0dHIoJ2Rpc2FibGVkJyk7XG4gICAgICAgICAgICAgICAgfSk7XG4gICAgICAgICAgICB9KTtcbiAgICAgICAgfVxuICAgICAgICBlbHNlIHtcbiAgICAgICAgICAgICQoJyN1c2VfbXVsdGlwbGVfbG9jYXRpb25zJykuYXR0cignZGlzYWJsZWQnLCB0cnVlKTtcbiAgICAgICAgICAgICQoJyNtdWx0aXBsZS1sb2NhdGlvbnMtc2V0dGluZ3MnKS5zbGlkZVVwKGZ1bmN0aW9uICgpIHtcbiAgICAgICAgICAgICAgICAkKCcjc2luZ2xlLWxvY2F0aW9uLXNldHRpbmdzJykuc2xpZGVEb3duKCk7XG4gICAgICAgICAgICAgICAgaWYoICEgJCgnI2hpZGVfb3BlbmluZ19ob3VycycpLmlzKCc6Y2hlY2tlZCcpICkge1xuICAgICAgICAgICAgICAgICAgICAkKCcjb3BlbmluZy1ob3Vycy1ob3VycycpLnNsaWRlRG93bigpO1xuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICAkKCcjc2wtc2V0dGluZ3MnKS5zbGlkZVVwKCk7XG4gICAgICAgICAgICAgICAgJCgnI3VzZV9tdWx0aXBsZV9sb2NhdGlvbnMnKS5yZW1vdmVBdHRyKCdkaXNhYmxlZCcpO1xuICAgICAgICAgICAgfSk7XG5cbiAgICAgICAgfVxuICAgIH0pO1xuXG4gICAgJCgnI2hpZGVfb3BlbmluZ19ob3VycycpLmNsaWNrKGZ1bmN0aW9uICgpIHtcbiAgICAgICAgaWYgKCQodGhpcykuaXMoJzpjaGVja2VkJykpIHtcbiAgICAgICAgICAgICQoJyNvcGVuaW5nLWhvdXJzLWhvdXJzLCAjb3BlbmluZy1ob3Vycy1zZXR0aW5ncycpLnNsaWRlVXAoKTtcbiAgICAgICAgfVxuICAgICAgICBlbHNlIHtcbiAgICAgICAgICAgICQoJyNvcGVuaW5nLWhvdXJzLXNldHRpbmdzJykuc2xpZGVEb3duKCk7XG4gICAgICAgICAgICBpZiAoISAkKCcjdXNlX211bHRpcGxlX2xvY2F0aW9ucycpLmlzKCc6Y2hlY2tlZCcpKSB7XG4gICAgICAgICAgICAgICAgJCgnI29wZW5pbmctaG91cnMtaG91cnMnKS5zbGlkZURvd24oKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfVxuICAgIH0pO1xuICAgICQoJyNtdWx0aXBsZV9vcGVuaW5nX2hvdXJzLCAjd3BzZW9fbXVsdGlwbGVfb3BlbmluZ19ob3VycycpLmNsaWNrKGZ1bmN0aW9uICgpIHtcbiAgICAgICAgaWYgKCQodGhpcykuaXMoJzpjaGVja2VkJykpIHtcbiAgICAgICAgICAgICQoJy5vcGVuaW5nLWhvdXJzIC5vcGVuaW5nLWhvdXJzLXNlY29uZCcpLnNsaWRlRG93bigpO1xuICAgICAgICB9XG4gICAgICAgIGVsc2Uge1xuICAgICAgICAgICAgJCgnLm9wZW5pbmctaG91cnMgLm9wZW5pbmctaG91cnMtc2Vjb25kJykuc2xpZGVVcCgpO1xuICAgICAgICB9XG4gICAgfSk7XG4gICAgJCgnI29wZW5pbmdfaG91cnNfMjRoJykuY2xpY2soZnVuY3Rpb24gKCkge1xuICAgICAgICAkKCcjb3BlbmluZy1ob3Vycy1jb250YWluZXIgc2VsZWN0JykuZWFjaChmdW5jdGlvbiAoKSB7XG4gICAgICAgICAgICAkKHRoaXMpLmZpbmQoJ29wdGlvbicpLmVhY2goZnVuY3Rpb24gKCkge1xuICAgICAgICAgICAgICAgIGlmICgkKCcjb3BlbmluZ19ob3Vyc18yNGgnKS5pcygnOmNoZWNrZWQnKSkge1xuICAgICAgICAgICAgICAgICAgICAvLyBVc2UgMjQgaG91clxuICAgICAgICAgICAgICAgICAgICBpZiAoJCh0aGlzKS52YWwoKSAhPSAnY2xvc2VkJykge1xuICAgICAgICAgICAgICAgICAgICAgICAgJCh0aGlzKS50ZXh0KCQodGhpcykudmFsKCkpO1xuICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgICAgICAgICAgLy8gVXNlIDEyIGhvdXJcbiAgICAgICAgICAgICAgICAgICAgaWYgKCQodGhpcykudmFsKCkgIT0gJ2Nsb3NlZCcpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgIC8vIFNwbGl0IHRoZSBzdHJpbmcgYmV0d2VlbiBob3VycyBhbmQgbWludXRlc1xuICAgICAgICAgICAgICAgICAgICAgICAgdmFyIHRpbWUgPSAkKHRoaXMpLnZhbCgpLnNwbGl0KCc6Jyk7XG5cbiAgICAgICAgICAgICAgICAgICAgICAgIC8vIHVzZSBwYXJzZUludCB0byByZW1vdmUgbGVhZGluZyB6ZXJvZXMuXG4gICAgICAgICAgICAgICAgICAgICAgICB2YXIgaG91ciA9IHBhcnNlSW50KHRpbWVbMF0pO1xuICAgICAgICAgICAgICAgICAgICAgICAgdmFyIG1pbnV0ZXMgPSB0aW1lWzFdO1xuICAgICAgICAgICAgICAgICAgICAgICAgdmFyIHN1ZmZpeCA9ICdBTSc7XG5cbiAgICAgICAgICAgICAgICAgICAgICAgIC8vIGlmIHRoZSBob3VycyBudW1iZXIgaXMgZ3JlYXRlciB0aGFuIDEyLCBzdWJ0cmFjdCAxMi5cbiAgICAgICAgICAgICAgICAgICAgICAgIGlmIChob3VyID49IDEyKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgaWYgKGhvdXIgPiAxMikge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBob3VyID0gaG91ciAtIDEyO1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBzdWZmaXggPSAnUE0nO1xuICAgICAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgICAgICAgICAgaWYgKGhvdXIgPT0gMCkge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGhvdXIgPSAxMjtcbiAgICAgICAgICAgICAgICAgICAgICAgIH1cblxuICAgICAgICAgICAgICAgICAgICAgICAgJCh0aGlzKS50ZXh0KGhvdXIgKyAnOicgKyBtaW51dGVzICsgJyAnICsgc3VmZml4KTtcbiAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgIH0pO1xuICAgICAgICB9KVxuICAgIH0pO1xuICAgICQoJy53aWRnZXQtY29udGVudCcpLm9uKCdjbGljaycsICcjd3BzZW8tY2hlY2tib3gtbXVsdGlwbGUtbG9jYXRpb25zLXdyYXBwZXIgaW5wdXRbdHlwZT1jaGVja2JveF0nLCBmdW5jdGlvbiAoKSB7XG4gICAgICAgIHdwc2VvX3Nob3dfYWxsX2xvY2F0aW9uc19zZWxlY3Rib3goJCh0aGlzKSk7XG4gICAgfSk7XG5cbiAgICAvLyBTaG93IGxvY2F0aW9ucyBtZXRhYm94IGJlZm9yZSBXUCBTRU8gbWV0YWJveFxuICAgIGlmICgkKCcjd3BzZW9fbG9jYXRpb25zJykubGVuZ3RoID4gMCAmJiAkKCcjd3BzZW9fbWV0YScpLmxlbmd0aCA+IDApIHtcbiAgICAgICAgJCgnI3dwc2VvX2xvY2F0aW9ucycpLmluc2VydEJlZm9yZSgkKCcjd3BzZW9fbWV0YScpKTtcbiAgICB9XG5cbiAgICAkKCcub3BlbmluZ2hvdXJzX2Zyb20nKS5jaGFuZ2UoZnVuY3Rpb24gKCkge1xuICAgICAgICB2YXIgdG9faWQgPSAkKHRoaXMpLmF0dHIoJ2lkJykucmVwbGFjZSgnX2Zyb20nLCAnX3RvX3dyYXBwZXInKTtcbiAgICAgICAgdmFyIHNlY29uZF9pZCA9ICQodGhpcykuYXR0cignaWQnKS5yZXBsYWNlKCdfZnJvbScsICdfc2Vjb25kJyk7XG5cbiAgICAgICAgaWYgKCQodGhpcykudmFsKCkgPT0gJ2Nsb3NlZCcpIHtcbiAgICAgICAgICAgICQoJyMnICsgdG9faWQpLmNzcygnZGlzcGxheScsICdub25lJyk7XG4gICAgICAgICAgICAkKCcjJyArIHNlY29uZF9pZCkuY3NzKCdkaXNwbGF5JywgJ25vbmUnKTtcbiAgICAgICAgfVxuICAgICAgICBlbHNlIHtcbiAgICAgICAgICAgICQoJyMnICsgdG9faWQpLmNzcygnZGlzcGxheScsICdpbmxpbmUnKTtcbiAgICAgICAgICAgICQoJyMnICsgc2Vjb25kX2lkKS5jc3MoJ2Rpc3BsYXknLCAnYmxvY2snKTtcbiAgICAgICAgfVxuICAgIH0pLmNoYW5nZSgpO1xuICAgICQoJy5vcGVuaW5naG91cnNfZnJvbV9zZWNvbmQnKS5jaGFuZ2UoZnVuY3Rpb24gKCkge1xuICAgICAgICB2YXIgdG9faWQgPSAkKHRoaXMpLmF0dHIoJ2lkJykucmVwbGFjZSgnX2Zyb20nLCAnX3RvX3dyYXBwZXInKTtcblxuICAgICAgICBpZiAoJCh0aGlzKS52YWwoKSA9PSAnY2xvc2VkJykge1xuICAgICAgICAgICAgJCgnIycgKyB0b19pZCkuY3NzKCdkaXNwbGF5JywgJ25vbmUnKTtcbiAgICAgICAgfVxuICAgICAgICBlbHNlIHtcbiAgICAgICAgICAgICQoJyMnICsgdG9faWQpLmNzcygnZGlzcGxheScsICdpbmxpbmUnKTtcbiAgICAgICAgfVxuICAgIH0pLmNoYW5nZSgpO1xuICAgICQoJy5vcGVuaW5naG91cnNfdG8nKS5jaGFuZ2UoZnVuY3Rpb24gKCkge1xuICAgICAgICB2YXIgZnJvbV9pZCA9ICQodGhpcykuYXR0cignaWQnKS5yZXBsYWNlKCdfdG8nLCAnX2Zyb20nKTtcbiAgICAgICAgdmFyIHRvX2lkID0gJCh0aGlzKS5hdHRyKCdpZCcpLnJlcGxhY2UoJ190bycsICdfdG9fd3JhcHBlcicpO1xuICAgICAgICBpZiAoJCh0aGlzKS52YWwoKSA9PSAnY2xvc2VkJykge1xuICAgICAgICAgICAgJCgnIycgKyB0b19pZCkuY3NzKCdkaXNwbGF5JywgJ25vbmUnKTtcbiAgICAgICAgICAgICQoJyMnICsgZnJvbV9pZCkudmFsKCdjbG9zZWQnKTtcbiAgICAgICAgfVxuICAgIH0pO1xuICAgICQoJy5vcGVuaW5naG91cnNfdG9fc2Vjb25kJykuY2hhbmdlKGZ1bmN0aW9uICgpIHtcbiAgICAgICAgdmFyIGZyb21faWQgPSAkKHRoaXMpLmF0dHIoJ2lkJykucmVwbGFjZSgnX3RvJywgJ19mcm9tJyk7XG4gICAgICAgIHZhciB0b19pZCA9ICQodGhpcykuYXR0cignaWQnKS5yZXBsYWNlKCdfdG8nLCAnX3RvX3dyYXBwZXInKTtcbiAgICAgICAgaWYgKCQodGhpcykudmFsKCkgPT0gJ2Nsb3NlZCcpIHtcbiAgICAgICAgICAgICQoJyMnICsgdG9faWQpLmNzcygnZGlzcGxheScsICdub25lJyk7XG4gICAgICAgICAgICAkKCcjJyArIGZyb21faWQpLnZhbCgnY2xvc2VkJyk7XG4gICAgICAgIH1cbiAgICB9KTtcblxuICAgIGlmICgkKCcuc2V0X2N1c3RvbV9pbWFnZXMnKS5sZW5ndGggPiAwKSB7XG4gICAgICAgIGlmICh0eXBlb2Ygd3AgIT09ICd1bmRlZmluZWQnICYmIHdwLm1lZGlhICYmIHdwLm1lZGlhLmVkaXRvcikge1xuICAgICAgICAgICAgJCgnLndyYXAnKS5vbignY2xpY2snLCAnLnNldF9jdXN0b21faW1hZ2VzJywgZnVuY3Rpb24gKGUpIHtcbiAgICAgICAgICAgICAgICBlLnByZXZlbnREZWZhdWx0KCk7XG4gICAgICAgICAgICAgICAgdmFyIGJ1dHRvbiA9ICQodGhpcyk7XG4gICAgICAgICAgICAgICAgdmFyIGlkID0gYnV0dG9uLmF0dHIoJ2RhdGEtaWQnKTtcbiAgICAgICAgICAgICAgICB3cC5tZWRpYS5lZGl0b3Iuc2VuZC5hdHRhY2htZW50ID0gZnVuY3Rpb24gKHByb3BzLCBhdHRhY2htZW50KSB7XG4gICAgICAgICAgICAgICAgICAgIGlmKCBhdHRhY2htZW50Lmhhc093blByb3BlcnR5KCdzaXplcycpICkge1xuICAgICAgICAgICAgICAgICAgICAgICAgdmFyIHVybCA9IGF0dGFjaG1lbnQuc2l6ZXNbcHJvcHMuc2l6ZV0udXJsO1xuICAgICAgICAgICAgICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgICAgICAgICAgICAgdmFyIHVybCA9IGF0dGFjaG1lbnQudXJsO1xuICAgICAgICAgICAgICAgICAgICB9XG5cbiAgICAgICAgICAgICAgICAgICAgJCgnIycgKyBpZCArICdfaW1hZ2VfY29udGFpbmVyJykuYXR0cignc3JjJywgdXJsICk7XG4gICAgICAgICAgICAgICAgICAgICQoJy53cHNlby1sb2NhbC0nICsgaWQgKyAnLXdyYXBwZXIgLndwc2VvLWxvY2FsLWhpZGUtYnV0dG9uJykuc2hvdygpO1xuICAgICAgICAgICAgICAgICAgICAkKCcjaGlkZGVuXycgKyBpZCkuYXR0cigndmFsdWUnLCBhdHRhY2htZW50LmlkKTtcbiAgICAgICAgICAgICAgICB9O1xuICAgICAgICAgICAgICAgIHdwLm1lZGlhLmVkaXRvci5vcGVuKGJ1dHRvbik7XG4gICAgICAgICAgICAgICAgcmV0dXJuIGZhbHNlO1xuICAgICAgICAgICAgfSk7XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICAkKCcucmVtb3ZlX2N1c3RvbV9pbWFnZScpLm9uKCdjbGljaycsIGZ1bmN0aW9uIChlKSB7XG4gICAgICAgIGUucHJldmVudERlZmF1bHQoKTtcblxuICAgICAgICB2YXIgaWQgPSAkKHRoaXMpLmF0dHIoJ2RhdGEtaWQnKTtcbiAgICAgICAgJCgnIycgKyBpZCkuYXR0cignc3JjJywgJycpLmhpZGUoKTtcbiAgICAgICAgJCgnI2hpZGRlbl8nICsgaWQpLmF0dHIoJ3ZhbHVlJywgJycpO1xuICAgICAgICAkKCcud3BzZW8tbG9jYWwtJyArIGlkICsgJy13cmFwcGVyIC53cHNlby1sb2NhbC1oaWRlLWJ1dHRvbicpLmhpZGUoKTtcbiAgICB9KTtcblxuICAgIC8vIENvcHkgbG9jYXRpb24gZGF0YVxuICAgICQoJyN3cHNlb19jb3B5X2Zyb21fbG9jYXRpb24nKS5jaGFuZ2UoZnVuY3Rpb24gKCkge1xuICAgICAgICB2YXIgbG9jYXRpb25faWQgPSAkKHRoaXMpLnZhbCgpO1xuXG4gICAgICAgIGlmIChsb2NhdGlvbl9pZCA9PSAnJylcbiAgICAgICAgICAgIHJldHVybjtcblxuICAgICAgICAkLnBvc3Qod3BzZW9fbG9jYWxfZGF0YS5hamF4dXJsLCB7XG4gICAgICAgICAgICBsb2NhdGlvbl9pZDogbG9jYXRpb25faWQsXG4gICAgICAgICAgICBzZWN1cml0eTogd3BzZW9fbG9jYWxfZGF0YS5zZWNfbm9uY2UsXG4gICAgICAgICAgICBhY3Rpb246ICd3cHNlb19jb3B5X2xvY2F0aW9uJ1xuICAgICAgICB9LCBmdW5jdGlvbiAocmVzdWx0KSB7XG4gICAgICAgICAgICBpZiAocmVzdWx0LmNoYXJBdChyZXN1bHQubGVuZ3RoIC0gMSkgPT0gMCkge1xuICAgICAgICAgICAgICAgIHJlc3VsdCA9IHJlc3VsdC5zbGljZSgwLCAtMSk7XG4gICAgICAgICAgICB9XG4gICAgICAgICAgICBlbHNlIGlmIChyZXN1bHQuc3Vic3RyaW5nKHJlc3VsdC5sZW5ndGggLSAyKSA9PSBcIi0xXCIpIHtcbiAgICAgICAgICAgICAgICByZXN1bHQgPSByZXN1bHQuc2xpY2UoMCwgLTIpO1xuICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICB2YXIgZGF0YSA9ICQucGFyc2VKU09OKHJlc3VsdCk7XG4gICAgICAgICAgICBpZiAoZGF0YS5zdWNjZXNzID09ICd0cnVlJyB8fCBkYXRhLnN1Y2Nlc3MgPT0gdHJ1ZSkge1xuXG4gICAgICAgICAgICAgICAgZm9yICh2YXIgaSBpbiBkYXRhLmxvY2F0aW9uKSB7XG4gICAgICAgICAgICAgICAgICAgIHZhciB2YWx1ZSA9IGRhdGEubG9jYXRpb25baV07XG5cbiAgICAgICAgICAgICAgICAgICAgaWYgKHZhbHVlICE9IG51bGwgJiYgdmFsdWUgIT0gJycgJiYgdHlwZW9mIHZhbHVlICE9ICd1bmRlZmluZWQnKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICBpZiAoaSA9PSAnaXNfcG9zdGFsX2FkZHJlc3MnIHx8IGkgPT0gJ211bHRpcGxlX29wZW5pbmdfaG91cnMnKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgaWYgKHZhbHVlID09ICcxJykge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAkKCcjd3BzZW9fJyArIGkpLmF0dHIoJ2NoZWNrZWQnLCAnY2hlY2tlZCcpO1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAkKCcub3BlbmluZy1ob3VycyAub3BlbmluZy1ob3VyLXNlY29uZCcpLnNsaWRlRG93bigpO1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICAgICAgICAgIGVsc2UgaWYgKGkuaW5kZXhPZignb3BlbmluZ19ob3VycycpID4gLTEpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAkKCcjJyArIGkpLnZhbCh2YWx1ZSk7XG4gICAgICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgICAgICAgICBlbHNlIHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAkKCcjd3BzZW9fJyArIGkpLnZhbCh2YWx1ZSk7XG4gICAgICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICB9XG4gICAgICAgIH0pO1xuICAgIH0pO1xufSk7XG5cbndpbmRvdy53cHNlb19zaG93X2FsbF9sb2NhdGlvbnNfc2VsZWN0Ym94ID0gZnVuY3Rpb24ob2JqKSB7XG4gICAgJCA9IGpRdWVyeTtcblxuICAgICRvYmogPSAkKG9iaik7XG4gICAgdmFyIHBhcmVudCA9ICRvYmoucGFyZW50cygnLndpZGdldC1pbnNpZGUnKTtcbiAgICB2YXIgJGxvY2F0aW9uc1dyYXBwZXIgPSAkKCcjd3BzZW8tbG9jYXRpb25zLXdyYXBwZXInLCBwYXJlbnQpO1xuXG4gICAgaWYgKCRvYmouaXMoJzpjaGVja2VkJykpIHtcbiAgICAgICAgJGxvY2F0aW9uc1dyYXBwZXIuc2xpZGVVcCgpO1xuICAgIH1cbiAgICBlbHNlIHtcbiAgICAgICAgJGxvY2F0aW9uc1dyYXBwZXIuc2xpZGVEb3duKCk7XG4gICAgfVxufVxuIl19
