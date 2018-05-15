<?php

class WPEAE_BingAccessTokenAuthentication {
    /*
     * Get the access token.
     *
     * @param string $grantType    Grant type.
     * @param string $scopeUrl     Application Scope URL.
     * @param string $clientID     Application client ID.
     * @param string $clientSecret Application client ID.
     * @param string $authUrl      Oauth Url.
     *
     * @return string.
     */
     function getTokens($grantType, $scopeUrl, $clientID, $clientSecret, $authUrl){
                                    try {
            //Initialize the Curl Session.
            $ch = curl_init();
            //Create the request Array.
            $paramArr = array (
                'grant_type'    => $grantType,
                 'scope'         => $scopeUrl,
                 'client_id'     => $clientID,
                 'client_secret' => $clientSecret
            );
            //Create an Http Query.//
            $paramArr = http_build_query($paramArr);
            //Set the Curl URL.
            curl_setopt($ch, CURLOPT_URL, $authUrl);
            //Set HTTP POST Request.
            curl_setopt($ch, CURLOPT_POST, TRUE);
            //Set data to POST in HTTP "POST" Operation.
            curl_setopt($ch, CURLOPT_POSTFIELDS, $paramArr);
            //CURLOPT_RETURNTRANSFER- TRUE to return the transfer as a string of the return value of curl_exec().
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, TRUE);
            //CURLOPT_SSL_VERIFYPEER- Set FALSE to stop cURL from verifying the peer's certificate.
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            //Execute the  cURL session.
            $strResponse = curl_exec($ch);
            //Get the Error Code returned by Curl.
            $curlErrno = curl_errno($ch);
            if($curlErrno){
                $curlError = curl_error($ch);
                throw new Exception($curlError);
            }
            //Close the Curl Session.
            curl_close($ch);
            //Decode the returned JSON string.
            $objResponse = json_decode($strResponse);
            if (isset($objResponse->error)){
                throw new Exception($objResponse->error_description);
            }
            return $objResponse->access_token;
        } catch (Exception $e) {
            echo "Exception-".$e->getMessage();
        }
    }
    
    function getAzureTokens($clientKey, $authUrl){
        $result = array("state" => "ok", "data" => "");
        
        try {
            //Initialize the Curl Session.
            $ch = curl_init();
            //Set the Curl URL.
            //curl_setopt($ch, CURLOPT_URL, $authUrl . '?Subscription-Key=' . $clientKey);
            curl_setopt($ch, CURLOPT_URL, $authUrl);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Ocp-Apim-Subscription-Key: ' . $clientKey
            ));
            //Set HTTP POST Request.
            curl_setopt($ch, CURLOPT_POST, TRUE);
            //Set data to POST in HTTP "POST" Operation.
            curl_setopt($ch, CURLOPT_POSTFIELDS, ""); // $paramArr);
            //CURLOPT_RETURNTRANSFER- TRUE to return the transfer as a string of the return value of curl_exec().
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, TRUE);
            //CURLOPT_SSL_VERIFYPEER- Set FALSE to stop cURL from verifying the peer's certificate.
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            //Execute the cURL session.
            $strResponse = curl_exec($ch);
            //Get the Error Code returned by Curl.
            $curlErrno = curl_errno($ch);
            if($curlErrno){
            $curlError = curl_error($ch);
            throw new Exception($curlError);
            }
            //Close the Curl Session.
            curl_close($ch);
    
            if (substr($strResponse,0,1) === "{"){
                $jsonResponse = json_decode($strResponse);
                
                if (isset($jsonResponse->error) && isset($jsonResponse->error->message) && $jsonResponse->error->message )
                    $result = array("state" => "error", "data" => $jsonResponse->error->message, "statusCode" => $jsonResponse->error->code);
                if ($jsonResponse->message)
                    $result = array("state" => "error", "data" => $jsonResponse->message, "statusCode" => $jsonResponse->statusCode);
            }
            else $result["data"] = $strResponse;    
            
        } catch (Exception $e) {
            $result = array("state" => "error", "data" => $e->getMessage());
        }
        
        return $result;
            
    }
}
/*
 * Class:WPEAE_BingHTTPTranslator
 * 
 * Processing the translator request.
 */
