<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_FacetType.php';

class CombinedPaymentOptionCodeType extends EbatNs_FacetType
{
	const CodeType_NoCombinedPayment = 'NoCombinedPayment';
	const CodeType_DiscountSpecified = 'DiscountSpecified';
	const CodeType_SpecifyDiscountLater = 'SpecifyDiscountLater';
	const CodeType_CustomCode = 'CustomCode';

	/**
	 * @return 
	 **/
	function __construct()
	{
		parent::__construct('CombinedPaymentOptionCodeType', 'urn:ebay:apis:eBLBaseComponents');
	}
}
$Facet_CombinedPaymentOptionCodeType = new CombinedPaymentOptionCodeType();
?>