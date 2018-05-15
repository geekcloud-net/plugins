<?php

/**
 * All plugin constants should be defined here
 * Use this file to define only constants
 */
class TVE_Ult_Const {
	/**
	 * TU plugin version
	 */
	const PLUGIN_VERSION = '2.0.29';

	/**
	 * Database version for current TU version
	 */
	const DB_VERSION = '1.2';

	/**
	 * Required TCB version
	 */
	const REQUIRED_TCB_VERSION = '2.0.29';

	/**
	 * Database prefix for all TU tables
	 */
	const DB_PREFIX = 'tve_ult_';

	/**
	 * Post types
	 */
	const POST_TYPE_NAME_FOR_CAMPAIGN = 'tve_ult_campaign';
	const POST_TYPE_NAME_FOR_SCHEDULE = 'tve_ult_schedule';

	/**
	 * Trigger Types
	 */
	const TRIGGER_TYPE_FIRST_VISIT = 'first';
	const TRIGGER_TYPE_PROMOTION = 'promotion';
	const TRIGGER_TYPE_LEADS_CONVERSION = 'conversion';
	const TRIGGER_TYPE_PAGE_VISIT = 'url';

	/**
	 * Trigger Options
	 */
	const TRIGGER_OPTION_MOVE = 'move';
	const TRIGGER_OPTION_END = 'end';
	const TRIGGER_OPTION_SPECIFIC = 'specific';
	const TRIGGER_OPTION_CONVERSION = 'conversion';

	/**
	 * Campaigns types
	 */
	const CAMPAIGN_STATUS_RUNNING = 'running';
	const CAMPAIGN_STATUS_PAUSED = 'paused';

	/**
	 * Campaigns types
	 */
	const CAMPAIGN_TYPE_ABSOLUTE = 'absolute';
	const CAMPAIGN_TYPE_ROLLING = 'rolling';
	const CAMPAIGN_TYPE_EVERGREEN = 'evergreen';

	/**
	 * Campaigns rolling types
	 */
	const CAMPAIGN_ROLLING_TYPE_DAILY = 'daily';
	const CAMPAIGN_ROLLING_TYPE_WEEKLY = 'weekly';
	const CAMPAIGN_ROLLING_TYPE_MONTHLY = 'monthly';
	const CAMPAIGN_ROLLING_TYPE_YEARLY = 'yearly';

	/**
	 * Settings constants for get_option
	 */
	const SETTINGS_TIME_ZONE = 'tve_ult_time_zone';
	const SETTINGS_TIME_ZONE_OFFSET = 'tve_ult_gmt_offset';
	const SETTINGS_DATE_FORMAT = 'tve_ult_date_format';
	const SETTINGS_TIME_FORMAT = 'tve_ult_time_format';

	/**
	 * Design types
	 */
	const DESIGN_TYPE_HEADER_BAR = 'header-bar';
	const DESIGN_TYPE_FOOTER_BAR = 'footer-bar';
	const DESIGN_TYPE_WIDGET = 'widget';
	const DESIGN_TYPE_SHORTCODE = 'shortcode';
	/**
	 * Translate domain string to be used in translation functions
	 */
	const T = 'thrive-ult';

	/**
	 * Statuses
	 */
	const STATUS_PUBLISH = 'publish';
	const STATUS_TRASH = 'trash';

	/**
	 * Fields
	 */
	const FIELD_TEMPLATE = 'tpl';
	const FIELD_GLOBALS = 'globals';
	const FIELD_CONTENT = 'content';
	const FIELD_INLINE_CSS = 'inline_css';
	const FIELD_USER_CSS = 'user_css';
	const FIELD_CUSTOM_FONTS = 'fonts';
	const FIELD_ICON_PACK = 'icons';
	const FIELD_MASONRY = 'masonry';
	const FIELD_TYPEFOCUS = 'typefocus';
	const FIELD_STATE_INDEX = 'state_index';
	const FIELD_STATE_VISIBILITY = 'visibility';

	/**
	 * All kind of constants that cannot be classified
	 */
	const DESIGN_QUERY_KEY_NAME = 'tud_id';

