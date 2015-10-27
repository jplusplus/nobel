<?php
/* Contains a class for querying the Nobel laureates database.
*/ 
namespace Toplist;
require __DIR__ . '/../vendor/bordercloud/sparql/Endpoint.php'; //This lib is not autoloaded

/* $parameters is an array, that may contain these keys:
   'gender': "male", "female"
   'region': "south-asia"
   'award': "Physics"
   'decade': "201"
   */
Class Query {

    var $awards = array('Physics',
                        'Chemistry',
                        'Literature',
                        'Peace',
                        'Physiology_or_Medicine',
                        'Economic_Sciences');

    function __construct( $parameters ){
        echo "HEJ";

    }
}

Class SPARQLQuery extends Query{
    var $endpoint = "http://data.nobelprize.org/sparql";
    var $prefixes = array (
                    'nobel: <http://data.nobelprize.org/terms/>',
                    'foaf: <http://xmlns.com/foaf/0.1/>',
                    'rdfs: <http://www.w3.org/2000/01/rdf-schema#>',
                    'rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>'
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
        $query .= "\nSELECT DISTINCT ?label ";
        /* Add where clauses (filter) to query */
        $wheres  = array(
            '?laur rdf:type nobel:Laureate',
            '?laur rdfs:label ?label'
        );
        if (isset($parameters['award'])){
            $award = $parameters['award'];
            if (in_array($award, $this->awards)) {
                $wheres[] = '?laur nobel:laureateAward ?award';
                $wheres[] = "?award nobel:category <http://data.nobelprize.org/resource/category/$award>";
            }
        }
        if (isset($parameters['gender'])){
            $gender = $parameters['gender'];
            if (in_array($gender, array('male', 'female'))){
                $wheres[] = "?laur foaf:gender '$gender'";
            }
        }
        $whereString = $this->_joinAndAffix($wheres,
                                           "\n",
                                           "\t", ' .');
        $query .= "WHERE {\n$whereString\n}";

        $this->_query = $query;
        $result = $endpoint->query($query);
        $this->_result = $result["result"]["rows"];

    }

    function get(){
        $result = $this->_result;
        array_walk($result, function(&$value, $key){
                                $value["name"] = $value["label"];
                                unset($value["label type"]);
                            });
        return $result;
    }
} 