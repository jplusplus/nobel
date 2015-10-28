<?php
require __DIR__ . '/lib/list.php';

$list = new Toplist($_GET);
$laureates = $list->getData();

$dom = new \DOMDocument('1.0', 'utf-8');

/* Append script tag with main.js */
$gToplistSettings = array(
    'endpoint' => "/nobel/list-api.php",
    );

$jquery_js = 'window.jQuery || document.write("<script src=\'https://code.jquery.com/jquery-2.1.4.min.js\'>\x3C/script>");';
$script = $dom->createElement('script', $jquery_js);
$dom->appendChild($script);

$js = file_get_contents(__DIR__ . '/js/main.js');
$js = 'var gToplistSettings = ' . json_encode($gToplistSettings, JSON_UNESCAPED_UNICODE) . ';' . $js;
$script = $dom->createElement('script', JShrink\Minifier::minify($js));
$dom->appendChild($script);

/* Append div tag */
function createTag($dom, $tag, $content, $attributes = array()){
    $element = $dom->createElement($tag, $content);
    foreach ($attributes as $key => $val){
        $attr = $dom->createAttribute($key);
        $attr->value = $val;
        $element->appendChild($attr);
    }
    return $element;
}

$container = createTag($dom, 'div', '', 'toplist');

$list = createTag($dom, 'ul', '');
foreach ($laureates as $label => $laureate) {
    $li = createTag($dom, 'li', '');
    $h3 = createTag($dom, 'h3', $laureate["name"], array("data-name" => $laureate["name"], "class" => "name"));

    $li->appendChild($h3);
/*
    <li>
    <h3 class="name"></h3>
    <span class="gender"></span> | <span class="award"></span>
    <div class="sparkline popularity"></div>
</li>
*/
    $list->appendChild($li);
}
$container->appendChild($list);

$dom->appendChild($container);
echo $dom->saveHTML();
