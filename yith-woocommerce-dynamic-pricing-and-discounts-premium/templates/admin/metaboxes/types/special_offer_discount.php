<?php
if(!defined('ABSPATH')){
	exit;
}
global $post;
extract( $args );
$db_value =  get_post_meta( $post->ID, $id, true );

//$db_value = $db_value === '' ? array() : $db_value;
$limit = empty( $db_value ) ? 1 : count( $db_value ) ;
$pricing_rules_options = YITH_WC_Dynamic_Pricing()->pricing_rules_options;

?>

<?php if ( function_exists( 'yith_field_deps_data' ) ) : ?>
<div id="<?php esc_attr_e( $id ); ?>-container" <?php echo yith_field_deps_data( $args ); ?> class="yith-plugin-fw-metabox-field-row">
	<?php else: ?>
    <div id="<?php esc_attr_e( $id ); ?>-container" <?php if ( isset( $deps ) ): ?> data-field="<?php echo $id ?>" data-dep="<?php echo $deps['ids'] ?>" data-value="<?php echo $deps['values'] ?>" <?php endif ?>>
		<?php endif; ?>
	<label for="<?php esc_attr_e( $id  );?>"><?php echo( $label);?></label>

	<div class="discount-table-rules-wrapper">
        <table class="special-offers-rules">
            <tr>
                <th><?php _e( 'Purchase', 'ywdpd' ) ?></th>
                <th><?php _e( 'Receive', 'ywdpd' ) ?></th>
                <th><?php _e( 'Type of Discount', 'ywdpd' ) ?></th>
                <th><?php _e( 'Discount Amount', 'ywdpd' ) ?></th>
                <th><?php _e( 'Repeat', 'ywdpd' ) ?></th>
            </tr>
            <tr>
                <td>
                    <input type="text"
                           name="<?php echo $name . "[purchase]" ?>"
                           id="<?php echo $id . "[purchase]" ?>"
                           value="<?php echo isset( $db_value['purchase'] ) ? $db_value['purchase'] : '' ?>"
                           placeholder="<?php _e( 'e.g. 5', 'ywdpd' ) ?>">
                </td>
                <td>
                    <input type="text"
                           name="<?php echo $name . "[receive]" ?>"
                           id="<?php echo $id . "[receive]" ?>"
                           value="<?php echo isset( $db_value['receive'] ) ? $db_value['receive'] : '' ?>"
                           placeholder="<?php _e( 'e.g. 10 - * for unlimited items', 'ywdpd' ) ?>">
                </td>
                <td>
                    <select name="<?php echo $name . "[type_discount]" ?>"
                            id="<?php echo $id . "[type_discount]" ?>">
						<?php foreach ( $pricing_rules_options['type_of_discount'] as $key_type => $type ): ?>
                            <option
                                    value="<?php echo $key_type ?>" <?php if( isset($db_value['type_discount']) ) {
								selected( $db_value['type_discount'], $key_type );
							} ?>><?php echo $type ?></option>
						<?php endforeach ?>
                    </select>
                </td>
                <td>
                    <input type="text"
                           name="<?php echo $name . "[discount_amount]" ?>"
                           id="<?php echo $id . "[discount_amount]" ?>"
                           value="<?php echo isset( $db_value['discount_amount'] ) ? $db_value['discount_amount'] : '' ?>"
                           placeholder="<?php _e( 'e.g. 50', 'ywdpd' ) ?>">
                </td>
                <td>
                    <input type="checkbox"
                           name="<?php echo $name . "[repeat]" ?>"
                           id="<?php echo $id . "[repeat]" ?>"
                           value="1" <?php echo ( isset( $db_value['repeat'] ) && $db_value['repeat'] == 1 ) ? 'checked' : '' ?> />
                </td>
            </tr>
        </table>

	</div>

</div>