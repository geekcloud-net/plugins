<?php
if(!defined('ABSPATH')){
    exit;
}

$default =  isset( $option['default'] ) ? $option['default'] : '';
$id = $option['id'];
$name = $option['name'];
$desc = isset( $option['desc'] ) ? $option['desc'] : '';
$value =  get_option( $id, $default );
$value = $value === '' ? array() : $value;
$placeholder = isset( $option['placeholder'] ) ? $option['placeholder'] : '';

$days = yith_get_worksday();
?>
<tr valign="top">
    <th scope="row"><label for="<?php esc_attr_e( $id  );?>"><?php echo( $name );?></label></th>
    <td>
        <select  name="<?php esc_attr_e( $id );?>[]" multiple="multiple" id="<?php esc_attr_e( $id );?>" class="wc-enhanced-select" placeholder="<?php echo $placeholder;?>" >
            <?php
                foreach( $days as $key => $day ):?>
                    <option value="<?php echo $key;?>" <?php selected( true, in_array( $key, $value ) );?>><?php echo $day;?></option>
                <?php endforeach; ?>
        </select>
        <span class="description"><?php echo $desc;?></span>
        <a href="" class="yith_select_all_day" ><?php _e('Select all','yith-woocommerce-delivery-date' );?></a>
        <a href="" class="yith_select_clear" ><?php _e('Clear','yith-woocommerce-delivery-date' );?></a>

    </td>
</tr>
