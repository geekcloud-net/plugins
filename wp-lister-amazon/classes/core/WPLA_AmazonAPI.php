<?php

class WPLA_AmazonAPI {

    var $account_id;
    var $market_id;
    var $api_host;

    var $SellerId;
    var $MarketplaceId;
    var $AccessKey;
    var $SecretKey;
    var $allowed_marketplace_ids;

    var $dblogger;
    var $service;
	
	public function __construct( $account_id = false ) {

        if ( $account_id ) {

            $this->account = new WPLA_AmazonAccount( $account_id );

            $this->account_id = $account_id;
            $this->market_id  = $this->account->market_id;
            $this->api_host   = str_replace( 'amazon', 'mws.amazonservices', $this->account->getMarket()->url );
            $this->api_host   = str_replace( 'mws.amazonservices.co.jp', 'mws.amazonservices.jp', $this->api_host ); // fix MWS JP URL

            $this->setMerchantId( $this->account->merchant_id );
            $this->setMarketplaceId( $this->account->marketplace_id );
            $this->setAccessKeyId( $this->account->access_key_id );
            $this->setSecretKey( $this->account->secret_key );
            $this->setAllowedMarkets( $this->account->allowed_markets );

        }

        $this->dblogger = new WPLA_AmazonLogger();

    }

    // init MWS API
    public function initAPI( $section = false ) {
        WPLA()->logger->debug('initAPI() '.$section);

        // set up environment
        self::fixMissingIconv();
        if ( ! defined('DATE_FORMAT') ) define('DATE_FORMAT', 'Y-m-d H:i:s');

        // set up include_path
        $success = set_include_path(get_include_path() . PATH_SEPARATOR . WPLA_PATH . '/includes/amazon/src');     
        if ( $success === false && ( strpos( get_include_path(), 'amazon/src' ) === false ) ) {
            WPLA()->logger->error('This server does not support set_include_path()' );

            // display error message
            wpla_show_message('<b>Serious Error:</b> This server does not support <i>set_include_path()</i>, which is required by WP-Lister to communicate with Amazon.<br><br>You need to contact your hosting provider to have this fixed - or switch to a better hoster!', 'error');
            do_action('wpla_admin_notices');

            // log to db
            $this->dblogger->updateLog( array(
                'callname'    => 'Serious Error: This server does not support set_include_path()',
                'response'    => "Serious Error: This server does not support set_include_path(),\nwhich is required by WP-Lister to communicate with Amazon.\n\nYou need to contact your hosting provider to have this fixed - or switch to a better hoster!",
                'request'     => get_include_path() . PATH_SEPARATOR . WPLA_PATH . '/includes/amazon/src',
                'result'      => get_include_path(),
                'http_code'   => 500,
                'success'     => 'Error'
            ));
        }

        // use autoloader to load AmazonAPI classes
        spl_autoload_register('WPLA_AmazonAPI::autoloadAmazonClasses');

        // configure api
        $config = array (
          'ServiceURL' => 'https://' . $this->api_host, // https://mws.amazonservices.com
          'ProxyHost' => null,
          'ProxyPort' => -1,
          'MaxErrorRetry' => 3,
        );

        // Instantiate Implementation of MarketplaceWebService
        if ( 'Products' == $section ) {
            
            $config['ServiceURL'] .= '/Products/2011-10-01';

            $service = new MarketplaceWebServiceProducts_Client(
                $this->AccessKey, 
                $this->SecretKey, 
                'WP-Lister for Amazon',
                WPLA_VERSION,
                $config
            );        

        } elseif ( 'Orders' == $section ) {
            
            $config['ServiceURL'] .= '/Orders/2013-09-01';

            $service = new MarketplaceWebServiceOrders_Client(
                $this->AccessKey, 
                $this->SecretKey, 
                'WP-Lister for Amazon',
                WPLA_VERSION,
                $config
            );        

        } elseif ( 'Sellers' == $section ) {
            
            $config['ServiceURL'] .= '/Sellers/2011-07-01';

            $service = new MarketplaceWebServiceSellers_Client(
                $this->AccessKey, 
                $this->SecretKey, 
                'WP-Lister for Amazon',
                WPLA_VERSION,
                $config
            );        

        } elseif ( 'FBAOutbound' == $section ) {
            
            $config['ServiceURL'] .= '/FulfillmentOutboundShipment/2010-10-01';

            $service = new FBAOutboundServiceMWS_Client(
                $this->AccessKey, 
                $this->SecretKey, 
                $config,
                'WP-Lister for Amazon',
                WPLA_VERSION
            );        

        } else {
            
            $service = new MarketplaceWebService_Client(
                $this->AccessKey, 
                $this->SecretKey, 
                $config,
                'WP-Lister for Amazon',
                WPLA_VERSION
            );        
        }

        // make dblogger available in MarketplaceWebService_Client
        $service->dblogger   = $this->dblogger;  
        $service->account_id = $this->account_id;
        $service->market_id  = $this->market_id;

        // Uncomment to try out Mock Service that simulates MarketplaceWebService
        // responses without calling MarketplaceWebService service.
        // $service = new MarketplaceWebService_Mock();

        $this->service = $service;

    }

    // auto load API classes
    static function autoloadAmazonClasses($className){
        $open_basedir = ini_get( 'open_basedir' );
        if ( empty( $open_basedir ) ) {
            $includePaths = explode(PATH_SEPARATOR, get_include_path());
        } else {
            $includePaths = array( WPLA_PATH . '/includes/amazon/src' );
        }

        $filePath = str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

        foreach($includePaths as $includePath){
            if(file_exists($includePath . DIRECTORY_SEPARATOR . $filePath)){
                require_once $filePath;
                return;
            }
        }
    }

    // fix missing iconv extension
    static function fixMissingIconv(){

        // if ( ! function_exists('iconv_set_encoding') ) {
        //     // prevent fatal error in Client.php when iconv extension is not loaded
        //     function iconv_set_encoding( $encoding ) {
        //         @ini_set('default_charset', $encoding);
        //     }
        // }

        // set charset here - instead of multiple Client.php files
        self::setDefaultEncoding('UTF-8');

    } // fixMissingIconv()

    // set charset in a way that's compatible with PHP5.6
    static function setDefaultEncoding($enc) {

        if ( ( PHP_VERSION_ID < 50600 ) && function_exists('iconv_set_encoding') ) {
            iconv_set_encoding('input_encoding',    $enc);
            iconv_set_encoding('output_encoding',   $enc);
            iconv_set_encoding('internal_encoding', $enc);
        } else {
            ini_set('default_charset', $enc);
        }

    } // setDefaultEncoding()



    // GetFulfillmentPreview (V2)
    //
    // usage example:
    //
    // $products = array(
    //     array(
    //         'sku' => '887717364485',
    //         'qty' => 1
    //     )
    // );
    //
    // $address = array(
    //     'name'     => 'Test Customer',
    //     'street'   => 'Test Street 1',
    //     'city'     => 'New York',
    //     'postcode' => '10001',
    //     'state'    => 'NY',
    //     'country'  => 'US'
    // );
    //
    // $api = new WPLA_AmazonAPI();
    // $result = $api->getFulfillmentPreview( $products, $address );
    // echo "<pre>";print_r($result);echo"</pre>";

