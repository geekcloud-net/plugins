<?php
if( !defined('ABSPATH')){
    exit;
}

$default = isset( $option['default'] ) ? $option['default'] : array('min' => 1, 'max' => 10 );

$id = $option['id'];
$name = $option['name'];
$desc = isset( $option['desc'] ) ? $option['desc'] : '';
$value = get_option( $option['id'], $default );
?>
<tr valign="top">
    <th scope="row"><?php echo $name;?></th>
    <td class="forminp">
        <div class="yith_range_content">
            <span class="yith_range_start">
                <label for="<?php esc_attr_e( $id );?>_start"><?php _e('Between', 'yith-woocommerce-delivery-date');?></label>
                <input type="number" min="0" step="1" value="<?php echo $value['min'];?>" name="<?php esc_attr_e( $id );?>[min]"/>
            </span>
            <span class="yith_range_end">
                <label for="<?php esc_attr_e( $id );?>_end"><?php _e('and', 'yith-woocommerce-delivery-date');?></label>
                <input type="number" min="0" step="1" value="<?php echo $value['max'];?>" name="<?php echo $id;?>[max]"/>
            </span>
        </div>
        <span class="description"><?php echo $desc;?></span>
    </td>
</tr>
