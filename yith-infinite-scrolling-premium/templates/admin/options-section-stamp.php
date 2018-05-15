<div class="infs-section <?php echo $key ?>">

	<div class="section-head">
		<?php echo __( 'Options for ', 'yith-infinite-scrolling' ) . $key ?>
		<span class="remove" data-section="<?php echo $key ?>"></span>
	</div>

	<div class="section-body">
		<table>

			<tr>
				<th>
					<label for="<?php echo $id ?>[<?php echo $key ?>][navSelector]"><?php _e( 'Navigation Selector', 'yith-infinite-scrolling' ); ?></label>
				</th>
				<td>
					<input type="text" name="<?php echo $name ?>[<?php echo $key ?>][navSelector]" id="<?php echo $id ?>[<?php echo $key ?>][navSelector]" value="" />
					<span class="desc-inline"><?php _e( 'The selector that contains the navigation of this section. Selectors can be class or ID names: the first ones must have a dot in front (.class-name), while the second must have a hash (#id-name).', 'yith-infinite-scrolling' ) ?></span>
				</td>
			</tr>

			<tr>
				<th>
					<label for="<?php echo $id ?>[<?php echo $key ?>][nextSelector]"><?php _e( 'Next Selector', 'yith-infinite-scrolling' ); ?></label>
				</th>
				<td>
					<input type="text" name="<?php echo $name ?>[<?php echo $key ?>][nextSelector]" id="<?php echo $id ?>[<?php echo $key ?>][nextSelector]" value="" />
					<span class="desc-inline"><?php _e( 'The selector of the link that redirects to the next page of this section. Selectors can be class or ID names: the first ones must have a dot in front (.class-name), while the second must have a hash (#id-name).', 'yith-infinite-scrolling' ) ?></span>
				</td>
			</tr>

			<tr>
				<th>
					<label for="<?php echo $id ?>[<?php echo $key ?>][itemSelector]"><?php _e( 'Item Selector', 'yith-infinite-scrolling' ); ?></label>
				</th>
				<td>
					<input type="text" name="<?php echo $name ?>[<?php echo $key ?>][itemSelector]" id="<?php echo $id ?>[<?php echo $key ?>][itemSelector]" value="" />
					<span class="desc-inline"><?php _e( 'The selector of the single item in the page. Selectors can be class or ID names: the first ones must have a dot in front (.class-name), while the second must have a hash (#id-name).', 'yith-infinite-scrolling' ) ?></span>
				</td>
			</tr>

			<tr>
				<th>
					<label for="<?php echo $id ?>[<?php echo $key ?>][contentSelector]"><?php _e( 'Content Selector', 'yith-infinite-scrolling' ); ?></label>
				</th>
				<td>
					<input type="text" name="<?php echo $name ?>[<?php echo $key ?>][contentSelector]" id="<?php echo $id ?>[<?php echo $key ?>][contentSelector]" value="" />
					<span class="desc-inline"><?php _e( 'The selector that contains your section content. Selectors can be class or ID names: the first ones must have a dot in front (.class-name), while the second must have a hash (#id-name).', 'yith-infinite-scrolling' ) ?></span>
				</td>
			</tr>

			<tr>
				<th>
					<label for="<?php echo $id ?>[<?php echo $key ?>][eventType]"><?php _e( 'Event Type', 'yith-infinite-scrolling' ); ?></label>
				</th>
				<td>
					<select name="<?php echo $name ?>[<?php echo $key ?>][eventType]" id="<?php echo $id ?>[<?php echo $key ?>][eventType]" class="yith-infs-eventype-select">
						<?php foreach( $eventType as $type => $label ) : ?>
							<option value="<?php echo $type ?>">
								<?php echo $label ?>
							</option>
						<?php endforeach; ?>
					</select>
					<span class="desc-inline"><?php _e( 'Select the type of pagination', 'yith-infinite-scrolling' ) ?></span>
				</td>
			</tr>

			<tr class="deps-button">
				<th>
					<label for="<?php echo $id ?>[<?php echo $key ?>][buttonLabel]"><?php _e( 'Button Label', 'yith-infinite-scrolling' ); ?></label>
				</th>
				<td>
					<input type="text" name="<?php echo $name ?>[<?php echo $key ?>][buttonLabel]" id="<?php echo $id ?>[<?php echo $key ?>][buttonLabel]" value="<?php _e( 'Load More', 'yith-infinite-scrolling' ) ?>">
					<span class="desc-inline"><?php _e( 'Set button label', 'yith-infinite-scrolling' ) ?></span>
				</td>
			</tr>

			<tr class="deps-button">
				<th>
					<label for="<?php echo $id ?>[<?php echo $key ?>][buttonClass]"><?php _e( 'Extra Class of the Button', 'yith-infinite-scrolling' ); ?></label>
				</th>
				<td>
					<input type="text" name="<?php echo $name ?>[<?php echo $key ?>][buttonClass]" id="<?php echo $id ?>[<?php echo $key ?>][buttonClass]" value="">
					<span class="desc-inline"><?php _e( 'Add a custom class to customize the button style. Use space for multiple classes.', 'yith-infinite-scrolling' ) ?></span>
				</td>
			</tr>

			<tr class="deps-scroll">
				<th>
					<label for="<?php echo $id ?>[<?php echo $key ?>][presetLoader]"><?php _e( 'Choose a Loader', 'yith-infinite-scrolling' ); ?></label>
				</th>
				<td>
					<select name="<?php echo $name ?>[<?php echo $key ?>][presetLoader]" id="<?php echo $id ?>[<?php echo $key ?>][eventType]" class="yith-infs-loader-select">
						<?php foreach ( $presetLoader as $preset => $url ) : ?>
							<option value="<?php echo $preset ?>" data-loader_url="<?php echo $url ?>" >
								<?php echo $preset ?>
							</option>
						<?php endforeach; ?>
					</select>
					<span class="desc-inline"><?php _e( 'Choose a preset loader to use.', 'yith-infinite-scrolling' ) ?></span>
					<img class="yith-infs-loader-preview" src="" />
				</td>
			</tr>

			<tr class="deps-scroll">
				<th>
					<label for="<?php echo $id ?>[<?php echo $key ?>][customLoader]"><?php _e( 'Custom Loader', 'yith-infinite-scrolling' ); ?></label>
				</th>
				<td>
					<input type="text" name="<?php echo $name ?>[<?php echo $key ?>][customLoader]" id="<?php echo $id ?>[<?php echo $key ?>][customLoader]" value="" class="upload_img_url" >
					<input type="button" value="<?php _e( 'Upload', 'yith-infinite-scrolling' ) ?>" id="<?php echo $id ?>[<?php echo $key ?>][customLoader]-button" class="upload_img_button button" />
					<span class="desc-inline"><?php _e( 'Upload a custom loading image. This option overrides the previous one.', 'yith-infinite-scrolling' ) ?></span>
				</td>
			</tr>

			<tr>
				<th>
					<label for="<?php echo $id ?>[<?php echo $key ?>][loadEffect]"><?php _e( 'Load Effect', 'yith-infinite-scrolling' ); ?></label>
				</th>
				<td>
					<select name="<?php echo $name ?>[<?php echo $key ?>][loadEffect]" id="<?php echo $id ?>[<?php echo $key ?>][loadEffect]">
						<?php foreach ( $loadEffect as $effect => $label ) : ?>
							<option value="<?php echo $effect ?>">
								<?php echo $label ?>
							</option>
						<?php endforeach; ?>
					</select>
					<span class="desc-inline"><?php _e( 'Type of animation for the loading of new contents.', 'yith-infinite-scrolling' ) ?></span>
				</td>
			</tr>

		</table>
	</div>

</div>