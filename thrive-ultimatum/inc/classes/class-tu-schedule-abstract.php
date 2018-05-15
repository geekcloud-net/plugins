<?php

/**
 * Base class for campaign schedules
 */
abstract class TU_Schedule_Abstract {

	/**
	 * Campaign schedule settings
	 *
	 * @var array
	 */
	protected $settings = array();
	protected $conversion_events = array();
	protected $lockdown = '';

	/**
	 * Campaign for which this schedule applies
	 *
	 * @var int
	 */
	protected $campaign_id;

	protected static $types = array(
		TVE_Ult_Const::CAMPAIGN_TYPE_ABSOLUTE  => 'TU_Schedule_Absolute',
		TVE_Ult_Const::CAMPAIGN_TYPE_ROLLING   => 'TU_Schedule_Rolling',
		TVE_Ult_Const::CAMPAIGN_TYPE_EVERGREEN => 'TU_Schedule_Evergreen',
	);

	/**
	 * TVE_Ult_Schedule_Abstract constructor.
	 *
	 * @param int $campaign_id
	 */
	public function __construct( $campaign_id ) {
		$this->campaign_id = $campaign_id;
	}

	/**
	 * Instantiates a campaign schedule based on campaign type
	 *
	 * @param string $campaign_id
	 *
	 * @return TU_Schedule_Abstract
	 */
	public static function factory( $campaign_id ) {

		$campaign_type = get_post_meta( $campaign_id, TVE_Ult_Const::META_NAME_FOR_CAMPAIGN_TYPE, true );
		/**
		 * if nothing is set for a campaign, return an empty container
		 */
		if ( ! isset( self::$types[ $campaign_type ] ) ) {
			require_once TVE_Ult_Const::plugin_path() . 'inc/classes/class-tu-schedule-none.php';

			return new TU_Schedule_None( $campaign_id );
		}

		return new self::$types[ $campaign_type ]( $campaign_id );
	}

	/**
	 * Check if the schedule for a campaign applies (for the current date)
	 *
	 * @return bool
	 */
	public abstract function applies();

	/**
	 * set schedule settings
	 *
	 * @param array $settings
	 *
	 * @return $this
	 */
	public function set( $settings = null ) {
		if ( is_null( $settings ) ) {
			$settings = tve_ult_get_campaign_settings( $this->campaign_id );
		}
		$this->settings = $settings;

		return $this;
	}

	/**
	 * Getter for schedule settings
	 *
	 * @return array schedule settings
	 */
	public function get() {
		return $this->settings;
	}

	/**
	 * Getter for conversion events
	 *
	 * @return array
	 */
	public function get_conversion_events() {
		return $this->conversion_events;
	}

	/**
	 * Setter for conversion events
	 *
	 * @param array $conversion_events
	 */
	public function set_conversion_events( $conversion_events ) {
		$this->conversion_events = $conversion_events;
	}

	/**
	 * Getter for lockdown
	 *
	 * @return array
	 */
	public function get_lockdown() {
		return $this->lockdown;
	}

	/**
	 * Setter for lockdown
	 *
	 * @param array $lockdown
	 */
	public function set_lockdown( $lockdown ) {
		$this->lockdown = $lockdown;
	}
	
	/**
	 * @param $offset
	 *
	 * @return string
	 */
	protected function format_gmt_offset( $offset ) {

		$h         = (int) $offset;
		$remainder = $offset - $h;

		return ( ( $h < 0 ) ? '-' : '+' ) .
		       str_pad( abs( $h ), 2, '0', STR_PAD_LEFT ) . ':' .
		       str_pad( abs( $remainder ) * 60, 2, STR_PAD_LEFT );
	}

	/**
	 * Returns the current date / time as a timestamp
	 * it takes into account also the default timezone on the server
	 *
	 * @param bool $strip_seconds
	 *
	 * @return int $timestamp
	 */
	protected function now( $strip_seconds = true ) {

		/**
		 * this will return the current time set from the server, with the difference set in gmt_offset option added / substracted from the time
		 *
		 * floor this value to the :00 seconds
		 */

		if ( $strip_seconds ) {
			$time = floor( tve_ult_current_time( 'timestamp' ) / 60 ) * 60;
		} else {
			$time = tve_ult_current_time( 'timestamp' );
		}

		return $time;
	}

