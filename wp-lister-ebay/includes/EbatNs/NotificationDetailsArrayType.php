<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_ComplexType.php';
require_once 'NotificationDetailsType.php';

/**
  * Type used by the <b>NotificationDetailsArray</b> container that is returned by the <b>GetNotificationsUsage</b> call. The <b>NotificationDetailsArray</b> container consists of one or more notifications that match the input criteria in the call request.
  * <br><br>
  * This container is only returned if an <b>ItemID</b> value was specified in the request, and there were notifications related to this listing during the specified time range.
  * 
 **/

class NotificationDetailsArrayType extends EbatNs_ComplexType
{
	/**
	* @var NotificationDetailsType
	**/
	protected $NotificationDetails;


	/**
	 * Class Constructor 
	 **/
	function __construct()
	{
		parent::__construct('NotificationDetailsArrayType', 'urn:ebay:apis:eBLBaseComponents');
		if (!isset(self::$_elements[__CLASS__]))
		{
			self::$_elements[__CLASS__] = array_merge(self::$_elements[get_parent_class()],
			array(
				'NotificationDetails' =>
				array(
					'required' => false,
					'type' => 'NotificationDetailsType',
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
	 * @return NotificationDetailsType
	 * @param integer $index 
	 **/
	function getNotificationDetails($index = null)
	{
		if ($index !== null)
		{
			return $this->NotificationDetails[$index];
		}
		else
		{
			return $this->NotificationDetails;
		}
	}

	/**
	 * @return void
	 * @param NotificationDetailsType $value
	 * @param integer $index 
	 **/
	function setNotificationDetails($value, $index = null)
	{
		if ($index !== null)
		{
			$this->NotificationDetails[$index] = $value;
		}
		else
		{
			$this->NotificationDetails= $value;
		}
	}

	/**
	 * @return void
	 * @param NotificationDetailsType $value
	 **/
	function addNotificationDetails($value)
	{
		$this->NotificationDetails[] = $value;
	}

}
?>
