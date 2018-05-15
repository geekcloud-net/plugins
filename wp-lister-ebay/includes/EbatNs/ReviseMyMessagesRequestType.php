<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'AbstractRequestType.php';
require_once 'MyMessagesMessageIDArrayType.php';
require_once 'MyMessagesAlertIDArrayType.php';

/**
  * This call can be used to mark one or more messages as 'Read', to flag one or more messages, and/or to move one or more messages to another My Messages folder. Any of these actions can be applied on up to 10 messages with one call.
  * 
 **/

class ReviseMyMessagesRequestType extends AbstractRequestType
{
	/**
	* @var MyMessagesMessageIDArrayType
	**/
	protected $MessageIDs;

	/**
	* @var MyMessagesAlertIDArrayType
	**/
	protected $AlertIDs;

	/**
	* @var boolean
	**/
	protected $Read;

	/**
	* @var boolean
	**/
	protected $Flagged;

	/**
	* @var long
	**/
	protected $FolderID;


	/**
	 * Class Constructor 
	 **/
	function __construct()
	{
		parent::__construct('ReviseMyMessagesRequestType', 'urn:ebay:apis:eBLBaseComponents');
		if (!isset(self::$_elements[__CLASS__]))
		{
			self::$_elements[__CLASS__] = array_merge(self::$_elements[get_parent_class()],
			array(
				'MessageIDs' =>
				array(
					'required' => false,
					'type' => 'MyMessagesMessageIDArrayType',
					'nsURI' => 'urn:ebay:apis:eBLBaseComponents',
					'array' => false,
					'cardinality' => '0..1'
				),
				'AlertIDs' =>
				array(
					'required' => false,
					'type' => 'MyMessagesAlertIDArrayType',
					'nsURI' => 'urn:ebay:apis:eBLBaseComponents',
					'array' => false,
					'cardinality' => '0..1'
				),
				'Read' =>
				array(
					'required' => false,
					'type' => 'boolean',
					'nsURI' => 'http://www.w3.org/2001/XMLSchema',
					'array' => false,
					'cardinality' => '0..1'
				),
				'Flagged' =>
				array(
					'required' => false,
					'type' => 'boolean',
					'nsURI' => 'http://www.w3.org/2001/XMLSchema',
					'array' => false,
					'cardinality' => '0..1'
				),
				'FolderID' =>
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
	 * @return MyMessagesMessageIDArrayType
	 **/
	function getMessageIDs()
	{
		return $this->MessageIDs;
	}

	/**
	 * @return void
	 **/
	function setMessageIDs($value)
	{
		$this->MessageIDs = $value;
	}

	/**
	 * @return MyMessagesAlertIDArrayType
	 **/
	function getAlertIDs()
	{
		return $this->AlertIDs;
	}

	/**
	 * @return void
	 **/
	function setAlertIDs($value)
	{
		$this->AlertIDs = $value;
	}

	/**
	 * @return boolean
	 **/
	function getRead()
	{
		return $this->Read;
	}

	/**
	 * @return void
	 **/
	function setRead($value)
	{
		$this->Read = $value;
	}

	/**
	 * @return boolean
	 **/
	function getFlagged()
	{
		return $this->Flagged;
	}

	/**
	 * @return void
	 **/
	function setFlagged($value)
	{
		$this->Flagged = $value;
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

}
?>
