<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_ComplexType.php';
require_once 'ItemRatingDetailsType.php';

/**
  * Type used by the <b>SellerItemRatingDetailArray</b> container in the <b>LeaveFeedback</b> request payload. This container is used by an eBay buyer to leave one or more Detailed Seller Ratings for their order partner concerning an order line item.
  * 
 **/

class ItemRatingDetailArrayType extends EbatNs_ComplexType
{
	/**
	* @var ItemRatingDetailsType
	**/
	protected $ItemRatingDetails;


	/**
	 * Class Constructor 
	 **/
	function __construct()
	{
		parent::__construct('ItemRatingDetailArrayType', 'urn:ebay:apis:eBLBaseComponents');
		if (!isset(self::$_elements[__CLASS__]))
		{
			self::$_elements[__CLASS__] = array_merge(self::$_elements[get_parent_class()],
			array(
				'ItemRatingDetails' =>
				array(
					'required' => false,
					'type' => 'ItemRatingDetailsType',
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
	 * @return ItemRatingDetailsType
	 * @param integer $index 
	 **/
	function getItemRatingDetails($index = null)
	{
		if ($index !== null)
		{
			return $this->ItemRatingDetails[$index];
		}
		else
		{
			return $this->ItemRatingDetails;
		}
	}

	/**
	 * @return void
	 * @param ItemRatingDetailsType $value
	 * @param integer $index 
	 **/
	function setItemRatingDetails($value, $index = null)
	{
		if ($index !== null)
		{
			$this->ItemRatingDetails[$index] = $value;
		}
		else
		{
			$this->ItemRatingDetails= $value;
		}
	}

	/**
	 * @return void
	 * @param ItemRatingDetailsType $value
	 **/
	function addItemRatingDetails($value)
	{
		$this->ItemRatingDetails[] = $value;
	}

}
?>
