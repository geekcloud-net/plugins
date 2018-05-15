<?php
/**
 * WoocommercePointOfSale Outlets Class
 *
 * @author    Actuality Extensions
 * @package   WoocommercePointOfSale/Classes/Outlets
 * @category    Class
 * @since     0.1
 */


if (!defined('ABSPATH')) exit; // Exit if accessed directly


class WC_Pos_Outlets
{

    public $outlet_address_fields = array();
    public $outlet_contact_fields = array();
    public $outlet_social_fields = array();

    /**
     * @var WC_Pos_Outlets The single instance of the class
     * @since 1.9
     */
    protected static $_instance = null;

    /**
     * Main WC_Pos_Outlets Instance
     *
     * Ensures only one instance of WC_Pos_Outlets is loaded or can be loaded.
     *
     * @since 1.9
     * @static
     * @return WC_Pos_Outlets Main instance
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

    /**
     * Init address fields we display + save
     */
    public function init_form_fields($country = '')
    {
        $countries = new WC_Countries();
        $filds = $countries->get_address_fields($country, '');
        unset($filds['first_name']);
        unset($filds['last_name']);
        unset($filds['company']);
        $c[''] = __('Select a country…', 'wc_point_of_sale');
        $filds['country']['options'] = array_merge($c, $countries->get_allowed_countries());
        $filds['country']['type'] = 'select';

        if ($country != '') {
            $filds['country']['value'] = $country;
            $state[''] = __('Select a state…', 'wc_point_of_sale');
            $states = array_merge($state, $countries->get_allowed_country_states());
            if (!empty($states[$country])) {
                $filds['state']['options'] = $states[$country];
                $filds['state']['type'] = 'select';
            }
        }

        $this->outlet_address_fields = $filds;

        $this->outlet_contact_fields = array(
            'email' => array(
                'label' => __('Email', 'woocommerce'),
                'url' => '<a href="mailto:%s" target="_blank">%s</a>',
            ),
            'phone' => array(
                'label' => __('Phone', 'woocommerce'),
            ),
            'fax' => array(
                'label' => __('Fax', 'woocommerce'),
            ),
            'website' => array(
                'label' => __('Website', 'woocommerce'),
                'url' => '<a href="http://%s" target="_blank">%s</a>',
            ),
        );
        $this->outlet_social_fields = array(
            'twitter' => array(
                'label' => __('Twitter', 'wc_point_of_sale'),
                'url' => '<a href="http://twitter.com/%s" target="_blank">%s</a>',
                'description' => __('The Twitter name of the outlet e.g. for twitter.com/acme enter just "acme".', 'wc_point_of_sale'),
            ),
            'facebook' => array(
                'label' => __('Facebook', 'wc_point_of_sale'),
                'url' => '<a href="http://www.facebook.com/%s" target="_blank">%s</a>',
                'description' => __('The Facebook page of the outlet e.g. for facebook.com/acme enter just "acme".', 'wc_point_of_sale'),
            ),
            'instagram' => array(
                'label' => __('Instagram', 'wc_point_of_sale'),
                'url' => '<a href="http://instagram.com/%s" target="_blank">#%s</a>',
                'description' => __('The Instagram page of the outlet e.g. for instagram.com/acme enter just "acme".', 'wc_point_of_sale'),
            ),
            'snapchat' => array(
                'label' => __('Snapchat', 'wc_point_of_sale'),
                'url' => '<a href="http:///snapchat.com/%s" target="_blank">%s</a>',
                'description' => __('The Snapchat account of the outlet e.g. for snapchat.com/acme enter just "acme".', 'wc_point_of_sale'),
            ),
        );
    }

