
<div style="width: 40rem;">
    <div class="list-group">

<?php
$q = $pdo->query("select VALUE from core_propertys where NAME = 'WEB_CHECKED'")->fetchAll();
$checks = $q[0]['VALUE'];

if ($checks) {
    $sql = "select v.* ".
           "  from core_variables v ".
           " where v.ID in ($checks) ";
} else {
    $sql = "select v.* ".
           "  from core_variables v ".
           " where v.ID = 0 ";
}

$q = $pdo->query($sql)->fetchAll();

$rows = [];

foreach (explode(',', $checks) as $key) {
    for ($i = 0; $i < count($q); $i++) {
        $row = $q[$i];
        if ($q[$i]['ID'] == $key) {
            $c = decodeAppControl($row['APP_CONTROL']);
            $itemLabel = mb_strtoupper($row['COMM']); // groupVariableName($groupTitle, mb_strtoupper($row['COMM']), mb_strtoupper($c['label']));
            $c['title'] = $itemLabel;

            $rows[] = [
                'DATA' => $row, 
                'CONTROL' => $c
            ];
            break;
        }
    }
}

for ($i = 0; $i < count($rows); $i++) {
    $row = $rows[$i];
    $c = $row['CONTROL'];
?>   
    <div class="list-group-item checked-edit-item">
        <div class="checked-edit-item-label">
            <?php print($c['label']); ?>
            <?php print($row['DATA']['COMM']); ?>
        </div>
        <div class="checked-edit-item-edit" style="white-space: nowrap;">
            <a class="btn btn-sm btn-outline-primary checked-edit-item-order-up"
                id="up_<?php print($row['DATA']['ID']); ?>" href="#"><img src="img/arrow-thick-top-2x.png"></a>
            <a class="btn btn-sm btn-outline-primary checked-edit-item-order-down"
                id="down_<?php print($row['DATA']['ID']); ?>" href="#"><img src="img/arrow-thick-bottom-2x.png"></a>
        </div>
    </div>
<?php  
}
?>
    </div>
</div>

<script>
    $('document').ready(() => {
        $('.checked-edit-item-order-up').on('click', (e) => {
            e.preventDefault();
            let id = $(e.currentTarget).attr('id').substr(3);
            
            $.ajax({
                url: 'api.php?page=checked_up&id=' + id,
            }).done((res) => {
                if (res == 'OK') {
                    let item = $(e.currentTarget).parent().parent();
                    item.insertBefore(item.prev());
                    recalcDisabledButtons();
                } else {
                    alert(res);
                }
            });
            
            return false;
        });
        
        $('.checked-edit-item-order-down').on('click', (e) => {
            e.preventDefault();
            let id = $(e.currentTarget).attr('id').substr(5);
            
            $.ajax({
                url: 'api.php?page=checked_down&id=' + id,
            }).done((res) => {
                if (res == 'OK') {
                    let item = $(e.currentTarget).parent().parent();
                    item.insertAfter(item.next());
                    recalcDisabledButtons();
                } else {
                    alert(res);
                }                
            });          
            
            return false;
        });
        
        recalcDisabledButtons();
    });
    
    function recalcDisabledButtons() {
        $('.checked-edit-item-order-up.disabled').removeClass('disabled');
        $('.checked-edit-item-order-down.disabled').removeClass('disabled');
        
        let ls = $('.list-group-item');
        if (ls.length > 0) {
             $('.checked-edit-item-order-up', ls[0]).addClass('disabled');
             $('.checked-edit-item-order-down', ls[ls.length - 1]).addClass('disabled');
        }
    }
    
    
    function variableOnChanged(varID, varValue, varTime) {
        
    }
</script>