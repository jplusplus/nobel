<?php
/* Constants are defined here, so that we can use require_once from settings.php
   and settings.default.php
 */


/* Debug levels */
define('PRODUCTION', 0);
define('DEVELOPMENT', 1);
define('DEBUG', 2);
define('VERBOSE', 3);

/* Settings are set */
define('SETTINGS', true);

/* Add a global cache object */
require("vendor/phpfastcache/phpfastcache/phpfastcache.php");
