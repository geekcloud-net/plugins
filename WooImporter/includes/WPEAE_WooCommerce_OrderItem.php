<?php

/**
 * Description of WPEAE_AliexpressOrderFulfillmentAjax
 *
 * @author Geometrix
 */
if (!class_exists('WPEAE_WooCommerce_OrderItem')):

	class WPEAE_WooCommerce_OrderItem {
		private $orderItem;
		
		function __construct($order_item){
			$this->orderItem  = $order_item;
		}
		
		public function getName(){
			if (is_array($this->orderItem)) return $this->orderItem['name'];
			if (get_class($this->orderItem) == 'WC_Order_Item_Product') return $this->orderItem->get_name();  
		}
		
		public function getProductID(){
			if (is_array($this->orderItem)) return $this->orderItem['product_id'];
			if (get_class($this->orderItem) == 'WC_Order_Item_Product') return $this->orderItem->get_product_id();   
		}
		
		public function getVariationID(){
			if (is_array($this->orderItem)) return $this->orderItem['variation_id'];
			if (get_class($this->orderItem) == 'WC_Order_Item_Product') return $this->orderItem->get_variation_id();   
		}
		
		public function getQuantity(){
			if (is_array($this->orderItem)) return $this->orderItem['qty'];
			if (get_class($this->orderItem) == 'WC_Order_Item_Product') return $this->orderItem->get_quantity();     
		}

		
	}


endif;

