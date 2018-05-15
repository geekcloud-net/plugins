<?php
	class WpFcMobileCache{
		private $folder_name = "wpfc-mobile-cache";
		private $wptouch = false;

		public function __construct(){

		}

		public function set_wptouch($status){
			$this->wptouch = $status;
		}

		public function delete_cache($wpContentDir){
			$mobile_cache_path = $wpContentDir."/cache/wpfc-mobile-cache";

			if(is_dir($mobile_cache_path)){
				if(is_dir($wpContentDir."/cache/tmpWpfc")){
					rename($mobile_cache_path, $wpContentDir."/cache/tmpWpfc/mobile_".time());
				}else if(@mkdir($wpContentDir."/cache/tmpWpfc", 0755, true)){
					rename($mobile_cache_path, $wpContentDir."/cache/tmpWpfc/mobile_".time());
				}
			}
		}

		public function update_htaccess($data){
			preg_match("/RewriteEngine\sOn(.+)/is", $data, $out);
			$htaccess = "\n##### mobile #####\n";
			$htaccess .= $out[0];

			if($this->wptouch){
				$wptouch_rule = "RewriteCond %{HTTP:Cookie} !^.*(wptouch-pro-view=desktop).*$";
				$htaccess = str_replace("RewriteCond %{HTTP:Cookie}", $wptouch_rule."\n"."RewriteCond %{HTTP:Cookie}", $htaccess);
			}

			$htaccess = str_replace("RewriteCond %{HTTP:Cookie} !safirmobilswitcher=mobil", "RewriteCond %{HTTP:Cookie} !safirmobilswitcher=masaustu", $htaccess);
			$htaccess = str_replace("RewriteCond %{HTTP_USER_AGENT} !^.*", "RewriteCond %{HTTP_USER_AGENT} ^.*", $htaccess);
			$htaccess = preg_replace("/\/cache\/all\//", "/cache/".$this->get_folder_name()."/", $htaccess);

			//$htaccess = preg_replace("/(\/cache\/)[^\/]+(\/.{1}1\/index\.html)/","$1".$this->get_folder_name()."$2", $htaccess);
			$htaccess .= "\n##### mobile #####\n";

			return $htaccess;
		}

		public function get_folder_name(){
			return $this->folder_name;
		}
	}
?>