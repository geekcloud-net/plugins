<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'WMAdminTemplateSync', false ) ) {
	return;
}

/**
 * WMAdminTemplateSync Class.
 */
class WMAdminTemplateSync {
	
	public static function output() {
        $l_is_ok                                     = Woomelly()->woomelly_status();
        $woomelly_alive                              = Woomelly()->woomelly_is_connect();
        $shipping_modes_available_temp               = WMeli::get_me();
        $templatesyncsingle                          = false;
        $woomelly_accepts_mercadopago_field          = false;
        $error_save                                  = false;
        $woomelly_shipping_dimensions_field          = true;
        $woomelly_shipping_local_pick_up_field       = true;
        $woomelly_shipping_free_shipping_field       = true;
        $categories                                  = array();
        $all_data_category                           = array();
        $shipping_modes_id                           = array();
        $woomelly_custom_shipping_cost               = array();
        $listing_type_id                             = array();
        $shipping_modes_available                    = array();
        $all_location_country                        = array();
        $all_location_state                          = array();
        $all_location_city                           = array();
        $woomelly_attributes                         = array();
        $shipping_modes_string                       = '';
        $path_from_root                              = '';
        $woomelly_category_field                     = '';
        $woomelly_category_name_field                = '';
        $woomelly_buying_mode_field                  = '';
        $woomelly_listing_type_id_field              = '';
        $woomelly_condition_field                    = '';
        $woomelly_shipping_mode_field                = '';
        $woomelly_shipping_accepted_methods_field    = '';
        $woomelly_title_field                        = '';
        $woomelly_official_store_id_field            = '';
        $woomelly_status_field                       = '';
        $woomelly_price_field                        = '';
        $woomelly_stock_field                        = '';
        $woomelly_price_one_field                    = '';
        $woomelly_price_two_field                    = '';
        $woomelly_price_three_field                  = '';
        $woomelly_stock_one_field                    = '';
        $woomelly_stock_two_field                    = '';
        $woomelly_stock_three_field                  = '';
        $woomelly_seller_custom_field_field          = '';
        $woomelly_video_id_field                     = '';
        $woomelly_warranty_field                     = '';
        $woomelly_description_field                  = '';
        $all_categories                              = '';
        $woomelly_location_country_field             = '';
        $woomelly_location_state_field               = '';
        $woomelly_location_city_field                = '';
        $required_allow_variations                   = '';
        $woomelly_separate_variations_field          = '';
        $products_by_template                        = array();
        $woomelly_name_template_field                = '';
        
        $action = ( (isset($_GET['action']) && $_GET['action']!="")? $_GET['action'] : '' ) ;
        $template_id = ( (isset($_GET['woomelly_template_id']) && $_GET['woomelly_template_id']!="")? absint($_GET['woomelly_template_id']) : 0 ) ;
        
        if ( $template_id == 0 ) {
            $template_id = ( (isset($_POST['woomelly_template_id']) && $_POST['woomelly_template_id']!="")? absint($_POST['woomelly_template_id']) : 0 ) ;
        }
        
        if ( !empty($shipping_modes_available_temp) ) {
            $shipping_modes_available = $shipping_modes_available_temp->shipping_modes; 
        }            
        
        if ( isset($_POST['wm_templatesync_page_submit']) && $_POST['wm_templatesync_page_submit']!="" ) {

            if ( $action == "add" ) {
                $wm_template = new WMTemplateSync();
            } else {
                $wm_template = wm_get_template_sync( $template_id );
                if ( !is_object($wm_template) ) {
                    $wm_template = new WMTemplateSync();
                }
            }
            if ( !isset($_POST['woomelly_category_field']) || $_POST['woomelly_category_field']=="" ) {
                $error_save = true;
            } else {
                $wm_template->set_woomelly_category_field( $_POST['woomelly_category_field'] );
                if ( isset($_POST['woomelly_category_name_field']) && $_POST['woomelly_category_name_field']!="" ) {
                    $wm_template->set_woomelly_category_name_field( $_POST['woomelly_category_name_field'] );
                } else {
                    $wm_template->set_woomelly_category_name_field( $_POST['woomelly_category_field'] );
                }
            }
            if ( isset($_POST['woomelly_name_template_field']) && $_POST['woomelly_name_template_field']!="" && $error_save == false ) {
                $wm_template->set_woomelly_name_template_field( $_POST['woomelly_name_template_field'] );
            }
            if ( isset($_POST['woomelly_buying_mode_field']) && $_POST['woomelly_buying_mode_field']!="" && $error_save == false ) {
                $wm_template->set_woomelly_buying_mode_field( $_POST['woomelly_buying_mode_field'] );
            }
            if ( isset($_POST['woomelly_listing_type_id_field']) && $_POST['woomelly_listing_type_id_field']!="" && $error_save == false ) {
                $wm_template->set_woomelly_listing_type_id_field( $_POST['woomelly_listing_type_id_field'] );
            }
            if ( isset($_POST['woomelly_condition_field']) && $_POST['woomelly_condition_field']!="" && $error_save == false ) {
                $wm_template->set_woomelly_condition_field( $_POST['woomelly_condition_field'] );
            }
            if ( isset($_POST['woomelly_accepts_mercadopago_field']) && $_POST['woomelly_accepts_mercadopago_field']!="" ) {
                $wm_template->set_woomelly_accepts_mercadopago_field( true );
            } else {
                $wm_template->set_woomelly_accepts_mercadopago_field( false );
            }
            if ( isset($_POST['woomelly_shipping_mode_field']) && $_POST['woomelly_shipping_mode_field']!="" && $error_save == false ) {
                $woomelly_shipping_mode_field_temp = $_POST['woomelly_shipping_mode_field'];
                switch ( $woomelly_shipping_mode_field_temp ) {
                    case 'custom':
                        if ( isset($_POST['woomelly_custom_shipping_cost_description_field']) && isset($_POST['woomelly_custom_shipping_cost_cost_field']) ) {
                            $woomelly_custom_shipping_cost_description_field = $_POST['woomelly_custom_shipping_cost_description_field'];
                            $woomelly_custom_shipping_cost_cost_field = $_POST['woomelly_custom_shipping_cost_cost_field'];
                            $woomelly_custom_shipping_cost = array();
                            if ( !empty($woomelly_custom_shipping_cost_description_field) && !empty($woomelly_custom_shipping_cost_cost_field) ) {
                                $xx = 0;
                                foreach ( $woomelly_custom_shipping_cost_description_field as $value ) {
                                    if ( isset($woomelly_custom_shipping_cost_cost_field[$xx]) && $value!="" && $woomelly_custom_shipping_cost_cost_field[$xx]!="" ) {
                                        $woomelly_custom_shipping_cost[] = $value . "::" . $woomelly_custom_shipping_cost_cost_field[$xx];
                                    }
                                    $xx++;
                                }
                                if ( !empty($woomelly_custom_shipping_cost) ) {
                                    $wm_template->set_woomelly_shipping_mode_field( 'custom' );
                                    $wm_template->set_woomelly_custom_shipping_cost_field( $woomelly_custom_shipping_cost );
                                } else {
                                    $wm_template->set_woomelly_shipping_mode_field( 'not_specified' );
                                    $wm_template->set_woomelly_custom_shipping_cost_field( array() );
                                }
                            } else {
                                $wm_template->set_woomelly_shipping_mode_field( 'not_specified' );
                                $wm_template->set_woomelly_custom_shipping_cost_field( array() );
                            }
                        } else {
                            $wm_template->set_woomelly_shipping_mode_field( 'not_specified' );
                            $wm_template->set_woomelly_custom_shipping_cost_field( array() );
                        }
                        $wm_template->set_woomelly_shipping_accepted_methods_field( '' );
                        break;
                    case 'me1':
                        $wm_template->set_woomelly_shipping_mode_field( 'me1' );
                        $wm_template->set_woomelly_custom_shipping_cost_field( array() );
                        if ( isset($_POST['woomelly_shipping_accepted_methods_field']) && $_POST['woomelly_shipping_accepted_methods_field']!="" ) {
                            $wm_template->set_woomelly_shipping_accepted_methods_field( $_POST['woomelly_shipping_accepted_methods_field'] );
                        }
                        break;
                    case 'me2':
                        $wm_template->set_woomelly_shipping_mode_field( 'me2' );
                        $wm_template->set_woomelly_custom_shipping_cost_field( array() );
                        if ( isset($_POST['woomelly_shipping_accepted_methods_field']) && $_POST['woomelly_shipping_accepted_methods_field']!="" ) {
                            $wm_template->set_woomelly_shipping_accepted_methods_field( $_POST['woomelly_shipping_accepted_methods_field'] );
                        }
                        break;
                    default:
                        $wm_template->set_woomelly_shipping_mode_field( 'not_specified' );
                        $wm_template->set_woomelly_custom_shipping_cost_field( array() );
                        $wm_template->set_woomelly_shipping_accepted_methods_field( '' );
                        break;
                }
            }
            if ( isset($_POST['woomelly_shipping_local_pick_up_field']) && $_POST['woomelly_shipping_local_pick_up_field']!="" ) {
                $wm_template->set_woomelly_shipping_local_pick_up_field( true );
            } else {
                $wm_template->set_woomelly_shipping_local_pick_up_field( false );
            }
            if ( isset($_POST['woomelly_shipping_free_shipping_field']) && $_POST['woomelly_shipping_free_shipping_field']!="" ) {
                $wm_template->set_woomelly_shipping_free_shipping_field( true );
            } else {
                $wm_template->set_woomelly_shipping_free_shipping_field( false );
            }
            if ( isset($_POST['woomelly_shipping_dimensions_field']) && $_POST['woomelly_shipping_dimensions_field']!="") {
                $wm_template->set_woomelly_shipping_dimensions_field( true );
            } else {
                $wm_template->set_woomelly_shipping_dimensions_field( false );
            }
            if ( isset($_POST['woomelly_status_field']) && $error_save == false ) {
                $wm_template->set_woomelly_status_field( $_POST['woomelly_status_field'] );
            }
            if ( isset($_POST['woomelly_title_field']) && $error_save == false ) {
                $wm_template->set_woomelly_title_field( $_POST['woomelly_title_field'] );
            }
            if ( isset($_POST['woomelly_description_field']) && $error_save == false ) {
                $wm_template->set_woomelly_description_field( $_POST['woomelly_description_field'] );
            }
            if ( isset($_POST['woomelly_official_store_id_field']) && $error_save == false ) {
                $wm_template->set_woomelly_official_store_id_field( $_POST['woomelly_official_store_id_field'] );
            }
            if ( isset($_POST['woomelly_price_one_field']) && isset($_POST['woomelly_price_two_field']) && isset($_POST['woomelly_price_three_field']) && $error_save == false ) {
                if ( $_POST['woomelly_price_one_field'] == "" ) {
                    $wm_template->set_woomelly_price_field( implode("::", array("", "", "")) );
                } else {
                    $wm_template->set_woomelly_price_field( implode("::", array($_POST['woomelly_price_one_field'], $_POST['woomelly_price_two_field'], $_POST['woomelly_price_three_field'])) );
                }
            }
            if ( isset($_POST['woomelly_stock_one_field']) && isset($_POST['woomelly_stock_two_field']) && isset($_POST['woomelly_stock_three_field']) && $error_save == false ) {
                if ( $_POST['woomelly_stock_one_field'] == "" ) {
                    $wm_template->set_woomelly_stock_field( implode("::", array("", "", "")) );
                } else {
                    $wm_template->set_woomelly_stock_field( implode("::", array($_POST['woomelly_stock_one_field'], $_POST['woomelly_stock_two_field'], $_POST['woomelly_stock_three_field'])) );
                }
            }
            if ( isset($_POST['woomelly_seller_custom_field']) && $error_save == false ) {
                $wm_template->set_woomelly_seller_custom_field( $_POST['woomelly_seller_custom_field'] );
            }
            if ( isset($_POST['woomelly_video_id_field']) && $error_save == false ) {
                $wm_template->set_woomelly_video_id_field( $_POST['woomelly_video_id_field'] );
            }
            if ( isset($_POST['woomelly_warranty_field']) && $error_save == false ) {
                $wm_template->set_woomelly_warranty_field( $_POST['woomelly_warranty_field'] );
            }
            if ( isset($_POST['woomelly_location_country_field']) && $error_save == false ) {
                $wm_template->set_woomelly_location_country_field( $_POST['woomelly_location_country_field'] );
            }
            if ( isset($_POST['woomelly_location_state_field']) && $error_save == false ) {
                $wm_template->set_woomelly_location_state_field( $_POST['woomelly_location_state_field'] );
            }
            if ( isset($_POST['woomelly_location_city_field']) && $error_save == false ) {
                $wm_template->set_woomelly_location_city_field( $_POST['woomelly_location_city_field'] );
            }
            if ( isset($_POST['woomelly_separate_variations_field']) && $error_save == false ) {
                $wm_template->set_woomelly_separate_variations_field( $_POST['woomelly_separate_variations_field'] );
            }
            if ( $error_save == false ) {
                $template_id = $wm_template->save();
                if ( $template_id > 0 ) {
                    $action = "edit";
                    wm_print_alert( __("Changes stored correctly.", "woomelly") );
                } else {
                    wm_print_alert( __("Sorry, there was a problem with storing the data.", "woomelly"), 'danger' );
                }
            } else {
                wm_print_alert( __("Sorry, there was a problem with storing the data.", "woomelly"), 'danger' );
            }
        } else if ( isset($_POST['wm_templatesync_page_submit_delete_security']) && $_POST['wm_templatesync_page_submit_delete_security']=="delete" ) {
            WMTemplateSync::delete( $template_id );
            wm_print_alert( __("Templates successfully deleted.", "woomelly") );
            $action = "add";
        }
        
        if ( $action=="edit" && $template_id > 0 ) {
            $wm_template_sync = wm_get_template_sync( $template_id );
            if ( is_object($wm_template_sync) ) {
                $templatesyncsingle = true;
                $woomelly_name_template_field = $wm_template_sync->get_woomelly_name_template_field();
                $woomelly_category_field = $wm_template_sync->get_woomelly_category_field();
                $woomelly_category_name_field = $wm_template_sync->get_woomelly_category_name_field();
                $listing_type_id = WMeli::get_available_listing_types( $woomelly_category_field );
                $woomelly_buying_mode_field = $wm_template_sync->get_woomelly_buying_mode_field();
                $woomelly_listing_type_id_field = $wm_template_sync->get_woomelly_listing_type_id_field();
                $woomelly_condition_field = $wm_template_sync->get_woomelly_condition_field();
                $woomelly_accepts_mercadopago_field = $wm_template_sync->get_woomelly_accepts_mercadopago_field();
                $woomelly_shipping_mode_field = $wm_template_sync->get_woomelly_shipping_mode_field();
                $woomelly_location_country_field = $wm_template_sync->get_woomelly_location_country_field();
                $woomelly_location_state_field = $wm_template_sync->get_woomelly_location_state_field();
                $woomelly_location_city_field = $wm_template_sync->get_woomelly_location_city_field();
                $woomelly_separate_variations_field = $wm_template_sync->get_woomelly_separate_variations_field();
                $woomelly_shipping_accepted_methods_field = $wm_template_sync->get_woomelly_shipping_accepted_methods_field();
                $products_by_template = $wm_template_sync->get_products();
                
                $all_location_country = WMeli::get_location_countries();
                if ( $woomelly_location_country_field != "" )
                    $all_location_state = WMeli::get_location_states( $woomelly_location_country_field );
                if ( $woomelly_location_state_field != "" )
                    $all_location_city = WMeli::get_location_cities( $woomelly_location_state_field );


                if ( $woomelly_shipping_mode_field == 'me1' || $woomelly_shipping_mode_field == 'me2' ) {
                    $shipping_modes_id_array = WMeli::get_shipping_modes( $woomelly_category_field );
                    if ( !empty($shipping_modes_id_array) ) {
                        foreach ( $shipping_modes_id_array as $value_master ) {
                            if ( $value_master->mode == $woomelly_shipping_mode_field ) {
                                $shipping_modes_id = $value_master->shipping_attributes->free->accepted_methods;
                                if ( !empty($shipping_modes_id) ) {
                                    foreach ( $shipping_modes_id as $value ) {
                                        $shipping_methods = WMeli::get_shipping_methods( $value );
                                        if ( !empty($shipping_methods) ) {
                                            if ( $shipping_methods->status == 'active' ) {
                                                $shipping_modes_string .= '<option value="'.$shipping_methods->id.'" '.( ($woomelly_shipping_accepted_methods_field==$shipping_methods->id)? "selected=\"selected\"" : "" ).'>'.$shipping_methods->name.'</option>';
                                            }
                                        }
                                        unset( $shipping_methods );
                                    }
                                }
                            }
                        }
                    }
                }

                $woomelly_custom_shipping_cost = $wm_template_sync->get_woomelly_custom_shipping_cost_field();
                $woomelly_shipping_local_pick_up_field = $wm_template_sync->get_woomelly_shipping_local_pick_up_field();
                $woomelly_shipping_free_shipping_field = $wm_template_sync->get_woomelly_shipping_free_shipping_field();
                $woomelly_shipping_dimensions_field = $wm_template_sync->get_woomelly_shipping_dimensions_field();
                $woomelly_title_field = $wm_template_sync->get_woomelly_title_field();
                $woomelly_official_store_id_field = $wm_template_sync->get_woomelly_official_store_id_field();
                $woomelly_status_field = $wm_template_sync->get_woomelly_status_field();
                $woomelly_price_field = $wm_template_sync->get_woomelly_price_field();
                $woomelly_stock_field = $wm_template_sync->get_woomelly_stock_field();
                
                if ( $woomelly_price_field != "" ) {
                    $woomelly_price_field = explode('::', $woomelly_price_field);
                    $woomelly_price_one_field = $woomelly_price_field[0];
                    $woomelly_price_two_field = $woomelly_price_field[1];
                    $woomelly_price_three_field = $woomelly_price_field[2];
                }

                if ( $woomelly_stock_field != "" ) {
                    $woomelly_stock_field = explode('::', $woomelly_stock_field);
                    $woomelly_stock_one_field = $woomelly_stock_field[0];
                    $woomelly_stock_two_field = $woomelly_stock_field[1];
                    $woomelly_stock_three_field = $woomelly_stock_field[2];
                }

                $woomelly_seller_custom_field_field = $wm_template_sync->get_woomelly_seller_custom_field();
                $woomelly_video_id_field = $wm_template_sync->get_woomelly_video_id_field();
                $woomelly_warranty_field = $wm_template_sync->get_woomelly_warranty_field();
            }
        } else if ( $action=="add" ) {
            $templatesyncsingle = true;
        }
        
        if ( $woomelly_category_field == "" ) {
            $categories = WMeli::get_categories();
        } else {
            $all_data_category = WMeli::get_category( $woomelly_category_field );
        }
        
        if ( !empty($categories) ) {
            $all_categories = '<option value="">' . __("- Select -", "woomelly") . '</option>';
            foreach ( $categories as $category ) {
                $all_categories .= '<option value="'.$category->id.'">'.$category->name.'</option>';
            }
        } else if ( $woomelly_category_field != "" && !empty($all_data_category) ) {
            $all_categories .= '<option value="'.$all_data_category->id.'" selected="selected">'.$all_data_category->name.'</option>';
            if ( !empty($all_data_category->path_from_root) ) {
                $coma = true;
                foreach ( $all_data_category->path_from_root as $value ) {
                    if ( $coma == true ) {
                        $path_from_root .= $value->name;
                        $coma = false;
                    } else {
                        $path_from_root .= ' > ' . $value->name;
                    }
                }
                $path_from_root .= ' (' . $all_data_category->id . ')';
            }
            if ( $all_data_category->settings->immediate_payment == 'required' ) {
                $woomelly_accepts_mercadopago_field = true;
            }

            $woomelly_attributes = WMeli::get_attributes( $woomelly_category_field );
            if ( !empty($woomelly_attributes) ) {
                $required_allow_variations .= '<ul class="uk-list uk-list-bullet">';
                $almost_one = false;
                foreach ( $woomelly_attributes as $att ) {
                    if ( isset($att->tags->allow_variations) && isset($att->tags->required) && $att->value_type == 'list' ) {
                        $almost_one = true;
                        $required_allow_variations .= '<li>'.sprintf(__("Attribute %s allows variations and is mandatory.", "woomelly"), '<strong>'.$att->name.'</strong>').'</li>';
                    } else if ( isset($att->tags->allow_variations) && !isset($att->tags->required) && $att->value_type == 'list' ) {
                        $almost_one = true;
                        $required_allow_variations .= '<li>'.sprintf(__("Attribute %s allows variations but is optional.", "woomelly"), '<strong>'.$att->name.'</strong>').'</li>';
                    } else if ( !isset($att->tags->allow_variations) && isset($att->tags->required) ) {
                        $almost_one = true;
                        $required_allow_variations .= '<li>'.sprintf(__("Attribute %s is mandatory.", "woomelly"), '<strong>'.$att->name.'</strong>').'</li>';
                    }
                }
                $required_allow_variations .= '</ul>';
                if ( !$almost_one ) {
                    $required_allow_variations = '';
                }
            }
        }
        
        if ( $templatesyncsingle == true && $l_is_ok['result'] ) {
            include_once( Woomelly()->get_dir() . '/template/admin/templatesyncsingle.php' );
            return;
        }
        
        $testListTable = new WMListTable();
        if ( isset($_POST['s']) ) {
            $testListTable->prepare_items( $_POST['s'] );
        } else {
            $testListTable->prepare_items();
        }
        
        include_once( Woomelly()->get_dir() . '/template/admin/templatesync.php' );
    }
}