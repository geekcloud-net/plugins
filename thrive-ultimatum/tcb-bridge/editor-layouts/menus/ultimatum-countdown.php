<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 7/20/2017
 * Time: 5:46 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?>


<div id="tve-ultimatum_countdown-component" class="tve-component" data-view="ultimatum_countdown">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Ultimatum Countdown Options', TVE_Ult_Const::T ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="row padding-top-10 middle-xs">
			<div class="col-xs-12 tcb-text-center">
				<button class="blue tve-button click" data-fn="change_countdown">
					<?php echo __( 'Change Countdown', TVE_Ult_Const::T ); ?>
				</button>
			</div>
		</div>
	</div>
</div>
