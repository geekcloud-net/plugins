jQuery(document).ready(function($) {
    
    $(".select-all").click(function () {
        var select = $(this).is(':checked') ? true : false;

        $(this).closest('.inside').find('input').prop('checked', select);
    });
    
});

