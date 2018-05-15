<?php

// this is the URL our updater / license checker pings. This should be the URL of the site with EDD installed
define( 'ADBC_EDD_STORE_URL', 'http://sigmaplugin.com' ); // you should use your own CONSTANT name, and be sure to replace it throughout this file

// the name of your product. This should match the download name in EDD exactly
define( 'ADBC_EDD_PLUGIN_NAME', 'WordPress Advanced Database Cleaner' ); // you should use your own CONSTANT name, and be sure to replace it throughout this file

if( !class_exists( 'EDD_SL_Plugin_Updater' ) ) {
	// load our custom updater
	include( dirname( __FILE__ ) . '/EDD_SL_Plugin_Updater.php' );
}

function aDBc_edd_sl_plugin_updater() {

	// retrieve our license key from the DB
	$license_key = trim( get_option( 'aDBc_edd_license_key' ) );

	// setup the updater
	$edd_updater = new EDD_SL_Plugin_Updater( ADBC_EDD_STORE_URL, ADBC_MAIN_PLUGIN_FILE_PATH, array(
			'version' 	=> ADBC_PLUGIN_VERSION, 	// current version number
			'license' 	=> $license_key, 			// license key (used get_option above to retrieve from DB)
			'item_name' => ADBC_EDD_PLUGIN_NAME, 	// name of this plugin
			'author' 	=> 'Younes JFR.'  			// author of this plugin
		)
	);

}
add_action( 'admin_init', 'aDBc_edd_sl_plugin_updater', 0 );

/************************************************
* the code below is just a standard options page.
************************************************/
function aDBc_edd_license_page() {
	$license 	= get_option( 'aDBc_edd_license_key' );
	$status 	= get_option( 'aDBc_edd_license_status' );
	?>
	<div class="aDBc-content-max-width">

		<form method="post" action="options.php">

			<?php settings_fields('aDBc_edd_license'); ?>

			<table class="form-table">
				<tbody>
					<tr>
						<td style="width:120px;font-size:13px">
							<b><?php _e('License Key'); ?></b>
						</td>
						<td width="350px">
							<input id="aDBc_edd_license_key" name="aDBc_edd_license_key" type="text" class="regular-text" value="<?php esc_attr_e( $license ); ?>" />
						</td>
						<td>
							<input type="submit" name="submit" id="submit" class="button button-primary" value="Save license key"  />
						</td>
					</tr>
					<?php if( false !== $license ) { ?>
						<tr>
							<td style="font-size:13px">
								<b><?php _e('License status'); ?></b>
							</td>
							<td>
								<?php if( $status !== false && $status == 'valid' ) { ?>
									<span style="color:green;font-size:13px; background:#eee;padding:4px 8px"><b><?php _e('Active'); ?></b></span>
									<?php wp_nonce_field( 'edd_sample_nonce', 'edd_sample_nonce' ); ?>
									<input style="vertical-align:middle" type="submit" class="button-secondary" name="edd_license_deactivate" value="<?php _e('Deactivate License'); ?>"/>
								<?php } else { ?>
									<span style="color:red;font-size:13px; background:#eee;padding:4px 8px"><?php _e('Inactive'); ?></span>
									<?php wp_nonce_field( 'edd_sample_nonce', 'edd_sample_nonce' ); ?>
									<input style="vertical-align:middle" type="submit" class="button-secondary" name="edd_license_activate" value="<?php _e('Activate License'); ?>"/>
								<?php } ?>
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>

		</form>
	</div>
	<?php
}

function aDBc_edd_register_option() {
	// creates our settings in the options table
	register_setting('aDBc_edd_license', 'aDBc_edd_license_key', 'aDBc_edd_sanitize_license' );
}
add_action('admin_init', 'aDBc_edd_register_option');

function aDBc_edd_sanitize_license( $new ) {
	$old = get_option( 'aDBc_edd_license_key' );
	if( $old && $old != $new ) {
		delete_option( 'aDBc_edd_license_status' ); // new license has been entered, so must reactivate
	}
	return $new;
}

/***********************************************
* Activate a license key
***********************************************/
function aDBc_edd_activate_license() {

	// listen for our activate button to be clicked
	if( isset( $_POST['edd_license_activate'] ) ) {

		// run a quick security check
	 	if( ! check_admin_referer( 'edd_sample_nonce', 'edd_sample_nonce' ) )
			return; // get out if we didn't click the Activate button

		// retrieve the license from the database
		$license = trim( get_option( 'aDBc_edd_license_key' ) );

		// data to send in our API request
		$api_params = array(
			'edd_action'=> 'activate_license',
			'license' 	=> $license,
			'item_name' => urlencode( ADBC_EDD_PLUGIN_NAME ), // the name of our product in EDD
			'url'       => home_url()
		);

		// Call the custom API.
		$response = wp_remote_post( ADBC_EDD_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) )
			return false;

		// decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		// $license_data->license will be either "valid" or "invalid"

		update_option( 'aDBc_edd_license_status', $license_data->license );

	}
}
add_action('admin_init', 'aDBc_edd_activate_license');

