function initUI() {
    
    // Init filters
    $(".toplist-filter-ui").each(function() {
        var $form = $(this);
        $form.find(".filter").on("change", function() {
            if (gToplistSettings.updateUrl && (typeof (history.pushState) != "undefined") ) {
                var urlPath = window.location.pathname + "?" + $form.serialize();
                window.history.pushState("toplist-ui", "", urlPath);
            }

            // Load new list
            var url = gToplistSettings.endpoint + "?" + $form.serialize(); 
            $.ajax({
                url: url,
                type: "GET",
                dataType: "html",
                success: function(htmlBlob) {
                    $toplist = $("#toplist-ui");
                    $toplist.html( htmlBlob );
                    $toplist.trigger("init");
                },
                error: function(err) {
                    console.log(err);
                }
            })
        })
    });
}
$(document).ready(function() {
    initUI();
});
