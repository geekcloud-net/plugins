<?php

if ( ! class_exists( 'TCB_Thrive_LightboxClose' ) ) {
	/**
	 * Class TCB_Thrive_LightboxClose
	 */
	class TCB_Thrive_LightboxClose extends TCB_Event_Action_Abstract {

		protected $key = 'close_lightbox';

		public function getName() {
			return 'Close Thrive Lightbox';
		}

		public function getJsActionCallback() {
			return tcb_template( 'actions/close-lightbox.js', null, true );
		}


		public function get_editor_js_view() {
			return 'ThriveLightboxClose';
		}

		public function render_editor_settings() {
			tcb_template( 'actions/lightbox-close', null );
		}

		public function get_options() {
			return array( 'labels' => __( 'Close Lightbox', 'thrive-cb' ) );

		}
	}
}
