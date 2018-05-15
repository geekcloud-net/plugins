<?php

/**
 * Created by PhpStorm.
 * User: radu
 * Date: 08.02.2016
 * Time: 10:23
 */
class TU_Schedule_Rolling extends TU_Schedule_Abstract {

	/**
	 * check if the campaign should be displayed
	 */
	public function applies() {

		// Before anything let's check if the campaign has started if not let's end it all
		if ( ! $this->is_past( $this->settings['start'] ) ) {
			return false;
		}

		// Let's check if the campaign will ever end
		if ( null !== ( $end = $this->get_global_end_date() ) && ! $this->is_future( $end ) ) {
			return false;
		}

		//check if end cookie is set for this campaign
		if ( isset( $_COOKIE[ $this->cookie_end_name() ] ) ) {
			return false;
		}

		// check if any conversion event meet the criteria and end the campaign if it does so
		if ( ! empty( $this->conversion_events ) ) {
			foreach ( $this->conversion_events as $event ) {
				$trigger = $this->check_triggers( $event['trigger_options'] );

				if ( ! $trigger ) {
					return false;
				}
			}
		}

		// Check if it's a period where the campaign should repeat
		$check_repeat = 'check_repeat_' . $this->settings['rolling_type'];
		if ( ! method_exists( $this, $check_repeat ) ) {
			return false;
		}

		return $this->$check_repeat();
	}

	/**
	 * returns the end date calculated for the campaign (the "global" end date - when the campaign is actually ending)
	 * if no end date or number of occurrences have been set, returns null
	 *
	 * the return date / time is constructed by adding time intervals to the start date, if the end date is not fixed
	 *
	 * @return array|null array with 2 keys:
	 *      date - a date formatted with date('j F Y')
	 *      time - time in the format H:i (no seconds)
	 */
	public function get_global_end_date() {
		if ( empty( $this->settings['end'] ) ) {
			/* this campaign will never end */
			return null;
		}

		if ( is_array( $this->settings['end'] ) ) {
			/* end is already a fixed date */
			$absolute_end_date = $this->settings['end'];
		} else {
			$absolute_end_date = $this->set_after_occurrence_date( $this->settings['rolling_type'], $this->settings['start'], $this->settings['end'] * $this->settings['repeat'] );
		}

		return $absolute_end_date;
	}

	/**
	 * get the start and end date / time for the currently detected interval
	 *
	 * @return array with keys: date, time ( j F Y and H:i )
	 */
	public function get_current_daily_interval() {
		// let's make our variable names shorter so the code is more readable
		$start    = $this->settings['start'];
		$repeat   = $this->settings['repeat'];
		$duration = $this->settings['duration'];

		// check for the difference in days between the start of the campaign and today
		// in order to find the start of the roll interval we're in
		$diff       = floor( ( $this->now() - $this->date( $start ) ) / ( 60 * 60 * 24 ) );
		$int_number = floor( $diff / $repeat );

		// set the start date of this roll interval (for daily this will also be the start of the campaign on each roll)
		$temp            = $this->set_after_occurrence_date( $this->settings['rolling_type'], $start, $int_number * $repeat );
		$interval        = array(
			'start' => array(
				'date' => $temp['date'],
				'time' => $start['time'],
			),
		);
		$interval['end'] = tve_ult_add_to_date( $interval['start'], 0, $duration );

		return $interval;
	}

