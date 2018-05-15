<?php
  if (!function_exists('wpeae_ali_forbidden_words')){
	function wpeae_ali_forbidden_words($text){
	
		$forbidden_words = get_option('wpeae_ali_forbidden_words', 'aliexpress,china');
		
		if (!empty($forbidden_words)){
			$forbidden_words = explode(',', $forbidden_words);
			
			foreach ($forbidden_words as $word){
				$word = trim($word);
				$text = str_ireplace($word, "", $text);    
			}    
		}
		
		return trim($text);  
	}    
}
?>
