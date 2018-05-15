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
 * MarketplaceWebServiceSellers_Model_GetServiceStatusResponse
 * 
 * Properties:
 * <ul>
 * 
 * <li>GetServiceStatusResult: MarketplaceWebServiceSellers_Model_GetServiceStatusResult</li>
 * <li>ResponseMetadata: MarketplaceWebServiceSellers_Model_ResponseMetadata</li>
 *
 * </ul>
 */ 
class MarketplaceWebServiceSellers_Model_GetServiceStatusResponse extends MarketplaceWebServiceSellers_Model
{

    /**
     * Construct new MarketplaceWebServiceSellers_Model_GetServiceStatusResponse
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>GetServiceStatusResult: MarketplaceWebServiceSellers_Model_GetServiceStatusResult</li>
     * <li>ResponseMetadata: MarketplaceWebServiceSellers_Model_ResponseMetadata</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (

        'GetServiceStatusResult' => array('FieldValue' => null, 'FieldType' => 'MarketplaceWebServiceSellers_Model_GetServiceStatusResult'),


        'ResponseMetadata' => array('FieldValue' => null, 'FieldType' => 'MarketplaceWebServiceSellers_Model_ResponseMetadata'),

        );
        parent::__construct($data);
    }

       
    /**
     * Construct MarketplaceWebServiceSellers_Model_GetServiceStatusResponse from XML string
     * 
     * @param string $xml XML string to construct from
     * @return MarketplaceWebServiceSellers_Model_GetServiceStatusResponse 
     */
    public static function fromXML($xml)
    {
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        $xpath = new DOMXPath($dom);
    	$xpath->registerNamespace('a', 'https://mws.amazonservices.com/Sellers/2011-07-01');
        $response = $xpath->query('//a:GetServiceStatusResponse');
        if ($response->length == 1) {
            return new MarketplaceWebServiceSellers_Model_GetServiceStatusResponse(($response->item(0))); 
        } else {
            throw new Exception ("Unable to construct MarketplaceWebServiceSellers_Model_GetServiceStatusResponse from provided XML. 
                                  Make sure that GetServiceStatusResponse is a root element");
        }
          
    }
    
    /**
     * Gets the value of the GetServiceStatusResult.
     * 
     * @return GetServiceStatusResult GetServiceStatusResult
     */
    public function getGetServiceStatusResult() 
    {
        return $this->_fields['GetServiceStatusResult']['FieldValue'];
    }

    /**
     * Sets the value of the GetServiceStatusResult.
     * 
     * @param GetServiceStatusResult GetServiceStatusResult
     * @return void
     */
    public function setGetServiceStatusResult($value) 
    {
        $this->_fields['GetServiceStatusResult']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the GetServiceStatusResult  and returns this instance
     * 
     * @param GetServiceStatusResult $value GetServiceStatusResult
     * @return MarketplaceWebServiceSellers_Model_GetServiceStatusResponse instance
     */
    public function withGetServiceStatusResult($value)
    {
        $this->setGetServiceStatusResult($value);
        return $this;
    }


    /**
     * Checks if GetServiceStatusResult  is set
     * 
     * @return bool true if GetServiceStatusResult property is set
     */
    public function isSetGetServiceStatusResult()
    {
        return !is_null($this->_fields['GetServiceStatusResult']['FieldValue']);

    }

    /**
     * Gets the value of the ResponseMetadata.
     * 
     * @return ResponseMetadata ResponseMetadata
     */
    public function getResponseMetadata() 
    {
        return $this->_fields['ResponseMetadata']['FieldValue'];
    }

    /**
     * Sets the value of the ResponseMetadata.
     * 
     * @param ResponseMetadata ResponseMetadata
     * @return void
     */
    public function setResponseMetadata($value) 
    {
        $this->_fields['ResponseMetadata']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the ResponseMetadata  and returns this instance
     * 
     * @param ResponseMetadata $value ResponseMetadata
     * @return MarketplaceWebServiceSellers_Model_GetServiceStatusResponse instance
     */
    public function withResponseMetadata($value)
    {
        $this->setResponseMetadata($value);
        return $this;
    }


    /**
     * Checks if ResponseMetadata  is set
     * 
     * @return bool true if ResponseMetadata property is set
     */
    public function isSetResponseMetadata()
    {
        return !is_null($this->_fields['ResponseMetadata']['FieldValue']);

    }



    /**
     * XML Representation for this object
     * 
     * @return string XML for this object
     */
    public function toXML() 
    {
        $xml = "";
        $xml .= "<GetServiceStatusResponse xmlns=\"https://mws.amazonservices.com/Sellers/2011-07-01\">";
        $xml .= $this->_toXMLFragment();
        $xml .= "</GetServiceStatusResponse>";
        return $xml;
    }

    private $_responseHeaderMetadata = null;

    public function getResponseHeaderMetadata() {
        return $this->_responseHeaderMetadata;
    }

    public function setResponseHeaderMetadata($responseHeaderMetadata) {
        return $this->_responseHeaderMetadata = $responseHeaderMetadata;
    }

}