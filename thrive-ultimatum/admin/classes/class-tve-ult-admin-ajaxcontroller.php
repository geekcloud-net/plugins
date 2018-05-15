<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ultimatum
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access
}

/**
 * Class Tve_Ult_Admin_AjaxController
 *
 * Ajax controller to handle admin ajax requests
 * Specially built for backbone models
 */
class Tve_Ult_Admin_AjaxController {

	/**
	 * @var Tve_Ult_Admin_AjaxController $instance
	 */
	protected static $instance;

	/**
	 * Tve_Ult_Admin_AjaxController constructor.
	 * Protected constructor because we want to use it as singleton
	 */
	protected function __construct() {
	}

	/**
	 * Sets the request's header with server protocol and status
	 * Sets the request's body with specified $message
	 *
	 * @param $message
	 * @param string $status
	 */
	protected function error( $message, $status = '404 Not Found' ) {
		header( $_SERVER['SERVER_PROTOCOL'] . ' ' . $status );
		echo $message;
		wp_die();
	}

	/**
	 * Returns the params from $_POST or $_REQUEST
	 *
	 * @param $key
	 * @param null $default
	 *
	 * @return mixed|null|$default
	 */
	protected function param( $key, $default = null ) {
		return isset( $_POST[ $key ] ) ? $_POST[ $key ] : ( isset( $_REQUEST[ $key ] ) ? $_REQUEST[ $key ] : $default );
	}

	/**
	 * Gets the SingleTone's instance
	 *
	 * @return Tve_Ult_Admin_AjaxController
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new Tve_Ult_Admin_AjaxController();
		}

		return self::$instance;
	}

	/**
	 * Entry-point for each ajax request
	 * This should dispatch the request to the appropriate method based on the "route" parameter
	 *
	 * @return array|object
	 */
	public function handle() {
		if ( ! check_ajax_referer( 'tve_ult_admin_ajax_request', '_nonce', false ) ) {
			$this->error( sprintf( __( 'Invalid request', TVE_Ult_Const::T ) ) );
		}

		$route = $this->param( 'route' );

		$route       = preg_replace( '#([^a-zA-Z0-9-])#', '', $route );
		$method_name = $route . 'Action';

		if ( ! method_exists( $this, $method_name ) ) {
			$this->error( sprintf( __( 'Method %s not implemented', TVE_Ult_Const::T ), $method_name ) );
		}

		return $this->{$method_name}();
	}

	/**
	 * Show the Display Settings popup for a campaign
	 */
	protected function displaySettingsCampaignAction() {
		$memory_limit = (int) ini_get( 'memory_limit' );
		if ( $memory_limit < 256 ) {
			ini_set( 'memory_limit', '256M' );
		}

		require_once TVE_Ult_Const::plugin_path() . 'inc/classes/display_settings/class-thrive-display-settings-manager.php';
		$display_settings_manager = new Thrive_Ult_Display_Settings_Manager( $this->param( 'campaign_id' ) );

		return $display_settings_manager->get_popup_data();
	}

	/**
	 * Save display settings for a Campaign
	 *
	 * @return array
	 */
	protected function displaySettingsSaveAction() {
		require_once TVE_Ult_Const::plugin_path() . 'inc/classes/display_settings/class-thrive-display-settings-manager.php';
		$display_settings_manager = new Thrive_Ult_Display_Settings_Manager( $this->param( 'campaign_id' ) );
		$result                   = $display_settings_manager->save_options();

		/**
		 * check if there is a knonwn cache plugin installed and clear the cache if that's the case
		 */
		$cache_plugin = tve_dash_detect_cache_plugin();
		if ( $cache_plugin ) {
			tve_dash_cache_plugin_clear( $cache_plugin );
		}

		return array(
			'success' => $result === true ? true : false,
			'message' => $result !== true ? 'Error while saving: ' . $result : '',
		);
	}

	/**
	 * Save a template containing display settings
	 *
	 * @return array
	 */
	public function displaySettingsSaveTemplateAction() {
		require_once TVE_Ult_Const::plugin_path() . 'inc/classes/display_settings/class-thrive-display-settings-manager.php';
		$display_settings_manager = new Thrive_Ult_Display_Settings_Manager();
		$result                   = $display_settings_manager->save_template();

		return array(
			'success'   => $result === true ? true : false,
			'message'   => $result !== true ? sprintf( __( 'Error while saving: %s', TVE_Ult_Const::T ), $result ) : '',
			'templates' => $result === true ? apply_filters( 'thrive_display_options_get_templates', array() ) : array(),
		);
	}