	/**
	 * get all the applicable weekly intervals that can apply based on the current date
	 *
	 * @return array of intervals ( each interval with start and end date / time, formatted in j F Y and H:i )
	 */
	public function get_current_weekly_intervals() {
		// let's make our variable names shorter so the code is more readable
		$start  = $this->settings['start'];
		$repeat = $this->settings['repeat'];

		// get the first day of the week before the start date so we can do the week difference
		$start_date = date( 'w', strtotime( $start['date'] ) );

		//set the fake start date of the campaign
		$week_start['date'] = date( 'j F Y', strtotime( $start['date'] . '-' . $start_date . ' days' ) );
		$week_start['time'] = $start['time'];

		//get the number of whole campaigns that rolled until today (weeks + repeat every x weeks)
		$diff       = floor( ( $this->now() - $this->date( $week_start ) ) / ( 60 * 60 * 24 * 7 ) );
		$int_number = floor( $diff / $repeat );

		// set the start date of this roll interval
		$temp              = $this->set_after_occurrence_date( $this->settings['rolling_type'], $week_start, $int_number * $repeat );
		$int_start['date'] = $temp['date'];
		$int_start['time'] = $this->settings['start']['time'];

		//construct an array of all weekdays where the campaign should show
		$display_intervals = $this->get_display_days( $int_start );

		// check if the repeat is 1, and it's not the first week
		// if we're in our first week this could cause erroneous displays of the campaign
		if ( $repeat === 1 && $int_start !== $week_start ) {
			// check if any end interval is in the next week
			foreach ( $display_intervals as $day_id => $interval ) {
				$partial_start = date( 'w', strtotime( $interval['start']['date'] ) );
				$partial_end   = date( 'w', strtotime( $interval['end']['date'] ) );

				// if the start day is smaller and end week are not the same then the end
				// is in the next month and should be shown at the beginning of
				// this month too because we're already in the second month
				if ( $partial_start >= $partial_end ) {
					// create the new array with the correct interval
					$display_intervals['new']['start']['date'] = date( 'j F Y', strtotime( 'last sunday', strtotime( $interval['start']['date'] ) ) );
					$display_intervals['new']['start']['time'] = '00:00';
					$display_intervals['new']['end']['date']   = date( 'j F Y', strtotime( $interval['end']['date'] . ' -1 week' ) );
					$display_intervals['new']['end']['time']   = $interval['end']['time'];
				}
			}
		}

		return $display_intervals;
	}

	/**
	 * get all the applicable monthly intervals that can apply based on the current date
	 *
	 * @return array of intervals ( each interval with start and end date / time, formatted in j F Y and H:i )
	 */
	public function get_current_monthly_intervals() {
		$start  = $this->settings['start'];
		$repeat = $this->settings['repeat'];

		// get the first day of the month before the start date so we can do the month difference
		$start_date = date( 'j', strtotime( $start['date'] ) );

		//set the fake start date of the campaign
		$month_start['date'] = date( '1 F Y', strtotime( $start['date'] ) );
		$month_start['time'] = $start['time'];

		//get the number of whole campaigns that rolled until today (moths + repeat every x months)
		$start_date = $this->date( $month_start );
		$now        = $this->now();

		$i = 0;

		while ( ( $start_date = strtotime( '+1 MONTH', $start_date ) ) <= $now ) {
			$i ++;
		}
		$int_number = floor( $i / $repeat );

		// set the start date of this roll interval
		$temp              = $this->set_after_occurrence_date( $this->settings['rolling_type'], $month_start, $int_number * $repeat );
		$int_start['date'] = $temp['date'];
		$int_start['time'] = $start['time'];

		//construct an array of all weekdays where the campaign should show
		$display_intervals = $this->get_display_days( $int_start );

		// check if the repeat is 1, and it's not the first month
		// if we're in our first month this could cause erroneous displlays of the campaign
		if ( $repeat === 1 && $int_start !== $month_start ) {
			// check if any end interval is in the next month
			foreach ( $display_intervals as $day_id => $interval ) {
				$partial_start = date( 'F', strtotime( $interval['start']['date'] ) );
				$partial_end   = date( 'F', strtotime( $interval['end']['date'] ) );

				// if the start month and end month are not the same then the end
				// is in the next month and should be shown at the beginning of
				// this month too because we're already in the second month
				if ( $partial_start !== $partial_end ) {
					// create the new array with the correct interval
					$display_intervals['new']['start']['date'] = date( '1 F Y', strtotime( $interval['start']['date'] ) );
					$display_intervals['new']['start']['time'] = '00:00';
					$display_intervals['new']['end']['date']   = date( 'j F Y', strtotime( $interval['end']['date'] . ' -1 Month' ) );
					$display_intervals['new']['end']['time']   = $interval['end']['time'];

				}
			}
		}

		return $display_intervals;
	}

