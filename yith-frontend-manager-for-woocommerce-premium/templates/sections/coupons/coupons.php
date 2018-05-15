<?php

defined( 'ABSPATH' ) or exit;

$act = isset( $_GET['act'] ) ?  $_GET['act'] : '';

if ( $act == 'delete' ) {
    do_action( 'yith_wcfm_delete_coupon' );
}

do_action( 'yith_wcfm_before_section_template', 'coupons', '', $act );

$list_table_cols = apply_filters( 'yith_wcfm_shop_coupon_columns', array(
        'code'          => __('Code', 'yith-frontend-manager-for-woocommerce'),
        'coupon type'   => __('Coupon type', 'yith-frontend-manager-for-woocommerce'),
        'coupon_amount' => __('Coupon amount', 'yith-frontend-manager-for-woocommerce'),
        'description'   => __('Description', 'yith-frontend-manager-for-woocommerce'),
        'product_ids'   => __('Product IDs', 'yith-frontend-manager-for-woocommerce'),
        'usage'         => __('Usage / Limit', 'yith-frontend-manager-for-woocommerce'),
        'expiry_date'   => __('Expiry date', 'yith-frontend-manager-for-woocommerce'),
    )
);

$base_section_uri = yith_wcfm_get_section_url( 'current' );
$current_discount_type = ! empty( $_GET['coupon_type'] ) ? wc_clean( $_GET['coupon_type'] ) : '';
?>

<div id="yith-wcfm-coupons">
    <h1><?php echo __('Coupons', 'yith-frontend-manager-for-woocommerce'); ?></h1>

    <div class="actions">
        <form id="coupons-filter" method="get" action="<?php echo $base_section_uri ?>">
            <?php yith_wcfm_months_dropdown( 'shop_coupon' ); ?>
            <select name="coupon_type" id="dropdown_shop_coupon_type">
                <option value=""><?php echo __('Show all types', 'yith-frontend-manager-for-woocommerce'); ?></option>
                <?php foreach( $coupon_types as $id => $name ) : ?>
                    <option <?php selected( $id, $current_discount_type, true ); ?>value="<?php echo $id ?>"><?php echo $name; ?></option>
                <?php endforeach; ?>
            </select>
            <input type="submit" name="filter_action" id="post-query-submit" class="button" value="Filter">
            <?php if( ! empty( $_GET['coupon_type'] ) || ! empty( $_GET['m'] ) ) : ?>
                <a href="<?php echo $base_section_uri ?>" class="button-primary" >
                    <?php _e( 'Reset', 'yith-frontend-manager-for-woocommerce' )?>
                </a>
            <?php endif; ?>
        </form>
    </div>

    <table class="table yith-wcfm-shop-coupons-list-table">
        <tr>
            <?php foreach( $list_table_cols as $id => $label ) : ?>
                <th class="<?php echo $id ?>"><?php echo $label; ?></th>
            <?php endforeach; ?>
        </tr>

        <?php

        $args = array(
            'posts_per_page'    => -1,
            'orderby'           => 'title',
            'order'             => 'asc',
            'post_type'         => 'shop_coupon',
            'post_status'       => 'publish',
        );

        if ( ! empty( $_GET['coupon_type'] ) ) {
            $args['meta_key']   = 'discount_type';
            $args['meta_value'] = $current_discount_type;
        }

        if ( ! empty( $_GET['m'] ) ) {
            $args['m'] = wc_clean( $_GET['m'] );
        }

        $coupons = get_posts( apply_filters( 'yith_wcfm_query_coupons_args', $args ) );

        if ( count( $coupons ) > 0 ) :

            $coupon_names = array();
            foreach ( $coupons as $coupon ) :
                $is_coupon_instance_of_wc_coupon = $coupon instanceof WC_Coupon;

                $coupon_title   = $is_coupon_instance_of_wc_coupon instanceof WC_Coupon ? yit_get_prop( $coupon, 'name' ) : $coupon->post_title;
                $coupon_excerpt = $is_coupon_instance_of_wc_coupon instanceof WC_Coupon ? yit_get_prop( $coupon, 'short_description' ) : $coupon->post_excerpt;
                array_push( $coupon_names, $coupon_title );

                if( ! $is_coupon_instance_of_wc_coupon ){
                    $coupon = new WC_Coupon( $coupon_title );
                }

                $coupon_url_args = array(
                    'code' => $coupon_title
                );

                $coupon_url = add_query_arg( $coupon_url_args, yith_wcfm_get_section_url( 'current', 'coupon' ) );
                $coupon_delete_url = add_query_arg( array( 'act' => 'delete', 'code' => $coupon_title ), $base_section_uri );

                //Coupon args
                $discount_type  = yit_get_prop( $coupon, 'discount_type' );
                $amount         = yit_get_prop( $coupon, 'amount' );
                $product_ids    = yit_get_prop( $coupon, 'product_ids' );
                $usage_count    = yit_get_prop( $coupon, 'usage_count' );
                $usage_limit    = yit_get_prop( $coupon, 'usage_limit' );
                $date_expires   = yit_datetime_to_timestamp( yit_get_prop( $coupon, 'date_expires' ) );

                $section_uri = array(
                    'edit_uri'      => $coupon_url,
                    'delete_uri'    => $coupon_delete_url
                );
                ?>
                
                <tr>
                    <td>
                        <a href="<?php echo $coupon_url; ?>">
                            <?php echo $coupon_title; ?>
                        </a>
                        <?php yith_wcfm_add_inline_action( $section_uri ) ?>
                    </td>
                    <td><?php echo isset( $coupon_types[ $discount_type ] ) ? $coupon_types[ $discount_type ] : $discount_type; ?></td>
                    <td><?php echo $amount; ?></td>
                    <td><?php echo $coupon_excerpt; ?></td>
                    <td><?php echo implode( ', ', $product_ids ); ?></td>
                    <td><?php echo ( $usage_count > 0 ? $usage_count : 0 ) . ' / ' . ( $usage_limit > 0 ? $usage_limit : '&infin;' ) ; ?></td>
                    <td><?php echo ( $date_expires > 0 ? date_i18n( get_option( 'date_format' ), $date_expires ) : '&infin;' ); ?></td>
                </tr>

            <?php endforeach;

        else : ?>

            <tr><td colspan="6"><?php echo __('No coupons found', 'yith-frontend-manager-for-woocommerce'); ?></td></tr>

        <?php endif; ?>

    </table>

</div>

<?php
do_action( 'yith_wcfm_after_section_template', 'coupons', '', $act );
