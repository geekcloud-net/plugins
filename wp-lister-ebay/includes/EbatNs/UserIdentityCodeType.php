<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_FacetType.php';

class UserIdentityCodeType extends EbatNs_FacetType
{
	const CodeType_eBayUser = 'eBayUser';
	const CodeType_eBayPartner = 'eBayPartner';
	const CodeType_CustomCode = 'CustomCode';

	/**
	 * @return 
	 **/
	function __construct()
	{
		parent::__construct('UserIdentityCodeType', 'urn:ebay:apis:eBLBaseComponents');
	}
}
$Facet_UserIdentityCodeType = new UserIdentityCodeType();
?>