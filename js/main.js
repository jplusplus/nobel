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
        $("body").trigger("update-toplist");
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
        $listItem.find(".name").text(row.name);
        $listItem.find(".gender").text(row.gender).attr("data-filter-value", row.gender);
        $listItem.find(".country").text(row.country);
        $listItem.find(".awards").text(row.awards.map(function(d) { return d.award + "("+ d.year +")" }));
        return $listItem;
    }

    TopList.prototype.initFilterLinks = function() {
        var self = this;
        self.$container.find(".filterable").click(function(){
            var $el = $(this);
            var filter = {};
            var key = $el.attr("data-filter-key");
            var value = $el.attr("data-filter-value");
            filter[key] = value;
            gToplistSettings.state.changeFilter(filter);
        });
    }

    /*  Fetch data and update DOM
    */
    TopList.prototype.update = function() {
        var self = this;
        self.clear();
        var url = self.filterset.asApiEndpoint();
        console.log(url);
        $.getJSON(url, function(data) {
            data.forEach(function(row) {
                var $li = self.renderListItem(row);
                self.$container.append($li);
            })
            self.initFilterLinks();
        })
        .error(function(err) { console.log(err); })
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
gToplistSettings.state = new FilterSet(["gender", "award", "country"], gToplistSettings.endpoint);

// Get current filters from url params
gToplistSettings.state.urlSync();

var pageInitialized = false; //Prevent ready block to fire twice;
$(document).ready(function() {
    if(pageInitialized) return;
    pageInitialized = true;

    /* inject CSS */
    var css = document.createElement("style");
    console.log("I run!");
    document.getElementsByTagName("head")[0].appendChild(css);
    var cssCode = "             \
.filterable {                   \
    text-decoration: underline; \
    cursor: pointer;            \
}                               \
.list-item span {               \
    display: inline-block;      \
    padding: 0 4px;             \
}                               \
    ";
    if (css.styleSheet) {
        // IE
        css.styleSheet.cssText += cssCode;
    } else {
        // Other browsers
        css.innerHTML += cssCode;
    }

    var topList = new TopList(".toplist ul", gToplistSettings.state);
    topList.update();
    $("body").on("update-toplist", function() {
        topList.update();
    })
});