    public function getFulfillmentPreview( $products, $shipping_address ) {
        $this->initAPI('FBAOutbound');

        $request = new FBAOutboundServiceMWS_Model_GetFulfillmentPreviewRequest();
        $request->setSellerId( $this->SellerId );

        // set items
        $items = new FBAOutboundServiceMWS_Model_GetFulfillmentPreviewItemList();
        $total_number_of_units = 0;
        foreach ( $products as $product ) {

            $item = new FBAOutboundServiceMWS_Model_GetFulfillmentPreviewItem();
            $item->setSellerSKU( $product['sku'] );
            $item->setSellerFulfillmentOrderItemId( $product['sku'] );
            $item->setQuantity( $product['qty'] );

            // $items->setmember( $item );  // setmember() replaces all values
            $items->withmember( $item );    // withmember() adds a new value
            $total_number_of_units += $product['qty'];
        }
        $request->setItems( $items );

        // set address
        $address = new FBAOutboundServiceMWS_Model_Address();
        $address->setName(                  $shipping_address['name'] );
        $address->setLine1(                 $shipping_address['street'] );
        $address->setCity(                  $shipping_address['city'] );
        $address->setPostalCode(            $shipping_address['postcode'] );
        $address->setStateOrProvinceCode(   $shipping_address['state'] );
        $address->setCountryCode(           $shipping_address['country'] );
        // $address->setDistrictOrCounty( '' );
        // $address->setPhoneNumber( '' );

        $request->setAddress( $address );


        $previews = array();

        // invoke request
        try {
            $response = $this->service->GetFulfillmentPreview($request);
              
            if ($response->isSetGetFulfillmentPreviewResult()) {
                $getFulfillmentPreviewResult = $response->getGetFulfillmentPreviewResult();

                // process previews
                $FulfillmentPreviews = $getFulfillmentPreviewResult->getFulfillmentPreviews();
                $previewList = $FulfillmentPreviews->getmember();

                foreach ($previewList as $preview) {

                    $key = $preview->getShippingSpeedCategory();

                    $previews[ $key ] = new stdClass();
                    $previews[ $key ]->ShippingSpeedCategory        = $preview->getShippingSpeedCategory();
                    $previews[ $key ]->ScheduledDeliveryInfo        = $preview->getScheduledDeliveryInfo();
                    $previews[ $key ]->IsFulfillable                = $preview->getIsFulfillable();
                    $previews[ $key ]->IsCODCapable                 = $preview->getIsCODCapable();

                    if ( $preview->getEstimatedShippingWeight() ) {
                        $previews[ $key ]->EstimatedShippingWeightUnit  = $preview->getEstimatedShippingWeight()->getUnit();
                        $previews[ $key ]->EstimatedShippingWeightValue = $preview->getEstimatedShippingWeight()->getValue();
                    }
                    $previews[ $key ]->UnfulfillablePreviewItems    = $preview->getUnfulfillablePreviewItems()->getmember();
                    $previews[ $key ]->OrderUnfulfillableReasons    = $preview->getOrderUnfulfillableReasons();
                    // $previews[ $key ]->EstimatedFees                = $preview->getEstimatedFees()->getmember();
                    // $previews[ $key ]->FulfillmentPreviewShipments  = $preview->getFulfillmentPreviewShipments()->getmember();

                    $EstimatedFees = array();
                    $TotalShippingFee = 0;
                    if ( $preview->getEstimatedFees() ) {
                        $EstimatedFeesList = $preview->getEstimatedFees()->getmember();

                        foreach ( $EstimatedFeesList as $fee ) {
                            $feeKey = $fee->getName();
                            $EstimatedFees[ $feeKey ] = $fee->getAmount()->getValue();
                            if ( 'FBAPerUnitFulfillmentFee' == $feeKey ) {
                                $TotalShippingFee    += $fee->getAmount()->getValue() * $total_number_of_units;
                            } else {
                                $TotalShippingFee    += $fee->getAmount()->getValue();
                            }
                        }
                    }

                    $previews[ $key ]->EstimatedFees                = $EstimatedFees;
                    $previews[ $key ]->TotalShippingFee             = $TotalShippingFee;

                    $FulfillmentPreviewShipments = array();
                    $shipment = null;
                    if ( $preview->getFulfillmentPreviewShipments() ) {
                        $FulfillmentPreviewShipmentsList = $preview->getFulfillmentPreviewShipments()->getmember();
                        foreach ( $FulfillmentPreviewShipmentsList as $shipmentKey => $shipment ) {
                            $FulfillmentPreviewShipments[ $shipmentKey ] = new stdClass();
                            $FulfillmentPreviewShipments[ $shipmentKey ]->EarliestShipDate = $shipment->getEarliestShipDate();
                            $FulfillmentPreviewShipments[ $shipmentKey ]->LatestShipDate = $shipment->getLatestShipDate();
                            $FulfillmentPreviewShipments[ $shipmentKey ]->EarliestArrivalDate = $shipment->getEarliestArrivalDate();
                            $FulfillmentPreviewShipments[ $shipmentKey ]->LatestArrivalDate = $shipment->getLatestArrivalDate();
                            // $FulfillmentPreviewShipments[ $shipmentKey ]->FulfillmentPreviewItems = $shipment->getFulfillmentPreviewItems();
                        }
                    }

                    $previews[ $key ]->FulfillmentPreviewShipments  = $FulfillmentPreviewShipments;
                    $previews[ $key ]->EarliestArrivalDate          = is_object($shipment) ? $shipment->getEarliestArrivalDate() : null;
                    $previews[ $key ]->LatestArrivalDate            = is_object($shipment) ? $shipment->getLatestArrivalDate() : null;

                }
                // echo "<pre>previews: ";print_r($previews);echo"</pre>";#die();

                // sort previews from Standard to Priority
                $sorted_previews = array();
                if ( isset( $previews['Standard']  ) ) $sorted_previews['Standard']  = $previews['Standard'];
                if ( isset( $previews['Expedited'] ) ) $sorted_previews['Expedited'] = $previews['Expedited'];
                if ( isset( $previews['Priority']  ) ) $sorted_previews['Priority']  = $previews['Priority'];

                // log to db - parsed request
                $this->dblogger->updateLog( array(
                    'result'    => json_encode( $previews ),
                    'success'   => 'Success'
                ));

                $result = new stdClass();
                $result->previews = $sorted_previews;
                $result->success = true;
                return $result;

            }

        } catch ( FBAOutboundServiceMWS_Exception $ex ) {
            $error = new stdClass();
            $error->ErrorMessage = $ex->getMessage();
            $error->ErrorCode    = $ex->getErrorCode();
            $error->StatusCode   = $ex->getStatusCode();
            return $error;
        }

        $result = new stdClass();
        $result->success = false;
        return $result;
    }





    // ListMarketplaceParticipations (V2)
    public function listMarketplaceParticipations() {
        $this->initAPI('Sellers');

        $request = new MarketplaceWebServiceSellers_Model_ListMarketplaceParticipationsRequest();
        $request->setSellerId( $this->SellerId );
             
        $allowed_markets = array();

        // invoke request
        try {
            $response = $this->service->listMarketplaceParticipations($request);
              
            if ($response->isSetListMarketplaceParticipationsResult()) {
                $listMarketplaceParticipationsResult = $response->getListMarketplaceParticipationsResult();

                // process marketplaces
                $listMarketplaces = $listMarketplaceParticipationsResult->getListMarketplaces();
                $marketplaceList = $listMarketplaces->getMarketplace();
                foreach ($marketplaceList as $marketplace) {

                    $key = $marketplace->getMarketplaceId();

                    $allowed_markets[ $key ] = new stdClass();
                    $allowed_markets[ $key ]->MarketplaceId       = $marketplace->getMarketplaceId();
                    $allowed_markets[ $key ]->Name                = $marketplace->getName();
                    $allowed_markets[ $key ]->DefaultLanguageCode = $marketplace->getDefaultLanguageCode();
                    $allowed_markets[ $key ]->DefaultCountryCode  = $marketplace->getDefaultCountryCode();
                    $allowed_markets[ $key ]->DefaultCurrencyCode = $marketplace->getDefaultCurrencyCode();
                    $allowed_markets[ $key ]->DomainName          = $marketplace->getDomainName();
                }

                // process participations
                $listParticipations = $listMarketplaceParticipationsResult->getListParticipations();
                $participationList = $listParticipations->getParticipation();
                foreach ($participationList as $participation) {

                    $key = $marketplace->getMarketplaceId();
                    $allowed_markets[ $key ]->MarketplaceId              = $participation->getMarketplaceId();
                    $allowed_markets[ $key ]->SellerId                   = $participation->getSellerId();
                    $allowed_markets[ $key ]->HasSellerSuspendedListings = $participation->getHasSellerSuspendedListings();
                }
                // echo "<pre>allowed_markets: ";print_r($allowed_markets);echo"</pre>";#die();

                $result = new stdClass();
                $result->allowed_markets = $allowed_markets;
                $result->success = true;
                return $result;

            }

        } catch ( MarketplaceWebServiceSellers_Exception $ex ) {
            $error = new stdClass();
            $error->ErrorMessage = $ex->getMessage();
            $error->ErrorCode    = $ex->getErrorCode();
            $error->StatusCode   = $ex->getStatusCode();
            return $error;
        }

        $result = new stdClass();
        $result->success = false;
        return $result;
    }







    // submit feed (V2)
    public function submitFeed( $FeedType, $FeedContent ) {
        $this->initAPI();

        // init stream ressource                           
        $feedHandle = @fopen('php://memory', 'rw+');
        fwrite($feedHandle, $FeedContent);
        rewind($feedHandle);

        $marketplaceIdArray = array("Id" => array( $this->MarketplaceId ) );

        $request = new MarketplaceWebService_Model_SubmitFeedRequest();
        $request->setSellerId( $this->SellerId );
        $request->setMarketplaceIdList( $marketplaceIdArray );
        $request->setPurgeAndReplace( false );
        $request->setFeedType( $FeedType );

        $request->setFeedContent( $feedHandle );
        rewind( $feedHandle );
        $request->setContentMd5( base64_encode( md5( stream_get_contents($feedHandle) , true ) ) );
        rewind( $feedHandle );

        $result = $this->invokeSubmitFeed( $this->service, $request );
        @fclose($feedHandle);

        return $result;
    }

    function invokeSubmitFeed(MarketplaceWebService_Interface $service, $request) 
    {
        try {
            $response = $service->submitFeed($request);

            if ($response->isSetSubmitFeedResult()) { 
                $submitFeedResult = $response->getSubmitFeedResult();

                if ($submitFeedResult->isSetFeedSubmissionInfo()) { 
                    $feedSubmissionInfo = $submitFeedResult->getFeedSubmissionInfo();

                    if ( $feedSubmissionInfo->isSetFeedSubmissionId() ) {
                
                        $result = new stdClass();
                        $result->FeedSubmissionId     = $feedSubmissionInfo->getFeedSubmissionId();
                        $result->FeedProcessingStatus = $feedSubmissionInfo->getFeedProcessingStatus();
                        $result->FeedType             = $feedSubmissionInfo->getFeedType();
                        $result->SubmittedDate        = $feedSubmissionInfo->getSubmittedDate()->format(DATE_FORMAT);
                        $result->success              = true;

                        // log to db - parsed request
                        $this->dblogger->updateLog( array(
                            'result'    => json_encode( $result ),
                            'success'   => 'Success'
                        ));

                        return $result;
                    }
                } 
            } 

        } catch (MarketplaceWebService_Exception $ex) {
            $error = new stdClass();
            $error->ErrorMessage = $ex->getMessage();
            $error->ErrorCode    = $ex->getErrorCode();
            $error->StatusCode   = $ex->getStatusCode();
            $error->success      = false;
            return $error;
        }

        $result = new stdClass();
        $result->success = false;
        return $result;
    }


    // fetch feeds submission result (V2) (combined)
    public function getFeedSubmissionResult( $FeedSubmissionId ) {
        $this->initAPI();

        $resultHandle = @fopen('php://memory', 'rw+');

        $request = new MarketplaceWebService_Model_GetFeedSubmissionResultRequest();
        $request->setMerchant( $this->SellerId );
        $request->setFeedSubmissionId( $FeedSubmissionId );
        $request->setFeedSubmissionResult( $resultHandle );
             
        // invoke request
        try {
            $response = $this->service->getFeedSubmissionResult($request);
              
            if ($response->isSetGetFeedSubmissionResultResult()) {

                rewind( $resultHandle );  
                $result = new stdClass();
                $result->content = stream_get_contents( $resultHandle );
                $result->success = true;

                // log to db - parsed request
                $this->dblogger->updateLog( array(
                    'result'    => $result->content,
                    'success'   => 'Success'
                ));

                return $result;

            }

        } catch ( MarketplaceWebService_Exception $ex ) {
            $error = new stdClass();
            $error->ErrorMessage = $ex->getMessage();
            $error->ErrorCode    = $ex->getErrorCode();
            $error->StatusCode   = $ex->getStatusCode();
            $error->success      = false;
            return $error;
        }

        $result = new stdClass();
        $result->success = false;
        return $result;
    }





