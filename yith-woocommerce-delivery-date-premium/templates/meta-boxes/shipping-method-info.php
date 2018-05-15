<?php
if( !defined('ABSPATH')){
	exit;
}

global $post;

if( version_compare(WC()->version, '2.6.0','>=') ){
	$shipping_zones = WC_Shipping_Zones::get_zones();
	$global_zone = new WC_Shipping_Zone( 0 );
	
	$shipping_method_av = array();
	
	$url = admin_url('admin.php');
	foreach( $shipping_zones as $zone ){
		
		$shipping_methods = $zone['shipping_methods'];
		$single_zone = array();
		foreach( $shipping_methods as $method ){
			
			if( isset( $method->instance_settings['select_process_method'] ) && $post->ID == $method->instance_settings['select_process_method'] ){
				
				$params = array( 'page'=> 'wc-settings','tab' => 'shipping', 'instance_id' => $method->instance_id );
				$edit_link = esc_url( add_query_arg( $params, $url ) );
				
				$shipping_method = array(
						'name' => $method->title,
						'edit_link' => $edit_link,
						);
				$single_zone[]= $shipping_method;
			}
		}
		
		if( count( $single_zone ) >0 ){
			$zone_name = $zone['zone_name'];
			$zone_id = $zone['zone_id'];
			
			$shipping_method_av[$zone_id] = array('zone_name' => $zone_name, 'shipping_method' => $single_zone );
			
		}
	}
	
	$global_method = $global_zone->get_shipping_methods();
	$single_zone = array();
	foreach( $global_method as $method ){
			
		if( isset( $method->instance_settings['select_process_method'] ) && $post->ID == $method->instance_settings['select_process_method'] ){
	
			$params = array( 'page'=> 'wc-settings','tab' => 'shipping', 'instance_id' => $method->instance_id );
			$edit_link = esc_url( add_query_arg( $params, $url ) );
	
			$shipping_method = array(
					'name' => $method->title,
					'edit_link' => $edit_link,
			);
			$single_zone[]= $shipping_method;
		}
	}
	if( count( $single_zone ) >0 ){
		$zone_name = $global_zone->get_zone_name();
		$zone_id =  version_compare( WC()->version, '3.0.0','>=' ) ? $global_zone->get_id() : $global_zone->get_zone_id();
			
		$shipping_method_av[$zone_id] = array('zone_name' => $zone_name, 'shipping_method' => $single_zone );
			
	}
	
	echo '<div class="ywcdd_shipping_info_content">';
	if( count( $shipping_method_av ) > 0 ){
		$message = __('This processing method is set in WooCommerce Shipping Method:','yith-woocommerce-delivery-date');
	}else{
		$message = __('This method is not set in any WooCommerce Shipping Method','yith-woocommerce-delivery-date' );
	}
	
	echo sprintf('<h4>%s</h4>', $message );
	foreach( $shipping_method_av as $key => $method_available ):?>
		
		   <div class="ywcdd_shipping_zone_list">
		   		<span><strong><?php echo $method_available['zone_name']?></strong></span>
		   		<ul class="ywcdd_method_list">
		   		 <?php 
		   		 foreach( $method_available['shipping_method'] as $ship_method ){
		   		 	$li = sprintf('<li><a href="%s" target="_blank" title="%s">%s</a></li>', $ship_method['edit_link'], __('Edit settings', 'yith-woocommerce-delivery-date'),$ship_method['name'] );
		   		 	echo $li;
		   		 }
		   		 	?>	
		   		</ul>
		   </div>
	
	<?php endforeach;
	echo '<div class="ywcdd_manage_zone">';
	$url = admin_url('admin.php');
	$params = array( 'page'=> 'wc-settings','tab' => 'shipping' );
	$zone_url = esc_url( add_query_arg($params, $url ) );
	echo sprintf('<a href="%s" target="_blank" class="button button-primary button-small">%s</a>',$zone_url, __('Manage Shipping Zones','yith-woocommerce-delivery-date') );
	echo '</div></div>'	;
	
}else{

	WC()->shipping->load_shipping_methods();
	$shipping_methods = WC()->shipping()->get_shipping_methods();
	$single_zone = array();
	$url = admin_url('admin.php');
	foreach( $shipping_methods as $method ){

		if( isset( $method->settings['select_process_method'] ) && $post->ID == $method->settings['select_process_method'] ){

			$params = array( 'page'=> 'wc-settings','tab' => 'shipping', 'section' => 'wc_shipping_'.$method->id );
			$edit_link = esc_url( add_query_arg( $params, $url ) );

			$shipping_method = array(
				'name' => $method->title,
				'edit_link' => $edit_link,
			);
			$single_zone[]= $shipping_method;
		}
	}

	echo '<div class="ywcdd_shipping_info_content">';
	if( count( $single_zone ) > 0 ){
		$message = __('This processing method is set in WooCommerce Shipping Method:','yith-woocommerce-delivery-date');
	}else{
		$message = __('This method is not set in any WooCommerce Shipping Method','yith-woocommerce-delivery-date' );
	}

	echo sprintf('<h4>%s</h4>', $message );
	echo '<div class="ywcdd_shipping_zone_list">';
	echo '<ul class="ywcdd_method_list">';
	foreach( $single_zone as $key => $method_available ):
		$li = sprintf('<li><a href="%s" target="_blank" title="%s">%s</a></li>', $method_available['edit_link'], __('Edit settings', 'yith-woocommerce-delivery-date'),$method_available['name'] );
		echo $li;


	endforeach;
	echo '</ul></div><div class="ywcdd_manage_zone">';
	$url = admin_url('admin.php');
	$params = array( 'page'=> 'wc-settings','tab' => 'shipping' );
	$zone_url = esc_url( add_query_arg($params, $url ) );
	echo sprintf('<a href="%s" target="_blank" class="button button-primary button-small">%s</a>',$zone_url, __('Manage Shipping Methods','yith-woocommerce-delivery-date') );
	echo '</div></div>'	;




}