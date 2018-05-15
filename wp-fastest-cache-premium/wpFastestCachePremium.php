<?php
/*
Plugin Name: WP Fastest Cache Premium
Plugin URI: http://www.wpfastestcache.com/
Description: The Premium Version of WP Fastest Cache
Version: 1.4.3
Author: Emre Vona
Author URI: http://tr.linkedin.com/in/emrevona
Licence: GPLv2

Copyright (C)2014 Emre Vona
*/
	if (!defined('WPFC_WP_CONTENT_BASENAME')) {
		if (!defined('WPFC_WP_PLUGIN_DIR')) {
			if(preg_match("/(\/trunk\/|\/wp-fastest-cache-premium\/)$/", plugin_dir_path( __FILE__ ))){
				define("WPFC_WP_PLUGIN_DIR", preg_replace("/(\/trunk\/|\/wp-fastest-cache-premium\/)$/", "", plugin_dir_path( __FILE__ )));
			}else if(preg_match("/\\\wp-fastest-cache-premium\/$/", plugin_dir_path( __FILE__ ))){
				//D:\hosting\LINEapp\public_html\wp-content\plugins\wp-fastest-cache/
				define("WPFC_WP_PLUGIN_DIR", preg_replace("/\\\wp-fastest-cache-premium\/$/", "", plugin_dir_path( __FILE__ )));
			}
		}

		define("WPFC_WP_CONTENT_DIR", dirname(WPFC_WP_PLUGIN_DIR));
		define("WPFC_WP_CONTENT_BASENAME", basename(WPFC_WP_CONTENT_DIR));
	}
	

	if(!isset($GLOBALS["wp_fastest_cache_options"])){
		if($wp_fastest_cache_options = get_option("WpFastestCache")){
			$GLOBALS["wp_fastest_cache_options"] = json_decode($wp_fastest_cache_options);
		}else{
			$GLOBALS["wp_fastest_cache_options"] = array();
		}
	}

	add_action('wp_ajax_wpfc_image_credit_template_ajax_request', "wpfc_image_credit_template_ajax_request");

	function wpfc_image_credit_template_ajax_request(){
		include_once(WPFC_WP_PLUGIN_DIR."/wp-fastest-cache-premium/pro/templates/buy_credit.php");
		exit;
	}

	if(!is_admin()){
		if(isset($GLOBALS["wp_fastest_cache_options"]->wpFastestCacheStatus)){
			if(isset($GLOBALS["wp_fastest_cache_options"]->wpFastestCacheLazyLoad)){
				include_once plugin_dir_path( __FILE__ )."pro/library/lazy-load.php";
				
				$lazy = new WpFastestCacheLazyLoad();

				add_filter( 'wp_get_attachment_image_attributes', array($lazy, "mark_attachment_page_images"));
				add_filter( 'the_content', array($lazy, "mark_content_images"), 99);
			}
		}

		if(isset($GLOBALS["wp_fastest_cache_options"]->wpFastestCacheWidgetCache)){
			if(isset($GLOBALS["wp_fastest_cache_options"]->wpFastestCacheWidgetCache)){
				include_once plugin_dir_path( __FILE__ )."pro/library/widget-cache.php";

				WpfcWidgetCache::action();
			}
		}

		if(isset($GLOBALS["wp_fastest_cache_options"]->wpFastestCacheMobileTheme) && $GLOBALS["wp_fastest_cache_options"]->wpFastestCacheMobileTheme){
			if(isset($GLOBALS["wp_fastest_cache_options"]->wpFastestCacheMobileTheme_themename) && $GLOBALS["wp_fastest_cache_options"]->wpFastestCacheMobileTheme_themename){
				add_action('plugins_loaded', 'wpfc_mts_init', 1);
				
				function wpfc_mts_init(){
					if(isset($GLOBALS['wp_fastest_cache']) && method_exists($GLOBALS['wp_fastest_cache'], 'get_operating_systems')){
						$is_mobile = false;

						foreach ($GLOBALS['wp_fastest_cache']->get_mobile_browsers() as $value) {
							if(preg_match("/".$value."/i", $_SERVER['HTTP_USER_AGENT'])){
								$is_mobile = true;
							}
						}

						foreach ($GLOBALS['wp_fastest_cache']->get_operating_systems() as $key => $value) {
							if(preg_match("/".$value."/i", $_SERVER['HTTP_USER_AGENT'])){
								$is_mobile = true;
							}
						}
					}

					if($is_mobile){
						$themes = wp_get_themes();
						$GLOBALS["wp_fastest_cache_mobile_theme_obj"] = $themes[$GLOBALS["wp_fastest_cache_options"]->wpFastestCacheMobileTheme_themename];

						add_filter('stylesheet', 'wpfc_load_mobile_style');
						add_filter('template', 'wpfc_load_mobile_theme');
					}
				}

				function wpfc_load_mobile_style(){
					return $GLOBALS["wp_fastest_cache_mobile_theme_obj"]->get_template();
				}

				function wpfc_load_mobile_theme(){
					return $GLOBALS["wp_fastest_cache_mobile_theme_obj"]->get_stylesheet();
				}
			}
		}
	}else{
		add_action('delete_attachment', 'wpfc_delete_webp');

		function wpfc_delete_webp($id){
			if(isset($id)){
				$data = wp_get_attachment_metadata($id);

				if(isset($data["file"])){
					$path = WPFC_WP_CONTENT_DIR."/uploads/".dirname($data["file"]);

					if(file_exists($path."/".basename($data["file"]).".webp")){
						@unlink($path."/".basename($data["file"]).".webp");
					}

					if(isset($data["file"])){
						foreach ($data["sizes"] as $key => $value) {
							if(file_exists($path."/".$value["file"].".webp")){
								@unlink($path."/".$value["file"].".webp");
							}
						}
					}
				}
			}
		}

		include_once plugin_dir_path( __FILE__ )."pro/library/widget-cache.php";
		WpfcWidgetCache::add_filter_admin();

		include_once WPFC_WP_PLUGIN_DIR."/wp-fastest-cache-premium/pro/library/admin.php";
		$wpfcpa = new WPFC_PREMIUM_ADMIN();
	}
?>