	/**
	 * Actions
	 */
	const ACTION_TEMPLATE = 'tve_ult_tpl';
	const ACTION_STATE = 'tve_ult_state';
	const ACTION_SAVE_DESIGN_CONTENT = 'tve_ult_save_design_content';

	/**
	 * METAs
	 */
	const META_NAME_FOR_CAMPAIGN_TYPE = 'tve_ult_campaign_type';
	const META_NAME_FOR_ROLLING_TYPE = 'tve_ult_rolling_type';
	const META_NAME_FOR_STATUS = 'tve_ult_status';
	const META_NAME_FOR_CAMPAIGN_SETTINGS = 'tve_ult_campaign_settings';
	const META_NAME_FOR_SCHEDULE = 'tve_ult_schedule';
	const META_NAME_FOR_CAMPAIGN_ORDER = 'tve_campaign_order';
	const META_NAME_FOR_CAMPAIGN_LINK = 'tve_campaign_linked_to';
	const META_NAME_FOR_LOCKDOWN = 'tve_campaign_lockdown';
	const META_NAME_FOR_LOCKDOWN_SETTINGS = 'tve_campaign_lockdown_settings';
	const META_PREFIX_NAME_FOR_EDIT_STATE = 'tve_ult_edit_state_';

	/**
	 * Event types
	 */
	const EVENT_TYPE_START = 'start';
	const EVENT_TYPE_TIME = 'time';
	const EVENT_TYPE_CONV = 'conv';
	const EVENT_TYPE_END = 'end';

	/**
	 * DATES formats
	 */
	const FULL_DATE_FORMAT = 'Y-m-d H:i';
	const DATE_FORMAT = 'Y-m-d';

	/**
	 * Event log constants
	 */
	const LOG_TYPE_IMPRESSION = 1;
	const LOG_TYPE_CONVERSION = 2;

	/**
	 * Cookie names
	 */
	const COOKIE_IMPRESSION = 'tu_campaign_impression_';
	const COOKIE_CONVERSION = 'tu_campaign_conversion_';
	const COOKIE_NAME = 'tu_campaign_';
	const COOKIE_END_NAME = 'tu_campaign_end_';
	const COOKIE_FORCE_DISPLAY = 'tu_force_campaign_';


	/**
	 * Full path to the plugin folder (!includes a trailing slash if the $file argument is missing)
	 *
	 * @param string $file
	 *
	 * @return string
	 */
	public static function plugin_path( $file = '' ) {
		return plugin_dir_path( __FILE__ ) . ltrim( $file, '\\/' );
	}

	/**
	 * Full plugin url
	 *
	 * @param string $file if sent, it will return the full URL to the file
	 *
	 * @return string
	 */
	public static function plugin_url( $file = '' ) {
		return plugin_dir_url( __FILE__ ) . ltrim( $file, '\\/' );
	}

	/**
	 * All campaign types
	 *
	 * @return array
	 */
	public static function campaign_types() {
		return array(
			self::CAMPAIGN_TYPE_ABSOLUTE,
			self::CAMPAIGN_TYPE_ROLLING,
			self::CAMPAIGN_TYPE_EVERGREEN,
		);
	}

	/**
	 * All campaign statuses
	 *
	 * @return array
	 */
	public static function campaign_statuses() {
		return array(
			self::CAMPAIGN_STATUS_PAUSED,
			self::CAMPAIGN_STATUS_RUNNING,
		);
	}

	/**
	 * Design types without details
	 *
	 * @return array
	 */
	public static function design_types() {
		return array(
			self::DESIGN_TYPE_HEADER_BAR,
			self::DESIGN_TYPE_FOOTER_BAR,
			self::DESIGN_TYPE_WIDGET,
		);
	}

