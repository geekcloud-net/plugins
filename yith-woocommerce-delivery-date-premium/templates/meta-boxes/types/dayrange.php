<?php
if( !defined('ABSPATH')){
    exit;
}

extract( $args );
global $post;


$value = get_post_meta( $post->ID, $id, true );
if( empty( $value ) ){
    $value = array( 'min' => 0 ,'max' => 10 );
}

?>
<div id="<?php esc_attr_e($id);?>-container">
    <label for="<?php esc_attr_e( $id);?>"><?php esc_attr_e( $label );?></label>
    <div class="yith_range_content">
            <span class="yith_range_start">
                <?php _ex('Between', 'Days span','yith-woocommerce-delivery-date');?>
                <input type="number" min="0" step="1" value="<?php echo $value['min'];?>" name="<?php esc_attr_e( $id );?>[min]"/>
            </span>
            <span class="yith_range_end">
               <?php _ex('and','Days span' ,'yith-woocommerce-delivery-date');?>
                <input type="number" min="0" step="1" value="<?php echo $value['max'];?>" name="<?php echo $id;?>[max]"/>
            </span>
    </div>
    <span class="desc inline"><?php echo $desc ?></span>
</div>