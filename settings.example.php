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
/* and $baseUrl/list-api.php, gallery-api.php etc  */
/* must be publically accessible.                  */
$baseUrl = 'http://localhost/nobel';

/* Default number of list items, if not specified */
$maxListItems = 10;

/* Profile page url. %d will be replaced by numeric id */
$gProfilePageUrl = 'http://www.nobelprize.org/nobel_prizes/redirect_to_facts.php?id=%d';

/* Url to thumbnail service.                      */
/* Should return an approximately 162 px wide     */
/* image, the closer to a square the better.      */
/* %d will be replaced by numeric id.             */
$gImageAPI = 'http://www.nobelprize.org/nobel_prizes/get_image.php?id=%d&size=3';

/* Url to page toplist API for the local site.    */
$gStatsToplistAPI = 'http://www.nobelprize.org/nobel_prizes/popular_api.php';

/* Url to laureate stats API for nobelprize.org   */
$StatsLaureatePageAPI = 'http://www.nobelprize.org/nobel_prizes/popular_byid_api.php';

/* How many days should should be aggregated in   */
/* datapoint in the page view statistics          */
$gStatsInterval = 1;

/* When should we start counting statistics       */
/* Can be either a date on the format YYYYMMDD,   */
/* or a dateoffset, like '2 months'               */
//$gStatsStart = '20150901';
$gStatsStart = '10 weeks';


/* What languages should we base the Wikipedia    */
/* visit statistics on? Provide an weight for each*/
/* edition, for createing a weighted average.     */
$gStatsWPEditions = array(
	'zh' => 935,  // Chinese, including script varieties (zh-hans, zh-tw, etc), but excluding Minnan, Yue (Cantonese), Mindong, Wu, Hakka, and Gan WP.
	'en' => 387,  // English, not including simplified English WP or Scots WP.
	'es' => 365,  // Spanish
	'hi' => 295,  // Hindi, not including Urdu WP
	'ar' => 295,  // Arabic, excluding Egyptian Arabic WP
);

/* Gallery images are picked from pictures        */
/* in Wikipedia articles. What WP editions should */
/* we scan for images?                            */
$gImageSourceWPEditions = array( 'en', 'es', 'de', 'ru' );

/* When retriving images from Wikipedia pages, we */
/* want to exclude some pics that are often used  */
/* to illustrate navigation boxes and similar.    */
$gImageBlacklist = array(
	'Tom Sawyer 1876 frontispiece.jpg',
	'Nobel Prize.png',
	'Дмитрий Иванович Менделеев 8.jpg',
);

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
