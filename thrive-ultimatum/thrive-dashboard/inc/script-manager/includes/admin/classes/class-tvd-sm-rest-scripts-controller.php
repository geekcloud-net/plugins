<?php

class TVD_SM_REST_Scripts_Controller {

	public $version = 1;
	public $namespace = 'script-manager/v';

	public function register_routes() {

		register_rest_route( $this->namespace . $this->version, '/' . 'scripts', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_scripts' ),
				'permission_callback' => array( $this, 'general_permissions_check' ),

			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_scripts' ),
				'permission_callback' => array( $this, 'general_permissions_check' ),
			),
		) );

		register_rest_route( $this->namespace . $this->version, '/' . 'scripts-order', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'order_scripts' ),
				'permission_callback' => array( $this, 'general_permissions_check' ),
			),
		) );

		register_rest_route( $this->namespace . $this->version, '/' . 'clear-old-scripts', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'clear_individual_scripts' ),
				'permission_callback' => array( $this, 'general_permissions_check' ),
			),
		) );

		register_rest_route( $this->namespace . $this->version, '/scripts/(?P<id>[\d]+)', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_scripts' ),
				'permission_callback' => array( $this, 'general_permissions_check' ),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_script' ),
				'permission_callback' => array( $this, 'general_permissions_check' ),
			),
		) );
	}


	/**
	 *
	 * Clears the scripts added individually to landing pages.
	 *
	 * @return WP_REST_Response
	 */
	public function clear_individual_scripts() {
		foreach ( get_pages( array( 'meta_key' => 'tve_landing_page' ) ) as $page ) {
			update_post_meta( $page->ID, 'tve_global_scripts', array( 'head' => '', 'footer' => '' ) );
		}

		return new WP_REST_Response( 1, 200 );
	}

	/**
	 * Return scripts - calls get_scripts from the Admin Helper class
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_scripts() {
		return new WP_REST_Response( tah()->tvd_sm_get_scripts(), 200 );
	}

	/**
	 * Updates script order
	 *
	 * @param WP_REST_Request $request The request data from admin.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function order_scripts( $request ) {

		/* flips the id with the index (which is used as the order afterwards) */
		$new_script_order = array_flip( $request->get_param( 'scripts' ) );
		$scripts          = tah()->tvd_sm_get_scripts();

		/* updates the order of scripts in this group : for every id that was changed, update the order */
		foreach ( $scripts as $key => $script ) {
			/* if the current iterated id exists in $new_script_order, then update the order */
			if ( isset( $new_script_order[ $script['id'] ] ) ) {
				$scripts[ $key ]['order'] = $new_script_order[ $script['id'] ];
			}
		}

		if ( tah()->tvd_sm_update_option( 'global_lp_scripts', $scripts ) ) {
			return new WP_REST_Response( $scripts, 200 );
		} else {
			return new WP_Error( 'cant-update-order', __( "Couldn't add/update the 'order' field in the database.", TVE_DASH_TRANSLATE_DOMAIN ), array( 'status' => 500 ) );
		}
	}

	/**
	 * Updates scripts
	 *
	 * @param WP_REST_Request $request The request data from admin.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_scripts( $request ) {

		$script_id = $request->get_param( 'id' );

		$array_to_add_edit = array(
			'id'        => $script_id,
			'label'     => $request->get_param( 'label' ),
			'status'    => $request->get_param( 'status' ),
			'placement' => $request->get_param( 'placement' ),
			'code'      => $request->get_param( 'code' ),
			'order'     => $request->get_param( 'order' ),
			'icon'      => $request->get_param( 'icon' )
		);

		$scripts = tah()->tvd_sm_get_scripts();
		if ( empty( $script_id ) ) {
			/* add */
			$script_id               = tah()->tvd_sm_get_last_id_plus_one( $scripts );
			$array_to_add_edit['id'] = $script_id;
			$scripts[]               = $array_to_add_edit;
		} else {
			/* edit */
			$scripts[ tah()->tvd_sm_retrieve_key_for_id( $script_id, $scripts ) ] = $array_to_add_edit;
		}

		if ( tah()->tvd_sm_update_option( 'global_lp_scripts', $scripts ) ) {
			return new WP_REST_Response( $array_to_add_edit, 200 );
		} else {
			return new WP_Error( 'cant-update', __( "Couldn't add/update the fields in the database.", TVE_DASH_TRANSLATE_DOMAIN ), array( 'status' => 500 ) );
		}
	}

	/**
	 * Deletes scripts
	 *
	 * @param WP_REST_Request $request The request data from admin.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_script( $request ) {
		$script_id = $request->get_param( 'id' );
		$scripts   = tah()->tvd_sm_get_scripts();
		unset( $scripts[ tah()->tvd_sm_retrieve_key_for_id( $script_id, $scripts ) ] );

		if ( update_option( 'global_lp_scripts', $scripts ) ) {
			return new WP_REST_Response( $script_id, 200 );
		} else {
			return new WP_Error( 'cant-delete', __( "Couldn't delete the field from the database.", TVE_DASH_TRANSLATE_DOMAIN ), array( 'status' => 500 ) );
		}
	}

	/**
	 * If the user has access to the admin pages, then he is allowed to perform any operation on the scripts.
	 * @return bool
	 */
	public function general_permissions_check() {
		return current_user_can( 'manage_options' );
	}
}
