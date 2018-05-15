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
 * Functions which operate with DB
 *
 * e.g.: CRUD functions
 */

/**
 * Save a campaign
 *
 * @param $model array
 *
 * @return false|int id of model or false on error
 */
function tve_ult_save_campaign( $model ) {
	if ( ! empty( $model['ID'] ) ) {
		unset( $model[ $model['ID'] ] );
		$item = get_post( $model['ID'] );
		if ( $item && get_post_type( $item ) === TVE_Ult_Const::POST_TYPE_NAME_FOR_CAMPAIGN ) {
			$id = wp_update_post( $model );
		}
	} else {
		$default = array(
			'post_type'   => TVE_Ult_Const::POST_TYPE_NAME_FOR_CAMPAIGN,
			'post_status' => 'publish',
			'status'      => TVE_Ult_Const::CAMPAIGN_STATUS_PAUSED,
		);
		if ( ! empty( $model['copy'] ) ) {
			$model['post_title'] = __( 'Copy of ', TVE_Ult_Const::T ) . $model['post_title'];
		}
		$id = wp_insert_post( array_merge( $default, $model ) );
	}

	if ( empty( $id ) || is_wp_error( $id ) || $id == 0 ) {
		return false;
	}

	if ( isset( $model['order'] ) ) {
		update_post_meta( $id, TVE_Ult_Const::META_NAME_FOR_CAMPAIGN_ORDER, (int) $model['order'] );
	}

	/**
	 * adds campaign designs/states/events if current item is copy of another campaign
	 */
	if ( ! empty( $model['copy'] ) ) {
		$model['ID']     = $id;
		$model['status'] = TVE_Ult_Const::CAMPAIGN_STATUS_PAUSED;
		tve_ult_copy_campaign_attributes( $model );
	}

	/**
	 * adds campaign values from attribute template
	 */
	if ( isset( $model['template_values'] ) && $model['template_values'] ) {
		$campaign_template     = TVE_Ult_Const::campaign_template_details( $model['tpl'] );
		$model['type']         = $campaign_template['type'];
		$model['status']       = $campaign_template['status'];
		$model['rolling_type'] = $campaign_template['rolling_type'];
		$model['settings']     = $campaign_template['settings'];
		tve_ult_update_campaign_from_template( $campaign_template, $id );
		unset( $model['template_values'] );
	}
	if ( isset( $model['status'] ) ) {
		update_post_meta( $id, TVE_Ult_Const::META_NAME_FOR_STATUS, $model['status'] );
	}

	/**
	 * save campaign type
	 */

	if ( ! empty( $model['type'] ) ) {
		update_post_meta( $id, TVE_Ult_Const::META_NAME_FOR_CAMPAIGN_TYPE, $model['type'] );
		update_post_meta( $id, TVE_Ult_Const::META_NAME_FOR_ROLLING_TYPE, $model['rolling_type'] );
		update_post_meta( $id, TVE_Ult_Const::META_NAME_FOR_CAMPAIGN_SETTINGS, $model['settings'] );
		$start_event = tve_ult_save_start_event( $id );
		/**
		 * delete out-ranged events
		 */
		tve_ult_clear_timeline( $start_event['campaign_id'], $start_event['days'] * 24 + $start_event['hours'] );
	}

	/**
	 * Save Lockdown options
	 */

	if ( isset( $model['lockdown'] ) ) {
		update_post_meta( $id, TVE_Ult_Const::META_NAME_FOR_LOCKDOWN, $model['lockdown'] );
		if ( $model['lockdown'] == true ) {
			update_post_meta( $id, TVE_Ult_Const::META_NAME_FOR_LOCKDOWN_SETTINGS, $model['lockdown_settings'] );
		}
	}

	return $id;
}

/**
 * Saves the date settings
 *
 * @param $model
 *
 * @return bool
 */
function tve_ult_save_date_settings( $model ) {
	if ( $model ) {
		update_option( TVE_Ult_Const::SETTINGS_TIME_ZONE, $model['offset'] );
		update_option( TVE_Ult_Const::SETTINGS_DATE_FORMAT, $model['date_format'] );
		update_option( TVE_Ult_Const::SETTINGS_TIME_FORMAT, $model['time_format'] );
		/* Also updates the gmt_offset value */
		$gmt_offset = tve_ult_gmt_offset_from_tzstring( $model['offset'] );
		update_option( TVE_Ult_Const::SETTINGS_TIME_ZONE_OFFSET, $gmt_offset );

		return true;
	}

	return false;
}

/**
 * Copies events, designs and states from campaign
 *
 * @param array $model
 *
 * @return true/false
 */
function tve_ult_copy_campaign_attributes( $model ) {
	if ( empty( $model ) ) {
		return false;
	}
	$original = tve_ult_get_campaign( $model['copy'] );
	if ( empty( $original ) ) {
		return false;
	}

	$designs = tve_ult_get_designs( $model['copy'], array( 'parent_id' => - 1 ) );

	foreach ( $designs as $design ) {
		if ( $design['parent_id'] == 0 ) {
			$design['post_parent'] = $model['ID'];
			$old_design_id         = $design['id'];
			$design['id']          = '';
			$design_id             = tve_ult_save_design( $design );
			foreach ( $designs as $state ) {
				if ( $state['parent_id'] == $old_design_id ) {
					$state['parent_id']   = $design_id['id'];
					$state['post_parent'] = $model['ID'];
					$state['id']          = '';
					tve_ult_save_design( $state );
				}
			}
		}
	}

	require_once plugin_dir_path( __FILE__ ) . 'classes/display_settings/class-thrive-ult-campaign-options.php';
	$campaign_options = new Thrive_Ult_Campaign_Options( $model['ID'] );
	$campaign_options->copyOptions( $model['copy'] );

	return true;
}

/**
 * Saves events, designs and states for campaign created from attribute template
 *
 * @param array $campaign_template
 * @param mixed $id
 *
 * @return true/false
 */
function tve_ult_update_campaign_from_template( $campaign_template, $id ) {
	if ( empty( $campaign_template ) || empty( $id ) ) {
		return false;
	}

	if ( isset( $campaign_template['designs'] ) ) {
		$campaign_template_designs = $campaign_template['designs'];
		$designs_created           = array();
		$states_created            = array();
		foreach ( $campaign_template_designs as $design_key => $design ) {
			$design['post_parent']                  = $id;
			$design[ TVE_Ult_Const::FIELD_CONTENT ] = tve_ult_editor_get_template_content( $design );
			$added_design                           = tve_ult_save_design( $design );
			$states                                 = $design['states'];
			$designs_created[ $design_key ]         = $added_design['id'];

			foreach ( $states as $state_key => $state ) {
				$state['post_parent']                  = $id;
				$state['parent_id']                    = $added_design['id'];
				$state[ TVE_Ult_Const::FIELD_CONTENT ] = tve_ult_editor_get_template_content( $state );
				$added_state                           = tve_ult_save_design( $state );
				$states_created[ $state_key ]          = $added_state['id'];
			}
		}
		if ( isset( $campaign_template['events'] ) ) {
			$campaign_template_events = $campaign_template['events'];
			foreach ( $campaign_template_events as $state_key => $event ) {
				$event['campaign_id'] = $id;
				$event_actions        = $event['actions'];
				foreach ( $event_actions as $action_key => $action ) {
					$event['actions'][ $action_key ]['design'] = isset( $designs_created[ $action['design'] ] ) ? $designs_created[ $action['design'] ] : '';
					$event['actions'][ $action_key ]['state']  = isset( $states_created[ $action['state'] ] ) ? $states_created[ $action['state'] ] : '';
				}
				tve_ult_save_event( $event );
			}
		}
	}

	return true;

}

