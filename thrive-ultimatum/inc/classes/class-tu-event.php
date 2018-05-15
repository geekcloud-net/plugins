<?php

class TU_Event {

	/**
	 * @var WP_Post
	 */
	protected $campaign;

	public function __construct( $campaign ) {
		$this->campaign = $campaign;
	}

	public function get_designs() {
		$data = $this->get_data();

		if ( empty( $data['actions'] ) ) {
			return array();
		}

		$designs = array();

		foreach ( $data['actions'] as $action ) {
			if ( $action['key'] === TU_Event_Action::DESIGN_SHOW ) {
				$designs[] = tve_ult_get_design( $action['design'] == $action['state'] ? $action['design'] : $action['state'] );
			}
		}

		return $designs;
	}

	/**
	 * Returns the event's data from DB
	 *
	 * @return array|null|object|void
	 */
	protected function get_data() {
		$hours = $this->campaign->tu_schedule_instance->hours_until_end();

		global $tve_ult_db;

		$data = $tve_ult_db->get_closest_event( $this->campaign->ID, $hours );

		if ( $data ) {
			$data['actions'] = unserialize( $data['actions'] );
		}

		return $data;
	}
}
