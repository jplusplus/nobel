<?php
/* Purges all the cache. You will probably need to run as root if
   using the filec cache.

   Usage:

   php purgeCache.php

   or

   sudo php purgeCache.php
*/

require 'maintenance.php';

__c()->clean();
