<?php
$shipping = new WC_Shipping();
?>
<div class="md-modal md-dynamicmodal md-close-by-overlay md-register" id="modal-add_custom_shipping">
    <div class="md-content">
        <h1><?php _e('Shipping', 'wc_point_of_sale'); ?><span class="md-close"></span></h1>
        <div class="full-height">
            <div class="two_cols">
                <div class="box_content col-1">
                    <table id="custom_shipping_table" cellspacing="0" cellpadding="0">
                        <thead>
                        <tr>
                            <th class="shipping_title">
                                <?php _e('Shipping Method', 'wc_point_of_sale'); ?>
                            </th>
                            <th class="shipping_price">
                                <?php _e('Price (' . get_woocommerce_currency_symbol() . ')', 'wc_point_of_sale'); ?>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td class="shipping_title"><input type="text" id="custom_shipping_title"></td>
                            <td class="shipping_price"><input type="text" id="custom_shipping_price"></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-2">
                    <div class="custom-shipping-fields">
                        <h3>
                            <input type="checkbox" value="1" id="custom-add-shipping-details" class="input-checkbox"
                                   style="margin: 5px 10px 5px 0;">
                            <label class="checkbox"
                                   for="custom-add-shipping-details"><?php _e('Add shipping details', 'wc_point_of_sale'); ?></label>
                        </h3>
                        <div id="custom-shipping-details-wrap">
                            <h3> <?php _e('Shipping Address', 'wc_point_of_sale'); ?> </h3>
                            <div id="custom-shipping-shippingaddress"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="wrap-button">
            <button class="button button-primary wp-button-large alignright"
                    id="add_custom_shipping"><?php _e('Add Shipping', 'wc_point_of_sale'); ?></button>
            <div class="clearfix"></div>
        </div>
    </div>
</div>