    // fetch list of feeds (V2)
    public function getFeedSubmissionList( $FeedSubmissionId = false ) {
        $this->initAPI();

        $request = new MarketplaceWebService_Model_GetFeedSubmissionListRequest();
        $request->setMerchant( $this->SellerId );

        if ( $FeedSubmissionId ) {
            if ( ! is_array($FeedSubmissionId) ) $FeedSubmissionId = array( $FeedSubmissionId );
            // $request->setFeedSubmissionIdList( $FeedSubmissionId );            

            // limit to 100 IDs to prevent API error
            if ( sizeof($FeedSubmissionId) > 100 ) $FeedSubmissionId = array_slice( $FeedSubmissionId, 0, 100 );

            $idList = new MarketplaceWebService_Model_IdList();
            // $idList->withId('<Feed Submission Id>');
            $idList->setId( $FeedSubmissionId );
            $request->setFeedSubmissionIdList($idList);
        } else {
            $request->setMaxCount( 50 );
        }

        $result = $this->invokeGetFeedSubmissionList( $this->service, $request );
        return $result;
    }
                                                                     
    /**
    * Get Feed Submission List
    * returns a list of feed submission identifiers and their associated metadata
    *   
    * @param MarketplaceWebService_Interface $service instance of MarketplaceWebService_Interface
    * @param mixed $request MarketplaceWebService_Model_GetFeedSubmissionList or array of parameters
    */
    function invokeGetFeedSubmissionList(MarketplaceWebService_Interface $service, $request) {
        $feeds = array();

        try {

            $response = $service->getFeedSubmissionList($request);

            if ($response->isSetGetFeedSubmissionListResult()) { 
                $getFeedSubmissionListResult = $response->getGetFeedSubmissionListResult();
                // if ($getFeedSubmissionListResult->isSetNextToken()) 
                // if ($getFeedSubmissionListResult->isSetHasNext()) 

                $feedSubmissionInfoList = $getFeedSubmissionListResult->getFeedSubmissionInfoList();

                foreach ($feedSubmissionInfoList as $feedSubmissionInfo) {

                    $feed = new stdClass();
                    $feed->FeedSubmissionId        = $feedSubmissionInfo->getFeedSubmissionId();
                    $feed->FeedType                = $feedSubmissionInfo->getFeedType();
                    $feed->FeedProcessingStatus    = $feedSubmissionInfo->getFeedProcessingStatus();

                    if ( $feedSubmissionInfo->isSetSubmittedDate() ) 
                        $feed->SubmittedDate           = $feedSubmissionInfo->getSubmittedDate()->format(DATE_FORMAT);
                    if ( $feedSubmissionInfo->isSetStartedProcessingDate() ) 
                        $feed->StartedProcessingDate   = $feedSubmissionInfo->getStartedProcessingDate()->format(DATE_FORMAT);
                    if ( $feedSubmissionInfo->isSetCompletedProcessingDate() ) 
                        $feed->CompletedProcessingDate = $feedSubmissionInfo->getCompletedProcessingDate()->format(DATE_FORMAT);

                    $feeds[] = $feed;
                }
            } 
    
        } catch (MarketplaceWebService_Exception $ex) {
            // echo("Caught Exception: " . $ex->getMessage() . "\n");
            // echo("Response Status Code: " . $ex->getStatusCode() . "\n");
            // echo("Error Code: " . $ex->getErrorCode() . "\n");
            // echo("Error Type: " . $ex->getErrorType() . "\n");
            // echo("Request ID: " . $ex->getRequestId() . "\n");
            // echo("XML: " . $ex->getXML() . "\n");
            // echo("ResponseHeaderMetadata: " . $ex->getResponseHeaderMetadata() . "\n");

            $error = new stdClass();
            $error->ErrorMessage = $ex->getMessage();
            $error->ErrorCode    = $ex->getErrorCode();
            $error->StatusCode   = $ex->getStatusCode();
            return $error;
        } 

        // log to db - parsed request
        $this->dblogger->updateLog( array(
            'result'    => json_encode( $feeds ),
            'success'   => 'Success'
        ));

        return $feeds;
    }
                            


    // cancel feed submission (V2) (combined)
    public function cancelFeedSubmission( $FeedSubmissionId ) {
        $this->initAPI();

        $request = new MarketplaceWebService_Model_CancelFeedSubmissionsRequest();
        $request->setMerchant( $this->SellerId );

        // set idList
        if ( ! is_array($FeedSubmissionId) ) $FeedSubmissionId = array( $FeedSubmissionId );
        $idList = new MarketplaceWebService_Model_IdList();
        $idList->setId( $FeedSubmissionId );
        $request->setFeedSubmissionIdList($idList);

            
        // invoke request
        try {
            $response = $this->service->cancelFeedSubmissions($request);
              
            if ($response->isSetCancelFeedSubmissionsResult()) {

                $cancelFeedSubmissionsResult = $response->getCancelFeedSubmissionsResult();
                $feedSubmissionInfoList = $cancelFeedSubmissionsResult->getFeedSubmissionInfoList();

                $feeds = array();
                foreach ($feedSubmissionInfoList as $feedSubmissionInfo) {

                    $feed = new stdClass();
                    $feed->FeedSubmissionId        = $feedSubmissionInfo->getFeedSubmissionId();
                    $feed->FeedType                = $feedSubmissionInfo->getFeedType();
                    $feed->FeedProcessingStatus    = $feedSubmissionInfo->getFeedProcessingStatus();

                    if ( $feedSubmissionInfo->isSetSubmittedDate() ) 
                        $feed->SubmittedDate           = $feedSubmissionInfo->getSubmittedDate()->format(DATE_FORMAT);
                    if ( $feedSubmissionInfo->isSetStartedProcessingDate() ) 
                        $feed->StartedProcessingDate   = $feedSubmissionInfo->getStartedProcessingDate()->format(DATE_FORMAT);
                    if ( $feedSubmissionInfo->isSetCompletedProcessingDate() ) 
                        $feed->CompletedProcessingDate = $feedSubmissionInfo->getCompletedProcessingDate()->format(DATE_FORMAT);

                    $feeds[] = $feed;
                }

                $result = new stdClass();
                $result->feeds = $feeds;
                $result->success = true;

                // log to db - parsed request
                $this->dblogger->updateLog( array(
                    'result'    => json_encode( $result ),
                    'success'   => 'Success'
                ));

                return $result;

            }

        } catch ( MarketplaceWebService_Exception $ex ) {
            $error = new stdClass();
            $error->ErrorMessage = $ex->getMessage();
            $error->ErrorCode    = $ex->getErrorCode();
            $error->StatusCode   = $ex->getStatusCode();
            return $error;
        }

        $result = new stdClass();
        $result->success = false;
        return $result;
    }



    /**
     * Products API V2
     */


    // get matching product by ID (V2) (combined)
    public function getMatchingProductForId( $ProductID, $IdType = 'ASIN' ) {
        WPLA()->logger->info('getMatchingProductForId() - '.$ProductID);
        $this->initAPI('Products');

        $request = new MarketplaceWebServiceProducts_Model_GetMatchingProductForIdRequest();
        $request->setSellerId( $this->SellerId );
        $request->setMarketplaceId( $this->MarketplaceId );
        $request->setIdType( $IdType );

        // set idList
        if ( ! is_array($ProductID) ) $ProductID = array( $ProductID );
        $idList = new MarketplaceWebServiceProducts_Model_IdListType();
        $idList->setId( $ProductID );
        $request->setIdList( $idList );

            
        // invoke request
        try {
            $response = $this->service->GetMatchingProductForId($request);

            // parse XML response
            $dom = new DOMDocument();
            $dom->loadXML($response->toXML());
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $xml_data = $dom->saveXML();
            // WPLA()->logger->info('XML: '.print_r($xml_data,1));

            $parsed_xml = $this->parseXML( $xml_data );
            WPLA()->logger->debug('parsed_xml: '.print_r($parsed_xml,1));

            // $res = $response->getGetMatchingProductForIdResult();
            // echo("ResponseHeaderMetadata: " . $response->getResponseHeaderMetadata() . "\n");              
    
            $result = new stdClass();
            $result->product = $parsed_xml;
            $result->success = true;

            // log to db - parsed request
            $this->dblogger->updateLog( array(
                'response'  => $xml_data,
                'result'    => json_encode( $result->product ),
                'success'   => 'Success'
            ));

            return $result;

        } catch ( MarketplaceWebServiceProducts_Exception $ex ) {
            $error = new stdClass();
            $error->ErrorMessage = $ex->getMessage();
            $error->ErrorCode    = $ex->getErrorCode();
            $error->StatusCode   = $ex->getStatusCode();

            $error->ErrorType    = $ex->getErrorType();
            $error->RequestId    = $ex->getRequestId();
            $error->XML          = $ex->getXML();
            $error->HeaderMeta   = $ex->getResponseHeaderMetadata();

            $error->success      = false;

            // log to db - parsed request
            $this->dblogger->updateLog( array(
                'result'    => json_encode( $error ),
                'success'   => 'Error'
            ));
            return $error;
        }

        $result = new stdClass();
        $result->success = false;
        return $result;
    } // getMatchingProductForId()


