<?php

    $d = $wpl_amazon_order['details'];

?><html>
<head>
    <title>Amazon Order <?php echo $wpl_amazon_order['order_id'] ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <style type="text/css">
        body,td,p { color:#2f2f2f; font:12px/16px "Open Sans",sans-serif; }
    </style>
</head>

<body>

    <h2>Details for Order <?php echo $wpl_amazon_order['order_id'] ?></h2>

    <table width="100%" border="0">
        <tr>
            <td width="20%">            
                <b>Date:</b>
            </td><td>
                <?php echo $wpl_amazon_order['date_created'] ?>
            </td>
        </tr>
        <tr>
            <td>            
                <b>Status:</b>
            </td><td>
                <?php echo $d->OrderStatus ?>
            </td>
        </tr>
        <tr>
            <td>            
                <b>Buyer Name:</b>
            </td><td>
                <?php echo @$d->BuyerName ?>
            </td>
        </tr>
        <tr>
            <td>            
                <b>Buyer Email:</b>
            </td><td>
                <?php echo @$d->BuyerEmail ?>
            </td>
        </tr>
    </table>

        
    <h2>Shipping and Payment</h2>

    <table width="100%" border="0">
        <tr><td width="50%" valign="top">
            
            <?php if (isset($d->ShippingAddress)): ?>
                <b>Shipping address:</b><br>
                <?php echo $d->ShippingAddress->Name ?> <br>
                <?php if (isset($d->ShippingAddress->AddressLine1)): ?>
                <?php echo $d->ShippingAddress->AddressLine1 ?> <br>
                <?php endif; ?>
                <?php if (isset($d->ShippingAddress->AddressLine2)): ?>
                <?php echo $d->ShippingAddress->AddressLine2 ?> <br>
                <?php endif; ?>

                <!-- US address format -->
                <?php echo $d->ShippingAddress->City ?>, 
                <?php echo @$d->ShippingAddress->StateOrRegion ?>
                <?php echo @$d->ShippingAddress->PostalCode ?> <br>
                <?php echo $d->ShippingAddress->CountryCode ?> <br>

                <!-- EU address format -->
                <!-- <?php echo @$d->ShippingAddress->PostalCode ?>  -->
                <!-- <?php echo $d->ShippingAddress->City ?> <br> -->
                <!-- <?php echo $d->ShippingAddress->CountryCode ?> <br> -->

                <br>
            <?php endif; ?>
            <b>Shipping service:</b><br>
            <?php echo $d->ShipServiceLevel ?> <br>
            <br>

        </td><td width="50%" valign="top">

            <b>Payment address:</b><br>
            <?php if ( @$d->Buyer->RegistrationAddress ) : ?>
                <?php echo $d->Buyer->RegistrationAddress->Name ?> <br>
                <?php if (isset($d->Buyer->RegistrationAddress->AddressLine1)): ?>
                <?php echo $d->Buyer->RegistrationAddress->AddressLine1 ?> <br>
                <?php endif; ?>
                <?php if (isset($d->Buyer->RegistrationAddress->AddressLine2)): ?>
                <?php echo $d->Buyer->RegistrationAddress->AddressLine2 ?> <br>
                <?php endif; ?>
                <?php echo $d->Buyer->RegistrationAddress->PostalCode ?> 
                <?php echo $d->Buyer->RegistrationAddress->City ?> <br>
                <?php echo $d->Buyer->RegistrationAddress->CountryCode ?> <br>
            <?php else: ?>
                No registration address provided.<br>
            <?php endif; ?>
            <br>
            <b>Payment method:</b><br>
            <?php echo $d->PaymentMethod ?> <br>
            <br>
            
        </td></tr>
    </table>

    <?php if ( is_array( $wpl_amazon_order['items'] ) ) : ?>
    <h2>Purchased Items</h2>

    <table width="100%" border="0">
        <tr><th>            
            SKU / ASIN <br>
        </th><th>
            <?php echo __('Quantity','wpla') ?> 
        </th><th>
            <?php echo __('Name','wpla') ?> <br>
        </th><th>
            <?php echo __('Price','wpla') ?> 
        </th><th>
            <?php echo __('Shipping','wpla') ?> 
        </th></tr>

        <?php foreach ( $wpl_amazon_order['items'] as $item ) : ?>

            <tr><td width="20%">                      
                <?php echo $item->SellerSKU ?> <br>
                <a href="admin.php?page=wpla&amp;s=<?php echo $item->ASIN ?>" target="_blank">
                    <?php echo $item->ASIN ?>
                </a>
            </td><td>
                <?php echo $item->QuantityOrdered ?> 
            </td><td>
                <?php echo $item->Title ?> <br>
                <?php if ( isset( $item->GiftWrapLevel ) ) : ?> 
                    <span style="font-style:italic">Gift wrap option: <?php echo $item->GiftWrapLevel ?> 
                    (+<?php echo $item->GiftWrapPrice->Amount ?> <?php echo $wpl_amazon_order['currency'] ?>)</span>
                <?php endif; ?> 
            </td><td>
                <?php #echo woocommerce_price( $item->ItemPrice->Amount ) ?> 
                <?php echo isset( $item->ItemPrice->Amount ) ? $item->ItemPrice->Amount : 'N/A' ?> 
                <?php echo $wpl_amazon_order['currency'] ?> 
            </td><td>
                <?php #echo woocommerce_price( $item->ShippingPrice->Amount ) ?> 
                <?php echo isset( $item->ShippingPrice->Amount ) ? $item->ShippingPrice->Amount : 'N/A' ?> 
                <?php echo $wpl_amazon_order['currency'] ?> 
                <?php if ( isset( $item->ShippingDiscount->Amount ) && $item->ShippingDiscount->Amount > 0 ) : ?> 
                    <br>( - <?php echo $item->ShippingDiscount->Amount ?> 
                    <?php echo $wpl_amazon_order['currency'] ?> )
                <?php endif; ?> 
            </td></tr>

        <?php endforeach; ?>

    </table>
    <?php endif; ?>
    
    <?php if ( is_array( $wpl_amazon_order['history'] ) ) : ?>

        <h2>History</h2>

        <table width="100%" border="0">
            <tr><th>            
                <?php echo __('Date','wpla') ?> 
            </th><th>
                <?php echo __('Time','wpla') ?> 
            </th><th>
                <?php echo __('Message','wpla') ?> 
            </th><th>
                <?php #echo __('Success','wpla') ?> 
            </th></tr>

            <?php foreach ( $wpl_amazon_order['history'] as $record ) : ?>

                <tr><td width="16%">                      
                    <?php // echo gmdate( get_option( 'date_format' ), $record->time ) ?> 
                    <?php echo gmdate( 'Y-m-d', $record->time ) ?> 
                </td><td width="12%">                      
                    <?php echo gmdate( 'H:i:s', $record->time ) ?> 
                </td><td>
                    <?php echo $record->msg ?> 
                    
                    <?php if ( isset( $record->details['asin'] ) ) : ?>
                        <a href="admin.php?page=wpla&amp;s=<?php echo $record->details['asin'] ?>" target="_blank">
                            &raquo; find item <?php echo $record->details['asin'] ?>
                        </a>
                    <?php endif; ?>

                    <?php if ( isset( $record->details['product_id'] ) ) : ?>
                        <a href="post.php?action=edit&amp;post=<?php echo $record->details['product_id'] ?>" target="_blank">
                            &raquo; edit product <?php echo $record->details['product_id'] ?>
                        </a>
                    <?php endif; ?>

                </td><td>
                    <?php echo $record->success ? '<span style="color:darkgreen;">OK</span>' : '<span style="color:darkred;">FAILED</span>' ?> 
                </td></tr>

            <?php endforeach; ?>

        </table>

    <?php endif; ?>
    
           
    <?php if ( ! empty( $wpl_wc_order_notes ) ) : ?>

        <h2>Order Notes</h2>

        <table width="100%" border="0">
            <tr><th>            
                <?php echo __('Date','wpla') ?> 
            </th><th>
                <?php echo __('Time','wpla') ?> 
            </th><th>
                <?php echo __('Message','wpla') ?> 
            </th></tr>

            <?php foreach ( $wpl_wc_order_notes as $record ) : ?>

                <tr><td width="16%">                      
                    <?php echo gmdate( get_option( 'date_format' ), strtotime($record->comment_date) ) ?> 
                </td><td width="12%">                      
                    <?php echo gmdate( 'H:i:s', strtotime($record->comment_date) ) ?> 
                </td><td>
                    <?php echo $record->comment_content ?> 
                </td></tr>

            <?php endforeach; ?>

        </table>

    <?php endif; ?>
    
    <h2>Debug Data</h2>
    <a href="#" onclick="jQuery(this).hide();jQuery('#wpla_order_details_debug').slideDown();return false;" class="button">Show Debug Info</a>
    <pre id="wpla_order_details_debug" style="display:none"><?php print_r( $wpl_amazon_order ) ?></pre>
    <!-- <a href="admin.php?page=wpla-orders&amp;action=view_amazon_order_details&amp;amazon_order=<?php echo $wpl_amazon_order['id'] ?>" class="button" target="_blank">View in new tab</a> -->

           
    <pre><?php #print_r( $d ); ?></pre>
    <pre><?php #print_r( $wpl_amazon_order ); ?></pre>


</body>
</html>



