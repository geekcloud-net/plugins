<?php
// BSF CORE commom functions
if(!function_exists('bsf_get_option')) {
	function bsf_get_option($request = false) {
		$bsf_options = get_option('bsf_options');
		if(!$request)
			return $bsf_options;
		else
			return (isset($bsf_options[$request])) ? $bsf_options[$request] : false;
	}
}
if(!function_exists('bsf_update_option')) {
	function bsf_update_option($request, $value) {
		$bsf_options = get_option('bsf_options');
		$bsf_options[$request] = $value;
		return update_option('bsf_options', $bsf_options);
	}
}

/*
* Generate RGB colors from given HEX color
*
* @function: ultimate_hex2rgb()
* @Package: Ultimate Addons for Visual Compoer
* @Since: 2.1.0
* @param: $hex - HEX color value
* 		  $opecaty - Opacity in float value
* @returns: value with rgba(r,g,b,opacity);
*/
if(!function_exists('ultimate_hex2rgb')){
	function ultimate_hex2rgb($hex,$opacity=1) {
	   $hex = str_replace("#", "", $hex);
	   if(strlen($hex) == 3) {
		  $r = hexdec(substr($hex,0,1).substr($hex,0,1));
		  $g = hexdec(substr($hex,1,1).substr($hex,1,1));
		  $b = hexdec(substr($hex,2,1).substr($hex,2,1));
	   } else {
		  $r = hexdec(substr($hex,0,2));
		  $g = hexdec(substr($hex,2,2));
		  $b = hexdec(substr($hex,4,2));
	   }
	   $rgba = 'rgba('.$r.','.$g.','.$b.','.$opacity.')';
	   //return implode(",", $rgb); // returns the rgb values separated by commas
	   return $rgba; // returns an array with the rgb values
	}
}


// return responsive data
function get_ultimate_vc_responsive_media_css($args) {
	$content = '';
	if(isset($args) && is_array($args)) {
		//  get targeted css class/id from array
		if (array_key_exists('target',$args)) {
			if(!empty($args['target'])) {
				$content .=  " data-ultimate-target='".esc_attr( $args['target'] )."' ";
			}
		}

		//  get media sizes
		if (array_key_exists('media_sizes',$args)) {
			if(!empty($args['media_sizes'])) {
				$content .=  " data-responsive-json-new='".json_encode( $args['media_sizes'] )."' ";
			}
		}
	}
	return $content;
}

/* Single Image Param */

/**   Filter for image uploader
 *
 * @args    null|null
 *     or   null|URL
 *     or   ID|URL
 * @return  array|json
 *-------------------------------------------------*/
if(!function_exists('ult_img_single_init')) {
    function ult_img_single_init( $content = null, $data = '', $size = 'full' ){

      $final = '';

      if($content!='' && $content!='null|null') {

        //  Create an array
        $mainStr = explode('|', (string)$content);
        $string = '';
        $mainArr = array();

        $temp_id = $mainStr[0];
        $temp_url = (isset($mainStr[1])) ? $mainStr[1] : 'null';

        if( !empty($mainStr) && is_array($mainStr) ) {
          foreach ($mainStr as $key => $value) {
            if( !empty($value) ) {
            	if(stripos($value, '^') !== false) {
            		$tmvav_array = explode('^', $value);
	            	if(is_array($tmvav_array) && !empty($tmvav_array)) {
	            		if(!empty($tmvav_array)) {
		            		if(isset($tmvav_array[0])) {
		            			$mainArr[$tmvav_array[0]] = (isset($tmvav_array[1])) ? $tmvav_array[1] : '';
		            		}
		            	}
	            	}
            	}
            	else {
            		$mainArr['id'] = $temp_id;
            		$mainArr['url'] = $temp_url;
            	}
            }
          }
        }

        if($data!='') {
          switch ($data) {
            case 'url':     // First  - Priority for ID
                            if( !empty($mainArr['id']) && $mainArr['id'] != 'null' ) {

                              $Image_Url = '';
                              //  Get image URL, If input is number - e.g. 100x48 / 140x40 / 350x53
                              if( preg_match('/^\d/', $size) === 1 ) {
                                $size = explode('x', $size);

                                //  resize image using vc helper function - wpb_resize
                                $img = wpb_resize( $mainArr['id'], null, $size[0], $size[1], true );
                                if ( $img ) {
                                  $Image_Url = $img['url']; // $img['width'], $img['height'],
                                }

                              } else {

                                //  Get image URL, If input is string - [thumbnail, medium, large, full]
                                $hasImage = wp_get_attachment_image_src( $mainArr['id'], $size ); // returns an array
                                $Image_Url = $hasImage[0];
                              }

                              if( isset( $Image_Url ) && !empty( $Image_Url ) ) {
                                $final = $Image_Url;
                              } else {

                                //  Second - Priority for URL - get {image from url}
                                if(isset($mainArr['url']))
                                  $final = ult_get_url($mainArr['url']);

                              }
                            } else {
                              //  Second - Priority for URL - get {image from url}
                              if(isset($mainArr['url']))
                                $final = ult_get_url($mainArr['url']);
                            }
            break;
            case 'title':
            	$final = isset($mainArr['title']) ? $mainArr['title'] : get_post_meta($mainArr['id'], '_wp_attachment_image_title', true);
            break;
            case 'caption':
            	$final = isset($mainArr['caption']) ? $mainArr['caption'] : get_post_meta($mainArr['id'], '_wp_attachment_image_caption', true);
            break;
            case 'alt':
            	$final = isset($mainArr['alt']) ? $mainArr['alt'] : get_post_meta($mainArr['id'], '_wp_attachment_image_alt', true);
            break;
            case 'description':
            	$final = isset($mainArr['description']) ? $mainArr['description'] : get_post_meta($mainArr['id'], '_wp_attachment_image_description', true);
            break;
            case 'json':
                          $final = json_encode($mainArr);
              break;

            case 'sizes':
                          $img_size = getImageSquereSize( $img_id, $img_size );

                          $img = wpb_getImageBySize( array(
                            'attach_id' => $img_id,
                            'thumb_size' => $img_size,
                            'class' => 'vc_single_image-img'
                          ) );
                          $final = $img;
              break;

            case 'array':
            default:
                          $final = $mainArr;
              break;

          }
        }
      }

      return $final;
    }
	add_filter('ult_get_img_single', 'ult_img_single_init',10,3);
}

