<?php
/* Contains classes for fetching popularity stats (page view) for laureates
*/ 
namespace Toplist;
if(!defined('TopList')) {
   die('Not permitted');
}
require_once $baseDir . 'lib/wikipedia-stats.php';

/* Base class for popularity lists */
Class PopularityList {

    var $list;

    function __construct(){
    }

    /* Return a laureate list ordered by popularity */
    function getOrdered( $onlyKeys=true ){

        $orderedList = $this->list;
        /* sort by most recent value */
        uasort($orderedList, function ($a, $b){
            return ($a[0] < $b[0]) ? 0 : 1;
        });
        if ($onlyKeys){
            return array_keys($orderedList);
        } else {
            return $orderedList;
        }
    }
}


/* Popularity list based on nobelprize.org pageviews */
Class OnsitePopularityList extends PopularityList {

    function _fetch( $url ){
        $json = file_get_contents( $url );
        /* The API actually doesn't return JSON, but a JS style object */
        /* Adding quotes arounc the keys will allow us to parse it. */
        $json = preg_replace('/([{\[,])\s*([a-zA-Z0-9_]+?):/', '$1"$2":', $json);
        $json = str_replace(',"0": [, , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , ]', '', $json);
        $response = json_decode($json, true);
        return $response["pageviews"];

    }

    function __construct(){
        global $gStatsToplistAPI;
        global $gCacheLocal;
        if ($gCacheLocal){
            $cacheKey = 'LD-' . md5( $gStatsToplistAPI );
            $result = __c()->get( $cacheKey );
            if ( $result === null ){
                $result = $this->_fetch($gStatsToplistAPI);
                global $gExternalStatsCacheTime;
                __c()->set( $cacheKey, $result, $gExternalStatsCacheTime * 3600 );
            }
        } else {
            $result = $this->_fetch($gStatsToplistAPI);
        }

        $this->list = $result;
    }

    /* TODO code duplication with article stats */
    function getIndividual( $id, $granularity ){
        $list = $this->list[$id];

        $chunks = array_chunk ( $list , $granularity );

        /* Normalize last chunk */
        $lastChunk = array_pop($chunks);
        $lastCount = ( array_sum($lastChunk) / count($lastChunk) ) * $granularity;

        $outdata = array();
        foreach( $chunks as $chunk){
            $outdata[] = array_sum($chunk);
        }
        $outdata[] = $lastCount;
        return $outdata;

    }

}


/* Popularity list based on enwp(?) view counts */
Class WikipediaPopularityList extends PopularityList {

    /* Start with an array of Wikipedia article names */
    function __construct( $articles ){

        $this->list = array();

        /* query Wikimedia API for stats */
        foreach( $articles as $id => $wp){
            $article = new ArticleStats( $wp );
            $this->list[$id] = $article->getViews();
        }

    }

    function getOrdered( $onlyKeys=true ){

        $orderedList = $this->list;
        /* sort by most recent value */
        arsort($orderedList);
        if ($onlyKeys){
            return array_keys($orderedList);
        } else {
            return $orderedList;
        }
    }

}

/* Represents visitor stats for a Wikimedia project article */
Class ArticleStats {

    var $project;
    var $pageName;

    function __construct( $pageName, $project='en.wikipedia' ){
        $this->project = $project;
        $this->pageName = $pageName;
    }

    function _pageviewsPerArticle( $from=null, $to=null ){

        /* Default $to is yesterday */
        if ($to === null) {
            $to = WikistatsQuery::createDateString( 'yesterday' );
        }
        /* Default $from is two weeks ago */
        if ($from === null) {
            $from = WikistatsQuery::createDateString( '-2 weeks' );
        }

        $wikistats = new WikistatsQuery();
        $items = $wikistats->getPageViews( $this->project, $this->pageName, $from, $to );
        return $items;
    }

    function getViews( $from=null ){

        $data = $this->_pageviewsPerArticle();
        if ($data === null) {
            return null;
        }
        $count = 0;
        foreach( $data as $item ){
            $count += $item['views'];
        }
        return $count;
    }

    function getPoints( $granularity, $from ){

        $data = $this->_pageviewsPerArticle( $from );
        if ($data === null) {
            return null;
        }

        $points = array();
        foreach( $data as $item ){
            $points[] = $item['views'];
        }
        $chunks = array_chunk ( $points , $granularity );

        /* Normalize last chunk */
        $lastChunk = array_pop($chunks);
        $lastCount = ( array_sum($lastChunk) / count($lastChunk) ) * $granularity;

        $outdata = array();
        foreach( $chunks as $chunk){
            $outdata[] = array_sum($chunk);
        }
        $outdata[] = $lastCount;
        return $outdata;
    }

}