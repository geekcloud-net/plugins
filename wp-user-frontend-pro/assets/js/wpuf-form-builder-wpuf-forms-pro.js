jQuery(document).ready(function ($) {
    'use strict';

    /**
     * Only proceed if current page is a 'Post Forms' form builder page
     */
    if (!$('#wpuf-form-builder.wpuf-form-builder-post').length) {
        return;
    }

    var changeExpirationFieldVisibility = function(e){
        var checkbox_obj = e.target ? $(e.target) : $(e);
        checkbox_obj.is(':checked') ? $('.wpuf_expiration_field').show() : $('.wpuf_expiration_field').hide();
    };

    var setTimeExpiration = function(e){
        var timeArray = {
            'day' : 30,
            'month' : 12,
            'year': 100
        };

        $('#wpuf-expiration_time_value').html('');
        var timeVal = e.target?$(e.target).val():$(e).val();

        for(var time = 1; time <= timeArray[timeVal]; time++){
            $('#wpuf-expiration_time_value').append('<option value="'+ time +'" >'+ time +'</option>');
        }
    };

    // on page load
    changeExpirationFieldVisibility(':checkbox#wpuf-enable_post_expiration');
    //on change enable expiration check status
    $('#wpuf-enable_post_expiration').on('click', changeExpirationFieldVisibility);
    //on change expiration type drop down
    $('#wpuf-expiration_time_type').on('change', setTimeExpiration);
});
