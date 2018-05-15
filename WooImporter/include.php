<?php

//date_default_timezone_set('GMT');

if (!defined('WPEAE_NAME')) {
	define('WPEAE_NAME', 'WooImporter');
}

if (!defined('WPEAE_TABLE_LOG')) {
	define('WPEAE_TABLE_LOG', 'wpeae_log');
}

if (!defined('WPEAE_TABLE_GOODS')) {
	define('WPEAE_TABLE_GOODS', 'wpeae_goods');
}

if (!defined('WPEAE_TABLE_ACCOUNT')) {
	define('WPEAE_TABLE_ACCOUNT', 'wpeae_account');
}

if (!defined('WPEAE_TABLE_PRICE_FORMULA')) {
	define('WPEAE_TABLE_PRICE_FORMULA', 'wpeae_price_formula');
}

if (!defined('WPEAE_NO_IMAGE_URL')) {
	define('WPEAE_NO_IMAGE_URL', 'http://pics.ebaystatic.com/aw/pics/express/icons/iconPlaceholder_96x96.gif');
}

if (!defined('WPEAE_DEL_COOKIES_FILE_AFTER')) {
	define('WPEAE_DEL_COOKIES_FILE_AFTER', 2 * 24 * 60 * 60);
}

include_once(dirname(__FILE__) . '/includes/WPEAE_Log.php');

include_once(dirname(__FILE__) . '/includes/WPEAE_Http_Cookie.php');
include_once(dirname(__FILE__) . '/includes/WPEAE_Http.php');
include_once(dirname(__FILE__) . '/includes/WPEAE_Requests_Response.php');

include_once(dirname(__FILE__) . '/includes/api/WPEAE_Helper.php');
include_once(dirname(__FILE__) . '/includes/api/WPEAE_Goods.php');
include_once(dirname(__FILE__) . '/includes/api/WPEAE_AbstractAccount.php');
include_once(dirname(__FILE__) . '/includes/api/WPEAE_AbstractLoader.php');
include_once(dirname(__FILE__) . '/includes/api/WPEAE_AbstractConfigurator.php');
include_once(dirname(__FILE__) . '/includes/api/WPEAE_WooCommerce.php');
include_once(dirname(__FILE__) . '/includes/api/WPEAE_PriceFormula.php');

include_once(dirname(__FILE__) . '/includes/WPEAE_Utils.php');
include_once(dirname(__FILE__) . '/includes/WPEAE_DashboardPage.php');
include_once(dirname(__FILE__) . '/includes/WPEAE_SettingsPage.php');
include_once(dirname(__FILE__) . '/includes/WPEAE_AddonsPage.php');
include_once(dirname(__FILE__) . '/includes/WPEAE_WooCommerce_ProductList.php');
include_once(dirname(__FILE__) . '/includes/WPEAE_WooCommerce_OrderItem.php');
include_once(dirname(__FILE__) . '/includes/WPEAE_WooCommerce_OrderList.php');


include_once(dirname(__FILE__) . '/includes/WPEAE_Ajax.php');

if (file_exists(dirname(__FILE__) . '/includes/WPEAE_Update.php')) {
	include_once(dirname(__FILE__) . '/includes/WPEAE_Update.php');
}

$WPEAE_GLOBAL_API_LIST = array();

if (!function_exists('wpeae_add_api')) {

	function wpeae_add_api($api_configurator) {
		global $WPEAE_GLOBAL_API_LIST;
		if (!is_array($WPEAE_GLOBAL_API_LIST)) {
			$WPEAE_GLOBAL_API_LIST = array();
		}
		if ($api_configurator instanceof WPEAE_AbstractConfigurator) {
			$find = false;
			foreach ($WPEAE_GLOBAL_API_LIST as $tmp_api) {
				if ($tmp_api->get_type() === $api_configurator->get_type()) {
					$find = true;
					break;
				}
			}
			if (!$find) {
				$WPEAE_GLOBAL_API_LIST[$api_configurator->get_type()] = $api_configurator;
			}
		}
	}

}

/* include api modules */
foreach (glob(WPEAE_ROOT_PATH . 'includes/api/*', GLOB_ONLYDIR) as $dir) {
	$file_list = scandir($dir . '/');
	$include_array = array();
	foreach ($file_list as $f) {
		if (is_file($dir . '/' . $f)) {
			$file_info = pathinfo($f);
			if ($file_info["extension"] == "php") {
				$file_data = get_file_data($dir . '/' . $f, array('position' => '@position'));
				$include_array[$dir . '/' . $f] = IntVal($file_data['position']);
			}
		}
	}
	asort($include_array);
	foreach ($include_array as $file => $p) {
		include_once($file);
	}
}
/* include api modules */

