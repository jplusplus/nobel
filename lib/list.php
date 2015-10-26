<?php

require __DIR__ . '/../settings.php';
require __DIR__ . '/../vendor/autoload.php';

class Toplist {
    var $list_length;
    var $endPoint = 'http://api.nobelprize.org/v1/laureate.json';

    /* Constructor. Will parse the parameters. */
    function Toplist( $parameters ) {
        /* Validate parameters. No not accept any invalid value */
        $gump = new \GUMP();
        $parameters = $gump->sanitize($parameters);
        $gump->validation_rules(array(
            'length'    => 'integer|min_numeric,3|max_numeric,50',
            'debug'     => 'boolean'
        ));
        $gump->filter_rules(array(
            'length' => 'trim|sanitize_numbers',
            'debug' => 'trim'
        ));
        $parameters = $gump->run($parameters);

        if($parameters === false) {
            echo $gump->get_readable_errors(true); //DEBUG
            $parameters = array();
        }

        $this->list_length = isset($parameters['length']) ? $parameters['length'] : NUM_ITEMS;

    }

    /* Return a top-list of laureate id's matching the filter */
    function getList(){
        $list = array();
        for($i = 1; $i <= $this->list_length; $i++){
          $list[] = $i;
        }

        return $list;

    }

    /* Get data for all laureates matching the filter */
    function getData() {
        $list = $this->getList();

        $laureates = array();
        foreach ($list as $id) {
            $url = $this->endPoint . '?id=' . $id;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            $response = curl_exec($ch);
            curl_close($ch);

            $laureate = json_decode($response, true)["laureates"][0];
            $laureates[] = array(
                    "name" => $laureate["firstname"] . " " . $laureate["surname"]
                );

        }

        return $laureates;
    }

}
