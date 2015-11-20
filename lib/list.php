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

        /* Validate parameters. No not accept any invalid value */
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

        // Add random sparkline data, link and image
        foreach ($list as &$row) {
            $min = rand(0, 80);
            $max = rand(50, 500);
            $sparkline = array();
            for ($i = 0; $i < 120; $i++) {
                $sparkline[] = rand($min, $max);
            }
            $row['popularity'] = $sparkline;
            global $gProfilePageUrl;
            $row['url'] = sprintf($gProfilePageUrl, $row['id']);
            global $gImageAPI;
            $row['image'] = sprintf($gImageAPI, $row['id']);

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
                $date = new \DateTime();
                $date->add(\DateInterval::createFromDateString('-'.$gStatsStart));
                $gStatsStart = $date->format('Ymd');
            }
            /* Get sparkline data */
            foreach ($finalList as &$laureate){
                $article = new ArticleStats( $wpNames[$laureate["dbPedia"]] );
                $laureate["popularity"] = $article->getPoints($gStatsInterval, $gStatsStart);
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

        }

        return $finalList;

    }

}
