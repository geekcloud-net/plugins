<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>

<div id="tve-image-component" class="tve-component" data-view="Image">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Image Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-view="ImagePicker"></div>
		<hr>
		<div class="tve-control" data-view="ImageSize"></div>
		<hr>
		<div class="input-label grey-text"><?php echo __( 'Image Style', 'thrive-cb' ); ?></div>
		<div class="row padding-top-5 middle-xs">
			<div class="col-xs-12 tcb-input-button-wrapper">
				<div class="col-sep click" data-fn="open_style_picker"></div>
				<input type="text" id="tcb-image-style-name" class="style-change-input click" data-fn="open_style_picker" readonly value="<?php esc_attr_e( 'None', 'thrive-cb' ) ?>" data-default="<?php esc_attr_e( 'None', 'thrive-cb' ) ?>">
				<?php tcb_icon( 'edit', false, 'sidebar', 'tcb-input-button click', array( 'data-fn' => 'open_style_picker' ) ) ?>
			</div>

		</div>
		<div class="tve-control" data-key="StylePicker" data-initializer="style_picker_control"></div>
		<hr>
		<div class="row">
			<div class="col-xs-12">

			</div>

			<div class="col-xs-12">

			</div>
			<div class="col-xs-12">

			</div>
			<div class="col-xs-12">
				<div class="tve-control" data-view="ImageTitle"></div>
			</div>
			<div class="col-xs-12 padding-top-10">
				<div class="tve-control" data-view="ImageAltText"></div>
			</div>
			<div class="col-xs-12">
				<div class="tve-control" data-view="ImageCaption"></div>
			</div>
			<div class="col-xs-12">
				<div class="tve-control" data-view="ImageFullSize"></div>
			</div>
			<hr>
			<div class="col-xs-12">
				<div class="tve-control" data-view="ImageLink"></div>
			</div>
			<div class="col-xs-6">
				<div class="tve-control padding-top-10" data-view="LinkNewTab"></div>
			</div>
			<div class="col-xs-6">
				<div class="tve-control padding-top-10" data-view="LinkNoFollow"></div>
			</div>
		</div>
	</div>
</div>

<div id="tve-image-effects-component" class="tve-component" data-view="ImageEffects">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Image Effects', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="row">
			<div class="col-xs-12">
				<div class="tve-control" data-view="ImageGreyscale"></div>
			</div>
			<div class="col-xs-12 padding-top-10">
				<div class="tve-control" data-view="ImageOpacity"></div>
			</div>
			<div class="col-xs-12 padding-top-10">
				<div class="tve-control" data-view="ImageBlur"></div>
			</div>
			<div class="col-xs-12 padding-top-15">
				<div class="tve-control" data-view="ImageOverlaySwitch"></div>
			</div>
			<div class="col-xs-12 padding-top-15">
				<div class="tve-control" data-view="ImageOverlay"></div>
			</div>
		</div>
	</div>
</div>