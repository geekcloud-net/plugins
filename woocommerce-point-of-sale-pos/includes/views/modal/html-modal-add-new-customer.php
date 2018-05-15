<!-- Add New Customer Popup box -->
<?php
$checkout = new WC_Checkout();
//WC 3.0 Notice
/*if( isset($checkout->checkout_fields['order']) ){
    unset($checkout->checkout_fields['order']['order_comments']);
}*/

$a = isset($checkout->checkout_fields['order']) ? count($checkout->checkout_fields['order']) : 0;
$o = isset($checkout->checkout_fields['pos_custom_order']) ? count($checkout->checkout_fields['pos_custom_order']) : 0;
$c = isset($checkout->checkout_fields['pos_acf']) ? count($checkout->checkout_fields['pos_acf']) : 0;
?>
<div class="md-modal md-dynamicmodal md-close-by-overlay md-register" id="modal-order_customer">
    <div class="md-content woocommerce">
        <h1><?php _e('Customer Details', 'wc_point_of_sale'); ?><span class="md-close"></span></h1>
        <h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
            <a href="#pos_billing_details"
               class="nav-tab nav-tab-active"><?php _e('Billing Details', 'wc_point_of_sale'); ?></a>
            <a href="#pos_shipping_details" class="nav-tab"><?php _e('Shipping Details', 'wc_point_of_sale'); ?></a>
            <a href="#pos_additional_fields"
               class="nav-tab" <?php echo !$a ? 'style="display:none;"' : ''; ?> ><?php _e('Additional Fields', 'wc_point_of_sale'); ?></a>
            <a href="#pos_custom_fields"
               class="nav-tab" <?php echo !$c ? 'style="display:none;"' : ''; ?>><?php _e('Custom Fields', 'wc_point_of_sale'); ?></a>
            <a href="#pos_order_fields"
               class="nav-tab" <?php echo !$o ? 'style="display:none;"' : ''; ?>><?php _e('Custom Order Fields', 'wc_point_of_sale'); ?></a>
        </h2>
        <div id="customer_details" class="col3-set">
        </div>
        <div class="wrap-button">
            <button class="button button-primary wp-button-large alignright" type="button" id="save_customer">
                <?php _e('Save Customer', 'wc_point_of_sale'); ?>
            </button>

            <label for="createaccount" class="checkbox alignright" id="create_new_account">
                <input class="input-checkbox" id="createaccount" type="checkbox" value="1"/>
                <?php _e('Create an account?', 'woocommerce'); ?>
            </label>

        </div>
    </div>
</div>