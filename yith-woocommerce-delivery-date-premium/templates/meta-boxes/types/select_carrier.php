<?php
if( !defined('ABSPATH')){
    exit;
}
global $post;
extract($args);
$all_carrier = get_posts( array('post_type'=> 'yith_carrier', 'post_status' => 'publish', 'numberposts' => -1 ) );
$carrier_selected = get_post_meta( $post->ID, $id, true );
$carrier_selected = empty( $carrier_selected ) ? array() : $carrier_selected;
$class = get_option('yith_delivery_date_enable_carrier_system') === 'yes' ? '' : 'ywcdd_block';


?>
<div class="<?php esc_attr_e($id);?>-container <?php echo $class;?>">
    <label for="<?php esc_attr_e( $id  );?>"><?php echo( $label);?></label>

    <select name="yit_metaboxes[<?php esc_attr_e( $id );?>][]" multiple="multiple" id="<?php esc_attr_e( $id );?>" class="wc-enhanced-select ywcdd_select_carrier" placeholder="<?php echo $placeholder;?>">
        <?php
        foreach( $all_carrier as $key => $carrier ):?>
            <option value="<?php echo $carrier->ID;?>" <?php selected( true, in_array( $carrier->ID, $carrier_selected ) );?>><?php echo get_the_title( $carrier->ID );?></option>
        <?php endforeach; ?>
    </select>
    <p>
    <a href="" class="yith_select_all_day" ><?php _e('Select all','yith-woocommerce-delivery-date' );?></a>
    <a href="" class="yith_select_clear" ><?php _e('Clear','yith-woocommerce-delivery-date' );?></a>
    </p>
    <span class="desc inline">
      
    	<?php 
    	
    	  $extra_desc = '';
    	  $new_post = admin_url('post-new.php');
    	  $params = array('post_type' => 'yith_carrier');
    	  $new_carrier_url = esc_url( add_query_arg( $params, $new_post ) );
    	  $extra_desc = sprintf(' <a href="%s">%s</a>', $new_carrier_url, __('or create one', 'yith-woocommerce-delivery-date' ) );
    	  
    	echo $desc.$extra_desc ?>
        
    </span>
  
</div>
