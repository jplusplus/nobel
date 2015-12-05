<?php
/* Contains a parent class and any helper functions for
   setting up local API's 
*/ 
namespace Toplist;
if(!defined('TopList')) {
   die('Not permitted');
}

Class Api {

    function __construct(){
    }

    function write_headers(){
        header('Content-Type: application/json; charset=utf-8');
    }

    function write_json( $data ){
        echo json_encode( $data );
    }

}
