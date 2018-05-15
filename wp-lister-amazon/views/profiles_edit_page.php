<?php include_once( dirname(__FILE__).'/common_header.php' ); ?>

<style type="text/css">

	.postbox h3 {
	    cursor: default;
	}

	#amazon_categories_tree_wrapper {
		/*max-height: 320px;*/
		/*margin-left: 35%;*/
		overflow: auto;
		width: 65%;
		display: none;
	}

</style>

<?php
	// echo "<pre>";print_r($wpl_profile);echo"</pre>";#die();
	$profile_data = $wpl_profile->data;
	$field_data = $wpl_profile->fields;
?>

<div class="wrap amazon-page">
	<div class="icon32" style="background: url(<?php echo $wpl_plugin_url; ?>img/amazon-32x32.png) no-repeat;" id="wpl-icon"><br /></div>
	<?php if ( $wpl_profile->id ): ?>
	<h2><?php echo __('Edit Profile','wpla') ?></h2>
	<?php else: ?>
	<h2><?php echo __('New Profile','wpla') ?></h2>
	<?php endif; ?>
	
	<?php echo $wpl_message ?>

	<form method="post" action="<?php echo $wpl_form_action; ?>">

	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">

			<div id="postbox-container-1" class="postbox-container">
				<div id="side-sortables" class="meta-box">
					<?php include('profile/edit_sidebar.php') ?>
				</div>
			</div> <!-- #postbox-container-1 -->


			<!-- #postbox-container-2 -->
			<div id="postbox-container-2" class="postbox-container">
				<div class="meta-box-sortables ui-sortable">
					

					<div class="postbox" id="GeneralSettingsBox">
						<h3 class="hndle"><span><?php echo __('General','wpla'); ?></span></h3>
						<div class="inside">

							<div id="titlediv" style="margin-bottom:5px;">
								<div id="titlewrap">
									<label class="text_label"><?php echo __('Profile name','wpla'); ?> *</label>
									<input type="text" name="wpla_profile_name" size="30" value="<?php echo $wpl_profile->profile_name; ?>" id="title" autocomplete="off" style="width:65%;">
								</div>
							</div>

							<label for="wpl-text-profile_description" class="text_label">
								<?php echo __('Profile description','wpla'); ?>
                                <?php wpla_tooltip('A profile description is optional and only used within WP-Lister.') ?>
							</label>
							<input type="text" name="wpla_profile_description" id="wpl-text-profile_description" value="<?php echo str_replace('"','&quot;', $wpl_profile->profile_description ); ?>" class="text_input" />
							<br class="clear" />

						</div>
					</div>


					<?php include('profile/edit_template_data.php') ?>


					<div class="submit" style="padding-top: 0; float: right; display:none;">
						<input type="submit" value="<?php echo __('Save profile','wpla'); ?>" name="submit" class="button-primary">
					</div>
						
				</div> <!-- .meta-box-sortables -->
			</div> <!-- #postbox-container-1 -->



		</div> <!-- #post-body -->
		<br class="clear">
	</div> <!-- #poststuff -->

	</form>

	<!-- hidden ajax categories tree -->
	<div id="amazon_categories_tree_wrapper">
		<div id="amazon_categories_tree_container">TEST</div>
	</div>


	<?php if ( get_option('wpla_log_level') > 6 ): ?>
	<pre><?php print_r($wpl_profile); ?></pre>
	<?php endif; ?>


	<script type="text/javascript">

		jQuery( document ).ready(
			function () {

				// enable chosen.js
				// jQuery("select.chosen_select").chosen();
				

			    // 
			    // Validation
			    // 
				// check required values on submit
				jQuery('.amazon-page form').on('submit', function() {
					
					// country required
					// if ( jQuery('#wpl-text-country')[0].value == '' ) {
					// 	alert('Please select a country.'); return false;
					// }

					// profile name is required
					var wpla_profile_name = jQuery("input[name='wpla_profile_name']")[0].value;
					if( wpla_profile_name.length == 0){
						alert('Please enter a name for this profile.'); return false;
					}

					// account is required
					var wpla_account_id = jQuery("input[name='wpla_account_id']");
					if( wpla_account_id.filter(':checked').length == 0){
						alert('Please select an account.'); return false;
					}

					// check required rows
					var missing_required_fields = false;
					jQuery('.wpla_actually_required_row').css('background-color','transparent');
					// check input fields
					jQuery('.wpla_actually_required_row').find('input').each( function(index, field) {
						// console.log(field.value);
						if ( ! field.value ) {
							jQuery(field).parent().parent().css('background-color','#ffeeee');
							missing_required_fields = true;
						}
					});
					// check select fields
					jQuery('.wpla_actually_required_row').find('select').each( function(index, field) {
						// console.log(field.value);
						if ( ! field.value ) {
							jQuery(field).parent().parent().css('background-color','#ffeeee');
							missing_required_fields = true;
						}
					});
					if (missing_required_fields) {
						alert('Please fill in all required fields.'); 
						return false;						
					}

					return true;
				})





				// jqueryFileTree - amazon categories / browse tree guide
			    jQuery('#amazon_categories_tree_container').fileTree({
			        root: '/0/',
			        script: ajaxurl+'?action=wpla_get_amazon_categories_tree',
			        expandSpeed: 400,
			        collapseSpeed: 400,
			        loadMessage: 'loading browse tree guide...',
			        multiFolder: false
			    }, function(catpath) {

					console.log('catpath: ',catpath);

					// get cat id from full path
			        var cat_id = catpath.split('/').pop(); // get last item - like php basename()

			        var cat_array = catpath.split('/');
			        if ( cat_array[ cat_array.length - 1 ] == '' ) {
			        	cat_id = cat_array[ cat_array.length - 2 ];
			        }

			        // get name of selected category
			        // var cat_name = '';

			        // var pathname = wpl_getCategoryPathName( catpath.split('/') );
			        // var pathname = catpath;
					console.log('cat_id: ',cat_id);

					// insert shortcode / value
					wpla_insert_selected_browse_node( cat_id );

			        // update fields
			        // jQuery('#amazon_category_id_'+wpla_selecting_cat).attr( 'value', cat_id );
			        // jQuery('#amazon_category_name_'+wpla_selecting_cat).html( pathname );
			        
			        // close thickbox
			        // tb_remove();


			    });
	



			}
		);

	</script>

</div>



	
