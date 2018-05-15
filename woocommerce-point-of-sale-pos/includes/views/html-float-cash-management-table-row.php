<tr>
    <td>
        <?php echo date_i18n(__('jS F Y', 'woocommerce'), strtotime($row['time'])) . "\n"; ?>
        <?php _e('at', 'wc_point_of_sale') ?>
        <?php echo date_i18n(__('g:i:s A', 'woocommerce'), strtotime($row['time'])) . "\n"; ?>
    </td>
    <td>
        <?php echo ($author) ? $author->first_name . ' ' . $author->last_name : '' ?>
    </td>
    <td>
        <?php echo $row['title'] ?>
        <?php echo ($row['note']) ? '</br><small class="meta">' . $row['note'] . '</small>' : '' ?>
    </td>
    <td class="<?php echo $row['type'] ?>">
        <?php echo wc_price($row['amount']) ?>
    </td>
</tr>
