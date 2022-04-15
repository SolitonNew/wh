@extends('admin.hubs.hubs')

@section('page-down-menu')
<div class="dropdown-divider"></div>
<a href="#" class="dropdown-item" onclick="hostAdd(); return false;">@lang('admin/hubs.soft_host_add')</a>
@endsection

@section('page-content')
<div class="content-body" scroll-store="hostsList">
    <table id="hosts_table" class="table table-sm table-hover table-bordered table-fixed-header">
        <thead>
            <tr>
                <th scope="col" style="width: 60px;"><span>@lang('admin/hubs.host_ID')</span></th>
                <th scope="col" style="width: 150px;"><span>@lang('admin/hubs.host_TYP')</span></th>
                <th scope="col" style="width: 110px;"><span>@lang('admin/hubs.host_CHANNELS')</span></th>
                <th scope="col" style="width: 250px;"><span>@lang('admin/hubs.host_DEVICES')</span></th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
            <tr data-id="{{ $row->id }}" class="{{ $row->lost ? 'row-with-ow-lost' : '' }}">
                <td>{{ $row->id }}</td>
                <td>{{ $row->typ }}</td>
                <td></td>
                <td>
                    @foreach($row->devices as $v)
                    <div><a class="nowrap" href="#" onclick="deviceEdit({{ $v->id }}); return false;">[{{ $v->channel }}] {{ $v->name }}</a></div>
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
<script>
    $(document).ready(() => {
        $('#hosts_table tbody tr').on('click', function (e) {
            if ($(this).hasClass('table-empty')) return ;
            if ($(e.target).is('a')) return ;
            hostEdit($(this).data('id'));
        });
    });
    
    function hostAdd() {
        dialog('{{ route("admin.hub-softhost-edit", [$hubID, -1]) }}');
    }
    
    function hostEdit(id) {
        dialog('{{ route("admin.hub-softhost-edit", [$hubID, ""]) }}/' + id);
    }
    
    function deviceEdit(id) {
        dialog('{{ route("admin.hub-device-edit", [$hubID, ""]) }}/' + id);
    }
</script>
@endsection