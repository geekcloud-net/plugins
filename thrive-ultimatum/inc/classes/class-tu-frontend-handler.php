<?php

/**
 * Contains logic for frontend display of campaigns
 *
 * @class TU_Frontend_Handler
 */
class TU_Frontend_Handler {

	/**
	 * @var WP_Post the current campaign that's being displayed. ONLY available during the ajax-call for Lazy-load
	 */
	public $campaign;

	/**
	 * @var WP_Post[] the list of campaigns for which the display settings apply for the current request (ONLY AVAILABLE DURING THE MAIN REQUEST)
	 */
	protected $campaigns;

	/**
	 * called on template_redirect hook
	 *
	 * it should handle all redirections if a current page is a promotion page and the special offer conditions are not met
	 *
	 * for fixed date campaigns:
	 *      redirect to pre-offer before the campaign starts
	 *      redirect to expired after the campaign ended
	 *
	 * for rolling date campaigns:
	 *      redirect to pre-offer before the campaign start or if the campaign will be shown again (is not currently in the rolling date interval)
	 *      redirect to expired after the campaign has ended
	 *
	 * for evergreen campaigns:
	 *      redirect to pre-offer before the triggers are met
	 *      redirect to expired if the campaign has expired for the current user
	 *
	 * Also, this is the point where email addresses passed in as URL parameters are stored and timestamped
	 *
	 */
	public function hook_template_redirect() {

		if ( current_user_can( 'edit_posts' ) ) {
			return false;
		}

		$is_shop = function_exists( 'is_shop' ) && is_shop();
		if ( ! $is_shop && ( ! is_singular() || ! $this->should_display_campaign() ) ) {
			return;
		}

		$the_id = $is_shop ? get_option( 'woocommerce_shop_page_id' ) : get_the_ID();
		if ( empty( $the_id ) ) {
			return;
		}
		/**
		 * get all campaigns that have the current page set as a promotion (special offer) page
		 */

		if ( ! ( $campaigns = tve_ult_get_campaigns_for_promotion_page( $the_id ) ) ) {
			return;
		}

		$campaigns_logic = array();

		foreach ( $campaigns as $campaign ) {
			/* @var TU_Schedule_Abstract $schedule */
			$schedule = $campaign->tu_schedule_instance;

			$campaigns_logic[ $campaign->ID ] = true;

			if ( $schedule->should_redirect_pre_access() ) {
				$permalink                        = isset( $campaign->lockdown_settings['preaccess']['id'] ) ? get_post_permalink( $campaign->lockdown_settings['preaccess']['id'] ) : $campaign->lockdown_settings['preaccess']['value'];
				$campaigns_logic[ $campaign->ID ] = $permalink;
				continue;
			}

			/** if the campaign is evergreen save the email in the db if not exists */
			if ( $campaign->type == TVE_Ult_Const::CAMPAIGN_TYPE_EVERGREEN ) {

				/* @var $schedule TU_Schedule_Evergreen */

				//if we have the email in the url
				unset( $email_param );
				if ( isset( $_GET['tu_em'] ) && filter_var( $_GET['tu_em'], FILTER_VALIDATE_EMAIL ) && isset( $_GET['tu_id'] ) && $_GET['tu_id'] == $campaign->ID ) {
					$email_param = $params['lockdown']['email'] = $_GET['tu_em'];
				}

				if ( isset( $_COOKIE[ $schedule->cookie_name() ] ) ) {
					$cookie               = $schedule->get_cookie_data();
					$cookie               = $cookie['cookie'];
					$params['start_date'] = $cookie['start_date'];

					$has_email = $schedule->verify_cookie();

					if ( ! empty( $has_email ) ) {
						$params['lockdown']['email']     = $cookie['lockdown']['email'];
						$params['lockdown']['cookie_id'] = $cookie['lockdown']['log_id'];
					}
					/**
					 * If a visitor changes the email from the link and tries to access the special offer page, also save that email address with the same start date as the first one
					 */
					if ( isset( $email_param ) && $email_param != $params['lockdown']['email'] ) {
						$params['lockdown']['email'] = $email_param; // make sure the new email address is timestampped with the same date
					}
				}

				if ( isset( $params['lockdown']['email'] ) ) {
					$schedule->set_cookie_and_save_log( $params );
				} elseif ( isset( $_GET['tu_id'] ) && $_GET['tu_id'] == $campaign->ID ) {
					$permalink                        = isset( $campaign->lockdown_settings['expired']['id'] ) ? get_post_permalink( $campaign->lockdown_settings['expired']['id'] ) : $campaign->lockdown_settings['expired']['value'];
					$campaigns_logic[ $campaign->ID ] = $permalink;
					continue;
				}
			}

			if ( $schedule->should_redirect_expired() ) {
				$permalink                        = isset( $campaign->lockdown_settings['expired']['id'] ) ? get_post_permalink( $campaign->lockdown_settings['expired']['id'] ) : $campaign->lockdown_settings['expired']['value'];
				$campaigns_logic[ $campaign->ID ] = $permalink;
				continue;
			}

			/**
			 * we've found the 1st campaign applicable
			 * in this case we should let the logic be
			 */
			if ( isset( $campaigns_logic[ $campaign->ID ] ) && $campaigns_logic[ $campaign->ID ] === true ) {
				return;
			}
		}

		/**
		 * at this moment we should have at least 1 campaign with 1 redirect because:
		 * - if there is no campaign there is another if above
		 * - if there is an applicable campaign there is an if in loop
		 */
		wp_redirect( array_shift( $campaigns_logic ) );
		exit;
	}

