<?php
if(!defined('ABSPATH')){
    exit;
}
global $post;

extract( $args );

$value =  get_post_meta( $post->ID, $id, true );
$value = $value === '' ? array() : $value;

$days = yith_get_worksday();
?>
<div class="<?php esc_attr_e($id);?>-container">
    <label for="<?php esc_attr_e( $id  );?>"><?php echo( $label);?></label>
  
        <select name="<?php esc_attr_e( $id );?>[]" multiple="multiple" id="<?php esc_attr_e( $id );?>" class="wc-enhanced-select" data-placeholder="<?php echo $placeholder;?>" >
            <?php
                foreach( $days as $key => $day ):?>
                    <option value="<?php echo $key;?>" <?php selected( true, in_array( $key, $value ) );?>><?php echo $day;?></option>
                <?php endforeach; ?>
        </select>
        <span class="desc inline"><?php echo $desc;?></span>
        <a href="" class="yith_select_all_day" ><?php _e('Select all','yith-woocommerce-delivery-date' );?></a>
        <a href="" class="yith_select_clear" ><?php _e('Clear','yith-woocommerce-delivery-date' );?></a>
</div>