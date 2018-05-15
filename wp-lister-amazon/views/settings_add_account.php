<?php include_once( dirname(__FILE__).'/common_header.php' ); ?>

<style type="text/css">
	
	#AuthSettingsBox ol li {
		margin-bottom: 25px;
	}

</style>



				<form method="post" id="addAccountForm" action="<?php echo $wpl_form_action; ?>">
					<input type="hidden" name="action" value="wpla_add_account" >
                    <?php wp_nonce_field( 'wpla_add_account' ); ?>
					<input type="hidden" name="wpla_amazon_market_code" id="wpla_amazon_market_code" value="" >

					<div class="postbox" id="AddAccountBox">
						<h3 class="hndle"><span><?php echo __('Add Amazon Account','wpla') ?></span></h3>
						<div class="inside">

							<label for="wpla_account_title" class="text_label"><?php echo __('Title','wpla'); ?>:</label>
							<input type="text" name="wpla_account_title" value="<?php echo @$wpla_account_title ?>" class="text_input" />

							<label for="wpla-amazon_market_id" class="text_label"><?php echo __('Amazon Marketplace','wpla'); ?>:</label>
							<select id="wpla-amazon_market_id" name="wpla_amazon_market_id" title="Site" class=" required-entry select">
								<option value="">-- <?php echo __('Please select','wpla'); ?> --</option>
								<?php foreach ( $wpl_amazon_markets as $market ) : ?>
									<option 
										value="<?php echo $market->id ?>" 
										<?php if ( @$wpl_text_amazon_market_id == $market->id ): ?>selected="selected"<?php endif; ?>
										<?php if ( ! $market->enabled ): ?>disabled="disabled"<?php endif; ?>
										><?php echo $market->title ?> <?php if ( in_array( $market->code, array('IN','JP','CN','BR') ) ): ?>(not supported yet)<?php endif; ?></option>					
								<?php endforeach; ?>
							</select>

							<div id="wrap_account_details" style="display:none">

								<p style="padding-left:0.2em;">
									In order to add a new Amazon account to WP-Lister you need to:
									<ol>
										<li>Click on "Sign in with Amazon" and sign into your account.</li>
										<li>Go to the <a href="#" id="wpla_btn_userperms">User Permissions</a> page in Seller Central as the primary user.</li>
										<li>Under "Amazon MWS Developer Permissions" you can view your <strong>Seller ID</strong>. Copy and paste it in the field below.</li>
										<li>Click "View your credentials" to see your <strong>AWS Access Key ID</strong> and <strong>Secret Key</strong>. Copy and paste them in the corresponding fields below.</li>
										<li>Click "Add new account" to add the new account to WP-Lister.</li>
									</ol>
								
								</p>

								<!--
								<label for="wpla_application_name" class="text_label"><?php echo __('Application Name','wpla'); ?>:</label>
								<input type="text" name="wpla_application_name" id="wpla_application_name" value="WP-Lister for Amazon" disabled class="text_input disabled" />

								<label for="wpla_developer_account" class="text_label"><?php echo __('Developer Account Number','wpla'); ?>:</label>
								<input type="text" name="wpla_developer_account" id="wpla_developer_account" value="<?php echo @$wpla_developer_account ?>" disabled class="text_input disabled" />
								-->

								<label for="wpla_merchant_id" class="text_label"><?php echo __('Seller ID','wpla'); ?>:</label>
								<input type="text" name="wpla_merchant_id" id="wpla_merchant_id" value="<?php echo @$wpla_merchant_id ?>" class="text_input" />

								<label for="wpla_access_key_id" class="text_label"><?php echo __('AWS Access Key ID','wpla'); ?>:</label>
								<input type="text" name="wpla_access_key_id" id="wpla_access_key_id" value="<?php echo @$wpla_access_key_id ?>" class="text_input" />

								<label for="wpla_secret_key" class="text_label"><?php echo __('Secret Key','wpla'); ?>:</label>
								<input type="text" name="wpla_secret_key" id="wpla_secret_key" value="<?php echo @$wpla_secret_key ?>" class="text_input" />

								<!-- <label for="wpla_marketplace_id" class="text_label"><?php echo __('Marketplace ID','wpla'); ?>:</label> -->
								<input type="hidden" name="wpla_marketplace_id" id="wpla_marketplace_id" value="<?php echo @$wpla_marketplace_id ?>" class="" />

								<p>
								<a href="#" id="wpla_btn_add_account" class="button-secondary" style="float:left;">Add new account</a>
								<a href="#" id="wpla_btn_signin" class="button-primary" style="float:right;" target="_blank">Sign in with Amazon</a>
								</p>
								<br style="clear:both" />

							</div>

						</div>
					</div>


				</form>


	<div id="debug_output" style="display:none">
		<?php echo "<pre>";print_r($wpl_amazon_accounts);echo"</pre>"; ?>
	</div>

	<script type="text/javascript">

		function wpla_load_market_details( market_id ) {
	
	        // load market details
	        var params = {
	            action: 'wpla_load_market_details',
	            market_id: market_id,
	            nonce: 'TODO'
	        };
	        var jqxhr = jQuery.getJSON( ajaxurl, params )
	        .success( function( response ) { 

	            // set global queue
	            // jQuery('#wpla_developer_account').attr( 'value', response.developer_id );
	            jQuery('#wpla_amazon_market_code').attr( 'value', response.code );
	            jQuery('#wpla_marketplace_id').attr( 'value', response.marketplace_id );
	            jQuery('#wpla_btn_signin').attr( 'href', response.signin_url );
	            jQuery('#wpla_btn_userperms').attr( 'href', response.signin_url );

	        })
	        .error( function(e,xhr,error) { 
	            // alert( "There was a problem fetching the job list. The server responded:\n\n" + e.responseText ); 
	            console.log( "error", xhr, error ); 
	            console.log( e.responseText ); 
	            jQuery('#debug_output').html( e.responseText );
	        });

		}

		jQuery( document ).ready(
			function () {
		
				// amazon site selector during install: submit form on selection
				jQuery('#AddAccountBox #wpla-amazon_market_id').change( function(event, a, b) {					

					var market_id = event.target.value;
					if ( market_id ) {

						wpla_load_market_details( market_id );

						jQuery('#wrap_account_details').slideDown(300);
					} else {
						jQuery('#wrap_account_details').slideUp(300);						
					}
					
				});

				// add new account button
				jQuery('#wpla_btn_add_account').click( function() {					
					jQuery('#addAccountForm').first().submit();
					return false;
				});

				// jQuery('#ConnectionSettingsBox #wpla-amazon_market_id').change( function(event, a, b) {					
				// 	var market_id = event.target.value;
				// 	if ( market_id == '0') {
				// 		jQuery('#wrap_enable_xyz').slideDown(300);
				// 	} else {
				// 		jQuery('#wrap_enable_xyz').slideUp(300);						
				// 	}
				// });

				// confirm delete
				// jQuery('#delete_account').click( function() {					
				// 	return confirm('Do you really want to do this?');				
				// });


			}
		);
	
	</script>
