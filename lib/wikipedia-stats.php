<?php
/* Contains a class for querying wikimedia.org for
   Wikipedia page view statistics.
*/ 
namespace Toplist;
if(!defined('TopList')) {
   die('Not permitted');
}

require_once $baseDir . 'lib/external-data.php';

Class WikistatsQuery extends ExternalData {

    var $endPoint = 'https://wikimedia.org/api/rest_v1/metrics/pageviews/per-article/';

    function __construct( ){
    }

    /* Get the corresponding page names in other Wikipedia editions */
    function getPageViews( $proj, $title, $from, $to ) {

        $params = array(
            $proj,
            'all-access',
            'all-agents',
            str_replace(' ', '_', $title),
            'daily',
            $from,
            $to,
            );
        $url = $this->endPoint . implode( '/', $params );
        global $gExternalLaureateDataCacheTime;
        $response = $this->fetchAndCache( $url, 60 * $gExternalLaureateDataCacheTime, function( $res ){
            if (array_key_exists('items', $res)){
                $items = $res['items'];
            } else {
                $items = null;
            }
            return $items;
        });
        return $response;
    }

}
