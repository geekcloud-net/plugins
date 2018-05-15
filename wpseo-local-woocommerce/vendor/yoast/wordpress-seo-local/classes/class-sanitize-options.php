<?php
/**
 * @package WPSEO_Local\Main
 * @since   3.7
 */

if ( ! class_exists( 'WPSEO_Local_Sanitize_Options' ) ) {

	/**
	 * WPSEO_Local_Sanitize_Options class. Handles the sanitation of option fields from the Local SEO plugin
	 */
	class WPSEO_Local_Sanitize_Options {
		/**
		 * Constructor for the WPSEO_Local_Sanitize_Options.
		 *
		 * @since 3.9
		 */
		public function __construct() {
			add_filter( 'pre_update_option_wpseo_local', array( $this, 'sanitize_options' ), 10, 2 );
		}

		/**
		 * Sanitize options for wpseo_local
		 *
		 * @param string $new_value New option value.
		 * @param string $old_value Old option value.
		 *
		 * @return mixed $new_value
		 *
		 * @since 3.9
		 */
		public function sanitize_options( $new_value, $old_value ) {
			// Slugs.
			$new_value['locations_slug']     = isset( $new_value['locations_slug'] ) ? sanitize_title( $new_value['locations_slug'] ) : WPSEO_Local_Core::$defaults['locations_slug'];
			$new_value['location_taxo_slug'] = isset( $new_value['location_taxo_slug'] ) ? sanitize_title( $new_value['locations_taxo_slug'] ) : WPSEO_Local_Core::$defaults['location_taxo_slug'];

			// Labels.
			$new_value['locations_label_singular'] = isset( $new_value['locations_label_singular'] ) ? sanitize_text_field( $new_value['locations_label_singular'] ) : WPSEO_Local_Core::$defaults['locations_label_singular'];
			$new_value['locations_label_plural']   = isset( $new_value['locations_label_plural'] ) ? sanitize_text_field( $new_value['locations_label_plural'] ) : WPSEO_Local_Core::$defaults['locations_label_plural'];
			$new_value['show_route_label']         = isset( $new_value['show_route_label'] ) ? sanitize_text_field( $new_value['show_route_label'] ) : WPSEO_Local_Core::$defaults['show_route_label'];

			// Single location settings.
			$new_value['location_name']        = isset( $new_value['location_name'] ) ? sanitize_text_field( $new_value['location_name'] ) : WPSEO_Local_Core::$defaults['location_name'];
			$new_value['business_type']        = isset( $new_value['business_type'] ) ? sanitize_text_field( $new_value['business_type'] ) : WPSEO_Local_Core::$defaults['business_type'];
			$new_value['location_address']     = isset( $new_value['location_address'] ) ? sanitize_text_field( $new_value['location_address'] ) : WPSEO_Local_Core::$defaults['location_address'];
			$new_value['location_city']        = isset( $new_value['location_city'] ) ? sanitize_text_field( $new_value['location_city'] ) : WPSEO_Local_Core::$defaults['location_city'];
			$new_value['location_state']       = isset( $new_value['location_state'] ) ? sanitize_text_field( $new_value['location_state'] ) : WPSEO_Local_Core::$defaults['location_state'];
			$new_value['location_zipcode']     = isset( $new_value['location_zipcode'] ) ? sanitize_text_field( $new_value['location_zipcode'] ) : WPSEO_Local_Core::$defaults['location_zipcode'];
			$new_value['location_country']     = isset( $new_value['location_country'] ) ? sanitize_text_field( $new_value['location_country'] ) : WPSEO_Local_Core::$defaults['location_country'];
			$new_value['location_phone']       = isset( $new_value['location_phone'] ) ? sanitize_text_field( $new_value['location_phone'] ) : WPSEO_Local_Core::$defaults['location_phone'];
			$new_value['location_phone_2nd']   = isset( $new_value['location_phone_2nd'] ) ? sanitize_text_field( $new_value['location_phone_2nd'] ) : WPSEO_Local_Core::$defaults['location_phone_2nd'];
			$new_value['location_fax']         = isset( $new_value['location_fax'] ) ? sanitize_text_field( $new_value['location_fax'] ) : WPSEO_Local_Core::$defaults['location_fax'];
			$new_value['location_email']       = isset( $new_value['location_email'] ) ? sanitize_email( $new_value['location_email'] ) : WPSEO_Local_Core::$defaults['location_email'];
			$new_value['location_url']         = isset( $new_value['location_url'] ) ? esc_url_raw( $new_value['location_url'] ) : WPSEO_Local_Core::$defaults['location_url'];
			$new_value['location_vat_id']      = isset( $new_value['location_vat_id'] ) ? sanitize_text_field( $new_value['location_vat_id'] ) : WPSEO_Local_Core::$defaults['location_vat_id'];
			$new_value['location_tax_id']      = isset( $new_value['location_tax_id'] ) ? sanitize_text_field( $new_value['location_tax_id'] ) : WPSEO_Local_Core::$defaults['location_tax_id'];
			$new_value['location_coc_id']      = isset( $new_value['location_coc_id'] ) ? sanitize_text_field( $new_value['location_coc_id'] ) : WPSEO_Local_Core::$defaults['location_coc_id'];
			$new_value['location_coords_lat']  = isset( $new_value['location_coords_lat'] ) ? filter_var( $new_value['location_coords_lat'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION ) : WPSEO_Local_Core::$defaults['location_coords_lat'];
			$new_value['location_coords_long'] = isset( $new_value['location_coords_long'] ) ? filter_var( $new_value['location_coords_long'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION ) : WPSEO_Local_Core::$defaults['location_coords_long'];

			// Opening hours.
			$new_value['opening_hours_monday_from']           = isset( $new_value['opening_hours_monday_from'] ) ? sanitize_text_field( $new_value['opening_hours_monday_from'] ) : WPSEO_Local_Core::$defaults['opening_hours_monday_from'];
			$new_value['opening_hours_monday_to']             = isset( $new_value['opening_hours_monday_to'] ) ? sanitize_text_field( $new_value['opening_hours_monday_to'] ) : WPSEO_Local_Core::$defaults['opening_hours_monday_to'];
			$new_value['opening_hours_monday_second_from']    = isset( $new_value['opening_hours_monday_second_from'] ) ? sanitize_text_field( $new_value['opening_hours_monday_second_from'] ) : WPSEO_Local_Core::$defaults['opening_hours_monday_second_from'];
			$new_value['opening_hours_monday_second_to']      = isset( $new_value['opening_hours_monday_second_to'] ) ? sanitize_text_field( $new_value['opening_hours_monday_second_to'] ) : WPSEO_Local_Core::$defaults['opening_hours_monday_second_to'];
			$new_value['opening_hours_tuesday_from']          = isset( $new_value['opening_hours_tuesday_from'] ) ? sanitize_text_field( $new_value['opening_hours_tuesday_from'] ) : WPSEO_Local_Core::$defaults['opening_hours_tuesday_from'];
			$new_value['opening_hours_tuesday_to']            = isset( $new_value['opening_hours_tuesday_to'] ) ? sanitize_text_field( $new_value['opening_hours_tuesday_to'] ) : WPSEO_Local_Core::$defaults['opening_hours_tuesday_to'];
			$new_value['opening_hours_tuesday_second_from']   = isset( $new_value['opening_hours_tuesday_second_from'] ) ? sanitize_text_field( $new_value['opening_hours_tuesday_second_from'] ) : WPSEO_Local_Core::$defaults['opening_hours_tuesday_second_from'];
			$new_value['opening_hours_tuesday_second_to']     = isset( $new_value['opening_hours_tuesday_second_to'] ) ? sanitize_text_field( $new_value['opening_hours_tuesday_second_to'] ) : WPSEO_Local_Core::$defaults['opening_hours_tuesday_second_to'];
			$new_value['opening_hours_wednesday_from']        = isset( $new_value['opening_hours_wednesday_from'] ) ? sanitize_text_field( $new_value['opening_hours_wednesday_from'] ) : WPSEO_Local_Core::$defaults['opening_hours_wednesday_from'];
			$new_value['opening_hours_wednesday_to']          = isset( $new_value['opening_hours_wednesday_to'] ) ? sanitize_text_field( $new_value['opening_hours_wednesday_to'] ) : WPSEO_Local_Core::$defaults['opening_hours_wednesday_to'];
			$new_value['opening_hours_wednesday_second_from'] = isset( $new_value['opening_hours_wednesday_second_from'] ) ? sanitize_text_field( $new_value['opening_hours_wednesday_second_from'] ) : WPSEO_Local_Core::$defaults['opening_hours_wednesday_second_from'];
			$new_value['opening_hours_wednesday_second_to']   = isset( $new_value['opening_hours_wednesday_second_to'] ) ? sanitize_text_field( $new_value['opening_hours_wednesday_second_to'] ) : WPSEO_Local_Core::$defaults['opening_hours_wednesday_second_to'];
			$new_value['opening_hours_thursday_from']         = isset( $new_value['opening_hours_thursday_from'] ) ? sanitize_text_field( $new_value['opening_hours_thursday_from'] ) : WPSEO_Local_Core::$defaults['opening_hours_thursday_from'];
			$new_value['opening_hours_thursday_to']           = isset( $new_value['opening_hours_thursday_to'] ) ? sanitize_text_field( $new_value['opening_hours_thursday_to'] ) : WPSEO_Local_Core::$defaults['opening_hours_thursday_to'];
			$new_value['opening_hours_thursday_second_from']  = isset( $new_value['opening_hours_thursday_second_from'] ) ? sanitize_text_field( $new_value['opening_hours_thursday_second_from'] ) : WPSEO_Local_Core::$defaults['opening_hours_thursday_second_from'];
			$new_value['opening_hours_thursday_second_to']    = isset( $new_value['opening_hours_thursday_second_to'] ) ? sanitize_text_field( $new_value['opening_hours_thursday_second_to'] ) : WPSEO_Local_Core::$defaults['opening_hours_thursday_second_to'];
			$new_value['opening_hours_friday_from']           = isset( $new_value['opening_hours_friday_from'] ) ? sanitize_text_field( $new_value['opening_hours_friday_from'] ) : WPSEO_Local_Core::$defaults['opening_hours_friday_from'];
			$new_value['opening_hours_friday_to']             = isset( $new_value['opening_hours_friday_to'] ) ? sanitize_text_field( $new_value['opening_hours_friday_to'] ) : WPSEO_Local_Core::$defaults['opening_hours_friday_to'];
			$new_value['opening_hours_friday_second_from']    = isset( $new_value['opening_hours_friday_second_from'] ) ? sanitize_text_field( $new_value['opening_hours_friday_second_from'] ) : WPSEO_Local_Core::$defaults['opening_hours_friday_second_from'];
			$new_value['opening_hours_friday_second_to']      = isset( $new_value['opening_hours_friday_second_to'] ) ? sanitize_text_field( $new_value['opening_hours_friday_second_to'] ) : WPSEO_Local_Core::$defaults['opening_hours_friday_second_to'];
			$new_value['opening_hours_saturday_from']         = isset( $new_value['opening_hours_saturday_from'] ) ? sanitize_text_field( $new_value['opening_hours_saturday_from'] ) : WPSEO_Local_Core::$defaults['opening_hours_saturday_from'];
			$new_value['opening_hours_saturday_to']           = isset( $new_value['opening_hours_saturday_to'] ) ? sanitize_text_field( $new_value['opening_hours_saturday_to'] ) : WPSEO_Local_Core::$defaults['opening_hours_saturday_to'];
			$new_value['opening_hours_saturday_second_from']  = isset( $new_value['opening_hours_saturday_second_from'] ) ? sanitize_text_field( $new_value['opening_hours_saturday_second_from'] ) : WPSEO_Local_Core::$defaults['opening_hours_saturday_second_from'];
			$new_value['opening_hours_saturday_second_to']    = isset( $new_value['opening_hours_saturday_second_to'] ) ? sanitize_text_field( $new_value['opening_hours_saturday_second_to'] ) : WPSEO_Local_Core::$defaults['opening_hours_saturday_second_to'];
			$new_value['opening_hours_sunday_from']           = isset( $new_value['opening_hours_sunday_from'] ) ? sanitize_text_field( $new_value['opening_hours_sunday_from'] ) : WPSEO_Local_Core::$defaults['opening_hours_sunday_from'];
			$new_value['opening_hours_sunday_to']             = isset( $new_value['opening_hours_sunday_to'] ) ? sanitize_text_field( $new_value['opening_hours_sunday_to'] ) : WPSEO_Local_Core::$defaults['opening_hours_sunday_to'];
			$new_value['opening_hours_sunday_second_from']    = isset( $new_value['opening_hours_sunday_second_from'] ) ? sanitize_text_field( $new_value['opening_hours_sunday_second_from'] ) : WPSEO_Local_Core::$defaults['opening_hours_sunday_second_from'];
			$new_value['opening_hours_sunday_second_to']      = isset( $new_value['opening_hours_sunday_second_to'] ) ? sanitize_text_field( $new_value['opening_hours_sunday_second_to'] ) : WPSEO_Local_Core::$defaults['opening_hours_sunday_second_to'];

			// Address settings.
			$new_value['address_format'] = isset( $new_value['address_format'] ) ? sanitize_text_field( $new_value['address_format'] ) : WPSEO_Local_Core::$defaults['address_format'];

			// Map and store locator settings.
			$new_value['unit_system']      = isset( $new_value['unit_system'] ) ? sanitize_text_field( $new_value['unit_system'] ) : WPSEO_Local_Core::$defaults['unit_system'];
			$new_value['map_view_style']   = isset( $new_value['map_view_style'] ) ? sanitize_text_field( $new_value['map_view_style'] ) : WPSEO_Local_Core::$defaults['map_view_style'];
			$new_value['default_country']  = isset( $new_value['default_country'] ) ? sanitize_text_field( $new_value['default_country'] ) : WPSEO_Local_Core::$defaults['default_country'];
			$new_value['show_route_label'] = isset( $new_value['show_route_label'] ) ? sanitize_text_field( $new_value['show_route_label'] ) : WPSEO_Local_Core::$defaults['show_route_label'];
			$new_value['custom_marker']    = isset( $new_value['custom_marker'] ) ? filter_var( $new_value['custom_marker'], FILTER_SANITIZE_NUMBER_INT ) : WPSEO_Local_Core::$defaults['custom_marker'];
			$new_value['sl_num_results']   = isset( $new_value['sl_num_results'] ) ? filter_var( $new_value['sl_num_results'], FILTER_SANITIZE_NUMBER_INT ) : WPSEO_Local_Core::$defaults['sl_num_results'];

			// API Keys.
			$new_value['api_key_browser'] = isset( $new_value['api_key_browser'] ) ? sanitize_text_field( $new_value['api_key_browser'] ) : WPSEO_Local_Core::$defaults['api_key_browser'];
			$new_value['api_key']         = isset( $new_value['api_key'] ) ? sanitize_text_field( $new_value['api_key'] ) : WPSEO_Local_Core::$defaults['api_key'];

			return $new_value;
		}
	}
}
