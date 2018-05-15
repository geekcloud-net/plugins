<?php include_once( dirname(__FILE__).'/../common_header.php' ); ?>

<style type="text/css">

	a.right,
	input.button {
		float: right;
	}



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

    /* checkbox column */
    .csv-table thead .check-column {
        text-align: center;
    }
    .csv-table .check-column {
        display:none;
    }

</style>

<div class="wrap">
	<div class="icon32" style="background: url(<?php echo $wpl_plugin_url; ?>img/amazon-32x32.png) no-repeat;" id="wpl-icon"><br /></div>
	<h2>
		<?php if ( $wpl_step == 2 ) : ?>
			<?php echo __('Preview Import','wpla') ?> - <?php echo __('Step','wpla') . ' ' . ($wpl_step-1) ?>
		<?php endif; ?>
		<?php if ( $wpl_step == 3 ) : ?>
			<?php echo __('Import Products','wpla') ?> - <?php echo __('Step','wpla') . ' ' . ($wpl_step-1) ?>
		<?php endif; ?>
		<?php if ( $wpl_step == 4 ) : ?>
			<?php echo __('Import Process Finished','wpla') ?>
		<?php endif; ?>
	</h2>
	<?php echo $wpl_message ?>

    <?php
        // check if report has required default columns - seller-sku and asin/asin1
        $is_invalid_report = false;
        $first_row = reset($wpl_data_rows);
        if ( ! isset($first_row['seller-sku']) ) $is_invalid_report = true;
        // if ( ! isset($first_row['asin']) && ! isset($first_row['asin1']) ) $is_invalid_report = true;
    ?>
    <?php if ( $is_invalid_report ) : ?>
        <div id="message" class="error">
            <p>
                <b><?php echo __('Error: This report seems to use localized column headers and can not be processed.','wpla') ?></b>
            </p>
            <p>
                To change the default language used in reports, please log in to Seller Central, visit  
                <i>Settings &raquo; Account Info &raquo; Feed Processing Report Language &raquo; Edit</i> - and select <i>English (US)</i>.
            </p>
            <p>
                Then wait about 5-10 minutes for Amazon to update your settings before you request a new inventory report.
            </p>
        </div>
    <?php endif; ?>

	<div class="postbox-container" style="width:100%; <?php if ( $is_invalid_report ) echo 'display:none;' ?>">
		<div class="metabox-holder">
			<div class="meta-box-sortables ui-sortable">

				<div class="postbox" id="RunImportBox">
					<h3 class="hndle"><span><?php echo __('Summary','wpla'); ?></span></h3>
					<div class="inside">
						<p>
							<?php if ( $wpl_step == 2 ) : ?>
								<?php echo sprintf( __('Your inventory report for account <b>%s</b> contains <b>%s products</b> in total.','wpla'), $wpl_account->title, count($wpl_report_summary->report_skus) ); ?><br>
								<?php echo __('First click on "Process Report" to update existing listings and products.','wpla'); ?><br>
							<?php endif; ?>
							<?php if ( $wpl_step == 3 ) : ?>
                                <?php // echo sprintf( __('Great, %s rows of your inventory report have been processed.','wpla'), count($wpl_report_summary->report_skus), $wpl_account->title ); // TODO: show actual number of *selected* rows ?>
                                <?php echo __('Your inventory report has been processed.','wpla'); ?><br>
								<?php echo __('Next, click "Import Products" to create missing products in WooCommerce.','wpla'); ?><br>
							<?php endif; ?>
						</p>

						<h4><?php echo __('Step 1: Update Listings and Products','wpla') ?></h4>
						<p>
                            <?php if ( $wpl_reports_update_woo_stock || $wpl_reports_update_woo_price ) : ?>
    							<?php echo sprintf( __('There are <b>%s new listings</b> which will be added to the import queue, <b>%s existing listings</b> and <b>%s existing products</b> will be updated.','wpla'), count($wpl_report_summary->listings_to_import), count($wpl_report_summary->listings_to_update), count($wpl_report_summary->products_to_update) ); ?>
                            <?php else : ?>
                                <?php echo sprintf( __('There are <b>%s new listings</b> which will be added to the import queue and <b>%s existing listings</b> will be updated.','wpla'), count($wpl_report_summary->listings_to_import), count($wpl_report_summary->listings_to_update) ); ?>
                            <?php endif; ?>
						</p>

                        <?php if ( $wpl_reports_update_woo_stock || $wpl_reports_update_woo_price ) : ?>
                        <p>
                            <?php if ( $wpl_reports_update_woo_stock && $wpl_reports_update_woo_price ) : ?>
                                <?php echo __('Existing products will have both price and quantity updated from this report.','wpla'); ?>
                            <?php elseif ( $wpl_reports_update_woo_stock ) : ?>
                                <?php echo __('Note: Existing WooCommerce products will have only the stock quantity updated - prices will not be updated.','wpla'); ?>
                            <?php elseif ( $wpl_reports_update_woo_price ) : ?>
                                <?php echo __('Note: Existing WooCommerce products will have only the price updated - stock levels will not be updated!','wpla'); ?>
                                (<?php echo __('not recommended','wpla'); ?>)
                            <?php endif; ?>
                        </p>
                        <?php endif; ?>

						<p>
							<?php $btn_class = $wpl_step == 2 ? 'button-primary' : 'button-secondary'; ?>
                            <a id="btn_process_amazon_report" data-id="<?php echo $wpl_report_id ?>" class="button button-small wpl_job_button <?php echo $btn_class ?>">
                                <?php echo __('Process full report','wpla'); ?>
                            </a>

                            <a id="btn_process_selected_report_rows" data-id="<?php echo $wpl_report_id ?>" class="button button-small wpl_job_button <?php echo $btn_class ?>" style="display:none;">
                                <?php echo __('Process selected rows','wpla'); ?>
                            </a>

                            <a id="btn_toggle_selection_mode" data-id="<?php echo $wpl_report_id ?>" class="button button-small wpl_job_button">
                                <?php echo __('Select rows to process','wpla'); ?>
                            </a>
						</p>

						<h4><?php echo __('Step 2: Import Products','wpla') ?></h4>
                        <?php // if ( count($wpl_report_summary->products_to_import) ) : ?>
						<?php if ( intval(@$wpl_status_summary->imported) ) : ?>
							<p>
                                <?php if ( $wpl_step == 3 ) : ?>
                                    <!-- step 3: show import queue status -->
                                    <?php echo sprintf( __('There are <b>%s items</b> in the import queue, waiting to be imported to WooCommerce.','wpla'), intval(@$wpl_status_summary->imported) ); ?>
                                <?php else : ?>
                                    <!-- step 2: show report summary info -->
                                    <?php echo sprintf( __('There are <b>%s new products</b> in this report which will be added to WooCommerce.','wpla'), count($wpl_report_summary->products_to_import) ); ?>
                                <?php endif; ?>
							</p>
							<p>
								<?php $btn_class = $wpl_step == 3 ? 'button-primary' : 'button-secondary'; ?>
								<a id="btn_batch_create_products_reminder" class="button button-small wpl_job_button <?php echo $btn_class ?>">
									<?php echo __('Import / Update Products','wpla'); ?>
								</a>
							</p>
						<?php else: ?>
							<p>
								<?php echo __('All products from this report already exist in WooCommerce.','wpla'); ?>
							</p>
						<?php endif; ?>

                        <p>
                            <b><?php echo __('Please note','wpla'); ?>:</b>
                            <?php echo __('Sale prices can not be imported from Amazon and will be <em>removed</em> when an imported product is updated.','wpla'); ?>
                        </p>
						
						<!-- 
						<h4><?php echo __('Totals','wpla') ?></h4>
						<p>
							<?php echo __('Products to be imported','wpla') .': '. count($wpl_report_summary->products_to_import) ?><br>
							<?php echo __('Products to be updated','wpla')  .': '. count($wpl_report_summary->products_to_update) ?><br>
							<?php echo __('Listings to be imported','wpla') .': '. count($wpl_report_summary->listings_to_import) ?><br>
							<?php echo __('Listings to be updated','wpla')  .': '. count($wpl_report_summary->listings_to_update) ?><br>
						</p>
						<p>
							<?php echo __('Click on "Start Import" to fetch product details from Amazon and add them to your website.','wpla'); ?><br>
						</p>
 						-->

					</div>
				</div> <!-- postbox -->

				<div class="postbox" id="ImportPreviewBox">
					<h3 class="hndle">
                        <span><?php echo __('Report Rows','wpla'); ?></span>
                        <div style="float:right;">
                            <input id="wpla_current_page" type="hidden" value="1" />
                            <input id="wpla_total_pages" type="hidden" value="<?php echo intval( count($wpl_report_summary->report_skus) / 100 ) ?>" />
                            <a id="wpla_prev_page" class="button button-small" title="previous page">&laquo;</a>
                            <a id="wpla_next_page" class="button button-small" title="next page">&raquo;</a>
                            <input id="wpla_import_preview_search_box" type="text" placeholder="Filter by SKU, ASIN or name..." style="font-size: 12px; font-weight: normal; width:200px;">
                            <a href="#" id="wpla_btn_filter" class="button button-small" title="apply filter">Search</a>
                        </div>
                    </h3>
					<div class="inside">

                        <div id="wpla_import_preview_table_container">
                            <?php WPLA_ImportHelper::render_import_preview_table( $wpl_data_rows, $wpl_report_summary ) ?>
                        </div>

						<!-- <p> -->
							<!-- Note: This preview shows a maxmimum of 100 rows only. -->
						<!-- </p> -->

                        <?php
                            // $max_num_pages = intval( count($wpl_report_summary->report_skus) / 100 );
                            // $page = 1;

                            // echo paginate_links( array(
                            //     // 'base'    => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
                            //     // 'format'  => '?paged=%#%',
                            //     'current' => max( 1, $page ),
                            //     'total'   => $max_num_pages
                            // ) );
                        ?>

						<?php
							// echo "<pre>";print_r($wpl_report_summary);echo"</pre>";#die();
							// echo "<pre>";print_r($wpl_data_rows);echo"</pre>";#die();
						?>

					</div>
				</div> <!-- postbox -->


			</div>
		</div>
	</div>

	<br style="clear:both;"/>

