<?php

/**
 * Created by PhpStorm.
 * User: radu
 * Date: 06.02.2016
 * Time: 15:05
 */
class TU_Schedule_Absolute extends TU_Schedule_Abstract {

	/**
	 * check if the campaign should be displayed
	 */
	public function applies() {
		//check if end cookie is set for this campaign
		if ( isset( $_COOKIE[ $this->cookie_end_name() ] ) ) {
			return false;
		}

		if ( $this->is_future( $this->settings['end'] ) && $this->is_past( $this->settings['start'] ) ) {
			// check if any conversion event meet the criteria and end the campaign if it does so
			if ( ! empty( $this->conversion_events ) ) {
				foreach ( $this->conversion_events as $event ) {
					$trigger = $this->check_triggers( $event['trigger_options'] );

					if ( ! $trigger ) {
						return false;
					}
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Returns the number of hours remained until the campaign/interval ends
	 *
	 * @return int
	 */
	public function hours_until_end() {
		$now  = date( 'Y-m-d H:i', $this->now() );
		$end  = date( 'Y-m-d H:i', $this->date( $this->settings['end'] ) );
		$diff = tve_ult_date_diff( $now, $end );

		return ( $diff['days'] * 24 ) + $diff['hours'];
	}

	/**
	 * get the end date of the current campaign instance (interval)
	 *
	 * should return a date in the format: Y-m-d H:i:s
	 *
	 * @return string
	 */
	public function get_end_date() {

		return date( 'Y-m-d H:i:00', $this->date( $this->settings['end'] ) );
	}

	/**
	 * @inheritdoc
	 */
	public function get_duration() {
		$start_date = tve_ult_pre_format_date( $this->settings['start']['date'], $this->settings['start']['time'] );
		$end_date   = tve_ult_pre_format_date( $this->settings['end']['date'], $this->settings['end']['time'] );
		$diff       = tve_ult_date_diff( $start_date, $end_date );

		return $diff['days'] * 24 + $diff['hours'];
	}

	/**
	 * checks if the campaign is not yet started
	 *
	 * @return bool
	 */
	public function should_redirect_pre_access() {

		if ( isset( $_GET['tu_id'] ) && $_GET['tu_id'] != $this->campaign_id ) {
			return false;
		}

		return $this->is_future( $this->settings['start'] );
	}

	/**
	 * checks if the campaign has ended - either the end date is in the past, or this campaign has ended due to a conversion event
	 *
	 * @return bool
	 */
	public function should_redirect_expired() {
		if ( isset( $_COOKIE[ $this->cookie_end_name() ] ) ) {
			return true;
		}

		return $this->is_past( $this->settings['end'] );
	}
}
