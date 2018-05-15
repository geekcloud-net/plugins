(function($) {

	$('.wpbf-screenshot-upload').click(function(e) {
		e.preventDefault();

		var custom_uploader = wp.media({
			title: 'Login Logo',
			button: {
				text: 'Upload Image'
			},
			multiple: false  // Set this to true to allow multiple files to be selected
		})
		.on('select', function() {
			var attachment = custom_uploader.state().get('selection').first().toJSON();
			$('.udb-branding-login-logo').attr('src', attachment.url);
			$('.wpbf-screenshot-url').val(attachment.url);

		})
		.open();
	});

	$('.wpbf-screenshot-remove').click(function(e) {
		e.preventDefault();
		$('.wpbf-screenshot-url').val('');
	});

})( jQuery );