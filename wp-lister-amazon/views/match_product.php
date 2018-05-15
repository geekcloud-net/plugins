<?php

    $wpl_default_matcher_selection = get_option( 'wpla_default_matcher_selection', 'title' );
    if ( ! $wpl_query_select ) $wpl_query_select = $wpl_default_matcher_selection;

    // // get market_url for default account
    // $account_id = get_option('wpla_default_account_id');
    // if ( $account_id ) {
    //     $account    = new WPLA_AmazonAccount( $account_id );
    //     $market     = new WPLA_AmazonMarket( $account->market_id );
    //     $market_url = 'http://www.'.$market->url.'/dp/';
    // } else {
    //     $market_url = 'http://www.amazon.com/dp/';
    // }

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
            border-bottom: 1px solid #ccc;
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
            background-color:#ffe;
        }

        #wpla_matcher_query_form {
            float: right;
            width: 66%;
        }
        #wpla_matcher_query_form {
            /*float: right;*/
            width: 98%;
            position: absolute;
            bottom: 0;
            left: 0;
            background-color: #eee;
            padding: 1%;
        }
        #wpla_matcher_query_input {
            width: 65%;
        }
        #wpla_matcher_query_select {
            width: 14%;
        }
        #wpla_matcher_query_form .button {
        	width: 19%;
        }

    </style>
</head>

<body>

    <!-- <h3>Matching products for <i><?php #echo $wpl_query ?></i> -->

        <!-- <form id="custom_query_form" method="post" action="<?php echo $wpl_form_action; ?>" > -->
        <!-- <form id="wpla_matcher_query_form" method="post" action="admin-ajax.php" onsubmit="WPLA.ProductMatcher.submitQuery();event.preventDefault();return false;"> -->
        <form id="wpla_matcher_query_form" action="#">
            <!-- <big>Matching products for </big> -->
            <input type="hidden" name="action" value="<?php echo esc_attr( $_REQUEST['action'] ) ?>" />
            <input type="hidden" name="id"     value="<?php echo esc_attr( $_REQUEST['id'] ) ?>" />
            <input type="text"   name="query"  value="<?php echo esc_attr( $wpl_query ) ?>" id="wpla_matcher_query_input" />
            <select id="wpla_matcher_query_select" name="query_select" class="select">
                <option value="title" data-value="<?php echo htmlspecialchars( $wpl_query_product->post->post_title ) ?>" <?php if ( $wpl_query_select == 'title' ): ?>selected="selected"<?php endif; ?> ><?php echo __('Title','wpla'); ?></option>
                <option value="sku"   data-value="<?php echo htmlspecialchars( $wpl_query_product->sku )              ?>" <?php if ( $wpl_query_select == 'sku'   ): ?>selected="selected"<?php endif; ?> ><?php echo __('SKU','wpla');   ?></option>
                <?php foreach ($wpl_query_product_attributes as $attribute_label => $attribute_value) : ?>
                    <option value="<?php echo $attribute_label ?>" data-value="<?php echo htmlspecialchars( $attribute_value ) ?>" <?php if ( $wpl_query_select == $attribute_label ): ?>selected="selected"<?php endif; ?> ><?php echo $attribute_label ?></option>
                <?php endforeach; ?>
            </select>
            <input type="submit" name="submit" value="<?php echo 'Search' ?>"   onclick="WPLA.ProductMatcher.submitQuery();return false;" class="button" />
        </form>

    <!-- </h3> -->
 
    <?php if ( is_array($wpl_products) && ! empty($wpl_products) ) : ?>
    <table class="wpla_match_results" style="width:100%">
    <?php foreach ($wpl_products as $product ) : ?>
    
        <tr><td class="img">
            <a href="http://www.<?php echo $wpl_market_url ?>/dp/<?php echo $product->ASIN ?>/" title="Click on the image to open this product on Amazon" target="_blank">
                <img src="<?php echo $product->AttributeSets->ItemAttributes->SmallImage->URL ?>" />
            </a>
        </td><td class="info hover">
            <?php echo $product->AttributeSets->ItemAttributes->Title ?><br>

            <?php if ( isset( $product->AttributeSets->ItemAttributes->Brand ) ) : ?>
                Brand: <?php echo $product->AttributeSets->ItemAttributes->Brand ?><br>
            <?php endif; ?>

            <?php if ( isset( $product->AttributeSets->ItemAttributes->Model ) ) : ?>
                Model: <?php echo $product->AttributeSets->ItemAttributes->Model ?><br>
            <?php endif; ?>

        </td><td class="info hover" style="text-align:right; width:20%;">

            <?php if ( isset( $product->lowest_price ) && $product->lowest_price ) : ?>
                <big><?php echo wc_price( $product->lowest_price ) ?></big>
                <br>
            <?php elseif ( isset( $product->AttributeSets->ItemAttributes->ListPrice ) ) : ?>
                <big><?php echo $product->AttributeSets->ItemAttributes->ListPrice->Amount ?>&nbsp;<?php echo $product->AttributeSets->ItemAttributes->ListPrice->CurrencyCode ?></big>
                <br>
            <?php endif; ?>
    
            <a href="http://www.<?php echo $wpl_market_url ?>/dp/<?php echo $product->ASIN ?>/" target="_blank" class="button button-small">
                view
            </a>
            <a href="#" onclick="WPLA.ProductMatcher.match(this,'<?php echo $wpl_post_id ?>','<?php echo $product->ASIN ?>');return false;" class="button button-small">
                select
            </a>
        </td></tr>

    <?php endforeach; ?>
    </table>
    <?php else : ?>
        <p>
            <?php echo sprintf( __('There were no products found for query %s.','wpla'), $wpl_query ) ?>
        </p>
    <?php endif; ?>


    <!-- <h3>Debug</h3> -->
    <!-- <pre><?php #print_r( $wpl_products ) ?></pre> -->

    <script type="text/javascript">

            jQuery('#wpla_matcher_query_select').change(function(){
                // var selected_property = jQuery('#wpla_matcher_query_select').val();
                var selected_property = jQuery('#wpla_matcher_query_select').find(":selected").data('value');
                // alert(selected_property);

                jQuery('#wpla_matcher_query_input').attr('value', selected_property );

                return false;
            });
            // jQuery('#wpla_matcher_query_select').change();

    </script>


</body>
</html>
