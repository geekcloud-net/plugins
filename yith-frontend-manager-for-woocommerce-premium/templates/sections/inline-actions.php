<?php
/**
 * Frontend Manager content
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<small class="act">
    <a href="<?php echo $edit_uri; ?>" class="yith-wcfm-edit edit">
        <?php echo __('Edit', 'yith-frontend-manager-for-woocommerce'); ?>
    </a>
    |
    <a href="<?php echo $delete_uri ?>" class="yith-wcfm-delete delete">
        <?php echo __('Delete', 'yith-frontend-manager-for-woocommerce'); ?>
    </a>
    <?php if( ! empty( $view_uri ) ) : ?>
        |
        <a href="<?php echo $view_uri ?>" class="yith-wcfm-view view">
            <?php echo __('View', 'yith-frontend-manager-for-woocommerce'); ?>
        </a>
    <?php endif; ?>
</small>