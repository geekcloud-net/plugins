<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'WMAdminSettings', false ) ) {
	return;
}

/**
 * WMAdminSettings Class.
 */
class WMAdminSettings {

	public static function output() {
        $user = array();
        $get_me_user = array();
        $currencies = array();
        $currencies_temp = array();
        $site_id = array();
        $all_data_category = array();
        $sites_id = WMeli::get_sites_id();
        $url = WMeli::auth_url();
        $webhook_url = Woomelly()->get_webhook_url();
        $saved = false;
        
        if ( isset($_POST['wm_settings_page_submit']) && $_POST['wm_settings_page_submit']!="" ) {
            $wm_settings_page = new WMSettings();
            if ( isset($_POST['wm_settings_page_app_id']) )
                $wm_settings_page->set_app_id( $_POST['wm_settings_page_app_id'] );
            if ( isset($_POST['wm_settings_page_secret_key']) )
                $wm_settings_page->set_secret_key( $_POST['wm_settings_page_secret_key'] );
            if ( isset($_POST['wm_settings_page_site_id']) )
                $wm_settings_page->set_site_id( $_POST['wm_settings_page_site_id'] );
            $wm_settings_page->save();
            $saved = true;
        } else if ( isset($_POST['wm_settings_page_notification_submit']) && $_POST['wm_settings_page_notification_submit']!="" ) {
            $wm_settings_page = new WMSettings();
            if ( isset($_POST['wm_settings_refresh_token']) ) {
                $wm_settings_page->set_settings_refresh_token( true );
                if ( isset($_POST['wm_settings_refresh_token_email']) && $_POST['wm_settings_refresh_token_email'] != "" ) {
                    $wm_settings_page->set_settings_refresh_token_email( $_POST['wm_settings_refresh_token_email'] );
                }
            } else {
                $wm_settings_page->set_settings_refresh_token( false );
            }            
            if ( isset($_POST['wm_settings_notification_sync']) ) {
                $wm_settings_page->set_settings_notification_sync( true );
                if ( isset($_POST['wm_settings_notification_sync_email']) && $_POST['wm_settings_notification_sync_email'] != "" ) {
                    $wm_settings_page->set_settings_notification_sync_email( $_POST['wm_settings_notification_sync_email'] );
                }
            } else {
                $wm_settings_page->set_settings_notification_sync( false );
            }
            if ( isset($_POST['wm_settings_sync_automatic']) ) {
                $wm_settings_page->set_settings_sync_automatic( true );
                if ( isset($_POST['wm_settings_sync_automatic_time']) && $_POST['wm_settings_sync_automatic_time'] != "" ) {
                    $wm_settings_page->set_settings_sync_automatic_time( $_POST['wm_settings_sync_automatic_time'] );
                }
            } else {
                $wm_settings_page->set_settings_sync_automatic( false );
            }
            $wm_settings_page->save();
            $saved = true;
        } else if ( isset($_POST['wm_settings_page_publish_submit']) && $_POST['wm_settings_page_publish_submit']!="" ) {
            $wm_settings_page = new WMSettings();
            if ( isset($_POST['wm_settings_page_currency_id']) && $_POST['wm_settings_page_currency_id']!="" ) {
                $wm_settings_page->set_settings_currency_id( $_POST['wm_settings_page_currency_id'] );
            }
            if ( isset($_POST['wm_settings_page_template']) && $_POST['wm_settings_page_template']!="" ) {
                $wm_settings_page->set_settings_template( $_POST['wm_settings_page_template'] );
            }
            if ( isset($_POST['wm_settings_format_template']) && $_POST['wm_settings_format_template']!="" ) {
                $wm_settings_page->set_settings_format_template( $_POST['wm_settings_format_template'] );
            }
            $settings_omit_fields = array();
            if ( isset($_POST['wm_settings_omit_fields_title']) ) {
                $settings_omit_fields[0] = true;
            } else {
                $settings_omit_fields[0] = false;
            }
            if ( isset($_POST['wm_settings_omit_fields_description']) ) {
                $settings_omit_fields[1] = true;
            } else {
                $settings_omit_fields[1] = false;
            }
            if ( isset($_POST['wm_settings_omit_fields_pictures']) ) {
                $settings_omit_fields[2] = true;
            } else {
                $settings_omit_fields[2] = false;
            }
            if ( isset($_POST['wm_settings_omit_fields_stock']) ) {
                $settings_omit_fields[3] = true;
            } else {
                $settings_omit_fields[3] = false;
            }
            if ( isset($_POST['wm_settings_omit_fields_sku']) ) {
                $settings_omit_fields[4] = true;
            } else {
                $settings_omit_fields[4] = false;
            }
            $wm_settings_page->set_settings_omit_fields( $settings_omit_fields );
            $wm_settings_page->save();
            $saved = true;
        }

        if ( $saved == true ) {
            wm_print_alert( __("The changes were stored correctly.", "woomelly") );
        }
        if ( isset($_GET['code']) && $_GET['code'] != "" ) {
            $woomelly_get_settings = new WMSettings();
            $user = WMeli::authorize( $_GET['code'] );
            if ( !empty($user) ) {
                $woomelly_get_settings->set_access_token( $user->access_token );
                $woomelly_get_settings->set_expires_in( time() + $user->expires_in );
                $woomelly_get_settings->set_refresh_token( $user->refresh_token );
                $woomelly_get_settings->save();
                $get_me_user = WMeli::get_me();
                if ( !empty($get_me_user) ) {
                    $woomelly_get_settings->set_user_id( $get_me_user->id );
                    $woomelly_get_settings->set_permalink( $get_me_user->permalink );
                    $woomelly_get_settings->save();
                } else {
                    $woomelly_get_settings->set_access_token( '' );
                    $woomelly_get_settings->set_expires_in( '' );
                    $woomelly_get_settings->set_refresh_token( '' );
                    $woomelly_get_settings->save();
                    echo "
                        <div class='notice notice-error is-dismissible'>
                            <p><strong>".__( 'There is a problem obtaining the users values. Try again or write to makeplugins@gmail.com', 'woomelly')."</strong></p>
                        </div>";
                }
            }
            unset( $woomelly_get_settings );
        }     
        $woomelly_get_settings = new WMSettings();
        $wm_settings_page_app_id = $woomelly_get_settings->get_app_id();
        $wm_settings_page_secret_key = $woomelly_get_settings->get_secret_key();
        $wm_settings_page_site_id = $woomelly_get_settings->get_site_id();
        $wm_settings_page_access_token = $woomelly_get_settings->get_access_token();
        $wm_settings_refresh_token = $woomelly_get_settings->get_settings_refresh_token();
        $wm_settings_refresh_token_email = $woomelly_get_settings->get_settings_refresh_token_email();
        $wm_settings_notification_sync = $woomelly_get_settings->get_settings_notification_sync();
        $wm_settings_notification_sync_email = $woomelly_get_settings->get_settings_notification_sync_email();
        $wm_settings_sync_automatic = $woomelly_get_settings->get_settings_sync_automatic();
        $wm_settings_sync_automatic_time = $woomelly_get_settings->get_settings_sync_automatic_time();
        $wm_settings_page_currency_id = $woomelly_get_settings->get_settings_currency_id();
        $wm_settings_page_template = $woomelly_get_settings->get_settings_template();
        $wm_settings_omit_fields = $woomelly_get_settings->get_settings_omit_fields();
        $woomelly_alive = Woomelly()->woomelly_is_connect();
        $tags_available = $woomelly_get_settings->get_tags_available('string');
        $wm_settings_format_template = $woomelly_get_settings->get_settings_format_template();
        if ( $wm_settings_page_site_id != "" ) {
            $site_id = WMeli::get_sites_id( $wm_settings_page_site_id );
            if ( !empty($site_id) ) {
                $currencies_temp = $site_id->currencies;
                if ( !empty($currencies_temp) ) {
                    $get_currencies = array();
                    foreach ( $currencies_temp as $value ) {
                        if ( $wm_settings_page_currency_id == "" ) {
                            $woomelly_get_settings->set_settings_currency_id( $value->id );
                            $woomelly_get_settings->save();
                            $wm_settings_page_currency_id = $value->id;
                        }
                        $get_currencies = WMeli::get_currencies( $value->id );
                        if ( !empty($get_currencies) ) {
                            $currencies[] = $get_currencies;
                        }
                    }
                }
            }
        }
        $is_connect = Woomelly()->woomelly_is_connect();
        $l_is_ok = Woomelly()->woomelly_status();
        
        include_once( Woomelly()->get_dir() . '/template/admin/settings.php' );
	}
}