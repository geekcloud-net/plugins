<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_FacetType.php';

class StoreCustomListingHeaderLinkCodeType extends EbatNs_FacetType
{
	const CodeType_None = 'None';
	const CodeType_AboutMePage = 'AboutMePage';
	const CodeType_CustomPage = 'CustomPage';
	const CodeType_CustomCategory = 'CustomCategory';
	const CodeType_CustomCode = 'CustomCode';

	/**
	 * @return 
	 **/
	function __construct()
	{
		parent::__construct('StoreCustomListingHeaderLinkCodeType', 'urn:ebay:apis:eBLBaseComponents');
	}
}
$Facet_StoreCustomListingHeaderLinkCodeType = new StoreCustomListingHeaderLinkCodeType();
?>