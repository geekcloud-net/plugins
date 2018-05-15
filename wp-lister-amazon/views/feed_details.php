<?php

    // enqueue jQuery and button styles
    wp_enqueue_script('jquery');
    wp_enqueue_style('buttons');
    wp_enqueue_style('wp-admin');

    // get feed permalink
    $signature      = md5( $wpl_feed->id . get_option('wpla_instance') );
    $feed_permalink = admin_url( 'admin-ajax.php?action=wpla_feed_details' ) . '&id='.$wpl_feed->id.'&sig='.$signature;

    // clean debug data
    // unset( $wpl_feed->types );

    // page title
    $page_title = $wpl_feed->FeedSubmissionId ? 'Feed '.$wpl_feed->FeedSubmissionId : 'Pendind feed #'.$wpl_feed->id;

?><html>
<head>
    <title><?php echo $page_title ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <?php wp_print_styles(); ?>
    <?php wp_print_scripts(); ?>
    <style type="text/css">
        pre {
        	background-color: #eee;
        	border: 1px solid #ccc;
        	padding: 20px;
        }

        html {
            /*background-color: #fff;*/
        }

        /* nav tabs */
        .tab-content {
            background-color: #fff; 
            padding: 1em; 
            padding-bottom: 1.5em;
        }
        .nav-tab-active, .nav-tab-active:hover {
            border-bottom: 1px solid #fff;
            background: #fff;
        }
        a.nav-tab:focus {
            outline: 0;
            color: #000;
            box-shadow: none;
            -webkit-box-shadow: none;
        }

        body.wp-core-ui,
        body.wp-core-ui td,
        body.wp-core-ui th,
        .csv-table td,
        .csv-table th {
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
        .csv-table tr:nth-child(odd) {
            background: #FFF;
        }


        #support_request_wrap {
            /*margin-top: 15px;*/
            /*padding: 20px;*/
            /*padding-top: 0;*/
            /*background-color:#eee;*/
            /*border: 1px solid #ccc;*/
            /*display: none;*/
        }
        #support_request_wrap label {
            float: left;
            width: 25%;
            line-height: 23px;
        }
        #support_request_wrap .text-input,
        #support_request_wrap textarea {
            width: 70%;
        }

    </style>
</head>

