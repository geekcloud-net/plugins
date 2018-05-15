<?php

/**
 * Handles AJAX calls related to design states
 *
 * Class TU_State_Manager
 */
class TU_State_Manager extends TU_Request_Handler {

	/**
	 * @var $instance self
	 */
	protected static $instance = null;

	/**
	 * Row from DB
	 *
	 * @var $design array always the main (default) state for a design
	 */
	protected $design = null;

	/**
	 * TU_State_Manager constructor.
	 *
	 * @param $design
	 */
	private function __construct( $design ) {
		$this->design = $design;
	}

	/**
	 * Returns the instance of the design
	 *
	 * @param $design array the design being edited - this is always the main (default) state for a design
	 *
	 * @return self
	 */
	public static function getInstance( $design ) {

		if ( ! empty( self::$instance ) && self::$instance->design['id'] === $design['id'] ) {
			return self::$instance;
		}

		if ( ! empty( $design ) && ! empty( $design['parent_id'] ) ) {
			$design = tve_ult_get_design( $design['parent_id'] );
		}

		return new self( $design );
	}

	/**
	 * get the html for the state bar
	 *
	 * @param array $current_design the design currently being displayed
	 *
	 * @return string
	 */
	protected function state_bar( $current_design ) {
		global $design;
		ob_start();

		$do_not_wrap = true;

		include TVE_Ult_Const::plugin_path() . 'tcb-bridge/editor/states.php';
		$state_bar = ob_get_contents();

		ob_end_clean();

		return $state_bar;
	}

	/**
	 * Compose all the data that's required on a page after the content has been changed
	 * (editor content / CSS links / fonts etc)
	 *
	 * @param array $current_design
	 *
	 * @return array
	 */
	public function state_data( $current_design ) {
		global $design;

		$design = $this->design;

		$state_bar = $this->state_bar( $current_design );

		$config = tve_ult_editor_get_template_config( $current_design[ TVE_Ult_Const::FIELD_TEMPLATE ] );

		/** $css is an array with 2 keys fonts and css which need to be included in the page, if they do not already exist */
		$css_links        = array();
		$enqueued_scripts = tve_ult_enqueue_design_scripts( $current_design );

		foreach ( $enqueued_scripts ['fonts'] as $_id => $_font ) {
			$css_links[ $_id ] = $_font;
		}

		foreach ( $enqueued_scripts ['css'] as $_id => $_css ) {
			if ( $_id == 'tve_ult_design' ) {
				continue;
			}
			$css_links[ $_id ] = $_css;
		}

		/** javascript global page data (that will overwrite parts of the global tve_path_params variable) */
		$javascript_data = array(
			'custom_post_data' => array(
				TVE_Ult_Const::DESIGN_QUERY_KEY_NAME => $current_design['id'],
				'design_id'                          => $current_design['id'],
			),
			'tve_globals'      => isset( $design[ TVE_Ult_Const::FIELD_GLOBALS ] ) ? $design[ TVE_Ult_Const::FIELD_GLOBALS ] : array( 'e' => 1 ),
		);

		/** javascript global page data for the TU - editor part */
		$editor_js = array(
			TVE_Ult_Const::FIELD_GLOBALS => $current_design[ TVE_Ult_Const::FIELD_GLOBALS ],
			'design_id'                  => $current_design['id'],
			'current_css'                => empty( $config['css'] ) ? '' : ( 'tve-ult-' . TU_Template_Manager::type( $current_design[ TVE_Ult_Const::FIELD_TEMPLATE ] ) . '-' . str_replace( '.css', '', $config['css'] ) ),
		);

		/**
		 * remember the latest edited for this design so that the next time the user will open the parent design
		 * we can show him directly this child
		 */
		update_post_meta( $current_design['post_parent'], TVE_Ult_Const::META_PREFIX_NAME_FOR_EDIT_STATE . $this->design['id'], $current_design['id'] );

		ob_start();
		tve_ult_editor_output_custom_css( $current_design, false );
		$custom_css = ob_get_contents();
		ob_end_clean();

		return array(
			'state_bar'         => $state_bar,
			'main_page_content' => trim( $this->render_ajax_content( $current_design ) ),
			'custom_css'        => $custom_css,
			'css'               => $css_links,
			'tve_path_params'   => $javascript_data,
			'tve_ult_page_data' => $editor_js,
		);
	}

