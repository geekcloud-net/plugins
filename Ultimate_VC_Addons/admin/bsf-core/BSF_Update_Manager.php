<?php

// delete these transients/options for debugging
// set_site_transient( 'update_plugins', null );
// set_site_transient( 'update_themes', null );
// delete_option( 'brainstrom_products' );

/**
 *
 */
if ( ! class_exists( 'BSF_Update_Manager' ) ) {

	class BSF_Update_Manager {

		public function __construct() {

			// update data to WordPress's transient
			add_filter( 'pre_set_site_transient_update_plugins', array(
				$this,
				'brainstorm_update_plugins_transient'
			) );
			add_filter( 'pre_set_site_transient_update_themes', array( $this, 'brainstorm_update_themes_transient' ) );

			// display changelog in update details
			add_filter( 'plugins_api', array( $this, 'bsf_get_plugin_information' ), 10, 3 );

			// display correct error messages
			add_action( 'load-plugins.php', array( $this, 'bsf_update_display_license_link' ) );
			add_filter( 'upgrader_pre_download', array( $this, 'bsf_change_no_package_message' ), 20, 3 );
		}

		public function brainstorm_update_plugins_transient( $_transient_data ) {

			global $pagenow;

			if ( ! is_object( $_transient_data ) ) {
				$_transient_data = new stdClass;
			}

			$update_data = $this->bsf_update_transient_data( 'plugins' );
			
			foreach ( $update_data as $key => $product ) {

				if ( isset( $product['template'] ) && $product['template'] != '' ) {
					$template = $product['template'];
				} elseif ( isset( $product['init'] ) && $product['init'] != '' ) {
					$template = $product['init'];
				}

				if ( isset( $_transient_data->response[ $template ] ) ) {
					continue;
				}

				$plugin              = new stdClass();
				$plugin->id          = isset( $product['id'] ) ? $product['id'] : '';
				$plugin->slug        = $this->bsf_get_plugin_slug( $template );
				$plugin->plugin      = isset( $template ) ? $template : '';

				if ( $this->use_beta_version( $plugin->id ) ) {
					$plugin->new_version = isset( $product['version_beta'] ) ? $product['version_beta'] : '';
				} else {
					$plugin->new_version = isset( $product['remote'] ) ? $product['remote'] : '';
				}

				$plugin->url         = isset( $product['purchase_url'] ) ? $product['purchase_url'] : '';

				if ( BSF_License_Manager::bsf_is_active_license( $product['id'] ) == 'registered' ) {
					$plugin->package = $this->bsf_get_package_uri( $product['id'] );
				} else {
					$plugin->package = '';
					$bundled         = self::bsf_is_product_bundled( $plugin->id );
					if ( ! empty( $bundled ) ) {
						$parent_id              = $bundled[0];
						$parent_name            = brainstrom_product_name( $parent_id );
						$plugin->upgrade_notice = "This plugin is came bundled with the " . $parent_name . ". For receiving updates, you need to register license of " . $parent_name . ".";
					} else {
						$plugin->upgrade_notice = 'Please activate your license to receive automatic updates.';
					}
				}

				$plugin->tested = isset( $product['tested'] ) ? $product['tested'] : '';

				$_transient_data->last_checked          = time();
				$_transient_data->response[ $template ] = $plugin;
			}

			return $_transient_data;
		}

		public function brainstorm_update_themes_transient( $_transient_data ) {

			global $pagenow;

			if ( ! is_object( $_transient_data ) ) {
				$_transient_data = new stdClass;
			}

			if ( 'themes.php' != $pagenow && 'update-core.php' !== $pagenow ) {
				return $_transient_data;
			}

			$update_data = $this->bsf_update_transient_data( 'themes' );

			foreach ( $update_data as $key => $product ) {

				if ( isset( $product['template'] ) && $product['template'] != '' ) {
					$template = $product['template'];
				}

				$themes                = array();
				$themes['theme']       = isset( $template ) ? $template : '';
				
				if ( $this->use_beta_version( $product['id'] ) ) {
					$themes['new_version'] = isset( $product['version_beta'] ) ? $product['version_beta'] : '';
				} else {
					$themes['new_version'] = isset( $product['remote'] ) ? $product['remote'] : '';	
				}
				
				$themes['url']         = isset( $product['purchase_url'] ) ? $product['purchase_url'] : '';
				if ( BSF_License_Manager::bsf_is_active_license( $product['id'] ) == 'registered' ) {
					$themes['package'] = $this->bsf_get_package_uri( $product['id'] );
				} else {
					$themes['package']        = '';
					$themes['upgrade_notice'] = 'Please activate your license to receive automatic updates.';
				}
				$_transient_data->last_checked          = time();
				$_transient_data->response[ $template ] = $themes;
			}

			return $_transient_data;
		}

		/**
		 * Updates information on the "View version x.x details" page with custom data.
		 *
		 * @uses api_request()
		 *
		 * @param mixed $_data
		 * @param string $_action
		 * @param object $_args
		 *
		 * @return object $_data
		 */
		public function bsf_get_plugin_information( $_data, $_action = '', $_args = null ) {


			if ( $_action != 'plugin_information' ) {

				return $_data;

			}

			$brainstrom_products = apply_filters( 'bsf_get_plugin_information', get_option( 'brainstrom_products', array() ) );

			$plugins      = isset( $brainstrom_products['plugins'] ) ? $brainstrom_products['plugins'] : array();
			$themes       = isset( $brainstrom_products['themes'] ) ? $brainstrom_products['themes'] : array();
			$all_products = $plugins + $themes;

			foreach ( $all_products as $key => $product ) {

				$product_slug = isset( $product['slug'] ) ? $product['slug'] : '';

				if ( $product_slug == $_args->slug ) {

					$id = isset( $product['id'] ) ? $product['id'] : '';

					$info              = new stdClass();

					if ( $this->use_beta_version( $id ) ) {
						$info->new_version = isset( $product['version_beta'] ) ? $product['version_beta'] : '';
					} else {
						$info->new_version = isset( $product['remote'] ) ? $product['remote'] : '';	
					}
					
					$product_name      = isset( $product['name'] ) ? $product['name'] : '';
					$info->name        = apply_filters( "bsf_product_name_{$id}", $product_name );
					$info->slug        = $product_slug;
					$info->version     = isset( $product['version'] ) ? $product['version'] : '';
					$info->author      = 'Brainstorm Force';
					$info->url         = isset( $product['changelog_url'] ) ? $product['changelog_url'] : '';
					$info->homepage    = isset( $product['purchase_url'] ) ? $product['purchase_url'] : '';

					if ( BSF_License_Manager::bsf_is_active_license( $id ) == true ) {
						$package_url         = $this->bsf_get_package_uri( $id );
						$info->package       = $package_url;
						$info->download_link = $package_url;
					}

					$info->sections                = array();
					$info->sections['description'] = isset( $product['description'] ) ? $product['description'] : '';
					$info->sections['changelog']   = 'Thank you for using ' . $info->name . '. </br></br>To make your experience using ' . $info->name . ' better we release updates regularly, you can view the full changelog <a href="' . $info->url . '">here</a>';

					$_data = $info;
				}
			}

			return $_data;
		}

		// helpers

		public static function bsf_is_product_bundled( $bsf_product, $search_by = 'id' ) {
			$brainstrom_bundled_products = get_option( 'brainstrom_bundled_products', array() );
			$product_parent              = array();

			foreach ( $brainstrom_bundled_products as $parent => $products ) {

				foreach ( $products as $key => $product ) {

					if ( $search_by == 'init' ) {

						if ( $product->init == $bsf_product ) {
							$product_parent[] = $parent;
						}
					} elseif ( $search_by == 'id' ) {

						if ( $product->id == $bsf_product ) {
							$product_parent[] = $parent;
						}
					} elseif ( $search_by == 'name' ) {

						if ( strcasecmp( $product->name, $bsf_product ) == 0 ) {
							$product_parent[] = $parent;
						}
					}
				}
			}

			// Bundled plugins are installed when the demo is imported on Ajax request and bundled products should be unchanged in the ajax.
			if ( ! defined( 'DOING_AJAX' ) ) {

				$key = array_search( 'astra-pro-sites', $product_parent );

				if ( false !== $key ) {
					unset( $product_parent[ $key ] );
				}
			}

			$product_parent = apply_filters( 'bsf_is_product_bundled', array_unique( $product_parent ), $bsf_product, $search_by );			

			return $product_parent;
		}

		public function bsf_get_package_uri( $product_id ) {

			// use the cached url for 2 hours.
			if ( ! $this->time_since_last_versioncheck( 2 ) ) {
				$product       = get_brainstorm_product( $product_id );
				$status        = BSF_License_Manager::bsf_is_active_license( $product_id );

				if ( $this->use_beta_version( $product_id ) ) {
					$download_file = isset( $product['download_url_beta'] ) ? $product['download_url_beta'] : '';
				} else {
					$download_file = isset( $product['download_url'] ) ? $product['download_url'] : '';
				}

				if ( $download_file !== '' ) {

					if ( $status == false ) {
						return '';
					}

					$timezone = date_default_timezone_get();
					$hash     = 'file=' . $download_file . '&hashtime=' . strtotime( date( 'd-m-Y h:i:s a' ) ) . '&timezone=' . $timezone;

					$get_path      = 'http://downloads.brainstormforce.com/';
					$download_path = rtrim( $get_path, '/' ) . '/download.php?' . $hash . '&base=ignore';

					return $download_path;
				}
			}

			$product       				 = get_brainstorm_product( $product_id );
			$status        				 = BSF_License_Manager::bsf_is_active_license( $product_id );
			$brainstrom_products         = get_option( 'brainstrom_products', array() );
			$brainstrom_bundled_products = get_option( 'brainstrom_bundled_products', array() );
			$plugins      				 = isset( $brainstrom_products['plugins'] ) ? $brainstrom_products['plugins'] : array();
			$themes       				 = isset( $brainstrom_products['themes'] ) ? $brainstrom_products['themes'] : array();
			$all_products 				 = $plugins + $themes;
			$path         				 = get_api_url() . '?referer=package-' . $product_id;
			$is_bundled   				 = self::bsf_is_product_bundled( $product_id );
			$purchase_key 				 = isset( $all_products[ $product_id ]['purchase_key'] ) ? $all_products[ $product_id ]['purchase_key'] : null;
			$bundled      				 = false;

			if ( ! empty( $is_bundled ) ) {
				$bundled = true;
			}

			$data = array(
				'action'       => 'bsf_product_update_request',
				'id'           => $product_id,
				'username'     => '', // username is being depracated in new Graupi
				'purchase_key' => $purchase_key,
				'site_url'     => get_site_url(),
				'bundled'      => $bundled
			);

			$request = wp_remote_post(
				$path, array(
					'body'      => $data,
					'timeout'   => '30'
				)
			);

			// Request http URL if the https version fails.
			if ( is_wp_error( $request ) && wp_remote_retrieve_response_code( $request ) !== 200 ) {
				$path    = get_api_url( true ) . '?referer=package-' . $product_id;
				$request = wp_remote_post(
					$path, array(
						'body'      => $data,
						'timeout'   => '30'
					)
				);
			}

			if ( ! is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) === 200 ) {

				$result = json_decode( wp_remote_retrieve_body( $request ) );

				if ( isset( $result->error ) && ! $result->error ) {
					
					if ( $this->use_beta_version( $product_id ) ) {
						$download_path = $result->update_data->download_url_beta;
					} else {
						$download_path = $result->update_data->download_url;
					}

					$timezone      = date_default_timezone_get();
					$hash          = 'file=' . $download_path . '&hashtime=' . strtotime( date( 'd-m-Y h:i:s a' ) ) . '&timezone=' . $timezone;

					$get_path      = 'http://downloads.brainstormforce.com/';
					$download_path = rtrim( $get_path, '/' ) . '/download.php?' . $hash . '&base=ignore';

					return $download_path;
				}
			}
		}

		public function bsf_update_transient_data( $product_type ) {

			$this->_maybe_force_check_bsf_product_updates();

			$all_products    = array();
			$update_required = array();

			if ( $product_type == 'plugins' ) {
				$all_products = brainstorm_get_all_products( false, true, false );
			}

			if ( $product_type == 'themes' ) {
				$all_products = brainstorm_get_all_products( true, false, true );
			}

			foreach ( $all_products as $key => $product ) {

				$product_id = isset( $product['id'] ) ? $product['id'] : '';

				$constant = strtoupper( str_replace( '-', '_', $product_id ) );
				$constant = 'BSF_' . $constant . '_CHECK_UPDATES';

				if ( defined( $constant ) && ( constant( $constant ) === 'false' || constant( $constant ) === false ) ) {
					continue;
				}

				$remote       = isset( $product['remote'] ) ? $product['remote'] : '';
				$local        = isset( $product['version'] ) ? $product['version'] : '';
				$version_beta = isset( $product['version_beta'] ) ? $product['version_beta'] : $remote;

				if ( $this->use_beta_version( $product_id ) ) {
					$remote = $version_beta;
				}

				if ( version_compare( $remote, $local, '>' ) ) {
					array_push( $update_required, $product );
				}
			}
			
			return $update_required;
		}

		public function _maybe_force_check_bsf_product_updates () {

			if ( $this->time_since_last_versioncheck( 2 ) ) {
				global $ultimate_referer;
				$ultimate_referer = 'on-transient-delete-2-hours';
				bsf_check_product_update();
				update_option( 'bsf_local_transient', (string) current_time( 'timestamp' ) );
				set_transient( 'bsf_check_product_updates', true, 2 * 24 * 60 * 60 );
			}

		}

		public function time_since_last_versioncheck( $hours_completed ) {

			$seconds = $hours_completed * 3600;
			$status  = false;

			$bsf_local_transient = (int) get_option( 'bsf_local_transient', false );
			
			if ( $bsf_local_transient != false ) {

				// Find seconds passed since the last timestamp update (i.e. last request made)
				$elapsed_seconds = (int) current_time( 'timestamp' ) - $bsf_local_transient;

				// IF time is more than the required seconds allow a new HTTP request.
				if ( $elapsed_seconds > $seconds ) {
					$status = true;
				}

			} else {

				// If timestamp is not yet set - allow the HTTP request.
				$status = true;
			}

			return $status;
		}

		public function use_beta_version( $product_id ) {

			$product = get_brainstorm_product( $product_id );
			$stable  = isset( $product['remote'] ) ? $product['remote'] : '';
			$beta    = isset( $product['version_beta'] ) ? $product['version_beta'] : '';

			// If beta version is not set, return
			if ( $beta == '' ) {
				return false;
			}

			if ( version_compare( $stable, $beta, '<' ) &&
			     self::bsf_allow_beta_updates( $product_id )
			) {

				return true;
			}

			return false;
		}

		public function beta_version_normalized( $beta ) {
			$beta_explode = explode( '-', $beta );

			$version = $beta_explode[0] . '.' . str_replace( 'beta', '', $beta_explode[1] );

			return $version;
		}

		public static function bsf_allow_beta_updates( $product_id ) {
			return apply_filters( "bsf_allow_beta_updates_{$product_id}", false );
		}

		public function bsf_get_plugin_slug( $template ) {
			$slug = explode( '/', $template );

			if ( isset( $slug[0] ) ) {
				return $slug[0];
			}

			return '';
		}

		public function bsf_update_display_license_link() {

			$brainstorm_all_products = $this->brainstorm_all_products();

			foreach ( $brainstorm_all_products as $key => $product ) {

				if( isset( $product['id'] ) ) {
					$id = $product['id'];

					if ( BSF_License_Manager::bsf_is_active_license( $id ) == false ) {
						if ( isset( $product['template'] ) && $product['template'] != '' ) {
							$template = $product['template'];
						} elseif ( isset( $product['init'] ) && $product['init'] != '' ) {
							$template = $product['init'];
						}

						if ( is_plugin_active( $template ) ) {
							add_action( "in_plugin_update_message-$template", array(
								$this,
								'bsf_add_registration_message'
							), 9, 2 );
						}
					}
				}
			}

		}

		public function brainstorm_all_products() {

			$brainstrom_products         = get_option( 'brainstrom_products', array() );
			$brainstrom_products_plugins = isset( $brainstrom_products['plugins'] ) ? $brainstrom_products['plugins'] : array();
			$brainstrom_products_themes  = isset( $brainstrom_products['themes'] ) ? $brainstrom_products['themes'] : array();
			$brainstrom_bundled_products = get_option( 'brainstrom_bundled_products', array() );

			$bundled = array();

			foreach ( $brainstrom_bundled_products as $parent => $children ) {

				foreach ( $children as $key => $product ) {
					$bundled[ $product->id ] = (array) $product;
				}

			}

			// array of all the products
			$all_products = $brainstrom_products_plugins + $brainstrom_products_themes + $bundled;

			return $all_products;
		}

		public function bsf_add_registration_message( $plugin_data, $response ) {

			$plugin_init = isset( $plugin_data['plugin'] ) ? $plugin_data['plugin'] : '';

			if ( '' !== $plugin_init ) {
				$product_id        = brainstrom_product_id_by_init( $plugin_init );
				$bundled           = self::bsf_is_product_bundled( $plugin_init, 'init' );
				$registration_page = bsf_registration_page_url( '', $product_id );
			} else {
				$plugin_name 		= isset( $plugin_data['name'] ) ? $plugin_data['name'] : '';
				$product_id  		= brainstrom_product_id_by_name( $plugin_name );
				$bundled           	= self::bsf_is_product_bundled( $plugin_name, 'name' );
				$registration_page 	= bsf_registration_page_url( '', $product_id );
			}

			if ( ! empty( $bundled ) ) {
				$parent_id   = $bundled[0];
				$registration_page 	= bsf_registration_page_url( '', $parent_id );
				$parent_name = apply_filters( "bsf_product_name_{$parent_id}", brainstrom_product_name( $parent_id ) );
				printf( __( ' <br>This plugin is came bundled with the <i>%1$s</i>. For receiving updates, you need to register license of <i>%2$s</i> <a href="%3$s">here</a>.' ), $parent_name, $parent_name, $registration_page );
			} else {
				printf( __( ' <i>Click <a href="%1$s">here</a> to activate your license.</i>' ), $registration_page );
			}

		}

		public function bsf_change_no_package_message( $reply, $package, $current ) {

			// Read atts into separate veriables so that easy to reference below.
			$strings = $current->strings;

			if ( isset( $current->skin->plugin_info ) ) {
				$plugin_info = $current->skin->plugin_info;

				$plugin_name 			= $plugin_info['Name'];
				$product_id  			= brainstrom_product_id_by_name( $plugin_name );
				$plugin_name 			= apply_filters( "bsf_product_name_{$product_id}", $plugin_name );
				$is_bundled  			= self::bsf_is_product_bundled( $plugin_name, 'name' );
				$registration_page_url 	= bsf_registration_page_url( '', $product_id );

				if ( empty( $is_bundled ) ) {
					if ( strcasecmp( $plugin_info['Author'], "Brainstorm Force" ) !== 0 ) {

						// This is not our product, let's leave.
						return $reply;
					}
				} else {
					$is_bundled  = isset( $is_bundled[0] ) ? $is_bundled[0] : $plugin_name;
					$plugin_name = apply_filters( "bsf_product_name_{$is_bundled}", brainstrom_product_name( $is_bundled ) );
					$registration_page_url = bsf_registration_page_url( '', $is_bundled );
				}

				$strings['downloading_package'] = 'Downloading the package...';

				if ( $plugin_info['Author'] == 'Brainstorm Force' ) {
					$strings['no_package'] = sprintf(
						__( 'Click <a target="_blank" href="%1s">here</a> to activate license of <i>%2s</i> to receive automatic updates.' ),
						$registration_page_url,
						$plugin_name
					);
				} elseif ( $is_bundled !== '' ) {
					$strings['no_package'] = sprintf(
						__( 'This plugin is came bundled with the <i>%1s</i>. For receiving updates, you need to register license of <i>%2s</i> <a target="_blank" href="%3s">here</a>.' ),
						$plugin_name,
						$plugin_name,
						$registration_page_url
					);
				}

			} elseif ( isset( $current->skin->theme_info ) ) {
				$theme_info   = $current->skin->theme_info;
				$theme_author = $theme_info->get( 'Author' );
				$theme_name   = $theme_info->get( 'Name' );
				$product_id   = brainstrom_product_id_by_name( $theme_name );

				if ( $theme_author == 'Brainstorm Force' ) {
					$strings['downloading_package'] = 'Downloading the package...';
					$strings['no_package']          = sprintf(
						__( 'Click <a target="_blank" href="%1s">here</a> to activate license of <i>%2s</i> to receive automatic updates.' ),
						bsf_registration_page_url( '', $product_id ),
						$theme_name
					);
				}
			}

			// restore the strings back to WP_Upgrader
			$current->strings = $strings;

			// We are not changing teh return parameter.
			return $reply;
		}

	} // class BSF_Update_Manager

	new BSF_Update_Manager();
}