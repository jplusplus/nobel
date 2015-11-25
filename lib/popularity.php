<?php
/* Contains classes for fetching popularity stats (page view) for laureates
*/ 
namespace Toplist;
if(!defined('TopList')) {
   die('Not permitted');
}


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

    function __construct(){
        global $gStatsToplistAPI;
        $json = file_get_contents( $gStatsToplistAPI );
        /* The API actually doesn't return JSON, but a JS style object */
        /* Adding quotes arounc the keys will allow us to parse it. */
        $json = preg_replace('/([{\[,])\s*([a-zA-Z0-9_]+?):/', '$1"$2":', $json);
        $json = str_replace(',"0": [, , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , , ]', '', $json);
        $this->list = json_decode($json, true)["pageviews"];
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

        global $gTimezone;
        /* Default $to is yesterday */
        if ($to === null){
            $date = new \DateTime('now', new \DateTimeZone($gTimezone));
            $date->add(\DateInterval::createFromDateString('yesterday'));
            $to = $date->format('Ymd');
        }
        /* Default $from is two weeks ago */
        if ($from === null){
            $date = new \DateTime('now', new \DateTimeZone($gTimezone));
            $date->add(\DateInterval::createFromDateString('-2 weeks'));
            $from = $date->format('Ymd');
        }

        $project = $this->project;
        $pageName = str_replace(' ', '_', $this->pageName);

        $endpoint = "https://wikimedia.org/api/rest_v1/metrics/pageviews/per-article/$project/all-access/all-agents/$pageName/daily/$from/$to";
        $md5 = md5($endpoint);
        $items = __c()->get($md5);
        if ($items === null){
            $json = file_get_contents($endpoint);
            $response = json_decode($json, true);
            if (array_key_exists('items', $response)){
                $items = $response['items'];
            } else {
                $items = null;
            }
            global $gExternalLaureateDataCacheTime;
            __c()->set($md5, $items, $gExternalLaureateDataCacheTime*3600);
        }

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