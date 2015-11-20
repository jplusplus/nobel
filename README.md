Installation
============

 * Install Composer: `curl -sS https://getcomposer.org/installer | php`
 * Run `php composer.phar install`
 * `copy settings.example.php settings.php` and then make any modifications needed to settings.php

End points
==========

 * list.php (for inclusion from other PHP scripts)
 * list-api.php (for ajax communication)

GET-parameters for endpoints:

 * `length`: Number of laureates to display (3 < length < 50)
 * `debug`: Print useful debugging messages
 * `award`: 'Physics', 'Chemistry', 'Literature', 'Peace', 'Physiology_or_Medicine', 'Economic_Sciences'
 * `gender`: 'male', 'female'
 * `gender`: 'male', 'female'
 * `popularity`: 'wikipedia', 'site-stats'

Settings
========
All settings need to be copied from settings.example.php to settings.php, and then modified there.

* $gStatsWPEditions: What Wikipedia editions to base Wikipedia popularity statistics on, and how to weigh them respectively. The default weight are roughly based on native speakers, but note that the native language Wikipedia is not always the first choice in all communities, and also see specific notes in `settings.examples.php` about the scope of some editions.