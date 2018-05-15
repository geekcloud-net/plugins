<?php
/**
 * @package Yoast\VideoSEO
 */

/**
 * Class WPSEO_Video_Wrappers
 *
 * @since 2.0.3
 */
class WPSEO_Video_Wrappers {

	/**
	 * Fallback function for WP SEO functionality, Validate INT
	 *
	 * @since 2.0.3
	 *
	 * @param int $integer Number to validate.
	 *
	 * @return mixed
	 */
	public static function yoast_wpseo_video_validate_int( $integer ) {
		// WPSEO 1.8+.
		if ( method_exists( 'WPSEO_Utils', 'validate_int' ) ) {
			return WPSEO_Utils::validate_int( $integer );
		}

		return WPSEO_Option::validate_int( $integer );
	}

	/**
	 * Fallback function for WP SEO functionality, is_url_relative
	 *
	 * @since 2.0.3
	 *
	 * @param string $url URL to check.
	 *
	 * @return mixed
	 */
	public static function yoast_wpseo_video_is_url_relative( $url ) {
		// WPSEO 1.6.1+.
		if ( method_exists( 'WPSEO_Utils', 'is_url_relative' ) ) {
			return WPSEO_Utils::is_url_relative( $url );
		}

		return wpseo_is_url_relative( $url );
	}

	/**
	 * Fallback for WP SEO functionality, sanitize_url
	 *
	 * @since 2.0.3
	 *
	 * @param string $string URL to check.
	 *
	 * @return mixed
	 */
	public static function yoast_wpseo_video_sanitize_url( $string ) {
		// WPSEO 1.8+.
		if ( method_exists( 'WPSEO_Utils', 'sanitize_url' ) ) {
			return WPSEO_Utils::sanitize_url( $string, array( 'http', 'https', 'ftp', 'ftps' ) );
		}

		return WPSEO_Option::sanitize_url( $string, array( 'http', 'https', 'ftp', 'ftps' ) );
	}

	/**
	 * Fallback for admin_header
	 *
	 * @since 2.0.3
	 *
	 * @param bool   $form             Form or not.
	 * @param string $option_long_name Full option name.
	 * @param string $option           Option name.
	 * @param bool   $contains_files   Contains file upload or not.
	 *
	 * @return mixed
	 */
	public static function admin_header( $form = true, $option_long_name = 'yoast_wpseo_options', $option = 'wpseo', $contains_files = false ) {
		// WPSEO 2.0+.
		if ( method_exists( 'Yoast_Form', 'admin_header' ) ) {
			Yoast_Form::get_instance()->admin_header( $form, $option, $contains_files, $option_long_name );

			return;
		}

		return self::admin_pages()->admin_header( true, $option_long_name, $option );
	}

	/**
	 * Fallback for admin_footer
	 *
	 * @since 2.0.3
	 *
	 * @param bool $submit       Submit button or not.
	 * @param bool $show_sidebar Show sidebar or not.
	 *
	 * @return mixed
	 */
	public static function admin_footer( $submit = true, $show_sidebar = true ) {
		// WPSEO 2.0+.
		if ( method_exists( 'Yoast_Form', 'admin_footer' ) ) {
			Yoast_Form::get_instance()->admin_footer( $submit, $show_sidebar );

			return;
		}

		return self::admin_pages()->admin_footer( $submit, $show_sidebar );
	}

	/**
	 * Fallback for the textinput method
	 *
	 * @since 2.0.3
	 *
	 * @param string $var    Variable.
	 * @param string $label  Label.
	 * @param string $option Option.
	 *
	 * @return mixed
	 */
	public static function textinput( $var, $label, $option = '' ) {
		// WPSEO 2.0+.
		if ( method_exists( 'Yoast_Form', 'textinput' ) ) {
			if ( $option !== '' ) {
				Yoast_Form::get_instance()->set_option( $option );
			}

			Yoast_Form::get_instance()->textinput( $var, $label );

			return;
		}

		return self::admin_pages()->textinput( $var, $label, $option );
	}

	/**
	 * Wrapper for select method.
	 *
	 * @since 2.0.3
	 *
	 * @param string $var    Variable.
	 * @param string $label  Label.
	 * @param array  $values Values.
	 * @param string $option Option.
	 */
	public static function select( $var, $label, $values, $option = '' ) {
		// WPSEO 2.0+.
		if ( method_exists( 'Yoast_Form', 'select' ) ) {
			if ( $option !== '' ) {
				Yoast_Form::get_instance()->set_option( $option );
			}

			Yoast_Form::get_instance()->select( $var, $label, $values );
			return;
		}

		return self::admin_pages()->select( $var, $label, $option );
	}

