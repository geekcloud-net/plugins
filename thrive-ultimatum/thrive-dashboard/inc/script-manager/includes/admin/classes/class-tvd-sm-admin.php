<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden.
}

/**
 * Class TVD_SM_Admin
 */
class TVD_SM_Admin {

	private $_dashboard_page = 'tve_dash_script_manager';

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'current_screen', array( $this, 'conditional_hooks' ) );
	}

	public function includes() {
		include_once 'class-tvd-sm-rest-scripts-controller.php';
		include_once 'class-tvd-sm-admin-helper.php';
	}

	public function hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'rest_api_init', array( $this, 'admin_create_rest_routes' ) );
	}

	public function init() {
		$this->includes();
		$this->hooks();
	}

	public function enqueue_scripts() {
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		if ( $screen_id === 'admin_page_tve_dash_script_manager' ) {
			tve_dash_enqueue();
			tve_dash_enqueue_style( 'tvd-sm-admin-css', TVD_SM_Constants::url( '/assets/css/admin.css' ) );
			tve_dash_enqueue_script( 'tvd-sm-admin-js', TVD_SM_Constants::url( 'assets/js/dist/admin.min.js' ), array( 'jquery', 'jquery-ui-sortable', 'backbone' ), false, true );

			$params = array(
				'routes'                => array(
					'scripts'                  => get_rest_url() . 'script-manager/v1/scripts',
					'scripts_order'            => get_rest_url() . 'script-manager/v1/scripts-order',
					'clear_page_level_scripts' => get_rest_url() . 'script-manager/v1/clear-old-scripts',
				),
				'nonce'                 => wp_create_nonce( 'wp_rest' ),
				'scripts'               => tah()->tvd_sm_get_scripts(),
				'script_placement_text' => array(
					'head'       => 'Before ' . htmlentities( '</head>' ),
					'body_open'  => 'After ' . htmlentities( '<body>' ),
					'body_close' => 'Before ' . htmlentities( '</body>' ),
				),
				'translations'          => include TVD_SM_Constants::path( 'includes/i18n.php' ),
				'dash_url'              => admin_url( 'admin.php?page=tve_dash_section' ),
				'url'                   => TVD_SM_Constants::url(),
				'recognized_scripts'    => array(
					'keywords' => TVD_SM_Constants::get_recognized_scripts_keywords(),
					'data'     => TVD_SM_Constants::get_recognized_scripts_data(),
				),
			);

			wp_localize_script( 'tvd-sm-admin-js', 'TVD_SM_CONST', $params );
		}
	}

	/**
	 * Hook based on the current screen
	 */
	public function conditional_hooks() {
		if ( ! $screen = get_current_screen() ) {
			return;
		}

		/**
		 * if screen = main dashboard then enable and display the feature
		 */
		if ( $screen->id === 'toplevel_page_tve_dash_section' ) {
			add_filter( 'tve_dash_filter_features', array( $this, 'admin_script_feature' ) );
			add_filter( 'tve_dash_features', array( $this, 'admin_enable_feature' ) );
		}

		/**
		 * if screen = script_manager feature then load all the templates for the SM admin side
		 */
		if ( $screen->id === 'admin_page_tve_dash_script_manager' ) {
			add_action( 'admin_print_scripts', array( $this, 'admin_backbone_templates' ), 9 );
		}
	}

	/**
	 * Display the Script Manager Feature on the dashboard
	 *
	 * @param $features
	 *
	 * @return mixed
	 */
	public function admin_script_feature( $features ) {
		$features['script_manager'] = array(
			'icon'        => 'tvd-nm-icon-code',
			'title'       => 'Landing Pages Analytics & Scripts',
			'description' => __( 'Add & edit scripts on your website.', TVE_DASH_TRANSLATE_DOMAIN ),
			'btn_link'    => add_query_arg( 'page', $this->_dashboard_page, admin_url( 'admin.php' ) ),
			'btn_text'    => __( 'Manage Scripts', TVE_DASH_TRANSLATE_DOMAIN ),
		);

		return $features;
	}

	/**
	 * Enable the SM feature to be displayed on Thrive Features Section
	 *
	 * @param $features
	 *
	 * @return mixed
	 */
	public function admin_enable_feature( $features ) {
		$features['script_manager'] = true;

		return $features;
	}

	/**
	 * Add page to admin menu so the page could be accessed
	 */
	public function admin_menu() {
		add_submenu_page( null, __( 'Landing Pages Analytics & Scripts', TVE_DASH_TRANSLATE_DOMAIN ), __( 'Landing Pages Analytics & Scripts', TVE_DASH_TRANSLATE_DOMAIN ), 'manage_options', $this->_dashboard_page, array(
			$this,
			'admin_dashboard',
		) );
	}

	/**
	 * Main TVD_SM page content
	 */
	public function admin_dashboard() {
		include TVD_SM_Constants::path( 'includes/admin/views/dashboard.php' );
	}

	public function admin_create_rest_routes() {
		$controller = new TVD_SM_REST_Scripts_Controller();
		$controller->register_routes();
	}

	/**
	 * Add templates as scripts in the footer.
	 */
	public function admin_backbone_templates() {
		$templates = tve_dash_get_backbone_templates( TVD_SM_Constants::path( 'includes/admin/views/templates' ), 'templates' );
		tve_dash_output_backbone_templates( $templates );
	}
}

return new TVD_SM_Admin();
