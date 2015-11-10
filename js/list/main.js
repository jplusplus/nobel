// Move to ui folder
function initUI() {
    
    // Init filters
    $(".toplist-filter-ui").each(function() {
        var $form = $(this);
        var $toplist = $( $form.attr("data-filter-for") );
        $form.find(".filter").on("change", function() {
            var $select = $(this);
            var key = $select.attr("name");
            var value = $select.val();
            value = (value == "null" && value == "") ? null : value;
            var filter = {};
            filter[key] = value;
            $toplist.data("filterset").changeFilter(filter);
        })
    })  

    // Init sparkline selector
    function showSparkline(sparklineToShow) {
        var sparklineToHide = sparklineToShow == "wikipedia" ? "page-views" : "wikipedia";
        $(".popularity." + sparklineToShow).removeClass("hide");
        $(".popularity." + sparklineToHide).addClass("hide");

    }
    $("#sparkline-select").on("change", function() {
        var sparklineToShow = $(this).val();
        showSparkline(sparklineToShow);
    })
    showSparkline($("#sparkline-select").val());
}
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

    /* inject CSS */
    var css = document.createElement("style");
    document.getElementsByTagName("head")[0].appendChild(css);
    var cssCode = "¤CSS";
    if (css.styleSheet) {
        // IE
        css.styleSheet.cssText += cssCode;
    } else {
        // Other browsers
        css.innerHTML += cssCode;
    }

    $(".toplist").each(function() {
        var $el = $(this);
        var id = $el.attr("id");

        var filterset = new FilterSet(["gender", "award", "country"], $el, gToplistSettings.endpoint);
        filterset.urlSync();
        topLists[id] = new TopList($el, filterset).initSparkLines();
    })

    initUI();
});


