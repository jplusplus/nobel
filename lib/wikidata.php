<?php
/* Contains a class for querying WikiData.
*/ 
namespace Toplist;
if(!defined('TopList')) {
   die('Not permitted');
}

require_once $baseDir . 'lib/external-data.php';

Class WikiDataQuery extends ExternalData {

    var $endPoint = "https://www.wikidata.org/w/api.php";

    function __construct( ){
    }

    /* Get the corresponding page names in other Wikipedia editions */
    function getSitelinks( $title, $originLanguage='en') {

        $sitename = $originLanguage . 'wiki';  // enwiki
        $params = array(
            'action' => 'wbgetentities',
            'sites'  => $sitename,
            'props'  => 'sitelinks',
            'normalize' => null,
            'titles' => $title,
            'format' => 'json'
            );
        $url = $this->endPoint . '?' . http_build_query( $params );
        /* Cache for 60 days */
        $response = $this->fetchAndCache( $url, 60 * 24, function( $res ){
            $firstEntity = reset($res['entities']);
            if ( !array_key_exists('sitelinks', $firstEntity)){
                return array();
            }
            $iwLinks = $firstEntity['sitelinks'];
            array_walk($iwLinks, function( &$item, &$key ){
                $item = $item['title'];
            });
            return $iwLinks;
        });
        return $response;
    }

}
