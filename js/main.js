<<<<<<< HEAD
/* 
=======================
FilterSet:
Handles the current filter state
=======================
*/ 

FilterSet = (function() {
    function FilterSet(availableFilters) {
        var self = this;
        self.availableFilters = availableFilters;
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
        var this = self;
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
    return FilterSet;
})();

function renderList() {
    var endpoint = "list-api.php?";
    var currentFilters = gFilters.currentFilters;
    for (key in currentFilters) {
        var value = currentFilters[key];
        if (value !== null) {
            endpoint += key + "=" + value + "&";
        }
    }
    $.getJSON(endpoint, function(data) {
        console.log(data);
    })
}


/* 
==============
    INIT
==============
*/ 

/*  Store the current state of the list this global variable
    Valid filters are defined here.
*/
var gFilters = new FilterSet(["gender", "award"]);
gFilters.urlSync();

renderList()

