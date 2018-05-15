<?php

/**
 * Description of WPEAE_Goods
 *
 * @author Geometrix
 */
if (!class_exists('WPEAE_Goods')):

    class WPEAE_Goods {
        /* Tags for goods fields:
          #empty# - fields is empty
          #needload# - fields not load yet (need load more details)
          #notuse# - fields not use by module
         */

        public $availability = true;
        public $type = "";
        public $external_id = "";
        public $variation_id = "-";
        public $detail_url = "";
        public $image = "";
        public $photos = "";
        public $title = "";
        public $subtitle = "";
        public $description = "";
        public $keywords = "";
        public $regular_price = "";
        public $price = "";
        public $curr = "";
        public $category_id = "";
        public $category_name = "";
        public $link_category_id = "";
        public $seller_url = "";
        public $additional_meta = array();
        public $user_image = "";
        public $user_photos = "";
        public $user_title = "";
        public $user_subtitle = "";
        public $user_description = "";
        public $user_keywords = "";
        public $user_regular_price = "";
        public $user_price = "";
        public $user_schedule_time = "";
        public $loaded = false;
        public $post_id = NULL;

        public function __construct($params = "", $loaded = false) {
            $this->loaded = $loaded;

            if ($params && (is_object($params) || is_array($params))) {
                foreach ($params as $field => $value) {
                    if (property_exists(get_class($this), $field)) {
                        if ($this->is_serialized($value)) {
                            $this->$field = unserialize($value);
                        } else {
                            $this->$field = $value;
                        }
                    }
                }
            } else if ($params) {
                list($this->type, $this->external_id, $this->variation_id) = explode("#", $params . "#-");
            }
        }

        public function getId($dlv = "#") {
            return $this->type . $dlv . $this->external_id . ((strlen($this->variation_id) && $this->variation_id !== '-') ? $dlv . $this->variation_id : "");
        }

        public function getAllPhotos() {
            $photos = array($this->image);
            if ($this->photos && $this->photos != "#needload#") {
                $photos = array_merge($photos, explode(",", $this->photos));
            }
            if ($this->user_photos) {
                $photos = array_merge($photos, explode(",", $this->user_photos));
            }
            $photos = array_unique($photos);

            return $photos;
        }

        public function get_prop($prop, $edit = true) {
            $res = "";
            $user_porp = "user_" . $prop;
            if ($edit && property_exists(get_class($this), $user_porp) && $this->$user_porp) {
                $res = $this->$user_porp;
            } else if (property_exists(get_class($this), $prop) && $this->$prop) {
                $res = $this->$prop;
            }
            return is_array($res) ? $res : (trim($res) == "#empty#" ? "" : trim($res));
        }

        public static function get_normalized_object($goods, $edit_fields = array()) {
            $res_goods = new WPEAE_Goods();
            foreach (get_object_vars($goods) as $f => $val) {
                $res_goods->$f = $goods->get_prop($f, in_array($f, $edit_fields));
            }
            return $res_goods;
        }

        public static function get_normalized_value($goods, $field) {
            $result = "";
            if (property_exists(get_class($goods), $field)) {
                $result = $goods->$field;
                $result = WPEAE_Goods::normalized($result);
            }
            return $result;
        }

        public static function normalized($value) {
            $result = $value;
            if ($result && !is_array($result)) {
                $result = str_replace("#empty#", "", $result);
                $result = str_replace("#notuse#", "", $result);
                $result = str_replace("#needload#", "", $result);
            }
            return $result;
        }

        public static function get_normalize_price($price) {
            return (isset($price) && $price) ? sprintf("%01.2f", str_replace(array('US $', 'RUB', '$', 'GPB', 'BRL', 'CAD', 'AUD', 'EUR', 'INR', 'UAH', 'JPY', 'MXN', 'IDR', 'TRY', 'SEK', '.00'), '', $price)) : "0.00";
        }

        public function need_load_more_detail() {
            foreach (get_object_vars($this) as $f => $val) {
                if (!is_array($val) && strval($val) === "#needload#") {
                    return true;
                }
            }
            return false;
        }

        public function save($action = "ALL") {
            /** @var wpdb $wpdb */
            global $wpdb;

            if (!$this->type || !$this->external_id)
                return;

            $apiData = array(
                'type' => $this->type,
                'external_id' => $this->external_id,
                'variation_id' => $this->variation_id,
                'image' => $this->image,
                'detail_url' => $this->detail_url,
                'seller_url' => $this->seller_url,
                'photos' => $this->photos,
                'title' => $this->title,
                'subtitle' => $this->subtitle,
                'description' => $this->description,
                'keywords' => $this->keywords,
                'regular_price' => $this->regular_price,
                'price' => $this->price,
                'curr' => $this->curr,
                'category_id' => $this->category_id,
                'category_name' => $this->category_name,
                'link_category_id' => is_array($this->link_category_id)?array_shift($this->link_category_id):$this->link_category_id,
                'additional_meta' => serialize($this->additional_meta));

            $userData = array(
                'type' => $this->type,
                'external_id' => $this->external_id,
                'variation_id' => $this->variation_id,
                'user_image' => (strlen(trim($this->user_title)) == 0 ? "#empty#" : trim($this->user_image)),
                'user_photos' => (strlen(trim($this->user_title)) == 0 ? "#empty#" : trim($this->user_photos)),
                'user_title' => (strlen(trim($this->user_title)) == 0 ? "#empty#" : trim($this->user_title)),
                'user_subtitle' => (strlen(trim($this->user_subtitle)) == 0 ? "#empty#" : trim($this->user_subtitle)),
                'user_description' => (strlen(trim($this->user_description)) == 0 ? "#empty#" : trim($this->user_description)),
                'user_keywords' => (strlen(trim($this->user_keywords)) == 0 ? "#empty#" : trim($this->user_keywords)),
                'user_schedule_time' => trim($this->user_schedule_time),
                'user_regular_price' => (strlen(trim($this->user_regular_price)) == 0 ? "#empty#" : trim($this->user_regular_price)),
                'user_regular_price' => (strlen(trim($this->user_regular_price)) == 0 ? "#empty#" : trim($this->user_regular_price)),
            );
            if ($action == "ALL") {
                $data = array_merge($apiData, $userData);
            } else if ($action == "API") {
                $data = $apiData;
            } else if ($action == "USER") {
                $data = $userData;
            }

            if ($this->loaded) {
                $wpdb->update($wpdb->prefix . WPEAE_TABLE_GOODS, $data, array('type' => $this->type, 'external_id' => $this->external_id, 'variation_id' => $this->variation_id));
            } else {
                $wpdb->replace($wpdb->prefix . WPEAE_TABLE_GOODS, $data);
            }

            $this->loaded = true;
        }

        public function save_field($field, $value = "") {
            /** @var wpdb $wpdb */
            global $wpdb;

            if (!$this->type || !$this->external_id)
                return;

            $this->$field = $value;
            $data = array('type' => $this->type, 'external_id' => $this->external_id, 'variation_id' => $this->variation_id, $field => (strlen(trim($value)) == 0 ? "#" : trim($value)));
            if ($this->loaded) {
                $wpdb->update($wpdb->prefix . WPEAE_TABLE_GOODS, $data, array('type' => $this->type, 'external_id' => $this->external_id, 'variation_id' => $this->variation_id));
            } else {
                $wpdb->insert($wpdb->prefix . WPEAE_TABLE_GOODS, $data);
            }
        }

        public function load() {
            /** @var wpdb $wpdb */
            global $wpdb;
            $this->loaded = false;
            if ($this->type && $this->external_id) {
                $results = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . WPEAE_TABLE_GOODS . " WHERE type='$this->type' and external_id='$this->external_id' and variation_id='$this->variation_id'");
                if ($results) {
                    foreach ($results[0] as $key => $val) {
                        if ($this->is_serialized($val)) {
                            $this->$key = unserialize($val);
                        } else {
                            $this->$key = $val;
                        }
                    }
                    $this->loaded = true;
                }
                $this->post_id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key='external_id' AND meta_value='%s' LIMIT 1", $this->getId()));
            }
            return $this->loaded;
        }

        public function get_product_meta($meta_key) {
            /** @var wpdb $wpdb */
            global $wpdb;
            $post_id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key='external_id' AND meta_value='%s' LIMIT 1", $this->getId()));
            return get_post_meta($post_id, $meta_key, true);
        }

        private function is_serialized($str) {
            return (@unserialize($str) !== false || $str == 'b:0;');
            //return ($str == serialize(false) || unserialize($str) !== false);
        }

        public static function load_goods_list($page, $per_page, $filter = "") {
            global $wpdb;
            $result = array();

            $where = " WHERE 1 ";
            $where .= $filter;

            $db_res = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . WPEAE_TABLE_GOODS . $where . " LIMIT " . (($page - 1) * $per_page) . ", " . $per_page);
            if ($db_res) {
                foreach ($db_res as $row) {
                    $result[] = new WPEAE_Goods($row, true);
                }
            }
            return array("total" => count($result), "page" => $page, "per_page" => $per_page, "items" => $result);
        }

        public static function clear_list($delete_schedule_post = false) {
            /** @var wpdb $wpdb */
            global $wpdb;
            if ($delete_schedule_post) {
                $wpdb->query("TRUNCATE " . $wpdb->prefix . WPEAE_TABLE_GOODS);
            } else {
                $wpdb->query("DELETE FROM " . $wpdb->prefix . WPEAE_TABLE_GOODS . " WHERE NULLIF(NULLIF(user_schedule_time, '0000-00-00 00:00:00'), '') IS NULL");
            }
        }

    }

    endif;

