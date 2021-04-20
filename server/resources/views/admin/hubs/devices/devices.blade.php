@extends('admin.hubs.hubs')

@section('page-down-menu')
<div class="dropdown-divider"></div>
<a href="#" class="dropdown-item" onclick="deviceAdd(); return false;">@lang('admin/hubs.device_add')</a>
@endsection

@section('page-content')
<style>
    .device-value {
        
    }
    .device-value.actual {
        font-weight: bold;
        background-color: rgb(255, 193, 7);
        transition-duration: 1s;
    }
</style>
<div class="content-body" scroll-store="devicesList">
    <table id="devices_table" class="table table-sm table-hover table-bordered table-fixed-header">
        <thead>
            <tr>
                <th scope="col" style="width: 60px;"><span>@lang('admin/hubs.device_ID')</span></th>
                <th scope="col" style="width: 80px;"><span>@lang('admin/hubs.device_TYP')</span></th>
                <th scope="col" style="width: 100px;"><span>@lang('admin/hubs.device_NAME')</span></th>
                <th scope="col" style="width: 200px;"><span>@lang('admin/hubs.device_COMM')</span></th>
                <th scope="col" style="width: 50px;"><span>@lang('admin/hubs.device_APP_CONTROL')</span></th>
                <th scope="col" style="width: 50px;"><span>@lang('admin/hubs.device_VALUE')</span></th>
                <th scope="col" style="width: 50px;"><span>@lang('admin/hubs.device_CHANNEL')</span></th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
            <tr data-id="{{ $row->id }}" class="{{ $row->with_events ? 'row-with-events' : '' }}">
                <td>{{ $row->id }}</td>
                <td>{{ $row->typ }}</td>
                <td>{{ $row->name }}</td>
                <td>{{ $row->comm }}</td>
                <td>{{ Lang::get('admin/hubs.app_control.'.$row->app_control) }}</td>
                <td class="device-value" data-time="{{ \Carbon\Carbon::parse($row->last_update)->timestamp }}">{{ $row->value }}</td>
                <td>{{ $row->channel }}</td>
            </tr>
            @empty
            <tr class="table-empty">
                <td colspan="9">@lang('dialogs.table_empty')</td>
            </tr>                
            @endforelse
        </tbody>
    </table>
</div>
<script>
    const deviceActialityInterval = 15; /* in seconds */
    var deviceActualityBackgroundColor = [255, 193, 7];
    
    $(document).ready(() => {
        $('#devices_table tbody tr').on('click', function () {
            if ($(this).hasClass('table-empty')) return ;
            dialog('{{ route("admin.hub-device-edit", [$hubID, ""]) }}/' + $(this).data('id'));
        });
        
        setInterval(function () {
            deviceUpdateActuality();
        }, 1000);
        
        deviceUpdateActuality();
    });

    function deviceAdd() {
        dialog('{{ route("admin.hub-device-edit", [$hubID, -1]) }}');
    }
    
    function deviceUpdateValue(id, value, time) {
        $('#devices_table tr[data-id="' + id + '"] td.device-value')
            .text(value)
            .data('time', time)
            .addClass('actual');
    }
    
    function deviceUpdateActuality() {
        let actualTime = (new Date()).getTime() / 1000 - deviceActialityInterval - serverTimeOffset;
        $('#devices_table td.device-value').each(function () {
            let time = parseInt($(this).data('time'));
            if (time < actualTime) {
                $(this).removeClass('actual').css({
                    'background-color': '',
                });
            } else {
                let t = (time - actualTime) / deviceActialityInterval;
                let color = deviceActualityBackgroundColor.join(', ');
                $(this).addClass('actual').css({
                    'background-color': 'rgba(' + color + ',' + t + ')',
                });
            }
        });
    }
    
    function variableChangesHandler(data) {
        $(data).each(function () {
            let id = $(this).data('varid');
            if (id) {
                deviceUpdateValue(id, $(this).data('value'), $(this).data('time'));
            }
        });
    }
</script>
@endsection