/* include addons */
$dirs = glob(WPEAE_ROOT_PATH . 'addons/*', GLOB_ONLYDIR);
if ($dirs && is_array($dirs)) {
	foreach (glob(WPEAE_ROOT_PATH . 'addons/*', GLOB_ONLYDIR) as $dir) {
		$file_list = scandir($dir . '/');
		foreach ($file_list as $f) {
			if (is_file($dir . '/' . $f)) {
				$file_info = pathinfo($f);
				if ($file_info["extension"] == "php") {
					include_once($dir . '/' . $f);
				}
			}
		}
	}
}
/* include addons */

if (!function_exists('wpeae_get_api_list')) {

	function wpeae_get_api_list($installed_only = false) {
		global $WPEAE_GLOBAL_API_LIST;
		$api_list = array();

		foreach ($WPEAE_GLOBAL_API_LIST as $api) {
			if ($api instanceof WPEAE_AbstractConfigurator && (!$installed_only || $api->is_instaled())) {
				$api_list[$api->get_type()] = $api;
			}
		}
		return $api_list;
	}

}

if (!function_exists('wpeae_get_api')) {

	function wpeae_get_api($type) {
		foreach (wpeae_get_api_list() as /* @var $api WPEAE_AbstractConfigurator */ $api) {
			if ($api->get_type() == $type) {
				return $api;
			}
		}
		return false;
	}

}

if (!function_exists('wpeae_get_default_api')) {

	function wpeae_get_default_api() {
		$api_list = wpeae_get_api_list();

		foreach ($api_list as $api) {
			if ($api->is_instaled()) {
				return $api;
			}
		}
		return false;
	}

}

if (!function_exists('wpeae_get_root_menu_id')) {

	function wpeae_get_root_menu_id() {
		$default_api = wpeae_get_default_api();
		return WPEAE_ROOT_MENU_ID . ($default_api ? ("-" . $default_api->get_type()) : "");
	}

}

if (!function_exists('wpeae_get_loader')) {

	function wpeae_get_loader($type) {
		$api_list = wpeae_get_api_list();
		foreach ($api_list as $api) {
			if ($api->get_type() === $type && class_exists($api->get_config_value("loader_class"))) {
				$class_name = $api->get_config_value("loader_class");
				return apply_filters('wpeae_get_loader', new $class_name($api));
			}
		}
		return false;
	}

}

if (!function_exists('wpeae_get_account')) {

	function wpeae_get_account($type) {
		$api_list = wpeae_get_api_list();
		foreach ($api_list as $api) {
			if ($api->get_type() === $type && class_exists($api->get_config_value("account_class"))) {
				$class_name = $api->get_config_value("account_class");
				return apply_filters('wpeae_get_account', new $class_name($api));
			}
		}
		return false;
	}

}

if (!function_exists('wpeae_get_api_path')) {

	function wpeae_get_api_path($api) {
		if ($api instanceof WPEAE_AbstractConfigurator) {
			return WPEAE_ROOT_PATH . 'includes/api/' . $api->get_type() . '/';
		}
		return "";
	}

}

if (!function_exists('wpeae_get_api_url')) {

	function wpeae_get_api_url($api) {
		if ($api instanceof WPEAE_AbstractConfigurator) {
			return WPEAE_ROOT_URL . 'includes/api/' . $api->get_type() . '/';
		}
		return false;
	}

}

if (!function_exists('wpeae_api_enqueue_style')) {

	function wpeae_api_enqueue_style($api) {
		$dirs = glob(wpeae_get_api_path($api) . 'styles/', GLOB_ONLYDIR);
		if ($dirs && is_array($dirs)) {
			foreach (glob(wpeae_get_api_path($api) . 'styles/', GLOB_ONLYDIR) as $dir) {
				$file_list = scandir($dir . '/');
				foreach ($file_list as $f) {
					if (is_file($dir . '/' . $f)) {
						$file_info = pathinfo($f);
						if ($file_info["extension"] == "css") {
							wp_enqueue_style('wpeae-' . $api->get_type() . '-' . $file_info["filename"], wpeae_get_api_url($api) . 'styles/' . $file_info["basename"], array(), $api->get_config_value("version"));
						}
					}
				}
			}
		}
	}

}

