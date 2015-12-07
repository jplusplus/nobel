<?php
/* Contains a parent class for classes querying external API's
*/ 
namespace Toplist;
if(!defined('TopList')) {
   die('Not permitted');
}

Class ExternalData {

    var $endPoint;

    function __construct(){
    }

    /* Call an external json API and json decode,
       if not in cache already.
       $cacheTime in hours
    */
    function fetchAndCache( $url, $cacheTime, $cb = null ){
        $cacheKey = 'ED-' . md5( $url );
        $result = __c()->get( $cacheKey );
        if ( $result === null ){
            $json = file_get_contents( $url );
            $result = json_decode( $json, true );
            if ( is_callable( $cb ) ){
                $result = $cb( $result );
            }
            __c()->set( $cacheKey, $result, $cacheTime * 3600 ); //cache for cacheTime hours
        }
        return $result;
    }

}


/* For querying SPAQRL API's */
Class ExternalDataSparql extends ExternalData {

    function __construct(){
    }

    /* Joins an array and prefixes each element */
    function _joinAndAffix( $list, $glue, $prefix = "", $suffix = "" ){
        array_walk(
            $list,
            function(&$value, $key, $affix) { 
                $value = $affix[0] . $value . $affix[1];
            }, array($prefix, $suffix));
        return implode($glue, $list);
    }

    /* URI encode only path of uri (i.e. keep slashes in hostname etc) */
    function _encodeUri( $uri ){
        $encodedUri = rawurlencode($uri);
        $encodedUri = str_replace('%2F', '/', $encodedUri);
        $encodedUri = str_replace('%3A', ':', $encodedUri);
        $encodedUri = str_replace('%2C', ',', $encodedUri);
        $encodedUri = str_replace('%27', "'", $encodedUri);
        $encodedUri = str_replace('%28', "(", $encodedUri);
        $encodedUri = str_replace('%29', ")", $encodedUri);
        
        return $encodedUri;
    }

    /* Call an external SPARQL API, if not in cache already.
       $cacheTime in hours
    */
    function fetchAndCache( $query, $cacheTime, $cb = null ){
        $cacheKey = 'ED-' . md5( $query );
        $result = __c()->get( $cacheKey );
        if ( $result === null ){
            $result = $this->endPoint->query($query);
            $result = $result["result"]["rows"];
            if ( is_callable( $cb ) ){
                $result = $cb( $result );
            }
            __c()->set( $cacheKey, $result, $cacheTime * 3600 ); //cache for cacheTime hours
        }
        return $result;
    }

}
