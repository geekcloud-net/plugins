<?php
/**
 * @package WPSEO_Local\Frontend
 */

/**
 * Address shortcode handler
 *
 * @since 0.1
 *
 * @param array $atts Array of shortcode parameters.
 *
 * @return string
 */
function wpseo_local_show_address( $atts ) {
	$atts = wpseo_check_falses( shortcode_atts( array(
		'id'                 => '',
		'term_id'            => '',
		'max_number'         => '',
		'hide_name'          => false,
		'hide_address'       => false,
		'show_state'         => true,
		'show_country'       => true,
		'show_phone'         => true,
		'show_phone_2'       => true,
		'show_fax'           => true,
		'show_email'         => true,
		'show_url'           => false,
		'show_vat'           => false,
		'show_tax'           => false,
		'show_coc'           => false,
		'show_price_range'   => false,
		'show_logo'          => false,
		'show_opening_hours' => false,
		'hide_closed'        => false,
		'oneline'            => false,
		'comment'            => '',
		'from_sl'            => false,
		'from_widget'        => false,
		'widget_title'       => '',
		'before_title'       => '',
		'after_title'        => '',
		'hide_json_ld'       => false,
		'echo'               => false,
	), $atts, 'wpseo_local_show_address' ) );

	$options = get_option( 'wpseo_local' );
	if ( isset( $options['hide_opening_hours'] ) && $options['hide_opening_hours'] == 'on' ) {
		$atts['show_opening_hours'] = false;
	}

	$is_postal_address = false;
	$output            = '';

	/*
	* This array can be used in a filter to change the order and the labels of contact details
	*/
	$business_contact_details = array(
		array(
			'key'   => 'phone',
			'label' => __( 'Phone', 'yoast-local-seo' ),
		),
		array(
			'key'   => 'phone_2',
			'label' => __( 'Secondary phone', 'yoast-local-seo' ),
		),
		array(
			'key'   => 'fax',
			'label' => __( 'Fax', 'yoast-local-seo' ),
		),
		array(
			'key'   => 'email',
			'label' => __( 'Email', 'yoast-local-seo' ),
		),
		array(
			'key'   => 'url',
			'label' => __( 'URL', 'yoast-local-seo' ),
		),
		array(
			'key'   => 'vat',
			'label' => __( 'VAT ID', 'yoast-local-seo' ),
		),
		array(
			'key'   => 'tax',
			'label' => __( 'Tax ID', 'yoast-local-seo' ),
		),
		array(
			'key'   => 'coc',
			'label' => __( 'Chamber of Commerce ID', 'yoast-local-seo' ),
		),
		array(
			'key'   => 'price_range',
			'label' => __( 'Price range', 'yoast-local-seo' ),
		),
	);

	$business_contact_details = apply_filters( 'wpseo_local_contact_details', $business_contact_details );

	// Initiate the Locations repository and get query the locations.
	$repo      = new WPSEO_Local_Locations_Repository();
	$locations = $repo->get( array(
		'id'          => explode( ',', $atts['id'] ),
		'category_id' => $atts['term_id'],
		'number'      => $atts['max_number'],
	) );

	foreach ( $locations as $location ) {
		$tag_title_open  = '';
		$tag_title_close = '';
		if ( ! $atts['oneline'] ) {
			if ( ! $atts['from_widget'] ) {
				$tag_name        = apply_filters( 'wpseo_local_location_title_tag_name', 'h3' );
				$tag_title_open  = '<' . esc_html( $tag_name ) . '>';
				$tag_title_close = '</' . esc_html( $tag_name ) . '>';
			}
			else {
				if ( $atts['from_widget'] && $atts['widget_title'] == '' ) {
					$tag_title_open  = $atts['before_title'];
					$tag_title_close = $atts['after_title'];
				}
			}
		}
		$container_id = 'wpseo_location-' . esc_attr( $atts['id'] );
		$output = '<div id="' . $container_id . '" class="wpseo-location">';

		if ( false == $atts['hide_name'] ) {
			$pt_object = get_post_type_object( 'wpseo_locations' );
			$output .= $tag_title_open . ( ( $atts['from_sl'] && $pt_object->public ) ? '<a href="' . esc_url( $location['business_url'] ) . '">' : '' ) . '<span class="wpseo-business-name">' . esc_html( $location['business_name'] ) . '</span>' . ( ( $atts['from_sl'] ) ? '</a>' : '' ) . $tag_title_close;
		}

		if ( $atts['show_logo'] && ! empty( $location['business_logo'] ) && is_numeric( $location['business_logo'] ) ) {
			$output .= '<figure>';
			$output .= wp_get_attachment_image( $location['business_logo'], apply_filters( 'yoast_seo_local_business_logo_size', 'full' ) );
			$output .= '</figure>';
		}
		$output .= '<' . ( ( $atts['oneline'] ) ? 'span' : 'div' ) . ' class="wpseo-address-wrapper">';

		// Output city/state/zipcode in right format.
		$address_format = ! empty( $options['address_format'] ) ? $options['address_format'] : 'address-state-postal';

		$format                = new WPSEO_Local_Address_Format();
		$address_format_output = $format->get_address_format( $address_format, array(
			'show_logo'          => ! empty( $business_location_logo ) ? true : false,
			'business_address'   => $location['business_address'],
			'business_address_2' => $location['business_address_2'],
			'oneline'            => $atts['oneline'],
			'business_zipcode'   => $location['business_zipcode'],
			'business_city'      => $location['business_city'],
			'business_state'     => $location['business_state'],
			'show_state'         => $atts['show_state'],
			'escape_output'      => false,
			'use_tags'           => true,
		) );

		if ( ! empty( $address_format_output ) && false === $atts['hide_address'] ) {
			$output .= $address_format_output;
		}

		if ( $atts['show_country'] && ! empty( $location['business_country'] ) ) {
			$output .= ( $atts['oneline'] ) ? ', ' : ' ';
		}

		if ( $atts['show_country'] && ! empty( $location['business_country'] ) ) {
			$output .= '<' . ( ( $atts['oneline'] ) ? 'span' : 'div' ) . '  class="country-name">' . WPSEO_Local_Frontend::get_country( $location['business_country'] ) . '</' . ( ( $atts['oneline'] ) ? 'span' : 'div' ) . '>';
		}

		$output .= '</' . ( ( $atts['oneline'] ) ? 'span' : 'div' ) . '>';
		$details_output = '';

		foreach ( $business_contact_details as $order => $details ) {
			if ( 'phone' == $details['key'] && $atts['show_phone'] && ! empty( $location['business_phone'] ) ) {
				/* translators: %s extends to the label for phone */
				$details_output .= sprintf( '<span class="wpseo-phone">%s: <a href="' . esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $location['business_phone'] ) ) . '" class="tel"><span>' . esc_html( $location['business_phone'] ) . '</span></a></span>' . ( ( $atts['oneline'] ) ? ' ' : '<br/>' ), esc_html( $details['label'] ) );
			}

			if ( 'phone_2' == $details['key'] && $atts['show_phone_2'] && ! empty( $location['business_phone_2nd'] ) ) {
				/* translators: %s extends to the label for 2nd phone */
				$details_output .= sprintf( '<span class="wpseo-phone2nd">%s: <a href="' . esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $location['business_phone_2nd'] ) ) . '" class="tel">' . esc_html( $location['business_phone_2nd'] ) . '</a></span>' . ( ( $atts['oneline'] ) ? ' ' : '<br/>' ), esc_html( $details['label'] ) );
			}

			if ( 'fax' == $details['key'] && $atts['show_fax'] && ! empty( $location['business_fax'] ) ) {
				/* translators: %s extends to the label for fax */
				$details_output .= sprintf( '<span class="wpseo-fax">%s: <span class="tel">' . esc_html( $location['business_fax'] ) . '</span></span>' . ( ( $atts['oneline'] ) ? ' ' : '<br/>' ), esc_html( $details['label'] ) );
			}

			if ( 'email' == $details['key'] && $atts['show_email'] && ! empty( $location['business_email'] ) ) {
				/* translators: %s extends to the label for e-mail */
				$details_output .= sprintf( '<span class="wpseo-email">%s: <a href="' . esc_url( 'mailto:' . antispambot( $location['business_email'] ) ) . '">' . antispambot( esc_html( $location['business_email'] ) ) . '</a></span>' . ( ( $atts['oneline'] ) ? ' ' : '<br/>' ), esc_html( $details['label'] ) );
			}

			if ( 'url' == $details['key'] && $atts['show_url'] ) {
				/* translators: %s extends to the label for business url */
				$details_output .= sprintf( '<span class="wpseo-url">%s: <a href="' . esc_url( $location['business_url'] ) . '">' . esc_html( $location['business_url'] ) . '</a></span>' . ( ( $atts['oneline'] ) ? ' ' : '<br/>' ), esc_html( $details['label'] ) );
			}

			if ( 'vat' == $details['key'] && $atts['show_vat'] && ! empty( $location['business_vat'] ) ) {
				/* translators: %s extends to the label for businss VAT number */
				$details_output .= sprintf( '<span class="wpseo-vat">%s: <span>' . esc_html( $location['business_vat'] ) . '</span></span>' . ( ( $atts['oneline'] ) ? ' ' : '<br/>' ), esc_html( $details['label'] ) );
			}

			if ( 'tax' == $details['key'] && $atts['show_tax'] && ! empty( $location['business_tax'] ) ) {
				/* translators: %s extends to the label for business tax number */
				$details_output .= sprintf( '<span class="wpseo-tax">%s: <span>' . esc_html( $location['business_tax'] ) . '</span></span>' . ( ( $atts['oneline'] ) ? ' ' : '<br/>' ), esc_html( $details['label'] ) );
			}

			if ( 'coc' == $details['key'] && $atts['show_coc'] && ! empty( $location['business_coc'] ) ) {
				/* translators: %s extends to the label for business COC number*/
				$details_output .= sprintf( '<span class="wpseo-coc">%s: ' . esc_html( $location['business_coc'] ) . '</span>' . ( ( $atts['oneline'] ) ? ' ' : '<br/>' ), esc_html( $details['label'] ) );
			}

			if ( 'price_range' == $details['key'] && $atts['show_price_range'] && ! empty( $location['business_price_range'] ) ) {
				/* translators: %s extends to the label for business Price Range */
				$details_output .= sprintf( '<span class="wpseo-price-range">%s: ' . esc_html( $location['business_price_range'] ) . '</span>' . ( ( $atts['oneline'] ) ? ' ' : '<br/>' ), esc_html( $details['label'] ) );
			}
		}

		if ( '' != $details_output && true == $atts['oneline'] ) {
			$output .= ' - ';
		}

		$output .= $details_output;
		if ( $atts['show_opening_hours'] ) {
			$args = array(
				'id'          => ( wpseo_has_multiple_locations() ) ? $atts['id'] : '',
				'hide_closed' => $atts['hide_closed'],
			);
			$output .= '<br/>' . wpseo_local_show_opening_hours( $args, true, false ) . '<br/>';
		}
		$output .= '</div>';
	}

	if ( $atts['comment'] != '' ) {
		$output .= '<div class="wpseo-extra-comment">' . wpautop( html_entity_decode( $atts['comment'] ) ) . '</div>';
	}

	if ( $atts['echo'] ) {
		echo $output;
	}

	// Add filter to add optional output. This is for example used to add JSON+LD.
	if ( true !== $atts['hide_json_ld'] ) {
		$output = apply_filters( 'wpseo_show_address_after', $output, $atts['id'], $container_id );
	}

	return $output;
}

