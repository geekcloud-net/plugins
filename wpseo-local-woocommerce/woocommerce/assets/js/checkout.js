jQuery(document).ready(function ($) {

    //wrap the right section, and find our target
    $('.shipping_address', $('.woocommerce-shipping-fields')).prevAll().addBack().wrapAll('<div class="woocommerce-shipping-fields-wrapper"></div>');
    var $shipping_div = $('.woocommerce-shipping-fields-wrapper');

    //hide by default
    $shipping_div.hide();

    //bind the event
    $(document.body).on('updated_checkout', function (e) {

        //find the currently selected shippping method
        var shipping_method = $('.shipping_method:checked').val();

        if ( ! shipping_method ) {
            shipping_method = $('.shipping_method option:selected').val();
        }

        //does it have our shipping-method name in it?
        var index = shipping_method.search('yoast_wcseo_local_pickup_');

        if ( index > -1 ) {
            $shipping_div.hide();   // go hide
        } else {
            $shipping_div.show();   // show it
        }
    });
});