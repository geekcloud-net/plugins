<?php
/**
 * SKIN CUSTOM CODE GOES HERE
 */

function ywfm_skin1_specific_eunque() {
	$skin     = get_option( 'yith_wcfm_skin', 'default' );
	$skin_url = YITH_WCFM_TEMPLATE_URL . 'skins/' . $skin . '/';
	wp_enqueue_style( 'ywfm-open-sans', '//fonts.googleapis.com/css?family=Open+Sans:400,600,700', false );
	wp_enqueue_script( 'ywfm-skin1-double-tap-to-go', $skin_url . 'assets/js/jquery.doubletaptogo.min.js' );
	wp_enqueue_script( 'ywfm-skin1-script', $skin_url . 'assets/js/script.js' );
}

add_action( 'wp_enqueue_scripts', 'ywfm_skin1_specific_eunque', 15 );

/**
 * Add a welcome user message before sidebar nav
 */

add_action( 'yith_wcfm_before_account_navigation_link_list', 'ywfm_skin1_add_welcome_message' );

function ywfm_skin1_add_welcome_message() {
	$current_user    = wp_get_current_user();
	$user_first_name = $current_user->user_firstname;
	$user_last_name  = $current_user->user_lastname;
	$user_email      = $current_user->user_email;
	$display_name    = $current_user->display_name;
	$avatar_size     = apply_filters( 'yith_wcfm_user_avatar_size', 88 );
	$user_avatar     = apply_filters( 'yith_wcfm_user_avatar', get_avatar( $user_email, $avatar_size ), $avatar_size );
	$user_name       = '' == $user_first_name ? $display_name : $user_first_name . ' ' . $user_last_name;

	?>

    <div id="ywfm_user-infos">
        <div class="user-image">
			<?php echo $user_avatar; ?>
        </div>
        <div class="user-name">
			<?php echo __( 'Hi', 'yith-frontend-manager-for-woocommerce' ) . ', ' . $user_name; ?>
        </div>
    </div>
	<?php

}

/**
 * Enable support for mobile devices on each used theme
 */
function ywfm_add_mobile_viewport_support() {
	echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
}

add_action( 'wp_head', 'ywfm_add_mobile_viewport_support' );


/**
 * Remove the unused scripts and styles from the active theme
 */
function yfmfw_remove_scripts() {
	$wp_theme   = wp_get_theme();
	$theme_name = strtolower( $wp_theme->Name );

	// Specific for YITH Nielsen theme
	if ( $theme_name == 'nielsen' || $theme_name == 'rémy' || $theme_name == 'mindig' || $theme_name == 'desire-sexy-shop' ) {

		/**
		 * Redefine the smooth scroll js function to avoid the functionality
		 */
		$remove_scroll = 'jQuery.srSmoothscroll = function() {
		  return false;
		}';
		wp_add_inline_script( 'yit-common', $remove_scroll );
	}

}

add_action( 'wp_enqueue_scripts', 'yfmfw_remove_scripts', 100 );

/**
 * Load skin1 header template
 */
function yith_wcfm_load_skin1_header_loader() {
	include_once( __DIR__ . '/header.php' );
}
add_action( 'yith_wcfm_load_skin1_header', 'yith_wcfm_load_skin1_header_loader', 10 );

/**
 * Load skin1 header template
 */
function yith_wcfm_load_skin1_footer_loader() {
	include_once( __DIR__ . '/footer.php' );
}
add_action( 'yith_wcfm_load_skin1_footer', 'yith_wcfm_load_skin1_footer_loader', 10 );