<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly


class WC_Pos_Booking
{

    /**
     * __construct function.
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        add_filter('wc_pos_enqueue_scripts', array($this, 'pos_enqueue_scripts'), 1, 10);
        add_action('pos_admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
    }

    public function pos_enqueue_scripts($sctipts)
    {
        $sctipts['wc-pos-booking'] = WC_POS()->plugin_url() . '/assets/js/register/booking.js';
        return $sctipts;
    }

    public function admin_enqueue_scripts()
    {
        global $wp_locale;

        $colorpicker_l10n = array(
            'clear' => __('Clear'),
            'defaultString' => __('Default'),
            'pick' => __('Select Color'),
            'current' => __('Current Color'),
        );
        wp_localize_script('wp-color-picker', 'wpColorPickerL10n', $colorpicker_l10n);

        $r = array(
            'f' => 'MM',
            'j' => 'd',
            'y' => 'yy',
        );
        $wc_date_format = wc_date_format();
        $time_format = get_option('time_format');
        $wc_date_format = strtolower($wc_date_format);
        foreach ($r as $key => $replace) {
            $wc_date_format = str_replace($key, $replace, $wc_date_format);
        }
        $pos_booking_form_args = array(
            'closeText' => __('Close', 'wc_point_of_sale'),
            'currentText' => __('Today', 'wc_point_of_sale'),
            'prevText' => __('Previous', 'wc_point_of_sale'),
            'nextText' => __('Next', 'wc_point_of_sale'),
            'monthNames' => array_values($wp_locale->month),
            'monthNamesShort' => array_values($wp_locale->month_abbrev),
            'dayNames' => array_values($wp_locale->weekday),
            'dayNamesShort' => array_values($wp_locale->weekday_abbrev),
            'dayNamesMin' => array_values($wp_locale->weekday_initial),
            'firstDay' => get_option('start_of_week'),
            'current_time' => date('Ymd', current_time('timestamp')),
            'date_format' => $wc_date_format,
            'time_format' => $time_format,
            'bookings_data_labels' => apply_filters('woocommerce_bookings_data_labels', array(
                'type' => __('Booking Type', 'woocommerce-bookings'),
                'date' => __('Booking Date', 'woocommerce-bookings'),
                'time' => __('Booking Time', 'woocommerce-bookings'),
                'duration' => __('Duration', 'woocommerce-bookings'),
                'persons' => __('Person(s)', 'woocommerce-bookings')
            )),
        );

        wp_enqueue_script('jquery-ui-datepicker');
        wp_localize_script('jquery-ui-datepicker', 'pos_booking_form_args', $pos_booking_form_args);

        // Variables for JS scripts
        $booking_form_params = array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'i18n_date_unavailable' => __('This date is unavailable', 'woocommerce-bookings'),
            'i18n_date_fully_booked' => __('This date is fully booked and unavailable', 'woocommerce-bookings'),
            'i18n_date_partially_booked' => __('This date is partially booked - but bookings still remain', 'woocommerce-bookings'),
            'i18n_date_available' => __('This date is available', 'woocommerce-bookings'),
            'i18n_start_date' => __('Choose a Start Date', 'woocommerce-bookings'),
            'i18n_end_date' => __('Choose an End Date', 'woocommerce-bookings'),
            'i18n_dates' => __('Dates', 'woocommerce-bookings'),
            'i18n_choose_options' => __('Please select the options for your booking above first', 'woocommerce-bookings'),
            'i18n_label_persons' => __('Persons', 'woocommerce-bookings'),
        );

        wp_localize_script('jquery-ui-datepicker', 'booking_form_params', apply_filters('booking_form_params', $booking_form_params));

    }


    /**
     * Main WC_Pos_Booking Instance
     *
     * Ensures only one instance of WC_Pos_Booking is loaded or can be loaded.
     *
     * @since 1.9
     * @static
     * @return WC_Pos_Booking Main instance
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Cloning is forbidden.
     *
     * @since 1.9
     */
    public function __clone()
    {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'woocommerce'), '1.9');
    }

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 1.9
     */
    public function __wakeup()
    {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'woocommerce'), '1.9');
    }

}

return new WC_Pos_Booking();