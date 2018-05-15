<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'AbstractRequestType.php';
require_once 'CurrencyCodeType.php';
require_once 'CombinedPaymentPeriodCodeType.php';
require_once 'ModifyActionCodeType.php';
require_once 'FlatShippingDiscountType.php';
require_once 'CalculatedShippingDiscountType.php';
require_once 'CalculatedHandlingDiscountType.php';
require_once 'PromotionalShippingDiscountDetailsType.php';
require_once 'ShippingInsuranceType.php';

/**
  * This call enables a seller to create and manage shipping discounts rules. These are the same shipping discount rules that can be created or managed in My eBay Shipping Preferences.
  * <br/><br/>
  * The types of shipping discount rules that can be created and managed with this call include flat-rate shipping rules, calculated shipping rules, and promotional shipping rules. This call can also be used by sellers to set whether or not they allow buyers to combine separate line items into one Combined Invoice order, and how many days they allow buyers to perform that action.
  * <br/><br/>
  * A seller can only create, update, or delete one discount rule type with each call. The action to take (either <code>Add</code>, <code>Update</code>, or <code>Delete</code>) is set and controlled with the <b>ModifyActionCode</b> field.
  * 
 **/

class SetShippingDiscountProfilesRequestType extends AbstractRequestType
{
	/**
	* @var CurrencyCodeType
	**/
	protected $CurrencyID;

	/**
	* @var CombinedPaymentPeriodCodeType
	**/
	protected $CombinedDuration;

	/**
	* @var ModifyActionCodeType
	**/
	protected $ModifyActionCode;

	/**
	* @var FlatShippingDiscountType
	**/
	protected $FlatShippingDiscount;

	/**
	* @var CalculatedShippingDiscountType
	**/
	protected $CalculatedShippingDiscount;

	/**
	* @var CalculatedHandlingDiscountType
	**/
	protected $CalculatedHandlingDiscount;

	/**
	* @var PromotionalShippingDiscountDetailsType
	**/
	protected $PromotionalShippingDiscountDetails;

	/**
	* @var ShippingInsuranceType
	**/
	protected $ShippingInsurance;

	/**
	* @var ShippingInsuranceType
	**/
	protected $InternationalShippingInsurance;


	/**
	 * Class Constructor 
	 **/
	function __construct()
	{
		parent::__construct('SetShippingDiscountProfilesRequestType', 'urn:ebay:apis:eBLBaseComponents');
		if (!isset(self::$_elements[__CLASS__]))
		{
			self::$_elements[__CLASS__] = array_merge(self::$_elements[get_parent_class()],
			array(
				'CurrencyID' =>
				array(
					'required' => false,
					'type' => 'CurrencyCodeType',
					'nsURI' => 'urn:ebay:apis:eBLBaseComponents',
					'array' => false,
					'cardinality' => '0..1'
				),
				'CombinedDuration' =>
				array(
					'required' => false,
					'type' => 'CombinedPaymentPeriodCodeType',
					'nsURI' => 'urn:ebay:apis:eBLBaseComponents',
					'array' => false,
					'cardinality' => '0..1'
				),
				'ModifyActionCode' =>
				array(
					'required' => false,
					'type' => 'ModifyActionCodeType',
					'nsURI' => 'urn:ebay:apis:eBLBaseComponents',
					'array' => false,
					'cardinality' => '0..1'
				),
				'FlatShippingDiscount' =>
				array(
					'required' => false,
					'type' => 'FlatShippingDiscountType',
					'nsURI' => 'urn:ebay:apis:eBLBaseComponents',
					'array' => false,
					'cardinality' => '0..1'
				),
				'CalculatedShippingDiscount' =>
				array(
					'required' => false,
					'type' => 'CalculatedShippingDiscountType',
					'nsURI' => 'urn:ebay:apis:eBLBaseComponents',
					'array' => false,
					'cardinality' => '0..1'
				),
				'CalculatedHandlingDiscount' =>
				array(
					'required' => false,
					'type' => 'CalculatedHandlingDiscountType',
					'nsURI' => 'urn:ebay:apis:eBLBaseComponents',
					'array' => false,
					'cardinality' => '0..1'
				),
				'PromotionalShippingDiscountDetails' =>
				array(
					'required' => false,
					'type' => 'PromotionalShippingDiscountDetailsType',
					'nsURI' => 'urn:ebay:apis:eBLBaseComponents',
					'array' => false,
					'cardinality' => '0..1'
				),
				'ShippingInsurance' =>
				array(
					'required' => false,
					'type' => 'ShippingInsuranceType',
					'nsURI' => 'urn:ebay:apis:eBLBaseComponents',
					'array' => false,
					'cardinality' => '0..1'
				),
				'InternationalShippingInsurance' =>
				array(
					'required' => false,
					'type' => 'ShippingInsuranceType',
					'nsURI' => 'urn:ebay:apis:eBLBaseComponents',
					'array' => false,
					'cardinality' => '0..1'
				)));
		}
		$this->_attributes = array_merge($this->_attributes,
		array(
));
	}