	/**
	 * its logic has been moved on wp_print_footer_script action
	 *
	 * @see $this->hook_print_footer_scripts()
	 */
	public function hook_enqueue_scripts() {
		/**
		 * this function initializes $this->campaigns
		 * and we need them here because they are needed to be loaded for widget logic
		 */
		$this->get_available_campaigns();

		/**
		 * check if shop or single
		 */
		$is_shop = function_exists( 'is_shop' ) && is_shop();
		$the_id  = $is_shop ? get_option( 'woocommerce_shop_page_id' ) : get_the_ID();

		/**
		 * check if we should enqueue tooltip assets
		 */
		if ( ! empty( $the_id ) && $campaigns = tve_ult_get_campaigns_for_promotion_page( $the_id ) ) {
			$tooltip_url  = defined( 'TVE_DEBUG' ) && TVE_DEBUG ? 'js/tooltip.js' : 'js/dist/tooltip.min.js';
			$velocity_url = defined( 'TVE_DEBUG' ) && TVE_DEBUG ? 'js/velocity.js' : 'js/dist/velocity.min.js';

			wp_script_is( 'tve-ult-velocity' ) || wp_enqueue_script( 'tve-ult-velocity', TVE_Ult_Const::plugin_url( $velocity_url ), array( 'jquery' ), TVE_Ult_Const::PLUGIN_VERSION, true );
			wp_script_is( 'tve-ult-tooltip' ) || wp_enqueue_script( 'tve-ult-tooltip', TVE_Ult_Const::plugin_url( $tooltip_url ), array( 'jquery' ), TVE_Ult_Const::PLUGIN_VERSION, true );
			wp_style_is( 'tve-ult-styles' ) || wp_enqueue_style( 'tve-ult-styles', TVE_Ult_Const::plugin_url( 'css/styles.css' ), array(), TVE_Ult_Const::PLUGIN_VERSION );
		}
	}

	/**
	 * called on wp_print_footer_scripts WP hook
	 * it iterates through the campaigns and selects the ones that are viable for display
	 * the conditions for display are:
	 *      1) the Display settings for the campaign match
	 *      2) the schedule for the matched campaign applies
	 *
	 * we cannot verify point (2) at this point, because of caching plugins - point (2) will be verified in the ajax - load phase
	 *
	 * if a campaign can be displayed, than we enqueue the scripts and data required on frontend, in order to make the AJAX call that will display the campaign design
	 *
	 * we have the following possible triggers for a campaign:
	 *  a) time-based
	 *  b) user-based
	 *  c) event-based (e.g. a Conversion event for a campaign triggers the start of another campaign)
	 */
	public function hook_print_footer_scripts() {
		if ( ! $this->should_display_campaign() ) {
			return;
		}

		$available_campaigns = $this->get_available_campaigns();
		$shortcode_campaigns = TU_Shortcodes::get_campaigns();

		$return_ids = create_function( '$campaign', 'return $campaign->ID;' );

		/**
		 * campaign IDs that should only display ribbons and widgets - campaigns that have been matched by display settings
		 */
		$matched_display_settings_ids = array_map( $return_ids, $available_campaigns );

		$campaigns_by_id = array_unique( array_merge( $matched_display_settings_ids, $shortcode_campaigns ) );

		$scripts = array();

		if ( ! ( $campaigns_by_id ) ) {
			$scripts[] = TVE_Ult_Const::plugin_url( 'js/dist/no-campaign.min.js' );
		} else {
			$scripts[] = TVE_Ult_Const::plugin_url( 'js/dist/frontend.min.js' );
		}

		/**
		 * localize data required by scripts
		 */
		$this->localize_data( 'TVE_Ult_Data', $this->get_localization( $campaigns_by_id, $matched_display_settings_ids ) );

		/**
		 * print required scripts
		 */
		foreach ( $scripts as $script ) {
			$this->print_script( $script );
		}
	}