/**
 * Shortcode for shoing all locations at once. May come handy for "office overview" pages
 *
 * @since 1.1.7
 *
 * @param array $atts Array of shortcode parameters.
 *
 * @return string
 */
function wpseo_local_show_all_locations( $atts  ) {
	$atts = wpseo_check_falses( shortcode_atts( array(
		'number'             => -1,
		'term_id'            => '',
		'orderby'            => 'menu_order title',
		'order'              => 'ASC',
		'show_state'         => true,
		'show_country'       => true,
		'show_phone'         => true,
		'show_phone_2'       => true,
		'show_fax'           => true,
		'show_email'         => true,
		'show_url'           => false,
		'show_opening_hours' => false,
		'hide_closed'        => false,
		'oneline'            => false,
		'echo'               => false,
		'comment'            => '',
	), $atts, 'wpseo_local_show_all_locations' ) );

	// Don't show any data when post_type is not activated. This function/shortcode makes no sense for single location.
	if ( ! wpseo_has_multiple_locations() ) {
		return '';
	}

	$output = '';
	$repo   = new WPSEO_Local_Locations_Repository();

	if ( '' != $atts['term_id'] ) {
		$locations = $repo->get( array(
			'category_id' => $atts['term_id'],
			'number'      => $atts['number'],
			'orderby'     => $atts['orderby'],
			'order'       => $atts['order'],
		), false );
	}
	else {
		$locations = $repo->get( array(
			'number'  => $atts['number'],
			'orderby' => $atts['orderby'],
			'order'   => $atts['order'],
		), false );
	}

	if ( count( $locations ) > 0 ) {
		$output .= '<div class="wpseo-all-locations">';
		foreach ( $locations as $location_id ) {

			$location = apply_filters( 'wpseo_all_locations_location', wpseo_local_show_address( array(
				'id'                 => $location_id,
				'show_state'         => $atts['show_state'],
				'show_country'       => $atts['show_country'],
				'show_phone'         => $atts['show_phone'],
				'show_phone_2'       => $atts['show_phone_2'],
				'show_fax'           => $atts['show_fax'],
				'show_email'         => $atts['show_email'],
				'show_url'           => $atts['show_url'],
				'show_opening_hours' => $atts['show_opening_hours'],
				'hide_closed'        => $atts['hide_closed'],
				'oneline'            => $atts['oneline'],
				'echo'               => false,
			) ) );

			$output .= $location;

		}

		if ( $atts['comment'] != '' ) {
			$output .= '<div class="wpseo-extra-comment">' . wpautop( html_entity_decode( $atts['comment'] ) ) . '</div>';
		}

		$output .= '</div>';
	}
	else {
		echo '<p>' . __( 'There are no locations to show.', 'yoast-local-seo' ) . '</p>';
	}

	if ( $atts['echo'] ) {
		echo $output;
	}

	return $output;
}