/**
 * Get the list of campaigns based on filters param
 *
 * @param array $filters allows passing query values to the get_posts function, and some extra values
 *
 * @return array $posts
 */
function tve_ult_get_campaigns( $filters = array() ) {
	$defaults = array(
		'posts_per_page' => - 1,
		'post_type'      => TVE_Ult_Const::POST_TYPE_NAME_FOR_CAMPAIGN,
		'meta_key'       => TVE_Ult_Const::META_NAME_FOR_CAMPAIGN_ORDER,
		'orderby'        => 'meta_value_num',
		'order'          => 'ASC',
		'get_designs'    => true,
		'get_events'     => true,
		'get_settings'   => true,
		'get_logs'       => false,
		'only_running'   => false,
		'lockdown'       => false,
	);

	$filters = array_merge( $defaults, $filters );

	if ( isset( $filters['campaign_type'] ) ) {
		/* specifically overwrite the meta_query options */
		$filters['meta_query'] = array(
			array(
				'key'     => TVE_Ult_Const::META_NAME_FOR_CAMPAIGN_TYPE,
				'value'   => $filters['campaign_type'],
				'compare' => ' = ',
			),
		);
	}

	/**
	 * only retrieve started campaigns
	 */
	if ( ! empty( $filters['only_running'] ) ) {
		$filters['meta_query']    = isset( $filters['meta_query'] ) ? $filters['meta_query'] : array();
		$filters['meta_query'] [] = array(
			'key'   => TVE_Ult_Const::META_NAME_FOR_STATUS,
			'value' => TVE_Ult_Const::CAMPAIGN_STATUS_RUNNING,
		);
	}

	/**
	 * SUPP-5262 stupid conflict with s2Member - this filter is causing the page to blank out
	 * Their code is completely un readable so we're just gonna remove the filter
	 */
	remove_filter( 'pre_get_posts', 'c_ws_plugin__s2member_security::security_gate_query', 100 );

	$posts = get_posts( $filters );

	require_once TVE_Ult_Const::plugin_path() . 'inc/classes/display_settings/class-thrive-display-settings-manager.php';

	foreach ( $posts as $post ) {
		$post->type         = get_post_meta( $post->ID, TVE_Ult_Const::META_NAME_FOR_CAMPAIGN_TYPE, true );
		$post->rolling_type = get_post_meta( $post->ID, TVE_Ult_Const::META_NAME_FOR_ROLLING_TYPE, true );
		$post->status       = get_post_meta( $post->ID, TVE_Ult_Const::META_NAME_FOR_STATUS, true );
		$post->order        = (int) get_post_meta( $post->ID, TVE_Ult_Const::META_NAME_FOR_CAMPAIGN_ORDER, true );
		if ( ! $post->status ) {
			$post->status = TVE_Ult_Const::CAMPAIGN_STATUS_PAUSED;
		}
		$post->settings  = tve_ult_get_campaign_settings( $post->ID );
		$post->linked_to = get_post_meta( $post->ID, TVE_Ult_Const::META_NAME_FOR_CAMPAIGN_LINK, true );
		if ( ! empty( $filters['get_logs'] ) ) {
			$post->impressions     = tve_ult_count_campaign_logs( $post );
			$post->conversions     = 0;
			$post->conversion_rate = 0;
			if ( $post->impressions && tve_ult_campaign_has_conversions( $post ) ) {
				$post->conversions     = tve_ult_count_campaign_logs( $post, TVE_Ult_Const::LOG_TYPE_CONVERSION );
				$post->conversion_rate = sprintf( '%.2f', 100 * $post->conversions / $post->impressions );
			}
			$post->has_event_logs = ( $post->impressions > 1 || $post->conversions );
		}

		/** also append the designs to the post */
		$post->designs = array();
		if ( ! empty( $filters['get_designs'] ) ) {
			$post->designs = tve_ult_get_designs( $post->ID );
		}

		require_once TVE_Ult_Const::plugin_path( 'inc/classes/display_settings/class-thrive-ult-campaign-options.php' );
		$display_settings = new Thrive_Ult_Campaign_Options( $post->ID );
		$display_settings->initOptions();

		$post->has_display_settings = $display_settings->checkForAnyOptionChecked();

		/** also append the events to the post */
		if ( ! empty( $filters['get_events'] ) ) {
			$post->timeline          = array();
			$post->conversion_events = array();

			$events = tve_ult_get_events( array(
				'campaign_id' => $post->ID,
				'type'        => array(
					TVE_Ult_Const::EVENT_TYPE_START,
					TVE_Ult_Const::EVENT_TYPE_TIME,
					TVE_Ult_Const::EVENT_TYPE_CONV,
				),
				'order'       => array(
					'days'  => 'desc',
					'hours' => 'desc',
				),
			) );

			foreach ( $events as $event ) {
				if ( $event['type'] === TVE_Ult_Const::EVENT_TYPE_CONV ) {
					$post->conversion_events[] = $event;
				} else {
					$post->timeline[] = $event;
				}
			}
		}
		if ( ! empty( $filters['lockdown'] ) ) {
			$post->lockdown          = get_post_meta( $post->ID, TVE_Ult_Const::META_NAME_FOR_LOCKDOWN, true );
			$post->lockdown_settings = tve_ult_get_lockdown_settings( $post->ID );

		}
	}

	if ( ! empty( $filters['get_settings'] ) ) {
		foreach ( $posts as $post ) {
			$post->tu_schedule_instance = TU_Schedule_Abstract::factory( $post->ID );
			if ( $post->type === TVE_Ult_Const::CAMPAIGN_TYPE_ROLLING ) {
				$post->settings['rolling_type'] = $post->rolling_type;
			}
			$post->tu_schedule_instance->set( $post->settings );
			$post->tu_schedule_instance->set_conversion_events( $post->conversion_events );
			$post->tu_schedule_instance->set_lockdown( $post->lockdown );
		}
	}

	return $posts;
}

