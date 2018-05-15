<?php

// echo "<pre>";print_r($wpl_fields);echo"</pre>";#die();
// echo "<pre>";print_r($wpl_values);echo"</pre>";die();
// echo "<pre>";print_r($wpl_profile_field_data);echo"</pre>";die();
// echo "<pre>";print_r($wpl_product_attributes);echo"</pre>";die();

$current_group = '';

$profile_editor_mode = get_option('wpla_profile_editor_mode','default');
$is_expert_mode      = $profile_editor_mode == 'expert' ? true : false;

?>

<div>
	<input type="text" id="_wpla_tpl_col_filter" placeholder="Search..." onchange="wpla_update_filter();" />
	&nbsp;
	<input type="checkbox" id="_wpla_tpl_col_only_required" onchange="wpla_update_filter();" />
	<label for="_wpla_tpl_col_only_required"><?php echo __('show only required fields','wpla') ?></label>
	&nbsp;
	<input type="checkbox" id="_wpla_tpl_col_and_preferred" onchange="wpla_update_filter();" />
	<label for="_wpla_tpl_col_and_preferred"><?php echo __('and preferred fields','wpla') ?></label>
	&nbsp;
	<input type="checkbox" id="_wpla_tpl_col_hide_empty" onchange="wpla_update_filter();" />
	<label for="_wpla_tpl_col_hide_empty"><?php echo __('hide empty fields','wpla') ?></label>
</div>


