<?php
/* Contains a class for querying the dbPedia endpoint.
   Todo: Merge with db.php!

*/ 
namespace Toplist;
if(!defined('TopList')) {
   die('Not permitted');
}

//require $baseDir . 'vendor/bordercloud/sparql/Endpoint.php'; //This lib is not autoloaded


Class DbPediaQuery {

    var $endpoint;
    var $_uris;

    /* Joins an array and prefixes each element */
    function _joinAndAffix( $list, $glue, $prefix = "", $suffix = "" ){
        array_walk(
            $list,
            function(&$value, $key, $affix) { 
                $value = $affix[0] . $value . $affix[1];
            }, array($prefix, $suffix));
        return implode($glue, $list);
    }

    function __construct( $laureates ){

        $this->endpoint = new \Endpoint('http://dbpedia.org/sparql');
        $this->_uris = $this->_joinAndAffix( $laureates, ', ', '<', '>');

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
                $laureateDictionary[$row['uri']] = array_pop($pathParts);
            }
        }
        return $laureateDictionary;
    }
}