	/**
	 * Get the date formats
	 *
	 * @param $format
	 *
	 * @return array
	 */
	public static function date_format_details( $format ) {
		$all_formats = array(
			'dS F Y' => array(
				'description' => 'dd Month yyyy',
				'date'        => date( 'dS F Y', time() ),
			),
			'F d Y'  => array(
				'description' => 'Month dd yyyy',
				'date'        => date( 'F d Y', time() ),
			),
			'Y-m-d'  => array(
				'description' => 'yyyy-mm-dd',
				'date'        => date( 'Y-m-d', time() ),
			),
			'Y/m/d'  => array(
				'description' => 'yyyy/mm/dd',
				'date'        => date( 'Y/m/d', time() ),
			),
			'd/m/Y'  => array(
				'description' => 'dd/mm/yyyy',
				'date'        => date( 'd/m/Y', time() ),
			),
		);

		if ( $format == 'all' ) {
			return $all_formats;
		}
		$extracted = array();
		foreach ( $all_formats as $k => $v ) {
			if ( $format == $k ) {
				$extracted[ $k ] = $v;
			}
		}

		return $extracted;

	}

	/**
	 * Get the timezone int
	 *
	 * @param $tzstring
	 *
	 * @return mixed
	 */
	public static function get_timezone( $tzstring ) {
		$timezone = 0;
		if ( $tzstring ) {
			if ( preg_match( '/^UTC[+-]/', $tzstring ) ) {
				$timezone = preg_replace( '/UTC\+?/', '', $tzstring );
			} else {
				$timezone_obj = new DateTimeZone( $tzstring );
				$timezone     = timezone_offset_get( $timezone_obj, date_create() ) / 3600;
			}
		}

		return $timezone;
	}

	/**
	 * Design types with details
	 *      name        translated name
	 *      description translated description
	 *
	 * @return array
	 */
	public static function campaign_types_details() {
		return array(
			self::CAMPAIGN_TYPE_ABSOLUTE  => array(
				'name'    => __( 'Fixed Dates Campaign', self::T ),
				'image'   => 'tvd-absolute-campaign.png',
				'tooltip' => __( 'Use this campaign type for an offer with predefined start and end dates. For instance, an offer available between the 5th and 12th of November.', self::T ),
			),
			self::CAMPAIGN_TYPE_ROLLING   => array(
				'name'    => __( 'Recurring Campaign', self::T ),
				'image'   => 'tvd-rolling-campaign.png',
				'tooltip' => __( 'Use this campaign type for an offer with a definite repetition cycle. For instance, an offer that starts on November 5th and is repeated monthly.', self::T ),
			),
			self::CAMPAIGN_TYPE_EVERGREEN => array(
				'name'    => __( 'Evergreen Campaign', self::T ),
				'image'   => 'tvd-evergreen-campaign.png',
				'tooltip' => __( 'Use this campaign type for offers triggered by user actions, not by dates. For instance, an offer showing for 7 days after a new user subscribes to a newsletter', self::T ),
			),
		);
	}

	/**
	 * Design types with details
	 *      name        translated name
	 *      description translated description
	 *
	 * @return array
	 */
	public static function design_types_details() {
		return array(
			self::DESIGN_TYPE_HEADER_BAR => array(
				'name'          => __( 'Top ribbon', self::T ),
				'description'   => __( 'shows at the top of the screen', self::T ),
				'image'         => 'tvu-header-bar-design.png',
				'edit_selector' => '.thrv_ult_bar',
			),
			self::DESIGN_TYPE_FOOTER_BAR => array(
				'name'          => __( 'Bottom ribbon', self::T ),
				'description'   => __( 'shows at the bottom of the screen', self::T ),
				'image'         => 'tvu-footer-bar-design.png',
				'edit_selector' => '.thrv_ult_bar',
			),
			self::DESIGN_TYPE_WIDGET     => array(
				'name'          => __( 'Widget', self::T ),
				'description'   => __( 'displays in any widget area on your site', self::T ),
				'image'         => 'tvu-widget-design.png',
				'edit_selector' => '.thrv_ult_widget',
			),
			self::DESIGN_TYPE_SHORTCODE  => array(
				'name'          => __( 'Shortcode', self::T ),
				'description'   => __( 'display shortcode design in your content', self::T ),
				'image'         => 'tvu-shortcode-design.png',
				'edit_selector' => '.thrv_ult_shortcode',
			),
		);
	}

	/**
	 * Gets details for a specific design type
	 *
	 * @param $design_type
	 *
	 * @return null|array
	 */
	public static function design_details( $design_type ) {
		$designs = self::design_types_details();
		if ( ! array_key_exists( $design_type, $designs ) ) {
			return null;
		}

		return $designs[ $design_type ];
	}

