<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_FacetType.php';

class VeROItemStatusCodeType extends EbatNs_FacetType
{
	const CodeType_Received = 'Received';
	const CodeType_Submitted = 'Submitted';
	const CodeType_Removed = 'Removed';
	const CodeType_SubmissionFailed = 'SubmissionFailed';
	const CodeType_ClarificationRequired = 'ClarificationRequired';
	const CodeType_CustomCode = 'CustomCode';

	/**
	 * @return 
	 **/
	function __construct()
	{
		parent::__construct('VeROItemStatusCodeType', 'urn:ebay:apis:eBLBaseComponents');
	}
}
$Facet_VeROItemStatusCodeType = new VeROItemStatusCodeType();
?>