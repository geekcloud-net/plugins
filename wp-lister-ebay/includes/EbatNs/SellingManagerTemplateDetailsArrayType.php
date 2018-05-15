<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_ComplexType.php';
require_once 'SellingManagerTemplateDetailsType.php';

/**
  * Type used by the <strong>SellingManagerTemplateDetailsArray</strong> container that is returned in the <strong>GetSellingManagerInventory</strong> and <strong>GetSellingManagerTemplates</strong> calls. The <strong>SellingManagerTemplateDetailsArray</strong> container consists of one or more Selling Manager Templates that match the input criteria.
  * 
 **/

class SellingManagerTemplateDetailsArrayType extends EbatNs_ComplexType
{
	/**
	* @var SellingManagerTemplateDetailsType
	**/
	protected $SellingManagerTemplateDetails;


	/**
	 * Class Constructor 
	 **/
	function __construct()
	{
		parent::__construct('SellingManagerTemplateDetailsArrayType', 'urn:ebay:apis:eBLBaseComponents');
		if (!isset(self::$_elements[__CLASS__]))
		{
			self::$_elements[__CLASS__] = array_merge(self::$_elements[get_parent_class()],
			array(
				'SellingManagerTemplateDetails' =>
				array(
					'required' => false,
					'type' => 'SellingManagerTemplateDetailsType',
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
	 * @return SellingManagerTemplateDetailsType
	 * @param integer $index 
	 **/
	function getSellingManagerTemplateDetails($index = null)
	{
		if ($index !== null)
		{
			return $this->SellingManagerTemplateDetails[$index];
		}
		else
		{
			return $this->SellingManagerTemplateDetails;
		}
	}

	/**
	 * @return void
	 * @param SellingManagerTemplateDetailsType $value
	 * @param integer $index 
	 **/
	function setSellingManagerTemplateDetails($value, $index = null)
	{
		if ($index !== null)
		{
			$this->SellingManagerTemplateDetails[$index] = $value;
		}
		else
		{
			$this->SellingManagerTemplateDetails= $value;
		}
	}

	/**
	 * @return void
	 * @param SellingManagerTemplateDetailsType $value
	 **/
	function addSellingManagerTemplateDetails($value)
	{
		$this->SellingManagerTemplateDetails[] = $value;
	}

}
?>
