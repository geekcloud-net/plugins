<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

/* define a list of post types that the user can choose from */
$all_post_types = get_post_types( array(
	'public'  => true,
	'show_ui' => true,
) );

$blacklist = apply_filters( 'tve_post_types_blacklist', array( 'tcb_lightbox' ) );
$saved     = maybe_unserialize( get_option( 'tve_hyperlink_settings', array( 'post', 'page' ) ) ); // by default, show posts and pages

$all_post_types = array_diff( $all_post_types, $blacklist ); ?>
<h2 class="tcb-modal-title"><?php esc_html_e( 'Thrive Hyperlink Settings', 'thrive-cb' ) ?></h2>
<p><?php echo __( 'Select the content to be included in search results.', 'thrive-cb' ) ?></p>
<div class="inline-checkboxes row">
	<?php foreach ( $all_post_types as $i => $post_type ) : $info = get_post_type_object( $post_type ) ?>
		<div class="col col-xs-4">
			<label for="tcb-post-type-<?php echo $i ?>" class="tcb-checkbox tcb-truncate" title="<?php echo esc_attr( $info->labels->menu_name ) ?>">
				<input type="checkbox" class="post-type" name="post_types[]" id="tcb-post-type-<?php echo $i ?>"<?php checked( in_array( $post_type, $saved ) ) ?>
					   value="<?php echo esc_attr( $post_type ) ?>">
				<span><?php echo $info->labels->menu_name; ?></span>
			</label>
		</div>
	<?php endforeach ?>
</div>

<div class="tcb-modal-footer clearfix padding-top-40">
	<button type="button" class="tcb-modal-save tcb-right tve-button medium green"><?php echo __( 'Continue', 'thrive-cb' ) ?></button>
</div>
