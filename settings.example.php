<?php
namespace Toplist;
if(!defined('TopList')) {
   die('Not permitted');
}
define('SETTINGS', true);

/* -------------- GLOBAL SETTINGS --------------- */

/* Local path to this PHP app, with trailing slash */
/* Unless you have moved settings.php, or have a   */
/* very exotic server setup, you can probably      */
/* leave this as it is.                            */
$baseDir = __DIR__ . '/';

/* The public url to the directory containing this */
/* PHP app. This url is used by the frontend app,  */
/* and $baseUrl/list-api.php must be publically    */
/* accessible.                                     */
$baseUrl = '/nobel';

/* Default number of list items, if not specified */
$maxListItems = 10;

/* Profile page url. %d will be replaced by numeric id */
$gProfilePageUrl = '//www.nobelprize.org/nobel_prizes/redirect_to_facts.php?id=%d';

/* Url to thumbnail service.                      */
/* Should return an approximately 162 px wide     */
/* image, the closer to a square the better.      */
/* %d will be replaced by numeric id.             */
$gImageAPI = '//www.nobelprize.org/nobel_prizes/get_image.php?id=%d&size=3';

/* Url to page stats API for the local site.      */
/* Should return stats by laureate.               */
$gPageStatsAPI = '//www.nobelprize.org/nobel_prizes/popular_api.php';

/* How many days should should be aggregated in   */
/* datapoint in the page view statistics          */
$gStatsInterval = 7;

/* When should we start counting statistics (Ymd) */
$gStatsStart = '20110101';

/* Cache type. Can be auto, memcache, files, etc. */
/* see http://www.phpfastcache.com/ for full list */
include("vendor/phpfastcache/phpfastcache/phpfastcache.php");
\phpFastCache::$config['storage'] = 'files';

/* The number of hours to cache external data on  */
/* individual laureates, e.g. Wikipedia page view */
/* stats.                                         */
$gExternalLaureateDataCacheTime = 12;

/* The number of hours to cache lists based on    */
/* external data, e.g Wikipedia popularity toplist*/
$gExternalDataListsCacheTime = 4;

/* Time zone to use when fetching statistics      */
$gTimezone = 'Europe/Stockholm';

/* Debug modes */
define('PRODUCTION', 0);
define('DEVELOPMENT', 1);
define('DEBUG', 2);
/* Debug level. Use PRODUCTION for, well, production */
$debugLevel = DEBUG;


/* ----------------------------------------------- */

if ($debugLevel == DEBUG){
    ini_set('display_errors', 'On');
    error_reporting(E_ALL | E_STRICT);
}
