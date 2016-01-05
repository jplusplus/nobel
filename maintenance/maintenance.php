<?php
define('TopList', TRUE);
require __DIR__ . '/../settings.default.php';
require __DIR__ . '/../settings.php';

if (php_sapi_name() !== "cli") {
    die( 'Not allowed' );
}
