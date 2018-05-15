<?php

/**
 * Created by PhpStorm.
 * User: radu
 * Date: 08.02.2016
 * Time: 10:24
 */
class TU_Schedule_Evergreen extends TU_Schedule_Abstract {

	public function applies() {

		$apply = false;

		// check if we have a cookie set
		if ( ! isset( $_COOKIE[ $this->cookie_name( $this->campaign_id ) ] ) ) {

			// set the start date of the campaign
			$start_date['date'] = date( 'j F Y', $this->now() );
			$start_date['time'] = date( 'H:i:s', $this->now() );

			$value = array(
				'start_date' => $start_date,
			);
			$value = maybe_serialize( $value );

			if ( empty( $this->settings['evergreen_repeat'] ) ) {
				$expire = strtotime( '2038-01-01 ' );
			} else {
				$end_after = $this->settings['end'] + $this->settings['duration'];
				$expire    = $this->calc_real_end_time( strtotime( '+' . $end_after . ' days', $this->date( $start_date ) ) );
			}

			//check the cookie trigger
			if ( $this->settings['trigger']['type'] === TVE_Ult_Const::TRIGGER_TYPE_FIRST_VISIT ) {
				//set the cookie
				$apply = $this->setCookie( $value, $expire );
			}

			if ( $this->settings['trigger']['type'] === TVE_Ult_Const::TRIGGER_TYPE_PAGE_VISIT ) {
				// get the current post ID, we need to check if the trigger matches
				$post_id = $this->param( 'post_id' );

				if ( intval( $post_id ) === $this->settings['trigger']['ids'] ) {
					//set the cookie
					$apply = $this->setCookie( $value, $expire );

				}
			}

			/**
			 * if there is a campaign's cookie then we need to read it and update the email log's {end} prop with {0}
			 * mark it as non-ended
			 */
			$this->update_email_log( array(), array( 'end' => 0 ) );
		}

		//check if end cookie is set for this campaign
		if ( isset( $_COOKIE[ $this->cookie_end_name() ] ) ) {
			return false;
		}

		//get the start date from cookie
		$data = $this->get_cookie_data();

		// check if the campaign should be shown
		if ( isset( $data['end_date'] ) && isset( $data['start_date'] ) && $this->is_future( $data['end_date'] ) && $this->is_past( $data['start_date'] ) ) {
			// check if any conversion event meet the criteria and end the campaign if it does so
			if ( ! empty( $this->conversion_events ) ) {
				foreach ( $this->conversion_events as $event ) {
					$trigger = $this->check_triggers( $event['trigger_options'] );

					if ( ! $trigger ) {
						return false;
					}
				}
			}

			$apply = true;
		}

		return $apply;
	}

	/**
	 * Checks if realTime option is set and
	 * adds calculates the real time
	 *
	 * @param int $end_time
	 *
	 * @return int timestamp
	 */
	public function calc_real_end_time( $end_time ) {

		if ( empty( $this->settings['real'] ) ) {
			return $end_time;
		}

		$realtime = empty( $this->settings['realtime'] ) ? '00:00' : $this->settings['realtime'];

		//for the expiration date set the real time
		$real = date( 'Y-m-d', $end_time ) . ' ' . $realtime . ':00';
		$real = strtotime( $real );
		//if the real is behind expiration add +1 day
		if ( $real < $end_time ) {
			$real = strtotime( '+1day', $real );
		}

		return $real;
	}

	/**
	 * Un-serializes and read in the cookie data
	 *
	 * @return array
	 */
	public function get_cookie_data() {
		if ( ! isset( $_COOKIE[ $this->cookie_name( $this->campaign_id ) ] ) ) {
			return array();
		}
		$value      = maybe_unserialize( stripcslashes( $_COOKIE[ $this->cookie_name( $this->campaign_id ) ] ) );
		$start_date = tve_ult_pre_format_date( $value['start_date']['date'], $value['start_date']['time'] );

		$end_date = date( 'Y-m-d H:i', strtotime( $start_date . ' +' . $this->settings['duration'] . ' days' ) );
		$end_date = date( 'Y-m-d H:i:s', $this->calc_real_end_time( strtotime( $end_date ) ) );

		return array(
			'start_date' => $start_date,
			'end_date'   => $end_date,
			'cookie'     => $value,
		);
	}

