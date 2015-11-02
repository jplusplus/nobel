<?php

include "list.php";

?><html>
<head>
<title>Test page</title>
<link rel="stylesheet" href="http://leowallentin.se/css/main.min.css" />
<link rel="stylesheet" href="//highlightjs.org/static/styles/github.css" />
</head>
<body><article>
    <header>
        <h1>List widget demo</h1>
    </header>

    <h2>Procedure style</h2>
    <pre><code class="php5">
        TopList\printWidget();
    </code></pre>
<?php

    TopList\printWidget();

?>
    <h2>Procedure style, with config</h2>
    <pre><code class="php5">
        $listFilter = array('gender' => 'female', 'region' => 'asia');
        TopList\printWidget( $listFilter );
    </code></pre>
<?php

    $listFilter = array('gender' => 'female', 'region' => 'asia');
    TopList\printWidget( $listFilter );


?>
    <h2>User url parameters</h2>
    <pre><code class="php5">
        TopList\printWidget( $_GET );
    </code></pre>
<?php

    TopList\printWidget( $_GET );

?>
    <h2>Object oriented style, 1</h2>
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
    <h2>Object oriented style, 2</h2>
    <pre><code class="php5">
        $widget = new TopList\Widget( $listFilter );
        $widget->printHTML();
    </code></pre>
<?php

    $widget = new TopList\Widget( $listFilter );
    $widget->printHTML();

?>
    <h2>Object oriented style, 3</h2>
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