    public function display()
    {
        $this->init_form_fields();
        ?>
        <div class="wrap nosubsub" id="wc-pos-outlets">
            <h2>
                <?php echo get_admin_page_title(); ?>
                <?php if (isset($_GET['s']) && !empty($_GET['s'])) { ?>
                    <span class="subtitle">Search results for “<?php echo $_GET['s']; ?>”</span>
                <?php } ?>
            </h2>

            <?php if (isset($_GET['message']) && !empty($_GET['message'])) {
                $message = self::get_message($_GET['message']);
                if (!empty($message)) {
                    ?>
                    <div class="<?php echo $message['class']; ?> below-h2" id="message">
                        <p><?php echo $message['text']; ?></p></div>
                <?php }
            } ?>
            <div id="ajax-response"></div>
            <form method="get" action="" class="search-form">
                <p class="search-box">
                    <label for="outlet-search-input"
                           class="screen-reader-text"><?php _e('Search Outlets', 'wc_point_of_sale'); ?></label>
                    <input type="hidden" value="wc_pos_outlets" name="page">
                    <input type="search" value="" name="s" id="outlet-search-input">
                    <input type="submit" value="<?php _e('Search Outlets', 'wc_point_of_sale'); ?>" class="button"
                           id="search-submit" name="">
                </p>

            </form>
            <br class="clear">
            <div id="col-container">
                <div id="col-right" class="outlet_table">
                    <?php self::display_outlet_table(); ?>
                </div> <!-- /col-right -->
                <div id="col-left">
                    <?php self::display_outlet_form();
                    ?>
                </div> <!-- /col-left -->
            </div>
        </div>
        <?php
    }