	/**
	 * Updates the email log set in campaign's COOKIE
	 * By default sets the {end} property to {1}
	 *
	 * If email_log_id is not saved in COOKIE then nothing happens
	 *
	 * @param array $trigger_options
	 * @param array $options
	 *
	 * @return false|int
	 */
	public function update_email_log( $trigger_options = array(), $options = array() ) {

		if ( ! isset( $_COOKIE[ $this->cookie_name() ] ) ) {
			return false;
		}

		$data = $this->get_cookie_data();

		if ( ! isset( $data['cookie']['lockdown'] ) || empty( $data['cookie']['lockdown']['log_id'] ) ) {
			return false;
		}

		$updates = array(
			'end' => 1,
		);

		$updates = array_merge( $updates, $options );

		global $tve_ult_db;

		$model = $tve_ult_db->get_email_log_by_id( $data['cookie']['lockdown']['log_id'] );
		$model = array_merge( $model, $updates );

		return $tve_ult_db->save_email_log( $model );
	}

	/**
	 * Returns the number of hours remained until the campaign/interval ends
	 *
	 * @return int
	 */
	public function hours_until_end() {

		if ( ! isset( $_COOKIE[ $this->cookie_name( $this->campaign_id ) ] ) ) {
			return $this->now();
		}

		$value      = maybe_unserialize( stripcslashes( $_COOKIE[ $this->cookie_name( $this->campaign_id ) ] ) );
		$start_date = tve_ult_pre_format_date( $value['start_date']['date'], $value['start_date']['time'] );

		$now = date( 'Y-m-d H:i', $this->now() );
		$end = date( 'Y-m-d H:i', strtotime( $start_date . ' +' . $this->settings['duration'] . ' days' ) );

		/**
		 * calc the real end time
		 */
		$end = date( 'Y-m-d H:i', $this->calc_real_end_time( strtotime( $end ) ) );

		$diff = tve_ult_date_diff( $now, $end );

		$return = ( $diff['days'] * 24 ) + $diff['hours'];

		return $return;
	}

	/**
	 * get the end date of the current campaign instance (interval)
	 * based on campaign's cookie
	 *
	 * should return a date in the format: Y-m-d H:i:s
	 *
	 * @return string
	 */
	public function get_end_date() {
		if ( ! ( $data = $this->get_cookie_data() ) ) {
			return '';
		}

		// for evergreen campaigns, we need to also take into account the exact second at which the campaign was started
		$start_parts = explode( ':', isset( $data['cookie']['start_date']['time'] ) ? $data['cookie']['start_date']['time'] : '00:00:00' );
		$seconds     = count( $start_parts ) == 2 ? '00' : $start_parts[2];

		$has_seconds = count( explode( ':', $data['end_date'] ) ) === 3;

		$return = $data['end_date'] . ( ! $has_seconds ? ':' . $seconds : '' ); // we also need seconds

		return $return;
	}

	/**
	 * Get the duration
	 *
	 * @return mixed
	 */
	public function get_duration() {
		return $this->settings['duration'] * 24;
	}

	/**
	 * Check if we should redirect to pre-access page or not
	 *
	 * @return bool:
	 *  - true: there is no valid request or there is no cookie
	 *  - false: if there is valid request or the is valid cookie
	 */
	public function should_redirect_pre_access() {
		$valid_request = ! empty( $_GET['tu_id'] ) && ! empty( $_GET['tu_em'] ) && $_GET['tu_id'] == $this->campaign_id && filter_var( $_GET['tu_em'], FILTER_VALIDATE_EMAIL );
		$valid_cookie  = isset( $_COOKIE[ $this->cookie_name() ] );

		if ( $valid_request || $valid_cookie ) {
			/**
			 * do not redirect because we have request or cookie
			 */
			return false;
		}

		/**
		 * redirect because we have no request ether cookie
		 */
		return true;
	}

