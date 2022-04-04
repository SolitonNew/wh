<?php

include 'app/connection.php';

$page = '';

if (isset($_GET['page'])) {
    $page = $_GET['page'];
} else
if (isset($_POST['page'])) {
    $page = $_POST['page'];
}

switch ($page) {
    case 'changes':
        if (isset($_GET['lastID']) && $_GET['lastID'] > -1) {
            $lastID = (int)$_GET['lastID'];
            $rows = $pdo->query("select c.ID, c.VARIABLE_ID, c.VALUE, UNIX_TIMESTAMP(c.CHANGE_DATE) * 1000 CHANGE_DATE ".
                                "  from core_variable_changes_mem c ".
                                " where c.ID > $lastID ".
                                "   and c.VALUE <> 85 ".
                                " order by c.ID")->fetchAll();
            return print(json_encode($rows));
        } else {
            $d = $pdo->query('select max(ID) MAX_ID from core_variable_changes_mem')->fetchAll();
            if (count($d) > 0) {
                return print('LAST_ID: '.$d[0]['MAX_ID']);
            }
            return print('ERROR');
        }
        break;
    case 'data':
        if (isset($_GET['id'])) {

        } else 
        if (isset($_POST['id'])) {
            $varID = (int)$_POST['id'];
            $varVal = (float)$_POST['value'];
            try {
                $pdo->query("CALL CORE_SET_VARIABLE($varID, $varVal, -1)");
            } catch (Exception $e) {
                print($e);
            }
        }
        break;
    case 'checked_add':
        $id = $_GET['id'];
        $q = $pdo->query("select VALUE from core_properties where NAME = 'WEB_CHECKED'")->fetchAll();
        if (count($q)) {
            if ($q[0]['VALUE']) {
                $a = explode(',', $q[0]['VALUE']);
            } else {
                $a = [];
            }
            if (!in_array($id, $a)) {
                $a[] = $id;
                $s = join(',', $a);
                $pdo->query("update core_properties set VALUE = '$s' where NAME = 'WEB_CHECKED'");
                print('OK');
            }
        }
        break;
    case 'checked_del':
        $id = $_GET['id'];
        $q = $pdo->query("select VALUE from core_properties where NAME = 'WEB_CHECKED'")->fetchAll();
        if (count($q)) {
            if ($q[0]['VALUE']) {
                $a = explode(',', $q[0]['VALUE']);
            } else {
                $a = [];
            }
            $i = array_search($id, $a);
            if ($i > -1) {
                array_splice($a, $i, 1);
                $s = join(',', $a);
                $pdo->query("update core_properties set VALUE = '$s' where NAME = 'WEB_CHECKED'");
                print('OK');
            }
        }
        break;
    case 'checked_up':
        $id = $_GET['id'];
        $q = $pdo->query("select VALUE from core_properties where NAME = 'WEB_CHECKED'")->fetchAll();
        if (count($q)) {
            $a = explode(',', $q[0]['VALUE']);
            for ($i = 1; $i < count($a); $i++) {
                if ($a[$i] == $id) {
                    $t = $a[$i - 1];
                    $a[$i - 1] = $a[$i];
                    $a[$i] = $t;
                    $s = join(',', $a);
                    $pdo->query("update core_properties set VALUE = '$s' where NAME = 'WEB_CHECKED'");
                    print('OK');
                    break;
                }
            }
        }
        break;
    case 'checked_down':
        $id = $_GET['id'];
        $q = $pdo->query("select VALUE from core_properties where NAME = 'WEB_CHECKED'")->fetchAll();
        if (count($q)) {
            $a = explode(',', $q[0]['VALUE']);
            for ($i = 0; $i < count($a) - 1; $i++) {
                if ($a[$i] == $id) {
                    $t = $a[$i + 1];
                    $a[$i + 1] = $a[$i];
                    $a[$i] = $t;
                    $s = join(',', $a);
                    $pdo->query("update core_properties set VALUE = '$s' where NAME = 'WEB_CHECKED'");
                    print('OK');
                    break;
                }
            }
        }
        break;
    case 'checked_color':
        $action = $_POST['action'];
        
        $keyword = $_POST['keyword'];
        
        $color = '';
        if (isset($_POST['color'])) {
            $color = $_POST['color'];
        }
        $q = $pdo->query("select VALUE from core_properties where NAME = 'WEB_COLOR'")->fetchAll();        
        
        if (count($q)) {
            if ($q[0]['VALUE']) {
                $a = json_decode($q[0]['VALUE'], true);
                if (count($a) == 0) {
                    $a = [];
                }
            } else {
                $a = [];
            }
            
            switch ($action) {
                case 'add':
                    $a[] = [
                        'keyword' => $keyword,
                        'color' => $color
                    ];
                    break;
                case 'set':
                    $finded = false;
                    for ($i = 0; $i < count($a); $i++) {
                        if (mb_strtoupper($a[$i]['keyword']) == mb_strtoupper($keyword)) {
                            $finded = true;
                            $a[$i]['color'] = $color;
                            break;
                        }
                    }
                    if (!$finded) {
                        $a[] = [
                            'keyword' => $keyword,
                            'color' => $color
                        ];
                    }
                    break;
                case 'del':
                    for ($i = 0; $i < count($a); $i++) {
                        if (mb_strtoupper($a[$i]['keyword']) == mb_strtoupper($keyword)) {
                            array_splice($a, $i, 1);
                            break;
                        }
                    }
                    break;
            }
            
            $pdo->prepare("update core_properties set VALUE = ? where NAME = 'WEB_COLOR'")->execute([json_encode($a)]);
            return 'OK';
        }
        break;
}