<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_FacetType.php';

class PictureFormatCodeType extends EbatNs_FacetType
{
	const CodeType_JPG = 'JPG';
	const CodeType_GIF = 'GIF';
	const CodeType_PNG = 'PNG';
	const CodeType_CustomCode = 'CustomCode';

	/**
	 * @return 
	 **/
	function __construct()
	{
		parent::__construct('PictureFormatCodeType', 'urn:ebay:apis:eBLBaseComponents');
	}
}
$Facet_PictureFormatCodeType = new PictureFormatCodeType();
?>