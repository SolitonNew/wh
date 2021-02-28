<div class="list-group row row-cols-1 row-cols-sm-2 row-cols-lg-3" style="flex-direction: row; padding: 0 1rem; margin-bottom: 1px;">
    @foreach($rows as $row)
    <div class="list-group-item" style="margin-right:-1px;margin-bottom:-1px;border-top-width:1px;">
        <div class="room-item">
            <div class="room-item-name">
                @if($row->control->typ == 3)
                <a href="{{ route('terminal.variable', $row->data->ID) }}">{{ $row->control->title }}</a>
                @else
                {{ $row->control->title }}
                @endif
            </div>
            @if($row->control->typ == 1)
            <div class="room-text-value" id="variable_{{ $row->data->ID }}" app_control="1">
                <span class="room-item-variable-value">{{ $row->data->VALUE }}</span>
                <span class="room-item-variable-label">{{ $row->control->resolution }}</span>
            </div>
            @elseif($row->control->typ == 2)
            <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" app_control="2"
                       id="variable_{{ $row->data->ID }}" {{ $row->data->VALUE > 0 ? 'checked=""' : '' }} >
                <label class="custom-control-label" for="variable_{{ $row->data->ID }}"></label>
            </div>
            @elseif($row->control->typ == 3)
            <div class="room-text-value" id="variable_{{ $row->data->ID }}" app_control="3">
                <span class="room-item-variable-value">{{ $row->data->VALUE * $row->control->varStep }}</span>
                <span class="room-item-variable-label">{{ $row->control->resolution }}</span>
            </div>
            @endif
        </div>
        @if($row->control->typ == 1)
        <div class="variable-chart">
            <canvas id="chart_{{ $row->data->ID }}" height="100"></canvas>
        </div>
        @endif
    </div>
    @endforeach
</div>

<script type="text/javascript" src="/js/Chart.min.js"></script>
<script type="text/javascript" src="/js/Chart.bundle.min.js"></script>
<script type="text/javascript">
    var chartList = new Array();
    var serverCurrTime = {{ time() * 1000 }};
    var localDeltaTime = serverCurrTime - (new Date()).getTime();
    var chartTimeRange = 3 * 60 * 60 * 1000;
    var chartTimeOffset = 1 * 60 * 1000;
    var chartMaxTime = serverCurrTime + chartTimeOffset;
    var chartMinTime = chartMaxTime - chartTimeRange;
    
    @foreach($charts as $chart)
    var ctx = document.getElementById("chart_{{ $chart->ID }}");
    var chart = new Chart(ctx, {
        type: 'line',
        data: {
            datasets: [{
                data: [{{ $chart->data }}],
                lineTension: 0,
                @if($chart->color)
                backgroundColor: {!! $chart->color !!}
                @endif
            }]
        },
        options: {
            legend: {display: false},
            scales: {
                xAxes: [{
                    type: 'time',
                    time: {
                        unit: 'hour',
                        displayFormats: {
                            hour: 'HH:mm',
                        }
                    },
                    position: 'bottom',
                    ticks: {
                        min: chartMinTime,
                        max: chartMaxTime,
                    },
                }],
                yAxes: [{
                    ticks: {
                        stepSize: 1.0,
                    }
                }]
            },
            tooltips: {
                enabled: false,
            }
        }
    });
    
    chartList.push({
        id: {{ $chart->ID }},
        chart: chart
    });
    
    @endforeach
        
    let chartAutoscrollCounter = 0;
        
    function chartAutoScroll() {
        var chartMaxTime = (new Date()).getTime() + localDeltaTime + chartTimeOffset;
        var chartMinTime = chartMaxTime - chartTimeRange;
        
        for (let i = 0; i < chartList.length; i++) {
            chartList[i].chart.options.scales.xAxes[0].ticks.min = chartMinTime;
            chartList[i].chart.options.scales.xAxes[0].ticks.max = chartMaxTime;
        
            if (chartAutoscrollCounter == 0) {
                for (let k = chartList[i].chart.data.datasets[0].data.length - 1; k > -1; k--) {
                    if (chartList[i].chart.data.datasets[0].data[k].x < chartMinTime) {
                        chartList[i].chart.data.datasets[0].data.splice(0, k);
                        break;
                    }
                }
            }
            
            chartList[i].chart.update();
        }        
        
        chartAutoscrollCounter++;
        if (chartAutoscrollCounter > 6) {
            chartAutoscrollCounter = 0;
        }
        
        setTimeout(chartAutoScroll, {{ config('app.chart_update_interval') }});
    }
    
    chartAutoScroll();
    
    function chartAppendChanges(varID, varValue, varTime) {
        for (let i = 0; i < chartList.length; i++) {
            if (chartList[i].id == varID) {
                chartList[i].chart.data.datasets[0].data.push({
                    x: varTime,
                    y: varValue
                });
                chartList[i].chart.update();
            }
        }        
    }
    
    var variableSteps = [{{ $varSteps }}];
    
    function variableOnChanged(varID, varValue, varTime) {
        variableStep = 1;
        for (let i = 0; i < variableSteps.length; i++) {
            if (variableSteps[i].id == varID) {
                variableStep = variableSteps[i].step;
                break;
            }
        }
        
        var v = $('#variable_' + varID);
        switch (v.attr('app_control')) {
            case '1':
                $('.room-item-variable-value', v).text(varValue);
                chartAppendChanges(varID, varValue, varTime)
                break;
            case '2':
                v.prop('checked', parseInt(varValue) > 0);
                break;
            case '3':
                $('.room-item-variable-value', v).text(parseFloat(varValue) * variableStep);
                break;
        }
    }
    
</script>