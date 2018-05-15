<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'AbstractResponseType.php';
require_once 'TaskStatusCodeType.php';

/**
  * The base response of a <b>GetStoreCategoryUpdateStatus</b> call. The response includes the status of an eBay Store Category hierarchy change that was made with a <b>SetStoreCategories</b> call.
  * 
 **/

class GetStoreCategoryUpdateStatusResponseType extends AbstractResponseType
{
	/**
	* @var TaskStatusCodeType
	**/
	protected $Status;


	/**
	 * Class Constructor 
	 **/
	function __construct()
	{
		parent::__construct('GetStoreCategoryUpdateStatusResponseType', 'urn:ebay:apis:eBLBaseComponents');
		if (!isset(self::$_elements[__CLASS__]))
		{
			self::$_elements[__CLASS__] = array_merge(self::$_elements[get_parent_class()],
			array(
				'Status' =>
				array(
					'required' => false,
					'type' => 'TaskStatusCodeType',
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
	 * @return TaskStatusCodeType
	 **/
	function getStatus()
	{
		return $this->Status;
	}

	/**
	 * @return void
	 **/
	function setStatus($value)
	{
		$this->Status = $value;
	}

}
?>