/**
 * Set post's status on trash
 *
 * @param      $id
 * @param bool $force_delete whether or not to bypass trash and delete the campaign permanently
 *
 * @return false | int number of deleted rows or false on error
 */
function tve_ult_delete_campaign( $id, $force_delete = true ) {
	global $tve_ult_db;

	$post = get_post( $id );

	if ( empty( $post ) || $post->post_type !== TVE_Ult_Const::POST_TYPE_NAME_FOR_CAMPAIGN ) {
		return false;
	}

	if ( $force_delete ) {
		/**
		 * also delete designs, events, logs, saved emails and display settings
		 */
		$tve_ult_db->delete_designs( $post->ID );
		$tve_ult_db->delete_events( $post->ID );
		$tve_ult_db->delete_display_settings( $post->ID );
		$tve_ult_db->delete_event_logs( $post->ID );
		$tve_ult_db->delete_email_logs( $post->ID );
	}

	$linked_ids = get_post_meta( $id, TVE_Ult_Const::META_NAME_FOR_CAMPAIGN_LINK, true );

	if ( ! empty( $linked_ids ) ) {
		foreach ( $linked_ids as $link_id ) {
			$events = tve_ult_get_events( array(
				'campaign_id' => $link_id,
				'type'        => TVE_Ult_Const::EVENT_TYPE_CONV,
				'order'       => array(
					'days'  => 'desc',
					'hours' => 'desc',
				),
			) );

			foreach ( $events as $event ) {
				if ( $event['trigger_options']['event'] === TVE_Ult_Const::TRIGGER_OPTION_MOVE && $event['trigger_options']['end_id'] === $id ) {
					$event['trigger_options']['event']  = TVE_Ult_Const::TRIGGER_OPTION_END;
					$event['trigger_options']['end_id'] = '';

					$result = tve_ult_save_event( $event );

					if ( ! isset( $result ) ) {
						return false;
					}
				}
			}
		}
	}

	if ( $force_delete ) {
		$deleted = wp_delete_post( $post->ID, true );
	} else {
		$post->post_status = 'trash';
		$deleted           = wp_update_post( $post );
	}

	if ( $deleted === 0 || is_wp_error( $deleted ) ) {
		return false;
	}

	return $deleted;
}

/**
 * Get campaign based on filters
 *
 * @param       $id
 * @param array $filters
 *      get_designs => false by default
 *
 * @return false|WP_Post on success or false on error
 */
function tve_ult_get_campaign( $id, $filters = array() ) {
	$defaults = array(
		'get_designs'       => false,
		'designs_hierarchy' => false, // this allows getting all the designs
		'get_events'        => false,
		'get_settings'      => false,
		'get_logs'          => true,
	);
	$filters  = array_merge( $defaults, $filters );

	$item = get_post( $id );

	if ( empty( $item ) || ! in_array( $item->post_type, array( TVE_Ult_Const::POST_TYPE_NAME_FOR_CAMPAIGN ) ) ) {
		return false;
	}

	/** also append the designs to the post */
	$item->designs = array();
	if ( ! empty( $filters['get_designs'] ) ) {
		$item->designs = empty( $filters['designs_hierarchy'] ) ? tve_ult_get_designs( $id ) : tve_ult_get_designs_hierarchy( $id );
	}

	$item->type         = get_post_meta( $id, TVE_Ult_Const::META_NAME_FOR_CAMPAIGN_TYPE, true );
	$item->rolling_type = get_post_meta( $id, TVE_Ult_Const::META_NAME_FOR_ROLLING_TYPE, true );
	$item->status       = get_post_meta( $id, TVE_Ult_Const::META_NAME_FOR_STATUS, true );
	$item->linked_to    = get_post_meta( $id, TVE_Ult_Const::META_NAME_FOR_CAMPAIGN_LINK, true );
	$item->settings     = tve_ult_get_campaign_settings( $id );

	if ( ! empty( $filters['get_logs'] ) ) {
		$item->impressions     = tve_ult_count_campaign_logs( $id );
		$item->conversions     = 0;
		$item->conversion_rate = 0;
		if ( $item->impressions && tve_ult_campaign_has_conversions( $id ) ) {
			$item->conversions     = tve_ult_count_campaign_logs( $id, TVE_Ult_Const::LOG_TYPE_CONVERSION );
			$item->conversion_rate = sprintf( '%.2f', 100 * $item->conversions / $item->impressions );
		}
		$item->has_event_logs = ( $item->impressions > 1 || $item->conversions );
	}

	$item->lockdown          = get_post_meta( $id, TVE_Ult_Const::META_NAME_FOR_LOCKDOWN, true );
	$item->lockdown_settings = tve_ult_get_lockdown_settings( $id );

	/** also append the events to the post */
	if ( ! empty( $filters['get_events'] ) ) {
		$item->timeline          = array();
		$item->conversion_events = array();

		$events = tve_ult_get_events( array(
			'campaign_id' => $item->ID,
			'type'        => array(
				TVE_Ult_Const::EVENT_TYPE_START,
				TVE_Ult_Const::EVENT_TYPE_TIME,
				TVE_Ult_Const::EVENT_TYPE_CONV,
			),
			'order'       => array(
				'days'  => 'desc',
				'hours' => 'desc',
			),
		) );

		foreach ( $events as $event ) {
			if ( $event['type'] === TVE_Ult_Const::EVENT_TYPE_CONV ) {
				$item->conversion_events[] = $event;
			} else {
				$item->timeline[] = $event;
			}
		}
	}

	if ( ! empty( $filters['get_settings'] ) ) {
		$item->tu_schedule_instance = TU_Schedule_Abstract::factory( $item->ID );
		if ( $item->type === TVE_Ult_Const::CAMPAIGN_TYPE_ROLLING ) {
			$item->settings['rolling_type'] = $item->rolling_type;
		}
		$item->tu_schedule_instance->set( $item->settings );
		$item->tu_schedule_instance->set_conversion_events( $item->conversion_events );
	}

	return $item;
}

/**
 * Save campaign status
 *
 * @param $id     int
 * @param $status string
 *
 * @return false|int id of model or false on error
 */
