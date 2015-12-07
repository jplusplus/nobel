<?php
/* Contains a class for querying the MediaWiki API at a Wikipedia
*/ 
namespace Toplist;
if(!defined('TopList')) {
   die('Not permitted');
}

require_once $baseDir . 'lib/external-data.php';

Class WikipediaQuery extends ExternalData {

    var $endPoint;

    function __construct( $edition ){
        $this->endPoint = "https://$edition.wikipedia.org/w/api.php";
    }

    /* Get all images used at the page $title */
    function getImages( $title, $width=300, $height=null ) {

        $params = array(
            'action'    => 'query',
            'prop'      => 'imageinfo',
            'generator' => 'images',
            'iiprop'    => 'extmetadata|mediatype|size|url',
            'iiextmetadatalanguage' => 'en',
            'format'    => 'json',
            'titles'    => $title,
        );
        if ($height) {
            $params['iiurlheight'] = $height;
        } elseif ($width) {
            $params['iiurlwidth'] = $width;
        }

        $url = $this->endPoint . '?' . http_build_query( $params );

        global $gExternalLaureateDataCacheTime;
        $response = $this->fetchAndCache( $url, $gExternalLaureateDataCacheTime, function( $response ){
            $images = array();
            if (!array_key_exists('query', $response)){
                /* invalid page or no images */
                return $images;
            }

            $pages = $response["query"]["pages"];
            global $debugLevel;
            if ( $debugLevel >= VERBOSE ){
                $num = count($pages);
                error_log( "Gallery: Found $num image pages." );
            }
            foreach ( $pages as $page ){
                $titleParts = explode(':', $page["title"]); // Add only part after ':'
                $title = $titleParts[1];

                global $gImageBlacklist;
                if ( !in_array( $title, $gImageBlacklist ) ){
                    $allImageNames[] = $title;
                    $imgInfo = array_pop($page["imageinfo"]);
                    if ( $imgInfo["mediatype"] === 'BITMAP' &&
                         $imgInfo["width"] > 200 &&
                         $imgInfo["height"] > 280 ){

                        $metaData = $imgInfo["extmetadata"];

                        $attributionRequired = ('true' === @$metaData["AttributionRequired"]["value"]);
                        $cred = '';
                        if ($attributionRequired){
                            $cred .= @$metaData["LicenseShortName"]["value"] ?: @$metaData["LicenseShortName"]["value"];
                            $cred .= ', ';
                            $cred .= implode(' ', array( strip_tags(@$metaData["Credit"]["value"]), strip_tags(@$metaData["Artist"]["value"]) ));
                        }
                        $images[] = array (
                            "caption"   => strip_tags(@$metaData['ImageDescription']['value'] ?: ''),
                            "credit"    => $cred,
                            "url"       => $imgInfo['thumburl'],
                            "sourceurl" => $imgInfo['descriptionurl'],

                        );
                    }
                }

            }

            return $images;
        });
        return $response;
    }

}
