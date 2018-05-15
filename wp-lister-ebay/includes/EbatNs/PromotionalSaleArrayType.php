<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_ComplexType.php';
require_once 'PromotionalSaleType.php';

/**
  * This type is used by the <strong>PromotionalSaleDetails</strong> container returned in the <strong>GetPromotionalSaleDetails</strong> call. The <strong>PromotionalSaleDetails</strong> container consists of one or promotional sales that match the input criteria.
  * <br/><br/>
  * Promotional Sales are only available to eBay sellers who are eBay Store subscribers.
  * 
 **/

class PromotionalSaleArrayType extends EbatNs_ComplexType
{
	/**
	* @var PromotionalSaleType
	**/
	protected $PromotionalSale;


	/**
	 * Class Constructor 
	 **/
	function __construct()
	{
		parent::__construct('PromotionalSaleArrayType', 'urn:ebay:apis:eBLBaseComponents');
		if (!isset(self::$_elements[__CLASS__]))
		{
			self::$_elements[__CLASS__] = array_merge(self::$_elements[get_parent_class()],
			array(
				'PromotionalSale' =>
				array(
					'required' => false,
					'type' => 'PromotionalSaleType',
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
	 * @return PromotionalSaleType
	 * @param integer $index 
	 **/
	function getPromotionalSale($index = null)
	{
		if ($index !== null)
		{
			return $this->PromotionalSale[$index];
		}
		else
		{
			return $this->PromotionalSale;
		}
	}

	/**
	 * @return void
	 * @param PromotionalSaleType $value
	 * @param integer $index 
	 **/
	function setPromotionalSale($value, $index = null)
	{
		if ($index !== null)
		{
			$this->PromotionalSale[$index] = $value;
		}
		else
		{
			$this->PromotionalSale= $value;
		}
	}

	/**
	 * @return void
	 * @param PromotionalSaleType $value
	 **/
	function addPromotionalSale($value)
	{
		$this->PromotionalSale[] = $value;
	}

}
?>
