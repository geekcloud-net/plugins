<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 5/4/2017
 * Time: 12:21 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>

<div id="tve-commentsfacebook-component" class="tve-component" data-view="CommentsFacebook">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Facebook Comments Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-view="URL"></div>
		<hr>
		<div class="tve-control" data-view="color_scheme"></div>
		<hr>
		<div class="tve-control" data-view="order_by"></div>
		<hr>
		<div class="tve-control" data-view="nr_of_comments"></div>
		<hr>
		<div class="tve-advanced-controls extend-grey">
			<div class="dropdown-header" data-prop="advanced">
				<span>
					<?php echo __( 'Comments Moderators', 'thrive-cb' ); ?>
				</span>
				<i></i>
			</div>
			<div class="dropdown-content clear-top">
				<div class="tve-control" data-view="moderators"></div>
				<hr>
				<div class="row margin-top-0">
					<div class="col-xs-1 blue-text"><?php tcb_icon( 'info' ); ?></div>
					<div class="col-xs-11 info-text"><?php echo __( 'This only works if you are friends on Facebook with the moderator(s)', 'thrive-cb' ); ?></div>
				</div>
			</div>
		</div>

	</div>
</div>
