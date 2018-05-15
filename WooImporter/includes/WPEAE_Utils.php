<?php

/* * class
 * Description of WPEAE_Utils
 *
 * @author Geometrix
 * 
 * @position: -1
 */
if (!class_exists('WPEAE_Utils')):

    class WPEAE_Utils {

        public static function remove_tags($html, $tags = array()) {
            if(function_exists('mb_convert_encoding')){
                $html = trim(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
            }else{
                $html = htmlspecialchars_decode(utf8_decode(htmlentities($html, ENT_COMPAT, 'UTF-8', false)));    
            }
            
            $dom = new DOMDocument();
            libxml_use_internal_errors(true);
            @$dom->loadHTML($html);
            libxml_use_internal_errors(false);
            $dom->formatOutput = true;

            if (!$tags) {
                $tags = array('script', 'head', 'meta', 'style', 'map', 'noscript', 'object');

                if (get_option('wpeae_remove_img_from_desc', false)) {
                    $tags[] = 'img';
                }
                if (get_option('wpeae_remove_link_from_desc', false)) {
                    $tags[] = 'a';
                }
            }
            foreach ($tags as $tag) {
                $elements = $dom->getElementsByTagName($tag);
                for ($i = $elements->length; --$i >= 0;) {
                    $e = $elements->item($i);
                    if ($tag == 'a') {
                        while ($e->hasChildNodes()) {
                            $child = $e->removeChild($e->firstChild);
                            $e->parentNode->insertBefore($child, $e);
                        }
                        $e->parentNode->removeChild($e);
                    } else {
                        $e->parentNode->removeChild($e);
                    }
                }
            }
            
            return preg_replace('~<(?:!DOCTYPE|/?(?:html|body))[^>]*>\s*~i', '', $dom->saveHTML());
        }

        public static function get_categories_tree() {
            $categories = get_terms("product_cat", array('hide_empty' => 0, 'hierarchical' => true));
            $categories = json_decode(json_encode($categories), TRUE);
            $categories = WPEAE_Utils::build_categories_tree($categories, 0);
            return $categories;
        }

        private static function build_categories_tree($all_cats, $parent_cat, $level = 1) {
            $res = array();
            foreach ($all_cats as $c) {
                if ($c['parent'] == $parent_cat) {
                    $c['level'] = $level;
                    $res[] = $c;
                    $child_cats = WPEAE_Utils::build_categories_tree($all_cats, $c['term_id'], $level + 1);
                    if ($child_cats) {
                        $res = array_merge($res, $child_cats);
                    }
                }
            }
            return $res;
        }

    }

    

    

    

    
endif;