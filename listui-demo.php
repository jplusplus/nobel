<?php
/* Disable this script in development environments */
/***************************************************/

include "list.php";

?><html><head><title>List UI demo</title>
<!--<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/foundation/5.5.3/css/foundation.css" />-->

<link rel="stylesheet" href="css/foundation.min.css" />
<link rel="stylesheet" href="//www.nobelprize.org/css/nobel_custom.css?v=20141203" />

<link rel="stylesheet" href="//highlightjs.org/static/styles/github.css" />

<style>
    /* FOR DEMO PAGE ONLY */
    body {
        background-image: none;
    }
</style>

</head><body><article class="row">


    <header>
        <h1>List UI demo</h1>
    </header>

    <p class="lead intro">Include list.php, and call TopList\printUI() to render the filter view.</p>

    <pre><code class="php5">
        include "list.php";
        TopList\printUI();
    </code></pre>

    <hr>

<?php

    TopList\printUI();


?></article>
<!--<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/8.9.1/highlight.min.js"></script>
<script>hljs.initHighlightingOnLoad();</script>-->
<?php
