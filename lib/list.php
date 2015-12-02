<?php
/* This is the entry point for all PHP scripts */
namespace Toplist;
if(!defined('TopList')) {
   die('Not permitted');
}
if(!defined('SETTINGS')) {
    require __DIR__ . '/settings.php';
}

require $baseDir . 'vendor/autoload.php';
require $baseDir . 'lib/db.php';
require $baseDir . 'lib/dbpedia.php';
require $baseDir . 'lib/wikidata.php';
require $baseDir . 'lib/popularity.php';

/* This class represents a laureate top list. */
class TList {
    var $list_length;
    var $parameters;
    var $profileDataFile;
    static $validationRules = array (
            'length'    => 'integer|min_numeric,3|max_numeric,50',
            'award'     => 'alpha_dash',
            'gender'    => 'alpha',
            'region'    => 'alpha_dash',
            'popularity'=> 'alpha_dash',
        );
    static $filterRules = array(
            'length'    => 'trim|sanitize_numbers',
            'award'     => 'trim|sanitize_string',
            'gender'    => 'trim|sanitize_string',
            'region'    => 'trim',
            'popularity'=> 'trim|sanitize_string'
        );

    /* Constructor. Will parse the parameters. */
    function __construct( $parameters ) {
        global $baseDir;
        $this->profileDataFile = $baseDir . 'data/profile-pages.csv';

        /* Validate parameters. Do not accept any invalid value */
        $gump = new \GUMP();
        $parameters = $gump->sanitize($parameters);
        $gump->validation_rules( self::$validationRules );
        $gump->filter_rules( self::$filterRules );

        $parameters = $gump->run($parameters);

        if($parameters === false) {
            global $debugLevel;
            if ( $debugLevel >= DEBUG ) {
                echo $gump->get_readable_errors( true );
            }
            $parameters = array();
        }

        global $maxListItems;
        $this->list_length = isset($parameters['length']) ? $parameters['length'] : $maxListItems;
        $this->parameters = $parameters;

    }

    /* Get all allowed parameters */
    static function getParameters(){
        return array_keys(self::$validationRules);
    }

    /* Get data for all laureates matching the filter */
    function getData() {

        $query = new SPARQLQuery($this->parameters);
        $list = $query->get();
        // Laureate id's, for looking up Wikipedia links
        $lids = array_map(function ($l) {return $l['dbPedia'];}, $list);

        // Add link and image, replace underscore in award names
        foreach ($list as &$row) {
            global $gProfilePageUrl;
            $row['url'] = sprintf($gProfilePageUrl, $row['id']);
            global $gImageAPI;
            $row['image'] = sprintf($gImageAPI, $row['id']);
            array_walk($row["awards"], function (&$v, $k){
                $v['award'] = str_replace("_", " ", $v['award']);
            });

        }
        unset($row); // PHP is weird, but see http://php.net/manual/en/control-structures.foreach.php

        if ( array_key_exists('popularity', $this->parameters) && $this->parameters['popularity'] === 'wikipedia'){

            global $gExternalDataListsCacheTime;
            /* Get and cache all WP ids from dbPedia */
            $md5 = md5(serialize($list));
            $wpNames = null;//__c()->get($md5);
            if ( $wpNames === null ){
                $dbPediaQuery = new DbPediaQuery($lids);
                $wpNames = $dbPediaQuery->getWikipediaNames();
                __c()->set($md5, $wpNames, $gExternalDataListsCacheTime*3600);
            }

            /* Get and cache most viewed list for this subset of laureates */
            $md5 = md5(serialize($lids));
            $orderedList = null;//__c()->get($md5);
            if ( $orderedList === null ){
                $popularityList = new WikipediaPopularityList($wpNames);
                $orderedList = $popularityList->getOrdered();
                __c()->set($md5, $orderedList, $gExternalDataListsCacheTime*3600);
            }
            usort($list, function($a, $b) use ($orderedList){
                $ida = $a['dbPedia'];
                $idb = $b['dbPedia'];
                $posa = array_search($ida, $orderedList);
                $posb = array_search($idb, $orderedList);
                return $posa > $posb ? 1 : -1;
            });
            /* Truncate list to max length */
            $finalList = array_values (array_slice($list, 0, $this->list_length));

            global $gStatsInterval;
            global $gStatsStart;
            if (preg_match('/^\d{8}/', $gStatsStart)){
                /* A date */
            } else {
                /* Assume an offset */
                global $gTimezone;
                $date = new \DateTime( 'now', new \DateTimeZone($gTimezone) );
                $date->add(\DateInterval::createFromDateString('-'.$gStatsStart));
                $gStatsStart = $date->format('Ymd');
            }
            /* Get sparkline data */
            foreach ($finalList as &$laureate){
                $enWpName = $wpNames[$laureate["dbPedia"]];

                /* get iw links */
                $wikiDataQuery = new WikiDataQuery();
                $iwLinks = $wikiDataQuery->getSitelinks($enWpName);

                /* get Article stats for each WP */
                global $gStatsWPEditions;
                $totalWeight = 0; // Keep track of weights, in case not all languages have an article
                $totalStats = array();
                foreach ($gStatsWPEditions as $code => $weight ){
                    if ( array_key_exists( $code . 'wiki', $iwLinks )){
                        $wiki = $iwLinks[$code . 'wiki'];
                        $article = new ArticleStats( $wiki, "$code.wikipedia" );
                        $stat = $article->getPoints($gStatsInterval, $gStatsStart);
                        if ( $stat !== null ){
                            foreach ($stat as $k=>$v) {
                                $stat[$k] = $v * $weight;
                            }
                            $totalStats[] = $stat;
                            $totalWeight += $weight;
                        }
                    }
                }
                /* summarize stats */
                $sumArray = array();
                foreach ($totalStats as $k=>$subArray) {
                  foreach ($subArray as $id=>$value) {
                    if (!isset($sumArray[$id])){
                        $sumArray[$id] = 0;
                    }
                    $sumArray[$id] += $value;
                  }
                }
                foreach ($sumArray as $k=>$v) {
                    $sumArray[$k] = (int) ($sumArray[$k] / $totalWeight);
                }
                $laureate["popularity"] = $sumArray;
            }
            unset($laureate); // PHP is weird, but see http://php.net/manual/en/control-structures.foreach.php

        } else {
            $popularityList = new OnsitePopularityList();
            $orderedList = $popularityList->getOrdered();
            usort($list, function($a, $b) use ($orderedList){
                $ida = $a['id'];
                $idb = $b['id'];
                $posa = array_search($ida, $orderedList);
                $posb = array_search($idb, $orderedList);
                return $posa < $posb ? 1 : -1;
            });
            /* Truncate list to max length */
            $finalList = array_values (array_slice($list, 0, $this->list_length));

            /* Get sparkline data. TODO: Honour $gStatsInterval */
            global $gStatsInterval;
            foreach ($finalList as &$laureate){
                $laureate["popularity"] = array_reverse( $popularityList->getIndividual( $laureate["id"], $gStatsInterval ) );
            }

        }

        return $finalList;

    }

}
