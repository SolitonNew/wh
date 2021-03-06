@foreach(\App\Http\Models\VariableChangesMemModel::getLastVariables() as $row)
<div class="log-row" data-id="{{ $row->id }}" data-varID="{{ $row->variable_id }}" data-value="{{ $row->value }}">
    <div class="log-time text-primary">[{{ \Carbon\Carbon::parse($row->change_date)->format('H:i:s') }}]</div>
    <div class="log-text">
        @if($row->app_control > 0)
        '@lang('admin/variables.log_app_control.'.$row->app_control). {{ $row->comm }}'
        @else
        '{{ $row->comm }}'
        @endif
        {{ \App\Http\Models\VariableChangesMemModel::decodeLogValue($row->app_control, $row->value) }}
    </div>
</div>
@endforeach
