<?php include_once( dirname(__FILE__).'/common_header.php' ); ?>

<style type="text/css">

	td.column-price, 
	td.column-fees {
		/*text-align: right;*/
	}
	th.column-listing_title {
		width: 33%;
	}
	th.column-status,
	th.column-quantity,
	th.column-lowest_price,
	th.column-buybox_price,
	th.column-loffer_price,
	th.column-price {
		width: 8%;
	}
	th.column-date_published,
	th.column-account {
		width: 10%;
	}
	th.column-profile,
	th.column-sku {
		width: 12%;
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
	<h2><?php echo __('Listings','wpla') ?></h2>
	<?php echo $wpl_message ?>

	<!-- show listings table -->
	<?php $wpl_listingsTable->views(); ?>
    <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
    <form id="listings-filter" method="get" action="<?php echo $wpl_form_action; ?>" >
        <!-- For plugins, we also need to ensure that the form posts back to our current page -->
        <?php wp_nonce_field( 'bulk-listings' ); ?>
        <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ) ?>" />
        <input type="hidden" name="listing_status" value="<?php echo isset($_REQUEST['listing_status']) ? $_REQUEST['listing_status'] : ''; ?>" />
        <!-- Now we can render the completed list table -->
		<?php $wpl_listingsTable->search_box( __('Search','wpla'), 'listing-search-input' ); ?>
        <?php $wpl_listingsTable->display() ?>
    </form>

	<br style="clear:both;"/>


	<div class="submit" style="display:none">

		<a id="btn_update_all_published_items" class="btn_update_all_published_items button-secondary wpl_job_button"
		   title="<?php echo __('Update all published items from Amazon.','wpla') ?>"
			><?php echo __('Update all published items','wpla'); ?></a>

	</div>

	<form method="post" action="<?php echo $wpl_form_action; ?>">
		<div class="submit" style="padding-top: 0; float: left; padding-left:0;">
			<?php wp_nonce_field( 'wpla_listings_tools' ); ?>
			<?php if ( isset($_REQUEST['listing_status']) && ( $_REQUEST['listing_status'] == 'imported' ) ) : ?>
				<input type="hidden" name="action" value="wpla_clear_import_queue" />
				<input type="submit" value="<?php echo __('Clear import queue','wpla') ?>" name="submit" class="button-secondary" >
			<?php else : ?>
				<input type="hidden" name="action" value="wpla_resubmit_all_failed" />
				<input type="submit" value="<?php echo __('Resubmit all failed items','wpla') ?>" name="submit" class="button-secondary" >
			<?php endif; ?>
		</div>
	</form>

	<script type="text/javascript">
		jQuery( document ).ready( function () {
		
			// ask again before ending items
			jQuery('.row-actions .end_item a').on('click', function() {
				return confirm("<?php echo __('Are you sure you want to end this listing?.','wpla') ?>");
			})

			// ask again before relisting items
			jQuery('.row-actions .relist a').on('click', function() {
				return confirm("<?php echo __('Are you sure you want to relist this ended listing?.','wpla') ?>");
			})

			// ask again before deleting items
			jQuery('.row-actions .delete a').on('click', function() {
				return confirm("<?php echo __('Are you sure you want to remove this listing?.','wpla') ?>");
			})
			jQuery('#wpl_dupe_details a.delete').on('click', function() {
				return confirm("<?php echo __('Are you sure you want to remove this listing?.','wpla') ?>");
			})

			// apply lowest price link
			jQuery('#the-list .column-lowest_price a').on('click', function() {
				var listing_id = jQuery(this).data('id');
				console.log(listing_id);

				var tb_url = 'admin-ajax.php?action=wpla_use_lowest_price&id='+listing_id+'&width=640&height=220';
				tb_show('Apply lowest price to product', tb_url );

				return false;
			})




			// handle bulk actions click
			jQuery(".tablenav .actions input[type='submit'].action").on('click', function() {
				
				if ( 'doaction'  == this.id ) var selected_action = jQuery("select[name='action']").first().val();
				if ( 'doaction2' == this.id ) var selected_action = jQuery("select[name='action2']").first().val();

				// console.log( this.id );
				// console.log('action',selected_action);

				// if ( selected_action == 'delete_listing' ) {
				// 	var confirmed = confirm("<?php echo __('Are you sure you want to do this?','wpla') .' '.  __('You should not delete listings which have been recently published on eBay!','wpla') ?>");
				// 	if ( ! confirmed ) return false;
				// }

				if ( selected_action == 'wpla_fetch_pdescription' ) {
					var confirmed = confirm("<?php echo __('Are you sure you want to do this?','wpla') .' '.  __('This will overwrite your current product descriptions in WooCommerce.','wpla') ?>");
					if ( ! confirmed ) return false;
				}

				if ( selected_action == 'wpla_trash_listing' ) {
					var confirmed = confirm("<?php echo __('Are you sure you want to do this?','wpla') .' '.  __('Removing the listing also removes the sales history for the item. If you were to relist these listings later you would then start out with a lower sales rank.','wpla') ?>");
					if ( ! confirmed ) return false;
				}

				// create array of selected listing IDs
				var item_ids = [];
				var checked_items = jQuery(".check-column input:checked[name='listing[]']");
				checked_items.each( function(index, checkbox) {
					 item_ids.push( checkbox.value );
					 // console.log( 'checked listing ID', checkbox.value );
				});
				// console.log( item_ids );

				// check if any items were selected
				if ( item_ids.length > 0 ) {
					var params = {
						'item_ids': item_ids
					}

					if ( 'wpla_fetch_pdescription' == selected_action ) {
						WPLA.JobRunner.runJob( 'fetchProductDescription', 'Updating product descriptions from Amazon...', params );
						return false;
					}

				}

				return true;

			})


			// adjust width of extra table rows (inline errors and warnings)
			var column_count = jQuery('#the-list tr:first td').length;
			jQuery('#the-list .wpla_auto_width_column').attr( 'colspan', column_count - 2 ); // 1 col left and right padding

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