<?php

class Thrive_Ult_Display_Settings_Manager {
	/** @var int */
	protected $campaign_id;

	/** @var Thrive_Ult_Campaign_Options */
	protected $saved_options;

	public function __construct( $campaign_id = null ) {
		if ( isset( $campaign_id ) ) {
			$this->campaign_id = $campaign_id;
		}
	}

	public function initHangers( $campaign_id ) {
		$hangers[] = new Thrive_Ult_Hanger( 'show_options', $campaign_id );
		$hangers[] = new Thrive_Ult_Hanger( 'hide_options', $campaign_id );
		$tabs      = array(
			'other_screens'     => __( 'Basic Settings', TVE_Ult_Const::T ),
			'taxonomy_terms'    => __( 'Categories etc.', TVE_Ult_Const::T ),
			'posts'             => __( 'Posts', TVE_Ult_Const::T ),
			'pages'             => __( 'Pages', TVE_Ult_Const::T ),
			'page_templates'    => __( 'Page Templates', TVE_Ult_Const::T ),
			'post_types'        => __( 'Post Types', TVE_Ult_Const::T ),
			'taxonomy_archives' => __( 'Archive Pages', TVE_Ult_Const::T ),
			'others'            => __( 'Other', TVE_Ult_Const::T )
		);
		/**
		 * @var $hanger Thrive_Ult_Hanger
		 */
		foreach ( $hangers as $hanger ) {
			$hanger->initTabs( $tabs, $this->saved_options );
		}

		return $hangers;
	}

	/**
	 * Call this only once
	 * Saved options for a campaign are the same for all tabs
	 */
	protected function load_saved_options() {
		$this->saved_options = new Thrive_Ult_Campaign_Options( $this->campaign_id );
		$this->saved_options->initOptions();
	}

	public function get_popup_data() {
		$this->load_dependencies();

		$this->load_saved_options();

		$group = isset( $_GET['campaign_id'] ) ? $_GET['campaign_id'] : $this->campaign_id;

		$hangers = $this->initHangers( $group );

		//used in file included at the end of this function
		$saved_templates = apply_filters( 'thrive_display_options_get_templates', array() );

		return array(
			'hangers'        => $hangers,
			'savedTemplates' => $saved_templates,
		);
	}

	public function load_template() {
		$this->load_dependencies();

		$templates   = new Thrive_Ult_Saved_Options();
		$template_id = $_REQUEST['template_id'];
		if ( strpos( $template_id, 'TU-' ) === 0 ) {
			$template_id = str_replace( 'TU-', '', $template_id );
		} else {
			$template = apply_filters( 'thrive_display_options_get_template', array(), $template_id );
		}
		$templates->initOptions( isset( $template ) ? false : $template_id, isset( $template ) ? $template : null );

		$hangers = array(
			new Thrive_Ult_Hanger( 'show_options', $_REQUEST['campaign_id'] ),
			new Thrive_Ult_Hanger( 'hide_options', $_REQUEST['campaign_id'] ),
		);

		$identifiers = array(
			'other_screens'     => __( 'Basic Settings', TVE_Ult_Const::T ),
			'taxonomy_terms'    => __( 'Categories etc.', TVE_Ult_Const::T ),
			'posts'             => __( 'Posts', TVE_Ult_Const::T ),
			'pages'             => __( 'Pages', TVE_Ult_Const::T ),
			'page_templates'    => __( 'Page Templates', TVE_Ult_Const::T ),
			'post_types'        => __( 'Post Types', TVE_Ult_Const::T ),
			'taxonomy_archives' => __( 'Archive Pages', TVE_Ult_Const::T ),
			'others'            => __( 'Other', TVE_Ult_Const::T )
		);

		/**
		 * @var $hanger Thrive_Ult_Hanger
		 */
		foreach ( $hangers as $hanger ) {
			/**
			 * @var $tab Thrive_Ult_Tab
			 */
			foreach ( $identifiers as $identifier => $label ) {

				$tab = Thrive_Ult_Tab_Factory::build( $identifier );
				$tab->setGroup( $_REQUEST['campaign_id'] )
				    ->setIdentifier( $identifier )
				    ->setSavedOptions( new Thrive_Ult_Campaign_Options( $_REQUEST['campaign_id'], $templates->getShowGroupOptions(), $templates->getHideGroupOptions() ) )
				    ->setLabel( $label )
				    ->setHanger( $hanger->identifier )
				    ->initOptions()
				    ->initFilters();
				$hanger->tabs[] = $tab;
			}
		}
		wp_send_json( $hangers );

	}

	public function getSavedTemplates() {
		$savedTemplates = new Thrive_Ult_Saved_Options();
		$templates      = $savedTemplates->getAll();

		foreach ( $templates as $template ) {
			$template->id           = TVE_Dash_Product_LicenseManager::TU_TAG . $template->id;
			$template->show_options = $this->processTpl( json_decode( stripcslashes( $template->show_options ), true ) );
			$template->hide_options = $this->processTpl( json_decode( stripcslashes( $template->hide_options ), true ) );
		}

		return $templates;
	}

	protected function processTpl( $savedOptions ) {
		$return = array();
		foreach ( $savedOptions['tabs'] as $index => $tab ) {
			$options  = $this->checkBackwardsComp( $tab['options'] );
			$return[] = array(
				'options' => $options,
				'index'   => $index
			);
		}

		return $return;
	}

	public function checkBackwardsComp( $options ) {
		$return = array();
		foreach ( $options as $o ) {
			if ( is_array( $o ) ) {
				if ( ! empty( $o['isChecked'] ) || ( ! empty( $o['type'] ) && $o['type'] == 'direct_url' ) ) {
					$return [] = $o['id'];
				}
			} else {
				$return [] = $o;
			}
		}

		return $return;
	}

	public function save_options() {
		if ( empty( $_POST['options'] ) || empty( $_POST['campaign_id'] ) ) {
			return __( 'Empty values', TVE_Ult_Const::T );
		}

		require_once plugin_dir_path( __FILE__ ) . 'class-thrive-ult-campaign-options.php';

		$campaign_id = new Thrive_Ult_Campaign_Options( $_POST['campaign_id'], $_POST['options'][0], $_POST['options'][1] );

		return $campaign_id->save();
	}

	public function save_template() {
		if ( empty( $_POST['options'] ) || empty( $_POST['name'] ) ) {
			return false;
		}

		require_once plugin_dir_path( __FILE__ ) . 'class-thrive-ult-saved-options.php';

		$template = new Thrive_Ult_Saved_Options( $_POST['name'], $_POST['options'][0], $_POST['options'][1], '' );

		return $template->save();
	}

	/**
	 * Load all the dependencies that are needed for this manager
	 */
	public function load_dependencies() {
		require_once plugin_dir_path( __FILE__ ) . 'class-thrive-ult-filter.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-thrive-ult-action.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-thrive-ult-option.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-thrive-ult-hanger.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-thrive-ult-tab-interface.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-thrive-ult-tab.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-thrive-ult-tab-factory.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-thrive-ult-posts-tab.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-thrive-ult-pages-tab.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-thrive-ult-page-templates-tab.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-thrive-ult-post-types-tab.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-thrive-ult-taxonomy-archives-tab.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-thrive-ult-taxonomy-terms-tab.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-thrive-ult-other-screens-tab.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-thrive-ult-direct-urls-tab.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-thrive-ult-visitors-status-tab.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-thrive-ult-campaign-options.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-thrive-ult-saved-options.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-thrive-others-tab.php';
	}

}
