<?php
/**
 * @package WPSEO_Local\Admin\
 * @since   4.0
 */

if ( ! defined( 'WPSEO_LOCAL_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

if ( ! class_exists( 'WPSEO_Local_Admin_General_Settings' ) ) {

	/**
	 * WPSEO_Local_Admin_General_Settings class.
	 *
	 * Build the WPSEO Local admin form.
	 *
	 * @since   4.0
	 */
	class WPSEO_Local_Admin_General_Settings {

		/**
		 * @var string Holds the slug for this settings tab.
		 */
		private $slug = 'general';

		/**
		 * @var mixed Holds WPSEO Local Core instance.
		 */
		private $wpseo_local_core;

		/**
		 * @var mixed Holds WPSEO Local Timezone Repository instance.
		 */
		private $wpseo_local_timezone_repository;

		/**
		 * @var mixed wpseo_local options.
		 */
		private $options;

		/**
		 * WPSEO_Local_Admin_General_Settings constructor.
		 */
		public function __construct() {
			$this->get_core();
			$this->get_timezone_repository();
			$this->get_options();

			add_filter( 'wpseo_local_admin_tabs', array( $this, 'create_tab' ) );
			add_filter( 'wpseo_local_admin_help_center_video', array( $this, 'set_video' ) );

			add_action( 'wpseo_local_admin_' . $this->slug . '_content', array( $this, 'multiple_locations' ), 10 );
			add_action( 'wpseo_local_admin_' . $this->slug . '_content', array( $this, 'single_location_settings' ), 10 );
			add_action( 'wpseo_local_admin_' . $this->slug . '_content', array( $this, 'multiple_locations_settings' ), 10 );
			add_action( 'wpseo_local_admin_' . $this->slug . '_content', array( $this, 'business_image' ), 10 );
			add_action( 'wpseo_local_admin_' . $this->slug . '_content', array( $this, 'opening_hours' ), 10 );
			add_action( 'wpseo_local_admin_' . $this->slug . '_content', array( $this, 'store_locator' ), 10 );
			add_action( 'wpseo_local_admin_' . $this->slug . '_content', array( $this, 'advanced' ), 10 );
			add_action( 'wpseo_local_admin_' . $this->slug . '_content', array( $this, 'local_config' ), 10 );

			add_action( 'pre_update_option_wpseo_local', array( $this, 'update_lat_long' ), 10, 2 );
			add_action( 'pre_update_option_wpseo_local', array( $this, 'update_timezone' ), 10, 2 );
		}

		/**
		 * Set WPSEO Local Core instance in local property
		 */
		private function get_core() {
			global $wpseo_local_core;
			$this->wpseo_local_core = $wpseo_local_core;
		}

		/**
		 * Set WPSEO Local Core Timezone Repository in local property
		 */
		private function get_timezone_repository() {
			$wpseo_local_timezone_repository       = new WPSEO_Local_Timezone_Repository();
			$this->wpseo_local_timezone_repository = $wpseo_local_timezone_repository;
		}

		/**
		 * Get wpseo_local options.
		 */
		private function get_options() {
			$this->options = get_option( 'wpseo_local' );
		}

		/**
		 * @param array $tabs Array holding the tabs.
		 *
		 * @return mixed
		 */
		public function create_tab( $tabs ) {
			$tabs[ $this->slug ] = __( 'General settings', 'yoast-local-seo' );

			return $tabs;
		}

		/**
		 * @param array $videos Array holding the videos for the help center.
		 *
		 * @return mixed
		 */
		public function set_video( $videos ) {
			$videos[ $this->slug ] = 'https://yoa.st/screencast-local-settings';

			return $videos;
		}

		/**
		 * Add local config action.
		 */
		public function local_config() {
			do_action( 'wpseo_local_config' );
		}

		/**
		 * Multiple locations checkbox
		 */
		public function multiple_locations() {
			WPSEO_Local_Admin_Page::section_before( 'select-multiple-locations' );
			echo '<p>' . sprintf( __( 'If you have more than one location, you can enable this feature. %s will create a new Custom Post Type for you where you can manage your locations. If it\'s not enabled you can enter your address details below. These fields will be ignored when you enable this option.', 'yoast-local-seo' ), 'Yoast SEO' ) . '</p>';
			WPSEO_Local_Admin_Wrappers::checkbox( 'use_multiple_locations', '', __( 'Use multiple locations', 'yoast-local-seo' ) );
			WPSEO_Local_Admin_Page::section_after(); // End select-multiple-locations section.
		}

		/**
		 * Single locations settings section.
		 */
		public function single_location_settings() {
			WPSEO_Local_Admin_Page::section_before( 'single-location-settings', 'clear: both; ' . ( wpseo_has_multiple_locations() ? 'display: none;' : '' ) );
			WPSEO_Local_Admin_Wrappers::textinput( 'location_name', apply_filters( 'yoast-local-seo-admin-label-business-name', __( 'Business name', 'yoast-local-seo' ) ) );

			WPSEO_Local_Admin_Wrappers::select( 'business_type', apply_filters( 'yoast-local-seo-admin-label-business-type', __( 'Business type', 'yoast-local-seo' ) ), $this->wpseo_local_core->get_local_business_types() );
			echo '<p class="desc label" style="border:none; margin-bottom: 0;">' . sprintf( __( 'If your business type is not listed, please read %1$sthe FAQ entry%2$s.', 'yoast-local-seo' ), '<a href="http://kb.yoast.com/article/49-my-business-is-not-listed-can-you-add-it" target="_blank">', '</a>' ) . '</p>';

			WPSEO_Local_Admin_Wrappers::textinput( 'location_address', apply_filters( 'yoast-local-seo-admin-label-business-address', __( 'Business address', 'yoast-local-seo' ) ) );
			WPSEO_Local_Admin_Wrappers::textinput( 'location_address_2', apply_filters( 'yoast-local-seo-admin-label-business-address-2', __( 'Business address line 2', 'yoast-local-seo' ) ) );
			WPSEO_Local_Admin_Wrappers::textinput( 'location_city', apply_filters( 'yoast-local-seo-admin-label-business-city', __( 'Business city', 'yoast-local-seo' ) ) );
			WPSEO_Local_Admin_Wrappers::textinput( 'location_state', apply_filters( 'yoast-local-seo-admin-label-business-state', __( 'Business state', 'yoast-local-seo' ) ) );
			WPSEO_Local_Admin_Wrappers::textinput( 'location_zipcode', apply_filters( 'yoast-local-seo-admin-label-business-zipcode', __( 'Business zipcode', 'yoast-local-seo' ) ) );
			WPSEO_Local_Admin_Wrappers::select( 'location_country', apply_filters( 'yoast-local-seo-admin-label-business-country', __( 'Business country', 'yoast-local-seo' ) ), WPSEO_Local_Frontend::get_country_array() );
			WPSEO_Local_Admin_Wrappers::textinput( 'location_phone', apply_filters( 'yoast-local-seo-admin-label-business-phone', __( 'Business phone', 'yoast-local-seo' ) ) );
			WPSEO_Local_Admin_Wrappers::textinput( 'location_phone_2nd', apply_filters( 'yoast-local-seo-admin-label-business-phone-2', __( '2nd Business phone', 'yoast-local-seo' ) ) );
			WPSEO_Local_Admin_Wrappers::textinput( 'location_fax', apply_filters( 'yoast-local-seo-admin-label-business-fax', __( 'Business fax', 'yoast-local-seo' ) ) );
			WPSEO_Local_Admin_Wrappers::textinput( 'location_email', apply_filters( 'yoast-local-seo-admin-label-business-email', __( 'Business email', 'yoast-local-seo' ) ) );
			WPSEO_Local_Admin_Wrappers::textinput( 'location_url', apply_filters( 'yoast-local-seo-admin-label-business-url', __( 'URL', 'yoast-local-seo' ) ), '', array( 'placeholder' => WPSEO_Sitemaps_Router::get_base_url( '' ) ) );
			WPSEO_Local_Admin_Wrappers::textinput( 'location_vat_id', apply_filters( 'yoast-local-seo-admin-label-business-vat-id', __( 'VAT ID', 'yoast-local-seo' ) ) );
			WPSEO_Local_Admin_Wrappers::textinput( 'location_tax_id', apply_filters( 'yoast-local-seo-admin-label-business-tax-id', __( 'Tax ID', 'yoast-local-seo' ) ) );
			WPSEO_Local_Admin_Wrappers::textinput( 'location_coc_id', apply_filters( 'yoast-local-seo-admin-label-business-coc-id', __( 'Chamber of Commerce ID', 'yoast-local-seo' ) ) );
			WPSEO_Local_Admin_Wrappers::textinput( 'location_price_range', apply_filters( 'yoast-local-seo-admin-label-business-price-range', __( 'Price range', 'yoast-local-seo' ) ) );

			echo '<p>' . __( 'You can enter the lat/long coordinates yourself. If you leave them empty they will be calculated automatically. If you want to re-calculate these fields, please make them blank before saving this location.', 'yoast-local-seo' ) . '</p>';
			WPSEO_Local_Admin_Wrappers::textinput( 'location_coords_lat', apply_filters( 'yoast-local-seo-admin-label-business-lat', __( 'Latitude', 'yoast-local-seo' ) ) );
			WPSEO_Local_Admin_Wrappers::textinput( 'location_coords_long', apply_filters( 'yoast-local-seo-admin-label-business-long', __( 'Longitude', 'yoast-local-seo' ) ) );
			// Only show the map when lat/long coords are there.
			if ( '' != $this->options['location_coords_lat'] && '' != $this->options['location_coords_long'] ) {
				echo '<p>' . __( 'If the marker is not in the right location for your store, you can drag the pin to the location where you want it.', 'yoast-local-seo' ) . '</p>';

				wpseo_local_show_map( array(
					'echo'       => true,
					'show_route' => false,
					'map_style'  => 'roadmap',
					'draggable'  => true,
				) );
			}
			WPSEO_Local_Admin_Page::section_after(); // End show-single-locaton section.
		}

		/**
		 * Multiple locations settings section.
		 */
		public function multiple_locations_settings() {
			WPSEO_Local_Admin_Page::section_before( 'multiple-locations-settings', 'clear: both; ' . ( wpseo_has_multiple_locations() ? '' : 'display: none;' ) );
			WPSEO_Local_Admin_Wrappers::textinput( 'locations_slug', apply_filters( 'yoast-local-seo-admin-label-locations-slug', __( 'Locations slug', 'yoast-local-seo' ) ) );
			echo '<p class="desc label" style="border: 0; margin-bottom: 0; padding-bottom: 0;">' . __( 'The slug for your location pages. Default slug is <code>locations</code>.', 'yoast-local-seo' ) . '<br>';
			if ( wpseo_has_multiple_locations() ) {
				echo '<a href="' . get_post_type_archive_link( 'wpseo_locations' ) . '" target="_blank">' . __( 'View them all', 'yoast-local-seo' ) . '</a> ' . __( 'or', 'yoast-local-seo' ) . ' <a href="' . admin_url( 'edit.php?post_type=wpseo_locations' ) . '">' . __( 'edit them', 'yoast-local-seo' ) . '</a>';
			}
			echo '</p>';
			WPSEO_Local_Admin_Wrappers::textinput( 'locations_label_singular', apply_filters( 'yoast-local-seo-admin-label-locations-label', __( 'Locations label singular', 'yoast-local-seo' ) ) );
			echo '<p class="desc label" style="border: 0; margin-bottom: 0; padding-bottom: 0;">' . __( 'The singular label for your location pages. Default label is <code>Location</code>.', 'yoast-local-seo' ) . '<br>';
			echo '</p>';
			WPSEO_Local_Admin_Wrappers::textinput( 'locations_label_plural', apply_filters( 'yoast-local-seo-admin-label-locations-label-plural', __( 'Locations label plural', 'yoast-local-seo' ) ) );
			echo '<p class="desc label" style="border: 0; margin-bottom: 0; padding-bottom: 0;">' . __( 'The plural label for your location pages. Default label is <code>Locations</code>.', 'yoast-local-seo' ) . '<br>';
			echo '</p>';
			WPSEO_Local_Admin_Wrappers::textinput( 'locations_taxo_slug', apply_filters( 'yoast-local-seo-admin-label-locations-category-slug', __( 'Locations category slug', 'yoast-local-seo' ) ) );
			echo '<p class="desc label" style="border: 0; margin-bottom: 0; padding-bottom: 0;">' . __( 'The slug for your location categories. Default slug is <code>locations-category</code>.', 'yoast-local-seo' ) . '<br>';
			if ( wpseo_has_multiple_locations() ) {
				echo '<a href="' . admin_url( 'edit-tags.php?taxonomy=wpseo_locations_category&post_type=wpseo_locations' ) . '">' . __( 'Edit the categories', 'yoast-local-seo' ) . '</a>';
			}
			echo '</p>';
			WPSEO_Local_Admin_Page::section_after();
		}

		/**
		 * Business image section.
		 */
		public function business_image() {
			WPSEO_Local_Admin_Page::section_before( 'wpseo-local-business_image', null, 'wpseo-local-business_image-wrapper' );
			echo '<h2>' . __( 'Business image', 'yoast-local-seo' ) . '</h2>';
			echo '<label class="textinput" for="business_image">' . __( 'Business image', 'yoast-local-seo' ) . ':</label>';
			WPSEO_Local_Admin_Wrappers::hidden( 'business_image' );

			$business_image = ! empty( $this->options['business_image'] );
			echo '<img src="' . ( isset( $this->options['business_image'] ) ? wp_get_attachment_image_url( $this->options['business_image'], 'medium' ) : '' ) . '" id="business_image_image_container" class="wpseo-local-hide-button' . ( ( false == $business_image ) ? ' hidden' : '' ) . '">';
			echo '<br class="wpseo-local-hide-button' . ( ( false == $business_image ) ? ' hidden' : '' ) . '">';
			echo '<p class="desc label" style="border: none; margin-bottom: 0;">';
			echo '<a href="javascript:" class="set_custom_images button" data-id="business_image">' . __( 'Set image', 'yoast-local-seo' ) . '</a>';
			echo ' <a href="javascript:" class="remove_custom_image wpseo-local-hide-button' . ( ( false == $business_image ) ? ' hidden' : '' ) . '" style="color: #a00 !important;" data-id="business_image">' . __( 'Remove image', 'yoast-local-seo' ) . '</a>';
			echo '</p>';
			WPSEO_Local_Admin_Page::section_after();
		}

		/**
		 * Opening hours settings section.
		 */
		public function opening_hours() {
			WPSEO_Local_Admin_Page::section_before( 'opening-hours-container', 'clear: both; ' );
			echo '<h2>' . __( 'Opening hours', 'yoast-local-seo' ) . '</h2>';
			WPSEO_Local_Admin_Wrappers::checkbox( 'hide_opening_hours', '', __( 'Hide opening hours option', 'yoast-local-seo' ) );

			$hide_opening_hours = isset( $this->options['hide_opening_hours'] ) && $this->options['hide_opening_hours'] == 'on';
			WPSEO_Local_Admin_Page::section_before( 'opening-hours-settings', 'clear: both; display:' . ( ( true == $hide_opening_hours ) ? 'none' : 'block' ) . ';' );
			WPSEO_Local_Admin_Wrappers::textinput( 'closed_label', __( 'Closed label', 'yoast-local-seo' ) );
			WPSEO_Local_Admin_Wrappers::checkbox( 'opening_hours_24h', '', __( 'Use 24h format', 'yoast-local-seo' ) );
			WPSEO_Local_Admin_Page::section_after(); // End opening-hours-inner section.
			WPSEO_Local_Admin_Page::section_before( 'opening-hours-hours', 'clear: both; display:' . ( ( true == $hide_opening_hours || wpseo_has_multiple_locations() ) ? 'none' : 'block' ) . ';' );
			WPSEO_Local_Admin_Wrappers::checkbox( 'multiple_opening_hours', '', __( 'I have two sets of opening hours per day', 'yoast-local-seo' ) );
			echo '<p>' . __( 'If a specific day only has one set of opening hours, please set the second set for that day to <strong>closed</strong>', 'yoast-local-seo' ) . '</p>';
			foreach ( $this->wpseo_local_core->days as $key => $day ) {
				$field_name        = 'opening_hours_' . $key;
				$value_from        = isset( $this->options[ $field_name . '_from' ] ) ? esc_attr( $this->options[ $field_name . '_from' ] ) : '09:00';
				$value_to          = isset( $this->options[ $field_name . '_to' ] ) ? esc_attr( $this->options[ $field_name . '_to' ] ) : '17:00';
				$value_second_from = isset( $this->options[ $field_name . '_second_from' ] ) ? esc_attr( $this->options[ $field_name . '_second_from' ] ) : '09:00';
				$value_second_to   = isset( $this->options[ $field_name . '_second_to' ] ) ? esc_attr( $this->options[ $field_name . '_second_to' ] ) : '17:00';

				$use_24_hours = isset( $this->options['opening_hours_24h'] ) ? $this->options['opening_hours_24h'] : false;
				WPSEO_Local_Admin_Page::section_before( 'opening-hours-' . $key, null, 'opening-hours' );
				echo '<label class="textinput">' . $day . ':</label>';
				echo '<select class="openinghours_from" style="width: 100px;" id="' . $field_name . '_from" name="wpseo_local[' . $field_name . '_from]">';
				echo wpseo_show_hour_options( $use_24_hours, $value_from );
				echo '</select><span id="' . $field_name . '_to_wrapper"> - ';
				echo '<select class="openinghours_to" style="width: 100px;" id="' . $field_name . '_to" name="wpseo_local[' . $field_name . '_to]">';
				echo wpseo_show_hour_options( $use_24_hours, $value_to );
				echo '</select>';

				WPSEO_Local_Admin_Page::section_before( 'opening-hours-second-' . $key, null, 'opening-hours-second ' . ( ( empty( $this->options['multiple_opening_hours'] ) || $this->options['multiple_opening_hours'] != 'on' ) ? 'hidden' : '' ) . '' );
				echo '<label class="textinput">&nbsp;</label>';
				echo '<select class="openinghours_from_second" style="width: 100px;" id="' . $field_name . '_second_from" name="wpseo_local[' . $field_name . '_second_from]">';
				echo wpseo_show_hour_options( $use_24_hours, $value_second_from );
				echo '</select><span id="' . $field_name . '_second_to_wrapper"> - ';
				echo '<select class="openinghours_to_second" style="width: 100px;" id="' . $field_name . '_second_to" name="wpseo_local[' . $field_name . '_second_to]">';
				echo wpseo_show_hour_options( $use_24_hours, $value_second_to );
				echo '</select>';
				WPSEO_Local_Admin_Page::section_after(); // End opening-hours-second-{key} section.

				WPSEO_Local_Admin_Page::section_after(); // End opening-hours-{$key} section.
			}

			WPSEO_Local_Admin_Page::section_after(); // End opening-hours-hours section.

			WPSEO_Local_Admin_Page::section_after(); // End opening-hours-container section.
		}

		/**
		 * Store locator settings section.
		 */
		public function store_locator() {
			WPSEO_Local_Admin_Page::section_before( 'sl-settings', 'clear: both; ' . ( wpseo_has_multiple_locations() ? '' : 'display: none;' ) );
			echo '<h2>' . __( 'Store locator settings', 'yoast-local-seo' ) . '</h2>';
			WPSEO_Local_Admin_Wrappers::textinput( 'sl_num_results', __( 'Number of results', 'yoast-local-seo' ) );
			WPSEO_Local_Admin_Page::section_after();
		}

		/**
		 * Advanced settings section.
		 */
		public function advanced() {
			WPSEO_Local_Admin_Page::section_before( 'wpseo-local-advanced' );
			echo '<h2>' . __( 'Advanced settings', 'yoast-local-seo' ) . '</h2>';
			WPSEO_Local_Admin_Wrappers::select( 'unit_system', __( 'Unit System', 'yoast-local-seo' ), array(
				'METRIC'   => __( 'Metric', 'yoast-local-seo' ),
				'IMPERIAL' => __( 'Imperial', 'yoast-local-seo' ),
			) );

			if ( true == is_ssl() ) {
				WPSEO_Local_Admin_Wrappers::checkbox( 'detect_location', __( 'Automatically detect the users location as starting point.', 'yoast-local-seo' ), __( 'Location detection', 'yoast-local-seo' ) );
			}
			else {
				echo '<label class="checkbox" for="detect_location">Location detection:</label>';
				echo '<p class="desc label" style="border:none; margin-bottom: 0;"><em>' . __( 'This option only works on HTTPS websites.', 'yoast-local-seo' ) . '</em></p>';
			}

			WPSEO_Local_Admin_Wrappers::select( 'map_view_style', __( 'Default map style', 'yoast-local-seo' ), array(
				'HYBRID'    => __( 'Hybrid', 'yoast-local-seo' ),
				'SATELLITE' => __( 'Satellite', 'yoast-local-seo' ),
				'ROADMAP'   => __( 'Roadmap', 'yoast-local-seo' ),
				'TERRAIN'   => __( 'Terrain', 'yoast-local-seo' ),
			) );
			WPSEO_Local_Admin_Wrappers::select( 'address_format', __( 'Address format', 'yoast-local-seo' ), array(
				'address-state-postal'       => '{address} {city}, {state} {zipcode} &nbsp;&nbsp;&nbsp;&nbsp; (New York, NY 12345 )',
				'address-state-postal-comma' => '{address} {city}, {state}, {zipcode} &nbsp;&nbsp;&nbsp;&nbsp; (New York, NY, 12345 )',
				'address-postal-city-state'  => '{address} {zipcode} {city}, {state} &nbsp;&nbsp;&nbsp;&nbsp; (12345 New York, NY )',
				'address-postal'             => '{address} {city} {zipcode} &nbsp;&nbsp;&nbsp;&nbsp; (New York 12345 )',
				'address-postal-comma'       => '{address} {city}, {zipcode} &nbsp;&nbsp;&nbsp;&nbsp; (New York, 12345 )',
				'address-city'               => '{address} {city} &nbsp;&nbsp;&nbsp;&nbsp; (New York)',
				'postal-address'             => '{zipcode} {state} {city} {address} &nbsp;&nbsp;&nbsp;&nbsp; (12345 NY New York)',
			) );

			/* translators: %s extends to <a href="mailto:pluginsupport@yoast.com">pluginsupport@yoast.com</a> */
			echo '<p class="desc label" style="border:none; margin-bottom: 0;">' . sprintf( __( 'A lot of countries have their own address format. Please choose one that matches yours. If you have something completely different, please let us know via %s.', 'yoast-local-seo' ), '<a href="mailto:pluginsupport@yoast.com">pluginsupport@yoast.com</a>' ) . '</p>';

			// Chosen allows us to clear a set option (to pass no value), but to do that it requires an empty option.
			$countries = ( array( '' => '' ) + WPSEO_Local_Frontend::get_country_array() );

			WPSEO_Local_Admin_Wrappers::select( 'default_country', __( 'Default country', 'yoast-local-seo' ), $countries );

			echo '<p class="desc label" style="border:none; margin-bottom: 0;">' . __( 'If you\'re having multiple locations and they\'re all in one country, you can select your default country here. This country will be used in the storelocator search to improve the search results.', 'yoast-local-seo' ) . '</p>';
			WPSEO_Local_Admin_Wrappers::textinput( 'show_route_label', __( '"Show route" label', 'yoast-local-seo' ), '', array( 'placeholder' => __( 'Show route', 'yoast-local-seo' ) ) );

			WPSEO_Local_Admin_Page::section_before( 'wpseo-local-custom-marker', null, 'wpseo-local-custom_marker-wrapper' );
			echo '<label class="textinput" for="custom_marker">' . __( 'Custom marker', 'yoast-local-seo' ) . ':</label>';
			WPSEO_Local_Admin_Wrappers::hidden( 'custom_marker' );

			$show_marker = ! empty( $this->options['custom_marker'] );
			echo '<img src="' . ( isset( $this->options['custom_marker'] ) ? wp_get_attachment_url( $this->options['custom_marker'] ) : '' ) . '" id="custom_marker_image_container" class="wpseo-local-hide-button' . ( ( false == $show_marker ) ? ' hidden' : '' ) . '">';
			echo '<br class="wpseo-local-hide-button' . ( ( false == $show_marker ) ? ' hidden' : '' ) . '">';
			echo '<p class="desc label" style="border: none; margin-bottom: 0;">';
			echo '<a href="javascript:" class="set_custom_images button" data-id="custom_marker">' . __( 'Set custom marker image', 'yoast-local-seo' ) . '</a>';
			echo ' <a href="javascript:" class="remove_custom_image wpseo-local-hide-button' . ( ( false == $show_marker ) ? ' hidden' : '' ) . '" style="color: #a00 !important;" data-id="custom_marker">' . __( 'Remove marker', 'yoast-local-seo' ) . '</a>';
			echo '</p>';
			if ( true == $show_marker ) {
				$this->wpseo_local_core->check_custom_marker_size( $this->options['custom_marker'] );
			}
			else {
				echo '<p class="desc label" style="border:none; margin-bottom: 0;">' . __( 'The custom marker should be 100x100 px. If the image exceeds those dimensions it could (partially) cover the info popup.', 'yoast-local-seo' ) . '</p>';
			}
			WPSEO_Local_Admin_Page::section_after(); // End wpseo-local-custom-marker section.
			WPSEO_Local_Admin_Page::section_after(); // End wpseo-local-advanced section.
		}

		/**
		 * @param array $new_value New value for wpseo_local options.
		 * @param array $old_value Old value for wpseo_local options.
		 *
		 * @return mixed
		 */
		public function update_lat_long( $new_value, $old_value ) {
			// Calculate lat/long coordinates when address is entered.
			if ( empty( $old_value['location_coords_lat'] ) || empty( $old_value['location_coords_long'] ) ) {
				$location_coordinates = $this->wpseo_local_core->get_geo_data( array(
					'_wpseo_business_address' => isset( $new_value['location_address'] ) ? esc_attr( $new_value['location_address'] ) : '',
					'_wpseo_business_city'    => isset( $new_value['location_city'] ) ? esc_attr( $new_value['location_city'] ) : '',
					'_wpseo_business_state'   => isset( $new_value['location_state'] ) ? esc_attr( $new_value['location_state'] ) : '',
					'_wpseo_business_zipcode' => isset( $new_value['location_zipcode'] ) ? esc_attr( $new_value['location_zipcode'] ) : '',
					'_wpseo_business_country' => isset( $new_value['location_country'] ) ? esc_attr( $new_value['location_country'] ) : '',
				), true );
				if ( ! empty( $location_coordinates['coords'] ) ) {
					$new_value['location_coords_lat']  = str_replace( ',', '.', $location_coordinates['coords']['lat'] );
					$new_value['location_coords_long'] = str_replace( ',', '.', $location_coordinates['coords']['long'] );
				}
			}

			return $new_value;
		}

		/**
		 * @param array $new_value New value for wpseo_local options.
		 * @param array $old_value Old value for wpseo_local options.
		 *
		 * @return mixed
		 */
		public function update_timezone( $new_value, $old_value ) {
			// Multiple locations are set, there is no need for this update.
			if ( wpseo_has_multiple_locations() ) {
				return $new_value;
			}

			if ( empty( $old_value['location_coords_lat'] ) || empty( $old_value['location_coords_long'] ) ) {
				$timezone = $this->wpseo_local_timezone_repository->get_coords_timezone();

				if ( ! empty( $timezone ) ) {
					$new_value['location_timezone'] = $timezone;
				}
			}

			return $new_value;
		}
	}
}
