
/* 
==============
    INIT
==============
*/ 

$(document).ready(function() {

    $(".toplist").each(function() {
        var $el = $(this);

        var filterset = new FilterSet(["gender", "award", "region", "popularity"], $el, gToplistSettings.endpoint);
        var toplist = new TopList($el, filterset).initSparkLines();
    });

});
