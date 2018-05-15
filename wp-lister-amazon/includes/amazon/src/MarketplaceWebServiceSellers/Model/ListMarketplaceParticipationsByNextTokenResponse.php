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
 * MarketplaceWebServiceSellers_Model_ListMarketplaceParticipationsByNextTokenResponse
 * 
 * Properties:
 * <ul>
 * 
 * <li>ListMarketplaceParticipationsByNextTokenResult: MarketplaceWebServiceSellers_Model_ListMarketplaceParticipationsByNextTokenResult</li>
 * <li>ResponseMetadata: MarketplaceWebServiceSellers_Model_ResponseMetadata</li>
 *
 * </ul>
 */ 
class MarketplaceWebServiceSellers_Model_ListMarketplaceParticipationsByNextTokenResponse extends MarketplaceWebServiceSellers_Model
{

    /**
     * Construct new MarketplaceWebServiceSellers_Model_ListMarketplaceParticipationsByNextTokenResponse
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>ListMarketplaceParticipationsByNextTokenResult: MarketplaceWebServiceSellers_Model_ListMarketplaceParticipationsByNextTokenResult</li>
     * <li>ResponseMetadata: MarketplaceWebServiceSellers_Model_ResponseMetadata</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (

        'ListMarketplaceParticipationsByNextTokenResult' => array('FieldValue' => null, 'FieldType' => 'MarketplaceWebServiceSellers_Model_ListMarketplaceParticipationsByNextTokenResult'),


        'ResponseMetadata' => array('FieldValue' => null, 'FieldType' => 'MarketplaceWebServiceSellers_Model_ResponseMetadata'),

        );
        parent::__construct($data);
    }

       
    /**
     * Construct MarketplaceWebServiceSellers_Model_ListMarketplaceParticipationsByNextTokenResponse from XML string
     * 
     * @param string $xml XML string to construct from
     * @return MarketplaceWebServiceSellers_Model_ListMarketplaceParticipationsByNextTokenResponse 
     */
    public static function fromXML($xml)
    {
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        $xpath = new DOMXPath($dom);
    	$xpath->registerNamespace('a', 'https://mws.amazonservices.com/Sellers/2011-07-01');
        $response = $xpath->query('//a:ListMarketplaceParticipationsByNextTokenResponse');
        if ($response->length == 1) {
            return new MarketplaceWebServiceSellers_Model_ListMarketplaceParticipationsByNextTokenResponse(($response->item(0))); 
        } else {
            throw new Exception ("Unable to construct MarketplaceWebServiceSellers_Model_ListMarketplaceParticipationsByNextTokenResponse from provided XML. 
                                  Make sure that ListMarketplaceParticipationsByNextTokenResponse is a root element");
        }
          
    }
    
    /**
     * Gets the value of the ListMarketplaceParticipationsByNextTokenResult.
     * 
     * @return ListMarketplaceParticipationsByNextTokenResult ListMarketplaceParticipationsByNextTokenResult
     */
    public function getListMarketplaceParticipationsByNextTokenResult() 
    {
        return $this->_fields['ListMarketplaceParticipationsByNextTokenResult']['FieldValue'];
    }

    /**
     * Sets the value of the ListMarketplaceParticipationsByNextTokenResult.
     * 
     * @param ListMarketplaceParticipationsByNextTokenResult ListMarketplaceParticipationsByNextTokenResult
     * @return void
     */
    public function setListMarketplaceParticipationsByNextTokenResult($value) 
    {
        $this->_fields['ListMarketplaceParticipationsByNextTokenResult']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the ListMarketplaceParticipationsByNextTokenResult  and returns this instance
     * 
     * @param ListMarketplaceParticipationsByNextTokenResult $value ListMarketplaceParticipationsByNextTokenResult
     * @return MarketplaceWebServiceSellers_Model_ListMarketplaceParticipationsByNextTokenResponse instance
     */
    public function withListMarketplaceParticipationsByNextTokenResult($value)
    {
        $this->setListMarketplaceParticipationsByNextTokenResult($value);
        return $this;
    }


    /**
     * Checks if ListMarketplaceParticipationsByNextTokenResult  is set
     * 
     * @return bool true if ListMarketplaceParticipationsByNextTokenResult property is set
     */
    public function isSetListMarketplaceParticipationsByNextTokenResult()
    {
        return !is_null($this->_fields['ListMarketplaceParticipationsByNextTokenResult']['FieldValue']);

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
     * @return MarketplaceWebServiceSellers_Model_ListMarketplaceParticipationsByNextTokenResponse instance
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
        $xml .= "<ListMarketplaceParticipationsByNextTokenResponse xmlns=\"https://mws.amazonservices.com/Sellers/2011-07-01\">";
        $xml .= $this->_toXMLFragment();
        $xml .= "</ListMarketplaceParticipationsByNextTokenResponse>";
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