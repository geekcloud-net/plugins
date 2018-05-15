<?php include_once( dirname(__FILE__).'/common_header.php' ); ?>

<style type="text/css">

	th.column-details {
		width: 25%;
	}

</style>

<div class="wrap">
	<div class="icon32" style="background: url(<?php echo $wpl_plugin_url; ?>img/amazon-32x32.png) no-repeat;" id="wpl-icon"><br /></div>
	<h2>
		<?php echo __('Reports','wpla') ?>
		<a href="<?php echo $wpl_form_action; ?>" class="add-new-h2">Refresh</a>
	</h2>
	<?php echo $wpl_message ?>


	<!-- show reports table -->
	<?php $wpl_reportsTable->views(); ?>

    <form id="reports-filter" method="get" action="<?php echo $wpl_form_action; ?>" >
        <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ) ?>" />
		<?php $wpl_reportsTable->search_box(__('Search','wpla'), 'report-search-input'); ?>
        <input type="hidden" name="paged" value="<?php echo isset( $_REQUEST['paged'] ) ? $_REQUEST['paged'] : '-1' ?>" />
        <input type="hidden" name="report_status" value="<?php echo isset( $_REQUEST['report_status'] ) ? $_REQUEST['report_status'] : '' ?>" />
        <input type="hidden" name="account_id" value="<?php echo isset( $_REQUEST['account_id'] ) ? $_REQUEST['account_id'] : '' ?>" />
    </form>

    <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
    <form id="profiles-filter" method="post" action="<?php echo $wpl_form_action; ?>" >
        <!-- For plugins, we also need to ensure that the form posts back to our current page -->
        <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ) ?>" />
        <!-- Now we can render the completed list table -->
        <?php $wpl_reportsTable->display() ?>
    </form>

	<br style="clear:both;"/>


	<form method="post" action="<?php echo $wpl_form_action; ?>">
		<div class="submit" style="padding-top: 0; float: left;">
			<?php wp_nonce_field( 'wpla_update_reports' ); ?>
			<input type="hidden" name="action" value="wpla_update_reports" />
			<input type="submit" value="<?php echo __('Update reports','wpla') ?>" name="submit" class="button-secondary"
				   title="<?php echo __('Update recent reports from Amazon.','wpla') ?>">
	
			<a id="btn_request_report" class="btn_request_report button-secondary wpl_job_button"><?php echo __('Request report','wpla'); ?></a>
	
		</div>
	</form>
	<br style="clear:both;"/>


	<div class="request_report_wrapper" style="display:none">

		<!-- <hr> -->
		<?php $wpla_report_type = '' ?>
		<h3><?php _e('Request a new report','wpla') ?></h3>

		<form method="post" action="<?php echo $wpl_form_action; ?>">
			<div class="submit" style="padding-top: 0;">
				<?php wp_nonce_field( 'wpla_request_report' ); ?>
				<input type="hidden" name="action" value="wpla_request_report" />

				<p><?php _e('Please select the type of report you want to request','wpla') ?>:</p>

				<!-- <label for="wpla_report_type" class="text_label"><?php echo __('Select the type of report to request','wpla') ?></label> -->
				<select id="wpla_report_type" name="wpla_report_type" title="Updates" class=" required-entry select">
					<optgroup label="<?php echo __('Supported Report Types','wpla') ?>">
						<option value="_GET_MERCHANT_LISTINGS_DATA_" 				<?php if ( $wpla_report_type == '_GET_MERCHANT_LISTINGS_DATA_' ): 				?>selected="selected"<?php endif; ?>><?php echo __('Merchant Listings Report','wpla') ?></option>
						<option value="_GET_AFN_INVENTORY_DATA_" 					<?php if ( $wpla_report_type == '_GET_AFN_INVENTORY_DATA_' ): 					?>selected="selected"<?php endif; ?>><?php echo 'FBA Amazon Fulfilled Inventory Report' ?></option>
						<option value="_GET_AMAZON_FULFILLED_SHIPMENTS_DATA_" 		<?php if ( $wpla_report_type == '_GET_AMAZON_FULFILLED_SHIPMENTS_DATA_' ): 		?>selected="selected"<?php endif; ?>><?php echo 'FBA Amazon Fulfilled Shipments Report' ?></option>
						<option value="_GET_FBA_FULFILLMENT_INVENTORY_HEALTH_DATA_"	<?php if ( $wpla_report_type == '_GET_FBA_FULFILLMENT_INVENTORY_HEALTH_DATA_' ):?>selected="selected"<?php endif; ?>><?php echo 'FBA Inventory Health Report' ?></option>
					</optgroup>
					<optgroup label="<?php echo __('Currently Unsupported Report Types','wpla') ?>">
						<option value="_GET_MERCHANT_LISTINGS_DEFECT_DATA_"			<?php if ( $wpla_report_type == '_GET_MERCHANT_LISTINGS_DEFECT_DATA_' ): 		?>selected="selected"<?php endif; ?>><?php echo 'Listing Quality and Suppressed Listing Report' ?></option>
						<option value="_GET_FLAT_FILE_OPEN_LISTINGS_DATA_" 			<?php if ( $wpla_report_type == '_GET_FLAT_FILE_OPEN_LISTINGS_DATA_' ): 		?>selected="selected"<?php endif; ?>><?php echo __('Open Listings Report','wpla') ?></option>
						<option value="_GET_XML_BROWSE_TREE_DATA_" 					<?php if ( $wpla_report_type == '_GET_XML_BROWSE_TREE_DATA_' ): 				?>selected="selected"<?php endif; ?>><?php echo __('Browse Tree Report','wpla') ?></option>
						<option value="_GET_AFN_INVENTORY_DATA_BY_COUNTRY_" 		<?php if ( $wpla_report_type == '_GET_AFN_INVENTORY_DATA_BY_COUNTRY_' ): 		?>selected="selected"<?php endif; ?>><?php echo 'FBA Multi-Country Inventory Report' ?></option>
						<option value="_GET_FBA_MYI_UNSUPPRESSED_INVENTORY_DATA_"	<?php if ( $wpla_report_type == '_GET_FBA_MYI_UNSUPPRESSED_INVENTORY_DATA_' ): 	?>selected="selected"<?php endif; ?>><?php echo 'FBA Manage Inventory Report' ?></option>
						<option value="_GET_FLAT_FILE_PAYMENT_SETTLEMENT_DATA_"		<?php if ( $wpla_report_type == '_GET_FLAT_FILE_PAYMENT_SETTLEMENT_DATA_' ): 	?>selected="selected"<?php endif; ?>><?php echo 'Flat File Settlement Report' ?></option>
						<option value="_GET_V2_SETTLEMENT_REPORT_DATA_FLAT_FILE_"	<?php if ( $wpla_report_type == '_GET_V2_SETTLEMENT_REPORT_DATA_FLAT_FILE_' ): 	?>selected="selected"<?php endif; ?>><?php echo 'Flat File Settlement Report (v2)' ?></option>
					</optgroup>
				</select>

				<input type="submit" value="<?php echo __('Submit request','wpla') ?>" name="submit" class="button-secondary" 
					   title="<?php echo __('Request report from Amazon.','wpla') ?>">
			</div>
		</form>

	</div>

	<script type="text/javascript">
		jQuery( document ).ready(
			function () {
		
				// ask again before ending items
				jQuery('.btn_request_report').on('click', function() {
					jQuery('.request_report_wrapper').slideToggle(300);
				})
	
			}
		);
	
	</script>



</div>