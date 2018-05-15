<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_ComplexType.php';
require_once 'GroupValidationRulesType.php';
require_once 'NameRecommendationType.php';

/**
  * This type is used to provide details about recommended Product Identifier types. The <b>ProductIdentifiers</b>  container will consist of one or more Product Identifier types that can be, or possibly required to be used when listing a product.
  * <br>
  * <br>
  * <span class="tablenote"><b>Note:</b>
  * The <b>ProductIdentifiers</b> container will only be returned in the Sandbox environment for the time being. This container has not been wired on in production. An alternative way to see if a Product Identifier type is required for a category is to use the <b>GetCategoryFeatures</b> call and look for the values returned under the <b>EANEnabled</b>, <b>ISBNEnabled</b>, <b>UPCEnabled</b>, and other fields that indicate the Product Identifier types that are supported/required for the category.<br>
  * <br>
  * 
 **/

class ProductIdentifiersType extends EbatNs_ComplexType
{
	/**
	* @var GroupValidationRulesType
	**/
	protected $ValidationRules;

	/**
	* @var NameRecommendationType
	**/
	protected $NameRecommendation;


	/**
	 * Class Constructor 
	 **/
	function __construct()
	{
		parent::__construct('ProductIdentifiersType', 'urn:ebay:apis:eBLBaseComponents');
		if (!isset(self::$_elements[__CLASS__]))
		{
			self::$_elements[__CLASS__] = array_merge(self::$_elements[get_parent_class()],
			array(
				'ValidationRules' =>
				array(
					'required' => false,
					'type' => 'GroupValidationRulesType',
					'nsURI' => 'urn:ebay:apis:eBLBaseComponents',
					'array' => false,
					'cardinality' => '0..1'
				),
				'NameRecommendation' =>
				array(
					'required' => false,
					'type' => 'NameRecommendationType',
					'nsURI' => 'urn:ebay:apis:eBLBaseComponents',
					'array' => true,
					'cardinality' => '0..*'
				)));
		}
		$this->_attributes = array_merge($this->_attributes,
		array(
));
	}

	/**
	 * @return GroupValidationRulesType
	 **/
	function getValidationRules()
	{
		return $this->ValidationRules;
	}

	/**
	 * @return void
	 **/
	function setValidationRules($value)
	{
		$this->ValidationRules = $value;
	}

	/**
	 * @return NameRecommendationType
	 * @param integer $index 
	 **/
	function getNameRecommendation($index = null)
	{
		if ($index !== null)
		{
			return $this->NameRecommendation[$index];
		}
		else
		{
			return $this->NameRecommendation;
		}
	}

	/**
	 * @return void
	 * @param NameRecommendationType $value
	 * @param integer $index 
	 **/
	function setNameRecommendation($value, $index = null)
	{
		if ($index !== null)
		{
			$this->NameRecommendation[$index] = $value;
		}
		else
		{
			$this->NameRecommendation= $value;
		}
	}

	/**
	 * @return void
	 * @param NameRecommendationType $value
	 **/
	function addNameRecommendation($value)
	{
		$this->NameRecommendation[] = $value;
	}

}
?>
