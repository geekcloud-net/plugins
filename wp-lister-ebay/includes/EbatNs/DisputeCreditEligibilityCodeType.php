<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_FacetType.php';

class DisputeCreditEligibilityCodeType extends EbatNs_FacetType
{
	const CodeType_InEligible = 'InEligible';
	const CodeType_Eligible = 'Eligible';
	const CodeType_CustomCode = 'CustomCode';

	/**
	 * @return 
	 **/
	function __construct()
	{
		parent::__construct('DisputeCreditEligibilityCodeType', 'urn:ebay:apis:eBLBaseComponents');
	}
}
$Facet_DisputeCreditEligibilityCodeType = new DisputeCreditEligibilityCodeType();
?>