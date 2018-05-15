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
 * MarketplaceWebServiceSellers_Model_ListMarketplaceParticipationsByNextTokenResult
 * 
 * Properties:
 * <ul>
 * 
 * <li>NextToken: string</li>
 * <li>ListParticipations: MarketplaceWebServiceSellers_Model_ListParticipations</li>
 * <li>ListMarketplaces: MarketplaceWebServiceSellers_Model_ListMarketplaces</li>
 *
 * </ul>
 */ 
class MarketplaceWebServiceSellers_Model_ListMarketplaceParticipationsByNextTokenResult extends MarketplaceWebServiceSellers_Model
{

    /**
     * Construct new MarketplaceWebServiceSellers_Model_ListMarketplaceParticipationsByNextTokenResult
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>NextToken: string</li>
     * <li>ListParticipations: MarketplaceWebServiceSellers_Model_ListParticipations</li>
     * <li>ListMarketplaces: MarketplaceWebServiceSellers_Model_ListMarketplaces</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'NextToken' => array('FieldValue' => null, 'FieldType' => 'string'),

        'ListParticipations' => array('FieldValue' => null, 'FieldType' => 'MarketplaceWebServiceSellers_Model_ListParticipations'),


        'ListMarketplaces' => array('FieldValue' => null, 'FieldType' => 'MarketplaceWebServiceSellers_Model_ListMarketplaces'),

        );
        parent::__construct($data);
    }

        /**
     * Gets the value of the NextToken property.
     * 
     * @return string NextToken
     */
    public function getNextToken() 
    {
        return $this->_fields['NextToken']['FieldValue'];
    }

    /**
     * Sets the value of the NextToken property.
     * 
     * @param string NextToken
     * @return this instance
     */
    public function setNextToken($value) 
    {
        $this->_fields['NextToken']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the NextToken and returns this instance
     * 
     * @param string $value NextToken
     * @return MarketplaceWebServiceSellers_Model_ListMarketplaceParticipationsByNextTokenResult instance
     */
    public function withNextToken($value)
    {
        $this->setNextToken($value);
        return $this;
    }


    /**
     * Checks if NextToken is set
     * 
     * @return bool true if NextToken  is set
     */
    public function isSetNextToken()
    {
        return !is_null($this->_fields['NextToken']['FieldValue']);
    }

    /**
     * Gets the value of the ListParticipations.
     * 
     * @return ListParticipations ListParticipations
     */
    public function getListParticipations() 
    {
        return $this->_fields['ListParticipations']['FieldValue'];
    }

    /**
     * Sets the value of the ListParticipations.
     * 
     * @param ListParticipations ListParticipations
     * @return void
     */
    public function setListParticipations($value) 
    {
        $this->_fields['ListParticipations']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the ListParticipations  and returns this instance
     * 
     * @param ListParticipations $value ListParticipations
     * @return MarketplaceWebServiceSellers_Model_ListMarketplaceParticipationsByNextTokenResult instance
     */
    public function withListParticipations($value)
    {
        $this->setListParticipations($value);
        return $this;
    }


    /**
     * Checks if ListParticipations  is set
     * 
     * @return bool true if ListParticipations property is set
     */
    public function isSetListParticipations()
    {
        return !is_null($this->_fields['ListParticipations']['FieldValue']);

    }

    /**
     * Gets the value of the ListMarketplaces.
     * 
     * @return ListMarketplaces ListMarketplaces
     */
    public function getListMarketplaces() 
    {
        return $this->_fields['ListMarketplaces']['FieldValue'];
    }

    /**
     * Sets the value of the ListMarketplaces.
     * 
     * @param ListMarketplaces ListMarketplaces
     * @return void
     */
    public function setListMarketplaces($value) 
    {
        $this->_fields['ListMarketplaces']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the ListMarketplaces  and returns this instance
     * 
     * @param ListMarketplaces $value ListMarketplaces
     * @return MarketplaceWebServiceSellers_Model_ListMarketplaceParticipationsByNextTokenResult instance
     */
    public function withListMarketplaces($value)
    {
        $this->setListMarketplaces($value);
        return $this;
    }


    /**
     * Checks if ListMarketplaces  is set
     * 
     * @return bool true if ListMarketplaces property is set
     */
    public function isSetListMarketplaces()
    {
        return !is_null($this->_fields['ListMarketplaces']['FieldValue']);

    }




}