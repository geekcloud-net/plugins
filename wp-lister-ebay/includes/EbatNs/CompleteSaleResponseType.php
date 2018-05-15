<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'AbstractResponseType.php';

/**
  * Indicates the success or failure of the attempt to leave feedback for the buyer, change the paid status in My eBay, and/or change the shipped status in My eBay.
  * <br><br>
  * When <b>CompleteSale</b> is applied to a specified order (by specifying <b>OrderID</b>), it applies to each line item within the order.
  * 
 **/

class CompleteSaleResponseType extends AbstractResponseType
{

	/**
	 * Class Constructor 
	 **/
	function __construct()
	{
		parent::__construct('CompleteSaleResponseType', 'urn:ebay:apis:eBLBaseComponents');
		if (!isset(self::$_elements[__CLASS__]))
		{
			self::$_elements[__CLASS__] = array_merge(self::$_elements[get_parent_class()],
			array(
));
		}
		$this->_attributes = array_merge($this->_attributes,
		array(
));
	}

}
?>
