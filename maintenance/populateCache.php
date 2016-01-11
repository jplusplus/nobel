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

fwrite(STDOUT, 'Cache settings:');
$config_str = print_r( \phpFastCache::$config, true);
fwrite(STDOUT, $config_str);

/* Parse command line args */
$options = getopt( 'l::', array('limit::') );
$limit = (int) @$options['l'] ?: (int) @$options['limit'] ?: 0;

/* Fetch list of laureates from the stats API */
$onsitePopularityList = new Toplist\OnsitePopularityList();
$laureates = array_keys( $onsitePopularityList->list );

$i = 0;
foreach ( $laureates as $laureate ) {
    $i++;
    echo "Fetching data for laureate $laureate...\n";

    /* Fetch gallery data. Using the API endpoint */
    /* to future proof the script. */
    $response = file_get_contents( "$baseUrl/gallery-api.php?id=$laureate&height=300" );
    if ($response){
        fwrite(STDOUT, "Fetched laureate data\n");
    } else {
        fwrite(STDOUT, "Warning: Failed to fetch laureate data\n");
    }

    if ( $limit && ( $i >= $limit ) ){
        fwrite(STDOUT, "Aborting after $i laureates\n");
        break;
    }
}

//Fetch unfiltered list
fwrite(STDOUT, "Fetching unfiltered list data 1/3...\n");
$response = file_get_contents( "$baseUrl/list-api.php?popularity=wikipedia&gender=female" );
if ($response){
    fwrite(STDOUT, "Fetched unfiltered list data\n");
} else {
    fwrite(STDOUT, "Warning: Failed to fetch unfiltered list data\n");
}

//Fetch unfiltered list
fwrite(STDOUT, "Fetching unfiltered list data 2/3...\n");
$response = file_get_contents( "$baseUrl/list-api.php?popularity=wikipedia&gender=male&region=europe" );
if ($response){
    fwrite(STDOUT, "Fetched unfiltered list data\n");
} else {
    fwrite(STDOUT, "Warning: Failed to fetch unfiltered list data\n");
}

//Fetch unfiltered list
fwrite(STDOUT, "Fetching unfiltered list data 3/3...\n");
$response = file_get_contents( "$baseUrl/list-api.php?popularity=wikipedia&gender=male" );
if ($response){
    fwrite(STDOUT, "Fetched unfiltered list data\n");
} else {
    fwrite(STDOUT, "Warning: Failed to fetch unfiltered list data\n");
}
