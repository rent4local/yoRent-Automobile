(function () {
    parseJsonData = function (t) {
        if (t == '')
            return(false);
        if (t == 'Your session seems to be timed out. Please try reloading the page.') {
            t = '{"status":0,"msg":"' + t + '"}';
        }
        try {
            var ans = eval('[' + t + ']');
            return(ans[0]);
        } catch (e) {
            return(false);
        }
    };

    topReferers = function (interval) {
        $('.topReferers').html('<li>' + fcom.getLoader() + '</li>');
        data = "rtype=top_referrers&interval=" + interval;

        fcom.ajax(fcom.makeUrl('home', 'dashboardStats'), data, function (t) {
            $('.topReferers').html(t);
        });
    };

    topCountries = function (interval) {
        $('.topCountries').html('<li>' + fcom.getLoader() + '</li>');
        data = "rtype=top_countries&interval=" + interval;

        fcom.ajax(fcom.makeUrl('home', 'dashboardStats'), data, function (t) {
            $('.topCountries').html(t);
        });
    };

    topProducts = function (interval) {
        $('.topProducts').html('<li>' + fcom.getLoader() + '</li>');
        data = "rtype=top_products&interval=" + interval;

        fcom.ajax(fcom.makeUrl('home', 'dashboardStats'), data, function (t) {
            $('.topProducts').html(t);
        });
    };

    getTopSearchKeyword = function (interval) {
        $('.topSearchKeyword').html('<li>' + fcom.getLoader() + '</li>');
        data = "rtype=top_search_keyword&interval=" + interval;
        fcom.ajax(fcom.makeUrl('home', 'dashboardStats'), data, function (t) {
            $('.topSearchKeyword').html(t);
        });
    };

    traficSource = function (interval) {
        $('#piechart').html(fcom.getLoader());
        data = "rtype=traffic_source&interval=" + interval;

        fcom.ajax(fcom.makeUrl('home', 'dashboardStats'), data, function (t) {
            var ans = parseJsonData(t);
            if (ans) {
                var dataTraficSrc = google.visualization.arrayToDataTable(ans);
                var optionsTraficSrc = {title: '', width: $('#piechart').width(), height: 360, pieHole: 0.4, pieStartAngle: 100, legend: {position: 'bottom', textStyle: {fontSize: 12, alignment: 'center'}}};
                var trafic = new google.visualization.PieChart(document.getElementById('piechart'));
                trafic.draw(dataTraficSrc, optionsTraficSrc);
            } else {
                $('#piechart').html(t);
            }
        });
    };

    visitorStats = function () {
        $('#visitsGraph').html(fcom.getLoader());
        data = "rtype=visitors_stats";

        fcom.ajax(fcom.makeUrl('home', 'dashboardStats'), data, function (t) {

            var ans = parseJsonData(t);

            if (ans) {
                var dataVisits = google.visualization.arrayToDataTable(ans);
                var optionVisits = {title: '', width: $('#visitsGraph').width(), height: 240, curveType: 'function',
                    legend: {position: 'bottom', },

                    hAxis: {direction: (layoutDirection == 'rtl') ? -1 : 1},
                    series: {
                        0: {
                            targetAxisIndex: (layoutDirection == 'rtl') ? 1 : 0},
                        1: {
                            targetAxisIndex: (layoutDirection == 'rtl') ? 1 : 0},
                        2: {
                            targetAxisIndex: (layoutDirection == 'rtl') ? 1 : 0},
                        3: {
                            targetAxisIndex: (layoutDirection == 'rtl') ? 1 : 0}
                    }
                };

                var visits = new google.visualization.LineChart(document.getElementById('visitsGraph'));
                visits.draw(dataVisits, optionVisits);
            } else {
                $('#visitsGraph').html(t);
            }
        });
    };

    searchStatistics = function (type, tab) {
        if (tab === undefined || tab === null) {
            tab = 'tabs_01';
        } else {
            tab = $(tab).attr('rel');
        }
        data = "type=" + type;
        $('#' + tab).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('home', 'searchStatistics'), data, function (t) {
            $('#' + tab).html(t);
        });
    };

    latestOrders = function () {
        $('#latest-orders-js').html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('home', 'latestOrders'), '', function (t) {
            $('#latest-orders-js').html(t);
        });
    };

})();

$(document).ready(function () {
    if (layoutDirection != 'rtl') {
        $position = 'start';
    } else {
        $position = 'end';
    }
    callChart('monthlysales--js', $SalesChartKey, $SalesChartVal, $position);
    $('.counter').each(function () {
        var $this = $(this),
                countTo = $this.attr('data-count');

        $({countNum: $this.text()}).animate({
            countNum: countTo
        },
                {
                    duration: 8000,
                    easing: 'linear',
                    step: function () {
                        if ($this.attr('data-currency') == 1) {
                            $this.text(dataCurrency + Math.floor(this.countNum));
                        } else {
                            $this.text(Math.floor(this.countNum));
                        }
                    },
                    complete: function () {
                        if ($this.attr('data-currency') == 1) {
                            $this.text(dataCurrency + this.countNum);
                        } else {
                            $this.text(this.countNum);
                        }
                    
                    }
                });
    });
});

$(window).on('load', function () {
    visitorStats();
    traficSource('yearly');
    topReferers('yearly');
    topCountries('yearly');
    getTopSearchKeyword('yearly');
    $('.carousel--oneforth-js').slick(getSlickSliderSettings(4));
    /* FUNCTION FOR SCROLLBAR */
    /* $('.scrollbar-js').enscroll({
        verticalTrackClass: 'scroll__track',
        verticalHandleClass: 'scroll__handle'
    }); */
    searchStatistics('statistics');
    latestOrders();
});
