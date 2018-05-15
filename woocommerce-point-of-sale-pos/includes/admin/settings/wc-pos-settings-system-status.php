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

if (!class_exists('WC_POS_Admin_System_Status')) :

    /**
     * WC_POS_Admin_Settings_CSS
     */
    class WC_POS_Admin_System_Status extends WC_Settings_Page
    {
        private $last_update = array(
            'date' => '',
            'log' => array()
        );
        private $force_updates = array(
            '3.2.1' => 'wp-content/plugins/woocommerce-point-of-sale/includes/updates/wc_pos-update-3.2.1.php',
            '3.2.2.0' => 'wp-content/plugins/woocommerce-point-of-sale/includes/updates/wc_pos-update-3.2.2.0.php',
            '4.0.0' => 'wp-content/plugins/woocommerce-point-of-sale/includes/updates/wc_pos-update-4.0.0.php',
            '4.1.9' => 'wp-content/plugins/woocommerce-point-of-sale/includes/updates/wc_pos-update-4.1.9.php',
            '4.1.9.10' => 'wp-content/plugins/woocommerce-point-of-sale/includes/updates/wc_pos-update-4.1.9.10.php',
        );

        /**
         * Constructor.
         */
        public function __construct()
        {
            $this->id = 'system_status';
            $this->label = __('Advanced', 'wc_point_of_sale');

            add_filter('wc_pos_settings_tabs_array', array($this, 'add_settings_page'), 20);
            add_action('woocommerce_sections_' . $this->id, array($this, 'output_sections'));
            add_action('wc_pos_settings_' . $this->id, array($this, 'output'));
            add_action('wc_pos_settings_save_' . $this->id, array($this, 'save'));

        }

        /**
         * Get settings array
         *
         * @return array
         */
        public function get_settings()
        {
            $GLOBALS['hide_save_button'] = true;
            $update_status = __('OK', 'wc_point_of_sale');
            $last_update = get_option('wc_pos_last_force_db_update');
            $this->last_update = ($last_update) ? $last_update : $this->last_update;
            ?>
            <table class="widefat striped" style="margin-bottom: 1em;">
                <thead>
                <tr>
                    <th colspan="2">
                        <b><?php _e('WordPress Environment', 'wc_point_of_sale') ?></b>
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td style="width: 30%;">
                        <?php _e('Site URL:', 'wc_point_of_sale') ?>
                    </td>
                    <td>
                        <?php echo get_site_url(); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 30%;">
                        <?php _e('WooCommerce Version:', 'wc_point_of_sale') ?>
                    </td>
                    <td>
                        <?php echo WC()->version; ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php _e('WordPress Version:', 'wc_point_of_sale') ?>
                    </td>
                    <td>
                        <?php echo get_bloginfo('version'); ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php _e('Language:', 'wc_point_of_sale') ?>
                    </td>
                    <td>
                        <?php echo get_locale(); ?>
                    </td>
                </tr>
            </table>
            <table class="widefat striped" style="margin-bottom: 1em;">
                <thead>
                <tr>
                    <th colspan="2">
                        <b><?php _e('Server Environment', 'wc_point_of_sale') ?></b>
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td style="width: 30%;">
                        <?php _e('Server Info:', 'wc_point_of_sale') ?>
                    </td>
                    <td>
                        <?php echo esc_html($_SERVER['SERVER_SOFTWARE']); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 30%;">
                        <?php _e('PHP Version:', 'wc_point_of_sale') ?>
                    </td>
                    <td>
                        <?php echo $php_version = phpversion(); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 30%;">
                        <?php _e('PHP Post Max Size:', 'wc_point_of_sale') ?>
                    </td>
                    <td>
                        <?php echo size_format(wc_let_to_num(ini_get('post_max_size'))); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 30%;">
                        <?php _e('PHP Time Limit:', 'wc_point_of_sale') ?>
                    </td>
                    <td>
                        <?php echo ini_get('max_execution_time'); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 30%;">
                        <?php _e('PHP Max Input Vars:', 'wc_point_of_sale') ?>
                    </td>
                    <td>
                        <?php echo ini_get('max_input_vars'); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 30%;">
                        <?php _e('Max Upload Size:', 'wc_point_of_sale') ?>
                    </td>
                    <td>
                        <?php echo size_format(wp_max_upload_size()); ?>
                    </td>
                </tr>
            </table>
            <table class="widefat striped" style="margin-bottom: 1em;">
                <thead>
                <tr>
                    <th colspan="2">
                        <b><?php _e('Database', 'wc_point_of_sale') ?></b>
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td style="width: 30%;">
                        <?php _e('Last Forced Update: ', 'wc_point_of_sale') ?>
                    </td>
                    <td>
                        <?php echo $this->last_update['date'] ?>
                    </td>
                </tr>
                <?php $i = 1 ?>
                <?php foreach ($this->last_update['log'] as $version => $result) { ?>
                    <!--Show only last update-->
                    <?php if ($i == count($this->last_update['log'])) { ?>
                        <tr>
                            <td>
                                <?php echo __('POS Database Version: ', 'wc_point_of_sale') ?>
                            </td>
                            <td>
                                <?php echo $version ?>
                            </td>
                        </tr>
                        <?php if ($result) { ?>
                            <?php foreach ($result as $res) {
                                if ($res) {
                                    $update_status = __('Updated', 'wc_point_of_sale');
                                    break;
                                }
                            } ?>
                        <?php } ?>
                        <tr>
                            <td>
                                <?php echo __('Result:', 'wc_point_of_sale') ?>
                            </td>
                            <td>
                                <?php echo $update_status ?>
                            </td>
                        </tr>
                    <?php } ?>
                    <?php $i++ ?>
                <?php } ?>
                <tr>
                    <td>
                        <?php echo __('Database Update: ', 'wc_point_of_sale') ?>
                    </td>
                    <td>
                        <input name="save" class="button" type="submit"
                               value="<?php _e('Force Update', 'wc_point_of_sale'); ?>"/><br><span class="description"
                                                                                                   style="margin-top: .5em; display: inline-block;"><?php echo __('Use with caution: this tool will update the database to the latest version - useful when settings are not being applied as per configured in settings, registers, receipts and outlets.', 'wc_point_of_sale') ?></span>
                    </td>
                </tr>
            </table>
            <table class="widefat striped api_settings" style="margin-bottom: 1em;">
                <thead>
                <tr>
                    <th colspan="2">
                        <b><?php _e('API', 'wc_point_of_sale') ?></b>
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td style="width: 30%;">
                        <?php _e('API Enabled:', 'wc_point_of_sale') ?>
                    </td>
                    <td>
                        <?php echo 'yes' === get_option('woocommerce_api_enabled') ? '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>' : '<mark class="no">&ndash;</mark>'; ?>
                    </td>
                </tr>
            </table>
            <table class="widefat striped api_settings" style="margin-bottom: 1em;">
                <thead>
                <tr>
                    <th colspan="2">
                        <b><?php _e('Setup', 'wc_point_of_sale') ?></b>
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td style="width: 30%;">
                        <?php _e('Setup Wizard:', 'wc_point_of_sale') ?>
                    </td>
                    <td>
                        <a class="button"
                           href="<?php echo admin_url(); ?>admin.php?page=wc_pos-setup"><?php _e('Run Setup Wizard', 'wc_point_of_sale'); ?></a>
                    </
                    <br><span class="description"
                              style="margin-top: .5em; display: inline-block;"></<?php echo __('This tool will update the database to the latest version - useful when settings are not being applied as per configured in settings, registers, receipts and outlets.', 'wc_point_of_sale') ?></span>
                    </td>
                </tr>
            </table>
            <table class="widefat striped api_settings" style="margin-bottom: 1em;">
                <thead>
                <tr>
                    <th colspan="2">
                        <b><?php _e('DB Caching', 'wc_point_of_sale') ?></b>
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <?php var_dump(get_current_user_id()) ?>
                    <td style="width: 30%;">
                        <?php _e('Cache data to DB:', 'wc_point_of_sale') ?>
                    </td>
                    <td>
                        <a class="button"
                           href="<?php echo admin_url() ?>admin.php?action=wc_pos-cache-pos-data&wp-nonce=<?php echo wp_create_nonce('cache_data') ?>"><?php _e('Start', 'wc_point_of_sale'); ?></a>
                        <br><span class="description"
                                  style="margin-top: .5em; display: inline-block;"></<?php echo __('This tool will update the database to the latest version - useful when settings are not being applied as per configured in settings, registers, receipts and outlets.', 'wc_point_of_sale') ?></span>
                    </td>
                </tr>
            </table>
            <input type="hidden" class="update-log" value="<?php var_export($this->last_update) ?>">
            <?php return $this->last_update;
        }

        /**
         * Save settings
         */
        public function save()
        {
            $last_update['date'] = date('Y-m-d H:i');
            foreach ($this->force_updates as $version => $update) {
                include(ABSPATH . $update);
                if (isset($result)) {
                    $last_update['log'][$version] = $result;
                    unset($result);
                }
            }
            update_option('wc_pos_last_force_db_update', $last_update);
        }

    }

endif;

return new WC_POS_Admin_System_Status();
