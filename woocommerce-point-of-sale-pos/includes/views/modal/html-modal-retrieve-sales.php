<div class="md-modal full-width md-dynamicmodal md-close-by-overlay" id="modal-retrieve_sales">
    <div class="md-content">
        <h1><?php _e('Retrieve Sales', 'wc_point_of_sale'); ?> - <i><?php _e('All', 'wc_point_of_sale'); ?></i> <span class="md-close"></span></h1>
        <div class="full-height" id="retrieve-sales-wrapper">
            <div class="box_content">
                <div class="tablenav_wrap tablenav_wrap_top">
                    <div class="tablenav top">
                        <div class="alignleft actions bulkactions">
                            <?php $reg_ = WC_POS()->register()->get_data_names(); ?>
                            <select id="bulk-action-retrieve_sales">
                                <option value="all" data-name="<?php _e('All', 'wc_point_of_sale'); ?>"><?php _e('All', 'wc_point_of_sale'); ?></option>
                                <option value="<?php echo $data['ID']; ?>" data-name="<?php echo $data['name']; ?>"><?php _e('This Register', 'wc_point_of_sale'); ?></option>
                                <?php if(!empty($reg_)){
                                    foreach ($reg_ as $reg_id => $reg_name) {
                                        if ($reg_id == $data['ID']) continue;
                                        echo '<option value="'.$reg_id.'" data-name="'.$reg_name.'">'.$reg_name.'</option>';
                                    }
                                } ?>
                            </select>
                            <input type="button" value="<?php _e('Load', 'wc_point_of_sale'); ?>" class="button action" id="btn_retrieve_from">

                        </div>
                        <p class="search-box">
                            <label for="post-search-input" class="screen-reader-text"><?php _e('Search Orders', 'wc_point_of_sale'); ?>:</label>
                            <input type="search" value="" id="orders-search-input">
                            <input type="button" value="<?php _e('Search Orders', 'wc_point_of_sale'); ?>" class="button" id="orders-search-submit">
                        </p>
                    </div>
                    <table class="wp-list-table widefat fixed retrieve_sales_nav">
                        <thead>
                            <tr>
                                <th class="manage-column column-order_status" scope="col">
                                    <span data-tip="<?php _e('Status', 'woocommerce'); ?>" class="status_head tips"><?php _e('Status', 'woocommerce'); ?></span>
                                </th>
                                <th class="manage-column column-order_title" scope="col"><?php _e('Order', 'woocommerce'); ?></th>
                                <th class="manage-column column-order_items" scope="col"><?php _e('Purchased', 'woocommerce'); ?></th>
                                <th class="manage-column column-shipping_address" scope="col"><?php _e('Ship to', 'woocommerce'); ?></th>
                                <th class="manage-column column-customer_message" scope="col">
                                    <span data-tip="<?php _e('Customer Message', 'woocommerce'); ?>" class="notes_head tips"><?php _e('Customer Message', 'woocommerce'); ?></span>
                                </th>
                                <th class="manage-column column-order_notes" scope="col">
                                    <span class="order-notes_head tips" data-tip="<?php _e('Order Notes', 'woocommerce'); ?>"><?php _e('Order Notes', 'woocommerce'); ?></span>
                                </th>
                                <th class="manage-column column-order_date" scope="col"><?php _e('Date', 'woocommerce'); ?></th>
                                <th class="manage-column column-order_total" scope="col"><?php _e('Total', 'woocommerce'); ?></th>
                                <th class="manage-column column-order_actions" scope="col"><?php _e('Actions', 'woocommerce'); ?></th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <div id="retrieve_sales_popup_inner">
                </div>
                <div class="tablenav_wrap tablenav_wrap_bottom">
                    <table class="wp-list-table widefat fixed retrieve_sales_nav">
                        <tfoot>
                            <tr>
                                <th class="manage-column column-order_status" scope="col">
                                    <span data-tip="<?php _e('Status', 'woocommerce'); ?>" class="status_head tips"><?php _e('Status', 'woocommerce'); ?></span>
                                </th>
                                <th class="manage-column column-order_title" scope="col"><?php _e('Order', 'woocommerce'); ?></th>
                                <th class="manage-column column-order_items" scope="col"><?php _e('Purchased', 'woocommerce'); ?></th>
                                <th class="manage-column column-shipping_address" scope="col"><?php _e('Ship to', 'woocommerce'); ?></th>
                                <th class="manage-column column-customer_message" scope="col">
                                    <span data-tip="<?php _e('Customer Message', 'woocommerce'); ?>" class="notes_head tips"><?php _e('Customer Message', 'woocommerce'); ?></span>
                                </th>
                                <th class="manage-column column-order_notes" scope="col">
                                    <span class="order-notes_head tips" data-tip="<?php _e('Order Notes', 'woocommerce'); ?>"><?php _e('Order Notes', 'woocommerce'); ?></span>
                                </th>
                                <th class="manage-column column-order_date" scope="col"><?php _e('Date', 'woocommerce'); ?></th>
                                <th class="manage-column column-order_total" scope="col"><?php _e('Total', 'woocommerce'); ?></th>
                                <th class="manage-column column-order_actions" id="order_actions" scope="col"><?php _e('Actions', 'woocommerce'); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <div class="wrap-button"></div>
    </div>
</div>