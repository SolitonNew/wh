@extends('admin.hubs.hubs')

@section('page-down-menu')
<div class="dropdown-divider"></div>
<a href="#" class="dropdown-item" onclick="deviceAdd(); return false;">@lang('admin/hubs.device_add')</a>
@endsection

@section('page-content')
<div class="content-body" scroll-store="devicesList">
    <table id="devices_table" class="table table-sm table-hover table-bordered table-fixed-header">
        <thead>
            <tr>
                <th scope="col" style="width: 60px;"><span>@lang('admin/hubs.device_ID')</span></th>
                <th scope="col" style="width: 80px;"><span>@lang('admin/hubs.device_TYP')</span></th>
                <th scope="col" style="width: 50px;"><span>@lang('admin/hubs.device_READONLY')</span></th>
                <th scope="col" style="width: 100px;"><span>@lang('admin/hubs.device_NAME')</span></th>
                <th scope="col" style="width: 150px;"><span>@lang('admin/hubs.device_COMM')</span></th>
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
                <td>{{ Lang::get('admin/hubs.device_readonly_list.'.$row->direction) }}</td>
                <td>{{ $row->name }}</td>
                <td>{{ $row->comm }}</td>
                <td>{{ lang::get('admin/hubs.app_control.'.$row->app_control) }}</td>
                <td>{{ $row->value }}</td>
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
@endsection