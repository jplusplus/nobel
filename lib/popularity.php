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
    function getOrdered(){

        $orderedList = $this->list;
        /* sort by most recent value */
        uasort($orderedList, function ($a, $b){
            return ($a[0] < $b[0]) ? 0 : 1;
        });
        return array_keys($orderedList);
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

    function __construct($lids){

        $this->list = array();
        // TODO: cache
        /* First get all WP ids from dbPedia */
        $dbPediaQuery = new DbPediaQuery($lids);
        $wpNames = $dbPediaQuery->get();

        /* Then query Wikimedia API for stats */
        foreach( $wpNames as $dbpedia => $wp){
            $project = 'en';
            $endpoint = "https://wikimedia.org/api/rest_v1/metrics/pageviews/per-article/$project.wikipedia/all-access/all-agents/$wp/daily/20151101/20151115";
            $json = file_get_contents($endpoint);
            $items = json_decode($json, true)['items'];
            $count = 0;
            foreach( $items as $item ){
                $count += $item['views'];
            }
            $this->list[$dbpedia] = $count;
        }

    }

}