	/**
	 * Prints the json data in HTML
	 *
	 * @param string $name
	 * @param mixed $data
	 */
	public function localize_data( $name, $data ) {
		$output = "var $name = " . wp_json_encode( $data ) . ';';

		echo "<script type='text/javascript'>\n";
		echo "/* <![CDATA[ */\n";
		echo "$output\n";
		echo "/* ]]> */\n";
		echo "</script>\n";
	}

	/**
	 * Prints the script in HTML
	 *
	 * @param $src
	 */
	public function print_script( $src ) {

		if ( defined( 'TVE_DEBUG' ) && TVE_DEBUG ) {
			$src = preg_replace( '#\.min\.js$#', '.js', $src );
		}

		$src .= '?v=' . TVE_Ult_Const::PLUGIN_VERSION;

		?>		<script type="text/javascript" src="<?php echo $src ?>"></script><?php
	}

	/**
	 * fetch a campaign list that matches the current page based on the selected display settings
	 *
	 * we do this here, as this is dependent on the current request, and the schedule checking will be done via ajax, in order to circumvent caching plugins
	 *
	 * @return WP_Post[]
	 */
	protected function get_available_campaigns() {

		/**
		 * cache the campaigns during the current request
		 */
		if ( ! empty( $this->campaigns ) ) {
			return $this->campaigns;
		}

		$filtered = array();

		$all_campaigns = tve_ult_get_campaigns( array(
			'get_designs'  => true,
			'get_events'   => false,
			'get_settings' => false,
			'get_logs'     => false,
			'only_running' => true,
		) );;

		foreach ( $all_campaigns as $campaign ) {

			/**
			 * if the schedule for the campaign applies (e.g. the current time is in the required interval, or a cookie exists)
			 * we continue checking if the display settings apply
			 *
			 */
			if ( ! isset( $manager ) ) {
				require_once TVE_Ult_Const::plugin_path( 'inc/classes/display_settings/class-thrive-display-settings-manager.php' );
				$manager = new Thrive_Ult_Display_Settings_Manager();
				$manager->load_dependencies();
			}

			$saved_ptions = new Thrive_Ult_Campaign_Options( $campaign->ID );
			$saved_ptions->initOptions();

			$available = $saved_ptions->displayCampaign();

			/**
			 * a campaign is available if it has display settings and also has designs other than shortcodes
			 */
			if ( $available && count( $campaign->designs ) ) {
				$other_than_shortcode = false;
				foreach ( $campaign->designs as $design ) {
					if ( $design['post_type'] !== TVE_Ult_Const::DESIGN_TYPE_SHORTCODE ) {
						$other_than_shortcode = true;
						break;
					}
				}
				$available = $other_than_shortcode;
			}

			/**
			 * TODO: why do we check here for shortcodes if the shortcodes campaigns are loaded separately ? see line 193: $shortcode_campaigns = TU_Shortcodes::get_campaigns();
			 *
			 * the campaign has no display settings and has no other designs than shortcode
			 * but a shortcode design of campaign was used
			 *
			 * not sure why this check is made here
			 */
			if ( ! $available && in_array( $campaign->ID, array_values( TU_Shortcodes::get_campaigns() ) ) ) {
//				$available = true;
			}

			if ( $available ) {
				$filtered [] = $campaign;
			}
		}

		$this->campaigns = $filtered;

		return $filtered;
	}

