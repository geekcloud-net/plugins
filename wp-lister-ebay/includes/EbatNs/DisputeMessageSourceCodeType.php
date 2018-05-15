<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_FacetType.php';

class DisputeMessageSourceCodeType extends EbatNs_FacetType
{
	const CodeType_Buyer = 'Buyer';
	const CodeType_Seller = 'Seller';
	const CodeType_eBay = 'eBay';
	const CodeType_CustomCode = 'CustomCode';

	/**
	 * @return 
	 **/
	function __construct()
	{
		parent::__construct('DisputeMessageSourceCodeType', 'urn:ebay:apis:eBLBaseComponents');
	}
}
$Facet_DisputeMessageSourceCodeType = new DisputeMessageSourceCodeType();
?>