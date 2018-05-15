<div class="md-modal md-dynamicmodal md-close-by-overlay" data-action="" id="modal-cash_management">
    <div class="md-content woocommerce">
        <h1><span class="title"></span> <span class="md-close"></span></h1>
	    <div class="cash-content">    
	        <p id="cash_management_details" class="form-row form-row-wide col3-set">
		        <label for="amount"><?php _e('Amount', 'wc_point_of_sale'); ?></label>
	            <input type="text" name="amount" onkeyup="amount_validation(this);" onchange="to_float(this);" placeholder="<?php _e('Amount e.g, 50.00', 'wc_point_of_sale') ?>">
	        </p>
	        <p class="form-row form-row-wide">
		        <label for="note"><?php _e('Note', 'wc_point_of_sale'); ?></label>
	            <input type="text" name="note" placeholder="<?php _e('Type to add a note', 'wc_point_of_sale') ?>">
	        </p>
	    </div>
        <div class="wrap-button">
            <button class="button button-primary wp-button-large alignright" type="button" id="add-cash-action" disabled>
                <?php _e('Save Customer', 'wc_point_of_sale'); ?>
            </button>
        </div>
    </div>
</div>
<div class="md-overlay"></div>
<div class="md-overlay-prompt"></div>