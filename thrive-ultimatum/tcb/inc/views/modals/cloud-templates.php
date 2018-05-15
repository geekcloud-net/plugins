<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
$name_placeholder = '<span class="element-name"></span>';
?>

<h2 class="tcb-modal-title">
	<?php echo sprintf( esc_html( __( 'Choose %s Template', 'thrive-cb' ) ), $name_placeholder ) ?>
</h2>
<div class="status tpl-ajax-status">Fetching data ...</div>
<div class="error-container"></div>
<div class="tve-templates-wrapper">
	<div class="content-templates" id="cloud-templates"></div>
</div>

<div class="tcb-modal-footer clearfix padding-top-20 row end-xs">
	<div class="col col-xs-12">
		<button type="button" class="tcb-right tve-button medium green tcb-modal-save">
			<?php echo __( 'Choose Template', 'thrive-cb' ) ?>
		</button>
	</div>
</div>

