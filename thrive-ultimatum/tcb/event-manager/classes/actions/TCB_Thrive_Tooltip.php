<?php

class TCB_Thrive_Tooltip extends TCB_Event_Action_Abstract {

	protected $key = 'thrive_tooltip';
	/**
	 * available tooltip positions
	 *
	 * @var array
	 */
	protected $_positions = array(
			'top'          => 'Top',
			'top_right'    => 'Top right',
			'right'        => 'Right',
			'bottom_right' => 'Bottom right',
			'bottom'       => 'Bottom',
			'bottom_left'  => 'Bottom left',
			'left'         => 'Left',
			'top_left'     => 'Top left',
		);

	/**
	 * available tooltip styles
	 *
	 * @var array
	 */
	protected $_styles = array(
			'light' => 'Light',
			'dark'  => 'Dark',
		);

	/**
	 * available tooltip text decorations
	 *
	 * @var array
	 */
	protected $_decorations	= array(
			'solid'  => 'Solid',
			'dotted' => 'Dotted',
			'dashed' => 'Dashed',

		);

	/**
	 * Should return the user-friendly name for this Action
	 *
	 * @return string
	 */
	public function getName() {
		return __( 'Tooltip', 'thrive-cb' );
	}

	/**
	 * Should return an actual string containing the JS function that's handling this action.
	 * The function will be called with 3 parameters:
	 *      -> event_trigger (e.g. click, dblclick etc)
	 *      -> action_code (the action that's being executed)
	 *      -> config (specific configuration for each specific action - the same configuration that has been setup in the settings section)
	 *
	 * Example (php): return 'function (trigger, action, config) { console.log(trigger, action, config); }';
	 *
	 * The function will be called in the context of the element
	 *
	 * The output MUST be a valid JS function definition.
	 *
	 * @return string the JS function definition (declaration + body)
	 */
	public function getJsActionCallback() {
		return tcb_template( 'actions/tooltip.js.php', null, true );
	}

	public function render_editor_settings() {
		tcb_template( 'actions/tooltip', array(
			'positions' => $this->_positions,
			'styles'    => $this->_styles,
		) );
	}

	/**
	 * Backbone View implementing the tooltip functionality.
	 *
	 * @return string
	 */
	public function get_editor_js_view() {
		return 'Tooltip';
	}

	public function get_options() {
		return array( 'labels' => __( 'Tooltip', 'thrive-cb' ) );
	}
}
