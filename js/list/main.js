
/* 
==============
    INIT
==============
*/ 


var topLists = {};
var topListInitialized = false; //Prevent ready block to fire twice;
$(document).ready(function() {
    if(topListInitialized) return;
    topListInitialized = true;

    $(".toplist").each(function() {
        var $el = $(this);
        var id = $el.attr("id");

        var filterset = new FilterSet(["gender", "award", "region", "popularity"], $el, gToplistSettings.endpoint);
        filterset.urlSync();
        topLists[id] = new TopList($el, filterset).initSparkLines();
    })

    initUI();
});