	/**
	 * get the start and end date / time for the currently detected interval
	 *
	 * @return array with keys: date, time ( j F Y and H:i )
	 */
	public function get_current_yearly_interval() {
		$start    = $this->settings['start'];
		$repeat   = $this->settings['repeat'];
		$duration = $this->settings['duration'];

		// check for the difference in years between the start of the campaign and today
		// in order to find the start of the roll interval we're in
		$diff       = floor( ( $this->now() - $this->date( $start ) ) / ( 60 * 60 * 24 * 365 ) );
		$int_number = floor( $diff / $repeat );

		// set the start date of this roll interval (for daily this will also be the start of the campaign on each roll)
		$temp              = $this->set_after_occurrence_date( $this->settings['rolling_type'], $start, $int_number * $repeat );
		$int_start['date'] = $temp['date'];
		$int_start['time'] = $start['time'];

		// define the date where the campaign should stop showing in this rol;
		$int_end = tve_ult_add_to_date( $int_start, $duration );

		return array(
			'start' => $int_start,
			'end'   => $int_end,
		);
	}

	/**
	 * Check if the daily campaign should show
	 *
	 * @return bool
	 */
	public function check_repeat_daily() {
		$interval = $this->get_current_daily_interval();

		// check if we're in the interval if we are we'll show the campaign
		return $this->is_future( $interval['end'] ) && $this->is_past( $interval['start'] );
	}

