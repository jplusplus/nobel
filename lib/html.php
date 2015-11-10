<?php
/* Contains all code producing list widgets and list controls */
namespace Toplist;
if(!defined('TopList')) {
   die('Not permitted');
}

/* Base class for html producing classes */
class TListHtml {

    var $dom;

    function __construct() {
        $this->dom = new \DOMDocument('1.0', 'utf-8');
    }

    protected function _createTag( $tag, $content = '', $attributes = array() ){
        $element = $this->dom->createElement($tag, $content);
        foreach ($attributes as $key => $val){
            $attr = $this->dom->createAttribute($key);
            $attr->value = $val;
            $element->appendChild($attr);
        }
        return $element;
    }

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

    /* Return everything under js/$dir/ and css/$dir as a string */
    protected function _getScripts( $dir ){
        global $baseDir;
        $js = $this->_loadFiles($baseDir . 'js/' . $dir . '/*.js');
        $css = $this->_loadFiles($baseDir . 'css/' . $dir . '/*.css');

        global $debugLevel;
        if ( $debugLevel > PRODUCTION ){
            $css = str_replace("\n", "", $css);
        }
        $js = str_replace('¤CSS', $css, $js);

        return $js;
    }

    /* Append a script tag with $js */
    protected function _appendScript( $js ){
        global $debugLevel;
        if ( $debugLevel > PRODUCTION ){
            $script = $this->dom->createElement('script', $js);
        } else {
            $script = $this->dom->createElement('script', \JShrink\Minifier::minify($js));
        }
        $this->dom->appendChild($script);
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
class TListWidget extends TListHtml {

    var $laureates;
    var $id;
    var $jsSettings;

    function __construct( TList $list, $id=0 ) {
        parent::__construct();

        global $baseUrl;
        $this->jsSettings = array( 'endpoint' => "$baseUrl/list-api.php",
                                  );
        $this->laureates = $list->getData();
        $this->id = $id;
    }

    function getHTML(){

        $id = $this->id;
        if ($id === 1){
            /* Append script tag with jQuery */
            $jquery_js = 'window.jQuery || document.write("<script src=\'https://code.jquery.com/jquery-2.1.4.min.js\'>\x3C/script>");';
            $script = $this->dom->createElement('script', $jquery_js);
            $this->dom->appendChild($script);

            /* Append script tag */
            $js = 'var gToplistSettings = ' . json_encode($this->jsSettings) . ';';
            $js .= $this->_getScripts('list');
            $this->_appendScript( $js );

        }

        $container = $this->_createTag( 'div', '', array('id' =>  'toplist-'.$id, 'class' => "toplist"));

        $list = $this->_createTag( 'ul', '', array( 'class' => 'list') );
        foreach ($this->laureates as $label => $laureate) {
            $li = $this->_createTag( 'li', '', array('class' => 'list-item',
                                                     'data-id' => $laureate['id'],
                                                     'data-name' => $laureate['name'],
                                                     'data-gender' => $laureate['gender'],
                                                     'data-awards' => json_encode($laureate['awards']),
                                                     )
                                    );

            // Wikipedia sparkline            
            $wikipediaContainer = $this->_createTag( 'div', '', array("class" => "popularity wikipedia" ));

            $wikipediaSparkline = $this->_createTag( 'span', '', array(
                                                            "class" => "sparkline",
                                                            "data-values" => implode(",", $laureate['popularity'])
                                                        ));
            $wikipediaTitle = $this->_createTag('span', 'Page views on Wikipedia, 2007-', array("class" => "title"));

            $wikipediaContainer -> appendChild($wikipediaSparkline);
            $wikipediaContainer -> appendChild($wikipediaTitle);

            $li->appendChild($wikipediaContainer);

            // Page view sparkline
            $pageViewContainer = $this->_createTag( 'div', '', array("class" => "page-views popularity" ));

            $pageViewSparkline = $this->_createTag( 'span', '', array(
                                                            "class" => "sparkline",
                                                            "data-values" => implode(",", $laureate['popularity'])
                                                        ));
            $pageViewTitle = $this->_createTag('span', 'Page views on nobel.se, 20XX-', array("class" => "title"));

            $pageViewContainer -> appendChild($pageViewSparkline);
            $pageViewContainer -> appendChild($pageViewTitle);

            $li->appendChild($pageViewContainer);

            // Image 
            $img = $this->_createTag( 'img', '', array("class" => "image", "src" => $laureate['image'] ));
            $li->appendChild($img);

            // Name of laureate
            $h3 = $this->_createTag( 'h3', '', array("class" => "name"));
            $a = $this->_createTag('a', $laureate["name"], array("href" => $laureate['laureates_url']));
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

        return $this->dom->saveHTML();

    }

}

/* This class represents a full UI (a list with filter controls) */
/* There can only be one full UI at the same page */
class TListUI extends TListHtml {

    function __construct() {
        parent::__construct();


    }

    function getHTML( $selectedParams = array()){

        $intro = $this->loremIpsum(250);
        $options = array('null' => 'Filter by award');
        $options += array(
                        'Physics' => 'Physics',
                        'Chemistry' => 'Chemistry',
                        'Literature' => 'Literature',
                        'Peace' => 'Peace',
                        'Physiology_or_Medicine' => 'Physiology or medicine',
                        'Economic_Sciences' => 'Economic sciences');
        $optionsCode = '';
        foreach ($options as $key => $value) {
            if ( isset($selectedParams['award']) && $selectedParams['award'] === $key ){
                $selected = ' selected';
            } else {
                $selected = ' ';
            }
            $optionsCode .= "<option value=\"$key\" $selected>$value</option>";
        }

        $this->dom->loadHTML(

<<<END
<form action="" method="GET" data-filter-for="#toplist-1" class="toplist-filter-ui">
 <p>$intro</p>
 <div class="row">
    <div class="small-6 columns">
        <label for="award-filter">Award</label>
        <select id="award-filter" class="filter" name="award">
            $optionsCode
        </select>
    </div>
    <div class="small-6 columns">
        <label for="gender-filter">Gender</label>
        <select id="gender-filter" class="filter" name="gender">
            <option value="null">Filter by gender</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
        </select>
    </div>
</div>
<div class="row">

    <div class="small-6 columns">
        <label for="region-filter">Region</label>
        <select id="region-filter" class="filter" name="region">
            <option value="null">Filter by region</option>
            <option value="asia">Asia</option>
            <option value="africa">Africa</option>
            <option value="europe">Europe</option>
            <option value="oceania">Oceania</option>
            <option value="america" class="optgroup">America</option>
            <option value="south-america" class="optchild">&nbsp;&nbsp;&nbsp;South</option>
            <option value="north-america" class="optchild">&nbsp;&nbsp;&nbsp;Nouth</option>
        </select>
    </div>
    <div class="small-6 columns">
        <label for="parkline-select">Popularity measure</label>
        <select id="sparkline-select" class="" name="sparkline-select">
            <option value="page-views">Page views</option>
            <option value="wikipedia">Wikipedia</option>
        </select>
    </div>
</div>

    
 <input type="submit" value="Submit" class="hideonjs button">
</form>
END
        , LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );

        $js = $this->_getScripts('ui');
        $this->_appendScript( $js );

        return $this->dom->saveHTML();
    }

}