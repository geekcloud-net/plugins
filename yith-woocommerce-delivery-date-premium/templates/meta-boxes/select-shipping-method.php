<?php
if(!defined('ABSPATH')){
	exit;
}
global $post;

$shipping_method = get_post_meta( $post->ID,'_ywcdd_shipping_method', true );
if( version_compare( WC()->version,'2.6.0' ,'>=' ) ) {
	$shipping_zones = WC_Shipping_Zones::get_zones();
	$global_zone = new WC_Shipping_Zone( 0 );
	$shipping_method = explode(':', $shipping_method );// first element zone_id, second element shipping_method_id
	$selected_zone_id = isset( $shipping_method[0] ) ? $shipping_method[0] : '';
	$selected_shipping_id = isset( $shipping_method[1] ) ? $shipping_method[1] : '';
	
	$show_shipping_method = empty( $selected_zone_id ) ? 'ywcdd_hide' : '';
	?>
	<div class="ywcdd_select_shipping_method">
		<div id="ywcdd_select_shpping_zone">
			<label for="ywcdd_shipping_zone"><?php _e( 'Shipping Zone', 'yith-woocommerce-delivery-date' ); ?></label>
			<select id="ywcdd_shipping_zone"  name="ywcdd_shipping[zone]">
				<option value="" <?php selected('', $selected_zone_id );?>><?php _e('Select  a Shipping Zone','yith-woocommerce-delivery-date' );?></option>
				<?php foreach ( $shipping_zones as $zone ): ?>
					<option value="<?php esc_attr_e( $zone['zone_id'] ); ?>" <?php selected( $zone['zone_id'], $selected_zone_id );?>><?php echo $zone['zone_name']; ?></option>
				<?php endforeach; ?>
				<option value="<?php esc_attr_e( $global_zone->get_zone_id() ); ?>" <?php selected( $global_zone->get_zone_id(), $selected_zone_id );?> ><?php echo $global_zone->get_zone_name(); ?></option>
			</select>
		</div>
		<div id="ywcdd_shipping_method_content" class="<?php echo $show_shipping_method;?>">
			<label for="ywcdd_shipping_method"><?php _e('Shipping Method', 'yith-woocommerce-delivery-date' );?></label>
			<select id="ywcdd_shipping_method" name="ywcdd_shipping[method]">
				<option value=""><?php _e('Select a Shipping Method','yith-woocommerce-delivery-date' );?></option>
			</select>
		</div>
		<label for="ywcdd_mandatory"><?php _e('Set as Mandatory','yith-woocommerce-delivery' );?></label>
		<input type="checkbox" value="1" id="ywcdd_mandatory" name="ywcdd_shipping[mandatory]">
		<input type="hidden" id="ywcdd_shipping_method_value" value="<?php esc_attr_e( $selected_shipping_id );?>">
	</div>
	<?php
}
