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
 * MarketplaceWebServiceSellers_Model_ListParticipations
 * 
 * Properties:
 * <ul>
 * 
 * <li>Participation: MarketplaceWebServiceSellers_Model_Participation</li>
 *
 * </ul>
 */ 
class MarketplaceWebServiceSellers_Model_ListParticipations extends MarketplaceWebServiceSellers_Model
{

    /**
     * Construct new MarketplaceWebServiceSellers_Model_ListParticipations
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>Participation: MarketplaceWebServiceSellers_Model_Participation</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'Participation' => array('FieldValue' => array(), 'FieldType' => array('MarketplaceWebServiceSellers_Model_Participation')),
        );
        parent::__construct($data);
    }

        /**
     * Gets the value of the Participation.
     * 
     * @return array of Participation Participation
     */
    public function getParticipation() 
    {
        return $this->_fields['Participation']['FieldValue'];
    }

    /**
     * Sets the value of the Participation.
     * 
     * @param mixed Participation or an array of Participation Participation
     * @return this instance
     */
    public function setParticipation($participation) 
    {
        if (!$this->_isNumericArray($participation)) {
            $participation =  array ($participation);    
        }
        $this->_fields['Participation']['FieldValue'] = $participation;
        return $this;
    }


    /**
     * Sets single or multiple values of Participation list via variable number of arguments. 
     * For example, to set the list with two elements, simply pass two values as arguments to this function
     * <code>withParticipation($participation1, $participation2)</code>
     * 
     * @param Participation  $participationArgs one or more Participation
     * @return MarketplaceWebServiceSellers_Model_ListParticipations  instance
     */
    public function withParticipation($participationArgs)
    {
        foreach (func_get_args() as $participation) {
            $this->_fields['Participation']['FieldValue'][] = $participation;
        }
        return $this;
    }   



    /**
     * Checks if Participation list is non-empty
     * 
     * @return bool true if Participation list is non-empty
     */
    public function isSetParticipation()
    {
        return count ($this->_fields['Participation']['FieldValue']) > 0;
    }




}