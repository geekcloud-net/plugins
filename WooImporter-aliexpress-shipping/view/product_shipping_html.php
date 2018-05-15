<?php
$wpeae_shipping_html = '<div id="wpeae_to_country">' .
	woocommerce_form_field('wpeae_to_country_field', array(
		   'type'       => 'select',
		   'class'      => array( 'chzn-drop' ),
		   'label'      => __('Ship To Country','wpeae-ali-ship'),
		   'placeholder'    => __('Select a Country','wpeae-ali-ship'),
		   'options'    => $countries,
		   'default' => $this->get_order_country(),
		   'return' => true
			)
	 ) .
'</div>' .
'<div id="wpeae_shipping" style="display: none;">' .
	 woocommerce_form_field('wpeae_shipping_field', array(
			'type'       => 'select',
			'class'      => array( 'chzn-drop' ),
			'label'      => __('Shipping method','wpeae-ali-ship'),
			'placeholder'    => __('Select a Shipping method','wpeae-ali-ship'),
            'options'    => array(''=>__('Select a Shipping method','wpeae-ali-ship')),
			'return' => true
			 )
	  ) .
'</div>';
$wpeae_shipping_html = str_replace(array("\r", "\n"), '', $wpeae_shipping_html);
?>
<div class="wpeae_shipping">
</div>
<script>
jQuery(document).ready(function($){
   var v = '<?php echo addslashes($wpeae_shipping_html); ?>';
   window.wpeae_shipping_api.init(v); 
});
</script>
