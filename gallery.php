<?php
/* Entry point for PHP scripts. See index.php for usage example.

*/
namespace Toplist;
if(!defined('TopList')) {
    define('TopList', TRUE);
}
if(!defined('SETTINGS')) {
    require __DIR__ . '/settings.php';
}

require __DIR__ . '/lib/html.php';


/* List widget*/
class Gallery {

    var $laureate;

    function __construct( $laureate ){

        $this->laureate = $laureate;

    }

    private function _run(){
        $laureate = $this->laureate;
        global $baseUrl;
        $json = file_get_contents( "$baseUrl/gallery-api.php?id=$laureate&height=300" );
        $response = json_decode($json, true);
        $this->widget = new TGalleryWidget($response[$laureate]);
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

function printGallery( $laureate ){
    $obj = new Gallery( $laureate );
    $obj->printHTML();
}