function tve_ult_save_campaign_status( $id, $status ) {

	if ( empty( $id ) || empty( $status ) ) {
		return array( 'success' => false, 'message' => 'invalid_call' );
	}
	$item = tve_ult_get_campaign( $id );
	if ( $item->status == $status ) {
		return array( 'success' => true, 'data' => $item->ID );
	}

	if ( empty( $item ) ) {
		return array( 'success' => false, 'message' => 'invalid_id' );
	}
	if ( $status == TVE_Ult_Const::CAMPAIGN_STATUS_RUNNING ) {
		/**
		 * step 1 check campaign duration / type settings
		 */
		$campaign_type     = $item->type;
		$campaign_settings = $item->settings;
		if ( empty( $campaign_type ) || empty( $campaign_settings ) ) {
			return array( 'success' => false, 'message' => 'settings_error' );
		}

		if ( $campaign_type == TVE_Ult_Const::CAMPAIGN_TYPE_EVERGREEN && empty( $item->linked_to ) && empty( $campaign_settings['trigger']['type'] ) ) {
			return array( 'success' => false, 'message' => 'evergreen_linked' );
		}

		if ( $campaign_type == TVE_Ult_Const::CAMPAIGN_TYPE_ABSOLUTE ) {
			$current_time = tve_ult_current_time( 'timestamp' );
			$enddate      = strtotime( $campaign_settings['end']['date'] . ' ' . $campaign_settings['end']['time'] );
			if ( $current_time > $enddate ) {
				return array( 'success' => false, 'message' => 'end_date_in_past' );
			}
		}

		/**
		 * step 2 check if the user configured display settings for the campaign
		 */
		require_once TVE_Ult_Const::plugin_path( 'inc/classes/display_settings/class-thrive-ult-campaign-options.php' );
		$manager = new Thrive_Ult_Campaign_Options( $id );
		if ( ! $manager->checkForAnyOptionChecked() ) {
			return array( 'success' => false, 'message' => 'display_settings_error' );
		}

		/**
		 * step 3 check if the user has a lockdown campaign and all the pages set
		 */
		if ( ! empty( $item->lockdown ) && ( empty( $item->lockdown_settings['preaccess'] ) || empty( $item->lockdown_settings['expired'] ) || empty( $item->lockdown_settings['promotion'] ) ) ) {
			return array( 'success' => false, 'message' => 'lockdown' );
		}

		if ( empty( $item->lockdown ) && $campaign_settings['trigger']['type'] == TVE_Ult_Const::TRIGGER_TYPE_PROMOTION ) {
			return array( 'success' => false, 'message' => 'invalid_trigger' );
		}

		/**
		 * step 4. check if the campaign has at least one design and that design has a template selected
		 */
		$designs = tve_ult_get_designs( $id );
		if ( count( $designs ) < 1 ) {
			return array( 'success' => false, 'message' => 'design_error' );
		}
		$has_template = false;
		foreach ( $designs as $d ) {
			if ( ! empty( $d[ TVE_Ult_Const::FIELD_TEMPLATE ] ) ) {
				$has_template = true;
				break;
			}
		}
		if ( ! $has_template ) {
			return array( 'success' => false, 'message' => 'design_tpl_error' );
		}

		if ( ! function_exists( 'tve_leads_get_groups' ) && $campaign_settings['trigger']['type'] == TVE_Ult_Const::TRIGGER_TYPE_LEADS_CONVERSION ) {
			return array( 'success' => false, 'message' => 'invalid_leads_trigger' );
		}
	}

	update_post_meta( $id, TVE_Ult_Const::META_NAME_FOR_STATUS, $status );
	wp_update_post( $item );

	return array( 'success' => true, 'data' => $item->ID );

}

/**
 *
 * Gets the campaign settings by id, checks if we have them saved if not creates empty values
 *
 * @param $id
 *
 * @return mixed
 */
function tve_ult_get_campaign_settings( $id ) {
	$settings = get_post_meta( $id, TVE_Ult_Const::META_NAME_FOR_CAMPAIGN_SETTINGS, true );

	if ( empty( $settings ) ) {
		$settings = array();
		$settings['duration']         = 1;
		$settings['repeat']           = 1;
		$settings['evergreen_repeat'] = 0;
		$settings['real']             = 0;
		$settings['realtime']         = '00:00';
		$settings['start']['date']    = '';
		$settings['start']['time']    = '00:00';
		$settings['end']['date']      = '';
		$settings['end']['time']      = '00:00';
		$settings['trigger']['type']  = '';
		$settings['trigger']['ids']   = '';
	}

	return $settings;
}

/**
 * Get the lockdown settings or get the default ones
 *
 * @param $id
 *
 * @return mixed
 */
function tve_ult_get_lockdown_settings( $id ) {

	$defaults = array(
		'preaccess' => array(),
		'promotion' => array(
			array(
				'id'    => '',
				'label' => '',
				'type'  => '',
				'value' => '',
				'link'  => '',
			),
		),
		'expired'   => array(),
		'service'   => array(),
	);

	$settings = get_post_meta( $id, TVE_Ult_Const::META_NAME_FOR_LOCKDOWN_SETTINGS, true );
	$settings = empty( $settings ) ? array() : $settings;
	$settings = array_merge( $defaults, $settings );

	foreach ( $settings as $page => &$set ) {
		if ( $page === 'service' ) {
			continue;
		}

		if ( ! empty( $set['id'] ) ) {
			$object       = get_post( $set['id'] );
			$set['value'] = is_object( $object ) ? $object->post_title : null;
		}
	}

	//backwards compatibility for promotion
	if ( ! empty( $settings['promotion'] ) && array_key_exists( 'value', $settings['promotion'] ) ) {

		$old_settings         = $settings['promotion'];
		$old_settings['link'] = $settings['service']['value'];
		unset( $settings['service']['value'] );

		$settings['promotion']   = array();
		$settings['promotion'][] = $old_settings;
	}

	return $settings;
}

/**
 * Get the timezone string setup from WP admin settings screen.
 *
 * @return string the value setup from WP admin general settings
 */
function tve_ult_default_tzstring() {
	$tzstring = get_option( 'timezone_string' );
	if ( ! empty( $tzstring ) ) {
		return $tzstring;
	}
	$offset = get_option( 'gmt_offset' );
	if ( $offset == 0 ) {
		return 'UTC+0';
	}

	return ( $offset < 0 ? 'UTC' : 'UTC+' ) . $offset;
}

/**
 * Get global settings
 *
 * @return array
 */
function tve_ult_get_date_settings() {
	$settings = array(
		'date_format' => get_option( TVE_Ult_Const::SETTINGS_DATE_FORMAT ),
		'time_format' => get_option( TVE_Ult_Const::SETTINGS_TIME_FORMAT ),
		'offset'      => get_option( TVE_Ult_Const::SETTINGS_TIME_ZONE ),
	);
	if ( ! empty( $settings['offset'] ) && ! empty( $settings['time_format'] ) && ! empty( $settings['date_format'] ) ) {
		return $settings;
	}

	$default_settings = array(
		'date_format' => 'dS F Y',
		'time_format' => '24',
		'offset'      => tve_ult_default_tzstring(),
	);

	return $default_settings;
}

/**
 * Get the GMT offset setup from TU settings.
 * The setting can also be saved as a timezone string.
 *
 * By default, it returns the wp gmt_offset option
 *
 *
 * @return float|false Timezone GMT offset, false otherwise.
 */
