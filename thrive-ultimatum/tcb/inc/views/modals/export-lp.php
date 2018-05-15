<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
?>

<h2 class="tcb-modal-title"><?php echo __( 'Export Template', 'thrive-cb' ) ?></h2>

<div class="tvd-input-field margin-bottom-5 margin-top-25">
	<input type="text" id="tve-template-name" required>
	<label for="tve-template-name"><?php echo __( 'Template Name', 'thrive-cb' ); ?></label>
</div>

<div class="tve-template-image">
	<div>
		<?php echo __( 'Recommended image size: 166x140px. If you do not choose a picture, the default template thumbnail will be used.', 'thrive-cb' ); ?>
	</div>

	<div class="row margin-top-20">
		<div class="col-xs-4">
			<div class="thumbnail-preview" data-default="<?php echo TVE_LANDING_PAGE_TEMPLATE . '/thumbnails/blank.png'; ?>" style="height:140px;width:166px;background-size: cover;background-repeat: no-repeat;"></div>
		</div>
		<div class="col-xs-8">
			<button type="button" class="tve-button green choose-image margin-right-10"><?php echo __( 'Choose Image', 'thrive-cb' ) ?></button>
			<button type="button" class="tve-button red remove-image"><?php echo __( 'Remove Image', 'thrive-cb' ) ?></button>
		</div>
	</div>
</div>

<div class="tcb-modal-footer clearfix padding-top-20 row end-xs">
	<div class="col col-xs-12">
		<button type="button" class="tcb-right tve-button medium green tcb-modal-save">
			<?php echo __( 'Download File', 'thrive-cb' ) ?>
		</button>
	</div>
</div>

