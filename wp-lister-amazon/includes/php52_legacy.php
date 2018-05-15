<?php
/**
 * make certain functions from PHP 5.3 available to PHP 5.2
 */


// str_getcsv()
if ( ! function_exists('str_getcsv') ) {
    function str_getcsv($input, $delimiter = ',', $enclosure = '"') {

        if( ! preg_match("/[$enclosure]/", $input) ) {
          return (array)preg_replace(array("/^\\s*/", "/\\s*$/"), '', explode($delimiter, $input));
        }

        $token = "##"; $token2 = "::";
        //alternate tokens "\034\034", "\035\035", "%%";
        $t1 = preg_replace(array("/\\\[$enclosure]/", "/$enclosure{2}/",
             "/[$enclosure]\\s*[$delimiter]\\s*[$enclosure]\\s*/", "/\\s*[$enclosure]\\s*/"),
             array($token2, $token2, $token, $token), trim(trim(trim($input), $enclosure)));

        $a = explode($token, $t1);
        foreach($a as $k=>$v) {
            if ( preg_match("/^{$delimiter}/", $v) || preg_match("/{$delimiter}$/", $v) ) {
                $a[$k] = trim($v, $delimiter); $a[$k] = preg_replace("/$delimiter/", "$token", $a[$k]); }
        }
        $a = explode($token, implode($token, $a));
        return (array)preg_replace(array("/^\\s/", "/\\s$/", "/$token2/"), array('', '', $enclosure), $a);

    }
}
