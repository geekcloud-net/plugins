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
 * MarketplaceWebServiceSellers_Model_Marketplace
 * 
 * Properties:
 * <ul>
 * 
 * <li>MarketplaceId: string</li>
 * <li>Name: string</li>
 * <li>DefaultLanguageCode: string</li>
 * <li>DefaultCountryCode: string</li>
 * <li>DefaultCurrencyCode: string</li>
 * <li>DomainName: string</li>
 *
 * </ul>
 */ 
class MarketplaceWebServiceSellers_Model_Marketplace extends MarketplaceWebServiceSellers_Model
{

    /**
     * Construct new MarketplaceWebServiceSellers_Model_Marketplace
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>MarketplaceId: string</li>
     * <li>Name: string</li>
     * <li>DefaultLanguageCode: string</li>
     * <li>DefaultCountryCode: string</li>
     * <li>DefaultCurrencyCode: string</li>
     * <li>DomainName: string</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'MarketplaceId' => array('FieldValue' => null, 'FieldType' => 'string'),
        'Name' => array('FieldValue' => null, 'FieldType' => 'string'),
        'DefaultLanguageCode' => array('FieldValue' => null, 'FieldType' => 'string'),
        'DefaultCountryCode' => array('FieldValue' => null, 'FieldType' => 'string'),
        'DefaultCurrencyCode' => array('FieldValue' => null, 'FieldType' => 'string'),
        'DomainName' => array('FieldValue' => null, 'FieldType' => 'string'),
        );
        parent::__construct($data);
    }

        /**
     * Gets the value of the MarketplaceId property.
     * 
     * @return string MarketplaceId
     */
    public function getMarketplaceId() 
    {
        return $this->_fields['MarketplaceId']['FieldValue'];
    }

    /**
     * Sets the value of the MarketplaceId property.
     * 
     * @param string MarketplaceId
     * @return this instance
     */
    public function setMarketplaceId($value) 
    {
        $this->_fields['MarketplaceId']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the MarketplaceId and returns this instance
     * 
     * @param string $value MarketplaceId
     * @return MarketplaceWebServiceSellers_Model_Marketplace instance
     */
    public function withMarketplaceId($value)
    {
        $this->setMarketplaceId($value);
        return $this;
    }


    /**
     * Checks if MarketplaceId is set
     * 
     * @return bool true if MarketplaceId  is set
     */
    public function isSetMarketplaceId()
    {
        return !is_null($this->_fields['MarketplaceId']['FieldValue']);
    }

    /**
     * Gets the value of the Name property.
     * 
     * @return string Name
     */
    public function getName() 
    {
        return $this->_fields['Name']['FieldValue'];
    }

    /**
     * Sets the value of the Name property.
     * 
     * @param string Name
     * @return this instance
     */
    public function setName($value) 
    {
        $this->_fields['Name']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the Name and returns this instance
     * 
     * @param string $value Name
     * @return MarketplaceWebServiceSellers_Model_Marketplace instance
     */
    public function withName($value)
    {
        $this->setName($value);
        return $this;
    }


    /**
     * Checks if Name is set
     * 
     * @return bool true if Name  is set
     */
    public function isSetName()
    {
        return !is_null($this->_fields['Name']['FieldValue']);
    }

    /**
     * Gets the value of the DefaultLanguageCode property.
     * 
     * @return string DefaultLanguageCode
     */
    public function getDefaultLanguageCode() 
    {
        return $this->_fields['DefaultLanguageCode']['FieldValue'];
    }

    /**
     * Sets the value of the DefaultLanguageCode property.
     * 
     * @param string DefaultLanguageCode
     * @return this instance
     */
    public function setDefaultLanguageCode($value) 
    {
        $this->_fields['DefaultLanguageCode']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the DefaultLanguageCode and returns this instance
     * 
     * @param string $value DefaultLanguageCode
     * @return MarketplaceWebServiceSellers_Model_Marketplace instance
     */
    public function withDefaultLanguageCode($value)
    {
        $this->setDefaultLanguageCode($value);
        return $this;
    }


    /**
     * Checks if DefaultLanguageCode is set
     * 
     * @return bool true if DefaultLanguageCode  is set
     */
    public function isSetDefaultLanguageCode()
    {
        return !is_null($this->_fields['DefaultLanguageCode']['FieldValue']);
    }

    /**
     * Gets the value of the DefaultCountryCode property.
     * 
     * @return string DefaultCountryCode
     */
    public function getDefaultCountryCode() 
    {
        return $this->_fields['DefaultCountryCode']['FieldValue'];
    }

    /**
     * Sets the value of the DefaultCountryCode property.
     * 
     * @param string DefaultCountryCode
     * @return this instance
     */
    public function setDefaultCountryCode($value) 
    {
        $this->_fields['DefaultCountryCode']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the DefaultCountryCode and returns this instance
     * 
     * @param string $value DefaultCountryCode
     * @return MarketplaceWebServiceSellers_Model_Marketplace instance
     */
    public function withDefaultCountryCode($value)
    {
        $this->setDefaultCountryCode($value);
        return $this;
    }


    /**
     * Checks if DefaultCountryCode is set
     * 
     * @return bool true if DefaultCountryCode  is set
     */
    public function isSetDefaultCountryCode()
    {
        return !is_null($this->_fields['DefaultCountryCode']['FieldValue']);
    }

    /**
     * Gets the value of the DefaultCurrencyCode property.
     * 
     * @return string DefaultCurrencyCode
     */
    public function getDefaultCurrencyCode() 
    {
        return $this->_fields['DefaultCurrencyCode']['FieldValue'];
    }

    /**
     * Sets the value of the DefaultCurrencyCode property.
     * 
     * @param string DefaultCurrencyCode
     * @return this instance
     */
    public function setDefaultCurrencyCode($value) 
    {
        $this->_fields['DefaultCurrencyCode']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the DefaultCurrencyCode and returns this instance
     * 
     * @param string $value DefaultCurrencyCode
     * @return MarketplaceWebServiceSellers_Model_Marketplace instance
     */
    public function withDefaultCurrencyCode($value)
    {
        $this->setDefaultCurrencyCode($value);
        return $this;
    }


    /**
     * Checks if DefaultCurrencyCode is set
     * 
     * @return bool true if DefaultCurrencyCode  is set
     */
    public function isSetDefaultCurrencyCode()
    {
        return !is_null($this->_fields['DefaultCurrencyCode']['FieldValue']);
    }

    /**
     * Gets the value of the DomainName property.
     * 
     * @return string DomainName
     */
    public function getDomainName() 
    {
        return $this->_fields['DomainName']['FieldValue'];
    }

    /**
     * Sets the value of the DomainName property.
     * 
     * @param string DomainName
     * @return this instance
     */
    public function setDomainName($value) 
    {
        $this->_fields['DomainName']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the DomainName and returns this instance
     * 
     * @param string $value DomainName
     * @return MarketplaceWebServiceSellers_Model_Marketplace instance
     */
    public function withDomainName($value)
    {
        $this->setDomainName($value);
        return $this;
    }


    /**
     * Checks if DomainName is set
     * 
     * @return bool true if DomainName  is set
     */
    public function isSetDomainName()
    {
        return !is_null($this->_fields['DomainName']['FieldValue']);
    }




}