	/**
	 * Load a saved Campaign Display Settings template
	 */
	public function displaySettingsLoadTemplateAction() {
		require_once TVE_Ult_Const::plugin_path() . 'inc/classes/display_settings/class-thrive-display-settings-manager.php';
		$display_settings_manager = new Thrive_Ult_Display_Settings_Manager();

		$display_settings_manager->load_template();
	}

	/**
	 * Performs actions for campaigns based on request's method and model
	 * Dies with error if the operation was not executed
	 *
	 * @return mixed
	 */
	protected function campaignsAction() {
		$model  = json_decode( file_get_contents( 'php://input' ), true );
		$method = empty( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ) ? 'GET' : $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];

		if ( ( $custom = $this->param( 'custom' ) ) ) {
			switch ( $custom ) {
				case 'chart_data':
					return tve_ult_get_chart_data( $this->param( 'ID' ) );
					break;
				case 'update_order':
					$ordered = $this->param( 'new_order', array() );
					foreach ( $ordered as $post_id => $order ) {
						update_post_meta( $post_id, TVE_Ult_Const::META_NAME_FOR_CAMPAIGN_ORDER, $order );
					}

					return $ordered;
					break;
				default:
					return array();
			}
		}

		switch ( $method ) {
			case 'POST':
			case 'PUT':
			case 'PATCH':
				if ( ! ( $item = tve_ult_save_campaign( $model ) ) ) {
					$this->error( __( 'Could not save', TVE_Ult_Const::T ) );
				}

				return $item;
				break;
			case 'DELETE':
				if ( ! ( $deleted = tve_ult_delete_campaign( $this->param( 'ID' ), true ) ) ) {
					$this->error( __( 'Could not delete', TVE_Ult_Const::T ) );
				}

				return $deleted;
				break;
			case 'GET':

				$campaign = tve_ult_get_campaign( $this->param( 'ID', 0 ), array(
					'get_designs' => true,
					'designs_hierarchy' => true,
					'get_events'  => true,
				) );

				if ( $campaign === false ) {
					$this->error( __( 'Item not found', TVE_Ult_Const::T ) );
				}

				/* we also need to load the display settings for the campaign - to show the summary */
				require_once TVE_Ult_Const::plugin_path() . 'inc/classes/display_settings/class-thrive-display-settings-manager.php';
				$display_settings_manager = new Thrive_Ult_Display_Settings_Manager( $this->param( 'ID' ) );

				$_data                          = $display_settings_manager->get_popup_data();
				$campaign->display_settings     = $_data['hangers'];
				$campaign->display_settings_tpl = $_data['savedTemplates'];

				$timeline = new TU_Timeline( $campaign );
				$timeline->prepare_events();

				return $campaign;
				break;
		}
	}

	/**
	 * Save the Date Settings
	 *
	 * @return bool
	 */
	protected function dateSettingsAction() {
		$model  = json_decode( file_get_contents( 'php://input' ), true );
		$method = empty( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ) ? 'GET' : $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];

		switch ( $method ) {
			case 'POST':
			case 'PUT':
			case 'PATCH':
				if ( ! ( $item = tve_ult_save_date_settings( $model ) ) ) {
					$this->error( __( 'Could not save', TVE_Ult_Const::T ) );
				}

				return $item;
				break;
			case 'DELETE':
			case 'GET':
		}
	}

	/**
	 * Performs actions for designs based on request's method and model
	 * Dies with error if the operation was not executed
	 *
	 * @return mixed
	 */
	public function designsAction() {
		$model  = json_decode( file_get_contents( 'php://input' ), true );
		$method = empty( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ) ? 'GET' : $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];

		switch ( $method ) {
			case 'POST':
			case 'PUT':
			case 'PATCH':
				if ( ! ( $item = tve_ult_save_design( $model ) ) ) {
					$this->error( __( 'Could not save', TVE_Ult_Const::T ) );
				}

				return $item;
				break;
			case 'DELETE':
				if ( ! ( $deleted = tve_ult_delete_design( (int) $this->param( 'ID' ) ) ) ) {
					$this->error( __( 'Could not delete design', TVE_Ult_Const::T ) );
				}

				return $deleted;
				break;
			case 'GET':
				if ( ! ( $item = tve_ult_get_design( (int) $this->param( 'ID' ) ) ) ) {
					$this->error( __( 'Item not found', TVE_Ult_Const::T ) );
				}

				return $item;
				break;
		}
	}

	/**
	 * Fetch the list of designs. If the request contains a get_states key, it will return the full list of designs for a campaign( including child states )
	 *
	 * @return array
	 */
	public function designListAction() {
		$data   = json_decode( file_get_contents( 'php://input' ), true );
		$method = empty( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ) ? 'GET' : $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];

		if ( ! ( $campaign_id = $this->param( 'campaign_id' ) ) ) {
			$this->error( __( 'Missing Campaign', TVE_Ult_Const::T ) );
		}

		switch ( $method ) {
			case 'GET':
				if ( $this->param( 'get_states' ) ) {
					$designs = tve_ult_get_designs_hierarchy( $campaign_id );
				} else {
					$designs = tve_ult_get_designs( $campaign_id );
				}

				return $designs;
			default:
				return array();
		}
	}

	/**
	 * Entry point for actions
	 * Dies with error if the operation was not executed
	 *
	 * @return array
	 */
	public function actionsAction() {
		$model  = json_decode( file_get_contents( 'php://input' ), true );
		$method = empty( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ) ? 'GET' : $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];

		switch ( $method ) {
			case 'GET':
				/** gets the options html for an action */
				if ( $this->param( 'custom' ) === 'options' ) {
					$html = TU_Event_Action::getInstance()->get_options_html( $this->param( 'action_model' ) );

					return array(
						'html' => $html,
					);
				}
				break;
		}
	}

	/**
	 * Entry point for campaign status actions
	 * Dies with error if the operation was not executed
	 *
	 * @return array
	 */
	public function campaignStatusAction() {
		$item = tve_ult_save_campaign_status( $this->param( 'ID' ), $this->param( 'status' ) );
		if ( ! $item['success'] ) {
			$error_list = array(
				'invalid_call'           => __( 'Invalid call', TVE_Ult_Const::T ),
				'invalid_id'             => __( 'The campaign ID is invalid', TVE_Ult_Const::T ),
				'settings_error'         => __( "You cannot start this campaign until you've setup the campaign type and duration", TVE_Ult_Const::T ),
				'design_error'           => __( 'You cannot start this campaign until you add at least a design', TVE_Ult_Const::T ),
				'design_tpl_error'       => __( 'You cannot start this campaign until you have at least one design that has a template selected', TVE_Ult_Const::T ),
				'display_settings_error' => __( 'You cannot start this campaign before setting up the display options', TVE_Ult_Const::T ),
				'evergreen_linked'       => __( 'You cannot start this campaign until you setup a start trigger type', TVE_Ult_Const::T ),
				'end_date_in_past'       => __( 'You cannot start this campaign because the end date is in the past', TVE_Ult_Const::T ),
				'lockdown'               => __( 'You cannot start this campaign before setting up the lockdown options', TVE_Ult_Const::T ),
				'invalid_trigger'        => __( 'You cannot start this campaign with an invalid or empty trigger', TVE_Ult_Const::T ),
				'invalid_leads_trigger'  => __( 'You cannot start this campaign with a Leads Conversion as a trigger when Thrive Leads is deactivated', TVE_Ult_Const::T ),
			);
			$this->error( $error_list[ $item['message'] ], 422 );
		}

		return $item['data'];
	}

	/**
	 * Entry point for events
	 * Dies with error if the operation was not executed
	 *
	 * @return mixed
	 */
	public function eventsAction() {
		$model  = json_decode( file_get_contents( 'php://input' ), true );
		$method = empty( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ) ? 'GET' : $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];

		switch ( $method ) {
			case 'PATCH':
			case 'PUT':
			case 'POST':
				if ( ! ( $item = tve_ult_save_event( $model ) ) ) {
					$this->error( __( 'Could not save', TVE_Ult_Const::T ) );
				}

				return $item;
				break;
			case 'DELETE':
				if ( ! ( $deleted = tve_ult_delete_event( (int) $this->param( 'ID' ) ) ) ) {
					$this->error( __( 'Could not delete', TVE_Ult_Const::T ) );
				}

				return $deleted;
				break;
			case 'GET':
				$campaign = tve_ult_get_campaign( $this->param( 'campaign_id', 0 ), array(
					'get_designs' => false,
					'get_events'  => true,
				) );

				if ( $campaign === false ) {
					$this->error( __( 'Item not found', TVE_Ult_Const::T ) );
				}

				$timeline = new TU_Timeline( $campaign );
				$timeline->prepare_events();

				return $campaign->timeline;
				break;
		}
	}

	/**
	 * Does a progresive search based on a string
	 * @return mixed
	 */
	public function progressiveGetPostsAction() {

		$s = wp_unslash( $this->param( 'term' ) );
		$s = trim( $s );

		$all_post_types = get_post_types();

		$exception_list = apply_filters( 'tvu_post_types_blacklist', array(
			'attachment',
			'focus_area',
			'thrive_optin',
			'wysijap',
			'product_variation',
			'revision',
			'nav_menu_item',
			'tve_lead_shortcode',
			'tve_lead_1c_signup',
			'tve_form_type',
			'tve_lead_group',
			'tcb_lightbox',
			'tve_lead_2s_lightbox',
			'tve_ult_campaign',
			'tvo_testimonials',
			'reply',
			'tva_lesson',
		) );
		$post_types     = array_diff( $all_post_types, $exception_list );

		$args    = array(
			'post_type'   => $post_types,
			'post_status' => 'publish',
			's'           => $s,
			'numberposts' => 10,
		);
		$results = array();
		foreach ( get_posts( $args ) as $post ) {
			$post_type_obj = get_post_type_object( $post->post_type );
			$results []    = array(
				'id'    => $post->ID,
				'label' => $post->post_title,
				'type'  => $post_type_obj->labels->menu_name,

			);
		}

		return $results;
	}

	/**
	 * Returns post title id and url on a post fetched by id
	 * @return bool
	 */
	public function getPostByIDAction() {

		$post = get_post( (int) $this->param( 'id' ) );

		if ( empty( $post ) ) {
			return false;
		}

		$result['id']    = $post->ID;
		$result['title'] = $post->post_title;
		$result['url']   = get_permalink( $post->ID );

		if ( isset( $result ) ) {
			return $result;
		}

		return false;
	}

	public function settingsAction() {
		if ( ( $custom = $this->param( 'custom' ) ) ) {
			switch ( $custom ) {
				case 'purge_cache':
					tve_ult_purge_cache();

					return tve_ult_get_campaigns( array( 'get_logs' => true ) );
					break;
				default:
					return array();
			}
		}
	}

	/**
	 * searches a tag by keywords, used in Display settings
	 */
	public function tagSearchAction() {
		if ( ! $this->param( 'tax' ) ) {
			wp_die( 0 );
		}

		$taxonomy = sanitize_key( $this->param( 'tax' ) );
		$tax      = get_taxonomy( $taxonomy );
		if ( ! $tax ) {
			wp_die( 0 );
		}

		if ( ! current_user_can( $tax->cap->assign_terms ) ) {
			wp_die( - 1 );
		}

		$s = wp_unslash( $this->param( 'q' ) );

		$comma = _x( ',', 'tag delimiter' );
		if ( ',' !== $comma ) {
			$s = str_replace( $comma, ',', $s );
		}
		if ( false !== strpos( $s, ',' ) ) {
			$s = explode( ',', $s );
			$s = $s[ count( $s ) - 1 ];
		}
		$s = trim( $s );

		if ( strlen( $s ) < 2 ) {
			wp_die();
		}

		$results = get_terms( $taxonomy, array(
			'name__like' => $s,
			'fields'     => 'id=>name',
			'number'     => 10,
		) );

		$json = array();
		foreach ( $results as $id => $name ) {
			$json [] = array(
				'label' => $name,
				'id'    => $id,
				'value' => $name,
			);
		}
		wp_send_json( $json );
	}

	/**
	 * searches a post by keywords
	 */
	public function postSearchAction() {
		if ( ! $this->param( 'q' ) ) {
			wp_die( 0 );
		}
		$s = wp_unslash( $this->param( 'q' ) );

		/** @var WP_Post[] $posts */
		$posts = get_posts( array(
			'posts_per_page' => 10,
			's'              => $s,
		) );

		$json = array();
		foreach ( $posts as $post ) {
			$json [] = array(
				'label' => $post->post_title,
				'id'    => $post->ID,
				'value' => $post->post_title,
			);
		}

		wp_send_json( $json );
	}
}
