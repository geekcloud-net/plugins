<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_ComplexType.php';

/**
  * Type used by the <b>MaxGranularFitmentCount</b> field that is returned under the <b>FeatureDefinitions</b> container. The <b>MaxGranularFitmentCount</b> field is returned as empty and indicates that a maximum parts compatibility threshold is applicable to some motor vehicle parts and accessory categories on the corresponding eBay site. This field will not be returned if one or more <b>FeatureID</b> fields are included in the call request and <b>MaxGranularFitmentCount</b> is not one of the values passed into those <b>FeatureID</b> fields.
  * 
 **/

class MaxGranularFitmentCountDefinitionType extends EbatNs_ComplexType
{

	/**
	 * Class Constructor 
	 **/
	function __construct()
	{
		parent::__construct('MaxGranularFitmentCountDefinitionType', 'urn:ebay:apis:eBLBaseComponents');
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