	/**
	 * Campaign attribute templates with details
	 *      name        translated name
	 *      description translated description
	 *
	 * @return array
	 */
	public static function campaign_attribute_templates() {
		$event = TU_Event_Action::get_details( TU_Event_Action::DESIGN_SHOW );

		return array(
			'0' => array(
				'id'           => '0',
				'is_empty'     => true,
				'name'         => __( 'Build from scratch', self::T ),
				'description'  => 'Build a campaign from scratch with no predefined settings',
				'image'        => self::plugin_url( 'admin/img/tvu-campaign-template1.png' ),
				'type'         => '',
				'rolling_type' => '',
				'status'       => self::CAMPAIGN_STATUS_PAUSED,
				'settings'     => '',
			),
			'1' => array(
				'id'           => '1',
				'name'         => __( '7 day offer', self::T ),
				'description'  => __( '7 day Evergreen offer triggered when a user first visits the site. 3 days before the end of the 7 day period, the state is changed to display that the offer is in its last 3 days.', self::T ),
				'image'        => self::plugin_url( 'admin/img/tvu-campaign-template2.png' ),
				'type'         => self::CAMPAIGN_TYPE_EVERGREEN,
				'rolling_type' => '',
				'status'       => self::CAMPAIGN_STATUS_PAUSED,
				'settings'     => array(
					'start'    => array(
						'date' => tve_ult_current_time( 'j F Y' ),
						'time' => '00:00',
					),
					'end'      => '10000',
					'duration' => '7',
					'repeat'   => 0,
					'real'     => 0,
					'repeatOn' => array(),
					'trigger'  => array(
						'type' => self::TRIGGER_TYPE_FIRST_VISIT,
						'ids'  => '',
					),
				),
				'designs'      => array(
					'design_0' => array(
						'post_type'   => self::DESIGN_TYPE_HEADER_BAR,
						'post_title'  => '7 day offer',
						'post_parent' => '',
						'post_status' => TVE_Ult_Const::STATUS_PUBLISH,
						'parent_id'   => 0,
						'tcb_fields'  => array(),
						'tpl'         => 'ribbon|set_01',
						'states'      => array(
							'state_0' => array(
								'post_title'  => 'Last 3 days',
								'post_type'   => self::DESIGN_TYPE_HEADER_BAR,
								'post_parent' => '',
								'post_status' => TVE_Ult_Const::STATUS_PUBLISH,
								'parent_id'   => 0,
								'tcb_fields'  => array(),
								'tpl'         => 'ribbon|set_01',
							),
						),
					),
				),
				'events'       => array(
					'event_0' => array(
						'campaign_id' => 0,
						'actions'     => array(
							'0' => array(
								'key'    => $event['key'],
								'design' => 'design_0',
								'state'  => 'state_0',
								'name'   => $event['name'] . ' <strong>7 day offer (Last 3 days!)',
							),
						),
						'days'        => 3,
						'hours'       => 0,
					),
				),
			),
			'2' => array(
				'id'           => '2',
				'name'         => __( 'Christmas special', self::T ),
				'description'  => __( 'Triggered to start and end on specific dates. When the offer has 2 days left, the state is changed to reflect the small amount of time before the offer expires.', self::T ),
				'image'        => self::plugin_url( 'admin/img/tvu-campaign-template3.png' ),
				'type'         => self::CAMPAIGN_TYPE_ABSOLUTE,
				'rolling_type' => '',
				'status'       => self::CAMPAIGN_STATUS_PAUSED,
				'settings'     => array(
					'start'    => array(
						'date' => '17 December ' . tve_ult_current_time( 'Y' ),
						'time' => '00:00',
					),
					'end'      => array(
						'date' => '24 December ' . tve_ult_current_time( 'Y' ),
						'time' => '00:00',
					),
					'duration' => '1',
					'repeat'   => '1',
					'repeatOn' => array(),
					'trigger'  => array(
						'type' => '',
						'ids'  => '',
					),
				),
				'designs'      => array(
					'design_0' => array(
						'post_type'   => self::DESIGN_TYPE_HEADER_BAR,
						'post_title'  => 'Christmas special',
						'post_parent' => '',
						'post_status' => TVE_Ult_Const::STATUS_PUBLISH,
						'parent_id'   => 0,
						'tcb_fields'  => array(),
						'tpl'         => 'ribbon|set_01',
						'states'      => array(
							'state_0' => array(
								'post_title'  => '2 days left!',
								'post_type'   => self::DESIGN_TYPE_HEADER_BAR,
								'post_parent' => '',
								'post_status' => TVE_Ult_Const::STATUS_PUBLISH,
								'parent_id'   => 0,
								'tcb_fields'  => array(),
								'tpl'         => 'ribbon|set_01',
							),
						),
					),
				),
				'events'       => array(
					'event_0' => array(
						'campaign_id' => 0,
						'actions'     => array(
							'0' => array(
								'key'    => $event['key'],
								'design' => 'design_0',
								'state'  => 'state_0',
								'name'   => $event['name'] . ' <strong>Christmas special (2 days left!)</strong>',
							),
						),
						'days'        => 2,
						'hours'       => 0,
					),
				),
			),
			'3' => array(
				'id'           => '3',
				'name'         => __( 'End of month specials', self::T ),
				'description'  => __( 'Triggered to start on a certain day of the month and to last for 2 days.', self::T ),
				'image'        => self::plugin_url( 'admin/img/tvu-campaign-template4.png' ),
				'type'         => self::CAMPAIGN_TYPE_ROLLING,
				'rolling_type' => self::CAMPAIGN_ROLLING_TYPE_MONTHLY,
				'status'       => self::CAMPAIGN_STATUS_PAUSED,
				'settings'     => array(
					'start'    => array(
						'date' => tve_ult_current_time( 'j F Y' ),
						'time' => '00:00',
					),
					'end'      => '',
					'duration' => '2',
					'repeat'   => '1',
					'repeatOn' => array( 28 ),
					'trigger'  => array(
						'type' => '',
						'ids'  => '',
					),
				),
				'designs'      => array(
					'design_0' => array(
						'post_type'   => self::DESIGN_TYPE_HEADER_BAR,
						'post_parent' => '',
						'post_status' => TVE_Ult_Const::STATUS_PUBLISH,
						'parent_id'   => 0,
						'tcb_fields'  => array(),
						'tpl'         => 'ribbon|set_01',
						'states'      => array(),
					),
				),
				'events'       => array(),
			),
		);
	}

