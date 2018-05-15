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
<div id="tve-lp-base-component" class="tve-component" data-view="LpBase">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Landing Page Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-view="ContentFullWidth"></div>
		<div class="width-setting">
			<hr>
			<div class="tve-control" data-view="ContentWidth"></div>
		</div>
		<hr>
		<div class="tve-control" data-view="RemoveThemeCss"></div>
	</div>
</div>