/**
 * Maps shortcode handler
 *
 * @since 0.1
 *
 * @param array $atts Array of shortcode parameters.
 *
 * @return string
 */
function wpseo_local_show_map( $atts ) {
	global $map_counter, $wpseo_enqueue_geocoder, $wpseo_map;

	$options = get_option( 'wpseo_local' );

	// Define all used variables.
	$location_array     = array();
	$lats               = array();
	$longs              = array();
	$all_categories     = array();
	$location_array_str = '';
	$map                = '';

	// Backwards compatibility for scrollable / zoomable functions.
	if ( is_array( $atts ) && ! array_key_exists( 'zoomable', $atts ) ) {
		$atts['zoomable'] = ( isset( $atts['scrollable'] ) ) ? $atts['scrollable'] : true;
	}

	$atts = wpseo_check_falses( shortcode_atts( array(
		'id'                      => '',
		'term_id'                 => '',
		'center'                  => '',
		'max_number'              => '',
		'width'                   => 400,
		'height'                  => 300,
		'zoom'                    => -1,
		'show_route'              => true,
		'show_state'              => true,
		'show_country'            => false,
		'show_url'                => false,
		'show_email'              => false,
		'default_show_infowindow' => false,
		'map_style'               => ( isset( $options['map_view_style'] ) ) ? $options['map_view_style'] : 'ROADMAP',
		'scrollable'              => true,
		'draggable'               => true,
		'marker_clustering'       => false,
		'show_route_label'        => ( isset( $options['show_route_label'] ) && ! empty( $options['show_route_label'] ) ) ? $options['show_route_label'] : __( 'Show route', 'yoast-local-seo' ),
		'from_sl'                 => false,
		'show_category_filter'    => false,
		'hide_json_ld'            => ( wpseo_has_multiple_locations() ? false : true ),
		'echo'                    => false,
	), $atts, 'wpseo_local_show_map' ) );

	if ( ! isset( $map_counter ) ) {
		$map_counter = 0;
	}
	else {
		$map_counter++;
	}

	// Check if zoom is set to true or false by the wpseo_check_falses function. If so, turn them back into 0 or 1.
	if ( true === $atts['zoom'] ) {
		$atts['zoom'] = 1;
	}
	else {
		if ( false === $atts['zoom'] ) {
			$atts['zoom'] = 0;
		}
	}

	// Initiate the Locations repository and get query the locations.
	$repo      = new WPSEO_Local_Locations_Repository();
	$locations = $repo->get( array(
		'id'          => explode( ',', $atts['id'] ),
		'category_id' => $atts['term_id'],
		'number'      => $atts['max_number'],
	) );

	// Initialize JSON+LD class.
	$wpseo_json_ld = null;
	if ( true !== $atts['hide_json_ld'] ) {
		$wpseo_json_ld = new WPSEO_Local_JSON_LD();
	}

	$noscript_output = '<ul>';
	foreach ( $locations as $location_key => $location ) {
		$terms = array();
		if ( isset( $location['terms'] ) && ! empty( $location['terms'] ) ) {
			foreach ( $location['terms'] as $key => $term ) {
				$terms[ $term->slug ]          = $term->name;
				$all_categories[ $term->slug ] = $term->name;
			}
		}

		if ( ( post_type_exists( 'wpseo_locations' ) && true === get_post_type_object( 'wpseo_locations' )->public ) ) {
			$self_url = get_permalink( $location['post_id'] );
		}
		else {
			$self_url = '';
		}

		// Allow the option for a user to alter the URL that shows in the maps infowindow box.
		$self_url = apply_filters( 'yoast_seo_local_change_map_location_url', $self_url, $location['post_id'] );

		if ( $location['coords']['lat'] != '' && $location['coords']['long'] != '' ) {
			$full_address = wpseo_local_show_address( array(
				'id'                 => isset( $location['post_id'] ) ? $location['post_id'] : '',
				'hide_name'          => true,
				'business_address'   => wpseo_cleanup_string( $location['business_address'] ),
				'business_address_2' => wpseo_cleanup_string( $location['business_address_2'] ),
				'oneline'            => false,
				'business_zipcode'   => $location['business_zipcode'],
				'business_city'      => $location['business_city'],
				'business_state'     => $location['business_state'],
				'show_state'         => $atts['show_state'],
				'show_country'       => $atts['show_country'],
				'show_email'         => $atts['show_email'],
				'show_url'           => $atts['show_url'],
				'escape_output'      => true,
				'use_tags'           => true,
				'hide_json_ld'       => true, // Don't show JSON+LD in the infoWindow, since Google cannot parse it here.
			) );
			$location_array_str .= "location_data.push( {
				'name': '" . wpseo_cleanup_string( $location['business_name'] ) . "',
				'url': '" . wpseo_cleanup_string( $location['business_url'] ) . "',
				'address': " . wp_json_encode( $full_address ) . ",
				'country': '" . WPSEO_Local_Frontend::get_country( $location['business_country'] ) . "',
				'show_country': " . ( ( $atts['show_country'] ) ? 'true' : 'false' ) . ",
				'url': '" . esc_url( $location['business_url'] ) . "',
				'show_url': " . ( ( $atts['show_url'] ) ? 'true' : 'false' ) . ",
				'email': '" . antispambot( $location['business_email'] ) . "',
				'show_email': " . ( ( $atts['show_email'] ) ? 'true' : 'false' ) . ",
				'phone': '" . wpseo_cleanup_string( $location['business_phone'] ) . "',
				'phone_2nd': '" . wpseo_cleanup_string( $location['business_phone_2nd'] ) . "',
				'lat': " . wpseo_cleanup_string( $location['coords']['lat'] ) . ",
				'long': " . wpseo_cleanup_string( $location['coords']['long'] ) . ",
				'custom_marker': '" . wpseo_cleanup_string( $location['custom_marker'] ) . "',
				'categories': " . json_encode( $terms ) . ",
				'self_url': '" . $self_url . "',
			} );\n";
		}
		$noscript_output .= '<li>';
		if ( $location['business_url'] != get_permalink() ) {
			$noscript_output .= '<a href="' . $location['business_url'] . '">';
		}
		$noscript_output .= $location['business_name'];
		if ( $location['business_url'] != get_permalink() ) {
			$noscript_output .= '</a>';
		}

		if ( true !== $atts['hide_json_ld'] ) {
			$noscript_output .= '<script type="application/ld+json">' . $wpseo_json_ld->get_full_output_for_location( $location['post_id'] ) . '</script>';
		}
		$noscript_output .= '</li>';

		$full_address                                    = $location['business_address'] . ', ' . $location['business_city'] . ( ( strtolower( $location['business_country'] ) == 'us' ) ? ', ' . $location['business_state'] : '' ) . ', ' . $location['business_zipcode'] . ', ' . WPSEO_Local_Frontend::get_country( $location['business_country'] );
		$location_array[ $location_key ]['full_address'] = $full_address;

		// Add coordinates to lats and longs arrays to use in centering.
		if ( ! empty( $location['coords']['lat'] ) && is_numeric( $location['coords']['lat'] ) ) {
			$lats[] = $location['coords']['lat'];
		}
		if ( ! empty( $location['coords']['long'] ) && is_numeric( $location['coords']['long'] ) ) {
			$longs[] = $location['coords']['long'];
		}
	}
	$noscript_output .= '</ul>';

	$map                    = '';
	$wpseo_enqueue_geocoder = true;

	if ( ! is_array( $lats ) || empty( $lats ) || ! is_array( $longs ) || empty( $longs ) ) {
		return;
	}

	if ( $atts['center'] === '' ) {
		$center_lat  = ( min( $lats ) + ( ( max( $lats ) - min( $lats ) ) / 2 ) );
		$center_long = ( min( $longs ) + ( ( max( $longs ) - min( $longs ) ) / 2 ) );
	}
	else {
		$center_lat  = get_post_meta( $atts['center'], '_wpseo_coordinates_lat', true );
		$center_long = get_post_meta( $atts['center'], '_wpseo_coordinates_long', true );
	}

	// Default to zoom 10 if there's only one location as a center + bounds would zoom in far too much.
	if ( -1 == $atts['zoom'] && 1 === count( $location_array ) ) {
		$atts['zoom'] = 10;
	}

	if ( $location_array_str != '' ) {
		$wpseo_map .= '<script type="text/javascript">
			var map_' . $map_counter . ';
			var directionsDisplay_' . $map_counter . ';

			function wpseo_map_init' . ( ( $map_counter != 0 ) ? '_' . $map_counter : '' ) . '() {
				var location_data = new Array();' . PHP_EOL . $location_array_str . '
				map_' . $map_counter . ' = wpseo_show_map( location_data, ' . $map_counter . ', ' . $center_lat . ', ' . $center_long . ', ' . $atts['zoom'] . ', "' . $atts['map_style'] . '", "' . $atts['scrollable'] . '", "' . $atts['draggable'] . '", "' . $atts['default_show_infowindow'] . '", "' . is_admin() . '", "' . $atts['marker_clustering'] . '" );
				directionsDisplay_' . $map_counter . ' = wpseo_get_directions(map_' . $map_counter . ', location_data, ' . $map_counter . ', "' . $atts['show_route'] . '");
			}

			if( window.addEventListener )
				window.addEventListener( "load", wpseo_map_init' . ( ( $map_counter != 0 ) ? '_' . $map_counter : '' ) . ', false );
			else if(window.attachEvent )
				window.attachEvent( "onload", wpseo_map_init' . ( ( $map_counter != 0 ) ? '_' . $map_counter : '' ) . ');
		</script>' . PHP_EOL;

		// Override(reset) the setting for images inside the map.
		$map .= '<div id="map_canvas' . ( ( $map_counter != 0 ) ? '_' . $map_counter : '' ) . '" class="wpseo-map-canvas" style="max-width: 100%; width: ' . $atts['width'] . 'px; height: ' . $atts['height'] . 'px;">' . $noscript_output . '</div>';

		$route_tag   = apply_filters( 'wpseo_local_location_route_title_name', 'h3' );
		$route_label = apply_filters( 'wpseo_local_location_route_label', __( 'Route', 'yoast-local-seo' ) );

		if ( $atts['show_route'] && ( ( $atts['id'] != 'all' && strpos( $atts['id'], ',' ) === false ) || $atts['from_sl'] ) ) {
			$location = reset( $locations );
			$map .= '<div id="wpseo-directions-wrapper"' . ( ( $atts['from_sl'] ) ? ' style="display: none;"' : '' ) . '>';
			if ( ! empty( $route_label ) ) {
				$map .= '<' . esc_html( $route_tag ) . ' id="wpseo-directions" class="wpseo-directions-heading">' . $route_label . '</' . esc_html( $route_tag ) . '>';
			}
			$map .= '<form action="" method="post" class="wpseo-directions-form" id="wpseo-directions-form' . ( ( $map_counter != 0 ) ? '_' . $map_counter : '' ) . '" onsubmit="wpseo_calculate_route( map_' . $map_counter . ', directionsDisplay_' . $map_counter . ', ' . $location['coords']['lat'] . ', ' . $location['coords']['long'] . ', ' . $map_counter . '); return false;">';
			$map .= '<p>';
			$map .= __( 'Your location', 'yoast-local-seo' ) . ': <input type="text" size="20" id="origin' . ( ( $map_counter != 0 ) ? '_' . $map_counter : '' ) . '" value="' . ( ! empty( $_REQUEST['wpseo-sl-search'] ) ? esc_attr( $_REQUEST['wpseo-sl-search'] ) : '' ) . '" />';
			// Show icon for retrieving current location.
			if ( true === wpseo_may_use_current_location() ) {
				$map .= ' <a href="javascript:" class="wpseo_use_current_location" data-target="origin' . ( ( $map_counter != 0 ) ? '_' . $map_counter : '' ) . '"><img src="' . plugins_url( 'images/location-icon.svg', WPSEO_LOCAL_FILE ) . '" class="wpseo_use_current_location_image" height="24" width="24" alt="' . __( 'Use my current location', 'yoast-local-seo' ) . '" data-loading-text="' . __( 'Determining current location', 'yoast-local-seo' ) . '"></a> ';
				$map .= '<br>';
			}
			$map .= '<input type="submit" class="wpseo-directions-submit" value="' . $atts['show_route_label'] . '">';
			$map .= '<span id="wpseo-noroute" style="display: none;">' . __( 'No route could be calculated.', 'yoast-local-seo' ) . '</span>';
			$map .= '</p>';
			$map .= '</form>';
			$map .= '<div id="directions' . ( ( $map_counter != 0 ) ? '_' . $map_counter : '' ) . '"></div>';
			$map .= '</div>';
		}

		// Show the filter if categories are set, there's more than 1 and if the filter is enabled.
		if ( isset( $all_categories ) && count( $all_categories ) > 1 && $atts['show_category_filter'] ) {
			$map .= '<select id="filter-by-location-category-' . $map_counter . '" class="location-category-filter" onchange="filterMarkers(this.value, ' . $map_counter . ')">';
			$map .= '<option value="">' . __( 'All categories', 'yoast-local-seo' ) . '</option>';
			foreach ( $all_categories as $category_slug => $category_name ) {
				$map .= '<option value="' . $category_slug . '">' . $category_name . '</option>';
			}
			$map .= '</select>';
		}
	}

	if ( $atts['echo'] ) {
		echo $map;
	}

	return $map;
}

