@extends('admin.hubs.hubs')

@section('page-content')
<div class="content-body" scroll-store="hostsList">
    <table id="owList" class="table table-sm table-hover table-bordered table-fixed-header">
        <thead>
            <tr>
                <th scope="col" style="width: 50px;"><span>@lang('admin/configuration.table_ID')</span></th>
                <th scope="col" style="width: 150px;"><span>@lang('admin/configuration.table_COMM')</span></th>
                <th scope="col" style="width: 300px;"><span>@lang('admin/configuration.table_ROM')</span></th>
                <th scope="col" style="width: 150px;"><span>@lang('admin/configuration.table_CHANNELS')</span></th>
                <th scope="col" style="width: 250px;"><span>@lang('admin/configuration.table_VARIABLES')</span></th>
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
                    @foreach($row->variables as $v)
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