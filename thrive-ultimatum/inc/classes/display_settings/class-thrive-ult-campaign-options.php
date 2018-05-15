<?php

/**
 * Class GroupOptions
 * JSON options saved by user in database
 * Mapper model over database table
 */
class Thrive_Ult_Campaign_Options {
	private $table_name = 'settings_campaign';
	private $campaign_id;
	private $description;
	public $show_options;
	public $hide_options;
	private $db;

	protected $init_done = false;

	public function __construct( $campaign_id, $show_options = '', $hide_options = '' ) {
		/**
		 * @var $wpdb wpdb
		 */
		global $wpdb;
		$this->db           = $wpdb;
		$this->table_name   = tve_ult_table_name( $this->table_name );
		$this->campaign_id  = $campaign_id;
		$this->show_options = $show_options;
		$this->hide_options = $hide_options;
	}

	protected function _processPreSave( $jsonOptions ) {
		$options = @json_decode( stripcslashes( $jsonOptions ), true );
		if ( empty( $options ) || empty( $options['tabs'] ) ) {
			return json_encode( array( 'identifier' => $jsonOptions['identifier'] ) );
		}

		$clean_options = array();

		foreach ( $options['tabs'] as $index => $tabOptions ) {
			$clean_options['tabs'][ $index ]['options'] = $tabOptions;
		}

		return json_encode( $clean_options );
	}

	public function save() {
		if ( $this->delete() === false ) {
			return $this->db->last_error;
		}

		$this->db->suppress_errors();
		$show_options = $this->_processPreSave( $this->show_options );
		$hide_options = $this->_processPreSave( $this->hide_options );

		return $this->db->insert( $this->table_name, array(
			'campaign_id'  => $this->campaign_id,
			'description'  => $this->description,
			'show_options' => $show_options,
			'hide_options' => $hide_options
		) ) !== false ? true : $this->db->last_error;
	}

	/**
	 * Deletes Group
	 *
	 * @return false|int Affected rows on success or false on error
	 */
	public function delete() {
		//new code for WP 4.1.2
		$result = $this->db->query(
			$this->db->prepare( "DELETE FROM `{$this->table_name}` WHERE `campaign_id` = %d", $this->campaign_id )
		);

		return $result;
	}

	/**
	 * Read options from database
	 * @return $this
	 */
	public function initOptions() {
		if ( ! $this->init_done ) {
			$sql = "SELECT * FROM {$this->table_name} WHERE `campaign_id` = '{$this->campaign_id}'";
			$row = $this->db->get_row( $sql );
			if ( $row ) {
				$this->show_options = $row->show_options;
				$this->hide_options = $row->hide_options;
				$this->description  = $row->description;
			}
			$this->init_done = true;
		}

		return $this;
	}

	/**
	 * copy options from database to new campaign
	 *
	 * @Param $from contains id of campaign to copy options from
	 *
	 * @return true|error
	 */
	public function copyOptions( $from ) {
		$from = intval( $from );
		$sql  = "SELECT * FROM {$this->table_name} WHERE `campaign_id` = '{$from}'";
		$row  = $this->db->get_row( $sql );
		if ( $row ) {
			$this->show_options = $row->show_options;
			$this->hide_options = $row->hide_options;
			$this->description  = $row->description;
		}

		return $this->db->insert( $this->table_name, array(
			'campaign_id'  => $this->campaign_id,
			'description'  => $this->description,
			'show_options' => $this->show_options,
			'hide_options' => $this->hide_options
		) ) !== false ? true : $this->db->last_error;
	}

	/**
	 * @return string
	 */
	public function getShowGroupOptions() {
		return $this->show_options;
	}

	/**
	 * @return string
	 */
	public function getHideGroupOptions() {
		return $this->hide_options;
	}

