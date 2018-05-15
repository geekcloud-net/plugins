<?php

unset( $wpl_feed->types );

?><html>
<head>
    <title>feed results</title>
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

<body>

    <h2>Processing Report for feed <?php echo $wpl_feed->FeedSubmissionId ?></h2>

    <h3>Details</h3>
    Feed Submission ID: <?php echo $wpl_feed->FeedSubmissionId ?><br>
    Feed Type: <?php echo $wpl_feed->FeedType ?><br>

    <h3>Submission Result</h3>

    <?php if ( is_array($wpl_rows) && ( sizeof($wpl_rows)>0 ) ) : ?>

        <?php
            // check if processing result has required default columns - error-code (which is renamed to code)
            $is_localized_result = false;
            $first_row = reset($wpl_rows);
            if ( ! isset($first_row['code']) ) $is_localized_result = true;
        ?>
        <?php if ( $is_localized_result ) : ?>
            <div id="message" class="error">
                <p>
                    <b><?php echo __('This report seems to use localized column headers and can not be processed.','wpla') ?></b>
                </p>
                <p>
                    To change the default language used in reports, please log in to Seller Central, visit  
                    <i>Settings &raquo; Account Info &raquo; Feed Processing Report Language &raquo; Edit</i> - and select <i>English (US)</i>.
                </p>
            </div>
            <br>
        <?php endif; ?>

        <table class="csv-table">
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
                    <td><?php echo $value ?></td>
                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

    <?php elseif ( $wpl_result_header ) : ?>
        <pre><?php echo $wpl_result_header ?></pre>
    <?php else : ?>
        Result hasn't been loaded yet.
    <?php endif; ?>

    <!-- <h3>Debug Data</h3> -->
    <!-- <pre><?php #print_r( $wpl_feed ) ?></pre> -->

    <p>
        <a href="admin.php?page=wpla-feeds&amp;action=wpla_download_feed_results&amp;amazon_feed=<?php echo $wpl_feed->id ?>&amp;_wpnonce=<?php echo wp_create_nonce( 'wpla_download_feed_results' ); ?>" class="button">Download CSV</a>
    </p>


</body>
</html>