if(!function_exists('ult_get_url')) {
    function ult_get_url($img) {
		if( isset($img) && !empty($img) ) {
        	return $img;
      	}
    }
}

//  USE THIS CODE TO SUPPORT CUSTOM SIZE OPTION
if(!function_exists('getImageSquereSize')) {
    function getImageSquereSize( $img_id, $img_size ) {
      	if ( preg_match_all( '/(\d+)x(\d+)/', $img_size, $sizes ) ) {
        	$exact_size = array(
          		'width' => isset( $sizes[1][0] ) ? $sizes[1][0] : '0',
          		'height' => isset( $sizes[2][0] ) ? $sizes[2][0] : '0',
        	);
      	} else {
        	$image_downsize = image_downsize( $img_id, $img_size );
        	$exact_size = array(
        	  'width' => $image_downsize[1],
        	  'height' => $image_downsize[2],
        	);
      	}
      	
      	if ( isset( $exact_size['width'] ) && (int) $exact_size['width'] !== (int) $exact_size['height'] ) {
        	$img_size = (int) $exact_size['width'] > (int) $exact_size['height'] 
        		? $exact_size['height'] . 'x' . $exact_size['height']
          		: $exact_size['width'] . 'x' . $exact_size['width'];
      	}

      	return $img_size;
    }
}

/* Ultimate Box Shadow */
if ( !function_exists('ultimate_get_box_shadow') ) {
	
	function ultimate_get_box_shadow( $content = null, $data = '' ){
        //    e.g.    horizontal:14px|vertical:20px|blur:30px|spread:40px|color:#81d742|style:inset|
      	$final = '';

      	if($content!='') {

	        //  Create an array
	        $mainStr = explode('|', $content);
	        $string = '';
	        $mainArr = array();
	        if( !empty($mainStr) && is_array($mainStr) ) {
	          foreach ($mainStr as $key => $value) {
	            if(!empty($value)) {
	              $string=explode(":",$value);
	              if(is_array($string)) {
	                if( !empty($string[1]) && $string[1] != 'outset' ) {
	                  $mainArr[$string[0]]=$string[1];
	                }
	              }
	            }
	          }
	        }

	        $rm_bar = str_replace("|","",$content);
	        $rm_colon = str_replace(":"," ",$rm_bar);
	        $rmkeys = str_replace("horizontal","",$rm_colon);
	        $rmkeys = str_replace("vertical","",$rmkeys);
	        $rmkeys = str_replace("blur","",$rmkeys);
	        $rmkeys = str_replace("spread","",$rmkeys);
	        $rmkeys = str_replace("color","",$rmkeys);
	        $rmkeys = str_replace("style","",$rmkeys);
	        $rmkeys = str_replace("outset","",$rmkeys);     // Remove outset from style - To apply {outset} box shadow

	        if($data!='') {
	          switch ($data) {
	            case 'data':
	                          $final  = $rmkeys;
	              break;
	            case 'array':
	                          $final = $mainArr;
	              break;
	            case 'css':
	            default:
	                      $final  = 'box-shadow:'.$rmkeys.';';
	              break;
	          }
	        } else {
	          $final  = 'box-shadow:'.$rmkeys.';';
	        }
      	}

      	return $final;
   	}
	
	add_filter('Ultimate_GetBoxShadow', 'ultimate_get_box_shadow',10,3);
}
