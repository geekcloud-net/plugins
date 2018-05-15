<?php
/**
 * @package WPSEO_Local\Main
 * @since   1.0
 */

if ( ! class_exists( 'WPSEO_Local_Core' ) ) {

	/**
	 * WPSEO_Local_Core class. Handles all basic needs for the plugin, like custom post_type/taxonomy.
	 */
	class WPSEO_Local_Core {

		/**
		 * @var array $options Stores the options for this plugin.
		 */
		var $options = array();

		/**
		 * @var array $days Contains the days, used for opening hours
		 */
		var $days = array();

		/**
		 * @var Yoast_Plugin_License_Manager Holds an instance of the license manager class
		 */
		protected $license_manager = null;

		/**
		 * @var array Holds the default option values for Yoast Local SEO.
		 */
		public static $defaults;

		/**
		 * Constructor for the WPSEO_Local_Core class.
		 *
		 * @since 1.0
		 */
		public function __construct() {

			$this->options = get_option( 'wpseo_local' );
			$this->days    = array(
				'monday'    => __( 'Monday', 'yoast-local-seo' ),
				'tuesday'   => __( 'Tuesday', 'yoast-local-seo' ),
				'wednesday' => __( 'Wednesday', 'yoast-local-seo' ),
				'thursday'  => __( 'Thursday', 'yoast-local-seo' ),
				'friday'    => __( 'Friday', 'yoast-local-seo' ),
				'saturday'  => __( 'Saturday', 'yoast-local-seo' ),
				'sunday'    => __( 'Sunday', 'yoast-local-seo' ),
			);

			if ( wpseo_has_multiple_locations() ) {
				$this->create_custom_post_type();
				$this->create_taxonomies();
				$this->exclude_taxonomy();
				add_filter( 'wpseo_primary_term_taxonomies', array(
					$this,
					'filter_wpseo_primary_term_taxonomies',
				), 10, 3 );
			}

			if ( is_admin() ) {

				$this->license_manager = $this->get_license_manager();

				add_action( 'wpseo_licenses_forms', array( $this->license_manager, 'show_license_form' ) );
				add_action( 'update_option_wpseo_local', array( $this, 'save_permalinks_on_option_save' ), 10, 2 );

				// Setting action for removing the transient on update options.
				if ( method_exists( 'WPSEO_Sitemaps_Cache', 'register_cache_clear_option' ) ) {
					WPSEO_Sitemaps_Cache::register_clear_on_option_update( 'wpseo_local', 'kml' );
				}
			}
			else {
				// XML Sitemap Index addition.
				add_action( 'template_redirect', array( $this, 'redirect_old_sitemap' ) );
				$this->init();
				add_filter( 'wpseo_sitemap_index', array( $this, 'add_to_index' ) );
			}

			// Add support for Jetpack's Omnisearch.
			$this->support_jetpack_omnisearch();
			add_action( 'save_post', array( $this, 'invalidate_sitemap' ) );

			// Run update if needed.
			add_action( 'init', array( $this, 'do_upgrade' ), 14 );

			// Set the default plugin options.
			add_action( 'init', array( $this, 'set_defaults' ), 14 );
			add_action( 'init', array( $this, 'check_defaults' ), 14 );

			// Add Local SEO to the adminbar.
			add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 95 );

			add_action( 'update_option_wpseo_titles', array( $this, 'maybe_add_or_remove_option_error' ), 10, 2 );
		}

		/**
		 * Add Local SEO to the admin bar menu under SEO Settings
		 *
		 * @since 3.4
		 */
		public function admin_bar_menu() {
			global $wp_admin_bar;

			$wp_admin_bar->add_menu( array(
				'parent' => 'wpseo-settings',
				'id'     => 'wpseo-local',
				'title'  => 'Local SEO',
				'href'   => admin_url( 'admin.php?page=wpseo_local' ),
			) );
		}

		/**
		 * This method will perform some checks before performing plugin upgrade (when needed).
		 */
		public function do_upgrade() {
			$options = get_option( 'wpseo_local' );

			if ( ! isset( $options['version'] ) ) {
				$options['version'] = '0';
			}

			// Update Yoast SEO settings if updated from before 7.2.
			if ( version_compare( $options['version'], '7.2', '<' ) ) {
				WPSEO_Local_Core::update_yoast_seo_settings();
			}

			if ( version_compare( $options['version'], WPSEO_LOCAL_VERSION, '<' ) ) {

				// Upgrade to new licensing class.
				$license_manager = $this->get_license_manager();

				if ( $license_manager->license_is_valid() === false ) {

					if ( isset( $options['license'] ) ) {
						$license_manager->set_license_key( $options['license'] );
					}

					if ( isset( $options['license-status'] ) ) {
						$license_manager->set_license_status( $options['license-status'] );
					}
				}

				// First check if this is a multisite or not.
				if ( ! is_multisite() ) {
					// Performing other upgrades.
					wpseo_local_do_upgrade( $options['version'] );
				}
				else {
					// If this is a multisite, get all the blogs and loop through them.
					global $wpdb;
					$network_blogs = $wpdb->get_col( $wpdb->prepare( "SELECT blog_id FROM $wpdb->blogs WHERE site_id = %d", $wpdb->siteid ) );
					if ( is_array( $network_blogs ) && $network_blogs !== array() ) {
						foreach ( $network_blogs as $blog_id ) {
							switch_to_blog( $blog_id );
							wpseo_local_do_upgrade( $options['version'] );
							restore_current_blog();
						}
					}
				}

				// Update current version in database.
				$options['version'] = WPSEO_LOCAL_VERSION;
				update_option( 'wpseo_local', $options );
			}
		}

		/**
		 * Returns an instance of the Yoast_Plugin_License_Manager class
		 * Takes care of remotely (de)activating licenses and plugin updates.
		 */
		protected function get_license_manager() {

			// We need WP SEO 1.5+ or higher but WP SEO Local doesn't have a version check.
			if ( ! $this->license_manager ) {

				if ( ! class_exists( 'Yoast_Plugin_License_Manager' ) ) {
					return null;
				}

				$license_manager = new Yoast_Plugin_License_Manager( new Yoast_Product_WPSEO_Local() );
				$license_manager->set_license_constant_name( 'WPSEO_LOCAL_LICENSE' );
				$license_manager->setup_hooks();

				$this->license_manager = $license_manager;
			}

			return $this->license_manager;
		}

		/**
		 * Adds the rewrite for the Geo sitemap and KML file
		 *
		 * @since 1.0
		 */
		public function init() {

			if ( isset( $GLOBALS['wpseo_sitemaps'] ) ) {
				add_action( 'wpseo_do_sitemap_geo', array( $this, 'build_local_sitemap' ) );
				add_action( 'wpseo_do_sitemap_locations', array( $this, 'build_kml' ) );

				add_rewrite_rule( 'geo-sitemap\.xml$', 'index.php?sitemap=geo_', 'top' );
				add_rewrite_rule( 'locations\.kml$', 'index.php?sitemap=locations', 'top' );


				if ( preg_match( '/(geo-sitemap.xml|locations.kml)(.*?)$/', $_SERVER['REQUEST_URI'], $match ) ) {
					if ( in_array( $match[1], array( 'geo-sitemap.xml', 'locations.kml' ) ) ) {
						$sitemap = 'geo';
						if ( $match[1] == 'locations.kml' ) {
							$sitemap = 'locations';
						}

						$GLOBALS['wpseo_sitemaps']->build_sitemap( $sitemap );
					}
					else {
						return;
					}

					// 404 for invalid or emtpy sitemaps.
					if ( $GLOBALS['wpseo_sitemaps']->bad_sitemap ) {
						$GLOBALS['wp_query']->is_404 = true;

						return;
					}

					$GLOBALS['wpseo_sitemaps']->output();
					$GLOBALS['wpseo_sitemaps']->sitemap_close();
				}
			}
		}

		/**
		 * Method to invalidate the sitemap
		 *
		 * @param integer $post_id Post ID.
		 */
		public function invalidate_sitemap( $post_id ) {
			// If this is just a revision, don't invalidate the sitemap cache yet.
			if ( wp_is_post_revision( $post_id ) ) {
				return;
			}

			if ( get_post_type( $post_id ) === 'wpseo_locations' && method_exists( 'WPSEO_Sitemaps_Cache', 'invalidate' ) ) {
				WPSEO_Sitemaps_Cache::invalidate( 'kml' );
			}
		}

		/**
		 * Adds support for Jetpack's Omnisearch
		 */
		function support_jetpack_omnisearch() {
			if ( class_exists( 'Jetpack_Omnisearch_Posts' ) ) {
				new Jetpack_Omnisearch_Posts( 'wpseo_locations' );
			}
		}


		/**
		 * Redirects old geo_sitemap.xml to geo-sitemap.xml to be more in line with other XML sitemaps of Yoast SEO plugin.
		 *
		 * @since 1.2.2.1
		 */
		public function redirect_old_sitemap() {
			if ( preg_match( '/(geo_sitemap.xml)(.*?)$/', $_SERVER['REQUEST_URI'], $match ) ) {

				if ( $match[1] == 'geo_sitemap.xml' && method_exists( 'WPSEO_Sitemaps_Router', 'get_base_url' ) ) {
					wp_redirect( trailingslashit( WPSEO_Sitemaps_Router::get_base_url( '' ) ) . 'geo-sitemap.xml', 301 );
					exit;
				}
			}
		}

		/**
		 * @param boolean $exclude  Defaults to false.
		 * @param string  $taxonomy Name of the taxonomy to exclude.
		 *
		 * @return bool
		 */
		public function exclude_taxonomy_for_sitemap( $exclude, $taxonomy ) {
			if ( $taxonomy == 'wpseo_locations_category' ) {
				$exclude = true;
			}

			return $exclude;
		}

		/**
		 * Adds the Geo Sitemap to the Index Sitemap.
		 *
		 * @since 1.0
		 *
		 * @param string $str String with the filtered additions to the index sitemap in it.
		 *
		 * @return string $str String with the local XML sitemap additions to the index sitemap in it.
		 */
		public function add_to_index( $str ) {
			$base_url = '';
			if ( method_exists( 'WPSEO_Sitemaps_Router', 'get_base_url' ) ) {
				$base_url = WPSEO_Sitemaps_Router::get_base_url( 'geo-sitemap.xml' );
			}

			$date = get_option( 'wpseo_local_xml_update' );
			if ( ! $date || $date == '' ) {
				$date = date( 'c' );
			}

			$str .= '<sitemap>' . "\n";
			$str .= '<loc>' . $base_url . '</loc>' . "\n";
			$str .= '<lastmod>' . $date . '</lastmod>' . "\n";
			$str .= '</sitemap>' . "\n";

			return $str;
		}

		/**
		 * Pings Google with the (presumeably updated) Geo Sitemap.
		 *
		 * @since 1.0
		 */
		static private function ping() {

			// Ping Google. Just do it.
			if ( method_exists( 'WPSEO_Sitemaps_Router', 'get_base_url' ) ) {
				wp_remote_get( 'http://www.google.com/webmasters/tools/ping?sitemap=' . WPSEO_Sitemaps_Router::get_base_url( 'geo-sitemap.xml' ) );
			}
		}

		/**
		 * Updates the last update time transient for the local sitemap and pings Google with the sitemap.
		 *
		 * @since 1.0
		 */
		static function update_sitemap() {
			// Empty sitemap cache.
			$caching = apply_filters( 'wpseo_enable_xml_sitemap_transient_caching', true );
			if ( $caching ) {
				delete_transient( 'wpseo_sitemap_cache_kml' );
			}

			update_option( 'wpseo_local_xml_update', date( 'c' ) );

			// Ping sitemap.
			WPSEO_Local_Core::ping();
		}

		/**
		 * Set defaults settings for Local SEO.
		 *
		 * @since 3.4
		 */
		public static function set_defaults() {
			$defaults = array(
				'location_name'                       => '',
				'location_taxo_slug'                  => '',
				'business_type'                       => '',
				'business_image'                      => '',
				'location_address'                    => '',
				'location_address_2'                  => '',
				'location_city'                       => '',
				'location_state'                      => '',
				'location_zipcode'                    => '',
				'location_country'                    => '',
				'location_phone'                      => '',
				'location_phone_2nd'                  => '',
				'location_fax'                        => '',
				'location_email'                      => '',
				'location_url'                        => '',
				'location_vat_id'                     => '',
				'location_tax_id'                     => '',
				'location_coc_id'                     => '',
				'location_coords_lat'                 => '',
				'location_coords_long'                => '',
				'locations_slug'                      => '',
				'locations_label_singular'            => '',
				'locations_label_plural'              => '',
				'locations_taxo_slug'                 => '',
				'sl_num_results'                      => 10,
				'closed_label'                        => '',
				'opening_hours_monday_from'           => '09:00',
				'opening_hours_monday_to'             => '17:00',
				'opening_hours_monday_second_from'    => '09:00',
				'opening_hours_monday_second_to'      => '17:00',
				'opening_hours_tuesday_from'          => '09:00',
				'opening_hours_tuesday_to'            => '17:00',
				'opening_hours_tuesday_second_from'   => '09:00',
				'opening_hours_tuesday_second_to'     => '17:00',
				'opening_hours_wednesday_from'        => '09:00',
				'opening_hours_wednesday_to'          => '17:00',
				'opening_hours_wednesday_second_from' => '09:00',
				'opening_hours_wednesday_second_to'   => '17:00',
				'opening_hours_thursday_from'         => '09:00',
				'opening_hours_thursday_to'           => '17:00',
				'opening_hours_thursday_second_from'  => '09:00',
				'opening_hours_thursday_second_to'    => '17:00',
				'opening_hours_friday_from'           => '09:00',
				'opening_hours_friday_to'             => '17:00',
				'opening_hours_friday_second_from'    => '09:00',
				'opening_hours_friday_second_to'      => '17:00',
				'opening_hours_saturday_from'         => '09:00',
				'opening_hours_saturday_to'           => '17:00',
				'opening_hours_saturday_second_from'  => '09:00',
				'opening_hours_saturday_second_to'    => '17:00',
				'opening_hours_sunday_from'           => '09:00',
				'opening_hours_sunday_to'             => '17:00',
				'opening_hours_sunday_second_from'    => '09:00',
				'opening_hours_sunday_second_to'      => '17:00',
				'unit_system'                         => 'METRIC',
				'map_view_style'                      => 'HYBRID',
				'address_format'                      => 'address-state-postal',
				'default_country'                     => '',
				'show_route_label'                    => '',
				'custom_marker'                       => '',
				'api_key_browser'                     => '',
				'api_key'                             => '',
			);

			$defaults = apply_filters( 'wpseo_local_defaults', $defaults );

			WPSEO_Local_Core::$defaults = $defaults;
		}

		/**
		 * Check the default options and set them as option if needed.
		 *
		 * @since 3.9
		 */
		public function check_defaults() {
			$options = get_option( 'wpseo_local' );

			foreach ( WPSEO_Local_Core::$defaults as $option => $value ) {
				if ( empty( $options[ $option ] ) ) {
					$options[ $option ] = $value;
				}
			}

			update_option( 'wpseo_local', $options );
		}

		/**
		 * Update settings in wpseo_titles in Yoast SEO.
		 * To make the JSON+LD work correctly 'company_or_person' needs to be set to company.
		 * We force that update here.
		 *
		 * @since 7.2
		 */
		public static function update_yoast_seo_settings() {
			if ( class_exists( 'WPSEO_Options' ) ) {
				$wpseo_titles = WPSEO_Options::get( 'wpseo_titles' );

				if ( $wpseo_titles['company_or_person'] !== 'company' ) {
					// Always set it to company, no matter what.
					$update_option = WPSEO_Options::set( 'company_or_person', 'company' );

					if ( true === $update_option ) {
						$notification_center = Yoast_Notification_Center::get();
						$notification        = new Yoast_Notification(
							sprintf( __( 'In order to make full use of Yoast SEO: Local functionality, we have changed the setting for \'Person or Company\' under %1$sYoast SEO Search Appearance%2$s to \'Company\'', 'yoast-local-seo' ), '<a href="' . admin_url( 'admin.php?page=wpseo_titles' ) . '">', '</a>' ),
							array(
								'type' => Yoast_Notification::UPDATED,
								'id'   => 'PersonOrCompanySettingUpdate',
							)
						);

						$notification_center->add_notification( $notification );
					}
				}
			}
		}

		/**
		 * If the 'company_or_person' option is set to anything but 'company' thrown an error in Yoast Notification Center.
		 *
		 * @param array $old_value Old option value.
		 * @param array $new_value New option value.
		 *
		 * @since 7.2
		 */
		public function maybe_add_or_remove_option_error( $old_value, $new_value ) {
			if ( class_exists( 'Yoast_Notification_Center' ) ) {
				$notification_center = Yoast_Notification_Center::get();
				$notification_id     = 'PersonOrCompanySettingError';

				if ( 'company' !== $new_value['company_or_person'] ) {
					$notification = new Yoast_Notification(
						sprintf( __( 'In order to make full use of Yoast SEO: Local functionality, you should concider changing the setting for \'Person or Company\' under %1$sYoast SEO Search Appearance%2$s to \'Company\'', 'yoast-local-seo' ), '<a href="' . admin_url( 'admin.php?page=wpseo_titles' ) . '">', '</a>' ),
						array(
							'type' => Yoast_Notification::ERROR,
							'id'   => $notification_id,
						)
					);

					$notification_center->add_notification( $notification );
				}
				else {
					$notification = $notification_center->get_notification_by_id( $notification_id );

					if ( $notification instanceof Yoast_Notification ) {
						$notification_center->remove_notification( $notification );
					}
				}
			}
		}

		/**
		 * This function generates the Geo sitemap's contents.
		 *
		 * @since 1.0
		 */
		public function build_local_sitemap() {


			// Remark: no transient caching needed here, since the one home_url() request is faster than getting the transient cache.
			$kml_url = '';
			if ( method_exists( 'WPSEO_Sitemaps_Router', 'get_base_url' ) ) {
				$kml_url = WPSEO_Sitemaps_Router::get_base_url( 'locations.kml' );
			}

			// Build entry for Geo Sitemap.
			$output = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:geo="http://www.google.com/geo/schemas/sitemap/1.0">
				<url>
					<loc>' . $kml_url . '</loc>
					<lastmod>' . date( 'c' ) . '</lastmod>
					<priority>1</priority>
				</url>
			</urlset>';

			if ( isset( $GLOBALS['wpseo_sitemaps'] ) ) {
				$GLOBALS['wpseo_sitemaps']->set_sitemap( $output );
				$GLOBALS['wpseo_sitemaps']->renderer->set_stylesheet( '<?xml-stylesheet type="text/xsl" href="' . dirname( plugin_dir_url( __FILE__ ) ) . '/styles/geo-sitemap.xsl"?>' );
			}
		}

		/**
		 * This function generates the KML file contents.
		 *
		 * @since 1.0
		 */
		public function build_kml() {

			$output  = '';
			$caching = apply_filters( 'wpseo_enable_xml_sitemap_transient_caching', true );

			if ( $caching ) {
				$output = get_transient( 'wpseo_sitemap_cache_kml' );
			}

			if ( ! $output || '' == $output ) {
				$location_data = $this->get_location_data();

				if ( isset( $location_data['businesses'] ) && is_array( $location_data['businesses'] ) && count( $location_data['businesses'] ) > 0 ) {
					$output = "<kml xmlns=\"http://www.opengis.net/kml/2.2\" xmlns:atom=\"http://www.w3.org/2005/Atom\">\n";
					$output .= "\t<Document>\n";
					$output .= "\t\t<name>" . ( ! empty( $location_data['kml_name'] ) ? $location_data['kml_name'] : ' Locations for ' . $location_data['business_name'] ) . "</name>\n";

					if ( ! empty( $location_data->author ) ) {
						$output .= "\t\t<atom:author>\n";
						$output .= "\t\t\t<atom:name>" . $location_data['author'] . "</atom:name>\n";
						$output .= "\t\t</atom:author>\n";
					}
					if ( ! empty( $location_data_fields['business_website'] ) ) {
						$output .= "\t\t<atom:link href=\"" . $location_data['website'] . "\" />\n";
					}

					$output .= "\t\t<open>1</open>\n";
					$output .= "\t\t<Folder>\n";

					foreach ( $location_data['businesses'] as $key => $business ) {
						if ( ! empty( $business ) ) {
							$business_name        = esc_attr( $business['business_name'] );
							$business_description = ! empty( $business['business_description'] ) ? esc_attr( strip_shortcodes( $business['business_description'] ) ) : '';
							$business_description = htmlentities( $business_description );
							$business_url         = esc_url( $business['business_url'] );
							if ( wpseo_has_multiple_locations() && ! empty( $business['post_id'] ) ) {
								$business_url = get_permalink( $business['post_id'] );
							}
							if ( ! isset( $business['full_address'] ) || empty( $business['full_address'] ) ) {
								$address_format           = ! empty( $this->options['address_format'] ) ? $this->options['address_format'] : 'address-state-postal';
								$format                   = new WPSEO_Local_Address_Format();
								$business['full_address'] = $format->get_address_format( $address_format, array(
									'business_address'   => ( isset( $business['business_address'] ) ) ? $business['business_address'] : '',
									'business_address_2' => ( isset( $business['business_address_2'] ) ) ? $business['business_address_2'] : '',
									'oneline'            => false,
									'business_zipcode'   => ( isset( $business['business_zipcode'] ) ) ? $business['business_zipcode'] : '',
									'business_city'      => ( isset( $business['business_city'] ) ) ? $business['business_city'] : '',
									'business_state'     => ( isset( $business['business_state'] ) ) ? $business['business_state'] : '',
									'show_state'         => true,
									'escape_output'      => false,
									'use_tags'           => false,
								) );

								if ( ! empty( $business['business_country'] ) ) {
									$business['full_address'] .= ', ' . WPSEO_Local_Frontend::get_country( $business['business_country'] );
								}
							}
							$business_fulladdress = $business['full_address'];

							$output .= "\t\t\t<Placemark>\n";
							$output .= "\t\t\t\t<name><![CDATA[" . $business_name . "]]></name>\n";
							$output .= "\t\t\t\t<address><![CDATA[" . $business_fulladdress . "]]></address>\n";
							$output .= "\t\t\t\t<phoneNumber><![CDATA[" . $business['business_phone'] . "]]></phoneNumber>\n";
							$output .= "\t\t\t\t<description><![CDATA[" . $business_description . "]]></description>\n";
							$output .= "\t\t\t\t<atom:link href=\"" . $business_url . "\"/>\n";
							$output .= "\t\t\t\t<LookAt>\n";
							$output .= "\t\t\t\t\t<latitude>" . $business['coords']['lat'] . "</latitude>\n";
							$output .= "\t\t\t\t\t<longitude>" . $business['coords']['long'] . "</longitude>\n";
							$output .= "\t\t\t\t\t<altitude>1500</altitude>\n";
							$output .= "\t\t\t\t\t<range></range>\n";
							$output .= "\t\t\t\t\t<tilt>0</tilt>\n";
							$output .= "\t\t\t\t\t<heading></heading>\n";
							$output .= "\t\t\t\t\t<altitudeMode>relativeToGround</altitudeMode>\n";
							$output .= "\t\t\t\t</LookAt>\n";
							$output .= "\t\t\t\t<Point>\n";
							$output .= "\t\t\t\t\t<coordinates>" . $business['coords']['long'] . ',' . $business['coords']['lat'] . ",0</coordinates>\n";
							$output .= "\t\t\t\t</Point>\n";
							$output .= "\t\t\t</Placemark>\n";
						}
					}

					$output .= "\t\t</Folder>\n";
					$output .= "\t</Document>\n";
					$output .= "</kml>\n";

					if ( $caching ) {
						set_transient( 'wpseo_sitemap_cache_kml', $output, DAY_IN_SECONDS );
					}
				}
			}

			if ( isset( $GLOBALS['wpseo_sitemaps'] ) ) {
				$GLOBALS['wpseo_sitemaps']->set_sitemap( $output );
				$GLOBALS['wpseo_sitemaps']->renderer->set_stylesheet( '<?xml-stylesheet type="text/xsl" href="' . dirname( plugin_dir_url( __FILE__ ) ) . '/styles/kml-file.xsl"?>' );
			}
		}

		/**
		 * Empties the sitemap cache when saving the options
		 *
		 * @param mixed $old_value Old option value.
		 * @param mixed $new_value New option value.
		 */
		public function save_permalinks_on_option_save( $old_value, $new_value ) {
			// Empty sitemap cache.
			$caching = apply_filters( 'wpseo_enable_xml_sitemap_transient_caching', true );
			if ( $caching ) {
				delete_transient( 'wpseo_sitemap_cache_kml' );
			}
		}

		/**
		 * Builds an array based upon the data from the wpseo_locations post type. This data is needed as input for the Geo sitemap & KML API.
		 *
		 * @since 1.0
		 *
		 * @param null|int $post_id Post ID of location.
		 *
		 * @return array
		 */
		public function get_location_data( $post_id = null ) {
			$locations = array();

			// Define base URL.
			$base_url = '';
			if ( method_exists( 'WPSEO_Sitemaps_Router', 'get_base_url' ) ) {
				$base_url = WPSEO_Sitemaps_Router::get_base_url( '' );
			}

			$repo                    = new WPSEO_Local_Locations_Repository();
			$locations['businesses'] = $repo->get( array(
				'id' => $post_id,
			) );

			$base = $GLOBALS['wp_rewrite']->using_index_permalinks() ? 'index.php/' : '';

			$locations['business_name'] = get_option( 'blogname' );
			$locations['kml_name']      = 'Locations for ' . $locations['business_name'] . '.';
			$locations['kml_url']       = home_url( $base . '/locations.kml' );
			$locations['kml_website']   = $base_url;
			$locations['author']        = get_option( 'blogname' );

			return $locations;
		}

		/**
		 * Retrieves the lat/long coordinates from the Google Maps API
		 *
		 * @param array $location_info Array with location info. Array structure: array( _wpseo_business_address, _wpseo_business_city, _wpseo_business_state, _wpseo_business_zipcode, _wpseo_business_country ).
		 * @param bool  $force_update  Whether to force the update or not.
		 * @param int   $post_id       Post ID of location where GEO data is needed from.
		 *
		 * @return bool|array Returns coordinates in array ( Format: array( 'lat', 'long' ) ). False when call the Maps API did not succeed.
		 */
		public function get_geo_data( $location_info, $force_update = false, $post_id = 0 ) {
			$address_format = ! empty( $this->options['address_format'] ) ? $this->options['address_format'] : 'address-state-postal';
			$format         = new WPSEO_Local_Address_Format();
			$full_address   = $format->get_address_format( $address_format, array(
				'business_address' => $location_info['_wpseo_business_address'],
				'oneline'          => false,
				'business_zipcode' => $location_info['_wpseo_business_zipcode'],
				'business_city'    => $location_info['_wpseo_business_city'],
				'business_state'   => $location_info['_wpseo_business_state'],
				'show_state'       => true,
				'escape_output'    => false,
				'use_tags'         => false,
			) );
			$full_address   .= ', ' . WPSEO_Local_Frontend::get_country( $location_info['_wpseo_business_country'] );

			$coordinates = array();

			if ( ( $post_id === 0 || empty( $post_id ) ) && isset( $location_info['_wpseo_post_id'] ) ) {
				$post_id = $location_info['_wpseo_post_id'];
			}

			if ( $force_update || empty( $location_info['_wpseo_coords']['lat'] ) || empty( $location_info['_wpseo_coords']['long'] ) ) {

				$results = wpseo_geocode_address( $full_address );

				if ( is_wp_error( $results ) ) {
					return false;
				}

				if ( isset( $results->results[0] ) && ! empty( $results->results[0] ) ) {
					$coordinates['lat']  = $results->results[0]->geometry->location->lat;
					$coordinates['long'] = $results->results[0]->geometry->location->lng;

					if ( wpseo_has_multiple_locations() && $post_id !== 0 ) {
						// Set lat & long.
						update_post_meta( $post_id, '_wpseo_coordinates_lat', $coordinates['lat'] );
						update_post_meta( $post_id, '_wpseo_coordinates_long', $coordinates['long'] );
					}
					else {
						$options = get_option( 'wpseo_local' );
						// Set lat & long.
						$options['location_coords_lat']  = $coordinates['lat'];
						$options['location_coords_long'] = $coordinates['long'];

						update_option( 'wpseo_local', $options );
					}
				}
			}
			else {
				$coordinates['lat']  = $location_info['_wpseo_coords']['lat'];
				$coordinates['long'] = $location_info['_wpseo_coords']['long'];
			}

			$return_array['coords']       = $coordinates;
			$return_array['full_address'] = $full_address;

			return $return_array;
		}

		/**
		 * Check if the uploaded custom marker does not exceed 100x100px
		 *
		 * @param int $image_id The ID of the uploaded custom marker.
		 */
		public function check_custom_marker_size( $image_id ) {
			if ( empty( $image_id ) ) {
				return;
			}

			$image = wp_get_attachment_image_src( $image_id );

			if ( ! is_array( $image ) ) {
				return;
			}

			if ( $image[1] > 100 || $image[2] > 100 ) {
				echo '<p class="desc label" style="border:none; margin-bottom: 0;">' . __( 'The uploaded custom marker exceeds the recommended size of 100x100 px. Please be aware this might influence the info popup.', 'yoast-local-seo' ) . '</p>';
			}
		}

		/**
		 * Creates the wpseo_locations Custom Post Type
		 */
		public function create_custom_post_type() {
			/* Locations as Custom Post Type */
			$label_singular = ! empty( $this->options['locations_label_singular'] ) ? $this->options['locations_label_singular'] : __( 'Location', 'yoast-local-seo' );
			$label_plural   = ! empty( $this->options['locations_label_plural'] ) ? $this->options['locations_label_plural'] : __( 'Locations', 'yoast-local-seo' );
			$labels         = array(
				'name'               => $label_plural,
				'singular_name'      => $label_singular,
				/* translators: %s extends to the singular label for the location post type */
				'add_new'            => sprintf( __( 'New %s', 'yoast-local-seo' ), $label_singular ),
				/* translators: %s extends to the singular label for the location post type */
				'new_item'           => sprintf( __( 'New %s', 'yoast-local-seo' ), $label_singular ),
				/* translators: %s extends to the singular label for the location post type */
				'add_new_item'       => sprintf( __( 'Add New %s', 'yoast-local-seo' ), $label_singular ),
				/* translators: %s extends to the singular label for the location post type */
				'edit_item'          => sprintf( __( 'Edit %s', 'yoast-local-seo' ), $label_singular ),
				/* translators: %s extends to the singular label for the location post type */
				'view_item'          => sprintf( __( 'View %s', 'yoast-local-seo' ), $label_singular ),
				/* translators: %s extends to the plural label for the location post type */
				'search_items'       => sprintf( __( 'Search %s', 'yoast-local-seo' ), $label_plural ),
				/* translators: %s extends to the plural label for the location post type */
				'not_found'          => sprintf( __( 'No %s found', 'yoast-local-seo' ), $label_plural ),
				/* translators: %s extends to the plural label for the location post type */
				'not_found_in_trash' => sprintf( __( 'No %s found in trash', 'yoast-local-seo' ), $label_plural ),
			);

			$slug = ! empty( $this->options['locations_slug'] ) ? $this->options['locations_slug'] : 'locations';

			$args_cpt = array(
				'labels'          => $labels,
				'public'          => true,
				'show_ui'         => true,
				'capability_type' => 'post',
				'hierarchical'    => false,
				'rewrite'         => array(
					'slug'       => esc_attr( $slug ),
					'with_front' => apply_filters( 'yoast_seo_local_cpt_with_front', true ),
				),
				'has_archive'     => esc_attr( $slug ),
				'menu_icon'       => 'dashicons-location',
				'query_var'       => true,
				'supports'        => array(
					'title',
					'editor',
					'excerpt',
					'author',
					'thumbnail',
					'revisions',
					'custom-fields',
					'page-attributes',
					'publicize',
					'wpcom-markdown',
				),
			);
			$args_cpt = apply_filters( 'wpseo_local_cpt_args', $args_cpt );

			register_post_type( 'wpseo_locations', $args_cpt );
		}

		/**
		 * Create custom taxonomy for wpseo_locations Custom Post Type
		 */
		public function create_taxonomies() {
			$location_post_type       = get_post_type_object( 'wpseo_locations' );
			$post_type_singular_label = $location_post_type->labels->singular_name;

			$labels = array(
				/* translators: %s extends to the singular label for the location category */
				'name'              => sprintf( __( '%s categories', 'yoast-local-seo' ), $post_type_singular_label ),
				/* translators: %s extends to the singular label for the location category */
				'singular_name'     => sprintf( __( '%s category', 'yoast-local-seo' ), $post_type_singular_label ),
				/* translators: %s extends to the singular label for the location category */
				'search_items'      => sprintf( __( 'Search %s categories', 'yoast-local-seo' ), $post_type_singular_label ),
				/* translators: %s extends to the singular label for the location category */
				'all_items'         => sprintf( __( 'All %s categories', 'yoast-local-seo' ), $post_type_singular_label ),
				/* translators: %s extends to the singular label for the location category */
				'parent_item'       => sprintf( __( 'Parent %s category', 'yoast-local-seo' ), $post_type_singular_label ),
				/* translators: %s extends to the singular label for the location category */
				'parent_item_colon' => sprintf( __( 'Parent %s category:', 'yoast-local-seo' ), $post_type_singular_label ),
				/* translators: %s extends to the singular label for the location category */
				'edit_item'         => sprintf( __( 'Edit %s category', 'yoast-local-seo' ), $post_type_singular_label ),
				/* translators: %s extends to the singular label for the location category */
				'update_item'       => sprintf( __( 'Update %s category', 'yoast-local-seo' ), $post_type_singular_label ),
				/* translators: %s extends to the singular label for the location category */
				'add_new_item'      => sprintf( __( 'Add New %s category', 'yoast-local-seo' ), $post_type_singular_label ),
				/* translators: %s extends to the singular label for the location category */
				'new_item_name'     => sprintf( __( 'New %s category name', 'yoast-local-seo' ), $post_type_singular_label ),
				/* translators: %s extends to the singular label for the location category */
				'menu_name'         => apply_filters( 'wpseo_locations_category_label', sprintf( __( '%s categories', 'yoast-local-seo' ), $post_type_singular_label ) ),
			);

			$slug = ! empty( $this->options['locations_taxo_slug'] ) ? $this->options['locations_taxo_slug'] : 'locations-category';

			$args = array(
				'hierarchical'          => true,
				'labels'                => $labels,
				'show_ui'               => true,
				'show_admin_column'     => true,
				'update_count_callback' => '_update_post_term_count',
				'query_var'             => true,
				'rewrite'               => array( 'slug' => esc_attr( $slug ) ),
			);
			$args = apply_filters( 'wpseo_local_custom_taxonomy_args', $args );

			// NOTE: when using the wpseo_locations_category_slug filter, be sure to save the permalinks in order for it to work.
			register_taxonomy( 'wpseo_locations_category', 'wpseo_locations', $args );
		}

		/**
		 * Call filter to exclude taxonomies from sitemap
		 */
		public function exclude_taxonomy() {
			add_filter( 'wpseo_sitemap_exclude_taxonomy', array( $this, 'exclude_taxonomy_for_sitemap' ), 10, 2 );
		}

		/**
		 * Filter the WPSEO primary term taxonomies to make sure the location categories are added to the array.
		 *
		 * Enable primary term for location categories, by adding this to the taxonomies array.
		 *
		 * @param array  $taxonomies     An array of taxonomy objects that are primary_term enabled.
		 * @param string $post_type      The post type for which to filter the taxonomies.
		 * @param array  $all_taxonomies All taxonomies for this post type, even ones that don't have primary term.
		 *
		 * @return array
		 */
		public function filter_wpseo_primary_term_taxonomies( $taxonomies, $post_type, $all_taxonomies ) {
			if ( isset( $all_taxonomies['wpseo_locations_category'] ) ) {
				$taxonomies['wpseo_locations_category'] = $all_taxonomies['wpseo_locations_category'];
			}

			return $taxonomies;
		}

		/**
		 * Inserts attachment in WordPress. Used by import panel.
		 *
		 * @param int    $post_id   The post ID where the attachment belongs to.
		 * @param string $image_url file url of the file which has to be uploaded.
		 * @param bool   $set_thumb If there's an image in the import file, then set is as a Featured Image.
		 *
		 * @return int|WP_Error attachment ID. Returns WP_Error when upload goes wrong.
		 */
		public function insert_attachment( $post_id, $image_url, $set_thumb = false ) {

			$file_array  = array();
			$description = get_the_title( $post_id );
			$tmp         = download_url( $image_url );

			// Set variables for storage.
			// Fix file filename for query strings.
			preg_match( '/[^\?]+\.(jpg|jpe|jpeg|gif|png)/i', $image_url, $matches );
			$file_array['name']     = basename( $matches[0] );
			$file_array['tmp_name'] = $tmp;

			// If error storing temporarily, unlink.
			if ( is_wp_error( $tmp ) ) {
				@unlink( $file_array['tmp_name'] );
				$file_array['tmp_name'] = '';
			}

			// Do the validation and storage stuff.
			$attachment_id = media_handle_sideload( $file_array, $post_id, $description );

			// If error storing permanently, unlink.
			if ( is_wp_error( $attachment_id ) ) {
				@unlink( $file_array['tmp_name'] );

				return $attachment_id;
			}

			if ( $set_thumb ) {
				update_post_meta( $post_id, '_thumbnail_id', $attachment_id );
			}

			return $attachment_id;
		}

		/**
		 * Returns the valid local business types currently shown on Schema.org
		 *
		 * @link http://schema.org/docs/full.html In the bottom of this page is a list of Local Business types.
		 * @return array
		 */
		public function get_local_business_types() {
			return array(
				'Organization'                => 'Organization',
				'Corporation'                 => 'Corporation',
				'GovernmentOrganization'      => 'Government Organization',
				'NGO'                         => 'NGO',
				'EducationalOrganization'     => 'Educational Organization',
				'CollegeOrUniversity'         => '&mdash; College or University',
				'ElementarySchool'            => '&mdash; Elementary School',
				'HighSchool'                  => '&mdash; High School',
				'MiddleSchool'                => '&mdash; Middle School',
				'Preschool'                   => '&mdash; Preschool',
				'School'                      => '&mdash; School',
				'PerformingGroup'             => 'Performing Group',
				'DanceGroup'                  => '&mdash; Dance Group',
				'MusicGroup'                  => '&mdash; Music Group',
				'TheaterGroup'                => '&mdash; Theater Group',
				'SportsTeam'                  => 'Sports Team',
				'LocalBusiness'               => 'Local Business',
				'AnimalShelter'               => 'Animal Shelter',
				'AutomotiveBusiness'          => 'Automotive Business',
				'AutoBodyShop'                => '&mdash; Auto Body Shop',
				'AutoDealer'                  => '&mdash; Auto Dealer',
				'AutoPartsStore'              => '&mdash; Auto Parts Store',
				'AutoRental'                  => '&mdash; Auto Rental',
				'AutoRepair'                  => '&mdash; Auto Repair',
				'AutoWash'                    => '&mdash; Auto Wash',
				'GasStation'                  => '&mdash; Gas Station',
				'MotorcycleDealer'            => '&mdash; Motorcycle Dealer',
				'MotorcycleRepair'            => '&mdash; Motorcycle Repair',
				'ChildCare'                   => 'Child Care',
				'DryCleaningOrLaundry'        => 'Dry Cleaning or Laundry',
				'EmergencyService'            => 'Emergency Service',
				'FireStation'                 => '&mdash; Fire Station',
				'Hospital'                    => '&mdash; Hospital',
				'PoliceStation'               => '&mdash; Police Station',
				'EmploymentAgency'            => 'Employment Agency',
				'EntertainmentBusiness'       => 'Entertainment Business',
				'AdultEntertainment'          => '&mdash; Adult Entertainment',
				'AmusementPark'               => '&mdash; Amusement Park',
				'ArtGallery'                  => '&mdash; Art Gallery',
				'Casino'                      => '&mdash; Casino',
				'ComedyClub'                  => '&mdash; Comedy Club',
				'MovieTheater'                => '&mdash; Movie Theater',
				'NightClub'                   => '&mdash; Night Club',
				'FinancialService'            => 'Financial Service',
				'AccountingService'           => '&mdash; Accounting Service',
				'AutomatedTeller'             => '&mdash; Automated Teller',
				'BankOrCreditUnion'           => '&mdash; Bank or Credit Union',
				'InsuranceAgency'             => '&mdash; Insurance Agency',
				'FoodEstablishment'           => 'Food Establishment',
				'Bakery'                      => '&mdash; Bakery',
				'BarOrPub'                    => '&mdash; Bar or Pub',
				'Brewery'                     => '&mdash; Brewery',
				'CafeOrCoffeeShop'            => '&mdash; Cafe or Coffee Shop',
				'FastFoodRestaurant'          => '&mdash; Fast Food Restaurant',
				'IceCreamShop'                => '&mdash; Ice Cream Shop',
				'Restaurant'                  => '&mdash; Restaurant',
				'Winery'                      => '&mdash; Winery',
				'GovernmentOffice'            => 'Government Office',
				'PostOffice'                  => '&mdash; Post Office',
				'HealthAndBeautyBusiness'     => 'Health And Beauty Business',
				'BeautySalon'                 => '&mdash; Beauty Salon',
				'DaySpa'                      => '&mdash; Day Spa',
				'HairSalon'                   => '&mdash; Hair Salon',
				'HealthClub'                  => '&mdash; Health Club',
				'NailSalon'                   => '&mdash; Nail Salon',
				'TattooParlor'                => '&mdash; Tattoo Parlor',
				'HomeAndConstructionBusiness' => 'Home And Construction Business',
				'Electrician'                 => '&mdash; Electrician',
				'GeneralContractor'           => '&mdash; General Contractor',
				'HVACBusiness'                => '&mdash; HVAC Business',
				'HousePainter'                => '&mdash; House Painter',
				'Locksmith'                   => '&mdash; Locksmith',
				'MovingCompany'               => '&mdash; Moving Company',
				'Plumber'                     => '&mdash; Plumber',
				'RoofingContractor'           => '&mdash; Roofing Contractor',
				'InternetCafe'                => 'Internet Cafe',
				'Library'                     => ' Library',
				'LodgingBusiness'             => 'Lodging Business',
				'BedAndBreakfast'             => '&mdash; Bed And Breakfast',
				'Hostel'                      => '&mdash; Hostel',
				'Hotel'                       => '&mdash; Hotel',
				'Motel'                       => '&mdash; Motel',
				'MedicalOrganization'         => 'Medical Organization',
				'Dentist'                     => '&mdash; Dentist',
				'DiagnosticLab'               => '&mdash; Diagnostic Lab',
				'Hospital'                    => '&mdash; Hospital',
				'MedicalClinic'               => '&mdash; Medical Clinic',
				'Optician'                    => '&mdash; Optician',
				'Pharmacy'                    => '&mdash; Pharmacy',
				'Physician'                   => '&mdash; Physician',
				'VeterinaryCare'              => '&mdash; Veterinary Care',
				'ProfessionalService'         => 'Professional Service',
				'AccountingService'           => '&mdash; Accounting Service',
				'LegalService'                => '&mdash; Legal Service',
				'Dentist'                     => '&mdash; Dentist',
				'Electrician'                 => '&mdash; Electrician',
				'GeneralContractor'           => '&mdash; General Contractor',
				'HousePainter'                => '&mdash; House Painter',
				'Locksmith'                   => '&mdash; Locksmith',
				'Notary'                      => '&mdash; Notary',
				'Plumber'                     => '&mdash; Plumber',
				'RoofingContractor'           => '&mdash; Roofing Contractor',
				'RadioStation'                => 'Radio Station',
				'RealEstateAgent'             => 'Real Estate Agent',
				'RecyclingCenter'             => 'Recycling Center',
				'SelfStorage'                 => 'Self Storage',
				'ShoppingCenter'              => 'Shopping Center',
				'SportsActivityLocation'      => 'Sports Activity Location',
				'BowlingAlley'                => '&mdash; Bowling Alley',
				'ExerciseGym'                 => '&mdash; Exercise Gym',
				'GolfCourse'                  => '&mdash; Golf Course',
				'HealthClub'                  => '&mdash; Health Club',
				'PublicSwimmingPool'          => '&mdash; Public Swimming Pool',
				'SkiResort'                   => '&mdash; Ski Resort',
				'SportsClub'                  => '&mdash; Sports Club',
				'StadiumOrArena'              => '&mdash; Stadium or Arena',
				'TennisComplex'               => '&mdash; Tennis Complex',
				'Store'                       => ' Store',
				'AutoPartsStore'              => '&mdash; Auto Parts Store',
				'BikeStore'                   => '&mdash; Bike Store',
				'BookStore'                   => '&mdash; Book Store',
				'ClothingStore'               => '&mdash; Clothing Store',
				'ComputerStore'               => '&mdash; Computer Store',
				'ConvenienceStore'            => '&mdash; Convenience Store',
				'DepartmentStore'             => '&mdash; Department Store',
				'ElectronicsStore'            => '&mdash; Electronics Store',
				'Florist'                     => '&mdash; Florist',
				'FurnitureStore'              => '&mdash; Furniture Store',
				'GardenStore'                 => '&mdash; Garden Store',
				'GroceryStore'                => '&mdash; Grocery Store',
				'HardwareStore'               => '&mdash; Hardware Store',
				'HobbyShop'                   => '&mdash; Hobby Shop',
				'HomeGoodsStore'              => '&mdash; HomeGoods Store',
				'JewelryStore'                => '&mdash; Jewelry Store',
				'LiquorStore'                 => '&mdash; Liquor Store',
				'MensClothingStore'           => '&mdash; Mens Clothing Store',
				'MobilePhoneStore'            => '&mdash; Mobile Phone Store',
				'MovieRentalStore'            => '&mdash; Movie Rental Store',
				'MusicStore'                  => '&mdash; Music Store',
				'OfficeEquipmentStore'        => '&mdash; Office Equipment Store',
				'OutletStore'                 => '&mdash; Outlet Store',
				'PawnShop'                    => '&mdash; Pawn Shop',
				'PetStore'                    => '&mdash; Pet Store',
				'ShoeStore'                   => '&mdash; Shoe Store',
				'SportingGoodsStore'          => '&mdash; Sporting Goods Store',
				'TireShop'                    => '&mdash; Tire Shop',
				'ToyStore'                    => '&mdash; Toy Store',
				'WholesaleStore'              => '&mdash; Wholesale Store',
				'TelevisionStation'           => 'Television Station',
				'TouristInformationCenter'    => 'Tourist Information Center',
				'TravelAgency'                => 'Travel Agency',
				'Airport'                     => 'Airport',
				'Aquarium'                    => 'Aquarium',
				'Beach'                       => 'Beach',
				'BusStation'                  => 'BusStation',
				'BusStop'                     => 'BusStop',
				'Campground'                  => 'Campground',
				'Cemetery'                    => 'Cemetery',
				'Crematorium'                 => 'Crematorium',
				'EventVenue'                  => 'Event Venue',
				'FireStation'                 => 'Fire Station',
				'GovernmentBuilding'          => 'Government Building',
				'CityHall'                    => '&mdash; City Hall',
				'Courthouse'                  => '&mdash; Courthouse',
				'DefenceEstablishment'        => '&mdash; Defence Establishment',
				'Embassy'                     => '&mdash; Embassy',
				'LegislativeBuilding'         => '&mdash; Legislative Building',
				'Hospital'                    => 'Hospital',
				'MovieTheater'                => 'Movie Theater',
				'Museum'                      => 'Museum',
				'MusicVenue'                  => 'Music Venue',
				'Park'                        => 'Park',
				'ParkingFacility'             => 'Parking Facility',
				'PerformingArtsTheater'       => 'Performing Arts Theater',
				'PlaceOfWorship'              => 'Place Of Worship',
				'BuddhistTemple'              => '&mdash; Buddhist Temple',
				'CatholicChurch'              => '&mdash; Catholic Church',
				'Church'                      => '&mdash; Church',
				'HinduTemple'                 => '&mdash; Hindu Temple',
				'Mosque'                      => '&mdash; Mosque',
				'Synagogue'                   => '&mdash; Synagogue',
				'Playground'                  => 'Playground',
				'PoliceStation'               => 'PoliceStation',
				'RVPark'                      => 'RVPark',
				'StadiumOrArena'              => 'StadiumOrArena',
				'SubwayStation'               => 'SubwayStation',
				'TaxiStand'                   => 'TaxiStand',
				'TrainStation'                => 'TrainStation',
				'Zoo'                         => 'Zoo',
				'Residence'                   => 'Residence',
				'ApartmentComplex'            => '&mdash; Apartment Complex',
				'GatedResidenceCommunity'     => '&mdash; Gated Residence Community',
				'SingleFamilyResidence'       => '&mdash; Single Family Residence',
			);
		}
	}
}
