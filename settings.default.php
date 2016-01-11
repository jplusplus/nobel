<?php
namespace Toplist;
if(!defined('TopList')) {
   die('Not permitted');
}
require_once 'constants.php';

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
$gProfilePageUrl = 'http://www.nobelprize.org/nobel_prizes/redirect_to_facts.php?id=%d&cat=%s';

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
/* one datapoint in the page view statistics      */
$gStatsInterval = 1;

/* When should we start counting statistics       */
/* Can be either a date on the format YYYYMMDD,   */
/* or a dateoffset, like '2 months'               */
//$gStatsStart = '20150901';
$gStatsStart = '10 weeks';

/* Description text to th UI controls. Can be left blank.*/
$gUIIntro = <<<EOT
Use the controls to filter the top list.
The list is sorted by popularity at either nobelprize.org, or at Wikipedia (using statistics from the Chinese, English, Spanish, Hindi and Arabic Wikipedias).
EOT;

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
$gImageSourceWPEditions = array( 'en', 'es', 'de', 'nl' /*, 'ru'*/ );
// ruwp contains a lot pf portraits as genre pictures,
// hence commenting out

/* When retriving images from Wikipedia pages, we */
/* want to exclude some pics that are often used  */
/* to illustrate navigation boxes and similar.    */
/* NB: Space, not underscore in filenames!        */
$gImageBlacklist = array(
    /* Navbox images */
    'Tom Sawyer 1876 frontispiece.jpg', //ruwp
    'Nobel Prize.png',
    'Дмитрий Иванович Менделеев 8.jpg', //ruwp
    'Charles_Darwin_1880.jpg', //ruwp
    /* Specific laureates */
    'Agnes von Kurowsky in Milan.jpg', //Hemmingway, nlwp
    /* School buildings */
    'Merton College front quad.jpg', //TS Eliot, nlwp
    'St John\'s Church, Little Gidding.jpg', //TS Eliot, nlwp
    'Vivienne Haigh-Wood Eliot 1920.jpg',
    'Harper Midway Chicago.jpg',
    'University of Minnesota-20031209.jpg',
    /* US military decorations sometimes shown in enwp*/
    'Bronze Star medal.jpg',
    'Air Medal front.jpg',
    'Meritorious Service Medal (United_States).png',
    'Purpleheart.jpg',
    'Dfc-usa.jpg',
    'Us legion of merit legionnaire.png',
    'Silver Star medal.png',
    /* More american logotypes */
    'Conservative Elephant.png',
    '2006 AEGold Proof Obv.png',

);

/* Cache type. Can be auto, memcache, files, etc. */
/* see http://www.phpfastcache.com/ for full list */
\phpFastCache::setup("storage","auto");


/* The number of hours to cache external data on  */
/* individual laureates, e.g. Wikipedia images    */
$gExternalLaureateDataCacheTime = 720;

/* The number of hours to store external stats,   */
/* e.g. Wikipedia pageviews                       */
$gExternalStatsCacheTime = 36;

/* Should we cache responses from local API's? If */
/* our caching is not superfast, and API's are on */
/* on the same server, it might be faster not to. */
$gCacheLocal = true;

/* Time zone to use when fetching statistics      */
$gTimezone = 'Europe/Stockholm';

/* Should we update the url to reflect current    */
/* filtering of lists? This could potentially     */
/* interfere with other javascript on the site.   */
$gUpdateUrl = true;

/* Debug level. Use PRODUCTION for, well, production */
$debugLevel = DEBUG;


/* ----------------------------------------------- */

if ($debugLevel >= DEBUG){
    ini_set('display_errors', '1');
    error_reporting(E_ALL | E_STRICT);
}
