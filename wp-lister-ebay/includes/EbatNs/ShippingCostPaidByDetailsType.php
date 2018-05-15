<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_ComplexType.php';

/**
  * This type defines the <b>ShippingCostPaidBy</b> container that is returned in <b>GeteBayDetails</b> if <b>ReturnPolicyDetails</b> is set as a <b>DetailName</b> value (or if no value is included in the request).
  * 
 **/

class ShippingCostPaidByDetailsType extends EbatNs_ComplexType
{
	/**
	* @var token
	**/
	protected $ShippingCostPaidByOption;

	/**
	* @var string
	**/
	protected $Description;


	/**
	 * Class Constructor 
	 **/
	function __construct()
	{
		parent::__construct('ShippingCostPaidByDetailsType', 'urn:ebay:apis:eBLBaseComponents');
		if (!isset(self::$_elements[__CLASS__]))
		{
			self::$_elements[__CLASS__] = array_merge(self::$_elements[get_parent_class()],
			array(
				'ShippingCostPaidByOption' =>
				array(
					'required' => false,
					'type' => 'token',
					'nsURI' => 'http://www.w3.org/2001/XMLSchema',
					'array' => false,
					'cardinality' => '0..1'
				),
				'Description' =>
				array(
					'required' => false,
					'type' => 'string',
					'nsURI' => 'http://www.w3.org/2001/XMLSchema',
					'array' => false,
					'cardinality' => '0..1'
				)));
		}
		$this->_attributes = array_merge($this->_attributes,
		array(
));
	}

	/**
	 * @return token
	 **/
	function getShippingCostPaidByOption()
	{
		return $this->ShippingCostPaidByOption;
	}

	/**
	 * @return void
	 **/
	function setShippingCostPaidByOption($value)
	{
		$this->ShippingCostPaidByOption = $value;
	}

	/**
	 * @return string
	 **/
	function getDescription()
	{
		return $this->Description;
	}

	/**
	 * @return void
	 **/
	function setDescription($value)
	{
		$this->Description = $value;
	}

}
?>
