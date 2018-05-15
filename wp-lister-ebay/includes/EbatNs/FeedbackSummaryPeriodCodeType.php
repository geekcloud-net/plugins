<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_FacetType.php';

class FeedbackSummaryPeriodCodeType extends EbatNs_FacetType
{
	const CodeType_ThirtyDays = 'ThirtyDays';
	const CodeType_FiftyTwoWeeks = 'FiftyTwoWeeks';
	const CodeType_CustomCode = 'CustomCode';

	/**
	 * @return 
	 **/
	function __construct()
	{
		parent::__construct('FeedbackSummaryPeriodCodeType', 'urn:ebay:apis:eBLBaseComponents');
	}
}
$Facet_FeedbackSummaryPeriodCodeType = new FeedbackSummaryPeriodCodeType();
?>