	/**
	 * some general restrictions on pages where we are sure that no campaigns will be displayed
	 * e.g. on the TCB editor page, when previewing a lightbox / thrive leads form etc
	 *
	 * @return bool whether or not the current request can display a campaign
	 *
	 */
	public function should_display_campaign() {

		if ( is_editor_page_raw() || is_singular( array( TVE_Ult_Const::POST_TYPE_NAME_FOR_CAMPAIGN ) ) ) {
			return false;
		}

		if ( function_exists( 'tve_leads_is_preview_page' ) && tve_leads_is_preview_page() ) {
			return false;
		}

		/**
		 * Allow short-circuiting the TU campaign logic
		 *
		 * hook to this filter and return false if a campaign should not be displayed for the current request
		 *
		 * @since 0.0.1
		 *
		 * @param bool $can_display
		 */
		$can_display = apply_filters( 'thrive_ult_can_display_campaign', true );

		return $can_display;
	}

	/**
	 * gets the non-shortcode or mixed campaigns
	 *
	 * @param array $campaigns
	 *
	 * @return array
	 */
	public function get_nonshortcode_campaigns( $campaigns ) {
		$nonshortcode_campaigns = array();
		foreach ( $campaigns as $campaign ) {
			$other_than_shortcode = false;
			foreach ( $campaign->designs as $design ) {
				if ( $design['post_type'] !== TVE_Ult_Const::DESIGN_TYPE_SHORTCODE ) {
					$other_than_shortcode = true;
				}
				continue;
			}
			if ( $other_than_shortcode ) {
				$nonshortcode_campaigns[] = $campaign->ID;
			}
		}

		return $nonshortcode_campaigns;
	}

	/**
	 * Prints the json response that is required by frontend.js file
	 *
	 * @se insert_response()
	 *
	 * @param array $current
	 * @param array $params
	 *
	 * @return array|void
	 */
	public function ajax_load( $current = array(), $params = array() ) {

		if ( empty( $params ) ) {
			$params = $_REQUEST;
		}

		$this->check_evergreen_triggers();

		if ( empty( $params['campaign_ids'] ) || ! is_array( $params['campaign_ids'] ) ) {
			return empty( $params['hard_ajax'] ) ? array() : wp_die();
		}

		$matched_display_settings_ids = ! empty( $params['matched_display_settings'] ) ? $params['matched_display_settings'] : array();

		$applicable_campaigns   = $this->get_applicable_campaigns( $_REQUEST['campaign_ids'] );
		$nonshortcode_campaigns = $this->get_nonshortcode_campaigns( $applicable_campaigns );

		if ( empty( $applicable_campaigns ) ) {
			return empty( $params['hard_ajax'] ) ? array() : wp_die();
		}

		add_filter( 'option_tve_leads_ajax_load', '__return_false' );

		/**
		 * "enqueue" the default scripts and css needed
		 */
		tve_ult_enqueue_default_scripts();

		$response = array(
			'resources' => array(
				'css' => array(),
				'js'  => array(),
			),
			'body_end'  => '',
		);

		$shortcode_campaigns = $this->param( 'shortcode_campaign_ids', array() );
		$shortcode_campaigns = array_map( 'intval', $shortcode_campaigns );
		/* TODO: why is this the first campaign ?! */
		$main_campaign_id = reset( $nonshortcode_campaigns );

		foreach ( $applicable_campaigns as $campaign ) {
			$is_shortcode = in_array( $campaign->ID, $shortcode_campaigns );
			$is_main      = $campaign->ID === $main_campaign_id;

			/* TODO: we should rewrite this, this is only a hotfix */
			$is_main = $is_main && in_array( $campaign->ID, $matched_display_settings_ids );
			if ( $main_campaign_id && $is_main ) {
				$response[ $campaign->ID ] = $this->prepare_campaign_response( $campaign );
			} elseif ( $is_shortcode ) {
				$response[ $campaign->ID ] = $this->prepare_campaign_response( $campaign, true );
			}
		}

		$outer_html = apply_filters( 'tve_ult_body_end_html', '' );

		$response['resources']['css'] = $this->get_outer_css( 'tve' );
		$response['resources']['js']  = $this->get_outer_scripts( 'tve' );

		/** print footer javascript */
		ob_start();
		tve_print_footer_events();
		echo $outer_html;
		$response['body_end'] = ob_get_contents();
		ob_end_clean();

		return empty( $params['hard_ajax'] ) ? $response : wp_send_json( $response );
	}

