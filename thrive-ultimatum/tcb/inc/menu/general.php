<div id="tve-layout-component" class="tve-component" data-view="Layout">

</div>

<div id="tve-responsive-component" class="tve-component" data-view="Responsive"></div>
<div id="tve-styles-templates-component" class="tve-component" data-view="StylesTemplates"></div>

<div id="tve-background-component" class="tve-component" data-view="Background">
	<div class="dropdown-header" data-prop="docked">
		<div class="group-description"><?php echo __( 'Background Style', 'thrive-cb' ) ?></div>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="gradient-layers"></div>
		<div class="tve-control" data-key="PreviewFilterList" data-view="PreviewList"></div>
		<div class="tve-control" data-view="PreviewList"></div>
		<div class="v-sep"></div>
		<div class="tve-control" data-view="ColorPicker" data-show-gradient="0"></div>
		<div class="tve-control video-bg" data-key="video" data-initializer="video"></div>
	</div>
</div>

<div id="tve-typography-component" class="tve-component" data-view="Typography">
	<div class="dropdown-header" data-prop="docked">
		<div class="group-description"><?php echo __( 'Typography', 'thrive-cb' ) ?></div>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="hide-states typography-button-toggle-controls">
			<div class="tve-control" data-view="ToggleControls"></div>

			<div class="tve-control tcb-typography-toggle-element tcb-typography-font-size" data-view="FontSize"></div>
			<div class="tve-control tcb-typography-toggle-element tcb-typography-line-height" data-view="LineHeight"></div>
			<div class="tve-control tcb-typography-toggle-element tcb-typography-letter-spacing" data-view="LetterSpacing"></div>
			<hr class="typography-font-color-hr">
		</div>
		<div class="tve-control" data-view="FontColor"></div>
		<hr class="typography-text-align-style-hr">
		<div class="row">
			<div class="tve-control col-xs-6" data-view="TextAlign"></div>
			<div class="tve-control col-xs-6" data-view="TextStyle"></div>
		</div>
		<div class="row middle-xs">
			<div class="tve-control col-xs-12" data-view="TextTransform"></div>
		</div>
		<hr>
		<div class="row tve-control" data-view="FontFace">
			<div class="col-xs-12">
				<span class="input-label"><?php echo __( 'Font Face', 'thrive-cb' ); ?></span>
			</div>
			<div class="col-xs-12 tcb-input-button-wrapper">
				<div class="col-sep click" data-fn="openFonts"></div>
				<input type="text" class="font-face-input click" data-fn="openFonts" readonly>
				<?php tcb_icon( 'edit', false, 'sidebar', 'tcb-input-button click', array( 'data-fn' => 'openFonts' ) ) ?>
			</div>
		</div>
		<div class="tve-advanced-controls extend-grey">
			<div class="dropdown-header" data-prop="advanced">
				<span>
					<?php echo __( 'Advanced', 'thrive-cb' ); ?>
				</span>
				<i></i>
			</div>

			<div class="dropdown-content clear-top">
				<div class="hide-states">
					<div class="tve-control" data-view="Slider" data-key="p_spacing"></div>
					<div class="tve-control" data-view="Slider" data-key="h1_spacing"></div>
					<div class="tve-control" data-view="Slider" data-key="h2_spacing"></div>
					<div class="tve-control" data-view="Slider" data-key="h3_spacing"></div>
				</div>
			</div>
		</div>
	</div>
</div>

<div id="tve-borders-component" class="tve-component" data-view="Borders">
	<div class="borders-options action-group">
		<div class="dropdown-header" data-prop="docked">
			<div class="group-description">
				<?php echo __( 'Borders & Corners', 'thrive-cb' ); ?>
			</div>
			<i></i>
		</div>
		<div class="dropdown-content">

			<div class="tve-control" data-view="Borders"></div>
			<hr>
			<div class="tve-control" data-view="Corners"></div>

		</div>
	</div>
</div>

