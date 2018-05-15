<?php
// Porto countdown

add_shortcode('porto_countdown', 'porto_shortcode_countdown');
add_action('vc_after_init', 'porto_load_countdown_shortcode');

function porto_shortcode_countdown( $atts, $content = null ) {

    ob_start();
    if  ($template = porto_shortcode_template( 'porto_countdown' ) ) {
        include $template;
    }
    return ob_get_clean();
}

function porto_load_countdown_shortcode() {

    $animation_type = porto_vc_animation_type();
    $animation_duration = porto_vc_animation_duration();
    $animation_delay = porto_vc_animation_delay();
    $custom_class = porto_vc_custom_class();

    vc_map(
        array(
           "name" => __("Porto Countdown","porto-shortcodes"),
           "base" => "porto_countdown",
           "class" => "porto_countdown",
           "icon" => "porto4_vc_countdown",
           "category" => __( 'Porto', 'porto-shortcodes' ),
           "description" => __("Countdown Timer.","porto-shortcodes"),
           "params" => array(
                array(
                    "type" => "dropdown",
                    "class" => "",
                    "heading" => __("Countdown Timer Style", "porto-shortcodes"),
                    "param_name" => "count_style",
                    "value" => array(
                            __("Digit and Unit Side by Side","porto-shortcodes") => "porto-cd-s1",
                            __("Digit and Unit Up and Down","porto-shortcodes") => "porto-cd-s2",
                        ),
                    "group" => "General Settings",
                ),
                array(
                    "type" => "datetimepicker",
                    "class" => "",
                    "heading" => __("Target Time For Countdown", "porto-shortcodes"),
                    "param_name" => "datetime",
                    "value" => "",
                    "description" => __("Date and time format (yyyy/mm/dd hh:mm:ss).", "porto-shortcodes"),
                    "group" => "General Settings",
                ),
                array(
                    "type" => "dropdown",
                    "class" => "",
                    "heading" => __("Countdown Timer Depends on", "porto-shortcodes"),
                    "param_name" => "porto_tz",
                    "value" => array(
                            __("WordPress Defined Timezone","porto-shortcodes") => "porto-wptz",
                            __("User's System Timezone","porto-shortcodes") => "porto-usrtz",
                        ),
                    "group" => "General Settings",
                ),
                array(
                    "type" => "checkbox",
                    "class" => "",
                    "heading" => __("Select Time Units To Display In Countdown Timer", "porto-shortcodes"),
                    "param_name" => "countdown_opts",
                    "value" => array(
                            __("Years","porto-shortcodes") => "syear",
                            __("Months","porto-shortcodes") => "smonth",
                            __("Weeks","porto-shortcodes") => "sweek",
                            __("Days","porto-shortcodes") => "sday",
                            __("Hours","porto-shortcodes") => "shr",
                            __("Minutes","porto-shortcodes") => "smin",
                            __("Seconds","porto-shortcodes") => "ssec",
                        ),
                    "group" => "General Settings",
                ),
                $custom_class,
                array(
                    "type" => "textfield",
                    "class" => "",
                    "heading" => __("Day (Singular)", "porto-shortcodes"),
                    "param_name" => "string_days",
                    "value" => "Day",
                    "group" => "Strings Translation",
                ),
                array(
                    "type" => "textfield",
                    "class" => "",
                    "heading" => __("Days (Plural)", "porto-shortcodes"),
                    "param_name" => "string_days2",
                    "value" => "Days",
                    "group" => "Strings Translation",
                ),
                array(
                    "type" => "textfield",
                    "class" => "",
                    "heading" => __("Week (Singular)", "porto-shortcodes"),
                    "param_name" => "string_weeks",
                    "value" => "Week",
                    "group" => "Strings Translation",
                ),
                array(
                    "type" => "textfield",
                    "class" => "",
                    "heading" => __("Weeks (Plural)", "porto-shortcodes"),
                    "param_name" => "string_weeks2",
                    "value" => "Weeks",
                    "group" => "Strings Translation",
                ),
                array(
                    "type" => "textfield",
                    "class" => "",
                    "heading" => __("Month (Singular)", "porto-shortcodes"),
                    "param_name" => "string_months",
                    "value" => "Month",
                    "group" => "Strings Translation",
                ),
                array(
                    "type" => "textfield",
                    "class" => "",
                    "heading" => __("Months (Plural)", "porto-shortcodes"),
                    "param_name" => "string_months2",
                    "value" => "Months",
                    "group" => "Strings Translation",
                ),
                array(
                    "type" => "textfield",
                    "class" => "",
                    "heading" => __("Year (Singular)", "porto-shortcodes"),
                    "param_name" => "string_years",
                    "value" => "Year",
                    "group" => "Strings Translation",
                ),
                array(
                    "type" => "textfield",
                    "class" => "",
                    "heading" => __("Years (Plural)", "porto-shortcodes"),
                    "param_name" => "string_years2",
                    "value" => "Years",
                    "group" => "Strings Translation",
                ),
                array(
                    "type" => "textfield",
                    "class" => "",
                    "heading" => __("Hour (Singular)", "porto-shortcodes"),
                    "param_name" => "string_hours",
                    "value" => "Hour",
                    "group" => "Strings Translation",
                ),
                array(
                    "type" => "textfield",
                    "class" => "",
                    "heading" => __("Hours (Plural)", "porto-shortcodes"),
                    "param_name" => "string_hours2",
                    "value" => "Hours",
                    "group" => "Strings Translation",
                ),
                array(
                    "type" => "textfield",
                    "class" => "",
                    "heading" => __("Minute (Singular)", "porto-shortcodes"),
                    "param_name" => "string_minutes",
                    "value" => "Minute",
                    "group" => "Strings Translation",
                ),
                array(
                    "type" => "textfield",
                    "class" => "",
                    "heading" => __("Minutes (Plural)", "porto-shortcodes"),
                    "param_name" => "string_minutes2",
                    "value" => "Minutes",
                    "group" => "Strings Translation",
                ),
                array(
                    "type" => "textfield",
                    "class" => "",
                    "heading" => __("Second (Singular)", "porto-shortcodes"),
                    "param_name" => "string_seconds",
                    "value" => "Second",
                    "group" => "Strings Translation",
                ),
                array(
                    "type" => "textfield",
                    "class" => "",
                    "heading" => __("Seconds (Plural)", "porto-shortcodes"),
                    "param_name" => "string_seconds2",
                    "value" => "Seconds",
                    "group" => "Strings Translation",
                ),
                array(
                    "type" => "porto_param_heading",
                    "text" => __("Timer Digit Settings","porto-shortcodes"),
                    "param_name" => "countdown_typograpy",
                    "group" => "Typography",
                    'edit_field_class' => 'no-top-margin vc_column vc_col-sm-12',
                ),
                array(
                    "type" => "textfield",
                    "heading"       =>  __("Font Weight", "porto-shortcodes"),
                    "param_name"    =>  "tick_style",
                    "group" => "Typography"
                ),
                array(
                    "type" => "colorpicker",
                    "class" => "",
                    "heading" => __("Timer Digit Text Color", "porto-shortcodes"),
                    "param_name" => "tick_col",
                    "value" => "",
                    "group" => "Typography",
                ),
                array(
                    "type" => "textfield",
                    "class" => "",
                    "heading" => __("Timer Digit Text Size", 'porto-shortcodes'),
                    "param_name" => "tick_size",
                    "group" => "Typography"
                ),
                array(
                    "type" => "textfield",
                    "class" => "",
                    "heading" => __("Timer Digit Text Line height", 'porto-shortcodes'),
                    "param_name" => "tick_line_height",
                    "group" => "Typography"
                ),
                array(
                    "type" => "porto_param_heading",
                    "text" => __("Timer Unit Settings","porto-shortcodes"),
                    "param_name" => "countdown_typograpy",
                    "group" => "Typography",
                    'edit_field_class' => 'no-top-margin vc_column vc_col-sm-12',
                ),
                array(
                    "type" => "colorpicker",
                    "class" => "",
                    "heading" => __("Timer Unit Text Color", "porto-shortcodes"),
                    "param_name" => "tick_sep_col",
                    "value" => "",
                    "group" => "Typography",
                ),
                array(
                    "type" => "textfield",
                    "class" => "",
                    "heading" => __("Timer Unit Text Size", 'porto-shortcodes'),
                    "param_name" => "tick_sep_size",
                    "group" => "Typography"
                ),
                array(
                    'type' => 'css_editor',
                    'heading' => __( 'Css', 'porto-shortcodes' ),
                    'param_name' => 'css_countdown',
                    'group' => __( 'Design ', 'porto-shortcodes' ),
                    'edit_field_class' => 'vc_col-sm-12 vc_column no-vc-background no-vc-border creative_link_css_editor',
                ),
            )
        )
    );

    if( class_exists( 'WPBakeryShortCode' ) ) {
        class WPBakeryShortCode_porto_countdown extends WPBakeryShortCode {
        }
    }
}