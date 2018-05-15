<?php include_once( dirname(__FILE__).'/common_header.php' ); ?>

<style type="text/css">

	.inside p {
		width: 60%;
	}

	a.right,
	input.button {
		float: right;
	}

</style>

<div class="wrap">
	<div class="icon32" style="background: url(<?php echo $wpl_plugin_url; ?>img/amazon-32x32.png) no-repeat;" id="wpl-icon"><br /></div>
	<!-- <h2><?php echo __('Tools','wpla') ?></h2> -->

	<?php include_once( dirname(__FILE__).'/tools_tabs.php' ); ?>
	<?php echo $wpl_message ?>


	<div style="width:640px;" class="postbox-container">
		<div class="metabox-holder">
			<div class="meta-box-sortables ui-sortable">
				

				<div class="postbox" id="InventoryToolBox" style="display:block;">
					<h3 class="hndle"><span><?php echo __('Inventory Check','wpla'); ?></span></h3>
					<div class="inside">

						<!-- check for out of sync products (published) --> 
						<form method="post" action="<?php echo $wpl_form_action; ?>">
								<?php wp_nonce_field( 'wpla_tools_page' ); ?>
								<input type="hidden" name="action" value="check_wc_out_of_sync" />
								<input type="hidden" name="mode" value="published" />
								<input type="hidden" name="prices" value="1" />
								<input type="submit" value="<?php echo __('Check product inventory and prices','wpla'); ?>" name="submit" class="button">
								<p><?php echo __('Check all published listings and find products with different stock or price in WooCommerce.','wpla'); ?>
									<br>
									<small>Note: If you are using price modifiers in your profile, this check could find false positives which are actually in sync.</small>
								</p>
						</form>
						<br style="clear:both;"/>

						<!-- check for out of sync products (published) --> 
						<form method="post" action="<?php echo $wpl_form_action; ?>">
								<?php wp_nonce_field( 'wpla_tools_page' ); ?>
								<input type="hidden" name="action" value="check_wc_out_of_sync" />
								<input type="hidden" name="mode" value="published" />
								<input type="hidden" name="prices" value="0" />
								<input type="submit" value="<?php echo __('Check product inventory only','wpla'); ?>" name="submit" class="button">
								<p><?php echo __('Check all published listings and find products with different stock levels in WooCommerce.','wpla'); ?></p>
						</form>
						<br style="clear:both;"/>

						<!-- check for out of sync products (sold) --> 
						<form method="post" action="<?php echo $wpl_form_action; ?>">
								<?php wp_nonce_field( 'wpla_tools_page' ); ?>
								<input type="hidden" name="action" value="check_wc_out_of_sync" />
								<input type="hidden" name="mode" value="sold" />
								<input type="hidden" name="prices" value="0" />
								<input type="submit" value="<?php echo __('Check sold listings','wpla'); ?>" name="submit" class="button">
								<p><?php echo __('Check all sold listings and find products with different stock levels in WooCommerce.','wpla'); ?>
								</p>
						</form>
						<br style="clear:both;"/>

						<!hr>

						<!-- check for sold products that are still in stock --> 
						<!--
						<form method="post" action="<?php echo $wpl_form_action; ?>">
								<?php wp_nonce_field( 'wpla_tools_page' ); ?>
								<input type="hidden" name="action" value="check_wc_sold_stock" />
								<input type="submit" value="<?php echo __('Check sold listings','wpla'); ?>" name="submit" class="button">
								<p><?php echo __('Check all sold listings and find products which are still in stock in WooCommerce.','wpla'); ?>
								</p>
						</form>
						<br style="clear:both;"/>
						-->

						<!-- check for out of stock products --> 
						<form method="post" action="<?php echo $wpl_form_action; ?>">
								<?php wp_nonce_field( 'wpla_tools_page' ); ?>
								<input type="hidden" name="action" value="check_wc_out_of_stock" />
								<input type="submit" value="<?php echo __('Check out of stock products','wpla'); ?>" name="submit" class="button">
								<p><?php echo __('Check all published listings and find products which are out of stock in WooCommerce.','wpla'); ?>
								</p>
						</form>
						<br style="clear:both;"/>

						<!-- check FBA stock levels --> 
						<form method="post" action="<?php echo $wpl_form_action; ?>">
								<?php wp_nonce_field( 'wpla_tools_page' ); ?>
								<input type="hidden" name="action" value="check_wc_fba_stock" />
								<input type="hidden" name="mode" value="in_stock_only" />
								<input type="submit" value="<?php echo __('Check FBA stock levels','wpla'); ?>" name="submit" class="button">
								<p><?php echo __('Check all FBA items (with stock) and compare their stock levels to WooCommerce.','wpla'); ?>
								</p>
						</form>
						<br style="clear:both;"/>

						<!-- check FBA stock levels (all) --> 
						<form method="post" action="<?php echo $wpl_form_action; ?>">
								<?php wp_nonce_field( 'wpla_tools_page' ); ?>
								<input type="hidden" name="action" value="check_wc_fba_stock" />
								<input type="hidden" name="mode" value="all_stock" />
								<input type="submit" value="<?php echo __('Check all FBA stock levels','wpla'); ?>" name="submit" class="button">
								<p><?php echo __('Check all FBA items (online, with and without stock) and compare their stock levels to WooCommerce.','wpla'); ?>
								</p>
						</form>
						<br style="clear:both;"/>

						<!-- fix parent variation stock status --> 
						<form method="post" action="<?php echo $wpl_form_action; ?>">
								<?php wp_nonce_field( 'wpla_tools_page' ); ?>
								<input type="hidden" name="action" value="wpla_fix_variable_stock_status" />
								<input type="submit" value="<?php echo __('Fix variable stock status','wpla'); ?>" name="submit" class="button">
								<p><?php echo __('This will fix any variable products which show up as out of stock while in fact there is stock for some or all variations.','wpla'); ?>
								</p>
						</form>
						<br style="clear:both;"/>

						<!-- check FBA stock levels (all) --> 
						<form method="post" action="<?php echo $wpl_form_action; ?>">
								<?php wp_nonce_field( 'wpla_tools_page' ); ?>
								<input type="hidden" name="action" value="wpla_check_for_missing_products" />
								<input type="submit" value="<?php echo __('Find missing products','wpla'); ?>" name="submit" class="button">
								<p><?php echo __('Find items which are linked to a product which does not exist in WooCommerce.','wpla'); ?>
								</p>
						</form>
						<br style="clear:both;"/>

					</div>
				</div> <!-- postbox -->


			</div>
		</div>
	</div>

	<br style="clear:both;"/>

</div>



<script type="text/javascript">
	
	// on page load
	jQuery( document ).ready( function () {
	
		// autosubmit next inventory check step
		var autosubmit_url = jQuery("#wpla_auto_next_step").attr('href')
		if ( autosubmit_url != undefined ) {
			window.location.href = autosubmit_url;
		}

	});

</script>
