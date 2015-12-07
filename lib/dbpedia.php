<?php
/* Contains a class for querying the dbPedia endpoint.
   Todo: Merge with ED!

*/ 
namespace Toplist;
if(!defined('TopList')) {
   die('Not permitted');
}

require_once $baseDir . 'lib/external-data.php';

Class DbPediaQuery extends ExternalDataSparql {

    var $endpoint;
    var $_uris;

    function __construct( $laureates ){

        if ( !is_array( $laureates) ){
            $laureates = array( $laureates );
        }
        $this->endpoint = new \Endpoint('http://dbpedia.org/sparql');
        $uris = array_map(array($this, '_encodeUri'), $laureates);
        $this->_uris = $this->_joinAndAffix( $uris, ', ', '<', '>');
    }

    /* Return an indexed array of laureates data */
    function getWikipediaNames(){
        $laureateDictionary = array();
        $uris = $this->_uris;

        $query = "SELECT ?uri ?label {
            ?uri foaf:isPrimaryTopicOf ?label
            FILTER (?uri IN ($uris))
          }";

        $result = $this->endpoint->query($query);
        foreach( $result["result"]["rows"] as $row){
            $host = parse_url( $row['label'], PHP_URL_HOST );
            $pathParts = explode('/', parse_url( $row['label'], PHP_URL_PATH ));
            if ('en.wikipedia.org' === $host){
                //use the part after the last / as article name. Would break in case of / in name, e.g. in some khoisan languages.
                $laureateDictionary[rawurldecode($row['uri'])] = array_pop($pathParts);
            }
        }
        return $laureateDictionary;
    }
}

