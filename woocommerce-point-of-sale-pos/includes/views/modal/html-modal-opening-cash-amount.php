<!-- Add New Customer Popup box -->
<?php
$checkout = new WC_Checkout();
//WC 3.0 Notice
/*if (isset($checkout->checkout_fields['order'])) {
    unset($checkout->checkout_fields['order']['order_comments']);
}*/

$a = isset($checkout->checkout_fields['order']) ? count($checkout->checkout_fields['order']) : 0;
$o = isset($checkout->checkout_fields['pos_custom_order']) ? count($checkout->checkout_fields['pos_custom_order']) : 0;
$c = isset($checkout->checkout_fields['pos_acf']) ? count($checkout->checkout_fields['pos_acf']) : 0;
?>
<div class="md-modal md-dynamicmodal md-close-by-overlay" id="modal-opening_cash_amount">
    <div class="md-content woocommerce">
        <h1><?php _e('Opening Cash', 'wc_point_of_sale'); ?><span class="md-close"></span></h1>
        <div class="opening-cash-content col3-set">
            <p class="form-row form-row-wide validate-required">
                <label for="opening_amount"><?php _e('Amount', 'wc_point_of_sale'); ?></label>
                <input type="number" step="0.01" placeholder="0.00" id="opening_amount" name="opening_amount">
            </p>
            <p class="form-row form-row-wide validate-required">
                <label for="opening_amount_note"><?php _e('Note', 'wc_point_of_sale'); ?></label>
                <input type="text" placeholder="<?php _e('Type to add a note', 'wc_point_of_sale') ?>" id="opening_amount_note" name="opening_amount_note">
            </p>
        </div>
        <div class="wrap-button">
            <button class="button button-primary wp-button-large alignright" type="button" id="set_opening_cash_amount">
                <?php _e('Set Amount', 'wc_point_of_sale'); ?>
            </button>
        </div>
    </div>
</div>