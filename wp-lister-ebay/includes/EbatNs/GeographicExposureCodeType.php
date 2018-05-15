<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_FacetType.php';

class GeographicExposureCodeType extends EbatNs_FacetType
{
	const CodeType_National = 'National';
	const CodeType_LocalOnly = 'LocalOnly';
	const CodeType_LocalOptional = 'LocalOptional';
	const CodeType_CustomCode = 'CustomCode';

	/**
	 * @return 
	 **/
	function __construct()
	{
		parent::__construct('GeographicExposureCodeType', 'urn:ebay:apis:eBLBaseComponents');
	}
}
$Facet_GeographicExposureCodeType = new GeographicExposureCodeType();
?>