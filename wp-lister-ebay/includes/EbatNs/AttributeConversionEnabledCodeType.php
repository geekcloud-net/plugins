<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_FacetType.php';

class AttributeConversionEnabledCodeType extends EbatNs_FacetType
{
	const CodeType_NotApplicable = 'NotApplicable';
	const CodeType_Enabled = 'Enabled';
	const CodeType_Disabled = 'Disabled';
	const CodeType_CustomCode = 'CustomCode';

	/**
	 * @return 
	 **/
	function __construct()
	{
		parent::__construct('AttributeConversionEnabledCodeType', 'urn:ebay:apis:eBLBaseComponents');
	}
}
$Facet_AttributeConversionEnabledCodeType = new AttributeConversionEnabledCodeType();
?>