	/**
	 * Check if the weekly campaign should show
	 *
	 * @return bool
	 */
	public function check_repeat_weekly() {
		$display_intervals = $this->get_current_weekly_intervals();

		// check if today is in any of the display intervals
		foreach ( $display_intervals as $interval ) {
			if ( $this->is_future( $interval['end'] ) && $this->is_past( $interval['start'] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if the monthly campaign should show
	 *
	 * @return bool
	 */
	public function check_repeat_monthly() {
		$display_intervals = $this->get_current_monthly_intervals();
		// check if today is in any of the display intervals
		foreach ( $display_intervals as $interval ) {
			if ( $this->is_future( $interval['end'] ) && $this->is_past( $interval['start'] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if the yearly campaign should show
	 *
	 * @return bool
	 */
	public function check_repeat_yearly() {
		$interval = $this->get_current_yearly_interval();

		// check if we're in the interval if we are we'll show the campaign
		return $this->is_future( $interval['end'] ) && $this->is_past( $interval['start'] );
	}

	/**
	 * Set the end date accordingly taking in consideration the rolling type
	 *
	 * @param string $rolling    campaign type
	 * @param array  $date       with 2 indexes [date,time]
	 * @param int    $occurrence in days|weeks|months|years
	 *
	 * @return mixed
	 */
	public function set_after_occurrence_date( $rolling, $date, $occurrence ) {
		switch ( $rolling ) {
			//append number of days
			case TVE_Ult_Const::CAMPAIGN_ROLLING_TYPE_DAILY:
				$date['date'] = date( 'j F Y', strtotime( $date['date'] . '  ' . $date['time'] . ' + ' . $occurrence . ' days' ) );
				break;
			//append number of weeks
			case TVE_Ult_Const::CAMPAIGN_ROLLING_TYPE_WEEKLY:
				$date['date'] = date( 'j F Y', strtotime( $date['date'] . '  ' . $date['time'] . ' + ' . $occurrence . ' weeks' ) );
				break;
			//append number of months
			case TVE_Ult_Const::CAMPAIGN_ROLLING_TYPE_MONTHLY:
				$date['date'] = date( 'j F Y', strtotime( $date['date'] . '  ' . $date['time'] . ' + ' . $occurrence . ' months' ) );
				break;
			//append number of years
			case TVE_Ult_Const::CAMPAIGN_ROLLING_TYPE_YEARLY:
				$date['date'] = date( 'j F Y', strtotime( $date['date'] . '  ' . $date['time'] . ' + ' . $occurrence . ' years' ) );
				break;
		}

		return $date;
	}

	/**
	 * Get the days on which the campaign should show
	 *
	 * @return array
	 */
	public function get_display_days( $start_date ) {
		$dates = array();
		foreach ( $this->settings['repeatOn'] as $day ) {
			$dates[ $day ]['start'] = tve_ult_add_to_date( $start_date, $day );
			$dates[ $day ]['end']   = tve_ult_add_to_date( $start_date, $day + $this->settings['duration'] );
		}

		return $dates;
	}

	/**
	 * Returns the number of hours remained until the campaign/interval ends
	 *
	 * @return int
	 */
	public function hours_until_end() {

		$method = "get_{$this->settings['rolling_type']}_end_date";
		$now    = date( 'Y-m-d H:i', $this->now() );

		if ( method_exists( $this, $method ) ) {
			$end = call_user_func( array( $this, $method ) );
		} else {
			$end = date( 'Y-m-d H:00:00', $this->now() );
		}

		$diff = tve_ult_date_diff( $now, $end );

		return ( $diff['days'] * 24 ) + $diff['hours'];
	}

	/**
	 * Adds duration hours to start date and returns the string date
	 *
	 * @return bool|string
	 */
	public function get_daily_end_date() {
		$start_date = date( 'Y-m-d' );
		$start_hour = $this->settings['start']['time'];
		$start      = tve_ult_pre_format_date( $start_date, $start_hour );

		return date( 'Y-m-d H:i:s', strtotime( $start . ' +' . $this->settings['duration'] . ' hours' ) );
	}

	/**
	 * Adds duration days to start date and returns the string date
	 *
	 * @return string
	 */
	public function get_yearly_end_date() {
		// check for the difference in years between the start of the campaign and today
		// in order to find the start of the roll interval we're in
		$diff       = floor( ( $this->now() - $this->date( $this->settings['start'] ) ) / ( 60 * 60 * 24 * 365 ) );
		$int_number = floor( $diff / $this->settings['repeat'] );

		// set the start date of this roll interval (for daily this will also be the start of the campaign on each roll)
		$temp              = $this->set_after_occurrence_date( $this->settings['rolling_type'], $this->settings['start'], $int_number * $this->settings['repeat'] );
		$int_start['date'] = $temp['date'];
		$int_start['time'] = $this->settings['start']['time'];

		$start        = tve_ult_pre_format_date( $int_start['date'], $int_start['time'] );
		$interval_end = date( 'Y-m-d H:i:s', strtotime( $start . ' +' . $this->settings['duration'] . ' days' ) );

		if ( is_array( $this->settings['end'] ) ) {
			$end_date = tve_ult_pre_format_date( $this->settings['end']['date'], $this->settings['end']['time'] );
		}

		return isset( $end_date ) && $end_date < $interval_end ? ( $end_date . ':00' ) : $interval_end;
	}

	/**
	 * Calculates the end date of the selected interval
	 * If the current date is not within any interval
	 * NOW() is returned. This will make the last event occur
	 *
	 * @return string
	 */
	public function get_weekly_end_date() {

		$intervals = $this->get_current_weekly_intervals();

		$now = $this->now();

		/**
		 * Foreach possible display intervals - find the one that applies to the current time
		 * If now is between the interval that means
		 * we have to calculate how many hours we have to until
		 * the current interval ends.
		 * Based on that we select the current event.
		 */
		foreach ( $intervals as $possible_option ) {
			$interval_start = $this->date( $possible_option['start'] );
			$interval_end   = $this->date( $possible_option['end'] );
			if ( $interval_start <= $now && $now < $interval_end ) {
				$interval = array(
					'start' => date( 'Y-m-d H:i', $interval_start ),
					'end'   => date( 'Y-m-d H:i:00', $interval_end ),
				);
			}
		}

		if ( ! isset( $interval ) ) {
			/**
			 * this will make the last event to be selected because the events
			 * are ordered by duration asc and last event has smallest duration
			 *
			 */
			return date( 'Y-m-d H:00', $this->now() );
		}

		if ( is_array( $this->settings['end'] ) ) {
			$end_date = tve_ult_pre_format_date( $this->settings['end']['date'], $this->settings['end']['time'] );
		}

		return isset( $end_date ) && $end_date < $interval['end'] ? ( $end_date . ':00' ) : $interval['end'];
	}

	/**
	 * Calculates the end date of the selected interval
	 * If the current date is not within any interval
	 * NOW() is returned. This will make the last event occur
	 *
	 * @return string
	 */
	public function get_monthly_end_date() {
		$campaign_start = date( 'Y-m-01 ' . $this->settings['start']['time'] . ':00', $this->now() );

		foreach ( $this->settings['repeatOn'] as $day ) {
			$interval_start = date( 'Y-m-d H:i', strtotime( $campaign_start . ' +' . ( $day ) . ' days' ) );
			$interval_end   = date( 'Y-m-d H:i:00', strtotime( $interval_start . ' +' . (int) $this->settings['duration'] . ' days' ) );
			if ( strtotime( $interval_start ) <= $this->now() && $this->now() < strtotime( $interval_end ) ) {
				$interval = array(
					'start' => $interval_start,
					'end'   => $interval_end,
				);
				break;
			}
		}

		if ( ! isset( $interval ) ) {
			/**
			 * this will make the last event to be selected because the events
			 * are ordered by duration asc and last event has smallest duration
			 */
			return date( 'Y-m-d H:00:00', $this->now() );
		}

		if ( is_array( $this->settings['end'] ) ) {
			$end_date = tve_ult_pre_format_date( $this->settings['end']['date'], $this->settings['end']['time'] );
		}

		return isset( $end_date ) && $end_date < $interval['end'] ? ( $end_date . ':00' ) : $interval['end'];
	}

	/**
	 * get the end date of the current campaign instance (interval)
	 *
	 * should return a date in the format: Y-m-d H:i:s
	 *
	 * @return string
	 */
	public function get_end_date() {
		$method = "get_{$this->settings['rolling_type']}_end_date";
		if ( empty( $this->settings['rolling_type'] ) || ! method_exists( $this, $method ) ) {
			return '';
		}

		return call_user_func( array( $this, $method ) );
	}

	/**
	 * @inheritdoc
	 */
	public function get_duration() {
		$duration = 0;

		switch ( $this->settings['rolling_type'] ) {
			case TVE_Ult_Const::CAMPAIGN_ROLLING_TYPE_DAILY:
				$duration = $this->settings['duration'];
				break;
			case TVE_Ult_Const::CAMPAIGN_ROLLING_TYPE_WEEKLY:
			case TVE_Ult_Const::CAMPAIGN_ROLLING_TYPE_MONTHLY:
			case TVE_Ult_Const::CAMPAIGN_ROLLING_TYPE_YEARLY:
				$duration = $this->settings['duration'] * 24;
				break;
		}

		return $duration;
	}

	/**
	 * checks performed for a daily campaign - pre access page
	 *
	 * @return bool
	 */
	protected function should_redirect_pre_access_daily() {
		$interval = $this->get_current_daily_interval();

		/* if the currently detected interval has ended, this means that there is at least one more interval (global settings are checked in a previous step) */

		return $this->is_past( $interval['end'] );
	}

	/**
	 * checks performed for a weekly campaign - pre-access
	 *
	 * @return bool
	 */
	protected function should_redirect_pre_access_weekly() {
		/**
		 * pre-access redirection is achieved when the campaign did not end and it currently does not apply
		 */
		if ( $this->should_redirect_expired() || $this->applies() ) {
			return false;
		}

		return true;
	}

	/**
	 * checks performed for a monthly campaign
	 *
	 * @return bool
	 */
	protected function should_redirect_pre_access_monthly() {
		/**
		 * use the expired and applies functionalities for this
		 */
		if ( $this->should_redirect_expired() || $this->applies() ) {
			return false;
		}

		return true;
	}

	/**
	 * checks for pre-access redirects for yearly campaigns
	 *
	 * @return bool
	 */
	protected function should_redirect_pre_access_yearly() {

		/**
		 * use the expires and applies functionalities
		 */
		if ( $this->should_redirect_expired_yearly() || $this->applies() ) {
			return false;
		}

		return true;
	}

	/**
	 * checks if a weekly campaign is completely ended at this point
	 *
	 * the global end date is not sufficient, because it actually represents the actual and date of the last week in which this campaign occurs
	 */
	public function should_redirect_expired_weekly() {
		$end_date      = $this->get_global_end_date();
		$end_timestamp = $this->date( $end_date );

		/* if end date is farther in the future than one cycle from now, this campaign is not ended */
		if ( $end_timestamp > $this->now() + ( $this->settings['repeat'] * 7 * DAY_IN_SECONDS ) ) {
			return false;
		}

		/* if the campaign ends a less than one cycle from now and if all intervals have ended, the campaign is finished */
		foreach ( $this->get_current_weekly_intervals() as $interval ) {
			if ( $this->is_future( $interval['end'] ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * checks performed for a monthly campaign expired page redirection
	 *
	 * @return bool
	 */
	public function should_redirect_expired_monthly() {
		$end_date = $this->get_global_end_date();

		if ( $this->is_past( $end_date ) ) {
			return true;
		}
		$end_timestamp = strtotime( tve_ult_pre_format_date( $end_date['date'], $end_date['time'] ) . " -{$this->settings['repeat']} months" );

		/* if end date is farther in the future than one cycle from now, this campaign is not ended */
		if ( $end_timestamp > $this->now() ) {
			return false;
		}

		/* if we have at least one interval that has not ended yet between now and the end date, the campaign is not expired */
		foreach ( $this->get_current_monthly_intervals() as $interval ) {
			if ( $this->is_future( $interval['end'] ) && $this->date( $interval['end'] ) < $this->date( $end_date ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * checks performed for redirects to the expired page of a yearly campaign
	 *
	 * @return bool
	 */
	protected function should_redirect_expired_yearly() {
		$end_date      = $this->get_global_end_date();
		$end_timestamp = strtotime( tve_ult_pre_format_date( $end_date['date'], $end_date['time'] ) . " -{$this->settings['repeat']} years" );

		/* if end date is farther in the future than one cycle from now, this campaign is not ended */
		if ( $end_timestamp > $this->now() ) {
			return false;
		}
		/* if we have at least one interval that has not ended yet between now and the end date, the campaign is not expired */
		$interval = $this->get_current_yearly_interval();
		if ( $this->is_future( $interval['end'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * the redirection to the pre-access page should happen:
	 *
	 * a) when the campaign has not yet started
	 * b) when a cycle is finished and another cycle is yet to begin
	 *
	 * @return bool
	 */
	public function should_redirect_pre_access() {
		/* first, some global checks */
		if ( $this->is_future( $this->settings['start'] ) ) {
			return true;
		}
		if ( isset( $_COOKIE[ $this->cookie_end_name() ] ) ) {
			return false;
		}
		/* if the campaign has ended, return false */
		if ( null !== ( $end_date = $this->get_global_end_date() ) && ! $this->is_future( $end_date ) ) {
			return false;
		}

		$method = 'should_redirect_pre_access_' . $this->settings['rolling_type'];
		if ( ! method_exists( $this, $method ) ) {
			return false;
		}

		return $this->{$method}();
	}

	/**
	 * checks performed for a daily campaign - expired page
	 *
	 * the redirection to the expired page should only happen after the campaign has ended
	 *
	 * @return bool
	 */
	public function should_redirect_expired() {
		/* first, some global checks */
		if ( $this->is_future( $this->settings['start'] ) ) {
			return false;
		}

		if ( isset( $_COOKIE[ $this->cookie_end_name() ] ) ) {
			return true;
		}

		/*
		 * short-circuit if the campaign never ends
		 */
		if ( empty( $this->settings['end'] ) ) {
			return false;
		}

		/* if the campaign has ended, return false */
		if ( null !== ( $end_date = $this->get_global_end_date() ) && $this->is_past( $end_date ) ) {
			return true;
		}

		$method = 'should_redirect_expired_' . $this->settings['rolling_type'];
		if ( method_exists( $this, $method ) ) {
			return $this->{$method}();
		}

		return false;
	}
}
