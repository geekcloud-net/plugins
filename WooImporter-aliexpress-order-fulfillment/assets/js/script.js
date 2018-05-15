jQuery(function ($) {
	$(document).on("click", ".wpeae_aliexpress_order_fulfillment", function () {
	    if ( typeof $(this).attr('id') == "undefined" && $(this).attr('href').substr(0,1) == "#" ) var id = $(this).attr('href').substr(1);
        else var id =  $(this).attr('id').split('-')[1];
		
		$.wpeae_ali_fulfill_order(id);
		
		return false;
	});
	
	$.wpeae_ali_fulfill_order = function (id) {
		var data = {'action': 'wpeae_get_aliexpress_order_data', 'id': id};

		$.post(ajaxurl, data, function (response) {
		
			var json = jQuery.parseJSON(response);
		

			if (json.state === 'error') {
				
				console.log(json);
				jQuery('.wrap > h1').after('<div class="error notice is-dismissible"><p>'+json.error_message+'</p><button id="wpeae-fulfill-dismiss-admin-message" class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>');
				
				jQuery("#wpeae-fulfill-dismiss-admin-message").click(function(event) {
					event.preventDefault();
					jQuery('.' + 'error').fadeTo(100, 0, function() {
						jQuery('.' + 'error').slideUp(100, function() {
							jQuery('.' + 'error').remove();
						});
					});
				});
		
			} else {
				console.log(json.data);
				
				if (json.action == 'upd_ord_status'){
					/*
					var complete_btn = jQuery('#post-'+json.data.id).find('a.complete');
					if (complete_btn.length > 0) complete_btn[0].click();
					*/
				}
				wpeae_get_order_fulfillment(json.data.content, function(){} );
			
			}

		});	
	}
		
});