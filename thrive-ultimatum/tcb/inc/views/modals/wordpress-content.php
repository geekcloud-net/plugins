<h2 class="tcb-modal-title"><?php echo __( 'Insert WordPress Content into the page', 'thrive-cb' ) ?></h2>

<div class="row padding-top-10" id="tve_tinymce_shortcode_mce_holder">
	<div class="col col-xs-12">
		<?php
		tcb_remove_tinymce_conflicts();
		/* TODO: this can be removed after moving to admin */
		if (!function_exists('get_current_screen')) {
			/* fixes a conflict with tasty recipes plugin */
			require ABSPATH . 'wp-admin/includes/screen.php';
		}
		wp_editor( '', 'tve_tinymce_shortcode', array(
			'dfw'               => true,
			'tabfocus_elements' => 'insert-media-button,save-post',
			'editor_height'     => 260,
			'textarea_rows'     => 15,
		) );
		?>
	</div>
</div>

<div class="row padding-top-10">
	<div class="col col-xs-12">
		<button type="button" class="tcb-right tve-button medium green tcb-modal-save"><?php echo __( 'Save', 'thrive-cb' ) ?></button>
	</div>
</div>
