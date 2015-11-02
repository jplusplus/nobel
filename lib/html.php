<?php
/* Contains all code producing list widgets and list controls */
namespace Toplist;
if(!defined('TopList')) {
   die('Not permitted');
}

/* This class represents a listwidget */
class TListWidget {

    var $laureates;
    var $dom;
    var $id;
    var $jsSettings;


    function __construct( TList $list, $id=0 ) {
        global $baseUrl;
        $this->jsSettings = array( 'endpoint' => "$baseUrl/list-api.php",
                                  );
        $this->laureates = $list->getData();
        $this->id = $id;
    }


    private function _createTag($tag, $content = '', $attributes = array()){
        $element = $this->dom->createElement($tag, $content);
        foreach ($attributes as $key => $val){
            $attr = $this->dom->createAttribute($key);
            $attr->value = $val;
            $element->appendChild($attr);
        }
        return $element;
    }


    function getHTML(){
        $this->dom = new \DOMDocument('1.0', 'utf-8');

        $id = $this->id;
        if ($id === 1){
            /* Append script tag with jQuery */
            $jquery_js = 'window.jQuery || document.write("<script src=\'https://code.jquery.com/jquery-2.1.4.min.js\'>\x3C/script>");';
            $script = $this->dom->createElement('script', $jquery_js);
            $this->dom->appendChild($script);

            /* Append script tag with main.js */
            global $baseDir;
            $js = file_get_contents($baseDir . 'js/main.js');
            $css = file_get_contents($baseDir . 'css/main.css');
            $css = str_replace("\n", "", $css);
            $js = 'var gToplistSettings = ' . json_encode($this->jsSettings, JSON_UNESCAPED_UNICODE) . ';' . $js;
            $js = str_replace('¤CSS', $css, $js);
            global $debugLevel;
            if ($debugLevel === DEBUG){
                $script = $this->dom->createElement('script', $js);
            } else {
                $script = $dom->createElement('script', JShrink\Minifier::minify($js));
            }
            $this->dom->appendChild($script);
        }

        $container = $this->_createTag( 'div', '', array('class' => "toplist id_$id"));

        $list = $this->_createTag( 'ul' );
        foreach ($this->laureates as $label => $laureate) {
            $li = $this->_createTag( 'li', '', array('class' => 'list-item',
                                                     'data-id' => $laureate['id'],
                                                     'data-name' => $laureate['name'],
                                                     'data-gender' => $laureate['gender'],
                                                     'data-awards' => json_encode($laureate['awards']),
                                                     )
                                    );
            
            $h3 = $this->_createTag( 'h3', '', array("class" => "name"));
            $a = $this->_createTag('a', $laureate["name"], array("href" => $laureate['laureates_url']));
            $h3->appendChild($a);
            $li->appendChild($h3);

            $genderspan = $this->_createTag( 'span', $laureate["gender"], array(
                                                                            "class" => "gender filterable",
                                                                            "data-filter-key" => "gender",
                                                                            "data-filter-value" => $laureate["gender"]
                                                                            ));
            $li->appendChild($genderspan);
            
            $awardsString = implode(', ', array_map(function($el){
                                                        return str_replace( '_', ' ', $el['award'] ) . ' (' . $el['year'] . ')';
                                                    }, $laureate['awards']));
            $awardspan = $this->_createTag( 'span', $awardsString, array("class" => "awards"));
            $li->appendChild($awardspan);

            $sparklinediv = $this->_createTag( 'div', '', array("class" => "sparkline popularity"));
            $li->appendChild($sparklinediv);

            $list->appendChild($li);
        }
        $container->appendChild($list);

        $this->dom->appendChild($container);

        return $this->dom->saveHTML();

    }

    function printHTML(){
        echo $this->getHTML();
    }

}
