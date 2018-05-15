<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

?>
<div class="wrap">
    <h2 class="uk-heading-divider"><?php echo __("Connection", "woomelly"); ?></h2>
    <br>
    <?php if ( $l_is_ok['result'] ) { ?>
        <div style="padding-top: 15px;">
            <ul uk-tab>
                <li class="uk-active">
                    <a href="#"><?php echo __("Connection", "woomelly"); ?></a>
                </li>
                <li>
                    <a href="#"><?php echo __("Last Connection", "woomelly"); ?></a>
                </li>
            </ul>
            <ul class="uk-switcher uk-margin">
                <li>
                    <?php wm_print_alert(__('Once the synchronization starts, avoid reloading the page or closing the browser since it would interrupt the process in its entirety.', 'woomelly'), 'warning', false ); ?>
                    <div class="row center-xs" style="margin: 20px auto; padding: 50px 0px 50px 0px; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,.13); color: #555;">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <h3><?php echo __("Specify what type of synchronization you want to perform:", "woomelly"); ?></h3>
                        </div>
                        <?php if ( $woomelly_alive == true) { ?>
                            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                <button id="woomelly-sync-all-product" class="ladda-button" data-color="green" data-style="zoom-out" data-size="xl" style="width: 100%;">
                                    <span class="ladda-label"><span uk-icon="icon: cloud-upload; ratio: 2;"></span> <?php echo __("Woo - Meli", "woomelly"); ?> </span><span class="ladda-spinner"></span>
                                </button>
                            </div>
                        <?php } else { ?>
                            <div class="uk-alert-danger" style="background: #fef4f6; color: #f0506e; position: relative; padding: 15px 29px 15px 15px;">
                                <p style="font-size: 15px; font-weight: normal; line-height: 1.5; text-rendering: optimizeLegibility;">
                                    <?php echo sprintf( __( 'Excuse me, you have a connection problem with Mercadolibre. Verify that your website is %s', 'woomelly'), '<a href="'.admin_url( "admin.php?page=woomelly-settings" ).'" >'.__("connected and authorized correctly with Mercadolibre.", "woomelly").'</a>' ); ?>
                                </p>
                            </div>
                        <?php } ?>
                    </div>
                </li>
                <li>
                    <div class="uk-margin-small uk-card uk-card-default uk-card-body">
                        <div class="woomelly-spinner" id="woomelly-spinner-page-connect" style="text-align: right; display: none;">
                            <span class="uk-margin-small-right" uk-spinner></span>
                        </div>
                        <dl class="uk-description-list uk-description-list-divider" id="woomeli_description_log_last_sync">
                            <?php echo $file_last_sync_log; ?>
                        </dl>
                    </div>
                </li>
            </ul>
        </div>
    <?php } else {
            echo $l_is_ok['form'];
        } ?>
</div>
<script>
    var all_products = <?php echo json_encode($all_products); ?>;
    var total_products = all_products.length;
    var l = Ladda.create( document.querySelector( '#woomelly-sync-all-product' ) );
    jQuery('#woomelly-sync-all-product').click(function( e ) {
        e.preventDefault();
        jQuery( "#woomelly-import-all-product" ).prop( "disabled", true );
        jQuery( "#woomelly-spinner-page-connect" ).css( {"display": ""} );
        l.start();
        l.setProgress( 0 );
        var activeAjaxConnections = 0;
        var ii = 0;
        if ( ii <= total_products - 1 ) {
            jQuery("#woomeli_description_log_last_sync").empty();
            for ( ii = 0; ii <= total_products - 1; ii++ ) {
                var data = {
                    "action"    : "woomelly_do_sync_product",
                    "wm_id"     : all_products[ii],
                    "wm_type"   : "woomelly_manual"
                };
                jQuery.ajax({
                    beforeSend: function(xhr) {
                        activeAjaxConnections++;
                    },                
                    //async: false,    
                    //cache: false,                
                    type: 'POST',
                    url: ajaxurl,
                    data: data,
                    ajaxI: ii,
                    ajaxY: total_products,
                    success: function(response) {
                        activeAjaxConnections--;
                        ii = this.ajaxI;
                        total_products = this.ajaxY;
                        l.setProgress( (((ii*100)/total_products)/100)/1 );
                        jQuery("#woomeli_description_log_last_sync").append( response );
                        if (0 == activeAjaxConnections) {                            
                            swal( "<?php echo __('Process Completed!','woomelly'); ?>", "<?php echo __('See details in last connection tab.','woomelly'); ?>", "success" );
                            l.stop();
                            jQuery( "#woomelly-spinner-page-connect" ).css( {"display": "none"} );
                            //jQuery('#woomelly-import-all-product').prop( "disabled", false );
                        }

                    }, error: function(response) {
                        activeAjaxConnections--;
                        if (0 == activeAjaxConnections) {
                            swal( "<?php echo __('Process Not Completed!','woomelly'); ?>", "<?php echo __('There was a drawback that interrupted the process. Try again.','woomelly'); ?>", "error" );
                            l.stop();
                            jQuery( "#woomelly-spinner-page-connect" ).css( {"display": "none"} );
                            //jQuery('#woomelly-import-all-product').prop( "disabled", false );
                        }
                    }
                });
            }
        } else {
            swal( "<?php echo __('Cancelled!','woomelly'); ?>", "<?php echo __('There are no products configured to synchronize with Mercadolibre.','woomelly'); ?>", "error" );
            l.stop();
            jQuery( "#woomelly-spinner-page-connect" ).css( {"display": "none"} );
        }
    });
</script>