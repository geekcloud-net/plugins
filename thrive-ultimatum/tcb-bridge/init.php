<?php
/**
 * main entry point for setups that do have TCB as a separate plugin
 */

/**
 * this will make sure that posts and pages are not editable with TCB when the user only has TU
 */
add_filter( 'tcb_post_types', 'tve_ult_disable_tcb_edit' );

/**
 * this will hide the Thrive Lightboxes menu link that's added from TCB - in case the TCB plugin is not installed
 */
add_filter( 'tcb_lightbox_menu_visible', '__return_false' );

/**
 * if the plugin-core.php file has not yet been included, include it here
 */
if ( ! defined( 'TVE_TCB_CORE_INCLUDED' ) ) {
	require_once TVE_Ult_Const::plugin_path() . 'tcb/plugin-core.php';
}

/**
 * it's possible that this function is defined from thrive ultimatum or TCB
 * I think we're ok even if this is loaded from thrive ultimatum (the css files will then be loaded from the TCB folder inside TL)
 */
if ( ! function_exists( 'tve_editor_url' ) ) {
	/**
	 * we need override the base path here
	 */
	function tve_editor_url( $file = null ) {
		return rtrim( plugin_dir_url( dirname( __FILE__ ) ) . '/tcb/' . ( null !== $file ? ltrim( $file, '/\\' ) : '' ), '/' );
	}
}

/**
 *
 * block regular posts / pages etc to be edited with TCB - this uses a force_whitelist array key that will just return the posts editable with TCB when TU is installed
 *
 * @param array $post_types
 *
 * @return array
 */
function tve_ult_disable_tcb_edit( $post_types ) {
	$post_types['force_whitelist'] = isset( $post_types['force_whitelist'] ) ? $post_types['force_whitelist'] : array();
	$post_types['force_whitelist'] = array_merge( $post_types['force_whitelist'], array(
		TVE_Ult_Const::POST_TYPE_NAME_FOR_CAMPAIGN,
	) );

	return $post_types;
}