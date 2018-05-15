<?php

/**
 * Description of WPEAE_AliexpressOrderFulfillmentAjax
 *
 * @author Geometrix
 */
if (!class_exists('WPEAE_AliexpressOrderItem')):

	class WPEAE_AliexpressOrderItem {
		private $orderItem;
		
		function __construct($order_item){
			$this->orderItem  = $order_item;
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

