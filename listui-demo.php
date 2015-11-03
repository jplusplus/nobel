<?php
/* Disable this script in development environments */
/***************************************************/

include "list.php";

?><html><head><title>List UI demo</title>
<link rel="stylesheet" href="http://leowallentin.se/css/main.min.css" />
<link rel="stylesheet" href="//highlightjs.org/static/styles/github.css" />
</head><body><article>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/8.9.1/highlight.min.js"></script>
<script>hljs.initHighlightingOnLoad();</script>
<?php
