TopList = (function() {
    function TopList($container, filterset) {
        var self = this;
        self.$container = $container;

        self.addLoader();
        
        // Bind filterset to DOM element
        self.filterset = filterset;
        
        $container.on("update", function() {
            self.update();
        });
        $container.on("init", function(){
            self.initSparkLines();
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

    TopList.prototype.addLoader = function(){

        // Loading spinner
        // Source: http://tobiasahlin.com/spinkit/
        this.$container.append(
            $("<div>").attr("class", "loading-container").html(
                '<div class="spinner">' +
                    '<div class="dot1"></div>' +
                    '<div class="dot2"></div>' +
                '</div>'
            )
        )
    }

    TopList.prototype.initSparkLines = function() {
        var self = this;
        self.$container.find(".popularity").each(function() {
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
            $title.text("Page views on " + sources[popularitySource] + " since " + dateToString(startDate, interval));
            var laureate_id = $sparkline.data("id");
            $.get( gToplistSettings["sparkline-endpoint"],
                   { id: laureate_id, popularity: popularitySource },
              function( data ) {
                if (!data){
                    $title.text("No data available");
                    return;
                }
                $sparkline.sparkline(data, {
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
//                    tagValuesAttribute: "data-values",
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


              }
            );

        });
        return self;
    }

    /*  Fetch data and update DOM
    */
    TopList.prototype.update = function() {
        var self = this;
        /* Replace content with loader */
        self.$container.html( "" );
        self.addLoader();
        self.$container.addClass("loading");
    }
    return TopList;
})();