	/**
	 * @param mixed $description
	 */
	public function setDescription( $description ) {
		$this->description = $description;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDescription() {
		return $this->description;
	}

	// get current URL
	public function get_current_URL() {
		$requested_url = is_ssl() ? 'https://' : 'http://';
		$requested_url .= $_SERVER['HTTP_HOST'];
		$requested_url .= $_SERVER['REQUEST_URI'];

		return $requested_url;
	}

	/**
	 * Check if any option is checked
	 * @return bool
	 */
	public function checkForAnyOptionChecked() {
		$this->initOptions();
		$showingOptions = @json_decode( stripcslashes( $this->getShowGroupOptions() ) );
		if ( empty( $showingOptions ) ) {
			return false;
		}

		$optionsChecked = strpos( $this->getShowGroupOptions(), "true" );
		if ( $optionsChecked ) {
			return true;
		}
		foreach ( $showingOptions->tabs as $tab ) {
			if ( ! empty( $tab->options ) ) {
				foreach ( $tab->options as $opt ) {
					if ( ! is_object( $opt ) ) {
						return true;
					}
				}
			}
		}

		if ( empty( $showingOptions->tabs[7] ) ) {
			return false;
		}

		foreach ( $showingOptions->tabs[7]->options as $item ) {
			if ( is_object( $item ) ) {
				if ( $item->isChecked || $item->type == 'direct_url' ) {
					return true;
				}
			} else {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return bool
	 */
	public function displayCampaign() {
		$display = true;

		/**
		 * if none of the options is not selected do not sow the campaign
		 */
		if ( ! $this->checkForAnyOptionChecked() ) {
			return false;
		}

		if ( is_front_page() ) {

			/* @var $otherScreensTab OtherScreensTab */
			$otherScreensTab = Thrive_Ult_Tab_Factory::build( 'other_screens' );
			$otherScreensTab->setSavedOptions( $this );

			/* @var $directUrlsTab DirectUrlsTab */
			$directUrlsTab = Thrive_Ult_Tab_Factory::build( 'direct_urls' );
			$directUrlsTab->setSavedOptions( $this );

			/* @var $visitorsStatusTab VisitorsStatusTab */
			$visitorsStatusTab = Thrive_Ult_Tab_Factory::build( 'visitors_status' );
			$visitorsStatusTab->setSavedOptions( $this );
			$visitorsStatus = is_user_logged_in() ? 'logged_in' : 'logged_out';

			$inclusion = $otherScreensTab->isScreenAllowed( 'front_page' )
			             || $directUrlsTab->isUrlAllowed( $this->get_current_URL() )
			             || $visitorsStatusTab->isStatusAllowed( $visitorsStatus );

			if ( $inclusion === false ) {
				return false;
			}

			$exclusion = $otherScreensTab->isScreenDenied( 'front_page' )
			             || $directUrlsTab->isUrlDenied( $this->get_current_URL() )
			             || $visitorsStatusTab->isStatusDenied( $visitorsStatus );

			if ( $exclusion === true ) {
				$display = false;
			}

			//endif is_front_page

		} else if ( is_home() ) {

			/* @var $otherScreensTab OtherScreensTab */
			$otherScreensTab = Thrive_Ult_Tab_Factory::build( 'other_screens' );
			$otherScreensTab->setSavedOptions( $this );

			/* @var $directUrlsTab DirectUrlsTab */
			$directUrlsTab = Thrive_Ult_Tab_Factory::build( 'direct_urls' );
			$directUrlsTab->setSavedOptions( $this );

			/* @var $visitorsStatusTab VisitorsStatusTab */
			$visitorsStatusTab = Thrive_Ult_Tab_Factory::build( 'visitors_status' );
			$visitorsStatusTab->setSavedOptions( $this );
			$visitorsStatus = is_user_logged_in() ? 'logged_in' : 'logged_out';

			$inclusion = $otherScreensTab->isScreenAllowed( 'blog_index' )
			             || $directUrlsTab->isUrlAllowed( $this->get_current_URL() )
			             || $visitorsStatusTab->isStatusAllowed( $visitorsStatus );

			if ( $inclusion === false ) {
				return false;
			}

			$exclusion = $otherScreensTab->isScreenDenied( 'blog_index' )
			             || $directUrlsTab->isUrlDenied( $this->get_current_URL() )
			             || $visitorsStatusTab->isStatusDenied( $visitorsStatus );

			if ( $exclusion === true ) {
				$display = false;
			}

		} else if ( is_page() ) {

			/* @var $post WP_Post */
			global $post;

			/** @var Thrive_Ult_Other_Screens_Tab $otherScreensTab */
			$otherScreensTab = Thrive_Ult_Tab_Factory::build( 'other_screens' );
			$otherScreensTab->setSavedOptions( $this );

			/* @var $pagesTab PagesTab */
			$pagesTab = Thrive_Ult_Tab_Factory::build( 'pages' );
			$pagesTab->setSavedOptions( $this );

			/* @var $pageTemplatesTab PageTemplatesTab */
			$pageTemplatesTab = Thrive_Ult_Tab_Factory::build( 'page_templates' );
			$pageTemplatesTab->setSavedOptions( $this );

			/* @var $postTypesTab PostTypesTab */
			$postTypesTab = Thrive_Ult_Tab_Factory::build( 'post_types' );
			$postTypesTab->setSavedOptions( $this );

			/* @var $directUrlsTab DirectUrlsTab */
			$directUrlsTab = Thrive_Ult_Tab_Factory::build( 'direct_urls' );
			$directUrlsTab->setSavedOptions( $this );

			/* @var $visitorsStatusTab VisitorsStatusTab */
			$visitorsStatusTab = Thrive_Ult_Tab_Factory::build( 'visitors_status' );
			$visitorsStatusTab->setSavedOptions( $this );
			$visitorsStatus = is_user_logged_in() ? 'logged_in' : 'logged_out';

			/* @var $taxonomyTermsTab Thrive_Ult_Taxonomy_Terms_Tab */
			$taxonomyTermsTab = Thrive_Ult_Tab_Factory::build( 'taxonomy_terms' );
			$taxonomyTermsTab->setSavedOptions( $this );

			$inclusion = $otherScreensTab->allTypesAllowed( get_post_type() ) || $pagesTab->isPageAllowed( $post )
			             || $postTypesTab->isTypeAllowed( get_post_type() )
			             || $directUrlsTab->isUrlAllowed( $this->get_current_URL() )
			             || $pageTemplatesTab->isTemplateAllowed( basename( get_page_template() ) )
			             || $visitorsStatusTab->isStatusAllowed( $visitorsStatus )
			             || $taxonomyTermsTab->isPostAllowed( $post );

			if ( $inclusion === false ) {
				return false;
			}

			$exclusion = $otherScreensTab->allTypesDenied( get_post_type() ) || $pagesTab->isPageDenied( $post )
			             || $postTypesTab->isDeniedType( get_post_type() )
			             || $directUrlsTab->isUrlDenied( $this->get_current_URL() )
			             || $pageTemplatesTab->isTemplateDenied( basename( get_page_template() ) )
			             || $visitorsStatusTab->isStatusDenied( $visitorsStatus )
			             || $taxonomyTermsTab->isPostDenied( $post );

			if ( $exclusion === true ) {
				$display = false;
			}

			//endif is_page

		} else if ( is_single() ) {

			/* @var $post WP_Post */
			global $post;

			/** @var Thrive_Ult_Other_Screens_Tab $otherScreensTab */
			$otherScreensTab = Thrive_Ult_Tab_Factory::build( 'other_screens' );
			$otherScreensTab->setSavedOptions( $this );

			/* @var $postsTab PostsTab */
			$postsTab = Thrive_Ult_Tab_Factory::build( 'posts' );
			$postsTab->setSavedOptions( $this );

			/* @var $postTypesTab PostTypesTab */
			$postTypesTab = Thrive_Ult_Tab_Factory::build( 'post_types' );
			$postTypesTab->setSavedOptions( $this );

			/* @var $directUrlsTab DirectUrlsTab */
			$directUrlsTab = Thrive_Ult_Tab_Factory::build( 'direct_urls' );
			$directUrlsTab->setSavedOptions( $this );

			/* @var $visitorsStatusTab VisitorsStatusTab */
			$visitorsStatusTab = Thrive_Ult_Tab_Factory::build( 'visitors_status' );
			$visitorsStatusTab->setSavedOptions( $this );
			$visitorsStatus = is_user_logged_in() ? 'logged_in' : 'logged_out';

			/* @var $taxonomyTermsTab Thrive_Ult_Taxonomy_Terms_Tab */
			$taxonomyTermsTab = Thrive_Ult_Tab_Factory::build( 'taxonomy_terms' );
			$taxonomyTermsTab->setSavedOptions( $this );

			$inclusion = $otherScreensTab->allTypesAllowed( get_post_type() ) || $postsTab->isPostAllowed( $post )
			             || $postTypesTab->isTypeAllowed( get_post_type() )
			             || $directUrlsTab->isUrlAllowed( $this->get_current_URL() )
			             || $visitorsStatusTab->isStatusAllowed( $visitorsStatus )
			             || $taxonomyTermsTab->isPostAllowed( $post );

			if ( $inclusion === false ) {
				return false;
			}

			$exclusion = $otherScreensTab->allTypesDenied( get_post_type() ) || $postsTab->isPostDenied( $post )
			             || $postTypesTab->isDeniedType( get_post_type() )
			             || $directUrlsTab->isUrlDenied( $this->get_current_URL() )
			             || $visitorsStatusTab->isStatusDenied( $visitorsStatus )
			             || $taxonomyTermsTab->isPostDenied( $post );

			if ( $exclusion === true ) {
				$display = false;
			}

			//endif is_single

		} else if ( is_archive() ) {

			$taxonomy = get_queried_object();

			/* @var $taxonomyArchivesTab TaxonomyArchivesTab */
			$taxonomyArchivesTab = Thrive_Ult_Tab_Factory::build( 'taxonomy_archives' );
			$taxonomyArchivesTab->setSavedOptions( $this );

			/* @var $directUrlsTab DirectUrlsTab */
			$directUrlsTab = Thrive_Ult_Tab_Factory::build( 'direct_urls' );
			$directUrlsTab->setSavedOptions( $this );

			/* @var $visitorsStatusTab VisitorsStatusTab */
			$visitorsStatusTab = Thrive_Ult_Tab_Factory::build( 'visitors_status' );
			$visitorsStatusTab->setSavedOptions( $this );
			$visitorsStatus = is_user_logged_in() ? 'logged_in' : 'logged_out';

			$inclusion = $taxonomyArchivesTab->isTaxonomyAllowed( $taxonomy )
			             || $directUrlsTab->isUrlAllowed( $this->get_current_URL() )
			             || $visitorsStatusTab->isStatusAllowed( $visitorsStatus );

			if ( $inclusion === false ) {
				return false;
			}

			$exclusion = $taxonomyArchivesTab->isTaxonomyDenied( $taxonomy )
			             || $directUrlsTab->isUrlDenied( $this->get_current_URL() )
			             || $visitorsStatusTab->isStatusDenied( $visitorsStatus );

			if ( $exclusion === true ) {
				$display = false;
			}

			//endif is_archive

		} else if ( is_404() ) {

			/* @var $otherScreensTab Thrive_Ult_Other_Screens_Tab */
			$otherScreensTab = Thrive_Ult_Tab_Factory::build( 'other_screens' );
			$otherScreensTab->setSavedOptions( $this );

			/* @var $directUrlsTab DirectUrlsTab */
			$directUrlsTab = Thrive_Ult_Tab_Factory::build( 'direct_urls' );
			$directUrlsTab->setSavedOptions( $this );

			/* @var $visitorsStatusTab VisitorsStatusTab */
			$visitorsStatusTab = Thrive_Ult_Tab_Factory::build( 'visitors_status' );
			$visitorsStatusTab->setSavedOptions( $this );
			$visitorsStatus = is_user_logged_in() ? 'logged_in' : 'logged_out';

			$inclusion = $otherScreensTab->isScreenAllowed( '404_error_page' )
			             || $directUrlsTab->isUrlAllowed( $this->get_current_URL() )
			             || $visitorsStatusTab->isStatusAllowed( $visitorsStatus );

			if ( $inclusion === false ) {
				return false;
			}

			$exclusion = $otherScreensTab->isScreenDenied( '404_error_page' )
			             || $directUrlsTab->isUrlDenied( $this->get_current_URL() )
			             || $visitorsStatusTab->isStatusDenied( $visitorsStatus );

			if ( $exclusion === true ) {
				$display = false;
			}

			//endif is_404

		} else if ( is_search() ) {

			/* @var $otherScreensTab OtherScreensTab */
			$otherScreensTab = Thrive_Ult_Tab_Factory::build( 'other_screens' );
			$otherScreensTab->setSavedOptions( $this );

			/* @var $directUrlsTab DirectUrlsTab */
			$directUrlsTab = Thrive_Ult_Tab_Factory::build( 'direct_urls' );
			$directUrlsTab->setSavedOptions( $this );

			/* @var $visitorsStatusTab VisitorsStatusTab */
			$visitorsStatusTab = Thrive_Ult_Tab_Factory::build( 'visitors_status' );
			$visitorsStatusTab->setSavedOptions( $this );
			$visitorsStatus = is_user_logged_in() ? 'logged_in' : 'logged_out';

			$inclusion = $otherScreensTab->isScreenAllowed( 'search_page' )
			             || $directUrlsTab->isUrlAllowed( $this->get_current_URL() )
			             || $visitorsStatusTab->isStatusAllowed( $visitorsStatus );

			if ( $inclusion === false ) {
				return false;
			}

			$exclusion = $otherScreensTab->isScreenDenied( 'search_page' )
			             || $directUrlsTab->isUrlDenied( $this->get_current_URL() )
			             || $visitorsStatusTab->isStatusDenied( $visitorsStatus );

			if ( $exclusion === true ) {
				$display = false;
			}

			//endif is_search
		} else {
			$current_url = $this->get_current_URL();
			/* @var $directUrlsTab Thrive_Ult_Direct_Urls_Tab */
			$directUrlsTab = Thrive_Ult_Tab_Factory::build( 'direct_urls' );
			$directUrlsTab->setSavedOptions( $this );

			$display = $directUrlsTab->isUrlAllowed( $current_url ) && ! $directUrlsTab->isUrlDenied( $current_url );
		}

		return $display;
	}

	public function getTabSavedOptions( $tabIndex, $hanger ) {
		$options = json_decode( stripcslashes( $this->$hanger ) );

		if ( empty( $options ) || empty( $options->tabs[ $tabIndex ] ) || empty( $options->tabs[ $tabIndex ]->options ) ) {
			return array();
		}
		$opts   = $options->tabs[ $tabIndex ]->options;
		$return = array();
		foreach ( $opts as $option ) {
			if ( is_object( $option ) ) {
				if ( ! $option->isChecked && $option->type != 'direct_url' ) {
					continue;
				}
				$return [] = $option->id;
			} else {
				$return [] = $option;
			}
		}

		return $return;
	}
}
