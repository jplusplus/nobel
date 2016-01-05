<?php
/* Contains all code producing list widgets and list controls */
namespace Toplist;
if(!defined('TopList')) {
   die('Not permitted');
}
require $baseDir . 'vendor/autoload.php';

/* Counter singleton for html snippets */
class SnippetCounter {

    private $CurrentValue = 0;
    private static $m_pInstance; 

    private function __construct() {}

    public static function getInstance() {
        if (!self::$m_pInstance) {
            self::$m_pInstance = new SnippetCounter();
        }
        return self::$m_pInstance;
    }

    public function getNext() {
        $this->CurrentValue += 1;
        return $this->CurrentValue;
    }

}

/* Base class for html producing classes */
class Html {

    var $dom;
    var $fragmentNumber;
    var $css;
    var $printScripts;

    function __construct( $printScripts=true ) {
        $this->printScripts = $printScripts;
        $this->dom = new \DOMDocument('1.0', 'utf-8');
        /* keep track of the html fragments */
        $this->fragmentNumber = SnippetCounter::getInstance()->getNext();
        if ( ($this->fragmentNumber === 1) && $this->printScripts ){
            /* Make sure jQuery loads */
            $jquery_js = 'window.jQuery || document.write("<script src=\'https://code.jquery.com/jquery-2.1.4.min.js\'>\x3C/script>");';
            $script = $this->dom->createElement('script', $jquery_js);
            $this->dom->appendChild($script);
            
            /* add commons css to resource loader queue */
            $this->_addStyles('common');

        }
    }

    protected function _truncate( $text, $chars ) {
        if ( strlen($text) > $chars ){
            $text = $text . " ";
            $text = substr( $text, 0, $chars );
            $text = substr( $text, 0, strrpos( $text, ' ' ) );
            $text = $text . "…";
        }
        return $text;
    }

    protected function _createTag( $tag, $content = '', $attributes = array() ){
        $element = $this->dom->createElement($tag, $content);
        foreach ($attributes as $key => $val){
            $attr = $this->dom->createAttribute($key);
            $attr->value = htmlentities($val);
            $element->appendChild($attr);
        }
        return $element;
    }

    protected function _appendHtml( $html, &$tag ){
            $captionDom = new \DOMDocument();
            $captionDom->loadHTML( '<?xml encoding="utf-8" ?><div>' . $html . '</div>');
            $tempImported = $this->dom->importNode($captionDom->getElementsByTagName('div')->item(0), true);
            $tag->appendChild($tempImported);
    }

    /* Child classes should always call this 
       function before returning their HTML
    */
    protected function _finalizeHtml() {

        if ( $this->printScripts ){
            $js = $this->_getScripts('common', 'js');
            $css = htmlentities($this->css);
            global $debugLevel;
            if ( $debugLevel > PRODUCTION ){
                $css = str_replace("\n", "\\\n", $css);
            } else {
                $css = preg_replace('/[\n\r](\s+)?/', '', $css);
            }
            $js .= <<<END
$(document).ready(function() {
/* inject CSS */
var css = document.createElement("style");
document.getElementsByTagName("head")[0].appendChild(css);
var cssCode = "$css";
if (css.styleSheet) {
    // IE
    css.styleSheet.cssText += cssCode;
} else {
    // Other browsers
    css.innerHTML += cssCode;
}
});
END;
            $this->_appendScript( $js );
        }
        return $this->dom->saveHTML();
    } 

    /* Echo returned HTML to the screen */
    public function printHTML( $params = null ){
        echo $this->getHTML( $params );
    }

    /* Concat and return content of all files */
    private function _loadFiles( $path ){
        $files = glob($path);
        $str = '';
        foreach( $files as $file ){
            $str .= htmlspecialchars(file_get_contents($file));
        }
        return $str;

    }

    /* retun an array of <option> html strings */
    protected function _createOptions( array $options, $selected=null, $level=0, $optgroup=null ){
        $output = array();
        foreach ($options as $key => $value) {
            if (is_array($value)){
                $subarray = $this->_createOptions( $value, $selected, $level+1, $key );
                $output = array_merge( $output, $subarray);
            } else {
                if ( $selected === $key ){
                    $selectedStr = ' selected';
                } else {
                    $selectedStr = '';
                }
                $indent = '';
                if ($level){
                    $classes = array();
                    if ($optgroup === $key){
                        $classes[] = 'optgroup';
                        $indent = str_repeat( "&nbsp;&nbsp;&nbsp;", $level-1 );
                    } else {
                        $classes[] = 'optchild';
                        $indent = str_repeat( "&nbsp;&nbsp;&nbsp;", $level );
                    }
                    $classes[] = "level-$level";

                    $class = implode(' ', $classes);
                } else {
                    $class = '';
                }
                $output[] = "<option value=\"$key\" class=\"$class\" $selectedStr>$indent$value</option>";
            }
        }
        return $output;
    }

