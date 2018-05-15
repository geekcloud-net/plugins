<div class="setting-content">
        <!--<p><a href="https://go.developer.ebay.com/step-step-guide-new-affiliate-developers" target="_blank">Step-by-Step Guide for New Affiliate Developers</a></p>-->
	<h3><?php _ex('Common settings', 'Setting section', 'wpeae'); ?></h3>
	<table class="form-table">
		<tr valign="top">
			<th scope="row" class="titledesc"><label for="wpeae_ebay_per_page"><?php _ex('Products per page', 'Setting title', 'wpeae'); ?></label></th>
			<td class="forminp forminp-text">
				<input type="text" id="wpeae_ebay_per_page" name="wpeae_ebay_per_page" value="<?php echo esc_attr(get_option('wpeae_ebay_per_page', 20)); ?>"/>
				<span class="description"><?php printf(_x('the maximum number of items is %d', 'Setting desc', 'wpeae'), 100); ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc"><label for="wpeae_ebay_extends_cats"><?php _ex('Use sub categories', 'Setting title', 'wpeae'); ?></label></th>
			<td class="forminp forminp-text"><input type="checkbox" id="wpeae_ebay_extends_cats" name="wpeae_ebay_extends_cats" value="yes" <?php if (get_option('wpeae_ebay_extends_cats', false)): ?>checked<?php endif; ?>/></td>
		</tr>
                <tr valign="top">
			<th scope="row" class="titledesc"><label for="wpeae_ebay_user_random_quantity"><?php _ex('Use random quantity', 'Setting title', 'wpeae'); ?></label></th>
			<td class="forminp forminp-text"><input type="checkbox" id="wpeae_ebay_user_random_quantity" name="wpeae_ebay_user_random_quantity" value="yes" <?php if (get_option('wpeae_ebay_user_random_quantity', false)): ?>checked<?php endif; ?>/></td>
		</tr>
                
                <tr valign="top">
			<th scope="row" class="titledesc"><label for="wpeae_ebay_default_site"><?php _ex('Default Site', 'Setting title', 'wpeae'); ?></label></th>
			<td class="forminp forminp-select">
				<?php $sites = $this->get_sites(); ?>
                                <?php $wpeae_ebay_default_site = get_option('wpeae_ebay_default_site', '0'); ?>
				<select name="wpeae_ebay_default_site" id="wpeae_ebay_default_site">
                                    <?php foreach($sites as $site):?>
                                        <option value="<?php echo $site['code']; ?>" <?php if ($wpeae_ebay_default_site == $site['code']): ?>selected="selected"<?php endif; ?>><?php echo $site['name']; ?></option>
                                    <?php endforeach;?>
				</select>
			</td>
		</tr>
                
           
        <tr valign="top">
			<th scope="row" class="titledesc"><label><?php _ex('Rebuild  eBay categories', 'Setting title', 'wpeae'); ?></label></th>
			<td class="forminp forminp-text">
				<input type="submit" class="button" name="ebay-rebuild-categories" value="<?php _ex('Rebuild', 'Setting title', 'wpeae'); ?>"/>
			</td>
		</tr>
        <tr valign="top">
            <th scope="row" class="titledesc"><label><?php _ex('Import eBay categories to Woocommerce', 'Setting title', 'wpeae'); ?></label></th>
            <td class="forminp forminp-text">
                <input type="submit" class="button" name="ebay-import-categories" value="<?php _ex('Import', 'Setting title', 'wpeae'); ?>"/>
            </td>
        </tr> 
	</table>

	<h3><?php _ex('Affiliate setting', 'Setting section', 'wpeae'); ?></h3>
	<table class="form-table">
		<tr valign="top">
			<th scope="row" class="titledesc"><label for="wpeae_ebay_custom_id"><?php _ex('CustomId', 'Setting title', 'wpeae'); ?></label></th>
			<td class="forminp forminp-text"><input type="text" id="wpeae_ebay_custom_id" name="wpeae_ebay_custom_id" value="<?php echo esc_attr(get_option('wpeae_ebay_custom_id')); ?>"/>
			<br/><span class="description"><?php _ex('You can define an affiliate customId if you want an ID to monitor your marketing efforts. Chose an ID up to up to 256 characters in length. If you are using the eBay Partner Network, and you provide a customId, the tracking URL returned by the eBay Partner Network will contain your customId value.', 'Setting desc', 'wpeae'); ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc"><label for="wpeae_ebay_geo_targeting"><?php _ex('geoTargeting', 'Setting title', 'wpeae'); ?></label></th>
			<td class="forminp forminp-text"><input type="checkbox" id="wpeae_ebay_geo_targeting" name="wpeae_ebay_geo_targeting" value="yes" <?php if (get_option('wpeae_ebay_geo_targeting', false)): ?>checked<?php endif; ?>/><br/><span class="description"><?php _ex('The geoTargeting parameter will be used for geographical targeting your affiliate programs. The geo-targeting feature works for English speaking countries (US, UK, CA, AU, and IE) only.', 'Setting desc', 'wpeae'); ?></span></td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc"><label for="wpeae_ebay_network_id"><?php _ex('Network_id', 'Setting title', 'wpeae'); ?></label></th>
			<td class="forminp forminp-select">
				<?php $cur_wpeae_ebay_network_id = get_option('wpeae_ebay_network_id', '9'); ?>
				<select name="wpeae_ebay_network_id" id="wpeae_ebay_network_id">
					<option value="2" <?php if ($cur_wpeae_ebay_network_id == "2"): ?>selected="selected"<?php endif; ?>><?php _ex('Be Free', 'Setting option', 'wpeae'); ?></option>
					<option value="3" <?php if ($cur_wpeae_ebay_network_id == "3"): ?>selected="selected"<?php endif; ?>><?php _ex('Affilinet', 'Setting option', 'wpeae'); ?></option>
					<option value="4" <?php if ($cur_wpeae_ebay_network_id == "4"): ?>selected="selected"<?php endif; ?>><?php _ex('TradeDoubler', 'Setting option', 'wpeae'); ?></option>
					<option value="5" <?php if ($cur_wpeae_ebay_network_id == "5"): ?>selected="selected"<?php endif; ?>><?php _ex('Mediaplex', 'Setting option', 'wpeae'); ?></option>
					<option value="6" <?php if ($cur_wpeae_ebay_network_id == "6"): ?>selected="selected"<?php endif; ?>><?php _ex('DoubleClick', 'Setting option', 'wpeae'); ?></option>
					<option value="7" <?php if ($cur_wpeae_ebay_network_id == "7"): ?>selected="selected"<?php endif; ?>><?php _ex('Allyes', 'Setting option', 'wpeae'); ?></option>
					<option value="8" <?php if ($cur_wpeae_ebay_network_id == "8"): ?>selected="selected"<?php endif; ?>><?php _ex('BJMT', 'Setting option', 'wpeae'); ?></option>
					<option value="9" <?php if ($cur_wpeae_ebay_network_id == "9"): ?>selected="selected"<?php endif; ?>><?php _ex('eBay Partner Network', 'Setting option', 'wpeae'); ?></option>
				</select>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc"><label for="wpeae_ebay_tracking_id"><?php _ex('TrackingId', 'Setting title', 'wpeae'); ?></label></th>
			<td class="forminp forminp-text"><input type="text" id="wpeae_ebay_tracking_id" name="wpeae_ebay_tracking_id" value="<?php echo esc_attr(get_option('wpeae_ebay_tracking_id')); ?>"/><br/><span class="description"><?php _ex('Specify the affiliate value obtained from your tracking partner. For the eBay Partner Network, the tracking ID is the provided Campaign ID ("campid"). A Campaign ID is a unique 10-digit number used for associating traffic and is valid across all programs to which you have been accepted. Another example of this value is the Affiliate ID given to you by TradeDoubler.', 'Setting desc', 'wpeae'); ?></span></td>
		</tr>
	</table>
	<!--
	<h3>Currency settings</h3>
	<table class="form-table">
		<tr valign="top">
			<th scope="row" class="titledesc"><label for="wpeae_ebay_using_woocommerce_currency">Using woocommerce currency</label></th>
			<td class="forminp forminp-text">
				<input type="checkbox" id="wpeae_ebay_using_woocommerce_currency" name="wpeae_ebay_using_woocommerce_currency" value="yes" <?php if (get_option('wpeae_ebay_using_woocommerce_currency', false)): ?>checked<?php endif; ?>/>
				<span class="description">try get price in woocommerce currency</span>
			</td>
		</tr>
	</table>
	-->
</div>