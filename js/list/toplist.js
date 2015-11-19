TopList = (function() {
    function TopList($container, filterset) {
        var self = this;
        self.$container = $container;
        self.$list = $container.find(".list");

        // Loading spinner
        // Source: http://tobiasahlin.com/spinkit/
        $container.append(
            $("<div>").attr("class", "loading-container").html(
                '<div class="spinner">' +
                    '<div class="dot1"></div>' +
                    '<div class="dot2"></div>' +
                '</div>'
            )
        )

        // Bind filterset to DOM element
        self.filterset = filterset;
        $container.data("filterset", filterset);
        
        //  Make a template out of the first element in the list
        self.$listElementTemplate = self.$list.find(".list-item").first().clone();

        $container.on("update", function() {
            self.update();
        });
    }
    // Take a datestring (20110101) and return a Date
    function parseDate(dateString) {
        var year = +dateString.substring(0,4);
        var month = +dateString.substring(4,6) - 1;
        var day = +dateString.substring(6,8);
        var dateString = dateString.split("-");
        return new Date(year, month, day);
    }
    function addMonths(date, n) {
        return date.setMonth(date.getMonth() + n);
    }

    /*  Takes data about a person and renders a list item based on the list item
        template.
    */
    TopList.prototype.renderListItem = function(row) {
        var self = this;
        var $listItem = self.$listElementTemplate.clone();
        $listItem.find(".name").text(row.name);
        $listItem.find(".image").attr("src", row.image);
        $listItem.find(".gender").text(row.gender).attr("data-filter-value", row.gender);
        $listItem.find(".country").text(row.country);
        $listItem.find(".awards").text(row.awards.map(function(d) { return d.award + "("+ d.year +")" }));        
        $listItem.find(".popularity .sparkline").attr("data-values", row.popularity.join(","));
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
            var $el = $(this);
            var startDate = parseDate($el.attr("data-start-date"));

            // Interval is the number of days in am x bin
            var interval = +$el.attr("data-interval");
            $(this).sparkline("html", {
                width: "100%",
                height: "2em",
                lineColor: "#666",
                fillColor: "#eee",
                minSpotColor: false,
                maxSpotColor: "#EEA200",
                highlightSpotColor: "#EEA200",
                highlightLineColor: "#EEA200",
                spotRadius: 2,
                chartRangeMin: 0,
                chartRangeMax: 200,
                tagValuesAttribute: "data-values",
                startDate: startDate,
                tooltipOffsetY: -10,
                //tooltipClassname: 'sparkline-tooltip',
                tooltipFormatter: function(sparkline, options, fields) {
                    var startDate = options.userOptions.startDate;
                    var date = new Date(startDate.getFullYear(), startDate.getMonth(), startDate.getDate())
                    var xValue = fields.x;

                    /*  Get the date of the hovered position by multiplying
                        the interval with the x-value (0,1,2,3...)
                    */
                    date.setDate(date.getDate() + xValue * interval);
                    var dateString;
                    if (interval >= 365) {
                        // Date format if interval is year
                        dateString = date.getFullYear();
                    }
                    else if (interval >= 30) {
                        // Date format if interval is month or greater
                        dateString = date.getFullYear() + "-" + (date.getMonth() + 1 );
                    } 
                    else {
                        // Date format if interval is shorter than month
                        dateString = date.getFullYear() + "-" + (date.getMonth() + 1 ) + "-" + date.getDate();
                    }
                    return "<div class='tooltip-content'>" + dateString +"</div>";
                }
            });
        });
        return self;
    }

    /*  Fetch data and update DOM
    */
    TopList.prototype.update = function() {
        var self = this;
        self.clear();
        self.$container.addClass("loading");
        var url = self.filterset.asApiEndpoint();
        $.getJSON(url, function(data) {
            self.$container.removeClass("loading");
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