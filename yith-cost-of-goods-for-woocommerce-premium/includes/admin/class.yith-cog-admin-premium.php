<?php
/*
 * This file belongs to the YITH Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */
if ( ! defined( 'YITH_COG_PATH' ) ) {
    exit( 'Direct access forbidden.' );
}

/**
 *
 *
 * @class      YITH_COG_Admin_Premium
 * @package    Yithemes
 * @since      Version 1.0.0
 * @author     Francisco Mendoza
 *
 */

if ( ! class_exists( 'YITH_COG_Admin_Premium' ) ) {
    /**
     * Class YITH_COG_Admin_Premium
     *
     * @author
     */
    class YITH_COG_Admin_Premium extends YITH_COG_Admin {
        /**
         * Construct
         *
         * @author
         * @since 1.0
         */

        public function __construct(){

            parent::__construct();

            /* ====== ENQUEUE STYLES AND JS ====== */
            add_action( 'admin_enqueue_scripts', array($this, 'enqueue_scripts' ) );
            /* === Register Panel Settings === */
            add_action( 'admin_menu', array($this, 'add_users_settings_page'), 5 );

            /* Register plugin to licence/update system */
            add_action('wp_loaded', array($this, 'register_plugin_for_activation'), 99);
            add_action('admin_init', array($this, 'register_plugin_for_updates'));

            // General Product Field
            // add cost field to simple products under the 'General' tab
            add_action( 'woocommerce_product_options_pricing', array( $this, 'add_cost_field_to_simple_product' ) );
            // add cost field to variable products under the 'General' tab
            add_action( 'woocommerce_product_options_sku', array( $this, 'add_cost_field_to_variable_product' ) );
            // save the cost field for simple products
            add_action( 'woocommerce_process_product_meta', array( $this, 'save_simple_product_cost' ), 10, 2 );
            // add cost field to variable products under the 'Variations' tab after the shipping class select
            add_action( 'woocommerce_product_after_variable_attributes', array( $this, 'add_cost_field_to_product_variation' ), 15, 3 );
            // save the cost field for variable products
            add_action( 'woocommerce_save_product_variation', array( $this, 'save_variation_product_cost' ) );
            // save the default cost, cost/min/max costs for variable products
            add_action( 'woocommerce_process_product_meta_variable', array( $this, 'save_variable_product_cost' ), 15 );
            add_action( 'woocommerce_ajax_save_product_variations',  array( $this, 'save_variable_product_cost' ), 15 );

            //Bulk Actions
            // adds the product variation 'Cost' bulk edit action
            add_action( 'woocommerce_variable_product_bulk_edit_actions', array( $this, 'add_variable_product_bulk_edit_cost_action' ) );
            // save variation cost for bulk edit action
            add_action( 'woocommerce_bulk_edit_variations_default', array( $this, 'variation_bulk_action_variable_cost' ), 10, 4 );
            //Edit products Cost in the Product List Table with the Bulk Action
            add_filter( 'bulk_actions-edit-product', array( $this,'bulk_actions_edit_product' ) );
            add_filter( 'handle_bulk_actions-edit-product', array( $this,'handle_bulk_actions_edit_product' ), 10, 3 ) ;

            // Product List Table
            // Adds a "Cost" column header
            add_filter( 'manage_edit-product_columns', array( $this, 'product_list_table_cost_column_header' ), 11 );
            // Renders the product cost in the product list table
            add_action( 'manage_product_posts_custom_column', array( $this, 'product_list_table_cost_column' ), 11 );
            // Make the "Cost" column display as sortable
            add_filter( 'manage_edit-product_sortable_columns', array( $this, 'product_list_table_cost_column_sortable' ), 11 );
            // Make the "Cost" column sortable
            add_filter( 'request', array( $this, 'product_list_table_cost_column_orderby' ), 11 );

            //Quick edit
            add_action( 'woocommerce_product_quick_edit_end',  array( $this, 'render_quick_edit_cost_field' ) );
            add_action( 'manage_product_posts_custom_column',  array( $this, 'add_quick_edit_inline_values' ) );
            add_action( 'woocommerce_product_quick_edit_save', array( $this, 'save_quick_edit_cost_field' ) );

            //Reports tab contents
            add_action( 'yith_cog_stock_reports_options', array( $this, 'stock_report_tab_content') );
            add_action( 'yith_cog_reports_options', array( $this, 'report_tab_content') );

            // Render the Apply Cost buttons in the settings tab
            add_action( 'woocommerce_admin_field_yith_cog_apply_cost_html', array($this, 'yith_apply_cost_buttons' ) );
            // Render the import Cost buttons in the settings tab
            add_action( 'woocommerce_admin_field_yith_cog_import_cost_html', array($this, 'yith_import_cost_buttons' ) );

        }


        /**
         * Register plugins for activation tab
         *
         * @return void
         * @since    1.0.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function register_plugin_for_activation()
        {
            if (!class_exists('YIT_Plugin_Licence')) {
                require_once YITH_COG_PATH . '/plugin-fw/licence/lib/yit-licence.php';
                require_once YITH_COG_PATH . '/plugin-fw/licence/lib/yit-plugin-licence.php';
            }
            YIT_Plugin_Licence()->register(YITH_COG_INIT, YITH_COG_SECRETKEY, YITH_COG_SLUG);

        }

        /**
         * Register plugins for update tab
         *
         * @return void
         * @since    1.0.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function register_plugin_for_updates()
        {
            if (!class_exists('YIT_Upgrade')) {
                require_once(YITH_COG_PATH . '/plugin-fw/lib/yit-upgrade.php');
            }
            YIT_Upgrade()->register(YITH_COG_SLUG, YITH_COG_INIT);
        }


        /**
         * Add Report settings page
         */
        public function add_users_settings_page()
        {
            if ( !empty( $this->_panel ) ) {
                return;
            }
            $admin_tabs = array(
                'settings' => __( 'General', 'yith-cost-of-goods-for-woocommerce' ),
                'reports' => __( 'Reports', 'yith-cost-of-goods-for-woocommerce' ),
                'stock-reports' => __( 'Stock Reports', 'yith-cost-of-goods-for-woocommerce' )
            );

            $args = array(
                'create_menu_page' => true,
                'parent_slug' => '',
                'page_title' => __('Cost of Goods', 'yith-cost-of-goods-for-woocommerce'),
                'menu_title' => __('Cost of Goods', 'yith-cost-of-goods-for-woocommerce'),
                'capability' => 'manage_options',
                'parent' => '',
                'parent_page' => 'yit_plugin_panel',
                'page' => $this->_panel_page,
                'admin-tabs' => apply_filters('fm_my_admin_users_options_tabs', $admin_tabs),
                'options-path' => YITH_COG_PATH . '/settings',
                'plugin-url' => YITH_COG_URL
            );

            /* === Fixed: not updated theme  === */
            if (!class_exists('YIT_Plugin_Panel_WooCommerce')) {
                require_once('../plugin-fw/lib/yit-plugin-panel-wc.php');
            }

            $this->_panel = new YIT_Plugin_Panel_WooCommerce($args);
            $this->_main_panel_option = "yit_{$args['parent']}_options";

            $this->save_default_options();
        }


        /** Plugin Tabs ***************************************/

        /**
         * Stock Report Tab in the YITH plugin
         */
        public function stock_report_tab_content() {
            if ( file_exists( constant( 'YITH_COG_PATH' ) . 'templates/stock_report_tab.php' ) ) {
                require_once( constant( 'YITH_COG_PATH' ) . 'templates/stock_report_tab.php' );
            }
        }

        /**
         * Report Tab in the YITH plugin
         */
        public function report_tab_content() {
            if ( file_exists( constant( 'YITH_COG_PATH' ) . 'templates/cog_report_tab.php' ) ) {
                require_once( constant( 'YITH_COG_PATH' ) . 'templates/cog_report_tab.php' );
            }
        }

        /**
         * Add default option to panel
         */
        public function save_default_options()
        {
            $options = get_option($this->_main_panel_option);

            if ($options === false) {
                add_option($this->_main_panel_option, $this->_panel->get_default_options());
            }
        }

        /**
         * Add settings to panel
         */
        public function yith_cog_settings( $settings ) {

            $updated_settings = array();
            foreach ( $settings as $setting ) {
                $updated_settings[] = $setting;

                if ( isset( $setting['id'] ) && 'product_inventory_options' === $setting['id']
                    && isset( $setting['type'] ) && 'sectionend' === $setting['type'] ) {
                    $updated_settings = array_merge( $updated_settings, $this->yith_cog_add_settings() );
                }
            }
            return $updated_settings;
        }

        /** Render the Cost for the Products & other methods ***************************************/

        /**
         * Add cost field to simple products
         */
        public function add_cost_field_to_simple_product(){

            woocommerce_wp_text_input(
                array(
                    'id' => 'yith_cog_cost',
                    'class' => 'wc_input_price short',
                    'label' => __('YITH Cost of Good', 'yith-cost-of-goods-for-woocommerce') . ' (' . get_woocommerce_currency_symbol() . ')',
                    'data_type' => 'price',
                )
            );
        }

        /**
         * Add cost field to variable products
         */
        public function add_cost_field_to_variable_product() {

            woocommerce_wp_text_input(
                array(
                    'id'                => 'yith_cog_cost_variable',
                    'class'             => 'wc_input_price short',
                    'wrapper_class'     => 'show_if_variable',
                    'label' => __('YITH Cost of Good', 'yith-cost-of-goods-for-woocommerce') . ' (' . get_woocommerce_currency_symbol() . ')',
                    'data_type'         => 'price',
                )
            );
        }

        /**
         * Save cost field for simple product
         */
        public function save_simple_product_cost($post_id){

            $product_type = empty($_POST['product-type']) ? 'simple' : sanitize_title(stripslashes($_POST['product-type']));

            if ($product_type !== 'variable') {
                update_post_meta($post_id, 'yith_cog_cost', $_POST['yith_cog_cost']);
            }
        }

        /**
         * Add cost field to variable products under the Variations tab after the shipping class dropdown
         */
        public function add_cost_field_to_product_variation( $loop, $variation_data, $variation ) {

            $default_cost = get_post_meta( $variation->post_parent, 'yith_cog_cost_variable', true );
            $cost         = get_post_meta( $variation->ID,          'yith_cog_cost', true );

            // if the variation cost is actually the default variable product cost
            if ( 'yes' === get_post_meta( $variation->ID, 'yith_cog_default_cost', true ) ) {
                $cost = '';
            }
            ?>
            <div>
                <p class="form-row form-row-first">
                    <label><?php
                        /* translators: Placeholder: %s - currency symbol */
                        printf( esc_html__( 'YITH Cost of Good: (%s)', 'yith-cost-of-goods-for-woocommerce' ), esc_html( get_woocommerce_currency_symbol() ) ); ?></label>
                    <input type="text" size="6" name="variable_cost_of_good[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( $cost ); ?>" class="wc_input_price" placeholder="<?php echo esc_attr( $default_cost ); ?>" />
                </p>
            </div>
            <?php
        }

        /**
         * Update cost meta for a variation
         */
        public function update_variation_product_cost( $variation_id, $cost ) {

            $parent_id    = null;
            $default_cost = null;

            if ( '' !== $cost ) {
                update_post_meta( $variation_id, 'yith_cog_cost',         wc_format_decimal( $cost ) );
                update_post_meta( $variation_id, 'yith_cog_default_cost', 'no' );
            } else {
                if ( is_null( $default_cost ) ) {
                    $parent_id    = wp_get_post_parent_id( $variation_id );
                    $default_cost = get_post_meta( $parent_id, 'yith_cog_cost_variable', true );
                }
                if ( $default_cost ) {
                    update_post_meta( $variation_id, 'yith_cog_cost',         wc_format_decimal( $default_cost ) );
                    update_post_meta( $variation_id, 'yith_cog_default_cost', 'yes' );
                } else {
                    update_post_meta( $variation_id, 'yith_cog_cost',         '' );
                    update_post_meta( $variation_id, 'yith_cog_default_cost', 'no' );
                }
            }
        }

        /**
         * Save cost field for the variation product
         */
        public function save_variation_product_cost( $variation_id ) {

            if ( false !== ( $i = array_search( $variation_id, $_POST['variable_post_id'] ) ) ) {

                $cost = $_POST['variable_cost_of_good'][ $i ];
                $this->update_variation_product_cost( $variation_id, $cost );
            }
        }

        /**
         * Save the overall cost/min/max costs for variable products
         */
        public function save_variable_product_cost( $post_id ) {

            if ( isset( $_POST['yith_cog_cost_variable'] ) ) {
                $cost = $_POST['yith_cog_cost_variable'];
            } else {
                $cost = get_post_meta( $post_id, 'yith_cog_cost_variable', true );
            }

            $this->update_variable_product_cost( $post_id, $cost );
        }

        /**
         * Update the cost meta for a variable product and set variations costs if needed.
         */
        protected function update_variable_product_cost( $product, $cost ) {

            $product = wc_get_product( $product );
            $product_id = $product->get_id();

            if ( ! $product ) {
                return;
            }

            update_post_meta( $product_id, 'yith_cog_cost_variable', wc_format_decimal( $cost ) );

            foreach ( $product->get_children() as $child_id ) {

                if ( $child_product = wc_get_product( $child_id ) ) {

                    $child_cost = get_post_meta( $child_id, 'yith_cog_cost', true );
                    $is_default = 'yes' === get_post_meta( $child_id, 'yith_cog_default_cost', true );

                    if ( '' === $child_cost || $is_default ) {
                        update_post_meta( $child_id, 'yith_cog_cost', wc_format_decimal( $cost ) );
                        update_post_meta( $child_id, 'yith_cog_default_cost', '' !== $cost ? 'yes' : 'no' );
                    }
                }
            }

            list( $min_variation_cost, $max_variation_cost ) = YITH_COG_Product::get_variable_product_min_max_costs( $product_id );

            update_post_meta( $product_id, 'yith_cog_cost',               wc_format_decimal( $min_variation_cost ) );
            update_post_meta( $product_id, 'yith_cog_min_variation_cost', wc_format_decimal( $min_variation_cost ) );
            update_post_meta( $product_id, 'yith_cog_max_variation_cost', wc_format_decimal( $max_variation_cost ) );
        }



        /** Bulk Actions methods ***************************************/

        /**
         * Cost bulk edit action on the products admin table
         */
        function bulk_actions_edit_product( $bulk_actions ) {
            $bulk_actions['set_cost_of_goods'] = __( 'Set Cost of Goods', 'yith-cost-of-goods-for-woocommerce' );
            return $bulk_actions;
        }

        /**
         * Handle the Cost bulk edit action on the products admin table
         */
        function handle_bulk_actions_edit_product( $redirect_to, $action, $post_ids ) {
            if ( $action !== 'set_cost_of_goods' ) {
                return $redirect_to;
            } else if ( ! isset( $_REQUEST['yith_cog_cost'] ) || empty ( $_REQUEST['yith_cog_cost'] ) ) {
                return $redirect_to;
            }

            $updated_post_ids = array();
            $new_cog = (float) $_REQUEST['yith_cog_cost'];

            foreach ( $post_ids as $post_id ) {
                $product = wc_get_product( $post_id );

                if  ( $product->get_type() == 'simple' ) {
                    if ( ! update_post_meta( $post_id, 'yith_cog_cost', $new_cog ) ) {
                        wp_die( __( 'Error during updating the product Cost of Goods.' ) );
                    }
                    $updated_post_ids[] = $post_id;
                }
                if  ( $product->get_type() == 'variable' ) {
                    if ( ! update_post_meta( $post_id, 'yith_cog_cost_variable', $new_cog ) ) {
                        wp_die( __( 'Error during updating the product Cost of Goods.' ) );
                    }
                    $updated_post_ids[] = $post_id;
                }
            }

            $redirect_to = add_query_arg( 'bulk_product_cog_update_results', count( $updated_post_ids ), $redirect_to );

            return $redirect_to;
        }


        /**
         * Cost bulk edit action on the product admin Variations tab
         */
        public function add_variable_product_bulk_edit_cost_action() {

            ?><optgroup label="<?php esc_attr_e( 'Cost of Goods', 'yith-cost-of-goods-for-woocommerce' ); ?>">
            <option value="variable_cost_of_goods"><?php _e( 'Set cost', 'yith-cost-of-goods-for-woocommerce' ); ?></option>
            </optgroup><?php
        }

        /**
         * Set variation cost for variations via bulk edit
         */
        public function variation_bulk_action_variable_cost( $bulk_action, $data, $product_id, $variations ) {


            if ( empty( $data['value'] ) ) {
                return;
            }
            if ( 'variable_cost_of_goods' !== $bulk_action ) {
                return;
            }
            foreach ( $variations as $variation_id ) {
                $this->update_variation_product_cost( $variation_id, $data['value'] );
            }
        }


        /** Product List table methods ********************************************/

        /**
         * Add a "Cost" column header on the Product list table
         */
        public function product_list_table_cost_column_header( $existing_columns )
        {

            $columns = array();
            foreach ($existing_columns as $key => $value) {
                $columns[$key] = $value;

                if ('price' === $key) {
                    $columns['cost'] = __('Cost', 'yith-cost-of-goods-for-woocommerce');
                }
            }
            return $columns;
        }

        /**
         * Product cost value in the products list table
         */
        public function product_list_table_cost_column($column)
        {
            /* @type \WC_Product $the_product */
            global $post, $the_product;

            if ('cost' === $column) {

                if (YITH_COG_Product::get_cost_html($the_product)) {
                    echo YITH_COG_Product::get_cost_html($the_product);
                } else {
                    echo '<span class="na">&ndash;</span>';
                }
            }
            apply_filters( 'yith_cog_product_list_table_column_value', $column );
        }

        /**
         * Add the Cost column to the list of sortable columns
         */
        public function product_list_table_cost_column_sortable($columns)
        {
            $columns['cost'] = 'cost';
            return $columns;
        }

        /**
         * Add the Cost column to the orderby clause if sorting by cost
         */
        public function product_list_table_cost_column_orderby($vars)
        {

            if (isset($vars['orderby']) && 'cost' === $vars['orderby']) {

                $vars = array_merge($vars, array(
                    'meta_key' => 'yith_cog_cost',
                    'orderby' => 'meta_value_num',
                ));
            }
            return $vars;
        }


        /** Quick Edit ****************************************************/

        /**
         * Quick edit cost field.
         */
        public function render_quick_edit_cost_field( ) {

            ?>
            <br class="clear" />
            <label class="alignleft">
                <span class="title"><?php esc_html_e( 'Cost of Gods', 'yith-cost-of-goods-for-woocommerce' ); ?></span>
                <span class="input-text-wrap">
					<input type="text" name="yith_cog_cost" class="text yith-cog-cost" value="">
				</span>
            </label>
            <?php
        }

        /**
         * Add markup for the custom product meta values so Quick Edit can fill the inputs.
         */
        public function add_quick_edit_inline_values( $column ) {
            /* @type \WC_Product $the_product */
            global $the_product;

            if ( 'name' === $column ) {

                $meta_key   = $the_product->is_type( 'variable' ) ? 'yith_cog_cost_variable' : 'yith_cog_cost';
                $meta_value = get_post_meta( $the_product->get_id(), $meta_key, true );

                echo '<div id="yith_cog_inline_' . esc_attr( $the_product->get_id() ) . '" class="hidden">';
                echo '<div class="yith_cog_cost">' . esc_html( $meta_value ) . '</div>';
                echo '</div>';
            }
        }


        /**
         * Save the quick edit cost field
         */
        public function save_quick_edit_cost_field( $product ) {

            $cost = isset( $_REQUEST['yith_cog_cost'] ) ? $_REQUEST['yith_cog_cost'] : '';

            if ( $product->is_type( 'variable' ) ) {
                $this->update_variable_product_cost( $product, $cost );
            } else {
                update_post_meta( $product->get_id(), 'yith_cog_cost', wc_format_decimal( $cost ) );
            }
        }

        /** Apply Cost buttons. ****************************************************/

        /**
         * Render the apply cost buttons.
         */
        public function yith_apply_cost_buttons(){

            ?>
            <tr id="ajax_zone">
                <td width="20%">
                    <ul>
                        <li><?php esc_html_e( 'Apply costs to orders that do not have costs set. ', 'yith-cost-of-goods-for-woocommerce' );?></li>
                        <br>
                        <li><?php esc_html_e( 'Apply and override the costs on all your orders. ', 'yith-cost-of-goods-for-woocommerce' );?></li>
                    </ul>
                </td>
                <td>
                    <ul>
                        <li><label><a class="apply_cost button" id="yith_cog_apply_cost" >Apply Costs</a></label><?php echo wc_help_tip( __( 'This will apply costs to previous orders if the cost was not set.', 'yith-cost-of-goods-for-woocommerce' ) ); ?></li>
                        <br>
                        <li><label><a class="apply_cost_overriding button"  id="yith_cog_apply_cost_overriding" >Apply Costs</a></label><?php echo wc_help_tip( __( 'This will apply costs to previous orders, overriding the previous cost if it was set.', 'yith-cost-of-goods-for-woocommerce' ) ); ?></li>
                    </ul>
                    <span class="description"><?php esc_html_e( 'This actions cannot be undone', 'yith-cost-of-goods-for-woocommerce' ); ?></span>
                </td>
            </tr>
            <?php
        }


        /** Import Cost buttons. ****************************************************/

        /**
         * Render the import cost buttons.
         */
        public function yith_import_cost_buttons(){

            ?>
            <tr id="ajax_zone_import_cost">
                <td width="20%">
                    <ul>
                        <li><?php esc_html_e( 'Import Cost of Goods from WooCommerce. ', 'yith-cost-of-goods-for-woocommerce' );?></li>
                    </ul>
                </td>
                <td>
                    <ul>
                        <li><label><a class="import_cost button" id="yith_cog_import_cost" >Import Costs</a></label><?php echo wc_help_tip( __( 'This will import the costs of the products from WooCommerce.', 'yith-cost-of-goods-for-woocommerce' ) ); ?></li>
                    </ul>
                    <span class="description"><?php esc_html_e( 'This action cannot be undone', 'yith-cost-of-goods-for-woocommerce' ); ?></span>
                </td>
            </tr>
            <?php
        }


        public function enqueue_scripts(){

            /* ====== Style ====== */
            wp_register_style( 'yith-cog-style', YITH_COG_ASSETS_URL . 'css/yith-cog-style.css', array(), YITH_COG_VERSION  );
            wp_enqueue_style( 'yith-cog-style' );

            /* ====== Script ====== */
            wp_register_script('yith-cog-admin-js', YITH_COG_ASSETS_URL . 'js/yith-cog-admin.js', array(
                'jquery',
                'jquery-ui-sortable',
                'jquery-ui-datepicker',
            ), YITH_COG_VERSION, true);
            wp_enqueue_script('yith-cog-admin-js');

            wp_localize_script('yith-cog-admin-js', 'object', array( 'ajaxurl' => admin_url('admin-ajax.php')));
        }


    }
}


