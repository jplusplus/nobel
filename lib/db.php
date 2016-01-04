<?php
/* Contains a class for querying the Nobel laureates database.
*/ 
namespace Toplist;
if(!defined('TopList')) {
   die('Not permitted');
}

Class Query {

    var $awards = array('Physics',
                        'Chemistry',
                        'Literature',
                        'Peace',
                        'Physiology_or_Medicine',
                        'Economic_Sciences');

    /* $parameters may contain these keys:
   'gender': "male", "female"
   'region': "south-asia"
   'award': "Physics"
   'decade': "201"
   */
    function __construct( $parameters = array() ){
    }

    /* Return an indexed array of laureates */
    function get(){
    }
}


/* Class for querying a single laureate for a single property */
Class SimpleSPARQLQuery extends Query{

    var $id;
    var $endpoint;
    var $query;

    function __construct( $id ){
        $this->id = $id;
        $this->endpoint = new \Endpoint('http://data.nobelprize.org/sparql');
        $this->query = "PREFIX owl: <http://www.w3.org/2002/07/owl#>
        SELECT ?laur ?sameAs {
            ?laur owl:sameAs ?sameAs
            FILTER (?laur IN (<http://data.nobelprize.org/resource/laureate/$id>))
        }";
    }

    function getDbpedia(){
        /* Get dbPedia url */
        $result = $this->endpoint->query($this->query);
        $dbPediaUri = null;
        $dbPediaLinks = array_filter( $result["result"]["rows"], function( $var ){
            $host = parse_url( $var["sameAs"], PHP_URL_HOST );
            return ('dbpedia.org' === $host);
        });
        //Use only the first link, if multiple
        $dbPediaLinkObj = array_pop($dbPediaLinks);
        $dbPediaLink = $dbPediaLinkObj["sameAs"];
        return $dbPediaLink;
    }

}


Class SPARQLQuery extends Query{
    var $endpoint = "http://data.nobelprize.org/sparql";
    var $prefixes = array (
                    'nobel: <http://data.nobelprize.org/terms/>',
                    'foaf: <http://xmlns.com/foaf/0.1/>',
                    'rdfs: <http://www.w3.org/2000/01/rdf-schema#>',
                    'rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>',
                    'dbpedia-owl: <http://dbpedia.org/ontology/>',
                    'yago: <http://yago-knowledge.org/resource/>',
                    'viaf: <http://viaf.org/viaf/>',
                    'meta: <http://www4.wiwiss.fu-berlin.de/bizer/d2r-server/metadata#>',
                    'dcterms: <http://purl.org/dc/terms/>',
                    'd2r: <http://sites.wiwiss.fu-berlin.de/suhl/bizer/d2r-server/config.rdf#>',
                    'dbpedia: <http://dbpedia.org/resource/>',
                    'owl: <http://www.w3.org/2002/07/owl#>',
                    'xsd: <http://www.w3.org/2001/XMLSchema#>',
                    'map: <http://data.nobelprize.org/resource/#>',
                    'freebase: <http://rdf.freebase.com/ns/>',
                    'dbpprop: <http://dbpedia.org/property/>',
                    'skos: <http://www.w3.org/2004/02/skos/core#>',
                );

    var $_query;
    var $_result;

    /* Joins an array and prefixes each element */
    function _joinAndAffix( $list, $glue, $prefix = "", $suffix = "" ){
        array_walk(
            $list,
            function(&$value, $key, $affix) { 
                $value = $affix[0] . $value . $affix[1];
            }, array($prefix, $suffix));
        return implode($glue, $list);
    }

    function __construct( $parameters ){
        $endpoint = new \Endpoint($this->endpoint);

        /* Add namespace prefixes to query */
        $query = $this->_joinAndAffix($this->prefixes,
                                      "\n",
                                      "PREFIX ");
        /* Add select statement to query */
        $query .= "\nSELECT DISTINCT * ";
        /* Add where clauses to query */
        $wheres  = array(
            '?laur rdf:type nobel:Laureate',
            /* Properties to retrive*/
            '?laur rdfs:label ?label',
            'optional { ?laur dbpedia-owl:birthPlace ?birthPlace }',
            'optional { ?laur owl:sameAs ?sameAs }',
            '?laur nobel:laureateAward ?award',
            '?laur nobel:nobelPrize ?prize',
            '?laur foaf:gender ?gender'
        );
        /* Select by award */
        if (isset($parameters['award'])){
            $award = $parameters['award'];
            if (in_array($award, $this->awards)) {
                $wheres[] = "?award nobel:category <http://data.nobelprize.org/resource/category/$award>";
            }
        }
        /* Select by gender */
        if (isset($parameters['gender'])){
            $gender = $parameters['gender'];
            if (in_array($gender, array('male', 'female'))){
                $wheres[] = "?laur foaf:gender '$gender'";
            }
        }
        $whereString = $this->_joinAndAffix($wheres,
                                           "\n",
                                           "\t", ' .');

        /* Select by region */
        if ( isset($parameters['region']) ){

            global $baseUrl;
            $url = "$baseUrl/regions-api.php";
            $regions_json = file_get_contents( $url );
            $regionMapping = json_decode( $regions_json, true );
            // assure that region is in list
            if (in_array( $parameters['region'], array_keys( $regionMapping ) ) ){
                $filters = array();
                $data = $regionMapping[$parameters['region']];
                foreach ($data as $str){
                    $str = urlencode(str_replace(" ", "_", $str));
                    $filters[] = "?birthPlace = <http://data.nobelprize.org/resource/country/$str>";
                }
                $filter = implode(' || ', $filters);
                $whereString .= "\n\tFILTER( $filter )";

            }
        }
        $query .= "WHERE {\n$whereString\n}\n";

        $this->_query = $query;
        global $gCacheLocal;
        if ($gCacheLocal){
            $cacheKey = 'LD-' . md5( $query );
            $result = __c()->get( $cacheKey );
            if ( $result === null ){
                $result = $endpoint->query($query);
                global $gExternalLaureateDataCacheTime;
                __c()->set( $cacheKey, $result, $gExternalLaureateDataCacheTime * 3600 );
            }
        } else {
            $result = $endpoint->query($query);
        }
        $this->_result = $result["result"]["rows"];

    }

    function get(){
        $result = $this->_result;
        $output = array();
        if (!$result){
            return $output;
        }
        foreach( $result as $k => $value ){
            $key = $value["label"];
            if (!isset($output[$key])){
                $output[$key] = array(
                    'id' => null,
                    'name' => null,
                    'laureates_url' => null,
                    'country' => null,
                    'city' => null,
                    'dbPedia' => null,
                    'awards' => array(),
                    'gender' => null,
                );
            }

            // print_r($value);

            /* name */
            $output[$key]['name'] = $value["label"];

            /* id */
            $pathParts = explode('/', parse_url($value['laur'], PHP_URL_PATH));
            $output[$key]['id'] = array_pop($pathParts);

            /* award, award-year */
            if (isset($value['prize'])){
                $pathParts = explode('/', parse_url($value['prize'], PHP_URL_PATH));
                $award = array('award' => $pathParts[3],
                               'year'  => $pathParts[4]);
                if (!in_array($award, $output[$key]['awards'])) {
                    $output[$key]['awards'][] = $award;
                }
            }

            /* gender */
            if (isset($value['gender'])){
                $output[$key]['gender'] = $value['gender'];
            }
            /* DBPedia */
            if (isset($value['sameAs'])){
                $host = parse_url($value['sameAs'], PHP_URL_HOST);
                if ('dbpedia.org' === $host){
                    $output[$key]['dbPedia'] = $value['sameAs'];
                }
            }

            /* country, city */
            if (isset($value["birthPlace"])){
                $pathParts = explode('/', parse_url($value["birthPlace"], PHP_URL_PATH));
                if ($pathParts[2] === 'country'){
                    $output[$key]['country'] = $pathParts[3];
                } elseif ($pathParts[2] === 'city'){
                    $output[$key]['city'] = $pathParts[3];
                }
            }
        }
//        print_r($output);
        return $output;
    }
}