    public function display_edit_form($id = 0)
    {
        $data = array();
        $ajax = false;
        if ($id) {
            $data = $this->get_data($id);
            $data = $data[0];
            foreach ($data['contact'] as $i => $val) {
                $data[$i] = $val;
            }
            foreach ($data['social'] as $i => $val) {
                $data[$i] = $val;
            }
            $this->init_form_fields($data['country']);
        }
        if (isset($_POST['action']) && $_POST['action'] == 'wc_pos_edit_update_outlets_address' && isset($_POST['country'])) {
            $ajax = true;
            foreach ($_POST as $key => $value) {
                $data[$key] = $value;
            }
            if ($data['country'] != '')
                $this->init_form_fields($data['country']);
            else
                $this->init_form_fields();
        }

        ?>
        <?php if (!$ajax) : ?>
        <div class="wrap" id="wc-pos-outlets-edit">
        <h2><?php _e('Edit Outlet', 'wc_point_of_sale'); ?></h2>
        <div id="ajax-response"></div>
        <form id="edit_wc_pos_outlets" class="validate" action="" method="post">
    <?php endif; ?>
        <input type="hidden" value="edit-wc-pos-outlets" name="action">
        <input type="hidden" value="<?php echo $data['ID']; ?>" name="id" id="id_outlet">
        <?php wp_nonce_field('nonce-edit-wc-pos-outlets', '_wpnonce_edit-wc-pos-outlets'); ?>
        <table class="form-table">
            <tbody>
            <tr class="form-field form-required">
                <th valign="top" scope="row">
                    <label for="_outlet_name"><?php _e('Name', 'wc_point_of_sale'); ?></label>
                </th>
                <td>
                    <?php if ($data['name']) { ?>
                        <input type="text" aria-required="true" size="40" id="_outlet_name" name="_outlet_name"
                               value="<?php echo $data['name']; ?>" required>
                    <?php } else { ?>
                        <input type="text" aria-required="true" size="40" id="_outlet_name" name="_outlet_name"
                               required>
                    <?php } ?>
                    <p class="description"><?php _e('The name of the outlet as it appears when opening a register.', 'wc_point_of_sale'); ?>
                    <p>
                </td>
            </tr>
            <?php
            foreach ($this->outlet_address_fields as $key => $field) {
                if (!isset($field['type']))
                    $field['type'] = 'text';
                $value = "";
                if (isset($field['value'])) {
                    $value = $field['value'];
                } else {
                    $value = $data[$key];
                }

                switch ($field['type']) {
                    case "select" :
                        wc_pos_select(array(
                            'id' => '_outlet_' . $key,
                            'label' => isset($field['label']) ? $field['label'] : '',
                            'options' => $field['options'],
                            'value' => $value,
                            'description' => isset($field['description']) ? $field['description'] : '',
                            'wrapper_tag' => 'tr',
                            'wrapper_label_tag' => '<th valign="top" scope="row">%s</th>',
                            'wrapper_field_tag' => '<td>%s</td>'));
                        break;
                    default :
                        wc_pos_text_input(array(
                            'id' => '_outlet_' . $key,
                            'label' => isset($field['label']) ? $field['label'] : '',
                            'value' => $value,
                            'description' => isset($field['description']) ? $field['description'] : '',
                            'wrapper_tag' => 'tr',
                            'wrapper_label_tag' => '<th valign="top" scope="row">%s</th>',
                            'wrapper_field_tag' => '<td>%s</td>'));
                        break;
                }
            }
            ?>
            <tr class="form-field">
                <th colspan="2">
                    <h3><?php _e('Contact Details', 'wc_point_of_sale'); ?></h3>
                    <p class="description"><?php echo _e('Enter the contact details of the outlet as this will appear on receipts that are printed from registers at this outlet.', 'wc_point_of_sale'); ?></p>
                </th>
            </tr>
            <?php
            foreach ($this->outlet_contact_fields as $key => $field) {
                if (!isset($field['type']))
                    $field['type'] = 'text';
                $value = "";
                if ($data[$key]) {
                    $value = $data[$key];
                }
                switch ($field['type']) {
                    case "select" :
                        wc_pos_select(array(
                            'id' => '_outlet_' . $key,
                            'label' => isset($field['label']) ? $field['label'] : '',
                            'options' => $field['options'],
                            'value' => $value,
                            'description' => isset($field['description']) ? $field['description'] : '',
                            'wrapper_tag' => 'tr',
                            'wrapper_label_tag' => '<th valign="top" scope="row">%s</th>',
                            'wrapper_field_tag' => '<td>%s</td>'));
                        break;
                    default :
                        wc_pos_text_input(array(
                            'id' => '_outlet_' . $key,
                            'label' => isset($field['label']) ? $field['label'] : '',
                            'value' => $value,
                            'description' => isset($field['description']) ? $field['description'] : '',
                            'wrapper_tag' => 'tr',
                            'wrapper_label_tag' => '<th valign="top" scope="row">%s</th>',
                            'wrapper_field_tag' => '<td>%s</td>'));
                        break;
                }
            }
            ?>
            <tr class="form-field">
                <th colspan="2">
                    <h3><?php _e('Social Details', 'wc_point_of_sale'); ?></h3>
                    <p class="description"><?php echo _e('Enter the social details of the outlet as this will appear on receipts that are printed from registers at this outlet.', 'wc_point_of_sale'); ?></p>
                </th>
            </tr>
            <?php
            foreach ($this->outlet_social_fields as $key => $field) {
                if (!isset($field['type']))
                    $field['type'] = 'text';
                $value = "";
                if ($data[$key]) {
                    $value = $data[$key];
                }
                if ($key == 'twitter') {
                    $value = '@' . str_replace('@', '', $value);
                }
                switch ($field['type']) {
                    case "select" :
                        wc_pos_select(array('id' => '_outlet_' . $key, 'label' => $field['label'], 'options' => $field['options'], 'value' => $value, 'description' => $field['description'], 'wrapper_tag' => 'tr', 'wrapper_label_tag' => '<th valign="top" scope="row">%s</th>', 'wrapper_field_tag' => '<td>%s</td>'));
                        break;
                    default :
                        wc_pos_text_input(array('id' => '_outlet_' . $key, 'label' => $field['label'], 'value' => $value, 'description' => $field['description'], 'wrapper_tag' => 'tr', 'wrapper_label_tag' => '<th valign="top" scope="row">%s</th>', 'wrapper_field_tag' => '<td>%s</td>'));
                        break;
                }
            }
            ?>
            </tbody>
        </table>
        <p class="submit"><input type="submit" value="<?php _e('Update', 'wc_point_of_sale'); ?>"
                                 class="button button-primary" id="submit" name="submit"></p>
        <?php if (!$ajax) : ?>
        </form>
        </div>
    <?php endif; ?>
        <?php
    }

    public function display_outlet_table()
    {
        ?>
        <div class="col-wrap">
            <form id="wc_pos_outlets_table" action="" method="post">
                <?php
                $outlet_table = WC_POS()->outlet_table();
                $outlet_table->prepare_items();
                $outlet_table->display();
                ?>
            </form>
        </div>
        <?php
    }

