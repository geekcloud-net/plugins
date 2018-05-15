<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'EbatNs_ComplexType.php';

/**
  * Type used by the <b>MaximumItemRequirements</b> container to specify the maximum quantity of an order line item that a prospective buyer may purchase during any given 10-day period.
  * 
 **/

class MaximumItemRequirementsType extends EbatNs_ComplexType
{
	/**
	* @var int
	**/
	protected $MaximumItemCount;

	/**
	* @var int
	**/
	protected $MinimumFeedbackScore;


	/**
	 * Class Constructor 
	 **/
	function __construct()
	{
		parent::__construct('MaximumItemRequirementsType', 'urn:ebay:apis:eBLBaseComponents');
		if (!isset(self::$_elements[__CLASS__]))
		{
			self::$_elements[__CLASS__] = array_merge(self::$_elements[get_parent_class()],
			array(
				'MaximumItemCount' =>
				array(
					'required' => false,
					'type' => 'int',
					'nsURI' => 'http://www.w3.org/2001/XMLSchema',
					'array' => false,
					'cardinality' => '0..1'
				),
				'MinimumFeedbackScore' =>
				array(
					'required' => false,
					'type' => 'int',
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
	 * @return int
	 **/
	function getMaximumItemCount()
	{
		return $this->MaximumItemCount;
	}

	/**
	 * @return void
	 **/
	function setMaximumItemCount($value)
	{
		$this->MaximumItemCount = $value;
	}

	/**
	 * @return int
	 **/
	function getMinimumFeedbackScore()
	{
		return $this->MinimumFeedbackScore;
	}

	/**
	 * @return void
	 **/
	function setMinimumFeedbackScore($value)
	{
		$this->MinimumFeedbackScore = $value;
	}

}
?>
