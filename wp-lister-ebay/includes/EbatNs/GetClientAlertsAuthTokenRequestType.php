<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'AbstractRequestType.php';

/**
  * This is the base request type for the <b>GetClientAlertsAuthToken</b> call. This call retrieves a Client Alerts token for the user, which is required when the user makes a <b>GetUserAlerts</b> call (Client Alerts API).
  * 
 **/

class GetClientAlertsAuthTokenRequestType extends AbstractRequestType
{

	/**
	 * Class Constructor 
	 **/
	function __construct()
	{
		parent::__construct('GetClientAlertsAuthTokenRequestType', 'urn:ebay:apis:eBLBaseComponents');
		if (!isset(self::$_elements[__CLASS__]))
		{
			self::$_elements[__CLASS__] = array_merge(self::$_elements[get_parent_class()],
			array(
));
		}
		$this->_attributes = array_merge($this->_attributes,
		array(
));
	}

}
?>
