<?php
$api_list = wpeae_get_api_list(true);
$current_module = (isset($_REQUEST['module']) && $_REQUEST['module']) ? $_REQUEST['module'] : _x('common', 'setting tab name', 'wpeae'); 

?>

<div class="wpeae-settings-content">
	<h1><?php printf( __('%s settings', 'wpeae'), WPEAE_NAME ); ?></h1>
	<div class="wrap light-tabs" default-rel="<?php echo $current_module; ?>">
		<h2 class="nav-tab-wrapper">
			<a href="#" class="nav-tab<?php echo $current_module == "common" ? " nav-tab-active" : ""; ?>" rel="common"><?php _ex('Common settings', 'Setting tab', 'wpeae'); ?></a>
			<a href="#" class="nav-tab<?php echo $current_module == "price_formula" ? " nav-tab-active" : ""; ?>" rel="price_formula"><?php _ex('Price Rules', 'Setting tab', 'wpeae'); ?></a>
			<?php foreach ($api_list as $api): ?>
				<a href="#" class="nav-tab<?php echo $current_module == $api->get_type() ? " nav-tab-active" : ""; ?>" rel="<?php echo $api->get_type(); ?>"><?php printf( _x('%s settings', 'Setting tab', 'wpeae'), $api->get_config_value("dashboard_title") ); ?></a>
			<?php endforeach; ?>
		</h2>
		<div class="tab_content" rel="common">
			<form method="post">
				<input type="hidden" name="setting_form" value="1"/>
				<input type="hidden" name="module" value="common"/>
				<h3><?php _ex('Common settings', 'Setting section', 'wpeae'); ?></h3>
				<table class="form-table">
					<tr valign="top">
						<th scope="row" class="titledesc"><label for="wpeae_currency_conversion_factor"><?php _ex('Currency conversion factor', 'Setting title', 'wpeae'); ?></label></th>
						<td class="forminp forminp-text"><input type="text" id="wpeae_currency_conversion_factor" name="wpeae_currency_conversion_factor" value="<?php echo esc_attr(get_option('wpeae_currency_conversion_factor', '1')); ?>"/></td>
					</tr>

					<tr valign="top">
						<th scope="row" class="titledesc">
							<label for="wpeae_default_type"><?php _ex('Default Product Type', 'Setting title', 'wpeae'); ?></label>
						</th>
						<td class="forminp forminp-select">
							<?php $cur_wpeae_default_type = get_option('wpeae_default_type', 'simple'); ?>
							<select name="wpeae_default_type" id="wpeae_default_type">
								<option value="simple" <?php if ($cur_wpeae_default_type == "simple"): ?>selected="selected"<?php endif; ?>><?php _ex('Simple Product', 'Setting option', 'wpeae'); ?></option>
								<option value="external" <?php if ($cur_wpeae_default_type == "external"): ?>selected="selected"<?php endif; ?>><?php _ex('External/Affiliate Product', 'Setting option', 'wpeae'); ?></option>
								<!--<option value="grouped" <?php if ($cur_wpeae_default_type == "grouped"): ?>selected="selected"<?php endif; ?>>Grouped Product</option>-->
								<!--<option value="variable" <?php if ($cur_wpeae_default_type == "variable"): ?>selected="selected"<?php endif; ?>>Variable Product</option>-->
							</select>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row" class="titledesc">
							<label for="wpeae_default_status"><?php _ex('Default Product Status', 'Setting title', 'wpeae'); ?></label>
						</th>
						<td class="forminp forminp-select">
							<?php $cur_wpeae_default_status = get_option('wpeae_default_status', 'publish'); ?>
							<select name="wpeae_default_status" id="wpeae_default_status">
								<option value="publish" <?php if ($cur_wpeae_default_status == "publish"): ?>selected="selected"<?php endif; ?>><?php _ex('publish', 'Setting option', 'wpeae'); ?></option>
								<option value="draft" <?php if ($cur_wpeae_default_status == "draft"): ?>selected="selected"<?php endif; ?>><?php _e('draft', 'Setting option', 'wpeae'); ?></option>
							</select> 						
						</td>
					</tr>
				</table>

				<h3><?php _ex('Import setting', 'Setting section', 'wpeae'); ?></h3>
				<table class="form-table">
					<tr valign="top">
						<th scope="row" class="titledesc"><label for="wpeae_remove_link_from_desc"><?php _ex('Remove links from description', 'Setting title', 'wpeae'); ?></label></th>
						<td class="forminp forminp-text"><input type="checkbox" id="wpeae_remove_link_from_desc" name="wpeae_remove_link_from_desc" value="yes" <?php if (get_option('wpeae_remove_link_from_desc', false)): ?>checked<?php endif; ?>/></td>
					</tr>
					<tr valign="top">
						<th scope="row" class="titledesc"><label for="wpeae_remove_img_from_desc"><?php _ex('Remove images from description', 'Setting title', 'wpeae'); ?></label></th>
						<td class="forminp forminp-text"><input type="checkbox" id="wpeae_remove_img_from_desc" name="wpeae_remove_img_from_desc" value="yes" <?php if (get_option('wpeae_remove_img_from_desc', false)): ?>checked<?php endif; ?>/></td>
					</tr>
                                        <tr valign="top">
						<th scope="row" class="titledesc"><label for="wpeae_import_load_image_from_descr"><?php _ex('Download image from description', 'Setting title', 'wpeae'); ?></label></th>
						<td class="forminp forminp-text"><input type="checkbox" id="wpeae_import_load_image_from_descr" name="wpeae_import_load_image_from_descr" value="yes" <?php if (get_option('wpeae_import_load_image_from_descr', false)): ?>checked<?php endif; ?>/></td>
					</tr>
                                        <tr valign="top">
						<th scope="row" class="titledesc"><label for="wpeae_import_extended_attribute"><?php _ex('Use extended attributes', 'Setting title', 'wpeae'); ?></label></th>
						<td class="forminp forminp-text"><input type="checkbox" id="wpeae_import_extended_attribute" name="wpeae_import_extended_attribute" value="yes" <?php if (get_option('wpeae_import_extended_attribute', false)): ?>checked<?php endif; ?>/></td>
					</tr>
					<tr valign="top">
						<th scope="row" class="titledesc"><label for="wpeae_import_product_images_limit"><?php _ex('Import product images limit', 'Setting title', 'wpeae'); ?></label></th>
						<td class="forminp forminp-text"><input type="text" id="wpeae_import_product_images_limit" name="wpeae_import_product_images_limit" value="<?php echo esc_attr(get_option('wpeae_import_product_images_limit')); ?>"/></td>
					</tr>
					<tr valign="top">
						<th scope="row" class="titledesc"><label for="wpeae_min_product_quantity"><?php _ex('Default product quantity', 'Setting title', 'wpeae'); ?></label></th>
						<td class="forminp forminp-text">
							<?php _ex('from', 'Setting desc', 'wpeae'); ?>: <input type="text" style="width:60px" id="wpeae_min_product_quantity" name="wpeae_min_product_quantity" value="<?php echo esc_attr(get_option('wpeae_min_product_quantity', 5)); ?>"/>
							<?php _ex('to', 'Setting desc', 'wpeae'); ?>: <input type="text" style="width:60px" id="wpeae_max_product_quantity" name="wpeae_max_product_quantity" value="<?php echo esc_attr(get_option('wpeae_max_product_quantity', 10)); ?>"/>
						</td>
					</tr>
				</table>

				<h3><?php _ex('Schedule setting', 'Setting section', 'wpeae'); ?></h3>
				<table class="form-table">
					<tr valign="top">
						<th scope="row" class="titledesc"><label for="wpeae_price_auto_update"><?php _ex('Auto Update (stock avail. only)', 'Setting title', 'wpeae'); ?></label></th>
						<td class="forminp forminp-text"><input type="checkbox" id="wpeae_price_auto_update" name="wpeae_price_auto_update" value="yes" <?php if (get_option('wpeae_price_auto_update', false)): ?>checked<?php endif; ?>/></td>
					</tr>

					<tr valign="top">
						<th scope="row" class="titledesc"><label for="wpeae_regular_price_auto_update"><?php _ex('Auto Update Price', 'Setting title', 'wpeae'); ?></label></th>
						<td class="forminp forminp-text"><input type="checkbox" id="wpeae_regular_price_auto_update" name="wpeae_regular_price_auto_update" value="yes" <?php if (!get_option('wpeae_price_auto_update', false)): ?>disabled<?php endif; ?> <?php if (get_option('wpeae_regular_price_auto_update', false)): ?>checked<?php endif; ?>/></td>
					</tr>

					<tr valign="top">
						<th scope="row" class="titledesc">
							<label for="wpeae_not_available_product_status"><?php _ex('Not available product status', 'Setting title', 'wpeae'); ?></label>
						</th>
						<td class="forminp forminp-select">
							<?php $cur_wpeae_not_available_product_status = get_option('wpeae_not_available_product_status', 'trash'); ?>
							<select name="wpeae_not_available_product_status" id="wpeae_not_available_product_status" <?php if (!get_option('wpeae_price_auto_update', false)): ?>disabled<?php endif; ?>>
								<option value="trash" <?php if ($cur_wpeae_not_available_product_status == "trash"): ?>selected="selected"<?php endif; ?>><?php _ex('Trash', 'Setting option', 'wpeae'); ?></option>
								<option value="outofstock" <?php if ($cur_wpeae_not_available_product_status == "outofstock"): ?>selected="selected"<?php endif; ?>><?php _ex('Out of stock', 'Setting option', 'wpeae'); ?></option>
								<option value="instock" <?php if ($cur_wpeae_not_available_product_status == "instock"): ?>selected="selected"<?php endif; ?>><?php _ex('In stock', 'Setting option', 'wpeae'); ?></option>
							</select>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row" class="titledesc">
							<label for="wpeae_price_auto_update_period"><?php _ex('Update Schedule', 'Setting title', 'wpeae'); ?></label>
						</th>
						<td class="forminp forminp-select">
							<?php $cur_wpeae_price_auto_update_period = get_option('wpeae_price_auto_update_period', 'daily'); ?>
							<select name="wpeae_price_auto_update_period" id="wpeae_price_auto_update_period" <?php if (!get_option('wpeae_price_auto_update', false)): ?>disabled<?php endif; ?>>
								<option value="wpeae_5_mins" <?php if ($cur_wpeae_price_auto_update_period == "wpeae_5_mins"): ?>selected="selected"<?php endif; ?>><?php _ex('Every 5 Minutes', 'Setting option', 'wpeae'); ?></option>
								<option value="wpeae_15_mins" <?php if ($cur_wpeae_price_auto_update_period == "wpeae_15_mins"): ?>selected="selected"<?php endif; ?>><?php _e('Every 15 Minutes', 'Setting option', 'wpeae'); ?></option>
								<option value="hourly" <?php if ($cur_wpeae_price_auto_update_period == "hourly"): ?>selected="selected"<?php endif; ?>><?php _ex('hourly','Setting option', 'wpeae'); ?></option>
								<option value="twicedaily" <?php if ($cur_wpeae_price_auto_update_period == "twicedaily"): ?>selected="selected"<?php endif; ?>><?php _ex('twicedaily', 'Setting option', 'wpeae'); ?></option>
								<option value="daily" <?php if ($cur_wpeae_price_auto_update_period == "daily"): ?>selected="selected"<?php endif; ?>><?php _ex('daily', 'Setting title', 'wpeae'); ?></option>
							</select> 						
						</td>
					</tr>

					<th scope="row" class="titledesc"><label for="wpeae_update_per_schedule"><?php _ex('The number of products update per schedule', 'Setting title', 'wpeae'); ?></label></th>
					<td class="forminp forminp-text"><input type="text" id="wpeae_update_per_schedule" name="wpeae_update_per_schedule" value="<?php echo esc_attr(get_option('wpeae_update_per_schedule', 20)); ?>" <?php if (!get_option('wpeae_price_auto_update', false)): ?>disabled<?php endif; ?>/></td>
				</table>

				<h3><?php _ex('Proxy settings', 'Setting section', 'wpeae'); ?></h3>
				<table class="form-table">
					<tr valign="top">
						<th scope="row" class="titledesc"><label for="wpeae_use_proxy"><?php _ex('Use proxy', 'Setting title', 'wpeae'); ?></label></th>
						<td class="forminp forminp-text"><input type="checkbox" id="wpeae_use_proxy" name="wpeae_use_proxy" value="yes" <?php if (get_option('wpeae_use_proxy', false)): ?>checked<?php endif; ?>/></td>
					</tr>
					<tr valign="top"<?php if (!get_option('wpeae_use_proxy', false)): ?>style="display:none;"<?php endif; ?>>
						<th scope="row" class="titledesc"><label for="wpeae_proxies_list"><?php _ex('Proxy list', 'Setting title', 'wpeae'); ?></label></th>
						<td class="forminp forminp-text">
							<textarea id="wpeae_proxies_list" name="wpeae_proxies_list" style="width:500px;height: 150px;"><?php echo get_option('wpeae_proxies_list', ''); ?></textarea>
							<div style="padding-top: 5px;">
								<span class="description">
								<?php printf('%s<br/>proxy.example.com:8080<br/>username:password@proxy.example.com:8080<br/><strong>%s<a href="http://www.squidproxies.com/billing/aff.php?aff=1112" target="_blank">%s</a></strong>', 
							_x('Proxy example:', 'Setting "Proxy" desc 1', 'wpeae' ),
							_x('You can buy proxies ', 'Setting "Proxy" desc 2', 'wpeae' ),_x('here ', 'Setting "Proxy" desc 3', 'wpeae' ));
							?>
								</span>
							</div>
							<div style="padding-top: 5px;">
								<a href="#" id="proxy_test" class="proxy_test"><?php _ex('Test proxy', 'Setting title', 'wpeae'); ?></a>
								<div id="proxy_test_result" style="padding: 10px;font-size: 85%;"></div>
							</div>
						</td>
					</tr>
				</table>


				<?php do_action('wpeae_print_common_setting_page'); ?>


				<input class="button-primary" type="submit" value="<?php _e('Save settings', 'wpeae'); ?>"/><br/>
			</form>
			<script>
				(function ($) {



					jQuery("#wpeae_price_auto_update").change(function () {
						jQuery("#wpeae_price_auto_update_period").prop('disabled', !jQuery(this).is(':checked'));
						jQuery("#wpeae_regular_price_auto_update").prop('disabled', !jQuery(this).is(':checked'));
						jQuery("#wpeae_regular_price_auto_update").prop('checked', jQuery(this).is(':checked'));
						jQuery("#wpeae_update_per_schedule").prop('disabled', !jQuery(this).is(':checked'));
						jQuery("#wpeae_not_available_product_status").prop('disabled', !jQuery(this).is(':checked'));
						return true;
					});

					jQuery("#wpeae_use_proxy").change(function () {
						if (jQuery(this).is(':checked')) {
							jQuery("#wpeae_proxies_list").closest('tr').show();
						} else {
							jQuery("#wpeae_proxies_list").closest('tr').hide();
						}
					});

					$(".proxy_test").click(function () {
						var data = {'action': 'wpeae_proxy_test'};
						$('#proxy_test_result').html('testing...');
						$.post(ajaxurl, data, function (response) {
							$('#proxy_test_result').html(response);
						});
						return false;
					});
				})(jQuery);


			</script>

		</div>
		<div class="tab_content" rel="price_formula">

			<h3><?php _ex('Add price rule', 'Setting section', 'wpeae'); ?></h3>
			<table>
				<tr id="wpeae_price_formula_add_form">
					<td>&nbsp;</td>
					<td>
						<select name="type">
							<option value=""><?php _ex('Any module', 'Setting option', 'wpeae'); ?></option>
							<?php foreach ($api_list as $api): ?>
								<option value="<?php echo $api->get_type(); ?>"><?php echo $api->get_type(); ?></option>
							<?php endforeach; ?>
						</select>
					</td>
					<td>
						<?php $categories_tree = WPEAE_Utils::get_categories_tree(); ?>
						<select name="category" style="width:100%">
							<option value=""><?php _ex('Any category', 'Setting option', 'wpeae'); ?></option>
							<?php foreach ($categories_tree as $cat): ?>
								<option value="<?php echo $cat['term_id'] ?>"><?php
							for ($i = 1; $i < $cat["level"]; $i++) {
								echo " - ";
							}
								?><?php echo $cat['name'] ?></option>
								<?php endforeach; ?>
						</select>
					</td>
					<td><input type="text" name="min_price" value="" placeholder="Min price"/></td>
					<td class="price_label"> < <?php _ex('PRICE', 'Setting formula argument', 'wpeae'); ?> < </td>
					<td><input type="text" name="max_price" value="" placeholder="Max price"/></td>
					<td>
						<select name="sign">
							<option value="="> = </option>
							<option value="+"> + </option>
							<option value="*"> * </option>
						</select>
					</td>
					<td><input type="text" name="value" value="" placeholder="Value"/></td>
					<td class="discount">
						Discount % <select name="discount1">
							<option value=""><?php _ex('source', 'Setting option', 'wpeae'); ?> %</option>
							<option value="0">0%</option>
							<option value="5">5%</option>
							<option value="10">10%</option>
							<option value="15">15%</option>
							<option value="20">20%</option>
							<option value="25">25%</option>
							<option value="30">30%</option>
							<option value="35">35%</option>
							<option value="40">40%</option>
							<option value="45">45%</option>
							<option value="50">50%</option>
							<option value="55">55%</option>
							<option value="60">60%</option>
							<option value="65">65%</option>
							<option value="70">70%</option>
							<option value="75">75%</option>
							<option value="80">80%</option>
							<option value="85">85%</option>
							<option value="90">90%</option>
							<option value="95">95%</option>
						</select>

						- <select name="discount2">
							<option value=""><?php _ex('source', 'Setting option', 'wpeae'); ?> %</option>
							<option value="0">0%</option>
							<option value="5">5%</option>
							<option value="10">10%</option>
							<option value="15">15%</option>
							<option value="20">20%</option>
							<option value="25">25%</option>
							<option value="30">30%</option>
							<option value="35">35%</option>
							<option value="40">40%</option>
							<option value="45">45%</option>
							<option value="50">50%</option>
							<option value="55">55%</option>
							<option value="60">60%</option>
							<option value="65">65%</option>
							<option value="70">70%</option>
							<option value="75">75%</option>
							<option value="80">80%</option>
							<option value="85">85%</option>
							<option value="90">90%</option>
							<option value="95">95%</option>
						</select>
					</td>
					<td><button class="button-primary" id="wpeae_add_formula"><?php _ex('Add', 'Setting button', 'wpeae'); ?></button></td>
				</tr>
			</table>
			<div class="price_formula_description"><?php _ex('Here you can configure your price modification algorithm.', 'Setting desc' , 'wpeae'); ?></div>

			<h3><?php _ex('Price rules list', 'Setting section', 'wpeae'); ?></h3>
			<?php $formula_list = WPEAE_PriceFormula::load_formulas_list(); ?>
			<table id="wpeae_price_formula" class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th class="manage-column column-pos">#</th>
						<th class="manage-column column-module"><?php _ex('Module', 'Setting column', 'wpeae'); ?></th>
						<th class="manage-column column-category"><?php _ex('Category', 'Setting column', 'wpeae'); ?></th>
						<th class="manage-column column-price"><?php _ex('Price', 'Setting column', 'wpeae'); ?></th>
						<th class="manage-column column-value"><?php _ex('New Price', 'Setting column', 'wpeae'); ?></th>
						<th class="manage-column column-discount"><?php _ex('Discount', 'Setting column', 'wpeae'); ?> %</th>
						<th class="manage-column column-action">&nbsp;</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($formula_list as $formula): ?>
						<tr formula-id="<?php echo $formula->id; ?>">
							<td><?php echo $formula->pos; ?></td>
							<td><?php echo $formula->type; ?></td>
							<td><?php echo $formula->category_name; ?></td>
							<td><?php echo $formula->min_price; ?> < <?php _ex('PRICE', 'Setting formula argument', 'wpeae'); ?> < <?php echo $formula->max_price; ?></td>
							<td><?php echo ($formula->sign == "=") ? $formula->value : (_x('PRICE', 'Setting formula argument', 'wpeae') . " " . $formula->sign . " " . $formula->value); ?></td>
							<td>
								<?php
								if (strlen(trim((string) $formula->discount1)) > 0 && strlen(trim((string) $formula->discount2)) > 0) {
									if (IntVal($formula->discount1) > IntVal($formula->discount2)) {
										echo $formula->discount2 . "% &mdash; " . $formula->discount1 . "%";
									} else {
										echo $formula->discount1 . "% &mdash; " . $formula->discount2 . "%";
									}
								} else if (strlen(trim((string) $formula->discount1)) > 0 || strlen(trim((string) $formula->discount2)) > 0) {
									echo (strlen(trim((string) $formula->discount1)) > 0 ? $formula->discount1 : $formula->discount2) . "%";
								} else {
									echo _x('source', 'Setting formula argument', 'wpeae') . " %";
								}
								?>
							</td>
							<td>
								<a class="button-primary wpeae_edit_formula"><?php _ex('Edit', 'Setting button', 'wpeae'); ?></a>
								<a class="button-primary wpeae_del_formula"><?php _ex('Delete', 'Setting button', 'wpeae'); ?></a>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

		</div>

		<?php foreach ($api_list as $api): ?>
			<div class="tab_content" rel="<?php echo $api->get_type(); ?>">
				<form method="post" enctype="multipart/form-data">
					<input type="hidden" name="setting_form" value="1"/>
					<input type="hidden" name="module" value="<?php echo $api->get_type(); ?>"/>

					<?php do_action('wpeae_print_api_setting_page', $api); ?>

					<input class="button-primary" type="submit" value="Save settings"/><br/>
				</form>
			</div>
		<?php endforeach; ?>

		<script>
			jQuery(".wpeae-settings-content .account-content a.use_custom_account_param").click(function () {
				jQuery(this).closest('form').find('input[name="account_type"]').remove();
				jQuery(this).closest('form').append('<input type="hidden" name="account_type" value="custom"/>');
				jQuery(this).closest('form').submit();
				return false;
			});

			jQuery(".wpeae-settings-content .account-content a.use_default_account_param").click(function () {
				jQuery(this).closest('form').find('input[name="account_type"]').remove();
				jQuery(this).closest('form').append('<input type="hidden" name="account_type" value="default"/>');
				jQuery(this).closest('form').submit();
				return false;
			});
		</script>
	</div>
</div>
