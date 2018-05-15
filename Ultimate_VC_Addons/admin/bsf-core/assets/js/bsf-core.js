jQuery( document ).on('click', '.bsf-envato-form-activation', function(event) {
	form 	 	= jQuery( this );
	product_id 	= form.siblings( 'form input[name="product_id"]' ).val();
	url 		= form.siblings( 'form input[name="url"]' ).val();
	redirect 	= form.siblings( 'form input[name="redirect"]' ).val();

	jQuery.ajax({
		url: ajaxurl,
		dataType: 'json',
		data: {
			action: 'bsf_envato_redirect_url',
			product_id: product_id,
			url: url,
			redirect: redirect
		}
	})
	.done(function( response ) {
		window.location = response.data.url;
		return true;
	})
	.fail(function(e) {
		return false;
	});

	return false;
});
