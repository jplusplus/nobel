
<style>

    .filterable {
        text-decoration: underline;
        cursor: pointer;
    }
    .list-item span {
        display: inline-block;
        padding: 0 4px;
    }
</style>
<?php
require __DIR__ . '/lib/list.php';

$list = new Toplist($_GET);
$laureates = $list->getData();

$dom = new \DOMDocument('1.0', 'utf-8');

/* Append script tag with main.js */
$gToplistSettings = array(
    'endpoint' => 'list-api.php',
    );

$jquery_js = 'window.jQuery || document.write("<script src=\'https://code.jquery.com/jquery-2.1.4.min.js\'>\x3C/script>");';
$script = $dom->createElement('script', $jquery_js);
$dom->appendChild($script);

$js = file_get_contents(__DIR__ . '/js/main.js');
$js = 'var gToplistSettings = ' . json_encode($gToplistSettings, JSON_UNESCAPED_UNICODE) . ';' . $js;
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
                                          'data-name' => $laureate['name'],
                                          'data-gender' => $laureate['gender'],
                                          'data-awards' => json_encode($laureate['awards']),
                                    )
    );
    
    $h3 = createTag($dom, 'h3', $laureate["name"], array("class" => "name"));
    $li->appendChild($h3);

    $genderspan = createTag($dom, 'span', $laureate["gender"], array(
                                                                    "class" => "gender filterable",
                                                                    "data-filter-key" => "gender",
                                                                    "data-filter-value" => $laureate["gender"]
                                                                    ));
    $li->appendChild($genderspan);

    $countryspan = createTag($dom, 'span', $laureate["region"], array(
                                                                    "class" => "region filterable",
                                                                    "data-filter-key" => "region",
                                                                    "data-filter-value" => $laureate["region"]
                                                                    ));
    $li->appendChild($countryspan);

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