	/**
	 * if the campaign has started and is currently ended, redirect the user to the expired page
	 *
	 * TODO: this needs more tweaks: I think we need to better identify the end date
	 *
	 * @return bool
	 */
	public function should_redirect_expired() {
		if ( isset( $_COOKIE[ $this->cookie_end_name() ] ) ) {
			return true;
		}

		//we need to check the end date from the db too
		$cookie_end_date = $this->get_end_date();
		if ( empty( $cookie_end_date ) && empty( $_GET['tu_em'] ) ) {
			return true;
		}

		$should_redirect = false;

		$email = isset( $_GET['tu_em'] ) ? $_GET['tu_em'] : null;
		if ( $email_log = tve_ult_get_email_log( $this->campaign_id, $email ) ) {
			$log_end_date = date( 'Y-m-d H:i:s', strtotime( $email_log['started'] . ' +' . $this->settings['duration'] . 'days' ) );
			$log_end_date = $this->calc_real_end_time( strtotime( $log_end_date ) );
			$log_end_date = date( 'Y-m-d H:i:s', $log_end_date );

			$should_redirect = $email_log['end'] == 1 || $this->is_past( $log_end_date );
		}

		$should_redirect = $should_redirect || ! empty( $cookie_end_date ) && $this->is_past( $cookie_end_date );

		if ( $should_redirect && ! isset( $_COOKIE[ $this->cookie_end_name() ] ) ) {
			$this->set_end_cookie();
		}

		return $should_redirect;
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

			$related_campaign = tve_ult_get_campaign( $options['end_id'], array(
				'get_settings' => true,
			) );
			/** @var TU_Schedule_Abstract $related_campaign_schedule */
			$related_campaign_schedule = $related_campaign->tu_schedule_instance;

			// cannot start a campaign if its status is not running
			// check if the campaign didn't already start for this user
			if ( $related_campaign->status === TVE_Ult_Const::CAMPAIGN_STATUS_RUNNING && ! isset( $_COOKIE[ $this->cookie_name( $options['end_id'] ) ] ) ) {

				// get campaign settings
				$settings = get_post_meta( $options['end_id'], TVE_Ult_Const::META_NAME_FOR_CAMPAIGN_SETTINGS, true );

				// set cookie
				$start_date['date'] = date( 'j F Y', $this->now() );
				$start_date['time'] = date( 'H:i:s', $this->now() );
				$value              = maybe_serialize( array( 'start_date' => $start_date ) );

				//set the cookie expiration date
				// there are issues on 32 bit platforms using this: we need to make sure that this is not in the past (something like 1907)
				$expire = strtotime( '+' . ( $settings['end'] + $settings['duration'] ) . ' days', $this->date( $start_date ) );
				$expire = $this->calc_real_end_time( $expire );
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

				/**
				 * for linked lockdown campaign we need to save a email log in DB
				 * in this way when the user triggers the linked campaign in new browser we know him and we know that time to show
				 */
				$data = $this->get_cookie_data();
				if ( ! empty( $data['cookie'] ) && ( $cookie_data = $data['cookie'] ) && ! empty( $cookie_data['lockdown'] ) ) {
					tve_ult_save_email_log( array(
						'campaign_id'    => $related_campaign->ID,
						'email'          => ! empty( $cookie_data['lockdown']['email'] ) ? $cookie_data['lockdown']['email'] : null,
						'started'        => tve_ult_pre_format_date( date( 'j F Y', $this->now() ), date( 'H:i:s', $this->now( false ) ) ),
						'has_impression' => 1,
					) );
				}
			}
		}
		if ( ! isset( $_COOKIE[ $this->cookie_end_name() ] ) ) {
			/**
			 * the conversion can be made only if the campaign is not ended
			 */
			$this->register_conversion();
			$this->set_end_cookie();
		}

