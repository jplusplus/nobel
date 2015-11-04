<?php
/* This is the entry point for all PHP scripts */
namespace Toplist;
if(!defined('TopList')) {
   die('Not permitted');
}

require $baseDir . 'vendor/autoload.php';
require $baseDir . 'lib/db.php';

/* This class represents a laureate top list. */
class TList {
    var $list_length;
    var $parameters;
    var $profileDataFile;
    static $validationRules = array (
            'length'    => 'integer|min_numeric,3|max_numeric,50',
            'award'     => 'alpha',
            'gender'    => 'alpha',
            'region'    => 'alpha_dash',
        );
    static $filterRules = array(
            'length' => 'trim|sanitize_numbers',
            'award'  => 'trim|sanitize_string',
            'gender' => 'trim|sanitize_string',
            'region' => 'trim',
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

        // Import profile pages for poularity statistic
        $data = array_map( 'str_getcsv',
                           file( $this->profileDataFile,
                                 FILE_SKIP_EMPTY_LINES
                                )
                          );
        $headers = array_shift($data);
        foreach ($data as $row) {
            if ( array_key_exists($row[1], $list) ){
                $list[$row[1]]['stats_url'] = $row[0];
            }
        }
        // Add random sparkline data
        foreach ($list as &$row) {
            $min = rand(0, 80);
            $max = rand(50, 500);
            $sparkline = array();
            for ($i = 0; $i < 120; $i++) {
                $sparkline[] = rand($min, $max);
            }
            $row['popularity'] = $sparkline;
        }
        unset($row); // PHP is weird, but see http://php.net/manual/en/control-structures.foreach.php

        return array_values (array_slice($list, 0, $this->list_length));

    }

}