    // get competitive pricing by ASIN/SKU (V2) (combined)
    public function getCompetitivePricingForId( $ProductID ) {
        WPLA()->logger->info('getCompetitivePricingForId() - '.join(', ',$ProductID));
        $this->initAPI('Products');

        $request = new MarketplaceWebServiceProducts_Model_GetCompetitivePricingForASINRequest();
        $request->setSellerId( $this->SellerId );
        $request->setMarketplaceId( $this->MarketplaceId );

        // set idList
        if ( ! is_array($ProductID) ) $ProductID = array( $ProductID );
        $idList = new MarketplaceWebServiceProducts_Model_ASINListType();
        $idList->setASIN( $ProductID );
        $request->setASINList( $idList );

            
        // invoke request
        try {
            $response = $this->service->GetCompetitivePricingForASIN($request);

            // parse XML response
            $dom = new DOMDocument();
            $dom->loadXML($response->toXML());
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $xml_data = $dom->saveXML();
            // WPLA()->logger->info('XML: '.print_r($xml_data,1));

            $parsed_xml = $this->parseXML( $xml_data );
            WPLA()->logger->debug('parsed_xml: '.print_r($parsed_xml,1));
            // echo "<pre>parsed_xml: ";print_r($parsed_xml);echo"</pre>";

            // unify results for single and multiple items - result is an array of items
            // if ( is_array( $parsed_xml->products ) ) {
            //     $products = $parsed_xml->products;
            // } else {
            //     $products = array( $parsed_xml->products );
            // }

            $result = new stdClass();
            $result->products = $parsed_xml;
            $result->success  = true;

            // log to db - parsed request
            $this->dblogger->updateLog( array(
                'response'  => $xml_data,
                'result'    => json_encode( $result->products ),
                'success'   => 'Success'
            ));

            return $result;

        } catch ( MarketplaceWebServiceProducts_Exception $ex ) {
            $error = new stdClass();
            $error->ErrorMessage = $ex->getMessage();
            $error->ErrorCode    = $ex->getErrorCode();
            $error->StatusCode   = $ex->getStatusCode();

            $error->ErrorType    = $ex->getErrorType();
            $error->RequestId    = $ex->getRequestId();
            $error->XML          = $ex->getXML();
            $error->HeaderMeta   = $ex->getResponseHeaderMetadata();

            // log to db - parsed request
            $this->dblogger->updateLog( array(
                'result'    => json_encode( $error ),
                'success'   => 'Error'
            ));
            return $error;
        }

        $result = new stdClass();
        $result->success = false;
        return $result;
    } // getCompetitivePricingForId()


    // get lowest prices for ASIN (V2) (combined)
    public function getLowestOfferListingsForASIN( $ProductID ) {
        // WPLA()->logger->info('getLowestOfferListingsForASIN() - '.join(', ',$ProductID));
        $this->initAPI('Products');

        $request = new MarketplaceWebServiceProducts_Model_GetLowestOfferListingsForASINRequest();
        $request->setSellerId( $this->SellerId );
        $request->setMarketplaceId( $this->MarketplaceId );
        $request->setExcludeMe( true );

        // set idList
        if ( ! is_array($ProductID) ) $ProductID = array( $ProductID );
        $idList = new MarketplaceWebServiceProducts_Model_ASINListType();
        $idList->setASIN( $ProductID );
        $request->setASINList( $idList );

          
        // invoke request
        try {
            $response = $this->service->GetLowestOfferListingsForASIN($request);

            // parse XML response
            $dom = new DOMDocument();
            $dom->loadXML($response->toXML());
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $xml_data = $dom->saveXML();
            // WPLA()->logger->info('XML: '.print_r($xml_data,1));

            $parsed_xml = $this->parseXML( $xml_data );
            WPLA()->logger->debug('parsed_xml: '.print_r($parsed_xml,1));
            // echo "<pre>parsed_xml: ";print_r($parsed_xml);echo"</pre>";

            // unify results for single and multiple items - result is an array of items
            // if ( is_array( $parsed_xml->products ) ) {
            //     $products = $parsed_xml->products;
            // } else {
            //     $products = array( $parsed_xml->products );
            // }

            $result = new stdClass();
            $result->products = $parsed_xml;
            $result->success  = true;

            // log to db - parsed request
            $this->dblogger->updateLog( array(
                'response'  => $xml_data,
                'result'    => json_encode( $result->products ),
                'success'   => 'Success'
            ));

            return $result;

        } catch ( MarketplaceWebServiceProducts_Exception $ex ) {
            $error = new stdClass();
            $error->ErrorMessage = $ex->getMessage();
            $error->ErrorCode    = $ex->getErrorCode();
            $error->StatusCode   = $ex->getStatusCode();

            $error->ErrorType    = $ex->getErrorType();
            $error->RequestId    = $ex->getRequestId();
            $error->XML          = $ex->getXML();
            $error->HeaderMeta   = $ex->getResponseHeaderMetadata();

            // log to db - parsed request
            $this->dblogger->updateLog( array(
                'result'    => json_encode( $error ),
                'success'   => 'Error'
            ));
            return $error;
        }

        $result = new stdClass();
        $result->success = false;
        return $result;
    } // getLowestOfferListingsForASIN()





    /**
     * Orders API V2
     */


