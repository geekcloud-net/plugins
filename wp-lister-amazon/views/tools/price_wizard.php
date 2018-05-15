<?php

    $defaults = array(
        'min_base_price'       => '',
        'max_base_price'       => '',
        'min_price_amount'     => '',
        'max_price_amount'     => '',
        'min_price_percentage' => '',
        'max_price_percentage' => '',
    );

    // load last used options
    $last_used_options = get_option('wpla_price_wizard_options');
    if ( $last_used_options && is_array($last_used_options) ) 
        $defaults = array_merge( $defaults, $last_used_options );

    // echo "<pre>";print_r($defaults);echo"</pre>";die();
?><html>
<head>
    <title>request details</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <style type="text/css">
        pre {
        	background-color: #eee;
        	border: 1px solid #ccc;
        	padding: 20px;
        }

        #wpla_minmax_wizard_form input {
            max-width: 175px;
        }

    </style>
</head>

<body>

    <h3>Min./Max. Price Wizard</h3>

    <p>
        Minimum and maxmimum prices are required to use the Repricing Tool.
    </p>
    <p>
        This tool will automatically calculate minimum and maximum prices using a custom formula:
    </p>

    <form id="wpla_minmax_wizard_form" method="post" action="<?php echo $wpl_form_action; ?>" >
    <!-- <form id="wpla_minmax_wizard_form" action="#"> -->
        <input type="hidden" name="action" value="wpla_bulk_apply_minmax_prices" />
        <input type="hidden" name="item_ids" value="<?php echo join( ',', $wpl_selected_items ) ?>" />

        <input type="hidden" name="s"                value="<?php echo isset($_REQUEST['s']) ? $_REQUEST['s'] : ''; ?>" />
        <input type="hidden" name="repricing_status" value="<?php echo isset($_REQUEST['repricing_status']) ? $_REQUEST['repricing_status'] : ''; ?>" />
        <input type="hidden" name="buybox_status"    value="<?php echo isset($_REQUEST['buybox_status'])    ? $_REQUEST['buybox_status']    : ''; ?>" />
        <input type="hidden" name="stock_status"     value="<?php echo isset($_REQUEST['stock_status'])     ? $_REQUEST['stock_status']     : ''; ?>" />
        <input type="hidden" name="fba_status"       value="<?php echo isset($_REQUEST['fba_status'])       ? $_REQUEST['fba_status']       : ''; ?>" />
        <input type="hidden" name="fba_age"          value="<?php echo isset($_REQUEST['fba_age'])          ? $_REQUEST['fba_age']          : ''; ?>" />

        <table>
            <tr>
                <td style="width:128px">
                    <?php echo __('Min. Price based on','wpla'); ?>
                </td>
                <td>

                    <select id="wpla_min_base_price" name="min_base_price" class="select">
                        <option value="no_change"   <?php if ( $defaults['min_base_price'] == 'no_change'   ): ?>selected="selected"<?php endif; ?> >-- <?php echo __('no change','wpla'); ?> --</option>
                        <option value="price"       <?php if ( $defaults['min_base_price'] == 'price'       ): ?>selected="selected"<?php endif; ?> ><?php echo __('Regular Price','wpla'); ?></option>
                        <option value="sale_price"  <?php if ( $defaults['min_base_price'] == 'sale_price'  ): ?>selected="selected"<?php endif; ?> ><?php echo __('Sale Price','wpla');   ?></option>
                        <option value="msrp"        <?php if ( $defaults['min_base_price'] == 'msrp'        ): ?>selected="selected"<?php endif; ?> ><?php echo __('MSRP Price','wpla'); ?></option>
                        <option value="fixed"       <?php if ( $defaults['min_base_price'] == 'fixed'       ): ?>selected="selected"<?php endif; ?> ><?php echo __('Fixed Amount','wpla'); ?></option>
                        <option value="remove"      <?php if ( $defaults['min_base_price'] == 'remove'      ): ?>selected="selected"<?php endif; ?> ><?php echo __('Remove min. price','wpla'); ?></option>
                    </select>

                </td>
                <td>
                    <input type="text" name="min_price_percentage" placeholder="enter percentage (-5%)" value="<?php echo $defaults['min_price_percentage'] ?>" />
                </td>
                <td>
                    <input type="text" name="min_price_amount" placeholder="enter amount (-10)" value="<?php echo $defaults['min_price_amount'] ?>" />
                </td>
            </tr>
            <tr>
                <td><?php echo __('Max. Price based on','wpla'); ?></td>
                <td>

                    <select id="wpla_max_base_price" name="max_base_price" class="select">
                        <option value="no_change"   <?php if ( $defaults['max_base_price'] == 'no_change'   ): ?>selected="selected"<?php endif; ?> >-- <?php echo __('no change','wpla'); ?> --</option>
                        <option value="price"       <?php if ( $defaults['max_base_price'] == 'price'       ): ?>selected="selected"<?php endif; ?> ><?php echo __('Regular Price','wpla'); ?></option>
                        <option value="sale_price"  <?php if ( $defaults['max_base_price'] == 'sale_price'  ): ?>selected="selected"<?php endif; ?> ><?php echo __('Sale Price','wpla');   ?></option>
                        <option value="msrp"        <?php if ( $defaults['max_base_price'] == 'msrp'        ): ?>selected="selected"<?php endif; ?> ><?php echo __('MSRP Price','wpla'); ?></option>
                        <option value="fixed"       <?php if ( $defaults['max_base_price'] == 'fixed'       ): ?>selected="selected"<?php endif; ?> ><?php echo __('Fixed Amount','wpla'); ?></option>
                        <option value="remove"      <?php if ( $defaults['max_base_price'] == 'remove'      ): ?>selected="selected"<?php endif; ?> ><?php echo __('Remove max. price','wpla'); ?></option>
                    </select>

                </td>
                <td>
                    <input type="text" name="max_price_percentage" placeholder="enter percentage (+5%)" value="<?php echo $defaults['max_price_percentage'] ?>" />
                </td>
                <td>
                    <input type="text" name="max_price_amount" placeholder="enter amount (+10)" value="<?php echo $defaults['max_price_amount'] ?>" />
                </td>
            </tr>
        </table>

        <p>
            Click below to update minimum and maximum prices for <?php echo count($wpl_selected_items) ?> selected listings.
        </p>

        <!-- <input type="submit" name="submit" value="<?php echo 'Search' ?>"   onclick="WPLA.ProductMatcher.submitQuery();return false;" class="button" /> -->
        <input type="submit" name="submit" value="<?php echo 'Update Prices' ?>" onclick="" class="button button-primary" />

    </form>


    <!-- <h3>Debug</h3> -->
    <!-- <pre><?php print_r( $wpl_selected_items ) ?></pre> -->

    <script type="text/javascript">
    </script>

</body>
</html>
