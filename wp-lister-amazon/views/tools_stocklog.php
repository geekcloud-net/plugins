<?php include_once( dirname(__FILE__).'/common_header.php' ); ?>

<style type="text/css">

	th.column-old_stock,
	th.column-new_stock {
		width: 8%;
	}
	th.column-timestamp,
	th.column-user {
		width: 12%;
	}
	th.column-method {
		width: 20%;
	}
	th.column-caller,
	th.column-sku {
		width: 15%;
	}

</style>

<div class="wrap">
	<div class="icon32" style="background: url(<?php echo $wpl_plugin_url; ?>img/amazon-32x32.png) no-repeat;" id="wpl-icon"><br /></div>
	<!-- <h2><?php echo __('Stock Log','wpla') ?></h2> -->

	<?php include_once( dirname(__FILE__).'/tools_tabs.php' ); ?>
	<?php echo $wpl_message ?>

	<!-- show listings table -->
	<?php $wpl_listingsTable->views(); ?>
    <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
    <form id="listings-filter" method="get" action="<?php echo $wpl_form_action; ?>" >
        <!-- For plugins, we also need to ensure that the form posts back to our current page -->
        <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ) ?>" />
        <input type="hidden" name="tab"  value="<?php echo esc_attr( $_REQUEST['tab'] ) ?>" />
        <!-- Now we can render the completed list table -->
		<?php $wpl_listingsTable->search_box( __('Search','wpla'), 'listing-search-input' ); ?>
        <?php $wpl_listingsTable->display() ?>
    </form>
	<br style="clear:both;"/>

	Current log size: <?php echo $wpl_tableSize ?> mb

	<script type="text/javascript">
		jQuery( document ).ready( function () {
		
			// init tooltips
			jQuery(".wide_error_tip").tipTip({
		    	'attribute' : 'data-tip',
		    	'maxWidth' : '100%',
		    	'fadeIn' : 50,
		    	'fadeOut' : 50,
		    	'delay' : 200
		    });

		});
	</script>

</div>