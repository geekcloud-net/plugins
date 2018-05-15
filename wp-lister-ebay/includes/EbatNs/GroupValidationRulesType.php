<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_ComplexType.php';

/**
  *  This type is used to specify how many of the corresponding Product Identifier types are required to be specified at listing time.
  * 
 **/

class GroupValidationRulesType extends EbatNs_ComplexType
{
	/**
	* @var int
	**/
	protected $MinRequired;


	/**
	 * Class Constructor 
	 **/
	function __construct()
	{
		parent::__construct('GroupValidationRulesType', 'urn:ebay:apis:eBLBaseComponents');
		if (!isset(self::$_elements[__CLASS__]))
		{
			self::$_elements[__CLASS__] = array_merge(self::$_elements[get_parent_class()],
			array(
				'MinRequired' =>
				array(
					'required' => false,
					'type' => 'int',
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
	 * @return int
	 **/
	function getMinRequired()
	{
		return $this->MinRequired;
	}

	/**
	 * @return void
	 **/
	function setMinRequired($value)
	{
		$this->MinRequired = $value;
	}

}
?>
