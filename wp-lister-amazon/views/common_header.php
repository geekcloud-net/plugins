<?php 
	// make sure we show all admin messages - even the ones generated after the WP admin_notices hook has fired
	do_action( 'wpla_admin_notices' );
?>

<!-- jobs window -->
<div id="wpla_jobs_window_container" style="display:none">
	<div id="wpla_jobs_window">
		
		<h2 id="wpla_jobs_title">Jobs</h2>
		
		<div id="wpla_progressbar"><span class="caption">loading...</span></div>			
		<div id="wpla_jobs_message">warming up...</div>
		
		<div id="wpla_jobs_log">
			<span></span>
		</div>
		
		<div id="wpla_jobs_footer_msg" style="">
			<?php echo __("Please don't close this window until all tasks are completed.",'wpla') ?>
		</div>
		<div class="submit" style="float:right; padding: 10px 0 0 0;">
			<a class="btn_close button-secondary"><?php echo __('Close window','wpla') ?></a>
			<a class="btn_cancel button-secondary"><?php echo __('Cancel','wpla') ?></a>
		</div>

	</div>
</div>


<script type="text/javascript">
	
	var wpla_url= '<?php echo WPLA_URL; ?>/';
	var wpla_ajax_error_handling = "<?php echo get_option( 'wpla_ajax_error_handling', 'halt' ); ?>";

	// on page load
	jQuery( document ).ready(
		function () {
	
			// init JobRunner
			WPLA.JobRunner.init();

			// btn_update_amazon_data
			// jQuery('#btn_update_amazon_data').click( function(event) {
			// 	WPLA.JobRunner.runJob( 'updateAmazonData', 'Loading data from Amazon...' );
			// });

			// btn_process_amazon_report
			jQuery('#btn_process_amazon_report').click( function(event) {
				var params = { item_id : jQuery(this).attr('data-id') };
				WPLA.JobRunner.runJob( 'processAmazonReport', 'Processing report...', params );
				return false;
			});

			// btn_process_selected_report_rows
			jQuery('#btn_process_selected_report_rows').click( function(event) {

		        // create array of selected SKUs
		        var selected_skus = [];
		        jQuery(".check-column input:checked[name='row[]']").each( function(index, checkbox) {
		             selected_skus.push( checkbox.value );
		        });

				var params = { 
					report_id : jQuery(this).attr('data-id'), 
					sku_list  : selected_skus
				};

				WPLA.JobRunner.runJob( 'processRowsFromAmazonReport', 'Processing selected rows...', params );
				return false;
			});

			// .row-actions .process_amazon_report a
			// jQuery('.row-actions .process_amazon_report a').click( function(event) {
			// 	var params = { item_id : jQuery(this).attr('data-id') };
			// 	WPLA.JobRunner.runJob( 'processAmazonReport', 'Processing report...', params );
			// 	return false;
			// });

			// .row-actions .process_fba_report a
			jQuery('.row-actions .process_fba_report a').click( function(event) {
				var params = { item_id : jQuery(this).attr('data-id') };
				WPLA.JobRunner.runJob( 'processAmazonReport', 'Processing FBA report...', params );
				return false;
			});

			// .row-actions .process_fba_inv_age_report a
			jQuery('.row-actions .process_fba_inv_age_report a').click( function(event) {
				var params = { item_id : jQuery(this).attr('data-id') };
				WPLA.JobRunner.runJob( 'processAmazonReport', 'Processing FBA Inventory Age report...', params );
				return false;
			});

			// .row-actions .process_quality_report a
			jQuery('.row-actions .process_quality_report a').click( function(event) {
				var params = { item_id : jQuery(this).attr('data-id') };
				WPLA.JobRunner.runJob( 'processAmazonReport', 'Processing Listing Quality report...', params );
				return false;
			});

			// btn_batch_create_products
			jQuery('#btn_batch_create_products_reminder').click( function(event) {
				WPLA.JobRunner.runJob( 'createAllImportedProducts', 'Importing products...' );
			});

			// btn_batch_update_no_asin
			jQuery('.btn_batch_update_no_asin').click( function(event) {
				WPLA.JobRunner.runJob( 'updateProductsWithoutASIN', 'Updating products...' );
			});


			// init tooltips
			jQuery(".tips, .help_tip").tipTip({
		    	'attribute' : 'data-tip',
		    	'maxWidth' : '250px',
		    	'fadeIn' : 50,
		    	'fadeOut' : 50,
		    	'delay' : 200
		    });

		}
	);

</script>


<style type="text/css">

	#tiptip_holder #tiptip_content {
		max-width: 250px;
	}

</style>
