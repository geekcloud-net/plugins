<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_FacetType.php';

class NotificationPayloadTypeCodeType extends EbatNs_FacetType
{
	const CodeType_eBLSchemaSOAP = 'eBLSchemaSOAP';
	const CodeType_CustomCode = 'CustomCode';

	/**
	 * @return 
	 **/
	function __construct()
	{
		parent::__construct('NotificationPayloadTypeCodeType', 'urn:ebay:apis:eBLBaseComponents');
	}
}
$Facet_NotificationPayloadTypeCodeType = new NotificationPayloadTypeCodeType();
?>