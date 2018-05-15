<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'AbstractResponseType.php';
require_once 'OrderArrayType.php';

/**
  * The base response type for the <b>GetOrderTransactions</b> call. This call retrieves detailed information about one or more eBay.com orders. An <b>OrderArray.Order</b> container is returned for each order that matches the input criteria in the call request.
  * 
 **/

class GetOrderTransactionsResponseType extends AbstractResponseType
{
	/**
	* @var OrderArrayType
	**/
	protected $OrderArray;


	/**
	 * Class Constructor 
	 **/
	function __construct()
	{
		parent::__construct('GetOrderTransactionsResponseType', 'urn:ebay:apis:eBLBaseComponents');
		if (!isset(self::$_elements[__CLASS__]))
		{
			self::$_elements[__CLASS__] = array_merge(self::$_elements[get_parent_class()],
			array(
				'OrderArray' =>
				array(
					'required' => false,
					'type' => 'OrderArrayType',
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
	 * @return OrderArrayType
	 **/
	function getOrderArray()
	{
		return $this->OrderArray;
	}

	/**
	 * @return void
	 **/
	function setOrderArray($value)
	{
		$this->OrderArray = $value;
	}

}
?>
