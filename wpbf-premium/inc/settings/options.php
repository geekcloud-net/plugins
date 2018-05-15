<?php
/**
 * Options
 *
 * @package Page Builder Framework
 */

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// post types array
$post_types = get_post_types( array( 'public' => true ) );

add_action( 'load-post.php', 'wpbf_premium_metabox_setup' );
add_action( 'load-post-new.php', 'wpbf_premium_metabox_setup' );

/* Meta box setup function. */
function wpbf_premium_metabox_setup() {

	/* Add meta boxes on the 'add_meta_boxes' hook. */
	add_action( 'add_meta_boxes', 'wpbf_premium_add_metaboxes', 20 );

	/* Save post meta on the 'save_post' hook. */
	add_action( 'save_post', 'wpbf_premium_meta_save', 10, 2 );

}

function wpbf_premium_add_metaboxes( $post_types ) {

	add_meta_box( 'wpbf_header', esc_html__( 'Transparent Header', 'wpbfpremium' ), 'wpbf_premium_options_metabox', $post_types, 'side', 'default' );

}

function wpbf_premium_options_metabox( $post ) {

	wp_nonce_field( basename( __FILE__ ), 'wpbf_premium_options_nonce' );
	$wpbf_stored_meta = get_post_meta( $post->ID );

	if (!isset( $wpbf_stored_meta['wpbf_premium_options'][0] ) ) {
		$wpbf_stored_meta['wpbf_premium_options'][0] = false;
	}

	$mydata = $wpbf_stored_meta['wpbf_premium_options'];

	if ( strpos( $mydata[0], 'transparent-header') !== false ) {
		$transparent_header = 'transparent-header';
	} else {
		$transparent_header = false;
	}

	?>

	<div>
		<input id="transparent-header" type="checkbox" name="wpbf_premium_options[]" value="transparent-header" <?php checked( $transparent_header, 'transparent-header' ); ?> />
		<label for="transparent-header"><?php _e( 'Transparent Header', 'wpbfpremium' ); ?></label>
	</div>


<?php }

function wpbf_premium_meta_save( $post_id ) {

	$is_autosave = wp_is_post_autosave( $post_id );
	$is_revision = wp_is_post_revision( $post_id );
	$is_valid_nonce = ( isset( $_POST['wpbf_premium_options_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['wpbf_premium_options_nonce'] ), basename( __FILE__ ) ) ) ? true : false;

	if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return $post_id;
	}

	// save template options
	if ( isset( $_POST['wpbf_premium_options'] ) ) {

		$checked = array();

		// sanitizing
		if ( in_array( 'transparent-header', $_POST['wpbf_premium_options'] ) !== false ) {

			$checked[] .= 'transparent-header';

		} else {

			// if sanitization fails, pass an empty array.
			$checked[] = array();

		}

	}

	update_post_meta( $post_id, 'wpbf_premium_options', $checked );

}