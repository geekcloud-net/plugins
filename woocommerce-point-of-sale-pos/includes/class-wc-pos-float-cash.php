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

if (!class_exists('WC_Pos_Float_Cash')) :

    /**
     * WC_Pos_Float_Cash
     */
    class WC_Pos_Float_Cash
    {
        public $register;
        public $outlet_name;
        public $cash_balance;
        public $cash_data = array();

        public function __construct($register_id)
        {
            self::register_styles();
            self::register_scripts();

            $this->register = $this->get_register_by_id(intval($register_id));
            $this->register->detail = json_decode($this->register->detail);
            $this->register->settings = json_decode($this->register->settings);
            $this->cash_balance = $this->get_cash_balance();
            $this->outlet_name = WC_POS()->outlet()->get_data_names($this->register->outlet);
        }

        private function register_scripts()
        {
            wp_enqueue_script('float-cash-management', WC_POS()->plugin_url() . '/assets/js/register/float-cash-management.js', array('jquery'));
            wp_enqueue_script('wc-pos-modal-classie', WC_POS()->plugin_url() . '/assets/js/register/modal/classie.js', array('jquery'));
            wp_enqueue_script('wc-pos-modal-modalEffects', WC_POS()->plugin_url() . '/assets/js/register/modal/modalEffects.js', array('jquery'));
            wp_enqueue_script('wc-pos-modal-cssParser', WC_POS()->plugin_url() . '/assets/js/register/modal/cssParser.js', array('jquery'));

        }

        private function register_styles()
        {
            wp_enqueue_style('modal-component', WC_POS()->plugin_url() . '/assets/css/register/modal-component.css');
        }

        private function get_register_by_id($register_id)
        {
            global $wpdb;
            return $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wc_poin_of_sale_registers WHERE `ID` = {$register_id}");
        }

        private function get_cash_balance()
        {
            global $wpdb;
            if ($cash_statuses = get_option('wc_pos_cash_management_order_status')) {
                $statuses = "'" . implode("','", $cash_statuses) . "'";
            }
            $balance = 0;
            if (isset ($this->register->detail->opening_cash_amount) && $this->register->detail->opening_cash_amount && $this->register->detail->opening_cash_amount->status) {
                $balance = $balance + $this->register->detail->opening_cash_amount->amount;
                $this->cash_data[] = array(
                    'title' => __('Opening cash amount', 'wc_point_of_sale'),
                    'type' => 'opening_cash_amount',
                    'amount' => $this->register->detail->opening_cash_amount->amount,
                    'note' => $this->register->detail->opening_cash_amount->note,
                    'user' => $this->register->detail->opening_cash_amount->user,
                    'time' => $this->register->detail->opening_cash_amount->time
                );
            }
            $sql = "SELECT ID, post_status FROM {$wpdb->posts}
                INNER JOIN {$wpdb->postmeta} reg_id
                ON ( reg_id.post_id = {$wpdb->posts}.ID AND reg_id.meta_key = 'wc_pos_id_register' AND reg_id.meta_value = {$this->register->ID} )
              WHERE {$wpdb->posts}.post_type='shop_order' AND {$wpdb->posts}.post_date > '{$this->register->opened}' AND post_status IN ({$statuses}) 
            ";

            $results = $wpdb->get_results($sql);

            foreach ($results as $result) {
                $order = new WC_Order($result->ID);
                if ($order->get_payment_method()  == 'cod') {
                    $balance = $balance + $order->get_total();
                    $this->cash_data[] = array(
                        'title' => __('Order #', 'wc_point_of_sale') . $order->get_order_number(),
                        'type' => 'wc_order',
                        'amount' => $order->get_total(),
                        'note' => '',
                        'user' => $order->get_user_id(),
                        'time' => $order->get_date_created()
                    );
                };
            }

            if (isset($this->register->detail->cash_management_actions) && $this->register->detail->cash_management_actions) {
                foreach ($this->register->detail->cash_management_actions as $cash_action) {
                    $cash_action = (array)$cash_action;
                    switch ($cash_action['type']) {
                        case 'add-cash':
                            $balance = $balance + $cash_action['amount'];
                            $cash_action['title'] = __('Cash in', 'wc_point_of_sale');
                            break;
                        case 'remove-cash':
                            $balance = $balance - $cash_action['amount'];
                            $cash_action['title'] = __('Cash out', 'wc_point_of_sale');
                            break;
                    }
                    $this->cash_data[] = $cash_action;
                }
            }
            return $balance;
        }

        public function render_page()
        {
            $this->sort_cash_data();
            include_once('views/html-float-cash-management.php');
            include_once('views/modal/html-modal-cash-management.php');
        }

        private function sort_cash_data()
        {
            if ($this->cash_data) {
                foreach ($this->cash_data as $key => $val) {
                    $sort_arr[$key] = $val['time'];
                }
                array_multisort($sort_arr, SORT_DESC, $this->cash_data);
            }
        }

        //TODO: Need optimization this in one query
        public static function set_actual_cash($register_id, $sum)
        {
            global $wpdb;
            $sql = $wpdb->prepare("SELECT `detail` FROM {$wpdb->prefix}wc_poin_of_sale_registers WHERE `ID` = %d", $register_id);
            $register_details['detail'] = json_decode($wpdb->get_var($sql));
            $register_details['detail']->actual_cash = floatval($sum);
            $register_details['detail'] = json_encode($register_details['detail']);
            $wpdb->update("{$wpdb->prefix}wc_poin_of_sale_registers", $register_details, array('ID' => $register_id));

            $register_dates = $wpdb->get_row($wpdb->prepare("SELECT `opened`, `closed` FROM {$wpdb->prefix}wc_poin_of_sale_registers WHERE `ID` = %d", $register_id));
            WC_Pos_Session_Reports::update_actual_cash($register_id, $register_dates, $sum);
        }
    }

endif;