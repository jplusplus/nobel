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

    /* Create a datestring for the Wikimedia API,
       based on an offset
    */
    static function createDateString( $dateString='yesterday' ){
        global $gTimezone;
        $date = new \DateTime( 'now', new \DateTimeZone( $gTimezone ) );
        $date->add(\DateInterval::createFromDateString( $dateString ));
        return $date->format('Ymd');
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
        $response = $this->fetchAndCache( $url, 60 * 24, function( $res ){
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
