jQuery('#video_seo_done').hide();

function update_bar(percentage) {
	if (percentage > 100) {
		percentage = 99;
	}

	percentage = Math.round(percentage * 100) / 100;

	jQuery('.bar', '#video_seo_progressbar').css('width', percentage + '%');
	jQuery('#video_seo_percentage_hidden').val(percentage);

	if (percentage >= 5) {
		jQuery('.bar_status', '#video_seo_progressbar').html(percentage + '%');
	}
	else {
		jQuery('.bar_status', '#video_seo_progressbar').html('&nbsp;');
	}
}

function video_fetch(start) {
	var totalposts = parseInt(jQuery('#video_seo_total_posts').html());
	var force_reindex = jQuery('#video_seo_force_reindex').length;

	var data = {
		'action' : 'index_posts',
		'type'   : 'index',
		'start'  : start,
		'total'  : totalposts,
		'portion': 5,
		'nonce' : jQuery('#videoseo-nonce-ajax').val()
	};

	if ( force_reindex > 0 ) {
		data.force = 'on';
	}

	jQuery.post(ajaxurl, data, function (response) {
		if (start < totalposts) {
			setTimeout(function () {
				video_fetch((start + 5))
			}, 200);

			calculate_to_go(start, totalposts, response);
		}
		else {
			update_bar(100);
			jQuery('#video_seo_total_time').html('&nbsp;');
			jQuery('#video_seo_done').show();
		}
	});
	var posts_to_go = totalposts - start;

	if (posts_to_go < 0) {
		posts_to_go = 0;
	}
	jQuery('#video_seo_posts_to_go').html(posts_to_go);

	var part = (5 / totalposts) * 100;
	var percentage = parseFloat(jQuery('#video_seo_percentage_hidden').val());
	var new_perc = part + percentage;

	update_bar(new_perc);
}

function calculate_to_go(current, totalposts, time) {
	var posts_to_go = totalposts - current;
	var factor = posts_to_go / 5;
	var total_to_go = factor * parseInt(time);

	if (total_to_go == Infinity) {
		jQuery('#video_seo_total_time').html('Unknown');
	}
	else {
		jQuery('#video_seo_total_time').html(Math.round(total_to_go) + ' seconds');
	}
}

function get_total_posts() {
	update_bar(0);

	var data = {
		'action': 'index_posts',
		'type'  : 'total_posts',
		'nonce' : jQuery('#videoseo-nonce-ajax').val()
	};

	jQuery.post(ajaxurl, data, function (response) {
		var totalposts = response;
		jQuery('#video_seo_total_posts').html(totalposts);
		video_fetch(0);
	});

}

setTimeout(function () {
	get_total_posts()
}, 500);