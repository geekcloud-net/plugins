<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_FacetType.php';

class ItemCompatibilityEnabledCodeType extends EbatNs_FacetType
{
	const CodeType_Disabled = 'Disabled';
	const CodeType_ByApplication = 'ByApplication';
	const CodeType_BySpecification = 'BySpecification';
	const CodeType_CustomCode = 'CustomCode';

	/**
	 * @return 
	 **/
	function __construct()
	{
		parent::__construct('ItemCompatibilityEnabledCodeType', 'urn:ebay:apis:eBLBaseComponents');
	}
}
$Facet_ItemCompatibilityEnabledCodeType = new ItemCompatibilityEnabledCodeType();
?>