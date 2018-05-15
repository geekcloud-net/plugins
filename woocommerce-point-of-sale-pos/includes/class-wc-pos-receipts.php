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

if (!class_exists('WC_Pos_Receipts')) :

    /**
     * WC_Pos_Receipts Class
     */
    class WC_Pos_Receipts
    {

        /**
         * @var WC_Pos_Receipts The single instance of the class
         * @since 1.9
         */
        protected static $_instance = null;

        /**
         * Main WC_Pos_Receipts Instance
         *
         * Ensures only one instance of WC_Pos_Receipts is loaded or can be loaded.
         *
         * @since 1.9
         * @static
         * @return WC_Pos_Receipts Main instance
         */
        public static function instance()
        {
            if (is_null(self::$_instance)) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        /**
         * Cloning is forbidden.
         *
         * @since 1.9
         */
        public function __clone()
        {
            _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'woocommerce'), '1.9');
        }

        /**
         * Unserializing instances of this class is forbidden.
         *
         * @since 1.9
         */
        public function __wakeup()
        {
            _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'woocommerce'), '1.9');
        }

        /**
         * __construct function.
         *
         * @access public
         * @return void
         */
        public function __construct()
        {

        }

        public function get_data($ids = '')
        {
            global $wpdb;
            $filter = '';
            if (!empty($ids)) {
                if (is_array($ids)) {
                    $ids = implode(',', array_map('intval', $ids));
                    $filter .= "WHERE ID IN  == ($ids)";
                } else {
                    $filter .= "WHERE ID = $ids";
                }
            }
            if (isset($_REQUEST['s']) && !empty($_REQUEST['s']) && $_REQUEST['page'] == WC_POS()->id_receipts) {
                $s = $_REQUEST['s'];
                $filter = "WHERE lower( concat(name) ) LIKE lower('%$s%')";
            }
            $table_name = $wpdb->prefix . "wc_poin_of_sale_receipts";
            $db_data = $wpdb->get_results("SELECT * FROM $table_name $filter");
            $data = array();

            foreach ($db_data as $value) {
                $data[] = get_object_vars($value);
            }
            return $data;
        }

        public function get_data_names()
        {
            $data = $this->get_data();
            $names_list = array();
            foreach ($data as $value) {
                $names_list[$value['ID']] = $value['name'];
            }
            return $names_list;
        }

        public static function get_default_receipt_options()
        {
            $receipt_options = array(
                'name' => '',
                'print_outlet_address' => 'yes',
                'print_outlet_contact_details' => 'yes',
                'telephone_label' => __('', 'wc_point_of_sale'),
                'fax_label' => __('', 'wc_point_of_sale'),
                'email_label' => __('', 'wc_point_of_sale'),
                'website_label' => __('', 'wc_point_of_sale'),
                'receipt_title' => __('Receipt', 'wc_point_of_sale'),
                'order_number_label' => __('Order', 'wc_point_of_sale'),
                'order_date_label' => __('Date', 'wc_point_of_sale'),
                'order_date_format' => 'Y-m-d',
                'print_order_time' => 'yes',
                'print_server' => 'yes',
                'served_by_label' => __('Served by', 'wc_point_of_sale'),
                'served_by_type' => 'username',
                'tax_label' => __('Tax', 'wc_point_of_sale'),
                'total_label' => __('Total', 'wc_point_of_sale'),
                'payment_label' => __('Sales', 'wc_point_of_sale'),
                'print_number_items' => 'yes',
                'items_label' => __('Items', 'wc_point_of_sale'),
                'print_barcode' => 'yes',
                'show_image_product' => 'no',
                'print_tax_number' => 'no',
                'tax_number_label' => __('Tax', 'wc_point_of_sale'),
                'header_text' => '',
                'footer_text' => '',
                'logo' => '',
                'text_size' => '',
                'title_position' => 'center',
                'logo_size' => '',
                'logo_position' => '',
                'contact_position' => '',
                'tax_number_position' => '',
                'print_order_notes' => 'yes',
                'order_notes_label' => __('Note', 'wc_point_of_sale'),
                'print_customer_name' => 'yes',
                'customer_name_label' => __('Customer', 'wc_point_of_sale'),
                'print_customer_email' => 'yes',
                'customer_email_label' => __('Email', 'wc_point_of_sale'),
                'print_customer_phone' => 'yes',
                'customer_phone_label' => __('Telephone', 'wc_point_of_sale'),
                'print_customer_ship_address' => 'yes',
                'customer_ship_address_label' => __('Shipping', 'wc_point_of_sale'),
                'show_sku' => '',
                'show_cost' => 'yes',
                'show_register' => 'yes',
                'show_outlet' => 'yes',
                'show_site_name' => 'yes',
                'gift_receipt_title' => __('Gift Receipt', 'wc_point_of_sale'),
                'print_copies_count' => 1,
                'receipt_width' => '0',
                'show_twitter' => 'no',
                'show_facebook' => 'no',
                'show_instagram' => 'no',
                'show_snapchat' => 'no',
                'socials_display_option' => ''
            );
            return $receipt_options;
        }

        public function display_single_receipt_page()
        {
            # receipt
            global $user_ID;
            $receipt_ID = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            $user_ID = isset($user_ID) ? (int)$user_ID : 0;
            $form_action = 'save_receipt';

            $receipt_options = self::get_default_receipt_options();

            if ($receipt_ID) {
                $d_receipt_options = $this->get_data($receipt_ID);
                $d_receipt_options = $d_receipt_options[0];
                $receipt_options = array_merge($receipt_options, $d_receipt_options);
            }
            ?>
            <div class="wrap">
                <h2><?php
                    if ($_GET['action'] == 'edit') {
                        _e('Edit Receipt Template', 'wc_point_of_sale');
                        echo ' <a href="' . esc_url(admin_url('admin.php?page=' . WC_POS()->id_receipts . '&action=add')) . '" class="add-new-h2">' . __('Add New', 'wc_point_of_sale') . '</a>';
                    } elseif ($_GET['action'] == 'add') {
                        _e('Add New Receipt Template', 'wc_point_of_sale');
                    }
                    ?></h2>
                <?php echo $this->display_messages(); ?>
                <div id="lost-connection-notice" class="error hidden">
                    <p>
                        <span class="spinner"></span> <?php _e('<strong>Connection lost.</strong> Saving has been disabled until you&#8217;re reconnected.'); ?>
                        <span class="hide-if-no-sessionstorage"><?php _e('We&#8217;re backing up this post in your browser, just in case.'); ?></span>
                    </p>
                </div>
                <form action="" method="post" id="edit_wc_pos_receipt">
                    <?php wp_nonce_field('wc_point_of_sale_edit_receipt'); ?>
                    <input type="hidden" id="user-id" name="user_ID" value="<?php echo (int)$user_ID ?>"/>
                    <input type="hidden" id="receipt-id" name="receipt_ID" value="<?php echo (int)$receipt_ID ?>"/>
                    <input type="hidden" id="hiddenaction" name="action" value="<?php echo esc_attr($form_action) ?>"/>
                    <input type="hidden" id="referredby" name="referredby"
                           value="<?php echo esc_url(wp_get_referer()); ?>"/>
                    <div id="poststuff">
                        <div id="post-body" class="metabox-holder columns-2">
                            <div id="postbox-container-2" class="postbox-container">
                                <div class="meta-box-sortables">

                                    <div class="postbox ">
                                        <button type="button" class="handlediv button-link" aria-expanded="true">
                                            <span class="screen-reader-text"><?php _e('Click to toggle'); ?> </span>
                                            <span class="toggle-indicator" aria-hidden="true"></span>
                                        </button>
                                        <h3 class="hndle">
                                            <span><?php _e('General Details', 'wc_point_of_sale'); ?></span>
                                        </h3>
                                        <div class="inside">
                                            <table id="receipt_options">
                                                <tr>
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="receipt_name"><?php _e('Template Name', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <input type="text" id="receipt_name" name="receipt_name"
                                                               value="<?php echo $receipt_options['name']; ?>">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="receipt_receipt_title"><?php _e('Receipt Title', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <input type="text" id="receipt_receipt_title"
                                                               name="receipt_receipt_title"
                                                               value="<?php echo $receipt_options['receipt_title']; ?>">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="gift_receipt_title"><?php _e('Gift Receipt Title', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <input type="text" id="gift_receipt_title"
                                                               name="gift_receipt_title"
                                                               value="<?php echo $receipt_options['gift_receipt_title']; ?>">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="receipt_print_copies_count"><?php _e('Number of Copies', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <select id="receipt_print_copies_count"
                                                                name="print_copies_count">
                                                            <option value="1" <?php echo ($receipt_options['print_copies_count'] == 1) ? 'selected' : '' ?>>
                                                                1
                                                            </option>
                                                            <option value="2" <?php echo ($receipt_options['print_copies_count'] == 2) ? 'selected' : '' ?>>
                                                                2
                                                            </option>
                                                            <option value="3" <?php echo ($receipt_options['print_copies_count'] == 3) ? 'selected' : '' ?>>
                                                                3
                                                            </option>
                                                            <option value="4" <?php echo ($receipt_options['print_copies_count'] == 4) ? 'selected' : '' ?>>
                                                                4
                                                            </option>
                                                            <option value="5" <?php echo ($receipt_options['print_copies_count'] == 5) ? 'selected' : '' ?>>
                                                                5
                                                            </option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="receipt_width"><?php _e('Receipt Width', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <input type="number" name="receipt_width"
                                                               class="form-control bfh-number" id="receipt_width"
                                                               min="0" max="120" step="5" default="0"
                                                               value="<?php echo $receipt_options['receipt_width']; ?>"/><span
                                                                style="margin-left: 10px;">mm</span>
                                                        <span style="margin-top: .5em; background: #f5f5f5; border: 1px solid #ddd; border-radius: 5px; padding: .5em 1em; margin-left: 1em;"><?php _e('0mm = Dynamic Width', 'wc_point_of_sale'); ?></span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="receipt_print_tax_summary"><?php _e('Include Tax Summary', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" value="yes"
                                                               id="receipt_print_tax_summary"
                                                               name="tax_summary" <?php echo ($receipt_options['tax_summary'] == 'yes') ? 'checked="checked"' : ''; ?>>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div id="major-publishing-actions">
                                            <div id="publishing-action">
                                                <span class="spinner"></span>
                                                <input type="submit" accesskey="p" value="Save"
                                                       class="button button-primary button-large" id="save_receipt">
                                            </div>
                                            <div class="clear"></div>
                                        </div>
                                    </div>
                                    <div class="postbox ">
                                        <button type="button" class="handlediv button-link" aria-expanded="true">
                                            <span class="screen-reader-text"><?php _e('Click to toggle'); ?> </span>
                                            <span class="toggle-indicator" aria-hidden="true"></span>
                                        </button>
                                        <h3 class="hndle">
                                            <span><?php _e('Style Details', 'wc_point_of_sale'); ?></span>
                                        </h3>
                                        <div class="inside">
                                            <table id="receipt_options">
                                                <tr>
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="title_position"><?php _e('Title Position', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <select type="text" id="title_position" name="title_position"
                                                                class="wc_pos_receipt">
                                                            <option value="left" <?php selected('left', $receipt_options['title_position'], true); ?> ><?php _e('Left', 'wc_point_of_sale'); ?></option>
                                                            <option value="center" <?php selected('center', $receipt_options['title_position'], true); ?>><?php _e('Center', 'wc_point_of_sale'); ?></option>
                                                            <option value="right" <?php selected('right', $receipt_options['title_position'], true); ?>><?php _e('Right', 'wc_point_of_sale'); ?></option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr class="show_receipt_print_outlet_contact_details">
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="logo_position"><?php _e('Logo Position', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <select type="text" id="logo_position" name="logo_position"
                                                                class="wc_pos_receipt">
                                                            <option value="left" <?php selected('left', $receipt_options['logo_position'], true); ?> ><?php _e('Left', 'wc_point_of_sale'); ?></option>
                                                            <option value="center" <?php selected('center', $receipt_options['logo_position'], true); ?>><?php _e('Center', 'wc_point_of_sale'); ?></option>
                                                            <option value="right" <?php selected('right', $receipt_options['logo_position'], true); ?>><?php _e('Right', 'wc_point_of_sale'); ?></option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="logo_size"><?php _e('Logo Size', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <select type="text" id="logo_size" name="logo_size"
                                                                class="wc_pos_receipt">
                                                            <option value="normal" <?php selected('nomal', $receipt_options['logo_size'], true); ?> ><?php _e('Normal', 'wc_point_of_sale'); ?></option>
                                                            <option value="small" <?php selected('small', $receipt_options['logo_size'], true); ?>><?php _e('Small', 'wc_point_of_sale'); ?></option>
                                                            <option value="large" <?php selected('large', $receipt_options['logo_size'], true); ?>><?php _e('Large', 'wc_point_of_sale'); ?></option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="text_size"><?php _e('Text Size', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <select type="text" id="text_size" name="text_size"
                                                                class="wc_pos_receipt">
                                                            <option value="normal" <?php selected('nomal', $receipt_options['text_size'], true); ?> ><?php _e('Normal', 'wc_point_of_sale'); ?></option>
                                                            <option value="small" <?php selected('small', $receipt_options['text_size'], true); ?>><?php _e('Small', 'wc_point_of_sale'); ?></option>
                                                            <option value="large" <?php selected('large', $receipt_options['text_size'], true); ?>><?php _e('Large', 'wc_point_of_sale'); ?></option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr class="show_receipt_print_outlet_contact_details">
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="contact_position"><?php _e('Address, Contact & Social Alignment', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <select type="text" id="contact_position"
                                                                name="contact_position" class="wc_pos_receipt">
                                                            <option value="left" <?php selected('left', $receipt_options['contact_position'], true); ?> ><?php _e('Left', 'wc_point_of_sale'); ?></option>
                                                            <option value="center" <?php selected('center', $receipt_options['contact_position'], true); ?>><?php _e('Center', 'wc_point_of_sale'); ?></option>
                                                            <option value="right" <?php selected('right', $receipt_options['contact_position'], true); ?>><?php _e('Right', 'wc_point_of_sale'); ?></option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr class="show_receipt_print_outlet_contact_details">
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="tax_number_position"><?php _e('Tax Number Position', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <select type="text" id="tax_number_position"
                                                                name="tax_number_position" class="wc_pos_receipt">
                                                            <option value="left" <?php selected('left', $receipt_options['tax_number_position'], true); ?> ><?php _e('Left', 'wc_point_of_sale'); ?></option>
                                                            <option value="center" <?php selected('center', $receipt_options['tax_number_position'], true); ?>><?php _e('Center', 'wc_point_of_sale'); ?></option>
                                                            <option value="right" <?php selected('right', $receipt_options['tax_number_position'], true); ?>><?php _e('Right', 'wc_point_of_sale'); ?></option>
                                                        </select>
                                                    </td>
                                                </tr>
                                            </table>

                                        </div>
                                    </div>

                                    <div class="postbox ">
                                        <button type="button" class="handlediv button-link" aria-expanded="true">
                                            <span class="screen-reader-text"><?php _e('Click to toggle'); ?> </span>
                                            <span class="toggle-indicator" aria-hidden="true"></span>
                                        </button>
                                        <h3 class="hndle">
                                            <label class="receipt_labels"
                                                   for="receipt_logo"><?php _e('Receipt Logo', 'wc_point_of_sale'); ?></label>
                                        </h3>
                                        <div class="inside">
                                            <p class="hide-if-no-js">
                                                <?php $receipt_logo_style = (!$receipt_options['logo']) ? 'style="display: none;"' : ''; ?>
                                            <div class="placeholder"
                                                 id="receipt_logo_placeholder" <?php echo ($receipt_options['logo']) ? 'style="display: none;"' : ''; ?>>
                                                <?php _e('No logo selected', 'wc_point_of_sale'); ?>
                                            </div>
                                            <div class="set_receipt_logo" id="set_receipt_logo_img" href="#"
                                                <?php echo $receipt_logo_style; ?>>
                                                <?php $attachment_image_logo = wp_get_attachment_image_src($receipt_options['logo'], 'full'); ?>
                                                <img src="<?php echo $attachment_image_logo[0] ?>"
                                                     style="max-height: 100px;">
                                            </div>
                                            <input type="hidden" name="receipt_logo" id="receipt_logo"
                                                   value="<?php echo $receipt_options['logo']; ?>">
                                            <a class="remove_receipt_logo button" href="#"
                                               title="<?php _e('Remove', 'wc_point_of_sale'); ?>" <?php echo $receipt_logo_style; ?> >
                                                <?php _e('Remove', 'wc_point_of_sale'); ?>
                                            </a>
                                            <a class="set_receipt_logo button" id="set_receipt_logo_text" href="#"
                                               title="<?php _e('Select Logo', 'wc_point_of_sale'); ?>" <?php echo ($receipt_options['logo']) ? 'style="display: none;"' : ''; ?> >
                                                <?php _e('Select Logo', 'wc_point_of_sale'); ?>
                                            </a>
                                            <p class="description"
                                               style="margin-top: 1em;"><?php _e('Recommend logo height to width ratio is 1:4 i.e. 70px height and 280px width..', 'wc_point_of_sale'); ?></p>
                                            </p>
                                        </div>

                                    </div>
                                    <div class="postbox ">
                                        <button type="button" class="handlediv button-link" aria-expanded="true">
                                            <span class="screen-reader-text"><?php _e('Click to toggle'); ?> </span>
                                            <span class="toggle-indicator" aria-hidden="true"></span>
                                        </button>
                                        <h3 class="hndle">
                                            <span><?php _e('Header Details', 'wc_point_of_sale'); ?></span>
                                        </h3>
                                        <div class="inside">
                                            <table id="receipt_options">
                                                <tr>
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="show_site_name"><?php _e('Shop Name', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" id="show_site_name"
                                                               value="yes"
                                                               name="show_site_name" <?php echo ($receipt_options['show_site_name'] == 'yes') ? 'checked="checked"' : ''; ?>>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="show_outlet"><?php _e('Outlet Name', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" id="show_outlet"
                                                               value="yes"
                                                               name="show_outlet" <?php echo ($receipt_options['show_outlet'] == 'yes') ? 'checked="checked"' : ''; ?>>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="receipt_print_outlet_address"><?php _e('Outlet Address', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" id="receipt_print_outlet_address"
                                                               value="yes"
                                                               name="receipt_print_outlet_address" <?php echo ($receipt_options['print_outlet_address'] == 'yes') ? 'checked="checked"' : ''; ?>>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="receipt_print_outlet_contact_details"><?php _e('Outlet Contact Details', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" id="receipt_print_outlet_contact_details"
                                                               value="yes"
                                                               name="receipt_print_outlet_contact_details" <?php echo ($receipt_options['print_outlet_contact_details'] == 'yes') ? 'checked="checked"' : ''; ?>>
                                                    </td>
                                                </tr>
                                                <tr class="show_receipt_print_outlet_contact_details">
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="receipt_telephone_label"><?php _e('Telephone Label', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <input type="text" id="receipt_telephone_label"
                                                               name="receipt_telephone_label"
                                                               value="<?php echo $receipt_options['telephone_label']; ?>">
                                                    </td>
                                                </tr>
                                                <tr class="show_receipt_print_outlet_contact_details">
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="receipt_fax_label"><?php _e('Fax Label', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <input type="text" id="receipt_fax_label"
                                                               name="receipt_fax_label"
                                                               value="<?php echo $receipt_options['fax_label']; ?>">
                                                    </td>
                                                </tr>
                                                <tr class="show_receipt_print_outlet_contact_details">
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="receipt_email_label"><?php _e('Email Label', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <input type="text" id="receipt_email_label"
                                                               name="receipt_email_label"
                                                               value="<?php echo $receipt_options['email_label']; ?>">
                                                    </td>
                                                </tr>
                                                <tr class="show_receipt_print_outlet_contact_details">
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="receipt_website_label"><?php _e('Website Label', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <input type="text" id="receipt_website_label"
                                                               name="receipt_website_label"
                                                               value="<?php echo $receipt_options['website_label']; ?>">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="receipt_print_tax_number"><?php _e('Tax Number', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" id="receipt_print_tax_number" value="yes"
                                                               name="receipt_print_tax_number" <?php echo ($receipt_options['print_tax_number'] == 'yes') ? 'checked="checked"' : ''; ?> >
                                                    </td>
                                                </tr>
                                                <tr class="show_receipt_print_tax_number">
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="receipt_tax_number_label"><?php _e('Tax Number Label', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <input type="text" id="receipt_tax_number_label"
                                                               name="receipt_tax_number_label"
                                                               value="<?php echo $receipt_options['tax_number_label']; ?>">
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="postbox ">
                                        <button type="button" class="handlediv button-link" aria-expanded="true">
                                            <span class="screen-reader-text"><?php _e('Click to toggle'); ?> </span>
                                            <span class="toggle-indicator" aria-hidden="true"></span>
                                        </button>
                                        <h3 class="hndle">
                                            <span><?php _e('Order Details', 'wc_point_of_sale'); ?></span>
                                        </h3>
                                        <div class="inside">
                                            <table id="receipt_options">
                                                <tr>
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="receipt_order_number_label"><?php _e('Order Number Label', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <input type="text" id="receipt_order_number_label"
                                                               name="receipt_order_number_label"
                                                               value="<?php echo $receipt_options['order_number_label']; ?>">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="receipt_print_order_time"><?php _e('Order Date', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" id="receipt_print_order_time" value="yes"
                                                               name="receipt_print_order_time" <?php echo ($receipt_options['print_order_time'] == 'yes') ? 'checked="checked"' : ''; ?>>
                                                    </td>
                                                </tr>
                                                <tr class="show_receipt_print_order_time">
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="receipt_order_date_label"><?php _e('Order Date Label', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <input type="text" id="receipt_order_date_label"
                                                               name="receipt_order_date_label"
                                                               value="<?php echo $receipt_options['order_date_label']; ?>">
                                                    </td>
                                                </tr>
                                                <tr class="show_receipt_print_order_time">
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="receipt_order_date_format"><?php _e('Order Date Format', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $date_formats = array_unique(apply_filters('date_formats', array(__('F j, Y'), 'Y-m-d', 'm/d/Y', 'd/m/Y')));

                                                        $custom = true;

                                                        foreach ($date_formats as $format) {
                                                            echo "\t<label title='" . esc_attr($format) . "'><input type='radio' name='receipt_order_date_format' value='" . esc_attr($format) . "'";
                                                            if ($receipt_options['order_date_format'] === $format) { // checked() uses "==" rather than "==="
                                                                echo " checked='checked'";
                                                                $custom = false;
                                                            }
                                                            echo ' /> ' . date_i18n($format) . "</label><br />\n";
                                                        }

                                                        echo '	<label><input type="radio" name="receipt_order_date_format" id="date_format_custom_radio" value="\c\u\s\t\o\m"';
                                                        checked($custom);
                                                        echo '/> ' . __('Custom:') . '<span class="screen-reader-text"> ' . __('enter a custom date format in the following field') . "</span></label>\n";
                                                        echo '<label for="date_format_custom" class="screen-reader-text">' . __('Custom date format:') . '</label><input type="text" name="receipt_order_date_format_custom" id="receipt_order_date_format_custom" value="' . esc_attr($receipt_options['order_date_format']) . '" class="small-text" /> <span class="screen-reader-text">' . __('example:') . ' </span><span class="example"> ' . date_i18n($receipt_options['order_date_format']) . "</span> <span class='spinner'></span>\n";
                                                        ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="receipt_print_customer_name"><?php _e('Customer Name', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" id="receipt_print_customer_name"
                                                               value="yes"
                                                               name="receipt_print_customer_name" <?php echo ($receipt_options['print_customer_name'] == 'yes') ? 'checked="checked"' : ''; ?>>
                                                    </td>
                                                </tr>
                                                <tr class="show_receipt_print_customer_name">
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="receipt_customer_name_label"><?php _e('Customer Name Label', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <input type="text" id="receipt_customer_name_label"
                                                               name="receipt_customer_name_label"
                                                               value="<?php echo $receipt_options['customer_name_label']; ?>">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="receipt_print_customer_name"><?php _e('Customer Name', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" id="receipt_print_customer_name"
                                                               value="yes"
                                                               name="receipt_print_customer_name" <?php echo ($receipt_options['print_customer_name'] == 'yes') ? 'checked="checked"' : ''; ?>>
                                                    </td>
                                                </tr>
                                                <tr class="show_receipt_print_customer_name">
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="receipt_customer_name_label"><?php _e('Customer Name Label', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <input type="text" id="receipt_customer_name_label"
                                                               name="receipt_customer_name_label"
                                                               value="<?php echo $receipt_options['customer_name_label']; ?>">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="receipt_print_customer_email"><?php _e('Customer Email', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" id="receipt_print_customer_email"
                                                               value="yes"
                                                               name="receipt_print_customer_email" <?php echo ($receipt_options['print_customer_email'] == 'yes') ? 'checked="checked"' : ''; ?>>
                                                    </td>
                                                </tr>
                                                <tr class="show_receipt_print_customer_email">
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="receipt_customer_email_label"><?php _e('Customer Email Label', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <input type="text" id="receipt_customer_email_label"
                                                               name="receipt_customer_email_label"
                                                               value="<?php echo $receipt_options['customer_email_label']; ?>">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="receipt_print_customer_phone"><?php _e('Customer Phone', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" id="receipt_print_customer_phone"
                                                               value="yes"
                                                               name="receipt_print_customer_phone" <?php echo ($receipt_options['print_customer_phone'] == 'yes') ? 'checked="checked"' : ''; ?>>
                                                    </td>
                                                </tr>
                                                <tr class="show_receipt_print_customer_phone">
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="receipt_customer_phone_label"><?php _e('Customer Phone Label', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <input type="text" id="receipt_customer_phone_label"
                                                               name="receipt_customer_phone_label"
                                                               value="<?php echo $receipt_options['customer_phone_label']; ?>">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="receipt_print_customer_ship_address"><?php _e('Shipping Address', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" id="receipt_print_customer_ship_address"
                                                               value="yes"
                                                               name="receipt_print_customer_ship_address" <?php echo ($receipt_options['print_customer_ship_address'] == 'yes') ? 'checked="checked"' : ''; ?>>
                                                    </td>
                                                </tr>
                                                <tr class="show_receipt_print_customer_ship_address">
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="receipt_customer_ship_address_label"><?php _e('Shipping Address Label', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <input type="text" id="receipt_customer_ship_address_label"
                                                               name="receipt_customer_ship_address_label"
                                                               value="<?php echo $receipt_options['customer_ship_address_label']; ?>">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="receipt_print_server"><?php _e('Cashier Name', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" id="receipt_print_server" value="yes"
                                                               name="receipt_print_server" <?php echo ($receipt_options['print_server'] == 'yes') ? 'checked="checked"' : ''; ?>>
                                                    </td>
                                                </tr>
                                                <tr class="show_receipt_print_register_name_served">
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="show_register"><?php _e('Register Name', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" id="show_register" value="yes"
                                                               name="show_register" <?php echo ($receipt_options['show_register'] == 'yes') ? 'checked="checked"' : ''; ?>>
                                                    </td>
                                                </tr>
                                                <tr class="show_receipt_print_server">
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="receipt_served_by_label"><?php _e('Served By Label', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <input type="text" id="receipt_served_by_label"
                                                               name="receipt_served_by_label"
                                                               value="<?php echo $receipt_options['served_by_label']; ?>">
                                                    </td>
                                                </tr>
                                                <tr class="show_receipt_print_server">
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="receipt_served_by_type"><?php _e('Served By Type', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <select id="receipt_served_by_type"
                                                                name="receipt_served_by_type">
                                                            <option value="username" <?php selected($receipt_options['served_by_type'], 'username', true); ?> >
                                                                <?php _e('Username', 'wc_point_of_sale'); ?>
                                                            </option>
                                                            <option value="display_name" <?php selected($receipt_options['served_by_type'], 'display_name', true); ?> >
                                                                <?php _e('Display Name', 'wc_point_of_sale'); ?>
                                                            </option>
                                                            <option value="nickname" <?php selected($receipt_options['served_by_type'], 'nickname', true); ?> >
                                                                <?php _e('Nickname', 'wc_point_of_sale'); ?>
                                                            </option>
                                                        </select>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="postbox ">
                                        <button type="button" class="handlediv button-link" aria-expanded="true">
                                            <span class="screen-reader-text"><?php _e('Click to toggle'); ?> </span>
                                            <span class="toggle-indicator" aria-hidden="true"></span>
                                        </button>
                                        <h3 class="hndle">
                                            <span><?php _e('Product Details', 'wc_point_of_sale'); ?></span>
                                        </h3>
                                        <div class="inside">
                                            <table id="receipt_options">
                                                <tr>
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="receipt_show_image_product"><?php _e('Product Image', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" id="receipt_show_image_product"
                                                               value="yes"
                                                               name="receipt_show_image_product" <?php echo ($receipt_options['show_image_product'] == 'yes') ? 'checked="checked"' : ''; ?>>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="show_sku"><?php _e('SKU', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" id="show_sku" value="yes"
                                                               name="show_sku" <?php echo ($receipt_options['show_sku'] == 'yes') ? 'checked="checked"' : ''; ?>>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <label class="show_cost"
                                                               for="show_cost"><?php _e('Product Cost', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" id="show_cost" value="yes"
                                                               name="show_cost" <?php echo ($receipt_options['show_cost'] == 'yes') ? 'checked="checked"' : ''; ?>>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="receipt_tax_label"><?php _e('Tax Label', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <input type="text" id="receipt_tax_label"
                                                               name="receipt_tax_label"
                                                               value="<?php echo $receipt_options['tax_label']; ?>">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="receipt_total_label"><?php _e('Total Label', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <input type="text" id="receipt_total_label"
                                                               name="receipt_total_label"
                                                               value="<?php echo $receipt_options['total_label']; ?>">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="receipt_payment_label"><?php _e('Payment Label', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <input type="text" id="receipt_payment_label"
                                                               name="receipt_payment_label"
                                                               value="<?php echo $receipt_options['payment_label']; ?>">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="receipt_print_number_items"><?php _e('Number of Items', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" id="receipt_print_number_items"
                                                               value="yes"
                                                               name="receipt_print_number_items" <?php echo ($receipt_options['print_number_items'] == 'yes') ? 'checked="checked"' : ''; ?>>
                                                    </td>
                                                </tr>
                                                <tr class="show_receipt_print_number_items">
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="receipt_items_label"><?php _e('Number of Items Label', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <input type="text" id="receipt_items_label"
                                                               name="receipt_items_label"
                                                               value="<?php echo $receipt_options['items_label']; ?>">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="receipt_print_order_notes"><?php _e('Order Notes', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" id="receipt_print_order_notes"
                                                               value="yes"
                                                               name="receipt_print_order_notes" <?php echo ($receipt_options['print_order_notes'] == 'yes') ? 'checked="checked"' : ''; ?>>
                                                    </td>
                                                </tr>
                                                <tr class="show_receipt_print_order_notes">
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="receipt_order_notes_label"><?php _e('Order Notes Label', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <input type="text" id="receipt_order_notes_label"
                                                               name="receipt_order_notes_label"
                                                               value="<?php echo $receipt_options['order_notes_label']; ?>">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="receipt_print_barcode"><?php _e('Order Barcode', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" id="receipt_print_barcode" value="yes"
                                                               name="receipt_print_barcode" <?php echo ($receipt_options['print_barcode'] == 'yes') ? 'checked="checked"' : ''; ?>>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="postbox ">
                                        <button type="button" class="handlediv button-link" aria-expanded="true">
                                            <span class="screen-reader-text"><?php _e('Click to toggle'); ?> </span>
                                            <span class="toggle-indicator" aria-hidden="true"></span>
                                        </button>
                                        <h3 class="hndle">
                                            <span><?php _e('Social', 'wc_point_of_sale'); ?></span>
                                        </h3>
                                        <div class="inside">
                                            <table id="receipt_options">
                                                <tr>
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="socials_display_option"><?php _e('Display social', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <select id="socials_display_option"
                                                                name="socials_display_option">
                                                            <option value="none" <?php echo ($receipt_options['socials_display_option'] == 'none') ? 'selected' : ''; ?>><?php _e('None', 'wc_point_of_sale') ?></option>
                                                            <option value="footer" <?php echo ($receipt_options['socials_display_option'] == 'footer') ? 'selected' : ''; ?>><?php _e('Footer', 'wc_point_of_sale') ?></option>
                                                            <option value="header" <?php echo ($receipt_options['socials_display_option'] == 'header') ? 'selected' : ''; ?>><?php _e('Header', 'wc_point_of_sale') ?></option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr class="social">
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="show_twitter"><?php _e('Show Twitter', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" id="show_twitter" value="yes"
                                                               name="show_twitter" <?php echo ($receipt_options['show_twitter'] == 'yes') ? 'checked="checked"' : ''; ?>>
                                                    </td>
                                                </tr>
                                                <tr class="social">
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="show_facebook"><?php _e('Show Facebook', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" id="show_facebook" value="yes"
                                                               name="show_facebook" <?php echo ($receipt_options['show_facebook'] == 'yes') ? 'checked="checked"' : ''; ?>>
                                                    </td>
                                                </tr>
                                                <tr class="social">
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="show_instagram"><?php _e('Show Instagram', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" id="show_instagram" value="yes"
                                                               name="show_instagram" <?php echo ($receipt_options['show_instagram'] == 'yes') ? 'checked="checked"' : ''; ?>>
                                                    </td>
                                                </tr>
                                                <tr class="social">
                                                    <td>
                                                        <label class="receipt_labels"
                                                               for="show_snapchat"><?php _e('Show Snapchat', 'wc_point_of_sale'); ?></label>
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" id="show_snapchat" value="yes"
                                                               name="show_snapchat" <?php echo ($receipt_options['show_snapchat'] == 'yes') ? 'checked="checked"' : ''; ?>>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="postbox ">
                                        <button type="button" class="handlediv button-link" aria-expanded="true">
                                            <span class="screen-reader-text"><?php _e('Click to toggle'); ?> </span>
                                            <span class="toggle-indicator" aria-hidden="true"></span>
                                        </button>
                                        <h3 class="hndle">
                                            <label class="receipt_labels"
                                                   for="receipt_header_text"><?php _e('Header Text', 'wc_point_of_sale'); ?></label>
                                        </h3>
                                        <div class="inside">
                                            <div class="postarea edit-form-section">
                                                <?php wp_editor(stripslashes($receipt_options['header_text']), 'receipt_header_text', array(
                                                    'dfw' => false,
                                                    'editor_height' => 200,
                                                    'media_buttons' => false,
                                                    'textarea_name' => 'receipt_header_text',
                                                    'tinymce' => array(
                                                        'resize' => false,
                                                        'add_unload_trigger' => false,
                                                    ),
                                                )); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="postbox ">
                                        <button type="button" class="handlediv button-link" aria-expanded="true">
                                            <span class="screen-reader-text"><?php _e('Click to toggle'); ?> </span>
                                            <span class="toggle-indicator" aria-hidden="true"></span>
                                        </button>
                                        <h3 class="hndle">
                                            <label class="receipt_labels"
                                                   for="receipt_footer_text"><?php _e('Footer Text', 'wc_point_of_sale'); ?></label>
                                        </h3>
                                        <div class="inside">
                                            <div class="postarea edit-form-section">
                                                <?php wp_editor(stripslashes($receipt_options['footer_text']), 'receipt_footer_text', array(
                                                    'dfw' => false,
                                                    'editor_height' => 200,
                                                    'media_buttons' => false,
                                                    'textarea_name' => 'receipt_footer_text',
                                                    'tinymce' => array(
                                                        'resize' => false,
                                                        'add_unload_trigger' => false,
                                                    ),
                                                )); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="postbox pos_receipt_custom_css">
                                        <button type="button" class="handlediv button-link" aria-expanded="true">
                                            <span class="screen-reader-text"><?php _e('Click to toggle'); ?> </span>
                                            <span class="toggle-indicator" aria-hidden="true"></span>
                                        </button>
                                        <h2 class="hndle">
                                            <label class="receipt_labels"
                                                   for="receipt_custom_css"><?php _e('CSS', 'wc_point_of_sale'); ?></label>
                                        </h2>
                                        <div class="inside">
                                            <p class="description">
                                                <?php _e('Customise the look and feel of your receipts using custom CSS. This will only applied when the receipt is being printed.', 'wc_point_of_sale'); ?>
                                            </p>
                                            <textarea name="receipt_custom_css"
                                                      id="receipt_custom_css"><?php echo $receipt_options['custom_css']; ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- /postbox-container-2 -->
                            <div id="postbox-container-1" class="postbox-container">
                                <div class="meta-box-sortables">
                                    <div class="postbox ">
                                        <div class="inside" id="print-receipt-preview-display">
                                            <?php
                                            require_once(WC_POS()->plugin_path() . '/includes/views/html-print-receipt-preview.php');
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- /postbox-container-1 -->
                        </div>
                    </div>

                </form>
            </div>
            <?php
        }

        function display_messages()
        {
            $i = 0;
            if (isset($_GET['message']) && !empty($_GET['message'])) $i = $_GET['message'];
            $messages = array(
                0 => '', // Unused. Messages start at index 1.
                1 => '<div id="message" class="updated"><p>' . __('Receipt template created.') . '</p></div>',
                2 => '<div id="message" class="updated"><p>' . __('Receipt template updated.') . '</p></div>',
                3 => '<div id="message" class="updated"><p>' . __('Receipt template deleted.') . '</p></div>',
            );
            return $messages[$i];
        }

        public function save_receipt()
        {
            global $wpdb;
            check_admin_referer('wc_point_of_sale_edit_receipt');
            $new = false;

            if (!empty($_POST['receipt_order_date_format']) && isset($_POST['receipt_order_date_format_custom']) && '\c\u\s\t\o\m' == wp_unslash($_POST['receipt_order_date_format']))
                $_POST['receipt_order_date_format'] = $_POST['receipt_order_date_format_custom'];

            $data = array(
                'name' => isset($_POST['receipt_name']) ? $_POST['receipt_name'] : '',
                'print_outlet_address' => isset($_POST['receipt_print_outlet_address']) ? $_POST['receipt_print_outlet_address'] : '',
                'print_outlet_contact_details' => isset($_POST['receipt_print_outlet_contact_details']) ? $_POST['receipt_print_outlet_contact_details'] : '',
                'telephone_label' => isset($_POST['receipt_telephone_label']) ? $_POST['receipt_telephone_label'] : '',
                'fax_label' => isset($_POST['receipt_fax_label']) ? $_POST['receipt_fax_label'] : '',
                'email_label' => isset($_POST['receipt_email_label']) ? $_POST['receipt_email_label'] : '',
                'website_label' => isset($_POST['receipt_website_label']) ? $_POST['receipt_website_label'] : '',
                'receipt_title' => isset($_POST['receipt_receipt_title']) ? $_POST['receipt_receipt_title'] : '',
                'order_number_label' => isset($_POST['receipt_order_number_label']) ? $_POST['receipt_order_number_label'] : '',
                'order_date_label' => isset($_POST['receipt_order_date_label']) ? $_POST['receipt_order_date_label'] : '',
                'order_date_format' => isset($_POST['receipt_order_date_format']) ? $_POST['receipt_order_date_format'] : get_option('date_format'),
                'print_order_time' => isset($_POST['receipt_print_order_time']) ? $_POST['receipt_print_order_time'] : '',
                'print_server' => isset($_POST['receipt_print_server']) ? $_POST['receipt_print_server'] : '',
                'served_by_label' => isset($_POST['receipt_served_by_label']) ? $_POST['receipt_served_by_label'] : '',
                'served_by_type' => isset($_POST['receipt_served_by_type']) ? $_POST['receipt_served_by_type'] : 'username',
                'tax_label' => isset($_POST['receipt_tax_label']) ? $_POST['receipt_tax_label'] : '',
                'total_label' => isset($_POST['receipt_total_label']) ? $_POST['receipt_total_label'] : '',
                'payment_label' => isset($_POST['receipt_payment_label']) ? $_POST['receipt_payment_label'] : '',
                'print_number_items' => isset($_POST['receipt_print_number_items']) ? $_POST['receipt_print_number_items'] : '',
                'items_label' => isset($_POST['receipt_items_label']) ? $_POST['receipt_items_label'] : '',
                'print_barcode' => isset($_POST['receipt_print_barcode']) ? $_POST['receipt_print_barcode'] : '',
                'show_image_product' => isset($_POST['receipt_show_image_product']) ? $_POST['receipt_show_image_product'] : '',
                'print_tax_number' => isset($_POST['receipt_print_tax_number']) ? $_POST['receipt_print_tax_number'] : '',
                'tax_number_label' => isset($_POST['receipt_tax_number_label']) ? $_POST['receipt_tax_number_label'] : '',
                'header_text' => isset($_POST['receipt_header_text']) ? $_POST['receipt_header_text'] : '',
                'footer_text' => isset($_POST['receipt_footer_text']) ? $_POST['receipt_footer_text'] : '',
                'logo' => isset($_POST['receipt_logo']) ? $_POST['receipt_logo'] : '',
                'text_size' => isset($_POST['text_size']) ? $_POST['text_size'] : '',
                'title_position' => isset($_POST['title_position']) ? $_POST['title_position'] : '',
                'logo_size' => isset($_POST['logo_size']) ? $_POST['logo_size'] : '',
                'logo_position' => isset($_POST['logo_position']) ? $_POST['logo_position'] : '',
                'contact_position' => isset($_POST['contact_position']) ? $_POST['contact_position'] : '',
                'tax_number_position' => isset($_POST['tax_number_position']) ? $_POST['tax_number_position'] : '',
                'print_order_notes' => isset($_POST['receipt_print_order_notes']) ? $_POST['receipt_print_order_notes'] : '',
                'order_notes_label' => isset($_POST['receipt_order_notes_label']) ? $_POST['receipt_order_notes_label'] : '',

                'print_customer_name' => isset($_POST['receipt_print_customer_name']) ? $_POST['receipt_print_customer_name'] : '',
                'customer_name_label' => isset($_POST['receipt_customer_name_label']) ? $_POST['receipt_customer_name_label'] : '',
                'print_customer_email' => isset($_POST['receipt_print_customer_email']) ? $_POST['receipt_print_customer_email'] : '',
                'customer_email_label' => isset($_POST['receipt_customer_email_label']) ? $_POST['receipt_customer_email_label'] : '',
                'print_customer_phone' => isset($_POST['receipt_print_customer_phone']) ? $_POST['receipt_print_customer_phone'] : '',
                'customer_phone_label' => isset($_POST['receipt_customer_phone_label']) ? $_POST['receipt_customer_phone_label'] : '',
                'print_customer_ship_address' => isset($_POST['receipt_print_customer_ship_address']) ? $_POST['receipt_print_customer_ship_address'] : '',
                'customer_ship_address_label' => isset($_POST['receipt_customer_ship_address_label']) ? $_POST['receipt_customer_ship_address_label'] : '',
                'custom_css' => isset($_POST['receipt_custom_css']) ? $_POST['receipt_custom_css'] : '',
                'show_sku' => isset($_POST['show_sku']) ? $_POST['show_sku'] : '',
                'show_cost' => isset($_POST['show_cost']) ? $_POST['show_cost'] : '',
                'show_outlet' => isset($_POST['show_outlet']) ? $_POST['show_outlet'] : '',
                'show_register' => isset($_POST['show_register']) ? $_POST['show_register'] : '',
                'show_site_name' => isset($_POST['show_site_name']) ? $_POST['show_site_name'] : '',
                'gift_receipt_title' => isset($_POST['gift_receipt_title']) ? $_POST['gift_receipt_title'] : '',
                'print_copies_count' => isset($_POST['print_copies_count']) ? $_POST['print_copies_count'] : '1',
                'tax_summary' => isset($_POST['tax_summary']) ? $_POST['tax_summary'] : '',
                'receipt_width' => isset($_POST['receipt_width']) ? $_POST['receipt_width'] : '0',
                'show_twitter' => isset($_POST['show_twitter']) ? $_POST['show_twitter'] : 'no',
                'show_facebook' => isset($_POST['show_facebook']) ? $_POST['show_facebook'] : 'no',
                'show_instagram' => isset($_POST['show_instagram']) ? $_POST['show_instagram'] : 'no',
                'show_snapchat' => isset($_POST['show_snapchat']) ? $_POST['show_snapchat'] : 'no',
                'socials_display_option' => isset($_POST['socials_display_option']) ? $_POST['socials_display_option'] : 'none',
            );
            $table_name = $wpdb->prefix . "wc_poin_of_sale_receipts";
            if (isset($_POST['receipt_ID']) && !empty($_POST['receipt_ID'])) {
                $rows_affected = $wpdb->update($table_name, $data, array('ID' => $_POST['receipt_ID']));
                return wp_redirect(add_query_arg(array("page" => WC_POS()->id_receipts, "action" => 'edit', 'id' => $_POST['receipt_ID'], "message" => 2), 'admin.php'));
            } else {
                $rows_affected = $wpdb->insert($table_name, $data);
                return wp_redirect(add_query_arg(array("page" => WC_POS()->id_receipts, "action" => 'edit', 'id' => $wpdb->insert_id, "message" => 1), 'admin.php'));
            }
        }

        function display_receipt_table()
        {
            ?>
            <div class="wrap">
                <h2><?php
                    _e('Receipt Templates', 'wc_point_of_sale');
                    echo ' <a href="' . esc_url(admin_url('admin.php?page=' . WC_POS()->id_receipts . '&action=add')) . '" class="add-new-h2">' . __('Add New', 'wc_point_of_sale') . '</a>';
                    if (!empty($_REQUEST['s']))
                        printf(' <span class="subtitle">' . __('Search results for &#8220;%s&#8221;') . '</span>', $_REQUEST['s']);
                    ?>
                </h2>
                <?php echo $this->display_messages(); ?>
                <div id="lost-connection-notice" class="error hidden">
                    <p>
                        <span class="spinner"></span> <?php _e('<strong>Connection lost.</strong> Saving has been disabled until you&#8217;re reconnected.'); ?>
                        <span class="hide-if-no-sessionstorage"><?php _e('We&#8217;re backing up this post in your browser, just in case.'); ?></span>
                    </p>
                </div>
                <?php
                $receipts_table = WC_POS()->receipts_table();
                $receipts_table->views();
                ?>
                <form action="" method="post" id="edit_wc_pos_receipt">
                    <?php
                    $receipts_table->search_box('Search', 'wc_pos_receipts_is_search');
                    $receipts_table->prepare_items();
                    $receipts_table->display();
                    ?>
                </form>
            </div>
            <?php
        }

        function delete_receipt($ids = 0)
        {
            global $wpdb;
            $filter = '';
            if ($ids)
                $ids = wc_pos_check_can_delete('receipt', $ids);

            if ($ids) {
                if (is_array($ids)) {
                    $ids = implode(',', array_map('intval', $ids));
                    $filter .= "WHERE ID IN ($ids)";
                } else {
                    $filter .= "WHERE ID = $ids";
                }
                $table_name = $wpdb->prefix . "wc_poin_of_sale_receipts";
                $query = "DELETE FROM $table_name $filter";
                if ($wpdb->query($query)) {
                    return wp_redirect(add_query_arg(array("page" => WC_POS()->id_receipts, "message" => 3), 'admin.php'));
                }
            }
            return wp_redirect(add_query_arg(array("page" => WC_POS()->id_receipts), 'admin.php'));
        }

        public function get_style_templates()
        {
            $elems = 'body.pos_receipt, table.order-info, table.customer-info, table.receipt_items, #pos_receipt_title, #pos_receipt_address, #pos_receipt_contact, #pos_receipt_header, #pos_receipt_header p, #pos_receipt_footer, #pos_receipt_footer p, #pos_receipt_tax, #pos_receipt_info, #pos_receipt_items, #receipt_print_tax_summary, #pos_receipt_tax_breakdown, table.tax_breakdown';
            $templates = array(
                'text_size' => array(
                    'normal' => $elems . '{font-size: 14px;}',
                    'small' => $elems . '{font-size: 12px;}',
                    'large' => $elems . '{font-size: 16px;}',
                ),
                'title_position' => array(
                    'left' => '#pos_receipt_title {text-align: left;}',
                    'center' => '#pos_receipt_title {text-align: center;}',
                    'right' => '#pos_receipt_title {text-align: right;}',
                ),
                'logo_size' => array(
                    'normal' => '#print_receipt_logo {height: 50px;}',
                    'small' => '#print_receipt_logo {height: 25px;}',
                    'large' => '#print_receipt_logo {height: 100px;}',
                ),
                'logo_position' => array(
                    'left' => '#pos_receipt_logo {text-align: left;}',
                    'center' => '#pos_receipt_logo {text-align: center;}',
                    'right' => '#pos_receipt_logo {text-align: right;}',
                ),
                'contact_position' => array(
                    'left' => '#pos_receipt_address, #pos_receipt_contact, .display-socials {text-align: left;}',
                    'center' => '#pos_receipt_address, #pos_receipt_contact, .display-socials {text-align: center;}',
                    'right' => '#pos_receipt_address, #pos_receipt_contact, .display-socials {text-align: right;}',
                ),
                'tax_number_position' => array(
                    'left' => '#pos_receipt_tax {text-align: left;}',
                    'center' => '#pos_receipt_tax {text-align: center;}',
                    'right' => '#pos_receipt_tax {text-align: right;}',
                ),
            );

            return apply_filters('pos_receipt_style_templates', $templates);
        }


    }

endif;