<fieldset class="inline-edit-col-center">
	<div class="inline-edit-col">
		&nbsp;
	</div>
</fieldset><!-- Spacer fieldset -->
<fieldset class="inline-edit-col-right">
	<div id="wplister-fields" class="inline-edit-col" style="clear:left;">
		<h4><?php _e('eBay Listing', 'wplister'); ?></h4>

		<div class="inline-edit-group wp-clearfix">
			<label class="alignleft">
				<input type="checkbox" value="yes" name="revise_listing" />
				<span class="checkbox-title"><?php _e( 'Revise on update', 'wplister' ); ?></span>
			</label>
		</div>

		<div class="price_fields">
			<label>
				<span class="title"><?php _e( 'eBay Price', 'wplister' ); ?></span>
				<span class="input-text-wrap">
					<input type="text" name="_ebay_start_price" id="ebay_start_price" class="text ebay_start_price wc_input_price" value="">
				</span>
			</label>
			<br class="clear" />
		</div>
	</div>
</fieldset>