<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'WMAdminDashboard', false ) ) {
	return;
}

/**
 * WMAdminDashboard Class.
 */
class WMAdminDashboard {
	
    public static function output() {
        $woomelly_get_settings          = array();
        $get_me_user_temp               = array();
        $get_me_user_applications       = array();
        $get_me_user_address            = array();
        $get_me_meli                    = '';
        $get_me_user                    = '';
        $get_me_user_transactions       = '';
        $permalink                      = '';
        $l_is_ok                        = Woomelly()->woomelly_status();
        $is_connect                     = Woomelly()->woomelly_is_connect();

        $get_me_meli .= '
            <li>
                <strong>'.__("Connection", "woomelly").':</strong>
                <a href="'.admin_url( "admin.php?page=woomelly-settings" ).'">
                    <span class="uk-label '.( ($is_connect)? "uk-label-success" : "uk-label-danger" ).'">'.( ($is_connect)? __("Active", "woomelly") : __("Inactive", "woomelly") ).'</span>
                </a>
            </li>';
        $get_me_user .= $get_me_meli;
        $get_me_user_transactions .= $get_me_meli;
        $get_me_user_temp = WMeli::get_me();
        if ( !empty($get_me_user_temp) ) {
            $permalink .= '
                <a href="'.$get_me_user_temp->permalink.'" target="_blank">
                    <span class="uk-badge">'.__("see", "woomelly").'</span>
                </a>';
            $get_me_meli .= '
                <li>
                    <strong>'.__("user points", "woomelly").':</strong>
                    <span class="uk-label uk-label-primary">'.$get_me_user_temp->points.'</span>
                </li>';
            $get_me_user_applications = WMeli::get_applications();
            if ( !empty($get_me_user_applications) ) {
                $get_me_meli .= '
                    <li>
                        <strong>'.__("app url", "woomelly").':</strong> '.$get_me_user_applications->url.'
                    </li>';
                $get_me_meli .= '
                    <li>
                        <strong>'.__("app callback url", "woomelly").':</strong> '.$get_me_user_applications->callback_url.'
                    </li>';
                $get_me_meli .= '
                    <li>
                        <strong>'.__("app max requests by hours", "woomelly").':</strong> '.$get_me_user_applications->max_requests_per_hour.'
                    </li>';
                $get_me_meli .= '
                    <li>
                        <strong>'.__("app scopes", "woomelly").':</strong> '.implode(", ", $get_me_user_applications->scopes).'
                    </li>';
                $get_me_meli .= '
                    <li>
                        <strong>'.__("app notifications callback url", "woomelly").':</strong> '.$get_me_user_applications->notifications_callback_url.'
                    </li>';
                $get_me_meli .= '
                    <li>
                        <strong>'.__("app notifications topics", "woomelly").':</strong> '.implode(", ", $get_me_user_applications->notifications_topics).'
                    </li>';
            }
            $get_me_user .= '
                <li>
                    <strong>'.__("id", "woomelly").':</strong> '.$get_me_user_temp->id.'
                </li>';
            $get_me_user .= '
                <li>
                    <strong>'.__("nickname", "woomelly").':</strong> '.$get_me_user_temp->nickname.'
                </li>';
            $get_me_user .= '
                <li>
                    <strong>'.__("registration date", "woomelly").':</strong> '.( ($get_me_user_temp->registration_date!="")? gmdate("Y-m-d H:i:s", strtotime($get_me_user_temp->registration_date)) : "" ).'
                </li>';
            $get_me_user .= '
                <li>
                    <strong>'.__("full name", "woomelly").':</strong> '.$get_me_user_temp->first_name.' '.$get_me_user_temp->last_name.'
                </li>';
            //$get_me_user .= '<li><strong>'.__("gender", "woomelly").':</strong> '.$get_me_user_temp['body']->gender.'</li>';
            //$get_me_user .= '<li><strong>'.__("country id", "woomelly").':</strong> '.$get_me_user_temp['body']->country_id.'</li>';
            $get_me_user .= '
                <li>
                    <strong>'.__("email", "woomelly").':</strong> '.$get_me_user_temp->email.'
                </li>';
            $get_me_user .= '
                <li>
                    <strong>'.__("email secure", "woomelly").':</strong> '.$get_me_user_temp->secure_email.'
                </li>';
            $get_me_user .= '
                <li>
                    <strong>'.__("identification", "woomelly").':</strong> '.$get_me_user_temp->identification->type.' - '.$get_me_user_temp->identification->number.'
                </li>';         
            $get_me_user_address = WMeli::get_me( 'addresses' );
            if ( !empty($get_me_user_address) ) {
                $enter = false;
                $aa = 1;
                foreach ( $get_me_user_address as $value_address ) {
                    if ( $enter == false ) {
                        $get_me_user .= '
                            <li>
                                <strong>'.sprintf( __( "address %s", "woomelly"), $aa ).':</strong> '.$value_address->address_line.__( ", (zip)", "woomelly").$value_address->zip_code.', '.$value_address->city->name.', '.$value_address->state->name.', '.$value_address->country->name.__( ". (type)", "woomelly").implode(", ", $value_address->types).'
                            </li>';
                        $enter = true;
                    } else {
                        $get_me_user .= '
                            <li>
                                <strong>'.sprintf( __( "address %s", "woomelly"), $aa ).':</strong> '.$value_address->address_line.__( ", (zip)", "woomelly").$value_address->zip_code.', '.$value_address->city->name.', '.$value_address->state->name.', '.$value_address->country->name.__( ". (type)", "woomelly").implode(", ", $value_address->types).'
                            </li>';
                    }
                    $aa++;
                }
            }
            $get_me_user .= '
                <li>
                    <strong>'.__("phone", "woomelly").':</strong> ('.$get_me_user_temp->phone->area_code.') '.$get_me_user_temp->phone->number.' / ('.$get_me_user_temp->alternative_phone->area_code.') '.$get_me_user_temp->alternative_phone->number.'
                </li>';
            $get_me_user .= '
                <li>
                    <strong>'.__("user type", "woomelly").':</strong> '.$get_me_user_temp->user_type.'
                </li>';
            $get_me_user .= '
                <li>
                    <strong>'.__("shipping modes", "woomelly").':</strong> '.implode(', ', $get_me_user_temp->shipping_modes).'
                </li>';
            $get_me_user_transactions .= '
                <li>
                    <strong>'.__("REPUTATION OF THE SELLER", "woomelly").'</strong>
                </li>';
            $get_me_user_transactions .= '
                <li>
                    <strong>'.__("level id", "woomelly").':</strong> '.$get_me_user_temp->seller_reputation->level_id.'
                </li>';
            $get_me_user_transactions .= '
                <li>
                    <strong>'.__("cancelled", "woomelly").':</strong> '.$get_me_user_temp->seller_reputation->transactions->canceled.' / '.($get_me_user_temp->seller_reputation->transactions->total).'
                </li>';
            $get_me_user_transactions .= '
                <li>
                    <strong>'.__("completed", "woomelly").':</strong> '.$get_me_user_temp->seller_reputation->transactions->completed.' / '.($get_me_user_temp->seller_reputation->transactions->total).'
                </li>';
            $get_me_user_transactions .= '
                <li>
                    <strong>'.__("negative", "woomelly").':</strong> '.$get_me_user_temp->seller_reputation->transactions->ratings->negative.'
                </li>';
            $get_me_user_transactions .= '
                <li>
                    <strong>'.__("neutral", "woomelly").':</strong> '.$get_me_user_temp->seller_reputation->transactions->ratings->neutral.'
                </li>';
            $get_me_user_transactions .= '
                <li>
                    <strong>'.__("positive", "woomelly").':</strong> '.$get_me_user_temp->seller_reputation->transactions->ratings->positive.'
                </li>';
            $get_me_user_transactions .= '<li></li>';
            $get_me_user_transactions .= '
                <li>
                    <strong>'.__("REPUTATION OF THE BUYER", "woomelly").'</strong>
                </li>';
            $get_me_user_transactions .= '
                <li>
                    <strong>'.__("canceled transactions", "woomelly").':</strong> '.$get_me_user_temp->buyer_reputation->canceled_transactions.'
                </li>';
            $get_me_user_transactions .= '
                <li>
                    <strong>'.__("canceled (paid / total)", "woomelly").':</strong> '.$get_me_user_temp->buyer_reputation->transactions->canceled->paid.'/'.$get_me_user_temp->buyer_reputation->transactions->canceled->total.'
                </li>';
            $get_me_user_transactions .= '
                <li>
                    <strong>'.__("completed", "woomelly").':</strong> '.$get_me_user_temp->buyer_reputation->transactions->completed.'
                </li>';
            $get_me_user_transactions .= '
                <li>
                    <strong>'.__("not yet qualified (paid / total)", "woomelly").':</strong> '.$get_me_user_temp->buyer_reputation->transactions->not_yet_rated->paid.'/'.$get_me_user_temp->buyer_reputation->transactions->not_yet_rated->total.'
                </li>';
            $get_me_user_transactions .= '
                <li>
                    <strong>'.__("unqualified (paid / total)", "woomelly").':</strong> '.$get_me_user_temp->buyer_reputation->transactions->unrated->paid.'/'.$get_me_user_temp->buyer_reputation->transactions->unrated->total.'
                </li>';
            $get_me_user .= '</ul>';
        }
        
        include_once( Woomelly()->get_dir() . '/template/admin/dashboard.php' );
    }
}