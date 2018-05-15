<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_ComplexType.php';

/**
  * Defines the payment options group feature. If a field of this type is present, the corresponding feature applies to the site. The field is returned as an integer. The below are the integer values returned and its meanings.
  * 0 - Ebay Payment Process Enabled
  * 1 - Non Standard Payments Enabled
  * 2 - Ebay Payment Process Excluded
  * 
 **/

class PaymentOptionsGroupEnabledDefinitionType extends EbatNs_ComplexType
{

	/**
	 * Class Constructor 
	 **/
	function __construct()
	{
		parent::__construct('PaymentOptionsGroupEnabledDefinitionType', 'urn:ebay:apis:eBLBaseComponents');
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