	/**
	 * Foreach campaign we need to have its response: html designs, assets, etc
	 *
	 * @param $campaign
	 * @param $is_shortcode
	 *
	 * @return array|void
	 */
	public function prepare_campaign_response( $campaign, $is_shortcode = false ) {
		/**
		 * Registers an impression for the campaign
		 */
		if ( ! isset( $_COOKIE[ TVE_Ult_Const::COOKIE_IMPRESSION . $campaign->ID ] ) ) {

			$email_log = tve_ult_get_email_log( $campaign->ID, $this->param( 'tu_em' ) );

			/**
			 * Action filter - register an impression for a campaign
			 *
			 * @since 0.1
			 *
			 * @param WP_Post $campaign
			 */
			if ( ! tve_dash_is_crawler() && ( empty( $email_log ) || empty( $email_log['has_impression'] ) ) ) {

				do_action( 'tve_ult_action_impression', $campaign );

				if ( ! empty( $email_log ) ) {
					$email_log['has_impression'] = 1;
					tve_ult_save_email_log( $email_log );
				}
			}
			/*
			 * expiration dates for cookies:
			 * - for evergreen campaigns: the number of days entered in the "Don't show this campaign until" field
			 * - for others: 1 year
			 * also modifies the global $_COOKIE variable - so that it's available later in the same request if required
			 */
			$expiry = tve_ult_current_time( 'timestamp' ) + YEAR_IN_SECONDS;
			if ( $campaign->type === TVE_Ult_Const::CAMPAIGN_TYPE_EVERGREEN && ! empty( $campaign->settings ) && ! empty( $campaign->settings['end'] ) ) {
				$cookie = $campaign->settings['end'] + $campaign->settings['duration'];
				$expiry = tve_ult_current_time( 'timestamp' ) + $cookie * DAY_IN_SECONDS;
			}
			$now = tve_ult_current_time( 'Y-m-d H:i:s' );
			setcookie( TVE_Ult_Const::COOKIE_IMPRESSION . $campaign->ID, $now, $expiry, '/' );
			$_COOKIE[ TVE_Ult_Const::COOKIE_IMPRESSION . $campaign->ID ] = $now;
		}

		$this->campaign = $campaign;
		/** @var TU_Schedule_Abstract $schedule */
		$schedule = $campaign->tu_schedule_instance;

		$event   = new TU_Event( $campaign );
		$designs = $event->get_designs();

		/**
		 * $html will containing a key-value pair for the designs
		 * design_id => html code
		 */
		$html          = array();
		$design_exists = false;
		foreach ( $designs as $design ) {
			if ( ! $is_shortcode || ( $is_shortcode && $design['post_type'] === TVE_Ult_Const::DESIGN_TYPE_SHORTCODE ) ) {
				tve_ult_enqueue_design_scripts( $design );

				if ( $design['post_type'] === TVE_Ult_Const::DESIGN_TYPE_SHORTCODE ) {

					/**
					 * The shortcode will contain design attribute id only for the main state
					 * but the TU_Event returns the exact state that need to be displayed
					 * so we need to display a state on main placeholder
					 *
					 * @see TU_Shortcode_Countdown->placeholder() and TU_Shortcodes how the placeholders are rendered
					 */
					$key = $design['post_type'] . '-';
					$key .= $design['parent_id'] ? $design['parent_id'] : $design['id'];

					$html[ $key ] = tve_ult_get_design_html( $design );
				} else {
					$html[ $design['post_type'] ] = tve_ult_get_design_html( $design );
				}
				$design_exists = true;
			}
		}
		if ( $design_exists ) {
			$end_date = $schedule->get_end_date();
			$response = array(
				'html'             => $html,
				'resources'        => isset( $GLOBALS['tve_ult_res'] ) ? $GLOBALS['tve_ult_res'] : array(),
				'timer_components' => tve_ult_get_date_components( $end_date ),
			);
			/* for evergreen campaigns we need to also send the exact second at which the campaign started */
			if ( $schedule instanceof TU_Schedule_Evergreen ) {
				$response['timer_components']['sec'] = date( 's', strtotime( $end_date ) );
			}

			return $response;
		} else {
			return array();
		}
	}

