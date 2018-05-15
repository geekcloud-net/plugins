<?php include_once( dirname(__FILE__).'/common_header.php' ); ?>

<style type="text/css">

	th.column-details {
		width: 25%;
	}

</style>

<div class="wrap">
	<div class="icon32" style="background: url(<?php echo $wpl_plugin_url; ?>img/amazon-32x32.png) no-repeat;" id="wpl-icon"><br /></div>
	<h2>
		<?php echo __('Feeds','wpla') ?>
		<a href="<?php echo $wpl_form_action; ?>" class="add-new-h2">Refresh</a>
	</h2>
	<?php echo $wpl_message ?>


	<!-- show feed table -->
	<?php $wpl_feedsTable->views(); ?>

    <form id="feeds-filter" method="get" action="<?php echo $wpl_form_action; ?>" >
        <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ) ?>" />
		<?php $wpl_feedsTable->search_box(__('Search','wpla'), 'feed-search-input'); ?>
        <input type="hidden" name="paged" value="<?php echo isset( $_REQUEST['paged'] ) ? $_REQUEST['paged'] : '-1' ?>" />
        <input type="hidden" name="feed_status" value="<?php echo isset( $_REQUEST['feed_status'] ) ? $_REQUEST['feed_status'] : '' ?>" />
        <input type="hidden" name="account_id" value="<?php echo isset( $_REQUEST['account_id'] ) ? $_REQUEST['account_id'] : '' ?>" />
    </form>

    <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
    <form id="profiles-filter" method="post" action="<?php echo $wpl_form_action; ?>" >
        <!-- For plugins, we also need to ensure that the form posts back to our current page -->
        <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ) ?>" />
        <!-- Now we can render the completed list table -->
        <?php $wpl_feedsTable->display() ?>
    </form>

	<br style="clear:both;"/>


	<form method="post" action="<?php echo $wpl_form_action; ?>">
		<div class="submit" style="padding-top: 0; float: left;">
			<?php wp_nonce_field( 'wpla_update_feeds' ); ?>
			<input type="hidden" name="action" value="wpla_update_feeds" />
			<input type="submit" value="<?php echo __('Update feeds','wpla') ?>" name="submit" class="button-secondary"
				   title="<?php echo __('Update recent feeds from Amazon.','wpla') ?>">
		</div>
	</form>

	<form method="post" action="<?php echo $wpl_form_action; ?>">
		<div class="submit" style="padding-top: 0; float: left;">
			<?php wp_nonce_field( 'wpla_submit_pending_feeds' ); ?>
			<input type="hidden" name="action" value="submit_pending_feeds_to_amazon" />
			<input type="submit" value="<?php echo __('Submit pending feeds','wpla') ?>" name="submit" class="button-secondary"
				   title="<?php echo __('Submit all pending feeds to Amazon.','wpla') ?>">
	
		</div>
	</form>
	<br style="clear:both;"/>


</div>


<script type="text/javascript">
	/*
    function wpla_hide_empty_table_columns() {

        var Table      = jQuery('#wpla_feed_data_table').first();
        var Columns    = jQuery('#wpla_feed_data_table th');
        var Rows       = jQuery('#wpla_feed_data_table td');
        var key        = '';
        var has_values = null;

        // loop columns
        Columns.each(function( i ) {

			key        = jQuery(this).attr('class');
			has_values = false;

            // check all fields in this column
			jQuery('#wpla_feed_data_table td.'+key).each(function( i ) {

                field_content = jQuery(this).html();
                console.log('field_content', field_content, field_content.length );

                if ( field_content.length > 0 ) {
                	has_values = true;                	
                }

            });

			// hide column if empty
			if ( ! has_values ) {
				jQuery('#wpla_feed_data_table .'+key).toggle();
			}

            // console.log('key', key );
            // console.log('has_values', has_values );

        }); // each column
    };
    */
</script>

