<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'AbstractResponseType.php';
require_once 'OfferArrayType.php';
require_once 'UserIDType.php';
require_once 'AmountType.php';
require_once 'ListingStatusCodeType.php';

/**
  * Includes detailed bidding data for the auction listing that was specified in the request. Unless the listing is private, the actual eBay user IDs of all bidders are returned if the listing's seller makes this API call. If a bidder makes this API call, only that bidder's eBay user ID is returned, and the rest of the bidder's user IDs are anonymized.
  * 
 **/

class GetAllBiddersResponseType extends AbstractResponseType
{
	/**
	* @var OfferArrayType
	**/
	protected $BidArray;

	/**
	* @var UserIDType
	**/
	protected $HighBidder;

	/**
	* @var AmountType
	**/
	protected $HighestBid;

	/**
	* @var ListingStatusCodeType
	**/
	protected $ListingStatus;


	/**
	 * Class Constructor 
	 **/
	function __construct()
	{
		parent::__construct('GetAllBiddersResponseType', 'urn:ebay:apis:eBLBaseComponents');
		if (!isset(self::$_elements[__CLASS__]))
		{
			self::$_elements[__CLASS__] = array_merge(self::$_elements[get_parent_class()],
			array(
				'BidArray' =>
				array(
					'required' => false,
					'type' => 'OfferArrayType',
					'nsURI' => 'urn:ebay:apis:eBLBaseComponents',
					'array' => false,
					'cardinality' => '0..1'
				),
				'HighBidder' =>
				array(
					'required' => false,
					'type' => 'UserIDType',
					'nsURI' => 'urn:ebay:apis:eBLBaseComponents',
					'array' => false,
					'cardinality' => '0..1'
				),
				'HighestBid' =>
				array(
					'required' => false,
					'type' => 'AmountType',
					'nsURI' => 'urn:ebay:apis:eBLBaseComponents',
					'array' => false,
					'cardinality' => '0..1'
				),
				'ListingStatus' =>
				array(
					'required' => false,
					'type' => 'ListingStatusCodeType',
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
	 * @return OfferArrayType
	 **/
	function getBidArray()
	{
		return $this->BidArray;
	}

	/**
	 * @return void
	 **/
	function setBidArray($value)
	{
		$this->BidArray = $value;
	}

	/**
	 * @return UserIDType
	 **/
	function getHighBidder()
	{
		return $this->HighBidder;
	}

	/**
	 * @return void
	 **/
	function setHighBidder($value)
	{
		$this->HighBidder = $value;
	}

	/**
	 * @return AmountType
	 **/
	function getHighestBid()
	{
		return $this->HighestBid;
	}

	/**
	 * @return void
	 **/
	function setHighestBid($value)
	{
		$this->HighestBid = $value;
	}

	/**
	 * @return ListingStatusCodeType
	 **/
	function getListingStatus()
	{
		return $this->ListingStatus;
	}

	/**
	 * @return void
	 **/
	function setListingStatus($value)
	{
		$this->ListingStatus = $value;
	}

}
?>
