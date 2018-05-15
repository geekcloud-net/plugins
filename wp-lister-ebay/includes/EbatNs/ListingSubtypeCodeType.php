<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_FacetType.php';

class ListingSubtypeCodeType extends EbatNs_FacetType
{
	const CodeType_ClassifiedAd = 'ClassifiedAd';
	const CodeType_LocalMarketBestOfferOnly = 'LocalMarketBestOfferOnly';
	const CodeType_CustomCode = 'CustomCode';

	/**
	 * @return 
	 **/
	function __construct()
	{
		parent::__construct('ListingSubtypeCodeType', 'urn:ebay:apis:eBLBaseComponents');
	}
}
$Facet_ListingSubtypeCodeType = new ListingSubtypeCodeType();
?>