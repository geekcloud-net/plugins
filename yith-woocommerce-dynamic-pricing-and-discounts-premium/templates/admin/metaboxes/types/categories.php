<?php
if(!defined('ABSPATH')){
	exit;
}
global $post;
extract( $args );
$value =  get_post_meta( $post->ID, $id, true );
$value = ! is_array( $value ) ? explode( ',', $value ) : $value;

//Products
$category_string = array();
$new_value = array();
if ( ! empty( $value ) ) {
    foreach ( $value as $key => $term_id ){
        $search_by = is_numeric( $term_id ) ? 'id' : 'slug';
	    $term      = get_term_by( $search_by, $term_id, 'product_cat' );
	    if( $term ){
		    $category_string[ $term->term_id ]= $term->formatted_name .= $term->name . ' (' . $term->count . ')';
		    $new_value[] = $term->term_id;
	    }
    }
}

?>

<?php if ( function_exists( 'yith_field_deps_data' ) ) : ?>
<div id="<?php esc_attr_e( $id ); ?>-container" <?php echo yith_field_deps_data( $args ); ?> class="yith-plugin-fw-metabox-field-row">
	<?php else: ?>
    <div id="<?php esc_attr_e( $id ); ?>-container" <?php if ( isset( $deps ) ): ?> data-field="<?php echo $id ?>" data-dep="<?php echo $deps['ids'] ?>" data-value="<?php echo $deps['values'] ?>" <?php endif ?>>
		<?php endif; ?>
	<label for="<?php esc_attr_e( $id  );?>"><?php echo( $label);?></label>

	<?php
	if ( function_exists( 'yit_add_select2_fields' ) ) {
		$args = array(
			'type'             => 'hidden',
			'class'            => 'yith-categories-select wc-product-search',
			'id'               =>  $id,
			'name'             =>  $name,
			'data-placeholder' => esc_attr( $placeholder ),
			'data-allow_clear' => true,
			'data-selected'    => $category_string,
			'data-multiple'    => true,
			'data-action'      => 'ywdpd_json_search_categories',
			'value'            => implode( ',', $new_value ),
			'style'            => 'width:90%',
		);

		yit_add_select2_fields( $args );
	} ?>

</div>