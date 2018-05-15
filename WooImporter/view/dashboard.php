<?php /* @var $dashboard WPEAE_DashboardPage */ ?>
<div>
	<?php if ($dashboard->api): ?>
		<h1><?php echo $dashboard->api->get_config_value("dashboard_title") ? $dashboard->api->get_config_value("dashboard_title") : $dashboard->api->get_type(); ?></h1>
	<?php endif; ?>

	<?php $dashboard->prepare_items(); ?>

	<?php settings_errors('wpeae_dashboard_error'); ?>
	<p>
		<?php settings_errors('wpeae_goods_list'); ?>
	</p>

	<?php if ($dashboard->show_dashboard): ?>
		<h2><?php _e('Search Filter','wpeae'); ?></h2>

		<form id="wpeae-search-form" method="GET">
			<input type="hidden" name="type" value="<?php echo esc_attr($dashboard->type); ?>" />
			<input type="hidden" name="page" id="page" value="<?php echo esc_attr(((isset($_GET['page'])) ? $_GET['page'] : '')); ?>" />
			<input type="hidden" id="reset" name="reset" value="" />

			<table class="form-table">
				<tbody>
					<?php $filters = $dashboard->api->get_filters(); ?>
					<?php foreach ($filters as $filter_id => $filter): ?> 
						<tr>
							<th>
								<?php if (isset($filter['config']['label'])): ?>
									<label for="<?php echo is_array($filter['name']) ? reset($filter['name']) : $filter['name']; ?>">
										<?php echo $filter['config']['label']; ?>:
									</label>
								<?php endif; ?>
							</th>
							<td>
								<?php if (isset($filter['config']['type']) && $filter['config']['type'] == 'select'): ?>
									<?php $is_multiple = isset($filter['config']['multiple']) && $filter['config']['multiple']; ?>
									<select <?php echo $is_multiple ? "multiple" : ""; ?> id="<?php echo $filter['name']; ?>" name="<?php echo $filter['name']; ?><?php echo $is_multiple ? "[]" : ""; ?>" class="<?php echo isset($filter['config']['class']) ? $filter['config']['class'] : ""; ?>" style="<?php echo isset($filter['config']['style']) ? $filter['config']['style'] : ""; ?>">
										<?php if (is_array($filter['config']['data_source'])): ?>
											<?php foreach ($filter['config']['data_source'] as $c): ?>
												<?php if ($is_multiple): ?>
													<option <?php if (isset($c["level"])): ?>class="level_<?php echo $c["level"]; ?>"<?php endif; ?> value="<?php echo esc_attr($c["id"]); ?>"<?php if (isset($dashboard->filter[$filter['name']]) && is_array($dashboard->filter[$filter['name']]) && in_array($c["id"], $dashboard->filter[$filter['name']])): ?> selected<?php endif; ?>><?php echo esc_attr(((isset($c["level"]) && intval($c["level"]) > 0) ? str_repeat(" - ", intval($c["level"]) - 1) : "") . $c["name"]); ?></option>
												<?php else: ?>
													<option <?php if (isset($c["level"])): ?>class="level_<?php echo $c["level"]; ?>"<?php endif; ?> value="<?php echo esc_attr($c["id"]); ?>"<?php if (isset($dashboard->filter[$filter['name']]) && $dashboard->filter[$filter['name']] == $c["id"]): ?> selected<?php endif; ?>><?php echo esc_attr(((isset($c["level"]) && intval($c["level"]) > 0) ? str_repeat(" - ", intval($c["level"]) - 1) : "") . $c["name"]); ?></option>
												<?php endif; ?>

											<?php endforeach; ?>
										<?php endif; ?>
									</select>
								<?php else: ?>
									<?php if (isset($filter['config']['type']) && $filter['config']['type'] == 'checkbox'): ?>
										<?php // echo $dashboard->filter[$filter['name']]; ?>
										<?php if (is_array($filter['name'])): ?>
											<?php foreach ($filter['name'] as $nn): ?>
												<?php if (isset($filter['config'][$nn]['label'])): ?>
													<label for="<?php echo $nn; ?>"><?php echo $filter['config'][$nn]['label']; ?></label>
												<?php endif; ?>
												<input name="<?php echo $nn; ?>" id="<?php echo $nn; ?>"        
													   value="<?php echo esc_attr(isset($dashboard->filter[$nn]) ? $dashboard->filter[$nn] : (isset($filter['config'][$nn]['default']) ? $filter['config'][$nn]['default'] : "")); ?>" 
													   <?php if (isset($dashboard->filter[$nn])) : ?>checked<?php endif; ?>
													   type="checkbox"/>
												   <?php endforeach; ?>
											   <?php else: ?>
											<input name="<?php echo $filter['name']; ?>" id="<?php echo $filter['name']; ?>" 
												   value="<?php echo esc_attr(isset($dashboard->filter[$filter['name']]) ? $dashboard->filter[$filter['name']] : (isset($filter['config']['default']) ? $filter['config']['default'] : "")); ?>" 
												   <?php if (isset($dashboard->filter[$filter['name']])) : ?>checked<?php endif; ?>
												   type="checkbox"/>
											   <?php endif; ?>

									<?php else: ?>
										<?php if (is_array($filter['name'])): ?>
											<?php foreach ($filter['name'] as $nn): ?>
												<?php if (isset($filter['config'][$nn]['label'])): ?>
													<label for="<?php echo $nn; ?>"><?php echo $filter['config'][$nn]['label']; ?></label>
												<?php endif; ?>
												<input name="<?php echo $nn; ?>" id="<?php echo $nn; ?>" 
													   placeholder="<?php echo isset($filter['config'][$nn]['placeholder']) ? $filter['config'][$nn]['placeholder'] : ""; ?>" 
													   value="<?php echo esc_attr(isset($dashboard->filter[$nn]) ? $dashboard->filter[$nn] : (isset($filter['config'][$nn]['default']) ? $filter['config'][$nn]['default'] : "")); ?>" 
													   class="small-text" type="text"/>
												   <?php endforeach; ?>
											   <?php else: ?>
											<input name="<?php echo $filter['name']; ?>" id="<?php echo $filter['name']; ?>" 
												   placeholder="<?php echo isset($filter['config']['placeholder']) ? $filter['config']['placeholder'] : ""; ?>" 
												   value="<?php echo esc_attr(isset($dashboard->filter[$filter['name']]) ? $dashboard->filter[$filter['name']] : (isset($filter['config']['default']) ? $filter['config']['default'] : "")); ?>" 
												   class="regular-text" type="text"/>
											   <?php endif; ?>
										   <?php endif; ?>
									   <?php endif; ?>

								<?php if (isset($filter['config']['description'])): ?>    
									<span class="description"><?php echo $filter['config']['description']; ?></span>
								<?php endif; ?>
							</td>

						</tr>

						<?php if (isset($filter['config']['dop_row']) && $filter['config']['dop_row']): ?>
							<tr><th colspan="2"><?php echo $filter['config']['dop_row']; ?></th></tr>
						<?php endif; ?>

					<?php endforeach; ?>

				</tbody>
			</table>

			<h2><?php _e('Link to category','wpeae'); ?></h2>

			<table class="form-table">
				<tbody>

					<tr>
						<th><label for="category_id"><?php _e('Category:','wpeae');?></label></th>
						<td>
							<select id="link_category_id" name="link_category_id" class="category_list" style="width:25em;">
								<option value=""></option>
								<?php foreach ($dashboard->link_categories as $c): ?>
									<option value="<?php echo $c["term_id"]; ?>"<?php if (isset($dashboard->filter["link_category_id"]) && $dashboard->filter["link_category_id"] == $c["term_id"]): ?> selected<?php endif; ?>>
										<?php
										for ($i = 1; $i < $c["level"]; $i++) {
											echo "-";
										}
										?>
										<?php echo $c["name"]; ?>
									</option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
				</tbody>
			</table>


			<?php if ($dashboard->loader->has_account()) : ?>
				<div><input type="button" id="wpeae-do-filter" class="button button-primary" value="<?php _e('Search', 'wpeae'); ?>"/></div>
				<h2><?php _e('Products list','wpeae'); ?></h2>
				<div class="before_list"><?php do_action('wpeae_before_product_list', $dashboard); ?></div>
				<div id="wpeae-goods-table">
					<div class='import_process_loader'></div>
					<?php $dashboard->display(); ?>
				</div>

				<?php add_thickbox(); ?>

			<?php endif; ?>
		</form>
	<?php endif; ?>


	<?php if ($dashboard->api->is_instaled() && $dashboard->show_dashboard): ?>
		<div id="upload_image_dlg" style="display: none">
			<div>
				<form id="image_upload_form" method="post" action="#" enctype="multipart/form-data" >
					<input type='hidden' value='<?php echo wp_create_nonce('upload_thumb'); ?>' name='_nonce' />
					<input type="hidden" name="upload_product_id" id="upload_product_id" value=""/>
					<input type="hidden" name="action" id="action" value="wpeae_upload_image"/>
					<input type="file" name="upload_image" id="upload_image"/>
					<br/><br/>
					<input id="submit-ajax" name="submit-ajax" type="submit" value="Upload this Image" class="button button-primary"/> <span id="upload_progress"></span>
				</form>
			</div>
		</div>

		<div id="edit_desc_dlg" style="display: none"></div>
	<?php endif; ?>
</div>