function tve_ult_gmt_offset() {
	$gmt_offset = get_option( TVE_Ult_Const::SETTINGS_TIME_ZONE_OFFSET );

	return false === $gmt_offset ? get_option( 'gmt_offset' ) : $gmt_offset;
}

/**
 * Save design based on $model sent as parameter
 *
 * @param $model
 *
 * @return false|array updated design or false on error
 */
function tve_ult_save_design( $model ) {
	global $tve_ult_db;

	if ( empty( $model['post_parent'] ) ) {
		return false;
	}

	$campaign = get_post( $model['post_parent'] );
	if ( empty( $campaign ) || is_wp_error( $campaign ) ) {
		return false;
	}

	$type = get_post_type( $campaign );
	if ( ! $type || ! in_array( $type, array( TVE_Ult_Const::POST_TYPE_NAME_FOR_CAMPAIGN ) ) ) {
		return false;
	}

	if ( empty( $model['post_title'] ) ) {
		$design_details      = TVE_Ult_Const::design_details( $model['post_type'] );
		$model['post_title'] = $design_details['name'];
	}

	foreach ( TVE_Ult_Const::editor_fields() as $field ) {
		$model['tcb_fields'][ $field ] = isset( $model[ $field ] ) ? $model[ $field ] : '';
	}

	if ( ! ( $id = $tve_ult_db->save_design( $model ) ) ) {
		return false;
	}

	/** when the 1st design is added */
	$designs = tve_ult_get_designs( $model['post_parent'] );
	if ( count( $designs ) == 1 && empty( $model['parent_id'] ) ) {
		$model['id'] = (string) $id;

		$events = tve_ult_get_events( array(
			'campaign_id' => $model['post_parent'],
			'type'        => TVE_Ult_Const::EVENT_TYPE_START,
		) );

		$start_event = reset( $events );

		tve_ult_add_design_to_event( $start_event, $model );
	}

	return tve_ult_get_design( $id );
}

/**
 * Reads designs from DB based on $campaignID and $filters
 *
 * @param       $campaign_id
 * @param array $filters
 *      post_status default is publish
 *
 * @return array
 */
function tve_ult_get_designs( $campaign_id, $filters = array() ) {

	global $tve_ult_db;

	$defaults = array(
		'post_parent' => $campaign_id,
		'post_status' => TVE_Ult_Const::STATUS_PUBLISH,
		'parent_id'   => 0,
	);

	$filters = array_merge( $defaults, $filters );

	if ( $filters['parent_id'] === - 1 ) {
		unset( $filters['parent_id'] );
	}

	$designs = $tve_ult_db->get_designs( $filters );

	foreach ( $designs as $key => $design ) {
		$designs[ $key ]['tcb_edit_url']    = tve_ult_get_editor_url( $campaign_id, $design['id'] );
		$designs[ $key ]['tcb_preview_url'] = tve_ult_get_preview_url( $campaign_id, $design['id'] );

		if ( ! empty( $design[ TVE_Ult_Const::FIELD_TEMPLATE ] ) ) {
			list( $type, $name ) = TU_Template_Manager::tpl_type_key( $design[ TVE_Ult_Const::FIELD_TEMPLATE ] );
			$designs[ $key ]['thumb_url'] = TVE_Ult_Const::plugin_url( 'tcb-bridge/editor-templates/' . $type . '/thumbnails/admin/' . $name . '.png' );
		}

		$type                              = TVE_Ult_Const::design_details( $design['post_type'] );
		$designs[ $key ]['type_nice_name'] = $type['name'];

		if ( ! empty( $design['post_type'] ) && $design['post_type'] === TVE_Ult_Const::DESIGN_TYPE_SHORTCODE ) {
			$designs[ $key ]['shortcode'] = TU_Shortcodes::get_countdown_shortcode( $campaign_id, $design['id'] );
		}
	}

	return $designs;
}

/**
 * Gets designs based on its id
 *
 * @param $design_id
 *
 * @return false|array with design's properties or false on error or not found
 */
function tve_ult_get_design( $design_id ) {
	global $tve_ult_db;

	$design = $tve_ult_db->get_design( $design_id );

	if ( empty( $design ) ) {
		return false;
	}

	$design['tcb_edit_url']                         = tve_ult_get_editor_url( $design['post_parent'], $design['id'] );
	$design['tcb_preview_url']                      = tve_ult_get_preview_url( $design['post_parent'], $design['id'] );
	$design[ TVE_Ult_Const::DESIGN_QUERY_KEY_NAME ] = $design['id'];

	if ( ! empty( $design[ TVE_Ult_Const::FIELD_TEMPLATE ] ) ) {
		list( $type, $name ) = TU_Template_Manager::tpl_type_key( $design[ TVE_Ult_Const::FIELD_TEMPLATE ] );
		$design['thumb_url'] = TVE_Ult_Const::plugin_url( 'tcb-bridge/editor-templates/' . $type . '/thumbnails/admin/' . $name . '.png' );
	}

	$type                     = TVE_Ult_Const::design_details( $design['post_type'] );
	$design['type_nice_name'] = $type['name'];

	return $design;
}

/**
 * fetches and returns all states related to a $design
 * $design can either be the main state (parent_id = 0) or a sub-state
 *
 * @param array|int $design
 *
 * @return array[] list of states
 */
function tve_ult_get_related_states( $design ) {

	if ( is_numeric( $design ) ) {
		$design = tve_ult_get_design( $design );
	}

	$parent = empty( $design['parent_id'] ) ? $design : tve_ult_get_design( $design['parent_id'] );

	return array_merge( array( $parent ), tve_ult_get_child_states( $parent ) );
}

/**
 * fetches and returns all children (child states) of the $parent design
 * $parent is the main state of the design
 *
 * @param array|int $parent
 *
 * @return array[] array of designs
 */
function tve_ult_get_child_states( $parent ) {

	global $tve_ult_db;

	return $tve_ult_db->get_designs( array(
		'parent_id' => (int) ( is_array( $parent ) ? $parent['id'] : $parent ),
		'order'     => 'id ASC',
	) );
}

/**
 * Delete Design from designs tables
 *
 * @param $design
 *
 * @return false|array
 */
function tve_ult_delete_design( $design ) {
	if ( is_array( $design ) ) {
		$design = $design['id'];
	}
	$design_model = tve_ult_get_design( $design );
	if ( empty( $design_model ) ) {
		return false;
	} else {
		tve_ult_cleanup_event_actions( $design_model );
	}

	global $tve_ult_db;

	$result = $tve_ult_db->delete_design( $design );
	if ( empty( $result ) ) {
		return false;
	}

	$response = array(
		'deleted' => $result,
	);
	/**
	 * Checks if the campaign is running and there are no more designs. If this is the case, pauses the campaign and notifies the user about it.
	 */
	$campaign_id     = $design_model['post_parent'];
	$campaign_status = get_post_meta( $campaign_id, TVE_Ult_Const::META_NAME_FOR_STATUS, true );
	if ( $campaign_status === TVE_Ult_Const::CAMPAIGN_STATUS_RUNNING ) {
		$remaining_designs = tve_ult_get_designs( $campaign_id );
		if ( empty( $remaining_designs ) ) {
			$response['campaign_paused'] = __( 'This campaign has been automatically paused because all designs have been deleted', TVE_Ult_Const::T );
			$response['campaign_status'] = TVE_Ult_Const::CAMPAIGN_STATUS_PAUSED;
			tve_ult_save_campaign_status( $campaign_id, TVE_Ult_Const::CAMPAIGN_STATUS_PAUSED );
		}
	}

	return $response;
}

