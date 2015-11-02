<?php
namespace Toplist;
if(!defined('TopList')) {
   die('Not permitted');
}

/* -------------- GLOBAL SETTINGS --------------- */

/* Local path to this PHP app, with trailing slash */
/* Unless you have moved settings.php, or have a   */
/* very exotic server setup, you can probably      */
/* leave this as it is.                            */
$baseDir = __DIR__ . '/';

/* The public url to the directory containing this */
/* PHP app. This url is used by the frontend app   */
/* and $baseUrl/list-api.php must be publically    */
/* accessible.                                     */
$baseUrl = '/nobel';

/* Default number of list items, if not specified */
const NUM_ITEMS = 10;


/* Debug modes */
const PRODUCTION = 0;
const DEBUG = 2;
/* Debug level. Use PRODUCTION for, well, production */
$debugLevel = DEBUG;
