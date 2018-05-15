  <tr class="item <?php if ( ! empty( $class ) ) echo $class; ?>" data-order_item_id="<?php echo $item_id; ?>">
    <td class="name">
      <a href="#" class="remove_order_item button tips" data-tip="<?php _e( 'Remove', 'wc_point_of_sale' ); ?>"></a>
      <a href="#" class="button add_custom_meta tips" data-tip="<?php _e( 'Edit Product', 'wc_point_of_sale' ); ?>"></a>
      <?php if ($_product):
        $tip = '<strong>' . __( 'Product ID:', 'woocommerce' ) . '</strong> ' . absint( $item['product_id'] );

        if ( $item['variation_id'] )
          $tip .= '<br/><strong>' . __( 'Variation ID:', 'woocommerce' ) . '</strong> ' . absint( $item['variation_id'] );

        if ( $_product && $_product->get_sku() )
          $tip .= '<br/><strong>' . __( 'Product SKU:', 'woocommerce' ).'</strong> ' . esc_html( $_product->get_sku() );

        if ( $_product && isset( $_product->variation_data ) )
          $tip .= '<br/>' . wc_get_formatted_variation( $_product->variation_data, true );
       ?>
      <?php echo $_product->get_image( 'shop_thumbnail', array( 'title' => '', 'class' => 'tips', 'data-tip' => $tip ) ); ?>
    <?php else: ?>
      <img src="<?php echo wc_placeholder_img_src(); ?>" class="tips" data-tip="<?php _e('Custom product'); ?>" >
    <?php endif; ?>	 
      <span class="product_name_regiser">
        <?php if ( $_product && $_product->get_sku() ) echo esc_html( $_product->get_sku() ) . ' &ndash; '; ?>
        <?php if ( $_product ) : ?>
          <a target="_blank" href="<?php echo esc_url( admin_url( 'post.php?post='. absint( $_product->id ) .'&action=edit' ) ); ?>">
            <?php echo esc_html( $item['name'] ); ?>
          </a>
        <?php else : ?>
          <?php echo esc_html( $item['name'] ); ?>
          <input type="hidden" value="true" name="custom_product_id[<?php echo esc_attr( $_product_id_var ); ?>]">
          <input type="hidden" value="<?php echo esc_html( $item['name'] ); ?>" name="custom_product_name[<?php echo esc_attr( $_product_id_var ); ?>]">
        <?php endif; ?>
    </span>
    <br>
    <?php if ( $_product && $stock_qty = $_product->get_stock_quantity() ) : ?>
      <span><b><?php echo $stock_qty ; ?></b> in stock </span>
    <?php endif; ?>
    <input type="hidden" class="product_custom_meta" value="true" name="product_custom_meta[<?php echo absint( $_product_id_var ); ?>]">
    <input type="hidden" class="product_item_id" name="product_item_id[]" value="<?php echo esc_attr( $_product_id_var ); ?>" />
    <input type="hidden" class="product_parent_id" value="<?php echo esc_attr( $_product_id ); ?>" />

    <div class="view">
      <ul class="display_meta">
      <?php
        if ( $metadata = $order->has_meta( $item_id ) ) {
          foreach ( $metadata as $meta ) {

            // Skip hidden core fields
            if ( in_array( $meta['meta_key'], apply_filters( 'woocommerce_hidden_order_itemmeta', array(
              '_qty',
              '_tax_class',
              '_product_id',
              '_variation_id',
              '_line_subtotal',
              '_line_subtotal_tax',
              '_line_total',
              '_line_tax',
            ) ) ) ) {
              continue;
            }

            // Skip serialised meta
            if ( is_serialized( $meta['meta_value'] ) ) {
              continue;
            }
            $meta_input = '';
            if( strpos($meta['meta_key'], 'pa_') === 0 )
              $meta['meta_key'] = substr($meta['meta_key'], 3);
            
            $meta_key = wp_kses_post( urldecode( ucwords($meta['meta_key']) ) );
            $meta_val = wp_kses_post( urldecode( ucwords($meta['meta_value']) ) );

            $meta_input = '<input type="hidden"  class="pos_pr_variations"  data-attrlabel="' . $meta_key . '" value="' . $meta_val . '" name="variations[' . $_product_id_var . '][' . $meta_key . ']">';

            echo '<li class="pos_pr_variations_li"><span class="meta_label">' . $meta_key . '</span><span class="meta_value">' . $meta_val . $meta_input . '</span></li>';
          }
        }
      ?>
      </ul>
    </div>
    </td>
	<td class="quantity">
      <div class="edit">
      <input type="text" min="0" autocomplete="off" name="order_item_qty[<?php echo $_product_id_var; ?>]" placeholder="0" value="<?php echo esc_attr( $item['qty'] ); ?>" class="quantity" />
    </div>
    </td>
    <td class="line_cost"> 
      <div class="view">
        <?php
        $is_taxable = 'true';
        $taxclass   = '';
        if ($_product)
          $taxclass   = $_product->get_tax_class();
         if ($_product && !$_product->is_taxable()){
          $is_taxable = 'false';
          }?>
        <input type="text" class="product_price" placeholder="<?php echo $item_price ; ?>" value="<?php echo $item_price ; ?>" name="order_item_price[<?php echo esc_attr( $_product_id_var ); ?>]" data-original="<?php echo $item_price ; ?>" data-istaxable="<?php echo $is_taxable; ?>'" data-taxclass="<?php echo $taxclass; ?>'" data-discountsymbol="currency_symbol" data-percent="0" data-modprice="<?php echo $item_price ; ?>" >
        <input type="hidden" value="0" class="pr_coupon_discount" />
        <input type="hidden" value="<?php echo $item_price ; ?>" class="final_total_amount" />
      </div>
    </td>
    <td class="line_cost_total">
      <div class="view">
        <span class="amount"><?php echo wc_price( $item['line_total'] ); ?></span>
      </div>
    </td>
  </tr>