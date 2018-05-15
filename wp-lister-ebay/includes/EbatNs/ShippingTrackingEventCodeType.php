<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_FacetType.php';

class ShippingTrackingEventCodeType extends EbatNs_FacetType
{
	const CodeType_ValetReadyForPickup = 'ValetReadyForPickup';
	const CodeType_ValetPickedUpOrder = 'ValetPickedUpOrder';
	const CodeType_CustomCode = 'CustomCode';

	/**
	 * @return 
	 **/
	function __construct()
	{
		parent::__construct('ShippingTrackingEventCodeType', 'urn:ebay:apis:eBLBaseComponents');
	}
}
$Facet_ShippingTrackingEventCodeType = new ShippingTrackingEventCodeType();
?>