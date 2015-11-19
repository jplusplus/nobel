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
        $project = $this->project;
        $pageName = $this->pageName;
        $endpoint = "https://wikimedia.org/api/rest_v1/metrics/pageviews/per-article/$project/all-access/all-agents/$pageName/daily/20151101/20151115";
        $json = file_get_contents($endpoint);
        $items = json_decode($json, true)['items'];
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

        $md5 = md5(serialize(array($granularity, $from)));
        $points = __c()->get($md5);
        if ( $points === null ){
            $points = array();
            $data = $this->_pageviewsPerArticle();
            foreach( $data as $item ){
                $points[] = $item['views'];
            }
            __c()->set($md5, $points, 4*60*60); // cache for 4h
        }
        return $points;
    }

}