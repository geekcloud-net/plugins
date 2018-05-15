<?php
if( !defined( 'ABSPATH' ) ){
    exit;
}

$default = isset( $option['default'] ) ? $option['default'] : array( 'completed', 'processing' );
$id = $option['id'];
$name = $option['name'];
$desc = isset( $option['desc'] ) ? $option['desc'] : '';
$value =  get_option( $id, $default );
//$value = $value === '' ? array() : $value;
$placeholder = isset( $option['placeholder'] ) ? $option['placeholder'] : '';

$order_status = wc_get_order_statuses();
?>
<tr valign="top">
    <th scope="row"><label for="<?php esc_attr_e( $id  );?>"><?php echo( $name );?></label></th>
    <td>
        <select  name="<?php esc_attr_e( $id );?>[]" multiple="multiple" id="<?php esc_attr_e( $id );?>" class="wc-enhanced-select" data-allow_clear="true" placeholder="<?php echo $placeholder;?>" >
            <?php
            foreach( $order_status as $key => $status ):
                $key = 'wc-' === substr( $key, 0, 3 ) ? substr( $key, 3 ) : $key;
                ?>
                <option value="<?php echo $key;?>" <?php selected( true, in_array( $key, $value ) );?>><?php echo $status;?></option>
            <?php endforeach; ?>
        </select>
        <span class="description"><?php echo $desc;?></span>
    </td>
</tr>
