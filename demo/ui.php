<?php
/* Disable this script in development environments */
define('TopList', TRUE);
include "../settings.default.php";
include "../settings.php";
if ($debugLevel === PRODUCTION){
    die('Not permitted');
}
include "../list.php";

global $baseUrl;

?><!DOCTYPE html><html><head><title>List UI demo</title>
<!--<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/foundation/5.5.3/css/foundation.css" />-->

<link rel="stylesheet" href="<?php echo $baseUrl; ?>/css/foundation.min.css" />
<link rel="stylesheet" href="//www.nobelprize.org/css/nobel_custom.css?v=20141203" />

<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.0.0/styles/default.min.css" />

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
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.0.0/highlight.min.js"></script>
<script>
hljs.initHighlightingOnLoad();
/* No-js / jso-nly CSS support */
var htmlClassList = document.documentElement.classList;
htmlClassList.add("js");
</script>
