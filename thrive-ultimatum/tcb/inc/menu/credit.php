<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 3/28/2017
 * Time: 11:41 AM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>

<div id="tve-credit-component" class="tve-component" data-view="Credit">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Credit Card Icons', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-key="style" data-initializer="credit_style_control"></div>
		<div class="tve-control margin-top-20" data-key="monochrome_background" data-view="ColorPicker"></div>
		<hr>
		<div class="tve-control tve-cards-list" data-key="cards_list" data-initializer="credit_style_control"></div>
		<div class="tve-control" data-key="preview" data-initializer="card_preview_control"></div>
		<hr>
		<div class="tve-control" data-key="size" data-view="Slider"></div>
	</div>
</div>
