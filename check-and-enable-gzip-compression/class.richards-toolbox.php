<?php

class Richards_Toolbox {
	public $error;

	function checkGZIPCompression($url) {
		
		$api_url = 'https://checkgzipcompression.com/js/checkgzip.json?url=' . urlencode($url);
		$result = wp_remote_get($api_url);
		//disabled
		//$result = @file_get_contents($api_url);
		if(is_wp_error($result) || !isset($result['body'])) {
			if(is_wp_error($result)) {
				$this->error = $result;
			} else {
				$this->error = 'Unkown error';
			}
			return false;
		}
		$body = json_decode($result['body']);
		if(isset($body->error) && $body->error) {
			$this->error = $body->error;
			return false;
		}
		return $body;
	}

	function enableGZIPCompression() {
		update_option( 'richards-toolbox-gzip-enabled', 1 );
		if(@$_GET['apache'] == '1') {
			update_option( 'richards-toolbox-htaccess-enabled', 1 );
			add_filter('mod_rewrite_rules', 'richards_toolbox_addHtaccessContent');
			save_mod_rewrite_rules();
		} else {
			update_option( 'richards-toolbox-htaccess-enabled', 0 );
			remove_filter('mod_rewrite_rules', 'richards_toolbox_addHtaccessContent');
			save_mod_rewrite_rules();
		}
	}

	function disableGZIPCompression() {
		update_option( 'richards-toolbox-htaccess-enabled', 0 );
		update_option( 'richards-toolbox-gzip-enabled', 0 );
		remove_filter('mod_rewrite_rules', 'richards_toolbox_addHtaccessContent');
		save_mod_rewrite_rules();
	}
	function isApache() {
		return strpos(strtolower($_SERVER['SERVER_SOFTWARE']), 'apache') !== false;
	}

	function microtime_float()
	{
	    list($usec, $sec) = explode(" ", microtime());
	    return ((float)$usec + (float)$sec);
	}
}
