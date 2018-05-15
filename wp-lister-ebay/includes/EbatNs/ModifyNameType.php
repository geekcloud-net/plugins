<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_ComplexType.php';

/**
  * Type used by the <b>ModifyName</b> container in a <b>ReviseFixedPriceItem</b> or <b>RelistFixedPriceItem</b> call to rename a Variation Specific name for a multiple-variation listing. A <b>ModifyName</b> container is needed for each Variation Specific name  that the seller wishes to change the name of in a multiple-variation listing.
  * 
 **/

class ModifyNameType extends EbatNs_ComplexType
{
	/**
	* @var string
	**/
	protected $Name;

	/**
	* @var string
	**/
	protected $NewName;


	/**
	 * Class Constructor 
	 **/
	function __construct()
	{
		parent::__construct('ModifyNameType', 'urn:ebay:apis:eBLBaseComponents');
		if (!isset(self::$_elements[__CLASS__]))
		{
			self::$_elements[__CLASS__] = array_merge(self::$_elements[get_parent_class()],
			array(
				'Name' =>
				array(
					'required' => false,
					'type' => 'string',
					'nsURI' => 'http://www.w3.org/2001/XMLSchema',
					'array' => false,
					'cardinality' => '0..1'
				),
				'NewName' =>
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
	function getName()
	{
		return $this->Name;
	}

	/**
	 * @return void
	 **/
	function setName($value)
	{
		$this->Name = $value;
	}

	/**
	 * @return string
	 **/
	function getNewName()
	{
		return $this->NewName;
	}

	/**
	 * @return void
	 **/
	function setNewName($value)
	{
		$this->NewName = $value;
	}

}
?>
