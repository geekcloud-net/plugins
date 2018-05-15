<?php
/**
 * Created by PhpStorm.
 * User: radu
 * Date: 05.08.2014
 * Time: 14:35
 */

if ( ! class_exists( 'TCB_Thrive_Lightbox' ) ) {
	/**
	 *
	 * handles the server-side logic for the Thrive Lightbox action = opens a lightbox on an Event Trigger
	 *
	 * Class TCB_Thrive_Lightbox
	 */
	class TCB_Thrive_Lightbox extends TCB_Event_Action_Abstract {

		protected $key = 'thrive_lightbox';

		/**
		 * holds all lightbox ids that have been rendered in the footer - this is to not render a lightbox twice
		 *
		 * @var array
		 */
		private static $loaded_lightboxes = array();

		/**
		 * holds all lightbox ids that have been parsed for events configuration - this is to not create an infinite loop in case of
		 * lightboxes used within lightboxes
		 *
		 * @var array
		 */
		private static $lightboxes_events_parsed = array();

		/**
		 * Should return the user-friendly name for this Action
		 *
		 * @return string
		 */
		public function getName() {
			return 'Open Thrive Lightbox';
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
		 * The output MUST be a valid JS function definition.
		 *
		 * @return string the JS function definition (declaration + body)
		 */
		public function getJsActionCallback() {
			return 'function(t,a,c){var $t=jQuery("#tve_thrive_lightbox_"+c.l_id).css("display", ""),a=c.l_anim?c.l_anim:"instant";TCB_Front.openLightbox($t,a);return false;};';
		}

		/**
		 * makes all necessary changes to the content depending on the $data param
		 *
		 * this gets called each time this action is encountered in the DOM event configuration
		 *
		 * @param $data
		 */
		public function applyContentFilter( $data ) {
			$lightbox_id = isset( $data['config']['l_id'] ) ? intval( $data['config']['l_id'] ) : 0;

			if ( ! $lightbox_id ) {
				return false;
			}

			if ( isset( self::$loaded_lightboxes[ $lightbox_id ] ) ) {
				return '';
			}

			$lightbox = get_post( $lightbox_id );
			if ( empty( $lightbox ) ) {
				return '';
			}

			global $post;
			$old_post                          = $post;
			$GLOBALS['tcb_main_post_lightbox'] = $old_post;
			$post                              = $lightbox;

			/**
			 * this if was added for TU Main Ajax request, the the html must be returned
			 */
			if ( ! has_filter( 'the_content', 'tve_editor_content' ) ) {
				add_filter( 'the_content', 'tve_editor_content' );
			}

			$lightbox_html = tcb_lightbox( $lightbox_id )->get_html();

			$post                                    = $old_post;
			self::$loaded_lightboxes[ $lightbox_id ] = $lightbox_html;

			ob_start();
			tve_load_custom_css( $lightbox_id );
			$lightbox_html = ob_get_contents() . $lightbox_html;
			ob_end_clean();

			return $lightbox_html;
		}

		/**
		 * check if the associated lightbox exists and it's not trashed
		 *
		 * @return bool
		 */
		public function validateConfig() {
			$lightbox_id = $this->config['l_id'];
			if ( empty( $lightbox_id ) ) {
				return false;
			}

			$lightbox = get_post( $lightbox_id );
			if ( empty( $lightbox ) || $lightbox->post_status === 'trash' || $lightbox->post_type != 'tcb_lightbox' ) {
				return false;
			}

			return true;
		}

		/**
		 * make sure that if custom icons are used, the CSS for that is included in the main page
		 * the same with Custom Fonts
		 *
		 * @param array $data
		 */
		public function mainPostCallback( $data ) {

			$lightbox_id = empty( $data['config']['l_id'] ) ? 0 : $data['config']['l_id'];
			if ( isset( self::$lightboxes_events_parsed[ $lightbox_id ] ) ) {
				return;
			}
			self::$lightboxes_events_parsed[ $lightbox_id ] = true;
			if ( tve_get_post_meta( $lightbox_id, 'thrive_icon_pack' ) && ! wp_style_is( 'thrive_icon_pack', 'enqueued' ) ) {
				TCB_Icon_Manager::enqueue_icon_pack();
			}

			tve_enqueue_extra_resources( $lightbox_id );

			/* check for the lightbox style and include it */
			tve_enqueue_style_family( $lightbox_id );

			tve_enqueue_custom_fonts( $lightbox_id, true );

			/* output any css needed for the extra (imported) fonts */
			if ( function_exists( 'tve_output_extra_custom_fonts_css' ) ) {
				tve_output_extra_custom_fonts_css( $lightbox_id );
			}

			$js_suffix = defined( 'TVE_DEBUG' ) && TVE_DEBUG ? '.js' : '.min.js';

			if ( tve_get_post_meta( $lightbox_id, 'tve_has_masonry' ) ) {
				wp_script_is( 'jquery-masonry' ) || wp_enqueue_script( 'jquery-masonry', array( 'jquery' ) );
			}

			$lightbox_content = get_post_meta( $lightbox_id, 'tve_updated_post', true );
			tve_parse_events( $lightbox_content );

			$globals = tve_get_post_meta( $lightbox_id, 'tve_globals' );
			if ( ! empty( $globals['js_sdk'] ) ) {
				foreach ( $globals['js_sdk'] as $handle ) {
					wp_script_is( 'tve_js_sdk_' . $handle ) || wp_enqueue_script( 'tve_js_sdk_' . $handle, tve_social_get_sdk_link( $handle ), array(), false );
				}
			}
		}

		public function get_editor_js_view() {
			return 'ThriveLightbox';
		}

		public function get_options() {
			return array(
				'labels' => __( 'Open Lightbox', 'thrive-cb' ),
				'data'   => self::lightbox_data(),
			);
		}

		public function render_editor_settings() {
			tcb_template( 'actions/lightbox', self::animations() );
		}

		/**
		 * Get all TCB lightboxes - if the current post is a landing page, only lightboxes specific to that landing page are returned
		 *
		 * @return array
		 */
		public static function lightbox_data() {
			$post_id               = get_the_ID();
			$landing_page_template = tve_post_is_landing_page( $post_id );

			$all_lightboxes = get_posts( array(
				'posts_per_page' => - 1,
				'post_type'      => 'tcb_lightbox',
			) );

			$data['lightboxes'] = array();
			foreach ( $all_lightboxes as $lightbox ) {
				if ( (int) $lightbox->ID === (int) $post_id ) { // makes no sense to open the same lightbox from within itself
					continue;
				}
				/**
				 * @deprecated in TCB2 - display all lightboxes on all pages..
				 *
				 * $lightbox_lp = get_post_meta( $lightbox->ID, 'tve_lp_lightbox', true );
				 * if ( ! empty( $landing_page_template ) ) {
				 * if ( $lightbox_lp !== $landing_page_template ) {
				 * continue;
				 * }
				 * } elseif ( ! empty( $lightbox_lp ) ) {
				 * continue;
				 * }
				 */
				$data['lightboxes'] [] = array(
					'id'       => $lightbox->ID,
					'title'    => $lightbox->post_title,
					'edit_url' => tcb_get_editor_url( $lightbox->ID ),
				);
			}
			/* we use this to display the user the possibility of creating a new lightbox */
			$data['for_landing_page'] = $landing_page_template;
			$data['animations']       = self::animations();

			return $data;
		}

		/**
		 * available lightbox animations
		 *
		 * @return array
		 */
		public static function animations() {
			return array(
				'instant'      => __( 'Instant (No animation)', 'thrive-cb' ),
				'zoom_in'      => __( 'Zoom', 'thrive-cb' ),
				'zoom_out'     => __( 'Zoom Out', 'thrive-cb' ),
				'rotate'       => __( 'Rotational', 'thrive-cb' ),
				'slide_top'    => __( 'Slide in from Top', 'thrive-cb' ),
				'slide_bottom' => __( 'Slide in from Bottom', 'thrive-cb' ),
				'lateral'      => __( 'Lateral', 'thrive-cb' ),
			);
		}
	}
}
