var wpeae_reload_page_after_ajax = false;
jQuery(function ($) {

	$(document).on("click", ".wpeae-order-info", function () {
		if ( typeof $(this).attr('id') == "undefined" && $(this).attr('href').substr(0,1) == "#" ) var id = $(this).attr('href').substr(1);
        else var id =  $(this).attr('id').split('-')[1];
        
		$.wpeae_show_order(id);
		return false;
	});

	$.wpeae_show_order = function (id) {
		$('<div id="wpeae-dialog' + id + '"></div>').dialog({
			dialogClass: 'wp-dialog',
			modal: true,
			title: "WooImporter Info (ID: " + id + ")",
			open: function () {
				$('#wpeae-dialog' + id).html(wpeae_wc_ol_script.lang.please_wait_data_loads);
				var data = {'action': 'wpeae_order_info', 'id': id};

				$.post(ajaxurl, data, function (response) {
					//console.log('response: ', response);
					var json = jQuery.parseJSON(response);
					//console.log('result: ', json);

					if (json.state === 'error') {

						console.log(json);

					} else {
						//console.log(json);
						$('#wpeae-dialog' + json.data.id).html(json.data.content.join('<br/>'));
					}

				});


			},
			close: function (event, ui) {
				$("#wpeae-dialog" + id).remove();
			},
			buttons: {
				Ok: function () {
					$(this).dialog("close");
				}
			}
		});

		return false;

	};

});

