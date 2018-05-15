<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_ComplexType.php';
require_once 'MembershipDetailType.php';

/**
  * Type used by the <b>Membership</b> container that is returned in the response of the <b>GetUser</b> call if the seller is enrolled in one or more eBay membership programs such as eBay Plus.
  * <br/><br/>
  * <span class="tablenote"><b>Note:</b> Currently, eBay Plus is available only to buyers in Germany (DE), but this program is scheduled to come to the Austria and Australia marketplaces in the near future.
  * </span>
  * 
 **/

class MembershipDetailsType extends EbatNs_ComplexType
{
	/**
	* @var MembershipDetailType
	**/
	protected $Program;


	/**
	 * Class Constructor 
	 **/
	function __construct()
	{
		parent::__construct('MembershipDetailsType', 'urn:ebay:apis:eBLBaseComponents');
		if (!isset(self::$_elements[__CLASS__]))
		{
			self::$_elements[__CLASS__] = array_merge(self::$_elements[get_parent_class()],
			array(
				'Program' =>
				array(
					'required' => false,
					'type' => 'MembershipDetailType',
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
	 * @return MembershipDetailType
	 * @param integer $index 
	 **/
	function getProgram($index = null)
	{
		if ($index !== null)
		{
			return $this->Program[$index];
		}
		else
		{
			return $this->Program;
		}
	}

	/**
	 * @return void
	 * @param MembershipDetailType $value
	 * @param integer $index 
	 **/
	function setProgram($value, $index = null)
	{
		if ($index !== null)
		{
			$this->Program[$index] = $value;
		}
		else
		{
			$this->Program= $value;
		}
	}

	/**
	 * @return void
	 * @param MembershipDetailType $value
	 **/
	function addProgram($value)
	{
		$this->Program[] = $value;
	}

}
?>
