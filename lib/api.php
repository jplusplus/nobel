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

    /* Run GUMP on parameters */
    function _parseParameters( $parameters, $validationRules, $filterRules ){
        $gump = new \GUMP();
        $parameters = $gump->sanitize( $parameters );
        $parameters = $gump->run( $parameters );
        if( $parameters === false ) {
            global $debugLevel;
            if ( $debugLevel >= DEBUG ) {
                echo $gump->get_readable_errors( true );
            }
            $parameters = array();
        }
        return $parameters;
    }

    /* Run GUMP on _GET parameters */
    function getParameters( $validationRules, $filterRules ){
        $gump = new \GUMP();
        $parameters = $gump->sanitize($_GET);
        return $this->_parseParameters( $parameters, $validationRules, $filterRules );
    }

    function write_headers( $contentType = 'application/json' ){
        header("Content-Type: $contentType; charset=utf-8");
    }

    function write_json( $data ){
        echo json_encode( $data );
    }

    function write_html( $data ){
        echo ( $data );
    }

}
