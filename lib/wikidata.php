<?php
/* Contains a class for querying WikiData.
*/ 
namespace Toplist;
if(!defined('TopList')) {
   die('Not permitted');
}

Class WikiDataQuery extends ExternalData {

    function __construct( ){
    }

    /* Get the corresponding page names in other Wikipedia editions */
    function getSitelinks( $title, $originLanguage='en') {

        $sitename = $originLanguage . 'wiki';  // enwiki
        $endPoint = "https://www.wikidata.org/w/api.php?action=wbgetentities&sites=$sitename&props=sitelinks&normalize&titles=$title&format=json";
        /* Cache for 60 days */
        $response = $this->fetchAndCache( $endPoint, 60 * 24, function( $res ){
            $firstEntity = reset($res['entities']);
            $iwLinks = $firstEntity['sitelinks'];
            array_walk($iwLinks, function( &$item, &$key ){
                $item = $item['title'];
            });
            return $iwLinks;
        });
        return $response;
    }

}
