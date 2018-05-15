jQuery(document).ready(function($) {
	/**
	 * TinyNav
	 */
	if ( $.isFunction($.fn.tinyNav) ) {
		$(window).bind( "resize", qc_create_tinynav_menu );
		qc_create_tinynav_menu();
	}

	/**
	 * Time Ago
	 */
	$('.last-updated').timeago();

	$( document ).on('click', 'a.ticket-meta-toggle', function() {
		if ( $(this).hasClass('detail-show') ) {
			$(this).removeClass('detail-show');
			$(this).parent().find('.ticket-meta').hide(400);
		} else {
			$(this).addClass('detail-show');
			$(this).parent().find('.ticket-meta').show(400);
		}
		return false;
	});

});

/* Removes counts from menu and creates TinyNav menu */
function qc_create_tinynav_menu() {
	if ( jQuery(window).width() <= 800 ) {
		jQuery(".tabber-navigation > ul span").remove();
		jQuery(".tinynav").remove();

		jQuery(".tabber-navigation > ul").tinyNav({
			active: 'current-tab',
			header: QC_General.text_mobile_navigation
		});
	}
}
