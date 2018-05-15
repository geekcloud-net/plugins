<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 5/16/2017
 * Time: 12:58 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

?>
<div id="tve-countdownevergreen-component" class="tve-component" data-view="CountdownEvergreen">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Countdown Evergreen Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-key="style" data-initializer="countdown_style_control"></div>
		<hr>
		<div class="tve-control" data-view="Color"></div>
		<hr>
		<div class="row middle-xs padding-top-10">
			<div class="col-xs-6">
				<div class="tve-control" data-view="Day"></div>
			</div>
			<div class="col-xs-6">
				<div class="tve-control" data-view="Hour"></div>
			</div>
		</div>
		<div class="row middle-xs padding-top-10">
			<div class="col-xs-6">
				<div class="tve-control" data-view="Minute"></div>
			</div>
			<div class="col-xs-6">
				<div class="tve-control" data-view="Second"></div>
			</div>
		</div>

		<div class="tve-control padding-top-10" data-view="StartAgain"></div>

		<div class="row middle-xs padding-top-10 tcb-hidden tcb-start-again-control">
			<div class="col-xs-6">
				<div class="tve-control" data-view="ExpDay"></div>
			</div>
			<div class="col-xs-6">
				<div class="tve-control" data-view="ExpHour"></div>
			</div>
		</div>
		<hr>
		<div class="tve-control" data-view="CompleteText"></div>
	</div>
</div>