	/**
	 * Check the wp_scripts() and returns the registered scripts based on $filter
	 *
	 * @param string $filter
	 *
	 * @return array
	 */
	public function get_outer_scripts( $filter ) {
		$scripts = array();

		$wp_scripts = wp_scripts();

		foreach ( $wp_scripts->queue as $handle ) {
			if ( ! isset( $wp_scripts->registered[ $handle ] ) || strpos( $handle, $filter ) === false ) {
				continue;
			}

			$obj = $wp_scripts->registered[ $handle ];

			if ( null === $obj->ver ) {
				$ver = '';
			} else {
				$ver = $obj->ver ? $obj->ver : $wp_scripts->default_version;
			}

			if ( ! $obj->src ) {
				continue;
			}

			$src = $obj->src;

			if ( ! preg_match( '|^(https?:)?//|', $src ) && ! ( $wp_scripts->content_url && 0 === strpos( $src, $wp_scripts->content_url ) ) ) {
				$src = $wp_scripts->base_url . $src;
			}

			if ( ! empty( $ver ) ) {
				$src = add_query_arg( 'ver', $ver, $src );
			}

			/** This filter is documented in wp-includes/class.wp-scripts.php */
			$src = esc_url( apply_filters( 'script_loader_src', $src, $handle ) );

			if ( ! $src ) {
				continue;
			}

			$scripts[ $handle ] = $src;
		}

		return $scripts;
	}

	/**
	 * Check the wp_styles() and returns the registered styles based on $filter
	 *
	 * @param string $filter
	 *
	 * @return array
	 */
	public function get_outer_css( $filter ) {
		$css = array();

		$wp_styles = wp_styles();

		foreach ( $wp_styles->queue as $handle ) {
			if ( ! isset( $wp_styles->registered[ $handle ] ) && strpos( $handle, $filter ) !== false ) {
				continue;
			}

			$obj = $wp_styles->registered[ $handle ];
			if ( null === $obj->ver ) {
				$ver = '';
			} else {
				$ver = $obj->ver ? $obj->ver : $wp_styles->default_version;
			}

			$css[ $handle ] = $wp_styles->_css_href( $obj->src, $ver, $handle );
		}

		return $css;
	}

	/**
	 * check for a Conversion event that has the trigger set as visit to page
	 * it should loop the $_COOKIE variable and for each campaign that's registered in the cookie,
	 *
	 * Also, check for any evergreen campaigns that should be started during this request (e.g. first page visit, visit to a specific page)
	 *
	 * @return mixed
	 */
	public function ajax_conversion_event_check() {
		$post_id = $this->param( 'post_id' );

		$this->check_evergreen_triggers();

		foreach ( $_COOKIE as $key => $value ) {
			/**
			 * we might have evergreen|other campaign started which are not visible on this request
			 * evergreen campaign has COOKIE_NAME and the others have COOKIE_IMPRESSION
			 */
			$cookie_impression = strpos( $key, TVE_Ult_Const::COOKIE_IMPRESSION ) !== false;
			$cookie_evergreen  = strpos( $key, TVE_Ult_Const::COOKIE_NAME ) !== false;

			if ( ! $cookie_impression && ! $cookie_evergreen ) {
				continue;
			}

			/**
			 * find on which cookie determines the campaign id
			 */
			$cookie_name = $cookie_impression ? TVE_Ult_Const::COOKIE_IMPRESSION : TVE_Ult_Const::COOKIE_NAME;

			$campaign_id = (int) str_replace( $cookie_name, '', $key );
			$campaign    = tve_ult_get_campaign( $campaign_id, array(
				'get_settings' => true,
				'get_events'   => true,
			) );
			if ( empty( $campaign ) ) {
				continue;
			}

			/** do the conversion if the campaign conversion event match the post_id */
			$campaign->tu_schedule_instance->check_specific_events( $campaign, $post_id );
		}

		return 'conversion checked';
	}

