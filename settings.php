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
define('NUM_ITEMS', 10);


/* Debug modes */
define('PRODUCTION', 0);
define('DEVELOPMENT', 1);
define('DEBUG', 2);
/* Debug level. Use PRODUCTION for, well, production */
$debugLevel = DEBUG;
