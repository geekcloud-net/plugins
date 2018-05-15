<?php
/**
 * @package WPSEO_LOCAL\Admin
 *
 * @since 4.0
 */

if ( ! defined( 'WPSEO_LOCAL_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

if ( ! class_exists( 'WPSEO_Local_Admin_Wrappers' ) ) {

	/**
	 * Class WPSEO_Local_Wrappers contains wrapper methods to be sure WPSEO Local is backwards compatible with Yoast SEO
	 *
	 * @since 4.0
	 */
	class WPSEO_Local_Admin_Wrappers {

		/**
		 * Fallback for admin_header
		 *
		 * @param bool   $form             Whether or not the form start tag should be included.
		 * @param string $option_long_name Group name of the option.
		 * @param string $option           The short name of the option to use for the current page.
		 * @param bool   $contains_files   Whether the form should allow for file uploads.
		 *
		 * @return void|mixed
		 */
		public static function admin_header( $form = true, $option_long_name = 'yoast_wpseo_options', $option = 'wpseo', $contains_files = false ) {

			if ( method_exists( 'Yoast_Form', 'admin_header' ) ) {
				Yoast_Form::get_instance()->admin_header( $form, $option, $contains_files, $option_long_name );

				return;
			}

			echo self::admin_pages()->admin_header( true, 'yoast_wpseo_local_options', 'wpseo_local' );
		}

		/**
		 * Fallback for admin_footer
		 *
		 * @param bool $submit       Whether or not a submit button and form end tag should be shown.
		 * @param bool $show_sidebar Whether or not to show the banner sidebar - used by premium plugins to disable it.
		 *
		 * @return void|mixed
		 */
		public static function admin_footer( $submit = true, $show_sidebar = true ) {

			if ( method_exists( 'Yoast_Form', 'admin_footer' ) ) {
				Yoast_Form::get_instance()->admin_footer( $submit, $show_sidebar );

				return;
			}

			echo self::admin_pages()->admin_footer( $submit, $show_sidebar );
		}

		/**
		 * Fallback for the textinput method
		 *
		 * @param string       $var    The variable within the option to create the text input field for.
		 * @param string       $label  The label to show for the variable.
		 * @param string       $option The name of the used option.
		 * @param array|string $attr   Extra class to add to the input field.
		 *
		 * @return mixed|void
		 */
		public static function textinput( $var, $label, $option = '', $attr = array() ) {
			if ( method_exists( 'Yoast_Form', 'textinput' ) ) {
				if ( $option !== '' ) {
					Yoast_Form::get_instance()->set_option( $option );
				}

				Yoast_Form::get_instance()->textinput( $var, $label, $attr );

				return;
			}

			echo self::admin_pages()->textinput( $var, $label, $option );
		}

		/**
		 * Wrapper for select method.
		 *
		 * @param string $var    The variable within the option to create the select for.
		 * @param string $label  The label to show for the variable.
		 * @param array  $values The select options to choose from.
		 * @param string $option The name of the used option.
		 *
		 * @return void|mixed
		 */
		public static function select( $var, $label, $values, $option = '' ) {
			if ( method_exists( 'Yoast_Form', 'select' ) ) {
				if ( $option !== '' ) {
					Yoast_Form::get_instance()->set_option( $option );
				}

				Yoast_Form::get_instance()->select( $var, $label, $values );

				return;
			}

			echo self::admin_pages()->select( $var, $label, $values, $option );
		}

		/**
		 * Wrapper for checkbox method
		 *
		 * @param string $var        The variable within the option to create the checkbox for.
		 * @param string $label      The label to show for the variable.
		 * @param bool   $label_left Whether the label should be left (true) or right (false).
		 * @param string $option     The name of the used option.
		 *
		 * @return void|mixed
		 */
		public static function checkbox( $var, $label, $label_left = false, $option = '' ) {
			if ( method_exists( 'Yoast_Form', 'checkbox' ) ) {
				if ( $option !== '' ) {
					Yoast_Form::get_instance()->set_option( $option );
				}

				Yoast_Form::get_instance()->checkbox( $var, $label, $label_left );

				return;
			}

			echo self::admin_pages()->checkbox( $var, $label, $label_left, $option );
		}

		/**
		 * Create a hidden input field.
		 *
		 * @param string $var    The variable within the option to create the hidden input for.
		 * @param string $option The name of the used option.
		 *
		 * @return void|mixed
		 */
		public static function hidden( $var, $option = '' ) {
			if ( method_exists( 'Yoast_Form', 'hidden' ) ) {
				if ( $option !== '' ) {
					Yoast_Form::get_instance()->set_option( $option );
				}

				Yoast_Form::get_instance()->hidden( $var );

				return;
			}

			echo self::admin_pages()->hidden( $var, $option );

		}

		/**
		 * Create a upload field.
		 *
		 * @param string $var    The variable within the option to create the file upload field for.
		 * @param string $label  The label to show for the variable.
		 * @param string $option The option the variable belongs to.
		 *
		 * @return mixed|void
		 */
		public static function file_upload( $var, $label = '', $option = '' ) {
			if ( method_exists( 'Yoast_Form', 'file_upload' ) ) {
				if ( $option !== '' ) {
					Yoast_Form::get_instance()->set_option( $option );
				}

				Yoast_Form::get_instance()->file_upload( $var, $label );

				return;
			}

			echo self::admin_pages()->file_upload( $var, $label );

		}

		/**
		 * Returns the wpseo_admin pages global variable
		 *
		 * @return mixed
		 */
		private static function admin_pages() {
			global $wpseo_admin_pages;

			return $wpseo_admin_pages;
		}
	} /* End of class */

} /* End of class-exists wrapper */
