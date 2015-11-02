<?php

include "list.php";

?><html><head><title>List widget demo</title>
<link rel="stylesheet" href="http://leowallentin.se/css/main.min.css" />
<link rel="stylesheet" href="//highlightjs.org/static/styles/github.css" />
</head><body><article>
    <header>
        <h1>List widget demo</h1>
    </header>

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
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/8.9.1/highlight.min.js"></script>
<script>hljs.initHighlightingOnLoad();</script>
<?php
