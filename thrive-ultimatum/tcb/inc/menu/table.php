<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>

<div id="tve-table-component" class="tve-component" data-view="Table">
	<div class="table-options action-group">
		<div class="dropdown-header" data-prop="docked">
			<div class="group-description">
				<?php echo __( 'Table Options', 'thrive-cb' ); ?>
			</div>
			<i></i>
		</div>

		<div class="dropdown-content">
			<div class="tve-control" data-key="cellpadding" data-view="Slider"></div>
			<div class="tve-control" data-key="sortable" data-view="Checkbox"></div>
			<div class="tve-control" data-key="valign"></div>
			<hr>
			<div class="tve-control" data-key="header_bg" data-view="ColorPicker"></div>
			<br>
			<div class="tve-control" data-key="cell_bg" data-view="ColorPicker"></div>
			<hr>
			<div class="hide-desktop hide-tablet">
				<div class="tve-control" data-key="mobile_table" data-view="Checkbox"></div>
				<span class="blue-text info-text">
					<?php echo __( 'This will apply some transformations on the table, making it responsive for mobile devices. Note that this will have unpredictable results if there are merged cells in the table.', 'thrive-cb' ) ?>
				</span>
				<hr>
				<div class="show-mobile-table">
					<div class="tve-control" data-key="mobile_header_width" data-view="Slider"></div>
				</div>
			</div>
			<div class="tcb-text-center hide-tablet hide-mobile">
				<button class="tve-button grey long click" data-fn="manage_cells"><?php echo __( 'Manage cells', 'thrive-cb' ) ?></button>
			</div>
			<div class="tve-advanced-controls extend-grey">
				<div class="dropdown-header" data-prop="advanced">
				<span>
					<?php echo __( 'Advanced', 'thrive-cb' ); ?>
				</span>
					<i></i>
				</div>

				<div class="dropdown-content clear-top">
					<div class="tve-control" data-key="even_rows" data-view="ColorPicker"></div>
					<br>
					<div class="tve-control" data-key="odd_rows" data-view="ColorPicker"></div>
					<hr>
					<div class="tcb-text-center">
						<span class="click tcb-text-uppercase clear-format" data-fn="clear_alternating_colors"><?php tcb_icon( 'close2' ) ?>
							&nbsp;<?php echo __( 'Clear alternating colors', 'thrive-cb' ) ?></span>
					</div>
					<hr>
					<div class="row middle-xs">
						<div class="col-xs-6">
							<button class="tve-button blue click" data-fn="reset_widths"><?php echo __( 'Reset widths', 'thrive-cb' ) ?></button>
						</div>
						<div class="col-xs-6 tcb-text-right">
							<button class="tve-button blue click" data-fn="reset_heights"><?php echo __( 'Reset heights', 'thrive-cb' ) ?></button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
