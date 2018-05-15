<?php
/**
 * User Profile views
 *
 * @package Framework\Views
 */

/**
 * User Profile view page
 */
class APP_User_Profile extends APP_View_Page {

	function __construct() {
		parent::__construct( 'edit-profile.php', __( 'Edit Profile', APP_TD ), array( 'internal_use_only' => true ) );
		add_action( 'init', array( $this, 'update' ) );
	}

	static function get_id() {
		return parent::_get_id( __CLASS__ );
	}

	function update() {
		if ( !isset( $_POST['action'] ) || 'app-edit-profile' != $_POST['action'] )
			return;

		check_admin_referer( 'app-edit-profile' );

		require ABSPATH . '/wp-admin/includes/user.php';

		$r = edit_user( $_POST['user_id'] );

		if ( is_wp_error( $r ) ) {
			$this->errors = $r;
		} else {
			do_action( 'personal_options_update', $_POST['user_id'] );

			appthemes_add_notice( 'updated-profile', __( 'Your profile has been updated.', APP_TD ), 'success' );

			$redirect_url = add_query_arg( array( 'updated' => 'true' ) );
			wp_redirect( $redirect_url );
			exit();
		}
	}

	function template_redirect() {
		// Prevent non-logged-in users from accessing the edit-profile.php page
		appthemes_auth_redirect_login();

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	function enqueue_scripts() {
		wp_enqueue_script( 'user-profile' );
	}

}

function appthemes_get_edit_profile_url() {
	if ( $page_id = APP_User_Profile::get_id() )
		return get_permalink( $page_id );

	return get_edit_profile_url( get_current_user_id() );
}

