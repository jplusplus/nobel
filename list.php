<?php
/* Entry point for PHP scripts. See index.php for usage examples. */

namespace Toplist;
require __DIR__ . '/settings.php';
require $baseDir . 'lib/list.php';
require $baseDir . 'lib/html.php';

class Widget {

    var $list;
    var $widget;

    function __construct( $parameters = array() ){
        $this->list = new TList( $parameters );
        $this->widget = new TListWidget($this->list, DEBUG);
    }

    function printHTML(){
        $this->widget->printHTML();
    }

    function getHTML(){
        return $this->widget->getHTML();
    }

}

function printWidget( $parameters = array() ){
    $obj = new Widget( $parameters );
    $obj->printHTML();
}
