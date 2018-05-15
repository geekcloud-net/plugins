<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_ComplexType.php';

/**
  *    This type is used to reference a seller's specific domestic and/or international shipping rate tables. Shipping rate tables allow sellers to configure specific shipping costs based on the shipping destinations and level of service (e.g. economy, standard, expedite, and one-day). Generally speaking, sellers want to use these shipping rate tables so they can charge a higher shipping cost to the buyer whenever shipping costs are higher for them as well. For example, shipping to Alaska or Hawaii is generally more expensive than shipping to any other of the 48 US states, or in regards to international shipping, shipping to some regions and countries are more expensive than others.
  *     <br><br>
  *  Sellers configure domestic and international shipping rate tables in My eBay Shipping Preferences. To apply shipping rate tables, the shipping cost type must be flat-rate.
  *  <br><br>
  *  For domestic shipping rate tables, the three supported domestic regions are Alaska & Hawaii, US Protectorates (e.g. Puerto Rico and Guam), and APO/FPO destinations, which are US military bases/locations outside of the continental US. In addition to setting one flat rate based on the destination and service level, the seller also has the option of adding an extra charge based on the weight of the shipping package, or they can add a surcharge instead.
  *  <br><br>
  *  For international shipping rate tables, specific rates may be set up for any and all geographical regions and individual countries within those regions. Similar to domestic shipping rate tables, the seller has the option of adding an extra charge based on the weight of the shipping package. Sellers cannot add a surcharge for international shipping.
  *  <br/><br/>
  *  <span class="tablenote"><b>Note: </b> The capability to create and use multiple domestic and international shipping rate tables (up to 40 per seller account) has rolled out to the US and Australia sites. This capability will also be rolling out to the UK and Germany sites in the near future. Currently, for sites other than the US and Australia, only one domestic and one international shipping rate table may be set up per seller. Until the seller's account is updated with the new shipping rate tables in My eBay, the seller will continue to use the <b>DomesticRateTable</b> and <b>InternationalRateTable</b> tags and pass in <code>Default</code> as the value. Once the seller's account is updated with the new shipping rate tables in My eBay, the seller will be required to use the new <b>DomesticRateTableId</b> and <b>InternationalRateTableId</b> tags, and the <b>DomesticRateTable</b> and <b>InternationalRateTable</b> tags will not work.  Note that shipping rate tables can also be applied to Shipping business policies that are applied against a listing. The new shipping rate tables have all of the functionality of the old shipping rate tables, plus the seller has access to all domestic regions and not just the special regions (such as Alaska & Hawaii, US Protectorates, and APO/FPO locations in US).</span>
  * 
 **/

class RateTableDetailsType extends EbatNs_ComplexType
{
	/**
	* @var string
	**/
	protected $DomesticRateTable;

	/**
	* @var string
	**/
	protected $InternationalRateTable;

	/**
	* @var string
	**/
	protected $DomesticRateTableId;

	/**
	* @var string
	**/
	protected $InternationalRateTableId;


	/**
	 * Class Constructor 
	 **/
	function __construct()
	{
		parent::__construct('RateTableDetailsType', 'urn:ebay:apis:eBLBaseComponents');
		if (!isset(self::$_elements[__CLASS__]))
		{
			self::$_elements[__CLASS__] = array_merge(self::$_elements[get_parent_class()],
			array(
				'DomesticRateTable' =>
				array(
					'required' => false,
					'type' => 'string',
					'nsURI' => 'http://www.w3.org/2001/XMLSchema',
					'array' => false,
					'cardinality' => '0..1'
				),
				'InternationalRateTable' =>
				array(
					'required' => false,
					'type' => 'string',
					'nsURI' => 'http://www.w3.org/2001/XMLSchema',
					'array' => false,
					'cardinality' => '0..1'
				),
				'DomesticRateTableId' =>
				array(
					'required' => false,
					'type' => 'string',
					'nsURI' => 'http://www.w3.org/2001/XMLSchema',
					'array' => false,
					'cardinality' => '0..1'
				),
				'InternationalRateTableId' =>
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
	 * @return string
	 **/
	function getDomesticRateTable()
	{
		return $this->DomesticRateTable;
	}

	/**
	 * @return void
	 **/
	function setDomesticRateTable($value)
	{
		$this->DomesticRateTable = $value;
	}

	/**
	 * @return string
	 **/
	function getInternationalRateTable()
	{
		return $this->InternationalRateTable;
	}

	/**
	 * @return void
	 **/
	function setInternationalRateTable($value)
	{
		$this->InternationalRateTable = $value;
	}

	/**
	 * @return string
	 **/
	function getDomesticRateTableId()
	{
		return $this->DomesticRateTableId;
	}

	/**
	 * @return void
	 **/
	function setDomesticRateTableId($value)
	{
		$this->DomesticRateTableId = $value;
	}

	/**
	 * @return string
	 **/
	function getInternationalRateTableId()
	{
		return $this->InternationalRateTableId;
	}

	/**
	 * @return void
	 **/
	function setInternationalRateTableId($value)
	{
		$this->InternationalRateTableId = $value;
	}

}
?>
