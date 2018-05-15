<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'AbstractRequestType.php';

/**
  *   Moves a Selling Manager inventory folder.
  * <br><br>
  * This call is subject to change without notice; the deprecation process is
  * inapplicable to this call. This call is only applicable and accessible by eBay sellers with a Selling Manager Pro subscription.
  * 
 **/

class MoveSellingManagerInventoryFolderRequestType extends AbstractRequestType
{
	/**
	* @var long
	**/
	protected $FolderID;

	/**
	* @var long
	**/
	protected $NewParentFolderID;


	/**
	 * Class Constructor 
	 **/
	function __construct()
	{
		parent::__construct('MoveSellingManagerInventoryFolderRequestType', 'urn:ebay:apis:eBLBaseComponents');
		if (!isset(self::$_elements[__CLASS__]))
		{
			self::$_elements[__CLASS__] = array_merge(self::$_elements[get_parent_class()],
			array(
				'FolderID' =>
				array(
					'required' => false,
					'type' => 'long',
					'nsURI' => 'http://www.w3.org/2001/XMLSchema',
					'array' => false,
					'cardinality' => '0..1'
				),
				'NewParentFolderID' =>
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
	function getFolderID()
	{
		return $this->FolderID;
	}

	/**
	 * @return void
	 **/
	function setFolderID($value)
	{
		$this->FolderID = $value;
	}

	/**
	 * @return long
	 **/
	function getNewParentFolderID()
	{
		return $this->NewParentFolderID;
	}

	/**
	 * @return void
	 **/
	function setNewParentFolderID($value)
	{
		$this->NewParentFolderID = $value;
	}

}
?>
