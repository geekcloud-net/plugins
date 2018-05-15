<?php

/**
 * Description of WPEAE_EbaySite
 *
 * @author Geometrix
 */
if (!class_exists('WPEAE_EbaySite')):

    class WPEAE_EbaySite {

        public $id = "";
        public $language = "";
        public $country = "";
        public $siteid = "";
        public $sitecode = "";
        public $sitename = "";

        public function __construct($data = array()) {
            if ($data) {
                foreach ($data as $field => $value) {
                    if (property_exists(get_class($this), $field)) {
                        $this->$field = $value;
                    }
                }
            }
        }

        public static function load_sites() {
            $sites = array(
                array('language' => 'en-US', 'country' => 'US', 'siteid' => '0', 'sitecode' => 'EBAY-US', 'sitename' => 'eBay United States'),
                array('language' => 'de-AT', 'country' => 'AT', 'siteid' => '16', 'sitecode' => 'EBAY-AT', 'sitename' => 'eBay Austria'),
                array('language' => 'en-AU', 'country' => 'AU', 'siteid' => '15', 'sitecode' => 'EBAY-AU', 'sitename' => 'eBay Australia'),
                array('language' => 'de-CH', 'country' => 'CH', 'siteid' => '193', 'sitecode' => 'EBAY-CH', 'sitename' => 'eBay Switzerland'),
                array('language' => 'en-DE', 'country' => 'DE', 'siteid' => '77', 'sitecode' => 'EBAY-DE', 'sitename' => 'eBay Germany'),
                array('language' => 'en-CA', 'country' => 'CA', 'siteid' => '2', 'sitecode' => 'EBAY-ENCA', 'sitename' => 'eBay Canada (English)'),
                array('language' => 'en-ES', 'country' => 'ES', 'siteid' => '186', 'sitecode' => 'EBAY-ES', 'sitename' => 'eBay Spain'),
                array('language' => 'fr-FR', 'country' => 'FR', 'siteid' => '71', 'sitecode' => 'EBAY-FR', 'sitename' => 'eBay France'),
                array('language' => 'fr-BE', 'country' => 'BE', 'siteid' => '23', 'sitecode' => 'EBAY-FRBE', 'sitename' => 'eBay Belgium(French)'),
                array('language' => 'fr-CA', 'country' => 'CA', 'siteid' => '210', 'sitecode' => 'EBAY-FRCA', 'sitename' => 'eBay Canada (French)'),
                array('language' => 'en-GB', 'country' => 'GB', 'siteid' => '3', 'sitecode' => 'EBAY-GB', 'sitename' => 'eBay UK'),
                array('language' => 'zh-Hant', 'country' => 'HK', 'siteid' => '201', 'sitecode' => 'EBAY-HK', 'sitename' => 'eBay Hong Kong'),
                array('language' => 'en-IE', 'country' => 'IE', 'siteid' => '205', 'sitecode' => 'EBAY-IE', 'sitename' => 'eBay Ireland'),
                array('language' => 'en-IN', 'country' => 'IN', 'siteid' => '203', 'sitecode' => 'EBAY-IN', 'sitename' => 'eBay India'),
                array('language' => 'it-IT', 'country' => 'IT', 'siteid' => '101', 'sitecode' => 'EBAY-IT', 'sitename' => 'eBay Italy'),
                array('language' => 'en-US', 'country' => 'US', 'siteid' => '100', 'sitecode' => 'EBAY-MOTOR', 'sitename' => 'eBay Motors'),
                array('language' => 'en-MY', 'country' => 'MY', 'siteid' => '207', 'sitecode' => 'EBAY-MY', 'sitename' => 'eBay Malaysia'),
                array('language' => 'nl-NL', 'country' => 'NL', 'siteid' => '146', 'sitecode' => 'EBAY-NL', 'sitename' => 'eBay Netherlands'),
                array('language' => 'nl-BE', 'country' => 'BE', 'siteid' => '123', 'sitecode' => 'EBAY-NLBE', 'sitename' => 'eBay Belgium(Dutch)'),
                array('language' => 'en-PH', 'country' => 'PH', 'siteid' => '211', 'sitecode' => 'EBAY-PH', 'sitename' => 'eBay Philippines'),
                array('language' => 'pl-PL', 'country' => 'PH', 'siteid' => '212', 'sitecode' => 'EBAY-PL', 'sitename' => 'eBay Poland'),
                array('language' => 'en-SG', 'country' => 'SG', 'siteid' => '216', 'sitecode' => 'EBAY-SG', 'sitename' => 'eBay Singapore'),
            );

            foreach ($sites as $row) {
                $result[] = new WPEAE_EbaySite($row, true);
            }


            return $result;
        }

    }

    

    

    

    

endif;