    /* Return everything under js/$dir/ or css/$dir as a string */
    protected function _getScripts( $dir, $type ){
        if (!in_array($type, array('js', 'css'))){
            return null;
        }
        global $baseDir;
        $path = $baseDir . $type . '/' . $dir . '/*.' . $type;
        $script = $this->_loadFiles($path);
        $script = str_replace('"', '\"', $script);
        return $script;
    }

    /* Append a script tag with $js */
    protected function _appendScript( $js ){
        if ( !$js ){
            return null;
        }

        global $debugLevel;
        if ( $debugLevel > PRODUCTION ){
            $script = $this->dom->createElement('script', $js);
        } else {
            $script = $this->dom->createElement('script', $js);
            // bug in the lib: https://github.com/tedious/JShrink/issues/13
            // $script = $this->dom->createElemet('script', \JShrink\Minifier::minify($js));
        } 
        $this->dom->appendChild($script);
    }

    protected function _addStyles( $dir ){
        $this->css .= $this->_getScripts($dir, 'css');
    }

    /* Return $length characters of lorem ipsum. */
    public function loremIpsum( $length ){

        $lorem = <<<END
        Posse dicta cotidieque ei eum, at illud decore regione mei, everti eripuit cu quo. Graeco perfecto id est, vis legere iuvaret definitiones no. Quo imperdiet consectetuer et, per cu rebum tractatos conceptam. Quot ponderum gubergren cu mei, ea sed adhuc idque quaerendum, no inimicus vulputate usu.
Vim at invidunt volutpat, ne vel atqui timeam singulis. At veri dissentiet deterruisset per, solet discere eu eum. Et has prompta placerat perpetua, eruditi ocurreret vituperatoribus no mea. Putent conceptam incorrupte an vix, mel ei veri ponderum. Amet falli dicam ei qui, sit aliquam consequat ea, mea dolor nominavi gubergren ut.
Impedit fabellas ad vis, eu lucilius expetenda quo, aeterno saperet cu mel. Duo ut meis contentiones, tation graecis instructior at cum. Mea ex persius convenire patrioque, magna constituto sit et, id mel odio minimum signiferumque. Id ius esse justo mnesarchum, mel dicit disputando deterruisset ne, has soleat inermis efficiantur no.
Inani habemus atomorum vim ad, ludus docendi euripidis his no, aliquid electram percipitur ea quo. Cum rebum labores an, et has ornatus dolorem. Te has vidit ocurreret, adolescens deseruisse ad per, eam verear necessitatibus cu. Ipsum detracto corrumpit ne his, cu harum iudicabit est. Quaeque meliore dissentiunt ea eum, tation dissentiet duo ex.
Nonumy animal aliquip usu eu, te paulo laoreet sed, autem illud nobis sea eu. Sea no tota civibus ullamcorper, id usu oratio doctus quaerendum, an ferri utinam vix. Et agam officiis eum, eos at alii philosophia voluptatibus. Diam corrumpit disputando ex quo. Ferri maluisset persecuti ad mel, ut equidem tibique ullamcorper usu. Nec cu vocent latine fastidii, vel ut scripta pericula accusamus.
Id nec enim facer ancillae. Pri ut possit consulatu, pro te eius insolens, vis eu nihil dissentias. Pro eu graeci noster, vis epicuri molestie rationibus in. Ius ne hinc liber consulatu, duo cu malis doctus minimum.
END;
        $str = mb_substr( $lorem, 0, $length);
        $lastSpace = strrpos( $str, " " );
        if ($lastSpace) {
            $str = mb_substr( $str, 0, $lastSpace);
        }
        return $str;
    }

}

/* This class represents a listwidget */
class TListWidget extends Html {

    var $laureates;
    var $id;
    var $jsSettings;
    var $printScripts;

    function __construct( $list, $id=0, $printScripts = true ) {
        parent::__construct( $printScripts );

        global $baseUrl;
        global $gStatsStart;
        global $gStatsInterval;
        global $gUpdateUrl;
        $this->jsSettings = array( 'endpoint' => "$baseUrl/listhtml-api.php",
                                   'sparkline-endpoint' => "$baseUrl/sparklines-api.php",
                                   'statsStart' => $gStatsStart,
                                   'statsInterval' => $gStatsInterval,
                                   'updateUrl' => $gUpdateUrl,
                                  );
        $this->laureates = $list;
        $this->id = $id;
    }