    // get order details (V2) (not working!)
    public function getOrder_v2( $OrderID ) {
        WPLA()->logger->info('getOrder() - '.$OrderID);
        $this->initAPI('Orders');

        $request = new MarketplaceWebServiceOrders_Model_GetOrderRequest();
        $request->setSellerId( $this->SellerId );
        // $request->setMarketplaceId( $this->MarketplaceId );

        // set AmazonOrderId
        $request->setAmazonOrderId( $OrderID );

            
        // invoke request
        try {
            $response = $this->service->GetOrder($request);

            echo "<pre>";print_r($response);echo"</pre>";#die();
            echo "<pre>";
            // $xml_data = $response->toXML();
            // echo "<pre>";print_r($xml_data);echo"</pre>";#die();

                echo("        GetOrderResponse\n");
                if ($response->isSetGetOrderResult()) { 
                    echo("            GetOrderResult\n");
                    $getOrderResult = $response->getGetOrderResult();
                    if ($getOrderResult->isSetOrders()) { 
                        echo("                Orders\n");
                        $orders = $getOrderResult->getOrders();
                        $orderList = $orders->getOrder();
                        foreach ($orderList as $order) {
                            echo("                    Order\n");
                            if ($order->isSetAmazonOrderId()) 
                            {
                                echo("                        AmazonOrderId\n");
                                echo("                            " . $order->getAmazonOrderId() . "\n");
                            }
                            if ($order->isSetSellerOrderId()) 
                            {
                                echo("                        SellerOrderId\n");
                                echo("                            " . $order->getSellerOrderId() . "\n");
                            }
                            if ($order->isSetPurchaseDate()) 
                            {
                                echo("                        PurchaseDate\n");
                                echo("                            " . $order->getPurchaseDate() . "\n");
                            }
                            if ($order->isSetLastUpdateDate()) 
                            {
                                echo("                        LastUpdateDate\n");
                                echo("                            " . $order->getLastUpdateDate() . "\n");
                            }
                            if ($order->isSetOrderStatus()) 
                            {
                                echo("                        OrderStatus\n");
                                echo("                            " . $order->getOrderStatus() . "\n");
                            }
                            if ($order->isSetFulfillmentChannel()) 
                            {
                                echo("                        FulfillmentChannel\n");
                                echo("                            " . $order->getFulfillmentChannel() . "\n");
                            }
                            if ($order->isSetSalesChannel()) 
                            {
                                echo("                        SalesChannel\n");
                                echo("                            " . $order->getSalesChannel() . "\n");
                            }
                            if ($order->isSetOrderChannel()) 
                            {
                                echo("                        OrderChannel\n");
                                echo("                            " . $order->getOrderChannel() . "\n");
                            }
                            if ($order->isSetShipServiceLevel()) 
                            {
                                echo("                        ShipServiceLevel\n");
                                echo("                            " . $order->getShipServiceLevel() . "\n");
                            }
                            if ($order->isSetShippingAddress()) { 
                                echo("                        ShippingAddress\n");
                                $shippingAddress = $order->getShippingAddress();
                                if ($shippingAddress->isSetName()) 
                                {
                                    echo("                            Name\n");
                                    echo("                                " . $shippingAddress->getName() . "\n");
                                }
                                if ($shippingAddress->isSetAddressLine1()) 
                                {
                                    echo("                            AddressLine1\n");
                                    echo("                                " . $shippingAddress->getAddressLine1() . "\n");
                                }
                                if ($shippingAddress->isSetAddressLine2()) 
                                {
                                    echo("                            AddressLine2\n");
                                    echo("                                " . $shippingAddress->getAddressLine2() . "\n");
                                }
                                if ($shippingAddress->isSetAddressLine3()) 
                                {
                                    echo("                            AddressLine3\n");
                                    echo("                                " . $shippingAddress->getAddressLine3() . "\n");
                                }
                                if ($shippingAddress->isSetCity()) 
                                {
                                    echo("                            City\n");
                                    echo("                                " . $shippingAddress->getCity() . "\n");
                                }
                                if ($shippingAddress->isSetCounty()) 
                                {
                                    echo("                            County\n");
                                    echo("                                " . $shippingAddress->getCounty() . "\n");
                                }
                                if ($shippingAddress->isSetDistrict()) 
                                {
                                    echo("                            District\n");
                                    echo("                                " . $shippingAddress->getDistrict() . "\n");
                                }
                                if ($shippingAddress->isSetStateOrRegion()) 
                                {
                                    echo("                            StateOrRegion\n");
                                    echo("                                " . $shippingAddress->getStateOrRegion() . "\n");
                                }
                                if ($shippingAddress->isSetPostalCode()) 
                                {
                                    echo("                            PostalCode\n");
                                    echo("                                " . $shippingAddress->getPostalCode() . "\n");
                                }
                                if ($shippingAddress->isSetCountryCode()) 
                                {
                                    echo("                            CountryCode\n");
                                    echo("                                " . $shippingAddress->getCountryCode() . "\n");
                                }
                                if ($shippingAddress->isSetPhone()) 
                                {
                                    echo("                            Phone\n");
                                    echo("                                " . $shippingAddress->getPhone() . "\n");
                                }
                            } 
                            if ($order->isSetOrderTotal()) { 
                                echo("                        OrderTotal\n");
                                $orderTotal = $order->getOrderTotal();
                                if ($orderTotal->isSetCurrencyCode()) 
                                {
                                    echo("                            CurrencyCode\n");
                                    echo("                                " . $orderTotal->getCurrencyCode() . "\n");
                                }
                                if ($orderTotal->isSetAmount()) 
                                {
                                    echo("                            Amount\n");
                                    echo("                                " . $orderTotal->getAmount() . "\n");
                                }
                            } 
                            if ($order->isSetNumberOfItemsShipped()) 
                            {
                                echo("                        NumberOfItemsShipped\n");
                                echo("                            " . $order->getNumberOfItemsShipped() . "\n");
                            }
                            if ($order->isSetNumberOfItemsUnshipped()) 
                            {
                                echo("                        NumberOfItemsUnshipped\n");
                                echo("                            " . $order->getNumberOfItemsUnshipped() . "\n");
                            }
                            if ($order->isSetPaymentExecutionDetail()) { 
                                echo("                        PaymentExecutionDetail\n");
                                $paymentExecutionDetail = $order->getPaymentExecutionDetail();
                                $paymentExecutionDetailItemList = $paymentExecutionDetail->getPaymentExecutionDetailItem();
                                foreach ($paymentExecutionDetailItemList as $paymentExecutionDetailItem) {
                                    echo("                            PaymentExecutionDetailItem\n");
                                    if ($paymentExecutionDetailItem->isSetPayment()) { 
                                        echo("                                Payment\n");
                                        $payment = $paymentExecutionDetailItem->getPayment();
                                        if ($payment->isSetCurrencyCode()) 
                                        {
                                            echo("                                    CurrencyCode\n");
                                            echo("                                        " . $payment->getCurrencyCode() . "\n");
                                        }
                                        if ($payment->isSetAmount()) 
                                        {
                                            echo("                                    Amount\n");
                                            echo("                                        " . $payment->getAmount() . "\n");
                                        }
                                    } 
                                    if ($paymentExecutionDetailItem->isSetPaymentMethod()) 
                                    {
                                        echo("                                PaymentMethod\n");
                                        echo("                                    " . $paymentExecutionDetailItem->getPaymentMethod() . "\n");
                                    }
                                }
                            } 
                            if ($order->isSetPaymentMethod()) 
                            {
                                echo("                        PaymentMethod\n");
                                echo("                            " . $order->getPaymentMethod() . "\n");
                            }
                            if ($order->isSetMarketplaceId()) 
                            {
                                echo("                        MarketplaceId\n");
                                echo("                            " . $order->getMarketplaceId() . "\n");
                            }
                            if ($order->isSetBuyerEmail()) 
                            {
                                echo("                        BuyerEmail\n");
                                echo("                            " . $order->getBuyerEmail() . "\n");
                            }
                            if ($order->isSetBuyerName()) 
                            {
                                echo("                        BuyerName\n");
                                echo("                            " . $order->getBuyerName() . "\n");
                            }
                            if ($order->isSetShipmentServiceLevelCategory()) 
                            {
                                echo("                        ShipmentServiceLevelCategory\n");
                                echo("                            " . $order->getShipmentServiceLevelCategory() . "\n");
                            }
                            if ($order->isSetShippedByAmazonTFM()) 
                            {
                                echo("                        ShippedByAmazonTFM\n");
                                echo("                            " . $order->getShippedByAmazonTFM() . "\n");
                            }
                            if ($order->isSetTFMShipmentStatus()) 
                            {
                                echo("                        TFMShipmentStatus\n");
                                echo("                            " . $order->getTFMShipmentStatus() . "\n");
                            }
                        }
                    } 
                } 


            // parse XML response
            // $dom = new DOMDocument();
            // $dom->loadXML($response->toXML());
            // $dom->preserveWhiteSpace = false;
            // $dom->formatOutput = true;
            // $xml_data = $dom->saveXML();
            // WPLA()->logger->info('XML:'.print_r($xml_data,1));

            // $parsed_xml = $this->parseXML( $xml_data );
            // WPLA()->logger->info('parsed_xml:'.print_r($parsed_xml,1));

            // $res = $response->getGetMatchingProductForIdResult();
            // echo("ResponseHeaderMetadata: " . $response->getResponseHeaderMetadata() . "\n");              

            // if ($response->isSetSubmitFeedResult()) { 
            //     $submitFeedResult = $response->getSubmitFeedResult();

            //     if ($submitFeedResult->isSetFeedSubmissionInfo()) { 
            //         $feedSubmissionInfo = $submitFeedResult->getFeedSubmissionInfo();

            //         if ( $feedSubmissionInfo->isSetFeedSubmissionId() ) {
                
            //             $result = new stdClass();
            //             $result->FeedSubmissionId     = $feedSubmissionInfo->getFeedSubmissionId();
            //             $result->FeedProcessingStatus = $feedSubmissionInfo->getFeedProcessingStatus();
            //             $result->FeedType             = $feedSubmissionInfo->getFeedType();
            //             $result->SubmittedDate        = $feedSubmissionInfo->getSubmittedDate()->format(DATE_FORMAT);
            //             $result->success              = true;

            //             // log to db - parsed request
            //             $this->dblogger->updateLog( array(
            //                 'result'    => json_encode( $result ),
            //                 'success'   => 'Success'
            //             ));

            //             return $result;
            //         }
            //     } 
            // } 
    
            $result = new stdClass();
            // $result->product = $parsed_xml;
            $result->order   = 'TODO';
            $result->success = true;

            // log to db - parsed request
            $this->dblogger->updateLog( array(
                'response'  => $xml_data,
                'result'    => json_encode( $result->order ),
                'success'   => 'Success'
            ));

            return $result;

        } catch ( MarketplaceWebServiceOrders_Exception $ex ) {
            $error = new stdClass();
            $error->ErrorMessage = $ex->getMessage();
            $error->ErrorCode    = $ex->getErrorCode();
            $error->StatusCode   = $ex->getStatusCode();

            $error->ErrorType    = $ex->getErrorType();
            $error->RequestId    = $ex->getRequestId();
            $error->XML          = $ex->getXML();
            $error->HeaderMeta   = $ex->getResponseHeaderMetadata();

            // log to db - parsed request
            $this->dblogger->updateLog( array(
                'result'    => json_encode( $error ),
                'success'   => 'Error'
            ));
            return $error;
        }

        $result = new stdClass();
        $result->success = false;
        return $result;
    }






















    /**
     * legacy functions (v1)
     * these still use sendSignedRequest() instead of the SDK API functions and initAPI()
     * TODO: convert to v2
     */

    // this method is not used anymore - replaced by getMatchingProductForId()
    public function getProduct_deprecated( $asin ) {

        $action  = 'GetMatchingProductForId';
        $section = 'Products';
        $version = '2011-10-01';
        $params  = array();
        $params['MarketplaceId'] = $this->MarketplaceId; 
        $params['IdType'] = 'ASIN'; 
        $params['IdList.Id.1'] = $asin; 

        $result = $this->sendSignedRequest( $action, $section, $params, $version );

        // check if products were returned
        // if ( isset( $result->GetMatchingProductForIdResult->Products->Product ) ) {
        if ( isset( $result->ItemAttributes ) ) {

            // fetch item attributes from secondary namespace
            // $ns2 = $result->GetMatchingProductForIdResult->Products->Product->AttributeSets->children('ns2', true);
            // $ns2 = $this->processSimpleXmlResult( $ns2 );
        
            // return products array
            return $result->ItemAttributes;
        }

        // return result object in case of errors
        // $result = $this->processSimpleXmlResult( $result );
        return $result;
    }

    public function listMatchingProducts( $query ) {

        $action  = 'ListMatchingProducts';
        $section = 'Products';
        $version = '2011-10-01';
        $params  = array();
        $params['MarketplaceId'] = $this->MarketplaceId; 
        $params['Query'] = trim( $query ); 

        $result = $this->sendSignedRequest( $action, $section, $params, $version );

        // check if array products were returned (obsolete?)
        if ( is_array( $result ) ) {
            return $result;
        }

        // return result object in case of errors
        // $result = $this->processSimpleXmlResult( $result );
        return $result;
    }

    public function getOrders( $from_date, $days = false ) {
        // $this->GetServiceStatus();

        $action  = 'ListOrders';
        $section = 'Orders';
        $version = '2013-09-01';
        $params  = array();
        // $params['LastUpdatedAfter'] = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", strtotime( $from_date.' UTC' ) + 1 ); // 1sec offset causes trouble if throttling is active
        $params['LastUpdatedAfter']    = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", strtotime( $from_date.' UTC' ) + 0 ); // no offset - will return the most recent order(s) as well
        // $params['MarketplaceId.Id.1'] = $this->MarketplaceId; // 
        // $marketplaceIdArray = array("Id" => array( $this->MarketplaceId ) );
        // $request->setMarketplaceIdList( $marketplaceIdArray );
        $enable_orders_filter = get_option( 'wpla_fetch_orders_filter', 0 );
        if ( is_array( $this->allowed_marketplace_ids ) && ! empty( $this->allowed_marketplace_ids ) && ! $enable_orders_filter ) {
            $i = 1;
            foreach ($this->allowed_marketplace_ids as $MarketplaceId) {
                $params['MarketplaceId.Id.'.$i] = $MarketplaceId; // fetch orders from all marketplaces for account
                $i++;
            }
        } else {
            $params['MarketplaceId.Id.1'] = $this->MarketplaceId; // fall back
        }


        // handle custom number of days
        if ( $days ) {
            $params['LastUpdatedAfter'] = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", time() - $days * 24 * 3600 ); 
            // $params['MaxResultsPerPage'] = 5; // debug
        }

        $result = $this->sendSignedRequest( $action, $section, $params, $version );
        $result = $this->processSimpleXmlResult( $result );

        // check if orders were returned
        if ( isset( $result->ListOrdersResult->Orders->Order ) ) {
        
            // return fixed orders array
            $orders = $result->ListOrdersResult->Orders->Order;
            if ( is_object($orders) ) $orders = array( $orders );

            // check for NextToken
            $NextToken = isset( $result->ListOrdersResult->NextToken ) ? $result->ListOrdersResult->NextToken : false;
            while ( $NextToken ) {

                // call ListOrdersByNextToken
                $this->initLogger();
                $params = array( 'NextToken' => $NextToken );
                $result = $this->sendSignedRequest( 'ListOrdersByNextToken', $section, $params, $version );
                $result = $this->processSimpleXmlResult( $result );

                // check if orders were returned
                if ( isset( $result->ListOrdersByNextTokenResult->Orders->Order ) ) {

                    $next_orders = $result->ListOrdersByNextTokenResult->Orders->Order;
                    if ( is_object($next_orders) ) $next_orders = array( $next_orders );

                    // merge orders array
                    $orders = array_merge( $orders, $next_orders );
                }

                // check for NextToken
                $NextToken = isset( $result->ListOrdersByNextTokenResult->NextToken ) ? $result->ListOrdersByNextTokenResult->NextToken : false;

            } // while NextToken

            return $orders;

        // check if empty result was returned
        } elseif ( isset( $result->ListOrdersResult->Orders ) ) {

            // return empty array
            return array();

        }

        // return result object in case of errors
        return $result;
    }