</div>



<script type="text/javascript">
    jQuery( document ).ready( function () {

        var wpla_report_id = '<?php echo $wpl_report_id ?>';

        // disable Enter key in filter field
        // jQuery('#wpla_import_preview_search_box').keypress(function(event) { 
        //     setTimeout( wpla_update_preview, 1000 );
        //     return event.keyCode != 13; 
        // });

        // update rows when search box changes
        jQuery('#wpla_import_preview_search_box').change(function(event) { 
            wpla_update_preview();
        });

        // button: next page
        jQuery('#wpla_next_page').click(function(event) { 
            var next_page = parseInt( jQuery('#wpla_current_page').val() ) + 1;
            // var total_pages = ...;
            // if ( next_page > total_pages ) next_page = 1;
            jQuery('#wpla_current_page').val( next_page );
            wpla_update_preview();
        });
        // button: prev page
        jQuery('#wpla_prev_page').click(function(event) { 
            var prev_page = parseInt( jQuery('#wpla_current_page').val() ) - 1;
            if ( prev_page < 1 ) prev_page = 1;
            jQuery('#wpla_current_page').val( prev_page );
            wpla_update_preview();
        });

        // button: search
        jQuery('#wpla_btn_filter').click(function(event) { 
            wpla_update_preview();
            return false;
        });

        // handle field filter changes
        function wpla_update_preview() {

            var query = jQuery('#wpla_import_preview_search_box').val();
            var page  = jQuery('#wpla_current_page').val();
            console.log('query',query);
            console.log('page',query);

            var params = {
                action: 'wpla_get_import_preview_table',
                report_id: wpla_report_id,
                query: query,
                pagenum: page,
                nonce: 'TODO'
            };
            jQuery( "#wpla_import_preview_table_container" ).load( ajaxurl, params, function() {
                console.log('report rows were updated.');                
                wpla_refresh_table_events();
                wpla_show_or_hide_checkbox_column();
            });

        } // wpla_update_preview()


        // handle select all checkbox in table header
        function wpla_refresh_table_events() {
            console.log('wpla_refresh_table_events');

            // refresh listener
            jQuery('#wpla_import_preview_table #cb-select-all-1').change(function(event) { 
                console.log('select all checkbox was clicked');

                if ( 'checked' == jQuery('#wpla_import_preview_table #cb-select-all-1').attr('checked') ) {
                    jQuery('#wpla_import_preview_table tbody .check-column input').attr('checked','checked');   // tick all
                } else {                    
                    jQuery('#wpla_import_preview_table tbody .check-column input').attr('checked',null);        // untick all
                }
                
            });

        } // wpla_refresh_table_events()

        // show or hide checkbox column
        function wpla_show_or_hide_checkbox_column() {
            console.log('wpla_show_or_hide_checkbox_column');

            if ( 'none' == jQuery('#btn_process_selected_report_rows').css('display') ) {
                // default mode: hide checkbox column
                jQuery('#wpla_import_preview_table .check-column').hide();
            } else {
                // show checkbox column
                jQuery('#wpla_import_preview_table .check-column').show();
            }

        } // wpla_show_or_hide_checkbox_column()



        // handle button "Select rows"
        jQuery('#btn_toggle_selection_mode').click(function(event) { 
            jQuery('#btn_process_selected_report_rows').toggle();
            jQuery('#btn_process_amazon_report').toggle();
            wpla_show_or_hide_checkbox_column();
        });



    });

</script>


