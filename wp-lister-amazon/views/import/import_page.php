<?php include_once( dirname(__FILE__).'/../common_header.php' ); ?>

<style type="text/css">

	.postbox-container .postbox {
		float: left;
		width: 49%;
	}
	.postbox-container #InventoryImportBox {
		margin-right: 1%;
	}

	.wpla_reports_table th {
		text-align: left;
		/*border-bottom: 1px solid #555;*/
	}

	/* tooltip icons should not float right on this page */
	img.help_tip {
		float: none;
	}

	a.right,
	input.button {
		float: right;
	}

</style>

<div class="wrap">
	<div class="icon32" style="background: url(<?php echo $wpl_plugin_url; ?>img/amazon-32x32.png) no-repeat;" id="wpl-icon"><br /></div>
	<h2><?php echo __('Import','wpla') ?></h2>
	<?php echo $wpl_message ?>


	<div style="width:100%" class="postbox-container">
		<div class="metabox-holder">
			<div class="meta-box-sortables ui-sortable">

				
				<div class="postbox" id="ImportToolBox" style="float:right;">
					<h3 class="hndle"><span><?php echo __('Import by ASIN','wpla'); ?></span></h3>
					<div class="inside">

						<p>
							<?php echo __('To import products which are not already in your inventory on Amazon, enter the ASINs of the products to import below.','wpla'); ?>
						</p>
						<!--
						<p>
							<?php echo sprintf( __('These products will be added to your default account %s.','wpla'), '<b>'.$wpl_default_account_title.'</b>' ); ?>
						</p>
						-->

						<form method="post" action="<?php echo $wpl_form_action; ?>&mode=asin&step=2">
							<?php wp_nonce_field( 'wpla_import_page' ); ?>
							<input type="hidden" name="action" value="wpla_bulk_import_asins" />

							<textarea name="wpla_asin_list" style="width:100%;height:230px;"><?php echo isset($_REQUEST['wpla_asin_list']) ? $_REQUEST['wpla_asin_list'] : '' ?></textarea>

							<p>
	
								<select name="wpla_import_account_id">
									<option>&mdash; select your account &mdash;</option>
									<?php $all_accounts = WPLA()->accounts; ?>
									<?php $default_account_id = get_option('wpla_default_account_id'); ?>
									<?php foreach ( $all_accounts as $account ) : ?>
										<option value="<?php echo $account->id ?>"
										<?php if ( $account->id == $default_account_id ) echo 'selected' ?>
											><?php echo $account->title ?> (<?php echo $account->market_code ?>)</option>
									<?php endforeach; ?>
								</select>

								<input type="submit" value="<?php echo __('Import ASINs','wpla'); ?>" name="submit" class="button">

							</p>

						</form>
						<br style="clear:both;"/>

					</div>
				</div> <!-- postbox -->


				<div class="postbox" id="InventoryImportBox">
					<h3 class="hndle"><span><?php echo __('Import from Inventory Report','wpla'); ?></span></h3>
					<div class="inside">


						<?php if ( ! empty($wpl_recent_reports) ) : ?>
							<p><?php echo __('To update your inventory from Amazon, select an inventory report below and click "Preview".','wpla'); ?></p>

							<table style="width:100%;" class="wpla_reports_table">
								<tr>
									<!-- <th><?php echo __('Request ID','wpla') ?></th> -->
									<th><?php echo __('Date','wpla') ?></th>
									<th><?php echo __('Account','wpla') ?></th>
									<th><?php echo __('Size','wpla') ?></th>
									<th>&nbsp;</th>
								</tr>
							<?php foreach ($wpl_recent_reports as $report) : ?>
								<tr>
									<!-- <td><?php echo $report->ReportRequestId ?></td> -->
									<td>
										<?php echo $report->CompletedDate ?><br>
										<span style="color:silver">
											<?php echo human_time_diff( strtotime($report->CompletedDate.' UTC'), time() ) ?> ago
										</span>
									</td>
									<td><?php echo WPLA_AmazonAccount::getAccountTitle( $report->account_id ) ?></td>
									<td>
										<a href="admin.php?page=wpla-reports&action=view_amazon_report_details&amazon_report=<?php echo $report->id ?>" target="_blank">
											<?php echo intval($report->line_count) - 1 ?> rows
										</a>
									</td>
									<td>
										<a href="admin.php?page=wpla-import&mode=inventory&report_id=<?php echo $report->id ?>&step=2" class="button button-small">											
											<?php echo __('Preview','wpla') ?>
										</a>
									</td>
								</tr>
							<?php endforeach; ?>
							</table>

	                        <p>
	                            <b><?php echo __('Note','wpla'); ?>:</b>
	                            <?php echo __('Sale prices can not be imported from Amazon and will be <em>removed</em> when an imported product is updated.','wpla'); ?>
	                        </p>

						<?php elseif( $wpl_reports_in_progress ) : ?>

								<p>
									<i>Note: <?php echo sprintf( __('%s report request(s) are currently in progress.','wpla'), $wpl_reports_in_progress ) ?></i>
								</p>
								<p>
									<?php echo __('Please wait until your inventory report has been generated.','wpla'); ?><br>
									<?php echo __('Click on "Check Now" to check the report status now.','wpla'); ?>
								</p>

						<?php else : ?>
							<form method="post" action="<?php echo $wpl_form_action; ?>">
								<?php wp_nonce_field( 'wpla_import_page' ); ?>
								<input type="hidden" name="action" value="wpla_request_new_inventory_report" />

								<p>
									<?php echo __('There are no recent inventory reports that have been created within the last 24 hours.','wpla'); ?>
								</p>
								<p>
									<?php echo __('Please request a new Merchant Listings Report and wait until it has been generated.','wpla'); ?>
								</p>

								<input type="submit" value="<?php echo __('Request Inventory Report','wpla'); ?>" name="submit" class="button">
							</form>
							<br style="clear:both;"/>
						<?php endif; ?>

					</div>
				</div> <!-- postbox -->


				<div class="postbox" id="ImportOptionsBox" style="float:left;">
					<h3 class="hndle"><span><?php echo __('Report Processing Options','wpla'); ?></span></h3>
					<div class="inside">

						<p><?php echo __('Select whether you want to update stock levels and / or prices for each WooCommerce product when an inventory report is processed.','wpla'); ?></p>

						<form method="post" action="<?php echo $wpl_form_action; ?>">
							<?php wp_nonce_field( 'wpla_import_page' ); ?>
							<input type="hidden" name="action" value="wpla_update_import_options" />

							<input type="submit" value="<?php echo __('Save Options','wpla'); ?>" name="submit" class="button">

							<input type="checkbox" name="wpla_reports_update_woo_stock" id="wpla_reports_update_woo_stock" value="1" <?php if ($wpl_reports_update_woo_stock) echo 'checked' ?> />
							<label for="wpla_reports_update_woo_stock" class="text_label">
								<?php echo __('Update stock levels','wpla'); ?>
                                <?php wpla_tooltip('Tick this box to update stock levels in WooCommerce when processing an inventory report.<br>(not applicable for FBA items)<br><br>Note: Stock levels for listings with the status "changed" or "submitted" will <b>not</b> be updated. That is why it is recommended to wait until all feeds have been submitted and processed before using this option.') ?>
							</label><br>

							<input type="checkbox" name="wpla_reports_update_woo_price" id="wpla_reports_update_woo_price" value="1" <?php if ($wpl_reports_update_woo_price) echo 'checked' ?> />
							<label for="wpla_reports_update_woo_price" class="text_label">
								<?php echo __('Update product prices','wpla'); ?>
                                <?php wpla_tooltip('Tick this box to update product prices in WooCommerce when processing an inventory report.') ?>
							</label><br>

							<input type="checkbox" name="wpla_reports_update_woo_condition" id="wpla_reports_update_woo_condition" value="1" <?php if ($wpl_reports_update_woo_condition) echo 'checked' ?> />
							<label for="wpla_reports_update_woo_condition" class="text_label">
								<?php echo __('Update item conditions','wpla'); ?>
                                <?php wpla_tooltip('Tick this box if you want your item conditions to be updated from your inventory report.<br><br>This should only be required if you manually change an existing SKU from "new" to "used" (or vice versa) manually on Seller Central.') ?>
							</label><br>

							<input type="checkbox" name="wpla_import_creates_all_variations" id="wpla_import_creates_all_variations" value="1" <?php if ($wpl_import_creates_all_variations) echo 'checked' ?> />
							<label for="wpla_import_creates_all_variations" class="text_label">
								<?php echo __('Create all variations','wpla'); ?>
                                <?php wpla_tooltip('When importing variable products, by default only variations which exist in your inventory report are created in WooCommerce.<br><br>Tick this box if you want to create <i>all</i> variations available on Amazon.<br>(backup your database first!)') ?>
							</label><br>

							<input type="checkbox" name="wpla_import_variations_as_simple" id="wpla_import_variations_as_simple" value="1" <?php if ($wpl_import_variations_as_simple) echo 'checked' ?> />
							<label for="wpla_import_variations_as_simple" class="text_label">
								<?php echo __('Create variations as simple products','wpla'); ?>
                                <?php wpla_tooltip('Enable this option if you do not want the import process to create variable products in WooCommerce, but create a single product for each variation instead.') ?>
							</label><br>

						</form>
						<br style="clear:both;"/>

						<p><?php echo __('Note: Processing a report will always update the information on Amazon &raquo Listings.','wpla'); ?></p>

					</div>
				</div> <!-- postbox -->

			</div>
		</div>
	</div>

	<br style="clear:both;"/>

</div>



<script type="text/javascript">
	jQuery( document ).ready(
		function () {
	
			// highlight save button when options are modified
			jQuery("#ImportOptionsBox input[type='checkbox']").on('click', function() {

				jQuery('#ImportOptionsBox .button').addClass('button-primary');

				return true;				
			})

		}
	);

</script>
