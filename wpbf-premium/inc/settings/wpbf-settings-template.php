<?php
/**
 * Settings Page Template
 *
 * @package Ultimate Dashboard
 */
 
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

?>

<div class="wrap">

	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<?php
		$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'settings';
		if( isset( $_GET[ 'tab' ] ) ) {
			$active_tab = $_GET[ 'tab' ];
		}
	?>

	<!-- Tabs -->
	<h2 class="nav-tab-wrapper">
		<a href="?page=wpbf-premium&tab=settings" class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Settings', 'ryvpopup' ); ?></a>
		<a href="?page=wpbf-premium&tab=license" class="nav-tab <?php echo $active_tab == 'license' ? 'nav-tab-active' : ''; ?>"><?php _e( 'License', 'ryvpopup' ); ?></a>
	</h2>

	<form method="post" action="options.php">

		<?php 

		if ( $active_tab == 'settings' ) {

			settings_fields( 'wpbf-premium-group' );
			do_settings_sections( 'wpbf-premium-settings' );

		} elseif( $active_tab == 'license' ) {

			$license = get_option( 'wpbf_premium_license_key' );
			$status = get_option( 'wpbf_premium_license_status' );
			settings_fields('wpbf_premium_license');

		?>
		
		<div style="max-width: 700px; padding: 30px; background: #fff; margin-top: 20px;">

		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row" valign="top">
						<?php _e( 'License Key', 'wpbfpremium' ); ?>
					</th>
					<td>
						<input id="wpbf_premium_license_key" name="wpbf_premium_license_key" type="password" class="regular-text" value="<?php esc_attr_e( $license ); ?>" />
						<p class="description" for="wpbf_premium_license_key"><?php _e( 'Enter your Premium Add-On license key.', 'wpbfpremium' ); ?></p>
					</td>
				</tr>
				<?php if( false !== $license && "" !== $license ) { ?>
					<tr valign="top">
						<th scope="row" valign="top">
							<?php _e( 'Activate License', 'wpbfpremium' ); ?>
						</th>
						<td>
							<?php if( $status !== false && $status == 'valid' ) { ?>
								<span style="color:#fff; background:#6dbb7a; border: none; margin-right: 5px; cursor: auto;" class="button-secondary"><?php _e( 'active', 'wpbfpremium' ); ?></span>
								<?php wp_nonce_field( 'wpbf_premium_nonce', 'wpbf_premium_nonce' ); ?>
								<input type="submit" class="button-secondary" name="wpbf_premium_license_deactivate" value="<?php _e( 'Deactivate License', 'wpbfpremium' ); ?>"/>
							<?php } else {
								wp_nonce_field( 'wpbf_premium_nonce', 'wpbf_premium_nonce' ); ?>
								<input style="background:tomato; color: #fff; border: none;" type="submit" class="button-secondary" name="wpbf_premium_license_activate" value="<?php _e( 'Activate License', 'wpbfpremium' ); ?>"/>
							<?php } ?>
						</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>

		</div>

		<?php }

		submit_button(); ?>

	</form>

	<?php echo "Version " . WPBF_PREMIUM_VERSION; ?>

</div>