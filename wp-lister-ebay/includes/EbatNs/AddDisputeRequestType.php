<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'AbstractRequestType.php';
require_once 'DisputeExplanationCodeType.php';
require_once 'DisputeReasonCodeType.php';
require_once 'ItemIDType.php';

/**
  * Enables a seller to create an Unpaid Item case against a buyer, or to cancel a
  * single line item order.
  * <br>
  * <br>
  * Although the seller is eligible to open up an Unpaid Item case two days after the buyer purchases the item or wins the item through an auction, it is recommended that the seller contacts the buyer first to try and make it right before opening a case. The <a href="http://pages.ebay.com/help/sell/unpaid-items.html" target="_blank">Unpaid Items</a> help page talks more about how a seller should to handle an unpaid item.
  * <br>
  * <br>
  * To cancel a multiple line item order programmatically, the seller would have to use the <a href="https://developer.ebay.com/Devzone/post-order/post-order_v2_cancellation__post.html" target="_blank">POST /post-order/v2/cancellation</a> call of the <b>Post-Order API</b>.
  * 
 **/

class AddDisputeRequestType extends AbstractRequestType
{
	/**
	* @var DisputeExplanationCodeType
	**/
	protected $DisputeExplanation;

	/**
	* @var DisputeReasonCodeType
	**/
	protected $DisputeReason;

	/**
	* @var ItemIDType
	**/
	protected $ItemID;

	/**
	* @var string
	**/
	protected $TransactionID;

	/**
	* @var string
	**/
	protected $OrderLineItemID;


	/**
	 * Class Constructor 
	 **/
	function __construct()
	{
		parent::__construct('AddDisputeRequestType', 'urn:ebay:apis:eBLBaseComponents');
		if (!isset(self::$_elements[__CLASS__]))
		{
			self::$_elements[__CLASS__] = array_merge(self::$_elements[get_parent_class()],
			array(
				'DisputeExplanation' =>
				array(
					'required' => false,
					'type' => 'DisputeExplanationCodeType',
					'nsURI' => 'urn:ebay:apis:eBLBaseComponents',
					'array' => false,
					'cardinality' => '0..1'
				),
				'DisputeReason' =>
				array(
					'required' => false,
					'type' => 'DisputeReasonCodeType',
					'nsURI' => 'urn:ebay:apis:eBLBaseComponents',
					'array' => false,
					'cardinality' => '0..1'
				),
				'ItemID' =>
				array(
					'required' => false,
					'type' => 'ItemIDType',
					'nsURI' => 'urn:ebay:apis:eBLBaseComponents',
					'array' => false,
					'cardinality' => '0..1'
				),
				'TransactionID' =>
				array(
					'required' => false,
					'type' => 'string',
					'nsURI' => 'http://www.w3.org/2001/XMLSchema',
					'array' => false,
					'cardinality' => '0..1'
				),
				'OrderLineItemID' =>
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
	 * @return DisputeExplanationCodeType
	 **/
	function getDisputeExplanation()
	{
		return $this->DisputeExplanation;
	}

	/**
	 * @return void
	 **/
	function setDisputeExplanation($value)
	{
		$this->DisputeExplanation = $value;
	}

	/**
	 * @return DisputeReasonCodeType
	 **/
	function getDisputeReason()
	{
		return $this->DisputeReason;
	}

	/**
	 * @return void
	 **/
	function setDisputeReason($value)
	{
		$this->DisputeReason = $value;
	}

	/**
	 * @return ItemIDType
	 **/
	function getItemID()
	{
		return $this->ItemID;
	}

	/**
	 * @return void
	 **/
	function setItemID($value)
	{
		$this->ItemID = $value;
	}

	/**
	 * @return string
	 **/
	function getTransactionID()
	{
		return $this->TransactionID;
	}

	/**
	 * @return void
	 **/
	function setTransactionID($value)
	{
		$this->TransactionID = $value;
	}

	/**
	 * @return string
	 **/
	function getOrderLineItemID()
	{
		return $this->OrderLineItemID;
	}

	/**
	 * @return void
	 **/
	function setOrderLineItemID($value)
	{
		$this->OrderLineItemID = $value;
	}

}
?>
