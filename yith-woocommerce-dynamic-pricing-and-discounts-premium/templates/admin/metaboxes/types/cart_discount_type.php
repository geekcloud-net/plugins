<?php
if(!defined('ABSPATH')){
	exit;
}
global $post;
extract( $args );
$db_value =  get_post_meta( $post->ID, $id, true );
//$db_value = $db_value === '' ? array() : $db_value;
$cart_rules_options = YITH_WC_Dynamic_Pricing()->cart_rules_options;
$discount_type = isset($db_value['discount_type']) ? $db_value['discount_type'] : '' ;

?>


<?php if ( function_exists( 'yith_field_deps_data' ) ) : ?>
<div id="<?php esc_attr_e( $id ); ?>-container" <?php echo yith_field_deps_data( $args ); ?> class="yith-plugin-fw-metabox-field-row">
	<?php else: ?>
    <div id="<?php esc_attr_e( $id ); ?>-container" <?php if ( isset( $deps ) ): ?> data-field="<?php echo $id ?>" data-dep="<?php echo $deps['ids'] ?>" data-value="<?php echo $deps['values'] ?>" <?php endif ?>>
		<?php endif; ?>
	<label for="<?php esc_attr_e( $id  );?>"><?php echo( $label);?></label>

	<div class="discount-table-rules-wrapper">
        <table class="cart-discount-amount" width="90%">
            <tr>
                <th width="45%"><?php _e( 'Discount Type', 'ywdpd' ); ?></th>
                <th with="45%"><?php _e( 'Amount', 'ywdpd' ); ?></th>
            </tr>
            <tr>
                <td>
                    <select name="<?php echo $name ?>[discount_type]"
                            id="<?php echo $id . '[discount_type]' ?>">
						<?php foreach ( $cart_rules_options['discount_type'] as $key_type => $type ): ?>
                            <option
                                    value="<?php echo $key_type ?>" <?php selected( $discount_type, $key_type ) ?>><?php echo $type ?></option>
						<?php endforeach ?>
                    </select>
                </td>

                <td>
                    <input type="text" name="<?php echo $name ?>[discount_amount]"
                           id="<?php echo $id . '[discount_amount]' ?>"
                           value="<?php echo ( isset( $db_value['discount_amount'] ) ) ? $db_value['discount_amount'] : '' ?>" />
                </td>
            </tr>
        </table>

	</div>

</div>