<?php

class TU_Timeline {

	/**
	 * @var WP_Post with few more extra fields added
	 * @see tve_ult_get_campaign
	 */
	protected $campaign;

	/**
	 * TU_Timeline constructor.
	 *
	 * @param WP_Post $campaign
	 */
	public function __construct( $campaign ) {
		$this->campaign = $campaign;
	}

	public function prepare_events() {
		if ( empty( $this->campaign->settings ) || empty( $this->campaign->type )
		     || ! in_array( $this->campaign->type, TVE_Ult_Const::campaign_types() )
		) {
			return;
		}

		if ( isset( $this->campaign->timeline[0] ) && $this->campaign->timeline[0]['type'] === TVE_Ult_Const::EVENT_TYPE_START ) {
			$this->campaign->timeline[0]['name'] = __( 'Campaign Start', TVE_Ult_Const::T );
		}

		array_push( $this->campaign->timeline, array(
			'name'        => __( 'End Campaign', TVE_Ult_Const::T ),
			'campaign_id' => $this->campaign->ID,
			'days'        => 0,
			'hours'       => 0,
			'is_end'      => true,
			'type'        => TVE_Ult_Const::EVENT_TYPE_END,
			'label'       => '0',
		) );
	}
}
