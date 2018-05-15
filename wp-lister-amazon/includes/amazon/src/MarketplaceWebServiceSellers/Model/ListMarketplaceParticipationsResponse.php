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
 * MarketplaceWebServiceSellers_Model_ListMarketplaceParticipationsResponse
 * 
 * Properties:
 * <ul>
 * 
 * <li>ListMarketplaceParticipationsResult: MarketplaceWebServiceSellers_Model_ListMarketplaceParticipationsResult</li>
 * <li>ResponseMetadata: MarketplaceWebServiceSellers_Model_ResponseMetadata</li>
 *
 * </ul>
 */ 
class MarketplaceWebServiceSellers_Model_ListMarketplaceParticipationsResponse extends MarketplaceWebServiceSellers_Model
{

    /**
     * Construct new MarketplaceWebServiceSellers_Model_ListMarketplaceParticipationsResponse
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>ListMarketplaceParticipationsResult: MarketplaceWebServiceSellers_Model_ListMarketplaceParticipationsResult</li>
     * <li>ResponseMetadata: MarketplaceWebServiceSellers_Model_ResponseMetadata</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (

        'ListMarketplaceParticipationsResult' => array('FieldValue' => null, 'FieldType' => 'MarketplaceWebServiceSellers_Model_ListMarketplaceParticipationsResult'),


        'ResponseMetadata' => array('FieldValue' => null, 'FieldType' => 'MarketplaceWebServiceSellers_Model_ResponseMetadata'),

        );
        parent::__construct($data);
    }

       
    /**
     * Construct MarketplaceWebServiceSellers_Model_ListMarketplaceParticipationsResponse from XML string
     * 
     * @param string $xml XML string to construct from
     * @return MarketplaceWebServiceSellers_Model_ListMarketplaceParticipationsResponse 
     */
    public static function fromXML($xml)
    {
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        $xpath = new DOMXPath($dom);
    	$xpath->registerNamespace('a', 'https://mws.amazonservices.com/Sellers/2011-07-01');
        $response = $xpath->query('//a:ListMarketplaceParticipationsResponse');
        if ($response->length == 1) {
            return new MarketplaceWebServiceSellers_Model_ListMarketplaceParticipationsResponse(($response->item(0))); 
        } else {
            throw new Exception ("Unable to construct MarketplaceWebServiceSellers_Model_ListMarketplaceParticipationsResponse from provided XML. 
                                  Make sure that ListMarketplaceParticipationsResponse is a root element");
        }
          
    }
    
    /**
     * Gets the value of the ListMarketplaceParticipationsResult.
     * 
     * @return ListMarketplaceParticipationsResult ListMarketplaceParticipationsResult
     */
    public function getListMarketplaceParticipationsResult() 
    {
        return $this->_fields['ListMarketplaceParticipationsResult']['FieldValue'];
    }

    /**
     * Sets the value of the ListMarketplaceParticipationsResult.
     * 
     * @param ListMarketplaceParticipationsResult ListMarketplaceParticipationsResult
     * @return void
     */
    public function setListMarketplaceParticipationsResult($value) 
    {
        $this->_fields['ListMarketplaceParticipationsResult']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the ListMarketplaceParticipationsResult  and returns this instance
     * 
     * @param ListMarketplaceParticipationsResult $value ListMarketplaceParticipationsResult
     * @return MarketplaceWebServiceSellers_Model_ListMarketplaceParticipationsResponse instance
     */
    public function withListMarketplaceParticipationsResult($value)
    {
        $this->setListMarketplaceParticipationsResult($value);
        return $this;
    }


    /**
     * Checks if ListMarketplaceParticipationsResult  is set
     * 
     * @return bool true if ListMarketplaceParticipationsResult property is set
     */
    public function isSetListMarketplaceParticipationsResult()
    {
        return !is_null($this->_fields['ListMarketplaceParticipationsResult']['FieldValue']);

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
     * @return MarketplaceWebServiceSellers_Model_ListMarketplaceParticipationsResponse instance
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
        $xml .= "<ListMarketplaceParticipationsResponse xmlns=\"https://mws.amazonservices.com/Sellers/2011-07-01\">";
        $xml .= $this->_toXMLFragment();
        $xml .= "</ListMarketplaceParticipationsResponse>";
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