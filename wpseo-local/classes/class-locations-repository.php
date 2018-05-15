<?php
/**
 * @package WPSEO_Local\Main
 */

if ( ! class_exists( 'WPSEO_Local_Locations_Repository' ) ) {

	/**
	 * Class WPSEO_Local_Locations_Repository
	 *
	 * This class handles the querying of all locations
	 */
	class WPSEO_Local_Locations_Repository {
		/**
		 * @var WP_Query $query
		 *
		 * Stores the last executed query
		 */
		public $query;

		/**
		 * @var array $map
		 *
		 * This array determines where the implementing code needs to fetch
		 * the meta values from. Should the repository query a location from
		 * the posts table, the `postmeta` value of this array is used to get a
		 * value from the post_meta table. When querying from the options table
		 * for a single location, the `option` value is used to get a value
		 * from the options table with that key.
		 */
		protected $map = array(
			'business_type'        => array(
				'postmeta' => '_wpseo_business_type',
				'option'   => 'business_type',
			),
			'business_address'     => array(
				'postmeta' => '_wpseo_business_address',
				'option'   => 'location_address',
			),
			'business_address_2'   => array(
				'postmeta' => '_wpseo_business_address_2',
				'option'   => 'location_address_2',
			),
			'business_city'        => array(
				'postmeta' => '_wpseo_business_city',
				'option'   => 'location_city',
			),
			'business_state'       => array(
				'postmeta' => '_wpseo_business_state',
				'option'   => 'location_state',
			),
			'business_zipcode'     => array(
				'postmeta' => '_wpseo_business_zipcode',
				'option'   => 'location_zipcode',
			),
			'business_country'     => array(
				'postmeta' => '_wpseo_business_country',
				'option'   => 'location_country',
			),
			'business_phone'       => array(
				'postmeta' => '_wpseo_business_phone',
				'option'   => 'location_phone',
			),
			'business_phone_2nd'   => array(
				'postmeta' => '_wpseo_business_phone_2nd',
				'option'   => 'location_phone_2nd',
			),
			'business_fax'         => array(
				'postmeta' => '_wpseo_business_fax',
				'option'   => 'location_fax',
			),
			'business_email'       => array(
				'postmeta' => '_wpseo_business_email',
				'option'   => 'location_email',
			),
			'business_price_range' => array(
				'postmeta' => '_wpseo_business_price_range',
				'option'   => 'location_price_range',
			),
			'business_coc'         => array(
				'postmeta' => '_wpseo_business_coc_id',
				'option'   => 'location_coc_id',
			),
			'business_tax'         => array(
				'postmeta' => '_wpseo_business_tax_id',
				'option'   => 'location_tax_id',
			),
			'business_vat'         => array(
				'postmeta' => '_wpseo_business_vat_id',
				'option'   => 'location_vat_id',
			),
		);

		/**
		 * @var array $custom_map
		 *
		 * The following callback methods are defined on this class. The methods
		 * are called based on what meta key is required by the array of meta keys
		 * passed to this repository when querying locations. If the key isn't passed
		 * to that array, the callback should not be called.
		 */
		protected $custom_map = array(
			'business_name'        => array(
				'postmeta_cb' => 'cb_postmeta_name',
				'options_cb'  => 'cb_options_name',
			),
			'business_url'         => array(
				'postmeta_cb' => 'cb_postmeta_url',
				'options_cb'  => 'cb_options_url',
			),
			'business_description' => array(
				'postmeta_cb' => 'cb_postmeta_description',
				'options_cb'  => 'cb_options_description',
			),
			'coords'               => array(
				'postmeta_cb' => 'cb_postmeta_coords',
				'options_cb'  => 'cb_options_coords',
			),
			'post_id'              => array(
				'postmeta_cb' => 'cb_postmeta_id',
				'options_cb'  => 'cb_options_id',
			),
			'is_postal_address'    => array(
				'postmeta_cb' => 'cb_postmeta_postal',
				'options_cb'  => 'cb_options_postal',
			),
			'business_type'        => array(
				'postmeta_cb' => 'cb_postmeta_type',
				'options_cb'  => 'cb_options_type',
			),
			'custom_marker'        => array(
				'postmeta_cb' => 'cb_postmeta_custom_marker',
				'options_cb'  => 'cb_options_custom_marker',
			),
			'business_logo'        => array(
				'postmeta_cb' => 'cb_postmeta_logo',
				'options_cb'  => 'cb_options_logo',
			),
			'business_image'       => array(
				'postmeta_cb' => 'cb_postmeta_image',
				'options_cb'  => 'cb_options_image',
			),
		);

		/**
		 * Prepares arguments for retrieving data from multiple locations.
		 *
		 * @param array $arguments Array with  (optional) arguments.
		 *
		 * @return array Array with arguments, compared and parse with the defaults.
		 */
		private function prepare_arguments( $arguments ) {
			return wp_parse_args( $arguments, array(
				'number'      => -1,
				'orderby'     => 'title',
				'order'       => 'ASC',
				'fields'      => 'ids',
				'category_id' => 0,
				'id'          => array(),
			) );
		}

		/**
		 * @param array $meta_fields Meta field keys provided to query.
		 *
		 * @return array
		 * @todo Specify the default fields of the meta to be returned
		 */
		private function prepare_meta_fields( $meta_fields ) {
			$allowed_fields = array_merge( array_keys( $this->map ), array_keys( $this->custom_map ) );

			if ( ! empty( $meta_fields ) ) {
				return array_intersect_key( $allowed_fields, $meta_fields );
			}

			// No meta fields specified, so we return the defaults (full set of fields, for now).
			return $allowed_fields;
		}

		/**
		 * Get the location details, automatically populated with meta fields
		 * Can be loaded from post meta or options table, based on multiple location setting
		 *
		 * @param array $arguments   Arguments to filter the query.
		 * @param bool  $load_meta   Automatically load all meta fields after querying.
		 * @param array $meta_fields Specify what meta fields need to be loaded.
		 *
		 * @return array containing the queried locations
		 */
		public function get( $arguments = array(), $load_meta = true, $meta_fields = array() ) {
			$locations   = array();
			$meta_fields = $this->prepare_meta_fields( $meta_fields );

			if ( ! empty( $arguments['id'] ) && ! is_array( $arguments['id'] ) ) {
				$arguments['id'] = (array) $arguments['id'];
			}

			if ( wpseo_has_multiple_locations() ) {
				$this->query = $this->get_filter_locations( $arguments );
				$posts       = $this->query->posts;

				if ( ! $load_meta ) {
					return $posts;
				}

				foreach ( $posts as $post ) {
					$locations[ $post ]          = $this->load_meta_from_meta( $post, $meta_fields );
					$locations[ $post ]['terms'] = $this->load_terms( $post );
				}

				wp_reset_postdata();
			}
			else {
				$locations[] = $this->load_meta_from_options( $meta_fields );
			}

			return $locations;
		}

		/**
		 * Load meta fields from post meta fields
		 *
		 * @param int   $location_id Id of specific location.
		 * @param array $meta_fields Specify what meta fields need to be loaded.
		 *
		 * @return array
		 */
		public function load_meta_from_meta( $location_id, $meta_fields = array() ) {
			$data = array();

			foreach ( $this->map as $key => $value ) {
				if ( in_array( $key, $meta_fields ) ) {
					$data[ $key ] = get_post_meta( $location_id, $value['postmeta'], true );
				}
			}

			foreach ( $this->custom_map as $key => $value ) {
				if ( in_array( $key, $meta_fields ) ) {
					$data[ $key ] = call_user_func( array( $this, $value['postmeta_cb'] ), $location_id );
				}
			}

			return $data;
		}

		/**
		 * Load meta fields from options table
		 *
		 * @param array $meta_fields Specify what meta fields need to be loaded.
		 *
		 * @return array
		 */
		public function load_meta_from_options( $meta_fields = array() ) {
			$options = get_option( 'wpseo_local' );
			$data    = array();

			foreach ( $this->map as $key => $value ) {
				if ( in_array( $key, $meta_fields ) ) {
					$data[ $key ] = isset( $options[ $value['option'] ] ) ? $options[ $value['option'] ] : '';
				}
			}

			foreach ( $this->custom_map as $key => $value ) {
				if ( in_array( $key, $meta_fields ) ) {
					$data[ $key ] = call_user_func( array( $this, $value['options_cb'] ), $options );
				}
			}

			return $data;
		}

		/**
		 * Load wpseo_locations_category terms.
		 *
		 * @param string | int $location_id Id of the location to get the location categories from.
		 *
		 * @return array The wpseo_locations_category terms.
		 */
		public function load_terms( $location_id ) {
			$data = array();

			// Put all categories in an array, to be passed on to the map later on and for the categories filter.
			$terms = get_the_terms( $location_id, 'wpseo_locations_category' );
			if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				foreach ( $terms as $term ) {
					$data[] = $term;
				}
			}

			return $data;
		}

		/**
		 * This method retrieves location based on given parameters. Possible parameters (with their defaults):
		 * number: -1 (amount of locations)
		 * orderby: title
		 * order: ASC
		 * fields: ids
		 * category_id: 0 (term_id of the wpseo-location-category taxonomy)
		 * location_ids: array() (array of location Ids to retrieve)
		 *
		 * @param array $arguments Arguments for getting filtered locations.
		 *
		 * @return WP_Query The locations.
		 */
		public function get_filter_locations( $arguments = array() ) {
			$arguments = $this->prepare_arguments( $arguments );

			$location_args = array(
				'post_type'      => 'wpseo_locations',
				'posts_per_page' => ( empty( $arguments['id'] ) || 'all' == $arguments['id'][0] ) ? $arguments['number'] : count( $arguments['id'] ),
				'orderby'        => $arguments['orderby'],
				'order'          => $arguments['order'],
				'fields'         => $arguments['fields'],
			);

			if ( current_user_can( 'edit_posts' ) ) {
				$location_args['post_status'] = array(
					'publish',
					'draft',
				);
			}

			$tax_query = array();

			if ( '' != $arguments['category_id'] ) {
				$tax_query[] = array(
					'taxonomy' => 'wpseo_locations_category',
					'field'    => 'term_id',
					'terms'    => $arguments['category_id'],
				);

				$location_args['tax_query'] = $tax_query;
			}

			// Check the possible array of ID's that has to be passed.
			if ( ! empty( $arguments['id'] ) ) {
				// Force arguments to be an array.
				$arguments['id'] = ( is_array( $arguments['id'] ) ) ? $arguments['id'] : (array) $arguments['id'];

				if ( 1 == count( $arguments['id'] ) ) {
					if ( is_numeric( $arguments['id'][0] ) ) {
						$location_args['p'] = (int) $arguments['id'][0];
					}
					else {
						if ( 'current' == $arguments['id'][0] && is_singular( 'wpseo_locations' ) ) {
							$location_args['p'] = get_queried_object_id();
						}
					}
				}
				else {
					$location_args['post__in'] = $arguments['id'];
				}
			}

			return new WP_Query( $location_args );
		}

		/**
		 * Queries all locations that have 'Attorney' as business type
		 * for the 3.4 or below update routine
		 *
		 * @return WP_Query
		 */
		public function get_attorney_locations() {
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

			return new WP_Query( $locations_args );
		}

		/**
		 * @param int $location_id Id of the location to get the title from.
		 *
		 * @return string
		 */
		public function cb_postmeta_name( $location_id ) {
			return get_the_title( $location_id );
		}

		/**
		 * @param int $location_id Id of the location to get the description from.
		 *
		 * @return string
		 */
		public function cb_postmeta_description( $location_id ) {
			return wpseo_local_get_excerpt( $location_id );
		}

		/**
		 * @param int $location_id Id of the location to get the url from.
		 *
		 * @return false|mixed|string
		 */
		public function cb_postmeta_url( $location_id ) {
			$url = get_post_meta( $location_id, '_wpseo_business_url', true );

			$post_type_object = get_post_type_object( 'wpseo_locations' );
			if ( empty( $url ) && $post_type_object->public ) {
				$url = get_permalink( $location_id );
			}

			return $url;
		}

		/**
		 * @param int $location_id Id of the location to get the coords from.
		 *
		 * @return array
		 */
		public function cb_postmeta_coords( $location_id ) {
			return array(
				'lat'  => str_replace( ',', '.', get_post_meta( $location_id, '_wpseo_coordinates_lat', true ) ),
				'long' => str_replace( ',', '.', get_post_meta( $location_id, '_wpseo_coordinates_long', true ) ),
			);
		}

		/**
		 * @param int $location_id Id of the location to get the id from.
		 *
		 * @return mixed
		 */
		public function cb_postmeta_id( $location_id ) {
			return $location_id;
		}

		/**
		 * @param int $location_id Id of the location to get the postal address flag from.
		 *
		 * @return bool
		 */
		public function cb_postmeta_postal( $location_id ) {
			$is_postal_address = get_post_meta( $location_id, '_wpseo_is_postal_address', true );

			return $is_postal_address == '1';
		}

		/**
		 * @param int $location_id Id of the location to get the type from.
		 *
		 * @return mixed
		 */
		public function cb_postmeta_type( $location_id ) {
			return get_post_meta( $location_id, '_wpseo_business_type', true );
		}

		/**
		 * @param int $location_id Id of the location to get the custom marker value from.
		 *
		 * @return false|mixed|string
		 */
		public function cb_postmeta_custom_marker( $location_id ) {
			$custom_marker = get_post_meta( $location_id, '_wpseo_business_location_custom_marker', true );

			// If no custom marker for a location is set, check if there are custom markers for terms.
			if ( empty( $custom_marker ) ) {
				$terms = get_the_terms( $location_id, 'wpseo_locations_category' );

				if ( ! empty( $terms ) && false !== $terms && ! is_wp_error( $terms ) ) {
					$terms = wp_list_pluck( $terms, 'term_id' );

					$terms = apply_filters( 'wpseo_local_custom_marker_order', $terms );

					if ( class_exists( 'WPSEO_Primary_Term' ) ) {
						// Check if there's a primary term.
						$primary_term = new WPSEO_Primary_Term( 'wpseo_locations_category', $location_id );
						if ( method_exists( $primary_term, 'get_primary_term ' ) ) {
							$primary_term = $primary_term->get_primary_term();

							if ( ! empty( $primary_term ) ) {
								// If there is a primary term, replace the term array with the primary term.
								$terms = array( $primary_term );
							}
						}

						if ( method_exists( 'WPSEO_Taxonomy_Meta', 'get_term_meta' ) ) {
							$tax_meta = WPSEO_Taxonomy_Meta::get_term_meta( (int) $terms[0], 'wpseo_locations_category' );
						}
					}

					if ( isset( $tax_meta['wpseo_local_custom_marker'] ) && ! empty( $tax_meta['wpseo_local_custom_marker'] ) ) {
						$custom_marker = wp_get_attachment_url( $tax_meta['wpseo_local_custom_marker'] );
					}
				}
			}

			// If no custom markers are set for a location or terms, fall back to custom marker set in WPSEO Local options.
			if ( empty( $custom_marker ) ) {
				$options       = get_option( 'wpseo_local' );
				$custom_marker = $this->cb_options_custom_marker( $options );
			}

			return $custom_marker;
		}

		/**
		 * @param int $location_id Id of the location to get the custom marker value from.
		 *
		 * @return int|false
		 */
		public function cb_postmeta_logo( $location_id ) {
			$logo = get_post_meta( $location_id, '_wpseo_business_location_logo', true );

			if ( empty( $logo ) ) {
				$options = get_option( 'wpseo' );
				$logo    = $this->cb_options_logo( $options );
			}

			$logo_id = yoast_wpseo_local_get_attachment_id_from_src( $logo );

			return $logo_id;
		}

		/**
		 * @param int $location_id Id of the location to get the custom marker value from.
		 *
		 * @return int|false
		 */
		public function cb_postmeta_image( $location_id ) {
			$business_image = get_post_thumbnail_id( $location_id );

			if ( empty( $business_image ) ) {
				$options        = get_option( 'wpseo_local' );
				$business_image = $this->cb_options_image( $options );
			}

			return $business_image;
		}

		/**
		 * @param array $options WPSEO Local options array.
		 *
		 * @return string
		 */
		public function cb_options_name( $options ) {
			return isset( $options['location_name'] ) ? $options['location_name'] : '';
		}

		/**
		 * @param array $options WPSEO Local options array.
		 *
		 * @return string
		 */
		public function cb_options_description( $options ) {
			return get_option( 'blogname' ) . ' - ' . get_option( 'blogdescription' );
		}

		/**
		 * @param array $options WPSEO Local options array.
		 *
		 * @return string
		 */
		public function cb_options_url( $options ) {
			$url = isset( $options['location_url'] ) ? esc_url( $options['location_url'] ) : null;
			if ( null === $url ) {
				$url = ( true === class_exists( 'WPSEO_Utils' ) ? WPSEO_Utils::home_url() : trailingslashit( get_home_url() ) );
			}

			return $url;
		}

		/**
		 * @param array $options WPSEO Local options array.
		 *
		 * @return array
		 */
		public function cb_options_coords( $options ) {
			return array(
				'lat'  => $options['location_coords_lat'],
				'long' => $options['location_coords_long'],
			);
		}

		/**
		 * @param array $options WPSEO Local options array.
		 *
		 * @return string
		 */
		public function cb_options_id( $options ) {
			return '';
		}

		/**
		 * @param array $options WPSEO Local options array.
		 *
		 * @return string
		 */
		public function cb_options_postal( $options ) {
			return '';
		}

		/**
		 * @param array $options WPSEO Local options array.
		 *
		 * @return string
		 */
		public function cb_options_type( $options ) {
			if ( isset( $options['business_type'] ) ) {
				return $options['business_type'];
			}

			return '';
		}

		/**
		 * @param array $options WPSEO Local options array.
		 *
		 * @return false|string
		 */
		public function cb_options_custom_marker( $options ) {
			if ( isset( $options['custom_marker'] ) && intval( $options['custom_marker'] ) ) {
				return wp_get_attachment_url( $options['custom_marker'] );
			}

			return '';
		}

		/**
		 * @return false|string Return ID of the company logo.
		 */
		public function cb_options_logo() {
			$options = get_option( 'wpseo' );

			if ( isset( $options['company_logo'] ) && filter_var( $options['company_logo'], FILTER_VALIDATE_URL ) ) {
				$logo_id = yoast_wpseo_local_get_attachment_id_from_src( $options['company_logo'] );

				return $logo_id;
			}

			return '';
		}

		/**
		 * @param array $options WPSEO Local options array.
		 *
		 * @return false|string Return ID of the company logo.
		 */
		public function cb_options_image( $options ) {
			if ( isset( $options['business_image'] ) && intval( $options['business_image'] ) ) {
				return $options['business_image'];
			}

			return '';
		}
	}
}