	/**
	 * @param array $campaign_ids usually displayable campaigns
	 *
	 * @return bool|WP_Post
	 */
	public function get_applicable_campaigns( $campaign_ids ) {

		/**
		 * campaigns will come by default ordered, we can take the first one for which the schedule applies
		 */
		$campaign_ids         = array_map( 'intval', $campaign_ids );
		$post_id              = $this->param( 'post_id' );
		$applicable_campaigns = array();

		$campaigns = tve_ult_get_campaigns( array(
			'get_designs'  => true,
			'get_events'   => true,
			'get_settings' => true,
			'get_logs'     => false,
			'lockdown'     => true,
			'only_running' => true,
		) );

		$return_ids      = create_function( '$campaign', 'return $campaign->ID;' );
		$campaigns_by_id = array_combine( array_map( $return_ids, $campaigns ), $campaigns );

		$singular    = $this->param( 'is_singular' );
		$is_singular = ! empty( $singular );

		/**
		 * first loop - check for any possible conversion events and end any campaigns that have had a conversion
		 */
		$campaign_conversions = array();
		foreach ( $campaigns as $campaign ) {
			/** @var TU_Schedule_Abstract $schedule */
			$schedule = $campaign->tu_schedule_instance;

			/** check if a non displayable campaign has a conversion event with specific trigger that needs to do_conversion() */

			if ( $is_singular ) {
				/** the real post_id is received only in singular requests */
				$campaign_conversions[ $campaign->ID ] = $schedule->check_specific_events( $campaign, $post_id );
			}
		}

		/**
		 * second loop: check if we need to display a specific campaign, as a result of a conversion event
		 * $campaign_ids are sorted in the order of priority, so it's ok to take on the one with the highest priority
		 */
		foreach ( $campaign_ids as $campaign_id ) {

			if ( ! isset( $campaigns_by_id[ $campaign_id ] ) ) {
				continue;
			}

			$campaign = $campaigns_by_id[ $campaign_id ];
			$schedule = $campaign->tu_schedule_instance;

			if ( isset( $_COOKIE[ $schedule->cookie_force_display() ] ) && ! isset( $_COOKIE[ $schedule->cookie_end_name() ] ) ) {
				$applicable_campaigns[] = $campaign;
			}
		}

		/**
		 * lastly, check for "regular" campaign triggers - intervals, first visits to site etc
		 */
		foreach ( $campaign_ids as $campaign_id ) {

			if ( ! isset( $campaigns_by_id[ $campaign_id ] ) ) {
				continue;
			}

			$campaign = $campaigns_by_id[ $campaign_id ];
			$schedule = $campaign->tu_schedule_instance;
			/** if the campaign has a conversion event then it cannot be applied */
			if ( empty( $campaign_conversions[ $campaign_id ] ) && $schedule->applies() ) {
				$applicable_campaigns[] = $campaign;
			}
		}

		return $applicable_campaigns;
	}

	/**
	 * get the first campaign which has a schedule that matches
	 *
	 * @param array $campaign_ids usually displayable campaigns
	 *
	 * @return bool|WP_Post
	 */
	public function get_applicable_campaign( $campaign_ids ) {
		/**
		 * campaigns will come by default ordered, we can take the first one for which the schedule applies
		 */
		$campaign_ids = array_map( 'intval', $campaign_ids );
		$post_id      = $this->param( 'post_id' );

		$campaigns = tve_ult_get_campaigns( array(
			'get_designs'  => false,
			'get_events'   => true,
			'get_settings' => true,
			'get_logs'     => false,
			'lockdown'     => true,
			'only_running' => true,
		) );

		$return_ids      = create_function( '$campaign', 'return $campaign->ID;' );
		$campaigns_by_id = array_combine( array_map( $return_ids, $campaigns ), $campaigns );

		$singular    = $this->param( 'is_singular' );
		$is_singular = ! empty( $singular );

		/**
		 * first loop - check for any possible conversion events and end any campaigns that have had a conversion
		 */
		$campaign_conversions = array();
		foreach ( $campaigns as $campaign ) {
			/** @var TU_Schedule_Abstract $schedule */
			$schedule = $campaign->tu_schedule_instance;

			/** check if a non displayable campaign has a conversion event with specific trigger that needs to do_conversion() */

			if ( $is_singular ) {
				/** the real post_id is received only in singular requests */
				$campaign_conversions[ $campaign->ID ] = $schedule->check_specific_events( $campaign, $post_id );
			}
		}

		/**
		 * second loop: check if we need to display a specific campaign, as a result of a conversion event
		 * $campaign_ids are sorted in the order of priority, so it's ok to take on the one with the highest priority
		 */
		foreach ( $campaign_ids as $campaign_id ) {
			$campaign = $campaigns_by_id[ $campaign_id ];
			$schedule = $campaign->tu_schedule_instance;
			if ( isset( $_COOKIE[ $schedule->cookie_force_display() ] ) && ! isset( $_COOKIE[ $schedule->cookie_end_name() ] ) ) {
				return $campaign;
			}
		}

		/**
		 * lastly, check for "regular" campaign triggers - intervals, first visits to site etc
		 */
		foreach ( $campaign_ids as $campaign_id ) {
			$campaign = $campaigns_by_id[ $campaign_id ];
			$schedule = $campaign->tu_schedule_instance;

			/** if the campaign has a conversion event then it cannot be applied */
			if ( empty( $campaign_conversions[ $campaign_id ] ) && $schedule->applies() ) {
				return $campaign;
			}
		}

		return false;
	}

