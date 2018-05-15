<?php include_once( dirname(__FILE__).'/common_header.php' ); ?>

<style type="text/css">

	td.column-price, 
	td.column-fees {
		/*text-align: right;*/
	}
	th.column-listing_title {
		width: 33%;
	}
	th.column-quantity,
	th.column-fba_inv_age_90,
	th.column-fba_inv_age_180,
	th.column-fba_inv_age_270,
	th.column-fba_inv_age_365,
	th.column-fba_inv_age_365_plus,
	th.column-fba_fee_ltsf_12,
	th.column-lowest_price,
	th.column-buybox_price,
	th.column-loffer_price,
	th.column-compet_price,
	th.column-min_price,
	th.column-max_price,
	th.column-sale_price,
	th.column-ebay_price,
	th.column-msrp,
	th.column-price {
		width: 8%;
	}
	th.column-status,
	th.column-sku {
		width: 12%;
	}

	th.column-min_price,
	td.column-min_price {
		border-left: 1px solid #eee;
	}
	th.column-max_price,
	td.column-max_price {
		border-right: 1px solid #eee;
	}

	th.column-img {
		width: 100px;
	}
	td.column-img img {
		max-width: 100px;
		max-height: 90px;
		width: auto !important;
		height: auto !important;
	}
	
	td.column-listing_title a.product_title_link {
		color: #555;
	}
	td.column-listing_title a.product_title_link:hover {
		/*color: #21759B;*/
		color: #D54E21;
	}

	td.column-listing_title a.missing_product_title_link {
		color: #D54E21;
	}

	.tablenav .actions a.wpl_job_button {
		display: inline-block;
		margin: 0;
		margin-top: 1px;
		margin-right: 5px;
	}

	#TB_window table.variations_table {
		width: 99%
	}
	#TB_window table.variations_table th {
		border-bottom: 1px solid #aaa;
		padding: 4px 9px;
	}
	#TB_window table.variations_table td {
		border-bottom: 1px solid #ccc;
		padding: 4px 9px;
	}

</style>