/**
 * Opening hours shortcode handler, for not breaking backwards compatibility
 *
 * @param  array $atts Array of shortcode attributes.
 *
 * @return string
 */
function wpseo_local_show_openinghours_shortcode_cb( $atts ) {
	return wpseo_local_show_opening_hours( $atts );
}

/**
 * Function for displaying opening hours
 *
 * @since 0.1
 *
 * @param array $atts        Array of shortcode parameters.
 * @param bool  $show_schema Choose to show schema.org HTML or not. This parameter is deprecated in favor of the JSON+LD implementation and won't have any effect anymore.
 * @param bool  $standalone  Whether the opening hours are used stand alone or part of another function (like address).
 *
 * @return string
 */
function wpseo_local_show_opening_hours( $atts, $show_schema = true, $standalone = true ) {
	$atts = wpseo_check_falses( shortcode_atts( array(
		'id'           => '',
		'term_id'      => '',
		'hide_closed'  => false,
		'echo'         => false,
		'comment'      => '',
		'show_days'    => array(),
		'hide_json_ld' => false,
	), $atts, 'wpseo_local_opening_hours' ) );

	$options = get_option( 'wpseo_local' );
	if ( isset( $options['hide_opening_hours'] ) && $options['hide_opening_hours'] == 'on' ) {
		return false;
	}

	// Initiate the Locations repository and get query the locations.
	$repo      = new WPSEO_Local_Locations_Repository();
	$locations = $repo->get( array(
		'id'          => explode( ',', $atts['id'] ),
		'category_id' => $atts['term_id'],
	) );
	$container_id = 'wpseo-opening-hours-' . $atts['id'];
	$output    = '';
	foreach ( $locations as $location ) {
		if ( '' == $location['business_type'] ) {
			$location['business_type'] = 'LocalBusiness';
		}
		// Output meta tags with required address information when using this as stand alone.
		if ( true == $standalone ) {
			$output .= '<div class="wpseo-opening-hours-wrapper">';
		}
		$output .= '<table class="wpseo-opening-hours" id ="' . $container_id . '">';

		// Make the array itterable (Is that a word?).
		$opening_hours_repo = new WPSEO_Local_Opening_Hours_Repository();
		$days               = $opening_hours_repo->get_days();

		if ( ! is_array( $atts['show_days'] ) ) {
			$show_days = explode( ',', $atts['show_days'] );
		}
		else {
			$show_days = (array) $atts['show_days'];
		}

		// Loop through the days array where start_of_week is the first key, with a max of 7.
		foreach ( $days as $key => $day ) {

			// Check if the opening hours for this day should be shown.
			if ( is_array( $show_days ) && ! empty( $show_days ) && ! in_array( $key, $show_days ) ) {
				continue;
			}

			$oh_post_id    = ( true === wpseo_has_multiple_locations() ) ? $location['post_id'] : 'options';
			$opening_hours = $opening_hours_repo->get_opening_hours( $key, $oh_post_id, $options );

			// Skip when it's closed on this day.
			if ( ( $opening_hours['value_from'] == 'closed' || $opening_hours['value_to'] == 'closed' ) && $atts['hide_closed'] ) {
				continue;
			}

			$output .= '<tr>';
			$output .= '<td class="day">' . $day . '</td>';
			$output .= '<td class="time">';

			$output_time = '';
			if ( $opening_hours['value_from'] != 'closed' && $opening_hours['value_to'] != 'closed' ) {
				$output_time .= '<span>' . $opening_hours['value_from_formatted'] . ' - ' . $opening_hours['value_to_formatted'] . '</span>';
			}
			else {
				$output_time .= ( ! empty( $options['closed_label'] ) ? $options['closed_label'] : __( 'Closed', 'yoast-local-seo' ) );
			}

			if ( $opening_hours['use_multiple_times'] ) {
				if ( $opening_hours['value_from'] != 'closed' && $opening_hours['value_to'] != 'closed' && $opening_hours['value_second_from'] != 'closed' && $opening_hours['value_second_to'] != 'closed' ) {
					$output_time .= '<span class="openingHoursAnd"> ' . __( 'and', 'yoast-local-seo' ) . ' </span> ';
					$output_time .= '<span>' . $opening_hours['value_second_from_formatted'] . ' - ' . $opening_hours['value_second_to_formatted'] . '</span>';
				}
				else {
					$output_time .= '';
				}
			}

			$output_time = apply_filters( 'wpseo_opening_hours_time', $output_time, $day, $opening_hours['value_from'], $opening_hours['value_to'], $atts );
			$output .= $output_time;
			$output .= '</td>';
			$output .= '</tr>';
		}

		$output .= '</table>';

		if ( true == $standalone ) {
			$output .= '</div>'; // .wpseo-opening-hours-wrapper
		}

		if ( $atts['comment'] != '' ) {
			$output .= '<div class="wpseo-extra-comment">' . wpautop( html_entity_decode( $atts['comment'] ) ) . '</div>';
		}
	}

	// Add filter to add optional output. This is for example used to add JSON+LD.
	if ( true !== $atts['hide_json_ld'] ) {
		$output = apply_filters( 'wpseo_show_opening_hours_after', $output, $atts['id'], $container_id );
	}

	if ( $atts['echo'] ) {
		echo $output;
	}

	return $output;
}

