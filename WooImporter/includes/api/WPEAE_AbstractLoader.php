<?php

/**
 * Description of WPEAE_AbstractLoader
 *
 * @author Geometrix
 */
if (!class_exists('WPEAE_AbstractLoader')):

	abstract class WPEAE_AbstractLoader {

		public $account;
		public $api;

		public function __construct($api) {
			$this->api = $api;
			$this->account = wpeae_get_account($api->get_type());
		}

		public function prepare_filter($filter) {
			return $filter;
		}

		public function load_list_proc($filter, $page = 1) {
			$result = $this->load_list($filter, $page);

			foreach ($result["items"] as $key => $item) {
				// update user price by formula
				$formulas = WPEAE_PriceFormula::get_goods_formula($item);
				if ($formulas) {
					$item->user_price = WPEAE_PriceFormula::apply_formula($item->user_price, $formulas[0]);
					$item->save_field("user_price", sprintf("%01.2f", $item->user_price));

					$item = WPEAE_PriceFormula::calc_regular_price($item, $formulas[0]);
					$item->save('API');
					$item->save_field("user_regular_price", sprintf("%01.2f", $item->user_regular_price));
				}
			}

			// apply some filters for goods list
			$result["items"] = apply_filters('wpeae_load_list_item_proc', $result["items"], $filter);

			return $result;
		}

		public function load_detail_proc(&$goods, $params = array()) {
			$result = $this->load_detail($goods, $params);
			if ($result['state'] === "ok") {
				$result["goods"] = apply_filters('wpeae_get_detail_proc', $goods, $params);
				$goods = $result["goods"];
			}
			return $result;
		}

		public function get_detail_proc($productId, $params = array()) {

			$result = $this->get_detail($productId, $params);

			if ($result['state'] === "ok") {
				$goods = $result["goods"];

				// get category id
				if (isset($params['wc_product_id']) && $params['wc_product_id']) {
					$cats = wp_get_object_terms($params['wc_product_id'], 'product_cat');
					if (!is_wp_error($cats) && $cats) {
                                            $cats_ids = array();
                                            foreach($cats as $c){
                                                $cats_ids[] = $c->term_id;
                                            }
					    //$goods->link_category_id = $cats[0]->term_id;
                                            $goods->link_category_id = $cats_ids;
					}
				}

				// update user price by formula
				$formulas = WPEAE_PriceFormula::get_goods_formula($goods);
				if ($formulas) {
					$goods->user_price = WPEAE_PriceFormula::apply_formula($goods->user_price, $formulas[0]);
					$goods = WPEAE_PriceFormula::calc_regular_price($goods, $formulas[0]);
				}

				$result["goods"] = apply_filters('wpeae_get_detail_proc', $result["goods"], $params);
			}
			return $result;
		}

		abstract public function load_list($filter, $page = 1);

		abstract public function load_detail($goods, $params = array());

		abstract public function get_detail($productId, $params = array());

		abstract public function check_availability($goods);

		public function has_account() {
			return (isset($this->account) && $this->account->is_load());
		}

	}

	

	

	
endif;