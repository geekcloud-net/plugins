<?php
/**
 * Metabox for Discount Actions
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $post;
if ( isset( $_REQUEST['ywdpd_discount_type'] ) ) {
	$type = $_REQUEST['ywdpd_discount_type'];
} elseif ( isset( $_REQUEST['yit_metaboxes']['_discount_type'] ) ) {
	$type = $_REQUEST['yit_metaboxes']['_discount_type'];
} elseif ( isset( $_REQUEST['post'] ) ) {
	$type = get_post_meta( $post->ID, '_discount_type', true );
}



?>
<div class="submitbox ywdpd_submit_box" id="submitpost">
    <div id="major-publishing-actions">

        <div id="publishing-action">
            <div id="delete-action">
                <a class="submitdelete deletion" href="<?php echo YITH_WC_Dynamic_Pricing_Admin()->get_delete_post_link( '', $post->ID, $type ) ?>"><?php  _e( 'Delete', 'ywdpd' ) ?></a>
            </div>

            <span class="spinner"></span>
            <input name="original_publish" type="hidden" id="original_publish" value="Publish">
            <input type="submit" name="publish" id="publish" class="button button-primary button-large" value="Save"></div>
        <div class="clear"></div>
    </div>
</div>
