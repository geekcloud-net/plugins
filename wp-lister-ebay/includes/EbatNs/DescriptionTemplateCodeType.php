<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_FacetType.php';

class DescriptionTemplateCodeType extends EbatNs_FacetType
{
	const CodeType_Layout = 'Layout';
	const CodeType_Theme = 'Theme';
	const CodeType_CustomCode = 'CustomCode';

	/**
	 * @return 
	 **/
	function __construct()
	{
		parent::__construct('DescriptionTemplateCodeType', 'urn:ebay:apis:eBLBaseComponents');
	}
}
$Facet_DescriptionTemplateCodeType = new DescriptionTemplateCodeType();
?>