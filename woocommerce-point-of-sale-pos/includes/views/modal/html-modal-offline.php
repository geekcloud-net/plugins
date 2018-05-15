<div class="md-modal md-dynamicmodal md-message" id="modal-lost-connection">
    <div class="md-content">
        <div>
	        <div class="md-message-icon">
        		<span class="dashicons dashicons-admin-plugins"></span>
			</div>
		    <p><?php _e( "Connection interrupted. Attempting to restore connection", 'wc_point_of_sale'); ?>
		    	<span class="dot-one">.</span>
		    	<span class="dot-two">.</span>
		    	<span class="dot-three">.</span>
	    	</p>
        </div>
    </div>
</div>
<div class="md-modal md-dynamicmodal md-message" id="modal-reconnected-successfuly">
    <div class="md-content">
        <div>
            <div class="md-message-icon">
        		<span class="dashicons dashicons-cloud"></span>
			</div>
		    <p><?php _e( "Connected", 'wc_point_of_sale'); ?></p>
	        <button class="button button-primary md-close" type="button" >
	            <?php _e('Continue', 'wc_point_of_sale'); ?>
	        </button>
        </div>
    </div>
</div>