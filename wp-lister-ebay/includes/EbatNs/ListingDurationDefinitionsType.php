<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_ComplexType.php';
require_once 'ListingDurationDefinitionType.php';

/**
  * A type used by the <b>ListingDurations</b> container node that is returned in the response of <b>GetCategoryFeatures</b> call. A <b>ListingDurations</b> container is returned for each listing type supported for the eBay site, and the supported listing duration times for those listing types.
  * 
 **/

class ListingDurationDefinitionsType extends EbatNs_ComplexType
{
	/**
	* @var ListingDurationDefinitionType
	**/
	protected $ListingDuration;


	/**
	 * Class Constructor 
	 **/
	function __construct()
	{
		parent::__construct('ListingDurationDefinitionsType', 'urn:ebay:apis:eBLBaseComponents');
		if (!isset(self::$_elements[__CLASS__]))
		{
			self::$_elements[__CLASS__] = array_merge(self::$_elements[get_parent_class()],
			array(
				'ListingDuration' =>
				array(
					'required' => false,
					'type' => 'ListingDurationDefinitionType',
					'nsURI' => 'urn:ebay:apis:eBLBaseComponents',
					'array' => true,
					'cardinality' => '0..*'
				)));
		}
		$this->_attributes = array_merge($this->_attributes,
		array(
			'Version' =>
			array(
				'name' => ' Version',
				'type' => 'int',
				'use' => 'optional'
			)));
	}

	/**
	 * @return ListingDurationDefinitionType
	 * @param integer $index 
	 **/
	function getListingDuration($index = null)
	{
		if ($index !== null)
		{
			return $this->ListingDuration[$index];
		}
		else
		{
			return $this->ListingDuration;
		}
	}

	/**
	 * @return void
	 * @param ListingDurationDefinitionType $value
	 * @param integer $index 
	 **/
	function setListingDuration($value, $index = null)
	{
		if ($index !== null)
		{
			$this->ListingDuration[$index] = $value;
		}
		else
		{
			$this->ListingDuration= $value;
		}
	}

	/**
	 * @return void
	 * @param ListingDurationDefinitionType $value
	 **/
	function addListingDuration($value)
	{
		$this->ListingDuration[] = $value;
	}


}
?>
