<?php include_once( dirname(__FILE__).'/common_header.php' ); ?>

<style type="text/css">

	th.column-details {
		width: 25%;
	}

</style>

<div class="wrap">
	<div class="icon32" style="background: url(<?php echo $wpl_plugin_url; ?>img/amazon-32x32.png) no-repeat;" id="wpl-icon"><br /></div>
	<h2><?php echo __('Profiles','wpla') ?></h2>
	<?php echo $wpl_message ?>


	<!-- show profiles table -->
    <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
    <form id="profiles-filter" method="post" action="<?php echo $wpl_form_action; ?>" >
        <!-- For plugins, we also need to ensure that the form posts back to our current page -->
        <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ) ?>" />
        <!-- Now we can render the completed list table -->
		<?php $wpl_profilesTable->search_box( __('Search','wpla'), 'profile-search-input' ); ?>
        <?php $wpl_profilesTable->display() ?>
    </form>

	<br style="clear:both;"/>

    <form id="profiles-addnew" method="get" action="<?php echo $wpl_form_action; ?>" style="display: inline;">
        <!-- For plugins, we also need to ensure that the form posts back to our current page -->
        <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ) ?>" />
        <input type="hidden" name="action" value="add_new_profile" />

		<input type="submit" value="<?php echo __('Add New Profile','wpla') ?>" name="submit" class="button">
    </form>

	<form id="profiles-upload" method="post" action="<?php echo $wpl_form_action; ?>" enctype="multipart/form-data" style="display: inline;">
        <a href="#" onclick="jQuery('#wpla_file_upload_profile').click();return false;" class="button-secondary">
        	<?php echo __('Upload Profile','wpla'); ?>
        </a> 

        <input type="hidden" name="action" value="wpla_upload_listing_profile" />
        <?php wp_nonce_field( 'wpla_upload_listing_profile' ); ?>
        <input type="file" id="wpla_file_upload_profile" name="wpla_file_upload_profile" onchange="this.form.submit();" style="display:none" />
    </form>
	<br style="clear:both;"/>

	<script type="text/javascript">
		jQuery( document ).ready(
			function () {
		
				// ask again before deleting
				jQuery('.row-actions .delete a').on('click', function() {
					return confirm("<?php echo __('Are you sure you want to delete this item?.','wpla') ?>");
				})
	
			}
		);
	
	</script>

</div>