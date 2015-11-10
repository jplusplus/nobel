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

TopList = (function() {
    function TopList($container, filterset) {
        var self = this;
        self.$container = $container;
        self.$list = $container.find(".list");

        // Bind filterset to DOM element
        self.filterset = filterset;
        $container.data("filterset", filterset);
        
        //  Make a template out of the first element in the list
        self.$listElementTemplate = self.$list.find(".list-item").first().clone();

        $container.on("update", function() {
            self.update();
        });
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
        $listItem.find(".popularity.sparkline").attr("data-values", row.popularity.join(","));
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

    TopList.prototype.initSparkLines = function() {
        var self = this;
        self.$list.find(".sparkline").each(function() {
            $(this).sparkline("html", {
                width: "100%",
                height: "2em",
                lineColor: "#666",
                fillColor: "#eee",
                maxSpotColor: "#EEA200",
                chartRangeMin: 0,
                tagValuesAttribute: "data-values"
            });
        });
        return self;
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
                self.$list.append($li);
            })
            self.initSparkLines();
            self.initFilterLinks();
        })
        .error(function(err) { console.log(err); })
    }
    TopList.prototype.clear = function() {
        var self = this;
        self.$list.empty();
    }
    return TopList;
})();



/* 
==============
    INIT
==============
*/ 


var topLists = {};

var pageInitialized = false; //Prevent ready block to fire twice;
$(document).ready(function() {
    if(pageInitialized) return;
    pageInitialized = true;

    /* inject CSS */
    var css = document.createElement("style");
    document.getElementsByTagName("head")[0].appendChild(css);
    var cssCode = "¤CSS";
    if (css.styleSheet) {
        // IE
        css.styleSheet.cssText += cssCode;
    } else {
        // Other browsers
        css.innerHTML += cssCode;
    }

    $(".toplist").each(function() {
        var $el = $(this);
        var id = $el.attr("id");

        var filterset = new FilterSet(["gender", "award", "country"], $el, gToplistSettings.endpoint);
        filterset.urlSync();
        topLists[id] = new TopList($el, filterset).initSparkLines();
    })

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
        })
    })
});


