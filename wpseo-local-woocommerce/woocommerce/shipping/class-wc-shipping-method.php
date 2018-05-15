<?php

if ( ! class_exists( 'WC_Shipping_Method' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Yoast_WCSEO_Local_Shipping_Method extends WC_Shipping_Flat_Rate {
	/** @var array */
	private $available_locations = array();

	/** @var array */
	private $saved_locations = array();

	/** @var array */
	private $location_categories = array();

	public function __construct() {
		$this->id                 = 'yoast_wcseo_local_pickup';
		$this->title              = __( 'Local store pickup', 'yoast-local-seo-woocommerce' );
		$this->method_title       = __( 'Local Store Pickup', 'yoast-local-seo-woocommerce' );
		$this->method_description = __( 'This shipping method enables customers to pick up their order in local stores defined in the Local SEO plugin.',
			'yoast-local-seo-woocommerce' );

		$this->enabled = $this->get_option( 'enabled' );

		$this->init();
	}

	public function resolve_defaults( $location ) {

		$defaults = array(
			'status' => '',
			'price'   => ''
		);

		// get the category-terms for this location
		$terms = wp_get_object_terms( $location->ID, array( 'wpseo_locations_category' ), array( 'fields' => 'ids' ) );

		// if we have found any
		if ( is_array( $terms ) && ( ! empty( $terms ) ) ) {

			//there can be only one...
			$connor_mccloud = array_shift( $terms );

			//lookup...
			foreach ( $this->location_categories as $category ) {

				if ( $category->term_id == $connor_mccloud ) {

					if ( $category->allowed === true ) {
						$defaults['status'] = esc_attr( __( 'Default: Allow', 'yoast-local-seo-woocommerce' ) );
					} else {
						$defaults['status'] = esc_attr( __( 'Default: Disallow', 'yoast-local-seo-woocommerce' ) );
					}

					$defaults['price'] = esc_attr( sprintf( __( 'Default: %d', 'yoast-local-seo-woocommerce' ), $category->price ) );

				}
			}
		}

		return $defaults;

	}

	public function get_category_specific_settings() {
		return $this->get_settings_subset( 'category_specific' );
	}

	public function save_category_specific_settings( $settings ) {
		$this->save_settings_subset( 'category_specific', $settings );
	}

	public function get_location_specific_settings() {
		return $this->get_settings_subset( 'location_specific' );
	}

	public function save_location_specific_settings( $settings ) {
		$this->save_settings_subset( 'location_specific', $settings );
	}

	public function get_settings_subset( $key ) {
		$settings = get_option( $this->plugin_id . $this->id . '_settings' );

		return ( isset( $settings[ $key ] ) ) ? $settings[ $key ] : array();
	}

	public function save_settings_subset( $key, $settings_subset ) {
		$settings         = get_option( $this->plugin_id . $this->id . '_settings' );
		$settings[ $key ] = $settings_subset;
		update_option( $this->plugin_id . $this->id . '_settings', $settings );
	}

	public function get_posted_ids_for_keys( $keys = array() ) {
		$ids = array();

		foreach ( $keys as $key ) {
			if ( isset( $_POST[ $key ] ) ) {
				$ids = array_merge( $ids, array_map( 'intval', array_keys( $_POST[ $key ] ) ) );
			}
		}

		return $ids;
	}

	public function save_category_options() {

		$category_specific_settings = array();
		$posted_ids                 = $this->get_posted_ids_for_keys( array(
			'yoast_wcseo_local_pickup_cat_allowed',
			'yoast_wcseo_local_pickup_cat_cost',
		) );

		foreach ( $posted_ids as $posted_id ) {
			if ( isset( $_POST['yoast_wcseo_local_pickup_cat_allowed'][ $posted_id ] ) && $_POST['yoast_wcseo_local_pickup_cat_allowed'][ $posted_id ] == 'on' ) {
				$category_specific_settings[ $posted_id ]['allowed'] = 'yes';
			}

			if ( isset( $_POST['yoast_wcseo_local_pickup_cat_cost'][ $posted_id ] ) ) {
				//$category_specific_settings[ $posted_id ]['price'] = sanitize_text_field( stripslashes( $_POST['yoast_wcseo_local_pickup_cat_cost'][ $posted_id ] ) );
				//$category_specific_settings[ $posted_id ]['price'] = (float) sanitize_text_field( $_POST['yoast_wcseo_local_pickup_cat_cost'][ $posted_id ] );
				$category_specific_settings[ $posted_id ]['price'] = $this->sanitize_costs_field( $_POST['yoast_wcseo_local_pickup_cat_cost'][ $posted_id ] );
			}
		}

		$this->save_category_specific_settings( $category_specific_settings );
	}

	public function save_location_options() {

		$location_specific_settings = array();
		$posted_ids                 = $this->get_posted_ids_for_keys( array(
			'yoast_wcseo_local_pickup_location_allowed',
			'yoast_wcseo_local_pickup_location_cost',
		) );

		foreach ( $posted_ids as $posted_id ) {
			if ( isset( $_POST['yoast_wcseo_local_pickup_location_allowed'][ $posted_id ] ) && $_POST['yoast_wcseo_local_pickup_location_allowed'][ $posted_id ] == 'on' ) {
				$location_specific_settings[ $posted_id ]['allowed'] = 'yes';
			}

			if ( isset( $_POST['yoast_wcseo_local_pickup_location_cost'][ $posted_id ] ) ) {
				//$location_specific_settings[ $posted_id ]['price'] = sanitize_text_field( stripslashes( $_POST['yoast_wcseo_local_pickup_location_cost'][ $posted_id ] ) );
				//$location_specific_settings[ $posted_id ]['price'] = (float) sanitize_text_field( $_POST['yoast_wcseo_local_pickup_location_cost'][ $posted_id ] );
				$location_specific_settings[ $posted_id ]['price'] = $this->sanitize_costs_field( $_POST['yoast_wcseo_local_pickup_location_cost'][ $posted_id ] );
			}
		}

		$this->save_location_specific_settings( $location_specific_settings );
	}

	/**
	 * This method sanitizes the entered shipping costs.
	 *
	 * @param $raw_post_value string Raw value, entered in the costs input field.
	 *
	 * @return string Sanitized value.
	 */
	public function sanitize_costs_field( $raw_post_value ) {

		//first perform some basic sanitazion
		$sanitized_value = stripslashes( $raw_post_value );
		$sanitized_value = wp_specialchars_decode( $sanitized_value );
		$sanitized_value = sanitize_text_field( $sanitized_value );

		return $sanitized_value;
	}

	public function init() {
		$this->init_form_fields();
		$this->init_settings();

		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ), 0 );
		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'save_category_options' ), 0 );
		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'save_location_options' ), 0 );

	}

	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'        => array(
				'title'   => __( 'Enable/Disable', 'yoast-local-seo-woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable this shipping method', 'yoast-local-seo-woocommerce' ),
				'default' => 'no',
			),
			'checkout_text'  => array(
				'title' => __( 'Checkout text', 'yoast-local-seo-woocommerce' ),
				'type'  => 'textarea',
				'label' => __( 'The text that appears during the checkout process for this shipping method', 'yoast-local-seo-woocommerce' ),
			),
			'checkout_mode' => array(
				'title'       => __( 'Checkout mode', 'yoast-local-seo-woocommerce' ),
				'label'       => __( 'Choose between these checkout modes', 'yoast-local-seo-woocommerce' ),
				'type'        => 'select',
				'default'     => 'radio',
				'options'     => array(
					'radio' => __( 'Radio', 'yoast-local-seo-woocommerce' ),
					'select'   => __( 'Dropdown (basic)', 'yoast-local-seo-woocommerce' ),
					'select2'   => __( 'Dropdown (advanced)', 'yoast-local-seo-woocommerce' )
				)
			),
			'category_costs' => array(
				'type' => 'category_costs_table',
			),
			'location_costs' => array(
				'type' => 'location_costs_table',
			),
		);

		if ( ! wpseo_has_multiple_locations() ) {
			$cost_desc = __( 'Enter a cost (excl. tax) or sum, e.g. <code>10.00 * [qty]</code>.', 'yoast-local-seo-woocommerce' ) . '<br/><br/>' . __( 'Use <code>[qty]</code> for the number of items, <br/><code>[cost]</code> for the total cost of items, and <code>[fee percent="10" min_fee="20" max_fee=""]</code> for percentage based fees.', 'woocommerce', 'yoast-local-seo-woocommerce' );

			$this->form_fields['costs'] = array(
				'title'       => __( 'Local pickup costs', 'yoast-local-seo-woocommerce' ),
				'desc_tip'    => esc_attr( $cost_desc ),
				'type'        => 'currency',
				'label'       => __( 'The costs for the local pickup for customers', 'yoast-local-seo-woocommerce' ),
				'placeholder' => __( 'Enter a price (excl. tax), like: 42.12', 'yoast-local-seo-woocommerce' ),
			);
		}

	}

	public function calculate_shipping( $package = array() ) {

		if ( ! $this->is_enabled() ) {
			return;
		}

		if ( ! wpseo_has_multiple_locations() ) {
			$price   = isset( $this->settings['costs'] ) ? $this->settings['costs'] : 0;
			$options = get_option( 'wpseo_local' );

			// evaluate the price, it may contain shortcodes
			$price = $this->evaluate_cost( $price, array(
				'qty'  => $this->get_package_item_qty( $package ),
				'cost' => $package['contents_cost'],
			) );

			$this->add_rate( array(
				'id'      => $this->id . '_single',
				'label'   => $options['location_name'],
				'cost'    => $price,
				'package' => $package,
			) );

			return;
		}

		//first we harvest all the single locations
		$single_locations           = array();
		$allowed_location_ids       = array();
		$disallowed_location_ids    = array();
		$location_specific_settings = $this->get_location_specific_settings();
		if ( is_array( $location_specific_settings ) && ( ! empty( $location_specific_settings ) ) ) {

			//get the specs for all entered single locations
			foreach ( $location_specific_settings as $id => $location_setting ) {
				if ( isset( $location_setting['allowed'] ) && ( $location_setting['allowed'] == 'yes' ) ) {
					$allowed_location_ids[ $id ] = $location_setting['price'];
				}
				else {
					array_push( $disallowed_location_ids, $id );
				}
			}

			//if we have single locations that are allowed,...go get them
			if ( is_array( $allowed_location_ids ) && ( ! empty( $allowed_location_ids ) ) ) {

				$params = array(
					'post_type'      => 'wpseo_locations',
					'posts_per_page' => -1,
					'post__in'       => array_keys( $allowed_location_ids ),
					'no_found_rows'  => true,
				);

				$single_locations = get_posts( $params );
			}
		}

		$allowed_category_ids    = array();
		$disallowed_category_ids = array();
		foreach ( $this->get_category_specific_settings() as $id => $category_setting ) {
			if ( isset( $category_setting['allowed'] ) && ( $category_setting['allowed'] == 'yes' ) ) {
				$allowed_category_ids[ $id ] = $category_setting['price'];
			}
			else {
				array_push( $disallowed_category_ids, $id );
			}
		}

		//secondly we get the locations by category, ignoring the single ID's that are specifically not allowed
		$params = array(
			'post_type'      => 'wpseo_locations',
			'posts_per_page' => -1,
			'post__not_in'   => array_values( $disallowed_location_ids ),
			'no_found_rows'  => true,
			'tax_query'      => array(
				'relation' => 'AND',
				array(
					'taxonomy' => 'wpseo_locations_category',
					'field'    => 'term_id',
					'terms'    => array_keys( $allowed_category_ids ),
				),
				array(
					'taxonomy' => 'wpseo_locations_category',
					'field'    => 'term_id',
					'terms'    => array_values( $disallowed_category_ids ),
					'operator' => 'NOT IN',
				),
			),
		);

		if ( empty( $allowed_category_ids ) && empty( $disallowed_category_ids ) ) {
			unset( $params['tax_query'] );
		}

		$category_locations = get_posts( $params );

		//merge all harvested locations
		$locations = array_merge( $category_locations, $single_locations );

		foreach ( $locations as $location ) {

			unset( $price );

			if ( isset( $allowed_location_ids[ $location->ID ] ) && null != trim( $allowed_location_ids[ $location->ID ] ) ) {

				$price = $allowed_location_ids[ $location->ID ];

			}
			else {

				$location_categories = get_the_terms( $location->ID, 'wpseo_locations_category' );

				if ( is_array( $location_categories ) && ( ! empty( $location_categories ) ) ) {
					foreach ( $location_categories as $cat ) {
						if ( in_array( $cat->term_id, array_keys( $allowed_category_ids ) ) ) {
							if ( ! empty( $allowed_category_ids[ $cat->term_id ] ) ) {
								$price = $allowed_category_ids[ $cat->term_id ];
							}
						}
					}
				}
			}

			if ( ! isset( $price ) ) {
				continue;
			}

			// evaluate the price, it may contain shortcodes
			$price = $this->evaluate_cost( $price, array(
				'qty'  => $this->get_package_item_qty( $package ),
				'cost' => $package['contents_cost'],
			) );

			$this->add_rate( array(
				'id'      => $this->id . '_' . $location->ID,
				'label'   => $location->post_title,
				'cost'    => $price,
				'package' => $package,
			) );
		}
	}

	public function generate_category_costs_table_html() {

		$this->location_categories  = $this->get_location_categories();
		$category_specific_settings = $this->get_category_specific_settings();

		if ( ! wpseo_has_multiple_locations() ) {
			return '';
		}

		if ( empty( $this->location_categories ) || is_wp_error( $this->location_categories ) ) {
			$url = admin_url( 'edit-tags.php?taxonomy=wpseo_locations_category&post_type=wpseo_locations' );

			return '<p>' . sprintf( __( 'You have not yet added any location categories, or you don\'t have assigned locations yet to these categories. After <a href="%s">adding location categories</a>, you can set category specific shipping settings here.',
				'yoast-local-seo-woocommerce' ), $url ) . '</p>';
		}

		foreach ( $this->location_categories as $key => $value ) {
			if ( isset( $category_specific_settings[ $value->term_id ] ) ) {
				if ( isset( $category_specific_settings[ $value->term_id ]['allowed'] ) ) {
					$this->location_categories[ $key ]->allowed = ( $category_specific_settings[ $value->term_id ]['allowed'] == 'yes' );
				}

				if ( isset( $category_specific_settings[ $value->term_id ]['price'] ) ) {
					$this->location_categories[ $key ]->price = $category_specific_settings[ $value->term_id ]['price'];
				}
			}
		}


		ob_start();
		include( 'includes/category-costs-table.php' );

		return ob_get_clean();
	}

	public function get_location_categories() {
		return get_terms(
			array( 'wpseo_locations_category' )
		);
	}

	public function generate_location_costs_table_html() {
		if ( ! wpseo_has_multiple_locations() ) {
			$url = admin_url( 'admin.php?page=wpseo_local' );

			return '<p>' . sprintf( __( 'You manage only a single location. In the <a href="%s">Local SEO settings</a>, you can specify if you want to manage multiple locations.',
				'yoast-local-seo-woocommerce' ), $url ) . '</p>';
		}

		$this->available_locations = $this->get_available_locations();
		$this->saved_locations     = $this->get_saved_locations();

		if ( ( empty( $this->available_locations ) && empty( $this->saved_locations ) ) || is_wp_error( $this->available_locations ) ) {
			$url = admin_url( 'edit.php?post_type=wpseo_locations' );

			return '<p>' . sprintf( __( 'You have not yet added any locations. After <a href="%s">adding locations</a>, you can set location specific shipping settings here.',
				'yoast-local-seo-woocommerce' ), $url ) . '</p>';
		}

		$location_specific_settings = $this->get_location_specific_settings();

		foreach ( $this->saved_locations as $key => $value ) {
			if ( isset( $location_specific_settings[ $value->ID ] ) ) {
				if ( isset( $location_specific_settings[ $value->ID ]['allowed'] ) ) {
					$this->saved_locations[ $key ]->allowed = ( $location_specific_settings[ $value->ID ]['allowed'] == 'yes' );
				}

				if ( isset( $location_specific_settings[ $value->ID ]['price'] ) ) {
					$this->saved_locations[ $key ]->price = $location_specific_settings[ $value->ID ]['price'];
				}
			}
		}

		ob_start();
		include( 'includes/location-costs-table.php' );

		return ob_get_clean();
	}

	public function get_available_locations() {
		$saved_location_ids = array_keys( $this->get_location_specific_settings() );

		return get_posts( array(
			'post__not_in'   => $saved_location_ids,
			'post_type'      => 'wpseo_locations',
			'posts_per_page' => -1,
		) );
	}

	public function get_saved_locations() {
		$saved_location_ids = array_keys( $this->get_location_specific_settings() );

		if ( empty( $saved_location_ids ) ) {
			return array();
		}

		return get_posts( array(
			'post__in'       => $saved_location_ids,
			'post_type'      => 'wpseo_locations',
			'posts_per_page' => -1,
		) );
	}
}
