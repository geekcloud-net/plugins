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
<div id="tve-lead_generation_radio-component" class="tve-component" data-view="LeadGenerationRadio">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Lead Generation Radio', 'thrive-cb' ); ?>
		<i></i>
	</div>

	<div class="dropdown-content">
		<div class="tve-control" data-key="columns_number"></div>
		<hr>
		<div class="tve-control" data-key="required" data-view="Checkbox"></div>
	</div>
</div>
