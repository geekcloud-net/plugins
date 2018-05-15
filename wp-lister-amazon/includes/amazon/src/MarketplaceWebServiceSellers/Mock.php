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
 *  @see MarketplaceWebServiceSellers_Interface
 */
require_once ('MarketplaceWebServiceSellers/Interface.php'); 

/**
 * This contains the Sellers section of the Marketplace Web Service.
 * 
 */
class  MarketplaceWebServiceSellers_Mock implements MarketplaceWebServiceSellers_Interface
{
    // Public API ------------------------------------------------------------//

            
    /**
     * List Marketplace Participations 
     * This operation can be used to list all Marketplaces that a seller can sell in.
     * The operation returns a List of Participation elements and a List of Marketplace
     * elements. The SellerId is the only parameter required by this operation.
     *   
     * @see http://docs.amazonwebservices.com/${docPath}ListMarketplaceParticipations.html      
     * @param mixed $request array of parameters for MarketplaceWebServiceSellers_Model_ListMarketplaceParticipations request or MarketplaceWebServiceSellers_Model_ListMarketplaceParticipations object itself
     * @see MarketplaceWebServiceSellers_Model_ListMarketplaceParticipations
     * @return MarketplaceWebServiceSellers_Model_ListMarketplaceParticipationsResponse MarketplaceWebServiceSellers_Model_ListMarketplaceParticipationsResponse
     *
     * @throws MarketplaceWebServiceSellers_Exception
     */
    public function listMarketplaceParticipations($request) 
    {
        require_once ('MarketplaceWebServiceSellers/Model/ListMarketplaceParticipationsResponse.php');
        return MarketplaceWebServiceSellers_Model_ListMarketplaceParticipationsResponse::fromXML($this->_invoke('ListMarketplaceParticipations'));
    }


            
    /**
     * List Marketplace Participations By Next Token 
     * If ListMarketplaces cannot return all the marketplaces in one go, it will
     * provide a nextToken.  That nextToken can be used with this operation to
     * retrieve the next batch of Marketplaces for that SellerId.
     *   
     * @see http://docs.amazonwebservices.com/${docPath}ListMarketplaceParticipationsByNextToken.html      
     * @param mixed $request array of parameters for MarketplaceWebServiceSellers_Model_ListMarketplaceParticipationsByNextToken request or MarketplaceWebServiceSellers_Model_ListMarketplaceParticipationsByNextToken object itself
     * @see MarketplaceWebServiceSellers_Model_ListMarketplaceParticipationsByNextToken
     * @return MarketplaceWebServiceSellers_Model_ListMarketplaceParticipationsByNextTokenResponse MarketplaceWebServiceSellers_Model_ListMarketplaceParticipationsByNextTokenResponse
     *
     * @throws MarketplaceWebServiceSellers_Exception
     */
    public function listMarketplaceParticipationsByNextToken($request) 
    {
        require_once ('MarketplaceWebServiceSellers/Model/ListMarketplaceParticipationsByNextTokenResponse.php');
        return MarketplaceWebServiceSellers_Model_ListMarketplaceParticipationsByNextTokenResponse::fromXML($this->_invoke('ListMarketplaceParticipationsByNextToken'));
    }


            
    /**
     * Get Service Status 
     * Returns the service status of a particular MWS API section. The operation
     * takes no input. All API sections within the API are required to implement this operation.
     *   
     * @see http://docs.amazonwebservices.com/${docPath}GetServiceStatus.html      
     * @param mixed $request array of parameters for MarketplaceWebServiceSellers_Model_GetServiceStatus request or MarketplaceWebServiceSellers_Model_GetServiceStatus object itself
     * @see MarketplaceWebServiceSellers_Model_GetServiceStatus
     * @return MarketplaceWebServiceSellers_Model_GetServiceStatusResponse MarketplaceWebServiceSellers_Model_GetServiceStatusResponse
     *
     * @throws MarketplaceWebServiceSellers_Exception
     */
    public function getServiceStatus($request) 
    {
        require_once ('MarketplaceWebServiceSellers/Model/GetServiceStatusResponse.php');
        return MarketplaceWebServiceSellers_Model_GetServiceStatusResponse::fromXML($this->_invoke('GetServiceStatus'));
    }

    // Private API ------------------------------------------------------------//

    private function _invoke($actionName)
    {
        return $xml = file_get_contents('MarketplaceWebServiceSellers/Mock/' . $actionName . 'Response.xml', /** search include path */ TRUE);
    }
}