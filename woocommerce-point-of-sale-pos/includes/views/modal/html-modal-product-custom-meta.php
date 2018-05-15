<div class="md-modal md-dynamicmodal md-close-by-overlay md-register" id="modal-add_product_custom_meta">
    <div class="md-content">
        <h1><?php _e('Edit Product', 'wc_point_of_sale'); ?><span class="md-close"></span></h1>
        <div>
            <div class="box_content">
                <input type="hidden" id="add_custom_meta_product_id">
                <table id="product_custom_title" cellspacing="0" cellpadding="0">
                    <thead>
                        <tr>
                            <th class="product_title">
                                <?php _e('Product Title', 'wc_point_of_sale'); ?>
                            </th>
                            <th class="product_is_taxable">
                                <?php _e('Taxable', 'wc_point_of_sale'); ?>
                            </th>
                            <th class="product_tax_class">
                                <?php _e('Tax Class', 'wc_point_of_sale'); ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="product_title"><input type="text" id="product_new_custom_title"></td>
                            <td class="product_is_taxable"><input type="checkbox" id="product_new_is_taxable"><label for="product_new_is_taxable" class="pos_register_toggle"></label></td>
                            <td class="product_tax_class">
                            <?php
                                $tax_classes         = WC_Tax::get_tax_classes();
                                $classes_options     = array();
                                $classes_options[''] = __( 'Standard', 'woocommerce' );

                                if ( ! empty( $tax_classes ) ) {
                                    foreach ( $tax_classes as $class ) {
                                        $classes_options[ sanitize_title( $class ) ] = esc_html( $class );
                                    }
                                }
                            ?>
                                <select id="product_new_tax_class">
                                    <?php foreach ( $classes_options as $key => $value) {
                                        echo "<option value='" . $key . "'>".$value."</option>";
                                    }?>
                                </select>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <?php do_action('wc_pos_modal_add_product_custom_meta', 'edit'); ?>

                <table id="product_custom_meta_table" cellspacing="0" cellpadding="0">
                    <thead>
                        <tr>
                            <th class="meta_label">
                                <?php _e('Product Attribute', 'wc_point_of_sale'); ?>
                            </th>
                            <th class="meta_attribute">
                                <?php _e('Meta Value', 'wc_point_of_sale'); ?>
                            </th>
                            <th class="remove_meta"></th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
        <div class="wrap-button">
            <button class="alignleft" id="add_product_custom_meta"><?php _e('Add Meta', 'wc_point_of_sale'); ?></button>
            <button class="alignright" id="save_product_custom_meta"><?php _e('Update Product', 'wc_point_of_sale'); ?></button>
        </div>
    </div>
</div>