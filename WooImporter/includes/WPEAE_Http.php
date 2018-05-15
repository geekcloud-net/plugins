<?php

/**
 * Description of WPEAE_Http
 *
 * @author Geometrix
 */
if (!class_exists('WPEAE_Http')):

	class WPEAE_Http {
	

		public static function normalize_cookies( $cookies ) {
			$cookie_jar = new Requests_Cookie_Jar();

			foreach ( $cookies as $name => $value ) {
				if ( $value instanceof WPEAE_Http_Cookie ) {
					$cookie_jar[ $value->name ] = new Requests_Cookie( $value->name, $value->value, $value->get_attributes(), $value->get_flags() );
				} elseif ( is_scalar( $value ) ) {
					$cookie_jar[ $name ] = new Requests_Cookie( $name, $value );
				}
			}

			return $cookie_jar;
		}    
	}

endif;