    public function display_outlet_form($id = 0)
    {
        $data = array(
            'name' => '',
            'country' => '',
            'address_1' => '',
            'address_2' => '',
            'city' => '',
            'state' => '',
            'postcode' => '',
            'email' => '',
            'phone' => '',
            'fax' => '',
            'website' => '',
            'twitter' => '',
            'facebook' => '',
        );
        $ajax = false;
        if (isset($_POST['action']) && $_POST['action'] == 'wc_pos_new_update_outlets_address' && isset($_POST['country'])) {
            $ajax = true;
            foreach ($_POST as $key => $value) {
                $data[$key] = $value;
            }
            if ($data['country'] != '')
                $this->init_form_fields($data['country']);
            else
                $this->init_form_fields();
        }

        ?>
        <?php if (!$ajax) : ?>
        <div class="col-wrap">
        <p><?php echo _e('Outlets for your store can be managed here. Outlets can be added using the form below. To edit an outlet, simply hover over the outlet and click on Edit.', 'wc_point_of_sale'); ?></p>
        <div class="form-wrap">
        <h3><?php _e('Outlet Details', 'wc_point_of_sale'); ?></h3>
        <form id="add_wc_pos_outlets" class="validate" action="" method="post">
    <?php endif; ?>
        <input type="hidden" value="add-wc-pos-outlets" name="action">
        <?php wp_nonce_field('nonce-add-wc-pos-outlets', '_wpnonce_add-wc-pos-outlets'); ?>

        <div class="form-field form-required">
            <label for="_outlet_name"><?php _e('Name', 'wc_point_of_sale'); ?></label>
            <?php if ($data['name']) { ?>
                <input type="text" aria-required="true" size="40" id="_outlet_name" name="_outlet_name"
                       value="<?php echo $data['name']; ?>">
            <?php } else { ?>
                <input type="text" aria-required="true" size="40" id="_outlet_name" name="_outlet_name">
            <?php } ?>
            <p><?php echo _e('The name of the outlet as it appears when opening a register.', 'wc_point_of_sale'); ?></p>
        </div>
        <?php
        foreach ($this->outlet_address_fields as $key => $field) {
            if (!isset($field['type']))
                $field['type'] = 'text';
            $value = "";
            if (isset($field['value'])) {
                $value = $field['value'];
            } else {
                $value = $data[$key];
            }

            switch ($field['type']) {
                case "select" :
                    wc_pos_select(array('id' => '_outlet_' . $key, 'label' => isset($field['label']) ? $field['label'] : '', 'options' => isset($field['options']) ? $field['options'] : array(), 'value' => $value));
                    break;
                default :
                    wc_pos_text_input(array('id' => '_outlet_' . $key, 'label' => isset($field['label']) ? $field['label'] : '', 'value' => $value, 'description' => isset($field['description']) ? $field['description'] : ''));
                    break;
            }
        }
        ?>
        <br class="clear">
        <p><?php echo _e('The address of the outlet at which the register will be located.', 'wc_point_of_sale'); ?></p>
        <br class="clear">
        <h3><?php _e('Contact Details', 'wc_point_of_sale'); ?></h3>
        <p><?php echo _e('Enter the contact details of the outlet as this will appear on receipts that are printed from registers at this outlet.', 'wc_point_of_sale'); ?></p>
        <?php
        foreach ($this->outlet_contact_fields as $key => $field) {
            if (!isset($field['type']))
                $field['type'] = 'text';
            $value = "";
            if ($data[$key]) {
                $value = $data[$key];
            }
            switch ($field['type']) {
                case "select" :
                    wc_pos_select(array('id' => '_outlet_' . $key, 'label' => isset($field['label']) ? $field['label'] : '', 'options' => isset($field['options']) ? $field['options'] : array(), 'value' => $value));
                    break;
                default :
                    wc_pos_text_input(array('id' => '_outlet_' . $key, 'label' => isset($field['label']) ? $field['label'] : '', 'value' => $value, 'description' => isset($field['description']) ? $field['description'] : ''));
                    break;
            }
        }
        ?>
        <br class="clear">
        <h3><?php _e('Social Details', 'wc_point_of_sale'); ?></h3>
        <p><?php echo _e('Enter the social details of the outlet as this will appear on receipts that are printed from registers at this outlet.', 'wc_point_of_sale'); ?></p>
        <?php
        foreach ($this->outlet_social_fields as $key => $field) {
            if (!isset($field['type']))
                $field['type'] = 'text';
            $value = "";
            if ($data[$key]) {
                $value = $data[$key];
            }
            if ($key == 'twitter') {
                $value = '@' . str_replace('@', '', $value);
            }
            switch ($field['type']) {
                case "select" :
                    wc_pos_select(array('id' => '_outlet_' . $key, 'label' => $field['label'], 'options' => $field['options'], 'value' => $value));
                    break;
                default :
                    wc_pos_text_input(array('id' => '_outlet_' . $key, 'label' => $field['label'], 'value' => $value, 'description' => $field['description']));
                    break;
            }
        }
        ?>
        <p class="submit"><input type="submit" value="<?php _e('Add New Outlet', 'wc_point_of_sale'); ?>"
                                 class="button button-primary" id="submit" name="submit"></p>
        <?php if (!$ajax) : ?>
        </form>
        </div>
        </div>
    <?php endif; ?>
        <?php
    }