	/**
	 * Wrapper for checkbox method
	 *
	 * @since 2.0.3
	 *
	 * @param string $var        Variable.
	 * @param string $label      Label.
	 * @param bool   $label_left Label left or right.
	 * @param string $option     Option.
	 *
	 * @return mixed
	 */
	public static function checkbox( $var, $label, $label_left = false, $option = '' ) {
		// WPSEO 2.0+.
		if ( method_exists( 'Yoast_Form', 'checkbox' ) ) {
			if ( $option !== '' ) {
				Yoast_Form::get_instance()->set_option( $option );
			}

			Yoast_Form::get_instance()->checkbox( $var, $label, $label_left );
			return;
		}

		return self::admin_pages()->checkbox( $var, $label, $label_left, $option );
	}

	/**
	 * Returns the wpseo_admin pages global variable
	 *
	 * @since 2.0.3
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
	 * validate_bool from WPSEO_Option_Video
	 *
	 * @since 2.0.3
	 *
	 * @param bool $bool_to_validate Validate bool.
	 *
	 * @return bool
	 */
	public static function validate_bool( $bool_to_validate ) {
		// WPSEO 1.8+.
		if ( class_exists( 'WPSEO_Utils' ) && method_exists( 'WPSEO_Utils', 'validate_bool' ) ) {
			return WPSEO_Utils::validate_bool( $bool_to_validate );
		}

		return WPSEO_Option_Video::validate_bool( $bool_to_validate );
	}

	/**
	 * Wrapper function to check if we have a valid datetime.
	 *
	 * @since 2.0.3
	 * @since 4.1   Moved from the WPSEO_Video_Sitemap class to this one.
	 *
	 * @param string $datetime Date Time.
	 *
	 * @return bool
	 */
	public static function is_valid_datetime( $datetime ) {
		// WPSEO 2.0+.
		if ( method_exists( 'WPSEO_Utils', 'is_valid_datetime' ) ) {
			return WPSEO_Utils::is_valid_datetime( $datetime );
		}

		return true;
	}

	/**
	 * Call WPSEO_Sitemaps::register_sitemap() if the method exists.
	 *
	 * @since 4.1
	 *
	 * @param string   $name     The name of the sitemap.
	 * @param callback $function Function to build your sitemap.
	 * @param string   $rewrite  Optional. Regular expression to match your sitemap with.
	 */
	public static function register_sitemap( $name, $function, $rewrite = '' ) {
		// WPSEO 1.4.23+.
		if ( isset( $GLOBALS['wpseo_sitemaps'] ) && is_object( $GLOBALS['wpseo_sitemaps'] ) && method_exists( 'WPSEO_Sitemaps', 'register_sitemap' ) ) {
			$GLOBALS['wpseo_sitemaps']->register_sitemap( $name, $function, $rewrite );
		}
	}

	/**
	 * Call WPSEO_Sitemaps::register_xsl() if the method exists.
	 *
	 * @since 4.1
	 *
	 * @param string   $name     The name of the XSL file.
	 * @param callback $function Function to build your XSL file.
	 * @param string   $rewrite  Optional. Regular expression to match your sitemap with.
	 */
	public static function register_xsl( $name, $function, $rewrite = '' ) {
		// WPSEO 1.4.23+.
		if ( isset( $GLOBALS['wpseo_sitemaps'] ) && is_object( $GLOBALS['wpseo_sitemaps'] ) && method_exists( 'WPSEO_Sitemaps', 'register_xsl' ) ) {
			$GLOBALS['wpseo_sitemaps']->register_xsl( $name, $function, $rewrite );
		}
	}

	/**
	 * Call WPSEO_Sitemaps::set_sitemap() if the method exists.
	 *
	 * @since 4.1
	 *
	 * @param string $sitemap The generated sitemap to output.
	 */
	public static function set_sitemap( $sitemap ) {
		// WPSEO 1.4.23+.
		if ( isset( $GLOBALS['wpseo_sitemaps'] ) && is_object( $GLOBALS['wpseo_sitemaps'] ) && method_exists( 'WPSEO_Sitemaps', 'set_sitemap' ) ) {
			$GLOBALS['wpseo_sitemaps']->set_sitemap( $sitemap );
		}
	}

	/**
	 * Call WPSEO_Sitemaps::set_stylesheet() if the method exists.
	 *
	 * @since 4.1
	 *
	 * @param string $stylesheet Full xml-stylesheet declaration.
	 */
	public static function set_stylesheet( $stylesheet ) {
		if ( isset( $GLOBALS['wpseo_sitemaps'] ) && is_object( $GLOBALS['wpseo_sitemaps'] ) ) {

			// WPSEO 3.2+.
			if ( method_exists( 'WPSEO_Sitemaps_Renderer', 'set_stylesheet' ) && property_exists( $GLOBALS['wpseo_sitemaps'], 'renderer' ) && ( $GLOBALS['wpseo_sitemaps']->renderer instanceof WPSEO_Sitemaps_Renderer ) ) {
				$GLOBALS['wpseo_sitemaps']->renderer->set_stylesheet( $stylesheet );
				return;
			}

			// WPSEO 1.4.23+.
			if ( method_exists( $GLOBALS['wpseo_sitemaps'], 'set_stylesheet' ) ) {
				$GLOBALS['wpseo_sitemaps']->set_stylesheet( $stylesheet );
				return;
			}
		}
	}

