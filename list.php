<?php
namespace Toplist;
require __DIR__ . '/settings.php';
require $baseDir . 'lib/list.php';
require $baseDir . 'lib/html.php';

$list = new TList($_GET);
$widget = new TListWidget($list, DEBUG);
$widget->printHTML();