	/**
	 * Gets details for a specific campaign attribute template
	 *
	 * @param $template_key
	 *
	 * @return null|array
	 */
	public static function campaign_template_details( $template_key ) {
		$templates = self::campaign_attribute_templates();
		if ( ! array_key_exists( $template_key, $templates ) ) {
			return null;
		}

		return $templates[ $template_key ];
	}

	/**
	 * A list with all fields that TCB uses to store various pieces of content / flags
	 *
	 * @return array
	 */
	public static function editor_fields() {
		return array(
			self::FIELD_CUSTOM_FONTS,
			self::FIELD_GLOBALS,
			self::FIELD_MASONRY,
			self::FIELD_TYPEFOCUS,
			self::FIELD_ICON_PACK,
			self::FIELD_INLINE_CSS,
			self::FIELD_CONTENT,
			self::FIELD_USER_CSS,
			self::FIELD_TEMPLATE,
			self::FIELD_STATE_INDEX,
			self::FIELD_STATE_VISIBILITY,
		);
	}

	/**
	 * All possible event types
	 *
	 * @return array
	 */
	public static function event_types() {
		return array(
			self::EVENT_TYPE_START => self::EVENT_TYPE_START,
			self::EVENT_TYPE_TIME  => self::EVENT_TYPE_TIME,
			self::EVENT_TYPE_CONV  => self::EVENT_TYPE_CONV,
			self::EVENT_TYPE_END   => self::EVENT_TYPE_END,
		);
	}

	/**
	 * Possible log types.
	 *
	 * @return array
	 */
	public static function log_types() {
		return array(
			self::LOG_TYPE_CONVERSION,
			self::LOG_TYPE_IMPRESSION,
		);
	}
}
