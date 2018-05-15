<?php

/**
 * Description of WPEAE_PriceFormula
 *
 * @author Geometrix
 */
if (!class_exists('WPEAE_PriceFormula')) {

    class WPEAE_PriceFormula {

        public $id = 0;
        // formulа
        public $type = "";
        public $pos = 0;
        public $category = 0;
        public $category_name = "";
        public $min_price = 0;
        public $max_price = 0;
        public $sign = "=";
        public $value = 1;
        public $discount1 = "";
        public $discount2 = "";

        public function __construct($id = 0) {
            $this->id = $id;
            if ($this->id) {
                $formula = WPEAE_PriceFormula::load($id);
                if ($formula) {
                    foreach ($formula as $field => $val) {
                        $this->$field = $val;
                    }
                }
            }
        }

        public static function save(/* @var $formula WPEAE_PriceFormula */ &$formula) {
            /** @var wpdb $wpdb */
            global $wpdb;

            $f_data = array("type" => $formula->type,
                "category" => $formula->category,
                "category_name" => $formula->category_name,
                "min_price" => floatval($formula->min_price),
                "max_price" => floatval($formula->max_price),
                "sign" => $formula->sign,
                "value" => $formula->value,
                "discount1" => $formula->discount1,
                "discount2" => $formula->discount2,);

            if ($formula->id) {
                $wpdb->update($wpdb->prefix . WPEAE_TABLE_PRICE_FORMULA, array("pos" => intval($formula->pos), "formula" => serialize($f_data)), array('id' => intval($formula->id)));
            } else {
                $wpdb->insert($wpdb->prefix . WPEAE_TABLE_PRICE_FORMULA, array("pos" => intval($formula->pos), "formula" => serialize($f_data)));
                $formula->id = $wpdb->insert_id;
            }

            return $formula;
        }

        public static function load($id) {
            /** @var wpdb $wpdb */
            global $wpdb;

            $formula = false;

            $results = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . WPEAE_TABLE_PRICE_FORMULA . " WHERE id='$id'");

            if ($results) {
                $formula = new WPEAE_PriceFormula();
                $formula->id = intval($results[0]->id);
                $formula->pos = intval($results[0]->pos);

                $f_data = unserialize($results[0]->formula);
                foreach ($f_data as $field => $value) {
                    if (property_exists(get_class($formula), $field)) {
                        $formula->$field = esc_attr($value);
                    }
                }
            }
            return $formula;
        }

        public static function load_formulas_list() {
            /** @var wpdb $wpdb */
            global $wpdb;

            $formula = array();

            $results = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . WPEAE_TABLE_PRICE_FORMULA . " ORDER BY pos");

            if ($results) {
                foreach ($results as $row) {
                    $f = new WPEAE_PriceFormula();
                    $f->id = intval($row->id);
                    $f->pos = intval($row->pos);

                    $f_data = unserialize($row->formula);
                    foreach ($f_data as $field => $value) {
                        if (property_exists(get_class($f), $field)) {
                            $f->$field = esc_attr($value);
                        }
                    }
                    $formula[] = $f;
                }
            }
            return $formula;
        }

        public static function delete($id) {
            /** @var wpdb $wpdb */
            global $wpdb;

            $wpdb->delete($wpdb->prefix . WPEAE_TABLE_PRICE_FORMULA, array('id' => $id));
        }

        public static function recalc_pos() {
            /** @var wpdb $wpdb */
            global $wpdb;
            $wpdb->query('UPDATE wp_wpeae_price_formula dest, (SELECT @r:=@r+1 as new_pos, z.id from(select id from wp_wpeae_price_formula order by pos) z, (select @r:=0)y) src SET dest.pos = src.new_pos where dest.id=src.id;');
        }

        public static function get_goods_formula(/* @var $dashboard WPEAE_Goods */ $goods, $single = true) {
            $res_formula_list = array();
            $formula_list = WPEAE_PriceFormula::load_formulas_list();

            foreach ($formula_list as $formula) {
                $check = true;

                if (isset($formula->min_price) && $formula->min_price && (float) $formula->min_price >= (float) $goods->user_price) {
                    $check = false;
                }

                if (isset($formula->max_price) && $formula->max_price && (float) $formula->max_price <= (float) $goods->user_price) {
                    $check = false;
                }

                if (isset($formula->type) && $formula->type && $formula->type != $goods->type) {
                    $check = false;
                }

                if (isset($formula->category) && $formula->category && ((is_array($goods->link_category_id) && !in_array((int) $formula->category, $goods->link_category_id)) || (!is_array($goods->link_category_id) && (int) $formula->category != (int) $goods->link_category_id))) {
                    $check = false;
                }

                if ($check) {
                    $res_formula_list[] = $formula;

                    if ($single) {
                        break;
                    }
                }
            }

            return $res_formula_list;
        }

        public static function apply_formula($price, $formula) {
            $result = $price;
            if ($formula->sign == "=") {
                $result = $formula->value;
            } else if ($formula->sign == "*") {
                $result *= $formula->value;
            } else if ($formula->sign == "+") {
                $result += $formula->value;
            }
            return round($result, 2);
        }

        public static function calc_regular_price(&$goods, $formula) {
            $discount = 0;

            $discount_perc = $goods->get_product_meta('discount_perc');
            if ($discount_perc || strlen(trim((string) $discount_perc)) > 0) {
                $discount = IntVal($discount_perc);
            } else {
                if (isset($goods->additional_meta['original_discount']) && strlen(trim((string) $goods->additional_meta['original_discount'])) > 0) {
                    $discount = IntVal($goods->additional_meta['original_discount']);
                }
                if (strlen(trim((string) $formula->discount1)) > 0 && strlen(trim((string) $formula->discount2)) > 0) {
                    if (IntVal($formula->discount1) > IntVal($formula->discount2)) {
                        $discount = rand(IntVal($formula->discount2), IntVal($formula->discount1));
                    } else {
                        $discount = rand(IntVal($formula->discount1), IntVal($formula->discount2));
                    }
                } else if (strlen(trim((string) $formula->discount1)) > 0 || strlen(trim((string) $formula->discount2)) > 0) {
                    $discount = strlen(trim((string) $formula->discount1)) > 0 ? IntVal($formula->discount1) : IntVal($formula->discount2);
                }
            }

            $goods->additional_meta['discount_perc'] = $discount;
            $goods->user_regular_price = round(($goods->user_price * 100) / (100 - $discount), 2);

            return $goods;
        }

    }

}