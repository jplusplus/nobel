
// Clear filters for an ui connected to a specific toplist
function clearFilters(topListID) {
    var $form = $('form[data-filter-for="#' + topListID + '"]');
    var $toplist = $('#' + topListID);
    var clearedFilters = {};

    // Select first option in filter selects
    $form.find(".filter:not([name='popularity'])").each(function() {
        var $select = $(this);
        var key = $select.attr("name");
        var value = $select.find("option:first").val();
        $select.val(value);
        clearedFilters[key] = value;
    })

    // Update list
    $toplist.data("filterset").changeFilter(clearedFilters);
}