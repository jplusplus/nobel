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
            if (gToplistSettings.updateUrl && (typeof (history.pushState) != "undefined") ) {
                var urlPath = window.location.pathname + "?" + $form.serialize();
                window.history.pushState("toplist-ui", "", urlPath);
            }
        })
    });
}
$(document).ready(function() {
    initUI();
});
