/* global console */
/* global visualizer */

(function($) {
    var all_charts;
    // so that we know which charts belong to our library.
    var rendered_charts = [];

    function renderChart(id, v) {
        renderSpecificChart(id, all_charts[id], v);
    }

    function renderSpecificChart(id, chart, v) {
        var render, container, series, data, table, settings, i, j, row, date, axis, property, format, formatter, type, rows, cols;

        if(chart.library !== 'chartjs'){
            return;
        }
        rendered_charts[id] = 'yes';

        series = chart.series;
        data = chart.data;

        container = document.getElementById(id);
        if (container == null) {
            return;
        }

        var myChart = new Chart(container, {
    type: 'bar',
    data: {
        labels: ['Red', 'Blue', 'Yellow', 'Green', 'Purple', 'Orange'],
        datasets: [{
            label: '# of Votes',
            data: [12, 19, 3, 5, 2, 3],
            backgroundColor: [
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(255, 159, 64, 0.2)'
            ],
            borderColor: [
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero: true
                }
            }]
        }
    }
});

        // allow user to extend the settings.
        $('body').trigger('visualizer:chart:settings:extend', {id: id, chart: chart, settings: settings});

        $('.loader').remove();
    }

    function render(v) {
        for (var id in (all_charts || {})) {
            renderChart(id, v);
        }
    }

    if(typeof visualizer !== 'undefined'){
        // called while updating the chart.
        visualizer.update = function(){
            renderChart('canvas', visualizer);
        };
    }

    $('body').on('visualizer:render:chart:start', function(event, v){
        all_charts = v.charts;
        render(v);
    });

    $('body').on('visualizer:render:specificchart:start', function(event, v){
        renderSpecificChart(v.id, v.chart, v.v);
    });

    // front end actions
    $('body').on('visualizer:action:specificchart', function(event, v){
        switch(v.action){
            case 'print':
                var id = v.id;
                if(typeof rendered_charts[id] === 'undefined'){
                    return;
                }
                $('#' + id + ' .buttons-print').trigger('click');
                break;
        }
    });

})(jQuery);