<body class="wp-core-ui">

    <h2 class="nav-tab-wrapper" style="margin-bottom:0;">  
        <a href="#" id="wpla_tab_feed_content" class="nav-tab nav-tab-active">Feed Content</a>  
        <a href="#" id="wpla_tab_feed_results" class="nav-tab"               >Processing Report</a>  
        <a href="#" id="wpla_tab_feed_support" class="nav-tab"               >Support</a>  
    </h2>

    <!-- Feed Content tab -->
    <div class="tab-content wpla_tab_feed_content_wrapper">

        <a href="#" onclick="wpla_hide_empty_table_columns();return false;" class="button" style="float:right;">Toggle empty columns</a>

        <h2 style="margin-top:0;"><?php echo $wpl_feed->getRecordTypeName( $wpl_feed->FeedType ) ?> - Batch ID <?php echo $wpl_feed->FeedSubmissionId ?> <!--(<?php echo $wpl_feed->id ?>)--></h2>

        <!-- <h3>Details</h3> -->
        <table>
            <?php if ( $wpl_feed->template_name ) : ?>
            <tr><td>
                Feed Template
            </td><td>
                : &nbsp; <?php echo $wpl_feed->template_name ?>
            </td></tr>
            <?php endif; ?>
            <tr><td>
                Feed Type
            </td><td>
                : &nbsp; <?php echo $wpl_feed->FeedType ?>
            </td></tr>
            <?php if ( $wpl_feed->SubmittedDate ) : ?>
            <tr><td>
                Submitted at
            </td><td>
                : &nbsp; <?php echo $wpl_feed->SubmittedDate ?>
                ( <?php echo human_time_diff( strtotime($wpl_feed->SubmittedDate.' UTC') ) ?> ago )
            </td></tr>
            <?php endif; ?>
        </table>
        <br>

        <!-- <h3>CSV Data</h3> -->
        <?php if ( is_array($wpl_rows) && ( sizeof($wpl_rows)>0 ) ) : ?>
        <table id="wpla_feed_data_table" class="csv-table">
            <thead>
            <tr>
                <?php foreach ($wpl_rows[0] as $key => $value) : ?>
                    <th class="wpla_csv-<?php echo $key ?>"><?php echo $key ?></th>
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
                            if ( in_array($key, array('sku','item_sku')) ) {
                                $url = 'admin.php?page=wpla&s=' . $value;
                                echo '<a href="'.$url.'" target="_blank">'.$value.'</a>';
                            // } elseif ( in_array($key, array('item_name','item_name')) ) {
                            //     echo utf8_encode( $value );
                            } elseif ( 'http' == substr($value, 0,4) ) {
                                echo '<a href="'.$value.'" target="_blank">'.$value.'</a>';
                            } else {
                                echo strlen($value) > 150 ? substr($value,0,150).'...' : $value;
                            }
                        ?></td>
                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else : ?>
            Feed content hasn't been loaded yet.
        <?php endif; ?>

        <!-- <h3>Debug Data</h3> -->
        <br>
        <pre id="wpla_feed_details_debug" style="display:none"><?php unset( $wpl_feed->types ); print_r( $wpl_feed ) ?></pre>
        <a href="#" onclick="jQuery('#wpla_feed_details_debug').slideToggle();return false;" class="button">Debug Data</a> &nbsp;
        <a href="<?php echo $feed_permalink ?>" class="button">Permalink</a> &nbsp;
        <a href="admin.php?page=wpla-feeds&amp;action=view_amazon_feed_details_raw&amp;amazon_feed=<?php echo $wpl_feed->id ?>&amp;_wpnonce=<?php echo wp_create_nonce( 'wpla_view_feed_details_raw' ); ?>" class="button" target="_blank">View raw feed</a> &nbsp;
        <a href="admin.php?page=wpla-feeds&amp;action=wpla_download_feed_content&amp;amazon_feed=<?php echo $wpl_feed->id ?>&amp;_wpnonce=<?php echo wp_create_nonce( 'wpla_download_feed_content' ); ?>" class="button">Download CSV</a>

    </div>


    <!-- Processing Results tab -->
    <div class="tab-content wpla_tab_feed_results_wrapper" style="display:none;">

        <h2 style="margin-top:0;">Processing Report for feed <?php echo $wpl_feed->FeedSubmissionId ?></h2>
        <!-- Feed Type: <?php echo $wpl_feed->FeedType ?><br> -->

        <?php if ( $wpl_result_header ) : ?>
            <pre style="background-color:transparent; border:none; padding:0;"><?php echo $wpl_result_header ?></pre>
        <?php endif; ?>

        <!-- <h3>Submission Result</h3> -->
        <?php if ( is_array($wpl_result_rows) && ( sizeof($wpl_result_rows)>0 ) ) : ?>

            <?php
            // check if processing result has required default columns - error-code (which is renamed to code)
            $is_localized_result = false;
            $first_row = reset($wpl_result_rows);
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
                    <?php foreach ($wpl_result_rows[0] as $key => $value) : ?>
                        <th><?php echo $key ?></th>
                    <?php endforeach; ?>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($wpl_result_rows as $row) : ?>
                <tr>
                    <?php foreach ($row as $key => $value) : ?>
                        <td><?php echo $value ?></td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

        <?php elseif ( $wpl_result_header ) : ?>
            <!-- <pre><?php #echo $wpl_result_header ?></pre> -->
        <?php else : ?>
            Result hasn't been loaded yet.
        <?php endif; ?>

        <p>
            <a href="admin.php?page=wpla-feeds&amp;action=wpla_download_feed_results&amp;amazon_feed=<?php echo $wpl_feed->id ?>&amp;_wpnonce=<?php echo wp_create_nonce( 'wpla_download_feed_results' ); ?>" class="button">Download CSV</a>
        </p>

    </div>


    <!-- Support tab -->
    <?php
        $msg_content  = "Hi Support,\n\n";
        $msg_content .= "please have a look at this feed for me, will you? I can't get this to work...\n\n";
        $msg_content .= $feed_permalink."\n\n";
        $msg_content .= "Thanks in advance!";
    ?>
    <div class="tab-content wpla_tab_feed_support_wrapper" style="display:none;">
        <h2 style="margin-top:0;">Request Support</h2>
        <div id="support_request_wrap" style="">
            <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>" >
                <?php wp_nonce_field( 'wpla_send_to_support' ); ?>
                <input type="hidden" name="feed_id" value="<?php echo $wpl_feed->id ?>" />
                <input type="hidden" name="send_to_support" value="yes" />

                <!-- <h2><?php echo __('Send to support','wpla') ?></h2> -->
                Please try to provide as many details as possible about what we might need to do to reproduce the issue.
                <br><br>

                <label for="user_name"><?php echo __('Your Name','wpla') ?></label>
                <input type="text" name="user_name" id="user_name" value="" class="text-input" placeholder="Enter your name"/>
                
                <label for="user_email"><?php echo __('Your Email','wpla') ?></label>
                <input type="text" name="user_email" value="<?php echo get_bloginfo ( 'admin_email' ) ?>" class="text-input"/>
                
                <label for="user_msg"><?php echo __('Your Message','wpla') ?></label>
                <textarea name="user_msg" style="height:12em;"><?php echo $msg_content ?></textarea>
                <br style="clear:both"/>

                <input type="submit" value="<?php echo __('Send to support','wpla') ?>" class="button-primary"/>
            </form>         
        </div>
        <!--    
        <p>
            Use the following link to reference this feed when contacting support:
        </p>
        <code><?php echo $feed_permalink ?></code>
        -->    
    </div>




