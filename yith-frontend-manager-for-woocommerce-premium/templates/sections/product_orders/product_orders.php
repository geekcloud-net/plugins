<?php

defined( 'ABSPATH' ) or exit;

$page_id = isset( $_GET['page_id'] ) && $_GET['page_id'] > 0 ?  $_GET['page_id'] : '';

?>

<div id="yith-wcfm-orders">

    <h1><?php echo __('Orders', 'yith-frontend-manager-for-woocommerce'); ?></h1>

    <?php

    if ( isset( $_GET['trashed'] ) && $_GET['trashed'] == 'ok' ) {
        wc_print_notice( __('1 order moved to the Trash.', 'yith-frontend-manager-for-woocommerce'), 'success' );
    }

    $section_obj->pagination( 'top' );
    ?>

    <table class="table">
        <tr>
            <?php foreach( $columns as $column ) : ?>
                <th><?php echo $column ?></th>
            <?php endforeach; ?>
        </tr>
    <?php

    if( count( $orders ) > 0 ) :
        foreach ( $orders as $order ) :
            ?>

            <tr>
                <?php foreach( $columns as $column =>  $label ) : ?>
                    <?php $class = $column; ?>
                    <?php $class .= isset( $cols_class[ $column ] ) ? ' ' . $cols_class[ $column ] : ''; ?>
                    <td class="<?php echo $class ?>"><?php do_action( 'yith_wcfm_order_cols', $column, $order ) ?></td>
                <?php endforeach; ?>
            </tr>

        <?php endforeach;
    else : ?>

        <tr><td colspan="8"><?php echo __('No orders found', 'yith-frontend-manager-for-woocommerce'); ?></td></tr>

    <?php endif; ?>

    </table>

    <?php $section_obj->pagination( 'bottom' ); ?>

</div>

<?php wp_reset_query(); ?>
