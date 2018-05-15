<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 5/20/2017
 * Time: 9:45 AM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

?>
<div id="tve-responsivevideo-component" class="tve-component" data-view="ResponsiveVideo">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Video Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-key="responsive_video" data-initializer="responsive_video"></div>
		<div class="tve-control" data-key="style" data-initializer="responsive_video_style"></div>
	</div>
</div>