    // GetOrder (v1)
    public function getOrder( $OrderID ) {

        $action  = 'GetOrder';
        $section = 'Orders';
        $version = '2013-09-01';
        $params  = array();
        $params['AmazonOrderId.Id.1'] = $OrderID; // 

        $result = $this->sendSignedRequest( $action, $section, $params, $version );
        $result = $this->processSimpleXmlResult( $result );

        // check if orders were returned
        if ( isset( $result->GetOrderResult->Orders->Order ) ) {
        
            // return fixed orders array
            $orders = $result->GetOrderResult->Orders->Order;
            if ( is_object($orders) ) $orders = array( $orders );

            return $orders;

        // check if empty result was returned
        } elseif ( isset( $result->GetOrderResult->Orders ) ) {

            // return empty array
            return array();

        }

        // return result object in case of errors
        return $result;
    }

    public function getOrderLineItems( $AmazonOrderId ) {
        // $this->GetServiceStatus();

        $action  = 'ListOrderItems';
        $section = 'Orders';
        $version = '2013-09-01';
        $params  = array();
        $params['AmazonOrderId']      = $AmazonOrderId;
        // $params['MarketplaceId.Id.1'] = $this->MarketplaceId; // 

        $result = $this->sendSignedRequest( $action, $section, $params, $version );
        $result = $this->processSimpleXmlResult( $result );

        // check if orders were returned
        if ( isset( $result->ListOrderItemsResult->OrderItems->OrderItem ) ) {
        
            // unify results for single and multiple items - result is an array of items
            if ( is_array( $result->ListOrderItemsResult->OrderItems->OrderItem ) ) {
                $items = $result->ListOrderItemsResult->OrderItems->OrderItem;
            } else {
                $items = array( $result->ListOrderItemsResult->OrderItems->OrderItem );
            }

            // return orders array
            return $items;

        // check if empty result was returned
        } elseif ( isset( $result->ListOrderItemsResult->OrderItems ) ) {

            // return empty array
            return array();

        }

        // return result object in case of errors
        return $result;
    }


    // fetch report requests list (V2)
    public function getReportRequestList_v2( $ReportRequestId = false ) {
        WPLA()->logger->info('getReportRequestList_v2()');
        $this->initAPI();

        $request = new MarketplaceWebService_Model_GetReportRequestListRequest();
        $request->setMerchant( $this->SellerId );
        $request->setMarketplace( $this->MarketplaceId );

        if ( $ReportRequestId ) {
            if ( ! is_array($ReportRequestId) ) $ReportRequestId = array( $ReportRequestId );
            // $request->setReportRequestIdList( $ReportRequestId );            

            // limit to 100 IDs to prevent API error
            if ( sizeof($ReportRequestId) > 100 ) $ReportRequestId = array_slice( $ReportRequestId, 0, 100 );

            $idList = new MarketplaceWebService_Model_IdList();
            // $idList->withId('<Feed Submission Id>');
            $idList->setId( $ReportRequestId );
            $request->setReportRequestIdList($idList);
        } else {
            $request->setMaxCount( 10 );
        }

        $result = $this->invokeGetReportRequestList( $this->service, $request );
        return $result;
    }
                                                          
    /**
    * Get Report List Action Sample
    * returns a list of reports; by default the most recent ten reports,
    * regardless of their acknowledgement status
    *   
    * @param MarketplaceWebService_Interface $service instance of MarketplaceWebService_Interface
    * @param mixed $request MarketplaceWebService_Model_GetReportList or array of parameters
    */
    function invokeGetReportRequestList( MarketplaceWebService_Interface $service, $request ) {
        $reports = array();

        try {

            $response = $service->getReportRequestList($request);
                  
            if ($response->isSetGetReportRequestListResult()) { 
                $getReportRequestListResult = $response->getGetReportRequestListResult();
                // if ($getReportRequestListResult->isSetNextToken()) 
                // if ($getReportRequestListResult->isSetHasNext()) 

                $reportRequestInfoList = $getReportRequestListResult->getReportRequestInfoList();
                foreach ($reportRequestInfoList as $reportRequestInfo) {
    
                    $report = new stdClass();
                    $report->ReportRequestId           = $reportRequestInfo->getReportRequestId();
                    $report->ReportType                = $reportRequestInfo->getReportType();
                    $report->ReportProcessingStatus    = $reportRequestInfo->getReportProcessingStatus();

                    if ( $reportRequestInfo->isSetStartDate() ) 
                        $report->StartDate              = $reportRequestInfo->getStartDate()->format(DATE_FORMAT);
                    if ( $reportRequestInfo->isSetEndDate() ) 
                        $report->EndDate                = $reportRequestInfo->getEndDate()->format(DATE_FORMAT);
                    if ( $reportRequestInfo->isSetSubmittedDate() ) 
                        $report->SubmittedDate          = $reportRequestInfo->getSubmittedDate()->format(DATE_FORMAT);

                    if ( $reportRequestInfo->isSetCompletedDate() ) 
                        $report->CompletedDate          = $reportRequestInfo->getCompletedDate()->format(DATE_FORMAT);
                    if ( $reportRequestInfo->isSetStartedProcessingDate() ) 
                        $report->StartedProcessingDate  = $reportRequestInfo->getStartedProcessingDate()->format(DATE_FORMAT);
                    if ( $reportRequestInfo->isSetGeneratedReportId() ) 
                        $report->GeneratedReportId      = $reportRequestInfo->getGeneratedReportId();

                    $reports[] = $report;
                }
            } 

        } catch (MarketplaceWebService_Exception $ex) {
            // echo("Caught Exception: " . $ex->getMessage() . "\n");
            // echo("Response Status Code: " . $ex->getStatusCode() . "\n");
            // echo("Error Code: " . $ex->getErrorCode() . "\n");
            // echo("Error Type: " . $ex->getErrorType() . "\n");
            // echo("Request ID: " . $ex->getRequestId() . "\n");
            // echo("XML: " . $ex->getXML() . "\n");
            // echo("ResponseHeaderMetadata: " . $ex->getResponseHeaderMetadata() . "\n");

            $error = new stdClass();
            $error->ErrorMessage = $ex->getMessage();
            $error->ErrorCode    = $ex->getErrorCode();
            $error->StatusCode   = $ex->getStatusCode();
            return $error;
        }

        // log to db - parsed request
        $this->dblogger->updateLog( array(
            'result'    => json_encode( $reports ),
            'success'   => 'Success'
        ));

        return $reports;
    }
                                                                     
    // request report (V2)
    public function requestReport_v2( $ReportType ) {
        WPLA()->logger->info('requestReport_v2()');
        $this->initAPI();

        $marketplaceIdArray = array("Id" => array( $this->MarketplaceId ) );

        $request = new MarketplaceWebService_Model_RequestReportRequest();
        $request->setMerchant( $this->SellerId );
        $request->setMarketplaceIdList( $marketplaceIdArray );
        $request->setReportType( $ReportType );
        // $request->setReportOptions('ShowSalesChannel=true');

        $result = $this->invokeRequestReport( $this->service, $request );

        return $result;
    }


    public function requestReport( $ReportType ) {

        $params  = array();
        $params['ReportType'] = $ReportType;
        $version = '2009-01-01';

        // some reports require a StartDate...
        $report_types_requiring_startdate = array('_GET_AMAZON_FULFILLED_SHIPMENTS_DATA_');
        if ( in_array( $ReportType, $report_types_requiring_startdate ) ) {
            $startdate = date('Y-m-d', strtotime('-1 week') ); // hardcoded to one week for now
            $params['StartDate'] = $startdate . 'T00:00:00+00:00';
        }

        // set MarketplaceId - otherwise Amazon defaults to home MarketplaceId
        $params['MarketplaceIdList.Id.1'] = $this->MarketplaceId;

        // send request
        $result = $this->sendSignedRequest( 'RequestReport', null, $params, $version );
        $result = $this->processSimpleXmlResult( $result );

        // check if report requests were returned
        if ( isset( $result->RequestReportResult->ReportRequestInfo ) ) {
            return (array) $result->RequestReportResult;
        }

        // return result object in case of errors
        return $result;
    }

    public function getReportRequestList() {

        $params  = array();
        $version = '2009-01-01';

        // set MarketplaceId (has no effect)
        // $params['MarketplaceId'] = $this->MarketplaceId;

        // send request
        // $result = $this->sendSignedRequest( 'GetReportRequestList' );
        $result = $this->sendSignedRequest( 'GetReportRequestList', null, $params, $version );
        $result = $this->processSimpleXmlResult( $result );

        // check if report requests were returned
        if ( isset( $result->GetReportRequestListResult->ReportRequestInfo ) ) {
            return  $result->GetReportRequestListResult->ReportRequestInfo;
        }

        // return result object in case of errors
        return $result;
    }

