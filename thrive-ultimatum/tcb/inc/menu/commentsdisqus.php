<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 5/3/2017
 * Time: 2:04 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>

<div id="tve-commentsdisqus-component" class="tve-component" data-view="CommentsDisqus">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Disqus Comments Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-view="ForumName"></div>
		<div class="row margin-top-10">
			<div class="col-xs-1 blue-text"><?php tcb_icon( 'info' ); ?></div>
			<div class="col-xs-11 info-text"><?php echo __( 'Your forum name is part of the login address to Disqus. E.g. "http://healtylife.disqus.com", the forum name is "healtylife".', 'thrive-cb' ); ?></div>
		</div>
		<hr>
		<div class="tve-control" data-view="URL"></div>
		<div class="row margin-top-10">
			<div class="col-xs-1 blue-text"><?php tcb_icon( 'info' ); ?></div>
			<div class="col-xs-11 info-text"><?php echo __( 'The URL of the current place of content will be used. You can specify a different URL to store comments against if you prefer.', 'thrive-cb' ); ?></div>
		</div>
	</div>
</div>