/**
 * Checks whether website uses multiple location (Custom Post Type) or not (info from options)
 *
 * @return bool Multiple locations enbaled or not
 */
function wpseo_has_multiple_locations() {
	$options = get_option( 'wpseo_local' );

	return isset( $options['use_multiple_locations'] ) && $options['use_multiple_locations'] == 'on';
}

/**
 * Check whether the usage of current location for Map routes is allowed.
 *
 * @return bool Is allowed to use current location or not.
 */
function wpseo_may_use_current_location() {
	$options = get_option( 'wpseo_local' );

	return isset( $options['detect_location'] ) && $options['detect_location'] == 'on';
}

/**
 * @param bool $use_24h True if time should be displayed in 24 hours. False if time should be displayed in AM/PM mode.
 * @param int  $default default time for dropdown.
 *
 * @return string Complete dropdown with all options.
 */
function wpseo_show_hour_options( $use_24h = false, $default = 9 ) {
	$options = get_option( 'wpseo_local' );
	$output = '<option value="closed">' . ( ! empty( $options['closed_label'] ) ? $options['closed_label'] : __( 'Closed', 'yoast-local-seo' ) ) . '</option>';

	for ( $i = 0; $i < 24; $i++ ) {
		$time                = strtotime( sprintf( '%1$02d', $i ) . ':00' );
		$time_quarter        = strtotime( sprintf( '%1$02d', $i ) . ':15' );
		$time_half           = strtotime( sprintf( '%1$02d', $i ) . ':30' );
		$time_threequarters  = strtotime( sprintf( '%1$02d', $i ) . ':45' );
		$value               = date( 'H:i', $time );
		$value_quarter       = date( 'H:i', $time_quarter );
		$value_half          = date( 'H:i', $time_half );
		$value_threequarters = date( 'H:i', $time_threequarters );

		$time_value               = date( 'g:i A', $time );
		$time_quarter_value       = date( 'g:i A', $time_quarter );
		$time_half_value          = date( 'g:i A', $time_half );
		$time_threequarters_value = date( 'g:i A', $time_threequarters );

		if ( $use_24h ) {
			$time_value               = date( 'H:i', $time );
			$time_quarter_value       = date( 'H:i', $time_quarter );
			$time_half_value          = date( 'H:i', $time_half );
			$time_threequarters_value = date( 'H:i', $time_threequarters );
		}

		$output .= '<option value="' . $value . '"' . selected( $value, $default, false ) . '>' . $time_value . '</option>';
		$output .= '<option value="' . $value_quarter . '" ' . selected( $value_quarter, $default, false ) . '>' . $time_quarter_value . '</option>';
		$output .= '<option value="' . $value_half . '" ' . selected( $value_half, $default, false ) . '>' . $time_half_value . '</option>';
		$output .= '<option value="' . $value_threequarters . '" ' . selected( $value_threequarters, $default, false ) . '>' . $time_threequarters_value . '</option>';
	}

	return $output;
}