    public function getReport( $ReportId ) {

        $params  = array();
        $params['ReportId'] = $ReportId;  
        $version = '2009-01-01';

        // send request
        $result = $this->sendSignedRequest( 'GetReport', null, $params, $version, true );

        // return raw CSV or XML result
        return $result;
    }




    public function setMerchantId( $merchant_id ) {
        $this->SellerId = $merchant_id;
    }
    public function setMarketplaceId( $marketplace_id ) {
        $this->MarketplaceId = $marketplace_id;
    }
    public function setAccessKeyId( $access_key_id ) {
        $this->AccessKey = $access_key_id;
    }
    public function setSecretKey( $secret_key ) {
        $this->SecretKey = $secret_key;
    }
    public function setAllowedMarkets( $allowed_markets ) {
        $allowed_markets = maybe_unserialize( $allowed_markets );
        if ( ! is_array($allowed_markets) ) $allowed_markets = array();
        $this->allowed_marketplace_ids = array();
        foreach ($allowed_markets as $market) {
            // only use marketplaces that begin with www.amazon
            if ( 'www.amazon' == substr($market->DomainName, 0, 10 ) ) {
                $this->allowed_marketplace_ids[] = $market->MarketplaceId;
            }
        }
    }

    public function processSimpleXmlResult( $result ) {
        return json_decode( json_encode( $result ) );
    }

    public function GetServiceStatus() {

        $action  = 'GetServiceStatus';
        $section = 'Orders';
        $params  = array();
        return $this->sendSignedRequest( $action, $section, $params );

    }


    /*****************************************************/

    // send signed API request
    // http://stackoverflow.com/questions/11694376/converting-amazon-mws-scratchpad-queries-to-api-calls
    function sendSignedRequest( $action, $section = false, $params = array(), $version = '2009-01-01', $return_raw = false ) {

        // $base_url = "https://mws.amazonservices.de/Products/2011-01-01";
        // $host = "mws.amazonservices.de";
        $api_section = $section ? $section . '/' . $version : '';

        $base_params = array(
            'AWSAccessKeyId' => $this->AccessKey,
            // 'Action' => "ListMatchingProducts",
            'Action' => $action,
            'SellerId' => $this->SellerId,
            'SignatureMethod' => "HmacSHA256",
            'SignatureVersion' => "2",
            'Timestamp'=> gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", time()),
            'Version'=> $version,
            // 'MarketplaceId' => MARKETPLACE_ID,
            // 'Query' => $searchTerm,
            // 'QueryContextId' => "Books"
        );

        $params = array_merge( $base_params, $params );

        // debug
        // echo "<pre>params: ";print_r($params);echo"</pre>";#die();

        // Sort the URL parameters
        $url_parts = array();
        foreach(array_keys($params) as $key)
            $url_parts[] = $key . "=" . str_replace('%7E', '~', rawurlencode($params[$key]));

        sort($url_parts);

        // Construct the string to sign
        $url_string = implode("&", $url_parts);
        $string_to_sign = "GET\n".$this->api_host."\n/$api_section\n" . $url_string;

        // Sign the request
        $signature = hash_hmac("sha256", $string_to_sign, $this->SecretKey, TRUE);

        // Base64 encode the signature and make it URL safe
        $signature = urlencode(base64_encode($signature));

        $url = 'https://' . $this->api_host . '/' . $api_section . '?' . $url_string . "&Signature=" . $signature;

        // log to db - before request
        $this->dblogger->updateLog( array(
            'callname'    => $action,
            'request'     => $string_to_sign,
            'parameters'  => maybe_serialize( $params ),
            'request_url' => $url,
            'account_id'  => $this->account_id,
            'market_id'   => $this->market_id,
            'success'     => 'pending'
        ));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_TIMEOUT, 600);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        // If you are having problems, try adding this to the end of the curl-setopt block:
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        $curlinfo = curl_getinfo($ch);

        // echo "<pre>response: ";print_r( htmlspecialchars( $response ) );echo"</pre>";#die();
        // echo "<pre>curl: ";print_r( $curlinfo );echo"</pre>";#die();

        // log to db - after request
        $this->dblogger->updateLog( array(
            'response'    => $response,
            'http_code'   => $curlinfo['http_code'],
            'success'     => 'HTTP OK'
        ));

        curl_close($ch);

        if ($response === false) {
            // throw new Exception('Server connection is failed. Please try again later.');

            // log to db - parsed request
            $this->dblogger->updateLog( array(
                'result'    => 'The response from Amazon was empty. (Server connection is failed. Please try again later.)',
                'curl'      => maybe_serialize( $curlinfo ),
                'success'   => 'Error'
            ));
            wpla_show_message("There was a problem communicating with Amazon. Please try again later. (Response to $action request was empty)",'error');
            return false;

        } else {

            if ( $return_raw ) return $response;

            $parsed_xml = $this->parseXML( $response );
            // echo "<pre>XML: ";print_r( $parsed_xml );echo"</pre>";#die();

            if ( isset( $parsed_xml->Error ) ) {
                $success = 'Error';
            } elseif ( isset( $parsed_xml->GetMatchingProductForIdResult->Error ) ) {
                $success = 'Error';
            } elseif ( isset( $parsed_xml->GetCompetitivePricingForASINResult->Error ) ) {
                $success = 'Error';
            } elseif ( isset( $parsed_xml->GetLowestOfferListingsForASINResult->Error ) ) {
                $success = 'Error';
            } elseif ( isset( $parsed_xml->ListMatchingProductsResult->Error ) ) {
                $success = 'Error';
            } elseif ( isset( $parsed_xml->GetOrderResult->Error ) ) {
                $success = 'Error';
            } elseif ( isset( $parsed_xml->ListOrderItemsResult->Error ) ) {
                $success = 'Error';
            } else {
                $success = 'Success';
            }


            // log to db - parsed request
            $this->dblogger->updateLog( array(
                'result'    => json_encode( $parsed_xml ),
                'curl'      => maybe_serialize( $curlinfo ),
                'success'   => $success
            ));

        }

