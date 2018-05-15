;(function($) {

	$('#enable_mailpoet').change(function(e) {
		e.preventDefault();
		
        if ($(this).is(":checked")) {
             $('.wpuf-redirect-to').removeClass('wpuf-hide');
        }
        else {
             $('.wpuf-redirect-to').addClass('wpuf-hide');
        }
    });


})(jQuery);