<?php
/*
 * This file has to be included at the end of all editor layouts
 */

if ( ! empty( $is_ajax_render ) ) {
	/**
	 * If AJAX-rendering the contents, we need to only output the html part,
	 * and do not include any of the custom css / fonts etc needed - used in the state manager
	 */
	return;
}
?>

<?php do_action( 'get_footer' ) ?>
<?php wp_footer() ?>
</body>
</html>
