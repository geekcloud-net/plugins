<?php

include_once(dirname(__FILE__) . '/WPEAE_BingTranslateService.php');

/**
 * Description of WPEAE_TranslateContent
 *
 * @author Geometrix
 */
if (!class_exists('WPEAE_TranslateContent')):
class WPEAE_TranslateContent {
    
    private $languages;
    /**
    * Microsoft Bing Translate API
    * 
    * @var WPEAE_BingTranslateService
    */
    private $translateService = false; 
    
    function __construct() {
        
        $this->setupBingService();
        
        add_action('wpeae_get_localized_url', array($this, 'get_localized_url'), 1000, 2);
        
        add_filter('wpeae_get_localized_text', array($this, 'get_localized_text'), 1000, 3);
        add_filter('wpeae_get_localized_attributes', array($this, 'get_localized_attributes'), 1000, 3);
        $this->languages['aliexpress'] = get_option('wpeae_aliexpress_language', 'en');
        add_filter('wpeae_get_localized_cookies', array($this, 'wpeae_get_localized_cookies'), 1000, 2);
    
    }
    
    function get_localized_url($url, $params){
        $current_lang = $this->languages['aliexpress'];
        
        if ($params['type'] == 'aliexpress_desc' && $current_lang != 'en'){
            $external_id = $params['external_id'];
            $url = 'http://m.aliexpress.com/item-desc/' . $external_id . '.html?site=' . $current_lang; 
        }
        
        if ($params['type'] == 'aliexpress_desc2' && $current_lang != 'en'){
            $external_id = $params['external_id'];
            $url = "http://" . $current_lang . ".aliexpress.com/getDescModuleAjax.htm?productId=" . $external_id;  
        }
        
        if ($params['type'] == 'aliexpress_request'){
            $url = $url . "&language=" . $current_lang;
        }
        
        if ($params['type'] == 'aliexpress_reviews' && $current_lang != 'en'){
            $url = str_replace('www', $current_lang, $url);
        }
        
        if ($params['type'] == 'aliexpress_categories' && $current_lang != 'en'){
            $url = str_replace('www', $current_lang, $url);
        }
        
        return $url;     
    }
    
