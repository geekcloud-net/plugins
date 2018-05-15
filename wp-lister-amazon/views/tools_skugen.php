<?php include_once( dirname(__FILE__).'/common_header.php' ); ?>

<style type="text/css">

	td.column-price, 
	td.column-fees {
		/*text-align: right;*/
	}
	th.column-title {
		width: 33%;
	}
	th.column-status,
	th.column-sku, {
	th.column-sku_preview {
		width: 12%;
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

</style>

<div class="wrap">
	<div class="icon32" style="background: url(<?php echo $wpl_plugin_url; ?>img/amazon-32x32.png) no-repeat;" id="wpl-icon"><br /></div>
	<!-- <h2><?php echo __('Generate missing SKUs','wpla') ?></h2> -->

	<?php include_once( dirname(__FILE__).'/tools_tabs.php' ); ?>
	<?php echo $wpl_message ?>

	<!-- show listings table -->
	<?php $wpl_skugenTable->views(); ?>
    <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
    <form id="listings-filter" method="get" action="<?php echo $wpl_form_action; ?>" >
        <!-- For plugins, we also need to ensure that the form posts back to our current page -->
        <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ) ?>" />
        <input type="hidden" name="sku_status" value="<?php echo isset($_REQUEST['sku_status']) ? $_REQUEST['sku_status'] : ''; ?>" />
        <input type="hidden" name="tab" value="skugen" />
        <!-- Now we can render the completed list table -->
		<?php $wpl_skugenTable->search_box( __('Search','wpla'), 'listing-search-input' ); ?>
        <?php $wpl_skugenTable->display() ?>
    </form>
	<br style="clear:both;"/>


	<form method="post" action="<?php echo $wpl_form_action; ?>">
        <input type="hidden" name="sku_status" value="<?php echo isset($_REQUEST['sku_status']) ? $_REQUEST['sku_status'] : ''; ?>" />
		<div class="submit" style="padding-top: 0; float: left; padding-left:0;">
			<?php #wp_nonce_field( 'wpla_tools_page' ); ?>

			<h3>Options</h3>
			<p>
				Select how you want your SKUs to be generated:
			</p>

			<h4>Simple Products and Parent Variations</h4>
			<select name="wpla_skugen_mode_simple">
				<option value="1" <?php if ( $wpl_skugen_mode_simple == '1' ) echo 'selected' ?> ><?php echo __('First letter of every word','wpla') ?></option>
				<option value="2" <?php if ( $wpl_skugen_mode_simple == '2' ) echo 'selected' ?> ><?php echo __('First two letters of every word','wpla') ?></option>
			</select>
			<p>
				Example: A product title of "T-Shirt - The Big Bang Theory" would become "TSTBBT" using the first letter of every word.
			</p>

			<h4>Product Variations</h4>
			<select name="wpla_skugen_mode_variation">
				<option value="0" <?php if ( $wpl_skugen_mode_variation == '0' ) echo 'selected' ?> ><?php echo __('Append full attribute value','wpla') ?></option>
				<option value="1" <?php if ( $wpl_skugen_mode_variation == '1' ) echo 'selected' ?> ><?php echo __('Append first letter of every attribute','wpla') ?></option>
				<option value="2" <?php if ( $wpl_skugen_mode_variation == '2' ) echo 'selected' ?> ><?php echo __('Append first two letters of every attribute','wpla') ?></option>
				<option value="3" <?php if ( $wpl_skugen_mode_variation == '3' ) echo 'selected' ?> ><?php echo __('Append first three letters of every attribute','wpla') ?></option>
				<option value="9" <?php if ( $wpl_skugen_mode_variation == '9' ) echo 'selected' ?> ><?php echo __('Append variation ID','wpla') ?></option>
			</select>
			<p>
				Example: A green XL shirt with the parent SKU "TS" could become TS-GREEN-XL or TS-GRE-XL or TS-GR-XL or TS-G-X or TS-1234.
			</p>

			<h4>Case Conversion</h4>
			<select name="wpla_skugen_mode_case">
				<option value="0" <?php if ( $wpl_skugen_mode_case == '0' ) echo 'selected' ?> ><?php echo __('No conversion','wpla') ?></option>
				<option value="1" <?php if ( $wpl_skugen_mode_case == '1' ) echo 'selected' ?> ><?php echo __('Convert to upper case','wpla') ?></option>
				<option value="2" <?php if ( $wpl_skugen_mode_case == '2' ) echo 'selected' ?> ><?php echo __('Convert to lower case','wpla') ?></option>
			</select>

			<br style="clear:both;"/>
			<br style="clear:both;"/>
			<input type="hidden" name="action" value="wpla_save_skugen_options" />
            <?php wp_nonce_field( 'wpla_save_skugen_options' ); ?>
			<input type="submit" value="<?php echo __('Save options','wpla') ?>" name="submit" class="button button-primary" >

		</div>
	</form>

	<script type="text/javascript">
		jQuery( document ).ready( function () {

			// ask for confirmation before generating ALL SKUs
			jQuery('#btn_generate_all_missing_skus').on('click', function() {
				return confirm("<?php echo __('Are you sure you want generate SKUs for all products without SKU?','wpla') ?>");
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