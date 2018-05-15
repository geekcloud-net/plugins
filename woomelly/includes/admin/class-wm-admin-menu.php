<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'WMAdminMenu', false ) ) {
	return new WMAdminMenu();
}

/**
 * WMAdminMenu Class.
 */
class WMAdminMenu {		
    /**
     * Default constructor.
     */	
	public function __construct () {
		add_action( 'in_admin_header', array( $this, 'woomelly_in_admin_header' ), 10 );
		add_action( 'in_admin_footer', array( $this, 'woomelly_in_admin_footer' ), 10 );
		add_action( 'admin_footer', array( $this, 'woomelly_admin_functions_javascript' ) );
		add_action( 'admin_menu', array( $this, 'woomelly_pages_admin_menu' ), 10 );
		add_action( 'admin_bar_menu', array( $this, 'woomelly_pages_admin_bar_menu' ), 1000 );
		add_filter( 'mce_buttons', array( $this, 'woomelly_mce_buttons' ), 10 );
		add_action( 'woocommerce_product_bulk_edit_end', array( $this, 'woomelly_woocommerce_product_bulk_edit_end' ), 10 );
		add_action( 'woocommerce_product_bulk_edit_save', array( $this, 'woomelly_woocommerce_product_bulk_edit_save' ), 10, 1 );
		add_filter( 'manage_product_posts_columns', array( $this, 'woomelly_define_columns' ), 10, 1 );
		add_action( 'manage_product_posts_custom_column', array( $this, 'woomelly_render_columns' ), 10, 2 );
		add_action( 'restrict_manage_posts', array( $this, 'woomelly_restrict_manage_posts' ), 10 );
		add_action( 'parse_query', array( $this, 'woomelly_search_custom_fields' ), 10, 1 );
		add_filter( 'woocommerce_product_data_tabs', array( $this, 'woomelly_woocommerce_product_data_tabs' ), 10 );
		add_action( 'woocommerce_product_data_panels', array( $this, 'woomelly_woocommerce_product_data_panels' ), 10 );
		add_action( 'save_post', array( $this, 'woomelly_save_post' ), 10, 2 );
		add_action( 'add_meta_boxes', array( $this, 'woomelly_add_meta_boxes_sync_product' ), 10 );
		add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'woomelly_woocommerce_admin_order_data_after_billing_address' ), 10, 1 );
		add_action( 'woocommerce_product_after_variable_attributes', array( $this, 'woomelly_woocommerce_product_after_variable_attributes' ), 10, 3 );
		add_action( 'woocommerce_variation_options_dimensions', array( $this, 'woomelly_woocommerce_variation_options_dimensions' ), 10, 3 );
		add_action( 'woocommerce_save_product_variation', array( $this, 'woomelly_woocommerce_save_product_variation' ), 10, 2 );
		add_action( 'admin_notices', array( $this, 'woomelly_admin_warnings'), 10 );
		add_filter( 'bulk_actions-edit-product', array( $this, 'woomelly_add_bulk_actions'), 10, 1 );
		add_filter( 'handle_bulk_actions-edit-product', array( $this, 'woomelly_handle_bulk_actions'), 10, 3 );
		add_filter( 'parse_request', array( $this, 'woomelly_handle_apiml_requests'), 0 );
	} //End __construct()

	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), Woomelly()->get_version() );
	} //End __clone()

	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), Woomelly()->get_version() );
	} //End __wakeup()

	/**
	 * woomelly_mce_buttons.
	 *
	 * @return array
	 */	
	public function woomelly_mce_buttons ( $buttons ) {
	   array_push( $buttons, "woomelly_button" );
	   return $buttons;
	} //End woomelly_mce_buttons()

	/**
	 * woomelly_in_admin_header.
	 *
	 * @return string
	 */	
	public function woomelly_in_admin_header () {
		$wm_page = '';
		
		if ( isset($_GET['page']) && $_GET['page']!="" ) {
			$wm_page = $_GET['page'];
		}
		
		if ( in_array( $wm_page, Woomelly()->get_pages() ) ) {
			echo '<div class="woomelly-header" id="woomelly-header"></div>';
		}
	} //End woomelly_in_admin_header()
	
	/**
	 * woomelly_in_admin_footer.
	 *
	 * @return string
	 */		
	public function woomelly_in_admin_footer () {
		$wm_page = '';
		
		if ( isset($_GET['page']) && $_GET['page']!="" ) {
			$wm_page = $_GET['page'];
		}
		
		if ( in_array( $wm_page, Woomelly()->get_pages() ) ) {
			echo '<div class="woomelly-footer" id="woomelly-footer"></div>';
		}
	} //End woomelly_in_admin_footer()

	/**
	 * woomelly_admin_functions_javascript.
	 *
	 * @return string
	 */		
	public function woomelly_admin_functions_javascript () {
		$url = WMeli::auth_url();
		$wmsettings = new WMSettings();
		$site = $wmsettings->get_site_id();
		$user_id = $wmsettings->get_user_id();
		$access_token = $wmsettings->get_access_token();
		?>
		<script type="text/javascript">		    
		    function wm_copy_to_clipboard (element) {
		        var temp = jQuery( "<input>" );
		        jQuery( "body" ).append( temp );
		        temp.val( jQuery(element).text() ).select();
		        document.execCommand("copy");
		        temp.remove();
		        UIkit.notification({message: '<?php echo __("Copied!", "woomelly"); ?>', status: 'primary', pos: 'bottom-right'});
		    }

		    function strPad (input, pad_length, pad_string, pad_type) {
				var output = input.toString();
					if (pad_string === undefined) { pad_string = ' '; }
					if (pad_type === undefined) { pad_type = 'STR_PAD_RIGHT'; }
					if (pad_type == 'STR_PAD_RIGHT') {
						while (output.length < pad_length) {
							output = output + pad_string;
						}
					} else if (pad_type == 'STR_PAD_LEFT') {
						while (output.length < pad_length) {
							output = pad_string + output;
						}
					} else if (pad_type == 'STR_PAD_BOTH') {
						var j = 0;
						while (output.length < pad_length) {
							if (j % 2) {
								output = output + pad_string;
							} else {
								output = pad_string + output;
							}
							j++;
						}
					}
				return output;
			}

		    function wm_waiting (element) {
				jQuery(element).waitMe({
		            effect : "timer",
		            text : "",
		            bg : "rgba(255,255,255,0.7)",
		            color : "#000",
		            maxSize : "30",
		            waitTime : -1,
		            textPos : "vertical",
		            fontSize : "",
		            source : "",
		            onClose : function() {}
		        });
		    }
		    
		    jQuery( "#wm_settings_page_unlink" ).click(function() {
		        wm_waiting( ".wm_settings_page_container");
		        var data = {
		            "action" : "woomelly_do_unlink",
		        };
		        jQuery.post(ajaxurl, data, function(response) {
		            jQuery( ".wm_settings_page_container" ).waitMe( "hide" );
		            if ( response == "success" ) {
		                jQuery( "#wm_settings_page_container_unlink" ).remove();
		                UIkit.notification({message: '<span uk-icon=\'icon: check\'></span> <?php echo __("Store successfully unlinked!", "woomelly"); ?>', status: 'success', pos: 'bottom-center'});
		                jQuery( "#wm_settings_page_container" ).append( "<div id='wm_settings_page_container_link' class='uk-alert-primary wm_settings_page_container' uk-alert><div><?php echo __('Application currently not connected to Mercadolibre. If you want to connect your store press ', 'woomelly'); ?><a href='<?php echo $url; ?>' class='uk-button uk-button-primary uk-button-small' style='margin-left: 5px;'><?php echo __('Link', 'woomelly'); ?></a></div></div>" );
		            } else {
		                UIkit.notification({message: '<span uk-icon=\'icon: warning\'></span> ' + response, status: 'danger', pos: 'bottom-center'});                
		            }
		        }).error(function(data){
		            jQuery( ".wm_settings_page_container" ).waitMe( "hide" );
		            UIkit.notification({message: '<span uk-icon=\'icon: warning\'></span> <?php echo __("Sorry, there was an inconvenience in unlinking your store.", "woomelly"); ?>', status: 'danger', pos: 'bottom-center'});
		        });
		    });
		    
		    if ( jQuery( "#wm_settings_refresh_token" ).is(':checked') ) {
		        jQuery( "#wm_settings_refresh_token_email" ).prop( "disabled", false ).css({"background-color": ""});
		    } else {
		        jQuery( "#wm_settings_refresh_token_email" ).prop( "disabled", true ).css({"background-color": "#ddd"});        
		    }
		    if ( jQuery( "#wm_settings_sync_automatic" ).is(':checked') ) {
		        jQuery( "#wm_settings_sync_automatic_time" ).prop( "disabled", false ).css({"background-color": ""});
		    } else {
		        jQuery( "#wm_settings_sync_automatic_time" ).prop( "disabled", true ).css({"background-color": "#ddd"});        
		    }
		    if ( jQuery( "#wm_settings_notification_sync" ).is(':checked') ) {
		        jQuery( "#wm_settings_notification_sync_email" ).prop( "disabled", false ).css({"background-color": ""});
		    } else {
		        jQuery( "#wm_settings_notification_sync_email" ).prop( "disabled", true ).css({"background-color": "#ddd"});        
		    }
		    jQuery( "#wm_settings_refresh_token" ).click(function() {
		        if ( jQuery( "#wm_settings_refresh_token" ).is(':checked') ) {
		            jQuery( "#wm_settings_refresh_token_email" ).prop( "disabled", false ).css({"background-color": ""});
		        } else {
		            jQuery( "#wm_settings_refresh_token_email" ).prop( "disabled", true ).css({"background-color": "#ddd"});            
		        }
		    });
		    jQuery( "#wm_settings_notification_sync" ).click(function() {
		        if ( jQuery( "#wm_settings_notification_sync" ).is(':checked') ) {
		            jQuery( "#wm_settings_notification_sync_email" ).prop( "disabled", false ).css({"background-color": ""});
		        } else {
		            jQuery( "#wm_settings_notification_sync_email" ).prop( "disabled", true ).css({"background-color": "#ddd"});            
		        }
		    });
		    jQuery( "#wm_settings_sync_automatic" ).click(function() {
		        if ( jQuery( "#wm_settings_sync_automatic" ).is(':checked') ) {
		            jQuery( "#wm_settings_sync_automatic_time" ).prop( "disabled", false ).css({"background-color": ""});
		        } else {
		            jQuery( "#wm_settings_sync_automatic_time" ).prop( "disabled", true ).css({"background-color": "#ddd"});            
		        }
		    });

	        jQuery( "#woomelly_category_field" ).change(function() {
				wm_waiting( ".wrap-page-templatesync-single" );
	            jQuery.get( "https://api.mercadolibre.com/categories/" + jQuery( this ).val(), function( data ) {
	                if ( data.children_categories.length > 0 ) {
	                	var subcategory = '<option value=""><?php echo __("- Select -", "woomelly"); ?></option>';
		                jQuery.each( data.children_categories, function(index, obj) {
		                    subcategory += "<option value=" + obj.id + ">" + obj.name + "</option>";
		                });
		                if ( data.path_from_root.length > 1 ) {
			                jQuery.each( data.path_from_root, function(index, obj) {
			                    if ( data.id != obj.id ) {
			                    	subcategory += "<option value=" + obj.id + ">↑ " + obj.name + "</option>";
			                	}
			                });
			            }
		                jQuery( "#woomelly_category_field" ).empty().append( subcategory );
	                } else {
	                	jQuery( "#woomelly_category_data_detail" ).css( {"display": ""} );
	                	
	                	var comma = false;
	                	var path = "";
	                	var buying_modes = "";
	                	var listing_type_id = "";
	                	var condition = "";
	                	var shipping_modes = "";
		                var user_id = "<?php echo $user_id; ?>";
		                var access_token = "<?php echo $access_token; ?>";
		                var site = "<?php echo $site; ?>";

	                	if ( data.path_from_root.length > 0 ) {
			                jQuery.each( data.path_from_root, function(index, obj) {
			                	if ( comma == false ) {
			                		path += obj.name;
			                		comma = true;
			                	} else {
			                		path += ' > ' + obj.name;
			                	}
			                });
		                }
		                jQuery( "#woomelly_path_from_root" ).text( path );
		                jQuery( "#woomelly_category_name_field" ).val( data.name + " ("+data.id+")" );
	                	if ( data.settings.buying_modes.length > 0 ) {
			                jQuery.each( data.settings.buying_modes, function(index, obj) {
								buying_modes += '<option value="'+obj+'">'+obj+'</option>';
			                });
		                }	                
		                jQuery( "#woomelly_buying_mode_field" ).empty().append( buying_modes );
						jQuery.get( "https://api.mercadolibre.com/users/"+user_id+"/available_listing_types?category_id="+data.id+"&access_token="+access_token, function( data ) {
							if ( data.available.length > 0 ) {
				                jQuery.each( data.available, function(index, obj) {
				                    listing_type_id += "<option value=" + obj.id + ">" + obj.name + "</option>";
				                });
				                jQuery( "#woomelly_listing_type_id_field" ).empty().append( listing_type_id );
							}
			            }).fail(function() {
			            });

						jQuery.get( "https://api.mercadolibre.com/classified_locations/countries", function( data ) {
							if ( data ) {
								var country = '<option value=""><?php echo __("- Country -", "woomelly"); ?></option>';
				                jQuery.each( data, function(index, obj) {
				                    country += "<option value=" + obj.id + ">" + obj.name + "</option>";
				                });
				                jQuery( "#woomelly_location_country_field" ).empty().append( country );
				                jQuery( "#woomelly_location_state_field" ).empty().append( '<option value=""><?php echo __("- State -", "woomelly"); ?></option>' )
				                jQuery( "#woomelly_location_city_field" ).empty().append( '<option value=""><?php echo __("- City -", "woomelly"); ?></option>' );
							}
			            }).fail(function() {
			            });

						jQuery.get( "https://api.mercadolibre.com/users/"+user_id+"?access_token="+access_token, function( data ) {
		                	if ( data.shipping_modes.length > 0 ) {
				                jQuery.each( data.shipping_modes, function(index, obj) {
									shipping_modes += '<option value="'+obj+'">'+obj+'</option>';
				                });
			                }
			                jQuery( "#woomelly_shipping_mode_field" ).empty().append( shipping_modes );

			                if ( jQuery( "#woomelly_shipping_mode_field" ).val() == "custom" ) {
                				jQuery( ".woomelly_custom_shipping_cost_title_field" ).css( {"display": ""} );
                				jQuery( ".woomelly_custom_shipping_cost_field" ).css( {"display": ""} );
			                } else {
                				jQuery( ".woomelly_custom_shipping_cost_title_field" ).css( {"display": "none"} );
                				jQuery( ".woomelly_custom_shipping_cost_field" ).css( {"display": "none"} );	                	
			                }
							if ( jQuery( "#woomelly_shipping_mode_field" ).val() == 'me1' || jQuery( "#woomelly_shipping_mode_field" ).val() == 'me2' ) {
								jQuery( ".woomelly_shipping_accepted_methods_field" ).css( {"display": ""} );
						        var wm_category_field = jQuery( "#woomelly_category_field" ).val();
						        var mode = jQuery( "#woomelly_shipping_mode_field" ).val();
						        var shipping_methods = '';
								jQuery.get( "https://api.mercadolibre.com/users/"+user_id+"/shipping_modes?category_id="+wm_category_field+"&access_token="+access_token, function( data ) {
									if ( data ) {
										jQuery( "#woomelly_shipping_accepted_methods_field" ).empty();
										for ( var ii = data.length - 1; ii >= 0; ii-- ) {
											if ( mode == data[ii].mode ) {
						                		if ( data[ii].shipping_attributes.free.accepted_methods.length > 0 ) {
						                			jQuery.each( data[ii].shipping_attributes.free.accepted_methods, function(index, objtwo) {
														jQuery.get( "https://api.mercadolibre.com/sites/"+site+"/shipping_methods/"+objtwo, function( data ) {
															shipping_methods = '<option value="'+data.id+'">'+data.name+'</option>';
															jQuery( "#woomelly_shipping_accepted_methods_field" ).append( shipping_methods );
														}).fail(function() {
											            });
						                			});
											        
						                		}
											}
										}			
									}
					            }).fail(function() {
					            });
							} else {
								jQuery( ".woomelly_shipping_accepted_methods_field" ).css( {"display": "none"} );
							}
			            }).fail(function() {
			            });
	                	if ( data.settings.item_conditions.length > 0 ) {
			                jQuery.each( data.settings.item_conditions, function(index, obj) {
								condition += '<option value="'+obj+'">'+obj+'</option>';
			                });
		                }
		                jQuery( "#woomelly_condition_field" ).empty().append( condition );
		                if ( data.settings.immediate_payment == 'optional' ) {
		                	jQuery( ".woomelly_accepts_mercadopago_field" ).empty().append( '<div class="uk-margin woomelly_accepts_mercadopago_field"><label class="uk-form-label" for="woomelly_accepts_mercadopago_field"><?php echo __("Accept Mercadopago", "woomelly"); ?></label><input class="checkbox" name="woomelly_accepts_mercadopago_field" id="woomelly_accepts_mercadopago_field" value="1" type="checkbox" style="margin-left: 5px;"/></div>' );
	                	} else {
		                	jQuery( ".woomelly_accepts_mercadopago_field" ).empty().append( '<div class="uk-margin woomelly_accepts_mercadopago_field"><label class="uk-form-label" for="woomelly_accepts_mercadopago_field"><?php echo __("Accept Mercadopago", "woomelly"); ?></label><input class="checkbox" name="woomelly_accepts_mercadopago_field" id="woomelly_accepts_mercadopago_field" value="1" checked="checked" type="checkbox" style="margin-left: 5px;"/></div>' );	                		
	                	}
	                }
					jQuery( ".wrap-page-templatesync-single" ).waitMe( "hide" );
	            }).fail(function() {
	            	jQuery( ".wrap-page-templatesync-single" ).waitMe( "hide" );
	            });
	        });
			
			jQuery( "#woomelly_shipping_mode_field" ).change(function() {
				if ( jQuery( this ).val() == 'custom' ) {
                	jQuery( ".woomelly_custom_shipping_cost_title_field" ).css( {"display": ""} );
                	jQuery( ".woomelly_custom_shipping_cost_field" ).css( {"display": ""} );
				} else {
                	jQuery( ".woomelly_custom_shipping_cost_title_field" ).css( {"display": "none"} );
                	jQuery( ".woomelly_custom_shipping_cost_field" ).css( {"display": "none"} );
				}
				if ( jQuery( this ).val() == 'me1' || jQuery( this ).val() == 'me2' ) {
					jQuery( ".woomelly_shipping_accepted_methods_field" ).css( {"display": ""} );
			        var user_id = "<?php echo $user_id; ?>";
			        var access_token = "<?php echo $access_token; ?>";
			        var wm_category_field = jQuery( "#woomelly_category_field" ).val();
			        var mode = jQuery( this ).val();
			        var site = "<?php echo $site; ?>";
			        var shipping_methods = '';
					jQuery.get( "https://api.mercadolibre.com/users/"+user_id+"/shipping_modes?category_id="+wm_category_field+"&access_token="+access_token, function( data ) {
						if ( data ) {
							jQuery( "#woomelly_shipping_accepted_methods_field" ).empty();
							for ( var ii = data.length - 1; ii >= 0; ii-- ) {
								if ( mode == data[ii].mode ) {
			                		if ( data[ii].shipping_attributes.free.accepted_methods.length > 0 ) {
			                			jQuery.each( data[ii].shipping_attributes.free.accepted_methods, function(index, objtwo) {
											jQuery.get( "https://api.mercadolibre.com/sites/"+site+"/shipping_methods/"+objtwo, function( data ) {
												shipping_methods = '<option value="'+data.id+'">'+data.name+'</option>';
												jQuery( "#woomelly_shipping_accepted_methods_field" ).append( shipping_methods );
											}).fail(function() {
								            });
			                			});
								        
			                		}
								}
							}			
						}
		            }).fail(function() {
		            });
				} else {
					jQuery( ".woomelly_shipping_accepted_methods_field" ).css( {"display": "none"} );
				}
			});
			
			jQuery( "#woomelly_category_field_reset" ).click(function() {
		        jQuery( "#woomelly_category_data_detail" ).css( {"display": "none"} );
		        wm_waiting( "#wrap-page-templatesync-single" );
				var site = "<?php echo $site; ?>";
				jQuery.get( "https://api.mercadolibre.com/sites/" + site + "/categories", function( data ) {
					if ( data.length > 0 ) {
						var subcategory_reset = '<option value=""><?php echo __("- Select -", "woomelly"); ?></option>';
						jQuery( "#woomelly_category_field" ).empty();
		                jQuery.each( data, function(index, obj) {
		                    subcategory_reset += "<option value=" + obj.id + ">" + obj.name + "</option>";
		                });
		                jQuery( "#woomelly_category_field" ).append( subcategory_reset );
					}
					jQuery( "#wrap-page-templatesync-single" ).waitMe( "hide" );
	            }).fail(function() {
	            	jQuery( "#wrap-page-templatesync-single" ).waitMe( "hide" );
	            });
			});
			
			jQuery( "#woomelly_location_country_field" ).change(function() {
				wm_waiting( ".woomelly_location_field" );
			    jQuery.get( "https://api.mercadolibre.com/classified_locations/countries/" + jQuery( this ).val(), function( data ) {
			        if ( data ) {
			            var states = '<option value=""><?php echo __("- State -", "woomelly"); ?></option>';
			            jQuery.each( data.states, function(index, obj) {
			                states += "<option value=" + obj.id + ">" + obj.name + "</option>";
			            });
			            jQuery( "#woomelly_location_state_field" ).empty().append( states );
				        jQuery( "#woomelly_location_city_field" ).empty().append( '<option value=""><?php echo __("- City -", "woomelly"); ?></option>' );
			        }
			        jQuery( ".woomelly_location_field" ).waitMe( "hide" );
			    }).fail(function() {
			    	jQuery( ".woomelly_location_field" ).waitMe( "hide" );
			    });
			});

			jQuery( "#woomelly_location_state_field" ).change(function() {
				wm_waiting( ".woomelly_location_field" );
			    jQuery.get( "https://api.mercadolibre.com/classified_locations/states/" + jQuery( this ).val(), function( data ) {
			        if ( data ) {
			            var cities = '<option value=""><?php echo __("- City -", "woomelly"); ?></option>';
			            jQuery.each( data.cities, function(index, obj) {
			                cities += "<option value=" + obj.id + ">" + obj.name + "</option>";
			            });
			            jQuery( "#woomelly_location_city_field" ).empty().append( cities );
			        }
			        jQuery( ".woomelly_location_field" ).waitMe( "hide" );
			    }).fail(function() {
			    	jQuery( ".woomelly_location_field" ).waitMe( "hide" );
			    });
			});

			var max_fields      = 10;
			var wrapper         = jQuery( ".woomelly_custom_shipping_cost_field" );
			var add_button      = jQuery( ".woomelly_add_custom_shipping_cost_field_button" );
			var xx = 1;
			jQuery( add_button ).click(function(e) {
				e.preventDefault();
				if ( xx < max_fields ) {
					xx++;
					jQuery( wrapper ).append('<div><input name="woomelly_custom_shipping_cost_description_field[]" class="woomelly_custom_shipping_cost_description_field" type="text" style="width: 70%;"/><input name="woomelly_custom_shipping_cost_cost_field[]" class="woomelly_custom_shipping_cost_cost_field" type="text" style="width: 20%;"/><a href="#" class="remove_field" style="width: 20%; text-decoration: none;"><span class="dashicons dashicons-no-alt" style="vertical-align: middle;"></span></a></div>');
				}
			});
			
			jQuery( wrapper ).on("click",".remove_field", function(e){
				e.preventDefault();
				jQuery( this ).parent( 'div' ).remove();
				xx--;
			});
		</script>
		<?php
	} //End woomelly_admin_functions_javascript()

	/**
	 * woomelly_pages_admin_menu.
	 *
	 * @return void
	 */		
	public function woomelly_pages_admin_menu () {
		add_menu_page( 'Woomelly', 'Woomelly', 'manage_options', 'woomelly-menu', '', Woomelly()->get_assets_url() .'images/meli.png', 71 );
		add_submenu_page( 'woomelly-menu', __('Overview', 'woomelly'), __('Overview', 'woomelly'), 'manage_options', 'woomelly-menu', array( $this, 'woomelly_dashboard') );
		add_submenu_page( 'woomelly-menu', __('Settings', 'woomelly'), __('Settings', 'woomelly'), 'manage_options', 'woomelly-settings', array( $this, 'woomelly_settings') );
		add_submenu_page( 'woomelly-menu', __('TemplateSync', 'woomelly'), __('TemplateSync', 'woomelly'), 'manage_options', 'woomelly-templatesync', array( $this, 'woomelly_templatesync') );
		add_submenu_page( 'woomelly-menu', __('Connection', 'woomelly'), __('Connection', 'woomelly'), 'manage_options', 'woomelly-connection', array( $this, 'woomelly_connection') );
		add_submenu_page( 'woomelly-menu', __('Extensions', 'woomelly'), __('Extensions', 'woomelly'), 'manage_options', 'woomelly-extensions', array( $this, 'woomelly_extensions') );
		add_submenu_page( 'woomelly-menu', __('License', 'woomelly'), __('License', 'woomelly'), 'manage_options', 'woomelly-license', array( $this, 'woomelly_license') );
	} //End woomelly_pages_admin_menu()

	/**
	 * woomelly_dashboard.
	 *
	 * @return template
	 */	
	public function woomelly_dashboard () {
		WMAdminDashboard::output();
	} //End woomelly_dashboard()

	/**
	 * woomelly_settings.
	 *
	 * @return template
	 */	
	public function woomelly_settings () {
		WMAdminSettings::output();
	} //End woomelly_settings()

	/**
	 * woomelly_templatesync.
	 *
	 * @return template
	 */	
	public function woomelly_templatesync () {
		WMAdminTemplateSync::output();
	} //End woomelly_templatesync()

	/**
	 * woomelly_extensions.
	 *
	 * @return template
	 */	
	public function woomelly_extensions () {
		WMAdminExtensions::output();
	} //End woomelly_extensions()

	/**
	 * woomelly_connection.
	 *
	 * @return template
	 */	
	public function woomelly_connection () {
		WMAdminConnection::output();
	} //End woomelly_connection()

	/**
	 * woomelly_license.
	 *
	 * @return template
	 */	
	public function woomelly_license () {
		WMAdminLicense::output();
	} //End woomelly_license()

	/**
	 * woomelly_pages_admin_bar_menu.
	 *
	 * @return void
	 */	
	public function woomelly_pages_admin_bar_menu () {
		global $wp_admin_bar;
		$wp_admin_bar->add_menu(
			array(
				'id' => 'woomelly-menu',
				'title' => '<img src="'.Woomelly()->get_assets_url() .'images/meli.png'.'" style="margin-right: 5px;" alt="">' . __("Woomelly", "woomelly" ),
				'href' => get_admin_url( NULL, '/admin.php?page=woomelly-menu' )
			)
		);
		$wp_admin_bar->add_menu(
			array(
				'id' => 'woomelly-settings',
				'parent' => 'woomelly-menu',
				'title' => __("Settings", "woomelly" ),
				'href' => get_admin_url( NULL, '/admin.php?page=woomelly-settings' )
			)
		);
		$wp_admin_bar->add_menu(
			array(
				'id' => 'woomelly-templatesync',
				'parent' => 'woomelly-menu',
				'title' => __("TemplateSync", "woomelly" ),
				'href' => get_admin_url( NULL, '/admin.php?page=woomelly-templatesync' )
			)
		);
		$wp_admin_bar->add_menu(
			array(
				'id' => 'woomelly-connection',
				'parent' => 'woomelly-menu',
				'title' => __("Connection", "woomelly" ),
				'href' => get_admin_url( NULL, '/admin.php?page=woomelly-connection' )
			)
		);
		$wp_admin_bar->add_menu(
			array(
				'id' => 'woomelly-extensions',
				'parent' => 'woomelly-menu',
				'title' => __("Extensions", "woomelly" ),
				'href' => get_admin_url( NULL, '/admin.php?page=woomelly-extensions' )
			)
		);
		$wp_admin_bar->add_menu(
			array(
				'id' => 'woomelly-license',
				'parent' => 'woomelly-menu',
				'title' => __("License", "woomelly" ),
				'href' => get_admin_url( NULL, '/admin.php?page=woomelly-license' )
			)
		);
	} //End woomelly_pages_admin_bar_menu()

	/**
	 * woomelly_woocommerce_product_bulk_edit_end.
	 *
	 * @return string
	 */
	public function woomelly_woocommerce_product_bulk_edit_end () {
		?>
		<label for="woomelly_templates_sync">
			<span class="title"><?php echo __( 'Connection Template', 'woomelly' ); ?></span>
				<span class="input-text-wrap">
					<select class="woomelly_templates_sync" name="woomelly_templates_sync" id="woomelly_templates_sync">
					<?php
					echo '<option value="">' . __( "— Without changes —", "woocommerce" ) . '</option>';
					echo '<option value="-1">' . __( "— Remove Template —", "woomelly" ) . '</option>';
					$options = WMTemplateSync::get_all_select();
					if ( !empty($options) ) {
						foreach ( $options as $value ) {
							echo '<option value="' . esc_attr( $value['ID'] ) . '">' . esc_html( $value['title'] ) . '</option>';
						}
					}
					?>
				</select>
			</span>
		</label>
		<?php
	} //End woomelly_woocommerce_product_bulk_edit_end()

	/**
	 * woomelly_woocommerce_product_bulk_edit_save.
	 *
	 * @return void
	 */
	public function woomelly_woocommerce_product_bulk_edit_save ( $product ) {
		if ( isset($_GET['post']) && !empty($_GET['post']) && isset($_GET['woomelly_templates_sync']) && $_GET['woomelly_templates_sync']!="" ) {
			foreach ( $_GET['post'] as $value ) {
				$wm_product = new WMProduct( $value );
				if ( $wm_product->get_id() > 0 ) {
					$wm_product->set_woomelly_template_sync_id( $_GET['woomelly_templates_sync'] );
				}
				unset( $wm_product );
			}
		}
	} //End woomelly_woocommerce_product_bulk_edit_save()

	/**
	 * woomelly_define_columns.
	 *
	 * @return array
	 */
	public function woomelly_define_columns( $columns ) {
		$columns['woomelly_template_sync'] = '<span class="wc-type parent-tips" data-tip="'.__("Meli", "woomelly").'" style="color: #ccb402;">'.__("Meli", "woomelly").'</span>';
		return $columns;
	} //End woomelly_define_columns()

	/**
	 * woomelly_render_columns.
	 *
	 * @return string
	 */
	public function woomelly_render_columns ( $column, $post_id ) {
		$print = '';
		$sync_problem = false;
		$woomelly_template_sync = '';

		if ( $column == 'woomelly_template_sync' && absint( $post_id ) > 0 ) {
			$wm_product = new WMProduct( $post_id );			
			if ( $wm_product->get_id() > 0 ) {
				$woomelly_template_sync_id = absint( $wm_product->get_woomelly_template_sync_id() );
				if ( $woomelly_template_sync_id > 0 ) {
					$woomelly_template_sync = wm_get_templatesync_meta( 'woomelly_name_template_field', $woomelly_template_sync_id );
					if ( $woomelly_template_sync != "" ) {
						$sync_problem = $wm_product->get_woomelly_sync_problem();
						$print .= '<a href="'.admin_url( "admin.php?page=woomelly-templatesync&amp;action=edit&amp;woomelly_template_id=" . $wm_product->get_woomelly_template_sync_id() ).'"><span>'.$woomelly_template_sync.'</span></a>';
						if ( $wm_product->get_woomelly_status_meli_field() == 'true' ) {
							if ( $wm_product->get_woomelly_url_meli_field() != "" ) {
								$print .= '<br><a href="'.$wm_product->get_woomelly_url_meli_field().'" style="text-decoration: underline;" target="_blank"><mark class="instock">'.sprintf(__("SYNC %s", "woomelly"), '<span class="dashicons dashicons-yes"></span>').(($sync_problem)? '<span class="dashicons dashicons-warning" style="color: red;"></span>' : '').'</mark></a>';
							} else {
								$print .= '<br><a><mark class="instock">'.sprintf(__("SYNC %s", "woomelly"), '<span class="dashicons dashicons-yes"></span>').(($sync_problem)? '<span class="dashicons dashicons-warning" style="color: red;"></span>' : '').'</mark></a>';
							}
						} else {
							$print .= '<br><mark class="outofstock">'.sprintf(__("SYNC %s", "woomelly"), '<span class="dashicons dashicons-no-alt"></span>').(($sync_problem)? '<span class="dashicons dashicons-warning" style="color: red;"></span>' : '').'</mark>';
						}
						$print .= '<br><strong style="font-size: 10px;">'. strtoupper($wm_product->get_woomelly_status_name_field()).'</strong>';				
					} else {
						$print .= '<span class="dashicons dashicons-no" style="color: red;"></span>';
					}
				} else {
					$print .= '<span class="dashicons dashicons-no" style="color: red;"></span>';
				}
			} else {
				$print .= '<span class="dashicons dashicons-no" style="color: red;"></span>';
			}
			echo $print;
		}
	} //End woomelly_render_columns()

	/**
	 * woomelly_restrict_manage_posts.
	 *
	 * @return string
	 */
	public function woomelly_restrict_manage_posts () {
		global $typenow;
		$woomelly_filter_template_sync = '';
		$woomelly_filter_status = '';
		$woomelly_filter_code = '';

		if ( isset($_GET['woomelly_filter_template_sync']) && $_GET['woomelly_filter_template_sync'] != "" ){
			$woomelly_filter_template_sync = wc_clean( wp_unslash( $_GET['woomelly_filter_template_sync'] ) );
		}
		if ( isset($_GET['woomelly_filter_status']) && $_GET['woomelly_filter_status'] != "" ){
			$woomelly_filter_status = wc_clean( wp_unslash( $_GET['woomelly_filter_status'] ) );
		}
		if ( isset($_GET['woomelly_filter_code']) && $_GET['woomelly_filter_code'] != "" ){
			$woomelly_filter_code = wc_clean( wp_unslash( $_GET['woomelly_filter_code'] ) );
		}
		if ( 'product' === $typenow ) {
		?>
			<select name="woomelly_filter_template_sync" id="woomelly_filter_template_sync">
				<option value=""><?php echo __("Filter by WMtemplate", "woomelly"); ?></option>
				<option value="wm_filter_without_template" <?php echo (($woomelly_filter_template_sync=="wm_filter_without_template")? "selected=\"selected\"" : ""); ?>><?php echo __("— Without template —", "woomelly"); ?></option>
				<option value="wm_filter_with_template" <?php echo (($woomelly_filter_template_sync=="wm_filter_with_template")? "selected=\"selected\"" : ""); ?>><?php echo __("— With template —", "woomelly"); ?></option>
				<?php
					$options = WMTemplateSync::get_all_select();
					if ( !empty($options) ) {
						foreach ( $options as $value ) {
							echo '<option value="' . esc_attr( $value['ID'] ) . '" '.(($woomelly_filter_template_sync==esc_attr( $value["ID"] ))? "selected=\"selected\"" : "").'>' . esc_html( $value['title'] ) . '</option>';
						}
					}
				?>
			</select>
			<select name="woomelly_filter_status" id="woomelly_filter_status">
				<option value=""><?php echo __("Filter by WMstatus", "woomelly"); ?></option>
				<option value="wm_filter_without_connect" <?php echo (($woomelly_filter_status=="wm_filter_without_connect")? "selected=\"selected\"" : ""); ?>><?php echo __( "— Without connection —", "woomelly" ); ?></option>;
				<option value="wm_filter_with_connect" <?php echo (($woomelly_filter_status=="wm_filter_with_connect")? "selected=\"selected\"" : ""); ?>><?php echo __( "— With connection —", "woomelly" ); ?></option>;
				<option value="wm_filter_active_connect" <?php echo (($woomelly_filter_status=="wm_filter_active_connect")? "selected=\"selected\"" : ""); ?>><?php echo __( "— Active in Meli —", "woomelly" ); ?></option>;
				<option value="wm_filter_paused_connect" <?php echo (($woomelly_filter_status=="wm_filter_paused_connect")? "selected=\"selected\"" : ""); ?>><?php echo __( "— Paused in Meli —", "woomelly" ); ?></option>;
				<option value="wm_filter_closed_connect" <?php echo (($woomelly_filter_status=="wm_filter_closed_connect")? "selected=\"selected\"" : ""); ?>><?php echo __( "— Finished in Meli —", "woomelly" ); ?></option>;
				<option value="wm_filter_reclosed_connect" <?php echo (($woomelly_filter_status=="wm_filter_reclosed_connect")? "selected=\"selected\"" : ""); ?>><?php echo __( "— Relist in Meli —", "woomelly" ); ?></option>;
				<option value="wm_filter_syncproblem_connect" <?php echo (($woomelly_filter_status=="wm_filter_syncproblem_connect")? "selected=\"selected\"" : ""); ?>><?php echo __( "— Sync Problem —", "woomelly" ); ?></option>;
			</select>
			<input type="text" name="woomelly_filter_code" id="woomelly_filter_code" placeholder="<?php echo __('Filter by WMcode', 'woomelly'); ?>" value="<?php echo $woomelly_filter_code; ?>" style="width: 125px;" />
			<?php
		}		
	} //End woomelly_restrict_manage_posts()

	/**
	 * woomelly_search_custom_fields.
	 *
	 * @return void
	 */
	public function woomelly_search_custom_fields ( $wp ) {
		global $pagenow, $typenow;
		$active_post_type = false;
		$woomelly_filter_template_sync = '';
		$woomelly_filter_status = '';
		$woomelly_filter_code = '';

		if ( $typenow == 'product' ) {
			if ( 'edit.php' == $pagenow && $wp->query_vars['post_type'] == 'product' && ( isset( $_GET['woomelly_filter_template_sync'] ) || isset( $_GET['woomelly_filter_status'] ) || isset( $_GET['woomelly_filter_code'] ) ) ) {
				$woomelly_filter_template_sync = wc_clean( wp_unslash( $_GET['woomelly_filter_template_sync'] ) );
				$woomelly_filter_status = wc_clean( wp_unslash( $_GET['woomelly_filter_status'] ) );
				$woomelly_filter_code = wc_clean( wp_unslash( $_GET['woomelly_filter_code'] ) );
				if ( $woomelly_filter_template_sync != "" ) {
					$wp->set('post_type', 'product');
					$active_post_type = true;
					if ( $woomelly_filter_template_sync == 'wm_filter_without_template' ) {
				        $wp->set('meta_query', array(
				        	'relation' => 'OR',
				            array(
								'key'       => '_wm_template_sync_id',
						        'compare'   => 'NOT EXISTS'
				            ),
				            array(
				                'key'       => '_wm_template_sync_id',
				                'value'     => '',
				                'compare'   => '='
				            )
				        ));
					} else if ( $woomelly_filter_template_sync == 'wm_filter_with_template' ) {
				        $wp->set('meta_query', array(
				        	'relation' => 'AND',
				            array(
								'key'       => '_wm_template_sync_id',
						        'compare'   => 'EXISTS'
				            ),
				            array(
				                'key'       => '_wm_template_sync_id',
				                'value'     => '',
				                'compare'   => '!='
				            )
				        ));					
					} else {
				        $wp->set('meta_query', array(
				            array(
				                'key'       => '_wm_template_sync_id',
				                'value'     => $woomelly_filter_template_sync,
				                'compare'   => '='
				            )
				        ));					
					}
				}
				if ( $woomelly_filter_status != "" ) {
					if ( !$active_post_type ) {
						$wp->set('post_type', 'product');
					}
					switch ( $woomelly_filter_status ) {
						case 'wm_filter_without_connect':
					        $wp->set('meta_query', array(
					        	'relation' => 'OR',
					            array(
									'key'       => '_wm_status_meli',
							        'compare'   => 'NOT EXISTS'
					            ),
					            array(
					                'key'       => '_wm_status_meli',
					                'value'     => '',
					                'compare'   => '='
					            )
					        ));
							break;
						case 'wm_filter_with_connect':
					        $wp->set('meta_query', array(
					        	'relation' => 'AND',
					            array(
									'key'       => '_wm_status_meli',
							        'compare'   => 'EXISTS'
					            ),
					            array(
					                'key'       => '_wm_status_meli',
					                'value'     => '',
					                'compare'   => '!='
					            )
					        ));
							break;
						case 'wm_filter_active_connect':
					        $wp->set('meta_query', array(
					            array(
					                'key'       => '_wm_status',
					                'value'     => 'active',
					                'compare'   => '='
					            )
					        ));
							break;
						case 'wm_filter_paused_connect':
					        $wp->set('meta_query', array(
					            array(
					                'key'       => '_wm_status',
					                'value'     => 'paused',
					                'compare'   => '='
					            )
					        ));
							break;
						case 'wm_filter_closed_connect':
					        $wp->set('meta_query', array(
					            array(
					                'key'       => '_wm_status',
					                'value'     => 'closed',
					                'compare'   => '='
					            )
					        ));
							break;
						case 'wm_filter_reclosed_connect':
					        $wp->set('meta_query', array(
					            array(
					                'key'       => '_wm_status',
					                'value'     => 'reclosed',
					                'compare'   => '='
					            )
					        ));
							break;
						case 'wm_filter_syncproblem_connect':
					        $wp->set('meta_query', array(
					        	'relation' => 'AND',
					            array(
									'key'       => '_wm_sync_problem',
							        'compare'   => 'EXISTS'
					            ),
					            array(
					                'key'       => '_wm_sync_problem',
					                'value'     => true,
					                'compare'   => '='
					            )
					        ));
							break;
					}
				}
				if ( $woomelly_filter_code != "" ) {
					if ( !$active_post_type ) {
						$wp->set('post_type', 'product');
					}
			        $wp->set('meta_query', array(
			            array(
							'key'       => '_wm_code_meli',
							'value'     => $woomelly_filter_code,
					        'compare'   => 'LIKE'
			            )
			        ));
				}
			}
		}
	} //End woomelly_search_custom_fields()

	/**
	 * woomelly_woocommerce_product_data_tabs.
	 *
	 * @return void
	 */
	public function woomelly_woocommerce_product_data_tabs ( $product_data_tabs ) {
		$product_data_tabs['product-tab-woomelly-detail'] = array(
			'label' => __( 'WM Details', 'woomelly' ),
			'target' => 'product_tab_woomelly_detail_data',
			'priority' => 41,
		);
		$product_data_tabs['product-tab-woomelly-attributes'] = array(
			'label' => __( 'WM Technical', 'woomelly' ),
			'target' => 'product_tab_woomelly_attributes_data',
			//'class'  => array( 'show_if_simple' ),
			'priority' => 42,
		);
		return $product_data_tabs;
	} //End woomelly_woocommerce_product_data_tabs()

	/**
	 * woomelly_woocommerce_product_data_panels.
	 *
	 * @return string
	 */
	public function woomelly_woocommerce_product_data_panels () {
		global $woocommerce, $post;
		$l_is_ok = Woomelly()->woomelly_status();
		$woomelly_alive = Woomelly()->woomelly_is_connect();
		$wm_product = new WMProduct( $post->ID );
		$woomelly_status_meli_field = "";
		$woomelly_code_meli_field = "";
		$woomelly_status_field = "";
		$woomelly_substatus_field = "";
		$woomelly_sync_status_field = "";
		$woomelly_url_meli_field = "";
		$woomelly_sales_meli_field = "";
		$woomelly_duration_start_meli_field = "";
		$woomelly_duration_end_meli_field = "";
		$woomelly_expiration_time_meli_field = "";
		$woomelly_created_meli_field = "";
		$woomelly_updated_meli_field = "";
		$woomelly_thumbnail_meli_field = "";
		$woomelly_description_meli_field = "";
		$woomelly_template_sync_id = "";
		$woomelly_category_field = "";
		$attributes = array();
		$product_children_sync = array();

		if ( $wm_product->get_id() > 0 ) {
			$woomelly_status_meli_field = $wm_product->get_woomelly_status_meli_field();

			$product_children_sync = $wm_product->product_children_sync();
			if ( empty($product_children_sync) ) {
				$woomelly_url_meli_field = $wm_product->get_woomelly_url_meli_field();
				$woomelly_code_meli_field = $wm_product->get_woomelly_code_meli_field();
				if ( $woomelly_code_meli_field == "" ) {
					$woomelly_code_meli_field = __("Without code", "woomelly");
				} else {
					$woomelly_code_meli_field = '<a style="vertical-align: middle;" href="'.$woomelly_url_meli_field.'" target="_blank">'.$woomelly_code_meli_field.'</a>';
				}
			} else {
				$comma = false;
				foreach ( $product_children_sync as $key => $product_child_sync ) {
					if ( !$comma ) {
						$woomelly_code_meli_field = $product_child_sync;
						$comma = true;
					} else {
						$woomelly_code_meli_field .= ', ' . $product_child_sync;
					}
				}
			}
			$woomelly_status_field = $wm_product->get_woomelly_status_field();
			$woomelly_substatus_field = implode(', ', $wm_product->get_woomelly_substatus_field());
			$woomelly_sync_status_field = $wm_product->get_woomelly_sync_status_field();
			$woomelly_sales_meli_field = $wm_product->get_woomelly_sales_meli_field();
			$woomelly_duration_start_meli_field = $wm_product->get_woomelly_duration_start_meli_field();
			$woomelly_duration_end_meli_field = $wm_product->get_woomelly_duration_end_meli_field();
			$woomelly_expiration_time_meli_field = $wm_product->get_woomelly_expiration_time_meli_field();
			$woomelly_created_meli_field = $wm_product->get_woomelly_created_meli_field();
			$woomelly_updated_meli_field = $wm_product->get_woomelly_updated_meli_field();
			$woomelly_thumbnail_meli_field = $wm_product->get_woomelly_thumbnail_meli_field();
			$woomelly_description_meli_field = $wm_product->get_woomelly_description_meli_field();
			$woomelly_custom_title_field = $wm_product->get_woomelly_custom_title_field();
			$woomelly_template_sync_id = $wm_product->get_woomelly_template_sync_id();
			$wm_template_sync = new WMTemplateSync( $woomelly_template_sync_id );
			if ( $wm_template_sync->get_id() > 0 ) {
				$woomelly_category_field = $wm_template_sync->get_woomelly_category_field();
				$attributes = WMeli::get_attributes( $woomelly_category_field, 'simple' );
			}
		}
		if ( $woomelly_url_meli_field == "" ) {
			$woomelly_url_meli_field = "#";
		}
		if ( $woomelly_sales_meli_field == "" ) {
			$woomelly_sales_meli_field = __("No sales", "woomelly");
		}
		if ( $woomelly_duration_start_meli_field == "" ) {
			$woomelly_duration_start_meli_field = __("---", "woomelly");
		}
		if ( $woomelly_duration_end_meli_field == "" ) {
			$woomelly_duration_end_meli_field = __("---", "woomelly");
		}
		if ( $woomelly_expiration_time_meli_field == "" ) {
			$woomelly_expiration_time_meli_field = __("---", "woomelly");
		}
		if ( $woomelly_created_meli_field == "" ) {
			$woomelly_created_meli_field = __("---", "woomelly");
		}
		if ( $woomelly_updated_meli_field == "" ) {
			$woomelly_updated_meli_field = __("---", "woomelly");
		}
		if ( $woomelly_thumbnail_meli_field == "" ) {
			$woomelly_thumbnail_meli_field = '<strong><span style="vertical-align: middle;">'.__("Without preview", "woomelly").'</strong>';
		} else {
			$woomelly_thumbnail_meli_field = '<img src="'.$woomelly_thumbnail_meli_field.'" />';			
		}

		$wm_settings = new WMSettings();
		$site = $wm_settings->get_site_id();
		$user_id = $wm_settings->get_user_id();
		$access_token = $wm_settings->get_access_token();

		?>
		<!-- Tab Detail Mercadolibre -->
		<div id="product_tab_woomelly_detail_data" class="panel woocommerce_options_panel">
			<?php if ( $woomelly_alive && $l_is_ok['result'] ) { ?>
				<p class="form-field woomelly_status_meli_field ">
					<label><?php echo __("Connected", "woomelly"); ?></label>
					<?php if ( $woomelly_status_meli_field ) { ?>
						<span class="dashicons dashicons-yes" style="color: green; vertical-align: middle;"></span><?php if ( $woomelly_substatus_field != "" ) { echo '( '.$woomelly_substatus_field.' )'; } ?>
					<?php } else { ?>
						<span class="dashicons dashicons-no-alt" style="color: red; vertical-align: middle;"></span><?php if ( $woomelly_substatus_field != "" ) { echo '( '.$woomelly_substatus_field.' )'; } ?>
					<?php } ?>
					<?php echo wc_help_tip( __("Connection status of the product with Mercadolibre.", "woomelly") ); ?>
				</p>
				<p class="form-field woomelly_template_sync_field">
					<?php echo ( ($woomelly_template_sync_id!="")? '<a href="'.admin_url( "admin.php?page=woomelly-templatesync&amp;action=edit&amp;woomelly_template_id=" . $woomelly_template_sync_id ).'"><label for="woomelly_template_sync_field">'.__("Connection Template", "woomelly").'</label></a>' : '<label for="woomelly_template_sync_field">'.__("Connection Template", "woomelly").'</label>' ); ?>
					<select class="select short" name="woomelly_template_sync_field" id="woomelly_template_sync_field" <?php echo ( ($woomelly_status_meli_field)? 'disabled="disabled"' : '' ); ?>>
						<option value=""><?php echo __("- Select -", "woomelly"); ?></option>
						<?php
							$all_templates_sync = WMTemplateSync::get_all_select();
							if ( !empty($all_templates_sync) ) {
								foreach ( $all_templates_sync as $value ) {
									echo '<option value="' . esc_attr( $value["ID"] ) . '" '.selected( $value["ID"], $woomelly_template_sync_id ).'  >' . esc_html( $value["title"] ) . '</option>';
								}
							}
						?>
					</select>
					<?php echo wc_help_tip(__("Synchronization Templates", "woomelly")); ?>
				</p>
				<p class="form-field woomelly_sync_status_field">
					<label for="woomelly_sync_status_field"><?php echo __("Synchronization Status", "woomelly"); ?></label>
					<input class="checkbox" name="woomelly_sync_status_field" id="woomelly_sync_status_field" value="1" <?php echo ( ($woomelly_sync_status_field)? "checked=\"checked\"" : "" ); ?> type="checkbox" />
					<?php echo wc_help_tip( __("Uncheck the field if you want this product not to synchronize with Mercadolibre but maintain its current configuration", "woomelly") ); ?>
				</p>
				<p class="form-field woomelly_status_field">
					<label for="woomelly_status_field"><?php echo __("Mercadolibre Status", "woomelly"); ?></label>
					<select class="select short" name="woomelly_status_field" id="woomelly_status_field" <?php if ( $woomelly_status_meli_field != "true" ) { echo "disabled='disabled'"; } ?>>							
						<option value="active" <?php echo ( ($woomelly_status_field == 'active')? 'selected="selected"' : '' ); ?> <?php echo ( ($woomelly_status_field != 'active'&&$woomelly_status_field != 'paused')? 'disabled="disabled"' : '' ); ?>><?php echo __("Active", "woomelly"); ?></option>
						<option value="payment_required" <?php echo ( ($woomelly_status_field == 'payment_required')? 'selected="selected"' : '' ); ?> disabled="disabled"><?php echo __("Pending of Payment", "woomelly"); ?></option>
						<?php
						$woomelly_substatus_field_array = array();
						$woomelly_substatus_field_array = explode(", ", $woomelly_substatus_field);
						if ( $woomelly_status_field == 'under_review' && in_array('warning', $woomelly_substatus_field_array) ) { ?>
							<option value="under_review" disabled="disabled"><?php echo __("Waiting For Patch", "woomelly"); ?></option>
						<?php } else if ( $woomelly_status_field == 'under_review' && in_array('waiting_for_patch', $woomelly_substatus_field_array) ) { ?>
							<option value="under_review" disabled="disabled"><?php echo __("Hidden - Waiting For Patch", "woomelly"); ?></option>
						<?php } else if ( $woomelly_status_field == 'under_review' && in_array('held', $woomelly_substatus_field_array) ) { ?>
							<option value="under_review" <?php echo ( ($woomelly_status_field == 'under_review')? 'selected="selected"' : '' ); ?> disabled="disabled"><?php echo __("Held", "woomelly"); ?></option>
						<?php } else if ( $woomelly_status_field == 'under_review' && in_array('pending_documentation', $woomelly_substatus_field_array) ) { ?>
							<option value="under_review" <?php echo ( ($woomelly_status_field == 'under_review')? 'selected="selected"' : '' ); ?> disabled="disabled"><?php echo __("Pending Documentation", "woomelly"); ?></option>
						<?php } else if ( $woomelly_status_field == 'under_review' && in_array('forbidden', $woomelly_substatus_field_array) ) { ?>
							<option value="under_review" <?php echo ( ($woomelly_status_field == 'under_review')? 'selected="selected"' : '' ); ?> disabled="disabled"><?php echo __("Forbidden", "woomelly"); ?></option>
						<?php } else { ?>
							<option value="under_review" <?php echo ( ($woomelly_status_field == 'under_review')? 'selected="selected"' : '' ); ?> disabled="disabled"><?php echo __("Under Review", "woomelly"); ?></option>
						<?php }
						if ( $woomelly_status_field == 'paused' && in_array('out_of_stock', $woomelly_substatus_field_array) ) { ?>
							<option value="paused" disabled="disabled"><?php echo __("Paused for Stock", "woomelly"); ?></option>
						<?php } else { ?>
							<option value="paused" <?php echo ( ($woomelly_status_field == 'paused')? 'selected="selected"' : '' ); ?> <?php echo ( ($woomelly_status_field != 'active')? 'disabled="disabled"' : '' ); ?>><?php echo __("Paused", "woomelly"); ?></option>
						<?php }
						if ( $woomelly_status_field == 'closed' && in_array('expired', $woomelly_substatus_field_array) ) { ?>
							<option value="closed" disabled="disabled"><?php echo __("Expired", "woomelly"); ?></option>
						<?php } else if ( $woomelly_status_field == 'closed' && in_array('waiting_for_patch', $woomelly_substatus_field_array) ) { ?>
							<option value="closed" disabled="disabled"><?php echo __("Hidden - Waiting For Patch", "woomelly"); ?></option>
						<?php } else if ( $woomelly_status_field == 'closed' && in_array('held', $woomelly_substatus_field_array) ) { ?>
							<option value="closed" <?php echo ( ($woomelly_status_field == 'closed')? 'selected="selected"' : '' ); ?> disabled="disabled"><?php echo __("Held", "woomelly"); ?></option>
						<?php } else if ( $woomelly_status_field == 'closed' && in_array('deleted', $woomelly_substatus_field_array) ) { ?>
							<option value="closed" <?php echo ( ($woomelly_status_field == 'closed')? 'selected="selected"' : '' ); ?> disabled="disabled"><?php echo __("deleted", "woomelly"); ?></option>
						<?php } else if ( $woomelly_status_field == 'closed' && in_array('suspended', $woomelly_substatus_field_array) ) { ?>
							<option value="closed" <?php echo ( ($woomelly_status_field == 'closed')? 'selected="selected"' : '' ); ?> disabled="disabled"><?php echo __("Suspended", "woomelly"); ?></option>
						<?php } else if ( $woomelly_status_field == 'closed' && in_array('freezed', $woomelly_substatus_field_array) ) { ?>
							<option value="closed" <?php echo ( ($woomelly_status_field == 'closed')? 'selected="selected"' : '' ); ?> disabled="disabled"><?php echo __("Freezed", "woomelly"); ?></option>
						<?php } else { ?>
								<option value="closed" <?php echo ( ($woomelly_status_field == 'closed')? 'selected="selected"' : '' ); ?> <?php echo ( ($woomelly_status_field != 'active' && $woomelly_status_field != 'paused')? 'disabled="disabled"' : '' ); ?>><?php echo __("Finished", "woomelly"); ?></option>
						<?php } ?>
						<option value="reclosed" <?php echo ( ($woomelly_status_field == 'reclosed')? 'selected="selected"' : '' ); ?>  <?php echo ( ($woomelly_status_field == 'closed')? '' : 'disabled="disabled"' ); ?>><?php echo __("Relist", "woomelly"); ?></option>
						<option value="inactive" <?php echo ( ($woomelly_status_field == 'inactive')? 'selected="selected"' : '' ); ?> disabled="disabled"><?php echo __("Inactive", "woomelly"); ?></option>
					</select>
					<?php echo wc_help_tip( __("States available in Mercadolibre. You must have your product already synchronized with mercadolibre to see the available options.", "woomelly") ); ?>
				</p>
				<p class="form-field woomelly_custom_title_field">
					<label for="woomelly_custom_title_field"><?php echo __("Custom Title", "woomelly"); ?></label>
					<input class="short" name="woomelly_custom_title_field" id="woomelly_custom_title_field" value="<?php echo $woomelly_custom_title_field; ?>" type="text" maxlength="60">
					<?php echo wc_help_tip( __("This field is used to set a custom title to your products. Remember that the maximum number of characters allowed for the title of mercadolibre publication is 60 characters.", "woomelly") ); ?>
				</p>
				<p class="form-field woomelly_description_meli_field ">
					<label for="woomelly_description_meli_field"><?php echo __("Plain Text Description", "woomelly"); ?></label>
					<textarea class="short" name="woomelly_description_meli_field" id="woomelly_description_meli_field" placeholder="" rows="2" cols="20"><?php echo $woomelly_description_meli_field; ?></textarea>
					<?php echo wc_help_tip( __("This field is used to place information in plain text without taking into consideration the general description of the product (optional).", "woomelly") ); ?>
				</p>				
				<p class="form-field woomelly_code_meli_field ">
					<label><?php echo __("Code", "woomelly"); ?></label>
					<?php echo $woomelly_code_meli_field; ?>
					<?php echo wc_help_tip( __("Product code in Mercadolibre.", "woomelly") ); ?>
				</p>
				<p class="form-field woomelly_sales_meli_field ">
					<label><?php echo __("Sales", "woomelly"); ?></label>
					<strong><span style="vertical-align: middle;"><?php echo $woomelly_sales_meli_field; ?></strong>
					<?php echo wc_help_tip( __("Number of registered sales of the product in Mercadolibre.", "woomelly") ); ?>
				</p>
				<p class="form-field woomelly_duration_meli_field ">
					<label><?php echo __("Duration", "woomelly"); ?></label>
					<strong><span style="vertical-align: middle;"><?php echo $woomelly_duration_start_meli_field . ' / ' . $woomelly_duration_end_meli_field; ?></strong>
				</p>
				<p class="form-field woomelly_expiration_time_meli_field ">
					<label><?php echo __("Expiration Date", "woomelly"); ?></label>
					<strong><span style="vertical-align: middle;"><?php echo $woomelly_expiration_time_meli_field; ?></strong>
				</p>					
				<p class="form-field woomelly_created_meli_field ">
					<label><?php echo __("Creation and Update", "woomelly"); ?></label>
					<strong><span style="vertical-align: middle;"><?php echo $woomelly_created_meli_field . ' | ' . $woomelly_updated_meli_field; ?></strong>
				</p>
				<p class="form-field woomelly_thumbnail_meli_field ">
					<label><?php echo __("Preview", "woomelly"); ?></label>
					<?php echo $woomelly_thumbnail_meli_field; ?>
				</p>
			<?php } else { ?>
				<div class="uk-alert-danger woomelly_alert_dont_connect">
				    <p><?php echo sprintf( __( 'Sorry, you have a problem with your license or in connection with Mercadolibre. Verify that your website is %s', 'woomelly'), '<a href="'.admin_url( "admin.php?page=woomelly-settings" ).'" >'.__("connected and authorized correctly with Mercadolibre.", "woomelly").'</a>' ); ?></p>
				</div>
			<?php } ?>				
		</div>

		<!-- Tab Attributes Mercadolibre -->
		<div id="product_tab_woomelly_attributes_data" class="panel woocommerce_options_panel">
		<?php if ( $woomelly_alive && $l_is_ok['result'] ) {
				if ( $woomelly_category_field != "" ) {
					if ( !empty($attributes) && $wm_product->get_id() > 0 ) {
						$xx = 0;
						foreach ( $attributes as $value_attribute ) {
							switch ($value_attribute->value_type) {
								case 'string':
									$data_woomelly_attributes_field = $wm_product->get_value_attribute_field($value_attribute->id);
										?>
										<p class="form-field woomelly_variation_attribute_field">
											<label><?php echo $value_attribute->name; ?><?php if ( isset($value_attribute->tags->required) ) { echo '<span style="color: red; padding-left: 5px;">*</span>'; } ?></label>
											<input class="short" name="woomelly_variation_attribute_field-<?php echo $xx; ?>" value="<?php echo $data_woomelly_attributes_field; ?>" maxlength="<?php echo $value_attribute->value_max_length; ?>" type="text" />
											<input name="woomelly_attribute_hidden-<?php echo $xx; ?>" value="<?php echo $value_attribute->id . '::value_name'; ?>" type="hidden">
										</p>
										<?php
									$xx++;
									unset( $data_woomelly_attributes_field );
									break;
								case 'number':
									$data_woomelly_attributes_field = $wm_product->get_value_attribute_field($value_attribute->id);
										?>
										<p class="form-field woomelly_variation_attribute_field">
											<label><?php echo $value_attribute->name; ?><?php if ( isset($value_attribute->tags->required) ) { echo '<span style="color: red; padding-left: 5px;">*</span>'; } ?></label>
											<input class="short wc_input_price" name="woomelly_variation_attribute_field-<?php echo $xx; ?>" value="<?php echo $data_woomelly_attributes_field; ?>" maxlength="<?php echo $value_attribute->value_max_length; ?>" type="number" />
											<input name="woomelly_attribute_hidden-<?php echo $xx; ?>" value="<?php echo $value_attribute->id . '::value_name'; ?>" type="hidden">
										</p>
										<?php
									$xx++;
									unset( $data_woomelly_attributes_field );
									break;
								case 'number_unit':
									$data_woomelly_attributes_field = $wm_product->get_value_attribute_field($value_attribute->id);
									if ( $data_woomelly_attributes_field == "" ) {
										$data_woomelly_attributes_field = array( "", "" );
									}
										?>
										<p class="form-field woomelly_variation_attribute_field">
											<label><?php echo $value_attribute->name; ?><?php if ( isset($value_attribute->tags->required) ) { echo '<span style="color: red; padding-left: 5px;">*</span>'; } ?></label>
											<input class="short wc_input_price" name="woomelly_variation_attribute_field-<?php echo $xx; ?>" value="<?php echo $data_woomelly_attributes_field[0]; ?>" maxlength="<?php echo $value_attribute->value_max_length; ?>" type="number" />
											<select id="woomelly_variation_attribute_field" name="woomelly_variation_attribute_field_select-<?php echo $xx; ?>" class="select short" style="width: 15%;">
												<?php foreach ( $value_attribute->allowed_units as $value_allowed_unit ) { ?>
												<option value="<?php echo $value_allowed_unit->id; ?>" <?php selected( $data_woomelly_attributes_field[1], $value_allowed_unit->id );?>><?php echo $value_allowed_unit->name; ?></option>
												<?php } ?>
											</select>
											<input name="woomelly_attribute_hidden-<?php echo $xx; ?>" value="<?php echo $value_attribute->id . '::number_unit'; ?>" type="hidden">
										</p>
										<?php
									$xx++;
									unset( $data_woomelly_attributes_field );
									break;
								case 'boolean':
									$data_woomelly_attributes_field = $wm_product->get_value_attribute_field($value_attribute->id);
										?>
										<p class="form-field woomelly_variation_attribute_field">
											<label><?php echo $value_attribute->name; ?><?php if ( isset($value_attribute->tags->required) ) { echo '<span style="color: red; padding-left: 5px;">*</span>'; } ?></label>
											<select id="woomelly_variation_attribute_field" name="woomelly_variation_attribute_field-<?php echo $xx; ?>" class="select short">
												<option value=""><?php echo __("- Select -", "woomelly"); ?></option>
												<?php foreach ( $value_attribute->values as $values ) { ?>
												<option value="<?php echo $values->id; ?>" <?php selected( $data_woomelly_attributes_field, $values->id ); ?>><?php echo $values->name; ?></option>
												<?php } ?>
											</select>
											<input name="woomelly_attribute_hidden-<?php echo $xx; ?>" value="<?php echo $value_attribute->id . '::value_id'; ?>" type="hidden">
										</p>
										<?php
									$xx++;
									unset( $data_woomelly_attributes_field );
									break;
								case 'list':
									$data_woomelly_attributes_field = $wm_product->get_value_attribute_field($value_attribute->id);
										?>
										<p class="form-field woomelly_variation_attribute_field">
											<label><?php echo $value_attribute->name; ?><?php if ( isset($value_attribute->tags->required) ) { echo '<span style="color: red; padding-left: 5px;">*</span>'; } ?></label>
											<select id="woomelly_variation_attribute_field" name="woomelly_variation_attribute_field-<?php echo $xx; ?>" class="select short">
												<option value=""><?php echo __("- Select -", "woomelly"); ?></option>
												<?php foreach ( $value_attribute->values as $values ) { ?>
												<option value="<?php echo $values->id; ?>" <?php selected( $data_woomelly_attributes_field, $values->id ); ?>><?php echo $values->name; ?></option>
												<?php } ?>
											</select>
											<input name="woomelly_attribute_hidden-<?php echo $xx; ?>" value="<?php echo $value_attribute->id . '::value_id'; ?>" type="hidden">
										</p>
										<?php
									$xx++;
									unset( $data_woomelly_attributes_field );
									break;
							}
						}
						echo '<input name="woomelly_total_attribute_field" value="' . $xx . '" type="hidden" />';
					}
				} else { ?>
					<div class="uk-alert-warning woomelly_alert_not_templatesync">
						<p><?php echo sprintf( __( 'Sorry, but it has no connection template assigned. Check the %s and assign one of them to this product.', 'woomelly'), '<a href="'.admin_url( "admin.php?page=woomelly-templatesync" ).'" >'.__("List", "woomelly").'</a>' ); ?></p>
					</div>
				<?php }
			} else { ?>
			<div class="uk-alert-danger woomelly_alert_dont_connect">
				<p><?php echo sprintf( __( 'Sorry, you have a problem with your license or in connection with Mercadolibre. Verify that your website is %s', 'woomelly'), '<a href="'.admin_url( "admin.php?page=woomelly-settings" ).'" >'.__("connected and authorized correctly with Mercadolibre.", "woomelly").'</a>' ); ?></p>
			</div>
		<?php } ?>
		</div>
		
		<?php wp_nonce_field( 'woomelly_save_data', 'woomelly_save_data' ); ?>
		<?php
	} //End woomelly_woocommerce_product_data_panels()

	/**
	 * woomelly_save_post.
	 *
	 * @return void
	 */
	public function woomelly_save_post ( $post_id, $post ) {

		// $post_id and $post are required
		if ( empty( $post_id ) || empty( $post ) ) {
			return;
		}
		// Dont' save meta boxes for revisions or autosaves
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
			return;
		}
		// Check the nonce
		if ( !isset( $_POST['woomelly_save_data'] ) || ! wp_verify_nonce( $_POST['woomelly_save_data'], 'woomelly_save_data' ) ) {
			return;
		}
		// Check the post being saved == the $post_id to prevent triggering this call for other save_post events
		if ( !isset( $_POST['post_ID'] ) || $_POST['post_ID'] != $post_id ) {
			return;
		}
		// Check user has permission to edit
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( is_object($post) && $post->post_type == 'product' ) {
			$woomelly_alive = Woomelly()->woomelly_is_connect();
			$l_is_ok = Woomelly()->woomelly_status();
			$attributes = array();
			
			if ( $woomelly_alive && $l_is_ok['result']) {
				$wm_product = new WMProduct( $post_id );
				if ( $wm_product->get_id() > 0 ) {
					
					if ( isset($_POST['woomelly_sync_status_field']) ) {
						$wm_product->set_woomelly_sync_status_field( $_POST['woomelly_sync_status_field'] );
					} else {
						$wm_product->set_woomelly_sync_status_field( "" );
					}
					if ( isset($_POST['woomelly_status_field']) ) {
						$wm_product->set_woomelly_status_field( $_POST['woomelly_status_field'] );
					}
					if ( isset($_POST['woomelly_description_meli_field']) ) {
						$wm_product->set_woomelly_description_meli_field( $_POST['woomelly_description_meli_field'] );
					}
					if ( isset($_POST['woomelly_custom_title_field']) ) {
						$wm_product->set_woomelly_custom_title_field( $_POST['woomelly_custom_title_field'] );
					}
					if ( isset($_POST['woomelly_template_sync_field']) ) {
						$wm_product->set_woomelly_template_sync_id( $_POST['woomelly_template_sync_field'] );
					}
					if ( isset($_POST['woomelly_total_attribute_field']) && absint($_POST['woomelly_total_attribute_field']) > 0 ) {
						for ($jj=0; $jj < absint($_POST['woomelly_total_attribute_field']); $jj++) { 
							if ( isset($_POST['woomelly_variation_attribute_field-'.$jj]) && isset($_POST['woomelly_attribute_hidden-'.$jj]) ) {
								$data_attribute = explode("::", $_POST['woomelly_attribute_hidden-'.$jj]);
								switch ($data_attribute[1]) {
									case 'value_name':
										$attributes[$data_attribute[0]] = 'value_name::' . $_POST['woomelly_variation_attribute_field-'.$jj];
										break;
									case 'value_id':
										$attributes[$data_attribute[0]] = 'value_id::' . $_POST['woomelly_variation_attribute_field-'.$jj];
										break;
									case 'number_unit':
										$attributes[$data_attribute[0]] = 'number_unit::' . $_POST['woomelly_variation_attribute_field-'.$jj] . '::' . $_POST['woomelly_variation_attribute_field_select-'.$jj];
										break;
								}
							}
						}
						$wm_product->set_woomelly_attribute_field( $attributes );
					}
				}
			}
		}
	} //End woomelly_save_post()

	/**
	 * woomelly_add_meta_boxes_sync_product.
	 *
	 * @return void
	 */
	public function woomelly_add_meta_boxes_sync_product () {
	    add_meta_box( 'meta-box-sync-product-id', __( 'Synchronize with Mercadolibre', 'woomelly' ),  array( $this, 'woomelly_add_meta_boxes_sync_product_function' ), 'product', 'side', 'default' );
	} //End woomelly_add_meta_boxes_sync_product()

	/**
	 * woomelly_add_meta_boxes_sync_product_function.
	 *
	 * @return string
	 */
	public function woomelly_add_meta_boxes_sync_product_function ( $post ) {
		$_product = wc_get_product( $post->ID );
		$woomelly_alive = Woomelly()->woomelly_is_connect();
		$l_is_ok = Woomelly()->woomelly_status();

		if ( is_object($_product) && $woomelly_alive == true && $l_is_ok['result'] ) {
			$wm_product = new WMProduct( $post->ID );
			$woomelly_status_meli_field = "";
			$woomelly_updated_field = "";
			$woomelly_updated_user_field = "";
			if ( $wm_product->get_id() > 0 ) {
				$woomelly_status_meli_field = $wm_product->get_woomelly_status_meli_field();
				$woomelly_updated_field = $wm_product->get_woomelly_updated_field();
				$woomelly_updated_user_field = $wm_product->get_woomelly_updated_user_field();
			}

			?>
			<div class="progress-demo">
				<button id="woomelly-sync-product" class="ladda-button" data-color="green" data-style="zoom-out" data-size="s" style="width: 100%;"><?php echo __("Sync", "woomelly"); ?></button>
			</div>
			<div style="margin-top: 15px; word-wrap: break-word;" class="woomelly-sync-product-individual-log" id="woomelly-sync-product-individual-log">
			</div>
			<?php if ( $woomelly_status_meli_field == "true" ) { ?>
				<div style="border-top: 1px solid #e5e5e5;width: 100%;border: 1px solid #e5e5e5;border-top: none;background-color: #f7f7f7;box-shadow: 0 1px 1px rgba(0,0,0,0.04);margin-top: 15px; word-wrap: break-word;">
					<a class="woomelly-reset-product" id="woomelly-reset-product" href=""><?php echo __("Unlink with Mercadolibre", "woomelly"); ?></a><br>
					<span style="font-size: 11px;"><?php echo sprintf( __( "Last Synchronization: %s (%s)", "woomelly"), $woomelly_updated_field, $woomelly_updated_user_field ); ?></span>
				</div>
			<?php } ?>
			<script>
				var lonly = Ladda.create( document.querySelector( '.progress-demo button' ) );
				jQuery('#woomelly-sync-product').click(function( e ) {
					e.preventDefault();
					lonly.start();
					lonly.setProgress( 0.5 );
	                var data = {
	                    "action"                                : "woomelly_do_sync_product",
	                    "wm_id"                                 : "<?php echo $post->ID; ?>",
	                    "wm_type"								: "woomelly_only",
	                };
	                jQuery.post(ajaxurl, data, function(response) {
	                	if ( response != "" ) {
	                		var arr = response.split(':::');
	                		if ( arr[0] == "ok" ) {
	                			jQuery( "#woomelly-sync-product-individual-log" ).empty();
	                			swal( "<?php echo __('Synchronized!', 'woomelly'); ?>", "<?php echo __('Product Successfully Synchronized!', 'woomelly'); ?>", "success" );
	                			setTimeout(location.reload.bind(location), 3000);
	                		} else {
	                			jQuery( "#woomelly-sync-product-individual-log" ).empty().append( arr[1] );
	                		}
	                	}
	                	lonly.stop();
	                }).error(function(data){
	                	lonly.stop();
	                });
				});
				jQuery('#woomelly-reset-product').click(function( e ) {
					e.preventDefault();
			        wm_waiting( "#meta-box-sync-product-id" );
					swal({
					  title: "<?php echo __('Are you sure?', 'woomelly'); ?>",
					  text: "<?php echo __('Once eliminated you will not be able to reverse such action!', 'woomelly'); ?>",
					  icon: "warning",
					  buttons: true,
					  dangerMode: true,
					}).then((willDelete) => {
						if (willDelete) {
							var data = {
								"action" : "woomelly_do_reset_product",
								"wm_id" : "<?php echo $post->ID; ?>",
								"wm_type" : "woomelly_only",
							};
							jQuery.post(ajaxurl, data, function(response) {
								var arr = response.split(':::');
								jQuery( "#meta-box-sync-product-id" ).waitMe( "hide" );
								if ( arr[0] == "ok" ) {
									swal( "<?php echo __('Success!', 'woomelly'); ?>", arr[1], "success" );
									jQuery( "#woomelly-sync-product-individual-log" ).empty();
									setTimeout(location.reload.bind(location), 3000);
								} else {
									swal( "<?php echo __('Cancelled!', 'woomelly'); ?>", arr[1], "error" );
									jQuery( "#woomelly-sync-product-individual-log" ).empty();                			
								}
							}).error(function(data){
								jQuery( "#meta-box-sync-product-id" ).waitMe( "hide" );
								swal( "<?php echo __('Cancelled!','woomelly'); ?>", "<?php echo __('The action has been canceled!','woomelly'); ?>", "error" );
								jQuery( "#woomelly-sync-product-individual-log" ).empty();
							});
						} else {
							jQuery( "#meta-box-sync-product-id" ).waitMe( "hide" );
							swal( "<?php echo __('Cancelled!','woomelly'); ?>", "<?php echo __('The action has been canceled!','woomelly'); ?>", "error" );
						}
					});
				});
			</script>
		<?php } else { ?>
				<div class="uk-alert-danger" style="background: #fef4f6; color: #f0506e; position: relative; padding: 15px 29px 15px 15px;">
				    <p style="font-size: 15px; font-weight: normal; line-height: 1.5; text-rendering: optimizeLegibility;"><?php echo sprintf( __( 'Sorry, you have a problem with your license or in connection with Mercadolibre. Verify that your website is %s', 'woomelly'), '<a href="'.admin_url( "admin.php?page=woomelly-settings" ).'" >'.__("connected and authorized correctly with Mercadolibre.", "woomelly").'</a>' ); ?></p>
				</div>
		<?php }
	} //End woomelly_add_meta_boxes_sync_product_function()

	/**
	 * woomelly_woocommerce_variation_options_dimensions.
	 *
	 * @return string
	 */
	public function woomelly_woocommerce_variation_options_dimensions ( $loop, $variation_data, $variation ) {
		$_product_variation = wc_get_product( $variation->ID );
		$wm_product = new WMProduct( $variation->ID );
		if ( is_object($_product_variation) && $wm_product->get_id() > 0 ) {
			$variation_dimentions = $wm_product->get_woomelly_variation_dimentions_field();
			?>
			<div>
				<p class="form-row hide_if_variation_virtual form-row-full">
					<label for="woomelly_variation_dimentions_field"><?php echo __("Do you want to skip sending dimensions?", "woomelly"); ?>
						<input class="checkbox" name="woomelly_variation_dimentions_field_<?php echo $loop; ?>" id="woomelly_variation_dimentions_field" <?php echo ( ($variation_dimentions)? 'checked="checked"' : '' ); ?> type="checkbox" />
					</label>
				</p>
			</div>
			<?php
		}
	} //End woomelly_woocommerce_variation_options_dimensions()

	/**
	 * woomelly_woocommerce_admin_order_data_after_billing_address.
	 *
	 * @return string
	 */
	public function woomelly_woocommerce_admin_order_data_after_billing_address ( $order ) {
		$wm_order = new WMOrder( $order->get_id() );
		if ( is_object($wm_order) && $wm_order->get_id() > 0 ) {
			echo '<p>
					<strong>' . __( "Mercadolibre #:", "woomelly" ) .'</strong>
					<br>
					'.$wm_order->get_woomelly_code_meli_field().'
				</p>';
		}
	} //End woomelly_woocommerce_admin_order_data_after_billing_address()

	/**
	 * woomelly_woocommerce_product_after_variable_attributes.
	 *
	 * @return string
	 */
	public function woomelly_woocommerce_product_after_variable_attributes ( $loop, $variation_data, $variation ) {
		$attributes_allow_variations = array();
		$_product_variation = wc_get_product( $variation->ID );
		$attributes_meli = array();
		$attributes = array();
		$woomelly_category_field = "";
		$woomelly_alive = Woomelly()->woomelly_is_connect();
		$l_is_ok = Woomelly()->woomelly_status();

		if ( is_object($_product_variation) ) {
			$list_all_variations = $_product_variation->get_attributes();
			$wm_product = new WMProduct( $_product_variation->get_parent_id() );
			$wm_product_variation = new WMProduct( $_product_variation->get_id() );
			if ( $wm_product->get_id() > 0 && $wm_product_variation->get_id() > 0 ) {
				$woomelly_template_sync_id = $wm_product->get_woomelly_template_sync_id();
				$woomelly_variation_extra_img = $wm_product_variation->get_woomelly_variation_extra_img_field();
				$attributes_meli = $wm_product_variation->get_woomelly_variation_field();
				$wm_template_sync = new WMTemplateSync( $woomelly_template_sync_id );				
				if ( $wm_template_sync->get_id() > 0 ) {
					$woomelly_category_field = $wm_template_sync->get_woomelly_category_field();
					$attributes = WMeli::get_attributes( $woomelly_category_field, 'general' );
				}
			}
			?>
			<p class="form-row woomelly_attribute_variation_field form-row-full" style="border-bottom: 1px solid #eee;">
				<label><?php echo __("Attributes in Mercadolibre", "woomelly"); ?></label>
				<?php echo wc_help_tip(__("Attributes in Mercadolibre", "woomelly"));
				if ( $woomelly_alive && $l_is_ok['result'] ) {
					if ( $woomelly_category_field == "" ) { ?>
						<div class="uk-alert-warning woomelly_alert_not_templatesync">
							<span><?php echo sprintf( __( 'Sorry, but it has no connection template assigned. Check the %s and assign one of them to this product.', 'woomelly'), '<a href="'.admin_url( "admin.php?page=woomelly-templatesync" ).'" >'.__("list", "woomelly").'</a>' ); ?></p>
						</div>
					<?php } else {
						if ( !empty($attributes) ) {
							$xx = 0;
							foreach ( $attributes as $value_attribute ) {
								if ( isset($value_attribute->tags->variation_attribute) ) {
									switch ($value_attribute->value_type) {
										case 'string':
											$data_woomelly_attributes_field = $wm_product_variation->get_value_attribute_field($value_attribute->id);
												?>
												<div>
													<p class="form-field woomelly_attribute_variation_field form-row form-row-full">
														<label><?php echo $value_attribute->name; ?><?php if ( isset($value_attribute->tags->required) ) { echo '<span style="color: red; padding-left: 5px;">*</span>'; } ?></label>
														<input class="short" name="woomelly_attribute_variation_field_<?php echo $loop; ?>-<?php echo $xx; ?>" value="<?php echo $data_woomelly_attributes_field; ?>" maxlength="<?php echo $value_attribute->value_max_length; ?>" type="text" />
														<input name="woomelly_attribute_hidden_<?php echo $loop; ?>-<?php echo $xx; ?>" value="<?php echo $value_attribute->id . '::value_name'; ?>" type="hidden">
													</p>
												</div>
												<?php
											$xx++;
											unset( $data_woomelly_attributes_field );
											break;
										case 'number':
											$data_woomelly_attributes_field = $wm_product_variation->get_value_attribute_field($value_attribute->id);
												?>
												<div>
													<p class="form-field woomelly_attribute_variation_field form-row form-row-full">
														<label><?php echo $value_attribute->name; ?><?php if ( isset($value_attribute->tags->required) ) { echo '<span style="color: red; padding-left: 5px;">*</span>'; } ?></label>
														<input class="short wc_input_price" name="woomelly_attribute_variation_field_<?php echo $loop; ?>-<?php echo $xx; ?>" value="<?php echo $data_woomelly_attributes_field; ?>" maxlength="<?php echo $value_attribute->value_max_length; ?>" type="number" />
														<input name="woomelly_attribute_hidden_<?php echo $loop; ?>-<?php echo $xx; ?>" value="<?php echo $value_attribute->id . '::value_name'; ?>" type="hidden">
													</p>
												</div>
												<?php
											$xx++;
											unset( $data_woomelly_attributes_field );
											break;
										case 'number_unit':
											$data_woomelly_attributes_field = $wm_product_variation->get_value_attribute_field($value_attribute->id);
											if ( $data_woomelly_attributes_field == "" ) {
												$data_woomelly_attributes_field = array( "", "" );
											} ?>
											<div>
												<p class="form-field woomelly_attribute_variation_field form-row form-row-full">
													<label><?php echo $value_attribute->name; ?><?php if ( isset($value_attribute->tags->required) ) { echo '<span style="color: red; padding-left: 5px;">*</span>'; } ?></label>
													<input class="short wc_input_price" name="woomelly_attribute_variation_field_<?php echo $loop; ?>-<?php echo $xx; ?>" value="<?php echo $data_woomelly_attributes_field[0]; ?>" maxlength="<?php echo $value_attribute->value_max_length; ?>" type="number" />
													<select id="woomelly_attribute_variation_field" name="woomelly_attribute_variation_field_select_<?php echo $loop; ?>-<?php echo $xx; ?>" class="select short">
														<?php foreach ( $value_attribute->allowed_units as $value_allowed_unit ) { ?>
														<option value="<?php echo $value_allowed_unit->id; ?>" <?php selected( $data_woomelly_attributes_field[1], $value_allowed_unit->id );?>><?php echo $value_allowed_unit->name; ?></option>
														<?php } ?>
													</select>
													<input name="woomelly_attribute_hidden_<?php echo $loop; ?>-<?php echo $xx; ?>" value="<?php echo $value_attribute->id . '::number_unit'; ?>" type="hidden">
												</p>
											</div>
											<?php
											$xx++;
											unset( $data_woomelly_attributes_field );
											break;
										case 'boolean':
											$data_woomelly_attributes_field = $wm_product_variation->get_value_attribute_field($value_attribute->id);
												?>
												<div>
													<p class="form-field woomelly_attribute_variation_field form-row form-row-full">
														<label><?php echo $value_attribute->name; ?><?php if ( isset($value_attribute->tags->required) ) { echo '<span style="color: red; padding-left: 5px;">*</span>'; } ?></label>
														<select id="woomelly_attribute_variation_field" name="woomelly_attribute_variation_field_<?php echo $loop; ?>-<?php echo $xx; ?>" class="select short">
															<option value=""><?php echo __("- Select -", "woomelly"); ?></option>
															<?php foreach ( $value_attribute->values as $values ) { ?>
															<option value="<?php echo $values->id; ?>" <?php selected( $data_woomelly_attributes_field, $values->id ); ?>><?php echo $values->name; ?></option>
															<?php } ?>
														</select>
														<input name="woomelly_attribute_hidden_<?php echo $loop; ?>-<?php echo $xx; ?>" value="<?php echo $value_attribute->id . '::value_id'; ?>" type="hidden">
													</p>
												</div>
												<?php
											$xx++;
											unset( $data_woomelly_attributes_field );
											break;
										case 'list':
											$data_woomelly_attributes_field = $wm_product_variation->get_value_attribute_field($value_attribute->id);
												?>
												<div>
													<p class="form-field woomelly_attribute_variation_field form-row form-row-full">
														<label><?php echo $value_attribute->name; ?><?php if ( isset($value_attribute->tags->required) ) { echo '<span style="color: red; padding-left: 5px;">*</span>'; } ?></label>
														<select id="woomelly_attribute_variation_field" name="woomelly_attribute_variation_field_<?php echo $loop; ?>-<?php echo $xx; ?>" class="select short">
															<option value=""><?php echo __("- Select -", "woomelly"); ?></option>
															<?php foreach ( $value_attribute->values as $values ) { ?>
															<option value="<?php echo $values->id; ?>" <?php selected( $data_woomelly_attributes_field, $values->id ); ?>><?php echo $values->name; ?></option>
															<?php } ?>
														</select>
														<input name="woomelly_attribute_hidden_<?php echo $loop; ?>-<?php echo $xx; ?>" value="<?php echo $value_attribute->id . '::value_id'; ?>" type="hidden">
													</p>
												</div>
												<?php
											$xx++;
											unset( $data_woomelly_attributes_field );
											break;
									}
								}
							}
							echo '<input name="woomelly_total_attribute_variation_field_'.$loop.'" value="' . $xx . '" type="hidden" />';
						}
					}
				} else { ?>
					<div class="uk-alert-danger woomelly_alert_dont_connect">
						<span><?php echo sprintf( __( 'Sorry, you have a problem with your license or in connection with Mercadolibre. Verify that your website is %s', 'woomelly'), '<a href="'.admin_url( "admin.php?page=woomelly-settings" ).'" >'.__("connected and authorized correctly with Mercadolibre.", "woomelly").'</a>' ); ?></p>
					</div>					
				<?php } ?>
			</p>
			<?php
			if ( $woomelly_alive && $l_is_ok['result'] ) {
				if ( !empty($list_all_variations) ) {
					foreach ( $list_all_variations as $key => $att ) {							
						$data_to_send = array(
							'attribute_name' => $key,
							'attribute_value' => $att,
							'id' => '',
							'value_id' => ''
						);
						if ( !empty($attributes_meli) ) {
							foreach ($attributes_meli as $valueTemp ) {
								if ( isset($valueTemp['attribute_name']) && $key == $valueTemp['attribute_name'] ) {
									unset( $data_to_send );
									$data_to_send = $valueTemp;
									if ( !isset($data_to_send['attribute_value']) || $data_to_send['attribute_value'] == "" ) {
										$data_to_send['attribute_value'] = $att;
									}
									break;
								}
							}
						}
						?>
						<div>
							<p class="form-row woomelly_variation_field form-row-full" style="border-bottom: 1px solid #eee;">
								<label><?php echo sprintf( __( 'Mercadolibre Variations: %s', 'woomelly'), '<strong>'.ucfirst($_product_variation->get_attribute($key)).'</strong>' ); ?></label>
								<?php
								echo wc_help_tip( __("Mercadolibre Variations", "woomelly") );
								if ( $woomelly_category_field == "" ) {
									echo '<br>' . sprintf( __( 'Sorry, but it has no connection template assigned. Check the %s and assign one of them to this product.', 'woomelly'), '<a href="'.admin_url( "admin.php?page=woomelly-templatesync" ).'" >'.__("list", "woomelly").'</a>' );
								} else {
									$select_allow_variations = wm_get_select_attributes( $attributes, $data_to_send, $loop );
									if ( $select_allow_variations!="" ) {
										echo $select_allow_variations;
									} else {
										echo '<br>' . __("There are no attributes available to configure this variation.", "woomelly") . '<input type="hidden" value="" name="woomelly_attribute_field"/>';
									}
									unset( $select_allow_variations );
								}
								?>
							<p>
						</div>
						<?php
						unset($data_to_send);
					}						
				}
			}
		}
	} //End woomelly_woocommerce_product_after_variable_attributes()

	/**
	 * woomelly_woocommerce_save_product_variation.
	 *
	 * @return void
	 */
	public function woomelly_woocommerce_save_product_variation ( $post_id, $i ) {
		$wm_product = new WMProduct( $post_id );
		$attributes = array();
		$variations = array();
		$woomelly_alive = Woomelly()->woomelly_is_connect();
		$l_is_ok = Woomelly()->woomelly_status();

		if ( $woomelly_alive && $l_is_ok['result']) {
			if ( $wm_product->get_id() > 0 ) {
				if ( isset($_POST['woomelly_total_attribute_variation_field_'.$i]) && absint($_POST['woomelly_total_attribute_variation_field_'.$i]) > 0 ) {
					for ($jj=0; $jj < absint($_POST['woomelly_total_attribute_variation_field_'.$i]); $jj++) { 
						if ( isset($_POST['woomelly_attribute_variation_field_'.$i.'-'.$jj]) && isset($_POST['woomelly_attribute_hidden_'.$i.'-'.$jj]) ) {
							$data_attribute = explode("::", $_POST['woomelly_attribute_hidden_'.$i.'-'.$jj]);
							switch ($data_attribute[1]) {
								case 'value_name':
									$attributes[$data_attribute[0]] = 'value_name::' . $_POST['woomelly_attribute_variation_field_'.$i.'-'.$jj];
									break;
								case 'value_id':
									$attributes[$data_attribute[0]] = 'value_id::' . $_POST['woomelly_attribute_variation_field_'.$i.'-'.$jj];
									break;
								case 'number_unit':
									$attributes[$data_attribute[0]] = 'number_unit::' . $_POST['woomelly_attribute_variation_field_'.$i.'-'.$jj] . '::' . $_POST['woomelly_attribute_variation_field_select_'.$i.'-'.$jj];
									break;
							}
						}
					}
					$wm_product->set_woomelly_attribute_field( $attributes );
				}
				if ( isset($_POST['woomelly_attribute_field_'.$i]) ) {
					
					if ( !empty($_POST['woomelly_attribute_field_'.$i]) ) {
						foreach ( $_POST['woomelly_attribute_field_'.$i] as $value ) {
							$data = explode( '::', $value );
							if ( isset($data[0]) && isset($data[1]) && isset($data[2]) && isset($data[3]) && $data[2]=="-1" && $data[3]!="-1" ) {
								//custom
								$variations[] = array( 'id' => $data[3], 'value_name' => $data[1], 'attribute_name' => $data[0], 'attribute_value' => $data[1] );
							} else if ( isset($data[0]) && isset($data[1]) && isset($data[2]) && isset($data[3]) && $data[2]=="-1" && $data[3]=="-1" ) {
								//all custom
								$variations[] = array( 'name' => wc_attribute_label($data[0]), 'value_name' => $data[1], 'attribute_name' => $data[0], 'attribute_value' => $data[1] );
							} else if ( isset($data[0]) && isset($data[1]) && isset($data[2]) && isset($data[3]) && $data[2]!="-1" && $data[3]!="-1" ) {
								//variation with id
								$variations[] = array( 'id' => $data[3], 'value_id' => $data[2], 'attribute_name' => $data[0], 'attribute_value' => $data[1] );
							} else {
								//any
							}
							unset( $data );
						}
					}
					$wm_product->set_woomelly_variation_field( $variations );
				}
				if ( isset($_POST['woomelly_variation_dimentions_field_'.$i]) ) {
					$wm_product->set_woomelly_variation_dimentions_field($_POST['woomelly_variation_dimentions_field_'.$i]);
				} else {
					$wm_product->set_woomelly_variation_dimentions_field(false);
				}
				$variation_images = array();
				if ( isset($_POST['woomelly_variation_extra_img_url_1_'.$i]) ) {
					$variation_images[] = $_POST['woomelly_variation_extra_img_url_1_'.$i];
				}
				if ( isset($_POST['woomelly_variation_extra_img_url_2_'.$i]) ) {
					$variation_images[] = $_POST['woomelly_variation_extra_img_url_2_'.$i];
				}
				if ( isset($_POST['woomelly_variation_extra_img_url_3_'.$i]) ) {
					$variation_images[] = $_POST['woomelly_variation_extra_img_url_3_'.$i];
				}
				if ( isset($_POST['woomelly_variation_extra_img_url_4_'.$i]) ) {
					$variation_images[] = $_POST['woomelly_variation_extra_img_url_4_'.$i];
				}
				if ( isset($_POST['woomelly_variation_extra_img_url_5_'.$i]) ) {
					$variation_images[] = $_POST['woomelly_variation_extra_img_url_5_'.$i];
				}
				if ( isset($_POST['woomelly_variation_extra_img_url_6_'.$i]) ) {
					$variation_images[] = $_POST['woomelly_variation_extra_img_url_6_'.$i];
				}
				if ( isset($_POST['woomelly_variation_extra_img_url_7']) ) {
					$variation_images[] = $_POST['woomelly_variation_extra_img_url_7_'.$i];
				}
				if ( isset($_POST['woomelly_variation_extra_img_url_8_'.$i]) ) {
					$variation_images[] = $_POST['woomelly_variation_extra_img_url_8_'.$i];
				}
				$wm_product->set_woomelly_variation_extra_img_field( $variation_images );
			}
		}
	} //End woomelly_woocommerce_save_product_variation()

	/**
	 * woomelly_add_bulk_actions.
	 *
	 * @return void
	 */
	public function woomelly_add_bulk_actions ( $bulk_actions ) {
		$bulk_actions['wm_without_connect'] = __( '— Without connection —', 'woomelly');
		$bulk_actions['wm_connect'] = __( '— With connection —', 'woomelly');
		$bulk_actions['wm_active'] = __( '— Activate in Meli —', 'woomelly');
		$bulk_actions['wm_paused'] = __( '— Pause in Meli —', 'woomelly');
		$bulk_actions['wm_finish'] = __( '— Finish in Meli —', 'woomelly');
		$bulk_actions['wm_republish'] = __( '— Relist in Meli —', 'woomelly');
		$bulk_actions['wm_reset'] = __( '— Reset —', 'woomelly');
		return $bulk_actions;
	} //End woomelly_add_bulk_actions()

	/**
	 * woomelly_handle_bulk_actions.
	 *
	 * @return url redirect
	 */
	public function woomelly_handle_bulk_actions ( $redirect_to, $doaction, $post_ids ) {
		if ( !empty($post_ids) ) {
			switch ( $doaction ) {
				case 'wm_without_connect':			
					foreach ( $post_ids as $post_id ) {
						WMProduct::update_status_bulk_actions($post_id, 'wm_without_connect');
					}
					/*$redirect_to = add_query_arg( 'woomelly_bulk_wm_without_connect', count( $post_ids ), $redirect_to );
					return $redirect_to;*/
					wc_setcookie( 'wm_without_connect', count( $post_ids ) );
					break;
				case 'wm_connect':
					foreach ( $post_ids as $post_id ) {
						WMProduct::update_status_bulk_actions($post_id, 'wm_connect');
					}
					/*$redirect_to = add_query_arg( 'woomelly_bulk_wm_connect', count( $post_ids ), $redirect_to );
					return $redirect_to;*/
					wc_setcookie( 'wm_connect', count( $post_ids ) );
					break;
				case 'wm_active':
					foreach ( $post_ids as $post_id ) {
						if ( WMProduct::get_woomelly_status_meli_product( $post_id ) ) {
							WMProduct::update_status_bulk_actions($post_id, 'wm_active');
						}
					}
					/*$redirect_to = add_query_arg( 'woomelly_bulk_wm_active', count( $post_ids ), $redirect_to );
					return $redirect_to;*/
					wc_setcookie( 'wm_active', count( $post_ids ) );
					break;
				case 'wm_paused':
					foreach ( $post_ids as $post_id ) {
						if ( WMProduct::get_woomelly_status_meli_product( $post_id ) ) {
							WMProduct::update_status_bulk_actions($post_id, 'wm_paused');
						}
					}
					/*$redirect_to = add_query_arg( 'woomelly_bulk_wm_paused', count( $post_ids ), $redirect_to );
					return $redirect_to;*/
					wc_setcookie( 'wm_paused', count( $post_ids ) );
					break;
				case 'wm_finish':
					foreach ( $post_ids as $post_id ) {
						if ( WMProduct::get_woomelly_status_meli_product( $post_id ) ) {
							WMProduct::update_status_bulk_actions($post_id, 'wm_finish');
						}
					}
					/*$redirect_to = add_query_arg( 'woomelly_bulk_wm_finish', count( $post_ids ), $redirect_to );
					return $redirect_to;*/
					wc_setcookie( 'wm_finish', count( $post_ids ) );
					break;
				case 'wm_republish':
					foreach ( $post_ids as $post_id ) {
						if ( WMProduct::get_woomelly_status_meli_product( $post_id ) ) {
							WMProduct::update_status_bulk_actions($post_id, 'wm_republish');
						}
					}				
					/*$redirect_to = add_query_arg( 'woomelly_bulk_wm_republish', count( $post_ids ), $redirect_to );
					return $redirect_to;*/
					wc_setcookie( 'wm_republish', count( $post_ids ) );
					break;
				case 'wm_reset':
					foreach ( $post_ids as $post_id ) {
						WMProduct::reset( $post_id );
					}				
					/*$redirect_to = add_query_arg( 'woomelly_bulk_wm_reset', count( $post_ids ), $redirect_to );
					return $redirect_to;*/
					wc_setcookie( 'wm_reset', count( $post_ids ) );
					break;
				default:
					break;
			}
		}

		return $redirect_to;
	} //End woomelly_handle_bulk_actions()

	/**
	 * woomelly_admin_warnings.
	 *
	 * @return string
	 */	
	public function woomelly_admin_warnings () {
		if ( isset($_COOKIE['wm_without_connect']) && $_COOKIE['wm_without_connect'] != "" ) {
			$_count = intval( $_COOKIE['wm_without_connect'] );
			echo '<div class="updated"><p>' . sprintf( _n( 'Product updated correctly: Without Connect status.', 'Products updated correctly: Without Connect status. Total products modified: %s', $_count, 'woomelly' ), number_format_i18n( $_count ) ) . '</p></div>';
			wc_setcookie( 'wm_without_connect', '', time() - HOUR_IN_SECONDS );
		}
		if ( isset($_COOKIE['wm_connect']) && $_COOKIE['wm_connect'] != "" ) {
			$_count = intval( $_COOKIE['wm_connect'] );
	        echo '<div class="updated"><p>' . sprintf( _n( 'Product updated correctly: Connect status.', 'Products updated correctly: Connect status. Total products modified: %s', $_count, 'woomelly' ), number_format_i18n( $_count ) ) . '</p></div>';
			wc_setcookie( 'wm_connect', '', time() - HOUR_IN_SECONDS );
		}
		if ( isset($_COOKIE['wm_active']) && $_COOKIE['wm_active'] != "" ) {
			$_count = intval( $_COOKIE['wm_active'] );
	        echo '<div class="updated"><p>' . sprintf( _n( 'Product updated correctly: Active status.', 'Products updated correctly: Active status. Total products modified: %s', $_count, 'woomelly' ), number_format_i18n( $_count ) ) . '</p></div>';
			wc_setcookie( 'wm_active', '', time() - HOUR_IN_SECONDS );
		}
		if ( isset($_COOKIE['wm_paused']) && $_COOKIE['wm_paused'] != "" ) {
			$_count = intval( $_COOKIE['wm_paused'] );
	        echo '<div class="updated"><p>' . sprintf( _n( 'Product updated correctly: Paused status.', 'Products updated correctly: Paused status. Total products modified: %s', $_count, 'woomelly' ), number_format_i18n( $_count ) ) . '</p></div>';
			wc_setcookie( 'wm_paused', '', time() - HOUR_IN_SECONDS );
		}
		if ( isset($_COOKIE['wm_finish']) && $_COOKIE['wm_finish'] != "" ) {
			$_count = intval( $_COOKIE['wm_finish'] );
	        echo '<div class="updated"><p>' . sprintf( _n( 'Product updated correctly: Finish status.', 'Products updated correctly: Finish status. Total products modified: %s', $_count, 'woomelly' ), number_format_i18n( $_count ) ) . '</p></div>';
			wc_setcookie( 'wm_finish', '', time() - HOUR_IN_SECONDS );
		}
		if ( isset($_COOKIE['wm_republish']) && $_COOKIE['wm_republish'] != "" ) {
			$_count = intval( $_COOKIE['wm_republish'] );
	        echo '<div class="updated"><p>' . sprintf( _n( 'Product updated correctly: Republish status.', 'Products updated correctly: Republish status. Total products modified: %s', $_count, 'woomelly' ), number_format_i18n( $_count ) ) . '</p></div>';
			wc_setcookie( 'wm_republish', '', time() - HOUR_IN_SECONDS );
		}
		if ( isset($_COOKIE['wm_reset']) && $_COOKIE['wm_reset'] != "" ) {
			$_count = intval( $_COOKIE['wm_reset'] );
	        echo '<div class="updated"><p>' . sprintf( _n( 'Product updated correctly: Reset.', 'Products updated correctly: Reset. Total products modified: %s', $_count, 'woomelly' ), number_format_i18n( $_count ) ) . '</p></div>';
			wc_setcookie( 'wm_reset', '', time() - HOUR_IN_SECONDS );
		}
		/*$_errors = array();
		$memory = memory_num( WP_MEMORY_LIMIT );
		if ( $memory < 127108864 ) {
			$_errors[] = '<p><strong style="color: red;">Current memory limit: ' . size_format( $memory ) . '</strong> | We recommend setting memory to at least 128MB. See: <a href="http://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP">Increasing memory allocated to PHP</a></p>';
		}
		if ( !empty($_errors) ) {
			foreach ( $_errors as $_error ) {
				echo '<div class="updated notice is-dismissible"> <p>'.$_error.'</p> </div>';
			}
		}*/
	} //End woomelly_admin_warnings()





	/**
	 * woomelly_admin_warnings.
	 *
	 * @return string
	 */	
	public function woomelly_handle_apiml_requests () {
		http_response_code();
		$wm_data = array();

		if ( isset($_GET['wm-api']) && trim($_GET['wm-api'])==="mercadolibre" ) {
			// @codingStandardsIgnoreStart
			// $HTTP_RAW_POST_DATA is deprecated on PHP 5.6.
			if ( function_exists( 'phpversion' ) && version_compare( phpversion(), '5.6', '>=' ) ) {
				$wm_data = json_decode( file_get_contents( 'php://input' ) );
			} else {
				global $HTTP_RAW_POST_DATA;

				// A bug in PHP < 5.2.2 makes $HTTP_RAW_POST_DATA not set by default,
				// but we can do it ourself.
				if ( ! isset( $HTTP_RAW_POST_DATA ) ) {
					$HTTP_RAW_POST_DATA = json_decode( file_get_contents( 'php://input' ) );
					$wm_data = $HTTP_RAW_POST_DATA;
				}
			}

			//do_action( 'woomelly_notification_orders', '/orders/1702357712', '2018-05-06T20:23:10.833Z' );

			if ( !empty($wm_data) ) {
				if ( isset($wm_data->resource) && isset($wm_data->topic) && isset($wm_data->application_id) && isset($wm_data->sent) ) {
					if ( $wm_data->resource!="" && $wm_data->topic!="" && $wm_data->application_id!="" && $wm_data->sent!="" ) {
						$wm_settings_page = new WMSettings();
						if ( $wm_data->application_id == $wm_settings_page->get_app_id() ) {
							switch ( $wm_data->topic ) {
								case 'items':
									break;
								case 'questions':
									break;
								case 'payments':
									break;
								case 'pictures':
									break;
								case 'messages':
									break;
								case 'orders_v2':
									$settings_extensions = $wm_settings_page->get_settings_extensions();									
									if ( $settings_extensions['order'] ) {
										$last_notification = WMNotification::get_woomelly_last_notification();
										//$last_resource = WMNotification::get_woomelly_last_resource();
										WMNotification::set_woomelly_last_notification( $wm_data->sent );
										//WMNotification::set_woomelly_last_resource( $wm_data->resource );
										//if ( $last_resource === $wm_data->resource ) {
											$_diff_time = wm_diff_time( $wm_data->sent, $last_notification );
											if ( $last_notification == "" || $_diff_time > 0  ) {
												Woomelly()->woomelly_set_log( "OK " . $wm_data->sent . " - ".$last_notification, 'notification' );
												do_action( 'woomelly_notification_orders', $wm_data->resource, $wm_data->sent );
												
											}
										/*} else {
											Woomelly()->woomelly_set_log( "OK " . $wm_data->sent . " - ".$last_notification, 'notification' );
											do_action( 'woomelly_notification_orders', $wm_data->resource, $wm_data->sent );
										}*/
									}
									break;
								case 'orders':
									$settings_extensions = $wm_settings_page->get_settings_extensions();									
									if ( $settings_extensions['order'] ) {
										$last_notification = WMNotification::get_woomelly_last_notification();
										//$last_resource = WMNotification::get_woomelly_last_resource();
										WMNotification::set_woomelly_last_notification( $wm_data->sent );
										//WMNotification::set_woomelly_last_resource( $wm_data->resource );
										//if ( $last_resource === $wm_data->resource ) {
											$_diff_time = wm_diff_time( $wm_data->sent, $last_notification );
											if ( $last_notification == "" || $_diff_time > 0  ) {
												Woomelly()->woomelly_set_log( "OK " . $wm_data->sent . " - ".$last_notification, 'notification' );
												do_action( 'woomelly_notification_orders', $wm_data->resource, $wm_data->sent );
												
											}
										/*} else {
											Woomelly()->woomelly_set_log( "OK " . $wm_data->sent . " - ".$last_notification, 'notification' );
											do_action( 'woomelly_notification_orders', $wm_data->resource, $wm_data->sent );
										}*/
									}
									break;
								case 'shipments':
									break;
							}
						}
					}
				}
			}
		}
		// @codingStandardsIgnoreEnd
	}


}

return new WMAdminMenu();