		$this->update_email_log( $options );
	}

	/**
	 * Save the email in the db
	 *
	 * @param data
	 *
	 * @return false|int
	 */
	public function save_email_log( $data ) {

		$model['campaign_id'] = $this->campaign_id;
		isset( $data['date'] ) ? $model['started'] = $data['date'] : $model['started'] = date( 'Y-m-d H:i:s', $this->now( false ) );
		isset( $_GET['tu_em'] ) && ! isset( $data['email'] ) ? $model['email'] = $_GET['tu_em'] : $model['email'] = $data['email'];
		if ( isset( $data['type'] ) ) {
			$model['type'] = $data['type'];
		}

		return tve_ult_save_email_log( $model );
	}

	/**
	 * Create the cookie for the user
	 *
	 * @param $value
	 * @param $expire
	 *
	 * @return bool
	 */
	public function setCookie( $value, $expire ) {
		if ( ! headers_sent() ) {
			setcookie( $this->cookie_name( $this->campaign_id ), $value, $expire, '/' );
		}

		if ( $expire <= $this->now( false ) ) {
			unset( $_COOKIE[ $this->cookie_name( $this->campaign_id ) ] );

			return false;
		}

		$_COOKIE[ $this->cookie_name( $this->campaign_id ) ] = $value;

		return true;
	}

	/**
	 * Check if the cookie has the lockdown options
	 *
	 * @return bool
	 */
	public function verify_cookie() {
		$cookie = $this->get_cookie_data();
		if ( ! empty( $cookie['cookie']['lockdown']['email'] ) && ! empty( $cookie['cookie']['lockdown']['log_id'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if we have data in the db and save it, also save the cookie
	 *
	 * @param $params
	 */
	public function set_cookie_and_save_log( $params ) {
		if ( empty( $params['start_date'] ) ) {
			$params['start_date']['date'] = date( 'j F Y', $this->now() );
			$params['start_date']['time'] = date( 'H:i:s', $this->now( false ) );
		}
		$db   = tve_ult_get_email_log( $this->campaign_id, $params['lockdown']['email'] );
		$date = tve_ult_pre_format_date( $params['start_date']['date'], $params['start_date']['time'] );

		/**
		 * Checks performed for a repeating evergreen campaign:
		 * if a repeating evergreen campaign has been shown before to the user and the required amount of time has passed, the campaign should be shown again*
		 * *only if the time setup form "Repeat after" has passed
		 */
		if ( ! empty( $db ) && ! isset( $_COOKIE[ $this->cookie_name() ] ) && ! empty( $this->settings['evergreen_repeat'] ) ) {
			/* check if the required amount of time has passed since the previous campaign has been displayed */
			$end_after_days = (int) $this->settings['end'] + (int) $this->settings['duration'];
			/*
			 * a "cycle" ends when the campaign has ended and the time setup in "Display this campaign again after x days" has also passed
			 */
			$cycle_end = $this->calc_real_end_time( strtotime( '+' . $end_after_days . ' days', $this->date( $db['started'] ) ) );
			if ( $cycle_end < tve_ult_current_time( 'timestamp' ) ) {
				$db['started'] = date( 'Y-m-d H:i:s', $this->now( false ) );
				tve_ult_save_email_log( $db );
			}
		}

		if ( ! isset( $db ) ) {
			$data = array(
				'date'  => $date,
				'email' => $params['lockdown']['email'],
			);
			if ( isset( $params['lockdown']['type'] ) ) {
				$data['type'] = $params['lockdown']['type'];
				unset( $params['lockdown']['type'] );
			}
			$params['lockdown']['log_id'] = $this->save_email_log( $data );
		} else {
			$params['lockdown']['log_id'] = $db['id'];
			if ( $db['started'] != $date ) {
				//split the date
				$params['start_date']['date'] = substr( $db['started'], 0, strpos( $db['started'], ' ' ) );
				$params['start_date']['time'] = strstr( $db['started'], ' ' );
			}
		}

		$data = $this->set_cookie_data( $params );
		unset( $_COOKIE[ $this->cookie_name( $this->campaign_id ) ] );
		$this->setCookie( $data['value'], $data['expire'] );
	}

	/**
	 * Set the cookie for he end of the campaign
	 */
	public function set_end_cookie() {
		if ( empty( $this->settings['evergreen_repeat'] ) ) {
			$expire = strtotime( '2038-01-01 ' );
		} else {
			$end_after_days = $this->settings['end'] + $this->settings['duration'];
			$data           = $this->get_cookie_data();
			$expire         = $this->calc_real_end_time( strtotime( '+' . $end_after_days . ' days', $this->date( $data['cookie']['start_date'] ) ) );
		}

		if ( ! headers_sent() ) {
			setcookie( $this->cookie_end_name(), 'end', $expire, '/' );
			/**
			 * also, unset the "force_campaign" cookie
			 */
			setcookie( $this->cookie_force_display(), '', time() - DAY_IN_SECONDS * 200, '/' );
		}
		$_COOKIE[ $this->cookie_end_name( $this->campaign_id ) ] = 'end';
		unset( $_COOKIE[ $this->cookie_force_display() ] );
	}

	/**
	 * Prepare the cookie data for the setCookie function
	 *
	 * @param string $params
	 *
	 * @return array
	 */
	public function set_cookie_data( $params ) {

		if ( ! isset( $params['start_date'] ) ) {
			// set the start date of the campaign
			$params['start_date']['date'] = date( 'j F Y', $this->now() );
			$params['start_date']['time'] = date( 'H:i:s', $this->now() );
		}

		$value = maybe_serialize( $params );

		if ( empty( $this->settings['evergreen_repeat'] ) ) {
			$expire = strtotime( '2038-01-01 ' );
		} else {
			$end_after = $this->settings['end'] + $this->settings['duration'];
			$expire    = $this->calc_real_end_time( strtotime( '+' . $end_after . ' days', $this->date( $params['start_date'] ) ) );
		}

		return array(
			'value'  => $value,
			'expire' => $expire,
		);
	}

	/**
	 * Extend the is past to check the past time to also be equal to now
	 *
	 * @param string $date
	 *
	 * @return bool
	 */
	public function is_past( $date ) {
		return is_string( $date ) && $this->date( $date ) <= $this->now( false );
	}
}
