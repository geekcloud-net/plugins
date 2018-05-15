<div class="setting-content">
	<h3><?php _ex('Common settings', 'Setting section', 'wpeae'); ?></h3>
	<table class="form-table">
		<tr valign="top">
			<th scope="row" class="titledesc"><label for="wpeae_ali_per_page"><?php _ex('Products per page', 'Setting title', 'wpeae'); ?></label></th>
			<td class="forminp forminp-text">
				<input type="text" id="wpeae_ali_per_page" name="wpeae_ali_per_page" value="<?php echo esc_attr(get_option('wpeae_ali_per_page', 20)); ?>"/>
				<span class="description"><?php printf(_x('the maximum number of items is %d', 'Setting desc', 'wpeae'), 40); ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc"><label for="wpeae_ali_local_currency"><?php _ex('Local Currency', 'Setting title', 'wpeae'); ?>: </label></th>
			<td class="forminp forminp-select">
				<?php $cur_wpeae_ali_local_currency = get_option('wpeae_ali_local_currency', 'usd'); ?>
				<select name="wpeae_ali_local_currency" id="wpeae_ali_local_currency">
										<option value=""> - </option>
					<option value="usd" <?php if ($cur_wpeae_ali_local_currency == "usd"): ?>selected="selected"<?php endif; ?>><?php _ex('usd', 'Setting option', 'wpeae'); ?></option>
					<option value="rub" <?php if ($cur_wpeae_ali_local_currency == "rub"): ?>selected="selected"<?php endif; ?>><?php _ex('rub', 'Setting option', 'wpeae'); ?></option>
					<option value="gbp" <?php if ($cur_wpeae_ali_local_currency == "gbp"): ?>selected="selected"<?php endif; ?>><?php _ex('gbp', 'Setting option', 'wpeae'); ?></option>
					<option value="brl" <?php if ($cur_wpeae_ali_local_currency == "brl"): ?>selected="selected"<?php endif; ?>><?php _ex('brl', 'Aliexpress setting option', 'wpeae'); ?></option> 
					<option value="cad" <?php if ($cur_wpeae_ali_local_currency == "cad"): ?>selected="selected"<?php endif; ?>><?php _ex('cad', 'Setting option', 'wpeae'); ?></option>
					<option value="aud" <?php if ($cur_wpeae_ali_local_currency == "aud"): ?>selected="selected"<?php endif; ?>><?php _ex('aud', 'Setting option', 'wpeae'); ?></option>
					<option value="eur" <?php if ($cur_wpeae_ali_local_currency == "eur"): ?>selected="selected"<?php endif; ?>><?php _ex('eur', 'Setting option', 'wpeae'); ?></option>
					<option value="inr" <?php if ($cur_wpeae_ali_local_currency == "inr"): ?>selected="selected"<?php endif; ?>><?php _ex('inr', 'Setting option', 'wpeae'); ?></option>
					<option value="uah" <?php if ($cur_wpeae_ali_local_currency == "uah"): ?>selected="selected"<?php endif; ?>><?php _ex('uah', 'Setting option', 'wpeae'); ?></option>
					<option value="jpy" <?php if ($cur_wpeae_ali_local_currency == "jpy"): ?>selected="selected"<?php endif; ?>><?php _ex('jpy', 'Setting option', 'wpeae'); ?></option>
					<option value="mxn" <?php if ($cur_wpeae_ali_local_currency == "mxn"): ?>selected="selected"<?php endif; ?>><?php _ex('mxn', 'Setting option', 'wpeae'); ?></option>
					<option value="idr" <?php if ($cur_wpeae_ali_local_currency == "idr"): ?>selected="selected"<?php endif; ?>><?php _ex('idr', 'Setting option', 'wpeae'); ?></option>
					<option value="try" <?php if ($cur_wpeae_ali_local_currency == "try"): ?>selected="selected"<?php endif; ?>><?php _ex('try', 'Setting option', 'wpeae'); ?></option>
					<option value="sek" <?php if ($cur_wpeae_ali_local_currency == "sek"): ?>selected="selected"<?php endif; ?>><?php _ex('sek', 'Setting option', 'wpeae'); ?></option>
				</select>
			</td>
		</tr>
                <tr valign="top">
			<th scope="row" class="titledesc"><label><?php _ex('Rebuild  aliexpress categories', 'Setting title', 'wpeae'); ?></label></th>
			<td class="forminp forminp-text">
				<input type="submit" class="button" name="aliexpress-rebuild-categories" value="<?php _ex('Rebuild', 'Setting title', 'wpeae'); ?>"/>
			</td>
		</tr>
                
	</table>
	<h3><?php _ex('Import setting', 'Setting section', 'wpeae'); ?></h3>
	<table class="form-table">
	  <tr valign="top">
			<th scope="row" class="titledesc"><label for="wpeae_ali_forbidden_words"><?php _ex('Forbidden phrases', 'Setting title', 'wpeae'); ?></label></th>
			<td class="forminp forminp-text">
				<textarea id="wpeae_ali_forbidden_words" name="wpeae_ali_forbidden_words" style="width:500px;height: 150px;"><?php echo esc_attr( get_option('wpeae_ali_forbidden_words', 'aliexpress,china') ); ?></textarea>
				<div style="padding-top: 5px;">
					<span class="description">
						<?php printf('<strong>%s</strong>%s<br/>%s<br/>%s(<strong>%s</strong>)%s(<strong>%s</strong>).', 
							_x('Example:', 'Setting "Forbidden Word" desc 1', 'wpeae' ),
							_x(' aliexpress, china, shipping method', 'Setting "Forbidden Word" desc 2', 'wpeae' ),
							_x('Please note, word is case-insensative.', 'Setting "Forbidden Word" desc 3', 'wpeae' ),
							_x('It checks Product title and description, review`s text', 'Setting "Forbidden Word" desc 4', 'wpeae'),
							_x('if Review Add-on installed', 'Setting "Forbidden Word" desc 5', 'wpeae'),
							_x(' and shipping method name ', 'Setting "Forbidden Word" desc 6', 'wpeae'),
							_x('Shipping Add-on installed', 'Setting "Forbidden Word" desc 7', 'wpeae')
							);	   
						?>		
					</span>
				</div>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc"><label for="wpeae_ali_links_to_affiliate"><?php _ex('Convert all links from description to affiliate links', 'Setting title', 'wpeae'); ?></label></th>
			<td class="forminp forminp-text"><input type="checkbox" id="wpeae_ali_links_to_affiliate" name="wpeae_ali_links_to_affiliate" value="yes" <?php if (get_option('wpeae_ali_links_to_affiliate', false)): ?>checked<?php endif; ?>/></td>
		</tr>
				<tr valign="top">
			<th scope="row" class="titledesc"><label for="wpeae_ali_import_description"><?php _ex('Import Description', 'Setting title', 'wpeae'); ?></label></th>
			<td class="forminp forminp-text"><input type="checkbox" id="wpeae_ali_import_description" name="wpeae_ali_import_description" value="yes" <?php if (get_option('wpeae_ali_import_description', true)): ?>checked<?php endif; ?>/></td>
		</tr>
				<tr valign="top">
			<th scope="row" class="titledesc"><label for="wpeae_ali_https_image_url"><?php _ex('Https image url', 'Setting title', 'wpeae'); ?></label></th>
			<td class="forminp forminp-text"><input type="checkbox" id="wpeae_ali_https_image_url" name="wpeae_ali_https_image_url" value="yes" <?php if (get_option('wpeae_ali_https_image_url', false)): ?>checked<?php endif; ?>/></td>
		</tr>
	</table>
</div>