    public function save_outlet($redirect = true)
    {
        global $wpdb;
        $id = 0;
        if (isset($_POST['id']) && $_POST['id'] != '') $id = $_POST['id'];
        $this->init_form_fields();
        $contact = array();
        $social = array();
        $data = array();
        foreach ($this->outlet_address_fields as $key => $value) {
            $contact[$key] = stripslashes($_POST['_outlet_' . $key]);
        }
        foreach ($this->outlet_contact_fields as $key => $value) {
            $social[$key] = stripslashes($_POST['_outlet_' . $key]);
        }
        foreach ($this->outlet_social_fields as $key => $value) {
            $social[$key] = stripslashes($_POST['_outlet_' . $key]);
        }
        $data['name'] = stripslashes($_POST['_outlet_name']);
        $data['contact'] = json_encode($contact);
        $data['social'] = json_encode($social);
        $table_name = $wpdb->prefix . "wc_poin_of_sale_outlets";
        if ($id) {
            $rows_affected = $wpdb->update($table_name, $data, array('ID' => $id));
            if ($redirect) {
                return wp_redirect(add_query_arg(array("page" => WC_POS()->id_outlets, "message" => 2), 'admin.php'));
            }
        } else {
            $rows_affected = $wpdb->insert($table_name, $data);
            if ($redirect) {
                return wp_redirect(add_query_arg(array("page" => WC_POS()->id_outlets, "message" => 1), 'admin.php'));
            }
        }
    }

    public function delete_outlet($ids = 0)
    {
        global $wpdb;
        if (!$ids) {
            if (isset($_POST['id']) && !empty($_POST['id'])) {
                $ids = $_POST['id'];
            } elseif (isset($_GET['id']) && !empty($_GET['id'])) {
                $ids = $_GET['id'];
            }
        }
        $filter = '';
        if ($ids) {
            $filter = wc_pos_check_can_delete('outlet', $ids);

            if ($filter) {
                $table_name = $wpdb->prefix . "wc_poin_of_sale_outlets";
                $query = "DELETE FROM $table_name $filter";
                if ($wpdb->query($query)) {
                    return wp_redirect(add_query_arg(array("page" => WC_POS()->id_outlets, "message" => 3), 'admin.php'));
                }
            }
        }
        return wp_redirect(add_query_arg(array("page" => WC_POS()->id_outlets), 'admin.php'));
    }

    function get_message($id = 0)
    {
        $message = array();
        switch ($id) {
            case 1:
                $message['class'] = 'updated';
                $message['text'] = __('Outlet added.', 'wc_point_of_sale');
                break;
            case 2:
                $message['class'] = 'updated';
                $message['text'] = __('Outlet updated.', 'wc_point_of_sale');
                break;
            case 3:
                $message['class'] = 'updated';
                $message['text'] = __('Outlet deleted.', 'wc_point_of_sale');
                break;
        }
        return $message;
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
        if (isset($_GET['s']) && !empty($_GET['s']) && isset($_GET['page']) && $_GET['page'] == WC_POS()->id_outlets) {
            $s = $_GET['s'];
            $filter = "WHERE lower( concat(name, contact, social) ) LIKE lower('%$s%')";
        }
        $table_name = $wpdb->prefix . "wc_poin_of_sale_outlets";
        $db_data = $wpdb->get_results("SELECT * FROM $table_name $filter");
        $data = array();

        foreach ($db_data as $value) {
            $value->contact = (array)json_decode($value->contact);
            $value->social = (array)json_decode($value->social);
            $data[] = get_object_vars($value);
        }
        return $data;
    }

    public function get_data_names($outlet_id = 0)
    {
        if (!$outlet_id) {
            $data = self::get_data();
        } else {
            $data = self::get_data($outlet_id);
        }
        $names_list = array();
        foreach ($data as $value) {
            $names_list[$value['ID']] = $value['name'];
        }
        return $names_list;
    }
}