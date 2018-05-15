<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="wrap">
    <h2 class="uk-heading-divider wp-heading-inline"><?php echo ( ($action=="add")? __("Add new Template", "woomelly") : sprintf( __( "Edit Template #%s %s %s", "woomelly"), str_pad($template_id, 6, "0", STR_PAD_LEFT), '<a href="'.admin_url( "admin.php?page=woomelly-templatesync&action=add" ).'" class="page-title-action">'.__("Create New", "woomelly").'</a>', '<a href="'.admin_url( "edit.php?post_type=product" ).'" class="page-title-action">'.__("Add Products", "woomelly").'</a>' ) ); ?></h2>
    <br>	
	<div class="wrap-page-templatesync-single" id="wrap-page-templatesync-single">
        <form id="woomelly-form-templatesync" class="uk-form-stacked" form action="admin.php?page=woomelly-templatesync&amp;action=edit" method="post">
            <?php if ( $woomelly_alive) { ?>
                <div class="uk-margin woomelly_name_template_field">
	                <label class="uk-form-label" for="woomelly_name_template_field"><?php echo __("Name of Template", "woomelly") ?> <span class="uk-margin-small-right" uk-icon="icon: question" uk-tooltip="title: <?php echo __('Select a name to your template. By default set the template ID.', 'woomelly'); ?>; pos: right"></span></label>
	                <div class="uk-form-controls">
						<input class="uk-input uk-form-small col-xs-10 col-sm-10 col-md-10 col-lg-10" name="woomelly_name_template_field" id="woomelly_name_template_field" value="<?php echo $woomelly_name_template_field; ?>" type="text">
	            	</div>
	            </div>
                <div class="uk-margin woomelly_category_field">
	                <label class="uk-form-label" for="woomelly_category_field"><?php echo sprintf(__("Category of Mercadolibre %s", "woomelly"), '<span style="color: red;">*</span>'); ?> <span class="uk-margin-small-right" uk-icon="icon: question" uk-tooltip="title: <?php echo __('The leaf category is the last category of the tree and is the only category where you can publish articles.', 'woomelly'); ?>; pos: right"></span></label>
	                <div class="uk-form-controls">
	                    <select class="uk-select uk-form-small col-xs-6 col-sm-6 col-md-10 col-lg-10" name="woomelly_category_field" id="woomelly_category_field">
	                        <?php echo $all_categories; ?>
	                    </select>
	                    <input type="hidden" id="woomelly_category_name_field" name="woomelly_category_name_field" value="<?php echo $woomelly_category_name_field; ?>" />
	                    <button type="button" class="uk-button uk-button-danger uk-button-small col-xs-6 col-sm-6 col-md-2 col-lg-2" id="woomelly_category_field_reset"><?php echo __("Reset", "woomelly"); ?></button>
	                </div>
                </div>
                <div id="woomelly_category_data_detail" style="<?php if ( empty($all_data_category) ) { echo 'display: none;'; } ?>float: left;width: 100%;">
	                <div class="uk-margin woomelly_path_from_root">
	                    <span id="woomelly_path_from_root"><?php echo $path_from_root; ?></span>
	                </div>
					<div class="uk-child-width-1-3" uk-grid>
					    <div>
						    <div class="uk-margin woomelly_buying_mode_field">
						        <label class="uk-form-label" for="woomelly_buying_mode_field"><?php echo sprintf(__("Sales Mode %s", "woomelly"), '<span style="color: red;">*</span>'); ?> <span class="uk-margin-small-right" uk-icon="icon: question" uk-tooltip="title: <?php echo __('Sale mode allowed in this category.', 'woomelly'); ?>; pos: right"></span></label>
						        <div class="uk-form-controls">
						            <select class="uk-select uk-form-small" name="woomelly_buying_mode_field" id="woomelly_buying_mode_field">
									<?php
										if ( !empty($all_data_category) && !empty($all_data_category->settings->buying_modes) ) {
											foreach ( $all_data_category->settings->buying_modes as $value ) {
												echo '<option value="'.$value.'" '.( ($woomelly_buying_mode_field==$value)? "selected=\"selected\"" : "" ).'>'.$value.'</option>';
											}
										}
									?>
						            </select>
						        </div>
						    </div>
					        <div class="uk-margin woomelly_listing_type_id_field">
					            <label class="uk-form-label" for="woomelly_listing_type_id_field"><?php echo sprintf(__("Publication Type %s", "woomelly"), '<span style="color: red;">*</span>'); ?> <span class="uk-margin-small-right" uk-icon="icon: question" uk-tooltip="title: <?php echo __('Mercadolibre suggests publishing in the following way: MLA, MLB, MLC, MLM, MCO (Premium, Classic, Free). MPE, MLV, MLU (Premium, Classical, Free). In other sites you can use any of the listings listed.', 'woomelly'); ?>; pos: right"></span></label>
					            <div class="uk-form-controls">
					                <select class="uk-select uk-form-small" name="woomelly_listing_type_id_field" id="woomelly_listing_type_id_field">
									<?php
										if ( !empty($all_data_category) && !empty($listing_type_id) && isset($listing_type_id->available) && !empty($listing_type_id->available) ) {
											foreach ( $listing_type_id->available as $value ) {
												echo '<option value="'.$value->id.'" '.( ($woomelly_listing_type_id_field==$value->id)? "selected=\"selected\"" : "" ).'>'.$value->name.'</option>';
											}
										}
									?>
					                </select>
					            </div>
					        </div>
					        <div class="uk-margin woomelly_condition_field">
					            <label class="uk-form-label" for="woomelly_condition_field"><?php echo sprintf(__("Condition %s", "woomelly"), '<span style="color: red;">*</span>'); ?> <span class="uk-margin-small-right" uk-icon="icon: question" uk-tooltip="title: <?php echo __('Publication conditions allowed for that category.', 'woomelly'); ?>; pos: right"></span></label>
					            <div class="uk-form-controls">
					                <select class="uk-select uk-form-small" name="woomelly_condition_field" id="woomelly_condition_field">
									<?php
										if ( !empty($all_data_category) && !empty($all_data_category->settings->item_conditions) ) {
											foreach ( $all_data_category->settings->item_conditions as $value ) {
												echo '<option value="'.$value.'" '.( ($woomelly_condition_field==$value)? "selected=\"selected\"" : "" ).'>'.$value.'</option>';
											}
										}
									?>
					                </select>
					            </div>
					        </div>
					        <div class="uk-margin woomelly_accepts_mercadopago_field">
								<?php if ( !empty($all_data_category) ) { ?>
					            	<label class="uk-form-label" for="woomelly_accepts_mercadopago_field"><?php echo __("Accept Mercadopago", "woomelly"); ?></label>        
					            	<input class="checkbox" name="woomelly_accepts_mercadopago_field" id="woomelly_accepts_mercadopago_field" value="1" type="checkbox" <?php echo ( ($woomelly_accepts_mercadopago_field==true)? "checked=\"checked\"" : "" ); ?> />
								<?php } ?>
					        </div>
					        <div class="uk-margin woomelly_shipping_mode_field">
					            <label class="uk-form-label" for="woomelly_shipping_mode_field"><?php echo sprintf(__("Shipping Mode %s", "woomelly"), '<span style="color: red;">*</span>' ); ?> <span class="uk-margin-small-right" uk-icon="icon: question" uk-tooltip="title: <?php echo __('not_specified: It means that the seller did not specify any shipping price for their items and the buyer must contact the seller to arrange a shipping option and the purchase price. custom: Sellers can include a table with up to 10 shipping costs in one item and the buyer must deliver that number at the end of the process and leave [checkout]. me1 (MercadoEnvios mode 1): This method offers a shipping calculator to calculate the shipping cost of each order and allow the seller to select the shipping service of their choice, but choosing a carrier. The seller is responsible for managing the tracking number. me2 (MercadoEnvios mode 2): This method offers the seller a prepaid label and a numerical tracking code with a predefined local carrier in each country. The seller does not have to worry about choosing a carrier or handling the tracking number. It is the most recommended mode because it offers an excellent experience for both buyers and sellers. ML chooses the transport company.', 'woomelly'); ?>; pos: right"></span></label>
					            <div class="uk-form-controls">
					                <select class="uk-select uk-form-small" name="woomelly_shipping_mode_field" id="woomelly_shipping_mode_field">
									<?php
										if ( !empty($all_data_category) && !empty($shipping_modes_available) ) {
											foreach ( $shipping_modes_available as $value ) {
												echo '<option value="'.$value.'" '.( ($woomelly_shipping_mode_field==$value)? "selected=\"selected\"" : "" ).'>'.$value.'</option>';
											}
										}
									?>
					                </select>
					            </div>
					        </div>
					        <div class="uk-margin woomelly_custom_shipping_cost_title_field" <?php if ( $woomelly_shipping_mode_field != 'custom' ) { echo "style='display: none;'"; } ?>>
					            <label class="uk-form-label"><?php echo sprintf(__("Shipping Custom Cost %s", "woomelly"), '<span class="uk-margin-small-right" uk-icon="icon: question" uk-tooltip="title: '.__("Add Description and Costs to your personalized shipments.", "woomelly").'; pos: right"></span>'); ?></label>
					            <button class="uk-button uk-button-primary uk-button-small woomelly_add_custom_shipping_cost_field_button" style="width: 100%;">Add</button>
					        </div>
							<div class="uk-margin woomelly_custom_shipping_cost_field" <?php if ( $woomelly_shipping_mode_field != 'custom' ) { echo "style='display: none;'"; } ?>>
							<?php if ( !empty($woomelly_custom_shipping_cost) ) {
								foreach ( $woomelly_custom_shipping_cost as $value ) {
									$woomelly_custom_shipping_cost_array = explode( '::', $value ); ?>
									<div>
										<input name="woomelly_custom_shipping_cost_description_field[]" id="woomelly_custom_shipping_cost_description_field" type="text" style="width: 70%;" value="<?php echo $woomelly_custom_shipping_cost_array[0]; ?>" /><input name="woomelly_custom_shipping_cost_cost_field[]" id="woomelly_custom_shipping_cost_cost_field" type="text" style="width: 20%;" value="<?php echo $woomelly_custom_shipping_cost_array[1]; ?>" /><a href="#" class="remove_field" style="width: 20%; text-decoration: none;"><span class="dashicons dashicons-no-alt" style="vertical-align: middle;"></span></a>
									</div>
								<?php
									unset( $woomelly_custom_shipping_cost_array );
								}
							} ?>
							</div>
							<div class="uk-margin woomelly_shipping_accepted_methods_field" <?php if ( $woomelly_shipping_mode_field != 'me1' && $woomelly_shipping_mode_field != 'me2' ) { echo "style='display: none;'"; } ?>>        
					            <label class="uk-form-label" for="woomelly_shipping_accepted_methods_field"><?php echo sprintf(__("Free Methods Accepted %s", "woomelly"), '<span class="uk-margin-small-right" uk-icon="icon: question" uk-tooltip="title: '.__("Select Free Methods Accepted", "woomelly").'; pos: right"></span>'); ?></label>
					            <div class="uk-form-controls">
					                <select class="uk-select uk-form-small" name="woomelly_shipping_accepted_methods_field" id="woomelly_shipping_accepted_methods_field">
					                <?php echo $shipping_modes_string; ?>
					                </select>
					            </div>
					        </div>
					        <div class="uk-margin woomelly_shipping_local_pick_up_field">
					            <label class="uk-form-label" for="woomelly_shipping_local_pick_up_field"><?php echo __("Local Pick up", "woomelly") ?> <span class="uk-margin-small-right" uk-icon="icon: question" uk-tooltip="title: <?php echo __('Select local Pick up', 'woomelly'); ?>; pos: right"></span></label>
					            <input class="checkbox" name="woomelly_shipping_local_pick_up_field" id="woomelly_shipping_local_pick_up_field" value="1" type="checkbox" <?php echo ( ($woomelly_shipping_local_pick_up_field==true)? "checked=\"checked\"" : "" ); ?>/>
					        </div>
					        <div class="uk-margin woomelly_shipping_free_shipping_field">
					            <label class="uk-form-label" for="woomelly_shipping_free_shipping_field"><?php echo __("Free shipping", "woomelly"); ?> <span class="uk-margin-small-right" uk-icon="icon: question" uk-tooltip="title: <?php echo __('Select Free shipping', 'woomelly'); ?>; pos: right"></span></label>
					            <input class="checkbox" name="woomelly_shipping_free_shipping_field" id="woomelly_shipping_free_shipping_field" value="1" type="checkbox" <?php echo ( ($woomelly_shipping_free_shipping_field==true)? "checked=\"checked\"" : "" ); ?>/>
					        </div>
					        <div class="uk-margin woomelly_shipping_dimensions_field">
					            <label class="uk-form-label" for="woomelly_shipping_dimensions_field"><?php echo __("Send Dimensions", "woomelly"); ?> <span class="uk-margin-small-right" uk-icon="icon: question" uk-tooltip="title: <?php echo __('Select Send Woocommerce Dimensions', 'woomelly'); ?>; pos: right"></span></label>
					            <input class="checkbox" name="woomelly_shipping_dimensions_field" id="woomelly_shipping_dimensions_field" value="1" type="checkbox" <?php echo ( ($woomelly_shipping_dimensions_field==true)? "checked=\"checked\"" : "" ); ?>/>
					        </div>
					    </div>
					    <div>
					        <div class="uk-margin woomelly_title_field">
					            <label class="uk-form-label" for="woomelly_title_field"><?php echo __("Publication Title", "woomelly"); ?> <span class="uk-margin-small-right" uk-icon="icon: question" uk-tooltip="title: <?php echo __('The best way to build a title is name + brand + model + technical specifications and features + additional services. Separate words with spaces and do not use symbols or punctuation marks. Control to avoid words with spelling errors. For example: Ipod Touch Apple 16gb 5 Generation.', 'woomelly'); ?>; pos: right"></span></label>
					            <div class="uk-form-controls">
					                <input class="uk-input uk-form-small" name="woomelly_title_field" id="woomelly_title_field" value="<?php echo $woomelly_title_field; ?>" placeholder="{name}" type="text">
					            </div>
					        </div>
					        <div class="uk-margin woomelly_status_field">
					            <label class="uk-form-label" for="woomelly_status_field"><?php echo __("Publication Status", "woomelly"); ?> <span class="uk-margin-small-right" uk-icon="icon: question" uk-tooltip="title: <?php echo __('States available in Mercadolibre. You can adjust from here or directly from the product (optional).', 'woomelly'); ?>; pos: right"></span></label>
					            <div class="uk-form-controls">
					                <select class="uk-select uk-form-small" name="woomelly_status_field" id="woomelly_status_field">
					                    <option value="" <?php echo ( ($woomelly_status_field == '')? 'selected="selected"' : '' ); ?>><?php echo __("- Select -", "woomnelly"); ?></option>
										<option value="active" <?php echo ( ($woomelly_status_field == 'active')? 'selected="selected"' : '' ); ?>><?php echo __("Active", "woomnelly"); ?></option>
										<option value="inactive" <?php echo ( ($woomelly_status_field == 'inactive')? 'selected="selected"' : '' ); ?>><?php echo __("Inactive", "woomnelly"); ?></option>
					                </select>
					            </div>
					        </div>
					        <div class="uk-margin woomelly_official_store_id_field">
					            <label class="uk-form-label" for="woomelly_official_store_id_field"><?php echo __("ID Official Store", "woomelly"); ?> <span class="uk-margin-small-right" uk-icon="icon: question" uk-tooltip="title: <?php echo __('Enter the ID of your official MercadoShop store (optional).', 'woomelly'); ?>; pos: right"></span></label>
					            <div class="uk-form-controls">
					                <input class="uk-input uk-form-small" name="woomelly_official_store_id_field" id="woomelly_official_store_id_field" value="<?php echo $woomelly_official_store_id_field; ?>" type="text">
					            </div>
					        </div>
					        <div class="uk-margin woomelly_price_field">
					            <label class="uk-form-label" for="woomelly_price_two_field"><?php echo __("Price Variation", "woomelly"); ?> <span class="uk-margin-small-right" uk-icon="icon: question" uk-tooltip="title: <?php echo __('It is used to adjust the amount of the sale price of the product (optional).', 'woomelly'); ?>; pos: right"></span></label>
					            <div class="uk-form-controls">
					                <select class="uk-select uk-form-small" name="woomelly_price_one_field" id="woomelly_price_one_field" style="width: 20%;">
										<option value="" <?php echo ( ($woomelly_price_one_field=="+")? "selected='selected'" : "" ); ?>></option>
										<option value="+" <?php echo ( ($woomelly_price_one_field=="+")? "selected='selected'" : "" ); ?>> + </option>
										<option value="-" <?php echo ( ($woomelly_price_one_field=="-")? "selected='selected'" : "" ); ?>> - </option>
										<option value="*" <?php echo ( ($woomelly_price_one_field=="*")? "selected='selected'" : "" ); ?>> * </option>
										<option value="/" <?php echo ( ($woomelly_price_one_field=="/")? "selected='selected'" : "" ); ?>> / </option>
					                </select>
					                <input class="uk-input uk-form-small wc_input_price" name="woomelly_price_two_field" id="woomelly_price_two_field" value="<?php echo $woomelly_price_two_field; ?>" type="text" style="width: 50%;" />
					                <select class="uk-select uk-form-small" name="woomelly_price_three_field" id="woomelly_price_three_field" style="width: 20%;">
										<option value="" <?php echo ( ($woomelly_price_three_field=="%")? "selected='selected'" : "" ); ?>></option>
										<option value="%" <?php echo ( ($woomelly_price_three_field=="%")? "selected='selected'" : "" ); ?>> % </option>
					                </select>
					            </div>
					        </div>
					        <div class="uk-margin woomelly_stock_field">
					            <label class="uk-form-label" for="woomelly_stock_two_field"><?php echo __("Inventory Variation", "woomelly"); ?> <span class="uk-margin-small-right" uk-icon="icon: question" uk-tooltip="title: <?php echo __('It is used to adjust the product inventory (optional).', 'woomelly'); ?>; pos: right"></span></label>
					            <div class="uk-form-controls">
					               <select class="uk-select uk-form-small" name="woomelly_stock_one_field" id="woomelly_stock_one_field" style="width: 20%;">
										<option value="" <?php echo ( ($woomelly_stock_one_field=="+")? "selected='selected'" : "" ); ?>></option>
										<option value="+" <?php echo ( ($woomelly_stock_one_field=="+")? "selected='selected'" : "" ); ?>> + </option>
										<option value="-" <?php echo ( ($woomelly_stock_one_field=="-")? "selected='selected'" : "" ); ?>> - </option>
										<option value="*" <?php echo ( ($woomelly_stock_one_field=="*")? "selected='selected'" : "" ); ?>> * </option>
										<option value="/" <?php echo ( ($woomelly_stock_one_field=="/")? "selected='selected'" : "" ); ?>> / </option>
					                </select>
					                <input class="uk-input uk-form-small" name="woomelly_stock_two_field" id="woomelly_stock_two_field" value="<?php echo $woomelly_stock_two_field; ?>" type="text" style="width: 50%;" />                     
					                <select class="uk-select uk-form-small" name="woomelly_stock_three_field" id="woomelly_stock_three_field" style="width: 20%;">
										<option value="" <?php echo ( ($woomelly_stock_three_field=="%")? "selected='selected'" : "" ); ?>></option>
										<option value="%" <?php echo ( ($woomelly_stock_three_field=="%")? "selected='selected'" : "" ); ?>> % </option>
					                </select>
					            </div>
					        </div>
					        <div class="uk-margin woomelly_seller_custom_field">
					            <label class="uk-form-label" for="woomelly_seller_custom_field"><?php echo __("SKU", "woomelly"); ?> <span class="uk-margin-small-right" uk-icon="icon: question" uk-tooltip="title: <?php echo __('Used to place the product SKU.', 'woomelly'); ?>; pos: right"></span></label>
					            <div class="uk-form-controls">
					                <input class="uk-input uk-form-small" name="woomelly_seller_custom_field" id="woomelly_seller_custom_field" value="<?php echo $woomelly_seller_custom_field_field; ?>" placeholder="{sku}" type="text">
					            </div>
					        </div>
					        <div class="uk-margin woomelly_video_id_field">
					            <label class="uk-form-label" for="woomelly_video_id_field"><?php echo __("Video ID", "woomelly"); ?> <span class="uk-margin-small-right" uk-icon="icon: question" uk-tooltip="title: <?php echo __('YouTube video ID, e.g. https://youtu.be/hgma71jxCsc your select  hgma71jxCsc. Or https://www.youtube.com/watch?v=hgma71jxCsc your select hgma71jxCsc (optional).', 'woomelly'); ?>; pos: right"></span></label>
					            <div class="uk-form-controls">
					                <input class="uk-input uk-form-small" name="woomelly_video_id_field" id="woomelly_video_id_field" value="<?php echo $woomelly_video_id_field; ?>" type="text">
					            </div>
					        </div>
					        <div class="uk-margin woomelly_warranty_field">
					            <label class="uk-form-label" for="woomelly_warranty_field"><?php echo __("Warranty", "woomelly"); ?> <span class="uk-margin-small-right" uk-icon="icon: question" uk-tooltip="title: <?php echo __('You can place here a guarantee for your publication (optional).', 'woomelly'); ?>; pos: right"></span></label>
					            <div class="uk-form-controls">
					                <textarea class="uk-textarea" name="woomelly_warranty_field" id="woomelly_warranty_field" rows="2"><?php echo $woomelly_warranty_field; ?></textarea>
					            </div>
					        </div>
							<div class="uk-margin woomelly_location_field">
								<label class="uk-form-label" for="woomelly_location_country_field"><?php echo __("Location", "woomelly"); ?> <span class="uk-margin-small-right" uk-icon="icon: question" uk-tooltip="title: <?php echo __('Select a location for the product (mandatory in some cases).', 'woomelly'); ?>; pos: right"></span></label>
								<div class="uk-form-controls">
									<select class="uk-select uk-form-small" name="woomelly_location_country_field" id="woomelly_location_country_field" style="width: 30%;">
										<option value=""><?php echo __("Country", "woomelly"); ?></option>
										<?php
											if ( !empty($all_location_country) ) {
												foreach ( $all_location_country as $value ) {
													?>
													<option value="<?php echo $value->id; ?>" <?php selected( $value->id, $woomelly_location_country_field); ?>><?php echo $value->name; ?></option>
													<?php
												}
											}
										?>
									</select>
									<select class="uk-select uk-form-small" name="woomelly_location_state_field" id="woomelly_location_state_field" style="width: 30%;">
										<option value=""><?php echo __("State", "woomelly"); ?></option>
										<?php
											if ( !empty($all_location_state) ) {
												foreach ( $all_location_state as $value ) {
													?>
													<option value="<?php echo $value->id; ?>" <?php selected( $value->id, $woomelly_location_state_field); ?>><?php echo $value->name; ?></option>
													<?php
												}
											}
										?>
									</select>
									<select class="uk-select uk-form-small" name="woomelly_location_city_field" id="woomelly_location_city_field" style="width: 30%;">
										<option value=""><?php echo __("City", "woomelly"); ?></option>
										<?php
											if ( !empty($all_location_city) ) {
												foreach ( $all_location_city as $value ) {
													?>
													<option value="<?php echo $value->id; ?>" <?php selected( $value->id, $woomelly_location_city_field); ?>><?php echo $value->name; ?></option>
													<?php
												}
											}
										?>
									</select>

								</div>
							</div>
					    </div>
					    <div>
					    	<div class="uk-margin uk-alert-primary uk-alert" id="woomelly_required_allow_variations">
						    	<?php echo sprintf(__("Total product with this template: %s.", "woomelly"), count($products_by_template)) . '<br>';
						    	 if ( $required_allow_variations == "" && $action!="add" ) {
						    		echo __("No attribute is mandatory or allows variations.", "woomelly");
						    	} else if ( $required_allow_variations == "" && $action=="add" ) {
						    		echo __("Please save the changes to update attribute information.", "woomelly");
						    	} else {
						    		echo $required_allow_variations;
						    	} ?>
					    	</div>
					        <div class="uk-margin woomelly_separate_variations_field">
					            <label class="uk-form-label" for="woomelly_separate_variations_field"><?php echo __("Independent Variations", "woomelly"); ?> <span class="uk-margin-small-right" uk-icon="icon: question" uk-tooltip="title: <?php echo __('Send variations as independent products (optional).', 'woomelly'); ?>; pos: right"></span></label>
					            <div class="uk-form-controls">
					                <select class="uk-select uk-form-small" name="woomelly_separate_variations_field" id="woomelly_separate_variations_field">
										<option value="inactive" <?php echo ( ($woomelly_separate_variations_field == 'inactive' || $woomelly_separate_variations_field == '')? 'selected="selected"' : '' ); ?>><?php echo __("Inactive", "woomnelly"); ?></option>
										<option value="active" <?php echo ( ($woomelly_separate_variations_field == 'active')? 'selected="selected"' : '' ); ?>><?php echo __("Active", "woomnelly"); ?></option>
					                </select>
					            </div>
					        </div>
					    </div>
					</div>
					<input type="hidden" value="<?php echo absint($template_id); ?>" name="woomelly_template_id">
                    <div class="uk-margin">
                        <input name="wm_templatesync_page_submit" type="submit" class="uk-button uk-button-primary" value="<?php echo __('Save changes', 'woomelly'); ?>" />
                        <?php if ( $action != "add" ) { ?> 
                        	<input name="wm_templatesync_page_submit_delete" type="submit" class="uk-button uk-button-link" style="color: red; margin-left: 5px;" id="wm_templatesync_page_submit_delete" value="<?php echo __('Delete', 'woomelly'); ?>" />
                    		<input type="hidden" name="wm_templatesync_page_submit_delete_security" id="wm_templatesync_page_submit_delete_security" value="">
                    	<?php } ?>
                    </div>					
                </div>
            <?php } else { ?>
				<div class="uk-alert-danger woomelly_alert_dont_connect">
					<p><?php echo sprintf( __( 'Excuse me, you have a connection problem with Mercadolibre. Verify that your website is %s', 'woomelly'), '<a href="'.admin_url( "admin.php?page=woomelly-settings" ).'" >'.__("connected and authorized correctly with Mercadolibre.", "woomelly").'</a>' ); ?></p>
				</div>
            <?php } ?>
        </form>
    </div>
</div>
<script>
	jQuery('#wm_templatesync_page_submit_delete').click(function( e ) {
		e.preventDefault();
		swal({
		  title: "<?php echo __('Are you sure?', 'woomelly'); ?>",
		  text: "<?php echo __('Once eliminated you will not be able to reverse such action!', 'woomelly'); ?>",
		  icon: "warning",
		  buttons: true,
		  dangerMode: true,
		}).then((willDelete) => {
			if (willDelete) {
				jQuery("#wm_templatesync_page_submit_delete_security").val("delete");
				jQuery("#woomelly-form-templatesync").submit();
			} else {
				swal( "<?php echo __('Cancelled!', 'woomelly'); ?>", "<?php echo __('The action has been canceled!', 'woomelly'); ?>", "error");
			}
		});
	});
</script>