/**
 * Cleanup actions from events that use deleted design
 *
 * @param $design
 *
 * @return false|true
 */
function tve_ult_cleanup_event_actions( $design ) {
	if ( empty( $design ) ) {
		return false;
	}
	$filters = array( 'campaign_id' => $design['post_parent'] );
	$events  = tve_ult_get_events( $filters );
	foreach ( $events as $event ) {
		if ( empty( $event['actions'] ) ) {
			continue;
		}
		$actions = $event['actions'];
		foreach ( $actions as $key => $action ) {
			if ( $action['design'] == $design['id'] ) {
				/* main design gets deleted => completely remove the design from the list of actions */
				unset( $event['actions'][ $key ] );
			} elseif ( $action['state'] == $design['id'] && ! empty( $design['parent_id'] ) ) {
				/* design state gets deleted => auto-select the main state in the timeline event */
				$event['actions'][ $key ]['state'] = $design['parent_id'];
			}
		}
		$event['actions'] = array_values( $event['actions'] );
		tve_ult_save_event( $event );
	}

	return true;
}

/**
 * Get the events based on filters
 *
 * @param array $filters
 *
 * @return array $events
 */
function tve_ult_get_events( $filters = array() ) {
	$events = array();

	$defaults = array(
		'order' => array(
			'days'  => 'desc',
			'hours' => 'desc',
		),
	);

	if ( ! is_array( $filters ) ) {
		return $events;
	}

	$filters = array_merge( $defaults, $filters );

	if ( ! empty( $filters['campaign_id'] ) ) {
		global $tve_ult_db;
		$events = $tve_ult_db->get_events( $filters );
	}
	foreach ( $events as &$event ) {
		$event['actions']         = ! empty( $event['actions'] ) ? unserialize( $event['actions'] ) : array();
		$event['trigger_options'] = ! empty( $event['trigger_options'] ) ? unserialize( $event['trigger_options'] ) : array();
	}

	return $events;
}


/**
 * Insert into DB a start event if it does not already exists
 * A campaign can have only one start event
 *
 * @param int|WP_Post $campaign
 *
 * @return false|int
 */
function tve_ult_save_start_event( $campaign ) {
	if ( ! is_object( $campaign ) ) {
		$campaign = tve_ult_get_campaign( $campaign );
	}

	global $tve_ult_db;

	$item = array(
		'campaign_id' => $campaign->ID,
		'days'        => 0,
		'hours'       => 0,
		'type'        => TVE_Ult_Const::EVENT_TYPE_START,
	);

	$events = $tve_ult_db->get_events( array(
		'campaign_id' => $campaign->ID,
		'type'        => TVE_Ult_Const::EVENT_TYPE_START,
	) );

	if ( ! empty( $events ) ) {
		$start_event = $events[0];
		$item        = array_merge( $start_event, $item );
	}

	$start_date = tve_ult_pre_format_date( $campaign->settings['start']['date'], $campaign->settings['start']['time'] );

	switch ( $campaign->type ) {
		case TVE_Ult_Const::CAMPAIGN_TYPE_ABSOLUTE:
			$end           = tve_ult_pre_format_date( $campaign->settings['end']['date'], $campaign->settings['end']['time'] );
			$diff          = tve_ult_date_diff( $start_date, $end );
			$item['days']  = $diff['days'];
			$item['hours'] = $diff['hours'];
			break;
		case TVE_Ult_Const::CAMPAIGN_TYPE_ROLLING:
			if ( $campaign->rolling_type === TVE_Ult_Const::CAMPAIGN_ROLLING_TYPE_DAILY ) {
				$item['days']  = 0;
				$item['hours'] = $campaign->settings['duration'];
			} else {
				$item['days']  = $campaign->settings['duration'];
				$item['hours'] = 0;
			}
			break;
		case TVE_Ult_Const::CAMPAIGN_TYPE_EVERGREEN:
			$item['days']  = $campaign->settings['duration'];
			$item['hours'] = 0;
			break;
	}

	return tve_ult_save_event( $item );
}

/**
 * Returns an array with two elements: designs and states
 * Array contains data prepared based on $field_filter param
 *      - designs : list with all designs of campaign
 *      - states : array associative with designs IDs and their states
 *
 * @param       $campaign_id
 * @param array $fields_filter
 *
 * @return array
 */
function tve_ult_get_designs_and_states( $campaign_id, $fields_filter = array() ) {
	$designs = tve_ult_get_designs( $campaign_id );
	$states  = array();

	foreach ( $designs as $design ) {
		$children             = tve_ult_get_designs( $campaign_id, array( 'parent_id' => $design['id'] ) );
		$design['post_title'] = ' - ' . __( 'main state', TVE_Ult_Const::T ) . ' - ';
		array_unshift( $children, $design );
		$states[ $design['id'] ] = $children;
	}

	if ( empty( $fields_filter ) ) {
		return array(
			'designs' => $designs,
			'states'  => $states,
		);
	}

	foreach ( $designs as &$design ) {
		$design = tve_ult_array_filter( $design, $fields_filter );
	}

	foreach ( $states as &$state ) {
		foreach ( $state as &$s ) {
			$s = tve_ult_array_filter( $s, $fields_filter );
		}
	}

	return array(
		'designs' => $designs,
		'states'  => $states,
	);
}

/**
 * Save an event into DB
 *
 * @param array $item
 *
 * @return int|false
 */
