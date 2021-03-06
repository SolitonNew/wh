<?php 
$varMin = $control['varMin'];
$varMax = $control['varMax'];
$varStep = $control['varStep'];

?>

<div class="variable-3-body">
    <div class="card">
        <div class="card-body">
            <div class="variable-3-value-body">
                <div class="variable-3-value-body-center">
                    <div class="variable-3-value">
                        <span class="variable-3-value-text text-primary" id="varValueText" ><?php print($varValue * $varStep); ?></span>
                        <span class="variable-3-value-dimension"><?php print($control['resolution']); ?></span>
                    </div>
                </div>
            </div>
            <div class="variable-3-value-body2">
                <div class="text-secondary"><?php print($varMin); ?> <?php print($control['resolution']); ?></div>
                <div style="flex-grow: 1;"></div>
                <div class="text-secondary"><?php print($varMax); ?> <?php print($control['resolution']); ?></div>
            </div>
            <div>
                <input type="range" class="custom-range" id="varValueRange"
                       min="<?php print($varMin); ?>" 
                       max="<?php print($varMax); ?>" 
                       step="<?php print($varStep); ?>"
                       value="<?php print($varValue * $varStep); ?>">
            </div>
        </div>
    </div>
</div>

<script>
    var variableID = <?php print($varID); ?>;
    var variableStep = <?php print($varStep); ?>;
    
    $(document).ready(() => {
        $('#varValueRange').on('change', (e) => {
            varVal = parseFloat($(e.target).val()) / variableStep;
            
            $.ajax({
                method: "POST",
                url: "api.php",
                data: {page: 'data', id: variableID, value: varVal},
            }).done((data)=>{
                if (data) {
                    alert(data);
                }
            });
        });
        
        $('#varValueRange').on('input', (e) => {
            $('#varValueText').text($(e.target).val());
        });
    });
    
    function variableOnChanged(varID, varValue, varTime) {
        if (varID == variableID) {
            $('#varValueText').text(varValue * variableStep);
            $('#varValueRange').val(varValue * variableStep);
        }
    }
</script>