<table id="feed-template-data" style="clear:both;">

	<?php foreach ( $wpl_fields as $field ) : ?>

		<?php if ( $field['group'] != $current_group && ! empty( $field['group'] ) ) : ?>
			<?php
				// skip specific groups that are handled internally - like Image or Variations
				// if ( in_array( $field['group_id'], array('Image','Variation', 'Bild','Variations', 'Immagine','Varianti' ) ) )
				if ( in_array( $field['group_id'], array('Image','Images','Bild','Immagine') )  &&  ! $is_expert_mode )
					continue;

				// process group
				$current_group = $field['group'];
				$group_description = str_replace( $field['group_id'].' - ', '', $current_group );
				$group_title = $field['group_id'] == 'Ungrouped' ? '' : $field['group_id'];
			?>
		
			<tr class="wpla_tpl_section_header">
				<th colspan="3">
					<h4><?php echo $group_title ?></h4>
					<small><?php echo $group_description ?></small>
				</th>
			</tr>

			<!--
			<tr>
				<th width="40%">Field</th>
				<th width="45%">Value</th>
				<th width="15%">&nbsp;</th>
			</tr>
			-->

		<?php endif; ?>

		<?php
			// skip specific fields that are handled internally - like update_delete
			$internal_fields = array(
				// 'external_product_id_type',
				// 'external_product_id',

				// category feeds
				'update_delete',
				'item_sku',
				'quantity',
				'parent_child',
				'parent_sku',
				'relationship_type',
				'main_image_url',
				'other_image_url1',
				'other_image_url2',
				'other_image_url3',
				'other_image_url4',
				'other_image_url5',
				'other_image_url6',
				'other_image_url7',
				'other_image_url8',

				// ListingLoader
				'sku',
				'operation-type',
				'main-offer-image',
				'offer-image1',
				'offer-image2',
				'offer-image3',
				'offer-image4',
				'offer-image5',
				'ASIN-hint', // TODO

				// BookLoader
				'main_offer_image',
				'offer_image1',
				'offer_image2',
				'offer_image3',
				'offer_image4',
				'offer_image5',
			);

			// display quantity and image fields in expert mode
			if ( $is_expert_mode ) {
				$internal_fields = array(
					// category feeds
					'update_delete',
					'item_sku',
					// 'quantity',
					'parent_child',
					'parent_sku',
					'relationship_type',

					// ListingLoader
					'sku',
					'operation-type',
					// 'main-offer-image',
					'ASIN-hint',
				);
			}

			if ( in_array( $field['field'], $internal_fields ) )
				continue;

			// quantity and main image are not required - not even in expert mode
			if ( $field['field'] == 'quantity' ) 				$field['required'] = 'Optional';
			if ( $field['field'] == 'main_image_url' ) 			$field['required'] = 'Optional';

			// fulfillment_center_id is not required, even though some feed templates might say so (like Auto Accessory UK)
			if ( $field['field'] == 'fulfillment_center_id' ) 	$field['required'] = 'Optional';

			// merchant_shipping_group_name is not required, even though some feed templates might say so (like Clothing UK)
			if ( $field['field'] == 'merchant_shipping_group_name' ) 	$field['required'] = 'Optional';
			// merchant_shipping_group_name should be a text field, even though some feed templates provide a list of values (like Clothing UK)
			if ( $field['field'] == 'merchant_shipping_group_name' ) 	unset( $wpl_values['merchant_shipping_group_name'] );

			// if account is registered brand, external_product_id is not required
			if ( $wpl_is_reg_brand ) {
				if ( in_array( $field['field'], array('external_product_id','external_product_id_type') ) ) {
					$field['required'] = '';
				}
			}

			$is_preferred         = in_array( $field['required'], array('Preferred','Empfohlen','Facoltativo','Recomendado') ); // TODO: add FR
			$is_maybe_required    = in_array( $field['required'], array('Required','Erforderlich','Obbligatorio','Obligatoire','Obligatorio') );
			$is_actually_required = $field['group_id'] == 'Ungrouped' ? false : $is_maybe_required; // required fields in "Ungrouped" are not actually required... (Shoes tpl)
			$row_class1           = $is_preferred         ? 'wpla_preferred_row' : 'wpla_optional_row';
			$row_class1           = $is_maybe_required    ? 'wpla_required_row'  : $row_class1;
			$row_class2           = $is_actually_required ? 'wpla_actually_required_row' : '';
		?>

		<tr id="wpla_tpl_row_<?php echo $field['field'] ?>" class="wpla_tpl_row <?php echo $row_class1 .' '. $row_class2 ?>">
			<td width="40%">
				<span class="wpla_field_label"><?php echo $field['label'] ?></span>
				<?php $field_definition = str_replace( "<br />\n<br />\n", '<br />', nl2br($field['definition']) ) ?>
                <?php wpla_tooltip( '<b>Accepted Values</b><br>' . nl2br($field['accepted']) ) ?>
                <?php wpla_tooltip( '<b>Definition</b><br><i>' . $field['field'] . '</i><br>' . $field_definition ) ?>
			</td>
			<td width="50%">

				<?php if ( isset( $wpl_values[ $field['field'] ] ) ) : ?>

					<!-- select from a list of values -->
					<select	name="tpl_col_<?php echo $field['field'] ?>" id="tpl_col_<?php echo $field['field'] ?>" >

						<optgroup label="<?php echo 'Select from Allowed Values' ?>">
							<option value="">&mdash; none &mdash;</option>
							<?php foreach ( explode( '|', $wpl_values[ $field['field'] ]['values'] ) as $value ) : ?>
								<option value="<?php echo $value ?>" 
									<?php if ( isset($wpl_profile_field_data[ $field['field'] ]) && $wpl_profile_field_data[ $field['field'] ] == $value ) : ?>
										selected="selected"
									<?php endif; ?>
									><?php 
										if ( in_array( $field['field'], array('feed_product_type','condition_type') ) ) {
											echo wpla_spacify( $value );
										} else {
											echo $value;
										}
									?></option>
							<?php endforeach; ?>
						</optgroup>

						<optgroup label="<?php echo 'Pull value from Product Attribute' ?>">
							<?php foreach ( $wpl_product_attributes as $attribute ) : ?>
								<?php $value = '[' . str_replace('pa_', 'attribute_', $attribute->name ) . ']' ?>
								<option value="<?php echo $value ?>" 
									<?php if ( isset($wpl_profile_field_data[ $field['field'] ]) && $wpl_profile_field_data[ $field['field'] ] == $value ) : ?>
										selected="selected"
									<?php endif; ?>
									><?php echo $attribute->label; ?></option>
							<?php endforeach; ?>
						</optgroup>

						<optgroup label="<?php echo 'Custom Values ' ?>">
							<?php
								$wpl_other_shortcodes = array(
									'[---]' => '-- leave empty --',
								);

								// handle custom shortcodes registered by wpla_register_profile_shortcode()
								foreach (WPLA()->getShortcodes() as $key => $custom_shortcode) {
									$wpl_other_shortcodes[ "[$key]" ] = $custom_shortcode['title'];
								}
							
								// handle custom variation meta fields
								$variation_meta_fields = get_option('wpla_variation_meta_fields', array() );
								foreach ( $variation_meta_fields as $key => $varmeta ) {
									$key = 'meta_'.$key;
									$wpl_other_shortcodes[ "[$key]" ] = $varmeta['label'];
								}
							?>
							<?php foreach ( $wpl_other_shortcodes as $value => $label ) : ?>
								<option value="<?php echo $value ?>" 
									<?php if ( isset($wpl_profile_field_data[ $field['field'] ]) && $wpl_profile_field_data[ $field['field'] ] == $value ) : ?>
										selected="selected"
									<?php endif; ?>
									><?php echo $label; ?></option>
							<?php endforeach; ?>
						</optgroup>
					</select>

				<?php else : ?>
					
					<!-- custom text field -->
					<input type="text" 
						name="tpl_col_<?php echo $field['field'] ?>" 
						id="tpl_col_<?php echo $field['field'] ?>" 
						value="<?php echo isset($wpl_profile_field_data[ $field['field'] ]) ? $wpl_profile_field_data[ $field['field'] ] : '' ?>" 
						placeholder="<?php echo $field['example'] ?>" 
					/>					

					<a href="#" onclick="wpla_select_shortcode('<?php echo $field['field'] ?>');return false;" title="Select attribute">
						<img class="browse_shortcodes" data-tip="" src="<?php echo WPLA_URL ?>/img/search2.png" height="16" width="16" />
					</a>

				<?php endif; ?>
                <?php // wpla_tooltip( $field['accepted'] ) ?>

			</td>
			<td width="10%" class="col_required">
				<?php // echo $field['required'] ?>
				<?php 
					switch ( $field['required'] ) {
						case 'Required':
						case 'Erforderlich':	// DE
						case 'Obbligatorio':	// IT
						case 'Obligatoire':		// FR
						case 'Obligatorio':		// ES
							echo '<b>'.$field['required'].'</b>';
							break;
						
						case 'Optional':
						case 'Consigliato':		// IT
						case 'Optionnel':		// FR
						case 'Opcional':		// ES
							echo '<span style="color:silver">Optional</span>';
							break;
						
						default:
							echo $field['required'];
							break;
					}

				?>
			</td>
		</tr>
	<?php endforeach; ?>

