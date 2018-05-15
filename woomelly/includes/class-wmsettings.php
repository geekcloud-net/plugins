<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( !class_exists( 'WMSettings' ) ) {
    /**
     * WMSettings Class.
     */
    class WMSettings {
    	private $app_id = '';
    	private $secret_key = '';
    	private $site_id = '';
    	private $access_token = '';
    	private $expires_in = '';
    	private $refresh_token = '';
    	private $user_id = '';
    	private $permalink = '';
    	private $settings_refresh_token = true;
    	private $settings_refresh_token_email = '';
    	private $settings_notification_sync = true;
    	private $settings_notification_sync_email = '';
    	private $settings_sync_automatic = true;
    	private $settings_sync_automatic_time_before = '12';
    	private $settings_sync_automatic_time = '12';
    	private $settings_licence = 'una-licencia-valida';
    	private $settings_licence_email = '';
    	private $settings_template = '';
    	private $settings_currency_id = '';
    	private $settings_format_template = 'plain_text';
    	private $settings_omit_fields = array();
    	private $settings_extensions = array();
    	private $settings_datetime_order = '';

        /**
         * Default constructor.
         */    	
		public function __construct () {
			$woomelly_settings 		= array();
			$woomelly_settings 		= get_option( 'woomelly_settings' );
			if ( !isset($woomelly_settings['app_id']) ) {
				$this->app_id = '';
			} else {
				$this->app_id = $woomelly_settings['app_id'];
			}
			if ( !isset($woomelly_settings['secret_key']) ) {
				$this->secret_key = '';
			} else {
				$this->secret_key = $woomelly_settings['secret_key'];
			}
			if ( !isset($woomelly_settings['site_id']) ) {
				$this->site_id = '';
			} else {
				$this->site_id = $woomelly_settings['site_id'];
			}
			if ( !isset($woomelly_settings['access_token']) ) {
				$this->access_token = '';
			} else {
				$this->access_token = $woomelly_settings['access_token'];
			}
			if ( !isset($woomelly_settings['expires_in']) ) {
				$this->expires_in = '';
			} else {
				$this->expires_in = $woomelly_settings['expires_in'];
			}
			if ( !isset($woomelly_settings['refresh_token']) ) {
				$this->refresh_token = '';
			} else {
				$this->refresh_token = $woomelly_settings['refresh_token'];
			}
			if ( !isset($woomelly_settings['user_id']) ) {
				$this->user_id = '';
			} else {
				$this->user_id = $woomelly_settings['user_id'];
			}
			if ( !isset($woomelly_settings['permalink']) ) {
				$this->permalink = '';
			} else {
				$this->permalink = $woomelly_settings['permalink'];
			}
			if ( !isset($woomelly_settings['settings_refresh_token']) ) {
				$this->settings_refresh_token = true;
			} else {
				$this->settings_refresh_token = $woomelly_settings['settings_refresh_token'];
			}
			if ( !isset($woomelly_settings['settings_sync_automatic']) ) {
				$this->settings_sync_automatic = true;
			} else {
				$this->settings_sync_automatic = $woomelly_settings['settings_sync_automatic'];
			}
			if ( !isset($woomelly_settings['settings_notification_sync']) ) {
				$this->settings_notification_sync = true;
			} else {
				$this->settings_notification_sync = $woomelly_settings['settings_notification_sync'];
			}
			if ( !isset($woomelly_settings['settings_sync_automatic_time']) ) {
				$this->settings_sync_automatic_time = '12';
			} else {
				$this->settings_sync_automatic_time = $woomelly_settings['settings_sync_automatic_time'];
			}
			$this->settings_sync_automatic_time_before = $this->settings_sync_automatic_time;
			if ( !isset($woomelly_settings['settings_refresh_token_email']) ) {
				$this->settings_refresh_token_email = get_option('admin_email');
			} else {
				$this->settings_refresh_token_email = $woomelly_settings['settings_refresh_token_email'];
			}
			if ( !isset($woomelly_settings['settings_notification_sync_email']) ) {
				$this->settings_notification_sync_email = get_option('admin_email');
			} else {
				$this->settings_notification_sync_email = $woomelly_settings['settings_notification_sync_email'];
			}
			if ( !isset($woomelly_settings['settings_licence']) ) {
				$this->settings_licence = 'una-licencia-valida';
			} else {
				$this->settings_licence = $woomelly_settings['settings_licence'];
			}
			if ( !isset($woomelly_settings['settings_licence_email']) ) {
				$this->settings_licence_email = get_option('admin_email');
			} else {
				$this->settings_licence_email = $woomelly_settings['settings_licence_email'];
			}
			if ( !isset($woomelly_settings['settings_template']) ) {
				$this->settings_template = '{name}
==== Basic Characteristics ====
{description}
- Condition: {condition}
- Warranty: {warranty}
{tags}
==== Important information before making your purchase ====
In case you have any inconvenience, doubt, delay, etc. With your order, we invite you to contact us and we will gladly solve it immediately. We are always to help you ;)

Please take into account our Customer Service Hours:
- Monday to Friday from 10am to 6pm.
- Saturdays from 10am to 3pm.

WOOMELLY SHOP
We are a Mercadolider specialist in the electrical trade. We have more than 10,000 specific sales in Mercadolibre. If you need any additional product, we invite you to search our wide variety of products.';
			} else {
				$this->settings_template = $woomelly_settings['settings_template'];
			}			
			if ( !isset($woomelly_settings['settings_currency_id']) ) {
				$this->settings_currency_id = '';
			} else {
				$this->settings_currency_id = $woomelly_settings['settings_currency_id'];
			}
			if ( !isset($woomelly_settings['settings_datetime_order']) ) {
				$this->settings_datetime_order = '';
			} else {
				$this->settings_datetime_order = $woomelly_settings['settings_datetime_order'];
			}		
			if ( !isset($woomelly_settings['settings_format_template']) ) {
				$this->settings_format_template = 'plain_text';
			} else {
				$this->settings_format_template = $woomelly_settings['settings_format_template'];
			}
			if ( !isset($woomelly_settings['settings_omit_fields']) ) {
				$this->settings_omit_fields = array(false, false, false, false, false);
			} else {
				$this->settings_omit_fields = $woomelly_settings['settings_omit_fields'];
			}
			if ( !isset($woomelly_settings['settings_extensions']) ) {				
				$this->settings_extensions = array();
				foreach ( $this->get_extension_available() as $value) {
					$this->settings_extensions[$value] = false;
				}
			} else {
				$this->settings_extensions = $woomelly_settings['settings_extensions'];
			}
		} //End __construct()

		public function __clone () {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), Woomelly()->get_version() );
		} //End __clone()

		public function __wakeup () {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), Woomelly()->get_version() );
		} //End __wakeup()

		/**
		 * get_app_id.
		 *
		 * @return string
		 */
		public function get_app_id () {
			return $this->app_id;
		} //End get_app_id()

		/**
		 * set_app_id.
		 *
		 * @return void
		 */
		public function set_app_id ( $app_id ) {
			$this->app_id = trim($app_id);
		} //End set_app_id()

		/**
		 * get_secret_key.
		 *
		 * @return string
		 */
		public function get_secret_key () {
			return $this->secret_key;
		} //End get_secret_key()

		/**
		 * set_secret_key.
		 *
		 * @return void
		 */
		public function set_secret_key ( $secret_key ) {
			$this->secret_key = trim($secret_key);
		} //End set_secret_key()

		/**
		 * get_site_id.
		 *
		 * @return string
		 */
		public function get_site_id () {
			return $this->site_id;
		} //End get_site_id()

		/**
		 * set_site_id.
		 *
		 * @return void
		 */
		public function set_site_id ( $site_id ) {
			$this->site_id = trim($site_id);
		} //End set_site_id()

		/**
		 * get_access_token.
		 *
		 * @return string
		 */
		public function get_access_token () {
			return $this->access_token;
		} //End get_access_token()

		/**
		 * set_access_token.
		 *
		 * @return void
		 */
		public function set_access_token ( $access_token ) {
			$this->access_token = trim($access_token);
		} //End set_access_token()

		/**
		 * get_expires_in.
		 *
		 * @return string
		 */
		public function get_expires_in () {
			return $this->expires_in;
		} //End get_expires_in()

		/**
		 * set_expires_in.
		 *
		 * @return void
		 */
		public function set_expires_in ( $expires_in ) {
			$this->expires_in = trim($expires_in);
		} //End set_expires_in()

		/**
		 * get_refresh_token.
		 *
		 * @return string
		 */
		public function get_refresh_token () {
			return $this->refresh_token;
		} //End get_refresh_token()

		/**
		 * set_refresh_token.
		 *
		 * @return void
		 */
		public function set_refresh_token ( $refresh_token ) {
			$this->refresh_token = trim($refresh_token);
		} //End set_refresh_token()

		/**
		 * get_user_id.
		 *
		 * @return string
		 */
		public function get_user_id () {
			return $this->user_id;
		} //End get_user_id()

		/**
		 * set_user_id.
		 *
		 * @return void
		 */
		public function set_user_id ( $user_id ) {
			$this->user_id = trim($user_id);
		} //End set_user_id()

		/**
		 * get_permalink.
		 *
		 * @return string
		 */
		public function get_permalink () {
			return $this->permalink;
		} //End get_permalink()

		/**
		 * set_permalink.
		 *
		 * @return void
		 */
		public function set_permalink ( $permalink ) {
			$this->permalink = trim($permalink);
		} //End set_permalink()

		/**
		 * get_settings_refresh_token.
		 *
		 * @return bool
		 */
		public function get_settings_refresh_token () {
			return boolval($this->settings_refresh_token);
		} //End get_settings_refresh_token()

		/**
		 * set_settings_refresh_token.
		 *
		 * @return void
		 */
		public function set_settings_refresh_token ( $settings_refresh_token ) {
			$this->settings_refresh_token = boolval($settings_refresh_token);
		} //End set_settings_refresh_token()

		/**
		 * get_settings_refresh_token_email.
		 *
		 * @return string
		 */
		public function get_settings_refresh_token_email () {
			return $this->settings_refresh_token_email;
		} //End get_settings_refresh_token_email()

		/**
		 * set_settings_refresh_token_email.
		 *
		 * @return void
		 */
		public function set_settings_refresh_token_email ( $settings_refresh_token_email ) {
			$this->settings_refresh_token_email = trim($settings_refresh_token_email);
		} //End settings_refresh_token_email()

		/**
		 * get_settings_notification_sync.
		 *
		 * @return bool
		 */
		public function get_settings_notification_sync () {
			return boolval($this->settings_notification_sync);
		} //End get_settings_notification_sync()

		/**
		 * set_settings_notification_sync.
		 *
		 * @return void
		 */
		public function set_settings_notification_sync ( $settings_notification_sync ) {
			$this->settings_notification_sync = boolval($settings_notification_sync);
		} //End set_settings_notification_sync()

		/**
		 * get_settings_notification_sync_email.
		 *
		 * @return string
		 */
		public function get_settings_notification_sync_email () {
			return $this->settings_notification_sync_email;
		} //End get_settings_notification_sync_email()

		/**
		 * set_settings_notification_sync_email.
		 *
		 * @return void
		 */
		public function set_settings_notification_sync_email ( $settings_notification_sync_email ) {
			$this->settings_notification_sync_email = trim($settings_notification_sync_email);
		} //End set_settings_notification_sync_email()

		/**
		 * get_settings_sync_automatic.
		 *
		 * @return bool
		 */
		public function get_settings_sync_automatic () {
			return boolval($this->settings_sync_automatic);
		} //End get_settings_sync_automatic()

		/**
		 * set_settings_sync_automatic.
		 *
		 * @return void
		 */
		public function set_settings_sync_automatic ( $settings_sync_automatic ) {
			$this->settings_sync_automatic = boolval($settings_sync_automatic);
		} //End set_settings_sync_automatic()

		/**
		 * get_settings_sync_automatic_time.
		 *
		 * @return string
		 */		
		public function get_settings_sync_automatic_time () {
			return absint( $this->settings_sync_automatic_time );
		} //End get_settings_sync_automatic_time()

		/**
		 * set_settings_sync_automatic_time.
		 *
		 * @return void
		 */	
		public function set_settings_sync_automatic_time ( $settings_sync_automatic_time ) {
			$this->settings_sync_automatic_time = trim($settings_sync_automatic_time);
		} //End set_settings_sync_automatic_time()

		/**
		 * get_settings_licence.
		 *
		 * @return string
		 */	
		public function get_settings_licence () {
			return $this->settings_licence;
		} //End settings_licence()

		/**
		 * set_settings_licence.
		 *
		 * @return void
		 */	
		public function set_settings_licence ( $settings_licence ) {
			$this->settings_licence = trim($settings_licence);
		} //End set_settings_licence()

		/**
		 * get_settings_licence_email.
		 *
		 * @return string
		 */	
		public function get_settings_licence_email () {
			return $this->settings_licence_email;
		} //End get_settings_licence_email()

		/**
		 * set_settings_licence_email.
		 *
		 * @return void
		 */	
		public function set_settings_licence_email ( $settings_licence_email ) {
			$this->settings_licence_email = trim($settings_licence_email);
		} //End set_settings_licence_email()

		/**
		 * get_settings_template.
		 *
		 * @return string
		 */	
		public function get_settings_template () {
			return $this->settings_template;
		} //End get_settings_template()

		/**
		 * set_settings_template.
		 *
		 * @return void
		 */	
		public function set_settings_template ( $settings_template ) {
			$this->settings_template = trim($settings_template);
		} //End set_settings_template()

		/**
		 * get_settings_currency_id.
		 *
		 * @return string
		 */	
		public function get_settings_currency_id () {
			return $this->settings_currency_id;
		} //End get_settings_currency_id()

		/**
		 * set_settings_currency_id.
		 *
		 * @return void
		 */	
		public function set_settings_currency_id ( $settings_currency_id ) {
			$this->settings_currency_id = trim($settings_currency_id);
		} //End set_settings_currency_id()

		/**
		 * get_settings_datetime_order.
		 *
		 * @return string
		 */	
		public function get_settings_datetime_order () {
			return $this->settings_datetime_order;
		} //End get_settings_datetime_order()

		/**
		 * set_settings_datetime_order.
		 *
		 * @return void
		 */	
		public function set_settings_datetime_order ( $settings_datetime_order = '' ) {
			if ( $settings_datetime_order == "" ) {
				$settings_datetime_order = current_time( 'timestamp', true );
			} else {
				$settings_datetime_order = trim($settings_datetime_order);
			}
			$this->settings_datetime_order = $settings_datetime_order;
		} //End set_settings_datetime_order()

		/**
		 * get_settings_format_template.
		 *
		 * @return string
		 */	
		public function get_settings_format_template () {
			return $this->settings_format_template;
		} //End get_settings_format_template()

		/**
		 * set_settings_format_template.
		 *
		 * @return void
		 */	
		public function set_settings_format_template ( $settings_format_template ) {
			$this->settings_format_template = trim($settings_format_template);
		} //End set_settings_format_template()

		/**
		 * get_settings_omit_fields.
		 *
		 * @return string
		 */	
		public function get_settings_omit_fields () {
			return $this->settings_omit_fields;
		} //End get_settings_omit_fields()

		/**
		 * set_settings_omit_fields.
		 *
		 * @return void
		 */	
		public function set_settings_omit_fields ( $settings_omit_fields ) {
			if ( !is_array($settings_omit_fields) ) {
				unset( $settings_omit_fields );
				$settings_omit_fields = array(false, false, false, false, false);
			}
			$this->settings_omit_fields = $settings_omit_fields;
		} //End set_settings_omit_fields()

		/**
		 * get_settings_extensions.
		 *
		 * @return string
		 */	
		public function get_settings_extensions () {
			return $this->settings_extensions;
		} //End get_settings_extensions()

		/**
		 * set_settings_extensions.
		 *
		 * @return void
		 */	
		public function set_settings_extensions ( $key, $value ) {
			if ( in_array($key, $this->get_extension_available()) ) {
				if ( $value == "ON" ) {
					$this->settings_extensions[$key] = false;
				} else {
					$this->settings_extensions[$key] = true;
				}
			}
		} //End set_settings_extensions()

		/**
		 * get_tags_available.
		 *
		 * @return array | string
		 */	
		public function get_tags_available ( $format = '' ) {
			$tags = array(
				"{name}" => "",
				"{slug}" => "",
				"{featured}" => "",
				"{description}" => "",
				"{short_description}" => "",
				"{price}" => "",
				"{stock_quantity}" => "",
				"{stock_status}" => "",
				"{weight}" => "",
				"{length}" => "",
				"{width}" => "",
				"{height}" => "",
				"{attributes}" => "",
				"{categories}" => "",
				"{tags}" => "",
				"{sku}" => "",
				"{gallery}" => "",
				"{condition}" => "",
				"{warranty}" => ""
			);
			switch ( $format ) {
				case 'string':
					$out = '';
					$comma = false;
					foreach ( $tags as $key => $value ) {
						if ( $key == "{condition}" || $key == "{warranty}" || $key == "{name}" || $key == "{description}" || $key == "{short_description}" || $key == "{price}" || $key == "{categories}" || $key == "{tags}" ) {
							if ( !$comma ) {
								$out .= $key;
								$comma = true;
							} else {
								$out .= ', ' . $key;
							}
						}
					}
					return $out;
					break;
				case 'html':
					# code...
					break;				
				default:
					return $tags;
					break;
			}

			return $tags;
		} //End get_tags_available()

		/**
		 * get_extension_available.
		 *
		 * @return array
		 */	
		public function get_extension_available () {
			$extension = array(
				'receive-data',
				'order',
				'feedback'
			);

			return $extension;
		} //End get_extension_available()		

		/**
		 * save.
		 *
		 * @return void
		 */	
		public function save () {
			$woomelly_settings = array();
			
			$woomelly_settings['app_id'] = $this->app_id;
			$woomelly_settings['secret_key'] = $this->secret_key;
			$woomelly_settings['site_id'] = $this->site_id;
			$woomelly_settings['access_token'] = $this->access_token;
			$woomelly_settings['expires_in'] = $this->expires_in;
			$woomelly_settings['refresh_token'] = $this->refresh_token;
			$woomelly_settings['user_id'] = $this->user_id;
			$woomelly_settings['permalink'] = $this->permalink;
			$woomelly_settings['settings_notification_sync'] = $this->settings_notification_sync;
			$woomelly_settings['settings_notification_sync_email'] = $this->settings_notification_sync_email;
			$woomelly_settings['settings_refresh_token'] = $this->settings_refresh_token;
			$woomelly_settings['settings_refresh_token_email'] = $this->settings_refresh_token_email;
			$woomelly_settings['settings_sync_automatic'] = $this->settings_sync_automatic;
			$woomelly_settings['settings_sync_automatic_time'] = $this->settings_sync_automatic_time;
			$woomelly_settings['settings_licence'] = $this->settings_licence;
			$woomelly_settings['settings_licence_email'] = $this->settings_licence_email;
			$woomelly_settings['settings_template'] = $this->settings_template;
			$woomelly_settings['settings_currency_id'] = $this->settings_currency_id;
			$woomelly_settings['settings_datetime_order'] = $this->settings_datetime_order;
			$woomelly_settings['settings_format_template'] = $this->settings_format_template;
			$woomelly_settings['settings_omit_fields'] = $this->settings_omit_fields;
			$woomelly_settings['settings_extensions'] = $this->settings_extensions;

			update_option( 'woomelly_settings', $woomelly_settings );

			if ( $this->settings_sync_automatic_time_before != $this->settings_sync_automatic_time ) {
            	wp_clear_scheduled_hook( 'woomelly_sync_automatic_event' );
            	wp_schedule_event( time(), 'wm_sync_automatic_time', 'woomelly_sync_automatic_event' );
			}
		} //End save()
    }
}