<div class="wrap">
	<div class="icon32" style="background: url(<?php echo $wpl_plugin_url; ?>img/amazon-32x32.png) no-repeat;" id="wpl-icon"><br /></div>
	<!-- <h2><?php echo __('Repricing Tool','wpla') ?></h2> -->

	<?php include_once( dirname(__FILE__).'/tools_tabs.php' ); ?>
	<?php echo $wpl_message ?>

	<!-- show listings table -->
	<?php $wpl_listingsTable->views(); ?>
    <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
    <form id="listings-filter" method="get" action="<?php echo $wpl_form_action; ?>" >
        <!-- For plugins, we also need to ensure that the form posts back to our current page -->
        <?php wp_nonce_field( 'bulk-listings' ); ?>
        <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ) ?>" />
        <input type="hidden" name="repricing_status" value="<?php echo isset($_REQUEST['repricing_status']) ? $_REQUEST['repricing_status'] : ''; ?>" />
        <input type="hidden" name="buybox_status"    value="<?php echo isset($_REQUEST['buybox_status'])    ? $_REQUEST['buybox_status']    : ''; ?>" />
        <input type="hidden" name="stock_status" 	 value="<?php echo isset($_REQUEST['stock_status'])     ? $_REQUEST['stock_status']     : ''; ?>" />
        <input type="hidden" name="fba_status" 		 value="<?php echo isset($_REQUEST['fba_status'])       ? $_REQUEST['fba_status']       : ''; ?>" />
        <input type="hidden" name="fba_age" 		 value="<?php echo isset($_REQUEST['fba_age'])          ? $_REQUEST['fba_age']          : ''; ?>" />
        <!-- Now we can render the completed list table -->
		<?php $wpl_listingsTable->search_box( __('Search','wpla'), 'listing-search-input' ); ?>
        <?php $wpl_listingsTable->display() ?>
    </form>
	<br style="clear:both;"/>

	<h4>Instructions</h4>
	<p>
		The repricing tool allows you to automatically adjust your product prices to the currently lowest prices on Amazon.
	</p>
	<p>
		The button below will automatically update all products which have a minimum price set and where the product price is currently higher than the lowest price on Amazon.
		It will only update the Custom Amazon Price field - the prices on your website will not be affected.
	</p>

	<form method="post" action="<?php echo $wpl_form_action; ?>">
		<div class="submit" style="padding-top: 0; float: left; padding-left:0;">

	        <input type="hidden" name="s"                value="<?php echo isset($_REQUEST['s']) ? $_REQUEST['s'] : ''; ?>" />
	        <input type="hidden" name="repricing_status" value="<?php echo isset($_REQUEST['repricing_status']) ? $_REQUEST['repricing_status'] : ''; ?>" />
    	    <input type="hidden" name="buybox_status"    value="<?php echo isset($_REQUEST['buybox_status'])    ? $_REQUEST['buybox_status']    : ''; ?>" />
	        <input type="hidden" name="stock_status" 	 value="<?php echo isset($_REQUEST['stock_status'])     ? $_REQUEST['stock_status']     : ''; ?>" />
	        <input type="hidden" name="fba_status" 		 value="<?php echo isset($_REQUEST['fba_status'])       ? $_REQUEST['fba_status']       : ''; ?>" />
	        <input type="hidden" name="fba_age" 		 value="<?php echo isset($_REQUEST['fba_age'])          ? $_REQUEST['fba_age']          : ''; ?>" />
			<input type="hidden" name="action"           value="wpla_apply_lowest_price_to_all_items" />
            <?php wp_nonce_field( 'wpla_apply_lowest_price_to_all_items' ); ?>
			<input type="submit" value="<?php echo __('Apply lowest price to all items','wpla') ?>" name="submit" class="button-secondary" >
		</div>
	</form>

	<p style="clear:both;">
		If there have been any changes, a new Price And Quantity Update feed will be generated and scheduled for submission. 
	</p>
	<p>
		Please make sure you test the repricing tool manually before you enable the automatic repricing option in advanced settings.
	</p>

	<form method="post" action="<?php echo $wpl_form_action; ?>">
		<div class="submit" style="padding-top: 0; float: left; padding-left:0;">
			<?php wp_nonce_field( 'wpla_resubmit_all_failed_prices' ); ?>
	        <input type="hidden" name="s"                value="<?php echo isset($_REQUEST['s']) ? $_REQUEST['s'] : ''; ?>" />
	        <input type="hidden" name="repricing_status" value="<?php echo isset($_REQUEST['repricing_status']) ? $_REQUEST['repricing_status'] : ''; ?>" />
	        <input type="hidden" name="buybox_status"    value="<?php echo isset($_REQUEST['buybox_status'])    ? $_REQUEST['buybox_status']    : ''; ?>" />
	        <input type="hidden" name="stock_status" 	 value="<?php echo isset($_REQUEST['stock_status'])     ? $_REQUEST['stock_status']     : ''; ?>" />
	        <input type="hidden" name="fba_status" 		 value="<?php echo isset($_REQUEST['fba_status'])       ? $_REQUEST['fba_status']       : ''; ?>" />
	        <input type="hidden" name="fba_age" 		 value="<?php echo isset($_REQUEST['fba_age'])          ? $_REQUEST['fba_age']          : ''; ?>" />
			<input type="hidden" name="action"           value="wpla_resubmit_all_failed_prices" />
			<input type="submit" value="<?php echo __('Resubmit all failed prices','wpla') ?>" name="submit" class="button-secondary" >
		</div>
	</form>


	<script type="text/javascript">
		jQuery( document ).ready( function () {
		
			// apply lowest price link
			jQuery('#the-list .edit_price input').on('change', function() {
				var current_field = jQuery(this);
				var listing_id    = jQuery(this).data('id');
				var column        = jQuery(this).data('col');
				var value         = jQuery(this).val();
				console.log('id',listing_id);
				console.log('col',column);
				console.log('val',value);

                // prepare request
                var params = {
					action: 'wpla_update_price_column',
					listing_id: listing_id,
					column: column,
					value: value,
                    nonce: 'TODO'
                };

                current_field.addClass('disabled');
                var jqxhr = jQuery.getJSON( ajaxurl, params )
                .success( function( response ) { 

                    current_field.removeClass('disabled');

                    if ( response.success ) {

                        // current_field.highlight();

                    } else {
                        current_field.after( 'ERROR: ' + response );
                    }

                })
                .error( function(e,xhr,error) { 
                    current_field.removeClass('disabled');
                    console.log( 'error', xhr, error ); 
                    console.log( e.responseText ); 
                    current_field.after('Server Error: ' + e.responseText );
                });

			}) // on price field change

			// disable Enter key in price fields
			jQuery('#the-list .edit_price input').keypress(function(event) { return event.keyCode != 13; });


			// toggle edit price fields
			jQuery('#btn_toggle_price_editor').on('click', function() {

				jQuery(this).toggleClass('button-primary');
				jQuery('#the-list .display_price').toggle();				
				jQuery('#the-list .edit_price').toggle();				

				return false;
			}) 





			// handle bulk actions click
			jQuery(".tablenav .actions input[type='submit'].action").on('click', function() {
				
				if ( 'doaction'  == this.id ) var selected_action = jQuery("select[name='action']").first().val();
				if ( 'doaction2' == this.id ) var selected_action = jQuery("select[name='action2']").first().val();

				// create array of selected listing IDs
				var item_ids = [];
				var checked_items = jQuery(".check-column input:checked[name='listing[]']");
				checked_items.each( function(index, checkbox) {
					 item_ids.push( checkbox.value );
				});

				// check if any items were selected
				if ( item_ids.length > 0 ) {
					var params = {
						'item_ids': item_ids
					}

					if ( 'minmax_price_wiz' == selected_action ) {

						// set extra parameters for ajax URL - to return user to the same page/view
						var ajaxurl_extra_params = '';
						ajaxurl_extra_params += '&s='                + "<?php echo isset($_REQUEST['s'])                ? urlencode( $_REQUEST['s'] )   : ''; ?>";
						ajaxurl_extra_params += '&repricing_status=' + "<?php echo isset($_REQUEST['repricing_status']) ? $_REQUEST['repricing_status'] : ''; ?>";
						ajaxurl_extra_params += '&buybox_status='    + "<?php echo isset($_REQUEST['buybox_status'])    ? $_REQUEST['buybox_status']    : ''; ?>";
						ajaxurl_extra_params += '&stock_status='     + "<?php echo isset($_REQUEST['stock_status'])     ? $_REQUEST['stock_status']     : ''; ?>";
						ajaxurl_extra_params += '&fba_status='       + "<?php echo isset($_REQUEST['fba_status'])       ? $_REQUEST['fba_status']       : ''; ?>";
						ajaxurl_extra_params += '&fba_age='          + "<?php echo isset($_REQUEST['fba_age'])          ? $_REQUEST['fba_age']          : ''; ?>";

						// load MinMax wizard
				        var sep   = ajaxurl.indexOf('?') > 0 ? '&' : '?'; // fix for ajaxurl altered by WPML: /wp-admin/admin-ajax.php?lang=en
				        var tbURL = ajaxurl + sep + "action=wpla_show_minmax_price_wizard&item_ids=" + item_ids.join(',') + ajaxurl_extra_params + "&width=640&height=420"; 
				        tb_show("Set minimum and maximum prices for "+item_ids.length+" product(s)", tbURL);

						return false;
					}

				}

				if ( 'minmax_price_wiz' == selected_action ) {
					alert('Please select the products you want to update.');
					return false;
				}

				return true;
			})




			// init tooltips
			jQuery(".wide_error_tip").tipTip({
		    	'attribute' : 'data-tip',
		    	'maxWidth' : '100%',
		    	'fadeIn' : 50,
		    	'fadeOut' : 50,
		    	'delay' : 200
		    });

		});
	</script>

</div>