/**
 * Geocode the given address.
 *
 * @param string $address The address that needs to be geocoded.
 *
 * @return array|WP_Error
 */
function wpseo_geocode_address( $address ) {
	$geocode_url = 'https://maps.google.com/maps/api/geocode/json?address=' . urlencode( $address ) . '&oe=utf8&sensor=false';
	$api_key     = yoast_wpseo_local_get_api_key_server();
	if ( ! empty( $api_key ) ) {
		$geocode_url .= '&key=' . $api_key;
	}

	$response = wp_remote_get( $geocode_url );

	if ( is_wp_error( $response ) || $response['response']['code'] != 200 || empty( $response['body'] ) ) {
		return new WP_Error( 'wpseo-no-response', "Didn't receive a response from Maps API" );
	}

	$response_body = json_decode( $response['body'] );

	if ( 'OK' != $response_body->status ) {
		$error_code = 'wpseo-zero-results';
		if ( $response_body->status == 'OVER_QUERY_LIMIT' ) {
			$error_code = 'wpseo-query-limit';
		}

		return new WP_Error( $error_code, $response_body->status );
	}

	return $response_body;
}

/**
 * Checks whether array keys are meant to mean false but aren't set to false.
 *
 * @param array $atts Array to check.
 *
 * @return array
 */
function wpseo_check_falses( $atts ) {
	if ( ! is_array( $atts ) ) {
		return $atts;
	}

	foreach ( $atts as $key => $value ) {
		if ( $value === 'false' || $value === 'off' || $value === 'no' || $value === '0' ) {
			$atts[ $key ] = false;
		}
		else {
			if ( $value === 'true' || $value === 'on' || $value === 'yes' || $value === '1' ) {
				$atts[ $key ] = true;
			}
		}
	}

	return $atts;
}

// Set the global to false, if the script is needed, the global will be set to true.
$wpseo_enqueue_geocoder = false;
/**
 * Places scripts in footer for Google Maps use.
 */
