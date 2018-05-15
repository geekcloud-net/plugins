<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_ComplexType.php';
require_once 'ValType.php';

/**
  *   This type is only applicable for Half.com listings, and since the Half.com site was taken down, this type is no longer applicable.
  *   
 **/

class AttributeType extends EbatNs_ComplexType
{
	/**
	* @var ValType
	**/
	protected $Value;


	/**
	 * Class Constructor 
	 **/
	function __construct()
	{
		parent::__construct('AttributeType', 'urn:ebay:apis:eBLBaseComponents');
		if (!isset(self::$_elements[__CLASS__]))
		{
			self::$_elements[__CLASS__] = array_merge(self::$_elements[get_parent_class()],
			array(
				'Value' =>
				array(
					'required' => false,
					'type' => 'ValType',
					'nsURI' => 'urn:ebay:apis:eBLBaseComponents',
					'array' => true,
					'cardinality' => '0..*'
				)));
		}
		$this->_attributes = array_merge($this->_attributes,
		array(
			'attributeID' =>
			array(
				'name' => ' attributeID',
				'type' => 'int',
				'use' => 'optional'
			),
			'attributeLabel' =>
			array(
				'name' => ' attributeLabel',
				'type' => 'string',
				'use' => 'optional'
			)));
	}

	/**
	 * @return ValType
	 * @param integer $index 
	 **/
	function getValue($index = null)
	{
		if ($index !== null)
		{
			return $this->Value[$index];
		}
		else
		{
			return $this->Value;
		}
	}

	/**
	 * @return void
	 * @param ValType $value
	 * @param integer $index 
	 **/
	function setValue($value, $index = null)
	{
		if ($index !== null)
		{
			$this->Value[$index] = $value;
		}
		else
		{
			$this->Value= $value;
		}
	}

	/**
	 * @return void
	 * @param ValType $value
	 **/
	function addValue($value)
	{
		$this->Value[] = $value;
	}



}
?>
