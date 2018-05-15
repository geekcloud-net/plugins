<?php
/**
 * Template for the product list
 */
?>

<script type="text/template" id="tmpl-cart-product-item">
    <tr class="item new_row {{{cart_item_data.data.type}}}" id="{{cart_item_key}}">
        <td class="name">
            <a href="#" class="remove_order_item button tips" data-tip="<?php _e('Remove', 'wc_point_of_sale'); ?>"></a>
            {{#if editable}}
            <a href="#" class="add_custom_meta button tips" data-tip="<?php _e('Edit', 'wc_point_of_sale'); ?>"></a>
            {{/if}}
            {{displayProductItemImage}}
            <span class="product_name_register">{{displayProductItemTitle}}</span>
            <div class="view">
                {{displayProductItemMeta}}
            </div>
        </td>
        <td class="quantity">
            <div class="edit">
                {{#if editable}}
                <input type="text" min="0" autocomplete="off" placeholder="0" value="{{cart_item_data.quantity}}"
                       class="quantity">
                {{else}}
                <input type="text" min="0" autocomplete="off" placeholder="0" value="{{cart_item_data.quantity}}"
                       class="quantity" disabled="disabled">
                {{/if}}
            </div>
        </td>
        <?php do_action('wc_pos_tmpl_cart_product_item_col'); ?>
        <td class="line_cost_total">
            <div class="view">
                <span class="amount">{{{cart_item_data.formatedprice}}}</span>
            </div>
        </td>
    </tr>
    <tr class="item_note new_row" id="item_note-{{cart_item_key}}">
        <td colspan="2">
            <input type="text" name="item_note" placeholder="<?php _e('Add a note...', 'wc_point_of_sale') ?>" class="item_note" data-item="{{cart_item_key}}">
        </td>
        <td class="line_cost">
            <div class="view">
                {{#if editable}}
                <input type="text" class="product_price" placeholder="{{cart_item_data.price}}"
                       value="{{cart_item_data.price}}" data-discountsymbol="currency_symbol" data-percent="0" data-row="{{cart_item_key}}">
                {{else}}
                <input type="text" class="product_price" placeholder="{{cart_item_data.price}}"
                       value="{{cart_item_data.price}}" data-discountsymbol="currency_symbol" data-percent="0"
                       disabled="disabled" data-row="{{cart_item_key}}">
                {{/if}}
            </div>
        </td>
    </tr>
</script>

<script type="text/template" id="tmpl-cart-customer-item">
    <tr data-customer_id="{{id}}" class="item">
        <td class="avatar">
            <img width="64" height="64" class="avatar avatar-64 photo avatar-default" src="{{avatar_url}}" alt="">
        </td>
        <td class="name">
            <a href="#" class="customer-loaded-name show_customer_popup">{{fullname}}</a>
            <br>
            <a href="#" class="customer-loaded-email show_customer_popup">{{email}}</a>
        </td>
        <?php if (isset($GLOBALS['wc_points_rewards'])) {
            global $wc_points_rewards;
            $points_label = $wc_points_rewards->get_points_label(2); ?>
            <td class="customer_points"><span
                        class="customer_points_label"><b>{{points_balance}}</b> <?php echo $points_label ?></span></td>
        <?php } ?>

        <td class="remove_customer">
            <a data-tip="<?php _e('Remove', 'wc_point_of_sale'); ?>" class="remove_customer_row tips" href="#"></a>
        </td>
    </tr>
</script>
<script type="text/template" id="tmpl-cart-default-customer-item">
    <?php
    $user_to_add = absint($this->data['default_customer']);
    if ($user_to_add > 0) {
        ?>
        <tr data-customer_id="<?php echo $user_to_add; ?>" class="item">
            <td class="avatar">
                <?php echo get_avatar($user_to_add, 64); ?>
            </td>
            <td class="name">
                <?php if (!$user_to_add) { ?>
                    <?php echo $username; ?>
                <?php } else { ?>
                    <a href="#"
                       class="customer-loaded-name show_customer_popup"><?php echo $user_data['first_name'] . ' ' . $user_data['last_name']; ?></a>
                    <br>
                    <a href="#" class="customer-loaded-email show_customer_popup"><?php echo $user_data['email']; ?></a>
                <?php } ?>
                <input type="hidden" id="pos_c_user_id" name="user_id" value="<?php echo esc_attr($user_to_add); ?>"/>
                <input type="hidden" id="pos_c_user_data" value='<?php echo esc_attr(json_encode($user_data)); ?>'/>
                <input type="hidden" id="pos_c_billing_addr" value='<?php echo esc_attr(json_encode($b_addr)); ?>'/>
                <input type="hidden" id="pos_c_shipping_addr" value='<?php echo esc_attr(json_encode($s_addr)); ?>'/>
            </td>
            <?php if (isset($GLOBALS['wc_points_rewards'])) {
                global $wc_points_rewards;
                $points_label = $wc_points_rewards->get_points_label(2);
                $points_balance = WC_Points_Rewards_Manager::get_users_points($user_to_add);
                ?>
                <td class="customer_points"><span
                            class="customer_points_label"><b><?php echo $points_balance; ?></b> <?php echo $points_label; ?></span>
                </td>
            <?php } ?>

            <td class="remove_customer">
                <a href="#" class="remove_customer_row tips" data-tip="<?php _e('Remove', 'wc_point_of_sale'); ?>"></a>
            </td>
        </tr>
    <?php } else {
        ?>
        <tr class="item new_row" data-customer_id="0">
            <td class="avatar">
                <?php echo get_avatar(0, 64); ?>
            <td class="name"><?php _e('Guest', 'wc_point_of_sale'); ?></td>
            <?php if (isset($GLOBALS['wc_points_rewards'])) { ?>
                <td class="customer_points"></td>
            <?php } ?>
            <td class="remove_customer">
                <a data-tip="<?php _e('Remove', 'wc_point_of_sale'); ?>" class="remove_customer_row tips" href="#"></a>
            </td>
        </tr>
    <?php }
    ?>
</script>
<script type="text/template" id="tmpl-cart-guest-customer-item">
    <tr class="item" data-customer_id="0">
        <td class="avatar">
            <?php echo get_avatar(0, 64); ?>
        <td class="name"><?php _e('Guest', 'wc_point_of_sale'); ?></td>
        <?php if (isset($GLOBALS['wc_points_rewards'])) { ?>
            <td class="customer_points"></td>
        <?php } ?>
        <td class="remove_customer">
            <a data-tip="<?php _e('Remove', 'wc_point_of_sale'); ?>" class="remove_customer_row tips" href="#"></a>
        </td>
    </tr>
</script>
<script type="text/template" id="tmpl-cart-fee-row">
    {{#each this}}
    <tr data-fee="{{name}}">
        <th class="fee_label">{{name}}<span class="remove-fee"><?php _e('Remove', 'wc_point_of_sale'); ?></span>
        </th>
        <td class="fee_amount"><span>{{{amount}}}</span></td>
    </tr>
    {{/each}}
</script>
<script type="text/template" id="tmpl-cart-tax-row">
    {{#each this}}
    <tr>
        <th class="tax_label">{{label}}</th>
        <td class="tax_amount"><span>{{{amount}}}</span></td>
    </tr>
    {{/each}}
</script>
<script type="text/template" id="tmpl-cart-ship-row">
    <th class="ship_label">{{title}} <span
                id="clear_shipping"><?php _e('Remove', 'wc_point_of_sale'); ?></span></th>
    <td class="ship_amount"><span>{{{price}}}</span></td>
</script>

<script type="text/template" id="tmpl-cart-coupon-code">
    <tr data-coupon="{{coupon_code}}" class="tr_order_coupon order_coupon_{{coupon_code}}">
        <th class="coupon_label"><?php _e('Coupon', 'wc_point_of_sale'); ?><span
                    class="span_clear_order_coupon"><?php _e('Remove', 'wc_point_of_sale'); ?></span></th>
        <td class="coupon_amount">
            <span>
                <span class="coupon_code">{{coupon_code}}</span>
                <span class="formatted_coupon">{{{amount}}}</span>
            </span>
        </td>
    </tr>
</script>
<script type="text/template" id="tmpl-cart-coupon-label">
    <tr data-coupon="{{coupon_code}}" class="tr_order_coupon order_coupon_{{coupon_code}}">
        <th class="coupon_label">{{coupon_label}}<span
                    class="span_clear_order_coupon"><?php _e('Remove', 'wc_point_of_sale'); ?></span></th>
        <td class="coupon_amount">
            <span class="formatted_coupon">{{{amount}}}</span>
        </td>
    </tr>
</script>
<script type="text/template" id="tmpl-cart-pos-discount">
    <tr data-coupon="POS Discount" class="tr_order_coupon order_coupon_pos_discount">
        <th class="coupon_label"><?php _e('Discount', 'wc_point_of_sale'); ?><span
                    class="span_clear_order_coupon"><?php _e('Remove', 'wc_point_of_sale'); ?></span></th>
        <td class="coupon_amount">
            <span>
                <span class="formatted_coupon">{{{amount}}}</span>
            </span>
        </td>
    </tr>
</script>
<script type="text/template" id="tmpl-cart-points-earned">
    <tr id="tr_order_points_earned">
    </tr>
</script>

<script type="text/template" id="tmpl-current-cashier-name">
    <a class="pos_register_user_panel" href="<?php echo $admin_url; ?>profile.php">
        <span class="pos_register_user_image"><img width="64" height="64" class="avatar avatar-64 photo avatar-default"
                                                   src="{{avatar_url}}" alt=""></span>
        <span class="pos_register_user_name">{{display_name}}</span>
    </a>
</script>
<script type="text/template" id="tmpl-locked-register">
    <div>
        <div class="post-locked-avatar"><img width="64" height="64" class="avatar avatar-64 photo avatar-default"
                                             src="{{avatar_url}}" alt=""></div>
        <p tabindex="0">
            {{message}}
        </p>
        <a class="button"
           href="<?php echo admin_url('admin.php?page=wc_pos_registers'); ?>"><?php _e('All Registers', 'wc_point_of_sale'); ?></a>
    </div>
</script>
<script type="text/template" id="tmpl-recurring-total-item">
    <tr class="tr_recurring_total_item">
        <th class="subtotal_label">
            {{#if label}}
            <?php _e('Recurring Totals', 'wc_point_of_sale'); ?>
            {{/if}}
        </th>
        <td class="subtotal_amount">
            {{{recurring_total}}}
            <div class="first-payment-date">
                <small><?php _e('First renewal', 'wc_point_of_sale'); ?>: {{{next_payment_date}}}</small>
            </div>
        </td>
    </tr>
</script>
