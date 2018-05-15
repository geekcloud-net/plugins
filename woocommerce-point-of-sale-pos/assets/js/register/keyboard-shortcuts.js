jQuery(document).key('ctrl+shift+s', function (e) {
    e.preventDefault();
    jQuery('.wc_pos_register_save').click();
});
jQuery(document).key('ctrl+shift+v', function (e) {
    e.preventDefault();
    jQuery('.wc_pos_register_void').click();
});
jQuery(document).key('ctrl+shift+l', function (e) {
    e.preventDefault();
    jQuery('#retrieve_sales').click();
});
jQuery(document).key('ctrl+shift+p', function (e) {
    e.preventDefault();
    jQuery('.wc_pos_register_pay').click();
});
jQuery(document).key('ctrl+shift+n', function (e) {
    e.preventDefault();
    jQuery('.wc_pos_register_notes').click();
});
jQuery(document).key('ctrl+shift+m', function (e) {
    e.preventDefault();
    jQuery('#add_product_to_register').click();
});
jQuery(document).key('ctrl+shift+d', function (e) {
    e.preventDefault();
    jQuery('.wc_pos_register_discount').click();
});
jQuery(document).key('ctrl+shift+u', function (e) {
    e.preventDefault();
    jQuery('#s2id_customer_user .select2-default').mousedown();
});
jQuery(document).key('ctrl+shift+f', function (e) {
    e.preventDefault();
    jQuery('#s2id_add_product_id #s2id_autogen1').click();
});
jQuery(document).key('ctrl+shift+r', function (e) {
    e.preventDefault();
    jQuery('#sync_data').click();
});
jQuery(document).key('ctrl+shift+c', function (e) {
    e.preventDefault();
    jQuery('#add_shipping_to_register').click();
});
jQuery(document).key('esc', function (e) {
    e.preventDefault();
    jQuery('.md-show .md-close').click();
});