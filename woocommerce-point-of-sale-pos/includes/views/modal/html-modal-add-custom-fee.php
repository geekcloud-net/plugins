<div class="md-modal md-dynamicmodal md-close-by-overlay md-register" id="modal-add_custom_fee">
    <div class="md-content">
        <h1><span class="currency_symbol"><?php echo get_woocommerce_currency_symbol(); ?></span><?php _e('Fee', 'wc_point_of_sale'); ?><span class="md-close"></span></h1>
		<div class="media-frame-wrap">
			<table id="custom_fee_table">
				<thead>
					<tr>
						<th class="fee_name"><?php _e('Fee Name', 'wc_point_of_sale') ?>
						</th>
						<th class="fee_taxable"><?php _e('Taxable', 'wc_point_of_sale') ?>
						</th>
				</thead>
				<tbody>
					<tr>
						<td class="fee_name"><input type="text" id="fee-name">
						</td>
						<td class="fee_taxable"><input type="checkbox" name="taxable" id="taxable-fee"><label for="taxable-fee" class="pos_register_toggle"></label>
						</td>
			</table>
	        <div id="custom_fee_value"></div>
    	</div>
		<div class="wrap-button">
                <button id="add-fee" class="alignright wc_points_rewards_apply_fee"
                type="button"><?php _e('Add Fee', 'wc_point_of_sale'); ?></button>
        </div>
    </div>
</div>