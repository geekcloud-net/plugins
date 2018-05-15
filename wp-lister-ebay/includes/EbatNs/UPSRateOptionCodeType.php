<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_FacetType.php';

class UPSRateOptionCodeType extends EbatNs_FacetType
{
	const CodeType_UPSDailyRates = 'UPSDailyRates';
	const CodeType_UPSOnDemandRates = 'UPSOnDemandRates';
	const CodeType_CustomCode = 'CustomCode';

	/**
	 * @return 
	 **/
	function __construct()
	{
		parent::__construct('UPSRateOptionCodeType', 'urn:ebay:apis:eBLBaseComponents');
	}
}
$Facet_UPSRateOptionCodeType = new UPSRateOptionCodeType();
?>