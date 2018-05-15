<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_FacetType.php';

class GalleryTypeCodeType extends EbatNs_FacetType
{
	const CodeType_None = 'None';
	const CodeType_Featured = 'Featured';
	const CodeType_Gallery = 'Gallery';
	const CodeType_Plus = 'Plus';
	const CodeType_CustomCode = 'CustomCode';

	/**
	 * @return 
	 **/
	function __construct()
	{
		parent::__construct('GalleryTypeCodeType', 'urn:ebay:apis:eBLBaseComponents');
	}
}
$Facet_GalleryTypeCodeType = new GalleryTypeCodeType();
?>