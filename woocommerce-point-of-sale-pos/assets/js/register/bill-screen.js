jQuery(document).ready(function () {
    bill_refresh();
    setInterval(function () {
        bill_refresh()
    }, 1000);
});

function bill_refresh() {
    var register_status = localStorage.getItem('register_status_' + reg_id);
    jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
            action: 'wc_pos_refresh_bill_screen',
            register_status: register_status,
            register_cart: JSON.parse(localStorage.getItem('cart_' + reg_id))
        },
        success: function (response) {
            jQuery('.content').html(response);
        },
        error: function () {

        }
    });
}
