<?php

/**
 * Created by PhpStorm.
 * User: radu
 * Date: 11.08.2014
 * Time: 16:03
 */
class TCB_Thrive_CSS_Animation extends TCB_Event_Action_Abstract {

	protected $key = 'thrive_animation';

	/**
	 * available CSS animations
	 *
	 * @var array
	 */
	protected $_animations
		= array(
			'slide_top'    => 'Top to bottom',
			'slide_bottom' => 'Bottom to top',
			'slide_left'   => 'Left to right',
			'slide_right'  => 'Right to left',
			'appear'       => 'Appear from Centre (Zoom In)',
			'zoom_out'     => 'Zoom Out',
			'fade'         => 'Fade in',
			'rotate'       => 'Rotational',
			'roll_in'      => 'Roll In',
			'roll_out'     => 'Roll Out',
			'grow'         => 'Grow',
			'shrink'       => 'Shrink',
		);

	/**
	 *
	 * @return array
	 */
	public static function get_config() {
		$config = array(
			'slide'  => array(
				'title' => __( 'Sliding', 'thrive-cb' ),
				'items' => array(
					'slide_top'    => array(
						'title'   => __( 'Slide, top', 'thrive-cb' ),
						'trigger' => array( 'tve-viewport' ),
					),
					'slide_bottom' => array(
						'title'   => __( 'Slide, bottom', 'thrive-cb' ),
						'trigger' => array( 'tve-viewport' ),
					),
					'slide_right'  => array(
						'title'   => __( 'Slide, right', 'thrive-cb' ),
						'trigger' => array( 'tve-viewport' ),
					),
					'slide_left'   => array(
						'title'   => __( 'Slide, left', 'thrive-cb' ),
						'trigger' => array( 'tve-viewport' ),
					),
				),
			),
			'zoom'   => array(
				'title' => __( 'Zoom (Appear)', 'thrive-cb' ),
				'items' => array(
					'appear'   => array(
						'title'   => __( 'Zoom in', 'thrive-cb' ),
						'trigger' => array( 'tve-viewport' ),
					),
					'zoom_out' => array(
						'title'   => __( 'Zoom out', 'thrive-cb' ),
						'trigger' => array( 'tve-viewport' ),
					),
				),
			),
			'modify' => array(
				'title' => __( 'Modify', 'thrive-cb' ),
				'items' => array(
					'grow'   => array(
						'title'   => __( 'Grow', 'thrive-cb' ),
						'trigger' => array( 'tve-viewport', 'mouseover' ),
					),
					'shrink' => array(
						'title'   => __( 'Shrink', 'thrive-cb' ),
						'trigger' => array( 'tve-viewport', 'mouseover' ),
					),
				),
			),
			'other'  => array(
				'title' => __( 'Other (Appear)', 'thrive-cb' ),
				'items' => array(
					'fade_in' => array(
						'title'   => __( 'Fade in', 'thrive-cb' ),
						'trigger' => array( 'tve-viewport' ),
					),
					'rotate'  => array(
						'title'   => __( 'Rotate', 'thrive-cb' ),
						'trigger' => array( 'tve-viewport' ),
					),
					'roll_in' => array(
						'title'   => __( 'Roll in', 'thrive-cb' ),
						'trigger' => array( 'tve-viewport' ),
					),
				),
			),
		);

		return apply_filters( 'tcb_animations', $config );
	}

	/**
	 * Should return the user-friendly name for this Action
	 *
	 * @return string
	 */
	public function getName() {
		return 'Animation';
	}

	/**
	 * Should output the settings needed for this Action when a user selects it from the list
	 *
	 * @param mixed $data existing configuration data, etc
	 */
	public function renderSettings( $data ) {
		return $this->renderTCBSettings( 'animation', $data );
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
		$classes = array();
		foreach ( array_keys( $this->_animations ) as $anim ) {
			$classes [] = 'tve_anim_' . $anim;
		}
		$classes = implode( ' ', $classes );

		return 'function(trigger, action, config) {
			
            var $element = jQuery(this),
                $at = $element.closest(".tcb-col, .thrv_wrapper");
            if ($at.length === 0) {
                $at = $element;
            }
            if (!config.loop && $at.data("a-done")) {return;}
            $at.data("a-done",1);
            $at.removeClass("' . $classes . '").addClass("tve_anim_" + config.anim).removeClass("tve_anim_start");
            if (config.loop) {
             $at.addClass("tve_anim_start");
				if (trigger === "mouseover") { $element.one("mouseleave", function () { $at.removeClass("tve_anim_start"); }); }
				if (trigger === "tve-viewport") { $element.one("tve-viewport-leave", function () { $at.removeClass("tve_anim_start"); }); }
            }
            else{
             setTimeout(function () {
                $at.addClass("tve_anim_start");
            }, 50);
            }
            return false;
        }';
	}

	public function getSummary() {
		if ( ! empty( $this->config ) ) {
			return ': ' . $this->_animations[ $this->config['anim'] ];
		}
	}

	public function get_editor_js_view() {
		return 'Animation';
	}

	public function render_editor_settings() {
		tcb_template( 'actions/animation', self::get_config() );
	}

	public function get_options() {
		$labels   = array(
			'__config_key' => 'anim',
		);
		$triggers = array();
		foreach ( self::get_config() as $item ) {
			foreach ( $item['items'] as $key => $data ) {
				$labels[ $key ]   = $data['title'];
				$triggers[ $key ] = $data['trigger'];
			}
		}

		return array(
			'labels'   => $labels,
			'triggers' => $triggers,
		);
	}
}
