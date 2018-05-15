<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'AbstractResponseType.php';
require_once 'SellingManagerTemplateDetailsArrayType.php';

/**
  * The base response of the <b>GetSellingManagerTemplates</b> call. A <b>SellingManagerTemplateDetails</b> container is returned for each Selling Manager Template that matches the input criteria.
  * 
 **/

class GetSellingManagerTemplatesResponseType extends AbstractResponseType
{
	/**
	* @var SellingManagerTemplateDetailsArrayType
	**/
	protected $SellingManagerTemplateDetailsArray;


	/**
	 * Class Constructor 
	 **/
	function __construct()
	{
		parent::__construct('GetSellingManagerTemplatesResponseType', 'urn:ebay:apis:eBLBaseComponents');
		if (!isset(self::$_elements[__CLASS__]))
		{
			self::$_elements[__CLASS__] = array_merge(self::$_elements[get_parent_class()],
			array(
				'SellingManagerTemplateDetailsArray' =>
				array(
					'required' => false,
					'type' => 'SellingManagerTemplateDetailsArrayType',
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
	 * @return SellingManagerTemplateDetailsArrayType
	 **/
	function getSellingManagerTemplateDetailsArray()
	{
		return $this->SellingManagerTemplateDetailsArray;
	}

	/**
	 * @return void
	 **/
	function setSellingManagerTemplateDetailsArray($value)
	{
		$this->SellingManagerTemplateDetailsArray = $value;
	}

}
?>
