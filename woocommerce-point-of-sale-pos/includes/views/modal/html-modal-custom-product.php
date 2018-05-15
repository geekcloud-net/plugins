<?php 
$product_types = apply_filters('wc_pos_edit_product_types', array());
?>
<div class="md-modal md-dynamicmodal md-close-by-overlay md-register" id="modal-add_custom_product">
    <div class="md-content">
        <h1><?php _e('Product', 'wc_point_of_sale'); ?><span class="md-close"></span></h1>
        <div class="full-height">
            <div class="box_content">
                <table id="custom_product_table" cellspacing="0" cellpadding="0">
                    <thead>
                        <tr>
                            <th class="product_title">
                                <?php _e('Product Title', 'wc_point_of_sale'); ?>
                            </th>
                            <?php                             
                            if (!empty($product_types)) {
                                ?>
                                <th class="product_type">
                                    <?php _e('Type', 'wc_point_of_sale'); ?>
                                </th>
                                <?php
                            }
                            ?>
                            <th class="product_price">
                                <?php printf(__('Price (%s)', 'wc_point_of_sale'), get_woocommerce_currency_symbol()); ?>
                            </th>
                            <th class="product_quantity">
                                <?php _e('Quantity', 'wc_point_of_sale'); ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="product_title"><input type="text" id="custom_product_title"></td>
                            <?php                             
                            if (!empty($product_types)) {
                                ?>
                                <td class="product_type">
                                    <select id="product_type">
                                        <option value="simple"><?php _e('Simple'); ?></option>
                                        <?php 
                                        foreach ($product_types as $key => $value) {
                                            ?>
                                            <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                </td>
                                <?php
                            }
                            ?>
                            <td class="product_price"><input type="text" id="custom_product_price"></td>
                            <td class="product_quantity"><input type="number" id="custom_product_quantity" value="1"></td>
                        </tr>
                    </tbody>
                </table>

                <?php do_action('wc_pos_modal_add_product_custom_meta', 'custom'); ?>
                <table id="custom_product_meta_table" cellspacing="0" cellpadding="0">
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
            <button class="alignleft " id="add_custom_product_meta"><?php _e('Add Meta', 'wc_point_of_sale'); ?></button>
            <button class="alignright" id="add_custom_product"><?php _e('Add Product', 'wc_point_of_sale'); ?></button>
        </div>
    </div>
</div>