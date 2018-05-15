<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 5/12/2017
 * Time: 9:33 AM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

$time_settings = tve_get_time_settings();
?>

<div id="tve-countdown-component" class="tve-component" data-view="Countdown">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Countdown Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-key="style" data-initializer="countdown_style_control"></div>
		<hr>
		<div class="tve-control" data-view="Color"></div>
		<hr>

		<div class="tve-control" data-view="EndDate"></div>
		<div class="row middle-xs padding-top-10">
			<div class="col-xs-6">
				<div class="tve-control" data-view="Hour"></div>
			</div>
			<div class="col-xs-6">
				<div class="tve-control" data-view="Minute"></div>
			</div>
		</div>
		<div class="margin-top-10">
			<span class="grey-text"><?php echo __( 'Timezone', 'thrive-cb' ); ?> UTC <?php echo $time_settings['tzd']; ?></span>
		</div>
		<hr>
		<div class="tve-control" data-view="CompleteText"></div>
	</div>
</div>

