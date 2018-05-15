<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( !class_exists( 'Woomelly' ) ) {
	final class Woomelly {
		private static $_instance = null;
		public $_version;
		public $_token;
		public $_domain;
		public $_webhook;
		public $_pages;
		public $_pages_product;
		public $_file;
		public $_dir;
		public $_template_dir;
		public $_assets_dir;
		public $_assets_url;
		public $_webhook_url;

		/**
		 * Default constructor.
		 */
		public function __construct ( $file = __FILE__, $version ) {
			$this->_version 			= $version;
			$this->_token 				= 'woomelly';
			$this->_domain 				= 'woomelly';
			$this->_webhook 			= 'wm-api=mercadolibre';
			$this->_pages 				= array( 'woomelly-menu', 'woomelly-settings', 'woomelly-connection', 'woomelly-notifications', 'woomelly-extensions', 'woomelly-license', 'woomelly-templatesync' );
			$this->_pages_product		= array( 'post.php', 'post-new.php' );
			$this->_file 				= $file;
			$this->_dir 				= dirname( __DIR__ );
			$this->_template_dir 		= trailingslashit( $this->_dir ) . 'template';
			$this->_assets_dir 			= trailingslashit( $this->_dir ) . 'assets';
			$this->_assets_url 			= esc_url( trailingslashit( plugins_url( '/assets', $this->_file ) ) );
			$this->_webhook_url			= get_home_url() . '/?' . $this->_webhook;

			$this->define_constants();
			$this->includes();
			$this->woomelly_load_plugin_textdomain();
			$this->init_hooks();
		} //End __construct()

		/**
		 * Gets the main WC_Checkout Instance.
		 *
		 * @return Woomelly Main instance
		 */
		public static function instance ( $file = __FILE__, $version ) {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self( $file, $version );
			}
			return self::$_instance;
		} //End instance()

		/**
		 * When the object is cloned, make sure meta is duplicated correctly.
		 */
		public function __clone () {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
		} //End __clone()

		/**
		 * Re-run the constructor with the object ID.
		 */
		public function __wakeup () {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
		} //End __wakeup()	

		/**
		 * define.
		 *
		 * @return void
		 */
		private function define ( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		} //End define()

		/**
		 * define_constants.
		 *
		 * @return void
		 */
		private function define_constants () {
			$upload_dir = wp_upload_dir( null, false );
			$this->define( 'WM_DEBUG_LOG', $upload_dir['basedir'] . '/woomelly_debug.log' );
			$this->define( 'WM_ERROR_LOG', $upload_dir['basedir'] . '/woomelly_error.log' );
			$this->define( 'WM_LAST_SYNC_LOG', $upload_dir['basedir'] . '/woomelly_last_sync.log' );
			$this->define( 'WM_SYNC_LOG', $upload_dir['basedir'] . '/woomelly_sync.log' );
			$this->define( 'WM_NOTIFICATION_LOG', $upload_dir['basedir'] . '/woomelly_notification.log' );
			$this->define( 'WM_DEBUG', true );
		} //End define_constants()

		/**
		 * includes.
		 *
		 * @return void
		 */
		public function includes () {
			if ( ! function_exists( 'is_plugin_active' ) ) {
				require_once ( ABSPATH . 'wp-admin/includes/plugin.php' );
			}
			if( !class_exists('WP_List_Table') ) {
			    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
			}
			include_once WM_PLUGIN_FILE . '/includes/admin/class-wm-admin-menu.php';
			include_once WM_PLUGIN_FILE . '/includes/admin/class-wm-admin-dashboard.php';
			include_once WM_PLUGIN_FILE . '/includes/admin/class-wm-admin-settings.php';
			include_once WM_PLUGIN_FILE . '/includes/admin/class-wm-admin-templatesync.php';
			include_once WM_PLUGIN_FILE . '/includes/admin/class-wm-admin-extensions.php';
			include_once WM_PLUGIN_FILE . '/includes/admin/class-wm-admin-connection.php';
			include_once WM_PLUGIN_FILE . '/includes/admin/class-wm-admin-license.php';
			include_once WM_PLUGIN_FILE . '/includes/admin/class-wm-admin-assets.php';
			include_once WM_PLUGIN_FILE . '/includes/class-wminstall.php';
			include_once WM_PLUGIN_FILE . '/includes/class-wmnotification.php';
			include_once WM_PLUGIN_FILE . '/includes/class-meli.php';
			include_once WM_PLUGIN_FILE . '/includes/class-wmeli.php';
			include_once WM_PLUGIN_FILE . '/includes/class-wmsettings.php';
			include_once WM_PLUGIN_FILE . '/includes/class-wmproduct.php';
			include_once WM_PLUGIN_FILE . '/includes/class-wmorder.php';
			include_once WM_PLUGIN_FILE . '/includes/class-wmtemplatesync.php';
			include_once WM_PLUGIN_FILE . '/includes/class-wmlisttable.php';
			include_once WM_PLUGIN_FILE . '/includes/wm-core-functions.php';
		} //End includes()

		/**
		 * init_hooks.
		 *
		 * @return void
		 */
		private function init_hooks () {
			add_action( 'wp_ajax_woomelly_do_extension', array( $this, 'woomelly_do_extension' ), 10 );
			add_action( 'wp_ajax_woomelly_do_unlink', array( $this, 'woomelly_do_unlink' ), 10 );
			add_action( 'wp_ajax_woomelly_do_sync_product', array( $this, 'woomelly_do_sync_product_function' ), 10 );
			add_action( 'wp_ajax_woomelly_do_reset_product', array( $this, 'woomelly_do_reset_product_function' ), 10 );
		} //End init_hooks()

		/**
		 * get_version.
		 *
		 * @return string
		 */
		public function get_version () {
			return $this->_version;
		} //End get_version()

		/**
		 * get_token.
		 *
		 * @return string
		 */
		public function get_token () {
			return $this->_token;
		} //End get_token()		

		/**
		 * get_domain.
		 *
		 * @return string
		 */
		public function get_domain () {
			return $this->_domain;
		} //End get_domain()

		/**
		 * get_webhook.
		 *
		 * @return string
		 */
		public function get_webhook () {
			return $this->_webhook;
		} //End get_webhook()

		/**
		 * get_pages.
		 *
		 * @return array
		 */
		public function get_pages () {
			return $this->_pages;
		} //End get_pages()

		/**
		 * get_pages_product.
		 *
		 * @return array
		 */
		public function get_pages_product () {
			return $this->_pages_product;
		} //End get_pages_product()

		/**
		 * get_file.
		 *
		 * @return string
		 */
		public function get_file () {
			return $this->_file;
		} //End get_file()

		/**
		 * get_dir.
		 *
		 * @return string
		 */
		public function get_dir () {
			return $this->_dir;
		} //End get_dir()

		/**
		 * get_template_dir.
		 *
		 * @return string
		 */
		public function get_template_dir () {
			return $this->_template_dir;
		} //End get_template_dir()

		/**
		 * get_assets_dir.
		 *
		 * @return string
		 */
		public function get_assets_dir () {
			return $this->_assets_dir;
		} //End get_assets_dir()

		/**
		 * get_assets_url.
		 *
		 * @return string
		 */
		public function get_assets_url () {
			return $this->_assets_url;
		} //End get_assets_url()

		/**
		 * get_webhook_url.
		 *
		 * @return string
		 */
		public function get_webhook_url () {
			return $this->_webhook_url;
		} //End get_webhook_url()

		/**
		 * woomelly_load_plugin_textdomain.
		 *
		 * @return void
		 */
		public function woomelly_load_plugin_textdomain () {
		    $domain = $this->_domain;
		    $locale = apply_filters( 'woomelly_plugin_locale', get_locale(), $domain );
		    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
		    load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->_file ) ) . '/languages/' );
		} //End woomelly_load_plugin_textdomain()

		/**
		 * woomelly_status_license.
		 *
		 * @return bool
		 */
		public function woomelly_status_license () {
			$_result = false;

			$settings_licence = new WMSettings();
			$_settings_licence = $settings_licence->get_settings_licence();
			if ( $_settings_licence != "" ) {					
				$_result = true;					
			}

			return $_result;
		} //End woomelly_load_plugin_textdomain()

		/**
		 * woomelly_get_form_license.
		 *
		 * @return string
		 */
		public function woomelly_get_form_license () {
			$settings_licence = array();

			if ( isset($_POST['wm_licence_page_submit']) ) {
				if ( isset($_POST['wm_licence_item_purchase']) && $_POST['wm_licence_item_purchase'] != "" ) {
					$save_settings = new WMSettings();
					$save_settings->set_settings_licence( $_POST['wm_licence_item_purchase'] );
					$save_settings->set_settings_licence_email( ( (isset($_POST['wm_licence_email']))? $_POST['wm_licence_email'] : '' ) );
					$save_settings->save();
					$error = false;
				}
			}

			$settings_licence = new WMSettings();
			$_settings_licence = $settings_licence->get_settings_licence();
			$_settings_licence_email = $settings_licence->get_settings_licence_email();			
			
			$form = '
				<div id="wm_licence_page_container">
    				<div id="wm_licence_page_container_msg" class="uk-alert-primary wm_licence_page_container" uk-alert>
        				<div>'.__("Enter your license code.", "woomelly").'</div>
    				</div>
				</div>
				<form class="uk-form-horizontal" form action="" method="post">
    				<div class="uk-margin">
        				<label class="uk-form-label" for="wm_licence_item_purchase">'.sprintf(__("Your purchase code %s", "woomelly"), '<span style="color: red;">*</span>').'</label>
        				<div class="uk-inline" style="width: 70%;">
            				<span class="uk-form-icon" uk-icon="icon: lock"></span>
            				<input type="password" name="wm_licence_item_purchase" class="uk-input" id="wm_licence_item_purchase" value="'.$_settings_licence.'" placeholder="'.__("Your Purchase license...", "woomelly").'" />
        				</div>
    				</div>
    				<div class="uk-margin">
        				<label class="uk-form-label" for="wm_licence_email">'.__("Your Email", "woomelly").'</label>
        				<div class="uk-inline" style="width: 70%;">
            				<span class="uk-form-icon" uk-icon="icon: mail"></span>
            				<input name="wm_licence_email" class="uk-input" id="wm_licence_email" value="'.$_settings_licence_email.'" type="text" placeholder="'.__("Your email...", "woomelly").'" />
        				</div>
        				<span class="uk-margin-small-right" uk-icon="icon: question" title="'.__("This email will be used for possible updates and corrections of Woomelly.", "woomelly").'" uk-tooltip></span>
    				</div>
    				<div class="uk-margin">
        				<input name="wm_licence_page_submit" type="submit" class="uk-button uk-button-primary" value="'.__("Save changes", "woomelly").'" />
    				</div>
				</form>';
				
			return $form;
		}

		/**
		 * woomelly_status.
		 *
		 * @return array
		 */
		public function woomelly_status () {
			$form = $this->woomelly_get_form_license();
			if ( $this->woomelly_status_license() ) {
				return array('result' => true, 'form' => '' );
			} else {				
				return array('result' => false, 'form' => $form );
			}			
		} //End woomelly_status()

		/**
		 * woomelly_set_log.
		 *
		 * @return void
		 */
		public function woomelly_set_log ( $message, $type = "debug" ) {
			if ( $message != "" ) {
				switch ( $type ) {
					case 'debug':
						if ( WM_DEBUG == true ) {
							if ( is_array($message) || is_object($message) ) {
								$message = serialize( $message );
							}
							file_put_contents( WM_DEBUG_LOG, "\n[".date_format(date_create(), 'Y-m-d H:i:s')."] ".$message, FILE_APPEND );
						}
					break;
					case 'error':
						if ( is_array($message) || is_object($message) ) {
							$message = serialize( $message );
						}
						file_put_contents( WM_ERROR_LOG, "\n[".date_format(date_create(), 'Y-m-d H:i:s')."] ".$message, FILE_APPEND );
					break;
					case 'last_sync':
						if ( is_array($message) || is_object($message) ) {
							$message = serialize( $message );
						}
						file_put_contents( WM_LAST_SYNC_LOG, $message, FILE_APPEND );
					break;
					case 'sync':
						if ( is_array($message) || is_object($message) ) {
							$message = serialize( $message );
						}
						file_put_contents( WM_SYNC_LOG, "\n[".date_format(date_create(), 'Y-m-d H:i:s')."] ".$message, FILE_APPEND );
					break;
					case 'notification':
						if ( is_array($message) || is_object($message) ) {
							$message = serialize( $message );
						}
						file_put_contents( WM_NOTIFICATION_LOG, "\n[".date_format(date_create(), 'Y-m-d H:i:s')."] ".$message, FILE_APPEND );
					break;
				}
			}
		} //End woomelly_set_log()

		/**
		 * woomelly_email_notification.
		 *
		 * @return void
		 */
		public function woomelly_email_notification ( $message, $type ) {
			$woomelly_get_settings = array();
			
			if ( $message != "" ) {
				switch ( $type ) {
					case 'refresh_token':
						$woomelly_get_settings = new WMSettings();
						if ( $woomelly_get_settings->get_settings_refresh_token() == true && $woomelly_get_settings->get_settings_refresh_token_email() != "" ) {
							if ( is_array($message) || is_object($message) ) {
								$message = serialize($message);
							}
							$subject = sprintf(__("%s - Woomelly Notification Refresh Token", "woomelly"), get_bloginfo( "name" ));
							$body = '								
							<table width="100%" cellpadding="0" cellspacing="0" style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
								<tr style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
									<td class="content-block" style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 20px;" valign="top">
										<p class="aligncenter" style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 20px; color: #000; line-height: 1.2em; font-weight: 400; text-align: center;" align="center">
											<strong>'.__("Sorry, we have presented the following error when refreshing the Mercadolibre token", "woomelly").':</strong>
										</p>
										<p class="aligncenter" style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; color: #000; line-height: 1.2em; font-weight: 400; text-align: center;" align="center">
											'.$message.'
										</p>
									</td>
								</tr>
							</table>';
					        $headers = array('Content-Type: text/html; charset=UTF-8');
					        $to = $woomelly_get_settings->get_settings_refresh_token_email();
					        wp_mail( $to, $subject, $body, $headers );
						}
					break;
					case 'summary_sync':
						$woomelly_get_settings = new WMSettings();
						if ( $woomelly_get_settings->get_settings_notification_sync() == true && $woomelly_get_settings->get_settings_notification_sync_email() != "" ) {
							if ( is_array($message) || is_object($message) ) {
								$message = serialize($message);
							}
							$subject = sprintf(__("%s - Woomelly Synchronization Summary", "woomelly"), get_bloginfo( "name" ));
							$body = '								
							<table width="100%" cellpadding="0" cellspacing="0" style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
								<tr style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
									<td class="content-block" style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; margin: 0; padding: 0 0 20px;" valign="top">
										
										<p class="aligncenter" style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 20px; color: #000; line-height: 1.2em; font-weight: 400; text-align: center;" align="center">
											<strong>'.__("A synchronization has been made on your site", "woomelly").':</strong>
										</p>
										<p class="aligncenter" style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; color: #000; line-height: 1.2em; font-weight: 400; text-align: center;" align="center">
											'.$message.'
										</p>
									</td>
								</tr>
							</table>';
					        $headers = array('Content-Type: text/html; charset=UTF-8');
					        $to = $woomelly_get_settings->get_settings_notification_sync_email();
					        wp_mail( $to, $subject, $body, $headers );
						}
					break;
				}
			}
		} //End woomelly_email_notification()

		/**
		 * woomelly_is_connect.
		 *
		 * @return bool
		 */
		public function woomelly_is_connect () {
			return WMeli::is_connect();
		} //End woomelly_is_connect()

		/**
		 * woomelly_do_extension.
		 *
		 * @return string
		 */
		public function woomelly_do_extension () {
			if ( isset($_POST['wmext']) && $_POST['wmext']!="" && isset($_POST['wmvalue']) && $_POST['wmvalue']!="" ) {
				$woomelly_settings = new WMSettings();
				$ext = trim($_POST['wmext']);
				$ext_value = trim($_POST['wmvalue']);
				$woomelly_settings->set_settings_extensions($ext, $ext_value);
				if ( $woomelly_settings->get_settings_datetime_order() == "" ) {
					$woomelly_settings->set_settings_datetime_order();
					/*$t = microtime(true);
					$micro = sprintf("%06d",($t - floor($t)) * 1000000);
					$d = new DateTime( date('Y-m-d H:i:s.'.$micro, $t) );
					WMNotification::set_woomelly_settings_notification( 'last_notification_order', $d->format("Y-m-d H:i:s.u") );*/
				}
				$woomelly_settings->save();
				echo "success";
			} else {
				echo __("You must select an extension.", "woomelly");
			}

			wp_die();
		} //End woomelly_do_extension()

		/**
		 * woomelly_do_unlink.
		 *
		 * @return string
		 */
		public function woomelly_do_unlink () {
			$revoke_permiso_app = array();
			$woomelly_settings = new WMSettings();
		    $revoke_permiso_app = WMeli::unlink();
		    
		    //if ( !empty($revoke_permiso_app) ) {
				$woomelly_settings->set_access_token( '' );
				$woomelly_settings->set_expires_in( '' );
				$woomelly_settings->set_refresh_token( '' );
				$woomelly_settings->set_user_id( '' );
				$woomelly_settings->set_permalink( '' );
				$woomelly_settings->save();
				echo "success";		    	
		    /*} else {
		    	echo __("Disculpe, no se pudo desvincular este sitio correctamente.", "woomelly");
		    }*/
			
			wp_die();
		} //End woomelly_do_unlink()

		/**
		 * woomelly_do_reset_product_function.
		 *
		 * @return string
		 */
		public function woomelly_do_reset_product_function () {
			$wm_id = 0;
			$message_finish = '';
			if ( isset($_POST['wm_id']) && isset($_POST['action']) && $_POST['action'] == 'woomelly_do_reset_product' ) {
				$wm_id = absint($_POST['wm_id']);
				if ( $wm_id > 0 ) {
					WMProduct::reset( $wm_id );
					$message_finish = 'ok:::' . __("Product successfully unlinked.", "woomelly");
				} else {
					$message_finish = 'nook:::<span style="background: #fef4f6; color: #f0506e;">'.__("Problems when obtaining the product data.", "woomelly").' <span class="dashicons dashicons-no-alt" style="color: red; vertical-align: middle;"></span></span>';
				}
			}
			
			echo $message_finish;
			wp_die();			
		} //End woomelly_do_reset_product_function()

		/**
		 * woomelly_do_sync_product_function.
		 *
		 * @return array
		 */
		public function woomelly_do_sync_product_function () {
			$wm_id = 0;
			$result = 'nook:::<span style="background: #fef4f6; color: #f0506e;">'.__("Sorry, but there is no possibility of obtaining the product code.", "woomelly").' <span class="dashicons dashicons-no-alt" style="color: red; vertical-align: middle;"></span></span>';
			if ( isset($_POST['wm_id']) && isset($_POST['action']) && $_POST['action'] == 'woomelly_do_sync_product' ) {
				$wm_id = absint($_POST['wm_id']);
				$wm_type = ( (isset($_POST['wm_type']) && $_POST['wm_type']!="")? trim($_POST['wm_type']) : 'woomelly_automatic' );
				if ( $wm_id > 0 ) {
					$result = $this->woomelly_sync_product_to_meli( $wm_id, $wm_type );
				}
			}		
			echo $result;
			wp_die();
		} //End woomelly_do_sync_product_function()

		/**
		 * woomelly_do_sync_automatic_product_function.
		 *
		 * @return array
		 */
		public function woomelly_do_sync_automatic_product_function ( $product_id ) {
			$result = 'error';
			$wm_id = 0;
			$wm_id = absint($product_id);
			$wm_type = 'woomelly_automatic';
			
			if ( $wm_id > 0 ) {
				$result = $this->woomelly_sync_product_to_meli( $wm_id, $wm_type );
			}

			return $result;
		} //End woomelly_do_sync_automatic_product_function()


		/**
		 * woomelly_sync_product_to_meli.
		 *
		 * @return array | string
		 */
		public function woomelly_sync_product_to_meli ( $woo_id, $woo_type ) {
			$result = '';
			$result_meli = array();
			$result_meli_description = array();
			$item_meli = array();
			$woomelly_get_settings = array();
			$all_wm_variations = array();
			$wm_variations = array();
			$available_variations = array();			
			$_product = wc_get_product( $woo_id );
			$woomelly_status_meli_field = "";
			$stock_status = wc_get_product_stock_status_options();
			$message_finish = '';
			$variable_to_simple = false;
			$wm_product = new WMProduct( $woo_id );

			if ( is_object($_product) && $wm_product->get_id() > 0 ) {
				$wm_template_sync = new WMTemplateSync( $wm_product->get_woomelly_template_sync_id() );
				if ( $wm_template_sync->get_id()>0 && $_product->get_status()=='publish') {
					if ( $wm_product->get_woomelly_sync_status_field() == "1" && $wm_template_sync->get_woomelly_status_field() != 'inactive' ) {
						/*if ( get_transient( '_status_woomelly_sync_info' ) == false ) {
							set_transient( '_status_woomelly_sync_info', 'true', 5 );
						}*/
						$woomelly_get_settings = new WMSettings();
						$woomelly_description_field = $wm_product->get_woomelly_description_meli_field();
						$woomelly_status_meli_field = $wm_product->get_woomelly_status_field();
						$variable_to_simple = ( ($wm_template_sync->get_woomelly_separate_variations_field() != 'inactive')? true : false );
						if ( $woomelly_status_meli_field != 'payment_required' ) {
							$tags = $woomelly_get_settings->get_tags_available();
							$format_template = $woomelly_get_settings->get_settings_format_template();
							$wm_settings_omit_fields = $woomelly_get_settings->get_settings_omit_fields();
							
							$product_name = $wm_product->get_woomelly_custom_title_field();
							if ( $product_name == "" ) {
								$product_name = $_product->get_name();
							}
							$tags['{name}'] = $product_name;
							$tags['{slug}'] = esc_url( get_permalink($_product->get_id()) );
							$tags['{featured}'] = '<img src="' . wp_get_attachment_url( $_product->get_image_id() ) . '" />';
							$tags['{description}'] = ( ($woomelly_description_field=="")? apply_filters( 'the_content', get_post_field( 'post_content', $_product->get_id() ) ) : $woomelly_description_field );
							$tags['{short_description}'] = $_product->get_short_description();
							$tags['{sku}'] = $_product->get_sku();
							$tags['{price}'] = $_product->get_price();
							$tags['{stock_quantity}'] = $_product->get_stock_quantity();
							$tags['{stock_status}'] = $stock_status[$_product->get_stock_status()];
							$tags['{weight}'] = $_product->get_weight();
							$tags['{length}'] = $_product->get_length();
							$tags['{width}'] = $_product->get_width();
							$tags['{height}'] = $_product->get_height();
							$tags['{attributes}'] = "";
							$tags['{categories}'] = wc_get_product_category_list( $_product->get_id() );
							$tags['{tags}'] = wc_get_product_tag_list( $_product->get_id() );
							$tags['{gallery}'] = "";
							$tags['{condition}'] = "";
							$tags['{warranty}'] = "";
							// non_mercado_pago_payment_methods
							// sale_terms
							// geolocation
							// coverage_areas
							// listing_source					
							// differential_pricing
							// deal_ids
							// currency_id
								$item_meli['currency_id'] = $woomelly_get_settings->get_settings_currency_id();
							// currency_id
							// official_store_id
								$woomelly_official_store_id_field = $wm_template_sync->get_woomelly_official_store_id_field();
								if ( $woomelly_official_store_id_field != "" ) {
									$item_meli['official_store_id'] = $woomelly_official_store_id_field;
								}
							// official_store_id
							// buying_mode
								$item_meli['buying_mode'] = $wm_template_sync->get_woomelly_buying_mode_field();
							// buying_mode
							// condition
								$item_meli['condition'] = $wm_template_sync->get_woomelly_condition_field();
								switch ($item_meli['condition']) {
									case 'new':
										$tags['{condition}'] = __("New", "woomelly");
									break;
									case 'used':
										$tags['{condition}'] = __("Used", "woomelly");
									break;
									default:
										$tags['{condition}'] = __("Not specified", "woomelly");
									break;
								}
							// condition
							// accepts_mercadopago
								$woomelly_accepts_mercadopago_field = $wm_template_sync->get_woomelly_accepts_mercadopago_field();
								if ( $woomelly_accepts_mercadopago_field == true ) {
									$item_meli['accepts_mercadopago'] = true;	
								} else {
									$item_meli['accepts_mercadopago'] = false;
								}
							// accepts_mercadopago
							// location
								$woomelly_location_country_field = $wm_template_sync->get_woomelly_location_country_field();
								$woomelly_location_state_field = $wm_template_sync->get_woomelly_location_state_field();
								$woomelly_location_city_field = $wm_template_sync->get_woomelly_location_city_field();
								if ( $woomelly_location_country_field != "" && $woomelly_location_state_field != "" && $woomelly_location_city_field != "" ) {
									$item_meli['location'] = array(
										'city' => array(
											'id' => $woomelly_location_city_field,
											'state' => array(
												'id' => $woomelly_location_state_field
											),
											'country' => array(
												'id' => $woomelly_location_country_field
											)
										)
									);
								}
							// location
							// warranty
								$woomelly_warranty_field = $wm_template_sync->get_woomelly_warranty_field();
								if ( $woomelly_warranty_field != "" ) {
									$item_meli['warranty'] = $woomelly_warranty_field;
									$tags['{warranty}'] = $woomelly_warranty_field;
								}
							// warranty
							// video_id
								$woomelly_video_id_field = $wm_template_sync->get_woomelly_video_id_field();
								if ( $woomelly_video_id_field != "" ) {
									$item_meli['video_id'] = $woomelly_video_id_field;
								}
							// video_id
							// pictures
								$_image = false;
								$woomelly_pictures_field = array();
								$_image = wp_get_attachment_url( $_product->get_image_id() );
								if ( function_exists( 'jetpack_photon_url' ) ) {
									$_image = jetpack_photon_url( $_image );
								}
								if ( $_image && $_image!="" ) {
									$woomelly_pictures_field[] = array( 'source' => $_image );
								}
								$get_all_gallery = $_product->get_gallery_image_ids();
								if ( !empty($get_all_gallery) ) {
									foreach ( $get_all_gallery as $value ) {
										$_image = wp_get_attachment_url( $value );
										if ( function_exists( 'jetpack_photon_url' ) ) {
											$_image = jetpack_photon_url( $_image );
										}
										if ( $_image && $_image!="" ) {
											$woomelly_pictures_field[] = array( 'source' => $_image );
										}
									}
								}
								$item_meli['pictures'] = $woomelly_pictures_field;
							// pictures
							// shipping general
								$woomelly_shipping_mode_field = $wm_template_sync->get_woomelly_shipping_mode_field();
								switch ($woomelly_shipping_mode_field) {
									case 'not_specified':
										$item_meli['shipping']['mode'] = 'not_specified';
										$woomelly_shipping_local_pick_up_field = $wm_template_sync->get_woomelly_shipping_local_pick_up_field();
										$item_meli['shipping']['local_pick_up'] = $woomelly_shipping_local_pick_up_field;
										$woomelly_shipping_free_shipping_field = $wm_template_sync->get_woomelly_shipping_free_shipping_field();
										$item_meli['shipping']['free_shipping'] = $woomelly_shipping_free_shipping_field;
										break;
									case 'custom':
										$scost = array();
										$woomelly_custom_shipping_cost = $wm_template_sync->get_woomelly_custom_shipping_cost_field();
										if ( $woomelly_custom_shipping_cost == "" ) {
											$woomelly_custom_shipping_cost = array();
										}
										if ( !empty($woomelly_custom_shipping_cost) ) {
											
											foreach ( $woomelly_custom_shipping_cost as $value ) {
												$woomelly_custom_shipping_cost_array = explode( '::', $value );
												if ( !empty($woomelly_custom_shipping_cost_array) && isset($woomelly_custom_shipping_cost_array[0]) && isset($woomelly_custom_shipping_cost_array[1]) ) {
													if ( $woomelly_custom_shipping_cost_array[0] != "" ) {
														$sc = floatval($woomelly_custom_shipping_cost_array[1]);
														if ( $sc > 0 ) {
															$scost[] = array(
																'description' => $woomelly_custom_shipping_cost_array[0], 
																'cost' => round( wc_format_decimal( $sc ), 2 )
															);
														}
													}
												}
												unset( $woomelly_custom_shipping_cost_array );
											}
										}
										if ( !empty($scost) ) {
											$item_meli['shipping']['mode'] = 'custom';
											$item_meli['shipping']['costs'] = $scost;
										} else {
											$item_meli['shipping']['mode'] = 'not_specified';
										}
										break;
									case 'me1':
										$item_meli['shipping']['mode'] = 'me1';
										$woomelly_shipping_local_pick_up_field = $wm_template_sync->get_woomelly_shipping_local_pick_up_field();
										$item_meli['shipping']['local_pick_up'] = $woomelly_shipping_local_pick_up_field;
										$woomelly_shipping_free_shipping_field = $wm_template_sync->get_woomelly_shipping_free_shipping_field();
										$item_meli['shipping']['free_shipping'] = $woomelly_shipping_free_shipping_field;
										if ( $woomelly_shipping_free_shipping_field ) {
											$item_meli['shipping']['free_methods'] = array(
												array(
													"id" => $wm_template_sync->get_woomelly_shipping_accepted_methods_field(),
													"rule" => array(
														"free_mode" => "country",
														"value" => null
													)
												)
											);
										}
										break;
									case 'me2':
										$item_meli['shipping']['mode'] = 'me2';
										$woomelly_shipping_local_pick_up_field = $wm_template_sync->get_woomelly_shipping_local_pick_up_field();
										$item_meli['shipping']['local_pick_up'] = $woomelly_shipping_local_pick_up_field;
										$woomelly_shipping_free_shipping_field = $wm_template_sync->get_woomelly_shipping_free_shipping_field();
										$item_meli['shipping']['free_shipping'] = $woomelly_shipping_free_shipping_field;
										if ( $woomelly_shipping_free_shipping_field ) {
											$item_meli['shipping']['free_methods'] = array(
												array(
													"id" => $wm_template_sync->get_woomelly_shipping_accepted_methods_field(),
													"rule" => array(
														"free_mode" => "country",
														"value" => null
													)
												)
											);
										}
										break;
									default:
										$item_meli['shipping']['mode'] = 'not_specified';
										$woomelly_shipping_local_pick_up_field = $wm_template_sync->get_woomelly_shipping_local_pick_up_field();
										$item_meli['shipping']['local_pick_up'] = $woomelly_shipping_local_pick_up_field;
										$woomelly_shipping_free_shipping_field = $wm_template_sync->get_woomelly_shipping_free_shipping_field();
										$item_meli['shipping']['free_shipping'] = $woomelly_shipping_free_shipping_field;
										break;
								}
							// shipping general
							// variations
								if ( $_product->get_type() == 'variable' ) {
									$available_variations = wm_get_available_variations( $_product );
									if ( !empty($available_variations) ) {
										foreach ( $available_variations as $available_variation ) {
											$wm_product_variation = new WMProduct( $available_variation['variation_id'] );
											$woomelly_variation_field = $wm_product_variation->get_woomelly_clean_variation_field();
											if ( !empty($woomelly_variation_field) ) {
												$wm_variations['attribute_combinations'] = $woomelly_variation_field;
											/* else {
												$custom_attribute_combinations = array();
												foreach ( $available_variation['attributes'] as $key => $woo_attributes ) {
													$custom_attribute_combinations[] = array( 'name' => wm_get_name_attribute( $key ), 'value_name' => $woo_attributes );
												}
												$wm_variations['attribute_combinations'] = $custom_attribute_combinations;
												unset( $custom_attribute_combinations );
											}*/
												$wm_variations['available_quantity'] = $wm_template_sync->get_woomelly_stock_product( $_product, $available_variation['availability'] );
												$wm_variations['price'] = $wm_template_sync->get_woomelly_price_product( $available_variation['display_price'] );
												$woomelly_sku_field = $wm_template_sync->get_woomelly_seller_custom_field();
												$woomelly_sku_field = wm_replace_tags( $woomelly_sku_field, array('{sku}' => $available_variation['sku']) );
												$wm_variations['seller_custom_field'] = $woomelly_sku_field;
												$woomelly_variation_attributes_field = $wm_product_variation->get_woomelly_attribute_field();
												if ( !empty($woomelly_variation_attributes_field) ) {
													$wm_variations['attributes'] = $woomelly_variation_attributes_field;
												}
												$extra_img = array();
												if ( $wm_settings_omit_fields[2] == false ) {
													$_image = wp_get_attachment_url( $available_variation['image_id'] );
													if ( function_exists( 'jetpack_photon_url' ) ) {
														$_image = jetpack_photon_url( $_image );
													}
													if ( $_image && $_image!="" ) {
														$extra_img[] = $_image;
														$item_meli['pictures'][] = array( 'source' => $_image );
													}
													if ( !empty($get_all_gallery) ) {
														foreach ( $get_all_gallery as $value ) {
															$_image = wp_get_attachment_url( $value );
															if ( function_exists( 'jetpack_photon_url' ) ) {
																$_image = jetpack_photon_url( $_image );
															}
															if ( $_image && $_image!="" ) {
																$extra_img[] = $_image;
															}
														}
													}
													$wm_variations['picture_ids'] = $extra_img;
												}
												$all_wm_variations[] = $wm_variations;
												unset( $wm_variations );																					
												unset( $woomelly_variation_attributes_field );
												unset( $extra_img );
												unset( $extra_img_field );
											}
											unset( $wm_product_variation );
											unset( $woomelly_variation_field );
										}
									} else {
										$variable_to_simple = false;
									}
									$item_meli['variations'] = $all_wm_variations;
									if ( !empty($all_wm_variations) ) {
										$variable_to_simple = false;
									}
								} else {
									$item_meli['variations'] = array();
								}
							// variations
							if ( $variable_to_simple && $_product->get_type() == 'variable' ) {
								/*if ( isset($item_meli['pictures']) ) {
									unset( $item_meli['pictures'] );
								}*/
								$error_sync = false;
								foreach ( $available_variations as $available_variation ) {
									if ( !$error_sync ) {
										$wm_product_variation = new WMProduct( $available_variation['variation_id'] );
										if ( $wm_product_variation->get_id() ) {
											$woomelly_variation_field = $wm_product_variation->get_woomelly_clean_variation_field();
											// price
												$woomelly_price_field = $available_variation['display_price'];
												$woomelly_price_field = $wm_template_sync->get_woomelly_price_product( $woomelly_price_field );
												$item_meli['price'] = $woomelly_price_field;
											// price
											// available_quantity
												$woomelly_stock_field = $wm_template_sync->get_woomelly_stock_product( $_product, $available_variation['availability'] );							
												$item_meli['available_quantity'] = $woomelly_stock_field;
											// available_quantity
											// seller_custom_field
												$woomelly_sku_field = $wm_template_sync->get_woomelly_seller_custom_field();
												$woomelly_sku_field = wm_replace_tags( $woomelly_sku_field, array('{sku}' => $available_variation['sku']) );
												$item_meli['seller_custom_field'] = $woomelly_sku_field;
											// seller_custom_field
											// title
												$woomelly_title_field = $wm_template_sync->get_woomelly_title_field();
												$product_name = $wm_product->get_woomelly_custom_title_field();
												if ( $product_name == "" ) {
													$product_name = $_product->get_name();
												}
												$woomelly_title_field = wm_replace_tags( $woomelly_title_field, array('{name}' => $product_name . ' ' . implode('-', $available_variation['attributes'])) );
												$item_meli['title'] = $woomelly_title_field;
											// title
											// attributes
												$woomelly_variation_attributes_field = $wm_product->get_woomelly_attribute_field();
												if ( !empty($woomelly_variation_attributes_field) ) {
													$item_meli['attributes'] = $woomelly_variation_attributes_field;
												}									
											// attributes
											unset( $woomelly_variation_attributes_field );
											// pictures
												/*$extra_img = array();
												$_image = wp_get_attachment_url( $available_variation['image_id'] );
												if ( $_image && $_image!="" ) {
													$extra_img[] = array( 'source' => $_image );
												}
												$extra_img_field = $wm_product_variation->get_woomelly_variation_extra_img_field();
												if ( empty($extra_img_field) ) {
													foreach ( $get_all_gallery as $value ) {
														$_image = wp_get_attachment_url( $value );
														if ( $_image && $_image!="" ) {
															$extra_img[] = array( 'source' => $_image );
														}
													}
												} else {
													foreach ( $extra_img_field as $value ) {
														$extra_img[] = array( 'source' => wp_get_attachment_url( $value ) );
													}
												}
												$item_meli['pictures'] = $extra_img;*/
											// pictures
											unset( $extra_img );
											unset( $extra_img_field );
											// shipping dimensions
												$woomelly_shipping_dimensions_field = $wm_product->get_woomelly_variation_dimentions_field();
												if ( $woomelly_shipping_dimensions_field ) {
													$dimensions = "";
													$get_height = $available_variation['height'];
													$get_width = $available_variation['width'];
													$get_length = $available_variation['length'];
													$get_weight = $available_variation['weight'];
													if ( $get_height!="" && $get_width!="" && $get_length!="" && $get_weight!="" ) {
														$dimensions .= $get_height . "x" . $get_width . "x" . $get_length . "," . $get_weight;
														$item_meli['shipping']['dimensions'] = $dimensions;
													}
												} else {
													$item_meli['shipping']['dimensions'] = null;
												}
											// shipping dimensions
											$woomelly_status_meli_field = $wm_product_variation->get_woomelly_status_meli_field();
											if ( $woomelly_status_meli_field != "true" ) {
												// site_id
													$item_meli['site_id'] = $woomelly_get_settings->get_site_id();
												// site_id
												// category_id
													$item_meli['category_id'] = $wm_template_sync->get_woomelly_category_field();
												// category_id
												// listing_type_id
													$item_meli['listing_type_id'] = $wm_template_sync->get_woomelly_listing_type_id_field();
												// listing_type_id
												// description
													$woomelly_description_field = wm_replace_tags( $woomelly_get_settings->get_settings_template(), $tags );
													if ( $format_template == 'plain_text' ) {
														$item_meli['description'] = array( 'plain_text' => strip_tags($woomelly_description_field) );
													} else {
														$item_meli['description'] = array( 'text' => $woomelly_description_field );
													}
												// description
												$result_meli = WMeli::post_item( $item_meli, true );
												if ( !empty($result_meli) && isset($result_meli['httpCode']) && ( $result_meli['httpCode']=='201' || $result_meli['httpCode']=='402' ) ) {
													$wm_product_variation->success_sync( $result_meli['body'], $wm_product->get_id() );
													$wm_product->set_woomelly_status_field( 'active' );
												} else {
													if ( !empty($result_meli) && is_object($result_meli['body']) ) {
														$error_sync = true;
														switch ($woo_type) {
															case 'woomelly_only':
																$message_finish = 'nook:::<span style="background: #fef4f6; color: #f0506e;">'.( (isset($result_meli['body']->message))? $result_meli['body']->message : __("Connection error with Mercadolibre. Try again in a few seconds.", "woomelly") ).' <span class="dashicons dashicons-no-alt" style="color: red; vertical-align: middle;"></span></span><br>';
																if ( !empty($result_meli['body']->cause) ) {
																	$message_finish .= '<ul style="background: #fef4f6; color: #f0506e;">';
																	foreach ( $result_meli['body']->cause as $value ) {
																		$message_finish .= '<li type="square" style="margin-left: 20px;">'.$value->code.':<br>'.$value->message.'</li>';
																	}
																	$message_finish .= '</ul>';
																}
																break;
															case 'woomelly_manual':
																$message_finish .= '<dt><a style="color:red;" href="'.admin_url( "post.php?post=".$woo_id."&action=edit" ).'">'.$_product->get_name() . ' | ' . $wm_template_sync->get_format_id().'</a> <a href="'.$wm_product->get_woomelly_url_meli_field().'" target="_blank">'.__("(see)", "woomelly").'</a></dt><dd>'.( (isset($result_meli['body']->message))? $result_meli['body']->message : __("Connection error with Mercadolibre. Try again in a few seconds.", "woomelly") ).'</dd>';
																if ( !empty($result_meli['body']->cause) ) {
																	$message_finish .= ' (';
																	foreach ( $result_meli['body']->cause as $value ) {
																		$message_finish .= $value->code.': '.$value->message;
																	}
																	$message_finish .= ' )';
																}
																//Woomelly()->woomelly_set_log( $message_finish, 'last_sync' );
																break;
															case 'woomelly_automatic':
																$message_finish = 'error1';
																break;
															default:
																# code...
																break;
														}
														$wm_product->set_woomelly_sync_problem( true );
													} else {
														$error_sync = true;
														switch ($woo_type) {
															case 'woomelly_only':
																$message_finish = 'nook:::<span style="background: #fef4f6; color: #f0506e;">'.__("Connection error with Mercadolibre. Try again in a few seconds.", "woomelly").' <span class="dashicons dashicons-no-alt" style="color: red; vertical-align: middle;"></span></span><br>';								
																break;
															case 'woomelly_manual':
																$message_finish .= '<dt><a style="color:red;" href="'.admin_url( "post.php?post=".$woo_id."&action=edit" ).'">'.$_product->get_name() . ' | ' . $wm_template_sync->get_format_id().'</a> <a href="'.$wm_product->get_woomelly_url_meli_field().'" target="_blank">'.__("(see)", "woomelly").'</a></dt><dd>'.__("Connection error with Mercadolibre. Try again in a few seconds.", "woomelly").'</dd>';
																//Woomelly()->woomelly_set_log( $message_finish, 'last_sync' );
															break;
															case 'woomelly_automatic':
																$message_finish = 'error2';
																break;
															default:
																# code...
																break;
														}
														$wm_product->set_woomelly_sync_problem( true );
													}										
												}
											} else {
												$sync_description = false;
												// status
													$woomelly_status_meli_field = $wm_product->get_woomelly_status_field();
													$item_meli['status'] = $woomelly_status_meli_field;
												// status
												if ( $woomelly_status_meli_field == 'reclosed' ) {
													// listing_type_id
														$item_meli['listing_type_id'] = $wm_template_sync->get_woomelly_listing_type_id_field();
													// listing_type_id
													unset( $item_meli['currency_id'] );
													unset( $item_meli['buying_mode'] );
													unset( $item_meli['condition'] );
													unset( $item_meli['pictures'] );
													unset( $item_meli['accepts_mercadopago'] );
													unset( $item_meli['warranty'] );
													unset( $item_meli['status'] );
													unset( $item_meli['attributes'] );
													unset( $item_meli['variations'] );
													unset( $item_meli['shipping'] );
													unset( $item_meli['seller_custom_field'] );	
													$item_meli['quantity'] = $item_meli['available_quantity'];
													unset( $item_meli['available_quantity'] );
													$result_meli = WMeli::relist_item( $wm_product_variation->get_woomelly_code_meli_field(), $item_meli, true );
													if ( !empty($result_meli) && isset($result_meli['httpCode']) && ( $result_meli['httpCode']=='201' || $result_meli['httpCode']=='402' ) ) {
														$wm_product_variation->success_sync( $result_meli['body'], $wm_product->get_id() );
														$wm_product->set_woomelly_status_field( 'active' );
													} else {
														if ( !empty($result_meli) && is_object($result_meli['body'])) {
															$error_sync = true;
															switch ($woo_type) {
																case 'woomelly_only':
																	$message_finish = 'nook:::<span style="background: #fef4f6; color: #f0506e;">'.( (isset($result_meli['body']->message))? $result_meli['body']->message : __("Connection error with Mercadolibre. Try again in a few seconds.", "woomelly") ).' <span class="dashicons dashicons-no-alt" style="color: red; vertical-align: middle;"></span></span><br>';
																	if ( !empty($result_meli['body']->cause) ) {
																		$message_finish .= '<ul style="background: #fef4f6; color: #f0506e;">';
																		foreach ( $result_meli['body']->cause as $value ) {
																			$message_finish .= '<li type="square" style="margin-left: 20px;">'.$value->code.':<br>'.$value->message.'</li>';
																		}
																		$message_finish .= '</ul>';
																	}
																	break;
																case 'woomelly_manual':
																	$message_finish .= '<dt><a style="color:red;" href="'.admin_url( "post.php?post=".$woo_id."&action=edit" ).'">'.$_product->get_name() . ' | ' . $wm_template_sync->get_format_id().'</a> <a href="'.$wm_product->get_woomelly_url_meli_field().'" target="_blank">'.__("(see)", "woomelly").'</a></dt><dd>'.( (isset($result_meli['body']->message))? $result_meli['body']->message : __("Connection error with Mercadolibre. Try again in a few seconds.", "woomelly") ).'</dd>';
																	if ( !empty($result_meli['body']->cause) ) {
																		$message_finish .= ' (';
																		foreach ( $result_meli['body']->cause as $value ) {
																			$message_finish .= $value->code.': '.$value->message;
																		}
																		$message_finish .= ') ';
																	}
																	//Woomelly()->woomelly_set_log( $message_finish, 'last_sync' );
																break;
																case 'woomelly_automatic':
																	$message_finish = 'error3';
																	break;
																default:
																	# code...
																	break;
															}
															$wm_product->set_woomelly_sync_problem( true );
														} else {
															$error_sync = true;
															switch ($woo_type) {
																case 'woomelly_only':
																	$message_finish = 'nook:::<span style="background: #fef4f6; color: #f0506e;">'.__("Connection error with Mercadolibre. Try again in a few seconds.", "woomelly").' <span class="dashicons dashicons-no-alt" style="color: red; vertical-align: middle;"></span></span><br>';
																	break;
																case 'woomelly_manual':
																	$message_finish .= '<dt><a style="color:red;" href="'.admin_url( "post.php?post=".$woo_id."&action=edit" ).'">'.$_product->get_name() . ' | ' . $wm_template_sync->get_format_id().'</a> <a href="'.$wm_product->get_woomelly_url_meli_field().'" target="_blank">'.__("(see)", "woomelly").'</a></dt><dd>'.__("Connection error with Mercadolibre. Try again in a few seconds.", "woomelly").'</dd>';
																	//Woomelly()->woomelly_set_log( $message_finish, 'last_sync' );
																break;
																case 'woomelly_automatic':
																	$message_finish = 'error4';
																	break;
																default:
																	# code...
																	break;
															}
															$wm_product->set_woomelly_sync_problem( true );
														}
													}
												} else {
													if ( $wm_settings_omit_fields[0] == true ) {
														unset( $item_meli['title'] );
													}
													if ( $wm_settings_omit_fields[2] == true ) {
														unset( $item_meli['pictures'] );
													}
													if ( $wm_settings_omit_fields[3] == true ) {
														unset( $item_meli['available_quantity'] );
													}
													if ( $wm_settings_omit_fields[4] == true ) {
														unset( $item_meli['seller_custom_field'] );
													}
													if ( $wm_settings_omit_fields[1] == false ) {
														// description
															$woomelly_description_field = wm_replace_tags( $woomelly_get_settings->get_settings_template(), $tags );
															if ( $format_template == 'plain_text' ) {
																$update_description = array( 'text' => '', 'plain_text' => strip_tags($woomelly_description_field) );
															} else {
																$update_description = array( 'text' => $woomelly_description_field, 'plain_text' => '' );										
															}
															if ( !empty($update_description) ) {
																$result_meli_description = WMeli::put_item( $wm_product_variation->get_woomelly_code_meli_field() . '/description', $update_description, true );
																if ( is_object($result_meli_description) ) {
																	$sync_description = true;
																} else {
																	if ( !empty($result_meli_description) && is_object($result_meli_description['body']) ) {
																		$error_sync = true;
																		switch ($woo_type) {
																			case 'woomelly_only':
																				$message_finish = 'nook:::<span style="background: #fef4f6; color: #f0506e;">'.( (isset($result_meli_description['body']->message))? $result_meli_description['body']->message : __("Connection error with Mercadolibre. Try again in a few seconds.", "woomelly") ).' <span class="dashicons dashicons-no-alt" style="color: red; vertical-align: middle;"></span></span><br>';
																				if ( !empty($result_meli_description['body']->cause) ) {
																					$message_finish .= '<ul style="background: #fef4f6; color: #f0506e;">';
																					foreach ( $result_meli_description['body']->cause as $value ) {
																						$message_finish .= '<li type="square" style="margin-left: 20px;">'.$value->code.':<br>'.$value->message.'</li>';
																					}
																					$message_finish .= '</ul>';
																				}
																				break;
																			case 'woomelly_manual':
																				$message_finish .= '<dt><a style="color:red;" href="'.admin_url( "post.php?post=".$woo_id."&action=edit" ).'">'.$_product->get_name() . ' | ' . $wm_template_sync->get_format_id().'</a> <a href="'.$wm_product->get_woomelly_url_meli_field().'" target="_blank">'.__("(see)", "woomelly").'</a></dt><dd>'.( (isset($result_meli_description['body']->message))? $result_meli_description['body']->message : __("Connection error with Mercadolibre. Try again in a few seconds.", "woomelly") ).'</dd>';
																				if ( !empty($result_meli_description['body']->cause) ) {
																					$message_finish .= ' (';
																					foreach ( $result_meli_description['body']->cause as $value ) {
																						$message_finish .= $value->code.': '.$value->message;
																					}
																					$message_finish .= ') ';
																				}
																				//Woomelly()->woomelly_set_log( $message_finish, 'last_sync' );
																			break;
																			case 'woomelly_automatic':
																				$message_finish = 'error5';
																				break;
																			default:
																				# code...
																				break;
																		}
																		$wm_product->set_woomelly_sync_problem( true );
																	} else {
																		$error_sync = true;
																		switch ($woo_type) {
																			case 'woomelly_only':
																				$message_finish = 'nook:::<span style="background: #fef4f6; color: #f0506e;">'.__("Connection error with Mercadolibre. Try again in a few seconds.", "woomelly").' <span class="dashicons dashicons-no-alt" style="color: red; vertical-align: middle;"></span></span><br>';
																				break;
																			case 'woomelly_manual':
																				$message_finish .= '<dt><a style="color:red;" href="'.admin_url( "post.php?post=".$woo_id."&action=edit" ).'">'.$_product->get_name() . ' | ' . $wm_template_sync->get_format_id().'</a> <a href="'.$wm_product->get_woomelly_url_meli_field().'" target="_blank">'.__("(see)", "woomelly").'</a></dt><dd>'.__("Connection error with Mercadolibre. Try again in a few seconds.", "woomelly").'</dd>';
																				//Woomelly()->woomelly_set_log( $message_finish, 'last_sync' );
																			break;
																			case 'woomelly_automatic':
																				$message_finish = 'error6';
																			break;
																			default:
																				# code...
																				break;
																		}
																		$wm_product->set_woomelly_sync_problem( true );
																	}
																}
															}					
														// description
													} else {
														$sync_description = true;	
													}
													if ( $sync_description == true ) {
														$result_meli = WMeli::put_item( $wm_product_variation->get_woomelly_code_meli_field(), $item_meli, true );
														if ( is_object($result_meli) ) {
															$wm_product_variation->success_sync( $result_meli, $wm_product->get_id() );
															$wm_product->set_woomelly_status_field( $result_meli->status );												
														} else {
															$test_sales_item_meli = $item_meli;
															unset( $item_meli );
															unset( $result_meli );
															$item_meli = wm_clean_item_with_sales( $test_sales_item_meli );
															unset( $test_sales_item_meli );
															$result_meli = WMeli::put_item( $wm_product_variation->get_woomelly_code_meli_field(), $item_meli, true );
															if ( is_object($result_meli) ) {
																$wm_product_variation->success_sync( $result_meli, $wm_product->get_id() );
																$wm_product->set_woomelly_status_field( $result_meli->status );
															} else {
																if ( !empty($result_meli) && is_object($result_meli['body']) ) {
																	$error_sync = true;
																	switch ($woo_type) {
																		case 'woomelly_only':
																			$message_finish = 'nook:::<span style="background: #fef4f6; color: #f0506e;">'.( (isset($result_meli['body']->message))? $result_meli['body']->message : __("Connection error with Mercadolibre. Try again in a few seconds.", "woomelly") ).' <span class="dashicons dashicons-no-alt" style="color: red; vertical-align: middle;"></span></span><br>';
																			if ( !empty($result_meli['body']->cause) ) {
																				if ( !empty($result_meli['body']->cause) ) {
																					$message_finish .= '<ul style="background: #fef4f6; color: #f0506e;">';
																					foreach ( $result_meli['body']->cause as $value ) {
																						$message_finish .= '<li type="square" style="margin-left: 20px;">'.$value->code.':<br>'.$value->message.'</li>';
																					}
																					$message_finish .= '</ul>';
																				}
																			}
																			break;
																		case 'woomelly_manual':
																			$message_finish .= '<dt><a style="color:red;" href="'.admin_url( "post.php?post=".$woo_id."&action=edit" ).'">'.$_product->get_name() . ' | ' . $wm_template_sync->get_format_id().'</a> <a href="'.$wm_product->get_woomelly_url_meli_field().'" target="_blank">'.__("(see)", "woomelly").'</a></dt><dd>'.( (isset($result_meli['body']->message))? $result_meli['body']->message : __("Connection error with Mercadolibre. Try again in a few seconds.", "woomelly") ).'</dd>';
																			if ( !empty($result_meli['body']->cause) ) {
																				$message_finish .= ' (';
																				foreach ( $result_meli['body']->cause as $value ) {
																					$message_finish .= $value->code.': '.$value->message;
																				}
																				$message_finish .= ') ';
																			}
																			//Woomelly()->woomelly_set_log( $message_finish, 'last_sync' );
																		break;
																		case 'woomelly_automatic':
																			$message_finish = 'error7';
																		break;
																		default:
																			# code...
																			break;
																	}
																	$wm_product->set_woomelly_sync_problem( true );
																} else {
																	$error_sync = true;
																	switch ($woo_type) {
																		case 'woomelly_only':
																			$message_finish = 'nook:::<span style="background: #fef4f6; color: #f0506e;">'.__("Connection error with Mercadolibre. Try again in a few seconds.", "woomelly").' <span class="dashicons dashicons-no-alt" style="color: red; vertical-align: middle;"></span></span>';
																			break;
																		case 'woomelly_manual':
																			$message_finish .= '<dt><a style="color:red;" href="'.admin_url( "post.php?post=".$woo_id."&action=edit" ).'">'.$_product->get_name() . ' | ' . $wm_template_sync->get_format_id().'</a> <a href="'.$wm_product->get_woomelly_url_meli_field().'" target="_blank">'.__("(see)", "woomelly").'</a></dt><dd>'.__("Connection error with Mercadolibre. Try again in a few seconds.", "woomelly").'</dd>';
																			//Woomelly()->woomelly_set_log( $message_finish, 'last_sync' );
																		break;
																		case 'woomelly_automatic':
																			$message_finish = 'error8';
																		break;
																		default:
																			# code...
																			break;
																	}
																	$wm_product->set_woomelly_sync_problem( true );
																}
															}
														}
													} else {
														if ( !empty($result_meli_description) ) {
															$error_sync = true;
															switch ($woo_type) {
																case 'woomelly_only':
																	$message_finish = 'nook:::<span style="background: #fef4f6; color: #f0506e;">'.( (isset($result_meli_description['body']->message))? $result_meli_description['body']->message : __("Connection error with Mercadolibre. Try again in a few seconds.", "woomelly") ).' <span class="dashicons dashicons-no-alt" style="color: red; vertical-align: middle;"></span></span><br>';
																	if ( !empty($result_meli_description['body']->cause) ) {
																			if ( !empty($result_meli_description['body']->cause) ) {
																			$message_finish .= '<ul style="background: #fef4f6; color: #f0506e;">';
																				foreach ( $result_meli_description['body']->cause as $value ) {
																					$message_finish .= '<li type="square" style="margin-left: 20px;">'.$value->code.':<br>'.$value->message.'</li>';
																				}
																				$message_finish .= '</ul>';
																			}
																	}
																	break;
																case 'woomelly_manual':
																	$message_finish .= '<dt><a style="color:red;" href="'.admin_url( "post.php?post=".$woo_id."&action=edit" ).'">'.$_product->get_name() . ' | ' . $wm_template_sync->get_format_id().'</a> <a href="'.$wm_product->get_woomelly_url_meli_field().'" target="_blank">'.__("(see)", "woomelly").'</a></dt><dd>'.( (isset($result_meli_description['body']->message))? $result_meli_description['body']->message : __("Connection error with Mercadolibre. Try again in a few seconds.", "woomelly") ).'</dd>';
																	if ( !empty($result_meli_description['body']->cause) ) {
																		$message_finish .= ' (';
																		foreach ( $result_meli_description['body']->cause as $value ) {
																			$message_finish .= $value->code.': '.$value->message;
																		}
																		$message_finish .= ') ';
																	}
																	//Woomelly()->woomelly_set_log( $message_finish, 'last_sync' );
																break;
																case 'woomelly_automatic':
																	$message_finish = 'error9';
																	break;
																default:
																	# code...
																	break;
															}
															$wm_product->set_woomelly_sync_problem( true );
														}					
													}
												}
											}
										} else {
											$error_sync = false;
											switch ($woo_type) {
												case 'woomelly_only':
													$message_finish = 'nook:::<span style="background: #fef4f6; color: #f0506e;">'.__("The product code could not be obtained.", "woomelly").' <span class="dashicons dashicons-no-alt" style="color: red; vertical-align: middle;"></span></span>';
													break;
												case 'woomelly_manual':
													$message_finish .= '<dt><a style="color:red;" href="'.admin_url( "post.php?post=".$woo_id."&action=edit" ).'">'.sprintf( __("Invalid Product", "woomelly"), $woo_id).'</a></dt><dd>'.__("The product code could not be obtained.", "woomelly").'</dd>';
													//Woomelly()->woomelly_set_log( $message_finish, 'last_sync' );
													break;
												case 'woomelly_automatic':
													$message_finish = 'error10';
													break;	
												default:
													# code...
													break;
											}
										}
									}
								}
								if ( !$error_sync ) {
									$wm_product->success_sync();
									switch ($woo_type) {
										case 'woomelly_only':
											$message_finish = 'ok:::<span>'.__("SUCCESSFUL SYNCHRONIZATION.", "woomelly").'<span class="dashicons dashicons-yes" style="color: green; vertical-align: middle;"></span></span>';
											//Woomelly()->woomelly_set_log( $result_meli, 'sync' );
											break;
										case 'woomelly_manual':
											$message_finish .= '<dt><a href="'.admin_url( "post.php?post=".$woo_id."&action=edit" ).'">'.$_product->get_name() . ' | ' . $wm_template_sync->get_format_id().'</a> <a href="'.$wm_product->get_woomelly_url_meli_field().'" target="_blank">'.__("(see)", "woomelly").'</a></dt><dd>'.__("SUCCESSFUL SYNCHRONIZATION.", "woomelly").'</dd>';
											//Woomelly()->woomelly_set_log( $message_finish, 'last_sync' );
											//Woomelly()->woomelly_set_log( $result_meli, 'sync' );
											break;
										case 'woomelly_automatic':
											$message_finish = 'success';
											break;
										default:
											# code...
											break;
									}
								}
							} else if ( ($_product->get_type() == 'variable' && !empty($all_wm_variations)) || $_product->get_type() != 'variable' ) {
								// price
									if ( $_product->get_type() != 'variable' ) {
										$woomelly_price_field = $_product->get_price();
										$woomelly_price_field = $wm_template_sync->get_woomelly_price_product( $woomelly_price_field );
										$item_meli['price'] = $woomelly_price_field;
										$tags['{price}'] = $woomelly_price_field;
									}
								// price
								// available_quantity
									if ( $_product->get_type() != 'variable' ) {
										$woomelly_stock_field = $wm_template_sync->get_woomelly_stock_product( $_product );							
										$item_meli['available_quantity'] = $woomelly_stock_field;
										$tags['{stock_quantity}'] = $woomelly_stock_field;
									}
								// available_quantity
								// seller_custom_field
									if ( $_product->get_type() != 'variable' ) {
										$woomelly_sku_field = $wm_template_sync->get_woomelly_seller_custom_field();
										$woomelly_sku_field = wm_replace_tags( $woomelly_sku_field, $tags );
										$item_meli['seller_custom_field'] = $woomelly_sku_field;
										$tags['{sku}'] = $woomelly_sku_field;
									}
								// seller_custom_field
								// title
									$woomelly_title_field = $wm_template_sync->get_woomelly_title_field();
									$woomelly_title_field = wm_replace_tags( $woomelly_title_field, $tags );
									$item_meli['title'] = $woomelly_title_field;
									$tags['{name}'] = $woomelly_title_field;
								// title
								// attributes
									$woomelly_attributes_field = $wm_product->get_woomelly_attribute_field();
									$item_meli['attributes'] = $woomelly_attributes_field;
								// attributes
								// shipping dimensions
									if ( $_product->get_type() != 'variable' ) {
										$woomelly_shipping_dimensions_field = $wm_template_sync->get_woomelly_shipping_dimensions_field();
										if ( $woomelly_shipping_dimensions_field ) {
											$dimensions = "";
											$get_height = $_product->get_height();
											$get_width = $_product->get_width();
											$get_length = $_product->get_length();
											$get_weight = $_product->get_weight();
											if ( $get_height!="" && $get_width!="" && $get_length!="" && $get_weight!="" ) {
												$dimensions .= $get_height . "x" . $get_width . "x" . $get_length . "," . $get_weight;
												$item_meli['shipping']['dimensions'] = $dimensions;
											}
										} else {
											$item_meli['shipping']['dimensions'] = null;
										}
									}
								// shipping dimensions
								$woomelly_status_meli_field = $wm_product->get_woomelly_status_meli_field();
								if ( $woomelly_status_meli_field != "true" ) {
									// site_id
										$item_meli['site_id'] = $woomelly_get_settings->get_site_id();
									// site_id
									// category_id
										$item_meli['category_id'] = $wm_template_sync->get_woomelly_category_field();
									// category_id
									// listing_type_id
										$item_meli['listing_type_id'] = $wm_template_sync->get_woomelly_listing_type_id_field();
									// listing_type_id
									// description
										$woomelly_description_field = wm_replace_tags( $woomelly_get_settings->get_settings_template(), $tags );
										if ( $format_template == 'plain_text' ) {
											$item_meli['description'] = array( 'plain_text' => strip_tags($woomelly_description_field) );
										} else {
											$item_meli['description'] = array( 'text' => $woomelly_description_field );
										}
									// description
									$result_meli = WMeli::post_item( $item_meli, true );
									if ( !empty($result_meli) && isset($result_meli['httpCode']) && ( $result_meli['httpCode']=='201' || $result_meli['httpCode']=='402' ) ) {
										$wm_product->success_sync( $result_meli['body'] );
										switch ($woo_type) {
											case 'woomelly_only':
												$message_finish = 'ok:::<span>'.__("SUCCESSFUL SYNCHRONIZATION.", "woomelly").'<span class="dashicons dashicons-yes" style="color: green; vertical-align: middle;"></span></span>';
												//Woomelly()->woomelly_set_log( $result_meli, 'sync' );
												break;
											case 'woomelly_manual':
												$message_finish .= '<dt><a href="'.admin_url( "post.php?post=".$woo_id."&action=edit" ).'">'.$_product->get_name() . ' | ' . $wm_template_sync->get_format_id().'</a> <a href="'.$wm_product->get_woomelly_url_meli_field().'" target="_blank">'.__("(see)", "woomelly").'</a></dt><dd>'.__("SUCCESSFUL SYNCHRONIZATION.", "woomelly").'</dd>';
												//Woomelly()->woomelly_set_log( $message_finish, 'last_sync' );
												//Woomelly()->woomelly_set_log( $result_meli, 'sync' );
											break;
											case 'woomelly_automatic':
												$message_finish = 'success';
												break;
											default:
												# code...
												break;
										}
									} else {
										if ( !empty($result_meli) && is_object($result_meli['body']) ) {
											switch ($woo_type) {
												case 'woomelly_only':
													$message_finish = 'nook:::<span style="background: #fef4f6; color: #f0506e;">'.( (isset($result_meli['body']->message))? $result_meli['body']->message : __("Connection error with Mercadolibre. Try again in a few seconds.", "woomelly") ).' <span class="dashicons dashicons-no-alt" style="color: red; vertical-align: middle;"></span></span><br>';
													if ( !empty($result_meli['body']->cause) ) {
														$message_finish .= '<ul style="background: #fef4f6; color: #f0506e;">';
														foreach ( $result_meli['body']->cause as $value ) {
															$message_finish .= '<li type="square" style="margin-left: 20px;">'.$value->code.':<br>'.$value->message.'</li>';
														}
														$message_finish .= '</ul>';
													}
													break;
												case 'woomelly_manual':
													$message_finish .= '<dt><a style="color:red;" href="'.admin_url( "post.php?post=".$woo_id."&action=edit" ).'">'.$_product->get_name() . ' | ' . $wm_template_sync->get_format_id().'</a> <a href="'.$wm_product->get_woomelly_url_meli_field().'" target="_blank">'.__("(see)", "woomelly").'</a></dt><dd>'.( (isset($result_meli['body']->message))? $result_meli['body']->message : __("Connection error with Mercadolibre. Try again in a few seconds.", "woomelly") ).'</dd>';
													if ( !empty($result_meli['body']->cause) ) {
														$message_finish .= ' (';
														foreach ( $result_meli['body']->cause as $value ) {
															$message_finish .= $value->code.': '.$value->message;
														}
														$message_finish .= ' )';
													}
													//Woomelly()->woomelly_set_log( $message_finish, 'last_sync' );
												break;
												case 'woomelly_automatic':
													$message_finish = 'error11';
													break;
												default:
													# code...
													break;
											}
											$wm_product->set_woomelly_sync_problem( true );
										} else {
											switch ($woo_type) {
												case 'woomelly_only':
													$message_finish = 'nook:::<span style="background: #fef4f6; color: #f0506e;">'.__("Connection error with Mercadolibre. Try again in a few seconds.", "woomelly").' <span class="dashicons dashicons-no-alt" style="color: red; vertical-align: middle;"></span></span><br>';								
													break;
												case 'woomelly_manual':
													$message_finish .= '<dt><a style="color:red;" href="'.admin_url( "post.php?post=".$woo_id."&action=edit" ).'">'.$_product->get_name() . ' | ' . $wm_template_sync->get_format_id().'</a> <a href="'.$wm_product->get_woomelly_url_meli_field().'" target="_blank">'.__("(see)", "woomelly").'</a></dt><dd>'.__("Connection error with Mercadolibre. Try again in a few seconds.", "woomelly").'</dd>';
													//Woomelly()->woomelly_set_log( $message_finish, 'last_sync' );
												break;
												case 'woomelly_automatic':
													$message_finish = 'error12';
													break;
												default:
													# code...
													break;
											}
											$wm_product->set_woomelly_sync_problem( true );
										}
									}
								} else {
									$sync_description = false;
									// status
										$woomelly_status_meli_field = $wm_product->get_woomelly_status_field();
										$item_meli['status'] = $woomelly_status_meli_field;
									// status
									if ( $woomelly_status_meli_field == 'reclosed' ) {
										// listing_type_id
											$item_meli['listing_type_id'] = $wm_template_sync->get_woomelly_listing_type_id_field();
										// listing_type_id
										unset( $item_meli['currency_id'] );
										unset( $item_meli['buying_mode'] );
										unset( $item_meli['condition'] );
										unset( $item_meli['pictures'] );
										unset( $item_meli['accepts_mercadopago'] );
										unset( $item_meli['warranty'] );
										unset( $item_meli['status'] );
										unset( $item_meli['attributes'] );
										unset( $item_meli['variations'] );
										unset( $item_meli['shipping'] );
										unset( $item_meli['seller_custom_field'] );										
										$item_meli['quantity'] = $item_meli['available_quantity'];
										unset( $item_meli['available_quantity'] );
										$result_meli = WMeli::relist_item( $wm_product->get_woomelly_code_meli_field(), $item_meli, true );
										if ( !empty($result_meli) && isset($result_meli['httpCode']) && ( $result_meli['httpCode']=='201' || $result_meli['httpCode']=='402' ) ) {
											$wm_product->success_sync( $result_meli['body'] );
											$wm_product->set_woomelly_status_field( 'active' );
											switch ($woo_type) {
												case 'woomelly_only':
													$message_finish = 'ok:::<span>'.__("SUCCESSFUL SYNCHRONIZATION.", "woomelly").'<span class="dashicons dashicons-yes" style="color: green; vertical-align: middle;"></span></span>';
													//Woomelly()->woomelly_set_log( $result_meli, 'sync' );
													break;
												case 'woomelly_manual':
													$message_finish .= '<dt><a href="'.admin_url( "post.php?post=".$woo_id."&action=edit" ).'">'.$_product->get_name() . ' | ' . $wm_template_sync->get_format_id().'</a> <a href="'.$wm_product->get_woomelly_url_meli_field().'" target="_blank">'.__("(see)", "woomelly").'</a></dt><dd>'.__("SUCCESSFUL SYNCHRONIZATION.", "woomelly").'</dd>';
													//Woomelly()->woomelly_set_log( $message_finish, 'last_sync' );
													//Woomelly()->woomelly_set_log( $result_meli, 'sync' );
													break;
												case 'woomelly_automatic':
													$message_finish = 'success';
													break;
												default:
													# code...
													break;
											}
										} else {
											if ( !empty($result_meli) && is_object($result_meli['body'])) {
												switch ($woo_type) {
													case 'woomelly_only':
														$message_finish = 'nook:::<span style="background: #fef4f6; color: #f0506e;">'.( (isset($result_meli['body']->message))? $result_meli['body']->message : __("Connection error with Mercadolibre. Try again in a few seconds.", "woomelly") ).' <span class="dashicons dashicons-no-alt" style="color: red; vertical-align: middle;"></span></span><br>';
														if ( !empty($result_meli['body']->cause) ) {
															$message_finish .= '<ul style="background: #fef4f6; color: #f0506e;">';
															foreach ( $result_meli['body']->cause as $value ) {
																$message_finish .= '<li type="square" style="margin-left: 20px;">'.$value->code.':<br>'.$value->message.'</li>';
															}
															$message_finish .= '</ul>';
														}
														break;
													case 'woomelly_manual':
														$message_finish .= '<dt><a style="color:red;" href="'.admin_url( "post.php?post=".$woo_id."&action=edit" ).'">'.$_product->get_name() . ' | ' . $wm_template_sync->get_format_id().'</a> <a href="'.$wm_product->get_woomelly_url_meli_field().'" target="_blank">'.__("(see)", "woomelly").'</a></dt><dd>'.( (isset($result_meli['body']->message))? $result_meli['body']->message : __("Connection error with Mercadolibre. Try again in a few seconds.", "woomelly") ).'</dd>';
														if ( !empty($result_meli['body']->cause) ) {
															$message_finish .= ' (';
															foreach ( $result_meli['body']->cause as $value ) {
																$message_finish .= $value->code.': '.$value->message;
															}
															$message_finish .= ') ';
														}
														//Woomelly()->woomelly_set_log( $message_finish, 'last_sync' );
													break;
													case 'woomelly_automatic':
														$message_finish = 'error13';
														break;
													default:
														# code...
														break;
												}
												$wm_product->set_woomelly_sync_problem( true );
											} else {
												switch ($woo_type) {
													case 'woomelly_only':
														$message_finish = 'nook:::<span style="background: #fef4f6; color: #f0506e;">'.__("Connection error with Mercadolibre. Try again in a few seconds.", "woomelly").' <span class="dashicons dashicons-no-alt" style="color: red; vertical-align: middle;"></span></span><br>';
														break;
													case 'woomelly_manual':
														$message_finish .= '<dt><a style="color:red;" href="'.admin_url( "post.php?post=".$woo_id."&action=edit" ).'">'.$_product->get_name() . ' | ' . $wm_template_sync->get_format_id().'</a> <a href="'.$wm_product->get_woomelly_url_meli_field().'" target="_blank">'.__("(see)", "woomelly").'</a></dt><dd>'.__("Connection error with Mercadolibre. Try again in a few seconds.", "woomelly").'</dd>';
														//Woomelly()->woomelly_set_log( $message_finish, 'last_sync' );
													break;
													case 'woomelly_automatic':
														$message_finish = 'error14';
														break;
													default:
														# code...
														break;
												}
												$wm_product->set_woomelly_sync_problem( true );
											}
										}
									} else {
										if ( $wm_settings_omit_fields[0] == true ) {
											unset( $item_meli['title'] );
										}
										if ( $wm_settings_omit_fields[2] == true ) {
											unset( $item_meli['pictures'] );
										}
										if ( $wm_settings_omit_fields[3] == true ) {
											unset( $item_meli['available_quantity'] );
										}
										if ( $wm_settings_omit_fields[4] == true ) {
											unset( $item_meli['seller_custom_field'] );
										}
										if ( $wm_settings_omit_fields[1] == false ) {
											// description
												$woomelly_description_field = wm_replace_tags( $woomelly_get_settings->get_settings_template(), $tags );
												if ( $format_template == 'plain_text' ) {
													$update_description = array( 'text' => '', 'plain_text' => strip_tags($woomelly_description_field) );
												} else {
													$update_description = array( 'text' => $woomelly_description_field, 'plain_text' => '' );										
												}
												if ( !empty($update_description) ) {
													$result_meli_description = WMeli::put_item( $wm_product->get_woomelly_code_meli_field() . '/description', $update_description, true );
													if ( is_object($result_meli_description) ) {
														$sync_description = true;
													} else {
														if ( !empty($result_meli_description) && is_object($result_meli_description['body']) ) {
															switch ($woo_type) {
																case 'woomelly_only':
																	$message_finish = 'nook:::<span style="background: #fef4f6; color: #f0506e;">'.( (isset($result_meli_description['body']->message))? $result_meli_description['body']->message : __("Connection error with Mercadolibre. Try again in a few seconds.", "woomelly") ).' <span class="dashicons dashicons-no-alt" style="color: red; vertical-align: middle;"></span></span><br>';
																	if ( !empty($result_meli_description['body']->cause) ) {
																		$message_finish .= '<ul style="background: #fef4f6; color: #f0506e;">';
																		foreach ( $result_meli_description['body']->cause as $value ) {
																			$message_finish .= '<li type="square" style="margin-left: 20px;">'.$value->code.':<br>'.$value->message.'</li>';
																		}
																		$message_finish .= '</ul>';
																	}
																	break;
																case 'woomelly_manual':
																	$message_finish .= '<dt><a style="color:red;" href="'.admin_url( "post.php?post=".$woo_id."&action=edit" ).'">'.$_product->get_name() . ' | ' . $wm_template_sync->get_format_id().'</a> <a href="'.$wm_product->get_woomelly_url_meli_field().'" target="_blank">'.__("(see)", "woomelly").'</a></dt><dd>'.( (isset($result_meli_description['body']->message))? $result_meli_description['body']->message : __("Connection error with Mercadolibre. Try again in a few seconds.", "woomelly") ).'</dd>';
																	if ( !empty($result_meli_description['body']->cause) ) {
																		$message_finish .= ' (';
																		foreach ( $result_meli_description['body']->cause as $value ) {
																			$message_finish .= $value->code.': '.$value->message;
																		}
																		$message_finish .= ') ';
																	}
																	//Woomelly()->woomelly_set_log( $message_finish, 'last_sync' );
																break;
																case 'woomelly_automatic':
																	$message_finish = 'error15';
																	break;
																default:
																	# code...
																	break;
															}
															$wm_product->set_woomelly_sync_problem( true );
														} else {
															switch ($woo_type) {
																case 'woomelly_only':
																	$message_finish = 'nook:::<span style="background: #fef4f6; color: #f0506e;">'.__("Connection error with Mercadolibre. Try again in a few seconds.", "woomelly").' <span class="dashicons dashicons-no-alt" style="color: red; vertical-align: middle;"></span></span><br>';
																	break;
																case 'woomelly_manual':
																	$message_finish .= '<dt><a style="color:red;" href="'.admin_url( "post.php?post=".$woo_id."&action=edit" ).'">'.$_product->get_name() . ' | ' . $wm_template_sync->get_format_id().'</a> <a href="'.$wm_product->get_woomelly_url_meli_field().'" target="_blank">'.__("(see)", "woomelly").'</a></dt><dd>'.__("Connection error with Mercadolibre. Try again in a few seconds.", "woomelly").'</dd>';
																	//Woomelly()->woomelly_set_log( $message_finish, 'last_sync' );
																break;
																case 'woomelly_automatic':
																	$message_finish = 'error16';
																	break;
																default:
																	# code...
																	break;
															}
															$wm_product->set_woomelly_sync_problem( true );
														}
													}
												}					
											// description
										} else {
											$sync_description = true;
										}
										if ( $sync_description == true ) {
											$result_meli = WMeli::put_item( $wm_product->get_woomelly_code_meli_field(), $item_meli, true );
											if ( is_object($result_meli) ) {
												$wm_product->success_sync( $result_meli );
												switch ($woo_type) {
													case 'woomelly_only':
														$message_finish = 'ok:::<span>'.__("SUCCESSFUL SYNCHRONIZATION.", "woomelly").'<span class="dashicons dashicons-yes" style="color: green; vertical-align: middle;"></span></span>';
														//Woomelly()->woomelly_set_log( $result_meli, 'sync' );
														break;
													case 'woomelly_manual':
														$message_finish .= '<dt><a href="'.admin_url( "post.php?post=".$woo_id."&action=edit" ).'">'.$_product->get_name() . ' | ' . $wm_template_sync->get_format_id().'</a> <a href="'.$wm_product->get_woomelly_url_meli_field().'" target="_blank">'.__("(see)", "woomelly").'</a></dt><dd>'.__("SUCCESSFUL SYNCHRONIZATION.", "woomelly").'</dd>';
														//Woomelly()->woomelly_set_log( $message_finish, 'last_sync' );
														//Woomelly()->woomelly_set_log( $result_meli, 'sync' );
													break;
													case 'woomelly_automatic':
														$message_finish = 'success';
														break;
													default:
														# code...
														break;
												}
											} else {
												$test_sales_result_meli = $item_meli;
												unset( $item_meli );
												unset( $result_meli );
												$item_meli = wm_clean_item_with_sales( $test_sales_result_meli );
												$result_meli = WMeli::put_item( $wm_product->get_woomelly_code_meli_field(), $item_meli, true );
												if ( is_object($result_meli) ) {
													$wm_product->success_sync( $result_meli );
													switch ($woo_type) {
														case 'woomelly_only':
															$message_finish = 'ok:::<span>'.__("SUCCESSFUL SYNCHRONIZATION.", "woomelly").'<span class="dashicons dashicons-yes" style="color: green; vertical-align: middle;"></span></span>';
															//Woomelly()->woomelly_set_log( $result_meli, 'sync' );
															break;
														case 'woomelly_manual':
															$message_finish .= '<dt><a href="'.admin_url( "post.php?post=".$woo_id."&action=edit" ).'">'.$_product->get_name() . ' | ' . $wm_template_sync->get_format_id().'</a> <a href="'.$wm_product->get_woomelly_url_meli_field().'" target="_blank">'.__("(see)", "woomelly").'</a></dt><dd>'.__("SUCCESSFUL SYNCHRONIZATION.", "woomelly").'</dd>';
															//Woomelly()->woomelly_set_log( $message_finish, 'last_sync' );
															//Woomelly()->woomelly_set_log( $result_meli, 'sync' );
															break;
														case 'woomelly_automatic':
															$message_finish = 'success';
															break;
														default:
															# code...
															break;
													}
												} else {												
													if ( !empty($result_meli) && is_object($result_meli['body']) ) {
														switch ($woo_type) {
															case 'woomelly_only':
																$message_finish = 'nook:::<span style="background: #fef4f6; color: #f0506e;">'.( (isset($result_meli['body']->message))? $result_meli['body']->message : __("Connection error with Mercadolibre. Try again in a few seconds.", "woomelly") ).' <span class="dashicons dashicons-no-alt" style="color: red; vertical-align: middle;"></span></span><br>';
																if ( !empty($result_meli['body']->cause) ) {
																	if ( !empty($result_meli['body']->cause) ) {
																		$message_finish .= '<ul style="background: #fef4f6; color: #f0506e;">';
																		foreach ( $result_meli['body']->cause as $value ) {
																			$message_finish .= '<li type="square" style="margin-left: 20px;">'.$value->code.':<br>'.$value->message.'</li>';
																		}
																		$message_finish .= '</ul>';
																	}
																}
																break;
															case 'woomelly_manual':
																$message_finish .= '<dt><a style="color:red;" href="'.admin_url( "post.php?post=".$woo_id."&action=edit" ).'">'.$_product->get_name() . ' | ' . $wm_template_sync->get_format_id().'</a> <a href="'.$wm_product->get_woomelly_url_meli_field().'" target="_blank">'.__("(see)", "woomelly").'</a></dt><dd>'.( (isset($result_meli['body']->message))? $result_meli['body']->message : __("Connection error with Mercadolibre. Try again in a few seconds.", "woomelly") ).'</dd>';
																if ( !empty($result_meli['body']->cause) ) {
																	$message_finish .= ' (';
																	foreach ( $result_meli['body']->cause as $value ) {
																		$message_finish .= $value->code.': '.$value->message;
																	}
																	$message_finish .= ') ';
																}
																//Woomelly()->woomelly_set_log( $message_finish, 'last_sync' );
															break;
															case 'woomelly_automatic':
																$message_finish = 'error17';
																break;
															default:
																# code...
																break;
														}
														$wm_product->set_woomelly_sync_problem( true );
													} else {
														switch ($woo_type) {
															case 'woomelly_only':
																$message_finish = 'nook:::<span style="background: #fef4f6; color: #f0506e;">'.__("Connection error with Mercadolibre. Try again in a few seconds.", "woomelly").' <span class="dashicons dashicons-no-alt" style="color: red; vertical-align: middle;"></span></span>';
																break;
															case 'woomelly_manual':
																$message_finish .= '<dt><a style="color:red;" href="'.admin_url( "post.php?post=".$woo_id."&action=edit" ).'">'.$_product->get_name() . ' | ' . $wm_template_sync->get_format_id().'</a> <a href="'.$wm_product->get_woomelly_url_meli_field().'" target="_blank">'.__("(see)", "woomelly").'</a></dt><dd>'.__("Connection error with Mercadolibre. Try again in a few seconds.", "woomelly").'</dd>';
																//Woomelly()->woomelly_set_log( $message_finish, 'last_sync' );
															break;
															case 'woomelly_automatic':
																$message_finish = 'error18';
																break;
															default:
																# code...
																break;
														}
														$wm_product->set_woomelly_sync_problem( true );
													}
												}
											}
										} else {
											if ( !empty($result_meli_description) ) {
												switch ($woo_type) {
													case 'woomelly_only':
														$message_finish = 'nook:::<span style="background: #fef4f6; color: #f0506e;">'.( (isset($result_meli_description['body']->message))? $result_meli_description['body']->message : __("Connection error with Mercadolibre. Try again in a few seconds.", "woomelly") ).' <span class="dashicons dashicons-no-alt" style="color: red; vertical-align: middle;"></span></span><br>';
														if ( !empty($result_meli_description['body']->cause) ) {
															if ( !empty($result_meli_description['body']->cause) ) {
																$message_finish .= '<ul style="background: #fef4f6; color: #f0506e;">';
																foreach ( $result_meli_description['body']->cause as $value ) {
																	$message_finish .= '<li type="square" style="margin-left: 20px;">'.$value->code.':<br>'.$value->message.'</li>';
																}
																$message_finish .= '</ul>';
															}
														}
														break;
													case 'woomelly_manual':
														Woomelly()->woomelly_set_log( $result_meli_description['body'], 'last_sync' );
														$message_finish .= '<dt><a style="color:red;" href="'.admin_url( "post.php?post=".$woo_id."&action=edit" ).'">'.$_product->get_name() . ' | ' . $wm_template_sync->get_format_id().'</a> <a href="'.$wm_product->get_woomelly_url_meli_field().'" target="_blank">'.__("(see)", "woomelly").'</a></dt><dd>'.( (isset($result_meli_description['body']->message))? $result_meli_description['body']->message : __("Connection error with Mercadolibre. Try again in a few seconds.", "woomelly") ).'</dd>';
														if ( !empty($result_meli_description['body']->cause) ) {
															$message_finish .= ' (';
															foreach ( $result_meli_description['body']->cause as $value ) {
																$message_finish .= $value->code.': '.$value->message;
															}
															$message_finish .= ') ';
														}
														//Woomelly()->woomelly_set_log( $message_finish, 'last_sync' );
													break;
													case 'woomelly_automatic':
														$message_finish = 'error19';
														break;
													default:
														# code...
														break;
												}
												$wm_product->set_woomelly_sync_problem( true );
											}					
										}
									}
								}
							} else if ( empty($available_variations) ) {
								switch ($woo_type) {
									case 'woomelly_only':
										$message_finish = 'nook:::<span style="background: #fef4f6; color: #f0506e;">'.__("This product is variable but does not have any charged variation. Add variations or modify the product type to simple and add a price.", "woomelly").' <span class="dashicons dashicons-no-alt" style="color: red; vertical-align: middle;"></span></span>';
										break;
									case 'woomelly_manual':
										$message_finish .= '<dt><a style="color:red;" href="'.admin_url( "post.php?post=".$woo_id."&action=edit" ).'">'.$_product->get_name() . ' | ' . $wm_template_sync->get_format_id().'</a> <a href="'.$wm_product->get_woomelly_url_meli_field().'" target="_blank">'.__("(see)", "woomelly").'</a></dt><dd>'.__("This product is variable but does not have any charged variation. Add variations or modify the product type to simple and add a price.", "woomelly").'</dd>';
										//Woomelly()->woomelly_set_log( $message_finish, 'last_sync' );
									break;
									case 'woomelly_automatic':
										$message_finish = 'error20';
										break;
									default:
										# code...
										break;
								}
								$wm_product->set_woomelly_sync_problem( true );								
							} else {
								switch ($woo_type) {
									case 'woomelly_only':
										$message_finish = 'nook:::<span style="background: #fef4f6; color: #f0506e;">'.__("This product has variations but they are not configured with Mercadolibre nor does it have the option of individual active products in its connection template. Check which one best suits your needs and re-synchronize that product.", "woomelly").' <span class="dashicons dashicons-no-alt" style="color: red; vertical-align: middle;"></span></span>';
										break;
									case 'woomelly_manual':
										$message_finish .= '<dt><a style="color:red;" href="'.admin_url( "post.php?post=".$woo_id."&action=edit" ).'">'.$_product->get_name() . ' | ' . $wm_template_sync->get_format_id().'</a> <a href="'.$wm_product->get_woomelly_url_meli_field().'" target="_blank">'.__("(see)", "woomelly").'</a></dt><dd>'.__("This product has variations but they are not configured with Mercadolibre nor does it have the option of individual active products in its connection template. Check which one best suits your needs and re-synchronize that product.", "woomelly").'</dd>';
										//Woomelly()->woomelly_set_log( $message_finish, 'last_sync' );
									break;
									case 'woomelly_automatic':
										$message_finish = 'error21';
										break;
									default:
										# code...
										break;
								}
								$wm_product->set_woomelly_sync_problem( true );								
							}
						} else {
							switch ($woo_type) {
								case 'woomelly_only':
									$message_finish = 'nook:::<span style="background: #fef4f6; color: #f0506e;">'.__("The product can not be synchronized because it is pending payment. Go to your Mercadolibre account.", "woomelly").' <span class="dashicons dashicons-no-alt" style="color: red; vertical-align: middle;"></span></span>';
									break;
								case 'woomelly_manual':
									$message_finish .= '<dt><a style="color:red;" href="'.admin_url( "post.php?post=".$woo_id."&action=edit" ).'">'.$_product->get_name() . ' | ' . $wm_template_sync->get_format_id().'</a> <a href="'.$wm_product->get_woomelly_url_meli_field().'" target="_blank">'.__("(see)", "woomelly").'</a></dt><dd>'.__("The product can not be synchronized because it is pending payment. Go to your Mercadolibre account.", "woomelly").'</dd>';
									//Woomelly()->woomelly_set_log( $message_finish, 'last_sync' );
								break;
								case 'woomelly_automatic':
									$message_finish = 'error22';
									break;
								default:
									# code...
									break;
							}
							$wm_product->set_woomelly_sync_problem( true );
						}
					} else {
						switch ($woo_type) {
							case 'woomelly_only':
								$message_finish = 'nook:::<span style="background: #fef4f6; color: #f0506e;">'.__("The product is not active for synchronization.", "woomelly").' <span class="dashicons dashicons-no-alt" style="color: red; vertical-align: middle;"></span></span>';
								break;
							case 'woomelly_manual':
								$message_finish .= '<dt><a style="color:red;" href="'.admin_url( "post.php?post=".$woo_id."&action=edit" ).'">'.$_product->get_name() . ' | ' . $wm_template_sync->get_format_id().'</a> <a href="'.$wm_product->get_woomelly_url_meli_field().'" target="_blank">'.__("(see)", "woomelly").'</a></dt><dd>'.__("The product is not active for synchronization.", "woomelly").'</dd>';
								//Woomelly()->woomelly_set_log( $message_finish, 'last_sync' );
							break;
							case 'woomelly_automatic':
								$message_finish = 'error23';
								break;
							default:
								# code...
								break;
						}
						$wm_product->set_woomelly_sync_problem( true );
					}
				} else {
					switch ($woo_type) {
						case 'woomelly_only':
							$message_finish = 'nook:::<span style="background: #fef4f6; color: #f0506e;">'.__("The product is not active for synchronization.", "woomelly").' <span class="dashicons dashicons-no-alt" style="color: red; vertical-align: middle;"></span></span>';
							break;
						case 'woomelly_manual':
							$message_finish .= '<dt><a style="color:red;" href="'.admin_url( "post.php?post=".$woo_id."&action=edit" ).'">'.$_product->get_name() . ' | ' . $wm_template_sync->get_format_id().'</a> <a href="'.$wm_product->get_woomelly_url_meli_field().'" target="_blank">'.__("(see)", "woomelly").'</a></dt><dd>'.__("The product is not active for synchronization.", "woomelly").'</dd>';
							//Woomelly()->woomelly_set_log( $message_finish, 'last_sync' );
							break;
						case 'woomelly_automatic':
							$message_finish = 'error24';
							break;							
						default:
							# code...
							break;
					}
					$wm_product->set_woomelly_sync_problem( true );
				}
			} else {
				switch ($woo_type) {
					case 'woomelly_only':
						$message_finish = 'nook:::<span style="background: #fef4f6; color: #f0506e;">'.__("The product code could not be obtained.", "woomelly").' <span class="dashicons dashicons-no-alt" style="color: red; vertical-align: middle;"></span></span>';
						break;
					case 'woomelly_manual':
						$message_finish .= '<dt><a style="color:red;" href="'.admin_url( "post.php?post=".$woo_id."&action=edit" ).'">'.sprintf( __("Invalid Product", "woomelly"), $woo_id).'</a></dt><dd>'.__("The product code could not be obtained.", "woomelly").'</dd>';
						//Woomelly()->woomelly_set_log( $message_finish, 'last_sync' );
						break;
					case 'woomelly_automatic':
						$message_finish = 'error25';
						break;
					default:
						# code...
						break;
				}
			}
			
			if ( $woo_type == 'woomelly_automatic' ) {
				return $message_finish;
			} else {
				echo $message_finish;
			}
		} //End woomelly_sync_product_to_meli()
	}
}