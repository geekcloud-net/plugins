<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'AbstractResponseType.php';

/**
  * The base response of the <b>SaveItemToSellingManagerTemplate</b> call. A successful call will return a <b>TemplateID</b> value for the newly-created Selling Manager template.
  * 
 **/

class SaveItemToSellingManagerTemplateResponseType extends AbstractResponseType
{
	/**
	* @var long
	**/
	protected $TemplateID;


	/**
	 * Class Constructor 
	 **/
	function __construct()
	{
		parent::__construct('SaveItemToSellingManagerTemplateResponseType', 'urn:ebay:apis:eBLBaseComponents');
		if (!isset(self::$_elements[__CLASS__]))
		{
			self::$_elements[__CLASS__] = array_merge(self::$_elements[get_parent_class()],
			array(
				'TemplateID' =>
				array(
					'required' => false,
					'type' => 'long',
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
	 * @return long
	 **/
	function getTemplateID()
	{
		return $this->TemplateID;
	}

	/**
	 * @return void
	 **/
	function setTemplateID($value)
	{
		$this->TemplateID = $value;
	}

}
?>
