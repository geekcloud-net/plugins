<?php
/**
 * Template for the modals
 */
?>

<script type="text/template" id="tmpl-missing-attributes">
    <table class="product-fields-table">
        <tbody>
        {{#each attr}}
        <tr>
            <td>{{name}}</td>
            <td>
                <select data-label="{{name}}" data-taxonomy="{{slug}}" class="attribute_{{slug}}">
                    <option value=""><?php _e('Choose an option', 'wc_point_of_sale'); ?></option>
                    {{missingAttributesOptions}}
                </select>
            </td>
        </tr>
        {{/each}}
        </tbody>
    </table>
</script>

<script type="text/template" id="tmpl-product-addons">
    {{#each product_addons}}
    <table class="product-fields-table" data-required="{{required}}">
        <tr>
            <td><p class="product_addon_label">{{name}} {{#if required}}<abbr class="required"
                                                                              title="Required field">*</abbr>{{/if}}</p>
                <p class="description">{{description}}</p>
            </td>
            <td>
                {{#switch type}}
                {{#case "checkbox" break=true}}
                {{#each options}}
                <label class="pos_addon_label">{{label}}{{{accountingPOS price}}}
                    <input type="checkbox" data-raw-price="{{price}}" data-price="{{price}}"
                           class="addon addon-checkbox {{../type}}_{{@../index}} {{../type}}_{{@../index}}_{{@index}}"
                           name="{{../type}}_{{@../index}}">
                    <span class="pos_checkmark"></span>
                </label>
                {{/each}}
                {{/case}}
                {{#case "radiobutton" break=true}}
                {{#each options}}
                <label class="pos_addon_label">{{label}}{{{accountingPOS price}}}
                    <input type="radio" data-raw-price="{{price}}" data-price="{{price}}"
                           class="addon addon-radio {{../type}}_{{@../index}} {{../type}}_{{@../index}}_{{@index}}"
                           name="{{../type}}_{{@../index}}">
                    <span class="pos_radio"></span>
                </label>
                {{/each}}
                {{/case}}
                {{#case "custom_textarea" break=true}}
                {{#each options}}
                <label class="pos_addon_label pos_addon_textarea">
                <textarea data-raw-price="{{price}}" data-price="{{price}}"
                          placeholder="{{label}}{{{accountingPOS price}}}"
                          class="input-text addon addon-custom-textarea {{../type}}_{{@../index}} {{../type}}_{{@../index}}_{{@index}}"
                          {{product_addons_maxlength}}></textarea>
                </label>
                {{/each}}
                {{/case}}
                {{#case "custom" break=true}}
                {{#each options}}
                <label class="pos_addon_label pos_addon_text">
                    <input type="text" data-raw-price="{{price}}" data-price="{{price}}"
                           placeholder="{{label}}{{{accountingPOS price}}}"
                           class="input-text addon addon-custom {{../type}}_{{@../index}} {{../type}}_{{@../index}}_{{@index}}"
                           {{product_addons_maxlength}}>
                </label>
                {{/each}}
                {{/case}}
                {{#case "custom_email" break=true}}
                {{#each options}}
                <label class="pos_addon_label pos_addon_text">
                    <input type="email" data-raw-price="{{price}}" placeholder="{{label}}{{{accountingPOS price}}}"
                           data-price="{{price}}"
                           pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$"
                           class="input-text addon addon-custom addon-custom-email {{../type}}_{{@../index}} {{../type}}_{{@../index}}_{{@index}}">
                </label>
                {{/each}}
                {{/case}}
                {{#case "custom_price" break=true}}
                {{#each options}}
                <label class="pos_addon_label pos_addon_text">
                    <input type="number" step="any" data-raw-price="{{price}}"
                           placeholder="{{label}}{{{accountingPOS price}}}"
                           class="input-text addon addon-custom-price {{../type}}_{{@../index}} {{../type}}_{{@../index}}_{{@index}}"
                           {{product_addons_max}} {{product_addons_min}}>
                </label>
                {{/each}}
                {{/case}}
                {{#case "custom_letters_only" break=true}}
                {{#each options}}
                <label class="pos_addon_label pos_addon_text">
                    <input type="text" data-raw-price="{{price}}" data-price="{{price}}" pattern="[A-Za-z]*"
                           placeholder="{{label}}{{{accountingPOS price}}}"
                           class="input-text addon addon-custom addon-custom-pattern {{../type}}_{{@../index}} {{../type}}_{{@../index}}_{{@index}}"
                           {{product_addons_maxlength}}>
                </label>
                {{/each}}
                {{/case}}
                {{#case "custom_digits_only" break=true}}
                {{#each options}}
                <label class="pos_addon_label pos_addon_text">
                    <input type="text" data-raw-price="{{price}}" data-price="{{price}}" pattern="[0-9]*"
                           placeholder="{{label}}{{{accountingPOS price}}}"
                           class="input-text addon addon-custom addon-custom-pattern {{../type}}_{{@../index}} {{../type}}_{{@../index}}_{{@index}}"
                           {{product_addons_maxlength}}>
                </label>
                {{/each}}
                {{/case}}
                {{#case "custom_letters_or_digits" break=true}}
                {{#each options}}
                <label class="pos_addon_label pos_addon_text">
                    <input type="text" data-raw-price="{{price}}" data-price="{{price}}" pattern="[A-Za-z0-9]*"
                           placeholder="{{label}}{{{accountingPOS price}}}"
                           class="input-text addon addon-custom addon-custom-pattern {{../type}}_{{@../index}} {{../type}}_{{@../index}}_{{@index}}"
                           {{product_addons_maxlength}}>
                </label>
                {{/each}}
                {{/case}}
                {{#case "input_multiplier" break=true}}
                {{#each options}}
                <label class="pos_addon_label pos_addon_text">
                    <input type="number" step="any" data-raw-price="{{price}}" data-price="{{price}}"
                           placeholder="{{label}}{{{accountingPOS price}}}"
                           class="input-text addon addon-input_multiplier {{../type}}_{{@../index}} {{../type}}_{{@../index}}_{{@index}}"
                           {{product_addons_max}} {{product_addons_min}}>
                    <span class="addon-alert"><?php _e('This must be a number!', 'wc_point_of_sale'); ?></span>
                </label>
                {{/each}}
                {{/case}}
                {{#case "select" break=true}}
            <td colspan="2">
                <select class="addon addon-select {{type}}_{{@index}}">
                    <option value=""><?php _e('Choose an option...', 'wc_point_of_sale'); ?></option>
                    {{#each options}}
                    <option data-raw-price="{{price}}" data-price="{{price}}" value="{{label}}">
                        {{label}}{{{accountingPOS price}}}
                    </option>
                    {{/each}}
                </select>
                {{/case}}
                {{/switch}}
                {{../type}}
            </td>
        </tr>
    </table>
    {{/each}}
</script>
<?php
if (get_option('woocommerce_prices_include_tax') === 'no') {
    $tax_mode = 'excl';
} else {
    $tax_mode = 'incl';
}
$tax_display_mode = get_option('woocommerce_tax_display_shop');
?>
<script type="text/template" id="tmpl-product-addons-total">
    <tbody id="product-addons-total" data-show-grand-total="1"
           data-tax-mode="<?php echo esc_attr($tax_mode); ?>"
           data-tax-display-mode="<?php echo esc_attr($tax_display_mode); ?>">
    </tbody>
</script>

<script type="text/template" id="tmpl-add-custom-item-meta">
    {{#each this}}
    <tr>
        <td class="meta_label"><input type="text" class="meta_label_value" value="{{meta_key}}"></td>
        <td class="meta_attribute"><input type="text" class="meta_attribute_value" value="{{meta_v}}"></td>
        <td class="remove_meta"><span href="#" data-tip="<?php _e('Remove', 'wc_point_of_sale'); ?>"
                                      class="remove_custom_product_meta"></span></td>
    </tr>
    {{/each}}
</script>

<script type="text/template" id="tmpl-form-add-customer">
    <input type="hidden" id="customer_details_id" value="{{id}}">
    <div id="pos_billing_details" class="pos-customer-details-tab">
        <div class="woocommerce-billing-fields">
            <?php
            // Fix for checkout address if the WooCommerce Subscriptions plugin activated
            remove_all_filters('woocommerce_checkout_get_value');
            $checkout = WC()->checkout();
            $wc_reg_generate_username_opt = get_option('woocommerce_registration_generate_username');
            $wc_reg_generate_pass_opt = get_option('woocommerce_registration_generate_password');
            $required_fields = get_option('wc_pos_customer_create_required_fields', false);
            $hide_fields = get_option('wc_pos_hide_not_required_fields', 'no');
            //unset($checkout->checkout_fields['order']['order_comments']);
            if (isset($checkout->checkout_fields['account'])) {
                foreach ($checkout->checkout_fields['account'] as $key => $field) :
                    if (($key == 'account_username' && $wc_reg_generate_username_opt == 'yes')
                        || ($key == 'account_password' && $wc_reg_generate_pass_opt == 'yes')
                    ) {
                        continue;
                    }
                    $value = str_replace('account_', 'billing_address.', $key);
                    if (strpos($value, 'billing_address.') === false && strpos($value, 'billing_address.') !== 0 && $key != 0) {
                        $value = 'billing_address.' . $key;
                    }
                    if ($key == 'account_password') {
                        $field['class'][] = 'form-row-first';
                    }
                    woocommerce_form_field('billing_' . $key, $field, '{{' . $value . '}}');
                    if ($key == 'account_password') {
                        $args = array(
                            'type' => 'password',
                            'label' => __('Password confirm'),
                            'required' => true,
                            'placeholder' => 'Password confirm',
                            'class' => array('form-row-last')
                        );
                        woocommerce_form_field('billing_password_confirm', $args, '');
                    }
                endforeach;
            }
            if (isset($checkout->checkout_fields['billing'])) {
                if (function_exists('wc_address_validation')) {
                    $validation_handler = wc_address_validation()->get_handler_instance();
                    $validation_handler->show_billing_postcode_lookup();
                }
                foreach ($checkout->checkout_fields['billing'] as $key => $field) :
                    $value = str_replace('billing_', 'billing_address.', $key);
                    if ($required_fields !== false && !in_array($key, $required_fields) && !in_array($key, array('billing_first_name','billing_last_name','billing_email'))) {
                        $field['required'] = false;
                    }
                    if ($hide_fields == 'yes' && $field['required'] == false) {
                        continue;
                    }
                    if (strpos($value, 'billing_address.') === false && strpos($value, 'billing_address.') !== 0) {
                        $value = 'billing_address.[' . $key . ']';
                    }
                    woocommerce_form_field($key, $field, '{{' . $value . '}}');
                endforeach;
            }
            ?>
            <?php do_action('woocommerce_after_checkout_billing_form', $checkout); ?>
        </div>
    </div>
    </div>
    <div id="pos_shipping_details" class="pos-customer-details-tab">
        <div class="woocommerce-shipping-fields">
            <div class="shipping_address">
                <p class="billing-same-as-shipping">
                    <a id="billing-same-as-shipping"
                       class="button billing-same-as-shipping"><?php _e('Copy billing address', 'wc_point_of_sale'); ?></a>
                </p>
                <?php
                if (isset($checkout->checkout_fields['shipping'])) {
                    if (function_exists('wc_address_validation')) {
                        $validation_handler->show_shipping_postcode_lookup();
                    }
                    foreach ($checkout->checkout_fields['shipping'] as $key => $field) :
                        if ($required_fields !== false && !in_array($key, $required_fields)) {
                            $field['required'] = false;
                        }
                        if ($hide_fields == 'yes' && $field['required'] == false) {
                            continue;
                        }
                        $value = str_replace('shipping_', 'shipping_address.', $key);
                        woocommerce_form_field($key, $field, '{{' . $value . '}}');
                    endforeach;
                }
                ?>
            </div>
        </div>
    </div>
    <div id="pos_additional_fields" class="pos-customer-details-tab">
        <div class="woocommerce-additional-fields">

            <?php
            if (isset($checkout->checkout_fields['order'])) {
                foreach ($checkout->checkout_fields['order'] as $key => $field) : ?>

                    <?php woocommerce_form_field($key, $field, '{{additional_fields.' . $key . '}}'); ?>

                <?php endforeach;

            } ?>

        </div>
    </div>
    <div id="pos_order_fields" class="pos-customer-details-tab">
        <div class="woocommerce-additional-fields">

            <?php
            if (isset($checkout->checkout_fields['pos_custom_order'])) {
                foreach ($checkout->checkout_fields['pos_custom_order'] as $key => $field) {
                    if ($field['type'] == 'checkbox') {
                        $option_count = 0;
                        echo '<p class="form-row">';
                        echo '<label>' . $field['label'] . '</label>';
                        foreach ($field['options'] as $value => $label) {
                            $input = sprintf('<input type="%s" name="%s" id="%s-%s" value="%s" />', esc_attr($field['type']), $key . '[]', $field['id'], $option_count, esc_attr($value));
                            printf('<label for="%1$s-%2$s">%3$s%4$s</label>', $field['id'], $option_count, $input, esc_html($label));
                            $option_count++;
                        }
                        echo '</p>';

                    } else {
                        woocommerce_form_field($key, $field, '{{custom_order_fields.' . $field['id'] . '}}');
                    }
                }
            } ?>

        </div>
    </div>
    <div id="pos_custom_fields" class="pos-customer-details-tab">
        <div class="woocommerce-custom-fields">

            <?php if (isset($checkout->checkout_fields['pos_acf'])) : ?>
                <?php foreach ($checkout->checkout_fields['pos_acf'] as $group) : ?>

                    <h3><?php echo $group['title']; ?></h3>

                    <?php
                    foreach ($group['fields'] as $key => $field) {
                        switch ($field['type']) {
                            case 'checkbox':
                                $option_count = 0;
                                echo '<p class="form-row">';
                                echo '<label>' . $field['label'] . '</label>';
                                foreach ($field['options'] as $value => $label) {
                                    $input = sprintf('<input type="%s" name="%s" id="%s-%s" value="%s" />', esc_attr($field['type']), $key . '[]', $field['id'], $option_count, esc_attr($value));
                                    printf('<label for="%1$s-%2$s">%3$s%4$s</label>', $field['id'], $option_count, $input, esc_html($label));
                                    $option_count++;
                                }
                                echo '</p>';

                                break;
                            case 'radio':
                                $option_count = 0;
                                echo '<p class="form-row">';
                                echo '<label>' . $field['label'] . '</label>';
                                foreach ($field['options'] as $value => $label) {
                                    $input = sprintf('<input type="%s" name="%s" id="%s-%s" value="%s" />', esc_attr($field['type']), $key, $field['id'], $option_count, esc_attr($value));
                                    printf('<label for="%1$s-%2$s">%3$s%4$s</label>', $field['id'], $option_count, $input, esc_html($label));
                                    $option_count++;
                                }
                                echo '</p>';

                                break;
                            case 'textarea':
                            case 'password' :
                            case 'text' :
                            case 'email' :
                            case 'tel' :
                            case 'number' :
                                woocommerce_form_field($key, $field, '{{custom_fields.' . $key . '}}');
                                break;
                            case 'select' :
                                if (isset($field['multiple']) && $field['multiple'])
                                    $key .= '[]';
                                woocommerce_form_field($key, $field);
                                break;
                            case 'wysiwyg':
                                $field['type'] = 'textarea';
                                woocommerce_form_field($key, $field, '{{custom_fields.' . $key . '}}');
                                break;
                            default:
                                $field['class'] = isset($field['class']) ? implode(' ', $field['class']) : '';
                                echo '<div class="form-row acf-form-row">';
                                do_action('acf/create_fields', array($field), 0);
                                echo '</div>';
                                break;
                        }


                    } ?>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>
    </div>
    <div class="clear"></div>
</script>
<script type="text/template" id="tmpl-custom-shipping-shippingaddress">
    <?php
    $countries = WC()->countries->get_allowed_countries();
    foreach ($checkout->checkout_fields['shipping'] as $key => $field) :
        $value = str_replace('shipping_', 'shipping_address.', $key);
        if (isset($field['type']) && $field['type'] == 'state' && 1 === sizeof($countries)) {
            get_single_country_states('custom_' . $key, $field, $countries);
        } else {
            woocommerce_form_field('custom_' . $key, $field, '{{' . $value . '}}');
        }
    endforeach; ?>
</script>

<script type="text/template" id="tmpl-custom-shipping-method-title-price">
    <tr>
        <td class="shipping_title"><input type="text" id="custom_shipping_title" value="{{title}}"></td>
        <td class="shipping_price"><input type="text" id="custom_shipping_price" value="{{price}}"></td>
    </tr>
</script>

<script type="text/template" id="tmpl-retrieve-sales-orders-list">
    <table class="wp-list-table widefat fixed striped posts retrieve_sales_nav">
        <tbody>
        {{#each this}}
        <tr class="iedit author-self level-0 post-{{id}} type-shop_order status-wc-pending post-password-required hentry">
            <td class="order_status column-order_status">{{{order_status}}}</td>
            <td class="order_title column-order_title has-row-actions column-primary">
                {{displayOrderTitle}}
            </td>
            <td class="order_items column-order_items">
                <a class="show_order_items" href="#">
                    {{getCountItems}}
                </a>
                <table cellspacing="0" class="order_items" style="display: none;">
                    <tbody>
                    {{order_items_list}}
                    </tbody>
                </table>
            </td>
            <td class="shipping_address column-shipping_address">
                {{{formatted_shipping_address}}}
            </td>
            <td class="customer_message column-customer_message">{{{customer_message}}}</td>
            <td class="order_notes column-order_notes">{{{order_notes}}}</td>
            <td class="order_date column-order_date">{{{order_date}}}</td>
            <td class="order_total column-order_total">{{{order_total}}}</td>
            <td class="crm_actions column-crm_actions"><p>
                    <a href="{{this.id}}"
                       class="button load_order_data"><?php _e('Load', 'wc_point_of_sale'); ?></a>
                    <a href="<?php echo admin_url('admin.php?print_pos_receipt=true&order_id=') ?>{{this.id}}&_wpnonce=<?= wp_create_nonce('print_pos_receipt') ?>"
                       class="button reprint_receipts"><?php _e('Print', 'wc_point_of_sale'); ?></a>
                </p></td>
        </tr>
        {{/each}}
        </tbody>
    </table>
</script>

<script type="text/template" id="tmpl-retrieve-sales-orders-not-found">
    <table class="wp-list-table widefat fixed striped posts retrieve_sales_nav">
        <tbody>
        <tr class="no-items">
            <td colspan="9" class="colspanchange"><?php _e('No Orders found', 'wc_point_of_sale'); ?></td>
        </tr>
        </tbody>
    </table>
</script>

<script type="text/template" id="tmpl-retrieve-sales-orders-pager">
    <div class="tablenav">
        <div class="tablenav-pages">
            <span class="displaying-num">{{items}}</span>
            {{#if count}}
            <span class="pagination-links">
                {{#if urls.a}}
                <a href="#" class="first-page" onclick="{{{urls.a}}}">
                    <span class="screen-reader-text">First page</span>
                    <span aria-hidden="true">«</span>
                </a>
                {{else}}
                    <span aria-hidden="true" class="tablenav-pages-navspan">«</span>
                {{/if}}
                {{#if urls.b}}
                <a href="#" class="prev-page" onclick="{{{urls.b}}}">
                    <span class="screen-reader-text">Previous page</span>
                    <span aria-hidden="true">‹</span>
                </a>
                {{else}}
                    <span aria-hidden="true" class="tablenav-pages-navspan">‹</span>
                {{/if}}
            
                <span class="paging-input"><label class="screen-reader-text"
                                                  for="current-page-selector">Current Page</label>
                    <input type="text" aria-describedby="table-paging" size="1" value="{{currentpage}}"
                           id="current-page-selector" class="current-page" data-count="{{count}}"
                           data-reg_id="{{reg_id}}">
                    of <span class="total-pages">{{countpages}}</span>
                </span>

                {{#if urls.c}}
                <a href="#" class="next-page" onclick="{{{urls.c}}}">
                    <span class="screen-reader-text">Next page</span>
                    <span aria-hidden="true">›</span>
                </a>
                {{else}}
                    <span aria-hidden="true" class="tablenav-pages-navspan">›</span>
                {{/if}}
                {{#if urls.d}}
                <a href="#" class="last-page" onclick="{{{urls.d}}}">
                    <span class="screen-reader-text">Last page</span>
                    <span aria-hidden="true">»</span>
                </a>
                {{else}}
                    <span aria-hidden="true" class="tablenav-pages-navspan">»</span>
                {{/if}}
            </span>
            {{/if}}
        </div>
    </div>
</script>

<script type="text/template" id="tmpl-confirm-box-content">
    {{#if title}}
    <h3>{{{title}}}</h3>
    {{/if}}
    {{#if content}}
    <div>
        {{{content}}}
    </div>
    {{/if}}
    <div class="wrap-button">
        <button class="button" type="button" id="cancel-button">
            <?php _e('Cancel', 'wc_point_of_sale'); ?>
        </button>
        <button class="button button-primary" type="button" id="confirm-button">
            <?php _e('Ok', 'wc_point_of_sale'); ?>
        </button>
    </div>
</script>
<script type="text/template" id="tmpl-confirm-void-register">
    <div class="md-message-icon">
        <span class="dashicons dashicons-warning"></span>
    </div>
    <p><?php _e("Are you sure you want to clear all fields and start from scratch?", 'wc_point_of_sale'); ?></p>
</script>
<script type="text/template" id="tmpl-prompt-email-receipt">
    <div class="md-message-icon">
        <span class="dashicons dashicons-email"></span>
    </div>
    <p><?php _e("Do you want to email the receipt? If so, enter customer email.", 'wc_point_of_sale'); ?></p>
</script>

<script type="text/template" id="tmpl-booking-data">
    <table class="product-fields-table">
        <tbody>
        {{#each fields}}
        <tr>
            <td>{{{label}}}</td>
            <td>
                {{#switch type}}
                {{#case "datetime-picker" break=true}}
                <div class="wc-bookings-date-picker">
                    <div class="wc-bookings-date-picker-date-fields">
                        {{#if month_before_day}}
                        <label>
                            <input type="text" name="{{name}}_month"
                                   placeholder="<?php _e('mm', 'woocommerce-bookings'); ?>" size="2"
                                   class="required_for_calculation booking_date_month"/>
                        </label> / <label>
                            <input type="text" name="{{name}}_day"
                                   placeholder="<?php _e('dd', 'woocommerce-bookings'); ?>" size="2"
                                   class="required_for_calculation booking_date_day"/>
                        </label>
                        {{ else }}
                        <label>
                            <input type="text" name="{{name}}_day"
                                   placeholder="<?php _e('dd', 'woocommerce-bookings'); ?>" size="2"
                                   class="required_for_calculation booking_date_day"/>
                        </label> / <label>
                            <input type="text" name="{{name}}_month"
                                   placeholder="<?php _e('mm', 'woocommerce-bookings'); ?>" size="2"
                                   class="required_for_calculation booking_date_month"/>
                        </label>
                        {{/if}}
                        / <label>
                            <input type="text" value="<?php echo date('Y'); ?>" name="{{name}}_year"
                                   placeholder="<?php _e('YYYY', 'woocommerce-bookings'); ?>" size="4"
                                   class="required_for_calculation booking_date_year"/>
                        </label>
                    </div>
                    <a class="wc-bookings-date-picker-choose-date button"><?php _e('Choose Date', 'woocommerce-bookings'); ?></a>
                    <div class="picker" data-display='{{{display}}}' data-availability='{{{json availability_rules}}}'
                         data-default-availability='{{{default_availability}}}'
                         data-fully-booked-days='{{{json fully_booked_days}}}'
                         data-partially-booked-days='{{{json partially_booked_days}}}' data-min_date='{{{min_date_js}}}'
                         data-max_date='{{{max_date_js}}}' data-default_date='{{{default_date}}}'></div>
                </div>
                {{/case}}
                {{#case "date-picker" break=true}}
                <div class="wc-bookings-date-picker">
                    <div class="wc-bookings-date-picker-date-fields">
                        {{#if ../product.is_customer_range_picker}}
                        <div><?php echo esc_html(apply_filters('woocommerce_bookings_date_picker_start_label', __('Start', 'woocommerce-bookings'))); ?>
                            :
                        </div>
                        {{/if}}
                        {{#if month_before_day}}
                        <label>
                            <input type="text" name="{{name}}_month"
                                   placeholder="<?php _e('mm', 'woocommerce-bookings'); ?>" size="2"
                                   class="booking_date_month"/>
                        </label> / <label>
                            <input type="text" name="{{name}}_day"
                                   placeholder="<?php _e('dd', 'woocommerce-bookings'); ?>" size="2"
                                   class="booking_date_day"/>
                        </label>
                        {{ else }}
                        <label>
                            <input type="text" name="{{name}}_day"
                                   placeholder="<?php _e('dd', 'woocommerce-bookings'); ?>" size="2"
                                   class="booking_date_day"/>
                        </label> / <label>
                            <input type="text" name="{{name}}_month"
                                   placeholder="<?php _e('mm', 'woocommerce-bookings'); ?>" size="2"
                                   class="booking_date_month"/>
                        </label>
                        {{/if}}
                        / <label>
                            <input type="text" value="<?php echo date('Y'); ?>" name="{{name}}_year"
                                   placeholder="<?php _e('YYYY', 'woocommerce-bookings'); ?>" size="4"
                                   class="booking_date_year"/>
                        </label>
                    </div>
                    {{#if ../product.is_customer_range_picker}}
                    <div class="wc-bookings-date-picker-date-fields">
                        <div><?php echo esc_html(apply_filters('woocommerce_bookings_date_picker_end_label', __('End', 'woocommerce-bookings'))); ?>
                            :
                        </div>

                        {{#if month_before_day}}
                        <label>
                            <input type="text" name="{{name}}_to_month"
                                   placeholder="<?php _e('mm', 'woocommerce-bookings'); ?>" size="2"
                                   class="booking_to_date_month"/>
                        </label> / <label>
                            <input type="text" name="{{name}}_to_day"
                                   placeholder="<?php _e('dd', 'woocommerce-bookings'); ?>" size="2"
                                   class="booking_to_date_day"/>
                        </label>
                        {{ else }}
                        <label>
                            <input type="text" name="{{name}}_to_day"
                                   placeholder="<?php _e('dd', 'woocommerce-bookings'); ?>" size="2"
                                   class="booking_to_date_day"/>
                        </label> / <label>
                            <input type="text" name="{{name}}_to_month"
                                   placeholder="<?php _e('mm', 'woocommerce-bookings'); ?>" size="2"
                                   class="booking_to_date_month"/>
                        </label>
                        {{/if}}
                        / <label>
                            <input type="text" value="<?php echo date('Y'); ?>" name="{{name}}_to_year"
                                   placeholder="<?php _e('YYYY', 'woocommerce-bookings'); ?>" size="4"
                                   class="booking_to_date_year"/>
                        </label>
                    </div>
                    {{/if}}
                    <a class="wc-bookings-date-picker-choose-date button"><?php _e('Choose Date', 'woocommerce-bookings'); ?></a>
                    <div class="picker" data-display='{{{display}}}' data-duration-unit='{{{duration_unit}}}'
                         data-availability='{{{json availability_rules}}}'
                         data-default-availability='{{{json default_availability}}}'
                         data-fully-booked-days='{{{json fully_booked_days}}}'
                         data-partially-booked-days='{{{json partially_booked_days}}}'
                         data-buffer-days='{{{json buffer_days}}}' data-min_date='{{{min_date_js}}}'
                         data-max_date='{{{max_date_js}}}' data-default_date='{{{default_date}}}'
                         data-is_range_picker_enabled='{{{is_range_picker_enabled}}}'></div>
                </div>
                {{/case}}
                {{#case "month-picker" break=true}}
                <ul class="block-picker">
                    {{#each blocks as |block blockKey|}}
                    <li data-block="{{date 'Ym' block}}"><a href="#" class="button" data-value="{{date 'Y-m' block}}">{{date
                            'M y' block}}</a></li>
                    {{/each}}
                </ul>
                <input type="hidden" name="{{name}}_yearmonth" id="{{name}}"/>
                {{/case}}
                {{#case "hidden" break=true}}
                <input
                        type="hidden"
                        value="{{min}}"
                        step="{{step}}"
                        min="{{min}}"
                        max="{{max}}"
                        name="{{name}}"
                        id="{{name}}"
                /> {{{after}}}
                {{/case}}
                {{#case "number" break=true}}
                <input
                        type="number"
                        value="{{min}}"
                        step="{{step}}"
                        min="{{min}}"
                        max="{{max}}"
                        name="{{name}}"
                        id="{{name}}"
                /> {{{after}}}
                {{/case}}
                {{#case "select" break=true}}
                <select name="{{name}}" id="{{name}}">
                    {{#each options}}
                    <option value="{{@key}}">{{this}}</option>
                    {{/each}}
                </select>
                {{/case}}
                {{/switch}}
            </td>
        </tr>
        {{#switch type}}
        {{#case "datetime-picker" break=true}}
        <tr>
            <td><?php _e('Time', 'wc_point_of_sale'); ?></td>
            <td>
                <ul class="block-picker">
                    <li><?php _e('Choose a date above to see available times.', 'wc_point_of_sale'); ?></li>
                </ul>
                <input type="hidden" class="required_for_calculation" name="{{name}}_time" id="{{name}}"/>
            </td>
        </tr>
        {{/case}}
        {{/switch}}
        {{/each}}
        </tbody>
    </table>
</script>

<script type="text/template" id="tmpl-search-customer-result">
    <div class="user-item" data-id="{{id}}">
        <div class="avatar">
            <img class="avatar avatar-64 photo avatar-default" src="{{avatar_url}}" alt="" width="64" height="64">
        </div>
        <div class="user-name">
            {{{fullname}}}
        </div>
    </div>
</script>