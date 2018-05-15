<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_ComplexType.php';
require_once 'SiteCodeType.php';

/**
  * Type used by the <b>Program</b> container that is returned in the response of the <b>GetUser</b> call if the seller is enrolled in one or more eBay membership programs, such as eBay Plus. The <b>Program</b> container provides the eBay site, program (such as '<code>EBAYPLUS</code>'), and the membership expiration date.
  * <br/><br/>
  * <span class="tablenote"><b>Note:</b> Currently, eBay Plus is available only to buyers in Germany (DE), but this program is scheduled to come to the Austria and Australia marketplaces in the near future.
  * </span>
  * 
 **/

class MembershipDetailType extends EbatNs_ComplexType
{
	/**
	* @var string
	**/
	protected $ProgramName;

	/**
	* @var SiteCodeType
	**/
	protected $Site;

	/**
	* @var dateTime
	**/
	protected $ExpiryDate;


	/**
	 * Class Constructor 
	 **/
	function __construct()
	{
		parent::__construct('MembershipDetailType', 'urn:ebay:apis:eBLBaseComponents');
		if (!isset(self::$_elements[__CLASS__]))
		{
			self::$_elements[__CLASS__] = array_merge(self::$_elements[get_parent_class()],
			array(
				'ProgramName' =>
				array(
					'required' => false,
					'type' => 'string',
					'nsURI' => 'http://www.w3.org/2001/XMLSchema',
					'array' => false,
					'cardinality' => '0..1'
				),
				'Site' =>
				array(
					'required' => false,
					'type' => 'SiteCodeType',
					'nsURI' => 'urn:ebay:apis:eBLBaseComponents',
					'array' => false,
					'cardinality' => '0..1'
				),
				'ExpiryDate' =>
				array(
					'required' => false,
					'type' => 'dateTime',
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
	 * @return string
	 **/
	function getProgramName()
	{
		return $this->ProgramName;
	}

	/**
	 * @return void
	 **/
	function setProgramName($value)
	{
		$this->ProgramName = $value;
	}

	/**
	 * @return SiteCodeType
	 **/
	function getSite()
	{
		return $this->Site;
	}

	/**
	 * @return void
	 **/
	function setSite($value)
	{
		$this->Site = $value;
	}

	/**
	 * @return dateTime
	 **/
	function getExpiryDate()
	{
		return $this->ExpiryDate;
	}

	/**
	 * @return void
	 **/
	function setExpiryDate($value)
	{
		$this->ExpiryDate = $value;
	}

}
?>
