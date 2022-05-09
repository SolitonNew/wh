@foreach(\App\Models\EventMem::getLastDeviceChanges($logLastID) as $row)
<div class="log-row" 
     data-id="{{ $row->id }}" data-varID="{{ $row->device_id }}" 
     data-value="{{ $row->value }}" data-time="{{ \Carbon\Carbon::parse($row->created_at)->timestamp }}">
    <div class="log-time text-primary">[{{ parse_datetime($row->created_at)->format('H:i:s') }}]</div>
    <div class="log-text">
        @if($row->app_control > 0)
        '@lang('admin/hubs.log_app_control.'.$row->app_control). {{ $row->comm ?? $row->group_name }}'
        @else
        '{{ $row->comm ?? $row->group_name }}'
        @endif
        <span class="strong">{{ \App\Models\EventMem::decodeLogValue($row->app_control, $row->value) }}</span>
    </div>
</div>
@endforeach
