<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 3/6/2017
 * Time: 11:10 AM
 */

/**
 * @return array
 */
function tcb_admin_get_localization() {
	return array(
		'admin_nonce' => wp_create_nonce( TCB_Admin_Ajax::NONCE ),
		'dash_url'    => admin_url( 'admin.php?page=tve_dash_section' ),
		't'           => include tcb_admin()->admin_path( 'includes/i18n.php' ),
	);
}

/**
 * @param array $templates
 *
 * @return array
 */
function tcb_admin_get_category_templates( $templates = array() ) {
	$return         = array();
	$no_preview_img = tcb_admin()->admin_url( 'assets/images/no-template-preview.jpg' );
	foreach ( $templates as $key => $tpl ) {
		if ( empty( $tpl['image_url'] ) ) {
			$tpl['image_url'] = $no_preview_img;
		}
		if ( isset( $tpl['id_category'] ) && is_numeric( $tpl['id_category'] ) ) {
			if ( empty( $return[ $tpl['id_category'] ] ) ) {
				$return[ $tpl['id_category'] ] = array();
			}
			$return[ $tpl['id_category'] ][] = array_merge( array( 'id' => $key ), $tpl );
		} elseif ( isset( $tpl['id_category'] ) && $tpl['id_category'] === '[#page#]' ) {
			$return[ $tpl['id_category'] ][] = array_merge( array( 'id' => $key ), $tpl );
		} else {
			if ( empty( $return['uncategorized'] ) ) {
				$return['uncategorized'] = array();
			}
			$return['uncategorized'][] = array_merge( array( 'id' => $key ), $tpl );
		}
	}

	return $return;
}

/**
 * Displays an icon using svg format
 *
 * @param string $icon
 * @param bool   $return whether to return the icon as a string or to output it directly
 *
 * @return string|void
 */
function tcb_admin_icon( $icon, $return = false ) {
	$html = '<svg class="tcb-admin-icon tcb-admin-icon-' . $icon . '"><use xlink:href="#icon-' . $icon . '"></use></svg>';

	if ( false !== $return ) {
		return $html;
	}

	echo $html;
}