	/**
	 * Call WPSEO_OpenGraph::image() if the method exists.
	 *
	 * @since 4.1
	 *
	 * @param string $image Image URL.
	 */
	public static function og_image_output( $image ) {
		if ( isset( $GLOBALS['wpseo_og'] ) && is_object( $GLOBALS['wpseo_og'] ) ) {

			// WPSEO 2.1.1 - image() method with new behaviour.
			if ( method_exists( 'WPSEO_OpenGraph', 'image' ) ) {
				$GLOBALS['wpseo_og']->image( $image );
			}
		}
	}

	/**
	 * Returns the result of WPSEO_Utils::is_development_mode() if the method exists.
	 *
	 * @since 4.1
	 *
	 * @return bool
	 */
	public static function is_development_mode() {
		// WPSEO 3.0+.
		if ( method_exists( 'WPSEO_Utils', 'is_development_mode' ) ) {
			return WPSEO_Utils::is_development_mode();
		}

		return false;
	}

	/**
	 * Returns the result of get_base_url from WPSEO_Sitemaps_Router if the method exists,
	 * otherwise it will return the result from the deprecated wpseo_xml_sitemaps_base_url() function.
	 *
	 * @since 4.1
	 *
	 * @param string $sitemap Sitemap file name.
	 *
	 * @return string
	 */
	public static function xml_sitemaps_base_url( $sitemap ) {
		// WPSEO 3.2+.
		if ( method_exists( 'WPSEO_Sitemaps_Router', 'get_base_url' ) ) {
			return WPSEO_Sitemaps_Router::get_base_url( $sitemap );
		}

		if ( function_exists( 'wpseo_xml_sitemaps_base_url' ) ) {
			return wpseo_xml_sitemaps_base_url( $sitemap );
		}
	}

	/**
	 * Call WPSEO_Sitemaps::ping_search_engines() if the method exists,
	 * otherwise it will call the deprecated wpseo_ping_search_engines() function.
	 *
	 * @since 4.1
	 *
	 * @param string $sitemapurl Sitemap URL.
	 *
	 * @return void
	 */
	public static function ping_search_engines( $sitemapurl = null ) {
		// WPSEO 3.2+.
		if ( method_exists( 'WPSEO_Sitemaps', 'ping_search_engines' ) ) {
			WPSEO_Sitemaps::ping_search_engines( $sitemapurl );
			return;
		}

		if ( function_exists( 'wpseo_ping_search_engines' ) ) {
			wpseo_ping_search_engines( $sitemapurl );
			return;
		}
	}

	/**
	 * Wrapper function to invalidate a cached sitemap.
	 *
	 * @since 4.1
	 *
	 * @param null|string $type The type to get the key for. Null for all caches.
	 *
	 * @return void
	 */
	public static function invalidate_cache_storage( $type = null ) {
		// WPSEO 3.2+.
		if ( method_exists( 'WPSEO_Sitemaps_Cache_Validator', 'invalidate_storage' ) ) {
			WPSEO_Sitemaps_Cache_Validator::invalidate_storage( $type );
			return;
		}

		// WPSEO 1.8.0+.
		if ( method_exists( 'WPSEO_Utils', 'clear_sitemap_cache' ) ) {
			WPSEO_Utils::clear_sitemap_cache( $type );
			return;
		}
	}

	/**
	 * Wrapper function to invalidate a sitemap type.
	 *
	 * @since 4.1
	 *
	 * @param string $type Sitemap type to invalidate.
	 *
	 * @return void
	 */
	public static function invalidate_sitemap( $type ) {
		// WPSEO 3.2+.
		if ( method_exists( 'WPSEO_Sitemaps_Cache', 'invalidate' ) ) {
			WPSEO_Sitemaps_Cache::invalidate( $type );
			return;
		}

		// WPSEO 1.5.4+.
		if ( function_exists( 'wpseo_invalidate_sitemap_cache' ) ) {
			wpseo_invalidate_sitemap_cache( $type );
			return;
		}
	}

	/**
	 * Call WPSEO_Sitemaps_Cache::register_clear_on_option_update() if the method exists,
	 * otherwise it will call the deprecated WPSEO_Utils::register_cache_clear_option() function.
	 *
	 * @since 4.1
	 *
	 * @param string $option Option name.
	 * @param string $type   Sitemap type.
	 *
	 * @return void
	 */
	public static function register_cache_clear_option( $option, $type = '' ) {
		// WPSEO 3.2+.
		if ( method_exists( 'WPSEO_Sitemaps_Cache', 'register_clear_on_option_update' ) ) {
			WPSEO_Sitemaps_Cache::register_clear_on_option_update( $option, $type );
			return;
		}

		// WPSEO 2.2+.
		if ( method_exists( 'WPSEO_Utils', 'register_cache_clear_option' ) ) {
			WPSEO_Utils::register_cache_clear_option( $option, $type );
			return;
		}
	}
}
