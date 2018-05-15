<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_FacetType.php';

class LogisticsPlanCodeType extends EbatNs_FacetType
{
	const CodeType_PickUpDropOff = 'PickUpDropOff';
	const CodeType_DigitalDelivery = 'DigitalDelivery';
	const CodeType_CustomCode = 'CustomCode';

	/**
	 * @return 
	 **/
	function __construct()
	{
		parent::__construct('LogisticsPlanCodeType', 'urn:ebay:apis:eBLBaseComponents');
	}
}
$Facet_LogisticsPlanCodeType = new LogisticsPlanCodeType();
?>