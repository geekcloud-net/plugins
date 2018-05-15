<?php include_once( dirname(__FILE__).'/common_header.php' ); ?>

<style type="text/css">
	
	#AuthSettingsBox ol li {
		margin-bottom: 25px;
	}
	#AuthSettingsBox ol li > small {
		margin-left: 4px;
	}

	#side-sortables .postbox input.text_input,
	#side-sortables .postbox select.select {
	    width: 50%;
	}
	#side-sortables .postbox label.text_label {
	    width: 45%;
	}
	#side-sortables .postbox p.desc {
	    margin-left: 5px;
	}

</style>

<div class="wrap amazon-page">
	<div class="icon32" style="background: url(<?php echo $wpl_plugin_url; ?>img/amazon-32x32.png) no-repeat;" id="wpl-icon"><br /></div>
          
	<?php include_once( dirname(__FILE__).'/settings_tabs.php' ); ?>		
	<?php echo $wpl_message ?>

	<form method="post" id="settingsForm" action="<?php echo $wpl_form_action; ?>">

	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">

			<div id="postbox-container-1" class="postbox-container">
				<div id="side-sortables" class="meta-box">


					<!-- first sidebox -->
					<div class="postbox" id="submitdiv">
						<!--<div title="Click to toggle" class="handlediv"><br></div>-->
						<h3 class="hndle"><span><?php echo __('Sync Status','wpla'); ?></span></h3>
						<div class="inside">

							<div id="submitpost" class="submitbox">

								<div id="misc-publishing-actions">
									<div class="misc-pub-section">
									<?php /* if ( @$wpl_amazon_token_userid ): ?>
										<p>
											<!-- <b><?php echo __('Account Details','wpla') ?></b> -->
											<table style="width:95%">
												<tr><td><?php echo __('User ID','wpla') . ':</td><td>' . $wpl_amazon_token_userid ?></td></tr>
												<tr><td><?php echo __('Status','wpla') . ':</td><td>' . $wpl_amazon_user->Status ?></td></tr>
												<tr><td><?php echo __('Score','wpla') . ':</td><td>' . $wpl_amazon_user->FeedbackScore ?></td></tr>
												<tr><td><?php echo __('Site','wpla') . ':</td><td>' . $wpl_amazon_user->Site ?></td></tr>
												<?php if ( $wpl_amazon_user->StoreOwner ) : ?>
												<tr><td><?php echo __('Store','wpla') . ':</td><td>' ?><a href="<?php echo $wpl_amazon_user->StoreURL ?>" target="_blank"><?php echo __('visit store','wpla') ?></a></td></tr>
												<?php endif; ?>
											</table>												
										</p>
									<?php endif; */ ?>

									<?php if ( empty( WPLA()->accounts ) ): ?>
										<p><?php echo __('No Amazon account has been set up yet.','wpla') ?></p>
									<?php elseif ( $wpl_option_cron_schedule && $wpl_option_sync_inventory ): ?>
										<p><?php echo __('Sync is enabled.','wpla') ?></p>
										<p><?php echo __('Sales will be synchronized between WooCommerce and Amazon.','wpla') ?></p>
									<?php elseif ( WPLA_LIGHT ): ?>
										<p><?php echo __('Sync is not available in WP-Lister Lite.','wpla') ?></p>
										<p><?php echo __('To synchronize sales across Amazon and WooCommerce you need to upgrade to WP-Lister Pro.','wpla') ?></p>
									<?php else: ?>
										<p><?php echo __('Sync is currently disabled.','wpla') ?></p>
										<p><?php echo __('Amazon and WooCommerce sales will not be synchronized!','wpla') ?></p>
									<?php endif; ?>

									</div>
								</div>

								<div id="major-publishing-actions">
									<div id="publishing-action">
										<input type="submit" value="<?php echo __('Update Settings','wpla'); ?>" id="save_settings" class="button-primary" name="save">
									</div>
									<div class="clear"></div>
								</div>

							</div>

						</div>
					</div>

					<?php if ( $wpl_is_staging_site ) : ?>
					<div class="postbox" id="StagingSiteBox">
						<h3 class="hndle"><span><?php echo __('Staging Site','wpla') ?></span></h3>
						<div class="inside">
							<p>
								<span style="color:darkred; font-weight:bold">
									Note: Automatic background updates and order creation have been disabled on this staging site.
								</span>
							</p>
						</div>
					</div>
					<?php endif; ?>

					<?php if ( $wpl_option_cron_schedule ) : ?>
					<div class="postbox" id="UpdateScheduleBox">
						<h3 class="hndle"><span><?php echo __('Update Schedule','wpla') ?></span></h3>
						<div class="inside">

							<p>
							<?php if ( wp_next_scheduled( 'wpla_update_schedule' ) ) : ?>
								<?php echo __('Next scheduled update','wpla'); ?>: 
								<?php echo human_time_diff( wp_next_scheduled( 'wpla_update_schedule' ), current_time('timestamp',1) ) ?>
								<?php echo wp_next_scheduled( 'wpla_update_schedule' ) < current_time('timestamp',1) ? 'ago' : '' ?>
							<?php elseif ( $wpl_option_cron_schedule == 'external' ) : ?>
								<?php echo __('Background updates are handled by an external cron job.','wpla'); ?> 
								<a href="#TB_inline?height=420&width=900&inlineId=cron_setup_instructions" class="thickbox">
									<?php echo __('Details','wpla'); ?>
								</a>

								<div id="cron_setup_instructions" style="display: none;">
									<h2>
										<?php echo __('How to set up an external cron job','wpla'); ?>
									</h2>
									<p>
										<?php echo __('Luckily, you don\'t have to be a server admin to set up an external cron job.','wpla'); ?>
										<?php echo __('You can ask your server admin to set up a cron job on your own server - or use a 3rd party web based cron service, which provides a user friendly interface and additional features for a small annual fee.','wpla'); ?>
									</p>

									<h3>
										<?php echo __('Option A: Web cron service','wpla'); ?>
									</h3>
									<p>
										<?php $ec_link = '<a href="https://www.easycron.com/" target="_blank">www.easycron.com</a>' ?>
										<?php echo sprintf( __('The easiest way to set up a cron job is to sign up with %s and use the following URL to create a new task.','wpla'), $ec_link ); ?><br>
									</p>
									<code>
										<?php echo bloginfo('url') ?>/wp-admin/admin-ajax.php?action=wplister_run_scheduled_tasks
									</code>

									<h3>
										<?php echo __('Option B: Server cron job','wpla'); ?>
									</h3>
									<p>
										<?php echo __('If you prefer to set up a cron job on your own server you can create a cron job that will execute the following command:','wpla'); ?>
									</p>

									<code style="font-size:0.8em;">
										wget -q -O - <?php echo bloginfo('url') ?>/wp-admin/admin-ajax.php?action=wplister_run_scheduled_tasks >/dev/null 2>&1
									</code>

									<p>
										<?php echo __('Note: Your cron job should run at least every 15 minutes but not more often than every 5 minutes.','wpla'); ?>
									</p>
								</div>

							<?php else: ?>
								<span style="color:darkred; font-weight:bold">
									Warning: Update schedule is disabled.
								</span></p><p>
								Please click the "Save Settings" button above in order to reset the update schedule.
							<?php endif; ?>
							</p>

							<?php if ( get_option('wpla_cron_last_run') ) : ?>
							<p>
								<?php echo __('Last run','wpla'); ?>: 
								<?php echo human_time_diff( get_option('wpla_cron_last_run'), current_time('timestamp',1) ) ?> ago
							</p>
							<?php endif; ?>

						</div>
					</div>
					<?php endif; ?>

				</div>
			</div> <!-- #postbox-container-1 -->


			<!-- #postbox-container-2 -->
			<div id="postbox-container-2" class="postbox-container">
				<div class="meta-box-sortables ui-sortable">
					
					<input type="hidden" name="action" value="save_wpla_settings" >
                    <?php wp_nonce_field( 'wpla_save_settings' ); ?>



					<div class="postbox" id="UpdateOptionBox">
						<h3 class="hndle"><span><?php echo __('Background Tasks','wpla') ?></span></h3>
						<div class="inside">
							<!-- <p><?php echo __('Enable to update listings and transactions using WP-Cron.','wpla'); ?></p> -->

							<label for="wpl-option-cron_schedule" class="text_label">
								<?php echo __('Update interval','wpla') ?>
                                <?php wpla_tooltip('Select how often WP-Lister should run background jobs like checking for new sales on Amazon, submitting pending feeds and checking for processing results, etc.<br><br>It is recommended to use an external cron job or set this interval to 5 - 15 minutes.<br><br>Setting the update interval <i>to manually</i> will disable all background tasks and should only be used for testing and debuging but never on a live production site.') ?>
							</label>
							<select id="wpl-option-cron_schedule" name="wpla_option_cron_schedule" class=" required-entry select">
								<option value="five_min" 	<?php if ( $wpl_option_cron_schedule == 'five_min'    ): ?>selected="selected"<?php endif; ?>><?php echo __('5 min.','wpla') ?></option>
								<option value="ten_min" 	<?php if ( $wpl_option_cron_schedule == 'ten_min'     ): ?>selected="selected"<?php endif; ?>><?php echo __('10 min.','wpla') ?></option>
								<option value="fifteen_min" <?php if ( $wpl_option_cron_schedule == 'fifteen_min' ): ?>selected="selected"<?php endif; ?>><?php echo __('15 min.','wpla') ?></option>
								<option value="thirty_min" 	<?php if ( $wpl_option_cron_schedule == 'thirty_min'  ): ?>selected="selected"<?php endif; ?>><?php echo __('30 min.','wpla') ?></option>
								<option value="hourly" 		<?php if ( $wpl_option_cron_schedule == 'hourly'      ): ?>selected="selected"<?php endif; ?>><?php echo __('hourly','wpla') ?></option>
								<option value="daily" 		<?php if ( $wpl_option_cron_schedule == 'daily'       ): ?>selected="selected"<?php endif; ?>><?php echo __('daily','wpla') ?> (<?php echo __('not recommended','wpla') ?>)</option>
								<option value="" 			<?php if ( $wpl_option_cron_schedule == ''            ): ?>selected="selected"<?php endif; ?>><?php echo __('manually','wpla') ?> (<?php echo __('not recommended','wpla') ?>)</option>
								<option value="external" 	<?php if ( $wpl_option_cron_schedule == 'external'    ): ?>selected="selected"<?php endif; ?>><?php echo __('Use external cron job','wpla') ?></option>
							</select>

							
					        <!-- ## BEGIN PRO ## -->
							<label for="wpl-option-sync_inventory" class="text_label">
								<?php echo __('Synchronize sales','wpla') ?>
                                <?php wpla_tooltip('Do you want WP-Lister to reduce the stock quantity in WooCommerce when an item is sold on Amazon - and vice versa?') ?>
							</label>
							<select id="wpl-option-sync_inventory" name="wpla_option_sync_inventory" class=" required-entry select">
								<option value="1" <?php if ( $wpl_option_sync_inventory == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Yes','wpla'); ?></option>
								<option value="0" <?php if ( $wpl_option_sync_inventory != '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('No','wpla'); ?></option>
							</select>
					        <!-- ## END PRO ## -->

						</div>
					</div>


					<div class="postbox" id="FBAOptionsBox">
						<h3 class="hndle"><span><?php echo __('Fulfillment by Amazon (FBA)','wpla') ?></span></h3>
						<div class="inside">

							<label for="wpl-fba_enabled" class="text_label">
								<?php echo __('Enable FBA','wpla') ?>
                                <?php wpla_tooltip('Enable this if you are using FBA for any or all of your products. This will automatically generate a daily FBA inventory feed and process it to keep WP-Lister up to date with your stock levels on FBA.') ?>
							</label>
							<select id="wpl-fba_enabled" name="wpla_fba_enabled" class=" required-entry select">
								<option value="0" <?php if ( $wpl_fba_enabled != '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('No','wpla'); ?></option>
								<option value="1" <?php if ( $wpl_fba_enabled == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Yes','wpla'); ?></option>
							</select>

							<p class="desc" style="display: block;">
								<?php echo __('Enable this if you are using Fulfillment by Amazon.','wpla'); ?>
							</p>

							<label for="wpl-fba_fulfillment_center_id" class="fba_option text_label">
								<?php echo __('Fulfillment Center','wpla') ?>
                                <?php wpla_tooltip('Select either Amazon US or Amazon EU.') ?>
							</label>
							<select id="wpl-fba_fulfillment_center_id" name="wpla_fba_fulfillment_center_id" class="fba_option required-entry select">
								<option value="AMAZON_NA"  <?php if ( $wpl_fba_fulfillment_center_id == 'AMAZON_NA'  ): ?>selected="selected"<?php endif; ?>><?php echo 'Amazon US' ?> </option>
								<option value="AMAZON_EU"  <?php if ( $wpl_fba_fulfillment_center_id == 'AMAZON_EU'  ): ?>selected="selected"<?php endif; ?>><?php echo 'Amazon EU' ?> </option>
								<option value="AMAZON_CA"  <?php if ( $wpl_fba_fulfillment_center_id == 'AMAZON_CA'  ): ?>selected="selected"<?php endif; ?>><?php echo 'Amazon CA' ?> (experimental)</option>
								<option value="AMAZON_IN"  <?php if ( $wpl_fba_fulfillment_center_id == 'AMAZON_IN'  ): ?>selected="selected"<?php endif; ?>><?php echo 'Amazon IN' ?> (experimental)</option>
							</select>

					        <!-- ## BEGIN PRO ## -->
							<label for="wpl-fba_default_delivery_sla" class="fba_option text_label">
								<?php echo __('Default Shipping service','wpla') ?>
                                <?php wpla_tooltip('This default value will be used for all orders which are automatically submitted to FBA.') ?>
							</label>
							<select id="wpl-fba_default_delivery_sla" name="wpla_fba_default_delivery_sla" class="fba_option required-entry select">
								<option value="Standard"   <?php if ( $wpl_fba_default_delivery_sla == 'Standard'  ): ?>selected="selected"<?php endif; ?>><?php echo __('Standard','wpla'); ?> (3-5 business days)</option>
								<option value="Expedited"  <?php if ( $wpl_fba_default_delivery_sla == 'Expedited' ): ?>selected="selected"<?php endif; ?>><?php echo __('Expedited','wpla'); ?> (2 business days)</option>
								<option value="Priority"   <?php if ( $wpl_fba_default_delivery_sla == 'Priority'  ): ?>selected="selected"<?php endif; ?>><?php echo __('Priority','wpla'); ?> (1 business day)</option>
							</select>

							<label for="wpl-fba_default_order_comment" class="fba_option text_label">
								<?php echo __('Default Packing Slip Comment','wpla'); ?>
                                <?php wpla_tooltip('This default value will be used for all orders which are automatically submitted to FBA.') ?>
							</label>
							<input type="text" name="wpla_fba_default_order_comment" id="wpl-fba_default_order_comment" value="<?php echo $wpl_fba_default_order_comment; ?>" placeholder="Thank you for your order" class="fba_option text_input" />

							<p class="desc fba_option" style="display: block;">
								<?php echo __('These default values will be used when orders are submitted automatically.','wpla'); ?>
							</p>
					        <!-- ## END PRO ## -->

							<label for="wpl-fba_enable_fallback" class="fba_option text_label">
								<?php echo __('Fallback to Seller Fulfilled','wpla') ?> 
                                <?php wpla_tooltip('With this option enabled, an item will be switched from FBA to being seller-fulfilled when there is no stock in FBA but there is still stock left in WooCommerce.') ?>
							</label>
							<select id="wpl-fba_enable_fallback" name="wpla_fba_enable_fallback" class="fba_option required-entry select">
								<option value="0" <?php if ( $wpl_fba_enable_fallback != '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('No','wpla'); ?></option>
								<option value="1" <?php if ( $wpl_fba_enable_fallback == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Yes','wpla'); ?></option>
							</select>

							<p class="desc fba_option" style="display: block;">
								<?php echo __('Fall back to remaining WooCommerce stock when FBA stock reaches zero.','wpla'); ?>
							</p>

							<label for="wpl-fba_only_mode" class="fba_option text_label">
								<?php echo __('Enable FBA only mode','wpla') ?> 
                                <?php wpla_tooltip('With this option enabled, WP-Lister will assume that all items from your FBA Inventory Report are fulfilled by Amazon only.<br><br>When processing an FBA Inventory Report, all FBA stock levels will be synchronized to WooCommerce.<br><br>Falling back to seller fulfillment will be disabled.') ?>
							</label>
							<select id="wpl-fba_only_mode" name="wpla_fba_only_mode" class="fba_option required-entry select">
								<option value="0" <?php if ( $wpl_fba_only_mode != '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('No','wpla'); ?></option>
								<option value="1" <?php if ( $wpl_fba_only_mode == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Yes','wpla'); ?></option>
							</select>

							<p class="desc fba_option" style="display: block;">
								<?php echo __('Enable this if all your products use FBA or to sync all FBA stock levels to WooCommerce.','wpla'); ?>
							</p>

					        <!-- ## BEGIN PRO ## -->
							<label for="wpl-fba_autosubmit_orders" class="fba_option text_label">
								<?php echo __('Enable Multi-Channel Fulfillment','wpla') ?>
								<?php // $allowed_order_statuses = apply_filters( 'wpla_mcf_enabled_order_statuses', array( 'wc-completed', 'wc-processing', 'wc-on-hold' ) ); ?>
								<?php $allowed_order_statuses = apply_filters( 'wpla_mcf_enabled_order_statuses', array( 'wc-completed', 'wc-processing' ) ); // removed on-hold for now - until "hold" FBA action is implemented ?>
                                <?php wpla_tooltip('This will check for new WooCommerce orders (within 24h) where all order line items are available in FBA and submit them to be fulfilled by Amazon automatically.<br>(only for orders with status: '.join( ', ', $allowed_order_statuses ).')') ?>
							</label>
							<select id="wpl-fba_autosubmit_orders" name="wpla_fba_autosubmit_orders" class="fba_option required-entry select">
								<option value="0" <?php if ( $wpl_fba_autosubmit_orders != '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('No','wpla'); ?></option>
								<option value="1" <?php if ( $wpl_fba_autosubmit_orders == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Yes','wpla'); ?></option>
							</select>

							<p class="desc fba_option" style="display: block;">
								<?php echo __('Fulfill new WooCommerce orders via FBA automatically.','wpla'); ?>
							</p>

							<label for="wpl-fba_wc_shipping_options" class="fba_option text_label">
								<?php echo __('Enable FBA Shipping Options','wpla') ?>
                                <?php wpla_tooltip('Enable this option to allow your customers to select from available FBA shipping options (Standard, Expedited, Priority) when purchasing FBA items on your website.<br><br>(Shows actual shipping fees from Amazon.<br>Only  if all cart items are available on FBA.<br>Requires Multi-Channel Fulfillment.)') ?>
							</label>
							<select id="wpl-fba_wc_shipping_options" name="wpla_fba_wc_shipping_options" class="fba_option required-entry select">
								<option value="0" <?php if ( $wpl_fba_wc_shipping_options != '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('No','wpla'); ?></option>
								<option value="1" <?php if ( $wpl_fba_wc_shipping_options == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Yes','wpla'); ?></option>
							</select>

							<p class="desc fba_option" style="display: block;">
								<?php echo __('Enable FBA shipping options (Standard, Expedited, Priority) on WooCommerce checkout.','wpla'); ?>
								<?php if ( $wpl_fba_wc_shipping_options == '1' ): ?>
									<a href="admin.php?page=wc-settings&amp;tab=shipping&amp;section=wpla_shipping_method">Settings</a>
								<?php endif; ?>
							</p>

							<label for="wpl-fba_default_notification" class="fba_option text_label">
								<?php echo __('Enable customer notification','wpla') ?>
                                <?php wpla_tooltip('This will use the customer billing email address as <i>NotificationEmail</i> when submitting orders to FBA.') ?>
							</label>
							<select id="wpl-fba_default_notification" name="wpla_fba_default_notification" class="fba_option required-entry select">
								<option value="0" <?php if ( $wpl_fba_default_notification != '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('No','wpla'); ?></option>
								<option value="1" <?php if ( $wpl_fba_default_notification == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Yes','wpla'); ?></option>
							</select>

							<p class="desc fba_option" style="display: block;">
								<?php echo __('Let Amazon notify the customer about FBA shipments.','wpla'); ?>
							</p>
					        <!-- ## END PRO ## -->

							<label for="wpl-fba_report_schedule" class="fba_option text_label">
								<?php echo __('Request FBA reports','wpla') ?>
                                <?php wpla_tooltip('If you use multi-channel fulfillment with eBay orders, you should lower this option to 6 hours.') ?>
							</label>
							<select id="wpl-fba_report_schedule" name="wpla_fba_report_schedule" class="fba_option required-entry select">
								<option value="daily"        <?php if ( $wpl_fba_report_schedule == 'daily'        ): ?>selected="selected"<?php endif; ?>><?php echo __('Daily','wpla') ?> (default)</option>
								<option value="twelve_hours" <?php if ( $wpl_fba_report_schedule == 'twelve_hours' ): ?>selected="selected"<?php endif; ?>><?php echo __('Every 12 hours','wpla') ?></option>
								<option value="six_hours"    <?php if ( $wpl_fba_report_schedule == 'six_hours'    ): ?>selected="selected"<?php endif; ?>><?php echo __('Every 6 hours','wpla') ?></option>
								<option value="three_hours"  <?php if ( $wpl_fba_report_schedule == 'three_hours'  ): ?>selected="selected"<?php endif; ?>><?php echo __('Every 3 hours','wpla') ?></option>
							</select>
							<p class="desc fba_option" style="display: block;">
								<?php echo __('Select how often FBA Shipment and Inventory Reports should be fetched from Amazon.','wpla'); ?>
							</p>

						</div>
					</div>


			        <!-- ## BEGIN PRO ## -->
					<div class="postbox" id="OtherSettingsBox">
						<h3 class="hndle"><span><?php echo __('WooCommerce orders','wpla') ?></span></h3>
						<div class="inside">

							<label for="wpl-option-create_orders" class="text_label">
								<?php echo __('Create orders','wpla') ?>
                                <?php wpla_tooltip('Enable this if you want WP-Lister to create orders in WooCommerce from sales on Amazon.') ?>
							</label>
							<select id="wpl-option-create_orders" name="wpla_option_create_orders" class=" required-entry select">
								<option value="1" <?php if ( $wpl_option_create_orders == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Yes','wpla'); ?></option>
								<option value="0" <?php if ( $wpl_option_create_orders != '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('No','wpla'); ?></option>
							</select>

							<label for="wpl-fetch_orders_filter" class="text_label">
								<?php echo __('Filter orders','wpla'); ?>
                                <?php wpla_tooltip('Select whether you want to import orders placed on all allowed marketplaces, or only orders placed on the marketplace defined in the account settings.<br><br>Please enable this option if you have added multiple Amazon sites / marketplaces using the same Merchant ID.<br><br>Important: If you enable that option, you need to add an account for every marketplace you are selling on, or WP-Lister will not be able to fetch all orders.') ?>
							</label>
							<select id="wpl-fetch_orders_filter" name="wpla_fetch_orders_filter" class="required-entry select">
								<option value="0" <?php if ( $wpl_fetch_orders_filter == '0' ): ?>selected="selected"<?php endif; ?>><?php echo __('No, import all orders','wpla'); ?> (default)</option>
								<option value="1" <?php if ( $wpl_fetch_orders_filter == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Yes, import only orders from main marketplace','wpla'); ?></option>
							</select>

							<label for="wpl-skip_foreign_item_orders" class="text_label">
								<?php echo __('Skip orders for foreign items','wpla') ?>
                                <?php wpla_tooltip('If you have items listed on Amazon which do not exist in WP-Lister, you can enable this option to skip orders which do not contain any known order line items.<br><br>Orders which contain both known and foreign items will still be created in WooCommerce.') ?>
							</label>
							<select id="wpl-skip_foreign_item_orders" name="wpla_skip_foreign_item_orders" class=" required-entry select">
								<option value="0" <?php if ( $wpl_skip_foreign_item_orders != '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('No','wpla'); ?> (<?php _e('default','wpla'); ?>)</option>
								<option value="1" <?php if ( $wpl_skip_foreign_item_orders == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Yes','wpla'); ?></option>
							</select>
							<!-- <p class="desc" style="display: block;">
								<?php echo __('Enable this option to create orders in WooCommerce only for items which exist in WP-Lister.','wpla'); ?><br>
							</p> -->

							<label for="wpl-option-new_order_status" class="text_label">
								<?php echo __('Status for unshipped orders','wpla') ?>
                                <?php wpla_tooltip('Select the WooCommerce order status for orders which have been completed on Amazon and are waiting to be shipped.<br>The default status is <i>Processing</i>.') ?>
							</label>
							<select id="wpl-option-new_order_status" name="wpla_option_new_order_status" class=" required-entry select">
								<?php foreach ( wc_get_order_statuses() as $status_slug => $status_name ) : ?>
									<?php $status_slug = str_replace( 'wc-', '', $status_slug ); ?>
									<option value="<?php echo $status_slug ?>" <?php if ( $wpl_option_new_order_status == $status_slug ): ?>selected="selected"<?php endif; ?>><?php echo $status_name ?>
								<?php endforeach; ?>
								<!-- <option value="completed" <?php if ( $wpl_option_new_order_status == 'completed' ): ?>selected="selected"<?php endif; ?>><?php echo __('completed','wpla'); ?></option> -->
								<!-- <option value="processing"  <?php if ( $wpl_option_new_order_status != 'completed' ): ?>selected="selected"<?php endif; ?>><?php echo __('processing','wpla'); ?></option> -->
							</select>

							<label for="wpl-option-shipped_order_status" class="text_label">
								<?php echo __('Status for shipped orders','wpla') ?>
                                <?php wpla_tooltip('Select the WooCommerce order status for orders which have been completed on Amazon and are marked as shipped.<br>The default status is <i>Completed</i>.') ?>
							</label>
							<select id="wpl-option-shipped_order_status" name="wpla_option_shipped_order_status" class=" required-entry select">
								<?php foreach ( wc_get_order_statuses() as $status_slug => $status_name ) : ?>
									<?php $status_slug = str_replace( 'wc-', '', $status_slug ); ?>
									<option value="<?php echo $status_slug ?>" <?php if ( $wpl_option_shipped_order_status == $status_slug ): ?>selected="selected"<?php endif; ?>><?php echo $status_name ?>
								<?php endforeach; ?>
								<!-- <option value="completed" <?php if ( $wpl_option_shipped_order_status == 'completed' ): ?>selected="selected"<?php endif; ?>><?php echo __('completed','wpla'); ?></option> -->
								<!-- <option value="processing"  <?php if ( $wpl_option_shipped_order_status != 'completed' ): ?>selected="selected"<?php endif; ?>><?php echo __('processing','wpla'); ?></option> -->
							</select>

							<label for="wpl-option-use_amazon_order_number" class="text_label">
								<?php echo __('Use Amazon Order Number','wpla') ?>
								<?php wpla_tooltip('Enable this if you want WP-Lister use the order number from Amazon when creating new orders') ?>
							</label>
							<select id="wpl-option-use_amazon_order_number" name="wpla_option_use_amazon_order_number" class=" required-entry select">
								<option value="1" <?php selected( $wpl_option_use_amazon_order_number, 1 ); ?>><?php echo __('Yes','wpla'); ?></option>
								<option value="0" <?php selected( $wpl_option_use_amazon_order_number, 0 ); ?>><?php echo __('No','wpla'); ?></option>
							</select>

							<label for="wpl-disable_new_order_emails" class="text_label">
								<?php echo __('Disable New Order emails','wpla'); ?>
                                <?php wpla_tooltip('Disable New Order notifications being sent to the admin when an Amazon order is created.') ?>
							</label>
							<select id="wpl-disable_new_order_emails" name="wpla_disable_new_order_emails" class="required-entry select">
								<option value=""  <?php if ( $wpl_disable_new_order_emails == ''  ): ?>selected="selected"<?php endif; ?>><?php echo __('No','wpla'); ?></option>
								<option value="1" <?php if ( $wpl_disable_new_order_emails == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Yes','wpla'); ?> (recommended)</option>
							</select>

                            <label for="wpl-disable_on_hold_order_emails" class="text_label">
                                <?php echo __('Disable On-hold Order emails','wpla'); ?>
                                <?php wpla_tooltip('Disable email notifications being sent to the customer when an Amazon order is created with status on-hold.') ?>
                            </label>
                            <select id="wpl-disable_on_hold_order_emails" name="wpla_disable_on_hold_order_emails" class="required-entry select">
                                <option value="0"  <?php selected( $wpl_disable_on_hold_order_emails, 0 ); ?>><?php echo __('No','wpla'); ?></option>
                                <option value="1" <?php selected( $wpl_disable_on_hold_order_emails, 1 ); ?>><?php echo __('Yes','wpla'); ?> (recommended)</option>
                            </select>

							<label for="wpl-disable_processing_order_emails" class="text_label">
								<?php echo __('Disable Processing Order emails','wpla'); ?>
                                <?php wpla_tooltip('Disable email notifications being sent to the customer when an Amazon order is created with status processing.') ?>
							</label>
							<select id="wpl-disable_processing_order_emails" name="wpla_disable_processing_order_emails" class="required-entry select">
								<option value=""  <?php if ( $wpl_disable_processing_order_emails == ''  ): ?>selected="selected"<?php endif; ?>><?php echo __('No','wpla'); ?></option>
								<option value="1" <?php if ( $wpl_disable_processing_order_emails == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Yes','wpla'); ?> (recommended)</option>
							</select>

							<label for="wpl-disable_completed_order_emails" class="text_label">
								<?php echo __('Disable Completed Order emails','wpla'); ?>
                                <?php wpla_tooltip('Disable email notifications being sent to the customer when an Amazon order is created with status completed.') ?>
							</label>
							<select id="wpl-disable_completed_order_emails" name="wpla_disable_completed_order_emails" class="required-entry select">
								<option value=""  <?php if ( $wpl_disable_completed_order_emails == ''  ): ?>selected="selected"<?php endif; ?>><?php echo __('No','wpla'); ?></option>
								<option value="1" <?php if ( $wpl_disable_completed_order_emails == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Yes','wpla'); ?> (recommended)</option>
							</select>

							<label for="wpl-disable_changed_order_emails" class="text_label">
								<?php echo __('Disable emails on status change','wpla'); ?>
                                <?php wpla_tooltip('Disable email notifications being sent to the customer when the order status of an Amazon order is changed manually.') ?>
							</label>
							<select id="wpl-disable_changed_order_emails" name="wpla_disable_changed_order_emails" class="required-entry select">
								<option value=""  <?php if ( $wpl_disable_changed_order_emails == ''  ): ?>selected="selected"<?php endif; ?>><?php echo __('No','wpla'); ?></option>
								<option value="1" <?php if ( $wpl_disable_changed_order_emails == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Yes','wpla'); ?> (recommended)</option>
							</select>

							<label for="wpl-disable_new_account_emails" class="text_label">
								<?php echo __('Disable New Account emails','wpla'); ?>
                                <?php wpla_tooltip('Disable New Account notifications being sent to the user when a customer account is created.') ?>
							</label>
							<select id="wpl-disable_new_account_emails" name="wpla_disable_new_account_emails" class="required-entry select">
								<option value=""  <?php if ( $wpl_disable_new_account_emails == ''  ): ?>selected="selected"<?php endif; ?>><?php echo __('No','wpla'); ?></option>
								<option value="1" <?php if ( $wpl_disable_new_account_emails == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Yes','wpla'); ?> (recommended)</option>
							</select>

							<p class="desc" style="display: block;">
								<?php echo __('WooCommerce sends out various notifications when an order status is changed.','wpla'); ?><br>
								<?php echo __('WP-Lister can disable these notifications when creating Amazon orders in WooCommerce.','wpla'); ?>
							</p>

							<label for="wpl-create_orders_without_email" class="text_label">
								<?php echo __('Leave email address empty','wpla'); ?>
                                <?php wpla_tooltip('Enable this option to create WooCommerce orders without email address.<br><br>The email addresses provided by Amazon are not real customer email addresses and should not be used for marketing purposes.') ?>
							</label>
							<select id="wpl-create_orders_without_email" name="wpla_create_orders_without_email" class="required-entry select">
								<option value=""  <?php if ( $wpl_create_orders_without_email == ''  ): ?>selected="selected"<?php endif; ?>><?php echo __('No','wpla'); ?></option>
								<option value="1" <?php if ( $wpl_create_orders_without_email == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Yes','wpla'); ?></option>
							</select>

							<p class="desc" style="display: block;">
								<?php echo __('Create orders without email addresses to make sure that no plugin sends marketing emails via Amazon.','wpla'); ?>
							</p>

							<label for="wpl-auto_complete_sales" class="text_label">
								<?php echo __('Mark as shipped on Amazon','wpla'); ?>
                                <?php wpla_tooltip('This completes an Amazon order with the shipping date set to today when the order status is changed to completed.<br>Only applicable if default new order status is <em>processing</em>.') ?>
							</label>
							<select id="wpl-auto_complete_sales" name="wpla_auto_complete_sales" class="required-entry select">
								<option value=""  <?php if ( $wpl_auto_complete_sales == ''  ): ?>selected="selected"<?php endif; ?>><?php echo __('No','wpla'); ?></option>
								<option value="1" <?php if ( $wpl_auto_complete_sales == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Yes','wpla'); ?> (recommended)</option>
							</select>

							<p class="desc" style="display: block;">
								<?php echo __('Automatically mark an order as shipped on Amazon when it is completed in WooCommerce.','wpla'); ?>
							</p>

							<label for="wpl-default_shipping_provider" class="text_label">
								<?php echo __('Default Shipping Provider','wpla') ?>
                                <?php wpla_tooltip('Select which shipping provider should be used when marking orders as shipped automatically on Amazon.') ?>
							</label>
							<select id="wpl-default_shipping_provider" name="wpla_default_shipping_provider" class=" required-entry select">
								<option value=""   <?php if ( $wpl_default_shipping_provider == '' ):   ?>selected="selected"<?php endif; ?>>-- <?php echo __('none','wpla') ?> --</option>
				                <?php foreach (WPLA_Order_MetaBox::getShippingProviders() as $provider) : ?>
									<option value="<?php echo $provider ?>"   <?php if ( $wpl_default_shipping_provider == $provider ):   ?>selected="selected"<?php endif; ?>><?php echo $provider ?></option>
				                <?php endforeach; ?>
							</select>

							<label for="wpl-default_shipping_service_name" class="text_label other_shipping_option">
								<?php echo __('Default Shipping Provider Name','wpla') ?>
                                <?php wpla_tooltip('Enter the name of your shipping provider.') ?>
							</label>
							<input type="text" name="wpla_default_shipping_service_name" id="wpl-default_shipping_service_name" value="<?php echo $wpl_default_shipping_service_name; ?>" class="text_input other_shipping_option" />

                            <label for="wpl-option-orders-tax-mode" class="text_label">
                                <?php _e( 'Order tax processing', 'wpla' ); ?>
                                <?php wpla_tooltip( 'How should WP-Lister handle order taxes?<br/><br/><strong>None</strong><br/>No taxes will be processed.<br/><br/><strong>Automatically detect taxes</strong><br/>Automatically calculate the taxes based on the products\' tax class and your WooCommerce tax rates.<br/><br/><strong>Fixed Rate (VAT)</strong><br/>Set a global VAT rate for all order items.<br/><br/><strong>Import from Amazon</strong><br/>Use sales tax data as provided by Amazon.'); ?>
                            </label>
                            <select id="wpl-option-orders-tax-mode" name="wpla_orders_tax_mode" class="required-entry select">
                                <option value=""            <?php selected( $wpl_orders_tax_mode, '' ); ?>><?php _e( 'None', 'wpla' ); ?></option>
                                <option value="autodetect"  <?php selected( $wpl_orders_tax_mode, 'autodetect' ); ?>><?php _e( 'Automatically detect taxes', 'wpla' ); ?></option>
                                <option value="fixed"       <?php selected( $wpl_orders_tax_mode, 'fixed' ); ?>><?php _e( 'Fixed rate (VAT)', 'wpla' ); ?></option>
                                <option value="import"      <?php selected( $wpl_orders_tax_mode, 'import' ); ?>><?php _e( 'Import from Amazon', 'wpla' ); ?></option>
                            </select>

							<label for="wpl-option-orders_tax_rate_id" class="text_label fixed_tax import_tax">
								<?php echo __('Tax rate','wpla') ?>
                                <?php wpla_tooltip('Select the WooCommerce tax rate that will used for creating orders.<br>(Required for both "Fixed Rate" and "Import" tax processing mode.)') ?>
							</label>
							<select id="wpl-option-orders_tax_rate_id" name="wpla_orders_tax_rate_id" class="required-entry select fixed_tax import_tax">
								<option value="">-- <?php echo __('no tax rate','wpla'); ?> --</option>
								<?php foreach ($wpl_tax_rates as $rate) : ?>
									<option value="<?php echo $rate->tax_rate_id ?>" <?php if ( $wpl_orders_tax_rate_id == $rate->tax_rate_id ): ?>selected="selected"<?php endif; ?>><?php echo $rate->tax_rate_name ?></option>					
								<?php endforeach; ?>
							</select>

							<label for="wpl-text-orders_fixed_vat_rate" class="text_label fixed_tax">
								<?php echo __('Tax rate (percent)','wpla'); ?>
                                <?php wpla_tooltip('To apply VAT to created orders, enter the tax rate here.<br>Example: For 19% VAT enter "19".') ?>
							</label>
							<input type="text" name="wpla_orders_fixed_vat_rate" id="wpl-text-orders_fixed_vat_rate" value="<?php echo $wpl_orders_fixed_vat_rate; ?>" class="text_input fixed_tax" />

							<label for="wpl-option-record-discounts" class="text_label">
								<?php echo __('Record Promo Discounts','wpla'); ?>
								<?php wpla_tooltip('Store promotion discounts from Amazon as Order Discounts when creating WooCommerce Orders.') ?>
							</label>
							<select id="wpl-option-record-discounts" name="wpla_option_record_discounts" class=" required-entry select">
								<option value="0" <?php selected( 0, $wpl_option_record_discounts ); ?>><?php echo __('No','wpla'); ?></option>
								<option value="1" <?php selected( 1, $wpl_option_record_discounts ); ?>><?php echo __('Yes','wpla'); ?></option>
							</select>

							<label for="wpl-text-orders_default_payment_title" class="text_label">
								<?php echo __('Default payment title','wpla'); ?>
                                <?php wpla_tooltip('The payment method in Amazon orders often defaults to "Other". Enter your own payment title here which will be used instead of "Other" when creating orders in WooCommerce.') ?>
							</label>
							<input type="text" name="wpla_orders_default_payment_title" id="wpl-text-orders_default_payment_title" value="<?php echo $wpl_orders_default_payment_title; ?>" placeholder="Other" class="text_input" />

							<label for="wpl-option-create_customers" class="text_label">
								<?php echo __('Create customers','wpla') ?>
                                <?php wpla_tooltip('Enable this to create Amazon customers as WordPress users when creating orders.<br><br>Note: Amazon hides the customers real email address. It only provides an anonymized email address which will be used to create user accounts.') ?>
							</label>
							<select id="wpl-option-create_customers" name="wpla_option_create_customers" class=" required-entry select">
								<option value="0" <?php if ( $wpl_option_create_customers != '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('No','wpla'); ?></option>
								<option value="1" <?php if ( $wpl_option_create_customers == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Yes','wpla'); ?></option>
							</select>

							<p class="desc" style="display: block;">
								<?php echo __('Create WordPress user accounts for your customers.','wpla'); ?>
							</p>

						</div>
					</div>
			        <!-- ## END PRO ## -->


				<?php if ( ( is_multisite() ) && ( is_main_site() ) ) : ?>
				<p>
					<b>Warning:</b> Deactivating WP-Lister on a multisite network will remove all settings and data from all sites.
				</p>
				<?php endif; ?>


				</div> <!-- .meta-box-sortables -->
			</div> <!-- #postbox-container-1 -->



		</div> <!-- #post-body -->
		<br class="clear">
	</div> <!-- #poststuff -->

	</form>






	<script type="text/javascript">
		jQuery( document ).ready( function($) {

			// hide FBA options if FBA is disabled
			$('#wpl-fba_enabled').change(function() {
				if ( $('#wpl-fba_enabled').val() != 1 ) {
					$('#FBAOptionsBox .fba_option').hide();
				} else {
					$('#FBAOptionsBox .fba_option').show();
				}
			}).change();

			// hide shipping provider name option unless "Other" is selected
			$('#wpl-default_shipping_provider').change(function() {
				if ( $('#wpl-default_shipping_provider').val() != 'Other' ) {
					$('#OtherSettingsBox .other_shipping_option').hide();
				} else {
					$('#OtherSettingsBox .other_shipping_option').show();
				}
			}).change();

			// Toggle Fixed Tax elements
            $('#wpl-option-orders-tax-mode').change(function() {

                // initially hide the elements
                $('.fixed_tax, .import_tax').hide();

                if ( $(this).val() == 'fixed' ) {
                    $('.fixed_tax').show();
                } else if ( $(this).val() == 'import' ) {
                    $('.import_tax').show();
                }
            }).change();

		});
	
	</script>


</div>