<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_FacetType.php';

class PictureUploadPolicyCodeType extends EbatNs_FacetType
{
	const CodeType_Add = 'Add';
	const CodeType_ClearAndAdd = 'ClearAndAdd';
	const CodeType_CustomCode = 'CustomCode';

	/**
	 * @return 
	 **/
	function __construct()
	{
		parent::__construct('PictureUploadPolicyCodeType', 'urn:ebay:apis:eBLBaseComponents');
	}
}
$Facet_PictureUploadPolicyCodeType = new PictureUploadPolicyCodeType();
?>