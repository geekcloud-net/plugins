<style type="text/css">

</style>



					<div class="postbox" id="FeedTemplateBox">
						<h3 class="hndle"><span><?php echo __('Feed Template','wpla'); ?></span></h3>
						<div class="inside">

							<p>
								<?php echo __('Feed templates are required to list new products which do not exist on Amazon yet.','wpla'); ?>
								<?php echo __('Select a feed template to see all available options.','wpla'); ?>
							</p>

							<label for="wpl-text-tpl_id" class="text_label">
								<?php echo __('Feed Template','wpla'); ?>
                                <?php wpla_tooltip('Each main category on Amazon uses a different feed template with special fields for that particular category.<br><br>The Listing Loader template is not category specific, but it can be used only for products that already exist on Amazon.<br><br>Please select the right template for your category and marketplace / account.<br><br>Make sure all the required fields are filled in - or are populated from product details or attributes.') ?>
							</label>
							<select id="wpl-text-tpl_id" name="wpla_tpl_id" class="required-entry select">
							<option value="">-- <?php echo __('Select feed template','wpla') ?> --</option> 
							<optgroup label="Generic Feeds">
								<?php foreach ( $wpl_liloader_templates as $tpl ) : ?>
									<option value="<?php echo $tpl->id ?>" 
										<?php if ( $wpl_profile->tpl_id == $tpl->id ) : ?>
											selected="selected"
										<?php endif; ?>
										<?php $site = new WPLA_AmazonMarket( $tpl->site_id ); ?>
										><?php echo $tpl->title ?> (<?php echo $site ? $site->code : '?' ?>)</option>
								<?php endforeach; ?>
							</optgroup>
							<optgroup label="Category Specific Feeds">
								<?php foreach ( $wpl_category_templates as $tpl ) : ?>
									<option value="<?php echo $tpl->id ?>" 
										<?php if ( $wpl_profile->tpl_id == $tpl->id ) : ?>
											selected="selected"
										<?php endif; ?>
										<?php $site = new WPLA_AmazonMarket( $tpl->site_id ); ?>
										><?php echo $tpl->title ?> (<?php echo $site ? $site->code : '?' ?>)</option>
								<?php endforeach; ?>
							</optgroup>
							</select>
							<br class="clear" />
							<p class="desc" style="">
								<?php $link = sprintf( '<a href="%s">%s</a>', 'admin.php?page=wpla-settings&tab=categories', __('Amazon &raquo; Settings &raquo; Categories','wpla') ); ?>
								<?php echo sprintf( __('You can add additional feed templates at %s.','wpla'), $link ); ?>
							</p>

						</div>
					</div>

					<div class="postbox" id="FeedDataBox">
						<h3 class="hndle"><span><?php echo __('Feed Attributes','wpla'); ?></span></h3>
						<div class="inside">

							<p class="" style="">
								<i><?php echo __('No feed template selected.','wpla'); ?></i>
							</p>


						</div>
					</div>





	<script type="text/javascript">

		// load template data
		function loadTemplateData() {
			var tpl_id = jQuery('#wpl-text-tpl_id')[0].value;
			var profile_id = '<?php echo $wpl_profile->id ?>';
			var wpla_plugin_url = '<?php echo WPLA_URL ?>';

			// jQuery('#FeedDataBox .inside').slideUp(500);
			// jQuery('#FeedDataBox .loadingMsg').slideDown(500);

			var loading_img = '<img src="'+wpla_plugin_url+'img/ajax-loader.gif" class="loading"/>';
			jQuery('#FeedDataBox .inside').html( '<div style="text-align:center;">' + loading_img + '<p><i>loading feed template...</i></p></div>' );

	        // fetch category conditions
	        var params = {
	            action: 'wpla_load_template_data_for_profile',
	            id: tpl_id,
	            profile_id: profile_id,
	            nonce: 'TODO'
	        };

	        var jqxhr = jQuery('#FeedDataBox .inside').load( ajaxurl, params, function( response, status, xhr ) {
				if ( status == "error" ) {
			    	var msg = "Sorry but there was an error: ";
			    	jQuery( "#error" ).html( msg + xhr.status + " " + xhr.statusText );
			  	} else {
		
					// init tooltips
					jQuery("#FeedDataBox .help_tip").tipTip({
				    	'attribute' : 'data-tip',
				    	'maxWidth' : '250px',
				    	'fadeIn' : 50,
				    	'fadeOut' : 50,
				    	'delay' : 200
				    });

			  	}
			});
	        // console.log('jqxhr',jqxhr);

	        /*
	        var jqxhr = jQuery.getJSON( ajaxurl, params )
	        .success( function( response ) { 

	            // append to log
	            // console.log( 'response: ', response ); 
	            TemplateData = response;

	            // buildItemConditions();
				// jQuery('#FeedDataBox .inside').slideDown(500);
				// jQuery('#FeedDataBox .loadingMsg').slideUp(500);

	        })
	        .error( function(e,xhr,error) { 
	            console.log( "error", xhr, error ); 
	            console.log( e.responseText ); 
	        });			
			*/
		}


		// init 
		jQuery( document ).ready( function () {
			
			jQuery('#wpl-text-tpl_id').change(function() {
				if ( jQuery('#wpl-text-tpl_id').val() != '' ) {
					loadTemplateData();
				} else {
					jQuery('#wpl-text-fixed_price_container').hide();
				}
			});
			jQuery('#wpl-text-tpl_id').change();

		});	

	
	</script>
