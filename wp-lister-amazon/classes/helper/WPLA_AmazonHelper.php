<?php

class WPLA_AmazonHelper {
	
    const MARKETPLACE_CA = 24;
    const MARKETPLACE_DE = 25;
    const MARKETPLACE_US = 29;
    const MARKETPLACE_JP = 27;
    const MARKETPLACE_CN = 32;

    // ########################################

    public static function isASIN($string)
    {
        return !empty($string) &&
               $string{0} == 'B' &&
               strlen($string) == 10;
    }

    public static function isISBN($string)
    {
        $string = (string)$string;

        if (strlen($string) == 10) {

            $subTotal = 0;
            $mpBase = 10;
            for ($x=0; $x<=8; $x++) {
                $mp = $mpBase - $x;
                $subTotal += ($mp * $string{$x});
            }

            $rest = $subTotal % 11;
            $checkDigit = $string{9};
            if (strtolower($checkDigit) == "x") {
                $checkDigit = 10;
            }

            return $checkDigit == (11 - $rest);

        } elseif (strlen($string) == 13) {

            $subTotal = 0;
            for ($x=0; $x<=11; $x++) {
                $mp = ($x + 1) % 2 == 0 ? 3 : 1;
                $subTotal += $mp * $string{$x};
            }

            $rest = $subTotal % 10;
            $checkDigit = $string{12};
            if (strtolower($checkDigit) == "x") {
                $checkDigit = 10;
            }

            return $checkDigit == (10 - $rest);
        }

        return false;
    }

    //-----------------------------------------

    public function isUPC($upc)
    {
        return $this->isWorldWideId($upc,'UPC');
    }

    public function isEAN($ean)
    {
        return $this->isWorldWideId($ean,'EAN');
    }

    private function isWorldWideId($worldWideId,$type)
    {
        $adapters = array(
            'UPC' => array(
                '8'  => 'Upce',
                '12' => 'Upca'
            ),
            'EAN' => array(
                '8'  => 'Ean8',
                '13' => 'Ean13'
            )
        );

        $length = strlen($worldWideId);

        if (!isset($adapters[$type],$adapters[$type][$length])) {
            return false;
        }

        return true;

        // try {
        //     $validator = new Zend_Validate_Barcode($adapters[$type][$length]);
        //     return $validator->isValid($worldWideId);
        // } catch (Zend_Validate_Exception $e) {
        //     return true;
        // }
    }

    // ########################################

    public function getRegisterUrl($marketplace_id = NULL)
    {

        $domain = WPLA_AmazonMarket::getUrl( $marketplace_id );
        $applicationName = 'WP-Lister for Amazon';

        return 'https://sellercentral.'.
                $domain.
                '/gp/mws/registration/register.html?ie=UTF8&*Version*=1&*entries*=0&applicationName='.
                rawurlencode($applicationName).'&appDevMWSAccountId='.
                WPLA_AmazonMarket::getDeveloperKey( $marketplace_id );
    }

    public function getItemUrl($product_id, $marketplace_id = NULL)
    {

        $domain = WPLA_AmazonMarket::getUrl( $marketplace_id );

        return 'http://'.$domain.'/gp/product/'.$product_id;
    }

    public function getOrderUrl($order_id, $marketplace_id = NULL)
    {

        $domain = WPLA_AmazonMarket::getUrl( $marketplace_id );

        return 'https://sellercentral.'.$domain.'/gp/orders-v2/details/?orderID='.$order_id;
    }


    public function getCarriers()
    {
        return array(
            'usps'  => 'USPS',
            'ups'   => 'UPS',
            'fedex' => 'FedEx',
            'dhl'   => 'DHL',
            'Fastway',
            'GLS',
            'GO!',
            'Hermes Logistik Gruppe',
            'Royal Mail',
            'Parcelforce',
            'City Link',
            'TNT',
            'Target',
            'SagawaExpress',
            'NipponExpress',
            'YamatoTransport'
        );
    }

    public function downloadImage($url, $imagePath)
    {
        // Prepare image file
        // ---------
        $fileHandler = fopen($imagePath, 'w+');
        // ---------

        // Send request
        // ---------
        $curlHandler = curl_init();
        curl_setopt($curlHandler, CURLOPT_URL, $url);

        curl_setopt($curlHandler, CURLOPT_FILE, $fileHandler);
        curl_setopt($curlHandler, CURLOPT_REFERER, $url);
        curl_setopt($curlHandler, CURLOPT_AUTOREFERER, 1);

        curl_exec($curlHandler);
        curl_close($curlHandler);

        fclose($fileHandler);
        // ---------

        // Check if download was successful
        // ---------
        $imageInfo = is_file($imagePath) ? getimagesize($imagePath) : NULL;

        if (empty($imageInfo)) {
            throw new Exception("Image {$url} was not downloaded.");
        }
        // ---------
    }


    // ---------------------------------------

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

        echo "<pre>Model / Connector / Server / Protocol / sendRequestAsPost</pre>";
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

        echo "<pre>Model / Connector / Server / Protocol / sendRequestAsGet</pre>";
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

} // class WPLA_AmazonHelper

