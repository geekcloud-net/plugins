<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
?>

<h2 class="tcb-modal-title"><?php echo __( 'Connect with service', 'thrive-cb' ) ?></h2>

<div class="tve-wizard-breadcrumbs"></div>

<div class="tve-wizard-content">

</div>

<div style="display: none;">
	<?php wp_editor( '', 'tcb_lg_success_message', array(
		'quicktags'     => false,
		'media_buttons' => false,
		'textarea_rows' => 10,
	) ); ?>
</div>
