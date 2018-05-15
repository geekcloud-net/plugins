<?php

/**
 * Handles all Admin access functionality.
 */
class UB_Admin_Bar_Tab extends UltimateBrandingAdmin {

	protected  $js_files = array();
	protected  $css_files = array();


	/**
	 * Constructs the admin url tab
	 * Enqueues css and js and registers to hooks
	 * @since 1.5
	 * @access public
	 */
	public function __construct() {

		$this->register_js( UB_Admin_Bar::NAME, 'jquery.classywiggle.min' );
		$this->register_js( UB_Admin_Bar::NAME, 'main' );
		$this->register_css( UB_Admin_Bar::NAME, 'main' );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_local_scripts' ) );
		add_action( 'ultimatebranding_settings_adminbar', array( &$this, 'create_admin_page' ) );
		add_action( 'ultimatebranding_admin_header_adminbar', array( &$this, 'js_print_scripts' ) );
	}

	private function get_enqueue_handle( $module_name, $file_name ) {
		return $module_name . '-' . str_replace( '.', '-', $file_name );
	}

	private function register_css( $module_name, $file ) {
		$this->css_files[ $module_name ][] = $file;
		add_action( 'ultimatebranding_admin_header_adminbar', array( $this, 'register_modules_css' ) );
	}

	public function register_modules_css() {
		if ( empty( $this->build ) ) {
			global $ub_version;
			$this->build = $ub_version;
		}
		foreach ( $this->css_files as $module_name => $files ) {
			foreach ( $files as $file_name ) {
				$file_path = ub_files_url( 'modules/' . $module_name . '-files/css/'. $file_name . '.css' );
				wp_enqueue_style( $this->get_enqueue_handle( $module_name, $file_name ), $file_path, array(), $this->build );
			}
		}
	}

	public function register_modules_js() {
		if ( empty( $this->build ) ) {
			global $ub_version;
			$this->build = $ub_version;
		}
		foreach ( $this->js_files as $module_name => $files ) {
			foreach ( $files as $file_name ) {
				$file_path = ub_files_url( 'modules/' . $module_name . '-files/js/'. $file_name . '.js' );
				wp_enqueue_script( $this->get_enqueue_handle( $module_name, $file_name ), $file_path, array(), $this->build, true );
			}
		}
	}

	private function register_js( $module_name, $file ) {
		$this->js_files[ $module_name ][] = $file;
		add_action( 'ultimatebranding_admin_header_adminbar', array( $this, 'register_modules_js' ) );
	}

	/**
	 * Register localized translated strings
	 * @since 1.5
	 * @access public
	 * @return void
	 */
	function register_local_scripts() {
		wp_localize_script( $this->get_enqueue_handle( UB_Admin_Bar::NAME, 'main' ), 'ub_admin_bar', array(
			'new_bar'            => __( 'New Bar', 'ub' ),
			'new_bar_sub_menu'   => __( 'New Bar Submenu', 'ub' ),
			'save_before_adding' => __( 'Please save before you can add submenus', 'ub' ),
		) );
	}

	/**
	 * Adds admin menu entry for Custom Admin bar module
	 * @return void
	 */
	function create_admin_menu_entry() {
		if ( @$_POST && isset( $_POST['option_page'] ) ) {
			$changed = false;
			if ( 'wdcab_options' == @$_POST['option_page'] ) {
				if ( isset( $_POST['wdcab']['links']['_last_'] ) ) {
					$last = $_POST['wdcab']['links']['_last_'];
					unset( $_POST['wdcab']['links']['_last_'] );
					if ( @$last['url'] && @$last['title'] ) {
						$_POST['wdcab']['links'][] = $last;
					}
				}
				if ( isset( $_POST['wdcab']['links'] ) ) {
					$_POST['wdcab']['links'] = array_filter( $_POST['wdcab']['links'] );
				}
				ub_update_option( 'wdcab', $_POST['wdcab'] );
				$changed = true;
			}

			if ( $changed ) {
				$goback = UB_Help::add_query_arg_raw( 'settings-updated', 'true', wp_get_referer() );
				wp_redirect( $goback );
				die;
			}
		}
		$page  = is_multisite() ? 'settings.php' : 'options-general.php';
		$perms = is_multisite() ? 'manage_network_options' : 'manage_options';
		add_submenu_page( $page, __( 'Custom Admin Bar', 'ub' ), __( 'Custom Admin Bar', 'ub' ), $perms, 'wdcab', array(
			$this,
			'create_admin_page',
		) );
	}

	/**
	 * Renders admin tab
	 * @since 1.5
	 * @access public
	 * @return void
	 */
	function create_admin_page() {
		global $wp_version;
		$version = preg_replace( '/-.*$/', '', $wp_version );
		$this->render( UB_Admin_Bar::NAME, 'general_settings', array(
			'enable_hiding_default_bars' => version_compare( $version, '3.3', '>=' ),
		) );
?>
        <div class="postbox-container" id="ub_admin_bar_menus">
<?php
		$this->render( UB_Admin_Bar::NAME, 'menus' );
?>
        </div>
<?php
		$this->render( UB_Admin_Bar::NAME, 'add_new' );
	}

	/**
	 * Enqueues required core scripts
	 * @return void
	 */
	function js_print_scripts() {
		wp_enqueue_script( array(
			'jquery-effects-shake'
		) );
	}
}