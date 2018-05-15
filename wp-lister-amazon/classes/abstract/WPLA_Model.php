<?php

class WPLA_Model {
	
	public $result;
	
	// flexible object encoder
	static public function encodeObject( $obj ) {

		// prevent "Invalid UTF-8 sequence in argument" warning
		// $str = mb_check_encoding($str, 'UTF-8') ? $str : utf8_encode($str);

		$str = json_encode( $obj );
		// WPLA()->logger->info('json_encode - input: '.print_r($obj,1));
		// WPLA()->logger->info('json_encode - output: '.$str);
		// WPLA()->logger->info('json_last_error(): '.json_last_error() );

		if ( $str == '{}' ) return serialize( $obj );
		else return $str;
	}	
	
	// flexible object decoder
	public function decodeObject( $str, $assoc = false, $loadAmazonClasses = false ) {

		if ( $str == '' ) return false; 

		// json_decode
		$obj = json_decode( $str, $assoc );
		//WPLA()->logger->info('json_decode: '.print_r($obj,1));
		if ( is_object($obj) || is_array($obj) ) return $obj;
		
		// unserialize fallback
		$obj = maybe_unserialize( $str );
		//WPLA()->logger->info('unserialize: '.print_r($obj,1));
		if ( is_object($obj) || is_array($obj) ) return $obj;
		
		// mb_unserialize fallback
		$obj = $this->mb_unserialize( $str );
		// WPLA()->logger->info('mb_unserialize: '.print_r($obj,1));
		if ( is_object($obj) || is_array($obj) ) return $obj;

		// log error
		$e = new Exception;
		WPLA()->logger->error('backtrace: '.$e->getTraceAsString());
		WPLA()->logger->error('mb_unserialize returned: '.print_r($obj,1));
		WPLA()->logger->error('decodeObject() - not an valid object: '.$str);
		return $str;
	}	

	/**
	 * Multi-byte Unserialize
	 * UTF-8 will screw up a serialized string
	 * http://stackoverflow.com/questions/2853454/php-unserialize-fails-with-non-encoded-characters
	 * https://gist.github.com/rwarasaurus/f2abf620c7747c49119d
	 */
	function mb_unserialize($string) {

		// special handling for asterisk wrapped in zero bytes
	    $string = str_replace( "\0*\0", "*\0", $string);
		$string = preg_replace_callback('#s:\d+:"(.*?)";#s', function($matches) { return sprintf('s:%d:"%s";', strlen($matches[1]), $matches[1]); }, $string);
	    $string = str_replace('*\0', "\0*\0", $string);

	    return unserialize($string);
	}

	function is_ajax() {
		return defined( 'DOING_AJAX' ) && DOING_AJAX;
	}

	// check if given WordPress plugin is active
	public function is_plugin_active( $plugin ) {

		if ( is_multisite() ) {

			// check for network activation
			if ( ! function_exists( 'is_plugin_active_for_network' ) )
				require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

			if ( function_exists('is_plugin_active_for_network') && is_plugin_active_for_network( $plugin ) )
				return true;				

		}

    	return in_array( $plugin, (array) get_option( 'active_plugins', array() ) );
	}

	// custom mb_strlen implementation
	public function mb_strlen( $string ) {

		// use mb_strlen() if available
		if ( function_exists('mb_strlen') ) return mb_strlen( $string );

		// fallback if PHP was compiled without multibyte support
		$length = preg_match_all( '(.)su', $string, $matches );
    	return $length;

	}

	// custom mb_substr implementation
	public function mb_substr( $string, $start, $length ) {

		// use mb_substr() if available
		if ( function_exists('mb_substr') ) return mb_substr( $string, $start, $length );

		// fallback if PHP was compiled without multibyte support
		// $string = substr( $string, $start, $length );

		// snippet from http://www.php.net/manual/en/function.mb-substr.php#107698
	    $string = join("", array_slice( preg_split("//u", $string, -1, PREG_SPLIT_NO_EMPTY), $start, $length ) );

    	return $string;

	}


} // class WPLA_Model

