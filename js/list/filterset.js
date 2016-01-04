/* 
=======================
FilterSet:
Handles the current filter state
=======================
*/ 

FilterSet = (function() {

    function FilterSet(availableFilters, $elem, endpoint) {
        var self = this;
        self.availableFilters = availableFilters;
        self.currentFilters = {};
        self.availableFilters.forEach(function(key) {
            self.currentFilters[key] = null;
        });
    }

    return FilterSet;
})();
