<?php
/* Tries to populate the cache for lists and galleries.
   Running this script will take quite some time. You
   might want to put in in a cron job, to run during
   low trafic hours.

   Usage:

   php populateCache.php
   php populateCache.php --limit="10"
   php populateCache.php -l10

   Default limit is 0, meaning loop over every laureate.

*/

require 'maintenance.php';
require $baseDir . 'lib/popularity.php';

/* Parse command line args */
$options = getopt( 'l::', array('limit::') );
$limit = (int) @$options['l'] ?: (int) @$options['limit'] ?: 0;

/* Fetch list of laureates from the stats API */
$onsitePopularityList = new Toplist\OnsitePopularityList();
$laureates = array_keys( $onsitePopularityList->list );

$i = 0;
foreach ( $laureates as $laureate ) {
    $i++;
    echo "Fetching data for laureate $laureate.\n";

    /* Fetch gallery data. Using the API endpoint */
    /* to future proof the script. */
    $response = file_get_contents( "$baseUrl/gallery-api.php?id=$laureate&height=300" );
    if ($response){
        echo "Fetched gallery data.\n";
    } else {
        echo "Warning: Failed to fetch gallery data.\n";
    }

    if ( $limit && ( $i >= $limit ) ){
        echo "Aborting after $i laureates.\n";
        break;
    }
}

//Fetch unfiltered list
echo "Fetching unfiltered list data.\n";
$response = file_get_contents( "$baseUrl/list-api.php?popularity=wikipedia" );
if ($response){
    echo "Fetched unfiltered list data.\n";
} else {
    echo "Warning: Failed to fetch unfiltered list data.\n";
}
