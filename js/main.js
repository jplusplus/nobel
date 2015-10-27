/* 
=======================
FilterSet:
Handles the current filter state
=======================
*/ 

FilterSet = (function() {
    function FilterSet(availableFilters, endpoint) {
        var self = this;
        self.availableFilters = availableFilters;
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
        })
    }

    // Get the params from current url and apply them as filters
    FilterSet.prototype.urlSync = function() {
        var self = this;
        var params = getQueryParams(document.location.search);
        self.update(params);
    }

    // Pass an object with filters to update the current 
    // Example .update({ gender: "female" })
    FilterSet.prototype.update = function(args) {
        var self = this;
        for (key in args) {
            if (key in self.currentFilters) {
                self.currentFilters[key] = args[key];
            }
        }
    }

    FilterSet.prototype.asApiEndpoint = function() {
        var self = this;
        var url = self.endpoint + "?";
        for (key in self.currentFilters) {
            var value = self.currentFilters[key];
            if (value !== null) {
                url += key + "=" + value + "&";
            }
        }
        return url;
    }
    return FilterSet;
})();

TopList = (function() {
    function TopList(selector, filterset) {
        var self = this;
        self.$container = $(selector);
        self.$listElement = self.$container.find("li").first().clone(); 
        self.filterset = filterset;
    }
    TopList.prototype.update = function() {
        var self = this;
        var url = self.filterset.asApiEndpoint();

        $.getJSON(url, function(data) {
            data.forEach(function(row) {
                console.log(row);
            })
        })
    }
    return TopList;
})();



/* 
==============
    INIT
==============
*/ 

/*  Store the current state of the list this global variable
    Valid filters are defined here.
*/
var gFilters = new FilterSet(["gender", "award"], "list-api.php");
gFilters.urlSync();

var list = new TopList("ul", gFilters);

list.update();

