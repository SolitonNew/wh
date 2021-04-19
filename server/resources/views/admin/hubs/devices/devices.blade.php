@extends('admin.hubs.hubs')

@section('page-down-menu')
<div class="dropdown-divider"></div>
<a href="#" class="dropdown-item" onclick="deviceAdd(); return false;">@lang('admin/hubs.device_add')</a>
@endsection

@section('page-content')
<style>
    .device-value {
        transition-duration: 3s;
    }
    .device-value.actual {
        font-weight: bold;
        background-color: var(--warning);
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
                <td class="device-value">{{ $row->value }}</td>
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
    $(document).ready(() => {
        $('#devices_table tbody tr').on('click', function () {
            if ($(this).hasClass('table-empty')) return ;
            dialog('{{ route("admin.hub-device-edit", [$hubID, ""]) }}/' + $(this).data('id'));
        });
        
        setInterval(function () {
            let now = (new Date()).getTime();
            $('#devices_table td.device-value.actual').each(function () {
                let counter = parseInt($(this).data('counter')) - 1;
                if (counter > 0) {
                    $(this).data('counter', counter);
                } else {
                    $(this).data('counter', 0).removeClass('actual');
                }
            });
        }, 1000);
    });

    function deviceAdd() {
        dialog('{{ route("admin.hub-device-edit", [$hubID, -1]) }}');
    }
    
    function deviceUpdateValue(id, value) {
        $('#devices_table tr[data-id="' + id + '"] td.device-value')
            .text(value)
            .data('counter', 15)
            .addClass('actual');
    }
    
    function variableChangesHandler(data) {
        $(data).each(function () {
            let id = $(this).data('varid');
            if (id) {
                deviceUpdateValue(id, $(this).data('value'));
            }
        });
    }
</script>
@endsection