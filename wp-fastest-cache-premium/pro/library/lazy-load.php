<?php
	class WpFastestCacheLazyLoad{
		public function __construct(){}

		public function is_lazy($img){
			//Slider Revolution
			//<img src="dummy.png" data-lazyload="transparent.png" data-bgposition="center center" data-bgfit="cover" data-bgrepeat="no-repeat" data-bgparallax="off" class="rev-slidebg" data-no-retina>
			if(preg_match("/\sdata-lazyload\=[\"\']/i", $img)){
				return true;
			}

			return false;
		}

		public function mark_attachment_page_images($attr) {
			if(isset($attr['src'])){
				if($this->is_thumbnail($attr['src'])){
					return $attr;
				}
			}

			$attr['wpfc-lazyload-disable'] = "true";
			
			return $attr;
		}

		public function is_thumbnail($src){
			// < 299x299
			if(preg_match("/\-[12]\d{0,2}x[12]\d{0,2}\.(jpg|jpeg|png)/i", $src)){
				return true;
			}

			// < 299x99
			if(preg_match("/\-[12]\d{0,2}x\d{0,2}\.(jpg|jpeg|png)/i", $src)){
				return true;
			}

			// < 99x299
			if(preg_match("/\-\d{0,2}x[12]\d{0,2}\.(jpg|jpeg|png)/i", $src)){
				return true;
			}

			// < 99x99
			if(preg_match("/\-\d{0,2}x\d{0,2}\.(jpg|jpeg|png)/i", $src)){
				return true;
			}

			return false;
		}

		public function mark_content_images($content){
			preg_match_all( '/<img[^\>]+>/i', $content, $matches);

			if(count($matches[0]) > 0){
				foreach ( $matches[0] as $img ) {
					if($this->is_thumbnail($img)){
						continue;
					}

					$tmp_img = preg_replace("/<img\s/", "<img wpfc-lazyload-disable=\"true\" ", $img);

					$content = str_replace($img, $tmp_img, $content );
				}
			}

			return $content;
		}

		public function images_to_lazyload($content, $inline_scripts) {
			if(isset($GLOBALS["wp_fastest_cache"]->noscript)){
				$inline_scripts = $inline_scripts.$GLOBALS["wp_fastest_cache"]->noscript;
			}

			preg_match_all( '/<img[^\>]+>/i', $content, $matches);

			if(count($matches[0]) > 0){
				foreach ( $matches[0] as $key => $img) {

					// don't to the replacement if the image appear in js
					if(!preg_match("/".preg_quote($img, "/")."/i", $inline_scripts)){

						// don't to the replacement if quote of src does not exist
						if(preg_match("/\ssrc\=[\"\']/i", $img)){
							
							// don't to the replacement if the image is a data-uri
							if(!preg_match("/src\=[\'\"]data\:image/i", $img)){
								if(!preg_match("/onload=[\"\']/i", $img)){
									if(preg_match("/wpfc-lazyload-disable/", $img)){
										$tmp_img = preg_replace("/\swpfc-lazyload-disable\=[\"\']true[\"\']\s*/", " ", $img);
									}else{
										if($key < 3){
											$tmp_img = $img;
										}else{
											if(preg_match("/\ssrc\=[\"\'][^\"\']+[\"\']/i", $img)){
												if(preg_match("/mc\.yandex\.ru\/watch/i", $img)){
													$tmp_img = $img;
												}else if($this->is_lazy($img)){
													$tmp_img = $img;
												}else{
													$tmp_img = $img;
													$tmp_img = preg_replace("/\ssrc\=/i", " wpfc-data-original-src=", $tmp_img);
													$tmp_img = preg_replace("/\ssrcset\=/i", " wpfc-data-original-srcset=", $tmp_img);
													$tmp_img = preg_replace("/<img\s/i", "<img onload=\"Wpfcll.r(this,true);\" src=\"".WPFC_WP_CONTENT_URL."/plugins/wp-fastest-cache-premium/pro/images/blank.gif$2\" ", $tmp_img);
												}
											}
										}
									}

									$content = str_replace($img, $tmp_img, $content);
								}
							}


						}




						
					}
				}
			}

			return $content;
		}

		public function iframe_to_lazyload($content, $inline_scripts) {
			preg_match_all('/<iframe[^\>]+>/i', $content, $matches);

			if(count($matches[0]) > 0){
				foreach ( $matches[0] as $iframe ) {
					// don't to the replacement if the frame appear in js
					if(!preg_match("/".preg_quote($iframe, "/")."/i", $inline_scripts)){
						if(!preg_match("/onload=[\"\']/i", $iframe)){
							$tmp_iframe = preg_replace("/\ssrc\=/i", " onload=\"Wpfcll.r(this,true);\" wpfc-data-original-src=", $iframe);

							$content = str_replace($iframe, $tmp_iframe, $content);
						}
					}
				}
			}

			return $content;
		}

		public function get_js_source_new(){
			$js = "\n<script data-wpfc-render=\"false\">".file_get_contents(WPFC_WP_PLUGIN_DIR."/wp-fastest-cache-premium/pro/js/lazy-load-new.js")."</script>\n";
			
			$js = preg_replace("/var\sself/", "var s", $js);
			$js = preg_replace("/self\./", "s.", $js);
			$js = preg_replace("/Wpfc_Lazyload/", "Wpfcll", $js);
			$js = preg_replace("/(\.?)init(\:?)/", "$1i$2", $js);
			$js = preg_replace("/(\.?)load_images(\:?)/", "$1li$2", $js);
			$js = preg_replace("/\s*(\=|\:|\;|\{|\}|\,)\s*/", "$1", $js);
			$js = preg_replace("/originalsrcset/", "osrcs", $js);
			$js = preg_replace("/originalsrc/", "osrc", $js);


			//$content = substr_replace($content, $js."\n"."</body>", strripos($content, "</body>"), strlen("</body>"));

			return $js;
		}

		public function get_js_source(){
			$js = "\n<script data-wpfc-render=\"false\">".file_get_contents(WPFC_WP_PLUGIN_DIR."/wp-fastest-cache-premium/pro/js/lazy-load.js")."</script>\n";
			
			$js = preg_replace("/var\sself/", "var s", $js);
			$js = preg_replace("/self\./", "s.", $js);
			$js = preg_replace("/Wpfc_Lazyload/", "Wpfcll", $js);
			$js = preg_replace("/(\.?)init(\:?)/", "$1i$2", $js);
			$js = preg_replace("/(\.?)load_images(\:?)/", "$1li$2", $js);
			$js = preg_replace("/\s*(\=|\:|\;|\{|\}|\,)\s*/", "$1", $js);
			$js = preg_replace("/originalsrcset/", "osrcs", $js);
			$js = preg_replace("/originalsrc/", "osrc", $js);


			//$content = substr_replace($content, $js."\n"."</body>", strripos($content, "</body>"), strlen("</body>"));

			return $js;
		}
	}
?>