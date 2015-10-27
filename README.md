Installation
============

 * Install Composer: `curl -sS https://getcomposer.org/installer | php`
 * Run `php composer.phar install`

End points
==========

 * list.php (for inclusion from other PHP scripts)
 * list-api.php (for ajax communication)

GET-parameters for endpoints:

 * `length`: Number of laureates to display (3 < length < 50)
 * `debug`: Print useful debugging messages
 * `award`: 'Physics', 'Chemistry', 'Literature', 'Peace', 'Physiology_or_Medicine', 'Economic_Sciences'
 * `gender`: 'male', 'female'
