<?php
$sel = '';
if (isset($_GET['sel'])) {
    $sel = $_GET['sel'];
}

$app_controls = [];

?>

<div style="width: 40rem;">
    <div class="alert alert-dark" style="margin-bottom: 1rem;">
        <select id="filter" class="form-control">
            <option value="-1">-- ВСЕ --</option>
            <?php foreach ($CONTOL_LABELS as $key => $val) { ?>
            <?php if ($val) { 
                    $app_controls[] = $key;
            ?>
            <option value="<?php print($key); ?>"  <?php if ($sel == $key) print('selected'); ?>><?php print($val); ?></option>>
            <?php } ?>
            <?php } ?>
        </select>
    </div>

    <div class="list-group">
<?php

$app_controls = join(',', $app_controls);

$q = $pdo->query("select VALUE from core_properties where NAME = 'WEB_CHECKED'")->fetchAll();
$checks = explode(',', $q[0]['VALUE']);

$where = '';
if ($sel > 0) {
    $where = " and v.APP_CONTROL = $sel ";
}

$sql = "select v.* ".
       "  from core_variables v ".
       " where v.APP_CONTROL in ($app_controls) ".
       $where.
       " order by v.COMM";

$ls = $pdo->query($sql)->fetchAll();

foreach ($ls as $row) {
    $c = decodeAppControl($row['APP_CONTROL']);
?>   
    <div class="list-group-item checked-edit-item">
        <div class="checked-edit-item-label">
            <?php print($c['label']); ?>
            <?php print($row['COMM']); ?>
        </div>
        <div class="checked-edit-item-edit <?php if (in_array($row['ID'], $checks)) print('del'); ?>">
            <a class="btn btn-sm btn-outline-primary checked-edit-item-edit-del" id="del_<?php print($row['ID']); ?>" href="#">
                <img src="img/check-2x.png">
            </a>
            <a class="btn btn-sm btn-outline-primary checked-edit-item-edit-add" id="add_<?php print($row['ID']); ?>" href="#">
                <img src="img/check-2x.png" style="opacity: 0;">
            </a>
        </div>
    </div>
<?php  
}
?>
    </div>
</div>

<script>
    $('document').ready(() => {
        $('.checked-edit-item-edit-del').on('click', (e) => {
            e.preventDefault();
            let id = $(e.currentTarget).attr('id').substr(4);
            
            $.ajax({
                url: 'api.php?page=checked_del&id=' + id,
            }).done((res) => {
                if (res == 'OK') {
                    $(e.currentTarget).parent().removeClass('del');
                } else {
                    alert(res);
                }
            });
        });
        
        $('.checked-edit-item-edit-add').on('click', (e) => {
            e.preventDefault();
            let id = $(e.currentTarget).attr('id').substr(4);
            
            $.ajax({
                url: 'api.php?page=checked_add&id=' + id,
            }).done((res) => {
                if (res == 'OK') {
                    $(e.currentTarget).parent().addClass('del');
                } else {
                    alert(res);
                }
            });
        });
        
        $('#filter').change((e) => {
            let id = $(e.target).val()
            let url = '?page=checked_edit';
            if (id == -1) {
                //
            } else {
                url += '&sel=' + id;
            }
            window.location = url;
        });
    });
    
    function variableOnChanged(varID, varValue, varTime) {
        
    }
</script>