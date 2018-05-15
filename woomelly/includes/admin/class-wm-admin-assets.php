<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'WMAdminAssets', false ) ) {
	return new WMAdminAssets();
}

/**
 * WMAdminAssets Class.
 */
class WMAdminAssets {
    /**
     * Default constructor.
     */	
	public function __construct () {
		add_action( 'wp_enqueue_scripts', array( $this, 'woomelly_enqueue_styles' ), 10 );
		add_action( 'wp_enqueue_scripts', array( $this, 'woomelly_enqueue_scripts' ), 10 );
		add_action( 'admin_enqueue_scripts', array( $this, 'woomelly_admin_enqueue_styles' ), 10, 1 );			
		add_action( 'admin_enqueue_scripts', array( $this, 'woomelly_admin_enqueue_scripts' ), 10, 1 );
		//add_filter( 'mce_external_plugins', array( $this, 'woomelly_mce_external_plugins' ), 10, 1 );
	} //End __construct()

	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), Woomelly()->get_version() );
	} //End __clone()

	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), Woomelly()->get_version() );
	} //End __wakeup()
 
	/**
	 * woomelly_enqueue_styles.
	 *
	 * @return void
	 */	
	public function woomelly_enqueue_styles () {
		wp_register_style( Woomelly()->get_token() . '-frontend', esc_url( Woomelly()->get_assets_url() ) . 'css/frontend.css', array(), Woomelly()->get_version() );
		wp_enqueue_style( Woomelly()->get_token() . '-frontend' );
	} //End woomelly_enqueue_styles()

	/**
	 * woomelly_enqueue_scripts.
	 *
	 * @return void
	 */	
	public function woomelly_enqueue_scripts () {
		wp_register_script( Woomelly()->get_token() . '-frontend', esc_url( Woomelly()->get_assets_url() ) . 'js/frontend.js', array( 'jquery' ), Woomelly()->get_version() );
		wp_enqueue_script( Woomelly()->get_token() . '-frontend' );
	} //End woomelly_enqueue_scripts()

	/**
	 * woomelly_admin_enqueue_styles.
	 *
	 * @return void
	 */	
	public function woomelly_admin_enqueue_styles ( $hook = '' ) {
		$wm_page = $hook;
		if ( isset($_GET['page']) && $_GET['page']!="" ) {
			$wm_page = $_GET['page'];
		}
		if ( in_array( $wm_page, Woomelly()->get_pages() ) ) {
			wp_register_style( Woomelly()->get_token() . '-uikit', esc_url( Woomelly()->get_assets_url() ) . 'css/uikit/uikit.min.css', array(), Woomelly()->get_version() );
			wp_enqueue_style( Woomelly()->get_token() . '-uikit' );
			/*wp_register_style( Woomelly()->get_token() . '-dataTables', esc_url( Woomelly()->get_assets_url() ) . 'css/dataTables/jquery.dataTables.min.css', array(), '1.10.16' );
			wp_enqueue_style( Woomelly()->get_token() . '-dataTables' );*/
			wp_register_style( Woomelly()->get_token() . '-flexboxgrid', esc_url( Woomelly()->get_assets_url() ) . 'css/flexboxgrid/flexboxgrid.min.css', array(), Woomelly()->get_version() );
			wp_enqueue_style( Woomelly()->get_token() . '-flexboxgrid' );
		}
		wp_register_style( Woomelly()->get_token() . '-ladda', esc_url( Woomelly()->get_assets_url() ) . 'css/ladda/ladda.min.css', array(), Woomelly()->get_version() );
		wp_enqueue_style( Woomelly()->get_token() . '-ladda' );		
		wp_register_style( Woomelly()->get_token() . '-waitMe', esc_url( Woomelly()->get_assets_url() ) . 'css/waitMe/waitMe.min.css', array(), Woomelly()->get_version() );
		wp_enqueue_style( Woomelly()->get_token() . '-waitMe' );
		wp_register_style( Woomelly()->get_token() . '-admin', esc_url( Woomelly()->get_assets_url() ) . 'css/admin.css', array(), Woomelly()->get_version() );
		wp_enqueue_style( Woomelly()->get_token() . '-admin' );
		wp_enqueue_style( 'thickbox' );
	} //End woomelly_admin_enqueue_styles()

	/**
	 * woomelly_admin_enqueue_scripts.
	 *
	 * @return void
	 */	
	public function woomelly_admin_enqueue_scripts ( $hook = '' ) {
		$wm_page = $hook;
		if ( isset($_GET['page']) && $_GET['page']!="" ) {
			$wm_page = $_GET['page'];
		}
		if ( in_array( $wm_page, Woomelly()->get_pages() ) ) {
			wp_register_script( Woomelly()->get_token() . '-uikit', esc_url( Woomelly()->get_assets_url() ) . 'js/uikit/uikit.min.js', array( 'jquery' ), Woomelly()->get_version() );
			wp_enqueue_script( Woomelly()->get_token() . '-uikit' );
			wp_register_script( Woomelly()->get_token() . '-uikit-icons', esc_url( Woomelly()->get_assets_url() ) . 'js/uikit/uikit-icons.min.js', array( 'jquery' ), Woomelly()->get_version() );
			wp_enqueue_script( Woomelly()->get_token() . '-uikit-icons' );
			/*wp_register_script( Woomelly()->get_token() . '-dataTables', esc_url( Woomelly()->get_assets_url() ) . 'js/dataTables/jquery.dataTables.min.js', array( 'jquery' ), '1.10.16' );
			wp_enqueue_script( Woomelly()->get_token() . '-dataTables' );*/
		}
		wp_register_script( Woomelly()->get_token() . '-spin', esc_url( Woomelly()->get_assets_url() ) . 'js/ladda/spin.min.js', array( 'jquery' ), Woomelly()->get_version() );
		wp_enqueue_script( Woomelly()->get_token() . '-spin' );
		wp_register_script( Woomelly()->get_token() . '-ladda', esc_url( Woomelly()->get_assets_url() ) . 'js/ladda/ladda.min.js', array( 'jquery' ), Woomelly()->get_version() );
		wp_enqueue_script( Woomelly()->get_token() . '-ladda' );
		wp_register_script( Woomelly()->get_token() . '-sweetAlert', esc_url( Woomelly()->get_assets_url() ) . 'js/sweetAlert/sweetalert.min.js', array(), Woomelly()->get_version() );
		wp_enqueue_script( Woomelly()->get_token() . '-sweetAlert' );	
		wp_register_script( Woomelly()->get_token() . '-waitMe', esc_url( Woomelly()->get_assets_url() ) . 'js/waitMe/waitMe.min.js', array( 'jquery' ), Woomelly()->get_version() );
		wp_enqueue_script( Woomelly()->get_token() . '-waitMe' );
		wp_register_script( Woomelly()->get_token() . '-admin', esc_url( Woomelly()->get_assets_url() ) . 'js/admin.js', array( 'jquery' ), Woomelly()->get_version() );
		wp_enqueue_script( Woomelly()->get_token() . '-admin' );
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_script( 'media-upload' );
	} //End woomelly_admin_enqueue_scripts()

	/**
	 * woomelly_mce_external_plugins.
	 *
	 * @return array
	 */	
	/*public function woomelly_mce_external_plugins ( $plugin_array ) {
		$wm_page = '';
		if ( isset($_GET['page']) && $_GET['page']!="" ) {
			$wm_page = $_GET['page'];
		}
		if ( in_array( $wm_page, Woomelly()->get_pages() ) ) {
			$plugin_array['woomelly_button'] = Woomelly()->get_assets_url() . '/js/tinymce/tinymce_buttons.js';
		}
		return $plugin_array;
	}*/

}

return new WMAdminAssets();