    function wpeae_get_localized_cookies($value, $params){
        $current_lang = $this->languages['aliexpress'];
        
        if ($current_lang === 'en') return $value;
        
        if ($params['type'] == "aliexpress_xman_us_f" ) {
            if ($current_lang === 'fr'){
                return "x_l=0&x_locale=fr_FR";    
            }
            if ($current_lang === 'it'){
                return "x_l=0&x_locale=it_IT";
            }  
            
            if ($current_lang === 'ru'){
                return 'x_l=0&x_locale=ru_RU'; 
            }
            
            if ($current_lang === 'de'){
                return 'x_l=0&x_locale=de_DE';
            }
            
            if ($current_lang === 'pt'){
                return 'x_l=0&x_locale=pt_BR'; //pt    
            }
            
            if ($current_lang === 'es'){
                return 'x_l=0&x_locale=es_ES'; //es
            }
            
            if ($current_lang === 'nl'){
                return 'x_l=0&x_locale=nl_NL'; //nl dutch    
            }
            
            if ($current_lang === 'tr'){
                return 'x_l=0&x_locale=tr_TR'; //tr    
            }
            
            if ($current_lang === 'ja'){
                return 'x_l=0&x_locale=ja_JP'; //ja    
            }
            
            if ($current_lang === 'ko'){
                return 'x_l=0&x_locale=ko_KR'; //ko    
            }
            
            if ($current_lang === 'th'){
                return 'x_l=0&x_locale=th_TH'; //th    
            }
            
            if ($current_lang === 'vi'){
                return 'x_l=0&x_locale=vi_VN'; //vi   
            }
            
            if ($current_lang === 'ar'){
                return 'x_l=0&x_locale=ar_MA';//ar   
            }
            
            if ($current_lang === 'he'){
                return 'x_l=0&x_locale=iw_IL'; //he 
            }
            
            if ($current_lang === 'pl'){
                return 'x_l=0&x_locale=pl_PL'; //pl 
            }
            
            if ($current_lang === 'id'){
                return 'x_l=0&x_locale=in_ID'; //id
            }
                                
        }    
        
        if ($params['type'] == "aliexpress_aep_usuc_f" ) {
            if ($current_lang === 'fr'){
                return "site=fra&b_locale=fr_FR";    
            }
            
            if ($current_lang === 'it'){
                return "site=ita&b_locale=it_IT";
            } 
            
            if ($current_lang === 'ru'){
                return 'site=rus&b_locale=ru_RU'; 
            }
            
            if ($current_lang === 'de'){
                return 'site=deu&b_locale=de_DE';
            } 
            
            if ($current_lang === 'pt'){
                return 'site=bra&b_locale=pt_BR'; //pt  
            }
            
            if ($current_lang === 'es'){
               return 'site=esp&b_locale=es_ES';  
            }
            
            if ($current_lang === 'nl'){
                return 'site=nld&b_locale=nl_NL'; //dutch
            }
            
            if ($current_lang === 'tr'){
                return 'site=tur&b_locale=tr_TR'; //tur
            }
            
            if ($current_lang === 'ja'){
                    return 'site=jpn&b_locale=ja_JP';
            }
            
            if ($current_lang === 'ko'){
                return 'site=kor&b_locale=ko_KR';
            }
            
            if ($current_lang === 'th'){
                return 'site=tha&b_locale=th_TH';
            }
            
            if ($current_lang === 'vi'){
                return 'site=vnm&b_locale=vi_VN';
            }
    
            if ($current_lang === 'ar'){
                return 'site=ara&b_locale=ar_MA';
            }
            
            if ($current_lang === 'he'){
                return 'site=isr&b_locale=iw_IL'; //he
            }
            
            if ($current_lang === 'pl'){
                return 'site=pol&b_locale=pl_PL';
            }    
            
            if ($current_lang === 'id'){
                return 'site=idn&b_locale=in_ID';
            }    
    
        }    
    }
    
    
    function get_localized_attributes($data, $api_type){

        if ( $this->translateService ){
            $target = $this->languages[$api_type];
            
            if ($api_type === 'aliexpress'){
                $allowed_languages = array('ar', 'he', 'pl');
                if (!in_array($target, $allowed_languages) )  return $data;    
            }
            
            $names = array();
            $values = array();
            
            foreach ($data as $attr_key => $attr_val){
                $names[] = $data[$attr_key]['name'];
                $values[] = $data[$attr_key]['value'];
            }
            
            $strings = $this->translateService->translateArray( array_merge($names,$values), $target );
            
            if ($strings && is_array($strings)){
                
                list($names, $values) = array_chunk($strings, count($strings)/2);
        
                for ($i=0; $i<=count($data)-1; $i++){
                    $data[$i] = array('name'=> $names[$i], 'value'=> $values[$i]);
                }
            
            }
            
        }
        
        return $data;    
                    
    }
    
    public function get_localized_text($data, $api_type, $check_allowed_languages = true){        
        if ( $this->translateService ){  
            $target = $this->languages[$api_type];
            if ($check_allowed_languages && $api_type === 'aliexpress'){
                $allowed_languages = array('ar', 'he', 'pl');
                if (!in_array($target, $allowed_languages) )  return $data;    
            }
            $data = str_replace(array("\r\n", "\r", "\n"), "", $data);
            //TODO: max length 10 000
            $tr_data = $this->translateService->translate($data, $target);
            if ($tr_data && $target == 'ru') $data = iconv('UTF-8', 'WINDOWS-1251', $tr_data);
            else return $tr_data;
        }           
        
        return $data;        
    }
    
    private function setupBingService(){
        //$bing_client_id = get_option('wpeae_aliexpress_bing_client_id', '');
        $bing_client_secret = get_option('wpeae_aliexpress_bing_secret', '');
        
        if ($bing_client_secret) {
            $this->translateService = new WPEAE_BingTranslateService($bing_client_secret);
        }
            
            
    }
    
    
}
endif;

new WPEAE_TranslateContent();