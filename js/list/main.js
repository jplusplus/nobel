
/* 
==============
    INIT
==============
*/ 


var topLists = {};
$(document).ready(function() {

    $(".toplist").each(function() {
        var $el = $(this);
        var id = $el.attr("id");

        var filterset = new FilterSet(["gender", "award", "region", "popularity"], $el, gToplistSettings.endpoint);
        topLists[id] = new TopList($el, filterset).initSparkLines();
    });

});


