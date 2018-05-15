<?php
/**
 * WooCommerce Yoast SEO plugin file.
 *
 * @package WPSEO/WooCommerce
 */

/**
 * Class WPSEO_WooCommerce_Wrappers.
 */
class WPSEO_WooCommerce_Wrappers {

	/**
	 * Fallback for admin_header.
	 *
	 * @param bool   $form             Using a form or not.
	 * @param string $option_long_name The option long name.
	 * @param string $option           The option name.
	 * @param bool   $contains_files   If the form contains files.
	 *
	 * @return mixed
	 */
	public static function admin_header( $form = true, $option_long_name = 'yoast_wpseo_options', $option = 'wpseo', $contains_files = false ) {

		if ( method_exists( 'Yoast_Form', 'admin_header' ) ) {
			Yoast_Form::get_instance()->admin_header( $form, $option, $contains_files, $option_long_name );

			return;
		}

		return self::admin_pages()->admin_header( true, $option_long_name, $option );
	}

	/**
	 * Fallback for admin_footer.
	 *
	 * @param bool $submit       Show the submit button or not.
	 * @param bool $show_sidebar Show the sidebar or not.
	 *
	 * @return mixed
	 */
	public static function admin_footer( $submit = true, $show_sidebar = true ) {
		if ( method_exists( 'Yoast_Form', 'admin_footer' ) ) {
			Yoast_Form::get_instance()->admin_footer( $submit, $show_sidebar );

			return;
		}

		return self::admin_pages()->admin_footer( $submit, $show_sidebar );
	}

	/**
	 * Returns the wpseo_admin pages global variable.
	 *
	 * @return mixed
	 */
	private static function admin_pages() {
		global $wpseo_admin_pages;

		if ( ! $wpseo_admin_pages instanceof WPSEO_Admin_Pages ) {
			$wpseo_admin_pages = new WPSEO_Admin_Pages();
		}

		return $wpseo_admin_pages;
	}

	/**
	 * Returns the result of validate bool from WPSEO_Utils if this class exists, otherwise it will return the result from
	 * validate_bool from WPSEO_Option_Woo.
	 *
	 * @param mixed $bool_to_validate Variable to validate as bool.
	 *
	 * @return bool
	 */
	public static function validate_bool( $bool_to_validate ) {
		if ( class_exists( 'WPSEO_Utils' ) && method_exists( 'WPSEO_Utils', 'validate_bool' ) ) {
			return WPSEO_Utils::validate_bool( $bool_to_validate );
		}

		return WPSEO_Option_Woo::validate_bool( $bool_to_validate );
	}
}