	/**
	 * Renders the html contents for a new design to replace the previously edited one
	 *
	 * @param $current_design array
	 *
	 * @return string html
	 */
	public function render_ajax_content( $current_design ) {
		global $design;

		$design = $current_design;

		ob_start();
		$is_ajax_render = true;
		include TVE_Ult_Const::plugin_path() . 'tcb-bridge/editor/campaign/' . TU_Template_Manager::type( $current_design[ TVE_Ult_Const::FIELD_TEMPLATE ] ) . '.php';
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	/**
	 * API-calls after this point
	 * --------------------------------------------------------------------
	 */
	/**
	 * Add a new state
	 */
	public function api_add() {

		$child = $this->design;
		unset( $child['id'] );

		$child[ TVE_Ult_Const::FIELD_USER_CSS ] = '';
		$child['parent_id']                     = $this->design['id'];
		$child['post_title']                    = $this->param( 'post_title' );

		if ( ! ( $child = tve_ult_save_design( $child ) ) ) {
			return array(
				'error' => 'Could not save the new design'
			);
		}

		return $this->state_data( $child );

	}

	/**
	 * Change the name for a state
	 */
	public function api_edit_name() {
		if ( ! ( $state = tve_ult_get_design( $this->param( 'id' ) ) ) ) {
			return array();
		}

		global $design;
		$design = $this->design;

		$state['post_title'] = $this->param( 'post_title' );
		tve_ult_save_design( $state );

		return array(
			'state_bar' => $this->state_bar( tve_ult_get_design( $this->param( 'design_id' ) ) ),
		);
	}

	/**
	 * display a state
	 * @return array
	 */
	public function api_display() {

		if ( ! ( $id = $this->param( 'id' ) ) || ! ( $design = tve_ult_get_design( $id ) ) ) {
			return $this->state_data( $this->design );
		}

		return $this->state_data( $design );
	}

	/**
	 * duplicate a state
	 */
	public function api_duplicate() {
		if ( ! ( $id = $this->param( 'id' ) ) ) {
			return $this->state_data( $this->design );
		}

		if ( ! ( $design = tve_ult_get_design( $id ) ) ) {
			return array(
				'error' => __( 'Design not found', TVE_Ult_Const::T ),
			);
		}

		$child = $design;
		if ( empty( $child['parent_id'] ) ) {
			/** if the default one gets duplicated, this means adding the new design as a child of the main one */
			$child['parent_id'] = $design['id'];
		}

		unset( $child['id'] );
		/**
		 * the user custom CSS is only saved in the parent state
		 */
		$child[ TVE_Ult_Const::FIELD_USER_CSS ] = '';

		if ( ! ( $child = tve_ult_save_design( $child ) ) ) {
			return array(
				'error' => __( 'Design could not be saved', TVE_Ult_Const::T ),
			);
		}

		return $this->state_data( $child );
	}

	/**
	 * delete a child state
	 */
	public function api_delete() {
		if ( ! ( $id = $this->param( 'id' ) ) ) {
			return $this->state_data( $this->design );
		}

		$active_state = $this->param( 'design_id' );

		$all        = tve_ult_get_child_states( $this->design['id'] );
		$to_display = $this->design;
		$previous   = $this->design;

		/**
		 * handle designs like this, because we'll display the previous one if the user deletes the currently active state
		 */
		foreach ( $all as $v ) {
			if ( $active_state == $v['id'] ) {
				$active_state = $v;
			}
			/**
			 * make sure we don't delete the parent / default state for a design
			 */
			if ( $v['id'] == $id && ! empty( $v['parent_id'] ) ) {
				tve_ult_delete_design( $v['id'] );
				$to_display = $previous;
			}
			$previous = $v;
		}

		if ( ! is_array( $active_state ) ) {
			/**
			 * this means the default state is currently displayed
			 */
			$to_display = $this->design;

		} elseif ( $active_state['id'] != $id ) {

			/**
			 * if we just deleted the active state, we need to display the previous one
			 */
			$to_display = $active_state;
		}

		return $this->state_data( $to_display );
	}
}
