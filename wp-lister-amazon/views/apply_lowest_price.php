<?php

    $wpl_default_lowest_price_selection = get_option( 'wpla_default_lowest_price_selection', '' );

    #echo "<pre>";print_r($wpl_product);echo"</pre>";#die();

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
        .wpla_match_results td {
            /*background-color:#ffc;*/
            /*border-bottom: 1px solid #ccc;*/
            vertical-align: top;
        }
        .wpla_match_results td.img {
            height:75px; 
            text-align: center; 
            vertical-align: middle
        }
        .wpla_match_results td.info {
            padding: 10px 15px;
        }
        .wpla_match_results td.info a {
            margin-top: 1em;
        }
        .wpla_match_results tr:hover td.hover {
            /*background-color:#ffe;*/
        }

        #wpla_price_matcher_query_form {
            float: right;
            width: 66%;
        }
        #wpla_price_matcher_query_form {
            /*float: right;*/
            width: 98%;
            position: absolute;
            bottom: 0;
            left: 0;
            background-color: #eee;
            padding: 1%;
        }
        #wpla_matcher_query_input {
            width: 14%;
            text-align: right;
        }
        #wpla_matcher_price_type_select {
            width: 34%;
        }
        #wpla_price_matcher_query_form .button {
            width: 24%;
        }

        #wpla_price_matcher_query_form label {
        	width: 25%;
            display: inline-block;
            text-align: right;
        }

    </style>
</head>

<body>

    <!-- <h3>Matching products for <i><?php #echo $wpl_query ?></i> -->

        <!-- <form id="custom_query_form" method="post" action="<?php echo $wpl_form_action; ?>" > -->
        <!-- <form id="wpla_price_matcher_query_form" method="post" action="admin-ajax.php" onsubmit="WPLA.PriceMatcher.applyPrice();event.preventDefault();return false;"> -->
        <form id="wpla_price_matcher_query_form" action="#">
            <input type="hidden" name="action"     value="wpla_apply_lowest_price" />
            <input type="hidden" name="listing_id" value="<?php echo esc_attr( $_REQUEST['id'] ) ?>" />
            <input type="hidden" name="post_id"    value="<?php echo $wpl_post_id ?>" />

            <label for="new_price">Lowest price on Amazon:</label>
            <input type="text"   name="new_price"  value="<?php echo number_format($wpl_lowest_price,2) ?>" id="wpla_matcher_query_input" placeholder="Enter your price" />
            
            <select id="wpla_matcher_price_type_select" name="price_type_select" class="select">
                <option value=""     <?php if ( $wpl_default_lowest_price_selection != 'sale' ): ?>selected="selected"<?php endif; ?> ><?php echo __('Use as Regular Price','wpla'); ?></option>
                <option value="sale" <?php if ( $wpl_default_lowest_price_selection == 'sale' ): ?>selected="selected"<?php endif; ?> ><?php echo __('Use as Sale Price','wpla');   ?></option>
            </select>
            <input type="submit" name="submit" value="<?php echo 'Apply Price' ?>"   onclick="WPLA.PriceMatcher.applyPrice();return false;" class="button" />
        </form>

    <!-- </h3> -->
 
    <table class="wpla_match_results" style="width:100%; margin-top:2em;">
    
        <tr><td class="info hover">
            <big><?php echo $wpl_product->post->post_title ?></big><br>

            SKU: <?php echo $wpl_product->get_sku() ?><br>

            <!-- Regular Price: <?php echo wc_price( $wpl_product->get_regular_price() ) ?><br> -->
            <!-- Sale Price: <?php echo wc_price( $wpl_product->get_sale_price() ) ?><br> -->

        </td><td class="info hover" style="text-align:right; width:20%;">

            <?php if ( $wpl_product->get_sale_price() ) : ?>
                <big><del><?php echo wc_price( $wpl_product->get_regular_price() ) ?></del></big>
                <big><?php echo wc_price( $wpl_product->get_sale_price() ) ?></big>
                <br>
            <?php else : ?>
                <big><?php echo wc_price( $wpl_product->get_regular_price() ) ?></big>
                <br>
            <?php endif; ?>
    
            <a href="post.php?post=<?php echo $wpl_post_id ?>&amp;action=edit" target="_blank" class="button button-small">
                View Product
            </a>
            <!--
            <a href="#" onclick="WPLA.PriceMatcher.match(this,'<?php echo $wpl_post_id ?>','<?php echo $wpl_product->ASIN ?>');return false;" class="button button-small">
                select
            </a>
            -->
        </td></tr>

        <?php if ( $wpl_listing['min_price'] || $wpl_listing['min_price'] ) : ?> 
        <tr><td colspan="2">

            <br>
            Your min. price: <?php echo wc_price( $wpl_listing['min_price'] ) ?><br>
            Your max. price: <?php echo wc_price( $wpl_listing['max_price'] ) ?><br>

        </td></tr>
        <?php endif; ?>

    </table>


    <!-- <h3>Debug</h3> -->
    <!-- <pre><?php #print_r( $wpl_products ) ?></pre> -->

    <script type="text/javascript">

        jQuery('#wpla_matcher_price_type_select').change(function(){
            // var selected_property = jQuery('#wpla_matcher_price_type_select').val();
            var selected_property = jQuery('#wpla_matcher_price_type_select').find(":selected").data('value');
            // alert(selected_property);

            jQuery('#wpla_matcher_query_input').attr('value', selected_property );

            return false;
        });
        // jQuery('#wpla_matcher_price_type_select').change();

    </script>


</body>
</html>
