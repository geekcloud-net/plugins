<?php 
if ($user_to_add > 0) {
    ?>
    <tr data-customer_id="<?php echo $user_to_add; ?>" class="item <?php if (!empty($class)) echo $class; ?>">
        <td class="avatar">
            <?php echo get_avatar( $user_to_add, 64); ?>
        </td>
        <td class="name">
            <?php if (!$user_to_add) { ?>
                <?php echo $username; ?>
            <?php } else { ?>
                <a href="#" class="customer-loaded-name show_customer_popup" ><?php echo $user_data['first_name'] . ' ' . $user_data['last_name']; ?></a>
                <br>
                <a href="#" class="customer-loaded-email show_customer_popup" ><?php echo $user_data['email']; ?></a>
            <?php } ?>
            <input type="hidden" id="pos_c_user_id" name="user_id" value="<?php echo esc_attr($user_to_add); ?>" />
            <input type="hidden" id="pos_c_user_data" value='<?php echo esc_attr(json_encode($user_data)); ?>' />
            <input type="hidden" id="pos_c_billing_addr" value='<?php echo esc_attr(json_encode($b_addr)); ?>' />
            <input type="hidden" id="pos_c_shipping_addr" value='<?php echo esc_attr(json_encode($s_addr)); ?>' />
        </td>
        <?php if( isset( $GLOBALS['wc_points_rewards'] ) ){
	        global $wc_points_rewards;
	        $points_label = $wc_points_rewards->get_points_label(2);
            $points_balance = WC_Points_Rewards_Manager::get_users_points( $user_to_add );
            
            ?>
            <td class="customer_points" ><span class="customer_points_label"><b><?php echo $points_balance; ?></b> <?php echo $points_label; ?></span></td>
        <?php } ?>
        
        <td class="remove_customer">
            <a href="#" class="remove_customer_row tips" data-tip="<?php _e('Remove', 'wc_point_of_sale'); ?>"></a>
        </td>
    </tr>
    <?php } else {
    ?>
    <!-- For place Order from Guest Account -->
    <tr data-customer_id="<?php echo $user_to_add; ?>" class="item <?php if (!empty($class)) echo $class; ?>">
        <td class="avatar">
            <?php echo get_avatar( $user_to_add, 64); ?>
        </td>
        <td class="name" ><?php _e('Guest', 'wc_point_of_sale'); ?></td>
        <?php if( isset( $GLOBALS['wc_points_rewards'] ) ){
            ?>
            <td class="customer_points" ></td>
        <?php } ?>
        <td class="remove_customer">
            <a href="#" class="remove_customer_row tips" data-tip="<?php _e('Remove', 'wc_point_of_sale'); ?>"></a>
        </td>
    </tr> 
<?php }
?>