/***********************************************
* Deactivate a license key.
* This will descrease the site count
***********************************************/
function aDBc_edd_deactivate_license() {

	// listen for our activate button to be clicked
	if( isset( $_POST['edd_license_deactivate'] ) ) {

		// run a quick security check
	 	if( ! check_admin_referer( 'edd_sample_nonce', 'edd_sample_nonce' ) )
			return; // get out if we didn't click the Activate button

		// retrieve the license from the database
		$license = trim( get_option( 'aDBc_edd_license_key' ) );


		// data to send in our API request
		$api_params = array(
			'edd_action'=> 'deactivate_license',
			'license' 	=> $license,
			'item_name' => urlencode( ADBC_EDD_PLUGIN_NAME ), // the name of our product in EDD
			'url'       => home_url()
		);

		// Call the custom API.
		$response = wp_remote_post( ADBC_EDD_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) )
			return false;

		// decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		// $license_data->license will be either "deactivated" or "failed"
		if( $license_data->license == 'deactivated' )
			delete_option( 'aDBc_edd_license_status' );

	}
}
add_action('admin_init', 'aDBc_edd_deactivate_license');

/***********************************************
* Deactivate a license key after uninstall.
* This will descrease the site count
***********************************************/
function aDBc_edd_deactivate_license_after_uninstall() {

	// retrieve the license from the database
	$license = trim( get_option( 'aDBc_edd_license_key' ) );

	// data to send in our API request
	$api_params = array(
		'edd_action'=> 'deactivate_license',
		'license' 	=> $license,
		'item_name' => urlencode( ADBC_EDD_PLUGIN_NAME ), // the name of our product in EDD
		'url'       => home_url()
	);

	// Call the custom API.
	$response = wp_remote_post( ADBC_EDD_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );
}

/*****************************************************************************************
* Check if a license key is still valid (remote check)
*****************************************************************************************/
function aDBc_edd_check_license() {

	$license = trim( get_option( 'aDBc_edd_license_key' ) );

	$api_params = array(
		'edd_action' 	=> 'check_license',
		'license'		=> $license,
		'item_name' 	=> urlencode( ADBC_EDD_PLUGIN_NAME ),
		'url'       	=> home_url()
	);

	// Call the custom API.
	$response = wp_remote_post( ADBC_EDD_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

	if ( is_wp_error( $response ) )
		return false;

	$license_data = json_decode( wp_remote_retrieve_body( $response ) );

	if( $license_data->license == 'valid' ) {
		echo 'valid'; exit;
		// this license is still valid
	} else {
		echo 'invalid'; exit;
		// this license is no longer valid
	}
}

/*****************************************************************************************
* Check if a license is activated
*****************************************************************************************/
function aDBc_edd_is_license_activated() {
	$license_status = trim( get_option( 'aDBc_edd_license_status' ) );
	if( $license_status == 'valid' ) {
		return true;
	} else {
		return false;
	}
}