<script type="text/javascript">
    function wpla_hide_empty_table_columns() {

        var Table      = jQuery('#wpla_feed_data_table').first();
        var Columns    = jQuery('#wpla_feed_data_table th');
        var Rows       = jQuery('#wpla_feed_data_table td');
        var key        = '';
        var has_values = null;

        // loop columns
        Columns.each(function( i ) {

            key        = jQuery(this).attr('class');
            has_values = false;

            // check all fields in this column
            jQuery('#wpla_feed_data_table td.'+key).each(function( i ) {

                field_content = jQuery(this).html();
                // console.log('field_content', field_content, field_content.length );

                if ( field_content.length > 0 ) {
                    has_values = true;                  
                }

            });

            // hide column if empty
            if ( ! has_values ) {
                jQuery('#wpla_feed_data_table .'+key).toggle();
            }

            // console.log('key', key );
            // console.log('has_values', has_values );

        }); // each column
    };

    if ( 'function' == typeof wpla_hide_empty_table_columns ) wpla_hide_empty_table_columns();


    // support form
    jQuery( document ).ready( function () {
        
        jQuery('#support_request_wrap form').submit(function() {
            
            if ( jQuery('#support_request_wrap form #user_name').val() == '' ) {
                alert('Please enter your name.');
                return false;
            }

        });

    }); 


    // nav tabs
    jQuery( document ).ready( function () {
        
        jQuery('.nav-tab').click(function() {
            
            jQuery('.nav-tab').removeClass('nav-tab-active');
            jQuery(this).addClass('nav-tab-active');

            jQuery('.tab-content').hide();
            jQuery('.' + this.id + '_wrapper').show();

            return false;
        });

    }); 

</script>


</body>
</html>
