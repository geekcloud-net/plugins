<?php

/**
 * Description of WPEAE_AbstractConfigurator
 *
 * @author Geometrix
 */
if (!class_exists('WPEAE_AbstractConfigurator')):

    abstract class WPEAE_AbstractConfigurator {

        private $filter_config = array();

        public function __construct() {
            wpeae_add_api($this);
            $this->check_api_configure();
            
            $this->init_module();

            add_action('wpeae_print_api_setting_page', array($this, 'print_api_account_setting_page'), 10, 1);

            add_action('wpeae_print_api_setting_page', array($this, 'print_api_setting_page'), 11, 1);
        }

        public final function init() {
            add_filter('wpeae_get_dashboard_columns', array($this, 'modify_columns'), 10, 2);
            add_filter('wpeae_get_dashboard_sortable_columns', array($this, 'modify_sortable_columns'), 10, 1);
            add_filter('wpeae_dashboard_column_default', array($this, 'modify_column_data'), 10, 3);

            add_action('wpeae_befor_dashboard_render', array($this, 'print_page_header'), 10, 1);

            if (!$this->is_instaled()) {
                add_action('wpeae_dashboard_render', array($this, 'print_promo_page'), 10, 1);
            } else {
                add_action('wpeae_dashboard_render', array($this, 'print_page'), 10, 1);
            }

            add_action('wpeae_after_dashboard_render', array($this, 'print_page_footer'), 10, 1);

            add_action('wpeae_print_api_setting_page', array($this, 'print_api_setting_page'), 10, 1);

            $this->init_filters();

            $this->configure_filters();

            do_action('wpeae_init_custom_filter', $this);
        }

        // should return config array!!!
        abstract public function get_config();

        public final function get_type() {
            return $this->get_config_value("type");
        }

        public final function get_config_value($key) {
            $config = $this->get_config();
            return isset($config[$key]) ? $config[$key] : false;
        }

        public function is_instaled() {
            $config = $this->get_config();
            return (is_array($config) && count($config) && isset($config['instaled']) && $config['instaled']) ? true : false;
        }

        public function print_page(/* @var $dashboard WPEAE_DashboardPage */ $dashboard) {
            $dashboard_view = wpeae_get_api_path($this) . "view/dashboard.php";
            if (file_exists($dashboard_view)) {
                include_once $dashboard_view;
            } else {
                include_once WPEAE_ROOT_PATH . '/view/dashboard.php';
            }
        }

        /**
         * @deprecated deprecated since version 2.1.0.15
         */
        public function print_promo_page(/* @var $dashboard WPEAE_DashboardPage */ $dashboard) {
            $promo_page_view = wpeae_get_api_path($this) . "view/promo_page.php";
            if (file_exists($promo_page_view)) {
                include_once $promo_page_view;
            } else {
                include_once WPEAE_ROOT_PATH . 'view/promo_page.php';
            }
        }

        public function print_page_header(/* @var $dashboard WPEAE_DashboardPage */ $dashboard) {
            
        }

        public function print_page_footer(/* @var $dashboard WPEAE_DashboardPage */ $dashboard) {
            echo '<div class="wpeae_module_version">Module version: ' . $this->get_config_value("version") . '</div>';
        }

        public function print_api_account_setting_page($api) {
            if ($api->get_type() == $this->get_type()) {
                $api_account = wpeae_get_account($api->get_type());
                if ($api_account) {
                    $api_account->print_form();
                }
            }
        }

        public function print_api_setting_page($api) {
            if ($api->get_type() == $this->get_type()) {
                $setting_view = wpeae_get_api_path($this) . "view/settings.php";
                if (file_exists($setting_view)) {
                    include_once $setting_view;
                }
            }
        }

        public function install() {
            
        }

        public function uninstall() {
            
        }

        // configure common filters
        private final function init_filters() {
            $this->add_filter("wpeae_productId", "wpeae_productId", 10, array("type" => "edit",
                "label" => "ProductId",
                "dop_row" => "OR configure search filter",
                "placeholder" => "Please enter your productId"));
            $this->add_filter("wpeae_query", "wpeae_query", 20, array("type" => "edit",
                "label" => "Keywords",
                "placeholder" => "Please enter your Keywords"));
            $this->add_filter("price", array("wpeae_min_price", "wpeae_max_price"), 30, array("type" => "edit",
                "label" => "Price",
                "wpeae_min_price" => array("label" => "from $", "default" => "0.00"),
                "wpeae_max_price" => array("label" => " to $", "default" => "0.00")));
        }

        // configure custom api filters
        protected function configure_filters() {
            
        }

        public final function add_filter($id, $name, $order = 1000, $config = array()) {
            $this->filter_config[$id] = array('id' => $id, 'name' => $name, 'config' => $config, 'order' => $order);
        }

        public final function remove_filter($id) {
            unset($this->filter_config[$id]);
        }

        public final function get_filters() {
            $result = array();
            foreach ($this->filter_config as $id => $filter) {
                $result[$id] = $filter;
                if (isset($filter['config']['data_source']) && $filter['config']['data_source']) {
                    if (is_array($filter['config']['data_source'])) {
                        $result[$id]['config']['data_source'] = $filter['config']['data_source'][0]->{$filter['config']['data_source'][1]}();
                    } else {
                        $result[$id]['config']['data_source'] = ${$filter['config']['data_source']}();
                    }
                }
            }
            if (!function_exists('WPEAE_AbstractConfigurator_cmp')) {

                function WPEAE_AbstractConfigurator_cmp($a, $b) {
                    if ($a['order'] == $b['order']) {
                        return 0;
                    }
                    return ($a['order'] < $b['order']) ? -1 : 1;
                }

            }
            uasort($result, 'WPEAE_AbstractConfigurator_cmp');

            return $result;
        }

        // configure custom api initialization
        protected function init_module() {
            
        }

        public function modify_columns($columns, $api) {
            return $columns;
        }

        public function modify_sortable_columns($columns) {
            $sortable_columns = $columns;
            if (is_array($this->get_config_value("sort_columns"))) {
                foreach ($this->get_config_value("sort_columns") as $sc) {
                    $sortable_columns[$sc] = array($sc, false);
                }
            }
            return $sortable_columns;
        }

        public function modify_column_data($data, /* @var $item WPEAE_Goods */ $item, $column_name) {
            return $data;
        }

        // if you want save custom api settings, impliment this method in you class
        public function save_setting($data) {
            
        }

        private final function check_api_configure() {
            $config = $this->get_config();
            if (!is_array($config)) {
                throw new Exception('WPEAE Error: ' . get_class($this) . ' uncorect API configure! get_config() must return array');
            } else if (!isset($config['type']) || !$config['type']) {
                throw new Exception('WPEAE Error: ' . get_class($this) . ' uncorect API configure! Config array must have not empty "type"');
            } else if ($this->is_instaled() && !isset($config['account_class'])) {
                throw new Exception('WPEAE Error: ' . get_class($this) . ' uncorect API configure! Config array must have correct "account_class"');
            } else if ($this->is_instaled() && !isset($config['loader_class'])) {
                throw new Exception('WPEAE Error: ' . get_class($this) . ' uncorect API configure! Config array must have correct "loader_class"');
            }
        }

    }

    

    

    
    
endif;