<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'AbstractRequestType.php';
require_once 'InventoryStatusType.php';

/**
  * Enables a seller to change the price and/or quantity of one to four
  * active, fixed-price listings. The fixed-price listing to modify is identified with the <b>ItemID</b> of the listing and/or the <b>SKU</b> value of the item (if a seller-defined SKU value exists for the listing). If the seller is modifying one or more variations within a multiple-variation listing, the <b>ItemID</b> and <b>SKU</b> fields in the <b>InventoryStatus</b> container become required, with the <b>ItemID</b> value identifying the listing, and the <b>SKU</b> value identifying the specific product variation within that multiple-variation listing. Each variation within a multiple-variation listing requires a seller-defined SKU value.
  * <br/><br/>
  * Whether updating the price and/or quantity of a single-variation listing or a specific variation within a multiple-variation listing, the limit of items or item variations that can be modified with one call is four.
  * 
 **/

class ReviseInventoryStatusRequestType extends AbstractRequestType
{
	/**
	* @var InventoryStatusType
	**/
	protected $InventoryStatus;


	/**
	 * Class Constructor 
	 **/
	function __construct()
	{
		parent::__construct('ReviseInventoryStatusRequestType', 'urn:ebay:apis:eBLBaseComponents');
		if (!isset(self::$_elements[__CLASS__]))
		{
			self::$_elements[__CLASS__] = array_merge(self::$_elements[get_parent_class()],
			array(
				'InventoryStatus' =>
				array(
					'required' => false,
					'type' => 'InventoryStatusType',
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
	 * @return InventoryStatusType
	 * @param integer $index 
	 **/
	function getInventoryStatus($index = null)
	{
		if ($index !== null)
		{
			return $this->InventoryStatus[$index];
		}
		else
		{
			return $this->InventoryStatus;
		}
	}

	/**
	 * @return void
	 * @param InventoryStatusType $value
	 * @param integer $index 
	 **/
	function setInventoryStatus($value, $index = null)
	{
		if ($index !== null)
		{
			$this->InventoryStatus[$index] = $value;
		}
		else
		{
			$this->InventoryStatus= $value;
		}
	}

	/**
	 * @return void
	 * @param InventoryStatusType $value
	 **/
	function addInventoryStatus($value)
	{
		$this->InventoryStatus[] = $value;
	}

}
?>
