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
 * List Marketplace Participations  Sample
 */

include_once ('.config.inc.php'); 

/************************************************************************
 * Instantiate Implementation of MarketplaceWebServiceSellers
 * 
 * AWS_ACCESS_KEY_ID and AWS_SECRET_ACCESS_KEY constants 
 * are defined in the .config.inc.php located in the same 
 * directory as this sample
 ***********************************************************************/
// United States:
//$serviceUrl = "https://mws.amazonservices.com/Sellers/2011-07-01";
// Europe
//$serviceUrl = "https://mws-eu.amazonservices.com/Sellers/2011-07-01";
// Japan
//$serviceUrl = "https://mws.amazonservices.jp/Sellers/2011-07-01";
// China
//$serviceUrl = "https://mws.amazonservices.com.cn/Sellers/2011-07-01";
// Canada
//$serviceUrl = "https://mws.amazonservices.ca/Sellers/2011-07-01";

 $config = array (
   'ServiceURL' => $serviceUrl,
   'ProxyHost' => null,
   'ProxyPort' => -1,
   'MaxErrorRetry' => 3,
 );

 $service = new MarketplaceWebServiceSellers_Client(
        AWS_ACCESS_KEY_ID,
        AWS_SECRET_ACCESS_KEY,
        APPLICATION_NAME,
        APPLICATION_VERSION,
        $config);
 
 
 
/************************************************************************
 * Uncomment to try out Mock Service that simulates MarketplaceWebServiceSellers
 * responses without calling MarketplaceWebServiceSellers service.
 *
 * Responses are loaded from local XML files. You can tweak XML files to
 * experiment with various outputs during development
 *
 * XML files available under MarketplaceWebServiceSellers/Mock tree
 *
 ***********************************************************************/
 // $service = new MarketplaceWebServiceSellers_Mock();

/************************************************************************
 * Setup request parameters and uncomment invoke to try out 
 * sample for List Marketplace Participations Action
 ***********************************************************************/
 // @TODO: set request. Action can be passed as MarketplaceWebServiceSellers_Model_ListMarketplaceParticipationsRequest
 $request = new MarketplaceWebServiceSellers_Model_ListMarketplaceParticipationsRequest();
 $request->setSellerId(MERCHANT_ID);
 // object or array of parameters
 invokeListMarketplaceParticipations($service, $request);

                        
/**
  * List Marketplace Participations Action Sample
  * This operation can be used to list all Marketplaces that a seller can sell in.
  * The operation returns a List of Participation elements and a List of Marketplace
  * elements. The SellerId is the only parameter required by this operation.
  *   
  * @param MarketplaceWebServiceSellers_Interface $service instance of MarketplaceWebServiceSellers_Interface
  * @param mixed $request MarketplaceWebServiceSellers_Model_ListMarketplaceParticipations or array of parameters
  */
  function invokeListMarketplaceParticipations(MarketplaceWebServiceSellers_Interface $service, $request) 
  {
      try {
              $response = $service->listMarketplaceParticipations($request);
              
                echo ("Service Response\n");
                echo ("=============================================================================\n");

                echo("        ListMarketplaceParticipationsResponse\n");
                if ($response->isSetListMarketplaceParticipationsResult()) { 
                    echo("            ListMarketplaceParticipationsResult\n");
                    $listMarketplaceParticipationsResult = $response->getListMarketplaceParticipationsResult();
                    if ($listMarketplaceParticipationsResult->isSetNextToken()) 
                    {
                        echo("                NextToken\n");
                        echo("                    " . $listMarketplaceParticipationsResult->getNextToken() . "\n");
                    }
                    if ($listMarketplaceParticipationsResult->isSetListParticipations()) { 
                        echo("                ListParticipations\n");
                        $listParticipations = $listMarketplaceParticipationsResult->getListParticipations();
                        $participationList = $listParticipations->getParticipation();
                        foreach ($participationList as $participation) {
                            echo("                    Participation\n");
                            if ($participation->isSetMarketplaceId()) 
                            {
                                echo("                        MarketplaceId\n");
                                echo("                            " . $participation->getMarketplaceId() . "\n");
                            }
                            if ($participation->isSetSellerId()) 
                            {
                                echo("                        SellerId\n");
                                echo("                            " . $participation->getSellerId() . "\n");
                            }
                            if ($participation->isSetHasSellerSuspendedListings()) 
                            {
                                echo("                        HasSellerSuspendedListings\n");
                                echo("                            " . $participation->getHasSellerSuspendedListings() . "\n");
                            }
                        }
                    } 
                    if ($listMarketplaceParticipationsResult->isSetListMarketplaces()) { 
                        echo("                ListMarketplaces\n");
                        $listMarketplaces = $listMarketplaceParticipationsResult->getListMarketplaces();
                        $marketplaceList = $listMarketplaces->getMarketplace();
                        foreach ($marketplaceList as $marketplace) {
                            echo("                    Marketplace\n");
                            if ($marketplace->isSetMarketplaceId()) 
                            {
                                echo("                        MarketplaceId\n");
                                echo("                            " . $marketplace->getMarketplaceId() . "\n");
                            }
                            if ($marketplace->isSetName()) 
                            {
                                echo("                        Name\n");
                                echo("                            " . $marketplace->getName() . "\n");
                            }
                            if ($marketplace->isSetDefaultLanguageCode()) 
                            {
                                echo("                        DefaultLanguageCode\n");
                                echo("                            " . $marketplace->getDefaultLanguageCode() . "\n");
                            }
                            if ($marketplace->isSetDefaultCountryCode()) 
                            {
                                echo("                        DefaultCountryCode\n");
                                echo("                            " . $marketplace->getDefaultCountryCode() . "\n");
                            }
                            if ($marketplace->isSetDefaultCurrencyCode()) 
                            {
                                echo("                        DefaultCurrencyCode\n");
                                echo("                            " . $marketplace->getDefaultCurrencyCode() . "\n");
                            }
                            if ($marketplace->isSetDomainName()) 
                            {
                                echo("                        DomainName\n");
                                echo("                            " . $marketplace->getDomainName() . "\n");
                            }
                        }
                    } 
                } 
                if ($response->isSetResponseMetadata()) { 
                    echo("            ResponseMetadata\n");
                    $responseMetadata = $response->getResponseMetadata();
                    if ($responseMetadata->isSetRequestId()) 
                    {
                        echo("                RequestId\n");
                        echo("                    " . $responseMetadata->getRequestId() . "\n");
                    }
                } 

              echo("            ResponseHeaderMetadata: " . $response->getResponseHeaderMetadata() . "\n");
     } catch (MarketplaceWebServiceSellers_Exception $ex) {
         echo("Caught Exception: " . $ex->getMessage() . "\n");
         echo("Response Status Code: " . $ex->getStatusCode() . "\n");
         echo("Error Code: " . $ex->getErrorCode() . "\n");
         echo("Error Type: " . $ex->getErrorType() . "\n");
         echo("Request ID: " . $ex->getRequestId() . "\n");
         echo("XML: " . $ex->getXML() . "\n");
         echo("ResponseHeaderMetadata: " . $ex->getResponseHeaderMetadata() . "\n");
     }
 }
            