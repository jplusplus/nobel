<?php
/* Contains a class for assigning regions to nations. Currently using a hardcoded
   CSV file. With more complete linked data from nobelprize.org this could be
   solved better using DBPedia/Wikidata.

   TODO: caching
*/ 
namespace Toplist;
if(!defined('TopList')) {
   die('Not permitted');
}

/* Handles all mapping of regions <-> nation/country
*/
Class RegionFinder {

    var $dataFile;
    var $data;
    var $allRegions = array();

    function __construct(){

            global $baseDir;
            $this->dataFile = $baseDir . 'data/regions.csv';

            $data = array_map('str_getcsv', file($this->dataFile, FILE_SKIP_EMPTY_LINES));
            $headers = array_shift($data);

            $reverse_data = array();
            foreach ($data as $row) {
                $target = array_shift( $row );
                foreach ($row as $col){
                    if ( $col ){
                        $col = $this->_slugify( $col );
                        if ( !array_key_exists( $col, $reverse_data ) ){
                            $reverse_data[$col] = array();
                        }
                        if ( !in_array($target, $reverse_data[$col]) ){
                            $reverse_data[$col][] = $target;
                            if ( !in_array($col, $this->allRegions) ){
                                $this->allRegions[] = $col;
                            }
                        }
                    }
                }
            }
            $this->data = $reverse_data;
    }

    /* Harmonize nation names */
    function _slugify( $string ){
        return strtolower(str_replace(' ', '-', $string));
    }

    /* Return all available regions */
    function getRegionList(){
        return $this->allRegions;
    }

    /* Return all available regions */
    function getRegionMapping(){
        return $this->data;
    }

    /* Return a list of regions this nation belongs to.
     */
    function getRegions( $nation ){
        $nation = $this->_slugify( $nation );
        return $this->data[$nation];
    }

}