Class WPEAE_BingHTTPTranslator {
    /*
     * Create and execute the HTTP CURL request.
     *
     * @param string $url        HTTP Url.
     * @param string $authHeader Authorization Header string.
     * @param string $postData   Data to post.
     *
     * @return string.
     *
     */
    function curlRequest($url, $authHeader, $postData=''){
        
      //  $my_file = fopen('tvdata.txt', 'w+');
        
        //Initialize the Curl Session.
        $ch = curl_init();
        //Set the Curl url.
        curl_setopt ($ch, CURLOPT_URL, $url);
        //Set the HTTP HEADER Fields.
        curl_setopt ($ch, CURLOPT_HTTPHEADER, array($authHeader,"Content-Type: text/xml"));
        //CURLOPT_RETURNTRANSFER- TRUE to return the transfer as a string of the return value of curl_exec().
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //CURLOPT_SSL_VERIFYPEER- Set FALSE to stop cURL from verifying the peer's certificate.
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, False);
        if($postData) {
            //Set HTTP POST Request.
            curl_setopt($ch, CURLOPT_POST, TRUE);
            //Set data to POST in HTTP "POST" Operation.
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        }
         
        //Execute the  cURL session.
        $curlResponse = curl_exec($ch);
        //Get the Error Code returned by Curl.
        $curlErrno = curl_errno($ch);
        if ($curlErrno) {
            $curlError = curl_error($ch);
            throw new Exception($curlError);
        }
        
        //Close a cURL session.
        curl_close($ch);
        
        //fwrite($my_file, $curlResponse);
       // fclose($my_file);
        return $curlResponse;
    }
     /*
     * Create Request XML Format.
     *
     * @param string $fromLanguage   Source language Code.
     * @param string $toLanguage     Target language Code.
     * @param string $contentType    Content Type.
     * @param string $inputStrArr    Input String Array.
     *
     * @return string.
     */
    function createReqXML($fromLanguage=false,$toLanguage,$contentType,$inputStrArr) {
        //Create the XML string for passing the values.
        $requestXml = "<TranslateArrayRequest>".
            "<AppId/>".
            "<From>" . ($fromLanguage ? $fromLanguage : '') . "</From>". 
            "<Options>" .
             "<Category xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\" />" .
              "<ContentType xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\">$contentType</ContentType>" .
              "<ReservedFlags xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\" />" .
              "<State xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\" />" .
              "<Uri xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\" />" .
              "<User xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\" />" .
            "</Options>" .
            "<Texts>";
        foreach ($inputStrArr as $inputStr)
        $requestXml .=  "<string xmlns=\"http://schemas.microsoft.com/2003/10/Serialization/Arrays\"><![CDATA[$inputStr]]></string>" ;
        $requestXml .= "</Texts>".
            "<To>$toLanguage</To>" .
          "</TranslateArrayRequest>";
        return $requestXml;
    }
}