function tve_ult_save_event( $item ) {
	global $tve_ult_db;

	if ( isset( $item['actions'] ) && is_array( $item['actions'] ) ) {
		$item['actions'] = serialize( $item['actions'] );
	}

	if ( ! empty( $item['trigger_options'] ) ) {

		if ( $item['trigger_options']['event'] === TVE_Ult_Const::TRIGGER_OPTION_MOVE ) {
			// check if we're editing the campaign and if the campaign to move to was changed
			if ( isset( $item['id'] ) && isset( $item['trigger_options']['end_id_old'] ) ) {
				tve_ult_remove_conversion_link( $item );

				unset( $item['trigger_options']['end_id_old'] );
			}

			tve_ult_set_conversion_link( $item );
		}

		$item['trigger_options'] = serialize( $item['trigger_options'] );
		if ( is_array( $item['actions'] ) ) {
			$item['actions'] = serialize( $item['actions'] );
		}
		$item['type']  = TVE_Ult_Const::EVENT_TYPE_CONV;
		$item['days']  = 0;
		$item['hours'] = 0;
		$item['hours'] = 0;

	} else {
		unset( $item['trigger_options'] );
	}

	$id = $tve_ult_db->save_event( $item );

	if ( ! empty( $item['type'] ) && $item['type'] === TVE_Ult_Const::EVENT_TYPE_START && empty( $item['actions'] ) ) {
		$designs = tve_ult_get_designs( $item['campaign_id'] );
		if ( ! empty( $designs ) ) {
			$design     = reset( $designs );
			$item['id'] = $id;
			tve_ult_add_design_to_event( $item, $design );
		}
	}

	return tve_ult_get_event( $id );
}

/**
 * Remove conversion link from a specific campaign
 *
 * @param $item
 */
function tve_ult_remove_conversion_link( $item ) {
	$linked = get_post_meta( $item['trigger_options']['end_id_old'], TVE_Ult_Const::META_NAME_FOR_CAMPAIGN_LINK, true );
	if ( ! empty( $linked ) ) {
		$key = array_search( $item['campaign_id'], $linked );
		unset( $linked[ $key ] );
	}

	update_post_meta( $item['trigger_options']['end_id_old'], TVE_Ult_Const::META_NAME_FOR_CAMPAIGN_LINK, $linked );
}

/**
 * Sets a conversion link to a specific campaign
 *
 * @param $item
 */
function tve_ult_set_conversion_link( $item ) {
	$linked = get_post_meta( $item['trigger_options']['end_id'], TVE_Ult_Const::META_NAME_FOR_CAMPAIGN_LINK, true );

	if ( ! empty( $linked ) ) {
		$key = array_search( $item['campaign_id'], $linked );
		if ( empty( $key ) ) {
			$linked[] = $item['campaign_id'];
		}
	} else {
		$linked[] = $item['campaign_id'];
	}

	update_post_meta( $item['trigger_options']['end_id'], TVE_Ult_Const::META_NAME_FOR_CAMPAIGN_LINK, $linked );
}

/**
 * Adds a display design action upon event
 *
 * @param array $event
 * @param array $design
 *
 * @return int|false
 */
function tve_ult_add_design_to_event( $event, $design ) {

	if ( empty( $event ) || empty( $design ) ) {
		return false;
	}

	$action_details      = TU_Event_Action::get_details( TU_Event_Action::DESIGN_SHOW );
	$event['actions'][0] = array(
		'key'         => $action_details['key'],
		'design'      => $design['id'],
		'design_name' => $design['post_title'],
		'state'       => $design['id'],
		'state_name'  => __( 'main state', TVE_Ult_Const::T ),
		'name'        => $action_details['name'] . ' <strong>' . $design['post_title'] . ' (' . __( 'main state', TVE_Ult_Const::T ) . ')</strong>',
	);

	return tve_ult_save_event( $event );

}

function tve_ult_get_event( $id ) {
	global $tve_ult_db;

	return $tve_ult_db->get_event( $id );
}

function tve_ult_delete_event( $id ) {
	global $tve_ult_db;

	// If we're deleting a conversion event we need to remove the link to the campaign it should move to
	$item = $tve_ult_db->get_event( $id );
	if ( ! empty( $item['trigger_options'] ) && $item['trigger_options']['event'] === TVE_Ult_Const::TRIGGER_OPTION_MOVE ) {
		$linked = get_post_meta( $item['trigger_options']['end_id'], TVE_Ult_Const::META_NAME_FOR_CAMPAIGN_LINK, true );

		if ( ! empty( $linked ) ) {
			$key = array_search( $item['campaign_id'], $linked );
			unset( $linked[ $key ] );

			update_post_meta( $item['trigger_options']['end_id'], TVE_Ult_Const::META_NAME_FOR_CAMPAIGN_LINK, $linked );
		}
	}

	return $tve_ult_db->delete_event( $id );
}


/**
 * action hook implementation - register an impression for a campaign
 *
 * @param int|WP_Post $campaign
 *
 * @return int|string the inserted ID or a string error message if the insert fails
 */
function tve_ult_register_impression( $campaign ) {

	if ( is_int( $campaign ) ) {
		$campaign = get_post( $campaign );
	}

	return tve_ult_add_event_log( $campaign );
}

/**
 * action hook implementation - register a conversion for a campaign
 *
 * @param int|WP_Post $campaign
 *
 * @return int|string the inserted ID or a string error message if the insert fails
 */
function tve_ult_register_conversion( $campaign ) {
	if ( is_int( $campaign ) ) {
		$campaign = get_post( $campaign );
	}

	return tve_ult_add_event_log( $campaign, TVE_Ult_Const::LOG_TYPE_CONVERSION );
}

/**
 * registers a campaign impression or conversion based on $type parameter - inserts a row in the event_log table
 *
 * @param WP_Post $campaign
 * @param int     $type event log type
 *
 * @return int|string the inserted ID or a string error message if the insert fails
 */
function tve_ult_add_event_log( $campaign, $type = TVE_Ult_Const::LOG_TYPE_IMPRESSION ) {
	if ( current_user_can( 'edit_posts' ) ) {
		return __( 'Not logging impression for logged user', TVE_Ult_Const::T );
	}

	global $tve_ult_db;
	/**
	 * also store a cached version of the campaign impressions / conversions in post_meta
	 */
	$impressions = intval( get_post_meta( $campaign->ID, 'tve_ult_log_data_' . $type, true ) );
	update_post_meta( $campaign->ID, 'tve_ult_log_data_' . $type, $impressions + 1 );

	return $tve_ult_db->insert_event_log( array(
		'type'        => $type,
		'campaign_id' => $campaign->ID,
	) );
}

/**
 * get the total number of impressions for a campaign
 *
 * @param WP_Post|int|string $campaign     campaign instance or campaign ID
 * @param int                $type         controls what to retrieve - conversions or impressions
 * @param bool               $bypass_cache whether or not to read in the full data from the event_log table
 *
 * @return int the number of impressions or conversions
 */
function tve_ult_count_campaign_logs( $campaign, $type = TVE_Ult_Const::LOG_TYPE_IMPRESSION, $bypass_cache = false ) {

	global $tve_ult_db;

	$campaign_id = is_numeric( $campaign ) ? $campaign : $campaign->ID;

	$count = '';

	if ( ! $bypass_cache ) {
		$count = (string) get_post_meta( $campaign_id, 'tve_ult_log_data_' . $type, true );
	}

	if ( 0 === strlen( $count ) ) {
		$count = $tve_ult_db->count_logs( array(
			'type'        => $type,
			'campaign_id' => $campaign_id,
		) );
		update_post_meta( $campaign_id, 'tve_ult_log_data_' . $type, $count );
	}

	return (int) $count;
}