if (!function_exists('wpeae_error_handler')) {

	function wpeae_error_handler($errno, $errstr, $errfile, $errline) {
		if (!(error_reporting() & $errno)) {
			return false;
		}

		switch ($errno) {
			case E_USER_ERROR:
				$mess = "<b>ERROR</b> [$errno] $errstr<br />\n Fatal error on line $errline in file $errfile, PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
				throw new Exception($mess);
			case E_USER_WARNING:
				$mess = "<b>My WARNING</b> [$errno] $errstr<br />\n";
				throw new Exception($mess);

			case E_USER_NOTICE:
				$mess = "<b>My NOTICE</b> [$errno] $errstr<br />\n";
				throw new Exception($mess);

			default:
				$mess = "<b>ERROR</b> [$errno] on line $errline in file $errfile: $errstr<br />\n";
				throw new Exception($mess);
		}

		/* Don't execute PHP internal error handler */
		return true;
	}

}

if (!function_exists('wpeae_write_log')) {

	function wpeae_write_log($message) {

		if ($message instanceof WP_error) {
			foreach ($message->get_error_codes() as $error_code) {
				foreach ($message->get_error_messages($error_code) as $error_message) {
					error_log($error_code . ": " . $error_message);
				}
			}
		} else if (is_array($message) || is_object($message)) {

			error_log(print_r($message, true));
		} else {
			error_log($message);
		}
	}

}

if (!function_exists('wpeae_add_js_hook')) {

	function wpeae_add_js_hook(&$result, $hook_name, $params) {
		if (!isset($result) || !$result) {
			$result = array();
		}

		if (!isset($result['js_hook'])) {
			$result['js_hook'] = array();
		} else if (!is_array($result['js_hook'])) {
			$result['js_hook'] = array($result['js_hook']);
		}
		$result['js_hook'][] = array('name' => $hook_name, 'params' => $params);

		return $result;
	}

}


if (!function_exists('wpeae_get_goods_by_post_id')) {

	function wpeae_get_goods_by_post_id($post_id) {
		$goods = false;
		if ($post_id) {
			$external_id = get_post_meta($post_id, "external_id", true);
			if ($external_id) {
				$goods = new WPEAE_Goods($external_id);
				$cats = wp_get_object_terms($post_id, 'product_cat');
				if (!is_wp_error($cats) && $cats) {
                                    $cats_ids = array();
                                    foreach($cats as $c){
                                        $cats_ids[] = $c->term_id;
                                    }
                                    $goods->link_category_id = $cats_ids;
                                    
                                    //$goods->link_category_id = $cats[0]->term_id;
				}
                                $goods->title = get_the_title($post_id);
                                $goods->additional_meta = array();
                                $original_product_url = get_post_meta($post_id, "original_product_url", true);
                                $goods->additional_meta['detail_url'] = $original_product_url?$original_product_url:'www.aliexpress.com/item//' . $goods->external_id . '.html';    
                                
                                $availability_meta = get_post_meta($post_id, "_wpeae_availability", true);
                                $goods->availability = $availability_meta?filter_var($availability_meta, FILTER_VALIDATE_BOOLEAN):true;
			}
		}
		return $goods;
	}

}

if (!function_exists('is_wpeae_goods')) {
	function is_wpeae_goods($post_id) {
		return get_post_meta($post_id, "external_id", true)?true:false;
	}
}

