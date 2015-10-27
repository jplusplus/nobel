<?php
require __DIR__ . '/lib/list.php';

$list = new Toplist($_GET);
$laureates = $list->getData();

$dom = new \DOMDocument('1.0', 'utf-8');

/* Append script tag with main.js */
$gToplistSettings = array(
	'endpoint' => "/nobel/list-api.php",
	);
$js = file_get_contents(__DIR__ . '/js/main.js');
$js = 'var gToplistSettings = ' . json_encode($gToplistSettings, JSON_UNESCAPED_UNICODE) . ';' . $js;

$jquery_js = 'window.jQuery || document.write("<script src=\'https://code.jquery.com/jquery-2.1.4.min.js\'>\x3C/script>");';

$script = $dom->createElement('script', $jquery_js);
$dom->appendChild($script);
$script = $dom->createElement('script', JShrink\Minifier::minify($js));
$dom->appendChild($script);

/* Append div tag */
$container = $dom->createElement('div');
$class = $dom->createAttribute('class');
$class->value = 'toplist';
$container->appendChild($class);

$list = $dom->createElement('ul');
foreach ($laureates as $label => $laureate) {
    $list_li = $dom->createElement('li', $laureate["name"]);
    $list->appendChild($list_li);
}
$container->appendChild($list);

$dom->appendChild($container);
echo $dom->saveHTML();
