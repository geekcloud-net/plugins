<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_FacetType.php';

class SellingManagerPaidStatusCodeType extends EbatNs_FacetType
{
	const CodeType_Paid = 'Paid';
	const CodeType_PartiallyPaid = 'PartiallyPaid';
	const CodeType_Unpaid = 'Unpaid';
	const CodeType_Pending = 'Pending';
	const CodeType_Refunded = 'Refunded';
	const CodeType_CustomCode = 'CustomCode';

	/**
	 * @return 
	 **/
	function __construct()
	{
		parent::__construct('SellingManagerPaidStatusCodeType', 'urn:ebay:apis:eBLBaseComponents');
	}
}
$Facet_SellingManagerPaidStatusCodeType = new SellingManagerPaidStatusCodeType();
?>