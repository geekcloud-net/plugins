<style type="text/css">

	.amazon-page #poststuff #side-sortables .postbox input.text_input,
	.amazon-page #poststuff #side-sortables .postbox select.select {
	    width: 35%;
	}
	.amazon-page #poststuff #side-sortables .postbox label.text_label {
	    width: 60%;
	}

	.amazon-page #poststuff #side-sortables .postbox .inside p.desc {
		margin-left: 2%;
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




					<!-- first sidebox -->
					<div class="postbox" id="submitdiv">
						<!--<div title="Click to toggle" class="handlediv"><br></div>-->
						<h3 class="hndle"><span><?php echo __('Update','wpla'); ?></span></h3>
						<div class="inside">

							<div id="submitpost" class="submitbox">

								<div id="misc-publishing-actions">
									<div class="misc-pub-section">
									<!-- optional save and apply to all prepared listings already using this profile -->
									<?php //if ( count($wpl_prepared_listings) > -1 ): ?>
									<?php if ( false ): ?>
										<p><?php printf( __('There are %s prepared, %s verified and %s published items using this profile.','wpla'), count($wpl_prepared_listings), count($wpl_verified_listings), count($wpl_published_listings) ) ?></p>

										<input type="checkbox" name="wpla_apply_changes_to_all_prepared" value="yes" id="apply_changes_to_all_prepared" <?php if ($wpl_prepared_listings) echo 'checked' ?>/>
										<label for="apply_changes_to_all_prepared"><?php printf( __('update %s prepared items','wpla'), count($wpl_prepared_listings) ) ?></label>
										<br class="clear" />

										<input type="checkbox" name="wpla_apply_changes_to_all_verified" value="yes" id="apply_changes_to_all_verified" <?php if ($wpl_verified_listings) echo 'checked' ?>/>
										<label for="apply_changes_to_all_verified"><?php printf( __('update %s verified items','wpla'), count($wpl_verified_listings) ) ?></label>
										<br class="clear" />

										<input type="checkbox" name="wpla_apply_changes_to_all_published" value="yes" id="apply_changes_to_all_published" <?php if ($wpl_published_listings) echo 'checked' ?>/>
										<label for="apply_changes_to_all_published"><?php printf( __('update %s published items','wpla'), count($wpl_published_listings) ) ?></label>
										<br class="clear" />

										<input type="checkbox" name="wpla_apply_changes_to_all_ended" value="yes" id="apply_changes_to_all_ended" <?php #if ($wpl_ended_listings) echo 'checked' ?>/>
										<label for="apply_changes_to_all_ended"><?php printf( __('update %s ended items','wpla'), count($wpl_ended_listings) ) ?></label>
										<br class="clear" />

									<?php elseif ( count($wpl_profile_listings) > -1 ): ?>
										<p><?php echo sprintf( __('There are %s items using this profile.','wpla'), count($wpl_profile_listings) ); ?></p>
									<?php else: ?>
										<p><?php echo __('There are no items using this profile.','wpla'); ?></p>
									<?php endif; ?>
									</div>
								</div>

								<div id="major-publishing-actions">
									<div id="publishing-action">
                                        <?php wp_nonce_field( 'wpla_save_profile' ); ?>
										<input type="hidden" name="action" value="wpla_save_profile" />
										<input type="hidden" name="wpla_profile_id" value="<?php echo $wpl_profile->id ?>" />
										<input type="hidden" name="return_to" value="<?php echo isset($_GET['return_to']) ? $_GET['return_to'] : '' ?>" />
										<input type="hidden" name="listing_status" value="<?php echo isset($_GET['listing_status']) ? $_GET['listing_status'] : '' ?>" />
										<input type="hidden" name="profile_id" value="<?php echo isset($_GET['profile_id']) ? $_GET['profile_id'] : '' ?>" />
										<input type="hidden" name="account_id" value="<?php echo isset($_GET['account_id']) ? $_GET['account_id'] : '' ?>" />
										<input type="hidden" name="s" value="<?php echo isset($_GET['s']) ? $_GET['s'] : '' ?>" />
										<input type="submit" value="<?php echo __('Save profile','wpla'); ?>" id="publish" class="button-primary" name="save">
									</div>
									<div class="clear"></div>
								</div>

							</div>

						</div>
					</div>


					<div class="postbox" id="AccountsBox">
						<h3 class="hndle"><span><?php echo __('Account','wpla'); ?></span></h3>
						<div class="inside">
							<?php foreach ($wpl_accounts as $account) : ?>
								<?php
									$checked  = ( $wpl_profile->account_id == $account->id ) ? 'checked="checked"' : '';
								?>

								<input type="radio" value="<?php echo $account->id ?>" id="account-<?php echo $account->id ?>" name="wpla_account_id" class="post-format" <?php echo $checked ?> > 
								<label for="account-<?php echo $account->id ?>"><?php echo $account->title ?></label><br>

							<?php endforeach; ?>
						</div>
					</div>



					<div class="postbox" id="PricesBox">
						<h3 class="hndle"><span><?php echo __('Pricing Options','wpla'); ?></span></h3>
						<div class="inside">

							<h4><?php echo __('Increase product price','wpla'); ?></h4>

							<label for="wpl-text-price_add_percentage" class="text_label">
								<?php echo __('by percentage','wpla'); ?>
                                <?php wpla_tooltip('Example: Enter "10" to increase the price on Amazon by 10 percent.') ?>
							</label>
							<input type="text" name="wpla_price_add_percentage" id="wpl-text-price_add_percentage" value="<?php echo isset($wpl_profile_details['price_add_percentage']) ? $wpl_profile_details['price_add_percentage'] : '' ?>" class="text_input" />
							<br class="clear" />

							<label for="wpl-text-price_add_amount" class="text_label">
								<?php echo __('by amount','wpla'); ?>
                                <?php wpla_tooltip('Example: Enter "10" to increase the price on Amazon by $10.') ?>
							</label>
							<input type="text" name="wpla_price_add_amount" id="wpl-text-price_add_amount" value="<?php echo isset($wpl_profile_details['price_add_amount']) ? $wpl_profile_details['price_add_amount'] : '' ?>" class="text_input" />
							<!-- <br class="clear" /> -->

							<p>
								<small>
									<?php echo __('Note: Price adjustments only work on the original WooCommerce price.','wpla'); ?>
									<?php echo __('It will be ignored if you enter a custom Amazon price for a product.','wpla'); ?>
								</small>
							</p>

						</div>
					</div>

					<div class="postbox" id="VariationOptionsBox">
						<h3 class="hndle"><span><?php echo __('Variations','wpla'); ?></span></h3>
						<div class="inside">

							<label for="wpl-variations_mode" class="text_label">
								<?php echo __('Enable variations','wpla'); ?>
                                <?php wpla_tooltip('If you disable variations, WP-Lister will omit the variation relationship data in the feed, which will result in each variation showing up as a separate product on Amazon.<br>Use this with care!<br>The default is "Yes".') ?>
							</label>
							<select id="wpl-variations_mode" name="wpla_variations_mode" title="Type" class=" required-entry select">
								<option value="default" <?php if ( isset($wpl_profile_details['variations_mode']) && $wpl_profile_details['variations_mode'] == 'default' ) echo 'selected' ?> ><?php echo __('Yes','wpla'); ?></option>
								<option value="flat"    <?php if ( isset($wpl_profile_details['variations_mode']) && $wpl_profile_details['variations_mode'] == 'flat'    ) echo 'selected' ?> ><?php echo __('No','wpla'); ?></option>
							</select>

							<p>
								<small>
									<?php echo __('You should only disable variations if your feed template does not support variations at all.','wpla'); ?>
								</small>
							</p>

						</div>
					</div>