/**
* Class:WPEAE_BingTranslateService
*/
Class WPEAE_BingTranslateService{
    
    private $clientSecret;
    private $clientID;
    
    private $authUrl = "https://datamarket.accesscontrol.windows.net/v2/OAuth2-13/"; 
    private $scopeUrl = "http://api.microsofttranslator.com";
    private $grantType    = "client_credentials";
    
    private $authUrlAzure = "https://api.cognitive.microsoft.com/sts/v1.0/issueToken"; 
    
    private $last_error = false;
    
    function __construct($clientSecret) {
        //$this->clientID = $clientID;
        $this->clientSecret = $clientSecret;         
    }
    
    public function getLastError(){
        return $this->last_error;    
    } 
    
    private function getAccessToken(){
        $this->last_error = false;
        //Create the AccessTokenAuthentication object.
        $authObj      = new WPEAE_BingAccessTokenAuthentication();

        //Get the Access token.
        $accessToken = $authObj->getAzureTokens($this->clientSecret, $this->authUrlAzure);
        
        if ($accessToken['state'] === "error") {
            $this->last_error = "<strong>Microsoft Azure error:</strong> " . $accessToken['data'];
            return false;
        }
        return $accessToken['data'];
                 
    }
    
    private function split_html_to_array($longString, $maxLineLength){

        $arrayWords = explode('<', $longString);

        // Auxiliar counters, foreach will use them
        $currentLength = 0;
        $index = 0;
       
        $arrayOutput = array();
        
        foreach ($arrayWords as $word) {
            
            if (empty($arrayOutput)) $arrayOutput[] = '';
            // +1 because the word will receive back the < in the end that it loses in explode()
            $wordLength = strlen($word) + 1;

            if (($currentLength + $wordLength) <= $maxLineLength) {
                $arrayOutput[$index] .=  $word . '<';

                $currentLength += $wordLength;
            } else {
                $index += 1;

                $currentLength = $wordLength;
                
                $arrayOutput[$index-1] = rtrim($arrayOutput[$index-1],"<");

                $arrayOutput[] = '<' . $word;
            }
        }
        
        $arrayOutput[$index] = rtrim($arrayOutput[$index],"<");
        
        return $arrayOutput;
    }
      
    
    function translate($text, $to, $from=false){
     
        $accessToken = $this->getAccessToken();
        
        if (!$accessToken) return $text;
        
        if (!$from && defined("WPEAE_LANGUAGE_FROM")) $from = WPEAE_LANGUAGE_FROM;
        
        if (strlen($text) > 10000 ) {
            
            $text_array = $this->split_html_to_array($text, 10000);
            
            $translatedStr = '';
            foreach ($text_array as $text_piece){
                $translatedStr .= $this->translate($text_piece, $to, $from);    
            }
            //TODO: for some reason last tag is cut
            return $translatedStr;   
        }
        
        //Create the authorization Header string.
        $authHeader = "Authorization: Bearer ". $accessToken;

        $params = "text=".urlencode($text)."&to=".$to;
        if ($from) $params .= "&from=".$from;
        $params .="&contentType=text/html";
        
        $translateUrl = "http://api.microsofttranslator.com/v2/Http.svc/Translate?$params";
        
        //Create the Translator Object.
        $translatorObj = new WPEAE_BingHTTPTranslator();
        
        //Get the curlResponse.
        $curlResponse = $translatorObj->curlRequest($translateUrl, $authHeader);
        
        $translatedStr = '';
        //Interprets a string of XML into an object.
        $xmlObj = simplexml_load_string($curlResponse);
        foreach((array)$xmlObj[0] as $val){
            $translatedStr = $val;
        } 
        
        return $translatedStr;   
    }
    
    function translateArray($str_array, $to, $from=false){

        $accessToken = $this->getAccessToken();
        
        if (!$accessToken) return false;
        
        //Create the authorization Header string.
        $authHeader = "Authorization: Bearer ". $accessToken;

        //Set the params.//
    
        $contentType  = 'text/plain';
        //Create the Translator Object.
        $translatorObj = new WPEAE_BingHTTPTranslator();
        
        if (!$from && defined("WPEAE_LANGUAGE_FROM")) $from = WPEAE_LANGUAGE_FROM;
    
        $requestXml = $translatorObj->createReqXML($from, $to, $contentType, $str_array);

        //HTTP TranslateMenthod URL.
        $translateUrl = "http://api.microsofttranslator.com/v2/Http.svc/TranslateArray";

        //Call HTTP Curl Request.
        $curlResponse = $translatorObj->curlRequest($translateUrl, $authHeader, $requestXml);
   
        $translatedStrArray = array();
        //Interprets a string of XML into an object.
        $xmlObj = simplexml_load_string($curlResponse);
        $i=0;
        
        foreach($xmlObj->TranslateArrayResponse as $translatedArrObj){
            $i++;
            $translatedStrArray[] = (string)$translatedArrObj->TranslatedText; 
        }
            
        return $translatedStrArray;
    }
} 
 
 ?>