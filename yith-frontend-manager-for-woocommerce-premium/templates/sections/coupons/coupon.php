<?php

defined( 'ABSPATH' ) or exit;

$coupon_code = isset( $_POST['post_title'] ) ? $_POST['post_title'] : '';
error_log( 'coupon' );
if ( isset( $_POST['act'] ) && ! empty( $coupon_code ) ) {
    $coupon_description = $_POST['excerpt'];

    if ( $_POST['act'] == 'new' ) {
        $coupon = array(
            'post_title'    => $coupon_code,
            'post_excerpt'  => $coupon_description,
            'post_status'   => 'publish',
            'post_author'   => get_current_user_id(),
            'post_type'     => 'shop_coupon'
        );                      
        $coupon_id = wp_insert_post( $coupon );
        wc_print_notice( __('Coupon created.', 'yith-frontend-manager-for-woocommerce'), 'success' );
    }

    else {
        $coupon = new WC_Coupon( $coupon_code );
        $coupon_id = yit_get_prop( $coupon, 'id' );
        wc_print_notice( __('Coupon updated.', 'yith-frontend-manager-for-woocommerce'), 'success' );
    }
                        
    // Coupon Data
    update_post_meta( $coupon_id, 'discount_type',              isset( $_POST['discount_type'] ) ? $_POST['discount_type'] : '' );
    update_post_meta( $coupon_id, 'coupon_amount',              isset( $_POST['coupon_amount'] ) ? $_POST['coupon_amount'] : '' );
    update_post_meta( $coupon_id, 'free_shipping',              isset( $_POST['free_shipping'] ) ? $_POST['free_shipping'] : '' );
    update_post_meta( $coupon_id, 'expiry_date',                isset( $_POST['expiry_date'] ) ? $_POST['expiry_date'] : '' );

    // Usage Restriction
    update_post_meta( $coupon_id, 'minimum_amount',             isset( $_POST['minimum_amount'] ) ? $_POST['minimum_amount'] : '' );
    update_post_meta( $coupon_id, 'maximum_amount',             isset( $_POST['maximum_amount'] ) ? $_POST['maximum_amount'] : '' );
    update_post_meta( $coupon_id, 'individual_use',             isset( $_POST['individual_use'] ) ? $_POST['individual_use'] : '' );
    update_post_meta( $coupon_id, 'exclude_sale_items',         isset( $_POST['exclude_sale_items'] ) ? $_POST['exclude_sale_items'] : '' );
    update_post_meta( $coupon_id, 'product_ids',                isset( $_POST['product_ids'] ) ? implode( ',', $_POST['product_ids'] ) : '' );
    update_post_meta( $coupon_id, 'exclude_product_ids',        isset( $_POST['exclude_product_ids'] ) ? implode( ',', $_POST['exclude_product_ids'] ) : '' );
    update_post_meta( $coupon_id, 'product_categories',         isset( $_POST['product_categories'] ) ? $_POST['product_categories'] : '' );
    update_post_meta( $coupon_id, 'exclude_product_categories', isset( $_POST['exclude_product_categories'] ) ? $_POST['exclude_product_categories'] : '' );
    update_post_meta( $coupon_id, 'customer_email',             isset( $_POST['customer_email'] ) ? $_POST['customer_email'] : '' );

    // Usage Limits
    update_post_meta( $coupon_id, 'usage_limit',                isset( $_POST['usage_limit'] ) ? $_POST['usage_limit'] : '' );
    update_post_meta( $coupon_id, 'limit_usage_to_x_items',     isset( $_POST['limit_usage_to_x_items'] ) ? $_POST['limit_usage_to_x_items'] : '' );
    update_post_meta( $coupon_id, 'usage_limit_per_user',       isset( $_POST['usage_limit_per_user'] ) ? $_POST['usage_limit_per_user'] : '' );

    do_action( 'yith_wcfm_coupon_updated', $coupon_id );
}

$endpoint_url = $section_obj->get_url( $section_obj->get_current_subsection( true ) );

if( ! empty( $_GET['code'] ) ){
    $coupon_code = $_GET['code'];
}

$coupon = new WC_Coupon( $coupon_code );

$commission_details_args = array(
    'description',
    'discount_type',
    'coupon_amount',
    'free_shipping',
    'expiry_date',
    'minimum_amount',
    'maximum_amount',
    'individual_use',
    'exclude_sale_items',
    'product_ids',
    'exclude_product_ids',
    'product_categories',
    'exclude_product_categories',
    'customer_email',
    'usage_limit',
    'limit_usage_to_x_items',
    'usage_limit_per_user',
    'usage_count',
);

$commission_details = array();

foreach( $commission_details_args as $arg ){
    $commission_details[ $arg ] = yit_get_prop( $coupon, $arg );

    if( 'customer_email' == $arg && is_array( $commission_details[ $arg ] ) ){
        $commission_details[ $arg ] = implode( ',', $commission_details[ $arg ] );
    }
}

