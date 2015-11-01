<?php
/* This is the entry point for all PHP scripts */
namespace Toplist;

require $baseDir . 'vendor/autoload.php';
require $baseDir . 'lib/db.php';

/* This class represents a laureate top list. */
class TList {
    var $list_length;
    var $parameters;
    var $profileDataFile;

    /* Constructor. Will parse the parameters. */
    function __construct( $parameters ) {
        global $baseDir;
        $this->profileDataFile = $baseDir . 'data/profile-pages.csv';

        /* Validate parameters. No not accept any invalid value */
        $gump = new \GUMP();
        $parameters = $gump->sanitize($parameters);
        $gump->validation_rules(array(
            'length'    => 'integer|min_numeric,3|max_numeric,50',
            'debug'     => 'boolean',
            'award'     => 'alpha',
            'gender'    => 'alpha',
            'region'    => 'alpha_dash',
        ));
        $gump->filter_rules(array(
            'length' => 'trim|sanitize_numbers',
            'debug'  => 'trim',
            'award'  => 'trim|sanitize_string',
            'gender' => 'trim|sanitize_string',
            'region' => 'trim',
        ));

        $parameters = $gump->run($parameters);

        if($parameters === false) {
            echo $gump->get_readable_errors(true); //DEBUG
            $parameters = array();
        }

        $this->list_length = isset($parameters['length']) ? $parameters['length'] : NUM_ITEMS;
        $this->parameters = $parameters;

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
        return array_values (array_slice($list, 0, $this->list_length));

    }

}