if (!function_exists('wpeae_get_sorted_products_ids')) {

	function wpeae_get_sorted_products_ids($sort_type, $ids_count) {
		// TODO add external_id exist check
		$result = array();

		$api_type_list = array();
		$api_list = wpeae_get_api_list(true);
		foreach ($api_list as $api) {
			$api_type_list[] = $api->get_type();
		}

		$ids0 = get_posts(array(
			'post_type' => 'product',
			'fields' => 'ids',
			'numberposts' => $ids_count,
			'meta_query' => array(
				array(
					'key' => 'import_type',
					'value' => $api_type_list,
					'compare' => 'IN'
				),
				array(
					'key' => $sort_type,
					'compare' => 'NOT EXISTS'
				)
			)
		));

		foreach ($ids0 as $id) {
			$result[] = $id;
		}

		if (($ids_count - count($result)) > 0) {
			$res = get_posts(array(
				'post_type' => 'product',
				'fields' => 'ids',
				'numberposts' => ($ids_count - count($result)),
				'meta_query' => array(
					array(
						'key' => 'import_type',
						'value' => $api_type_list,
						'compare' => 'IN'
					)
				),
				'order' => 'ASC',
				'orderby' => 'meta_value',
				'meta_key' => $sort_type,
				//allow hooks
				'suppress_filters' => false
			));

			foreach ($res as $id) {
				$result[] = $id;
			}
		}
		return $result;
	}

}
if (!function_exists('wpeae_remote_get')) {

	// args=array(headers=>array(), options=>array())
	function wpeae_remote_get($url, $args = array()) {

		//$response = Requests::get('https://github.com/timeline.json');
		//'user-agent' => 'Toolkit/1.7.3'
		$def_args = array('headers' => array('Accept-Encoding' => ''), 'timeout' => 30, 'useragent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36', 'verify' => false, 'sslverify' => false, 'verifyname'=>false);


		if (!is_array($args)) {
			$args = array();
		}

		foreach ($def_args as $key => $val) {
			if (!isset($args[$key])) {
				$args[$key] = $val;
			}
		}

		if (isset($args['headers'])) {
			$headers = $args['headers'];
			unset($args['headers']);
		}

		// If we've got cookies, use and convert them to Requests_Cookie.
		if (!empty($args['cookies'])) {
			$args['cookies'] = WPEAE_Http::normalize_cookies($args['cookies']);
		}

		try {
			// Avoid issues where mbstring.func_overload is enabled.
			if (function_exists('mbstring_binary_safe_encoding')) {
				mbstring_binary_safe_encoding();
			}else{
				if(defined('WP_DEBUG') && WP_DEBUG){
					error_log('WARNING! function mbstring_binary_safe_encoding is not exist!');
				}
			}
			
			$requests_response = Requests::get($url, $headers, $args);

			// Convert the response into an array
			$http_response = new WPEAE_Requests_Response($requests_response);
			$response = $http_response->to_array();

			// Add the original object to the array.
			$response['http_response'] = $http_response;
		} catch (Requests_Exception $e) {
			$response = new WP_Error('http_request_failed', $e->getMessage());
		}
		return $response;
	}

}

if (!function_exists('wpeae_cookies_file_path')) {

	function wpeae_cookies_file_path($proxy = "") {
		$proxy = ($proxy && is_array($proxy))?$proxy[0]:$proxy;
		$proxy_path = $proxy ? ("_" . str_replace(array(".", ":"), "_", $proxy)) : "";
		$file_path = WP_CONTENT_DIR . "/wpeae_cookie" . $proxy_path . ".txt";

		if (WPEAE_DEL_COOKIES_FILE_AFTER && file_exists($file_path)) {
			$time_upd = filemtime($file_path);

			if (abs(time() - $time_upd) > WPEAE_DEL_COOKIES_FILE_AFTER) {
				unlink($file_path);
			}
		}

		return $file_path;
	}

}

if (!function_exists('wpeae_proxy_get')) {

	function wpeae_proxy_get() {
		$proxy = "";
		if (get_option('wpeae_use_proxy', false)) {
			$proxies_str = str_replace(" ", "", get_option('wpeae_proxies_list', ''));
			$proxies_str = str_replace("\n", ";", $proxies_str);

			$arr_proxies = explode(";", $proxies_str);

			$arr_proxies = apply_filters('wpeae_get_proxy_list', $arr_proxies);

			$proxies = array();
			foreach ($arr_proxies as $k => $v) {
				$proxies[$k] = trim($v);
			}

			if ($proxies) {
				$proxy = $proxies[array_rand($proxies)];
				
				
				if($proxy){
					
					//username:password@proxy.example.com:8080
					$res_proxy_url = "";
					$res_proxy_user = "";
					$res_proxy_pass = "";
					$proxy = explode("@", $proxy);
					if (count($proxy) == 1) {
						$proxy_url = explode(":", $proxy[0]);
						if (count($proxy_url) == 1) {
							$res_proxy_url = $proxy_url[0].":80";
						} else if (count($proxy_url) == 2) {
							$res_proxy_url = $proxy_url[0].":".$proxy_url[1];
						}
					} else if (count($proxy) == 2) {
						$proxy_auth = explode(":", $proxy[0]);
						if (count($proxy_auth) == 1) {
							$res_proxy_user = $proxy_auth[0];
							$res_proxy_pass = '';
						} else if (count($proxy_auth) == 2) {
							$res_proxy_user = $proxy_auth[0];
							$res_proxy_pass = $proxy_auth[1];
						}

						$proxy_url = explode(":", $proxy[1]);
						if (count($proxy_url) == 1) {
							$res_proxy_url = $proxy_url[0].":80";
							
						} else if (count($proxy_url) == 2) {
							$res_proxy_url = $proxy_url[0].":".$proxy_url[1];
						}
					}
					
					if($res_proxy_user){
						$proxy = array( $res_proxy_url, $res_proxy_user, $res_proxy_pass);
					}else{
						$proxy = $res_proxy_url;
					}
					
				}
				
				
			}
		}
		return $proxy;
	}

}