<?php
/* Purges all the cache. You will probably need to run as root if
   using the filec cache.

   Usage:

   php purgeCache.php

   or

   sudo php purgeCache.php
*/

require 'maintenance.php';

fwrite(STDOUT, "Cache settings:\n");
$config_str = print_r( \phpFastCache::$config, true);
fwrite(STDOUT, $config_str);

fwrite(STDOUT, "\n\nClearing cache\n");
__c()->clean();
fwrite(STDOUT, "Done!");
