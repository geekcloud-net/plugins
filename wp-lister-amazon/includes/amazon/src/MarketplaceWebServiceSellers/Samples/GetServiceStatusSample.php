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
 * Get Service Status  Sample
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
 * sample for Get Service Status Action
 ***********************************************************************/
 // @TODO: set request. Action can be passed as MarketplaceWebServiceSellers_Model_GetServiceStatusRequest
 $request = new MarketplaceWebServiceSellers_Model_GetServiceStatusRequest();
 $request->setSellerId(MERCHANT_ID);
 // object or array of parameters
 invokeGetServiceStatus($service, $request);

                                
/**
  * Get Service Status Action Sample
  * Returns the service status of a particular MWS API section. The operation
  * takes no input. All API sections within the API are required to implement this operation.
  *   
  * @param MarketplaceWebServiceSellers_Interface $service instance of MarketplaceWebServiceSellers_Interface
  * @param mixed $request MarketplaceWebServiceSellers_Model_GetServiceStatus or array of parameters
  */
  function invokeGetServiceStatus(MarketplaceWebServiceSellers_Interface $service, $request) 
  {
      try {
              $response = $service->getServiceStatus($request);
              
                echo ("Service Response\n");
                echo ("=============================================================================\n");

                echo("        GetServiceStatusResponse\n");
                if ($response->isSetGetServiceStatusResult()) { 
                    echo("            GetServiceStatusResult\n");
                    $getServiceStatusResult = $response->getGetServiceStatusResult();
                    if ($getServiceStatusResult->isSetStatus()) 
                    {
                        echo("                Status\n");
                        echo("                    " . $getServiceStatusResult->getStatus() . "\n");
                    }
                    if ($getServiceStatusResult->isSetTimestamp()) 
                    {
                        echo("                Timestamp\n");
                        echo("                    " . $getServiceStatusResult->getTimestamp() . "\n");
                    }
                    if ($getServiceStatusResult->isSetMessageId()) 
                    {
                        echo("                MessageId\n");
                        echo("                    " . $getServiceStatusResult->getMessageId() . "\n");
                    }
                    if ($getServiceStatusResult->isSetMessages()) { 
                        echo("                Messages\n");
                        $messages = $getServiceStatusResult->getMessages();
                        $messageList = $messages->getMessage();
                        foreach ($messageList as $message) {
                            echo("                    Message\n");
                            if ($message->isSetLocale()) 
                            {
                                echo("                        Locale\n");
                                echo("                            " . $message->getLocale() . "\n");
                            }
                            if ($message->isSetText()) 
                            {
                                echo("                        Text\n");
                                echo("                            " . $message->getText() . "\n");
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
    