<?php
/**
 * Add extra profile fields for users in admin.
 *
 * @author    Actuality Extensions
 * @package   WoocommercePointOfSale/Classes/profile
 * @category    Class
 * @since     0.1
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('WC_Pos_Session_Reports')) :

    /**
     * WC_Pos_Session_Reports
     */
    class WC_Pos_Session_Reports
    {

        protected static $_instance = null;
        public $session_data = array();

        public static function instance()
        {
            if (is_null(self::$_instance)) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        public function __clone()
        {
            _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'woocommerce'), '1.9');
        }

        public function __wakeup()
        {
            _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'woocommerce'), '1.9');
        }

        public function __construct($register_data = '', $cashier_id = 0)
        {
            if ($register_data) {
                $this->session_data['register_id'] = $register_data->ID;
                $this->session_data['register_name'] = $register_data->name;
                $this->session_data['outlet_id'] = $register_data->outlet;
                $this->session_data['opened'] = $register_data->opened;
                $this->session_data['closed'] = $register_data->closed;
                $this->session_data['cashier_id'] = $cashier_id;
                $detail = json_decode($register_data->detail);
                $this->session_data['report_data']['opening_cash_amount'] = $detail->opening_cash_amount;
                $this->session_data['report_data']['cash_management_actions'] = $detail->cash_management_actions;
                $this->session_data['report_data']['actual_cash'] = $detail->actual_cash;

                $this->session_data['report_data'] = json_encode($this->session_data['report_data']);

                $this->session_data['total_sales'] = $this->get_session_total_sales();
            }
        }

        public function init_form_fields($country = '')
        {

        }

        public function display()
        {
            $this->init_form_fields();
            self::display_outlet_table();
        }

        public function display_outlet_table()
        {
            ?>
            <div class="col-wrap">
                <form id="<?php echo WC_POS()->id_session_reports ?>" action="" method="post">
                    <?php
                    $sessions_table = WC_POS()->sessions_table();
                    $sessions_table->prepare_items();
                    $sessions_table->display();
                    ?>
                </form>
            </div>
            <?php
        }

        public function get_data($page = 1, $per_page = 50)
        {
            global $wpdb;
            if ($page === 1) {
                $offset = 0;
            } else {
                $offset = ($page - 1) * $per_page;
            }
            $sql = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}wc_point_of_sale_sale_reports ORDER BY `id` DESC LIMIT %d OFFSET %d", $per_page, $offset);
            $db_data = $wpdb->get_results($sql);
            foreach ($db_data as $key => $val) {

                $val->report_data = json_decode($val->report_data);
                $this->session_data[$key] = get_object_vars($val);

                $outlet_name = WC_POS()->outlet()->get_data_names($val->outlet_id);
                $this->session_data[$key]['outlet_name'] = $outlet_name[1];

                $cashier_data = get_userdata($val->cashier_id);
                $this->session_data[$key]['cashier'] = $cashier_data->first_name . ' ' . $cashier_data->last_name;
            }
            return $this->session_data;
        }

        public function save()
        {
            global $wpdb;
            $wpdb->insert("{$wpdb->prefix}wc_point_of_sale_sale_reports", $this->session_data);
        }

        private function get_session_total_sales()
        {
            global $wpdb;

            $sql = "SELECT SUM(im.`meta_value`)
                    FROM {$wpdb->prefix}woocommerce_order_items oi
                    JOIN {$wpdb->prefix}woocommerce_order_itemmeta im ON im.order_item_id = oi.order_item_id
                    AND im.`meta_key` IN ('_line_total','_line_tax')
                    INNER JOIN
                    {$wpdb->prefix}posts p ON p.ID = oi.`order_id`
                    INNER JOIN {$wpdb->prefix}postmeta reg_id
                    ON reg_id.post_id = p.ID AND reg_id.meta_key = 'wc_pos_id_register' AND reg_id.meta_value = {$this->session_data['register_id']}
                    WHERE p.post_type='shop_order' AND p.post_date BETWEEN '{$this->session_data['opened']}' AND '{$this->session_data['closed']}'";
            return $wpdb->get_var($sql);
        }

        public function get_session_by_id($id)
        {
            global $wpdb;
            $sql = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}wc_point_of_sale_sale_reports WHERE `id` = %d", $id);
            $result = $wpdb->get_row($sql);
            $result->report_data = json_decode($result->report_data);
            return $result;
        }

        public static function update_actual_cash($register_id, $register_dates, $sum = 0)
        {
            global $wpdb;
            $tablename = $wpdb->prefix . 'wc_point_of_sale_sale_reports';
            $data = array();
            $sql = $wpdb->prepare("SELECT `id`, `report_data` FROM {$tablename}
                                  WHERE `register_id` = %d AND `opened` = '{$register_dates->opened}' AND `closed` = '{$register_dates->closed}'", $register_id);
            $session_report = $wpdb->get_row($sql);
            $data['report_data'] = json_decode($session_report->report_data);
            $data['report_data']->actual_cash = floatval($sum);
            $data['report_data'] = json_encode($data['report_data']);
            $wpdb->update($tablename, $data, array('id' => $session_report->id));
        }

        public function get_total_items()
        {
            global $wpdb;
            return $wpdb->get_var("SELECT COUNT(`id`) FROM {$wpdb->prefix}wc_point_of_sale_sale_reports");
        }

        public function delete_session_report()
        {
            global $wpdb;

            if (isset($_POST['id'])) {
                $ids = esc_sql(implode(",", $_POST['id']));
            }
            $sql = "DELETE FROM {$wpdb->prefix}wc_point_of_sale_sale_reports WHERE `id` IN ({$ids})";
            $wpdb->query($sql);
        }

        public static function get_total_sales_by_register_id_and_date($register_id, $date_closed)
        {
            global $wpdb;

            $sql = $wpdb->prepare("SELECT `total_sales` FROM {$wpdb->prefix}wc_point_of_sale_sale_reports WHERE `register_id` = %d AND `closed` LIKE %s", $register_id, $date_closed);
            return $wpdb->get_var($sql);
        }
    }

endif;