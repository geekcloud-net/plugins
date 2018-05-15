<?php /* inspired by Kaspars Dambis */

class WPL_CustomUpdater {
	
	var $api_url = 'http://update.wplab.de/api/';
	var $plugin_slug;
	var $api_key;
	var $domain;
	
	public function __construct() {
		
		// set plugin slug
		$this->plugin_slug = basename( WPLISTER_PATH );

		// hook into update check
		add_filter( 'pre_set_site_transient_update_plugins', array(&$this,'check_for_plugin_update') );

		// hook into plugin info screen
		if ( isset($_GET['plugin']) && ( $_GET['plugin'] == $this->plugin_slug ) ) {
			add_filter( 'plugins_api', array(&$this,'plugin_api_call'), 10, 3 );
		}

	}
		
	public function check_for_new_version() {

		$request_args = array(
			'slug' => $this->plugin_slug,
			'version' => WPLISTER_VERSION,
		);

		$request_string = $this->wpl_prepare_request( 'basic_check', $request_args );

		// Start checking for an update
		$raw_response = wp_remote_post( $this->api_url, $request_string );

		if ( !is_wp_error( $raw_response ) && ( wp_remote_retrieve_response_code( $raw_response ) == 200 ) ) {
			
			$response = unserialize( wp_remote_retrieve_body( $raw_response ) );
	
			if ( is_object( $response ) && !empty( $response ) ) {

				// store update data for later use
				$response->timestamp = time();
				update_option( 'wplister_update_details', $response );

				return $response;
			} else {

				// empty result means no new version
				$update = get_option( 'wplister_update_details', new stdClass() );
				$update->timestamp = time();
				$update->new_version = WPLISTER_VERSION;
				update_option( 'wplister_update_details', $update );

			}
		}

		return false;
	}
		
	public function check_for_plugin_update( $checked_data ) {

		if ( empty( $checked_data->checked ) )
			return $checked_data;

		$request_args = array(
			'slug' => $this->plugin_slug,
			'version' => $checked_data->checked[$this->plugin_slug .'/'. $this->plugin_slug .'.php'],
		);

		$request_string = $this->wpl_prepare_request( 'basic_check', $request_args );

		// Start checking for an update
		$raw_response = wp_remote_post( $this->api_url, $request_string );

		if ( !is_wp_error( $raw_response ) && ( wp_remote_retrieve_response_code( $raw_response ) == 200 ) ) {
			
			$response = unserialize( wp_remote_retrieve_body( $raw_response ) );
	
			if ( is_object( $response ) && !empty( $response ) ) {

				// Feed the update data into WP updater
				$checked_data->response[$this->plugin_slug .'/'. $this->plugin_slug .'.php'] = $response;
			}
		}

		// WPLE()->logger->info('check_for_plugin_update('.$this->plugin_slug.'): '.$this->api_url);
		// WPLE()->logger->info('raw_response: '.print_r($raw_response,1));
		// WPLE()->logger->info('response: '.print_r($response,1));
		// WPLE()->logger->info('checked_data: '.print_r($checked_data,1));

		return $checked_data;
	}


	public function plugin_api_call( $def, $action, $args ) {

		if ( @$args->slug != $this->plugin_slug )
			return false;

		// Get the current version
		// $plugin_info = get_site_transient( 'update_plugins' );
		// $current_version = $plugin_info->checked[$this->plugin_slug .'/'. $this->plugin_slug .'.php'];
		$args->version = WPLISTER_VERSION;

		$request_string = $this->wpl_prepare_request( $action, $args );

		$request = wp_remote_post( $this->api_url, $request_string );

		if ( is_wp_error( $request ) ) {
			$res = new WP_Error( 'plugins_api_failed', ( 'An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>' ), $request->get_error_message() );
		} else {
			$response_body = wp_remote_retrieve_body( $request );
			$res = unserialize(  $response_body );

			if ( $res === false )
				$res = new WP_Error( 'plugins_api_failed', __( 'An unknown error occurred','wplister' ), $response_body );
		}

		return $res;
	}


	public function wpl_prepare_request( $action, $args, $new_license_key = false ) {
		global $wp_version;

		// additional arguments
		// if ( is_array( $args ) ) {
		// 	$args['channel'] = get_option('wplister_update_channel');
		// } else {
		// 	$args->channel = get_option('wplister_update_channel');
		// }

		// set host domain domain
		// $url = parse_url( get_bloginfo( 'url' ) );
		$url = parse_url( get_option( 'home' ) );
		$this->domain = $url['host'];
		
		// set api-key
		$license_key = $new_license_key ? $new_license_key : get_option('wplister_license_key');
		$this->api_key = md5( $license_key . $this->domain );
		# debug:
		// $this->api_key = ( $license_key . $this->domain );

		// no license key should result in empty api key
		if ( trim($license_key) == '' ) $this->api_key = '';

		return array(
			'body' => array(
				'action'  => $action,
				'request' => serialize( $args ),
				'domain'  => $this->domain,
				'channel' => get_option('wple_update_channel','stable'),
				'api-key' => $this->api_key
			),
			'user-agent' => 'WordPress/' . $wp_version . ' ('.ProductWrapper::plugin.'); ' . get_bloginfo( 'url' )
		);
	}

