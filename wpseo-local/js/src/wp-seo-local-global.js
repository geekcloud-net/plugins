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
        }
        else {
            $('#use_multiple_locations').attr('disabled', true);
            $('#multiple-locations-settings').slideUp(function () {
                $('#single-location-settings').slideDown();
                if( ! $('#hide_opening_hours').is(':checked') ) {
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
        }
        else {
            $('#opening-hours-settings').slideDown();
            if (! $('#use_multiple_locations').is(':checked')) {
                $('#opening-hours-hours').slideDown();
            }
        }
    });
    $('#multiple_opening_hours, #wpseo_multiple_opening_hours').click(function () {
        if ($(this).is(':checked')) {
            $('.opening-hours .opening-hours-second').slideDown();
        }
        else {
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
        })
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
        }
        else {
            $('#' + to_id).css('display', 'inline');
            $('#' + second_id).css('display', 'block');
        }
    }).change();
    $('.openinghours_from_second').change(function () {
        var to_id = $(this).attr('id').replace('_from', '_to_wrapper');

        if ($(this).val() == 'closed') {
            $('#' + to_id).css('display', 'none');
        }
        else {
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
                    if( attachment.hasOwnProperty('sizes') ) {
                        var url = attachment.sizes[props.size].url;
                    } else {
                        var url = attachment.url;
                    }

                    $('#' + id + '_image_container').attr('src', url );
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

        if (location_id == '')
            return;

        $.post(wpseo_local_data.ajaxurl, {
            location_id: location_id,
            security: wpseo_local_data.sec_nonce,
            action: 'wpseo_copy_location'
        }, function (result) {
            if (result.charAt(result.length - 1) == 0) {
                result = result.slice(0, -1);
            }
            else if (result.substring(result.length - 2) == "-1") {
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
                        }
                        else if (i.indexOf('opening_hours') > -1) {
                            $('#' + i).val(value);
                        }
                        else {
                            $('#wpseo_' + i).val(value);
                        }
                    }
                }
            }
        });
    });
});

window.wpseo_show_all_locations_selectbox = function(obj) {
    $ = jQuery;

    $obj = $(obj);
    var parent = $obj.parents('.widget-inside');
    var $locationsWrapper = $('#wpseo-locations-wrapper', parent);

    if ($obj.is(':checked')) {
        $locationsWrapper.slideUp();
    }
    else {
        $locationsWrapper.slideDown();
    }
}
