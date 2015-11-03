<?php
/* Entry point for PHP scripts. See index.php for usage examples.

*/
namespace Toplist;
if(!defined('TopList')) {
    define('TopList', TRUE);
}
if(!defined('SETTINGS')) {
    require __DIR__ . '/settings.php';
}

require $baseDir . 'lib/list.php';
require $baseDir . 'lib/html.php';


/* Counter singleton for unique id's */
class Counter {

    private $CurrentValue = 0;
    private static $m_pInstance; 

    private function __construct() {}

    public static function getInstance() {
        if (!self::$m_pInstance) {
            self::$m_pInstance = new Counter();
        }
        return self::$m_pInstance;
    }

    public function getNext() {
        $this->CurrentValue += 1;
        return $this->CurrentValue;
    }

}


/* List widget*/
class Widget {

    var $list;
    var $widget;
    var $parameters;
    var $validParameters;

    function __construct( $parameters = array() ){

        $this->parameters = $parameters;
        $this->validParameters = TList::getParameters();
        /* copy parameters to class params */
        foreach ($this->validParameters as $parameter) {
            if ( array_key_exists( $parameter, $parameters ) ){
                $this->$parameter = $parameters[$parameter];
            } else {
                $this->$parameter = null;
            }
        }
    }

    private function _run(){

        $widgetCounter = Counter::getInstance()->getNext();

        /* update parameters in case of any changes */
        foreach ($this->validParameters as $parameter) {
            if ( isset( $this->$parameter ) ){
                $this->parameters[$parameter] = $this->$parameter;
            }
        }
        $this->list = new TList( $this->parameters );
        $this->widget = new TListWidget($this->list, $widgetCounter);
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

function printUI(){
    $obj = new TListUI();
    $obj->printHTML();

    $listObj = new Widget( $_GET );
    $listObj->printHTML();
}
