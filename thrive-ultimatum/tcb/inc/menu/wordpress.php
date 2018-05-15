<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 4/10/2017
 * Time: 1:09 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>

<div id="tve-wordpress-component" class="tve-component" data-view="Wordpress">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'WordPress Content Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="row padding-top-10 middle-xs">
			<div class="col-xs-12 tcb-text-center">
				<button class="blue tve-button click" data-fn="edit_wordpress_content">
					<?php echo __( 'EDIT WORDPRESS CONTENT', 'thrive-cb' ); ?>
				</button>
			</div>
		</div>
	</div>
</div>