function wpseo_enqueue_geocoder() {
	global $wpseo_enqueue_geocoder, $wpseo_map;

	if ( is_admin() && 'wpseo_locations' == get_post_type() ) {
		global $wpseo_enqueue_geocoder;

		$wpseo_enqueue_geocoder = true;
	}

	if ( $wpseo_enqueue_geocoder ) {
		$options         = get_option( 'wpseo_local' );
		$detect_location = isset( $options['detect_location'] ) && $options['detect_location'] == 'on';
		$default_country = isset( $options['default_country'] ) ? $options['default_country'] : '';
		if ( '' != $default_country ) {
			$default_country = WPSEO_Local_Frontend::get_country( $default_country );
		}

		// Load frontend scripts.
		$script_name = 'wp-seo-local-frontend-' . yoast_seo_local_flatten_version( WPSEO_LOCAL_VERSION ) . '.min.js';
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			$script_name = 'wp-seo-local-frontend-' . yoast_seo_local_flatten_version( WPSEO_LOCAL_VERSION ) . '.js';
		}

		wp_enqueue_script( 'wpseo-local-frontend', plugins_url( 'js/dist/' . $script_name, WPSEO_LOCAL_FILE ), '', WPSEO_LOCAL_VERSION, true );

		wp_localize_script( 'wpseo-local-frontend', 'wpseo_local_data', array(
			'ajaxurl'                   => 'admin-ajax.php',
			'adminurl'                  => admin_url(),
			'has_multiple_locations'    => wpseo_has_multiple_locations(),
			'unit_system'               => ! empty( $options['unit_system'] ) ? $options['unit_system'] : 'METRIC',
			'default_country'           => $default_country,
			'detect_location'           => $detect_location,
			'marker_cluster_image_path' => apply_filters( 'wpseo_local_marker_cluster_image_path', esc_url( trailingslashit( plugin_dir_url( dirname( __FILE__ ) ) ) . 'images/m' ) ),
		) );

		// Load Maps API script.
		$locale = get_locale();
		$locale = explode( '_', $locale );

		// Check if it might be a language spoken in more than one country.
		if ( isset( $locale[1] ) && in_array( $locale[0], array(
				'en',
				'de',
				'es',
				'it',
				'pt',
				'ro',
				'ru',
				'sv',
				'nl',
				'zh',
				'fr',
			) )
		) {
			$language = $locale[0] . '-' . $locale[1];
		}
		else {
			if ( isset( $locale[1] ) ) {
				$language = $locale[1];
			}
			else {
				$language = $locale[0];
			}
		}

		// Build Google Maps embedding URL.
		$google_maps_url = '//maps.google.com/maps/api/js';
		$api_key_browser = yoast_wpseo_local_get_api_key_browser();
		if ( ! empty( $api_key_browser ) ) {
			$google_maps_url = add_query_arg( array(
				'key' => $api_key_browser,
			), $google_maps_url );
		}

		if ( ! empty( $language ) ) {
			$google_maps_url = add_query_arg( array(
				'language' => esc_attr( strtolower( $language ) ),
			), $google_maps_url );
		}

		wp_enqueue_script( 'maps-geocoder', $google_maps_url, array(), null, true );

		echo '<style type="text/css">.wpseo-map-canvas img { max-width: none !important; }</style>' . PHP_EOL;
	}

	echo $wpseo_map;
}

/**
 * This function will clean up the given string and remove all unwanted characters.
 *
 * @param string $string String that has to be cleaned.
 *
 * @uses wpseo_utf8_to_unicode() to convert string to array of unicode characters.
 * @uses wpseo_unicode_to_utf8() to convert the unicode array back to a regular string.
 * @return string The clean string.
 */
function wpseo_cleanup_string( $string ) {
	$string = esc_attr( $string );

	// First generate array of all unicodes of this string.
	$unicode_array = wpseo_utf8_to_unicode( $string );
	foreach ( $unicode_array as $key => $unicode_item ) {
		// Remove unwanted unicode characters.
		if ( in_array( $unicode_item, array( 8232 ) ) ) {
			unset( $unicode_array[ $key ] );
		}
	}

	// Revert back to normal string.
	$string = wpseo_unicode_to_utf8( $unicode_array );

	return $string;
}

/**
 * Converts a string to array of unicode characters.
 *
 * @param string $str String that has to be converted to unicde array.
 *
 * @return array Array of unicode characters.
 */
function wpseo_utf8_to_unicode( $str ) {
	$unicode     = array();
	$values      = array();
	$looking_for = 1;

	for ( $i = 0; $i < strlen( $str ); $i++ ) {
		$this_value = ord( $str[ $i ] );

		if ( $this_value < 128 ) {
			$unicode[] = $this_value;
		}
		else {
			if ( count( $values ) == 0 ) {
				$looking_for = ( $this_value < 224 ) ? 2 : 3;
			}

			$values[] = $this_value;
			if ( count( $values ) == $looking_for ) {
				$number = ( $looking_for == 3 ) ? ( ( ( $values[0] % 16 ) * 4096 ) + ( ( $values[1] % 64 ) * 64 ) + ( $values[2] % 64 ) ) : ( ( ( $values[0] % 32 ) * 64 ) + ( $values[1] % 64 ) );

				$unicode[]   = $number;
				$values      = array();
				$looking_for = 1;
			}
		}
	}

	return $unicode;
}

/**
 * Converts unicode character array back to regular string.
 *
 * @param array $string_array Array of unicode characters.
 *
 * @return string Converted string.
 */
function wpseo_unicode_to_utf8( $string_array ) {
	$utf8 = '';

	foreach ( $string_array as $unicode ) {
		if ( $unicode < 128 ) {
			$utf8 .= chr( $unicode );
		}
		else {
			if ( $unicode < 2048 ) {
				$utf8 .= chr( 192 + ( ( $unicode - ( $unicode % 64 ) ) / 64 ) );
				$utf8 .= chr( 128 + ( $unicode % 64 ) );
			}
			else {
				$utf8 .= chr( 224 + ( ( $unicode - ( $unicode % 4096 ) ) / 4096 ) );
				$utf8 .= chr( 128 + ( ( ( $unicode % 4096 ) - ( $unicode % 64 ) ) / 64 ) );
				$utf8 .= chr( 128 + ( $unicode % 64 ) );
			}
		}
	}

	return $utf8;
}


