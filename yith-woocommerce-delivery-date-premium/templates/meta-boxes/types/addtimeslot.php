<?php
if( !defined('ABSPATH')){
    exit;
}
global $post;
extract( $args );

$label_fee = sprintf('%s (%s)',__('Fee','yith-woocommerce-delivery-date' ), get_woocommerce_currency_symbol() );
?>
<div id="<?php esc_attr_e($id);?>-container">
    <label for="<?php esc_attr_e( $id);?>"><?php esc_attr_e( $label );?></label>
    <div class="yith-new-time-slot">
        <input type="text" id="yith_timepicker_from" class="yith_timepicker" placeholder="<?php _e('Time From', 'yith-woocommerce-delivery-date' );?>" />
        <input type="text" id="yith_timepicker_to" class="yith_timepicker"  placeholder="<?php _e('Time To', 'yith-woocommerce-delivery-date' );?>" />
        <input type="number" id="yith_max_tot_order" min="0" step="1"  placeholder="<?php _e('Lockout', 'yith-woocommerce-delivery-date' );?>" />
        <input type="number" id="yith_fee" min="0" step="any" placeholder="<?php echo $label_fee;?>" />
        <input type="submit" id="yith_add_time_slot" class="button button-primary"  value="<?php _e('Add','yith-woocommerce-delivery-date' );?>"/>
        <input type="hidden" id="yith_carrier_id" value="<?php echo $post->ID;?>" />
        <input type="hidden" id="yith_metakey" value="<?php echo $id;?>" />
    </div>
    <div class="ywcdd_carrier_table">
    <?php
    wc_get_template( 'carrier-time-slot-table.php', array('post_id' => $post->ID, 'metakey'=> $id ),'',YITH_DELIVERY_DATE_TEMPLATE_PATH.'meta-boxes/' );
    ?>
    </div>
</div>
