<?php
/* Disable this script in development environments */
define('TopList', TRUE);
include "../settings.default.php";
include "../settings.php";
if ($debugLevel === PRODUCTION){
    die('Not permitted');
}
/***************************************************/

include "../gallery.php";
global $baseUrl;

?><!DOCTYPE html><html><head><title>Gallery widget demo</title>
<link rel="stylesheet" href="<?php echo $baseUrl; ?>/css/foundation.min.css" />
<link rel="stylesheet" href="//www.nobelprize.org/css/nobel_custom.css?v=20141203" />

<link rel="stylesheet" href="main.min.css" />
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.0.0/styles/default.min.css" />

<script src="<?php echo $baseUrl; ?>/js/jquery.min.js"></script>
<script src="<?php echo $baseUrl; ?>/js/foundation.min.js"></script>
<script src="<?php echo $baseUrl; ?>/js/foundation.orbit.js"></script>


</head><body><article>
    <header>
        <h1>Gallery widget demo</h1>
    </header>

    <p class="lead intro">Include gallery.php at the top of your code, and you are good to go:</p>
    <pre><code class="php5">require_once('gallery.php');</code></pre>
    <p>All PHP code is wrapped in a namespace called <code>TopList</code>. All Javascript code is wrapped in an object called <code>TopList</code>. There is also one variable in the global scope, called <code>gToplistSettings</code>. Except for these three objects, the code should be self contained, and not interfering with anything else.</p>

    <hr>
    <h2>Procedural style</h2>
    <p>Try a different laureate by adding <code>?id=x</code> to the url.</p>

    <pre><code class="php5">
        TopList\printGallery( @$_GET["id"] ?: 282 );
    </code></pre>
<?php

    TopList\printGallery( @$_GET["id"] ?: 282 );

?>
    <hr>
    <h2>Object oriented style</h2>

    <pre><code class="php5">
        $gallery = new TopList\Gallery( 4 );
        $gallery->printHTML();
    </code></pre>
<?php

    $gallery = new TopList\Gallery( 4 );
    $gallery->printHTML();

?>
    <hr>
    <h3>Custom parsing of output</h3>
    <pre><code class="php5">
        $gallery = new TopList\Gallery( 533 );
        $html = $gallery->getHTML();
        //Modify HTML
        $html .= "&lt;p>↑ This is a gallery&lt;/p>";
        echo( $html );
    </code></pre>
<?php

    $gallery = new TopList\Gallery( 533 );
    $html = $gallery->getHTML();
        //Modify HTML
    $html .= "<p>↑ This is a gallery</p>";
    echo( $html );

?></article>


<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.0.0/highlight.min.js"></script>
<script>hljs.initHighlightingOnLoad();</script>
<?php
