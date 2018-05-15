<?php
if ( !defined( 'YITH_WCBEP' ) ) { exit; } // Exit if accessed directly

?>

<div id="yith-wcbep-importer-wrapper">
    <h2><?php _e('Import Products', 'yith-woocommerce-bulk-product-editing') ?></h2>
    <form id="yith-wcbep-importer-form" action="yith_wcbep_import">
    <div id="yith-wcbep-importer-content">
            <input id="yith-wcbep-importer-upload-url" name="file_url" type="file" placeholder="<?php _e('File URL', 'yith-woocommerce-bulk-product-editing'); ?>">
        <input id="yith-wcbep-importer-upload-btn" type="button" class="button button-secondary button-large" value="<?php _e('Upload', 'yith-woocommerce-bulk-product-editing')?>">
    </div>

    <div id="yith-wcbep-importer-button-wrap">
        <input id="yith-wcbep-importer-save" type="button" class="button button-primary button-large" value="<?php _e('Import', 'yith-woocommerce-bulk-product-editing')?>">
        <input id="yith-wcbep-importer-cancel" type="button" class="button button-secondary button-large" value="<?php _e('Cancel', 'yith-woocommerce-bulk-product-editing')?>">
    </div>
    </form>
</div>