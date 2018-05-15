<?php

    // enqueue jQuery and button styles
    wp_enqueue_script('jquery');
    wp_enqueue_style('buttons');
    // wp_enqueue_style('wp-admin');

    // get report permalink
    $signature        = md5( $wpl_report->ReportRequestId . get_option('wpla_instance') );
    $report_permalink = admin_url( 'admin-ajax.php?action=wpla_report_details' ) . '&rrid='.$wpl_report->ReportRequestId.'&sig='.$signature;

?><html>
<head>
    <title>Report <?php echo $wpl_report->ReportRequestId ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <style type="text/css">
        pre {
        	background-color: #eee;
        	border: 1px solid #ccc;
        	padding: 20px;
        }

        body, td, th {
            font-size: .8em;
            font-family: Helvetica Neue,Helvetica,sans-serif;
        }

        #table-6 {
            width: 100%;
            border: 1px solid #B0B0B0;
        }
        #table-6 tbody {
            /* Kind of irrelevant unless your .css is alreadt doing something else */
            margin: 0;
            padding: 0;
            border: 0;
            outline: 0;
            /*font-size: 100%;*/
            vertical-align: baseline;
            background: transparent;
        }
        #table-6 thead {
            text-align: left;
        }
        #table-6 thead th {
            background: -moz-linear-gradient(top, #F0F0F0 0, #DBDBDB 100%);
            background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, #F0F0F0), color-stop(100%, #DBDBDB));
            filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#F0F0F0', endColorstr='#DBDBDB', GradientType=0);
            border: 1px solid #B0B0B0;
            color: #444;
            /*font-size: 16px;*/
            font-weight: bold;
            padding: 3px 10px;
        }
        #table-6 td {
            padding: 3px 10px;
        }
        #table-6 tr:nth-child(even) {
            background: #F2F2F2;
        }

    </style>
    <?php wp_print_styles(); ?>
    <?php wp_print_scripts(); ?>
</head>

<body class="wp-core-ui">

    <form action="<?php echo $report_permalink ?>" method="POST" style="float:right;">
        <input id="wpla_filter_query" name="query" type="text" class="search" 
            value="<?php echo $wpl_query ?>" 
            placeholder="Search report for..." 
            style="float: left; height: 28px; margin: 0 4px 0 0; border: 1px solid #ccc; padding: 2px 5px; width: 230px;" />
        <input type="submit" class="button" style="float:right;" value="Search" />
        <!-- <a href="#" onclick="wpla_btn_filter_report();return false;" class="button" style="float:right;">Filter</a> -->
    </form>

    <h2>
        Amazon Seller Report <?php echo $wpl_report->ReportRequestId ?>
        <?php if ( $wpl_query ) : ?>
            - showing matches for "<?php echo $wpl_query ?>"
        <?php endif; ?>
    </h2>

    <!-- <h3>Details</h3> -->
    Report Request ID: <?php echo $wpl_report->ReportRequestId ?><br>
    Report Type: <?php echo $wpl_report->ReportType ?> 
    <?php if ( $wpl_total_rows == sizeof($wpl_rows) ) : ?>
        (<?php echo $wpl_total_rows ?> rows)
    <?php else : ?>
        (<?php echo sizeof($wpl_rows) ?> of <?php echo $wpl_total_rows ?> rows)
    <?php endif; ?>

    <br>
    <br>

    <!-- <h3>CSV Data</h3> -->

    <?php if ( is_array($wpl_rows) && ( sizeof($wpl_rows)>0 ) ) : ?>
    <table id="table-6">
        <thead>
        <tr>
            <?php foreach ($wpl_rows[0] as $key => $value) : ?>
                <th><?php echo $key ?></th>
            <?php endforeach; ?>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($wpl_rows as $row) : ?>
        <tr>
            <?php foreach ($row as $key => $value) : ?>
                <?php $value = strip_tags( $value ); ?>
                <td class="wpla_csv-<?php echo $key ?>"
                    style="<?php echo strlen($value) > 50 ? 'min-width:300px;' : '' ?>"
                    ><?php 
                        if ( in_array($key, array('sku','seller-sku','asin1')) ) {
                            $url = 'admin.php?page=wpla&s=' . $value;
                            echo '<a href="'.$url.'" target="_blank">'.$value.'</a>';
                        } elseif ( in_array($key, array('merchant-order-id')) && is_numeric($value) ) {
                            if ( function_exists('wc_seq_order_number_pro') ) {
                                $order_id = wc_seq_order_number_pro()->find_order_by_order_number( $value );
                                $url = 'post.php?action=edit&post=' . $order_id;
                            } else {
                                $url = 'post.php?action=edit&post=' . $value;
                            }

                            echo '<a href="'.$url.'" target="_blank">'.$value.'</a>';
                        // } elseif ( in_array($key, array('item_name','item_name')) ) {
                        //     echo utf8_encode( $value );
                        } elseif ( 'http' == substr($value, 0,4) ) {
                            echo '<a href="'.$value.'" target="_blank">'.$value.'</a>';
                        } else {
                            echo strlen($value) > 150 ? substr($value,0,150).'...' : $value;
                        }
                    ?></td>
                <?php 
                    // $value = strlen($value) > 50 && ! strpos($value,'Error') ? substr($value,0,50).'...' : $value; 
                    // echo htmlspecialchars( $value ); 
                ?>
            <?php endforeach; ?>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php elseif ( $wpl_query ) : ?>
        <p>
            <b>No matching rows were found int this report.</b>
        </p>
    <?php else : ?>
        <p>
            <b>This report has no content - either it's empty or its content hasn't been loaded yet.</b>
        </p>
    <?php endif; ?>

    <br>
    <pre id="wpla_report_details_debug" style="display:none"><?php unset($wpl_report->types); print_r( $wpl_report ); ?></pre>
    <a href="#" onclick="jQuery('#wpla_report_details_debug').slideToggle();return false;" class="button">Debug Data</a> &nbsp;
    <a href="<?php echo $report_permalink ?>" class="button">Permalink</a> &nbsp;
    <a href="admin.php?page=wpla-reports&amp;action=wpla_download_report&amp;amazon_report=<?php echo $wpl_report->id ?>&_wpnonce=<?php echo wp_create_nonce( 'wpla_download_report' ); ?>" class="button button-primary">Download Report</a>


<script type="text/javascript">
    function wpla_btn_filter_report() {

        var Table      = jQuery('#wpla_feed_data_table').first();
        var Columns    = jQuery('#wpla_feed_data_table th');
        var Rows       = jQuery('#wpla_feed_data_table td');
        var key        = '';
        var has_values = null;

    };

</script>

</body>
</html>
