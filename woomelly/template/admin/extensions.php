<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

?>
<div class="wrap">
    <h2 class="uk-heading-divider"><?php echo __("Extensions", "woomelly"); ?></h2>
    <br>
    <?php if ( $l_is_ok['result'] ) { ?>
        <div style="padding-top: 15px;" id="wm-page-extensions">
            <?php wm_print_alert(__('At this moment the sales module is in test mode (BETA) since we are presenting problems with Mercadolibre and sales are duplicated in countries where the shopping cart is implemented. If you want to use it in test mode, you can activate it and inactivate it in this same page. Sorry for the inconvenience caused. Any questions can write to makeplugins@gmail.com.', 'woomelly'), 'danger', false ); ?>
            <div class="uk-margin-small uk-card uk-card-default uk-card-body">
                <dl class="uk-description-list uk-description-list-divider">
                    <dt><?php if ( $settings_extensions['order'] ) {
                        echo sprintf( __("Manage Sales %s", "woomelly"), '<button class="wm-extension-active uk-button uk-button-primary uk-align-right" data-wmext="order">'.__("ON", "woomelly").'</button>' );
                    } else {
                        echo sprintf( __("Manage Sales %s", "woomelly"), '<button class="wm-extension-active uk-button uk-button-default uk-align-right" data-wmext="order">'.__("OFF", "woomelly").'</button>' );
                    } ?>
                    </dt>
                    <dd><?php echo __("An order is a request a customer places on a listed item with the intention to purchase it under a series of conditions he/she will choose throughout the checkout flow.", "woomelly"); ?></dd>
                    <dt><?php if ( $settings_extensions['feedback'] ) {
                        echo sprintf( __("Manage feedback %s", "woomelly"), '<button class="wm-extension-active uk-button uk-button-primary uk-align-right" data-wmext="feedback">'.__("ON", "woomelly").'</button>' );
                    } else {
                        echo sprintf( __("Manage feedback %s", "woomelly"), '<button class="wm-extension-active uk-button uk-button-default uk-align-right" data-wmext="feedback">'.__("OFF", "woomelly").'</button>' );
                    } ?>
                    </dt>
                    <dd><?php echo __("According to MercadoLibres business rules, after the sale (or purchase) is complete, the buyer and the seller must provide feedback about the transaction and rate each other. Buyers and sellers build their reputations based on their trading partners ratings.", "woomelly"); ?></dd>
                </dl>
            </div>
        </div>
    <?php } else {
            echo $l_is_ok['form'];
        } ?>
</div>
<script>
    jQuery( ".wm-extension-active" ).click(function() {
        wm_waiting( "#wm-page-extensions");
        var wmext = jQuery(this).data("wmext");
        var wmvalue = jQuery(this).text();
        if ( jQuery(this).hasClass( "uk-button-primary" ) ) {
            jQuery(this).text('<?php echo __("OFF", "woomelly"); ?>');
            jQuery(this).removeClass( "uk-button-primary" ).addClass( "uk-button-default" );
        } else {
            jQuery(this).text('<?php echo __("ON", "woomelly"); ?>');
            jQuery(this).removeClass( "uk-button-default" ).addClass( "uk-button-primary" );
        }
        var data = {
            "action"    : "woomelly_do_extension",
            "wmext"     : wmext,
            "wmvalue"   : wmvalue,
        };
        jQuery.post(ajaxurl, data, function(response) {
            jQuery( "#wm-page-extensions" ).waitMe( "hide" );
            if ( response == "success" ) {
                UIkit.notification({message: '<?php echo __("Success!", "woomelly"); ?>', status: 'success', pos: 'bottom-right'});
            } else {
                UIkit.notification({message: '<?php echo __("Error!", "woomelly"); ?>', status: 'danger', pos: 'bottom-right'});
            }
        }).error(function(data){
            jQuery( "#wm-page-extensions" ).waitMe( "hide" );
            UIkit.notification({message: '<?php echo __("Error!", "woomelly"); ?>', status: 'danger', pos: 'bottom-right'});
        });
    });
</script>