/**
 * check whether or not this campaign has at least a Conversion Event defined
 *
 * @param WP_Post|int|string $campaign campaign instance or ID
 *
 * @return bool
 */
function tve_ult_campaign_has_conversions( $campaign ) {
	// TODO: implement this after Conversion Events are implemented

	$campaign_id = is_numeric( $campaign ) ? $campaign : $campaign->ID;

	return true;

}

/**
 * prepare the chart data for a campaign
 *
 * @param int $campaign_id
 *
 * @return array
 */
function tve_ult_get_chart_data( $campaign_id ) {
	global $tve_ult_db;

	$data = $tve_ult_db->count_event_logs_chart_data( $campaign_id );

	/**
	 * if there is no conversion, we do not show the conversion rate
	 */
	if ( ! array_filter( $data['conversions'] ) ) {
		unset( $data['conversions'] );

		return $data;
	}

	$cr = create_function( '$a, $b', 'return round( $a ? ( 100 * $b / $a ) : 0.00, 2 );' );

	$data['conversion_rates'] = array_map( $cr, $data['impressions'], $data['conversions'] );

	return $data;
}

/**
 * Delete events form timeline that have duration greater than campaign duration
 *
 * @param $campaign
 * @param $duration
 *
 * @return bool|false|int
 */
function tve_ult_clear_timeline( $campaign, $duration ) {

	if ( empty( $campaign ) || empty( $duration ) ) {
		return false;
	}

	global $tve_ult_db;

	return $tve_ult_db->clear_timeline( $campaign, $duration );
}

/**
 * Delete all impression/conversions post meta
 */
function tve_ult_purge_cache() {
	foreach ( TVE_Ult_Const::log_types() as $type ) {
		delete_post_meta_by_key( 'tve_ult_log_data_' . $type );
	}
}

/**
 * Inserts or updates an email log
 *
 * @param $model
 *
 * @return false|int
 */
function tve_ult_save_email_log( $model ) {

	if ( empty( $model['campaign_id'] ) ) {
		return false;
	}

	if ( empty( $model['email'] ) ) {
		return false;
	}

	if ( empty( $model['started'] ) ) {
		return false;
	}

	$campaign = tve_ult_get_campaign( $model['campaign_id'] );

	if ( empty( $campaign ) ) {
		return false;
	}

	global $tve_ult_db;

	return $tve_ult_db->save_email_log( $model );
}

/**
 * Get an email log based on campaign and email address
 *
 * @param $campaign_id
 * @param $email
 *
 * @return null|array
 */
function tve_ult_get_email_log( $campaign_id, $email ) {
	global $tve_ult_db;

	if ( empty( $campaign_id ) || empty( $email ) ) {
		return null;
	}

	return $tve_ult_db->get_email_log( $campaign_id, $email );
}

/**
 * get all the campaigns that have the page / post with $post_id setup as the campaign's promotion (special offer) page
 * we only need to fetch lockdown campaigns
 *
 * @param string|int $post_id
 *
 * @return WP_Post[] array of campaigns
 */
function tve_ult_get_campaigns_for_promotion_page( $post_id ) {
	$campaigns = tve_ult_get_campaigns( array(
		'lockdown'     => true,
		'only_running' => true,
	) );

	$permalink = get_permalink( $post_id );

	$filtered = array();
	foreach ( $campaigns as $campaign ) {
		if ( ! $campaign->lockdown ) {
			continue;
		}
		$by_post = false;
		$by_link = false;

		// backwards compatibility for promotion checks
		if ( ! empty( $campaign->lockdown_settings['promotion'] ) && ! array_key_exists( 'value', $campaign->lockdown_settings['promotion'] ) ) {
			foreach ( $campaign->lockdown_settings['promotion'] as $promotion ) {
				if ( isset( $promotion['id'] ) && $promotion['id'] == $post_id ) {
					$by_post = true;
				}
				if ( untrailingslashit( $promotion['value'] ) === untrailingslashit( $permalink ) ) {
					$by_link = true;
				}
			}
		} else {
			$by_post = isset( $campaign->lockdown_settings['promotion']['id'] ) && $campaign->lockdown_settings['promotion']['id'] == $post_id;
			$by_link = isset( $campaign->lockdown_settings['promotion']['value'] ) && untrailingslashit( $campaign->lockdown_settings['promotion']['value'] ) === untrailingslashit( $permalink );
		}

		if ( $by_post || $by_link ) {
			$filtered [] = $campaign;
		}
	}

	return $filtered;
}

/**
 * Get an email log based on the id of the log
 *
 * @param $id
 *
 * @return array
 */
function tve_ult_get_email_log_by_id( $id ) {
	global $tve_ult_db;

	return $tve_ult_db->get_email_log_by_id( $id );
}

/**
 * Return a list of all designs for a campaign, as a list with each element containing a key with its child states.
 * A sample result of this:
 * array(
 *      ID => '',
 *        post_title => '',
 *      // etc
 *      children => array( array(post_title => '') )
 * )
 *
 * @param int|string $campaign_id
 *
 * @return array
 */
function tve_ult_get_designs_hierarchy( $campaign_id ) {
	$all_designs = tve_ult_get_designs( $campaign_id, array(
		'parent_id' => null,
		'order'     => 'id ASC', // make sure we get all main states first
	) );
	$by_id       = array();
	foreach ( $all_designs as $design ) {
		$design['children'] = array();
		if ( empty( $design['parent_id'] ) ) {
			$by_id[ $design['id'] ] = $design;
			continue;
		}
		if ( ! isset( $by_id[ $design['parent_id'] ] ) ) { // invalid state
			continue;
		}
		$by_id[ $design['parent_id'] ]['children'] [] = $design;
	}

	return array_values( $by_id ); // numeric indexes, so that we can use this as a backbone collection
}

/**
 * Get all campaigns that have at least one shortcode design
 * The campaigns designs are also filtered only to shortcode designs
 *
 * @return array
 */
function tve_ult_get_campaign_with_shortcodes() {

	$campaigns = tve_ult_get_campaigns( array(
		'events'       => false,
		'get_settings' => false,
		'only_running' => true,
	) );

	$filtered = array();

	foreach ( $campaigns as &$campaign ) {
		if ( empty( $campaign->designs ) ) {
			continue;
		}

		foreach ( $campaign->designs as $key => &$design ) {
			if ( $design['post_type'] === TVE_Ult_Const::DESIGN_TYPE_SHORTCODE ) {
				$filtered[ $campaign->ID ] = $campaign;
			} else {
				/**
				 * unset the un-wanted designs
				 */
				unset( $campaign->designs[ $key ] );
			}
		}
	}

	return $filtered;
}
