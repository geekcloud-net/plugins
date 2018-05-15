<?php if ($register_cart['cart_contents']) { ?>
    <span class="bill_screen_header"><?php _e('Your Order', 'wc_point_of_sale'); ?></span>
    <div class="cart-items">
    <?php foreach ($register_cart['cart_contents'] as $product) { ?>
        <div class="row">
            <span class="product-total"><?php echo wc_price($product['quantity'] * $product['price']) ?></span>
            <span class="prod-title"><?php echo $product['data']['title'] ?></span><br>
            <span class="prod-qty"><?php echo $product['quantity'] ?></span> × <span class="prod-price"><?php echo $product['formatedprice'] ?></span><br>
            <?php if ($product['variation']) { ?>
                <em class="variations">
                    <?php foreach ($product['variation'] as $key => $val) { ?>
                        <span class="variaton"><?php echo $key . ': ' . $val ?></span><br>
                    <?php } ?>
                </em>
            <?php } ?>
        </div>
    <?php } ?>
    </div>
    <div class="cart-total">
        <div class="row">
            <?php _e('Subtotal', 'wc_point_of_sale') ?>
            <?php echo wc_price($register_cart['subtotal_ex_tax']); ?>
        </div>
        <div class="row">
            <?php _e('VAT', 'wc_point_of_sale') ?>
            <?php echo wc_price($register_cart['tax_total']); ?>
        </div>
        <div class="row">
            <?php _e('Total', 'wc_point_of_sale') ?>
            <?php echo wc_price($register_cart['total']); ?>
        </div>
    </div>
<?php } else { ?>

    <div class="empty-cart-message"><span class="empty-cart-message-content"><?php _e('Next Customer Please', 'wc_point_of_sale') ?></span></div>
<?php } ?>

