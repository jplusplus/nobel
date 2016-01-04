function initUI() {
    
    // Init filters
    $(".toplist-filter-ui").each(function() {
        var $form = $(this);
        var $toplist = $("#toplist-ui");
        $form.on("change", function() {

            $toplist.trigger("update");

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
                    $toplist.html( htmlBlob );
                    $toplist.trigger("init");
                },
                error: function(err) {
                    console.log(err);
                }
            });
        });
        var $reset = $form.find(".form-reset");
        $reset.on("click", function(){
            console.log("HEJ");
            $form.find(".filter").each(function() {
                var $select = $(this);
                var key = $select.attr("name");
                var value = $select.find("option:first").val();
                $select.val(value);
            })
            $form.trigger("change");
        });
    });
}
$(document).ready(function() {
    initUI();
});
