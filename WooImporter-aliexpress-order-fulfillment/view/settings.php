 <div class="setting-content">
	<h3>Order Fulfillment Setting</h3>
   <table class="form-table">
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="wpeae_aliorder_fulfillment_prefship">Preferred Shipping method</label>
			</th>
			<td class="forminp forminp-select">
				<?php $cur_wpeae_prefship = get_option('wpeae_aliorder_fulfillment_prefship', 'ePacket'); ?>
				<select name="wpeae_aliorder_fulfillment_prefship" id="wpeae_aliorder_fulfillment_prefship">
					<option value="ePacket" <?php if ($cur_wpeae_prefship == "ePacket"): ?>selected="selected"<?php endif; ?>>ePacket</option>
					<option value="EMS" <?php if ($cur_wpeae_prefship == "EMS"): ?>selected="selected"<?php endif; ?>>EMS</option>
					<option value="CAINIAO_STANDARD" <?php if ($cur_wpeae_prefship == "CAINIAO_STANDARD"): ?>selected="selected"<?php endif; ?>>AliExpress Standard Shipping</option>
					<option value="CAINIAO_PREMIUM" <?php if ($cur_wpeae_prefship == "CAINIAO_PREMIUM"): ?>selected="selected"<?php endif; ?>>AliExpress Premium Shipping</option>
					<option value="FEDEX_IE" <?php if ($cur_wpeae_prefship == "FEDEX_IE"): ?>selected="selected"<?php endif; ?>>Fedex IE</option>
					<option value="DHL" <?php if ($cur_wpeae_prefship == "DHL"): ?>selected="selected"<?php endif; ?>>DHL</option>
					<option value="CPAM" <?php if ($cur_wpeae_prefship == "CPAM"): ?>selected="selected"<?php endif; ?>>China Post Registered Air Mail</option>		
				</select>                         
			</td>
		</tr>  
	</table> 
 </div>