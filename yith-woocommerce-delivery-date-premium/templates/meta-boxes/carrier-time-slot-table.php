<?php
if( !defined('ABSPATH')){
    exit;
}
if( !class_exists('YITH_WCDD_Carrier_Time_Slot_Table')){
    require_once( YITH_DELIVERY_DATE_INC.'admin-tables/class.yith-wcdd-carrier-time-slot-table.php');

}

$carrier_table = new YITH_WCDD_Carrier_Time_Slot_Table( $post_id, $metakey );
$carrier_table->prepare_items();
$carrier_table->display();
