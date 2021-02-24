@foreach(\App\Http\Models\VariableChangesModel::getLastVariables() as $row)
<div class="log-row" data-id="{{ $row->ID }}" data-varID="{{ $row->VARIABLE_ID }}" data-value="{{ $row->VALUE }}">
    <div class="log-time text-primary">[{{ \Carbon\Carbon::parse($row->CHANGE_DATE)->format('H:i:s') }}]</div>
    <div class="log-text">
        '@lang('admin/variables.log_app_control.'.$row->APP_CONTROL). {{ $row->COMM }}'
        {{ \App\Http\Models\VariableChangesModel::decodeLogValue($row->APP_CONTROL, $row->VALUE) }}
    </div>
</div>
@endforeach
