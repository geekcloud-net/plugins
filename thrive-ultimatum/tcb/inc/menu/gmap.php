<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 4/28/2017
 * Time: 4:34 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?>

<div id="tve-gmap-component" class="tve-component" data-view="Gmap">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Google Map Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-view="address"></div>
		<hr>
		<div class="tve-control" data-view="zoom"></div>
	</div>
</div>
