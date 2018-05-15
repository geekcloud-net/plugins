<?php

class TU_Event_Action {

	/**
	 * If you change this keys make sure you review the Action Model validation
	 */
	const DESIGN_SHOW = 'design_show';
	const DESIGN_SWITCH_STATE = 'design_switch_state';
	const CAMPAIGN_END = 'campaign_end';
	const CAMPAIGN_MOVE = 'campaign_move';

	/**
	 * @var TU_Event_Action
	 */
	protected static $instance;

	/**
	 * Private to make sure of its singleton pattern
	 * TU_Event_Action constructor.
	 */
	private function __construct() {
	}

	/**
	 * Returns the params from $_POST or $_REQUEST
	 *
	 * @param $key
	 * @param null $default
	 *
	 * @return mixed|null|$default
	 */
	protected function param( $key, $default = null ) {
		return isset( $_POST[ $key ] ) ? $_POST[ $key ] : ( isset( $_REQUEST[ $key ] ) ? $_REQUEST[ $key ] : $default );
	}

	/**
	 * @return TU_Event_Action
	 */
	public static function getInstance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public static function get_keys() {
		return array(
			self::DESIGN_SHOW,
			self::DESIGN_SWITCH_STATE,
			self::CAMPAIGN_END,
			self::CAMPAIGN_MOVE,
		);
	}

	/**
	 * @return array with non-associative indexes with all actions
	 */
	public static function get_model_list() {
		$models = array();

		foreach ( self::get_detailed_list() as $key => $prop ) {
			$prop['key'] = $key;
			$models[]    = $prop;
		}

		return $models;
	}

	public static function get_detailed_list() {
		return array(
			self::DESIGN_SHOW => array(
				'key'         => self::DESIGN_SHOW,
				'name'        => __( 'Display Countdown Design', TVE_Ult_Const::T ),
				'description' => __( 'Displays a countdown design. If the design has multiple states, you can also choose a design state', TVE_Ult_Const::T ),
			),
//			self::DESIGN_SWITCH_STATE => array(
//				'key'         => self::DESIGN_SWITCH_STATE,
//				'name'        => __( 'Switch Countdown Design State', TVE_Ult_Const::T ),
//				'description' => __( 'Simply allows the user to switch from one state to another.', TVE_Ult_Const::T ),
//			),
//			self::CAMPAIGN_END        => array(
//				'key'         => self::CAMPAIGN_END,
//				'name'        => __( 'End Current Campaign', TVE_Ult_Const::T ),
//				'description' => __( 'Completely ends the Campaign so that no more triggers and actions are performed in the current timeline.', TVE_Ult_Const::T ),
//			),
//			self::CAMPAIGN_MOVE       => array(
//				'key'         => self::CAMPAIGN_MOVE,
//				'name'        => __( 'Move to new campaign', TVE_Ult_Const::T ),
//				'description' => __( 'Ends the current campaign and starts a new one.', TVE_Ult_Const::T ),
//			),
		);
	}

	/**
	 * Gets details of an action based on an action_key
	 *
	 * @param $action_key
	 *
	 * @return null
	 */
	public static function get_details( $action_key ) {
		if ( ! in_array( $action_key, self::get_keys() ) ) {
			return null;
		}

		$actions = self::get_detailed_list();

		return $actions[ $action_key ];
	}

	/**
	 * Gets the html options for a specific action
	 * This html is used when a user wants to add/edit an event and selects an action
	 *
	 * @param string|array $action
	 *
	 * @return string html
	 */
	public function get_options_html( $action ) {

		if ( ! is_array( $action ) ) {
			$action['key'] = $action;
		}

		if ( empty( $action['key'] ) || ! in_array( $action['key'], self::get_keys() ) ) {
			return '';
		}

		$method = "{$action['key']}_options";

		if ( ! method_exists( $this, $method ) ) {
			return $this->default_options();
		}

		return call_user_func( array( $this, $method ), $action );
	}

	protected function default_options() {
		return $this->view( 'default' );
	}

	/**
	 * Gets the options html for action
	 *
	 * @param array $action optional
	 *
	 * @return string html
	 */
	protected function design_show_options( $action = array() ) {
		$results = tve_ult_get_designs_and_states( $action['campaign'], array( 'id', 'post_title' ) );
		$designs = $results['designs'];

		$action       = array_merge( self::get_details( $action['key'] ), $action );
		$used_actions = $this->param( 'used_actions' );

		if ( ! isset( $event['actions'] ) ) {
			$event['actions'] = array();
		}

		$all_used = false;
		if ( $this->param( 'mode' ) === 'AddMode' && count( $designs ) === count( $event['actions'] ) ) {
			$all_used = true;
		}

		/**
		 * Assure unique design action per event
		 * (cannot be possible to have more actions that show the same design)
		 */
		if ( is_array( $used_actions ) && ! empty( $used_actions ) ) {

			foreach ( $results['designs'] as $key => $design ) {
				foreach ( $used_actions as $used ) {

					if ( isset( $action['design'] ) && $action['design'] == $design['id'] ) {
						continue;
					}

					if ( $used['design'] == $design['id'] ) {
						unset( $designs[ $key ] );
					}
				}
			}
		}

		return $this->view( self::DESIGN_SHOW, array(
			'designs'  => $designs,
			'states'   => json_encode( $results['states'] ),
			'action'   => $action,
			'all_used' => $all_used,
		) );
	}

	/**
	 * Gets the options html for action
	 *
	 * @param array $action optional
	 *
	 * @return string html
	 */
	protected function design_hide_options( $action = array() ) {
		$action = array_merge( self::get_details( $action['key'] ), $action );

		return $this->view( self::DESIGN_HIDE, array(
			'designs' => tve_ult_get_designs( $_REQUEST['campaign_id'] ),
			'action'  => $action,
		) );
	}

	/**
	 * Gets the options html for action
	 *
	 * @param array $action optional
	 *
	 * @return string html
	 */
	protected function campaign_move_options( $action = array() ) {
		$campaigns = tve_ult_get_campaigns( array( 'get_settings' => false ) );

		$action = array_merge( self::get_details( $action['key'] ), $action );

		return $this->view( self::CAMPAIGN_MOVE, array(
			'campaigns' => $campaigns,
			'action'    => $action,
		) );
	}

	/**
	 * Gets the options html for action
	 *
	 * @param array $action optional
	 *
	 * @return string html
	 */
	protected function campaign_end_options( $action = array() ) {
		$action = array_merge( self::get_details( $action['key'] ), $action );

		return $this->view( self::CAMPAIGN_END, array(
			'action' => $action,
		) );
	}

	/**
	 * Gets the options html for action
	 *
	 * @param array $action optional
	 *
	 * @return string html
	 */
	protected function design_switch_state_options( $action = array() ) {
		$results = tve_ult_get_designs_and_states( $_REQUEST['campaign_id'], array( 'id', 'post_title' ) );
		$action  = array_merge( self::get_details( $action['key'] ), $action );

		return $this->view( self::DESIGN_SWITCH_STATE, array(
			'designs' => $results['designs'],
			'states'  => json_encode( $results['states'] ),
			'action'  => $action,
		) );
	}

	/**
	 * Renders a view and returns its html
	 * based on $data
	 *
	 * @param $view
	 * @param array $data
	 *
	 * @return string
	 */
	protected function view( $view, $data = array() ) {
		$file = TVE_Ult_Const::plugin_path( "admin/views/action/options/" . $view . ".phtml" );

		if ( ! file_exists( $file ) ) {
			return '';
		}

		extract( $data );

		ob_start();
		include $file;
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}
}
