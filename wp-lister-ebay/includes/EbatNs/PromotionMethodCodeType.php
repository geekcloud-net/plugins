<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_FacetType.php';

class PromotionMethodCodeType extends EbatNs_FacetType
{
	const CodeType_CrossSell = 'CrossSell';
	const CodeType_UpSell = 'UpSell';
	const CodeType_CustomCode = 'CustomCode';

	/**
	 * @return 
	 **/
	function __construct()
	{
		parent::__construct('PromotionMethodCodeType', 'urn:ebay:apis:eBLBaseComponents');
	}
}
$Facet_PromotionMethodCodeType = new PromotionMethodCodeType();
?>