<div id="tve-animation-component" class="tve-component" data-view="Animation">
	<?php $tabs = tcb_get_editor_actions(); ?>
	<div class="animation-options action-group">
		<div class="dropdown-header" data-prop="docked">
			<div class="group-description">
				<?php esc_html_e( 'Animation & Action', 'thrive-cb' ); ?>
			</div>
			<i></i>
		</div>
		<div class="dropdown-content">
			<div class="tve-animation button-group-holder">
				<div class="group-label">
					<?php esc_html_e( 'Add new:', 'thrive-cb' ) ?>
				</div>
				<div class="tve-btn-group grey" id="tcb-anim-buttons">
					<?php foreach ( $tabs as $key => $tab ) : ?>
						<div class="btn-inline tve-btn click anim-<?php echo esc_attr( $key ) ?>" data-fn="tab_click"
							 data-value="<?php echo esc_attr( $key ) ?>"
							 title="<?php echo esc_attr( $tab['title'] ) ?>">
							<?php tcb_icon( $tab['icon'] ) ?>
						</div>
					<?php endforeach ?>
				</div>
			</div>
			<div class="actions-holder tcb-dark">
				<?php foreach ( $tabs as $key => $tab ) : ?>
					<div class="action-tab action-<?php echo esc_attr( $key ) ?>" style="display: none" data-tab="<?php echo esc_attr( $key ) ?>">
						<?php /* special case for links */ ?>
						<?php if ( $key == 'link' || isset( $tab['instance'] ) ) : ?>
							<div class="action-settings"
								 data-action="<?php echo isset( $tab['instance'] ) ? esc_attr( $tab['instance']->get_key() ) : 'link' ?>"
								 data-view="<?php echo isset( $tab['instance'] ) ? esc_attr( $tab['instance']->get_editor_js_view() ) : 'Link' ?>">
								<?php if ( isset( $tab['instance'] ) ) : $tab['instance']->render_editor_settings();
								else : tcb_template( 'actions/link' ); endif ?>
							</div>
						<?php elseif ( isset( $tab['actions'] ) ) : ?>
							<?php if ( $key === 'popup' ) : ?>
							<div class="tve-select-arrow">
								<label for="a-popup-trigger"><?php echo __( 'Trigger', 'thrive-cb' ) ?></label>
								<select class="tcb-dark" id="a-popup-trigger">
									<option value="click" selected><?php echo __( 'Click', 'thrive-cb' ) ?></option>
									<option value="tve-viewport"><?php echo __( 'Comes into viewport', 'thrive-cb' ) ?></option>
								</select>
							</div>
							<?php endif ?>
							<div class="action-collection">
								<?php
								$auto_select = count( $tab['actions'] ) == 1 ? 'checked="checked"' : '';
								foreach ( $tab['actions'] as $action ) : ?>
									<div class="action-item">
										<label class="tcb-radio">
											<input name="action_group_<?php echo esc_attr( $key ) ?>" <?php echo $auto_select ?> type="radio"
												   class="action-chooser change"
												   data-fn="action_select"
												   value="<?php echo esc_attr( $action['instance']->get_key() ) ?>">
											<span><?php echo esc_html( $action['instance']->getName() ) ?></span>
										</label>
										<div class="action-settings" style="display: none"
											 data-action="<?php echo esc_attr( $action['instance']->get_key() ) ?>"
											 data-view="<?php echo esc_attr( $action['instance']->get_editor_js_view() ) ?>">
											<?php $action['instance']->render_editor_settings() ?>
										</div>
									</div>
								<?php endforeach ?>
							</div>
						<?php endif ?>
					</div>
				<?php endforeach ?>
			</div>
			<div id="tcb-anim-list" class="tcb-relative"></div>
		</div>
	</div>
</div>

