<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_FacetType.php';

class OrderStatusFilterCodeType extends EbatNs_FacetType
{
	const CodeType_All = 'All';
	const CodeType_AwaitingPayment = 'AwaitingPayment';
	const CodeType_AwaitingShipment = 'AwaitingShipment';
	const CodeType_PaidAndShipped = 'PaidAndShipped';
	const CodeType_CustomCode = 'CustomCode';

	/**
	 * @return 
	 **/
	function __construct()
	{
		parent::__construct('OrderStatusFilterCodeType', 'urn:ebay:apis:eBLBaseComponents');
	}
}
$Facet_OrderStatusFilterCodeType = new OrderStatusFilterCodeType();
?>