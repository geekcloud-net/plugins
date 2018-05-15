<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 4/20/2017
 * Time: 3:59 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>

<div id="tve-divider-component" class="tve-component" data-view="Divider">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Divider Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-key="style" data-initializer="divider_style_control"></div>
		<hr>
		<div class="tve-control" data-view="divider_color"></div>
		<hr>
		<div class="tve-control" data-view="thickness"></div>
	</div>
</div>
