<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'AbstractResponseType.php';
require_once 'StoreType.php';

/**
  * The base response of the <b>GetStore</b> call. This response consists of the data describing a seller's eBay store, and includes the eBay Store name, the description of the store, the URL to the eBay Store, the subscription level, store theme information, and eBay Store Category hierarchy.
  * 
 **/

class GetStoreResponseType extends AbstractResponseType
{
	/**
	* @var StoreType
	**/
	protected $Store;


	/**
	 * Class Constructor 
	 **/
	function __construct()
	{
		parent::__construct('GetStoreResponseType', 'urn:ebay:apis:eBLBaseComponents');
		if (!isset(self::$_elements[__CLASS__]))
		{
			self::$_elements[__CLASS__] = array_merge(self::$_elements[get_parent_class()],
			array(
				'Store' =>
				array(
					'required' => false,
					'type' => 'StoreType',
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
	 * @return StoreType
	 **/
	function getStore()
	{
		return $this->Store;
	}

	/**
	 * @return void
	 **/
	function setStore($value)
	{
		$this->Store = $value;
	}

}
?>