    function getHTML(){

        if ($this->id){
            $id = $this->id;    
        } else {
            $id = $this->fragmentNumber;
        }
        if ( $this->id && $this->printScripts ){
            /* Add gToplistSettings */
            $js = 'var gToplistSettings = ' . json_encode($this->jsSettings) . ';';
            $js .= $this->_getScripts('list', 'js');
            $this->_appendScript( $js );

            $this->_addStyles('list');
        }

        $container = $this->_createTag( 'div', '', array('id' =>  'toplist-'.$id, 'class' => "toplist"));
        if ( count($this->laureates) === 0 ){
            $noDataMessage = $this->_createTag('div', 'Sorry, there are no laureates that match your query.', array("class" => "no-data-message"));
            $container->appendChild($noDataMessage); 
        }

        $list = $this->_createTag( 'ul', '', array( 'class' => 'list') );
        foreach ($this->laureates as $label => $laureate) {
            $li = $this->_createTag( 'li', '', array('class' => 'list-item',
                                                     'data-id' => $laureate['id'],
                                                     'data-name' => $laureate['name'],
                                                     'data-gender' => $laureate['gender'],
                                                     'data-awards' => json_encode($laureate['awards']),
                                                     )
                                    );

            // Popularity sparkline            
            $popularityContainer = $this->_createTag( 'div', '', array("class" => "popularity" ));

            global $gStatsStart;
            global $gStatsInterval;
            if (preg_match('/^\d{8}/', $gStatsStart)){
                /* A date */
                $statsStart = $gStatsStart;
            } else {
                /* Assume an offset */
                global $gTimezone;
                $date = new \DateTime( 'now', new \DateTimeZone($gTimezone) );
                $date->add(\DateInterval::createFromDateString('-'.$gStatsStart));
                $statsStart = $date->format('Y-m-d');
            }

            $popularitySparkline = $this->_createTag( 'span', '', array(
                                                            "data-id" => @$laureate['id'] ?: '',
                                                            "class" => "sparkline",
                                                            "data-start-date" => $statsStart,
                                                            "data-interval" => $gStatsInterval,
                                                            "data-values" => @implode(",", $laureate['popularity']) ?: '',
                                                        ));
            $popularityTitle = $this->_createTag('span', '', array("class" => "title"));

            $popularityContainer -> appendChild($popularitySparkline);
            $popularityContainer -> appendChild($popularityTitle);

            $li->appendChild($popularityContainer);


            // Image 
            $img = $this->_createTag( 'img', '', array( 'alt' => $laureate['name'],
                                                        'class' => "image",
                                                        "src" => $laureate['image'] ));
            $li->appendChild($img);

            // Name and link to laureate
            $href = $laureate["url"];
            $h3 = $this->_createTag( 'h3', '', array("class" => "name"));
            $a = $this->_createTag('a', $laureate["name"], array("href" => $href));
            $h3->appendChild($a);
            $li->appendChild($h3);

            /*$genderspan = $this->_createTag( 'span', $laureate["gender"], array(
                                                                            "class" => "gender filterable",
                                                                            "data-filter-key" => "gender",
                                                                            "data-filter-value" => $laureate["gender"]
                                                                            ));
            $li->appendChild($genderspan);*/
            
            $awardsString = implode(', ', array_map(function($el){
                                                        return str_replace( '_', ' ', $el['award'] ) . ' (' . $el['year'] . ')';
                                                    }, $laureate['awards']));
            $awardspan = $this->_createTag( 'span', $awardsString, array("class" => "awards"));
            $li->appendChild($awardspan);


            $list->appendChild($li);
        }
        $container->appendChild($list);
        $this->dom->appendChild($container);

        return $this->_finalizeHtml( $this->printScripts );

    }

}

/* This class represents a full UI (a list with filter controls) */
/* There can only be one full UI at the same page */
class TListUI extends Html {

    function __construct() {
        parent::__construct();
    }

