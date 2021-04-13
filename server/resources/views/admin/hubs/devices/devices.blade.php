@extends('admin.hubs.hubs')

@section('page-content')
<div class="content-body" scroll-store="devicesList">
    <table id="devices_table" class="table table-sm table-hover table-bordered table-fixed-header">
        <thead>
            <tr>
                <th scope="col" style="width: 60px;"><span>@lang('admin/variables.table_id')</span></th>
                <th scope="col" style="width: 150px;"><span>@lang('admin/variables.table_controller')</span></th>
                <th scope="col" style="width: 50px;"><span>@lang('admin/variables.table_typ')</span></th>
                <th scope="col" style="width: 50px;"><span>@lang('admin/variables.table_readonly')</span></th>
                <th scope="col" style="width: 50px;"><span>@lang('admin/variables.table_name')</span></th>
                <th scope="col" style="width: 50px;"><span>@lang('admin/variables.table_comm')</span></th>
                <th scope="col" style="width: 50px;"><span>@lang('admin/variables.table_app_control')</span></th>
                <th scope="col" style="width: 50px;"><span>@lang('admin/variables.table_value')</span></th>
                <th scope="col" style="width: 50px;"><span>@lang('admin/variables.table_channel')</span></th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
            <tr data-id="{{ $row->id }}" class="{{ $row->with_events ? 'row-with-events' : '' }} {{ $row->free_variable ? 'italic' : '' }}">
                <td>{{ $row->id }}</td>
                <td>{{ $row->controller_name }}</td>
                <td>{{ $row->typ }}</td>
                <td>{{ Lang::get('admin/variables.table_readonly_list.'.$row->direction) }}</td>
                <td>{{ $row->name }}</td>
                <td>{{ $row->comm }}</td>
                <td>{{ lang::get('admin/variables.app_control.'.$row->app_control) }}</td>
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