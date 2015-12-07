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
