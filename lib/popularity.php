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
        global $gPageStatsAPI;
        $json = file_get_contents( $gPageStatsAPI );
        /* The API actually doesn't return JSON, but a JS style object */
        /* Adding quotes arounc the keys will allow us to parse it. */
        $json = preg_replace('/([{\[,])\s*([a-zA-Z0-9_]+?):/', '$1"$2":', $json);
        $this->list = json_decode($json, true)["pageviews"];
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
        $pageName = $this->pageName;
        $endpoint = "https://wikimedia.org/api/rest_v1/metrics/pageviews/per-article/$project/all-access/all-agents/$pageName/daily/$from/$to";
        
        $md5 = md5($endpoint);
        $items = __c()->get($md5);
        if ($items === null){
            $json = file_get_contents($endpoint);
            $items = json_decode($json, true)['items'];
            global $gExternalLaureateDataCacheTime;
            __c()->set($md5, $items, $gExternalLaureateDataCacheTime*3600);
        }

        return $items;
    }

    function getViews( $from=null ){

        $data = $this->_pageviewsPerArticle();
        $count = 0;
        foreach( $data as $item ){
            $count += $item['views'];
        }
        return $count;
    }

    function getPoints( $granularity, $from ){

        $points = array();
        $data = $this->_pageviewsPerArticle( $from );
        foreach( $data as $item ){
            $points[] = $item['views'];
        }
        $chunks = array_chunk ( $points , $granularity );
        $outdata = array();
        foreach( $chunks as $chunk){
            $outdata[] = array_sum($chunk);
        }
        return $outdata;
    }

}