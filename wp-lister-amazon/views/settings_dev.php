<?php include_once( dirname(__FILE__).'/common_header.php' ); ?>

<style type="text/css">
	
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
						<h3 class="hndle"><span><?php echo __('Update','wpla'); ?></span></h3>
						<div class="inside">

							<div id="submitpost" class="submitbox">

								<div id="misc-publishing-actions">
									<div class="misc-pub-section">
										<p><?php echo __('This page contains some special options intended for developers and debugging.','wpla') ?></p>
										<p><?php echo sprintf( __('The daily maintenance ran %s ago.','wpla'), human_time_diff( get_option('wpla_daily_cron_last_run') ) ) ?></p>
									</div>
								</div>

								<div id="major-publishing-actions">
									<div id="publishing-action">
										<input type="hidden" name="action" value="save_wpla_devsettings" >
                                        <?php wp_nonce_field( 'wpla_save_devsettings' ); ?>
										<input type="submit" value="<?php echo __('Save Settings','wpla'); ?>" id="save_settings" class="button-primary" name="save">
									</div>
									<div class="clear"></div>
								</div>

							</div>

						</div>
					</div>

					<div class="postbox" id="VersionInfoBox">
						<h3 class="hndle"><span><?php echo __('Version Info','wpla') ?></span></h3>
						<div class="inside">

							<table style="width:100%">
								<tr><td>WP-Lister</td><td>	<?php echo WPLA_VERSION ?> </td></tr>
								<tr><td>Database</td><td> <?php echo get_option('wpla_db_version') ?> </td></tr>
								<tr><td>WordPress</td><td> <?php global $wp_version; echo $wp_version ?> </td></tr>
								<tr><td>WooCommerce</td><td> <?php echo defined('WC_VERSION') ? WC_VERSION : WOOCOMMERCE_VERSION ?> </td></tr>
							</table>

						</div>
					</div>

				</div>
			</div> <!-- #postbox-container-1 -->


			<!-- #postbox-container-2 -->
			<div id="postbox-container-2" class="postbox-container">
				<div class="meta-box-sortables ui-sortable">

					<div class="postbox" id="DbLoggingBox">
						<h3 class="hndle"><span><?php echo __('Logging and Maintenance','wpla') ?></span></h3>
						<div class="inside">

							<label for="wpl-option-log_to_db" class="text_label"><?php echo __('Log to database','wpla'); ?></label>
							<select id="wpl-option-log_to_db" name="wpla_option_log_to_db" title="Logging" class=" required-entry select">
								<option value="1" <?php if ( $wpl_option_log_to_db == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Yes','wpla'); ?></option>
								<option value="0" <?php if ( $wpl_option_log_to_db != '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('No','wpla'); ?></option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __('Enable to log all communication with Amazon to the database.','wpla'); ?>
							</p>

							<label for="wpl-option-log_record_limit" class="text_label">
								<?php echo __('Log entry size limit','wpla'); ?>
                                <?php wpla_tooltip('Limit the maximum size of a single log record. The default value is 4 kb.') ?>
							</label>
							<select id="wpl-option-log_record_limit" name="wpla_log_record_limit" class=" required-entry select">
								<option value="4096"  <?php if ( $wpl_log_record_limit == '4096' ):  ?>selected="selected"<?php endif; ?>>4 kb (default)</option>
								<option value="8192"  <?php if ( $wpl_log_record_limit == '8192' ):  ?>selected="selected"<?php endif; ?>>8 kb</option>
								<option value="64000" <?php if ( $wpl_log_record_limit == '64000' ): ?>selected="selected"<?php endif; ?>>64 kb</option>
							</select>

							<label for="wpl-option-log_days_limit" class="text_label">
								<?php echo __('Keep log records for','wpla'); ?>
                                <?php wpla_tooltip('Select how long log records should be kept. Older records are removed automatically. The default is 30 days.') ?>
							</label>
							<select id="wpl-option-log_days_limit" name="wpla_log_days_limit" class=" required-entry select">
								<option value="1"   <?php if ( $wpl_log_days_limit == '1' ):   ?>selected="selected"<?php endif; ?>> 1 day </option>
								<option value="2"   <?php if ( $wpl_log_days_limit == '2' ):   ?>selected="selected"<?php endif; ?>> 2 days</option>
								<option value="3"   <?php if ( $wpl_log_days_limit == '3' ):   ?>selected="selected"<?php endif; ?>> 3 days</option>
								<option value="7"   <?php if ( $wpl_log_days_limit == '7' ):   ?>selected="selected"<?php endif; ?>> 7 days</option>
								<option value="14"  <?php if ( $wpl_log_days_limit == '14' ):  ?>selected="selected"<?php endif; ?>>14 days</option>
								<option value="30"  <?php if ( $wpl_log_days_limit == '30' ):  ?>selected="selected"<?php endif; ?>>30 days (default)</option>
								<option value="60"  <?php if ( $wpl_log_days_limit == '60' ):  ?>selected="selected"<?php endif; ?>>60 days</option>
								<option value="90"  <?php if ( $wpl_log_days_limit == '90' ):  ?>selected="selected"<?php endif; ?>>90 days</option>
							</select>

							<label for="wpl-option-stock_days_limit" class="text_label">
								<?php echo __('Keep stock log for','wpla'); ?>
                                <?php wpla_tooltip('Select how long stock log records should be kept. Older records are removed automatically. The default is 180 days.') ?>
							</label>
							<select id="wpl-option-stock_days_limit" name="wpla_stock_days_limit" class=" required-entry select">
								<option value="7"   <?php if ( $wpl_stock_days_limit == '7' ):   ?>selected="selected"<?php endif; ?>> 7 days</option>
								<option value="14"  <?php if ( $wpl_stock_days_limit == '14' ):  ?>selected="selected"<?php endif; ?>>14 days</option>
								<option value="30"  <?php if ( $wpl_stock_days_limit == '30' ):  ?>selected="selected"<?php endif; ?>>30 days</option>
								<option value="60"  <?php if ( $wpl_stock_days_limit == '60' ):  ?>selected="selected"<?php endif; ?>>60 days</option>
								<option value="90"  <?php if ( $wpl_stock_days_limit == '90' ):  ?>selected="selected"<?php endif; ?>>90 days</option>
								<option value="180" <?php if ( $wpl_stock_days_limit == '180' ): ?>selected="selected"<?php endif; ?>>180 days (default)</option>
							</select>

							<label for="wpl-option-reports_days_limit" class="text_label">
								<?php echo __('Keep reports for','wpla'); ?>
                                <?php wpla_tooltip('Select how long Amazon reports should be kept. Older reports are removed automatically. The default is 90 days.') ?>
							</label>
							<select id="wpl-option-reports_days_limit" name="wpla_reports_days_limit" class=" required-entry select">
								<option value="7"   <?php if ( $wpl_reports_days_limit == '7' ):   ?>selected="selected"<?php endif; ?>> 7 days</option>
								<option value="14"  <?php if ( $wpl_reports_days_limit == '14' ):  ?>selected="selected"<?php endif; ?>>14 days</option>
								<option value="30"  <?php if ( $wpl_reports_days_limit == '30' ):  ?>selected="selected"<?php endif; ?>>30 days</option>
								<option value="60"  <?php if ( $wpl_reports_days_limit == '60' ):  ?>selected="selected"<?php endif; ?>>60 days</option>
								<option value="90"  <?php if ( $wpl_reports_days_limit == '90' ):  ?>selected="selected"<?php endif; ?>>90 days (default)</option>
							</select>

							<label for="wpl-option-feeds_days_limit" class="text_label">
								<?php echo __('Keep feeds for','wpla'); ?>
                                <?php wpla_tooltip('Select how long Amazon feeds should be kept. Older feeds are removed automatically. The default is 90 days.') ?>
							</label>
							<select id="wpl-option-feeds_days_limit" name="wpla_feeds_days_limit" class=" required-entry select">
								<option value="7"   <?php if ( $wpl_feeds_days_limit == '7' ):   ?>selected="selected"<?php endif; ?>> 7 days</option>
								<option value="14"  <?php if ( $wpl_feeds_days_limit == '14' ):  ?>selected="selected"<?php endif; ?>>14 days</option>
								<option value="30"  <?php if ( $wpl_feeds_days_limit == '30' ):  ?>selected="selected"<?php endif; ?>>30 days</option>
								<option value="60"  <?php if ( $wpl_feeds_days_limit == '60' ):  ?>selected="selected"<?php endif; ?>>60 days</option>
								<option value="90"  <?php if ( $wpl_feeds_days_limit == '90' ):  ?>selected="selected"<?php endif; ?>>90 days (default)</option>
							</select>

							<label for="wpl-option-orders_days_limit" class="text_label">
								<?php echo __('Keep sales data for','wpla'); ?>
                                <?php wpla_tooltip('Select how long Amazon orders should be kept. Older orders are removed from WP-Lister automatically but will remain in WooCommerce. The default is forever.') ?>
							</label>
							<select id="wpl-option-orders_days_limit" name="wpla_orders_days_limit" class=" required-entry select">
								<option value=""    <?php if ( $wpl_orders_days_limit == ''   ):  ?>selected="selected"<?php endif; ?>>forever (default)</option>
								<option value="90"  <?php if ( $wpl_orders_days_limit == '90' ):  ?>selected="selected"<?php endif; ?>>90 days</option>
								<option value="180" <?php if ( $wpl_orders_days_limit == '180' ): ?>selected="selected"<?php endif; ?>>180 days</option>
								<option value="365" <?php if ( $wpl_orders_days_limit == '365' ): ?>selected="selected"<?php endif; ?>>1 year</option>
							</select>

						</div>
					</div>

					<!--
					<div class="postbox" id="ErrorHandlingBox">
						<h3 class="hndle"><span><?php echo __('Error handling','wpla') ?></span></h3>
						<div class="inside">

							<label for="wpl-option-ajax_error_handling" class="text_label"><?php echo __('Handle 404 errors for admin-ajax.php','wpla'); ?></label>
							<select id="wpl-option-ajax_error_handling" name="wpla_ajax_error_handling" class=" required-entry select">
								<option value="halt" <?php if ( $wpl_ajax_error_handling == 'halt' ): ?>selected="selected"<?php endif; ?>><?php echo __('Halt on error','wpla'); ?></option>
								<option value="skip" <?php if ( $wpl_ajax_error_handling == 'skip' ): ?>selected="selected"<?php endif; ?>><?php echo __('Continue with next item','wpla'); ?></option>
								<option value="retry" <?php if ( $wpl_ajax_error_handling == 'retry' ): ?>selected="selected"<?php endif; ?>><?php echo __('Try again','wpla'); ?></option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __('404 errors for admin-ajax.php should actually never happen and are generally a sign of incorrect server configuration.','wpla'); ?>
								<?php echo __('This setting is just a workaround. You should consider moving to a proper hosting provider instead.','wpla'); ?>
							</p>

						</div>
					</div>
					-->

					<div class="postbox" id="FeedsOptionsBox" style="display:block;">
						<h3 class="hndle"><span><?php echo __('Feeds','wpla') ?></span></h3>
						<div class="inside">

							<label for="wpl-option-max_feed_size" class="text_label"><?php echo __('Maximum feed size','wpla'); ?></label>
							<select id="wpl-option-max_feed_size" name="wpla_max_feed_size" class=" required-entry select">
								<option value="10"    <?php if ( $wpl_max_feed_size == '10' ):    ?>selected="selected"<?php endif; ?>>10</option>
								<option value="20"    <?php if ( $wpl_max_feed_size == '20' ):    ?>selected="selected"<?php endif; ?>>20</option>
								<option value="50"    <?php if ( $wpl_max_feed_size == '50' ):    ?>selected="selected"<?php endif; ?>>50</option>
								<option value="100"   <?php if ( $wpl_max_feed_size == '100' ):   ?>selected="selected"<?php endif; ?>>100</option>
								<option value="200"   <?php if ( $wpl_max_feed_size == '200' ):   ?>selected="selected"<?php endif; ?>>200</option>
								<option value="500"   <?php if ( $wpl_max_feed_size == '500' ):   ?>selected="selected"<?php endif; ?>>500</option>
								<option value="1000"  <?php if ( $wpl_max_feed_size == '1000' ):  ?>selected="selected"<?php endif; ?>>1000 (Default)</option>
								<option value="2000"  <?php if ( $wpl_max_feed_size == '2000' ):  ?>selected="selected"<?php endif; ?>>2000</option>
								<option value="3000"  <?php if ( $wpl_max_feed_size == '3000' ):  ?>selected="selected"<?php endif; ?>>3000</option>
								<option value="5000"  <?php if ( $wpl_max_feed_size == '5000' ):  ?>selected="selected"<?php endif; ?>>5000</option>
								<option value="10000" <?php if ( $wpl_max_feed_size == '10000' ): ?>selected="selected"<?php endif; ?>>10000</option>
							</select>
							<p class="desc" style="display: block;">
								If you get a timeout error when opening the feeds page, please try to lower this value.
							</p>

							<label for="wpl-option-feed_encoding" class="text_label"><?php echo __('Feed encoding','wpla'); ?></label>
							<select id="wpl-option-feed_encoding" name="wpla_feed_encoding" class=" required-entry select">
								<option value="ISO-8859-1"  <?php if ( $wpl_feed_encoding == 'ISO-8859-1' ):   	?>selected="selected"<?php endif; ?>>ISO-8859-1 (Default)</option>
								<option value="UTF-8"      	<?php if ( $wpl_feed_encoding == 'UTF-8' ):    		?>selected="selected"<?php endif; ?>>UTF-8</option>
							</select>
							<p class="desc" style="display: block;">
								It is recommended to use the character set ISO-8859-1 to avoid issues with special characters.
							</p>

							<label for="wpl-option-feed_currency_format" class="text_label"><?php echo __('Feed currency format','wpla'); ?></label>
							<select id="wpl-option-feed_currency_format" name="wpla_feed_currency_format" class=" required-entry select">
								<option value="auto"         <?php if ( $wpl_feed_currency_format == 'auto' ): 			?>selected="selected"<?php endif; ?>>Use decimal comma in Price &amp; Quantity feed on EU marketplaces (DE, FR, IT, ES) (<?php _e('default','wpla'); ?>)</option>
								<option value="legacy"       <?php if ( $wpl_feed_currency_format == 'legacy' ): 		?>selected="selected"<?php endif; ?>>Legacy mode (always use decimal point)</option>
							</select>
							<p class="desc" style="display: block;">
								Set this option to use decimal comma on EU Price &amp; Quantity feeds.
							</p>

                            <label for="wpl-option-feed_shipment_time" class="text_label"><?php echo __('Send Shipment Time','wpla'); ?></label>
                            <select id="wpl-option-feed_shipment_time" name="wpla_feed_shipment_time" class=" required-entry select">
                                <option value="0" <?php selected( $wpl_feed_include_shipment_time, 0 ); ?>>No (Default)</option>
                                <option value="1" <?php selected( $wpl_feed_include_shipment_time, 1 ); ?>>Yes</option>
                            </select>
                            <p class="desc" style="display: block;">
                                Enable to include the shipment time in ship-date column in Order Fulfillment Feed.
                            </p>

						</div>
					</div>

					<div class="postbox" id="StagingSiteSettingsBox">
						<h3 class="hndle"><span><?php echo __('Staging site','wpla') ?></span></h3>
						<div class="inside">

							<p>
								<?php echo __('If you frequently clone your WordPress installation to a staging site, you can make WP-Lister automatically disable background updates and order creation when running on the staging site.','wpla'); ?>
								<?php echo __('Enter a unique part of your staging domain below to activate this feature.','wpla'); ?><br>
							</p>
							<label for="wpl-staging_site_pattern" class="text_label">
								<?php echo __('Staging site pattern','wpla') ?>
								<?php $tip_msg  = __('You do not need to enter the full domain name of your staging site.','wpla'); ?>
								<?php $tip_msg .= __('If your staging domain is mydomain.staging.wpengine.com enter staging.wpengine.com as a general pattern.','wpla'); ?>
                                <?php wpla_tooltip($tip_msg) ?>
							</label>
							<input type="text" name="wpla_staging_site_pattern" id="wpl-staging_site_pattern" value="<?php echo $wpl_staging_site_pattern; ?>" class="text_input" />
							<p class="desc" style="display: block;">
								Example: staging.wpengine.com
							</p>

						</div>
					</div>

					<div class="postbox" id="DeveloperToolBox" style="display:block;">
						<h3 class="hndle"><span><?php echo __('Debug options','wpla') ?></span></h3>
						<div class="inside">

							<label for="wpl-inventory_check_batch_size" class="text_label">
								<?php echo __('Check inventory in batches of','wpla'); ?>
                                <?php wpla_tooltip('If your server times out or runs out of memory when using the inventory check tool you may have to lower this setting.') ?>
							</label>
							<select id="wpl-inventory_check_batch_size" name="wpla_inventory_check_batch_size" class=" required-entry select">
								<option value="20"   <?php if ( $wpl_inventory_check_batch_size == '20'   ): ?>selected="selected"<?php endif; ?>>20 items</option>
								<option value="50"   <?php if ( $wpl_inventory_check_batch_size == '50'   ): ?>selected="selected"<?php endif; ?>>50 items</option>
								<option value="100"  <?php if ( $wpl_inventory_check_batch_size == '100'  ): ?>selected="selected"<?php endif; ?>>100 items</option>
								<option value="200"  <?php if ( $wpl_inventory_check_batch_size == '200'  ): ?>selected="selected"<?php endif; ?>>200 items (default)</option>
								<option value="500"  <?php if ( $wpl_inventory_check_batch_size == '500'  ): ?>selected="selected"<?php endif; ?>>500 items</option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __('Set the batch size for the inventory check tools.','wpla'); ?>
							</p>

							<label for="wpl-option-enable_item_edit_link" class="text_label">
								<?php echo __('Allow direct editing','wpla'); ?>
                                <?php wpla_tooltip('Shows an additional "Edit" link on the listing page, which allows you to edit the listing database fields directly.<br><br>It is not recommended to use this option at all - all your changes will be overwritten when the linked product is updated in WooCommerce!') ?>
							</label>
							<select id="wpl-option-enable_item_edit_link" name="wpla_enable_item_edit_link" class=" required-entry select">
								<option value="0" <?php if ( $wpl_enable_item_edit_link == '0' ): ?>selected="selected"<?php endif; ?>><?php echo __('No','wpla'); ?> (default)</option>
								<option value="1" <?php if ( $wpl_enable_item_edit_link == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Yes','wpla'); ?></option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __('Enable editing listing records directly. Not recommended.','wpla'); ?>
							</p>

							<label for="wpl-show_browse_node_ids" class="text_label"><?php echo __('Show browse node ID','wpla'); ?></label>
							<select id="wpl-show_browse_node_ids" name="wpla_show_browse_node_ids" title="Logging" class=" required-entry select">
								<option value="0" <?php if ( $wpl_show_browse_node_ids != '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('No','wpla'); ?> (Default)</option>
								<option value="1" <?php if ( $wpl_show_browse_node_ids == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Yes','wpla'); ?></option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __('Show browse node IDs when selecting a category.','wpla'); ?>
							</p>

							<label for="wpl-text-log_level" class="text_label"><?php echo __('Log to logfile','wpla'); ?></label>
							<select id="wpl-text-log_level" name="wpla_text_log_level" title="Logging" class=" required-entry select">
								<option value=""> -- <?php echo __('no logfile','wpla'); ?> -- </option>
								<option value="2" <?php if ( $wpl_text_log_level == '2' ): ?>selected="selected"<?php endif; ?>>Error</option>
								<option value="3" <?php if ( $wpl_text_log_level == '3' ): ?>selected="selected"<?php endif; ?>>Critical</option>
								<option value="4" <?php if ( $wpl_text_log_level == '4' ): ?>selected="selected"<?php endif; ?>>Warning</option>
								<option value="5" <?php if ( $wpl_text_log_level == '5' ): ?>selected="selected"<?php endif; ?>>Notice</option>
								<option value="6" <?php if ( $wpl_text_log_level == '6' ): ?>selected="selected"<?php endif; ?>>Info</option>
								<option value="7" <?php if ( $wpl_text_log_level == '7' ): ?>selected="selected"<?php endif; ?>>Debug</option>
								<option value="9" <?php if ( $wpl_text_log_level == '9' ): ?>selected="selected"<?php endif; ?>>All</option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __('Write debug information to logfile.','wpla'); ?>
								<?php if ( $wpl_text_log_level > 1 ): ?>
									<?php $wpl_log_size = file_exists( WPLA()->logger->file ) ? filesize( WPLA()->logger->file ) : 0; ?>
									<?php echo __('Current log file size','wpla'); ?>: <?php echo round($wpl_log_size/1024/1024,1) ?> mb
									[<a href="<?php echo bloginfo('url') ?>/wp-content/uploads/wp-lister/wpla.log" target="_blank">view log</a>]
									<?php if ( file_exists( WPLA()->logger->file_prev ) ) : ?>
										[<a href="<?php echo bloginfo('url') ?>/wp-content/uploads/wp-lister/wpla-old.log" target="_blank">view previous log</a>]
										(<?php echo round( filesize( WPLA()->logger->file_prev ) /1024/1024,1) ?> mb)
									<?php endif; ?>
								<?php endif; ?>
							</p>

					        <!-- ## BEGIN PRO ## -->
							<label for="wpl-option-updater_mode" class="text_label"><?php echo __('Updater','wpla'); ?></label>
							<select id="wpl-option-updater_mode" name="wpla_updater_mode" class=" required-entry select">
								<option value="new"  <?php if ( $wpl_updater_mode == 'new' ):  ?>selected="selected"<?php endif; ?>>new version (0.9+)</option>
								<option value="old"  <?php if ( $wpl_updater_mode == 'old' ):  ?>selected="selected"<?php endif; ?>>old version (pre 0.9)</option>
							</select>

							<label for="wpl-wpla_instance" class="text_label"><?php echo __('Instance ID','wpla'); ?></label>
							<input type="text" name="wpla_wpla_instance" id="wpl-wpla_instance" value="<?php echo get_option('wpla_instance') ?>" class="text_input" />
							<p class="desc" style="display: block;">
								Don't change this unless you migrated your installation to a different domain.
							</p>
					        <!-- ## END PRO ## -->

						</div>
					</div>

					<!--
					<div class="submit" style="padding-top: 0; float: right;">
						<input type="submit" value="<?php echo __('Save Settings','wpla') ?>" name="submit" class="button-primary">
					</div>
					-->


				</div> <!-- .meta-box-sortables -->
			</div> <!-- #postbox-container-1 -->



		</div> <!-- #post-body -->
		<br class="clear">
	</div> <!-- #poststuff -->

	</form>


</div>