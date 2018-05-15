<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 5/20/2017
 * Time: 9:47 AM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

?>

<div class="tcb-elem-placeholder thrv_responsive_video thrv_wrapper" data-type="youtube">
	<span class="tcb-inline-placeholder-action with-icon">
		<?php tcb_icon( 'video-player', false, 'editor' ); ?>
		<?php echo __( 'Insert Video', 'thrive-cb' ); ?>
	</span>

	<div class="tve_responsive_video_container" style="display: none;">
		<div class="video_overlay"></div>
	</div>
</div>
