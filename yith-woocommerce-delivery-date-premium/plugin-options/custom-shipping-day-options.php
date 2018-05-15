<?php
if( !defined('ABSPATH')){
	exit;
}
return apply_filters(
		'yith_wcdd_custom_shipping_day_options',
		array(
				'custom-shipping-day' => array(
						'shippingday' => array(
								'type' => 'custom_tab',
								'action' => 'yith_wcdd_shippingday_panel',
								'hide_sidebar'  => true
						)

				)
		)
		);