</table>

<style>
	#feed-template-data th {
		text-align: left;
	}
	#feed-template-data th h4 {
		margin-bottom: 0;
	}
	#feed-template-data input,
	#feed-template-data select {
		width:90%;
	}
</style>

<!-- hidden ajax categories tree -->
<div id="wpla_shortcode_selection_wrapper" style="display:none">
	<?php include('select_shortcode.php'); ?>
</div>

<script>

	var current_field;
	var do_replace;
	var prefer_keyword;

	// open shortcode selector
	function wpla_select_shortcode( fieldname ) {
		current_field  = fieldname;
		do_replace     = false;
		prefer_keyword = false;

		// item_type has a special selector
		if ( fieldname == 'item_type')					// default BTG field
			return wpla_select_from_btg( fieldname );
		if ( fieldname == 'recommended_browse_nodes') 	// used by clothing feed template
			return wpla_select_from_btg( fieldname );
		if ( fieldname == 'recommended_browse_nodes1') 	// used by lighting feed template
			return wpla_select_from_btg( fieldname );
		if ( fieldname == 'recommended_browse_nodes2') 	// used by lighting feed template
			return wpla_select_from_btg( fieldname );

		var tbHeight = tb_getPageSize()[1] - 120;
		var tbURL = "#TB_inline?height="+tbHeight+"&width=640&inlineId=wpla_shortcode_selection_wrapper"; 
		tb_show("Select an attribute", tbURL);  

	}

	// insert selected shortcode
	function wpla_insert_shortcode( shortcode ) {
		// var do_replace = jQuery.inArray( current_field, ['item_type','recommended_browse_nodes'] ) ? true : false; // upside down (?)
		// var do_replace = ( ( current_field == 'item_type' ) || ( current_field == 'recommended_browse_nodes' ) ) ? true : false;	
		var inputField = jQuery('#tpl_col_'+current_field);
		if ( do_replace ) {
			inputField.val( shortcode ); // replace
		} else {
			inputField.val( inputField.val() + shortcode ); // append
		}
		tb_remove();
	}

	// insert selected browse node id / keyword
	function wpla_insert_selected_browse_node( node_id ) {
		var inputField = jQuery('#tpl_col_'+current_field);

		// item_type column should use keyword instead of browse node id
		if ( prefer_keyword ) {
			var keyword = node_id = jQuery('#wpla_node_id_'+node_id).data('keyword');
			console.log('keyword: ',keyword);
			if ( keyword ) node_id = keyword;
		}

		if ( do_replace ) {
			inputField.val( node_id ); // replace
		} else {
			inputField.val( inputField.val() + node_id ); // append
		}
		tb_remove();
	}

	// open browse tree selector
	function wpla_select_from_btg( fieldname ) {
		current_field = fieldname;
		do_replace = true;

		// item_type column should use keyword instead of browse node id
		if ( fieldname == 'item_type')
			prefer_keyword = true;

		var tbHeight = tb_getPageSize()[1] - 120;
		var tbURL = "#TB_inline?height="+tbHeight+"&width=500&inlineId=amazon_categories_tree_wrapper"; 
		tb_show("Select a category", tbURL);  

	}

	// disable Enter key in filter field
	jQuery('#_wpla_tpl_col_filter').keypress(function(event) { 
		wpla_update_filter();
		return event.keyCode != 13; 
	});

	// handle field filter changes
	function wpla_update_filter() {

		var only_required = jQuery('#_wpla_tpl_col_only_required').attr('checked');
		var and_preferred = jQuery('#_wpla_tpl_col_and_preferred').attr('checked');
		var hide_empty    = jQuery('#_wpla_tpl_col_hide_empty'   ).attr('checked');

		// auto tick required checkbox when preferred checkbox is ticked
		if ( ! only_required && and_preferred ) {
			// jQuery('#_wpla_tpl_col_only_required').attr('checked','checked');
			jQuery('#_wpla_tpl_col_only_required').click();
			return;
		}

		if ( ! only_required && ! and_preferred ) {
			jQuery('.wpla_optional_row').show();
			jQuery('.wpla_preferred_row').show();
		} else if ( ! and_preferred ) {
			jQuery('.wpla_optional_row').show();
		}

		var query = jQuery('#_wpla_tpl_col_filter').val();
		if ( query ) {
			jQuery('.wpla_tpl_row').each( function( index ){

				// check for query match
				if ( this.id.match( query ) ) {
					jQuery(this).show();
				} else if ( jQuery(this).find('span.wpla_field_label').first().html().match( new RegExp(query, "i") ) ) {
					jQuery(this).show();
				} else {
					jQuery(this).hide();				
				}

			});		
			jQuery('.wpla_tpl_section_header').hide();			
		} else {
			jQuery('.wpla_tpl_row').show();			
			jQuery('.wpla_tpl_section_header').show();			
		}

		if ( only_required && and_preferred ) {
			jQuery('.wpla_optional_row').hide();
		} else if ( only_required ) {
			jQuery('.wpla_optional_row').hide();
			jQuery('.wpla_preferred_row').hide();
		}

		if ( hide_empty ) {
			jQuery('.wpla_tpl_row').each( function(index, value){
				var input_field  = jQuery(this).find('input').first();
				var select_field = jQuery(this).find('select').first();
				if ( ! input_field.val() && ! select_field.val() ) {
					jQuery(this).hide();
				}
			});
		}


	} // wpla_update_filter()



</script>
