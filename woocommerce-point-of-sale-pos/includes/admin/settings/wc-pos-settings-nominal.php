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

if (!class_exists('WC_POS_Admin_Settings_Nominal')) :

    /**
     * WC_POS_Admin_Settings_CSS
     */
    class WC_POS_Admin_Settings_Nominal extends WC_Settings_Page
    {

        /**
         * Constructor.
         */
        public function __construct()
        {
            $this->id = 'pos_nominal';
            $this->label = __('Denominations', 'wc_point_of_sale');

            add_filter('wc_pos_settings_tabs_array', array($this, 'add_settings_page'), 20);
            add_action('woocommerce_sections_' . $this->id, array($this, 'output_sections'));
            add_action('wc_pos_settings_' . $this->id, array($this, 'output'));
            // add_action('woocommerce_admin_field_text_style_editor', array($this, 'text_style_editor_setting'));
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
            $pos_nominal = get_option('wc_pos_cash_nominal');
            ?>
            <p><?php _e('Denominations', 'wc_point_of_sale'); ?></p>
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
            $pos_nominal = get_option('wc_pos_cash_nominal');
            echo '<h3>' . __('Denomination Options', 'wc_point_of_sale') . '</h3>';
            echo '<div class="cash-nominal-content-main">';
            echo '<div class="cash-nominal-content">';
            if ($pos_nominal) {
                foreach ($pos_nominal as $nominal) {
                    echo '<div class="nominal-row"><input type="number" name="wc_pos_cash_nominal[]" value="' . $nominal . '" step="0.01"><span class="remove"></span></div>';
                }
            }
            echo '</div>';
            echo '<a href="#" class="button add-nominal">' . __('Add Denomination', 'wc_point_of_sale') .  '</a>';
            echo '</div>';
            return apply_filters('woocommerce_point_of_sale_nominal_settings_fields', array(
                array('type' => 'sectionend', 'id' => 'pos_nominal'),
            ));
        }

        /**
         * Save settings
         */
        public function save()
        {
            $pos_nominal = (isset($_POST['wc_pos_cash_nominal'])) ? $_POST['wc_pos_cash_nominal'] : '';
            update_option('wc_pos_cash_nominal', $pos_nominal);
        }

    }

endif;

return new WC_POS_Admin_Settings_Nominal();
