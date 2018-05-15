<?php
/**
 *  WooCommerce Email Customizer with Drag and Drop Email Builder
 * Create awesome transactional emails with a drag and drop email builder
 * @author Flycart Technologies LLP
 * @license GNU GPL V3 or later
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

    global $wp_scripts, $woocommerce, $woocommerce, $current_user, $email_control_template_collection;

    // Load WC Emails
    // ----------------------------------------

    // Load mailer
    if (function_exists('WC')) {
        $wooinst = WC();
        $mailer = $wooinst->mailer();
        $mails = $mailer->get_emails();
    } else {
        $mailer = $woocommerce->mailer();
        $mails = $mailer->get_emails();
    }

    $show_type = isset($_REQUEST["woo_mb_email_type"]) ? sanitize_text_field($_REQUEST["woo_mb_email_type"]) : current($mails)->id;


    // Load WC Orders
    // ----------------------------------------

    $limit_orders = 10;

    $order_collection = new WP_Query(array(
        'post_type' => 'shop_order',
        'post_status' => array_keys(wc_get_order_statuses()),
        'posts_per_page' => $limit_orders,
    ));

    $order_collection = $order_collection->posts;
    $latest_order = (count($order_collection)) ? current($order_collection)->ID : FALSE;

    $show_order = isset($_REQUEST["woo_mb_email_order"]) ? sanitize_text_field($_REQUEST["woo_mb_email_order"]) : $latest_order;

    $src_url = "";
    $src_url .= 'admin.php?';
    $src_url .= 'page=woo_email_customizer_page_builder';
    $src_url .= '&woo_mb_render_email=true';
    $src_url .= '&woo_mb_email_type=' . $show_type;

    if ($show_order) {
        $src_url .= '&woo_mb_email_order=' . $show_order;
    }
    $woo_mb_settings = get_option('woo_mb_settings', '');
    if ($woo_mb_settings != ''){
        $woo_mb_settings = json_decode($woo_mb_settings);
    }

//$poste = get_post(27);
//    echo "<pre>";
//    var_dump($poste->post_content);
//    echo "</pre>";exit;
?>
<style type="text/css">
    <?php echo WooEmailCustomizerCommon::getCSSFromSettings(); ?>
</style>
<!-- Prevent Alertifyjs AutoInject css -->
<div id="alertifyCSS"></div>
<span class="alertify-logs"></span>

<!-- Start App -->
<div id="app">
    <email-builder-component></email-builder-component>
</div>
<script type="x-template" id="email-builder-template">
    <div id="email-builder">
        <div id="email-builder-settings" class="hide">
            <a class="md-btn md-btn-default md-btn-mini" @click="backToWooEmail()" ref="backWooButton" data-url="#" title="<?php esc_html_e('Return back', 'woo-email-customizer-page-builder'); ?>" href="#">
                <i class="material-icons"><?php esc_html_e('arrow_back', 'woo-email-customizer-page-builder'); ?></i>
            </a>
            <div class="email-builder-settings-options">
                <header>
                    <h3><?php esc_html_e('WooCommerce Email Customizer settings', 'woo-email-customizer-page-builder'); ?></h3>
                </header>
                <div class="email-builder-settings-option">
                    <a class="md-btn md-btn-default md-btn-mini" @click="resetDefaultTemplate()" ref="resetDefaultTemplate" data-url="#" title="<?php esc_html_e('Reset all template to default', 'woo-email-customizer-page-builder'); ?>" href="#">
                        <i class="actions material-icons">cached</i>
                    </a>
                    <span class="label"><?php esc_html_e('Reset all template to default', 'woo-email-customizer-page-builder'); ?></span>
                </div>
            </div>
            <br>
            <form name="settings" id="woo-mail-settings" action="#">
                <div class="email-builder-settings-option">
                    <div class="email-builder-settings_fields">
                        <label class="settings-label" for="settings_show_payment_instruction">
                            <?php esc_html_e('Display payment instruction above order table', 'woo-email-customizer-page-builder'); ?>
                        </label>
                        <?php
                        $show_payment_instruction = isset($woo_mb_settings->show_payment_instruction)? $woo_mb_settings->show_payment_instruction: 1;
                        ?>
                        <select name="settings[show_payment_instruction]" id="settings_show_payment_instruction">
                            <option <?php echo ($show_payment_instruction == 1)? 'selected="selected"': ''; ?> value="1"><?php esc_html_e('Yes', 'woo-email-customizer-page-builder'); ?></option>
                            <option <?php echo ($show_payment_instruction == 0)? 'selected="selected"': ''; ?> value="0"><?php esc_html_e('No', 'woo-email-customizer-page-builder'); ?></option>
                            <option <?php echo ($show_payment_instruction == 2)? 'selected="selected"': ''; ?> value="2"><?php esc_html_e('Only for Customer', 'woo-email-customizer-page-builder'); ?></option>
                        </select>
                    </div>
                    <div class="email-builder-settings_fields">
                        <label class="settings-label" for="settings_show_product_image">
                            <?php esc_html_e('Show product image', 'woo-email-customizer-page-builder'); ?>
                        </label>
                        <?php
                        $show_product_image = isset($woo_mb_settings->show_product_image)? $woo_mb_settings->show_product_image: 0;
                        ?>
                        <label><input type="radio" name="settings[show_product_image]" value="1"<?php echo ($show_product_image == 1)? ' checked': ''; ?>><?php esc_html_e('Yes', 'woo-email-customizer-page-builder'); ?></label>&nbsp;
                        <label><input type="radio" name="settings[show_product_image]" value="0"<?php echo ($show_product_image == 0)? ' checked': ''; ?>><?php esc_html_e('No', 'woo-email-customizer-page-builder'); ?></label>
                    </div>
                    <div class="email-builder-settings_fields show_product_image_option">
                        <label class="settings-label" for="settings_product_image_height">
                            <?php esc_html_e('Image height', 'woo-email-customizer-page-builder'); ?>
                        </label>
                        <?php
                        $product_image_height = isset($woo_mb_settings->product_image_height)? $woo_mb_settings->product_image_height: 32;
                        ?>
                        <input type="number" name="settings[product_image_height]" id="settings_product_image_height" value="<?php echo $product_image_height; ?>"/>px
                    </div>
                    <div class="email-builder-settings_fields show_product_image_option">
                        <label class="settings-label" for="settings_product_image_width">
                            <?php esc_html_e('Image width', 'woo-email-customizer-page-builder'); ?>
                        </label>
                        <?php
                        $product_image_width = isset($woo_mb_settings->product_image_width)? $woo_mb_settings->product_image_width: 32;
                        ?>
                        <input type="number" name="settings[product_image_width]" id="settings_product_image_width" value="<?php echo $product_image_width; ?>"/>px
                    </div>
                    <div class="email-builder-settings_fields">
                        <label class="settings-label" for="settings_order_item_table_border_color">
                            <?php esc_html_e('Order Item table border color', 'woo-email-customizer-page-builder'); ?>
                        </label>
                        <?php
                        $order_item_table_border_color = isset($woo_mb_settings->order_item_table_border_color)? $woo_mb_settings->order_item_table_border_color: '#dddddd';
                        ?>
                        <div class="email-builder-settings_fields_input">
                            <input class="color_picker_wooemail" id="settings_order_item_table_border_color" name="settings[order_item_table_border_color]" value="<?php echo $order_item_table_border_color; ?>" data-default-color="#dddddd"/>
                        </div>
                    </div>
                    <div class="email-builder-settings_fields">
                        <label class="settings-label" for="settings_container_width">
                            <?php esc_html_e('Container width', 'woo-email-customizer-page-builder'); ?>
                        </label>
                        <?php
                        $woo_mb_settings_container_width = isset($woo_mb_settings->container_width)? $woo_mb_settings->container_width: '';
                        if($woo_mb_settings_container_width == '') $woo_mb_settings_container_width = 640;
                        ?>
                        <div class="email-builder-settings_fields_input">
                            <input type="text" id="settings_container_width" name="settings[container_width]" value="<?php echo $woo_mb_settings_container_width; ?>"/>px
                        </div>
                    </div>
                    <div class="email-builder-settings_fields">
                        <label class="settings-label" for="settings_custom_css">
                            <?php esc_html_e('Custom CSS', 'woo-email-customizer-page-builder'); ?>
                        </label>
                        <?php
                        $woo_mb_settings_custom_css = isset($woo_mb_settings->custom_css)? $woo_mb_settings->custom_css: '';
                        ?>
                        <div class="email-builder-settings_fields_input">
                            <textarea id="settings_custom_css" name="settings[custom_css]"><?php echo $woo_mb_settings_custom_css; ?></textarea>
                        </div>
                    </div>
                    <div class="email-builder-settings_fields">
                        <label class="settings-label" for="settings_order_url">
                            <?php esc_html_e('Order URL', 'woo-email-customizer-page-builder'); ?>
                        </label>
                        <?php
                        $woo_mb_settings_order_url = isset($woo_mb_settings->order_url)? $woo_mb_settings->order_url: '';
                        ?>
                        <div class="email-builder-settings_fields_input">
                            <input type="text" id="settings_order_url" name="settings[order_url]" value="<?php echo $woo_mb_settings_order_url; ?>"/>
                            <?php esc_html_e('Use the shortcode [woo_mb_order_id] for dynamic id', 'woo-email-customizer-page-builder'); ?>
                        </div>
                    </div>
                    <div class="email-builder-settings_fields">
                        <a class="md-btn md-btn-default md-btn-mini" @click="saveWooEmailCustomizerSettings()" ref="saveWooEmailCustomizerSettings" data-url="#" title="<?php esc_html_e('Save settings', 'woo-email-customizer-page-builder'); ?>" href="#">
                            <i class="actions md-icon material-icons save md-color-green-600">save</i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
        <div class="email-builder-preview-actions" :class="{preview: preview}">
            <div class="md-btn-group">
                <button class="md-btn md-btn-danger" @click="preview = false"><?php esc_html_e('Close preview', 'woo-email-customizer-page-builder'); ?></button>
                <button class="md-btn md-btn-success" v-if="hasChanges()" @click="saveEmailTemplate()"><?php esc_html_e('Save email', 'woo-email-customizer-page-builder'); ?></button>
            </div>
        </div>
        <div class="email-builder-header" :class="{preview: preview}">
            <div class="email-builder-header-actions">
                <a class="md-btn md-btn-default md-btn-mini" @click="settings()" ref="settings" data-url="#" title="<?php esc_html_e('Settings', 'woo-email-customizer-page-builder'); ?>" href="#">
                    <i class="actions material-icons"><?php esc_html_e('settings', 'woo-email-customizer-page-builder'); ?></i>
                </a>
                <?php
                    $backlink = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : admin_url();
                    if (strrpos($backlink, 'login') != -1) $backlink = admin_url();
                ?>
                <a class="md-btn md-btn-default md-btn-mini" @click="backToAdmin()" ref="backButton" data-url="<?php echo esc_url($backlink); ?>" title="<?php esc_html_e('Return back', 'woo-email-customizer-page-builder'); ?>" href="#">
                    <i class="material-icons"><?php esc_html_e('arrow_back', 'woo-email-customizer-page-builder'); ?></i>
                </a>

                <?php 
                    $avail_lang_list = get_available_languages();
                    $lang_select = wp_dropdown_languages( array(
                            'id' => 'woo_mb_email_lang',
                            'name' => 'woo_mb_email_lang',
                            'languages' => $avail_lang_list,
                            'selected' => get_locale(),
                            'echo'      => 0,
                            'show_available_translations' => false
                        )  );

                    $lang_vue_attribs = '<select v-model="emailLang" @change="getEmailTemplate()" ';
                    $lang_select = str_replace( '<select', $lang_vue_attribs, $lang_select ); ?>

                <?php    echo $lang_select;  ?>

                <select v-model="emailType" @change="getEmailTemplate()" title="<?php _e('Choose which email to preview or send.', 'woo-email-customizer-page-builder'); ?>" name="woo_mb_email_type">
                    <option value="">
                        <?php _e("Email to show", 'woo-email-customizer-page-builder'); ?>
                    </option>
                    <?php
                    //Customer_Invoice
                    if (!empty($mails)) {
                        foreach ($mails as $mail) {
                            // "customer_reset_password" is not handling.
//                            if (!in_array($mail->id, array('customer_reset_password'))) {
                            ?>
                                <option value="<?php echo $mail->id ?>" <?php echo ($show_type == $mail->id) ? "selected" : ""; ?> >
                                    <?php echo ucwords($mail->title); ?>
                                </option>
                                <?php
//                            }
                        }
                    }
                    ?>
                </select>

                <select v-model="selectedOrder" @change="getEmailTemplate()" title="<?php _e('Choose which order to use to populate the email template preview.', 'woo-email-customizer-page-builder'); ?>" :disabled="emailType == ''" name="woo_mb_email_order">
                    <?php if (count($order_collection)) { ?>
                        <option value="">
                            <?php _e("Sample order to show", 'woo-email-customizer-page-builder'); ?>
                        </option>
                    <?php } else { ?>
                        <option value="">
                            <?php _e("There are no orders to preview...", 'woo-email-customizer-page-builder'); ?>
                        </option>
                    <?php }

                    // Show the orders.
                    foreach ($order_collection as $order_item) {
                        $order = new WC_Order($order_item->ID);
                        if ($order_item->ID !== '') { ?>
                            <option value="<?php echo $order_item->ID ?>" data-order-email="<?php echo $order->get_billing_email() ?>" <?php echo ($order_item->ID == $show_order) ? "selected" : ""; ?>>
                                <?php echo $order->get_order_number() ?>
                                - <?php echo $order->get_billing_first_name() ?> <?php echo $order->get_billing_last_name() ?>
                                (<?php echo $order->get_billing_email() ?>)
                            </option>
                            <?php
                        }
                    }

                    // If more than the orders limit then let the user know.
                    if ($limit_orders <= count($order_collection)) {?>
                        <option><?php printf(__('...Showing the most recent %u orders', 'woo-email-customizer-page-builder'), $limit_orders); ?></option>
                    <?php } ?>
                </select>
                <?php
                $tip = esc_html__("The order you select here is just used for previewing your email design. But when WooCommerce uses this email design to send notifications to the customers, the respective order data would be used.", 'woo-email-customizer-page-builder');
                ?>
                <span class="email-builder_hint_order update-nag">
                    <b><?php _e("The order data is used as a sample", 'woo-email-customizer-page-builder'); echo '<span class="woocommerce-help-tip" data-tip="' . $tip . '"></span>'; ?></b>
                </span>
<!--                    <div class="md-btn-group">-->
<!--                        <button class="md-btn md-btn-flat md-btn-flat-danger md-btn-small" :class="{disabled: currentLanguage == 'en'}" @click="changeLanguage('en')">En</button>-->
<!--                        <button class="md-btn md-btn-flat md-btn-flat-danger md-btn-small" :class="{disabled: currentLanguage == 'ru'}" @click="changeLanguage('ru')">Ru</button>-->
<!--                    </div>-->
            </div>
            <transition name="fade">
                <div class="email-builder-header-actions" v-if="orderEmailSelected() && !loading">
                    <div class="md-btn-group">
                        <i class="actions md-icon material-icons close md-color-red-600" :class="{disabled: !currentElement.type}" title="Close email settings" @click="editElement()"><?php esc_html_e('close', 'woo-email-customizer-page-builder'); ?></i>
                        <i class="actions md-icon material-icons save md-color-green-600" title="Save email" :class="{disabled: !hasChanges()}" @click="hasChanges() && saveEmailTemplate()"><?php esc_html_e('save', 'woo-email-customizer-page-builder'); ?></i>
                        <span class="copy_template_span" v-if="orderEmailSelected() && !loading" :class="{disabled: copyTemplate}" @click="copyTemplate = true">
                            <i class="actions md-icon material-icons md-icon dp48" title="<?php esc_html_e('Copy Template', 'woo-email-customizer-page-builder'); ?>">
                                <?php esc_html_e('content_copy', 'woo-email-customizer-page-builder'); ?>
                            </i>
                            <?php esc_html_e('Copy From', 'woo-email-customizer-page-builder'); ?>
                        </span>
                    </div>

                    <div class="md-btn-group">
                        <button class="md-btn md-btn-success" @click="previewEmail()">
                            <i class="material-icons"><?php esc_html_e('remove_red_eye', 'woo-email-customizer-page-builder'); ?></i>
                            <?php esc_html_e('Preview', 'woo-email-customizer-page-builder'); ?>
                        </button>
                        <button class="md-btn md-btn-info" :class="{disabled: showModal}" @click="showModal = true">
                            <i class="actions material-icons close md-color-red-300">open_in_new</i> <?php esc_html_e('Shortcodes', 'woo-email-customizer-page-builder'); ?>
                        </button>
                        <button class="md-btn md-btn-info" @click="sendTestMail()">
                            <i class="material-icons"><?php esc_html_e('email', 'woo-email-customizer-page-builder'); ?></i>
                            <?php esc_html_e('Send test email', 'woo-email-customizer-page-builder'); ?>
                        </button>
                    </div>
                </div>
            </transition>
        </div>

        <transition name="fade">
            <loading v-if="loading"></loading>
            <div class="email-builder-content" v-if="orderEmailSelected() && !loading">
                <div class="md-card" :class="{preview: preview}">
                    <div class="md-card-content elements-list">
                        <draggable :list="elements" :options="{group: { name: 'people', pull: 'clone', put: false }, sort: false, dragClass: 'drag-element'}" :clone="clone" element="ul" class="md-list md-list-addon" ref="elements">
                            <li v-for="element in elements" :data-type="element.type">
                                <div class="md-list-addon-element">
                                    <i class="material-icons" :class="element.iconClass" v-html="element.icon" :title="element.primary_head + '<br>' + element.second_head"></i>
                                </div>
                            </li>
                        </draggable>
                        <hr>
                        <ul class="md-list md-list-addon">
                            <li>
                                <div class="md-list-addon-element">
                                    <i class="actions material-icons" title="Email settings" @click="editElement('emailSettings')"><?php esc_html_e('settings', 'woo-email-customizer-page-builder'); ?></i>
                                </div>
                            </li>
                            <li>
                                <div class="md-list-addon-element">
                                    <i class="actions material-icons close md-color-red-300" :class="{disabled: showModal}" title="Show modal with shortcodes" @click="showModal = true"><?php esc_html_e('open_in_new', 'woo-email-customizer-page-builder'); ?></i>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <div position-relative :class="{configured: !!currentElement.type, preview: preview}">
                    <div class="builder" :style="Email.emailSettings.options">
                        <draggable class="email-container" :class="{empty: !Email.elements.length}" data-empty-template="Drag and drop elements here" :list="Email.elements" :options="{group: {name: 'people', put: true}, sort: true, handle: 'i.actions.move'}" ref="emailElements">
                            <div class="builder-element" :class="{selected: currentElement.id == element.id}" v-for="element in Email.elements">
                                <component :element="element" @click.native="editElement(element.id)" :is="element.component"></component>
<!--                                <i v-if="currentElement != element" class="actions edit md-icon material-icons" title="--><?php //esc_attr_e('Edit element', 'woo-email-customizer-page-builder'); ?><!--" @click="editElement(element.id)">--><?php //esc_html_e('edit', 'woo-email-customizer-page-builder'); ?><!--</i>-->
                                <i class="actions delete md-icon material-icons" title="<?php esc_attr_e('Delete element', 'woo-email-customizer-page-builder'); ?>" @click="removeElement(element)"><?php esc_html_e('delete', 'woo-email-customizer-page-builder'); ?></i>
                                <i class="actions clone md-icon material-icons" title="<?php esc_attr_e('Clone element', 'woo-email-customizer-page-builder'); ?>" @click="cloneElement(element)"><?php esc_html_e('content_copy', 'woo-email-customizer-page-builder'); ?></i>
                                <i v-if="Email.elements.length > 1" title="<?php esc_attr_e('Move element', 'woo-email-customizer-page-builder'); ?>" class="actions move md-icon material-icons"><?php esc_html_e('drag_handle', 'woo-email-customizer-page-builder'); ?></i>
                            </div>
                        </draggable>
                    </div>
                </div>
                <div class="md-card" :class="{empty: !currentElement.type || preview}">
                    <div class="md-card-content">
                        <form action="#" onsubmit="return false">
                            <div v-for="(option, key, index) in currentElement.options" v-if="currentElement.type && currentElement.type != 'emailSettings'" class="email-builder-element-edit-field">

                                <div v-if="key.indexOf('image') > -1 && key.search('Hide') == -1 && !currentElement.options[key + 'Hide']" class="form-row">
                                    <label for="builder_el_o_image_url" class="md-label"><?php esc_html_e('Image URL', 'woo-email-customizer-page-builder'); ?></label>
                                    <input id="builder_el_o_image_url" type="text" class="md-input" v-md-input v-input-file-upload v-model="currentElement.options[key]" />
                                </div>

                                <div v-if="key.substring(0, 4) == 'text'" class="form-row textarea">
                                    <label :for="'builder_el_text_' + key" class="md-label"><?php esc_html_e('Text', 'woo-email-customizer-page-builder'); ?></label>
                                    <textarea :id="'builder_el_text_' + key" v-tinymce-editor v-model="currentElement.options[key]"></textarea>
                                </div>

                                <div v-if="key == 'buttonText'" class="form-row">
                                    <label for="builder_el_o_btn_text" class="md-label"><?php esc_html_e('Button text', 'woo-email-customizer-page-builder'); ?></label>
                                    <input id="builder_el_o_btn_text" type="text" class="md-input" v-md-input v-model="currentElement.options[key]" />
                                </div>

                                <div v-if="key == 'url'" class="form-row">
                                    <label for="builder_el_o_url" class="md-label"><?php esc_html_e('URL', 'woo-email-customizer-page-builder'); ?></label>
                                    <input id="builder_el_o_url" type="url" class="md-input" v-md-input v-model="currentElement.options[key]" />
                                </div>

                                <div v-if="key == 'align'" class="form-row">
                                    <div class="md-btn-group">
                                        <button type="button" class="md-btn md-btn-flat" :class="{'md-btn-primary': option == 'left', 'md-btn-flat-primary': option != 'left'}" @click="currentElement.options[key] = 'left'"><?php esc_html_e('Left', 'woo-email-customizer-page-builder'); ?></button>
                                        <button type="button" class="md-btn md-btn-flat" :class="{'md-btn-primary': option == 'center', 'md-btn-flat-primary': option != 'center'}" @click="currentElement.options[key] = 'center'"><?php esc_html_e('Center', 'woo-email-customizer-page-builder'); ?></button>
                                        <button type="button" class="md-btn md-btn-flat" :class="{'md-btn-primary': option == 'right', 'md-btn-flat-primary': option != 'right'}" @click="currentElement.options[key] = 'right'"><?php esc_html_e('Right', 'woo-email-customizer-page-builder'); ?></button>
                                    </div>
                                </div>

                                <div v-if="['title', 'subTitle'].indexOf(key) > -1 || key.search('Link') > -1" class="form-row">
                                    <label :for="key" class="md-label">{{ key | makeTitle }}</label>
                                    <input :id="key" type="text" class="md-input" v-md-input v-model="currentElement.options[key]" />
                                </div>

                                <div v-if="key == 'padding'" class="form-row padding-inputs">
                                    <label for="builder_el_o_top" class="md-label"><?php esc_html_e('Top', 'woo-email-customizer-page-builder'); ?></label>
                                    <input id="builder_el_o_top" type="text" class="md-input" v-md-input v-model="currentElement.options[key][0]">

                                    <label for="builder_el_o_right" class="md-label"><?php esc_html_e('Right', 'woo-email-customizer-page-builder'); ?></label>
                                    <input id="builder_el_o_right" type="text" class="md-input" v-md-input v-model="currentElement.options[key][1]">

                                    <label for="builder_el_o_bottom" class="md-label"><?php esc_html_e('Bottom', 'woo-email-customizer-page-builder'); ?></label>
                                    <input id="builder_el_o_bottom" type="text" class="md-input" v-md-input v-model="currentElement.options[key][2]">

                                    <label for="builder_el_o_left" class="md-label"><?php esc_html_e('Left', 'woo-email-customizer-page-builder'); ?></label>
                                    <input id="builder_el_o_left" type="text" class="md-input" v-md-input v-model="currentElement.options[key][3]">
                                </div>

                                <div v-if="key == 'backgroundColor' || key == 'color' || key == 'buttonBackgroundColor'" class="form-row color-inputs">
                                    <label class="md-label" for="elementBackgroundColor" v-if="key == 'backgroundColor'"><?php esc_html_e('Background color', 'woo-email-customizer-page-builder'); ?></label>
                                    <label class="md-label" for="elementBackgroundColor" v-if="key == 'color'"><?php esc_html_e('Text color', 'woo-email-customizer-page-builder'); ?></label>
                                    <label class="md-label" for="elementBackgroundColor" v-if="key == 'buttonBackgroundColor'"><?php esc_html_e('Button background color', 'woo-email-customizer-page-builder'); ?></label>
                                    <input id="elementBackgroundColor" type="color" v-model="currentElement.options[key]" />
                                </div>

                                <div v-if="key == 'font'" class="block-properties">
                                    <h3><?php esc_html_e('Font', 'woo-email-customizer-page-builder'); ?></h3>
                                    <div class="form-row color-inputs" v-if="currentElement.options[key]['size']">
                                        <label for="font-size"><?php esc_html_e('Size', 'woo-email-customizer-page-builder'); ?></label>
                                        <input type="range" min="10" max="40" id="font-size" v-model="currentElement.options[key]['size']">
                                        <span>{{currentElement.options[key]['size']}}px</span>
                                    </div>
                                    <div class="form-row color-inputs" v-if="currentElement.options[key]['color']">
                                        <label for="font-color"><?php esc_html_e('Color', 'woo-email-customizer-page-builder'); ?></label>
                                        <input type="color" id="font-color" v-model="currentElement.options[key]['color']">
                                    </div>
                                    <div class="form-row color-inputs" v-if="currentElement.options[key]['weight']">
                                        <label for="font-weight"><?php esc_html_e('Weight', 'woo-email-customizer-page-builder'); ?></label>
                                        <select class="md-input" id="font-weight" v-model="currentElement.options[key]['weight']">
                                            <option v-for="option in currentElement.options[key]['weightOptions']" :value="option">{{option}}</option>
                                        </select>
                                    </div>
                                    <div class="form-row color-inputs" v-if="currentElement.options[key]['family']">
                                        <label for="font-family"><?php esc_html_e('Family', 'woo-email-customizer-page-builder'); ?></label>
                                        <select class="md-input" id="font-family" v-model="currentElement.options[key]['family']">
                                            <option :style="{fontFamily: option}" v-for="option in currentElement.options[key]['familyOptions']" :value="option">{{option}}</option>
                                        </select>
                                    </div>
                                </div>

                                <div v-if="key == 'buttons'">
                                    <div class="block-properties" v-for="(button, index) in option">
                                        <h3><?php esc_html_e('Button', 'woo-email-customizer-page-builder'); ?> {{index+1}}</h3>
                                        <div class="form-row color-inputs">
                                            <label :for="'button-active' + index"><?php esc_html_e('Active', 'woo-email-customizer-page-builder'); ?></label>
                                            <input type="checkbox" :id="'button-active' + index" v-model="button.active">
                                        </div>
                                        <div class="form-row color-inputs" v-if="button.active">
                                            <label :for="'button-width' + index"><?php esc_html_e('Full width', 'woo-email-customizer-page-builder'); ?></label>
                                            <input type="checkbox" :id="'button-width' + index" v-model="button.fullWidth">
                                        </div>
                                        <div class="form-row" v-if="button.active && !button.fullWidth">
                                            <div class="md-btn-group">
                                                <button type="button" class="md-btn md-btn-flat" :class="{'md-btn-primary': button.align == 'left', 'md-btn-flat-primary': button.align != 'left'}" @click="button.align = 'left'"><?php esc_html_e('Left', 'woo-email-customizer-page-builder'); ?></button>
                                                <button type="button" class="md-btn md-btn-flat" :class="{'md-btn-primary': button.align == 'center', 'md-btn-flat-primary': button.align != 'center'}" @click="button.align = 'center'"><?php esc_html_e('Center', 'woo-email-customizer-page-builder'); ?></button>
                                                <button type="button" class="md-btn md-btn-flat" :class="{'md-btn-primary': button.align == 'right', 'md-btn-flat-primary': button.align != 'right'}" @click="button.align = 'right'"><?php esc_html_e('Right', 'woo-email-customizer-page-builder'); ?></button>
                                            </div>
                                        </div>
                                        <div class="form-row color-inputs" v-if="button.active">
                                            <label :for="'button-color' + index"><?php esc_html_e('Background color', 'woo-email-customizer-page-builder'); ?></label>
                                            <input type="color" :id="'button-color' + index" v-model="button.backgroundColor">
                                        </div>
                                        <div class="form-row" v-if="button.active">
                                            <label for="builder_el_o_btn_text" class="md-label"><?php esc_html_e('Button text', 'woo-email-customizer-page-builder'); ?></label>
                                            <input id="builder_el_o_btn_text" type="text" class="md-input" v-md-input v-model="button.text" />
                                        </div>
                                        <div class="form-row" v-if="button.active">
                                            <label for="builder_el_o_url" class="md-label"><?php esc_html_e('Link', 'woo-email-customizer-page-builder'); ?></label>
                                            <input id="builder_el_o_url" type="url" class="md-input" v-md-input v-model="button.link" />
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div v-if="currentElement.type && currentElement.type == 'emailSettings'">

                                <div class="email-builder-element-edit-field">
                                    <div class="form-row padding-inputs">
                                        <label class="md-label" for="emailSettingsPaddingTop"><?php esc_html_e('Top', 'woo-email-customizer-page-builder'); ?></label>
                                        <input id="emailSettingsPaddingTop" class="md-input" type="text" v-md-input v-model="currentElement.options.paddingTop">
                                        <label class="md-label" for="emailSettingsPaddingLeft"><?php esc_html_e('Left', 'woo-email-customizer-page-builder'); ?></label>
                                        <input id="emailSettingsPaddingLeft" class="md-input" type="text" v-md-input v-model="currentElement.options.paddingLeft">
                                        <label class="md-label" for="emailSettingsPaddingBottom"><?php esc_html_e('Bottom', 'woo-email-customizer-page-builder'); ?></label>
                                        <input id="emailSettingsPaddingBottom" type="text" class="md-input" v-md-input v-model="currentElement.options.paddingBottom">
                                        <label class="md-label" for="emailSettingsPaddingRight"><?php esc_html_e('Right', 'woo-email-customizer-page-builder'); ?></label>
                                        <input id="emailSettingsPaddingRight" type="text" class="md-input" v-md-input v-model="currentElement.options.paddingRight">
                                    </div>
                                </div>

                                <div class="email-builder-element-edit-field">
                                    <div class="form-row color-inputs">
                                        <label class="md-label" for="emailSettingsBackground"><?php esc_html_e('Background color', 'woo-email-customizer-page-builder'); ?></label>
                                        <input id="emailSettingsBackground" type="color" v-model="currentElement.options.backgroundColor" />
                                    </div>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="email-builder-content" v-else>
                <p><?php _e('Please choose an <strong>email</strong> and an <strong>order</strong> from list above to show.', 'woo-email-customizer-page-builder'); ?></p>
            </div>
        </transition>
        <?php
        //Load order for Woo Mail Builder Send
        $order = new WC_Order($show_order);
        ?>
        <transition name="fade">
            <div id="openModal" class="modal" v-if="copyTemplate">
                <div class="overlay" @click.stop.prevent="copyTemplate = false"></div>
                <div class="content">
                    <h3>
                        <?php esc_html_e('Copy Template From', 'woo-email-customizer-page-builder'); ?> <a href="#" @click.stop.prevent="copyTemplate = false" title="Close modal" class="close">X</a>
                    </h3>
                    <div class="copy_template_container">
                        <?php
                        $avail_lang_list = get_available_languages();
                        $lang_select = wp_dropdown_languages( array(
                            'id' => 'woo_mb_email_lang',
                            'name' => 'woo_mb_email_lang',
                            'languages' => $avail_lang_list,
                            'selected' => get_locale(),
                            'echo'      => 0,
                            'show_available_translations' => false
                        )  );

                        $lang_vue_attribs = '<select v-model="emailLangFrom"';
                        $lang_select = str_replace( '<select', $lang_vue_attribs, $lang_select ); ?>

                        <?php    echo $lang_select;  ?>

                        <select v-model="emailTypeFrom" title="<?php _e('Choose which email to preview or send.', 'woo-email-customizer-page-builder'); ?>" name="woo_mb_email_type">
                            <option value="">
                                <?php _e("Email to show", 'woo-email-customizer-page-builder'); ?>
                            </option>
                            <?php
                            //Customer_Invoice
                            if (!empty($mails)) {
                                foreach ($mails as $mail) {
                                    // "customer_reset_password" is not handling.
//                            if (!in_array($mail->id, array('customer_reset_password'))) { ?>
                                    <option value="<?php echo $mail->id ?>" <?php echo ($show_type == $mail->id) ? "selected" : ""; ?> >
                                        <?php echo ucwords($mail->title); ?>
                                    </option>
                                    <?php
//                            }
                                }
                            }
                            ?>
                        </select>
                        <button class="md-btn md-btn-success" @click="copyTemplateFrom()"><?php _e("Copy template", 'woo-email-customizer-page-builder'); ?></button>
                    </div>
                </div>
            </div>
        </transition>
        <transition name="fade">
            <div id="openModal" class="modal" v-if="showModal">
                <div class="overlay" @click.stop.prevent="showModal = false"></div>
                <div class="content">
                    <h3>Short Codes <a href="#" @click.stop.prevent="showModal = false" title="Close modal" class="close">X</a></h3>
                    <table>
                        <tr>
                            <td class="codes-title"><?php esc_html_e('Order Details:', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td>[woo_mb_items]</td>
                            <td>- <?php esc_html_e('To Get Items', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td>[woo_mb_order_id]</td>
                            <td>- <?php esc_html_e('To Get Order ID', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td>[woo_mb_order_link]</td>
                            <td>- <?php esc_html_e('To Get Order URL: Takes url from settings', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td>[woo_mb_order_sub_total]</td>
                            <td>- <?php esc_html_e('To Get Order Sub-Total', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td>[woo_mb_order_total]</td>
                            <td>- <?php esc_html_e('To Get Order Total', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td>[woo_mb_order_fee]</td>
                            <td>- <?php esc_html_e('To Get Order Fee', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td>[woo_mb_order_refund]</td>
                            <td>- <?php esc_html_e('To Get Order Refunds', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td>[woo_mb_order_date]</td>
                            <td>- <?php esc_html_e('To Get Order Date', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td class="codes-title"><?php esc_html_e('Payment:', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td>[woo_mb_order_payment_method]</td>
                            <td>- <?php esc_html_e('To Get Payment Method', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td>[woo_mb_payment_method]</td>
                            <td>- <?php esc_html_e('To Get Payment Method', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td>[woo_mb_order_payment_url]</td>
                            <td>- <?php esc_html_e('To Get Payment URL', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td>[woo_mb_transaction_id]</td>
                            <td>- <?php esc_html_e('To Get Transaction ID', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td class="codes-title"><?php esc_html_e('Shipping:', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td>[woo_mb_shipping_method]</td>
                            <td>- <?php esc_html_e('To Get Shipping Method', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td>[woo_mb_order_shipping]</td>
                            <td>- <?php esc_html_e('To get Shipping Total', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td>[woo_mb_shipping_first_name]</td>
                            <td>- <?php esc_html_e('To Get Shipping First Name', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td>[woo_mb_shipping_last_name]</td>
                            <td>- <?php esc_html_e('To Get Shipping Last Name', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td>[woo_mb_shipping_company]</td>
                            <td>- <?php esc_html_e('To Get Shipping Company', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td>[woo_mb_shipping_address]</td>
                            <td>- <?php esc_html_e('To Get Shipping Address', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td>[woo_mb_shipping_address_1]</td>
                            <td>- <?php esc_html_e('To Get Shipping Address 1', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td>[woo_mb_shipping_address_2]</td>
                            <td>- <?php esc_html_e('To Get Shipping Address 2', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td>[woo_mb_shipping_city]</td>
                            <td>- <?php esc_html_e('To Get Shipping City', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td>[woo_mb_shipping_state]</td>
                            <td>- <?php esc_html_e('To Get Shipping State', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td>[woo_mb_shipping_postcode]</td>
                            <td>- <?php esc_html_e('To Get Shipping Postal Code', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td>[woo_mb_shipping_country]</td>
                            <td>- <?php esc_html_e('To Get Shipping Country', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td class="codes-title"><?php esc_html_e('Billing:', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td>[woo_mb_billing_address]</td>
                            <td>- <?php esc_html_e('To Get Billing Address', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td>[woo_mb_billing_first_name]</td>
                            <td>- <?php esc_html_e('To Get First Name', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td>[woo_mb_billing_last_name]</td>
                            <td>- <?php esc_html_e('To Get Last Name', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td>[woo_mb_billing_company]</td>
                            <td>- <?php esc_html_e('To Get Billing Company', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td>[woo_mb_billing_address_1]</td>
                            <td>- <?php esc_html_e('To Get Billing Address 1', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td>[woo_mb_billing_address_2]</td>
                            <td>- <?php esc_html_e('To Get Billing Address 2', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td>[woo_mb_billing_city]</td>
                            <td>- <?php esc_html_e('To Get Billing City', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td>[woo_mb_billing_state]</td>
                            <td>- <?php esc_html_e('To Get Billing State', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td>[woo_mb_billing_postcode]</td>
                            <td>- <?php esc_html_e('To Get Billing PostalTo Get User\'s Email Code', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td>[woo_mb_billing_country]</td>
                            <td>- <?php esc_html_e('To Get Billing Country', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td>[woo_mb_billing_phone]</td>
                            <td>- <?php esc_html_e('To Get Billing Phone', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td>[woo_mb_billing_email]</td>
                            <td>- <?php esc_html_e('To Get Billing Email', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td class="codes-title"><?php esc_html_e('General:', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td>[woo_mb_site_name]</td>
                            <td>- <?php esc_html_e('To get Site Name', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td>[woo_mb_site_url]</td>
                            <td>- <?php esc_html_e('To Get Site URL', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td>[woo_mb_user_id]</td>
                            <td>- <?php esc_html_e('To Get User Id', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td>[woo_mb_user_name]</td>
                            <td>- <?php esc_html_e('To Get User\'s Name', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td>[woo_mb_user_email]</td>
                            <td>- <?php esc_html_e('To Get User\'s Email', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td>[woo_mb_customer_provided_note]</td>
                            <td>- <?php esc_html_e('To Get Customer provided note', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td>[woo_mb_customer_note]</td>
                            <td>- <?php esc_html_e('To Get Customer last note', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td>[woo_mb_customer_notes]</td>
                            <td>- <?php esc_html_e('To Get all Customer Notes', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td class="codes-title"><?php esc_html_e('Reset Password:', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td>[woo_mb_password_reset_url]</td>
                            <td>- <?php esc_html_e('To get reset url', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td class="codes-title"><?php esc_html_e('New User:', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td>[woo_mb_user_password]</td>
                            <td>- <?php esc_html_e('To get User Password', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td>[woo_mb_user_activation_link]</td>
                            <td>- <?php esc_html_e('To get activation url', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td class="codes-title"><?php esc_html_e('Custom code:', 'woo-email-customizer-page-builder'); ?></td>
                        </tr>
                        <tr>
                            <td>[woo_mb_custom_code]</td>
                            <td>- <?php esc_html_e( 'Your custom code can be overridden by copying ['.plugin_dir_path(WOO_ECPB_PLUGIN_BASENAME).'templates/woo_mail/custom_code.php] to [yourtheme/'.plugin_dir_path(WOO_ECPB_PLUGIN_BASENAME).'woo_mail/custom_code.php].','woo-email-customizer-page-builder'); ?>
                                <br/>
                                <?php esc_html_e('You can also add attributes if required Eg:[woo_mb_custom_code type="pre-order-link"]', 'woo-email-customizer-page-builder'); ?>
                            </td>
                        </tr>
                        <?php
                        /* To get custom fields */
                        if(!empty($order)){
                            if(function_exists('wc_get_custom_checkout_fields')) {
                                $custom_fields = wc_get_custom_checkout_fields($order);
                                if (!empty($custom_fields)) {
                                    ?>
                                    <tr>
                                        <td class="codes-title"><?php esc_html_e('Custom fields:', 'woo-email-customizer-page-builder'); ?></td>
                                    </tr>
                                    <?php
                                    foreach ($custom_fields as $key => $custom_field) {
                                        ?>
                                        <tr>
                                            <td>[woo_mb_<?php echo $key; ?>]</td>
                                            <td>- <?php
                                                if (isset($custom_field['label'])) {
                                                    echo $custom_field['label'];
                                                } ?></td>
                                        </tr>
                                        <?php
                                    }
                                }
                            }
                        }

                        /**
                         * Compatible - Flexible Checkout Fields for WooCommerce
                         * */
                        $custom_fields_flexible_checkout = WooEmailCustomizerCommon::getCustomFieldsOfFlexibleCheckoutFields();
                        if(!empty($custom_fields_flexible_checkout) && count($custom_fields_flexible_checkout) > 0){
                            ?>
                            <tr>
                                <td class="codes-title"><?php esc_html_e('Flexible Checkout Custom Fields :', 'woo-email-customizer-page-builder'); ?></td>
                            </tr>
                            <?php
                            foreach ($custom_fields_flexible_checkout as $key => $custom_fields_flexible_checkout_field) {
                                ?>
                                <tr>
                                    <td>[woo_mb<?php echo $key; ?>]</td>
                                    <td>- <?php echo $custom_fields_flexible_checkout_field; ?></td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                    </table>
                </div>
            </div>
        </transition>
    </div>
</script>

<?php
$js = 'let order_info = [];';
$js .= 'let lang = "'.WOO_ECPB_LANG.'";
    let woo_email_customizer_user_mail = "'.wp_get_current_user()->user_email.'";
    let woo_email_customizer_ajax_url = "'.admin_url('admin-ajax.php').'";
    let woo_email_customizer_containerWidth = "'.$woo_mb_settings_container_width.'";';
wp_add_inline_script('woo-email', $js, 'after');
?>
<?php wp_enqueue_script('woo-email'); ?>
