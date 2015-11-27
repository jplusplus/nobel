<?php
/* Contains a class for querying WikiData.
*/ 
namespace Toplist;
if(!defined('TopList')) {
   die('Not permitted');
}


Class WikiDataQuery {

    function __construct( ){
    }

    /* Get the corresponding page names in other Wikipedia editions */
    function getSitelinks( $title, $originLanguage='en') {

        $sitename = $originLanguage . 'wiki';  // enwiki
        $endpoint = "https://www.wikidata.org/w/api.php?action=wbgetentities&sites=$sitename&props=sitelinks&titles=$title&format=json";
        $md5 = md5($endpoint);
        $iwLinks = __c()->get($md5);
        if ( $iwLinks === null ){
            $json = file_get_contents($endpoint);
            $response = json_decode($json, true);
            $firstEntity = reset($response['entities']);
            $iwLinks = $firstEntity['sitelinks'];
            __c()->set($md5, $iwLinks, 60 * 86400); //cache for 60 days. This would very rarely change.
        }
        array_walk($iwLinks, function( &$item, &$key ){
            $item = $item['title'];
        });
        return $iwLinks;
    }

}
