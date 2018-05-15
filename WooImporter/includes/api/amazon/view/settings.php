<div class="setting-content">
	<h3><?php _ex('Common settings', 'Setting section', 'wpeae'); ?></h3>
	<table class="form-table">
		<tr valign="top">
			<th scope="row" class="titledesc"><label for="wpeae_amazon_default_site"><?php _ex('Default site', 'Setting title', 'wpeae'); ?></label></th>
			<td class="forminp forminp-select">
				<?php $cur_wpeae_amazon_default_site = get_option('wpeae_amazon_default_site', 'com'); ?>
				<select name="wpeae_amazon_default_site" id="wpeae_amazon_default_site">
					<option value="com" <?php if ($cur_wpeae_amazon_default_site == "com"): ?>selected="selected"<?php endif; ?>><?php _ex('com', 'Setting option', 'wpeae'); ?></option>
					<option value="de" <?php if ($cur_wpeae_amazon_default_site == "de"): ?>selected="selected"<?php endif; ?>><?php _ex('de', 'Setting option', 'wpeae'); ?></option>
					<option value="co.uk" <?php if ($cur_wpeae_amazon_default_site == "co.uk"): ?>selected="selected"<?php endif; ?>><?php _ex('co.uk', 'Setting option', 'wpeae'); ?></option>
					<option value="ca" <?php if ($cur_wpeae_amazon_default_site == "ca"): ?>selected="selected"<?php endif; ?>><?php _ex('ca', 'Setting option', 'wpeae'); ?></option>
					<option value="fr" <?php if ($cur_wpeae_amazon_default_site == "fr"): ?>selected="selected"<?php endif; ?>><?php _ex('fr', 'Setting option', 'wpeae'); ?></option>
					<option value="co.jp" <?php if ($cur_wpeae_amazon_default_site == "co.jp"): ?>selected="selected"<?php endif; ?>><?php _ex('co.jp', 'Setting option', 'wpeae'); ?></option>
					<option value="it" <?php if ($cur_wpeae_amazon_default_site == "it"): ?>selected="selected"<?php endif; ?>><?php _ex('it', 'Setting option', 'wpeae'); ?></option>
					<option value="cn" <?php if ($cur_wpeae_amazon_default_site == "cn"): ?>selected="selected"<?php endif; ?>><?php _ex('cn', 'Setting option', 'wpeae'); ?></option>
					<option value="es" <?php if ($cur_wpeae_amazon_default_site == "es"): ?>selected="selected"<?php endif; ?>><?php _ex('es', 'Setting option', 'wpeae'); ?></option>
					<option value="in" <?php if ($cur_wpeae_amazon_default_site == "in"): ?>selected="selected"<?php endif; ?>><?php _ex('in', 'Setting option', 'wpeae'); ?></option>
				</select>
			</td>
		</tr>
	</table>

	<h3><?php _ex('Import setting', 'Setting section', 'wpeae'); ?></h3>
	<table class="form-table">
		<tr valign="top">
			<th scope="row" class="titledesc"><label for="wpeae_amazon_import_description"><?php _ex('Import Description', 'Setting title', 'wpeae'); ?></label></th>
			<td class="forminp forminp-text"><input type="checkbox" id="wpeae_amazon_import_description" name="wpeae_amazon_import_description" value="yes" <?php if (get_option('wpeae_amazon_import_description', true)): ?>checked<?php endif; ?>/></td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc"><label for="wpeae_amazon_default_condition"><?php _ex('Default condition', 'Setting title', 'wpeae'); ?></label></th>
			<td class="forminp forminp-text">
				<?php $cur_wpeae_amazon_default_condition = get_option('wpeae_amazon_default_condition', ''); ?>
				<select name="wpeae_amazon_default_condition" id="wpeae_amazon_default_condition">
					<option value=""></option>
					<option value="New" <?php if ($cur_wpeae_amazon_default_condition == "New"): ?>selected="selected"<?php endif; ?>><?php _ex('New', 'Setting option', 'wpeae'); ?></option>
					<option value="Used" <?php if ($cur_wpeae_amazon_default_condition == "Used"): ?>selected="selected"<?php endif; ?>><?php _ex('Used', 'Setting option', 'wpeae'); ?></option>
					<option value="Collectible" <?php if ($cur_wpeae_amazon_default_condition == "Collectible"): ?>selected="selected"<?php endif; ?>><?php _ex('Collectible', 'Setting option', 'wpeae'); ?></option>
					<option value="Refurbished" <?php if ($cur_wpeae_amazon_default_condition == "Refurbished"): ?>selected="selected"<?php endif; ?>><?php _ex('Refurbished', 'Setting option', 'wpeae'); ?></option>
					<option value="All" <?php if ($cur_wpeae_amazon_default_condition == "All"): ?>selected="selected"<?php endif; ?>><?php _ex('All', 'Setting option', 'wpeae'); ?></option>
				</select>
			</td>
		</tr>
	</table>
</div>