<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_ComplexType.php';

/**
  * Type used by the <b>MaxFlatShippingCost</b> field that is returned under the <b>FeatureDefinitions</b> container. The <b>MaxFlatShippingCost</b> field is returned as empty and indicates that a maximum flat-rate shipping cost threshold is enforced for some categories on the corresponding eBay site. This field will not be returned if one or more <b>FeatureID</b> fields are included in the call request and <b>MaxFlatShippingCost</b> is not one of the values passed into those <b>FeatureID</b> fields.
  * 
 **/

class MaxFlatShippingCostDefinitionType extends EbatNs_ComplexType
{

	/**
	 * Class Constructor 
	 **/
	function __construct()
	{
		parent::__construct('MaxFlatShippingCostDefinitionType', 'urn:ebay:apis:eBLBaseComponents');
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
