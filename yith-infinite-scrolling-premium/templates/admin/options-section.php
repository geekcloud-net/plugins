<?php

$id   = $this->_panel->get_id_field( $option['id'] );
$name = $this->_panel->get_name_field( $option['id'] );

$presetLoader = yinfs_get_preset_loader();

$eventType = $this->eventType;
$loadEffect = $this->loadEffect;

?>
<p>
	<input id="yith-infs-add-section" type="text" class="section-title" value="" />
	<a href="" id="yith-infs-add-section-button" class="button-secondary" data-section_id="<?php echo $id ?>" data-section_name="<?php echo $name ?>"><?php _e( 'Add section', 'yit' ) ?></a>
	<span class="error-input-section"></span>
</p>

<div id="<?php echo $id ?>-container" class="infs-sections-group" <?php if ( isset( $option['deps'] ) ): ?>data-field="<?php echo $id ?>" data-dep="<?php echo $this->_panel->get_id_field( $option['deps']['ids'] ) ?>" data-value="<?php echo $option['deps']['values'] ?>" <?php endif ?>>

	<?php if ( is_array( $db_value ) ) : ?>

		<?php foreach ( $db_value as $key => $value ) : ?>

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
								<input type="text" name="<?php echo $name ?>[<?php echo $key ?>][navSelector]" id="<?php echo $id ?>[<?php echo $key ?>][navSelector]" value="<?php echo isset( $db_value[ $key ]['navSelector'] ) ? $db_value[ $key ]['navSelector'] : '' ?>">
								<span class="desc-inline"><?php _e( 'The selector that contains the navigation of this section. Selectors can be class or ID names: the first ones must have a dot in front (.class-name), while the second must have a hash (#id-name).', 'yith-infinite-scrolling' ) ?></span>
							</td>
						</tr>

						<tr>
							<th>
								<label for="<?php echo $id ?>[<?php echo $key ?>][nextSelector]"><?php _e( 'Next Selector', 'yith-infinite-scrolling' ); ?></label>
							</th>
							<td>
								<input type="text" name="<?php echo $name ?>[<?php echo $key ?>][nextSelector]" id="<?php echo $id ?>[<?php echo $key ?>][nextSelector]" value="<?php echo isset( $db_value[ $key ]['nextSelector'] ) ? $db_value[ $key ]['nextSelector'] : '' ?>">
								<span class="desc-inline"><?php _e( 'The selector of the link that redirects to the next page of this section. Selectors can be class or ID names: the first ones must have a dot in front (.class-name), while the second must have a hash (#id-name).', 'yith-infinite-scrolling' ) ?></span>
							</td>
						</tr>

						<tr>
							<th>
								<label for="<?php echo $id ?>[<?php echo $key ?>][itemSelector]"><?php _e( 'Item Selector', 'yith-infinite-scrolling' ); ?></label>
							</th>
							<td>
								<input type="text" name="<?php echo $name ?>[<?php echo $key ?>][itemSelector]" id="<?php echo $id ?>[<?php echo $key ?>][itemSelector]" value="<?php echo isset( $db_value[ $key ]['itemSelector'] ) ? $db_value[ $key ]['itemSelector'] : '' ?>">
								<span class="desc-inline"><?php _e( 'The selector of the single item in the page. Selectors can be class or ID names: the first ones must have a dot in front (.class-name), while the second must have a hash (#id-name).', 'yith-infinite-scrolling' ) ?></span>
							</td>
						</tr>

						<tr>
							<th>
								<label for="<?php echo $id ?>[<?php echo $key ?>][contentSelector]"><?php _e( 'Content Selector', 'yith-infinite-scrolling' ); ?></label>
							</th>
							<td>
								<input type="text" name="<?php echo $name ?>[<?php echo $key ?>][contentSelector]" id="<?php echo $id ?>[<?php echo $key ?>][contentSelector]" value="<?php echo isset( $db_value[ $key ]['contentSelector'] ) ? $db_value[ $key ]['contentSelector'] : '' ?>">
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
									<option value="<?php echo $type ?>" <?php if( isset( $db_value[ $key ]['eventType'] ) ) selected( $type, $db_value[ $key ]['eventType'] ) ?> >
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
								<input type="text" name="<?php echo $name ?>[<?php echo $key ?>][buttonLabel]" id="<?php echo $id ?>[<?php echo $key ?>][buttonLabel]" value="<?php echo isset( $db_value[ $key ]['buttonLabel'] ) ? $db_value[ $key ]['buttonLabel'] : __( 'Load More', 'yith-infinite-scrolling' ) ?>">
								<span class="desc-inline"><?php _e( 'Set button label', 'yith-infinite-scrolling' ) ?></span>
							</td>
						</tr>

						<tr class="deps-button">
							<th>
								<label for="<?php echo $id ?>[<?php echo $key ?>][buttonClass]"><?php _e( 'Extra Class of the Button', 'yith-infinite-scrolling' ); ?></label>
							</th>
							<td>
								<input type="text" name="<?php echo $name ?>[<?php echo $key ?>][buttonClass]" id="<?php echo $id ?>[<?php echo $key ?>][buttonClass]" value="<?php echo isset( $db_value[ $key ]['buttonClass'] ) ? $db_value[ $key ]['buttonClass'] : '' ?>">
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
										<option value="<?php echo $preset ?>" data-loader_url="<?php echo $url ?>" <?php if( isset( $db_value[ $key ]['presetLoader'] ) ) selected( $preset, $db_value[ $key ]['presetLoader'] ) ?> >
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
								<input type="text" name="<?php echo $name ?>[<?php echo $key ?>][customLoader]" id="<?php echo $id ?>[<?php echo $key ?>][customLoader]" value="<?php echo isset( $db_value[ $key ]['customLoader'] ) ? $db_value[ $key ]['customLoader'] : '' ?>" class="upload_img_url" >
								<input type="button" value="<?php _e( 'Upload', 'yit' ) ?>" id="<?php echo $id ?>[<?php echo $key ?>][customLoader]-button" class="upload_img_button button" />
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
										<option value="<?php echo $effect ?>" <?php if( isset( $db_value[ $key ]['loadEffect'] ) ) selected( $effect, $db_value[ $key ]['loadEffect'] ) ?> >
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

		<?php endforeach; ?>

	<?php endif; ?>
</div>