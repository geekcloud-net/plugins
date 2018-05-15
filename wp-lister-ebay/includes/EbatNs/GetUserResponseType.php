<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'AbstractResponseType.php';
require_once 'UserType.php';

/**
  * Base response of the <b>GetUser</b> call. This response includes detailed information about the user, including Feedback data, eBay registration date, selling feature eligibility, valid subsriptions, etc.
  * 
 **/

class GetUserResponseType extends AbstractResponseType
{
	/**
	* @var UserType
	**/
	protected $User;


	/**
	 * Class Constructor 
	 **/
	function __construct()
	{
		parent::__construct('GetUserResponseType', 'urn:ebay:apis:eBLBaseComponents');
		if (!isset(self::$_elements[__CLASS__]))
		{
			self::$_elements[__CLASS__] = array_merge(self::$_elements[get_parent_class()],
			array(
				'User' =>
				array(
					'required' => false,
					'type' => 'UserType',
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
	 * @return UserType
	 **/
	function getUser()
	{
		return $this->User;
	}

	/**
	 * @return void
	 **/
	function setUser($value)
	{
		$this->User = $value;
	}

}
?>
