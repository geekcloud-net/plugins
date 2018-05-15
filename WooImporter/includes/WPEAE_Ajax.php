<?php

/**
 * Description of WPEAE_Ajax
 *
 * @author Geometrix
 */
if (!class_exists('WPEAE_Ajax')):

	class WPEAE_Ajax {

		function __construct() {
			
			
			add_action('wp_ajax_wpeae_product_info', array($this, 'product_info'));
			add_action('wp_ajax_wpeae_order_info', array($this, 'order_info'));
			
			add_action('wp_ajax_wpeae_edit_goods', array($this, 'edit_goods'));
			add_action('wp_ajax_wpeae_select_image', array($this, 'select_image'));
			add_action('wp_ajax_wpeae_load_details', array($this, 'load_details'));
			add_action('wp_ajax_wpeae_import_goods', array($this, 'import_goods'));
			add_action('wp_ajax_wpeae_load_and_import_goods', array($this, 'load_and_import_goods'));
			add_action('wp_ajax_wpeae_update_goods', array($this, 'update_goods'));
			
			add_action('wp_ajax_wpeae_schedule_import_goods', array($this, 'schedule_import_goods'));
			add_action('wp_ajax_wpeae_upload_image', array($this, 'upload_image'));

			add_action('wp_ajax_wpeae_description_editor', array($this, 'description_editor'));

			add_action('wp_ajax_wpeae_price_formula_get', array($this, 'price_formula_get'));
			add_action('wp_ajax_wpeae_price_formula_add', array($this, 'price_formula_add'));
			add_action('wp_ajax_wpeae_price_formula_edit', array($this, 'price_formula_edit'));
			add_action('wp_ajax_wpeae_price_formula_del', array($this, 'price_formula_del'));
						
			add_action('wp_ajax_wpeae_proxy_test', array($this, 'proxy_test'));
		}

		function product_info(){
			$result = array("state" => "ok", "data" => "");
			
			$post_id = isset($_POST['id']) ? $_POST['id'] : false;
			
			if (!$post_id) {
				$result['state'] = 'error';
				echo json_encode( $result );
				wp_die();
			} 
			
			$external_id = get_post_meta($post_id, "external_id", true);
			
			$time_value = get_post_meta($post_id, 'price_last_update', true);
					
			$time_value = $time_value ? date("Y-m-d H:i:s", $time_value) : 'not updated';
			
			$product_url = get_post_meta($post_id, 'product_url', true);
			$seller_url = get_post_meta($post_id, 'seller_url', true);
			
			$content = array();
			
			list($souce, $external_id) = explode('#', $external_id);
			
			$content[] = "Source: <span class='wpeae_value'>" . $souce . "</span>";
			$content[] = "Product url: <a target='_blank' href='" . $product_url . "'>here</a>";
			
			if ($seller_url)
				$content[] = "Seller url: <a target='_blank' href='" . $seller_url . "'>here</a>";
			
			$content[] = "External ID: <span class='wpeae_value'>" . $external_id . "</span>";  
			$content[] = "Last auto-update: <span class='wpeae_value'>" . $time_value . "</span>"; 
			
			$content = apply_filters('wpeae_ajax_product_info', $content, $post_id, $external_id, $souce);
			$result['data'] = array( 'content'=> $content, 'id'=> $post_id);
			
			echo json_encode( $result ); 
			wp_die();   
		}
		
		function order_info(){
			$result = array("state" => "ok", "data" => "");
			
			$post_id = isset($_POST['id']) ? $_POST['id'] : false;
			
			if (!$post_id) {
				$result['state'] = 'error';
				echo json_encode( $result );
				wp_die();
			}
			
			$content = array();
			   
			$order = new WC_Order($post_id); 
			
			$items = $order->get_items();

			$k = 1;
	
			foreach ($items as $item) {
				
				$wpeae_item = new WPEAE_Woocommerce_OrderItem($item);
				
				$product_name = $wpeae_item->getName();
				$product_id = $wpeae_item->getProductID();

				$product_url = get_post_meta($product_id, 'product_url', true);
				$seller_url = get_post_meta($product_id, 'seller_url', true);

				$tmp = '';
				
				if ($product_url)  $tmp =  $k . '). <a title="' . $product_name . '" href="' . $product_url . '" target="_blank" class="link_to_source product_url">Product page</a>';

				if ($seller_url) $tmp .= "<span class='seller_url_block'> | <a href='" . $seller_url . "' target='_blank' class='seller_url'>Seller</a></span>";

				$content[] = $tmp;
				$k++;
			}

			$content = apply_filters('wpeae_get_order_content', $content, $post_id);
			$result['data'] = array( 'content'=> $content, 'id'=> $post_id);
			
			echo json_encode( $result ); 
			wp_die();   
		}
		
		function description_editor() {
			$goods = new WPEAE_Goods(isset($_POST['id']) ? $_POST['id'] : "");
			$goods->load();

			if ($goods->photos == "#needload#") {
				echo '<h3><font style="color:red;">Description not load yet! Click "load more details"</font></h3>';
			} else {
				wp_editor($goods->get_prop("description"), $goods->getId('-'), array('media_buttons' => FALSE));
				echo '<input type="hidden" class="item_id" value="' . $goods->getId() . '"/>';
				echo '<input type="hidden" class="editor_id" value="' . $goods->getId('-') . '"/>';
				echo '<input type="button" class="save_description button" value="Save description"/>';

				_WP_Editors::enqueue_scripts();
								wp_enqueue_script('jquery-ui-dialog');
				print_footer_scripts();
				_WP_Editors::editor_js();
			}

			wp_die();
		}

		function edit_goods() {
			$result = array("state" => "ok", "message" => "");
			try {
				set_error_handler("wpeae_error_handler");

				$goods = new WPEAE_Goods(isset($_POST['id']) ? $_POST['id'] : "");
				$goods->load();

				$field = (isset($_POST['field']) ? $_POST['field'] : false);
				$value = (isset($_POST['value']) ? $_POST['value'] : "");

				//if (get_magic_quotes_gpc()) {
				$value = stripslashes($value);
				//}

				if ($field && property_exists(get_class($goods), $field)) {
					$goods->$field = $value;
					$goods->save_field($field, $value);
				}

				restore_error_handler();
			} catch (Exception $e) {
				$result['state'] = 'error';
				$result['message'] = $e->getMessage();
			}

			echo json_encode($result);

			wp_die();
		}

		function select_image() {
			$result = array("state" => "ok", "message" => "");
			try {
				set_error_handler("wpeae_error_handler");

				$goods = new WPEAE_Goods(isset($_POST['id']) ? $_POST['id'] : "");
				if ($goods->load()) {
					$goods->save_field('user_image', isset($_POST['image']) ? $_POST['image'] : "");
				}

				restore_error_handler();
			} catch (Exception $e) {
				$result['state'] = 'error';
				$result['message'] = $e->getMessage();
			}

			echo json_encode($result);

			wp_die();
		}

		function load_details() {
			$result = array("state" => "ok", "message" => "", "goods" => array(), "images_content" => "");
			try {
				set_error_handler("wpeae_error_handler");

				$goods = new WPEAE_Goods(isset($_POST['id']) ? $_POST['id'] : "");

				$edit_fields = isset($_POST['edit_fields']) ? $_POST['edit_fields'] : "";
				if ($edit_fields) {
					$edit_fields = explode(",", $edit_fields);
				}

				$goods->load();

				$loader = wpeae_get_loader($goods->type);
				if ($loader) {
					$res = $loader->load_detail_proc($goods);

					if ($res['state'] == "ok") {
						$description_content = WPEAE_DashboardPage::put_description_edit($goods, true);
						$goods->description = "#hidden#";
						$result = array("state" => "ok", "goods" => WPEAE_Goods::get_normalized_object($goods, $edit_fields), "images_content" => WPEAE_DashboardPage::put_image_edit($goods, true), "description_content" => $description_content);
					} else {
						$result['state'] = $res['state'];
						$result['message'] = $res['message'];
					}
				}
				restore_error_handler();
			} catch (Exception $e) {
				$result['state'] = 'error';
				$result['message'] = $e->getMessage();
			}
			echo json_encode($result);
			wp_die();
		}

		function import_goods() {
			$result = array("state" => "ok", "message" => "");
			try {
				set_error_handler("wpeae_error_handler");
				$goods = new WPEAE_Goods(isset($_POST['id']) ? $_POST['id'] : "");

				$edit_fields = isset($_POST['edit_fields']) ? $_POST['edit_fields'] : "";
				if ($edit_fields) {
					$edit_fields = explode(",", $edit_fields);
				}
								
			
				if ($goods->load()) {
					if ($goods->need_load_more_detail()) {
						$loader = wpeae_get_loader($goods->type);
						$result = $loader->load_detail_proc($goods);
					}
					$goods->save_field("user_schedule_time", NULL);
					if (!$goods->post_id && class_exists('WPEAE_WooCommerce')) {
						$result = WPEAE_WooCommerce::add_post($goods);
					}

					$description_content = WPEAE_DashboardPage::put_description_edit($goods, true);
					$goods->description = "#hidden#";
					$result["goods"] = WPEAE_Goods::get_normalized_object($goods, $edit_fields);
					$result["images_content"] = WPEAE_DashboardPage::put_image_edit($goods, true);
					$result["description_content"] = $description_content;
				} else {
					$result['state'] = 'error';
					$result['message'] = "Product " . $_POST['id'] . " not find.";
				}
				restore_error_handler();
			} catch (Exception $e) {
				$result['state'] = 'error';
				$result['message'] = $e->getMessage();
                                error_log($e->getTraceAsString());
			}
						
			echo json_encode(apply_filters( 'wpeae_after_ajax_import_goods', $result));

			wp_die();
		}

		function load_and_import_goods() {
			$result = array("state" => "ok", "message" => "");
			try {
				set_error_handler("wpeae_error_handler");
								$search_type = isset($_POST['search_type']) ? $_POST['search_type'] : "id";
								$product_id = isset($_POST['id']) ? $_POST['id'] : "";
								$system_code = isset($_POST['system_code']) ? $_POST['system_code'] : "";
								
								if(!$system_code){
									$tmp_goods = new WPEAE_Goods($product_id);
									$system_code = $tmp_goods->type;
									$product_id = $tmp_goods->external_id;
								}
								
				$link_category_id = isset($_POST['link_category_id']) ? intval($_POST['link_category_id']) : 0;
				$import_status = isset($_POST['import_status']) ? $_POST['import_status'] : "";

				$loader = wpeae_get_loader($system_code);

				if ($loader && class_exists('WPEAE_WooCommerce')) {
									if($search_type !== "id"){
										$res = $loader->load_list_proc(array('wpeae_query' => $product_id, 'link_category_id' => $link_category_id));
									}else{
										$res = $loader->load_list_proc(array('wpeae_productId' => $product_id, 'link_category_id' => $link_category_id));
									}
					


					if (isset($res['error']) && $res['error']) {
						$result['state'] = 'error';
						$result['message'] = $res['error'];
					} else {
						if (count($res["items"]) > 0) {
													foreach($res["items"] as $g){
														$goods = $g;
							$goods->load();

							if ($goods->need_load_more_detail()) {
								$res = $loader->load_detail_proc($goods);
							}

							if ($result['state'] == 'ok') {
								$goods->save_field("user_schedule_time", NULL);

								if (!$goods->post_id) {
																	$result = WPEAE_WooCommerce::add_post($goods, array("import_status" => $import_status));
																	$result['goods'] = $goods;
								} else {
									$result['state'] = 'error';
									$result['message'] = 'Product already loaded';
								}
							}
													}
							
						} else {
							$result['state'] = 'error';
							$result['message'] = 'Product not found';
						}
					}
				}

				restore_error_handler();
			} catch (Exception $e) {
				$result['state'] = 'error';
				$result['message'] = "Error: " . $e->getMessage();
			}

			echo json_encode($result);

			wp_die();
		}
				
				function update_goods() {
					$post_id = isset($_REQUEST['post_id']) ? $_REQUEST['post_id'] : "";
					
					$external_id = get_post_meta($post_id, "external_id", true);
					if ($external_id) {
						$result = wpeae_update_price_proc($post_id, false);
						$result['post_id'] = $post_id;
					}else{
						$result = array("state" => "error", "message" => "Product with post id ".$post_id." not found");    
					}
					
					echo json_encode(apply_filters( 'wpeae_after_ajax_update_goods', $result));
					wp_die();
				}

		function schedule_import_goods() {
			$result = array("state" => "ok", "message" => "");
			try {
				set_error_handler("wpeae_error_handler");

				$time_str = isset($_POST['time']) ? $_POST['time'] : "";
				$time = $time_str ? date("Y-m-d H:i:s", strtotime($time_str)) : "";

				$goods = new WPEAE_Goods(isset($_POST['id']) ? $_POST['id'] : "");
				if ($goods->load() && $time) {
					$result['message'] = $_POST['id'] . " loaded " . $time;
					$result['time'] = date("m/d/Y H:i", strtotime($time));
					$goods->save_field("user_schedule_time", $time);
				} else {
					$result['message'] = $_POST['id'] . " not loaded " . $time;
				}
				restore_error_handler();
			} catch (Exception $e) {
				$result['state'] = 'error';
				$result['message'] = $e->getMessage();
			}

			echo json_encode($result);

			wp_die();
		}
                
		function upload_image() {
			$result = array("state" => "warning", "message" => "file not found");
			try {
				set_error_handler("wpeae_error_handler");

				$goods = new WPEAE_Goods(isset($_POST['upload_product_id']) ? $_POST['upload_product_id'] : "");

				if ($goods->load()) {
					if (!function_exists('wp_handle_upload')) {
						require_once( ABSPATH . 'wp-admin/includes/file.php' );
					}

					if ($_FILES) {
						foreach ($_FILES as $file => $array) {
							if ($_FILES[$file]['error'] !== UPLOAD_ERR_OK) {
								$result["state"] = "error";
								$result["message"] = "upload error : " . $_FILES[$file]['error'];
							}

							$upload_overrides = array('test_form' => false);
							$movefile = wp_handle_upload($array, $upload_overrides);

							if ($movefile && !isset($movefile['error'])) {
								$movefile["url"];
								$goods->user_photos .= ($goods->user_photos ? "," : "") . $movefile["url"];
								$goods->save_field("user_photos", $goods->user_photos);
								$goods->save_field("user_image", $movefile["url"]);
								$result["state"] = "ok";
								$result["message"] = "";
								$result["goods"] = $goods;
								$result["images_content"] = WPEAE_DashboardPage::put_image_edit($goods, true);
								$result["cur_image"] = $goods->get_prop('image');
							} else {
								$result["state"] = "error";
								$result["message"] = "E1: " . $movefile['error'];
							}
						}
					}
				}

				restore_error_handler();
			} catch (Exception $e) {
				$result['state'] = 'error';
				$result['message'] = $e->getMessage();
			}
			echo json_encode($result);
			wp_die();
		}
		
		function price_formula_get() {
			if(!isset($_POST['id'])){
				echo json_encode(array("state" => "error", "message"=>"Uncknown price id"));
				wp_die();
			}
			
			$formula = WPEAE_PriceFormula::load($_POST['id']);
			
			if(!$formula){
				echo json_encode(array("state" => "error", "message"=>"Price formula(".$_POST['id'].") not found"));
				wp_die();
			}
			
			$api_list_arr = array();
			$api_list = wpeae_get_api_list(true);
			foreach($api_list as $api){
				$api_list_arr[] = array("id"=>$api->get_type(), "name"=>$api->get_type());
			}
			
			$categories_tree_arr = array();
			$categories_tree = WPEAE_Utils::get_categories_tree();
			
			foreach($categories_tree as $c){
				$categories_tree_arr[] = array("id"=>$c['term_id'], "name"=>$c['name'], "level"=>$c['level']);
			}
			
			$sign_list_arr = array(array("id"=>"=","name"=>" = "),array("id"=>"+","name"=>" + "),array("id"=>"*","name"=>" * "));
			
			$discount_list_arr = array(array("id"=>"","name"=>"source %"),array("id"=>"0","name"=>"0%"),array("id"=>"5","name"=>"5%"),array("id"=>"10","name"=>"10%"),array("id"=>"15","name"=>"15%"),array("id"=>"20","name"=>"20%"),array("id"=>"25","name"=>"25%"),array("id"=>"30","name"=>"30%"),array("id"=>"35","name"=>"35%"),array("id"=>"40","name"=>"40%"),array("id"=>"45","name"=>"45%"),array("id"=>"50","name"=>"50%"),array("id"=>"55","name"=>"55%"),array("id"=>"60","name"=>"60%"),array("id"=>"65","name"=>"65%"),array("id"=>"70","name"=>"70%"),array("id"=>"75","name"=>"75%"),array("id"=>"80","name"=>"80%"),array("id"=>"85","name"=>"85%"),array("id"=>"90","name"=>"90%"),array("id"=>"95","name"=>"95%"));
			
			echo json_encode(array("state" => "ok","formula"=>$formula, "categories_tree"=>$categories_tree_arr,"api_list"=>$api_list_arr,"sign_list"=>$sign_list_arr,"discount_list"=>$discount_list_arr));
			
			wp_die();
		}

		function price_formula_add() {
			$result = array("state" => "ok");

			$formula_list = WPEAE_PriceFormula::load_formulas_list();

			$formula = new WPEAE_PriceFormula();

			$formula->pos = count($formula_list) + 1;

			if (isset($_POST['type'])) {
				$formula->type = wp_unslash($_POST['type']);
			}
			if (isset($_POST['type_name'])) {
				$formula->type_name = wp_unslash($_POST['type_name']);
			}
			if (isset($_POST['category'])) {
				$formula->category = wp_unslash($_POST['category']);
			}
			if (isset($_POST['category_name'])) {
				$formula->category_name = wp_unslash($_POST['category_name']);
			}
			if (isset($_POST['min_price'])) {
				$formula->min_price = wp_unslash($_POST['min_price']);
			}
			if (isset($_POST['max_price'])) {
				$formula->max_price = wp_unslash($_POST['max_price']);
			}
			if (isset($_POST['sign'])) {
				$formula->sign = wp_unslash($_POST['sign']);
			}
			if (isset($_POST['value'])) {
				$formula->value = wp_unslash($_POST['value']);
			}
			if (isset($_POST['discount1'])) {
				$formula->discount1 = wp_unslash($_POST['discount1']);
			}
			if (isset($_POST['discount2'])) {
				$formula->discount2 = wp_unslash($_POST['discount2']);
			}

			WPEAE_PriceFormula::save($formula);

			$result['formula'] = $formula;
			echo json_encode($result);
			wp_die();
		}
		
		function price_formula_edit() {
			$result = array("state" => "ok");
			
			if(!isset($_POST['id']) && !intval($_POST['id'])){
				echo json_encode(array("state" => "error", "message"=>"Uncknown price id"));
				wp_die();
			}
			
			$formula = WPEAE_PriceFormula::load($_POST['id']);
			
			if(!$formula){
				echo json_encode(array("state" => "error", "message"=>"Price formula(".$_POST['id'].") not found"));
				wp_die();
			}

			if (isset($_POST['pos'])) {
				$formula->pos = wp_unslash($_POST['pos']);
			}
			if (isset($_POST['type'])) {
				$formula->type = wp_unslash($_POST['type']);
			}
			if (isset($_POST['type_name'])) {
				$formula->type_name = wp_unslash($_POST['type_name']);
			}
			if (isset($_POST['category'])) {
				$formula->category = wp_unslash($_POST['category']);
			}
			if (isset($_POST['category_name'])) {
				$formula->category_name = wp_unslash($_POST['category_name']);
			}
			if (isset($_POST['min_price'])) {
				$formula->min_price = wp_unslash($_POST['min_price']);
			}
			if (isset($_POST['max_price'])) {
				$formula->max_price = wp_unslash($_POST['max_price']);
			}
			if (isset($_POST['sign'])) {
				$formula->sign = wp_unslash($_POST['sign']);
			}
			if (isset($_POST['value'])) {
				$formula->value = wp_unslash($_POST['value']);
			}
			if (isset($_POST['discount1'])) {
				$formula->discount1 = wp_unslash($_POST['discount1']);
			}
			if (isset($_POST['discount2'])) {
				$formula->discount2 = wp_unslash($_POST['discount2']);
			}
			
			$formula_list = WPEAE_PriceFormula::load_formulas_list();
			foreach($formula_list as $f){
				if($formula->id != $f->id && (int)$f->pos>=(int)$formula->pos){
					$f->pos+=1;
					WPEAE_PriceFormula::save($f);
				}
			}

			WPEAE_PriceFormula::save($formula);
			
			WPEAE_PriceFormula::recalc_pos();

			$result['formula'] = $formula;
			echo json_encode($result);
			wp_die();
		}

		function price_formula_del() {
			$result = array("state" => "ok");
			if (isset($_POST['id'])) {
				WPEAE_PriceFormula::delete(intval($_POST['id']));
				WPEAE_PriceFormula::recalc_pos();
			}

			echo json_encode($result);
			wp_die();
		}
				
				
				function proxy_test(){
					echo "send request to http://gmetrixteam.com/addons/proxy_test.php...<br/>";
					$start = microtime(true);
									$cr = rand();
					$test_res = wpeae_remote_get("http://gmetrixteam.com/addons/proxy_test.php?cr=".$cr, array('proxy'=>wpeae_proxy_get()));
				echo "cr: $cr<br/>";
					if (is_wp_error($test_res)) {
						echo "error: [".$test_res->get_error_code()."] ".$test_res->get_error_message()."<br>";
					} else {
			echo $test_res['body']."<br/>";
						echo "ok<br>";
					}
					echo "request time: ".round(microtime(true) - $start, 3)."s<br/>";
					wp_die();
				}

	}

	endif;
new WPEAE_Ajax();