<?php

$roomID = (int)$_GET['roomID'];

$sql = "select NAME from plan_parts where ID = $roomID";
$q = $pdo->query($sql);
$rows = $q->fetchAll();

$groupTitle = '';
if (count($rows) > 0) {
    $groupTitle = mb_strtoupper($rows[0]['NAME']);
}

$q = $pdo->query("select VALUE from core_properties where NAME = 'WEB_COLOR'")->fetchAll();
$web_color = $q[0]['VALUE'];
if ($web_color) {
    $web_color = json_decode($web_color, true);
} else {
    $web_color = [];
}

?>

<nav aria-label="breadcrumb">
    <ol class="row breadcrumb">
        <li class="breadcrumb-item"><a href="/"><?php print($MAIN_MENUS['main']); ?></a></li>
        <li class="breadcrumb-item active" aria-current="page"><?php print($groupTitle); ?></li>
    </ol>
</nav>

<div class="list-group row row-cols-1 row-cols-sm-2 row-cols-lg-3" style="flex-direction: row; padding: 0 1rem; margin-bottom: 1px;">

<?php

$sql = "select v.* from core_variables v " .
       " where v.GROUP_ID = $roomID " .
       "  and APP_CONTROL in (1, 3, 4, 5, 7, 10, 11, 13, 14) ".
       " order by v.NAME";

$q = $pdo->query($sql);

$rows = [];
foreach ($q as $row) {
    $c = decodeAppControl($row['APP_CONTROL']);
    $itemLabel = groupVariableName($groupTitle, mb_strtoupper($row['COMM']), mb_strtoupper($c['label']));
    $c['title'] = $itemLabel;
    
    $rows[] = [
        'DATA' => $row, 
        'CONTROL' => $c
    ];
}

usort($rows, function ($item1, $item2) {
    return $item1['CONTROL']['title'] > $item2['CONTROL']['title'];
});

$charts = [];
$colors = [];
$varSteps = [];

foreach ($rows as $row) {
    $itemLabel = $row['CONTROL']['title'];
    $typ = $row['CONTROL']['typ'];
    $resolution = $row['CONTROL']['resolution'];
    $varStep = $row['CONTROL']['varStep'];
    
    $color = '';
    for ($i = 0; $i < count($web_color); $i++) {
        if (mb_strpos(mb_strtoupper($itemLabel), mb_strtoupper($web_color[$i]['keyword'])) !== false) {
            $color = $web_color[$i]['color'];
            if ($color) {
                $color = "'$color'";
            }
            break;
        }
    }
    
    $varID = $row['DATA']['ID'];
    $value = $row['DATA']['VALUE'] * $varStep;
    
    $varSteps[] = [
        'id' => $varID, 
        'step' => $varStep
    ]; 
    ?>
    <div class="list-group-item" style="margin-right:-1px;margin-bottom:-1px;border-top-width:1px;">
        <div class="room-item">
            <div class="room-item-name">
                <?php if ($typ == 3) { ?>
                <a href="?page=variable&varID=<?php print($varID); ?>">
                <?php } ?>
                <?php print($itemLabel); ?>
                <?php if ($typ == 3) { ?>
                </a>
                <?php } ?>
            </div>
            <?php
            if ($typ == 1) {
            ?>
            <div class="room-text-value" id="variable_<?php print($varID); ?>" app_control="1">
                <span class="room-item-variable-value"><?php print($value); ?></span>
                <span class="room-item-variable-label"><?php print($resolution); ?></span>
            </div>
            <?php
            } else
            if ($typ == 2) {
            ?>
            <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" app_control="2"
                       id="variable_<?php print($varID); ?>" <?php if ($value > 0) { print('checked=""'); } ?> >
                <label class="custom-control-label" for="variable_<?php print($varID); ?>"></label>
            </div>
            <?php
            } else
            if ($typ == 3) {
            ?>
            <div class="room-text-value" id="variable_<?php print($varID); ?>" app_control="3">
                <span class="room-item-variable-value"><?php print($value); ?></span>
                <span class="room-item-variable-label"><?php print($resolution); ?></span>
            </div>
            <?php } ?>
        </div>
        <?php if ($typ == 1) { ?>
        <div class="variable-chart">
            <canvas id="chart_<?php print($row['DATA']['ID']); ?>" height="100"></canvas>
        </div>
        <?php 
                $charts[] = $row['DATA']['ID'];
                $colors[] = $color;
            } 
        ?>
    </div>
    <?php
}
?>
</div>

<script type="text/javascript" src="js/Chart.min.js"></script>
<script type="text/javascript" src="js/Chart.bundle.min.js"></script>
<script type="text/javascript">
    var chartList = new Array();
    var serverCurrTime = <?php print(time() * 1000); ?>;
    var localDeltaTime = serverCurrTime - (new Date()).getTime();
    var chartTimeRange = 3 * 60 * 60 * 1000;
    var chartTimeOffset = 1 * 60 * 1000;
    var chartMaxTime = serverCurrTime + chartTimeOffset;
    var chartMinTime = chartMaxTime - chartTimeRange;
    
<?php 
    for ($i = 0; $i < count($charts); $i++) {
        $chart = $charts[$i];

        $sql = "select UNIX_TIMESTAMP(v.CHANGE_DATE) * 1000 V_DATE, v.VALUE ".
               "  from core_variable_changes_mem v ".
               " where v.VARIABLE_ID = $chart ".
               "   and v.VALUE <> 85 ".
               "   and v.CHANGE_DATE > (select max(zz.CHANGE_DATE) from core_variable_changes_mem zz where zz.VARIABLE_ID = $chart) - interval 3 hour".
               " order by v.ID ";
        $q = $pdo->query($sql)->fetchAll();

        $data = [];
        for ($r = 0; $r < count($q); $r++) {
            $x = $q[$r]['V_DATE'];
            $y = $q[$r]['VALUE'];
            $data[] = "{x: $x, y: $y}";
        }

        $data_text = join($data, ', ');
?>
    
    var ctx = document.getElementById("chart_<?php print($chart); ?>");
    var chart = new Chart(ctx, {
        type: 'line',
        data: {
            datasets: [{
                data: [<?php print($data_text); ?>],
                lineTension: 0,
                <?php if ($colors[$i]) print('backgroundColor: '.$colors[$i]).','; ?>
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
        id: <?php print($chart); ?>,
        chart: chart
    });
        
<?php
    }
?>
    
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
        
        setTimeout(chartAutoScroll, <?php print($CHART_UPDATE_INTERVAL); ?>);
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
    
<?php
    $vv = [];
    foreach ($varSteps as $v) {
        $vv[] = "{id: ".$v['id'].", step: ".$v['step']."}";
    }
?>    
    
    var variableSteps = [<?php print(join(', ', $vv))?>];
    
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