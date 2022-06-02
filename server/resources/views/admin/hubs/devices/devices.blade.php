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
    .room-path {
        text-align: left!important;
        white-space: nowrap;
        padding-left: 0.75rem!important;
    }
    .without-host td {
        color: rgba(0,0,0,0.4);
        font-style: italic;
    }
</style>
<div style="display: flex; flex-direction: column; height: 100%;">
    <div class="navbar navbar-page">
        <div class="navbar-page-group">
            <span class="strong">@lang('admin/hubs.device_filter'):</span>
            <select id="deviceFilter" class="custom-select select-tree" style="width: 300px;">
                <option value="none" class="italic">-- @lang('admin/hubs.device_filter_null') --</option>
                <option value="empty" class="italic" {{ $groupID == 'empty' ? 'selected' : '' }}>-- @lang('admin/hubs.device_group_empty') --</option>
                @foreach(\App\Models\Room::generateTree() as $row)
                <option value="{{ $row->id }}" {{ $row->id == $groupID ? 'selected' : '' }}>{!! $row->treePath !!} {{ $row->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="content-body" scroll-store="devicesList">
        <table id="devices_table" class="table table-sm table-hover table-bordered table-fixed-header">
            <thead>
                <tr>
                    <th scope="col" style="width: 60px;"><span>@lang('admin/hubs.device_ID')</span></th>
                    <th scope="col" style="width: 80px;"><span>@lang('admin/hubs.device_TYP')</span></th>
                    <th scope="col" style="width: 100px;"><span>@lang('admin/hubs.device_NAME')</span></th>
                    <th scope="col" style="width: 150px;"><span>@lang('admin/hubs.device_COMM')</span></th>
                    <th scope="col" style="width: 150px;"><span>@lang('admin/hubs.device_GROUP')</span></th>
                    <th scope="col" style="width: 50px;"><span>@lang('admin/hubs.device_APP_CONTROL')</span></th>
                    <th scope="col" style="width: 50px;"><span>@lang('admin/hubs.device_VALUE')</span></th>
                    <th scope="col" style="width: 50px;"><span>@lang('admin/hubs.device_CHANNEL')</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $row)
                <tr data-id="{{ $row->id }}" 
                    class="{{ $row->events->isEmpty() ? '' : 'row-with-events' }} {{ $row->freedevice ? 'without-host' : '' }}">
                    <td>{{ $row->id }}</td>
                    <td>{{ $row->typ }}</td>
                    <td>{{ $row->name }}</td>
                    <td>{{ $row->comm }}</td>
                    <td class="nowrap">
                        @if($row->room && $row->room->name){{ $row->room->name }}@else -- @lang('admin/hubs.device_group_empty') -- @endif
                    </td>
                    <td>{{ config('devices.app_controls.'.$row->app_control)['title'] }}</td>
                    <td class="device-value" data-time="{{ \Carbon\Carbon::parse($row->last_update)->timestamp }}">{{ $row->value }}</td>
                    <td>{{ $row->channel ?: '' }}</td>
                </tr>
                @empty
                <tr class="table-empty">
                    <td colspan="9">@lang('dialogs.table_empty')</td>
                </tr>                
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<script>
    const deviceActialityInterval = 15; /* in seconds */
    var deviceActualityBackgroundColor = [255, 193, 7];
    
    $(document).ready(() => {
        $('#deviceFilter').on('change', function () {
            window.location.href = '{{ route("admin.hub-devices", ["hubID" => $hubID, "groupID" => ""]) }}/' + $(this).val();
        });
        
        $('#devices_table tbody tr').on('click', function () {
            if ($(this).hasClass('table-empty')) return ;
            dialog('{{ route("admin.hub-device-edit", ["hubID" => $hubID, "id" => ""]) }}/' + $(this).data('id'));
        });
        
        setInterval(function () {
            deviceUpdateActuality();
        }, 3000);
        
        deviceUpdateActuality();
    });

    function deviceAdd() {
        dialog('{{ route("admin.hub-device-edit", ["hubID" => $hubID, "id" => -1]) }}');
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