	/**
	 * helper function
	 *
	 * @return string admin-ajax action user for ajax-loading campaign designs on frontend
	 */
	public function ajax_load_action() {
		return 'tve_ult_ajax_load';
	}

	/**
	 * helper function
	 *
	 * @return string admin-ajax action user for checking if any conversion event applies to the current page
	 */
	public function conversion_events_action() {
		return 'tve_ult_conversion_event';
	}

	/**
	 * prepares the localization of frontend javascript
	 *
	 * @param array $campaigns a list of campaigns that matches the display settings for the current request
	 * @param array $matched_display_settings_ids campaigns campaigns that have matched the display settings for the current page
	 *
	 * @return array
	 */
	public function get_localization( $campaigns, $matched_display_settings_ids ) {

		$post_id = null;

		if ( function_exists( 'is_shop' ) && is_shop() ) {
			$post_id = get_option( 'woocommerce_shop_page_id' );
		} else {
			$post_id = get_the_ID();
		}

		$campaign_ids = array();

		/**
		 * we need to localize emails from cookies so that we know if there are logs in db to register impression or not
		 * Flow:
		 * 1. localize emails
		 * 2. do tu request for designs with localized email
		 * 3. check for emails based on localized email
		 * 4. update log
		 *
		 * @see ajax_load
		 * @see prepare_campaign_response
		 */
		$cookie_email = '';

		foreach ( $campaigns as $campaign ) {
			$campaign_id     = $campaign instanceof WP_Post ? $campaign->ID : $campaign;
			$campaign_ids [] = $campaign_id;

			$campaign_cookie_name = TVE_Ult_Const::COOKIE_NAME . $campaign_id;

			if ( empty( $cookie_email ) && isset( $_COOKIE[ $campaign_cookie_name ] ) ) {
				$cookie_value = unserialize( stripcslashes( $_COOKIE[ $campaign_cookie_name ] ) );
				$cookie_email = ! empty( $cookie_value['lockdown'] ) ?
					! empty( $cookie_value['lockdown']['email'] ) ? $cookie_value['lockdown']['email'] : ''
					: '';
			}
		}

		$frontend_data = array(
			'ajaxurl'                  => admin_url( 'admin-ajax.php' ),
			'ajax_load_action'         => $this->ajax_load_action(),
			'conversion_events_action' => $this->conversion_events_action(),
			// at this point, campaign_ids should be ordered. In the ajax call, it's sufficient to just display the first one that matches
			'shortcode_campaign_ids'   => TU_Shortcodes::get_campaigns(),
			'matched_display_settings' => $matched_display_settings_ids,
			'campaign_ids'             => $campaign_ids,
			'post_id'                  => $post_id,
			'is_singular'              => is_singular(),
			'tu_em'                    => $this->param( 'tu_em', $cookie_email ),
		);

		/**
		 * Allows inserting custom data into the frontend javascript localization object
		 *
		 * @since 0.0.1
		 *
		 * @param array $frontend_data
		 */
		return apply_filters( 'thrive_ult_js_localize', $frontend_data );
	}

	/**
	 * getter. Returns the list of available campaigns
	 *
	 * @return WP_Post[]
	 */
	public function get_campaigns() {
		return $this->campaigns;
	}

	protected function param( $key, $default = null ) {
		return isset( $_POST[ $key ] ) ? $_POST[ $key ] : ( isset( $_REQUEST[ $key ] ) ? $_REQUEST[ $key ] : $default );
	}

	/**
	 * Check for any campaigns that might have the current request as a trigger ( applies to evergreen campaigns ) and set the start cookie.
	 *
	 * @return void
	 */
	public function check_evergreen_triggers() {
		/**
		 * This only applies to evergreen campaigns - only fetch evergreen campaigns
		 */
		$evergreen_campaigns = tve_ult_get_campaigns( array(
			'only_running'  => true,
			'get_designs'   => false,
			'get_events'    => false,
			'campaign_type' => TVE_Ult_Const::CAMPAIGN_TYPE_EVERGREEN,
			'get_settings'  => true,
		) );
		foreach ( $evergreen_campaigns as $campaign ) {
			//this will trigger any campaigns for which the current request acts as a trigger
			$campaign->tu_schedule_instance->applies();
		}
	}
}
