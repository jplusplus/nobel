<?php
require __DIR__ . '/lib/list.php';


$list = new Toplist($_GET);
$laureates = $list->getData();

$dom = new \DOMDocument('1.0', 'utf-8');
$list = $dom->createElement('ul');
foreach ($laureates as $laureate) {
    $list_li = $dom->createElement('li', $laureate["name"]);
    $list->appendChild($list_li);
}

$dom->appendChild($list);
echo $dom->saveHTML();

