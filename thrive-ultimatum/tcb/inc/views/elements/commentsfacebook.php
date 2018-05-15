<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 5/4/2017
 * Time: 12:00 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
$facebook_admins_arr = tve_get_comments_facebook_admins();
$facebook_admins_str = '';
if ( ! empty( $facebook_admins_arr ) && is_array( $facebook_admins_arr ) ) {
	$facebook_admins_str = implode( ';', $facebook_admins_arr );
}
?>

<div class="thrv_wrapper thrv_facebook_comments tve_draggable">
	<div class="tve-fb-comments" data-colorscheme="light" data-numposts="20" data-order-by="social" data-href="" data-fb-moderator-ids="<?php echo $facebook_admins_str; ?>"></div>
</div>
