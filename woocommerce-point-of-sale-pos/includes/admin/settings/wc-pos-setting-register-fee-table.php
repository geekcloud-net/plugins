<!--
<div id="custom-fees">
    <h3><?php _e('Custom fee', 'wc_point_of_sale') ?></h3>
    <table id="custom-fee">
        <thead>
        <tr>
            <th>
                <?php _e('Fee label', 'wc_point_of_sale') ?>
            </th>
            <th>
                <?php _e('Type', 'wc_point_of_sale') ?>
            </th>
            <th>
                <?php _e('Value', 'wc_point_of_sale') ?>
            </th>
            <th>
                <?php _e('Taxable', 'wc_point_of_sale') ?>
            </th>
            <th>
                <?php _e('Actions', 'wc_point_of_sale') ?>
            </th>
        </tr>
        </thead>
        <tbody>
        <?php if ($custom_fees) { ?>
            <?php foreach ($custom_fees as $key => $fee) { ?>
                <tr data-id="<?php echo $key ?>">
                    <td>
                        <input type="text" name="wc_pos_custom_fees[<?php echo $key ?>][name]" required
                               value="<?php echo $fee['name'] ?>">
                    </td>
                    <td>
                        <select name="wc_pos_custom_fees[<?php echo $key ?>][type]" required>
                            <option value="percent" <?php echo ($fee['type'] == 'percent') ? 'selected' : '' ?>>%
                            </option>
                            <option value="fixed" <?php echo ($fee['type'] == 'fixed') ? 'selected' : '' ?>><?php echo get_woocommerce_currency_symbol() ?></option>
                        </select>
                    </td>
                    <td>
                        <input type="text" name="wc_pos_custom_fees[<?php echo $key ?>][value]"
                               value="<?php echo $fee['value'] ?>" required>
                    </td>
                    <td>
                        <select name="wc_pos_custom_fees[<?php echo $key ?>][taxable]" required>
                            <option value="yes" <?php echo ($fee['taxable'] == 'yes') ? 'selected' : '' ?>> <?php _e('Yes', 'wc_point_of_sale') ?></option>
                            <option value="no" <?php echo ($fee['taxable'] == 'no') ? 'selected' : '' ?>> <?php _e('No', 'wc_point_of_sale') ?></option>
                        </select>
                    </td>
                    <td>
                        <a class="button add"
                           data-id="<?php echo $key ?>"><?php _e('Add new', 'wc_point_of_sale') ?></a>
                        <a class="button remove"
                           data-id="<?php echo $key ?>"><?php _e('Remove', 'wc_point_of_sale') ?></a>
                    </td>
                </tr>
            <?php } ?>
        <?php } else { ?>
            <tr data-id="0">
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>
                    <a class="button add" data-id="0"><?php _e('Add new', 'wc_point_of_sale') ?></a>
                    <a class="button remove" data-id="0"><?php _e('Remove', 'wc_point_of_sale') ?></a>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
<script type="text/template" id="tmpl-fee-row">
    <tr data-id="{{id}}">
        <td>
            <input type="text" name="wc_pos_custom_fees[{{id}}][name]" required
                   value="">
        </td>
        <td>
            <select name="wc_pos_custom_fees[{{id}}][type]" required>
                <option value="percent">%</option>
                <option value="fixed"><?php echo get_woocommerce_currency_symbol() ?></option>
            </select>
        </td>
        <td>
            <input type="text" name="wc_pos_custom_fees[{{id}}][value]"
                   value="" required>
        </td>
        <td>
            <select name="wc_pos_custom_fees[{{id}}][taxable]" required>
                <option value="yes"> <?php _e('Yes', 'wc_point_of_sale') ?></option>
                <option value="no"> <?php _e('No', 'wc_point_of_sale') ?></option>
            </select>
        </td>
        <td>
            <a class="button add" data-id="{{id}}"><?php _e('Add new', 'wc_point_of_sale') ?></a>
            <a class="button remove" data-id="{{id}}"><?php _e('Remove', 'wc_point_of_sale') ?></a>
        </td>
    </tr>
</script>
-->
