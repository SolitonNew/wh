@extends('terminal.device')

@section('device')
<div class="variable-3-body">
    <div class="card">
        <div class="card-body">
            <div class="variable-3-value-body">
                <div class="variable-3-value-body-center">
                    <div class="variable-3-value">
                        <span class="variable-3-value-text text-primary" id="varValueText" >{{ $deviceValue * $control->varStep }}</span>
                        <span class="variable-3-value-dimension">{{ $control->resolution }}</span>
                    </div>
                </div>
            </div>
            <div class="variable-3-value-body2">
                <div class="text-secondary">{{ $control->varMin }} {{ $control->resolution }}</div>
                <div style="flex-grow: 1;"></div>
                <div class="text-secondary">{{ $control->varMax }} {{ $control->resolution }}</div>
            </div>
            <div>
                <input type="range" class="custom-range" id="varValueRange"
                       min="{{ $control->varMin }}" 
                       max="{{ $control->varMax }}" 
                       step="{{ $control->varStep }}"
                       value="{{ $deviceValue * $control->varStep }}">
            </div>
        </div>
    </div>
</div>

<script>
    var deviceID = {{ $deviceID }};
    var deviceStep = {{ $control->varStep }};
    
    $(document).ready(() => {
        $('#varValueRange').on('change', (e) => {
            varVal = parseFloat($(e.target).val()) / deviceStep;
            
            $.ajax({
                method: "POST",
                url: "/device-set/" + deviceID + "/" + varVal,
                data: {_token: '{{ csrf_token() }}' },
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
        if (varID == deviceID) {
            $('#varValueText').text(varValue * deviceStep);
            $('#varValueRange').val(varValue * deviceStep);
        }
    }
</script>
@endsection