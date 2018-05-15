<?php include_once( dirname(__FILE__).'/common_header.php' ); ?>

<style type="text/css">

	/* sideboxes */
	#side-sortables .postbox input.text_input,
	#side-sortables .postbox select.select {
	    width: 50%;
	}
	#side-sortables .postbox label.text_label {
	    width: 45%;
	}

	.postbox h3 {
	    cursor: default;
	}
		
	/* backwards compatibility to WP 3.3 */
	#poststuff #post-body.columns-2 {
	    margin-right: 300px;
	}
	#poststuff #post-body {
	    padding: 0;
	}
	#post-body.columns-2 #postbox-container-1 {
	    float: right;
	    margin-right: -300px;
	    width: 280px;
	}
	#poststuff .postbox-container {
	    width: 100%;
	}
	#major-publishing-actions {
	    border-top: 1px solid #F5F5F5;
	    clear: both;
	    margin-top: -2px;
	    padding: 10px 10px 8px;
	}
	#post-body .misc-pub-section {
	    max-width: 100%;
	    border-right: none;
	}
</style>

<?php
	$item_details = maybe_unserialize( $wpl_item['details'] );
	// echo "<pre>";print_r($wpl_item);echo"</pre>";#die();
	// echo "<pre>";print_r($item_details);echo"</pre>";#die();
?>

