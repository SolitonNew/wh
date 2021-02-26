<nav aria-label="breadcrumb">
    <ol class="row breadcrumb">
        <li style="flex-grow: 1;"><?php print($MAIN_MENUS['main']); ?></li>
        <li><a href="?page=checked"><?php print($MAIN_MENUS['checked']); ?></a></li>
    </ol>
</nav>

<div style="display: flex; flex-direction: column; min-height: calc(100vh - 6rem);">
<div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3" style="flex-grow: 1;">
    
<?php
    $sql = 'select * from plan_parts order by NAME, ORDER_NUM';
    $groups = $pdo->query($sql)->fetchAll();
    
    $sql = 'select * from core_variables';
    $variables = $pdo->query($sql)->fetchAll();
    
    $groupList = [];
    
    function printItem($parentID, &$groups, $level, &$groupList) {
        for ($i = 0; $i < count($groups); $i++) {
            $row = $groups[$i];
            if ($row['PARENT_ID'] == $parentID) {
                switch ($level) {
                    case 0:
                        break;
                    case 1:
                    case 2:
                        $groupList[] = ['level' => $level, 'row' => $row];
                        break;
                }                
                printItem($row['ID'], $groups, $level + 1, $groupList);
            }            
        }        
    }
    
    function findVariable($roomID, $roomNameUpper, &$variables) {
        $res = [];
        for ($i = 0; $i < count($variables); $i++) {
            $var = $variables[$i];
            if ($var['GROUP_ID'] == $roomID) {
                if (mb_strtoupper(mb_substr($var['COMM'], 0, mb_strlen($roomNameUpper))) == $roomNameUpper) {
                    $res[] = $var;
                }
            }
        }
        return $res;
    }
    
    printItem(null, $groups, 0, $groupList);   
    
    $listStart = false;
    $currGroupTitle = '';
    
    /**
     * Названия вспомогательных выключателей освещения
     */
    $switches_2 = [
        ' НОЧНИК', 
        ' СТОЛОВАЯ'
    ];
    
    for ($i = 0; $i < count($groupList); $i++) {
        $row = $groupList[$i]['row'];        
        if ($groupList[$i]['level'] == 1) {
            $currGroupTitle = mb_strtoupper($row['NAME']);
?>
<?php if ($listStart) { ?>
    </div>
</div>
<?php } ?>
<div class="col">
<div class="list-group list-group-flush main-column" style="margin-bottom: 1rem;">
    <div class="alert alert-light" role="alert" style="margin-bottom: 0px;">
        <?php print($currGroupTitle); ?>
    </div>
<?php
            $listStart = true;
        } else {
            $roomNameUpper = mb_strtoupper($row['NAME']);
            
            $roomNameUpperCrop = str_replace($currGroupTitle, '', $roomNameUpper);
                
            $vars = findVariable($row['ID'], $roomNameUpper, $variables);
            
            if (count($vars) > 0) {
                $temperature_id = -1;
                $temperature_val = 0;

                $switch_1_id = -1;
                $switch_1_val = 1;

                $switch_2_id = -1; 
                $switch_2_val = 1;

                foreach ($vars as $v) {
                    switch ($v['APP_CONTROL']) {
                        case 4:                       
                            if (mb_strtoupper($v['COMM']) == $roomNameUpper) {
                                $temperature_id = $v['ID'];
                                $temperature_val = $v['VALUE'];
                            }
                            break;
                        case 1:
                            if (mb_strtoupper($v['COMM']) == $roomNameUpper) {
                                $switch_1_id = $v['ID'];
                                $switch_1_val = $v['VALUE'];
                            } else {
                                for ($n = 0; $n < count($switches_2); $n++) {
                                    if (mb_strtoupper($v['COMM']) == $roomNameUpper.$switches_2[$n]) {
                                        $switch_2_id = $v['ID'];
                                        $switch_2_val = $v['VALUE'];
                                        break;
                                    }
                                }
                            }
                            break;
                    }
                }
            
?>
<div class="list-group-item main-item">
    <a href="?page=room&roomID=<?php print($row['ID']); ?>"><?php print($roomNameUpperCrop); ?></a>
    <?php
    if ($temperature_id > -1) {
    ?>
    <div id="variable_<?php print($temperature_id); ?>" class="main-item-value" app_control="1">
        <span class="main-item-value-text"><?php print($temperature_val); ?></span><span class="main-item-value-label">°C</span>
    </div>
    <?php
    }
    if ($switch_1_id > -1) {
    ?>
    <div class="custom-control custom-switch">
        <input type="checkbox" class="custom-control-input" app_control="2"
               id="variable_<?php print($switch_1_id); ?>" <?php if ($switch_1_val > 0) { print('checked=""'); } ?> >
        <label class="custom-control-label" for="variable_<?php print($switch_1_id); ?>"></label>
    </div>
    <?php
    }
    if ($switch_2_id > -1) {
    ?>
    <div class="custom-control custom-switch">
        <input type="checkbox" class="custom-control-input" app_control="2"
               id="variable_<?php print($switch_2_id); ?>" <?php if ($switch_2_val > 0) { print('checked=""'); } ?> >
        <label class="custom-control-label" for="variable_<?php print($switch_2_id); ?>"></label>
    </div>    
    <?php
    }
    ?>
</div>
<?php
            }
        } 
    }
?>
<?php if ($listStart) { ?>
    </div>
</div>

<?php } ?>

<script>
    function variableOnChanged(varID, varValue, varTime) {
        var v = $('#variable_' + varID);
        switch (v.attr('app_control')) {
            case '1':
                $('.main-item-value-text', v).text(varValue);
                break;
            case '2':
                v.prop('checked', parseInt(varValue) > 0);
                break;
        }
    }
</script>

</div>

<?php include "video_list.php"; ?>
</div>