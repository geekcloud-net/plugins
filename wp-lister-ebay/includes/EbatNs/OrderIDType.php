<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_FacetType.php';

class OrderIDType extends EbatNs_FacetType
{

	/**
	 * @return 
	 **/
	function __construct()
	{
		parent::__construct('OrderIDType', 'urn:ebay:apis:eBLBaseComponents');
	}
}
$Facet_OrderIDType = new OrderIDType();
?>