    function getHTML( $selectedParams = array()){

        $awardOptions = array('null' => 'Filter by award');
        $awardOptions += array(
                        'Physics' => 'Physics',
                        'Chemistry' => 'Chemistry',
                        'Literature' => 'Literature',
                        'Peace' => 'Peace',
                        'Physiology_or_Medicine' => 'Physiology or medicine',
                        'Economic_Sciences' => 'Economic sciences');
        $awardOptionsCode = implode("\n", $this->_createOptions($awardOptions, isset($selectedParams['award']) ? $selectedParams['award'] : null ));

        $genderOptions = array('null' => 'Filter by gender');
        $genderOptions += array(
                        'male' => 'Male',
                        'female' => 'Female',
                        );
        $genderOptionsCode = implode("\n", $this->_createOptions($genderOptions, isset($selectedParams['gender']) ? $selectedParams['gender'] : null ));

        $regionOptions = array('null' => 'Filter by region');
        $regionOptions += array(
                        'africa' => 'Africa',
                        'america' => array(
                            'america' => 'America',
                            'south-america' => 'South',
                            'north-america' => array(
                                    'north-america' => 'North',
                                    'carribean' => 'Carribean',
                                    'central-america' => 'Central America',
                                ),
                            ),
                        'asia' => array(
                            'asia' => 'Asia',
                            'central-asia' => 'Central',
                            'east-asia' => 'East',
                            'middle-east' => 'Middle East',
                            'south-asia' => 'South',
                            'southeast-asia' => 'Southeast',
                            ),
                        'europe' => 'Europe',
                        'oceania' => 'Oceania',
                        );

        $regionOptionsCode = implode("\n", $this->_createOptions($regionOptions, isset($selectedParams['region']) ? $selectedParams['region'] : null ));

        $statOptions = array(
                        'page-views' => 'Page views',
                        'wikipedia' => 'Wikipedia',
                    );
        $statOptionsCode = implode("\n", $this->_createOptions($statOptions, isset($selectedParams['popularity']) ? $selectedParams['popularity'] : null ));

        global $gUIIntro;
        $formCode = 
<<<END
<form method="GET" data-filter-for="#toplist-ui" class="toplist-filter-ui" id="toplist-ui-form">
 <p>$gUIIntro</p>
 <div class="row">
    <div class="small-6 columns">
        <label for="award-filter">Award</label>
        <select id="award-filter" class="filter" name="award">
            $awardOptionsCode
        </select>
    </div>
    <div class="small-6 columns">
        <label for="gender-filter">Gender</label>
        <select id="gender-filter" class="filter" name="gender">
            $genderOptionsCode
        </select>
    </div>
</div>
<div class="row">

    <div class="small-6 columns">
        <label for="region-filter">Region</label>
        <select id="region-filter" class="filter" name="region">
            $regionOptionsCode
        </select>
    </div>
    <div class="small-6 columns">
        <label for="sparkline-select">Popularity measure</label>
        <select id="sparkline-select" class="filter" name="popularity">
            $statOptionsCode
        </select>
    </div>
</div>
<a href="#" class="form-reset jsonly">Clear filters</a>
    
<input type="submit" value="Submit" class="hideonjs button">
</form>
END;

        $this->_appendHtml($formCode, $this->dom);

        $js = $this->_getScripts('ui', 'js');
        $this->_appendScript( $js );

        $this->_addStyles('ui');

        return $this->_finalizeHtml();
    }

}


class TGalleryWidget extends Html {

    var $imageList;

    /* construct using an array from gallery-api.php */
    function __construct( $imageList ) {
        parent::__construct();
        if (is_array( $imageList )){
            $this->imageList = $imageList;
        } else {
            $this->imageList = array();

        }
    }

    function getHTML(){
        $js = $this->_getScripts('gallery', 'js');
        $this->_appendScript( $js );
        $this->_addStyles('gallery');

        $ulTag = $this->_createTag( 'ul', '', array(
                                                "class" => "gallery",
                                                "data-orbit" => null,
                                   ) );

        foreach ($this->imageList as $list) {
            $liTag = $this->_createTag('li');
            
            $divTag = $this->_createTag('div', '', array( 'class' => 'orbit-caption'));
            $caption = $list['caption'];
            $caption = $this->_truncate( $caption, 200 );

            if ($list['credit'] !== ''){
                $caption .= ' <i>' . $list['credit'] . '</i>';
            }
            $caption .= ' <a href="' . $list['sourceurl'] . '">Image from Wikimedia Commons</a>';
            $this->_appendHtml($caption, $divTag);

            $imgTag = $this-> _createTag('div', '', array(
                'class' => 'image-wrapper',
                'style' => 'background-image: url(' . $list['url'] . ');' 
            ));

            $liTag->appendChild($imgTag);
            $liTag->appendChild($divTag);

            $ulTag->appendChild($liTag);
        }

        $this->dom->appendChild($ulTag);
        return $this->_finalizeHtml();

    }
}