<?php

    $pricing_info = maybe_unserialize( $wpl_item['pricing_info'] );
    $buybox_data  = maybe_unserialize( $wpl_item['buybox_data'] );
    $loffer_data  = maybe_unserialize( $wpl_item['loffer_data'] );

?><html>
<head>
    <title>Pricing Details</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <style type="text/css">
        /*body,td,p { color:#2f2f2f; font:12px/16px "Open Sans",sans-serif; }*/
        body, td, th {
            font-size: .8em;
            font-family: Helvetica Neue,Helvetica,sans-serif;
        }

        pre {
            background-color: #eee;
            border: 1px solid #ccc;
            padding: 20px;
            font-size: 1.2em;
        }


        /*body.wpla_pnq_log table th,*/
        table.csv-table th {
            text-align: center;
        }
        table.csv-table td {
            text-align: center;
        }

        .csv-table {
            width: 100%;
            border: 1px solid #B0B0B0;
        }
        .csv-table tbody {
            /* Kind of irrelevant unless your .css is alreadt doing something else */
            margin: 0;
            padding: 0;
            border: 0;
            outline: 0;
            /*font-size: 100%;*/
            vertical-align: baseline;
            background: transparent;
        }
        .csv-table thead {
            text-align: left;
        }
        .csv-table thead th {
            background: -moz-linear-gradient(top, #F0F0F0 0, #DBDBDB 100%);
            background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, #F0F0F0), color-stop(100%, #DBDBDB));
            filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#F0F0F0', endColorstr='#DBDBDB', GradientType=0);
            border: 1px solid #B0B0B0;
            color: #444;
            /*font-size: 16px;*/
            font-weight: bold;
            padding: 3px 10px;
        }
        .csv-table td {
            padding: 3px 10px;
        }
        .csv-table tr:nth-child(even) {
            background: #F2F2F2;
        }

    </style>
</head>

<body class="wpla_pnq_log">

    <h2>Pricing Info Details for SKU <?php echo $wpl_item['sku'] ?></h2>

    <p>
        The information below was fetched via the Amazon API 
        <b><?php echo human_time_diff( strtotime($wpl_item['pricing_date'].' UTC') ); ?> ago</b> 
        at <?php echo $wpl_item['pricing_date'] ?>.<br>
    </p>

    <h4>Buy Box:
        <?php echo number_format( $wpl_item['buybox_price'], 2 ) ?>
        <?php if ( $wpl_item['has_buybox'] ) : ?>
            &nbsp;<img src="<?php echo WPLA_URL ?>/img/icon-success-32x32.png" style="height:16px; vertical-align:text-bottom;" />
        <?php endif; ?>
    </h4>        
    <table class="csv-table">
        <tr><th style="width:140px;">            
            Condition
        </th><th>
            LandedPrice
        </th><th>
            ListingPrice
        </th><th>
            Shipping
        </th><th>
            Is your price
        </th></tr>

        <?php foreach ( $buybox_data as $price ) : ?>

            <tr><td>
                <?php echo $price->condition ?>
                <?php if ( $price->condition != $price->subcondition ) : ?>
                    / <?php echo $price->subcondition ?>
                <?php endif; ?>
            </td><td>                
                <span style="font-weight: <?php echo $price->LandedPrice == $wpl_item['buybox_price'] ? 'bold' : 'normal' ?> ">
                    <?php echo number_format( $price->LandedPrice, 2 ) ?>
                </span>
            </td><td>
                <?php echo number_format( $price->ListingPrice, 2 ) ?>
            </td><td>
                <?php echo number_format( $price->Shipping, 2 ) ?>
            </td><td style="width:80px;">
                <?php echo $price->belongsToRequester ? 'yes' : 'no' ?>
            </td></tr>

        <?php endforeach; ?>

    </table>

   
    <h4>Lowest Offer:
        <?php echo number_format( $wpl_item['loffer_price'], 2 ) ?>
    </h4>        
    <table class="csv-table">
        <tr><th style="width:140px;">            
            Condition
        </th><th>
            LandedPrice
        </th><th>
            ListingPrice
        </th><th>
            Shipping
        </th><th>
            Offers
        </th></tr>

        <?php foreach ( $loffer_data as $price ) : ?>

            <tr><td>
                <?php echo $price->condition ?>
                <?php if ( $price->condition != $price->subcondition ) : ?>
                    / <?php echo $price->subcondition ?>
                <?php endif; ?>
            </td><td>
                <span style="font-weight: <?php echo $price->LandedPrice == $wpl_item['loffer_price'] ? 'bold' : 'normal' ?> ">
                    <?php echo number_format( $price->LandedPrice, 2 ) ?>
                </span>
            </td><td>
                <?php echo number_format( $price->ListingPrice, 2 ) ?>
            </td><td>
                <?php echo number_format( $price->Shipping, 2 ) ?>
            </td><td style="width:80px;">
                <?php echo $price->NumberOfOfferListingsConsidered ?>
            </td></tr>

        <?php endforeach; ?>

    </table>

    <p>
        The data returned by Amazon's API is not always accurate - 
        for example, there might be other offers at different prices - 
        but the repricing tool can only work based on the numbers you see on this page.
    </p>

   

    <br>
    <a href="#" onclick="jQuery('#wpla_pricing_details_debug').slideToggle();return false;" class="button">Debug Data</a> &nbsp;
    <div id="wpla_pricing_details_debug" style="display:none">       

        <h2>Debug Data</h2>        

        <h3>Buy Box results</h3>
        <pre><?php print_r( $buybox_data ); ?></pre>

        <h3>Lowest Offer results</h3>        
        <pre><?php print_r( $loffer_data ); ?></pre>

        <h3>pricing_info (deprecated)</h3>        
        <pre><?php print_r( $pricing_info ); ?></pre>

        <?php 
            unset( $wpl_item['details'] );
            unset( $wpl_item['attributes'] );
            unset( $wpl_item['history'] );
            unset( $wpl_item['pricing_info'] );
            unset( $wpl_item['buybox_data'] );
            unset( $wpl_item['loffer_data'] );
        ?>
        <h3>Listing Item</h3>        
        <pre><?php print_r( $wpl_item ); ?></pre>

    </div>

</body>
</html>
