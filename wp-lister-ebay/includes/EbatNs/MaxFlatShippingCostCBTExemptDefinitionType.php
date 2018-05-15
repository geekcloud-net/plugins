<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_ComplexType.php';

/**
  * Type used by the <b>MaxFlatShippingCostCBTExempt</b> field that is returned under the <b>FeatureDefinitions</b> container. The <b>MaxFlatShippingCostCBTExempt</b> field is returned as empty  and indicates that a maximum flat-rate shipping cost is not imposed by the corresponding eBay site if the item is being shipped internationally. This field will not be returned if one or more <b>FeatureID</b> fields are included in the call request and <b>MaxFlatShippingCostCBTExempt</b> is not one of the values passed into those <b>FeatureID</b> fields.
  * 
 **/

class MaxFlatShippingCostCBTExemptDefinitionType extends EbatNs_ComplexType
{

	/**
	 * Class Constructor 
	 **/
	function __construct()
	{
		parent::__construct('MaxFlatShippingCostCBTExemptDefinitionType', 'urn:ebay:apis:eBLBaseComponents');
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
