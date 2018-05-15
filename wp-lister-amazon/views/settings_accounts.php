<?php include_once( dirname(__FILE__).'/common_header.php' ); ?>

<style type="text/css">
	
	div.tablenav.top { display: none; }

	th.column-site {
		width: 20%;
	}
	th.column-status {
		width: 15%;
	}

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

	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">

			<div id="postbox-container-1" class="postbox-container">
				<div id="side-sortables" class="meta-box">


					<!-- first sidebox -->
					<div class="postbox" id="submitdiv">
						<!--<div title="Click to toggle" class="handlediv"><br></div>-->
						<h3 class="hndle"><span><?php echo __('Account Status','wpla'); ?></span></h3>
						<div class="inside">

							<div id="submitpost" class="submitbox">

								<div id="misc-publishing-actions">
									<div class="misc-pub-section">
										<?php if ( sizeof( $wpl_amazon_accounts ) == 0 ) : ?>
											<p><?php echo __('WP-Lister is not linked to your Amazon account yet.','wpla') ?></p>
										<?php else : ?>
											<p><?php echo __('Great, you have added at least one account.','wpla') ?></p>
										<?php endif; ?>

										<?php if ( ! $wpl_default_account ) : ?>
											<p><?php echo __('You need to select a default account.','wpla') ?></p>
										<?php endif; ?>

									</div>
								</div>

								<div id="major-publishing-actions">
									<div id="publishing-action">
										<!-- <input type="submit" value="<?php echo __('Save Settings','wpla'); ?>" id="save_settings" class="button-primary" name="save"> -->
									</div>
									<div class="clear"></div>
								</div>

							</div>

						</div>
					</div>

					<div class="postbox" id="HelpInfoBox">
						<h3 class="hndle"><span><?php echo __('Help','wpla') ?></span></h3>
						<div class="inside">
							
							<p>
								<h4><?php _e('Adding your Amazon account','wpla') ?></h4>
								<ol>
									<li><?php _e('Enter a short account title','wpla') ?></li>
									<li><?php _e('Select an Amazon marketplace','wpla') ?></li>
									<li><?php _e('Follow the step-by-step instructions which will appear below','wpla') ?></li>
								</ol>
							</p>

						</div>
					</div>

				</div>
			</div> <!-- #postbox-container-1 -->


			<!-- #postbox-container-2 -->
			<div id="postbox-container-2" class="postbox-container">
				<div class="meta-box-sortables ui-sortable">
					
				<?php if ( sizeof( $wpl_amazon_accounts ) == 0 ) : ?>
				
					<div class="postbox" id="AuthSettingsBox">
						<h3 class="hndle"><span><?php echo __('Amazon authorization','wpla') ?></span></h3>
						<div class="inside">
							<p><strong><?php echo __('Follow these steps to link WP-Lister with your Amazon account','wpla') ?></strong></p>

							<p>
								<?php _e('The Amazon Marketplace Web Service (MWS) allows WP-Lister to communicate with your Amazon Seller Account.','wpla') ?>
								<?php _e('Before you can start repricing, you will need to sign up for MWS, and then grant WP-Lister access to your account.','wpla') ?>
								<?php _e('In addition, once you sign up for MWS, you will be given your Merchant ID, Marketplace ID, AWS Access Key ID, and Secret Key.','wpla') ?>
							</p>

						</div>
					</div>

				<?php else: ?>

					<!-- show accounts table -->
				    <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
				    <form id="accounts-filter" method="post" action="<?php echo $wpl_form_action; ?>" >
				        <!-- For plugins, we also need to ensure that the form posts back to our current page -->
				        <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ) ?>" />
				        <!-- Now we can render the completed list table -->
				        <?php $wpl_accountsTable->display() ?>
				    </form>

					<div class="postbox" id="AccountsBox" style="display:none">
						<h3 class="hndle"><span><?php echo __('Accounts','wpla') ?></span></h3>
						<div class="inside">

						</div>
					</div>

				<?php endif; // $wpl_amazon_accounts ?>

				<?php require_once('settings_add_account.php') ?>

				</div> <!-- .meta-box-sortables -->
			</div> <!-- #postbox-container-1 -->


		</div> <!-- #post-body -->
		<br class="clear">
	</div> <!-- #poststuff -->



	<?php if ( isset( $_REQUEST['debug'] ) ) { echo "<pre>";print_r($wpl_amazon_accounts);echo"</pre>"; } ?>
	<?php #echo "<pre>";print_r($wpl_amazon_markets);echo"</pre>"; ?>


	<script type="text/javascript">
		jQuery( document ).ready(
			function () {

				// account details button
				jQuery('.wpla_btn_edit_account').click( function( ) {					
					jQuery( this ).nextAll('.amazon_account_details').slideToggle(300);
					return false;
				});

				// ask again before deleting items
				jQuery('a.delete').on('click', function() {
					return confirm("<?php echo __('Are you sure you want to remove this account from WP-Lister?','wpla') ?>");
				})
				// ask again before deleting items
				jQuery('.row-actions .delete_account a').on('click', function() {
					return confirm("<?php echo __('Are you sure you want to remove this account from WP-Lister?','wpla') ?>");
				})

			}
		);
	
	</script>


</div>