        return $parsed_xml;
    }



    /*****************************************************/

    private function parseXML( $xml ) {

        // get rid of useless namespace before parsing XML
        $xml = str_replace('ns2:', '', $xml);

        // parse XML
        $parsed_xml = simplexml_load_string( $xml );
        // echo "<pre>XML1: ";print_r( $parsed_xml );echo"</pre>";#die();

        // if ( isset( $parsed_xml->Error ) ) {
        //     $success = 'Error';
        // } else {
        //     $success = 'Success';
        // }


        // check for secondary namespace - GetMatchingProductForIdResult
        if ( isset( $parsed_xml->GetMatchingProductForIdResult->Products->Product ) ) {

            // shortcut
            $product = $parsed_xml->GetMatchingProductForIdResult->Products->Product;

            // fetch item attributes from secondary namespace
            // $ns2 = $parsed_xml->GetMatchingProductForIdResult->Products->Product->AttributeSets->children('ns2', true);
            // $ns2 = $this->processSimpleXmlResult( $ns2 );
        
            $product->variation_type = false;
            if ( $product->Relationships->VariationParent ) {
                $product->variation_type      = 'child';
                $product->VariationParentASIN = (string)$product->Relationships->VariationParent->Identifiers->MarketplaceASIN->ASIN;
            }
            if ( $product->Relationships->VariationChild ) {
                $product->variation_type = 'parent';
            }

            // extract ASIN and MarketplaceId
            $product->ASIN          = (string)$product->Identifiers->MarketplaceASIN->ASIN;
            $product->MarketplaceId = (string)$product->Identifiers->MarketplaceASIN->MarketplaceId;

            // return product node
            return $this->processSimpleXmlResult( $product );
        }

        // check for secondary namespace - ListMatchingProductsResult
        if ( isset( $parsed_xml->ListMatchingProductsResult->Products->Product ) ) {

            $products = array();
            foreach ($parsed_xml->ListMatchingProductsResult->Products->Product as $product) {

                // fetch item attributes from secondary namespace
                // $ns2 = $product->AttributeSets->children('ns2', true);
                // $ns2 = $this->processSimpleXmlResult( $ns2 );
            
                // extract ASIN and MarketplaceId
                $product->ASIN          = (string)$product->Identifiers->MarketplaceASIN->ASIN;
                $product->MarketplaceId = (string)$product->Identifiers->MarketplaceASIN->MarketplaceId;

                // store ItemAttributes in product object
                $products[] = $product;
            }

            // return products array
            return $this->processSimpleXmlResult( $products );
        } elseif ( isset( $parsed_xml->ListMatchingProductsResult ) ) {
            return array();
        }


        // parse GetCompetitivePricingForASINResult(s)
        if ( isset( $parsed_xml->GetCompetitivePricingForASINResult ) ) {

            // return product nodes directly
            $products = $this->parseGetCompetitivePricingForASINResult( $parsed_xml );
            return $products;
        }

        // parse GetLowestOfferListingsForASINResult(s)
        if ( isset( $parsed_xml->GetLowestOfferListingsForASINResult ) ) {

            // return product nodes directly
            $products = $this->parseGetLowestOfferListingsForASINResult( $parsed_xml );
            return $products;
        }


        // return $this->processSimpleXmlResult( $parsed_xml );
        return $parsed_xml;
    } // parseXML()


    // parse GetCompetitivePricingForASINResult(s)
    private function parseGetCompetitivePricingForASINResult( $parsed_xml ) {

        $products = array();
        foreach ($parsed_xml->GetCompetitivePricingForASINResult as $GetCompetitivePricingForASINResult) {

            // catch errors like "ASIN ... is not valid for marketplace ..."
            if ( $GetCompetitivePricingForASINResult->Error ) {

                // extract ASIN and error
                $product = new stdClass();
                $product->ASIN          = (string)$GetCompetitivePricingForASINResult['ASIN'];
                $product->status        = (string)$GetCompetitivePricingForASINResult['status'];
                $product->message       = $GetCompetitivePricingForASINResult->Error->Message;
                $product->prices        = array();
                $product->MarketplaceId = '';

                // add to products array
                $products[ $product->ASIN ] = $product;
                continue;
            }

            // shortcut
            $product = $GetCompetitivePricingForASINResult->Product;
            $prices  = array();

            $CompetitivePrices = $GetCompetitivePricingForASINResult->Product->CompetitivePricing->CompetitivePrices->CompetitivePrice;

            if ( $CompetitivePrices ) {

                // if ( is_array( $GetCompetitivePricingForASINResult->Product->CompetitivePricing->CompetitivePrices->CompetitivePrice ) )
                // foreach ($GetCompetitivePricingForASINResult->Product->CompetitivePricing->CompetitivePrices->CompetitivePrice as $CompetitivePrice) {

                // if ( ! is_array( $CompetitivePrices ) ) $CompetitivePrices = array( $CompetitivePrices );
                foreach ( $CompetitivePrices as $CompetitivePrice) {

                    // ini_set('display_errors', 1);
                    // error_reporting( E_ALL | E_STRICT );
                    // echo "<pre>";print_r($CompetitivePrice);echo"</pre>";#die();

                    // extract condition and subcondition
                    $CompetitivePrice->condition            = (string)$CompetitivePrice['condition'];
                    $CompetitivePrice->subcondition         = (string)$CompetitivePrice['subcondition'];
                    $CompetitivePrice->belongsToRequester   =   (bool)$CompetitivePrice['belongsToRequester'];

                    // extract everything
                    $new_price = new stdClass();
                    $new_price->LandedPrice                 =  (float)$CompetitivePrice->Price->LandedPrice->Amount;
                    $new_price->ListingPrice                =  (float)$CompetitivePrice->Price->ListingPrice->Amount;
                    $new_price->Shipping                    =  (float)$CompetitivePrice->Price->Shipping->Amount;
                    $new_price->condition                   = (string)$CompetitivePrice['condition'];
                    $new_price->subcondition                = (string)$CompetitivePrice['subcondition'];
                    $new_price->belongsToRequester          = (string)$CompetitivePrice['belongsToRequester'] == 'true' ? true : false;
                    $new_price->id                          =    (int)$CompetitivePrice->CompetitivePriceId;
                    $prices[] = $new_price;
                }

            }

            // extract ASIN and MarketplaceId
            $product->ASIN          = (string)$product->Identifiers->MarketplaceASIN->ASIN;
            $product->MarketplaceId = (string)$product->Identifiers->MarketplaceASIN->MarketplaceId;

            // convert XML object to PHP object
            $product = $this->processSimpleXmlResult( $product );
            $product->prices        =  (array)$prices;

            // add to products array
            $products[ $product->ASIN ] = $product;
        }

        // return product nodes
        return $products;

    } // parseGetCompetitivePricingForASINResult()


    // parse GetLowestOfferListingsForASINResult(s)
    private function parseGetLowestOfferListingsForASINResult( $parsed_xml ) {

        $products = array();
        foreach ($parsed_xml->GetLowestOfferListingsForASINResult as $GetLowestOfferListingsForASINResult) {

            // catch errors like "ASIN ... is not valid for marketplace ..."
            if ( $GetLowestOfferListingsForASINResult->Error ) {

                // extract ASIN and error
                $product = new stdClass();
                $product->ASIN          = (string)$GetLowestOfferListingsForASINResult['ASIN'];
                $product->status        = (string)$GetLowestOfferListingsForASINResult['status'];
                $product->message       = $GetLowestOfferListingsForASINResult->Error->Message;
                $product->prices        = array();
                $product->MarketplaceId = '';

                // add to products array
                $products[ $product->ASIN ] = $product;
                continue;
            }

            // shortcut
            $product = $GetLowestOfferListingsForASINResult->Product;
            $prices  = array();

            $LowestOfferListings = $GetLowestOfferListingsForASINResult->Product->LowestOfferListings->LowestOfferListing;

            if ( $LowestOfferListings ) {
                // if ( is_array( $GetLowestOfferListingsForASINResult->Product->LowestOfferListings->LowestOfferListing ) )
                // foreach ($GetLowestOfferListingsForASINResult->Product->LowestOfferListings->LowestOfferListing as $LowestOfferListing) {

                // if ( ! is_array( $LowestOfferListings ) ) $LowestOfferListings = array( $LowestOfferListings );
                foreach ( $LowestOfferListings as $LowestOfferListing) {

                    // ini_set('display_errors', 1);
                    // error_reporting( E_ALL | E_STRICT );
                    // echo "<pre>";print_r($LowestOfferListing);echo"</pre>";#die();

                    // extract condition and subcondition
                    // $LowestOfferListing->condition            = (string)$LowestOfferListing['condition'];
                    // $LowestOfferListing->subcondition         = (string)$LowestOfferListing['subcondition'];

                    // extract everything
                    $new_price = new stdClass();
                    $new_price->LandedPrice                     =  (float)$LowestOfferListing->Price->LandedPrice->Amount;
                    $new_price->ListingPrice                    =  (float)$LowestOfferListing->Price->ListingPrice->Amount;
                    $new_price->Shipping                        =  (float)$LowestOfferListing->Price->Shipping->Amount;
                    $new_price->condition                       = (string)$LowestOfferListing->Qualifiers->ItemCondition;
                    $new_price->subcondition                    = (string)$LowestOfferListing->Qualifiers->ItemSubcondition;
                    $new_price->FulfillmentChannel              = (string)$LowestOfferListing->Qualifiers->FulfillmentChannel;
                    $new_price->ShipsDomestically               = (string)$LowestOfferListing->Qualifiers->ShipsDomestically;
                    $new_price->ShippingTime                    = (string)$LowestOfferListing->Qualifiers->ShippingTime->Max;
                    $new_price->SellerPositiveFeedbackRating    = (string)$LowestOfferListing->Qualifiers->SellerPositiveFeedbackRating;
                    $new_price->SellerFeedbackCount             =    (int)$LowestOfferListing->SellerFeedbackCount;
                    $new_price->MultipleOffersAtLowestPrice     = (string)$LowestOfferListing->MultipleOffersAtLowestPrice == 'True' ? 1 : 0;
                    $new_price->NumberOfOfferListingsConsidered =    (int)$LowestOfferListing->NumberOfOfferListingsConsidered;
                    $new_price->id                              =    (int)$LowestOfferListing->CompetitivePriceId;
                    $prices[] = $new_price;
                    // echo "<pre>";print_r($new_price);echo"</pre>";#die();
                }

            }

            // extract ASIN and MarketplaceId
            $product->ASIN          = (string)$product->Identifiers->MarketplaceASIN->ASIN;
            $product->MarketplaceId = (string)$product->Identifiers->MarketplaceASIN->MarketplaceId;

            // convert XML object to PHP object
            $product = $this->processSimpleXmlResult( $product );
            $product->prices        =  (array)$prices;

            // add to products array
            $products[ $product->ASIN ] = $product;
        }

        // return product nodes
        return $products;

    } // parseGetLowestOfferListingsForASINResult()




    /*****************************************************/

    private function sendRequestAsPost($params)
    {
        $curlObject = curl_init();

        //set the server we are using
        curl_setopt($curlObject, CURLOPT_URL, $this->serverScript);

        // stop CURL from verifying the peer's certificate
        curl_setopt($curlObject, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlObject, CURLOPT_SSL_VERIFYHOST, false);

        // disable http headers
        curl_setopt($curlObject, CURLOPT_HEADER, false);

        // set the data body of the request
        curl_setopt($curlObject, CURLOPT_POST, true);
        curl_setopt($curlObject, CURLOPT_POSTFIELDS, http_build_query($params,'','&'));

        // set it to return the transfer as a string from curl_exec
        curl_setopt($curlObject, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlObject, CURLOPT_CONNECTTIMEOUT, $this->getConnectionTimeout());

        echo "<pre>API - sendRequestAsPost</pre>";
        echo "<pre>";print_r( $params );echo"</pre>";

        $response = curl_exec($curlObject);
        echo "<pre>";print_r( curl_getinfo($curlObject) );echo"</pre>";#die();

        curl_close($curlObject);

        if ($response === false) {
            throw new Exception('Server connection is failed. Please try again later.');
        }

        return $response;
    }

    private function sendRequestAsGet($params)
    {
        $curlObject = curl_init();

        die('please set $this->serverScript');

        //set the server we are using
        curl_setopt($curlObject, CURLOPT_URL, $this->serverScript.'?'.http_build_query($params,'','&'));

        // stop CURL from verifying the peer's certificate
        curl_setopt($curlObject, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlObject, CURLOPT_SSL_VERIFYHOST, false);

        // disable http headers
        curl_setopt($curlObject, CURLOPT_HEADER, false);
        curl_setopt($curlObject, CURLOPT_POST, false);

        // set it to return the transfer as a string from curl_exec
        curl_setopt($curlObject, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlObject, CURLOPT_CONNECTTIMEOUT, $this->getConnectionTimeout());

        echo "<pre>API - sendRequestAsGet</pre>";
        echo "<pre>";print_r( $params );echo"</pre>";

        $response = curl_exec($curlObject);
        echo "<pre>";print_r( curl_getinfo($curlObject) );echo"</pre>";#die();

        curl_close($curlObject);

        if ($response === false) {
            throw new Exception('Server connection is failed. Please try again later.');
        }

        return $response;
    }

    private function getConnectionTimeout()
    {
        return 300;
    }

    // re-init logger - required to log multiple requests in the same session
    private function initLogger()
    {
        $this->dblogger = new WPLA_AmazonLogger();
        if ( is_object( $this->service ) ) $this->service->dblogger = $this->dblogger;  
    }

} // class WPLA_AmazonAPI

