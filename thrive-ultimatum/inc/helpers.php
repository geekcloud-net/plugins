<?php
/**
 * Helpers
 */

/**
 * Adds days and hours to date
 *
 * @param array $date [date,time]
 * @param int   $days
 * @param int   $hours
 *
 * @return array
 */
function tve_ult_add_to_date( $date, $days = 0, $hours = 0 ) {

	$date['date'] = date( 'j F Y', strtotime( $date['date'] . '  ' . $date['time'] . ' + ' . $days . ' days ' . $hours . ' hours' ) );
	$date['time'] = date( 'H:i', strtotime( $date['date'] . '  ' . $date['time'] . ' + ' . $hours . ' hours' ) );

	return $date;
}

/**
 * Calculates how many days and hours are between 2 dates
 *
 * @param string $start_date
 * @param string $end_date
 *
 * @return array with two indexes: days and hours
 */
function tve_ult_date_diff( $start_date, $end_date ) {

	$diff  = abs( strtotime( $end_date ) - strtotime( $start_date ) );
	$days  = floor( $diff / 86400 );
	$hours = ( $diff - $days * 86400 ) / 3600;

	return array(
		'days'  => $days,
		'hours' => $hours,
	);
}

/**
 * Returns a UNIX formatted date based on received arguments
 *
 * @param string $date
 * @param string $time
 *
 * @return string
 */
function tve_ult_pre_format_date( $date, $time ) {

	if ( empty( $date ) || empty( $time ) ) {
		return false;
	}

	$date = date( 'Y-m-d H:i:s', strtotime( $date . ' ' . $time ) );

	return $date;
}

/**
 * Returns the $array that will have only the required $fields
 *
 * @param array $array
 * @param array $fields
 *
 * @return array
 */
function tve_ult_array_filter( $array, $fields ) {
	$fields = array_flip( $fields );

	return array_intersect_key( $array, $fields );
}

/**
 * helper function used to return the html for ribbons (footer & header)
 * this should not be called directly, use tve_ult_get_design_html
 *
 * @see tve_ult_get_design_html
 *
 * @param array $design    design data
 * @param bool  $is_footer controls whether this is a footer bar or a header bar
 *
 * @return string
 */
function _tve_ult_get_ribbon_html( $design, $is_footer = false ) {

	$html = tve_ult_editor_custom_content( $design, false );

	list( $type, $key ) = TU_Template_Manager::tpl_type_key( $design[ TVE_Ult_Const::FIELD_TEMPLATE ] );

	return sprintf(
		'<div class="tve-ult-design tve-ult-bar%s" style="display:none">
			<div class="tl-style" id="tvu_%s" data-state="%s">%s</div>
		</div>',
		$is_footer ? ' tvu-footer' : ' tvu-header',
		$key,
		$design['id'],
		$html
	);
}

/**
 * get the html for a widget
 * this should not be called directly, use tve_ult_get_design_html
 *
 * @see tve_ult_get_design_html
 *
 * @param array $design design data
 *
 * @return string
 */
function tve_ult_widget_html( $design ) {

	$html = tve_ult_editor_custom_content( $design, false );

	list( $type, $key ) = TU_Template_Manager::tpl_type_key( $design[ TVE_Ult_Const::FIELD_TEMPLATE ] );

	return sprintf(
		'<div class="tve-ult-widget tve-ult-design" style="display:none">
			<div class="tl-style" id="tvu_%s" data-state="%s">%s</div>
		</div>',
		$key,
		$design['id'],
		$html
	);
}


/**
 * get the html for a widget
 * this should not be called directly, use tve_ult_get_design_html
 *
 * @see tve_ult_get_design_html
 *
 * @param array $design design data
 *
 * @return string
 */
function tve_ult_shortcode_html( $design ) {

	$html = tve_ult_editor_custom_content( $design, false );

	list( $type, $key ) = TU_Template_Manager::tpl_type_key( $design[ TVE_Ult_Const::FIELD_TEMPLATE ] );

	return sprintf(
		'<div class="tve-ult-shortcode tve-ult-design" style="display:none">
			<div class="tl-style" id="tvu_%s" data-state="%s">%s</div>
		</div>',
		$key,
		$design['id'],
		$html
	);
}

/**
 * get the html for a design
 *
 * @param string|array $design_or_id either design id or design array
 *
 * @return string
 */
function tve_ult_get_design_html( $design_or_id ) {
	$design = is_numeric( $design_or_id ) ? tve_ult_get_design( $design_or_id ) : $design_or_id;

	if ( empty( $design ) || empty( $design[ TVE_Ult_Const::FIELD_TEMPLATE ] ) ) {
		return '';
	}

	switch ( $design['post_type'] ) {
		case TVE_Ult_Const::DESIGN_TYPE_FOOTER_BAR:
			return _tve_ult_get_ribbon_html( $design, true );
		case TVE_Ult_Const::DESIGN_TYPE_HEADER_BAR:
			return _tve_ult_get_ribbon_html( $design );
		case TVE_Ult_Const::DESIGN_TYPE_WIDGET:
			return tve_ult_widget_html( $design );
		case TVE_Ult_Const::DESIGN_TYPE_SHORTCODE:
			return tve_ult_shortcode_html( $design );
		default:
			return '';
	}

}

//add_action( 'init', function () {
//	tve_ult_helper_generate_test_event_logs( 5000, true );
//} ); 332 x 130

/**
 * generates test data for the event logs (conversions / impressions)
 *
 * @param int    $entries
 * @param string $start_date
 */
