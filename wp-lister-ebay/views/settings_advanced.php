<?php include_once( dirname(__FILE__).'/common_header.php' ); ?>

<style type="text/css">
	
	#poststuff #side-sortables .postbox input.text_input,
	#poststuff #side-sortables .postbox select.select {
	    width: 50%;
	}
	#poststuff #side-sortables .postbox label.text_label {
	    width: 45%;
	}
	#poststuff #side-sortables .postbox p.desc {
	    margin-left: 5px;
	}

</style>

<div class="wrap wplister-page">
	<div class="icon32" style="background: url(<?php echo $wpl_plugin_url; ?>img/hammer-32x32.png) no-repeat;" id="wpl-icon"><br /></div>
          
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
						<h3 class="hndle"><span><?php echo __('Update','wplister'); ?></span></h3>
						<div class="inside">

							<div id="submitpost" class="submitbox">

								<div id="misc-publishing-actions">
									<div class="misc-pub-section">
										<p><?php echo __('This page contains some advanced options for special use cases.','wplister') ?></p>
									</div>
								</div>

								<div id="major-publishing-actions">
									<div id="publishing-action">
                                        <?php wp_nonce_field( 'wplister_save_advanced_settings' ); ?>
										<input type="hidden" name="action" value="save_wplister_advanced_settings" >
										<input type="submit" value="<?php echo __('Save Settings','wplister'); ?>" id="save_settings" class="button-primary" name="save">
									</div>
									<div class="clear"></div>
								</div>

							</div>

						</div>
					</div>

					<?php if ( ( ! is_multisite() ) || ( is_main_site() ) ) : ?>
					<div class="postbox" id="UninstallSettingsBox">
						<h3 class="hndle"><span><?php echo __('Uninstall on deactivation','wplister') ?></span></h3>
						<div class="inside">

							<label for="wpl-option-uninstall" class="text_label"><?php echo __('Uninstall','wplister'); ?></label>
							<select id="wpl-option-uninstall" name="wpl_e2e_option_uninstall" class="required-entry select">
								<option value="0" <?php if ( $wpl_option_uninstall != '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('No','wplister'); ?></option>
								<option value="1" <?php if ( $wpl_option_uninstall == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Yes','wplister'); ?></option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __('Enable to completely remove listings, transactions and settings when deactivating the plugin.','wplister'); ?><br><br>
								<?php echo __('To remove your listing templates as well, please delete the folder <code>wp-content/uploads/wp-lister/templates/</code>.','wplister'); ?>
								<!-- ## BEGIN PRO ## -->
								<br><br>
								<?php echo __('Please deactivate your license first.','wplister'); ?>
								<!-- ## END PRO ## -->
							</p>

						</div>
					</div>
					<?php endif; ?>

					<?php #include('profile/edit_sidebar.php') ?>
				</div>
			</div> <!-- #postbox-container-1 -->

			<!-- #postbox-container-3 -->
			<?php if ( ( ! is_multisite() || is_main_site() ) && apply_filters( 'wpl_enable_capabilities_options', true ) ) : ?>
			<div id="postbox-container-3" class="postbox-container">
				<div class="meta-box-sortables ui-sortable">
					
					<div class="postbox" id="PermissionsSettingsBox">
						<h3 class="hndle"><span><?php echo __('Roles and Capabilities','wplister') ?></span></h3>
						<div class="inside">

							<?php
								$wpl_caps = array(
									'manage_ebay_listings'  => __('Manage Listings','wplister'),
									'manage_ebay_options'   => __('Manage Settings','wplister'),
									'prepare_ebay_listings' => __('Prepare Listings','wplister'),
									'publish_ebay_listings' => __('Publish Listings','wplister'),
								);
							?>

							<table style="width:100%">
                            <?php foreach ($wpl_available_roles as $role => $role_name) : ?>
                            	<tr>
                            		<th style="text-align: left">
		                                <?php echo $role_name; ?>
		                            </th>

		                            <?php foreach ($wpl_caps as $cap => $cap_name ) : ?>
                            		<td>
		                                <input type="checkbox" 
		                                    	name="wpl_permissions[<?php echo $role ?>][<?php echo $cap ?>]" 
		                                       	id="wpl_permissions_<?php echo $role.'_'.$cap ?>" class="checkbox_cap" 
		                                       	<?php if ( isset( $wpl_wp_roles[ $role ]['capabilities'][ $cap ] ) ) : ?>
		                                       		checked
		                                   		<?php endif; ?>
		                                       	/>
		                                       	<label for="wpl_permissions_<?php echo $role.'_'.$cap ?>">
				                               		<?php echo $cap_name; ?>
				                               	</label>
			                            </td>
		                            <?php endforeach; ?>

		                        </tr>
                            <?php endforeach; ?>
                        	</table>


						</div>
					</div>

				</div>
			</div> <!-- #postbox-container-1 -->
			<?php endif; ?>


			<!-- #postbox-container-2 -->
			<div id="postbox-container-2" class="postbox-container">
				<div class="meta-box-sortables ui-sortable">
					
					<?php do_action( 'wple_before_advanced_settings' ) ?>

					<div class="postbox" id="TemplateSettingsBox">
						<h3 class="hndle"><span><?php echo __('Listing Templates','wplister') ?></span></h3>
						<div class="inside">

							<label for="wpl-process_shortcodes" class="text_label">
								<?php echo __('Shortcode processing','wplister'); ?>
                                <?php wplister_tooltip('By default, WP-Lister runs your product description through the usual WordPress content filters which enabled you to use shortcodes in your product descriptions.<br>If a plugin causes trouble by adding unwanted HTML to your description on eBay, you should try setting this to "off".') ?>
							</label>
							<select id="wpl-process_shortcodes" name="wpl_e2e_process_shortcodes" class="required-entry select">
								<option value="off"     <?php if ( $wpl_process_shortcodes == 'off' ): ?>selected="selected"<?php endif; ?>><?php echo __('off','wplister'); ?></option>
								<option value="content" <?php if ( $wpl_process_shortcodes == 'content' ): ?>selected="selected"<?php endif; ?>><?php echo __('only in product description','wplister'); ?></option>
								<option value="full"    <?php if ( $wpl_process_shortcodes == 'full' ): ?>selected="selected"<?php endif; ?>><?php echo __('in description and listing template','wplister'); ?></option>
								<option value="remove"  <?php if ( $wpl_process_shortcodes == 'remove' ): ?>selected="selected"<?php endif; ?>><?php echo __('remove all shortcodes from description','wplister'); ?></option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __('Enable this if you want to use WordPress shortcodes in your product description or your listing template.','wplister'); ?><br>
							</p>

							<label for="wpl-remove_links" class="text_label">
								<?php echo __('Link handling','wplister'); ?>
                                <?php wplister_tooltip('Should WP-Lister replace links within the product description with plain text?') ?>
							</label>
							<select id="wpl-remove_links" name="wpl_e2e_remove_links" class="required-entry select">
								<option value="default"         <?php selected( 'default', $wpl_remove_links ); ?>><?php echo __('remove all links from description','wplister'); ?></option>
								<option value="remove_external" <?php selected( 'remove_external', $wpl_remove_links ); ?>><?php echo __('remove all non-eBay links from description','wplister'); ?></option>
								<option value="allow_all"       <?php selected( 'allow_all', $wpl_remove_links ); ?>><?php echo __('allow all links','wplister'); ?></option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __('Links are removed from product descriptions by default to avoid violating the eBay Links policy.','wplister'); ?>
								<?php echo __('Specifically you are not allowed to advertise products that you list on eBay by linking to their product pages on your site.','wplister'); ?>
								
								<?php echo __('Read more about eBay\'s Link policy','wplister'); ?>
								<a href="<?php echo __('http://pages.ebay.com/help/policies/listing-links.html','wplister'); ?>" target="_blank"><?php echo __('here','wplister'); ?></a>
							</p>

							<label for="wpl-template_ssl_mode" class="text_label">
								<?php echo __('HTTPS conversion','wplister'); ?>
                                <?php wplister_tooltip('Enable this to make sure all image links in your listing template use HTTPS.<br>If your site supports SSL, it is recommended to set this option to "Use HTTPS".') ?>
							</label>
							<select id="wpl-template_ssl_mode" name="wpl_e2e_template_ssl_mode" class="required-entry select">
								<option value=""           <?php if ( $wpl_template_ssl_mode == ''          ): ?>selected="selected"<?php endif; ?>><?php echo __('Off','wplister'); ?> (<?php _e('default','wplister'); ?>)</option>
								<option value="https"      <?php if ( $wpl_template_ssl_mode == 'https'     ): ?>selected="selected"<?php endif; ?>><?php echo __('Use HTTPS','wplister'); ?> (<?php _e('recommended','wplister'); ?>)</option>
								<option value="enforce"    <?php if ( $wpl_template_ssl_mode == 'enforce'   ): ?>selected="selected"<?php endif; ?>><?php echo __('Convert all HTTP content to HTTPS','wplister'); ?></option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __('Enable this if your site supports HTTPS.','wplister'); ?>
							</p>

							<label for="wpl-wc2_gallery_fallback" class="text_label">
								<?php echo __('Product Gallery','wplister'); ?>
                                <?php wplister_tooltip('In order to find additional product images, WP-Lister first checks if there is a dedicated <i>Product Gallery</i> (WC 2.0+).<br>
                                						If there\'s none, it can use all images which were uploaded (attached) to the product - as it was the default behaviour in WooCommerce 1.x.') ?>
							</label>
							<select id="wpl-wc2_gallery_fallback" name="wpl_e2e_wc2_gallery_fallback" class="required-entry select">
								<option value="attached" <?php if ( $wpl_wc2_gallery_fallback == 'attached' ): ?>selected="selected"<?php endif; ?>><?php echo __('use attached images if no Gallery found','wplister'); ?></option>
								<option value="none"     <?php if ( $wpl_wc2_gallery_fallback == 'none'     ): ?>selected="selected"<?php endif; ?>><?php echo __('use Product Gallery images','wplister'); ?> (<?php _e('default','wplister'); ?>)</option>
							</select>
							<?php if ( $wpl_wc2_gallery_fallback == 'attached' ): ?>
							<p class="desc" style="display: block;">
								<?php echo __('If you find unwanted images in your listings try disabling this option.','wplister'); ?>
							</p>
							<?php else : ?>
							<p class="desc" style="display: block;">
								<?php echo __('It is recommended to keep the default setting.','wplister'); ?><br>
							</p>
							<?php endif; ?>

							<label for="wpl-default_image_size" class="text_label">
								<?php echo __('Default image size','wplister'); ?>
                                <?php wplister_tooltip('Select the image size WP-Lister should use on eBay. It is recommended to set this to "full size".') ?>
							</label>
							<select id="wpl-default_image_size" name="wpl_e2e_default_image_size" class="required-entry select">
								<option value="full"    <?php if ( $wpl_default_image_size == 'full'   ): ?>selected="selected"<?php endif; ?>><?php echo __('full size','wplister'); ?> (<?php _e('default','wplister'); ?>)</option>
								<option value="large"   <?php if ( $wpl_default_image_size == 'large'  ): ?>selected="selected"<?php endif; ?>><?php echo __('large size','wplister'); ?></option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __('It is recommended to keep the default setting.','wplister'); ?><br>
							</p>

							<label for="wpl-gallery_items_limit" class="text_label">
								<?php echo __('Gallery Widget limit','wplister'); ?>
                                <?php wplister_tooltip('Limit the number of items displayed by the gallery widgets in your listing template - like <i>recent additions</i> or <i>ending soon</i>. The default is 12 items.') ?>
							</label>
							<select id="wpl-gallery_items_limit" name="wpl_e2e_gallery_items_limit" class="required-entry select">
								<option value="3" <?php if ( $wpl_gallery_items_limit == '3' ): ?>selected="selected"<?php endif; ?>>3 <?php echo __('items','wplister'); ?></option>
								<option value="6" <?php if ( $wpl_gallery_items_limit == '6' ): ?>selected="selected"<?php endif; ?>>6 <?php echo __('items','wplister'); ?></option>
								<option value="9" <?php if ( $wpl_gallery_items_limit == '9' ): ?>selected="selected"<?php endif; ?>>9 <?php echo __('items','wplister'); ?></option>
								<option value="12" <?php if ( $wpl_gallery_items_limit == '12' ): ?>selected="selected"<?php endif; ?>>12 <?php echo __('items','wplister'); ?> (<?php _e('default','wplister'); ?>)</option>
								<option value="15" <?php if ( $wpl_gallery_items_limit == '15' ): ?>selected="selected"<?php endif; ?>>15 <?php echo __('items','wplister'); ?></option>
								<option value="24" <?php if ( $wpl_gallery_items_limit == '24' ): ?>selected="selected"<?php endif; ?>>24 <?php echo __('items','wplister'); ?></option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __('The maximum number of items shown by listings template gallery widgets.','wplister'); ?>
							</p>

						</div>
					</div>

					<div class="postbox" id="UISettingsBox">
						<h3 class="hndle"><span><?php echo __('User Interface','wplister') ?></span></h3>
						<div class="inside">

							<?php if ( ! defined('WPLISTER_RESELLER_VERSION') ) : ?>
							<label for="wpl-text-admin_menu_label" class="text_label">
								<?php echo __('Menu label','wplister') ?>
                                <?php wplister_tooltip('You can change the main admin menu label in your dashboard from WP-Lister to anything you like.') ?>
							</label>
							<input type="text" name="wpl_e2e_text_admin_menu_label" id="wpl-text-admin_menu_label" value="<?php echo $wpl_text_admin_menu_label; ?>" class="text_input" />
							<p class="desc" style="display: block;">
								<?php echo __('Customize the main admin menu label of WP-Lister.','wplister'); ?><br>
							</p>
							<?php endif; ?>

							<label for="wpl-option-preview_in_new_tab" class="text_label">
								<?php echo __('Open preview in new tab','wplister') ?>
                                <?php wplister_tooltip('WP-Lister uses a Thickbox modal window to display the preview by default. However, this can cause issues in rare cases where you embed some JavaScript code (like NivoSlider) - or you might just want more screen estate to preview your listings.') ?>
							</label>
							<select id="wpl-option-preview_in_new_tab" name="wpl_e2e_option_preview_in_new_tab" class="required-entry select">
								<option value="0" <?php if ( $wpl_option_preview_in_new_tab != '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('No','wplister'); ?> (<?php _e('default','wplister'); ?>)</option>
								<option value="1" <?php if ( $wpl_option_preview_in_new_tab == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Yes','wplister'); ?></option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __('Select if you want the listing preview open in a new tab by default.','wplister'); ?><br>
							</p>

							<label for="wpl-option-enable_thumbs_column" class="text_label">
								<?php echo __('Listing thumbnails','wplister') ?>
                                <?php wplister_tooltip('Enable this to show product thumbnails on the listings page. Disabled by default to save screen estate.') ?>
							</label>
							<select id="wpl-option-enable_thumbs_column" name="wpl_e2e_enable_thumbs_column" class="required-entry select">
								<option value="0" <?php if ( $wpl_enable_thumbs_column != '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('No','wplister'); ?> (<?php _e('default','wplister'); ?>)</option>
								<option value="1" <?php if ( $wpl_enable_thumbs_column == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Yes','wplister'); ?></option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __('Show product images on listings page.','wplister'); ?><br>
							</p>

							<label for="wpl-enable_custom_product_prices" class="text_label">
								<?php echo __('Enable custom price field','wplister') ?>
                                <?php wplister_tooltip('If do not use custom prices in eBay and prefer less options when editing a product, you can disable the custom price fields here.') ?>
							</label>
							<select id="wpl-enable_custom_product_prices" name="wpl_e2e_enable_custom_product_prices" class=" required-entry select">
								<option value="0" <?php if ( $wpl_enable_custom_product_prices == '0' ): ?>selected="selected"<?php endif; ?>><?php echo __('No','wplister'); ?></option>
								<option value="1" <?php if ( $wpl_enable_custom_product_prices == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Yes','wplister'); ?> (<?php _e('default','wplister'); ?>)</option>
								<option value="2" <?php if ( $wpl_enable_custom_product_prices == '2' ): ?>selected="selected"<?php endif; ?>><?php echo __('Hide for variations','wplister'); ?></option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __('Show or hide the custom eBay price field.','wplister'); ?><br>
							</p>

							<label for="wpl-enable_mpn_and_isbn_fields" class="text_label">
								<?php echo __('Enable MPN and ISBN fields','wplister') ?>
                                <?php wplister_tooltip('If your variable products have MPNs or ISBNs, set this option to <i>Yes</i>.<br><br>If you need MPNs or ISBNs only on simple products, leave it at the default setting.<br><br>If you never use MPNs nor ISBNs, set it to <i>No</i>.') ?>
							</label>
							<select id="wpl-enable_mpn_and_isbn_fields" name="wpl_e2e_enable_mpn_and_isbn_fields" class=" required-entry select">
								<option value="0" <?php if ( $wpl_enable_mpn_and_isbn_fields == '0' ): ?>selected="selected"<?php endif; ?>><?php echo __('No','wplister'); ?></option>
								<option value="1" <?php if ( $wpl_enable_mpn_and_isbn_fields == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Yes','wplister'); ?></option>
								<option value="2" <?php if ( $wpl_enable_mpn_and_isbn_fields == '2' ): ?>selected="selected"<?php endif; ?>><?php echo __('Hide for variations','wplister'); ?> (<?php _e('default','wplister'); ?>)</option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __('Show or hide the MPN and ISBN fields.','wplister'); ?><br>
							</p>

							<label for="wpl-enable_categories_page" class="text_label">
								<?php echo __('Categories in main menu','wplister') ?>
                                <?php wplister_tooltip('This will add a <em>Categories</em> submenu entry visible to users who can manage listings.') ?>
							</label>
							<select id="wpl-enable_categories_page" name="wpl_e2e_enable_categories_page" class="required-entry select">
								<option value="0" <?php if ( $wpl_enable_categories_page != '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('No','wplister'); ?> (<?php _e('default','wplister'); ?>)</option>
								<option value="1" <?php if ( $wpl_enable_categories_page == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Yes','wplister'); ?></option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __('Enable this to make category settings available to users without access to other eBay settings.','wplister'); ?><br>
							</p>

                            <label for="wpl-store_categories_sorting" class="text_label">
                                <?php echo __('Store Categories Order','wplister') ?>
                                <?php wplister_tooltip('Choose whether to display the store categories using the manual order from eBay, or sort them alphabetically.') ?>
                            </label>
                            <select id="wpl-store_categories_sorting" name="wpl_e2e_store_categories_sorting" class="required-entry select">
                                <option value="default" <?php selected( $wpl_store_categories_sorting, 'default' ); ?>><?php echo __('Manual sort order','wplister'); ?> (<?php _e('default','wplister'); ?>)</option>
                                <option value="alphabetical" <?php selected( $wpl_store_categories_sorting, 'alphabetical' ); ?>><?php echo __('Sort alphabetically','wplister'); ?></option>
                            </select>
							<p class="desc" style="display: block;">
								<?php echo __('Select whether you want your store categories to be sorted alphabetically.','wplister'); ?><br>
							</p>

							<label for="wpl-enable_accounts_page" class="text_label">
								<?php echo __('Accounts in main menu','wplister') ?>
                                <?php wplister_tooltip('This will add a <em>Accounts</em> submenu entry visible to users who can manage listings.') ?>
							</label>
							<select id="wpl-enable_accounts_page" name="wpl_e2e_enable_accounts_page" class="required-entry select">
								<option value="0" <?php if ( $wpl_enable_accounts_page != '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('No','wplister'); ?> (<?php _e('default','wplister'); ?>)</option>
								<option value="1" <?php if ( $wpl_enable_accounts_page == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Yes','wplister'); ?></option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __('Enable this to make account settings available to users without access to other eBay settings.','wplister'); ?><br>
							</p>

							<label for="wpl-option-disable_wysiwyg_editor" class="text_label">
								<?php echo __('Disable WYSIWYG editor','wplister') ?>
                                <?php wplister_tooltip('Depending in your listing template content, you might want to disable the built in WP editor to edit your template content.') ?>
							</label>
							<select id="wpl-option-disable_wysiwyg_editor" name="wpl_e2e_option_disable_wysiwyg_editor" class="required-entry select">
								<option value="0" <?php if ( $wpl_option_disable_wysiwyg_editor != '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('No','wplister'); ?> (<?php _e('default','wplister'); ?>)</option>
								<option value="1" <?php if ( $wpl_option_disable_wysiwyg_editor == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Yes','wplister'); ?></option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __('Select the editor you want to use to edit listing templates.','wplister'); ?><br>
							</p>

							<label for="wpl-hide_dupe_msg" class="text_label">
								<?php echo __('Hide duplicates warning','wplister'); ?>
                                <?php wplister_tooltip('Technically, WP-Lister allows you to list the same product multiple times on eBay - in order to increase your visibility. However, this is not recommended as WP-Lister Pro would not be able to decrease the stock on eBay accordingly when the product is sold in WooCommerce.') ?>
							</label>
							<select id="wpl-hide_dupe_msg" name="wpl_e2e_hide_dupe_msg" class="required-entry select">
								<option value=""  <?php if ( $wpl_hide_dupe_msg == ''  ): ?>selected="selected"<?php endif; ?>><?php echo __('No','wplister'); ?> (<?php _e('recommended','wplister'); ?>)</option>
								<option value="1" <?php if ( $wpl_hide_dupe_msg == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Yes, I know what I am doing.','wplister'); ?></option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __('If you do not plan to use the synchronize sales feature, you can safely list one product multiple times.','wplister'); ?>
							</p>

                            <label for="wpl-option-display_product_counts" class="text_label">
                                <?php _e( 'Show eBay product totals', 'wplister' ); ?>
                                <?php wplister_tooltip('This will display the total number of products <i>On eBay</i> and <i>Not on eBay</i> on the Products admin page in WooCommerce.<br><br>Please note: Enabling this option requires some complex database queries which might slow down loading the Products admin page.<br><br>If the Products page is taking too long to load, you should disable this option or move to a more powerful hosting/server.'); ?>
                            </label>
                            <select id="wpl-option-display_product_counts" name="wpl_e2e_display_product_counts" class="required-entry select">
                                <option value="0" <?php selected( $wpl_display_product_counts, 0 ); ?>><?php _e('No', 'wplister'); ?> (default)</option>
                                <option value="1" <?php selected( $wpl_display_product_counts, 1 ); ?>><?php _e('Yes', 'wplister'); ?></option>
                            </select>
							<p class="desc" style="display: block;">
								<?php echo __('Enable this to display the total number of products on eBay / not on eBay in WooCommerce.','wplister'); ?>
							</p>

						</div>
					</div>

					<!-- ## BEGIN PRO ## -->
					<div class="postbox" id="OrderEmailSettingsBox">
						<h3 class="hndle"><span><?php echo __('Order Processing','wplister') ?></span></h3>
						<div class="inside">

							<label for="wpl-disable_new_order_emails" class="text_label">
								<?php echo __('Disable New Order emails','wplister'); ?>
                                <?php wplister_tooltip('Disable New Order notifications being sent to the admin when an eBay order is created.') ?>
							</label>
							<select id="wpl-disable_new_order_emails" name="wpl_e2e_disable_new_order_emails" class="required-entry select">
								<option value=""  <?php if ( $wpl_disable_new_order_emails == ''  ): ?>selected="selected"<?php endif; ?>><?php echo __('No','wplister'); ?></option>
								<option value="1" <?php if ( $wpl_disable_new_order_emails == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Yes','wplister'); ?> (<?php _e('recommended','wplister'); ?>)</option>
							</select>

							<label for="wpl-disable_processing_order_emails" class="text_label">
								<?php echo __('Disable Processing Order emails','wplister'); ?>
                                <?php wplister_tooltip('Disable email notifications being sent to the customer when an eBay order is created with status processing.') ?>
							</label>
							<select id="wpl-disable_processing_order_emails" name="wpl_e2e_disable_processing_order_emails" class="required-entry select">
								<option value=""  <?php if ( $wpl_disable_processing_order_emails == ''  ): ?>selected="selected"<?php endif; ?>><?php echo __('No','wplister'); ?></option>
								<option value="1" <?php if ( $wpl_disable_processing_order_emails == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Yes','wplister'); ?> (<?php _e('recommended','wplister'); ?>)</option>
							</select>

							<label for="wpl-disable_completed_order_emails" class="text_label">
								<?php echo __('Disable Completed Order emails','wplister'); ?>
                                <?php wplister_tooltip('Disable email notifications being sent to the customer when an eBay order is created with status completed.') ?>
							</label>
							<select id="wpl-disable_completed_order_emails" name="wpl_e2e_disable_completed_order_emails" class="required-entry select">
								<option value=""  <?php if ( $wpl_disable_completed_order_emails == ''  ): ?>selected="selected"<?php endif; ?>><?php echo __('No','wplister'); ?></option>
								<option value="1" <?php if ( $wpl_disable_completed_order_emails == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Yes','wplister'); ?> (<?php _e('recommended','wplister'); ?>)</option>
							</select>

							<label for="wpl-disable_changed_order_emails" class="text_label">
								<?php echo __('Disable emails on status change','wplister'); ?>
                                <?php wplister_tooltip('Disable email notifications being sent to the customer when the order status of an eBay order is changed manually.') ?>
							</label>
							<select id="wpl-disable_changed_order_emails" name="wpl_e2e_disable_changed_order_emails" class="required-entry select">
								<option value=""  <?php if ( $wpl_disable_changed_order_emails == ''  ): ?>selected="selected"<?php endif; ?>><?php echo __('No','wplister'); ?></option>
								<option value="1" <?php if ( $wpl_disable_changed_order_emails == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Yes','wplister'); ?> (<?php _e('recommended','wplister'); ?>)</option>
							</select>

							<p class="desc" style="display: block;">
								<?php echo __('WooCommerce sends out various notifications when an order status is changed.','wplister'); ?><br>
								<?php echo __('Here you can disable these notifications when creating eBay orders in WooCommerce.','wplister'); ?>
							</p>

                            <label for="wpl-use_ebay_order_number" class="text_label">
                                <?php echo __('Use eBay Order Number','wplister'); ?>
                                <?php wplister_tooltip('Enable this if you want WP-Lister to use the order number from eBay when creating new WC orders.') ?>
                            </label>
                            <select id="wpl-use_ebay_order_number" name="wpl_e2e_use_ebay_order_number" class="required-entry select">
                                <option value="0" <?php selected( $wpl_use_ebay_order_number, 0 ); ?>><?php echo __('No','wplister'); ?> (<?php _e('default','wplister'); ?>)</option>
                                <option value="1" <?php selected( $wpl_use_ebay_order_number, 1 ); ?>><?php echo __('Yes','wplister'); ?></option>
                            </select>
							<p class="desc" style="display: block;">
								<?php echo __('Use the original order number from eBay for new orders in WooCommerce.','wplister'); ?><br>
							</p>

							<label for="wpl-auto_complete_sales" class="text_label">
								<?php echo __('Complete sale on eBay automatically','wplister'); ?>
                                <?php wplister_tooltip('This completes an eBay order with the default feedback text and shipping date set to today when the order status is changed to completed.<br>Not applicable if default new order status is <em>Completed</em>.') ?>
							</label>
							<select id="wpl-auto_complete_sales" name="wpl_e2e_auto_complete_sales" class="required-entry select">
								<option value=""  <?php if ( $wpl_auto_complete_sales == ''  ): ?>selected="selected"<?php endif; ?>><?php echo __('No','wplister'); ?></option>
								<option value="1" <?php if ( $wpl_auto_complete_sales == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Yes','wplister'); ?> (<?php _e('recommended','wplister'); ?>)</option>
							</select>

							<p class="desc" style="display: block;">
								<?php echo __('Automatically complete the sale on eBay when an order is completed in WooCommerce.','wplister'); ?>
								<?php if ( $wpl_auto_complete_sales && get_option( 'wplister_new_order_status', 'processing' ) == 'completed' ) : ?>
									<br><b><?php echo 'This option will have no effect as long as the default status for new orders is set to Completed!' ?></b>
								<?php endif; ?>
							</p>

							<label for="wpl-default_feedback_text" class="text_label">
								<?php echo __('Default feedback text','wplister') ?>
                                <?php wplister_tooltip('Default feedback text to be used when auto complete option above is enabled. Leave empty to skip sending feedback.<br>Maximum length: 80 characters<br>Note: Seller feedback is always positive.') ?>
							</label>
							<input type="text" name="wpl_e2e_default_feedback_text" id="wpl-default_feedback_text" value="<?php echo $wpl_default_feedback_text; ?>" maxlength="80" class="text_input" />
							<p class="desc" style="display: block;">
								<?php echo __('This is what will be sent as your seller feedback when sales are completed automatically.','wplister'); ?><br>
							</p>

							<?php if ( 'order' == get_option( 'wplister_ebay_update_mode', 'order' ) ) : ?>

							<label for="wpl-option-create_incomplete_orders" class="text_label">
								<?php echo __('Create orders when','wplister') ?>
                                <?php wplister_tooltip('It is recommended to wait until the eBay purchase has been completed or combined orders can cause duplicates.<br>If you set this to <i>immediately</i>, WP-Lister will possibly create WooCommerce orders for cancelled eBay orders as well.') ?>
							</label>
							<select id="wpl-option-create_incomplete_orders" name="wpl_e2e_create_incomplete_orders" class=" required-entry select">
								<option value="0" <?php if ( $wpl_create_incomplete_orders != '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('When purchase has been completed','wplister'); ?> (<?php _e('recommended','wplister'); ?>)</option>
								<option value="1" <?php if ( $wpl_create_incomplete_orders == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Immediately','wplister'); ?></option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __('Orders can be created when they are downloaded or when they have been completed.','wplister'); ?><br>
								<?php if ( ( $wpl_create_incomplete_orders == '1' ) && ( get_option( 'woocommerce_hold_stock_minutes',false) ) ): ?>
									<span style="color:#C00">
										Warning: WooCommerce is set to cancel incomplete orders after <?php echo get_option( 'woocommerce_hold_stock_minutes') ?> minutes.
									</span>
								<?php endif; ?>
							</p>

							<?php else: ?>

							<label for="wpl-option-foreign_transactions" class="text_label">
								<?php echo __('Handle foreign transactions','wplister') ?>
                                <?php wplister_tooltip('WP-Lister is designed to process a sale on eBay only if it "knows" the sold item (ie. the listing was created by WP-Lister itself). Disable this on your own risk.') ?>
							</label>
							<select id="wpl-option-foreign_transactions" name="wpl_e2e_option_foreign_transactions" class="required-entry select">
								<option value="0" <?php if ( $wpl_option_foreign_transactions != '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Skip','wplister'); ?> (<?php _e('recommended','wplister'); ?>)</option>
								<option value="1" <?php if ( $wpl_option_foreign_transactions == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Import','wplister'); ?></option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __('Transactions for items which were not listed with WP-Lister are skipped by default.','wplister'); ?><br>
							</p>

							<?php endif; ?>

							<label for="wpl-option-handle_ebay_refunds" class="text_label">
								<?php echo __('Automatically Handle Refunds','wplister') ?>
								<?php wplister_tooltip('Enable this to automatically update an order in WooCommerce when the original eBay order is refunded.') ?>
							</label>
							<select id="wpl-option-handle_ebay_refunds" name="wpl_e2e_handle_ebay_refunds" class=" required-entry select">
								<option value="1" <?php selected( $wpl_handle_ebay_refunds, 1 ); ?>><?php echo __('Yes','wplister'); ?> (<?php _e('default','wplister'); ?>)</option>
								<option value="0" <?php selected( $wpl_handle_ebay_refunds, 0 ); ?>><?php echo __('No','wplister'); ?></option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __('Update an order in WooCommerce when the original eBay order is refunded.','wplister'); ?><br>
							</p>

							<label for="wpl-option-skip_foreign_site_orders" class="text_label">
								<?php echo __('Skip orders from foreign sites','wplister') ?>
                                <?php wplister_tooltip('If you use the same eBay account to sell on multiple sites, please enable this option to only process orders from the site selected in settings.<br>Otherwise you might have orders in the wrong currency as WooCommerce does not support multiple currencies.') ?>
							</label>
							<select id="wpl-option-skip_foreign_site_orders" name="wpl_e2e_skip_foreign_site_orders" class=" required-entry select">
								<option value="0" <?php if ( $wpl_skip_foreign_site_orders != '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('No','wplister'); ?> (<?php _e('default','wplister'); ?>)</option>
								<option value="1" <?php if ( $wpl_skip_foreign_site_orders == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Yes','wplister'); ?></option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __('Enable this option to process orders only from the selected eBay site.','wplister'); ?><br>
							</p>

							<label for="wpl-option-skip_foreign_item_orders" class="text_label">
								<?php echo __('Skip orders for foreign items','wplister') ?>
                                <?php wplister_tooltip('If you have items listed on eBay which do not exist in WP-Lister, you can enable this option to skip orders which do not contain any known order line items.<br><br>Orders which contain both known and foreign items will still be created in WooCommerce.') ?>
							</label>
							<select id="wpl-option-skip_foreign_item_orders" name="wpl_e2e_skip_foreign_item_orders" class=" required-entry select">
								<option value="0" <?php if ( $wpl_skip_foreign_item_orders != '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('No','wplister'); ?> (<?php _e('default','wplister'); ?>)</option>
								<option value="1" <?php if ( $wpl_skip_foreign_item_orders == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Yes','wplister'); ?></option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __('Enable this option to create orders in WooCommerce only for items which exist in WP-Lister.','wplister'); ?><br>
							</p>

							<label for="wpl-option-store_sku_as_order_meta" class="text_label">
								<?php echo __('Store SKU as line item meta field','wplister') ?>
                                <?php wplister_tooltip('An order in WooCommerce usually does not store the SKU for each order line item but only a reference to the product from which WooCommerce pulls the SKU to display on the order details page.<br><br>This can lead to problems when WP-Lister creates an order for a product which does not exist in WooCommerce, or if the SKU is changed, so by default the SKU is stored as a separate order line item meta field.') ?>
							</label>
							<select id="wpl-option-store_sku_as_order_meta" name="wpl_e2e_store_sku_as_order_meta" class=" required-entry select">
								<option value="1" <?php if ( $wpl_store_sku_as_order_meta == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Yes','wplister'); ?> (<?php _e('default','wplister'); ?>)</option>
								<option value="0" <?php if ( $wpl_store_sku_as_order_meta != '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('No','wplister'); ?></option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __('Disable this option if you do not want the SKU to appear in a separate row in WooCommerce.','wplister'); ?><br>
							</p>

                            <label for="wpl-option-match_sales_by_sku" class="text_label">
                                <?php echo __('Use SKU to match sold items','wplister') ?>
                                <?php wplister_tooltip('Normally when processing a new eBay sale, WP-Lister looks up the eBay Item ID in its database to find the right WooCommerce product to update the stock level. For most users this is the most reliable way of syncing eBay sales back to WooCommerce.<br><br>In some rare use cases, for example when listings are automatically translated and replicated across international eBay sites via a third party service like Webinterpret, the same product might be linked to multiple eBay Item IDs and WP-Lister would have to use the SKU instead to identify the right product in WooCommerce.') ?>
                            </label>
                            <select id="wpl-option-match_sales_by_sku" name="wpl_e2e_match_sales_by_sku" class=" required-entry select">
                                <option value="1" <?php selected( $wpl_match_sales_by_sku, 1 ); ?>><?php echo __('Yes','wplister'); ?></option>
                                <option value="0" <?php selected( $wpl_match_sales_by_sku, 0 ); ?>><?php echo __('No','wplister'); ?> (<?php _e('default','wplister'); ?>)</option>
                            </select>
							<p class="desc" style="display: block;">
								<?php echo __('This option should only be enabled in rare use cases. Read the tooltip for more details.','wplister'); ?><br>
							</p>

							<label for="wpl-option-process_multileg_orders" class="text_label">
								<?php echo __('Global Shipping Program','wplister') ?>
                                <?php wplister_tooltip('Select whether you want international orders which use Global Shipping Program to be handled in a special way.<br><br>If you choose to use the shipping address of the eBay shipping center, the order total of the created order in WooCommerce will not include the shipping fee.') ?>
							</label>
							<select id="wpl-option-process_multileg_orders" name="wpl_e2e_process_multileg_orders" class=" required-entry select">
								<option value="0" <?php if ( $wpl_process_multileg_orders != '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Use buyer shipping address','wplister'); ?> (<?php _e('default','wplister'); ?>)</option>
								<option value="1" <?php if ( $wpl_process_multileg_orders == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Use shipping address of eBay shipping center','wplister'); ?></option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __('How international orders using eBay\'s Global Shipping Program are created in WooCommerce.','wplister'); ?><br>
							</p>

                            <label for="wpl-option-orders_autodetect_tax_rates" class="text_label">
                                <?php echo __('Auto Detect Tax Rates','wplister') ?>
                                <?php wplister_tooltip('Automatically calculate line item taxes based on the purchased product\'s tax class.') ?>
                            </label>
                            <select id="wpl-option-orders_autodetect_tax_rates" name="wpl_e2e_orders_autodetect_tax_rates" class="required-entry select">
                                <option value="0" <?php selected( $wpl_orders_autodetect_tax_rates, 0 ); ?>><?php _e( 'No', 'wplister' ); ?> (<?php _e('default','wplister'); ?>)</option>
                                <option value="1" <?php selected( $wpl_orders_autodetect_tax_rates, 1 ); ?>><?php _e( 'Yes', 'wplister' ); ?></option>
                            </select>

							<label for="wpl-option-process_order_sales_tax_rate_id" class="text_label">
								<?php echo __('Sales tax rate','wplister') ?>
                                <?php wplister_tooltip('This tax rate will used for creating orders if the options below are enabled.') ?>
							</label>
							<select id="wpl-option-process_order_sales_tax_rate_id" name="wpl_e2e_process_order_sales_tax_rate_id" class="required-entry select">
								<option value="">-- <?php echo __('no tax rate','wplister'); ?> --</option>
								<?php foreach ($wpl_tax_rates as $rate) : ?>
									<option value="<?php echo $rate->tax_rate_id ?>" <?php if ( $wpl_process_order_sales_tax_rate_id == $rate->tax_rate_id ): ?>selected="selected"<?php endif; ?>><?php echo $rate->tax_rate_name ?> <?php echo $rate->tax_rate_class ? '('.$rate->tax_rate_class.')' : '' ?></option>					
								<?php endforeach; ?>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __('Select the tax rate to assign to created orders.','wplister'); ?><br>
							</p>

							<label for="wpl-option-process_order_tax_rate_id" class="text_label">
								<?php echo __('VAT tax rate','wplister') ?>
                                <?php wplister_tooltip('This tax rate will used for creating orders if the options below are enabled.<br><br>Note: If you do not select a WooCommerce tax rate here, WP-Lister will create all orders without applying any taxes.') ?>
							</label>
							<select id="wpl-option-process_order_tax_rate_id" name="wpl_e2e_process_order_tax_rate_id" class="required-entry select">
								<option value="">-- <?php echo __('no tax rate','wplister'); ?> --</option>
								<?php foreach ($wpl_tax_rates as $rate) : ?>
									<option value="<?php echo $rate->tax_rate_id ?>" <?php if ( $wpl_process_order_tax_rate_id == $rate->tax_rate_id ): ?>selected="selected"<?php endif; ?>><?php echo $rate->tax_rate_name ?> <?php echo $rate->tax_rate_class ? '('.$rate->tax_rate_class.')' : '' ?></option>					
								<?php endforeach; ?>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __('Select the tax rate to assign to created orders.','wplister'); ?> <?php echo __('Required to use the options below.','wplister'); ?><br>
							</p>

							<label for="wpl-text-orders_fixed_vat_rate" class="text_label">
								<?php echo __('VAT rate (percent)','wplister'); ?>
                                <?php wplister_tooltip('To apply VAT to created orders, enter the tax rate here.<br>Example: For 19% VAT enter "19".<br><br>This option applies to shipping fees and order items where no profile could be found. If a VAT rate is defined in your profile it will be used instead.') ?>
							</label>
							<input type="text" name="wpl_e2e_orders_fixed_vat_rate" id="wpl-text-orders_fixed_vat_rate" value="<?php echo $wpl_orders_fixed_vat_rate; ?>" class="text_input" />
							<p class="desc" style="display: block;">
								<?php echo __('Enter a default tax rate to be applied to order items and shipping fees.','wplister'); ?><br>
							</p>

							<label for="wpl-option-process_order_vat" class="text_label">
								<?php echo __('Create orders using profile VAT','wplister') ?>
                                <?php wplister_tooltip('With this option is enabled, WP-Lister will add a VAT tax row to created orders if the listing profile has VAT enabled.') ?>
							</label>
							<select id="wpl-option-process_order_vat" name="wpl_e2e_process_order_vat" class=" required-entry select">
								<option value="0" <?php if ( $wpl_process_order_vat != '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('No','wplister'); ?></option>
								<option value="1" <?php if ( $wpl_process_order_vat == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Yes','wplister'); ?> (<?php _e('default','wplister'); ?>)</option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __('Process and add VAT to created orders if enabled in the listing profile.','wplister'); ?><br>
							</p>


							<!-- 
							<label for="wpl-option-orders_apply_wc_tax" class="text_label">
								<?php echo __('Apply WooCommerce taxes','wplister') ?>
                                <?php wplister_tooltip('Enable this to apply WooCommerce taxes for orders created on eBay. This might be required if you charge VAT, as eBay will not return any VAT values in its order details.') ?>
							</label>
							<select id="wpl-option-orders_apply_wc_tax" name="wpl_e2e_orders_apply_wc_tax" class=" required-entry select">
								<option value="0" <?php if ( $wpl_orders_apply_wc_tax != '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('No','wplister'); ?> (<?php _e('default','wplister'); ?>)</option>
								<option value="1" <?php if ( $wpl_orders_apply_wc_tax == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Yes','wplister'); ?></option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __('Enable this to apply WooCommerce taxes for orders created on eBay.','wplister'); ?><br>
							</p>
							-->

						</div>
					</div>
					<!-- ## END PRO ## -->

					<div class="postbox" id="OtherSettingsBox">
						<h3 class="hndle"><span><?php echo __('Misc Options','wplister') ?></span></h3>
						<div class="inside">

							<label for="wpl-autofill_missing_gtin" class="text_label">
								<?php echo __('Missing Product Identifiers','wplister'); ?>
                                <?php wplister_tooltip('eBay requires product identifiers (UPC/EAN) in selected categories starting 2015 - missing EANs/UPCs can cause the revise process to fail.<br><br>If your products do not have either UPCs or EANs, please use this option.') ?>
							</label>
							<select id="wpl-autofill_missing_gtin" name="wpl_e2e_autofill_missing_gtin" class="required-entry select">
								<option value=""  <?php if ( $wpl_autofill_missing_gtin == ''  ): ?>selected="selected"<?php endif; ?>><?php echo __('Do nothing','wplister'); ?> (<?php _e('default','wplister'); ?>)</option>
								<option value="upc" <?php if ( $wpl_autofill_missing_gtin == 'upc' ): ?>selected="selected"<?php endif; ?>><?php echo __('If UPC is empty use "Does not apply" instead','wplister'); ?></option>
								<option value="ean" <?php if ( $wpl_autofill_missing_gtin == 'ean' ): ?>selected="selected"<?php endif; ?>><?php echo __('If EAN is empty use "Does not apply" instead','wplister'); ?></option>
								<option value="both" <?php if ( $wpl_autofill_missing_gtin == 'both' ): ?>selected="selected"<?php endif; ?>><?php echo __('If both fields are empty use "Does not apply" instead','wplister'); ?></option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __('Enable this option if your products do not have UPCs or EANs.','wplister'); ?>
							</p>

							<label for="wpl-option-local_timezone" class="text_label">
								<?php echo __('Local timezone','wplister') ?>
                                <?php wplister_tooltip('This is currently used to convert the order creation date from UTC to local time.') ?>
							</label>
							<select id="wpl-option-local_timezone" name="wpl_e2e_option_local_timezone" class="required-entry select">
								<option value="">-- <?php echo __('no timezone selected','wplister'); ?> --</option>
								<?php foreach ($wpl_timezones as $tz_id => $tz_name) : ?>
									<option value="<?php echo $tz_id ?>" <?php if ( $wpl_option_local_timezone == $tz_id ): ?>selected="selected"<?php endif; ?>><?php echo $tz_name ?></option>					
								<?php endforeach; ?>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __('Select your local timezone.','wplister'); ?><br>
							</p>

							<label for="wpl-convert_dimensions" class="text_label">
								<?php echo __('Dimension Unit Conversion','wplister'); ?>
                                <?php wplister_tooltip('WP-Lister assumes that you use the same dimension unit in WooCommerce as on eBay. Enable this to convert length, width and height from one unit to another.') ?>
							</label>
							<select id="wpl-convert_dimensions" name="wpl_e2e_convert_dimensions" class="required-entry select">
								<option value=""  <?php if ( $wpl_convert_dimensions == ''  ): ?>selected="selected"<?php endif; ?>><?php echo __('No conversion','wplister'); ?> (<?php _e('default','wplister'); ?>)</option>
								<option value="in-cm" <?php if ( $wpl_convert_dimensions == 'in-cm' ): ?>selected="selected"<?php endif; ?>><?php echo __('Convert inches to centimeters','wplister'); ?> ( in &raquo; cm )</option>
								<option value="mm-cm" <?php if ( $wpl_convert_dimensions == 'mm-cm' ): ?>selected="selected"<?php endif; ?>><?php echo __('Convert milimeters to centimeters','wplister'); ?> ( mm &raquo; cm )</option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __('Convert length, width and height to the unit required by eBay.','wplister'); ?>
							</p>

							<label for="wpl-convert_attributes_mode" class="text_label">
								<?php echo __('Use attributes as item specifics','wplister'); ?>
                                <?php wplister_tooltip('The default is to convert all WooCommerce product attributes to item specifics on eBay.<br><br>If you disable this option, only the item specifics defined in your listing profile will be sent to eBay.') ?>
							</label>
							<select id="wpl-convert_attributes_mode" name="wpl_e2e_convert_attributes_mode" class="required-entry select">
								<option value="all"    <?php if ( $wpl_convert_attributes_mode == 'all'    ): ?>selected="selected"<?php endif; ?>><?php echo __('Convert all attributes to item specifics','wplister'); ?> (<?php _e('default','wplister'); ?>)</option>
								<option value="single" <?php if ( $wpl_convert_attributes_mode == 'single' ): ?>selected="selected"<?php endif; ?>><?php echo __('Convert all attributes, but disable multi value attributes','wplister'); ?></option>
								<option value="none"   <?php if ( $wpl_convert_attributes_mode == 'none'   ): ?>selected="selected"<?php endif; ?>><?php echo __('Disabled','wplister'); ?></option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __('Disable this option if you do not want all product attributes to be sent to eBay.','wplister'); ?>
							</p>

							<label for="wpl-exclude_attributes" class="text_label">
								<?php echo __('Exclude attributes','wplister') ?>
                                <?php wplister_tooltip('If you want to hide certain product attributes from eBay enter their names separated by commas here.<br>Example: Brand,Size,MPN') ?>
							</label>
							<input type="text" name="wpl_e2e_exclude_attributes" id="wpl-exclude_attributes" value="<?php echo $wpl_exclude_attributes; ?>" class="text_input" />
							<p class="desc" style="display: block;">
								<?php echo __('Enter a comma separated list of product attributes to exclude from eBay.','wplister'); ?><br>
							</p>

							<label for="wpl-exclude_variation_values" class="text_label">
								<?php echo __('Exclude variations','wplister') ?>
                                <?php wplister_tooltip('If you want to hide certain variations from eBay enter their attribute values separated by commas here.<br>Example: Brown,Blue,Orange') ?>
							</label>
							<input type="text" name="wpl_e2e_exclude_variation_values" id="wpl-exclude_variation_values" value="<?php echo $wpl_exclude_variation_values; ?>" class="text_input" />
							<p class="desc" style="display: block;">
								<?php echo __('Enter a comma separated list of variation attribute values to exclude from eBay.','wplister'); ?><br>
							</p>

							<label for="wpl-enable_item_compat_tab" class="text_label">
								<?php echo __('Enable Item Compatibility tab','wplister'); ?>
                                <?php wplister_tooltip('Item compatibility lists are currently only created for imported products. Future versions of WP-Lister Pro will allow to define compatibility lists in WooCommerce.') ?>
							</label>
							<select id="wpl-enable_item_compat_tab" name="wpl_e2e_enable_item_compat_tab" class="required-entry select">
								<option value=""  <?php if ( $wpl_enable_item_compat_tab == ''  ): ?>selected="selected"<?php endif; ?>><?php echo __('No','wplister'); ?></option>
								<option value="1" <?php if ( $wpl_enable_item_compat_tab == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Yes','wplister'); ?> (<?php _e('default','wplister'); ?>)</option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __('Show eBay Item Compatibility List as new tab on single product page.','wplister'); ?>
							</p>

							<label for="wpl-disable_sale_price" class="text_label">
								<?php echo __('Use sale price','wplister'); ?>
                                <?php wplister_tooltip('Set this to No if you want your sale prices to be ignored. You can still use a relative profile price to increase your prices by a percentage.') ?>
							</label>
							<select id="wpl-disable_sale_price" name="wpl_e2e_disable_sale_price" class="required-entry select">
								<option value="0" <?php if ( $wpl_disable_sale_price != '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Yes','wplister'); ?> (<?php _e('default','wplister'); ?>)</option>
								<option value="1" <?php if ( $wpl_disable_sale_price == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('No','wplister'); ?></option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __('Should sale prices be used automatically on eBay?','wplister'); ?><br>
							</p>

							<label for="wpl-apply_profile_to_ebay_price" class="text_label">
								<?php echo __('Apply profile to eBay price','wplister'); ?>
								<?php wplister_tooltip('By default, a custom eBay price (set on the product level) takes precendence over any other prices, including regular prices, sale prices and prices in your listing profile.<br><br>So if you use a profile to reduce all prices by 10% - using the price modifier "-10%" - and you want this to be applied to custom eBay prices as well, please enable this option.') ?>
							</label>
							<select id="wpl-apply_profile_to_ebay_price" name="wpl_e2e_apply_profile_to_ebay_price" class="required-entry select">
								<option value="0" <?php selected( $wpl_apply_profile_to_ebay_price, 0 ); ?>><?php echo __('No','wplister'); ?> (<?php _e('default','wplister'); ?>)</option>
								<option value="1" <?php selected( $wpl_apply_profile_to_ebay_price, 1 ); ?>><?php echo __('Yes','wplister'); ?></option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __('Enable this to allow your listing profile to modify a custom eBay price set on the product level.','wplister'); ?><br>
							</p>

							<!-- ## BEGIN PRO ## -->

							<label for="wpl-option-external_products_inventory" class="text_label">
								<?php echo __('External products inventory','wplister') ?>
                                <?php wplister_tooltip('Enable inventory management on external products. External products have no inventory by default in WooCommerce. <br>Note: This feature is still experimental.') ?>
							</label>
							<select id="wpl-option-external_products_inventory" name="wpl_e2e_external_products_inventory" class=" required-entry select">
								<option value="0" <?php if ( $wpl_external_products_inventory != '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('No','wplister'); ?> (<?php _e('default','wplister'); ?>)</option>
								<option value="1" <?php if ( $wpl_external_products_inventory == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Yes','wplister'); ?></option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __('Enable inventory management on external products.','wplister'); ?><br>
							</p>

                            <label for="wpl-option-enable_out_of_stock_threshold" class="text_label">
                                <?php echo __('Out Of Stock Threshold','wplister'); ?>
                                <?php wplister_tooltip('Enable this to automatically reduce the quantity sent to eBay by the value you entered as "Out Of Stock Threshold" in WooCommerce.') ?>
                            </label>
                            <select id="wpl-option-enable_out_of_stock_threshold" name="wpl_e2e_enable_out_of_stock_threshold" class=" required-entry select">
                                <option value="0" <?php selected( 0, $wpl_enable_out_of_stock_threshold ); ?>><?php echo __('No','wplister'); ?> (<?php _e('default','wplister'); ?>)</option>
                                <option value="1" <?php selected( 1, $wpl_enable_out_of_stock_threshold ); ?>><?php echo __('Yes','wplister'); ?></option>
                            </select>
                            <p class="desc" style="display: block;">
                                <?php echo __('Enable this if you use the "Out Of Stock Threshold" option in WooCommerce.','wplister'); ?><br>
                            </p>

							<!-- ## END PRO ## -->

							<label for="wpl-option-allow_backorders" class="text_label">
								<?php echo __('Ignore backorders','wplister') ?>
                                <?php wplister_tooltip('Since eBay relies on each item having a definitive quantity, allowing backorders for WooCommerce products can cause issues when the last item is sold. WP-Lister can force WooCommerce to mark an product as out of stock when the quantity reaches zero, even with backorders allowed.') ?>
							</label>
							<select id="wpl-option-allow_backorders" name="wpl_e2e_option_allow_backorders" class="required-entry select">
								<option value="0" <?php if ( $wpl_option_allow_backorders != '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('No','wplister'); ?> (<?php _e('recommended','wplister'); ?>)</option>
								<option value="1" <?php if ( $wpl_option_allow_backorders == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Yes','wplister'); ?></option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __('Should a product be marked as out of stock even when it has backorders enabled?','wplister'); ?><br>
							</p>

							<label for="wpl-api_enable_auto_relist" class="text_label">
								<?php echo __('Enable API auto relist','wplister') ?>
                                <?php wplister_tooltip('When a locked product is marked out of stock via the API or CSV import, WP-Lister automatically ends the listing on eBay. Enable this option to allow WP-Lister to automatically relist the item when it is back in stock.<br><br>Note: We highly recommend using eBay\'s <i>Out Of Stock Control</i> feature over this option. This will prevent the listing from being ended in the first place and mark it as Out Of Stock instead.') ?>
							</label>
							<select id="wpl-api_enable_auto_relist" name="wpl_e2e_api_enable_auto_relist" class="required-entry select">
								<option value="0" <?php if ( $wpl_api_enable_auto_relist != '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('No','wplister'); ?> (<?php _e('default','wplister'); ?>)</option>
								<option value="1" <?php if ( $wpl_api_enable_auto_relist == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Yes','wplister'); ?> (<?php _e('not recommended','wplister'); ?>)</option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __('This option is deprecated. Instead you should enable Out Of Stock Control for your eBay account.','wplister'); ?>
								<?php // echo __('Enable this if you update your inventory via the API or CSV import.','wplister'); ?>
								<?php // echo __('This only effects locked items.','wplister'); ?>
							</p>

							<label for="wpl-auto_update_ended_items" class="text_label">
								<?php echo __('Auto update ended items','wplister') ?>
                                <?php wplister_tooltip('This can be helpful if you manually relisted items on eBay - which is not recommended.<br><br>We recommend against using this option as it might cause performance issues and other unexpected results.<br><br>If you experience any problems with this option enabled, please disable it again and see if it solves the problem.') ?>
							</label>
							<select id="wpl-auto_update_ended_items" name="wpl_e2e_auto_update_ended_items" class="required-entry select">
								<option value="0" <?php if ( $wpl_auto_update_ended_items != '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('No','wplister'); ?> (<?php _e('default','wplister'); ?>)</option>
								<option value="1" <?php if ( $wpl_auto_update_ended_items == '1' ): ?>selected="selected"<?php endif; ?>><?php echo __('Yes','wplister'); ?> (<?php _e('not recommended','wplister'); ?>)</option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __('Automatically update item details from eBay when a listing has ended.','wplister'); ?> (experimental!)
							</p>

							<label for="wpl-archive_days_limit" class="text_label">
								<?php echo __('Keep archived items for','wplister'); ?>
                                <?php wplister_tooltip('Select how long archived listings should be kept. Older records are removed automatically. The default is 90 days.') ?>
							</label>
							<select id="wpl-archive_days_limit" name="wpl_e2e_archive_days_limit" class=" required-entry select">
								<option value="7"  <?php if ( $wpl_archive_days_limit == '7' ):  ?>selected="selected"<?php endif; ?>>7 days</option>
								<option value="14"  <?php if ( $wpl_archive_days_limit == '14' ):  ?>selected="selected"<?php endif; ?>>14 days</option>
								<option value="30"  <?php if ( $wpl_archive_days_limit == '30' ):  ?>selected="selected"<?php endif; ?>>30 days</option>
								<option value="60"  <?php if ( $wpl_archive_days_limit == '60' ):  ?>selected="selected"<?php endif; ?>>60 days</option>
								<option value="90"  <?php if ( $wpl_archive_days_limit == '90' ):  ?>selected="selected"<?php endif; ?>>90 days</option>
							</select>
							<p class="desc" style="display: block;">
								<?php echo __('Select how long archived listings should be kept.','wplister'); ?>
							</p>

						</div>
					</div>

					<?php do_action( 'wple_after_advanced_settings' ) ?>


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


</div>