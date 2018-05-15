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

<h2 class="tcb-modal-title"><?php echo __( 'Save Content as Template', 'thrive-cb' ) ?></h2>
<div class="margin-top-20">
	<?php echo __( 'You can save your work as a template for use on another post/page on your site.', 'thrive-cb' ) ?>
</div>

<div class="tvd-input-field margin-bottom-5 margin-top-25">
	<input type="text" id="tve-template-name" required>
	<label for="tve-template-name"><?php echo __( 'Template Name', 'thrive-cb' ); ?></label>
</div>

<div class="tcb-modal-footer clearfix padding-top-20 row end-xs">
	<div class="col col-xs-12">
		<button type="button" class="tcb-right tve-button medium green tcb-modal-save">
			<?php echo __( 'Save Template', 'thrive-cb' ) ?>
		</button>
	</div>
</div>
