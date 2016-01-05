<?php
/* Disable this script in development environments */
define('TopList', TRUE);
include "../settings.default.php";
include "../settings.php";
if ($debugLevel === PRODUCTION){
    die('Not permitted');
}
/***************************************************/

include "../list.php";

global $baseUrl;

?><!DOCTYPE html><html><head><title>List widget demo</title>
<link rel="stylesheet" href="<?php echo $baseUrl; ?>/css/foundation.min.css" />
<link rel="stylesheet" href="//www.nobelprize.org/css/nobel_custom.css?v=20141203" />

<link rel="stylesheet" href="main.min.css" />
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.0.0/styles/default.min.css" />
</head><body><article>
    <header>
        <h1>List widget demo</h1>
    </header>

    <p class="lead intro">Include list.php at the top of your code, and you are good to go:</p>
    <pre><code class="php5">require_once('list.php');</code></pre>
    <p>All PHP code is wrapped in a namespace called <code>TopList</code>. All Javascript code is wrapped in an object called <code>TopList</code>. There is also one variable in the global scope, called <code>gToplistSettings</code>. Except for these three objects, the code should be self contained, and not interfering with anything else.</p>

    <hr>
    <h2>Procedural style</h2>

    <h3>One-liner</h3>
    <pre><code class="php5">
        TopList\printWidget();
    </code></pre>
<?php

    TopList\printWidget();

?>
    <hr>
    <h3>With config</h3>
    <pre><code class="php5">
        $listFilter = array('gender' => 'female', 'region' => 'asia');
        TopList\printWidget( $listFilter );
    </code></pre>
<?php

    $listFilter = array('gender' => 'female', 'region' => 'asia', 'length' => 3);
    TopList\printWidget( $listFilter );


?>
    <hr>
    <h3>Using url parameters</h3>
    <pre><code class="php5">
        TopList\printWidget( $_GET );
    </code></pre>
<?php

    TopList\printWidget( $_GET );

?>
    <hr>
    <h2>Object oriented style</h2>

    <h3>Setting options one by one</h3>
    <pre><code class="php5">
        $widget = new TopList\Widget();
        $widget->gender = 'female';
        $widget->region = 'asia';
        $widget->printHTML();
    </code></pre>
<?php

    $widget = new TopList\Widget();
    $widget->gender = 'female';
    $widget->region = 'asia';
    $widget->printHTML();

?>
    <hr>
    <h3>Setting options on initiation</h3>
    <pre><code class="php5">
        $listFilter = array('gender' => 'female', 'region' => 'asia', 'length' => 3);
        $widget = new TopList\Widget( $listFilter );
        $widget->printHTML();
    </code></pre>
<?php

    $listFilter = array('gender' => 'female', 'region' => 'asia', 'length' => 3);
    $widget = new TopList\Widget( $listFilter );
    $widget->printHTML();

?>
    <hr>
    <h3>Custom parsing of output</h3>
    <pre><code class="php5">
        $widget = new TopList\Widget( );
        $html = $widget->getHTML();
        // do something with html
        echo( $html );
    </code></pre>
<?php

    $widget = new TopList\Widget( );
    $html = $widget->getHTML();
    // do something with html
    echo( $html );

?></article>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.0.0/highlight.min.js"></script>
<script>hljs.initHighlightingOnLoad();</script>
<?php