function tve_ult_helper_generate_test_event_logs( $entries = 5000, $generate_conversions = true ) {
	global $tve_ult_db;
	$campaigns = tve_ult_get_campaigns( array(
		'get_settings' => false,
	) );
	$total     = count( $campaigns );

	$i   = 0;
	$now = strtotime( tve_ult_current_time( 'mysql' ) );
	while ( $i < $entries ) {
		srand();
		$campaign = $campaigns[ rand( 0, $total - 1 ) ];
		$start    = strtotime( $campaign->post_date );
		$date     = date( 'Y-m-d H:i:s', $start + rand( 0, $now - $start ) );
		$data     = array(
			'date'        => $date,
			'type'        => TVE_Ult_Const::LOG_TYPE_IMPRESSION,
			'campaign_id' => $campaign->ID,
		);
		$tve_ult_db->insert_event_log( $data );
		if ( $generate_conversions && rand( 0, 10 ) < 2 ) {
			$data['type'] = TVE_Ult_Const::LOG_TYPE_CONVERSION;
			$tve_ult_db->insert_event_log( $data );
		}

		$i ++;
	}

	die( 'logs generated' );

}

/**
 * get the formatted timezone difference from GMT
 * this is used from javascript, when calculating the end date for the countdown timer element
 *
 * @return string timezone time difference in the format: {+/-}HH:MM
 */
function tve_ult_get_timezone_format() {
	$timezone_offset = tve_ult_gmt_offset();
	$sign            = ( $timezone_offset < 0 ? '-' : '+' );
	$min             = abs( $timezone_offset ) * 60;
	$hour            = floor( $min / 60 );

	return $sign . str_pad( $hour, 2, '0', STR_PAD_LEFT ) . ':' . str_pad( $min % 60, 2, '0', STR_PAD_LEFT );
}

/**
 *
 * return every component of a date - used to populate the countdown timer element with the correct end date for the campaign
 *
 * @param string $date Y-m-d H:i:s formatted date
 *
 * @return array
 */
function tve_ult_get_date_components( $date ) {
	if ( empty( $date ) ) {
		return array();
	}

	$time = strtotime( $date );

	$diff       = tve_ult_date_diff( tve_ult_current_time( 'Y-m-d H:i:00' ), $date );
	$day_digits = strlen( (string) intval( $diff['days'] ) );

	return array(
		'date'     => date( 'Y-m-d', $time ),
		'hour'     => date( 'H', $time ),
		'min'      => date( 'i', $time ),
		'timezone' => tve_ult_get_timezone_format(),
		'dd'       => $day_digits,
	);
}

/**
 * Calculate and return the offset from GMT, in hours as a float value from a timezone string.
 * The timezone string can be either a string like: UTC+3.5 or an actual timezone, like: Europe/Zurich.
 *
 * @param string $tz_string
 *
 * @return float|false on failure
 */
function tve_ult_gmt_offset_from_tzstring( $tz_string ) {
	if ( empty( $tz_string ) ) {
		return false;
	}
	if ( strpos( $tz_string, 'UTC' ) === 0 ) {
		if ( $tz_string === 'UTC' ) {
			$value = '0';
		} else {
			$value = preg_replace( '#^UTC(\+|\-)?#', '', $tz_string );
		}

		return round( $value, 2 );
	}
	$timezone_object = timezone_open( $tz_string );
	$datetime_object = date_create();
	if ( false === $timezone_object || false === $datetime_object ) {
		return false;
	}

	return round( timezone_offset_get( $timezone_object, $datetime_object ) / HOUR_IN_SECONDS, 2 );
}


/**
 * Retrieve the current time based on specified type.
 *
 * The 'mysql' type will return the time in the format for MySQL DATETIME field.
 * The 'timestamp' type will return the current timestamp.
 * Other strings will be interpreted as PHP date formats (e.g. 'Y-m-d').
 *
 * If $gmt is set to either '1' or 'true', then both types will use GMT time.
 * if $gmt is false, the output is adjusted with the GMT offset setup from Thrive Ultimatum.
 *
 * @see current_time()
 *
 * @param string   $type Type of time to retrieve. Accepts 'mysql', 'timestamp', or PHP date
 *                       format string (e.g. 'Y-m-d').
 * @param int|bool $gmt  Optional. Whether to use GMT timezone. Default false.
 *
 * @return int|string Integer if $type is 'timestamp', string otherwise.
 */
function tve_ult_current_time( $type, $gmt = 0 ) {
	switch ( $type ) {
		case 'mysql':
			return ( $gmt ) ? gmdate( 'Y-m-d H:i:s' ) : gmdate( 'Y-m-d H:i:s', ( time() + ( tve_ult_gmt_offset() * HOUR_IN_SECONDS ) ) );
		case 'timestamp':
			return ( $gmt ) ? time() : time() + ( tve_ult_gmt_offset() * HOUR_IN_SECONDS );
		default:
			return ( $gmt ) ? date( $type ) : date( $type, time() + ( tve_ult_gmt_offset() * HOUR_IN_SECONDS ) );
	}
}

/**
 * Output or return the HTML ( <a> node ) needed for embedding a wistia popover video.
 *
 * @param        $video_id
 * @param string $link_content html to add inside the link
 * @param string $before       optional, some html / text to be prepended to the output
 * @param bool   $echo         whether to output the content or return it
 *
 * @return string|void
 */
function tve_ult_video( $video_id, $link_content = '<span class="tvd-icon-play tvu-tutorial-play"></span>', $before = '&nbsp;', $echo = true ) {
	$html = sprintf(
		'%s<a class="wistia-popover[height=450,playerColor=2bb914,width=800]" href="//fast.wistia.net/embed/iframe/%s?popover=true">%s</a>',
		$before,
		$video_id,
		$link_content
	);
	if ( ! $echo ) {
		return $html;
	}
	echo $html;
}
