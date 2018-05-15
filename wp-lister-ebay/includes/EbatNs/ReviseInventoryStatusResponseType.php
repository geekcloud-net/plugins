<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'AbstractResponseType.php';
require_once 'InventoryStatusType.php';
require_once 'InventoryFeesType.php';

/**
  * The base response type for the <b>ReviseInventoryStatus</b> call. The response includes a <b>Fees</b> container and an <b>InventoryStatus</b> container for each item and/or item variation that was revised.
  * 
 **/

class ReviseInventoryStatusResponseType extends AbstractResponseType
{
	/**
	* @var InventoryStatusType
	**/
	protected $InventoryStatus;

	/**
	* @var InventoryFeesType
	**/
	protected $Fees;


	/**
	 * Class Constructor 
	 **/
	function __construct()
	{
		parent::__construct('ReviseInventoryStatusResponseType', 'urn:ebay:apis:eBLBaseComponents');
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
				),
				'Fees' =>
				array(
					'required' => false,
					'type' => 'InventoryFeesType',
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

	/**
	 * @return InventoryFeesType
	 * @param integer $index 
	 **/
	function getFees($index = null)
	{
		if ($index !== null)
		{
			return $this->Fees[$index];
		}
		else
		{
			return $this->Fees;
		}
	}

	/**
	 * @return void
	 * @param InventoryFeesType $value
	 * @param integer $index 
	 **/
	function setFees($value, $index = null)
	{
		if ($index !== null)
		{
			$this->Fees[$index] = $value;
		}
		else
		{
			$this->Fees= $value;
		}
	}

	/**
	 * @return void
	 * @param InventoryFeesType $value
	 **/
	function addFees($value)
	{
		$this->Fees[] = $value;
	}

}
?>
