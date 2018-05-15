<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 4/18/2017
 * Time: 11:57 AM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>

<div id="tve-html-component" class="tve-component" data-view="Html">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Custom HTML Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="row padding-top-10 middle-xs">
			<div class="col-xs-12 tcb-text-center">
				<button class="blue tve-button click" data-fn="edit_html_content">
					<?php echo __( 'EDIT HTML CONTENT', 'thrive-cb' ); ?>
				</button>
			</div>
		</div>
	</div>
</div>
