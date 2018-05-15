jQuery( function ($) {

    // ======= click in the upload button to check when the logo is inserted ========
    var upload_button_clicked = false;
    $( "body" ).on( "change", "#ywpi_company_logo", function () {

        var tmpImg = new Image();
        tmpImg.src= $( this ).val(); //or  document.images[i].src;
        $( tmpImg ).one( 'load',function(){
            orgWidth = tmpImg.width;
            orgHeight = tmpImg.height;

            if ( orgWidth > 300 || orgHeight > 150 ){

                //alert( "The logo your uploading is " + orgWidth + "x" + orgHeight + ". Logo must be no bigger than 300 x 150 pixels" );

                alert( yith_wc_pdf_invoice_free_object.logo_message_1 + orgWidth + "x" + orgHeight + " pixels" + yith_wc_pdf_invoice_free_object.logo_message_2 );

                $( "body #ywpi_company_logo" ).val( '' );
                $( "body #ywpi_company_logo-container .upload_img_preview img" ).remove();
            }

        });

    });

});