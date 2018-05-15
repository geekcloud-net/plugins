<?php
/** 
 *  PHP Version 5
 *
 *  @category    Amazon
 *  @package     MarketplaceWebServiceSellers
 *  @copyright   Copyright 2008-2012 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *  @link        http://aws.amazon.com
 *  @license     http://aws.amazon.com/apache2.0  Apache License, Version 2.0
 *  @version     2011-07-01
 */
/******************************************************************************* 
 * 
 *  Marketplace Web Service Sellers PHP5 Library
 * 
 */

/**
 *  @see MarketplaceWebServiceSellers_Model
 */
require_once ('MarketplaceWebServiceSellers/Model.php');  

    

/**
 * MarketplaceWebServiceSellers_Model_ListMarketplaces
 * 
 * Properties:
 * <ul>
 * 
 * <li>Marketplace: MarketplaceWebServiceSellers_Model_Marketplace</li>
 *
 * </ul>
 */ 
class MarketplaceWebServiceSellers_Model_ListMarketplaces extends MarketplaceWebServiceSellers_Model
{

    /**
     * Construct new MarketplaceWebServiceSellers_Model_ListMarketplaces
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>Marketplace: MarketplaceWebServiceSellers_Model_Marketplace</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'Marketplace' => array('FieldValue' => array(), 'FieldType' => array('MarketplaceWebServiceSellers_Model_Marketplace')),
        );
        parent::__construct($data);
    }

        /**
     * Gets the value of the Marketplace.
     * 
     * @return array of Marketplace Marketplace
     */
    public function getMarketplace() 
    {
        return $this->_fields['Marketplace']['FieldValue'];
    }

    /**
     * Sets the value of the Marketplace.
     * 
     * @param mixed Marketplace or an array of Marketplace Marketplace
     * @return this instance
     */
    public function setMarketplace($marketplace) 
    {
        if (!$this->_isNumericArray($marketplace)) {
            $marketplace =  array ($marketplace);    
        }
        $this->_fields['Marketplace']['FieldValue'] = $marketplace;
        return $this;
    }


    /**
     * Sets single or multiple values of Marketplace list via variable number of arguments. 
     * For example, to set the list with two elements, simply pass two values as arguments to this function
     * <code>withMarketplace($marketplace1, $marketplace2)</code>
     * 
     * @param Marketplace  $marketplaceArgs one or more Marketplace
     * @return MarketplaceWebServiceSellers_Model_ListMarketplaces  instance
     */
    public function withMarketplace($marketplaceArgs)
    {
        foreach (func_get_args() as $marketplace) {
            $this->_fields['Marketplace']['FieldValue'][] = $marketplace;
        }
        return $this;
    }   



    /**
     * Checks if Marketplace list is non-empty
     * 
     * @return bool true if Marketplace list is non-empty
     */
    public function isSetMarketplace()
    {
        return count ($this->_fields['Marketplace']['FieldValue']) > 0;
    }




}