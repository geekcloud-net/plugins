jQuery(document).ready(function () {
    jQuery('.cash-button').on('click', function () {
        jQuery('#modal-cash_management').data('action', jQuery(this).data('action'));
        jQuery('#modal-cash_management h1 .title, #add-cash-action').text(jQuery(this).text());
        openModal('modal-cash_management');
    });
    jQuery('#add-cash-action').on('click', function () {
        jQuery.ajax({
            type: 'POST',
            url: wc_pos_params.ajax_url,
            data: {
                action: 'wc_pos_add_cash_management_action',
                action_type: jQuery('#modal-cash_management').data('action'),
                amount: jQuery('#cash_management_details [name="amount"]').val(),
                note: jQuery('#cash_management_details [name="note"]').val(),
                register: register_cash_management.register
            },
            success: function (responce) {
                jQuery('.cash-data tbody').prepend(responce);
                switch (jQuery('#modal-cash_management').data('action')) {
                    case 'remove-cash':
                        register_cash_management.cash_balance = register_cash_management.cash_balance - parseFloat(jQuery('#cash_management_details [name="amount"]').val());
                        break;
                    case 'add-cash':
                        register_cash_management.cash_balance = register_cash_management.cash_balance + parseFloat(jQuery('#cash_management_details [name="amount"]').val());
                        break;
                }
                closeModal('modal-cash_management');
                var total_html = jQuery('#cash-total .woocommerce-Price-amount').html();
                var new_total = total_html.replace(/[0-9.,]+$/g, register_cash_management.cash_balance.toFixed(2));
                jQuery('#cash-total .woocommerce-Price-amount').html(new_total);
            }
        });
    });
});

function amount_validation(el) {
    var pattern = /^[0-9.,]+$/;
    if (!pattern.test(jQuery(el).val())) {
        jQuery(el).addClass('error');
        jQuery('#add-cash-action').attr('disabled', true);
    } else {
        jQuery(el).removeClass('error');
        jQuery('#add-cash-action').attr('disabled', false);
    }
}

function to_float(el) {
    jQuery(el).val(parseFloat(jQuery(el).val()).toFixed(2));
}