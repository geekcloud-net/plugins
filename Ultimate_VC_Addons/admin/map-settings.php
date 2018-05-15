<?php
	if(isset($_POST['submit-map-settings'])) {
		$map_key = sanitize_text_field( $_POST['map_key'] );
		$is_update = bsf_update_option( 'map_key', $map_key );
	}
?>

<div class="wrap about-wrap bsf-page-wrapper ultimate-about bend">
  <div class="wrap-container">
    <div class="bend-heading-section ultimate-header">
      <h1><?php _e( "Google Maps", "ultimate_vc" ); ?></h1>
      <h3><?php _e( "To use the advanced features our Google Maps element provides, an API key from Google is necessary.", "ultimate_vc" ); ?></h3>
      <div class="bend-head-logo">
        <div class="bend-product-ver">
          <?php _e( "Version", "ultimate_vc" ); echo ' '.ULTIMATE_VERSION; ?>
        </div>
      </div>
    </div><!-- bend-heading section -->

    <div class="row ultimate-content">
        	<div class="col-md-12">

				<div class="container bsf-grid-row bsf-grid-border-row">

						<div class="bsf-wrap-content">
				        	<form action="" method="post">
				        		<p><label><?php echo __('API Key', 'ultimate_vc'); ?></label>
				        			<?php $map_key = bsf_get_option('map_key'); ?>
				        			<input type="text" name="map_key" value="<?php echo esc_html( $map_key ); ?>"/></p>
				        		<p><?php echo __('Get your API Key ', 'ultimate_vc'); ?><a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank"><?php echo __( 'here', 'ultimate_vc' ); ?></a> or <?php echo __( 'read ', 'ultimate_vc' ); ?><a href="http://bsf.io/google-map-api-key" target="_blank"><?php echo __('this article', 'ultimate_vc'); ?></a><?php echo __(' for more information.', 'ultimate_vc'); ?></p>
				        		<p class="submit"><input type="submit" name="submit-map-settings" id="submit-map-settings" class="button button-primary" value="<?php echo __( 'Save Changes', 'ultimate_vc' ); ?>"></p>
				        	</form>
						</div><!--bsf wrap content-->

				</div><!--container end-->

        	</div><!--col-md-12-->
        </div><!-- .ultimate-content -->
    </div><!-- bend-content-wrap -->
  </div><!-- .wrap-container -->
</div><!-- .bend -->
