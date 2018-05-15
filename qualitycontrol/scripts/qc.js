
jQuery(function($) {

	// When you click a parent menu item that doesn't go anywhere, don't let it jump.
	$('.tabber-navigation > ul > li > a').click(function(ev) {
		if ( '#' == $(this).attr('href') )
			ev.preventDefault();
	});

	// When you click into the description field, expand it for l33t code area.
	$('#comment').focus(function() {
		$(this).animate({
			height: '+200px'
		}, 200);
	});

	// Add auto-suggest
	var inputs = {
		'#ticket-assign input' : '?action=qc-user-search',
		'#ticket-tags input' : '?action=ajax-tag-search&tax=post_tag'
	};

	$.each(inputs, function(selector, url){
		$(selector).suggest( QC_L10N.ajaxurl + url, {
			multiple     : true,
			resultsClass : 'qc-suggest-results',
			selectClass  : 'qc-suggest-over',
			matchClass   : 'qc-suggest-match'
		});
	});

	$ticket_form = $('form#create-ticket');

	if ( $ticket_form.length && $.isFunction($.fn.validate) ) {
		$ticket_form.validate();
	}

});
