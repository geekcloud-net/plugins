<?php

$count_style = $datetime = $porto_tz = $countdown_opts = $tick_col = $tick_size = $tick_line_height = $tick_style = $tick_sep_col = $tick_sep_size = $tick_sep_line_height = '';
$tick_sep_style = $el_class = '';
$string_days = $string_weeks = $string_months = $string_years = $string_hours = $string_minutes = $string_seconds = '';
$string_days2 = $string_weeks2 = $string_months2 = $string_yers2 = $string_hours2 = $string_minutes2 = $string_seconds2 = '';
extract(shortcode_atts( array(
    'count_style'=>'porto-cd-s1',
    'datetime'=>'',
    'porto_tz'=>'porto-wptz',
    'countdown_opts'=>'',
    'tick_col'=>'',
    'tick_size'=>'36',
    'tick_line_height'=>'',
    'tick_style'=>'',
    'tick_sep_col'=>'',
    'tick_sep_size'=>'13',
    'tick_sep_line_height'=> '',
    'tick_sep_style'=>'',
    'el_class'=>'',
    'string_days' => 'Day',
    'string_days2' => 'Days',
    'string_weeks' => 'Week',
    'string_weeks2' => 'Weeks',
    'string_months' => 'Month',
    'string_months2' => 'Months',
    'string_years' => 'Year',
    'string_years2' => 'Years',
    'string_hours' => 'Hour',
    'string_hours2' => 'Hours',
    'string_minutes' => 'Minute',
    'string_minutes2' => 'Minutes',
    'string_seconds' => 'Second',
    'string_seconds2' => 'Seconds',
    'css_countdown' => '',
),$atts));
$count_frmt = $labels = $countdown_design_style = '';
$labels = $string_years2 .','.$string_months2.','.$string_weeks2.','.$string_days2.','.$string_hours2.','.$string_minutes2.','.$string_seconds2;
$labels2 = $string_years .','.$string_months.','.$string_weeks.','.$string_days.','.$string_hours.','.$string_minutes.','.$string_seconds;
$countdown_opt = explode(",",$countdown_opts);
if ( is_array( $countdown_opt ) ) {
    foreach($countdown_opt as $opt) {
        if ( $opt == "syear" ) $count_frmt .= 'Y';
        if ( $opt == "smonth" ) $count_frmt .= 'O';
        if ( $opt == "sweek" ) $count_frmt .= 'W';
        if ( $opt == "sday" ) $count_frmt .= 'D';
        if ( $opt == "shr" ) $count_frmt .= 'H';
        if ( $opt == "smin" ) $count_frmt .= 'M';
        if ( $opt == "ssec" ) $count_frmt .= 'S';
    }
}

$countdown_design_style = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $css_countdown, ' ' ), "porto_countdown", $atts );
$countdown_id = 'countdown-wrap-'.rand(1000, 9999);

$data_attr = '';
if ( $count_frmt == '' ) {
    $count_frmt = 'DHMS';
}


$tick_style_css = '';
$tick_sep_style_css = '';

if ( !is_numeric( $tick_size ) ) {
    $tick_size = preg_replace( '/[^0-9]/', "", $tick_size );
}
if ( !is_numeric( $tick_line_height ) ) {
    $tick_line_height = preg_replace( '/[^0-9]/', "", $tick_line_height );
}
if ( $tick_size ) {
    $tick_style_css .= 'font-size:'. esc_attr( $tick_size ) .'px;';
}
if ( $tick_line_height ) {
    $tick_style_css .= 'line-height:'. esc_attr( $tick_line_height ) .'px;';
}


if ( !is_numeric( $tick_sep_size ) ) {
    $tick_sep_size = preg_replace( '/[^0-9]/', "", $tick_sep_size );
}
if ( !is_numeric( $tick_sep_line_height ) ) {
    $tick_sep_line_height = preg_replace( '/[^0-9]/', "", $tick_sep_line_height );
}

if ( $tick_sep_size ) {
    $tick_sep_style_css .= 'font-size:'. esc_attr( $tick_sep_size ) .'px;';
}
if ( $tick_sep_line_height ) {
    $tick_sep_style_css .= 'line-height:'. esc_attr( $tick_sep_line_height ) .'px;';
}

$count_down_id = "count-down-wrap-".rand(1000,9999);

$data_attr .= ' data-tick-style="'.esc_attr($tick_style).'" ';
$data_attr .= ' data-tick-p-style="'.esc_attr($tick_sep_style).'" ';

if ( $tick_style ) {
    $tick_style_css = 'font-weight: '. esc_attr( $tick_style ) .';';
}
if ( $tick_sep_style ) {
    $tick_sep_style_css = 'font-weight: '. esc_attr( $tick_sep_style ) .';';
}
$output  = '<style>';
$output .=  '#'.$count_down_id.' .porto_countdown-amount { ';
$output .=      $tick_style_css;
$output .=  '   color: '. esc_attr( $tick_col ). ';';
$output .=  ' } ';
$output .=  '#'.$count_down_id.' .porto_countdown-period, #'.$count_down_id.' .porto_countdown-row:before {';
$output .=  '   color: '. esc_attr( $tick_sep_col ) .';';
$output .= $tick_sep_style_css;
$output .= '}';

$output .= '</style>';
$output .= '<div class="porto_countdown '.esc_attr($countdown_design_style).' '.esc_attr($el_class).' '.esc_attr($count_style).'"'. $tick_sep_style .'>';

if($datetime!=''){
    $output .='<div id="'.esc_attr($count_down_id).'"  class="porto_countdown-div porto_countdown-dateAndTime '.esc_attr($porto_tz).'" data-labels="'.esc_attr($labels).'" data-labels2="'.esc_attr($labels2).'"  data-terminal-date="'.esc_attr($datetime).'" data-countformat="'.esc_attr($count_frmt).'" data-time-zone="'.esc_attr(get_option('gmt_offset')).'" data-time-now="'.esc_attr(str_replace('-', '/', current_time('mysql'))).'"  data-tick-col="'.esc_attr($tick_col).'" data-tick-p-col="'.esc_attr($tick_sep_col).'" '.$data_attr.'>'.$datetime.'</div>';
}
$output .='</div>';

echo $output;