<?php
if(!defined('ABSPATH')){
	exit;
}
global $post;
extract( $args );
$value =  get_post_meta( $post->ID, $id, true );
$value = ! is_array( $value ) ? explode( ',', $value ) : $value;

//Products
$product_string = array();

if ( ! empty( $value ) ) {
    foreach ( $value as $key => $product_id ){
	    $product        = wc_get_product( $product_id );
	    if( $product ){
		    $product_string[ $product_id ]= wp_kses_post( html_entity_decode( $product->get_formatted_name() ) );
	    }else{
	        unset( $value[$key] );
        }
    }
}
?>
<?php if( function_exists( 'yith_field_deps_data' ) ) : ?>
    <div id="<?php esc_attr_e($id);?>-container" <?php echo yith_field_deps_data( $args ); ?> class="yith-plugin-fw-metabox-field-row">
<?php else: ?>
<div id="<?php esc_attr_e($id);?>-container" <?php if ( isset( $deps ) ): ?> data-field="<?php echo $id ?>" data-dep="<?php echo $deps['ids'] ?>" data-value="<?php echo $deps['values'] ?>" <?php endif ?>>
<?php endif; ?>
	<label for="<?php esc_attr_e( $id  );?>"><?php echo( $label);?></label>

	<?php
	if ( function_exists( 'yit_add_select2_fields' ) ) {
		$args = array(
			'type'             => 'hidden',
			'class'            => 'wc-product-search',
			'id'               =>  $id,
			'name'             =>  $name,
			'data-placeholder' => esc_attr( $placeholder ),
			'data-allow_clear' => true,
			'data-selected'    => $product_string,
			'data-multiple'    => true,
			'value'            => implode( ',', $value ),
			'style'            => 'width:90%',
		);

		yit_add_select2_fields( $args );
	} ?>

</div>