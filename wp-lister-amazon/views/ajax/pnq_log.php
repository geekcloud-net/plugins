<?php
?><html>
<head>
    <title>SKU Changelog</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <style type="text/css">
        /*body,td,p { color:#2f2f2f; font:12px/16px "Open Sans",sans-serif; }*/
        body, td, th {
            font-size: .8em;
            font-family: Helvetica Neue,Helvetica,sans-serif;
        }

        /*body.wpla_pnq_log table th,*/
        table.csv-table th {
            text-align: center;
        }
        table.csv-table td {
            text-align: right;
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

    <h2>Recent Price &amp; Quantity changes for SKU <?php echo $wpl_sku ?></h2>


    <table class="csv-table">
        <tr><th>            
            Date
        </th><th>
            Batch ID
        </th><th>
            Price
        </th><th>
            Min.
        </th><th>
            Max.
        </th><th>
            Qty
        </th><th>
            Status
        </th></tr>

        <?php foreach ( $wpl_log_rows as $row ) : ?>

            <tr><td width="25%" style="text-align:center;">
                <?php echo $row['CompletedProcessingDate'] ?>
                <?php if ( $row['CompletedProcessingDate'] ) : ?>
                    <br><span style="color:silver"><?php echo human_time_diff( strtotime($row['CompletedProcessingDate'].' UTC') ) ?> ago</span>
                <?php endif; ?>
            </td><td style="text-align:center;">
                <?php 
                    // get feed permalink
                    $feed_id        = $row['feed_id'];
                    $feed_permalink = admin_url( 'admin-ajax.php?action=wpla_feed_details' ) . '&id='.$feed_id.'&sig='.md5( $feed_id . get_option('wpla_instance') );
                    $feed_linktitle = $row['FeedSubmissionId'] ? $row['FeedSubmissionId'] : $feed_id;
                ?>
                <a href="<?php echo $feed_permalink ?>" target="_blank"><?php echo $feed_linktitle ?></a>
            </td><td>
                <?php echo number_format_i18n( $row['price'], 2 ) ?>
            </td><td>
                <?php echo number_format_i18n( $row['minimum-seller-allowed-price'], 2 ) ?>
            </td><td>
                <?php echo number_format_i18n( $row['maximum-seller-allowed-price'], 2 ) ?>
            </td><td>
                <?php echo $row['quantity'] ?>
            </td><td style="text-align:center;">
                <?php echo $row['FeedProcessingStatus'] ?>
            </td></tr>

        <?php endforeach; ?>

    </table>
   
    <!-- <h2>Debug Data</h2>         -->
    <!-- <pre><?php #print_r( $d ); ?></pre> -->

</body>
</html>
