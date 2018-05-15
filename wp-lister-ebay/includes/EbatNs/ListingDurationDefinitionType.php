<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_ComplexType.php';

/**
  * Type used by the <b>ListingDurations</b> container that is returned for each listing type supported for the eBay site. <b>ListingDurations</b> container shows the supported listing duration times for each listing type.
  * 
 **/

class ListingDurationDefinitionType extends EbatNs_ComplexType
{
	/**
	* @var token
	**/
	protected $Duration;


	/**
	 * Class Constructor 
	 **/
	function __construct()
	{
		parent::__construct('ListingDurationDefinitionType', 'urn:ebay:apis:eBLBaseComponents');
		if (!isset(self::$_elements[__CLASS__]))
		{
			self::$_elements[__CLASS__] = array_merge(self::$_elements[get_parent_class()],
			array(
				'Duration' =>
				array(
					'required' => false,
					'type' => 'token',
					'nsURI' => 'http://www.w3.org/2001/XMLSchema',
					'array' => true,
					'cardinality' => '0..*'
				)));
		}
		$this->_attributes = array_merge($this->_attributes,
		array(
			'durationSetID' =>
			array(
				'name' => ' durationSetID',
				'type' => 'int',
				'use' => 'optional'
			)));
	}

	/**
	 * @return token
	 * @param integer $index 
	 **/
	function getDuration($index = null)
	{
		if ($index !== null)
		{
			return $this->Duration[$index];
		}
		else
		{
			return $this->Duration;
		}
	}

	/**
	 * @return void
	 * @param token $value
	 * @param integer $index 
	 **/
	function setDuration($value, $index = null)
	{
		if ($index !== null)
		{
			$this->Duration[$index] = $value;
		}
		else
		{
			$this->Duration= $value;
		}
	}

	/**
	 * @return void
	 * @param token $value
	 **/
	function addDuration($value)
	{
		$this->Duration[] = $value;
	}


}
?>
