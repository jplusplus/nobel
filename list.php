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
        foreach ($parameters as $k => $v){
            $this->$k = $v;
        }
    }

    private function _run(){
        $this->list = new TList( array(
                                        'gender' => $this->gender || null,
                                        'length' => $this->length || null,
                                        'region' => $this->region || null,
                                        'award' => $this->award || null,
                                       ) );
        $this->widget = new TListWidget($this->list, DEBUG);
    }

    function printHTML(){
        $this->_run();
        $this->widget->printHTML();
    }

    function getHTML(){
        $this->_run();
        return $this->widget->getHTML();
    }

}

function printWidget( $parameters = array() ){
    $obj = new Widget( $parameters );
    $obj->printHTML();
}

function printUI( ){
    $obj = new Widget( $_GET );
    $obj->printHTML();
}