if (!function_exists('wpeae_sort_by_price')) {

    function wpeae_sort_by_price($g1, $g2) {
        $a = FloatVal($g1->price);
        $b = FloatVal($g2->price);
        if (abs($a - $b) < 0.001) {
            return 0;
        }
        return ($a < $b) ? -1 : 1;
    }

}

if (!function_exists('wpeae_sort_by_user_price')) {

    function wpeae_sort_by_user_price($g1, $g2) {
        $a = FloatVal($g1->user_price);
        $b = FloatVal($g2->user_price);
        if (abs($a - $b) < 0.001) {
            return 0;
        }
        return ($a < $b) ? -1 : 1;
    }

}

if (!function_exists('wpeae_sort_by_ship')) {

    function wpeae_sort_by_ship($g1, $g2) {
        $a = (isset($g1->additional_meta['ship']) && WPEAE_Goods::normalized($g1->additional_meta['ship'])) ? FloatVal($g1->additional_meta['ship']) : 0.00;
        $b = (isset($g2->additional_meta['ship']) && WPEAE_Goods::normalized($g2->additional_meta['ship'])) ? FloatVal($g2->additional_meta['ship']) : 0.00;
        if (abs($a - $b) < 0.001) {
            return 0;
        }
        return ($a < $b) ? -1 : 1;
    }

}

if (!function_exists('wpeae_sort_by_ship_to_locations')) {

    function wpeae_sort_by_ship_to_locations($g1, $g2) {
        $a = (isset($g1->additional_meta['ship_to_locations']) && WPEAE_Goods::normalized($g1->additional_meta['ship_to_locations'])) ? $g1->additional_meta['ship_to_locations'] : "0.00";
        $b = (isset($g2->additional_meta['ship_to_locations']) && WPEAE_Goods::normalized($g2->additional_meta['ship_to_locations'])) ? $g2->additional_meta['ship_to_locations'] : "";
        return strcasecmp($a, $b);
    }

}

if (!function_exists('wpeae_sort_by_curr')) {

    function wpeae_sort_by_curr($g1, $g2) {
        return strcasecmp($g1->curr, $g2->curr);
    }

}