/**
 * Run the upgrade procedures.
 *
 * @param string $db_version Version from database to check with.
 */
function wpseo_local_do_upgrade( $db_version ) {

	if ( version_compare( $db_version, '1.3.1', '<' ) ) {
		$options = get_option( 'wpseo_local' );

		if ( ! is_array( $options ) ) {
			return;
		}

		// Convert checkbox values from "1" to "on".
		foreach ( $options as $key => $value ) {
			if ( ! in_array( $key, array(
				'use_multiple_locations',
				'opening_hours_24h',
				'multiple_opening_hours',
			) )
			) {
				continue;
			}

			if ( $value == '1' ) {
				$options[ $key ] = 'on';
			}
		}
	}

	if ( version_compare( $db_version, '3.4', '<=' ) ) {
		// Update businesstypes from Attorneys to LegalServices if upgrading from version 3.4 or below.
		yoast_wpseo_local_update_business_type();
	}
}

/**
 * Retrieves excerpt from specific post.
 *
 * @param int $post_id The post ID of which the excerpt should be retrieved.
 *
 * @return string
 */
function wpseo_local_get_excerpt( $post_id ) {
	global $post;

	$original_post = $post;
	$post          = get_post( $post_id );
	setup_postdata( $post );

	$output = get_the_excerpt();

	// Set back original $post;.
	$post = $original_post;
	wp_reset_postdata();

	return $output;
}

/**
 * Create an upload field for an image
 */
function wpseo_local_upload_image() {

	$output = '';

	$output = '<p class="desc label" style="border:none; margin-bottom: 0;">' . __( 'If you want the map to display a custom marker pin for your locations, please upload it here.', 'yoast-local-seo' ) . '</p>';

	$output .= '<label for="upload_image">';
	$output .= '<input id="upload_image" type="text" size="36" name="ad_image" value="http://" /> ';
	$output .= '<input id="upload_image_button" class="button" type="button" value="Upload Image" />';
	$output .= '<br />Enter a URL or upload an image';
	$output .= '</label>';
	$output .= '<br class="clear"/>';

	return $output;
}

/**
 * @param string $value The value of the Business types array.
 * @param string $key   The key of the Business types array.
 */
function wpseo_local_sanitize_business_types( &$value, &$key ) {
	$value = str_replace( '&mdash;', '', $value );
	$value = trim( $value );
}

/**
 * @param array $atts Attributes array for the logo shortcode.
 *
 * @return string
 */
function wpseo_local_show_logo( $atts ) {
	$atts = wpseo_check_falses( shortcode_atts( array(
		'id' => get_the_ID(),
	), $atts ) );

	$output = '';

	if ( 'wpseo_locations' !== get_post_type( $atts['id'] ) ) {
		return '';
	}

	$location_logo = get_post_meta( $atts['id'], '_wpseo_business_location_logo', true );

	if ( '' === $location_logo ) {
		$wpseo_options = get_option( 'wpseo' );
		$location_logo = $wpseo_options['company_logo'];
	}

	if ( '' !== $location_logo ) {
		$output = '<img src="' . esc_url( $location_logo ) . '" alt="' . esc_attr( $location_logo_alt = get_post_meta( yoast_wpseo_local_get_attachment_id_from_src( $location_logo ), '_wp_attachment_image_alt', true ) ) . '">';
	}

	if ( ! empty( $output ) ) {
		return $output;
	}
}

/**
 * Return the ID of an image by src.
 *
 * @param string $src The image src.
 *
 * @return null|string
 */
function yoast_wpseo_local_get_attachment_id_from_src( $src ) {
	global $wpdb;
	$id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE guid = %s", array(
		$src,
	) ) );

	return $id;
}

/**
 * Update business type from Attorney to Legal Service
 */
function yoast_wpseo_local_update_business_type() {
	if ( wpseo_has_multiple_locations() ) {
		$locations_args = array(
			'post_type'  => 'wpseo_locations',
			'nopaging'   => true,
			'meta_query' => array(
				array(
					'key'     => '_wpseo_business_type',
					'value'   => 'Attorney',
					'compare' => '=',
				),
			),
		);

		$locations = new WP_Query( $locations_args );

		if ( $locations->have_posts() ) {
			while ( $locations->have_posts() ) {
				$locations->the_post();
				update_post_meta( get_the_ID(), '_wpseo_business_type', 'LegalService' );
			}
		}
	}
	else {
		$options = get_option( 'wpseo_local' );
		if ( isset( $options['business_type'] ) && 'Attorney' == $options['business_type'] ) {
			$options['business_type'] = 'LegalService';

			update_option( 'wpseo_local', $options );
		}
	}
}

/**
 * @return string
 */
function yoast_wpseo_local_get_api_key_browser() {
	$api_key_browser = '';
	if ( defined( 'WPSEO_LOCAL_API_KEY_BROWSER' ) ) {
		$api_key_browser = WPSEO_LOCAL_API_KEY_BROWSER;
	}
	else {
		$options = get_option( 'wpseo_local' );
		if ( isset( $options['api_key_browser'] ) ) {
			$api_key_browser = $options['api_key_browser'];
		}
	}

	return esc_attr( $api_key_browser );
}

/**
 * @return string
 */
function yoast_wpseo_local_get_api_key_server() {
	$api_key_server = '';
	if ( defined( 'WPSEO_LOCAL_API_KEY_SERVER' ) ) {
		$api_key_server = WPSEO_LOCAL_API_KEY_SERVER;
	}
	else {
		$options = get_option( 'wpseo_local' );
		if ( isset( $options['api_key'] ) ) {
			$api_key_server = $options['api_key'];
		}
	}

	return esc_attr( $api_key_server );
}

/**
 * Wrapper function to check whether a location is currently open or closed.
 *
 * @param null $post A post ID.
 *
 * @return bool|WP_Error
 *
 * @since 4.2
 */
function yoast_seo_local_is_location_open( $post = null ) {
	$timezone_repository = new WPSEO_Local_Timezone_Repository();

	return $timezone_repository->is_location_open( $post );
}

/**
 * Flattens a version number for use in a filename
 *
 * @param string $version The original version number.
 * @return string The flattened version number.
 */
function yoast_seo_local_flatten_version( $version ) {
	$parts = explode( '.', $version );
	if ( count( $parts ) === 2 ) {
		$parts[] = '0';
	}
	return implode( '', $parts );
}