	/**
	 * Gets the timestamp for the given date, based on the gmt_offset option from WP admin
	 *
	 * @param string|array $date
	 *
	 * @return int timestamp
	 */
	protected function date( $date ) {

		//TODO: is there anything else needed here ?
		// if the date is coming from our meta let's make it a string
		if ( is_array( $date ) ) {
			$date = tve_ult_pre_format_date( $date['date'], $date['time'] );
		}

		return strtotime( $date );
	}

	/**
	 * Checks if a date is in the past
	 *
	 * @param string $date Y-m-d H:i:s representation of the date
	 *
	 * @return bool
	 */
	public function is_past( $date ) {
		return $this->date( $date ) < $this->now();
	}

	/**
	 * Checks if a date received as parameter is in the future
	 *
	 * @param string $date Y-m-d H:i:s representation of the date
	 *
	 * @return bool
	 */
	public function is_future( $date ) {
		return $this->date( $date ) > $this->now();
	}

	/**
	 * Check if any of the triggers match for ending the campaign
	 *
	 * @param $options
	 *
	 * @return bool
	 */
	public function check_triggers( $options ) {
		// check if the trigger is a specific page or a conversion
		if ( $options['trigger'] === TVE_Ult_Const::TRIGGER_OPTION_CONVERSION ) {
			return true;
		} else {
			$post_id = $this->param( 'post_id' );

			if ( $post_id !== $options['trigger_ids'] ) {
				return true;
			}
		}

		$this->do_conversion( $options );

		return false;
	}

	/**
	 * Based on triggers update the email log saved in cookie
	 * with options sent as parameter;
	 *
	 * To be implemented in child classes
	 *
	 * @param array $trigger_options
	 * @param array $options
	 */
	public function update_email_log( $trigger_options = array(), $options = array() ) {

	}

	/**
	 *
	 * checks if a conversion event is fulfilled for a campaign
	 * if the action is "move to a new campaign" - we need to force the display of that campaign
	 * to do this, we set a special cookie - tu_force_campaign
	 *
	 * @param array $options trigger options
	 */
	public function do_conversion( $options ) {
		if ( $options['event'] === TVE_Ult_Const::TRIGGER_OPTION_MOVE ) {

			$campaign = tve_ult_get_campaign( $options['end_id'], array(
				'get_settings' => true,
			) );
			/** @var TU_Schedule_Abstract $related_campaign_schedule */
			$related_campaign_schedule = $campaign->tu_schedule_instance;

			// cannot start a campaign if its status is not running
			// check if the campaign didn't already start for this user
			if ( $campaign->status === TVE_Ult_Const::CAMPAIGN_STATUS_RUNNING && ! isset( $_COOKIE[ $this->cookie_name( $options['end_id'] ) ] ) ) {

				// get campaign settings
				$settings = get_post_meta( $options['end_id'], TVE_Ult_Const::META_NAME_FOR_CAMPAIGN_SETTINGS, true );

				// set cookie
				$start_date['date'] = date( 'j F Y', $this->now() );
				$start_date['time'] = date( 'H:i:s', $this->now() );
				$value              = maybe_serialize( array( 'start_date' => $start_date ) );

				//set the cookie expiration date
				// there are issues on 32 bit platforms using this: we need to make sure that this is not in the past (something like 1907)
				$expire = strtotime( '+' . ( $settings['end'] + $settings['duration'] ) . ' days', $this->date( $start_date ) );
				if ( $expire < tve_ult_current_time( 'timestamp' ) ) { // overflow
					$expire = strtotime( '2038-01-01 ' );
				}

				/**
				 * This means that a new evergreen campaign is started for a conversion event of this campaign
				 * this takes priority over any other campaign that should be displayed on this request - regardless of the priority settings (order of the campaigns)
				 */
				setcookie( $related_campaign_schedule->cookie_name(), $value, $expire, '/' );
				$_COOKIE[ $related_campaign_schedule->cookie_name() ] = $value;

				/**
				 * set a cookie that will force the display of this campaign
				 */
				$now_wp_time = tve_ult_current_time( 'Y-m-d H:i:s' );
				setcookie( $related_campaign_schedule->cookie_force_display(), $now_wp_time, $expire, '/' );
				$_COOKIE[ $related_campaign_schedule->cookie_force_display() ] = $now_wp_time;
			}
		}
		if ( ! isset( $_COOKIE[ $this->cookie_end_name() ] ) ) {
			/**
			 * the conversion can be made only if the campaign is not ended
			 */
			$this->register_conversion();
			setcookie( $this->cookie_end_name(), 'end', time() + ( YEAR_IN_SECONDS * 10 ), '/' );
			/**
			 * also, unset the "force_campaign" cookie
			 */
			setcookie( $this->cookie_force_display(), '', time() - DAY_IN_SECONDS * 200, '/' );
			unset( $_COOKIE[ $this->cookie_force_display() ] );
		}
	}

