jQuery(document).ready(function () {
    jQuery(document).on('keyup', function (e) {
        var code = e.keyCode;
        if ((code && code >= 96 && code <= 105) || code === 13) {
            press_keypad_key(code);
        }
    });
});

function press_keypad_key(code) {
    switch (code) {
        case 13:
            jQuery('#modal-qt-product .product-add-btn').click();
            break;
        case 96:
            jQuery('#modal-qt-product .keypad-key:contains("0")').click();
            break;
        case 97:
            jQuery('#modal-qt-product .keypad-key:contains("1")').click();
            break;
        case 98:
            jQuery('#modal-qt-product .keypad-key:contains("2")').click();
            break;
        case 99:
            jQuery('#modal-qt-product .keypad-key:contains("3")').click();
            break;
        case 100:
            jQuery('#modal-qt-product .keypad-key:contains("4")').click();
            break;
        case 101:
            jQuery('#modal-qt-product .keypad-key:contains("5")').click();
            break;
        case 102:
            jQuery('#modal-qt-product .keypad-key:contains("6")').click();
            break;
        case 103:
            jQuery('#modal-qt-product .keypad-key:contains("7")').click();
            break;
        case 104:
            jQuery('#modal-qt-product .keypad-key:contains("8")').click();
            break;
        case 105:
            jQuery('#modal-qt-product .keypad-key:contains("9")').click();
            break;
    }
}