	/**
	 * @return CurrencyCodeType
	 **/
	function getCurrencyID()
	{
		return $this->CurrencyID;
	}

	/**
	 * @return void
	 **/
	function setCurrencyID($value)
	{
		$this->CurrencyID = $value;
	}

	/**
	 * @return CombinedPaymentPeriodCodeType
	 **/
	function getCombinedDuration()
	{
		return $this->CombinedDuration;
	}

	/**
	 * @return void
	 **/
	function setCombinedDuration($value)
	{
		$this->CombinedDuration = $value;
	}

	/**
	 * @return ModifyActionCodeType
	 **/
	function getModifyActionCode()
	{
		return $this->ModifyActionCode;
	}

	/**
	 * @return void
	 **/
	function setModifyActionCode($value)
	{
		$this->ModifyActionCode = $value;
	}

	/**
	 * @return FlatShippingDiscountType
	 **/
	function getFlatShippingDiscount()
	{
		return $this->FlatShippingDiscount;
	}

	/**
	 * @return void
	 **/
	function setFlatShippingDiscount($value)
	{
		$this->FlatShippingDiscount = $value;
	}

	/**
	 * @return CalculatedShippingDiscountType
	 **/
	function getCalculatedShippingDiscount()
	{
		return $this->CalculatedShippingDiscount;
	}

	/**
	 * @return void
	 **/
	function setCalculatedShippingDiscount($value)
	{
		$this->CalculatedShippingDiscount = $value;
	}

	/**
	 * @return CalculatedHandlingDiscountType
	 **/
	function getCalculatedHandlingDiscount()
	{
		return $this->CalculatedHandlingDiscount;
	}

	/**
	 * @return void
	 **/
	function setCalculatedHandlingDiscount($value)
	{
		$this->CalculatedHandlingDiscount = $value;
	}

	/**
	 * @return PromotionalShippingDiscountDetailsType
	 **/
	function getPromotionalShippingDiscountDetails()
	{
		return $this->PromotionalShippingDiscountDetails;
	}

	/**
	 * @return void
	 **/
	function setPromotionalShippingDiscountDetails($value)
	{
		$this->PromotionalShippingDiscountDetails = $value;
	}

	/**
	 * @return ShippingInsuranceType
	 **/
	function getShippingInsurance()
	{
		return $this->ShippingInsurance;
	}

	/**
	 * @return void
	 **/
	function setShippingInsurance($value)
	{
		$this->ShippingInsurance = $value;
	}

	/**
	 * @return ShippingInsuranceType
	 **/
	function getInternationalShippingInsurance()
	{
		return $this->InternationalShippingInsurance;
	}

	/**
	 * @return void
	 **/
	function setInternationalShippingInsurance($value)
	{
		$this->InternationalShippingInsurance = $value;
	}

}
?>
