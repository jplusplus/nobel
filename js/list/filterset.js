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
        self.$elem = $elem;
        self.endpoint = endpoint;
        self.reset();
    }
    function getQueryParams(qs) {
        qs = qs.split('+').join(' ');

        var params = {},
            tokens,
            re = /[?&]?([^=]+)=([^&]*)/g;

        while (tokens = re.exec(qs)) {
            params[decodeURIComponent(tokens[1])] = decodeURIComponent(tokens[2]);
        }
        return params;
    }

    // Clear filters
    FilterSet.prototype.reset = function() {
        var self = this;
        self.currentFilters = {};
        self.availableFilters.forEach(function(key) {
            self.currentFilters[key] = null;
        });
        self.triggerUpdate();
    }

    // Get the params from current url and apply them as filters
    FilterSet.prototype.urlSync = function() {
        var self = this;
        var params = getQueryParams(document.location.search);
        self.changeFilter(params);
    }

    // Pass an object with filters to update the current 
    // Example .changeFilter({ gender: "female" })
    FilterSet.prototype.changeFilter = function(args) {
        var self = this;
        for (key in args) {
            if (key in self.currentFilters) {
                self.currentFilters[key] = args[key];
            }
        }
        self.triggerUpdate();
    }

    // Send signal to list to update DOM
    FilterSet.prototype.triggerUpdate = function() {
        var self = this;
        self.$elem.trigger("update");
    }

    FilterSet.prototype.asApiEndpoint = function() {
        var self = this;
        var url = self.endpoint + "?"; 
        $.each(self.currentFilters, function(key, value) {
            if (key[0] !== "_") {
                url += key + "=" + value + "&";
            } 
        })
        return url;
    }
    return FilterSet;
})();