	public function activate_license( $license_key, $email ) {
		global $wp_version;
	
		// Get the current version
		// $plugin_info = get_site_transient( 'update_plugins' );
		// $current_version = $plugin_info->checked[$this->plugin_slug .'/'. $this->plugin_slug .'.php'];

		$request_args = array(
			'slug'        => $this->plugin_slug,
			'version'     => WPLISTER_VERSION,
			'email'       => $email,
			'license_key' => $license_key
		);

		$request_string = $this->wpl_prepare_request( 'activate_license', $request_args, $license_key );

		$request = wp_remote_post( $this->api_url, $request_string );
		// echo "<pre>";print_r( $request );echo "</pre>";

		if ( is_wp_error( $request ) ) {
			$error_string = $request->get_error_message();
   			echo '<div id="message" class="error" style="display:block !important;"><p>' . $error_string . '</p></div>';
			print_r( $request );
			$res = new WP_Error( 'activate_license_failed', ( 'An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>' ), $request->get_error_message() );
		} else {
			$response_body = wp_remote_retrieve_body( $request );
			$res = @json_decode( $response_body );
			// print_r( $res );

			// activation successful
			if ( @$res->activated === true )
				return true;

			if ( $res === false )
				$res = new WP_Error( 'activate_license_failed', __( 'An unknown error occurred','wplister' ), $response_body );
		}

		return $res;

	}

	public function deactivate_license( $license_key, $email ) {
		global $wp_version;
	
		// Get the current version
		// $plugin_info = get_site_transient( 'update_plugins' );
		// $current_version = $plugin_info->checked[$this->plugin_slug .'/'. $this->plugin_slug .'.php'];

		$request_args = array(
			'slug'        => $this->plugin_slug,
			'version'     => WPLISTER_VERSION,
			'email'       => $email,
			'license_key' => $license_key
		);

		$request_string = $this->wpl_prepare_request( 'deactivate_license', $request_args );

		$request = wp_remote_post( $this->api_url, $request_string );
		// echo "<pre>";print_r( $request );echo "</pre>";

		if ( is_wp_error( $request ) ) {
			$error_string = $request->get_error_message();
   			echo '<div id="message" class="error" style="display:block !important;"><p>' . $error_string . '</p></div>';
			// echo "<pre>";print_r($request);echo"</pre>";#die();
			$res = new WP_Error( 'deactivate_license_failed', ( 'An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>' ), $request->get_error_message() );
		} else {
			$response_body = wp_remote_retrieve_body( $request );
			$res = @json_decode( $response_body );
			// echo "<pre>";print_r($request);echo"</pre>";#die();
			// echo "<pre>";print_r($res);echo"</pre>";#die();

			// deactivation successful
			if ( @$res->reset === true )
				return true;

			if ( $res === false ) {
				$res = new WP_Error( 'deactivate_license_failed', __( 'An unknown error occurred','wplister' ), $response_body );
			}
			
			
			// fallback to return raw request body
			if ( ! $res ) $res = $response_body;

		}

		return $res;

	}

	public function check_license( $license_key, $email ) {
		global $wp_version;
	
		// Get the current version
		// $plugin_info = get_site_transient( 'update_plugins' );
		// $current_version = $plugin_info->checked[$this->plugin_slug .'/'. $this->plugin_slug .'.php'];

		$request_args = array(
			'slug'        => $this->plugin_slug,
			'version'     => WPLISTER_VERSION,
			'email'       => $email,
			'license_key' => $license_key
		);

		$request_string = $this->wpl_prepare_request( 'check_license', $request_args, $license_key );

		$request = wp_remote_post( $this->api_url, $request_string );
		// echo "<pre>";print_r( $request );echo "</pre>";

		if ( is_wp_error( $request ) ) {
			$error_string = $request->get_error_message();
   			echo '<div id="message" class="error" style="display:block !important;"><p>' . $error_string . '</p></div>';
			print_r( $request );
			$res = new WP_Error( 'check_license_failed', ( 'An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>' ), $request->get_error_message() );
		} else {
			$response_body = wp_remote_retrieve_body( $request );
			$res = @json_decode( $response_body );
			// print_r( $res );

			// activation successful
			if ( @$res->success === true )
				return true;

			if ( $res === false )
				$res = new WP_Error( 'check_license_failed', __( 'An unknown error occurred','wplister' ), $response_body );
		}

		return $res;

	}


	
}

// instantiate object
$WPL_CustomUpdater = new WPL_CustomUpdater();


/*
// TEMP: Enable update check on every request. Normally you don't need this! This is for testing only!
set_site_transient('update_plugins', null);

// TEMP: Show which variables are being requested when query plugin API
add_filter('plugins_api_result', 'aaa_result', 10, 3);
function aaa_result($res, $action, $args) {
	print_r($res);
	return $res;
}
*/


