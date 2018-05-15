 <div class="setting-content">
	<h3>Translate Setting</h3>
	<?php /*if (!get_option('wpeae_aliexpress_bing_secret', '')):?>
		<div class="error notice">
			<p><?php _e( 'Please input your <strong>Microsoft Azure Key</strong> in WooImporter Translate. Settings<br/>Microsoft has yet again changed the API service. Its now been moved to Azure (from Bing).', 'wpeae_translate' ); ?></p>
		</div>
	<?php endif; */?>
    

    <?php if (isset($translateService) && $translateService->getLastError() !== false) : ?>
    <div class="error notice">
    <p><?php echo $translateService->getLastError();?></p>
    </div>
    <?php endif; ?> 
   
        
   <table class="form-table">
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="wpeae_aliexpress_language">Language</label>
			</th>
			<td class="forminp forminp-select">
				<?php $cur_wpeae_language = get_option('wpeae_aliexpress_language', 'en'); ?>
				<select name="wpeae_aliexpress_language" id="wpeae_aliexpress_language">
					<option value="en" <?php if ($cur_wpeae_language == "en"): ?>selected="selected"<?php endif; ?>>English</option>
					<option value="ar" <?php if ($cur_wpeae_language == "ar"): ?>selected="selected"<?php endif; ?>>Arabic</option>
					<option value="de" <?php if ($cur_wpeae_language == "de"): ?>selected="selected"<?php endif; ?>>German</option>
					<option value="es" <?php if ($cur_wpeae_language == "es"): ?>selected="selected"<?php endif; ?>>Spanish</option>
					<option value="fr" <?php if ($cur_wpeae_language == "fr"): ?>selected="selected"<?php endif; ?>>French</option>
					<option value="it" <?php if ($cur_wpeae_language == "it"): ?>selected="selected"<?php endif; ?>>Italian</option>
					<option value="pl" <?php if ($cur_wpeae_language == "pl"): ?>selected="selected"<?php endif; ?>>Polish</option>
					<option value="ja" <?php if ($cur_wpeae_language == "ja"): ?>selected="selected"<?php endif; ?>>Japanese</option>
					<option value="ko" <?php if ($cur_wpeae_language == "ko"): ?>selected="selected"<?php endif; ?>>Korean</option>
					<option value="nl" <?php if ($cur_wpeae_language == "nl"): ?>selected="selected"<?php endif; ?>>Notherlandish (Dutch)</option>
					<option value="pt" <?php if ($cur_wpeae_language == "pt"): ?>selected="selected"<?php endif; ?>>Portuguese (Brasil)</option>
					<option value="ru" <?php if ($cur_wpeae_language == "ru"): ?>selected="selected"<?php endif; ?>>Russian</option>
					<option value="th" <?php if ($cur_wpeae_language == "th"): ?>selected="selected"<?php endif; ?>>Thai</option>    
					<option value="id" <?php if ($cur_wpeae_language == "id"): ?>selected="selected"<?php endif; ?>>Indonesian</option>              
					<option value="tr" <?php if ($cur_wpeae_language == "tr"): ?>selected="selected"<?php endif; ?>>Turkish</option>
					<option value="vi" <?php if ($cur_wpeae_language == "vi"): ?>selected="selected"<?php endif; ?>>Vietnamese</option>
					<option value="he" <?php if ($cur_wpeae_language == "he"): ?>selected="selected"<?php endif; ?>>Hebrew</option>
				</select>                         
			</td>
		</tr> 
		
		<tr valign="top">
			<th scope="row" class="titledesc"><label for="wpeae_aliexpress_bing_secret">Microsoft Azure subscription key</label></th>
			<td class="forminp forminp-text">
			<?php if (!defined('WPEAE_DEMO_MODE') || !WPEAE_DEMO_MODE) : ?>
				<input type="text" id="wpeae_aliexpress_bing_secret" name="wpeae_aliexpress_bing_secret" value="<?php echo get_option('wpeae_aliexpress_bing_secret', ''); ?>" />
				<span class="description">Input Azure Key 1 or Key 2, also </span>&nbsp;<a href="https://www.microsoft.com/en-us/translator/getstarted.aspx" target="_blank">you can get Microsoft Azure Key here</a>, <strong>use this option for 'ar', 'he', 'pl' languages only</strong>
			<?php else: ?>
				<span class="description">You can`t change the Key on this demo website.</span>     
			<?php endif; ?>
			</td>
		</tr> 
	</table> 
 </div>