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
        var d = dateString.split("-");
        var year = +d[0];
        var month = +d[1] - 1;
        var day = +d[2];
        return new Date(year, month, day);
    }
    function dateToString(date, interval) {
        var dateString;
        var year = date.getFullYear();
        var month = date.getMonth() + 1;
        var day = date.getDate();
        if (interval >= 365) {
            // Date format if interval is year
            dateString = year;
        }
        else if (interval >= 30) {
            // Date format if interval is month or greater
            dateString = [year, pad(month, 2)].join("-");
        } 
        else {
            // Date format if interval is shorter than month
            dateString = [year, pad(month, 2), pad(day, 2)].join("-");
        }
        return dateString;
    }
    // 1 => 01
    function pad(n, width, z) {
      z = z || '0';
      n = n + '';
      return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
    }

    /*  Takes data about a person and renders a list item based on the list item
        template.
    */
    TopList.prototype.renderListItem = function(row) {
        var self = this;
        var $listItem = self.$listElementTemplate.clone();
        $listItem.find(".name a").text(row.name).attr("href", row.url);
        $listItem.find(".image").attr("src", row.image);
        $listItem.find(".gender").text(row.gender).attr("data-filter-value", row.gender);
        $listItem.find(".country").text(row.country);
        $listItem.find(".awards").text(row.awards.map(function(d) { return d.award + " ("+ d.year +")" }));
        if ( row.popularity ){
            $listItem.find(".popularity .sparkline").attr("data-values", row.popularity.join(","));
        }
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
        self.$list.find(".popularity").each(function() {
            var $el = $(this);
            var $sparkline = $el.find(".sparkline");
            var $title = $el.find(".title");

            // Interval is the number of days in am x bin
            var interval = +$sparkline.attr("data-interval");

            var startDate = parseDate($sparkline.attr("data-start-date"));
            
            // Chart title
            var popularitySource = self.filterset.currentFilters.popularity || "page-views";
            var sources = {
                "page-views": "Nobelprize.org",
                "wikipedia": "Wikipedia"
            }
            if (!$sparkline.attr("data-values")){
                $title.text("No data available");
                return;
            }
            $title.text("Page views on " + sources[popularitySource] + " since " + dateToString(startDate, interval));
            $sparkline.sparkline("html", {
                width: "200px",
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
                    var dateString = dateToString(date, interval);
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

            // Check if any laurates were returned
            // data.length will be 0 if no laurated match query 
            if (data.length == 0) {
                // No match
                self.$container.addClass("no-data");
            }
            else {
                // Match! => Render list
                self.$container.removeClass("no-data");

                data.forEach(function(row) {
                    var $li = self.renderListItem(row);
                    self.$list.append($li);
                })
                self.initSparkLines();
            }
        })
        .error(function(err) { console.log(err); })
    }
    TopList.prototype.clear = function() {
        var self = this;
        self.$list.empty();
    }
    return TopList;
})();