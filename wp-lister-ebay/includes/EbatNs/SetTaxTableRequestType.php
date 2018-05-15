<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'AbstractRequestType.php';
require_once 'TaxTableType.php';

/**
  * This call allows you to add or modify sales tax rates for one or more tax jurisdictions within the specified site. Any additions or modifications made with this call is saved in the seller's Sales Tax Table in My eBay.
  * <br/><br/>
  * Sales Tax Tables are only supported on the US, Canada (English  and French versions), and India sites, so this call is only applicable to those sites. To view their current Sales Tax Table, a seller may go to the Sales Tax Table in My eBay, or they can make a <b>GetTaxTable</b> call.
  * 
 **/

class SetTaxTableRequestType extends AbstractRequestType
{
	/**
	* @var TaxTableType
	**/
	protected $TaxTable;


	/**
	 * Class Constructor 
	 **/
	function __construct()
	{
		parent::__construct('SetTaxTableRequestType', 'urn:ebay:apis:eBLBaseComponents');
		if (!isset(self::$_elements[__CLASS__]))
		{
			self::$_elements[__CLASS__] = array_merge(self::$_elements[get_parent_class()],
			array(
				'TaxTable' =>
				array(
					'required' => false,
					'type' => 'TaxTableType',
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
	 * @return TaxTableType
	 **/
	function getTaxTable()
	{
		return $this->TaxTable;
	}

	/**
	 * @return void
	 **/
	function setTaxTable($value)
	{
		$this->TaxTable = $value;
	}

}
?>
