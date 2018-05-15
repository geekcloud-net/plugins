<?php

/* * class
 * Description of WPEAE_DashboardPage
 *
 * @author Geometrix
 */
if (!class_exists('WP_List_Table')) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
if (!class_exists('WPEAE_DashboardPage')):

	class WPEAE_DashboardPage extends WP_List_Table {

		public $type = "";
		public $api = false;
		public $loader = false;
		public $filter = array();
		public $sites = array();
		public $show_dashboard = true;
		public $link_categories = array();

		function __construct($type) {
			parent::__construct();
			$this->api = wpeae_get_api($type);

			if ($this->api && $this->api->is_instaled()) {
				$this->type = $this->api->get_type();
				$this->loader = wpeae_get_loader($this->type);

				wp_enqueue_script('jquery');

				wp_enqueue_script('jquery-ui-datepicker');
				//wp_enqueue_style('jqueryui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css', false, null);

				wp_enqueue_script('jquery-form', array('jquery'), false, true);
			}
		}

		function render() {
			// Initialize api module (hooks, filters and other)
			$this->api->init();

			if (is_plugin_active('woocommerce/woocommerce.php')) {
				do_action('wpeae_befor_dashboard_render', $this->api);

				wpeae_api_enqueue_style($this->api);

				$_SERVER['REQUEST_URI'] = remove_query_arg(array('reset'), $_SERVER['REQUEST_URI']);

				$this->filter = array();
				if (is_array($_GET) && $_GET) {
					$this->filter = array_merge($this->filter, $_GET);
					unset($this->filter['page']);
				}
                                
                                foreach($this->filter as $key=>$val){
                                    $this->filter[$key] = wp_unslash($val);
                                }

				$this->filter = $this->loader->prepare_filter($this->filter);

				$this->link_categories = WPEAE_Utils::get_categories_tree();

				do_action('wpeae_dashboard_render', $this);

				do_action('wpeae_after_dashboard_render', $this);
			}
		}

		function get_columns() {
			$columns = array('cb' => '<input type="checkbox" />', 'image' => '', 'info' => 'Information', 'ship_to_locations' => 'Ship to', 'condition' => 'Condition', 'price' => 'Source Price', 'user_price' => 'Posted Price', 'ship' => 'Shipment Charges', 'curr' => 'Currency');
			return apply_filters('wpeae_get_dashboard_columns', $columns, $this->api);
		}

		function get_sortable_columns() {
			$sortable_columns = array();
			return apply_filters('wpeae_get_dashboard_sortable_columns', $sortable_columns);
		}

		function column_cb($item) {
			return sprintf('<input type="checkbox" class="gi_ckb" name="gi[]" value="%s" ' . ($item->post_id ? 'disabled="disabled"' : '') . '/>', $item->getId('#'));
		}

		function column_default($item, $column_name) {
			$result_data = "";
			switch ($column_name) {
				case 'image':
					$result_data = WPEAE_DashboardPage::put_image_edit($item);
					break;
				case 'info':
					$actions = array();
					$actions['id'] = '<a href="' . $item->detail_url . '" target="_blank" class="link_to_source product_url">Product page</a>' . "<span class='seller_url_block' " . ($item->seller_url ? "" : "style='display:none'") . "> | <a href='" . $item->seller_url . "' target='_blank' class='seller_url'>Seller page</a></span>";
					$actions['load_more_detail'] = ($item->need_load_more_detail()) ? '<a href="#moredetails" class="moredetails">Load more details</a>' : '<i>Details loaded</i>';
					$actions['import'] = $item->post_id ? '<i>Posted</i>' : '<a href="#import_" class="post_import">Post to Woocommerce</a>';
					if (!$item->post_id) {
						$actions['schedule_import'] = $item->user_schedule_time ? ("<i>Will be post on " . date("m/d/Y H:i", strtotime($item->user_schedule_time))) . "</i>" : '<input type="text" class="schedule_post_date" style="visibility:hidden;width:0px;padding:0;margin:0;"/><a href="#scheduleimport" class="schedule_post_import">Schedule Post</a>';
					}

					$cat_name = "";
					foreach ($this->link_categories as $c) {
						if ($c['term_id'] == $item->link_category_id) {
							$cat_name = $c['name'];
							break;
						}
					}
                                        
                                        if(!$cat_name && $item->category_name){
                                            $cat_name = $item->category_name." (Source category)";
                                        }

					$result_data = WPEAE_DashboardPage::put_field($item, "title", true, "edit", "Title", "") .
							WPEAE_DashboardPage::put_field($item, 'subtitle', true, "edit", "Subtitle", "subtitle-block") .
							WPEAE_DashboardPage::put_field($item, 'keywords', true, "edit", "Keywords", "subtitle-block") .
							WPEAE_DashboardPage::put_description_edit($item) .
							(($cat_name) ? "<div>Link to category: $cat_name</div>" : "") .
							$this->row_actions($actions);
					break;
				case 'condition':
					$result_data = isset($item->additional_meta['condition']) ? WPEAE_Goods::normalized($item->additional_meta['condition']) : "";
					break;
				case 'ship_to_locations':
					$result_data = isset($item->additional_meta['ship_to_locations']) ? WPEAE_Goods::normalized($item->additional_meta['ship_to_locations']) : "";
					break;
				case 'ship':
					$result_data = (isset($item->additional_meta['ship']) && $item->additional_meta['ship']) ? WPEAE_Goods::get_normalize_price($item->additional_meta['ship']) : "";
					break;
				case 'ship':
					$result_data = WPEAE_DashboardPage::put_field($item, $column_name, true);
					break;
				default:
					$result_data = WPEAE_DashboardPage::put_field($item, $column_name, false);
					break;
			}

			return apply_filters('wpeae_dashboard_column_default', $result_data, $item, $column_name);
		}

		function no_items() {
			_e('Products no found.');
		}

		function get_bulk_actions() {
			$actions = array(
				'import' => 'Post to Woocommerce',
			);
			return $actions;
		}

		public function single_row($item) {
			echo '<tr id="' . $item->getId() . '">';
			$this->single_row_columns($item);
			echo '</tr>';
		}

		private function process_bulk_action() {
			$result_cnt = 0;
			set_error_handler("wpeae_error_handler");
			if (((isset($_GET['action']) && $_GET['action'] == "import") || (isset($_GET['action2']) && $_GET['action2'] == "import")) && isset($_GET['gi']) && is_array($_GET['gi'])) {
				foreach ($_GET['gi'] as $gi) {
					$goods = new WPEAE_Goods($gi);
					if ($goods->load() && !$goods->post_id) {
						if ($goods->need_load_more_detail()) {
							$result = $this->loader->load_detail_proc($goods);
						}
						if (class_exists('WPEAE_WooCommerce')) {
							$res = WPEAE_WooCommerce::add_post($goods);
							if ($res["state"] != "error") {
								$result_cnt++;
							}

							if ($res["message"]) {
								add_settings_error('wpeae_goods_posted', esc_attr('settings_updated'), $res["message"], $res["state"] != "ok" ? 'error' : 'updated');
							}
						}
					}
				}
			}
			restore_error_handler();
			return $result_cnt;
		}

		function prepare_items() {

			if ($this->loader) {

				if (!$this->loader->has_account()) {
					add_settings_error('wpeae_dashboard_error', esc_attr('settings_updated'), 'Account not found. You need configure account on setting page', 'error');
					$this->show_dashboard = false;
				} else if (!is_plugin_active('woocommerce/woocommerce.php')) {
					add_settings_error('wpeae_dashboard_error', esc_attr('settings_updated'), 'Please install the Woocommerce plugin first.', 'error');
					$this->show_dashboard = false;
				} else {
					$this->_column_headers = array($this->get_columns(), array(), $this->get_sortable_columns());
					$current_page = $this->get_pagenum();

					$result_cnt = $this->process_bulk_action();
					if ($result_cnt) {
						add_settings_error('wpeae_goods_posted', esc_attr('settings_updated'), "$result_cnt products have been loaded to WooCommerce", 'updated');
					}
					settings_errors('wpeae_goods_posted');

					if (isset($this->filter['reset']) && $this->filter['reset']) {
						WPEAE_Goods::clear_list();
					}

					$data = $this->loader->load_list_proc($this->filter, $current_page);

					if ($data["error"]) {
						add_settings_error('wpeae_goods_list', esc_attr('settings_updated'), $data["error"], 'error');
					}

					$this->set_pagination_args(array('total_items' => IntVal($data['total']), 'per_page' => IntVal($data['per_page'])));
					$this->items = $data["items"];

					// process local sort by columns
					if (isset($_GET['orderby']) && function_exists("wpeae_sort_by_" . $_GET['orderby'])) {
						uasort($this->items, "wpeae_sort_by_" . $_GET['orderby']);
						if (isset($_GET['order']) && $_GET['order'] == "desc") {
							$this->items = array_reverse($this->items);
						}
					}
				}
			}
		}

		static public function put_field($item, $field, $edit, $edit_text = "edit", $lable_text = "", $block_class = "") {
			$value = $item->get_prop($field, $edit);
						
			$loaded = $value != "#needload#";

			$out = '';
			if ($value != "#notuse#") {
				$out .= '<div class="block_field ' . $block_class . ($edit ? ' edit' : '') . '">';
				$out .= '<input type="hidden" class="field_code" value="' . $field . '"/>';
				if ($lable_text) {
					$out .= '<label class="field_label">' . $lable_text . ': </label>';
				}
				$out .= '<span class="field_text">' . ($loaded ? $value : '<font style="color:red;">Need to load more details</font>') . '</span>';
				if ($edit) {
					$out .= '<input type="text" class="field_edit" value="" style="width:100%;display:none"/>';
					$out .= '<input type="button" class="save_btn button" value="Save" style="display:none"/> ';
					$out .= '<input type="button" class="cancel_btn button" value="Cancel" style="display:none"/>';
					$out .= ' <a href="#edit" class="edit_btn" ' . ($loaded ? '' : 'style="display:none;"') . '>[' . $edit_text . ']</a>';
				}
				$out .= '</div>';
			}

			return $out;
		}

		static public function put_image_edit($item, $content_only = false) {
			$out = "";
			if (!$content_only) {
				$out .= sprintf('<a href="#TB_inline?width=320&height=450&inlineId=select-image-dlg-%1$s" class="thickbox select_image"><img src="%2$s"/></a>', $item->getId('-'), $item->get_prop('image'));
				$out .= '<a href="#TB_inline?width=320&height=150&inlineId=upload_image_dlg" class="thickbox upload_image">[upload image]</a>';
				$out .= '<div id="select-image-dlg-' . $item->getId('-') . '" style="display:none;">';
			}
			if ($item->photos == "#needload#") {
				$out .= '<h3><font style="color:red;">Photos not load yet! Click "load more details"</font></h3>';
			}
			$out .= '<h3>Click on an image to select it</h3>';
			$out .= '<input type="hidden" class="item_id" value="' . $item->getId() . '"/>';
			$cur_image = $item->user_image;

			$photos = $item->getAllPhotos();
			foreach ($photos as $photo) {
				$out .= sprintf('<div class="wpeae_select_image"><img class="' . ($cur_image == $photo ? "sel" : "") . '" src="%1$s"/></div>', $photo);
			}

			if (!$content_only) {
				$out .= '</div>';
			}
			return $out;
		}

		static public function put_description_edit($item, $content_only = false) {
			$out = "";
			if (!$content_only) {
				$out .= 'Description: <a href="#TB_inline?width=800&height=600&inlineId=edit_desc_dlg" class="thickbox edit_desc_action">[edit description]</a>';
			}

			if (!$content_only) {
				
			}
			return $out;
		}

	}

	

	
	
endif;
