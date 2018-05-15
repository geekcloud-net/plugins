<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_FacetType.php';

class ShippingServiceType extends EbatNs_FacetType
{
	const CodeType_Domestic = 'Domestic';
	const CodeType_International = 'International';

	/**
	 * @return 
	 **/
	function __construct()
	{
		parent::__construct('ShippingServiceType', 'urn:ebay:apis:eBLBaseComponents');
	}
}
$Facet_ShippingServiceType = new ShippingServiceType();
?>