extract( $commission_details );

?>

<div id="yith-wcfm-coupon" class="add-coupon">

    <h1><?php echo __('Coupon', 'woocommerce'); ?></h1>

    <form name="post" action="<?php echo add_query_arg( array( 'code' => $coupon_code ), $endpoint_url ); ?>" method="post" id="post">

        <input type="hidden" name="act" value="<?php echo $coupon_code != '' ? 'edit' : 'new'; ?>">

        <div class="options_group">

            <p class="form-field">
                <label for="title"><?php echo __('Coupon Code', 'yith-frontend-manager-for-woocommerce'); ?></label>
                <input type="text" name="post_title" value="<?php echo isset( $coupon_code ) ? $coupon_code : ''; ?>" id="title" spellcheck="true" autocomplete="off">
            </p>

            <p class="form-field">
                <label for="woocommerce-coupon-description"><?php echo __('Description', 'yith-frontend-manager-for-woocommerce'); ?></label>
                <textarea id="woocommerce-coupon-description" name="excerpt" cols="5" rows="2" placeholder="Description (optional)"><?php echo $description; ?></textarea>
            </p>

        </div>

        <h3><?php echo __('Coupon Data', 'yith-frontend-manager-for-woocommerce'); ?></h3>

        <div class="options_group">

            <p class="form-field discount_type_field ">
                <label for="discount_type"><?php echo __('Discount type', 'yith-frontend-manager-for-woocommerce'); ?></label>
                <select id="discount_type" name="discount_type" class="select">
                    <?php foreach( $coupon_types as $type => $description ) : ?>
                        <option value="<?php echo $type ?>"<?php echo $discount_type == $type ? 'selected="selected"' : ''; ?>><?php echo $description; ?></option>
                    <?php endforeach; ?>
                </select>
            </p>

            <p class="form-field coupon_amount_field ">
                <label for="coupon_amount"><?php echo __('Coupon amount', 'yith-frontend-manager-for-woocommerce'); ?></label>
                <input type="text" class="short wc_input_price" style="" name="coupon_amount" id="coupon_amount" value="<?php echo $coupon_amount; ?>" placeholder="0">
            </p>

            <p class="form-field free_shipping_field ">
                <label for="free_shipping"><?php echo __('Allow free shipping', 'yith-frontend-manager-for-woocommerce'); ?></label>
                <input type="checkbox" class="checkbox" style="" name="free_shipping" id="free_shipping" value="yes" <?php echo $free_shipping; ?>>
                <span class="description"><?php echo __('Check this box if the coupon grants free shipping. A free shipping method must be enabled
                in your shipping zone. It needs to be set up to require "a valid free shipping coupon" (see the "Free Shipping Requires"
                setting).',
                        'yith-frontend-manager-for-woocommerce'); ?></span>
            </p>

            <p class="form-field expiry_date_field ">
                <label for="expiry_date"><?php echo __('Coupon expiry date', 'yith-frontend-manager-for-woocommerce'); ?></label>
                <input type="text" class="date-picker" style="" name="expiry_date" id="expiry_date" value="<?php echo $expiry_date; ?>" placeholder="YYYY-MM-DD" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])">
            </p>

        </div>

        <h3><?php echo __('Usage Restriction', 'yith-frontend-manager-for-woocommerce'); ?></h3>

        <div class="options_group">
            
            <p class="form-field minimum_amount_field ">
                <label for="minimum_amount"><?php echo __('Minimum spend', 'yith-frontend-manager-for-woocommerce'); ?></label>
                <input type="text" class="short wc_input_price" style="" name="minimum_amount" id="minimum_amount" value="<?php echo $minimum_amount; ?>" placeholder="No minimum">
            </p>

            <p class="form-field maximum_amount_field ">
                <label for="maximum_amount"><?php echo __('Maximum spend', 'yith-frontend-manager-for-woocommerce'); ?></label>
                <input type="text" class="short wc_input_price" style="" name="maximum_amount" id="maximum_amount" value="<?php echo $maximum_amount; ?>" placeholder="No maximum">
            </p>
            
            <p class="form-field individual_use_field ">
                <label for="individual_use"><?php echo __('Individual use only', 'yith-frontend-manager-for-woocommerce'); ?></label>
                <input type="checkbox" class="checkbox" style="" name="individual_use" id="individual_use" value="yes" <?php echo $individual_use; ?>>
                <span class="description"><?php echo __('Check this box if the coupon cannot be used in conjunction with other coupons.', 'yith-frontend-manager-for-woocommerce'); ?></span>
            </p>
            
            <p class="form-field exclude_sale_items_field ">
                <label for="exclude_sale_items"><?php echo __('Exclude sale items', 'yith-frontend-manager-for-woocommerce'); ?></label>
                <input type="checkbox" class="checkbox" style="" name="exclude_sale_items" id="exclude_sale_items" value="yes" <?php echo $exclude_sale_items; ?>>
                <span class="description"><?php echo __('Check this box if the coupon should not apply to on-sale items. Per-item coupons
                will only work if the item is not on-sale. Per-cart coupons will only work if there are no sale items in the cart.',
                        'yith-frontend-manager-for-woocommerce'); ?></span>
            </p>

        </div>

        <div class="options_group">

            <p class="form-field">
                <label><?php echo __('Products', 'yith-frontend-manager-for-woocommerce'); ?></label>
                <select name="product_ids[]" class="product_ids-select2 wc-product-search" multiple="multiple" placeholder="Applied to..."><?php
                    $loop = new WP_Query( array( 'post_type' => 'product', 'posts_per_page' => '-1' ) );
                    while ( $loop->have_posts() ) :
                        $loop->the_post(); 
                        global $product;
                        $product_id     = yit_get_prop( $product, 'id' );
                        $product_title  = yit_get_prop( $product, 'title' );
                        echo '<option value="' . $product_id . '" ' . ( in_array( $product_id, $product_ids ) ? 'selected="selected"' : '' ) . '>' . $product_title . '</option>';
                    endwhile; 
                    wp_reset_query();
                ?></select>
            </p>
            
            <p class="form-field">
                <label><?php echo __('Exclude products', 'yith-frontend-manager-for-woocommerce'); ?></label>
                <select name="exclude_product_ids[]" class="exclude_product_ids-select2 wc-product-search" multiple="multiple" placeholder="Applied to..."><?php
                    $loop = new WP_Query( array( 'post_type' => 'product', 'posts_per_page' => '-1' ) );
                    while ( $loop->have_posts() ) :
                        $loop->the_post(); 
                        global $product;
                        $product_id     = yit_get_prop( $product, 'id' );
                        $product_title  = yit_get_prop( $product, 'title' );
                        echo '<option value="' . $product_id . '" ' . ( in_array( $product_id, $exclude_product_ids ) ? 'selected="selected"' : '' ) . '>' . $product_title . '</option>';
                    endwhile; 
                    wp_reset_query();
                ?></select>
            </p>

        </div>

        <div class="options_group">

            <p class="form-field">
                <label for="product_categories"><?php echo __('Product categories', 'yith-frontend-manager-for-woocommerce'); ?></label>
                <select name="product_categories[]" class="product_categories-select2" multiple="multiple" placeholder="Applied to..."><?php
	                yith_wcfm_echo_product_categories_childs_of( 0, 0, $product_categories );
                ?></select>
            </p>

            <p class="form-field">
                <label for="exclude_product_categories"><?php echo __('Exclude categories', 'yith-frontend-manager-for-woocommerce'); ?></label>
                <select name="exclude_product_categories[]" class="exclude_product_categories-select2" multiple="multiple" placeholder="Applied to..."><?php
	                yith_wcfm_echo_product_categories_childs_of( 0, 0, $exclude_product_categories );
                ?></select>
            </p>
        </div>

        <div class="options_group">

            <p class="form-field customer_email_field ">
                <label for="customer_email"><?php echo __('Email restrictions', 'yith-frontend-manager-for-woocommerce'); ?></label>
                <input type="email" class="" style="" name="customer_email" id="customer_email" value="<?php echo $customer_email; ?>" placeholder="No restrictions" multiple="multiple">
            </p>

        </div>

        <h3><?php echo __('Usage Limits', 'yith-frontend-manager-for-woocommerce'); ?></h3>

        <div class="options_group">

            <p class="form-field usage_limit_field ">
                <label for="usage_limit"><?php echo __('Usage limit per coupon', 'yith-frontend-manager-for-woocommerce'); ?></label>
                <input type="number" class="short" style="" name="usage_limit" id="usage_limit" value="<?php echo $usage_limit; ?>" placeholder="Unlimited usage" step="1" min="0">
            </p>

            <p class="form-field limit_usage_to_x_items_field ">
                <label for="limit_usage_to_x_items"><?php echo __('Limit usage to X items', 'yith-frontend-manager-for-woocommerce'); ?></label>
                <input type="number" class="short" style="" name="limit_usage_to_x_items" id="limit_usage_to_x_items" value="<?php echo $limit_usage_to_x_items; ?>" placeholder="Apply to all qualifying items in cart" step="1" min="0">
            </p>

            <p class="form-field usage_limit_per_user_field ">
                <label for="usage_limit_per_user"><?php echo __('Usage limit per user', 'yith-frontend-manager-for-woocommerce'); ?></label>
                <input type="number" class="short" style="" name="usage_limit_per_user" id="usage_limit_per_user" value="<?php echo $usage_limit_per_user; ?>" placeholder="Unlimited usage" step="1" min="0">
            </p>

            <input type="hidden" name="post_type" value="shop_coupon" />

        </div>

        <input type="submit" value="Save" />

    </form>
</div>