<div id="tve-shadow-component" class="tve-component" data-view="Shadow">
	<div class="borders-options action-group">
		<div class="dropdown-header" data-prop="docked">
			<div class="group-description">
				<?php echo __( 'Shadow', 'thrive-cb' ); ?>
			</div>
			<i></i>
		</div>
		<div class="dropdown-content">
			<div class="tve-shadow" id="tcb-shadow-buttons"></div>
			<div id="tcb-text-shadow-list" class="tcb-relative tcb-preview-list" data-shadow-type="text-shadow"></div>
			<div id="tcb-box-shadow-list" class="tcb-relative tcb-preview-list" data-shadow-type="box-shadow"></div>
		</div>
	</div>

</div>

<div id="tve-lp-advanced-component" class="tve-component" data-view="LpAdvanced">
	<div class="dropdown-header" data-prop="docked">
		<div class="group-description"><?php echo __( 'Advanced Options', 'thrive-cb' ) ?></div>
		<i></i>
	</div>
	<div class="dropdown-content">
		<p class="strip-css"></p>
		<p class="margin-top-10" style="line-height: 20px">Thrive Architect will strip out any Custom CSS from the
			&lt;head&gt; section from all Landing Pages built with it.
			Usually, this is extra CSS that is not needed throughout the Lading Page.
			By ticking the checkbox above, you will disable this functionality, and all Custom CSS will be included.
			Please keep in mind that including this Custom CSS might prevent some of the above controls to function properly, such as: background color,
			background image etc.
		</p>
	</div>
</div>

<div id="tve-lp-scripts-component" class="tve-component" data-view="LpScripts">
	<div class="dropdown-header" data-prop="docked">
		<div class="group-description"><?php echo __( 'Custom Scripts', 'thrive-cb' ) ?></div>
		<i></i>
	</div>
	<div class="dropdown-content">
		<p>
			Header scripts (Before the <b>&lt;/head&gt;</b> end tag)
		</p>
		<textarea title="<?php echo __( 'Header Scripts', 'thrive-cb' ); ?>" data-location="head"></textarea>

		<p>Body (header) scripts (Immediately after the <b>&lt;body&gt;</b> tag)</p>
		<textarea title="<?php echo __( 'Body Scripts', 'thrive-cb' ); ?>" data-location="body"></textarea>

		<p>Body (footer) scripts (Before the <b>&lt;/body&gt;</b> end tag)</p>
		<textarea title="<?php echo __( 'Footer Scripts', 'thrive-cb' ); ?>" data-location="footer"></textarea>
	</div>
</div>

<div id="tve-lp-fonts-component" class="tve-component" data-view="LpFonts">
	<div class="dropdown-header" data-prop="docked">
		<div class="group-description"><?php echo __( 'Landing Page Text Options', 'thrive-cb' ) ?></div>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tcb-text-center">
			<button class="tve-button grey long click" data-fn="edit_font_options"><?php echo __( 'EDIT PAGE TEXTS', 'thrive-cb' ); ?></button>
		</div>
	</div>
</div>

<div id="tve-cloud-templates-component" data-key="cloud_templates" class="tve-component dynamic-component" style="order: 5;" data-view="CloudTemplates">
	<div class="dropdown-header" data-prop="docked">
		<div class="group-description"><?php echo __( 'Template Options', 'thrive-cb' ) ?></div>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-image tve-button click whitey dashed" data-fn-click="open_modal">
			<?php echo __( 'Change Template', 'thrive-cb' ) ?>
		</div>

	</div>
</div>

<div id="tve-group-component" class="tve-component" data-view="Group">
	<div class="dropdown-header" data-prop="docked">
		<div class="group-description"><?php echo __( 'Currently styling', 'thrive-cb' ) ?></div>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="row">
			<div class="col-xs-10">
				<div class="tve-control" data-view="preview"></div>
			</div>
			<div class="col-xs-2">
				<div class="tve-control" data-view="ButtonToggle"></div>
			</div>
		</div>
		<hr>
		<div class="tcb-text-center margin-top-10">
			<a href="javascript:void(0);" class="click clear-format" data-fn="close_group_options"><?php tcb_icon( 'exit-to-app' ); ?> <span id="tcb-exit-group-editing"><?php echo __( 'Exit Group Styling', 'thrive-cb' ); ?></span></a>
		</div>

	</div>
</div>