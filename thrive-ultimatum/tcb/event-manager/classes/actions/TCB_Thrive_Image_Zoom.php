<?php

/**
 * Created by PhpStorm.
 * User: sala
 * Date: 04.12.2015
 * Time: 14:44
 */
class TCB_Thrive_Image_Zoom extends TCB_Event_Action_Abstract {

	protected $key = 'thrive_zoom';

	/**
	 * Should return the user-friendly name for this Action
	 *
	 * @return string
	 */
	public function getName() {
		return __( 'Open Image', 'thrive-cb' );
	}

	/**
	 * Should output the settings needed for this Action when a user selects it from the list
	 *
	 * @param mixed $data existing configuration data, etc
	 *
	 * @return string html
	 */
	public function renderSettings( $data ) {
		return $this->renderTCBSettings( 'zoom', $data );
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
		return tcb_template( 'actions/image-zoom.js', null, true );
	}

	public function applyContentFilter( $data ) {
		/**
		 * IF an ID exists in the config array, it means that the attachment with the corresponding id must be opened
		 * Append it to the body
		 */
		if ( ! empty( $data ) && ! empty( $data['config'] ) && ! empty( $data['config']['id'] ) ) {

			if ( empty( $data['config']['url'] ) ) {
				$image = wp_get_attachment_image( $data['config']['id'], empty( $data['config']['size'] ) ? 'full' : $data['config']['size'] );

				return sprintf( '<div class="tcb-image-zoom" style="display: none" id="tcb-image-zoom-%s">%s</div>', $data['config']['id'], $image );
			} elseif ( ! empty( $data['config']['url'] ) && filter_var( $data['config']['url'], FILTER_VALIDATE_URL ) !== false ) { //This is a custom image URL. Not saved in WordPress
				return sprintf( '<div class="tcb-image-zoom" style="display: none" id="tcb-image-zoom-%s">%s</div>', $data['config']['id'], '<img src="' . $data['config']['url'] . '" />' );
			}
		}
	}

	public function get_editor_js_view() {
		return 'ImageZoom';
	}

	public function get_options() {
		return array( 'labels' => __( 'Open Image', 'thrive-cb' ) );
	}

	public function render_editor_settings() {

	}
}
