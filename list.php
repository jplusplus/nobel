<?php
namespace Toplist;
require __DIR__ . '/settings.php';
require $baseDir . 'lib/list.php';

$list = new TList($_GET);
$laureates = $list->getData();

$dom = new \DOMDocument('1.0', 'utf-8');

/* Append script tag with main.js */
$gToplistSettings = array(
    'endpoint' => 'list-api.php',
    );

$jquery_js = 'window.jQuery || document.write("<script src=\'https://code.jquery.com/jquery-2.1.4.min.js\'>\x3C/script>");';
$script = $dom->createElement('script', $jquery_js);
$dom->appendChild($script);

$js = file_get_contents($baseDir . 'js/main.js');
$css = file_get_contents($baseDir . 'css/main.css');
$css = str_replace("\n", "", $css);
$js = 'var gToplistSettings = ' . json_encode($gToplistSettings, JSON_UNESCAPED_UNICODE) . ';' . $js;
$js = str_replace('¤CSS', $css, $js);
$script = $dom->createElement('script', $js);
//$script = $dom->createElement('script', JShrink\Minifier::minify($js));
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

$container = createTag($dom, 'div', '', array('class' => 'toplist'));

$list = createTag($dom, 'ul', '');
foreach ($laureates as $label => $laureate) {
    $li = createTag($dom, 'li', '', array('class' => 'list-item',
                                          'data-id' => $laureate['id'],
                                          'data-name' => $laureate['name'],
                                          'data-gender' => $laureate['gender'],
                                          'data-awards' => json_encode($laureate['awards']),
                                    )
    );
    
    $h3 = createTag($dom, 'h3', '', array("class" => "name"));
    $a = createTag($dom, 'a', $laureate["name"], array("href" => $laureate['laureates_url']));
    $h3->appendChild($a);
    $li->appendChild($h3);

    $genderspan = createTag($dom, 'span', $laureate["gender"], array(
                                                                    "class" => "gender filterable",
                                                                    "data-filter-key" => "gender",
                                                                    "data-filter-value" => $laureate["gender"]
                                                                    ));
    $li->appendChild($genderspan);
    
    $awardsString = implode(', ', array_map(function($el){
                                                return $el['award'] . ' (' . $el['year'] . ')';
                                            }, $laureate['awards']));
    $awardspan = createTag($dom, 'span', $awardsString, array("class" => "awards"));
    $li->appendChild($awardspan);

    $sparklinediv = createTag($dom, 'div', '', array("class" => "sparkline popularity"));
    $li->appendChild($sparklinediv);

    $list->appendChild($li);
}
$container->appendChild($list);

$dom->appendChild($container);
echo $dom->saveHTML();
