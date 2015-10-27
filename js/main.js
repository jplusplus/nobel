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
            url += key + "=" + value + "&";
        }
        return url;
    }
    return FilterSet;
})();

TopList = (function() {
    function TopList(selector, filterset) {
        var self = this;
        self.$container = $(selector);
        
        //  Make a template out of the first element in the list
        self.$listElementTemplate = self.$container.find("li").first().clone(); 
        self.filterset = filterset;
    }

    /*  Takes data about a person and renders a list item based on the list item
        template.
    */
    TopList.prototype.renderListItem = function(row) {
        var self = this;
        var $listItem = self.$listElementTemplate.clone();
        $listItem.text(row.name);
        return $listItem;
    }

    /*  Fetch data and update DOM
    */
    TopList.prototype.update = function() {
        var self = this;
        self.clear();

        var url = self.filterset.asApiEndpoint();
        $.getJSON(url, function(data) {
            data.forEach(function(row) {
                var $li = self.renderListItem(row);
                self.$container.append($li);
            })
        })
    }
    TopList.prototype.clear = function() {
        var self = this;
        self.$container.empty();
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
var gFilters = new FilterSet(["gender", "award"], gToplistSettings.endpoint);

// Get current filters from url params
gFilters.urlSync();

$(document).ready(function() {
    list = new TopList("ul", gFilters);
    gFilters.update({ award: "Chemistry" });
    list.update();    
});


