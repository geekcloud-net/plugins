<?php
if(!defined('ABSPATH')){
	exit;
}
global $post;
extract( $args );
$db_value =  get_post_meta( $post->ID, $id, true );
//$db_value = $db_value === '' ? array() : $db_value;
$limit = empty( $db_value ) ? 1 : count( $db_value ) ;
$cart_rules_options = YITH_WC_Dynamic_Pricing()->cart_rules_options;
$rules              = YITH_WC_Dynamic_Pricing_Helper()->get_roles();

?>

<?php if ( function_exists( 'yith_field_deps_data' ) ) : ?>
<div id="<?php esc_attr_e( $id ); ?>-container" <?php echo yith_field_deps_data( $args ); ?> class="yith-plugin-fw-metabox-field-row">
	<?php else: ?>
    <div id="<?php esc_attr_e( $id ); ?>-container" <?php if ( isset( $deps ) ): ?> data-field="<?php echo $id ?>" data-dep="<?php echo $deps['ids'] ?>" data-value="<?php echo $deps['values'] ?>" <?php endif ?>>
		<?php endif; ?>
	<label for="<?php esc_attr_e( $id  );?>"><?php echo( $label);?></label>

	<div class="discount-table-rules-wrapper">
        <table class="discount-rules">
            <tr>
                <th width="45%"><?php _e( 'Rule', 'ywdpd' ) ?></th>
                <th width="45%"><?php _e( 'Value', 'ywdpd' ) ?></th>
                <th></th>
            </tr>
			<?php


			for ( $i = 1; $i <= $limit; $i ++ ):
				$hide_first_remove = ( $i == 1 ) ? ' hide-remove' : '';
				?>
                <tr data-index="<?php echo $i ?>" class="ywdpd-select-wrapper">
                    <td>
                        <select
                                name="<?php echo $name . "[{$i}][rules_type]" ?>"
                                id="<?php echo $id . "[{$i}][rules_type]" ?>"
                                class="yith-ywdpd-eventype-select" data-field="rules_type" data-value="">
							<?php foreach ( $cart_rules_options['rules_type'] as $key_c => $type_c ): ?>
								<?php if ( isset( $type_c['label'] ) ): ?>
                                    <optgroup label="<?php echo $type_c['label'] ?>">
										<?php foreach ( $type_c['options'] as $key_opt => $value_opt ): ?>
                                            <option
                                                    value="<?php echo $key_opt ?>" <?php ( isset( $db_value[ $i ]['rules_type'] ) ) ? selected( $db_value[ $i ]['rules_type'], $key_opt ) : '' ?> ><?php echo $value_opt ?></option>
										<?php endforeach ?>
                                    </optgroup>
								<?php endif ?>
							<?php endforeach ?>
                        </select>
                    </td>
                    <td>
                        <table>
                            <tr class="deps-rules_type" data-type="role_list">
                                <td>
                                    <select
                                            name="<?php echo $name . "[{$i}][rules_type_role_list][]" ?>"
                                            multiple="multiple"
                                            id="<?php echo $name . "[{$i}][rules_type_role_list][]" ?>"
                                            data-placeholder="<?php _e( 'Select a role', 'ywdpd' ) ?>" class="wc-enhanced-select" style="width:100%">
		                                <?php foreach ( $rules as $key_rule => $rule ):
			                                ?>
                                            <option
                                                    value="<?php echo $key_rule ?>" <?php isset( $db_value[ $i ]['rules_type_role_list'] ) ? selected( in_array( $key_rule, $db_value[ $i ]['rules_type_role_list'] ) ) : '' ?>><?php echo $rule ?></option>
		                                <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>

                            <tr class="deps-rules_type" data-type="role_list_excluded">
                                <td>
                                    <select
                                            name="<?php echo $name . "[{$i}][rules_type_role_list_excluded][]" ?>"
                                            multiple="multiple"
                                            id="<?php echo $name . "[{$i}][rules_type_role_list_excluded][]" ?>"
                                            data-placeholder="<?php _e( 'Select a role', 'ywdpd' ) ?>" class="wc-enhanced-select" style="width:100%">
				                        <?php foreach ( $rules as $key_rule => $rule ):
					                        ?>
                                            <option
                                                    value="<?php echo $key_rule ?>" <?php isset( $db_value[ $i ]['rules_type_role_list_excluded'] ) ? selected( in_array( $key_rule, $db_value[ $i ]['rules_type_role_list_excluded'] ) ) : '' ?>><?php echo $rule ?></option>
				                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>

                            <tr class="deps-rules_type" data-type="customers_list">
                                <td>
	                                <?php
	                                $value          = isset( $db_value[ $i ]['rules_type_customers_list'] ) ? $db_value[ $i ]['rules_type_customers_list'] : '';
	                                $value          = ! is_array( $value ) ? explode( ',', $value ) : $value;
	                                // Customers, products
	                                $user_string = array();
	                                if ( ! empty( $value ) ) {
		                                foreach ( $value as $key => $customer_id ){
			                                $user        = get_user_by( 'id', $customer_id );
			                                if ( $user ) {
				                                $user_string[ $customer_id ] = esc_html( $user->display_name ) . ' (#' . absint( $user->ID ) . ' &ndash; ' . esc_html( $user->user_email );
			                                } else {
				                                unset( $value[ $key ] );
			                                }
		                                }
	                                }

	                                if ( function_exists( 'yit_add_select2_fields' ) ) {
		                                $args = array(
			                                'type'             => 'hidden',
			                                'class'            => 'wc-customer-search',
			                                'id'               =>  $id . "[{$i}][rules_type_customers_list]",
			                                'name'             =>  $name . "[{$i}][rules_type_customers_list][]",
			                                'data-placeholder' => esc_attr( __( 'Search for a customer', 'ywdpd' )  ),
			                                'data-allow_clear' => true,
			                                'data-selected'    => $user_string,
			                                'data-multiple'    => true,
			                                'value'            => implode( ',', $value ),
			                                'style'            => 'width:500px',
		                                );

		                                yit_add_select2_fields( $args );
	                                } ?>

                                </td>
                            </tr>

                            <tr class="deps-rules_type" data-type="customers_list_excluded">
                                <td>
	                                <?php
	                                $value          = isset( $db_value[ $i ]['rules_type_customers_list_excluded'] ) ? $db_value[ $i ]['rules_type_customers_list_excluded'] : '';
	                                $value          = ! is_array( $value ) ? explode( ',', $value ) : $value;
	                                // Customers, products
	                                $user_string = array();
	                                if ( ! empty( $value ) ) {
		                                foreach ( $value as $key => $customer_id ){
			                                $user        = get_user_by( 'id', $customer_id );
			                                if ( $user ) {
				                                $user_string[ $customer_id ] = esc_html( $user->display_name ) . ' (#' . absint( $user->ID ) . ' &ndash; ' . esc_html( $user->user_email );
			                                } else {
				                                unset( $value[ $key ] );
			                                }
		                                }
	                                }


	                                if ( function_exists( 'yit_add_select2_fields' ) ) {
		                                $args = array(
			                                'type'             => 'hidden',
			                                'class'            => 'wc-customer-search',
			                                'id'               =>  $id . "[{$i}][rules_type_customers_list_excluded]",
			                                'name'             =>  $name . "[{$i}][rules_type_customers_list_excluded][]",
			                                'data-placeholder' => esc_attr( __( 'Search for a customer', 'ywdpd' )  ),
			                                'data-allow_clear' => true,
			                                'data-selected'    => $user_string,
			                                'data-multiple'    => true,
			                                'value'            => $value,
			                                'style'            => 'width:500px',
		                                );

		                                yit_add_select2_fields( $args );
	                                } ?>

                                </td>
                            </tr>

                            <tr class="deps-rules_type" data-type="num_of_orders">
                                <td>
                                    <input type="text"
                                           name="<?php echo $name . "[{$i}][rules_type_num_of_orders]" ?>"
                                           id="<?php echo $name . "[{$i}][rules_type_num_of_orders]" ?>"
                                           placeholder="<?php _e( 'Minimum number of orders required', 'ywdpd' ) ?>"
                                           value="<?php echo ( isset( $db_value[ $i ]['rules_type_num_of_orders'] ) ) ? $db_value[ $i ]['rules_type_num_of_orders'] : '' ?>" />
                                </td>
                            </tr>

                            <tr class="deps-rules_type" data-type="max_num_of_orders">
                                <td>
                                    <input type="text"
                                           name="<?php echo $name . "[{$i}][rules_type_max_num_of_orders]" ?>"
                                           id="<?php echo $name . "[{$i}][rules_type_max_num_of_orders]" ?>"
                                           placeholder="<?php _e( 'Maximum number of orders required', 'ywdpd' ) ?>"
                                           value="<?php echo ( isset( $db_value[ $i ]['rules_type_max_num_of_orders'] ) ) ? $db_value[ $i ]['rules_type_max_num_of_orders'] : '' ?>" />
                                </td>
                            </tr>

                            <tr class="deps-rules_type" data-type="amount_spent">
                                <td>
                                    <input type="text"
                                           name="<?php echo $name . "[{$i}][rules_type_amount_spent]" ?>"
                                           id="<?php echo $name . "[{$i}][rules_type_amount_spent]" ?>"
                                           placeholder="<?php _e( 'Minimum past expense required', 'ywdpd' ) ?>"
                                           value="<?php echo ( isset( $db_value[ $i ]['rules_type_amount_spent'] ) ) ? $db_value[ $i ]['rules_type_amount_spent'] : '' ?>">
                                </td>
                            </tr>

                            <tr class="deps-rules_type" data-type="max_amount_spent">
                                <td>
                                    <input type="text"
                                           name="<?php echo $name . "[{$i}][rules_type_max_amount_spent]" ?>"
                                           id="<?php echo $name . "[{$i}][rules_type_max_amount_spent]" ?>"
                                           placeholder="<?php _e( 'Maximum past expense required', 'ywdpd' ) ?>"
                                           value="<?php echo ( isset( $db_value[ $i ]['rules_type_max_amount_spent'] ) ) ? $db_value[ $i ]['rules_type_max_amount_spent'] : '' ?>">
                                </td>
                            </tr>

                            <tr class="deps-rules_type" data-type="products_list">
                                <td>
	                                <?php
	                                $value          = isset( $db_value[ $i ]['rules_type_products_list'] ) ? $db_value[ $i ]['rules_type_products_list'] : '';
	                                $value          = ! is_array( $value ) ? explode( ',', $value ) : $value;
	                                $product_string = array();

	                                if ( ! empty( $value ) ) {
		                                foreach ( $value as $key => $product_id ) {
			                                $product = wc_get_product( $product_id );
			                                if ( $product ) {
				                                $product_string[ $product_id ] = wp_kses_post( html_entity_decode( $product->get_formatted_name() ) );
			                                } else {
				                                unset( $value[ $key ] );
			                                }
		                                }
	                                }

	                                if ( function_exists( 'yit_add_select2_fields' ) ) {
		                                $args = array(
			                                'type'             => 'hidden',
			                                'class'            => 'wc-product-search',
			                                'id'               => $id . "[{$i}][rules_type_products_list]",
			                                'name'             => $name . "[{$i}][rules_type_products_list][]",
			                                'data-placeholder' => esc_attr( __( 'Search for a product', 'ywdpd' ) ),
			                                'data-allow_clear' => true,
			                                'data-selected'    => $product_string,
			                                'data-multiple'    => true,
			                                'value'            => implode( ',', $value ),
			                                'style'            => 'width:100%',
		                                );

		                                yit_add_select2_fields( $args );
	                                }
	                                ?>

                                </td>
                            </tr>

                            <tr class="deps-rules_type" data-type="products_list_and">
                                <td>
	                                <?php
	                                $value          = isset( $db_value[ $i ]['rules_type_products_list_and'] ) ? $db_value[ $i ]['rules_type_products_list_and'] : '';
	                                $value          = ! is_array( $value ) ? explode( ',', $value ) : $value;
	                                $product_string = array();

	                                if ( ! empty( $value  ) ) {
		                                foreach ( $value  as $key => $product_id ){
			                                $product        = wc_get_product( $product_id );
			                                if( $product ){
				                                $product_string[ $product_id ]= wp_kses_post( html_entity_decode( $product->get_formatted_name() ) );
			                                }else{
				                                unset( $value[$key] );
			                                }
		                                }

	                                }

	                                if ( function_exists( 'yit_add_select2_fields' ) ) {
		                                $args = array(
			                                'type'             => 'hidden',
			                                'class'            => 'wc-product-search',
			                                'id'               =>  $id . "[{$i}][rules_type_products_list_and]",
			                                'name'             =>  $name . "[{$i}][rules_type_products_list_and][]",
			                                'data-placeholder' => esc_attr( __( 'Search for a product', 'ywdpd' )  ),
			                                'data-allow_clear' => true,
			                                'data-selected'    => $product_string,
			                                'data-multiple'    => true,
			                                'value'            => implode( ',', $value ),
			                                'style'            => 'width:100%',
		                                );

		                                yit_add_select2_fields( $args );
	                                } ?>

                                </td>
                            </tr>

                            <tr class="deps-rules_type" data-type="products_list_excluded">
                                <td>
	                                <?php
	                                $value          = isset( $db_value[ $i ]['rules_type_products_list_excluded'] ) ? $db_value[ $i ]['rules_type_products_list_excluded'] : '';
	                                $value          = ! is_array( $value ) ? explode( ',', $value ) : $value;
	                                $product_string = array();

	                                if ( ! empty( $value  ) ) {
		                                foreach ( $value  as $key => $product_id ){
			                                $product        = wc_get_product( $product_id );
			                                if( $product ){
				                                $product_string[ $product_id ]= wp_kses_post( html_entity_decode( $product->get_formatted_name() ) );
			                                }else{
				                                unset( $value[$key] );
			                                }
		                                }

	                                }


	                                if ( function_exists( 'yit_add_select2_fields' ) ) {
		                                $args = array(
			                                'type'             => 'hidden',
			                                'class'            => 'wc-product-search',
			                                'id'               =>  $id . "[{$i}][rules_type_products_list_excluded]",
			                                'name'             =>  $name . "[{$i}][rules_type_products_list_excluded][]",
			                                'data-placeholder' => esc_attr( __( 'Search for a product', 'ywdpd' )  ),
			                                'data-allow_clear' => true,
			                                'data-selected'    => $product_string,
			                                'data-multiple'    => true,
			                                'value'            => implode( ',', $value ),
			                                'style'            => 'width:100%',
		                                );

		                                yit_add_select2_fields( $args );
	                                } ?>

                                </td>
                            </tr>

                            <tr class="deps-rules_type" data-type="categories_list">
                                <td>
	                                <?php
	                                $value          = isset( $db_value[ $i ]['rules_type_categories_list'] ) ? $db_value[ $i ]['rules_type_categories_list'] : '';
	                                $value          = ! is_array( $value ) ? explode( ',', $value ) : $value;
	                                //Products
	                                $category_string = array();
	                                $new_value = array();
	                                if ( ! empty( $value ) ) {
		                                foreach ( $value as $key => $term_id ){
			                                $search_by = is_numeric( $term_id ) ? 'id' : 'slug';
			                                $term        = get_term_by( $search_by, $term_id, 'product_cat' );
			                                if( $term ){
				                                $category_string[ $term->term_id ]= $term->formatted_name .= $term->name . ' (' . $term->count . ')';
				                                $new_value[] = $term->term_id;
			                                }
		                                }
	                                }

	                                if ( function_exists( 'yit_add_select2_fields' ) ) {
		                                $args = array(
			                                'type'             => 'hidden',
			                                'class'            => 'yith-categories-select wc-product-search',
			                                'id'               =>  $id . "[{$i}][rules_type_categories_list]",
			                                'name'             =>  $name . "[{$i}][rules_type_categories_list][]",
			                                'data-placeholder' => esc_attr( __( 'Search for a category', 'ywdpd' )  ),
			                                'data-allow_clear' => true,
			                                'data-selected'    => $category_string,
			                                'data-multiple'    => true,
			                                'data-action'      => 'ywdpd_json_search_categories',
			                                'value'            => implode( ',', $new_value ),
			                                'style'            => 'width:100%',
		                                );

		                                yit_add_select2_fields( $args );
	                                } ?>

                                </td>
                            </tr>

                            <tr class="deps-rules_type" data-type="categories_list_and">
                                <td>
	                                <?php
	                                $value          = isset( $db_value[ $i ]['rules_type_categories_list_and'] ) ? $db_value[ $i ]['rules_type_categories_list_and'] : '';
	                                $value          = ! is_array( $value ) ? explode( ',', $value ) : $value;
	                                //Products
	                                $category_string = array();
	                                $new_value = array();
	                                if ( ! empty( $value ) ) {
		                                foreach ( $value as $key => $term_id ){
			                                $search_by = is_numeric( $term_id ) ? 'id' : 'slug';
			                                $term        = get_term_by( $search_by, $term_id, 'product_cat' );
			                                if( $term ){
				                                $category_string[ $term->term_id ]= $term->formatted_name .= $term->name . ' (' . $term->count . ')';
				                                $new_value[] = $term->term_id;
			                                }
		                                }
	                                }

	                                if ( function_exists( 'yit_add_select2_fields' ) ) {
		                                $args = array(
			                                'type'             => 'hidden',
			                                'class'            => 'yith-categories-select wc-product-search',
			                                'id'               =>  $id . "[{$i}][rules_type_categories_list_and]",
			                                'name'             =>  $name . "[{$i}][rules_type_categories_list_and][]",
			                                'data-placeholder' => esc_attr( __( 'Search for a category', 'ywdpd' )  ),
			                                'data-allow_clear' => true,
			                                'data-selected'    => $category_string,
			                                'data-multiple'    => true,
			                                'data-action'      => 'ywdpd_json_search_categories',
			                                'value'            => implode( ',', $new_value ),
			                                'style'            => 'width:100%',
		                                );

		                                yit_add_select2_fields( $args );
	                                } ?>

                                </td>
                            </tr>

                            <tr class="deps-rules_type"
                                data-type="categories_list_excluded">
                                <td><?php
	                                $value          = isset( $db_value[ $i ]['rules_type_categories_list_excluded'] ) ? $db_value[ $i ]['rules_type_categories_list_excluded'] : '';
	                                $value          = ! is_array( $value ) ? explode( ',', $value ) : $value;
	                                $category_string = array();
	                                $new_value = array();
	                                if ( ! empty( $value ) ) {
		                                foreach ( $value as $key => $term_id ){
			                                $search_by = is_numeric( $term_id ) ? 'id' : 'slug';
			                                $term        = get_term_by( $search_by, $term_id, 'product_cat' );
			                                if( $term ){
				                                $category_string[ $term->term_id ]= $term->formatted_name .= $term->name . ' (' . $term->count . ')';
				                                $new_value[] = $term->term_id;
			                                }
		                                }
	                                }

	                                if ( function_exists( 'yit_add_select2_fields' ) ) {
		                                $args = array(
			                                'type'             => 'hidden',
			                                'class'            => 'yith-categories-select wc-product-search',
			                                'id'               =>  $id . "[{$i}][rules_type_categories_list_excluded]",
			                                'name'             =>  $name . "[{$i}][rules_type_categories_list_excluded][]",
			                                'data-placeholder' => esc_attr( __( 'Search for a category', 'ywdpd' )  ),
			                                'data-allow_clear' => true,
			                                'data-selected'    => $category_string,
			                                'data-multiple'    => true,
			                                'data-action'      => 'ywdpd_json_search_categories',
			                                'value'            => implode( ',', $new_value ),
			                                'style'            => 'width:100%',
		                                );

		                                yit_add_select2_fields( $args );
	                                } ?>

                                </td>
                            </tr>

                            <tr class="deps-rules_type" data-type="tags_list">
                                <td>
	                                <?php
	                                $value          = isset( $db_value[ $i ]['rules_type_tags_list'] ) ? $db_value[ $i ]['rules_type_tags_list'] : '';
	                                $value          = ! is_array( $value ) ? explode( ',', $value ) : $value;
	                                $tags_string = array();
                                    $new_value = array();
	                                if ( ! empty( $value ) ) {
		                                foreach ( $value as $key => $term_id ){
			                                $search_by = is_numeric( $term_id ) ? 'id' : 'slug';
			                                $term        = get_term_by( $search_by, $term_id, 'product_tag' );
			                                if ( $term ) {
				                                $tags_string[ $term->term_id ] = $term->formatted_name .= $term->name . ' (' . $term->count . ')';
				                                $new_value[] = $term->term_id;
			                                }
		                                }
	                                }

	                                if ( function_exists( 'yit_add_select2_fields' ) ) {
		                                $args = array(
			                                'type'             => 'hidden',
			                                'class'            => 'yith-tags-select wc-product-search',
			                                'id'               =>  $id . "[{$i}][rules_type_tags_list]",
			                                'name'             =>  $name . "[{$i}][rules_type_tags_list][]",
			                                'data-placeholder' => esc_attr( __( 'Search for a tag', 'ywdpd' )  ),
			                                'data-allow_clear' => true,
			                                'data-selected'    => $tags_string,
			                                'data-multiple'    => true,
			                                'data-action'      => 'ywdpd_json_search_tags',
			                                'value'            => implode( ',', $new_value ),
			                                'style'            => 'width:100%',
		                                );

		                                yit_add_select2_fields( $args );
	                                } ?>

                                </td>
                            </tr>

                            <tr class="deps-rules_type" data-type="tags_list_and">
                                <td>
	                                <?php
	                                $value          = isset( $db_value[ $i ]['rules_type_tags_list_and'] ) ? $db_value[ $i ]['rules_type_tags_list_and'] : '';
	                                $value          = ! is_array( $value ) ? explode( ',', $value ) : $value;
	                                $tags_string = array();
	                                $new_value = array();
	                                if ( ! empty( $value ) ) {
		                                foreach ( $value as $key => $term_id ){
			                                $search_by = is_numeric( $term_id ) ? 'id' : 'slug';
			                                $term        = get_term_by( $search_by, $term_id, 'product_tag' );
			                                if ( $term ) {
				                                $tags_string[ $term->term_id ] = $term->formatted_name .= $term->name . ' (' . $term->count . ')';
				                                $new_value[] = $term->term_id;
			                                }
		                                }
	                                }

	                                if ( function_exists( 'yit_add_select2_fields' ) ) {
		                                $args = array(
			                                'type'             => 'hidden',
			                                'class'            => 'yith-tags-select wc-product-search',
			                                'id'               =>  $id . "[{$i}][rules_type_tags_list_and]",
			                                'name'             =>  $name . "[{$i}][rules_type_tags_list_and][]",
			                                'data-placeholder' => esc_attr( __( 'Search for a tag', 'ywdpd' )  ),
			                                'data-allow_clear' => true,
			                                'data-selected'    => $tags_string,
			                                'data-multiple'    => true,
			                                'data-action'      => 'ywdpd_json_search_tags',
			                                'value'            => implode( ',', $new_value ),
			                                'style'            => 'width:100%',
		                                );

		                                yit_add_select2_fields( $args );
	                                } ?>

                                </td>
                            </tr>

                            <tr class="deps-rules_type" data-type="tags_list_excluded">
                                <td>
	                                <?php
	                                $value          = isset( $db_value[ $i ]['rules_type_tags_list_excluded'] ) ? $db_value[ $i ]['rules_type_tags_list_excluded'] : '';
	                                $value          = ! is_array( $value ) ? explode( ',', $value ) : $value;
	                                $tags_string = array();
	                                $new_value = array();
	                                if ( ! empty( $value ) ) {
		                                foreach ( $value as $key => $term_id ){
			                                $search_by = is_numeric( $term_id ) ? 'id' : 'slug';
			                                $term        = get_term_by( $search_by, $term_id, 'product_tag' );
			                                if ( $term ) {
				                                $tags_string[ $term->term_id ] = $term->formatted_name .= $term->name . ' (' . $term->count . ')';
				                                $new_value[] = $term->term_id;
			                                }
		                                }
	                                }

	                                if ( function_exists( 'yit_add_select2_fields' ) ) {
		                                $args = array(
			                                'type'             => 'hidden',
			                                'class'            => 'yith-tags-select wc-product-search',
			                                'id'               =>  $id . "[{$i}][rules_type_tags_list_excluded]",
			                                'name'             =>  $name . "[{$i}][rules_type_tags_list_excluded][]",
			                                'data-placeholder' => esc_attr( __( 'Search for a tag', 'ywdpd' )  ),
			                                'data-allow_clear' => true,
			                                'data-selected'    => $tags_string,
			                                'data-multiple'    => true,
			                                'data-action'      => 'ywdpd_json_search_tags',
			                                'value'            => implode( ',', $new_value ),
			                                'style'            => 'width:100%',
		                                );

		                                yit_add_select2_fields( $args );
	                                } ?>

                                </td>
                            </tr>

							<?php if ( defined( 'YITH_WCBR_PREMIUM_INIT' ) ): ?>
                                <tr class="deps-rules_type" data-type="brand_list">
                                    <td>
	                                    <?php
	                                    $value          = isset( $db_value[ $i ]['rules_type_brand_list'] ) ? $db_value[ $i ]['rules_type_brand_list'] : '';
	                                    $value          = ! is_array( $value ) ? explode( ',', $value ) : $value;
	                                    $tags_string = array();
	                                    $new_value = array();
	                                    if ( ! empty( $value ) ) {
		                                    foreach ( $value as $key => $term_id ){
			                                    $search_by = is_numeric( $term_id ) ? 'id' : 'slug';
			                                    $term        = get_term_by( $search_by, $term_id, YITH_WCBR::$brands_taxonomy );
			                                    if ( $term ) {
				                                    $tags_string[ $term->term_id ] = $term->formatted_name .= $term->name . ' (' . $term->count . ')';
				                                    $new_value[] = $term->term_id;
			                                    }
		                                    }
	                                    }

	                                    if ( function_exists( 'yit_add_select2_fields' ) ) {
		                                    $args = array(
			                                    'type'             => 'hidden',
			                                    'class'            => 'yith-brands-select wc-product-search',
			                                    'id'               =>  $id . "[{$i}][rules_type_brand_list]",
			                                    'name'             =>  $name . "[{$i}][rules_type_brand_list][]",
			                                    'data-placeholder' => esc_attr( __( 'Search for a brand', 'ywdpd' )  ),
			                                    'data-allow_clear' => true,
			                                    'data-selected'    => $tags_string,
			                                    'data-multiple'    => true,
			                                    'data-action'      => 'ywdpd_brand_search',
			                                    'value'            => implode( ',', $new_value ),
			                                    'style'            => 'width:100%',
		                                    );

		                                    yit_add_select2_fields( $args );
	                                    } ?>
                                    </td>
                                </tr>

                                <tr class="deps-rules_type" data-type="brand_list_and">
                                    <td>
	                                    <?php
	                                    $value          = isset( $db_value[ $i ]['rules_type_brand_list'] ) ? $db_value[ $i ]['rules_type_brand_list'] : '';
	                                    $value          = ! is_array( $value ) ? explode( ',', $value ) : $value;
	                                    $tags_string = array();
	                                    $new_value = array();
	                                    if ( ! empty( $value ) ) {
		                                    foreach ( $value as $key => $term_id ){
			                                    $search_by = is_numeric( $term_id ) ? 'id' : 'slug';
			                                    $term        = get_term_by( $search_by, $term_id, YITH_WCBR::$brands_taxonomy );
			                                    if ( $term ) {
				                                    $tags_string[ $term->term_id ] = $term->formatted_name .= $term->name . ' (' . $term->count . ')';
				                                    $new_value[] = $term->term_id;
			                                    }
		                                    }
	                                    }

	                                    if ( function_exists( 'yit_add_select2_fields' ) ) {
		                                    $args = array(
			                                    'type'             => 'hidden',
			                                    'class'            => 'yith-brands-select wc-product-search',
			                                    'id'               =>  $id . "[{$i}][rules_type_brand_list_and]",
			                                    'name'             =>  $name . "[{$i}][rules_type_brand_list_and][]",
			                                    'data-placeholder' => esc_attr( __( 'Search for a brand', 'ywdpd' )  ),
			                                    'data-allow_clear' => true,
			                                    'data-selected'    => $tags_string,
			                                    'data-multiple'    => true,
			                                    'data-action'      => 'ywdpd_brand_search',
			                                    'value'            => implode( ',', $new_value ),
			                                    'style'            => 'width:100%',
		                                    );

		                                    yit_add_select2_fields( $args );
	                                    } ?>

                                    </td>
                                </tr>


                                <tr class="deps-rules_type"
                                    data-type="brand_list_excluded">
                                    <td>
	                                    <?php
	                                    $value          = isset( $db_value[ $i ]['rules_type_brand_list_excluded'] ) ? $db_value[ $i ]['rules_type_brand_list_excluded'] : '';
	                                    $value          = ! is_array( $value ) ? explode( ',', $value ) : $value;
	                                    $new_value = array();
	                                    $tags_string = array();
	                                    if ( ! empty( $value ) ) {
		                                    foreach ( $value as $key => $term_id ){
			                                    $search_by = is_numeric( $term_id ) ? 'id' : 'slug';
			                                    $term        = get_term_by( $search_by, $term_id, YITH_WCBR::$brands_taxonomy );
			                                    if ( $term ) {
				                                    $tags_string[ $term->term_id ] = $term->formatted_name .= $term->name . ' (' . $term->count . ')';
				                                    $new_value[] = $term->term_id;
			                                    }
		                                    }
	                                    }

	                                    if ( function_exists( 'yit_add_select2_fields' ) ) {
		                                    $args = array(
			                                    'type'             => 'hidden',
			                                    'class'            => 'yith-brands-select wc-product-search',
			                                    'id'               =>  $id . "[{$i}][rules_type_brand_list_excluded]",
			                                    'name'             =>  $name . "[{$i}][rules_type_brand_list_excluded][]",
			                                    'data-placeholder' => esc_attr( __( 'Search for a brand', 'ywdpd' )  ),
			                                    'data-allow_clear' => true,
			                                    'data-selected'    => $tags_string,
			                                    'data-multiple'    => true,
			                                    'data-action'      => 'ywdpd_brand_search',
			                                    'value'            => implode( ',', $new_value ),
			                                    'style'            => 'width:100%',
		                                    );

		                                    yit_add_select2_fields( $args );
	                                    } ?>

                                    </td>
                                </tr>

							<?php endif ?>


							<?php if ( defined( 'YITH_WPV_PREMIUM' ) ): ?>
                                <tr class="deps-rules_type" data-type="vendor_list">
                                    <td>
	                                    <?php
	                                    $value          = isset( $db_value[ $i ]['rules_type_vendor_list'] ) ? $db_value[ $i ]['rules_type_vendor_list'] : '';
	                                    $value          = ! is_array( $value ) ? explode( ',', $value ) : $value;
	                                    $tags_string = array();
	                                    $new_value = array();
	                                    if ( ! empty( $value ) ) {
		                                    foreach ( $value as $key => $term_id ){
			                                    $search_by = is_numeric( $term_id ) ? 'id' : 'slug';
			                                    $term        = get_term_by( $search_by, $term_id, YITH_Vendors ()->get_taxonomy_name () );
			                                    if ( $term ) {
				                                    $tags_string[ $term->term_id ] = $term->formatted_name .= $term->name . ' (' . $term->count . ')';
				                                    $new_value[] = $term->term_id;
			                                    }
		                                    }
	                                    }

	                                    if ( function_exists( 'yit_add_select2_fields' ) ) {
		                                    $args = array(
			                                    'type'             => 'hidden',
			                                    'class'            => 'yith-vendor-select wc-product-search',
			                                    'id'               =>  $id . "[{$i}][rules_type_vendor_list]",
			                                    'name'             =>  $name . "[{$i}][rules_type_vendor_list][]",
			                                    'data-placeholder' => esc_attr( __( 'Search for a vendor', 'ywdpd' )  ),
			                                    'data-allow_clear' => true,
			                                    'data-selected'    => $tags_string,
			                                    'data-multiple'    => true,
			                                    'data-action'      => 'ywdpd_vendor_search',
			                                    'value'            => implode( ',', $new_value ),
			                                    'style'            => 'width:100%',
		                                    );


		                                    yit_add_select2_fields( $args );
	                                    } ?>

                                    </td>
                                </tr>


                                <tr class="deps-rules_type"
                                    data-type="vendor_list_excluded">
                                    <td>
	                                    <?php
	                                    $value          = isset( $db_value[ $i ]['rules_type_vendor_list_excluded'] ) ? $db_value[ $i ]['rules_type_vendor_list_excluded'] : '';
	                                    $value          = ! is_array( $value ) ? explode( ',', $value ) : $value;
	                                    $tags_string = array();
	                                    $new_value = array();
	                                    if ( ! empty( $value ) ) {
		                                    foreach ( $value as $key => $term_id ) {
			                                    $search_by               = is_numeric( $term_id ) ? 'id' : 'slug';
			                                    $term                    = get_term_by( $search_by, $term_id, YITH_Vendors()->get_taxonomy_name() );
			                                    if ( $term ) {
				                                    $tags_string[ $term->term_id ] = $term->formatted_name .= $term->name . ' (' . $term->count . ')';
				                                    $new_value[] = $term->term_id;
			                                    }
		                                    }
	                                    }

	                                    if ( function_exists( 'yit_add_select2_fields' ) ) {
		                                    $args = array(
			                                    'type'             => 'hidden',
			                                    'class'            => 'yith-vendor-select wc-product-search',
			                                    'id'               => $id . "[{$i}][rules_type_vendor_list_excluded]",
			                                    'name'             => $name . "[{$i}][rules_type_vendor_list_excluded][]",
			                                    'data-placeholder' => esc_attr( __( 'Search for a vendor', 'ywdpd' ) ),
			                                    'data-allow_clear' => true,
			                                    'data-selected'    => $tags_string,
			                                    'data-multiple'    => true,
			                                    'data-action'      => 'ywdpd_vendor_search',
			                                    'value'            => implode( ',', $new_value ),
			                                    'style'            => 'width:100%',
		                                    );


		                                    yit_add_select2_fields( $args );
	                                    } ?>

                                    </td>
                                </tr>

							<?php endif ?>



                            <tr class="deps-rules_type" data-type="sum_item_quantity">
                                <td>
                                    <input type="text"
                                           name="<?php echo $name . "[{$i}][rules_type_sum_item_quantity]" ?>"
                                           id="<?php echo $name . "[{$i}][rules_type_sum_item_quantity]" ?>"
                                           value="<?php echo ( isset( $db_value[ $i ]['rules_type_sum_item_quantity'] ) ) ? $db_value[ $i ]['rules_type_sum_item_quantity'] : '' ?>">
                                </td>
                            </tr>

                            <tr class="deps-rules_type" data-type="sum_item_quantity_less">
                                <td>
                                    <input type="text"
                                           name="<?php echo $name . "[{$i}][rules_type_sum_item_quantity_less]" ?>"
                                           id="<?php echo $name . "[{$i}][rules_type_sum_item_quantity_less]" ?>"
                                           value="<?php echo ( isset( $db_value[ $i ]['rules_type_sum_item_quantity_less'] ) ) ? $db_value[ $i ]['rules_type_sum_item_quantity_less'] : '' ?>">
                                </td>
                            </tr>

                            <tr class="deps-rules_type"
                                data-type="count_cart_items_at_least">
                                <td>
                                    <input type="text"
                                           name="<?php echo $name . "[{$i}][rules_type_count_cart_items_at_least]" ?>"
                                           id="<?php echo $name . "[{$i}][rules_type_count_cart_items_at_least]" ?>"
                                           value="<?php echo ( isset( $db_value[ $i ]['rules_type_count_cart_items_at_least'] ) ) ? $db_value[ $i ]['rules_type_count_cart_items_at_least'] : '' ?>">
                                </td>
                            </tr>

                            <tr class="deps-rules_type" data-type="count_cart_items_less">
                                <td>
                                    <input type="text"
                                           name="<?php echo $name . "[{$i}][rules_type_count_cart_items_less]" ?>"
                                           id="<?php echo $name . "[{$i}][rules_type_count_cart_items_less]" ?>"
                                           value="<?php echo ( isset( $db_value[ $i ]['rules_type_count_cart_items_less'] ) ) ? $db_value[ $i ]['rules_type_count_cart_items_less'] : '' ?>">
                                </td>
                            </tr>

                            <tr class="deps-rules_type" data-type="subtotal_at_least">
                                <td>
                                    <input type="text"
                                           name="<?php echo $name . "[{$i}][rules_type_subtotal_at_least]" ?>"
                                           id="<?php echo $name . "[{$i}][rules_type_subtotal_at_least]" ?>"
                                           value="<?php echo ( isset( $db_value[ $i ]['rules_type_subtotal_at_least'] ) ) ? $db_value[ $i ]['rules_type_subtotal_at_least'] : '' ?>">
                                </td>
                            </tr>

                            <tr class="deps-rules_type" data-type="subtotal_less">
                                <td>
                                    <input type="text"
                                           name="<?php echo $name . "[{$i}][rules_type_subtotal_less]" ?>"
                                           id="<?php echo $name . "[{$i}][rules_type_subtotal_less]" ?>"
                                           value="<?php echo ( isset( $db_value[ $i ]['rules_type_subtotal_less'] ) ) ? $db_value[ $i ]['rules_type_subtotal_less'] : '' ?>">
                                </td>
                            </tr>


                        </table>
                    </td>
                    <td><span class="add-row"></span><span
                                class="remove-row <?php echo $hide_first_remove ?>"></span>
                    </td>
                </tr>
			<?php endfor; ?>
        </table>

	</div>

</div>