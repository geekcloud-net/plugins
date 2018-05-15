<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_ComplexType.php';
require_once 'ItemBestOffersType.php';

/**
  * A collection of details about the Best Offers received for a specific item. Empty if there are no Best Offers. Includes the buyer and seller messages only if
  * the <code>ReturnAll</code> detail level is used.
  * 
 **/

class ItemBestOffersArrayType extends EbatNs_ComplexType
{
	/**
	* @var ItemBestOffersType
	**/
	protected $ItemBestOffers;


	/**
	 * Class Constructor 
	 **/
	function __construct()
	{
		parent::__construct('ItemBestOffersArrayType', 'urn:ebay:apis:eBLBaseComponents');
		if (!isset(self::$_elements[__CLASS__]))
		{
			self::$_elements[__CLASS__] = array_merge(self::$_elements[get_parent_class()],
			array(
				'ItemBestOffers' =>
				array(
					'required' => false,
					'type' => 'ItemBestOffersType',
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
	 * @return ItemBestOffersType
	 * @param integer $index 
	 **/
	function getItemBestOffers($index = null)
	{
		if ($index !== null)
		{
			return $this->ItemBestOffers[$index];
		}
		else
		{
			return $this->ItemBestOffers;
		}
	}

	/**
	 * @return void
	 * @param ItemBestOffersType $value
	 * @param integer $index 
	 **/
	function setItemBestOffers($value, $index = null)
	{
		if ($index !== null)
		{
			$this->ItemBestOffers[$index] = $value;
		}
		else
		{
			$this->ItemBestOffers= $value;
		}
	}

	/**
	 * @return void
	 * @param ItemBestOffersType $value
	 **/
	function addItemBestOffers($value)
	{
		$this->ItemBestOffers[] = $value;
	}

}
?>
