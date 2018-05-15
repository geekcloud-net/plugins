<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package TCB2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

?>
<div id="tve-icon-component" class="tve-component" data-view="Icon">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Icon Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="hide-states">
			<div class="tve-control tcb-text-center" data-view="ModalPicker"></div>
			<hr>
			<div class="tve-control" data-view="Slider"></div>
			<hr>
		</div>
		<div class="tve-control" data-view="ColorPicker"></div>
	</div>
</div>
