/* global wp */
/* global wpseonews */
// Taken and adapted from http://www.webmaster-source.com/2013/02/06/using-the-wordpress-3-5-media-uploader-in-your-plugin-or-theme/
jQuery(document).ready(function($) {
	'use strict';
	var WPSEOCustomUploader;
	$('.wpseo_image_upload_button').click(function(e) {
		var wpseo_target_id = $(this).attr('id').replace(/_button$/, '');
		e.preventDefault();
		if (WPSEOCustomUploader) {
			WPSEOCustomUploader.open();
			return;
		}
		WPSEOCustomUploader = wp.media.frames.file_frame = wp.media({
			title: wpseonews.choose_image,
			button: { text: wpseonews.choose_image },
			multiple: false
		});
		WPSEOCustomUploader.on('select', function() {
			var attachment = WPSEOCustomUploader.state().get('selection').first().toJSON();
			$('#' + wpseo_target_id).val(attachment.url);
		});
		WPSEOCustomUploader.open();
	});
});
