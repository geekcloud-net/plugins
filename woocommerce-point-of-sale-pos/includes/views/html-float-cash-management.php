<?php if ($this->register->opened > $this->register->closed) { ?>
    <script>
        <?php echo 'var register_cash_management = ' . json_encode($this) ?>
    </script>
    <div class="wrap">
        <h1 class="wp-heading-inline"><?php _e('Cash Management', 'wc_point_of_sale') ?></h1>
        <a class="page-title-action cash-button remove-cash"
           data-action="remove-cash"><?php _e('Remove Cash', 'wc_point_of_sale') ?></a>
        <a class="page-title-action cash-button add-cash"
           data-action="add-cash"><?php _e('Add Cash', 'wc_point_of_sale') ?></a>
        <p><?php echo __('Register: #', 'wc_point_of_sale') . $this->register->ID . ' - ' . $this->register->name ?></p>
        <p><?php echo __('Outlet:', 'wc_point_of_sale') . ' ' . $this->outlet_name[1] ?></p>
        <p id="cash-total"><?php echo __('This is used to record your cash movement for the day. Current cash balance (including sales): ', 'wc_point_of_sale') ?><b><?php echo wc_price($this->cash_balance) ?></b></p>
        <table class="wp-list-table widefat fixed striped posts cash-data" style="width: 100%;">
            <thead>
            <tr>
                <th class="time"><?php _e('Time', 'wc_point_of_sale') ?></th>
                <th class="user"><?php _e('User', 'wc_point_of_sale') ?></th>
                <th class="reasons"><?php _e('Reason', 'wc_point_of_sale') ?></th>
                <th class="transaction"><?php _e('Transaction', 'wc_point_of_sale') ?></th>
            </tr>
            </thead>
            <tbody id="the-list">
            <?php foreach ($this->cash_data as $row) { ?>
                <?php $author = get_user_by('id', $row['user']) ?>
                <?php include('html-float-cash-management-table-row.php') ?>
            <?php } ?>
            </tbody>
            <tfoot>
            <tr>
                <th class="time"><?php _e('Time', 'wc_point_of_sale') ?></th>
                <th class="user"><?php _e('User', 'wc_point_of_sale') ?></th>
                <th class="reasons"><?php _e('Reasons', 'wc_point_of_sale') ?></th>
                <th class="transaction"><?php _e('Transaction', 'wc_point_of_sale') ?></th>
            </tr>
            </tfoot>
        </table>
    </div>
<?php } else { ?>
    <h2 class="closed-register"><?php _e('Register is closed.') ?></h2>
<?php } ?>
