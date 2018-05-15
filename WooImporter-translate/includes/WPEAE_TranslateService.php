<?php

/**
 * Description of WPEAE_TranslateService
 *
 * @author Geometrix
 */

 if (!class_exists('WPEAE_TranslateService')):
 
class WPEAE_TranslateService
{

	/**
	 * @param string $source
	 * @param string $target
	 * @param string $text
	 * @return string
	 */
	public static function translate($source, $target, $text) {

	
		$response = self::request($source, $target, $text);
		$start_array = array("id=result_box class=\"short_text\">","id=result_box class=\"long_text\">", "onmouseout=\"this.style.backgroundColor='#fff'\">");
		$response = self::parseResponse($start_array, "</span></div>", strval($response));
		
		$response = self::clean($response);

		return $response;
	}

	/**
	 * @param string $source
	 * @param string $target
	 * @param string $text
	 * @return array
	 */
	protected static function request($source, $target, $text) {

		
		$url = "https://translate.google.com/";

		$fields = array(
			'sl' => urlencode($source),
			'tl' => urlencode($target),
			'js' => urlencode('n'),
			'prev' => urlencode('_t'),
			'hl' => urlencode($source),
			'ie' => urlencode('UTF-8'),
			'text' => urlencode($text),
			'file' => urlencode(''),
			'edit-text' => urlencode('')
		);

		
		$fields_string = "";
		foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
			rtrim($fields_string, '&');

		
		$ch = curl_init();

	
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, count($fields));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_USERAGENT,"Mozilla/5.0 (Windows; U; Windows NT 5.1; ru-RU; rv:1.7.12) Gecko/20050919 Firefox/1.0.7");

		
		$result = curl_exec($ch);

		
		curl_close($ch);

		return $result;
	}

	/**
	 * @param string $start
	 * @param string $end
	 * @param string $string
	 * @return string
	 */
	protected static function parseResponse($start_array = "",$end = "", $string) {
		
		$temp = 0;
		foreach ($start_array as $start){
			
			$ss=strpos($string, $start);
			 if ($ss > 0) {
				 $temp = strpos($string, $start) + strlen($start);
				 break;
			 }
			 
		}
	   
		$result = substr($string, $temp, strlen($string));
		$dd = strpos($result, $end);
			
		if($dd == 0){
			$dd = strlen($result);
		}
		return substr($result, 0 ,$dd);
	}

	/**
	 * @param string
	 * @return string
	 */
	protected static function clean($str) {
		$str = strip_tags($str);
		$str = trim($str);
		$str = html_entity_decode($str, ENT_COMPAT, 'UTF-8');
		return $str;
	}

}

endif;