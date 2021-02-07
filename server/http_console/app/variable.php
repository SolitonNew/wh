<?php 

$varID = (int)$_GET['varID'];

$sql = "select p.NAME GROUP_TITLE, v.COMM VARIABLE_TITLE, v.APP_CONTROL, v.GROUP_ID, v.VALUE ".
       "  from core_variables v, plan_parts p ".
       " where v.id = $varID ".
       "   and p.ID = v.GROUP_ID";
$d = $pdo->query($sql)->fetchAll();

$groupID = -1;
$groupTitle = '';
$variableTitle = '';
$appControl = -1;
$control = [];
$varValue = '';
if (count($d) > 0) {
    $groupID = $d[0]['GROUP_ID'];
    $groupTitle = mb_strtoupper($d[0]['GROUP_TITLE']);
    $variableTitle = $d[0]['VARIABLE_TITLE'];
    $appControl = $d[0]['APP_CONTROL'];
    $control = decodeAppControl($appControl);
    $variableTitle = groupVariableName($groupTitle, mb_strtoupper($variableTitle), $control['label']);
    $varValue = $d[0]['VALUE'];
}

?>

<nav aria-label="breadcrumb">
    <ol class="row breadcrumb">
        <li class="breadcrumb-item"><a href="/"><?php print($MAIN_MENUS['main']); ?></a></li>
        <li class="breadcrumb-item"><a href="?page=room&roomID=<?php print($groupID); ?>"><?php print($groupTitle); ?></a></li>
        <li class="breadcrumb-item active" aria-current="page"><?php print($variableTitle); ?></li>
    </ol>
</nav>

<?php

switch ($control['typ']) {
    case 1:
        break;
    case 2:
        break;
    case 3:
        include 'variable_3.php';
        break;
}