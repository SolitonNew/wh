@extends('admin.hubs.hubs')

@section('page-down-menu')
<div class="dropdown-divider"></div>
<a href="#" class="dropdown-item" onclick="hostAdd(); return false;">@lang('admin/hubs.host_add')</a>
@endsection

@section('page-content')
<div class="content-body" scroll-store="hostsList">
    <table id="hosts_table" class="table table-sm table-hover table-bordered table-fixed-header">
        <thead>
            <tr>
                <th scope="col" style="width: 60px;"><span>@lang('admin/hubs.host_ID')</span></th>
                <th scope="col" style="width: 150px;"><span>@lang('admin/hubs.host_COMM')</span></th>
                <th scope="col" style="width: 250px;"><span>@lang('admin/hubs.host_ROM')</span></th>
                <th scope="col" style="width: 110px;"><span>@lang('admin/hubs.host_CHANNELS')</span></th>
                <th scope="col" style="width: 250px;"><span>@lang('admin/hubs.host_VARIABLES')</span></th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
            <tr data-id="{{ $row->id }}" class="{{ $row->lost ? 'row-with-ow-lost' : '' }}">
                <td>{{ $row->id }}</td>
                <td>{{ $row->comm }}</td>
                <td class="nowrap">{{ $row->rom }}</td>
                <td>{{ $row->channels }}</td>
                <td>
                    @foreach($row->devices as $v)
                    <div><a class="nowrap" href="#" onclick="showVariable({{ $v->id }}); return false;">[{{ $v->channel }}] {{ $v->name }}</a></div>
                    @endforeach
                </td>
            </tr>
            @empty
            <tr class="table-empty">
                <td colspan="6">@lang('dialogs.table_empty')</td>
            </tr>  
            @endforelse
        </tbody>
    </table>
</div>
@endsection