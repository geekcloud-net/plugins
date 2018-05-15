<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_FacetType.php';

class PricingTreatmentCodeType extends EbatNs_FacetType
{
	const CodeType_STP = 'STP';
	const CodeType_MAP = 'MAP';
	const CodeType_None = 'None';
	const CodeType_MFO = 'MFO';
	const CodeType_CustomCode = 'CustomCode';

	/**
	 * @return 
	 **/
	function __construct()
	{
		parent::__construct('PricingTreatmentCodeType', 'urn:ebay:apis:eBLBaseComponents');
	}
}
$Facet_PricingTreatmentCodeType = new PricingTreatmentCodeType();
?>