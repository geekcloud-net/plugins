<?php
if( !defined('ABSPATH')){
	exit;
}

?>
<tr>
<td class="timefrom column-timefrom has-row-actions column-primary" data-colname="From">
<input type="text" name="_ywcdd_addtimeslot[<?php esc_attr_e( $slot_id );?>][timefrom]" class="yith_timepicker timepicker_timefrom" value="<?php esc_attr_e($from)?>">
<button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button>
</td>
<td class="timeto column-timeto" data-colname="To">
<input type="text" name="_ywcdd_addtimeslot[<?php esc_attr_e( $slot_id );?>][timeto]" class="yith_timepicker timepicker_timeto" value="<?php esc_attr_e($to)?>">
</td>
<td class="max_order column-max_order" data-colname="Lockout after">
<input type="number" name="_ywcdd_addtimeslot[<?php esc_attr_e( $slot_id );?>][max_order]" min="0" class="yith_max_order" step="any" value="<?php esc_attr_e($max_order)?>"></td>
<td class="fee column-fee" data-colname="Fee (�)">
<input type="number" name="_ywcdd_addtimeslot[<?php esc_attr_e( $slot_id );?>][fee]" min="0" class="yith_fee" step="any" value="<?php esc_attr_e($fee );?>">
</td>
<td class="override_days column-override_days" data-colname="Workdays">

<?php 
$column = sprintf('<input type="checkbox" class="yith_override_day" value="yes" %s /><span class="description">%s</span>', checked('yes', 'no', false ), __('Override workdays','yith-woocommerce-delivery-date') );
$column .= sprintf('<input type="hidden"  class="yith_over_day" name="%1$s[%2$s][%3$s]" value="%4$s" />','_ywcdd_addtimeslot',$slot_id ,'override_days', ''  );

$days = yith_get_worksday();

$div_workdays = '<div class="yith_single_multiworkday">';
$div_workdays.=     sprintf('<select multiple="multiple" name="%s[%s][%s][]" class="wc-enhanced-select yith_dayworkselect">','_ywcdd_addtimeslot', $slot_id, 'day_selected' );
foreach( $days as $key_day => $day ){
	$div_workdays.= sprintf('<option value="%s">%s</option>', $key_day,  $day );
}
$div_workdays.='</select>';
$div_workdays .= sprintf('<a href="" class="yith_timeslot_all_day">%s</a>',__('Select all','yith-woocommerce-delivery-date' ) );
$div_workdays .= sprintf('<a href="" class="yith_timeslot_clear">%s</a>',__('Clear','yith-woocommerce-delivery-date' ) );
$div_workdays.= '</div>';

$column.=$div_workdays;

echo $column;
?>
</td>
<td>
<?php 
$column = '';
$column .= sprintf( '<a href="#" class="button button-secondary yith_update_time_slot" data-item_id="%s">%s</a>',$slot_id, __( 'Update', 'yith-woocommerce-delivery-date' ) );
$column .= ' ';
$column .= sprintf( '<a href="#" class="button button-secondary yith_delete_time_slot" data-item_id="%s">%s</a>',$slot_id, __( 'Delete', 'yith-woocommerce-delivery-date' ) );
echo $column;
?>
</td>