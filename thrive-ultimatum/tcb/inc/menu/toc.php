<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package TCB2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

?>
<div id="tve-toc-component" class="tve-component" data-view="TOC">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Table of Contents Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control margin-bottom-10" data-view="HeaderColor"></div>
		<div class="tve-control margin-bottom-10" data-view="HeadBackground"></div>
		<div class="hide-states">
			<hr>
			<div class="tve-control margin-bottom-10 tve-toc-control" data-view="Headings"></div>
			<hr>
			<div class="tve-control margin-bottom-10" data-view="Columns"></div>
			<hr>
			<div class="tve-control margin-bottom-10" data-view="MaxWidth"></div>
		</div>
	</div>
</div>