	/**
	 * Register conversion and
	 * Set cookie for conversion
	 */
	public function register_conversion() {
		if ( ! isset( $_COOKIE[ TVE_Ult_Const::COOKIE_IMPRESSION . $this->campaign_id ] ) ) {
			return;
		}

		if ( isset( $_COOKIE[ TVE_Ult_Const::COOKIE_CONVERSION . $this->campaign_id ] ) ) {
			return;
		}

		if ( tve_dash_is_crawler() ) {
			return;
		}

		tve_ult_register_conversion( $this->campaign_id );

		$expire = tve_ult_current_time( 'timestamp' ) + YEAR_IN_SECONDS;
		$value  = date( 'Y-m-d H:i:s' );

		/*
		 * expiration dates for cookies:
		 * - for evergreen campaigns: the number of days entered in the "Don't show this campaign until" field
		 * - for others: 1 year
		 * also modifies the global $_COOKIE variable - so that it's available later in the same request if required
		 */
		if ( $this instanceof TU_Schedule_Evergreen && ! empty( $this->settings ) && ! empty( $this->settings['end'] ) ) {
			$start_date = $_COOKIE[ TVE_Ult_Const::COOKIE_IMPRESSION . $this->campaign_id ];
			$expire     = strtotime( $start_date ) + $this->settings['end'] * DAY_IN_SECONDS;
		}

		setcookie( TVE_Ult_Const::COOKIE_CONVERSION . $this->campaign_id, $value, $expire, '/' );
		$_COOKIE[ TVE_Ult_Const::COOKIE_CONVERSION . $this->campaign_id ] = $value;
	}

	/**
	 * Loops through conversion events of campaign
	 * And if there are specific trigger that match the post_id
	 * does the conversion
	 *
	 * If an event is executed then the design should not be displayed
	 *
	 * @see do_conversion()
	 *
	 * @param WP_Post $campaign
	 * @param int $post_id
	 *
	 * @return bool if any conversion event was executed
	 */
	public function check_specific_events( $campaign, $post_id ) {
		$event_executed = false;

		foreach ( $campaign->conversion_events as $event ) {
			if ( $event['trigger_options']['trigger'] === TVE_Ult_Const::TRIGGER_OPTION_SPECIFIC && $post_id == $event['trigger_options']['trigger_ids'] ) {
				/** @var TU_Schedule_Abstract $schedule */
				$schedule = $campaign->tu_schedule_instance;
				$schedule->do_conversion( $event['trigger_options'] );
				$event_executed = true;
			}
		}

		return $event_executed;
	}

	/**
	 * @param mixed $id a campaign id
	 *
	 * @return string
	 */
	public function cookie_name( $id = null ) {
		return TVE_Ult_Const::COOKIE_NAME . ( is_null( $id ) ? $this->campaign_id : $id );
	}

	/**
	 *
	 * @return string
	 */
	public function cookie_end_name() {
		return TVE_Ult_Const::COOKIE_END_NAME . $this->campaign_id;
	}

	protected function param( $key, $default = null ) {
		return isset( $_POST[ $key ] ) ? $_POST[ $key ] : ( isset( $_REQUEST[ $key ] ) ? $_REQUEST[ $key ] : $default );
	}

	/**
	 * get the force display cookie for a campaign
	 * @return string
	 */
	public function cookie_force_display() {
		return TVE_Ult_Const::COOKIE_FORCE_DISPLAY . $this->campaign_id;
	}

	/**
	 * get the end date of the current campaign instance (interval)
	 *
	 * should return a date in the format: Y-m-d H:i:s
	 *
	 * @return string
	 */
	public abstract function get_end_date();

	/**
	 * Returns how many hours a schedule occurs
	 *
	 * @return int
	 */
	public abstract function get_duration();

	/**
	 * Should check whether or not this campaign should redirect the user to a pre-access page
	 *
	 * @return bool
	 */
	public abstract function should_redirect_pre_access();

	/**
	 * Should check whether or not this campaign has ended and the user should be redirected to the expired page
	 *
	 * @return bool
	 */
	public abstract function should_redirect_expired();

}
