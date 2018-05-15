 <div class="setting-content">
	<h3><?php _e('Shipping Setting','wpeae-ali-ship'); ?></h3>
   <table class="form-table">
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="wpeae_aliship_shipto"><?php _e('Default Ship-to Country','wpeae-ali-ship'); ?></label>
			</th>
			<td class="forminp forminp-select">
			
				<?php $shipping_countries = WPEAE_AliexpressShippingLoader::get_shipping_countries();
					  $cur_wpeae_aliship_shipto = get_option('wpeae_aliship_shipto', 'US');       
				?>
			
				<select id="wpeae_aliship_shipto" name="wpeae_aliship_shipto" class="category_list" style="width:25em;">
					<?php foreach ($shipping_countries as $c => $n): ?>
						<option value="<?php echo $c; ?>"<?php if ($cur_wpeae_aliship_shipto  == $c): ?> selected<?php endif; ?>>
							<?php echo $n; ?>
						</option>
					<?php endforeach; ?>
				</select>
					 
			</td>
		</tr> 
		<tr valign="top">
			<th scope="row" class="titledesc"><label for="wpeae_aliship_frontend"><?php _e('Show on Frontend','wpeae-ali-ship'); ?></label></th>
			<td class="forminp forminp-text">
				<input type="checkbox" id="wpeae_aliship_frontend" name="wpeae_aliship_frontend" <?php if (get_option('wpeae_aliship_frontend')): ?>value="yes" checked<?php endif; ?> />
			</td>
		</tr>
	</table> 
 </div>