<div class="wrap amazon-page">
	<div class="icon32" style="background: url(<?php echo $wpl_plugin_url; ?>img/hammer-32x32.png) no-repeat;" id="wpl-icon"><br /></div>
	<h2><?php echo __('Edit Listing','wpla') ?></h2>
	
	<?php echo $wpl_message ?>

	<form method="post" action="<?php echo $wpl_form_action; ?>">

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
									<!-- optional revise item on save -->
									<?php if ( ( $wpl_item['status'] == 'online' ) || ( $wpl_item['status'] == 'online' ) ): ?>
										<p><?php _e('Your changes to this item will only be updated on Amazon when you set the status to changed.','wpla') ?></p>
										<input type="checkbox" name="wpla_mark_as_changed_on_save" value="yes" id="mark_as_changed_on_save" />
										<label for="mark_as_changed_on_save"><?php _e('mark as changed','wpla') ?></label>
									<?php elseif ( ( $wpl_item['status'] == 'ended' ) || ( $wpl_item['status'] == 'ended' ) ): ?>
										<p><?php _e('This item has been ended.','wpla') ?></p>
									<?php else: ?>
										<p><?php _e('Listing status','wpla') ?>: <?php echo $wpl_item['status']; ?></p>
									<?php endif; ?>
									</div>

									<div class="misc-pub-section">
									</div>

								</div>

								<div id="major-publishing-actions">
									<div id="publishing-action">
                                        <?php wp_nonce_field( 'wpla_save_listing' ); ?>
										<input type="hidden" name="action" value="wpla_save_listing" />
										<input type="hidden" name="wpla_listing_id" value="<?php echo $wpl_item['id']; ?>" />
										<input type="hidden" name="wpla_status" value="<?php echo $wpl_item['status']; ?>" > 
										<input type="submit" value="<?php echo __('Update','wpla'); ?>" id="publish" class="button-primary" name="save">
									</div>
									<div class="clear"></div>
								</div>

							</div>

						</div>
					</div>


					<div class="postbox" id="ProfilesBox">
						<h3 class="hndle"><span><?php echo __('Profile','wpla'); ?></span></h3>
						<div class="inside">
							<?php foreach ($wpl_feed_profiles as $profile) : ?>
								<?php
									// echo "<pre>";print_r($profile);echo"</pre>";die();
									// $profile->profile_name = $profile['profile_name'];
									// $profile->profile_id = $profile['profile_path'];
									$checked  = ( $wpl_item['profile_id'] == $profile->profile_id ) ? 'checked="checked"' : '';
								?>

								<input type="radio" value="<?php echo $profile->profile_id ?>" id="profile-<?php echo basename($profile->profile_id) ?>" name="wpla_profile_id" class="post-format" <?php echo $checked ?> > 
								<label for="profile-<?php echo basename($profile->profile_id) ?>"><?php echo $profile->profile_name ?></label><br>

							<?php endforeach; ?>							

							<?php $checked  = empty( $wpl_item['profile_id'] ) ? 'checked="checked"' : ''; ?>
							<input type="radio" value="" id="profile-none" name="wpla_profile_id" class="post-format" <?php echo $checked ?> > 
							<label for="profile-none"><i><?php echo __('no profile','wpla') ?></i></label><br>

							<p>
								<?php _e('Note: Only new products have a profile assigned.','wpla') ?>
								<?php _e('Matched and imported products need no profile.','wpla') ?>
							</p>
						</div>
					</div>

					<div class="postbox" id="HelpBox">
						<h3 class="hndle"><span><?php echo __('Information','wpla'); ?></span></h3>
						<div class="inside">
							<p>
								<?php _e('Editing a single listing is intended mainly for debugging purposes.','wpla') ?>
								<?php _e('It is not recommended as part of your general workflow.','wpla') ?>
							</p>
							<p>
								<?php _e('If you find yourself editing single listings on a regular basis, you should contact us and describe your requirements. We will then work out a solution which benefts all users.','wpla') ?>
							</p>
						</div>
					</div>


				</div>
			</div> <!-- #postbox-container-2 -->

			<div id="postbox-container-2" class="postbox-container">
				<div class="meta-box-sortables ui-sortable">
					

					<div class="postbox" id="GeneralSettingsBox">
						<h3 class="hndle"><span><?php echo __('Item settings','wpla'); ?></span></h3>
						<div class="inside">

							<div id="titlediv" style="margin-bottom:5px;">
								<div id="titlewrap">
									<label for="wpl-text-listing_title" class="text_label"><?php echo __('Title','wpla'); ?></label>
									<input type="text" name="wpla_listing_title" size="30" value="<?php echo $wpl_item['listing_title']; ?>" id="title" autocomplete="off" style="width:65%;">
								</div>
							</div>

							<label for="wpl-text-price" class="text_label"><?php echo __('Price / Start price','wpla'); ?></label>
							<input type="text" name="wpla_price" id="wpl-text-price" value="<?php echo $wpl_item['price']; ?>" class="text_input" />
							<!-- <p class="desc" style="display: block;"><?php echo __('This will have no effect on product variations.','wpla'); ?></p> -->

							<label for="wpl-text-quantity" class="text_label"><?php echo __('Quantity','wpla'); ?></label>
							<input type="text" name="wpla_quantity" id="wpl-text-quantity" value="<?php echo $wpl_item['quantity']; ?>" class="text_input" />
							<!-- <p class="desc" style="display: block;"><?php echo __('This will have no effect on product variations.','wpla'); ?></p> -->

							<label for="wpl-text-quantity_sold" class="text_label"><?php echo __('Quantity sold','wpla'); ?></label>
							<input type="text" name="wpla_quantity_sold" size="30" value="<?php echo $wpl_item['quantity_sold']; ?>" class="text_input" />
							<br class="clear" />

						</div>
					</div>



					<div class="postbox" id="DeveloperToolBox" style="display:x-none;">
						<h3 class="hndle"><span><?php echo __('Developer options','wpla'); ?></span></h3>
						<div class="inside">
							<p>
								<?php echo __('You should not normally need to modify the following settings. Use at your own risk!','wpla') ?>
							</p>

							<label for="wpl-text-sku" class="text_label"><?php echo __('SKU','wpla'); ?></label>
							<input type="text" name="wpla_sku" size="30" value="<?php echo $wpl_item['sku']; ?>" class="text_input" />
							<br class="clear" />

							<label for="wpl-text-asin" class="text_label"><?php echo __('ASIN','wpla'); ?></label>
							<input type="text" name="wpla_asin" size="30" value="<?php echo $wpl_item['asin']; ?>" class="text_input" />
							<br class="clear" />

							<label for="wpl-text-fba_quantity" class="text_label"><?php echo __('FBA Quantity','wpla'); ?></label>
							<input type="text" name="wpla_fba_quantity" size="30" value="<?php echo $wpl_item['fba_quantity']; ?>" class="text_input" />
							<br class="clear" />

							<label for="wpl-text-fba_fcid" class="text_label"><?php echo __('Fulfillment Center ID','wpla'); ?></label>
							<select id="wpl-text-fba_fcid" name="wpla_fba_fcid" title="Laufzeit" class=" required-entry select">
								<option value="" <?php if ( $wpl_item['fba_fcid'] == '' ): ?>selected="selected"<?php endif; ?>><?php echo 'DEFAULT' ?></option>
								<option value="AMAZON_NA" <?php if ( $wpl_item['fba_fcid'] == 'AMAZON_NA' ): ?>selected="selected"<?php endif; ?>><?php echo 'AMAZON_NA' ?></option>
								<option value="AMAZON_EU" <?php if ( $wpl_item['fba_fcid'] == 'AMAZON_EU' ): ?>selected="selected"<?php endif; ?>><?php echo 'AMAZON_EU' ?></option>
								<option value="AMAZON_IN" <?php if ( $wpl_item['fba_fcid'] == 'AMAZON_IN' ): ?>selected="selected"<?php endif; ?>><?php echo 'AMAZON_IN' ?></option>
							</select>
							<br class="clear" />

							<label for="wpl-text-listing_status" class="text_label"><?php echo __('Listing status','wpla'); ?></label>
							<select id="wpl-text-listing_status" name="wpla_listing_status" title="Laufzeit" class=" required-entry select">
								<option value="matched" <?php if ( $wpl_item['status'] == 'matched' ): ?>selected="selected"<?php endif; ?>><?php echo __('matched','wpla'); ?></option>
								<option value="online" <?php if ( $wpl_item['status'] == 'online' ): ?>selected="selected"<?php endif; ?>><?php echo __('online','wpla'); ?></option>
								<option value="sold" <?php if ( $wpl_item['status'] == 'sold' ): ?>selected="selected"<?php endif; ?>><?php echo __('sold','wpla'); ?></option>
								<option value="ended" <?php if ( $wpl_item['status'] == 'ended' ): ?>selected="selected"<?php endif; ?>><?php echo __('ended','wpla'); ?></option>
								<option value="changed" <?php if ( $wpl_item['status'] == 'changed' ): ?>selected="selected"<?php endif; ?>><?php echo __('changed','wpla'); ?></option>
								<option value="prepared" <?php if ( $wpl_item['status'] == 'prepared' ): ?>selected="selected"<?php endif; ?>><?php echo __('prepared','wpla'); ?></option>
								<option value="submitted" <?php if ( $wpl_item['status'] == 'submitted' ): ?>selected="selected"<?php endif; ?>><?php echo __('submitted','wpla'); ?></option>
								<option value="imported" <?php if ( $wpl_item['status'] == 'imported' ): ?>selected="selected"<?php endif; ?>><?php echo __('imported','wpla'); ?></option>
								<option value="failed" <?php if ( $wpl_item['status'] == 'failed' ): ?>selected="selected"<?php endif; ?>><?php echo __('failed','wpla'); ?></option>
								<option value="trash" <?php if ( $wpl_item['status'] == 'trash' ): ?>selected="selected"<?php endif; ?>><?php echo __('trash','wpla'); ?></option>
								<option value="trashed" <?php if ( $wpl_item['status'] == 'trashed' ): ?>selected="selected"<?php endif; ?>><?php echo __('trashed','wpla'); ?></option>
							</select>
							<br class="clear" />

							<label for="wpl-text-pnq_status" class="text_label"><?php echo __('Price & Quantity status','wpla'); ?></label>
							<select id="wpl-text-pnq_status" name="wpla_pnq_status" title="Laufzeit" class=" required-entry select">
								<option value="0"  <?php if ( $wpl_item['pnq_status'] == '0'  ): ?>selected="selected"<?php endif; ?>><?php echo 'default (0)' ?></option>
								<option value="1"  <?php if ( $wpl_item['pnq_status'] == '1'  ): ?>selected="selected"<?php endif; ?>><?php echo 'changed (1)' ?></option>
								<option value="2"  <?php if ( $wpl_item['pnq_status'] == '2'  ): ?>selected="selected"<?php endif; ?>><?php echo 'submitted (2)' ?></option>
								<option value="-1" <?php if ( $wpl_item['pnq_status'] == '-1' ): ?>selected="selected"<?php endif; ?>><?php echo 'failed (-1)' ?></option>
							</select>
							<br class="clear" />

							<label for="wpl-text-post_id" class="text_label"><?php echo __('Product ID','wpla'); ?></label>
							<input type="text" name="wpla_post_id" size="30" value="<?php echo $wpl_item['post_id']; ?>" class="text_input" />
							<br class="clear" />

							<label for="wpl-text-source" class="text_label"><?php echo __('Source','wpla'); ?></label>
							<input type="text" name="wpla_source" size="30" value="<?php echo $wpl_item['source']; ?>" class="text_input" />
							<br class="clear" />

							<label for="wpl-enable_dev_mode" class="text_label"><?php echo __('Confirm updating dev options','wpla'); ?></label>
							<input type="checkbox" name="wpla_enable_dev_mode" id="wpl-enable_dev_mode" value="1" class="checkbox_input" />
							<span style="line-height: 24px">
								<?php echo __('Yes, I know what I am doing.','wpla'); ?>
							</span>
							<br class="clear" />


						</div>
					</div>


				    <!-- <h2>Debug Data</h2> -->
				    <?php 
				    	$wpl_item['pricing_info'] = maybe_unserialize( $wpl_item['pricing_info'] ); 
				    	$wpl_item['buybox_data']  = maybe_unserialize( $wpl_item['buybox_data']  ); 
				    	$wpl_item['loffer_data']  = maybe_unserialize( $wpl_item['loffer_data']  ); 
				    ?>
				    <a href="#" onclick="jQuery('.wplister_listing_details_debug').slideToggle();return false;" class="button"><?php _e('Show Debug Info','wpla') ?></a>
    				<pre class="wplister_listing_details_debug" style="display:none"><?php print_r( $wpl_item ) ?></pre>
    				<pre class="wplister_listing_details_debug" style="display:none"><?php print_r( $item_details ) ?></pre>
           


						
				</div> <!-- .meta-box-sortables -->
			</div> <!-- #postbox-container-2 -->


		</div> <!-- #post-body -->
		<br class="clear">
	</div> <!-- #poststuff -->

	</form>


	<?php if ( isset($_GET['debug']) || ( get_option('wpla_log_level') > 6 ) ): ?>
		<pre><?php #print_r($wpl_int_shipping_options); ?></pre>
		<pre><?php print_r(maybe_unserialize( $wpl_item['details'] ) ); ?></pre>
		<pre><?php #print_r($wpl_item); ?></pre>
		<pre><?php 
			$details = maybe_unserialize( $wpl_item['details'] ); 
			if ( is_array( $details->Variations->Variation ) )
			foreach ($details->Variations->Variation as $var) {
				// echo "<pre>";print_r($var);echo"</pre>";#die(); 	
				echo $var->SKU . ' - ' . $var->Quantity . '<br>';
			} 
		?></pre>
	<?php endif; ?>


	<script type="text/javascript">
		jQuery( document ).ready(
			function () {
		

				// check required values on submit
				jQuery('.amazon-page form').on('submit', function() {
					
					// duration is required
					// if ( jQuery('#wpl-text-listing_duration')[0].value == '' ) {
					// 	alert('Please select a listing duration.'); return false;
					// }

					// dispatch time is required
					// if ( jQuery('#wpl-text-dispatch_time')[0].value == '' ) {
					// 	alert('Please enter a handling time.'); return false;
					// }

					// first category is required
					// if ( jQuery('#wpl-text-ebay_category_1_id')[0].value == '' ) {
					// 	alert('Please select a main category.'); return false;
					// }

					return true;
				})


			}
		);
	
	</script>

</div>