<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( !class_exists( 'WMeli' ) ) {
	/**
	 * WMeli Class.
	 */
    class WMeli {
		
		/**
		 * is_connect.
		 *
		 * @return bool
		 */
		public static function is_connect () {
			$woomelly_get_settings = array();
			$woomelly_alive = array();
			$woomelly_get_settings = new WMSettings();

			if ( $woomelly_get_settings->get_access_token() == "" || $woomelly_get_settings->get_expires_in() == "" || $woomelly_get_settings->get_refresh_token() == "" || $woomelly_get_settings->get_user_id() == "" ) {
				return false;
			}
			if ( get_transient( '_status_meli_info' ) == false ) {
				$woomelly_alive = WMeli::get_me();
				if ( empty($woomelly_alive) ) {
					return false;
				} else {
					set_transient( '_status_meli_info', 'true', 60 * 60 * 1 );
				}				
			}
			if ( $woomelly_get_settings->get_expires_in() < time() ) {
				return wm_refresh_token( $woomelly_get_settings );
			}

			return true;
		} //End is_connect()

		/**
		 * refresh_token.
		 *
		 * @return bool
		 */
		public static function refresh_token ( $woomelly_get_settings = array() ) {
			$wm_refresh = array();
			if ( empty($woomelly_get_settings) ) {
				$woomelly_get_settings = new WMSettings();
			}
			$meli = new Meli( $woomelly_get_settings->get_app_id(), $woomelly_get_settings->get_secret_key(), $woomelly_get_settings->get_access_token(), $woomelly_get_settings->get_refresh_token() );
			$wm_refresh = $meli->refreshAccessToken();
			
			if ( isset($wm_refresh['httpCode']) && $wm_refresh['httpCode'] == 200 ) {
				$wm_refresh_array = new WMSettings();
				$wm_refresh_array->set_access_token( $wm_refresh['body']->access_token );
				$wm_refresh_array->set_expires_in( time() + $wm_refresh['body']->expires_in );
				$wm_refresh_array->set_refresh_token( $wm_refresh['body']->refresh_token );
				$wm_refresh_array->save();
				return true;
			} else {
				Woomelly()->woomelly_set_log( $wm_refresh, 'error' );
				Woomelly()->woomelly_email_notification( $wm_refresh, 'refresh_token' );
				return false;
			}
		} //End refresh_token()

		/**
		 * get_me.
		 *
		 * @return array
		 */
		public static function get_me ( $more = '' ) {
			$woomelly_get_settings = new WMSettings();
			$_result = array();

			$meli = new Meli( $woomelly_get_settings->get_app_id(), $woomelly_get_settings->get_secret_key() );
			$params = array( 'access_token' => $woomelly_get_settings->get_access_token() );
			if ( $more == "" ) {
				$url = '/users/me';
			} else {
				$url = '/users/me/' . $more;
			}

			$_result = $meli->get( $url, $params );

			if ( isset($_result['httpCode']) && $_result['httpCode'] == 200 && isset($_result['body']) && !empty($_result['body']) ) {
				return $_result['body'];
			} else {
				Woomelly()->woomelly_set_log( $_result, 'error' );
				return array();
			}
		} //End get_me()

		/**
		 * auth_url.
		 *
		 * @return array
		 */
		public static function auth_url () {
			$woomelly_get_settings = new WMSettings();
			$_result = '';

			if ( $woomelly_get_settings->get_site_id() != "" ) {
				$meli = new Meli( $woomelly_get_settings->get_app_id(), $woomelly_get_settings->get_secret_key() );
				$url = admin_url( 'admin.php?page=woomelly-settings' );
				$_result = $meli->getAuthUrl( $url, Meli::$AUTH_URL[$woomelly_get_settings->get_site_id()] );
			}

			return $_result;
		} //End auth_url()

		/**
		 * authorize.
		 *
		 * @return array
		 */
		public static function authorize ( $code ) {
			$woomelly_get_settings = new WMSettings();
			$_result = array();
			$code = trim($code);

			$meli = new Meli( $woomelly_get_settings->get_app_id(), $woomelly_get_settings->get_secret_key() );
			$_result = $meli->authorize( $code, admin_url( 'admin.php?page=woomelly-settings' ) );
			if ( isset($_result['httpCode']) && $_result['httpCode'] == 200 && isset($_result['body']) && !empty($_result['body']) ) {
				return $_result['body'];
			} else {
				Woomelly()->woomelly_set_log( $_result, 'error' );
				return array();
			}
		} //End auth_url()

		/**
		 * get_applications.
		 *
		 * @return array
		 */
		public static function get_applications () {
			$woomelly_get_settings = new WMSettings();
			$_result = array();

			$meli = new Meli( $woomelly_get_settings->get_app_id(), $woomelly_get_settings->get_secret_key() );
			$params = array( 'access_token' => $woomelly_get_settings->get_access_token() );
			$url = '/applications/' . $woomelly_get_settings->get_app_id();

			$_result = $meli->get( $url, $params );
			if ( isset($_result['httpCode']) && $_result['httpCode'] == 200 && isset($_result['body']) && !empty($_result['body']) ) {
				return $_result['body'];
			} else {
				Woomelly()->woomelly_set_log( $_result, 'error' );
				return array();
			}
		} //End get_applications()

		/**
		 * get_sites_id.
		 *
		 * @return array
		 */
		public static function get_sites_id ( $site_id = "" ) {
			$_result = array();
			$woomelly_get_settings = new WMSettings();

			$meli = new Meli( $woomelly_get_settings->get_app_id(), $woomelly_get_settings->get_secret_key() );
			if ( $site_id == "" ) {
				$url = '/sites';
			} else {
				$url = '/sites/' . $site_id;
			}

			$_result = $meli->get( $url );
			if ( isset($_result['httpCode']) && $_result['httpCode'] == 200 && isset($_result['body']) && !empty($_result['body']) ) {
				return $_result['body'];
			} else {
				Woomelly()->woomelly_set_log( $_result, 'error' );
				return array();
			}
		} //End get_sites_id()

		/**
		 * get_category.
		 *
		 * @return array
		 */
		public static function get_category ( $category_id ) {
			$_result = array();
			$woomelly_get_settings = new WMSettings();
			$meli = new Meli( $woomelly_get_settings->get_app_id(), $woomelly_get_settings->get_secret_key() );
			$url = '/categories/' . $category_id;

			$_result = $meli->get( $url );
			if ( isset($_result['httpCode']) && $_result['httpCode'] == 200 && isset($_result['body']) && !empty($_result['body']) ) {
				return $_result['body'];
			} else {
				Woomelly()->woomelly_set_log( $_result, 'error' );
				return array();
			}
		} //End get_category()

		/**
		 * get_categories.
		 *
		 * @return array
		 */
		public static function get_categories () {
			$_result = array();
			$woomelly_get_settings = new WMSettings();

			$meli = new Meli( $woomelly_get_settings->get_app_id(), $woomelly_get_settings->get_secret_key() );

			$site = $woomelly_get_settings->get_site_id();
			$url = '/sites/' . $site . '/categories';
			$_result = $meli->get( $url );
			if ( isset($_result['httpCode']) && $_result['httpCode'] == 200 && isset($_result['body']) && !empty($_result['body']) ) {
				return $_result['body'];
			} else {
				Woomelly()->woomelly_set_log( $_result, 'error' );
				return array();
			}
		} //End get_categories()

		/**
		 * get_attributes.
		 *
		 * @return array
		 */
		public static function get_attributes ( $category_id, $type = 'general' ) {
			$_result = array();
			$attributes = array();
			$woomelly_get_settings = new WMSettings();

			$meli = new Meli( $woomelly_get_settings->get_app_id(), $woomelly_get_settings->get_secret_key() );
			$url = '/categories/' . $category_id . '/attributes';
			$_result = $meli->get( $url );
			if ( isset($_result['httpCode']) && $_result['httpCode'] == 200 && isset($_result['body']) && !empty($_result['body']) ) {				
				switch ($type) {
					case 'general':
						if ( !empty($_result['body']) ) {
							foreach ( $_result['body'] as $value_attribute ) {
								if ( !isset($value_attribute->tags->fixed) ) {
									if ( !isset($value_attribute->tags->inferred) ) {
										if ( !isset($value_attribute->tags->others) ) {
											if ( !isset($value_attribute->tags->read_only) ) {
												$attributes[] = $value_attribute;
											}
										}
									}
								}
							}
							return $attributes;
						}					
						break;
					case 'simple':
						if ( !empty($_result['body']) ) {
							foreach ( $_result['body'] as $value_attribute ) {
								if ( !isset($value_attribute->tags->fixed) ) {
									if ( !isset($value_attribute->tags->inferred) ) {
										if ( !isset($value_attribute->tags->others) ) {
											if ( !isset($value_attribute->tags->read_only) ) {
												if ( !isset($value_attribute->tags->allow_variations) ) {
													$attributes[] = $value_attribute;
												}
											}
										}
									}
								}
							}
							return $attributes;
						}					
						break;

					default:
						return $_result['body'];
						break;
				}
				return $_result['body'];
			} else {
				Woomelly()->woomelly_set_log( $_result, 'error' );
				return array();
			}
		} //End get_attributes()

		/**
		 * get_location_countries.
		 *
		 * @return array
		 */
		public static function get_location_countries () {
			$_result = array();
			$woomelly_get_settings = new WMSettings();

			$meli = new Meli( $woomelly_get_settings->get_app_id(), $woomelly_get_settings->get_secret_key() );
			
			$url = '/classified_locations/countries';
			$_result = $meli->get( $url );
			if ( isset($_result['httpCode']) && $_result['httpCode'] == 200 && isset($_result['body']) && !empty($_result['body']) ) {
				return $_result['body'];
			} else {
				Woomelly()->woomelly_set_log( $_result, 'error' );
				return array();
			}
		} //End get_location_countries()

		/**
		 * get_location_states.
		 *
		 * @return array
		 */
		public static function get_location_states ( $country_id ) {
			$_result = array();
			$woomelly_get_settings = new WMSettings();

			$meli = new Meli( $woomelly_get_settings->get_app_id(), $woomelly_get_settings->get_secret_key() );
			
			$url = '/classified_locations/countries/' . $country_id;
			$_result = $meli->get( $url );
			if ( isset($_result['httpCode']) && $_result['httpCode'] == 200 && isset($_result['body']) && !empty($_result['body']) && isset($_result['body']->states) ) {
				return $_result['body']->states;
			} else {
				Woomelly()->woomelly_set_log( $_result, 'error' );
				return array();
			}
		} //End get_location_states()

		/**
		 * get_location_cities.
		 *
		 * @return array
		 */
		public static function get_location_cities ( $state_id ) {
			$_result = array();
			$woomelly_get_settings = new WMSettings();

			$meli = new Meli( $woomelly_get_settings->get_app_id(), $woomelly_get_settings->get_secret_key() );
			
			$url = '/classified_locations/states/' . $state_id;
			$_result = $meli->get( $url );
			if ( isset($_result['httpCode']) && $_result['httpCode'] == 200 && isset($_result['body']) && !empty($_result['body']) && isset($_result['body']->cities) ) {
				return $_result['body']->cities;
			} else {
				Woomelly()->woomelly_set_log( $_result, 'error' );
				return array();
			}
		} //End get_location_cities()

		/**
		 * get_currencies.
		 *
		 * @return array
		 */
		public static function get_currencies ( $currency_id ) {
			$_result = array();
			$woomelly_get_settings = new WMSettings();

			$meli = new Meli( $woomelly_get_settings->get_app_id(), $woomelly_get_settings->get_secret_key() );
			$url = '/currencies/' . $currency_id;

			$_result = $meli->get( $url );
			if ( isset($_result['httpCode']) && $_result['httpCode'] == 200 && isset($_result['body']) && !empty($_result['body']) ) {
				return $_result['body'];
			} else {
				Woomelly()->woomelly_set_log( $_result, 'error' );
				return array();
			}
		} //End get_currencies()

		/**
		 * get_available_listing_types.
		 *
		 * @return array
		 */
		public static function get_available_listing_types ( $category_id ) {
			$_result = array();
			$woomelly_get_settings = new WMSettings();

			$meli = new Meli( $woomelly_get_settings->get_app_id(), $woomelly_get_settings->get_secret_key() );
			$params = array( 'category_id' => $category_id, 'access_token' => $woomelly_get_settings->get_access_token() );
			$url = '/users/' . $woomelly_get_settings->get_user_id() . '/available_listing_types';
			$_result = $meli->get( $url, $params );
			if ( isset($_result['httpCode']) && $_result['httpCode'] == 200 && isset($_result['body']) && !empty($_result['body']) ) {
				return $_result['body'];
			} else {
				Woomelly()->woomelly_set_log( $_result, 'error' );
				return array();
			}
		} //End get_available_listing_types()

		/**
		 * get_shipping_methods.
		 *
		 * @return array
		 */
		public static function get_shipping_methods ( $shipping_methods_id ) {
			$_result = array();
			$woomelly_get_settings = new WMSettings();

			$meli = new Meli( $woomelly_get_settings->get_app_id(), $woomelly_get_settings->get_secret_key() );
			$url = '/sites/' . $woomelly_get_settings->get_site_id() . '/shipping_methods/' . $shipping_methods_id;

			$_result = $meli->get( $url );
			if ( isset($_result['httpCode']) && $_result['httpCode'] == 200 && isset($_result['body']) && !empty($_result['body']) ) {
				return $_result['body'];
			} else {
				Woomelly()->woomelly_set_log( $_result, 'error' );
				return array();
			}
		} //End get_shipping_methods()

		/**
		 * get_shipping_modes.
		 *
		 * @return array
		 */
		public static function get_shipping_modes ( $category_id ) {
			$_result = array();
			$woomelly_get_settings = new WMSettings();

			$meli = new Meli( $woomelly_get_settings->get_app_id(), $woomelly_get_settings->get_secret_key() );
			$params = array( 'category_id' => $category_id, 'access_token' => $woomelly_get_settings->get_access_token() );
			$url = '/users/' . $woomelly_get_settings->get_user_id() . '/shipping_modes';
			$_result = $meli->get( $url, $params );
			if ( isset($_result['httpCode']) && $_result['httpCode'] == 200 && isset($_result['body']) && !empty($_result['body']) ) {
				return $_result['body'];
			} else {
				Woomelly()->woomelly_set_log( $_result, 'error' );
				return array();
			}
		} //End get_shipping_modes()

		/**
		 * get_shipping.
		 *
		 * @return array
		 */
		public static function get_shipping ( $shipping_id ) {
			$_result = array();
			$woomelly_get_settings = new WMSettings();

			$meli = new Meli( $woomelly_get_settings->get_app_id(), $woomelly_get_settings->get_secret_key() );
			$params = array( 'access_token' => $woomelly_get_settings->get_access_token() );
			$url = '/shipments/' . $shipping_id;
			$_result = $meli->get( $url, $params );
			if ( isset($_result['httpCode']) && $_result['httpCode'] == 200 && isset($_result['body']) && !empty($_result['body']) ) {
				return $_result['body'];
			} else {
				Woomelly()->woomelly_set_log( $_result, 'error' );
				return array();
			}
		} //End get_shipping()

		/**
		 * post_item.
		 *
		 * @return array
		 */
		public static function post_item ( $item, $show_results = false ) {
			$_result = array();
			$woomelly_get_settings = new WMSettings();
			$meli = new Meli( $woomelly_get_settings->get_app_id(), $woomelly_get_settings->get_secret_key() );
			$params = array( 'access_token' => $woomelly_get_settings->get_access_token() );
			$url = '/items';

			$_result = $meli->post( $url, $item, $params );
			if ( isset($_result['httpCode']) && $_result['httpCode'] == 200 && isset($_result['body']) && !empty($_result['body']) ) {
				return $_result['body'];
			} else {
				Woomelly()->woomelly_set_log( $_result, 'error' );
				if ( $show_results == false ) {
					return array();
				} else {
					return $_result;
				}
			}
		} //End post_item()

		/**
		 * unlink.
		 *
		 * @return array
		 */
		public static function unlink ( $show_results = false ) {
			$_result = array();
			$woomelly_get_settings = new WMSettings();
			$meli = new Meli( $woomelly_get_settings->get_app_id(), $woomelly_get_settings->get_secret_key() );
			$params = array( 'access_token' => $woomelly_get_settings->get_access_token() );
			$url = '/users/' . $woomelly_get_settings->get_user_id() . '/applications/' . $woomelly_get_settings->get_app_id();

			$_result = $meli->delete( $url, $params );
			if ( isset($_result['httpCode']) && $_result['httpCode'] == 200 && isset($_result['body']) && !empty($_result['body']) ) {
				return $_result['body'];
			} else {
				Woomelly()->woomelly_set_log( $_result, 'error' );
				if ( $show_results == false ) {
					return array();
				} else {
					return $_result;
				}
			}
		} //End unlink()

		/**
		 * put_item.
		 *
		 * @return array
		 */
		public static function put_item ( $code, $item, $show_results = false ) {
			$_result = array();
			$woomelly_get_settings = new WMSettings();
			$meli = new Meli( $woomelly_get_settings->get_app_id(), $woomelly_get_settings->get_secret_key() );
			$params = array( 'access_token' => $woomelly_get_settings->get_access_token() );
			$url = '/items/' . $code;

			$_result = $meli->put( $url, $item, $params );
			if ( isset($_result['httpCode']) && $_result['httpCode'] == 200 && isset($_result['body']) && !empty($_result['body']) ) {
				return $_result['body'];
			} else {
				Woomelly()->woomelly_set_log( $_result, 'error' );
				if ( $show_results == false ) {
					return array();
				} else {
					return $_result;
				}
			}
		} //End put_item()

		/**
		 * relist_item.
		 *
		 * @return array
		 */
		public static function relist_item ( $code, $item, $show_results = false ) {
			$_result = array();
			$woomelly_get_settings = new WMSettings();
			$meli = new Meli( $woomelly_get_settings->get_app_id(), $woomelly_get_settings->get_secret_key() );
			$params = array( 'access_token' => $woomelly_get_settings->get_access_token() );
			$url = '/items/' . $code . '/relist';

			$_result = $meli->post( $url, $item, $params );
			if ( isset($_result['httpCode']) && $_result['httpCode'] == 200 && isset($_result['body']) && !empty($_result['body']) ) {
				return $_result['body'];
			} else {
				Woomelly()->woomelly_set_log( $_result, 'error' );
				if ( $show_results == false ) {
					return array();
				} else {
					return $_result;
				}
			}
		} //End relist_item()

		/**
		 * get_resource.
		 *
		 * @return array
		 */
		public static function get_resource ( $resource ) {
			$_result = array();
			$woomelly_get_settings = new WMSettings();

			$meli = new Meli( $woomelly_get_settings->get_app_id(), $woomelly_get_settings->get_secret_key() );
			$params = array( 'access_token' => $woomelly_get_settings->get_access_token() );
			$_result = $meli->get( $resource, $params );
			if ( isset($_result['httpCode']) && $_result['httpCode'] == 200 && isset($_result['body']) && !empty($_result['body']) ) {
				return $_result['body'];
			} else {
				Woomelly()->woomelly_set_log( $_result, 'error' );
				return array();
			}
		} //End get_resource()
    }
}