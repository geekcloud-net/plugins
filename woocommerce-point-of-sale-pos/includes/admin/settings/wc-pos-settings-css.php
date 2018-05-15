<?php
/**
 * WooCommerce POS CSS Settings
 *
 * @author    Actuality Extensions
 * @package   WoocommercePointOfSale/Classes/settings
 * @category    Class
 * @since     0.1
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('WC_POS_Admin_Settings_CSS')) :

    /**
     * WC_POS_Admin_Settings_CSS
     */
    class WC_POS_Admin_Settings_CSS extends WC_Settings_Page
    {

        /**
         * Constructor.
         */
        public function __construct()
        {
            $this->id = 'css_pos';
            $this->label = __('CSS', 'woocommerce');

            add_filter('wc_pos_settings_tabs_array', array($this, 'add_settings_page'), 20);
            add_action('woocommerce_sections_' . $this->id, array($this, 'output_sections'));
            add_action('wc_pos_settings_' . $this->id, array($this, 'output'));
            add_action('woocommerce_admin_field_text_style_editor', array($this, 'text_style_editor_setting'));
            add_action('wc_pos_settings_save_' . $this->id, array($this, 'save'));

        }

        /**
         * Output installed payment gateway settings.
         *
         * @access public
         * @return void
         */
        public function text_style_editor_setting()
        {
            $custom_styles = get_option('pos_custom_styles', '@media print {&#010&#010/*' .  __('Insert code here') . '*/&#010&#010}');
            if(!$custom_styles){
                $custom_styles = '@media print {&#010&#010/*' .  __('Insert code here') . '*/&#010&#010}';
            }
            ?>
            <p><?php _e('Customise the look and feel of your registers using custom CSS. This will only apply to the registers loaded through this plugin.', 'wc_point_of_sale'); ?></p>
            <textarea name="wc_pos_custom_styles" id="wc_pos_custom_styles"
                      style="width: 100%; min-height: 300px; "><?php echo $custom_styles; ?></textarea>
            <!-- Create a simple CodeMirror instance -->
            <link rel="stylesheet"
                  href="<?php echo WC_POS()->plugin_url(); ?>/assets/plugins/codemirror/codemirror.css">
            <script src="<?php echo WC_POS()->plugin_url(); ?>/assets/plugins/codemirror/codemirror.js"></script>
            <script src="<?php echo WC_POS()->plugin_url(); ?>/assets/plugins/codemirror/css.js"></script>
            <script>
                var editor = CodeMirror.fromTextArea(document.getElementById("wc_pos_custom_styles"), {
                    lineNumbers: true,
                    mode: "css",
                });
            </script>
            <?php
        }

        /**
         * Get settings array
         *
         * @return array
         */
        public function get_settings()
        {
            global $woocommerce;
            return apply_filters('woocommerce_point_of_sale_css_settings_fields', array(

                array('title' => __('CSS', 'woocommerce'), 'type' => 'title', 'id' => 'css_options'),

                array('type' => 'text_style_editor'),

                array('type' => 'sectionend', 'id' => 'css_options'),

            )); // End general settings

        }

        /**
         * Save settings
         */
        public function save()
        {
            $settings = $this->get_settings();

            $custom_styles = (isset($_POST['wc_pos_custom_styles'])) ? $_POST['wc_pos_custom_styles'] : '';
            update_option('pos_custom_styles', $custom_styles);

            WC_POS_Admin_Settings::save_fields($settings);
        }

    }

endif;

return new WC_POS_Admin_Settings_CSS();
