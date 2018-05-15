<?php
/* Generated on 14.02.18 14:28 by globalsync
 * $Id: $
 * $Log: $
 */

require_once 'AbstractResponseType.php';
require_once 'SuggestedCategoryArrayType.php';

/**
  * The base response of a <b>GetSuggestedCategories</b> call. This response includes an array of categories with the highest number
  * of listings whose listing titles or descriptions contain the keywords
  * specified in the <b>Query</b> field of the call request.
  *     
 **/

class GetSuggestedCategoriesResponseType extends AbstractResponseType
{
	/**
	* @var SuggestedCategoryArrayType
	**/
	protected $SuggestedCategoryArray;

	/**
	* @var int
	**/
	protected $CategoryCount;


	/**
	 * Class Constructor 
	 **/
	function __construct()
	{
		parent::__construct('GetSuggestedCategoriesResponseType', 'urn:ebay:apis:eBLBaseComponents');
		if (!isset(self::$_elements[__CLASS__]))
		{
			self::$_elements[__CLASS__] = array_merge(self::$_elements[get_parent_class()],
			array(
				'SuggestedCategoryArray' =>
				array(
					'required' => false,
					'type' => 'SuggestedCategoryArrayType',
					'nsURI' => 'urn:ebay:apis:eBLBaseComponents',
					'array' => false,
					'cardinality' => '0..1'
				),
				'CategoryCount' =>
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
	 * @return SuggestedCategoryArrayType
	 **/
	function getSuggestedCategoryArray()
	{
		return $this->SuggestedCategoryArray;
	}

	/**
	 * @return void
	 **/
	function setSuggestedCategoryArray($value)
	{
		$this->SuggestedCategoryArray = $value;
	}

	/**
	 * @return int
	 **/
	function getCategoryCount()
	{
		return $this->CategoryCount;
	}

	/**
	 * @return void
	 **/
	function setCategoryCount($value)
	{
		$this->CategoryCount = $value;
	}

}
?>
